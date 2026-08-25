<?php
/**
 * IndicatorAccessService
 *
 * Single source of truth for indicator subscription access control.
 * Checks Supabase (primary) and SQLite (fallback) for active
 * indicator subscriptions.
 *
 * @package App\Services
 */
namespace App\Services;

class IndicatorAccessService
{
    // Permanent admin emails — always have full access
    private const PERMANENT_EMAILS = [
        'bonfacewana3072@gmail.com',
        'langatgift6@gmail.com',
        'gackstoneb@gmail.com',
    ];

    private string $supabaseUrl;
    private string $serviceKey;
    private string $anonKey;

    public function __construct(
        string $supabaseUrl  = '',
        string $serviceKey   = '',
        string $anonKey      = ''
    ) {
        $this->supabaseUrl = $supabaseUrl  ?: (defined('SUPABASE_URL')     ? SUPABASE_URL     : 'https://iwoytmcxmbhmmbrbpzvf.supabase.co');
        $this->serviceKey  = $serviceKey   ?: (defined('SUPABASE_SERVICE') ? SUPABASE_SERVICE : '');
        $this->anonKey     = $anonKey      ?: (defined('SUPABASE_ANON')    ? SUPABASE_ANON    : '');
    }

    // ── Public API ────────────────────────────────────────────────────

    /**
     * Check indicator access for a user.
     *
     * @param  string $userId    Supabase user UUID
     * @param  string $email     User's email address
     * @return array {
     *     has_access: bool,
     *     plan_key: string|null,
     *     plan_name: string|null,
     *     signals_access: bool,
     *     ai_access: bool,
     *     premium_dashboard: bool,
     *     expires_at: string|null,
     *     days_remaining: int,
     *     status: string,
     *     tradingview_username: string|null,
     *     subscription_id: string|null,
     * }
     */
    public function checkAccess(string $userId, string $email): array
    {
        $defaultDenied = [
            'has_access'          => false,
            'plan_key'            => null,
            'plan_name'           => null,
            'signals_access'      => false,
            'ai_access'           => false,
            'premium_dashboard'   => false,
            'expires_at'          => null,
            'days_remaining'      => 0,
            'status'              => 'none',
            'tradingview_username' => null,
            'subscription_id'     => null,
        ];

        // Permanent admin access
        if ($this->isPermanentAdmin($email)) {
            return array_merge($defaultDenied, [
                'has_access'        => true,
                'plan_key'          => 'indicator_vip',
                'plan_name'         => 'VIP (Admin)',
                'signals_access'    => true,
                'ai_access'         => true,
                'premium_dashboard' => true,
                'expires_at'        => '9999-12-31T23:59:59+00:00',
                'days_remaining'    => 36500,
                'status'            => 'active',
            ]);
        }

        // Try Supabase first
        $sub = $this->fetchActiveSubscription($userId);

        // Fallback to SQLite
        if (empty($sub)) {
            $sub = $this->fetchActiveSubscriptionSQLite($userId, $email);
        }

        if (empty($sub)) {
            return $defaultDenied;
        }

        $expiresAt = $sub['expires_at'] ?? null;
        $daysRemaining = 0;
        if ($expiresAt) {
            $daysRemaining = max(0, (int) ceil((strtotime($expiresAt) - time()) / 86400));
        }

        return [
            'has_access'           => true,
            'plan_key'             => $sub['plan_key']           ?? null,
            'plan_name'            => $sub['plan_name']          ?? null,
            'signals_access'       => (bool)($sub['signals_access']     ?? false),
            'ai_access'            => (bool)($sub['ai_access']          ?? false),
            'premium_dashboard'    => (bool)($sub['premium_dashboard']  ?? false),
            'expires_at'           => $expiresAt,
            'days_remaining'       => $daysRemaining,
            'status'               => $sub['status']             ?? 'active',
            'tradingview_username' => $sub['tradingview_username'] ?? null,
            'subscription_id'      => $sub['id']                 ?? null,
        ];
    }

