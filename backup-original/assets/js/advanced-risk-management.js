// Advanced Risk Management System
class AdvancedRiskManagement {
    constructor() {
        this.riskProfiles = [];
        this.collectionStrategies = [];
        this.earlyWarnings = [];
        this.portfolioAnalysis = {};
        this.isMonitoring = false;
    }

    // Initialize advanced risk management
    async initialize() {
        await this.loadRiskDashboard();
        await this.loadEarlyWarnings();
        this.setupRealTimeMonitoring();
        this.setupEventListeners();
    }

    // Load risk dashboard
    async loadRiskDashboard() {
        try {
            const response = await fetch('/api/advanced-risk-management.php?action=dashboard');
            const result = await response.json();
            
            if (result.success) {
                this.updateRiskDashboardDisplay(result.data);
            }
        } catch (error) {
            console.error('Error loading risk dashboard:', error);
        }
    }

    // Load early warnings
    async loadEarlyWarnings() {
        try {
            const response = await fetch('/api/advanced-risk-management.php?action=early_warning');
            const result = await response.json();
            
            if (result.success) {
                this.earlyWarnings = result.data;
                this.updateEarlyWarningDisplay();
            }
        } catch (error) {
            console.error('Error loading early warnings:', error);
        }
    }

    // Update risk dashboard display
    updateRiskDashboardDisplay(data) {
        const container = document.getElementById('risk-dashboard');
        if (!container) return;

        container.innerHTML = `
            <div class="risk-dashboard-header">
                <h5>⚠️ Advanced Risk Management</h5>
                <div class="risk-status">
                    <span class="status-indicator active"></span>
                    <span>Monitoring Active</span>
                </div>
            </div>
            
            <div class="risk-summary-grid">
                <div class="risk-summary-card critical">
                    <div class="risk-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="risk-content">
                        <h6>Critical Risk</h6>
                        <h3>${data.risk_summary.critical_risk || 0}</h3>
                        <small>Immediate action required</small>
                    </div>
                </div>
                
                <div class="risk-summary-card high">
                    <div class="risk-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="risk-content">
                        <h6>High Risk</h6>
                        <h3>${data.risk_summary.high_risk || 0}</h3>
                        <small>Intensive monitoring</small>
                    </div>
                </div>
                
                <div class="risk-summary-card medium">
                    <div class="risk-icon">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <div class="risk-content">
                        <h6>Medium Risk</h6>
                        <h3>${data.risk_summary.medium_risk || 0}</h3>
                        <small>Regular monitoring</small>
                    </div>
                </div>
                
                <div class="risk-summary-card low">
                    <div class="risk-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="risk-content">
                        <h6>Low Risk</h6>
                        <h3>${data.risk_summary.low_risk || 0}</h3>
                        <small>Standard monitoring</small>
                    </div>
                </div>
            </div>
            
            <div class="early-warning-section">
                <h6>🚨 Early Warning Indicators</h6>
                <div class="warning-stats">
                    <div class="warning-stat">
                        <span class="stat-label">Total Alerts:</span>
                        <span class="stat-value">${data.warning_summary.total_alerts || 0}</span>
                    </div>
                    <div class="warning-stat">
                        <span class="stat-label">Critical:</span>
                        <span class="stat-value critical">${data.warning_summary.critical_alerts || 0}</span>
                    </div>
                    <div class="warning-stat">
                        <span class="stat-label">High:</span>
                        <span class="stat-value high">${data.warning_summary.high_alerts || 0}</span>
                    </div>
                    <div class="warning-stat">
                        <span class="stat-label">Active:</span>
                        <span class="stat-value active">${data.warning_summary.active_alerts || 0}</span>
                    </div>
                </div>
            </div>
            
            <div class="collection-effectiveness-section">
                <h6>💰 Collection Strategy Effectiveness</h6>
                <div class="collection-stats">
                    ${data.collection_stats.map(stat => `
                        <div class="collection-stat-item">
                            <div class="strategy-type">${stat.strategy_type}</div>
                            <div class="strategy-metrics">
                                <div class="metric">
                                    <span class="metric-label">Count:</span>
                                    <span class="metric-value">${stat.count}</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-label">Success Rate:</span>
                                    <span class="metric-value">${Math.round(stat.avg_success_rate || 0)}%</span>
                                </div>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: ${stat.avg_success_rate || 0}%"></div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    // Update early warning display
    updateEarlyWarningDisplay() {
        const container = document.getElementById('early-warning-display');
        if (!container) return;

        container.innerHTML = `
            <div class="warning-header">
                <h6>🚨 Early Warning Alerts</h6>
                <div class="warning-filters">
                    <select class="form-select" onchange="advancedRisk.filterWarnings(this.value)">
                        <option value="all">All Warnings</option>
                        <option value="critical">Critical</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
            </div>
            
            <div class="warnings-list">
                ${this.earlyWarnings.map(warning => `
                    <div class="warning-item severity-${warning.severity}">
                        <div class="warning-header">
                            <div class="warning-info">
                                <span class="member-name">${warning.member_name}</span>
                                <span class="indicator-type">${warning.indicator_type.replace('_', ' ')}</span>
                            </div>
                            <div class="warning-meta">
                                <span class="severity-badge bg-${this.getSeverityColor(warning.severity)}">${warning.severity}</span>
                                <span class="detection-time">${new Date(warning.detected_at).toLocaleString()}</span>
                            </div>
                        </div>
                        <div class="warning-content">
                            <p>${warning.description}</p>
                            <div class="member-details">
                                <small>Phone: ${warning.member_phone}</small>
                                <small>Address: ${warning.member_address}</small>
                                <small>Risk Category: ${warning.risk_category || 'unknown'}</small>
                            </div>
                        </div>
                        <div class="warning-actions">
                            <button class="btn btn-sm btn-outline-primary" onclick="advancedRisk.viewWarningDetails(${warning.id})">
                                <i class="fas fa-eye"></i> Details
                            </button>
                            <button class="btn btn-sm btn-outline-warning" onclick="advancedRisk.escalateWarning(${warning.id})">
                                <i class="fas fa-arrow-up"></i> Escalate
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="advancedRisk.resolveWarning(${warning.id})">
                                <i class="fas fa-check"></i> Resolve
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    // Get severity color
    getSeverityColor(severity) {
        const colors = {
            'critical': 'danger',
            'high': 'warning',
            'medium': 'info',
            'low': 'secondary'
        };
        return colors[severity] || 'secondary';
    }

    // Filter warnings
    async filterWarnings(severity) {
        try {
            const response = await fetch(`/api/advanced-risk-management.php?action=early_warning&severity=${severity}`);
            const result = await response.json();
            
            if (result.success) {
                this.earlyWarnings = result.data;
                this.updateEarlyWarningDisplay();
            }
        } catch (error) {
            console.error('Error filtering warnings:', error);
        }
    }

    // View warning details
    viewWarningDetails(warningId) {
        const warning = this.earlyWarnings.find(w => w.id === warningId);
        if (!warning) return;

        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Early Warning Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="warning-details">
                            <div class="detail-header">
                                <div class="member-info">
                                    <h6>${warning.member_name}</h6>
                                    <p>Member ID: ${warning.member_id}</p>
                                </div>
                                <div class="warning-meta">
                                    <span class="badge bg-${this.getSeverityColor(warning.severity)}">${warning.severity}</span>
                                    <span class="indicator-type">${warning.indicator_type.replace('_', ' ')}</span>
                                </div>
                            </div>
                            
                            <div class="warning-content">
                                <h6>Description</h6>
                                <p>${warning.description}</p>
                            </div>
                            
                            <div class="contact-info">
                                <h6>Contact Information</h6>
                                <p><strong>Phone:</strong> ${warning.member_phone}</p>
                                <p><strong>Address:</strong> ${warning.member_address}</p>
                            </div>
                            
                            <div class="risk-info">
                                <h6>Risk Assessment</h6>
                                <p><strong>Risk Category:</strong> ${warning.risk_category || 'Not assessed'}</p>
                                <p><strong>Collection Strategy:</strong> ${warning.strategy_type || 'Not assigned'}</p>
                            </div>
                            
                            <div class="timeline-info">
                                <h6>Timeline</h6>
                                <p><strong>Detected:</strong> ${new Date(warning.detected_at).toLocaleString()}</p>
                                <p><strong>Status:</strong> ${warning.status}</p>
                                ${warning.resolved_at ? `<p><strong>Resolved:</strong> ${new Date(warning.resolved_at).toLocaleString()}</p>` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-warning" onclick="advancedRisk.escalateWarning(${warning.id})">
                            <i class="fas fa-arrow-up"></i> Escalate
                        </button>
                        <button type="button" class="btn btn-success" onclick="advancedRisk.resolveWarning(${warning.id})">
                            <i class="fas fa-check"></i> Resolve
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

    // Escalate warning
    async escalateWarning(warningId) {
        try {
            this.showNotification('Escalation', 'Escalating warning...', 'info');
            
            // Simulate escalation
            setTimeout(() => {
                this.showNotification('Success', 'Warning escalated successfully', 'success');
                this.loadEarlyWarnings();
            }, 1500);
        } catch (error) {
            console.error('Error escalating warning:', error);
            this.showNotification('Error', 'Failed to escalate warning', 'error');
        }
    }

    // Resolve warning
    async resolveWarning(warningId) {
        try {
            this.showNotification('Resolution', 'Resolving warning...', 'info');
            
            // Simulate resolution
            setTimeout(() => {
                this.showNotification('Success', 'Warning resolved successfully', 'success');
                this.loadEarlyWarnings();
            }, 1500);
        } catch (error) {
            console.error('Error resolving warning:', error);
            this.showNotification('Error', 'Failed to resolve warning', 'error');
        }
    }

    // Setup real-time monitoring
    setupRealTimeMonitoring() {
        if (!this.isMonitoring) return;

        // Monitor risk indicators
        setInterval(() => {
            this.checkRiskIndicators();
        }, 60000); // Check every minute

        // Monitor early warnings
        setInterval(() => {
            this.loadEarlyWarnings();
        }, 30000); // Check every 30 seconds
    }

    // Check risk indicators
    async checkRiskIndicators() {
        try {
            // Check for new risk indicators
            const response = await fetch('/api/advanced-risk-management.php?action=risk_assessment');
            const result = await response.json();

            if (result.success && result.data.risk_score > 80) {
                this.showNotification('High Risk Alert', `Member ${result.data.member_id} has high risk score: ${result.data.risk_score}`, 'warning');
            }
        } catch (error) {
            console.error('Error checking risk indicators:', error);
        }
    }

    // Setup event listeners
    setupEventListeners() {
        // Auto-refresh dashboard data
        setInterval(() => {
            this.loadRiskDashboard();
        }, 60000); // Refresh every minute

        // Auto-refresh early warnings
        setInterval(() => {
            this.loadEarlyWarnings();
        }, 30000); // Refresh every 30 seconds
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
}

// Initialize advanced risk management when page loads
let advancedRisk = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('risk-dashboard') || document.getElementById('early-warning-display')) {
        advancedRisk = new AdvancedRiskManagement();
        advancedRisk.initialize();
    }
});
