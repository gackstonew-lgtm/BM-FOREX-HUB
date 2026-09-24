<?php
$activeNav = $activeNav ?? 'dashboard';
?>
<!-- Unified Global Header -->
<header class="topbar topbar--premium" id="mobileHeader">
  <!-- Row 1: Brand Logo, Platform Search, User Actions & Profile -->
  <div class="topbar__primary-row">
    <a class="logo logo--text" href="index.php" aria-label="BM Forex Hub home">
      <img class="logo__img" src="BM-ForexHub-Logo-Circle.png" alt="BM Forex Hub" width="32" height="32">
      <span>BM FOREX HUB</span>
    </a>
    
    <div class="topbar-search-wrap">
      <svg class="topbar-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" class="topbar-search-input" id="globalSearchInput" placeholder="Search markets, signals, tools, classes..." aria-label="Search platform">
    </div>

    <div class="topbar__actions">
      <button type="button" class="theme-toggle" title="Toggle light / dark theme" aria-label="Toggle light / dark theme"><svg class="theme-toggle__sun" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg><svg class="theme-toggle__moon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></button>
      
      <div class="bell-wrap" id="bellWrap">
        <button class="bell-btn" id="bellBtn" aria-label="Announcements" aria-expanded="false">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span class="bell-badge" id="bellBadge" hidden>0</span>
        </button>
        <div class="bell-dropdown" id="bellDropdown" hidden>
          <div class="bell-dropdown__head">Announcements</div>
          <div class="bell-dropdown__list" id="bellList"></div>
        </div>
      </div>

      <div class="topbar__user-pill" id="topbarUserPill">
        <div class="topbar__user-avatar" id="topbarAvatarWrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <a href="javascript:void(0)" onclick="typeof openProfileModal === 'function' ? openProfileModal() : (window.location.href='index.php')" id="topbarProfileLink" style="text-decoration:none;display:flex;align-items:center;gap:6px;" aria-label="My Profile">
          <div class="topbar__user-greeting">
            <small>Welcome,</small>
            <strong id="displayUsername">Trader</strong>
            <span class="topbar__premium-badge" id="headerPremiumBadge" hidden>Premium</span>
          </div>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:.6;flex-shrink:0;"><polyline points="6 9 12 15 18 9"/></svg>
        </a>
      </div>

      <button class="hamburger-btn" id="hamburgerBtn" aria-label="Open navigation menu" aria-expanded="false" aria-controls="mobileNav">
        <div class="bar" aria-hidden="true"><span></span><span></span><span></span></div>
        <span class="hamburger-btn__label">Menu</span>
      </button>
    </div>
  </div>

</header>
