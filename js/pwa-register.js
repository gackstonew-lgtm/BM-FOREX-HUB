/**
 * BM Forex Hub — PWA Client Registration & Lifecycle Manager
 */

(function () {
  'use strict';

  // Prevent duplicate execution
  if (window.__BM_PWA_INITIALIZED__) return;
  window.__BM_PWA_INITIALIZED__ = true;

  let deferredInstallPrompt = null;

  // 1. Service Worker Registration
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('./sw.js', { scope: './' })
        .then(function (registration) {
          console.log('[PWA] Service Worker registered with scope:', registration.scope);

          // Handle updates
          registration.addEventListener('updatefound', function () {
            const installingWorker = registration.installing;
            if (!installingWorker) return;

            installingWorker.addEventListener('statechange', function () {
              if (installingWorker.state === 'installed' && navigator.serviceWorker.controller) {
                showUpdateToast(registration);
              }
            });
          });
        })
        .catch(function (error) {
          console.warn('[PWA] Service Worker registration failed:', error);
        });

      // Handle controller change (SW update refresh)
      let refreshing = false;
      navigator.serviceWorker.addEventListener('controllerchange', function () {
        if (!refreshing) {
          refreshing = true;
          window.location.reload();
        }
      });
    });
  }

  // 2. Non-Intrusive Update Notification Toast
  function showUpdateToast(registration) {
    if (document.getElementById('bmPwaUpdateToast')) return;

    const toast = document.createElement('div');
    toast.id = 'bmPwaUpdateToast';
    toast.setAttribute('role', 'alert');
    toast.style.cssText = `
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 99999;
      background: #101722;
      border: 1px solid #1677FF;
      color: #FFFFFF;
      padding: 14px 20px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
      display: flex;
      align-items: center;
      gap: 14px;
      font-family: 'Inter', system-ui, sans-serif;
      font-size: 0.88rem;
      animation: bmToastSlideIn 0.3s ease;
    `;

    toast.innerHTML = `
      <span>⚡ A new version of BM Forex Hub is available.</span>
      <button id="bmPwaUpdateBtn" style="
        background: #1677FF;
        color: #fff;
        border: none;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
      ">Refresh</button>
    `;

    document.body.appendChild(toast);

    document.getElementById('bmPwaUpdateBtn').addEventListener('click', function () {
      if (registration.waiting) {
        registration.waiting.postMessage({ type: 'SKIP_WAITING' });
      } else {
        window.location.reload();
      }
    });
  }

  // 3. Online / Offline Connection Toast Indicator
  function createNetworkToast() {
    let toast = document.getElementById('bmPwaNetworkToast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'bmPwaNetworkToast';
      toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        left: 24px;
        z-index: 99998;
        padding: 10px 18px;
        border-radius: 20px;
        font-family: 'Inter', system-ui, sans-serif;
        font-size: 0.82rem;
        font-weight: 600;
        box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        transition: opacity 0.3s ease, transform 0.3s ease;
        opacity: 0;
        pointer-events: none;
        transform: translateY(20px);
      `;
      document.body.appendChild(toast);
    }
    return toast;
  }

  function showNetworkStatus(isOnline) {
    const toast = createNetworkToast();
    if (isOnline) {
      toast.textContent = '🟢 Online — Connected to BM Forex Hub';
      toast.style.background = '#101722';
      toast.style.color = '#16C784';
      toast.style.border = '1px solid rgba(22, 199, 132, 0.4)';
    } else {
      toast.textContent = '🔴 Offline — Live data unavailable';
      toast.style.background = '#101722';
      toast.style.color = '#F6465D';
      toast.style.border = '1px solid rgba(246, 70, 93, 0.4)';
    }

    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';

    setTimeout(function () {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(20px)';
    }, 4000);
  }

  window.addEventListener('online', function () { showNetworkStatus(true); });
  window.addEventListener('offline', function () { showNetworkStatus(false); });

  // 4. Non-Intrusive Install Prompt Handling
  window.addEventListener('beforeinstallprompt', function (e) {
    // Prevent immediate automatic banner popup
    e.preventDefault();
    deferredInstallPrompt = e;
    console.log('[PWA] Install prompt deferred & captured cleanly.');
  });

  // Global helper for user-initiated install action if needed
  window.triggerBMForexPWAInstall = function () {
    if (deferredInstallPrompt) {
      deferredInstallPrompt.prompt();
      deferredInstallPrompt.userChoice.then(function (choiceResult) {
        if (choiceResult.outcome === 'accepted') {
          console.log('[PWA] User accepted PWA installation');
        }
        deferredInstallPrompt = null;
      });
    }
  };

  // Add subtle CSS animation for toasts
  const style = document.createElement('style');
  style.textContent = `
    @keyframes bmToastSlideIn {
      from { transform: translateY(100%); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
  `;
  document.head.appendChild(style);

})();
