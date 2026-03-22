// Error Handling Wrapper
(function() {
    /**
 * KSP Lam Gabe Jaya - Dashboard JavaScript
 * Dashboard-specific functionality
 */

// Dashboard initialization
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

function initializeDashboard() {
    console.log('Dashboard module initialized');
    
    // Initialize charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        initializeCharts();
    }
    
    // Bind dashboard events
    bindDashboardEvents();
}

function initializeCharts() {
    // Growth Chart
    const growthCtx = document.getElementById('growthChart');
    if (growthCtx) {
        new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Anggota',
                    data: [1000, 1100, 1150, 1200, 1234, 1234],
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
    
    // Member Distribution Chart
    const memberCtx = document.getElementById('memberChart');
    if (memberCtx) {
        new Chart(memberCtx, {
            type: 'doughnut',
            data: {
                labels: ['Aktif', 'Tidak Aktif', 'Baru'],
                datasets: [{
                    data: [1234, 56, 234],
                    backgroundColor: ['#0d6efd', '#dc3545', '#ffc107']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
}

function bindDashboardEvents() {
    // Refresh button
    const refreshBtn = document.querySelector('[onclick="refreshDashboard()"]');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            refreshDashboard();
        });
    }
}

function refreshDashboard() {
    // Show loading state
    const cards = document.querySelectorAll('.stat-card');
    cards.forEach(card => {
        card.style.opacity = '0.6';
    });
    
    // Simulate data refresh
    setTimeout(() => {
        cards.forEach(card => {
            card.style.opacity = '1';
        });
        
        if (typeof showAlert === 'function') {
            showAlert('success', 'Dashboard berhasil diperbarui!');
        }
    }, 1000);
}

// Export for global access
window.dashboardFunctions = {
    refreshDashboard,
    initializeCharts
};

})();