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
