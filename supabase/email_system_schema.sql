-- ============================================================
-- BM Forex Hub — Email Notification System Database Schema
-- Compatible with PostgreSQL / Supabase & MySQL / SQLite (PDO)
-- ============================================================

-- 1. Email Queue Table
CREATE TABLE IF NOT EXISTS email_queue (
    id VARCHAR(64) PRIMARY KEY,
    campaign_id VARCHAR(64) NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    recipient_name VARCHAR(255) DEFAULT '',
    subject VARCHAR(500) NOT NULL,
    body TEXT NOT NULL,
    status VARCHAR(32) DEFAULT 'queued', -- queued, processing, completed, failed, cancelled
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    error_message TEXT DEFAULT NULL,
    scheduled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_eq_status_sched ON email_queue(status, scheduled_at);
CREATE INDEX IF NOT EXISTS idx_eq_campaign ON email_queue(campaign_id);

-- 2. Email History / Campaigns Table
CREATE TABLE IF NOT EXISTS email_history (
    id VARCHAR(64) PRIMARY KEY,
    admin_id VARCHAR(255) DEFAULT 'admin',
    subject VARCHAR(500) NOT NULL,
    message TEXT NOT NULL,
    recipient_group VARCHAR(64) NOT NULL, -- all, verified, unverified, active, inactive, selected
    selected_users TEXT DEFAULT NULL, -- JSON array of user IDs
    total_recipients INT DEFAULT 0,
    sent_count INT DEFAULT 0,
    failed_count INT DEFAULT 0,
    status VARCHAR(32) DEFAULT 'queued', -- queued, processing, completed, failed, cancelled
    processing_time INT DEFAULT 0, -- in seconds
    scheduled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_eh_status ON email_history(status);
CREATE INDEX IF NOT EXISTS idx_eh_created ON email_history(created_at);

-- 3. Email Drafts Table
CREATE TABLE IF NOT EXISTS email_drafts (
    id VARCHAR(64) PRIMARY KEY,
    admin_id VARCHAR(255) DEFAULT 'admin',
    subject VARCHAR(500) DEFAULT '',
    message TEXT DEFAULT '',
    recipient_group VARCHAR(64) DEFAULT 'all',
    selected_users TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 4. Email Activity Logs Table
CREATE TABLE IF NOT EXISTS email_logs (
    id VARCHAR(64) PRIMARY KEY,
    campaign_id VARCHAR(64) DEFAULT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    event VARCHAR(64) NOT NULL, -- queued, sent, failed, unsubscribed, otp_sent
    status VARCHAR(32) NOT NULL,
    provider VARCHAR(64) DEFAULT 'smtp',
    response TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_el_email ON email_logs(recipient_email);

-- 5. OTP Records & Rate Limits Table
CREATE TABLE IF NOT EXISTS otp_records (
    id VARCHAR(64) PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    type VARCHAR(32) DEFAULT 'signup', -- signup, password_reset
    expires_at DATETIME NOT NULL,
    attempts INT DEFAULT 0,
    resend_count INT DEFAULT 1,
    last_sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_otp_email_type ON otp_records(email, type);

-- 6. Unsubscribe Tokens Table
CREATE TABLE IF NOT EXISTS unsubscribe_tokens (
    id VARCHAR(64) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    token VARCHAR(255) NOT NULL,
    unsubscribed_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_unsub_email ON unsubscribe_tokens(email);
