<?php

namespace App\Services;

require_once __DIR__ . '/Contracts/PaymentGatewayInterface.php';

use App\Services\Contracts\PaymentGatewayInterface;

/**
 * Class KoraPaymentService
 * Principal Payment System Implementation for Kora Pay.
 * Follows SOLID principles and handles M-Pesa STK Push, Card payments, status verification, webhooks, and refunds.
 */
class KoraPaymentService implements PaymentGatewayInterface
{
    private string $publicKey;
    private string $secretKey;
    private string $encryptionKey;
    private string $baseUrl;
    private string $webhookUrl;
    private string $successUrl;
    private string $failedUrl;
    private string $cancelUrl;
    private string $logPath;

    public function __construct()
    {
        $this->publicKey     = getenv('KORA_PUBLIC_KEY')     ?: ($_SERVER['KORA_PUBLIC_KEY']     ?? '');
        $this->secretKey    = getenv('KORA_SECRET_KEY')    ?: ($_SERVER['KORA_SECRET_KEY']    ?? '');
        $this->encryptionKey = getenv('KORA_ENCRYPTION_KEY') ?: ($_SERVER['KORA_ENCRYPTION_KEY'] ?? '');
        
        $base = getenv('KORA_BASE_URL') ?: ($_SERVER['KORA_BASE_URL'] ?? 'https://api.korapay.com');
        $this->baseUrl = rtrim($base, '/');

        $this->webhookUrl = getenv('KORA_WEBHOOK_URL') ?: ($_SERVER['KORA_WEBHOOK_URL'] ?? 'https://bmforexhub.exchange/api/kora-webhook.php');
        $this->successUrl = strtok(getenv('KORA_SUCCESS_URL') ?: ($_SERVER['KORA_SUCCESS_URL'] ?? 'https://bmforexhub.exchange/subscribe.php'), '?');
        $this->failedUrl  = strtok(getenv('KORA_FAILED_URL')  ?: ($_SERVER['KORA_FAILED_URL']  ?? 'https://bmforexhub.exchange/subscribe.php'), '?');
        $this->cancelUrl  = strtok(getenv('KORA_CANCEL_URL')  ?: ($_SERVER['KORA_CANCEL_URL']  ?? 'https://bmforexhub.exchange/subscribe.php'), '?');

        $logDir = __DIR__ . '/../../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $this->logPath = $logDir . '/kora_payments.log';
    }

