<?php
// BM Forex Hub - bottom mobile navigation tab bar & overlay drawer
$activeTab = $activeTab ?? '';

$tabs = [
    'dashboard' => ['index.php', 'Dashboard', '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>'],
    'markets'   => ['overview.php', 'Markets', '<svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>'],
    'trade'     => ['trading.php', 'Trade', '<svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>'],
    'learn'     => ['classes.php', 'Learn', '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>'],
    'menu'      => ['javascript:void(0)', 'Menu', '<svg viewBox="0 0 24 24" aria-hidden="true"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>'],
];
?>
<!-- Mobile Bottom Navigation Tabbar -->
<nav class="mobile-tabbar" aria-label="Primary mobile navigation">
    <?php foreach ($tabs as $key => $tab): ?>
        <?php if ($key === 'menu'): ?>
            <button type="button" class="mobile-tabbar__item mobile-tabbar__item--menu" id="mobileTabbarMenuBtn" onclick="bmToggleMobileSidebar()" aria-label="Open Navigation Menu">
                <?= $tab[2] ?>
                <span>Menu</span>
            </button>
        <?php else: ?>
            <a class="mobile-tabbar__item<?= $activeTab === $key ? ' active' : '' ?>" href="<?= htmlspecialchars($tab[0], ENT_QUOTES, 'UTF-8') ?>" aria-current="<?= $activeTab === $key ? 'page' : 'false' ?>">
                <?= $tab[2] ?>
                <span><?= htmlspecialchars($tab[1], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>

<!-- Global Mobile Navigation Menu Overlay -->
<div class="mobile-nav" id="mobileNav" role="dialog" aria-modal="true" aria-label="Navigation menu">
  <button type="button" class="mobile-nav-close" id="mobileNavClose" onclick="bmCloseMobileMenu()" aria-label="Close menu">&times;</button>
  <div style="font-size:0.75rem; color:#8fa3b8; text-transform:uppercase; letter-spacing:0.12em; margin-bottom:12px; font-weight:700;">BM FOREX HUB SERVICES</div>
  <a href="javascript:void(0)" onclick="typeof openProfileModal==='function'?openProfileModal():window.location.href='index.php'">Profile</a>
  <a href="index.php?section=dashboard">Dashboard</a>
  <a href="overview.php">Market Overview</a>
  <a href="quick_tools.php">Quick Tools</a>
  <a href="index.php?section=signals">Signals Grid</a>
  <a href="index.php?section=strength">Currency Strength</a>
  <a href="index.php?section=news">Economic Calendar</a>
  <a href="trading.php">BM Quantum Edge</a>
  <a href="classes.php">Classes</a>
  <a href="index.php?section=methodology">Methodology</a>
  <a href="subscribe.php?service=copy_trading">Copy Trading</a>
  <a href="bm_elites.php">BM Elites Trading Circle</a>
  <a href="crypto.php">Crypto Trading</a>
  <a href="logout.php" style="color:#f6465d;">Logout</a>
</div>

<script>
(function () {
  if (document.body) {
    document.body.classList.add('has-mobile-tabbar');
  }
})();

function bmToggleMobileSidebar() {
  var mobileNav = document.getElementById('mobileNav');
  if (mobileNav) {
    var isOpen = mobileNav.classList.contains('open');
    if (isOpen) {
      bmCloseMobileMenu();
    } else {
      mobileNav.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
  }
}

function bmCloseMobileMenu() {
  var mobileNav = document.getElementById('mobileNav');
  if (mobileNav) {
    mobileNav.classList.remove('open');
    document.body.style.overflow = '';
  }
}
</script>
