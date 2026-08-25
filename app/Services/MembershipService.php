<?php
namespace App\Services;

require_once __DIR__ . '/CurrencyConversionService.php';

class MembershipService {
    const PLAN_COPYTRADING        = 'copytrading';
    const PLAN_GRID_MONTHLY       = 'grid_monthly';
    const PLAN_GRID_LIFETIME      = 'grid_lifetime';
    const PLAN_CLASSES_ONLINE     = 'classes_online';
    const PLAN_CLASSES_PHYSICAL   = 'classes_physical';
    const PLAN_ELITE_STARTER      = 'elite_starter';
    const PLAN_ELITE_INTERMEDIATE = 'elite_intermediate';
    const PLAN_ELITE_ADVANCED     = 'elite_advanced';
    const PLAN_ELITE_PROFESSIONAL = 'elite_professional';
    const PLAN_ELITE_ELITE        = 'elite_elite';

    // ── Indicator Platform Plans (Single $299 One-Time Payment) ──────
    const PLAN_INDICATOR_QUANTUM_EDGE = 'indicator_quantum_edge';
    const PLAN_INDICATOR_SILVER       = 'indicator_silver';
    const PLAN_INDICATOR_GOLD         = 'indicator_gold';
    const PLAN_INDICATOR_VIP          = 'indicator_vip';

    const PLAN_NAMES = [
        self::PLAN_COPYTRADING       => 'Copy Trading Integration',
        self::PLAN_GRID_MONTHLY      => 'Grid Signal — Monthly',
        self::PLAN_GRID_LIFETIME     => 'Grid Signal — Lifetime',
        self::PLAN_CLASSES_ONLINE    => 'Forex Classes — Online',
        self::PLAN_CLASSES_PHYSICAL  => 'Forex Classes — Physical',

        self::PLAN_ELITE_STARTER      => 'BM Elites — $1,000 USD',
        self::PLAN_ELITE_INTERMEDIATE => 'BM Elites — $2,000 USD',
        self::PLAN_ELITE_ADVANCED     => 'BM Elites — $3,000 USD',
        self::PLAN_ELITE_PROFESSIONAL => 'BM Elites — $5,000 USD',
        self::PLAN_ELITE_ELITE        => 'BM Elites — $10,000 USD',

        // Indicator Platform ($299 One-Time)
        self::PLAN_INDICATOR_QUANTUM_EDGE => 'BM Quantum Edge ($299 One-Time)',
        self::PLAN_INDICATOR_SILVER       => 'BM Quantum Edge (Legacy Silver)',
        self::PLAN_INDICATOR_GOLD         => 'BM Quantum Edge (Legacy Gold)',
        self::PLAN_INDICATOR_VIP          => 'BM Quantum Edge (Legacy VIP)',
    ];

    const PLAN_AMOUNTS_USD = [
        self::PLAN_COPYTRADING        => 249.00,
        self::PLAN_GRID_MONTHLY       => 25.00,
        self::PLAN_GRID_LIFETIME      => 499.00,
        self::PLAN_CLASSES_ONLINE     => 399.00,
        self::PLAN_CLASSES_PHYSICAL   => 599.00,
        self::PLAN_ELITE_STARTER      => 1000.00,
        self::PLAN_ELITE_INTERMEDIATE => 2000.00,
        self::PLAN_ELITE_ADVANCED     => 3000.00,
        self::PLAN_ELITE_PROFESSIONAL => 5000.00,
        self::PLAN_ELITE_ELITE        => 10000.00,

        // Indicator Platform ($299 One-Time)
        self::PLAN_INDICATOR_QUANTUM_EDGE => 299.00,
        self::PLAN_INDICATOR_SILVER       => 299.00,
        self::PLAN_INDICATOR_GOLD         => 299.00,
        self::PLAN_INDICATOR_VIP          => 299.00,
    ];

    // Legacy PLAN_AMOUNTS fallback (USD amounts)
    const PLAN_AMOUNTS = self::PLAN_AMOUNTS_USD;

    private $supabaseUrl;
    private $serviceKey;