    /**
     * Log transaction events securely
     */
    public function logTransaction(string $message, string $level = 'INFO'): void
    {
        $timestamp = date('c');
        $logMessage = "[$timestamp] [$level] $message\n";
        @file_put_contents($this->logPath, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Primary Payment Initialization Method (Handles M-Pesa STK push & Card Payments)
     */
    public function initializePayment(array $paymentData): array
    {
        if (empty($this->secretKey)) {
            $this->logTransaction("Payment initialization failed: KORA_SECRET_KEY missing", "ERROR");
            return [
                'success' => false,
                'message' => 'Payment gateway API secret key is missing.',
                'reference' => $paymentData['reference'] ?? '',
                'data' => []
            ];
        }

        $method = strtolower($paymentData['payment_method'] ?? 'mpesa');
        $phone  = $paymentData['phone'] ?? '';

        // Handle Card Payments directly with Card Details (Card Number, Name, Expiry, CVV)
        if ($method === 'card') {
            return $this->processCardPayment($paymentData);
        }

        // NOTE: Direct STK Push endpoint (/mobile-money) returns HTTP 403 on this account
        // (not enabled for this merchant). Skip it and go straight to Kora Checkout URL
        // which supports M-Pesa, Airtel Money, etc. on the hosted payment page.

        // Fallback or Card payment: Use Kora Checkout Charge Initialize endpoint
        $initEndpoint = $this->baseUrl . '/merchant/api/v1/charges/initialize';
        $initPayload  = [
            'amount'           => (float)($paymentData['amount'] ?? 0),
            'currency'         => strtoupper($paymentData['currency'] ?? 'KES'),
            'reference'        => $paymentData['reference'] ?? '',
            'notification_url' => $paymentData['notification_url'] ?? $this->webhookUrl,
            'redirect_url'     => $paymentData['redirect_url'] ?? $this->successUrl,
            'customer'         => [
                'name'  => $paymentData['customer_name'] ?? 'BM Forex Trader',
                'email' => $paymentData['customer_email'] ?? 'trader@bmforexhub.exchange',
            ],
        ];

        if (!empty($paymentData['channels']) && is_array($paymentData['channels'])) {
            $initPayload['channels'] = $paymentData['channels'];
        }

        $this->logTransaction("Initializing Kora Checkout session for Ref: {$initPayload['reference']}");
        $resInit = $this->sendHttpRequest($initEndpoint, 'POST', $initPayload);

        if ($resInit['http_code'] >= 200 && $resInit['http_code'] < 300 && ($resInit['json']['status'] ?? false) === true) {
            $checkoutUrl = $resInit['json']['data']['checkout_url'] ?? '';
            return [
                'success'      => true,
                'message'      => 'Redirecting to secure Kora Pay payment gateway...',
                'reference'    => $initPayload['reference'],
                'checkout_url' => $checkoutUrl,
                'data'         => $resInit['json']['data'] ?? [],
            ];
        }

        $errMsg = $resInit['json']['message'] ?? 'Unable to initialize payment session.';
        $this->logTransaction("Initialize error: $errMsg | HTTP {$resInit['http_code']} | Ref: {$initPayload['reference']}", "ERROR");
        if (!empty($resInit['raw'])) {
            $this->logTransaction("Kora raw response: " . substr($resInit['raw'], 0, 500), "DEBUG");
        }
        return [
            'success'   => false,
            'message'   => $errMsg,
            'reference' => $paymentData['reference'] ?? '',
            'data'      => $resInit['json']['data'] ?? [],
        ];
    }

    /**
     * Process Credit / Debit Card Payment with Card details (Name, Number, Expiry, CVV)
     */
    public function processCardPayment(array $paymentData): array
    {
        $cardName   = trim($paymentData['card_name'] ?? '');
        $cardNumber = preg_replace('/\D/', '', $paymentData['card_number'] ?? '');
        $cardExpiry = trim($paymentData['card_expiry'] ?? '');
        $cardCvv    = trim($paymentData['card_cvv'] ?? '');

        if (empty($cardNumber) || strlen($cardNumber) < 13) {
            return [
                'success'   => false,
                'message'   => 'Please provide a valid credit or debit card number.',
                'reference' => $paymentData['reference'] ?? '',
                'data'      => []
            ];
        }

        $expiryParts = explode('/', $cardExpiry);
        $expMonth = isset($expiryParts[0]) ? str_pad(trim($expiryParts[0]), 2, '0', STR_PAD_LEFT) : '12';
        $expYear  = isset($expiryParts[1]) ? trim($expiryParts[1]) : '28';
        if (strlen($expYear) === 4) {
            $expYear = substr($expYear, 2);
        }

        $last4 = substr($cardNumber, -4);
        $this->logTransaction("Processing Card Payment for Ref: {$paymentData['reference']} | Name: $cardName | Card ending in: $last4");

        // Construct Card charge payload
        $cardChargeData = [
            'reference' => $paymentData['reference'] ?? '',
            'amount'    => (float)($paymentData['amount'] ?? 0),
            'currency'  => strtoupper($paymentData['currency'] ?? 'KES'),
            'customer'  => [
                'name'  => !empty($cardName) ? $cardName : ($paymentData['customer_name'] ?? 'BM Forex Trader'),
                'email' => $paymentData['customer_email'] ?? 'trader@bmforexhub.exchange',
            ],
            'card' => [
                'number'       => $cardNumber,
                'cvv'          => $cardCvv,
                'expiry_month' => $expMonth,
                'expiry_year'  => $expYear,
                'name'         => $cardName,
            ],
        ];

        $endpoint = $this->baseUrl . '/merchant/api/v1/charges/card';
        $rawJson  = json_encode($cardChargeData);

        // Encrypt payload if key is available
        $encKey  = $this->encryptionKey;
        $payload = null;
        if (!empty($encKey) && strlen($encKey) >= 16) {
            $key    = strlen($encKey) < 32 ? str_pad($encKey, 32, "\0") : substr($encKey, 0, 32);
            $iv     = openssl_random_pseudo_bytes(16);
            $tag    = "";
            $ct     = openssl_encrypt($rawJson, "aes-256-gcm", $key, OPENSSL_RAW_DATA, $iv, $tag, "", 16);
            $encStr = bin2hex($iv) . ":" . bin2hex($ct) . ":" . bin2hex($tag);
            $payload = ['charge_data' => $encStr];
        } else {
            $payload = $cardChargeData;
        }

        $res = $this->sendHttpRequest($endpoint, 'POST', $payload);

        if ($res['http_code'] >= 200 && $res['http_code'] < 300 && ($res['json']['status'] ?? false) === true) {
            return [
                'success'   => true,
                'message'   => $res['json']['message'] ?? 'Card payment processed successfully.',
                'reference' => $paymentData['reference'],
                'data'      => $res['json']['data'] ?? [],
            ];
        }

        if (!empty($res['json']['data']['redirect_url']) || !empty($res['json']['data']['checkout_url'])) {
            return [
                'success'      => true,
                'message'      => 'Redirecting for 3D Secure card verification...',
                'reference'    => $paymentData['reference'],
                'checkout_url' => $res['json']['data']['redirect_url'] ?? $res['json']['data']['checkout_url'],
                'data'         => $res['json']['data'] ?? [],
            ];
        }

        // Declined, failed or otherwise unsuccessful card charge
        $errMsg = $res['json']['message'] ?? ($res['json']['error'] ?? 'Card payment failed. Please verify your card details and try again.');
        $this->logTransaction("Card charge failed: $errMsg | HTTP {$res['http_code']} | Ref: {$paymentData['reference']}", "ERROR");
        if (!empty($res['raw'])) {
            $this->logTransaction("Kora card raw response: " . substr($res['raw'], 0, 500), "DEBUG");
        }
        return [
            'success'   => false,
            'message'   => $errMsg,
            'reference' => $paymentData['reference'],
            'data'      => $res['json']['data'] ?? [
                'card_last4' => $last4,
                'card_name'  => $cardName,
            ],
        ];
    }

    /**
     * Compatibility interface method
     */
    public function initiateMobileMoneyPayment(array $paymentData): array
    {
        return $this->initializePayment($paymentData);
    }

    /**
     * Verify payment status by reference
     */
    public function verifyPayment(string $reference): array
    {
        return $this->getTransaction($reference);
    }

    /**
     * Verify transaction interface compliance
     */
    public function verifyTransaction(string $reference): array
    {
        return $this->getTransaction($reference);
    }

    /**
     * Get transaction status from Kora API
     */
    public function getTransaction(string $reference): array
    {
        if (empty($this->secretKey)) {
            return ['success' => false, 'status' => 'unknown', 'data' => []];
        }

        $endpoint = $this->baseUrl . '/merchant/api/v1/charges/' . urlencode($reference);
        $res = $this->sendHttpRequest($endpoint, 'GET');

        $status = $res['json']['data']['status'] ?? 'unknown';
        return [
            'success' => ($res['json']['status'] ?? false) === true,
            'status'  => strtolower($status),
            'data'    => $res['json']['data'] ?? [],
        ];
    }

    /**
     * Verify Webhook Signature (HMAC SHA-256)
     */
    public function verifyWebhookSignature(string $signatureHeader, string $rawBody): bool
    {
        $secret = $this->secretKey;
        if (empty($secret)) {
            $this->logTransaction("Webhook signature check skipped: No secret configured", "WARNING");
            return true;
        }

        if (empty($signatureHeader)) {
            $this->logTransaction("Webhook verification failed: Missing signature header", "WARNING");
            return false;
        }

        $computedRaw = hash_hmac('sha256', $rawBody, $secret);
        if (hash_equals($computedRaw, $signatureHeader)) {
            return true;
        }

        $decoded = json_decode($rawBody, true);
        if (isset($decoded['data'])) {
            $dataJson = json_encode($decoded['data']);
            $computedData = hash_hmac('sha256', $dataJson, $secret);
            if (hash_equals($computedData, $signatureHeader)) {
                return true;
            }
        }

        $this->logTransaction("Signature mismatch! Received: $signatureHeader | Computed: $computedRaw", "ERROR");
        return false;
    }

    /**
     * Process incoming Webhook payload
     */
    public function handleWebhook(string $signature, string $payload): array
    {
        if (!$this->verifyWebhookSignature($signature, $payload)) {
            return ['success' => false, 'message' => 'Invalid signature'];
        }

        $event = json_decode($payload, true);
        if (!$event) {
            return ['success' => false, 'message' => 'Invalid payload'];
        }

        $eventType = $event['event'] ?? ($event['type'] ?? '');
        $data      = $event['data'] ?? [];
        $ref       = $data['reference'] ?? '';
        $status    = strtolower($data['status'] ?? '');

        return [
            'success'   => true,
            'event'     => $eventType,
            'reference' => $ref,
            'status'    => $status,
            'data'      => $data,
        ];
    }

    /**
     * Refund payment
     */
    public function refundPayment(string $reference, float $amount, string $reason = 'Requested refund'): array
    {
        $endpoint = $this->baseUrl . '/merchant/api/v1/refunds';
        $payload  = [
            'reference' => $reference,
            'amount'    => $amount,
            'reason'    => $reason,
        ];

        $this->logTransaction("Refund requested for Ref: $reference | Amount: KES $amount | Reason: $reason");
        $res = $this->sendHttpRequest($endpoint, 'POST', $payload);

        return [
            'success' => ($res['json']['status'] ?? false) === true,
            'message' => $res['json']['message'] ?? 'Refund processed',
            'data'    => $res['json']['data'] ?? [],
        ];
    }

    /**
     * Helper for Curl / Stream HTTP requests
     */
    private function sendHttpRequest(string $url, string $method = 'GET', ?array $data = null): array
    {
        $headers = [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $payload = $data ? json_encode($data) : null;

        // Try cURL first (more reliable on shared hosting)
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST  => $method,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $response   = curl_exec($ch);
            $httpCode   = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErrno  = curl_errno($ch);
            $curlError  = curl_error($ch);
            curl_close($ch);

            if ($curlErrno !== 0) {
                $this->logTransaction("cURL error ($curlErrno): $curlError", "ERROR");
            }

            $json = $response ? (json_decode($response, true) ?? []) : [];
            return [
                'http_code' => $httpCode,
                'json'      => $json,
                'raw'       => $response,
            ];
        }

        // Fallback: stream context
        $ctx = stream_context_create([
            'http' => [
                'method'        => $method,
                'header'        => implode("\r\n", $headers),
                'content'       => $payload,
                'timeout'       => 15,
                'ignore_errors' => true,
            ],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);

        $response = @file_get_contents($url, false, $ctx);
        
        $httpCode = 0;
        if (isset($http_response_header)) {
            preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $matches);
            $httpCode = isset($matches[1]) ? (int)$matches[1] : 0;
        }

        $json = $response ? (json_decode($response, true) ?? []) : [];
        return [
            'http_code' => $httpCode,
            'json'      => $json,
            'raw'       => $response,
        ];
    }
}

// Alias KoraPayService to KoraPaymentService for backward compatibility
if (!class_exists('App\Services\KoraPayService')) {
    class_alias('App\Services\KoraPaymentService', 'App\Services\KoraPayService');
}
