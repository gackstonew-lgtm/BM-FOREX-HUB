<?php
/**
 * /api/admin/indicator-management.php
 * Admin CRUD API for indicator subscriber management.
 * Requires active admin session (sb_admin_required).
 *
 * Actions (POST body or GET param ?action=):
 *   list            GET  — List all indicator subscriptions (paginated, searchable)
 *   grant           POST — Grant access for a subscription
 *   revoke          POST — Revoke access for a subscription
 *   update_tv       POST — Update TradingView username + verification status
 *   analytics       GET  — Subscription analytics (counts + revenue)
 *   export_csv      GET  — Export subscriber list as CSV
 *
 * All actions are logged to indicator_admin_log.
 */

if (!function_exists('sb_admin_required')) {
    if (file_exists(__DIR__ . '/../../admin/config.php')) {
        require_once __DIR__ . '/../../admin/config.php';
    } elseif (file_exists(__DIR__ . '/../config.php')) {
        require_once __DIR__ . '/../config.php';
    } elseif (file_exists(__DIR__ . '/config.php')) {
        require_once __DIR__ . '/config.php';
    }
}

if (!defined('DB_PATH')) {
    if (file_exists(__DIR__ . '/../../engine_config.php')) {
        require_once __DIR__ . '/../../engine_config.php';
    } elseif (file_exists(__DIR__ . '/../engine_config.php')) {
        require_once __DIR__ . '/../engine_config.php';
    }
}

if (!class_exists('App\Services\IndicatorAccessService')) {
    if (file_exists(__DIR__ . '/../../app/Services/IndicatorAccessService.php')) {
        require_once __DIR__ . '/../../app/Services/IndicatorAccessService.php';
    } elseif (file_exists(__DIR__ . '/../app/Services/IndicatorAccessService.php')) {
        require_once __DIR__ . '/../app/Services/IndicatorAccessService.php';
    }
}

if (!class_exists('App\Services\TradingViewSyncService')) {
    if (file_exists(__DIR__ . '/../../app/Services/TradingViewSyncService.php')) {
        require_once __DIR__ . '/../../app/Services/TradingViewSyncService.php';
    } elseif (file_exists(__DIR__ . '/../app/Services/TradingViewSyncService.php')) {
        require_once __DIR__ . '/../app/Services/TradingViewSyncService.php';
    }
}

use App\Services\IndicatorAccessService;
use App\Services\TradingViewSyncService;

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// ── Admin Authorization (Supports PHP Admin Session OR Supabase Admin JWT) ──
$adminEmail = null;

if (!empty($_SESSION['admin_id']) && !empty($_SESSION['admin_token'])) {
    $adminEmail = $_SESSION['admin_user'] ?? 'admin';
} else {
    $token = '';
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['Authorization'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s+(.+)/i', $authHeader, $m)) {
        if (function_exists('apache_request_headers')) {
            foreach (apache_request_headers() as $k => $v) {
                if (strtolower($k) === 'authorization') { $authHeader = $v; break; }
            }
        }
        if (preg_match('/Bearer\s+(.+)/i', $authHeader, $m)) {
            $token = $m[1];
        }
    } else {
        $token = $m[1];
    }

    if (!empty($token)) {
        $anonKey = defined('SUPABASE_ANON') ? SUPABASE_ANON : (defined('SUPABASE_ANON_KEY') ? SUPABASE_ANON_KEY : '');
        $supaUrl = defined('SUPABASE_URL') ? SUPABASE_URL : 'https://iwoytmcxmbhmmbrbpzvf.supabase.co';
        $ctx = stream_context_create([
            'http' => [
                'header'  => "Authorization: Bearer $token\r\napikey: $anonKey",
                'timeout' => 5,
            ],
        ]);
        $resp = @file_get_contents("$supaUrl/auth/v1/user", false, $ctx);
        if ($resp) {
            $u = json_decode($resp, true);
            if (isset($u['email'])) {
                $email = strtolower(trim($u['email']));
                $permList = $GLOBALS['PERMANENT_ADMIN_ACCESS'] ?? ['bonfacewana3072@gmail.com', 'langatgift6@gmail.com', 'gackstoneb@gmail.com'];
                if (in_array($email, $permList, true) || ($u['user_metadata']['role'] ?? '') === 'admin' || ($u['app_metadata']['role'] ?? '') === 'admin') {
                    $adminEmail = $email;
                }
            }
        }
    }
}

if (!$adminEmail) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized. Administrator access required.']);
    exit;
}

$adminIp = $_SERVER['REMOTE_ADDR'] ?? null;

// ── Determine action ─────────────────────────────────────────────────
$action = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input  = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $input['action'] ?? ($_POST['action'] ?? '');
} else {
    $input  = [];
    $action = $_GET['action'] ?? 'list';
}

