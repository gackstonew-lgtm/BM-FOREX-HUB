<?php

namespace App\Services\Contracts;

/**
 * Interface PaymentGatewayInterface
 * Defines standard contract for payment gateway operations.
 */
interface PaymentGatewayInterface
{
    /**
     * Initiate a mobile money payment charge.
     *
     * @param array $paymentData [ 'amount' => float, 'currency' => string, 'reference' => string, 'phone' => string, 'customer_name' => string, 'customer_email' => string, 'notification_url' => string, 'description' => string ]
     * @return array Standardized response array [ 'success' => bool, 'message' => string, 'reference' => string, 'data' => array ]
     */
    public function initiateMobileMoneyPayment(array $paymentData): array;

    /**
     * Verify incoming webhook signature.
     *
     * @param string $signatureHeader Header signature string
     * @param string $rawBody Raw request payload body
     * @return bool True if valid signature
     */
    public function verifyWebhookSignature(string $signatureHeader, string $rawBody): bool;

    /**
     * Query transaction status from payment gateway.
     *
     * @param string $reference Transaction reference ID
     * @return array Standardized response array [ 'success' => bool, 'status' => string, 'data' => array ]
     */
    public function verifyTransaction(string $reference): array;
}
