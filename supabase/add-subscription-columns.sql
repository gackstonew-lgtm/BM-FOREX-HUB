-- Add columns for admin-granted subscriptions
-- Run this in Supabase SQL Editor if these columns don't exist

ALTER TABLE subscriptions ADD COLUMN IF NOT EXISTS granted_by text;
ALTER TABLE subscriptions ADD COLUMN IF NOT EXISTS amount_kes numeric;
ALTER TABLE subscriptions ADD COLUMN IF NOT EXISTS notes text;
ALTER TABLE subscriptions ADD COLUMN IF NOT EXISTS username text;
ALTER TABLE subscriptions ADD COLUMN IF NOT EXISTS plan_key text;
ALTER TABLE subscriptions ADD COLUMN IF NOT EXISTS plan_name text;
