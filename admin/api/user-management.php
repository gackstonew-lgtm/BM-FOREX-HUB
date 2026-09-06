<?php
error_reporting(0);
ini_set('display_errors', '0');
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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

require_once __DIR__ . '/../config.php';

// Verify Admin Session
if (empty($_SESSION['admin_id']) && empty($_SESSION['admin_user'])) {
    // Also check for Bearer service token if called from system service
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches) || $matches[1] !== SUPABASE_SERVICE) {
        send_json(['success' => false, 'error' => 'Unauthorized: Admin authentication required.'], 401);
    }
}

$adminUser = $_SESSION['admin_user'] ?? 'Admin';
$adminId   = $_SESSION['admin_id']   ?? 'admin-system';

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) { 
    send_json(['success' => false, 'error' => 'Invalid JSON request payload'], 400); 
}

$action = strtolower(trim($input['action'] ?? ''));
$targetUserId = trim($input['user_id'] ?? '');

if (empty($targetUserId)) {
    send_json(['success' => false, 'error' => 'Target User ID is required.'], 400);
}

// Supabase Admin REST & Auth API Helper
function supabase_admin_request($method, $endpoint, $payload = null) {
    $url = SUPABASE_URL . $endpoint;
    $ch = curl_init($url);
    $headers = [
        'apikey: ' . SUPABASE_SERVICE,
        'Authorization: Bearer ' . SUPABASE_SERVICE,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];

    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 15,
    ]);

    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return ['code' => 500, 'data' => null, 'error' => $err];
    }

    return [
        'code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// Log audit trail
function record_audit_log($adminId, $targetUserId, $actionType, $reason, $metadata = []) {
    $payload = [
        'admin_id'       => $adminId,
        'target_user_id' => $targetUserId,
        'action'         => $actionType,
        'reason'         => $reason,
        'metadata'       => json_encode($metadata),
        'created_at'     => date('c'),
    ];
    supabase_admin_request('POST', '/rest/v1/audit_logs', $payload);
}

// ── ACTION HANDLERS ──────────────────────────────────────────────

if ($action === 'suspend') {
    $reason = trim($input['reason'] ?? '');
    if (strlen($reason) < 5) {
        send_json(['success' => false, 'error' => 'A mandatory suspension reason of at least 5 characters is required.'], 400);
    }

    // 1. Suspend in Supabase Auth backend (set ban_duration to 100 years = 876000h)
    $authRes = supabase_admin_request('PUT', '/auth/v1/admin/users/' . urlencode($targetUserId), [
        'ban_duration' => '876000h',
        'user_metadata' => [
            'status' => 'suspended',
            'suspension_reason' => $reason
        ]
    ]);

    if ($authRes['code'] >= 400 && $authRes['code'] !== 404) {
        $msg = $authRes['data']['message'] ?? $authRes['data']['msg'] ?? 'Failed to update Supabase Auth backend.';
        send_json(['success' => false, 'error' => 'Supabase Auth Error: ' . $msg], $authRes['code']);
    }

    // 2. Update status in Supabase Database profiles table
    $dbRes = supabase_admin_request('PATCH', '/rest/v1/profiles?id=eq.' . urlencode($targetUserId), [
        'status'            => 'suspended',
        'suspended_at'      => date('c'),
        'suspension_reason' => $reason
    ]);

    // 3. Write Audit Log
    record_audit_log($adminId, $targetUserId, 'USER_SUSPENDED', $reason, [
        'admin_user' => $adminUser,
        'target_user_id' => $targetUserId
    ]);

    send_json([
        'success' => true,
        'message' => 'User account successfully suspended in Supabase Auth and database. Active sessions revoked.',
        'user_id' => $targetUserId,
        'status'  => 'suspended'
    ]);

} elseif ($action === 'reinstate') {
    // 1. Unban in Supabase Auth backend
    $authRes = supabase_admin_request('PUT', '/auth/v1/admin/users/' . urlencode($targetUserId), [
        'ban_duration' => 'none',
        'user_metadata' => [
            'status' => 'active'
        ]
    ]);

    // 2. Update status in Supabase Database profiles table
    $dbRes = supabase_admin_request('PATCH', '/rest/v1/profiles?id=eq.' . urlencode($targetUserId), [
        'status'            => 'active',
        'suspended_at'      => null,
        'suspension_reason' => null
    ]);

    // 3. Write Audit Log
    record_audit_log($adminId, $targetUserId, 'USER_REINSTATED', 'Account reinstated by admin', [
        'admin_user' => $adminUser
    ]);

    send_json([
        'success' => true,
        'message' => 'User account reinstated successfully.',
        'user_id' => $targetUserId,
        'status'  => 'active'
    ]);

} elseif ($action === 'delete') {
    $reason = trim($input['reason'] ?? '');
    $confirmation = trim($input['confirmation'] ?? '');
    $targetEmail  = trim($input['target_email'] ?? '');

    if (strlen($reason) < 5) {
        send_json(['success' => false, 'error' => 'A mandatory deletion reason of at least 5 characters is required.'], 400);
    }

    $isEmailMatch = (!empty($targetEmail) && strtolower($confirmation) === strtolower($targetEmail));
    $isDeleteMatch = ($confirmation === 'DELETE');

    if (!$isEmailMatch && !$isDeleteMatch) {
        send_json(['success' => false, 'error' => 'Confirmation failed. Please type the target user email or "DELETE" to confirm.'], 400);
    }

    // 1. Delete user from Supabase Authentication backend (purges auth.users entry)
    $authRes = supabase_admin_request('DELETE', '/auth/v1/admin/users/' . urlencode($targetUserId));

    // 2. GDPR Soft-Delete & PII Anonymization in database profiles table (retains financial ledger references)
    $anonEmail = 'anon_' . substr($targetUserId, 0, 8) . '_' . time() . '@anonymized.local';
    $anonUsername = 'DeletedUser_' . substr($targetUserId, 0, 6);

    $dbRes = supabase_admin_request('PATCH', '/rest/v1/profiles?id=eq.' . urlencode($targetUserId), [
        'status'     => 'deleted',
        'deleted_at' => date('c'),
        'email'      => $anonEmail,
        'username'   => $anonUsername,
        'first_name' => 'Anonymized',
        'last_name'  => 'User',
        'phone'      => null,
    ]);

    // 3. Write Audit Log
    record_audit_log($adminId, $targetUserId, 'USER_DELETED', $reason, [
        'admin_user' => $adminUser,
        'anonymized_email' => $anonEmail
    ]);

    send_json([
        'success' => true,
        'message' => 'User account permanently deleted from Supabase Auth backend. PII anonymized in database while preserving financial transaction ledger records.',
        'user_id' => $targetUserId,
        'status'  => 'deleted'
    ]);

} else {
    send_json(['success' => false, 'error' => 'Invalid action specified. Supported actions: suspend, reinstate, delete.'], 400);
}
