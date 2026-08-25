-- Add trial_started_at column to profiles
-- The actual value is set on first login after the update (see subscription-status.php)

ALTER TABLE profiles ADD COLUMN IF NOT EXISTS trial_started_at timestamptz;
