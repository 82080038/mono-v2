/**
 * KSP Lam Gabe Jaya - Service Worker
 * Development-Safe PWA Implementation
 */

const CACHE_NAME = 'ksp-lamgabejaya-v1';
const STATIC_CACHE = 'ksp-static-v1';
const DYNAMIC_CACHE = 'ksp-dynamic-v1';

// Environment detection
const isDevelopment = self.location.hostname === 'localhost' || 
                     self.location.hostname === '127.0.0.1';

// Files to cache (production only)
const STATIC_ASSETS = [
    '/',
    '/index.php',
    '/login.php',
    '/main.php',
    '/manifest.json',
    '/assets/css/main.css',
    '/assets/js/main.js',
    '/assets/icons/icon-192x192.png',
    '/assets/icons/icon-512x512.png'
];

// API endpoints to cache (production only)
const API_ENDPOINTS = [
    '/api/auth.php',
    '/api/logout.php',
    '/api/dashboard.php'
];

// Install event
self.addEventListener('install', event => {
    console.log('[SW] Installing service worker...');
    
    if (isDevelopment) {
        console.log('[SW] Development mode - skipping cache installation');
        self.skipWaiting();
        return;
    }
    
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                console.log('[SW] Static assets cached successfully');
                self.skipWaiting();
            })
            .catch(error => {
                console.error('[SW] Failed to cache static assets:', error);
            })
    );
});

// Activate event
self.addEventListener('activate', event => {
    console.log('[SW] Activating service worker...');
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames
                        .filter(cacheName => 
                            cacheName !== STATIC_CACHE && 
                            cacheName !== DYNAMIC_CACHE
                        )
                        .map(cacheName => {
                            console.log('[SW] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        })
                );
            })
            .then(() => {
                console.log('[SW] Service worker activated');
                return self.clients.claim();
            })
    );
});

// Fetch event
self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);
    
    // Development mode: Always fetch from network
    if (isDevelopment) {
        console.log('[SW] Development mode - fetching from network:', request.url);
        event.respondWith(fetch(request));
        return;
    }
    
    // Production mode: Network-first with cache fallback
    console.log('[SW] Production mode - handling fetch:', request.url);
    
    // Handle API requests
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(handleAPIRequest(request));
        return;
    }
    
    // Handle static assets
    if (STATIC_ASSETS.some(asset => url.pathname === new URL(asset, self.location.origin).pathname)) {
        event.respondWith(handleStaticRequest(request));
        return;
    }
    
    // Handle navigation requests
    if (request.mode === 'navigate') {
        event.respondWith(handleNavigationRequest(request));
        return;
    }
    
    // Default: Network-first with cache fallback
    event.respondWith(handleDefaultRequest(request));
});

// Handle API requests
async function handleAPIRequest(request) {
    try {
        // Try network first for API requests
        const response = await fetch(request);
        
        if (response.ok) {
            // Cache successful API responses
            const responseClone = response.clone();
            caches.open(DYNAMIC_CACHE)
                .then(cache => cache.put(request, responseClone));
        }
        
        return response;
    } catch (error) {
        console.log('[SW] Network failed for API, trying cache:', request.url);
        
        // Try cache for API requests
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Return offline API response
        return new Response(
            JSON.stringify({
                success: false,
                error: 'Offline mode - API unavailable',
                offline: true
            }),
            {
                status: 503,
                headers: {
                    'Content-Type': 'application/json'
                }
            }
        );
    }
}

// Handle static assets
async function handleStaticRequest(request) {
    try {
        // Try network first
        const response = await fetch(request);
        
        if (response.ok) {
            // Cache successful responses
            const responseClone = response.clone();
            caches.open(STATIC_CACHE)
                .then(cache => cache.put(request, responseClone));
        }
        
        return response;
    } catch (error) {
        console.log('[SW] Network failed for static asset, trying cache:', request.url);
        
        // Try cache
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Return offline page for navigation requests
        if (request.mode === 'navigate') {
            return caches.match('/offline.html') || new Response('Offline', { status: 503 });
        }
        
        throw error;
    }
}

