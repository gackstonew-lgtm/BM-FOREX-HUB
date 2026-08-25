<?php
require_once __DIR__ . '/../engine_config.php';
require_once __DIR__ . '/../admin/config.php';
require_once __DIR__ . '/../app/Services/MembershipService.php';

echo "=== BM FOREX HUB - BM ELITES VERIFICATION SUITE ===\n";

$service = new \App\Services\MembershipService();

// 1. Test Granting BM Elites Plan
$testUsername = 'elitetrader.test';
$testUserId = 'user-test-uuid-999';

echo "\n1. Testing grantEliteMembership for @$testUsername...\n";
$grantRes = $service->grantEliteMembership(
    'AdminTester',
    $testUsername,
    \App\Services\MembershipService::PLAN_ELITE_ELITE,
    30,
    10000,
    'Automated Test Suite Grant',
    $testUserId
);

echo "Grant Result: " . json_encode($grantRes, JSON_PRETTY_PRINT) . "\n";
assert($grantRes['success'] === true, "Grant must succeed");

// 2. Test getActiveSubscriptions
echo "\n2. Testing getActiveSubscriptions for @$testUsername...\n";
$activeSubs = $service->getActiveSubscriptions($testUserId, $testUsername);
echo "Found " . count($activeSubs) . " active subscription(s):\n";
echo json_encode($activeSubs, JSON_PRETTY_PRINT) . "\n";
assert(count($activeSubs) >= 1, "Must find at least 1 active subscription");
assert($activeSubs[0]['plan'] === \App\Services\MembershipService::PLAN_ELITE_ELITE, "Plan must be elite_elite");

// 3. Test getAllEliteMemberships
echo "\n3. Testing getAllEliteMemberships for Admin view...\n";
$allElites = $service->getAllEliteMemberships();
echo "Consolidated Elite Members count: " . count($allElites) . "\n";
$foundTest = false;
foreach ($allElites as $em) {
    if (strtolower($em['username']) === strtolower($testUsername)) {
        $foundTest = true;
        echo "Found test user in Elite list: " . json_encode($em) . "\n";
        break;
    }
}
assert($foundTest === true, "Test user must appear in consolidated Elite list");

// 4. Test Permanent Admin Access recognition
echo "\n4. Testing permanent admin access recognition...\n";
$permEmail = 'bonfacewana3072@gmail.com';
assert(bm_has_permanent_access($permEmail) === true, "Permanent admin must return true");

echo "\n=== ALL VERIFICATION TESTS PASSED SUCCESSFULLY! ===\n";
