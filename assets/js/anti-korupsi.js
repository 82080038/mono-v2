// Anti-Korupsi & Financial Security System
class AntiCorruptionSystem {
    constructor() {
        this.alerts = [];
        this.monitoringData = [];
        this.riskAssessment = {};
        this.isMonitoring = false;
    }

    // Initialize anti-korupsi system
    async initialize() {
        await this.loadMonitoringData();
        await this.loadAlerts();
        this.setupRealTimeMonitoring();
        this.setupEventListeners();
    }

    // Load monitoring data
    async loadMonitoringData() {
        try {
            const response = await fetch('/api/anti-korupsi.php?action=monitor');
            const result = await response.json();
            
            if (result.success) {
                this.monitoringData = result.data;
                this.updateMonitoringDisplay();
            }
        } catch (error) {
            console.error('Error loading monitoring data:', error);
        }
    }

    // Load alerts
    async loadAlerts() {
        try {
            const response = await fetch('/api/anti-korupsi.php?action=alert');
            const result = await response.json();
            
            if (result.success) {
                this.alerts = result.data;
                this.updateAlertsDisplay();
            }
        } catch (error) {
            console.error('Error loading alerts:', error);
        }
    }

    // Setup real-time monitoring
    setupRealTimeMonitoring() {
        if (!this.isMonitoring) return;

        // Monitor cash differences
        setInterval(() => {
            this.checkCashDifferences();
        }, 30000); // Check every 30 seconds

        // Monitor unusual patterns
        setInterval(() => {
            this.detectUnusualPatterns();
        }, 60000); // Check every minute
    }

    // Check cash differences
    async checkCashDifferences() {
        try {
            const response = await fetch('/api/anti-korupsi.php?action=monitor');
            const result = await response.json();
            
            if (result.success) {
                result.data.forEach(staff => {
                    if (staff.difference > 100000) { // More than 100k difference
                        this.createAlert('cash_mismatch', staff.staff_id, 
                            `Cash difference of Rp ${staff.difference.toLocaleString('id-ID')} detected for ${staff.staff_name}`);
                    }
                });
            }
        } catch (error) {
            console.error('Error checking cash differences:', error);
        }
    }

    // Detect unusual patterns
    async detectUnusualPatterns() {
        try {
            const response = await fetch('/api/anti-korupsi.php?action=fraud_detection');
            const result = await response.json();
            
            if (result.success) {
                result.data.forEach(fraud => {
                    if (fraud.confidence_score > 70) {
                        this.createAlert('fraud_suspicion', fraud.staff_id, 
                            `Suspicious activity detected: ${fraud.description}`);
                    }
                });
            }
        } catch (error) {
            console.error('Error detecting unusual patterns:', error);
        }
    }

    // Create alert
    async createAlert(type, staffId, description) {
        try {
            const formData = new FormData();
            formData.append('alert_type', type);
            formData.append('staff_id', staffId);
            formData.append('description', description);
            formData.append('severity', 'high');

            const response = await fetch('/api/anti-korupsi.php?action=alert', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Security Alert', description, 'warning');
                await this.loadAlerts();
            }
        } catch (error) {
            console.error('Error creating alert:', error);
        }
    }

