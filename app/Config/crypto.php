<?php
/**
 * BM Forex Hub — Crypto Buy/Sell centralized configuration.
 *
 * This is the single source of truth for which assets the UI *could*
 * show. The `enabled` flags here are a fallback only — the live,
 * admin-editable source of truth is the `crypto_asset_config` table
 * in Supabase (see supabase/add-crypto-tables.sql). The API layer
 * (api/crypto-config.php) always prefers the DB table when reachable
 * and falls back to this file if the DB call fails, so the site never
 * goes down just because Supabase is briefly unreachable.
 *
 * To add a new asset later: add a row here AND enable it in the
 * crypto_asset_config table (or via the admin panel).
 */

return [

    // Fiat currencies BM Forex Hub can actually settle payment in via
    // the existing Kora integration. Kenya first, per the business.
    'fiat' => [
        'KES' => [
            'name'    => 'Kenyan Shilling',
            'enabled' => true, // supported by the existing Kora integration
        ],
    ],

    // Crypto assets. `enabled` is a conservative default — false means
    // "do not show as purchasable" until settlement is confirmed.
    // CoinGecko ids are used to fetch live USD/KES pricing only —
    // they are NOT a claim that the settlement provider supports the
    // asset yet.
    'assets' => [
        'USDT' => ['name' => 'Tether',    'coingecko_id' => 'tether',    'enabled' => true],
        'USDC' => ['name' => 'USD Coin',  'coingecko_id' => 'usd-coin',  'enabled' => false],
        'BTC'  => ['name' => 'Bitcoin',   'coingecko_id' => 'bitcoin',   'enabled' => false],
        'ETH'  => ['name' => 'Ethereum',  'coingecko_id' => 'ethereum',  'enabled' => false],
        'SOL'  => ['name' => 'Solana',    'coingecko_id' => 'solana',    'enabled' => false],
        'XRP'  => ['name' => 'XRP',       'coingecko_id' => 'ripple',    'enabled' => false],
        'DOGE' => ['name' => 'Dogecoin',  'coingecko_id' => 'dogecoin',  'enabled' => false],
        'TRX'  => ['name' => 'TRON',      'coingecko_id' => 'tron',      'enabled' => false],
        'LTC'  => ['name' => 'Litecoin',  'coingecko_id' => 'litecoin',  'enabled' => false],
    ],

    // "Popular Crypto" ticker row on the dashboard — shown for market
    // awareness even for assets not yet enabled for buy/sell.
    'ticker_symbols' => ['BTC', 'ETH', 'USDT', 'USDC', 'SOL', 'XRP', 'DOGE'],

    // Networks a given asset could settle over, ONCE a settlement
    // provider that documents wallet/deposit/withdrawal support for
    // them is connected. See app/Services/KoraCryptoCapabilityService.php
    // — as of this file, Kora documents no such capability, so no code
    // path reads `enabled` here to unlock anything; it exists purely so
    // the UI/DB layer doesn't need a second migration later. Mirrors
    // supabase/add-crypto-wallets-and-networks.sql's crypto_network_config
    // seed rows, which are the live-editable version of this list.
    'networks' => [
        'USDT' => [
            'TRX' => ['name' => 'TRON (TRC-20)',    'enabled' => false],
            'ETH' => ['name' => 'Ethereum (ERC-20)', 'enabled' => false],
            'SOL' => ['name' => 'Solana',            'enabled' => false],
        ],
    ],

    // Fees. Overridable via env (CRYPTO_BUY_FEE_PERCENT / CRYPTO_SELL_FEE_PERCENT)
    // in api/crypto-config.php. These are the defaults if no env var is set.
    'default_buy_fee_percent'  => 1.5,
    'default_sell_fee_percent' => 1.5,

    // How long a quote is valid for before the user must refresh it.
    'quote_ttl_seconds' => 30,

    // Live price cache TTL (protects the CoinGecko rate limit).
    'price_cache_ttl_seconds' => 20,
];
