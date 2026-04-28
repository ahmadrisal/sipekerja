const CACHE_NAME = 'pakar-v1';

// CDN assets to cache on install
const CDN_ASSETS = [
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@300;400;500;600;700&display=swap',
    'https://cdn.tailwindcss.com',
    'https://cdn.jsdelivr.net/npm/apexcharts',
];

// Install: cache CDN assets
self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.allSettled(
                CDN_ASSETS.map(url => cache.add(url).catch(() => {}))
            );
        })
    );
});

// Activate: remove old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            )
        ).then(() => self.clients.claim())
    );
});

// Fetch: Network First strategy
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Only handle GET requests
    if (request.method !== 'GET') return;

    // Skip Livewire AJAX requests — let them fail naturally
    if (request.headers.get('X-Livewire')) return;

    const url = new URL(request.url);

    // For navigation requests (HTML pages): network first, fallback to offline page
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .catch(() => caches.match('/offline') || new Response('Offline', { status: 503 }))
        );
        return;
    }

    // For CDN assets: cache first
    if (url.origin !== self.location.origin) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) return cached;
                return fetch(request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(c => c.put(request, clone));
                    }
                    return response;
                }).catch(() => cached || new Response('', { status: 503 }));
            })
        );
        return;
    }

    // For same-origin static assets (icons, manifest): cache first
    if (url.pathname.startsWith('/icons/') || url.pathname === '/manifest.json' || url.pathname === '/favicon.ico') {
        event.respondWith(
            caches.match(request).then((cached) => {
                return cached || fetch(request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(c => c.put(request, clone));
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Everything else: network only
});
