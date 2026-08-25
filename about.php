<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="css/theme-light.css?v=1">
<script src="js/theme.js" defer></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require_once __DIR__ . '/components/pwa_meta.php'; ?>
<title>About Us | BM FOREX HUB</title>
<meta name="description" content="Learn about BM FOREX HUB — Kenya's leading forex education company. Founded in 2019, empowering traders through structured education, mentorship, and Smart Money Concepts.">
<link rel="icon" type="image/png" href="BM-ForexHub-Logo-Circle.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="css/ai-assistant.css">
<style>
:root{--void:#0B0F14;--panel:#101722;--panel2:#151D29;--line:#283548;--ink:#FFFFFF;--muted:#B8C3D1;--dim:#7F8B99;--gold:#1677FF;--gold2:#0D47A1;--teal:#16C784;--coral:#F6465D;--radius:14px}
*{box-sizing:border-box;margin:0;padding:0}html{scroll-behavior:smooth}
body{background:var(--void);color:var(--ink);font:16px/1.7 Inter,Arial,sans-serif;-webkit-font-smoothing:antialiased}
a{color:var(--gold);text-decoration:none}a:hover{text-decoration:underline}
::selection{background:var(--gold);color:#FFFFFF}
.shell{width:min(1120px,calc(100% - 40px));margin:auto}
img{max-width:100%;display:block}

/* NAV */
.topnav{position:sticky;top:0;z-index:20;background:rgba(11,15,20,.94);border-bottom:1px solid var(--line);backdrop-filter:blur(15px)}
.topnavin{min-height:76px;display:flex;align-items:center;justify-content:space-between}
.brand{display:flex;gap:10px;align-items:center;font-family:"Space Grotesk",sans-serif;font-weight:700;font-size:.95rem;color:var(--ink)}
.brand img{width:38px;height:38px;border-radius:50%}.brand em{color:var(--gold);font-style:normal}
.nav-links{display:flex;gap:24px;font-size:.9rem;color:var(--muted)}
.nav-links a:hover{color:var(--gold)}
.nav-actions{display:flex;gap:10px}
.button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:45px;padding:10px 20px;border:1px solid var(--line);border-radius:9px;font-weight:700;font-size:.9rem;transition:transform .2s,border-color .2s,background .2s}
.button:hover{transform:translateY(-2px);border-color:var(--gold);text-decoration:none}
.button.primary{background:var(--gold);color:#FFFFFF;border-color:var(--gold)}
.button.primary:hover{background:#2F80FF}

/* HERO */
.hero-about{position:relative;overflow:hidden;padding:100px 0 80px}
.hero-about::before{content:"";position:absolute;inset:0;background:url("BM-ForexHub-Logo-Circle.png") center/600px no-repeat;opacity:.04;pointer-events:none}
.hero-inner{display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:var(--panel2);border:1px solid var(--line);border-radius:100px;padding:6px 16px;font-size:.78rem;text-transform:uppercase;letter-spacing:.1em;color:var(--gold);font-weight:600;margin-bottom:20px}
.hero-badge .dot{width:6px;height:6px;background:var(--gold);border-radius:50%;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.hero-about h1{font-family:"Space Grotesk",sans-serif;font-size:2.8rem;font-weight:700;line-height:1.15;margin:0 0 20px}
.hero-about h1 .gold{color:var(--gold)}
.hero-about .lead{color:var(--muted);font-size:1.05rem;max-width:520px;line-height:1.7;margin-bottom:32px}
.hero-about .lead strong{color:var(--ink)}
.hero-visual{position:relative;display:flex;justify-content:center}
.hero-logo-frame{width:260px;height:260px;border-radius:50%;background:linear-gradient(135deg,var(--gold2),var(--gold));display:flex;align-items:center;justify-content:center;animation:float 6s ease-in-out infinite;position:relative}
.hero-logo-frame img{width:180px;height:180px;border-radius:50%;position:relative;z-index:1}
.hero-logo-frame::after{content:"";position:absolute;inset:-20px;border-radius:50%;border:1px solid rgba(22,119,255,.25);animation:spin 20s linear infinite}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}
@keyframes spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}
.hero-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:40px}
.stat-card{background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);padding:20px;text-align:center}
.stat-card .num{font-family:"Space Grotesk",sans-serif;font-size:1.6rem;font-weight:700;color:var(--gold)}
.stat-card .label{font-size:.78rem;color:var(--dim);margin-top:4px;text-transform:uppercase;letter-spacing:.06em}