    public function __construct($supabaseUrl = null, $serviceKey = null) {
        $this->supabaseUrl = $supabaseUrl ?? (defined('SUPABASE_URL') ? SUPABASE_URL : 'https://iwoytmcxmbhmmbrbpzvf.supabase.co');
        $this->serviceKey = $serviceKey ?? (defined('SUPABASE_SERVICE') ? SUPABASE_SERVICE : (defined('SUPABASE_SERVICE_KEY') ? SUPABASE_SERVICE_KEY : 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml3b3l0bWN4bWJobW1icmJwenZmIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc4Mzk4MzU2MSwiZXhwIjoyMDk5NTU5NTYxfQ.1ExI-o8zwDkUIH_loQPC_ZJhJaDgrB6gSf1KgAdigCU'));
    }

    private function request($endpoint, $method = 'GET', $body = null) {
        if (function_exists('sb_admin_get') && $method === 'GET') {
            $parts = explode('?', $endpoint);
            $table = $parts[0];
            $params = [];
            if (isset($parts[1])) {
                parse_str($parts[1], $params);
            }
            return sb_admin_get($table, $params);
        }
        
        $url = $this->supabaseUrl . '/rest/v1/' . $endpoint;
        $ch = curl_init($url);
        $headers = [
            'apikey: ' . $this->serviceKey,
            'Authorization: Bearer ' . $this->serviceKey,
            'Content-Type: application/json',
        ];
        if ($method === 'PATCH') $headers[] = 'Prefer: return=minimal';
        
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 15,
        ];
        if ($body !== null) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($body);
        }
        curl_setopt_array($ch, $opts);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ['code' => $code, 'data' => json_decode($resp, true)];
    }

    public function getElitesPlans() {
        try {
            $currencyService = new CurrencyConversionService();
            $rate = $currencyService->getExchangeRate();
        } catch (\Throwable $e) {
            $rate = 129.00;
        }

        return [
            ['plan_name' => 'Starter', 'plan_key' => self::PLAN_ELITE_STARTER, 'amount_usd' => self::PLAN_AMOUNTS_USD[self::PLAN_ELITE_STARTER], 'amount_kes' => round(self::PLAN_AMOUNTS_USD[self::PLAN_ELITE_STARTER] * $rate, 2), 'description' => 'BM Elites Starter Investment'],
            ['plan_name' => 'Intermediate', 'plan_key' => self::PLAN_ELITE_INTERMEDIATE, 'amount_usd' => self::PLAN_AMOUNTS_USD[self::PLAN_ELITE_INTERMEDIATE], 'amount_kes' => round(self::PLAN_AMOUNTS_USD[self::PLAN_ELITE_INTERMEDIATE] * $rate, 2), 'description' => 'BM Elites Intermediate Investment'],
            ['plan_name' => 'Advanced', 'plan_key' => self::PLAN_ELITE_ADVANCED, 'amount_usd' => self::PLAN_AMOUNTS_USD[self::PLAN_ELITE_ADVANCED], 'amount_kes' => round(self::PLAN_AMOUNTS_USD[self::PLAN_ELITE_ADVANCED] * $rate, 2), 'description' => 'BM Elites Advanced Investment'],
            ['plan_name' => 'Professional', 'plan_key' => self::PLAN_ELITE_PROFESSIONAL, 'amount_usd' => self::PLAN_AMOUNTS_USD[self::PLAN_ELITE_PROFESSIONAL], 'amount_kes' => round(self::PLAN_AMOUNTS_USD[self::PLAN_ELITE_PROFESSIONAL] * $rate, 2), 'description' => 'BM Elites Professional Investment'],
            ['plan_name' => 'Elite', 'plan_key' => self::PLAN_ELITE_ELITE, 'amount_usd' => self::PLAN_AMOUNTS_USD[self::PLAN_ELITE_ELITE], 'amount_kes' => round(self::PLAN_AMOUNTS_USD[self::PLAN_ELITE_ELITE] * $rate, 2), 'description' => 'BM Elites Ultimate VIP Investment'],
        ];
    }

