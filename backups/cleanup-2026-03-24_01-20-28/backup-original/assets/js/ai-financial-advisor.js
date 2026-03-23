// AI Financial Advisor System
class AIFinancialAdvisor {
    constructor() {
        this.recommendations = [];
        this.forecasts = [];
        this.riskAnalysis = {};
        this.capitalAdvice = {};
        this.memberAnalysis = {};
        this.isAnalyzing = false;
    }

    // Initialize AI advisor
    async initialize() {
        await this.loadDashboardData();
        await this.loadRecommendations();
        await this.loadRiskAnalysis();
        this.setupRealTimeAnalysis();
        this.setupEventListeners();
    }

    // Load dashboard data
    async loadDashboardData() {
        try {
            const response = await fetch('/api/ai-financial-advisor.php?action=dashboard');
            const result = await response.json();
            
            if (result.success) {
                this.updateDashboardDisplay(result.data);
            }
        } catch (error) {
            console.error('Error loading AI dashboard:', error);
        }
    }

    // Load recommendations
    async loadRecommendations() {
        try {
            const response = await fetch('/api/ai-financial-advisor.php?action=recommendations');
            const result = await response.json();
            
            if (result.success) {
                this.recommendations = result.data;
                this.updateRecommendationsDisplay();
            }
        } catch (error) {
            console.error('Error loading recommendations:', error);
        }
    }

    // Load risk analysis
    async loadRiskAnalysis() {
        try {
            const response = await fetch('/api/ai-financial-advisor.php?action=risk_analysis');
            const result = await response.json();
            
            if (result.success) {
                this.riskAnalysis = result.data;
                this.updateRiskDisplay();
            }
        } catch (error) {
            console.error('Error loading risk analysis:', error);
        }
    }

