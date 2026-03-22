/**
 * AI Dashboard Manager - Advanced Analytics & AI Insights
 * Integrates AI risk assessment and advanced analytics into dashboard
 */
class AIDashboardManager {
    constructor() {
        this.token = localStorage.getItem('authToken');
        this.userRole = localStorage.getItem('userRole');
        this.dashboardData = null;
        this.aiInsights = null;
        this.refreshInterval = null;
        
        this.init();
    }
    
    init() {
        // Check if user has access to AI features
        if (!this.hasAIAccess()) {
            console.log('[AI Dashboard] User does not have AI access');
            return;
        }
        
        // Initialize dashboard
        this.loadDashboardData();
        this.setupAutoRefresh();
        this.initializeEventListeners();
        
        console.log('[AI Dashboard] AI Dashboard initialized');
    }
    
    hasAIAccess() {
        const allowedRoles = ['Super Admin', 'Admin', 'Manager', 'Owner'];
        return allowedRoles.includes(this.userRole);
    }
    
    async loadDashboardData() {
        try {
            // Load basic dashboard data
            const dashboardResponse = await fetch(`/api/advanced-analytics.php?action=dashboard&token=${this.token}`);
            const dashboardData = await dashboardResponse.json();
            
            if (dashboardData.success) {
                this.dashboardData = dashboardData.dashboard;
                this.renderDashboard();
            }
            
            // Load AI insights
            await this.loadAIInsights();
            
        } catch (error) {
            console.error('[AI Dashboard] Error loading dashboard data:', error);
            this.showErrorMessage('Failed to load dashboard data');
        }
    }
    
    async loadAIInsights() {
        try {
            // Load risk assessment insights
            const riskResponse = await fetch(`/api/ai-risk-assessment.php?action=batch_risk_assessment&limit=10&token=${this.token}`);
            const riskData = await riskResponse.json();
            
            // Load predictive analytics
            const predictiveResponse = await fetch(`/api/advanced-analytics.php?action=predictive_analytics&token=${this.token}`);
            const predictiveData = await predictiveResponse.json();
            
            // Load staff performance
            const staffResponse = await fetch(`/api/advanced-analytics.php?action=staff_performance&token=${this.token}`);
            const staffData = await staffResponse.json();
            
            this.aiInsights = {
                risk_assessment: riskData.success ? riskData.batch_results : [],
                predictive: predictiveData.success ? predictiveData.predictions : null,
                staff_performance: staffData.success ? staffData.performance : []
            };
            
            this.renderAIInsights();
            
        } catch (error) {
            console.error('[AI Dashboard] Error loading AI insights:', error);
        }
    }
    
    renderDashboard() {
        if (!this.dashboardData) return;
        
        this.renderKeyMetrics();
        this.renderPerformanceCharts();
        this.renderRiskMetrics();
        this.renderCollectionMetrics();
    }
    
    renderKeyMetrics() {
        const metrics = this.dashboardData.basic_metrics;
        const performance = this.dashboardData.period_performance;
        
        // Update metric cards
        this.updateMetricCard('total-members', metrics.total_members || 0);
        this.updateMetricCard('active-loans', metrics.active_loans || 0);
        this.updateMetricCard('total-portfolio', this.formatCurrency(metrics.total_loan_portfolio || 0));
        this.updateMetricCard('collection-rate', this.calculateCollectionRate() + '%');
        
        // Update period metrics
        this.updateMetricCard('new-loans', performance.new_loans || 0);
        this.updateMetricCard('new-loan-amount', this.formatCurrency(performance.new_loan_amount || 0));
        this.updateMetricCard('approval-rate', this.calculateApprovalRate(performance) + '%');
    }
    
    renderPerformanceCharts() {
        this.renderMemberGrowthChart();
        this.renderLoanPerformanceChart();
        this.renderRevenueChart();
    }
    
