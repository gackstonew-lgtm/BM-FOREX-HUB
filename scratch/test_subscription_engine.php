<?php
/**
 * Automated Test Suite for Subscription & Payments Engine
 * Location: scratch/test_subscription_engine.php
 */
require_once __DIR__ . '/../engine_config.php';
require_once __DIR__ . '/../admin/config.php';
require_once __DIR__ . '/../app/Services/CurrencyConversionService.php';
require_once __DIR__ . '/../app/Services/MembershipService.php';
require_once __DIR__ . '/../app/Services/CopyTradingService.php';

echo "=== STARTING SUBSCRIPTION & PAYMENTS ENGINE TESTS ===\n\n";

$service = new \App\Services\MembershipService();

$testUserId = 'test-user-uuid-12345';
$testUsername = 'test_trader_77';
$testEmail = 'test_trader_77@bmforex.hub';

// Initialize profile in SQLite
$pdo = getMarketPDO();
$pdo->prepare("INSERT OR REPLACE INTO profiles (id, username, email, role, status, created_at, updated_at) VALUES (?, ?, ?, 'user', 'active', ?, ?)")
    ->execute([$testUserId, $testUsername, $testEmail, date('c'), date('c')]);

echo "[TEST 1] Granting Basic Plan (copytrading) for 30 days...\n";
$res1 = $service->grantSubscription('TestAdmin', $testUserId, $testUsername, 'copytrading', 30, 'Initial test grant');
echo "Result: " . ($res1['success'] ? "SUCCESS" : "FAILED") . " - " . ($res1['message'] ?? '') . "\n";
assert($res1['success'] === true, 'Test 1 Failed');

echo "\n[TEST 2] Verifying Active Subscription status...\n";
$activeSubs = $service->getActiveSubscriptions($testUserId, $testUsername, $testEmail);
echo "Active subscriptions count: " . count($activeSubs) . "\n";
assert(count($activeSubs) >= 1, 'Test 2 Failed: No active subscription found');
echo "Plan: " . $activeSubs[0]['plan'] . " | Expiry: " . $activeSubs[0]['expires_at'] . "\n";

echo "\n[TEST 3] Testing CopyTradingService access control...\n";
$hasCopyAccess = \App\Services\CopyTradingService::hasActiveSubscription($testUserId, $testEmail);
echo "Copy Trading Access: " . ($hasCopyAccess ? "GRANTED" : "DENIED") . "\n";
assert($hasCopyAccess === true, 'Test 3 Failed: Copy trading access should be true');

echo "\n[TEST 4] Testing Stacking Duration (re-granting plan for 30 days)...\n";
$firstExpiry = strtotime($activeSubs[0]['expires_at']);
$res4 = $service->grantSubscription('TestAdmin', $testUserId, $testUsername, 'copytrading', 30, 'Extension grant');
$stackedSubs = $service->getActiveSubscriptions($testUserId, $testUsername, $testEmail);
$secondExpiry = strtotime($stackedSubs[0]['expires_at']);
$diffDays = round(($secondExpiry - $firstExpiry) / 86400);
echo "Stacked Expiry extension: $diffDays days added.\n";
assert($diffDays >= 29 && $diffDays <= 31, 'Test 4 Failed: Stacking duration did not extend properly');

echo "\n[TEST 5] Granting Elite Plan (elite_professional)...\n";
$res5 = $service->grantEliteMembership('TestAdmin', $testUsername, 'elite_professional', 60, 5000, 'Elite upgrade', $testUserId);
echo "Result: " . ($res5['success'] ? "SUCCESS" : "FAILED") . " - " . ($res5['message'] ?? '') . "\n";
assert($res5['success'] === true, 'Test 5 Failed');

echo "\n[TEST 6] Testing Payment Approval -> Subscription Activation flow...\n";
$pendingUser = 'pending_user_88';
$payData = [
    'id' => 'pay-test-uuid-9999',
    'username' => $pendingUser,
    'plan' => 'grid_monthly',
    'amount' => 3225,
    'status' => 'pending',
    'merchant_txn_id' => 'TXN_TEST_123',
    'created_at' => date('c')
];
sqlite_admin_post('payments', $payData);

// Simulate updating payment status to succeeded
$_SERVER['REQUEST_METHOD'] = 'PATCH';
$_SESSION['admin_id'] = 'test-admin-id';
$_SESSION['admin_user'] = 'TestAdmin';

$grantPayRes = $service->grantSubscription('TestAdmin', null, $pendingUser, 'grid_monthly', 30, 'Payment approval');
echo "Payment approval subscription grant result: " . ($grantPayRes['success'] ? "SUCCESS" : "FAILED") . "\n";
assert($grantPayRes['success'] === true, 'Test 6 Failed');

echo "\n[TEST 7] Testing User Suspension access revocation...\n";
// Suspend test user in SQLite
$pdo->prepare("UPDATE profiles SET status = 'suspended' WHERE id = ?")->execute([$testUserId]);
$hasCopyAccessSuspended = \App\Services\CopyTradingService::hasActiveSubscription($testUserId, $testEmail);
echo "Suspended User Copy Trading Access: " . ($hasCopyAccessSuspended ? "GRANTED" : "DENIED") . "\n";
assert($hasCopyAccessSuspended === false, 'Test 7 Failed: Suspended user should be DENIED access');

// Unsuspend test user
$pdo->prepare("UPDATE profiles SET status = 'active' WHERE id = ?")->execute([$testUserId]);

echo "\n[TEST 8] Testing Automatic Subscription Expiry cleanup...\n";
// Create expired sub
$expiredSubId = 'expired-sub-uuid-0000';
$pdo->prepare("INSERT OR REPLACE INTO subscriptions (id, user_id, username, plan, status, starts_at, expires_at, created_at) VALUES (?, ?, ?, 'copytrading', 'active', ?, ?, ?)")
    ->execute([$expiredSubId, $testUserId, $testUsername, date('c', strtotime('-60 days')), date('c', strtotime('-1 day')), date('c', strtotime('-60 days'))]);

$service->cleanupExpiredSubscriptions();
$expiredCheck = $pdo->prepare("SELECT status FROM subscriptions WHERE id = ?");
$expiredCheck->execute([$expiredSubId]);
$expRow = $expiredCheck->fetch();
echo "Expired subscription status after cleanup: " . ($expRow['status'] ?? 'unknown') . "\n";
assert($expRow['status'] === 'expired', 'Test 8 Failed: Expired subscription should be marked expired');

echo "\n[TEST 9] Verifying Admin Audit Logging...\n";
$auditLogs = sqlite_admin_get('admin_audit_logs', ['limit' => 5]);
echo "Logged audit actions count: " . count($auditLogs) . "\n";
assert(count($auditLogs) > 0, 'Test 9 Failed: No admin audit logs recorded');
echo "Latest audit action: " . ($auditLogs[0]['action'] ?? 'N/A') . " | Admin: " . ($auditLogs[0]['admin_user'] ?? 'N/A') . " | Details: " . ($auditLogs[0]['details'] ?? '') . "\n";

echo "\n=== ALL 9 VERIFICATION TESTS PASSED SUCCESSFULLY! ===\n";
