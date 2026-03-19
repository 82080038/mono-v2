
// Advanced Dashboard Widgets
class DashboardWidgets {
    constructor() {
        this.widgets = {
            'member_stats': this.createMemberStatsWidget,
            'loan_stats': this.createLoanStatsWidget,
            'savings_stats': this.createSavingsStatsWidget,
            'financial_overview': this.createFinancialOverviewWidget,
            'recent_activity': this.createRecentActivityWidget,
            'quick_actions': this.createQuickActionsWidget,
            'notifications': this.createNotificationsWidget,
            'charts': this.createChartsWidget
        };
    }
    
    createMemberStatsWidget() {
        return {
            type: 'stats',
            title: 'Member Statistics',
            icon: 'users',
            data: this.fetchMemberStats()
        };
    }
    
    createLoanStatsWidget() {
        return {
            type: 'stats',
            title: 'Loan Statistics',
            icon: 'money-check-alt',
            data: this.fetchLoanStats()
        };
    }
    
    createSavingsStatsWidget() {
        return {
            type: 'stats',
            title: 'Savings Statistics',
            icon: 'piggy-bank',
            data: this.fetchSavingsStats()
        };
    }
    
    fetchMemberStats() {
        // Fetch member statistics from API
        return fetch('/api/crud.php?path=members&stats=true')
            .then(response => response.json());
    }
}
