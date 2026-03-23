/**
 * Dynamic Dashboard Renderer
 * Renders dashboard pages dynamically with error handling
 */

class DynamicDashboard {
    constructor() {
        this.apiBase = '/mono-v2/api';
        this.currentRole = null;
        this.dashboardPages = [];
        this.navigationMenu = [];
        this.missingPages = [];
        this.missingMenuItems = [];
    }

    /**
     * Initialize dashboard for user
     */
    async initialize(token) {
        try {
            console.log('[DynamicDashboard] Starting initialization...');
            this.currentRole = this.getRoleFromToken(token);
            console.log('[DynamicDashboard] Role detected:', this.currentRole);
            
            // Load dashboard pages
            console.log('[DynamicDashboard] Loading dashboard pages...');
            await this.loadDashboardPages(token);
            console.log('[DynamicDashboard] Dashboard pages loaded:', this.dashboardPages.length);
            
            // Load navigation menu
            console.log('[DynamicDashboard] Loading navigation menu...');
            await this.loadNavigationMenu(token);
            console.log('[DynamicDashboard] Navigation menu loaded:', this.navigationMenu.length);
            
            // Check for missing files
            this.checkMissingFiles();
            
            // Render dashboard
            console.log('[DynamicDashboard] Rendering dashboard...');
            this.renderDashboard();
            
            // Render navigation
            console.log('[DynamicDashboard] Rendering navigation...');
            this.renderNavigation();
            
            // Show errors if any
            this.showErrors();
            
            console.log('[DynamicDashboard] Initialization complete!');
            
        } catch (error) {
            console.error('[DynamicDashboard] Initialization failed:', error);
            this.showError('Failed to initialize dashboard: ' + error.message);
            throw error; // Re-throw so caller knows it failed
        }
    }

    /**
     * Get role from token
     */
    getRoleFromToken(token) {
        try {
            // Handle both JWT and simple tokens
            if (token.includes('.') && token.split('.').length === 3) {
                // JWT token format
                const payload = JSON.parse(atob(token.split('.')[1]));
                return payload.role || 'member';
            } else {
                // Simple token format - get from localStorage
                return localStorage.getItem('userRole') || 'member';
            }
        } catch (error) {
            console.error('Failed to parse token:', error);
            return localStorage.getItem('userRole') || 'member';
        }
    }

    /**
     * Load dashboard pages from API with timeout
     */
    async loadDashboardPages(token) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
        
        try {
            console.log('[DynamicDashboard] Loading dashboard pages with token:', token.substring(0, 20) + '...');
            
            const cleanToken = token.trim();
            
            const response = await fetch(`${this.apiBase}/dynamic-dashboard.php?action=get_dashboard&token=${encodeURIComponent(cleanToken)}`, {
                signal: controller.signal
            });
            clearTimeout(timeoutId);
            
            console.log('[DynamicDashboard] Dashboard API response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('[DynamicDashboard] Dashboard API response:', result);
            
            if (result.success) {
                this.dashboardPages = result.data.pages || [];
                this.missingPages = result.data.missing_pages || [];
            } else {
                throw new Error(result.message || 'Failed to load dashboard pages');
            }
        } catch (error) {
            clearTimeout(timeoutId);
            if (error.name === 'AbortError') {
                console.error('[DynamicDashboard] Dashboard API request timed out');
                throw new Error('Request timed out. Please check your connection.');
            }
            console.error('[DynamicDashboard] Failed to load dashboard pages:', error);
            this.showError('Failed to load dashboard pages: ' + error.message);
            throw error; // Re-throw so initialize knows it failed
        }
    }

    /**
     * Load navigation menu from API with timeout
     */
    async loadNavigationMenu(token) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
        
        try {
            console.log('[DynamicDashboard] Loading navigation menu...');
            
            const cleanToken = token.trim();
            
            const response = await fetch(`${this.apiBase}/dynamic-navigation.php?action=get_navigation&token=${encodeURIComponent(cleanToken)}`, {
                signal: controller.signal
            });
            clearTimeout(timeoutId);
            
            console.log('[DynamicDashboard] Navigation API response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('[DynamicDashboard] Navigation API response:', result);
            
            if (result.success) {
                this.navigationMenu = result.data.menu_tree || [];
                this.missingMenuItems = result.data.missing_files || [];
            } else {
                throw new Error(result.message || 'Failed to load navigation menu');
            }
        } catch (error) {
            clearTimeout(timeoutId);
            if (error.name === 'AbortError') {
                console.error('[DynamicDashboard] Navigation API request timed out');
                throw new Error('Request timed out. Please check your connection.');
            }
            console.error('[DynamicDashboard] Failed to load navigation menu:', error);
            this.showError('Failed to load navigation menu: ' + error.message);
            throw error; // Re-throw so initialize knows it failed
        }
    }

