// Minimal service worker — exists only so the browser considers this app
// installable as a standalone PWA. It intentionally does NOT cache pages or
// API responses: this is a live admin panel (dashboard stats, service lists,
// payment status) and caching that content could show stale or wrong data,
// or leak one user's data to the next session on a shared device.
self.addEventListener('install', function (event) {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', function (event) {
    event.respondWith(fetch(event.request));
});