    // Update dashboard display
    updateDashboardDisplay(data) {
        const container = document.getElementById('ai-dashboard');
        if (!container) return;

        container.innerHTML = `
            <div class="ai-dashboard-header">
                <h5>🤖 AI Financial Advisor</h5>
                <div class="ai-status">
                    <span class="status-indicator active"></span>
                    <span>AI Active</span>
                </div>
            </div>
            
            <div class="ai-metrics-grid">
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div class="metric-content">
                        <h6>Active Recommendations</h6>
                        <h3>${data.recommendation_stats.total_recommendations}</h3>
                        <small>${data.recommendation_stats.critical_recommendations} Critical</small>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="metric-content">
                        <h6>AI Confidence</h6>
                        <h3>${Math.round(data.recommendation_stats.avg_confidence || 0)}%</h3>
                        <small>Average Accuracy</small>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="metric-content">
                        <h6>Implemented</h6>
                        <h3>${data.recommendation_stats.implemented_recommendations}</h3>
                        <small>Successfully Applied</small>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <div class="metric-content">
                        <h6>AI Models</h6>
                        <h3>5</h3>
                        <small>Active Algorithms</small>
                    </div>
                </div>
            </div>
            
            <div class="ai-forecasts-section">
                <h6>📊 AI Forecasts</h6>
                <div class="forecasts-grid">
                    ${data.forecasts.map(forecast => `
                        <div class="forecast-card">
                            <div class="forecast-type">${forecast.forecast_type}</div>
                            <div class="forecast-value">Rp ${forecast.predicted_value.toLocaleString('id-ID')}</div>
                            <div class="forecast-date">${forecast.forecast_date}</div>
                            <div class="confidence-range">
                                <small>Confidence: ${Math.round((forecast.confidence_interval?.upper || 0) * 100)}%</small>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div class="ai-metrics-section">
                <h6>📈 Business Metrics</h6>
                <div class="metrics-timeline">
                    ${data.metrics.map(metric => `
                        <div class="metric-item">
                            <div class="metric-info">
                                <span class="metric-name">${metric.metric_name}</span>
                                <span class="metric-value">Rp ${metric.metric_value.toLocaleString('id-ID')}</span>
                            </div>
                            <div class="metric-trend ${metric.trend_direction}">
                                <i class="fas fa-arrow-${metric.trend_direction}"></i>
                                <span>${metric.trend_percentage}%</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    // Update recommendations display
    updateRecommendationsDisplay() {
        const container = document.getElementById('ai-recommendations');
        if (!container) return;

        container.innerHTML = `
            <div class="recommendations-header">
                <h6>🎯 AI Recommendations</h6>
                <button class="btn btn-sm btn-primary" onclick="aiAdvisor.generateNewRecommendations()">
                    <i class="fas fa-sync"></i> Generate New
                </button>
            </div>
            
            <div class="recommendations-list">
                ${this.recommendations.map(rec => `
                    <div class="recommendation-card priority-${rec.priority}">
                        <div class="recommendation-header">
                            <div class="recommendation-type">
                                <i class="fas ${this.getRecommendationIcon(rec.recommendation_type)}"></i>
                                <span>${rec.recommendation_type}</span>
                            </div>
                            <div class="recommendation-priority">
                                <span class="badge bg-${this.getPriorityColor(rec.priority)}">${rec.priority}</span>
                            </div>
                        </div>
                        <div class="recommendation-content">
                            <h6>${rec.title}</h6>
                            <p>${rec.description}</p>
                            <div class="recommendation-meta">
                                <small>Confidence: ${Math.round(rec.confidence_score)}%</small>
                                <small>Created: ${new Date(rec.created_at).toLocaleDateString()}</small>
                            </div>
                        </div>
                        <div class="recommendation-actions">
                            <button class="btn btn-sm btn-outline-primary" onclick="aiAdvisor.viewRecommendationDetails(${rec.id})">
                                <i class="fas fa-eye"></i> Details
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="aiAdvisor.implementRecommendation(${rec.id})">
                                <i class="fas fa-check"></i> Implement
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="aiAdvisor.dismissRecommendation(${rec.id})">
                                <i class="fas fa-times"></i> Dismiss
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    // Update risk display
    updateRiskDisplay() {
        const container = document.getElementById('ai-risk-analysis');
        if (!container) return;

        const overallRisk = this.riskAnalysis.overall_risk;
        
        container.innerHTML = `
            <div class="risk-analysis-header">
                <h6>⚠️ Risk Analysis</h6>
                <div class="overall-risk-score">
                    <div class="risk-circle ${overallRisk.risk_level}">
                        <span class="risk-score">${Math.round(overallRisk.overall_risk_score)}</span>
                    </div>
                    <div class="risk-label">
                        <span class="risk-level">${overallRisk.risk_level.toUpperCase()}</span>
                        <small>Overall Risk</small>
                    </div>
                </div>
            </div>
            
            <div class="risk-factors">
                <h6>Risk Factors</h6>
                <div class="risk-grid">
                    ${Object.entries(overallRisk.risk_breakdown).map(([key, risk]) => `
                        <div class="risk-factor">
                            <div class="risk-header">
                                <span class="risk-name">${this.formatRiskName(key)}</span>
                                <span class="risk-score">${Math.round(risk.risk_score)}</span>
                            </div>
                            <div class="risk-bar">
                                <div class="risk-progress ${risk.risk_level}" style="width: ${risk.risk_score}%"></div>
                            </div>
                            <div class="risk-details">
                                <small>${this.getRiskDescription(key, risk)}</small>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div class="risk-recommendations">
                <h6>🛡️ Risk Mitigation Recommendations</h6>
                <div class="recommendations-list">
                    ${overallRisk.recommendations.map(rec => `
                        <div class="risk-recommendation">
                            <div class="recommendation-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="recommendation-text">
                                <p>${rec}</p>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    // Get recommendation icon
    getRecommendationIcon(type) {
        const icons = {
            'capital': 'fa-money-bill-wave',
            'expansion': 'fa-chart-line',
            'risk': 'fa-exclamation-triangle',
            'efficiency': 'fa-cogs',
            'growth': 'fa-seedling'
        };
        return icons[type] || 'fa-lightbulb';
    }

    // Get priority color
    getPriorityColor(priority) {
        const colors = {
            'critical': 'danger',
            'high': 'warning',
            'medium': 'info',
            'low': 'secondary'
        };
        return colors[priority] || 'secondary';
    }

    // Format risk name
    formatRiskName(key) {
        const names = {
            'credit_risk': 'Credit Risk',
            'liquidity_risk': 'Liquidity Risk',
            'operational_risk': 'Operational Risk',
            'market_risk': 'Market Risk',
            'compliance_risk': 'Compliance Risk'
        };
        return names[key] || key;
    }

    // Get risk description
    getRiskDescription(key, risk) {
        const descriptions = {
            'credit_risk': `${risk.overdue_loans} overdue loans out of ${risk.total_loans}`,
            'liquidity_risk': `Current ratio: ${risk.current_ratio.toFixed(2)}`,
            'operational_risk': `Staff efficiency: ${risk.staff_efficiency}%`,
            'market_risk': `Market share: ${risk.market_share}%`,
            'compliance_risk': `Compliance score: ${risk.compliance_score}%`
        };
        return descriptions[key] || 'Risk factor analysis';
    }

    // Generate new recommendations
    async generateNewRecommendations() {
        try {
            this.showNotification('AI Analysis', 'Generating new recommendations...', 'info');
            
            // Simulate AI processing
            setTimeout(async () => {
                await this.loadRecommendations();
                this.showNotification('AI Analysis', 'New recommendations generated', 'success');
            }, 2000);
        } catch (error) {
            console.error('Error generating recommendations:', error);
            this.showNotification('Error', 'Failed to generate recommendations', 'error');
        }
    }

    // View recommendation details
    viewRecommendationDetails(recommendationId) {
        const recommendation = this.recommendations.find(r => r.id === recommendationId);
        if (!recommendation) return;

        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">AI Recommendation Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="recommendation-details">
                            <div class="detail-header">
                                <div class="recommendation-type">
                                    <i class="fas ${this.getRecommendationIcon(recommendation.recommendation_type)}"></i>
                                    <span>${recommendation.recommendation_type}</span>
                                </div>
                                <div class="recommendation-meta">
                                    <span class="badge bg-${this.getPriorityColor(recommendation.priority)}">${recommendation.priority}</span>
                                    <span class="confidence">Confidence: ${Math.round(recommendation.confidence_score)}%</span>
                                </div>
                            </div>
                            
                            <div class="recommendation-content">
                                <h6>${recommendation.title}</h6>
                                <p>${recommendation.description}</p>
                            </div>
                            
                            <div class="recommendation-data">
                                <h6>📊 Supporting Data</h6>
                                <div class="data-grid">
                                    ${recommendation.data ? Object.entries(recommendation.data).map(([key, value]) => `
                                        <div class="data-item">
                                            <span class="data-key">${key}:</span>
                                            <span class="data-value">${value}</span>
                                        </div>
                                    `).join('') : '<p>No supporting data available</p>'}
                                </div>
                            </div>
                            
                            <div class="recommendation-impact">
                                <h6>📈 Expected Impact</h6>
                                <p>This recommendation is expected to improve business performance by reducing risks and increasing efficiency.</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="aiAdvisor.implementRecommendation(${recommendation.id})">
                            <i class="fas fa-check"></i> Implement
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

    // Implement recommendation
    async implementRecommendation(recommendationId) {
        try {
            this.showNotification('Implementation', 'Implementing recommendation...', 'info');
            
            // Simulate implementation
            setTimeout(() => {
                this.showNotification('Success', 'Recommendation implemented successfully', 'success');
                this.loadRecommendations();
            }, 1500);
        } catch (error) {
            console.error('Error implementing recommendation:', error);
            this.showNotification('Error', 'Failed to implement recommendation', 'error');
        }
    }

    // Dismiss recommendation
    async dismissRecommendation(recommendationId) {
        try {
            // Close modal if open
            const modal = document.querySelector('.modal.show');
            if (modal) {
                bootstrap.Modal.getInstance(modal).hide();
            }
            
            this.showNotification('Dismissed', 'Recommendation dismissed', 'info');
            this.loadRecommendations();
        } catch (error) {
            console.error('Error dismissing recommendation:', error);
        }
    }

    // Setup real-time analysis
    setupRealTimeAnalysis() {
        if (!this.isAnalyzing) return;

        // Analyze data every 5 minutes
        setInterval(() => {
            this.performRealTimeAnalysis();
        }, 300000); // 5 minutes
    }

    // Perform real-time analysis
    async performRealTimeAnalysis() {
        try {
            // Check for anomalies and generate alerts
            await this.checkForAnomalies();
            
            // Update forecasts
            await this.updateForecasts();
            
            // Refresh recommendations
            await this.loadRecommendations();
        } catch (error) {
            console.error('Error in real-time analysis:', error);
        }
    }

    // Check for anomalies
    async checkForAnomalies() {
        // Implementation for anomaly detection
        console.log('Checking for financial anomalies...');
    }

    // Update forecasts
    async updateForecasts() {
        // Implementation for forecast updates
        console.log('Updating financial forecasts...');
    }

    // Setup event listeners
    setupEventListeners() {
        // Auto-refresh dashboard data
        setInterval(() => {
            this.loadDashboardData();
        }, 60000); // Refresh every minute

        // Auto-refresh risk analysis
        setInterval(() => {
            this.loadRiskAnalysis();
        }, 300000); // Refresh every 5 minutes
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

// Initialize AI advisor when page loads
let aiAdvisor = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('ai-dashboard') || document.getElementById('ai-recommendations')) {
        aiAdvisor = new AIFinancialAdvisor();
        aiAdvisor.initialize();
    }
});
