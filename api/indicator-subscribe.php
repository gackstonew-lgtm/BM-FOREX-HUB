<?php
/**
 * POST /api/indicator-subscribe.php
 * Initiates a Kora Pay checkout for an indicator subscription plan.
 *
 * Headers: Authorization: Bearer <supabase_jwt>
 * Body (JSON):
 *   {
 *     "plan": "indicator_silver|indicator_gold|indicator_vip",
 *     "phone": "+254712345678",           // for M-Pesa
 *     "payment_method": "mpesa|card",
 *     "tradingview_username": "mytvuser",  // optional, can be set later
 *     "card_name": "...",                  // for card payments
 *     "card_number": "...",
 *     "card_expiry": "MM/YY",
 *     "card_cvv": "..."
 *   }
 *
 * Response 200: { "ok": true, "merchantTransactionId": "...", "checkout_url": "..." }
 * Response 4xx/5xx: { "error": "..." }
 */
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '0');
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { ob_end_clean(); http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { ob_end_clean(); http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }

// ── Flush output buffer so only JSON reaches the client ──────────────
function send_json($data, $code = 200) {
    ob_end_clean();
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Catch fatal errors so the client always gets JSON ────────────────
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_end_clean();
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . trim($error['message'])]);
    }
});

// Rate limiting: max 5 payment initiations per 5 minutes per IP
if (session_status() === PHP_SESSION_NONE && !headers_sent()) session_start();
$rateKey = 'ind_sub_' . ($_SERVER['REMOTE_ADDR'] ?? 'cli');
if (!isset($_SESSION[$rateKey])) $_SESSION[$rateKey] = ['count' => 0, 'window' => time()];
if (time() - $_SESSION[$rateKey]['window'] > 300) {
    $_SESSION[$rateKey] = ['count' => 0, 'window' => time()];
}
$_SESSION[$rateKey]['count']++;
if ($_SESSION[$rateKey]['count'] > 5) {
    send_json(['error' => 'Too many requests. Please wait before trying again.'], 429);
}

require_once __DIR__ . '/kora-config.php';
require_once __DIR__ . '/../app/Services/IndicatorAccessService.php';
require_once __DIR__ . '/../app/Services/TradingViewSyncService.php';

use App\Services\KoraPaymentService;
use App\Services\IndicatorAccessService;
use App\Services\TradingViewSyncService;

// ── Parse request ────────────────────────────────────────────────────
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    send_json(['error' => 'Invalid JSON body'], 400);
}

$planKey    = trim($input['plan']   ?? '');
$phone      = trim($input['phone']  ?? '');
$method     = strtolower(trim($input['payment_method'] ?? ($input['method'] ?? 'mpesa')));
$tvUsername = trim($input['tradingview_username'] ?? '');
$cardName   = trim($input['card_name']   ?? '');
$cardNumber = trim($input['card_number'] ?? '');
$cardExpiry = trim($input['card_expiry'] ?? '');
$cardCvv    = trim($input['card_cvv']    ?? '');

// ── Validate plan ────────────────────────────────────────────────────
$indicatorPlans = [
    'indicator_quantum_edge' => ['signals_access' => true, 'ai_access' => true, 'premium_dashboard' => true],
    'indicator_vip'          => ['signals_access' => true, 'ai_access' => true, 'premium_dashboard' => true],
    'indicator_gold'         => ['signals_access' => true, 'ai_access' => true, 'premium_dashboard' => true],
    'indicator_silver'       => ['signals_access' => true, 'ai_access' => true, 'premium_dashboard' => true],
];

if (!$planKey || !isset($indicatorPlans[$planKey])) {
    $planKey = 'indicator_quantum_edge'; // default to canonical plan
}

if (!isset($KORA_PLANS[$planKey])) {
    $planKey = 'indicator_quantum_edge';
}

// Validate TradingView username if provided
if ($tvUsername) {
    $tvSync = new TradingViewSyncService();
    if (!$tvSync->validateUsername($tvUsername)) {
        send_json(['error' => 'Invalid TradingView username format.'], 400);
    }
}

// ── Validate payment method (supports hosted Kora checkout & direct inputs) ──
if (!empty($phone)) {
    $phone = normalize_phone($phone);
}
if ($method === 'card' && !empty($cardNumber)) {
    $cleanNum = preg_replace('/\D/', '', $cardNumber);
    if (empty($cleanNum) || strlen($cleanNum) < 13 || strlen($cleanNum) > 19) {
        send_json(['error' => 'Please enter a valid card number.'], 400);
    }
    if (empty($cardExpiry) || empty($cardCvv)) {
        send_json(['error' => 'Please enter card expiry date and CVV.'], 400);
    }
}