    /**
     * Single Source of Truth: Get all consolidated Elite memberships for Admin & Public Sync
     */
    public function getAllEliteMemberships() {
        $now = time();
        $membersMap = [];

        try {
            $currencyService = new CurrencyConversionService();
            $rate = $currencyService->getExchangeRate();
        } catch (\Throwable $e) {
            $rate = 129.00;
        }

        $permKes = round(10000 * $rate, 2);

        // 1. Permanent Admin Access users
        $permanentEmails = ['bonfacewana3072@gmail.com', 'langatgift6@gmail.com', 'gackstoneb@gmail.com'];
        foreach ($permanentEmails as $pEmail) {
            $username = explode('@', $pEmail)[0];
            $key = strtolower($username);
            $membersMap[$key] = [
                'id'          => 'perm-' . md5($pEmail),
                'username'    => $username,
                'email'       => $pEmail,
                'plan'        => self::PLAN_ELITE_ELITE,
                'plan_name'   => 'BM Elites — Permanent Admin Access',
                'amount'      => $permKes,
                'amount_usd'  => 10000,
                'amount_kes'  => $permKes,
                'status'      => 'succeeded',
                'starts_at'   => date('c', strtotime('-30 days')),
                'expires_at'  => date('c', strtotime('+3650 days')),
                'granted_by'  => 'System (Permanent)',
                'created_at'  => date('c', strtotime('-30 days')),
            ];
        }

        // 2. Query user_subscriptions
        $userSubsRes = $this->request('user_subscriptions?select=*&order=created_at.desc');
        $userSubs = $userSubsRes['data'] ?? [];
        if (is_array($userSubs)) {
            foreach ($userSubs as $us) {
                $plan = $us['plan'] ?? '';
                if (strpos($plan, 'elite_') === 0 || $plan === 'all') {
                    $uname = $us['username'] ?? $us['user_id'] ?? 'User';
                    $key = strtolower($uname);
                    if (!isset($membersMap[$key]) || strtotime($us['created_at'] ?? '') > strtotime($membersMap[$key]['created_at'] ?? '')) {
                        $rawAmt = (float)($us['amount_kes'] ?? 0);
                        $usdVal = (float)($us['amount_usd'] ?? (self::PLAN_AMOUNTS_USD[$plan] ?? ($rawAmt <= 10000 && $rawAmt > 0 ? $rawAmt : 10000)));
                        $kesVal = round($usdVal * $rate, 2);
                        $membersMap[$key] = [
                            'id'          => $us['id'] ?? uniqid(),
                            'user_id'     => $us['user_id'] ?? '',
                            'username'    => $uname,
                            'plan'        => $plan,
                            'plan_name'   => self::PLAN_NAMES[$plan] ?? ($us['plan_name'] ?? 'BM Elites'),
                            'amount'      => $kesVal,
                            'amount_usd'  => $usdVal,
                            'amount_kes'  => $kesVal,
                            'status'      => $us['status'] ?? 'active',
                            'starts_at'   => $us['starts_at'] ?? date('c'),
                            'expires_at'  => $us['expires_at'] ?? date('c', strtotime('+30 days')),
                            'granted_by'  => $us['granted_by'] ?? 'Admin',
                            'created_at'  => $us['created_at'] ?? date('c'),
                        ];
                    }
                }
            }
        }

        // 3. Query payments
        $payRes = $this->request('payments?select=*&order=created_at.desc');
        $payments = $payRes['data'] ?? [];
        if (is_array($payments)) {
            foreach ($payments as $p) {
                $plan = $p['plan'] ?? '';
                if (strpos($plan, 'elite_') === 0) {
                    $uname = $p['username'] ?? $p['user_id'] ?? 'User';
                    $key = strtolower($uname);
                    if (!isset($membersMap[$key])) {
                        $rawAmt = (float)($p['amount_kes'] ?? $p['amount'] ?? 0);
                        $usdVal = (float)($p['amount_usd'] ?? (self::PLAN_AMOUNTS_USD[$plan] ?? ($rawAmt <= 10000 && $rawAmt > 0 ? $rawAmt : 10000)));
                        $kesVal = round($usdVal * $rate, 2);
                        $membersMap[$key] = [
                            'id'          => $p['id'] ?? uniqid(),
                            'user_id'     => $p['user_id'] ?? '',
                            'username'    => $uname,
                            'plan'        => $plan,
                            'plan_name'   => self::PLAN_NAMES[$plan] ?? 'BM Elites',
                            'amount'      => $kesVal,
                            'amount_usd'  => $usdVal,
                            'amount_kes'  => $kesVal,
                            'status'      => $p['status'] ?? 'succeeded',
                            'starts_at'   => $p['created_at'] ?? date('c'),
                            'expires_at'  => date('c', strtotime('+30 days', strtotime($p['created_at'] ?? 'now'))),
                            'granted_by'  => 'Payment Gateway',
                            'created_at'  => $p['created_at'] ?? date('c'),
                        ];
                    }
                }
            }
        }

        // 4. Query subscriptions table
        $subsRes = $this->request('subscriptions?select=*&order=created_at.desc');
        $subs = $subsRes['data'] ?? [];
        if (is_array($subs)) {
            foreach ($subs as $s) {
                $plan = $s['plan'] ?? '';
                if (strpos($plan, 'elite_') === 0) {
                    $uname = $s['username'] ?? $s['user_id'] ?? 'User';
                    $key = strtolower($uname);
                    if (!isset($membersMap[$key])) {
                        $rawAmt = (float)($s['amount_kes'] ?? 0);
                        $usdVal = (float)($s['amount_usd'] ?? (self::PLAN_AMOUNTS_USD[$plan] ?? ($rawAmt <= 10000 && $rawAmt > 0 ? $rawAmt : 10000)));
                        $kesVal = round($usdVal * $rate, 2);
                        $membersMap[$key] = [
                            'id'          => $s['id'] ?? uniqid(),
                            'user_id'     => $s['user_id'] ?? '',
                            'username'    => $uname,
                            'plan'        => $plan,
                            'plan_name'   => self::PLAN_NAMES[$plan] ?? 'BM Elites',
                            'amount'      => $kesVal,
                            'amount_usd'  => $usdVal,
                            'amount_kes'  => $kesVal,
                            'status'      => $s['status'] ?? 'active',
                            'starts_at'   => $s['starts_at'] ?? date('c'),
                            'expires_at'  => $s['expires_at'] ?? date('c', strtotime('+30 days')),
                            'granted_by'  => $s['granted_by'] ?? 'Admin',
                            'created_at'  => $s['created_at'] ?? date('c'),
                        ];
                    }
                }
            }
        }

        return array_values($membersMap);
    }

