-- ============================================================
-- BM Forex Hub — Kotani Pay settlement provider activation
-- Run this AFTER supabase/add-crypto-tables.sql and
-- supabase/add-crypto-wallets-and-networks.sql.
-- Additive only: does not touch payments / subscriptions / profiles.
--
-- CONTEXT: app/Services/KotaniPayService.php + KotaniCryptoCapabilityService
-- are now the live settlement provider used by CryptoSettlementService
-- (BUY = Kotani onramp, SELL = Kotani offramp) whenever KOTANI_API_KEY
-- is configured in .env. This migration just widens the existing
-- dormant `crypto_wallets.provider` check constraint so 'kotani' is a
-- valid value alongside 'kora', and enables the network rows Kotani
-- actually supports for USDT so the admin panel / selector reflect it.
-- ============================================================

-- Widen provider check constraint to allow 'kotani' (and keep 'kora'
-- for historical/manual rows). Postgres requires dropping and
-- recreating a CHECK constraint to change it.
ALTER TABLE crypto_wallets DROP CONSTRAINT IF EXISTS crypto_wallets_provider_check;
ALTER TABLE crypto_wallets ADD CONSTRAINT crypto_wallets_provider_check
  CHECK (provider IN ('kora', 'kotani'));

ALTER TABLE crypto_wallets ALTER COLUMN provider SET DEFAULT 'kotani';

-- Track which settlement provider actually handled each transaction —
-- useful for support/audit once more than one provider has ever been
-- active. Nullable; existing rows are untouched.
ALTER TABLE crypto_transactions ADD COLUMN IF NOT EXISTS settlement_provider text;

-- NOTE ON NETWORKS: only flip a network's `enabled` flag to true here
-- once you've confirmed with Kotani Pay (via your integrator
-- dashboard/Postman collection) which networks your account can
-- actually settle USDT over — the codebase does not assume any
-- network is live by default. As a starting point matching Kotani's
-- documented Celo-based rails:
-- UPDATE crypto_network_config SET enabled = true WHERE symbol = 'USDT' AND network = 'CELO';
-- (Add a 'CELO' row first if it isn't already present:)
INSERT INTO crypto_network_config (symbol, network, enabled, sort_order) VALUES
  ('USDT', 'CELO', false, 3)
ON CONFLICT (symbol, network) DO NOTHING;
