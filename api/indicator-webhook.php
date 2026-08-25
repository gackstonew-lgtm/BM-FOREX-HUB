<?php
/**
 * POST /api/indicator-webhook.php
 * Handles Kora Pay payment webhooks for indicator subscriptions.
 *
 * Verifies HMAC signature, activates subscription on success,
 * sends confirmation email, notifies admin.
 * Idempotent — safe to call multiple times for the same transaction.
 */

// Ensure logs directory exists
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) @mkdir($logDir, 0755, true);

$rawBody  = file_get_contents('php://input');
$logEntry = date('c') . ' | IND_WEBHOOK | ' . $rawBody . "\n";
@file_put_contents($logDir . '/indicator_webhooks.log', $logEntry, FILE_APPEND | LOCK_EX);

header('Content-Type: application/json');

require_once __DIR__ . '/kora-config.php';
require_once __DIR__ . '/../app/Services/IndicatorAccessService.php';
require_once __DIR__ . '/../app/Services/TradingViewSyncService.php';

use App\Services\KoraPaymentService;
use App\Services\IndicatorAccessService;

// ── Parse webhook payload ────────────────────────────────────────────
$event = json_decode($rawBody, true);
if (!$event) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}

// ── Verify HMAC signature ────────────────────────────────────────────
$sigHeader  = $_SERVER['HTTP_X_KORAPAY_SIGNATURE']
           ?? $_SERVER['HTTP_X_FINGO_SIGNATURE']
           ?? '';

$koraService = new KoraPaymentService();
if ($sigHeader && !$koraService->verifyWebhookSignature($sigHeader, $rawBody)) {
    $logErr = date('c') . " | SIGNATURE_FAILED | Header: $sigHeader\n";
    @file_put_contents($logDir . '/indicator_webhooks.log', $logErr, FILE_APPEND | LOCK_EX);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid webhook signature']);
    exit;
}

// ── Extract event data ───────────────────────────────────────────────
$eventType      = $event['event'] ?? ($event['type'] ?? '');
$data           = $event['data']  ?? [];
$merchantTxnId  = $data['reference'] ?? ($data['merchantTransactionId'] ?? '');
$status         = strtolower($data['status'] ?? '');
$processorRef   = $data['kora_reference'] ?? ($data['processorReference'] ?? ($data['id'] ?? ''));
$message        = $data['processor_response'] ?? ($data['message'] ?? 'Completed');

// Only process indicator plan payments (BMFH_INDICATOR_*)
if (!$merchantTxnId || strpos($merchantTxnId, 'BMFH_INDICATOR_') !== 0) {
    http_response_code(200);
    echo json_encode(['ok' => true, 'message' => 'Not an indicator payment — ignored']);
    exit;
}

$supabaseUrl = SUPABASE_URL;
$serviceKey  = SUPABASE_SERVICE_KEY;

// ── Find payment record ──────────────────────────────────────────────
$payments = supabase_request(
    "$supabaseUrl/rest/v1/payments?merchant_txn_id=eq.$merchantTxnId&limit=1",
    $serviceKey
);

if (!$payments || count($payments) === 0) {
    $log = date('c') . " | UNKNOWN_TXN: $merchantTxnId\n";
    @file_put_contents($logDir . '/indicator_webhooks.log', $log, FILE_APPEND | LOCK_EX);
    http_response_code(200);
    echo json_encode(['ok' => true, 'message' => 'Payment not found — ignored']);
    exit;
}

$payment = $payments[0];

// ── Idempotency guard ────────────────────────────────────────────────
if (in_array($payment['status'], ['succeeded', 'failed'])) {
    http_response_code(200);
    echo json_encode(['ok' => true, 'message' => 'Already processed']);
    exit;
}

// ── Determine outcome ────────────────────────────────────────────────
$isSuccess = ($eventType === 'charge.success' || $eventType === 'transaction.succeeded'
           || $status === 'success' || $status === 'succeeded');
$isFailed  = ($eventType === 'charge.failed'  || $eventType === 'transaction.failed'
           || $status === 'failed' || $status === 'expired');

// Update payment status
supabase_request(
    "$supabaseUrl/rest/v1/payments?merchant_txn_id=eq.$merchantTxnId",
    $serviceKey, 'PATCH',
    ['status' => $isSuccess ? 'succeeded' : ($isFailed ? 'failed' : $payment['status']),
     'processor_ref' => $processorRef, 'raw_webhook' => $event]
);