/* SECTIONS */
section{padding:80px 0}
.section-center{text-align:center;max-width:700px;margin:0 auto 50px}
.eyebrow{display:inline-block;font-size:.78rem;text-transform:uppercase;letter-spacing:.12em;color:var(--gold);font-weight:600;margin-bottom:12px}
.section-title{font-family:"Space Grotesk",sans-serif;font-size:2rem;font-weight:700;margin:0 0 16px}
.section-sub{color:var(--muted);font-size:1rem;line-height:1.7}
.divider{width:100%;height:1px;background:var(--line)}

/* STORY */
.story-grid{display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center}
.story-img{position:relative;border-radius:var(--radius);overflow:hidden;aspect-ratio:4/3;background:var(--panel2);border:1px solid var(--line)}
.story-img .overlay-text{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:30px}
.story-img .overlay-text h3{font-family:"Space Grotesk",sans-serif;font-size:1.8rem;font-weight:700;color:var(--gold);margin-bottom:8px}
.story-img .overlay-text p{color:var(--muted);font-size:.9rem}
.story-content .year{font-family:"Space Grotesk",sans-serif;font-size:3rem;font-weight:700;color:var(--gold2);opacity:.5;line-height:1;margin-bottom:12px}
.story-content p{color:var(--muted);margin-bottom:16px;font-size:.95rem;line-height:1.75}
.story-content p strong{color:var(--ink)}

/* MISSION VISION */
.mv-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px}
.mv-card{background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);padding:36px 32px;position:relative;overflow:hidden;transition:border-color .3s}
.mv-card:hover{border-color:var(--gold)}
.mv-card::before{content:"";position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--gold),var(--gold2))}
.mv-card .icon{width:48px;height:48px;border-radius:12px;background:rgba(212,162,76,.1);display:flex;align-items:center;justify-content:center;font-size:1.3rem;margin-bottom:20px;color:var(--gold)}
.mv-card h3{font-family:"Space Grotesk",sans-serif;font-size:1.2rem;font-weight:700;margin-bottom:10px}
.mv-card p{color:var(--muted);font-size:.92rem;line-height:1.7}

/* SERVICES */
.services-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
.service-card{background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);padding:28px 24px;transition:all .3s}
.service-card:hover{border-color:var(--gold);transform:translateY(-4px)}
.service-card .s-icon{width:44px;height:44px;border-radius:10px;background:rgba(212,162,76,.08);display:flex;align-items:center;justify-content:center;font-size:1.2rem;margin-bottom:16px;color:var(--gold)}
.service-card h3{font-family:"Space Grotesk",sans-serif;font-size:1rem;font-weight:600;margin-bottom:8px}
.service-card p{color:var(--muted);font-size:.88rem;line-height:1.6}

/* WHY US */
.why-section{background:var(--panel)}
.why-grid{display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:start}
.why-list{display:flex;flex-direction:column;gap:14px}
.why-item{display:flex;align-items:flex-start;gap:14px;background:var(--panel2);border:1px solid var(--line);border-radius:12px;padding:18px 20px;transition:border-color .3s}
.why-item:hover{border-color:var(--gold)}
.why-item .check{width:32px;height:32px;border-radius:8px;background:rgba(212,162,76,.12);display:flex;align-items:center;justify-content:center;font-size:.9rem;color:var(--gold);flex-shrink:0}
.why-item span{font-size:.92rem;color:var(--muted);line-height:1.5}

/* VALUES */
.values-row{display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-top:20px}
.value-card{background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);padding:28px 16px;text-align:center;transition:all .3s}
.value-card:hover{border-color:var(--gold);transform:translateY(-4px)}
.value-card .v-icon{width:50px;height:50px;border-radius:50%;background:rgba(212,162,76,.1);display:flex;align-items:center;justify-content:center;font-size:1.3rem;margin:0 auto 14px;color:var(--gold)}
.value-card h4{font-family:"Space Grotesk",sans-serif;font-size:.95rem;font-weight:600;margin-bottom:6px}
.value-card p{color:var(--dim);font-size:.82rem;line-height:1.5}

