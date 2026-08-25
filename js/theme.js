/* ==========================================================================
   BM Forex Hub — Theme toggle (js/theme.js)
   Reads/writes <html data-theme="light"> and persists the choice in
   localStorage under the key 'bmTheme'. Loaded with defer on every page.
   The no-FOUC inline script in <head> applies the saved theme before CSS.
   ========================================================================== */
(function () {
  'use strict';
  var STORAGE_KEY = 'bmTheme';

  function currentTheme() {
    return document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
  }

  function applyTheme(theme) {
    if (theme === 'light') {
      document.documentElement.setAttribute('data-theme', 'light');
    } else {
      document.documentElement.removeAttribute('data-theme');
    }
    try {
      localStorage.setItem(STORAGE_KEY, theme);
    } catch (e) { /* private mode — ignore */ }
  }

  document.addEventListener('DOMContentLoaded', function () {
    var toggles = document.querySelectorAll('.theme-toggle');
    Array.prototype.forEach.call(toggles, function (btn) {
      btn.addEventListener('click', function () {
        applyTheme(currentTheme() === 'light' ? 'dark' : 'light');
      });
    });
  });
})();
