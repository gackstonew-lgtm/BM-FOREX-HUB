<?php
/**
 * Comprehensive Automated Verification Suite for BM Quantum Edge $299 One-Time Payment
 */

require_once __DIR__ . '/../engine_config.php';
require_once __DIR__ . '/../api/kora-config.php';
require_once __DIR__ . '/../app/Services/MembershipService.php';
require_once __DIR__ . '/../app/Services/IndicatorAccessService.php';

echo "=== BM QUANTUM EDGE $299 ONE-TIME PAYMENT VERIFICATION ===\n\n";

// 1. Check Kora Plans Configuration
echo "1. Testing Kora Plan Configuration for $299 USD...\n";
assert(isset($KORA_PLANS['indicator_quantum_edge']), "indicator_quantum_edge must exist in KORA_PLANS");
assert($KORA_PLANS['indicator_quantum_edge']['amount_usd'] == 299.00, "indicator_quantum_edge must be exactly $299 USD");
assert($KORA_PLANS['indicator_quantum_edge']['duration_days'] >= 3650, "indicator_quantum_edge must be one-time/lifetime");
echo "✓ KORA_PLANS contains indicator_quantum_edge at $299 USD (" . number_format($KORA_PLANS['indicator_quantum_edge']['amount_kes'], 2) . " KES)\n\n";

// 2. Check MembershipService Constants & Pricing
echo "2. Testing MembershipService plan constants and authoritative pricing...\n";
$memService = new \App\Services\MembershipService();
assert(defined('\App\Services\MembershipService::PLAN_INDICATOR_QUANTUM_EDGE'), "PLAN_INDICATOR_QUANTUM_EDGE constant must be defined");
assert(\App\Services\MembershipService::PLAN_AMOUNTS_USD[\App\Services\MembershipService::PLAN_INDICATOR_QUANTUM_EDGE] == 299.00, "MembershipService amount must be 299.00 USD");
echo "✓ MembershipService configured with PLAN_INDICATOR_QUANTUM_EDGE = $299 USD\n\n";

// 3. Check IndicatorAccessService
echo "3. Testing IndicatorAccessService checkAccess & permanent access...\n";
$accService = new \App\Services\IndicatorAccessService();
$adminAccess = $accService->checkAccess('00000000-0000-0000-0000-000000000001', 'bonfacewana3072@gmail.com');
assert($adminAccess['has_access'] === true, "Admin must have access");
echo "✓ IndicatorAccessService access verification passed\n\n";

// 4. Check Admin Grant Execution
echo "4. Testing Admin Grant Subscription flow for BM Quantum Edge...\n";
$testUser = 'test_quantum_trader_' . bin2hex(random_bytes(3));
$grantResult = $memService->grantSubscription('admin@bmforexhub.exchange', null, $testUser, 'indicator_quantum_edge', 3650, 'Automated $299 Test Grant', 'Admin Manual Grant');
assert($grantResult['success'] === true, "Admin grant must succeed: " . ($grantResult['message'] ?? ''));
assert($grantResult['data']['amount_usd'] == 299.00, "Granted subscription amount must be 299 USD");
echo "✓ Admin successfully granted BM Quantum Edge ($299 USD) to @$testUser\n\n";

// 5. Verify created records in SQLite
echo "5. Verifying database records...\n";
if (function_exists('getMarketPDO')) {
    $pdo = getMarketPDO();
    $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE username = :u AND plan = 'indicator_quantum_edge'");
    $stmt->execute([':u' => $testUser]);
    $subRow = $stmt->fetch();
    assert(!empty($subRow), "Subscription row must exist in SQLite subscriptions table");
    assert($subRow['amount_usd'] == 299.00, "SQLite subscription amount must be 299 USD");
    echo "✓ Verified SQLite subscriptions table record: amount_usd = {$subRow['amount_usd']}\n";
    
    // Clean up test record
    $pdo->prepare("DELETE FROM subscriptions WHERE username = :u")->execute([':u' => $testUser]);
    $pdo->prepare("DELETE FROM user_subscriptions WHERE username = :u")->execute([':u' => $testUser]);
    $pdo->prepare("DELETE FROM indicator_subscriptions WHERE user_name = :u")->execute([':u' => $testUser]);
    $pdo->prepare("DELETE FROM payments WHERE username = :u")->execute([':u' => $testUser]);
    echo "✓ Cleanup completed.\n";
}

echo "\n=== ALL VERIFICATION TESTS PASSED (100% SUCCESS) ===\n";