/* COMMITMENT */
.commit-section{background:var(--panel2)}
.commit-box{background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);padding:48px 40px;text-align:center;position:relative;overflow:hidden}
.commit-box::before{content:"";position:absolute;inset:0;background:linear-gradient(135deg,rgba(212,162,76,.05),transparent);pointer-events:none}
.commit-box blockquote{font-family:"Space Grotesk",sans-serif;font-size:1.4rem;font-weight:600;color:var(--ink);max-width:600px;margin:0 auto 16px;line-height:1.4}
.commit-box .attr{color:var(--dim);font-size:.85rem}

/* CTA */
.cta{padding:80px 0;text-align:center}
.cta h2{font-family:"Space Grotesk",sans-serif;font-size:2rem;font-weight:700;margin-bottom:12px}
.cta p{color:var(--muted);font-size:1rem;margin-bottom:30px}
.cta-buttons{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}

/* FOOTER */
footer{padding:28px 0;border-top:1px solid var(--line);text-align:center;font-size:.82rem;color:var(--dim)}
footer .links{margin-bottom:10px}
footer a{color:var(--gold)}

/* MOBILE */
@media(max-width:900px){
.hero-inner{grid-template-columns:1fr;text-align:center}
.hero-about .lead{margin-left:auto;margin-right:auto}
.hero-visual{order:-1}
.hero-logo-frame{width:180px;height:180px}
.hero-logo-frame img{width:120px;height:120px}
.hero-stats{max-width:500px;margin:40px auto 0}
.story-grid{grid-template-columns:1fr}
.services-grid{grid-template-columns:1fr 1fr}
.mv-grid{grid-template-columns:1fr}
.values-row{grid-template-columns:repeat(3,1fr)}
.why-grid{grid-template-columns:1fr}
.nav-links{display:none}
}
@media(max-width:640px){
.shell{width:min(100% - 24px,1120px)}
.hero-about{padding:60px 0 50px}
.hero-about h1{font-size:1.8rem}
.services-grid{grid-template-columns:1fr}
.values-row{grid-template-columns:1fr 1fr}
.hero-stats{grid-template-columns:repeat(3,1fr);gap:10px}
.stat-card{padding:14px 10px}
.stat-card .num{font-size:1.3rem}
section{padding:60px 0}
.commit-box{padding:32px 20px}
.commit-box blockquote{font-size:1.1rem}
}
</style>
</head>
<body>

<header class="topnav"><div class="shell topnavin">
<a class="brand" href="landing.php"><img src="BM-ForexHub-Logo-Circle.png" alt="" width="38" height="38">BM <em>FOREX</em> HUB</a>
<nav class="nav-links"><a href="landing.php">Home</a><a href="about.php" style="color:var(--gold)">About</a><a href="contact.php">Contact</a></nav>
<div class="nav-actions"><button type="button" class="theme-toggle" title="Toggle light / dark theme" aria-label="Toggle light / dark theme"><svg class="theme-toggle__sun" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg><svg class="theme-toggle__moon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></button><a class="button" href="login.php">Sign in</a><a class="button primary" href="register.php">Start learning</a></div>
</div></header>

<!-- HERO -->
<section class="hero-about"><div class="shell">
<div class="hero-inner">
<div>
<div class="hero-badge"><span class="dot"></span> Established 2019 — Kenya</div>
<h1>Master the Markets.<br><span class="gold">Build Your Financial Future.</span></h1>
<p class="lead">BM FOREX HUB is a leading forex education and trading solutions company dedicated to empowering individuals with the knowledge, discipline, and practical skills required to succeed in the global financial markets.</p>
<div class="hero-stats">
<div class="stat-card"><div class="num">6+</div><div class="label">Years Active</div></div>
<div class="stat-card"><div class="num">11+</div><div class="label">Pairs Covered</div></div>
<div class="stat-card"><div class="num">24/7</div><div class="label">Community</div></div>
</div>
</div>
<div class="hero-visual">
<div class="hero-logo-frame"><img src="BM-ForexHub-Logo-Circle.png" alt="BM FOREX HUB" width="180" height="180"></div>
</div>
</div>
</div></section>

