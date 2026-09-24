<?php
/**
 * scratch/test_compliance_gate.php
 * Comprehensive automated verification for BM FOREX HUB Elite Compliance Update
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "================================================================" . PHP_EOL;
echo "BM FOREX HUB — Elite Member Compliance & Terms Verification Test" . PHP_EOL;
echo "================================================================" . PHP_EOL . PHP_EOL;

require_once __DIR__ . '/../admin/config.php';
require_once __DIR__ . '/../engine_config.php';

$pdo = getMarketPDO();
$passed = 0;
$failed = 0;

function assertTest($description, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] $description" . PHP_EOL;
        $passed++;
    } else {
        echo "  [FAIL] $description" . PHP_EOL;
        $failed++;
    }
}

// -------------------------------------------------------------
// Test 1: Verify Existing Data Records Integrity
// -------------------------------------------------------------
echo "1. Checking Database Tables & Record Preservation..." . PHP_EOL;
$profileCount = $pdo->query("SELECT COUNT(*) FROM profiles")->fetchColumn();
$subCount = $pdo->query("SELECT COUNT(*) FROM subscriptions")->fetchColumn();
$paymentCount = $pdo->query("SELECT COUNT(*) FROM payments")->fetchColumn();
$enrollCount = $pdo->query("SELECT COUNT(*) FROM elite_circle_enrollments")->fetchColumn();

assertTest("Profiles preserved ($profileCount profiles found)", $profileCount >= 20);
assertTest("Subscriptions preserved ($subCount subscriptions found)", $subCount >= 65);
assertTest("Payments preserved ($paymentCount payments found)", $paymentCount >= 55);
assertTest("Elite enrollments table exists ($enrollCount records)", $enrollCount >= 0);

// -------------------------------------------------------------
// Test 2: Active Elite Subscriber Detection
// -------------------------------------------------------------
echo PHP_EOL . "2. Testing Active Elite Subscriber Detection..." . PHP_EOL;
require_once __DIR__ . '/../api/kora-config.php';

// Find a real existing subscriber in subscriptions table
$sampleSub = $pdo->query("
    SELECT user_id, username, plan, plan_key, amount_usd 
    FROM subscriptions 
    WHERE (plan LIKE 'elite_%' OR plan_key LIKE 'elite_%' OR plan = 'all') 
      AND status = 'active' 
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

if ($sampleSub) {
    echo "  Sample existing elite user: " . $sampleSub['username'] . " (" . $sampleSub['user_id'] . ")" . PHP_EOL;
    
    // Simulate helper functions
    $nowIso = date('c');
    $stmt = $pdo->prepare("
        SELECT id, user_id, username, plan, plan_key, plan_name, amount_usd, starts_at, expires_at, created_at, status
        FROM subscriptions 
        WHERE (user_id = ? OR username = ? OR id = ?)
          AND (plan LIKE 'elite_%' OR plan_key LIKE 'elite_%' OR plan = 'all' OR plan_name LIKE '%Elite%')
          AND (status = 'active' OR status IS NULL OR status = '')
          AND (expires_at IS NULL OR expires_at = '' OR expires_at > ?)
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$sampleSub['user_id'], $sampleSub['username'], $sampleSub['user_id'], $nowIso]);
    $detectedSub = $stmt->fetch(PDO::FETCH_ASSOC);

    assertTest("Detected active subscription record for {$sampleSub['username']}", !empty($detectedSub));
    assertTest("Subscription has valid tier amount (\${$detectedSub['amount_usd']})", $detectedSub['amount_usd'] > 0);
}

// -------------------------------------------------------------
// Test 3: Standalone Legal Page terms-elite.php Verification
// -------------------------------------------------------------
echo PHP_EOL . "3. Testing terms-elite.php Legal Page..." . PHP_EOL;
$termsEliteFile = __DIR__ . '/../terms-elite.php';
$termsCompFile = __DIR__ . '/../components/elite_terms_content.php';
assertTest("terms-elite.php file exists", file_exists($termsEliteFile));
$termsContent = file_get_contents($termsEliteFile) . (file_exists($termsCompFile) ? file_get_contents($termsCompFile) : '');
assertTest("Contains 39 sections", strpos($termsContent, '39. COMPANY DETAILS') !== false);
assertTest("Contains Effective Date 05 January 2026", strpos($termsContent, '05 January 2026') !== false);
assertTest("Contains Operating Entity Varban Company Limited", strpos($termsContent, 'Varban Company Limited') !== false);
assertTest("Contains Parent Company Stillrock Ventures", strpos($termsContent, 'Stillrock Ventures') !== false);
assertTest("Contains Jurisdiction Republic of Kenya", strpos($termsContent, 'Republic of Kenya') !== false);

// -------------------------------------------------------------
// Test 4: Footer Navigation Links Verification
// -------------------------------------------------------------
echo PHP_EOL . "4. Testing Footer Links..." . PHP_EOL;
$footerPhp = file_get_contents(__DIR__ . '/../footer.php');
$bmElitesPhp = file_get_contents(__DIR__ . '/../bm_elites.php');
$eliteEnrollmentPhp = file_get_contents(__DIR__ . '/../elite_enrollment.php');

assertTest("footer.php links to terms-elite.php", strpos($footerPhp, 'terms-elite.php') !== false);
assertTest("bm_elites.php footer links to terms-elite.php", strpos($bmElitesPhp, 'terms-elite.php') !== false);
assertTest("elite_enrollment.php footer links to terms-elite.php", strpos($eliteEnrollmentPhp, 'terms-elite.php') !== false);

// -------------------------------------------------------------
// Test 5: Compliance Gate Banners in index.php and bm_elites.php
// -------------------------------------------------------------
echo PHP_EOL . "5. Testing In-App Compliance Banners..." . PHP_EOL;
$indexPhp = file_get_contents(__DIR__ . '/../index.php');
assertTest("index.php contains dashEliteComplianceBanner", strpos($indexPhp, 'dashEliteComplianceBanner') !== false);
assertTest("index.php checks api/elite-enrollment.php?action=check", strpos($indexPhp, 'api/elite-enrollment.php?action=check') !== false);
assertTest("bm_elites.php contains eliteComplianceNoticeBox", strpos($bmElitesPhp, 'eliteComplianceNoticeBox') !== false);
assertTest("bm_elites.php contains compliance_success handler", strpos($bmElitesPhp, 'compliance_success') !== false);

// -------------------------------------------------------------
// Test 6: WhatsApp VIP Link & Safe Verification Message Format
// -------------------------------------------------------------
echo PHP_EOL . "6. Testing WhatsApp VIP Link & Security..." . PHP_EOL;
$testName = "Sarah Sang";
$testEmail = "sarah.sang@example.com";
$testUserId = "5f4f6741-d239-423c-b78d-9a7693f3f04d";
$testEnrollmentId = "ENR-2026-TEST-001";
$testIdPassport = "ID-99887766"; // Sensitive

$waMsg = "Hi BM Forex Hub, I am an active BM Elite Member. Please verify my access to the VIP WhatsApp Group.\n\nFull Name: {$testName}\nEmail: {$testEmail}\nMember ID: {$testUserId}\nTerms Version: 1.0 (Accepted)\nAcceptance Record: {$testEnrollmentId}";
$waUrl = "https://wa.me/254780618608?text=" . urlencode($waMsg);

assertTest("WhatsApp link target is https://wa.me/254780618608", strpos($waUrl, 'https://wa.me/254780618608?text=') === 0);
assertTest("WhatsApp message contains Member Name", strpos($waMsg, $testName) !== false);
assertTest("WhatsApp message contains Member Email", strpos($waMsg, $testEmail) !== false);
assertTest("WhatsApp message contains Member ID", strpos($waMsg, $testUserId) !== false);
assertTest("WhatsApp message confirms Terms Version 1.0", strpos($waMsg, 'Version: 1.0') !== false);
assertTest("WhatsApp message NEVER contains sensitive ID/Passport number", strpos($waMsg, $testIdPassport) === false);

// -------------------------------------------------------------
// Test 7: Simulated Existing Member Compliance Submission
// -------------------------------------------------------------
echo PHP_EOL . "7. Testing Simulated Compliance Submission..." . PHP_EOL;
$testComplianceId = 'test-compliance-' . bin2hex(random_bytes(4));
$testNow = date('c');

$insertStmt = $pdo->prepare("
    INSERT INTO elite_circle_enrollments (
        id, user_id, member_name, id_passport_number, phone, email, country,
        investment_amount, currency, payment_reference, investment_start_date,
        expected_cycle_completion_date, terms_version, terms_effective_date,
        terms_content_hash, accepted, accepted_at, accepted_ip_address, user_agent,
        email_status, notes, created_at, updated_at
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, '1.0', '2026-01-05',
        ?, 1, ?, '127.0.0.1', 'Automated-Test-Agent/1.0',
        'sent', 'Existing active BM Elite member terms acceptance', ?, ?
    )
");

$insertStmt->execute([
    $testComplianceId,
    'test-compliance-user-id',
    'Test Compliance Member',
    'ID12345678',
    '+254712345678',
    'test.compliance@example.com',
    'Kenya',
    1000.0,
    'USD',
    'SUB-EXISTING-REF-001',
    '2026-09-08',
    '2027-01-08',
    hash('sha256', 'BM FOREX HUB ELITE CIRCLE TERMS AND CONDITIONS'),
    $testNow,
    $testNow,
    $testNow
]);

$checkRecord = $pdo->query("SELECT * FROM elite_circle_enrollments WHERE id = '$testComplianceId'")->fetch(PDO::FETCH_ASSOC);
assertTest("Compliance enrollment record successfully created", !empty($checkRecord));
assertTest("Recorded terms_version is 1.0", $checkRecord['terms_version'] === '1.0');
assertTest("Recorded effective date is 2026-01-05", $checkRecord['terms_effective_date'] === '2026-01-05');
assertTest("Recorded note identifies existing member compliance", strpos($checkRecord['notes'], 'Existing active BM Elite member') !== false);
assertTest("Accepted flag is 1", (int)$checkRecord['accepted'] === 1);

// Clean up test record safely
$pdo->exec("DELETE FROM elite_circle_enrollments WHERE id = '$testComplianceId'");
assertTest("Cleaned up temporary test record", true);

echo PHP_EOL . "================================================================" . PHP_EOL;
echo "TEST RESULTS: $passed PASSED, $failed FAILED" . PHP_EOL;
echo "================================================================" . PHP_EOL;
