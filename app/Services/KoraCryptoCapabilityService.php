<?php

namespace App\Services;

/**
 * Class KoraCryptoCapabilityService
 *
 * ── SINGLE SOURCE OF TRUTH FOR "CAN WE ACTUALLY DO THIS?" ────────────
 *
 * Every other crypto class (CryptoSettlementService, the wallet layer,
 * the buy/sell API endpoints) MUST ask this service before claiming a
 * crypto operation is available. Nothing else in the codebase is
 * allowed to assume a Kora crypto capability exists.
 *
 * WHY EVERYTHING RETURNS false RIGHT NOW:
 * As of this implementation, Kora's publicly documented API
 * (https://developers.korapay.com) exposes Pay-ins, Payouts, Balance,
 * and Identity/KYC — there is no documented endpoint for creating a
 * customer stablecoin wallet, receiving crypto deposits, or sending
 * crypto payouts. The endpoint referenced in earlier planning docs
 * (`POST /merchant/api/v1/crypto/wallets`) is NOT part of Kora's
 * published API surface. Calling it would mean guessing at a
 * contract Kora has not documented — exactly what this project must
 * never do (see CryptoSettlementService's header comment).
 *
 * This class therefore fails closed: every capability is false until
 * one of two things happens, at which point ONLY this file should
 * need to change:
 *
 *   1. Kora publishes a documented crypto/stablecoin API and BM Forex
 *      Hub's merchant account is enabled for it — update the checks
 *      below to call Kora's real capability/status endpoint (or, if
 *      Kora doesn't expose a self-check, hardcode the confirmed
 *      capability here with a comment linking the docs page used).
 *
 *   2. BM Forex Hub integrates a separate licensed crypto liquidity/
 *      custody provider for wallets, deposits, and payouts — implement
 *      that provider's real capability check here instead.
 *
 * Downstream code reads capability flags, not the reason for them —
 * so flipping a flag here is enough to light up the corresponding
 * feature everywhere else, without touching the API layer.
 */
class KoraCryptoCapabilityService
{
    /** @var array<string,bool>|null cached for the lifetime of the request */
    private static ?array $cache = null;

    public function capabilities(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        // CRYPTO_SETTLEMENT_ENABLED in .env is deliberately NOT read here.
        // It exists only as a future kill switch for real capabilities —
        // never as a way to fake one on. Every value below is a hardcoded
        // `false` until the code itself is updated (see class header).

        self::$cache = [
            // Can we create/retrieve a per-customer stablecoin wallet address?
            'wallet_creation'    => false, // no documented Kora endpoint
            'wallet_retrieval'   => false, // no documented Kora endpoint
            // Can we detect/receive an inbound crypto deposit (needed for SELL)?
            'crypto_deposit'     => false, // no documented Kora endpoint
            // Can we send/withdraw crypto to a customer (needed for BUY delivery)?
            'crypto_withdrawal'  => false, // no documented Kora endpoint
            // Can we settle a confirmed fiat BUY into delivered crypto end-to-end?
            'crypto_settlement'  => false, // no documented Kora endpoint; flip only per the header instructions
            // Can we query the status of a crypto-side transaction from the provider?
            'crypto_tx_status'   => false, // no documented Kora endpoint
        ];

        return self::$cache;
    }

    public function supports(string $capability): bool
    {
        return (bool) ($this->capabilities()[$capability] ?? false);
    }

    /**
     * Human-readable reason, safe to surface to admins/users. Never
     * implies a timeline or promises a fix — just states the fact.
     */
    public function unavailableReason(string $capability): string
    {
        $labels = [
            'wallet_creation'   => 'Customer crypto wallet creation',
            'wallet_retrieval'  => 'Customer crypto wallet lookup',
            'crypto_deposit'    => 'Crypto deposit detection',
            'crypto_withdrawal' => 'Crypto withdrawal/send',
            'crypto_settlement' => 'Crypto settlement',
            'crypto_tx_status'  => 'Crypto transaction status lookup',
        ];
        $label = $labels[$capability] ?? $capability;
        return "$label is not enabled for this merchant account — Kora does not currently document this capability.";
    }

    /**
     * Convenience for API/admin responses: the full capability map plus
     * a plain-English summary line.
     */
    public function report(): array
    {
        $caps = $this->capabilities();
        return [
            'capabilities' => $caps,
            'summary'      => in_array(true, $caps, true)
                ? 'Some crypto settlement capabilities are enabled.'
                : 'Crypto settlement is not enabled for this merchant account — quotes, orders, and payment collection work, but crypto delivery/receipt is pending a settlement provider.',
        ];
    }
}
