<?php
/**
 * POST /api/crypto-sell.php
 * Body: { "quote_token": "..." }
 *
 * SELL means the user wants to give BM Forex Hub crypto and receive
 * fiat back. That requires a genuine crypto settlement/payout
 * mechanism, which is NOT assumed to exist yet (see
 * app/Services/CryptoSettlementService.php). This endpoint therefore:
 *   1. Re-validates the quote server-side (never trusts frontend numbers)
 *   2. Creates a tracked transaction
 *   3. Asks CryptoSettlementService to initiate the sell
 *   4. Tells the user honestly what happens next — it never claims
 *      the fiat has been paid out unless settlement actually confirms it.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }

require_once __DIR__ . '/crypto-config.php';

use App\Services\CryptoQuoteService;
use App\Services\CryptoSettlementService;

$user = crypto_get_user_from_token();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized — please log in']);
    exit;
}

if (!crypto_rate_limit('sell_' . $user['id'], 10)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many attempts — please wait a moment']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$quoteToken = $input['quote_token'] ?? '';
if (!$quoteToken) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing quote']);
    exit;
}

$quoteService = new CryptoQuoteService();
$verified = $quoteService->verifyQuote($quoteToken);
if (!$verified['ok']) {
    http_response_code(400);
    echo json_encode(['error' => $verified['error']]);
    exit;
}
$quote = $verified['quote'];

if ($quote['side'] !== 'SELL') {
    http_response_code(400);
    echo json_encode(['error' => 'This quote is not a SELL quote']);
    exit;
}

$enabledConfig = crypto_get_enabled_config();
$assetCfg = $enabledConfig['assets'][$quote['crypto']] ?? null;
if (!$assetCfg || empty($assetCfg['sell_enabled'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Selling this asset is not currently available']);
    exit;
}

$existing = crypto_supabase_request(
    SUPABASE_URL . "/rest/v1/crypto_transactions?quote_hash=eq.{$quote['nonce']}&limit=1"
);
if (is_array($existing) && count($existing) > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'This quote has already been submitted']);
    exit;
}

$reference = 'CRYPTO_SELL_' . strtoupper(bin2hex(random_bytes(8)));

$txnData = [
    'user_id'               => $user['id'],
    'transaction_reference' => $reference,
    'type'                  => 'SELL',
    'fiat_currency'         => $quote['fiat'],
    'fiat_amount'           => $quote['fiat_amount'],
    'crypto_symbol'         => $quote['crypto'],
    'crypto_amount'         => $quote['crypto_amount'],
    'quoted_rate'           => $quote['rate'],
    'fee_amount'            => $quote['fee_amount'],
    'fee_currency'          => $quote['fiat'],
    'status'                => 'SETTLEMENT_PENDING',
    'quote_hash'            => $quote['nonce'],
    'quote_expires_at'      => date('c', $quote['expires_at']),
];

$inserted = crypto_supabase_request(SUPABASE_URL . '/rest/v1/crypto_transactions', 'POST', $txnData);
if (!$inserted) {
    http_response_code(500);
    @error_log('BMFH crypto-sell insert failed: ' . json_encode($txnData));
    echo json_encode(['error' => 'Failed to create transaction record']);
    exit;
}

$settlementService = new CryptoSettlementService();
$settlement = $settlementService->initiateSell($txnData);

crypto_supabase_request(
    SUPABASE_URL . "/rest/v1/crypto_transactions?transaction_reference=eq.$reference",
    'PATCH',
    [
        'settlement_note'      => $settlement['message'] ?? null,
        'settlement_reference' => $settlement['settlement_reference'] ?? null,
        'wallet_address'       => $settlement['deposit_address'] ?? null,
        'network'              => $settlement['network'] ?? null,
    ]
);

echo json_encode([
    'ok'              => true,
    'reference'       => $reference,
    'status'          => $settlement['status'] ?? 'SETTLEMENT_PENDING',
    'message'         => $settlement['message'] ?? 'Your sell request has been recorded and is pending settlement.',
    // When Kotani Pay is configured, this is the address the customer
    // must send their crypto to in order to complete the sell.
    'deposit_address' => $settlement['deposit_address'] ?? null,
    'network'         => $settlement['network'] ?? null,
]);
