<?php
require_once __DIR__ . '/engine_config.php';
require_once __DIR__ . '/app/Services/CopyTradingService.php';

use App\Services\CopyTradingService;

?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="css/theme-light.css?v=1">
<script src="js/theme.js" defer></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Copy Trading Account Setup | BM Forex Hub</title>
<meta name="description" content="Set up your MT5 account credentials for BM Forex Hub automated copy trading.">
<link rel="icon" type="image/png" href="BM-ForexHub-Logo-Circle.png">
<meta name="robots" content="noindex, nofollow, noarchive">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="basics.css?v=<?= filemtime('basics.css') ?>">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="css/mobile.css?v=<?= filemtime('css/mobile.css') ?>">
<link rel="stylesheet" href="css/ai-assistant.css">

<style>
/* ===== Copy Trading Dashboard Layout ===== */
*, *::before, *::after { box-sizing: border-box; }
body {
  background: #0B0F14;
  color: #FFFFFF;
  font-family: 'Inter', system-ui, sans-serif;
  min-height: 100vh;
  margin: 0;
  display: flex;
}

/* Sidebar */
.dash-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 210px;
  height: 100vh;
  background: #101722;
  border-right: 1px solid #1e2d42;
  display: flex;
  flex-direction: column;
  z-index: 200;
  overflow-y: auto;
}
.dash-sidebar__brand {
  padding: 18px 16px 16px;
  border-bottom: 1px solid #1e2d42;
}
.dash-sidebar__logo {
  display: flex;
  align-items: center;
  gap: 9px;
  text-decoration: none;
}
.dash-sidebar__logo img {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 1px solid rgba(22,119,255,.5);
}
.dash-sidebar__logo span {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  font-size: 0.8rem;
  color: #fff;
  letter-spacing: -0.2px;
}
.dash-sidebar__nav {
  padding: 12px 8px;
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
}
.dash-sidebar__item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 12px;
  color: #8fa3b8;
  text-decoration: none;
  font-size: 0.82rem;
  font-weight: 500;
  border-radius: 6px;
  transition: all 0.15s ease;
}
.dash-sidebar__item:hover {
  color: #fff;
  background: rgba(255,255,255,0.05);
}
.dash-sidebar__item.active {
  color: #1677FF;
  background: rgba(22,119,255,0.12);
  font-weight: 600;
}
.dash-sidebar__bottom {
  padding: 12px 8px;
  border-top: 1px solid #1e2d42;
}

/* Main Area */
.dash-main-wrap {
  margin-left: 210px;
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}
.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 28px;
  background: rgba(16,23,34,0.92);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid #1e2d42;
  position: sticky;
  top: 0;
  z-index: 100;
}
.topbar__title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.1rem;
  font-weight: 700;
  color: #fff;
}
.topbar__user {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.82rem;
  color: #8fa3b8;
}

.ct-container {
  max-width: 820px;
  width: 100%;
  margin: 32px auto;
  padding: 0 24px;
}

/* Onboarding Header Banner */
.ct-banner {
  background: linear-gradient(135deg, rgba(22,119,255,0.15) 0%, rgba(13,71,161,0.08) 100%);
  border: 1px solid rgba(22,119,255,0.3);
  border-radius: 14px;
  padding: 24px;
  margin-bottom: 24px;
  display: flex;
  gap: 16px;
  align-items: flex-start;
}
.ct-banner__icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: rgba(22,119,255,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.ct-banner__icon svg {
  width: 24px;
  height: 24px;
  stroke: #1677FF;
}
.ct-banner__title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.15rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 6px;
}
.ct-banner__desc {
  font-size: 0.85rem;
  color: #b8c3d1;
  line-height: 1.5;
}

