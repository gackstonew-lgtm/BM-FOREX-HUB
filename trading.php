<?php
/**
 * BM Forex Hub — Premium Trading Dashboard
 * File: trading.php
 *
 * Gated area for subscribed indicator members only.
 * Embeds TradingView chart widget and displays server-side computed signal analytics.
 */
require_once __DIR__ . '/engine_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="css/theme-light.css?v=1">
<script src="js/theme.js" defer></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require_once __DIR__ . '/components/pwa_meta.php'; ?>
<title>BM FOREX HUB | Premium Trading Dashboard</title>
<meta name="description" content="Access our premium multi-engine TradingView indicator dashboard with real-time signals, structural filters, and AI assistant.">
<link rel="icon" type="image/png" href="BM-ForexHub-Logo-Circle.png">
<meta name="robots" content="noindex, nofollow, noarchive">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="basics.css?v=<?= filemtime('basics.css') ?>">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="css/mobile.css?v=<?= filemtime('css/mobile.css') ?>">
<link rel="stylesheet" href="css/ai-assistant.css?v=<?= filemtime('css/ai-assistant.css') ?>">
<link rel="stylesheet" href="css/trading-dashboard.css?v=<?= filemtime('css/trading-dashboard.css') ?>">

<style>
/* Sidebar specific adjustment to match index.php */
.dash-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 200px;
  height: 100vh;
  background: #101722;
  border-right: 1px solid #1e2d42;
  display: flex;
  flex-direction: column;
  z-index: 200;
  overflow-y: auto;
  overflow-x: hidden;
}
.dash-sidebar__brand {
  padding: 18px 16px 16px;
  border-bottom: 1px solid #1e2d42;
  flex-shrink: 0;
}
.dash-sidebar__logo {
  display: flex;
  align-items: center;
  gap: 9px;
  text-decoration: none;
}
.dash-sidebar__logo img {
  width: 34px !important;
  height: 34px !important;
  max-height: 40px !important;
  border-radius: 50%;
  object-fit: contain !important;
  flex-shrink: 0;
  border: 1px solid rgba(22,119,255,.5);
  background: #fff;
}
.dash-sidebar__logo span {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  font-size: 0.78rem;
  letter-spacing: 0.08em;
  color: #fff;
  line-height: 1.2;
}
.dash-sidebar__nav {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 10px 0;
}
.dash-sidebar__item {
  display: flex;
  align-items: center;
  gap: 11px;
  padding: 10px 16px;
  font-size: 0.83rem;
  font-weight: 500;
  color: #8fa3b8;
  text-decoration: none;
  border-radius: 0;
  transition: background .15s, color .15s;
  cursor: pointer;
  border: none;
  background: transparent;
  width: 100%;
  text-align: left;
  font-family: 'Inter', sans-serif;
}
.dash-sidebar__item svg {
  flex-shrink: 0;
  opacity: 0.75;
  transition: opacity .15s;
}
.dash-sidebar__item:hover {
  background: rgba(22,119,255,.1);
  color: #fff;
}
.dash-sidebar__item:hover svg { opacity: 1; }
.dash-sidebar__item.active {
  background: #1677FF;
  color: #fff;
  border-radius: 6px;
  margin: 0 8px;
  padding: 10px 10px;
  width: calc(100% - 16px);
}
.dash-sidebar__item.active svg { opacity: 1; }
.dash-sidebar__bottom {
  padding: 10px 0 16px;
  border-top: 1px solid #1e2d42;
  flex-shrink: 0;
}
.dash-sidebar__logout {
  color: #f6465d !important;
}
.dash-sidebar__logout svg {
  color: #f6465d;
}
.dash-sidebar__logout:hover {
  background: rgba(246,70,93,.1) !important;
  color: #f6465d !important;
}

@media (min-width: 900px) {
  body.has-sidebar .dash-main-wrap {
    margin-left: 200px;
  }
}
@media (max-width: 899px) {
  .dash-sidebar {
    display: none;
  }
  body.has-sidebar .dash-main-wrap {
    margin-left: 0;
  }
}