<div class="divider"></div>

<!-- OUR STORY -->
<section><div class="shell">
<div class="story-grid">
<div class="story-img">
<div class="overlay-text">
<h3>Est. 2019</h3>
<p>Nakuru, Kenya</p>
</div>
</div>
<div class="story-content">
<span class="eyebrow">Our Story</span>
<div class="year">2019</div>
<p>Based in Kenya, BM FOREX HUB was founded with a clear mission: to bridge the gap between beginner and professional traders by providing world-class forex education backed by practical market application.</p>
<p>We have trained aspiring traders from different backgrounds, helping them understand the markets with confidence through <strong>structured education</strong>, <strong>mentorship</strong>, and <strong>real-market experience</strong>.</p>
<p>At BM FOREX HUB, we believe that successful trading is not based on luck&mdash;it is built on education, consistency, proper risk management, and a proven trading strategy.</p>
</div>
</div>
</div></section>

<!-- MISSION & VISION -->
<section style="background:var(--panel)"><div class="shell">
<div class="section-center">
<span class="eyebrow">Purpose</span>
<h2 class="section-title">What Drives Us</h2>
<p class="section-sub">Our mission and vision shape every program, lesson, and interaction within the BM FOREX HUB community.</p>
</div>
<div class="mv-grid">
<div class="mv-card">
<div class="icon">◆</div>
<h3>Our Mission</h3>
<p>To empower traders through quality education, institutional trading strategies, and professional mentorship that enables them to achieve financial independence while promoting responsible risk management.</p>
</div>
<div class="mv-card">
<div class="icon">◇</div>
<h3>Our Vision</h3>
<p>To become Africa's most trusted and respected forex trading academy, producing disciplined, knowledgeable, and consistently profitable traders through innovation, integrity, and excellence.</p>
</div>
</div>
</div></section>

<!-- SERVICES -->
<section><div class="shell">
<div class="section-center">
<span class="eyebrow">What We Offer</span>
<h2 class="section-title">A Complete Framework for Developing Trading Skill</h2>
<p class="section-sub">Choose the support and study areas that match where you are now, then keep building with a clear, practical process.</p>
</div>
<div class="services-grid">
<div class="service-card"><div class="s-icon">⌁</div><h3>Forex Trading Classes</h3><p>Structured lessons that build market knowledge from the ground up for all experience levels.</p></div>
<div class="service-card"><div class="s-icon">◇</div><h3>Smart Money Concepts</h3><p>Study institutional-grade market structure, liquidity, and price-action analysis.</p></div>
<div class="service-card"><div class="s-icon">↗</div><h3>One-on-One Mentorship</h3><p>Focused guidance to support a deliberate and personalised learning plan.</p></div>
<div class="service-card"><div class="s-icon">◎</div><h3>Live Market Analysis</h3><p>See how professional ideas are applied to current market conditions in real time.</p></div>
<div class="service-card"><div class="s-icon">⇄</div><h3>Copy Trading</h3><p>Learn how copy-trading services work and understand the associated risks.</p></div>
<div class="service-card"><div class="s-icon">◈</div><h3>Risk Management</h3><p>Build habits for proper position sizing, planning, and capital preservation.</p></div>
<div class="service-card"><div class="s-icon">◌</div><h3>Trading Psychology</h3><p>Develop discipline, patience, and consistent decision-making under pressure.</p></div>
<div class="service-card"><div class="s-icon">↕</div><h3>Multi-Timeframe Analysis</h3><p>Connect broad market context with precise execution planning across timeframes.</p></div>
<div class="service-card"><div class="s-icon">⚙</div><h3>Automated Solutions</h3><p>Explore rule-based trading tools with responsible oversight and monitoring.</p></div>
<div class="service-card"><div class="s-icon">★</div><h3>VIP Trading Community</h3><p>Stay connected with a network of traders who value process and long-term growth.</p></div>
</div>
</div></section>

