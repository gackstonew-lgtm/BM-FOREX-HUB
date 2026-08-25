<?php
/**
 * Crypto Buy/Sell — API Bootstrap
 * NEVER expose secret keys to the frontend. Mirrors api/kora-config.php.
 */

// Load .env variables if present (idempotent with kora-config.php)
if (file_exists(__DIR__ . '/../.env')) {
    $env_lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $val) = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val, " \t\n\r\0\x0B\"'");
        if (!getenv($key)) {
            putenv("$key=$val");
            $_SERVER[$key] = $val;
        }
    }
}

require_once __DIR__ . '/../admin/config.php'; // SUPABASE_URL / SUPABASE_ANON / SUPABASE_SERVICE
require_once __DIR__ . '/../engine_config.php';
require_once __DIR__ . '/kora-config.php'; // reuses existing Kora credentials/service — do not duplicate
require_once __DIR__ . '/../app/Services/CryptoPriceService.php';
require_once __DIR__ . '/../app/Services/CryptoQuoteService.php';
require_once __DIR__ . '/../app/Services/KoraCryptoCapabilityService.php';
require_once __DIR__ . '/../app/Services/CryptoSettlementService.php';

if (!defined('SUPABASE_ANON_KEY'))    define('SUPABASE_ANON_KEY',    SUPABASE_ANON);
if (!defined('SUPABASE_SERVICE_KEY')) define('SUPABASE_SERVICE_KEY', SUPABASE_SERVICE);

$CRYPTO_CONFIG = require __DIR__ . '/../app/Config/crypto.php';

// ── Simple in-process rate limiting (per user, per minute) ─────────
// Uses a small file-based counter; good enough for this endpoint's
// volume without adding new infrastructure.
function crypto_rate_limit(string $key, int $maxPerMinute = 20): bool
{
    $dir = __DIR__ . '/../cache/ratelimit';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $bucket = $dir . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $key) . '_' . date('YmdHi') . '.txt';
    $count = 0;
    if (file_exists($bucket)) {
        $count = (int) file_get_contents($bucket);
    }
    if ($count >= $maxPerMinute) return false;
    @file_put_contents($bucket, (string) ($count + 1), LOCK_EX);
    return true;
}

function crypto_get_user_from_token(): ?array
{
    $auth_header = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['Authorization']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? '';

    if (!preg_match('/Bearer\s+(.+)/i', $auth_header, $m)) {
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        foreach ($headers as $k => $v) {
            if (strtolower($k) === 'authorization') { $auth_header = $v; break; }
        }
        if (!preg_match('/Bearer\s+(.+)/i', $auth_header, $m)) return null;
    }
    $token = $m[1];

    $ctx = stream_context_create([
        'http' => [
            'header'  => "Authorization: Bearer $token\r\napikey: " . SUPABASE_ANON_KEY . "\r\n",
            'timeout' => 5,
        ],
    ]);

    $resp = @file_get_contents(SUPABASE_URL . '/auth/v1/user', false, $ctx);
    if (!$resp) return null;
    $user = json_decode($resp, true);
    return isset($user['id']) ? $user : null;
}

function crypto_supabase_request($url, $method = 'GET', $data = null)
{
    $ctx = stream_context_create([
        'http' => [
            'method'  => $method,
            'header'  => implode("\r\n", [
                "apikey: " . SUPABASE_SERVICE_KEY,
                "Authorization: Bearer " . SUPABASE_SERVICE_KEY,
                "Content-Type: application/json",
                "Prefer: return=representation",
            ]),
            'content' => $data ? json_encode($data) : null,
            'timeout' => 10,
            'ignore_errors' => true,
        ],
    ]);
    $resp = @file_get_contents($url, false, $ctx);
    if ($resp === false) {
        @error_log('BMFH crypto_supabase_request FAILED: ' . $method . ' ' . $url);
        return null;
    }
    return json_decode($resp, true);
}

/**
 * Returns the live-enabled asset/fiat config, preferring the
 * admin-editable Supabase table and falling back to the static
 * app/Config/crypto.php file if the DB call fails.
 */
function crypto_get_enabled_config(): array
{
    global $CRYPTO_CONFIG;

    $rows = crypto_supabase_request(SUPABASE_URL . '/rest/v1/crypto_asset_config?order=sort_order.asc');

    if (is_array($rows) && count($rows) > 0) {
        $assets = [];
        $fiat   = [];
        foreach ($rows as $r) {
            $entry = [
                'symbol'      => $r['symbol'],
                'name'        => $r['name'],
                'enabled'     => (bool) $r['enabled'],
                'buy_enabled' => (bool) ($r['buy_enabled'] ?? $r['enabled']),
                'sell_enabled'=> (bool) ($r['sell_enabled'] ?? $r['enabled']),
            ];
            if ($r['kind'] === 'FIAT') {
                $fiat[$r['symbol']] = $entry;
            } else {
                $entry['coingecko_id'] = $CRYPTO_CONFIG['assets'][$r['symbol']]['coingecko_id'] ?? null;
                $assets[$r['symbol']] = $entry;
            }
        }
        return ['assets' => $assets, 'fiat' => $fiat, 'source' => 'db'];
    }

    // Fallback to static config file
    $assets = [];
    foreach ($CRYPTO_CONFIG['assets'] as $sym => $a) {
        $assets[$sym] = array_merge($a, ['symbol' => $sym, 'buy_enabled' => $a['enabled'], 'sell_enabled' => $a['enabled']]);
    }
    $fiat = [];
    foreach ($CRYPTO_CONFIG['fiat'] as $sym => $f) {
        $fiat[$sym] = array_merge($f, ['symbol' => $sym, 'buy_enabled' => $f['enabled'], 'sell_enabled' => $f['enabled']]);
    }
    return ['assets' => $assets, 'fiat' => $fiat, 'source' => 'static_fallback'];
}
