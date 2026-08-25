<?php
/**
 * POST /api/crypto-quote.php
 * Body: { "side": "BUY"|"SELL", "fiat": "KES", "crypto": "USDT",
 *         "fiat_amount": 13000 }              // for BUY
 *    or { "side": "SELL", "crypto_amount": 100 }  // for SELL
 *
 * Returns a signed, short-lived quote. The frontend must treat every
 * number in the response as display-only — the ONLY thing it sends
 * back at purchase time is the opaque `token`.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }

require_once __DIR__ . '/crypto-config.php';

use App\Services\CryptoQuoteService;

$user = crypto_get_user_from_token();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized — please log in']);
    exit;
}

if (!crypto_rate_limit('quote_' . $user['id'], 30)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many quote requests — please slow down']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { http_response_code(400); echo json_encode(['error' => 'Invalid JSON']); exit; }

$side        = strtoupper($input['side'] ?? '');
$fiat        = strtoupper($input['fiat'] ?? 'KES');
$crypto      = strtoupper($input['crypto'] ?? '');
$fiatAmount  = isset($input['fiat_amount']) ? (float) $input['fiat_amount'] : null;
$cryptoAmount = isset($input['crypto_amount']) ? (float) $input['crypto_amount'] : null;

if (!in_array($side, ['BUY', 'SELL'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid side']);
    exit;
}

// ── Enforce the admin-editable enabled list — never trust the frontend's asset choice blindly ──
$enabledConfig = crypto_get_enabled_config();

if (!isset($enabledConfig['fiat'][$fiat]) || !$enabledConfig['fiat'][$fiat]['enabled']) {
    http_response_code(400);
    echo json_encode(['error' => "$fiat is not currently supported"]);
    exit;
}
if (!isset($enabledConfig['assets'][$crypto])) {
    http_response_code(400);
    echo json_encode(['error' => "$crypto is not currently supported"]);
    exit;
}
$assetCfg = $enabledConfig['assets'][$crypto];
if ($side === 'BUY' && empty($assetCfg['buy_enabled'])) {
    http_response_code(400);
    echo json_encode(['error' => "Buying $crypto is not currently available"]);
    exit;
}
if ($side === 'SELL' && empty($assetCfg['sell_enabled'])) {
    http_response_code(400);
    echo json_encode(['error' => "Selling $crypto is not currently available"]);
    exit;
}

$quoteService = new CryptoQuoteService();
$result = $quoteService->buildQuote($side, $fiat, $crypto, $fiatAmount, $cryptoAmount);

if (!$result['ok']) {
    http_response_code(400);
    echo json_encode(['error' => $result['error']]);
    exit;
}

echo json_encode(['ok' => true, 'quote' => $result['quote']]);
