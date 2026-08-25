<?php
$host = preg_replace('/[^a-zA-Z0-9.:-]/', '', $_SERVER['HTTP_HOST'] ?? 'bmforexhub.com');
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$canonical = $scheme . '://' . $host . '/contact.php';
?>
<!doctype html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="css/theme-light.css?v=1">
<script src="js/theme.js" defer></script>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require_once __DIR__ . '/components/pwa_meta.php'; ?>
<title>Contact Us | BM FOREX HUB</title>
<meta name="description" content="Get in touch with BM FOREX HUB LTD. Contact us for questions, support, mentorship inquiries, or partnership opportunities.">
<link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
<link rel="icon" type="image/png" href="BM-ForexHub-Logo-Circle.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="css/ai-assistant.css">
<style>
:root{--void:#0B0F14;--panel:#101722;--panel2:#151D29;--line:#283548;--ink:#FFFFFF;--muted:#B8C3D1;--dim:#7F8B99;--gold:#1677FF;--gold2:#0D47A1;--teal:#16C784;--coral:#F6465D;--radius:14px}
*{box-sizing:border-box}html{scroll-behavior:smooth}
body{margin:0;background:var(--void);color:var(--ink);font:16px/1.6 Inter,Arial,sans-serif;-webkit-font-smoothing:antialiased}
a{color:inherit;text-decoration:none}button{font:inherit}img{max-width:100%;display:block}
::selection{background:var(--gold);color:var(--void)}
.shell{width:min(1180px,calc(100% - 40px));margin:auto}

.nav{position:sticky;top:0;z-index:20;background:rgba(10,14,20,.94);border-bottom:1px solid var(--line);backdrop-filter:blur(15px)}
.navin{min-height:76px;display:flex;align-items:center;justify-content:space-between;gap:22px}
.brand{display:flex;gap:10px;align-items:center;font-family:"Space Grotesk",sans-serif;font-weight:700;letter-spacing:.02em}
.brand img{width:38px;height:38px;border-radius:50%}.brand em{color:var(--gold);font-style:normal}
.links{display:flex;align-items:center;gap:22px;font-size:.9rem;color:var(--muted)}.links a:hover{color:var(--gold)}
.navactions{display:flex;gap:10px}
.button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:45px;padding:10px 18px;border:1px solid var(--line);border-radius:9px;font-weight:700;font-size:.9rem;transition:transform .2s,border-color .2s,background .2s}
.button:hover{transform:translateY(-2px);border-color:var(--gold)}.button.primary{background:var(--gold);color:var(--void);border-color:var(--gold)}.button.primary:hover{background:#e2b466}
.mobile-toggle{display:none;background:none;border:none;color:var(--ink);font-size:1rem;font-weight:600;cursor:pointer;padding:8px}

.page-hero{padding:80px 0 40px;text-align:center}
.page-hero .eyebrow{display:inline-block;font-size:.78rem;text-transform:uppercase;letter-spacing:.12em;color:var(--gold);font-weight:600;margin-bottom:12px}
.page-hero h1{font-family:"Space Grotesk",sans-serif;font-size:2.4rem;font-weight:700;margin:0 0 12px}
.page-hero p{color:var(--muted);font-size:1rem;max-width:560px;margin:auto}

.contact-grid{display:grid;grid-template-columns:1fr 1fr;gap:32px;padding:40px 0 80px}
.contact-card{background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);padding:36px 32px}
.contact-card h2{font-family:"Space Grotesk",sans-serif;font-size:1.3rem;font-weight:700;margin:0 0 24px;color:var(--gold)}
.info-row{display:flex;align-items:flex-start;gap:14px;padding:14px 0;border-bottom:1px solid var(--line)}
.info-row:last-child{border-bottom:none}
.info-icon{width:40px;height:40px;border-radius:10px;background:rgba(212,162,76,.1);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;color:var(--gold)}
.info-label{font-size:.78rem;text-transform:uppercase;letter-spacing:.08em;color:var(--dim);font-weight:600;margin-bottom:2px}
.info-value{font-size:.95rem;color:var(--ink)}
.info-value a{color:var(--gold);transition:color .2s}.info-value a:hover{color:#e2b466}
.info-address{font-size:.92rem;color:var(--muted);line-height:1.6}

.map-wrap{margin-top:24px;border-radius:var(--radius);overflow:hidden;border:1px solid var(--line)}
.map-wrap iframe{width:100%;height:340px;border:0;display:block}

.eyebrow{display:inline-block;font-size:.78rem;text-transform:uppercase;letter-spacing:.12em;color:var(--gold);font-weight:600;margin-bottom:12px}
.section-head{margin-bottom:40px}
.section-head h2{font-family:"Space Grotesk",sans-serif;font-size:1.6rem;font-weight:700;margin:0 0 8px}
.section-head p{color:var(--muted);font-size:.95rem}

.cta{padding:80px 0;background:var(--panel)}
.cta .section-head{margin-bottom:28px}
.cta .hero-buttons{display:flex;gap:12px;flex-wrap:wrap}

footer{padding:40px 0;border-top:1px solid var(--line)}
.footer-top{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:20px}
.footer-links{display:flex;gap:18px;font-size:.85rem;color:var(--muted);flex-wrap:wrap}
.footer-links a:hover{color:var(--gold)}
.copyright{text-align:center;margin-top:24px;padding-top:20px;border-top:1px solid var(--line);font-size:.82rem;color:var(--dim)}

@media(max-width:768px){
  .contact-grid{grid-template-columns:1fr}
  .links{display:none}.links.open{display:flex;flex-direction:column;position:absolute;top:76px;left:0;right:0;background:rgba(10,14,20,.97);border-bottom:1px solid var(--line);padding:20px;gap:16px}
  .mobile-toggle{display:block}
  .navin{position:relative}
  .page-hero h1{font-size:1.8rem}
}
</style>
</head>
<body>

<header class="nav"><div class="shell navin"><a class="brand" href="landing.php" aria-label="BM FOREX HUB home"><img src="BM-ForexHub-Logo-Circle.png" alt="BM FOREX HUB logo" width="38" height="38"><span>BM <em>FOREX</em> HUB</span></a><nav class="links" id="siteNav" aria-label="Primary navigation"><a href="landing.php">Home</a><a href="landing.php#about">About</a><a href="landing.php#offer">What we offer</a><a href="landing.php#pricing">Pricing</a><a href="landing.php#why">Why BM FOREX HUB</a><a href="landing.php#faq">FAQ</a><a href="contact.php" style="color:var(--gold)">Contact</a></nav><div class="navactions"><button type="button" class="theme-toggle" title="Toggle light / dark theme" aria-label="Toggle light / dark theme"><svg class="theme-toggle__sun" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg><svg class="theme-toggle__moon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></button><a class="button" href="login.php">Sign in</a><a class="button primary" href="register.php">Start learning</a><button class="mobile-toggle" type="button" aria-expanded="false" aria-controls="siteNav">Menu</button></div></div></header>

<section class="page-hero">
  <div class="shell">
    <span class="eyebrow">Get in touch</span>
    <h1>Contact Us</h1>
    <p>Have questions about our programs, mentorship, or partnerships? We'd love to hear from you.</p>
  </div>
</section>

<div class="shell">
  <div class="contact-grid">

    <div class="contact-card">
      <h2>Contact Information</h2>

      <div class="info-row">
        <div class="info-icon">🏢</div>
        <div>
          <div class="info-label">Company</div>
          <div class="info-value"><strong>BM FOREX HUB LTD</strong></div>
        </div>
      </div>

      <div class="info-row">
        <div class="info-icon">🌐</div>
        <div>
          <div class="info-label">Website</div>
          <div class="info-value"><a href="https://bmforexhub.exchange" target="_blank" rel="noopener noreferrer">bmforexhub.exchange</a></div>
        </div>
      </div>

      <div class="info-row">
        <div class="info-icon">✉️</div>
        <div>
          <div class="info-label">General Inquiries</div>
          <div class="info-value"><a href="mailto:info@admin.bmforexhub.exchange">info@admin.bmforexhub.exchange</a></div>
        </div>
      </div>

      <div class="info-row">
        <div class="info-icon">🛠️</div>
        <div>
          <div class="info-label">Support</div>
          <div class="info-value"><a href="mailto:support@bmforexhub.exchange">support@bmforexhub.exchange</a></div>
        </div>
      </div>

      <div class="info-row">
        <div class="info-icon">📱</div>
        <div>
          <div class="info-label">Phone</div>
          <div class="info-value"><a href="tel:+254780618608">+254 780 618 608</a></div>
        </div>
      </div>

      <div class="info-row">
        <div class="info-icon">📍</div>
        <div>
          <div class="info-label">Business Address</div>
          <div class="info-address">Shoppers Paradise Building, 4th Floor<br>Kenyatta Avenue<br>Nakuru, Kenya</div>
        </div>
      </div>
    </div>

    <div class="contact-card">
      <h2>Our Location</h2>
      <div class="map-wrap">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.7361!2d36.0675!3d-0.3031!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMMKwMTgnMTEuMiJTIDM2wrAwNCcwNC4wIkU!5e0!3m2!1sen!2ske!4v1700000000000!5m2!1sen!2ske" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="BM FOREX HUB office location - Shoppers Paradise, Nakuru"></iframe>
      </div>
      <p style="color:var(--dim);font-size:.82rem;margin-top:12px;">Shoppers Paradise Building, 4th Floor, Kenyatta Avenue, Nakuru, Kenya</p>
    </div>

  </div>
</div>

<section class="cta"><div class="shell"><div class="section-head"><span class="eyebrow" style="color:#39362d">Ready to start?</span><h2>Begin Your Trading Journey</h2><p>Join BM FOREX HUB today and start building the skills to trade with confidence.</p></div><div class="hero-buttons"><a class="button primary" href="register.php">Create Account</a><a class="button" href="https://wa.me/message/K5RM7MSWXBNPC1" target="_blank" rel="noopener noreferrer">WhatsApp Us</a><a class="button" href="https://t.me/bmforexhubafrica" target="_blank" rel="noopener noreferrer">Join Telegram</a></div></div></section>

<footer><div class="shell"><div class="footer-top"><a class="brand" href="landing.php"><img src="BM-ForexHub-Logo-Circle.png" alt="" width="38" height="38"><span>BM <em>FOREX</em> HUB</span></a><nav class="footer-links" aria-label="Footer navigation"><a href="about.php">About</a><a href="landing.php#offer">Courses</a><a href="contact.php">Contact</a><a href="terms.php">Terms</a><a href="privacy.php">Privacy</a><a href="refund.php">Refund</a><a href="risk-disclosure.php">Risk Disclosure</a><a href="cookie-policy.php">Cookies</a><a href="aml-kyc.php">AML &amp; KYC</a><a href="copyright-ip.php">Copyright &amp; IP</a><a href="disclaimer.php">Disclaimer</a><a href="login.php">Sign in</a><a href="register.php">Create account</a></nav></div><div class="copyright"><div><p>&copy; <?= date('Y') ?> BM FOREX HUB LTD. All rights reserved.</p><p style="margin-top:3px">VARBAN COMPANY LIMITED &bull; Registered under Licence Number: PVT-PJUY6LJX</p></div><p>Education first. No profit promises.</p></div></div></footer>

<script>
(function(){var toggle=document.querySelector('.mobile-toggle'),nav=document.getElementById('siteNav');if(!toggle||!nav)return;toggle.addEventListener('click',function(){var open=nav.classList.toggle('open');toggle.setAttribute('aria-expanded',open?'true':'false')});nav.querySelectorAll('a').forEach(function(link){link.addEventListener('click',function(){nav.classList.remove('open');toggle.setAttribute('aria-expanded','false')})})})();
</script>
<script src="js/motion.js" defer></script>
<script src="js/ai-assistant.js" defer></script>
</body>
</html>