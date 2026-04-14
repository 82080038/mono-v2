<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        #mainContent{margin-left:0}
        .page-header { background: linear-gradient(135deg,#0f2027,#203a43,#2c5364); color:#fff; padding:1.5rem 2rem; border-radius:12px; margin-bottom:1.5rem; }
        .kpi-card { background:#fff; border-radius:12px; padding:1.25rem 1.5rem; box-shadow:0 2px 8px rgba(0,0,0,.07); border-left:5px solid #e5e7eb; transition:transform .2s; }
        .kpi-card:hover { transform:translateY(-2px); }
        .kpi-card.primary  { border-color:#2563eb; }
        .kpi-card.success  { border-color:#16a34a; }
        .kpi-card.info     { border-color:#0891b2; }
        .kpi-card.warning  { border-color:#d97706; }
        .kpi-card.danger   { border-color:#dc2626; }
        .kpi-card.purple   { border-color:#7c3aed; }
        .kpi-value { font-size:1.8rem; font-weight:800; line-height:1; }
        .chart-card { background:#fff; border-radius:12px; padding:1.25rem; box-shadow:0 2px 8px rgba(0,0,0,.07); }
        .chart-card .chart-title { font-size:.9rem; font-weight:700; color:#374151; margin-bottom:1rem; }
        canvas { max-height:260px; }
    </style>
</head>
<body>
<?php $activePage = 'analytics'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">Analytics &amp; Statistik</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Analytics</li></ol></nav></div>
        <div class="ms-auto d-flex align-items-center gap-2">
            <select id="periodSelect" class="form-select form-select-sm" style="width:130px">
                <option value="6">6 Bulan</option>
                <option value="12" selected>12 Bulan</option>
                <option value="24">24 Bulan</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary" onclick="refreshAll()"><i class="fas fa-sync-alt"></i></button>
        </div>
    </div>
<div class="page-body">

    <!-- KPI Cards -->
    <div class="row g-3 mb-4" id="kpiRow">
        <div class="col-6 col-md-2"><div class="kpi-card primary"><div class="text-muted small">Total Anggota</div><div class="kpi-value text-primary" id="kpiMembers">—</div><small class="text-success" id="kpiMembersNew"></small></div></div>
        <div class="col-6 col-md-2"><div class="kpi-card success"><div class="text-muted small">Pinjaman Aktif</div><div class="kpi-value text-success" id="kpiLoans">—</div><small class="text-muted" id="kpiLoansNew"></small></div></div>
        <div class="col-6 col-md-2"><div class="kpi-card info"><div class="text-muted small">Outstanding</div><div class="kpi-value text-info" id="kpiOutstanding">—</div><small class="text-muted">saldo pinjaman</small></div></div>
        <div class="col-6 col-md-2"><div class="kpi-card purple"><div class="text-muted small">Total Simpanan</div><div class="kpi-value" style="color:#7c3aed" id="kpiSavings">—</div><small class="text-muted" id="kpiSavingsAcc"></small></div></div>
        <div class="col-6 col-md-2"><div class="kpi-card danger"><div class="text-muted small">NPL</div><div class="kpi-value text-danger" id="kpiNpl">—</div><small class="text-muted">kredit macet</small></div></div>
        <div class="col-6 col-md-2"><div class="kpi-card warning"><div class="text-muted small">Approval Pending</div><div class="kpi-value text-warning" id="kpiApprovals">—</div><small><a href="approval-workflow.php" class="text-warning text-decoration-none small">Lihat →</a></small></div></div>
    </div>

    <!-- Row 1: Loan Trend + Member Growth -->
    <div class="row g-4 mb-4">
        <div class="col-md-7">
            <div class="chart-card">
                <div class="chart-title"><i class="fas fa-hand-holding-usd text-success me-2"></i>Tren Pinjaman (Total & Outstanding)</div>
                <canvas id="chartLoan"></canvas>
            </div>
        </div>
        <div class="col-md-5">
            <div class="chart-card">
                <div class="chart-title"><i class="fas fa-users text-primary me-2"></i>Pertumbuhan Anggota</div>
                <canvas id="chartMembers"></canvas>
            </div>
        </div>
    </div>

    <!-- Row 2: Savings Trend + Loan by Status (donut) -->
    <div class="row g-4 mb-4">
        <div class="col-md-7">
            <div class="chart-card">
                <div class="chart-title"><i class="fas fa-piggy-bank text-info me-2"></i>Tren Simpanan (Setoran vs Penarikan)</div>
                <canvas id="chartSavings"></canvas>
            </div>
        </div>
        <div class="col-md-5">
            <div class="chart-card">
                <div class="chart-title"><i class="fas fa-circle-half-stroke text-warning me-2"></i>Status Pinjaman</div>
                <canvas id="chartLoanStatus"></canvas>
            </div>
        </div>
    </div>

    <!-- Row 3: NPL Trend + Top Borrowers -->
    <div class="row g-4">
        <div class="col-md-5">
            <div class="chart-card">
                <div class="chart-title"><i class="fas fa-exclamation-triangle text-danger me-2"></i>NPL Ratio (%)</div>
                <canvas id="chartNpl"></canvas>
            </div>
        </div>
        <div class="col-md-7">
            <div class="chart-card">
                <div class="chart-title"><i class="fas fa-trophy text-warning me-2"></i>Top 10 Peminjam (Outstanding)</div>
                <div class="table-responsive" style="max-height:280px;overflow-y:auto">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light sticky-top"><tr><th>#</th><th>Anggota</th><th class="text-end">Total Pinjam</th><th class="text-end">Outstanding</th></tr></thead>
                        <tbody id="topBorrowersBody"><tr><td colspan="4" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i></td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
</div></div>
<div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index:9999"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="../../assets/js/config.js"></script>
<script>
// ─── Auth Guard ───────────────────────────────────────────────────────────────
(function () {
    const u = JSON.parse(localStorage.getItem('userData') || '{}');
    if (!u.token) { window.location.href = '../../login.html'; return; }
    const r = (u.role || '').toLowerCase().replace(' ', '_');
    if (!['admin', 'super_admin', 'manager'].includes(r)) { window.location.href = '../../login.html'; return; }
})();

const BASE = (window.APP_CONFIG?.baseUrl || '') + '/api';
function getToken() { return JSON.parse(localStorage.getItem('userData') || '{}').token || ''; }
function authH()    { return { 'Authorization': 'Bearer ' + getToken() }; }
function logout()   { if (confirm('Keluar?')) { localStorage.removeItem('userData'); window.location.href = '../../login.html'; } }
function apiGet(ep, params) { return $.ajax({ url: BASE + ep, method: 'GET', data: params, headers: authH() }); }

function formatRpShort(v) {
    v = parseFloat(v || 0);
    if (v >= 1e9) return 'Rp ' + (v / 1e9).toFixed(1) + 'M';
    if (v >= 1e6) return 'Rp ' + (v / 1e6).toFixed(1) + 'Jt';
    if (v >= 1e3) return 'Rp ' + (v / 1e3).toFixed(0) + 'Rb';
    return 'Rp ' + v.toLocaleString('id-ID');
}
function formatRp(v) { return 'Rp ' + parseFloat(v || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 }); }

const charts = {};
function destroyChart(id) { if (charts[id]) { charts[id].destroy(); delete charts[id]; } }

const COLORS = ['#2563eb','#16a34a','#0891b2','#d97706','#dc2626','#7c3aed','#db2777','#ea580c','#65a30d','#0d9488'];
const C = { blue:'#2563eb', green:'#16a34a', teal:'#0891b2', amber:'#d97706', red:'#dc2626', purple:'#7c3aed' };

// ─── KPI Summary ─────────────────────────────────────────────────────────────
function loadSummary() {
    apiGet('/analytics.php', { action: 'summary' }).done(function (d) {
        if (!d.success) return;
        const s = d.data;
        $('#kpiMembers').text(s.total_members);
        $('#kpiMembersNew').text('+' + s.new_members_month + ' bulan ini');
        $('#kpiLoans').text(s.active_loans);
        $('#kpiLoansNew').text('+' + s.new_loans_month + ' bulan ini');
        $('#kpiOutstanding').text(formatRpShort(s.total_outstanding));
        $('#kpiSavings').text(formatRpShort(s.total_savings));
        $('#kpiSavingsAcc').text(s.total_savings_accounts + ' rekening');
        $('#kpiNpl').text(s.npl_count + ' loan');
        $('#kpiApprovals').text(s.pending_approvals);
    });
}

// ─── Loan Trend ───────────────────────────────────────────────────────────────
function loadLoanTrend() {
    const months = $('#periodSelect').val();
    apiGet('/analytics.php', { action: 'loan_trend', months }).done(function (d) {
        if (!d.success) return;
        destroyChart('loan');
        const rows = d.data;
        charts['loan'] = new Chart(document.getElementById('chartLoan'), {
            data: {
                labels: rows.map(r => r.month),
                datasets: [
                    { type: 'bar', label: 'Jumlah Pinjaman', data: rows.map(r => r.total_loans), backgroundColor: C.blue + '33', borderColor: C.blue, borderWidth: 2, yAxisID: 'y1' },
                    { type: 'line', label: 'Total Nominal (Jt)', data: rows.map(r => (r.total_amount / 1e6).toFixed(2)), borderColor: C.green, backgroundColor: C.green + '20', tension: .4, fill: true, yAxisID: 'y2' },
                ],
            },
            options: {
                responsive: true, interaction: { mode: 'index' },
                plugins: { legend: { position: 'top' } },
                scales: {
                    y1: { type: 'linear', position: 'left', title: { display: true, text: 'Jumlah' }, ticks: { stepSize: 1 } },
                    y2: { type: 'linear', position: 'right', title: { display: true, text: 'Juta Rp' }, grid: { drawOnChartArea: false } },
                },
            },
        });
    });
}

// ─── Member Growth ────────────────────────────────────────────────────────────
function loadMemberGrowth() {
    const months = $('#periodSelect').val();
    apiGet('/analytics.php', { action: 'member_growth', months }).done(function (d) {
        if (!d.success) return;
        destroyChart('members');
        const rows = d.data;
        charts['members'] = new Chart(document.getElementById('chartMembers'), {
            type: 'line',
            data: {
                labels: rows.map(r => r.month),
                datasets: [
                    { label: 'Anggota Baru', data: rows.map(r => r.new_members), borderColor: C.blue, backgroundColor: C.blue + '20', tension: .4, fill: true },
                    { label: 'Kumulatif', data: rows.map(r => r.cumulative), borderColor: C.green, backgroundColor: 'transparent', tension: .4, borderDash: [5, 5] },
                ],
            },
            options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: false } } },
        });
    });
}

// ─── Savings Trend ────────────────────────────────────────────────────────────
function loadSavingsTrend() {
    const months = $('#periodSelect').val();
    apiGet('/analytics.php', { action: 'savings_trend', months }).done(function (d) {
        if (!d.success) return;
        destroyChart('savings');
        const rows = d.data;
        charts['savings'] = new Chart(document.getElementById('chartSavings'), {
            type: 'bar',
            data: {
                labels: rows.map(r => r.month),
                datasets: [
                    { label: 'Setoran (Jt)', data: rows.map(r => (r.deposits / 1e6).toFixed(2)), backgroundColor: C.teal + 'bb' },
                    { label: 'Penarikan (Jt)', data: rows.map(r => (r.withdrawals / 1e6).toFixed(2)), backgroundColor: C.amber + 'bb' },
                ],
            },
            options: {
                responsive: true, plugins: { legend: { position: 'top' } },
                scales: { x: { stacked: false }, y: { title: { display: true, text: 'Juta Rp' } } },
            },
        });
    });
}

// ─── Loan by Status (Donut) ───────────────────────────────────────────────────
function loadLoanStatus() {
    apiGet('/analytics.php', { action: 'loan_by_status' }).done(function (d) {
        if (!d.success) return;
        destroyChart('loanStatus');
        const rows = d.data;
        charts['loanStatus'] = new Chart(document.getElementById('chartLoanStatus'), {
            type: 'doughnut',
            data: {
                labels: rows.map(r => r.status),
                datasets: [{ data: rows.map(r => r.total_loans), backgroundColor: COLORS, borderWidth: 2 }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'right', labels: { font: { size: 11 } } },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw} pinjaman` } },
                },
            },
        });
    });
}

// ─── NPL Trend ────────────────────────────────────────────────────────────────
function loadNplTrend() {
    const months = $('#periodSelect').val();
    apiGet('/analytics.php', { action: 'npl_trend', months }).done(function (d) {
        if (!d.success) return;
        destroyChart('npl');
        const rows = d.data;
        charts['npl'] = new Chart(document.getElementById('chartNpl'), {
            type: 'line',
            data: {
                labels: rows.map(r => r.month),
                datasets: [{
                    label: 'NPL Ratio (%)',
                    data: rows.map(r => parseFloat(r.npl_ratio) || 0),
                    borderColor: C.red, backgroundColor: C.red + '20', tension: .4, fill: true,
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, title: { display: true, text: '%' }, suggestedMax: 10 } },
            },
        });
    });
}

// ─── Top Borrowers ────────────────────────────────────────────────────────────
function loadTopBorrowers() {
    apiGet('/analytics.php', { action: 'top_borrowers', limit: 10 }).done(function (d) {
        if (!d.success || !d.data.length) {
            $('#topBorrowersBody').html('<tr><td colspan="4" class="text-center text-muted py-3">Belum ada data pinjaman</td></tr>');
            return;
        }
        $('#topBorrowersBody').html(d.data.map((r, i) => `
            <tr>
                <td><span class="badge ${i < 3 ? 'bg-warning text-dark' : 'bg-secondary'}">${i + 1}</span></td>
                <td><code class="small">${r.member_number}</code> ${r.full_name}</td>
                <td class="text-end">${formatRpShort(r.total_borrowed)}</td>
                <td class="text-end fw-bold text-danger">${formatRpShort(r.outstanding)}</td>
            </tr>`).join(''));
    });
}

// ─── Init & Refresh ───────────────────────────────────────────────────────────
function refreshAll() {
    loadSummary();
    loadLoanTrend();
    loadMemberGrowth();
    loadSavingsTrend();
    loadLoanStatus();
    loadNplTrend();
    loadTopBorrowers();
}

$(document).ready(function () {
    refreshAll();
    $('#periodSelect').on('change', function () {
        loadLoanTrend();
        loadMemberGrowth();
        loadSavingsTrend();
        loadNplTrend();
    });
});
</script>
</body>
</html>
