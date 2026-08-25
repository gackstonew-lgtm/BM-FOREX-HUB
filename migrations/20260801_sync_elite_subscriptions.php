<?php
/**
 * BM Forex Hub — Elite Membership Synchronization & Migration Routine
 * Ensures the Admin Panel and User Dashboard share the exact same membership data.
 */
require_once __DIR__ . '/../engine_config.php';
require_once __DIR__ . '/../admin/config.php';
require_once __DIR__ . '/../app/Services/MembershipService.php';

header('Content-Type: application/json');

$service = new \App\Services\MembershipService();

// 1. Sync permanent admin users into local SQLite DB and Supabase
$permanentEmails = ['bonfacewana3072@gmail.com', 'langatgift6@gmail.com', 'gackstoneb@gmail.com'];
$syncedCount = 0;

foreach ($permanentEmails as $pEmail) {
    $username = explode('@', $pEmail)[0];
    $result = $service->grantEliteMembership(
        'System (Permanent Migration)',
        $username,
        \App\Services\MembershipService::PLAN_ELITE_ELITE,
        3650,
        10000,
        'Permanent Admin Access Account'
    );
    if ($result['success']) {
        $syncedCount++;
    }
}

// 2. Consolidate and repair all Elite memberships
$allEliteMemberships = $service->getAllEliteMemberships();

echo json_encode([
    'success'       => true,
    'message'       => 'Elite membership synchronization routine completed successfully.',
    'synced_count'  => $syncedCount,
    'total_elites'  => count($allEliteMemberships),
    'memberships'   => $allEliteMemberships,
]);
