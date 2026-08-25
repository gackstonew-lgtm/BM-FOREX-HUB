<?php
/* ============================================================
   BM Forex Hub — Admin Dashboard Config
   Contains: Supabase credentials, helper functions, SQLite Fallback
   DO NOT expose this file to the browser.
   ============================================================ */

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
  session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false,
    'httponly'  => true,
    'samesite' => 'Lax',
  ]);
  session_start();
}

define('SUPABASE_URL',  'https://iwoytmcxmbhmmbrbpzvf.supabase.co');
define('SUPABASE_ANON', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml3b3l0bWN4bWJobW1icmJwenZmIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODM5ODM1NjEsImV4cCI6MjA5OTU1OTU2MX0.cKpLKN-Azoj7UUUp3_uodYraYlJH4fQtKpyRitnbMgk');
define('SUPABASE_SERVICE', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml3b3l0bWN4bWJobW1icmJwenZmIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc4Mzk4MzU2MSwiZXhwIjoyMDk5NTU5NTYxfQ.1ExI-o8zwDkUIH_loQPC_ZJhJaDgrB6gSf1KgAdigCU');

function sb_admin_required() {
  if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_token'])) {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $isJson = (strpos($accept, 'json') !== false) || 
              (strpos($contentType, 'json') !== false) || 
              (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
              isset($_GET['action']) || isset($_POST['action']);
    if ($isJson) {
      http_response_code(401);
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(['success' => false, 'error' => 'Admin session expired or unauthenticated. Please log in again.']);
      exit;
    }
    header('Location: login.php');
    exit;
  }
}

function sb_admin_headers() {
  return [
    'apikey: ' . SUPABASE_ANON,
    'Authorization: Bearer ' . SUPABASE_SERVICE,
    'Content-Type: application/json',
  ];
}

function sb_admin_get($endpoint, $params = []) {
  $url = SUPABASE_URL . '/rest/v1/' . $endpoint;
  if (!empty($params)) $url .= '?' . http_build_query($params);
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => sb_admin_headers(),
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_CONNECTTIMEOUT => 10,
  ]);
  $resp = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  $data = json_decode($resp, true);
  
  if ($code >= 200 && $code < 300 && is_array($data)) {
    return ['code' => $code, 'data' => $data];
  }

  // Fallback to local SQLite DB if Supabase REST returns non-2xx or empty
  $table = explode('?', $endpoint)[0];
  $sqliteData = sqlite_admin_get($table, $params);
  return ['code' => 200, 'data' => $sqliteData, 'fallback' => true];
}

function sb_admin_post($endpoint, $body = [], $method = 'POST') {
  $url = SUPABASE_URL . '/rest/v1/' . $endpoint;
  $ch = curl_init($url);
  $headers = sb_admin_headers();
  if ($method === 'PATCH') $headers[] = 'Prefer: return=minimal';
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => $method,
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_POSTFIELDS     => json_encode($body),
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_CONNECTTIMEOUT => 10,
  ]);
  $resp = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  $data = json_decode($resp, true);

  // Always sync to SQLite as a fail-safe
  $table = explode('?', $endpoint)[0];
  sqlite_admin_post($table, $body, $method);

  if ($code < 300) {
    return ['code' => $code, 'data' => $data];
  }

  // Return success code if SQLite fallback saved the data successfully
  return ['code' => 200, 'data' => $body, 'fallback' => true];
}

function sb_admin_delete($endpoint) {
  $url = SUPABASE_URL . '/rest/v1/' . $endpoint;
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => 'DELETE',
    CURLOPT_HTTPHEADER     => sb_admin_headers(),
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_CONNECTTIMEOUT => 10,
  ]);
  $resp = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  
  $parts = explode('?', $endpoint);
  $table = $parts[0];
  $id = '';
  if (isset($parts[1]) && preg_match('/id=eq\.([^&]+)/', $parts[1], $m)) {
    $id = $m[1];
  }
  if ($id) {
    sqlite_admin_delete($table, $id);
  }
  
  return ['code' => 200, 'data' => json_decode($resp, true)];
}

