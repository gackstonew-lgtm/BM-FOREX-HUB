<?php
header('Content-Type: application/json');
header('Cache-Control: public, max-age=15');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/engine.php';

$goldData = engine_fetch_live_xauusd_price();
if (is_array($goldData) && isset($goldData['price']) && (float)$goldData['price'] > 0) {
    echo json_encode($goldData);
    exit;
}

http_response_code(503);
echo json_encode(['error' => 'XAUUSD live market price feed unavailable', 'status' => 'offline']);
exit;

