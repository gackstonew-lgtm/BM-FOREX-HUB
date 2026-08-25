<?php
/**
 * GET /api/indicator-access.php
 * JWT-authenticated endpoint: returns the calling user's indicator access status.
 *
 * Headers: Authorization: Bearer <supabase_jwt>
 *
 * Response 200:
 *  {
 *    "has_access": bool,
 *    "plan_key": string|null,
 *    "plan_name": string|null,
 *    "signals_access": bool,
 *    "ai_access": bool,
 *    "premium_dashboard": bool,
 *    "expires_at": string|null,
 *    "days_remaining": int,
 *    "status": string,
 *    "tradingview_username": string|null,
 *    "subscription_id": string|null
 *  }
 *
 * Response 401: { "error": "Unauthorized" }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// Rate limiting: max 60 requests per minute per IP
$rateKey = 'ind_access_' . ($_SERVER['REMOTE_ADDR'] ?? 'cli');
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION[$rateKey])) $_SESSION[$rateKey] = ['count' => 0, 'window' => time()];
if (time() - $_SESSION[$rateKey]['window'] > 60) {
    $_SESSION[$rateKey] = ['count' => 0, 'window' => time()];
}
$_SESSION[$rateKey]['count']++;
if ($_SESSION[$rateKey]['count'] > 60) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests']);
    exit;
}

require_once __DIR__ . '/kora-config.php';
require_once __DIR__ . '/../app/Services/IndicatorAccessService.php';

// Verify JWT
$user = get_user_from_token();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId    = $user['id'];
$userEmail = $user['email'] ?? '';

// Expire stale subscriptions first
$accessService = new \App\Services\IndicatorAccessService();
$accessService->expireOldSubscriptions();

// Check access
$access = $accessService->checkAccess($userId, $userEmail);

$permList = $GLOBALS['PERMANENT_ADMIN_ACCESS'] ?? ['bonfacewana3072@gmail.com', 'langatgift6@gmail.com', 'gackstoneb@gmail.com'];
$isAdmin = in_array(strtolower(trim($userEmail)), $permList, true) || ($user['user_metadata']['role'] ?? '') === 'admin' || ($user['app_metadata']['role'] ?? '') === 'admin';
$access['is_admin'] = $isAdmin;

// Log dashboard visit (only if has access, to avoid spamming log)
if ($access['has_access']) {
    $accessService->logAction(
        $access['subscription_id'],
        $userId,
        $userEmail,
        'dashboard_visited',
        $access['plan_key'],
        ['timeframe' => 'check', 'ip' => $_SERVER['REMOTE_ADDR'] ?? null]
    );
}

echo json_encode($access);
exit;


// ── Helper: Validate Supabase JWT ────────────────────────────────────────────
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
    $user = json_decode($resp, true);
    return isset($user['id']) ? $user : null;
}
