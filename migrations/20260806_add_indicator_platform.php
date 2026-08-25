<?php
/**
 * Migration: 20260806_add_indicator_platform
 * Creates indicator platform tables in local SQLite fallback database.
 *
 * Usage: php migrations/20260806_add_indicator_platform.php
 */

require_once __DIR__ . '/../admin/config.php';

echo "=== BM Forex Hub — Indicator Platform Migration ===\n";
echo "Target: SQLite fallback database\n\n";

try {
    $pdo = getMarketPDO();

    // ── indicator_plans ──────────────────────────────────────
    echo "Creating indicator_plans table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS indicator_plans (
            id              TEXT PRIMARY KEY DEFAULT (lower(hex(randomblob(16)))),
            plan_key        TEXT UNIQUE NOT NULL,
            plan_name       TEXT NOT NULL,
            description     TEXT,
            price_usd       REAL NOT NULL,
            price_kes       REAL,
            duration_days   INTEGER NOT NULL DEFAULT 30,
            features        TEXT DEFAULT '[]',
            indicator_access   INTEGER DEFAULT 1,
            signals_access     INTEGER DEFAULT 0,
            ai_access          INTEGER DEFAULT 0,
            premium_dashboard  INTEGER DEFAULT 0,
            is_active          INTEGER DEFAULT 1,
            sort_order         INTEGER DEFAULT 0,
            created_at      TEXT DEFAULT (datetime('now'))
        )
    ");

    // Seed plans
    $plans = [
        [
            'indicator_silver', 'Silver',
            'BM Quantum Edge — your edge in every session',
            25.00, 3250.00, 30,
            '["BM Quantum Edge Advanced Multi-Engine Indicator","XAUUSD, EURUSD, GBPUSD, USDJPY, BTCUSD, NAS100, US30","HTF Trend Filter","Market Structure Detection","Supply & Demand Zones","Smart Entry Signals","Position Management (SL/TP)","Signal Strength Score","Trading Sessions Overlay","24/7 Dashboard Access"]',
            1, 0, 0, 0, 1
        ],
        [
            'indicator_gold', 'Gold',
            'Indicator + live curated trade signals from our analysts',
            45.00, 5850.00, 30,
            '["Everything in Silver","Live Trade Signals","BUY/SELL Alerts","Risk-to-Reward Levels","TP1 / TP2 / TP3 Targets","Market Bias Updates","Session Alerts","Priority Support"]',
            1, 1, 0, 0, 2
        ],
        [
            'indicator_vip', 'VIP',
            'Full premium suite — indicator, signals, AI and advanced dashboard',
            75.00, 9750.00, 30,
            '["Everything in Gold","BM Forex AI Assistant","Advanced Signal Dashboard","Multi-Symbol Watchlist","Session Heatmap","Trade Journal","Performance Analytics","VIP WhatsApp Group","Dedicated Account Manager"]',
            1, 1, 1, 1, 3
        ],
    ];

    $stmt = $pdo->prepare("
        INSERT OR IGNORE INTO indicator_plans
            (plan_key, plan_name, description, price_usd, price_kes, duration_days, features, indicator_access, signals_access, ai_access, premium_dashboard, sort_order)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($plans as $p) {
        $stmt->execute([$p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8], $p[9], $p[10], $p[11]]);
    }
    echo "  ✓ Seeded 3 indicator plans\n";

    // ── indicator_subscriptions ──────────────────────────────
    echo "Creating indicator_subscriptions table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS indicator_subscriptions (
            id                   TEXT PRIMARY KEY DEFAULT (lower(hex(randomblob(16)))),
            user_id              TEXT,
            user_email           TEXT,
            user_name            TEXT,
            plan_key             TEXT NOT NULL,
            plan_name            TEXT,
            status               TEXT DEFAULT 'pending',
            indicator_access     INTEGER DEFAULT 0,
            signals_access       INTEGER DEFAULT 0,
            ai_access            INTEGER DEFAULT 0,
            premium_dashboard    INTEGER DEFAULT 0,
            tradingview_username TEXT,
            tv_username_verified INTEGER DEFAULT 0,
            payment_reference    TEXT,
            payment_provider     TEXT DEFAULT 'korapay',
            amount_paid_usd      REAL,
            amount_paid_kes      REAL,
            started_at           TEXT,
            expires_at           TEXT,
            last_sync            TEXT,
            admin_notes          TEXT,
            granted_by           TEXT,
            created_at           TEXT DEFAULT (datetime('now')),
            updated_at           TEXT DEFAULT (datetime('now'))
        )
    ");

    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_indsub_user_id    ON indicator_subscriptions(user_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_indsub_status     ON indicator_subscriptions(status)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_indsub_expires_at ON indicator_subscriptions(expires_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_indsub_plan_key   ON indicator_subscriptions(plan_key)");

    // ── indicator_access_log ─────────────────────────────────
    echo "Creating indicator_access_log table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS indicator_access_log (
            id          TEXT PRIMARY KEY DEFAULT (lower(hex(randomblob(16)))),
            user_id     TEXT,
            user_email  TEXT,
            action      TEXT NOT NULL,
            plan_key    TEXT,
            ip_address  TEXT,
            user_agent  TEXT,
            details     TEXT,
            created_at  TEXT DEFAULT (datetime('now'))
        )
    ");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_indlog_user_id    ON indicator_access_log(user_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_indlog_created_at ON indicator_access_log(created_at)");

    // ── indicator_admin_log ──────────────────────────────────
    echo "Creating indicator_admin_log table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS indicator_admin_log (
            id              TEXT PRIMARY KEY DEFAULT (lower(hex(randomblob(16)))),
            admin_email     TEXT NOT NULL,
            admin_ip        TEXT,
            target_user_id  TEXT,
            target_email    TEXT,
            action          TEXT NOT NULL,
            subscription_id TEXT,
            details         TEXT,
            created_at      TEXT DEFAULT (datetime('now'))
        )
    ");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_adm_log_created_at ON indicator_admin_log(created_at)");

    echo "\n[SUCCESS] All indicator platform tables created and seeded.\n";
    echo "Run the Supabase SQL migration (migrations/20260806_add_indicator_platform.sql) in your Supabase project.\n";

} catch (Exception $e) {
    echo "[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