if ($isSuccess) {
    // ── Activate indicator subscription ──────────────────────────────
    $planKey  = $payment['plan'];
    $metadata = json_decode($payment['metadata'] ?? '{}', true) ?? [];
    $tvUser   = $metadata['tradingview_username'] ?? null;
    $duration = $metadata['duration_days'] ?? ($KORA_PLANS[$planKey]['duration_days'] ?? 30);

    $planFlags = [
        'signals_access'    => true,
        'ai_access'         => true,
        'premium_dashboard' => true,
    ];

    $accessService = new IndicatorAccessService();
    $result = $accessService->activateSubscription([
        'user_id'              => $payment['user_id'],
        'user_email'           => $payment['metadata'] ? (json_decode($payment['metadata'], true)['email'] ?? '') : '',
        'user_name'            => '',
        'plan_key'             => $planKey,
        'plan_name'            => $KORA_PLANS[$planKey]['name'] ?? $planKey,
        'payment_reference'    => $merchantTxnId,
        'amount_paid_usd'      => (float)($payment['amount_usd'] ?? 0),
        'amount_paid_kes'      => (float)($payment['amount_kes'] ?? 0),
        'tradingview_username' => $tvUser,
        'duration_days'        => (int)$duration,
        'signals_access'       => $planFlags['signals_access'],
        'ai_access'            => $planFlags['ai_access'],
        'premium_dashboard'    => $planFlags['premium_dashboard'],
    ]);

    // Log
    $accessService->logAction(
        $result['id'],
        $payment['user_id'],
        null,
        'subscription_activated',
        $planKey,
        ['via' => 'webhook', 'ref' => $merchantTxnId]
    );

    // Send confirmation email
    send_indicator_email($payment, 'success', $processorRef, $KORA_PLANS[$planKey] ?? [], $tvUser);

    $log = date('c') . " | ACTIVATED | Plan: $planKey | Ref: $merchantTxnId\n";
    @file_put_contents($logDir . '/indicator_webhooks.log', $log, FILE_APPEND | LOCK_EX);

} elseif ($isFailed) {
    send_indicator_email($payment, 'failed', $message, $KORA_PLANS[$payment['plan']] ?? [], null);

    $log = date('c') . " | FAILED | Ref: $merchantTxnId | Reason: $message\n";
    @file_put_contents($logDir . '/indicator_webhooks.log', $log, FILE_APPEND | LOCK_EX);
}

http_response_code(200);
echo json_encode(['ok' => true]);
exit;


// ── Email Notification ───────────────────────────────────────────────