    // Update monitoring display
    updateMonitoringDisplay() {
        const container = document.getElementById('financial-monitoring');
        if (!container) return;

        container.innerHTML = `
            <div class="monitoring-header">
                <h5>Financial Monitoring</h5>
                <button class="btn btn-sm btn-primary" onclick="antiCorruption.refreshData()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
            <div class="monitoring-grid">
                ${this.monitoringData.map(staff => `
                    <div class="staff-monitoring-card ${this.getStaffStatusClass(staff)}">
                        <div class="staff-info">
                            <h6>${staff.staff_name}</h6>
                            <small class="text-muted">${staff.staff_username}</small>
                        </div>
                        <div class="financial-metrics">
                            <div class="metric">
                                <span class="label">Collected:</span>
                                <span class="value">Rp ${staff.cash_collected.toLocaleString('id-ID')}</span>
                            </div>
                            <div class="metric">
                                <span class="label">Submitted:</span>
                                <span class="value">Rp ${staff.cash_submitted.toLocaleString('id-ID')}</span>
                            </div>
                            <div class="metric ${staff.difference > 0 ? 'negative' : 'positive'}">
                                <span class="label">Difference:</span>
                                <span class="value">Rp ${Math.abs(staff.difference).toLocaleString('id-ID')}</span>
                            </div>
                        </div>
                        <div class="status-indicator">
                            <span class="badge bg-${this.getStatusBadgeColor(staff.status)}">
                                ${staff.status.toUpperCase()}
                            </span>
                        </div>
                        <div class="actions">
                            <button class="btn btn-sm btn-outline-primary" onclick="antiCorruption.viewAuditTrail(${staff.staff_id})">
                                <i class="fas fa-search"></i> Audit
                            </button>
                            ${staff.difference > 100000 ? `
                                <button class="btn btn-sm btn-outline-danger" onclick="antiCorruption.reportIssue(${staff.staff_id})">
                                    <i class="fas fa-exclamation-triangle"></i> Report
                                </button>
                            ` : ''}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    // Update alerts display
    updateAlertsDisplay() {
        const container = document.getElementById('security-alerts');
        if (!container) return;

        container.innerHTML = `
            <div class="alerts-header">
                <h5>Security Alerts</h5>
                <span class="badge bg-danger">${this.alerts.length}</span>
            </div>
            <div class="alerts-list">
                ${this.alerts.length === 0 ? `
                    <div class="no-alerts">
                        <i class="fas fa-shield-alt fa-3x text-success"></i>
                        <p>No security alerts</p>
                    </div>
                ` : this.alerts.map(alert => `
                    <div class="alert-item alert-${this.getAlertSeverityClass(alert.severity)}">
                        <div class="alert-icon">
                            <i class="fas ${this.getAlertIcon(alert.alert_type)}"></i>
                        </div>
                        <div class="alert-content">
                            <h6>${alert.title}</h6>
                            <p>${alert.description}</p>
                            <small class="text-muted">${new Date(alert.created_at).toLocaleString()}</small>
                        </div>
                        <div class="alert-actions">
                            <button class="btn btn-sm btn-outline-primary" onclick="antiCorruption.investigateAlert(${alert.id})">
                                Investigate
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="antiCorruption.dismissAlert(${alert.id})">
                                Dismiss
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    // Get staff status class
    getStaffStatusClass(staff) {
        if (staff.difference > 100000) return 'critical';
        if (staff.difference > 50000) return 'warning';
        return 'normal';
    }

    // Get status badge color
    getStatusBadgeColor(status) {
        switch (status) {
            case 'critical': return 'danger';
            case 'warning': return 'warning';
            default: return 'success';
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
            case 'cash_mismatch': return 'fa-money-bill-wave';
            case 'unusual_activity': return 'fa-exclamation-triangle';
            case 'location_anomaly': return 'fa-map-marker-alt';
            case 'fraud_suspicion': return 'fa-user-secret';
            default: return 'fa-shield-alt';
        }
    }

    // View audit trail
    async viewAuditTrail(staffId) {
        try {
            const response = await fetch(`/api/anti-korupsi.php?action=audit&staff_id=${staffId}`);
            const result = await response.json();
            
            if (result.success) {
                this.showAuditTrailModal(result.data, staffId);
            }
        } catch (error) {
            console.error('Error loading audit trail:', error);
        }
    }

    // Show audit trail modal
    showAuditTrailModal(auditData, staffId) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Audit Trail - Staff ID: ${staffId}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="audit-timeline">
                            ${auditData.map(transaction => `
                                <div class="timeline-item">
                                    <div class="timeline-marker">
                                        <i class="fas ${this.getTransactionIcon(transaction.transaction_type)}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6>${transaction.transaction_type.toUpperCase()}</h6>
                                        <p>Amount: Rp ${transaction.amount.toLocaleString('id-ID')}</p>
                                        <p>Location: ${transaction.location_address || 'Unknown'}</p>
                                        <small class="text-muted">${new Date(transaction.transaction_time).toLocaleString()}</small>
                                        ${transaction.verified ? '<span class="badge bg-success ms-2">Verified</span>' : '<span class="badge bg-warning ms-2">Pending</span>'}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="antiCorruption.exportAuditTrail(${staffId})">
                            <i class="fas fa-download"></i> Export
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

    // Get transaction icon
    getTransactionIcon(type) {
        switch (type) {
            case 'collection': return 'fa-hand-holding-usd text-success';
            case 'submission': return 'fa-money-check-alt text-primary';
            case 'expense': return 'fa-receipt text-warning';
            default: return 'fa-exchange-alt text-secondary';
        }
    }

    // Report issue
    reportIssue(staffId) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Report Issue</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="issue-report-form">
                            <div class="form-group">
                                <label for="issue-type">Issue Type</label>
                                <select class="form-control" id="issue-type" required>
                                    <option value="">Select issue type</option>
                                    <option value="cash_mismatch">Cash Mismatch</option>
                                    <option value="unusual_activity">Unusual Activity</option>
                                    <option value="fraud_suspicion">Fraud Suspicion</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="issue-description">Description</label>
                                <textarea class="form-control" id="issue-description" rows="4" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="issue-severity">Severity</label>
                                <select class="form-control" id="issue-severity" required>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="antiCorruption.submitIssueReport(${staffId})">
                            Submit Report
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

    // Submit issue report
    async submitIssueReport(staffId) {
        try {
            const type = document.getElementById('issue-type').value;
            const description = document.getElementById('issue-description').value;
            const severity = document.getElementById('issue-severity').value;

            if (!type || !description) {
                this.showNotification('Error', 'Please fill all required fields', 'error');
                return;
            }

            await this.createAlert(type, staffId, `${severity.toUpperCase()}: ${description}`);
            
            this.showNotification('Success', 'Issue reported successfully', 'success');
            
            // Close modal
            const modal = document.querySelector('.modal.show');
            if (modal) {
                bootstrap.Modal.getInstance(modal).hide();
            }
        } catch (error) {
            console.error('Error submitting issue report:', error);
            this.showNotification('Error', 'Failed to submit report', 'error');
        }
    }

    // Investigate alert
    investigateAlert(alertId) {
        // This would open a detailed investigation interface
        this.showNotification('Investigation', `Investigating alert #${alertId}`, 'info');
    }

    // Dismiss alert
    async dismissAlert(alertId) {
        try {
            const response = await fetch('/api/anti-korupsi.php?action=dismiss_alert', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    alert_id: alertId
                })
            });

            const result = await response.json();

            if (result.success) {
                await this.loadAlerts();
                this.showNotification('Success', 'Alert dismissed', 'success');
            }
        } catch (error) {
            console.error('Error dismissing alert:', error);
        }
    }

    // Export audit trail
    exportAuditTrail(staffId) {
        // This would generate and download audit trail report
        window.print();
    }

    // Refresh data
    async refreshData() {
        await this.loadMonitoringData();
        await this.loadAlerts();
        this.showNotification('Success', 'Data refreshed', 'success');
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
        // Auto-refresh monitoring data
        setInterval(() => {
            this.loadMonitoringData();
        }, 60000); // Refresh every minute

        // Auto-refresh alerts
        setInterval(() => {
            this.loadAlerts();
        }, 30000); // Refresh every 30 seconds
    }
}

// Initialize anti-korupsi system when page loads
let antiCorruption = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('financial-monitoring') || document.getElementById('security-alerts')) {
        antiCorruption = new AntiCorruptionSystem();
        antiCorruption.initialize();
    }
});
