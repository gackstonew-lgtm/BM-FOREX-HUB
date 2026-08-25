<?php
/**
 * POST /api/crypto-buy.php
 * Body: { "quote_token": "...", "payment_method": "mpesa"|"card", "phone": "...", ...card fields }
 *
 * Flow: re-validate quote server-side -> create PENDING_PAYMENT
 * crypto_transactions row -> initialize Kora fiat payment (reusing
 * the existing KoraPaymentService, exactly like api/deposit.php does
 * for subscriptions) -> return checkout info.
 *
 * The Kora webhook (api/kora-webhook.php) is what ultimately marks the
 * transaction PAYMENT_CONFIRMED once Kora verifies the payment
 * server-side — never based on anything the frontend reports back.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }

require_once __DIR__ . '/crypto-config.php';
require_once __DIR__ . '/../app/Services/KotaniCryptoCapabilityService.php';

use App\Services\CryptoQuoteService;
use App\Services\KoraPaymentService;
use App\Services\KotaniCryptoCapabilityService;

$user = crypto_get_user_from_token();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized — please log in']);
    exit;
}

if (!crypto_rate_limit('buy_' . $user['id'], 10)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many purchase attempts — please wait a moment']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { http_response_code(400); echo json_encode(['error' => 'Invalid JSON']); exit; }

$quoteToken = $input['quote_token'] ?? '';
$method     = strtolower($input['payment_method'] ?? 'mpesa');
$phone      = $input['phone'] ?? '';

if (!$quoteToken) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing quote']);
    exit;
}

// ── Server-side quote re-validation (never trust frontend numbers) ──
$quoteService = new CryptoQuoteService();
$verified = $quoteService->verifyQuote($quoteToken);
if (!$verified['ok']) {
    http_response_code(400);
    echo json_encode(['error' => $verified['error']]);
    exit;
}
$quote = $verified['quote'];

if ($quote['side'] !== 'BUY') {
    http_response_code(400);
    echo json_encode(['error' => 'This quote is not a BUY quote']);
    exit;
}

// Re-check enabled state (an admin may have disabled the asset since the quote was issued)
$enabledConfig = crypto_get_enabled_config();
$assetCfg = $enabledConfig['assets'][$quote['crypto']] ?? null;
if (!$assetCfg || empty($assetCfg['buy_enabled'])) {
    http_response_code(400);
    echo json_encode(['error' => 'This asset is no longer available for purchase']);
    exit;
}

if ($method === 'mpesa') {
    $phone = normalize_phone_crypto($phone);
    if (!$phone) {
        http_response_code(400);
        echo json_encode(['error' => 'Please enter a valid M-Pesa phone number']);
        exit;
    }
} elseif ($method === 'card') {
    $cleanCardNum = preg_replace('/\D/', '', $input['card_number'] ?? '');
    if (empty($cleanCardNum) || strlen($cleanCardNum) < 13 || strlen($cleanCardNum) > 19) {
        http_response_code(400);
        echo json_encode(['error' => 'Please enter a valid credit or debit card number']);
        exit;
    }
    if (empty($input['card_expiry']) || empty($input['card_cvv'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Please enter card expiry date and CVV']);
        exit;
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payment method']);
    exit;
}

// ── Destination wallet address (required once Kotani Pay settlement
//    is actually configured — crypto has to be delivered somewhere).
//    When Kotani isn't configured yet, this is optional and the
//    transaction simply stays at SETTLEMENT_PENDING as before, so
//    nothing breaks for merchants still finishing setup. ──────────
$destinationAddress = trim($input['destination_address'] ?? '');
$network = trim($input['network'] ?? '');

$kotaniCapability = new KotaniCryptoCapabilityService();
if ($kotaniCapability->supports('crypto_withdrawal') && $destinationAddress === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Please provide the wallet address you want your ' . $quote['crypto'] . ' delivered to']);
    exit;
}
if ($destinationAddress !== '' && !preg_match('/^[a-zA-Z0-9]{20,100}$/', $destinationAddress)) {
    http_response_code(400);
    echo json_encode(['error' => 'That wallet address does not look valid']);
    exit;
}

// ── Idempotency: block a duplicate submit of the same quote nonce ──
$existing = crypto_supabase_request(
    SUPABASE_URL . "/rest/v1/crypto_transactions?quote_hash=eq.{$quote['nonce']}&limit=1"
);
if (is_array($existing) && count($existing) > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'This quote has already been submitted']);
    exit;
}

$reference = 'CRYPTO_BUY_' . strtoupper(bin2hex(random_bytes(8)));

$txnData = [
    'user_id'               => $user['id'],
    'transaction_reference' => $reference,
    'type'                  => 'BUY',
    'fiat_currency'         => $quote['fiat'],
    'fiat_amount'           => $quote['fiat_amount'],
    'crypto_symbol'         => $quote['crypto'],
    'crypto_amount'         => $quote['crypto_amount'],
    'quoted_rate'           => $quote['rate'],
    'fee_amount'            => $quote['fee_amount'],
    'fee_currency'          => $quote['fiat'],
    'status'                => 'PENDING_PAYMENT',
    'payment_method'        => $method,
    'quote_hash'            => $quote['nonce'],
    'quote_expires_at'      => date('c', $quote['expires_at']),
    'destination_address'   => $destinationAddress ?: null,
    'network'               => $network ?: null,
];

$inserted = crypto_supabase_request(SUPABASE_URL . '/rest/v1/crypto_transactions', 'POST', $txnData);
if (!$inserted) {
    http_response_code(500);
    @error_log('BMFH crypto-buy insert failed: ' . json_encode($txnData));
    echo json_encode(['error' => 'Failed to create transaction record']);
    exit;
}

// ── Initialize fiat payment via the EXISTING Kora integration ──────
$koraService = new KoraPaymentService();

$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443 ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'bmforexhub.exchange';
$webhook_url  = getenv('KORA_WEBHOOK_URL') ?: "$scheme://$host/api/kora-webhook.php";
// Clean redirect URL (Kora appends ?status=...). The reference travels
// as a URL fragment so it survives Kora's query-param append.
$redirect_url = "$scheme://$host/crypto.php#crypto_status=success&ref=" . urlencode($reference);

$chargeData = [
    'amount'           => $quote['fiat_amount'],
    'currency'         => $quote['fiat'],
    'reference'        => $reference,
    'description'      => 'Buy ' . round($quote['crypto_amount'], 6) . ' ' . $quote['crypto'] . ' — BM FOREX HUB',
    'notification_url' => $webhook_url,
    'redirect_url'     => $redirect_url,
    'customer_name'    => $user['user_metadata']['username'] ?? 'BM Forex Trader',
    'customer_email'   => $user['email'] ?? 'trader@bmforexhub.exchange',
    'phone'            => $phone,
    'payment_method'   => $method,
    'card_name'        => $input['card_name']   ?? '',
    'card_number'      => $input['card_number'] ?? '',
    'card_expiry'      => $input['card_expiry'] ?? '',
    'card_cvv'         => $input['card_cvv']    ?? '',
];

$koraResult = $koraService->initializePayment($chargeData);

if (!$koraResult['success']) {
    crypto_supabase_request(
        SUPABASE_URL . "/rest/v1/crypto_transactions?transaction_reference=eq.$reference",
        'PATCH',
        ['status' => 'FAILED', 'failure_reason' => $koraResult['message'] ?: 'Payment initialization failed']
    );
    http_response_code(400);
    echo json_encode(['error' => $koraResult['message'] ?: 'Payment request failed']);
    exit;
}

crypto_supabase_request(
    SUPABASE_URL . "/rest/v1/crypto_transactions?transaction_reference=eq.$reference",
    'PATCH',
    ['status' => 'PAYMENT_PROCESSING']
);

$resp = [
    'ok'          => true,
    'reference'   => $reference,
    'message'     => $koraResult['message'] ?: 'Redirecting to Kora Pay...',
];
if (!empty($koraResult['checkout_url'])) {
    $resp['checkout_url'] = $koraResult['checkout_url'];
}
echo json_encode($resp);
exit;

function normalize_phone_crypto($phone)
{
    $phone = preg_replace('/[\s\-()]/', '', trim($phone));
    if (preg_match('/^0(7|1)\d{8}$/', $phone)) return '+254' . substr($phone, 1);
    if (preg_match('/^254(7|1)\d{8}$/', $phone)) return '+' . $phone;
    if (preg_match('/^\+254(7|1)\d{8}$/', $phone)) return $phone;
    return null;
}