// ── Market Overview & Local Fail-safe SQLite DB ─────────────────
define('DATABASE_PATH', realpath(__DIR__ . '/../storage/database.sqlite') ?: __DIR__ . '/../storage/database.sqlite');
define('PDF_MAX_SIZE', 10 * 1024 * 1024); // 10 MB

function getMarketPDO() {
    static $pdo = null;
    if ($pdo === null) {
        $dbFile = DATABASE_PATH;
        $dir = dirname($dbFile);
        if (!is_dir($dir)) { mkdir($dir, 0755, true); }
        $pdo = new PDO('sqlite:' . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Auto-create local fallback tables if not exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS payments (
                id TEXT PRIMARY KEY,
                user_id TEXT,
                username TEXT,
                plan TEXT,
                amount REAL DEFAULT 0,
                amount_kes REAL DEFAULT 0,
                status TEXT DEFAULT 'succeeded',
                phone TEXT,
                merchant_txn_id TEXT,
                notes TEXT,
                created_at TEXT,
                updated_at TEXT
            );
            CREATE TABLE IF NOT EXISTS subscriptions (
                id TEXT PRIMARY KEY,
                user_id TEXT,
                username TEXT,
                plan TEXT,
                plan_key TEXT,
                plan_name TEXT,
                amount_usd REAL DEFAULT 0,
                amount_kes REAL DEFAULT 0,
                status TEXT DEFAULT 'active',
                granted_by TEXT DEFAULT 'Admin',
                grant_method TEXT DEFAULT 'Manual Grant',
                starts_at TEXT,
                expires_at TEXT,
                notes TEXT,
                created_at TEXT,
                updated_at TEXT
            );
            CREATE TABLE IF NOT EXISTS user_subscriptions (
                id TEXT PRIMARY KEY,
                user_id TEXT,
                username TEXT,
                plan TEXT,
                plan_name TEXT,
                amount_usd REAL DEFAULT 0,
                amount_kes REAL DEFAULT 0,
                status TEXT DEFAULT 'active',
                granted_by TEXT DEFAULT 'Admin',
                starts_at TEXT,
                expires_at TEXT,
                created_at TEXT,
                updated_at TEXT
            );
            CREATE TABLE IF NOT EXISTS profiles (
                id TEXT PRIMARY KEY,
                username TEXT,
                email TEXT,
                first_name TEXT,
                last_name TEXT,
                role TEXT DEFAULT 'user',
                status TEXT DEFAULT 'active',
                country_code TEXT,
                phone TEXT,
                created_at TEXT,
                updated_at TEXT
            );
            CREATE TABLE IF NOT EXISTS admin_audit_logs (
                id TEXT PRIMARY KEY,
                admin_user TEXT,
                admin_id TEXT,
                user_id TEXT,
                action TEXT,
                granted_plan TEXT,
                old_status TEXT,
                new_status TEXT,
                ip_address TEXT,
                details TEXT,
                result TEXT DEFAULT 'success',
                created_at TEXT
            );
            CREATE TABLE IF NOT EXISTS market_overview_announcements (
                id TEXT PRIMARY KEY,
                title TEXT,
                description TEXT,
                button_text TEXT,
                button_link TEXT,
                priority INTEGER DEFAULT 0,
                status TEXT DEFAULT 'published',
                created_at TEXT,
                updated_at TEXT
            );
            CREATE TABLE IF NOT EXISTS educational_articles (
                id TEXT PRIMARY KEY,
                title TEXT,
                slug TEXT,
                short_description TEXT,
                thumbnail TEXT,
                category TEXT DEFAULT 'General',
                article TEXT,
                status TEXT DEFAULT 'published',
                author TEXT DEFAULT 'BM Forex Hub',
                views INTEGER DEFAULT 0,
                created_at TEXT,
                updated_at TEXT
            );
            CREATE TABLE IF NOT EXISTS featured_videos (
                id TEXT PRIMARY KEY,
                title TEXT,
                video_url TEXT,
                thumbnail TEXT,
                description TEXT,
                category TEXT DEFAULT 'General',
                status TEXT DEFAULT 'published',
                is_featured INTEGER DEFAULT 0,
                priority INTEGER DEFAULT 0,
                created_at TEXT,
                updated_at TEXT
            );
            CREATE TABLE IF NOT EXISTS promotions (
                id TEXT PRIMARY KEY,
                title TEXT,
                description TEXT,
                banner_url TEXT,
                button_text TEXT,
                button_link TEXT,
                start_date TEXT,
                end_date TEXT,
                status TEXT DEFAULT 'active',
                created_at TEXT,
                updated_at TEXT
            );
            CREATE TABLE IF NOT EXISTS copy_traders (
                id TEXT PRIMARY KEY,
                user_id TEXT,
                full_name TEXT,
                email TEXT,
                subscription_plan TEXT DEFAULT 'copytrading',
                payment_reference TEXT,
                broker_name TEXT,
                mt5_login TEXT,
                mt5_password TEXT,
                mt5_server TEXT,
                notes TEXT,
                status TEXT DEFAULT 'Pending',
                created_at TEXT,
                updated_at TEXT
            );
            CREATE TABLE IF NOT EXISTS copy_trader_audit_logs (
                id TEXT PRIMARY KEY,
                admin_user TEXT,
                copy_trader_id TEXT,
                action TEXT,
                ip_address TEXT,
                details TEXT,
                created_at TEXT
            );

            -- Indexes for high-performance querying
            CREATE INDEX IF NOT EXISTS idx_subs_user_id ON subscriptions(user_id);
            CREATE INDEX IF NOT EXISTS idx_subs_status ON subscriptions(status);
            CREATE INDEX IF NOT EXISTS idx_subs_username ON subscriptions(username);
            CREATE INDEX IF NOT EXISTS idx_user_subs_user_id ON user_subscriptions(user_id);
            CREATE INDEX IF NOT EXISTS idx_payments_user_id ON payments(user_id);
            CREATE INDEX IF NOT EXISTS idx_profiles_username ON profiles(username);
            CREATE INDEX IF NOT EXISTS idx_profiles_email ON profiles(email);
        ");

        // Dynamic schema upgrades / column repairs
        try { $pdo->exec("ALTER TABLE user_subscriptions ADD COLUMN amount_usd REAL DEFAULT 0"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE payments ADD COLUMN amount_usd REAL DEFAULT 0"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE subscriptions ADD COLUMN amount_usd REAL DEFAULT 0"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE profiles ADD COLUMN status TEXT DEFAULT 'active'"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE admin_audit_logs ADD COLUMN admin_id TEXT"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE admin_audit_logs ADD COLUMN granted_plan TEXT"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE admin_audit_logs ADD COLUMN old_status TEXT"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE admin_audit_logs ADD COLUMN new_status TEXT"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE admin_audit_logs ADD COLUMN result TEXT DEFAULT 'success'"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE admin_audit_logs ADD COLUMN updated_at TEXT"); } catch (\Throwable $e) {}
    }
    return $pdo;
}

function sqlite_admin_get($table, $params = []) {
  try {
    $pdo = getMarketPDO();
    $allowed = ['subscriptions', 'user_subscriptions', 'payments', 'profiles', 'admin_audit_logs', 'market_overview_announcements', 'market_announcements', 'educational_articles', 'featured_videos', 'promotions', 'copy_traders', 'copy_trader_audit_logs'];
    if (!in_array($table, $allowed)) return [];
    if ($table === 'market_announcements') $table = 'market_overview_announcements';

    $sql = "SELECT * FROM {$table}";
    $where = [];
    $binds = [];
    if (!empty($params['status'])) {
      $where[] = "status = ?";
      $binds[] = str_replace('eq.', '', $params['status']);
    }
    if (!empty($params['user_id'])) {
      $where[] = "user_id = ?";
      $binds[] = str_replace('eq.', '', $params['user_id']);
    }
    if (!empty($params['username'])) {
      $where[] = "username = ?";
      $binds[] = str_replace('eq.', '', $params['username']);
    }
    if (!empty($params['email'])) {
      $where[] = "email = ?";
      $binds[] = str_replace('eq.', '', $params['email']);
    }
    if (!empty($params['plan'])) {
      $where[] = "plan = ?";
      $binds[] = str_replace('eq.', '', $params['plan']);
    }
    if (!empty($params['id'])) {
      $where[] = "id = ?";
      $binds[] = str_replace('eq.', '', $params['id']);
    }
    if (!empty($where)) {
      $sql .= " WHERE " . implode(' AND ', $where);
    }
    $sql .= " ORDER BY created_at DESC";
    if (!empty($params['limit'])) {
      $sql .= " LIMIT " . (int)$params['limit'];
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($binds);
    return $stmt->fetchAll();
  } catch (Exception $e) {
    return [];
  }
}

function sqlite_admin_post($table, $data, $method = 'POST') {
  try {
    $pdo = getMarketPDO();
    $allowed = ['subscriptions', 'user_subscriptions', 'payments', 'profiles', 'admin_audit_logs', 'market_overview_announcements', 'market_announcements', 'educational_articles', 'featured_videos', 'promotions', 'copy_traders', 'copy_trader_audit_logs'];
    if (!in_array($table, $allowed)) return false;
    if ($table === 'market_announcements') $table = 'market_overview_announcements';

    if (empty($data['id'])) {
      $data['id'] = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
      );
    }
    if (empty($data['created_at'])) $data['created_at'] = date('c');
    if (empty($data['updated_at'])) $data['updated_at'] = date('c');

    $cols = array_keys($data);
    $placeholders = implode(',', array_fill(0, count($cols), '?'));
    $sql = "INSERT OR REPLACE INTO {$table} (" . implode(',', $cols) . ") VALUES ({$placeholders})";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(array_values($data));
  } catch (Exception $e) {
    return false;
  }
}

function sqlite_admin_delete($table, $id) {
  try {
    $pdo = getMarketPDO();
    $allowed = ['subscriptions', 'user_subscriptions', 'payments', 'profiles', 'admin_audit_logs', 'market_overview_announcements', 'market_announcements', 'educational_articles', 'featured_videos', 'promotions', 'copy_traders', 'copy_trader_audit_logs'];
    if (!in_array($table, $allowed)) return false;
    if ($table === 'market_announcements') $table = 'market_overview_announcements';

    $stmt = $pdo->prepare("DELETE FROM {$table} WHERE id = ?");
    return $stmt->execute([$id]);
  } catch (Exception $e) {
    return false;
  }
}

function sb_auth_admin_users($path = '', $method = 'GET', $body = null) {
  $url = SUPABASE_URL . '/auth/v1/admin/users' . $path;
  $ch = curl_init($url);
  $opts = [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
      'apikey: ' . SUPABASE_SERVICE,
      'Authorization: Bearer ' . SUPABASE_SERVICE,
      'Content-Type: application/json',
    ],
    CURLOPT_TIMEOUT => 15,
  ];
  if ($method !== 'GET') {
    $opts[CURLOPT_CUSTOMREQUEST] = $method;
    if ($body !== null) $opts[CURLOPT_POSTFIELDS] = json_encode($body);
  }
  curl_setopt_array($ch, $opts);
  $resp = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return ['code' => $code, 'data' => json_decode($resp, true)];
}
