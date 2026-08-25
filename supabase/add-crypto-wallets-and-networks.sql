-- ============================================================
-- BM Forex Hub — Crypto wallet + network readiness schema
-- Run this AFTER supabase/add-crypto-tables.sql.
-- Additive only: does not touch payments / subscriptions / profiles
-- / crypto_transactions rows, and does not enable anything by itself.
--
-- IMPORTANT CONTEXT (read before enabling anything here):
-- As of this migration, Kora's publicly documented API does not
-- expose a customer stablecoin wallet, crypto deposit, or crypto
-- withdrawal endpoint (see app/Services/KoraCryptoCapabilityService.php,
-- which is the live source of truth). This schema exists so the
-- database is ready the moment that changes — either via Kora or a
-- separate settlement provider — without a second migration. Every
-- table/column here is dormant until KoraCryptoCapabilityService
-- reports the matching capability as true.
-- ============================================================

-- Per-user, per-asset, per-network wallet record. One row per
-- (user_id, currency, network) — enforced below so a duplicate
-- "create wallet" click can never produce two rows.
CREATE TABLE IF NOT EXISTS crypto_wallets (
  id                uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id           uuid NOT NULL,
  currency          text NOT NULL,                -- e.g. 'USDT'
  network           text NOT NULL,                -- e.g. 'TRX' | 'ETH' | 'SOL'
  wallet_reference  text,                          -- our idempotency key sent to the provider
  wallet_address    text,                          -- provider-returned deposit/receive address
  provider          text NOT NULL DEFAULT 'kora',  -- which settlement provider issued this
  provider_reference text,                         -- provider's own wallet/account id
  status            text NOT NULL DEFAULT 'PENDING'
                      CHECK (status IN ('PENDING','ACTIVE','DISABLED')),
  created_at        timestamptz NOT NULL DEFAULT now(),
  updated_at        timestamptz NOT NULL DEFAULT now(),
  UNIQUE (user_id, currency, network)
);

CREATE INDEX IF NOT EXISTS idx_crypto_wallets_user ON crypto_wallets(user_id);
CREATE INDEX IF NOT EXISTS idx_crypto_wallets_lookup ON crypto_wallets(user_id, currency, network);

-- Which network(s) each crypto asset supports, and whether each is
-- actually enabled. Seeded with the networks named in planning docs
-- (USDT / TRX, ETH, SOL) but every row starts disabled — an admin (or
-- a future capability-check script) must flip `enabled` only after
-- KoraCryptoCapabilityService (or its replacement) confirms the
-- network is genuinely usable.
CREATE TABLE IF NOT EXISTS crypto_network_config (
  symbol      text NOT NULL,          -- e.g. 'USDT'
  network     text NOT NULL,          -- e.g. 'TRX'
  enabled     boolean NOT NULL DEFAULT false,
  min_amount  numeric(28,10),         -- optional per-network minimum, set by admin
  sort_order  int NOT NULL DEFAULT 0,
  updated_at  timestamptz NOT NULL DEFAULT now(),
  PRIMARY KEY (symbol, network)
);

INSERT INTO crypto_network_config (symbol, network, enabled, sort_order) VALUES
  ('USDT', 'TRX', false, 0),
  ('USDT', 'ETH', false, 1),
  ('USDT', 'SOL', false, 2)
ON CONFLICT (symbol, network) DO NOTHING;

-- crypto_transactions needs a network column so a transaction can
-- record which network was used once wallets/networks go live.
-- Nullable — existing rows and today's network-less flow are
-- untouched.
ALTER TABLE crypto_transactions ADD COLUMN IF NOT EXISTS network text;
ALTER TABLE crypto_transactions ADD COLUMN IF NOT EXISTS wallet_address text;
ALTER TABLE crypto_transactions ADD COLUMN IF NOT EXISTS destination_address text;
ALTER TABLE crypto_transactions ADD COLUMN IF NOT EXISTS blockchain_tx_hash text;

CREATE INDEX IF NOT EXISTS idx_crypto_txn_network ON crypto_transactions(network) WHERE network IS NOT NULL;