    /**
     * Activate a new indicator subscription after successful payment.
     *
     * @param  array $params {
     *     user_id, user_email, user_name, plan_key, plan_name,
     *     payment_reference, amount_paid_usd, amount_paid_kes,
     *     tradingview_username, duration_days,
     *     signals_access, ai_access, premium_dashboard
     * }
     * @return array { ok: bool, id: string|null, error: string|null }
     */
    public function activateSubscription(array $params): array
    {
        $now        = date('c');
        $durationDays = (int)($params['duration_days'] ?? 30);
        $expiresAt  = date('c', strtotime("+{$durationDays} days"));
        $id         = $this->generateUuid();

        $data = [
            'id'                   => $id,
            'user_id'              => $params['user_id']              ?? null,
            'user_email'           => $params['user_email']           ?? null,
            'user_name'            => $params['user_name']            ?? null,
            'plan_key'             => $params['plan_key']             ?? 'indicator_silver',
            'plan_name'            => $params['plan_name']            ?? 'Silver',
            'status'               => 'active',
            'indicator_access'     => true,
            'signals_access'       => (bool)($params['signals_access']    ?? false),
            'ai_access'            => (bool)($params['ai_access']          ?? false),
            'premium_dashboard'    => (bool)($params['premium_dashboard']  ?? false),
            'tradingview_username' => $params['tradingview_username'] ?? null,
            'tv_username_verified' => false,
            'payment_reference'    => $params['payment_reference']   ?? null,
            'payment_provider'     => 'korapay',
            'amount_paid_usd'      => (float)($params['amount_paid_usd'] ?? 0),
            'amount_paid_kes'      => (float)($params['amount_paid_kes'] ?? 0),
            'started_at'           => $now,
            'expires_at'           => $expiresAt,
            'created_at'           => $now,
            'updated_at'           => $now,
        ];

        // Write to Supabase
        $res = $this->supabasePost('indicator_subscriptions', $data);

        // Always write to SQLite as fallback
        $this->activateSubscriptionSQLite($data);

        return [
            'ok'    => true,
            'id'    => $id,
            'error' => null,
        ];
    }

    /**
     * Revoke access for a subscription (admin action).
     */
    public function revokeAccess(string $subscriptionId, string $adminEmail = ''): bool
    {
        $data = [
            'status'            => 'cancelled',
            'indicator_access'  => false,
            'signals_access'    => false,
            'ai_access'         => false,
            'premium_dashboard' => false,
            'updated_at'        => date('c'),
        ];

        $this->supabasePatch("indicator_subscriptions", "id=eq.$subscriptionId", $data);
        $this->revokeAccessSQLite($subscriptionId);

        $this->logAction($subscriptionId, null, null, 'access_revoked', null, ['admin' => $adminEmail]);
        return true;
    }

    /**
     * Grant/restore access for a subscription (admin action).
     */
    public function grantAccess(string $subscriptionId, string $adminEmail = ''): bool
    {
        // Fetch subscription to get plan details
        $sub = $this->fetchSubscriptionById($subscriptionId);

        $data = [
            'status'            => 'active',
            'indicator_access'  => true,
            'updated_at'        => date('c'),
        ];
        if (!empty($sub)) {
            $planKey = $sub['plan_key'] ?? 'indicator_silver';
            $data['signals_access']    = in_array($planKey, ['indicator_gold', 'indicator_vip']);
            $data['ai_access']         = ($planKey === 'indicator_vip');
            $data['premium_dashboard'] = ($planKey === 'indicator_vip');
        }

        $this->supabasePatch("indicator_subscriptions", "id=eq.$subscriptionId", $data);
        $this->grantAccessSQLite($subscriptionId, $data);

        $this->logAction($subscriptionId, null, null, 'access_granted', null, ['admin' => $adminEmail]);
        return true;
    }

    /**
     * Log an action to the indicator_access_log table.
     */
    public function logAction(
        ?string $subscriptionId,
        ?string $userId,
        ?string $userEmail,
        string  $action,
        ?string $planKey,
        array   $details = []
    ): void {
        $data = [
            'id'         => $this->generateUuid(),
            'user_id'    => $userId,
            'user_email' => $userEmail,
            'action'     => $action,
            'plan_key'   => $planKey,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'details'    => array_merge($details, $subscriptionId ? ['subscription_id' => $subscriptionId] : []),
            'created_at' => date('c'),
        ];
        $this->supabasePost('indicator_access_log', $data);
        $this->logActionSQLite($data);
    }

