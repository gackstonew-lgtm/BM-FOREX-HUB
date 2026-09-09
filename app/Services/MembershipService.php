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
    const PLAN_ELITE_PREMIUM      = 'elite_premium';
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
        self::PLAN_ELITE_PREMIUM      => 'BM Elites — $6,000 USD',
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
        self::PLAN_ELITE_PREMIUM      => 6000.00,
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
        if ($method === 'PATCH') {
            $headers[] = 'Prefer: return=representation';
        } elseif ($method === 'POST') {
            $headers[] = 'Prefer: return=representation';
        }
        
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
        ];
        if ($body !== null) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($body);
        }
        curl_setopt_array($ch, $opts);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);
        
        $data = json_decode($resp, true);
        return [
            'code'    => $code,
            'data'    => $data,
            'success' => ($code >= 200 && $code < 300),
            'error'   => ($code < 200 || $code >= 300) ? (!empty($data['message']) ? $data['message'] : (!empty($data['error']) ? $data['error'] : ($curlErr ?: "HTTP $code"))) : null
        ];
    }

    /**
     * Resolve target user by UUID, username, or email address across Supabase Auth and Profiles
     */
    public function resolveTargetUser($identifier) {
        $identifier = trim($identifier ?? '');
        if (empty($identifier)) return null;

        $targetLower = strtolower($identifier);
        $isUuid = (bool)preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $identifier);

        // 1. Check Supabase profiles table
        if (function_exists('sb_admin_get')) {
            $params = [
                'select' => 'id,username,email,first_name,last_name,role,status',
            ];
            if ($isUuid) {
                $params['or'] = "(id.eq.{$identifier},username.ilike.{$identifier},email.ilike.{$identifier})";
            } else {
                $params['or'] = "(username.ilike.{$identifier},email.ilike.{$identifier})";
            }
            $res = sb_admin_get('profiles', $params);
            if (!empty($res['success']) && !empty($res['data']) && is_array($res['data'])) {
                foreach ($res['data'] as $p) {
                    if (($p['id'] ?? '') === $identifier || strtolower($p['username'] ?? '') === $targetLower || strtolower($p['email'] ?? '') === $targetLower) {
                        return [
                            'id'       => $p['id'],
                            'username' => !empty($p['username']) ? $p['username'] : (explode('@', $p['email'] ?? '')[0] ?: $identifier),
                            'email'    => $p['email'] ?? '',
                        ];
                    }
                }
            }
        }

        // 2. Query Supabase Auth admin users API with pagination
        if (function_exists('sb_auth_admin_users')) {
            $page = 1;
            $perPage = 1000;
            while (true) {
                $authRes = sb_auth_admin_users("?page={$page}&per_page={$perPage}");
                $userList = $authRes['data']['users'] ?? (is_array($authRes['data'] ?? null) ? $authRes['data'] : []);
                if (empty($userList) || !is_array($userList)) {
                    break;
                }

                foreach ($userList as $u) {
                    $uId = $u['id'] ?? '';
                    $uEmail = strtolower($u['email'] ?? '');
                    $uMetaName = strtolower($u['user_metadata']['username'] ?? ($u['user_metadata']['user_name'] ?? ''));
                    $uEmailPrefix = strtolower(explode('@', $u['email'] ?? '')[0] ?? '');

                    if ($uId === $identifier || $uEmail === $targetLower || $uMetaName === $targetLower || $uEmailPrefix === $targetLower) {
                        $uname = $u['user_metadata']['username'] ?? ($u['user_metadata']['user_name'] ?? ($u['email'] ? explode('@', $u['email'])[0] : $identifier));
                        
                        // Ensure profile exists in Supabase profiles
                        if (function_exists('sb_admin_post')) {
                            try {
                                sb_admin_post('profiles', [
                                    'id'         => $uId,
                                    'username'   => $uname,
                                    'email'      => $u['email'] ?? null,
                                    'role'       => 'user',
                                    'status'     => 'active',
                                    'created_at' => date('c'),
                                ]);
                            } catch (\Throwable $e) {}
                        }

                        return [
                            'id'       => $uId,
                            'username' => $uname,
                            'email'    => $u['email'] ?? '',
                        ];
                    }
                }

                // Secondary pass: check if dot-separated token (e.g. lastname+suffix) matches
                $parts = explode('.', $targetLower);
                $suffixToken = end($parts);
                if (strlen($suffixToken) >= 6) {
                    foreach ($userList as $u) {
                        $uMetaName = strtolower($u['user_metadata']['username'] ?? ($u['user_metadata']['user_name'] ?? ''));
                        if (strpos($uMetaName, $suffixToken) !== false) {
                            $uId = $u['id'] ?? '';
                            $uname = $u['user_metadata']['username'] ?? ($u['user_metadata']['user_name'] ?? ($u['email'] ? explode('@', $u['email'])[0] : $identifier));
                            if (function_exists('sb_admin_post')) {
                                try {
                                    sb_admin_post('profiles', [
                                        'id'         => $uId,
                                        'username'   => $uname,
                                        'email'      => $u['email'] ?? null,
                                        'role'       => 'user',
                                        'status'     => 'active',
                                        'created_at' => date('c'),
                                    ]);
                                } catch (\Throwable $e) {}
                            }

                            return [
                                'id'       => $uId,
                                'username' => $uname,
                                'email'    => $u['email'] ?? '',
                            ];
                        }
                    }
                }

                if (count($userList) < $perPage) {
                    break;
                }
                $page++;
            }
        }

        // 3. Fallback check in local SQLite profiles
        if (function_exists('getMarketPDO')) {
            try {
                $pdo = getMarketPDO();
                $stmt = $pdo->prepare("SELECT id, username, email FROM profiles WHERE id = :id OR LOWER(username) = :u OR LOWER(email) = :u LIMIT 1");
                $stmt->execute([':id' => $identifier, ':u' => $targetLower]);
                $row = $stmt->fetch();
                if ($row) {
                    return [
                        'id'       => $row['id'],
                        'username' => $row['username'] ?: $identifier,
                        'email'    => $row['email'] ?: '',
                    ];
                }
            } catch (\Throwable $e) {}
        }

        return null;
    }

    /**
     * Maps an input plan key to schema-compliant base plan (conforming to PostgreSQL check constraint)
     */
    public function mapPlanForDatabase($planKey) {
        $planKey = strtolower(trim($planKey ?? ''));
        
        $usdAmount = self::PLAN_AMOUNTS_USD[$planKey] ?? 0;
        $planName  = self::PLAN_NAMES[$planKey] ?? ucwords(str_replace('_', ' ', $planKey));

        // Allowed values in Supabase check constraint 'subscriptions_plan_check':
        // ('vip', 'copytrading', 'grid_monthly', 'grid_lifetime', 'classes_online', 'classes_physical', 'classes')
        if (strpos($planKey, 'elite_') === 0 || strpos($planKey, 'indicator_') === 0 || $planKey === 'vip' || $planKey === 'all') {
            $basePlan = 'vip';
        } elseif (in_array($planKey, ['copytrading', 'grid_monthly', 'grid_lifetime', 'classes_online', 'classes_physical', 'classes'], true)) {
            $basePlan = $planKey;
        } else {
            $basePlan = 'vip';
        }

        try {
            $currencyService = new CurrencyConversionService();
            $rate = $currencyService->getExchangeRate();
        } catch (\Throwable $e) {
            $rate = 129.00;
        }

        $kesAmount = round($usdAmount * $rate, 2);

        return [
            'base_plan'  => $basePlan,
            'plan_key'   => $planKey,
            'plan_name'  => $planName,
            'amount_usd' => $usdAmount,
            'amount_kes' => $kesAmount,
        ];
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
            ['plan_name' => 'Premium', 'plan_key' => self::PLAN_ELITE_PREMIUM, 'amount_usd' => self::PLAN_AMOUNTS_USD[self::PLAN_ELITE_PREMIUM], 'amount_kes' => round(self::PLAN_AMOUNTS_USD[self::PLAN_ELITE_PREMIUM] * $rate, 2), 'description' => 'BM Elites Premium Investment'],
            ['plan_name' => 'Elite', 'plan_key' => self::PLAN_ELITE_ELITE, 'amount_usd' => self::PLAN_AMOUNTS_USD[self::PLAN_ELITE_ELITE], 'amount_kes' => round(self::PLAN_AMOUNTS_USD[self::PLAN_ELITE_ELITE] * $rate, 2), 'description' => 'BM Elites Ultimate VIP Investment'],
        ];
    }

    /**
     * Single Source of Truth: Get all consolidated Elite memberships from Supabase Subscriptions
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
                'plan_key'    => self::PLAN_ELITE_ELITE,
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

        // 2. Query Authoritative Supabase subscriptions table
        $subsRes = $this->request('subscriptions?select=*&order=created_at.desc');
        $subs = $subsRes['data'] ?? [];
        if (is_array($subs)) {
            foreach ($subs as $s) {
                $plan = $s['plan'] ?? '';
                $planKey = $s['plan_key'] ?? $plan;
                $isElite = (strpos($planKey, 'elite_') === 0 || strpos($plan, 'elite_') === 0 || $planKey === 'all' || ($plan === 'vip' && strpos($planKey, 'indicator_') === false));
                
                if ($isElite) {
                    $uname = !empty($s['username']) ? $s['username'] : (!empty($s['user_id']) ? substr($s['user_id'], 0, 8) : 'User');
                    $key = strtolower($uname);
                    if (!isset($membersMap[$key]) || strtotime($s['created_at'] ?? '') > strtotime($membersMap[$key]['created_at'] ?? '')) {
                        $rawAmt = (float)($s['amount_kes'] ?? 0);
                        $usdVal = (float)($s['amount_usd'] ?? (self::PLAN_AMOUNTS_USD[$planKey] ?? ($rawAmt <= 10000 && $rawAmt > 0 ? $rawAmt : 10000)));
                        $kesVal = $rawAmt > 0 ? $rawAmt : round($usdVal * $rate, 2);
                        $membersMap[$key] = [
                            'id'          => $s['id'] ?? uniqid(),
                            'user_id'     => $s['user_id'] ?? '',
                            'username'    => $uname,
                            'plan'        => $planKey,
                            'plan_key'    => $planKey,
                            'plan_name'   => self::PLAN_NAMES[$planKey] ?? ($s['plan_name'] ?? 'BM Elites'),
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

        // 3. Fallback query SQLite subscriptions if Supabase returned empty
        if (count($membersMap) <= count($permanentEmails) && function_exists('getMarketPDO')) {
            try {
                $pdo = getMarketPDO();
                $stmt = $pdo->query("SELECT * FROM subscriptions WHERE plan LIKE 'elite_%' OR plan_key LIKE 'elite_%' OR plan = 'vip' ORDER BY created_at DESC");
                $localSubs = $stmt->fetchAll() ?: [];
                foreach ($localSubs as $s) {
                    $planKey = $s['plan_key'] ?? $s['plan'];
                    $uname = !empty($s['username']) ? $s['username'] : 'User';
                    $key = strtolower($uname);
                    if (!isset($membersMap[$key])) {
                        $rawAmt = (float)($s['amount_kes'] ?? 0);
                        $usdVal = (float)($s['amount_usd'] ?? (self::PLAN_AMOUNTS_USD[$planKey] ?? 10000));
                        $kesVal = $rawAmt > 0 ? $rawAmt : round($usdVal * $rate, 2);
                        $membersMap[$key] = [
                            'id'          => $s['id'] ?? uniqid(),
                            'user_id'     => $s['user_id'] ?? '',
                            'username'    => $uname,
                            'plan'        => $planKey,
                            'plan_key'    => $planKey,
                            'plan_name'   => self::PLAN_NAMES[$planKey] ?? ($s['plan_name'] ?? 'BM Elites'),
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
            } catch (\Throwable $e) {}
        }

        return array_values($membersMap);
    }

    public function getActiveSubscriptions($userId, $username = null, $email = null) {
        $nowTs = time();
        $nowIso = date('c');

        $subs = [];

        // 1. Authoritative Supabase REST Query First
        if ($userId) {
            $res = $this->request("subscriptions?user_id=eq." . urlencode($userId) . "&status=eq.active&order=expires_at.desc");
            $subs = $res['data'] ?? [];
            if (!is_array($subs)) $subs = [];
        }

        if (empty($subs) && $username) {
            $res = $this->request("subscriptions?username=eq." . urlencode($username) . "&status=eq.active&order=expires_at.desc");
            $subs = $res['data'] ?? [];
            if (!is_array($subs)) $subs = [];
        }

        // 2. Query local SQLite database as fail-safe fallback
        if (empty($subs) && function_exists('getMarketPDO')) {
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

        if (function_exists('sb_admin_patch')) {
            try {
                sb_admin_patch("subscriptions?status=eq.active&expires_at=lt." . urlencode($nowIso), [
                    'status' => 'expired'
                ]);
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
            try {
                sb_admin_post('admin_audit_logs', $logData);
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Production-Grade Grant Subscription Engine with Supabase Backend Persistence & Verification
     * Supports: copytrading, grid_monthly, grid_lifetime, classes_online, classes_physical, elite_*, indicator_*
     */
    public function grantSubscription($adminUser, $userId, $username, $plan, $durationDays = 30, $notes = '', $grantMethod = 'Manual Grant') {
        $plan = strtolower(trim($plan ?? ''));
        if (empty($plan) || (!isset(self::PLAN_NAMES[$plan]) && strpos($plan, 'elite_') !== 0 && strpos($plan, 'indicator_') !== 0)) {
            return [
                'success' => false,
                'message' => 'Invalid subscription plan specified.'
            ];
        }

        $durationDays = (int)$durationDays;
        if ($durationDays <= 0) {
            $durationDays = 30;
        }

        // Map plan to schema-compliant database values
        $planMapping = $this->mapPlanForDatabase($plan);
        $basePlan    = $planMapping['base_plan'];
        $planKey     = $planMapping['plan_key'];
        $planName    = $planMapping['plan_name'];
        $usdAmount   = $planMapping['amount_usd'];
        $kesAmount   = $planMapping['amount_kes'];

        $nowTs = time();
        $nowIso = date('c');

        // 1. Authoritative Target User Resolution across Supabase
        $userObj = $this->resolveTargetUser($userId ?: $username);
        if (!$userObj || empty($userObj['id'])) {
            return [
                'success' => false,
                'message' => "Target user '" . htmlspecialchars($username ?: $userId) . "' was not found in Supabase. Please ensure the user has created an account."
            ];
        }

        $resolvedUserId   = $userObj['id'];
        $resolvedUsername = !empty($userObj['username']) ? $userObj['username'] : $username;
        $resolvedEmail    = $userObj['email'] ?? '';

        // 2. Check Existing Active Subscriptions in Supabase (Duration Stacking & Coexistence)
        $baseTime = $nowTs;
        $oldStatus = 'none';
        $subId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
        $existingSubIdToUpdate = null;

        $existingSubsRes = $this->request("subscriptions?user_id=eq." . urlencode($resolvedUserId) . "&status=eq.active&order=expires_at.desc");
        $existingSubs = $existingSubsRes['data'] ?? [];

        if (is_array($existingSubs)) {
            foreach ($existingSubs as $ex) {
                $exPlan = $ex['plan'] ?? '';
                $exKey  = $ex['plan_key'] ?? $exPlan;

                // Match exact tier or same product category for extension
                if ($exKey === $planKey || ($basePlan !== 'vip' && $exPlan === $basePlan)) {
                    $oldStatus = 'active';
                    $existingExpiresTs = strtotime($ex['expires_at'] ?? '');
                    if ($existingExpiresTs > $nowTs) {
                        $baseTime = $existingExpiresTs; // Stack on top of remaining active duration
                    }
                    $existingSubIdToUpdate = $ex['id'] ?? null;
                    if ($existingSubIdToUpdate) {
                        $subId = $existingSubIdToUpdate;
                    }
                    break;
                }
            }
        }

        $expiresAtTs = strtotime("+$durationDays days", $baseTime);
        $expiresAt = date('c', $expiresAtTs);

        // 3. Construct Clean, Schema-Compliant Payload for Supabase subscriptions table
        // Authoritative Columns: id, user_id, username, plan, plan_key, plan_name, amount_kes, status, granted_by, starts_at, expires_at, notes, created_at
        $supaSubData = [
            'id'          => $subId,
            'user_id'     => $resolvedUserId,
            'username'    => $resolvedUsername,
            'plan'        => $basePlan,
            'plan_key'    => $planKey,
            'plan_name'   => $planName,
            'amount_kes'  => $kesAmount,
            'status'      => 'active',
            'granted_by'  => $adminUser,
            'starts_at'   => $nowIso,
            'expires_at'  => $expiresAt,
            'notes'       => $notes ?: "Admin manual grant by $adminUser",
            'created_at'  => $nowIso,
        ];

        // 4. Perform Authoritative Mutation on Supabase
        $supaRes = null;
        if ($existingSubIdToUpdate) {
            // Update existing subscription record
            $supaRes = $this->request("subscriptions?id=eq." . urlencode($subId), 'PATCH', [
                'plan'       => $basePlan,
                'plan_key'   => $planKey,
                'plan_name'  => $planName,
                'amount_kes' => $kesAmount,
                'status'     => 'active',
                'granted_by' => $adminUser,
                'expires_at' => $expiresAt,
                'notes'      => $notes ?: "Extended/Updated by $adminUser",
            ]);
        } else {
            // Insert new subscription record
            $supaRes = $this->request('subscriptions', 'POST', $supaSubData);
            if (!$supaRes['success'] && ($supaRes['code'] == 409 || strpos(($supaRes['error'] ?? ''), 'duplicate key') !== false)) {
                $supaRes = $this->request("subscriptions?id=eq." . urlencode($subId), 'PATCH', [
                    'plan'       => $basePlan,
                    'plan_key'   => $planKey,
                    'plan_name'  => $planName,
                    'amount_kes' => $kesAmount,
                    'status'     => 'active',
                    'granted_by' => $adminUser,
                    'expires_at' => $expiresAt,
                    'notes'      => $notes,
                ]);
            }
        }

        if (!$supaRes['success']) {
            $err = $supaRes['error'] ?? 'Database mutation rejected by Supabase.';
            $this->logAdminAction($adminUser, 'GRANT_SUBSCRIPTION', $resolvedUserId, $planName, $oldStatus, 'failed', "Error: $err", 'error');
            return [
                'success' => false,
                'message' => "Failed to grant subscription in Supabase backend: $err"
            ];
        }

        // 5. Authoritative Verification Gate: Re-read directly from Supabase
        $verifyRes = $this->request("subscriptions?id=eq." . urlencode($subId) . "&select=*");
        $verifiedData = $verifyRes['data'] ?? [];
        $verifiedRecord = (is_array($verifiedData) && !empty($verifiedData[0])) ? $verifiedData[0] : null;

        if (!$verifiedRecord || ($verifiedRecord['status'] ?? '') !== 'active' || ($verifiedRecord['user_id'] ?? '') !== $resolvedUserId) {
            $this->logAdminAction($adminUser, 'GRANT_SUBSCRIPTION', $resolvedUserId, $planName, $oldStatus, 'failed', 'Supabase post-write verification failed', 'error');
            return [
                'success' => false,
                'message' => 'Subscription grant could not be verified in Supabase database after write.'
            ];
        }

        // 6. Fail-Safe Local SQLite Synchronization (ACID Scope)
        $pdo = function_exists('getMarketPDO') ? getMarketPDO() : null;
        if ($pdo) {
            try {
                $pdo->beginTransaction();

                // Ensure local profile
                $stmt = $pdo->prepare("INSERT OR REPLACE INTO profiles (id, username, email, role, status, created_at, updated_at) VALUES (?, ?, ?, 'user', 'active', ?, ?)");
                $stmt->execute([$resolvedUserId, $resolvedUsername, $resolvedEmail, $nowIso, $nowIso]);

                // Sync local subscriptions table
                $stmt2 = $pdo->prepare("
                    INSERT OR REPLACE INTO subscriptions 
                    (id, user_id, username, plan, plan_key, plan_name, amount_usd, amount_kes, status, granted_by, grant_method, starts_at, expires_at, notes, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt2->execute([
                    $subId,
                    $resolvedUserId,
                    $resolvedUsername,
                    $basePlan,
                    $planKey,
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

                // Sync local user_subscriptions
                $stmt3 = $pdo->prepare("
                    INSERT OR REPLACE INTO user_subscriptions 
                    (id, user_id, username, plan, plan_name, amount_usd, amount_kes, status, granted_by, starts_at, expires_at, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?, ?)
                ");
                $stmt3->execute([
                    $subId,
                    $resolvedUserId,
                    $resolvedUsername,
                    $planKey,
                    $planName,
                    $usdAmount,
                    $kesAmount,
                    $adminUser,
                    $nowIso,
                    $expiresAt,
                    $nowIso,
                    $nowIso
                ]);

                // Local payments audit ledger
                $payId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
                $payStmt = $pdo->prepare("
                    INSERT INTO payments (id, user_id, username, plan, amount, amount_usd, amount_kes, status, merchant_txn_id, notes, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'succeeded', ?, ?, ?, ?)
                ");
                $payStmt->execute([
                    $payId,
                    $resolvedUserId,
                    $resolvedUsername,
                    $planKey,
                    $kesAmount,
                    $usdAmount,
                    $kesAmount,
                    'ADMIN_GRANT_' . strtoupper(uniqid()),
                    'Granted by admin ' . $adminUser . ($notes ? " ($notes)" : ''),
                    $nowIso,
                    $nowIso
                ]);

                $pdo->commit();
            } catch (\Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
            }
        }

        // 7. Audit Log
        $this->logAdminAction($adminUser, 'GRANT_SUBSCRIPTION', $resolvedUserId, $planName, $oldStatus, 'active', "Granted $planName for $durationDays days (expires " . date('Y-m-d H:i', $expiresAtTs) . "). Notes: $notes");

        return [
            'success'         => true,
            'message'         => "Subscription ($planName) granted to user @$resolvedUsername successfully for $durationDays days (expires " . date('Y-m-d H:i', $expiresAtTs) . ").",
            'subscription_id' => $subId,
            'user_id'         => $resolvedUserId,
            'username'        => $resolvedUsername,
            'plan'            => $basePlan,
            'plan_key'        => $planKey,
            'plan_name'       => $planName,
            'amount_usd'      => $usdAmount,
            'amount_kes'      => $kesAmount,
            'status'          => 'active',
            'starts_at'       => $nowIso,
            'expires_at'      => $expiresAt,
            'data'            => $verifiedRecord
        ];
    }

    /**
     * Unified Grant Elite Membership
     */
    public function grantEliteMembership($adminUser, $username, $plan, $durationDays = 30, $amount = 10000, $notes = '', $userId = null) {
        $plan = strtolower(trim($plan ?? ''));
        if (strpos($plan, 'elite_') !== 0) {
            $plan = self::PLAN_ELITE_ELITE;
        }

        return $this->grantSubscription($adminUser, $userId, $username, $plan, $durationDays, $notes, 'Elite Grant');
    }

    public function extendSubscription($id, $days = 30, $adminUser = 'Admin') {
        $id = trim($id ?? '');
        $days = (int)$days;
        if ($days <= 0) $days = 30;

        if (empty($id)) {
            return ['success' => false, 'message' => 'Subscription ID is required.'];
        }

        // 1. Fetch Subscription from Supabase
        $subRes = $this->request("subscriptions?id=eq." . urlencode($id) . "&limit=1");
        $sub = $subRes['data'][0] ?? null;

        if (!$sub && function_exists('getMarketPDO')) {
            try {
                $pdo = getMarketPDO();
                $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE id = ? LIMIT 1");
                $stmt->execute([$id]);
                $sub = $stmt->fetch();
            } catch (\Throwable $e) {}
        }

        if (!$sub) {
            return ['success' => false, 'message' => 'Subscription record not found.'];
        }

        $nowTs = time();
        $curExpiresTs = strtotime($sub['expires_at'] ?? 'now');
        $baseTs = max($nowTs, $curExpiresTs);
        $newExpiresTs = strtotime("+$days days", $baseTs);
        $newExpiresIso = date('c', $newExpiresTs);
        $nowIso = date('c');

        // 2. Mutate Supabase
        $patchRes = $this->request("subscriptions?id=eq." . urlencode($id), 'PATCH', [
            'expires_at' => $newExpiresIso,
            'status'     => 'active',
            'notes'      => ($sub['notes'] ? $sub['notes'] . ' | ' : '') . "Extended $days days by $adminUser on " . date('Y-m-d H:i')
        ]);

        if (!$patchRes['success']) {
            return ['success' => false, 'message' => 'Failed to extend subscription in Supabase: ' . ($patchRes['error'] ?? 'Unknown error')];
        }

        // 3. Verify in Supabase
        $verifyRes = $this->request("subscriptions?id=eq." . urlencode($id) . "&select=*");
        $verified = $verifyRes['data'][0] ?? null;
        if (!$verified || ($verified['status'] ?? '') !== 'active') {
            return ['success' => false, 'message' => 'Subscription extension could not be verified in Supabase.'];
        }

        // 4. Sync Local SQLite
        if (function_exists('getMarketPDO')) {
            try {
                $pdo = getMarketPDO();
                $stmt = $pdo->prepare("UPDATE subscriptions SET expires_at = ?, status = 'active', updated_at = ? WHERE id = ?");
                $stmt->execute([$newExpiresIso, $nowIso, $id]);
                $stmt2 = $pdo->prepare("UPDATE user_subscriptions SET expires_at = ?, status = 'active', updated_at = ? WHERE id = ?");
                $stmt2->execute([$newExpiresIso, $nowIso, $id]);
            } catch (\Throwable $e) {}
        }

        $this->logAdminAction($adminUser, 'EXTEND_SUBSCRIPTION', $sub['user_id'] ?? $sub['username'], $sub['plan_name'] ?? ($sub['plan_key'] ?? 'Subscription'), $sub['status'] ?? 'active', 'active', "Extended by $days days (new expiry: " . date('Y-m-d H:i', $newExpiresTs) . ")");

        return [
            'success'        => true,
            'message'        => "Subscription extended successfully by $days days. New expiry: " . date('Y-m-d H:i', $newExpiresTs),
            'subscription_id'=> $id,
            'new_expires_at' => $newExpiresIso,
            'data'           => $verified
        ];
    }

    public function revokeEliteMembership($id, $adminUser = 'Admin') {
        $id = trim($id ?? '');
        if (empty($id)) {
            return ['success' => false, 'message' => 'Subscription ID is required.'];
        }

        $nowIso = date('c');

        // 1. Mutate Supabase
        $patchRes = $this->request("subscriptions?id=eq." . urlencode($id), 'PATCH', [
            'status' => 'cancelled',
            'notes'  => "Revoked by admin $adminUser on " . date('Y-m-d H:i')
        ]);

        if (!$patchRes['success']) {
            return ['success' => false, 'message' => 'Failed to revoke subscription in Supabase: ' . ($patchRes['error'] ?? 'Unknown error')];
        }

        // 2. Verify in Supabase
        $verifyRes = $this->request("subscriptions?id=eq." . urlencode($id) . "&select=*");
        $verified = $verifyRes['data'][0] ?? null;
        if (!$verified || ($verified['status'] ?? '') !== 'cancelled') {
            return ['success' => false, 'message' => 'Subscription revocation could not be verified in Supabase.'];
        }

        // 3. Sync Local SQLite
        if (function_exists('getMarketPDO')) {
            try {
                $pdo = getMarketPDO();
                $stmt = $pdo->prepare("UPDATE subscriptions SET status = 'cancelled', updated_at = ? WHERE id = ?");
                $stmt->execute([$nowIso, $id]);
                $stmt2 = $pdo->prepare("UPDATE user_subscriptions SET status = 'cancelled', updated_at = ? WHERE id = ?");
                $stmt2->execute([$nowIso, $id]);
            } catch (\Throwable $e) {}
        }

        $this->logAdminAction($adminUser, 'REVOKE_SUBSCRIPTION', $id, 'N/A', 'active', 'cancelled', 'Revoked subscription ID: ' . $id);

        return [
            'success' => true,
            'message' => 'Subscription access successfully revoked in Supabase.',
            'subscription_id' => $id,
            'status' => 'cancelled'
        ];
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
