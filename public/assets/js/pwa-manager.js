/**
 * KSP Lam Gabe Jaya - PWA Manager
 * Progressive Web App management with development safety
 */

class PWAManager {
    constructor() {
        this.isDevelopment = this.detectDevelopment();
        this.isPWAEnabled = !this.isDevelopment && this.checkPWASupport();
        this.config = this.getPWAConfig();
        
        console.log('[PWA] Mode:', this.isDevelopment ? 'Development' : 'Production');
        console.log('[PWA] PWA Enabled:', this.isPWAEnabled);
        console.log('[PWA] Config:', this.config);
    }
    
    /**
     * Detect if running in development environment
     */
    detectDevelopment() {
        return window.location.hostname === 'localhost' || 
               window.location.hostname === '127.0.0.1' ||
               window.location.search.includes('dev=true') ||
               window.location.port === '3000' || // Common dev ports
               window.location.port === '8080';
    }
    
    /**
     * Check PWA support
     */
    checkPWASupport() {
        return 'serviceWorker' in navigator && 
               'PushManager' in window &&
               'Notification' in window;
    }
    
    /**
     * Get PWA configuration based on environment
     */
    getPWAConfig() {
        const configs = {
            development: {
                serviceWorker: false,
                offlineMode: false,
                pushNotifications: false,
                backgroundSync: false,
                installPrompt: false,
                debugMode: true
            },
            staging: {
                serviceWorker: true,
                offlineMode: false,
                pushNotifications: false,
                backgroundSync: false,
                installPrompt: true,
                debugMode: true
            },
            production: {
                serviceWorker: true,
                offlineMode: true,
                pushNotifications: true,
                backgroundSync: true,
                installPrompt: true,
                debugMode: false
            }
        };
        
        const env = this.isDevelopment ? 'development' : 'production';
        return configs[env];
    }
    
    /**
     * Initialize PWA features
     */
    async init() {
        if (!this.isPWAEnabled) {
            console.log('[PWA] PWA features disabled');
            this.showDevelopmentInfo();
            return;
        }
        
        try {
            // Initialize service worker
            if (this.config.serviceWorker) {
                await this.initServiceWorker();
            }
            
            // Initialize install prompt
            if (this.config.installPrompt) {
                this.initInstallPrompt();
            }
            
            // Initialize push notifications
            if (this.config.pushNotifications) {
                await this.initPushNotifications();
            }
            
            // Initialize background sync
            if (this.config.backgroundSync) {
                this.initBackgroundSync();
            }
            
            console.log('[PWA] PWA features initialized successfully');
            
        } catch (error) {
            console.error('[PWA] Failed to initialize PWA features:', error);
        }
    }
    
    /**
     * Initialize service worker
     */
    async initServiceWorker() {
        try {
            const registration = await navigator.serviceWorker.register('/sw.js');
            console.log('[PWA] Service Worker registered:', registration);
            
            // Check for updates
            registration.addEventListener('updatefound', () => {
                this.checkForUpdates(registration);
            });
            
            // Listen for controlling service worker
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                console.log('[PWA] Service Worker controller changed');
                this.showUpdatePrompt();
            });
            
