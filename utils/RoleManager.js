/**
 * Role Management JavaScript Utility
 * 
 * Client-side utility for managing role definitions, permissions, and UI components
 * Integrates with backend RoleManager API
 * 
 * @author KSP Lam Gabe Jaya Development Team
 * @version 1.0
 */

class RoleManagerJS {
    constructor(basePath = '/mono') {
        this.basePath = basePath;
        this.cache = new Map();
        this.cacheExpiry = 300000; // 5 minutes
    }

    /**
     * Get API endpoint URL
     */
    getApiUrl(endpoint) {
        return `${this.basePath}/api/crud.php?path=${endpoint}`;
    }

    /**
     * Check if cache is valid
     */
    isCacheValid(cacheKey) {
        const cached = this.cache.get(cacheKey);
        return cached && (Date.now() - cached.timestamp) < this.cacheExpiry;
    }

    /**
     * Set cache
     */
    setCache(cacheKey, data) {
        this.cache.set(cacheKey, {
            data: data,
            timestamp: Date.now()
        });
    }

    /**
     * Get from cache
     */
    getCache(cacheKey) {
        const cached = this.cache.get(cacheKey);
        return cached ? cached.data : null;
    }

    /**
     * Clear cache
     */
    clearCache() {
        this.cache.clear();
    }

    /**
     * Make API request with caching
     */
    async apiRequest(endpoint, useCache = true) {
        const cacheKey = endpoint;
        
        if (useCache && this.isCacheValid(cacheKey)) {
            return this.getCache(cacheKey);
        }

        try {
            const response = await fetch(this.getApiUrl(endpoint));
            const data = await response.json();
            
            if (data.success) {
                if (useCache) {
                    this.setCache(cacheKey, data.data);
                }
                return data.data;
            } else {
                throw new Error(data.message || 'API request failed');
            }
        } catch (error) {
            console.error(`API request failed for ${endpoint}:`, error);
            throw error;
        }
    }

    /**
     * Get all role definitions
     */
    async getAllRoles() {
        return this.apiRequest('role_definitions');
    }

    /**
     * Get specific role definition
     */
    async getRole(roleName) {
        return this.apiRequest(`role_definitions&role_name=${roleName}`);
    }

    /**
     * Get role permissions
     */
    async getRolePermissions(roleName) {
        return this.apiRequest(`role_permissions&role_name=${roleName}`);
    }

    /**
     * Get role menu items
     */
    async getRoleMenuItems(roleName) {
        return this.apiRequest(`role_menu_items&role_name=${roleName}`);
    }

    /**
     * Get role dashboard widgets
     */
    async getRoleDashboardWidgets(roleName) {
        const role = await this.getRole(roleName);
        return role.dashboard_widgets || [];
    }

    /**
     * Get role workflows
     */
    async getRoleWorkflows(roleName) {
        return this.apiRequest(`role_workflows&role_name=${roleName}`);
    }

    /**
     * Get role responsibilities
     */
    async getRoleResponsibilities(roleName) {
        const role = await this.getRole(roleName);
        return role.responsibilities || [];
    }

    /**
     * Get role daily tasks
     */
    async getRoleDailyTasks(roleName) {
        const role = await this.getRole(roleName);
        return role.daily_tasks || [];
    }

    /**
     * Get role weekly tasks
     */
    async getRoleWeeklyTasks(roleName) {
        const role = await this.getRole(roleName);
        return role.weekly_tasks || [];
    }

    /**
     * Get role monthly tasks
     */
    async getRoleMonthlyTasks(roleName) {
        const role = await this.getRole(roleName);
        return role.monthly_tasks || [];
    }

    /**
     * Get role KPI metrics
     */
    async getRoleKpiMetrics(roleName) {
        const role = await this.getRole(roleName);
        return role.kpi_metrics || [];
    }

    /**
     * Get role hierarchy
     */
    async getRoleHierarchy() {
        return this.apiRequest('role_hierarchy');
    }

    /**
     * Get permission matrix
     */
    async getPermissionMatrix() {
        return this.apiRequest('permission_matrix');
    }

    /**
     * Check if user has permission
     */
    async hasPermission(userRole, resource, action) {
        const matrix = await this.getPermissionMatrix();
        
        if (!matrix[resource] || !matrix[resource][userRole]) {
            return false;
        }

        const permissions = matrix[resource][userRole];
        
        // Check for all access
        if (permissions.includes('all')) {
            return true;
        }

        // Check specific action
        return permissions.includes(action);
    }

