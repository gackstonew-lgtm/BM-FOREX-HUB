<?php
// Central header used by front‑end pages (overview, landing, etc.)
// Mirrors the admin header to keep colors and layout consistent
require_once __DIR__ . '/engine_config.php';
?>
<header class="topbar">
    <a class="logo logo--text" href="index.php" aria-label="BM Forex Hub home">
        <span>BM Forex Hub Signal Terminal</span>
    </a>
    <nav aria-label="Section navigation">
        <a href="index.php">← Back to Dashboard</a>
    </nav>
    <div class="community-links" aria-label="Community links">
        <button type="button" class="theme-toggle" title="Toggle light / dark theme" aria-label="Toggle light / dark theme"><svg class="theme-toggle__sun" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg><svg class="theme-toggle__moon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></button>
        <a class="community-link" href="index.php">← Back to Dashboard</a>
        <span class="community-link community-link--user">👤 <span id="displayUsername">Trader</span></span>
        <a class="community-link community-link--logout" href="logout.php">Logout</a>
        <button type="button" class="hamburger-btn" id="hamburgerBtn" aria-label="Open navigation menu" aria-expanded="false" aria-controls="mobileNav">
            <span class="bar"><span></span><span></span><span></span></span>
            <span class="hamburger-btn__label">Menu</span>
        </button>
    </div>
</header>

<div class="mobile-nav" id="mobileNav" role="dialog" aria-modal="true" aria-label="Navigation menu">
    <button class="mobile-nav-close" id="mobileNavClose" aria-label="Close menu">&times;</button>
    <a href="index.php">Dashboard</a>
    <a href="overview.php">Market Overview</a>
    <a href="quick_tools.php">Quick Tools</a>
    <a href="index.php?section=signals">Signals Grid</a>
    <a href="trading.php">BM Quantum Edge</a>
    <a href="classes.php">Classes</a>
    <a href="crypto.php">&#8383; Crypto Trading</a>
    <a href="logout.php">Logout</a>
</div>
<script>
(function(){
  var btn = document.getElementById('hamburgerBtn');
  var nav = document.getElementById('mobileNav');
  var cls = document.getElementById('mobileNavClose');
  if (!btn || !nav) return;
  var open = function(){ nav.classList.add('open'); btn.setAttribute('aria-expanded','true'); document.body.style.overflow = 'hidden'; };
  var close = function(){ nav.classList.remove('open'); btn.setAttribute('aria-expanded','false'); document.body.style.overflow = ''; };
  btn.addEventListener('click', open);
  if (cls) cls.addEventListener('click', close);
  nav.querySelectorAll('a').forEach(function(a){ a.addEventListener('click', close); });
  nav.addEventListener('click', function(e){ if (e.target === nav) close(); });
  document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && nav.classList.contains('open')) close(); });
})();
</script>
