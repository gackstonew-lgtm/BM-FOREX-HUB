<?php

namespace App\Services;

/**
 * Class CryptoPriceService
 *
 * Retrieves current crypto market prices for the configured assets.
 * This is the ONLY place in the codebase that talks to the market
 * data provider — nothing else, and never the frontend, calls it
 * directly. Prices are cached briefly to a local file so we don't
 * hammer the upstream API and so a transient upstream failure doesn't
 * take the widget down.
 *
 * Provider: CoinGecko public "simple price" endpoint. It requires no
 * API key for this usage, so there is no secret to leak — but the
 * call still only ever happens server-side, keeping the door open to
 * swap in a paid/keyed provider later without touching the frontend
 * or any other backend file (see fetchFromProvider()).
 */
class CryptoPriceService
{
    private string $cacheFile;
    private int $cacheTtl;
    private array $config;

    public function __construct()
    {
        $this->config    = require __DIR__ . '/../Config/crypto.php';
        $this->cacheTtl  = (int) ($this->config['price_cache_ttl_seconds'] ?? 20);
        $cacheDir        = __DIR__ . '/../../cache';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        $this->cacheFile = $cacheDir . '/crypto_prices.json';
    }

    /**
     * Get current prices (in USD and KES) for the given asset symbols.
     * Returns: ['USDT' => ['usd' => 1.0, 'kes' => 129.4, 'usd_24h_change' => 0.01], ...]
     */
    public function getPrices(array $symbols): array
    {
        $cached = $this->readCache();
        $now    = time();

        if ($cached && ($now - $cached['fetched_at']) < $this->cacheTtl) {
            return $this->filterSymbols($cached['data'], $symbols);
        }

        $fresh = $this->fetchFromProvider($symbols);

        if ($fresh === null) {
            // Upstream failed — serve stale cache rather than breaking the widget.
            if ($cached) {
                return $this->filterSymbols($cached['data'], $symbols);
            }
            return [];
        }

        $this->writeCache($fresh, $now);
        return $this->filterSymbols($fresh, $symbols);
    }

    /**
     * Get a single asset's price. Convenience wrapper for quote building.
     */
    public function getPrice(string $symbol): ?array
    {
        $prices = $this->getPrices([$symbol]);
        return $prices[$symbol] ?? null;
    }

    private function filterSymbols(array $data, array $symbols): array
    {
        $out = [];
        foreach ($symbols as $s) {
            if (isset($data[$s])) $out[$s] = $data[$s];
        }
        return $out;
    }

    private function fetchFromProvider(array $symbols): ?array
    {
        $assets = $this->config['assets'] ?? [];
        $idMap  = [];
        foreach ($symbols as $s) {
            if (isset($assets[$s]['coingecko_id'])) {
                $idMap[$assets[$s]['coingecko_id']] = $s;
            }
        }
        if (empty($idMap)) return null;

        $ids = implode(',', array_keys($idMap));
        $url = 'https://api.coingecko.com/api/v3/simple/price'
             . '?ids=' . urlencode($ids)
             . '&vs_currencies=usd,kes'
             . '&include_24hr_change=true';

        $ctx = stream_context_create(['http' => [
            'method'  => 'GET',
            'timeout' => 6,
            'header'  => "Accept: application/json\r\nUser-Agent: BMForexHub/1.0\r\n",
            'ignore_errors' => true,
        ]]);

        $resp = @file_get_contents($url, false, $ctx);
        if ($resp === false) return null;

        $decoded = json_decode($resp, true);
        if (!is_array($decoded)) return null;

        $out = [];
        foreach ($idMap as $geckoId => $symbol) {
            if (!isset($decoded[$geckoId])) continue;
            $row = $decoded[$geckoId];
            $out[$symbol] = [
                'usd'            => (float) ($row['usd'] ?? 0),
                'kes'            => (float) ($row['kes'] ?? 0),
                'usd_24h_change' => (float) ($row['usd_24h_change'] ?? 0),
            ];
        }
        return $out;
    }

    private function readCache(): ?array
    {
        if (!file_exists($this->cacheFile)) return null;
        $raw = @file_get_contents($this->cacheFile);
        if (!$raw) return null;
        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || !isset($decoded['fetched_at'], $decoded['data'])) return null;
        return $decoded;
    }

    private function writeCache(array $data, int $fetchedAt): void
    {
        @file_put_contents(
            $this->cacheFile,
            json_encode(['fetched_at' => $fetchedAt, 'data' => $data]),
            LOCK_EX
        );
    }
}
