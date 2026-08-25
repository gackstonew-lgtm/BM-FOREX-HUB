<?php
$_SESSION['admin_id'] = 'test-admin';
$_SESSION['admin_user'] = 'Admin';
$_SERVER['REQUEST_METHOD'] = 'POST';

$inputData = json_encode([
    'username' => 'siidcal.husseinali',
    'plan' => 'grid_monthly',
    'duration_days' => 30,
    'notes' => 'Test grant via admin interface'
]);

// Run API endpoint logic
require_once __DIR__ . '/../admin/config.php';
require_once __DIR__ . '/../app/Services/MembershipService.php';

$membershipService = new \App\Services\MembershipService();
$adminUser = $_SESSION['admin_user'] ?? 'Admin';
$result = $membershipService->grantSubscription($adminUser, null, 'siidcal.husseinali', 'grid_monthly', 30, 'Test grant via admin interface', 'Admin Grant');

echo json_encode($result, JSON_PRETTY_PRINT);
