<?php
/**
 * POST/GET /api/elite-enrollment.php
 * 
 * Production endpoint for BM FOREX HUB Elite Circle Electronic Enrollment & Terms Acceptance.
 * Handles server-side validation, authoritative versioning, 4-month cycle calculation,
 * immutable database persistence, and administrative notifications.
 */

error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '0');
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/kora-config.php';
require_once __DIR__ . '/../admin/config.php';
require_once __DIR__ . '/../engine_config.php';

// Canonical Terms Constants (Source of Truth: BM FOREX HUB Elite Circle Terms & Conditions)
define('ELITE_TERMS_VERSION', '1.0');
define('ELITE_TERMS_EFFECTIVE_DATE', '2026-01-05');
define('ELITE_OPERATING_ENTITY', 'Varban Company Limited');
define('ELITE_PARENT_COMPANY', 'Stillrock Ventures');
define('ELITE_JURISDICTION', 'Republic of Kenya');
define('ELITE_CYCLE_MONTHS', 4);

// Canonical Terms Text for deterministic SHA-256 hash calculation
const CANONICAL_TERMS_SUMMARY = "BM FOREX HUB ELITE CIRCLE TERMS AND CONDITIONS | Effective Date: 05 January 2026 | Operating Entity: Varban Company Limited | Parent: Stillrock Ventures | Jurisdiction: Republic of Kenya | 4-Month Managed Investment Cycle";
define('ELITE_TERMS_CONTENT_HASH', hash('sha256', CANONICAL_TERMS_SUMMARY));

function send_response($data, $code = 200) {
    ob_end_clean();
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Global shutdown error handler
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Internal server error: ' . trim($error['message'])]);
    }
});

// Helper: Extract authenticated user from Supabase JWT token or session
function resolve_authenticated_user() {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] 
        ?? $_SERVER['Authorization'] 
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] 
        ?? '';

    if (!preg_match('/Bearer\s+(.+)/i', $auth_header, $m)) {
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        foreach ($headers as $k => $v) {
            if (strtolower($k) === 'authorization') { $auth_header = $v; break; }
        }
        if (preg_match('/Bearer\s+(.+)/i', $auth_header, $m)) {
            $token = trim($m[1]);
        }
    } else {
        $token = trim($m[1]);
    }

    if (!empty($token)) {
        $supabase_url  = defined('SUPABASE_URL') ? SUPABASE_URL : 'https://iwoytmcxmbhmmbrbpzvf.supabase.co';
        $supabase_anon = defined('SUPABASE_ANON') ? SUPABASE_ANON : (defined('SUPABASE_ANON_KEY') ? SUPABASE_ANON_KEY : '');

        $ctx = stream_context_create([
            'http' => [
                'header'  => "Authorization: Bearer $token\r\napikey: $supabase_anon",
                'timeout' => 8,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ]
        ]);

        $resp = @file_get_contents("$supabase_url/auth/v1/user", false, $ctx);
        if ($resp) {
            $user = json_decode($resp, true);
            if (!empty($user['id'])) {
                return $user;
            }
        }
    }

    // Session fallback for active session
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }
    if (!empty($_SESSION['user_id'])) {
        return [
            'id'    => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'] ?? '',
            'user_metadata' => $_SESSION['user_metadata'] ?? []
        ];
    }

    return null;
}

// Get user profile details from database
function get_user_profile_data($userId, $userEmail = '') {
    $pdo = getMarketPDO();
    $profile = [
        'full_name' => '',
        'first_name'=> '',
        'last_name' => '',
        'email'     => $userEmail,
        'phone'     => '',
        'country'   => '',
        'id_passport_number' => ''
    ];

    try {
        $stmt = $pdo->prepare("SELECT * FROM profiles WHERE id = ? OR email = ? LIMIT 1");
        $stmt->execute([$userId, $userEmail]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $first = trim($row['first_name'] ?? '');
            $last  = trim($row['last_name'] ?? '');
            $fullName = trim($first . ' ' . $last);
            $profile['first_name'] = $first;
            $profile['last_name']  = $last;
            $profile['full_name']  = $fullName ?: ($row['username'] ?? '');
            $profile['email']      = $row['email'] ?: $userEmail;
            $profile['phone']      = $row['phone'] ?? '';
            $profile['country']    = $row['country_code'] ?? ($row['country'] ?? '');
        }
    } catch (\Throwable $e) {}

    return $profile;
}

