<?php
namespace App\Services;

require_once __DIR__ . '/MembershipService.php';

class CopyTradingService {
    private static function getEncryptionKey(): string {
        $key = getenv('COPY_TRADING_ENCRYPTION_KEY');
        if (!$key) {
            // Secret fallback derived from system configuration
            $key = hash('sha256', (defined('SUPABASE_SERVICE_KEY') ? SUPABASE_SERVICE_KEY : 'BM_FOREX_HUB_COPY_TRADING_SECRET_2026'));
        }
        return substr(hash('sha256', $key), 0, 32);
    }

    /**
     * Encrypt MT5 Password using AES-256-CBC + HMAC-SHA256
     */
    public static function encryptPassword(string $plainText): string {
        if ($plainText === '') return '';
        $key = self::getEncryptionKey();
        $cipher = 'aes-256-cbc';
        $ivLen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivLen);
        $encrypted = openssl_encrypt($plainText, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $encrypted, $key, true);
        return base64_encode($iv . $encrypted . $hmac);
    }

    /**
     * Decrypt MT5 Password
     */
    public static function decryptPassword(string $cipherText): string {
        if ($cipherText === '') return '';
        $data = base64_decode($cipherText, true);
        if ($data === false) return $cipherText; // Fallback if unencrypted legacy

        $key = self::getEncryptionKey();
        $cipher = 'aes-256-cbc';
        $ivLen = openssl_cipher_iv_length($cipher);
        $hmacLen = 32;

        if (strlen($data) < ($ivLen + $hmacLen)) {
            return $cipherText;
        }

        $iv = substr($data, 0, $ivLen);
        $encrypted = substr($data, $ivLen, -$hmacLen);
        $hmac = substr($data, -$hmacLen);

        $calcedHmac = hash_hmac('sha256', $encrypted, $key, true);
        if (!hash_equals($hmac, $calcedHmac)) {
            return '[Decryption Failed - Key Mismatch]';
        }

        $decrypted = openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted !== false ? $decrypted : '[Decryption Error]';
    }

    /**
     * Verify if user has an active Copy Trading subscription
     */
    public static function hasActiveSubscription(string $userId, ?string $userEmail = null): bool {
        // Check if user is suspended
        if (function_exists('getMarketPDO')) {
            try {
                $pdo = getMarketPDO();
                $stmt = $pdo->prepare("SELECT status FROM profiles WHERE id = ? LIMIT 1");
                $stmt->execute([$userId]);
                $row = $stmt->fetch();
                if (!empty($row['status']) && $row['status'] === 'suspended') {
                    return false;
                }
            } catch (\Throwable $e) {}
        }

        if ($userEmail && function_exists('bm_has_permanent_access') && bm_has_permanent_access($userEmail)) {
            return true;
        }

        $membershipService = new MembershipService();
        $activeSubs = $membershipService->getActiveSubscriptions($userId, null, $userEmail);

        if (is_array($activeSubs)) {
            foreach ($activeSubs as $sub) {
                $plan = strtolower($sub['plan'] ?? '');
                if ($plan === 'copytrading' || $plan === 'copy_trading' || $plan === 'all' || strpos($plan, 'elite_') === 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Fetch Copy Trader submission by User ID
     */
    public static function getCopyTraderByUserId(string $userId): ?array {
        if (function_exists('sb_admin_get')) {
            $res = sb_admin_get("copy_traders?user_id=eq.$userId&limit=1");
            if (!empty($res['data'][0])) {
                return $res['data'][0];
            }
        }

        // SQLite fallback
        if (function_exists('sqlite_admin_get')) {
            $data = sqlite_admin_get('copy_traders', ['user_id' => "eq.$userId", 'limit' => 1]);
            if (!empty($data[0])) {
                return $data[0];
            }
        }
        return null;
    }

    /**
     * Save or Update Copy Trader Details
     */
    public static function saveCopyTrader(array $payload): array {
        $now = date('c');
        $existing = self::getCopyTraderByUserId($payload['user_id']);

        $encryptedPass = self::encryptPassword($payload['mt5_password']);

        $record = [
            'id'                   => $existing['id'] ?? ('ct_' . sprintf('%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff))),
            'user_id'              => $payload['user_id'],
            'full_name'            => $payload['full_name'] ?? 'Trader',
            'email'                => $payload['email'],
            'subscription_plan'    => $payload['subscription_plan'] ?? 'copytrading',
            'payment_reference'    => $payload['payment_reference'] ?? ('PAY_' . strtoupper(uniqid())),
            'broker_name'          => trim($payload['broker_name']),
            'mt5_login'            => trim($payload['mt5_login']),
            'mt5_password'         => $encryptedPass,
            'mt5_server'           => trim($payload['mt5_server']),
            'notes'                => trim($payload['notes'] ?? ''),
            'status'               => $existing['status'] ?? 'Pending',
            'created_at'           => $existing['created_at'] ?? $now,
            'updated_at'           => $now
        ];

        if (function_exists('sb_admin_post')) {
            sb_admin_post('copy_traders', $record);
        } else if (function_exists('sqlite_admin_post')) {
            sqlite_admin_post('copy_traders', $record);
        }

        return $record;
    }

    /**
     * Fetch all Copy Traders combining Supabase subscriptions and MT5 account submissions
     */
    public static function getAllCopyTraders(): array {
        $nowIso = date('c');
        $nowTs  = time();
        $membershipService = new MembershipService();

        try {
            $currencyService = new CurrencyConversionService();
            $rate = $currencyService->getExchangeRate();
        } catch (\Throwable $e) {
            $rate = 129.44;
        }

        $defaultAmountKes = round(249 * $rate, 2);

        $tradersMap = [];

        // 1. Permanent Admin Access users
        $permanentEmails = ['bonfacewana3072@gmail.com', 'langatgift6@gmail.com', 'gackstoneb@gmail.com'];
        foreach ($permanentEmails as $pEmail) {
            $username = explode('@', $pEmail)[0];
            $key = strtolower($username);
            $tradersMap[$key] = [
                'id'                  => 'perm-ct-' . md5($pEmail),
                'subscription_id'     => 'perm-sub-' . md5($pEmail),
                'user_id'             => 'perm-' . md5($pEmail),
                'username'            => $username,
                'email'               => $pEmail,
                'full_name'           => ucwords(str_replace(['.', '_'], ' ', $username)) . ' (Admin)',
                'plan'                => 'copytrading',
                'plan_name'           => 'Copy Trading — Permanent Admin Access',
                'amount_usd'          => 249,
                'amount_kes'          => $defaultAmountKes,
                'broker_name'         => 'BM Markets / Multi-Broker',
                'mt5_server'          => 'BMForexHub-Live',
                'mt5_login'           => 'ADMIN-MASTER',
                'mt5_password_set'    => true,
                'notes'               => 'Superadmin Master Access',
                'mt5_status'          => 'Active',
                'subscription_status' => 'active',
                'starts_at'           => date('c', strtotime('-30 days')),
                'expires_at'          => '2036-12-31T23:59:59+00:00',
                'granted_by'          => 'System (Permanent)',
                'created_at'          => date('c', strtotime('-30 days')),
                'is_permanent'        => true
            ];
        }

        // 2. Fetch all Copy Trading Subscriptions from Supabase
        if (function_exists('sb_admin_get')) {
            $subRes = sb_admin_get('subscriptions', [
                'select' => '*',
                'or'     => '(plan.eq.copytrading,plan_key.eq.copytrading,plan.eq.copy_trading,plan_key.eq.copy_trading)',
                'order'  => 'created_at.desc',
                'limit'  => 500
            ]);

            if (!empty($subRes['data']) && is_array($subRes['data'])) {
                foreach ($subRes['data'] as $sub) {
                    $uId = $sub['user_id'] ?? '';
                    $uName = $sub['username'] ?? '';
                    $key = $uId ?: strtolower($uName);
                    if (empty($key)) continue;

                    // Resolve user details if needed
                    $userEmail = $sub['email'] ?? '';
                    if (empty($uName) || empty($userEmail)) {
                        $resolved = $membershipService->resolveTargetUser($uId ?: $uName);
                        if ($resolved) {
                            $uName = $resolved['username'] ?: $uName;
                            $userEmail = $resolved['email'] ?: $userEmail;
                        }
                    }

                    $expTs = !empty($sub['expires_at']) ? strtotime($sub['expires_at']) : 0;
                    $isExpired = $expTs > 0 && $expTs <= $nowTs;
                    $subStatus = strtolower($sub['status'] ?? 'active');
                    if ($subStatus === 'active' && $isExpired) {
                        $subStatus = 'expired';
                    }

                    $usd = !empty($sub['amount_usd']) ? (float)$sub['amount_usd'] : 249;
                    $kes = !empty($sub['amount_kes']) ? (float)$sub['amount_kes'] : round($usd * $rate, 2);

                    $tradersMap[$key] = [
                        'id'                  => 'ct_sub_' . ($sub['id'] ?? uniqid()),
                        'subscription_id'     => $sub['id'] ?? null,
                        'user_id'             => $uId,
                        'username'            => $uName ?: ($userEmail ? explode('@', $userEmail)[0] : 'Trader'),
                        'email'               => $userEmail,
                        'full_name'           => $sub['full_name'] ?? ($uName ?: 'Trader'),
                        'plan'                => 'copytrading',
                        'plan_name'           => $sub['plan_name'] ?? 'Copy Trading Integration',
                        'amount_usd'          => $usd,
                        'amount_kes'          => $kes,
                        'broker_name'         => '--',
                        'mt5_server'          => '--',
                        'mt5_login'           => '--',
                        'mt5_password_set'    => false,
                        'notes'               => $sub['notes'] ?? '',
                        'mt5_status'          => 'Awaiting MT5 Setup',
                        'subscription_status' => $subStatus,
                        'starts_at'           => $sub['starts_at'] ?? $sub['created_at'] ?? $nowIso,
                        'expires_at'          => $sub['expires_at'] ?? date('c', strtotime('+36500 days')),
                        'granted_by'          => $sub['granted_by'] ?? ($sub['payment_id'] ? 'Payment Checkout' : 'Admin'),
                        'created_at'          => $sub['created_at'] ?? $nowIso,
                        'is_permanent'        => false
                    ];
                }
            }
        }

        // 3. Fetch all MT5 Account Submissions from copy_traders table
        $mt5Records = [];
        if (function_exists('sb_admin_get')) {
            $ctRes = sb_admin_get('copy_traders', ['select' => '*', 'order' => 'created_at.desc', 'limit' => 500]);
            if (!empty($ctRes['data']) && is_array($ctRes['data'])) {
                $mt5Records = $ctRes['data'];
            }
        }

        if (empty($mt5Records) && function_exists('getMarketPDO')) {
            try {
                $pdo = getMarketPDO();
                $stmt = $pdo->query("SELECT * FROM copy_traders ORDER BY created_at DESC");
                $mt5Records = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e) {}
        }

        foreach ($mt5Records as $mt5) {
            $uId = $mt5['user_id'] ?? '';
            $email = strtolower($mt5['email'] ?? '');
            $key = $uId ?: $email;
            if (empty($key)) continue;

            $existing = $tradersMap[$uId] ?? ($tradersMap[$email] ?? null);

            $rawStatus = trim($mt5['status'] ?? 'Pending');
            $statusNormalized = in_array(strtolower($rawStatus), ['active', 'connected']) ? 'Active' : (in_array(strtolower($rawStatus), ['cancelled', 'revoked']) ? 'Cancelled' : (in_array(strtolower($rawStatus), ['disconnected', 'inactive']) ? 'Disconnected' : 'Pending'));

            if ($existing) {
                $existing['id']               = $mt5['id'];
                $existing['broker_name']      = $mt5['broker_name'] ?: ($existing['broker_name'] ?? '--');
                $existing['mt5_server']       = $mt5['mt5_server'] ?: ($existing['mt5_server'] ?? '--');
                $existing['mt5_login']        = $mt5['mt5_login'] ?: ($existing['mt5_login'] ?? '--');
                $existing['mt5_password_set'] = !empty($mt5['mt5_password']);
                $existing['mt5_status']       = $statusNormalized;
                $existing['full_name']        = $mt5['full_name'] ?: $existing['full_name'];
                $existing['notes']            = ($existing['notes'] ? $existing['notes'] . ' | ' : '') . ($mt5['notes'] ?? '');
                $existing['created_at']       = $mt5['created_at'] ?: $existing['created_at'];
                $tradersMap[$key] = $existing;
            } else {
                $resolved = $membershipService->resolveTargetUser($uId ?: $email);
                $uname = $resolved['username'] ?? ($email ? explode('@', $email)[0] : 'Trader');
                $tradersMap[$key] = [
                    'id'                  => $mt5['id'],
                    'subscription_id'     => null,
                    'user_id'             => $uId,
                    'username'            => $uname,
                    'email'               => $email,
                    'full_name'           => $mt5['full_name'] ?: $uname,
                    'plan'                => 'copytrading',
                    'plan_name'           => 'Copy Trading Integration',
                    'amount_usd'          => 249,
                    'amount_kes'          => $defaultAmountKes,
                    'broker_name'         => $mt5['broker_name'] ?: '--',
                    'mt5_server'          => $mt5['mt5_server'] ?: '--',
                    'mt5_login'           => $mt5['mt5_login'] ?: '--',
                    'mt5_password_set'    => !empty($mt5['mt5_password']),
                    'notes'               => $mt5['notes'] ?? '',
                    'mt5_status'          => $statusNormalized,
                    'subscription_status' => ($statusNormalized === 'Active' || $statusNormalized === 'Pending') ? 'active' : 'cancelled',
                    'starts_at'           => $mt5['created_at'] ?? $nowIso,
                    'expires_at'          => date('c', strtotime('+36500 days')),
                    'granted_by'          => 'Direct MT5 Form',
                    'created_at'          => $mt5['created_at'] ?? $nowIso,
                    'is_permanent'        => false
                ];
            }
        }

        return array_values($tradersMap);
    }

    /**
     * Grant Copy Trading Subscription & optionally establish MT5 credentials
     */
    public static function grantCopyTradingAccess(string $adminUser, string $identifier, int $durationDays = 36500, string $notes = 'Admin Manual Grant', ?array $mt5Payload = null): array {
        $membershipService = new MembershipService();
        $user = $membershipService->resolveTargetUser($identifier);

        if (!$user) {
            return ['success' => false, 'message' => "Target user '{$identifier}' not found in Supabase Auth or Profiles."];
        }

        $userId = $user['id'];
        $username = $user['username'];
        $userEmail = $user['email'];

        // 1. Grant Subscription via MembershipService
        $grantRes = $membershipService->grantSubscription(
            $adminUser,
            $userId,
            $username,
            'copytrading',
            $durationDays,
            $notes,
            'Copy Trading Grant'
        );

        if (empty($grantRes['success'])) {
            return $grantRes;
        }

        // 2. If MT5 payload provided, save MT5 account credentials
        if (!empty($mt5Payload) && (!empty($mt5Payload['broker_name']) || !empty($mt5Payload['mt5_login']))) {
            $mt5Record = [
                'user_id'           => $userId,
                'full_name'         => $mt5Payload['full_name'] ?? $username,
                'email'             => $userEmail,
                'subscription_plan' => 'copytrading',
                'broker_name'       => trim($mt5Payload['broker_name'] ?? ''),
                'mt5_login'         => trim($mt5Payload['mt5_login'] ?? ''),
                'mt5_password'      => $mt5Payload['mt5_password'] ?? '',
                'mt5_server'        => trim($mt5Payload['mt5_server'] ?? ''),
                'notes'             => trim($mt5Payload['notes'] ?? $notes),
                'status'            => $mt5Payload['status'] ?? 'Active'
            ];
            self::saveCopyTrader($mt5Record);
        }

        self::logAudit($adminUser, $userId, 'GRANT_COPYTRADING', $_SERVER['REMOTE_ADDR'] ?? '', "Granted $durationDays days Copy Trading access to $username ($userEmail)");

        return [
            'success'         => true,
            'message'         => "Copy Trading access successfully granted and verified in Supabase for @{$username}!",
            'user'            => $user,
            'subscription_id' => $grantRes['subscription_id'] ?? null,
            'data'            => $grantRes['data'] ?? null
        ];
    }

    /**
     * Update Copy Trader MT5 Account Details & Status
     */
    public static function updateCopyTraderDetails(string $id, array $data, string $adminUser = 'Admin'): array {
        $now = date('c');

        // Fetch existing record
        $existing = null;
        if (function_exists('sb_admin_get')) {
            $res = sb_admin_get('copy_traders', ['id' => "eq.$id", 'limit' => 1]);
            $existing = $res['data'][0] ?? null;
        }
        if (!$existing && function_exists('getMarketPDO')) {
            try {
                $pdo = getMarketPDO();
                $stmt = $pdo->prepare("SELECT * FROM copy_traders WHERE id = ? OR user_id = ? LIMIT 1");
                $stmt->execute([$id, $id]);
                $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
            } catch (\Throwable $e) {}
        }

        $userId = $existing['user_id'] ?? ($data['user_id'] ?? $id);
        $recordId = $existing['id'] ?? $id;

        $update = [
            'id'          => $recordId,
            'user_id'     => $userId,
            'email'       => $data['email'] ?? ($existing['email'] ?? ''),
            'full_name'   => $data['full_name'] ?? ($existing['full_name'] ?? 'Trader'),
            'broker_name' => trim($data['broker_name'] ?? ($existing['broker_name'] ?? '')),
            'mt5_login'   => trim($data['mt5_login'] ?? ($existing['mt5_login'] ?? '')),
            'mt5_server'  => trim($data['mt5_server'] ?? ($existing['mt5_server'] ?? '')),
            'status'      => $data['status'] ?? ($existing['status'] ?? 'Active'),
            'notes'       => trim($data['notes'] ?? ($existing['notes'] ?? '')),
            'updated_at'  => $now
        ];

        if (!empty($data['mt5_password'])) {
            $update['mt5_password'] = self::encryptPassword($data['mt5_password']);
        } elseif (!empty($existing['mt5_password'])) {
            $update['mt5_password'] = $existing['mt5_password'];
        }

        if (function_exists('sb_admin_post')) {
            sb_admin_post('copy_traders', $update);
        }
        if (function_exists('getMarketPDO')) {
            try {
                $pdo = getMarketPDO();
                $stmt = $pdo->prepare("INSERT OR REPLACE INTO copy_traders (id, user_id, full_name, email, subscription_plan, payment_reference, broker_name, mt5_login, mt5_password, mt5_server, notes, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'copytrading', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $recordId,
                    $userId,
                    $update['full_name'],
                    $update['email'],
                    $existing['payment_reference'] ?? ('PAY_' . strtoupper(uniqid())),
                    $update['broker_name'],
                    $update['mt5_login'],
                    $update['mt5_password'] ?? '',
                    $update['mt5_server'],
                    $update['notes'],
                    $update['status'],
                    $existing['created_at'] ?? $now,
                    $now
                ]);
            } catch (\Throwable $e) {}
        }

        self::logAudit($adminUser, $recordId, 'UPDATE_DETAILS', $_SERVER['REMOTE_ADDR'] ?? '', "Updated broker: {$update['broker_name']}, MT5: {$update['mt5_login']}, Status: {$update['status']}");

        return [
            'success' => true,
            'message' => 'Copy Trader details and status updated successfully!',
            'data'    => $update
        ];
    }

    /**
     * Decrypt MT5 password for authorized administrator and record audit log
     */
    public static function revealPasswordForAdmin(string $copyTraderId, string $adminUser = 'Admin'): array {
        $record = null;
        if (function_exists('sb_admin_get')) {
            $res = sb_admin_get('copy_traders', ['id' => "eq.$copyTraderId", 'limit' => 1]);
            $record = $res['data'][0] ?? null;
            if (!$record) {
                $res2 = sb_admin_get('copy_traders', ['user_id' => "eq.$copyTraderId", 'limit' => 1]);
                $record = $res2['data'][0] ?? null;
            }
        }
        if (!$record && function_exists('getMarketPDO')) {
            try {
                $pdo = getMarketPDO();
                $stmt = $pdo->prepare("SELECT * FROM copy_traders WHERE id = ? OR user_id = ? LIMIT 1");
                $stmt->execute([$copyTraderId, $copyTraderId]);
                $record = $stmt->fetch(\PDO::FETCH_ASSOC);
            } catch (\Throwable $e) {}
        }

        if (!$record || empty($record['mt5_password'])) {
            return ['success' => false, 'message' => 'No stored MT5 password found for this account.'];
        }

        $decrypted = self::decryptPassword($record['mt5_password']);

        self::logAudit($adminUser, $record['id'] ?: $copyTraderId, 'REVEAL_PASSWORD', $_SERVER['REMOTE_ADDR'] ?? '', "Admin {$adminUser} viewed/copied MT5 password for login {$record['mt5_login']}");

        return [
            'success'   => true,
            'id'        => $record['id'] ?? $copyTraderId,
            'user_id'   => $record['user_id'] ?? $copyTraderId,
            'mt5_login' => $record['mt5_login'],
            'password'  => $decrypted
        ];
    }

    /**
     * Audit Log Entry for Admin Actions
     */
    public static function logAudit(string $adminUser, string $copyTraderId, string $action, string $ipAddress = '', string $details = ''): bool {
        $now = date('c');
        $log = [
            'id'             => 'ctlog_' . sprintf('%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)),
            'admin_user'     => $adminUser,
            'copy_trader_id' => $copyTraderId,
            'action'         => $action,
            'ip_address'     => $ipAddress ?: ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'),
            'details'        => $details,
            'created_at'     => $now
        ];

        if (function_exists('sb_admin_post')) {
            sb_admin_post('copy_trader_audit_logs', $log);
        } else if (function_exists('sqlite_admin_post')) {
            sqlite_admin_post('copy_trader_audit_logs', $log);
        }
        return true;
    }
}
