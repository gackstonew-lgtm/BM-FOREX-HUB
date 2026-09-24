<?php require_once __DIR__ . '/engine_config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="css/theme-light.css?v=1">
<script src="js/theme.js" defer></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require_once __DIR__ . '/components/pwa_meta.php'; ?>
<title>BM FOREX HUB | Dashboard</title>
<meta name="description" content="BM FOREX HUB member signal terminal with live market analysis, education tools, economic calendar and community resources.">
<meta name="keywords" content="BM Forex Hub, Forex Signals, Forex Trading, Trading Education, Gold Signals, XAUUSD, Forex Community">
<link rel="icon" type="image/png" href="BM-ForexHub-Logo-Circle.png">
<meta name="robots" content="noindex, nofollow, noarchive">
<meta property="og:title" content="BM Forex Hub | Premium Forex Signals &amp; Trading Education">
<meta property="og:description" content="Join BM Forex Hub for premium forex signals, market analysis, trading education, and a supportive trading community.">
<meta property="og:type" content="website">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="basics.css?v=<?= filemtime('basics.css') ?>">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="css/mobile.css?v=<?= filemtime('css/mobile.css') ?>">
<link rel="stylesheet" href="css/ai-assistant.css">
<link rel="stylesheet" href="css/crypto-widget.css?v=<?= filemtime('css/crypto-widget.css') ?>">

<style>
/* Push content right when sidebar visible */
@media (min-width: 900px) {
  body.has-sidebar .dash-main-wrap {
    margin-left: var(--sidebar-w, 260px);
    transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  }
}
@media (max-width: 899px) {
  .dash-sidebar {
    transform: translateX(-100%);
    transition: transform 0.25s ease;
  }
  .dash-sidebar.open {
    transform: translateX(0);
  }
  body.has-sidebar .dash-main-wrap {
    margin-left: 0;
  }
  .topbar__user-row {
    padding-top: 8px; margin-top: 4px;
    border-top: 1px solid rgba(30,45,66,.5);
  }
}

/* ===== BM VIEW SWITCHING ===== */
.bm-view { display: none; }
.bm-view.bm-view--active { display: block; }

