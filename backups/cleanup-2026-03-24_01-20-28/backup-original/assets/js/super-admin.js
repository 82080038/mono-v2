// Super Admin Multi-Tenant Monitoring System
class SuperAdminSystem {
    constructor() {
        this.instances = [];
        this.globalAlerts = [];
        this.analytics = {};
        this.systemHealth = {};
        this.currentUser = null;
    }

    // Initialize super admin system
    async initialize() {
        await this.loadDashboardData();
        await this.loadGlobalAlerts();
        this.setupRealTimeMonitoring();
        this.setupEventListeners();
        this.checkAuthentication();
    }

    // Check authentication
    checkAuthentication() {
        const user = JSON.parse(localStorage.getItem('superAdminUser') || '{}');
        if (!user.id) {
            this.showLoginModal();
        } else {
            this.currentUser = user;
            this.updateUserInterface();
        }
    }

    // Show login modal
    showLoginModal() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Super Admin Login</h5>
                    </div>
                    <div class="modal-body">
                        <form id="super-admin-login">
                            <div class="form-group">
                                <label for="admin-username">Username</label>
                                <input type="text" class="form-control" id="admin-username" required>
                            </div>
                            <div class="form-group">
                                <label for="admin-password">Password</label>
                                <input type="password" class="form-control" id="admin-password" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="superAdmin.login()">Login</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Login
    async login() {
        try {
            const username = document.getElementById('admin-username').value;
            const password = document.getElementById('admin-password').value;

            if (!username || !password) {
                this.showNotification('Error', 'Please enter username and password', 'error');
                return;
            }

            // Simulate authentication (in production, this would be a real API call)
            if (username === 'admin' && password === 'admin123') {
                this.currentUser = {
                    id: 1,
                    username: 'admin',
                    email: 'admin@superadmin.com',
                    full_name: 'Super Administrator',
                    role: 'super_admin'
                };
                
                localStorage.setItem('superAdminUser', JSON.stringify(this.currentUser));
                
                // Close modal
                const modal = document.querySelector('.modal.show');
                if (modal) {
                    bootstrap.Modal.getInstance(modal).hide();
                }
                
                this.updateUserInterface();
                this.showNotification('Success', 'Login successful', 'success');
            } else {
                this.showNotification('Error', 'Invalid credentials', 'error');
            }
        } catch (error) {
            console.error('Login error:', error);
            this.showNotification('Error', 'Login failed', 'error');
        }
    }

    // Logout
    logout() {
        localStorage.removeItem('superAdminUser');
        this.currentUser = null;
        this.showLoginModal();
    }

    // Update user interface
    updateUserInterface() {
        if (!this.currentUser) return;

        const userElement = document.getElementById('super-admin-user');
        if (userElement) {
            userElement.innerHTML = `
                <div class="user-info">
                    <span class="user-name">${this.currentUser.full_name}</span>
                    <span class="user-role">${this.currentUser.role}</span>
                </div>
                <div class="user-actions">
                    <button class="btn btn-sm btn-outline-secondary" onclick="superAdmin.logout()">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            `;
        }
    }

