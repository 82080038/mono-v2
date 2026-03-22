// Error Handling Wrapper
(function() {
    // Development Mode Configuration
const isDevelopment = window.location.hostname === 'localhost' || 
                     window.location.hostname === '127.0.0.1' ||
                     window.location.hostname.includes('dev');

if (isDevelopment) {
    console.log('🔧 Development Mode Detected');
    
    // Disable service worker cache in development
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            registrations.forEach(function(registration) {
                registration.unregister();
                console.log('🗑️ Service worker unregistered for development');
            });
        });
    }
    
    // Add development controls
    window.addEventListener('load', function() {
        const devControls = document.createElement('div');
        devControls.style.cssText = 'position: fixed; top: 10px; right: 10px; background: rgba(0,0,0,0.8); color: white; padding: 10px; border-radius: 5px; font-size: 12px; z-index: 10000;';
        devControls.innerHTML = '<div>🔧 Development Mode</div><button onclick="clearCache()" style="margin-top: 5px; padding: 2px 5px;">Clear Cache</button>';
        document.body.appendChild(devControls);
        
        window.clearCache = function() {
            if ('caches' in window) {
                caches.keys().then(function(names) {
                    names.forEach(function(name) {
                        caches.delete(name);
                    });
                    console.log('🗑️ All caches cleared');
                });
            }
        };
    });
    
    // Disable PWA install prompt in development
    window.addEventListener('beforeinstallprompt', function(e) {
        e.preventDefault();
        console.log('🔧 PWA install prompt disabled in development');
    });
} else {
    console.log('🚀 Production Mode - PWA enabled');
}

})();