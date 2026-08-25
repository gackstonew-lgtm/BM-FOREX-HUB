/**
 * BM Forex Hub — Trading Dashboard Frontend Component
 * File: js/trading-dashboard.js
 *
 * Coordinates authentication checking, TradingView Widget embedding,
 * real-time signal calculations polling, symbol/timeframe switching,
 * and watchlist rendering.
 */
(function() {
  'use strict';

  var state = {
    symbol: 'XAUUSD',
    timeframe: '60',
    access: null,
    tvWidget: null,
    pollInterval: null,
    isInitialLoad: true
  };

  var symbolToTV = {
    'XAUUSD': 'FX:XAUUSD',
    'EURUSD': 'FX:EURUSD',
    'GBPUSD': 'FX:GBPUSD',
    'USDJPY': 'FX:USDJPY',
    'BTCUSD': 'BINANCE:BTCUSDT',
    'NAS100': 'FOREXCOM:NAS100',
    'US30':   'FOREXCOM:SPX500' // or DJ:DJI
  };

  async function init() {
    showLoader(true);

    // Fallback safety timer: guarantees loading screen fades out after 2.5s even on slow network / API
    var safetyTimeout = setTimeout(function() {
      showLoader(false);
    }, 2500);

    try {
      if (typeof BMAuth === 'undefined') {
        throw new Error('BMAuth auth module not found');
      }
      var sessionRes = await BMAuth.getSession();
      if (!sessionRes || !sessionRes.session) {
        window.location.href = 'login.php';
        return;
      }

      var token = sessionRes.session.access_token;
      
      // Setup UI listeners & initialize TV widget immediately (non-blocking)
      setupEventListeners(token);
      initTVWidget();

      // Check subscription access with 4s timeout
      var access = await fetchAccessStatus(token);
      state.access = access;

      // If user is an authorized admin, initialize subscriber management
      if (access && access.is_admin) {
        initAdminSubscriberManagement(token);
      }

      if (access && !access.has_access) {
        showLockedOverlay(true);
        clearTimeout(safetyTimeout);
        showLoader(false);
        return;
      }

      // Fetch signal data in background so interface isn't blocked
      fetchSignalData(token).finally(function() {
        clearTimeout(safetyTimeout);
        showLoader(false);
      });

      // Start Polling (every 30 seconds)
      state.pollInterval = setInterval(function() {
        fetchSignalData(token);
      }, 30000);

    } catch(e) {
      console.error(e);
      clearTimeout(safetyTimeout);
      showLoader(false);
    }
  }

  async function fetchAccessStatus(token) {
    var resp = await fetch('api/indicator-access.php', {
      headers: { 'Authorization': 'Bearer ' + token }
    });
    if (!resp.ok) {
      if (resp.status === 401) {
        window.location.href = 'login.php';
        return;
      }
      throw new Error('Failed to verify subscription status');
    }
    return await resp.json();
  }

  async function fetchSignalData(token) {
    try {
      var url = 'api/trading-signals.php?symbol=' + encodeURIComponent(state.symbol) + '&tf=' + encodeURIComponent(state.timeframe);
      var resp = await fetch(url, {
        headers: { 'Authorization': 'Bearer ' + token }
      });
      if (resp.status === 403) {
        // Access revoked or expired
        clearInterval(state.pollInterval);
        showLockedOverlay(true);
        return;
      }
      if (!resp.ok) return;
      var data = await resp.json();
      updateUI(data);
    } catch(e) {
      console.error('Signal fetch error:', e);
    }
  }

  function initTVWidget() {
    var tvSymbol = symbolToTV[state.symbol] || 'FX:XAUUSD';
    
    // Convert tf to TradingView structure
    var tvInterval = '60';
    if (state.timeframe === '1')   tvInterval = '1';
    if (state.timeframe === '5')   tvInterval = '5';
    if (state.timeframe === '15')  tvInterval = '15';
    if (state.timeframe === '30')  tvInterval = '30';
    if (state.timeframe === '60')  tvInterval = '60';
    if (state.timeframe === '240') tvInterval = '240';
    if (state.timeframe === 'D')   tvInterval = 'D';
    if (state.timeframe === 'W')   tvInterval = 'W';

    if (state.tvWidget) {
      // If widget exists, just change the symbol/interval to avoid reload lag
      try {
        state.tvWidget.chart().setSymbol(tvSymbol);
        state.tvWidget.chart().setInterval(tvInterval);
        return;
      } catch(e) {
        // Fallback to recreate if chart interface not ready
      }
    }

    if (typeof TradingView === 'undefined') {
      console.error('TradingView library not loaded');
      return;
    }

    state.tvWidget = new TradingView.widget({
      "autosize": true,
      "symbol": tvSymbol,
      "interval": tvInterval,
      "timezone": "Etc/UTC",
      "theme": "dark",
      "style": "1",
      "locale": "en",
      "enable_publishing": false,
      "hide_side_toolbar": false,
      "allow_symbol_change": false,
      "container_id": "tvChartContainer",
      "studies": [
        "RSI@tv-basicstudies",
        "MASimple@tv-basicstudies"
      ],
      "disabled_features": [
        "header_compare",
        "header_undo_redo"
      ],
      "enabled_features": [
        "use_localstorage_for_settings"
      ],
      "loading_screen": {
        "backgroundColor": "#101722",
        "foregroundColor": "#1677FF"
      }
    });
  }

  function updateUI(data) {
    // 1. Update Hero Details
    document.getElementById('lblSymbol').textContent = data.symbol;
    
    // Plan Badge
    var planBadge = document.getElementById('planBadge');
    planBadge.className = 'td-plan-badge td-plan-badge--' + data.access.plan_key.replace('indicator_', '');
    planBadge.textContent = data.access.plan_name;

    // Expiry Badge
    var expiryBadge = document.getElementById('expiryBadge');
    expiryBadge.textContent = 'EXPIRES: ' + formatDate(data.access.expires_at);
    if (data.access.days_remaining <= 3) {
      expiryBadge.classList.add('urgent');
    } else {
      expiryBadge.classList.remove('urgent');
    }

    // 2. Trend & Signals Column
    // Direction Badge
    var dirBadge = document.getElementById('dirBadge');
    dirBadge.className = 'td-direction-badge ' + data.direction.toLowerCase();
    
    var dirIcon = '';
    if (data.direction === 'BULLISH') dirIcon = '▲ ';
    if (data.direction === 'BEARISH') dirIcon = '▼ ';
    dirBadge.textContent = dirIcon + data.direction;

    // Signal Pill
    var sigPill = document.getElementById('sigPill');
    sigPill.className = 'td-signal-pill ' + data.signal.toLowerCase();
    sigPill.querySelector('.td-sig-val').textContent = data.signal;

    // Strength
    document.getElementById('strengthVal').textContent = data.strength + '%';
    document.getElementById('strengthFill').style.width = data.strength + '%';

    // Levels
    document.getElementById('valPrice').textContent = formatPrice(data.price, data.symbol);
    document.getElementById('valEntry').textContent = data.entry ? formatPrice(data.entry, data.symbol) : 'Awaiting';
    
    var slVal = document.getElementById('valSL');
    slVal.textContent = data.stop_loss ? formatPrice(data.stop_loss, data.symbol) : 'Awaiting';
    
    var tpVal = document.getElementById('valTP');
    tpVal.textContent = data.take_profit_2 ? formatPrice(data.take_profit_2, data.symbol) : 'Awaiting';

    document.getElementById('valRR').textContent = data.risk_reward ? '1:' + data.risk_reward : 'N/A';
    document.getElementById('valBias').textContent = data.bias || 'Ranging';

    // RSI Pointer position
    if (data.rsi) {
      document.getElementById('rsiVal').textContent = 'RSI(14): ' + data.rsi;
      document.getElementById('rsiPointer').style.left = data.rsi + '%';
    }

    // EMA indicators
    document.getElementById('valEma200').textContent = data.ema_200 ? formatPrice(data.ema_200, data.symbol) : '0.00';
    document.getElementById('valEma50').textContent = data.ema_50 ? formatPrice(data.ema_50, data.symbol) : '0.00';

    // Sessions Panel
    var sessions = data.sessions;
    for (var s in sessions) {
      var badge = document.getElementById('sess_' + s);
      if (badge) {
        if (sessions[s].active) {
          badge.classList.add('active');
        } else {
          badge.classList.remove('active');
        }
      }
    }

    // Watchlist Updates
    renderWatchlist(data.watchlist);

    // Update Refresh Clock
    document.getElementById('refreshClock').textContent = 'Live • Updated ' + new Date().toLocaleTimeString();
  }

  function renderWatchlist(list) {
    var container = document.getElementById('watchlistContainer');
    if (!container) return;
    container.innerHTML = '';
    
    list.forEach(function(item) {
      var row = document.createElement('div');
      row.className = 'td-wl-row' + (item.symbol === state.symbol ? ' active' : '');
      row.onclick = function() {
        selectSymbol(item.symbol);
      };

      var name = document.createElement('span');
      name.className = 'td-wl-sym';
      name.textContent = item.symbol;

      var price = document.createElement('span');
      price.className = 'td-wl-price';
      price.textContent = formatPrice(item.price, item.symbol);

      var chg = document.createElement('span');
      chg.className = 'td-wl-chg ' + (item.change >= 0 ? 'pos' : 'neg');
      chg.textContent = (item.change >= 0 ? '+' : '') + item.change_pct + '%';

      row.appendChild(name);
      row.appendChild(price);
      row.appendChild(chg);
      container.appendChild(row);
    });
  }

  function selectSymbol(symbol) {
    if (symbol === state.symbol) return;
    state.symbol = symbol;
    
    // Update active class on symbol selectors
    var btns = document.querySelectorAll('.td-sym-btn');
    btns.forEach(function(b) {
      if (b.dataset.sym === symbol) {
        b.classList.add('active');
      } else {
        b.classList.remove('active');
      }
    });

    initTVWidget();
    
    // Trigger immediate API call
    if (typeof BMAuth !== 'undefined') {
      BMAuth.getSession().then(function(res) {
        if (res && res.session) fetchSignalData(res.session.access_token);
      });
    }
  }

  function selectTimeframe(tf) {
    if (tf === state.timeframe) return;
    state.timeframe = tf;

    // Update active state on buttons
    var btns = document.querySelectorAll('.td-tf-btn');
    btns.forEach(function(b) {
      if (b.dataset.tf === tf) {
        b.classList.add('active');
      } else {
        b.classList.remove('active');
      }
    });

    initTVWidget();

    // Trigger immediate API call
    if (typeof BMAuth !== 'undefined') {
      BMAuth.getSession().then(function(res) {
        if (res && res.session) fetchSignalData(res.session.access_token);
      });
    }
  }

  function setupEventListeners(token) {
    // Symbol buttons
    var symBtns = document.querySelectorAll('.td-sym-btn');
    symBtns.forEach(function(btn) {
      btn.onclick = function() {
        selectSymbol(btn.dataset.sym);
      };
    });

    // Timeframe buttons
    var tfBtns = document.querySelectorAll('.td-tf-btn');
    tfBtns.forEach(function(btn) {
      btn.onclick = function() {
        selectTimeframe(btn.dataset.tf);
      };
    });

    // TradingView username sync button/form
    var tvForm = document.getElementById('tvUsernameForm');
    if (tvForm) {
      tvForm.onsubmit = async function(e) {
        e.preventDefault();
        var input = document.getElementById('tvUsernameInput');
        var btn = document.getElementById('tvUsernameBtn');
        var username = input.value.trim();
        if (!username) return;

        btn.disabled = true;
        btn.textContent = 'Updating...';

        try {
          var resp = await fetch('api/admin/indicator-management.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
              action: 'update_tv',
              subscription_id: state.access.subscription_id,
              tradingview_username: username
            })
          });
          var res = await resp.json();
          if (res.ok) {
            showNotification('Success', 'TradingView username updated! Our team will grant Pine Script access shortly.');
            input.disabled = true;
            btn.style.display = 'none';
            document.getElementById('tvStatusLabel').textContent = 'Pending Verification';
            document.getElementById('tvStatusLabel').className = 'status-label pending';
          } else {
            showNotification('Error', res.error || 'Failed to update username.');
            btn.disabled = false;
            btn.textContent = 'Sync';
          }
        } catch(err) {
          showNotification('Error', 'An error occurred during update.');
          btn.disabled = false;
          btn.textContent = 'Sync';
        }
      };
    }
  }

  // ── UI Helpers ───────────────────────────────────────────────────────

  function showLoader(show) {
    var loader = document.getElementById('tdLoader');
    if (!loader) return;
    if (show) {
      loader.classList.remove('fade-out');
      loader.style.display = 'flex';
    } else {
      loader.classList.add('fade-out');
      setTimeout(function() {
        if (loader.classList.contains('fade-out')) {
          loader.style.display = 'none';
        }
      }, 400);
    }
  }

  function showLockedOverlay(show) {
    var overlay = document.getElementById('tdLockedOverlay');
    if (overlay) overlay.style.display = show ? 'flex' : 'none';
  }

  function showError(msg) {
    alert(msg);
  }

  function showNotification(title, msg) {
    alert(title + ': ' + msg);
  }

  function formatDate(isoStr) {
    if (!isoStr) return 'N/A';
    try {
      var d = new Date(isoStr);
      return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
    } catch(e) {
      return isoStr;
    }
  }

  function formatPrice(val, symbol) {
    var dec = 5;
    var sym = symbol.toUpperCase();
    if (sym.includes('JPY')) dec = 3;
    if (sym === 'XAUUSD' || sym === 'GOLD') dec = 2;
    if (sym === 'BTCUSD' || sym === 'NAS100' || sym === 'US30') dec = 1;
    return Number(val).toFixed(dec);
  }

  // ── Admin Quantum Edge Subscriber Management ──────────────────────────────
  var adminSubState = {
    page: 1,
    perPage: 20,
    search: '',
    plan: '',
    status: '',
    token: ''
  };

  function initAdminSubscriberManagement(token) {
    adminSubState.token = token;
    var adminSection = document.getElementById('adminIndicatorSection');
    if (!adminSection) return;
    adminSection.style.display = 'block';

    // Event listeners for search and filters
    var searchInput = document.getElementById('indSearchInput');
    var planFilter = document.getElementById('indPlanFilter');
    var statusFilter = document.getElementById('indStatusFilter');
    var refreshBtn = document.getElementById('indRefreshBtn');
    var prevBtn = document.getElementById('indPrevPageBtn');
    var nextBtn = document.getElementById('indNextPageBtn');

    var searchTimer = null;
    if (searchInput) {
      searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
          adminSubState.search = searchInput.value.trim();
          adminSubState.page = 1;
          fetchAdminSubscribers();
        }, 300);
      });
    }

    if (planFilter) {
      planFilter.addEventListener('change', function() {
        adminSubState.plan = planFilter.value;
        adminSubState.page = 1;
        fetchAdminSubscribers();
      });
    }

    if (statusFilter) {
      statusFilter.addEventListener('change', function() {
        adminSubState.status = statusFilter.value;
        adminSubState.page = 1;
        fetchAdminSubscribers();
      });
    }

    if (refreshBtn) {
      refreshBtn.addEventListener('click', function() {
        fetchAdminSubscribers();
      });
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', function() {
        if (adminSubState.page > 1) {
          adminSubState.page--;
          fetchAdminSubscribers();
        }
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', function() {
        adminSubState.page++;
        fetchAdminSubscribers();
      });
    }

    fetchAdminSubscribers();
  }

  async function fetchAdminSubscribers() {
    var tbody = document.getElementById('indSubscribersTbody');
    if (tbody) {
      tbody.innerHTML = '<tr><td colspan="8" class="td-table-empty">Loading indicator subscribers...</td></tr>';
    }

    var qs = new URLSearchParams({
      action: 'list',
      page: adminSubState.page,
      per_page: adminSubState.perPage,
      search: adminSubState.search,
      plan: adminSubState.plan,
      status: adminSubState.status
    }).toString();

    var endpoints = [
      'api/admin/indicator-management.php?' + qs,
      'api/indicator-management.php?' + qs,
      '../api/admin/indicator-management.php?' + qs
    ];

    var data = null;
    for (var i = 0; i < endpoints.length; i++) {
      try {
        var r = await fetch(endpoints[i], {
          headers: {
            'Authorization': 'Bearer ' + adminSubState.token,
            'Accept': 'application/json'
          }
        });
        if (r.ok) {
          data = await r.json();
          break;
        }
      } catch (e) {
        // try next endpoint
      }
    }

    if (!data || !Array.isArray(data.data)) {
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="8" class="td-table-empty" style="color:#FF6B6B;">Unable to load BM Quantum Edge subscribers. Please try again.</td></tr>';
      }
      return;
    }

    renderAdminSubscribers(data);
  }

  function renderAdminSubscribers(res) {
    var items = res.data || [];
    var total = res.total || 0;
    var page = res.page || 1;
    var perPage = res.per_page || 20;
    var totalPages = res.pages || Math.max(1, Math.ceil(total / perPage));

    // Update KPI Summary Metrics
    var activeCount = 0;
    var expCount = 0;
    var totalRevenueUsd = 0;

    items.forEach(function(item) {
      var isExp = item.expires_at ? new Date(item.expires_at).getTime() < Date.now() : false;
      if (item.status === 'active' && !isExp) {
        activeCount++;
      } else {
        expCount++;
      }
      totalRevenueUsd += parseFloat(item.amount_paid_usd || 0);
    });

    var elTotal = document.getElementById('indTotalSubscribers');
    if (elTotal) elTotal.textContent = total.toLocaleString();
    var elActive = document.getElementById('indActiveSubscribers');
    if (elActive) elActive.textContent = activeCount.toLocaleString();
    var elExp = document.getElementById('indExpiredSubscribers');
    if (elExp) elExp.textContent = expCount.toLocaleString();
    var elRev = document.getElementById('indTotalRevenue');
    if (elRev) elRev.textContent = '$' + totalRevenueUsd.toLocaleString() + ' USD';

    // Update Pagination UI
    var showingEl = document.getElementById('indShowingCount');
    if (showingEl) {
      var start = total === 0 ? 0 : (page - 1) * perPage + 1;
      var end = Math.min(page * perPage, total);
      showingEl.textContent = 'Showing ' + start + ' - ' + end + ' of ' + total.toLocaleString() + ' subscribers';
    }

    var indicatorEl = document.getElementById('indPageIndicator');
    if (indicatorEl) indicatorEl.textContent = 'Page ' + page + ' of ' + totalPages;

    var prevBtn = document.getElementById('indPrevPageBtn');
    if (prevBtn) prevBtn.disabled = (page <= 1);
    var nextBtn = document.getElementById('indNextPageBtn');
    if (nextBtn) nextBtn.disabled = (page >= totalPages);

    // Render Table Rows
    var tbody = document.getElementById('indSubscribersTbody');
    if (!tbody) return;

    if (!items.length) {
      tbody.innerHTML = '<tr><td colspan="8" class="td-table-empty">No BM Quantum Edge subscribers found.</td></tr>';
      return;
    }

    tbody.innerHTML = items.map(function(sub) {
      var isExp = sub.expires_at ? new Date(sub.expires_at).getTime() < Date.now() : false;
      var statusLabel = isExp ? 'expired' : (sub.status || 'active');
      var statusCls = isExp ? 'expired' : (statusLabel === 'active' ? 'active' : 'pending');

      var planKey = sub.plan_key || 'indicator_quantum_edge';
      var planTierCls = 'vip';
      var planName = sub.plan_name || 'BM Quantum Edge ($299)';

      var paidUsd = '$' + Number(sub.amount_paid_usd || 299).toLocaleString();
      var paidKes = sub.amount_paid_kes ? ' / KES ' + Number(sub.amount_paid_kes).toLocaleString() : '';

      var tvUser = sub.tradingview_username ? ('<span style="color:#60A5FA;font-weight:600;">@' + escapeHtml(sub.tradingview_username) + '</span>') : '<span style="color:#64748B;">Not Linked</span>';

      return '<tr>'
        + '<td><strong>@' + escapeHtml(sub.user_name || 'Subscriber') + '</strong><br><small style="color:#7F8B99;">' + escapeHtml(sub.user_email || '--') + '</small></td>'
        + '<td><span class="plan-tier-badge ' + planTierCls + '">' + escapeHtml(planName) + '</span></td>'
        + '<td style="color:#16C784;font-weight:600;">' + paidUsd + '<small style="color:#7F8B99;font-weight:400;">' + paidKes + '</small></td>'
        + '<td>' + tvUser + '</td>'
        + '<td><span class="sub-status-pill ' + statusCls + '">' + escapeHtml(statusLabel.toUpperCase()) + '</span></td>'
        + '<td style="color:#8FA3B8;">' + formatDate(sub.created_at || sub.starts_at) + '</td>'
        + '<td style="font-weight:500;">' + formatDate(sub.expires_at) + '</td>'
        + '<td>'
        + '<div style="display:flex;gap:4px;">'
        + '<button type="button" class="action-btn" title="Grant / Extend Access" onclick="adminGrantAccess(\'' + escapeHtml(sub.id) + '\')">+Access</button>'
        + '<button type="button" class="action-btn danger" title="Revoke Access" onclick="adminRevokeAccess(\'' + escapeHtml(sub.id) + '\')">&times;</button>'
        + '</div>'
        + '</td>'
        + '</tr>';
    }).join('');
  }

  window.adminGrantAccess = async function(subId) {
    if (!confirm('Grant / verify indicator access for this subscriber?')) return;
    try {
      var r = await fetch('api/admin/indicator-management.php', {
        method: 'POST',
        headers: {
          'Authorization': 'Bearer ' + adminSubState.token,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ action: 'grant', subscription_id: subId })
      });
      var res = await r.json();
      if (res && res.ok) {
        alert(res.message || 'Access granted successfully.');
        fetchAdminSubscribers();
      } else {
        alert('Error: ' + ((res && res.error) || 'Failed to grant access'));
      }
    } catch (e) {
      alert('Request error: ' + e.message);
    }
  };

  window.adminRevokeAccess = async function(subId) {
    if (!confirm('Are you sure you want to revoke access for this subscriber?')) return;
    try {
      var r = await fetch('api/admin/indicator-management.php', {
        method: 'POST',
        headers: {
          'Authorization': 'Bearer ' + adminSubState.token,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ action: 'revoke', subscription_id: subId })
      });
      var res = await r.json();
      if (res && res.ok) {
        alert(res.message || 'Access revoked successfully.');
        fetchAdminSubscribers();
      } else {
        alert('Error: ' + ((res && res.error) || 'Failed to revoke access'));
      }
    } catch (e) {
      alert('Request error: ' + e.message);
    }
  };

  function escapeHtml(str) {
    return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  // Run on load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
