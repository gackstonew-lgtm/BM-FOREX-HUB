/**
 * BM Forex Hub Signal Terminal — Production Service Worker
 * Version: 1.0.0
 * 
 * Financial Security & Caching Guidelines:
 * - Real-time market rates, live signals, balances, payment endpoints, and auth routes are NEVER cached.
 * - Static UI shell, stylesheets, scripts, fonts, and brand assets use Cache-First / Stale-While-Revalidate.
 * - HTML Page Navigations use Network-First with fallback to cached page or offline.html.
 */

const CACHE_NAME = 'bmforexhub-v1.0.0';

// Core static app shell assets to precache
const PRECACHE_ASSETS = [
  './',
  './index.php',
  './basics.css',
  './auth.css',
  './css/theme-light.css',
  './css/motion.css',
  './css/mobile.css',
  './css/ai-assistant.css',
  './css/crypto-widget.css',
  './css/overview.css',
  './css/classes.css',
  './css/elite.css',
  './css/header-top.css',
  './css/trading-dashboard.css',
  './js/theme.js',
  './js/motion.js',
  './js/auth.js',
  './js/ai-assistant.js',
  './js/calculators.js',
  './js/crypto-widget.js',
  './js/trading-dashboard.js',
  './js/pwa-register.js',
  './BM-ForexHub-Logo-Circle.png',
  './offline.html',
  './manifest.webmanifest',
  './icons/favicon-16x16.png',
  './icons/favicon-32x32.png',
  './icons/icon-72x72.png',
  './icons/icon-96x96.png',
  './icons/icon-128x128.png',
  './icons/icon-144x144.png',
  './icons/icon-152x152.png',
  './icons/icon-180x180.png',
  './icons/icon-192x192.png',
  './icons/icon-384x384.png',
  './icons/icon-512x512.png',
  './icons/maskable-icon-192x192.png',
  './icons/maskable-icon-512x512.png'
];

// Domains and URL patterns that MUST NOT be cached (Strict Network-Only)
const EXCLUDED_HOSTNAMES = [
  'supabase.co',
  'tradingview.com',
  'binance.com',
  'coingecko.com',
  'twelvedata.com',
  'financialmodelingprep.com'
];

const EXCLUDED_URL_PATTERNS = [
  /\/api\//i,
  /\/admin\//i,
  /login\.php/i,
  /register\.php/i,
  /logout\.php/i,
  /verify-email\.php/i,
  /forgot-password\.php/i,
  /subscribe\.php/i,
  /testpay\.php/i,
  /indicator-subscribe\.php/i,
  /\/rest\/v1\//i,
  /\/auth\/v1\//i
];

// Install Event — Precache core static shell
self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        return cache.addAll(PRECACHE_ASSETS).catch((err) => {
          console.warn('[PWA SW] Precache warning (some assets fetched on demand):', err);
        });
      })
  );
});

// Activate Event — Cleanup obsolete caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('[PWA SW] Removing obsolete cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Helper check for excluded dynamic / sensitive endpoints
function isExcluded(url) {
  // Check request method — never cache mutations
  const host = url.hostname;
  const pathname = url.pathname + url.search;

  if (EXCLUDED_HOSTNAMES.some(h => host.includes(h))) return true;
  if (EXCLUDED_URL_PATTERNS.some(pattern => pattern.test(pathname))) return true;

  return false;
}

// Fetch Event — Security & Cache Handling Strategy
self.addEventListener('fetch', (event) => {
  const req = event.request;
  const url = new URL(req.url);

  // Non-GET requests (POST, PUT, DELETE) -> Network Only
  if (req.method !== 'GET') {
    return;
  }

  // Non-HTTP/HTTPS schemes (browser extensions, etc.) -> Ignore
  if (!url.protocol.startsWith('http')) {
    return;
  }

  // Sensitive API, Auth, Payment, or Live Financial Data -> Network Only
  if (isExcluded(url)) {
    return;
  }

  // Strategy 1: HTML Navigations -> Network-First with Offline Page Fallback
  if (req.mode === 'navigate' || (req.headers.get('accept') && req.headers.get('accept').includes('text/html'))) {
    event.respondWith(
      fetch(req)
        .then((response) => {
          if (response && response.status === 200) {
            const copy = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(req, copy));
          }
          return response;
        })
        .catch(async () => {
          const cachedResponse = await caches.match(req);
          if (cachedResponse) {
            return cachedResponse;
          }
          const offlinePage = await caches.match('./offline.html');
          if (offlinePage) {
            return offlinePage;
          }
          return new Response(
            '<html><body><h2>Offline</h2><p>Live market connection unavailable. Please check your connection.</p></body></html>',
            { headers: { 'Content-Type': 'text/html' } }
          );
        })
    );
    return;
  }

  // Strategy 2: Static Assets (CSS, JS, Fonts, Images) -> Stale-While-Revalidate
  event.respondWith(
    caches.match(req).then((cachedResponse) => {
      const fetchPromise = fetch(req).then((networkResponse) => {
        if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
          const copy = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(req, copy));
        }
        return networkResponse;
      }).catch(() => {
        // Silent network fail for background revalidation
      });

      return cachedResponse || fetchPromise || fetch(req);
    })
  );
});

// Handle SW messages from client (e.g. skipWaiting trigger)
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});