    public function getActiveSubscriptions($userId, $username = null, $email = null) {
        $nowTs = time();
        $nowIso = date('c');

        $subs = [];

        // 1. Query local SQLite database first for fast & reliable lookup
        if (function_exists('getMarketPDO')) {
            try {
                $pdo = getMarketPDO();
                $where = ["status = 'active'"];
                $params = [];

                $userConds = [];
                if ($userId) {
                    $userConds[] = "user_id = :uid";
                    $params[':uid'] = $userId;
                }
                if ($username) {
                    $userConds[] = "LOWER(username) = :uname";
                    $params[':uname'] = strtolower($username);
                }
                if ($email) {
                    $userConds[] = "LOWER(username) = :uemail";
                    $params[':uemail'] = strtolower(explode('@', $email)[0]);
                }

                if (!empty($userConds)) {
                    $where[] = "(" . implode(" OR ", $userConds) . ")";
                    $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE " . implode(" AND ", $where) . " ORDER BY expires_at DESC");
                    $stmt->execute($params);
                    $subs = $stmt->fetchAll() ?: [];
                }
            } catch (\Throwable $e) {}
        }

        // 2. Query Supabase REST if needed or as primary fallback
        if (empty($subs) && $userId) {
            $res = $this->request("subscriptions?user_id=eq." . urlencode($userId) . "&status=eq.active&order=expires_at.desc");
            $subs = $res['data'] ?? [];
            if (!is_array($subs)) $subs = [];
        }

        if (empty($subs) && $username) {
            $res = $this->request("subscriptions?username=eq." . urlencode($username) . "&status=eq.active&order=expires_at.desc");
            $subs = $res['data'] ?? [];
            if (!is_array($subs)) $subs = [];
        }

        if (empty($subs) && $userId) {
            $res = $this->request("user_subscriptions?user_id=eq." . urlencode($userId) . "&status=eq.active&order=expires_at.desc");
            $subs = $res['data'] ?? [];
            if (!is_array($subs)) $subs = [];
        }

        // 3. Filter server-side server time: status === 'active' AND expires_at > now
        $activeOnly = [];
        foreach ($subs as $s) {
            $expiresTs = strtotime($s['expires_at'] ?? '');
            if (($s['status'] ?? '') === 'active' && $expiresTs > $nowTs) {
                $activeOnly[] = $s;
            }
        }

        return $activeOnly;
    }

