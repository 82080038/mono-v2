<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        
            #mainContent { margin-left: 0; }
        }
    </style>
</head>
<body>

<!-- ── Sidebar ─────────────────────────────────────────────────────────────── -->
<?php $activePage = 'dashboard'; require __DIR__ . '/partials/sidebar.php'; ?>

<!-- ── Main Content ────────────────────────────────────────────────────────── -->
<div id="mainContent">

    <!-- Topbar -->
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none me-2" onclick="$('#sidebar').toggleClass('show')">
            <i class="fas fa-bars"></i>
        </button>
        <div>
            <div class="topbar-title">Dashboard</div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active">Overview</li>
            </ol></nav>
        </div>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="text-muted small" id="lastUpdated"></span>
            <button class="btn btn-sm btn-outline-primary" onclick="refreshAll()">
                <i class="fas fa-sync-alt me-1"></i>Refresh
            </button>
        </div>
    </div>

    <div class="page-body">

        <!-- ── Stat Cards ── -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div><div class="text-muted small">Total Anggota</div><div class="fs-3 fw-bold text-primary" id="statMembers">—</div></div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-users"></i></div>
                    </div>
                    <small class="text-success"><i class="fas fa-circle-check me-1"></i><span id="statMembersActive">—</span> aktif</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div><div class="text-muted small">Pinjaman Aktif</div><div class="fs-3 fw-bold text-success" id="statLoans">—</div></div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-hand-holding-usd"></i></div>
                    </div>
                    <small class="text-muted">Outstanding: <span id="statLoansOutstanding">—</span></small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div><div class="text-muted small">Total Simpanan</div><div class="fs-3 fw-bold text-info" id="statSavings">—</div></div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="fas fa-piggy-bank"></i></div>
                    </div>
                    <small class="text-muted"><span id="statSavingsCount">—</span> rekening</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div><div class="text-muted small">Approval Pending</div><div class="fs-3 fw-bold text-warning" id="statApprovals">—</div></div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-tasks"></i></div>
                    </div>
                    <small><a href="approval-workflow.php" class="text-warning text-decoration-none">Lihat semua →</a></small>
                </div>
            </div>
        </div>

        <!-- ── Quick Actions ── -->
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-semibold"><i class="fas fa-bolt me-2 text-warning"></i>Akses Cepat — Phase 2</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-2">
                        <a href="accounting.php" class="qaction bg-primary bg-opacity-10 text-primary border-primary border-opacity-25">
                            <i class="fas fa-book"></i><span>Jurnal Umum</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-2">
                        <a href="accounting.php#tab-reports" class="qaction bg-success bg-opacity-10 text-success border-success border-opacity-25">
                            <i class="fas fa-file-chart-line"></i><span>Laporan Keuangan</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-2">
                        <a href="laporan-shu.php" class="qaction bg-warning bg-opacity-10 text-warning border-warning border-opacity-25">
                            <i class="fas fa-file-invoice-dollar"></i><span>SHU &amp; Distribusi</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-2">
                        <a href="approval-workflow.php" class="qaction bg-info bg-opacity-10 text-info border-info border-opacity-25">
                            <i class="fas fa-tasks"></i><span>Approval</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-2">
                        <a href="audit-log.php" class="qaction bg-dark bg-opacity-10 text-dark border-dark border-opacity-25">
                            <i class="fas fa-shield-alt"></i><span>Audit Trail</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-2">
                        <a href="reports.php" class="qaction bg-secondary bg-opacity-10 text-secondary border-secondary border-opacity-25">
                            <i class="fas fa-chart-bar"></i><span>Laporan Umum</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- ── Approval Pending ── -->
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-tasks me-2 text-warning"></i>Approval Menunggu</span>
                        <a href="approval-workflow.php?status=pending" class="btn btn-xs btn-sm btn-outline-warning py-0">Lihat Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div id="pendingApprovalsList" class="list-group list-group-flush">
                            <div class="list-group-item text-center text-muted py-3 small">
                                <i class="fas fa-spinner fa-spin me-2"></i>Memuat...
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Audit Recent ── -->
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-shield-alt me-2 text-secondary"></i>Aktivitas Terkini</span>
                        <a href="audit-log.php" class="btn btn-xs btn-sm btn-outline-secondary py-0">Audit Trail</a>
                    </div>
                    <div class="card-body p-0">
                        <div id="recentAuditList" class="list-group list-group-flush">
                            <div class="list-group-item text-center text-muted py-3 small">
                                <i class="fas fa-spinner fa-spin me-2"></i>Memuat...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /page-body -->