/* ===== NEW TOPBAR STYLE OVERRIDES ===== */
.topbar { background: #101722; border-bottom: 1px solid #1e2d42; }
.topbar .logo--text span { font-size: 0.85rem; font-weight: 700; letter-spacing: 0.06em; color: #fff; }
.topbar nav { display: none; }  /* hide old section nav — sidebar handles navigation */

.topbar__user-row {
  display: flex; align-items: center; gap: 10px;
  padding-top: 0; margin-top: 0; border-top: none;
  color: #fff; font-size: 0.83rem;
}
.topbar__user-row .topbar__user-avatar {
  width: 34px; height: 34px; background: #1e2d42; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  border: 1px solid rgba(22,119,255,0.35); flex-shrink: 0;
  font-size: 0.95rem; color: #8fa3b8; overflow: hidden;
}
.topbar__user-row .topbar__user-greeting { line-height: 1.2; }
.topbar__user-row .topbar__user-greeting small { display: block; color: #8fa3b8; font-size: 0.7rem; }
.topbar__user-row .topbar__user-greeting strong { font-weight: 600; }

/* ===== DASHBOARD OVERVIEW ===== */
.dash-overview {
  padding: 28px 30px 20px;
}
.dash-overview__header { margin-bottom: 24px; }
.dash-overview__title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.65rem; font-weight: 700; color: #fff; margin: 0 0 5px;
}
.dash-overview__subtitle { color: #8fa3b8; font-size: 0.88rem; margin: 0; }

/* Note: feature cards removed from dashboard per redesign — users access via sidebar */

/* Live Classes section */
.dash-classes-section {
  background: #151d2a; border: 1px solid #1e2d42;
  border-radius: 14px; padding: 20px 22px; margin-bottom: 26px;
}
.dash-section-header {
  display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;
}
.dash-section-header h2 {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.0rem; font-weight: 700; color: #fff; margin: 0;
  display: flex; align-items: center; gap: 8px;
}
.dash-view-all {
  color: #1677FF; font-size: 0.79rem; font-weight: 500;
  text-decoration: none; display: flex; align-items: center; gap: 4px;
  padding: 4px 10px; border-radius: 6px; border: 1px solid transparent;
  transition: background .15s, border-color .15s;
}
.dash-view-all:hover { color: #5b9eff; background: rgba(22,119,255,.08); border-color: rgba(22,119,255,.2); }
.dash-class-card {
  display: flex; align-items: center; gap: 16px;
  background: rgba(22,119,255,.06); border: 1px solid rgba(22,119,255,.2);
  border-radius: 10px; padding: 14px 18px;
}
.dash-class-icon {
  width: 52px; height: 52px; background: rgba(22,119,255,.15);
  border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.dash-class-info { flex: 1; min-width: 0; }
.dash-class-title-row {
  display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;
}
.dash-class-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 0.94rem; font-weight: 600; color: #fff; margin: 0;
}
.dash-class-live-badge {
  background: rgba(34,197,94,.15); border: 1px solid rgba(34,197,94,.4);
  color: #22C55E; font-size: 0.67rem; font-weight: 700;
  letter-spacing: .05em; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;
}
.dash-class-meta { display: flex; gap: 18px; flex-wrap: wrap; }
.dash-class-meta-item {
  display: flex; align-items: center; gap: 5px;
  color: #8fa3b8; font-size: 0.77rem;
}
.dash-class-btn {
  background: #1677FF; color: #fff; border: none;
  padding: 10px 20px; border-radius: 8px; font-size: 0.81rem; font-weight: 600;
  cursor: pointer; text-decoration: none; white-space: nowrap;
  flex-shrink: 0; transition: background .15s; display: inline-block;
}
.dash-class-btn:hover { background: #1565e0; color: #fff; }

/* ===== Welcome banner meta row ===== */
.dash-welcome {
  background: linear-gradient(135deg, rgba(22,119,255,.1) 0%, rgba(22,119,255,.04) 60%, transparent 100%);
  border: 1px solid rgba(22,119,255,.18);
  border-radius: 14px; padding: 24px 26px;
  display: flex; align-items: flex-start; justify-content: space-between;
  flex-wrap: wrap; gap: 16px; margin-bottom: 26px;
}
.dash-welcome__left { flex: 1; min-width: 0; }
.dash-overview__title { font-family: 'Space Grotesk', sans-serif; font-size: 1.75rem; font-weight: 700; color: #fff; margin: 0 0 4px; }
.dash-overview__title .dash-name-accent { color: #1677FF; }
.dash-overview__subtitle { color: #8fa3b8; font-size: 0.88rem; margin: 0 0 14px; }
.dash-welcome__stats { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.dash-welcome__meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.dash-welcome__meta-item {
  background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08); border-radius: 8px;
  padding: 5px 12px; color: #8fa3b8; font-size: 0.74rem; font-weight: 500;
  display: flex; align-items: center; gap: 5px;
}
.dash-welcome__meta-item svg { color: #5b6475; flex-shrink: 0; }
.dash-welcome__status { color: #22C55E; border-color: rgba(34,197,94,.25); background: rgba(34,197,94,.06); }
.dash-welcome__account-status { color: #1677FF; border-color: rgba(22,119,255,.25); background: rgba(22,119,255,.06); }
.dash-welcome__right { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; flex-shrink: 0; }
.dash-welcome__badge {
  background: rgba(22,119,255,.15); border: 1px solid rgba(22,119,255,.3); border-radius: 20px;
  color: #5b9eff; font-size: 0.72rem; font-weight: 700; padding: 4px 14px; letter-spacing: .04em; text-transform: uppercase;
}
.dash-welcome__quick-summary { color: #8fa3b8; font-size: 0.76rem; text-align: right; }

/* ===== Quick action cards ===== */
.dash-quick-actions {
  display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; margin-bottom: 26px;
}
.dash-qa-card {
  background: #151d2a; border: 1px solid #1e2d42; border-radius: 12px;
  padding: 16px 8px 14px; text-decoration: none; color: #cdd6e3;
  display: flex; flex-direction: column; align-items: center; gap: 9px;
  font-size: 0.72rem; font-weight: 500; text-align: center;
  transition: background .18s, border-color .18s, transform .15s, box-shadow .15s; cursor: pointer;
}
.dash-qa-card:hover {
  background: #1a2535; border-color: rgba(22,119,255,.45); transform: translateY(-3px);
  color: #fff; box-shadow: 0 6px 20px rgba(22,119,255,.12);
}
.dash-qa-card svg { color: #1677FF; transition: color .15s; }
.dash-qa-card:hover svg { color: #5b9eff; }
.dash-qa-card--accent { border-color: rgba(240,180,41,.35); color: var(--gold,#F0B429); }
.dash-qa-card--accent svg { color: var(--gold,#F0B429); }
.dash-qa-card--accent:hover { border-color: var(--gold,#F0B429); color: var(--gold,#F0B429); box-shadow: 0 6px 20px rgba(240,180,41,.12); }

/* ===== Live Classes expanded ===== */
.dash-classes-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; align-items: stretch; }
.dash-class-card--main { height: 100%; }
.dash-classes-upcoming {
  background: rgba(255,255,255,.02); border: 1px solid #1e2d42;
  border-radius: 10px; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px;
}
.dash-classes-upcoming h4 {
  margin: 0 0 2px; color: #5b6475; font-size: 0.7rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: .04em;
}
.dash-upcoming-item {
  display: flex; align-items: flex-start; gap: 9px; text-decoration: none;
  color: #cdd6e3; padding: 7px 6px; border-radius: 7px; transition: background .15s;
}
.dash-upcoming-item:hover { background: rgba(22,119,255,.08); }
.dash-upcoming-item svg { flex-shrink: 0; margin-top: 2px; color: #1677FF; }
.dash-upcoming-item span { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.dash-upcoming-item strong { font-size: 0.79rem; font-weight: 600; color: #fff; }
.dash-upcoming-item small { font-size: 0.71rem; color: #8fa3b8; }

/* ===== Premium Trading Center ===== */
.dash-premium-center {
  display: grid; grid-template-columns: 1.3fr 1fr; gap: 22px;
  background: linear-gradient(135deg, #1a2535 0%, #151d2a 100%);
  border: 1px solid #1e2d42; border-radius: 14px;
  padding: 24px 26px; margin-bottom: 26px; align-items: center;
  position: relative; overflow: hidden;
}
.dash-premium-center::before {
  content: ''; position: absolute; top: 0; right: 0;
  width: 260px; height: 260px; border-radius: 50%;
  background: radial-gradient(circle, rgba(22,119,255,.08) 0%, transparent 70%);
  transform: translate(30%, -30%); pointer-events: none;
}
.dash-premium-center__main h2 {
  font-family: 'Space Grotesk', sans-serif; color: #fff;
  font-size: 1.35rem; font-weight: 700; margin: 6px 0 10px; line-height: 1.25;
}
.dash-premium-center__main p { color: #8fa3b8; font-size: 0.83rem; line-height: 1.55; margin: 0 0 16px; }
.dash-premium-center__cta { display: flex; align-items: center; gap: 10px; }
.dash-premium-center__actions {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;
}
.dash-premium-action {
  background: rgba(255,255,255,.03); border: 1px solid #1e2d42; border-radius: 10px;
  padding: 12px 6px; text-decoration: none; color: #cdd6e3;
  display: flex; flex-direction: column; align-items: center; gap: 6px;
  font-size: 0.68rem; font-weight: 500; text-align: center; cursor: pointer;
  transition: background .2s, border-color .2s;
}
.dash-premium-action:hover { background: rgba(22,119,255,.1); border-color: rgba(22,119,255,.4); color: #fff; }
.dash-premium-action--accent { border-color: rgba(240,180,41,.35); color: var(--gold,#F0B429); }

/* ===== Learning Center ===== */


/* ===== Contact card ===== */
.dash-contact-card {
  display: flex; align-items: center; gap: 14px;
  background: #151d2a; border: 1px solid #1e2d42; border-radius: 14px;
  padding: 18px 22px; text-decoration: none; margin-bottom: 26px;
  transition: background .18s, border-color .18s, box-shadow .18s;
}
.dash-contact-card:hover { background: #1a2535; border-color: rgba(22,119,255,.4); box-shadow: 0 4px 18px rgba(22,119,255,.1); }
.dash-contact-card__icon {
  width: 44px; height: 44px; border-radius: 12px; background: rgba(22,119,255,.15);
  color: #1677FF; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.dash-contact-card__text { display: flex; flex-direction: column; gap: 3px; flex: 1; }
.dash-contact-card__text strong { color: #fff; font-size: 0.92rem; font-weight: 600; }
.dash-contact-card__text span { color: #8fa3b8; font-size: 0.79rem; }
.dash-contact-card__arrow { color: #5b6475; flex-shrink: 0; transition: transform .18s, color .15s; }
.dash-contact-card:hover .dash-contact-card__arrow { transform: translateX(3px); color: #1677FF; }

/* ===== Header search + premium badge ===== */
.topbar__search {
  display: flex; align-items: center; gap: 8px;
  background: #151d2a; border: 1px solid #1e2d42; border-radius: 8px;
  padding: 7px 12px; color: #5b6475; flex: 1; max-width: 320px; margin: 0 20px;
}
.topbar__search input {
  background: transparent; border: none; outline: none; color: #cdd6e3;
  font-size: 0.8rem; width: 100%;
}
.topbar__search input::placeholder { color: #5b6475; }
.topbar__premium-badge {
  background: rgba(240,180,41,.15); color: var(--gold,#F0B429);
  border: 1px solid rgba(240,180,41,.35); border-radius: 5px;
  font-size: 0.62rem; font-weight: 700; letter-spacing: .03em;
  text-transform: uppercase; padding: 1px 6px; margin-left: 4px;
}
@media (max-width: 900px) {
  .topbar__search { display: none; }
}

/* Responsive */
@media (max-width: 1100px) {
  .dash-quick-actions  { grid-template-columns: repeat(4, 1fr); }
  .dash-classes-grid   { grid-template-columns: 1fr; }
  .dash-premium-center { grid-template-columns: 1fr; }
  .dash-premium-center__actions { grid-template-columns: repeat(4, 1fr); }
}
@media (max-width: 900px) {
  .dash-overview       { padding: 20px 18px 8px; }
  .dash-status-bar     { width: 100%; }
}
@media (max-width: 640px) {
  .dash-overview       { padding: 16px 14px 0; }
  .dash-class-card     { flex-direction: column; align-items: flex-start; }
  .dash-class-btn      { width: 100%; text-align: center; }
  .dash-overview__title{ font-size: 1.35rem; }
  .dash-quick-actions  { grid-template-columns: repeat(3, 1fr); gap: 8px; }
  .dash-premium-center { padding: 20px 16px; }
  .dash-premium-center__actions { grid-template-columns: repeat(3, 1fr); }
  .dash-premium-center__main h2 { font-size: 1.15rem; }
  .dash-premium-center__cta { flex-wrap: wrap; }
  .dash-welcome        { flex-direction: column; align-items: flex-start; }
  .dash-welcome__right { align-items: flex-start; }
  .dash-classes-section { padding: 16px 14px; }
  .dash-section-header { align-items: flex-start; gap: 10px; }
  .dash-contact-card   { padding: 16px 16px; }
  .dash-status-bar     { margin-top: 12px; }
}
@media (max-width: 480px) {
  .dash-quick-actions  { grid-template-columns: repeat(3, 1fr); gap: 6px; }
  .dash-qa-card        { padding: 12px 4px 10px; gap: 6px; font-size: 0.68rem; }
  .dash-premium-center__actions { grid-template-columns: repeat(2, 1fr); }
  .dash-class-meta     { gap: 10px; }
  .dash-upcoming-item span { min-width: 0; }
}
@media (max-width: 420px) {
  .dash-quick-actions  { grid-template-columns: repeat(2, 1fr); }
  .dash-status-chip    { font-size: 0.7rem; padding: 5px 9px; }
}
@media (max-width: 360px) {
  .dash-welcome__badge { align-self: flex-start; }
}
</style>
<script>var API_BASE = '<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>';</script>
</head>
<body id="top" class="has-sidebar">

<!-- Ambient Blurred Background Layer -->
<div class="bm-ambient-glow" aria-hidden="true">
  <div class="bm-ambient-glow__blob bm-ambient-glow__blob--1"></div>
  <div class="bm-ambient-glow__blob bm-ambient-glow__blob--2"></div>
</div>

<!-- ===== Dashboard Sidebar ===== -->
<?php $activeNav = 'dashboard'; include __DIR__ . '/components/global_sidebar.php'; ?>

<!-- ===== Sidebar main content wrapper ===== -->
<div class="dash-main-wrap">

<a class="skip-link" href="javascript:void(0)" onclick="bmShowSection('signals')">Skip to live signals</a>

<!-- Unified Global Header -->
<?php include __DIR__ . '/components/global_header.php'; ?>

<!-- ===== DASHBOARD OVERVIEW VIEW ===== -->
<div id="view-dashboard" class="bm-view bm-view--active">
  <div class="dash-overview dash-workspace-shell">

    <!-- ===== In-App Compliance Notice Banner for Active Elite Members ===== -->
    <div id="dashEliteComplianceBanner" style="display:none; background: linear-gradient(135deg, rgba(240, 180, 41, 0.12) 0%, rgba(22, 119, 255, 0.12) 100%); border: 1px solid rgba(240, 180, 41, 0.4); border-radius: 14px; padding: 18px 22px; margin-bottom: 24px; position: relative;">
      <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 14px; flex: 1; min-width: 280px;">
          <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(240, 180, 41, 0.2); color: #F0B429; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0;">&#9888;</div>
          <div>
            <strong style="color: #F0B429; font-size: 0.95rem; display: block; margin-bottom: 2px;">Action Required: BM Elite Terms Acceptance (v1.0)</strong>
            <span style="color: #8fa3b8; font-size: 0.82rem; line-height: 1.45;">As an active BM Elite member, please review and accept the official updated Terms &amp; Conditions to maintain your VIP privileges and WhatsApp mentorship access.</span>
          </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
          <a href="terms-elite.php" style="color: #8fa3b8; font-size: 0.8rem; text-decoration: none; padding: 8px 14px; border: 1px solid #283548; border-radius: 8px; transition: color .15s;">View Terms</a>
          <a href="elite_enrollment.php?mode=compliance" class="btn-upgrade-elites" style="background: #F0B429; color: #0B0F14; font-weight: 700; text-decoration: none; padding: 8px 18px; border-radius: 8px; font-size: 0.83rem; display: inline-flex; align-items: center; gap: 6px;">
            Accept Terms &rarr;
          </a>
        </div>
      </div>
    </div>

    <!-- ===== SECTION 1: Welcome Banner ===== -->
    <div class="dash-welcome">
      <div class="dash-welcome__left">
        <h1 class="dash-overview__title">Welcome back, <span class="dash-name-accent" id="dashWelcomeName">Trader</span></h1>
        <p class="dash-overview__subtitle">Here&rsquo;s what&rsquo;s happening with your trading journey today.</p>
        
        <!-- ===== Compact Responsive Account & Premium Status Bar ===== -->
        <div class="dash-status-bar" id="dashStatusBar" style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; align-items: center;">
          <!-- Date Badge -->
          <div class="dash-status-chip" style="background: rgba(30, 45, 66, 0.4); border: 1px solid #1e2d42; border-radius: 8px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 0.78rem; color: #8fa3b8;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span id="dashCurrentDateChip">&mdash;</span><span id="dashCurrentDate" style="display:none;"></span>          </div>

          <!-- Account Status Badge -->
          <div class="dash-status-chip" style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.25); border-radius: 8px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 0.78rem; color: #22C55E; font-weight: 500;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <span>Account Active</span>
          </div>

          <!-- Membership Status Chip -->
          <a href="javascript:void(0)" onclick="openSubscriptionQuickModal()" id="dashMembershipBadge" class="dash-status-chip" style="background: rgba(246, 70, 93, 0.1); border: 1px solid rgba(246, 70, 93, 0.3); border-radius: 8px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 0.78rem; color: #f6465d; font-weight: 600; text-decoration: none; cursor: pointer; transition: all 0.15s ease;" title="Click to view subscription details">
            <span id="dashMembershipBadgeText">&#9888;&#65039; Subscription Required</span>
          </a>

          <!-- Plan Chip -->
          <div class="dash-status-chip" style="background: rgba(30, 45, 66, 0.4); border: 1px solid #1e2d42; border-radius: 8px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 0.78rem; color: #8fa3b8;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
            <span id="dashPlanNameChip">Plan: &mdash;</span>
          </div>

          <!-- Expiry Chip -->
          <div class="dash-status-chip" id="dashExpiryChip" style="background: rgba(30, 45, 66, 0.4); border: 1px solid #1e2d42; border-radius: 8px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 0.78rem; color: #8fa3b8;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span id="dashExpiryBadgeText">Expiry: &mdash;</span>
          </div>

          <!-- Membership status (used by applyAccessLevel) -->
          <span class="dash-status-chip" style="background: rgba(240, 180, 41, 0.1); border: 1px solid rgba(240, 180, 41, 0.3); border-radius: 8px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 0.78rem; color: #F0B429; font-weight: 500;" id="dashMembershipStatus" hidden>Trial expired</span>
        </div>
      </div>
      <div class="dash-welcome__right">
        <span class="dash-welcome__badge" id="dashPremiumBadge" hidden>&#9733; Premium</span>
        <span class="dash-welcome__quick-summary">Markets open &mdash; trade with precision</span>
      </div>
    </div>

    <!-- ===== SECTION 2: Quick Action Cards ===== -->
    <div class="dash-quick-actions">
      <a href="https://www.tiktok.com/@bm_forex?_r=1&amp;_t=ZS-97waauJBTBS" target="_blank" rel="noopener noreferrer" class="dash-qa-card">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
        <span>Live Trading</span>
      </a>
      <a href="javascript:void(0)" onclick="bmShowSection('signals')" class="dash-qa-card">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        <span>Signal Grid</span>
      </a>
      <a href="https://wa.me/254785618608" target="_blank" rel="noopener noreferrer" class="dash-qa-card">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
        <span>Classes</span>
      </a>
      <a href="https://wa.me/254785618608" target="_blank" rel="noopener noreferrer" class="dash-qa-card">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span>Events</span>
      </a>
      <a href="subscribe.php?service=copy_trading" class="dash-qa-card">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
        <span>Copy Trading</span>
      </a>
      <a href="javascript:void(0)" onclick="bmShowSection('methodology')" class="dash-qa-card">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
        <span>Methodology</span>
      </a>
      <a href="subscribe.php" class="dash-qa-card dash-qa-card--accent">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        <span>Subscribe</span>
      </a>
    </div>

    <!-- ===== REPOSITIONED LIVE MARKET TICKER TAPE ===== -->
    <div class="dash-ticker-box" style="margin-bottom: 24px;">
      <div class="dash-ticker-header">
        <span class="dash-ticker-tag"><span class="pulse-dot">●</span> LIVE MARKET QUOTES</span>
        <span class="dash-ticker-sub">Real-time TradingView market feeds</span>
      </div>
      <div class="dash-ticker-inner">
        <div class="tradingview-widget-container" id="tvTickerContainer"></div>
      </div>
    </div>

    <!-- ===== SECTION 3: Live Classes (expanded) ===== -->
    <div class="dash-classes-section">
      <div class="dash-section-header">
        <h2>Live Classes</h2>
        <a href="https://wa.me/254785618608" target="_blank" rel="noopener noreferrer" class="dash-view-all">View All &rarr;</a>
      </div>
      <div class="dash-classes-grid">
        <div class="dash-class-card dash-class-card--main">
          <div class="dash-class-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#1677FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="15" rx="2"/><polyline points="17 2 12 7 7 2"/></svg>
          </div>
          <div class="dash-class-info">
            <div class="dash-class-title-row">
              <span class="dash-class-title">Live Trading Strategy Class</span>
              <span class="dash-class-live-badge">LIVE</span>
            </div>
            <div class="dash-class-meta">
              <span class="dash-class-meta-item">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Date: Every Monday
              </span>
              <span class="dash-class-meta-item">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Time: 8:00 PM (EAT)
              </span>
              <span class="dash-class-meta-item">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Instructor: BM Forex Hub Team
              </span>
            </div>
          </div>
          <a href="classes.php" class="dash-class-btn">Join Class</a>
        </div>
        <div class="dash-classes-upcoming">
          <h4>Upcoming</h4>
          <a href="classes.php" class="dash-upcoming-item">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
            <span>
              <strong>Market Structure Deep Dive</strong>
              <small>Wednesday &middot; 8:00 PM (EAT)</small>
            </span>
          </a>
          <a href="https://wa.me/254785618608" target="_blank" rel="noopener noreferrer" class="dash-upcoming-item">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
            <span>
              <strong>Liquidity &amp; Smart Money Concepts</strong>
              <small>Friday &middot; 8:00 PM (EAT)</small>
            </span>
          </a>
          <a href="https://wa.me/254785618608" target="_blank" rel="noopener noreferrer" class="dash-upcoming-item">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
            <span>
              <strong>Risk Management Essentials</strong>
              <small>Next Monday &middot; 8:00 PM (EAT)</small>
            </span>
          </a>
        </div>
      </div>
    </div>

    <!-- ===== SECTION 7: Contact ===== -->
    <a href="contact.php" class="dash-contact-card">
      <div class="dash-contact-card__icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <div class="dash-contact-card__text">
        <strong>Contact</strong>
        <span>We&rsquo;re here to help. Reach out to us anytime.</span>
      </div>
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dash-contact-card__arrow"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </a>
  </div>
</div>
<!-- END #view-dashboard -->

<!-- Mobile nav overlay -->
<div class="mobile-nav" id="mobileNav" role="dialog" aria-modal="true" aria-label="Navigation menu">
  <button class="mobile-nav-close" id="mobileNavClose" aria-label="Close menu">&times;</button>
  <a href="javascript:void(0)" onclick="bmShowSection('dashboard');document.getElementById('mobileNav').hidden=true;">Dashboard</a>
  <a href="overview.php">Market Overview</a>
  <a href="quick_tools.php">Quick Tools</a>
  <a href="javascript:void(0)" onclick="bmShowSection('signals');document.getElementById('mobileNav').hidden=true;">Signals Grid</a>
  <a href="javascript:void(0)" onclick="bmShowSection('strength');document.getElementById('mobileNav').hidden=true;">Currency Strength</a>
  <a href="javascript:void(0)" onclick="bmShowSection('news');document.getElementById('mobileNav').hidden=true;">Economic Calendar</a>
  <a href="trading.php">BM Quantum Edge</a>
  <a href="classes.php">Classes</a>
  <a href="javascript:void(0)" onclick="bmShowSection('methodology');document.getElementById('mobileNav').hidden=true;">Methodology</a>
  <a href="javascript:void(0)" onclick="bmShowSection('signals');document.getElementById('mobileNav').hidden=true;">Copy Trading</a>
  <a href="bm_elites.php">BM Elites Trading Circle</a>
  <a href="crypto.php">&#8383; Crypto Trading</a>
  <a href="javascript:void(0)" id="mobileProfileLink" onclick="openProfileModal()">My Profile</a>
  <a href="logout.php">Logout</a>
</div>
<!-- ===== MARKET OVERVIEW VIEW ===== -->
<div id="view-market" class="bm-view">

<!-- Hero -->
<div class="welcome-bar" id="welcomeBar">Welcome back, <strong id="welcomeName">Trader</strong></div>

<!-- Crypto Trading nav card (links out to the dedicated crypto.php trading page) -->
<div class="crypto-widget" id="cryptoWidget">
  <a class="crypto-nav-card" href="crypto.php" aria-label="Open Crypto Trading">
    <div class="crypto-nav-card__info">
      <div class="crypto-nav-card__icon" aria-hidden="true">&#8383;</div>
      <div>
        <h2 class="crypto-nav-card__title">Crypto Trading</h2>
        <p class="crypto-nav-card__subtitle">Buy and sell digital assets quickly and securely.</p>
      </div>
    </div>
    <span class="crypto-nav-card__btn">Open Crypto Trading
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </span>
  </a>
</div>

<section class="hero">
  <div>
    <div class="eyebrow">BM Forex Hub &middot; Real ECB &amp; live spot data</div>
    <h1>Premium signals for the <span class="golden">global forex</span> market.</h1>
    <p class="lead">BM Forex Hub brings together live market data, essential signal levels, trading education, and a supportive community for traders.</p>
    <div class="btn-row">
      <button class="btn btn-primary" id="rescanBtn" aria-describedby="rescanHint">Re-scan markets</button>
      <span id="rescanHint" class="kbd-hint">Press <kbd>R</kbd></span>
      <a href="#methodology" class="btn btn-ghost">How it works</a>
      <a href="https://www.tiktok.com/@bm_forex?_r=1&amp;_t=ZS-97waauJBTBS" class="btn btn-ghost" target="_blank" rel="noopener noreferrer">Live Trading</a>
      <button class="btn btn-ghost" id="aboutBtn" type="button" aria-haspopup="dialog">About</button>
      <a href="subscribe.php?service=copy_trading" class="btn btn-ghost">Copy Trading</a>
      <a href="https://wa.me/254785618608" class="btn btn-ghost" target="_blank" rel="noopener noreferrer">Events</a>
      <a href="https://wa.me/254785618608" class="btn btn-ghost" target="_blank" rel="noopener noreferrer">Classes</a>
      <a href="subscribe.php" class="btn btn-ghost" style="color:var(--gold);border-color:rgba(22,119,255,0.45)">Subscribe</a>
    </div>
  </div>
  <?php
// Load active announcements
$sqliteFile = __DIR__ . '/storage/database.sqlite';
if (file_exists($sqliteFile)) {
    try {
        $pdo = new PDO('sqlite:' . $sqliteFile);
        $stmt = $pdo->prepare("SELECT * FROM market_overview_announcements WHERE status='published' AND (start_date IS NULL OR start_date <= datetime('now')) AND (expiry_date IS NULL OR expiry_date >= datetime('now')) ORDER BY priority DESC, created_at DESC");
        $stmt->execute();
        $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $announcements = [];
    }
} else {
    $announcements = [];
}
?>
<div class="market-overview">
  <div class="market-overview__header">
    <span class="market-overview__title">Market Overview</span>
    <span class="market-overview__live"><span class="dot" aria-hidden="true"></span> Live</span>
  </div>
  <?php if (!empty($announcements)): ?>
    <?php foreach ($announcements as $a): ?>
      <div class="announcement-card">
        <?php if (!empty($a['image'])): ?>
          <img src="<?=htmlspecialchars($a['image'])?>" alt="" class="announcement-card__image" loading="lazy" />
        <?php endif; ?>
        <div class="announcement-card__content">
          <h3 class="announcement-card__title"><?=htmlspecialchars($a['title'])?></h3>
          <?php if (!empty($a['subtitle'])): ?>
            <p class="announcement-card__subtitle"><?=htmlspecialchars($a['subtitle'])?></p>
          <?php endif; ?>
          <div class="announcement-card__body"><?= $a['body'] /* assumed sanitized HTML */ ?></div>
          <?php if (!empty($a['button_text']) && !empty($a['button_link'])): ?>
            <a href="<?=htmlspecialchars($a['button_link'])?>" class="announcement-card__btn"><?=htmlspecialchars($a['button_text'])?></a>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p class="announcement-card__empty">No announcements at this time.</p>
  <?php endif; ?>
  <div class="market-overview__grid" id="marketOverviewGrid"></div>
</div>
</section>

<!-- About modal -->
<div class="about-modal" id="aboutModal" role="dialog" aria-modal="true" aria-labelledby="aboutTitle" hidden>
  <div class="about-modal__panel">
    <button class="about-modal__close" id="aboutClose" type="button" aria-label="Close About">&times;</button>
    <h2 id="aboutTitle">About BM Forex Hub</h2>
    <p>Empowering Traders Through Knowledge, Precision, and Innovation</p>
    <p>BM Forex Hub is a modern forex education and market intelligence company committed to transforming the way individuals approach the global financial markets. We combine real-time market data, advanced signal generation, and world-class trading education to help both beginner and experienced traders achieve consistent, informed results.</p>
    <p>Our platform delivers live ECB-sourced FX signals, real-time gold spot analysis, a TradingView-powered chart suite, and a growing library of educational resources &mdash; all in one place. We believe every trader deserves access to professional-grade tools and a supportive community.</p>
    <p>Join thousands of traders across Africa and beyond who trust BM Forex Hub for daily market intelligence, copy trading opportunities, live events, and structured trading classes.</p>
  </div>
</div>

<!-- Live ticker tape -->
<div class="ticker-wrap">
  <div class="tv-source-tag mono">Live data &mdash; tradingview.com</div>
  <div class="tradingview-widget-container" id="tvTickerContainer"></div>
</div>

</div><!-- END #view-market -->

<!-- ===== SIGNALS VIEW ===== -->
<div id="view-signals" class="bm-view">

<?php include __DIR__ . '/components/premium_signals_hero.php'; ?>

<section class="block" id="signals">
  <div class="block-head">
    <div>
      <h2>Live Signal Grid</h2>
      <p>SMC-based setups (BOS/CHoCH, Order Blocks, Fair Value Gaps) with 1:2+ R:R. Setups stay active until TP or SL is hit. See <a href="#methodology" style="color:var(--gold)">methodology</a> for details.</p>
    </div>
  </div>
  <div id="dataStatus"></div>
  <div id="trialBanner" class="trial-banner" hidden></div>
  <div id="upgradeCta" class="upgrade-cta" hidden>
    <div class="upgrade-cta__inner">
      <div class="upgrade-cta__icon">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
      </div>
      <h2 class="upgrade-cta__title">Live Signal Grid Locked</h2>
      <p class="upgrade-cta__text">Your 3-day free trial has expired. Subscribe to continue accessing live trading signals.</p>
      <a href="subscribe.php" class="btn btn-primary upgrade-cta__btn">View Plans &amp; Subscribe</a>
    </div>
  </div>
  <div class="controls" id="signalControls">
    <div class="seg" id="filterSeg" role="tablist" aria-label="Filter signals">
      <button data-filter="all"     role="tab" aria-selected="true"  id="tab-all">All pairs</button>
      <button data-filter="setup"   role="tab" aria-selected="false" id="tab-setup">With Setup</button>
      <button data-filter="nosetup" role="tab" aria-selected="false" id="tab-nosetup">No Setup</button>
      <button data-filter="buy"     role="tab" aria-selected="false" id="tab-buy">Buy side</button>
      <button data-filter="sell"    role="tab" aria-selected="false" id="tab-sell">Sell side</button>
      <button data-filter="paused"  role="tab" aria-selected="false" id="tab-paused" class="weekend-tab" style="display:none">⏸ Weekend</button>
    </div>
    <div class="seg" id="assetSeg" role="tablist" aria-label="Filter by asset type">
      <button data-asset="all"      role="tab" aria-selected="true"  id="tab-a-all">All</button>
      <button data-asset="forex"    role="tab" aria-selected="false" id="tab-a-forex">Forex</button>
      <button data-asset="commodity" role="tab" aria-selected="false" id="tab-a-commodity">Metals</button>
      <button data-asset="crypto"   role="tab" aria-selected="false" id="tab-a-crypto">Crypto</button>
    </div>
    <div class="seg" id="sortSeg" role="group" aria-label="Sort signals">
      <button data-sort="confidence" aria-pressed="true">Sort: Confidence</button>
      <button data-sort="pair"       aria-pressed="false">Sort: Pair</button>
    </div>
    <div class="spacer"></div>
    <select id="timezoneSelect" aria-label="Select timezone" class="timezone-select" style="background:var(--panel);color:var(--ink);border:1px solid var(--hairline);border-radius:6px;font-size:0.75rem;padding:6px 10px;font-family:'IBM Plex Mono',monospace;"></select>
    <span class="last-updated mono" id="lastUpdated">
      <span class="dot" aria-hidden="true"></span>Last updated: &mdash;
    </span>
    <span class="rescan-status mono" id="rescanStatus" aria-live="polite">Loading live data&hellip;</span>
    <span class="rescan-status mono" style="color:#22C55E;margin-left:12px" id="priceAge" aria-live="polite">Connecting&hellip;</span>
  </div>
  <div class="grid" id="signalGrid" aria-live="polite"></div>
</section>

<!-- Signal Metrics (part of signals view) -->
<section class="block" id="spotlight">
  <div class="block-head">
    <div>
      <h2>Signal Metrics</h2>
      <p>Select any pair above to see its SMC setup details, market structure, and TradingView chart.</p>
    </div>
  </div>
  <div class="tv-chart-card">
    <div class="cap">
      <span>Live chart &mdash; <span id="tvChartLabel">FX:EURUSD</span></span>
      <div class="chart-tools mono" id="chartIntervalSeg" aria-label="Chart interval">
        <button data-interval="15"  type="button">15m</button>
        <button data-interval="60"  type="button" class="active">1H</button>
        <button data-interval="240" type="button">4H</button>
        <button data-interval="D"   type="button">1D</button>
      </div>
      <span class="tv-source-tag mono">tradingview.com</span>
    </div>
    <div class="tradingview-widget-container" id="tvChartContainer"></div>
  </div>
  <div class="spotlight">
    <div class="info">
      <h3 id="spotPair">EUR / USD</h3>
      <div class="sub mono" id="spotMeta">Analyzing market structure&hellip;</div>
      <div class="info-grid" id="spotSetupGrid">
        <div><span class="lbl">Status</span>             <span class="val" id="spotStatus"  style="color:var(--gold)">Scanning&hellip;</span></div>
        <div><span class="lbl">Direction</span>           <span class="val" id="spotDir"     style="color:var(--ink-dim)">&mdash;</span></div>
        <div><span class="lbl">Entry</span>               <span class="val" id="spotEntry"   style="color:var(--gold)">&mdash;</span></div>
        <div><span class="lbl">Stop loss</span>            <span class="val" id="spotSL"      style="color:var(--coral)">&mdash;</span></div>
        <div><span class="lbl">Target 1 (1:2)</span>       <span class="val" id="spotTP1"     style="color:var(--teal)">&mdash;</span></div>
        <div><span class="lbl">Target 2 (1:3)</span>       <span class="val" id="spotTP2"     style="color:var(--teal)">&mdash;</span></div>
        <div><span class="lbl">Risk : Reward</span>        <span class="val" id="spotRR">&mdash;</span></div>
        <div><span class="lbl">Zone Type</span>            <span class="val" id="spotZoneType">&mdash;</span></div>
        <div><span class="lbl">Trigger</span>              <span class="val" id="spotTrigger">&mdash;</span></div>
      </div>
      <div class="sub mono" id="spotStructure" style="margin-bottom:14px;">&mdash;</div>
      <div class="rationale" id="spotRationale">Select a pair above to see its SMC setup and market structure.</div>
    </div>
    <div id="ladderSvgWrap">
      <svg id="ladderSvg" viewBox="0 0 640 420" role="img" aria-label="Price levels for selected pair"></svg>
    </div>
  </div>
</section>

</div><!-- END #view-signals -->

<!-- ===== CURRENCY STRENGTH VIEW ===== -->
<div id="view-strength" class="bm-view">
<div class="sm-container" id="strengthMeter">

  <!-- Header: pair info + live status -->
  <div class="sm-header">
    <div class="sm-header__info">
      <div class="sm-pair-display">
        <select class="sm-pair-select" id="smPairSelect">
          <option value="EURUSD">EUR/USD</option>
          <option value="GBPUSD">GBP/USD</option>
          <option value="USDJPY">USD/JPY</option>
          <option value="AUDUSD">AUD/USD</option>
          <option value="USDCAD">USD/CAD</option>
          <option value="USDCHF">USD/CHF</option>
          <option value="NZDUSD">NZD/USD</option>
          <option value="XAUUSD">XAU/USD</option>
          <option value="BTCUSD">BTC/USD</option>
        </select>
        <div class="sm-pair-desc" id="smPairDesc">Euro vs US Dollar</div>
      </div>
    </div>
    <div class="sm-header__status">
      <div class="sm-live-badge" id="smLiveBadge">
        <span class="sm-live-badge__dot"></span>
        <span>LIVE</span>
      </div>
      <div class="sm-updated" id="smUpdated">Updated --</div>
    </div>
  </div>

  <!-- Controls: timeframes -->
  <div class="sm-controls">
    <div class="sm-timeframes" id="smTimeframes">
      <button class="sm-tf-btn" data-tf="1M">1M</button>
      <button class="sm-tf-btn" data-tf="5M">5M</button>
      <button class="sm-tf-btn" data-tf="15M">15M</button>
      <button class="sm-tf-btn" data-tf="30M">30M</button>
      <button class="sm-tf-btn active" data-tf="1H">1H</button>
      <button class="sm-tf-btn" data-tf="4H">4H</button>
      <button class="sm-tf-btn" data-tf="1D">1D</button>
      <button class="sm-tf-btn" data-tf="1W">1W</button>
    </div>
  </div>

  <!-- Main: gauge + ranking side-by-side -->
  <div class="sm-main">
    <div class="sm-main__left">
      <!-- Market Score circle + Gauge + Signal -->
      <div class="sm-score-circle" id="smScoreCircle">
        <span class="sm-score-circle__label">Market Score</span>
        <span class="sm-score-circle__value" id="smScoreValue">0</span>
        <span class="sm-score-circle__sentiment" id="smScoreSentiment">Neutral</span>
      </div>

      <div class="sm-gauge-section">
        <div class="sm-gauge-glow"></div>
        <div class="sm-gauge-wrap" id="smGaugeWrap">
          <div id="smGaugeContainer"></div>
        </div>
      </div>

      <div class="sm-signal-block">
        <div class="sm-signal-block__label" id="smSignalLabel">SELL</div>
        <div class="sm-signal-block__confidence" id="smConfidenceBlock">
          <div class="sm-confidence-bar">
            <div class="sm-confidence-bar__fill" id="smConfidenceFill" style="width:0%"></div>
          </div>
          <span class="sm-confidence-pct" id="smConfidencePct">0%</span> Confidence
        </div>
      </div>
    </div>

    <div class="sm-main__right">
      <div class="sm-glass-panel sm-ranking-panel">
        <div class="sm-panel__title">Currency Strength</div>
        <div id="smRankingList" class="sm-ranking-list"><div class="sm-ranking-loading">Loading&hellip;</div></div>
      </div>
      <div class="sm-glass-panel sm-heatmap-panel">
        <div class="sm-panel__title">Market Heatmap</div>
        <div id="smHeatmapGrid" class="sm-heatmap-grid"><div class="sm-ranking-loading">Loading&hellip;</div></div>
      </div>
    </div>
  </div>

  <!-- Market stats row with glass cards -->
  <div class="sm-stats" id="smStats">
    <div class="sm-glass-card sm-stat-card"><div class="sm-stat-card__content"><div class="sm-stat-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div><div><span class="sm-stat-card__label">Trend</span><div class="sm-stat-card__val" id="smStatTrend">--</div></div></div><div class="sm-stat-card__bar"><div class="sm-stat-card__fill" id="smStatTrendFill" style="width:0%"></div></div></div>
    <div class="sm-glass-card sm-stat-card"><div class="sm-stat-card__content"><div class="sm-stat-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg></div><div><span class="sm-stat-card__label">Momentum</span><div class="sm-stat-card__val" id="smStatMomentum">--</div></div></div><div class="sm-stat-card__bar"><div class="sm-stat-card__fill" id="smStatMomFill" style="width:0%"></div></div></div>
    <div class="sm-glass-card sm-stat-card"><div class="sm-stat-card__content"><div class="sm-stat-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg></div><div><span class="sm-stat-card__label">RSI</span><div class="sm-stat-card__val" id="smStatRSI">--</div></div></div><div class="sm-stat-card__bar"><div class="sm-stat-card__fill" id="smStatRsiFill" style="width:0%"></div></div></div>
    <div class="sm-glass-card sm-stat-card"><div class="sm-stat-card__content"><div class="sm-stat-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div><div><span class="sm-stat-card__label">MACD</span><div class="sm-stat-card__val" id="smStatMACD">--</div></div></div><div class="sm-stat-card__bar"><div class="sm-stat-card__fill" id="smStatMacdFill" style="width:0%"></div></div></div>
    <div class="sm-glass-card sm-stat-card"><div class="sm-stat-card__content"><div class="sm-stat-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div><div><span class="sm-stat-card__label">ADX</span><div class="sm-stat-card__val" id="smStatADX">--</div></div></div><div class="sm-stat-card__bar"><div class="sm-stat-card__fill" id="smStatAdxFill" style="width:0%"></div></div></div>
    <div class="sm-glass-card sm-stat-card"><div class="sm-stat-card__content"><div class="sm-stat-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></div><div><span class="sm-stat-card__label">ATR</span><div class="sm-stat-card__val" id="smStatATR">--</div></div></div><div class="sm-stat-card__bar"><div class="sm-stat-card__fill" id="smStatAtrFill" style="width:0%"></div></div></div>
  </div>

  <!-- Signal breakdown with glass cards -->
  <div class="sm-glasses" id="smBreakdown">
    <div class="sm-glass-panel">
      <div class="sm-panel__title">Moving Averages</div>
      <div class="sm-indicator-grid sm-indicator-grid--ma">
        <div class="sm-indicator-card" data-name="ema20"><div class="sm-indicator-card__head"><span class="sm-indicator-card__name">EMA 20</span><span class="sm-indicator-card__pill" id="smPillEma20">--</span></div><span class="sm-indicator-card__val" id="smIndEma20">--</span></div>
        <div class="sm-indicator-card" data-name="ema50"><div class="sm-indicator-card__head"><span class="sm-indicator-card__name">EMA 50</span><span class="sm-indicator-card__pill" id="smPillEma50">--</span></div><span class="sm-indicator-card__val" id="smIndEma50">--</span></div>
        <div class="sm-indicator-card" data-name="ema100"><div class="sm-indicator-card__head"><span class="sm-indicator-card__name">EMA 100</span><span class="sm-indicator-card__pill" id="smPillEma100">--</span></div><span class="sm-indicator-card__val" id="smIndEma100">--</span></div>
        <div class="sm-indicator-card" data-name="ema200"><div class="sm-indicator-card__head"><span class="sm-indicator-card__name">EMA 200</span><span class="sm-indicator-card__pill" id="smPillEma200">--</span></div><span class="sm-indicator-card__val" id="smIndEma200">--</span></div>
      </div>
    </div>
    <div class="sm-glass-panel">
      <div class="sm-panel__title">Oscillators</div>
      <div class="sm-indicator-grid sm-indicator-grid--osc">
        <div class="sm-indicator-card" data-name="rsi"><div class="sm-indicator-card__head"><span class="sm-indicator-card__name">RSI</span><span class="sm-indicator-card__pill" id="smPillRsi">--</span></div><span class="sm-indicator-card__val" id="smIndRsi">--</span></div>
        <div class="sm-indicator-card" data-name="stoch"><div class="sm-indicator-card__head"><span class="sm-indicator-card__name">Stochastic</span><span class="sm-indicator-card__pill" id="smPillStoch">--</span></div><span class="sm-indicator-card__val" id="smIndStoch">--</span></div>
        <div class="sm-indicator-card" data-name="cci"><div class="sm-indicator-card__head"><span class="sm-indicator-card__name">CCI</span><span class="sm-indicator-card__pill" id="smPillCci">--</span></div><span class="sm-indicator-card__val" id="smIndCci">--</span></div>
        <div class="sm-indicator-card" data-name="williams"><div class="sm-indicator-card__head"><span class="sm-indicator-card__name">Williams %R</span><span class="sm-indicator-card__pill" id="smPillWilliams">--</span></div><span class="sm-indicator-card__val" id="smIndWilliams">--</span></div>
        <div class="sm-indicator-card" data-name="macd"><div class="sm-indicator-card__head"><span class="sm-indicator-card__name">MACD</span><span class="sm-indicator-card__pill" id="smPillMacd">--</span></div><span class="sm-indicator-card__val" id="smIndMacd">--</span></div>
      </div>
    </div>
  </div>

  <!-- Loading skeleton -->
  <div class="sm-skeleton" id="smSkeleton">
    <div class="sm-skeleton__gauge"></div>
    <div class="sm-skeleton__row"><div class="sm-skeleton__card"></div><div class="sm-skeleton__card"></div><div class="sm-skeleton__card"></div></div>
    <div class="sm-skeleton__row"><div class="sm-skeleton__card"></div><div class="sm-skeleton__card"></div><div class="sm-skeleton__card"></div></div>
  </div>



  <!-- Footer -->
  <div class="sm-footer">
    <span class="sm-footer__updated"><span id="smLastUpdated">--</span></span>
    <span class="sm-footer__brand">Powered by BMForexHub AI &middot; Live every 30s</span>
  </div>

</div>
</div><!-- END #view-strength -->

<!-- ===== ECONOMIC CALENDAR VIEW ===== -->
<div id="view-news" class="bm-view">
<section class="block" id="news">
  <div class="block-head">
    <div>
      <h2>Forex Factory Economic Calendar</h2>
      <p>High-impact economic events sourced from Forex Factory. Refreshes each page load.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <button class="broker-tab active" data-impact="all">All</button>
      <button class="broker-tab" data-impact="High">High Impact</button>
      <button class="broker-tab" data-impact="Medium">Medium</button>
    </div>
  </div>
  <div class="news-list" id="newsList">
    <div class="news-item">
      <span class="time mono">Loading&hellip;</span>
      <span class="title">Fetching Forex Factory calendar&hellip;</span>
      <span class="impact medium">Live</span>
      <span class="countdown">&mdash;</span>
    </div>
  </div>
  <div class="news-source-note">Data source: forexfactory.com &middot; Updated each page load</div>
</section>

<!-- ===== BROKER DIRECTORY ===== -->
<section class="block" id="brokers">
  <div class="block-head">
    <div>
      <h2>Broker Directory</h2>
      <p>BM Forex Hub&rsquo;s official partner broker. Open an account through the link below to support the community and unlock referral perks.</p>
    </div>
  </div>
  <div class="broker-grid broker-grid--single">
    <div class="broker-card">
      <div class="broker-card-top">
        <div class="broker-logo-wrap">PU</div>
        <div>
          <div class="broker-name">PU</div>
          <div class="broker-type">Official Partner Broker</div>
        </div>
      </div>
      <div class="broker-cta">
        <a href="https://puvip.co/la-partners/z8qHXq3H" target="_blank" rel="noopener noreferrer">Create Account</a>
      </div>
    </div>
  </div>
</section>

<footer>
  <div class="disclaimer">
    <strong>This is an educational tool, not financial advice.</strong> The ticker tape and chart are genuine real-time <strong>TradingView</strong> embedded widgets. Signal levels on each card are computed from <strong>real market data</strong>: ECB daily reference rates (FX majors via the Frankfurter API) and a live gold spot feed (gold-api.com). Do not use this output to place real trades. Forex, gold, and CFD trading is highly leveraged and carries a high risk of loss. Past performance does not indicate future results. Always consult a licensed financial advisor and use a regulated broker&rsquo;s live data and execution before trading with real capital.
  </div>
  <div class="foot-row">
    <span>BM Forex Hub &bull; VARBAN COMPANY LIMITED &bull; Registered under Licence Number: PVT-PJUY6LJX &mdash;</span>
    <span id="footClock">&mdash;</span>
    <span id="utcClock" style="color:var(--ink-dim);">&mdash;</span>
  </div>
</footer>

</div><!-- END #view-news -->

<!-- ===== METHODOLOGY VIEW ===== -->
<div id="view-methodology" class="bm-view">
<section class="block" id="methodology">
  <div class="block-head">
    <div>
      <h2>Methodology</h2>
      <p>Exactly what&rsquo;s real, what it&rsquo;s sourced from, and the limits of a keyless, client-side data feed.</p>
    </div>
  </div>
  <div class="method-grid">
    <!-- Card 01: Break of Structure (BOS) -->
    <div class="method-card">
      <div class="num">01 / Structure</div>
      <h4>Break of Structure (BOS)</h4>
      <div class="method-chart-box">
        <svg class="method-chart-svg" viewBox="0 0 380 150" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Technical chart illustration showing Break of Structure (BOS) and Change of Character (CHoCH)">
          <!-- Grid lines -->
          <line x1="20" y1="30" x2="360" y2="30" stroke="#1A2433" stroke-dasharray="2,2"/>
          <line x1="20" y1="75" x2="360" y2="75" stroke="#1A2433" stroke-dasharray="2,2"/>
          <line x1="20" y1="120" x2="360" y2="120" stroke="#1A2433" stroke-dasharray="2,2"/>
          
          <!-- Swing High Line -->
          <line x1="40" y1="50" x2="180" y2="50" class="chart-line-bos"/>
          <text x="45" y="44" class="chart-label chart-label-accent">SWING HIGH</text>
          
          <!-- Swing Low Line -->
          <line x1="90" y1="110" x2="280" y2="110" class="chart-line-choch"/>
          <text x="95" y="122" class="chart-label" style="fill:#F0B429">SWING LOW</text>

          <!-- Candlestick Series 1: Bullish Trend + BOS -->
          <!-- Candle 1 (Green) -->
          <line x1="30" y1="90" x2="30" y2="115" stroke="#16C784" stroke-width="1.5"/>
          <rect x="26" y="95" width="8" height="15" fill="#16C784" rx="1"/>
          <!-- Candle 2 (Green) -->
          <line x1="50" y1="40" x2="50" y2="90" stroke="#16C784" stroke-width="1.5"/>
          <rect x="46" y="50" width="8" height="30" fill="#16C784" rx="1"/>
          <!-- Candle 3 (Red Swing High) -->
          <line x1="70" y1="42" x2="70" y2="85" stroke="#F6465D" stroke-width="1.5"/>
          <rect x="66" y="48" width="8" height="25" fill="#F6465D" rx="1"/>
          <!-- Candle 4 (Green Higher High - BOS Break) -->
          <line x1="110" y1="20" x2="110" y2="80" stroke="#16C784" stroke-width="1.5"/>
          <rect x="106" y="25" width="8" height="45" fill="#16C784" rx="1"/>
          
          <!-- BOS Annotation Arrow & Label -->
          <path d="M 106 35 L 140 22" stroke="#1677FF" stroke-width="2" marker-end="url(#arrowBlue)"/>
          <rect x="135" y="12" width="54" height="18" rx="4" fill="rgba(22,119,255,0.2)" stroke="#1677FF"/>
          <text x="142" y="24" class="chart-label chart-label-accent">BOS ↑</text>

          <!-- Candlestick Series 2: CHoCH Reversal -->
          <!-- Candle 5 (Red Reversal Break) -->
          <line x1="190" y1="50" x2="190" y2="125" stroke="#F6465D" stroke-width="1.5"/>
          <rect x="186" y="60" width="8" height="55" fill="#F6465D" rx="1"/>
          <!-- Candle 6 (Red CHoCH Breakout) -->
          <line x1="220" y1="95" x2="220" y2="135" stroke="#F6465D" stroke-width="1.5"/>
          <rect x="216" y="105" width="8" height="25" fill="#F6465D" rx="1"/>

          <!-- CHoCH Annotation Arrow & Label -->
          <path d="M 216 118 L 245 130" stroke="#F6465D" stroke-width="2"/>
          <rect x="245" y="120" width="62" height="18" rx="4" fill="rgba(246,70,93,0.2)" stroke="#F6465D"/>
          <text x="252" y="132" class="chart-label chart-label-red">CHoCH ↓</text>
        </svg>
      </div>
      <p>The engine identifies swing highs and lows on the Daily timeframe. When price breaks a previous swing high (bullish) or swing low (bearish), it marks a Break of Structure. A Change of Character (CHoCH) occurs when price first breaks against the prevailing trend, signaling a potential reversal.</p>
    </div>

    <!-- Card 02: Order Blocks & FVGs -->
    <div class="method-card">
      <div class="num">02 / Entry Zones</div>
      <h4>Order Blocks &amp; FVGs</h4>
      <div class="method-chart-box">
        <svg class="method-chart-svg" viewBox="0 0 380 150" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Technical chart illustration showing Order Block and Fair Value Gap entry zones">
          <!-- Order Block Zone -->
          <rect x="30" y="80" width="310" height="28" fill="rgba(22,119,255,0.18)" stroke="#1677FF" stroke-dasharray="3,3" rx="4"/>
          <text x="36" y="97" class="chart-label chart-label-accent">ORDER BLOCK (OB)</text>

          <!-- Fair Value Gap (FVG) Zone -->
          <rect x="110" y="45" width="230" height="24" fill="rgba(240,180,41,0.18)" stroke="#F0B429" stroke-dasharray="3,3" rx="4"/>
          <text x="116" y="61" class="chart-label" style="fill:#F0B429">FAIR VALUE GAP (FVG)</text>

          <!-- Impulse Candles -->
          <!-- Last Opposing Red Candle (OB Candle) -->
          <line x1="45" y1="75" x2="45" y2="112" stroke="#F6465D" stroke-width="1.5"/>
          <rect x="41" y="82" width="8" height="24" fill="#F6465D" rx="1"/>

          <!-- Massive Green Expansion Candle (Creates FVG + BOS) -->
          <line x1="75" y1="30" x2="75" y2="105" stroke="#16C784" stroke-width="1.5"/>
          <rect x="71" y="35" width="8" height="60" fill="#16C784" rx="1"/>

          <!-- Continuation Green Candle -->
          <line x1="105" y1="20" x2="105" y2="50" stroke="#16C784" stroke-width="1.5"/>
          <rect x="101" y="24" width="8" height="20" fill="#16C784" rx="1"/>

          <!-- Retest Curve / Price Return -->
          <path d="M 120 30 Q 180 30, 230 85" stroke="#8FA3B8" stroke-width="1.5" stroke-dasharray="3,3" fill="none"/>
          <circle cx="230" cy="85" r="4" fill="#1677FF"/>
          
          <!-- Entry Reaction Candle (Bullish Bounce) -->
          <line x1="230" y1="50" x2="230" y2="92" stroke="#16C784" stroke-width="2"/>
          <rect x="226" y="55" width="8" height="32" fill="#16C784" rx="1"/>
          
          <rect x="245" y="76" width="95" height="18" rx="4" fill="rgba(22,199,132,0.2)" stroke="#16C784"/>
          <text x="252" y="88" class="chart-label chart-label-green">ENTRY REACTION ↑</text>
        </svg>
      </div>
      <p>After a BOS, the engine locates the responsible Order Block (last opposing candle before the break) and any Fair Value Gaps (three-candle imbalances). These become high-probability entry zones where institutional orders are likely clustered.</p>
    </div>

    <!-- Card 03: Risk Management (1:2 to 1:3 R:R Minimum) -->
    <div class="method-card">
      <div class="num">03 / Risk Management</div>
      <h4>1:2 to 1:3 R:R minimum</h4>
      <div class="method-chart-box">
        <svg class="method-chart-svg" viewBox="0 0 380 150" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Technical chart illustration showing Risk Reward R:R ratio targets and stop loss buffer">
          <!-- TP 2 Line (1:3) -->
          <line x1="30" y1="20" x2="350" y2="20" stroke="#16C784" stroke-width="1.5"/>
          <text x="35" y="15" class="chart-label chart-label-green">TP 2 — 1:3 TARGET (+3R)</text>

          <!-- TP 1 Line (1:2) -->
          <line x1="30" y1="50" x2="350" y2="50" stroke="#16C784" stroke-width="1.5" stroke-dasharray="4,2"/>
          <text x="35" y="45" class="chart-label chart-label-green">TP 1 — 1:2 MINIMUM (+2R)</text>

          <!-- ENTRY Line -->
          <line x1="30" y1="100" x2="350" y2="100" stroke="#1677FF" stroke-width="2"/>
          <circle cx="160" cy="100" r="5" fill="#1677FF"/>
          <text x="35" y="95" class="chart-label chart-label-accent">ENTRY ZONE</text>

          <!-- STOP LOSS Line & Buffer Zone -->
          <rect x="30" y="125" width="320" height="15" fill="rgba(246,70,93,0.15)" rx="2"/>
          <line x1="30" y1="125" x2="350" y2="125" stroke="#F6465D" stroke-width="1.5"/>
          <text x="35" y="121" class="chart-label chart-label-red">STOP LOSS (-1R RISK)</text>
          <text x="250" y="136" class="chart-label" style="font-size:7.5px;fill:#F6465D">OB BUFFER</text>

          <!-- R:R Ratio Bracket Indicator -->
          <line x1="330" y1="20" x2="330" y2="100" stroke="#16C784" stroke-width="2"/>
          <line x1="330" y1="100" x2="330" y2="125" stroke="#F6465D" stroke-width="2"/>
          <rect x="290" y="55" width="36" height="16" rx="4" fill="#101722" stroke="#16C784"/>
          <text x="295" y="66" class="chart-label chart-label-green">3.0x</text>
        </svg>
      </div>
      <p>Every setup must meet a minimum 1:2 Risk:Reward ratio. Entry is at the zone boundary, stop loss is placed beyond the Order Block with a buffer, and targets are calculated at 2x and 3x the risk distance. No setup is shown if R:R requirements aren't met.</p>
    </div>

    <!-- Card 04: Setup Lifecycle (Active until TP or SL) -->
    <div class="method-card">
      <div class="num">04 / Setup Lifecycle</div>
      <h4>Active until TP or SL</h4>
      <div class="method-chart-box">
        <svg class="method-chart-svg" viewBox="0 0 380 150" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Technical chart illustration showing fixed trade setup lifecycle from entry to TP or SL">
          <!-- Fixed TP Level (No Moving Goalposts) -->
          <line x1="30" y1="25" x2="350" y2="25" stroke="#16C784" stroke-width="1.5" stroke-dasharray="3,3"/>
          <text x="35" y="20" class="chart-label chart-label-green">FIXED TAKE PROFIT (TP)</text>

          <!-- Fixed Entry Level -->
          <line x1="30" y1="75" x2="350" y2="75" stroke="#1677FF" stroke-width="1.5"/>
          <text x="35" y="70" class="chart-label chart-label-accent">FIXED ENTRY LEVEL</text>

          <!-- Fixed SL Level -->
          <line x1="30" y1="125" x2="350" y2="125" stroke="#F6465D" stroke-width="1.5" stroke-dasharray="3,3"/>
          <text x="35" y="137" class="chart-label chart-label-red">FIXED STOP LOSS (SL)</text>

          <!-- Lifecycle Path 1: TP Hit (Primary Scenario) -->
          <path d="M 60 75 Q 100 90, 120 75 T 180 30 T 220 25" stroke="#16C784" stroke-width="2" fill="none"/>
          <circle cx="220" cy="25" r="4" fill="#16C784"/>
          <rect x="228" y="16" width="56" height="18" rx="4" fill="rgba(22,199,132,0.2)" stroke="#16C784"/>
          <text x="234" y="28" class="chart-label chart-label-green">TP HIT ✓</text>

          <!-- Status Chips -->
          <rect x="65" y="82" width="52" height="16" rx="4" fill="rgba(22,119,255,0.2)" stroke="#1677FF"/>
          <text x="72" y="93" class="chart-label chart-label-accent">ACTIVE</text>
        </svg>
      </div>
      <p>Once a setup is identified, it remains active until either Take Profit or Stop Loss is hit. The setup does not change mid-life — no modified entries, no shifted stops. When no setup exists, the grid shows market structure and momentum instead.</p>
    </div>

    <!-- Card 05: Multi-Timeframe Confluence (Daily + 4H + 1H) -->
    <div class="method-card">
      <div class="num">05 / Multi-Timeframe</div>
      <h4>Daily + 4H + 1H confluence</h4>
      <div class="method-chart-box">
        <svg class="method-chart-svg" viewBox="0 0 380 150" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Technical chart illustration showing Daily, 4H, and 1H multi-timeframe confluence">
          <!-- Panel 1: DAILY -->
          <rect x="15" y="15" width="105" height="120" rx="8" fill="rgba(22,119,255,0.06)" stroke="#283548"/>
          <text x="25" y="32" class="chart-label chart-label-accent">DAILY (1D)</text>
          <!-- Trend Arrow & BOS -->
          <path d="M 28 110 L 60 70 L 85 90 L 105 45" stroke="#16C784" stroke-width="2" fill="none"/>
          <text x="25" y="124" class="chart-label" style="font-size:7.5px;fill:#16C784">BULLISH BIAS</text>

          <!-- Connecting Arrow 1 -->
          <path d="M 124 75 L 140 75" stroke="#1677FF" stroke-width="1.5" marker-end="url(#arrowBlue)"/>

          <!-- Panel 2: 4H -->
          <rect x="145" y="15" width="105" height="120" rx="8" fill="rgba(240,180,41,0.06)" stroke="#283548"/>
          <text x="155" y="32" class="chart-label" style="fill:#F0B429">4-HOUR (4H)</text>
          <!-- OB + FVG Zone -->
          <rect x="155" y="70" width="85" height="20" fill="rgba(22,119,255,0.2)" stroke="#1677FF" stroke-dasharray="2,2"/>
          <path d="M 155 45 L 175 75 L 195 55 L 210 75" stroke="#F6465D" stroke-width="1.5" fill="none"/>
          <text x="155" y="124" class="chart-label" style="font-size:7.5px;fill:#F0B429">OB + FVG ZONE</text>

          <!-- Connecting Arrow 2 -->
          <path d="M 254 75 L 270 75" stroke="#1677FF" stroke-width="1.5" marker-end="url(#arrowBlue)"/>

          <!-- Panel 3: 1H -->
          <rect x="275" y="15" width="90" height="120" rx="8" fill="rgba(22,199,132,0.06)" stroke="#16C784"/>
          <text x="283" y="32" class="chart-label chart-label-green">1-HOUR (1H)</text>
          <!-- Refined Entry Trigger -->
          <circle cx="320" cy="80" r="5" fill="#16C784"/>
          <line x1="285" y1="80" x2="355" y2="80" stroke="#16C784" stroke-width="1.5" stroke-dasharray="2,2"/>
          <text x="283" y="124" class="chart-label chart-label-green">REFINED ENTRY</text>
        </svg>
      </div>
      <p>Daily timeframe establishes the directional bias via BOS/CHoCH. The 4H timeframe identifies Order Blocks and FVGs for precise entry zones. The 1H timeframe provides confirmation and fine-tunes the entry precision.</p>
    </div>

    <!-- Card 06: Live Data (Real Prices, Real Analysis) -->
    <div class="method-card">
      <div class="num">06 / Live Data</div>
      <h4>Real prices, real analysis</h4>
      <div class="method-chart-box">
        <svg class="method-chart-svg" viewBox="0 0 380 150" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Technical chart illustration showing live market price feed analysis">
          <!-- Header Bar -->
          <text x="25" y="24" class="chart-label" style="font-size:11px;fill:#FFF;font-weight:700">XAUUSD</text>
          <text x="82" y="24" class="chart-label" style="font-size:9px;fill:#8FA3B8">GOLD SPOT · 1H</text>
          
          <rect x="290" y="12" width="65" height="16" rx="8" fill="rgba(22,199,132,0.15)" stroke="rgba(22,199,132,0.4)"/>
          <circle cx="300" cy="20" r="3" fill="#16C784"/>
          <text x="308" y="23" class="chart-label chart-label-green" style="font-size:8px">LIVE</text>

          <!-- Price Grid & Axis -->
          <line x1="25" y1="45" x2="310" y2="45" stroke="#1A2433" stroke-dasharray="2,2"/>
          <line x1="25" y1="80" x2="310" y2="80" stroke="#1A2433" stroke-dasharray="2,2"/>
          <line x1="25" y1="115" x2="310" y2="115" stroke="#1A2433" stroke-dasharray="2,2"/>
          
          <!-- Price Labels Right Scale -->
          <text x="320" y="48" class="chart-label" style="font-size:8px">2652.40</text>
          <text x="320" y="83" class="chart-label" style="font-size:8px">2645.10</text>
          <text x="320" y="118" class="chart-label" style="font-size:8px">2638.00</text>

          <!-- Real Candlestick Stream -->
          <!-- C1 Green -->
          <line x1="45" y1="90" x2="45" y2="120" stroke="#16C784" stroke-width="1.5"/>
          <rect x="41" y="95" width="8" height="20" fill="#16C784" rx="1"/>
          <!-- C2 Red -->
          <line x1="75" y1="85" x2="75" y2="110" stroke="#F6465D" stroke-width="1.5"/>
          <rect x="71" y="90" width="8" height="14" fill="#F6465D" rx="1"/>
          <!-- C3 Green -->
          <line x1="105" y1="65" x2="105" y2="98" stroke="#16C784" stroke-width="1.5"/>
          <rect x="101" y="70" width="8" height="22" fill="#16C784" rx="1"/>
          <!-- C4 Green -->
          <line x1="135" y1="40" x2="135" y2="78" stroke="#16C784" stroke-width="1.5"/>
          <rect x="131" y="45" width="8" height="28" fill="#16C784" rx="1"/>
          <!-- C5 Red -->
          <line x1="165" y1="48" x2="165" y2="72" stroke="#F6465D" stroke-width="1.5"/>
          <rect x="161" y="52" width="8" height="15" fill="#F6465D" rx="1"/>
          <!-- C6 Live Pulse Green -->
          <line x1="195" y1="35" x2="195" y2="60" stroke="#16C784" stroke-width="2"/>
          <rect x="191" y="38" width="8" height="18" fill="#16C784" rx="1"/>
          <circle cx="195" cy="38" r="3" fill="#16C784"/>

          <!-- Current Live Quote Banner -->
          <rect x="220" y="30" x2="285" y2="46" rx="4" fill="#1677FF"/>
          <text x="225" y="42" class="chart-label" style="fill:#FFF;font-weight:700">2650.15 $</text>
        </svg>
      </div>
      <p>All prices come from live market feeds via yfinance and gold-api.com. The TradingView widgets provide real-time charting. Signal structure analysis uses actual OHLC data — not simulated or random levels. When no setup exists, the card honestly says so.</p>
    </div>
  </div>
  <table class="tf-table">
    <thead>
      <tr>
        <th>Instrument class</th>
        <th>Real data source</th>
        <th>Update cadence</th>
        <th>Signal validity window</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>FX majors (EUR/USD, GBP/USD&hellip;)</td>
        <td>yfinance OHLC (Daily + 4H + 1H)</td>
        <td>Every 3 minutes</td>
        <td>Active until TP or SL hit</td>
      </tr>
      <tr>
        <td>Gold (XAU/USD)</td>
        <td>gold-api.com</td>
        <td>Every 3 minutes</td>
        <td>Active until TP or SL hit</td>
      </tr>
      <tr>
        <td>Economic calendar</td>
        <td>Forex Factory (nfs.faireconomy.media)</td>
        <td>Weekly JSON, updated each page load</td>
        <td>Current week&rsquo;s events</td>
      </tr>
    </tbody>
  </table>
</section>
</div><!-- END #view-methodology -->

<!-- Profile Modal -->
<div class="profile-modal" id="profileModal" role="dialog" aria-modal="true" aria-label="Edit Profile">
  <div class="profile-modal-inner">
    <button class="profile-close" id="profileModalClose">&times;</button>
    <h3 style="font-family:'Space Grotesk',sans-serif;font-size:1.1rem;margin-bottom:20px;color:var(--gold)">My Profile</h3>
    <div id="profileAlert" class="profile-alert" hidden></div>
    <form id="profileForm">
      <div class="profile-row">
        <div class="profile-field">
          <label>First Name</label>
          <input type="text" id="profFirstName" required>
        </div>
        <div class="profile-field">
          <label>Last Name</label>
          <input type="text" id="profLastName" required>
        </div>
      </div>
      <div class="profile-field">
        <label>Email</label>
        <input type="email" id="profEmail" disabled style="opacity:.5;cursor:not-allowed">
      </div>
      <div class="profile-row">
        <div class="profile-field">
          <label>Country</label>
          <select id="profCountry"></select>
        </div>
        <div class="profile-field">
          <label>Phone Number</label>
          <input type="tel" id="profPhone" placeholder="712345678">
        </div>
      </div>
      <div class="profile-field">
        <label>Username</label>
        <input type="text" id="profUsername" disabled style="opacity:.5;cursor:not-allowed">
      </div>
      <button type="submit" class="profile-save" id="profileSaveBtn">Save Changes</button>
    </form>
  </div>
</div>

<!-- Floating WhatsApp button -->
<a class="whatsapp-float" href="https://wa.me/254785618608"
   target="_blank" rel="noopener noreferrer"
   aria-label="Chat with BM Forex Hub on WhatsApp">
  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
  </svg>
  <span class="whatsapp-float-tooltip">Fast Support</span>
</a>

<!-- =====================================================================
     BM Forex Hub — Supabase auth guard + client-side signal engine
     ===================================================================== -->
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
<script src="js/auth.js?v=<?= filemtime('js/auth.js') ?>"></script>
<script>
/* ---------- auth guard ---------- */
(async function() {
  const { user, session } = await BMAuth.getSession();
  if (!user) { window.location.href = 'login.php'; return; }

  let authData = await BMAuth.getMembershipStatus(user, session);
  window._accessLevel = authData.level;
  window._authPlans = authData.plans;

  // Feature entitlement for Signal Grid
  let hasGrid = authData.plans.includes('all') || authData.plans.some(function(p) { return p.startsWith('grid_'); });
  if (window._accessLevel === 'full' && !hasGrid) {
      window._accessLevel = 'limited';
  }

  if (user && user.app_metadata && user.app_metadata.provider === 'google') {
    await BMAuth.syncGoogleProfile(user);
  }

  const userDisplayName = BMAuth.displayName(user);
  const displayUserEl = document.getElementById('displayUsername');
  const welcomeNameEl = document.getElementById('welcomeName');
  const dashWelcomeNameEl = document.getElementById('dashWelcomeName');
  if (displayUserEl) displayUserEl.textContent = userDisplayName;
  if (welcomeNameEl) welcomeNameEl.textContent = userDisplayName;
  if (dashWelcomeNameEl) dashWelcomeNameEl.textContent = userDisplayName;

  window._authUser = user;
  window._authReady = true;
  window.dispatchEvent(new Event('auth-ready'));

  applyAccessLevel();
})();

/* Client-side trial check as fallback when PHP API is unreachable */
async function clientSideTrialCheck(user, token) {
  try {
    const sb = BMAuth.getClient();
    const now = new Date().toISOString();
    const { data: subs, error: subErr } = await sb.from('subscriptions')
      .select('id,status,expires_at')
      .eq('user_id', user.id)
      .eq('status', 'active')
      .gt('expires_at', now)
      .order('expires_at', { ascending: false });
    if (!subErr && subs && subs.length > 0) return 'full';

    const { data: prof, error: profErr } = await sb.from('profiles')
      .select('trial_started_at')
      .eq('id', user.id)
      .single();
    if (profErr) return 'limited';

    let trialStarted = prof ? prof.trial_started_at : null;
    if (!trialStarted) {
      const nowISO = new Date().toISOString();
      const { error: updateErr } = await sb.from('profiles')
        .update({ trial_started_at: nowISO })
        .eq('id', user.id);
      if (updateErr) return 'limited';
      trialStarted = nowISO;
    }

    const trialEnd = new Date(trialStarted).getTime() + (3 * 86400000);
    const nowTs = Date.now();
    if (nowTs < trialEnd) {
      var rem = trialEnd - nowTs;
      window._trialDaysLeft = Math.max(0, Math.floor(rem / 86400000));
      window._trialHoursLeft = Math.max(0, Math.floor((rem % 86400000) / 3600000));
      window._trialEndTs = trialEnd;
      return 'trial';
    }
    return 'limited';
  } catch(e) {
    return 'limited';
  }
}

/* ---------- trial / limited access UI ---------- */
function _trialTimeStr(remaining) {
  if (remaining <= 0) return 'under 1 min';
  var d = Math.floor(remaining / 86400000);
  var h = Math.floor((remaining % 86400000) / 3600000);
  var m = Math.floor((remaining % 3600000) / 60000);
  if (d > 0) return d + ' day' + (d !== 1 ? 's' : '') + ' ' + h + ' hr' + (h !== 1 ? 's' : '') + ' ' + m + ' min';
  if (h > 0) return h + ' hr' + (h !== 1 ? 's' : '') + ' ' + m + ' min';
  return m + ' min';
}

function _renderTrialBanner() {
  var banner = document.getElementById('trialBanner');
  if (!banner || window._accessLevel !== 'trial' || !window._trialEndTs) return;
  var remaining = window._trialEndTs - Date.now();
  if (remaining <= 0) {
    window._accessLevel = 'limited';
    applyAccessLevel();
    return;
  }
  var timeStr = _trialTimeStr(remaining);
  banner.innerHTML = '<div class="trial-banner__inner">'
    + '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'
    + '<span><strong>Live Signal Trial: ' + timeStr + ' remaining.</strong> Subscribe now to keep live signal access.</span>'
    + '<a href="subscribe.php" class="trial-banner__btn">Subscribe</a>'
    + '</div>';
  banner.hidden = false;
}

function applyAccessLevel() {
  var banner = document.getElementById('trialBanner');
  var upgrade = document.getElementById('upgradeCta');
  var controls = document.getElementById('signalControls');
  var grid = document.getElementById('signalGrid');
  var dataStatus = document.getElementById('dataStatus');

  var premiumBadge = document.getElementById('headerPremiumBadge');
  var dashPremiumBadge = document.getElementById('dashPremiumBadge');
  var membershipEl = document.getElementById('dashMembershipStatus');
  var isFullAccess = (window._accessLevel === 'full');
  if (premiumBadge) premiumBadge.hidden = !isFullAccess;
  if (dashPremiumBadge) dashPremiumBadge.hidden = !isFullAccess;
  if (membershipEl) {
    if (window._accessLevel === 'full') {
      membershipEl.textContent = 'Premium member';
    } else if (window._accessLevel === 'trial') {
      var d = window._trialDaysLeft || 0;
      membershipEl.textContent = 'Free trial \u2014 ' + d + ' day' + (d === 1 ? '' : 's') + ' left';
    } else {
      membershipEl.textContent = 'Trial expired';
    }
  }

  if (window._accessLevel === 'limited') {
    // Only the Live Signal Grid locks — the rest of the dashboard
    // (Crypto Trading, Economic Calendar, Market Overview, Currency
    // Strength Meter, charts, nav, etc.) stays fully accessible.
    if (banner) banner.hidden = true;
    if (upgrade) upgrade.hidden = false;
    if (controls) controls.hidden = true;
    if (grid) { grid.hidden = true; grid.innerHTML = ''; }
    // Clear (not just hide) any signal-grid status copy so a locked
    // user's DOM never carries live setup/scan content underneath
    // the upgrade prompt.
    if (dataStatus) dataStatus.innerHTML = '';
  } else {
    if (upgrade) upgrade.hidden = true;
    if (controls) controls.hidden = false;
    if (grid) grid.hidden = false;
    if (window._accessLevel === 'trial') _renderTrialBanner();
  }
}

if (!window._trialTicker) {
  window._trialTicker = setInterval(function() {
    if (window._accessLevel === 'trial') _renderTrialBanner();
    else if (window._trialTicker) { clearInterval(window._trialTicker); window._trialTicker = null; }
  }, 60000);
}
</script>
<script>
/* ---------- announcements bell ---------- */
(function(){
  var btn=document.getElementById('bellBtn'),wrap=document.getElementById('bellWrap'),dropdown=document.getElementById('bellDropdown'),list=document.getElementById('bellList'),badge=document.getElementById('bellBadge');
  if(!btn)return;
  btn.addEventListener('click',function(e){e.stopPropagation();var open=dropdown.hidden;dropdown.hidden=!open;btn.setAttribute('aria-expanded',open?'true':'false');});
  document.addEventListener('click',function(e){if(!wrap.contains(e.target)){dropdown.hidden=true;btn.setAttribute('aria-expanded','false');}});
  fetch('https://iwoytmcxmbhmmbrbpzvf.supabase.co/rest/v1/announcements?active=eq.true&order=created_at.desc',{headers:{'apikey':'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml3b3l0bWN4bWJobW1icmJwenZmIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODM5ODM1NjEsImV4cCI6MjA5OTU1OTU2MX0.cKpLKN-Azoj7UUUp3_uodYraYlJH4fQtKpyRitnbMgk'}})
  .then(function(r){return r.json()}).then(function(items){
    if(!items||!items.length){list.innerHTML='<div class="bell-empty">No announcements yet.</div>';return;}
    badge.textContent=items.length;badge.hidden=false;
    list.innerHTML=items.map(function(a){var d=a.created_at?new Date(a.created_at).toLocaleDateString():'';return '<div class="bell-item"><div class="bell-item__title">'+esc(a.title)+'</div><div class="bell-item__msg">'+esc(a.message)+'</div>'+(d?'<div class="bell-item__date">'+d+'</div>':'')+'</div>';}).join('');
  }).catch(function(){list.innerHTML='<div class="bell-empty">Could not load announcements.</div>';});
  function esc(s){var d=document.createElement('div');d.textContent=s;return d.innerHTML;}
})();
</script>
<script>
/* ---------- constants ---------- */
const FX_PAIRS = [
  { code:'EURUSD', label:'EUR / USD', from:'EUR', to:'USD', decimals:4, tv:'FX:EURUSD' },
  { code:'GBPUSD', label:'GBP / USD', from:'GBP', to:'USD', decimals:4, tv:'FX:GBPUSD' },
  { code:'USDJPY', label:'USD / JPY', from:'USD', to:'JPY', decimals:2, tv:'FX:USDJPY' },
  { code:'USDCHF', label:'USD / CHF', from:'USD', to:'CHF', decimals:4, tv:'FX:USDCHF' },
  { code:'AUDUSD', label:'AUD / USD', from:'AUD', to:'USD', decimals:4, tv:'FX:AUDUSD' },
  { code:'NZDUSD', label:'NZD / USD', from:'NZD', to:'USD', decimals:4, tv:'FX:NZDUSD' },
  { code:'USDCAD', label:'USD / CAD', from:'USD', to:'CAD', decimals:4, tv:'FX:USDCAD' },
  { code:'XAUUSD', label:'XAU / USD', from:'XAU', to:'USD', decimals:2, tv:'OANDA:XAUUSD' },
];
const TIMEZONES = [
  { label:'EAT (UTC+3)', zone:'Africa/Nairobi', short:'EAT', tv:'Africa/Nairobi' },
  { label:'WAT (UTC+1)', zone:'Africa/Lagos',   short:'WAT', tv:'Africa/Lagos' },
  { label:'CAT (UTC+2)', zone:'Africa/Johannesburg', short:'CAT', tv:'Africa/Johannesburg' },
  { label:'GMT (UTC+0)', zone:'UTC', short:'UTC', tv:'Etc/UTC' },
  { label:'EST (UTC-5)', zone:'America/New_York', short:'EST', tv:'America/New_York' },
];
const POLL_MS = 30000;

/* ---------- state ---------- */
let signals = [], filterMode = 'all', assetMode = 'all', sortMode = 'pair';
let selectedId = 'EURUSD', lastScanTime = 0, newsFilter = 'all', allNews = [];
let currentTVSymbol = 'FX:EURUSD', currentTVInterval = '60';
let isWeekend = false;
let selectedTimezone = TIMEZONES[0];
let livePrices = {}, lastPriceUpdate = 0;

const livePrice = (code) => livePrices[code] || null;

/* ---------- utils ---------- */
const fmt = (v, d=4) => Number.isFinite(v) ? v.toFixed(d) : '\u2014';
const isTyping = el => /INPUT|TEXTAREA|SELECT/.test(el?.tagName) || el?.isContentEditable;
const fmtClock = (zone) => new Intl.DateTimeFormat('en-GB', { timeZone:zone, hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:false }).format(new Date());

/* ---------- API fetching ---------- */
const _cache = {};
function cachedFetch(url, ttl=45000, opts={}) {
  const key = url + JSON.stringify(opts);
  if (_cache[key] && Date.now() - _cache[key].t < ttl) return Promise.resolve(_cache[key].v);
  return fetch(url, { cache:'no-store', ...opts }).then(r => { if (!r.ok) throw new Error(r.status); return r.json(); }).then(data => { _cache[key] = { v:data, t:Date.now() }; return data; });
}

/* ---------- signal engine config ---------- */
const ENGINE_URL = 'api/engine.php';

async function engineAuthHeader() {
  try {
    if (typeof BMAuth === 'undefined') return {};
    const { session } = await BMAuth.getSession();
    const token = session && session.access_token;
    return token ? { 'Authorization': 'Bearer ' + token } : {};
  } catch (e) { return {}; }
}

/* ---------- currency strength poller (independent of signals/grid access) ---------- */
let _strengthTimer = null;
async function pollStrength() {
  try {
    const sr = await fetch(ENGINE_URL + '?route=strength', { cache: 'no-store' });
    if (sr.ok) {
      const sd = await sr.json();
      if (sd.currencies) {
        renderStrengthFromData(sd.currencies);
        return;
      }
    }
  } catch (e) { /* engine may be restarting */ }
  var sk = document.getElementById('smSkeleton');
  if (sk) sk.style.display = 'none';
}
function startStrengthPolling() {
  pollStrength();
  _strengthTimer = setInterval(pollStrength, 30000);
}

/* ---------- live price poller (every 10s) ---------- */
let _priceTimer = null;
async function pollPrices() {
  try {
    const r = await fetch(ENGINE_URL + '?route=prices', { cache: 'no-store' });
    if (!r.ok) return;
    const d = await r.json();
    if (d.prices && Object.keys(d.prices).length) {
      livePrices = d.prices;
      lastPriceUpdate = Date.now();
      updateLivePrices();
    }
  } catch(e) { /* engine may be restarting */ }
}
function updateLivePrices() {
  document.querySelectorAll('[data-live-price]').forEach(el => {
    const code = el.dataset.livePrice;
    const price = livePrices[code];
    if (price != null) {
      const sig = signals.find(s => s.pair.code === code);
      if (sig) {
        const old = el.textContent;
        const fresh = fmt(price, sig.pair.decimals);
        el.textContent = fresh;
        if (old !== fresh) { el.classList.add('price-flash'); setTimeout(() => el.classList.remove('price-flash'), 800); }
      }
    }
  });
  document.querySelectorAll('[data-live-price-mo]').forEach(el => {
    const code = el.dataset.livePriceMo;
    const price = livePrices[code];
    if (price != null) {
      const sig = signals.find(s => s.pair.code === code);
      if (sig) {
        const old = el.textContent;
        const fresh = fmt(price, sig.pair.decimals);
        el.textContent = fresh;
        if (old !== fresh) { el.classList.add('price-flash'); setTimeout(() => el.classList.remove('price-flash'), 800); }
      }
    }
  });
  const ageEl = document.getElementById('priceAge');
  if (ageEl) {
    const ageSec = Math.round((Date.now() - lastPriceUpdate) / 1000);
    ageEl.textContent = ageSec < 5 ? 'Live' : `${ageSec}s ago`;
  }
}
function startPricePolling() {
  pollPrices();
  _priceTimer = setInterval(pollPrices, 5000);
}

/* ---------- rescan ---------- */
let _abortCtrl = null;
let _forceRescan = false;
async function rescanAll() {
  _abortCtrl?.abort(); _abortCtrl = new AbortController();
  const { signal } = _abortCtrl;
  const st = document.getElementById('rescanStatus');
  if (st) st.textContent = 'Scanning live markets\u2026';

  const forceParam = _forceRescan ? '?force=1' : '';
  _forceRescan = false;

  try {
    const r = await fetch(ENGINE_URL + '?route=signals' + forceParam.replace('?', '&'), {
      signal, cache: 'no-store', headers: await engineAuthHeader()
    });
    if (!r.ok) throw new Error(r.status);
    const data = await r.json();
    if (data.signals && data.signals.length) {
      signals = deduplicateSignals(data.signals); lastScanTime = Date.now();
      isWeekend = !!data.weekend;
      if (!signals.find(s => s.pair.code === selectedId)) selectedId = signals[0].pair.code;
      // Defense-in-depth: if the server marks this response as locked
      // (expired trial / no subscription, verified server-side), make
      // sure only the Live Signal Grid reflects that — everything else
      // (Market Overview, Signal Metrics, charts) keeps rendering with
      // whatever non-setup data it received.
      if (data.signal_access === 'locked' && window._accessLevel !== 'full') {
        window._accessLevel = 'limited';
        applyAccessLevel();
      }
      renderAll();
      // Locked users get none of the Live Signal Grid's status copy either
      // — the scan/setup counts and data-source note are signal-grid
      // content just like the cards and filters.
      if (window._accessLevel === 'limited') {
        if (st) st.textContent = '';
        return;
      }
      const activeCount = signals.filter(s => s.setup).length;
      const pausedCount = signals.filter(s => s.paused).length;
      if (st) st.textContent = `SMC scan complete — ${activeCount} setup${activeCount !== 1 ? 's' : ''} active` + (pausedCount ? `, ${pausedCount} paused (weekend)` : '') + '.';
      const ds = document.getElementById('dataStatus');
      if (ds) ds.innerHTML = '<div class="status-banner info">Data: SMC multi-timeframe analysis (Daily + 4H + 1H).</div>';
      return;
    }
  } catch (e) {
    if (e.name === 'AbortError') return;
    console.warn('Engine unavailable:', e.message);
    if (st) st.textContent = 'Connecting to signal engine\u2026';
  }
}

/* ---------- premium gauge helpers ---------- */
var _smCurrencies = null;
var _smPairMap = {
  'EURUSD': {base:'EUR',quote:'USD'},
  'GBPUSD': {base:'GBP',quote:'USD'},
  'USDJPY': {base:'USD',quote:'JPY'},
  'AUDUSD': {base:'AUD',quote:'USD'},
  'USDCAD': {base:'USD',quote:'CAD'},
  'USDCHF': {base:'USD',quote:'CHF'},
  'NZDUSD': {base:'NZD',quote:'USD'},
  'XAUUSD': {base:'XAU',quote:'USD'},
  'BTCUSD': {base:'BTC',quote:'USD'}
};
function _smPairScore(pair, ccyMap) {
  var m = _smPairMap[pair];
  if (!m) return 0;
  var b = ccyMap[m.base], q = ccyMap[m.quote];

  // Dynamic fallback for XAU and BTC if missing from ccyMap
  if (!b && m.base === 'XAU') {
    var xauSig = (typeof signals !== 'undefined' && Array.isArray(signals)) ? signals.find(function(s){ return s.pair && s.pair.code === 'XAUUSD'; }) : null;
    var score = 58;
    if (xauSig && xauSig.structure && xauSig.structure.rsi) {
      score = Math.round(xauSig.structure.rsi);
    } else if (xauSig && xauSig.bias) {
      score = xauSig.bias === 'BULLISH' ? 72 : xauSig.bias === 'BEARISH' ? 28 : 50;
    } else if (typeof livePrices !== 'undefined' && livePrices['XAUUSD']) {
      var chg = livePrices['XAUUSD'].change_pct || livePrices['XAUUSD'].change || 0;
      score = Math.max(15, Math.min(88, Math.round(50 + chg * 15)));
    }
    b = { currency: 'XAU', score: score };
    ccyMap['XAU'] = b;
  }
  if (!b && m.base === 'BTC') {
    var btcSig = (typeof signals !== 'undefined' && Array.isArray(signals)) ? signals.find(function(s){ return s.pair && s.pair.code === 'BTCUSD'; }) : null;
    var score = 64;
    if (btcSig && btcSig.structure && btcSig.structure.rsi) {
      score = Math.round(btcSig.structure.rsi);
    } else if (btcSig && btcSig.bias) {
      score = btcSig.bias === 'BULLISH' ? 76 : btcSig.bias === 'BEARISH' ? 24 : 50;
    } else if (typeof livePrices !== 'undefined' && livePrices['BTCUSD']) {
      var chg = livePrices['BTCUSD'].change_pct || livePrices['BTCUSD'].change || 0;
      score = Math.max(15, Math.min(88, Math.round(50 + chg * 12)));
    }
    b = { currency: 'BTC', score: score };
    ccyMap['BTC'] = b;
  }

  if (!b || !q) return 0;
  var bs = b.score || 50, qs = q.score || 50;
  var raw = bs - qs; // -100 to +100
  // Pairs where USD is base (USDJPY, USDCAD, USDCHF) — invert
  if (m.base === 'USD') raw = -raw;
  return Math.max(-100, Math.min(100, raw));
}
function _smSignalLabel(score) {
  return score > 25 ? 'Strong Buy' : score > 5 ? 'Buy' : score > -5 ? 'Neutral' : score > -25 ? 'Sell' : 'Strong Sell';
}
function _smShortSignal(score) {
  return score > 5 ? 'BUY' : score < -5 ? 'SELL' : 'NEUTRAL';
}
function _smScoreConfidence(score) {
  return Math.min(99, Math.round(Math.abs(score) * 0.8 + 20));
}

var _smPairNames = {
  'EURUSD':'Euro vs US Dollar','GBPUSD':'British Pound vs US Dollar','USDJPY':'Japanese Yen vs US Dollar',
  'AUDUSD':'Australian Dollar vs US Dollar','USDCAD':'Canadian Dollar vs US Dollar','USDCHF':'Swiss Franc vs US Dollar',
  'NZDUSD':'New Zealand Dollar vs US Dollar','XAUUSD':'Gold vs US Dollar','BTCUSD':'Bitcoin vs US Dollar'
};

/* ---------- polar coordinate gauge builder ---------- */
function polarToCartesian(cx, cy, r, angleDeg) {
  var rad = angleDeg * Math.PI / 180;
  return { x: cx + r * Math.cos(rad), y: cy + r * Math.sin(rad) };
}
function describeArc(cx, cy, r, startAngle, endAngle) {
  var start = polarToCartesian(cx, cy, r, startAngle);
  var end = polarToCartesian(cx, cy, r, endAngle);
  var large = (endAngle - startAngle) > 180 ? 1 : 0;
  return 'M ' + start.x.toFixed(1) + ' ' + start.y.toFixed(1) + ' A ' + r + ' ' + r + ' 0 ' + large + ' 1 ' + end.x.toFixed(1) + ' ' + end.y.toFixed(1);
}
function buildGauge() {
  var c = document.getElementById('smGaugeContainer');
  if (!c) return;
  var cx = 200, cy = 270, arcR = 150, bandW = 18;
  var innerR = arcR - bandW / 2;
  var outerR = arcR + bandW / 2;
  var labelR = outerR + 16;
  var sepAngles = [216, 252, 288, 324];
  var labelDefs = [
    { text: 'Strong Sell', angle: 198 },
    { text: 'Sell', angle: 234 },
    { text: 'Neutral', angle: 270 },
    { text: 'Buy', angle: 306 },
    { text: 'Strong Buy', angle: 342 }
  ];
  var nl = innerR * 0.93;
  var nt = polarToCartesian(cx, cy, nl, 270);
  var h = '<svg class="sm-gauge" viewBox="0 0 400 320" aria-label="Currency strength gauge">';
  h += '<defs>';
  h += '<linearGradient id="gG" x1="0%" y1="0%" x2="100%" y2="0%">';
  h += '<stop offset="0%" stop-color="#F44336"/>';
  h += '<stop offset="20%" stop-color="#FF9800"/>';
  h += '<stop offset="35%" stop-color="#FFC107"/>';
  h += '<stop offset="50%" stop-color="#FFEB3B"/>';
  h += '<stop offset="65%" stop-color="#CDDC39"/>';
  h += '<stop offset="80%" stop-color="#8BC34A"/>';
  h += '<stop offset="100%" stop-color="#4CAF50"/>';
  h += '</linearGradient>';
  h += '<filter id="hs"><feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#000" flood-opacity="0.25"/></filter>';
  h += '</defs>';
  h += '<path d="' + describeArc(cx, cy, arcR, 180, 360) + '" fill="none" stroke="rgba(255,255,255,0.02)" stroke-width="' + (bandW + 8) + '"/>';
  h += '<path d="' + describeArc(cx, cy, arcR, 180, 360) + '" fill="none" stroke="url(#gG)" stroke-width="' + bandW + '" stroke-linecap="butt"/>';
  for (var i = 0; i < sepAngles.length; i++) {
    var a = sepAngles[i];
    var ip = polarToCartesian(cx, cy, innerR, a);
    var op = polarToCartesian(cx, cy, outerR, a);
    h += '<line x1="' + ip.x.toFixed(1) + '" y1="' + ip.y.toFixed(1) + '" x2="' + op.x.toFixed(1) + '" y2="' + op.y.toFixed(1) + '" stroke="rgba(255,255,255,0.2)" stroke-width="2" stroke-linecap="round"/>';
  }
  h += '<line class="sm-needle" id="smNeedle" x1="200" y1="270" x2="200" y2="135" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" transform="rotate(0, 200, 270)"/>';
  h += '<circle cx="200" cy="270" r="20" fill="#0B1220" stroke="rgba(255,255,255,0.12)" stroke-width="2" filter="url(#hs)"/>';
  h += '<circle cx="200" cy="270" r="8" fill="#F8FAFC"/>';
  for (var i = 0; i < labelDefs.length; i++) {
    var p = polarToCartesian(cx, cy, labelR, labelDefs[i].angle);
    h += '<text x="' + p.x.toFixed(1) + '" y="' + p.y.toFixed(1) + '" class="sm-zone-label" text-anchor="middle">' + labelDefs[i].text + '</text>';
  }
  h += '</svg>';
  c.innerHTML = h;
}
/* ------------------------------------------------ */
function refreshGauge() {
  if (!_smCurrencies) return;
  var ccyMap = {};
  for (var i = 0; i < _smCurrencies.length; i++) {
    var c = _smCurrencies[i];
    ccyMap[c.currency.toUpperCase()] = c;
  }
  var pairSelect = document.getElementById('smPairSelect');
  if (!pairSelect) return;
  var pair = pairSelect.value;
  var pairScore = _smPairScore(pair, ccyMap);
  var signal = _smShortSignal(pairScore);
  var isBullish = signal === 'BUY';
  var isBearish = signal === 'SELL';
  var confidence = _smScoreConfidence(pairScore);
  var sentLabel = _smSignalLabel(pairScore);

  // Hide skeleton, show content
  var sk = document.getElementById('smSkeleton');
  if (sk) sk.style.display = 'none';

  // Pair description
  var desc = document.getElementById('smPairDesc');
  if (desc) desc.textContent = _smPairNames[pair] || '';

  // Needle animation — pairScore * 0.9 maps -100..+100 to -90°..+90° around the gauge arc
  var needle = document.getElementById('smNeedle');
  if (needle) needle.setAttribute('transform', 'rotate(' + (pairScore * 0.9) + ', 200, 270)');

  // Score circle
  var scoreVal = document.getElementById('smScoreValue');
  if (scoreVal) {
    scoreVal.textContent = (pairScore > 0 ? '+' : '') + pairScore;
    scoreVal.style.color = isBullish ? '#00C853' : isBearish ? '#FF5252' : '#FFC107';
  }
  var scoreSent = document.getElementById('smScoreSentiment');
  if (scoreSent) {
    scoreSent.textContent = sentLabel;
    scoreSent.style.color = isBullish ? '#00C853' : isBearish ? '#FF5252' : '#FFC107';
  }

  // Signal block
  var labelEl = document.getElementById('smSignalLabel');
  if (labelEl) {
    labelEl.textContent = signal;
    labelEl.style.color = isBullish ? '#00C853' : isBearish ? '#FF5252' : '#FFC107';
  }
  var confEl = document.getElementById('smConfidencePct');
  if (confEl) confEl.textContent = confidence + '%';
  var confFill = document.getElementById('smConfidenceFill');
  if (confFill) {
    confFill.style.width = Math.min(confidence, 100) + '%';
    confFill.style.background = isBullish ? '#00C853' : isBearish ? '#FF5252' : '#FFC107';
  }

  // Stats
  var trend = isBullish ? 'Bullish' : isBearish ? 'Bearish' : 'Sideways';
  var momentum = Math.abs(pairScore) > 40 ? 'Strong' : Math.abs(pairScore) > 15 ? 'Moderate' : 'Weak';
  var rsiVal = Math.max(20, Math.min(80, Math.round(pairScore * 0.3 + 50)));
  var macdSig = signal;
  var adxVal = Math.max(15, Math.min(60, Math.round(Math.abs(pairScore) * 0.4 + 18)));
  var atrVal = Math.abs(pairScore) > 30 ? 'High' : Math.abs(pairScore) > 10 ? 'Medium' : 'Low';
  _smSetStat('smStatTrend', trend, isBullish ? '#00C853' : isBearish ? '#FF5252' : '#FFC107');
  _smSetStatByFill('smStatTrendFill', trend === 'Bullish' ? 85 : trend === 'Bearish' ? 25 : 50, isBullish ? '#00C853' : isBearish ? '#FF5252' : '#FFC107');
  _smSetStat('smStatMomentum', momentum, momentum === 'Strong' ? '#00C853' : momentum === 'Moderate' ? '#FFC107' : '#94A3B8');
  _smSetStatByFill('smStatMomFill', momentum === 'Strong' ? 85 : momentum === 'Moderate' ? 55 : 25, momentum === 'Strong' ? '#00C853' : momentum === 'Moderate' ? '#FFC107' : '#64748B');
  _smSetStat('smStatRSI', rsiVal, rsiVal > 60 ? '#00C853' : rsiVal < 40 ? '#FF5252' : '#FFC107');
  _smSetStatByFill('smStatRsiFill', rsiVal, rsiVal > 60 ? '#00C853' : rsiVal < 40 ? '#FF5252' : '#FFC107');
  _smSetStat('smStatMACD', macdSig, isBullish ? '#00C853' : isBearish ? '#FF5252' : '#FFC107');
  _smSetStatByFill('smStatMacdFill', isBullish ? 80 : isBearish ? 25 : 50, isBullish ? '#00C853' : isBearish ? '#FF5252' : '#FFC107');
  _smSetStat('smStatADX', adxVal, adxVal > 30 ? '#00C853' : adxVal > 20 ? '#FFC107' : '#94A3B8');
  _smSetStatByFill('smStatAdxFill', adxVal, adxVal > 30 ? '#00C853' : adxVal > 20 ? '#FFC107' : '#64748B');
  _smSetStat('smStatATR', atrVal, atrVal === 'High' ? '#FF5252' : atrVal === 'Medium' ? '#FFC107' : '#94A3B8');
  _smSetStatByFill('smStatAtrFill', atrVal === 'High' ? 85 : atrVal === 'Medium' ? 55 : 25, atrVal === 'High' ? '#FF5252' : atrVal === 'Medium' ? '#FFC107' : '#64748B');

  // Indicators
  var maSignals = _smDistributeSignals(pairScore, 0.4);
  _smSetIndicator('smIndEma20', 'smPillEma20', maSignals[0]);
  _smSetIndicator('smIndEma50', 'smPillEma50', maSignals[1]);
  _smSetIndicator('smIndEma100', 'smPillEma100', maSignals[2]);
  _smSetIndicator('smIndEma200', 'smPillEma200', maSignals[3]);

  var oscSignals = _smDistributeSignals(pairScore * 0.7 + 8, 0.6);
  _smSetIndicator('smIndRsi', 'smPillRsi', oscSignals[0]);
  _smSetIndicator('smIndStoch', 'smPillStoch', oscSignals[1]);
  _smSetIndicator('smIndCci', 'smPillCci', oscSignals[2]);
  _smSetIndicator('smIndWilliams', 'smPillWilliams', oscSignals[3]);
  _smSetIndicator('smIndMacd', 'smPillMacd', oscSignals[4]);

  // Ranking + Heatmap
  _smRenderRanking(ccyMap);
  _smRenderHeatmap(ccyMap);

  // Timestamp
  _smUpdateTimestamp();
}

function _smSetStat(id, val, color) {
  var el = document.getElementById(id);
  if (el) { el.textContent = val; el.style.color = color || '#e2e8f0'; }
}
function _smSetIndicator(valId, pillId, sig) {
  var v = document.getElementById(valId);
  var p = document.getElementById(pillId);
  if (v) v.textContent = sig.label || '--';
  if (p) {
    p.textContent = sig.text || sig.label || '--';
    p.className = 'sm-indicator__pill sm-pill--' + sig.pill;
  }
}
function _smDistributeSignals(score, jitter) {
  var out = [];
  for (var i = 0; i < 5; i++) {
    var offset = (i - 2) * 14 * jitter;
    var s = score + offset + (Math.random() * 8 - 4) * jitter;
    var label, pill;
    if (s > 20) { label = 'Strong Buy'; pill = 'strong-buy'; }
    else if (s > 5) { label = 'Buy'; pill = 'buy'; }
    else if (s > -5) { label = 'Neutral'; pill = 'neutral'; }
    else if (s > -20) { label = 'Sell'; pill = 'sell'; }
    else { label = 'Strong Sell'; pill = 'strong-sell'; }
    out.push({label:label, text:label, pill:pill});
  }
  return out;
}

function _smSetStatByFill(id, pct, color) {
  var el = document.getElementById(id);
  if (el) { el.style.width = Math.max(2, Math.min(100, pct)) + '%'; el.style.background = color || '#3B82F6'; }
}

function _smRenderRanking(ccyMap) {
  var el = document.getElementById('smRankingList');
  if (!el) return;
  var ccyOrder = ['USD','EUR','GBP','JPY','AUD','NZD','CAD','CHF','XAU','BTC'];
  var items = [];
  for (var i = 0; i < ccyOrder.length; i++) {
    var c = ccyMap[ccyOrder[i]];
    if (!c) continue;
    var s = c.score || 50;
    var col = s > 60 ? '#00C853' : s > 45 ? '#FFC107' : '#FF5252';
    items.push({ccy:ccyOrder[i], score:s, color:col});
  }
  items.sort(function(a,b){ return b.score - a.score; });
  var maxS = items.length ? items[0].score : 100;
  el.innerHTML = items.map(function(it){
    var pct = Math.max(3, it.score / maxS * 100);
    return '<div class="sm-ranking-item">'
      + '<span class="sm-ranking-item__ccy">' + it.ccy + '</span>'
      + '<div class="sm-ranking-item__bar-track"><div class="sm-ranking-item__bar-fill" style="width:' + pct + '%;background:' + it.color + '"></div></div>'
      + '<span class="sm-ranking-item__score" style="color:' + it.color + '">' + it.score + '</span>'
      + '</div>';
  }).join('');
}

function _smRenderHeatmap(ccyMap) {
  var el = document.getElementById('smHeatmapGrid');
  if (!el) return;
  var pairs = ['EURUSD','GBPUSD','USDJPY','AUDUSD','USDCAD','USDCHF','NZDUSD','XAUUSD','BTCUSD'];
  el.innerHTML = pairs.map(function(p){
    var s = _smPairScore(p, ccyMap);
    var cls = s > 5 ? 'bullish' : s < -5 ? 'bearish' : 'neutral';
    var lbl = s > 0 ? '+' : '';
    return '<div class="sm-heatmap-cell sm-heatmap-cell--' + cls + '">' + p.replace('USD','/USD') + ' ' + lbl + s + '</div>';
  }).join('');
}

function _smUpdateTimestamp() {
  var el = document.getElementById('smLastUpdated');
  var up = document.getElementById('smUpdated');
  if (el) {
    var d = new Date();
    el.textContent = d.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit',second:'2-digit'});
  }
  if (up) {
    var d = new Date();
    up.textContent = 'Updated ' + d.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
  }
}

function renderStrengthFromData(currencies) {
  _smCurrencies = currencies;
  refreshGauge();
}

/* ---------- strength meter event wiring ---------- */
document.addEventListener('DOMContentLoaded', function() {
  buildGauge();
  var pairSelect = document.getElementById('smPairSelect');
  if (pairSelect) {
    pairSelect.addEventListener('change', refreshGauge);
  }
  var tfBtns = document.querySelectorAll('.sm-tf-btn');
  for (var i = 0; i < tfBtns.length; i++) {
    tfBtns[i].addEventListener('click', function() {
      var parent = this.parentNode;
      var btns = parent.querySelectorAll('.sm-tf-btn');
      for (var j = 0; j < btns.length; j++) btns[j].classList.remove('active');
      this.classList.add('active');
      refreshGauge();
    });
  }
});

/* ---------- news ---------- */
async function fetchNews() {
  try {
    const data = await cachedFetch('api/news.php', 900000);
    allNews = Array.isArray(data) ? data : [];
  } catch(e) { console.warn('News fail:', e.message); }
  if (!allNews.length) {
    allNews = [
      { time:'\u2192', country:'ALL', title:'Economic calendar is temporarily unavailable.', impact:'Medium', forecast:'', previous:'', url:'https://www.forexfactory.com/calendar' },
      { title:'View Full Calendar on ForexFactory', country:'ALL', time:'\u2192', impact:'High', url:'https://www.forexfactory.com/calendar' },
    ];
  }
  renderNews();
}
function renderNews() {
  const el = document.getElementById('newsList');
  let events = newsFilter === 'all' ? allNews : allNews.filter(e => (e.impact||'').toLowerCase() === newsFilter.toLowerCase());
  if (!events.length) { el.innerHTML = '<div class="news-item"><span class="time mono">\u2014</span><span class="title">No events match this filter.</span><span class="impact medium">\u2014</span><span class="countdown">\u2014</span></div>'; return; }
  el.innerHTML = events.slice(0, 40).map(e => {
    const ic = (e.impact||'').toLowerCase();
    const cls = ic === 'high' ? 'high' : ic === 'medium'||ic === 'moderate' ? 'medium' : ic === 'holiday' ? 'holiday' : 'low';
    const lbl = ic === 'high' ? 'High' : ic === 'medium'||ic === 'moderate' ? 'Medium' : ic === 'holiday' ? 'Holiday' : 'Low';
    const ccy = (e.country||'FX').toUpperCase();
    const time = e.time ? e.time.replace(/(\d+:\d+)(am|pm)/i,'$1 $2').toUpperCase() : 'All Day';
    const url  = e.url || 'https://www.forexfactory.com/';
    const ext  = (e.forecast ? ` \u00b7 F: ${e.forecast}` : '') + (e.previous ? ` \u00b7 P: ${e.previous}` : '');
    return `<div class="news-item">
      <span class="time mono">${time}</span>
      <a class="title" href="${url}" target="_blank" rel="noopener noreferrer">
        <span class="ccy">${ccy}</span>${(e.title||'Economic Event').replace(/</g,'&lt;')}${ext}
      </a>
      <span class="impact ${cls}">${lbl}</span>
      <a class="countdown" href="${url}" target="_blank" rel="noopener noreferrer">FF &#8599;</a>
    </div>`;
  }).join('');
}

/* ---------- Price Levels SVG ---------- */
function drawPriceLevels(svgEl, sig, W, H) {
  const dec = sig.pair.decimals;
  const price = sig.currentPrice || 0;
  if (!price) return;

  let levels = [];
  if (sig.setup) {
    const s = sig.setup;
    levels = [
      { price: s.sl, color: '#F6465D', label: 'SL', width: 2 },
      { price: s.entry, color: '#1677FF', label: 'Entry', width: 2.5 },
      { price: s.tp1, color: '#16C784', label: 'TP1 (1:2)', width: 1.5 },
      { price: s.tp2, color: '#16C784', label: 'TP2 (1:3)', width: 1.5 },
    ];
    if (s.ob_high) levels.push({ price: s.ob_high, color: '#8B5CF6', label: 'OB High', width: 1, dash: '4 4' });
    if (s.ob_low) levels.push({ price: s.ob_low, color: '#8B5CF6', label: 'OB Low', width: 1, dash: '4 4' });
  }

  levels.push({ price: price, color: '#2F80FF', label: 'Price', width: 2 });

  const allPrices = levels.map(l => l.price);
  const lo = Math.min(...allPrices) * 0.999;
  const hi = Math.max(...allPrices) * 1.001;
  const rng = hi - lo || 1;
  const pad = 80, top = 20, bot = H - 20;
  const y = p => bot - ((p - lo) / rng) * (bot - top);

  let s = '';
  levels.forEach(l => {
    const yy = y(l.price);
    const dash = l.dash || '6 3';
    s += `<line x1="${pad}" y1="${yy}" x2="${W-20}" y2="${yy}" stroke="${l.color}" stroke-width="${l.width}" stroke-dasharray="${dash}" opacity="0.8"/>`;
    s += `<circle cx="${pad+6}" cy="${yy}" r="4" fill="${l.color}"/>`;
    s += `<text x="${pad+16}" y="${yy+4}" font-family="IBM Plex Mono,monospace" font-size="11" fill="${l.color}">${l.label} ${fmt(l.price, dec)}</text>`;
  });

  svgEl.setAttribute('viewBox', `0 0 ${W} ${H}`);
  svgEl.innerHTML = s;
}

/* ---------- grid rendering ---------- */
function deduplicateSignals(rawSignals) {
  if (!Array.isArray(rawSignals)) return [];
  const map = new Map();
  rawSignals.forEach(s => {
    if (!s || !s.pair) return;
    const code = (s.pair.code || s.pair.symbol || s.code || '').toUpperCase();
    if (!code) return;
    if (!map.has(code)) {
      map.set(code, s);
    } else {
      const existing = map.get(code);
      const hasSetup = !!s.setup;
      const existingHasSetup = !!existing.setup;
      if (!existingHasSetup && hasSetup) {
        map.set(code, s);
      } else if (existingHasSetup && hasSetup) {
        const existingRR = parseFloat(existing.setup.rr) || 0;
        const newRR = parseFloat(s.setup.rr) || 0;
        if (newRR > existingRR) {
          map.set(code, s);
        }
      }
    }
  });
  return Array.from(map.values());
}

function filteredSorted() {
  let list = deduplicateSignals(signals);
  if (assetMode !== 'all')  list = list.filter(s => s.pair.asset === assetMode);
  if (filterMode === 'setup')   list = list.filter(s => s.setup);
  if (filterMode === 'nosetup') list = list.filter(s => !s.setup && !s.paused);
  if (filterMode === 'paused')  list = list.filter(s => s.paused);
  if (filterMode === 'buy')     list = list.filter(s => s.setup && s.setup.direction === 'buy');
  if (filterMode === 'sell')    list = list.filter(s => s.setup && s.setup.direction === 'sell');
  if (sortMode === 'pair')      list.sort((a,b) => a.pair.code.localeCompare(b.pair.code));
  if (sortMode === 'setup')     list.sort((a,b) => (b.setup ? 1 : 0) - (a.setup ? 1 : 0));
  return list;
}
function skelCard() {
  return '<div class="card skeleton" aria-hidden="true"><div class="skeleton-line" style="width:50%;height:20px"></div><div class="skeleton-line" style="width:80%"></div><div class="skeleton-line" style="width:100%;height:60px"></div><div class="skeleton-line" style="width:60%"></div></div>';
}
function renderGrid() {
  const grid = document.getElementById('signalGrid');
  if (!grid) return;
  // Locked users get no grid content at all — the upgrade prompt is the
  // entire Live Signal Grid presentation for them (conditional render,
  // not a CSS cover-up).
  if (window._accessLevel === 'limited') { grid.innerHTML = ''; return; }
  if (!lastScanTime) { grid.innerHTML = Array.from({length:6}).map(skelCard).join(''); return; }
  const list = filteredSorted();
  if (!list.length) { grid.innerHTML = '<div class="status-banner info">No pairs match this filter right now.</div>'; return; }
  grid.innerHTML = '';
  list.forEach(sig => {
    const hasSetup = !!sig.setup;
    const dir = hasSetup ? sig.setup.direction : null;
    const bc = !hasSetup ? 'neutral' : dir === 'buy' ? 'buy' : 'sell';
    const card = document.createElement('div');
    card.className = 'card' + (sig.pair.code === selectedId ? ' selected':'') + (!hasSetup ? ' neutral':'');
    card.dataset.id = sig.pair.code;
    card.setAttribute('role','button'); card.setAttribute('tabindex','0');
    card.setAttribute('aria-pressed', sig.pair.code === selectedId ? 'true':'false');

    if (hasSetup) {
      card.setAttribute('aria-label', `${sig.pair.label}, ${dir} setup, R:R 1:${sig.setup.rr}`);
    } else {
      const trend = sig.structure?.trend || 'neutral';
      card.setAttribute('aria-label', `${sig.pair.label}, no active setup, trend ${trend}`);
    }

    const trend = sig.structure?.trend || 'neutral';
    const rsi = sig.structure?.rsi || 50;
    const dailyBias = sig.structure?.daily_bias || 'neutral';
    const trendIcon = trend === 'bullish' ? '\u25B2' : trend === 'bearish' ? '\u25BC' : '\u25CF';
    const trendColor = trend === 'bullish' ? '#22C55E' : trend === 'bearish' ? '#E8664A' : '#5B6472';

    if (sig.paused) {
      card.className = 'card neutral' + (sig.pair.code === selectedId ? ' selected':'');
      card.setAttribute('aria-label', `${sig.pair.label}, trading paused for weekend`);
      card.innerHTML = `
        <div class="card-top">
          <span class="pairname">${sig.pair.label}</span>
          <span class="dir-badge neutral" style="background:rgba(212,162,76,.15);color:var(--gold)">⏸ Paused</span>
        </div>
        <div class="cond-tags">
          <span style="color:${trendColor}">${trendIcon} ${trend}</span>
          <span style="color:var(--ink-dim)">${sig.pair.asset === 'commodity' ? 'Commodity' : 'Forex'}</span>
        </div>
        <div class="levels">
          <div style="grid-column:1/-1;text-align:center;padding:18px 0;color:var(--gold);font-size:.85rem;">
            Trading paused for the weekend<br><span style="color:var(--ink-dim);font-size:.78rem">Resumes Sunday 22:00 UTC</span>
          </div>
        </div>
        <div class="card-foot">
          <span class="expiry" style="color:var(--gold)">Weekend</span>
        </div>`;
    } else if (hasSetup) {
      const s = sig.setup;
      const rrColor = s.rr >= 3 ? '#22C55E' : '#4FD1C5';
      card.innerHTML = `
        <div class="card-top">
          <span class="pairname">${sig.pair.label}</span>
          <span class="dir-badge ${bc}">${dir === 'buy' ? 'BUY' : 'SELL'}</span>
        </div>
        <div class="cond-tags">
          <span style="color:var(--gold)">${s.type} ${s.trigger || ''}</span>
          <span style="color:${trendColor}">${trendIcon} ${dailyBias}</span>
        </div>
        <div class="levels">
          <div><span class="lbl">Current</span> <span class="val mono" data-live-price="${sig.pair.code}">${fmt(livePrice(sig.pair.code) || sig.currentPrice, sig.pair.decimals)}</span></div>
          <div><span class="lbl">Entry</span>   <span class="val entry mono">${fmt(s.entry, sig.pair.decimals)}</span></div>
          <div><span class="lbl">Stop loss</span><span class="val sl mono">${fmt(s.sl, sig.pair.decimals)}</span></div>
          <div><span class="lbl">Target 1</span> <span class="val tp mono">${fmt(s.tp1, sig.pair.decimals)}</span></div>
          <div><span class="lbl">Target 2</span> <span class="val tp mono">${fmt(s.tp2, sig.pair.decimals)}</span></div>
        </div>
        <div class="card-foot">
          <span class="expiry" style="color:${rrColor}">R:R 1:${s.rr}</span>
          <span class="expiry" style="color:var(--teal)">${s.risk_pips || '\u2014'} risk</span>
        </div>`;
    } else {
      const rsiColor = rsi > 70 ? '#E8664A' : rsi < 30 ? '#22C55E' : '#5B6472';
      card.innerHTML = `
        <div class="card-top">
          <span class="pairname">${sig.pair.label}</span>
          <span class="dir-badge neutral">No Setup</span>
        </div>
        <div class="cond-tags">
          <span style="color:${trendColor}">${trendIcon} ${trend}</span>
          <span style="color:${rsiColor}">RSI ${rsi}</span>
          <span style="color:var(--ink-dim)">Bias: ${dailyBias}</span>
        </div>
        <div class="levels">
          <div><span class="lbl">Current</span> <span class="val mono" data-live-price="${sig.pair.code}">${fmt(livePrice(sig.pair.code) || sig.currentPrice, sig.pair.decimals)}</span></div>
          <div style="grid-column:1/-1;text-align:center;padding:12px 0;color:var(--ink-dim);font-size:.85rem;">
            Waiting for a valid SMC setup to form
          </div>
        </div>
        <div class="card-foot">
          <span class="expiry" style="color:var(--ink-dim)">Watching</span>
        </div>`;
    }

    const open = () => {
      selectedId = sig.pair.code; renderAll();
      document.getElementById('spotlight').scrollIntoView({ behavior:'smooth', block:'start' });
    };
    card.addEventListener('click', open);
    card.addEventListener('keydown', e => { if (e.key==='Enter'||e.key===' ') { e.preventDefault(); open(); } });
    grid.appendChild(card);
  });
}

/* ---------- spotlight rendering ---------- */
function renderSpotlight() {
  const sig = signals.find(s => s.pair.code === selectedId);
  if (!sig) return;
  const curPrice = livePrice(sig.pair.code) || sig.currentPrice;
  const st = sig.structure || {};
  const hasSetup = !!sig.setup;

  document.getElementById('spotPair').textContent = sig.pair.label;

  const trendIcon = st.trend === 'bullish' ? '\u25B2' : st.trend === 'bearish' ? '\u25BC' : '\u25CF';
  const trendColor = st.trend === 'bullish' ? '#22C55E' : st.trend === 'bearish' ? '#E8664A' : '#5B6472';
  document.getElementById('spotMeta').innerHTML = `<span style="color:${trendColor}">${trendIcon} ${st.trend || 'neutral'}</span> &middot; RSI ${st.rsi || 50} &middot; Daily bias: ${st.daily_bias || 'neutral'}`;

  if (hasSetup) {
    const s = sig.setup;
    const dirColor = s.direction === 'buy' ? '#22C55E' : '#E8664A';
    document.getElementById('spotStatus').textContent = 'Active';
    document.getElementById('spotStatus').style.color = '#22C55E';
    document.getElementById('spotDir').textContent = s.direction === 'buy' ? 'BUY' : 'SELL';
    document.getElementById('spotDir').style.color = dirColor;
    document.getElementById('spotEntry').textContent = fmt(s.entry, sig.pair.decimals);
    document.getElementById('spotSL').textContent = fmt(s.sl, sig.pair.decimals);
    document.getElementById('spotTP1').textContent = fmt(s.tp1, sig.pair.decimals);
    document.getElementById('spotTP2').textContent = fmt(s.tp2, sig.pair.decimals);
    document.getElementById('spotRR').textContent = '1 : ' + s.rr;
    document.getElementById('spotRR').style.color = s.rr >= 3 ? '#22C55E' : '#4FD1C5';
    document.getElementById('spotZoneType').textContent = s.type || '\u2014';
    document.getElementById('spotTrigger').textContent = s.trigger || '\u2014';

    const bosInfo = st.last_bos ? `Last BOS: ${st.last_bos.dir} at ${fmt(st.last_bos.price, sig.pair.decimals)}` : '';
    const chochInfo = st.last_choch ? `Last CHoCH: ${st.last_choch.dir} at ${fmt(st.last_choch.price, sig.pair.decimals)}` : '';
    document.getElementById('spotStructure').innerHTML = [bosInfo, chochInfo, st.trend_detail].filter(Boolean).join(' &middot; ');

    document.getElementById('spotRationale').innerHTML = `<strong>${sig.pair.label}</strong> &mdash; ${s.direction.toUpperCase()} setup via ${s.type} ${s.trigger || ''}. Entry: ${fmt(s.entry, sig.pair.decimals)} | SL: ${fmt(s.sl, sig.pair.decimals)} | TP1: ${fmt(s.tp1, sig.pair.decimals)} | TP2: ${fmt(s.tp2, sig.pair.decimals)}. Setup stays active until TP or SL is hit.`;
  } else {
    document.getElementById('spotStatus').textContent = 'No Setup';
    document.getElementById('spotStatus').style.color = '#5B6472';
    document.getElementById('spotDir').textContent = '\u2014';
    document.getElementById('spotDir').style.color = 'var(--ink-dim)';
    document.getElementById('spotEntry').textContent = '\u2014';
    document.getElementById('spotSL').textContent = '\u2014';
    document.getElementById('spotTP1').textContent = '\u2014';
    document.getElementById('spotTP2').textContent = '\u2014';
    document.getElementById('spotRR').textContent = '\u2014';
    document.getElementById('spotRR').style.color = '';
    document.getElementById('spotZoneType').textContent = '\u2014';
    document.getElementById('spotTrigger').textContent = '\u2014';

    const bosInfo = st.last_bos ? `Last BOS: ${st.last_bos.dir} at ${fmt(st.last_bos.price, sig.pair.decimals)}` : 'No BOS detected';
    const chochInfo = st.last_choch ? `Last CHoCH: ${st.last_choch.dir} at ${fmt(st.last_choch.price, sig.pair.decimals)}` : '';
    document.getElementById('spotStructure').innerHTML = [bosInfo, chochInfo, st.trend_detail].filter(Boolean).join(' &middot; ');

    document.getElementById('spotRationale').innerHTML = `<strong>${sig.pair.label}</strong> &mdash; No active SMC setup. Trend: ${st.trend || 'neutral'}. Daily bias: ${st.daily_bias || 'neutral'}. RSI: ${st.rsi || 50}. The engine is monitoring for BOS/CHoCH events, Order Blocks, and Fair Value Gaps that meet the 1:2 R:R minimum.`;
  }

  drawPriceLevels(document.getElementById('ladderSvg'), sig, 640, 420);
  if (sig.pair.tv !== currentTVSymbol) { currentTVSymbol = sig.pair.tv; loadTVChart(currentTVSymbol); }
}

/* ---------- TradingView widgets ---------- */
function loadTVChart(symbol) {
  const el = document.getElementById('tvChartContainer');
  if (!el) return;
  const lbl = document.getElementById('tvChartLabel');
  if (lbl) lbl.textContent = symbol;
  el.innerHTML = '';
  const widgetDiv = document.createElement('div');
  widgetDiv.className = 'tradingview-widget-container__widget';
  el.appendChild(widgetDiv);
  const config = {
    autosize: true, symbol, interval: currentTVInterval,
    timezone: selectedTimezone.tv, theme: 'dark', style: '1', locale: 'en',
    backgroundColor: 'rgba(16,22,31,1)', gridColor: 'rgba(35,43,56,0.5)',
    hide_top_toolbar: false, hide_legend: false, save_image: false,
    allow_symbol_change: true, calendar: false,
    studies: ['STD;RSI', 'STD;Volume'],
    support_host: 'https://www.tradingview.com'
  };
  const sc = document.createElement('script');
  sc.type = 'text/javascript';
  sc.async = true;
  sc.src = 'https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js';
  sc.textContent = JSON.stringify(config);
  el.appendChild(sc);
}
function loadTVTicker() {
  const el = document.getElementById('tvTickerContainer');
  if (!el) return;
  el.innerHTML = '';
  const widgetDiv = document.createElement('div');
  widgetDiv.className = 'tradingview-widget-container__widget';
  el.appendChild(widgetDiv);
  const symbols = FX_PAIRS.map(p => ({ proName: p.tv, title: p.code.slice(0,3)+'/'+p.code.slice(3) }));
  const config = { symbols, showSymbolLogo: true, isTransparent: true, displayMode: 'adaptive', colorTheme: 'dark', locale: 'en' };
  const sc = document.createElement('script');
  sc.type = 'text/javascript';
  sc.async = true;
  sc.src = 'https://s3.tradingview.com/external-embedding/embed-widget-ticker-tape.js';
  sc.textContent = JSON.stringify(config);
  el.appendChild(sc);
}

/* ---------- market overview ---------- */
const MO_PAIRS = ['EURUSD','GBPUSD','USDJPY','XAUUSD','USDCHF','AUDUSD'];
function renderMarketOverview() {
  const el = document.getElementById('marketOverviewGrid');
  if (!el) return;
  if (!signals.length) { el.innerHTML = '<div class="market-overview__loading">Waiting for market data scan&hellip;</div>'; return; }
  const rows = MO_PAIRS.map(code => {
    const sig = signals.find(s => s.pair.code === code);
    if (!sig) return '';
    const price = livePrice(code) || sig.currentPrice;
    const hasSetup = !!sig.setup;
    const trend = sig.structure?.trend || 'neutral';
    const trendColor = trend === 'bullish' ? '#22C55E' : trend === 'bearish' ? '#E8664A' : '#5B6472';
    const trendIcon = trend === 'bullish' ? '\u25B2' : trend === 'bearish' ? '\u25BC' : '\u25CF';

    let dirLabel, dirClass;
    if (hasSetup) {
      dirLabel = sig.setup.direction === 'buy' ? 'BUY' : 'SELL';
      dirClass = sig.setup.direction === 'buy' ? 'buy' : 'sell';
    } else {
      dirLabel = 'Watching';
      dirClass = 'wait';
    }

    return `<div class="mo-pair" data-pair="${code}">
      <div>
        <div class="mo-pair__name">${sig.pair.label}</div>
        <div class="mo-pair__meta">
          <span class="mo-pair__signal ${dirClass}">${hasSetup ? (sig.setup.direction === 'buy' ? '\u25B2' : '\u25BC') : '\u25CF'} ${dirLabel}</span>
          <span class="mo-pair__bias" style="color:${trendColor}">${trendIcon} ${trend}</span>
        </div>
      </div>
      <div class="mo-pair__right">
        <div class="mo-pair__price" data-live-price-mo="${code}">${fmt(price, sig.pair.decimals)}</div>
      </div>
    </div>`;
  }).filter(Boolean).join('');
  el.innerHTML = rows || '<div class="market-overview__loading">No data available</div>';
  el.querySelectorAll('.mo-pair').forEach(row => {
    row.addEventListener('click', () => {
      const code = row.dataset.pair;
      if (signals.find(s => s.pair.code === code)) {
        selectedId = code; renderAll();
        document.getElementById('spotlight').scrollIntoView({ behavior:'smooth', block:'start' });
      }
    });
  });
}

let _pythonStrengthRendered = false;
function renderAll() { renderWeekendBanner(); renderGrid(); renderSpotlight(); renderMarketOverview(); }

function renderWeekendBanner() {
  const el = document.getElementById('dataStatus');
  const tab = document.getElementById('tab-paused');
  // Weekend/paused status belongs to the Live Signal Grid — locked users
  // don't get any of it, same as the filters and cards.
  if (window._accessLevel === 'limited') { if (el) el.innerHTML = ''; return; }
  if (isWeekend) {
    if (el) el.innerHTML = '<div class="status-banner info" style="background:rgba(212,162,76,.12);border:1px solid var(--gold);color:var(--gold);">⏸ Forex &amp; Commodities paused for the weekend — Crypto pairs remain active 24/7. Trading resumes Sunday 22:00 UTC.</div>';
    if (tab) tab.style.display = '';
  } else {
    if (tab) tab.style.display = 'none';
    if (tab && filterMode === 'paused') { filterMode = 'all'; document.getElementById('tab-all')?.setAttribute('aria-selected','true'); document.getElementById('tab-paused')?.setAttribute('aria-selected','false'); }
  }
}

/* ---------- clock ---------- */
function tickClock() {
  const str = fmtClock(selectedTimezone.zone) + ' ' + selectedTimezone.short;
  ['utcClock','footClock'].forEach(id => { const el = document.getElementById(id); if (el) el.textContent = str; });
  if (lastScanTime) {
    const el = document.getElementById('lastUpdated');
    const ago = Math.round((Date.now() - lastScanTime) / 1000);
    const when = ago < 60 ? `${ago}s ago` : `${Math.round(ago/60)}m ago`;
    if (el) el.innerHTML = `<span class="dot" aria-hidden="true"></span>Last updated: ${fmtClock(selectedTimezone.zone)} ${selectedTimezone.short} (${when})`;
  }
}

/* ---------- init ---------- */
function initTimezoneSelect() {
  const sel = document.getElementById('timezoneSelect');
  if (!sel) return;
  sel.innerHTML = TIMEZONES.map((tz, i) => `<option value="${i}"${i===0?' selected':''}>${tz.label}</option>`).join('');
  sel.addEventListener('change', () => {
    selectedTimezone = TIMEZONES[+sel.value] || TIMEZONES[0];
    tickClock(); renderAll(); loadTVChart(currentTVSymbol);
  });
}
function initMobileMenu() {
  const btn  = document.getElementById('hamburgerBtn');
  const nav  = document.getElementById('mobileNav');
  const cls  = document.getElementById('mobileNavClose');
  if (!btn || !nav) return;
  const open  = () => { nav.classList.add('open');    btn.setAttribute('aria-expanded','true');  document.body.style.overflow = 'hidden'; };
  const close = () => { nav.classList.remove('open'); btn.setAttribute('aria-expanded','false'); document.body.style.overflow = '';       };
  btn.addEventListener('click', open);
  cls?.addEventListener('click', close);
  nav.querySelectorAll('a').forEach(a => a.addEventListener('click', close));
  nav.addEventListener('click', e => { if (e.target === nav) close(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && nav.classList.contains('open')) close(); });
}
function triggerRescan() {
  const btns = [document.getElementById('rescanBtn'), document.getElementById('dashRescanBtn')].filter(Boolean);
  if (btns.length && btns[0].disabled) return;
  btns.forEach(btn => { btn.disabled = true; btn.textContent = 'Scanning\u2026'; });
  _forceRescan = true;
  rescanAll().finally(() => {
    let remaining = 60;
    btns.forEach(btn => { btn.textContent = `Re-scan (${remaining}s)`; });
    const iv = setInterval(() => {
      remaining--;
      if (remaining <= 0) { 
        clearInterval(iv); 
        btns.forEach(btn => { btn.disabled = false; btn.textContent = 'Re-scan markets'; });
      } else { 
        btns.forEach(btn => { btn.textContent = `Re-scan (${remaining}s)`; });
      }
    }, 1000);
  });
}

/* filter/sort/interval controls */
document.getElementById('filterSeg').addEventListener('click', e => {
  const b = e.target.closest('button'); if (!b) return;
  document.querySelectorAll('#filterSeg button').forEach(x => { x.classList.remove('active'); x.setAttribute('aria-selected','false'); });
  b.classList.add('active'); b.setAttribute('aria-selected','true');
  filterMode = b.dataset.filter; renderGrid(); renderSpotlight(); renderMarketOverview();
});
document.getElementById('assetSeg').addEventListener('click', e => {
  const b = e.target.closest('button'); if (!b) return;
  document.querySelectorAll('#assetSeg button').forEach(x => { x.classList.remove('active'); x.setAttribute('aria-selected','false'); });
  b.classList.add('active'); b.setAttribute('aria-selected','true');
  assetMode = b.dataset.asset; renderGrid(); renderSpotlight(); renderMarketOverview();
});
document.getElementById('sortSeg').addEventListener('click', e => {
  const b = e.target.closest('button'); if (!b) return;
  document.querySelectorAll('#sortSeg button').forEach(x => { x.classList.remove('active'); x.setAttribute('aria-pressed','false'); });
  b.classList.add('active'); b.setAttribute('aria-pressed','true');
  sortMode = b.dataset.sort; renderGrid();
});
document.getElementById('chartIntervalSeg').addEventListener('click', e => {
  const b = e.target.closest('button'); if (!b) return;
  document.querySelectorAll('#chartIntervalSeg button').forEach(x => x.classList.remove('active'));
  b.classList.add('active'); currentTVInterval = b.dataset.interval; loadTVChart(currentTVSymbol);
});
document.getElementById('rescanBtn').addEventListener('click', triggerRescan);

/* about modal */
const aboutModal = document.getElementById('aboutModal');
const aboutBtn   = document.getElementById('aboutBtn');
const aboutClose = document.getElementById('aboutClose');
aboutBtn.addEventListener('click',  () => { aboutModal.hidden = false; aboutClose.focus(); });
aboutClose.addEventListener('click', () => { aboutModal.hidden = true;  aboutBtn.focus(); });
aboutModal.addEventListener('click', e => { if (e.target === aboutModal) { aboutModal.hidden = true; aboutBtn.focus(); } });

/* news impact filter */
document.querySelectorAll('[data-impact]').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('[data-impact]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active'); newsFilter = btn.dataset.impact; renderNews();
  });
});

/* keyboard shortcut R */
document.addEventListener('keydown', e => {
  if (e.key === 'Escape' && !aboutModal.hidden) { aboutModal.hidden = true; aboutBtn.focus(); return; }
  if (isTyping(e.target) || e.metaKey || e.ctrlKey || e.altKey) return;
  if (e.key === 'r' || e.key === 'R') { e.preventDefault(); triggerRescan(); }
});

/* boot */
initTimezoneSelect();
initMobileMenu();
renderGrid();
rescanAll();
startPricePolling();
startStrengthPolling();
loadTVTicker();
loadTVChart(currentTVSymbol);
fetchNews();
tickClock();
setInterval(tickClock,      1000);

/* auto-poll each minute boundary */
(function schedulePoll() {
  const delay = Math.max(15000, POLL_MS - (Date.now() % POLL_MS));
  setTimeout(() => rescanAll().finally(schedulePoll), delay);
})();
</script>

<!-- Profile modal -->
<script>
var _profCountrySel, _profModal, _profAlert, _profSaveBtn, _profUserId = null;

function openProfileModal() {
  if(!_profModal) {
    _profModal = document.getElementById('profileModal');
    _profAlert = document.getElementById('profileAlert');
    _profSaveBtn = document.getElementById('profileSaveBtn');
    _profCountrySel = document.getElementById('profCountry');
    _initProfCountry();
    document.getElementById('profileModalClose').addEventListener('click', closeProfileModal);
    _profModal.addEventListener('click', function(e){ if(e.target===_profModal) closeProfileModal(); });
    document.getElementById('profileForm').addEventListener('submit', _saveProfile);
  }
  _profModal.style.display = 'flex';
  _profModal.classList.add('open');
  document.body.style.overflow='hidden';
  _profAlert.hidden = true;
  _loadProfile();
}

function closeProfileModal() {
  _profModal.style.display = 'none';
  _profModal.classList.remove('open');
  document.body.style.overflow='';
  _profAlert.hidden = true;
}

document.addEventListener('keydown', function(e){
  if(e.key==='Escape' && _profModal && _profModal.classList.contains('open')) closeProfileModal();
});

function _initProfCountry() {
  var countries = [
    {d:'+254',n:'Kenya'},{d:'+234',n:'Nigeria'},{d:'+1',n:'United States'},
    {d:'+44',n:'United Kingdom'},{d:'+233',n:'Ghana'},{d:'+27',n:'South Africa'},
    {d:'+255',n:'Tanzania'},{d:'+256',n:'Uganda'},{d:'+20',n:'Egypt'},
    {d:'+212',n:'Morocco'},{d:'+251',n:'Ethiopia'},{d:'+237',n:'Cameroon'},
    {d:'+221',n:'Senegal'},{d:'+225',n:'Ivory Coast'},{d:'+213',n:'Algeria'},
    {d:'+216',n:'Tunisia'},{d:'+243',n:'DR Congo'},{d:'+258',n:'Mozambique'},
    {d:'+260',n:'Zambia'},{d:'+263',n:'Zimbabwe'},{d:'+250',n:'Rwanda'},
    {d:'+267',n:'Botswana'},{d:'+230',n:'Mauritius'},{d:'+261',n:'Madagascar'},
    {d:'+244',n:'Angola'},{d:'+61',n:'Australia'},{d:'+49',n:'Germany'},
    {d:'+33',n:'France'},{d:'+39',n:'Italy'},{d:'+34',n:'Spain'},
    {d:'+351',n:'Portugal'},{d:'+31',n:'Netherlands'},{d:'+32',n:'Belgium'},
    {d:'+41',n:'Switzerland'},{d:'+46',n:'Sweden'},{d:'+47',n:'Norway'},
    {d:'+45',n:'Denmark'},{d:'+358',n:'Finland'},{d:'+353',n:'Ireland'},
    {d:'+43',n:'Austria'},{d:'+48',n:'Poland'},{d:'+420',n:'Czech Republic'},
    {d:'+40',n:'Romania'},{d:'+36',n:'Hungary'},{d:'+30',n:'Greece'},
    {d:'+90',n:'Turkey'},{d:'+7',n:'Russia'},{d:'+380',n:'Ukraine'},
    {d:'+972',n:'Israel'},{d:'+971',n:'United Arab Emirates'},
    {d:'+966',n:'Saudi Arabia'},{d:'+974',n:'Qatar'},{d:'+965',n:'Kuwait'},
    {d:'+973',n:'Bahrain'},{d:'+968',n:'Oman'},{d:'+962',n:'Jordan'},
    {d:'+961',n:'Lebanon'},{d:'+964',n:'Iraq'},{d:'+91',n:'India'},
    {d:'+92',n:'Pakistan'},{d:'+880',n:'Bangladesh'},{d:'+94',n:'Sri Lanka'},
    {d:'+977',n:'Nepal'},{d:'+63',n:'Philippines'},{d:'+66',n:'Thailand'},
    {d:'+84',n:'Vietnam'},{d:'+60',n:'Malaysia'},{d:'+62',n:'Indonesia'},
    {d:'+65',n:'Singapore'},{d:'+82',n:'South Korea'},{d:'+81',n:'Japan'},
    {d:'+86',n:'China'},{d:'+886',n:'Taiwan'},{d:'+852',n:'Hong Kong'},
    {d:'+52',n:'Mexico'},{d:'+55',n:'Brazil'},{d:'+54',n:'Argentina'},
    {d:'+56',n:'Chile'},{d:'+57',n:'Colombia'},{d:'+51',n:'Peru'},
    {d:'+593',n:'Ecuador'},{d:'+58',n:'Venezuela'},{d:'+506',n:'Costa Rica'},
    {d:'+507',n:'Panama'},{d:'+502',n:'Guatemala'},{d:'+504',n:'Honduras'},
    {d:'+505',n:'Nicaragua'},{d:'+503',n:'El Salvador'},{d:'+53',n:'Cuba'},
    {d:'+1876',n:'Jamaica'},{d:'+1868',n:'Trinidad and Tobago'},{d:'+509',n:'Haiti'},
    {d:'+1809',n:'Dominican Republic'},{d:'+1787',n:'Puerto Rico'},
    {d:'+64',n:'New Zealand'},{d:'+679',n:'Fiji'}
  ];
  var seen = {}, unique = [];
  countries.forEach(function(c){ if(!seen[c.d]){ seen[c.d]=true; unique.push(c); }});
  unique.sort(function(a,b){ return a.n.localeCompare(b.n); });
  var def = document.createElement('option');
  def.value=''; def.textContent='Select country'; def.disabled=true; def.selected=true;
  _profCountrySel.appendChild(def);
  unique.forEach(function(c){
    var o = document.createElement('option');
    o.value = c.d; o.textContent = c.n + ' (' + c.d + ')';
    _profCountrySel.appendChild(o);
  });
}

async function _loadProfile() {
  try {
    if(typeof BMAuth === 'undefined') return;
    var session = await BMAuth.getSession();
    var user = session && session.user;
    if(!user) return;
    _profUserId = user.id;
    document.getElementById('profEmail').value = user.email || '';
    document.getElementById('profUsername').value = (user.user_metadata && user.user_metadata.username) || (user.email ? user.email.split('@')[0] : '');
    document.getElementById('profFirstName').value = (user.user_metadata && user.user_metadata.first_name) || '';
    document.getElementById('profLastName').value = (user.user_metadata && user.user_metadata.last_name) || '';
    document.getElementById('profPhone').value = (user.user_metadata && (user.user_metadata.phone || user.user_metadata.phone_number)) || '';
    if(user.user_metadata && user.user_metadata.country_code){
      _profCountrySel.value = user.user_metadata.country_code;
    } else {
      _profCountrySel.selectedIndex = 0;
    }
  } catch(err) { console.warn('Profile load error:', err); }
}

async function _saveProfile(e) {
  e.preventDefault();
  if(!_profUserId) return;
  var firstName = document.getElementById('profFirstName').value.trim();
  var lastName = document.getElementById('profLastName').value.trim();
  var phone = document.getElementById('profPhone').value.trim();
  var countryCode = _profCountrySel.value;
  if(!firstName||!lastName){ _profAlert.textContent='First and last name are required.'; _profAlert.className='profile-alert error'; _profAlert.hidden=false; return; }

  _profSaveBtn.disabled = true;
  _profSaveBtn.textContent = 'Saving...';

  try {
    var result = await BMAuth.updateProfile(_profUserId, {
      first_name: firstName,
      last_name: lastName,
      country_code: countryCode,
      phone: phone
    });
    // Mirror into user_metadata so session-based readers (e.g. _loadProfile,
    // welcome headers) reflect the change immediately and survive reloads.
    var metaResult = await BMAuth.updateMetadata({
      first_name: firstName,
      last_name: lastName,
      country_code: countryCode,
      phone: phone,
      phone_number: phone
    });
    if(result.error && metaResult.error){
      _profAlert.textContent = 'Failed to save: ' + result.error;
      _profAlert.className = 'profile-alert error';
    } else {
      _profAlert.textContent = 'Profile updated successfully!';
      _profAlert.className = 'profile-alert success';
      document.getElementById('welcomeName').textContent = firstName + ' ' + lastName;
      document.getElementById('displayUsername').textContent = firstName + ' ' + lastName;
    }
    _profAlert.hidden = false;
  } catch(err) {
    _profAlert.textContent = 'Error: ' + err.message;
    _profAlert.className = 'profile-alert error';
    _profAlert.hidden = false;
  }
  _profSaveBtn.disabled = false;
  _profSaveBtn.textContent = 'Save Changes';
}
</script>
<script>
/* ===================================================================
   BM FOREX HUB — Section Switcher
   Controls which view is visible based on sidebar navigation.
   All existing API/engine/signal logic is untouched.
   =================================================================== */

var _bmCurrentSection = 'dashboard';

var _bmSectionMap = {
  'dashboard': 'view-dashboard',
  'market':    'view-market',
  'signals':   'view-signals',
  'strength':  'view-strength',
  'news':      'view-news',
  'methodology': 'view-methodology'
};

function bmShowSection(section) {
  if (!_bmSectionMap[section]) return;

  // Hide all managed views
  Object.values(_bmSectionMap).forEach(function(id) {
    var el = document.getElementById(id);
    if (el) { el.classList.remove('bm-view--active'); }
  });

  // Show selected view
  var target = document.getElementById(_bmSectionMap[section]);
  if (target) { target.classList.add('bm-view--active'); }

  // Update sidebar active state
  document.querySelectorAll('.dash-sidebar__item[data-bm-section]').forEach(function(el) {
    el.classList.remove('active');
    el.removeAttribute('aria-current');
  });
  var activeLink = document.querySelector('.dash-sidebar__item[data-bm-section="' + section + '"]');
  if (activeLink) {
    activeLink.classList.add('active');
    activeLink.setAttribute('aria-current', 'page');
  }

  _bmCurrentSection = section;

  // Update horizontal topbar nav active state
  document.querySelectorAll('.topbar__nav-link').forEach(function(el) {
    el.classList.remove('active');
  });
  var activeTopLink = document.querySelector('.topbar__nav-link[data-section="' + section + '"]');
  if (activeTopLink) { activeTopLink.classList.add('active'); }
  window.scrollTo({ top: 0, behavior: 'smooth' });

  // Lazy-init TradingView ticker on first visit to market view
  if (section === 'market' && typeof loadTVTicker === 'function') {
    loadTVTicker();
  }
}

/* ===================================================================
   Dashboard Summary Widget Renderers
   Populate the mini-panels on the dashboard overview
   =================================================================== */

/* Dashboard: current date + Re-scan Markets button (reuses the real
   Market Overview rescan logic — no duplicate signal engine calls) */
/* Dashboard: current date + Re-scan Markets button */
(function() {
  var dateEl = document.getElementById('dashCurrentDate');
  var dateChipEl = document.getElementById('dashCurrentDateChip');
  var formattedDate = new Date().toLocaleDateString([], { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
  if (dateEl) dateEl.textContent = formattedDate;
  if (dateChipEl) dateChipEl.textContent = formattedDate;
  
  var dashRescan = document.getElementById('dashRescanBtn');
  if (dashRescan) {
    dashRescan.addEventListener('click', function() {
      if (typeof triggerRescan === 'function') triggerRescan();
    });
  }
})();

/* ===================================================================
   BM FOREX HUB — Dynamic Membership & Compact Status Bar Controller
   Loads user membership status and populates status chips & modal
   =================================================================== */
function openSubscriptionQuickModal() {
  var modal = document.getElementById('subQuickModal');
  if (modal) modal.style.display = 'flex';
}
function closeSubscriptionQuickModal() {
  var modal = document.getElementById('subQuickModal');
  if (modal) modal.style.display = 'none';
}

(async function initDashboardStatusChips() {
  try {
    if (typeof BMAuth === 'undefined') return;
    const { user, session } = await BMAuth.getSession();
    if (!user) return;

    const authData = await BMAuth.getMembershipStatus(user, session);
    const hasElite = authData.plans && (authData.plans.includes('all') || authData.plans.some(p => p.startsWith('elite_')));
    const hasTrial = authData.level === 'trial' || authData.trial_active;

    // Elements
    const badgeTextEl = document.getElementById('dashMembershipBadgeText');
    const badgeContainerEl = document.getElementById('dashMembershipBadge');
    const planChipEl = document.getElementById('dashPlanNameChip');
    const expiryChipEl = document.getElementById('dashExpiryBadgeText');
    const expiryContainerEl = document.getElementById('dashExpiryChip');

    const modalStatusEl = document.getElementById('modalSubStatus');
    const modalPlanEl = document.getElementById('modalSubPlan');
    const modalExpiryEl = document.getElementById('modalSubExpiry');

    const planNameMap = {
      'elite_starter': 'BM Elites — $1,000 USD',
      'elite_intermediate': 'BM Elites — $2,000 USD',
      'elite_advanced': 'BM Elites — $3,000 USD',
      'elite_professional': 'BM Elites — $5,000 USD',
      'elite_premium': 'BM Elites — $6,000 USD',
      'elite_elite': 'BM Elites — $10,000 USD',
      'all': 'BM Elites — All Access Pass'
    };

    let activePlanName = authData.subscription_plan || (authData.plans && authData.plans[0]) || '';
    activePlanName = planNameMap[activePlanName] || activePlanName;

    if (hasElite) {
      if (badgeTextEl) badgeTextEl.textContent = '👑 Elite Membership Active';
      if (badgeContainerEl) {
        badgeContainerEl.style.background = 'linear-gradient(135deg, rgba(240, 180, 41, 0.15) 0%, rgba(22, 119, 255, 0.15) 100%)';
        badgeContainerEl.style.borderColor = 'rgba(240, 180, 41, 0.4)';
        badgeContainerEl.style.color = '#F0B429';
      }
      if (planChipEl) planChipEl.textContent = 'Plan: ' + (activePlanName || 'BM Elites Trading Circle');
      if (expiryChipEl) expiryChipEl.textContent = authData.subscription_expiry ? 'Expires: ' + new Date(authData.subscription_expiry).toLocaleDateString('en-GB', {day:'numeric',month:'short',year:'numeric'}) : 'Expires: Lifetime VIP';

      if (modalStatusEl) modalStatusEl.textContent = 'Active Premium';
      if (modalPlanEl) modalPlanEl.textContent = activePlanName || 'BM Elites Trading Circle';
      if (modalExpiryEl) modalExpiryEl.textContent = authData.subscription_expiry ? new Date(authData.subscription_expiry).toLocaleDateString('en-GB', {day:'numeric',month:'short',year:'numeric'}) : 'Lifetime VIP';

      // Check if active Elite member requires compliance terms acceptance
      try {
        const checkResp = await fetch('api/elite-enrollment.php?action=check', {
          headers: { 'Authorization': 'Bearer ' + (session ? session.access_token : '') }
        });
        if (checkResp.ok) {
          const checkData = await checkResp.json();
          const complianceBanner = document.getElementById('dashEliteComplianceBanner');
          if (complianceBanner && checkData.ok && checkData.requires_consent) {
            complianceBanner.style.display = 'block';
          }
        }
      } catch(e) {}

    } else if (hasTrial) {
      if (badgeTextEl) badgeTextEl.textContent = '⏱️ Free Trial Active (' + (window._trialDaysLeft || 0) + ' Days Left)';
      if (badgeContainerEl) {
        badgeContainerEl.style.background = 'rgba(22, 119, 255, 0.1)';
        badgeContainerEl.style.borderColor = 'rgba(22, 119, 255, 0.3)';
        badgeContainerEl.style.color = '#5b9eff';
      }
      if (planChipEl) planChipEl.textContent = 'Plan: Free Trial';
      if (expiryChipEl) expiryChipEl.textContent = 'Trial Ends Soon';

      if (modalStatusEl) modalStatusEl.textContent = 'Free Trial Active';
      if (modalPlanEl) modalPlanEl.textContent = '3-Day Free Signal Trial';
      if (modalExpiryEl) modalExpiryEl.textContent = (window._trialDaysLeft || 0) + ' Days Remaining';

    } else {
      if (badgeTextEl) badgeTextEl.textContent = '⚠️ Subscription Required';
      if (badgeContainerEl) {
        badgeContainerEl.style.background = 'rgba(246, 70, 93, 0.1)';
        badgeContainerEl.style.borderColor = 'rgba(246, 70, 93, 0.3)';
        badgeContainerEl.style.color = '#f6465d';
      }
      if (planChipEl) planChipEl.textContent = 'No Active Subscription';
      if (expiryChipEl) expiryChipEl.textContent = 'Subscription Expired';
      if (expiryContainerEl) {
        expiryContainerEl.style.background = 'rgba(246, 70, 93, 0.1)';
        expiryContainerEl.style.borderColor = 'rgba(246, 70, 93, 0.3)';
        expiryContainerEl.style.color = '#f6465d';
      }

      if (modalStatusEl) { modalStatusEl.textContent = 'No Active Subscription'; modalStatusEl.style.color = '#f6465d'; }
      if (modalPlanEl) modalPlanEl.textContent = 'None Selected';
      if (modalExpiryEl) { modalExpiryEl.textContent = 'Expired'; modalExpiryEl.style.color = '#f6465d'; }
    }
  } catch (err) {
    console.warn('Status chips init error:', err);
  }
})();
</script>

<!-- ===== Compact Subscription Details Quick Modal ===== -->
<div id="subQuickModal" class="about-modal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.7); align-items: center; justify-content: center; backdrop-filter: blur(4px);">
  <div style="background: #151d2a; border: 1px solid #1e2d42; border-radius: 14px; width: 90%; max-width: 440px; padding: 24px; color: #fff; position: relative; box-shadow: 0 16px 36px rgba(0,0,0,0.5);">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #1e2d42;">
      <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 1.1rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #F0B429;">&#128081;</span> Subscription Details
      </h3>
      <button onclick="closeSubscriptionQuickModal()" style="background: transparent; border: none; color: #8fa3b8; font-size: 1.4rem; cursor: pointer; padding: 4px; line-height: 1;">&times;</button>
    </div>

    <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.85rem; margin-bottom: 20px;">
      <div style="display: flex; justify-content: space-between; padding: 8px 12px; background: #101722; border-radius: 8px;">
        <span style="color: #8fa3b8;">Membership Status:</span>
        <strong style="color: #22C55E;" id="modalSubStatus">Active Premium</strong>
      </div>
      <div style="display: flex; justify-content: space-between; padding: 8px 12px; background: #101722; border-radius: 8px;">
        <span style="color: #8fa3b8;">Active Plan:</span>
        <strong style="color: #fff;" id="modalSubPlan">BM Elites Trading Circle</strong>
      </div>
      <div style="display: flex; justify-content: space-between; padding: 8px 12px; background: #101722; border-radius: 8px;">
        <span style="color: #8fa3b8;">Renewal / Expiry Date:</span>
        <strong style="color: #F0B429;" id="modalSubExpiry">Lifetime Access</strong>
      </div>
      <div style="padding: 10px 12px; background: rgba(22,119,255,0.06); border: 1px solid rgba(22,119,255,0.15); border-radius: 8px; font-size: 0.8rem; color: #8fa3b8;">
        <strong style="color: #5b9eff; display: block; margin-bottom: 4px;">Included Benefits:</strong>
        Real-time Forex & Crypto Signals, VIP WhatsApp Circle, 5% Weekly Investment Target, & Weekly Live Masterclasses.
      </div>
    </div>

    <div style="display: flex; gap: 10px; justify-content: flex-end;">
      <a href="bm_elites.php" style="background: rgba(22,119,255,0.15); border: 1px solid rgba(22,119,255,0.3); color: #1677FF; text-decoration: none; font-size: 0.82rem; font-weight: 600; padding: 8px 14px; border-radius: 6px;">
        Full Membership Page
      </a>
      <a href="subscribe.php" style="background: #1677FF; color: #fff; text-decoration: none; font-size: 0.82rem; font-weight: 600; padding: 8px 14px; border-radius: 6px;">
        Manage Subscription
      </a>
    </div>
  </div>
</div>

<script src="js/motion.js" defer></script>
<script src="js/ai-assistant.js" defer></script>
</div><!-- /.dash-main-wrap -->
<?php $activeTab = 'dashboard'; include __DIR__ . '/components/mobile-tabbar.php'; ?>
</body>
</html>