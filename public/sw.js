const CACHE_NAME = 'ecosystem-cache-v1';
const OFFLINE_URL = '/offline.html';

const PRECACHE_ASSETS = [
    OFFLINE_URL,
    '/images/eclectic_logo_nobg.png',
    '/images/icons/icon-192.png',
    '/images/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_ASSETS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
        )).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    const url = new URL(request.url);
    if (url.origin === self.location.origin && /\.(png|jpg|jpeg|svg|ico|css|js|woff2?)$/.test(url.pathname)) {
        event.respondWith(
            caches.match(request).then((cached) => cached || fetch(request).then((response) => {
                const clone = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                return response;
            }))
        );
    }
});

self.addEventListener('push', (event) => {
    let payload = {};
    try {
        payload = event.data ? event.data.json() : {};
    } catch (e) {
        payload = { title: 'Notifikasi Baru', body: event.data ? event.data.text() : '' };
    }

    const title = payload.title || 'Notifikasi Baru';
    const options = {
        body: payload.body || '',
        icon: '/images/icons/icon-192.png',
        badge: '/images/icons/icon-192.png',
        tag: payload.tag || undefined,
        data: { url: payload.url || '/notifications' },
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

// Temporary shim: LiteApi's push_subscriptions table is shared with the main
// app, which still emits the old /ticket/{id}?msg={msgId} link format. Rewrite
// it to this app's /tickets/{id}?highlight_message_id={msgId} route until the
// backend is updated to send the new format directly.
function normalizeNotificationUrl(rawUrl) {
    let parsed;
    try {
        parsed = new URL(rawUrl, self.location.origin);
    } catch (e) {
        return rawUrl;
    }

    const match = parsed.pathname.match(/^\/ticket\/(\d+)\/?$/);
    if (!match) {
        return rawUrl;
    }

    const ticketId = match[1];
    const msgId = parsed.searchParams.get('msg');

    let normalized = `/tickets/${ticketId}`;
    if (msgId) {
        normalized += `?highlight_message_id=${encodeURIComponent(msgId)}`;
    }

    return normalized;
}

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const rawUrl = (event.notification.data && event.notification.data.url) || '/notifications';
    const targetUrl = normalizeNotificationUrl(rawUrl);

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
            for (const client of clients) {
                if (client.url.includes(new URL(targetUrl, self.location.origin).pathname) && 'focus' in client) {
                    return client.focus();
                }
            }

            if (clients.length > 0 && 'focus' in clients[0]) {
                return clients[0].focus().then((client) => client.navigate(targetUrl));
            }

            return self.clients.openWindow(targetUrl);
        })
    );
});