</div><!-- /mainContent -->

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/config.js"></script>
<script src="../../assets/js/auth-fixed.js"></script>
<script>
// ─── Auth ─────────────────────────────────────────────────────────────────────
(function () {
    const u = JSON.parse(localStorage.getItem('userData') || '{}');
    if (!u.token) { window.location.href = '../../login.html'; return; }
    const r = (u.role || '').toLowerCase().replace(' ', '_');
    if (!['admin', 'super_admin', 'manager'].includes(r)) { window.location.href = '../../login.html'; return; }
    $('#sidebarUserName').text(u.name || 'Admin');
    $('#sidebarUserRole').text(u.role || '');
})();

const BASE = (window.APP_CONFIG?.baseUrl || '') + '/api';
function getToken() { return JSON.parse(localStorage.getItem('userData') || '{}').token || ''; }
function authH()    { return { 'Authorization': 'Bearer ' + getToken() }; }
function logout()   { if (confirm('Keluar dari sistem?')) { localStorage.removeItem('userData'); window.location.href = '../../login.html'; } }

function apiGet(ep, params) { return $.ajax({ url: BASE + ep, method: 'GET', data: params, headers: authH() }); }

function formatRp(v) { return 'Rp ' + parseFloat(v || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 }); }
function formatRpShort(v) {
    v = parseFloat(v || 0);
    if (v >= 1e9) return 'Rp ' + (v / 1e9).toFixed(1) + 'M';
    if (v >= 1e6) return 'Rp ' + (v / 1e6).toFixed(1) + 'Jt';
    return formatRp(v);
}
function formatDt(d) { return d ? new Date(d).toLocaleString('id-ID', { day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit' }) : '—'; }

// ─── Stats ────────────────────────────────────────────────────────────────────
function loadMemberStats() {
    apiGet('/members.php', { action: 'stats' })
        .done(function (d) {
            if (!d.success) return;
            $('#statMembers').text(d.data.total || d.data.count || '—');
            $('#statMembersActive').text(d.data.active || '—');
        })
        .fail(function () {
            // Fallback: just count from list
            apiGet('/members.php', { action: 'list', limit: 1 }).done(function (d) {
                if (d.success) $('#statMembers').text(d.data?.total || '—');
            });
        });
}

function loadLoanStats() {
    apiGet('/loans.php', { action: 'list', status: 'active', limit: 1 })
        .done(function (d) {
            if (!d.success) return;
            $('#statLoans').text(d.data?.total || d.data?.count || '—');
        }).fail(() => {});

    apiGet('/analytics.php', { action: 'summary' })
        .done(function (d) {
            if (!d.success) return;
            const s = d.data || {};
            if (s.total_outstanding) $('#statLoansOutstanding').text(formatRpShort(s.total_outstanding));
            if (s.total_savings) $('#statSavings').text(formatRpShort(s.total_savings));
            if (s.total_savings_accounts) $('#statSavingsCount').text(s.total_savings_accounts);
            if (s.total_loans) $('#statLoans').text(s.active_loans || s.total_loans);
        }).fail(() => {});
}

function loadApprovalStats() {
    apiGet('/approvals.php', { action: 'stats' })
        .done(function (d) {
            if (!d.success) return;
            const pending = d.data.pending || 0;
            $('#statApprovals').text(pending);
            if (pending > 0) {
                $('#sidebarPendingBadge').text(pending).show();
            } else {
                $('#sidebarPendingBadge').hide();
            }
        }).fail(() => { $('#statApprovals').text('—'); });
}

function loadPendingApprovals() {
    apiGet('/approvals.php', { action: 'list', status: 'pending', page: 1 })
        .done(function (d) {
            const container = $('#pendingApprovalsList');
            if (!d.success || !d.data.approvals.length) {
                container.html('<div class="list-group-item text-center text-muted py-3 small"><i class="fas fa-check-circle text-success me-2"></i>Tidak ada persetujuan yang menunggu</div>');
                return;
            }
            const typeIcon = { loan: 'hand-holding-usd', member: 'user', journal: 'journal-whills', savings_withdrawal: 'money-bill-wave' };
            const typeLabel = { loan: 'Pinjaman', member: 'Anggota', journal: 'Jurnal', savings_withdrawal: 'Penarikan' };
            container.html(d.data.approvals.slice(0, 6).map(a => `
                <a href="approval-workflow.php" class="list-group-item list-group-item-action py-2 px-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px">
                            <i class="fas fa-${typeIcon[a.entity_type] || 'file'}" style="font-size:.75rem"></i>
                        </div>
                        <div class="overflow-hidden flex-grow-1">
                            <div class="small fw-semibold text-truncate">${typeLabel[a.entity_type] || a.entity_type} #${a.entity_id}</div>
                            <div class="text-muted" style="font-size:.72rem">Level ${a.level} · Role: ${a.required_role} · ${formatDt(a.created_at)}</div>
                        </div>
                        <span class="badge bg-warning text-dark" style="font-size:.65rem">Pending</span>
                    </div>
                </a>`).join(''));
        }).fail(function () {
            $('#pendingApprovalsList').html('<div class="list-group-item text-muted small text-center">Gagal memuat</div>');
        });
}

function loadRecentAudit() {
    apiGet('/audit.php', { action: 'get_audit_logs', date_from: new Date(Date.now()-7*864e5).toISOString().slice(0,10), date_to: new Date().toISOString().slice(0,10), page: 1 })
        .done(function (d) {
            const container = $('#recentAuditList');
            if (!d.success || !d.data.logs.length) {
                container.html('<div class="list-group-item text-center text-muted py-3 small">Belum ada aktivitas</div>');
                return;
            }
            const actionColor = { CREATE:'success', UPDATE:'primary', DELETE:'danger', APPROVE:'success', REJECT:'warning', LOGIN:'secondary', LOGOUT:'secondary', EXPORT:'info' };
            container.html(d.data.logs.slice(0, 6).map(l => `
                <div class="list-group-item py-2 px-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-${actionColor[l.action] || 'secondary'}" style="font-size:.65rem;width:58px;text-align:center">${l.action}</span>
                        <div class="overflow-hidden flex-grow-1">
                            <div class="small text-truncate"><code class="small">${l.table_name}</code> ${l.description ? '· ' + l.description.substring(0,40) : ''}</div>
                            <div class="text-muted" style="font-size:.72rem">${l.user_name || '—'} · ${formatDt(l.created_at)}</div>
                        </div>
                    </div>
                </div>`).join(''));
        }).fail(function () {
            $('#recentAuditList').html('<div class="list-group-item text-muted small text-center">Gagal memuat</div>');
        });
}

function refreshAll() {
    $('#lastUpdated').text('Memuat...');
    loadMemberStats();
    loadLoanStats();
    loadApprovalStats();
    loadPendingApprovals();
    loadRecentAudit();
    setTimeout(() => $('#lastUpdated').text('Update: ' + new Date().toLocaleTimeString('id-ID')), 1500);
}

$(document).ready(function () {
    refreshAll();
    setInterval(function () { loadApprovalStats(); loadPendingApprovals(); }, 60000);
});
</script>
</body>
</html>
