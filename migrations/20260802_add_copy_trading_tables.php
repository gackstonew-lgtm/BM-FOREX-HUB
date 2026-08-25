<?php
/**
 * Migration: Create copy_traders and copy_trader_audit_logs tables
 */
require_once __DIR__ . '/../admin/config.php';

try {
    $pdo = getMarketPDO();
    echo "Creating copy_traders table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS copy_traders (
            id TEXT PRIMARY KEY,
            user_id TEXT,
            full_name TEXT,
            email TEXT,
            subscription_plan TEXT DEFAULT 'copytrading',
            payment_reference TEXT,
            broker_name TEXT,
            mt5_login TEXT,
            mt5_password TEXT,
            mt5_server TEXT,
            notes TEXT,
            status TEXT DEFAULT 'Pending',
            created_at TEXT,
            updated_at TEXT
        );
    ");

    echo "Creating copy_trader_audit_logs table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS copy_trader_audit_logs (
            id TEXT PRIMARY KEY,
            admin_user TEXT,
            copy_trader_id TEXT,
            action TEXT,
            ip_address TEXT,
            details TEXT,
            created_at TEXT
        );
    ");

    echo "[SUCCESS] Migration applied successfully.\n";
} catch (Exception $e) {
    echo "[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