    renderRiskMetrics() {
        const risk = this.dashboardData.risk_metrics;
        
        this.updateMetricCard('default-rate', this.calculateDefaultRate(risk) + '%');
        this.updateMetricCard('late-rate', this.calculateLateRate(risk) + '%');
        
        this.renderRiskDistributionChart();
        this.renderRiskTrendChart();
    }
    
    renderCollectionMetrics() {
        const collection = this.dashboardData.collection_performance;
        
        this.updateMetricCard('total-collections', collection.total_collections || 0);
        this.updateMetricCard('total-collected', this.formatCurrency(collection.total_collected || 0));
        this.updateMetricCard('avg-collection', this.formatCurrency(collection.avg_collection || 0));
        
        this.renderCollectionEfficiencyChart();
    }
    
    renderAIInsights() {
        if (!this.aiInsights) return;
        
        this.renderRiskAssessmentInsights();
        this.renderPredictiveInsights();
        this.renderStaffPerformanceInsights();
        this.renderAIRecommendations();
    }
    
    renderRiskAssessmentInsights() {
        const riskData = this.aiInsights.risk_assessment;
        if (!riskData || riskData.length === 0) return;
        
        // Create risk distribution
        const riskDistribution = this.calculateRiskDistribution(riskData);
        
        // Render risk assessment chart
        this.createRiskAssessmentChart(riskData);
        
        // Update high-risk members list
        this.updateHighRiskMembersList(riskData);
        
        // Update risk insights
        this.updateRiskInsights(riskDistribution);
    }
    
    renderPredictiveInsights() {
        const predictive = this.aiInsights.predictive;
        if (!predictive) return;
        
        // Render next month predictions
        this.renderNextMonthPredictions(predictive.next_month_predictions);
        
        // Render churn prediction
        this.renderChurnPrediction(predictive.churn_prediction);
        
        // Render demand prediction
        this.renderDemandPrediction(predictive.demand_prediction);
    }
    
    renderStaffPerformanceInsights() {
        const staffData = this.aiInsights.staff_performance;
        if (!staffData || staffData.length === 0) return;
        
        // Create staff performance chart
        this.createStaffPerformanceChart(staffData);
        
        // Update top performers list
        this.updateTopPerformersList(staffData);
        
        // Update performance insights
        this.updatePerformanceInsights(staffData);
    }
    
