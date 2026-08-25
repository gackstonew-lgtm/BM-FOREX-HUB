<?php
/**
 * POST /api/kora-webhook.php
 * Receives webhook events from Kora Pay.
 *
 * Verifies HMAC signature, processes payment outcomes,
 * updates Supabase, and dispatches confirmation/failure emails.
 */

// Ensure logs directory exists
$log_dir = __DIR__ . '/../logs';
if (!is_dir($log_dir)) {
    @mkdir($log_dir, 0755, true);
}

// Log raw webhook payload
$raw_body = file_get_contents('php://input');
$log_entry = date('c') . ' | ' . $raw_body . "\n";
@file_put_contents($log_dir . '/kora_webhooks.log', $log_entry, FILE_APPEND | LOCK_EX);

header('Content-Type: application/json');

require_once __DIR__ . '/kora-config.php';

use App\Services\KoraPayService;

// ── Parse webhook ──────────────────────────────────────────────────
$event = json_decode($raw_body, true);
if (!$event) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}

// ── Verify HMAC signature ──────────────────────────────────────────
$sig_header = $_SERVER['HTTP_X_KORAPAY_SIGNATURE'] 
    ?? $_SERVER['HTTP_X_FINGO_SIGNATURE'] 
    ?? $_SERVER['X_KORAPAY_SIGNATURE'] 
    ?? '';

$koraService = new KoraPayService();
if (!$koraService->verifyWebhookSignature($sig_header, $raw_body)) {
    $log_err = date('c') . " | SIGNATURE VERIFICATION FAILED | Header: $sig_header\n";
    @file_put_contents($log_dir . '/kora_webhooks.log', $log_err, FILE_APPEND | LOCK_EX);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid webhook signature']);
    exit;
}

// ── Extract event & transaction data ──────────────────────────────
$eventType = $event['event'] ?? ($event['type'] ?? '');
$data      = $event['data']  ?? [];

$merchant_txn_id = $data['reference'] ?? ($data['merchantTransactionId'] ?? '');
$status          = strtolower($data['status'] ?? '');
$amount          = $data['amount'] ?? 0;
$processor_ref   = $data['kora_reference'] ?? ($data['processorReference'] ?? ($data['id'] ?? ''));
$message         = $data['processor_response'] ?? ($data['message'] ?? 'Transaction completed');

if (!$merchant_txn_id) {
    http_response_code(200);
    echo json_encode(['ok' => true, 'message' => 'No transaction reference found — event ignored']);
    exit;
}

// ── Crypto Buy/Sell transactions are handled separately and never
//    touch the `payments` / `subscriptions` tables used below. ─────
if (strpos($merchant_txn_id, 'CRYPTO_') === 0) {
    handle_crypto_webhook($merchant_txn_id, $eventType, $status, $processor_ref, $message, $event, $log_dir);
    exit;
}

// ── Find matching payment record in Supabase ──────────────────────
$supabase_url = SUPABASE_URL;
$service_key  = SUPABASE_SERVICE_KEY;

$payments = supabase_request(
    "$supabase_url/rest/v1/payments?merchant_txn_id=eq.$merchant_txn_id&limit=1",
    $service_key
);

if (!$payments || count($payments) === 0) {
    $log_warn = date('c') . " | UNKNOWN TRANSACTION REF: $merchant_txn_id | event=$eventType\n";
    @file_put_contents($log_dir . '/kora_webhooks.log', $log_warn, FILE_APPEND | LOCK_EX);
    http_response_code(200);
    echo json_encode(['ok' => true, 'message' => 'Payment record not found — ignored']);
    exit;
}

$payment = $payments[0];

// ── Prevent duplicate processing (Idempotency) ─────────────────────
if ($payment['status'] === 'succeeded' || $payment['status'] === 'failed') {
    http_response_code(200);
    echo json_encode(['ok' => true, 'message' => 'Payment already processed']);
    exit;
}

// ── Process by status or event type ───────────────────────────────
$is_successful = ($eventType === 'charge.success' || $eventType === 'transaction.succeeded' || $status === 'success' || $status === 'succeeded');
$is_failed     = ($eventType === 'charge.failed'  || $eventType === 'transaction.failed'    || $status === 'failed'  || $status === 'expired');

$update = [
    'processor_ref' => $processor_ref,
    'raw_webhook'   => $event,
];