function send_indicator_email(array $payment, string $type, string $detail, array $planConfig, ?string $tvUsername): void
{
    $supabaseUrl = SUPABASE_URL;
    $serviceKey  = SUPABASE_SERVICE_KEY;

    $userResp = supabase_request(
        "$supabaseUrl/auth/v1/admin/users/{$payment['user_id']}",
        $serviceKey
    );
    $email    = $userResp['email'] ?? null;
    $username = $userResp['user_metadata']['first_name'] ?? ($userResp['user_metadata']['username'] ?? 'Trader');
    if (!$email) return;

    $planName    = $planConfig['name'] ?? ($payment['plan'] ?? 'Indicator Plan');
    $amountKes   = number_format($payment['amount_kes'] ?? 0);
    $amountUsd   = isset($planConfig['amount_usd']) ? ' ($' . number_format($planConfig['amount_usd']) . ' USD)' : '';
    $amountDisp  = "KES $amountKes$amountUsd";
    $ref         = $payment['merchant_txn_id'];
    $expiresDate = date('M d, Y', strtotime('+30 days'));
    $tvLine      = $tvUsername
        ? "<p style='color:#B8C3D1;font-size:13px'>Your TradingView username <strong style='color:#1677FF'>@$tvUsername</strong> has been registered and our team will grant Pine Script access within 24 hours.</p>"
        : "<p style='color:#B8C3D1;font-size:13px'>Don't forget to add your TradingView username in your <a href='https://bmforexhub.exchange/trading.php' style='color:#1677FF'>Trading Dashboard</a> so we can grant your indicator access.</p>";

    if ($type === 'success') {
        $subject = "✅ Indicator Subscription Activated — $planName | BM FOREX HUB";
        $body = "
<div style='font-family:Inter,Helvetica,sans-serif;max-width:560px;margin:0 auto;background:#0B0F14;color:#FFFFFF;padding:40px 30px;border-radius:14px;border:1px solid #283548'>
  <div style='text-align:center;margin-bottom:30px'>
    <img src='https://bmforexhub.exchange/BM-ForexHub-Logo-Circle.png' width='60' style='border-radius:50%' alt='BM Forex Hub'>
    <h2 style='color:#1677FF;margin:12px 0 4px;font-size:18px'>Indicator Access Activated!</h2>
  </div>
  <p style='color:#B8C3D1;font-size:14px;line-height:1.7'>Hi <strong style='color:#fff'>$username</strong>,</p>
  <p style='color:#B8C3D1;font-size:14px;line-height:1.7'>Your <strong style='color:#1677FF'>$planName</strong> indicator subscription is now active. You can access your premium trading dashboard immediately.</p>
  <div style='background:#151D29;border:1px solid #283548;border-radius:10px;padding:20px;margin:24px 0'>
    <p style='margin:0 0 8px;font-size:12px;color:#7F8B99;text-transform:uppercase'>Plan</p>
    <p style='margin:0 0 16px;font-size:15px;color:#fff;font-weight:600'>$planName</p>
    <p style='margin:0 0 8px;font-size:12px;color:#7F8B99;text-transform:uppercase'>Amount Paid</p>
    <p style='margin:0 0 16px;font-size:15px;color:#fff;font-weight:600'>$amountDisp</p>
    <p style='margin:0 0 8px;font-size:12px;color:#7F8B99;text-transform:uppercase'>Transaction Reference</p>
    <p style='margin:0 0 16px;font-size:13px;color:#fff;font-family:monospace'>$ref</p>
    <p style='margin:0 0 8px;font-size:12px;color:#7F8B99;text-transform:uppercase'>Access Expires</p>
    <p style='margin:0;font-size:15px;color:#16C784;font-weight:600'>$expiresDate</p>
  </div>
  $tvLine
  <div style='text-align:center;margin:24px 0'>
    <a href='https://bmforexhub.exchange/trading.php' style='display:inline-block;background:#1677FF;color:#fff;text-decoration:none;padding:14px 28px;border-radius:10px;font-weight:600;font-size:14px'>Open Trading Dashboard →</a>
  </div>
  <div style='text-align:center;margin-top:30px;padding-top:20px;border-top:1px solid #283548'>
    <p style='color:#7F8B99;font-size:12px;margin:0'>BM FOREX HUB LTD | Nakuru, Kenya</p>
    <p style='color:#7F8B99;font-size:12px;margin:4px 0 0'>support@bmforexhub.exchange | +254 780 618 608</p>
  </div>
</div>";
    } else {
        $subject = "❌ Payment Failed — $planName | BM FOREX HUB";
        $body = "
<div style='font-family:Inter,Helvetica,sans-serif;max-width:560px;margin:0 auto;background:#0B0F14;color:#FFFFFF;padding:40px 30px;border-radius:14px;border:1px solid #283548'>
  <div style='text-align:center;margin-bottom:30px'>
    <img src='https://bmforexhub.exchange/BM-ForexHub-Logo-Circle.png' width='60' style='border-radius:50%' alt='BM Forex Hub'>
    <h2 style='color:#F6465D;margin:12px 0 4px;font-size:18px'>Payment Failed</h2>
  </div>
  <p style='color:#B8C3D1;font-size:14px;line-height:1.7'>Hi <strong style='color:#fff'>$username</strong>,</p>
  <p style='color:#B8C3D1;font-size:14px;line-height:1.7'>Your payment for <strong style='color:#1677FF'>$planName</strong> could not be processed. Reason: <strong style='color:#F6465D'>$detail</strong></p>
  <div style='text-align:center;margin:24px 0'>
    <a href='https://bmforexhub.exchange/indicator-subscribe.php' style='display:inline-block;background:#1677FF;color:#fff;text-decoration:none;padding:14px 28px;border-radius:10px;font-weight:600;font-size:14px'>Try Again →</a>
  </div>
  <div style='text-align:center;margin-top:30px;padding-top:20px;border-top:1px solid #283548'>
    <p style='color:#7F8B99;font-size:12px;margin:0'>BM FOREX HUB LTD | Nakuru, Kenya</p>
    <p style='color:#7F8B99;font-size:12px;margin:4px 0 0'>support@bmforexhub.exchange | +254 780 618 608</p>
  </div>
</div>";
    }

    // Use existing mail service
    $mailService = null;
    if (class_exists('\\App\\Services\\MailService')) {
        try {
            $mailService = new \App\Services\MailService();
            $mailService->send($email, $subject, $body);
        } catch (\Throwable $e) {}
    }

    // Also notify admin
    try {
        if ($mailService) {
            $adminSubject = "[BMFH] Indicator " . ucfirst($type) . " — $planName | $email";
            $mailService->send('info@bmforexhub.exchange', $adminSubject,
                "<p>Indicator payment $type for <strong>$email</strong>.<br>Plan: $planName<br>Ref: $ref<br>Detail: $detail</p>"
            );
        }
    } catch (\Throwable $e) {}
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
