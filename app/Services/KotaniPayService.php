<?php

namespace App\Services;

require_once __DIR__ . '/Contracts/CryptoSettlementProviderInterface.php';

use App\Services\Contracts\CryptoSettlementProviderInterface;

/**
 * Class KotaniPayService
 *
 * Real integration with Kotani Pay's v3 API (documentation.kotanipay.com),
 * used as the crypto settlement provider referenced in
 * CryptoSettlementService — i.e. the piece Kora deliberately does NOT
 * do (see KoraCryptoCapabilityService). Kotani Pay publicly documents
 * exactly the two operations this platform needs:
 *
 *   - Onramp:  "Accept fiat payment and deliver crypto to a wallet
 *     address" -> used for BUY, after Kora has already confirmed the
 *     KES payment.
 *   - Offramp: "Accept crypto and disburse fiat via mobile money or
 *     bank transfer" -> used for SELL.
 *
 * Auth model per Kotani's docs: call POST /auth/login with an account
 * email to get a magic-link-issued JWT, then GET /auth/api-key to mint
 * a long-lived API key. That exchange is a one-time, human/dashboard
 * step (integrator.kotanipay.com) — this class expects the resulting
 * API key to already be in KOTANI_API_KEY and sends it as a Bearer
 * token on every request, exactly as documented.
 *
 * IMPORTANT — before enabling this in production:
 * Kotani's exact request/response JSON field names for the onramp and
 * offramp "create" endpoints should be confirmed against the live
 * Postman collection / dashboard for your integrator account (field
 * names can differ slightly between sandbox versions). The endpoint
 * paths, base URLs, and auth model below are taken directly from
 * Kotani's published documentation as of this writing; the payload
 * shapes are this project's best-documented interpretation and are
 * marked with TODO where they should be double-checked against your
 * own Postman collection before go-live. This mirrors the same
 * "never fake a capability" discipline used for Kora elsewhere in this
 * codebase — the difference here is that Kotani *does* document these
 * capabilities, so it's safe to wire up for real rather than gating
 * everything to false.
 */
class KotaniPayService implements CryptoSettlementProviderInterface
{
    private string $apiKey;
    private string $baseUrl;
    private string $webhookSecret;
    private string $webhookUrl;
    private string $logPath;

    // Default settlement network per asset — must match a network
    // Kotani actually supports for that asset on your integrator
    // account. Overridable per-request via $transaction['network'].
    private array $defaultNetwork = [
        'USDT' => 'CELO', // TODO: confirm the exact network code Kotani expects
                          // for your account (e.g. CELO, POLYGON, TRON) —
                          // see KOTANI_DEFAULT_NETWORK_<SYMBOL> env override below.
    ];

