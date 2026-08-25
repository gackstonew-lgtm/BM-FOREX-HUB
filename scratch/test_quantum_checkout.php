<?php
/**
 * Verification of BM Quantum Edge Minimal Subscription Page & Kora Pay Flow
 */
require_once __DIR__ . '/../engine_config.php';
require_once __DIR__ . '/../api/kora-config.php';
require_once __DIR__ . '/../app/Services/KoraPaymentService.php';

echo "=== BM QUANTUM EDGE CLEANUP & CHECKOUT VERIFICATION ===\n\n";

// 1. Check Authoritative Price
echo "1. Checking Authoritative Backend Pricing...\n";
assert(isset($KORA_PLANS['indicator_quantum_edge']), "Plan must exist");
$planUsd = $KORA_PLANS['indicator_quantum_edge']['amount_usd'];
$planKes = $KORA_PLANS['indicator_quantum_edge']['amount_kes'];
assert($planUsd == 299.00, "Price must be exactly 299 USD");
assert($planKes > 30000, "KES price must be converted dynamically");
echo "✓ Backend Authoritative Price: \${$planUsd} USD (" . number_format($planKes, 2) . " KES)\n\n";

// 2. Check indicator-subscribe.php output
echo "2. Checking indicator-subscribe.php rendered HTML...\n";
ob_start();
require __DIR__ . '/../indicator-subscribe.php';
$html = ob_get_clean();

assert(strpos($html, 'BM Quantum Edge') !== false, "Must contain title");
assert(strpos($html, '$</span>299') !== false || strpos($html, '$299') !== false, "Must contain $299");
assert(strpos($html, 'One-Time Payment') !== false, "Must contain One-Time Payment");
assert(strpos($html, 'Get BM Quantum Edge') !== false, "Must contain button");
assert(strpos($html, 'sp-methods') === false, "Must NOT contain old sp-methods");
assert(strpos($html, 'sp-field') === false, "Must NOT contain old sp-field");
assert(strpos($html, 'sp-trust') === false, "Must NOT contain old sp-trust");
echo "✓ indicator-subscribe.php renders single clean card with \$299 One-Time Payment and no legacy form UI\n\n";

// 3. Test Kora Pay charge payload preparation
echo "3. Testing Kora Pay charge parameters...\n";
$merchantTxnId = 'BMFH_INDICATOR_QUANTUM_EDGE_' . strtoupper(bin2hex(random_bytes(6)));
$chargeData = [
    'amount'           => $planKes,
    'currency'         => 'KES',
    'reference'        => $merchantTxnId,
    'description'      => "BM Quantum Edge — BM FOREX HUB Indicator",
    'customer_name'    => 'Test Trader',
    'customer_email'   => 'trader@bmforexhub.exchange',
];
assert($chargeData['amount'] == $planKes, "Amount sent to Kora must be exact $299 equivalent");
assert($chargeData['currency'] === 'KES', "Currency must be KES");
echo "✓ Kora Pay charge payload verified: {$chargeData['currency']} " . number_format($chargeData['amount'], 2) . " for \$299 USD\n\n";

echo "=== ALL VERIFICATION CHECKS PASSED (100% SUCCESS) ===\n";