// CSRF protection for session-based write actions
if (in_array($action, ['grant', 'revoke', 'update_tv']) && !empty($_SESSION['admin_id'])) {
    $csrfToken = $input['csrf_token'] ?? ($_POST['csrf_token'] ?? '');
    $sessionToken = $_SESSION['admin_csrf'] ?? '';
    if (!empty($sessionToken) && !hash_equals($sessionToken, $csrfToken)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit;
    }
}

$accessService = new IndicatorAccessService();
$tvService     = new TradingViewSyncService();

switch ($action) {

    // ── List subscribers ─────────────────────────────────────────────
    case 'list':
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $perPage  = min(100, max(10, (int)($_GET['per_page'] ?? 25)));
        $search   = trim($_GET['search'] ?? '');
        $planFilter  = trim($_GET['plan'] ?? '');
        $statusFilter= trim($_GET['status'] ?? '');
        $offset   = ($page - 1) * $perPage;

        $query = "select=*&order=created_at.desc&limit=$perPage&offset=$offset";
        if ($search) {
            $s     = urlencode($search);
            $query .= "&or=(user_email.ilike.*$s*,user_name.ilike.*$s*,tradingview_username.ilike.*$s*)";
        }
        if ($planFilter)   $query .= "&plan_key=eq.$planFilter";
        if ($statusFilter) $query .= "&status=eq.$statusFilter";

        $url  = SUPABASE_URL . "/rest/v1/indicator_subscriptions?$query";
        $data = sb_api_get_raw($url);

        // Count total
        $countUrl  = SUPABASE_URL . '/rest/v1/indicator_subscriptions?select=id&' .
            ($planFilter   ? "plan_key=eq.$planFilter&"   : '') .
            ($statusFilter ? "status=eq.$statusFilter&" : '');
        $countData = sb_api_get_raw($countUrl, ['Prefer: count=exact']);
        $total     = (int)($countData['count'] ?? count($data));

        // Log action
        log_admin_action($adminEmail, $adminIp, null, null, 'view_subscriber_list',
            null, ['page' => $page, 'search' => $search]);

        echo json_encode([
            'data'      => $data,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'pages'     => max(1, (int)ceil($total / $perPage)),
        ]);
        break;

    // ── Grant access ─────────────────────────────────────────────────
    case 'grant':
        $subId = trim($input['subscription_id'] ?? '');
        if (!$subId) { http_response_code(400); echo json_encode(['error' => 'subscription_id required']); exit; }

        $ok = $accessService->grantAccess($subId, $adminEmail);
        log_admin_action($adminEmail, $adminIp, null, null, 'grant_access', $subId, []);

        echo json_encode(['ok' => $ok, 'message' => 'Access granted successfully.']);
        break;

    // ── Revoke access ────────────────────────────────────────────────
    case 'revoke':
        $subId = trim($input['subscription_id'] ?? '');
        if (!$subId) { http_response_code(400); echo json_encode(['error' => 'subscription_id required']); exit; }

        $ok = $accessService->revokeAccess($subId, $adminEmail);
        log_admin_action($adminEmail, $adminIp, null, null, 'revoke_access', $subId, []);

        echo json_encode(['ok' => $ok, 'message' => 'Access revoked successfully.']);
        break;

    // ── Update TradingView username ──────────────────────────────────
    case 'update_tv':
        $subId      = trim($input['subscription_id'] ?? '');
        $tvUsername = trim($input['tradingview_username'] ?? '');
        $verified   = isset($input['verified']) ? (bool)$input['verified'] : false;

        if (!$subId) { http_response_code(400); echo json_encode(['error' => 'subscription_id required']); exit; }

        if ($tvUsername) {
            $result = $tvService->updateUsername($subId, $tvUsername);
            if (!$result['ok']) {
                http_response_code(400);
                echo json_encode($result);
                break;
            }
        }
        if ($verified || !$tvUsername) {
            $tvService->markVerified($subId, $verified);
        }

        log_admin_action($adminEmail, $adminIp, null, null, 'update_tv_username', $subId, [
            'username' => $tvUsername, 'verified' => $verified
        ]);

        echo json_encode(['ok' => true, 'message' => 'TradingView username updated.']);
        break;

    // ── Analytics ────────────────────────────────────────────────────
    case 'analytics':
        $supaUrl = SUPABASE_URL;
        $svcKey  = SUPABASE_SERVICE;

        // Active subscriptions
        $active = sb_api_get_raw("$supaUrl/rest/v1/indicator_subscriptions?status=eq.active&select=id,plan_key,amount_paid_usd,amount_paid_kes");
        $expired= sb_api_get_raw("$supaUrl/rest/v1/indicator_subscriptions?status=eq.expired&select=id,plan_key");
        $all    = sb_api_get_raw("$supaUrl/rest/v1/indicator_subscriptions?select=id,plan_key,status,amount_paid_usd,amount_paid_kes");

        $byPlan     = [];
        $totalRevUsd= 0;
        $totalRevKes= 0;
        foreach ($all as $row) {
            $pk = $row['plan_key'];
            if (!isset($byPlan[$pk])) $byPlan[$pk] = ['active' => 0, 'expired' => 0, 'revenue_usd' => 0];
            if ($row['status'] === 'active') {
                $byPlan[$pk]['active']++;
                $totalRevUsd += (float)($row['amount_paid_usd'] ?? 0);
                $totalRevKes += (float)($row['amount_paid_kes'] ?? 0);
            } elseif ($row['status'] === 'expired') {
                $byPlan[$pk]['expired']++;
            }
            $byPlan[$pk]['revenue_usd'] += (float)($row['amount_paid_usd'] ?? 0);
        }

        echo json_encode([
            'total'              => count($all),
            'active'             => count($active),
            'expired'            => count($expired),
            'total_revenue_usd'  => round($totalRevUsd, 2),
            'total_revenue_kes'  => round($totalRevKes, 2),
            'by_plan'            => $byPlan,
        ]);
        break;

    // ── Export CSV ───────────────────────────────────────────────────
    case 'export_csv':
        $all = sb_api_get_raw(SUPABASE_URL . '/rest/v1/indicator_subscriptions?select=*&order=created_at.desc&limit=5000');

        log_admin_action($adminEmail, $adminIp, null, null, 'export_csv', null, ['count' => count($all)]);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="indicator_subscribers_' . date('Ymd_His') . '.csv"');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID','User ID','Email','Name','Plan','Status','Indicator Access','Signals','AI','TV Username','TV Verified','Payment Ref','Amount USD','Amount KES','Started','Expires','Admin Notes','Created']);

        foreach ($all as $row) {
            fputcsv($out, [
                $row['id']                    ?? '',
                $row['user_id']               ?? '',
                $row['user_email']            ?? '',
                $row['user_name']             ?? '',
                $row['plan_key']              ?? '',
                $row['status']               ?? '',
                $row['indicator_access']      ? 'Yes' : 'No',
                $row['signals_access']        ? 'Yes' : 'No',
                $row['ai_access']             ? 'Yes' : 'No',
                $row['tradingview_username']  ?? '',
                $row['tv_username_verified']  ? 'Yes' : 'No',
                $row['payment_reference']     ?? '',
                $row['amount_paid_usd']       ?? '',
                $row['amount_paid_kes']       ?? '',
                $row['started_at']            ?? '',
                $row['expires_at']            ?? '',
                $row['admin_notes']           ?? '',
                $row['created_at']            ?? '',
            ]);
        }
        fclose($out);
        exit;

    default:
        http_response_code(400);
        echo json_encode(['error' => "Unknown action: $action"]);
        break;
}

