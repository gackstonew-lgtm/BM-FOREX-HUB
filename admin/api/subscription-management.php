<?php
/**
 * /admin/api/subscription-management.php
 * Admin API Endpoint Bridge for admin domain access.
 */

// Route to primary endpoint logic
$primaryEndpoint = __DIR__ . '/../../api/admin/subscription-management.php';
if (file_exists($primaryEndpoint)) {
    require_once $primaryEndpoint;
} else {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Primary subscription management endpoint logic missing.'
    ]);
}