if ($is_successful) {
    $update['status'] = 'succeeded';

    // Update payment record
    supabase_request(
        "$supabase_url/rest/v1/payments?merchant_txn_id=eq.$merchant_txn_id",
        $service_key,
        'PATCH',
        $update
    );

    // Create active subscription
    $plan        = $payment['plan'];
    $user_id     = $payment['user_id'];
    
    $plan_config = $KORA_PLANS[$plan] ?? null;
    $duration    = $plan_config['duration_days'] ?? 30;

    $sub_data = [
        'user_id'    => $user_id,
        'plan'       => $plan,
        'starts_at'  => date('c'),
        'expires_at' => date('c', strtotime("+{$duration} days")),
        'payment_id' => $payment['id'],
        'status'     => 'active',
    ];

    supabase_request(
        "$supabase_url/rest/v1/subscriptions",
        $service_key,
        'POST',
        $sub_data
    );

    // Send confirmation email
    send_payment_email($payment, 'success', $processor_ref);

    $log_ok = date('c') . " | SUCCEEDED | Ref: $merchant_txn_id | Plan: $plan | KoraRef: $processor_ref\n";
    @file_put_contents($log_dir . '/kora_webhooks.log', $log_ok, FILE_APPEND | LOCK_EX);

} elseif ($is_failed) {
    $update['status'] = 'failed';
    $update['failure_reason'] = $message;

    supabase_request(
        "$supabase_url/rest/v1/payments?merchant_txn_id=eq.$merchant_txn_id",
        $service_key,
        'PATCH',
        $update
    );

    // Send failure email
    send_payment_email($payment, 'failed', $message);

    $log_fail = date('c') . " | FAILED | Ref: $merchant_txn_id | Reason: $message\n";
    @file_put_contents($log_dir . '/kora_webhooks.log', $log_fail, FILE_APPEND | LOCK_EX);

} else {
    // Other intermediate status (pending, processing)
    $log_info = date('c') . " | INTERMEDIATE EVENT: $eventType | Ref: $merchant_txn_id\n";
    @file_put_contents($log_dir . '/kora_webhooks.log', $log_info, FILE_APPEND | LOCK_EX);
}

http_response_code(200);
echo json_encode(['ok' => true]);
exit;


// ══════════════════════════════════════════════════════════════════
// Email Notification Helpers
// ══════════════════════════════════════════════════════════════════