// Determine client IP address safely
function get_client_ip() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($parts[0]);
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1';
}

// Find user's active BM Elite subscription if one exists
function get_active_elite_subscription($userId, $userEmail = '') {
    $pdo = getMarketPDO();
    $nowIso = date('c');

    try {
        // 1. Check subscriptions table
        $stmt = $pdo->prepare("
            SELECT id, user_id, username, plan, plan_key, plan_name, amount_usd, starts_at, expires_at, created_at, status
            FROM subscriptions 
            WHERE (user_id = ? OR username = ? OR id = ?)
              AND (plan LIKE 'elite_%' OR plan_key LIKE 'elite_%' OR plan = 'all' OR plan_name LIKE '%Elite%')
              AND (status = 'active' OR status IS NULL OR status = '')
              AND (expires_at IS NULL OR expires_at = '' OR expires_at > ?)
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$userId, $userEmail, $userId, $nowIso]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) return $row;

        // 2. Check user_subscriptions table
        $stmt2 = $pdo->prepare("
            SELECT id, user_id, username, plan, plan_name, amount_usd, starts_at, expires_at, created_at, status
            FROM user_subscriptions 
            WHERE (user_id = ? OR username = ?)
              AND (plan LIKE 'elite_%' OR plan = 'all' OR plan_name LIKE '%Elite%')
              AND (status = 'active' OR status IS NULL OR status = '')
              AND (expires_at IS NULL OR expires_at = '' OR expires_at > ?)
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt2->execute([$userId, $userEmail, $nowIso]);
        $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        if ($row2) return $row2;

    } catch (\Throwable $e) {}

    return null;
}

// Determine if user is an active BM Elite Member
function isActiveBMEliteMember($userId, $userEmail = '') {
    $sub = get_active_elite_subscription($userId, $userEmail);
    if ($sub) return true;

    // Permanent admin accounts / VIP pass
    $adminEmails = ['admin@bmforexhub.exchange', 'info@admin.bmforexhub.exchange', 'gackstonebaraka@gmail.com'];
    if (in_array(strtolower($userEmail), $adminEmails)) {
        return true;
    }

    return false;
}

// ── Routing ─────────────────────────────────────────────────────────

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$method = $_SERVER['REQUEST_METHOD'];

// Parse JSON body for POST requests
$postData = [];
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    $postData = is_array($decoded) ? $decoded : $_POST;
    if (empty($action) && isset($postData['action'])) {
        $action = $postData['action'];
    }
    if (empty($action)) {
        $action = 'submit';
    }
}

$user = resolve_authenticated_user();
if (!$user) {
    send_response(['ok' => false, 'error' => 'Authentication required. Please sign in.'], 401);
}

$userId = $user['id'];
$userEmail = $user['email'] ?? '';

// ── ACTION: Check Current Terms Acceptance Status ────────────────────
if ($action === 'check') {
    $pdo = getMarketPDO();
    $stmt = $pdo->prepare("
        SELECT id, user_id, member_name, investment_amount, currency, investment_start_date, 
               expected_cycle_completion_date, terms_version, terms_effective_date, accepted, accepted_at, email_status
        FROM elite_circle_enrollments 
        WHERE (user_id = ? OR email = ?) AND terms_version = ? AND accepted = 1 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$userId, $userEmail, ELITE_TERMS_VERSION]);
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

    $isElite = isActiveBMEliteMember($userId, $userEmail);
    $activeSub = get_active_elite_subscription($userId, $userEmail);
    $termsAccepted = !empty($enrollment);
    $requiresConsent = $isElite && !$termsAccepted;

    send_response([
        'ok'                    => true,
        'accepted'              => $termsAccepted,
        'is_elite'              => $isElite,
        'requires_consent'      => $requiresConsent,
        'terms_version'         => ELITE_TERMS_VERSION,
        'terms_effective_date'  => ELITE_TERMS_EFFECTIVE_DATE,
        'enrollment'            => $enrollment ?: null,
        'existing_subscription' => $activeSub ?: null
    ]);
}

