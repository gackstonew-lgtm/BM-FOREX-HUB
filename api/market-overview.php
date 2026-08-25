<?php
// Public API Endpoint: api/market-overview.php
@header('Content-Type: application/json');
require_once __DIR__ . '/../admin/config.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$data = [];

try {
    if (function_exists('sb_admin_get')) {
        $res = sb_admin_get('market_overview', ['select' => '*', 'order' => 'priority.desc,created_at.desc']);
        if (isset($res['data']) && is_array($res['data']) && count($res['data']) > 0) {
            $data = $res['data'];
        }
    }
} catch (\Throwable $e) {}

if (empty($data) && function_exists('getMarketPDO')) {
    try {
        $pdo = getMarketPDO();
        $stmt = $pdo->query("SELECT * FROM market_overview WHERE status = 'published' ORDER BY priority DESC, id DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {}
}

echo json_encode(['success' => true, 'data' => $data, 'count' => count($data)]);