    renderAIRecommendations() {
        const recommendations = this.generateAIRecommendations();
        
        const container = document.getElementById('ai-recommendations');
        if (!container) return;
        
        container.innerHTML = `
            <div class="ai-recommendations">
                <h6><i class="fas fa-lightbulb text-warning me-2"></i>AI Recommendations</h6>
                <div class="recommendations-list">
                    ${recommendations.map(rec => `
                        <div class="recommendation-item ${rec.priority}">
                            <div class="recommendation-icon">
                                <i class="fas fa-${rec.icon}"></i>
                            </div>
                            <div class="recommendation-content">
                                <h7>${rec.title}</h7>
                                <p class="text-muted small">${rec.description}</p>
                                <div class="recommendation-action">
                                    <button class="btn btn-sm btn-outline-primary" onclick="aiDashboard.handleRecommendation('${rec.action}', ${JSON.stringify(rec.data).replace(/"/g, '&quot;')})">
                                        ${rec.actionText}
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    generateAIRecommendations() {
        const recommendations = [];
        
        if (!this.dashboardData || !this.aiInsights) {
            return recommendations;
        }
        
        // Risk-based recommendations
        const riskMetrics = this.dashboardData.risk_metrics;
        const defaultRate = this.calculateDefaultRate(riskMetrics);
        
        if (defaultRate > 5) {
            recommendations.push({
                priority: 'high',
                icon: 'exclamation-triangle',
                title: 'High Default Rate Detected',
                description: `Default rate is ${defaultRate}%. Consider tightening credit criteria.`,
                action: 'review_risk_criteria',
                actionText: 'Review Criteria',
                data: { defaultRate }
            });
        }
        
        // Staff performance recommendations
        const staffData = this.aiInsights.staff_performance;
        const lowPerformers = staffData.filter(s => s.collection_rate < 70);
        
        if (lowPerformers.length > 0) {
            recommendations.push({
                priority: 'medium',
                icon: 'users',
                title: 'Staff Performance Issues',
                description: `${lowPerformers.length} staff members have collection rates below 70%.`,
                action: 'training_needed',
                actionText: 'Schedule Training',
                data: { staffIds: lowPerformers.map(s => s.staff_id) }
            });
        }
        
        // Collection efficiency recommendations
        const collectionRate = this.calculateCollectionRate();
        if (collectionRate < 80) {
            recommendations.push({
                priority: 'high',
                icon: 'chart-line',
                title: 'Low Collection Efficiency',
                description: `Collection rate is ${collectionRate}%. Consider optimizing routes.`,
                action: 'optimize_routes',
                actionText: 'Optimize Routes',
                data: { currentRate: collectionRate }
            });
        }
        
        // Growth opportunities
        const growthRate = this.calculateGrowthRate();
        if (growthRate < 10) {
            recommendations.push({
                priority: 'low',
                icon: 'rocket',
                title: 'Growth Opportunity',
                description: `Member growth rate is ${growthRate}%. Consider marketing initiatives.`,
                action: 'growth_initiative',
                actionText: 'Plan Growth',
                data: { growthRate }
            });
        }
        
        return recommendations;
    }
    
    // Chart rendering methods
    renderMemberGrowthChart() {
        const growthData = this.dashboardData.member_growth;
        if (!growthData || growthData.length === 0) return;
        
        const ctx = document.getElementById('member-growth-chart');
        if (!ctx) return;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: growthData.map(d => this.formatDate(d.date)),
                datasets: [{
                    label: 'New Members',
                    data: growthData.map(d => d.new_members),
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
    
    renderLoanPerformanceChart() {
        const performance = this.dashboardData.period_performance;
        
        const ctx = document.getElementById('loan-performance-chart');
        if (!ctx) return;
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Pending'],
                datasets: [{
                    data: [performance.approved_loans || 0, (performance.new_loans || 0) - (performance.approved_loans || 0)],
                    backgroundColor: ['#28a745', '#ffc107']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
    
    renderRevenueChart() {
        // Implementation for revenue chart
        const ctx = document.getElementById('revenue-chart');
        if (!ctx) return;
        
        // Sample data - replace with actual revenue data
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                datasets: [{
                    label: 'Revenue',
                    data: [12000000, 15000000, 13000000, 17000000],
                    backgroundColor: '#17a2b8'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => this.formatCurrency(value)
                        }
                    }
                }
            }
        });
    }
    
    createRiskAssessmentChart(riskData) {
        const ctx = document.getElementById('risk-assessment-chart');
        if (!ctx) return;
        
        const riskCategories = ['LOW', 'MEDIUM', 'HIGH', 'VERY_HIGH'];
        const categoryCounts = riskCategories.map(cat => 
            riskData.filter(r => r.risk_category === cat).length
        );
        
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: riskCategories,
                datasets: [{
                    data: categoryCounts,
                    backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
    
    createStaffPerformanceChart(staffData) {
        const ctx = document.getElementById('staff-performance-chart');
        if (!ctx) return;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: staffData.slice(0, 5).map(s => s.staff_name),
                datasets: [{
                    label: 'Collection Rate (%)',
                    data: staffData.slice(0, 5).map(s => s.collection_rate),
                    backgroundColor: staffData.slice(0, 5).map(s => 
                        s.collection_rate >= 80 ? '#28a745' : 
                        s.collection_rate >= 60 ? '#ffc107' : '#dc3545'
                    )
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { 
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    }
    
    // Utility methods
    updateMetricCard(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }
    
    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }
    
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    }
    
    calculateCollectionRate() {
        const collection = this.dashboardData.collection_performance;
        if (!collection.total_expected || collection.total_expected === 0) return 0;
        return Math.round((collection.total_collected / collection.total_expected) * 100);
    }
    
    calculateApprovalRate(performance) {
        if (!performance.new_loans || performance.new_loans === 0) return 0;
        return Math.round((performance.approved_loans / performance.new_loans) * 100);
    }
    
    calculateDefaultRate(risk) {
        if (!risk.total_loans_period || risk.total_loans_period === 0) return 0;
        return Math.round((risk.defaults / risk.total_loans_period) * 100);
    }
    
    calculateLateRate(risk) {
        if (!risk.total_loans_period || risk.total_loans_period === 0) return 0;
        return Math.round((risk.late_payments / risk.total_loans_period) * 100);
    }
    
    calculateGrowthRate() {
        // Simple growth rate calculation based on new members
        const growth = this.dashboardData.member_growth;
        if (!growth || growth.length < 2) return 0;
        
        const recent = growth.slice(0, 7).reduce((sum, d) => sum + d.new_members, 0);
        const previous = growth.slice(7, 14).reduce((sum, d) => sum + d.new_members, 0);
        
        return previous > 0 ? Math.round(((recent - previous) / previous) * 100) : 0;
    }
    
    calculateRiskDistribution(riskData) {
        const distribution = { LOW: 0, MEDIUM: 0, HIGH: 0, VERY_HIGH: 0 };
        riskData.forEach(member => {
            distribution[member.risk_category]++;
        });
        return distribution;
    }
    
    // Event handling
    setupAutoRefresh() {
        // Refresh data every 5 minutes
        this.refreshInterval = setInterval(() => {
            this.loadDashboardData();
        }, 5 * 60 * 1000);
    }
    
    initializeEventListeners() {
        // Manual refresh button
        const refreshBtn = document.getElementById('refresh-dashboard');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.loadDashboardData();
            });
        }
        
        // Period selector
        const periodSelector = document.getElementById('period-selector');
        if (periodSelector) {
            periodSelector.addEventListener('change', (e) => {
                this.changePeriod(e.target.value);
            });
        }
    }
    
    async changePeriod(period) {
        try {
            const response = await fetch(`/api/advanced-analytics.php?action=dashboard&period=${period}&token=${this.token}`);
            const data = await response.json();
            
            if (data.success) {
                this.dashboardData = data.dashboard;
                this.renderDashboard();
            }
        } catch (error) {
            console.error('[AI Dashboard] Error changing period:', error);
        }
    }
    
    handleRecommendation(action, data) {
        switch (action) {
            case 'review_risk_criteria':
                this.openRiskReviewModal(data);
                break;
            case 'training_needed':
                this.openTrainingModal(data);
                break;
            case 'optimize_routes':
                this.openRouteOptimizationModal(data);
                break;
            case 'growth_initiative':
                this.openGrowthInitiativeModal(data);
                break;
            default:
                console.log('[AI Dashboard] Unknown recommendation action:', action);
        }
    }
    
    // Modal handlers
    openRiskReviewModal(data) {
        // Implementation for risk review modal
        console.log('[AI Dashboard] Opening risk review modal:', data);
    }
    
    openTrainingModal(data) {
        // Implementation for training modal
        console.log('[AI Dashboard] Opening training modal:', data);
    }
    
    openRouteOptimizationModal(data) {
        // Implementation for route optimization modal
        console.log('[AI Dashboard] Opening route optimization modal:', data);
    }
    
    openGrowthInitiativeModal(data) {
        // Implementation for growth initiative modal
        console.log('[AI Dashboard] Opening growth initiative modal:', data);
    }
    
    showErrorMessage(message) {
        // Show error message to user
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.dashboard-content');
        if (container) {
            container.prepend(alert);
        }
    }
    
    // Cleanup
    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
    }
}

// Initialize AI Dashboard when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.aiDashboard = new AIDashboardManager();
    });
} else {
    window.aiDashboard = new AIDashboardManager();
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AIDashboardManager;
}
