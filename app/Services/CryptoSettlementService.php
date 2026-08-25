<?php

namespace App\Services;

require_once __DIR__ . '/KotaniPayService.php';
require_once __DIR__ . '/KotaniCryptoCapabilityService.php';
require_once __DIR__ . '/KoraCryptoCapabilityService.php';

/**
 * Class CryptoSettlementService
 *
 * ── ADAPTER BOUNDARY BETWEEN FIAT PAYMENT AND CRYPTO DELIVERY ────────
 *
 * The existing Kora integration (App\Services\KoraPaymentService) only
 * ever processes FIAT payment (M-Pesa / card) — see
 * KoraCryptoCapabilityService for why Kora is not, and has never been,
 * used for the crypto side of a BUY/SELL. That gap is what this class
 * exists to fill.
 *
 * As of this revision, Kotani Pay (App\Services\KotaniPayService) is
 * wired in as the real crypto settlement provider, matching the flow:
 *
 *   BUY:  KES -> Kora Pay-in (fiat collected) -> payment confirmed
 *         -> Kotani Pay onramp delivers crypto to the customer's wallet.
 *   SELL: customer sends crypto to a Kotani-issued deposit address
 *         -> deposit confirmed -> Kotani Pay offramp disburses KES to
 *         the customer's mobile money/bank.
 *
 * Kotani is only actually used once KOTANI_API_KEY is configured
 * (KotaniCryptoCapabilityService fails closed otherwise) — until then,
 * this class behaves exactly as before: BUY stops at
 * SETTLEMENT_PENDING after fiat payment, and SELL stops at
 * SETTLEMENT_PENDING from the start. Nothing here ever fakes delivery.
 *
 * If Kotani is ever swapped out or Kora eventually documents a crypto
 * capability of its own, this is still the ONLY file that should need
 * to change — the API layer and frontend already treat "pending" vs
 * "completed" correctly.
 */
class CryptoSettlementService
{
    private KotaniCryptoCapabilityService $kotaniCapability;
    private KoraCryptoCapabilityService $koraCapability;
    private KotaniPayService $kotani;

    public function __construct()
    {
        $this->kotaniCapability = new KotaniCryptoCapabilityService();
        $this->koraCapability   = new KoraCryptoCapabilityService();
        $this->kotani           = new KotaniPayService();
    }

    public function isConfigured(): bool
    {
        return $this->kotaniCapability->supports('crypto_withdrawal')
            || $this->koraCapability->supports('crypto_withdrawal');
    }

    /**
     * Called after fiat payment for a BUY is confirmed. Delivers
     * `crypto_amount` of `crypto_symbol` to the transaction's
     * `destination_address` via Kotani Pay's onramp, when configured.
     */
    public function settleBuy(array $transaction): array
    {
        if ($this->kotaniCapability->supports('crypto_withdrawal')) {
            return $this->kotani->deliverCrypto($transaction);
        }

        return [
            'ok'      => false,
            'status'  => 'SETTLEMENT_PENDING',
            'message' => 'Payment received. Crypto delivery is pending settlement configuration ('
                . $this->kotaniCapability->unavailableReason('crypto_withdrawal') . ').',
        ];
    }

    /**
     * Called when a user wants to SELL crypto for fiat. Returns a
     * deposit address/instructions for the user to send crypto to, via
     * Kotani Pay's offramp, when configured.
     */
    public function initiateSell(array $transaction): array
    {
        if ($this->kotaniCapability->supports('crypto_deposit')) {
            return $this->kotani->requestCryptoDeposit($transaction);
        }

        return [
            'ok'      => false,
            'status'  => 'SETTLEMENT_PENDING',
            'message' => 'Crypto sell is not yet available for automatic payout ('
                . $this->kotaniCapability->unavailableReason('crypto_deposit') . ') — our team will contact you to complete this manually.',
        ];
    }

    /**
     * Called once a SELL's crypto deposit is confirmed (typically from
     * api/kotani-webhook.php) — disburses the fiat equivalent.
     */
    public function completeSell(array $transaction): array
    {
        if ($this->kotaniCapability->supports('crypto_deposit')) {
            return $this->kotani->disburseFiat($transaction);
        }

        return [
            'ok'      => false,
            'status'  => 'SETTLEMENT_PENDING',
            'message' => 'Settlement pending',
        ];
    }
}
