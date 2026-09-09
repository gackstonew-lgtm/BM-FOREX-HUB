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

if (!class_exists('App\Services\CopyTradingService')) {
    if (file_exists(__DIR__ . '/../../app/Services/CopyTradingService.php')) {
        require_once __DIR__ . '/../../app/Services/CopyTradingService.php';
    } elseif (file_exists(__DIR__ . '/../app/Services/CopyTradingService.php')) {
        require_once __DIR__ . '/../app/Services/CopyTradingService.php';
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

        // ── List all Copy Traders ───────────────────────────────────
        case 'list_copy_traders':
            if (class_exists('App\Services\CopyTradingService')) {
                $traders = \App\Services\CopyTradingService::getAllCopyTraders();
                echo json_encode([
                    'success' => true,
                    'data'    => $traders,
                    'total'   => count($traders)
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'CopyTradingService unavailable.']);
            }
            exit;

        // ── Grant Copy Trading Access ────────────────────────────────
        case 'grant_copytrading':
            $username = trim($input['username'] ?? '');
            $durationDays = (int)($input['duration_days'] ?? ($input['duration'] ?? 36500));
            $notes = trim($input['notes'] ?? 'Copy Trading Integration Access');

            if (empty($username)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Target username or email is required.']);
                exit;
            }

            $mt5Payload = null;
            if (!empty($input['broker_name']) || !empty($input['mt5_login'])) {
                $mt5Payload = [
                    'broker_name'  => trim($input['broker_name'] ?? ''),
                    'mt5_login'    => trim($input['mt5_login'] ?? ''),
                    'mt5_password' => $input['mt5_password'] ?? '',
                    'mt5_server'   => trim($input['mt5_server'] ?? ''),
                    'notes'        => $notes,
                    'status'       => trim($input['status'] ?? 'Active')
                ];
            }

            $result = \App\Services\CopyTradingService::grantCopyTradingAccess($adminUser, $username, $durationDays, $notes, $mt5Payload);
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => $result['message'] ?? 'Failed to grant Copy Trading.']);
            }
            exit;

        // ── Update Copy Trader Details & Status ──────────────────────
        case 'update_copy_trader':
            $id = trim($input['id'] ?? '');
            if (empty($id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Copy Trader ID is required.']);
                exit;
            }

            $result = \App\Services\CopyTradingService::updateCopyTraderDetails($id, $input, $adminUser);
            echo json_encode($result);
            exit;

        // ── Reveal Copy Trader MT5 Password for Admin ────────────────
        case 'reveal_copy_trader_password':
            $id = trim($input['id'] ?? '');
            if (empty($id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Copy Trader ID is required.']);
                exit;
            }

            $result = \App\Services\CopyTradingService::revealPasswordForAdmin($id, $adminUser);
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => $result['message'] ?? 'Password not found.']);
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

            $result = $service->extendSubscription($id, $days, $adminUser);
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => $result['message'] ?? 'Failed to extend subscription.']);
            }
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
