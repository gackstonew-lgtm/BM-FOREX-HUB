<?php
require_once __DIR__ . '/engine_config.php';
?>
<!-- ===== Global Site Footer ===== -->
<footer class="bm-site-footer">
  <div class="bm-site-footer__inner">
    <div class="bm-site-footer__brand">
      <img src="BM-ForexHub-Logo-Circle.png" alt="BM Forex Hub" width="28" height="28" loading="lazy">
      <span class="bm-site-footer__brand-name">BM FOREX HUB</span>
    </div>
    <p class="bm-site-footer__copy">&copy; <?php echo date('Y'); ?> BM Forex Hub. All Rights Reserved.</p>
    <nav class="bm-site-footer__nav" aria-label="Legal and support navigation">
      <a href="terms.php" class="bm-site-footer__link">Terms of Use</a>
      <span class="bm-site-footer__sep" aria-hidden="true">|</span>
      <a href="terms-elite.php" class="bm-site-footer__link">Elite Circle Terms</a>
      <span class="bm-site-footer__sep" aria-hidden="true">|</span>
      <a href="privacy.php" class="bm-site-footer__link">Privacy Policy</a>
      <span class="bm-site-footer__sep" aria-hidden="true">|</span>
      <a href="risk-disclosure.php" class="bm-site-footer__link">Risk Disclaimer</a>
      <span class="bm-site-footer__sep" aria-hidden="true">|</span>
      <a href="contact.php" class="bm-site-footer__link">Support</a>
    </nav>
  </div>
</footer>
<style>
/* ===== BM Global Site Footer ===== */
.bm-site-footer {
  margin-top: 48px;
  border-top: 1px solid var(--hairline, #1E2D42);
  background: var(--bg, #0B0F14);
  padding: 20px 24px 22px;
}
.bm-site-footer__inner {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
}
.bm-site-footer__brand {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}
.bm-site-footer__brand img {
  border-radius: 50%;
  opacity: 0.85;
}
.bm-site-footer__brand-name {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  color: var(--ink-muted, #B8C3D1);
}
.bm-site-footer__copy {
  font-size: 0.75rem;
  color: var(--ink-dim, #5B6475);
  margin: 0;
  order: 3;
}
.bm-site-footer__nav {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 6px 10px;
}
.bm-site-footer__sep {
  color: var(--hairline, #283548);
  font-size: 0.7rem;
  line-height: 1;
  flex-shrink: 0;
}
.bm-site-footer__link {
  font-size: 0.78rem;
  color: var(--ink-dim, #5B6475);
  text-decoration: none;
  transition: color 0.15s ease;
  white-space: nowrap;
}
.bm-site-footer__link:hover {
  color: var(--gold, #1677FF);
}
.bm-site-footer__link:focus-visible {
  outline: 2px solid var(--gold, #1677FF);
  outline-offset: 2px;
  border-radius: 2px;
}
.bm-site-footer__link--accent {
  color: #F0B429;
}
.bm-site-footer__link--accent:hover {
  color: #F0C84A;
}

/* Light theme overrides */
:root[data-theme="light"] .bm-site-footer {
  background: #F1F5F9;
  border-top-color: #E2E8F0;
}
:root[data-theme="light"] .bm-site-footer__brand-name {
  color: #64748B;
}
:root[data-theme="light"] .bm-site-footer__copy {
  color: #94A3B8;
}
:root[data-theme="light"] .bm-site-footer__sep {
  color: #CBD5E1;
}
:root[data-theme="light"] .bm-site-footer__link {
  color: #64748B;
}
:root[data-theme="light"] .bm-site-footer__link:hover {
  color: #1677FF;
}

/* Responsive */
@media (max-width: 768px) {
  .bm-site-footer__inner {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  .bm-site-footer__copy {
    order: unset;
  }
  .bm-site-footer__nav {
    gap: 6px 8px;
  }
}
</style>
<script src="js/motion.js" defer></script>
