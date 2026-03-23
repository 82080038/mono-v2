// Compliance & Adaptation System
class ComplianceAdaptation {
    constructor() {
        this.complianceStatus = {};
        this.adaptationStrategies = [];
        this.auditTrail = [];
        this.operationalFlexibility = {};
        this.isMonitoring = false;
    }

    // Initialize compliance adaptation system
    async initialize() {
        await this.loadComplianceDashboard();
        await this.loadAdaptationStrategies();
        this.setupRealTimeMonitoring();
        this.setupEventListeners();
    }

    // Load compliance dashboard
    async loadComplianceDashboard() {
        try {
            const response = await fetch('/api/compliance-adaptation.php?action=dashboard');
            const result = await response.json();
            
            if (result.success) {
                this.updateComplianceDashboardDisplay(result.data);
            }
        } catch (error) {
            console.error('Error loading compliance dashboard:', error);
        }
    }

    // Load adaptation strategies
    async loadAdaptationStrategies() {
        try {
            const response = await fetch('/api/compliance-adaptation.php?action=adaptation_strategies');
            const result = await response.json();
            
            if (result.success) {
                this.adaptationStrategies = result.data;
                this.updateStrategiesDisplay();
            }
        } catch (error) {
            console.error('Error loading adaptation strategies:', error);
        }
    }

