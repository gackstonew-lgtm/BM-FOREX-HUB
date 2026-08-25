-- Run this BEFORE pushing the dashboard update.
-- Resets all trials so every user gets a fresh 3-day trial on their next login.
UPDATE profiles SET trial_started_at = NULL;
