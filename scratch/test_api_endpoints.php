<?php
/**
 * Test API endpoints directly
 */

require_once __DIR__ . '/../admin/config.php';
require_once __DIR__ . '/../engine_config.php';

echo "Testing syntax of all modified/created files...\n";

$files = [
    'migrations/20260924_add_elite_circle_enrollments.php',
    'admin/config.php',
    'api/elite-enrollment.php',
    'admin/api/elite-enrollment.php',
    'api/deposit.php',
    'api/admin/subscription-management.php',
    'elite_enrollment.php',
    'bm_elites.php',
    'subscribe.php',
    'admin/views/elite_subscriptions.php'
];

foreach ($files as $file) {
    $fullPath = __DIR__ . '/../' . $file;
    if (!file_exists($fullPath)) {
        echo "[ERROR] File missing: $file\n";
        exit(1);
    }
    $cmd = 'c:\xampp\php\php.exe -l "' . $fullPath . '"';
    exec($cmd, $output, $returnVar);
    if ($returnVar !== 0) {
        echo "[ERROR] Lint failure on: $file\n" . implode("\n", $output) . "\n";
        exit(1);
    } else {
        echo "[OK] Lint passed: $file\n";
    }
    $output = [];
}

echo "\nAll PHP files passed syntax validation!\n";