.topbar { background: #101722; border-bottom: 1px solid #1e2d42; }
.topbar .logo--text span { font-size: 0.85rem; font-weight: 700; letter-spacing: 0.06em; color: #fff; }
.topbar__user-row {
  display: flex; align-items: center; gap: 10px;
  padding-top: 0; margin-top: 0; border-top: none;
  color: #fff; font-size: 0.83rem;
}
.topbar__user-row .topbar__user-avatar {
  width: 34px; height: 34px; background: #1e2d42; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  border: 1px solid rgba(22,119,255,0.35); flex-shrink: 0;
  font-size: 0.95rem; color: #8fa3b8; overflow: hidden;
}
.topbar__user-row .topbar__user-greeting { line-height: 1.2; }
.topbar__user-row .topbar__user-greeting small { display: block; color: #8fa3b8; font-size: 0.7rem; }
.topbar__user-row .topbar__user-greeting strong { font-weight: 600; }
.topbar__premium-badge {
  background: rgba(240,180,41,.15); color: var(--gold,#F0B429);
  border: 1px solid rgba(240,180,41,.35); border-radius: 5px;
  font-size: 0.62rem; font-weight: 700; letter-spacing: .03em;
  text-transform: uppercase; padding: 1px 6px; margin-left: 4px;
}

.tv-sync-form {
  display: flex; gap: 8px; margin-top: 10px;
}
.tv-sync-input {
  flex: 1; padding: 7px 10px; background: #101722; border: 1px solid #1e2d42;
  border-radius: 6px; color: #fff; font-size: 0.75rem; outline: none;
}
.tv-sync-input:focus { border-color: #1677FF; }
.tv-sync-btn {
  padding: 7px 14px; background: #1677FF; border: none; border-radius: 6px;
  color: #fff; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: background .15s;
}
.tv-sync-btn:hover { background: #1565e0; }

.status-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; padding: 3px 7px; border-radius: 4px; }
.status-label.verified { background: rgba(34,197,94,.15); color: #22c55e; border: 1px solid rgba(34,197,94,.3); }
.status-label.pending { background: rgba(240,180,41,.15); color: #f0b429; border: 1px solid rgba(240,180,41,.3); }
.status-label.none { background: rgba(143,163,184,.15); color: #8fa3b8; border: 1px solid rgba(143,163,184,.3); }
</style>
<script>var API_BASE = '<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>';</script>
</head>
<body id="top" class="has-sidebar">

<!-- ── Branded Loading Screen (Appears instantly) ───────────────────── -->
<div class="td-loading" id="tdLoader">
  <div class="td-loading__brand">
    <img src="BM-ForexHub-Logo-Circle.png" alt="BM Forex Hub Logo" class="td-loading__logo">
    <span class="td-loading__title">BM FOREX HUB</span>
    <span class="td-loading__sub">Loading Trading Workspace...</span>
  </div>
  <div class="td-spinner" role="progressbar" aria-label="Loading trading workspace"></div>
</div>

<!-- ===== Dashboard Sidebar ===== -->
<?php $activeNav = 'tools'; include __DIR__ . '/components/global_sidebar.php'; ?>

<div class="dash-main-wrap">
<!-- Unified Global Header -->
<?php include __DIR__ . '/components/global_header.php'; ?>

  <!-- ── Main Dashboard Workspace Wrap ────────────────────────────────────────── -->
  <div class="td-container-wrap">
    
    <!-- Hero Bar: Symbol and interval selection -->
    <header class="td-hero" aria-label="Symbol and interval select">
      <div class="td-hero__left">
        <div class="td-symbol-select" role="group" aria-label="Symbols">
          <button class="td-sym-btn active" data-sym="XAUUSD" aria-pressed="true">GOLD (XAUUSD)</button>
          <button class="td-sym-btn" data-sym="EURUSD" aria-pressed="false">EURUSD</button>
          <button class="td-sym-btn" data-sym="GBPUSD" aria-pressed="false">GBPUSD</button>
          <button class="td-sym-btn" data-sym="USDJPY" aria-pressed="false">USDJPY</button>
          <button class="td-sym-btn" data-sym="BTCUSD" aria-pressed="false">BTCUSD</button>
          <button class="td-sym-btn" data-sym="NAS100" aria-pressed="false">NAS100</button>
          <button class="td-sym-btn" data-sym="US30" aria-pressed="false">US30</button>
        </div>

        <div class="td-divider" role="presentation"></div>

        <div class="td-tf-group" role="group" aria-label="Timeframes">
          <button class="td-tf-btn" data-tf="5" aria-pressed="false">5M</button>
          <button class="td-tf-btn" data-tf="15" aria-pressed="false">15M</button>
          <button class="td-tf-btn" data-tf="30" aria-pressed="false">30M</button>
          <button class="td-tf-btn active" data-tf="60" aria-pressed="true">1H</button>
          <button class="td-tf-btn" data-tf="240" aria-pressed="false">4H</button>
          <button class="td-tf-btn" data-tf="D" aria-pressed="false">1D</button>
        </div>
      </div>

      <div class="td-hero__right">
        <span class="td-plan-badge td-plan-badge--silver" id="planBadge">Loading Plan</span>
        <span class="td-expiry-badge" id="expiryBadge">EXPIRES: Loading</span>
      </div>
    </header>

    <!-- Main Responsive Grid (75% Chart / 25% Signal Panel Desktop) -->
    <main class="td-main">
      <!-- 75% Live TradingView Chart Column -->
      <section class="td-chart-col" aria-label="Live TradingView Chart">
        <div id="tvChartContainer" role="application" aria-label="TradingView Widget"></div>
      </section>

      <!-- 25% Signal Analytics Right Panel -->
      <aside class="td-signals-col" aria-label="Market indicators and trade signals">
        
        <!-- Trend Direction -->
        <article class="td-signal-section">
          <div class="td-signal-header">
            <h2 class="td-signal-title">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1677FF" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
              Trend Filter
            </h2>
            <div class="td-refresh-label" id="refreshClock">Connecting...</div>
          </div>
          <div class="td-direction-badge neutral" id="dirBadge">NEUTRAL</div>
        </article>

        <!-- Current Signal & Strength -->
        <article class="td-signal-section">
          <div class="td-signal-header">
            <h2 class="td-signal-title">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
              Proprietary Signal
            </h2>
          </div>
          <div class="td-signal-pill hold" id="sigPill">
            <span>ACTION</span>
            <span class="td-sig-val">HOLD</span>
          </div>
          
          <div class="td-strength-wrap">
            <div class="td-strength-label">
              <span>Signal Confidence</span>
              <strong id="strengthVal">50%</strong>
            </div>
            <div class="td-strength-bar">
              <div class="td-strength-fill" id="strengthFill" style="width: 50%"></div>
            </div>
          </div>
        </article>

        <!-- Dynamic Calculated Key Levels -->
        <article class="td-signal-section">
          <div class="td-signal-header">
            <h2 class="td-signal-title">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F0B429" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
              Key Levels (<span id="lblSymbol">XAUUSD</span>)
            </h2>
          </div>
          <div class="td-level-row">
            <span class="td-level-label">Live Price</span>
            <span class="td-level-value" id="valPrice">0.00</span>
          </div>
          <div class="td-level-row">
            <span class="td-level-label">Smart Entry Zone</span>
            <span class="td-level-value entry" id="valEntry">0.00</span>
          </div>
          <div class="td-level-row">
            <span class="td-level-label">Calculated SL</span>
            <span class="td-level-value sl" id="valSL">0.00</span>
          </div>
          <div class="td-level-row">
            <span class="td-level-label">Take Profit (TP2)</span>
            <span class="td-level-value tp" id="valTP">0.00</span>
          </div>
          <div class="td-level-row">
            <span class="td-level-label">Risk-to-Reward</span>
            <span class="td-level-value rr" id="valRR">1:2</span>
          </div>
          <div class="td-level-row">
            <span class="td-level-label">EMA Filter (200)</span>
            <span class="td-level-value" id="valEma200">0.00</span>
          </div>
          <div class="td-level-row">
            <span class="td-level-label">EMA Structure (50)</span>
            <span class="td-level-value" id="valEma50">0.00</span>
          </div>
        </article>

        <!-- Momentum Index -->
        <article class="td-signal-section">
          <div class="td-signal-header">
            <h2 class="td-signal-title">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#8B5CF6" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              Momentum Index
            </h2>
          </div>
          <div class="td-bias-card" id="valBias">Ranging</div>
          <div class="td-rsi-wrap">
            <div style="display:flex; justify-content:space-between; font-size: 0.7rem; color: #8fa3b8; margin-top: 4px;">
              <span id="rsiVal">RSI(14): 50</span>
            </div>
            <div class="td-rsi-track">
              <div class="td-rsi-pointer" id="rsiPointer" style="left: 50%;"></div>
            </div>
          </div>
        </article>

        <!-- Session Overlays -->
        <article class="td-signal-section">
          <div class="td-signal-header">
            <h2 class="td-signal-title">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1677FF" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
              Active Sessions
            </h2>
          </div>
          <div class="td-sessions">
            <div class="td-session-badge" id="sess_sydney"><span class="td-session-dot"></span>Sydney</div>
            <div class="td-session-badge" id="sess_tokyo"><span class="td-session-dot"></span>Tokyo</div>
            <div class="td-session-badge" id="sess_london"><span class="td-session-dot"></span>London</div>
            <div class="td-session-badge" id="sess_new_york"><span class="td-session-dot"></span>New York</div>
          </div>
        </article>

        <!-- Live Watchlist -->
        <article class="td-signal-section">
          <div class="td-signal-header">
            <h2 class="td-signal-title">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
              Symbol Watchlist
            </h2>
          </div>
          <div class="td-watchlist" id="watchlistContainer"></div>
        </article>

        <!-- TradingView Integration Sync -->
        <article class="td-signal-section">
          <div class="td-signal-header">
            <h2 class="td-signal-title">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1677FF" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
              Pine Script Access
            </h2>
            <span class="status-label none" id="tvStatusLabel">Not Linked</span>
          </div>
          <form class="tv-sync-form" id="tvUsernameForm" action="#" method="POST">
            <input type="text" class="tv-sync-input" id="tvUsernameInput" placeholder="TradingView Username" autocomplete="off" aria-label="TradingView Username">
            <button type="submit" class="tv-sync-btn" id="tvUsernameBtn">Link</button>
          </form>
        </article>

        <!-- Subscription Management -->
        <article class="td-signal-section">
          <div class="td-signal-header">
            <h2 class="td-signal-title">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F0B429" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              Subscription
            </h2>
          </div>
          <div class="td-sub-status" id="subStatusCard">
            <div class="td-sub-status-row">
              <span class="td-sub-status-label">Status</span>
              <span class="td-sub-status-val ok" id="subStatusVal">Active Access</span>
            </div>
            <a href="indicator-subscribe.php" class="td-renew-btn">Manage Subscription</a>
          </div>
        </article>

      </aside>
    </main>

    <!-- ── Admin Only: BM Quantum Edge Subscriber Management Section ── -->
    <section class="td-admin-subscribers-section" id="adminIndicatorSection" style="display:none;" aria-label="Administrator Subscriber Management">
      <div class="td-admin-card">
        <div class="td-admin-header">
          <div class="td-admin-header__left">
            <div class="td-admin-header__icon">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
              <h2 class="td-admin-title">BM Quantum Edge Subscribers</h2>
              <p class="td-admin-subtitle">Administrator live subscriber management, TradingView access status &amp; package monitoring</p>
            </div>
          </div>
          <div class="td-admin-header__right">
            <span class="td-admin-badge">Admin View</span>
            <button type="button" class="btn-ind-refresh" id="indRefreshBtn" title="Refresh subscribers">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
              <span>Refresh</span>
            </button>
          </div>
        </div>

        <!-- Summary KPI Stats -->
        <div class="td-admin-stats-grid">
          <div class="td-admin-stat-card">
            <span class="td-admin-stat-label">Total Subscribers</span>
            <strong class="td-admin-stat-val" id="indTotalSubscribers">--</strong>
          </div>
          <div class="td-admin-stat-card">
            <span class="td-admin-stat-label">Active Subscribers</span>
            <strong class="td-admin-stat-val val-green" id="indActiveSubscribers">--</strong>
          </div>
          <div class="td-admin-stat-card">
            <span class="td-admin-stat-label">Expired / Pending</span>
            <strong class="td-admin-stat-val val-gold" id="indExpiredSubscribers">--</strong>
          </div>
          <div class="td-admin-stat-card">
            <span class="td-admin-stat-label">Monthly Indicator Volume</span>
            <strong class="td-admin-stat-val val-blue" id="indTotalRevenue">--</strong>
          </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="td-admin-toolbar">
          <div class="td-admin-search">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="indSearchInput" placeholder="Search by username, email, or TradingView..." autocomplete="off">
          </div>
          <div class="td-admin-filters">
            <select id="indPlanFilter" class="td-admin-select" aria-label="Filter by plan">
              <option value="">All Plans</option>
              <option value="indicator_silver">Silver ($25 USD)</option>
              <option value="indicator_gold">Gold ($45 USD)</option>
              <option value="indicator_vip">VIP ($75 USD)</option>
            </select>
            <select id="indStatusFilter" class="td-admin-select" aria-label="Filter by status">
              <option value="">All Statuses</option>
              <option value="active">Active</option>
              <option value="expired">Expired</option>
              <option value="pending">Pending</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>

        <!-- Table Container -->
        <div class="td-admin-table-wrap">
          <table class="td-admin-table">
            <thead>
              <tr>
                <th>Subscriber</th>
                <th>Plan Package</th>
                <th>Paid Amount</th>
                <th>TradingView User</th>
                <th>Status</th>
                <th>Subscribed Date</th>
                <th>Expiry Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="indSubscribersTbody">
              <tr><td colspan="8" class="td-table-empty">Loading subscribers...</td></tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Controls -->
        <div class="td-admin-pagination" id="indPaginationBar">
          <div class="td-pagination-count" id="indShowingCount">Showing 0 subscribers</div>
          <div class="td-pagination-btns">
            <button type="button" class="td-page-btn" id="indPrevPageBtn" disabled>&laquo; Prev</button>
            <span class="td-page-indicator" id="indPageIndicator">Page 1</span>
            <button type="button" class="td-page-btn" id="indNextPageBtn" disabled>Next &raquo;</button>
          </div>
        </div>
      </div>
    </section>

  </div>
</div>

<!-- ── Locked / Subscription Denied Overlay ───────────────────────────── -->
<div class="td-locked-overlay" id="tdLockedOverlay" style="display: none;">
  <div class="td-locked-card">
    <div class="td-locked-icon">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    </div>
    <h1 class="td-locked-title">Premium Access Required</h1>
    <p class="td-locked-subtitle">The TradingView Premium Dashboard is reserved for members with active BM Quantum Edge indicator access. Get instant lifetime access with a single one-time payment.</p>
    
    <div class="td-locked-plans" style="grid-template-columns: 1fr; max-width: 380px; margin: 0 auto 20px;">
      <div class="td-locked-plan vip active" onclick="window.location.href='indicator-subscribe.php?plan=indicator_quantum_edge'" style="border-color: #1677FF; background: rgba(22, 119, 255, 0.1);">
        <div class="td-locked-plan__name" style="font-size: 1.05rem; font-weight: 700;">BM Quantum Edge</div>
        <div class="td-locked-plan__price" style="font-size: 1.6rem; font-weight: 700; color: #fff; margin: 4px 0;">$299</div>
        <div style="font-size: 0.72rem; color: #16C784; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">One-Time Payment &bull; Lifetime Access</div>
      </div>
    </div>

    <a href="indicator-subscribe.php?plan=indicator_quantum_edge" class="td-locked-cta">Get Access & Unlock Now ($299)</a>
  </div>
</div>

<!-- Scripts -->
<script src="https://iwoytmcxmbhmmbrbpzvf.supabase.co/auth/v1/pages/provider/helper"></script>
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script src="js/auth.js?v=<?= filemtime('js/auth.js') ?>"></script>
<script src="https://s3.tradingview.com/tv.js" defer></script>
<script src="js/trading-dashboard.js?v=<?= filemtime('js/trading-dashboard.js') ?>" defer></script>

<script>
/* Sidebar and notifications controller */
(function() {
  fetch('https://iwoytmcxmbhmmbrbpzvf.supabase.co/rest/v1/announcements?active=eq.true&order=created_at.desc', {
    headers: { 'apikey': BMAuth.getClient().supabaseKey }
  })
  .then(r => r.json())
  .then(items => {
    var bellBadge = document.getElementById('bellBadge');
    var bellList = document.getElementById('bellList');
    if (!items || !items.length) {
      if (bellList) bellList.innerHTML = '<div class="bell-empty">No announcements yet.</div>';
      return;
    }
    if (bellBadge) {
      bellBadge.textContent = items.length;
      bellBadge.hidden = false;
    }
    if (bellList) {
      bellList.innerHTML = items.map(a => {
        var d = a.created_at ? new Date(a.created_at).toLocaleDateString() : '';
        return `<div class="bell-item">
          <div class="bell-item__title">${escapeHtml(a.title)}</div>
          <div class="bell-item__msg">${escapeHtml(a.message)}</div>
          ${d ? `<div class="bell-item__date">${d}</div>` : ''}
        </div>`;
      }).join('');
    }
  });

  BMAuth.getUser().then(function(user) {
    if (user) {
      document.getElementById('displayUsername').textContent = BMAuth.displayName(user);
    }
  });

  var bellBtn = document.getElementById('bellBtn');
  var bellDropdown = document.getElementById('bellDropdown');
  if (bellBtn && bellDropdown) {
    bellBtn.onclick = function(e) {
      e.stopPropagation();
      bellDropdown.hidden = !bellDropdown.hidden;
    };
    document.onclick = function() {
      bellDropdown.hidden = true;
    };
  }

  var hamburgerBtn = document.getElementById('hamburgerBtn');
  var mobileNav = document.getElementById('mobileNav');
  var mobileNavClose = document.getElementById('mobileNavClose');
  if (hamburgerBtn && mobileNav) {
    var openMenu = function() {
      mobileNav.classList.add('open');
      hamburgerBtn.setAttribute('aria-expanded', 'true');
      document.body.style.overflow = 'hidden';
    };
    var closeMenu = function() {
      mobileNav.classList.remove('open');
      hamburgerBtn.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
    };
    hamburgerBtn.onclick = openMenu;
    if (mobileNavClose) mobileNavClose.onclick = closeMenu;
    mobileNav.querySelectorAll('a').forEach(function(a) { a.addEventListener('click', closeMenu); });
    mobileNav.addEventListener('click', function(e) { if (e.target === mobileNav) closeMenu(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && mobileNav.classList.contains('open')) closeMenu(); });
  }

  function escapeHtml(str) {
    return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }
})();
</script>

<!-- Load Existing floating AI assistant -->
<script src="js/ai-assistant.js?v=<?= filemtime('js/ai-assistant.js') ?>" defer></script>

<?php $activeTab = 'trade'; include __DIR__ . '/components/mobile-tabbar.php'; ?>
</body>
</html>