    /**
     * Clean up expired subscriptions.
     */
    public function expireOldSubscriptions(): int
    {
        // Supabase
        $this->supabasePatch(
            'indicator_subscriptions',
            'status=eq.active&expires_at=lt.' . urlencode(date('c')),
            [
                'status'            => 'expired',
                'indicator_access'  => false,
                'signals_access'    => false,
                'ai_access'         => false,
                'premium_dashboard' => false,
                'updated_at'        => date('c'),
            ]
        );

        // SQLite
        return $this->expireOldSubscriptionsSQLite();
    }

    // ── Supabase Helpers ──────────────────────────────────────────────

    private function fetchActiveSubscription(string $userId): ?array
    {
        $now = urlencode(date('c'));
        $url = $this->supabaseUrl . "/rest/v1/indicator_subscriptions"
             . "?user_id=eq.$userId"
             . "&status=eq.active"
             . "&expires_at=gt.$now"
             . "&order=expires_at.desc&limit=1";

        $resp = $this->supabaseGet($url);
        return isset($resp[0]) ? $resp[0] : null;
    }

    private function fetchSubscriptionById(string $id): ?array
    {
        $url = $this->supabaseUrl . "/rest/v1/indicator_subscriptions?id=eq.$id&limit=1";
        $resp = $this->supabaseGet($url);
        return isset($resp[0]) ? $resp[0] : null;
    }

