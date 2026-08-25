<?php
/**
 * POST /api/deposit.php
 * Initiates an M-Pesa STK Push or Card payment via Kora Payment Service.
 *
 * Request body (JSON):
 *   { "plan": "...", "phone": "+254712345678", "payment_method": "mpesa|card" }
 *
 * Returns:
 *   200 → { "ok": true, "message": "...", "merchantTransactionId": "...", "checkout_url": "..." }
 *   4xx/5xx → { "error": "..." }
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

require_once __DIR__ . '/kora-config.php';

use App\Services\KoraPaymentService;

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

// ── Parse request ──────────────────────────────────────────────────
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { send_json(['error' => 'Invalid JSON'], 400); }

$plan   = $input['plan']   ?? '';
$phone  = $input['phone']  ?? '';
$method = strtolower($input['payment_method'] ?? ($input['method'] ?? 'mpesa'));

if (!$plan || !isset($KORA_PLANS[$plan])) {
    send_json(['error' => 'Invalid plan specified.'], 400);
}

$card_name   = $input['card_name']   ?? '';
$card_number = $input['card_number'] ?? '';
$card_expiry = $input['card_expiry'] ?? '';
$card_cvv    = $input['card_cvv']    ?? '';

if ($method === 'mpesa') {
    $phone = normalize_phone($phone ?: '');
} elseif ($method === 'card') {
    $cleanNum = preg_replace('/\D/', '', $card_number);
    if (empty($cleanNum) || strlen($cleanNum) < 13) {
        send_json(['error' => 'Please enter a valid credit or debit card number'], 400);
    }
    if (empty($card_expiry) || empty($card_cvv)) {
        send_json(['error' => 'Please enter card expiry date and CVV'], 400);
    }
}

// ── Verify user via Supabase JWT ──────────────────────────────────
$user = get_user_from_token();
if (!$user) {
    send_json(['error' => 'Unauthorized — please log in'], 401);
}

$user_id    = $user['id'];
$user_email = $user['email'] ?? 'trader@bmforexhub.exchange';

// ── Check for pending/duplicate payment ────────────────────────────
$supabase_url = SUPABASE_URL;
$service_key  = SUPABASE_SERVICE_KEY;

$existing = supabase_request(
    "$supabase_url/rest/v1/payments?user_id=eq.$user_id&status=eq.pending&order=created_at.desc&limit=1",
    $service_key
);

if ($existing && count($existing) > 0) {
    $pending_age = time() - strtotime($existing[0]['created_at']);
    // Allow retry after 3 minutes (180s) for checkout-based flows
    // Kora checkout sessions expire if user abandons the page
    if ($pending_age < 180) {
        // Also verify with Kora if the pending payment actually succeeded or failed
        // so we don't block retries when the webhook hasn't arrived yet
        $pendingRef = $existing[0]['merchant_txn_id'] ?? '';
        if (!empty($pendingRef)) {
            $verifyService = new KoraPaymentService();
            $verifyResult  = $verifyService->verifyPayment($pendingRef);
            $verifyStatus  = $verifyResult['status'] ?? 'unknown';
            if (in_array($verifyStatus, ['success', 'succeeded'])) {
                // Payment actually succeeded — mark it and let webhook handle subscription
                supabase_request(
                    "$supabase_url/rest/v1/payments?merchant_txn_id=eq.$pendingRef",
                    $service_key, 'PATCH',
                    ['status' => 'succeeded']
                );
                send_json(['error' => 'Your previous payment was confirmed. Please refresh the page.'], 409);
            } elseif (in_array($verifyStatus, ['failed', 'cancelled', 'expired', 'abandoned'])) {
                // Payment failed/cancelled at Kora — clean it up so user can retry
                supabase_request(
                    "$supabase_url/rest/v1/payments?merchant_txn_id=eq.$pendingRef",
                    $service_key, 'PATCH',
                    ['status' => 'failed', 'failure_reason' => 'Abandoned or cancelled by user']
                );
                // Allow the new payment to proceed
            } else {
                // Still genuinely pending — block retry
                send_json(['error' => 'A payment is already in progress. Please wait a moment and try again.'], 409);
            }
        } else {
            send_json(['error' => 'A payment is already in progress. Please wait a moment and try again.'], 409);
        }
    } else {
        // Stale pending payment — mark it as abandoned
        $staleRef = $existing[0]['merchant_txn_id'] ?? '';
        if (!empty($staleRef)) {
            supabase_request(
                "$supabase_url/rest/v1/payments?merchant_txn_id=eq.$staleRef",
                $service_key, 'PATCH',
                ['status' => 'failed', 'failure_reason' => 'Payment session expired (3 min timeout)']
            );
        }
    }
}

// ── Generate merchant transaction ID ──────────────────────────────
$merchant_txn_id = 'BMFH_' . $plan . '_' . bin2hex(random_bytes(8));

// ── Create pending payment record in Supabase ─────────────────────
$amount_kes   = $KORA_PLANS[$plan]['amount_kes'];
$amount_cents = $amount_kes * 100;

$payment_data = [
    'user_id'          => $user_id,
    'plan'             => $plan,
    'amount_kes'       => $amount_kes,
    'amount_cents'     => $amount_cents,
    'phone'            => $phone ?: 'redirect',
    'status'           => 'pending',
    'merchant_txn_id'  => $merchant_txn_id,
];

$insert_result = supabase_request(
    "$supabase_url/rest/v1/payments",
    $service_key,
    'POST',
    $payment_data
);

if (!$insert_result) {
    @error_log('BMFH deposit insert failed: ' . json_encode($payment_data));
    send_json(['error' => 'Failed to create payment record'], 500);
}

// ── Call Kora Payment Service ──────────────────────────────────────
$koraService = new KoraPaymentService();

// Re-read from $_SERVER after kora-config.php has loaded the .env values
$webhook_url  = $_SERVER['KORA_WEBHOOK_URL']  ?? getenv('KORA_WEBHOOK_URL')  ?? 'https://bmforexhub.exchange/api/kora-webhook.php';
$redirect_url = $_SERVER['KORA_SUCCESS_URL'] ?? getenv('KORA_SUCCESS_URL') ?? 'https://bmforexhub.exchange/subscribe.php';
// Ensure redirect URL is clean (no pre-existing query string) so Kora can append its own
$redirect_url = strtok($redirect_url, '?');


$chargeData = [
    'amount'           => $amount_kes,
    'currency'         => 'KES',
    'reference'        => $merchant_txn_id,
    'description'      => $KORA_PLANS[$plan]['name'] . ' — BM FOREX HUB',
    'notification_url' => $webhook_url,
    'redirect_url'     => $redirect_url,
    'customer_name'    => !empty($card_name) ? $card_name : ($user['user_metadata']['username'] ?? 'BM Forex Trader'),
    'customer_email'   => $user_email,
    'phone'            => $phone,
    'payment_method'   => $method,
    'card_name'        => $card_name,
    'card_number'      => $card_number,
    'card_expiry'      => $card_expiry,
    'card_cvv'         => $card_cvv,
];

$koraResult = $koraService->initializePayment($chargeData);

if ($koraResult['success']) {
    // For Card payments, update status to succeeded & create active subscription immediately
    if ($method === 'card') {
        supabase_request(
            "$supabase_url/rest/v1/payments?merchant_txn_id=eq.$merchant_txn_id",
            $service_key,
            'PATCH',
            ['status' => 'succeeded', 'processor_ref' => 'CARD_' . bin2hex(random_bytes(6))]
        );

        $plan_config = $KORA_PLANS[$plan] ?? null;
        $duration    = $plan_config['duration_days'] ?? 30;

        $sub_data = [
            'user_id'    => $user_id,
            'plan'       => $plan,
            'starts_at'  => date('c'),
            'expires_at' => date('c', strtotime("+{$duration} days")),
            'status'     => 'active',
        ];

        supabase_request(
            "$supabase_url/rest/v1/subscriptions",
            $service_key,
            'POST',
            $sub_data
        );
    }

    $respData = [
        'ok'                    => true,
        'message'               => $koraResult['message'] ?: ($method === 'card' ? 'Card payment processed successfully!' : 'Redirecting to Kora Pay...'),
        'merchantTransactionId' => $merchant_txn_id,
    ];
    if (!empty($koraResult['checkout_url'])) {
        $respData['checkout_url'] = $koraResult['checkout_url'];
    }
    send_json($respData);
}

$error_msg = $koraResult['message'] ?: 'Payment request failed';
supabase_request(
    "$supabase_url/rest/v1/payments?merchant_txn_id=eq.$merchant_txn_id",
    $service_key,
    'PATCH',
    ['status' => 'failed', 'failure_reason' => $error_msg]
);

send_json(['error' => $error_msg], 400);


// ══════════════════════════════════════════════════════════════════
// Helper Functions
// ══════════════════════════════════════════════════════════════════

function normalize_phone($phone) {
    $phone = preg_replace('/[\s\-()]/', '', trim($phone));
    if (preg_match('/^0(7|1)\d{8}$/', $phone)) {
        return '+254' . substr($phone, 1);
    }
    if (preg_match('/^254(7|1)\d{8}$/', $phone)) {
        return '+' . $phone;
    }
    if (preg_match('/^\+254(7|1)\d{8}$/', $phone)) {
        return $phone;
    }
    return null;
}

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

    $supabase_url  = SUPABASE_URL;
    $supabase_anon = SUPABASE_ANON_KEY;

    $ctx = stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer $token\r\napikey: $supabase_anon",
            'timeout' => 5,
        ],
    ]);

    $resp = @file_get_contents("$supabase_url/auth/v1/user", false, $ctx);
    if (!$resp) return null;
    $user = json_decode($resp, true);
    return isset($user['id']) ? $user : null;
}

function supabase_request($url, $service_key, $method = 'GET', $data = null) {
    $headers = [
        "apikey: $service_key",
        "Authorization: Bearer $service_key",
        "Content-Type: application/json",
        "Prefer: return=representation",
    ];
    $payload = $data ? json_encode($data) : null;

    // Try cURL first (more reliable on shared hosting)
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $decoded = $response ? json_decode($response, true) : null;
        if ($decoded === null && !empty($response)) {
            @error_log('BMFH supabase_request JSON decode error: ' . substr($response, 0, 500));
        }
        return $decoded;
    }

    // Fallback: stream context
    $ctx = stream_context_create([
        'http' => [
            'method'  => $method,
            'header'  => implode("\r\n", $headers),
            'content' => $payload,
            'timeout' => 10,
            'ignore_errors' => true,
        ],
    ]);
    $resp = @file_get_contents($url, false, $ctx);
    if ($resp === false) {
        @error_log('BMFH supabase_request FAILED: ' . $method . ' ' . $url);
        return null;
    }
    $decoded = json_decode($resp, true);
    if ($decoded === null && !empty($resp)) {
        @error_log('BMFH supabase_request JSON decode error: ' . substr($resp, 0, 500));
    }
    return $decoded;
}
