/**
 * PWA Manager - Enhanced PWA Features with Development Safety
 * Handles service worker registration, push notifications, and install prompts
 */
class PWAManager {
    constructor() {
        this.isDevelopment = location.hostname === 'localhost' || location.hostname === '127.0.0.1';
        this.deferredPrompt = null;
        this.swRegistration = null;
        this.isOnline = navigator.onLine;
        
        this.init();
    }
    
    async init() {
        // Register service worker
        await this.registerServiceWorker();
        
        // Setup install prompt
        this.setupInstallPrompt();
        
        // Setup online/offline detection
        this.setupConnectivityDetection();
        
        // Setup push notifications (only in production)
        if (!this.isDevelopment) {
            await this.setupPushNotifications();
        }
        
        console.log('[PWA] PWA Manager initialized');
    }
    
    async registerServiceWorker() {
        // Skip service worker registration in development to prevent caching issues
        if (this.isDevelopment) {
            console.log('[PWA] Service worker registration skipped in development mode');
            // Also unregister any existing service workers
            await this.unregisterAllServiceWorkers();
            return;
        }
        
        if ('serviceWorker' in navigator) {
            try {
                this.swRegistration = await navigator.serviceWorker.register('/sw.js');
                
                // Listen for updates
                this.swRegistration.addEventListener('updatefound', () => {
                    console.log('[PWA] New service worker found');
                    const newWorker = this.swRegistration.installing;
                    
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            this.showUpdateAvailable();
                        }
                    });
                });
                
