// PWA Installation Script
let deferredPrompt;
let installButton;

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    showInstallButton();
});

window.addEventListener('appinstalled', (e) => {
    hideInstallButton();
    console.log('PWA was installed');
});

function showInstallButton() {
    if (!installButton) {
        installButton = document.createElement('div');
        installButton.className = 'pwa-install-prompt';
        installButton.innerHTML = '<span>Install KSP Lam Gabe Jaya App</span><button id="install-btn">Install</button><button id="dismiss-btn">Later</button>';
        
        document.body.appendChild(installButton);
        
        document.getElementById('install-btn').addEventListener('click', installApp);
        document.getElementById('dismiss-btn').addEventListener('click', hideInstallButton);
    }
}

function hideInstallButton() {
    if (installButton) {
        installButton.remove();
        installButton = null;
    }
}

function installApp() {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted the install prompt');
            } else {
                console.log('User dismissed the install prompt');
            }
            deferredPrompt = null;
            hideInstallButton();
        });
    }
}

// Development mode cache control
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    console.log('Development mode detected - cache control enabled');
    
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            registrations.forEach(function(registration) {
                registration.unregister();
            });
        });
    }
}