    public function __construct()
    {
        $this->apiKey = getenv('KOTANI_API_KEY') ?: ($_SERVER['KOTANI_API_KEY'] ?? '');

        $base = getenv('KOTANI_BASE_URL') ?: ($_SERVER['KOTANI_BASE_URL'] ?? '');
        if (!$base) {
            // Fail safe to sandbox rather than production if misconfigured.
            $base = (getenv('KOTANI_ENV') ?: 'sandbox') === 'production'
                ? 'https://api.kotanipay.io'
                : 'https://sandbox-api.kotanipay.io';
        }
        $this->baseUrl = rtrim($base, '/');

        $this->webhookSecret = getenv('KOTANI_WEBHOOK_SECRET') ?: ($_SERVER['KOTANI_WEBHOOK_SECRET'] ?? '');

        $this->webhookUrl = getenv('KOTANI_WEBHOOK_URL') ?: ($_SERVER['KOTANI_WEBHOOK_URL'] ?? 'https://bmforexhub.exchange/api/kotani-webhook.php');

        $logDir = __DIR__ . '/../../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $this->logPath = $logDir . '/kotani_settlement.log';
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    private function log(string $message, string $level = 'INFO'): void
    {
        @file_put_contents($this->logPath, '[' . date('c') . "] [$level] $message\n", FILE_APPEND | LOCK_EX);
    }

    private function networkFor(array $transaction): string
    {
        return $transaction['network']
            ?? (getenv('KOTANI_DEFAULT_NETWORK_' . strtoupper($transaction['crypto_symbol'] ?? '')) ?: null)
            ?? ($this->defaultNetwork[$transaction['crypto_symbol'] ?? ''] ?? 'CELO');
    }

    /**
     * BUY settlement: onramp — deliver crypto to the customer's wallet.
     * Requires $transaction['destination_address'] to already be set
     * (collected from the customer at buy time and stored on the
     * crypto_transactions row — see api/crypto-buy.php).
     */
    public function deliverCrypto(array $transaction): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => 'SETTLEMENT_PENDING',
                'message' => 'Kotani Pay is not configured (KOTANI_API_KEY missing) — crypto delivery is pending.',
                'settlement_reference' => null,
                'provider_tx_id' => null,
            ];
        }

        $destination = trim($transaction['destination_address'] ?? '');
        if ($destination === '') {
            $this->log("BUY {$transaction['transaction_reference']}: missing destination_address — cannot deliver crypto", 'ERROR');
            return [
                'ok' => false,
                'status' => 'SETTLEMENT_PENDING',
                'message' => 'No destination wallet address was provided for this purchase — contact support to supply one.',
                'settlement_reference' => null,
                'provider_tx_id' => null,
            ];
        }

        $network = $this->networkFor($transaction);

        // TODO: confirm exact field names against Kotani's onramp "create"
        // endpoint in your Postman collection before go-live.
        $payload = [
            'externalReference' => $transaction['transaction_reference'],
            'assetCode'          => $transaction['crypto_symbol'],
            'network'            => $network,
            'amount'             => (string) $transaction['crypto_amount'],
            'walletAddress'      => $destination,
            'callbackUrl'        => $this->webhookUrl,
            'fiatAmount'         => (string) $transaction['fiat_amount'],
            'fiatCurrency'       => $transaction['fiat_currency'] ?? 'KES',
        ];

        $this->log("BUY {$transaction['transaction_reference']}: requesting onramp delivery of {$payload['amount']} {$payload['assetCode']} to {$destination} on {$network}");

        $res = $this->request('POST', '/api/v3/onramp', $payload);

        if ($res['ok']) {
            $providerRef = $res['json']['data']['id']
                ?? $res['json']['data']['requestId']
                ?? $res['json']['id']
                ?? null;

            return [
                'ok' => true,
                'status' => 'COMPLETED',
                'message' => 'Crypto delivered to your wallet address.',
                'settlement_reference' => $providerRef,
                'provider_tx_id' => $providerRef,
            ];
        }

        $this->log("BUY {$transaction['transaction_reference']}: onramp request failed — HTTP {$res['http_code']}: {$res['raw']}", 'ERROR');

        return [
            'ok' => false,
            'status' => 'SETTLEMENT_PENDING',
            'message' => $res['json']['message'] ?? 'Crypto delivery could not be confirmed yet — our team is checking on it.',
            'settlement_reference' => null,
            'provider_tx_id' => null,
        ];
    }

    /**
     * SELL step 1: offramp — ask Kotani for a deposit address the
     * customer should send crypto to, plus register the intended fiat
     * payout destination (mobile money number / bank details) so the
     * payout can be triggered once the deposit is confirmed.
     */
    public function requestCryptoDeposit(array $transaction): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => 'SETTLEMENT_PENDING',
                'message' => 'Kotani Pay is not configured (KOTANI_API_KEY missing) — crypto sell is not yet available.',
                'deposit_address' => null,
                'network' => null,
                'settlement_reference' => null,
            ];
        }

        $network = $this->networkFor($transaction);

        // TODO: confirm exact field names against Kotani's offramp "create"
        // endpoint in your Postman collection before go-live. Mobile money
        // payout details (phone/network) must be collected from the
        // customer — wire that into api/crypto-sell.php once the UI
        // collects it, then pass it through here as
        // $transaction['payout_phone'] / $transaction['payout_channel'].
        $payload = [
            'externalReference' => $transaction['transaction_reference'],
            'assetCode'          => $transaction['crypto_symbol'],
            'network'            => $network,
            'amount'             => (string) $transaction['crypto_amount'],
            'fiatCurrency'       => $transaction['fiat_currency'] ?? 'KES',
            'callbackUrl'        => $this->webhookUrl,
        ];
        if (!empty($transaction['payout_phone'])) {
            $payload['payoutChannel'] = $transaction['payout_channel'] ?? 'mobile-money';
            $payload['payoutPhone']   = $transaction['payout_phone'];
        }

        $this->log("SELL {$transaction['transaction_reference']}: requesting offramp deposit address for {$payload['amount']} {$payload['assetCode']} on {$network}");

        $res = $this->request('POST', '/api/v3/offramp', $payload);

        if ($res['ok']) {
            $data = $res['json']['data'] ?? $res['json'] ?? [];
            $depositAddress = $data['depositAddress'] ?? $data['walletAddress'] ?? null;
            $providerRef    = $data['id'] ?? $data['requestId'] ?? null;

            if (!$depositAddress) {
                $this->log("SELL {$transaction['transaction_reference']}: offramp accepted but no deposit address returned", 'ERROR');
                return [
                    'ok' => false,
                    'status' => 'SETTLEMENT_PENDING',
                    'message' => 'Sell request accepted but no deposit address was returned — our team will follow up.',
                    'deposit_address' => null,
                    'network' => $network,
                    'settlement_reference' => $providerRef,
                ];
            }

            return [
                'ok' => true,
                'status' => 'SETTLEMENT_PENDING',
                'message' => "Send {$transaction['crypto_amount']} {$transaction['crypto_symbol']} to the address shown to complete this sell.",
                'deposit_address' => $depositAddress,
                'network' => $network,
                'settlement_reference' => $providerRef,
            ];
        }

        $this->log("SELL {$transaction['transaction_reference']}: offramp request failed — HTTP {$res['http_code']}: {$res['raw']}", 'ERROR');

        return [
            'ok' => false,
            'status' => 'SETTLEMENT_PENDING',
            'message' => $res['json']['message'] ?? 'Crypto sell is not available right now — our team will contact you.',
            'deposit_address' => null,
            'network' => $network,
            'settlement_reference' => null,
        ];
    }

    /**
     * SELL step 2: called once the deposit is confirmed (typically from
     * the Kotani webhook) — disburses the fiat equivalent to the
     * customer. On Kotani's offramp flow this payout is normally
     * triggered automatically once the on-chain deposit is confirmed,
     * so this mostly exists as an explicit, auditable adapter boundary
     * and a place to re-trigger/verify payout status if needed.
     */
    public function disburseFiat(array $transaction): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => 'SETTLEMENT_PENDING',
                'message' => 'Kotani Pay is not configured — fiat payout is pending.',
                'settlement_reference' => null,
            ];
        }

        $providerRef = $transaction['settlement_reference'] ?? null;
        if (!$providerRef) {
            return [
                'ok' => false,
                'status' => 'SETTLEMENT_PENDING',
                'message' => 'No Kotani offramp reference on file for this transaction yet.',
                'settlement_reference' => null,
            ];
        }

        $status = $this->getTransactionStatus($providerRef);

        if ($status['ok'] && in_array(strtolower($status['status'] ?? ''), ['completed', 'success', 'successful'], true)) {
            return [
                'ok' => true,
                'status' => 'COMPLETED',
                'message' => 'Fiat payout confirmed by Kotani Pay.',
                'settlement_reference' => $providerRef,
            ];
        }

        return [
            'ok' => false,
            'status' => 'SETTLEMENT_PENDING',
            'message' => 'Fiat payout is still processing with Kotani Pay.',
            'settlement_reference' => $providerRef,
        ];
    }

    public function getTransactionStatus(string $providerReference): array
    {
        if (!$this->isConfigured() || !$providerReference) {
            return ['ok' => false, 'status' => 'unknown', 'data' => []];
        }

        // TODO: confirm the exact status-lookup path against Kotani's docs
        // for your account — commonly /api/v3/transactions/{id} or similar.
        $res = $this->request('GET', '/api/v3/transactions/' . rawurlencode($providerReference));

        if (!$res['ok']) {
            return ['ok' => false, 'status' => 'unknown', 'data' => []];
        }

        $data = $res['json']['data'] ?? $res['json'] ?? [];
        return [
            'ok' => true,
            'status' => $data['status'] ?? 'unknown',
            'data' => $data,
        ];
    }

    /**
     * Kotani signs webhooks with an HMAC secret shared via the
     * integrator dashboard (KOTANI_WEBHOOK_SECRET). Confirm the exact
     * header name and signing scheme (raw body vs. specific fields)
     * against your dashboard's webhook settings before relying on this
     * in production — the scheme below (HMAC-SHA256 of the raw body,
     * hex-encoded) is the common pattern used across Kotani's docs.
     */
    public function verifyWebhookSignature(string $signatureHeader, string $rawBody): bool
    {
        if (empty($this->webhookSecret) || empty($signatureHeader)) {
            return false;
        }
        $expected = hash_hmac('sha256', $rawBody, $this->webhookSecret);
        return hash_equals($expected, $signatureHeader);
    }

    private function request(string $method, string $path, ?array $payload = null): array
    {
        $url = $this->baseUrl . $path;
        $ch = curl_init($url);

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 20,
        ]);

        if ($payload !== null && in_array($method, ['POST', 'PATCH', 'PUT'], true)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $raw = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            $this->log("HTTP request to $url failed: $err", 'ERROR');
            return ['ok' => false, 'http_code' => 0, 'json' => null, 'raw' => $err];
        }

        $json = json_decode($raw, true);
        $ok = $httpCode >= 200 && $httpCode < 300;

        return ['ok' => $ok, 'http_code' => $httpCode, 'json' => $json, 'raw' => $raw];
    }
}
