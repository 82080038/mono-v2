<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Workflow - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .page-header { background: linear-gradient(135deg,#155799,#159957); color:#fff; padding:1.5rem; border-radius:12px; margin-bottom:1.5rem; }
        .stat-card { border-left:5px solid; border-radius:10px; padding:1rem 1.25rem; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.08); }
        .stat-card.pending  { border-color:#ffc107; }
        .stat-card.approved { border-color:#198754; }
        .stat-card.rejected { border-color:#dc3545; }
        .entity-badge { font-size:.78rem; padding:.25em .6em; border-radius:20px; }
        .timeline { border-left:3px solid #dee2e6; padding-left:1.25rem; margin-left:.5rem; }
        .timeline-item { position:relative; margin-bottom:1rem; }
        .timeline-item::before { content:''; position:absolute; left:-1.55rem; top:.3rem; width:12px; height:12px; border-radius:50%; background:#6c757d; border:2px solid #fff; box-shadow:0 0 0 2px #6c757d; }
        .timeline-item.approved::before { background:#198754; box-shadow:0 0 0 2px #198754; }
        .timeline-item.rejected::before { background:#dc3545; box-shadow:0 0 0 2px #dc3545; }
        .timeline-item.pending::before  { background:#ffc107; box-shadow:0 0 0 2px #ffc107; }
        #mainContent{margin-left:0}
    </style>
</head>
<body>
<?php $activePage = 'approval-workflow'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">Approval Workflow</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Approval Workflow</li></ol></nav></div>
    </div>
<div class="page-body">

    <!-- Stat Cards -->
    <div class="row g-3 mb-4" id="statsRow">
        <div class="col-md-4">
            <div class="stat-card pending d-flex align-items-center gap-3">
                <i class="fas fa-clock fa-2x text-warning"></i>
                <div><div class="text-muted small">Menunggu</div><div class="fs-3 fw-bold text-warning" id="statPending">—</div></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card approved d-flex align-items-center gap-3">
                <i class="fas fa-check-circle fa-2x text-success"></i>
                <div><div class="text-muted small">Disetujui</div><div class="fs-3 fw-bold text-success" id="statApproved">—</div></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card rejected d-flex align-items-center gap-3">
                <i class="fas fa-times-circle fa-2x text-danger"></i>
                <div><div class="text-muted small">Ditolak</div><div class="fs-3 fw-bold text-danger" id="statRejected">—</div></div>
            </div>
        </div>
    </div>

    <!-- Filter + Table -->
    <div class="card shadow-sm">
        <div class="card-header">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Status</label>
                    <select id="fStatus" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu</option>
                        <option value="approved">Disetujui</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Tipe Entitas</label>
                    <select id="fEntityType" class="form-select form-select-sm">
                        <option value="">Semua Tipe</option>
                        <option value="loan">Pinjaman</option>
                        <option value="member">Anggota</option>
                        <option value="journal">Jurnal</option>
                        <option value="savings_withdrawal">Penarikan</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-sm btn-primary" onclick="loadApprovals()"><i class="fas fa-search me-1"></i>Filter</button>
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fas fa-plus me-1"></i>Baru</button>
                </div>
                <div class="col-md-3 text-end">
                    <span class="badge bg-secondary" id="totalBadge">0 record</span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tipe</th>
                            <th>ID Entitas</th>
                            <th>Level</th>
                            <th>Role Diperlukan</th>
                            <th>Status</th>
                            <th>Diajukan</th>
                            <th>Diproses Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="approvalBody">
                        <tr><td colspan="9" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="paginationBar" class="d-flex justify-content-between align-items-center px-3 py-2 border-top"></div>
        </div>
    </div>
</div>

<!-- Modal: Detail & Action -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Detail Persetujuan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailBody">
                <div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
            </div>
            <div class="modal-footer" id="detailFooter">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Approve/Reject Note -->
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="actionModalHeader">
                <h5 class="modal-title" id="actionModalTitle">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="actionModalDesc" class="text-muted"></p>
                <label class="form-label">Catatan <span class="text-muted">(opsional untuk setuju, wajib untuk tolak)</span></label>
                <textarea id="actionNote" class="form-control" rows="3" placeholder="Masukkan catatan..."></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn" id="actionConfirmBtn" onclick="submitAction()">Konfirmasi</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Buat Approval Baru -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Buat Permohonan Persetujuan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Tipe Entitas <span class="text-danger">*</span></label>
                        <select id="cEntityType" class="form-select">
                            <option value="loan">Pinjaman</option>
                            <option value="member">Anggota</option>
                            <option value="journal">Jurnal</option>
                            <option value="savings_withdrawal">Penarikan Simpanan</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">ID Entitas <span class="text-danger">*</span></label>
                        <input type="number" id="cEntityId" class="form-control" min="1" placeholder="mis: 12">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Level</label>
                        <select id="cLevel" class="form-select">
                            <option value="1">Level 1 (Teller)</option>
                            <option value="2">Level 2 (Manager)</option>
                            <option value="3">Level 3 (Admin)</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Role yang Diperlukan <span class="text-danger">*</span></label>
                        <select id="cRequiredRole" class="form-select">
                            <option value="teller">Teller</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                            <option value="kasir">Kasir</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-success" onclick="createApproval()"><i class="fas fa-save me-1"></i>Buat</button>
            </div>
        </div>
    </div>
</div>
</div></div>
<div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index:9999"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/config.js"></script>
<script>
// ─── Auth Guard ───────────────────────────────────────────────────────────────
(function () {
    const u = JSON.parse(localStorage.getItem('userData') || '{}');
    if (!u.token) { window.location.href = '../../login.html'; return; }
    const r = (u.role || '').toLowerCase().replace(' ', '_');
    if (!['admin', 'super_admin', 'manager', 'teller', 'kasir'].includes(r)) {
        window.location.href = '../../login.html'; return;
    }
    $('#currentUserName').text(u.name || u.role || '');
})();

const BASE = (window.APP_CONFIG?.baseUrl || '') + '/api';
let pendingAction = null; // { id, type: 'approved'|'rejected' }

function getToken() { return JSON.parse(localStorage.getItem('userData') || '{}').token || ''; }
function authH()    { return { 'Authorization': 'Bearer ' + getToken() }; }
function logout()   { if (confirm('Keluar?')) { localStorage.removeItem('userData'); window.location.href = '../../login.html'; } }

function apiGet(ep, params) { return $.ajax({ url: BASE + ep, method: 'GET', data: params, headers: authH() }); }
function apiPost(ep, data)  { return $.ajax({ url: BASE + ep, method: 'POST', contentType: 'application/json', data: JSON.stringify(data), headers: authH() }); }

function showToast(msg, type = 'success') {
    const id = 't' + Date.now();
    $('#toastContainer').append(`<div id="${id}" class="toast align-items-center text-bg-${type} border-0 show">
        <div class="d-flex"><div class="toast-body">${msg}</div>
        <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`);
    setTimeout(() => $('#' + id).remove(), 4000);
}

// ─── Status formatting ────────────────────────────────────────────────────────
const statusBadge = { pending: 'bg-warning text-dark', approved: 'bg-success', rejected: 'bg-danger' };
const statusLabel = { pending: 'Menunggu', approved: 'Disetujui', rejected: 'Ditolak' };
const entityLabel = { loan: 'Pinjaman', member: 'Anggota', journal: 'Jurnal', savings_withdrawal: 'Penarikan' };
const entityIcon  = { loan: 'hand-holding-usd', member: 'user', journal: 'journal-whills', savings_withdrawal: 'money-bill-wave' };

function formatDt(d) { return d ? new Date(d).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'; }

// ─── Stats ────────────────────────────────────────────────────────────────────
function loadStats() {
    apiGet('/approvals.php', { action: 'stats' }).done(function (d) {
        if (!d.success) return;
        $('#statPending').text(d.data.pending);
        $('#statApproved').text(d.data.approved);
        $('#statRejected').text(d.data.rejected);
    });
}

// ─── List ─────────────────────────────────────────────────────────────────────
function loadApprovals(page = 1) {
    const params = { action: 'list', status: $('#fStatus').val(), entity_type: $('#fEntityType').val(), page };
    apiGet('/approvals.php', params)
        .done(function (d) {
            if (!d.success) { showToast(d.message, 'danger'); return; }
            const { approvals, total, pages } = d.data;
            $('#totalBadge').text(total + ' record');

            if (!approvals.length) {
                $('#approvalBody').html('<tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data persetujuan</td></tr>');
                $('#paginationBar').html('');
                return;
            }

            $('#approvalBody').html(approvals.map(a => `
                <tr>
                    <td>${a.id}</td>
                    <td><span class="entity-badge badge bg-primary"><i class="fas fa-${entityIcon[a.entity_type] || 'file'} me-1"></i>${entityLabel[a.entity_type] || a.entity_type}</span></td>
                    <td><code>#${a.entity_id}</code></td>
                    <td><span class="badge bg-secondary">L${a.level}</span></td>
                    <td><span class="badge bg-info text-dark text-capitalize">${a.required_role}</span></td>
                    <td><span class="badge ${statusBadge[a.status] || 'bg-secondary'}">${statusLabel[a.status] || a.status}</span></td>
                    <td><small>${formatDt(a.created_at)}</small><br><small class="text-muted">${a.created_by_name || '—'}</small></td>
                    <td><small>${a.actioned_by_name || '—'}</small></td>
                    <td class="text-nowrap">
                        <button class="btn btn-sm btn-outline-primary py-0" onclick="viewDetail(${a.id})"><i class="fas fa-eye"></i></button>
                        ${a.status === 'pending' ? `
                        <button class="btn btn-sm btn-success py-0 ms-1" onclick="openAction(${a.id},'approved')"><i class="fas fa-check"></i></button>
                        <button class="btn btn-sm btn-danger py-0 ms-1" onclick="openAction(${a.id},'rejected')"><i class="fas fa-times"></i></button>` : ''}
                    </td>
                </tr>`).join(''));

            renderPagination(page, pages, total);
        })
        .fail(function () { showToast('Gagal memuat data', 'danger'); });
}

function renderPagination(page, pages, total) {
    $('#paginationBar').html(`
        <small class="text-muted">Halaman ${page} dari ${pages} (${total} record)</small>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary" onclick="loadApprovals(${page - 1})" ${page <= 1 ? 'disabled' : ''}>‹ Prev</button>
            <button class="btn btn-outline-secondary" onclick="loadApprovals(${page + 1})" ${page >= pages ? 'disabled' : ''}>Next ›</button>
        </div>`);
}

// ─── Detail Modal ─────────────────────────────────────────────────────────────
function viewDetail(id) {
    $('#detailBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>');
    $('#detailFooter').html('<button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>');
    new bootstrap.Modal(document.getElementById('detailModal')).show();

    apiGet('/approvals.php', { action: 'get', id })
        .done(function (d) {
            if (!d.success) { $('#detailBody').html('<div class="alert alert-danger">' + d.message + '</div>'); return; }
            const a = d.data;
            const es = a.entity_summary || {};

            // Build entity summary section
            let esSummary = Object.entries(es).map(([k, v]) =>
                `<tr><td class="text-muted text-capitalize small">${k.replace(/_/g, ' ')}</td><td>${v}</td></tr>`).join('');

            // Build timeline
            let timeline = `
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="fw-semibold small">Diajukan</div>
                        <div class="text-muted small">${formatDt(a.created_at)} oleh ${a.created_by_name || '—'}</div>
                    </div>
                    <div class="timeline-item ${a.status}">
                        <div class="fw-semibold small">${statusLabel[a.status] || a.status}</div>
                        <div class="text-muted small">${a.actioned_at ? formatDt(a.actioned_at) + ' oleh ' + (a.actioned_by_name || '—') : 'Belum diproses'}</div>
                        ${a.note ? '<div class="badge bg-light text-dark border mt-1 text-wrap">' + a.note + '</div>' : ''}
                    </div>
                </div>`;

            $('#detailBody').html(`
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-secondary text-uppercase small">Info Persetujuan</h6>
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted small">ID</td><td>${a.id}</td></tr>
                            <tr><td class="text-muted small">Tipe</td><td>${entityLabel[a.entity_type] || a.entity_type}</td></tr>
                            <tr><td class="text-muted small">ID Entitas</td><td><code>#${a.entity_id}</code></td></tr>
                            <tr><td class="text-muted small">Level</td><td><span class="badge bg-secondary">Level ${a.level}</span></td></tr>
                            <tr><td class="text-muted small">Role</td><td><span class="badge bg-info text-dark text-capitalize">${a.required_role}</span></td></tr>
                            <tr><td class="text-muted small">Status</td><td><span class="badge ${statusBadge[a.status]}">${statusLabel[a.status]}</span></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-secondary text-uppercase small">Data Entitas</h6>
                        ${esSummary ? `<table class="table table-sm table-borderless">${esSummary}</table>` : '<p class="text-muted small">Tidak ada data</p>'}
                    </div>
                    <div class="col-12">
                        <h6 class="fw-bold text-secondary text-uppercase small">Timeline</h6>
                        ${timeline}
                    </div>
                </div>`);

            if (a.status === 'pending') {
                $('#detailFooter').html(`
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button class="btn btn-danger" onclick="openAction(${a.id},'rejected');$('#detailModal').modal('hide')"><i class="fas fa-times me-1"></i>Tolak</button>
                    <button class="btn btn-success" onclick="openAction(${a.id},'approved');$('#detailModal').modal('hide')"><i class="fas fa-check me-1"></i>Setujui</button>`);
            }
        })
        .fail(function () { $('#detailBody').html('<div class="alert alert-danger">Gagal memuat detail</div>'); });
}

// ─── Approve / Reject ─────────────────────────────────────────────────────────
function openAction(id, type) {
    pendingAction = { id, type };
    const isApprove = type === 'approved';
    $('#actionModalHeader').attr('class', 'modal-header ' + (isApprove ? 'bg-success text-white' : 'bg-danger text-white'));
    $('#actionModalTitle').text(isApprove ? 'Setujui Permohonan' : 'Tolak Permohonan');
    $('#actionModalDesc').text(isApprove
        ? `Anda akan menyetujui permohonan #${id}. Pastikan data sudah benar.`
        : `Anda akan menolak permohonan #${id}. Masukkan alasan penolakan.`);
    $('#actionNote').val('');
    $('#actionConfirmBtn')
        .text(isApprove ? 'Ya, Setujui' : 'Ya, Tolak')
        .attr('class', 'btn ' + (isApprove ? 'btn-success' : 'btn-danger'));
    new bootstrap.Modal(document.getElementById('actionModal')).show();
}

function submitAction() {
    if (!pendingAction) return;
    const note = $('#actionNote').val().trim();
    if (pendingAction.type === 'rejected' && !note) {
        showToast('Alasan penolakan wajib diisi', 'warning'); return;
    }
    apiPost('/approvals.php', { action: pendingAction.type, id: pendingAction.id, note })
        .done(function (d) {
            showToast(d.message, d.success ? 'success' : 'danger');
            if (d.success) {
                bootstrap.Modal.getInstance(document.getElementById('actionModal')).hide();
                pendingAction = null;
                loadApprovals();
                loadStats();
            }
        })
        .fail(function () { showToast('Gagal memproses persetujuan', 'danger'); });
}

// ─── Create ───────────────────────────────────────────────────────────────────
function createApproval() {
    const entityId = parseInt($('#cEntityId').val());
    if (!entityId || entityId < 1) { showToast('ID Entitas harus diisi dan valid', 'warning'); return; }

    const payload = {
        action:        'create',
        entity_type:   $('#cEntityType').val(),
        entity_id:     entityId,
        level:         parseInt($('#cLevel').val()),
        required_role: $('#cRequiredRole').val(),
    };
    apiPost('/approvals.php', payload)
        .done(function (d) {
            showToast(d.message, d.success ? 'success' : 'danger');
            if (d.success) {
                bootstrap.Modal.getInstance(document.getElementById('createModal')).hide();
                loadApprovals();
                loadStats();
            }
        })
        .fail(function () { showToast('Gagal membuat permohonan', 'danger'); });
}

// ─── Init ─────────────────────────────────────────────────────────────────────
$(document).ready(function () {
    loadStats();
    loadApprovals();

    // Auto-refresh pending count setiap 60 detik
    setInterval(loadStats, 60000);
});
</script>
</body>
</html>