// ── Verify user JWT ──────────────────────────────────────────────────
$user = get_user_from_token();
if (!$user) {
    send_json(['error' => 'Unauthorized — please log in first.'], 401);
}

$userId    = $user['id'];
$userEmail = $user['email'] ?? '';
$userName  = ($user['user_metadata']['first_name'] ?? '') . ' ' . ($user['user_metadata']['last_name'] ?? '');
$userName  = trim($userName) ?: ($user['user_metadata']['username'] ?? 'Trader');

// ── Check for duplicate pending payment (2-minute window) ────────────
$supabaseUrl = SUPABASE_URL;
$serviceKey  = SUPABASE_SERVICE_KEY;

$existing = supabase_request(
    "$supabaseUrl/rest/v1/payments?user_id=eq.$userId&status=eq.pending&plan=like.indicator_%&order=created_at.desc&limit=1",
    $serviceKey
);
if ($existing && count($existing) > 0) {
    $age = time() - strtotime($existing[0]['created_at']);
    if ($age < 120) {
        send_json(['error' => 'A payment is already in progress. Please wait 2 minutes.'], 409);
    }
}

// ── Generate transaction reference ──────────────────────────────────
$merchantTxnId = 'BMFH_' . strtoupper($planKey) . '_' . strtoupper(bin2hex(random_bytes(6)));

// ── Pricing ──────────────────────────────────────────────────────────
$planConfig = $KORA_PLANS[$planKey];
$amountKes  = $planConfig['amount_kes'];
$amountUsd  = $planConfig['amount_usd'];
$planName   = $planConfig['name'];
$durationDays = $planConfig['duration_days'];

// ── Store pending payment in Supabase ────────────────────────────────
$paymentData = [
    'user_id'         => $userId,
    'plan'            => $planKey,
    'amount_kes'      => $amountKes,
    'amount_usd'      => $amountUsd,
    'phone'           => $phone ?: 'redirect',
    'status'          => 'pending',
    'merchant_txn_id' => $merchantTxnId,
    'metadata'        => json_encode([
        'tradingview_username' => $tvUsername,
        'payment_method'       => $method,
        'plan_name'            => $planName,
        'duration_days'        => $durationDays,
    ]),
];

$insertResult = supabase_request("$supabaseUrl/rest/v1/payments", $serviceKey, 'POST', $paymentData);
if (!$insertResult) {
    // Fallback: try SQLite
    try {
        if (function_exists('getMarketPDO')) {
            $pdo = getMarketPDO();
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO payments
                (id, user_id, plan, amount_kes, phone, status, merchant_txn_id, created_at)
                VALUES (:id, :uid, :plan, :kes, :phone, 'pending', :txn, datetime('now'))");
            $stmt->execute([
                ':id'   => bin2hex(random_bytes(16)),
                ':uid'  => $userId,
                ':plan' => $planKey,
                ':kes'  => $amountKes,
                ':phone'=> $phone ?: 'redirect',
                ':txn'  => $merchantTxnId,
            ]);
        }
    } catch (\Throwable $e) {
        send_json(['error' => 'Failed to create payment record. Please try again.'], 500);
    }
}

// ── Initiate Kora Pay checkout ───────────────────────────────────────
$koraService  = new KoraPaymentService();
$scheme       = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443) ? 'https' : 'http';
$host         = $_SERVER['HTTP_HOST'] ?? 'bmforexhub.exchange';
$webhookUrl   = getenv('KORA_WEBHOOK_URL') ?: "$scheme://$host/api/indicator-webhook.php";
// Clean redirect URLs (Kora appends ?status=...). Reference travels as
// a fragment so it survives Kora's query-param append.
$successUrl   = "$scheme://$host/trading.php#status=success&ref=" . urlencode($merchantTxnId);
$failUrl      = "$scheme://$host/indicator-subscribe.php";

$chargeData = [
    'amount'           => $amountKes,
    'currency'         => 'KES',
    'reference'        => $merchantTxnId,
    'description'      => "$planName — BM FOREX HUB Indicator",
    'notification_url' => $webhookUrl,
    'redirect_url'     => $successUrl,
    'customer_name'    => $cardName ?: $userName,
    'customer_email'   => $userEmail,
    'phone'            => $phone,
    'payment_method'   => $method,
    'card_name'        => $cardName,
    'card_number'      => $cardNumber,
    'card_expiry'      => $cardExpiry,
    'card_cvv'         => $cardCvv,
];

