// KSP Lam Gabe Jaya Service Worker - Production Ready
const CACHE_NAME = "ksp-lamgabejaya-v1";
const urlsToCache = [
    "/",
    "/index.html",
    "/pages/admin/dashboard.html",
    "/pages/staff/dashboard.html",
    "/pages/member/dashboard.html",
    "/pages/creator/dashboard.html",
    "/assets/css/dashboard.css",
    "/assets/css/pwa.css",
    "/assets/js/api-helper.js",
    "/assets/js/pwa.js",
    "/api/auth-enhanced.php",
    "/api/member-dashboard.php",
    "/api/staff-dashboard.php",
    "/api/admin-dashboard.php",
    "/api/creator-dashboard.php"
];

// Install Service Worker
self.addEventListener("install", event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
            .then(() => self.skipWaiting())
    );
});

// Activate Service Worker
self.addEventListener("activate", event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Event - Network First Strategy
self.addEventListener("fetch", event => {
    const request = event.request;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== "GET") {
        return;
    }
    
    // API requests - Network First
    if (url.pathname.startsWith("/api/")) {
        event.respondWith(
            fetch(request)
                .then(response => {
                    // Cache successful API responses
                    if (response.ok) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME).then(cache => {
                            cache.put(request, responseClone);
                        });
                    }
                    return response;
                })
                .catch(() => {
                    // Try cache if network fails
                    return caches.match(request);
                })
        );
        return;
    }
    
    // Static files - Cache First
    event.respondWith(
        caches.match(request)
            .then(response => {
                return response || fetch(request);
            })
    );
});

// Background Sync
self.addEventListener("sync", event => {
    if (event.tag === "background-sync") {
        event.waitUntil(doBackgroundSync());
    }
});

// Push Notifications
self.addEventListener("push", event => {
    const options = {
        body: event.data.text(),
        icon: "/assets/images/logo.svg",
        badge: "/assets/images/logo.svg",
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: "explore",
                title: "View Details",
                icon: "/assets/images/checkmark.png"
            },
            {
                action: "close",
                title: "Close",
                icon: "/assets/images/xmark.png"
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification("KSP Lam Gabe Jaya", options)
    );
});

// Notification Click
self.addEventListener("notificationclick", event => {
    event.notification.close();
    
    if (event.action === "explore") {
        event.waitUntil(
            clients.openWindow("/pages/member/dashboard.html")
        );
    }
});

// Background Sync Function
async function doBackgroundSync() {
    try {
        // Sync offline data
        const cache = await caches.open(CACHE_NAME);
        const requests = await cache.keys();
        
        for (const request of requests) {
            if (request.url.includes("/api/")) {
                try {
                    await fetch(request);
                    console.log("Synced:", request.url);
                } catch (error) {
                    console.error("Sync failed:", request.url, error);
                }
            }
        }
    } catch (error) {
        console.error("Background sync error:", error);
    }
}
