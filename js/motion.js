/**
 * BM Forex Hub — Motion & Responsive Polish Engine (motion.js)
 * Lightweight, high-performance vanilla JS motion library
 */

(function () {
  'use strict';

  // Check reduced motion preference & touch capability
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const isTouchDevice = window.matchMedia('(hover: none) and (pointer: coarse)').matches;

  // Initialize Custom Interactive Motion Cursor & Scroll Aura
  function initCustomMotionCursor() {
    if (prefersReducedMotion || isTouchDevice) return;
    if (document.querySelector('.bm-cursor-dot')) return;

    const dot = document.createElement('div');
    dot.className = 'bm-cursor-dot';

    const ring = document.createElement('div');
    ring.className = 'bm-cursor-ring';

    document.body.appendChild(dot);
    document.body.appendChild(ring);

    let mouseX = -100, mouseY = -100;
    let ringX = -100, ringY = -100;
    let scrollTimeout = null;

    window.addEventListener('mousemove', function (e) {
      mouseX = e.clientX;
      mouseY = e.clientY;
      dot.style.transform = `translate3d(${mouseX}px, ${mouseY}px, 0) translate3d(-50%, -50%, 0)`;
    }, { passive: true });

    function renderCursor() {
      // Smooth ring lerp tracking
      ringX += (mouseX - ringX) * 0.18;
      ringY += (mouseY - ringY) * 0.18;

      ring.style.transform = `translate3d(${ringX}px, ${ringY}px, 0) translate3d(-50%, -50%, 0)`;
      requestAnimationFrame(renderCursor);
    }
    requestAnimationFrame(renderCursor);

    // Scroll active pulse handler
    window.addEventListener('scroll', function () {
      document.body.classList.add('bm-cursor-scrolling');
      if (scrollTimeout) clearTimeout(scrollTimeout);
      scrollTimeout = setTimeout(() => {
        document.body.classList.remove('bm-cursor-scrolling');
      }, 400);
    }, { passive: true });

    // Interactive element hover detection
    document.addEventListener('mouseover', function (e) {
      if (e.target.closest('a, button, input, select, textarea, .btn, .sp-card, .action-btn, .sp-elite-tier, .trial-banner__btn, .offer, .stat-card')) {
        document.body.classList.add('bm-cursor-hover');
      }
    });

    document.addEventListener('mouseout', function (e) {
      if (e.target.closest('a, button, input, select, textarea, .btn, .sp-card, .action-btn, .sp-elite-tier, .trial-banner__btn, .offer, .stat-card')) {
        document.body.classList.remove('bm-cursor-hover');
      }
    });
  }

  // Initialize Top Scroll Progress Indicator Bar
  function initScrollProgressBar() {
    if (document.querySelector('.bm-scroll-progress-bar')) return;

    const progressBar = document.createElement('div');
    progressBar.className = 'bm-scroll-progress-bar';
    document.body.appendChild(progressBar);

    window.addEventListener('scroll', function () {
      const scrollTop = window.scrollY || document.documentElement.scrollTop;
      const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
      const scrollPercent = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
      progressBar.style.width = `${scrollPercent}%`;
    }, { passive: true });
  }

  // Initialize Floating Scroll-To-Top Button
  function initScrollToTopBtn() {
    if (document.querySelector('.bm-scroll-top-btn')) return;

    const btn = document.createElement('button');
    btn.className = 'bm-scroll-top-btn';
    btn.setAttribute('aria-label', 'Scroll to top');
    btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>';
    document.body.appendChild(btn);

    window.addEventListener('scroll', function () {
      if (window.scrollY > 300) {
        btn.classList.add('is-visible');
      } else {
        btn.classList.remove('is-visible');
      }
    }, { passive: true });

    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // Auto Tag Grid & Card Elements for Smooth Entrance Reveal & Subtle Floating
  function autoTagRevealsAndFloats() {
    const cardSelectors = [
      '.sp-card', '.stat-card', '.offer', '.price-card', 
      '.hero-card', '.statement', '.value', '.crypto-card',
      '.mo-card', '.class-card', '.pos-card', '.inventory-card'
    ];

    cardSelectors.forEach(sel => {
      document.querySelectorAll(sel).forEach((el, index) => {
        // Tag Entrance Reveals
        if (!el.classList.contains('bm-reveal') && !el.classList.contains('bm-reveal-scale') && !el.classList.contains('bm-reveal-left') && !el.classList.contains('bm-reveal-right')) {
          if (index % 4 === 0) {
            el.classList.add('bm-reveal-left');
          } else if (index % 4 === 2) {
            el.classList.add('bm-reveal-right');
          } else {
            el.classList.add('bm-reveal');
          }
          const delayClass = `bm-delay-${(index % 5) + 1}`;
          el.classList.add(delayClass);
        }

        // Tag Subtle Floating Effects (3px–10px range)
        if (!prefersReducedMotion && !el.classList.contains('bm-float-sm') && !el.classList.contains('bm-float-md') && !el.classList.contains('bm-float-lg')) {
          const floatType = index % 3 === 0 ? 'bm-float-sm' : (index % 3 === 1 ? 'bm-float-md' : 'bm-float-lg');
          const floatDelay = `bm-float-delay-${(index % 5) + 1}`;
          el.classList.add(floatType, floatDelay);
        }
      });
    });
  }

  // Initialize IntersectionObserver for Scroll-Triggered Reveals
  function initScrollReveals() {
    if (prefersReducedMotion) {
      document.querySelectorAll('.bm-reveal, .bm-reveal-scale, .bm-reveal-left, .bm-reveal-right, .reveal, .reveal-up').forEach(el => {
        el.classList.add('is-visible');
      });
      return;
    }

    const observerOptions = {
      root: null,
      rootMargin: '0px 0px -40px 0px',
      threshold: 0.08
    };

    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          obs.unobserve(entry.target);
        }
      });
    }, observerOptions);

    const revealElements = document.querySelectorAll('.bm-reveal, .bm-reveal-scale, .bm-reveal-left, .bm-reveal-right, .reveal, .reveal-up');
    revealElements.forEach(el => observer.observe(el));
  }

  // Initialize Ripple Effect on Interactive Buttons and Cards
  function initRippleEffects() {
    if (prefersReducedMotion) return;

    document.addEventListener('click', function (e) {
      const target = e.target.closest('.bm-btn-interactive, .button, .btn, .sp-card, .action-btn, .sp-elite-tier, .trial-banner__btn');
      if (!target) return;

      const rect = target.getBoundingClientRect();
      const ripple = document.createElement('span');
      ripple.className = 'bm-ripple';
      
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;

      ripple.style.width = ripple.style.height = `${size}px`;
      ripple.style.left = `${x}px`;
      ripple.style.top = `${y}px`;

      // Remove existing ripple if present
      const existingRipple = target.querySelector('.bm-ripple');
      if (existingRipple) existingRipple.remove();

      target.appendChild(ripple);

      setTimeout(() => {
        ripple.remove();
      }, 600);
    });
  }

  // Auto Wrap Tables in Responsive Containers
  function initTableWrappers() {
    const tables = document.querySelectorAll('table:not(.bm-table-wrapped)');
    tables.forEach(table => {
      table.classList.add('bm-table-wrapped');
      if (!table.parentElement.classList.contains('bm-table-container') && !table.parentElement.classList.contains('table-wrap')) {
        const wrapper = document.createElement('div');
        wrapper.className = 'bm-table-container';
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
      }
    });
  }

  // Mobile Navigation Drawer Toggle Handler
  function initMobileDrawer() {
    const hamburgerBtns = document.querySelectorAll('.hamburger, .dash-mobile-toggle, .mobile-toggle');
    const sidebars = document.querySelectorAll('.sidebar, .dash-sidebar');

    hamburgerBtns.forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        sidebars.forEach(sidebar => {
          sidebar.classList.toggle('open');
        });
      });
    });

    // Close on outside click
    document.addEventListener('click', function (e) {
      if (!e.target.closest('.sidebar') && !e.target.closest('.dash-sidebar') && !e.target.closest('.hamburger') && !e.target.closest('.dash-mobile-toggle') && !e.target.closest('.mobile-toggle')) {
        sidebars.forEach(sidebar => sidebar.classList.remove('open'));
      }
    });
  }

  // Form Field Interactive Polish & Validation Shake
  function initFormPolish() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
      form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) {
          const invalidInput = form.querySelector(':invalid');
          if (invalidInput) {
            invalidInput.classList.add('bm-shake');
            setTimeout(() => invalidInput.classList.remove('bm-shake'), 400);
          }
        }
      });
    });

    const inputs = document.querySelectorAll('input:not([type="checkbox"]):not([type="radio"]), select, textarea');
    inputs.forEach(input => {
      input.classList.add('bm-input-enhanced');
    });
  }

  // Sidebar Collapse / Expand Toggle & Storage Handler
  function initSidebarCollapse() {
    try {
      if (localStorage.getItem('bmSidebarCollapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
      }
    } catch(e) {}

    const collapseBtns = document.querySelectorAll('#dashSidebarCollapseBtn, .dash-sidebar__toggle-btn');
    collapseBtns.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
        try {
          localStorage.setItem('bmSidebarCollapsed', isCollapsed ? 'true' : 'false');
        } catch(err) {}
      });
    });
  }

  // DOM Content Loaded Initializer
  document.addEventListener('DOMContentLoaded', function () {
    initCustomMotionCursor();
    initScrollProgressBar();
    initScrollToTopBtn();
    autoTagRevealsAndFloats();
    initScrollReveals();
    initRippleEffects();
    initTableWrappers();
    initMobileDrawer();
    initSidebarCollapse();
    initFormPolish();
  });

})();
