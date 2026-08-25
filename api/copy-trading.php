<?php
/**
 * REST API for Copy Trading User Submissions
 * GET  -> Fetch current user's Copy Trading account details
 * POST -> Create or update MT5 Copy Trading account credentials
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../admin/config.php';
require_once __DIR__ . '/../app/Services/CopyTradingService.php';
require_once __DIR__ . '/../app/Services/MailService.php';

use App\Services\CopyTradingService;

function get_user_from_token() {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] 
        ?? $_SERVER['Authorization'] 
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] 
        ?? '';
    if (!preg_match('/Bearer\s+(.+)/i', $auth_header, $m)) {
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        foreach ($headers as $k => $v) {
            if (strtolower($k) === 'authorization') { $auth_header = $v; break; }
        }
        if (!preg_match('/Bearer\s+(.+)/i', $auth_header, $m)) return null;
    }
    $token = $m[1];

    $ctx = stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer $token\r\napikey: " . SUPABASE_ANON,
            'timeout' => 5,
        ],
    ]);
    $resp = @file_get_contents(SUPABASE_URL . '/auth/v1/user', false, $ctx);
    if (!$resp) return null;
    $user = json_decode($resp, true);
    return isset($user['id']) ? $user : null;
}

$user = get_user_from_token();
if (!$user) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId    = $user['id'];
$userEmail = $user['email'] ?? '';
$userMeta  = $user['user_metadata'] ?? [];
$fullName  = trim(($userMeta['first_name'] ?? '') . ' ' . ($userMeta['last_name'] ?? ''));
if (empty($fullName)) {
    $fullName = $userMeta['username'] ?? explode('@', $userEmail)[0] ?? 'Trader';
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $record = CopyTradingService::getCopyTraderByUserId($userId);
    if ($record) {
        // Obfuscate password for user fetch
        unset($record['mt5_password']);
        $record['mt5_password_set'] = true;
    }
    echo json_encode(['ok' => true, 'data' => $record]);
    exit;
}

if ($method === 'POST') {
    // 1. Verify Active Subscription
    $hasSub = CopyTradingService::hasActiveSubscription($userId, $userEmail);
    if (!$hasSub) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'An active Copy Trading subscription is required to submit MT5 credentials.']);
        exit;
    }

    // 2. Parse Payload
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? [];

    $brokerName = trim($input['broker_name'] ?? '');
    $mt5Login   = trim($input['mt5_login'] ?? '');
    $mt5Pass    = trim($input['mt5_password'] ?? '');
    $mt5Server  = trim($input['mt5_server'] ?? '');
    $notes      = trim($input['notes'] ?? '');

    if (empty($brokerName) || empty($mt5Login) || empty($mt5Pass) || empty($mt5Server)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Broker Name, MT5 Login Number, Password, and Server are required.']);
        exit;
    }

    if (!is_numeric($mt5Login)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'MT5 Login Account Number must be numeric.']);
        exit;
    }

    $payload = [
        'user_id'           => $userId,
        'full_name'         => $fullName,
        'email'             => $userEmail,
        'subscription_plan' => 'copytrading',
        'broker_name'       => $brokerName,
        'mt5_login'         => $mt5Login,
        'mt5_password'      => $mt5Pass,
        'mt5_server'        => $mt5Server,
        'notes'             => $notes
    ];

    $saved = CopyTradingService::saveCopyTrader($payload);

    // 3. User & Admin Notifications
    $userMsg = "Your Copy Trading account has been successfully submitted. Our trading team will begin managing your account after verification.";
    
    // In-app notification creation if table exists
    try {
        if (function_exists('sb_admin_post')) {
            sb_admin_post('notifications', [
                'user_id'    => $userId,
                'type'       => 'copy_trading_submitted',
                'payload'    => json_encode(['message' => $userMsg, 'broker' => $brokerName, 'login' => $mt5Login]),
                'read_flag'  => 0,
                'created_at' => date('c')
            ]);
        }
    } catch (\Throwable $e) {}

    // Send email notification to user
    try {
        if (class_exists('App\Services\MailService')) {
            $subject = "Copy Trading Account Received — BM Forex Hub";
            $emailBody = "<p>Hello <strong>" . htmlspecialchars($fullName) . "</strong>,</p>" .
                         "<p>" . htmlspecialchars($userMsg) . "</p>" .
                         "<table style='width:100%;border-collapse:collapse;margin:16px 0;background:#151D29;color:#fff;border-radius:8px;padding:12px;'>" .
                         "<tr><td style='padding:8px;'><strong>Broker:</strong></td><td style='padding:8px;'>" . htmlspecialchars($brokerName) . "</td></tr>" .
                         "<tr><td style='padding:8px;'><strong>MT5 Account:</strong></td><td style='padding:8px;'>" . htmlspecialchars($mt5Login) . "</td></tr>" .
                         "<tr><td style='padding:8px;'><strong>Server:</strong></td><td style='padding:8px;'>" . htmlspecialchars($mt5Server) . "</td></tr>" .
                         "</table>" .
                         "<p>If you have any questions, our support team is available to assist you.</p>";
            
            $mailService = new \App\Services\MailService();
            $mailService->send($userEmail, $subject, $emailBody);
        }
    } catch (\Throwable $e) {
        error_log("Failed to send copy trading submission email: " . $e->getMessage());
    }

    echo json_encode([
        'ok'      => true,
        'message' => $userMsg,
        'data'    => [
            'broker_name' => $saved['broker_name'],
            'mt5_login'   => $saved['mt5_login'],
            'mt5_server'  => $saved['mt5_server'],
            'status'      => $saved['status'],
            'created_at'  => $saved['created_at']
        ]
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
