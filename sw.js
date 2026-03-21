
const CACHE_NAME = 'ksp-lamgabe-v1';
const urlsToCache = [
    '/',
    '/index.html',
    '/pages/admin/login.html',
    '/pages/staff/login.html',
    '/pages/member/login.html',
    '/pages/admin/dashboard.html',
    '/pages/staff/dashboard.html',
    '/pages/member/dashboard.html'
];

// Install event
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
});

// Fetch event - only cache GET requests, don't interfere with development
self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);
    
    // Don't cache API calls or development requests
    if (request.method !== 'GET' || url.pathname.startsWith('/api/') || url.searchParams.has('dev')) {
        return;
    }
    
    event.respondWith(
        caches.match(request)
            .then(response => {
                // Return cached version or fetch from network
                return response || fetch(request);
            })
    );
});

// Activate event
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});