    // Update compliance dashboard display
    updateComplianceDashboardDisplay(data) {
        const container = document.getElementById('compliance-dashboard');
        if (!container) return;

        container.innerHTML = `
            <div class="compliance-header">
                <h5>🛡️ Compliance & Adaptation</h5>
                <div class="compliance-status">
                    <span class="status-indicator active"></span>
                    <span>System Active</span>
                </div>
            </div>
            
            <div class="compliance-metrics">
                <div class="compliance-metric">
                    <div class="metric-header">
                        <h6>Regulatory Compliance</h6>
                        <div class="compliance-score">
                            <span class="score-value">${Math.round(data.compliance_summary.avg_compliance_score || 0)}%</span>
                        </div>
                    </div>
                    <div class="metric-details">
                        <div class="detail-item">
                            <span class="label">Compliant:</span>
                            <span class="value compliant">${data.compliance_summary.compliant_count || 0}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Partial:</span>
                            <span class="value partial">${data.compliance_summary.partial_count || 0}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Non-Compliant:</span>
                            <span class="value non-compliant">${data.compliance_summary.non_compliant_count || 0}</span>
                        </div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill ${this.getComplianceClass(data.compliance_summary.avg_compliance_score)}" 
                             style="width: ${data.compliance_summary.avg_compliance_score || 0}%"></div>
                    </div>
                </div>
                
                <div class="compliance-metric">
                    <div class="metric-header">
                        <h6>Adaptation Strategies</h6>
                        <div class="strategy-score">
                            <span class="score-value">${Math.round(data.strategy_summary.avg_effectiveness || 0)}%</span>
                        </div>
                    </div>
                    <div class="metric-details">
                        <div class="detail-item">
                            <span class="label">Implemented:</span>
                            <span class="value implemented">${data.strategy_summary.implemented_count || 0}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">In Progress:</span>
                            <span class="value in-progress">${data.strategy_summary.in_progress_count || 0}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">High Risk:</span>
                            <span class="value high-risk">${data.strategy_summary.high_risk_strategies || 0}</span>
                        </div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill ${this.getEffectivenessClass(data.strategy_summary.avg_effectiveness)}" 
                             style="width: ${data.strategy_summary.avg_effectiveness || 0}%"></div>
                    </div>
                </div>
                
                <div class="compliance-metric">
                    <div class="metric-header">
                        <h6>Audit Status</h6>
                        <div class="audit-score">
                            <span class="score-value">${data.audit_summary.completed_audits || 0}</span>
                        </div>
                    </div>
                    <div class="metric-details">
                        <div class="detail-item">
                            <span class="label">Completed:</span>
                            <span class="value completed">${data.audit_summary.completed_audits || 0}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Pending:</span>
                            <span class="value pending">${data.audit_summary.pending_audits || 0}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Total:</span>
                            <span class="value total">${data.audit_summary.total_audits || 0}</span>
                        </div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill ${this.getAuditClass(data.audit_summary.completed_audits, data.audit_summary.total_audits)}" 
                             style="width: ${this.getAuditPercentage(data.audit_summary.completed_audits, data.audit_summary.total_audits)}%"></div>
                    </div>
                </div>
            </div>
            
            <div class="operational-flexibility">
                <h6>🔄 Operational Flexibility</h6>
                <div class="flexibility-grid">
                    <div class="flexibility-item">
                        <div class="capability-header">
                            <span class="capability-name">Mobile Operations</span>
                            <span class="capability-status implemented">Implemented</span>
                        </div>
                        <div class="capability-metrics">
                            <div class="metric">
                                <span class="label">Coverage:</span>
                                <span class="value">100%</span>
                            </div>
                            <div class="metric">
                                <span class="label">Effectiveness:</span>
                                <span class="value">95%</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flexibility-item">
                        <div class="capability-header">
                            <span class="capability-name">Digital Payments</span>
                            <span class="capability-status implemented">Implemented</span>
                        </div>
                        <div class="capability-metrics">
                            <div class="metric">
                                <span class="label">Coverage:</span>
                                <span class="value">85%</span>
                            </div>
                            <div class="metric">
                                <span class="label">Effectiveness:</span>
                                <span class="value">90%</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flexibility-item">
                        <div class="capability-header">
                            <span class="capability-name">Remote Monitoring</span>
                            <span class="capability-status implemented">Implemented</span>
                        </div>
                        <div class="capability-metrics">
                            <div class="metric">
                                <span class="label">Coverage:</span>
                                <span class="value">100%</span>
                            </div>
                            <div class="metric">
                                <span class="label">Effectiveness:</span>
                                <span class="value">88%</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flexibility-item">
                        <div class="capability-header">
                            <span class="capability-name">Flexible Scheduling</span>
                            <span class="capability-status in-progress">In Progress</span>
                        </div>
                        <div class="capability-metrics">
                            <div class="metric">
                                <span class="label">Coverage:</span>
                                <span class="value">60%</span>
                            </div>
                            <div class="metric">
                                <span class="label">Effectiveness:</span>
                                <span class="value">75%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Update strategies display
    updateStrategiesDisplay() {
        const container = document.getElementById('adaptation-strategies-display');
        if (!container) return;

        container.innerHTML = `
            <div class="strategies-header">
                <h6>🔄 Adaptation Strategies</h6>
                <div class="strategy-filters">
                    <select class="form-select" onchange="complianceAdaptation.filterStrategies(this.value)">
                        <option value="all">All Strategies</option>
                        <option value="operational">Operational</option>
                        <option value="financial">Financial</option>
                        <option value="legal">Legal</option>
                        <option value="communication">Communication</option>
                    </select>
                </div>
            </div>
            
            <div class="strategies-list">
                ${this.adaptationStrategies.map(strategy => `
                    <div class="strategy-item ${strategy.implementation_status}">
                        <div class="strategy-header">
                            <div class="strategy-info">
                                <h6>${strategy.strategy_name}</h6>
                                <span class="strategy-type">${strategy.strategy_type}</span>
                            </div>
                            <div class="strategy-meta">
                                <span class="status-badge bg-${this.getStatusColor(strategy.implementation_status)}">${strategy.implementation_status}</span>
                                <span class="risk-badge bg-${this.getRiskColor(strategy.risk_level)}">${strategy.risk_level}</span>
                            </div>
                        </div>
                        <div class="strategy-content">
                            <p>${strategy.description}</p>
                            <div class="strategy-metrics">
                                <div class="metric">
                                    <span class="metric-label">Effectiveness:</span>
                                    <span class="metric-value">${Math.round(strategy.effectiveness_score || 0)}%</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-label">Created:</span>
                                    <span class="metric-value">${new Date(strategy.created_at).toLocaleDateString()}</span>
                                </div>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill ${this.getEffectivenessClass(strategy.effectiveness_score)}" 
                                     style="width: ${strategy.effectiveness_score || 0}%"></div>
                            </div>
                        </div>
                        <div class="strategy-actions">
                            <button class="btn btn-sm btn-outline-primary" onclick="complianceAdaptation.viewStrategyDetails(${strategy.id})">
                                <i class="fas fa-eye"></i> Details
                            </button>
                            ${strategy.implementation_status === 'planned' ? `
                                <button class="btn btn-sm btn-outline-success" onclick="complianceAdaptation.implementStrategy(${strategy.id})">
                                    <i class="fas fa-play"></i> Implement
                                </button>
                            ` : ''}
                            ${strategy.implementation_status === 'in_progress' ? `
                                <button class="btn btn-sm btn-outline-warning" onclick="complianceAdaptation.completeStrategy(${strategy.id})">
                                    <i class="fas fa-check"></i> Complete
                                </button>
                            ` : ''}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    // Get compliance class
    getComplianceClass(score) {
        if (score >= 90) return 'excellent';
        if (score >= 70) return 'good';
        if (score >= 50) return 'moderate';
        return 'poor';
    }

    // Get effectiveness class
    getEffectivenessClass(score) {
        if (score >= 80) return 'excellent';
        if (score >= 60) return 'good';
        if (score >= 40) return 'moderate';
        return 'poor';
    }

    // Get audit class
    getAuditClass(completed, total) {
        const percentage = this.getAuditPercentage(completed, total);
        if (percentage >= 80) return 'excellent';
        if (percentage >= 60) return 'good';
        if (percentage >= 40) return 'moderate';
        return 'poor';
    }

    // Get audit percentage
    getAuditPercentage(completed, total) {
        if (total === 0) return 0;
        return Math.round((completed / total) * 100);
    }

    // Get status color
    getStatusColor(status) {
        const colors = {
            'implemented': 'success',
            'in_progress': 'warning',
            'planned': 'info',
            'discontinued': 'secondary'
        };
        return colors[status] || 'secondary';
    }

    // Get risk color
    getRiskColor(risk) {
        const colors = {
            'low': 'success',
            'medium': 'warning',
            'high' : 'danger'
        };
        return colors[risk] || 'secondary';
    }

    // Filter strategies
    async filterStrategies(type) {
        try {
            const response = await fetch(`/api/compliance-adaptation.php?action=adaptation_strategies&type=${type}`);
            const result = await response.json();
            
            if (result.success) {
                this.adaptationStrategies = result.data;
                this.updateStrategiesDisplay();
            }
        } catch (error) {
            console.error('Error filtering strategies:', error);
        }
    }

    // View strategy details
    viewStrategyDetails(strategyId) {
        const strategy = this.adaptationStrategies.find(s => s.id === strategyId);
        if (!strategy) return;

        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Adaptation Strategy Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="strategy-details">
                            <div class="detail-header">
                                <div class="strategy-info">
                                    <h6>${strategy.strategy_name}</h6>
                                    <p>Type: ${strategy.strategy_type}</p>
                                </div>
                                <div class="strategy-meta">
                                    <span class="badge bg-${this.getStatusColor(strategy.implementation_status)}">${strategy.implementation_status}</span>
                                    <span class="badge bg-${this.getRiskColor(strategy.risk_level)}">${strategy.risk_level}</span>
                                </div>
                            </div>
                            
                            <div class="strategy-content">
                                <h6>Description</h6>
                                <p>${strategy.description}</p>
                            </div>
                            
                            <div class="strategy-metrics">
                                <h6>Performance Metrics</h6>
                                <div class="metrics-grid">
                                    <div class="metric-item">
                                        <span class="metric-label">Effectiveness Score:</span>
                                        <span class="metric-value">${Math.round(strategy.effectiveness_score || 0)}%</span>
                                    </div>
                                    <div class="metric-item">
                                        <span class="metric-label">Risk Level:</span>
                                        <span class="metric-value">${strategy.risk_level}</span>
                                    </div>
                                    <div class="metric-item">
                                        <span class="metric-label">Created:</span>
                                        <span class="metric-value">${new Date(strategy.created_at).toLocaleString()}</span>
                                    </div>
                                    ${strategy.implemented_at ? `
                                        <div class="metric-item">
                                            <span class="metric-label">Implemented:</span>
                                            <span class="metric-value">${new Date(strategy.implemented_at).toLocaleString()}</span>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        ${strategy.implementation_status === 'planned' ? `
                            <button type="button" class="btn btn-success" onclick="complianceAdaptation.implementStrategy(${strategy.id})">
                                <i class="fas fa-play"></i> Implement Strategy
                            </button>
                        ` : ''}
                        ${strategy.implementation_status === 'in_progress' ? `
                            <button type="button" class="btn btn-warning" onclick="complianceAdaptation.completeStrategy(${strategy.id})">
                                <i class="fas fa-check"></i> Mark Complete
                            </button>
                        ` : ''}
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

    // Implement strategy
    async implementStrategy(strategyId) {
        try {
            this.showNotification('Implementation', 'Implementing strategy...', 'info');
            
            // Simulate implementation
            setTimeout(() => {
                this.showNotification('Success', 'Strategy implementation started', 'success');
                this.loadAdaptationStrategies();
            }, 1500);
        } catch (error) {
            console.error('Error implementing strategy:', error);
            this.showNotification('Error', 'Failed to implement strategy', 'error');
        }
    }

    // Complete strategy
    async completeStrategy(strategyId) {
        try {
            this.showNotification('Completion', 'Completing strategy...', 'info');
            
            // Simulate completion
            setTimeout(() => {
                this.showNotification('Success', 'Strategy completed successfully', 'success');
                this.loadAdaptationStrategies();
            }, 1500);
        } catch (error) {
            console.error('Error completing strategy:', error);
            this.showNotification('Error', 'Failed to complete strategy', 'error');
        }
    }

    // Setup real-time monitoring
    setupRealTimeMonitoring() {
        if (!this.isMonitoring) return;

        // Monitor compliance status
        setInterval(() => {
            this.checkComplianceStatus();
        }, 60000); // Check every minute

        // Monitor adaptation strategies
        setInterval(() => {
            this.loadAdaptationStrategies();
        }, 30000); // Check every 30 seconds
    }

    // Check compliance status
    async checkComplianceStatus() {
        try {
            // Check for compliance issues
            const response = await fetch('/api/compliance-adaptation.php?action=compliance_monitoring');
            const result = await response.json();

            if (result.success) {
                const issues = result.data.compliance_issues;
                if (issues.some(issue => issue.count > 0)) {
                    this.showNotification('Compliance Alert', 'Compliance issues detected', 'warning');
                }
            }
        } catch (error) {
            console.error('Error checking compliance status:', error);
        }
    }

    // Setup event listeners
    setupEventListeners() {
        // Auto-refresh compliance dashboard
        setInterval(() => {
            this.loadComplianceDashboard();
        }, 60000); // Refresh every minute

        // Auto-refresh adaptation strategies
        setInterval(() => {
            this.loadAdaptationStrategies();
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

// Initialize compliance adaptation when page loads
let complianceAdaptation = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('compliance-dashboard') || document.getElementById('adaptation-strategies-display')) {
        complianceAdaptation = new ComplianceAdaptation();
        complianceAdaptation.initialize();
    }
});
