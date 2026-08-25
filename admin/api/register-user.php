<?php
error_reporting(0);
ini_set('display_errors', '0');
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

function send_json($data, $code = 200) {
    while (ob_get_level()) ob_end_clean();
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

register_shutdown_function(function() {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        send_json(['error' => 'Server error: ' . trim($e['message'])], 500);
    }
});

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { while (ob_get_level()) ob_end_clean(); http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { send_json(['error' => 'Method not allowed'], 405); }

define('SUPABASE_URL',     'https://iwoytmcxmbhmmbrbpzvf.supabase.co');
define('SUPABASE_SERVICE', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml3b3l0bWN4bWJobW1icmJwenZmIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc4Mzk4MzU2MSwiZXhwIjoyMDk5NTU5NTYxfQ.1ExI-o8zwDkUIH_loQPC_ZJhJaDgrB6gSf1KgAdigCU');

require_once __DIR__ . '/../../app/Services/OtpService.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) { send_json(['error' => 'Invalid JSON'], 400); }

$email    = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';
$metadata = $input['metadata'] ?? [];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json(['error' => 'A valid email is required.'], 400);
}
if (empty($password) || strlen($password) < 8) {
    send_json(['error' => 'Password must be at least 8 characters.'], 400);
}

$supabase_url = SUPABASE_URL;
$service_key  = SUPABASE_SERVICE;

// Check if user already exists
$checkCtx = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "apikey: $service_key\r\nAuthorization: Bearer $service_key",
        'timeout' => 10,
        'ignore_errors' => true,
    ],
]);
$checkResp = @file_get_contents("$supabase_url/auth/v1/admin/users?email=$email", false, $checkCtx);
if ($checkResp) {
    $checkData = json_decode($checkResp, true);
    $existingUsers = $checkData['users'] ?? $checkData ?? [];
    foreach ($existingUsers as $u) {
        if (strtolower($u['email'] ?? '') === $email) {
            $otpResult = OtpService::generateAndSend($email, 'signup');
            send_json([
                'success' => true,
                'user' => ['id' => $u['id'], 'email' => $email],
                'otp_sent' => !empty($otpResult['success']),
                'message' => 'Account already exists. A new verification code has been sent.'
            ]);
        }
    }
}

// Create user via Admin API
$createPayload = json_encode([
    'email' => $email,
    'password' => $password,
    'email_confirm' => false,
    'user_metadata' => $metadata,
]);

$createCtx = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "apikey: $service_key\r\nAuthorization: Bearer $service_key\r\nContent-Type: application/json",
        'content' => $createPayload,
        'timeout' => 15,
        'ignore_errors' => true,
    ],
]);

$createResp = @file_get_contents("$supabase_url/auth/v1/admin/users", false, $createCtx);

$httpCode = 0;
if (isset($http_response_header)) {
    foreach ($http_response_header as $header) {
        if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $header, $m)) {
            $httpCode = (int)$m[1];
        }
    }
}

if (!$createResp || $httpCode >= 400) {
    error_log("BMFH register-user create failed: HTTP $httpCode | resp=" . substr($createResp ?: '', 0, 500));
    send_json(['error' => 'Failed to create account. Please try again.'], 500);
}

$created = json_decode($createResp, true);
$userId = $created['id'] ?? null;

if (!$userId) {
    send_json(['error' => 'Account creation failed. Please try again.'], 500);
}

// Send OTP via Resend
$otpResult = OtpService::generateAndSend($email, 'signup');

send_json([
    'success' => true,
    'user' => ['id' => $userId, 'email' => $email],
    'otp_sent' => !empty($otpResult['success']),
    'message' => 'Account created! A verification code has been sent to your email.'
]);
