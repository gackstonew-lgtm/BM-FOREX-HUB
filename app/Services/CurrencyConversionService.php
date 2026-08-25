<?php

namespace App\Services;

/**
 * Class CurrencyConversionService
 *
 * Centralized, cached currency conversion service for USD -> KES exchange rates.
 * Fetches live exchange rates from financial data providers, caches locally for 1 hour,
 * and falls back gracefully to stale cache or configured baseline rate during outages.
 */
class CurrencyConversionService
{
    private string $cacheFile;
    private int $cacheTtl;
    private float $fallbackRate;

    public function __construct(int $cacheTtlSeconds = 3600, float $fallbackRate = 129.00)
    {
        $this->cacheTtl     = $cacheTtlSeconds;
        $this->fallbackRate = $fallbackRate;

        $cacheDir = __DIR__ . '/../../cache';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        $this->cacheFile = $cacheDir . '/currency_rates.json';
    }

    /**
     * Get current USD to KES exchange rate.
     */
    public function getExchangeRate(string $from = 'USD', string $to = 'KES'): float
    {
        $from = strtoupper(trim($from));
        $to   = strtoupper(trim($to));

        if ($from === $to) {
            return 1.0;
        }

        $cached = $this->readCache();
        $now    = time();

        if ($cached && isset($cached['rate']) && ($now - ($cached['fetched_at'] ?? 0)) < $this->cacheTtl) {
            return (float) $cached['rate'];
        }

        $freshRate = $this->fetchLatestRate();

        if ($freshRate !== null && $freshRate > 0) {
            $this->writeCache($freshRate, $now, 'live_api');
            return $freshRate;
        }

        // Upstream failed — serve stale cache if available
        if ($cached && isset($cached['rate']) && (float)$cached['rate'] > 0) {
            return (float) $cached['rate'];
        }

        return $this->fallbackRate;
    }

    /**
     * Convert USD amount to KES based on current exchange rate.
     */
    public function convertUsdToKes(float $usdAmount): float
    {
        $rate = $this->getExchangeRate('USD', 'KES');
        return round($usdAmount * $rate, 2);
    }

    /**
     * Convert KES amount to USD based on current exchange rate.
     */
    public function convertKesToUsd(float $kesAmount): float
    {
        $rate = $this->getExchangeRate('USD', 'KES');
        if ($rate <= 0) return 0.0;
        return round($kesAmount / $rate, 2);
    }

    /**
     * Format USD amount as professional string.
     */
    public function formatUsd(float $usdAmount, bool $includeSymbol = true): string
    {
        $formatted = number_format($usdAmount, $usdAmount == floor($usdAmount) ? 0 : 2);
        return $includeSymbol ? '$' . $formatted : $formatted;
    }

    /**
     * Format KES amount as professional string.
     */
    public function formatKes(float $kesAmount, bool $includeCode = true): string
    {
        $formatted = number_format($kesAmount, $kesAmount == floor($kesAmount) ? 0 : 2);
        return $includeCode ? 'KES ' . $formatted : $formatted;
    }

    /**
     * Get rate metadata including rate, fetch timestamp, and source.
     */
    public function getRateInfo(): array
    {
        $cached = $this->readCache();
        $rate = $this->getExchangeRate('USD', 'KES');
        return [
            'from'        => 'USD',
            'to'          => 'KES',
            'rate'        => $rate,
            'fetched_at'  => isset($cached['fetched_at']) ? date('c', $cached['fetched_at']) : date('c'),
            'source'      => $cached['source'] ?? 'fallback',
            'cache_ttl'   => $this->cacheTtl,
        ];
    }

    /**
     * Fetch latest USD to KES rate from multiple reliable rate APIs.
     */
    private function fetchLatestRate(): ?float
    {
        $providers = [
            'https://open.er-api.com/v6/latest/USD' => function($data) {
                return isset($data['rates']['KES']) ? (float)$data['rates']['KES'] : null;
            },
            'https://api.exchangerate-api.com/v4/latest/USD' => function($data) {
                return isset($data['rates']['KES']) ? (float)$data['rates']['KES'] : null;
            },
            'https://api.coingecko.com/api/v3/simple/price?ids=tether&vs_currencies=kes' => function($data) {
                return isset($data['tether']['kes']) ? (float)$data['tether']['kes'] : null;
            },
        ];

        foreach ($providers as $url => $extractor) {
            try {
                $ctx = stream_context_create(['http' => [
                    'method'  => 'GET',
                    'timeout' => 5,
                    'header'  => "Accept: application/json\r\nUser-Agent: BMForexHub/1.0\r\n",
                    'ignore_errors' => true,
                ]]);
                $resp = @file_get_contents($url, false, $ctx);
                if ($resp !== false) {
                    $decoded = json_decode($resp, true);
                    if (is_array($decoded)) {
                        $rate = $extractor($decoded);
                        if ($rate !== null && $rate > 50 && $rate < 500) {
                            return round($rate, 2);
                        }
                    }
                }
            } catch (\Throwable $e) {
                @error_log("[CURRENCY SERVICE WARNING] Failed rate fetch from $url: " . $e->getMessage());
            }
        }

        return null;
    }

    private function readCache(): ?array
    {
        if (!file_exists($this->cacheFile)) return null;
        $raw = @file_get_contents($this->cacheFile);
        if (!$raw) return null;
        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || !isset($decoded['fetched_at'], $decoded['rate'])) return null;
        return $decoded;
    }

    private function writeCache(float $rate, int $fetchedAt, string $source): void
    {
        @file_put_contents(
            $this->cacheFile,
            json_encode([
                'fetched_at' => $fetchedAt,
                'rate'       => $rate,
                'source'     => $source,
            ]),
            LOCK_EX
        );
    }
}
