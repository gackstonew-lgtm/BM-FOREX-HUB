<?php
// Public API Endpoint: api/classes.php
// Returns published live classes for user dashboard & classes page

@header('Content-Type: application/json');
require_once __DIR__ . '/../admin/config.php';
require_once __DIR__ . '/../app/Services/LiveClassStatusEngine.php';

use App\Services\LiveClassStatusEngine;

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$id = $_GET['id'] ?? '';
$classes = [];

// 1. Query Supabase REST
try {
    if (function_exists('sb_admin_get')) {
        $endpoint = 'live_classes?select=*&order=created_at.desc';
        if (!empty($id)) {
            $endpoint .= '&id=eq.' . urlencode($id);
        }
        $res = sb_admin_get($endpoint);
        if (isset($res['data']) && is_array($res['data']) && count($res['data']) > 0) {
            $classes = $res['data'];
        }
    }
} catch (\Throwable $e) {}

// 2. Fallback: Query local SQLite database
if (empty($classes) && function_exists('getMarketPDO')) {
    try {
        $pdo = getMarketPDO();
        if (!empty($id)) {
            $stmt = $pdo->prepare("SELECT * FROM live_classes WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $pdo->query("SELECT * FROM live_classes ORDER BY id DESC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if (is_array($rows)) {
            $classes = $rows;
        }
    } catch (\Throwable $e) {}
}

// Filter published & process through LiveClassStatusEngine
$processed = [];
foreach ($classes as $c) {
    $visibility = $c['visibility'] ?? 'published';
    if ($visibility === 'archived') continue; // Skip archived classes for public view
    
    $processed[] = LiveClassStatusEngine::processClass($c);
}

// Sort: Live classes first, then upcoming by countdown seconds, then ended
usort($processed, function($a, $b) {
    if ($a['is_live'] !== $b['is_live']) {
        return $a['is_live'] ? -1 : 1;
    }
    if ($a['live_status'] === 'scheduled' && $b['live_status'] === 'scheduled') {
        return $a['countdown_seconds'] <=> $b['countdown_seconds'];
    }
    return 0;
});

echo json_encode([
    'success' => true,
    'data'    => $processed,
    'count'   => count($processed)
]);
