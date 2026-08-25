<?php
/**
 * GET /api/trading-signals.php
 * Returns computed signal panel data for the trading dashboard.
 *
 * Server-side only. The Pine Script source is NEVER exposed.
 * Signal computation is performed by IndicatorAnalyticsService.
 *
 * Headers: Authorization: Bearer <supabase_jwt>
 * Query params: symbol=XAUUSD&tf=H1
 *
 * Response 200: { ...signal_data, watchlist: [...], sessions: {...} }
 * Response 401: { "error": "Unauthorized" }
 * Response 403: { "error": "No active indicator subscription" }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/kora-config.php';
require_once __DIR__ . '/../app/Services/IndicatorAccessService.php';
require_once __DIR__ . '/../app/Services/IndicatorAnalyticsService.php';

use App\Services\IndicatorAccessService;
use App\Services\IndicatorAnalyticsService;

// ── Verify JWT ───────────────────────────────────────────────────────
$user = get_user_from_token();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId    = $user['id'];
$userEmail = $user['email'] ?? '';

// ── Verify indicator access ──────────────────────────────────────────
$accessService = new IndicatorAccessService();
$access = $accessService->checkAccess($userId, $userEmail);

if (!$access['has_access']) {
    http_response_code(403);
    echo json_encode(['error' => 'No active indicator subscription', 'redirect' => '/indicator-subscribe.php']);
    exit;
}

// ── Parse query params ───────────────────────────────────────────────
$allowed_symbols    = ['XAUUSD','EURUSD','GBPUSD','USDJPY','BTCUSD','NAS100','US30','USDCHF','AUDUSD','NZDUSD','USDCAD'];
$allowed_timeframes = ['1','5','15','30','60','240','D','W'];

$rawSymbol    = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $_GET['symbol'] ?? 'XAUUSD'));
$rawTimeframe = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $_GET['tf']     ?? '60'));

$symbol    = in_array($rawSymbol,    $allowed_symbols,    true) ? $rawSymbol    : 'XAUUSD';
$timeframe = in_array($rawTimeframe, $allowed_timeframes, true) ? $rawTimeframe : '60';

// ── Get signal data ──────────────────────────────────────────────────
$analytics = new IndicatorAnalyticsService();

$signalData = $analytics->getSignalData($symbol, $timeframe);

// Watchlist symbols
$watchlistSymbols = ['XAUUSD', 'EURUSD', 'GBPUSD', 'USDJPY', 'BTCUSD', 'NAS100', 'US30'];
$watchlist  = $analytics->getWatchlistData($watchlistSymbols);

// Session data
$sessions   = $analytics->getSessionData();

// ── Build response ───────────────────────────────────────────────────
// IMPORTANT: Only signal outputs are returned — never the algorithm source
$response = array_merge($signalData, [
    'watchlist' => $watchlist,
    'sessions'  => $sessions,
    'access' => [
        'plan_key'          => $access['plan_key'],
        'plan_name'         => $access['plan_name'],
        'signals_access'    => $access['signals_access'],
        'ai_access'         => $access['ai_access'],
        'premium_dashboard' => $access['premium_dashboard'],
        'expires_at'        => $access['expires_at'],
        'days_remaining'    => $access['days_remaining'],
    ],
    'cache_ttl' => 30, // refresh every 30 seconds
]);

// Add cache headers — short TTL for real-time feel
header('Cache-Control: private, max-age=30');
header('X-BM-Signal-Engine: v1');

echo json_encode($response);
exit;


// ── JWT Helper ───────────────────────────────────────────────────────
function get_user_from_token(): ?array
{
    $header = $_SERVER['HTTP_AUTHORIZATION']
           ?? $_SERVER['Authorization']
           ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
           ?? '';

    if (!preg_match('/Bearer\s+(.+)/i', $header, $m)) {
        if (function_exists('apache_request_headers')) {
            foreach (apache_request_headers() as $k => $v) {
                if (strtolower($k) === 'authorization') { $header = $v; break; }
            }
        }
        if (!preg_match('/Bearer\s+(.+)/i', $header, $m)) return null;
    }

    $token = $m[1];
    $ctx   = stream_context_create([
        'http' => [
            'header'  => "Authorization: Bearer $token\r\napikey: " . SUPABASE_ANON_KEY,
            'timeout' => 5,
        ],
    ]);
    $resp = @file_get_contents(SUPABASE_URL . '/auth/v1/user', false, $ctx);
    if (!$resp) return null;
    $data = json_decode($resp, true);
    return isset($data['id']) ? $data : null;
}
