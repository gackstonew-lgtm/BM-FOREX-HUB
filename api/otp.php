<?php
/**
 * /api/otp.php
 * BM Forex Hub — Public OTP Endpoint
 * Generates a new verification code, enforces rate limits, and sends OTP.
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

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    while (ob_get_level()) ob_end_clean();
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'error' => 'Method not allowed.'], 405);
}

$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    $input = $_POST;
}

$action = $input['action'] ?? '';
$email  = strtolower(trim($input['email'] ?? ''));
$type   = strtolower(trim($input['type'] ?? 'signup'));

if ($action !== 'resend') {
    send_json(['success' => false, 'error' => 'Invalid action.'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json(['success' => false, 'error' => 'A valid email address is required.'], 400);
}

if (!in_array($type, ['signup', 'recovery', 'reset'], true)) {
    $type = 'signup';
}

try {
    require_once __DIR__ . '/../app/Services/MailService.php';
    require_once __DIR__ . '/../app/Services/EmailTemplateService.php';
    require_once __DIR__ . '/../app/Services/OtpService.php';

    $result = OtpService::generateAndSend($email, $type);

    if (!empty($result['success'])) {
        send_json(['success' => true, 'message' => $result['message'] ?? 'A new verification code has been sent to your email.'], 200);
    } else {
        $msg = $result['message'] ?? 'Unable to send OTP. Please try again later.';
        $code = (stripos($msg, 'limit') !== false) ? 429 : 400;
        send_json(['success' => false, 'error' => $msg], $code);
    }
} catch (\Throwable $e) {
    error_log('OTP endpoint error: ' . $e->getMessage());
    send_json(['success' => false, 'error' => 'Unable to send OTP. Please try again later.'], 500);
}
