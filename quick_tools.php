<?php
/**
 * BM Forex Hub — Dedicated Quick Trading Tools Page
 * Professional Forex Risk Management Calculators.
 */
require_once __DIR__ . '/engine_config.php';

$pageTitle = 'Quick Trading Tools';
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
<title><?= htmlspecialchars($pageTitle) ?> | BM FOREX HUB</title>
<meta name="description" content="Calculate pip values, lot sizes, margin requirements, position sizing and profit before placing your trades with professional risk management calculators.">
<meta name="robots" content="noindex, nofollow, noarchive">
<link rel="icon" type="image/png" href="BM-ForexHub-Logo-Circle.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="basics.css?v=<?= filemtime('basics.css') ?>">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="css/mobile.css?v=<?= filemtime('css/mobile.css') ?>">
<script>var API_BASE = '<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>';</script>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:#0B0F14;color:#fff;font-family:'Inter',system-ui,sans-serif;min-height:100vh;overflow-x:hidden;display:flex;flex-direction:column}

/* Topbar */
.mo-top{display:flex;align-items:center;justify-content:space-between;padding:14px 24px;border-bottom:1px solid #283548;background:#101722;position:sticky;top:0;z-index:100}
.mo-top__brand{display:flex;align-items:center;gap:10px;text-decoration:none;color:#fff;font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:0.85rem}
.mo-top__brand img{width:32px;height:32px;border-radius:50%;border:1px solid rgba(22,119,255,.4)}
.mo-top__nav{display:flex;gap:8px}
.mo-top__nav a{color:rgba(255,255,255,.55);text-decoration:none;font-size:0.78rem;padding:6px 12px;border-radius:6px;transition:all .2s}
.mo-top__nav a:hover,.mo-top__nav a.active{color:#fff;background:rgba(22,119,255,.15)}
.mo-top__back{color:rgba(255,255,255,.5);text-decoration:none;font-size:0.82rem;display:flex;align-items:center;gap:4px;transition:color .2s}
.mo-top__back:hover{color:#1677FF}

/* Page Layout Wrapper */
.qt-wrap{max-width:1280px;width:100%;margin:0 auto;padding:28px 24px;flex:1}
.qt-header{margin-bottom:32px}
.qt-title{font-family:'Space Grotesk',sans-serif;font-size:1.6rem;font-weight:700;margin-bottom:6px;color:#ffffff}
.qt-subtitle{color:#1677FF;font-size:0.92rem;font-weight:600;margin-bottom:8px}
.qt-desc{color:#B8C3D1;font-size:0.85rem;line-height:1.6;max-width:800px}

/* Grid Layout */
.qt-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:18px;margin-bottom:32px}

/* Tool Cards */
.qt-card{background:#151D29;border:1px solid #283548;border-radius:14px;padding:24px;display:flex;flex-direction:column;justify-content:space-between;transition:transform 0.25s ease,border-color 0.25s ease,box-shadow 0.25s ease;box-shadow:0 4px 16px rgba(0,0,0,0.25)}
.qt-card:hover{transform:translateY(-5px);border-color:rgba(22,119,255,0.45);box-shadow:0 12px 30px rgba(22,119,255,0.15)}
.qt-card__icon-wrap{width:48px;height:48px;border-radius:12px;background:rgba(22,119,255,0.12);border:1px solid rgba(22,119,255,0.25);display:flex;align-items:center;justify-content:center;color:#1677FF;margin-bottom:18px}
.qt-card__title{font-family:'Space Grotesk',sans-serif;font-size:1.05rem;font-weight:700;color:#ffffff;margin-bottom:8px}
.qt-card__desc{font-size:0.8rem;color:#B8C3D1;line-height:1.5;margin-bottom:20px;flex:1}
.qt-card__btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:11px 16px;background:#1677FF;color:#ffffff;font-size:0.82rem;font-weight:600;border-radius:8px;border:none;cursor:pointer;transition:background 0.2s,transform 0.2s;text-decoration:none}
.qt-card__btn:hover{background:#2F80FF;transform:translateY(-1px)}
.qt-card__btn:focus-visible{outline:2px solid #1677FF;outline-offset:2px}

/* Modal Overlay & Styling */
.mo-modal-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.75);z-index:500;display:none;align-items:center;justify-content:center;backdrop-filter:blur(5px)}
.mo-modal-overlay.open{display:flex}
.mo-modal{background:#151D29;border:1px solid #283548;border-radius:14px;padding:28px;width:100%;max-width:440px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 40px rgba(0,0,0,0.5)}
.mo-modal__header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #283548}
.mo-modal__title{font-family:'Space Grotesk',sans-serif;font-size:1.1rem;font-weight:700;color:#fff}
.mo-modal__close{background:none;border:none;color:#7F8B99;font-size:1.4rem;cursor:pointer;padding:4px;line-height:1;transition:color 0.2s}
.mo-modal__close:hover{color:#fff}
.mo-calc-field{margin-bottom:16px}
.mo-calc-field label{display:block;font-size:0.75rem;color:#B8C3D1;margin-bottom:6px;font-weight:500}
.mo-calc-field input,.mo-calc-field select{width:100%;padding:10px 14px;background:#202B3A;border:1px solid #283548;border-radius:8px;color:#fff;font-size:0.88rem;outline:none;transition:border-color .2s}
.mo-calc-field input:focus,.mo-calc-field select:focus{border-color:#1677FF}
.mo-calc-field select option{background:#202B3A;color:#fff}
.mo-calc-result{padding:14px;background:rgba(22,199,132,.1);border:1px solid rgba(22,199,132,.25);border-radius:10px;margin-top:16px;text-align:center}
.mo-calc-result__label{font-size:0.68rem;color:#16C784;text-transform:uppercase;letter-spacing:0.04em;font-weight:600}
.mo-calc-result__value{font-family:'IBM Plex Mono',monospace;font-size:1.25rem;font-weight:700;color:#fff;margin-top:4px}
.mo-promo__cta{width:100%;background:#1677FF;color:#fff;border:none;padding:12px;border-radius:8px;font-size:0.85rem;font-weight:600;cursor:pointer;transition:all 0.2s}
.mo-promo__cta:hover{background:#2F80FF}

/* Media Queries for Grid Responsiveness */
@media(max-width:1100px){
  .qt-grid{grid-template-columns:repeat(3,1fr)}
}
@media(max-width:768px){
  .qt-grid{grid-template-columns:repeat(2,1fr);gap:14px}
  .qt-wrap{padding:20px 16px}
  .mo-top{padding:10px 14px}
  .mo-top__nav{display:flex;gap:6px;overflow-x:auto}
  .mo-top__nav a{padding:5px 10px;font-size:0.75rem;white-space:nowrap}
}
@media(max-width:540px){
  .qt-grid{grid-template-columns:1fr;gap:14px}
  .qt-title{font-size:1.3rem}
  .qt-subtitle{font-size:0.85rem}
}
</style>
</head>
<body class="has-sidebar">

<!-- ===== Dashboard Sidebar ===== -->
<?php $activeNav = 'tools'; include __DIR__ . '/components/global_sidebar.php'; ?>

<div class="dash-main-wrap">
<!-- Unified Global Header -->
<?php include __DIR__ . '/components/global_header.php'; ?>

<div class="qt-wrap">

  <!-- Page Header -->
  <div class="qt-header">
    <h1 class="qt-title">Quick Trading Tools</h1>
    <div class="qt-subtitle">Professional Forex Risk Management Calculators</div>
    <p class="qt-desc">Calculate pip values, lot sizes, margin requirements, position sizing and profit before placing your trades to manage risk with precision.</p>
  </div>

  <!-- Tools Grid -->
  <div class="qt-grid">

    <!-- 1. Pip Calculator -->
    <div class="qt-card">
      <div>
        <div class="qt-card__icon-wrap" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
        <h2 class="qt-card__title">Pip Calculator</h2>
        <p class="qt-card__desc">Calculate pip value based on lot size, currency pair and account currency.</p>
      </div>
      <button type="button" class="qt-card__btn" onclick="openCalc('pip')" aria-label="Open Pip Calculator">
        <span>Open Calculator</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
    </div>

    <!-- 2. Lot Size Calculator -->
    <div class="qt-card">
      <div>
        <div class="qt-card__icon-wrap" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 16l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1zM2 16l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1zM7 21h10M12 3v18"/></svg>
        </div>
        <h2 class="qt-card__title">Lot Size Calculator</h2>
        <p class="qt-card__desc">Determine the appropriate lot size based on account risk percentage.</p>
      </div>
      <button type="button" class="qt-card__btn" onclick="openCalc('lot')" aria-label="Open Lot Size Calculator">
        <span>Open Calculator</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
    </div>

    <!-- 3. Margin Calculator -->
    <div class="qt-card">
      <div>
        <div class="qt-card__icon-wrap" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
        </div>
        <h2 class="qt-card__title">Margin Calculator</h2>
        <p class="qt-card__desc">Estimate the margin required before opening a trade.</p>
      </div>
      <button type="button" class="qt-card__btn" onclick="openCalc('margin')" aria-label="Open Margin Calculator">
        <span>Open Calculator</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
    </div>

    <!-- 4. Position Size Calculator -->
    <div class="qt-card">
      <div>
        <div class="qt-card__icon-wrap" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
        </div>
        <h2 class="qt-card__title">Position Size Calculator</h2>
        <p class="qt-card__desc">Calculate position size using stop loss and account balance.</p>
      </div>
      <button type="button" class="qt-card__btn" onclick="openCalc('position')" aria-label="Open Position Size Calculator">
        <span>Open Calculator</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
    </div>

    <!-- 5. Profit Calculator -->
    <div class="qt-card">
      <div>
        <div class="qt-card__icon-wrap" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <h2 class="qt-card__title">Profit Calculator</h2>
        <p class="qt-card__desc">Estimate potential profit or loss before executing your trade.</p>
      </div>
      <button type="button" class="qt-card__btn" onclick="openCalc('profit')" aria-label="Open Profit Calculator">
        <span>Open Calculator</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
    </div>

    <!-- 6. World Live Currency Converter -->
    <div class="qt-card">
      <div>
        <div class="qt-card__icon-wrap" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        </div>
        <h2 class="qt-card__title">Live Currency Converter</h2>
        <p class="qt-card__desc">Convert world currencies in real-time including KSh (KES), USD, EUR, GBP, BTC & Gold.</p>
      </div>
      <button type="button" class="qt-card__btn" onclick="openCalc('converter')" aria-label="Open Currency Converter">
        <span>Open Converter</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
    </div>

  </div>

</div><!-- /.qt-wrap -->

<!-- Shared Calculator Modal -->
<div class="mo-modal-overlay" id="calcModal" role="dialog" aria-modal="true" aria-labelledby="calcTitle">
  <div class="mo-modal">
    <div class="mo-modal__header">
      <span class="mo-modal__title" id="calcTitle">Calculator</span>
      <button type="button" class="mo-modal__close" onclick="closeCalc()" aria-label="Close calculator">&times;</button>
    </div>
    <div id="calcBody"></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
<script src="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/js/auth.js?v=<?= filemtime('js/auth.js') ?>"></script>
<script src="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/js/calculators.js?v=<?= filemtime('js/calculators.js') ?>"></script>
<script src="js/motion.js" defer></script>
<?php $activeTab = 'trade'; include __DIR__ . '/components/mobile-tabbar.php'; ?>
</body>
</html>