// ── ACTION: Prefill Authenticated Member Data ─────────────────────────
if ($action === 'prefill') {
    $profile = get_user_profile_data($userId, $userEmail);
    
    // Metadata fallback
    $meta = $user['user_metadata'] ?? [];
    if (empty($profile['full_name'])) {
        $nameFromMeta = trim(($meta['first_name'] ?? '') . ' ' . ($meta['last_name'] ?? ''));
        $profile['full_name'] = $nameFromMeta ?: ($meta['full_name'] ?? ($meta['name'] ?? ($meta['username'] ?? '')));
    }
    if (empty($profile['phone'])) {
        $profile['phone'] = $meta['phone'] ?? ($meta['phone_number'] ?? '');
    }
    if (empty($profile['country'])) {
        $profile['country'] = $meta['country_code'] ?? ($meta['country'] ?? '');
    }

    $isElite = isActiveBMEliteMember($userId, $userEmail);
    $activeSub = get_active_elite_subscription($userId, $userEmail);
    $stmt = $pdo = getMarketPDO();
    $checkStmt = $pdo->prepare("
        SELECT id, terms_version, accepted, accepted_at 
        FROM elite_circle_enrollments 
        WHERE (user_id = ? OR email = ?) AND terms_version = ? AND accepted = 1 
        LIMIT 1
    ");
    $checkStmt->execute([$userId, $userEmail, ELITE_TERMS_VERSION]);
    $existingEnrollment = $checkStmt->fetch(PDO::FETCH_ASSOC);

    $termsAccepted = !empty($existingEnrollment);
    $requiresConsent = $isElite && !$termsAccepted;

    send_response([
        'ok'                    => true,
        'user_id'               => $userId,
        'profile'               => $profile,
        'is_elite'              => $isElite,
        'requires_consent'      => $requiresConsent,
        'existing_subscription' => $activeSub ?: null,
        'enrollment'            => $existingEnrollment ?: null
    ]);
}

// ── ACTION: Submit Electronic Enrollment & Terms Acceptance ───────────
if ($action === 'submit' && $method === 'POST') {
    $memberName        = trim($postData['member_name'] ?? '');
    $idPassportNumber  = trim($postData['id_passport_number'] ?? '');
    $phone             = trim($postData['phone'] ?? '');
    $email             = trim($postData['email'] ?? $userEmail);
    $country           = trim($postData['country'] ?? '');
    $investmentAmount  = floatval($postData['investment_amount'] ?? 0);
    $currency          = strtoupper(trim($postData['currency'] ?? 'USD'));
    $paymentReference  = trim($postData['payment_reference'] ?? '');
    $startDateInput    = trim($postData['investment_start_date'] ?? '');
    $acceptedTerms     = !empty($postData['accepted']) && ($postData['accepted'] === true || $postData['accepted'] === '1' || $postData['accepted'] === 1 || $postData['accepted'] === 'true');
    $planParam         = trim($postData['plan'] ?? '');

    // 1. Validation
    if (empty($memberName)) {
        send_response(['ok' => false, 'error' => 'Full Name is required.'], 400);
    }
    if (empty($idPassportNumber)) {
        send_response(['ok' => false, 'error' => 'National ID or Passport Number is required for legal electronic enrollment.'], 400);
    }
    if (empty($phone)) {
        send_response(['ok' => false, 'error' => 'Phone number is required.'], 400);
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        send_response(['ok' => false, 'error' => 'A valid email address is required.'], 400);
    }
    if (empty($country)) {
        send_response(['ok' => false, 'error' => 'Country of residence is required.'], 400);
    }
    if ($investmentAmount <= 0) {
        send_response(['ok' => false, 'error' => 'Please enter a valid investment amount.'], 400);
    }
    if (!in_array($currency, ['USD', 'KES', 'EUR', 'GBP', 'USDT', 'OTHER'])) {
        $currency = 'USD';
    }
    if (!$acceptedTerms) {
        send_response(['ok' => false, 'error' => 'You must actively read and accept the BM FOREX HUB Elite Circle Terms and Conditions to proceed.'], 400);
    }

    // 2. Authoritative Cycle Dates Calculation
    // Start date defaults to current date if not specified or invalid
    $startDateTimestamp = !empty($startDateInput) ? strtotime($startDateInput) : time();
    if (!$startDateTimestamp || $startDateTimestamp < strtotime('-30 days')) {
        $startDateTimestamp = time();
    }
    $startDate = date('Y-m-d', $startDateTimestamp);
    // 4-Month complete trading cycle server calculation
    $completionDate = date('Y-m-d', strtotime('+' . ELITE_CYCLE_MONTHS . ' months', $startDateTimestamp));

    // 3. Server Timestamps & Client Metadata
    $serverNow       = date('c');
    $acceptedIp      = get_client_ip();
    $userAgent       = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

    $pdo = getMarketPDO();

    // 4. Idempotency & Duplicate Protection
    $checkStmt = $pdo->prepare("
        SELECT id, created_at FROM elite_circle_enrollments 
        WHERE (user_id = ? OR email = ?) AND terms_version = ? AND accepted = 1 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $checkStmt->execute([$userId, $userEmail, ELITE_TERMS_VERSION]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    $isExistingMember = !empty($postData['is_existing_member']) 
        || (isset($postData['mode']) && $postData['mode'] === 'compliance') 
        || isActiveBMEliteMember($userId, $userEmail);

    $defaultRedirectUrl = $isExistingMember 
        ? 'bm_elites.php?compliance_success=1' 
        : ('subscribe.php' . ($planParam ? '?plan=' . urlencode($planParam) : '?service=elite'));

    $enrollmentId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    // If duplicate submission within 60 seconds by same user, return existing without error
    if ($existing) {
        $existingAge = time() - strtotime($existing['created_at']);
        if ($existingAge < 60) {
            send_response([
                'ok'                    => true,
                'already_enrolled'      => true,
                'message'               => 'Elite Circle enrollment and Terms acceptance already recorded.',
                'enrollment_id'         => $existing['id'],
                'terms_version'         => ELITE_TERMS_VERSION,
                'terms_effective_date'  => ELITE_TERMS_EFFECTIVE_DATE,
                'accepted_at'           => $existing['created_at'],
                'accepted_date'         => date('d F Y', strtotime($existing['created_at'])),
                'accepted_time'         => date('H:i:s T', strtotime($existing['created_at'])),
                'redirect_url'          => $defaultRedirectUrl
            ]);
        }
    }

    // 5. Persist Enrollment Record to Database
    $enrollmentNote = $isExistingMember 
        ? 'Existing active BM Elite member terms acceptance' 
        : 'Electronic acceptance via web portal';

    $insertSql = "
        INSERT INTO elite_circle_enrollments (
            id, user_id, member_name, id_passport_number, phone, email, country,
            investment_amount, currency, payment_reference, investment_start_date,
            expected_cycle_completion_date, terms_version, terms_effective_date,
            terms_content_hash, accepted, accepted_at, accepted_ip_address, user_agent,
            email_status, notes, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, 1, ?, ?, ?,
            'pending', ?, ?, ?
        )
    ";

    $stmt = $pdo->prepare($insertSql);
    $stmt->execute([
        $enrollmentId,
        $userId,
        $memberName,
        $idPassportNumber,
        $phone,
        $email,
        $country,
        $investmentAmount,
        $currency,
        $paymentReference,
        $startDate,
        $completionDate,
        ELITE_TERMS_VERSION,
        ELITE_TERMS_EFFECTIVE_DATE,
        ELITE_TERMS_CONTENT_HASH,
        $serverNow,
        $acceptedIp,
        $userAgent,
        $enrollmentNote,
        $serverNow,
        $serverNow
    ]);

    // Also mirror to Supabase if accessible
    try {
        if (function_exists('sb_admin_post')) {
            sb_admin_post('elite_circle_enrollments', [
                'id'                            => $enrollmentId,
                'user_id'                       => $userId,
                'member_name'                   => $memberName,
                'id_passport_number'           => $idPassportNumber,
                'phone'                         => $phone,
                'email'                         => $email,
                'country'                       => $country,
                'investment_amount'             => $investmentAmount,
                'currency'                      => $currency,
                'payment_reference'             => $paymentReference,
                'investment_start_date'         => $startDate,
                'expected_cycle_completion_date'=> $completionDate,
                'terms_version'                 => ELITE_TERMS_VERSION,
                'terms_effective_date'          => ELITE_TERMS_EFFECTIVE_DATE,
                'terms_content_hash'            => ELITE_TERMS_CONTENT_HASH,
                'accepted'                      => 1,
                'accepted_at'                   => $serverNow,
                'accepted_ip_address'           => $acceptedIp,
                'notes'                         => $enrollmentNote,
                'created_at'                    => $serverNow,
                'updated_at'                    => $serverNow
            ]);
        }
    } catch (\Throwable $e) {}

    // 6. Send Administrative Notification Email to configured administrator recipient
    $adminEmail = getenv('ADMIN_EMAIL') ?: ($_SERVER['ADMIN_EMAIL'] ?? 'info@admin.bmforexhub.exchange');
    $emailSubject = $isExistingMember 
        ? "BM FOREX HUB — Existing BM Elite Member Terms Acceptance" 
        : "BM FOREX HUB — BM Elite Compliance & Terms Acceptance";
    $formattedAcceptDate = date('d F Y', strtotime($serverNow));
    $formattedAcceptTime = date('H:i:s T', strtotime($serverNow));
    $memberStatusLabel = $isExistingMember 
        ? "Existing Active BM Elite Member (Terms v1.0 Compliance)" 
        : "New Prospective BM Elite Member";

    $adminEmailHtml = "
    <div style='font-family: Arial, sans-serif; max-width: 640px; margin: 0 auto; background: #0B0F14; color: #FFFFFF; border: 1px solid #283548; border-radius: 12px; overflow: hidden;'>
        <div style='background: #151D29; padding: 24px 28px; border-bottom: 1px solid #283548;'>
            <h2 style='color: #1677FF; margin: 0 0 4px; font-size: 20px; letter-spacing: 0.04em;'>BM FOREX HUB</h2>
            <h3 style='color: #F0B429; margin: 0 0 4px; font-size: 16px;'>BM Elite / Elite Circle</h3>
            <h4 style='color: #FFFFFF; margin: 0; font-size: 14px; font-weight: 500;'>Electronic Enrollment &amp; Compliance Record</h4>
        </div>
        <div style='padding: 24px 28px;'>
            <p style='color: #8FA3B8; font-size: 13.5px; margin-top: 0; line-height: 1.5;'>The following compliance and terms acceptance record was submitted by an authenticated member and committed to the system ledger:</p>

            <div style='background: #101722; border: 1px solid #1E2D42; border-radius: 8px; padding: 18px; margin-bottom: 18px;'>
                <h4 style='color: #F0B429; margin: 0 0 12px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.06em;'>MEMBER DETAILS</h4>
                <table style='width: 100%; border-collapse: collapse; font-size: 13px; color: #FFFFFF;'>
                    <tr><td style='padding: 6px 0; color: #8FA3B8; width: 38%;'>Member Status:</td><td style='padding: 6px 0; font-weight: 700; color: " . ($isExistingMember ? "#F0B429" : "#16C784") . ";'>" . htmlspecialchars($memberStatusLabel) . "</td></tr>
                    <tr><td style='padding: 6px 0; color: #8FA3B8;'>Full Name:</td><td style='padding: 6px 0; font-weight: 600;'>" . htmlspecialchars($memberName) . "</td></tr>
                    <tr><td style='padding: 6px 0; color: #8FA3B8;'>ID / Passport Number:</td><td style='padding: 6px 0; font-weight: 600; color: #F0B429; font-family: monospace;'>" . htmlspecialchars($idPassportNumber) . "</td></tr>
                    <tr><td style='padding: 6px 0; color: #8FA3B8;'>Phone Number:</td><td style='padding: 6px 0;'>" . htmlspecialchars($phone) . "</td></tr>
                    <tr><td style='padding: 6px 0; color: #8FA3B8;'>Email Address:</td><td style='padding: 6px 0;'>" . htmlspecialchars($email) . "</td></tr>
                    <tr><td style='padding: 6px 0; color: #8FA3B8;'>Country of Residence:</td><td style='padding: 6px 0;'>" . htmlspecialchars($country) . "</td></tr>
                </table>
            </div>

            <div style='background: #101722; border: 1px solid #1E2D42; border-radius: 8px; padding: 18px; margin-bottom: 18px;'>
                <h4 style='color: #16C784; margin: 0 0 12px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.06em;'>INVESTMENT DETAILS</h4>
                <table style='width: 100%; border-collapse: collapse; font-size: 13px; color: #FFFFFF;'>
                    <tr><td style='padding: 6px 0; color: #8FA3B8; width: 38%;'>Investment Amount:</td><td style='padding: 6px 0; font-weight: 700; color: #16C784; font-size: 15px;'>" . htmlspecialchars($currency) . " " . number_format($investmentAmount, 2) . "</td></tr>
                    <tr><td style='padding: 6px 0; color: #8FA3B8;'>Currency:</td><td style='padding: 6px 0;'>" . htmlspecialchars($currency) . "</td></tr>
                    <tr><td style='padding: 6px 0; color: #8FA3B8;'>Payment / Subscription Ref:</td><td style='padding: 6px 0;'>" . htmlspecialchars($paymentReference ?: ($isExistingMember ? 'Existing Active Subscription' : 'Pending Payment Stage')) . "</td></tr>
                    <tr><td style='padding: 6px 0; color: #8FA3B8;'>Investment Start Date:</td><td style='padding: 6px 0;'>" . htmlspecialchars($startDate) . "</td></tr>
                    <tr><td style='padding: 6px 0; color: #8FA3B8;'>Expected Cycle Completion:</td><td style='padding: 6px 0; font-weight: 600;'>" . htmlspecialchars($completionDate) . " (4-Month Cycle)</td></tr>
                </table>
            </div>

            <div style='background: #101722; border: 1px solid #1E2D42; border-radius: 8px; padding: 18px; margin-bottom: 18px;'>
                <h4 style='color: #1677FF; margin: 0 0 12px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.06em;'>TERMS ACCEPTANCE</h4>
                <table style='width: 100%; border-collapse: collapse; font-size: 13px; color: #FFFFFF;'>
                    <tr><td style='padding: 6px 0; color: #8FA3B8; width: 38%;'>Terms:</td><td style='padding: 6px 0; font-weight: 600;'>BM FOREX HUB Elite Circle Terms &amp; Conditions</td></tr>
                    <tr><td style='padding: 6px 0; color: #8FA3B8;'>Terms Version:</td><td style='padding: 6px 0; font-weight: 600;'>" . ELITE_TERMS_VERSION . "</td></tr>
                    <tr><td style='padding: 6px 0; color: #8FA3B8;'>Terms Effective Date:</td><td style='padding: 6px 0;'>" . ELITE_TERMS_EFFECTIVE_DATE . "</td></tr>
                    <tr><td style='padding: 6px 0; color: #8FA3B8;'>Terms Accepted:</td><td style='padding: 6px 0; color: #16C784; font-weight: 700;'>YES</td></tr>
                    <tr><td style='padding: 6px 0; color: #8FA3B8;'>Acceptance Date:</td><td style='padding: 6px 0;'>" . $formattedAcceptDate . "</td></tr>
                    <tr><td style='padding: 6px 0; color: #8FA3B8;'>Acceptance Time:</td><td style='padding: 6px 0;'>" . $formattedAcceptTime . "</td></tr>
                    <tr><td style='padding: 6px 0; color: #8FA3B8;'>Acceptance Record ID:</td><td style='padding: 6px 0; font-family: monospace; font-size: 12px;'>" . htmlspecialchars($enrollmentId) . "</td></tr>
                    <tr><td style='padding: 6px 0; color: #8FA3B8;'>Authenticated User ID:</td><td style='padding: 6px 0; font-family: monospace; font-size: 12px;'>" . htmlspecialchars($userId) . "</td></tr>
                    <tr><td style='padding: 6px 0; color: #8FA3B8;'>Client IP Address:</td><td style='padding: 6px 0; font-family: monospace; font-size: 12px;'>" . htmlspecialchars($acceptedIp) . "</td></tr>
                </table>
            </div>

            <p style='color: #7F8B99; font-size: 12px; margin-bottom: 0;'>This is an automated production compliance record generated by the BM FOREX HUB compliance and legal audit subsystem.</p>
        </div>
    </div>
    ";

    $emailStatus = 'sent';
    $emailError = null;

    try {
        if (class_exists('App\Services\MailService')) {
            $mailService = new \App\Services\MailService();
            $res = $mailService->send($adminEmail, $emailSubject, $adminEmailHtml, 'BM Forex Hub Administrator');
            if (empty($res['success'])) {
                $emailStatus = 'failed';
                $emailError = $res['error'] ?? 'Mail provider dispatch failure';
            }
        } elseif (function_exists('mail')) {
            $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: BM FOREX HUB <info@bmforexhub.exchange>\r\n";
            @mail($adminEmail, $emailSubject, $adminEmailHtml, $headers);
        }
    } catch (\Throwable $e) {
        $emailStatus = 'failed';
        $emailError = $e->getMessage();
        error_log("Elite enrollment admin notification mail error: " . $e->getMessage());
    }

    // Update email status in database
    try {
        $updateStmt = $pdo->prepare("
            UPDATE elite_circle_enrollments 
            SET email_status = ?, email_sent_at = ?, email_error = ?, updated_at = ? 
            WHERE id = ?
        ");
        $updateStmt->execute([
            $emailStatus,
            $emailStatus === 'sent' ? $serverNow : null,
            $emailError,
            $serverNow,
            $enrollmentId
        ]);
    } catch (\Throwable $e) {}

    // 7. Success Response with redirect instructions
    send_response([
        'ok'                    => true,
        'message'               => $isExistingMember 
            ? 'Terms & Conditions acceptance verified. Your Elite VIP status and mentorship access have been confirmed.'
            : 'Elite Circle enrollment completed. Your Terms & Conditions acceptance has been recorded.',
        'enrollment_id'         => $enrollmentId,
        'is_existing_member'    => $isExistingMember,
        'terms_version'         => ELITE_TERMS_VERSION,
        'terms_effective_date'  => '05 January 2026',
        'accepted_at'           => $serverNow,
        'accepted_date'         => $formattedAcceptDate,
        'accepted_time'         => $formattedAcceptTime,
        'email_status'          => $emailStatus,
        'redirect_url'          => $defaultRedirectUrl
    ]);
}

send_response(['ok' => false, 'error' => 'Invalid action.'], 400);
