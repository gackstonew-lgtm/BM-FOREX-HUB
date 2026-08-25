<?php

namespace App\Services\Contracts;

/**
 * Interface CryptoSettlementProviderInterface
 *
 * Contract for any provider that can actually move crypto in/out on
 * BM Forex Hub's behalf — as opposed to KoraPaymentService /
 * PaymentGatewayInterface, which only ever moves FIAT (M-Pesa/card).
 *
 * A settlement provider must be able to:
 *   - BUY side:  accept confirmation that fiat was received, then send
 *     the purchased crypto to the customer's own wallet address.
 *   - SELL side: hand the customer a deposit address (or accept an
 *     existing one) to send crypto to, detect/confirm the deposit, and
 *     disburse the fiat equivalent to the customer's mobile money/bank.
 *
 * Implementations must never claim success without the provider
 * genuinely confirming it — see CryptoSettlementService for how this
 * is enforced end-to-end.
 */
interface CryptoSettlementProviderInterface
{
    /** Whether this provider is actually configured (credentials present). */
    public function isConfigured(): bool;

    /**
     * BUY: deliver `crypto_amount` of `crypto_symbol` to
     * `destination_address` (on `network`, if applicable) after fiat
     * payment for this transaction has already been confirmed.
     *
     * @param array $transaction The crypto_transactions row (already PAYMENT_CONFIRMED).
     * @return array [ 'ok' => bool, 'status' => string, 'message' => string,
     *                 'settlement_reference' => string|null, 'provider_tx_id' => string|null ]
     */
    public function deliverCrypto(array $transaction): array;

    /**
     * SELL: request/return a deposit address the customer should send
     * `crypto_symbol` (on `network`) to, and register the intended
     * fiat payout (mobile money/bank) once the deposit is confirmed.
     *
     * @param array $transaction The crypto_transactions row (SETTLEMENT_PENDING).
     * @return array [ 'ok' => bool, 'status' => string, 'message' => string,
     *                 'deposit_address' => string|null, 'network' => string|null,
     *                 'settlement_reference' => string|null ]
     */
    public function requestCryptoDeposit(array $transaction): array;

    /**
     * Called once an inbound deposit for a SELL is confirmed (e.g. by a
     * webhook) — disburses fiat to the customer via mobile money/bank.
     *
     * @param array $transaction The crypto_transactions row.
     * @return array [ 'ok' => bool, 'status' => string, 'message' => string,
     *                 'settlement_reference' => string|null ]
     */
    public function disburseFiat(array $transaction): array;

    /** Query the provider directly for a transaction's current status. */
    public function getTransactionStatus(string $providerReference): array;

    /** Verify an inbound webhook really came from this provider. */
    public function verifyWebhookSignature(string $signatureHeader, string $rawBody): bool;
}
