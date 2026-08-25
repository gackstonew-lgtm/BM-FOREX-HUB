<?php
/**
 * GET /api/crypto-transactions.php
 * Returns the authenticated user's recent crypto transactions.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }

require_once __DIR__ . '/crypto-config.php';

$user = crypto_get_user_from_token();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized — please log in']);
    exit;
}

$limit = isset($_GET['limit']) ? min(50, max(1, (int) $_GET['limit'])) : 10;

$rows = crypto_supabase_request(
    SUPABASE_URL . "/rest/v1/crypto_transactions?user_id=eq.{$user['id']}&order=created_at.desc&limit=$limit"
    . "&select=transaction_reference,type,fiat_currency,fiat_amount,crypto_symbol,crypto_amount,status,created_at"
);

echo json_encode(['ok' => true, 'transactions' => is_array($rows) ? $rows : []]);
