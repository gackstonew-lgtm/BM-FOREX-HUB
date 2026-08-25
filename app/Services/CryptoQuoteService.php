<?php

namespace App\Services;

/**
 * Class CryptoQuoteService
 *
 * Builds a short-lived, tamper-proof price quote and re-validates it
 * at purchase time. The frontend NEVER supplies a price, rate, or fee
 * — it only ever echoes back the opaque signed quote token it was
 * given, and the backend re-derives/re-checks everything before a
 * transaction is created.
 *
 * The quote is a signed token (not a DB row) so a bad/expired quote
 * can never linger as state that something else might trust; it's
 * self-contained and expires by construction.
 */
class CryptoQuoteService
{
    private CryptoPriceService $priceService;
    private array $config;
    private string $secret;

    public function __construct()
    {
        require_once __DIR__ . '/CryptoPriceService.php';
        $this->priceService = new CryptoPriceService();
        $this->config       = require __DIR__ . '/../Config/crypto.php';
        $this->secret        = getenv('CRYPTO_QUOTE_SECRET') ?: ($_SERVER['CRYPTO_QUOTE_SECRET'] ?? '');
        if (empty($this->secret)) {
            // Fall back so local/dev environments still work, but this
            // should always be set explicitly in production .env.
            $this->secret = getenv('KORA_SECRET_KEY') ?: 'bmfh-crypto-quote-fallback';
        }
    }

    /**
     * Build a quote.
     *
     * @param string $side   'BUY' or 'SELL'
     * @param string $fiat   e.g. 'KES'
     * @param string $crypto e.g. 'USDT'
     * @param float  $fiatAmount   Required for BUY (amount of fiat the user wants to spend).
     * @param float  $cryptoAmount Required for SELL (amount of crypto the user wants to sell).
     *
     * @return array{ok:bool, error?:string, quote?:array}
     */
    public function buildQuote(string $side, string $fiat, string $crypto, ?float $fiatAmount, ?float $cryptoAmount): array
    {
        $side = strtoupper($side);
        if (!in_array($side, ['BUY', 'SELL'], true)) {
            return ['ok' => false, 'error' => 'Invalid transaction side'];
        }

        $price = $this->priceService->getPrice($crypto);
        if (!$price || empty($price['kes']) || $price['kes'] <= 0) {
            return ['ok' => false, 'error' => 'Live price unavailable for ' . $crypto . ' — please try again shortly'];
        }
        $rate = (float) $price['kes']; // 1 unit of crypto in KES

        $feePercent = $side === 'BUY'
            ? (float) (getenv('CRYPTO_BUY_FEE_PERCENT')  ?: ($this->config['default_buy_fee_percent']  ?? 1.5))
            : (float) (getenv('CRYPTO_SELL_FEE_PERCENT') ?: ($this->config['default_sell_fee_percent'] ?? 1.5));

        if ($side === 'BUY') {
            if (!$fiatAmount || $fiatAmount <= 0) {
                return ['ok' => false, 'error' => 'Enter a valid amount to spend'];
            }
            if ($fiatAmount > 5000000) { // sanity ceiling; adjust to real compliance limits
                return ['ok' => false, 'error' => 'Amount exceeds the maximum allowed per transaction'];
            }
            $feeAmount    = round($fiatAmount * ($feePercent / 100), 2);
            $netFiat      = $fiatAmount - $feeAmount;
            $cryptoAmount = $netFiat / $rate;
        } else {
            if (!$cryptoAmount || $cryptoAmount <= 0) {
                return ['ok' => false, 'error' => 'Enter a valid amount to sell'];
            }
            $grossFiat  = $cryptoAmount * $rate;
            $feeAmount  = round($grossFiat * ($feePercent / 100), 2);
            $fiatAmount = $grossFiat - $feeAmount;
        }

        $ttl       = (int) ($this->config['quote_ttl_seconds'] ?? 30);
        $expiresAt = time() + $ttl;

        $payload = [
            'side'          => $side,
            'fiat'          => $fiat,
            'crypto'        => $crypto,
            'rate'          => $rate,
            'fee_percent'   => $feePercent,
            'fee_amount'    => round($feeAmount, 2),
            'fiat_amount'   => round($fiatAmount, 2),
            'crypto_amount' => round($cryptoAmount, 8),
            'expires_at'    => $expiresAt,
            'nonce'         => bin2hex(random_bytes(8)),
        ];

        $token = $this->sign($payload);

        return ['ok' => true, 'quote' => array_merge($payload, ['token' => $token])];
    }

    /**
     * Verify a quote token supplied by the frontend at purchase time.
     * Checks signature, expiry, AND re-checks the live price hasn't
     * moved beyond a small tolerance — protecting against paying
     * against a stale/manipulated rate.
     */
    public function verifyQuote(string $token): array
    {
        $payload = $this->unsign($token);
        if ($payload === null) {
            return ['ok' => false, 'error' => 'Invalid quote'];
        }
        if (time() > ($payload['expires_at'] ?? 0)) {
            return ['ok' => false, 'error' => 'Quote expired — please refresh and try again'];
        }

        $current = $this->priceService->getPrice($payload['crypto']);
        if (!$current || empty($current['kes'])) {
            return ['ok' => false, 'error' => 'Live price unavailable — please refresh and try again'];
        }

        $deviation = abs($current['kes'] - $payload['rate']) / max($payload['rate'], 0.00000001);
        if ($deviation > 0.03) { // >3% market move since the quote was issued
            return ['ok' => false, 'error' => 'Market price moved — please refresh your quote'];
        }

        return ['ok' => true, 'quote' => $payload];
    }

    private function sign(array $payload): string
    {
        $json = json_encode($payload);
        $b64  = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
        $mac  = hash_hmac('sha256', $b64, $this->secret);
        return $b64 . '.' . $mac;
    }

    private function unsign(string $token): ?array
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) return null;
        [$b64, $mac] = $parts;
        $expected = hash_hmac('sha256', $b64, $this->secret);
        if (!hash_equals($expected, $mac)) return null;
        $padded = strtr($b64, '-_', '+/');
        $json   = base64_decode($padded . str_repeat('=', (4 - strlen($padded) % 4) % 4));
        $data   = json_decode($json, true);
        return is_array($data) ? $data : null;
    }
}