    /**
     * Check for missing files
     */
    checkMissingFiles() {
        // Log missing pages
        if (this.missingPages.length > 0) {
            console.warn('Missing dashboard pages:', this.missingPages);
        }
        
        // Log missing menu items
        if (this.missingMenuItems.length > 0) {
            console.warn('Missing menu items:', this.missingMenuItems);
        }
    }

    /**
     * Render dashboard pages
     */
    renderDashboard() {
        const dashboardContainer = document.getElementById('dashboardPages');
        if (!dashboardContainer) return;

        dashboardContainer.innerHTML = '';

        // Group pages by category
        const categorizedPages = this.groupByCategory(this.dashboardPages);
        
        Object.keys(categorizedPages).forEach(category => {
            const categorySection = this.createCategorySection(category, categorizedPages[category]);
            dashboardContainer.appendChild(categorySection);
        });
    }

    /**
     * Group pages by category
     */
    groupByCategory(pages) {
        const categorized = {};
        
        pages.forEach(page => {
            const category = page.page_category || 'general';
            if (!categorized[category]) {
                categorized[category] = [];
            }
            categorized[category].push(page);
        });
        
        return categorized;
    }

    /**
     * Create category section
     */
    createCategorySection(category, pages) {
        const section = document.createElement('div');
        section.className = 'dashboard-category mb-4';
        
        const categoryTitle = document.createElement('h3');
        categoryTitle.className = 'category-title text-uppercase mb-3';
        categoryTitle.textContent = this.formatCategoryName(category);
        section.appendChild(categoryTitle);
        
        const row = document.createElement('div');
        row.className = 'row';
        
        pages.forEach(page => {
            const pageCard = this.createPageCard(page);
            row.appendChild(pageCard);
        });
        
        section.appendChild(row);
        return section;
    }

    /**
     * Create page card
     */
    createPageCard(page) {
        const col = document.createElement('div');
        col.className = 'col-md-6 col-lg-4 mb-3';
        
        const card = document.createElement('div');
        card.className = 'card dashboard-card h-100';
        card.setAttribute('data-page-key', page.page_key);
        
        const cardBody = document.createElement('div');
        cardBody.className = 'card-body';
        
        const cardHeader = document.createElement('div');
        cardHeader.className = 'd-flex align-items-center mb-3';
        
        const icon = document.createElement('i');
        icon.className = `${page.page_icon} fa-2x text-primary me-3`;
        cardHeader.appendChild(icon);
        
        const title = document.createElement('h5');
        title.className = 'card-title mb-0';
        title.textContent = page.page_title;
        cardHeader.appendChild(title);
        
        cardBody.appendChild(cardHeader);
        
        const description = document.createElement('p');
        description.className = 'card-text text-muted';
        description.textContent = page.page_description;
        cardBody.appendChild(description);
        
        const accessBadge = this.createAccessBadge(page.access_level);
        cardBody.appendChild(accessBadge);
        
        const cardFooter = document.createElement('div');
        cardFooter.className = 'card-footer bg-transparent';
        
        const openButton = document.createElement('button');
        openButton.className = 'btn btn-primary btn-sm';
        openButton.innerHTML = '<i class="fas fa-external-link-alt me-1"></i> Open';
        openButton.onclick = () => this.openPage(page);
        
        cardFooter.appendChild(openButton);
        cardBody.appendChild(cardFooter);
        
        card.appendChild(cardBody);
        col.appendChild(card);
        
        return col;
    }

    /**
     * Create access level badge
     */
    createAccessBadge(accessLevel) {
        const badge = document.createElement('span');
        badge.className = 'badge bg-secondary me-2';
        
        switch (accessLevel) {
            case 'admin':
                badge.className = 'badge bg-danger';
                badge.textContent = 'Admin';
                break;
            case 'write':
                badge.className = 'badge bg-warning';
                badge.textContent = 'Write';
                break;
            case 'read':
                badge.className = 'badge bg-info';
                badge.textContent = 'Read';
                break;
            default:
                badge.textContent = accessLevel;
        }
        
        return badge;
    }