$koraResult = $koraService->initializePayment($chargeData);

if ($koraResult['success']) {
    // For card payments: activate subscription immediately
    if ($method === 'card') {
        supabase_request(
            "$supabaseUrl/rest/v1/payments?merchant_txn_id=eq.$merchantTxnId",
            $serviceKey,
            'PATCH',
            ['status' => 'succeeded', 'processor_ref' => 'CARD_' . bin2hex(random_bytes(6))]
        );

        $accessService = new IndicatorAccessService();
        $planFlags     = $indicatorPlans[$planKey];
        $accessService->activateSubscription([
            'user_id'              => $userId,
            'user_email'           => $userEmail,
            'user_name'            => $userName,
            'plan_key'             => $planKey,
            'plan_name'            => $planName,
            'payment_reference'    => $merchantTxnId,
            'amount_paid_usd'      => $amountUsd,
            'amount_paid_kes'      => $amountKes,
            'tradingview_username' => $tvUsername,
            'duration_days'        => $durationDays,
            'signals_access'       => $planFlags['signals_access'],
            'ai_access'            => $planFlags['ai_access'],
            'premium_dashboard'    => $planFlags['premium_dashboard'],
        ]);

        // Log the activation
        $accessService->logAction(null, $userId, $userEmail, 'subscription_activated', $planKey, [
            'method' => 'card',
            'plan'   => $planKey,
        ]);
    }

    $resp = [
        'ok'                    => true,
        'message'               => $method === 'card'
            ? 'Card payment processed! Activating your indicator access...'
            : 'Redirecting to Kora Pay checkout...',
        'merchantTransactionId' => $merchantTxnId,
    ];
    if (!empty($koraResult['checkout_url'])) {
        $resp['checkout_url'] = $koraResult['checkout_url'];
    }
    send_json($resp);
}

// Payment gateway error
$errMsg = $koraResult['message'] ?: 'Payment request failed. Please try again.';
supabase_request(
    "$supabaseUrl/rest/v1/payments?merchant_txn_id=eq.$merchantTxnId",
    $serviceKey, 'PATCH',
    ['status' => 'failed', 'failure_reason' => $errMsg]
);

send_json(['error' => $errMsg], 400);


// ── Helper Functions ─────────────────────────────────────────────────

function normalize_phone(string $phone): ?string
{
    $phone = preg_replace('/[\s\-()]/', '', trim($phone));
    if (preg_match('/^0(7|1)\d{8}$/', $phone))    return '+254' . substr($phone, 1);
    if (preg_match('/^254(7|1)\d{8}$/', $phone))   return '+' . $phone;
    if (preg_match('/^\+254(7|1)\d{8}$/', $phone)) return $phone;
    return null;
}

function get_user_from_token(): ?array
{
    $header = $_SERVER['HTTP_AUTHORIZATION']
           ?? $_SERVER['Authorization']
           ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
           ?? '';

    if (!preg_match('/Bearer\s+(.+)/i', $header, $m)) {
        if (function_exists('apache_request_headers')) {
            foreach (apache_request_headers() as $k => $v) {
                if (strtolower($k) === 'authorization') { $header = $v; break; }
            }
        }
        if (!preg_match('/Bearer\s+(.+)/i', $header, $m)) return null;
    }

    $token = $m[1];
    $ctx   = stream_context_create([
        'http' => [
            'header'  => "Authorization: Bearer $token\r\napikey: " . SUPABASE_ANON_KEY,
            'timeout' => 5,
        ],
    ]);
    $resp = @file_get_contents(SUPABASE_URL . '/auth/v1/user', false, $ctx);
    if (!$resp) return null;
    $data = json_decode($resp, true);
    return isset($data['id']) ? $data : null;
}

function supabase_request(string $url, string $serviceKey, string $method = 'GET', ?array $data = null): ?array
{
    $headers = [
        "apikey: $serviceKey",
        "Authorization: Bearer $serviceKey",
        "Content-Type: application/json",
        "Prefer: return=representation",
    ];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POSTFIELDS     => $data ? json_encode($data) : null,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    $decoded = $resp ? json_decode($resp, true) : null;
    return is_array($decoded) ? $decoded : null;
}
