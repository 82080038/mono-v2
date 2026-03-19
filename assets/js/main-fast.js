/**
 * KSP Lam Gabe Jaya - Fast Main JavaScript Module
 * Optimized for speed, minimal initialization
 */

// Quick app initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('KSP Lam Gabe Jaya v2.0 - Fast Loading');

    // Basic page detection
    const path = window.location.pathname;
    const page = path.includes('login') ? 'login' :
                 path.includes('dashboard') ? 'dashboard' : 'home';

    console.log('Page:', page);

    // Check auth status
    const token = localStorage.getItem('authToken') || sessionStorage.getItem('authToken');
    console.log('Auth status:', token ? 'Logged in' : 'Not logged in');

    // Initialize tooltips if Bootstrap loaded
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    console.log('Application ready');
});
