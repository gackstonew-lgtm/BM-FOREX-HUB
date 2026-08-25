<?php
require_once __DIR__ . '/../app/Services/KoraPaymentService.php';
use App\Services\KoraPaymentService;

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

$koraService = new KoraPaymentService();

$testAmounts = [10, 20, 50, 100];

foreach ($testAmounts as $amt) {
    $ref = 'testpay_' . $amt . '_' . time();
    $paymentData = [
        'amount'           => (float)$amt,
        'currency'         => 'KES',
        'reference'        => $ref,
        'description'      => "testpay — $amt KES Test Transaction",
        'notification_url' => getenv('KORA_WEBHOOK_URL') ?: 'https://bmforexhub.exchange/api/kora-webhook.php',
        'redirect_url'     => getenv('KORA_SUCCESS_URL') ?: 'https://bmforexhub.exchange/subscribe.php',
        'customer_name'    => 'TestPay Customer',
        'customer_email'   => 'testpay@bmforexhub.exchange',
    ];

    echo "Testing Kora Checkout for $amt KES (Ref: $ref)...\n";
    $result = $koraService->initializePayment($paymentData);
    echo "  Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
    echo "  Message: " . ($result['message'] ?? '') . "\n";
    if (!empty($result['checkout_url'])) {
        echo "  Checkout URL: " . $result['checkout_url'] . "\n";
    }
    echo "---------------------------------------------------\n";
}