                console.log('[PWA] Service worker registered successfully');
            } catch (error) {
                console.error('[PWA] Service worker registration failed:', error);
            }
        }
    }
    
    async unregisterAllServiceWorkers() {
        if ('serviceWorker' in navigator) {
            const registrations = await navigator.serviceWorker.getRegistrations();
            console.log(`[PWA] Unregistering ${registrations.length} service workers in development`);
            
            for (let registration of registrations) {
                await registration.unregister();
                console.log('[PWA] Unregistered service worker:', registration.scope);
            }
        }
    }
    
    setupInstallPrompt() {
        // Listen for beforeinstallprompt event
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the mini-infobar from appearing on mobile
            e.preventDefault();
            // Stash the event so it can be triggered later
            this.deferredPrompt = e;
            
            // Show install prompt after user interaction
            if (!this.isDevelopment) {
                setTimeout(() => this.showInstallPrompt(), 5000);
            }
        });
        
        // Listen for app installed event
        window.addEventListener('appinstalled', () => {
            console.log('[PWA] App installed successfully');
            this.deferredPrompt = null;
            this.showInstallSuccess();
        });
    }
    
    setupConnectivityDetection() {
        window.addEventListener('online', () => {
            this.isOnline = true;
            console.log('[PWA] Connection restored');
            this.showConnectionStatus('online');
            
            // Trigger background sync
            if (this.swRegistration) {
                this.swRegistration.sync.register('background-sync');
            }
        });
        
        window.addEventListener('offline', () => {
            this.isOnline = false;
            console.log('[PWA] Connection lost');
            this.showConnectionStatus('offline');
        });
    }
    
    async setupPushNotifications() {
        if (!('Notification' in window) || !('PushManager' in window)) {
            console.log('[PWA] Push notifications not supported');
            return;
        }
        
        try {
            // Request notification permission
            const permission = await Notification.requestPermission();
            
            if (permission === 'granted') {
                console.log('[PWA] Notification permission granted');
                
                // Subscribe to push notifications
                if (this.swRegistration) {
                    const subscription = await this.swRegistration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: this.urlBase64ToUint8Array(this.getVapidPublicKey())
                    });
                    
                    // Send subscription to server
                    await this.sendSubscriptionToServer(subscription);
                    console.log('[PWA] Push notifications subscribed');
                }
            } else {
                console.log('[PWA] Notification permission denied');
            }
        } catch (error) {
            console.error('[PWA] Push notification setup failed:', error);
        }
    }
    
    showInstallPrompt() {
        if (this.deferredPrompt && !localStorage.getItem('pwa-install-dismissed')) {
            const installBanner = this.createInstallBanner();
            document.body.appendChild(installBanner);
        }
    }
    
    createInstallBanner() {
        const banner = document.createElement('div');
        banner.className = 'pwa-install-banner';
        banner.innerHTML = `
            <div class="pwa-install-content">
                <div class="pwa-install-icon">
                    <i class="fas fa-download"></i>
                </div>
                <div class="pwa-install-text">
                    <h6>Install KSP Lam Gabe Jaya</h6>
                    <p>Install app for better experience & offline access</p>
                </div>
                <div class="pwa-install-actions">
                    <button class="btn btn-sm btn-primary" id="pwa-install-btn">
                        <i class="fas fa-download me-1"></i> Install
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="pwa-dismiss-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        
        // Add styles
        const style = document.createElement('style');
        style.textContent = `
            .pwa-install-banner {
                position: fixed;
                bottom: 20px;
                left: 20px;
                right: 20px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                z-index: 9999;
                transform: translateY(100px);
                opacity: 0;
                transition: all 0.3s ease;
                max-width: 400px;
                margin: 0 auto;
            }
            .pwa-install-banner.show {
                transform: translateY(0);
                opacity: 1;
            }
            .pwa-install-content {
                display: flex;
                align-items: center;
                padding: 16px;
                gap: 12px;
            }
            .pwa-install-icon {
                background: #007bff;
                color: white;
                width: 40px;
                height: 40px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            .pwa-install-text h6 {
                margin: 0;
                font-size: 14px;
                font-weight: 600;
            }
            .pwa-install-text p {
                margin: 4px 0 0 0;
                font-size: 12px;
                color: #666;
            }
            .pwa-install-actions {
                display: flex;
                gap: 8px;
                margin-left: auto;
            }
        `;
        document.head.appendChild(style);
        
        // Show banner with animation
        setTimeout(() => banner.classList.add('show'), 100);
        
        // Handle install button
        document.getElementById('pwa-install-btn').addEventListener('click', async () => {
            await this.installApp();
            banner.remove();
        });
        
        // Handle dismiss button
        document.getElementById('pwa-dismiss-btn').addEventListener('click', () => {
            localStorage.setItem('pwa-install-dismissed', 'true');
            banner.remove();
        });
        
        return banner;
    }
    
    async installApp() {
        if (!this.deferredPrompt) return;
        
        // Show the install prompt
        this.deferredPrompt.prompt();
        
        // Wait for the user to respond to the prompt
        const { outcome } = await this.deferredPrompt.userChoice;
        
        if (outcome === 'accepted') {
            console.log('[PWA] User accepted the install prompt');
        } else {
            console.log('[PWA] User dismissed the install prompt');
        }
        
        // Clear the deferred prompt
        this.deferredPrompt = null;
    }
    
    showInstallSuccess() {
        this.showNotification('App Installed!', 'KSP Lam Gabe Jaya is now installed on your device', 'success');
    }
    
    showUpdateAvailable() {
        const updateBanner = document.createElement('div');
        updateBanner.className = 'pwa-update-banner';
        updateBanner.innerHTML = `
            <div class="pwa-update-content">
                <div class="pwa-update-icon">
                    <i class="fas fa-sync"></i>
                </div>
                <div class="pwa-update-text">
                    <h6>Update Available</h6>
                    <p>A new version of the app is available</p>
                </div>
                <div class="pwa-update-actions">
                    <button class="btn btn-sm btn-primary" id="pwa-update-btn">
                        <i class="fas fa-sync me-1"></i> Update
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="pwa-update-dismiss">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        
        // Add styles (reuse install banner styles)
        document.body.appendChild(updateBanner);
        
        // Handle update button
        document.getElementById('pwa-update-btn').addEventListener('click', () => {
            window.location.reload();
        });
        
        // Handle dismiss button
        document.getElementById('pwa-update-dismiss').addEventListener('click', () => {
            updateBanner.remove();
        });
    }
    
    showConnectionStatus(status) {
        const statusElement = document.getElementById('connection-status');
        
        if (status === 'online') {
            if (statusElement) {
                statusElement.className = 'connection-status online';
                statusElement.innerHTML = '<i class="fas fa-wifi"></i> Online';
            }
        } else {
            if (!statusElement) {
                const newStatusElement = document.createElement('div');
                newStatusElement.id = 'connection-status';
                newStatusElement.className = 'connection-status offline';
                newStatusElement.innerHTML = '<i class="fas fa-wifi-slash"></i> Offline';
                document.body.appendChild(newStatusElement);
            }
        }
    }
    
    showNotification(title, message, type = 'info') {
        if ('Notification' in window && Notification.permission === 'granted') {
            const notification = new Notification(title, {
                body: message,
                icon: '/icons/icon-192x192.png',
                badge: '/icons/badge-72x72.png',
                tag: 'ksp-notification'
            });
            
            setTimeout(() => notification.close(), 5000);
        }
    }
    
    async sendSubscriptionToServer(subscription) {
        try {
            await fetch('/api/push-subscription.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(subscription)
            });
        } catch (error) {
            console.error('[PWA] Failed to send subscription to server:', error);
        }
    }
    
    getVapidPublicKey() {
        // In production, this should come from your server
        return 'BMvY8QqLqR5GjNnBqQZqQZqQZqQZqQZqQZqQZqQZqQZqQZqQZqQZqQZqQZqQZqQZqQ';
    }
    
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        
        return outputArray;
    }
    
    // Public methods for manual control
    async requestNotificationPermission() {
        if ('Notification' in window) {
            return await Notification.requestPermission();
        }
        return 'denied';
    }
    
    async showInstallPromptManually() {
        if (this.deferredPrompt) {
            await this.installApp();
            return true;
        }
        return false;
    }
    
    isAppInstalled() {
        return window.matchMedia('(display-mode: standalone)').matches ||
               window.navigator.standalone === true;
    }
    
    getConnectionStatus() {
        return this.isOnline;
    }
}

// Initialize PWA Manager when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.pwaManager = new PWAManager();
    });
} else {
    window.pwaManager = new PWAManager();
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PWAManager;
}
