<?php
$activeNav = $activeNav ?? 'dashboard';
?>
<!-- ===== Dashboard Sidebar ===== -->
<aside class="dash-sidebar" id="dashSidebar" aria-label="Dashboard sidebar">
  <div class="dash-sidebar__brand">
    <a href="index.php" class="dash-sidebar__logo">
      <img src="BM-ForexHub-Logo-Circle.png" alt="BM Forex Hub">
      <span class="dash-sidebar__logo-text">BM FOREX HUB</span>
    </a>
    <button type="button" class="dash-sidebar__toggle-btn" id="dashSidebarCollapseBtn" title="Toggle sidebar collapse" aria-label="Toggle sidebar collapse">
      <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
  </div>
  <nav class="dash-sidebar__nav" aria-label="Main navigation">
    <a href="javascript:void(0)" class="dash-sidebar__item" onclick="typeof openProfileModal === 'function' ? openProfileModal() : (window.location.href='index.php')" id="sidebarProfileLink" data-tooltip="User Profile">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <span class="dash-sidebar__text">Profile</span>
    </a>

    <a href="index.php?section=dashboard" class="dash-sidebar__item <?= $activeNav === 'dashboard' ? 'active' : '' ?>" data-bm-section="dashboard" onclick="if(typeof bmShowSection === 'function') { bmShowSection('dashboard'); return false; }" data-tooltip="Dashboard">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      <span class="dash-sidebar__text">Dashboard</span>
    </a>

    <a href="overview.php" class="dash-sidebar__item <?= $activeNav === 'market' ? 'active' : '' ?>" data-tooltip="Market Overview">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <line x1="18" y1="20" x2="18" y2="10"/>
        <line x1="12" y1="20" x2="12" y2="4"/>
        <line x1="6" y1="20" x2="6" y2="14"/>
      </svg>
      <span class="dash-sidebar__text">Market Overview</span>
    </a>

    <a href="quick_tools.php" class="dash-sidebar__item <?= $activeNav === 'tools' ? 'active' : '' ?>" data-tooltip="Quick Tools">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
      <span class="dash-sidebar__text">Quick Tools</span>
    </a>

    <a href="index.php?section=signals" class="dash-sidebar__item <?= $activeNav === 'signals' ? 'active' : '' ?>" data-bm-section="signals" onclick="if(typeof bmShowSection === 'function') { bmShowSection('signals'); return false; }" data-tooltip="Signals Grid">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      <span class="dash-sidebar__text">Signals Grid</span>
    </a>

    <a href="index.php?section=strength" class="dash-sidebar__item <?= $activeNav === 'strength' ? 'active' : '' ?>" data-bm-section="strength" onclick="if(typeof bmShowSection === 'function') { bmShowSection('strength'); return false; }" data-tooltip="Currency Strength">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="5 12 12 5 19 12"/></svg>
      <span class="dash-sidebar__text">Currency Strength</span>
    </a>

    <a href="index.php?section=news" class="dash-sidebar__item <?= $activeNav === 'news' ? 'active' : '' ?>" data-bm-section="news" onclick="if(typeof bmShowSection === 'function') { bmShowSection('news'); return false; }" data-tooltip="Economic Calendar">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      <span class="dash-sidebar__text">Economic Calendar</span>
    </a>

    <a href="trading.php" class="dash-sidebar__item <?= $activeNav === 'trading' ? 'active' : '' ?>" data-tooltip="BM Quantum Edge">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
      <span class="dash-sidebar__text">BM Quantum Edge</span>
    </a>

    <a href="classes.php" class="dash-sidebar__item <?= $activeNav === 'classes' ? 'active' : '' ?>" data-tooltip="Classes">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
      <span class="dash-sidebar__text">Classes</span>
    </a>

    <a href="index.php?section=methodology" class="dash-sidebar__item <?= $activeNav === 'methodology' ? 'active' : '' ?>" data-bm-section="methodology" onclick="if(typeof bmShowSection === 'function') { bmShowSection('methodology'); return false; }" data-tooltip="Methodology">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
      <span class="dash-sidebar__text">Methodology</span>
    </a>

    <a href="subscribe.php?service=copy_trading" class="dash-sidebar__item <?= $activeNav === 'copy_trading' ? 'active' : '' ?>" data-tooltip="Copy Trading">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
      <span class="dash-sidebar__text">Copy Trading</span>
    </a>

    <a href="bm_elites.php" class="dash-sidebar__item <?= $activeNav === 'elites' ? 'active' : '' ?>" data-tooltip="BM Elites Trading Circle">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      <span class="dash-sidebar__text">BM Elites Trading Circle</span>
    </a>

    <a href="crypto.php" class="dash-sidebar__item <?= $activeNav === 'crypto' ? 'active' : '' ?>" data-tooltip="Crypto Trading">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.5 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-5.5-6z"/><path d="M13.5 2v5.5H19"/><path d="M9 13h6M9 17h4"/></svg>
      <span class="dash-sidebar__text">Crypto Trading</span>
    </a>
  </nav>
  <div class="dash-sidebar__bottom">
    <a href="logout.php" class="dash-sidebar__item dash-sidebar__logout" data-tooltip="Logout">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      <span class="dash-sidebar__text">Logout</span>
    </a>
  </div>
</aside>