<!-- WHY US -->
<section class="why-section"><div class="shell">
<div class="why-grid">
<div>
<span class="eyebrow">Why Choose BM FOREX HUB</span>
<h2 class="section-title" style="margin-top:8px">Professional Standards.<br>Practical Support.</h2>
<p class="section-sub" style="margin-bottom:24px">BM FOREX HUB supports your development with a learning-first culture and institutional concepts that can be tested, understood, and improved over time.</p>
<a class="button primary" href="register.php">Get Started</a>
</div>
<div class="why-list">
<div class="why-item"><div class="check">✓</div><span>Founded in 2019 with a clear mission</span></div>
<div class="why-item"><div class="check">✓</div><span>Experienced mentors and professional analysts</span></div>
<div class="why-item"><div class="check">✓</div><span>Institutional Smart Money Concepts (SMC)</span></div>
<div class="why-item"><div class="check">✓</div><span>Practical training with live market conditions</span></div>
<div class="why-item"><div class="check">✓</div><span>Structured learning from beginner to advanced</span></div>
<div class="why-item"><div class="check">✓</div><span>Professional risk management techniques</span></div>
<div class="why-item"><div class="check">✓</div><span>Continuous mentorship and trading support</span></div>
<div class="why-item"><div class="check">✓</div><span>A growing community committed to long-term success</span></div>
</div>
</div>
</div></section>

<!-- CORE VALUES -->
<section><div class="shell">
<div class="section-center">
<span class="eyebrow">Core Values</span>
<h2 class="section-title">How We Show Up for Our Community</h2>
</div>
<div class="values-row">
<div class="value-card"><div class="v-icon">◇</div><h4>Integrity</h4><p>Honesty, transparency, and professionalism in everything we do.</p></div>
<div class="value-card"><div class="v-icon">★</div><h4>Excellence</h4><p>High-quality education and outstanding service delivery.</p></div>
<div class="value-card"><div class="v-icon">◈</div><h4>Discipline</h4><p>Consistency and proper execution form the foundation of success.</p></div>
<div class="value-card"><div class="v-icon">↗</div><h4>Innovation</h4><p>Adapting to evolving financial markets and technology.</p></div>
<div class="value-card"><div class="v-icon">↑</div><h4>Growth</h4><p>Committed to the personal and financial development of every trader.</p></div>
</div>
</div></section>

<!-- COMMITMENT -->
<section class="commit-section"><div class="shell">
<div class="commit-box">
<blockquote>"At BM FOREX HUB, our commitment extends beyond teaching strategies. We are passionate about developing disciplined traders who understand market structure, manage risk responsibly, and make informed decisions."</blockquote>
<div class="attr">BM FOREX HUB &mdash; Master the Market. Elevate Your Future.</div>
</div>
</div></section>

<!-- CTA -->
<section class="cta"><div class="shell">
<h2>Ready to Start Your Journey?</h2>
<p>Join BM FOREX HUB and begin building the skills to trade with confidence.</p>
<div class="cta-buttons">
<a class="button primary" href="register.php">Create Account</a>
<a class="button" href="contact.php">Contact Us</a>
</div>
</div></section>

<footer><div class="shell">
<div class="links"><a href="landing.php">Home</a> · <a href="about.php">About</a> · <a href="terms.php">Terms</a> · <a href="privacy.php">Privacy</a> · <a href="refund.php">Refund</a> · <a href="risk-disclosure.php">Risk Disclosure</a> · <a href="cookie-policy.php">Cookies</a> · <a href="aml-kyc.php">AML &amp; KYC</a> · <a href="copyright-ip.php">Copyright &amp; IP</a> · <a href="disclaimer.php">Disclaimer</a> · <a href="contact.php">Contact</a></div>
<p>&copy; <?= date('Y') ?> BM FOREX HUB. All rights reserved.</p>
<p style="margin-top:4px">VARBAN COMPANY LIMITED &bull; Registered under Licence Number: PVT-PJUY6LJX</p>
</div></footer>

<script src="js/motion.js" defer></script>
<script src="js/ai-assistant.js" defer></script>
</body>
</html>