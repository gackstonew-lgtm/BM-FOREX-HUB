<?php
/**
 * TradingViewSyncService
 *
 * Abstraction layer for TradingView username management.
 * Phase 1: Manual admin workflow.
 * Phase 2: Can be extended with TradingView API when available.
 *
 * @package App\Services
 */
namespace App\Services;

class TradingViewSyncService
{
    private string $supabaseUrl;
    private string $serviceKey;

    public function __construct(string $supabaseUrl = '', string $serviceKey = '')
    {
        $this->supabaseUrl = $supabaseUrl ?: (defined('SUPABASE_URL')     ? SUPABASE_URL     : 'https://iwoytmcxmbhmmbrbpzvf.supabase.co');
        $this->serviceKey  = $serviceKey  ?: (defined('SUPABASE_SERVICE') ? SUPABASE_SERVICE : '');
    }

    /**
     * Validate a TradingView username format.
     * TradingView usernames: 3-30 chars, alphanumeric + underscore.
     */
    public function validateUsername(string $username): bool
    {
        $username = trim($username);
        if (strlen($username) < 2 || strlen($username) > 64) return false;
        return (bool) preg_match('/^[a-zA-Z0-9_\.\-]{2,64}$/', $username);
    }

    /**
     * Update TradingView username for a subscription.
     * Also marks verification as pending (admin must verify).
     */
    public function updateUsername(string $subscriptionId, string $username): array
    {
        $username = trim($username);

        if (!$this->validateUsername($username)) {
            return ['ok' => false, 'error' => 'Invalid TradingView username format.'];
        }

        $data = [
            'tradingview_username' => $username,
            'tv_username_verified' => false,
            'last_sync'            => date('c'),
            'updated_at'           => date('c'),
        ];

        // Update in Supabase
        $this->supabasePatch("indicator_subscriptions", "id=eq.$subscriptionId", $data);

        // Update in SQLite fallback
        $this->updateUsernameSQLite($subscriptionId, $username);

        return ['ok' => true, 'username' => $username];
    }

    /**
     * Mark a TradingView username as verified (admin action).
     * In Phase 1, admin manually confirms access was granted in TradingView.
     */
    public function markVerified(string $subscriptionId, bool $verified = true): bool
    {
        $data = [
            'tv_username_verified' => $verified,
            'last_sync'            => date('c'),
            'updated_at'           => date('c'),
        ];

        $this->supabasePatch("indicator_subscriptions", "id=eq.$subscriptionId", $data);
        $this->markVerifiedSQLite($subscriptionId, $verified);
        return true;
    }

    /**
     * Get all subscribers pending TradingView username verification.
     */
    public function getPendingVerifications(): array
    {
        $url = $this->supabaseUrl
             . '/rest/v1/indicator_subscriptions'
             . '?status=eq.active'
             . '&tv_username_verified=eq.false'
             . '&tradingview_username=not.is.null'
             . '&order=created_at.asc'
             . '&limit=100';

        $resp = $this->supabaseGet($url);
        return is_array($resp) ? $resp : [];
    }

    /**
     * Phase 2 hook: Override this method to implement automated
     * TradingView Pine Script access sharing when TradingView
     * provides a public API for this.
     *
     * @param  string $tvUsername  TradingView username to grant access
     * @param  string $scriptId    The Pine Script ID to share
     * @return array { ok: bool, message: string }
     */
    public function grantTradingViewAccess(string $tvUsername, string $scriptId = ''): array
    {
        // Phase 2 implementation point.
        // TradingView does not currently provide a public API for
        // Pine Script sharing automation. Access must be granted
        // manually inside TradingView settings.
        //
        // To implement Phase 2:
        // 1. Add TradingView session cookie / API credentials
        // 2. POST to TradingView's internal sharing endpoint
        // 3. Return { ok: true } on success
        //
        // For now, return a placeholder indicating manual action needed.
        return [
            'ok'      => false,
            'message' => 'Manual TradingView access grant required. See admin panel.',
            'phase'   => 1,
        ];
    }

    // ── Supabase Helpers ──────────────────────────────────────────────

    private function supabaseGet(string $url): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'apikey: ' . $this->serviceKey,
                'Authorization: Bearer ' . $this->serviceKey,
            ],
            CURLOPT_TIMEOUT => 8,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        $data = $resp ? json_decode($resp, true) : null;
        return is_array($data) ? $data : null;
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
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT    => 8,
        ]);
        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($code >= 200 && $code < 300);
    }

    // ── SQLite Fallback Helpers ───────────────────────────────────────

    private function updateUsernameSQLite(string $subscriptionId, string $username): void
    {
        try {
            if (!function_exists('getMarketPDO')) return;
            $pdo  = getMarketPDO();
            $stmt = $pdo->prepare("
                UPDATE indicator_subscriptions
                SET tradingview_username = :u, tv_username_verified = 0,
                    last_sync = datetime('now'), updated_at = datetime('now')
                WHERE id = :id
            ");
            $stmt->execute([':u' => $username, ':id' => $subscriptionId]);
        } catch (\Throwable $e) {
            @error_log('TradingViewSyncService SQLite error: ' . $e->getMessage());
        }
    }

    private function markVerifiedSQLite(string $subscriptionId, bool $verified): void
    {
        try {
            if (!function_exists('getMarketPDO')) return;
            $pdo  = getMarketPDO();
            $stmt = $pdo->prepare("
                UPDATE indicator_subscriptions
                SET tv_username_verified = :v, last_sync = datetime('now'), updated_at = datetime('now')
                WHERE id = :id
            ");
            $stmt->execute([':v' => $verified ? 1 : 0, ':id' => $subscriptionId]);
        } catch (\Throwable $e) {
            @error_log('TradingViewSyncService markVerifiedSQLite error: ' . $e->getMessage());
        }
    }
}
