<?php
require_once __DIR__ . '/config.php';
sb_admin_required();
$adminUser = $_SESSION['admin_user'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="../css/theme-light.css?v=1">
<script src="../js/theme.js" defer></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | BM Forex Hub</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/png" href="../BM-ForexHub-Logo-Circle.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600&display=swap">
<link rel="stylesheet" href="../css/motion.css?v=<?= filemtime('../css/motion.css') ?>">
<link rel="stylesheet" href="css/admin.css?v=<?= filemtime('css/admin.css') ?>">
<link rel="stylesheet" href="css/admin-email.css?v=<?= filemtime('css/admin-email.css') ?>">
<link rel="stylesheet" href="css/admin-contacts.css?v=<?= filemtime('css/admin-contacts.css') ?>">
</head>
<body>

<!-- Ambient Blurred Background Layer -->
<div class="bm-ambient-glow" aria-hidden="true">
  <div class="bm-ambient-glow__blob bm-ambient-glow__blob--1"></div>
  <div class="bm-ambient-glow__blob bm-ambient-glow__blob--2"></div>
</div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <img src="../BM-ForexHub-Logo-Circle.png" alt="Logo" class="sidebar-logo">
    <div class="sidebar-brand-info">
      <span class="sidebar-title">BM Forex Hub</span>
      <span class="sidebar-subtitle">Admin Panel</span>
    </div>
    <button type="button" class="sidebar-collapse-toggle" id="adminSidebarCollapseBtn" title="Toggle sidebar collapse" aria-label="Toggle sidebar collapse">
      <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
  </div>
  <nav class="sidebar-nav">
    <a href="#dashboard" class="sidebar-link active" data-section="dashboard" data-tooltip="Dashboard">
      <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      <span class="sidebar-link-text">Dashboard</span>
    </a>
    <div class="sidebar-label">Management</div>
    <a href="#users" class="sidebar-link" data-section="users" data-tooltip="Users">
      <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
      <span class="sidebar-link-text">Users</span>
    </a>
    <a href="#payments" class="sidebar-link" data-section="payments" data-tooltip="Payments">
      <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
      <span class="sidebar-link-text">Payments</span>
    </a>
    <a href="#elite-subscriptions" class="sidebar-link" data-section="elite-subscriptions" data-tooltip="Elite Subscriptions">
      <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/></svg>
      <span class="sidebar-link-text">Elite Subscriptions</span>
    </a>
    <a href="#copy-traders" class="sidebar-link" data-section="copy-traders" data-tooltip="Copy Traders">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
      <span class="sidebar-link-text">Copy Traders</span>
    </a>
    <div class="sidebar-label">Content</div>
    <a href="#market-overview" class="sidebar-link" data-section="market-overview" data-tooltip="Market Overview">
      <svg viewBox="0 0 24 24"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm4-8h12V5H7v4zm0 6h12v-4H7v4zm0 6h12v-4H7v4z"/></svg>
      <span class="sidebar-link-text">Market Overview</span>
    </a>
    <a href="#articles" class="sidebar-link" data-section="articles" data-tooltip="Educational Articles">
      <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      <span class="sidebar-link-text">Educational Articles</span>
    </a>
    <a href="#videos" class="sidebar-link" data-section="videos" data-tooltip="Featured Videos">
      <svg viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
      <span class="sidebar-link-text">Featured Videos</span>
    </a>
    <a href="#promotions" class="sidebar-link" data-section="promotions" data-tooltip="Promotions">
      <svg viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
      <span class="sidebar-link-text">Promotions</span>
    </a>
    <a href="#featured-signals" class="sidebar-link" data-section="featured-signals" data-tooltip="Featured Signals">
      <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
      <span class="sidebar-link-text">Featured Signals</span>
    </a>
    <div class="sidebar-label">Communications</div>
    <a href="#announcements" class="sidebar-link" data-section="announcements" data-tooltip="Announcements">
      <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
      <span class="sidebar-link-text">Announcements</span>
    </a>
    <a href="#live-classes" class="sidebar-link" data-section="live-classes" data-tooltip="Live Classes">
      <svg viewBox="0 0 24 24"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
      <span class="sidebar-link-text">Live Classes</span>
    </a>
    <a href="#bulk-notifications" class="sidebar-link" data-section="bulk-notifications" data-tooltip="Bulk Notifications">
      <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      <span class="sidebar-link-text">Bulk Notifications</span>
    </a>
    <a href="#registered-contacts" class="sidebar-link" data-section="registered-contacts" data-tooltip="Registered Contacts">
      <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
      <span class="sidebar-link-text">Registered Contacts</span>
    </a>
    <div class="sidebar-label">AI</div>
    <a href="#ai-knowledge" class="sidebar-link" data-section="ai-knowledge" data-tooltip="AI Knowledge Base">
      <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.82.49 3.53 1.35 5L2 22l5.1-1.31C8.54 21.53 10.22 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/></svg>
      <span class="sidebar-link-text">AI Knowledge Base</span>
    </a>
    <a href="#ai-settings" class="sidebar-link" data-section="ai-settings" data-tooltip="AI Settings">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
      <span class="sidebar-link-text">AI Settings</span>
    </a>
    <a href="#ai-conversations" class="sidebar-link" data-section="ai-conversations" data-tooltip="AI Conversations">
      <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
      <span class="sidebar-link-text">AI Conversations</span>
    </a>
    <a href="#content" class="sidebar-link" data-section="content" data-tooltip="Site Content">
      <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      <span class="sidebar-link-text">Site Content</span>
    </a>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-admin">
      <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <span class="sidebar-admin-name"><?php echo htmlspecialchars($adminUser); ?></span>
    </div>
    <a href="logout.php" class="sidebar-logout" data-tooltip="Sign Out">Sign Out</a>
  </div>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Main content -->
<main class="main">
  <header class="topbar-admin">
    <button class="hamburger" id="sidebarToggle" aria-label="Toggle sidebar">
      <span></span><span></span><span></span>
    </button>
    <h1 class="topbar-title" id="pageTitle">Dashboard</h1>
    <div class="topbar-search-wrap" style="margin: 0 16px;">
      <svg class="topbar-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" class="topbar-search-input" id="adminSearchInput" placeholder="Search users, payments, classes, articles..." aria-label="Search admin portal">
    </div>
    <div class="topbar-actions">
      <button type="button" class="theme-toggle" title="Toggle light / dark theme" aria-label="Toggle light / dark theme"><svg class="theme-toggle__sun" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg><svg class="theme-toggle__moon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></button>
      <span class="topbar-admin-name"><?php echo htmlspecialchars($adminUser); ?></span>
    </div>
  </header>

  <!-- Dashboard Section -->
  <section class="section dash-workspace-shell" id="sectionDashboard">
    <div class="welcome-bar">
      <div class="welcome-text">
        <span class="welcome-greeting">Welcome back,</span>
        <span class="welcome-name"><?php echo htmlspecialchars($adminUser); ?></span>
      </div>
      <div class="welcome-role">Administrator</div>
    </div>
    <div class="stats-grid" id="statsGrid">
      <div class="stat-card">
        <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></div>
        <div class="stat-info">
          <div class="stat-value" id="statTotal">--</div>
          <div class="stat-label">Total Users</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon--gold"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div class="stat-info">
          <div class="stat-value" id="statActive">--</div>
          <div class="stat-label">Active This Week</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon--teal"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg></div>
        <div class="stat-info">
          <div class="stat-value" id="statNewWeek">--</div>
          <div class="stat-label">New This Week</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon--coral"><svg viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg></div>
        <div class="stat-info">
          <div class="stat-value" id="statNewToday">--</div>
          <div class="stat-label">New Today</div>
        </div>
      </div>
    </div>

    <div class="chart-panel chart-panel--modern">
      <div class="chart-header">
        <div class="chart-header__left">
          <div class="chart-header__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
          </div>
          <div>
            <h3 class="panel-title" style="margin:0;">User Growth &amp; Registration Trend</h3>
            <p class="chart-header__subtitle">Real-time analytical spline graph based on registered trader profiles</p>
          </div>
        </div>
        <div class="chart-header__right">
          <div class="chart-kpi-chips">
            <div class="chart-kpi-chip">
              <span class="chart-kpi-chip__label">Period Total</span>
              <strong class="chart-kpi-chip__val" id="chartPeriodTotal">--</strong>
            </div>
            <div class="chart-kpi-chip">
              <span class="chart-kpi-chip__label">Daily Avg</span>
              <strong class="chart-kpi-chip__val" id="chartDailyAvg">--</strong>
            </div>
            <div class="chart-kpi-chip">
              <span class="chart-kpi-chip__label">Peak Day</span>
              <strong class="chart-kpi-chip__val" id="chartPeakDay">--</strong>
            </div>
          </div>
          <div class="chart-range-pills" role="group" aria-label="Chart time range">
            <button type="button" class="chart-range-btn" data-days="7">7D</button>
            <button type="button" class="chart-range-btn" data-days="14">14D</button>
            <button type="button" class="chart-range-btn active" data-days="30">30D</button>
          </div>
        </div>
      </div>
      <div class="chart-canvas-wrap" id="chartCanvasWrap">
        <canvas id="trendChart" height="230"></canvas>
        <div class="chart-tooltip" id="trendChartTooltip" style="display:none;"></div>
      </div>
    </div>
  </section>

  <!-- Users Section -->
  <section class="section" id="sectionUsers" style="display:none">
    <div class="users-panel">
      <div class="panel-header">
        <h3 class="panel-title">All Users</h3>
        <div class="panel-tools">
          <div class="search-box">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="userSearch" placeholder="Search by name..." autocomplete="off">
          </div>
          <button class="btn-refresh" id="refreshBtn" title="Refresh">
            <svg viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
          </button>
        </div>
      </div>
      <div class="table-wrap">
        <table class="user-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Country</th>
              <th>Role</th>
              <th>Joined</th>
              <th>Last Login</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="userTableBody">
            <tr><td colspan="9" class="table-empty">Loading users...</td></tr>
          </tbody>
        </table>
      </div>
      <div class="table-footer" id="tableFooter">
        <span class="table-count" id="tableCount">0 users</span>
        <div class="pagination" id="pagination"></div>
      </div>
    </div>
  </section>

  <!-- Payments Section -->
  <section class="section" id="sectionPayments" style="display:none">
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
      <div class="stat-card">
        <div class="stat-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
        <div class="stat-info"><div class="stat-value" id="payRevenue">$0</div><div class="stat-label">Total Revenue</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon--teal"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div class="stat-info"><div class="stat-value" id="payCompleted">0</div><div class="stat-label">Completed</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon--gold"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div class="stat-info"><div class="stat-value" id="payPending">0</div><div class="stat-label">Pending</div></div>
      </div>
    </div>
    <div class="users-panel">
      <div class="panel-header">
        <h3 class="panel-title">Payment Records</h3>
        <div class="panel-tools">
          <div class="search-box">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="paySearch" placeholder="Search username or plan..." autocomplete="off">
          </div>
          <button class="btn-primary-sm" id="grantSubBtn">+ Grant Subscription</button>
          <button class="btn-primary-sm" id="addPaymentBtn">+ Add Payment</button>
        </div>
      </div>
      <div class="table-wrap">
        <table class="user-table">
          <thead><tr><th>Username</th><th>Plan</th><th>Amount</th><th>Status</th><th>Date</th><th>Phone</th><th>Reference</th><th>Actions</th></tr></thead>
          <tbody id="payTableBody"><tr><td colspan="8" class="table-empty">Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Elite Subscriptions Section -->
  <?php include __DIR__ . '/views/elite_subscriptions.php'; ?>

  <!-- Copy Traders Section -->
  <?php include __DIR__ . '/views/copy_traders.php'; ?>

  <!-- Market Overview Section -->
  <section class="section" id="sectionMarketOverview" style="display:none">
    <div class="users-panel">
      <div class="panel-header">
        <h3 class="panel-title">Market Overview — Announcements</h3>
        <button class="btn-primary-sm" id="addMoBtn">+ New Announcement</button>
      </div>
      <div class="table-wrap">
        <table class="user-table">
          <thead><tr><th>Title</th><th>Status</th><th>Priority</th><th>Actions</th></tr></thead>
          <tbody id="moTableBody"><tr><td colspan="4" class="table-empty">Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Educational Articles Section -->
  <section class="section" id="sectionArticles" style="display:none">
    <div class="users-panel">
      <div class="panel-header">
        <h3 class="panel-title">Educational Articles</h3>
        <button class="btn-primary-sm" id="addArticleBtn">+ Add Article</button>
      </div>
      <div class="table-wrap">
        <table class="user-table">
          <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
          <tbody id="articleTableBody"><tr><td colspan="5" class="table-empty">Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Featured Videos Section -->
  <section class="section" id="sectionVideos" style="display:none">
    <div class="users-panel">
      <div class="panel-header">
        <h3 class="panel-title">Featured Videos</h3>
        <button class="btn-primary-sm" id="addVideoBtn">+ Add Video</button>
      </div>
      <div class="table-wrap">
        <table class="user-table">
          <thead><tr><th>Title</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
          <tbody id="videoTableBody"><tr><td colspan="4" class="table-empty">Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Promotions Section -->
  <section class="section" id="sectionPromotions" style="display:none">
    <div class="users-panel">
      <div class="panel-header">
        <h3 class="panel-title">Promotions</h3>
        <button class="btn-primary-sm" id="addPromoBtn">+ Add Promotion</button>
      </div>
      <div class="table-wrap">
        <table class="user-table">
          <thead><tr><th>Title</th><th>Status</th><th>Start</th><th>End</th><th>Actions</th></tr></thead>
          <tbody id="promoTableBody"><tr><td colspan="5" class="table-empty">Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Featured Signals Section -->
  <section class="section" id="sectionFeaturedSignals" style="display:none">
    <div class="users-panel">
      <div class="panel-header">
        <h3 class="panel-title">Featured Signals</h3>
        <button class="btn-primary-sm" id="addSignalBtn">+ Add Signal</button>
      </div>
      <div class="table-wrap">
        <table class="user-table">
          <thead><tr><th>Pair</th><th>Direction</th><th>Entry</th><th>TP</th><th>SL</th><th>Confidence</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="signalTableBody"><tr><td colspan="8" class="table-empty">Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Announcements Section -->
  <section class="section" id="sectionAnnouncements" style="display:none">
    <div class="users-panel">
      <div class="panel-header">
        <h3 class="panel-title">Announcements</h3>
        <button class="btn-primary-sm" id="addAnnounceBtn">+ New Announcement</button>
      </div>
      <div class="table-wrap">
        <table class="user-table">
          <thead><tr><th>Title</th><th>Message</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
          <tbody id="announceTableBody"><tr><td colspan="5" class="table-empty">Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Live Classes Section -->
  <section class="section" id="sectionLiveClasses" style="display:none">
    <div class="users-panel">
      <div class="panel-header">
        <h3 class="panel-title">Live Classes &amp; Meetings</h3>
        <button class="btn-primary-sm" id="addLiveClassBtn">+ New Live Class</button>
      </div>
      <div class="table-wrap">
        <table class="user-table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Instructor</th>
              <th>Date &amp; Time</th>
              <th>Duration</th>
              <th>Live Status</th>
              <th>Meeting Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="liveClassesTableBody">
            <tr><td colspan="7" class="table-empty">Loading live classes...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Bulk Notifications Section -->
  <section class="section" id="sectionBulkNotifications" style="display:none">
    <div class="email-tabs">
      <button class="email-tab active" data-tab="compose">Compose Broadcast</button>
      <button class="email-tab" data-tab="drafts">Drafts</button>
      <button class="email-tab" data-tab="history">Email History</button>
    </div>

    <!-- Live Progress Panel (hidden by default) -->
    <div class="campaign-progress-panel" id="liveCampaignProgress" style="display:none">
      <div class="progress-header">
        <div class="progress-title">Sending Campaign: <span id="progressSubject">--</span></div>
        <span class="status-dot status-dot--active" id="progressStatusBadge">Processing...</span>
      </div>
      <div class="progress-track">
        <div class="progress-fill" id="progressFill"></div>
      </div>
      <div class="progress-stats">
        <div>Sent: <span class="stat-num" id="progressStatSent">0</span></div>
        <div>Failed: <span class="stat-num" id="progressStatFailed">0</span></div>
        <div>Total: <span class="stat-num" id="progressStatTotal">0</span></div>
        <div>Remaining: <span class="stat-num" id="progressStatRemain">0</span></div>
      </div>
      <div class="progress-actions" style="margin-top:12px;display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" class="btn-secondary-admin" id="campaignPauseBtn" style="padding:4px 10px;font-size:12px;">Pause</button>
        <button type="button" class="btn-secondary-admin" id="campaignResumeBtn" style="padding:4px 10px;font-size:12px;display:none;">Resume</button>
        <button type="button" class="btn-secondary-admin" id="campaignCancelBtn" style="padding:4px 10px;font-size:12px;color:var(--coral, #FF6B6B);">Cancel</button>
      </div>
    </div>

    <!-- View: Compose -->
    <div id="viewEmailCompose" class="bulk-form-panel">
      <div class="form-row">
        <div class="form-col">
          <div class="form-group-admin">
            <label>Recipient Group</label>
            <select id="bulkRecipientGroup">
              <option value="all">All Users</option>
              <option value="subscribers">Subscribers (Active Plan Holders)</option>
              <option value="premium">Premium Members</option>
              <option value="vip">VIP Members</option>
              <option value="trial">Free Trial / Non-Paying Users</option>
              <option value="expired">Expired Members</option>
              <option value="verified">Verified Users</option>
              <option value="unverified">Unverified Users</option>
              <option value="active">Active Users (Logged in within 7 days)</option>
              <option value="inactive">Inactive Users</option>
              <option value="selected">Selected Users (Custom Selection)</option>
            </select>
            <div id="selectedRecipientsInfo" style="display:none;margin-top:8px;padding:10px 12px;background:rgba(22,119,255,.08);border:1px solid rgba(22,119,255,.2);border-radius:8px;font-size:.82rem;color:var(--teal);align-items:center;justify-content:space-between;gap:8px;">
              <span id="selectedRecipientsText">0 recipients selected</span>
              <button type="button" id="selectedRecipientsEdit" style="background:none;border:none;color:var(--gold);cursor:pointer;font-size:.82rem;text-decoration:underline;padding:0;">Edit selection</button>
            </div>
          </div>
        </div>
        <div class="form-col">
          <div class="form-group-admin">
            <label>Schedule Dispatch (Optional)</label>
            <input type="datetime-local" id="bulkScheduleDate">
          </div>
        </div>
      </div>

      <div class="form-group-admin">
        <label>Subject line</label>
        <input type="text" id="bulkSubject" placeholder="e.g. Important Market Update & New Signal Release" required>
      </div>

      <div class="form-group-admin">
        <label>Message Content</label>
        <div class="editor-toolbar">
          <button type="button" class="editor-btn" data-cmd="bold"><b>B</b></button>
          <button type="button" class="editor-btn" data-cmd="italic"><i>I</i></button>
          <button type="button" class="editor-btn" data-cmd="underline"><u>U</u></button>
          <button type="button" class="editor-btn" data-cmd="insertUnorderedList">&bull; List</button>
          <button type="button" class="editor-btn" data-cmd="insertOrderedList">1. List</button>
          <button type="button" class="editor-btn" data-cmd="createLink">&link; Link</button>
          <button type="button" class="editor-btn" data-cmd="insertImage">&img; Image</button>
          <button type="button" class="editor-btn" data-cmd="justifyLeft">Left</button>
          <button type="button" class="editor-btn" data-cmd="justifyCenter">Center</button>
          <button type="button" class="editor-btn" id="editorInsertEmojiBtn" title="Insert Emoji">😊 Emoji</button>
          <button type="button" class="editor-btn" id="editorInsertTableBtn" title="Insert Table">📊 Table</button>
        </div>
        <div class="editor-content" id="bulkEditor" contenteditable="true" placeholder="Compose your email broadcast here..."></div>

        <div class="tags-legend">
          <span class="tags-label">Dynamic Tags:</span>
          <span class="tag-badge" data-tag="{{firstName}}">{{firstName}}</span>
          <span class="tag-badge" data-tag="{{lastName}}">{{lastName}}</span>
          <span class="tag-badge" data-tag="{{email}}">{{email}}</span>
          <span class="tag-badge" data-tag="{{username}}">{{username}}</span>
          <span class="tag-badge" data-tag="{{registrationDate}}">{{registrationDate}}</span>
        </div>
      </div>

      <div class="bulk-actions">
        <button type="button" class="btn-secondary-admin" id="bulkPreviewBtn">Preview Email</button>
        <button type="button" class="btn-secondary-admin" id="bulkSaveDraftBtn">Save Draft</button>
        <button type="button" class="btn-primary-admin" id="bulkSendBtn">Send Broadcast</button>
      </div>
    </div>

    <!-- View: Drafts -->
    <div id="viewEmailDrafts" class="users-panel" style="display:none">
      <div class="table-wrap">
        <table class="user-table">
          <thead><tr><th>Subject</th><th>Recipient Group</th><th>Last Updated</th><th>Actions</th></tr></thead>
          <tbody id="draftsTableBody"><tr><td colspan="4" class="table-empty">Loading drafts...</td></tr></tbody>
        </table>
      </div>
    </div>

    <!-- View: History -->
    <div id="viewEmailHistory" class="users-panel" style="display:none">
      <div class="table-wrap">
        <table class="user-table">
          <thead><tr><th>Subject</th><th>Group</th><th>Delivered / Total</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
          <tbody id="historyTableBody"><tr><td colspan="6" class="table-empty">Loading email history...</td></tr></tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Registered Contacts Section -->
  <section class="section" id="sectionRegisteredContacts" style="display:none">
    <!-- Summary Stat Cards -->
    <div class="stats-grid" style="grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:20px;">
      <div class="stat-card" style="padding:16px 14px;">
        <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></div>
        <div class="stat-info"><div class="stat-value" id="statContactsTotal">--</div><div class="stat-label">Total Users</div></div>
      </div>
      <div class="stat-card" style="padding:16px 14px;">
        <div class="stat-icon stat-icon--teal"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div class="stat-info"><div class="stat-value" id="statContactsVerified">--</div><div class="stat-label">Verified</div></div>
      </div>
      <div class="stat-card" style="padding:16px 14px;">
        <div class="stat-icon stat-icon--gold"><svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg></div>
        <div class="stat-info"><div class="stat-value" id="statContactsPhone">--</div><div class="stat-label">With Phone</div></div>
      </div>
      <div class="stat-card" style="padding:16px 14px;">
        <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
        <div class="stat-info"><div class="stat-value" id="statContactsEmail">--</div><div class="stat-label">With Email</div></div>
      </div>
      <div class="stat-card" style="padding:16px 14px;">
        <div class="stat-icon stat-icon--coral"><svg viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg></div>
        <div class="stat-info"><div class="stat-value" id="statContactsToday">--</div><div class="stat-label">New Today</div></div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="contacts-filter-bar">
      <div class="contacts-filter-group">
        <div class="search-box">
          <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="contactsSearchInput" placeholder="Search name, email, phone..." autocomplete="off">
        </div>
        <select class="filter-select" id="contactsFilterVerif">
          <option value="all">Verification: All</option>
          <option value="verified">Verified Only</option>
          <option value="unverified">Unverified Only</option>
        </select>
        <select class="filter-select" id="contactsFilterStatus">
          <option value="all">Status: All</option>
          <option value="active">Active Only</option>
          <option value="inactive">Inactive Only</option>
          <option value="banned">Banned Only</option>
        </select>
        <input type="date" class="filter-input" id="contactsFilterDateFrom" title="From Date">
        <input type="date" class="filter-input" id="contactsFilterDateTo" title="To Date">
      </div>

      <div style="display:flex;gap:10px;">
        <button class="btn-secondary-admin" id="contactsAuditBtn">Audit Logs</button>
        <button class="btn-primary-admin" id="contactsTriggerExportBtn">Export Directory</button>
      </div>
    </div>

    <!-- Floating Bulk Selection Bar -->
    <div class="bulk-selection-bar" id="contactsBulkBar" style="display:none">
      <span class="bulk-count" id="contactsBulkCount">0 Users Selected</span>
      <button class="btn-primary-admin" id="contactsBulkExportBtn">Export Selected Contacts</button>
    </div>

    <!-- Main Contacts Data Table -->
    <div class="users-panel">
      <div class="table-wrap">
        <table class="user-table">
          <thead>
            <tr>
              <th class="chk-col"><input type="checkbox" id="contactsMasterChk" class="user-chk"></th>
              <th>Full Name</th>
              <th>Username</th>
              <th>Email Address</th>
              <th>Phone Number</th>
              <th>Country</th>
              <th>Registration Date</th>
              <th>Verification</th>
              <th>Status</th>
              <th>Quick Actions</th>
            </tr>
          </thead>
          <tbody id="contactsTableBody">
            <tr><td colspan="10" class="table-empty">Loading registered contacts...</td></tr>
          </tbody>
        </table>
      </div>
      <div class="table-footer">
        <span class="table-count" id="contactsShowingCount">Showing 0 users</span>
        <div class="pagination" id="contactsPagination"></div>
      </div>
    </div>
  </section>

  <!-- AI Knowledge Base Section -->
  <section class="section" id="sectionAiKnowledge" style="display:none">
    <div class="contacts-filter-bar">
      <div class="contacts-filter-group">
        <select class="filter-select" id="aiKbCategoryFilter">
          <option value="all">Category: All</option>
          <option value="general">General & Platform</option>
          <option value="company">Company Info</option>
          <option value="account">Account & Registration</option>
          <option value="payments">Deposits & Withdrawals</option>
          <option value="plans">Membership Tiers</option>
          <option value="trading_concepts">Trading Concepts (SMC / ICT)</option>
          <option value="contact">Contact & Escalation</option>
        </select>
      </div>
      <button class="btn-primary-admin" id="aiAddKbBtn">+ Add Knowledge Entry</button>
    </div>

    <div class="users-panel">
      <div class="table-wrap">
        <table class="user-table">
          <thead>
            <tr>
              <th>Category</th>
              <th>Question / Topic</th>
              <th>Answer Preview</th>
              <th>Keywords</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="aiKnowledgeTableBody">
            <tr><td colspan="6" class="table-empty">Loading Knowledge Base entries...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- AI Settings Section -->
  <section class="section" id="sectionAiSettings" style="display:none">
    <div class="users-panel" style="max-width:820px;padding:24px;">
      <h3 style="color:var(--gold);margin-bottom:20px;font-size:1.2rem;">AI Assistant Configuration & Personality</h3>
      <form id="aiSettingsForm">
        <div class="form-group-admin" style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
          <input type="checkbox" id="aiSetEnabled" class="user-chk" style="width:20px;height:20px;" checked>
          <label for="aiSetEnabled" style="margin:0;font-size:1rem;font-weight:600;color:var(--ink);">Enable Floating AI Support Assistant</label>
        </div>

        <div class="form-group-admin">
          <label>Welcome Greeting Message</label>
          <textarea id="aiSetWelcomeMsg" rows="2" required></textarea>
        </div>

        <div class="form-group-admin">
          <label>Suggested Quick Questions (One per line)</label>
          <textarea id="aiSetSuggestedQ" rows="4"></textarea>
        </div>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group-admin">
              <label>AI Personality Guidelines</label>
              <input type="text" id="aiSetPersonality" placeholder="educational, professional, friendly">
            </div>
          </div>
          <div class="form-col">
            <div class="form-group-admin">
              <label>Support Hours</label>
              <input type="text" id="aiSetOfficeHours" placeholder="24/7 Mon-Sun (EAT)">
            </div>
          </div>
        </div>

        <div class="form-group-admin">
          <label>Off-Topic Fallback Message (Domain Guard)</label>
          <textarea id="aiSetFallbackMsg" rows="2" required></textarea>
        </div>

        <div class="form-group-admin">
          <label>WhatsApp Escalation Prompt Message</label>
          <textarea id="aiSetEscalationMsg" rows="2" required></textarea>
        </div>

        <div class="form-group-admin">
          <label>Official WhatsApp Support Link</label>
          <input type="text" id="aiSetWhatsappUrl" required>
        </div>

        <div style="margin-top:24px;">
          <button type="submit" class="btn-primary-admin">Save AI Configuration</button>
        </div>
      </form>
    </div>
  </section>

  <!-- AI Conversations Section -->
  <section class="section" id="sectionAiConversations" style="display:none">
    <div class="contacts-filter-bar">
      <div class="contacts-filter-group">
        <div class="search-box">
          <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="aiLogsSearchInput" placeholder="Search user ID, message, session..." autocomplete="off">
        </div>
        <select class="filter-select" id="aiLogsEscalatedFilter">
          <option value="all">Escalated: All</option>
          <option value="yes">WhatsApp Escalated Only</option>
          <option value="no">AI Handled Only</option>
        </select>
      </div>

      <button class="btn-primary-admin" id="aiLogsExportBtn">Export Conversation Logs (.csv)</button>
    </div>

    <div class="users-panel">
      <div class="table-wrap">
        <table class="user-table">
          <thead>
            <tr>
              <th>Session ID</th>
              <th>User ID</th>
              <th>User Message</th>
              <th>AI Response</th>
              <th>Status</th>
              <th>IP Address</th>
              <th>Timestamp</th>
            </tr>
          </thead>
          <tbody id="aiLogsTableBody">
            <tr><td colspan="7" class="table-empty">Loading conversation logs...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Site Content Section -->
  <section class="section" id="sectionContent" style="display:none">
    <div class="users-panel">
      <div class="panel-header">
        <h3 class="panel-title">Edit Site Content</h3>
        <button class="btn-primary-sm" id="saveContentBtn">Save Changes</button>
      </div>
      <div class="content-editor" id="contentEditor">
        <div class="content-loading">Loading content...</div>
      </div>
    </div>
  </section>

  <!-- Add/Edit Knowledge Base Modal -->
  <div class="modal-overlay" id="aiKbModal">
    <div class="modal" style="max-width:550px;">
      <button class="modal-close" id="aiKbModalClose">&times;</button>
      <h3 class="modal-title" id="aiKbModalTitle" style="color:var(--gold);">Add Knowledge Base Entry</h3>
      <form id="aiKbForm" style="margin-top:16px;">
        <input type="hidden" id="aiKbId">

        <div class="form-group-admin">
          <label>Category</label>
          <select id="aiKbCategory">
            <option value="general">General & Platform</option>
            <option value="company">Company Info</option>
            <option value="account">Account & Registration</option>
            <option value="payments">Deposits & Withdrawals</option>
            <option value="plans">Membership Tiers</option>
            <option value="trading_concepts">Trading Concepts (SMC / ICT)</option>
            <option value="contact">Contact & Escalation</option>
          </select>
        </div>

        <div class="form-group-admin">
          <label>Question / Topic Header</label>
          <input type="text" id="aiKbQuestion" placeholder="e.g. What is an Order Block?" required>
        </div>

        <div class="form-group-admin">
          <label>Detailed Answer / AI Response</label>
          <textarea id="aiKbAnswer" rows="4" placeholder="Enter full accurate explanation..." required></textarea>
        </div>

        <div class="form-group-admin">
          <label>Keywords (Comma separated for search matching)</label>
          <input type="text" id="aiKbKeywords" placeholder="e.g. order block, ob, smc, institutional">
        </div>

        <div class="form-group-admin" style="display:flex;align-items:center;gap:8px;">
          <input type="checkbox" id="aiKbPublished" class="user-chk" checked>
          <label for="aiKbPublished" style="margin:0;">Publish immediately to live AI Assistant</label>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;">
          <button type="submit" class="btn-primary-admin">Save Entry</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Export Preview Confirmation Modal -->
  <div class="modal-overlay" id="contactsExportModal">
    <div class="modal" style="max-width:520px;">
      <button class="modal-close" id="exportModalClose">&times;</button>
      <h3 class="modal-title" style="color:var(--gold);">Export Registered Contacts</h3>
      <p style="font-size:.88rem;color:var(--ink-muted);margin:10px 0;">
        You are preparing to export <strong id="exportRecordCount" style="color:var(--ink);">0</strong> user records.
      </p>

      <div class="form-group-admin" style="margin-top:16px;">
        <label>Export Format</label>
        <select id="exportFormatSelect">
          <option value="csv">CSV (.csv) — Standard Spreadsheet (UTF-8 BOM)</option>
          <option value="xlsx">Excel (.xlsx) — Native XML Spreadsheet</option>
        </select>
      </div>

      <label style="font-family:'IBM Plex Mono',monospace;font-size:0.75rem;color:var(--ink-dim);text-transform:uppercase;">Included Fields</label>
      <div class="export-fields-grid">
        <label class="export-field-item"><input type="checkbox" class="export-field-chk user-chk" value="full_name" checked> Full Name</label>
        <label class="export-field-item"><input type="checkbox" class="export-field-chk user-chk" value="phone" checked> Phone Number</label>
        <label class="export-field-item"><input type="checkbox" class="export-field-chk user-chk" value="email" checked> Email Address</label>
        <label class="export-field-item"><input type="checkbox" class="export-field-chk user-chk" value="created_at" checked> Registration Date</label>
        <label class="export-field-item"><input type="checkbox" class="export-field-chk user-chk" value="verification_status" checked> Verification Status</label>
        <label class="export-field-item"><input type="checkbox" class="export-field-chk user-chk" value="account_status" checked> Account Status</label>
        <label class="export-field-item"><input type="checkbox" class="export-field-chk user-chk" value="username"> Username</label>
        <label class="export-field-item"><input type="checkbox" class="export-field-chk user-chk" value="country"> Country</label>
      </div>

      <div class="compliance-warning">
        <strong>Privacy & Compliance Notice:</strong> Exported user contact details contain sensitive personal information. Data must strictly be used for legitimate business communications (e.g. WhatsApp Community creation, official announcements) in full compliance with privacy laws and platform Terms.
      </div>

      <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;">
        <button type="button" class="btn-primary-admin" id="executeExportBtn">Download Export File</button>
      </div>
    </div>
  </div>

  <!-- Export Audit Logs Modal -->
  <div class="modal-overlay" id="contactsAuditModal">
    <div class="modal" style="max-width:650px;">
      <button class="modal-close" id="auditModalClose">&times;</button>
      <h3 class="modal-title">Export Audit Trail History</h3>
      <p style="font-size:.82rem;color:var(--ink-muted);margin-bottom:16px;">Log of all contact data exports performed by administrators.</p>
      <div style="max-height:380px;overflow-y:auto;">
        <table class="user-table">
          <thead>
            <tr>
              <th>Admin ID</th>
              <th>Format</th>
              <th>Records</th>
              <th>IP Address</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody id="auditLogsTableBody">
            <tr><td colspan="5" class="table-empty">Loading audit logs...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- User Multi-Select Modal -->
  <div class="modal-overlay" id="selectedUsersModal">
    <div class="modal" style="max-width:550px;">
      <button class="modal-close" id="selectedModalClose">&times;</button>
      <h3 class="modal-title">Select Specific Recipients</h3>
      <input type="text" id="userMultiSearch" placeholder="Search by name, username or email&hellip;"
        style="width:100%;padding:10px 14px;margin-top:14px;background:var(--bg,#0A0E14);border:1px solid var(--hairline);border-radius:8px;color:var(--ink,#E8EDF2);font-size:.9rem;outline:none;" />
      <div style="max-height:350px;overflow-y:auto;margin-top:12px;" id="selectedUsersList">
        <div style="text-align:center;padding:20px;color:var(--ink-dim);">Loading user list...</div>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:14px;">
        <span id="selectedCountLive" style="font-size:.85rem;color:var(--gold);">0 selected</span>
        <button type="button" class="btn-primary-admin" id="selectedDoneBtn">Done</button>
      </div>
    </div>
  </div>

  <!-- Send Confirmation Modal -->
  <div class="modal-overlay" id="bulkConfirmModal">
    <div class="modal" style="max-width:480px;text-align:center;">
      <h3 class="modal-title" style="color:var(--gold);">Confirm Broadcast Dispatch</h3>
      <p style="margin:16px 0;color:var(--ink-muted);font-size:.9rem;line-height:1.5;">
        You are about to send <strong id="confirmSubject" style="color:var(--ink);">--</strong> to 
        <strong id="confirmRecipientsCount" style="color:var(--gold);">--</strong> recipients in group 
        <strong id="confirmGroup" style="color:var(--teal);">--</strong>.
      </p>
      <div style="display:flex;gap:12px;justify-content:center;margin-top:20px;">
        <button type="button" class="btn-secondary-admin" id="confirmCancelBtn">Cancel</button>
        <button type="button" class="btn-primary-admin" id="confirmSendBtn">Proceed & Send</button>
      </div>
    </div>
  </div>

  <!-- User Detail Modal -->
  <div class="modal-overlay" id="userModal">
    <div class="modal">
      <button class="modal-close" id="modalClose">&times;</button>
      <h3 class="modal-title" id="modalUsername">User Details</h3>
      <div class="modal-body" id="modalBody"></div>
    </div>
  </div>

  <!-- Add Payment Modal -->
  <div class="modal-overlay" id="paymentModal">
    <div class="modal">
      <button class="modal-close" id="payModalClose">&times;</button>
      <h3 class="modal-title">Record Payment</h3>
      <form id="paymentForm">
        <div class="form-group">
          <label>Username</label>
          <input type="text" id="payFormUsername" required>
        </div>
        <div class="form-group">
          <label>Plan</label>
          <select id="payFormPlan" required>
            <option value="Copy Trading Integration">Copy Trading Integration ($249)</option>
            <option value="Grid Signal — Monthly">Grid Signal — Monthly ($25)</option>
            <option value="Grid Signal — Lifetime">Grid Signal — Lifetime ($499)</option>
            <option value="Forex Classes — Online">Forex Classes — Online ($399)</option>
            <option value="Forex Classes — Physical">Forex Classes — Physical ($599)</option>
          </select>
        </div>
        <div class="form-group">
          <label>Amount (USD)</label>
          <input type="number" id="payFormAmount" min="1" step="0.01" required>
        </div>
        <div class="form-group">
          <label>Status</label>
          <select id="payFormStatus">
            <option value="completed">Completed</option>
            <option value="pending">Pending</option>
          </select>
        </div>
        <div class="form-group">
          <label>Notes</label>
          <textarea id="payFormNotes" rows="2"></textarea>
        </div>
        <button type="submit" class="btn-primary-sm" style="width:100%">Save Payment</button>
      </form>
    </div>
  </div>

  <!-- Add Announcement Modal -->
  <div class="modal-overlay" id="announceModal">
    <div class="modal">
      <button class="modal-close" id="annModalClose">&times;</button>
      <h3 class="modal-title">New Announcement</h3>
      <form id="announceForm">
        <div class="form-group">
          <label>Title</label>
          <input type="text" id="annFormTitle" required>
        </div>
        <div class="form-group">
          <label>Message</label>
          <textarea id="annFormMessage" rows="4" required></textarea>
        </div>
        <div class="form-group">
          <label><input type="checkbox" id="annFormActive" checked> Active</label>
        </div>
        <button type="submit" class="btn-primary-sm" style="width:100%">Publish</button>
      </form>
    </div>
  </div>

  <!-- Add Market Overview Announcement Modal -->
  <div class="modal-overlay" id="moModal">
    <div class="modal">
      <button class="modal-close" id="moModalClose">&times;</button>
      <h3 class="modal-title" id="moModalTitle">New Announcement</h3>
      <form id="moForm">
        <input type="hidden" id="moFormId">
        <div class="form-group"><label>Title</label><input type="text" id="moFormTitle" required></div>
        <div class="form-group"><label>Description</label><textarea id="moFormDesc" rows="3"></textarea></div>
        <div class="form-group"><label>Button Text</label><input type="text" id="moFormBtn"></div>
        <div class="form-group"><label>Button Link</label><input type="text" id="moFormLink"></div>
        <div class="form-group"><label>Priority</label><input type="number" id="moFormPriority" value="0"></div>
        <div class="form-group"><label>Status</label><select id="moFormStatus"><option value="published">Published</option><option value="draft">Draft</option></select></div>
        <button type="submit" class="btn-primary-sm" style="width:100%">Save</button>
      </form>
    </div>
  </div>

  <!-- Add Article Modal -->
  <div class="modal-overlay" id="articleModal">
    <div class="modal">
      <button class="modal-close" id="articleModalClose">&times;</button>
      <h3 class="modal-title" id="articleModalTitle">Add Article</h3>
      <form id="articleForm">
        <input type="hidden" id="articleFormId">
        <div class="form-group"><label>Title</label><input type="text" id="articleFormTitle" required></div>
        <div class="form-group"><label>Short Description</label><textarea id="articleFormDesc" rows="3"></textarea></div>
        <div class="form-group"><label>Thumbnail URL</label><input type="text" id="articleFormThumb" placeholder="https://..."></div>
        <div class="form-group"><label>Category</label><input type="text" id="articleFormCat" placeholder="e.g. SMC, Risk Management"></div>
        <div class="form-group"><label>Article Content (HTML)</label><textarea id="articleFormContent" rows="5"></textarea></div>
        <div class="form-group"><label>Status</label><select id="articleFormStatus"><option value="published">Published</option><option value="draft">Draft</option></select></div>
        <button type="submit" class="btn-primary-sm" style="width:100%">Save</button>
      </form>
    </div>
  </div>

  <!-- Add Video Modal -->
  <div class="modal-overlay" id="videoModal">
    <div class="modal">
      <button class="modal-close" id="videoModalClose">&times;</button>
      <h3 class="modal-title" id="videoModalTitle">Add Video</h3>
      <form id="videoForm">
        <input type="hidden" id="videoFormId">
        <div class="form-group"><label>Title</label><input type="text" id="videoFormTitle" required></div>
        <div class="form-group"><label>Description</label><textarea id="videoFormDesc" rows="2"></textarea></div>
        <div class="form-group"><label>Video URL (YouTube embed)</label><input type="text" id="videoFormUrl" required placeholder="https://www.youtube.com/embed/..."></div>
        <div class="form-group"><label>Thumbnail URL</label><input type="text" id="videoFormThumb" placeholder="https://..."></div>
        <div class="form-group"><label>Status</label><select id="videoFormStatus"><option value="published">Published</option><option value="draft">Draft</option></select></div>
        <button type="submit" class="btn-primary-sm" style="width:100%">Save</button>
      </form>
    </div>
  </div>

  <!-- Add Promotion Modal -->
  <div class="modal-overlay" id="promoModal">
    <div class="modal">
      <button class="modal-close" id="promoModalClose">&times;</button>
      <h3 class="modal-title" id="promoModalTitle">Add Promotion</h3>
      <form id="promoForm">
        <input type="hidden" id="promoFormId">
        <div class="form-group"><label>Title</label><input type="text" id="promoFormTitle" required></div>
        <div class="form-group"><label>Description</label><textarea id="promoFormDesc" rows="2"></textarea></div>
        <div class="form-group"><label>Banner URL</label><input type="text" id="promoFormBanner" placeholder="https://..."></div>
        <div class="form-group"><label>Button Text</label><input type="text" id="promoFormBtn" placeholder="e.g. Learn More"></div>
        <div class="form-group"><label>Link URL</label><input type="text" id="promoFormLink" placeholder="https://..."></div>
        <div style="display:flex;gap:10px">
          <div class="form-group" style="flex:1"><label>Start Date</label><input type="date" id="promoFormStart"></div>
          <div class="form-group" style="flex:1"><label>End Date</label><input type="date" id="promoFormEnd"></div>
        </div>
        <div class="form-group"><label>Status</label><select id="promoFormStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        <button type="submit" class="btn-primary-sm" style="width:100%">Save</button>
      </form>
    </div>
  </div>

  <!-- Add/Edit Featured Signal Modal -->
  <div class="modal-overlay" id="signalModal">
    <div class="modal">
      <button class="modal-close" id="signalModalClose">&times;</button>
      <h3 class="modal-title" id="signalModalTitle">Add Signal</h3>
      <form id="signalForm">
        <input type="hidden" id="signalFormId">
        <div class="form-group"><label>Pair (e.g. EURUSD)</label><input type="text" id="signalFormPair" required></div>
        <div class="form-group"><label>Direction</label><select id="signalFormDir"><option value="buy">Buy</option><option value="sell">Sell</option></select></div>
        <div class="form-group"><label>Entry Price</label><input type="text" id="signalFormEntry" required></div>
        <div class="form-group"><label>Take Profit</label><input type="text" id="signalFormTp" required></div>
        <div class="form-group"><label>Stop Loss</label><input type="text" id="signalFormSl" required></div>
        <div class="form-group"><label>Risk Description</label><input type="text" id="signalFormRisk" placeholder="e.g. 1% of account"></div>
        <div class="form-group"><label>Confidence (0-100)</label><input type="number" id="signalFormConf" min="0" max="100" value="75"></div>
        <div class="form-group"><label>Status</label><select id="signalFormStatus"><option value="published">Published</option><option value="draft">Draft</option></select></div>
        <button type="submit" class="btn-primary-sm" style="width:100%">Save</button>
      </form>
    </div>
  </div>

  <!-- Add/Edit Live Class Modal -->
  <div class="modal-overlay" id="liveClassModal">
    <div class="modal" style="max-width: 680px; max-height: 90vh; overflow-y: auto;">
      <button class="modal-close" id="liveClassModalClose">&times;</button>
      <h3 class="modal-title" id="liveClassModalTitle">New Live Class</h3>
      <form id="liveClassForm">
        <input type="hidden" id="liveClassFormId">
        
        <div class="form-group">
          <label>Class Title *</label>
          <input type="text" id="liveClassFormTitle" placeholder="e.g. Advanced SMC & Order Block Masterclass" required>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <div class="form-group" style="flex: 1; min-width: 220px;">
            <label>Instructor Name *</label>
            <input type="text" id="liveClassFormInstructor" placeholder="e.g. BM Forex Hub Master Trader" required>
          </div>
          <div class="form-group" style="flex: 1; min-width: 220px;">
            <label>Instructor Photo (Image URL or Upload)</label>
            <div style="display:flex; gap:6px;">
              <input type="text" id="liveClassFormInstructorPhoto" placeholder="uploads/thumbnails/photo.jpg" style="flex:1;">
              <input type="file" id="liveClassFormInstructorFile" accept="image/*" style="display:none;">
              <button type="button" class="btn-secondary-admin" onclick="document.getElementById('liveClassFormInstructorFile').click()">Upload</button>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label>Description</label>
          <textarea id="liveClassFormDescription" placeholder="e.g. Learn how to identify liquidity sweeps and execute high R:R setups..." rows="3"></textarea>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <div class="form-group" style="flex: 1; min-width: 180px;">
            <label>Meeting Platform *</label>
            <select id="liveClassFormPlatform" required>
              <option value="Google Meet" selected>Google Meet</option>
              <option value="Zoom">Zoom</option>
              <option value="Microsoft Teams">Microsoft Teams</option>
              <option value="Custom">Custom</option>
            </select>
          </div>
          <div class="form-group" style="flex: 2; min-width: 260px;">
            <label>Meeting Link / URL *</label>
            <input type="url" id="liveClassFormLink" placeholder="e.g. https://meet.google.com/abc-defg-hij" required>
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <div class="form-group" style="flex: 1; min-width: 180px;">
            <label>Meeting ID (Optional)</label>
            <input type="text" id="liveClassFormMeetingId" placeholder="e.g. 849 2011 4829">
          </div>
          <div class="form-group" style="flex: 1; min-width: 180px;">
            <label>Passcode / Password (Optional)</label>
            <input type="text" id="liveClassFormMeetingPassword" placeholder="e.g. 123456">
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <div class="form-group" style="flex: 1.5; min-width: 220px;">
            <label>Start Date &amp; Time *</label>
            <input type="datetime-local" id="liveClassFormStartDatetime" required>
          </div>
          <div class="form-group" style="flex: 1; min-width: 150px;">
            <label>Duration (Minutes) *</label>
            <input type="number" id="liveClassFormDuration" placeholder="60" value="60" min="5" required>
          </div>
          <div class="form-group" style="flex: 1.2; min-width: 180px;">
            <label>Timezone</label>
            <input type="text" id="liveClassFormTimezone" value="Africa/Nairobi (EAT)">
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <div class="form-group" style="flex: 1; min-width: 220px;">
            <label>Class Banner Image</label>
            <div style="display:flex; gap:6px;">
              <input type="text" id="liveClassFormBanner" placeholder="uploads/images/banner.jpg" style="flex:1;">
              <input type="file" id="liveClassFormBannerFile" accept="image/*" style="display:none;">
              <button type="button" class="btn-secondary-admin" onclick="document.getElementById('liveClassFormBannerFile').click()">Upload</button>
            </div>
          </div>
          <div class="form-group" style="flex: 1; min-width: 220px;">
            <label>PDF Presentation / Class Notes</label>
            <div style="display:flex; gap:6px;">
              <input type="text" id="liveClassFormNotesPdf" placeholder="uploads/documents/notes.pdf" style="flex:1;">
              <input type="file" id="liveClassFormNotesFile" accept=".pdf" style="display:none;">
              <button type="button" class="btn-secondary-admin" onclick="document.getElementById('liveClassFormNotesFile').click()">Upload PDF</button>
            </div>
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <div class="form-group" style="flex: 2; min-width: 260px;">
            <label>Recording Video Link (Post-Class)</label>
            <input type="url" id="liveClassFormRecordingLink" placeholder="e.g. https://youtube.com/watch?v=xyz or drive link">
          </div>
          <div class="form-group" style="flex: 1; min-width: 140px;">
            <label>Max Attendees</label>
            <input type="number" id="liveClassFormMaxAttendees" value="500" min="1">
          </div>
          <div class="form-group" style="flex: 1; min-width: 140px;">
            <label>Visibility</label>
            <select id="liveClassFormVisibility">
              <option value="published" selected>Published</option>
              <option value="draft">Draft</option>
              <option value="archived">Archived</option>
            </select>
          </div>
        </div>

        <div style="display: flex; gap: 20px; margin: 12px 0 16px; background: rgba(22,119,255,0.06); padding: 10px 14px; border-radius: 8px; border: 1px solid rgba(22,119,255,0.15);">
          <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
            <input type="checkbox" id="liveClassFormIsLive">
            <strong>🔴 Go Live Now (Override)</strong>
          </label>
          <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
            <input type="checkbox" id="liveClassFormMeetingReady" checked>
            <strong>✅ Meeting Ready</strong>
          </label>
        </div>

        <div style="display:flex; gap:10px; margin-top:16px;">
          <button type="submit" class="btn-primary-sm" style="flex:2" id="liveClassFormSubmitBtn">Save Live Class</button>
          <button type="button" class="btn-secondary-admin" id="liveClassFormDuplicateBtn" style="display:none;">Duplicate</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Grant Subscription Modal -->
  <div class="modal-overlay" id="grantModal">
    <div class="modal modal-grant">
      <div class="modal-grant-header">
        <button class="modal-close" id="grantModalClose" aria-label="Close modal">&times;</button>
        <div class="modal-grant-title-wrap">
          <div class="modal-grant-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
          </div>
          <div>
            <h3 class="modal-grant-title">Grant Subscription</h3>
            <p class="modal-grant-subtitle">Manually allocate a subscription or membership plan to a user account</p>
          </div>
        </div>
      </div>

      <form id="grantForm">
        <div class="modal-grant-body">
          <!-- Inline Feedback Alert -->
          <div class="grant-alert" id="grantFormAlert"></div>

          <!-- Target User Field -->
          <div class="grant-form-group">
            <label class="grant-label" for="grantFormUsername">
              <span>Target User (Username or Email)</span>
              <span class="grant-label-optional" style="color:var(--blue);font-weight:600;">* Required</span>
            </label>
            <div class="grant-input-wrap">
              <span class="grant-input-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              </span>
              <input type="text" id="grantFormUsername" class="grant-input has-icon" list="grantUserList" required placeholder="Enter username or email address..." autocomplete="off">
            </div>
            <datalist id="grantUserList"></datalist>
            <span class="grant-hint">Type username or email registered on BM Forex Hub</span>
          </div>

          <!-- Subscription Plan Field -->
          <div class="grant-form-group">
            <label class="grant-label" for="grantFormPlan">
              <span>Subscription Plan</span>
              <span class="grant-label-optional" style="color:var(--blue);font-weight:600;">* Required</span>
            </label>
            <select id="grantFormPlan" class="grant-select" required>
              <optgroup label="Standard Services">
                <option value="copytrading">Copy Trading — KES 32,121 ($249 USD)</option>
                <option value="grid_monthly">Grid Signal Monthly — KES 3,225 ($25 USD)</option>
                <option value="grid_lifetime">Grid Signal Lifetime — KES 64,371 ($499 USD)</option>
                <option value="classes_online">Forex Classes Online — KES 51,471 ($399 USD)</option>
                <option value="classes_physical">Forex Classes Physical — KES 77,271 ($599 USD)</option>
              </optgroup>
              <optgroup label="BM Quantum Edge Indicator">
                <option value="indicator_quantum_edge">BM Quantum Edge — $299 USD (One-Time Payment)</option>
              </optgroup>
              <optgroup label="BM Elites Trading Circle">
                <option value="elite_starter">BM Elites — $1,000 USD Starter Tier</option>
                <option value="elite_intermediate">BM Elites — $2,000 USD Intermediate Tier</option>
                <option value="elite_advanced">BM Elites — $3,000 USD Advanced Tier</option>
                <option value="elite_professional">BM Elites — $5,000 USD Professional Tier</option>
                <option value="elite_elite">BM Elites — $10,000 USD VIP Elite Tier</option>
              </optgroup>
            </select>
          </div>

          <!-- Duration Field with Preset Quick Pills -->
          <div class="grant-form-group">
            <label class="grant-label" for="grantFormDuration">
              <span>Duration (Days)</span>
              <span class="grant-label-optional" style="color:var(--blue);font-weight:600;">* Required</span>
            </label>
            <input type="number" id="grantFormDuration" class="grant-input" min="1" max="3650" value="30" required>
            <div class="grant-preset-pills">
              <button type="button" class="grant-preset-btn active" data-days="30">30 Days (1 Mo)</button>
              <button type="button" class="grant-preset-btn" data-days="90">90 Days (3 Mo)</button>
              <button type="button" class="grant-preset-btn" data-days="365">1 Year (365 D)</button>
              <button type="button" class="grant-preset-btn" data-days="3650">Lifetime (10 Yrs)</button>
            </div>
          </div>

          <!-- Admin Notes Field -->
          <div class="grant-form-group">
            <label class="grant-label" for="grantFormNotes">
              <span>Admin Audit Notes</span>
              <span class="grant-label-optional">Optional</span>
            </label>
            <textarea id="grantFormNotes" class="grant-textarea" rows="2" placeholder="e.g. WhatsApp M-Pesa transaction reference, promo grant, or offline agreement..."></textarea>
          </div>
        </div>

        <div class="modal-grant-footer">
          <button type="button" class="btn-grant-cancel" id="grantFormCancelBtn">Cancel</button>
          <button type="submit" class="btn-grant-submit" id="grantFormSubmitBtn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            <span>Grant Subscription</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Email Live Preview Modal -->
  <div class="modal-overlay" id="emailPreviewModal">
    <div class="modal" style="max-width:700px;width:95%;max-height:90vh;display:flex;flex-direction:column;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <h3 class="modal-title" style="margin:0;">Broadcast Live Preview</h3>
        <button class="modal-close" id="emailPreviewModalClose" style="background:none;border:none;color:var(--muted);font-size:20px;cursor:pointer;">&times;</button>
      </div>
      <div id="emailPreviewFrameContainer" style="flex:1;min-height:450px;background:#0A0E14;border:1px solid var(--hairline);border-radius:8px;overflow:hidden;">
        <iframe id="emailPreviewFrame" style="width:100%;height:100%;min-height:450px;border:none;"></iframe>
      </div>
      <div style="margin-top:14px;text-align:right;">
        <button type="button" class="btn-secondary-admin" id="emailPreviewCloseBtn">Close Preview</button>
      </div>
    </div>
  </div>

  <!-- Grant Elite Modal -->
  <div class="modal-overlay" id="grantEliteModal">
    <div class="modal">
      <button class="modal-close" id="grantEliteModalClose">&times;</button>
      <h3 class="modal-title">Grant Elite Membership</h3>
      <form id="grantEliteForm">
        <div class="form-group">
          <label>Username or Email</label>
          <input type="text" id="grantEliteUsername" required placeholder="Target user's username or email...">
        </div>
        <div class="form-group">
          <label>Elite Tier Plan</label>
          <select id="grantElitePlan" required>
            <option value="elite_starter">BM Elites — $1,000 USD</option>
            <option value="elite_intermediate">BM Elites — $2,000 USD</option>
            <option value="elite_advanced">BM Elites — $3,000 USD</option>
            <option value="elite_professional">BM Elites — $5,000 USD</option>
            <option value="elite_elite" selected>BM Elites — $10,000 USD</option>
          </select>
        </div>
        <div class="form-group">
          <label>Amount (USD)</label>
          <input type="number" id="grantEliteAmount" value="10000" required>
        </div>
        <div class="form-group">
          <label>Access Duration (Days)</label>
          <input type="number" id="grantEliteDuration" value="30" min="1" max="3650" required>
        </div>
        <button type="submit" class="btn-primary-sm" style="width:100%" id="grantEliteSubmitBtn">Grant Elite Plan</button>
      </form>
    </div>
  </div>

  <!-- Market Overview Modal -->
  <div class="modal-overlay" id="moModal">
    <div class="modal">
      <button class="modal-close" id="moModalClose">&times;</button>
      <h3 class="modal-title" id="moModalTitle">Market Overview Announcement</h3>
      <form id="moForm">
        <input type="hidden" id="moFormId">
        <div class="form-group">
          <label>Title *</label>
          <input type="text" id="moFormTitle" placeholder="e.g. Weekly Market Analysis & Forecast" required>
        </div>
        <div class="form-group">
          <label>Banner Image (URL or Upload)</label>
          <div style="display:flex;gap:6px;">
            <input type="text" id="moFormBanner" placeholder="uploads/images/banner.jpg" style="flex:1;">
            <input type="file" id="moFormBannerFile" accept="image/*" style="display:none;">
            <button type="button" class="btn-secondary-admin" onclick="document.getElementById('moFormBannerFile').click()">Upload Image</button>
          </div>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea id="moFormDesc" rows="3" placeholder="Brief announcement or summary..."></textarea>
        </div>
        <div style="display:flex;gap:12px;">
          <div class="form-group" style="flex:1;">
            <label>Button Label (Optional)</label>
            <input type="text" id="moFormBtn" placeholder="e.g. View Analysis">
          </div>
          <div class="form-group" style="flex:1;">
            <label>Button Link / URL (Optional)</label>
            <input type="text" id="moFormLink" placeholder="e.g. https://bmforexhub.exchange/overview.php">
          </div>
        </div>
        <div style="display:flex;gap:12px;">
          <div class="form-group" style="flex:1;">
            <label>Priority</label>
            <input type="number" id="moFormPriority" value="0">
          </div>
          <div class="form-group" style="flex:1;">
            <label>Status</label>
            <select id="moFormStatus">
              <option value="published">Published</option>
              <option value="draft">Draft</option>
              <option value="archived">Archived</option>
            </select>
          </div>
        </div>
        <div style="display:flex;gap:10px;margin-top:10px;">
          <button type="submit" class="btn-primary-sm" style="flex:2">Save Announcement</button>
          <button type="button" class="btn-secondary-admin" id="moFormDuplicateBtn" style="display:none;">Duplicate</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Educational Article Modal -->
  <div class="modal-overlay" id="articleModal">
    <div class="modal" style="max-width:700px; max-height: 90vh; overflow-y: auto;">
      <button class="modal-close" id="articleModalClose">&times;</button>
      <h3 class="modal-title" id="articleModalTitle">Educational Article</h3>
      <form id="articleForm">
        <input type="hidden" id="articleFormId">
        <div class="form-group">
          <label>Title *</label>
          <input type="text" id="articleFormTitle" placeholder="e.g. Mastering Smart Money Concepts (SMC)" required>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
          <div class="form-group" style="flex:1;min-width:180px;">
            <label>Category</label>
            <input type="text" id="articleFormCat" placeholder="e.g. SMC / ICT, Analysis, Risk" value="General">
          </div>
          <div class="form-group" style="flex:1;min-width:140px;">
            <label>Read Time (Minutes)</label>
            <input type="number" id="articleFormReadTime" value="5" min="1">
          </div>
          <div class="form-group" style="flex:1;min-width:140px;">
            <label>Status</label>
            <select id="articleFormStatus">
              <option value="published">Published</option>
              <option value="draft">Draft</option>
            </select>
          </div>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
          <div class="form-group" style="flex:1;min-width:220px;">
            <label>Author Name</label>
            <input type="text" id="articleFormAuthor" value="BM Forex Hub Team">
          </div>
          <div class="form-group" style="flex:1;min-width:220px;">
            <label>Cover Image (URL or Upload)</label>
            <div style="display:flex;gap:6px;">
              <input type="text" id="articleFormThumb" placeholder="uploads/images/cover.jpg" style="flex:1;">
              <input type="file" id="articleFormThumbFile" accept="image/*" style="display:none;">
              <button type="button" class="btn-secondary-admin" onclick="document.getElementById('articleFormThumbFile').click()">Upload Image</button>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Short Description / Summary</label>
          <textarea id="articleFormDesc" rows="2" placeholder="Summary for article card preview..."></textarea>
        </div>
        <div class="form-group">
          <label>Article Content (HTML / Markdown supported)</label>
          <textarea id="articleFormContent" rows="8" placeholder="Full article body content..." required></textarea>
        </div>
        <div style="display:flex;gap:10px;margin-top:10px;">
          <button type="submit" class="btn-primary-sm" style="flex:2">Save Article</button>
          <button type="button" class="btn-secondary-admin" id="articleFormDuplicateBtn" style="display:none;">Duplicate</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Featured Video Modal -->
  <div class="modal-overlay" id="videoModal">
    <div class="modal">
      <button class="modal-close" id="videoModalClose">&times;</button>
      <h3 class="modal-title" id="videoModalTitle">Featured Video</h3>
      <form id="videoForm">
        <input type="hidden" id="videoFormId">
        <div class="form-group">
          <label>Video Title *</label>
          <input type="text" id="videoFormTitle" placeholder="e.g. Live Forex Session Breakdown" required>
        </div>
        <div class="form-group">
          <label>Video URL (YouTube or Vimeo) *</label>
          <input type="url" id="videoFormUrl" placeholder="https://www.youtube.com/watch?v=..." required>
        </div>
        <div class="form-group">
          <label>Thumbnail Image (URL or Upload)</label>
          <div style="display:flex;gap:6px;">
            <input type="text" id="videoFormThumb" placeholder="uploads/thumbnails/video_thumb.jpg" style="flex:1;">
            <input type="file" id="videoFormThumbFile" accept="image/*" style="display:none;">
            <button type="button" class="btn-secondary-admin" onclick="document.getElementById('videoFormThumbFile').click()">Upload Thumb</button>
          </div>
        </div>
        <div style="display:flex;gap:12px;">
          <div class="form-group" style="flex:1;">
            <label>Category</label>
            <input type="text" id="videoFormCat" placeholder="General" value="Strategy">
          </div>
          <div class="form-group" style="flex:1;">
            <label>Duration (e.g. 15:30)</label>
            <input type="text" id="videoFormDuration" value="10:00">
          </div>
          <div class="form-group" style="flex:1;">
            <label>Status</label>
            <select id="videoFormStatus">
              <option value="published">Published</option>
              <option value="draft">Draft</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea id="videoFormDesc" rows="3" placeholder="Video summary..."></textarea>
        </div>
        <div style="display:flex;gap:10px;margin-top:10px;">
          <button type="submit" class="btn-primary-sm" style="flex:2">Save Video</button>
          <button type="button" class="btn-secondary-admin" id="videoFormDuplicateBtn" style="display:none;">Duplicate</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Promotion Modal -->
  <div class="modal-overlay" id="promoModal">
    <div class="modal">
      <button class="modal-close" id="promoModalClose">&times;</button>
      <h3 class="modal-title" id="promoModalTitle">Promotion</h3>
      <form id="promoForm">
        <input type="hidden" id="promoFormId">
        <div class="form-group">
          <label>Title *</label>
          <input type="text" id="promoFormTitle" placeholder="e.g. Special Discount on Signal Package" required>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea id="promoFormDesc" rows="3" placeholder="Promotion details..."></textarea>
        </div>
        <div class="form-group">
          <label>Banner Image (URL or Upload)</label>
          <div style="display:flex;gap:6px;">
            <input type="text" id="promoFormBanner" placeholder="uploads/images/promo_banner.jpg" style="flex:1;">
            <input type="file" id="promoFormBannerFile" accept="image/*" style="display:none;">
            <button type="button" class="btn-secondary-admin" onclick="document.getElementById('promoFormBannerFile').click()">Upload Banner</button>
          </div>
        </div>
        <div style="display:flex;gap:12px;">
          <div class="form-group" style="flex:1;">
            <label>Button Text</label>
            <input type="text" id="promoFormBtn" placeholder="e.g. Claim Now">
          </div>
          <div class="form-group" style="flex:1;">
            <label>Button Link</label>
            <input type="text" id="promoFormLink" placeholder="e.g. https://bmforexhub.exchange/subscribe.php">
          </div>
        </div>
        <div style="display:flex;gap:12px;">
          <div class="form-group" style="flex:1;">
            <label>Start Date</label>
            <input type="date" id="promoFormStart">
          </div>
          <div class="form-group" style="flex:1;">
            <label>End Date</label>
            <input type="date" id="promoFormEnd">
          </div>
        </div>
        <div class="form-group">
          <label>Status</label>
          <select id="promoFormStatus">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div style="display:flex;gap:10px;margin-top:10px;">
          <button type="submit" class="btn-primary-sm" style="flex:2">Save Promotion</button>
          <button type="button" class="btn-secondary-admin" id="promoFormDuplicateBtn" style="display:none;">Duplicate</button>
        </div>
      </form>
    </div>
  </div>
</main>

<script src="js/admin.js?v=<?= filemtime('js/admin.js') ?>"></script>
<script src="js/admin-email.js?v=<?= filemtime('js/admin-email.js') ?>"></script>
<script src="js/admin-contacts.js?v=<?= filemtime('js/admin-contacts.js') ?>"></script>
<script src="js/admin-ai.js?v=<?= filemtime('js/admin-ai.js') ?>"></script>
<script src="../js/motion.js" defer></script>
</body>
</html>
