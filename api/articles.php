<?php
// Admin/Public API Endpoint: api/articles.php
@header('Content-Type: application/json');
require_once __DIR__ . '/../admin/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$TABLE = 'educational_articles';

/** Write to both Supabase and SQLite mirror. */
function articles_save($payload, $method) {
  global $TABLE;
  $id = $payload['id'] ?? null;
  if ($method === 'POST') {
    unset($payload['id']);
    $res = sb_admin_post($TABLE, $payload, 'POST');
  } else {
    if (empty($id)) return ['success' => false, 'error' => 'Missing article id.'];
    $res = sb_admin_post($TABLE . '?id=eq.' . urlencode($id), $payload, 'PATCH');
  }
  sqlite_admin_post($TABLE, $payload, $method);
  return ['success' => true, 'data' => $payload];
}

switch ($method) {
  case 'GET':
    $search = trim($_GET['search'] ?? '');
    $id = $_GET['id'] ?? '';
    $data = [];
    try {
      if (function_exists('sb_admin_get')) {
        $query = $TABLE . '?select=*&order=created_at.desc';
        if (!empty($id)) $query .= '&id=eq.' . urlencode($id);
        $res = sb_admin_get($query);
        if (isset($res['data']) && is_array($res['data']) && count($res['data']) > 0) {
          $data = $res['data'];
        }
      }
    } catch (\Throwable $e) {}
    if (empty($data) && function_exists('getMarketPDO')) {
      try {
        $pdo = getMarketPDO();
        if (!empty($id)) {
          $stmt = $pdo->prepare("SELECT * FROM {$TABLE} WHERE id = :id");
          $stmt->execute([':id' => $id]);
        } else {
          $stmt = $pdo->query("SELECT * FROM {$TABLE} ORDER BY created_at DESC");
        }
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (\Throwable $e) {}
    }
    if (!empty($search)) {
      $data = array_values(array_filter($data, function($a) use ($search) {
        $needle = strtolower($search);
        return strpos(strtolower($a['title'] ?? ''), $needle) !== false ||
               strpos(strtolower($a['category'] ?? ''), $needle) !== false;
      }));
    }
    echo json_encode(['success' => true, 'data' => $data, 'count' => count($data)]);
    exit;

  case 'POST':
  case 'PATCH':
    sb_admin_required();
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) $input = $_POST;
    $payload = [
      'title'             => $input['title'] ?? '',
      'slug'              => $input['slug'] ?? '',
      'short_description' => $input['short_description'] ?? '',
      'thumbnail'         => $input['thumbnail'] ?? '',
      'article'           => $input['article'] ?? $input['content'] ?? '',
      'category'          => $input['category'] ?? 'General',
      'status'            => $input['status'] ?? 'published',
      'author'            => $input['author'] ?? 'BM Forex Hub',
    ];
    if ($method === 'PATCH') $payload['id'] = $input['id'] ?? '';
    if (empty($payload['title'])) {
      http_response_code(400);
      echo json_encode(['success' => false, 'error' => 'Title is required.']);
      exit;
    }
    echo json_encode(articles_save($payload, $method));
    exit;

  case 'DELETE':
    sb_admin_required();
    $id = $_GET['id'] ?? '';
    if (empty($id)) {
      http_response_code(400);
      echo json_encode(['success' => false, 'error' => 'Missing article id.']);
      exit;
    }
    if (function_exists('sb_admin_delete')) {
      sb_admin_delete($TABLE . '?id=eq.' . urlencode($id));
    }
    if (function_exists('sqlite_admin_delete')) {
      sqlite_admin_delete($TABLE, $id);
    }
    echo json_encode(['success' => true, 'deleted' => $id]);
    exit;

  default:
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}
