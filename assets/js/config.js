/**
 * KSP Lam Gabe Jaya - Dynamic Configuration
 * Base path detection and configuration management
 */

// Dynamic Base Path Detection
(function() {
    'use strict';
    
    // Detect base path automatically
    function detectBasePath() {
        const currentPath = window.location.pathname;
        const pathParts = currentPath.split('/').filter(part => part.length > 0);
        
        // Remove filename if present (e.g., login.html)
        if (pathParts.length > 0 && pathParts[pathParts.length - 1].includes('.html')) {
            pathParts.pop(); // Remove the HTML file
        }
        
        // Find the application root by looking for common indicators
        let basePath = '';
        
        // Check if we're in a subdirectory structure
        if (currentPath.includes('/pages/')) {
            // We're in pages subdirectory, go up to root
            const pagesIndex = pathParts.indexOf('pages');
            if (pagesIndex > 0) {
                basePath = '/' + pathParts.slice(0, pagesIndex).join('/');
            }
        } else if (currentPath.includes('/api/')) {
            // We're in API subdirectory
            const apiIndex = pathParts.indexOf('api');
            if (apiIndex > 0) {
                basePath = '/' + pathParts.slice(0, apiIndex).join('/');
            }
        } else {
            // We're likely at root level
            basePath = '/' + pathParts.join('/');
        }
        
        // Ensure base path doesn't end with slash
        return basePath.replace(/\/$/, '') || '';
    }
    
    // Configuration object
    window.APP_CONFIG = {
        // Base path (auto-detected)
        BASE_PATH: detectBasePath(),
        
        // API endpoints
        API: {
            AUTH: 'api/auth.php',
            USERS: 'api/users.php',
            MEMBERS: 'api/members.php',
            LOANS: 'api/loans.php',
            SAVINGS: 'api/savings.php',
            TRANSACTIONS: 'api/transactions.php',
            REPORTS: 'api/reports.php'
        },
        
        // Page routes
        ROUTES: {
            LOGIN: 'login.html',
            DASHBOARD: {
                SUPER_ADMIN: 'pages/admin/dashboard-new.html',
                ADMIN: 'pages/admin/dashboard-new.html',
                MANTRI: 'pages/staff/dashboard.html',
                MEMBER: 'pages/member/dashboard.html',
                KASIR: 'pages/staff/dashboard.html',
                TELLER: 'pages/staff/dashboard.html',
                SURVEYOR: 'pages/staff/dashboard.html',
                COLLECTOR: 'pages/staff/dashboard.html'
            }
        },
        
        // Asset paths
        ASSETS: {
            CSS: 'assets/css',
            JS: 'assets/js',
            IMAGES: 'assets/images',
            FONTS: 'assets/fonts'
        },
        
        // Application settings
        APP: {
            NAME: 'KSP Lam Gabe Jaya',
            VERSION: '2.0',
            DEBUG: true,
            TOKEN_EXPIRY: 3600, // 1 hour
            SESSION_TIMEOUT: 30 * 60 * 1000 // 30 minutes
        }
    };
    
    // Helper function to build URLs
    window.buildUrl = function(path) {
        if (path.startsWith('http')) {
            return path; // Already absolute URL
        }
        
        const basePath = window.APP_CONFIG.BASE_PATH;
        const cleanPath = path.startsWith('/') ? path.slice(1) : path;
        
        return basePath ? `${basePath}/${cleanPath}` : cleanPath;
    };
    
    // Helper function to build API URLs
    window.buildApiUrl = function(endpoint) {
        const apiPath = window.APP_CONFIG.API[endpoint] || endpoint;
        return window.buildUrl(apiPath);
    };
    
    // Helper function to build page URLs
    window.buildPageUrl = function(page) {
        const pagePath = window.APP_CONFIG.ROUTES[page] || page;
        return window.buildUrl(pagePath);
    };
    
    // Debug information (remove in production)
    if (false && window.APP_CONFIG.APP.DEBUG) {
        console.log('APP_CONFIG:', window.APP_CONFIG);
        console.log('Detected BASE_PATH:', window.APP_CONFIG.BASE_PATH);
        console.log('Sample URLs:', {
            login: window.buildUrl('login.html'),
            api: window.buildApiUrl('AUTH'),
            dashboard: window.buildPageUrl('DASHBOARD.ADMIN')
        });
    }
})();
