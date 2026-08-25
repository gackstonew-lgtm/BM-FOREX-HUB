<?php
/**
 * GET /api/subscription-status.php
 * Returns the current user's active subscription(s).
 *
 * Headers: Authorization: Bearer <supabase_token>
 *
 * Returns:
 *   200 → { "subscriptions": [...], "plans": {...} }
 *   401 → { "error": "Unauthorized" }
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
require_once __DIR__ . '/kora-config.php';
require_once __DIR__ . '/../app/Services/MembershipService.php';

// ── Verify user ───────────────────────────────────────────────────
$user = get_user_from_token();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $user['id'];
$user_email = $user['email'] ?? '';
$user_username = $user['user_metadata']['username'] ?? (explode('@', $user_email)[0] ?? '');

// ── Check if user is suspended ──────────────────────────────────────
$isSuspended = false;
if (function_exists('sb_admin_get')) {
    $profRes = sb_admin_get('profiles', ['select' => 'status', 'id' => "eq.$user_id"]);
    if (!empty($profRes['data'][0]['status']) && $profRes['data'][0]['status'] === 'suspended') {
        $isSuspended = true;
    }
}
if (!$isSuspended && function_exists('getMarketPDO')) {
    try {
        $pdo = getMarketPDO();
        $stmt = $pdo->prepare("SELECT status FROM profiles WHERE id = :id OR LOWER(username) = :u LIMIT 1");
        $stmt->execute([':id' => $user_id, ':u' => strtolower($user_username)]);
        $row = $stmt->fetch();
        if (!empty($row['status']) && $row['status'] === 'suspended') {
            $isSuspended = true;
        }
    } catch (\Throwable $e) {}
}

if ($isSuspended) {
    echo json_encode([
        'subscriptions' => [],
        'payments'      => [],
        'plans'         => $FINGO_PLANS ?? [],
        'active_plans'  => [],
        'permanent_access' => false,
        'trial_active'  => false,
        'trial_days_left' => 0,
        'trial_hours_left' => 0,
        'trial_started' => null,
        'premium'       => false,
        'membership_status'   => 'Suspended',
        'subscription_status' => 'suspended',
        'subscription_plan'   => null,
        'subscription_expiry' => null,
        'days_remaining'      => 0
    ]);
    exit;
}

if (bm_has_permanent_access($user['email'] ?? null)) {
    echo json_encode([
        'subscriptions' => [[
            'id' => 'permanent-admin-access',
            'plan' => 'elite_elite',
            'plan_name' => 'BM Elites — Permanent Admin Access',
            'status' => 'active',
            'starts_at' => date('c', strtotime('-30 days')),
            'expires_at' => '2036-12-31T23:59:59+00:00',
            'amount_usd' => 10000,
            'amount_kes' => 1290000,
        ]],
        'payments' => [],
        'plans' => $FINGO_PLANS ?? [],
        'active_plans' => ['all', 'elite_elite', 'copytrading', 'grid_lifetime'],
        'permanent_access' => true,
        'trial_active'  => false,
        'trial_days_left' => 0,
        'trial_hours_left' => 0,
        'trial_started' => null,
        'premium' => true,
        'membership_status' => 'Premium Account',
        'subscription_status' => 'active',
        'subscription_plan' => 'BM Elites — Permanent Admin Access',
        'subscription_expiry' => '2036-12-31T23:59:59+00:00',
        'days_remaining' => 3650
    ]);
    exit;
}

$service = new \App\Services\MembershipService();

// Cleanup and get active subscription using ID, username, and email
$service->cleanupExpiredSubscriptions($user_id);
$activeSubs = $service->getActiveSubscriptions($user_id, $user_username, $user_email);
$activeSub = is_array($activeSubs) && count($activeSubs) > 0 ? $activeSubs[0] : null;

$premium = false;
$membership_status = 'Free Trial';
$subscription_status = null;
$subscription_plan = null;
$subscription_expiry = null;
$days_remaining = 0;
$active_plans = [];

if (count($activeSubs) > 0) {
    foreach ($activeSubs as $sub) {
        if (!empty($sub['plan'])) {
            $active_plans[] = $sub['plan'];
        }
    }
    // If premium is active, we don't start or evaluate a trial to avoid conflicting logic
    $trialStatus = [
        'trial_active'     => false,
        'trial_days_left'  => 0,
        'trial_hours_left' => 0,
        'trial_started'    => null,
    ];
    $premium = true;
    $membership_status = count($activeSubs) > 1 ? 'Multiple Active Plans' : 'Premium Account';
    $subscription_status = $activeSub['status'];
    $subscription_plan = count($activeSubs) > 1 ? 'Multiple Plans' : ($activeSub['plan_name'] ?? $activeSub['plan'] ?? null);
    $subscription_expiry = $activeSub['expires_at'];
    $days_remaining = max(0, ceil((strtotime($subscription_expiry) - time()) / 86400));
} else {
    $trialStatus = $service->getTrialStatus($user_id);
    if ($trialStatus['trial_active']) {
        $membership_status = 'Free Trial';
    } else {
        $membership_status = 'Subscription Required';
    }
}

// ── Get payment history ───────────────────────────────────────────
$payments = supabase_request(
    SUPABASE_URL . "/rest/v1/payments?user_id=eq.$user_id&order=created_at.desc&limit=10",
    SUPABASE_SERVICE_KEY
);
if (empty($payments) && function_exists('getMarketPDO')) {
    try {
        $pdo = getMarketPDO();
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE user_id = :uid OR LOWER(username) = :u ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([':uid' => $user_id, ':u' => strtolower($user_username)]);
        $payments = $stmt->fetchAll() ?: [];
    } catch (\Throwable $e) {}
}

echo json_encode(array_merge([
    'subscriptions' => $activeSubs,
    'payments'      => $payments ?? [],
    'plans'         => $FINGO_PLANS,
    'active_plans'  => $active_plans,
    'premium'       => $premium,
    'membership_status'   => $membership_status,
    'subscription_status' => $subscription_status,
    'subscription_plan'   => $subscription_plan,
    'subscription_expiry' => $subscription_expiry,
    'days_remaining'      => $days_remaining
], $trialStatus));
exit;


function get_user_from_token() {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] 
        ?? $_SERVER['Authorization'] 
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] 
        ?? '';
    if (!preg_match('/Bearer\s+(.+)/i', $auth_header, $m)) {
        // Apache fastcgi fallback
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        foreach ($headers as $k => $v) {
            if (strtolower($k) === 'authorization') { $auth_header = $v; break; }
        }
        if (!preg_match('/Bearer\s+(.+)/i', $auth_header, $m)) return null;
    }
    $token = $m[1];

    $ctx = stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer $token\r\napikey: " . SUPABASE_ANON_KEY,
            'timeout' => 5,
        ],
    ]);
    $resp = @file_get_contents(SUPABASE_URL . '/auth/v1/user', false, $ctx);
    if (!$resp) return null;
    $user = json_decode($resp, true);
    return isset($user['id']) ? $user : null;
}

function supabase_request($url, $service_key, $method = 'GET', $data = null) {
    $ctx = stream_context_create([
        'http' => [
            'method'  => $method,
            'header'  => implode("\r\n", [
                "apikey: $service_key",
                "Authorization: Bearer $service_key",
                "Content-Type: application/json",
            ]),
            'content' => $data ? json_encode($data) : null,
            'timeout' => 10,
        ],
    ]);
    $resp = @file_get_contents($url, false, $ctx);
    return $resp ? json_decode($resp, true) : null;
}
