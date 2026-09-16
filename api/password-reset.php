<?php
/**
 * /api/password-reset.php
 * BM Forex Hub — Public Password Reset & Verification API
 */

error_reporting(0);
ini_set('display_errors', '0');
ob_start();

$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: $origin");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
header('Access-Control-Allow-Credentials: true');

function send_json($data, $code = 200) {
    while (ob_get_level()) ob_end_clean();
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

register_shutdown_function(function() {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        send_json(['success' => false, 'error' => 'Server error: ' . trim($e['message'])], 500);
    }
});

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    while (ob_get_level()) ob_end_clean();
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'error' => 'Method not allowed'], 405);
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    $input = $_POST;
}

$action     = strtolower(trim($input['action'] ?? ''));
$email      = strtolower(trim($input['email'] ?? ''));
$code       = trim($input['code'] ?? '');
$resetToken = trim($input['reset_token'] ?? '');
$password   = $input['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json(['success' => false, 'error' => 'A valid email is required.'], 400);
}

define('SUPABASE_URL',     'https://iwoytmcxmbhmmbrbpzvf.supabase.co');
define('SUPABASE_SERVICE', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml3b3l0bWN4bWJobW1icmJwenZmIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc4Mzk4MzU2MSwiZXhwIjoyMDk5NTU5NTYxfQ.1ExI-o8zwDkUIH_loQPC_ZJhJaDgrB6gSf1KgAdigCU');

require_once __DIR__ . '/../app/Services/OtpService.php';

// ── Helper: Supabase Admin API request ──────────────────────────
function supabase_admin_get($endpoint) {
    $ctx = stream_context_create(['http' => [
        'method' => 'GET',
        'header' => "apikey: " . SUPABASE_SERVICE . "\r\nAuthorization: Bearer " . SUPABASE_SERVICE,
        'timeout' => 10,
    ]]);
    return @file_get_contents(SUPABASE_URL . $endpoint, false, $ctx);
}

function supabase_admin_put($endpoint, $payload) {
    $ctx = stream_context_create(['http' => [
        'method' => 'PUT',
        'header' => "apikey: " . SUPABASE_SERVICE . "\r\nAuthorization: Bearer " . SUPABASE_SERVICE . "\r\nContent-Type: application/json",
        'content' => json_encode($payload),
        'timeout' => 10,
        'ignore_errors' => true,
    ]]);
    return @file_get_contents(SUPABASE_URL . $endpoint, false, $ctx);
}

function get_http_code() {
    if (!isset($http_response_header)) return 0;
    foreach ($http_response_header as $h) {
        if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $h, $m)) return (int)$m[1];
    }
    return 0;
}

function find_user_by_email($email) {
    $email = strtolower(trim($email));
    if (empty($email)) return null;

    // 1. PostgREST profiles lookup
    $ctx = stream_context_create(['http' => [
        'method' => 'GET',
        'header' => "apikey: " . SUPABASE_SERVICE . "\r\nAuthorization: Bearer " . SUPABASE_SERVICE,
        'timeout' => 10,
    ]]);
    $resp = @file_get_contents(SUPABASE_URL . "/rest/v1/profiles?email=ilike." . urlencode($email) . "&select=id,email", false, $ctx);
    if ($resp) {
        $profiles = json_decode($resp, true);
        if (is_array($profiles) && !empty($profiles)) {
            foreach ($profiles as $p) {
                if (strtolower($p['email'] ?? '') === $email) {
                    return $p['id'];
                }
            }
        }
    }

    // 2. GoTrue paginated admin users lookup
    $page = 1;
    $perPage = 1000;
    while (true) {
        $usersResp = @file_get_contents(SUPABASE_URL . "/auth/v1/admin/users?page=$page&per_page=$perPage", false, $ctx);
        if (!$usersResp) break;
        $data = json_decode($usersResp, true);
        $users = $data['users'] ?? (is_array($data) ? $data : []);
        if (empty($users) || !is_array($users)) break;

        foreach ($users as $u) {
            if (strtolower($u['email'] ?? '') === $email) {
                return $u['id'];
            }
        }

        if (count($users) < $perPage) break;
        $page++;
    }

    return null;
}

// ── VERIFY OTP ──────────────────────────────────────────────────
if ($action === 'verify') {
    if (empty($code) || strlen($code) !== 6) {
        send_json(['success' => false, 'error' => 'A valid 6-digit code is required.'], 400);
    }
    $otpType = trim($input['otp_type'] ?? 'recovery');
    if (!in_array($otpType, ['signup', 'recovery', 'reset'], true)) $otpType = 'recovery';
    $result = OtpService::verify($email, $code, $otpType);
    $httpCode = $result['http_code'] ?? ($result['success'] ? 200 : 400);
    if (empty($result['success'])) {
        $result['error'] = $result['message'] ?? 'Invalid verification code.';
    }
    send_json($result, $httpCode);
}

// ── UPDATE PASSWORD ─────────────────────────────────────────────
if ($action === 'update') {
    if (empty($password) || strlen($password) < 8) {
        send_json(['success' => false, 'error' => 'Password must be at least 8 characters long.'], 400);
    }

    // Authorize via reset_token or OTP code
    if (!empty($resetToken)) {
        $tokenResult = OtpService::verifyResetToken($email, $resetToken);
        if (empty($tokenResult['success'])) {
            $httpCode = $tokenResult['http_code'] ?? 401;
            send_json(['success' => false, 'error' => $tokenResult['message'] ?? 'Reset authorization is invalid or expired. Please request a new code.'], $httpCode);
        }
    } elseif (!empty($code) && strlen($code) === 6) {
        $otpResult = OtpService::verify($email, $code, 'recovery');
        if (empty($otpResult['success'])) {
            $httpCode = $otpResult['http_code'] ?? 400;
            send_json(['success' => false, 'error' => $otpResult['message'] ?? 'Invalid or expired code.'], $httpCode);
        }
    } else {
        send_json(['success' => false, 'error' => 'Reset authorization token or verification code is required.'], 400);
    }

    $userId = find_user_by_email($email);
    if (!$userId) {
        send_json(['success' => false, 'error' => 'No account found with that email.'], 404);
    }

    $updateResp = supabase_admin_put("/auth/v1/admin/users/$userId", ['password' => $password]);
    if ($updateResp === false || get_http_code() >= 400) {
        send_json(['success' => false, 'error' => 'Failed to update password. Please try again.'], 500);
    }

    // Invalidate reset token upon success
    OtpService::consumeResetToken($email);

    send_json(['success' => true, 'message' => 'Password updated successfully!']);
}

// ── CONFIRM EMAIL ───────────────────────────────────────────────
if ($action === 'confirm-email') {
    $userId = find_user_by_email($email);
    if (!$userId) {
        send_json(['success' => false, 'error' => 'No account found with that email.'], 404);
    }
    $updateResp = supabase_admin_put("/auth/v1/admin/users/$userId", ['email_confirm' => true]);
    if ($updateResp === false || get_http_code() >= 400) {
        send_json(['success' => false, 'error' => 'Failed to confirm email. Please try again.'], 500);
    }
    send_json(['success' => true, 'user' => ['id' => $userId, 'email' => $email]]);
}

send_json(['success' => false, 'error' => 'Invalid action. Use "verify", "update", or "confirm-email".'], 400);
