<?php
/**
 * /admin/api/elite-enrollment.php
 * Admin API Endpoint Bridge for admin domain access.
 */

$primaryEndpoint = __DIR__ . '/../../api/elite-enrollment.php';
if (file_exists($primaryEndpoint)) {
    require_once $primaryEndpoint;
} else {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Primary elite enrollment endpoint missing.'
    ]);
}