// ── Helpers ──────────────────────────────────────────────────────────

function sb_api_get_raw(string $url, array $extraHeaders = []): array
{
    $headers = array_merge([
        'apikey: ' . SUPABASE_SERVICE,
        'Authorization: Bearer ' . SUPABASE_SERVICE,
        'Content-Type: application/json',
    ], $extraHeaders);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_HEADER         => true,
    ]);
    $raw      = curl_exec($ch);
    $hdrSize  = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $body = substr($raw, $hdrSize);
    $data = json_decode($body, true);
    return is_array($data) ? $data : [];
}

function log_admin_action(
    string  $adminEmail,
    ?string $adminIp,
    ?string $targetUserId,
    ?string $targetEmail,
    string  $action,
    ?string $subscriptionId,
    array   $details
): void {
    $data = [
        'admin_email'     => $adminEmail,
        'admin_ip'        => $adminIp,
        'target_user_id'  => $targetUserId,
        'target_email'    => $targetEmail,
        'action'          => $action,
        'subscription_id' => $subscriptionId,
        'details'         => $details,
        'created_at'      => date('c'),
    ];

    // Supabase
    $ch = curl_init(SUPABASE_URL . '/rest/v1/indicator_admin_log');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_HTTPHEADER     => [
            'apikey: ' . SUPABASE_SERVICE,
            'Authorization: Bearer ' . SUPABASE_SERVICE,
            'Content-Type: application/json',
            'Prefer: return=minimal',
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT    => 5,
    ]);
    curl_exec($ch);
    curl_close($ch);

    // SQLite fallback
    try {
        if (function_exists('getMarketPDO')) {
            $pdo  = getMarketPDO();
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO indicator_admin_log
                (id, admin_email, admin_ip, target_user_id, target_email, action, subscription_id, details, created_at)
                VALUES (:id,:ae,:ip,:tu,:te,:ac,:si,:det,:ca)");
            $stmt->execute([
                ':id'  => bin2hex(random_bytes(16)),
                ':ae'  => $adminEmail,
                ':ip'  => $adminIp,
                ':tu'  => $targetUserId,
                ':te'  => $targetEmail,
                ':ac'  => $action,
                ':si'  => $subscriptionId,
                ':det' => json_encode($details),
                ':ca'  => date('Y-m-d H:i:s'),
            ]);
        }
    } catch (\Throwable $e) {}
}
