<?php
require_once __DIR__ . '/../app/Services/KoraPaymentService.php';

use App\Services\KoraPaymentService;

// Load .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            putenv("$key=$value");
            $_SERVER[$key] = $value;
        }
    }
}

$koraService = new KoraPaymentService();

// Generate reference named testpay
$reference = 'testpay_' . time();
$amount = 5.0; // 5 KES
$currency = 'KES';

$paymentData = [
    'amount'           => $amount,
    'currency'         => $currency,
    'reference'        => $reference,
    'description'      => 'testpay — 5 KES Test Transaction',
    'notification_url' => getenv('KORA_WEBHOOK_URL') ?: 'https://bmforexhub.exchange/api/kora-webhook.php',
    'redirect_url'     => getenv('KORA_SUCCESS_URL') ?: 'https://bmforexhub.exchange/subscribe.php',
    'customer_name'    => 'TestPay Customer',
    'customer_email'   => 'testpay@bmforexhub.exchange',
];

echo "Initializing Kora Checkout payment for 5 KES (Ref: $reference)...\n";

$result = $koraService->initializePayment($paymentData);

echo "\nResult:\n";
print_r($result);

if (!empty($result['checkout_url'])) {
    echo "\n=======================================================\n";
    echo "CHECKOUT URL: " . $result['checkout_url'] . "\n";
    echo "REFERENCE:    " . $reference . "\n";
    echo "AMOUNT:       " . $amount . " KES\n";
    echo "=======================================================\n";
}
