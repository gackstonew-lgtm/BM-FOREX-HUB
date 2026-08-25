<?php
require_once __DIR__ . '/../engine_config.php';
require_once __DIR__ . '/../admin/config.php';

echo "=== TESTING API/ADMIN/SUBSCRIPTION-MANAGEMENT.PHP ===\n";

// Fake admin session
$_SESSION['admin_id'] = 'test-admin-id';
$_SESSION['admin_token'] = 'test-admin-token';
$_SESSION['admin_user'] = 'SuperAdmin';

require_once __DIR__ . '/../api/admin/subscription-management.php';
