<?php
/**
 * BM Forex Hub — Kora Pay Test Checkout Endpoint (testpay)
 * Allows testing live Kora Pay checkout transactions for testing and verification.
 */
require_once __DIR__ . '/app/Services/KoraPaymentService.php';
use App\Services\KoraPaymentService;

// Load .env if present
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
            $_SERVER[trim($key)] = trim($value);
        }
    }
}

$ref = 'testpay_' . time();
$requestedAmount = isset($_GET['amount']) ? (float)$_GET['amount'] : 10.0;
// Kora Pay channel minimum is 10 KES
$amount = max(10.0, $requestedAmount);

$koraService = new KoraPaymentService();

$paymentData = [
    'amount'           => $amount,
    'currency'         => 'KES',
    'reference'        => $ref,
    'description'      => 'testpay — BM FOREX HUB Kora Test Payment',
    'notification_url' => getenv('KORA_WEBHOOK_URL') ?: 'https://bmforexhub.exchange/api/kora-webhook.php',
    'redirect_url'     => getenv('KORA_SUCCESS_URL') ?: 'https://bmforexhub.exchange/subscribe.php',
    'customer_name'    => 'TestPay Customer',
    'customer_email'   => 'testpay@bmforexhub.exchange',
];

$result = $koraService->initializePayment($paymentData);

if ($result['success'] && !empty($result['checkout_url'])) {
    if (isset($_GET['redirect']) && $_GET['redirect'] === '1') {
        header('Location: ' . $result['checkout_url']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kora Pay Test Payment — BM Forex Hub</title>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; background: #0B1220; color: #F8FAFC; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .card { background: rgba(16, 24, 38, 0.95); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 32px; max-width: 480px; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,0.5); text-align: center; }
        .logo { font-size: 1.5rem; font-weight: 800; color: #D4A24C; margin-bottom: 8px; }
        .title { font-size: 1.25rem; font-weight: 700; margin-bottom: 16px; }
        .badge { display: inline-block; background: rgba(0, 200, 83, 0.15); color: #00C853; font-size: 0.85rem; font-weight: 600; padding: 4px 12px; border-radius: 20px; margin-bottom: 20px; }
        .info-box { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px; padding: 16px; margin-bottom: 24px; text-align: left; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem; }
        .info-row:last-child { margin-bottom: 0; }
        .lbl { color: #94A3B8; }
        .val { font-weight: 600; font-family: monospace; color: #F8FAFC; }
        .cta-btn { display: inline-block; width: 100%; padding: 14px; background: linear-gradient(135deg, #D4A24C, #B8860B); color: #0B1220; font-weight: 700; font-size: 1rem; border: none; border-radius: 8px; text-decoration: none; cursor: pointer; transition: transform 0.2s; box-sizing: border-box; }
        .cta-btn:hover { transform: translateY(-2px); opacity: 0.95; }
        .note { margin-top: 16px; font-size: 0.78rem; color: #64748B; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">BM FOREX HUB</div>
        <div class="title">Kora Pay Checkout Confirmation</div>
        <div class="badge">Reference: <?php echo htmlspecialchars($ref); ?></div>
        
        <div class="info-box">
            <div class="info-row"><span class="lbl">Payment Name:</span><span class="val">testpay</span></div>
            <div class="info-row"><span class="lbl">Transaction Ref:</span><span class="val"><?php echo htmlspecialchars($ref); ?></span></div>
            <div class="info-row"><span class="lbl">Amount:</span><span class="val"><?php echo number_format($amount, 2); ?> KES</span></div>
            <div class="info-row"><span class="lbl">Gateway:</span><span class="val">Kora Pay Checkout</span></div>
            <div class="info-row"><span class="lbl">Channel Minimum:</span><span class="val">10 KES (Kora Channel limit)</span></div>
        </div>

        <?php if ($result['success'] && !empty($result['checkout_url'])): ?>
            <a href="<?php echo htmlspecialchars($result['checkout_url']); ?>" class="cta-btn">Proceed to Kora Checkout Page &rarr;</a>
        <?php else: ?>
            <div style="color: #FF5252; font-size: 0.9rem; padding: 12px; background: rgba(255,82,82,0.1); border-radius: 8px;">
                <?php echo htmlspecialchars($result['message'] ?? 'Unable to initialize Kora Pay session.'); ?>
            </div>
        <?php endif; ?>

        <p class="note">Note: Kora Pay sets a minimum transaction limit of 10 KES per payment channel. Once paid, the webhook automatically verifies the transaction in Kora Pay.</p>
    </div>
</body>
</html>
