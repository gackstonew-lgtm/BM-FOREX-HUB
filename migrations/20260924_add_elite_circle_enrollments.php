<?php
/**
 * Migration: Create elite_circle_enrollments table
 * 
 * Additive, non-destructive migration to support mandatory BM FOREX HUB 
 * Elite Circle Electronic Enrollment & Terms Acceptance tracking.
 */
require_once __DIR__ . '/../admin/config.php';

try {
    $pdo = getMarketPDO();
    echo "Creating elite_circle_enrollments table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS elite_circle_enrollments (
            id TEXT PRIMARY KEY,
            user_id TEXT NOT NULL,
            member_name TEXT NOT NULL,
            id_passport_number TEXT NOT NULL,
            phone TEXT NOT NULL,
            email TEXT NOT NULL,
            country TEXT NOT NULL,
            investment_amount REAL NOT NULL,
            currency TEXT NOT NULL DEFAULT 'USD',
            payment_reference TEXT,
            investment_start_date TEXT NOT NULL,
            expected_cycle_completion_date TEXT NOT NULL,
            terms_version TEXT NOT NULL DEFAULT '1.0',
            terms_effective_date TEXT NOT NULL DEFAULT '2026-01-05',
            terms_content_hash TEXT NOT NULL,
            accepted INTEGER NOT NULL DEFAULT 1,
            accepted_at TEXT NOT NULL,
            accepted_ip_address TEXT,
            user_agent TEXT,
            email_status TEXT DEFAULT 'pending',
            email_sent_at TEXT,
            email_error TEXT,
            notes TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        );

        CREATE INDEX IF NOT EXISTS idx_elite_enrollments_user_id ON elite_circle_enrollments(user_id);
        CREATE INDEX IF NOT EXISTS idx_elite_enrollments_email ON elite_circle_enrollments(email);
        CREATE INDEX IF NOT EXISTS idx_elite_enrollments_terms ON elite_circle_enrollments(user_id, terms_version);
    ");

    echo "[SUCCESS] Migration elite_circle_enrollments applied successfully.\n";
} catch (Exception $e) {
    echo "[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
