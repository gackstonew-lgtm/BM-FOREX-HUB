-- Add Kora Pay columns to payments table in Supabase
-- Run this script in the Supabase SQL Editor if these optional columns are missing

ALTER TABLE payments ADD COLUMN IF NOT EXISTS kora_txn_id text;
ALTER TABLE payments ADD COLUMN IF NOT EXISTS kora_reference text;
ALTER TABLE payments ADD COLUMN IF NOT EXISTS failure_reason text;
ALTER TABLE payments ADD COLUMN IF NOT EXISTS raw_webhook jsonb;

CREATE INDEX IF NOT EXISTS idx_payments_merchant_txn_id ON payments(merchant_txn_id);
CREATE INDEX IF NOT EXISTS idx_payments_kora_txn_id ON payments(kora_txn_id);
