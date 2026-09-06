/* ============================================================
   BM Forex Hub — Admin Dashboard Core JS
   ============================================================ */
(function(){
  'use strict';

  /* ── Supabase Config (from config.php defines) ── */
  var SB_URL  = 'https://iwoytmcxmbhmmbrbpzvf.supabase.co';
  var SB_KEY  = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml3b3l0bWN4bWJobW1icmJwenZmIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc4Mzk4MzU2MSwiZXhwIjoyMDk5NTU5NTYxfQ.1ExI-o8zwDkUIH_loQPC_ZJhJaDgrB6gSf1KgAdigCU';

  function sbGet(path, params) {
    var url = SB_URL + '/rest/v1/' + path;
    if (params) url += '?' + params;
    return fetch(url, { headers: { 'apikey': SB_KEY, 'Authorization': 'Bearer ' + SB_KEY } })
      .then(function(r){ return r.json(); });
  }
  function sbGetAll(path, select, pageSize) {
    pageSize = pageSize || 1000;
    var all = [];
    function fetchPage(offset) {
      var url = SB_URL + '/rest/v1/' + path + '?select=' + (select||'*') + '&order=created_at.desc&limit=' + pageSize + '&offset=' + offset;
      return fetch(url, {
        headers: { 'apikey': SB_KEY, 'Authorization': 'Bearer ' + SB_KEY, 'Prefer': 'count=exact' }
      }).then(function(r){
        return r.json().then(function(data){
          if (!Array.isArray(data)) return all;
          all = all.concat(data);
          if (data.length === pageSize) return fetchPage(all.length).catch(function(){ return all; });
          return all;
        });
      }).catch(function(e){ console.error('sbGetAll fetch error at offset ' + offset + ':', e); return all; });
    }
    return fetchPage(0);
  }
  function sbPost(path, body, method) {    return fetch(SB_URL + '/rest/v1/' + path, {
      method: method || 'POST',
      headers: { 'apikey': SB_KEY, 'Authorization': 'Bearer ' + SB_KEY, 'Content-Type': 'application/json', 'Prefer': 'return=representation' },
      body: JSON.stringify(body)
    }).then(function(r){ return r.json(); });
  }
  function sbPatch(path, body) { return sbPost(path, body, 'PATCH'); }
  function sbDelete(path) {
    return fetch(SB_URL + '/rest/v1/' + path, {
      method: 'DELETE',
      headers: { 'apikey': SB_KEY, 'Authorization': 'Bearer ' + SB_KEY }
    }).then(function(r){ return r.json(); });
  }

  function esc(s){ return (s||'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
  function fmtDate(d){ return d ? new Date(d).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : '--'; }
  function fmtDateTime(d){ return d ? new Date(d).toLocaleString('en-GB',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) : '--'; }

  var DIAL_NAMES = {
    '+254':'Kenya','+234':'Nigeria','+1':'United States / Canada','+44':'United Kingdom',
    '+233':'Ghana','+27':'South Africa','+255':'Tanzania','+256':'Uganda','+20':'Egypt',
    '+212':'Morocco','+251':'Ethiopia','+237':'Cameroon','+221':'Senegal','+225':'Ivory Coast',
    '+213':'Algeria','+216':'Tunisia','+243':'DR Congo','+258':'Mozambique','+260':'Zambia',
    '+263':'Zimbabwe','+250':'Rwanda','+267':'Botswana','+230':'Mauritius','+261':'Madagascar',
    '+244':'Angola','+61':'Australia','+49':'Germany','+33':'France','+39':'Italy',
    '+34':'Spain','+351':'Portugal','+31':'Netherlands','+32':'Belgium','+41':'Switzerland',
    '+46':'Sweden','+47':'Norway','+45':'Denmark','+358':'Finland','+353':'Ireland',
    '+43':'Austria','+48':'Poland','+420':'Czech Republic','+40':'Romania','+36':'Hungary',
    '+30':'Greece','+90':'Turkey','+7':'Russia','+380':'Ukraine','+972':'Israel',
    '+971':'United Arab Emirates','+966':'Saudi Arabia','+974':'Qatar','+965':'Kuwait',
    '+973':'Bahrain','+968':'Oman','+962':'Jordan','+961':'Lebanon','+964':'Iraq',
    '+91':'India','+92':'Pakistan','+880':'Bangladesh','+94':'Sri Lanka','+977':'Nepal',
    '+95':'Myanmar','+66':'Thailand','+84':'Vietnam','+62':'Indonesia','+63':'Philippines',
    '+60':'Malaysia','+65':'Singapore','+852':'Hong Kong','+86':'China','+81':'Japan','+82':'South Korea',
    '+52':'Mexico','+55':'Brazil','+54':'Argentina','+56':'Chile','+57':'Colombia',
    '+51':'Peru','+593':'Ecuador','+58':'Venezuela','+506':'Costa Rica','+507':'Panama',
    '+502':'Guatemala','+504':'Honduras','+505':'Nicaragua','+503':'El Salvador',
    '+53':'Cuba','+1876':'Jamaica','+1868':'Trinidad and Tobago','+509':'Haiti',
    '+1809':'Dominican Republic','+1787':'Puerto Rico','+64':'New Zealand','+679':'Fiji',
    '+220':'Gambia','+226':'Burkina Faso','+227':'Niger','+228':'Togo','+229':'Benin',
    '+231':'Liberia','+232':'Sierra Leone','+235':'Chad','+236':'Central African Republic',
    '+238':'Cape Verde','+239':'Sao Tome','+240':'Equatorial Guinea','+241':'Gabon',
    '+242':'Congo','+245':'Guinea-Bissau','+246':'British Indian Ocean Territory',
    '+248':'Seychelles','+249':'Sudan','+252':'Somalia','+253':'Djibouti',
    '+257':'Burundi','+262':'Reunion','+264':'Namibia','+265':'Malawi',
    '+266':'Lesotho','+268':'Eswatini','+269':'Comoros'
  };
  function countryName(code) {
    if (!code) return '--';
    var key = code.toString().trim();
    if (DIAL_NAMES[key]) return DIAL_NAMES[key];
    if (DIAL_NAMES['+' + key]) return DIAL_NAMES['+' + key];
    return key;
  }

  /* ── Sidebar Navigation ── */
  var sectionMap = {
    'dashboard':          'sectionDashboard',
    'users':              'sectionUsers',
    'payments':           'sectionPayments',
    'elite-subscriptions':'sectionEliteSubscriptions',
    'copy-traders':       'sectionCopyTraders',
    'market-overview':    'sectionMarketOverview',
    'articles':           'sectionArticles',
    'videos':             'sectionVideos',
    'promotions':         'sectionPromotions',
    'featured-signals':   'sectionFeaturedSignals',
    'announcements':      'sectionAnnouncements',
    'live-classes':       'sectionLiveClasses',
    'bulk-notifications': 'sectionBulkNotifications',
    'registered-contacts':'sectionRegisteredContacts',
    'ai-knowledge':       'sectionAiKnowledge',
    'ai-settings':        'sectionAiSettings',
    'ai-conversations':   'sectionAiConversations',
    'content':            'sectionContent'
  };

  var titleMap = {
    'dashboard':'Dashboard','users':'Users','payments':'Payments',
    'elite-subscriptions':'Elite Subscriptions','copy-traders':'Copy Traders',
    'market-overview':'Market Overview','articles':'Educational Articles',
    'videos':'Featured Videos','promotions':'Promotions','featured-signals':'Featured Signals',
    'announcements':'Announcements','live-classes':'Live Classes',
    'bulk-notifications':'Bulk Notifications','registered-contacts':'Registered Contacts',
    'ai-knowledge':'AI Knowledge Base','ai-settings':'AI Settings',
    'ai-conversations':'AI Conversations','content':'Site Content'
  };

  var loaded = {};

  function showSection(name) {
    // Hide all sections
    document.querySelectorAll('.section').forEach(function(s){ s.style.display='none'; });
    // Deactivate all sidebar links
    document.querySelectorAll('.sidebar-link').forEach(function(l){ l.classList.remove('active'); });

    var secId = sectionMap[name];
    var sec = secId ? document.getElementById(secId) : null;
    if (sec) {
      sec.style.display = '';
      document.getElementById('pageTitle').textContent = titleMap[name] || name;
      // Activate sidebar link
      var link = document.querySelector('.sidebar-link[data-section="'+name+'"]');
      if (link) link.classList.add('active');
      // Load data on first visit
      if (!loaded[name]) {
        loaded[name] = true;
        if (name === 'users') loadUsers();
        else if (name === 'payments') loadPayments();
        else if (name === 'elite-subscriptions') loadEliteSubscriptions();
        else if (name === 'dashboard') loadDashboard();
        else if (name === 'articles') loadArticles();
        else if (name === 'videos') loadVideos();
        else if (name === 'promotions') loadPromotions();
        else if (name === 'market-overview') loadMO();
        else if (name === 'featured-signals') loadSignals();
        else if (name === 'announcements') loadAnnouncements();
        else if (name === 'live-classes') loadLiveClasses();
        else if (name === 'registered-contacts') loadContacts();
        else if (name === 'ai-knowledge') loadAiKb();
        else if (name === 'ai-conversations') loadAiLogs();
        else if (name === 'ai-settings') loadAiSettings();
      }
    }
  }

  // Bind sidebar links
  document.querySelectorAll('.sidebar-link').forEach(function(link){
    link.addEventListener('click', function(e){
      e.preventDefault();
      var sec = this.getAttribute('data-section');
      if (sec) showSection(sec);
      // Close mobile sidebar
      document.getElementById('sidebar').classList.remove('open');
      document.getElementById('sidebarOverlay').classList.remove('show');
    });
  });

  // Sidebar toggle (mobile drawer)
  var toggle = document.getElementById('sidebarToggle');
  if (toggle) toggle.addEventListener('click', function(){
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
  });
  var overlay = document.getElementById('sidebarOverlay');
  if (overlay) overlay.addEventListener('click', function(){
    document.getElementById('sidebar').classList.remove('open');
    this.classList.remove('show');
  });

  // Sidebar Collapse / Expand (Reference Image 2 Persistent Layout)
  try {
    if (localStorage.getItem('bmSidebarCollapsed') === 'true') {
      document.body.classList.add('sidebar-collapsed');
    }
  } catch(e) {}

  var collapseBtn = document.getElementById('adminSidebarCollapseBtn');
  if (collapseBtn) {
    collapseBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      var isCollapsed = document.body.classList.toggle('sidebar-collapsed');
      try {
        localStorage.setItem('bmSidebarCollapsed', isCollapsed ? 'true' : 'false');
      } catch(err) {}
    });
  }

  /* ── Theme Toggle ── */
  document.querySelectorAll('.theme-toggle').forEach(function(btn){
    btn.addEventListener('click', function(){
      var isLight = document.documentElement.getAttribute('data-theme') === 'light';
      document.documentElement.setAttribute('data-theme', isLight ? '' : 'light');
      try { localStorage.setItem('bmTheme', isLight ? 'dark' : 'light'); } catch(e){}
    });
  });

  /* ── Modal Helpers ── */
  window.openModal = function(id){ var m = document.getElementById(id); if(m) m.classList.add('show'); };
  window.closeModal = function(id){ var m = document.getElementById(id); if(m) m.classList.remove('show'); };
  document.querySelectorAll('.modal-close').forEach(function(btn){
    btn.addEventListener('click', function(){
      var m = this.closest('.modal-overlay');
      if (m) m.classList.remove('show');
    });
  });
  document.querySelectorAll('.modal-overlay').forEach(function(m){
    m.addEventListener('click', function(e){ if(e.target===this) this.classList.remove('show'); });
  });

  /* ── Dashboard Stats ── */
  function loadDashboard() {
    sbGetAll('profiles', 'id,created_at').then(function(profiles){
      var profileList = Array.isArray(profiles) ? profiles : [];
      return fetchAllAuthUsers().then(function(authUsers){
        var authList = Array.isArray(authUsers) ? authUsers : [];
        var profileMap = {};
        profileList.forEach(function(p){ if(p.id) profileMap[p.id] = p; });

        var merged = profileList.slice();
        authList.forEach(function(au){
          if(au.id && !profileMap[au.id]){
            merged.push({
              id: au.id,
              created_at: au.created_at || au.confirmed_at || new Date().toISOString()
            });
          }
        });

        var now = Date.now();
        var weekAgo = now - 7*86400000;
        var todayStart = new Date(); todayStart.setHours(0,0,0,0);
        var total = merged.length;
        var newWeek = merged.filter(function(u){ return u.created_at && new Date(u.created_at).getTime() > weekAgo; }).length;
        var newToday = merged.filter(function(u){ return u.created_at && new Date(u.created_at).getTime() >= todayStart.getTime(); }).length;
        document.getElementById('statTotal').textContent = total;
        document.getElementById('statActive').textContent = newWeek;
        document.getElementById('statNewWeek').textContent = newWeek;
        document.getElementById('statNewToday').textContent = newToday;
        buildTrendChart(merged);
      }).catch(function(){
        var total = profileList.length;
        document.getElementById('statTotal').textContent = total;
        buildTrendChart(profileList);
      });
    }).catch(function(e){ console.error('Dashboard load error:', e); });
  }

  /* ── Modern Financial Spline Area Graph Engine ── */
  var trendChartState = {
    users: [],
    days: 30,
    hoverIdx: -1,
    points: []
  };

  var _controlsBound = false;
  function initTrendChartControls() {
    if (_controlsBound) return;
    _controlsBound = true;
    document.querySelectorAll('.chart-range-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        document.querySelectorAll('.chart-range-btn').forEach(function(b){ b.classList.remove('active'); });
        this.classList.add('active');
        trendChartState.days = parseInt(this.dataset.days, 10) || 30;
        renderTrendGraph();
      });
    });

    window.addEventListener('resize', function() {
      if (trendChartState.users && trendChartState.users.length) {
        renderTrendGraph();
      }
    });
  }

  function buildTrendChart(users) {
    trendChartState.users = Array.isArray(users) ? users : [];
    initTrendChartControls();
    renderTrendGraph();
  }

  function renderTrendGraph() {
    var canvas = document.getElementById('trendChart');
    if (!canvas || !canvas.getContext) return;
    var ctx = canvas.getContext('2d');
    var users = trendChartState.users || [];
    var days = trendChartState.days || 30;

    // Aggregate user counts per day
    var counts = [];
    var labels = [];
    var fullDates = [];
    var now = new Date();
    for (var i = days - 1; i >= 0; i--) {
      var d = new Date(now);
      d.setDate(d.getDate() - i);
      var dayStr = d.toISOString().slice(0, 10);
      labels.push(d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' }));
      fullDates.push(d.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }));
      var countForDay = users.filter(function(u){ return u.created_at && u.created_at.slice(0, 10) === dayStr; }).length;
      counts.push(countForDay);
    }

    var totalPeriod = counts.reduce(function(a, b){ return a + b; }, 0);
    var avgDaily = (totalPeriod / days).toFixed(1);
    var maxVal = Math.max.apply(null, counts.concat([1]));

    // Update KPI chip metrics in header
    var elPeriod = document.getElementById('chartPeriodTotal');
    if (elPeriod) elPeriod.textContent = totalPeriod.toLocaleString() + ' users';
    var elAvg = document.getElementById('chartDailyAvg');
    if (elAvg) elAvg.textContent = avgDaily + ' / day';
    var elPeak = document.getElementById('chartPeakDay');
    if (elPeak) elPeak.textContent = maxVal.toLocaleString() + ' max';

    // High DPI Canvas Scaling
    var dpr = window.devicePixelRatio || 1;
    var parent = canvas.parentElement;
    var cssW = parent ? parent.clientWidth : 600;
    var cssH = 220;

    canvas.width = cssW * dpr;
    canvas.height = cssH * dpr;
    canvas.style.width = cssW + 'px';
    canvas.style.height = cssH + 'px';

    ctx.save();
    ctx.scale(dpr, dpr);

    var pad = { top: 25, right: 20, bottom: 35, left: 42 };
    var chartW = cssW - pad.left - pad.right;
    var chartH = cssH - pad.top - pad.bottom;

    ctx.clearRect(0, 0, cssW, cssH);

    // Subtle horizontal gridlines and Y-axis labels
    var gridSteps = 4;
    var yMax = Math.ceil(maxVal * 1.2) || 1;
    ctx.lineWidth = 1;
    ctx.font = '10px Inter, system-ui, sans-serif';
    ctx.textAlign = 'right';

    for (var g = 0; g <= gridSteps; g++) {
      var gy = pad.top + chartH - (chartH * (g / gridSteps));
      ctx.strokeStyle = 'rgba(255, 255, 255, 0.05)';
      ctx.beginPath();
      ctx.moveTo(pad.left, gy);
      ctx.lineTo(cssW - pad.right, gy);
      ctx.stroke();

      ctx.fillStyle = 'rgba(255, 255, 255, 0.4)';
      var valLabel = Math.round((yMax * g) / gridSteps);
      ctx.fillText(valLabel, pad.left - 8, gy + 3.5);
    }

    // Calculate coordinate points
    var points = [];
    var stepX = chartW / Math.max(1, (days - 1));
    for (var idx = 0; idx < days; idx++) {
      var px = pad.left + idx * stepX;
      var py = pad.top + chartH - ((counts[idx] / yMax) * chartH);
      points.push({ x: px, y: py, count: counts[idx], label: labels[idx], fullDate: fullDates[idx] });
    }
    trendChartState.points = points;

    // Draw Smooth Spline Gradient Area Fill
    if (points.length > 1) {
      var grad = ctx.createLinearGradient(0, pad.top, 0, pad.top + chartH);
      grad.addColorStop(0, 'rgba(22, 119, 255, 0.35)');
      grad.addColorStop(0.5, 'rgba(22, 119, 255, 0.10)');
      grad.addColorStop(1, 'rgba(22, 119, 255, 0.00)');

      ctx.beginPath();
      ctx.moveTo(points[0].x, pad.top + chartH);
      ctx.lineTo(points[0].x, points[0].y);

      for (var p = 0; p < points.length - 1; p++) {
        var p0 = points[p === 0 ? p : p - 1];
        var p1 = points[p];
        var p2 = points[p + 1];
        var p3 = points[p + 2 < points.length ? p + 2 : p + 1];

        var cp1x = p1.x + (p2.x - p0.x) / 6;
        var cp1y = p1.y + (p2.y - p0.y) / 6;
        var cp2x = p2.x - (p3.x - p1.x) / 6;
        var cp2y = p2.y - (p3.y - p1.y) / 6;

        ctx.bezierCurveTo(cp1x, cp1y, cp2x, cp2y, p2.x, p2.y);
      }

      ctx.lineTo(points[points.length - 1].x, pad.top + chartH);
      ctx.closePath();
      ctx.fillStyle = grad;
      ctx.fill();

      // Draw Smooth Glowing Stroke Line
      ctx.shadowColor = 'rgba(22, 119, 255, 0.55)';
      ctx.shadowBlur = 10;
      ctx.strokeStyle = '#1677FF';
      ctx.lineWidth = 2.5;
      ctx.lineCap = 'round';
      ctx.lineJoin = 'round';

      ctx.beginPath();
      ctx.moveTo(points[0].x, points[0].y);
      for (var p = 0; p < points.length - 1; p++) {
        var p0 = points[p === 0 ? p : p - 1];
        var p1 = points[p];
        var p2 = points[p + 1];
        var p3 = points[p + 2 < points.length ? p + 2 : p + 1];

        var cp1x = p1.x + (p2.x - p0.x) / 6;
        var cp1y = p1.y + (p2.y - p0.y) / 6;
        var cp2x = p2.x - (p3.x - p1.x) / 6;
        var cp2y = p2.y - (p3.y - p1.y) / 6;

        ctx.bezierCurveTo(cp1x, cp1y, cp2x, cp2y, p2.x, p2.y);
      }
      ctx.stroke();
      ctx.shadowBlur = 0; // reset shadow
    }

    // X-Axis Date Labels (distributed evenly)
    ctx.fillStyle = 'rgba(255, 255, 255, 0.45)';
    ctx.font = '10px Inter, system-ui, sans-serif';
    ctx.textAlign = 'center';
    var labelInterval = days <= 7 ? 1 : (days <= 14 ? 2 : 5);

    points.forEach(function(pt, idx) {
      if (idx % labelInterval === 0 || idx === points.length - 1) {
        ctx.fillText(pt.label, pt.x, cssH - 12);
      }
    });

    // Draw active hover guide crosshair and pulsating point
    var hIdx = trendChartState.hoverIdx;
    if (hIdx >= 0 && hIdx < points.length) {
      var hPoint = points[hIdx];

      // Vertical guide line
      ctx.strokeStyle = 'rgba(22, 119, 255, 0.4)';
      ctx.lineWidth = 1;
      ctx.setLineDash([4, 4]);
      ctx.beginPath();
      ctx.moveTo(hPoint.x, pad.top);
      ctx.lineTo(hPoint.x, pad.top + chartH);
      ctx.stroke();
      ctx.setLineDash([]); // reset dash

      // Outer glow circle
      ctx.beginPath();
      ctx.arc(hPoint.x, hPoint.y, 8, 0, Math.PI * 2);
      ctx.fillStyle = 'rgba(22, 119, 255, 0.25)';
      ctx.fill();

      // Inner solid point
      ctx.beginPath();
      ctx.arc(hPoint.x, hPoint.y, 4.5, 0, Math.PI * 2);
      ctx.fillStyle = '#FFFFFF';
      ctx.fill();
      ctx.strokeStyle = '#1677FF';
      ctx.lineWidth = 2.5;
      ctx.stroke();
    }

    ctx.restore();
    setupChartHoverInteractions(canvas, points);
  }

  function setupChartHoverInteractions(canvas, points) {
    if (canvas._hoverBound) return;
    canvas._hoverBound = true;
    var tooltip = document.getElementById('trendChartTooltip');

    canvas.addEventListener('mousemove', function(e) {
      var rect = canvas.getBoundingClientRect();
      var mouseX = e.clientX - rect.left;
      var mouseY = e.clientY - rect.top;

      // Find closest point
      var closestIdx = -1;
      var minDist = Infinity;
      points.forEach(function(pt, idx) {
        var dist = Math.abs(pt.x - mouseX);
        if (dist < minDist) {
          minDist = dist;
          closestIdx = idx;
        }
      });

      if (closestIdx !== -1 && minDist < 40) {
        trendChartState.hoverIdx = closestIdx;
        renderTrendGraph();

        if (tooltip) {
          var pt = points[closestIdx];
          tooltip.innerHTML = '<div class="chart-tooltip__date">' + esc(pt.fullDate) + '</div><div class="chart-tooltip__val"><span>' + pt.count + '</span> new registration' + (pt.count === 1 ? '' : 's') + '</div>';
          tooltip.style.display = 'block';
          tooltip.style.left = pt.x + 'px';
          tooltip.style.top = (pt.y - 12) + 'px';
        }
      } else {
        hideTooltip();
      }
    });

    canvas.addEventListener('mouseleave', function() {
      hideTooltip();
    });

    function hideTooltip() {
      if (trendChartState.hoverIdx !== -1) {
        trendChartState.hoverIdx = -1;
        renderTrendGraph();
      }
      if (tooltip) tooltip.style.display = 'none';
    }
  }

  /* ── Users ── */
  var usersPage = 0; var usersPerPage = 20; var allUsers = [];
  function loadUsers() {
    sbGetAll('profiles', '*').then(function(profiles){
      var profileList = Array.isArray(profiles) ? profiles : [];
      return fetchAllAuthUsers().then(function(authUsers){
        var authList = Array.isArray(authUsers) ? authUsers : [];
        var profileMap = {};
        profileList.forEach(function(p){ if(p.id) profileMap[p.id] = p; });

        var merged = profileList.slice();
        var authMap = {};
        authList.forEach(function(au){
          authMap[au.id] = au;
          if(au.id && !profileMap[au.id]){
            merged.push({
              id: au.id,
              email: au.email,
              username: (au.user_metadata && au.user_metadata.username) || (au.email ? au.email.split('@')[0] : 'User'),
              first_name: (au.user_metadata && au.user_metadata.first_name) || '',
              last_name: (au.user_metadata && au.user_metadata.last_name) || '',
              country_code: (au.user_metadata && au.user_metadata.country_code) || '',
              phone: au.phone || (au.user_metadata && au.user_metadata.phone) || '',
              role: 'user',
              status: 'active',
              created_at: au.created_at || au.confirmed_at || new Date().toISOString(),
              last_login: au.last_sign_in_at || null
            });
          }
        });

        merged.forEach(function(u){
          var au = authMap[u.id];
          if (au) {
            if (!u.email && au.email) u.email = au.email;
            if (au.last_sign_in_at) u.last_login = au.last_sign_in_at;
            if (!u.phone && au.phone) u.phone = au.phone;
          }
        });

        allUsers = merged;
        populateUserDatalists();
        renderUsers();
      }).catch(function(){
        allUsers = profileList;
        populateUserDatalists();
        renderUsers();
      });
    }).catch(function(e){ console.error('Users load error:', e); });
  }
  function fetchAllAuthUsers() {
    var all = [];
    function fetchPage(page) {
      return fetch(SB_URL + '/auth/v1/admin/users?page=' + page + '&per_page=1000', {
        headers: { 'apikey': SB_KEY, 'Authorization': 'Bearer ' + SB_KEY }
      }).then(function(r){ return r.json(); }).then(function(data){
        var users = data.users || [];
        all = all.concat(users);
        if (users.length === 1000) return fetchPage(page + 1);
        return all;
      });
    }
    return fetchPage(1);
  }
  function renderUsers() {
    var search = (document.getElementById('userSearch').value||'').toLowerCase();
    var filtered = allUsers.filter(function(u){
      return !search || (u.username||'').toLowerCase().includes(search) || (u.email||'').toLowerCase().includes(search);
    });
    var tbody = document.getElementById('userTableBody');
    var start = usersPage * usersPerPage;
    var page = filtered.slice(start, start + usersPerPage);
    if (!page.length) { tbody.innerHTML = '<tr><td colspan="9" class="table-empty">No users found.</td></tr>'; return; }
    tbody.innerHTML = page.map(function(u){
      var lastLogin = u.last_login ? fmtDate(u.last_login) : '--';
      var country = countryName(u.country_code);
      var statusVal = (u.status || 'active').toLowerCase();
      var isSuspended = statusVal === 'suspended';
      var isDeleted = statusVal === 'deleted';

      var actionsHtml = '<button class="btn-primary-sm" onclick="viewUser(\''+u.id+'\')">View</button> ';
      if (!isDeleted) {
        if (isSuspended) {
          actionsHtml += '<button class="btn-primary-sm" style="background:#f59e0b;border-color:#f59e0b;margin-left:2px;" onclick="reinstateUser(\''+u.id+'\')">Reinstate</button> ';
        } else {
          actionsHtml += '<button class="btn-primary-sm" style="background:#d97706;border-color:#d97706;margin-left:2px;" onclick="openSuspendUserModal(\''+u.id+'\')">Suspend</button> ';
        }
        actionsHtml += '<button class="btn-primary-sm" style="background:#ef4444;border-color:#ef4444;margin-left:2px;" onclick="openDeleteUserModal(\''+u.id+'\')">Delete</button>';
      } else {
        actionsHtml += '<span style="font-size:0.75rem;color:#94a3b8;font-style:italic;">Deleted</span>';
      }

      return '<tr>'
        + '<td><strong>'+esc(u.username||u.first_name||'--')+'</strong></td>'
        + '<td>'+esc(u.email||'--')+'</td>'
        + '<td>'+esc(u.phone||'--')+'</td>'
        + '<td>'+country+'</td>'
        + '<td>'+esc(u.role||'user')+'</td>'
        + '<td>'+fmtDate(u.created_at)+'</td>'
        + '<td>'+lastLogin+'</td>'
        + '<td><span class="account-status account-status--'+statusVal+'">'+esc(statusVal)+'</span></td>'
        + '<td style="white-space:nowrap;">'+actionsHtml+'</td>'
        + '</tr>';
    }).join('');
    document.getElementById('tableCount').textContent = filtered.length + ' users';
  }
  var userSearch = document.getElementById('userSearch');
  if (userSearch) userSearch.addEventListener('input', function(){ usersPage=0; renderUsers(); });
  var refreshBtn = document.getElementById('refreshBtn');
  if (refreshBtn) refreshBtn.addEventListener('click', function(){ loaded.users=false; loadUsers(); });

  window.viewUser = function(id) {
    var u = allUsers.find(function(x){return x.id===id;});
    if (!u) return;
    document.getElementById('modalUsername').textContent = u.username || u.email || 'User';
    document.getElementById('modalBody').innerHTML = '<div style="display:grid;gap:10px;font-size:.88rem;">'
      + '<div><strong>Email:</strong> '+esc(u.email||'--')+'</div>'
      + '<div><strong>Phone:</strong> '+esc(u.phone||'--')+'</div>'
      + '<div><strong>Role:</strong> '+esc(u.role||'user')+'</div>'
      + '<div><strong>Status:</strong> '+esc(u.status||'active')+'</div>'
      + '<div><strong>Joined:</strong> '+fmtDate(u.created_at)+'</div>'
      + '<div><strong>Last Login:</strong> '+(u.last_sign_in_at ? fmtDate(u.last_sign_in_at) : (u.last_login ? fmtDate(u.last_login) : '--'))+'</div>'
      + '<div><strong>Country:</strong> '+countryName(u.country_code)+'</div>'
      + '</div>';
    openModal('userModal');
  };

  /* ── User Suspend & Delete Actions ── */
  window.openSuspendUserModal = function(id) {
    var u = allUsers.find(function(x){ return x.id === id; });
    if (!u) return;
    document.getElementById('suspendUserId').value = u.id;
    document.getElementById('suspendReason').value = '';
    document.getElementById('suspendModalUserInfo').textContent = 'User: ' + (u.username || u.email || id) + ' (' + (u.email || 'No email') + ')';
    openModal('suspendUserModal');
  };

  window.openDeleteUserModal = function(id) {
    var u = allUsers.find(function(x){ return x.id === id; });
    if (!u) return;
    document.getElementById('deleteUserId').value = u.id;
    document.getElementById('deleteTargetEmail').value = u.email || '';
    document.getElementById('deleteConfirmationInput').value = '';
    document.getElementById('deleteReason').value = '';
    document.getElementById('deleteModalUserInfo').textContent = 'User: ' + (u.username || u.email || id) + ' (' + (u.email || 'No email') + ')';
    openModal('deleteUserModal');
  };

  window.reinstateUser = function(id) {
    var u = allUsers.find(function(x){ return x.id === id; });
    if (!u) return;
    if (!confirm('Are you sure you want to reinstate user "' + (u.email || u.username || id) + '"?')) return;

    fetch('api/user-management.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'reinstate', user_id: id })
    }).then(function(r){ return r.json(); }).then(function(res){
      if (res.success) {
        alert(res.message || 'User reinstated successfully.');
        loaded.users = false;
        loadUsers();
      } else {
        alert('Error: ' + (res.error || 'Failed to reinstate user.'));
      }
    }).catch(function(err){
      alert('Network error while reinstating user.');
    });
  };

  var suspendForm = document.getElementById('suspendUserForm');
  if (suspendForm) {
    suspendForm.addEventListener('submit', function(e){
      e.preventDefault();
      var userId = document.getElementById('suspendUserId').value;
      var reason = document.getElementById('suspendReason').value.trim();
      var submitBtn = document.getElementById('suspendSubmitBtn');

      if (reason.length < 5) {
        alert('Please provide a suspension reason of at least 5 characters.');
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = 'Suspending...';

      fetch('api/user-management.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'suspend', user_id: userId, reason: reason })
      }).then(function(r){ return r.json(); }).then(function(res){
        submitBtn.disabled = false;
        submitBtn.textContent = 'Confirm Suspension';

        if (res.success) {
          closeModal('suspendUserModal');
          alert(res.message || 'User account suspended successfully.');
          loaded.users = false;
          loadUsers();
        } else {
          alert('Error: ' + (res.error || 'Failed to suspend user account.'));
        }
      }).catch(function(err){
        submitBtn.disabled = false;
        submitBtn.textContent = 'Confirm Suspension';
        alert('Network error while executing suspension.');
      });
    });
  }

  var deleteForm = document.getElementById('deleteUserForm');
  if (deleteForm) {
    deleteForm.addEventListener('submit', function(e){
      e.preventDefault();
      var userId = document.getElementById('deleteUserId').value;
      var targetEmail = document.getElementById('deleteTargetEmail').value;
      var confirmation = document.getElementById('deleteConfirmationInput').value.trim();
      var reason = document.getElementById('deleteReason').value.trim();
      var submitBtn = document.getElementById('deleteSubmitBtn');

      if (reason.length < 5) {
        alert('Please provide a mandatory deletion reason of at least 5 characters.');
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = 'Deleting...';

      fetch('api/user-management.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'delete',
          user_id: userId,
          target_email: targetEmail,
          confirmation: confirmation,
          reason: reason
        })
      }).then(function(r){ return r.json(); }).then(function(res){
        submitBtn.disabled = false;
        submitBtn.textContent = 'Confirm Soft-Delete';

        if (res.success) {
          closeModal('deleteUserModal');
          alert(res.message || 'User account deleted and PII anonymized successfully.');
          loaded.users = false;
          loadUsers();
        } else {
          alert('Error: ' + (res.error || 'Failed to delete user account.'));
        }
      }).catch(function(err){
        submitBtn.disabled = false;
        submitBtn.textContent = 'Confirm Soft-Delete';
        alert('Network error while deleting user.');
      });
    });
  }

  /* ── Payments ── */
  var allPayments = [];
  function loadPayments() {
    sbGet('payments', 'select=*&order=created_at.desc&limit=500').then(function(data){
      allPayments = Array.isArray(data) ? data : [];
      renderPayments();
    });
  }
  function renderPayments() {
    var search = (document.getElementById('paySearch').value||'').toLowerCase();
    var filtered = allPayments.filter(function(p){
      return !search || (p.plan||'').toLowerCase().includes(search) || (p.merchant_txn_id||'').toLowerCase().includes(search) || (p.phone||'').includes(search);
    });
    var tbody = document.getElementById('payTableBody');
    if (!filtered.length) { tbody.innerHTML = '<tr><td colspan="8" class="table-empty">No payments found.</td></tr>'; return; }
    var revenue = 0, completed = 0, pending = 0;
    filtered.forEach(function(p){
      if (p.status === 'succeeded') { revenue += parseFloat(p.amount_kes||0); completed++; }
      else if (p.status === 'pending') pending++;
    });
    document.getElementById('payRevenue').textContent = 'KES ' + revenue.toLocaleString();
    document.getElementById('payCompleted').textContent = completed;
    document.getElementById('payPending').textContent = pending;
    tbody.innerHTML = filtered.slice(0,100).map(function(p){
      var statusCls = p.status === 'succeeded' ? 'active' : p.status === 'pending' ? 'pending' : 'inactive';
      return '<tr>'
        + '<td>'+esc(p.user_id||'--')+'</td>'
        + '<td>'+esc(p.plan||'--')+'</td>'
        + '<td>KES '+esc(String(p.amount_kes||0))+'</td>'
        + '<td><span class="account-status account-status--'+statusCls+'">'+esc(p.status||'--')+'</span></td>'
        + '<td>'+fmtDate(p.created_at)+'</td>'
        + '<td>'+esc(p.phone||'--')+'</td>'
        + '<td style="font-family:var(--font-mono);font-size:.72rem">'+esc(p.merchant_txn_id||'--')+'</td>'
        + '<td><button class="btn-primary-sm" onclick="viewUser(\''+p.user_id+'\')">User</button></td>'
        + '</tr>';
    }).join('');
  }
  var paySearch = document.getElementById('paySearch');
  if (paySearch) paySearch.addEventListener('input', renderPayments);

  function populateUserDatalists() {
    var dl = document.getElementById('grantUserList');
    if (!dl) return;
    var seen = {};
    var html = '';
    allUsers.forEach(function(u){
      if (u.username && !seen[u.username.toLowerCase()]) {
        seen[u.username.toLowerCase()] = true;
        html += '<option value="' + esc(u.username) + '">' + esc(u.username) + (u.email ? ' (' + esc(u.email) + ')' : '') + '</option>';
      }
      if (u.email && !seen[u.email.toLowerCase()]) {
        seen[u.email.toLowerCase()] = true;
        html += '<option value="' + esc(u.email) + '">' + esc(u.email) + (u.username ? ' (@' + esc(u.username) + ')' : '') + '</option>';
      }
    });
    dl.innerHTML = html;
  }

  /* ── Safe Multi-Path Admin API Fetcher ── */
  var ADMIN_API_ENDPOINTS = [
    'api/subscription-management.php',
    'api/admin/subscription-management.php',
    '../api/admin/subscription-management.php'
  ];

  function safeAdminFetch(payload, queryParams) {
    var qs = queryParams ? ('?' + new URLSearchParams(queryParams).toString()) : '';
    var isGet = !payload;
    
    function tryFetch(idx) {
      if (idx >= ADMIN_API_ENDPOINTS.length) {
        return Promise.reject(new Error('Unable to reach subscription management endpoint. Please verify server connection.'));
      }
      var url = ADMIN_API_ENDPOINTS[idx] + qs;
      var opts = {
        method: isGet ? 'GET' : 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
      };
      if (!isGet) {
        opts.body = JSON.stringify(payload);
      }

      return fetch(url, opts).then(function(r) {
        if (!r.ok) {
          if (r.status === 404 && idx < ADMIN_API_ENDPOINTS.length - 1) {
            return tryFetch(idx + 1);
          }
          return r.json().then(function(errObj) {
            throw new Error(errObj.error || errObj.message || ('Server returned HTTP error ' + r.status));
          }).catch(function() {
            throw new Error('Server returned HTTP error ' + r.status + ' (' + r.statusText + ')');
          });
        }
        return r.json();
      }).catch(function(err) {
        if (idx < ADMIN_API_ENDPOINTS.length - 1 && (err.message.includes('404') || err.message.includes('Failed to fetch'))) {
          return tryFetch(idx + 1);
        }
        throw err;
      });
    }

    return tryFetch(0);
  }

  /* ── Elite Subscriptions Management ── */
  var allEliteMembers = [];
  function loadEliteSubscriptions() {
    var tbody = document.getElementById('eliteTableBody');
    if (tbody) tbody.innerHTML = '<tr><td colspan="9" class="table-empty">Loading Elite members...</td></tr>';

    safeAdminFetch(null, { action: 'list_elite' })
      .then(function(res){
        allEliteMembers = (res && res.data && Array.isArray(res.data)) ? res.data : [];
        renderEliteSubscriptions();
      })
      .catch(function(err){
        console.error('Failed to load Elite members:', err);
        if (tbody) tbody.innerHTML = '<tr><td colspan="9" class="table-empty" style="color:#FF6B6B">Failed to load Elite members: ' + esc(err.message) + '</td></tr>';
      });
  }

  function renderEliteSubscriptions() {
    var search = (document.getElementById('eliteSearchInput') ? document.getElementById('eliteSearchInput').value : '').toLowerCase();
    var filterStatus = document.getElementById('eliteFilterStatus') ? document.getElementById('eliteFilterStatus').value : 'all';

    var filtered = allEliteMembers.filter(function(m){
      var uname = (m.username || '').toLowerCase();
      var email = (m.email || '').toLowerCase();
      var plan = (m.plan_name || m.plan || '').toLowerCase();
      var matchesSearch = !search || uname.includes(search) || email.includes(search) || plan.includes(search);
      
      var isExpired = m.expires_at ? new Date(m.expires_at).getTime() < Date.now() : false;
      var status = isExpired ? 'expired' : (m.status || 'active');

      var matchesStatus = (filterStatus === 'all') ||
        (filterStatus === 'active' && !isExpired && status !== 'cancelled') ||
        (filterStatus === 'expired' && isExpired) ||
        (filterStatus === 'cancelled' && status === 'cancelled');

      return matchesSearch && matchesStatus;
    });

    var activeCount = 0;
    var totalCapital = 0;
    allEliteMembers.forEach(function(m){
      var isExp = m.expires_at ? new Date(m.expires_at).getTime() < Date.now() : false;
      if (!isExp && m.status !== 'cancelled') {
        activeCount++;
        totalCapital += parseFloat(m.amount_usd || 0);
      }
    });

    if (document.getElementById('eliteStatActive')) document.getElementById('eliteStatActive').textContent = activeCount;
    if (document.getElementById('eliteStatCapital')) document.getElementById('eliteStatCapital').textContent = '$' + totalCapital.toLocaleString();
    if (document.getElementById('eliteStatTotal')) document.getElementById('eliteStatTotal').textContent = allEliteMembers.length;

    var tbody = document.getElementById('eliteTableBody');
    if (!tbody) return;

    if (!filtered.length) {
      tbody.innerHTML = '<tr><td colspan="9" class="table-empty">No Elite subscription records found.</td></tr>';
      if (document.getElementById('eliteShowingCount')) document.getElementById('eliteShowingCount').textContent = '0 members';
      return;
    }

    tbody.innerHTML = filtered.map(function(m){
      var isExp = m.expires_at ? new Date(m.expires_at).getTime() < Date.now() : false;
      var statusLabel = isExp ? 'expired' : (m.status || 'active');
      var statusCls = isExp ? 'inactive' : (statusLabel === 'active' || statusLabel === 'succeeded' ? 'active' : 'pending');

      var usdFormatted = '$' + Number(m.amount_usd || 0).toLocaleString();
      var kesFormatted = 'KES ' + Number(m.amount_kes || m.amount || 0).toLocaleString();

      var isPermanent = (m.id && String(m.id).startsWith('perm-')) || m.granted_by === 'System (Permanent)';

      var actionsHtml = '';
      if (!isPermanent) {
        actionsHtml = '<div style="display:flex;gap:4px;">'
          + '<button class="action-btn" title="Extend by 30 Days" onclick="extendEliteSub(\''+esc(m.id)+'\')">+30d</button>'
          + '<button class="action-btn danger" title="Revoke access" onclick="revokeEliteSub(\''+esc(m.id)+'\')">&times;</button>'
          + '</div>';
      } else {
        actionsHtml = '<span style="font-size:0.7rem;color:#F0B429;font-weight:600;">PERMANENT VIP</span>';
      }

      return '<tr>'
        + '<td><strong>@'+esc(m.username||'--')+'</strong>'+(m.email ? '<br><small style="color:#7F8B99">'+esc(m.email)+'</small>' : '')+'</td>'
        + '<td><span style="color:#fff;font-weight:600;">'+esc(m.plan_name||m.plan||'BM Elites')+'</span></td>'
        + '<td style="color:#16C784;font-weight:600;">'+usdFormatted+'</td>'
        + '<td style="color:#8FA3B8;">'+kesFormatted+'</td>'
        + '<td>'+fmtDate(m.starts_at || m.created_at)+'</td>'
        + '<td style="font-weight:500;">'+(isPermanent ? 'Permanent' : fmtDateTime(m.expires_at))+'</td>'
        + '<td><span class="account-status account-status--'+statusCls+'">'+esc(statusLabel.toUpperCase())+'</span></td>'
        + '<td><span style="font-size:0.75rem;color:#8FA3B8;">'+esc(m.granted_by||'Admin')+'</span></td>'
        + '<td>'+actionsHtml+'</td>'
        + '</tr>';
    }).join('');

    if (document.getElementById('eliteShowingCount')) {
      document.getElementById('eliteShowingCount').textContent = 'Showing ' + filtered.length + ' of ' + allEliteMembers.length + ' records';
    }
  }

  var eliteSearch = document.getElementById('eliteSearchInput');
  if (eliteSearch) eliteSearch.addEventListener('input', renderEliteSubscriptions);
  var eliteFilter = document.getElementById('eliteFilterStatus');
  if (eliteFilter) eliteFilter.addEventListener('change', renderEliteSubscriptions);
  var eliteRefresh = document.getElementById('eliteRefreshBtn');
  if (eliteRefresh) eliteRefresh.addEventListener('click', loadEliteSubscriptions);

  // Modal Triggers
  var grantSubBtn = document.getElementById('grantSubBtn');
  if (grantSubBtn) grantSubBtn.addEventListener('click', function(){
    populateUserDatalists();
    var alertEl = document.getElementById('grantFormAlert');
    if (alertEl) { alertEl.className = 'grant-alert'; alertEl.textContent = ''; }
    openModal('grantModal');
  });

  var grantEliteModalBtn = document.getElementById('grantEliteModalBtn');
  if (grantEliteModalBtn) grantEliteModalBtn.addEventListener('click', function(){
    populateUserDatalists();
    openModal('grantEliteModal');
  });

  // Preset Duration Pills in Grant Modal
  document.querySelectorAll('.grant-preset-btn').forEach(function(pbtn){
    pbtn.addEventListener('click', function(){
      document.querySelectorAll('.grant-preset-btn').forEach(function(b){ b.classList.remove('active'); });
      this.classList.add('active');
      var days = this.dataset.days;
      var durationInput = document.getElementById('grantFormDuration');
      if (durationInput && days) {
        durationInput.value = days;
      }
    });
  });

  var grantFormCancelBtn = document.getElementById('grantFormCancelBtn');
  if (grantFormCancelBtn) {
    grantFormCancelBtn.addEventListener('click', function(){
      closeModal('grantModal');
    });
  }

  function showGrantAlert(msg, type) {
    var alertEl = document.getElementById('grantFormAlert');
    if (!alertEl) { alert(msg); return; }
    alertEl.textContent = msg;
    alertEl.className = 'grant-alert show ' + (type || 'error');
  }

  // Grant Subscription Form Submit
  var grantForm = document.getElementById('grantForm');
  if (grantForm) {
    grantForm.addEventListener('submit', function(e){
      e.preventDefault();
      var alertEl = document.getElementById('grantFormAlert');
      if (alertEl) { alertEl.className = 'grant-alert'; alertEl.textContent = ''; }

      var username = (document.getElementById('grantFormUsername').value || '').trim();
      var plan = (document.getElementById('grantFormPlan').value || '').trim();
      var duration = parseInt(document.getElementById('grantFormDuration').value, 10);
      var notes = (document.getElementById('grantFormNotes').value || '').trim();

      // Frontend Form Validation
      if (!username) {
        showGrantAlert('Please enter target username or email address.', 'error');
        return;
      }
      if (!plan) {
        showGrantAlert('Please select a subscription plan.', 'error');
        return;
      }
      if (isNaN(duration) || duration <= 0) {
        showGrantAlert('Please enter a valid duration in days (minimum 1 day).', 'error');
        return;
      }

      var submitBtn = document.getElementById('grantFormSubmitBtn');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-sm"></span><span>Granting Subscription...</span>';
      }

      safeAdminFetch({
        action: 'grant_subscription',
        username: username,
        plan: plan,
        duration_days: duration,
        notes: notes
      })
      .then(function(res){
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg><span>Grant Subscription</span>';
        }

        if (res && res.success) {
          showGrantAlert(res.message || 'Subscription granted successfully!', 'success');
          setTimeout(function(){
            closeModal('grantModal');
            grantForm.reset();
            if (alertEl) { alertEl.className = 'grant-alert'; }
            if (typeof loadPayments === 'function') loadPayments();
            if (typeof loadEliteSubscriptions === 'function') loadEliteSubscriptions();
          }, 1200);
        } else {
          showGrantAlert((res && (res.error || res.message)) ? (res.error || res.message) : 'Unable to grant subscription. Please verify user details.', 'error');
        }
      })
      .catch(function(err){
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg><span>Grant Subscription</span>';
        }
        showGrantAlert('Unable to grant subscription: ' + (err.message || 'Server connection error'), 'error');
      });
    });
  }

  // Grant Elite Form Submit
  var grantEliteForm = document.getElementById('grantEliteForm');
  if (grantEliteForm) {
    grantEliteForm.addEventListener('submit', function(e){
      e.preventDefault();
      var username = (document.getElementById('grantEliteUsername').value || '').trim();
      var plan = document.getElementById('grantElitePlan').value;
      var amount = parseFloat(document.getElementById('grantEliteAmount').value) || 10000;
      var duration = parseInt(document.getElementById('grantEliteDuration').value, 10) || 30;

      if (!username) {
        alert('Please enter username or email for Elite plan.');
        return;
      }

      var submitBtn = document.getElementById('grantEliteSubmitBtn');
      if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<span class="spinner-sm"></span><span>Granting Elite...</span>'; }

      safeAdminFetch({
        action: 'grant_elite',
        username: username,
        plan: plan,
        amount: amount,
        duration_days: duration,
        notes: 'Admin Grant via Elite Subscriptions Panel'
      })
      .then(function(res){
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Grant Elite Plan'; }
        if (res && res.success) {
          alert(res.message || 'BM Elites access granted successfully!');
          closeModal('grantEliteModal');
          grantEliteForm.reset();
          loadEliteSubscriptions();
        } else {
          alert('Error granting Elite access: ' + ((res && (res.error || res.message)) || 'Unknown error'));
        }
      })
      .catch(function(err){
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Grant Elite Plan'; }
        alert('Error granting Elite plan: ' + err.message);
      });
    });
  }

  // Revoke Subscription helper
  window.revokeEliteSub = function(id) {
    if (!confirm('Are you sure you want to revoke this Elite subscription? The user will immediately lose Elite access.')) return;
    safeAdminFetch({ action: 'revoke_subscription', id: id })
    .then(function(res){
      if (res && res.success) {
        alert(res.message || 'Subscription revoked.');
        loadEliteSubscriptions();
      } else {
        alert('Error: ' + ((res && (res.error || res.message)) || 'Failed to revoke'));
      }
    })
    .catch(function(err){ alert('Request failed: ' + err.message); });
  };

  // Extend Subscription helper
  window.extendEliteSub = function(id) {
    var days = prompt('Enter number of days to extend subscription by:', '30');
    if (!days || isNaN(days) || parseInt(days, 10) <= 0) return;
    safeAdminFetch({ action: 'extend_subscription', id: id, days: parseInt(days, 10) })
    .then(function(res){
      if (res && res.success) {
        alert(res.message || 'Subscription extended.');
        loadEliteSubscriptions();
      } else {
        alert('Error: ' + ((res && (res.error || res.message)) || 'Failed to extend'));
      }
    })
    .catch(function(err){ alert('Request failed: ' + err.message); });
  };

  /* ── Articles ── */
  function loadArticles() {
    sbGet('educational_articles', 'select=*&order=created_at.desc').then(function(data){
      var tbody = document.getElementById('articleTableBody');
      if (!Array.isArray(data) || !data.length) { tbody.innerHTML = '<tr><td colspan="5" class="table-empty">No articles.</td></tr>'; return; }
      tbody.innerHTML = data.map(function(a){
        return '<tr><td><strong>'+esc(a.title)+'</strong></td><td>'+esc(a.category||'')+'</td><td><span class="account-status account-status--'+(a.status==='published'?'active':'inactive')+'">'+esc(a.status)+'</span></td><td>'+fmtDate(a.created_at)+'</td><td><button class="action-btn" onclick="editArticle(\''+a.id+'\')">Edit</button><button class="action-btn danger" onclick="deleteArticle(\''+a.id+'\')">Delete</button></td></tr>';
      }).join('');
    });
  }

  /* ── Videos ── */
  function loadVideos() {
    sbGet('featured_videos', 'select=*&order=created_at.desc').then(function(data){
      var tbody = document.getElementById('videoTableBody');
      if (!Array.isArray(data) || !data.length) { tbody.innerHTML = '<tr><td colspan="4" class="table-empty">No videos.</td></tr>'; return; }
      tbody.innerHTML = data.map(function(v){
        return '<tr><td><strong>'+esc(v.title)+'</strong></td><td><span class="account-status account-status--'+(v.status==='published'?'active':'inactive')+'">'+esc(v.status)+'</span></td><td>'+fmtDate(v.created_at)+'</td><td><button class="action-btn" onclick="editVideo(\''+v.id+'\')">Edit</button><button class="action-btn danger" onclick="deleteVideo(\''+v.id+'\')">Delete</button></td></tr>';
      }).join('');
    });
  }

  /* ── Promotions ── */
  function loadPromotions() {
    sbGet('promotions', 'select=*&order=created_at.desc').then(function(data){
      var tbody = document.getElementById('promoTableBody');
      if (!Array.isArray(data) || !data.length) { tbody.innerHTML = '<tr><td colspan="5" class="table-empty">No promotions.</td></tr>'; return; }
      tbody.innerHTML = data.map(function(p){
        return '<tr><td><strong>'+esc(p.title)+'</strong></td><td><span class="account-status account-status--'+(p.status==='active'?'active':'inactive')+'">'+esc(p.status)+'</span></td><td>'+fmtDate(p.start_date)+'</td><td>'+fmtDate(p.end_date)+'</td><td><button class="action-btn" onclick="editPromo(\''+p.id+'\')">Edit</button><button class="action-btn danger" onclick="deletePromo(\''+p.id+'\')">Delete</button></td></tr>';
      }).join('');
    });
  }

  /* ── Market Overview ── */
  function loadMO() {
    sbGet('market_overview_announcements', 'select=*&order=created_at.desc').then(function(data){
      var tbody = document.getElementById('moTableBody');
      if (!Array.isArray(data) || !data.length) { tbody.innerHTML = '<tr><td colspan="4" class="table-empty">No announcements.</td></tr>'; return; }
      tbody.innerHTML = data.map(function(m){
        return '<tr><td><strong>'+esc(m.title)+'</strong></td><td><span class="account-status account-status--'+(m.status==='published'?'active':'inactive')+'">'+esc(m.status)+'</span></td><td>'+esc(String(m.priority||0))+'</td><td><button class="action-btn" onclick="editMo(\''+m.id+'\')">Edit</button><button class="action-btn danger" onclick="deleteMo(\''+m.id+'\')">Delete</button></td></tr>';
      }).join('');
    });
  }

  /* ── Featured Signals ── */
  function loadSignals() {
    sbGet('featured_signals', 'select=*&order=created_at.desc').then(function(data){
      var tbody = document.getElementById('signalTableBody');
      if (!Array.isArray(data) || !data.length) { tbody.innerHTML = '<tr><td colspan="8" class="table-empty">No signals.</td></tr>'; return; }
      tbody.innerHTML = data.map(function(s){
        return '<tr><td>'+esc(s.pair||'')+'</td><td>'+esc(s.direction||'')+'</td><td>'+esc(s.entry||'')+'</td><td>'+esc(s.take_profit||'')+'</td><td>'+esc(s.stop_loss||'')+'</td><td>'+esc(String(s.confidence||''))+'</td><td><span class="account-status account-status--'+(s.status==='published'?'active':'inactive')+'">'+esc(s.status)+'</span></td><td><button class="action-btn" onclick="editSignal(\''+s.id+'\')">Edit</button><button class="action-btn danger" onclick="deleteSignal(\''+s.id+'\')">Delete</button></td></tr>';
      }).join('');
    });
  }

  /* ── Announcements ── */
  function loadAnnouncements() {
    sbGet('announcements', 'select=*&order=created_at.desc').then(function(data){
      var tbody = document.getElementById('announceTableBody');
      if (!Array.isArray(data) || !data.length) { tbody.innerHTML = '<tr><td colspan="5" class="table-empty">No announcements.</td></tr>'; return; }
      tbody.innerHTML = data.map(function(a){
        return '<tr><td><strong>'+esc(a.title)+'</strong></td><td>'+esc((a.message||'').substring(0,80))+'</td><td><span class="account-status account-status--'+(a.is_active?'active':'inactive')+'">'+(a.is_active?'Active':'Inactive')+'</span></td><td>'+fmtDate(a.created_at)+'</td><td><button class="action-btn" onclick="editAnnounce(\''+a.id+'\')">Edit</button><button class="action-btn danger" onclick="deleteAnnounce(\''+a.id+'\')">Delete</button></td></tr>';
      }).join('');
    });
  }

  /* ── Live Classes ── */
  function loadLiveClasses() {
    sbGet('live_classes', 'select=*&order=scheduled_at.desc').then(function(data){
      var tbody = document.getElementById('liveClassesTableBody');
      if (!Array.isArray(data) || !data.length) { tbody.innerHTML = '<tr><td colspan="7" class="table-empty">No live classes.</td></tr>'; return; }
      tbody.innerHTML = data.map(function(c){
        return '<tr><td><strong>'+esc(c.title)+'</strong></td><td>'+esc(c.instructor||'')+'</td><td>'+fmtDateTime(c.scheduled_at)+'</td><td>'+esc(String(c.duration||''))+' min</td><td>'+(c.is_live?'<span class="status-dot status-dot--active">Live</span>':'<span class="status-dot status-dot--pending">Scheduled</span>')+'</td><td>--</td><td><button class="action-btn" onclick="editLiveClass(\''+c.id+'\')">Edit</button><button class="action-btn danger" onclick="deleteLiveClass(\''+c.id+'\')">Delete</button></td></tr>';
      }).join('');
    });
  }

  /* ── Registered Contacts ── */
  var allContacts = [];
  function loadContacts() {
    sbGet('profiles', 'select=*&order=created_at.desc&limit=1000').then(function(data){
      allContacts = Array.isArray(data) ? data : [];
      var total = allContacts.length;
      var withPhone = allContacts.filter(function(c){ return c.phone; }).length;
      var withEmail = allContacts.filter(function(c){ return c.email; }).length;
      var todayStart = new Date(); todayStart.setHours(0,0,0,0);
      var newToday = allContacts.filter(function(c){ return c.created_at && new Date(c.created_at).getTime() >= todayStart.getTime(); }).length;
      document.getElementById('statContactsTotal').textContent = total;
      document.getElementById('statContactsVerified').textContent = total;
      document.getElementById('statContactsPhone').textContent = withPhone;
      document.getElementById('statContactsEmail').textContent = withEmail;
      document.getElementById('statContactsToday').textContent = newToday;
      renderContacts();
    });
  }
  function renderContacts() {
    var search = (document.getElementById('contactsSearchInput').value||'').toLowerCase();
    var statusFilter = document.getElementById('contactsFilterStatus').value;
    var filtered = allContacts.filter(function(c){
      if (search && !(c.username||'').toLowerCase().includes(search) && !(c.email||'').toLowerCase().includes(search) && !(c.phone||'').includes(search)) return false;
      if (statusFilter !== 'all' && c.status !== statusFilter) return false;
      return true;
    });
    var tbody = document.getElementById('contactsTableBody');
    if (!filtered.length) { tbody.innerHTML = '<tr><td colspan="10" class="table-empty">No contacts found.</td></tr>'; return; }
    tbody.innerHTML = filtered.slice(0,200).map(function(c){
      var statusCls = c.status || 'active';
      return '<tr>'
        + '<td class="chk-col"><input type="checkbox" class="user-chk contact-chk" value="'+c.id+'"></td>'
        + '<td><strong>'+esc(c.username||c.first_name||'--')+'</strong></td>'
        + '<td>'+esc(c.username||'--')+'</td>'
        + '<td>'+esc(c.email||'--')+'</td>'
        + '<td>'+esc(c.phone||'--')+'</td>'
        + '<td>'+countryName(c.country_code)+'</td>'
        + '<td>'+fmtDate(c.created_at)+'</td>'
        + '<td><span class="contact-verified contact-verified--yes">Active</span></td>'
        + '<td><span class="account-status account-status--'+statusCls+'">'+esc(statusCls)+'</span></td>'
        + '<td><button class="btn-primary-sm" onclick="viewUser(\''+c.id+'\')">View</button></td>'
        + '</tr>';
    }).join('');
    document.getElementById('contactsShowingCount').textContent = 'Showing ' + Math.min(filtered.length,200) + ' of ' + filtered.length + ' users';
  }
  var contactsSearch = document.getElementById('contactsSearchInput');
  if (contactsSearch) contactsSearch.addEventListener('input', renderContacts);
  var contactsFilterVerif = document.getElementById('contactsFilterVerif');
  if (contactsFilterVerif) contactsFilterVerif.addEventListener('change', renderContacts);
  var contactsFilterStatus = document.getElementById('contactsFilterStatus');
  if (contactsFilterStatus) contactsFilterStatus.addEventListener('change', renderContacts);

  /* ── AI Knowledge Base ── */
  function loadAiKb() {
    sbGet('ai_knowledge_base', 'select=*&order=category asc').then(function(data){
      var tbody = document.getElementById('aiKnowledgeTableBody');
      if (!Array.isArray(data) || !data.length) { tbody.innerHTML = '<tr><td colspan="6" class="table-empty">No knowledge entries.</td></tr>'; return; }
      tbody.innerHTML = data.map(function(k){
        return '<tr><td>'+esc(k.category||'')+'</td><td><strong>'+esc(k.question||'')+'</strong></td><td>'+esc((k.answer||'').substring(0,80))+'</td><td style="font-family:var(--font-mono);font-size:.72rem">'+esc(k.keywords||'')+'</td><td><span class="account-status account-status--'+(k.published?'active':'inactive')+'">'+(k.published?'Published':'Draft')+'</span></td><td><button class="btn-primary-sm" onclick="openModal(\'aiKbModal\')">Edit</button></td></tr>';
      }).join('');
    }).catch(function(){ document.getElementById('aiKnowledgeTableBody').innerHTML = '<tr><td colspan="6" class="table-empty">Knowledge base table not found.</td></tr>'; });
  }

  /* ── AI Conversations ── */
  function loadAiLogs() {
    sbGet('ai_conversations', 'select=*&order=created_at desc&limit=200').then(function(data){
      var tbody = document.getElementById('aiLogsTableBody');
      if (!Array.isArray(data) || !data.length) { tbody.innerHTML = '<tr><td colspan="7" class="table-empty">No conversations logged.</td></tr>'; return; }
      tbody.innerHTML = data.map(function(c){
        var statusCls = c.escalated ? 'inactive' : 'active';
        return '<tr><td style="font-family:var(--font-mono);font-size:.72rem">'+esc((c.session_id||'').substring(0,12))+'</td><td>'+esc(c.user_id||'--')+'</td><td>'+esc((c.user_message||'').substring(0,60))+'</td><td>'+esc((c.ai_response||'').substring(0,60))+'</td><td><span class="account-status account-status--'+statusCls+'">'+(c.escalated?'Escalated':'AI Handled')+'</span></td><td>'+esc(c.ip_address||'--')+'</td><td>'+fmtDateTime(c.created_at)+'</td></tr>';
      }).join('');
    }).catch(function(){ document.getElementById('aiLogsTableBody').innerHTML = '<tr><td colspan="7" class="table-empty">Conversations table not found.</td></tr>'; });
  }

  /* ── AI Settings ── */
  function loadAiSettings() {
    sbGet('ai_settings', 'select=*&limit=1').then(function(data){
      if (!Array.isArray(data) || !data.length) return;
      var s = data[0];
      var el = document.getElementById('aiSetEnabled'); if(el) el.checked = s.enabled !== false;
      var wg = document.getElementById('aiSetWelcomeMsg'); if(wg) wg.value = s.welcome_message || '';
      var sq = document.getElementById('aiSetSuggestedQ'); if(sq) sq.value = (s.suggested_questions||[]).join('\n');
      var pl = document.getElementById('aiSetPersonality'); if(pl) pl.value = s.personality || '';
      var oh = document.getElementById('aiSetOfficeHours'); if(oh) oh.value = s.office_hours || '';
      var fb = document.getElementById('aiSetFallbackMsg'); if(fb) fb.value = s.fallback_message || '';
      var es = document.getElementById('aiSetEscalationMsg'); if(es) es.value = s.escalation_message || '';
      var wa = document.getElementById('aiSetWhatsappUrl'); if(wa) wa.value = s.whatsapp_url || '';
    }).catch(function(){});
  }

  /* ── Content Creation & CRUD Event Wireups ── */

  function wireModalTrigger(btnId, modalId, onOpen) {
    var btn = document.getElementById(btnId);
    if (btn) {
      btn.addEventListener('click', function() {
        if (typeof onOpen === 'function') onOpen();
        openModal(modalId);
      });
    }
  }
  function wireModalClose(closeBtnId, modalId) {
    var btn = document.getElementById(closeBtnId);
    if (btn) {
      btn.addEventListener('click', function() {
        closeModal(modalId);
      });
    }
  }

  // 1. Featured Videos CRUD
  wireModalTrigger('addVideoBtn', 'videoModal', function() {
    if (document.getElementById('videoForm')) document.getElementById('videoForm').reset();
    if (document.getElementById('videoFormId')) document.getElementById('videoFormId').value = '';
    if (document.getElementById('videoModalTitle')) document.getElementById('videoModalTitle').textContent = 'Add Video';
  });
  wireModalClose('videoModalClose', 'videoModal');

  var videoForm = document.getElementById('videoForm');
  if (videoForm) {
    videoForm.addEventListener('submit', function(e) {
      e.preventDefault();
      var id = document.getElementById('videoFormId').value;
      var title = (document.getElementById('videoFormTitle').value || '').trim();
      var desc = (document.getElementById('videoFormDesc').value || '').trim();
      var url = (document.getElementById('videoFormUrl').value || '').trim();
      var thumb = (document.getElementById('videoFormThumb').value || '').trim();
      var status = document.getElementById('videoFormStatus').value;

      if (!title || !url) {
        alert('Please fill in required fields (Title and Video URL).');
        return;
      }

      var payload = { title: title, description: desc, video_url: url, thumbnail: thumb, status: status };
      if (id) payload.id = id;

      fetch('../api/videos.php', {
        method: id ? 'PATCH' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(function(r){ return r.json(); })
      .then(function(res) {
        if (res && res.success) {
          alert('Video saved successfully.');
          closeModal('videoModal');
          videoForm.reset();
          loadVideos();
        } else {
          alert('Error saving video: ' + ((res && res.error) || 'Failed'));
        }
      })
      .catch(function(err) { alert('Request failed: ' + err.message); });
    });
  }

  window.editVideo = function(id) {
    fetch('../api/videos.php?id=' + encodeURIComponent(id))
      .then(function(r){ return r.json(); })
      .then(function(res) {
        var item = (res.data && res.data[0]) ? res.data[0] : null;
        if (!item) return;
        document.getElementById('videoFormId').value = item.id;
        document.getElementById('videoFormTitle').value = item.title || '';
        document.getElementById('videoFormDesc').value = item.description || '';
        document.getElementById('videoFormUrl').value = item.video_url || item.url || '';
        document.getElementById('videoFormThumb').value = item.thumbnail || '';
        document.getElementById('videoFormStatus').value = item.status || 'published';
        document.getElementById('videoModalTitle').textContent = 'Edit Video';
        openModal('videoModal');
      });
  };

  window.deleteVideo = function(id) {
    if (!confirm('Are you sure you want to permanently delete this video?')) return;
    fetch('../api/videos.php?id=' + encodeURIComponent(id), { method: 'DELETE' })
      .then(function(r){ return r.json(); })
      .then(function(res) {
        if (res && res.success) {
          alert('Video deleted.');
          loadVideos();
        } else {
          alert('Failed to delete video.');
        }
      });
  };

  // 2. Promotions CRUD
  wireModalTrigger('addPromoBtn', 'promoModal', function() {
    if (document.getElementById('promoForm')) document.getElementById('promoForm').reset();
    if (document.getElementById('promoFormId')) document.getElementById('promoFormId').value = '';
    if (document.getElementById('promoModalTitle')) document.getElementById('promoModalTitle').textContent = 'Add Promotion';
  });
  wireModalClose('promoModalClose', 'promoModal');

  var promoForm = document.getElementById('promoForm');
  if (promoForm) {
    promoForm.addEventListener('submit', function(e) {
      e.preventDefault();
      var id = document.getElementById('promoFormId').value;
      var title = (document.getElementById('promoFormTitle').value || '').trim();
      var desc = (document.getElementById('promoFormDesc').value || '').trim();
      var banner = (document.getElementById('promoFormBanner').value || '').trim();
      var btnText = (document.getElementById('promoFormBtn').value || '').trim();
      var link = (document.getElementById('promoFormLink').value || '').trim();
      var start = document.getElementById('promoFormStart').value;
      var end = document.getElementById('promoFormEnd').value;
      var status = document.getElementById('promoFormStatus').value;

      if (!title) { alert('Please enter promotion title.'); return; }

      var payload = {
        title: title, description: desc, banner_url: banner,
        button_text: btnText, button_link: link, start_date: start,
        end_date: end, status: status
      };
      if (id) payload.id = id;

      fetch('../api/promotions.php', {
        method: id ? 'PATCH' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(function(r){ return r.json(); })
      .then(function(res) {
        if (res && res.success) {
          alert('Promotion saved successfully.');
          closeModal('promoModal');
          promoForm.reset();
          loadPromotions();
        } else {
          alert('Error saving promotion: ' + ((res && res.error) || 'Failed'));
        }
      })
      .catch(function(err) { alert('Request failed: ' + err.message); });
    });
  }

  window.editPromo = function(id) {
    fetch('../api/promotions.php?id=' + encodeURIComponent(id))
      .then(function(r){ return r.json(); })
      .then(function(res) {
        var item = (res.data && res.data[0]) ? res.data[0] : null;
        if (!item) return;
        document.getElementById('promoFormId').value = item.id;
        document.getElementById('promoFormTitle').value = item.title || '';
        document.getElementById('promoFormDesc').value = item.description || '';
        document.getElementById('promoFormBanner').value = item.banner_url || '';
        document.getElementById('promoFormBtn').value = item.button_text || '';
        document.getElementById('promoFormLink').value = item.button_link || '';
        document.getElementById('promoFormStart').value = item.start_date ? item.start_date.substring(0,10) : '';
        document.getElementById('promoFormEnd').value = item.end_date ? item.end_date.substring(0,10) : '';
        document.getElementById('promoFormStatus').value = item.status || 'active';
        document.getElementById('promoModalTitle').textContent = 'Edit Promotion';
        openModal('promoModal');
      });
  };

  window.deletePromo = function(id) {
    if (!confirm('Are you sure you want to permanently delete this promotion?')) return;
    fetch('../api/promotions.php?id=' + encodeURIComponent(id), { method: 'DELETE' })
      .then(function(r){ return r.json(); })
      .then(function(res) {
        if (res && res.success) {
          alert('Promotion deleted.');
          loadPromotions();
        } else {
          alert('Failed to delete promotion.');
        }
      });
  };

  // 3. Featured Signals CRUD
  wireModalTrigger('addSignalBtn', 'signalModal', function() {
    if (document.getElementById('signalForm')) document.getElementById('signalForm').reset();
    if (document.getElementById('signalFormId')) document.getElementById('signalFormId').value = '';
    if (document.getElementById('signalModalTitle')) document.getElementById('signalModalTitle').textContent = 'Add Signal';
  });
  wireModalClose('signalModalClose', 'signalModal');

  var signalForm = document.getElementById('signalForm');
  if (signalForm) {
    signalForm.addEventListener('submit', function(e) {
      e.preventDefault();
      var id = document.getElementById('signalFormId').value;
      var pair = (document.getElementById('signalFormPair').value || '').trim();
      var dir = document.getElementById('signalFormDir').value;
      var entry = (document.getElementById('signalFormEntry').value || '').trim();
      var tp = (document.getElementById('signalFormTp').value || '').trim();
      var sl = (document.getElementById('signalFormSl').value || '').trim();
      var risk = (document.getElementById('signalFormRisk').value || '').trim();
      var conf = parseInt(document.getElementById('signalFormConf').value, 10) || 75;
      var status = document.getElementById('signalFormStatus').value;

      if (!pair || !entry || !tp || !sl) {
        alert('Please fill in required signal fields (Pair, Entry, TP, SL).');
        return;
      }

      var payload = {
        pair: pair, direction: dir, entry: entry, take_profit: tp,
        stop_loss: sl, risk_desc: risk, confidence: conf, status: status
      };
      if (id) payload.id = id;

      var method = id ? 'PATCH' : 'POST';
      sbPost('featured_signals' + (id ? '?id=eq.' + encodeURIComponent(id) : ''), payload, method)
        .then(function(res) {
          alert('Signal saved successfully.');
          closeModal('signalModal');
          signalForm.reset();
          loadSignals();
        })
        .catch(function(err) { alert('Error saving signal: ' + err.message); });
    });
  }

  window.editSignal = function(id) {
    sbGet('featured_signals', 'id=eq.' + encodeURIComponent(id)).then(function(data) {
      var item = (Array.isArray(data) && data[0]) ? data[0] : null;
      if (!item) return;
      document.getElementById('signalFormId').value = item.id;
      document.getElementById('signalFormPair').value = item.pair || '';
      document.getElementById('signalFormDir').value = item.direction || 'buy';
      document.getElementById('signalFormEntry').value = item.entry || '';
      document.getElementById('signalFormTp').value = item.take_profit || '';
      document.getElementById('signalFormSl').value = item.stop_loss || '';
      document.getElementById('signalFormRisk').value = item.risk_desc || '';
      document.getElementById('signalFormConf').value = item.confidence || 75;
      document.getElementById('signalFormStatus').value = item.status || 'published';
      document.getElementById('signalModalTitle').textContent = 'Edit Signal';
      openModal('signalModal');
    });
  };

  window.deleteSignal = function(id) {
    if (!confirm('Are you sure you want to delete this signal?')) return;
    sbDelete('featured_signals?id=eq.' + encodeURIComponent(id)).then(function() {
      alert('Signal deleted.');
      loadSignals();
    });
  };

  // 4. Live Classes CRUD
  wireModalTrigger('addLiveClassBtn', 'liveClassModal', function() {
    if (document.getElementById('liveClassForm')) document.getElementById('liveClassForm').reset();
    if (document.getElementById('liveClassFormId')) document.getElementById('liveClassFormId').value = '';
    if (document.getElementById('liveClassModalTitle')) document.getElementById('liveClassModalTitle').textContent = 'New Live Class';
  });
  wireModalClose('liveClassModalClose', 'liveClassModal');

  var liveClassForm = document.getElementById('liveClassForm');
  if (liveClassForm) {
    liveClassForm.addEventListener('submit', function(e) {
      e.preventDefault();
      var id = document.getElementById('liveClassFormId').value;
      var title = (document.getElementById('liveClassFormTitle').value || '').trim();
      var instructor = (document.getElementById('liveClassFormInstructor').value || '').trim();
      var photo = (document.getElementById('liveClassFormInstructorPhoto').value || '').trim();
      var desc = (document.getElementById('liveClassFormDesc').value || '').trim();
      var category = document.getElementById('liveClassFormCategory').value;
      var date = document.getElementById('liveClassFormDate').value;
      var time = document.getElementById('liveClassFormTime').value;
      var duration = parseInt(document.getElementById('liveClassFormDuration').value, 10) || 60;
      var isLive = document.getElementById('liveClassFormIsLive').checked;
      var meetingUrl = (document.getElementById('liveClassFormMeetingUrl').value || '').trim();

      if (!title || !instructor || !date || !time) {
        alert('Please fill in required fields (Title, Instructor, Date, Time).');
        return;
      }

      var scheduledAt = new Date(date + 'T' + time + ':00').toISOString();
      var payload = {
        title: title, instructor: instructor, instructor_photo: photo,
        description: desc, category: category, scheduled_at: scheduledAt,
        duration: duration, is_live: isLive, meeting_url: meetingUrl
      };
      if (id) payload.id = id;

      sbPost('live_classes' + (id ? '?id=eq.' + encodeURIComponent(id) : ''), payload, id ? 'PATCH' : 'POST')
        .then(function(res) {
          alert('Live class saved successfully.');
          closeModal('liveClassModal');
          liveClassForm.reset();
          loadLiveClasses();
        })
        .catch(function(err) { alert('Error saving live class: ' + err.message); });
    });
  }

  window.editLiveClass = function(id) {
    sbGet('live_classes', 'id=eq.' + encodeURIComponent(id)).then(function(data) {
      var item = (Array.isArray(data) && data[0]) ? data[0] : null;
      if (!item) return;
      document.getElementById('liveClassFormId').value = item.id;
      document.getElementById('liveClassFormTitle').value = item.title || '';
      document.getElementById('liveClassFormInstructor').value = item.instructor || '';
      document.getElementById('liveClassFormInstructorPhoto').value = item.instructor_photo || '';
      document.getElementById('liveClassFormDesc').value = item.description || '';
      document.getElementById('liveClassFormCategory').value = item.category || 'Trading Strategy';
      if (item.scheduled_at) {
        var d = new Date(item.scheduled_at);
        document.getElementById('liveClassFormDate').value = d.toISOString().substring(0,10);
        document.getElementById('liveClassFormTime').value = d.toTimeString().substring(0,5);
      }
      document.getElementById('liveClassFormDuration').value = item.duration || 60;
      document.getElementById('liveClassFormIsLive').checked = !!item.is_live;
      document.getElementById('liveClassFormMeetingUrl').value = item.meeting_url || '';
      document.getElementById('liveClassModalTitle').textContent = 'Edit Live Class';
      openModal('liveClassModal');
    });
  };

  window.deleteLiveClass = function(id) {
    if (!confirm('Are you sure you want to delete this live class?')) return;
    sbDelete('live_classes?id=eq.' + encodeURIComponent(id)).then(function() {
      alert('Live class deleted.');
      loadLiveClasses();
    });
  };

  // 5. Educational Articles CRUD
  wireModalTrigger('addArticleBtn', 'articleModal', function() {
    if (document.getElementById('articleForm')) document.getElementById('articleForm').reset();
    if (document.getElementById('articleFormId')) document.getElementById('articleFormId').value = '';
    if (document.getElementById('articleModalTitle')) document.getElementById('articleModalTitle').textContent = 'Add Article';
  });
  wireModalClose('articleModalClose', 'articleModal');

  var articleForm = document.getElementById('articleForm');
  if (articleForm) {
    articleForm.addEventListener('submit', function(e) {
      e.preventDefault();
      var id = document.getElementById('articleFormId').value;
      var title = (document.getElementById('articleFormTitle').value || '').trim();
      var desc = (document.getElementById('articleFormDesc').value || '').trim();
      var content = (document.getElementById('articleFormContent').value || '').trim();
      var cat = document.getElementById('articleFormCategory').value;
      var status = document.getElementById('articleFormStatus').value;

      if (!title || !content) { alert('Please enter article title and content.'); return; }

      var payload = { title: title, description: desc, content: content, category: cat, status: status };
      if (id) payload.id = id;

      sbPost('educational_articles' + (id ? '?id=eq.' + encodeURIComponent(id) : ''), payload, id ? 'PATCH' : 'POST')
        .then(function() {
          alert('Article saved successfully.');
          closeModal('articleModal');
          articleForm.reset();
          loadArticles();
        })
        .catch(function(err) { alert('Error saving article: ' + err.message); });
    });
  }

  window.editArticle = function(id) {
    sbGet('educational_articles', 'id=eq.' + encodeURIComponent(id)).then(function(data) {
      var item = (Array.isArray(data) && data[0]) ? data[0] : null;
      if (!item) return;
      document.getElementById('articleFormId').value = item.id;
      document.getElementById('articleFormTitle').value = item.title || '';
      document.getElementById('articleFormDesc').value = item.description || '';
      document.getElementById('articleFormContent').value = item.content || '';
      document.getElementById('articleFormCategory').value = item.category || 'Forex Basics';
      document.getElementById('articleFormStatus').value = item.status || 'published';
      document.getElementById('articleModalTitle').textContent = 'Edit Article';
      openModal('articleModal');
    });
  };

  window.deleteArticle = function(id) {
    if (!confirm('Are you sure you want to delete this article?')) return;
    sbDelete('educational_articles?id=eq.' + encodeURIComponent(id)).then(function() {
      alert('Article deleted.');
      loadArticles();
    });
  };

  // 6. Announcements CRUD
  wireModalTrigger('addAnnounceBtn', 'announceModal', function() {
    if (document.getElementById('announceForm')) document.getElementById('announceForm').reset();
    if (document.getElementById('announceFormId')) document.getElementById('announceFormId').value = '';
  });
  wireModalClose('announceModalClose', 'announceModal');

  var announceForm = document.getElementById('announceForm');
  if (announceForm) {
    announceForm.addEventListener('submit', function(e) {
      e.preventDefault();
      var id = document.getElementById('announceFormId').value;
      var title = (document.getElementById('announceFormTitle').value || '').trim();
      var msg = (document.getElementById('announceFormMsg').value || '').trim();
      var active = document.getElementById('announceFormActive').checked;

      if (!title || !msg) { alert('Please enter announcement title and message.'); return; }

      var payload = { title: title, message: msg, is_active: active };
      if (id) payload.id = id;

      sbPost('announcements' + (id ? '?id=eq.' + encodeURIComponent(id) : ''), payload, id ? 'PATCH' : 'POST')
        .then(function() {
          alert('Announcement saved successfully.');
          closeModal('announceModal');
          announceForm.reset();
          loadAnnouncements();
        })
        .catch(function(err) { alert('Error saving announcement: ' + err.message); });
    });
  }

  window.editAnnounce = function(id) {
    sbGet('announcements', 'id=eq.' + encodeURIComponent(id)).then(function(data) {
      var item = (Array.isArray(data) && data[0]) ? data[0] : null;
      if (!item) return;
      document.getElementById('announceFormId').value = item.id;
      document.getElementById('announceFormTitle').value = item.title || '';
      document.getElementById('announceFormMsg').value = item.message || '';
      document.getElementById('announceFormActive').checked = !!item.is_active;
      openModal('announceModal');
    });
  };

  window.deleteAnnounce = function(id) {
    if (!confirm('Are you sure you want to delete this announcement?')) return;
    sbDelete('announcements?id=eq.' + encodeURIComponent(id)).then(function() {
      alert('Announcement deleted.');
      loadAnnouncements();
    });
  };

  /* ── Init ── */
  var hash = window.location.hash.replace('#','');
  if (hash && sectionMap[hash]) {
    showSection(hash);
  } else {
    showSection('dashboard');
  }

})();
