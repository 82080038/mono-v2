
// KSP Lam Gabe Jaya PWA Manager
class PWAManager {
    constructor() {
        this.isInstalled = false;
        this.deferredPrompt = null;
        this.isOnline = navigator.onLine;
        
        this.init();
    }
    
    init() {
        // Check if app is installed
        this.checkInstallation();
        
        // Listen for beforeinstallprompt
        window.addEventListener("beforeinstallprompt", (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallPrompt();
        });
        
        // Listen for app installed
        window.addEventListener("appinstalled", () => {
            this.isInstalled = true;
            this.hideInstallPrompt();
            console.log("PWA installed successfully");
        });
        
        // Listen for online/offline events
        window.addEventListener("online", () => {
            this.isOnline = true;
            this.updateOnlineStatus();
            this.syncOfflineData();
        });
        
        window.addEventListener("offline", () => {
            this.isOnline = false;
            this.updateOnlineStatus();
        });
        
        // Register service worker
        this.registerServiceWorker();
        
        // Request notification permission
        this.requestNotificationPermission();
        
        // Initialize background sync
        this.initBackgroundSync();
    }
    
    checkInstallation() {
        // Check if running in standalone mode
        this.isInstalled = window.matchMedia("(display-mode: standalone)").matches;
        
        // Check if launched from home screen
        if (navigator.standalone) {
            this.isInstalled = true;
        }
    }
    
    showInstallPrompt() {
        if (this.isInstalled) return;
        
        const prompt = document.querySelector(".pwa-install-prompt");
        if (prompt) {
            prompt.classList.remove("hidden");
        }
    }
    
    hideInstallPrompt() {
        const prompt = document.querySelector(".pwa-install-prompt");
        if (prompt) {
            prompt.classList.add("hidden");
        }
    }
    
    async installApp() {
        if (!this.deferredPrompt) return;
        
        this.deferredPrompt.prompt();
        const { outcome } = await this.deferredPrompt.userChoice;
        
        if (outcome === "accepted") {
            console.log("User accepted install prompt");
        } else {
            console.log("User dismissed install prompt");
        }
        
        this.deferredPrompt = null;
        this.hideInstallPrompt();
    }
    
    registerServiceWorker() {
        if ("serviceWorker" in navigator) {
            navigator.serviceWorker.register("/sw.js")
                .then(registration => {
                    console.log("Service Worker registered:", registration.scope);
                })
                .catch(error => {
                    console.error("Service Worker registration failed:", error);
                });
        }
    }
    
    updateOnlineStatus() {
        const indicator = document.querySelector(".offline-indicator");
        if (indicator) {
            indicator.textContent = this.isOnline ? "Online" : "Offline";
            indicator.className = "offline-indicator " + (this.isOnline ? "online" : "offline");
        }
    }
    
    async requestNotificationPermission() {
        if ("Notification" in navigator) {
            const permission = await Notification.requestPermission();
            console.log("Notification permission:", permission);
        }
    }
    
    async showNotification(title, options = {}) {
        if ("Notification" in navigator && Notification.permission === "granted") {
            return navigator.serviceWorker.ready.then(registration => {
                return registration.showNotification(title, options);
            });
        }
    }
    
    initBackgroundSync() {
        if ("serviceWorker" in navigator && "sync" in window.ServiceWorkerRegistration.prototype) {
            navigator.serviceWorker.ready.then(registration => {
                return registration.sync.register("background-sync");
            });
        }
    }
    
    async syncOfflineData() {
        if ("serviceWorker" in navigator) {
            try {
                const registration = await navigator.serviceWorker.ready;
                await registration.sync.register("background-sync");
                console.log("Background sync triggered");
            } catch (error) {
                console.error("Background sync failed:", error);
            }
        }
    }
}

// Initialize PWA Manager
const pwaManager = new PWAManager();

// Global functions for easy access
window.installPWA = () => pwaManager.installApp();
window.showNotification = (title, options) => pwaManager.showNotification(title, options);
window.isPWAInstalled = () => pwaManager.isInstalled;
window.isOnline = () => pwaManager.isOnline;
