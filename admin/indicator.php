<?php
/**
 * BM Forex Hub — Admin Indicator Platform Subscribers
 * File: admin/indicator.php
 *
 * Provides indicator subscriber management, access overrides,
 * TradingView username registration/verification, subscription analytics,
 * and data exporting.
 */
require_once __DIR__ . '/config.php';
sb_admin_required();

// Ensure CSRF token exists
if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['admin_csrf'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="../css/theme-light.css?v=1">
<script src="../js/theme.js" defer></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Indicator Subscribers | BM Forex Hub Admin</title>
<link rel="icon" type="image/png" href="../BM-ForexHub-Logo-Circle.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600&display=swap">
<link rel="stylesheet" href="../css/motion.css?v=<?= filemtime('../css/motion.css') ?>">
<link rel="stylesheet" href="css/admin.css?v=<?= filemtime('css/admin.css') ?>">
<link rel="stylesheet" href="css/admin-mobile.css?v=<?= filemtime('css/admin-mobile.css') ?>">

<style>
body{font-family:'Inter',sans-serif;background:#0B0F14;color:#fff;margin:0;padding:24px}
h1{font-family:'Space Grotesk',sans-serif;font-size:1.3rem;margin:0}
.back-link{color:#1677FF;text-decoration:none;font-size:0.85rem;display:inline-flex;align-items:center;gap:4px;margin-bottom:16px}
.header-bar{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;}

/* Analytics Cards */
.analytics-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}
@media (max-width: 900px) { .analytics-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 500px) { .analytics-grid { grid-template-columns: 1fr; } }

.anal-card {
  background: #151d2a;
  border: 1px solid #283548;
  border-radius: 12px;
  padding: 18px 20px;
}
.anal-card__title { font-size: 0.72rem; color: #7f8b99; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; }
.anal-card__val { font-size: 1.6rem; font-weight: 700; color: #fff; font-family: 'Space Grotesk', sans-serif; margin-top: 4px; }
.anal-card__subtitle { font-size: 0.68rem; color: #16c784; margin-top: 2px; }

/* Filter Bar */
.filter-bar {
  display: flex;
  gap: 12px;
  margin-bottom: 16px;
  flex-wrap: wrap;
  align-items: center;
}
.filter-bar input, .filter-bar select {
  background: #202b3a;
  border: 1px solid #283548;
  border-radius: 8px;
  color: #fff;
  padding: 8px 12px;
  font-size: 0.83rem;
  outline: none;
  box-sizing: border-box;
}
.filter-bar input:focus, .filter-bar select:focus { border-color: #1677FF; }
.search-input { width: 280px; }
.plan-select, .status-select { width: 160px; }

.export-btn {
  background: transparent;
  color: #1677FF;
  border: 1px solid #1677FF;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.83rem;
  transition: all .2s;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.export-btn:hover { background: rgba(22,119,255,.08); }

/* Table styling */
table{width:100%;border-collapse:collapse;margin-top:12px}
th{text-align:left;font-size:0.7rem;text-transform:uppercase;color:#7F8B99;padding:12px 14px;border-bottom:1px solid #283548;letter-spacing:0.04em}
td{padding:12px 14px;border-bottom:1px solid rgba(40,53,72,.4);font-size:0.83rem;color:#E2E8F0}
tr:hover td{background:rgba(22,119,255,.03)}

.user-cell { display: flex; flex-direction: column; gap: 2px; }
.user-cell__name { font-weight: 600; color: #fff; }
.user-cell__email { font-size: 0.72rem; color: #7f8b99; font-family: 'IBM Plex Mono', monospace; }

.plan-badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; border: 1px solid; }
.plan-badge.silver { color: #b8c3d1; border-color: rgba(184,195,209,.3); background: rgba(184,195,209,.05); }
.plan-badge.gold { color: #f0b429; border-color: rgba(240,180,41,.3); background: rgba(240,180,41,.05); }
.plan-badge.vip { color: #8b5cf6; border-color: rgba(139,92,246,.3); background: rgba(139,92,246,.05); }

.status-badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; }
.status-badge.active { background: rgba(22,199,132,.15); color: #16C784; }
.status-badge.expired { background: rgba(246,70,93,.15); color: #F6465D; }
.status-badge.pending { background: rgba(240,180,41,.15); color: #F0B429; }
.status-badge.cancelled { background: rgba(100,116,139,.15); color: #7F8B99; }

.tv-username-cell { font-family: 'IBM Plex Mono', monospace; font-size: 0.78rem; font-weight: 600; }
.tv-username-cell.empty { color: #5b6475; font-style: italic; }

.action-btn{background:transparent;border:1px solid #283548;border-radius:6px;color:#B8C3D1;padding:5px 10px;cursor:pointer;font-size:0.72rem;margin-right:4px;transition:all .15s;}
.action-btn:hover{background:rgba(22,119,255,.1);color:#fff;border-color:#1677FF}
.action-btn.danger:hover{background:rgba(246,70,93,.1);border-color:#F6465D;color:#F6465D}
.action-btn.success:hover{background:rgba(22,199,132,.1);border-color:#16C784;color:#16C784}

.table-empty{text-align:center;padding:60px;color:#7F8B99;font-size:0.88rem}

/* Modal */
.modal-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.75);z-index:500;display:none;align-items:center;justify-content:center;backdrop-filter:blur(5px)}
.modal-overlay.open{display:flex}
.modal{background:#151D29;border:1px solid #283548;border-radius:14px;padding:28px;width:100%;max-width:440px;box-shadow:0 20px 50px rgba(0,0,0,.5)}
.modal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.modal-header h2{font-family:'Space Grotesk',sans-serif;font-size:1.1rem;margin:0}
.modal-close{background:none;border:none;color:#7F8B99;font-size:1.3rem;cursor:pointer}
.form-group { margin-bottom: 16px; }
.form-group label { display:block; font-size:0.75rem; color:#B8C3D1; margin-bottom:6px; font-weight:500; }
.btn-primary { background:#1677FF; color:#fff; border:none; padding:12px; border-radius:8px; font-weight:600; cursor:pointer; width:100%; font-size:0.88rem; transition:background .15s; }
.btn-primary:hover { background:#1565e0; }

.pagination { display:flex; justify-content:center; gap:6px; margin-top:24px; }
.page-btn { background:#151d2a; border:1px solid #283548; border-radius:6px; color:#fff; padding:6px 12px; cursor:pointer; font-size:0.78rem; }
.page-btn:hover { border-color:#1677FF; }
.page-btn.active { background:#1677FF; border-color:#1677FF; }
</style>
</head>
<body>

<a href="index.php" class="back-link">&larr; Back to Dashboard</a>

<div class="header-bar">
  <div>
    <h1>Indicator Platform Subscriptions</h1>
    <p style="font-size:0.78rem; color:#7f8b99; margin:4px 0 0;">Manage TradingView access grants and monitor subscriber levels.</p>
  </div>
  
  <a href="../api/admin/indicator-management.php?action=export_csv" class="export-btn" id="exportBtn">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
    Export CSV
  </a>
</div>

<!-- Analytics panel -->
<div class="analytics-grid">
  <div class="anal-card">
    <div class="anal-card__title">Total Active Members</div>
    <div class="anal-card__val" id="cntActive">0</div>
    <div class="anal-card__subtitle" id="cntTotal">0 Total registered</div>
  </div>
  <div class="anal-card">
    <div class="anal-card__title">Total Indicator Volume</div>
    <div class="anal-card__val" id="revUSD">$0</div>
    <div class="anal-card__subtitle" id="revKES">KES 0</div>
  </div>
  <div class="anal-card">
    <div class="anal-card__title">Quantum Edge Members</div>
    <div class="anal-card__val" id="cntVip">0</div>
    <div class="anal-card__subtitle">$299 One-Time Access</div>
  </div>
  <div class="anal-card">
    <div class="anal-card__title">Entitlement Type</div>
    <div class="anal-card__val" id="cntGoldSilver" style="font-size:1.15rem;margin-top:6px;">One-Time</div>
    <div class="anal-card__subtitle">Lifetime Algorithm Access</div>
  </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
  <input type="text" class="search-input" id="searchInput" placeholder="Search email, name or TV username..." oninput="loadData(1)">
  
  <select class="plan-select" id="planFilter" onchange="loadData(1)">
    <option value="">All Indicator Plans</option>
    <option value="indicator_quantum_edge">BM Quantum Edge ($299 One-Time)</option>
    <option value="indicator_vip">VIP (Legacy)</option>
    <option value="indicator_gold">Gold (Legacy)</option>
    <option value="indicator_silver">Silver (Legacy)</option>
  </select>

  <select class="status-select" id="statusFilter" onchange="loadData(1)">
    <option value="">All Statuses</option>
    <option value="active">Active</option>
    <option value="pending">Pending</option>
    <option value="expired">Expired</option>
    <option value="cancelled">Cancelled</option>
  </select>
</div>

<!-- Subscribers Table -->
<div style="overflow-x:auto;">
  <table>
    <thead>
      <tr>
        <th>Subscriber</th>
        <th>Indicator Plan</th>
        <th>Status</th>
        <th>TradingView Username</th>
        <th>Started At</th>
        <th>Expires At</th>
        <th style="text-align:right;">Actions</th>
      </tr>
    </thead>
    <tbody id="tableBody">
      <tr>
        <td colspan="7" class="table-empty">Loading subscribers...</td>
      </tr>
    </tbody>
  </table>
</div>

<div class="pagination" id="pagination"></div>

<!-- Username Edit Modal -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-header">
      <h2>Verify TradingView Access</h2>
      <button class="modal-close" onclick="closeModal()">&times;</button>
    </div>
    <form id="editForm" onsubmit="saveTv(event)">
      <input type="hidden" id="editSubId">
      
      <div class="form-group">
        <label for="modalEmail">Subscriber Email</label>
        <input type="text" id="modalEmail" disabled style="opacity:0.6;">
      </div>

      <div class="form-group">
        <label for="modalTvUser">TradingView Username</label>
        <input type="text" id="modalTvUser" placeholder="e.g. tradingstar_12" required autocomplete="off">
      </div>

      <div class="form-group" style="display:flex; align-items:center; gap:8px;">
        <input type="checkbox" id="modalVerified" style="width:auto; cursor:pointer;">
        <label for="modalVerified" style="margin-bottom:0; cursor:pointer;">Access Granted in TradingView</label>
      </div>

      <button type="submit" class="btn-primary">Update TV Settings</button>
    </form>
  </div>
</div>

<script>
var CSRF = '<?= $csrfToken ?>';
var API_URL = '../api/admin/indicator-management.php';
var state = { page: 1, limit: 25 };

async function loadAnalytics() {
  try {
    var resp = await fetch(API_URL + '?action=analytics');
    var data = await resp.json();
    
    document.getElementById('cntActive').textContent = data.active;
    document.getElementById('cntTotal').textContent = data.total + ' Total registered';
    document.getElementById('revUSD').textContent = '$' + data.total_revenue_usd.toLocaleString(undefined, {minimumFractionDigits: 0});
    document.getElementById('revKES').textContent = 'KES ' + data.total_revenue_kes.toLocaleString(undefined, {minimumFractionDigits: 0});
    
    var byPlan = data.by_plan || {};
    var vipCount = byPlan.indicator_vip ? byPlan.indicator_vip.active : 0;
    var goldCount = byPlan.indicator_gold ? byPlan.indicator_gold.active : 0;
    var silverCount = byPlan.indicator_silver ? byPlan.indicator_silver.active : 0;
    
    document.getElementById('cntVip').textContent = vipCount;
    document.getElementById('cntGoldSilver').textContent = goldCount + ' / ' + silverCount;
  } catch(e) {
    console.error('Analytics load error:', e);
  }
}

async function loadData(page) {
  state.page = page || state.page;
  var search = document.getElementById('searchInput').value;
  var plan = document.getElementById('planFilter').value;
  var status = document.getElementById('statusFilter').value;

  var url = API_URL + '?action=list&page=' + state.page + '&per_page=' + state.limit;
  if (search) url += '&search=' + encodeURIComponent(search);
  if (plan)   url += '&plan=' + encodeURIComponent(plan);
  if (status) url += '&status=' + encodeURIComponent(status);

  try {
    var resp = await fetch(url);
    var res = await resp.json();
    
    var tbody = document.getElementById('tableBody');
    if (!res.data || res.data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7" class="table-empty">No indicator subscribers found.</td></tr>';
      document.getElementById('pagination').innerHTML = '';
      return;
    }

    var html = '';
    res.data.forEach(function(item) {
      var planClass = item.plan_key.replace('indicator_', '');
      var starts = item.started_at ? new Date(item.started_at).toLocaleDateString('en-GB') : '--';
      var expires = item.expires_at ? new Date(item.expires_at).toLocaleDateString('en-GB') : '--';
      var tvUser = item.tradingview_username ? '@' + esc(item.tradingview_username) : 'Not Linked';
      var tvClass = item.tradingview_username ? 'tv-username-cell' : 'tv-username-cell empty';
      
      var tvBadge = '';
      if (item.tradingview_username) {
        tvBadge = item.tv_username_verified 
          ? '<span class="status-label verified" style="margin-left:4px;">Verified</span>' 
          : '<span class="status-label pending" style="margin-left:4px;">Pending Sync</span>';
      }

      // Action buttons
      var accessBtn = '';
      if (item.status === 'active' && item.indicator_access) {
        accessBtn = '<button class="action-btn danger" onclick="revokeAccess(\'' + item.id + '\')">Revoke Access</button>';
      } else {
        accessBtn = '<button class="action-btn success" onclick="grantAccess(\'' + item.id + '\')">Grant Access</button>';
      }

      html += '<tr>' +
        '<td><div class="user-cell">' +
          '<span class="user-cell__name">' + esc(item.user_name || 'No Name') + '</span>' +
          '<span class="user-cell__email">' + esc(item.user_email) + '</span>' +
        '</div></td>' +
        '<td><span class="plan-badge ' + planClass + '">' + esc(item.plan_name) + '</span></td>' +
        '<td><span class="status-badge ' + item.status + '">' + item.status + '</span></td>' +
        '<td><div style="display:flex; align-items:center;"><span class="' + tvClass + '">' + tvUser + '</span>' + tvBadge + '</div></td>' +
        '<td>' + starts + '</td>' +
        '<td>' + expires + '</td>' +
        '<td style="text-align:right;">' +
          '<button class="action-btn" onclick="openEditModal(\'' + item.id + '\', \'' + esc(item.user_email) + '\', \'' + esc(item.tradingview_username || '') + '\', ' + (item.tv_username_verified ? 1 : 0) + ')">Manage TV</button>' +
          accessBtn +
        '</td>' +
      '</tr>';
    });

    tbody.innerHTML = html;
    renderPagination(res.pages, res.page);
  } catch(e) {
    console.error('Subscribers table load error:', e);
  }
}

function renderPagination(totalPages, currentPage) {
  var pagDiv = document.getElementById('pagination');
  if (totalPages <= 1) { pagDiv.innerHTML = ''; return; }
  
  var html = '';
  for (var i = 1; i <= totalPages; i++) {
    var active = (i === currentPage) ? 'active' : '';
    html += '<button class="page-btn ' + active + '" onclick="loadData(' + i + ')">' + i + '</button>';
  }
  pagDiv.innerHTML = html;
}

// ── CRUD Actions ─────────────────────────────────────────────────────

async function grantAccess(subId) {
  if (!confirm('Are you sure you want to manually grant indicator access for this subscriber?')) return;
  try {
    var resp = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'grant', subscription_id: subId, csrf_token: CSRF })
    });
    var d = await resp.json();
    if (d.ok) {
      loadData();
      loadAnalytics();
    } else {
      alert(d.error || 'Failed to grant access');
    }
  } catch(e) {
    alert('Network error occurred.');
  }
}

async function revokeAccess(subId) {
  if (!confirm('Are you sure you want to manually revoke indicator access? The user dashboard will be locked immediately.')) return;
  try {
    var resp = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'revoke', subscription_id: subId, csrf_token: CSRF })
    });
    var d = await resp.json();
    if (d.ok) {
      loadData();
      loadAnalytics();
    } else {
      alert(d.error || 'Failed to revoke access');
    }
  } catch(e) {
    alert('Network error occurred.');
  }
}

function openEditModal(subId, email, tvUsername, verified) {
  document.getElementById('editSubId').value = subId;
  document.getElementById('modalEmail').value = email;
  document.getElementById('modalTvUser').value = tvUsername;
  document.getElementById('modalVerified').checked = !!verified;
  document.getElementById('editModal').classList.add('open');
}

function closeModal() {
  document.getElementById('editModal').classList.remove('open');
}

async function saveTv(e) {
  e.preventDefault();
  var subId = document.getElementById('editSubId').value;
  var tvUser = document.getElementById('modalTvUser').value.trim();
  var verified = document.getElementById('modalVerified').checked;

  try {
    var resp = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'update_tv',
        subscription_id: subId,
        tradingview_username: tvUser,
        verified: verified,
        csrf_token: CSRF
      })
    });
    var d = await resp.json();
    if (d.ok) {
      closeModal();
      loadData();
    } else {
      alert(d.error || 'Failed to update settings');
    }
  } catch(e) {
    alert('Network error occurred.');
  }
}

function esc(s) {
  var d = document.createElement('div');
  d.textContent = s || '';
  return d.innerHTML;
}

// Initial triggers
loadAnalytics();
loadData(1);
</script>

</body>
</html>