// Handle navigation requests
async function handleNavigationRequest(request) {
    try {
        // Try network first
        const response = await fetch(request);
        
        if (response.ok) {
            // Cache successful navigation responses
            const responseClone = response.clone();
            caches.open(DYNAMIC_CACHE)
                .then(cache => cache.put(request, responseClone));
        }
        
        return response;
    } catch (error) {
        console.log('[SW] Network failed for navigation, trying cache:', request.url);
        
        // Try cache
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Return cached index page as fallback
        const indexPage = await caches.match('/main.php');
        if (indexPage) {
            return indexPage;
        }
        
        // Return offline page
        return new Response('Offline - Please check your connection', { status: 503 });
    }
}

// Handle default requests
async function handleDefaultRequest(request) {
    try {
        // Try network first
        const response = await fetch(request);
        
        if (response.ok) {
            // Cache successful responses
            const responseClone = response.clone();
            caches.open(DYNAMIC_CACHE)
                .then(cache => cache.put(request, responseClone));
        }
        
        return response;
    } catch (error) {
        console.log('[SW] Network failed, trying cache:', request.url);
        
        // Try cache
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        throw error;
    }
}

// Message event
self.addEventListener('message', event => {
    const data = event.data;
    
    switch (data.type) {
        case 'SKIP_WAITING':
            self.skipWaiting();
            break;
            
        case 'GET_VERSION':
            event.ports[0].postMessage({ version: CACHE_NAME });
            break;
            
        case 'FORCE_REFRESH':
            self.skipWaiting();
            event.ports[0].postMessage({ refreshed: true });
            break;
            
        default:
            console.log('[SW] Unknown message type:', data.type);
    }
});

// Background sync (production only)
self.addEventListener('sync', event => {
    if (isDevelopment) {
        console.log('[SW] Development mode - skipping background sync');
        return;
    }
    
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

// Background sync function
async function doBackgroundSync() {
    try {
        // Get pending actions from IndexedDB
        const pendingActions = await getPendingActions();
        
        for (const action of pendingActions) {
            try {
                // Retry the action
                await fetch(action.url, action.options);
                
                // Remove from pending actions
                await removePendingAction(action.id);
                
                console.log('[SW] Background sync successful for:', action.id);
            } catch (error) {
                console.error('[SW] Background sync failed for:', action.id, error);
            }
        }
    } catch (error) {
        console.error('[SW] Background sync error:', error);
    }
}

// IndexedDB helpers for background sync
async function getPendingActions() {
    // This would interact with IndexedDB to get pending actions
    // For now, return empty array
    return [];
}

async function removePendingAction(id) {
    // This would remove the action from IndexedDB
    console.log('[SW] Remove pending action:', id);
}

// Push notification (production only)
self.addEventListener('push', event => {
    if (isDevelopment) {
        console.log('[SW] Development mode - skipping push notification');
        return;
    }
    
    const options = {
        body: event.data.text(),
        icon: '/assets/icons/icon-192x192.png',
        badge: '/assets/icons/badge-72x72.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Lihat Detail',
                icon: '/assets/icons/checkmark.png'
            },
            {
                action: 'close',
                title: 'Tutup',
                icon: '/assets/icons/xmark.png'
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification('KSP Lam Gabe Jaya', options)
    );
});

// Notification click
self.addEventListener('notificationclick', event => {
    console.log('[SW] Notification click:', event.notification.data);
    
    event.notification.close();
    
    if (event.action === 'explore') {
        // Open the app to specific page
        event.waitUntil(
            clients.openWindow('/main.php')
        );
    } else if (event.action === 'close') {
        // Just close notification
        event.notification.close();
    } else {
        // Default: Open app
        event.waitUntil(
            clients.openWindow('/main.php')
        );
    }
});

console.log('[SW] Service worker loaded - Mode:', isDevelopment ? 'Development' : 'Production');
