<?php
/**
 * GET /api/crypto-prices.php
 * Public, read-only, cached live prices for the "Popular Crypto" row
 * and the asset selector. No authentication needed (it's market data,
 * not user data), but still rate-limited and server-cached.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }

require_once __DIR__ . '/crypto-config.php';

use App\Services\CryptoPriceService;

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!crypto_rate_limit('prices_' . $ip, 60)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests']);
    exit;
}

$enabledConfig = crypto_get_enabled_config();
$allSymbols = array_unique(array_merge(
    array_keys($enabledConfig['assets']),
    $CRYPTO_CONFIG['ticker_symbols'] ?? []
));

$priceService = new CryptoPriceService();
$prices = $priceService->getPrices($allSymbols);

$out = [];
foreach ($prices as $symbol => $p) {
    $out[] = [
        'symbol'         => $symbol,
        'usd'            => $p['usd'],
        'kes'            => $p['kes'],
        'usd_24h_change' => round($p['usd_24h_change'], 2),
    ];
}

echo json_encode([
    'ok'     => true,
    'prices' => $out,
    'assets' => array_values($enabledConfig['assets']),
    'fiat'   => array_values($enabledConfig['fiat']),
]);