function send_payment_email($payment, $type, $detail) {
    $supabase_url = SUPABASE_URL;
    $service_key  = SUPABASE_SERVICE_KEY;

    $user_resp = supabase_request(
        "$supabase_url/rest/v1/profiles?id=eq.{$payment['user_id']}&select=username",
        $service_key
    );

    $users = supabase_request(
        "$supabase_url/auth/v1/admin/users/{$payment['user_id']}",
        $service_key
    );

    $email    = $users['email'] ?? null;
    $username = $user_resp[0]['username'] ?? ($users['user_metadata']['first_name'] ?? 'Trader');

    if (!$email) return;

    $plan_config = $GLOBALS['KORA_PLANS'][$payment['plan']] ?? ['name' => $payment['plan']];
    $plan_name   = $plan_config['name'];
    $amount      = number_format($payment['amount_kes'] ?? 0);
    $usd_label   = isset($plan_config['amount_usd']) ? ' ($' . number_format($plan_config['amount_usd']) . ' USD)' : '';
    $amount_disp = 'KES ' . $amount . $usd_label;
    $date        = date('M d, Y H:i');
    $ref         = $payment['merchant_txn_id'];
    $duration_days = $plan_config['duration_days'] ?? 30;
    $is_lifetime   = $duration_days >= 3650;
    $access_line   = $is_lifetime
        ? "Your <strong style='color:#E8E6DE'>$plan_name</strong> access does not expire."
        : "Your subscription is active until <strong style='color:#E8E6DE'>" . date('M d, Y', strtotime("+{$duration_days} days")) . "</strong>.";

    if ($type === 'success') {
        $subject = "Payment Confirmed — {$plan_name} | BM FOREX HUB";
        $body = "
        <div style='font-family:Inter,Helvetica,sans-serif;max-width:560px;margin:0 auto;background:#0B0F14;color:#FFFFFF;padding:40px 30px;border-radius:14px;border:1px solid #283548'>
            <div style='text-align:center;margin-bottom:30px'>
                <img src='https://bmforexhub.exchange/BM-ForexHub-Logo-Circle.png' width='60' style='border-radius:50%' alt='BM Forex Hub'>
                <h2 style='color:#1677FF;margin:12px 0 4px;font-size:18px'>Payment Confirmed</h2>
            </div>
            <p style='color:#B8C3D1;font-size:14px;line-height:1.7'>Hi <strong style='color:#FFFFFF'>$username</strong>,</p>
            <p style='color:#B8C3D1;font-size:14px;line-height:1.7'>Your payment has been successfully processed. Your <strong style='color:#1677FF'>$plan_name</strong> subscription is now active.</p>
            <div style='background:#151D29;border:1px solid #283548;border-radius:10px;padding:20px;margin:24px 0'>
                <p style='margin:0 0 8px;font-size:13px;color:#7F8B99'>PLAN</p>
                <p style='margin:0 0 16px;font-size:15px;color:#FFFFFF;font-weight:600'>$plan_name</p>
                <p style='margin:0 0 8px;font-size:13px;color:#7F8B99'>AMOUNT PAID</p>
                <p style='margin:0 0 16px;font-size:15px;color:#FFFFFF;font-weight:600'>$amount_disp</p>
                <p style='margin:0 0 8px;font-size:13px;color:#7F8B99'>TRANSACTION REFERENCE</p>
                <p style='margin:0 0 16px;font-size:15px;color:#FFFFFF;font-weight:600;font-family:monospace'>$ref</p>
                <p style='margin:0 0 8px;font-size:13px;color:#7F8B99'>PAYMENT GATEWAY REFERENCE</p>
                <p style='margin:0;font-size:15px;color:#FFFFFF;font-weight:600;font-family:monospace'>$detail</p>
            </div>
            <p style='color:#B8C3D1;font-size:13px;line-height:1.7'>$access_line If you have any questions, reply to this email or contact us on WhatsApp.</p>
            <div style='text-align:center;margin-top:30px;padding-top:20px;border-top:1px solid #283548'>
                <p style='color:#7F8B99;font-size:12px;margin:0'>BM FOREX HUB LTD | Nakuru, Kenya</p>
                <p style='color:#7F8B99;font-size:12px;margin:4px 0 0'>support@bmforexhub.exchange | +254 780 618 608</p>
            </div>
        </div>";
    } else {
        $subject = "Payment Failed — BM FOREX HUB";
        $body = "
        <div style='font-family:Inter,Helvetica,sans-serif;max-width:560px;margin:0 auto;background:#0B0F14;color:#FFFFFF;padding:40px 30px;border-radius:14px;border:1px solid #283548'>
            <div style='text-align:center;margin-bottom:30px'>
                <img src='https://bmforexhub.exchange/BM-ForexHub-Logo-Circle.png' width='60' style='border-radius:50%' alt='BM Forex Hub'>
                <h2 style='color:#F6465D;margin:12px 0 4px;font-size:18px'>Payment Failed</h2>
            </div>
            <p style='color:#B8C3D1;font-size:14px;line-height:1.7'>Hi <strong style='color:#FFFFFF'>$username</strong>,</p>
            <p style='color:#B8C3D1;font-size:14px;line-height:1.7'>Your payment for <strong style='color:#1677FF'>$plan_name</strong> (KES $amount) could not be processed.</p>
            <div style='background:#151D29;border:1px solid #283548;border-radius:10px;padding:20px;margin:24px 0'>
                <p style='margin:0 0 8px;font-size:13px;color:#7F8B99'>REASON</p>
                <p style='margin:0;font-size:15px;color:#F6465D'>$detail</p>
            </div>
            <p style='color:#B8C3D1;font-size:14px;line-height:1.7'>Please try again from your dashboard. If the issue persists, contact us on WhatsApp.</p>
            <div style='text-align:center;margin-top:30px;padding-top:20px;border-top:1px solid #283548'>
                <p style='color:#7F8B99;font-size:12px;margin:0'>BM FOREX HUB LTD | Nakuru, Kenya</p>
                <p style='color:#7F8B99;font-size:12px;margin:4px 0 0'>support@bmforexhub.exchange | +254 780 618 608</p>
            </div>
        </div>";
    }

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: BM FOREX HUB <support@bmforexhub.exchange>\r\n";
    $headers .= "Reply-To: support@bmforexhub.exchange\r\n";

    @mail($email, $subject, $body, $headers);
}

/**
 * Handles Kora webhook events for CRYPTO_BUY_* references.
 * (CRYPTO_SELL_* never has a Kora fiat charge, so nothing to do here.)
 * Marks fiat payment confirmed, then hands off to the settlement
 * adapter — which, until a real crypto settlement provider is wired
 * in, leaves the transaction at SETTLEMENT_PENDING rather than ever
 * faking delivery.
 */