    // Load dashboard data
    async loadDashboardData() {
        try {
            const response = await fetch('/api/super-admin.php?action=dashboard');
            const result = await response.json();
            
            if (result.success) {
                this.updateDashboardDisplay(result.data);
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    }

    // Load global alerts
    async loadGlobalAlerts() {
        try {
            const response = await fetch('/api/super-admin.php?action=global_alerts');
            const result = await response.json();
            
            if (result.success) {
                this.globalAlerts = result.data;
                this.updateAlertsDisplay();
            }
        } catch (error) {
            console.error('Error loading global alerts:', error);
        }
    }

    // Update dashboard display
    updateDashboardDisplay(data) {
        const container = document.getElementById('super-admin-dashboard');
        if (!container) return;

        container.innerHTML = `
            <div class="dashboard-overview">
                <div class="overview-cards">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Instances</h5>
                            <h3 class="card-number">${data.total_instances}</h3>
                            <small class="text-muted">All koperasi instances</small>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Active</h5>
                            <h3 class="card-number text-success">${data.active_instances}</h3>
                            <small class="text-muted">Currently active</small>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Members</h5>
                            <h3 class="card-number">${data.total_members_all.toLocaleString('id-ID')}</h3>
                            <small class="text-muted">Across all instances</small>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Loans</h5>
                            <h3 class="card-number">${data.total_loans_all.toLocaleString('id-ID')}</h3>
                            <small class="text-muted">Active loans</small>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Amount</h5>
                            <h3 class="card-number">Rp ${(data.total_amount_all || 0).toLocaleString('id-ID')}</h3>
                            <small class="text-muted">Total portfolio value</small>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Active This Week</h5>
                            <h3 class="card-number text-info">${data.active_this_week}</h3>
                            <small class="text-muted">Recently active</small>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Update alerts display
    updateAlertsDisplay() {
        const container = document.getElementById('global-alerts');
        if (!container) return;

        container.innerHTML = `
            <div class="alerts-overview">
                <div class="alerts-header">
                    <h5>Global Alerts</h5>
                    <span class="badge bg-danger">${this.globalAlerts.length}</span>
                </div>
                <div class="alerts-list">
                    ${this.globalAlerts.length === 0 ? `
                        <div class="no-alerts">
                            <i class="fas fa-shield-alt fa-3x text-success"></i>
                            <p>No global alerts</p>
                        </div>
                    ` : this.globalAlerts.slice(0, 5).map(alert => `
                        <div class="alert-item alert-${this.getAlertSeverityClass(alert.severity)}">
                            <div class="alert-icon">
                                <i class="fas ${this.getAlertIcon(alert.alert_type)}"></i>
                            </div>
                            <div class="alert-content">
                                <h6>${alert.title}</h6>
                                <p>${alert.description}</p>
                                <small class="text-muted">${alert.instance_name} - ${new Date(alert.created_at).toLocaleString()}</small>
                            </div>
                            <div class="alert-actions">
                                <button class="btn btn-sm btn-outline-primary" onclick="superAdmin.investigateAlert(${alert.id})">
                                    Investigate
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>
                ${this.globalAlerts.length > 5 ? `
                    <div class="view-all">
                        <button class="btn btn-sm btn-outline-primary" onclick="superAdmin.viewAllAlerts()">
                            View All Alerts (${this.globalAlerts.length})
                        </button>
                    </div>
                ` : ''}
            </div>
        `;
    }

    // Load all koperasi instances
    async loadAllKoperasi() {
        try {
            const response = await fetch('/api/super-admin.php?action=all_koperasi');
            const result = await response.json();
            
            if (result.success) {
                this.instances = result.data;
                this.updateKoperasiList();
            }
        } catch (error) {
            console.error('Error loading koperasi instances:', error);
        }
    }

    // Update koperasi list
    updateKoperasiList() {
        const container = document.getElementById('koperasi-list');
        if (!container) return;

        container.innerHTML = `
            <div class="koperasi-grid">
                ${this.instances.map(instance => `
                    <div class="koperasi-card ${this.getInstanceStatusClass(instance)}">
                        <div class="card-header">
                            <h6>${instance.instance_name}</h6>
                            <span class="badge bg-${this.getStatusBadgeColor(instance.status)}">
                                ${instance.status}
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="instance-info">
                                <p><strong>Owner:</strong> ${instance.owner_name}</p>
                                <p><strong>Email:</strong> ${instance.owner_email}</p>
                                <p><strong>Phone:</strong> ${instance.owner_phone || 'N/A'}</p>
                                <p><strong>Members:</strong> ${instance.total_members}</p>
                                <p><strong>Loans:</strong> ${instance.total_loans}</p>
                                <p><strong>Amount:</strong> Rp ${(instance.total_amount || 0).toLocaleString('id-ID')}</p>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="instance-actions">
                                <button class="btn btn-sm btn-outline-primary" onclick="superAdmin.viewInstanceDetail(${instance.id})">
                                    <i class="fas fa-eye"></i> Details
                                </button>
                                <button class="btn btn-sm btn-outline-warning" onclick="superAdmin.suspendInstance(${instance.id})">
                                    <i class="fas fa-pause"></i> Suspend
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    // Get instance status class
    getInstanceStatusClass(instance) {
        if (instance.status === 'suspended') return 'suspended';
        if (instance.status === 'terminated') return 'terminated';
        return 'active';
    }

    // Get status badge color
    getStatusBadgeColor(status) {
        switch (status) {
            case 'active': return 'success';
            case 'suspended': return 'warning';
            case 'terminated': return 'danger';
            default: return 'secondary';
        }
    }

    // Get alert severity class
    getAlertSeverityClass(severity) {
        switch (severity) {
            case 'critical': return 'danger';
            case 'high': return 'warning';
            case 'medium': return 'info';
            default: return 'secondary';
        }
    }

    // Get alert icon
    getAlertIcon(type) {
        switch (type) {
            case 'system': return 'fa-server';
            case 'security': return 'fa-shield-alt';
            case 'financial': return 'fa-money-bill-wave';
            case 'performance': return 'fa-tachometer-alt';
            case 'compliance': return 'fa-balance-scale';
            default: return 'fa-exclamation-triangle';
        }
    }

    // View instance detail
    async viewInstanceDetail(instanceId) {
        try {
            const response = await fetch(`/api/super-admin.php?action=koperasi_detail&instance_id=${instanceId}`);
            const result = await response.json();
            
            if (result.success) {
                this.showInstanceDetailModal(result.data);
            }
        } catch (error) {
            console.error('Error loading instance detail:', error);
        }
    }

    // Show instance detail modal
    showInstanceDetailModal(instanceData) {
        const modal = document.createElement('div');
        modal.className = 'modal fade modal-lg';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${instanceData.instance.instance_name}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="instance-details">
                            <div class="detail-section">
                                <h6>Instance Information</h6>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>Owner Name:</label>
                                        <span>${instanceData.instance.owner_name}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Owner Email:</label>
                                        <span>${instanceData.instance.owner_email}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Owner Phone:</label>
                                        <span>${instanceData.instance.owner_phone || 'N/A'}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Status:</label>
                                        <span class="badge bg-${this.getStatusBadgeColor(instanceData.instance.status)}">
                                            ${instanceData.instance.status}
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <label>License Expiry:</label>
                                        <span>${instanceData.instance.license_expiry || 'N/A'}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Subscription:</label>
                                        <span>${instanceData.instance.subscription_plan}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h6>Financial Summary</h6>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>Total Members:</label>
                                        <span>${instanceData.financial_summary.total_members}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>New Members:</label>
                                        <span>${instanceData.financial_summary.new_members}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Active Loans:</label>
                                        <span>${instanceData.financial_summary.active_loans}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Total Loan Amount:</label>
                                        <span>Rp ${instanceData.financial_summary.total_loan_amount.toLocaleString('id-ID')}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Outstanding Amount:</label>
                                        <span>Rp ${instanceData.financial_summary.outstanding_amount.toLocaleString('id-ID')}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h6>Recent Activity</h6>
                                <div class="activity-timeline">
                                    ${instanceData.recent_activity.map(activity => `
                                        <div class="timeline-item">
                                            <div class="timeline-marker">
                                                <i class="fas ${this.getActivityIcon(activity.type)}"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6>${activity.type} (${activity.count})</h6>
                                                <small class="text-muted">Last activity: ${new Date(activity.last_activity).toLocaleString()}</small>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="superAdmin.exportInstanceData(${instanceData.instance.id})">
                            <i class="fas fa-download"></i> Export Data
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Get activity icon
    getActivityIcon(type) {
        switch (type) {
            case 'members': return 'fa-users text-success';
            case 'loans': return 'fa-hand-holding-usd text-primary';
            case 'payments': return 'fa-money-check-alt text-info';
            default: return 'fa-chart-line text-secondary';
        }
    }

    // Suspend instance
    async suspendInstance(instanceId) {
        if (!confirm('Are you sure you want to suspend this instance?')) {
            return;
        }

        try {
            // In production, this would be a real API call
            this.showNotification('Success', 'Instance suspended successfully', 'success');
            await this.loadAllKoperasi();
        } catch (error) {
            console.error('Error suspending instance:', error);
            this.showNotification('Error', 'Failed to suspend instance', 'error');
        }
    }

    // Investigate alert
    investigateAlert(alertId) {
        this.showNotification('Investigation', `Investigating alert #${alertId}`, 'info');
    }

    // Export instance data
    exportInstanceData(instanceId) {
        // This would generate and download instance data
        window.print();
    }

    // View all alerts
    viewAllAlerts() {
        // This would navigate to a dedicated alerts page
        console.log('Navigate to all alerts page');
    }

    // Setup real-time monitoring
    setupRealTimeMonitoring() {
        // Monitor system health
        setInterval(() => {
            this.checkSystemHealth();
        }, 60000); // Check every minute

        // Monitor global alerts
        setInterval(() => {
            this.loadGlobalAlerts();
        }, 30000); // Check every 30 seconds
    }

    // Check system health
    async checkSystemHealth() {
        try {
            const response = await fetch('/api/super-admin.php?action=system_health');
            const result = await response.json();
            
            if (result.success) {
                this.systemHealth = result.data;
                this.updateHealthDisplay();
            }
        } catch (error) {
            console.error('Error checking system health:', error);
        }
    }

    // Update health display
    updateHealthDisplay() {
        const container = document.getElementById('system-health');
        if (!container) return;

        container.innerHTML = `
            <div class="health-status">
                <div class="health-indicator">
                    <div class="indicator-circle ${this.getHealthClass(this.systemHealth.system_status)}">
                        <span class="health-score">${this.systemHealth.health_score}%</span>
                    </div>
                    <div class="health-label">
                        <h6>System Health</h6>
                        <span class="badge bg-${this.getHealthBadgeColor(this.systemHealth.system_status)}">
                            ${this.systemHealth.system_status.toUpperCase()}
                        </span>
                    </div>
                </div>
                <div class="health-metrics">
                    <div class="metric">
                        <span class="metric-label">Active Instances:</span>
                        <span class="metric-value">${this.systemHealth.health_metrics.daily_active}</span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Weekly Active:</span>
                        <span class="metric-value">${this.systemHealth.health_metrics.weekly_active}</span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Inactive 30 Days:</span>
                        <span class="metric-value text-warning">${this.systemHealth.health_metrics.inactive_30_days}</span>
                    </div>
                </div>
            </div>
        `;
    }

    // Get health class
    getHealthClass(status) {
        switch (status) {
            case 'healthy': return 'healthy';
            case 'warning': return 'warning';
            case 'critical': return 'critical';
            default: return 'unknown';
        }
    }

    // Get health badge color
    getHealthBadgeColor(status) {
        switch (status) {
            case 'healthy': return 'success';
            case 'warning': return 'warning';
            case 'critical': return 'danger';
            default: return 'secondary';
        }
    }

    // Show notification
    showNotification(title, message, type) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.innerHTML = `
            <strong>${title}:</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.getElementById('notifications');
        if (container) {
            container.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
    }

    // Setup event listeners
    setupEventListeners() {
        // Auto-refresh dashboard data
        setInterval(() => {
            this.loadDashboardData();
        }, 60000); // Refresh every minute

        // Auto-refresh koperasi list
        setInterval(() => {
            this.loadAllKoperasi();
        }, 300000); // Refresh every 5 minutes
    }
}

// Initialize super admin system when page loads
let superAdmin = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('super-admin-dashboard') || document.getElementById('global-alerts')) {
        superAdmin = new SuperAdminSystem();
        superAdmin.initialize();
    }
});
