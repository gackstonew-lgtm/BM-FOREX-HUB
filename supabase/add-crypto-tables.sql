-- ============================================================
-- BM Forex Hub — Crypto Buy/Sell feature
-- Run this script in the Supabase SQL Editor.
-- Additive only: does not touch payments / subscriptions / profiles.
-- ============================================================

-- Transaction history for crypto buy/sell (kept fully separate from
-- the existing `payments` table used by subscriptions).
CREATE TABLE IF NOT EXISTS crypto_transactions (
  id                  uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id             uuid NOT NULL,
  transaction_reference text NOT NULL UNIQUE,
  type                text NOT NULL CHECK (type IN ('BUY','SELL')),
  fiat_currency       text NOT NULL,
  fiat_amount         numeric(18,2) NOT NULL,
  crypto_symbol       text NOT NULL,
  crypto_amount       numeric(28,10) NOT NULL,
  quoted_rate         numeric(28,10) NOT NULL,
  fee_amount          numeric(18,2) NOT NULL DEFAULT 0,
  fee_currency        text NOT NULL DEFAULT 'KES',
  status              text NOT NULL DEFAULT 'PENDING_PAYMENT'
                        CHECK (status IN (
                          'PENDING_PAYMENT','PAYMENT_PROCESSING','PAYMENT_CONFIRMED',
                          'SETTLEMENT_PENDING','COMPLETED','FAILED','CANCELLED','REFUNDED'
                        )),
  payment_method      text,
  kora_reference      text,
  settlement_reference text,
  settlement_note     text,
  quote_hash          text,
  quote_expires_at    timestamptz,
  failure_reason      text,
  raw_webhook         jsonb,
  created_at          timestamptz NOT NULL DEFAULT now(),
  updated_at          timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_crypto_txn_user_id ON crypto_transactions(user_id);
CREATE INDEX IF NOT EXISTS idx_crypto_txn_reference ON crypto_transactions(transaction_reference);
CREATE INDEX IF NOT EXISTS idx_crypto_txn_status ON crypto_transactions(status);
CREATE INDEX IF NOT EXISTS idx_crypto_txn_created_at ON crypto_transactions(created_at DESC);

-- Admin-managed list of which assets/fiats are actually enabled for
-- purchase/sale right now. The frontend selector must only ever show
-- what is enabled here — never assume an asset is live just because
-- it appears in app/Config/crypto.php.
CREATE TABLE IF NOT EXISTS crypto_asset_config (
  symbol       text PRIMARY KEY,
  kind         text NOT NULL CHECK (kind IN ('CRYPTO','FIAT')),
  name         text NOT NULL,
  coingecko_id text,
  enabled      boolean NOT NULL DEFAULT false,
  buy_enabled  boolean NOT NULL DEFAULT false,
  sell_enabled boolean NOT NULL DEFAULT false,
  sort_order   int NOT NULL DEFAULT 0,
  updated_at   timestamptz NOT NULL DEFAULT now()
);

-- Seed rows — all disabled by default except KES, until an admin
-- (or the settlement checklist at the bottom of api/crypto-config.php)
-- confirms the provider genuinely supports each one.
INSERT INTO crypto_asset_config (symbol, kind, name, coingecko_id, enabled, buy_enabled, sell_enabled, sort_order) VALUES
  ('KES',  'FIAT',   'Kenyan Shilling', NULL,          true,  true,  true,  0),
  ('USDT', 'CRYPTO', 'Tether',          'tether',      true,  true,  true,  1),
  ('USDC', 'CRYPTO', 'USD Coin',        'usd-coin',    false, false, false, 2),
  ('BTC',  'CRYPTO', 'Bitcoin',         'bitcoin',     false, false, false, 3),
  ('ETH',  'CRYPTO', 'Ethereum',        'ethereum',    false, false, false, 4),
  ('SOL',  'CRYPTO', 'Solana',          'solana',      false, false, false, 5),
  ('XRP',  'CRYPTO', 'XRP',             'ripple',      false, false, false, 6),
  ('DOGE', 'CRYPTO', 'Dogecoin',        'dogecoin',    false, false, false, 7),
  ('TRX',  'CRYPTO', 'TRON',            'tron',        false, false, false, 8),
  ('LTC',  'CRYPTO', 'Litecoin',        'litecoin',    false, false, false, 9)
ON CONFLICT (symbol) DO NOTHING;

CREATE INDEX IF NOT EXISTS idx_crypto_asset_enabled ON crypto_asset_config(enabled);
