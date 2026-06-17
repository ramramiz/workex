// WorkeX Service Worker
const CACHE_NAME = 'workex-v1';
const OFFLINE_URL = '/offline.html';

// Assets to pre-cache on install
const PRE_CACHE_ASSETS = [
    '/',
    '/offline.html',
    '/manifest.json',
    '/pwa-icon-192.png',
    '/pwa-icon-512.png',
];

// ─── Install ──────────────────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRE_CACHE_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// ─── Activate ─────────────────────────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        }).then(() => self.clients.claim())
    );
});

// ─── Fetch ────────────────────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests and non-http(s) requests
    if (request.method !== 'GET' || !url.protocol.startsWith('http')) return;

    // Skip cross-origin requests (CDN fonts, Bootstrap, etc.) — let them pass through
    if (url.origin !== self.location.origin) return;

    // Skip API / AJAX / form routes
    if (
        url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/live-status/') ||
        url.pathname.startsWith('/notifications/') ||
        url.pathname.startsWith('/work-timer/') ||
        url.pathname.startsWith('/mailbox/') ||
        url.pathname.startsWith('/chat/') ||
        url.pathname.startsWith('/broadcasting/') ||
        url.pathname.startsWith('/sanctum/')
    ) return;

    // Strategy: Network-first for HTML pages, Cache-first for static assets
    const isHTMLRequest =
        request.headers.get('Accept') && request.headers.get('Accept').includes('text/html');

    if (isHTMLRequest) {
        // Network-first: try network, fallback to cache, then offline page
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    }
                    return response;
                })
                .catch(() =>
                    caches.match(request).then((cached) => cached || caches.match(OFFLINE_URL))
                )
        );
    } else {
        // Cache-first for static assets (JS, CSS, images, fonts)
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) return cached;
                return fetch(request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    }
                    return response;
                }).catch(() => caches.match(OFFLINE_URL));
            })
        );
    }
});

// ─── Push Notifications (future-ready) ────────────────────────────────────────
self.addEventListener('push', (event) => {
    if (!event.data) return;
    const data = event.data.json();
    const options = {
        body: data.body || 'You have a new notification.',
        icon: '/pwa-icon-192.png',
        badge: '/pwa-icon-192.png',
        vibrate: [100, 50, 100],
        data: { url: data.url || '/' },
        actions: [
            { action: 'open', title: 'Open App' },
            { action: 'close', title: 'Dismiss' },
        ],
    };
    event.waitUntil(
        self.registration.showNotification(data.title || 'WorkeX', options)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    if (event.action === 'close') return;
    const targetUrl = event.notification.data?.url || '/';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url === targetUrl && 'focus' in client) return client.focus();
            }
            if (clients.openWindow) return clients.openWindow(targetUrl);
        })
    );
});
