<?php
/**
 * POST /api/kotani-webhook.php
 * Receives status callbacks from Kotani Pay for onramp (BUY delivery)
 * and offramp (SELL deposit/payout) transactions.
 *
 * Mirrors api/kora-webhook.php's pattern (signature verification,
 * idempotency, honest status handling) but scoped only to the crypto
 * settlement side — this never touches `payments` / `subscriptions`.
 */

$log_dir = __DIR__ . '/../logs';
if (!is_dir($log_dir)) {
    @mkdir($log_dir, 0755, true);
}

$raw_body = file_get_contents('php://input');
@file_put_contents($log_dir . '/kotani_webhooks.log', date('c') . ' | ' . $raw_body . "\n", FILE_APPEND | LOCK_EX);

header('Content-Type: application/json');

require_once __DIR__ . '/../admin/config.php';
require_once __DIR__ . '/../app/Services/KotaniPayService.php';
require_once __DIR__ . '/../app/Services/CryptoSettlementService.php';

if (!defined('SUPABASE_ANON_KEY'))    define('SUPABASE_ANON_KEY',    SUPABASE_ANON);
if (!defined('SUPABASE_SERVICE_KEY')) define('SUPABASE_SERVICE_KEY', SUPABASE_SERVICE);

use App\Services\KotaniPayService;
use App\Services\CryptoSettlementService;

$event = json_decode($raw_body, true);
if (!$event) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}

// ── Verify signature ────────────────────────────────────────────────
// Header name should be confirmed against your Kotani Pay dashboard's
// webhook settings — X-Kotani-Signature is used here as the
// placeholder pending that confirmation (see KotaniPayService).
$sig_header = $_SERVER['HTTP_X_KOTANI_SIGNATURE'] ?? $_SERVER['X_KOTANI_SIGNATURE'] ?? '';

$kotani = new KotaniPayService();
if (!$kotani->verifyWebhookSignature($sig_header, $raw_body)) {
    @file_put_contents(
        $log_dir . '/kotani_webhooks.log',
        date('c') . " | SIGNATURE VERIFICATION FAILED | Header: $sig_header\n",
        FILE_APPEND | LOCK_EX
    );
    http_response_code(400);
    echo json_encode(['error' => 'Invalid webhook signature']);
    exit;
}

// ── Extract event data ──────────────────────────────────────────────
// Field names below (externalReference, status, id) match the payload
// shape KotaniPayService sends when creating onramp/offramp requests —
// confirm against Kotani's actual webhook payload docs before go-live.
$data = $event['data'] ?? $event;
$reference   = $data['externalReference'] ?? $data['reference'] ?? '';
$status      = strtolower($data['status'] ?? '');
$providerRef = $data['id'] ?? $data['requestId'] ?? '';
$txHash      = $data['transactionHash'] ?? $data['blockchainTxHash'] ?? null;
$depositAddr = $data['depositAddress'] ?? $data['walletAddress'] ?? null;

if (!$reference) {
    http_response_code(200);
    echo json_encode(['ok' => true, 'message' => 'No transaction reference found — event ignored']);
    exit;
}

$supabase_url = SUPABASE_URL;
$service_key  = SUPABASE_SERVICE_KEY;

$rows = kotani_supabase_request(
    "$supabase_url/rest/v1/crypto_transactions?transaction_reference=eq.$reference&limit=1",
    $service_key
);

if (!$rows || count($rows) === 0) {
    @file_put_contents(
        $log_dir . '/kotani_webhooks.log',
        date('c') . " | UNKNOWN TRANSACTION REF: $reference\n",
        FILE_APPEND | LOCK_EX
    );
    http_response_code(200);
    echo json_encode(['ok' => true, 'message' => 'Crypto transaction not found — ignored']);
    exit;
}

$txn = $rows[0];

// Idempotency — a terminal transaction is never re-processed.
if (in_array($txn['status'], ['COMPLETED', 'FAILED', 'CANCELLED', 'REFUNDED'], true)) {
    echo json_encode(['ok' => true, 'message' => 'Transaction already in a terminal state']);
    exit;
}

$is_successful = in_array($status, ['completed', 'success', 'successful'], true);
$is_failed     = in_array($status, ['failed', 'expired', 'cancelled'], true);

if ($txn['type'] === 'BUY') {
    // Onramp completion = crypto delivered.
    if ($is_successful) {
        kotani_supabase_request(
            "$supabase_url/rest/v1/crypto_transactions?transaction_reference=eq.$reference",
            $service_key, 'PATCH',
            [
                'status' => 'COMPLETED',
                'settlement_reference' => $providerRef ?: null,
                'blockchain_tx_hash' => $txHash,
                'raw_webhook' => $event,
            ]
        );
    } elseif ($is_failed) {
        kotani_supabase_request(
            "$supabase_url/rest/v1/crypto_transactions?transaction_reference=eq.$reference",
            $service_key, 'PATCH',
            ['status' => 'FAILED', 'failure_reason' => 'Kotani onramp reported: ' . $status, 'raw_webhook' => $event]
        );
    }
} else {
    // SELL: offramp deposit-confirmed -> trigger fiat disbursement; offramp payout-confirmed -> COMPLETED.
    if ($depositAddr && empty($txn['wallet_address'])) {
        kotani_supabase_request(
            "$supabase_url/rest/v1/crypto_transactions?transaction_reference=eq.$reference",
            $service_key, 'PATCH',
            ['wallet_address' => $depositAddr, 'settlement_reference' => $providerRef ?: null]
        );
    }

    if ($is_successful) {
        $settlementService = new CryptoSettlementService();
        $settlement = $settlementService->completeSell(array_merge($txn, ['settlement_reference' => $providerRef ?: ($txn['settlement_reference'] ?? null)]));

        kotani_supabase_request(
            "$supabase_url/rest/v1/crypto_transactions?transaction_reference=eq.$reference",
            $service_key, 'PATCH',
            [
                'status' => $settlement['status'] ?? 'SETTLEMENT_PENDING',
                'settlement_note' => $settlement['message'] ?? null,
                'blockchain_tx_hash' => $txHash,
                'raw_webhook' => $event,
            ]
        );
    } elseif ($is_failed) {
        kotani_supabase_request(
            "$supabase_url/rest/v1/crypto_transactions?transaction_reference=eq.$reference",
            $service_key, 'PATCH',
            ['status' => 'FAILED', 'failure_reason' => 'Kotani offramp reported: ' . $status, 'raw_webhook' => $event]
        );
    }
}

@file_put_contents(
    $log_dir . '/kotani_webhooks.log',
    date('c') . " | PROCESSED | Ref: $reference | status=$status\n",
    FILE_APPEND | LOCK_EX
);

http_response_code(200);
echo json_encode(['ok' => true]);
exit;

function kotani_supabase_request($url, $service_key, $method = 'GET', $data = null)
{
    $ctx = stream_context_create([
        'http' => [
            'method'  => $method,
            'header'  => implode("\r\n", [
                "apikey: $service_key",
                "Authorization: Bearer $service_key",
                "Content-Type: application/json",
                "Prefer: return=representation",
            ]),
            'content' => $data ? json_encode($data) : null,
            'timeout' => 10,
        ],
    ]);
    $resp = @file_get_contents($url, false, $ctx);
    return $resp ? json_decode($resp, true) : null;
}