    public function getActiveSubscription($userId) {
        $subs = $this->getActiveSubscriptions($userId);
        return is_array($subs) && count($subs) > 0 ? $subs[0] : null;
    }

    public function cleanupExpiredSubscriptions($userId = null) {
        $nowIso = date('c');
        $nowTs = time();

        if (function_exists('getMarketPDO')) {
            try {
                $pdo = getMarketPDO();
                $sql = "UPDATE subscriptions SET status = 'expired', updated_at = :now WHERE status = 'active' AND datetime(expires_at) <= datetime(:now2)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':now' => $nowIso, ':now2' => $nowIso]);

                $sql2 = "UPDATE user_subscriptions SET status = 'expired', updated_at = :now WHERE status = 'active' AND datetime(expires_at) <= datetime(:now2)";
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->execute([':now' => $nowIso, ':now2' => $nowIso]);
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Log Admin Audit Actions into local SQLite and Supabase
     */
    public function logAdminAction($adminUser, $action, $userId, $grantedPlan, $oldStatus, $newStatus, $details = '', $resultStatus = 'success') {
        $now = date('c');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $adminId = $_SESSION['admin_id'] ?? 'admin';

        $logData = [
            'id'           => sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)),
            'admin_user'   => $adminUser,
            'admin_id'     => $adminId,
            'user_id'      => $userId ?: 'N/A',
            'action'       => $action,
            'granted_plan' => $grantedPlan,
            'old_status'   => $oldStatus,
            'new_status'   => $newStatus,
            'ip_address'   => $ip,
            'details'      => $details,
            'result'       => $resultStatus,
            'created_at'   => $now,
        ];

        if (function_exists('sqlite_admin_post')) {
            sqlite_admin_post('admin_audit_logs', $logData);
        }
        if (function_exists('sb_admin_post')) {
            sb_admin_post('admin_audit_logs', $logData);
        }
    }

    /**
     * Production-Grade Grant Subscription Engine with ACID PDO Transactions
     * Supports: copytrading, grid_monthly, grid_lifetime, classes_online, classes_physical, elite_*
     */
    public function grantSubscription($adminUser, $userId, $username, $plan, $durationDays = 30, $notes = '', $grantMethod = 'Manual Grant') {
        $plan = strtolower(trim($plan));
        if (empty($plan) || (!isset(self::PLAN_NAMES[$plan]) && strpos($plan, 'elite_') !== 0)) {
            return [
                'success' => false,
                'message' => 'Invalid subscription plan specified.'
            ];
        }

        $durationDays = (int)$durationDays;
        if ($durationDays <= 0) {
            $durationDays = 30;
        }

        $planName = self::PLAN_NAMES[$plan] ?? (strpos($plan, 'elite_') === 0 ? 'BM Elites — Premium Tier' : ucwords(str_replace('_', ' ', $plan)));
        $usdAmount = self::PLAN_AMOUNTS_USD[$plan] ?? 0;
        $kesAmount = round($usdAmount * 129.00, 2);

        $nowTs = time();
        $nowIso = date('c');

        $pdo = function_exists('getMarketPDO') ? getMarketPDO() : null;
        $inTransaction = false;

        if ($pdo) {
            try {
                $pdo->beginTransaction();
                $inTransaction = true;
            } catch (\Throwable $e) {
                $inTransaction = false;
            }
        }

        try {
            // 1. Resolve & Validate Target User
            $resolvedUserId = $userId;
            $resolvedUsername = $username;

            if (!$resolvedUserId && $resolvedUsername) {
                if ($pdo) {
                    $stmt = $pdo->prepare("SELECT id, username, email FROM profiles WHERE LOWER(username) = :u OR LOWER(email) = :u LIMIT 1");
                    $stmt->execute([':u' => strtolower($resolvedUsername)]);
                    $row = $stmt->fetch();
                    if ($row) {
                        $resolvedUserId = $row['id'];
                        $resolvedUsername = $row['username'];
                    }
                }
                if (!$resolvedUserId && function_exists('sb_admin_get')) {
                    $res = sb_admin_get('profiles', ['select' => 'id,username,email']);
                    if ($res && !empty($res['data']) && is_array($res['data'])) {
                        $needle = strtolower($resolvedUsername);
                        foreach ($res['data'] as $p) {
                            if (strtolower($p['username'] ?? '') === $needle || strtolower($p['email'] ?? '') === $needle) {
                                $resolvedUserId = $p['id'];
                                $resolvedUsername = !empty($p['username']) ? $p['username'] : $resolvedUsername;
                                break;
                            }
                        }
                    }
                }
            }

            // Ensure profile exists in local SQLite DB
            if ($pdo && $resolvedUserId && $resolvedUsername) {
                $stmt = $pdo->prepare("INSERT OR IGNORE INTO profiles (id, username, role, status, created_at, updated_at) VALUES (?, ?, 'user', 'active', ?, ?)");
                $stmt->execute([$resolvedUserId, $resolvedUsername, $nowIso, $nowIso]);
            }

            // 2. Check existing active subscription for user & plan (Stacking Duration Rule)
            $baseTime = $nowTs;
            $oldStatus = 'none';
            $subId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));

            if ($pdo && ($resolvedUserId || $resolvedUsername)) {
                $stmt = $pdo->prepare("SELECT id, expires_at, status FROM subscriptions WHERE (user_id = :uid OR LOWER(username) = :uname) AND plan = :plan AND status = 'active' ORDER BY expires_at DESC LIMIT 1");
                $stmt->execute([
                    ':uid'   => $resolvedUserId ?: '',
                    ':uname' => strtolower($resolvedUsername ?: ''),
                    ':plan'  => $plan
                ]);
                $existing = $stmt->fetch();

                if ($existing) {
                    $oldStatus = 'active';
                    $existingExpiresTs = strtotime($existing['expires_at']);
                    if ($existingExpiresTs > $nowTs) {
                        $baseTime = $existingExpiresTs; // Stack duration on top of current active expiry
                    }
                    $subId = $existing['id']; // Update existing subscription ID
                }
            }

            $expiresAtTs = strtotime("+$durationDays days", $baseTime);
            $expiresAt = date('c', $expiresAtTs);

            // 3. Update / Insert Subscriptions in SQLite (Transaction Scope)
            if ($pdo) {
                $stmt = $pdo->prepare("
                    INSERT OR REPLACE INTO subscriptions 
                    (id, user_id, username, plan, plan_key, plan_name, amount_usd, amount_kes, status, granted_by, grant_method, starts_at, expires_at, notes, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $subId,
                    $resolvedUserId ?: null,
                    $resolvedUsername,
                    $plan,
                    $plan,
                    $planName,
                    $usdAmount,
                    $kesAmount,
                    $adminUser,
                    $grantMethod,
                    $nowIso,
                    $expiresAt,
                    $notes,
                    $nowIso,
                    $nowIso
                ]);

                // Also sync user_subscriptions
                $stmt2 = $pdo->prepare("
                    INSERT OR REPLACE INTO user_subscriptions 
                    (id, user_id, username, plan, plan_name, amount_usd, amount_kes, status, granted_by, starts_at, expires_at, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?, ?)
                ");
                $stmt2->execute([
                    $subId,
                    $resolvedUserId ?: null,
                    $resolvedUsername,
                    $plan,
                    $planName,
                    $usdAmount,
                    $kesAmount,
                    $adminUser,
                    $nowIso,
                    $expiresAt,
                    $nowIso,
                    $nowIso
                ]);

                // 4. Create Payment Audit Record in SQLite
                $payId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
                $payStmt = $pdo->prepare("
                    INSERT INTO payments (id, user_id, username, plan, amount, amount_usd, amount_kes, status, merchant_txn_id, notes, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'succeeded', ?, ?, ?, ?)
                ");
                $payStmt->execute([
                    $payId,
                    $resolvedUserId ?: null,
                    $resolvedUsername,
                    $plan,
                    $kesAmount,
                    $usdAmount,
                    $kesAmount,
                    'ADMIN_GRANT_' . strtoupper(uniqid()),
                    'Granted by admin ' . $adminUser . ($notes ? " ($notes)" : ''),
                    $nowIso,
                    $nowIso
                ]);
            }

            // 5. Sync to Supabase REST
            $subData = [
                'id'          => $subId,
                'user_id'     => $resolvedUserId ?: null,
                'username'    => $resolvedUsername,
                'plan'        => $plan,
                'plan_key'    => $plan,
                'plan_name'   => $planName,
                'amount_usd'  => $usdAmount,
                'amount_kes'  => $kesAmount,
                'status'      => 'active',
                'granted_by'  => $adminUser,
                'grant_method'=> $grantMethod,
                'starts_at'   => $nowIso,
                'expires_at'  => $expiresAt,
                'notes'       => $notes,
                'created_at'  => $nowIso,
                'updated_at'  => $nowIso,
            ];
            if (function_exists('sb_admin_post')) {
                sb_admin_post('subscriptions', $subData);
                sb_admin_post('user_subscriptions', [
                    'id'          => $subId,
                    'user_id'     => $resolvedUserId ?: null,
                    'username'    => $resolvedUsername,
                    'plan'        => $plan,
                    'plan_name'   => $planName,
                    'amount_usd'  => $usdAmount,
                    'amount_kes'  => $kesAmount,
                    'status'      => 'active',
                    'granted_by'  => $adminUser,
                    'starts_at'   => $nowIso,
                    'expires_at'  => $expiresAt,
                    'created_at'  => $nowIso,
                    'updated_at'  => $nowIso,
                ]);
            }

            // Sync to indicator_subscriptions if it's an indicator plan
            if (strpos($plan, 'indicator_') === 0) {
                if ($pdo) {
                    try {
                        $indStmt = $pdo->prepare("
                            INSERT OR REPLACE INTO indicator_subscriptions
                            (id, user_id, user_name, user_email, plan_key, plan_name, status, indicator_access, signals_access, ai_access, premium_dashboard, amount_paid_usd, amount_paid_kes, payment_reference, starts_at, expires_at, created_at, updated_at)
                            VALUES (?, ?, ?, ?, ?, ?, 'active', 1, 1, 1, 1, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $indStmt->execute([
                            $subId,
                            $resolvedUserId ?: null,
                            $resolvedUsername,
                            $resolvedUsername . '@bmforexhub.exchange',
                            $plan,
                            $planName,
                            $usdAmount,
                            $kesAmount,
                            'ADMIN_GRANT_' . strtoupper(uniqid()),
                            $nowIso,
                            $expiresAt,
                            $nowIso,
                            $nowIso
                        ]);
                    } catch (\Throwable $e) {}
                }
                if (function_exists('sb_admin_post')) {
                    try {
                        sb_admin_post('indicator_subscriptions', [
                            'id'                => $subId,
                            'user_id'           => $resolvedUserId ?: null,
                            'user_name'         => $resolvedUsername,
                            'user_email'        => $resolvedUsername . '@bmforexhub.exchange',
                            'plan_key'          => $plan,
                            'plan_name'         => $planName,
                            'status'            => 'active',
                            'indicator_access'  => true,
                            'signals_access'    => true,
                            'ai_access'         => true,
                            'premium_dashboard' => true,
                            'amount_paid_usd'   => $usdAmount,
                            'amount_paid_kes'   => $kesAmount,
                            'payment_reference' => 'ADMIN_GRANT_' . strtoupper(uniqid()),
                            'starts_at'         => $nowIso,
                            'expires_at'        => $expiresAt,
                            'created_at'        => $nowIso,
                            'updated_at'        => $nowIso,
                        ]);
                    } catch (\Throwable $e) {}
                }
            }

            // 6. Log Audit Action
            $this->logAdminAction($adminUser, 'GRANT_SUBSCRIPTION', $resolvedUserId, $planName, $oldStatus, 'active', "Granted for $durationDays days. Notes: $notes");

            // Commit Transaction
            if ($inTransaction && $pdo) {
                $pdo->commit();
            }

            return [
                'success' => true,
                'message' => "Subscription ($planName) granted to user @$resolvedUsername successfully for $durationDays days (expires " . date('Y-m-d H:i', $expiresAtTs) . ").",
                'data'    => $subData
            ];

        } catch (\Throwable $e) {
            if ($inTransaction && $pdo) {
                $pdo->rollBack();
            }
            $this->logAdminAction($adminUser, 'GRANT_SUBSCRIPTION', $userId, $plan, 'error', 'failed', $e->getMessage(), 'error');
            return [
                'success' => false,
                'message' => 'Failed to grant subscription: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Unified Grant Elite Membership
     */
    public function grantEliteMembership($adminUser, $username, $plan, $durationDays = 30, $amount = 10000, $notes = '', $userId = null) {
        $plan = strtolower(trim($plan));
        if (strpos($plan, 'elite_') !== 0) {
            $plan = self::PLAN_ELITE_ELITE;
        }

        return $this->grantSubscription($adminUser, $userId, $username, $plan, $durationDays, $notes, 'Elite Grant');
    }

    public function revokeEliteMembership($id, $adminUser = 'Admin') {
        $pdo = function_exists('getMarketPDO') ? getMarketPDO() : null;
        $nowIso = date('c');

        if ($pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE subscriptions SET status = 'cancelled', updated_at = ? WHERE id = ?");
                $stmt->execute([$nowIso, $id]);
                $stmt2 = $pdo->prepare("UPDATE user_subscriptions SET status = 'cancelled', updated_at = ? WHERE id = ?");
                $stmt2->execute([$nowIso, $id]);
            } catch (\Throwable $e) {}
        }

        if (function_exists('sb_admin_post')) {
            sb_admin_post('subscriptions?id=eq.' . $id, ['status' => 'cancelled', 'updated_at' => $nowIso], 'PATCH');
            sb_admin_post('user_subscriptions?id=eq.' . $id, ['status' => 'cancelled', 'updated_at' => $nowIso], 'PATCH');
        }

        $this->logAdminAction($adminUser, 'REVOKE_SUBSCRIPTION', $id, 'N/A', 'active', 'cancelled', 'Revoked subscription ID: ' . $id);

        return ['success' => true, 'message' => 'Subscription revoked successfully.'];
    }

    public function getTrialStatus($userId) {
        $res = $this->request("profiles?id=eq.$userId&select=trial_started_at");
        $data = $res['data'] ?? [];
        $trialStarted = null;

        if (is_array($data) && isset($data[0])) {
            $trialStarted = $data[0]['trial_started_at'] ?? null;
        }

        if (!$trialStarted) {
            $nowISO = date('c');
            $this->request("profiles?id=eq.$userId", 'PATCH', ['trial_started_at' => $nowISO]);
            $trialStarted = $nowISO;
        }

        $trialEnd = strtotime($trialStarted) + (3 * 86400);
        $nowTs = time();
        $trialActive = $nowTs < $trialEnd;
        $trialDaysLeft = max(0, floor(($trialEnd - $nowTs) / 86400));
        $trialHoursLeft = $trialActive ? floor((($trialEnd - $nowTs) % 86400) / 3600) : 0;

        return [
            'trial_active'     => $trialActive,
            'trial_days_left'  => $trialDaysLeft,
            'trial_hours_left' => $trialHoursLeft,
            'trial_started'    => $trialStarted,
        ];
    }
}