            return registration;
            
        } catch (error) {
            console.error('[PWA] Service Worker registration failed:', error);
            throw error;
        }
    }
    
    /**
     * Check for service worker updates
     */
    checkForUpdates(registration) {
        const newWorker = registration.installing;
        
        newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                console.log('[PWA] New version available');
                this.showUpdateNotification();
            }
        });
    }
    
    /**
     * Initialize install prompt
     */
    initInstallPrompt() {
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (event) => {
            console.log('[PWA] Install prompt available');
            event.preventDefault();
            deferredPrompt = event;
            
            // Show custom install button
            this.showInstallButton(deferredPrompt);
        });
        
        // Handle install button click
        window.addEventListener('click', (event) => {
            if (event.target.classList.contains('pwa-install-btn')) {
                this.installApp(deferredPrompt);
            }
        });
    }
    
    /**
     * Install PWA
     */
    async installApp(deferredPrompt) {
        if (!deferredPrompt) {
            console.log('[PWA] Install prompt not available');
            return;
        }
        
        try {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                console.log('[PWA] App installed successfully');
                this.showInstallSuccess();
            } else {
                console.log('[PWA] App installation declined');
            }
            
            deferredPrompt = null;
            
        } catch (error) {
            console.error('[PWA] App installation failed:', error);
        }
    }
    
    /**
     * Initialize push notifications
     */
    async initPushNotifications() {
        try {
            // Request notification permission
            const permission = await Notification.requestPermission();
            
            if (permission === 'granted') {
                console.log('[PWA] Notification permission granted');
                
                // Subscribe to push notifications
                const subscription = await this.subscribeToPush();
                console.log('[PWA] Push subscription:', subscription);
                
                // Send subscription to server
                await this.sendPushSubscription(subscription);
                
            } else {
                console.log('[PWA] Notification permission denied');
            }
            
        } catch (error) {
            console.error('[PWA] Push notification initialization failed:', error);
        }
    }
    
    /**
     * Subscribe to push notifications
     */
    async subscribeToPush() {
        if (!('PushManager' in window)) {
            throw new Error('Push notifications not supported');
        }
        
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: this.urlBase64ToUint8Array('your-public-vapid-key')
        });
        
        return subscription;
    }
    
    /**
     * Send push subscription to server
     */
    async sendPushSubscription(subscription) {
        try {
            const response = await fetch('/api/push-subscription.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(subscription)
            });
            
            if (!response.ok) {
                throw new Error('Failed to send push subscription');
            }
            
            console.log('[PWA] Push subscription sent to server');
            
        } catch (error) {
            console.error('[PWA] Failed to send push subscription:', error);
        }
    }
    
    /**
     * Initialize background sync
     */
    initBackgroundSync() {
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            console.log('[PWA] Background sync supported');
            
            // Register background sync for different events
            this.registerBackgroundSync('background-sync');
            this.registerBackgroundSync('pending-transactions');
            this.registerBackgroundSync('offline-actions');
        }
    }
    
    /**
     * Register background sync
     */
    registerBackgroundSync(tag) {
        navigator.serviceWorker.ready.then(registration => {
            return registration.sync.register(tag);
        }).then(() => {
            console.log(`[PWA] Background sync registered: ${tag}`);
        }).catch(error => {
            console.error(`[PWA] Background sync registration failed: ${tag}`, error);
        });
    }
    
    /**
     * Show development info
     */
    showDevelopmentInfo() {
        if (!this.config.debugMode) return;
        
        const info = document.createElement('div');
        info.className = 'alert alert-info alert-dismissible fade show position-fixed top-0 end-0 m-3';
        info.style.zIndex = '9999';
        info.innerHTML = `
            <strong>Development Mode</strong><br>
            <small>PWA features disabled for development</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(info);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            if (info.parentNode) {
                info.remove();
            }
        }, 5000);
    }
    
    /**
     * Show update notification
     */
    showUpdateNotification() {
        const notification = document.createElement('div');
        notification.className = 'alert alert-warning alert-dismissible fade show position-fixed top-0 end-0 m-3';
        notification.style.zIndex = '9999';
        notification.innerHTML = `
            <strong>Update Available</strong><br>
            <small>New version of the app is available</small>
            <button type="button" class="btn btn-sm btn-warning ms-2" onclick="pwaManager.updateApp()">Update Now</button>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
    }
    
    /**
     * Show update prompt
     */
    showUpdatePrompt() {
        if (confirm('New version available. Update now?')) {
            this.updateApp();
        }
    }
    
    /**
     * Update app
     */
    updateApp() {
        window.location.reload();
    }
    
    /**
     * Show install button
     */
    showInstallButton(deferredPrompt) {
        const button = document.createElement('button');
        button.className = 'pwa-install-btn btn btn-primary position-fixed bottom-0 end-0 m-3';
        button.innerHTML = `
            <i class="fas fa-download me-2"></i>
            Install App
        `;
        
        document.body.appendChild(button);
    }
    
    /**
     * Show install success
     */
    showInstallSuccess() {
        const notification = document.createElement('div');
        notification.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
        notification.style.zIndex = '9999';
        notification.innerHTML = `
            <strong>App Installed!</strong><br>
            <small>You can now use the app offline</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
    
    /**
     * Check online status
     */
    checkOnlineStatus() {
        const isOnline = navigator.onLine;
        
        if (!isOnline) {
            this.showOfflineNotification();
        } else {
            this.hideOfflineNotification();
        }
        
        return isOnline;
    }
    
    /**
     * Show offline notification
     */
    showOfflineNotification() {
        const notification = document.createElement('div');
        notification.id = 'offline-notification';
        notification.className = 'alert alert-warning alert-dismissible fade show position-fixed top-0 start-0 m-3';
        notification.style.zIndex = '9999';
        notification.innerHTML = `
            <strong>Offline Mode</strong><br>
            <small>You're currently offline. Some features may be limited.</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        if (!document.getElementById('offline-notification')) {
            document.body.appendChild(notification);
        }
    }
    
    /**
     * Hide offline notification
     */
    hideOfflineNotification() {
        const notification = document.getElementById('offline-notification');
        if (notification) {
            notification.remove();
        }
    }
    
    /**
     * Get app version
     */
    async getAppVersion() {
        if (navigator.serviceWorker && navigator.serviceWorker.controller) {
            const messageChannel = new MessageChannel();
            
            return new Promise((resolve) => {
                messageChannel.port1.onmessage = (event) => {
                    resolve(event.data.version);
                };
                
                navigator.serviceWorker.controller.postMessage(
                    { type: 'GET_VERSION' },
                    [messageChannel.port2]
                );
            });
        }
        
        return null;
    }
    
    /**
     * Force refresh service worker
     */
    async forceRefresh() {
        if (navigator.serviceWorker && navigator.serviceWorker.controller) {
            const messageChannel = new MessageChannel();
            
            return new Promise((resolve) => {
                messageChannel.port1.onmessage = (event) => {
                    resolve(event.data.refreshed);
                };
                
                navigator.serviceWorker.controller.postMessage(
                    { type: 'FORCE_REFRESH' },
                    [messageChannel.port2]
                );
            });
        }
        
        return false;
    }
    
    /**
     * Utility: Convert URL base64 to Uint8Array
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');
        
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        
        return outputArray;
    }
    
    /**
     * Get PWA status
     */
    getPWAStatus() {
        return {
            isDevelopment: this.isDevelopment,
            isPWAEnabled: this.isPWAEnabled,
            isInstalled: this.isInstalled(),
            isOnline: navigator.onLine,
            config: this.config
        };
    }
    
    /**
     * Check if app is installed
     */
    isInstalled() {
        return window.matchMedia('(display-mode: standalone)').matches ||
               window.navigator.standalone === true;
    }
}

// Initialize PWA Manager
const pwaManager = new PWAManager();

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        pwaManager.init();
    });
} else {
    pwaManager.init();
}

// Monitor online/offline status
window.addEventListener('online', () => {
    pwaManager.checkOnlineStatus();
    console.log('[PWA] Online');
});

window.addEventListener('offline', () => {
    pwaManager.checkOnlineStatus();
    console.log('[PWA] Offline');
});

// Export for global use
window.pwaManager = pwaManager;
