<?php
// Final validation — test the full deposit.php flow simulation
require_once 'api/kora-config.php';

echo "=== KORA PAY CONFIGURATION VALIDATION ===\n";

// 1. Check all keys are loaded
$checks = [
    'KORA_SECRET_KEY'     => getenv('KORA_SECRET_KEY'),
    'KORA_PUBLIC_KEY'     => getenv('KORA_PUBLIC_KEY'),
    'KORA_ENCRYPTION_KEY' => getenv('KORA_ENCRYPTION_KEY'),
    'KORA_BASE_URL'       => getenv('KORA_BASE_URL'),
    'KORA_SUCCESS_URL'    => $_SERVER['KORA_SUCCESS_URL'] ?? getenv('KORA_SUCCESS_URL'),
    'KORA_FAILED_URL'     => $_SERVER['KORA_FAILED_URL']  ?? getenv('KORA_FAILED_URL'),
];

$allOk = true;
foreach ($checks as $k => $v) {
    $ok = !empty($v);
    $allOk = $allOk && $ok;
    echo ($ok ? "✓" : "✗") . " $k: " . ($ok ? substr($v, 0, 30) . (strlen($v) > 30 ? '...' : '') : 'MISSING') . "\n";
}

echo "\n";

// 2. Check redirect URLs are clean (no pre-existing query params)
$successUrl = $_SERVER['KORA_SUCCESS_URL'] ?? getenv('KORA_SUCCESS_URL');
$cleanSuccess = strtok($successUrl, '?');
if (strpos($successUrl, '?') !== false) {
    echo "⚠ KORA_SUCCESS_URL has pre-existing query params — will be stripped to: $cleanSuccess\n";
} else {
    echo "✓ KORA_SUCCESS_URL is clean: $successUrl\n";
}

// 3. Verify plans are loaded
echo "\n=== PLANS LOADED ===\n";
foreach ($KORA_PLANS as $k => $p) {
    echo "✓ $k: {$p['name']} — KES " . number_format($p['amount_kes']) . " / USD " . number_format($p['amount_usd']) . "\n";
}

// 4. Test KoraPaymentService instantiation
echo "\n=== SERVICE INSTANTIATION ===\n";
use App\Services\KoraPaymentService;
$svc = new KoraPaymentService();
echo "✓ KoraPaymentService instantiated successfully\n";

echo "\n=== VALIDATION " . ($allOk ? "PASSED" : "FAILED") . " ===\n";
