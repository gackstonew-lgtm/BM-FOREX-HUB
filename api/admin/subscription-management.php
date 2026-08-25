<?php
/**
 * /api/admin/subscription-management.php
 * Admin API for managing user subscriptions & BM Elites access.
 * Requires active admin session.
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
sb_admin_required();

if (!defined('DB_PATH')) {
    if (file_exists(__DIR__ . '/../../engine_config.php')) {
        require_once __DIR__ . '/../../engine_config.php';
    } elseif (file_exists(__DIR__ . '/../engine_config.php')) {
        require_once __DIR__ . '/../engine_config.php';
    }
}

if (!class_exists('App\Services\MembershipService')) {
    if (file_exists(__DIR__ . '/../../app/Services/MembershipService.php')) {
        require_once __DIR__ . '/../../app/Services/MembershipService.php';
    } elseif (file_exists(__DIR__ . '/../app/Services/MembershipService.php')) {
        require_once __DIR__ . '/../app/Services/MembershipService.php';
    }
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$adminUser = $_SESSION['admin_user'] ?? 'Admin';
$service = new \App\Services\MembershipService();

// Determine input method
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$input = [];

if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $decoded = json_decode($rawInput, true);
    if (is_array($decoded)) {
        $input = $decoded;
    } else {
        $input = $_POST;
    }
    $action = $input['action'] ?? '';
} else {
    $action = $_GET['action'] ?? 'list_elite';
    $input = $_GET;
}

try {
    switch ($action) {

        // ── List all Elite Subscriptions ─────────────────────────────
        case 'list_elite':
            $members = $service->getAllEliteMemberships();
            echo json_encode([
                'success' => true,
                'data'    => $members,
                'total'   => count($members)
            ]);
            exit;

        // ── Grant General Subscription ───────────────────────────────
        case 'grant_subscription':
            $username = trim($input['username'] ?? '');
            $plan = trim($input['plan'] ?? '');
            $durationDays = (int)($input['duration_days'] ?? ($input['duration'] ?? 30));
            $notes = trim($input['notes'] ?? '');
            $userId = trim($input['user_id'] ?? '') ?: null;

            if (empty($username)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Target username or email is required.']);
                exit;
            }
            if (empty($plan)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Subscription plan is required.']);
                exit;
            }

            $result = $service->grantSubscription($adminUser, $userId, $username, $plan, $durationDays, $notes, 'Admin Manual Grant');
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => $result['message'] ?? 'Failed to grant subscription.']);
            }
            exit;

        // ── Grant BM Elites Membership ──────────────────────────────
        case 'grant_elite':
            $username = trim($input['username'] ?? '');
            $plan = trim($input['plan'] ?? \App\Services\MembershipService::PLAN_ELITE_ELITE);
            $amount = (float)($input['amount'] ?? 10000);
            $durationDays = (int)($input['duration_days'] ?? ($input['duration'] ?? 30));
            $notes = trim($input['notes'] ?? 'Elite Trading Circle VIP Access');
            $userId = trim($input['user_id'] ?? '') ?: null;

            if (empty($username)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Target username or email is required.']);
                exit;
            }

            $result = $service->grantEliteMembership($adminUser, $username, $plan, $durationDays, $amount, $notes, $userId);
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => $result['message'] ?? 'Failed to grant Elite membership.']);
            }
            exit;

        // ── Revoke Subscription ──────────────────────────────────────
        case 'revoke_subscription':
            $id = trim($input['id'] ?? '');
            if (empty($id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Subscription ID is required.']);
                exit;
            }

            $result = $service->revokeEliteMembership($id, $adminUser);
            echo json_encode($result);
            exit;

        // ── Extend Subscription ──────────────────────────────────────
        case 'extend_subscription':
            $id = trim($input['id'] ?? '');
            $days = (int)($input['days'] ?? 30);
            if (empty($id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Subscription ID is required.']);
                exit;
            }

            $pdo = function_exists('getMarketPDO') ? getMarketPDO() : null;
            if (!$pdo) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Database connection unavailable.']);
                exit;
            }

            $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            $sub = $stmt->fetch();

            if (!$sub) {
                // Try finding in user_subscriptions
                $stmt2 = $pdo->prepare("SELECT * FROM user_subscriptions WHERE id = ? LIMIT 1");
                $stmt2->execute([$id]);
                $sub = $stmt2->fetch();
            }

            if (!$sub) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Subscription record not found.']);
                exit;
            }

            $curExpiresTs = strtotime($sub['expires_at'] ?? 'now');
            $baseTs = max(time(), $curExpiresTs);
            $newExpiresTs = strtotime("+$days days", $baseTs);
            $newExpiresIso = date('c', $newExpiresTs);
            $nowIso = date('c');

            $upStmt = $pdo->prepare("UPDATE subscriptions SET expires_at = ?, status = 'active', updated_at = ? WHERE id = ?");
            $upStmt->execute([$newExpiresIso, $nowIso, $id]);

            $upStmt2 = $pdo->prepare("UPDATE user_subscriptions SET expires_at = ?, status = 'active', updated_at = ? WHERE id = ?");
            $upStmt2->execute([$newExpiresIso, $nowIso, $id]);

            if (function_exists('sb_admin_post')) {
                sb_admin_post('subscriptions?id=eq.' . urlencode($id), ['expires_at' => $newExpiresIso, 'status' => 'active', 'updated_at' => $nowIso], 'PATCH');
                sb_admin_post('user_subscriptions?id=eq.' . urlencode($id), ['expires_at' => $newExpiresIso, 'status' => 'active', 'updated_at' => $nowIso], 'PATCH');
            }

            $service->logAdminAction($adminUser, 'EXTEND_SUBSCRIPTION', $sub['user_id'] ?? $sub['username'], $sub['plan'] ?? 'Subscription', $sub['status'] ?? 'active', 'active', "Extended by $days days (new expiry: " . date('Y-m-d H:i', $newExpiresTs) . ")");

            echo json_encode([
                'success' => true,
                'message' => "Subscription extended successfully by $days days. New expiry: " . date('Y-m-d H:i', $newExpiresTs),
                'new_expires_at' => $newExpiresIso
            ]);
            exit;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unknown action: ' . htmlspecialchars($action)]);
            exit;
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Server error processing subscription request: ' . $e->getMessage()
    ]);
    exit;
}