    private function supabaseGet(string $url): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'apikey: ' . $this->serviceKey,
                'Authorization: Bearer ' . $this->serviceKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 8,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        if (!$resp) return null;
        $data = json_decode($resp, true);
        return is_array($data) ? $data : null;
    }

    private function supabasePost(string $table, array $data): bool
    {
        $url = $this->supabaseUrl . '/rest/v1/' . $table;
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_HTTPHEADER     => [
                'apikey: ' . $this->serviceKey,
                'Authorization: Bearer ' . $this->serviceKey,
                'Content-Type: application/json',
                'Prefer: return=minimal',
            ],
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_TIMEOUT        => 8,
        ]);
        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($code >= 200 && $code < 300);
    }

    private function supabasePatch(string $table, string $query, array $data): bool
    {
        $url = $this->supabaseUrl . '/rest/v1/' . $table . '?' . $query;
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'PATCH',
            CURLOPT_HTTPHEADER     => [
                'apikey: ' . $this->serviceKey,
                'Authorization: Bearer ' . $this->serviceKey,
                'Content-Type: application/json',
                'Prefer: return=minimal',
            ],
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_TIMEOUT        => 8,
        ]);
        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($code >= 200 && $code < 300);
    }

    // ── SQLite Fallback Helpers ───────────────────────────────────────

    private function fetchActiveSubscriptionSQLite(string $userId, string $email): ?array
    {
        try {
            if (!function_exists('getMarketPDO')) return null;
            $pdo  = getMarketPDO();
            $now  = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("
                SELECT * FROM indicator_subscriptions
                WHERE (user_id = :uid OR LOWER(user_email) = :em)
                  AND status = 'active'
                  AND expires_at > :now
                ORDER BY expires_at DESC LIMIT 1
            ");
            $stmt->execute([':uid' => $userId, ':em' => strtolower($email), ':now' => $now]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function activateSubscriptionSQLite(array $data): void
    {
        try {
            if (!function_exists('getMarketPDO')) return;
            $pdo  = getMarketPDO();
            $stmt = $pdo->prepare("
                INSERT OR IGNORE INTO indicator_subscriptions
                    (id, user_id, user_email, user_name, plan_key, plan_name, status,
                     indicator_access, signals_access, ai_access, premium_dashboard,
                     tradingview_username, tv_username_verified, payment_reference,
                     payment_provider, amount_paid_usd, amount_paid_kes,
                     started_at, expires_at, created_at, updated_at)
                VALUES
                    (:id, :user_id, :user_email, :user_name, :plan_key, :plan_name, :status,
                     :indicator_access, :signals_access, :ai_access, :premium_dashboard,
                     :tradingview_username, :tv_username_verified, :payment_reference,
                     :payment_provider, :amount_paid_usd, :amount_paid_kes,
                     :started_at, :expires_at, :created_at, :updated_at)
            ");
            $stmt->execute([
                ':id'                   => $data['id'],
                ':user_id'              => $data['user_id'],
                ':user_email'           => $data['user_email'],
                ':user_name'            => $data['user_name'],
                ':plan_key'             => $data['plan_key'],
                ':plan_name'            => $data['plan_name'],
                ':status'               => $data['status'],
                ':indicator_access'     => $data['indicator_access']    ? 1 : 0,
                ':signals_access'       => $data['signals_access']      ? 1 : 0,
                ':ai_access'            => $data['ai_access']            ? 1 : 0,
                ':premium_dashboard'    => $data['premium_dashboard']   ? 1 : 0,
                ':tradingview_username' => $data['tradingview_username'],
                ':tv_username_verified' => 0,
                ':payment_reference'    => $data['payment_reference'],
                ':payment_provider'     => 'korapay',
                ':amount_paid_usd'      => $data['amount_paid_usd'],
                ':amount_paid_kes'      => $data['amount_paid_kes'],
                ':started_at'           => $data['started_at'],
                ':expires_at'           => $data['expires_at'],
                ':created_at'           => $data['created_at'],
                ':updated_at'           => $data['updated_at'],
            ]);
        } catch (\Throwable $e) {
            @error_log('BMFH IndicatorAccessService SQLite write error: ' . $e->getMessage());
        }
    }

    private function revokeAccessSQLite(string $subscriptionId): void
    {
        try {
            if (!function_exists('getMarketPDO')) return;
            $pdo  = getMarketPDO();
            $stmt = $pdo->prepare("
                UPDATE indicator_subscriptions
                SET status = 'cancelled', indicator_access = 0, signals_access = 0,
                    ai_access = 0, premium_dashboard = 0, updated_at = datetime('now')
                WHERE id = :id
            ");
            $stmt->execute([':id' => $subscriptionId]);
        } catch (\Throwable $e) {
            @error_log('BMFH IndicatorAccessService revokeAccessSQLite error: ' . $e->getMessage());
        }
    }

    private function grantAccessSQLite(string $subscriptionId, array $data): void
    {
        try {
            if (!function_exists('getMarketPDO')) return;
            $pdo  = getMarketPDO();
            $stmt = $pdo->prepare("
                UPDATE indicator_subscriptions
                SET status = 'active', indicator_access = 1,
                    signals_access = :sa, ai_access = :aa, premium_dashboard = :pd,
                    updated_at = datetime('now')
                WHERE id = :id
            ");
            $stmt->execute([
                ':sa'  => $data['signals_access']    ? 1 : 0,
                ':aa'  => $data['ai_access']           ? 1 : 0,
                ':pd'  => $data['premium_dashboard']  ? 1 : 0,
                ':id'  => $subscriptionId,
            ]);
        } catch (\Throwable $e) {
            @error_log('BMFH IndicatorAccessService grantAccessSQLite error: ' . $e->getMessage());
        }
    }

    private function expireOldSubscriptionsSQLite(): int
    {
        try {
            if (!function_exists('getMarketPDO')) return 0;
            $pdo  = getMarketPDO();
            $stmt = $pdo->prepare("
                UPDATE indicator_subscriptions
                SET status = 'expired', indicator_access = 0, signals_access = 0,
                    ai_access = 0, premium_dashboard = 0, updated_at = datetime('now')
                WHERE status = 'active' AND expires_at < datetime('now')
            ");
            $stmt->execute();
            return (int) $stmt->rowCount();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function logActionSQLite(array $data): void
    {
        try {
            if (!function_exists('getMarketPDO')) return;
            $pdo  = getMarketPDO();
            $stmt = $pdo->prepare("
                INSERT INTO indicator_access_log
                    (id, user_id, user_email, action, plan_key, ip_address, user_agent, details, created_at)
                VALUES (:id, :uid, :em, :action, :pk, :ip, :ua, :det, :ca)
            ");
            $stmt->execute([
                ':id'     => $data['id'],
                ':uid'    => $data['user_id'],
                ':em'     => $data['user_email'],
                ':action' => $data['action'],
                ':pk'     => $data['plan_key'],
                ':ip'     => $data['ip_address'],
                ':ua'     => $data['user_agent'],
                ':det'    => json_encode($data['details'] ?? []),
                ':ca'     => $data['created_at'],
            ]);
        } catch (\Throwable $e) {
            @error_log('BMFH IndicatorAccessService logActionSQLite error: ' . $e->getMessage());
        }
    }

    // ── Utility ───────────────────────────────────────────────────────

    private function isPermanentAdmin(string $email): bool
    {
        return in_array(strtolower(trim($email)), array_map('strtolower', self::PERMANENT_EMAILS), true);
    }

    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