    /**
     * Check if role can manage another role
     */
    async canManageRole(managerRole, targetRole) {
        const hierarchy = await this.getRoleHierarchy();
        const managerLevel = hierarchy[managerRole] || 999;
        const targetLevel = hierarchy[targetRole] || 999;

        return managerLevel < targetLevel;
    }

    /**
     * Generate role-based navigation menu
     */
    async generateNavigationMenu(userRole, containerId) {
        try {
            const menuItems = await this.getRoleMenuItems(userRole);
            const container = document.getElementById(containerId);
            
            if (!container) {
                console.error(`Container ${containerId} not found`);
                return;
            }

            // Sort menu items by order
            const sortedItems = menuItems.sort((a, b) => (a.order || 0) - (b.order || 0));

            // Generate menu HTML
            let menuHTML = '<ul class="nav-menu">';
            
            sortedItems.forEach(item => {
                menuHTML += `
                    <li class="nav-item">
                        <a href="${item.url}" class="nav-link" data-menu-id="${item.id}">
                            <i class="${item.icon}"></i>
                            <span>${item.label}</span>
                        </a>
                    </li>
                `;
            });

            menuHTML += '</ul>';
            container.innerHTML = menuHTML;

            // Add active state handling
            this.addMenuActiveState();
            
        } catch (error) {
            console.error('Error generating navigation menu:', error);
        }
    }

    /**
     * Generate role-based dashboard widgets
     */
    async generateDashboardWidgets(userRole, containerId) {
        try {
            const widgets = await this.getRoleDashboardWidgets(userRole);
            const container = document.getElementById(containerId);
            
            if (!container) {
                console.error(`Container ${containerId} not found`);
                return;
            }

            // Sort widgets by position
            const sortedWidgets = widgets.sort((a, b) => {
                const positionOrder = { 'top': 1, 'left': 2, 'right': 3, 'bottom': 4 };
                return (positionOrder[a.position] || 999) - (positionOrder[b.position] || 999);
            });

            // Generate widget HTML
            let widgetsHTML = '<div class="dashboard-widgets">';
            
            sortedWidgets.forEach(widget => {
                widgetsHTML += this.generateWidgetHTML(widget);
            });

            widgetsHTML += '</div>';
            container.innerHTML = widgetsHTML;

            // Initialize widgets
            this.initializeWidgets();
            
        } catch (error) {
            console.error('Error generating dashboard widgets:', error);
        }
    }

    /**
     * Generate individual widget HTML
     */
    generateWidgetHTML(widget) {
        const sizeClass = widget.size || 'medium';
        const positionClass = widget.position || 'top';
        
        let widgetHTML = `
            <div class="widget widget-${sizeClass} widget-${positionClass}" data-widget-id="${widget.id}">
                <div class="widget-header">
                    <h5 class="widget-title">${widget.title}</h5>
                    <div class="widget-actions">
                        <button class="btn btn-sm btn-outline-secondary" onclick="roleManager.refreshWidget('${widget.id}')">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="widget-content">
        `;

        switch (widget.type) {
            case 'metrics':
                widgetHTML += this.generateMetricsWidget(widget);
                break;
            case 'chart':
                widgetHTML += this.generateChartWidget(widget);
                break;
            case 'table':
                widgetHTML += this.generateTableWidget(widget);
                break;
            case 'alerts':
                widgetHTML += this.generateAlertsWidget(widget);
                break;
            case 'map':
                widgetHTML += this.generateMapWidget(widget);
                break;
            case 'queue':
                widgetHTML += this.generateQueueWidget(widget);
                break;
            case 'schedule':
                widgetHTML += this.generateScheduleWidget(widget);
                break;
            case 'activity_feed':
                widgetHTML += this.generateActivityFeedWidget(widget);
                break;
            default:
                widgetHTML += '<p>Widget type not implemented</p>';
        }

        widgetHTML += `
                </div>
            </div>
        `;

        return widgetHTML;
    }

    /**
     * Generate metrics widget
     */
    generateMetricsWidget(widget) {
        return `
            <div class="metrics-container">
                <div class="metric-item">
                    <h3 class="metric-value">-</h3>
                    <p class="metric-label">Loading...</p>
                </div>
            </div>
        `;
    }