/* Status Card */
.ct-status-card {
  background: #101722;
  border: 1px solid #1e2d42;
  border-radius: 14px;
  padding: 24px;
  margin-bottom: 24px;
}
.ct-status-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}
.ct-status-badge {
  font-family: 'IBM Plex Mono', monospace;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.ct-status-badge.Pending { background: rgba(255,193,7,0.15); color: #FFC107; border: 1px solid rgba(255,193,7,0.3); }
.ct-status-badge.Active { background: rgba(22,199,132,0.15); color: #16C784; border: 1px solid rgba(22,199,132,0.3); }
.ct-status-badge.Suspended { background: rgba(246,70,93,0.15); color: #F6465D; border: 1px solid rgba(246,70,93,0.3); }
.ct-status-badge.Closed { background: rgba(255,255,255,0.1); color: #8fa3b8; border: 1px solid rgba(255,255,255,0.2); }

.ct-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
@media(max-width: 600px) { .ct-grid { grid-template-columns: 1fr; } }
.ct-info-group {
  background: #151D29;
  border: 1px solid #1e2d42;
  padding: 12px 16px;
  border-radius: 10px;
}
.ct-info-label { font-size: 0.72rem; color: #7F8B99; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.ct-info-val { font-size: 0.92rem; font-weight: 600; color: #fff; word-break: break-all; }

/* Form Elements */
.ct-card {
  background: #101722;
  border: 1px solid #1e2d42;
  border-radius: 14px;
  padding: 28px;
}
.ct-form-group {
  margin-bottom: 20px;
}
.ct-form-group label {
  display: block;
  font-size: 0.82rem;
  font-weight: 600;
  color: #b8c3d1;
  margin-bottom: 8px;
}
.ct-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.ct-input-wrap input, .ct-form-group textarea {
  width: 100%;
  padding: 12px 14px;
  background: #151D29;
  border: 1px solid #283548;
  border-radius: 8px;
  color: #fff;
  font-family: inherit;
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.ct-input-wrap input:focus, .ct-form-group textarea:focus {
  border-color: #1677FF;
  box-shadow: 0 0 0 3px rgba(22,119,255,0.2);
}
.ct-toggle-pass {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  color: #7F8B99;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.ct-toggle-pass:hover { color: #1677FF; }
.ct-checkbox-wrap {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-top: 24px;
  margin-bottom: 24px;
}
.ct-checkbox-wrap input[type="checkbox"] {
  width: 18px;
  height: 18px;
  accent-color: #1677FF;
  margin-top: 2px;
  cursor: pointer;
}
.ct-checkbox-wrap label {
  font-size: 0.84rem;
  color: #b8c3d1;
  line-height: 1.45;
  cursor: pointer;
}

.btn-submit {
  width: 100%;
  padding: 14px;
  border: none;
  border-radius: 10px;
  background: linear-gradient(135deg, #1677FF 0%, #0D47A1 100%);
  color: #fff;
  font-family: 'Space Grotesk', sans-serif;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}
.btn-submit:hover {
  box-shadow: 0 6px 24px rgba(22,119,255,0.4);
  transform: translateY(-1px);
}
.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

/* Toast alert */
.ct-alert {
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 0.85rem;
  margin-bottom: 20px;
  display: none;
}
.ct-alert.success { background: rgba(22,199,132,0.12); border: 1px solid rgba(22,199,132,0.3); color: #16C784; display: block; }
.ct-alert.error { background: rgba(246,70,93,0.12); border: 1px solid rgba(246,70,93,0.3); color: #F6465D; display: block; }

/* Responsive Sidebar Toggle */
.mobile-nav-toggle {
  display: none;
  background: none;
  border: none;
  color: #fff;
  font-size: 1.2rem;
  cursor: pointer;
}
@media(max-width: 768px) {
  .dash-sidebar { transform: translateX(-100%); transition: transform 0.2s ease; }
  .dash-sidebar.open { transform: translateX(0); }
  .dash-main-wrap { margin-left: 0; }
  .mobile-nav-toggle { display: block; }
}
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="dash-sidebar" id="dashSidebar">
  <div class="dash-sidebar__brand">
    <a href="index.php" class="dash-sidebar__logo">
      <img src="BM-ForexHub-Logo-Circle.png" alt="BM Forex Hub">
      <span>BM FOREX HUB</span>
    </a>
  </div>
  <nav class="dash-sidebar__nav">
    <a href="index.php" class="dash-sidebar__item">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      <span>Dashboard</span>
    </a>
    <a href="overview.php" class="dash-sidebar__item">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm4-8h12V5H7v4zm0 6h12v-4H7v4zm0 6h12v-4H7v4z"/></svg>
      <span>Market Overview</span>
    </a>
    <a href="quick_tools.php" class="dash-sidebar__item">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
      <span>Quick Tools</span>
    </a>
    <a href="index.php?section=signals" class="dash-sidebar__item">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      <span>Signals Grid</span>
    </a>
    <a href="trading.php" class="dash-sidebar__item">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
      <span>BM Quantum Edge</span>
    </a>
    <a href="classes.php" class="dash-sidebar__item">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
      <span>Classes</span>
    </a>
    <a href="copy_trading.php" class="dash-sidebar__item active">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
      <span>Copy Trading</span>
    </a>
    <a href="bm_elites.php" class="dash-sidebar__item">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      <span>BM Elites</span>
    </a>
    <a href="crypto.php" class="dash-sidebar__item">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.5 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-5.5-6z"/><path d="M13.5 2v5.5H19"/><path d="M9 13h6M9 17h4"/></svg>
      <span>Crypto Trading</span>
    </a>
  </nav>
  <div class="dash-sidebar__bottom">
    <a href="logout.php" class="dash-sidebar__item">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      <span>Logout</span>
    </a>
  </div>
</aside>

<!-- Main Wrap -->
<div class="dash-main-wrap">
  <div class="topbar">
    <div style="display:flex;align-items:center;gap:12px">
      <button type="button" class="mobile-nav-toggle" id="sidebarToggleBtn" aria-label="Toggle navigation">&#9776;</button>
      <span class="topbar__title">Copy Trading Setup</span>
    </div>
    <div class="topbar__user" id="topbarUser">
      <span>Loading account...</span>
    </div>
  </div>

  <div class="ct-container">

    <!-- Onboarding Explanation Banner -->
    <div class="ct-banner">
      <div class="ct-banner__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div>
        <div class="ct-banner__title">MT5 Account Onboarding &amp; Execution</div>
        <div class="ct-banner__desc">
          To enable automated trade copying on your MetaTrader 5 account, BM Forex Hub requires your MT5 investor or trading credentials. Our institutional master account will automatically replicate high-probability SMC trade setups onto your account with optimized lot sizing and strict risk parameters.
        </div>
      </div>
    </div>

    <!-- Feedback Alert -->
    <div class="ct-alert" id="ctAlert"></div>

    <!-- Existing Submission Status Card (if submitted) -->
    <div class="ct-status-card" id="ctStatusCard" style="display:none">
      <div class="ct-status-header">
        <h3 style="font-family:'Space Grotesk',sans-serif;font-size:1.05rem;margin:0">Active Copy Trading Submission</h3>
        <span class="ct-status-badge Pending" id="ctStatusBadge">Pending</span>
      </div>
      <div class="ct-grid">
        <div class="ct-info-group">
          <div class="ct-info-label">Broker Name</div>
          <div class="ct-info-val" id="dispBroker">--</div>
        </div>
        <div class="ct-info-group">
          <div class="ct-info-label">MT5 Login Account</div>
          <div class="ct-info-val" id="dispLogin">--</div>
        </div>
        <div class="ct-info-group">
          <div class="ct-info-label">MT5 Server</div>
          <div class="ct-info-val" id="dispServer">--</div>
        </div>
        <div class="ct-info-group">
          <div class="ct-info-label">Submitted On</div>
          <div class="ct-info-val" id="dispDate">--</div>
        </div>
      </div>
      <div style="margin-top:16px;text-align:right">
        <button type="button" class="btn btn-ghost" onclick="toggleForm()" style="font-size:0.82rem">Update MT5 Details</button>
      </div>
    </div>

    <!-- Form Container -->
    <div class="ct-card" id="ctFormCard">
      <h3 style="font-family:'Space Grotesk',sans-serif;font-size:1.15rem;margin-bottom:20px" id="formHeaderTitle">Enter Your MT5 Account Information</h3>
      
      <form id="ctForm" onsubmit="submitForm(event)">
        <datalist id="brokerOptions">
          <option value="Exness">
          <option value="IC Markets">
          <option value="FP Markets">
          <option value="Pepperstone">
          <option value="Deriv">
        </datalist>

        <div class="ct-form-group">
          <label for="brokerName">Broker Name *</label>
          <div class="ct-input-wrap">
            <input type="text" id="brokerName" list="brokerOptions" placeholder="e.g. Exness, IC Markets, Pepperstone, Deriv" required>
          </div>
        </div>

        <div class="ct-form-group">
          <label for="mt5Login">MT5 Login Account Number *</label>
          <div class="ct-input-wrap">
            <input type="number" id="mt5Login" placeholder="e.g. 50123984" required>
          </div>
        </div>

        <div class="ct-form-group">
          <label for="mt5Password">MT5 Password *</label>
          <div class="ct-input-wrap">
            <input type="password" id="mt5Password" placeholder="Enter your MT5 account password" required>
            <button type="button" class="ct-toggle-pass" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility">
              <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>

        <div class="ct-form-group">
          <label for="mt5Server">MT5 Server *</label>
          <div class="ct-input-wrap">
            <input type="text" id="mt5Server" placeholder="e.g. Exness-MT5Real6, ICMarketsSC-Live09, Pepperstone-Live01" required>
          </div>
        </div>

        <div class="ct-form-group">
          <label for="notes">Optional Notes &amp; Preferences</label>
          <textarea id="notes" rows="4" placeholder="e.g. Preferred risk level (1-2%), target lot sizing limit, max drawdown threshold, special instructions..."></textarea>
        </div>

        <div class="ct-checkbox-wrap">
          <input type="checkbox" id="authCheck" required>
          <label for="authCheck">I authorize BM Forex Hub to manage trades on this MT5 account in accordance with the platform's risk disclosure and trading terms.</label>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">Submit Copy Trading Details</button>
      </form>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
<script src="js/auth.js?v=<?= filemtime('js/auth.js') ?>"></script>
<script>
(function(){
  var _user = null;
  var _session = null;

  document.getElementById('sidebarToggleBtn').addEventListener('click', function(){
    document.getElementById('dashSidebar').classList.toggle('open');
  });

  /* Auth & Subscription Enforcement */
  window.addEventListener('DOMContentLoaded', async function(){
    try {
      var s = await BMAuth.getSession();
      if (!s || !s.user) {
        window.location.href = 'login.php';
        return;
      }
      _user = s.user;
      _session = s.session;

      var name = BMAuth.displayName(_user);
      document.getElementById('topbarUser').innerHTML = '<span>' + name + '</span>';

      /* Verify Copy Trading Subscription */
      var status = await BMAuth.getMembershipStatus(_user, _session);
      var plans = status.plans || [];
      var isCopyTrader = plans.includes('copytrading') || plans.includes('copy_trading') || plans.includes('all');
      var isPerm = ['bonfacewana3072@gmail.com','langatgift6@gmail.com','gackstoneb@gmail.com'].includes((_user.email||'').trim().toLowerCase());

      if (!isCopyTrader && !isPerm && status.level !== 'full') {
        window.location.href = 'subscribe.php?service=copy_trading&error=subscription_required';
        return;
      }

      /* Check URL params for payment success */
      var urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('payment_success') === '1') {
        showAlert('Payment verified! Please complete your MT5 account setup below.', 'success');
      }

      loadCopyTradingDetails();
    } catch(e) {
      console.error(e);
      window.location.href = 'subscribe.php?service=copy_trading&error=subscription_required';
    }
  });

  window.togglePasswordVisibility = function() {
    var passEl = document.getElementById('mt5Password');
    var eye = document.getElementById('eyeIcon');
    if (passEl.type === 'password') {
      passEl.type = 'text';
      eye.setAttribute('stroke', '#1677FF');
    } else {
      passEl.type = 'password';
      eye.setAttribute('stroke', 'currentColor');
    }
  };

  window.toggleForm = function() {
    var formCard = document.getElementById('ctFormCard');
    if (formCard.style.display === 'none') {
      formCard.style.display = 'block';
      formCard.scrollIntoView({ behavior: 'smooth' });
    } else {
      formCard.style.display = 'none';
    }
  };

  function showAlert(msg, type) {
    var el = document.getElementById('ctAlert');
    el.textContent = msg;
    el.className = 'ct-alert ' + (type || 'success');
  }

  async function loadCopyTradingDetails() {
    try {
      var token = _session ? _session.access_token : '';
      var resp = await fetch('api/copy-trading.php', {
        headers: { 'Authorization': 'Bearer ' + token }
      });
      if (resp.ok) {
        var res = await resp.json();
        if (res.data) {
          var d = res.data;
          document.getElementById('dispBroker').textContent = d.broker_name || '--';
          document.getElementById('dispLogin').textContent = d.mt5_login || '--';
          document.getElementById('dispServer').textContent = d.mt5_server || '--';
          document.getElementById('dispDate').textContent = d.created_at ? new Date(d.created_at).toLocaleDateString('en-GB') : '--';

          var badge = document.getElementById('ctStatusBadge');
          badge.textContent = d.status || 'Pending';
          badge.className = 'ct-status-badge ' + (d.status || 'Pending');

          document.getElementById('ctStatusCard').style.display = 'block';
          document.getElementById('formHeaderTitle').textContent = 'Update MT5 Account Information';

          // Pre-fill form
          document.getElementById('brokerName').value = d.broker_name || '';
          document.getElementById('mt5Login').value = d.mt5_login || '';
          document.getElementById('mt5Server').value = d.mt5_server || '';
          document.getElementById('notes').value = d.notes || '';
          document.getElementById('authCheck').checked = true;
          document.getElementById('ctFormCard').style.display = 'none';
        }
      }
    } catch(e) {}
  }

  window.submitForm = async function(e) {
    e.preventDefault();
    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = 'Submitting...';

    var payload = {
      broker_name: document.getElementById('brokerName').value,
      mt5_login: document.getElementById('mt5Login').value,
      mt5_password: document.getElementById('mt5Password').value,
      mt5_server: document.getElementById('mt5Server').value,
      notes: document.getElementById('notes').value
    };

    try {
      var token = _session ? _session.access_token : '';
      var resp = await fetch('api/copy-trading.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify(payload)
      });

      var res = await resp.json();
      if (res.ok) {
        showAlert(res.message || 'Your Copy Trading account has been successfully submitted. Our trading team will begin managing your account after verification.', 'success');
        document.getElementById('mt5Password').value = '';
        loadCopyTradingDetails();
        document.getElementById('ctFormCard').style.display = 'none';
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } else {
        showAlert(res.error || 'Failed to submit account details. Please try again.', 'error');
      }
    } catch(err) {
      showAlert('Network error. Please check your connection and try again.', 'error');
    }
    btn.disabled = false;
    btn.textContent = 'Submit Copy Trading Details';
  };
})();
</script>
<script src="js/motion.js" defer></script>
<script src="js/ai-assistant.js" defer></script>
<?php $activeTab = 'more'; include __DIR__ . '/components/mobile-tabbar.php'; ?>
</body>
</html>
