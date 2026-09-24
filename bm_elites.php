<?php 
require_once __DIR__ . '/engine_config.php';
require_once __DIR__ . '/app/Services/MembershipService.php';

$membership = new \App\Services\MembershipService();
$elitePlans = $membership->getElitesPlans();
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
<title>BM FOREX HUB | BM Elites Trading Circle</title>
<meta name="description" content="BM Elites Trading Circle is an exclusive managed investment model. Review the official Elite Circle Terms and proceed with electronic enrollment.">
<link rel="icon" type="image/png" href="BM-ForexHub-Logo-Circle.png">
<meta name="robots" content="noindex, nofollow, noarchive">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap">
<link rel="stylesheet" href="basics.css?v=<?= filemtime('basics.css') ?>">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="css/mobile.css?v=<?= filemtime('css/mobile.css') ?>">

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
}

/* ===== TOPBAR STYLE OVERRIDES ===== */
.topbar { background: #101722; border-bottom: 1px solid #1e2d42; }
.topbar .logo--text span { font-size: 0.85rem; font-weight: 700; letter-spacing: 0.06em; color: #fff; }
.topbar__user-info {
  display: flex; align-items: center; gap: 10px; color: #fff; font-size: 0.83rem;
}
.topbar__user-avatar {
  width: 34px; height: 34px; background: #1e2d42; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  border: 1px solid rgba(22,119,255,0.35); flex-shrink: 0;
  font-size: 0.95rem; color: #8fa3b8; overflow: hidden;
}
.topbar__user-greeting { line-height: 1.2; }
.topbar__user-greeting small { display: block; color: #8fa3b8; font-size: 0.7rem; }
.topbar__user-greeting strong { font-weight: 600; }

/* ===== DASHBOARD OVERVIEW ===== */
.dash-overview {
  padding: 28px 30px 20px;
  max-width: 1140px;
  margin: 0 auto;
}
.dash-overview__title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.65rem; font-weight: 700; color: #fff; margin: 0 0 5px;
}
.dash-overview__subtitle { color: #8fa3b8; font-size: 0.88rem; margin: 0; line-height: 1.55; }

/* ===== ELITE CIRCLE TERMS GATE & CONTAINER ===== */
.elite-terms-gate {
  max-width: 920px;
  margin: 0 auto;
}
.elite-terms-container {
  background: #101722;
  border: 1px solid #1E2D42;
  border-radius: 16px;
  overflow: hidden;
  margin-bottom: 28px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
}
.elite-terms-sections-wrap {
  padding: 24px 28px 12px;
  max-height: 540px;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: #283548 #0B0F14;
}
.elite-terms-sections-wrap::-webkit-scrollbar { width: 8px; }
.elite-terms-sections-wrap::-webkit-scrollbar-track { background: #0B0F14; }
.elite-terms-sections-wrap::-webkit-scrollbar-thumb { background: #283548; border-radius: 4px; }
.elite-terms-sections-wrap::-webkit-scrollbar-thumb:hover { background: #1677FF; }

/* Legal Header Box */
.legal-header-box {
  background: linear-gradient(180deg, #151D29 0%, #101722 100%);
  border: 1px solid #283548;
  border-radius: 14px;
  padding: 26px 24px;
  margin-bottom: 24px;
  position: relative;
  overflow: hidden;
}
.legal-header-box::before {
  content: '';
  position: absolute;
  top: 0; left: 10%; right: 10%; height: 2px;
  background: linear-gradient(90deg, transparent, #1677FF, #F0B429, #1677FF, transparent);
}
.legal-header-box h1 {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: #fff;
  margin: 8px 0 6px;
}
.legal-header-desc {
  color: #B8C3D1;
  font-size: 0.88rem;
  line-height: 1.6;
}
.badge-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  border-radius: 20px;
  background: rgba(240, 180, 41, 0.12);
  border: 1px solid rgba(240, 180, 41, 0.3);
  color: #F0B429;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}
.meta-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 10px 16px;
  margin-top: 16px;
  padding-top: 14px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 0.8rem;
  color: #7F8B99;
}
.meta-item strong { color: #fff; font-family: 'IBM Plex Mono', monospace; }

/* Callout Box */
.callout-box {
  background: rgba(246, 70, 93, 0.08);
  border: 1px solid rgba(246, 70, 93, 0.3);
  border-left: 4px solid #F6465D;
  border-radius: 10px;
  padding: 16px 20px;
  margin-bottom: 24px;
  font-size: 0.84rem;
  color: #D8E2ED;
  line-height: 1.6;
}
.callout-box strong { color: #F6465D; }

/* Table of Contents */
.toc-card {
  background: #0E141E;
  border: 1px solid #283548;
  border-radius: 12px;
  padding: 18px 22px;
  margin-bottom: 24px;
}
.toc-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 0.88rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 12px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.toc-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 6px 12px;
  font-size: 0.78rem;
}
.toc-grid a {
  color: #B8C3D1;
  text-decoration: none;
  padding: 2px 0;
  transition: color 0.15s;
}
.toc-grid a:hover { color: #1677FF; }

/* Legal Sections */
.legal-section {
  background: #151D29;
  border: 1px solid #283548;
  border-radius: 12px;
  padding: 20px 24px;
  margin-bottom: 16px;
}
.legal-section h2 {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 0.98rem;
  font-weight: 700;
  color: #F0B429;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.legal-section p {
  color: #B8C3D1;
  font-size: 0.86rem;
  line-height: 1.7;
  margin-bottom: 10px;
}
.legal-section p:last-child { margin-bottom: 0; }
.legal-section strong { color: #fff; }
.legal-section ul {
  color: #B8C3D1;
  margin: 0 0 10px 20px;
  font-size: 0.86rem;
}
.legal-section li { margin-bottom: 4px; }

/* Terms Acceptance Card */
.terms-acceptance-card {
  background: linear-gradient(180deg, #151D29 0%, #101722 100%);
  border: 1px solid rgba(22, 119, 255, 0.35);
  border-radius: 14px;
  padding: 24px 28px;
  margin-bottom: 32px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}
.elite-terms-accept-label {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  cursor: pointer;
  font-size: 0.92rem;
  color: #FFFFFF;
  line-height: 1.55;
  font-weight: 600;
  user-select: none;
}
.elite-terms-accept-label input[type="checkbox"] {
  width: 22px;
  height: 22px;
  min-width: 22px;
  margin-top: 2px;
  cursor: pointer;
  accent-color: #1677FF;
  border-radius: 4px;
}

/* Validation message */
.elite-terms-validation-msg {
  display: none;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  border-radius: 8px;
  background: rgba(246, 70, 93, 0.1);
  border: 1px solid rgba(246, 70, 93, 0.3);
  color: #F6465D;
  font-size: 0.84rem;
  margin-bottom: 16px;
}
.elite-terms-validation-msg.show {
  display: flex;
}

/* CTA Row & Button */
.terms-cta-row {
  margin-top: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}
.btn-enroll-proceed {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 16px 36px;
  border: none;
  border-radius: 10px;
  background: linear-gradient(135deg, #1677FF 0%, #0D47A1 100%);
  color: #ffffff;
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.05rem;
  font-weight: 700;
  text-decoration: none;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
  box-shadow: 0 8px 24px rgba(22, 119, 255, 0.35);
  max-width: 100%;
}
.btn-enroll-proceed:not(:disabled):hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(22, 119, 255, 0.5);
  color: #ffffff;
}
.btn-enroll-proceed:disabled {
  opacity: 0.42;
  cursor: not-allowed;
  box-shadow: none;
  transform: none;
}
.terms-step-hint {
  font-size: 0.78rem;
  color: #7F8B99;
  text-align: center;
}

/* Key Highlights Feature Cards */
.live-class-card {
  background: #151d2a;
  border: 1px solid #1e2d42;
  border-radius: 14px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  transition: transform 0.2s, border-color 0.2s;
}
.live-class-card:hover {
  border-color: rgba(22, 119, 255, 0.4);
  transform: translateY(-2px);
}

/* ===== BM ELITES ACTIVE SUBSCRIBER COUNTDOWN EXPERIENCE ===== */
.elite-hero-card {
  background: radial-gradient(120% 120% at 50% 0%, rgba(22, 119, 255, 0.16) 0%, #101722 75%), #101722;
  border: 1px solid rgba(22, 119, 255, 0.35);
  border-radius: 20px;
  padding: 40px 32px 36px;
  text-align: center;
  position: relative;
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.4), 0 0 32px rgba(22, 119, 255, 0.12);
  margin-bottom: 36px;
  overflow: hidden;
}
.elite-hero-card::before {
  content: '';
  position: absolute;
  top: 0; left: 15%; right: 15%; height: 2px;
  background: linear-gradient(90deg, transparent, #1677FF, #16C784, #1677FF, transparent);
}
.elite-badge-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 16px;
  border-radius: 30px;
  background: rgba(240, 180, 41, 0.12);
  border: 1px solid rgba(240, 180, 41, 0.35);
  color: #F0B429;
  font-size: 0.76rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-bottom: 16px;
}
.elite-badge-pill.expired {
  background: rgba(246, 70, 93, 0.12);
  border-color: rgba(246, 70, 93, 0.35);
  color: #F6465D;
}
.elite-hero-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(1.6rem, 3.2vw, 2.3rem);
  font-weight: 700;
  color: #FFFFFF;
  line-height: 1.25;
  margin: 0 0 10px;
}
.elite-hero-subtitle {
  color: #8FA3B8;
  font-size: 0.95rem;
  max-width: 580px;
  margin: 0 auto 28px;
  line-height: 1.55;
}
.elite-countdown-grid {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin: 0 auto 30px;
  max-width: 680px;
  flex-wrap: nowrap;
}
.elite-countdown-block {
  flex: 1;
  min-width: 90px;
  max-width: 135px;
  background: #151D2A;
  border: 1px solid #283548;
  border-top: 2px solid #1677FF;
  border-radius: 14px;
  padding: 20px 10px 14px;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.05);
  transition: transform 0.2s, border-color 0.2s;
}
.elite-countdown-block:hover {
  transform: translateY(-2px);
  border-top-color: #16C784;
}
.elite-countdown-num {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(2rem, 4vw, 2.75rem);
  font-weight: 700;
  color: #FFFFFF;
  line-height: 1;
  margin-bottom: 6px;
  letter-spacing: -0.02em;
}
.elite-countdown-label {
  font-family: 'Inter', sans-serif;
  font-size: 0.68rem;
  font-weight: 700;
  color: #8FA3B8;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}
.elite-meta-box {
  background: rgba(16, 23, 34, 0.85);
  border: 1px solid #1E2D42;
  border-radius: 12px;
  padding: 14px 22px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 24px;
  margin-bottom: 28px;
  font-size: 0.85rem;
  color: #B8C3D1;
  flex-wrap: wrap;
}
.elite-meta-item { display: flex; align-items: center; gap: 6px; }
.elite-meta-item strong { color: #FFFFFF; }
.elite-cta-group {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  flex-wrap: wrap;
}
.btn-upgrade-elites {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 14px 28px;
  background: linear-gradient(135deg, #1677FF 0%, #0D47A1 100%);
  color: #FFFFFF;
  font-family: 'Space Grotesk', sans-serif;
  font-size: 0.95rem;
  font-weight: 700;
  border-radius: 10px;
  text-decoration: none;
  border: none;
  box-shadow: 0 8px 24px rgba(22, 119, 255, 0.3);
  transition: transform 0.2s, box-shadow 0.2s;
  cursor: pointer;
}
.btn-upgrade-elites:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(22, 119, 255, 0.45);
}
.btn-vip-whatsapp {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 14px 26px;
  background: rgba(22, 199, 132, 0.12);
  border: 1px solid rgba(22, 199, 132, 0.35);
  color: #16C784;
  font-family: 'Inter', sans-serif;
  font-size: 0.92rem;
  font-weight: 600;
  border-radius: 10px;
  text-decoration: none;
  transition: background 0.2s, border-color 0.2s, transform 0.2s;
}
.btn-vip-whatsapp:hover {
  background: #16C784;
  color: #101722;
  transform: translateY(-2px);
}

/* Light theme overrides */
:root[data-theme="light"] .elite-terms-container {
  background: #FFFFFF;
  border-color: #E2E8F0;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}
:root[data-theme="light"] .legal-header-box {
  background: #F8FAFC;
  border-color: #E2E8F0;
}
:root[data-theme="light"] .legal-header-box h1 { color: #0F172A; }
:root[data-theme="light"] .legal-header-desc { color: #475569; }
:root[data-theme="light"] .meta-grid { border-top-color: #E2E8F0; color: #64748B; }
:root[data-theme="light"] .meta-item strong { color: #0F172A; }
:root[data-theme="light"] .toc-card { background: #F8FAFC; border-color: #E2E8F0; }
:root[data-theme="light"] .toc-title { color: #0F172A; }
:root[data-theme="light"] .toc-grid a { color: #475569; }
:root[data-theme="light"] .toc-grid a:hover { color: #1677FF; }
:root[data-theme="light"] .legal-section { background: #F8FAFC; border-color: #E2E8F0; }
:root[data-theme="light"] .legal-section h2 { color: #B45309; }
:root[data-theme="light"] .legal-section p { color: #334155; }
:root[data-theme="light"] .legal-section strong { color: #0F172A; }
:root[data-theme="light"] .terms-acceptance-card {
  background: #F8FAFC;
  border-color: rgba(22, 119, 255, 0.3);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
}
:root[data-theme="light"] .elite-terms-accept-label { color: #0F172A; }
:root[data-theme="light"] .elite-terms-sections-wrap {
  scrollbar-color: #CBD5E1 #F1F5F9;
}
:root[data-theme="light"] .elite-terms-sections-wrap::-webkit-scrollbar-track { background: #F1F5F9; }
:root[data-theme="light"] .elite-terms-sections-wrap::-webkit-scrollbar-thumb { background: #CBD5E1; }
:root[data-theme="light"] .live-class-card { background: #FFFFFF; border-color: #E2E8F0; }
:root[data-theme="light"] .dash-overview__title { color: #0F172A; }
:root[data-theme="light"] .dash-overview__subtitle { color: #64748B; }

/* Responsive adjustments */
@media (max-width: 768px) {
  .dash-overview { padding: 20px 16px; }
  .elite-terms-sections-wrap { padding: 18px 16px 8px; max-height: 420px; }
  .legal-header-box { padding: 20px 16px; }
  .legal-section { padding: 16px; }
  .terms-acceptance-card { padding: 18px 16px; }
  .btn-enroll-proceed { width: 100%; font-size: 0.95rem; padding: 14px 20px; }
  .elite-countdown-grid { gap: 8px; }
  .elite-countdown-block { min-width: 68px; padding: 14px 6px 10px; border-radius: 10px; }
  .elite-countdown-num { font-size: 1.65rem; }
  .elite-countdown-label { font-size: 0.58rem; letter-spacing: 0.06em; }
  .elite-hero-card { padding: 30px 16px 24px; }
  .elite-meta-box { flex-direction: column; gap: 8px; padding: 12px 14px; }
  .elite-cta-group { flex-direction: column; width: 100%; }
  .btn-upgrade-elites, .btn-vip-whatsapp { width: 100%; }
}
</style>
<script>var API_BASE = '<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>';</script>
</head>
<body id="top" class="has-sidebar">

<!-- ===== Dashboard Sidebar ===== -->
<?php $activeNav = 'elites'; include __DIR__ . '/components/global_sidebar.php'; ?>

<div class="dash-main-wrap">
  <!-- Unified Global Header -->
  <?php include __DIR__ . '/components/global_header.php'; ?>

  <!-- Main BM Elites View -->
  <div class="dash-overview">
    
    <?php if (isset($_GET['payment_success']) && $_GET['payment_success'] == 1): ?>
      <div class="card-inner-box" style="text-align: center; max-width: 650px; margin: 20px auto 36px; padding: 40px; background:#151D2A; border:1px solid #1E2D42; border-radius:16px;">
        <div style="width: 64px; height: 64px; background: rgba(22, 199, 132, 0.15); color: #16C784; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; margin: 0 auto 24px;">
          &check;
        </div>
        <h1 class="dash-overview__title" style="margin-bottom: 16px;">Subscription Successful!</h1>
        <p style="color: #8fa3b8; font-size: 1.05rem; line-height: 1.6; margin-bottom: 28px;">
          Welcome to the <strong>BM Elites Trading Circle</strong>. Your managed capital access is now active.
        </p>
        
        <div style="background: rgba(246, 70, 93, 0.08); border: 1px solid rgba(246, 70, 93, 0.2); border-radius: 12px; padding: 20px; text-align: left; margin-bottom: 32px;">
          <h4 style="color: #F6465D; margin: 0 0 10px; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Risk Disclaimer
          </h4>
          <p style="color: rgba(246, 70, 93, 0.9); font-size: 0.85rem; line-height: 1.55; margin: 0;">
            Trading Forex and leveraged financial instruments involves significant risk and may result in the loss of your invested capital. 
            BM FOREX HUB provides this managed investment model as a service, but past performance is not indicative of future results.
          </p>
        </div>

        <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
          <a href="https://wa.me/254780618608?text=Hi%20BM%20Forex%20Hub,%20I%20have%20successfully%20subscribed%20to%20BM%20Elites.%20Please%20add%20me%20to%20the%20VIP%20WhatsApp%20group." target="_blank" rel="noopener noreferrer" class="btn-vip-whatsapp" style="padding:14px 28px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            Join VIP WhatsApp Group
          </a>
          <a href="bm_elites.php" class="btn-upgrade-elites">
            Enter BM Elites Circle &rarr;
          </a>
        </div>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['compliance_success']) && $_GET['compliance_success'] == 1): ?>
      <div style="background: rgba(22, 199, 132, 0.12); border: 1px solid rgba(22, 199, 132, 0.35); border-radius: 12px; padding: 18px 24px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 12px;">
          <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(22, 199, 132, 0.2); color: #16C784; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">&check;</div>
          <div>
            <strong style="color: #16C784; font-size: 0.95rem; display: block;">Compliance Verified — Terms &amp; Conditions (v1.0) Accepted</strong>
            <span style="color: #8fa3b8; font-size: 0.82rem;">Thank you for accepting the BM FOREX HUB Elite Circle Terms &amp; Conditions. Your VIP countdown and WhatsApp mentorship access are active.</span>
          </div>
        </div>
        <a href="bm_elites.php" style="color: #8fa3b8; font-size: 0.78rem; text-decoration: none; padding: 4px 10px; border-radius: 6px; border: 1px solid #283548;">Dismiss</a>
      </div>
    <?php endif; ?>

    <!-- Title and Introduction Block -->
    <div class="dash-overview__header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom: 28px;">
      <div>
        <h1 class="dash-overview__title">BM Elites</h1>
        <p style="color:#1677FF; font-family:'Space Grotesk',sans-serif; font-size:0.95rem; font-weight:600; margin:2px 0 6px;">Elite Circle Membership</p>
        <p class="dash-overview__subtitle">BM Elites Trading Circle is an elite managed investment model where capital contributed by participants is pooled together and traded in the foreign exchange market by senior quantitative analysts.</p>
      </div>
      <div class="breadcrumbs" style="color:#8fa3b8; font-size:0.83rem;">
        <a href="index.php" style="color:#8fa3b8; text-decoration:none;">Dashboard</a> / <span style="color:#1677FF;">BM Elites</span>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- 1. ACTIVE BM ELITES SUBSCRIBER EXPERIENCE (COUNTDOWN)   -->
    <!-- (Shown dynamically via JS if active elite member is logged in) -->
    <!-- ======================================================== -->
    <div id="eliteActiveSubscriberHero" style="display:none;">
      
      <!-- Compliance Gate Notice for existing active members who haven't accepted Terms v1.0 -->
      <div id="eliteComplianceNoticeBox" style="display:none; background: linear-gradient(135deg, rgba(240, 180, 41, 0.12) 0%, rgba(22, 119, 255, 0.12) 100%); border: 1px solid rgba(240, 180, 41, 0.4); border-radius: 14px; padding: 22px 24px; margin-bottom: 24px; text-align: left;">
        <div style="display:flex; align-items:flex-start; gap:14px; flex-wrap:wrap;">
          <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(240, 180, 41, 0.2); color: #F0B429; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;">&#9888;</div>
          <div style="flex:1; min-width:260px;">
            <h3 style="color:#F0B429; font-size:1.05rem; margin:0 0 6px; font-family:'Space Grotesk',sans-serif;">Compliance Action Required: Elite Circle Terms Acceptance (v1.0)</h3>
            <p style="color:#B8C3D1; font-size:0.86rem; line-height:1.5; margin:0 0 14px;">As an active BM Elite member, reviewing and accepting the updated 39-section Terms &amp; Conditions (Effective 05 January 2026) is required to maintain your active VIP access and unlock the official WhatsApp Mentorship Group.</p>
            <a href="elite_enrollment.php?mode=compliance" class="btn-upgrade-elites" style="background:#F0B429; color:#0B0F14; font-weight:700; display:inline-flex; padding:10px 22px; font-size:0.88rem;">
              Review &amp; Accept Terms (v1.0) &rarr;
            </a>
          </div>
        </div>
      </div>

      <!-- Primary Live Countdown Card -->
      <div class="elite-hero-card">
        <div class="elite-badge-pill" id="eliteHeroBadge">
          <span style="font-size:0.9rem;">👑</span>
          <span id="eliteHeroBadgeText">ACTIVE BM ELITES ACCESS</span>
        </div>

        <h2 class="elite-hero-title" id="eliteCountdownHeading">
          Your Exclusive BM Elites Session Begins In
        </h2>
        <p class="elite-hero-subtitle" id="eliteCountdownSubheading">
          Weekly managed trading cycle is live. Profit distributions and institutional trade setups update dynamically.
        </p>

        <!-- 4-Digit Live Countdown Block -->
        <div class="elite-countdown-grid" id="eliteCountdownGrid">
          <div class="elite-countdown-block">
            <span class="elite-countdown-num" id="cdDays">00</span>
            <span class="elite-countdown-label">Days</span>
          </div>
          <div class="elite-countdown-block">
            <span class="elite-countdown-num" id="cdHours">00</span>
            <span class="elite-countdown-label">Hours</span>
          </div>
          <div class="elite-countdown-block">
            <span class="elite-countdown-num" id="cdMinutes">00</span>
            <span class="elite-countdown-label">Minutes</span>
          </div>
          <div class="elite-countdown-block">
            <span class="elite-countdown-num" id="cdSeconds">00</span>
            <span class="elite-countdown-label" style="color:#16C784;">Seconds</span>
          </div>
        </div>

        <!-- Expired Notice Container (Hidden until expired) -->
        <div id="eliteExpiredNotice" style="display:none; margin: 0 auto 24px; max-width: 540px; background: rgba(246, 70, 93, 0.1); border: 1px solid rgba(246, 70, 93, 0.35); border-radius: 12px; padding: 18px 24px;">
          <h3 style="color:#F6465D; font-size:1.1rem; margin:0 0 6px; font-family:'Space Grotesk',sans-serif;">Subscription Period Expired</h3>
          <p style="color:#8fa3b8; font-size:0.85rem; margin:0; line-height:1.5;">Your previous BM Elites subscription cycle has completed. Renew or upgrade your allocation to continue participating in the next pool cycle.</p>
        </div>

        <!-- Active Subscription Metadata Details -->
        <div class="elite-meta-box" id="eliteMetaBox">
          <div class="elite-meta-item">
            <span style="color:#8fa3b8;">Tier Allocation:</span>
            <strong id="eliteActiveTierLabel" style="color:#16C784;">BM Elites Plan</strong>
          </div>
          <div class="elite-meta-item">
            <span style="color:#8fa3b8;">Access Expiry:</span>
            <strong id="eliteActiveExpiryLabel">Calculating...</strong>
          </div>
          <div class="elite-meta-item">
            <span style="color:#8fa3b8;">Status:</span>
            <strong id="eliteActiveStatusLabel" style="color:#F0B429;">● Active Member</strong>
          </div>
        </div>

        <!-- Action CTAs -->
        <div class="elite-cta-group">
          <a href="subscribe.php?service=elite" class="btn-upgrade-elites" id="eliteUpgradeBtn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
            <span id="eliteUpgradeBtnText">Upgrade Elites</span>
          </a>
          <a href="https://wa.me/254780618608?text=Hi%20BM%20Forex%20Hub,%20I%20am%20an%20active%20BM%20Elites%20member." target="_blank" rel="noopener noreferrer" class="btn-vip-whatsapp" id="eliteVipWaBtn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            Join VIP WhatsApp Group
          </a>
        </div>
      </div>

      <!-- Unlocked Privileges Overview -->
      <div style="padding: 28px 30px; background: #151D2A; border: 1px solid #1E2D42; border-radius: 16px; margin-bottom: 32px;">
        <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 1.15rem; font-weight: 600; color: #fff; margin: 0 0 18px; display:flex; align-items:center; gap:8px;">
          <span style="color:#16C784;">✓</span> Unlocked Elite Trading Privileges
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
          <div style="background: #101722; border: 1px solid #1e2d42; border-radius: 12px; padding: 18px; display: flex; gap: 14px; align-items: flex-start; text-align: left;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(22, 119, 255, 0.15); color: #1677FF; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">&#9889;</div>
            <div>
              <h4 style="color: #fff; font-size: 0.95rem; font-weight: 600; margin: 0 0 4px;">High-Conviction Signals</h4>
              <p style="color: #8fa3b8; font-size: 0.82rem; margin: 0; line-height: 1.45;">Real-time trade entries, Take-Profit &amp; Stop-Loss targets for Forex, Gold (XAUUSD) &amp; Crypto.</p>
            </div>
          </div>
          <div style="background: #101722; border: 1px solid #1e2d42; border-radius: 12px; padding: 18px; display: flex; gap: 14px; align-items: flex-start; text-align: left;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(34, 197, 94, 0.15); color: #22C55E; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">&#128172;</div>
            <div>
              <h4 style="color: #fff; font-size: 0.95rem; font-weight: 600; margin: 0 0 4px;">VIP Mentorship Group</h4>
              <p style="color: #8fa3b8; font-size: 0.82rem; margin: 0; line-height: 1.45;">Direct access to senior traders, daily market breakdowns &amp; consultation on WhatsApp.</p>
            </div>
          </div>
          <div style="background: #101722; border: 1px solid #1e2d42; border-radius: 12px; padding: 18px; display: flex; gap: 14px; align-items: flex-start; text-align: left;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(240, 180, 41, 0.15); color: #F0B429; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">&#128200;</div>
            <div>
              <h4 style="color: #fff; font-size: 0.95rem; font-weight: 600; margin: 0 0 4px;">5% Weekly Target Returns</h4>
              <p style="color: #8fa3b8; font-size: 0.82rem; margin: 0; line-height: 1.45;">Managed trading pool allocation with slot-proportional weekly earnings payouts.</p>
            </div>
          </div>
          <div style="background: #101722; border: 1px solid #1e2d42; border-radius: 12px; padding: 18px; display: flex; gap: 14px; align-items: flex-start; text-align: left;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(167, 139, 250, 0.15); color: #a78bfa; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">&#127891;</div>
            <div>
              <h4 style="color: #fff; font-size: 0.95rem; font-weight: 600; margin: 0 0 4px;">Exclusive Live Classes</h4>
              <p style="color: #8fa3b8; font-size: 0.82rem; margin: 0; line-height: 1.45;">Live Google Meet sessions, Smart Money Concepts (SMC) &amp; ICT trading strategy archives.</p>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- ======================================================== -->
    <!-- 2. TERMS-FIRST MEMBERSHIP ENTRY POINT                    -->
    <!-- (Always fully rendered server-side by default)           -->
    <!-- ======================================================== -->
    <div id="eliteNonSubscriberHero">

      <div class="elite-terms-gate" id="eliteTermsGate">

        <!-- Authoritative Elite Circle Terms Container -->
        <div class="elite-terms-container" id="eliteTermsContainer" aria-label="Elite Circle Terms and Conditions">
          <div class="elite-terms-sections-wrap">
            <?php include __DIR__ . '/components/elite_terms_content.php'; ?>
          </div>
          
          <div style="padding:14px 24px; border-top:1px solid #1E2D42; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; background:#0E141E;">
            <span style="font-size:0.78rem; color:#7F8B99;">Elite Circle Terms Version 1.0 (Republic of Kenya)</span>
            <a href="terms-elite.php" target="_blank" rel="noopener noreferrer" style="font-size:0.78rem; color:#1677FF; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
              Open Full Document in New Tab
            </a>
          </div>
        </div>

        <!-- Validation Message -->
        <div class="elite-terms-validation-msg" id="eliteTermsValidationMsg" role="alert" aria-live="polite">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>Please read and accept the Elite Circle Terms and Conditions before proceeding.</span>
        </div>

        <!-- Acceptance Section & CTA Card -->
        <div class="terms-acceptance-card">
          <label class="elite-terms-accept-label" for="eliteTermsAcceptCheck">
            <input type="checkbox" id="eliteTermsAcceptCheck" name="elite_terms_accepted" aria-describedby="eliteTermsValidationMsg">
            <span>I have read and agree to the <a href="terms-elite.php" target="_blank" rel="noopener noreferrer" style="color:#1677FF; text-decoration:underline;">Elite Circle Terms and Conditions</a>.</span>
          </label>

          <div class="terms-cta-row">
            <button type="button" class="btn-enroll-proceed" id="eliteEnrollCTABtn" disabled aria-disabled="true">
              Proceed to Electronic Enrollment &amp; Acceptance &rarr;
            </button>
            <span class="terms-step-hint">Stage 1 of 4: Review Terms &rarr; Electronic Enrollment &rarr; Tier Selection &rarr; Payment</span>
          </div>
        </div>

        <!-- Program Highlights Grid -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:18px; margin-bottom:36px;">
          <div class="live-class-card">
            <h3 style="color:#F0B429; margin-bottom:8px; font-size:1.05rem; font-family:'Space Grotesk',sans-serif;">Expert Trading Management</h3>
            <p style="color:#8fa3b8; font-size:0.88rem; line-height:1.55; margin:0;">Experienced forex analysts execute institutional strategies on behalf of the pool to optimize risk-managed returns.</p>
          </div>
          <div class="live-class-card">
            <h3 style="color:#F0B429; margin-bottom:8px; font-size:1.05rem; font-family:'Space Grotesk',sans-serif;">5% Weekly Target Returns</h3>
            <p style="color:#8fa3b8; font-size:0.88rem; line-height:1.55; margin:0;">Target performance objective of <strong>5% weekly</strong> on allocated capital throughout the active cycle.</p>
          </div>
          <div class="live-class-card">
            <h3 style="color:#F0B429; margin-bottom:8px; font-size:1.05rem; font-family:'Space Grotesk',sans-serif;">Weekly Income Payout</h3>
            <p style="color:#8fa3b8; font-size:0.88rem; line-height:1.55; margin:0;">Earnings distributions are processed and paid out weekly directly to verified investor accounts.</p>
          </div>
          <div class="live-class-card">
            <h3 style="color:#F0B429; margin-bottom:8px; font-size:1.05rem; font-family:'Space Grotesk',sans-serif;">4-Month Managed Cycle</h3>
            <p style="color:#8fa3b8; font-size:0.88rem; line-height:1.55; margin:0;">Structured four-month trading cycle with proportional profit distributions based on capital share.</p>
          </div>
        </div>

      </div><!-- /.elite-terms-gate -->

    </div>

    <!-- Risk Disclaimer -->
    <div style="margin-bottom: 40px; padding: 18px 22px; background: #151d2a; border: 1px solid rgba(220, 53, 69, 0.25); border-radius: 12px; display: flex; align-items: flex-start; gap: 14px; max-width: 920px; margin-left: auto; margin-right: auto;">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f6465d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0; margin-top: 2px; opacity: 0.85;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      <div>
        <strong style="color: #f6465d; display: block; margin-bottom: 6px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Risk Disclaimer</strong>
        <span style="color: #8fa3b8; font-size: 0.82rem; line-height: 1.6; display: block;">Trading in foreign exchange and leveraged financial instruments carries significant risk. Return targets such as <strong>5% weekly</strong> are performance objectives based on trading strategy and market conditions and are <strong>not guaranteed</strong>. Always review the full risk disclosure and terms before allocating capital.</span>
      </div>
    </div>
    
  </div><!-- /.dash-overview -->
</div><!-- /.dash-main-wrap -->

<!-- Global Site Footer -->
<?php include __DIR__ . '/footer.php'; ?>

<!-- Scripts for Supabase, Auth and Dynamic UI functionality -->
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
<script src="js/auth.js?v=<?= filemtime('js/auth.js') ?>"></script>
<script>
(async function() {
  /* ---------- Auth Check & Active Subscription Gate ---------- */
  try {
    const { user, session } = await BMAuth.getSession();
    
    if (user) {
      const authData = await BMAuth.getMembershipStatus(user, session);
      const userDisplayName = BMAuth.displayName(user);
      const displayUserEl = document.getElementById('displayUsername');
      if (displayUserEl) displayUserEl.textContent = userDisplayName;

      // Evaluate whether user has active BM Elites subscription
      const plans = authData.plans || [];
      const isPermanent = authData.permanent_access || plans.includes('all');
      const hasElitePlan = isPermanent || plans.some(p => p.startsWith('elite_'));

      let eliteSub = null;
      if (Array.isArray(authData.subscriptions)) {
        eliteSub = authData.subscriptions.find(s => s.plan === 'all' || (s.plan_key && s.plan_key.startsWith('elite_')) || (s.plan && s.plan.startsWith('elite_')));
        if (!eliteSub && hasElitePlan && authData.subscriptions.length > 0) {
          eliteSub = authData.subscriptions[0];
        }
      }

      const nowTs = Date.now();
      let expiryTs = 0;
      let isExpired = false;

      if (eliteSub && eliteSub.expires_at) {
        expiryTs = new Date(eliteSub.expires_at).getTime();
        isExpired = expiryTs <= nowTs;
      } else if (authData.subscription_expiry) {
        expiryTs = new Date(authData.subscription_expiry).getTime();
        isExpired = expiryTs <= nowTs;
      } else if (isPermanent) {
        expiryTs = new Date('2036-12-31T23:59:59Z').getTime();
        isExpired = false;
      }

      const isEliteActive = hasElitePlan && !isExpired;

      const planNameMap = {
        'elite_starter': 'BM Elites — $1,000 USD Starter Tier',
        'elite_intermediate': 'BM Elites — $2,000 USD Intermediate Tier',
        'elite_advanced': 'BM Elites — $3,000 USD Advanced Tier',
        'elite_professional': 'BM Elites — $5,000 USD Professional Tier',
        'elite_premium': 'BM Elites — $6,000 USD Premium Tier',
        'elite_elite': 'BM Elites — $10,000 USD VIP Elite Tier',
        'all': 'BM Elites — Permanent Admin / VIP Access'
      };

      const activePlanKey = (eliteSub && eliteSub.plan) || (plans.find(p => p.startsWith('elite_'))) || (isPermanent ? 'all' : '');
      const activePlanTitle = planNameMap[activePlanKey] || (eliteSub && eliteSub.plan_name) || 'BM Elites Trading Circle';

      const subscriberHero = document.getElementById('eliteActiveSubscriberHero');
      const nonSubscriberHero = document.getElementById('eliteNonSubscriberHero');

      if (isEliteActive) {
        if (subscriberHero) subscriberHero.style.display = 'block';
        if (nonSubscriberHero) nonSubscriberHero.style.display = 'none';

        const tierLabel = document.getElementById('eliteActiveTierLabel');
        if (tierLabel) tierLabel.textContent = activePlanTitle;

        const statusLabel = document.getElementById('eliteActiveStatusLabel');
        if (statusLabel) {
          statusLabel.textContent = isPermanent ? '● Permanent VIP Access' : '● Active Member';
          statusLabel.style.color = '#16C784';
        }

        const expiryLabel = document.getElementById('eliteActiveExpiryLabel');
        if (expiryLabel) {
          if (isPermanent) {
            expiryLabel.textContent = 'Permanent (Perpetual)';
          } else if (expiryTs > 0) {
            const d = new Date(expiryTs);
            expiryLabel.textContent = d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) + ' ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
          }
        }

        initEliteCountdown(expiryTs, isPermanent);

        // Compliance check for active members
        try {
          const checkResp = await fetch('api/elite-enrollment.php?action=check', {
            headers: { 'Authorization': 'Bearer ' + (session ? session.access_token : '') }
          });
          if (checkResp.ok) {
            const checkData = await checkResp.json();
            const complianceBox = document.getElementById('eliteComplianceNoticeBox');
            const waBtn = document.getElementById('eliteVipWaBtn');

            if (checkData.ok && checkData.requires_consent) {
              if (complianceBox) complianceBox.style.display = 'block';
              if (waBtn) {
                waBtn.href = 'elite_enrollment.php?mode=compliance';
                waBtn.innerHTML = `
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  Unlock WhatsApp Group (Accept Terms)
                `;
              }
            } else if (checkData.ok && checkData.accepted) {
              if (complianceBox) complianceBox.style.display = 'none';
              if (waBtn) {
                const memberName = (checkData.enrollment && checkData.enrollment.member_name) || userDisplayName;
                const memberEmail = (checkData.enrollment && checkData.enrollment.email) || user.email;
                const enrollmentId = (checkData.enrollment && checkData.enrollment.id) || 'VERIFIED';
                const waMsg = `Hi BM Forex Hub, I am an active BM Elite Member. Please verify my access to the VIP WhatsApp Group.\n\nFull Name: ${memberName}\nEmail: ${memberEmail}\nMember ID: ${user.id}\nTerms Version: 1.0 (Accepted)\nAcceptance Record: ${enrollmentId}`;
                waBtn.href = `https://wa.me/254780618608?text=${encodeURIComponent(waMsg)}`;
              }
            }
          }
        } catch(e) {}
      }
    }
  } catch(err) {
    console.warn('Auth evaluation notice:', err);
  }

  // Elite Terms Gate Checkbox & CTA Handling
  const termsCheck = document.getElementById('eliteTermsAcceptCheck');
  const termsCtaBtn = document.getElementById('eliteEnrollCTABtn');
  const termsValMsg = document.getElementById('eliteTermsValidationMsg');

  if (termsCheck && termsCtaBtn) {
    termsCheck.addEventListener('change', function() {
      if (this.checked) {
        termsCtaBtn.disabled = false;
        termsCtaBtn.setAttribute('aria-disabled', 'false');
        if (termsValMsg) termsValMsg.classList.remove('show');
      } else {
        termsCtaBtn.disabled = true;
        termsCtaBtn.setAttribute('aria-disabled', 'true');
      }
    });

    termsCtaBtn.addEventListener('click', function(e) {
      e.preventDefault();
      if (!termsCheck.checked) {
        if (termsValMsg) {
          termsValMsg.classList.add('show');
          termsValMsg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        return false;
      }
      
      window.location.href = 'elite_enrollment.php';
    });
  }

  // Countdown function
  function initEliteCountdown(targetTs, isPerm) {
    const elDays = document.getElementById('cdDays');
    const elHours = document.getElementById('cdHours');
    const elMinutes = document.getElementById('cdMinutes');
    const elSeconds = document.getElementById('cdSeconds');

    if (!elDays || !elHours || !elMinutes || !elSeconds) return;

    let dynamicTargetTs = targetTs;
    if (isPerm) {
      const now = new Date();
      const nextCycle = new Date(now);
      const daysUntilSunday = (7 - now.getDay()) % 7 || 7;
      nextCycle.setDate(now.getDate() + daysUntilSunday);
      nextCycle.setHours(18, 0, 0, 0);
      dynamicTargetTs = nextCycle.getTime();
    }

    function update() {
      const current = Date.now();
      const diff = dynamicTargetTs - current;

      if (diff <= 0) {
        elDays.textContent = '00';
        elHours.textContent = '00';
        elMinutes.textContent = '00';
        elSeconds.textContent = '00';
        
        if (!isPerm) {
          clearInterval(timerId);
          window.location.reload();
        } else {
          dynamicTargetTs += 7 * 86400000;
        }
        return;
      }

      const totalSeconds = Math.max(0, Math.floor(diff / 1000));
      const days = Math.floor(totalSeconds / 86400);
      const hours = Math.floor((totalSeconds % 86400) / 3600);
      const minutes = Math.floor((totalSeconds % 3600) / 60);
      const seconds = totalSeconds % 60;

      elDays.textContent = String(days).padStart(2, '0');
      elHours.textContent = String(hours).padStart(2, '0');
      elMinutes.textContent = String(minutes).padStart(2, '0');
      elSeconds.textContent = String(seconds).padStart(2, '0');
    }

    update();
    const timerId = setInterval(update, 1000);
  }

  // Mobile hamburger navigation
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const mobileNav = document.getElementById('mobileNav');
  const mobileNavClose = document.getElementById('mobileNavClose');

  if (hamburgerBtn && mobileNav) {
    const openMenu = () => {
      mobileNav.classList.add('open');
      hamburgerBtn.setAttribute('aria-expanded', 'true');
      document.body.style.overflow = 'hidden';
    };
    const closeMenu = () => {
      mobileNav.classList.remove('open');
      hamburgerBtn.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
    };
    hamburgerBtn.addEventListener('click', openMenu);
    if (mobileNavClose) mobileNavClose.addEventListener('click', closeMenu);
    if (mobileNav) {
      mobileNav.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMenu));
      mobileNav.addEventListener('click', e => { if (e.target === mobileNav) closeMenu(); });
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && mobileNav.classList.contains('open')) closeMenu(); });
  }
})();
</script>
<?php $activeTab = 'more'; include __DIR__ . '/components/mobile-tabbar.php'; ?>

</body>
</html>
