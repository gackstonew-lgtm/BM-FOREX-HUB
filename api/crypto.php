<?php
/**
 * /api/crypto.php
 * Admin API for crypto asset configuration & transaction monitoring.
 * Requires an active admin session (sb_admin_required).
 *
 * GET  ?action=asset_config                 → list enabled assets/fiats
 * GET  ?action=transactions&limit=N         → recent crypto transactions
 * POST {action: "update_asset", symbol, enabled|buy_enabled|sell_enabled}
 *                                           → toggle an asset flag
 */

if (!function_exists('sb_admin_required')) {
    require_once __DIR__ . '/../admin/config.php';
}
sb_admin_required();

require_once __DIR__ . '/crypto-config.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = ($method === 'POST')
    ? (json_decode(file_get_contents('php://input'), true)['action'] ?? ($_POST['action'] ?? 'asset_config'))
    : ($_GET['action'] ?? 'asset_config');

try {
    switch ($action) {

        // ── Asset configuration (merge DB rows with static defaults) ──
        case 'asset_config':
            $cfg = crypto_get_enabled_config();
            $rows = crypto_supabase_request(SUPABASE_URL . '/rest/v1/crypto_asset_config?order=sort_order.asc');
            $assets = [];
            if (is_array($rows) && count($rows) > 0) {
                foreach ($rows as $r) {
                    $assets[] = [
                        'symbol'       => $r['symbol'],
                        'name'         => $r['name'],
                        'kind'         => $r['kind'] ?? 'CRYPTO',
                        'enabled'      => (bool) $r['enabled'],
                        'buy_enabled'  => (bool) ($r['buy_enabled'] ?? $r['enabled']),
                        'sell_enabled' => (bool) ($r['sell_enabled'] ?? $r['enabled']),
                        'sort_order'   => (int) ($r['sort_order'] ?? 0),
                    ];
                }
            } else {
                // Fallback to static config so the admin page never blanks out
                foreach (($cfg['assets'] ?? []) as $sym => $a) {
                    $assets[] = [
                        'symbol'       => $sym,
                        'name'         => $a['name'] ?? $sym,
                        'kind'         => 'CRYPTO',
                        'enabled'      => (bool) ($a['enabled'] ?? false),
                        'buy_enabled'  => (bool) ($a['buy_enabled'] ?? $a['enabled'] ?? false),
                        'sell_enabled' => (bool) ($a['sell_enabled'] ?? $a['enabled'] ?? false),
                        'sort_order'   => (int) ($a['sort_order'] ?? 0),
                    ];
                }
                foreach (($cfg['fiat'] ?? []) as $sym => $f) {
                    $assets[] = [
                        'symbol'       => $sym,
                        'name'         => $f['name'] ?? $sym,
                        'kind'         => 'FIAT',
                        'enabled'      => (bool) ($f['enabled'] ?? true),
                        'buy_enabled'  => true,
                        'sell_enabled' => true,
                        'sort_order'   => 0,
                    ];
                }
            }
            usort($assets, fn($a, $b) => $a['sort_order'] <=> $b['sort_order']);
            echo json_encode(['success' => true, 'assets' => $assets, 'source' => ($cfg['source'] ?? 'db')]);
            exit;

        // ── Transaction listing ───────────────────────────────────────
        case 'transactions':
            $limit = max(1, min(200, (int)($_GET['limit'] ?? 50)));
            $rows = crypto_supabase_request(SUPABASE_URL . "/rest/v1/crypto_transactions?order=created_at.desc&limit=$limit");
            if (!is_array($rows)) {
                $rows = [];
            }
            echo json_encode(['success' => true, 'transactions' => $rows, 'count' => count($rows)]);
            exit;

        // ── Toggle an asset flag ──────────────────────────────────────
        case 'update_asset':
            $raw = file_get_contents('php://input');
            $body = json_decode($raw, true);
            if (!is_array($body)) $body = $_POST;

            $symbol = strtoupper(trim($body['symbol'] ?? ''));
            if ($symbol === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Asset symbol is required.']);
                exit;
            }

            $patch = [];
            foreach (['enabled', 'buy_enabled', 'sell_enabled'] as $field) {
                if (array_key_exists($field, $body)) {
                    $patch[$field] = !empty($body[$field]);
                }
            }
            if (empty($patch)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Nothing to update.']);
                exit;
            }
            $patch['updated_at'] = date('c');

            $res = crypto_supabase_request(
                SUPABASE_URL . '/rest/v1/crypto_asset_config?symbol=eq.' . urlencode($symbol),
                'PATCH',
                $patch
            );
            echo json_encode([
                'success' => true,
                'symbol'  => $symbol,
                'patched' => $patch,
                'db'      => $res,
            ]);
            exit;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unknown action: ' . htmlspecialchars((string)$action)]);
            exit;
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
    exit;
}
