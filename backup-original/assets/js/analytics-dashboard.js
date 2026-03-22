// Real-time Analytics Dashboard
class AnalyticsDashboard {
    constructor() {
        this.data = {};
        this.charts = {};
        this.refreshInterval = null;
        this.isRealTime = true;
    }

    // Initialize dashboard
    async initialize() {
        await this.loadDashboardData();
        this.setupRealTimeUpdates();
        this.renderCharts();
        this.setupEventListeners();
    }

    // Load dashboard data
    async loadDashboardData() {
        try {
            const response = await fetch('/api/analytics-engine.php?action=dashboard');
            const result = await response.json();
            
            if (result.success) {
                this.data = result.data;
                this.updateDashboardDisplay();
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    }

    // Update dashboard display
    updateDashboardDisplay() {
        this.updateMetricsCards();
        this.updateQuickStats();
        this.updateActivityFeed();
    }

    // Update metrics cards
    updateMetricsCards() {
        const metrics = this.data;
        
        document.getElementById('today-collections').innerHTML = `
            <div class="metric-value">${metrics.today_collections.count}</div>
            <div class="metric-label">Transaksi</div>
            <div class="metric-change">Rp ${metrics.today_collections.amount.toLocaleString('id-ID')}</div>
        `;
        
        document.getElementById('active-staff').innerHTML = `
            <div class="metric-value">${metrics.active_staff}</div>
            <div class="metric-label">Staff Aktif</div>
            <div class="metric-change">Hari ini</div>
        `;
        
        document.getElementById('total-members').innerHTML = `
            <div class="metric-value">${metrics.total_members}</div>
            <div class="metric-label">Total Anggota</div>
            <div class="metric-change">Terdaftar</div>
        `;
        
        document.getElementById('active-loans').innerHTML = `
            <div class="metric-value">${metrics.active_loans}</div>
            <div class="metric-label">Pinjaman Aktif</div>
            <div class="metric-change">Berjalan</div>
        `;
        
        document.getElementById('overdue-payments').innerHTML = `
            <div class="metric-value">${metrics.overdue_payments}</div>
            <div class="metric-label">Jatuh Tempo</div>
            <div class="metric-change text-danger">Perlu ditindak</div>
        `;
    }

    // Update quick stats
    updateQuickStats() {
        const quickStatsContainer = document.getElementById('quick-stats');
        if (!quickStatsContainer) return;

        quickStatsContainer.innerHTML = `
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="stat-content">
                            <h6>Total Collections</h6>
                            <p>Rp ${this.data.today_collections.amount.toLocaleString('id-ID')}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h6>Active Staff</h6>
                            <p>${this.data.active_staff} orang</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-info">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-content">
                            <h6>Efficiency Rate</h6>
                            <p>87.5%</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-content">
                            <h6>Overdue</h6>
                            <p>${this.data.overdue_payments} pinjaman</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Update activity feed
    updateActivityFeed() {
        const feedContainer = document.getElementById('activity-feed');
        if (!feedContainer) return;

        // Simulate recent activities
        const activities = [
            { type: 'payment', user: 'Budi Santoso', amount: 500000, time: '2 menit lalu' },
            { type: 'visit', user: 'Staff Ahmad', location: 'Jl. Merdeka', time: '5 menit lalu' },
            { type: 'loan', user: 'Siti Nurhaliza', amount: 2000000, time: '10 menit lalu' },
            { type: 'payment', user: 'Ahmad Fauzi', amount: 750000, time: '15 menit lalu' }
        ];

        feedContainer.innerHTML = activities.map(activity => `
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas ${this.getActivityIcon(activity.type)}"></i>
                </div>
                <div class="activity-content">
                    <p class="activity-text">${this.getActivityText(activity)}</p>
                    <small class="text-muted">${activity.time}</small>
                </div>
            </div>
        `).join('');
    }

    // Get activity icon
    getActivityIcon(type) {
        const icons = {
            'payment': 'fa-money-bill-wave text-success',
            'visit': 'fa-map-marker-alt text-info',
            'loan': 'fa-hand-holding-usd text-warning',
            'collection': 'fa-coins text-primary'
        };
        return icons[type] || 'fa-circle text-secondary';
    }

    // Get activity text
    getActivityText(activity) {
        switch (activity.type) {
            case 'payment':
                return `<strong>${activity.user}</strong> melakukan pembayaran Rp ${activity.amount.toLocaleString('id-ID')}`;
            case 'visit':
                return `<strong>${activity.user}</strong> mengunjungi ${activity.location}`;
            case 'loan':
                return `<strong>${activity.user}</strong> mengajukan pinjaman Rp ${activity.amount.toLocaleString('id-ID')}`;
            case 'collection':
                return `<strong>${activity.user}</strong> melakukan penagihan`;
            default:
                return `<strong>${activity.user}</strong> melakukan aktivitas`;
        }
    }

    // Setup real-time updates
    setupRealTimeUpdates() {
        if (!this.isRealTime) return;

        this.refreshInterval = setInterval(() => {
            this.loadDashboardData();
        }, 30000); // Update every 30 seconds
    }

    // Render charts
    renderCharts() {
        this.renderCollectionsChart();
        this.renderStaffPerformanceChart();
        this.renderFinancialTrendChart();
    }

    // Render collections chart
    renderCollectionsChart() {
        const ctx = document.getElementById('collections-chart');
        if (!ctx) return;

        // This would integrate with Chart.js or similar library
        ctx.innerHTML = `
            <div class="chart-placeholder">
                <h6>Collections Trend (Last 7 Days)</h6>
                <div class="chart-bars">
                    ${this.generateMockChartData(7)}
                </div>
            </div>
        `;
    }

    // Render staff performance chart
    renderStaffPerformanceChart() {
        const ctx = document.getElementById('staff-performance-chart');
        if (!ctx) return;

        ctx.innerHTML = `
            <div class="chart-placeholder">
                <h6>Staff Performance Today</h6>
                <div class="performance-list">
                    ${this.generateMockStaffData()}
                </div>
            </div>
        `;
    }

    // Render financial trend chart
    renderFinancialTrendChart() {
        const ctx = document.getElementById('financial-trend-chart');
        if (!ctx) return;

        ctx.innerHTML = `
            <div class="chart-placeholder">
                <h6>Financial Trend (Last 30 Days)</h6>
                <div class="trend-chart">
                    ${this.generateMockTrendData()}
                </div>
            </div>
        `;
    }

    // Generate mock chart data
    generateMockChartData(days) {
        const data = [];
        for (let i = 0; i < days; i++) {
            const height = Math.random() * 80 + 20;
            data.push(`
                <div class="chart-bar" style="height: ${height}%">
                    <div class="bar-label">Day ${i + 1}</div>
                    <div class="bar-value">Rp ${(height * 10000).toLocaleString('id-ID')}</div>
                </div>
            `);
        }
        return data.join('');
    }

    // Generate mock staff data
    generateMockStaffData() {
        const staff = [
            { name: 'Ahmad', collections: 5, amount: 2500000, efficiency: 92 },
            { name: 'Budi', collections: 7, amount: 3200000, efficiency: 88 },
            { name: 'Siti', collections: 4, amount: 1800000, efficiency: 95 }
        ];

        return staff.map(member => `
            <div class="staff-performance-item">
                <div class="staff-info">
                    <strong>${member.name}</strong>
                    <small>${member.collections} collections</small>
                </div>
                <div class="staff-stats">
                    <span class="amount">Rp ${member.amount.toLocaleString('id-ID')}</span>
                    <span class="efficiency ${member.efficiency >= 90 ? 'text-success' : 'text-warning'}">
                        ${member.efficiency}%
                    </span>
                </div>
            </div>
        `).join('');
    }

    // Generate mock trend data
    generateMockTrendData() {
        const data = [];
        for (let i = 0; i < 30; i++) {
            const value = Math.random() * 10000000 + 5000000;
            data.push(`
                <div class="trend-point" style="left: ${(i / 30) * 100}%; bottom: ${(value / 15000000) * 100}%">
                    <div class="trend-value">Rp ${value.toLocaleString('id-ID')}</div>
                </div>
            `);
        }
        return data.join('');
    }

    // Setup event listeners
    setupEventListeners() {
        // Real-time toggle
        const realtimeToggle = document.getElementById('realtime-toggle');
        if (realtimeToggle) {
            realtimeToggle.addEventListener('change', (e) => {
                this.isRealTime = e.target.checked;
                if (this.isRealTime) {
                    this.setupRealTimeUpdates();
                } else {
                    clearInterval(this.refreshInterval);
                }
            });
        }

        // Refresh button
        const refreshBtn = document.getElementById('refresh-dashboard');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.loadDashboardData();
            });
        }

        // Date filter
        const dateFilter = document.getElementById('date-filter');
        if (dateFilter) {
            dateFilter.addEventListener('change', (e) => {
                this.filterDataByDate(e.target.value);
            });
        }

        // Export button
        const exportBtn = document.getElementById('export-data');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                this.exportData();
            });
        }
    }

    // Filter data by date
    async filterDataByDate(date) {
        try {
            const response = await fetch(`/api/analytics-engine.php?action=dashboard&date=${date}`);
            const result = await response.json();
            
            if (result.success) {
                this.data = result.data;
                this.updateDashboardDisplay();
            }
        } catch (error) {
            console.error('Error filtering data:', error);
        }
    }

    // Export data
    exportData() {
        // Create CSV data
        const csvData = this.generateCSVData();
        
        // Download CSV
        const blob = new Blob([csvData], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `analytics-export-${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
    }

    // Generate CSV data
    generateCSVData() {
        const headers = ['Date', 'Collections', 'Amount', 'Active Staff', 'Efficiency'];
        const rows = [headers.join(',')];

        // Add mock data
        for (let i = 0; i < 7; i++) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            
            const row = [
                date.toISOString().split('T')[0],
                Math.floor(Math.random() * 50) + 10,
                Math.floor(Math.random() * 10000000) + 5000000,
                Math.floor(Math.random() * 5) + 1,
                (Math.random() * 20 + 80).toFixed(1)
            ];
            
            rows.push(row.join(','));
        }

        return rows.join('\n');
    }

    // Cleanup
    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
    }
}

// Initialize analytics dashboard when page loads
let analyticsDashboard = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('analytics-dashboard')) {
        analyticsDashboard = new AnalyticsDashboard();
        analyticsDashboard.initialize();
    }
});

// Cleanup when page unloads
window.addEventListener('beforeunload', () => {
    if (analyticsDashboard) {
        analyticsDashboard.destroy();
    }
});
