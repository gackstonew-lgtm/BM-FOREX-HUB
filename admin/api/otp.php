<?php
/**
 * /admin/api/otp.php
 * Public OTP endpoint — generates a new verification code, enforces
 * hourly rate limits and dispatches it via the configured mail provider.
 *
 * Expected JSON body: { "action": "resend", "email": "...", "type": "signup|recovery|reset" }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . (isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*'));
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
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
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid action.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'A valid email address is required.']);
    exit;
}

if (!in_array($type, ['signup', 'recovery', 'reset'], true)) {
    $type = 'signup';
}

try {
    require_once __DIR__ . '/../../app/Services/MailService.php';
    require_once __DIR__ . '/../../app/Services/EmailTemplateService.php';
    require_once __DIR__ . '/../../app/Services/OtpService.php';

    $result = OtpService::generateAndSend($email, $type);

    if (!empty($result['success'])) {
        echo json_encode(['success' => true, 'message' => $result['message'] ?? 'A new verification code has been sent to your email.']);
    } else {
        echo json_encode(['success' => false, 'error' => $result['message'] ?? 'Unable to send OTP. Please try again later.']);
    }
} catch (\Throwable $e) {
    error_log('OTP endpoint error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Unable to send OTP. Please try again later.']);
}