function handle_crypto_webhook($merchant_txn_id, $eventType, $status, $processor_ref, $message, $event, $log_dir) {
    require_once __DIR__ . '/../admin/config.php';
    require_once __DIR__ . '/../app/Services/KoraCryptoCapabilityService.php';
    require_once __DIR__ . '/../app/Services/CryptoSettlementService.php';

    if (!defined('SUPABASE_ANON_KEY'))    define('SUPABASE_ANON_KEY',    SUPABASE_ANON);
    if (!defined('SUPABASE_SERVICE_KEY')) define('SUPABASE_SERVICE_KEY', SUPABASE_SERVICE);

    $supabase_url = SUPABASE_URL;
    $service_key  = SUPABASE_SERVICE_KEY;

    $rows = supabase_request(
        "$supabase_url/rest/v1/crypto_transactions?transaction_reference=eq.$merchant_txn_id&limit=1",
        $service_key
    );

    if (!$rows || count($rows) === 0) {
        $log = date('c') . " | CRYPTO UNKNOWN TXN: $merchant_txn_id | event=$eventType\n";
        @file_put_contents($log_dir . '/kora_webhooks.log', $log, FILE_APPEND | LOCK_EX);
        echo json_encode(['ok' => true, 'message' => 'Crypto transaction not found — ignored']);
        return;
    }

    $txn = $rows[0];

    // Idempotency — never re-process a terminal-state transaction.
    if (in_array($txn['status'], ['COMPLETED', 'FAILED', 'CANCELLED', 'REFUNDED', 'SETTLEMENT_PENDING'], true)) {
        echo json_encode(['ok' => true, 'message' => 'Crypto transaction already processed']);
        return;
    }

    $is_successful = ($eventType === 'charge.success' || $eventType === 'transaction.succeeded' || $status === 'success' || $status === 'succeeded');
    $is_failed     = ($eventType === 'charge.failed'  || $eventType === 'transaction.failed'    || $status === 'failed'  || $status === 'expired');

    if ($is_successful) {
        supabase_request(
            "$supabase_url/rest/v1/crypto_transactions?transaction_reference=eq.$merchant_txn_id",
            $service_key, 'PATCH',
            ['status' => 'PAYMENT_CONFIRMED', 'kora_reference' => $processor_ref, 'raw_webhook' => $event]
        );

        // Hand off to the settlement adapter. Never mark COMPLETED here directly —
        // only settleBuy() (once genuinely configured) may do that.
        $settlementService = new \App\Services\CryptoSettlementService();
        $settlement = $settlementService->settleBuy($txn);

        supabase_request(
            "$supabase_url/rest/v1/crypto_transactions?transaction_reference=eq.$merchant_txn_id",
            $service_key, 'PATCH',
            [
                'status'              => $settlement['status'] ?? 'SETTLEMENT_PENDING',
                'settlement_note'     => $settlement['message'] ?? null,
                'settlement_reference'=> $settlement['settlement_reference'] ?? null,
            ]
        );

        $log = date('c') . " | CRYPTO PAYMENT CONFIRMED | Ref: $merchant_txn_id | KoraRef: $processor_ref | settlement=" . ($settlement['status'] ?? 'SETTLEMENT_PENDING') . "\n";
        @file_put_contents($log_dir . '/kora_webhooks.log', $log, FILE_APPEND | LOCK_EX);

    } elseif ($is_failed) {
        supabase_request(
            "$supabase_url/rest/v1/crypto_transactions?transaction_reference=eq.$merchant_txn_id",
            $service_key, 'PATCH',
            ['status' => 'FAILED', 'failure_reason' => $message, 'raw_webhook' => $event]
        );
        $log = date('c') . " | CRYPTO PAYMENT FAILED | Ref: $merchant_txn_id | Reason: $message\n";
        @file_put_contents($log_dir . '/kora_webhooks.log', $log, FILE_APPEND | LOCK_EX);
    } else {
        $log = date('c') . " | CRYPTO INTERMEDIATE EVENT: $eventType | Ref: $merchant_txn_id\n";
        @file_put_contents($log_dir . '/kora_webhooks.log', $log, FILE_APPEND | LOCK_EX);
    }

    echo json_encode(['ok' => true]);
}

function supabase_request($url, $service_key, $method = 'GET', $data = null) {
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
