<?php

namespace App\Services;

require_once __DIR__ . '/KotaniPayService.php';

/**
 * Class KotaniCryptoCapabilityService
 *
 * Same purpose as KoraCryptoCapabilityService ("single source of truth
 * for what's actually possible right now") but for Kotani Pay, the
 * settlement provider now wired into CryptoSettlementService.
 *
 * Unlike Kora — whose public API simply does not document a crypto
 * capability at all, so every flag there is hardcoded false — Kotani
 * does document onramp (fiat->crypto delivery) and offramp
 * (crypto->fiat payout). So capability here is genuinely conditional:
 * true only once KOTANI_API_KEY is actually configured, false
 * otherwise (fails closed, never assumes credentials exist).
 */
class KotaniCryptoCapabilityService
{
    private static ?array $cache = null;
    private KotaniPayService $kotani;

    public function __construct()
    {
        $this->kotani = new KotaniPayService();
    }

    public function capabilities(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $configured = $this->kotani->isConfigured();

        self::$cache = [
            // Onramp: deliver crypto to a customer wallet after fiat is confirmed (BUY).
            'crypto_withdrawal' => $configured, // documented: Kotani Onramp
            // Offramp: accept a crypto deposit and disburse fiat (SELL).
            'crypto_deposit'    => $configured, // documented: Kotani Offramp
            'crypto_settlement' => $configured,
            'crypto_tx_status'  => $configured,
            // Kotani does not expose a persistent "customer wallet" object
            // the way this project originally speculated Kora might —
            // each onramp/offramp call is its own request, not a
            // reusable wallet. Leave these false until/unless confirmed
            // otherwise for your integrator account.
            'wallet_creation'   => false,
            'wallet_retrieval'  => false,
        ];

        return self::$cache;
    }

    public function supports(string $capability): bool
    {
        return (bool) ($this->capabilities()[$capability] ?? false);
    }

    public function unavailableReason(string $capability): string
    {
        $labels = [
            'wallet_creation'   => 'Customer crypto wallet creation',
            'wallet_retrieval'  => 'Customer crypto wallet lookup',
            'crypto_deposit'    => 'Crypto deposit / offramp',
            'crypto_withdrawal' => 'Crypto withdrawal / onramp',
            'crypto_settlement' => 'Crypto settlement',
            'crypto_tx_status'  => 'Crypto transaction status lookup',
        ];
        $label = $labels[$capability] ?? $capability;
        return "$label is not available — KOTANI_API_KEY is not configured for this merchant account.";
    }

    public function report(): array
    {
        $caps = $this->capabilities();
        return [
            'provider' => 'kotani',
            'capabilities' => $caps,
            'summary' => in_array(true, $caps, true)
                ? 'Kotani Pay is configured — crypto onramp (BUY delivery) and offramp (SELL payout) are live.'
                : 'Kotani Pay is not configured (KOTANI_API_KEY missing) — crypto delivery/receipt is pending a settlement provider.',
        ];
    }
}