    /**
     * Format category name
     */
    formatCategoryName(category) {
        return category.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    /**
     * Open page
     */
    async openPage(page) {
        try {
            const token = localStorage.getItem('authToken');
            
            // Check if page exists and user has access
            const response = await fetch(`${this.apiBase}/dynamic-dashboard.php?action=get_page_content&page_key=${page.page_key}&token=${token}`);
            const result = await response.json();
            
            if (!result.success) {
                if (result.error_type === 'file_missing') {
                    this.showError(`Page not found: ${page.page_title}`, 'warning');
                } else {
                    this.showError(result.message, 'danger');
                }
                return;
            }
            
            // Open page in new window or current window with absolute URL
            if (page.page_url) {
                const absoluteUrl = '/mono-v2/' + page.page_url;
                window.open(absoluteUrl, '_blank');
            }
            
        } catch (error) {
            console.error('Failed to open page:', error);
            this.showError('Failed to open page: ' + error.message);
        }
    }

    /**
     * Render navigation menu
     */
    renderNavigation() {
        const navContainer = document.getElementById('dynamicNavigation');
        if (!navContainer) return;

        navContainer.innerHTML = '';

        this.navigationMenu.forEach(item => {
            const navItem = this.createNavItem(item);
            navContainer.appendChild(navItem);
        });
    }

    /**
     * Create navigation item
     */
    createNavItem(item) {
        const li = document.createElement('li');
        li.className = 'nav-item';
        
        const a = document.createElement('a');
        a.className = 'nav-link';
        
        // Fix URL to be absolute (prepend /mono-v2/)
        if (item.menu_url) {
            a.href = '/mono-v2/' + item.menu_url;
        } else {
            a.href = '#';
        }
        
        if (!item.file_exists) {
            a.className += ' disabled';
            a.title = item.file_error || 'Page not found';
        }
        
        if (item.menu_icon) {
            const icon = document.createElement('i');
            icon.className = `${item.menu_icon} me-2`;
            a.appendChild(icon);
        }
        
        const text = document.createTextNode(item.menu_title);
        a.appendChild(text);
        
        // Add access level indicator
        if (item.access_level !== 'read') {
            const badge = this.createAccessBadge(item.access_level);
            badge.className += ' ms-2';
            a.appendChild(badge);
        }
        
        li.appendChild(a);
        
        // Add children if any
        if (item.children && item.children.length > 0) {
            const subMenu = document.createElement('ul');
            subMenu.className = 'nav nav-pills flex-column ms-3';
            
            item.children.forEach(child => {
                const childItem = this.createNavItem(child);
                subMenu.appendChild(childItem);
            });
            
            li.appendChild(subMenu);
        }
        
        return li;
    }

    /**
     * Show errors
     */
    showErrors() {
        if (this.missingPages.length > 0 || this.missingMenuItems.length > 0) {
            const errorContainer = document.getElementById('errorContainer');
            if (errorContainer) {
                errorContainer.innerHTML = this.createErrorHTML();
                errorContainer.style.display = 'block';
            }
        }
    }

    /**
     * Create error HTML
     */
    createErrorHTML() {
        let html = '<div class="alert alert-warning alert-dismissible fade show" role="alert">';
        html += '<h5><i class="fas fa-exclamation-triangle me-2"></i>Missing Files Detected</h5>';
        
        if (this.missingPages.length > 0) {
            html += '<h6>Missing Dashboard Pages:</h6>';
            html += '<ul class="mb-2">';
            this.missingPages.forEach(page => {
                html += `<li>${page.page_title} - ${page.page_url}</li>`;
            });
            html += '</ul>';
        }
        
        if (this.missingMenuItems.length > 0) {
            html += '<h6>Missing Menu Items:</h6>';
            html += '<ul class="mb-2">';
            this.missingMenuItems.forEach(item => {
                html += `<li>${item.menu_title} - ${item.menu_url}</li>`;
            });
            html += '</ul>';
        }
        
        html += '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        html += '</div>';
        
        return html;
    }

    /**
     * Show error message
     */
    showError(message, type = 'danger') {
        const alertContainer = document.getElementById('alertContainer');
        if (alertContainer) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alert);
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a dashboard page
    if (document.getElementById('dashboardPages') || document.getElementById('dynamicNavigation')) {
        const token = localStorage.getItem('authToken');
        if (token) {
            // Validate token format before initializing
            const isValidToken = validateTokenFormat(token);
            if (isValidToken) {
                const dashboard = new DynamicDashboard();
                dashboard.initialize(token);
            } else {
                console.error('Invalid token format detected, clearing auth data');
                localStorage.removeItem('authToken');
                localStorage.removeItem('userRole');
                window.location.href = '/mono-v2/login.html';
            }
        } else {
            console.error('No auth token found, redirecting to login');
            window.location.href = '/mono-v2/login.html';
        }
    }
});

// Validate token format
function validateTokenFormat(token) {
    try {
        // Try to decode as base64 JSON
        const decoded = atob(token);
        const jsonData = JSON.parse(decoded);
        // Check required fields
        return jsonData && typeof jsonData === 'object' && jsonData.role;
    } catch (e) {
        // Check if it's JWT format (3 parts with dots)
        if (token.includes('.') && token.split('.').length === 3) {
            return true;
        }
        return false;
    }
}

// Export for global use
window.DynamicDashboard = DynamicDashboard;