    /**
     * Generate chart widget
     */
    generateChartWidget(widget) {
        return `
            <div class="chart-container">
                <canvas id="chart-${widget.id}"></canvas>
            </div>
        `;
    }

    /**
     * Generate table widget
     */
    generateTableWidget(widget) {
        return `
            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Loading...</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Loading data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;
    }

    /**
     * Generate alerts widget
     */
    generateAlertsWidget(widget) {
        return `
            <div class="alerts-container">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Loading alerts...
                </div>
            </div>
        `;
    }

    /**
     * Generate map widget
     */
    generateMapWidget(widget) {
        return `
            <div class="map-container">
                <div id="map-${widget.id}" style="height: 300px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center">
                        <i class="fas fa-map-marked-alt fa-3x text-muted"></i>
                        <p class="mt-2 text-muted">Map loading...</p>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Generate queue widget
     */
    generateQueueWidget(widget) {
        return `
            <div class="queue-container">
                <div class="queue-item">
                    <div class="queue-info">
                        <h6>Loading queue...</h6>
                        <small class="text-muted">Please wait</small>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Generate schedule widget
     */
    generateScheduleWidget(widget) {
        return `
            <div class="schedule-container">
                <div class="schedule-item">
                    <div class="schedule-time">--:--</div>
                    <div class="schedule-info">
                        <h6>Loading schedule...</h6>
                        <small class="text-muted">Please wait</small>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Generate activity feed widget
     */
    generateActivityFeedWidget(widget) {
        return `
            <div class="activity-feed">
                <div class="activity-item">
                    <i class="fas fa-circle text-muted"></i>
                    <div class="activity-content">
                        <small class="text-muted">Loading activities...</small>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Add menu active state handling
     */
    addMenuActiveState() {
        const currentPath = window.location.pathname;
        const menuLinks = document.querySelectorAll('.nav-link');
        
        menuLinks.forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
            }
        });
    }

    /**
     * Initialize widgets
     */
    initializeWidgets() {
        // This would be implemented based on specific widget requirements
        console.log('Widgets initialized');
    }

    /**
     * Refresh specific widget
     */
    async refreshWidget(widgetId) {
        console.log(`Refreshing widget: ${widgetId}`);
        // Implementation would depend on widget type and data source
    }

    /**
     * Format role display name
     */
    formatRoleName(roleName) {
        return roleName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    /**
     * Get role color class
     */
    getRoleColorClass(roleName) {
        const colorMap = {
            'super_admin': 'danger',
            'admin': 'warning',
            'mantri': 'info',
            'kasir': 'success',
            'teller': 'primary',
            'surveyor': 'secondary',
            'collector': 'dark',
            'member': 'light'
        };
        
        return colorMap[roleName] || 'secondary';
    }

    /**
     * Format currency
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }

    /**
     * Format date
     */
    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('id-ID');
    }

    /**
     * Format time
     */
    formatTime(dateString) {
        return new Date(dateString).toLocaleTimeString('id-ID');
    }

    /**
     * Format datetime
     */
    formatDateTime(dateString) {
        return new Date(dateString).toLocaleString('id-ID');
    }
}

// Global instance
const roleManager = new RoleManagerJS();

// Utility functions for backward compatibility
async function getUserMenuItems(userRole) {
    return roleManager.getRoleMenuItems(userRole);
}

async function getUserDashboardWidgets(userRole) {
    return roleManager.getRoleDashboardWidgets(userRole);
}

async function checkUserPermission(userRole, resource, action) {
    return roleManager.hasPermission(userRole, resource, action);
}

async function canManageRole(managerRole, targetRole) {
    return roleManager.canManageRole(managerRole, targetRole);
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate navigation if container exists
    const navContainer = document.getElementById('navigationMenu');
    if (navContainer) {
        const userRole = getCurrentUserRole(); // This function should be defined in the main application
        if (userRole) {
            roleManager.generateNavigationMenu(userRole, 'navigationMenu');
        }
    }

    // Auto-generate dashboard widgets if container exists
    const widgetsContainer = document.getElementById('dashboardWidgets');
    if (widgetsContainer) {
        const userRole = getCurrentUserRole();
        if (userRole) {
            roleManager.generateDashboardWidgets(userRole, 'dashboardWidgets');
        }
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RoleManagerJS;
}
