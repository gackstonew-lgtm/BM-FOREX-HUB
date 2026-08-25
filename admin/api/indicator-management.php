<?php
/**
 * /admin/api/indicator-management.php
 * Bridge endpoint for indicator subscriber management.
 */
$primaryEndpoint = __DIR__ . '/../../api/admin/indicator-management.php';
if (file_exists($primaryEndpoint)) {
    require_once $primaryEndpoint;
} else {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['error' => 'Primary indicator management endpoint missing.']);
}
