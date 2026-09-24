<?php
require_once __DIR__ . '/engine_config.php';
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
<title>BM FOREX HUB Elite Circle Terms &amp; Conditions | Legal Agreement</title>
<meta name="description" content="Official Terms and Conditions for the BM FOREX HUB Elite Circle managed trading program (Version 1.0).">
<meta name="robots" content="index, follow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="BM-ForexHub-Logo-Circle.png">
<link rel="stylesheet" href="basics.css?v=<?= filemtime('basics.css') ?>">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="css/mobile.css?v=<?= filemtime('css/mobile.css') ?>">
<style>
:root {
  --void: #0B0F14;
  --panel: #101722;
  --panel2: #151D29;
  --line: #283548;
  --ink: #FFFFFF;
  --muted: #B8C3D1;
  --dim: #7F8B99;
  --gold: #1677FF;
  --gold-accent: #F0B429;
  --teal: #16C784;
  --coral: #F6465D;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body { background: var(--void); color: var(--ink); font: 16px/1.7 'Inter', Arial, sans-serif; -webkit-font-smoothing: antialiased; }
a { color: var(--gold); text-decoration: none; }
a:hover { text-decoration: underline; }
::selection { background: var(--gold); color: #fff; }

.shell { width: min(880px, calc(100% - 40px)); margin: auto; }
.topnav { position: sticky; top: 0; z-index: 50; background: rgba(11, 15, 20, 0.94); border-bottom: 1px solid var(--line); backdrop-filter: blur(15px); }
.topnavin { min-height: 64px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
.brand { display: flex; gap: 10px; align-items: center; font-family: "Space Grotesk", sans-serif; font-weight: 700; font-size: 0.95rem; color: #fff; text-decoration: none; }
.brand img { width: 32px; height: 32px; border-radius: 50%; }
.brand em { color: var(--gold); font-style: normal; }

.legal-header-box {
  background: linear-gradient(180deg, #151D29 0%, #101722 100%);
  border: 1px solid var(--line);
  border-radius: 16px;
  padding: 32px 28px;
  margin: 40px 0 32px;
  position: relative;
  overflow: hidden;
}
.legal-header-box::before {
  content: '';
  position: absolute;
  top: 0;
  left: 10%;
  right: 10%;
  height: 2px;
  background: linear-gradient(90deg, transparent, #1677FF, #F0B429, #1677FF, transparent);
}
.badge-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  border-radius: 20px;
  background: rgba(240, 180, 41, 0.12);
  border: 1px solid rgba(240, 180, 41, 0.3);
  color: var(--gold-accent);
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  margin-bottom: 12px;
}
h1 { font-family: "Space Grotesk", sans-serif; font-size: 2rem; font-weight: 700; margin-bottom: 8px; line-height: 1.25; }
.meta-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
  margin-top: 20px;
  padding-top: 18px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 0.82rem;
  color: var(--dim);
}
.meta-item strong { color: #fff; font-family: 'IBM Plex Mono', monospace; }

.toc-card {
  background: #101722;
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 20px 24px;
  margin-bottom: 36px;
}
.toc-title {
  font-family: "Space Grotesk", sans-serif;
  font-size: 0.95rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 12px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.toc-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 8px;
  font-size: 0.8rem;
}
.toc-grid a {
  color: var(--muted);
  text-decoration: none;
  padding: 4px 0;
  transition: color 0.15s;
}
.toc-grid a:hover { color: var(--gold); }

.legal-section {
  background: #151D29;
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 24px 28px;
  margin-bottom: 20px;
}
.legal-section h2 {
  font-family: "Space Grotesk", sans-serif;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--gold-accent);
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.legal-section p {
  color: var(--muted);
  font-size: 0.92rem;
  line-height: 1.7;
  margin-bottom: 12px;
}
.legal-section p:last-child { margin-bottom: 0; }
.legal-section strong { color: #fff; }
.legal-section ul {
  color: var(--muted);
  margin: 0 0 12px 24px;
  font-size: 0.92rem;
}
.legal-section li { margin-bottom: 6px; }

.callout-box {
  background: rgba(246, 70, 93, 0.08);
  border: 1px solid rgba(246, 70, 93, 0.25);
  border-radius: 10px;
  padding: 16px 20px;
  margin: 16px 0;
  color: #F6465D;
  font-size: 0.88rem;
  line-height: 1.6;
}

.acceptance-actions {
  text-align: center;
  margin: 40px 0 60px;
}
.btn-enroll-cta {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 16px 36px;
  border-radius: 10px;
  background: linear-gradient(135deg, #1677FF 0%, #0D47A1 100%);
  color: #fff;
  font-family: "Space Grotesk", sans-serif;
  font-size: 1rem;
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 8px 24px rgba(22, 119, 255, 0.35);
  transition: transform 0.2s, box-shadow 0.2s;
}
.btn-enroll-cta:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(22, 119, 255, 0.5);
  text-decoration: none;
}
</style>
</head>
<body>

<header class="topnav">
  <div class="shell topnavin">
    <a class="brand" href="index.php">
      <img src="BM-ForexHub-Logo-Circle.png" alt="BM Forex Hub" width="32" height="32">
      BM <em>FOREX</em> HUB
    </a>
    <div style="display:flex; align-items:center; gap:16px;">
      <button type="button" class="theme-toggle" title="Toggle light / dark theme" aria-label="Toggle light / dark theme">
        <svg class="theme-toggle__sun" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
        <svg class="theme-toggle__moon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      </button>
      <a href="bm_elites.php" style="color:var(--dim); font-size:0.85rem;">&larr; Back to BM Elites</a>
    </div>
  </div>
</header>

<main class="shell">
  <?php include __DIR__ . '/components/elite_terms_content.php'; ?>

  <div class="acceptance-actions">
    <a href="elite_enrollment.php" class="btn-enroll-cta">
      Proceed to Electronic Enrollment &amp; Acceptance &rarr;
    </a>
  </div>
</main>

<footer style="padding: 24px 0; border-top: 1px solid var(--line); text-align: center; font-size: 0.82rem; color: var(--dim); margin-top: 60px;">
  <div class="shell">
    <p>&copy; <?= date('Y') ?> BM FOREX HUB. All Rights Reserved. Operated by Varban Company Limited (Parent: Stillrock Ventures).</p>
    <div style="display:flex; justify-content:center; gap:16px; margin-top:8px; flex-wrap:wrap;">
      <a href="terms.php">General Terms</a>
      <span style="color:var(--line);">|</span>
      <a href="terms-elite.php">Elite Circle Terms</a>
      <span style="color:var(--line);">|</span>
      <a href="privacy.php">Privacy Policy</a>
      <span style="color:var(--line);">|</span>
      <a href="risk-disclosure.php">Risk Disclaimer</a>
      <span style="color:var(--line);">|</span>
      <a href="contact.php">Support</a>
    </div>
  </div>
</footer>

<script src="js/motion.js" defer></script>
</body>
</html>
