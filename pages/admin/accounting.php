<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akuntansi - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .page-header { background: linear-gradient(135deg,#1a472a,#2d6a4f); color:#fff; padding:1.5rem; border-radius:12px; margin-bottom:1.5rem; }
        .nav-tabs .nav-link { color:#495057; font-weight:500; }
        .nav-tabs .nav-link.active { color:#1a472a; border-bottom:3px solid #1a472a; font-weight:600; }
        .table th { background:#f8f9fa; font-size:.85rem; white-space:nowrap; }
        .badge-asset     { background:#0d6efd; }
        .badge-liability { background:#dc3545; }
        .badge-equity    { background:#198754; }
        .badge-revenue   { background:#0dcaf0; color:#000; }
        .badge-expense   { background:#ffc107; color:#000; }
        .journal-line-row td { padding:.35rem .5rem; font-size:.875rem; }
        .report-section { border-left:4px solid #1a472a; padding-left:1rem; margin-bottom:1rem; }
        .amount-col { text-align:right; font-family:monospace; }
        .total-row { font-weight:700; background:#e9f7ef; }
        .back-btn { color:#fff; text-decoration:none; }
        .back-btn:hover { color:#d4edda; }
        #mainContent{margin-left:0}
    </style>
</head>
<body>
<?php $activePage = 'accounting'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">Sistem Akuntansi</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Jurnal &amp; Laporan</li></ol></nav></div>
    </div>
<div class="page-body">

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="mainTabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-coa"><i class="fas fa-sitemap me-1"></i>Bagan Akun</a></li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-journal">
                <i class="fas fa-journal-whills me-1"></i>Jurnal Umum
            </a>
        </li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-reports"><i class="fas fa-file-alt me-1"></i>Laporan Keuangan</a></li>
        <li class="nav-item ms-auto">
            <button class="btn btn-sm btn-outline-info" onclick="openAccountingHelp()" data-bs-toggle="tooltip" title="Bantuan Akuntansi">
                <i class="fas fa-question-circle me-1"></i>Bantuan
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- ── TAB 1: BAGAN AKUN ─────────────────────────────────────────── -->
        <div class="tab-pane fade show active" id="tab-coa">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-sitemap me-2 text-success"></i>Bagan Akun (Chart of Accounts)</h6>
                    <button class="btn btn-sm btn-success" onclick="openAddCOAModal()"><i class="fas fa-plus me-1"></i>Tambah Akun</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="coaTable">
                            <thead><tr><th>Kode</th><th>Nama Akun</th><th>Tipe</th><th>Saldo Normal</th><th>Akun Induk</th><th>Aksi</th></tr></thead>
                            <tbody id="coaBody"><tr><td colspan="6" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Memuat...</td></tr></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── TAB 2: JURNAL UMUM ────────────────────────────────────────── -->
        <div class="tab-pane fade" id="tab-journal">
            <div class="row mb-3">
                <div class="col-md-3"><label class="form-label small">Dari Tanggal</label><input type="date" id="jDateFrom" class="form-control form-control-sm" value=""></div>
                <div class="col-md-3"><label class="form-label small">Sampai Tanggal</label><input type="date" id="jDateTo" class="form-control form-control-sm" value=""></div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-sm btn-primary" onclick="loadJournals()"><i class="fas fa-search me-1"></i>Cari</button>
                    <button class="btn btn-sm btn-success" onclick="openJournalModal()"><i class="fas fa-plus me-1"></i>Buat Jurnal</button>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>No Jurnal</th><th>Tanggal</th><th>Keterangan</th><th>Referensi</th><th>Status</th><th>Dibuat</th><th>Aksi</th></tr></thead>
                            <tbody id="journalBody"><tr><td colspan="7" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Memuat...</td></tr></tbody>
                        </table>
                    </div>
                    <div id="journalPagination" class="d-flex justify-content-end p-2"></div>
                </div>
            </div>
        </div>

        <!-- ── TAB 3: LAPORAN KEUANGAN ───────────────────────────────────── -->
        <div class="tab-pane fade" id="tab-reports">
            <div class="row mb-3">
                <div class="col-md-2">
                    <label class="form-label small">Jenis Laporan</label>
                    <select id="reportType" class="form-select form-select-sm" onchange="updateReportForm()">
                        <option value="trial_balance">Neraca Saldo</option>
                        <option value="balance_sheet">Neraca (Balance Sheet)</option>
                        <option value="income_statement">Laba Rugi</option>
                        <option value="cash_flow">Arus Kas</option>
                    </select>
                </div>
                <div class="col-md-2" id="fieldDateFrom"><label class="form-label small">Dari Tanggal</label><input type="date" id="rDateFrom" class="form-control form-control-sm"></div>
                <div class="col-md-2" id="fieldDateTo"><label class="form-label small">Sampai Tanggal</label><input type="date" id="rDateTo" class="form-control form-control-sm"></div>
                <div class="col-md-2" id="fieldAsOf" style="display:none"><label class="form-label small">Per Tanggal</label><input type="date" id="rAsOf" class="form-control form-control-sm"></div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-sm btn-primary" onclick="loadReport()"><i class="fas fa-chart-bar me-1"></i>Tampilkan</button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="exportReport('csv')"><i class="fas fa-file-csv me-1"></i>CSV</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="exportReport('pdf')"><i class="fas fa-file-pdf me-1"></i>PDF</button>
                </div>
            </div>
            <div id="reportContainer" class="card shadow-sm" style="display:none">
                <div class="card-header d-flex justify-content-between">
                    <h6 class="mb-0" id="reportTitle"></h6>
                    <small class="text-muted" id="reportSubtitle"></small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" id="reportTableContainer"></div>
                    <div class="p-3 border-top" id="reportSummary"></div>
                </div>
            </div>
        </div>

    </div><!-- /tab-content -->
</div>

<!-- Modal: Tambah/Edit Akun -->
<div class="modal fade" id="coaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="coaModalTitle">Tambah Akun</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="coaId">
                <div class="row g-3">
                    <div class="col-6"><label class="form-label">Kode Akun <span class="text-danger">*</span></label><input type="text" id="coaCode" class="form-control" placeholder="mis: 1-100"></div>
                    <div class="col-6"><label class="form-label">Tipe <span class="text-danger">*</span></label>
                        <select id="coaType" class="form-select">
                            <option value="asset">Aset</option><option value="liability">Kewajiban</option>
                            <option value="equity">Ekuitas</option><option value="revenue">Pendapatan</option><option value="expense">Beban</option>
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label">Nama Akun <span class="text-danger">*</span></label><input type="text" id="coaName" class="form-control"></div>
                    <div class="col-6"><label class="form-label">Saldo Normal <span class="text-danger">*</span></label>
                        <select id="coaNormal" class="form-select"><option value="debit">Debit</option><option value="credit">Kredit</option></select>
                    </div>
                    <div class="col-6"><label class="form-label">Akun Induk</label>
                        <select id="coaParent" class="form-select"><option value="">— Tidak ada —</option></select>
                    </div>
                    <div class="col-12"><label class="form-label">Catatan</label><input type="text" id="coaNotes" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="saveCOA()"><i class="fas fa-save me-1"></i>Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Buat Jurnal -->
<div class="modal fade" id="journalModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Buat Jurnal Umum</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4"><label class="form-label">Tanggal <span class="text-danger">*</span></label><input type="date" id="jEntryDate" class="form-control"></div>
                    <div class="col-md-8"><label class="form-label">Keterangan <span class="text-danger">*</span></label><input type="text" id="jDescription" class="form-control" placeholder="Keterangan transaksi"></div>
                </div>
                <h6 class="fw-semibold mb-2">Detail Jurnal (Baris Debit/Kredit)</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="journalLinesTable">
                        <thead class="table-light"><tr>
                            <th style="width:35%">Akun</th>
                            <th>Keterangan Baris</th>
                            <th style="width:15%" data-bs-toggle="tooltip" title="Uang/aset masuk atau bertambah">Debit (Rp)</th>
                            <th style="width:15%" data-bs-toggle="tooltip" title="Uang/aset keluar atau berkurang">Kredit (Rp)</th>
                            <th style="width:40px"></th>
                        </tr></thead>
                        <tbody id="journalLinesBody"></tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addJournalLine()"><i class="fas fa-plus me-1"></i>Tambah Baris</button>
                <div class="row mt-3">
                    <div class="col-md-6 ms-auto">
                        <div class="d-flex justify-content-between border-top pt-2">
                            <strong>Total Debit:</strong><strong id="totalDebitDisplay" class="text-primary">Rp 0</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <strong>Total Kredit:</strong><strong id="totalCreditDisplay" class="text-success">Rp 0</strong>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-1" id="balanceCheck">
                            <strong>Status:</strong><span id="balanceStatus" class="text-muted">—</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="saveJournal()"><i class="fas fa-save me-1"></i>Simpan Jurnal</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Detail Jurnal -->
<div class="modal fade" id="journalDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="journalDetailTitle">Detail Jurnal</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="journalDetailBody"></div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button class="btn btn-warning" id="reverseJournalBtn" onclick="reverseJournal()"><i class="fas fa-undo me-1"></i>Balik Jurnal</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Bantuan Akuntansi -->
<div class="modal fade" id="accountingHelpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-question-circle me-2"></i>Panduan Debet & Kredit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary"><i class="fas fa-plus-circle me-1"></i>Debet (DR)</h6>
                        <p class="text-muted small">Uang/aset masuk atau bertambah</p>
                        <ul class="list-unstyled small">
                            <li><strong>Aset:</strong> Bertambah (uang masuk, piutang bertambah)</li>
                            <li><strong>Biaya:</strong> Bertambah (biaya dikeluarkan)</li>
                            <li><strong>Kewajiban:</strong> Berkurang (utang dibayar)</li>
                            <li><strong>Pendapatan:</strong> Berkurang (pendapatan dikurangi)</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-success"><i class="fas fa-minus-circle me-1"></i>Kredit (CR)</h6>
                        <p class="text-muted small">Uang/aset keluar atau berkurang</p>
                        <ul class="list-unstyled small">
                            <li><strong>Aset:</strong> Berkurang (uang keluar, piutang berkurang)</li>
                            <li><strong>Biaya:</strong> Berkurang (biaya dikurangi)</li>
                            <li><strong>Kewajiban:</strong> Bertambah (utang baru)</li>
                            <li><strong>Pendapatan:</strong> Bertambah (pendapatan masuk)</li>
                        </ul>
                    </div>
                </div>
                <hr>
                <h6 class="fw-bold"><i class="fas fa-lightbulb me-1 text-warning"></i>Cara Mengingat Cepat</h6>
                <div class="alert alert-info small mb-0">
                    <strong>Aset & Biaya</strong> = Normal Debet (masuk/bertambah)<br>
                    <strong>Kewajiban, Modal, Pendapatan</strong> = Normal Kredit (keluar/bertambah)
                </div>
                <hr>
                <h6 class="fw-bold"><i class="fas fa-examples me-1 text-secondary"></i>Contoh Transaksi</h6>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr><th>Transaksi</th><th>Debet</th><th>Kredit</th></tr>
                    </thead>
                    <tbody class="small">
                        <tr>
                            <td>Setoran Simpanan</td>
                            <td class="text-primary">Kas Teller (uang masuk)</td>
                            <td class="text-success">Simpanan (kewajiban bertambah)</td>
                        </tr>
                        <tr>
                            <td>Penarikan Simpanan</td>
                            <td class="text-primary">Simpanan (kewajiban berkurang)</td>
                            <td class="text-success">Kas Teller (uang keluar)</td>
                        </tr>
                        <tr>
                            <td>Pembayaran Pinjaman</td>
                            <td class="text-primary">Kas Teller (uang masuk)</td>
                            <td class="text-success">Piutang + Jasa (aset berkurang + pendapatan)</td>
                        </tr>
                    </tbody>
                </table>
                <div class="alert alert-warning small mt-2 mb-0">
                    <i class="fas fa-check-circle me-1"></i>
                    <strong>Selalu seimbang:</strong> Total Debit = Total Kredit
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

</div></div>
<div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index:9999"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/config.js"></script>
<script src="../../assets/js/auth-fixed.js"></script>
<script>
// ─── Auth Guard ─────────────────────────────────────────────────────────────
(function () {
    const userData = JSON.parse(localStorage.getItem('userData') || '{}');
    if (!userData.token) { window.location.href = '../../login.html'; return; }
    const r = (userData.role || '').toLowerCase().replace(' ', '_');
    if (!['admin', 'super_admin', 'manager'].includes(r)) {
        window.location.href = '../../login.html'; return;
    }
    $('#currentUserName').text(userData.name || 'Admin');
})();

const BASE = (window.APP_CONFIG?.baseUrl || '') + '/api';
let coaList = [];
let currentJournalId = null;

function getToken() { return JSON.parse(localStorage.getItem('userData') || '{}').token || ''; }
function authHeaders() { return { 'Authorization': 'Bearer ' + getToken() }; }

function logout() {
    if (confirm('Keluar dari sistem?')) {
        localStorage.removeItem('userData');
        window.location.href = '../../login.html';
    }
}

function openAccountingHelp() {
    new bootstrap.Modal(document.getElementById('accountingHelpModal')).show();
}

// Initialize tooltips
$(document).ready(function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// ─── Helpers ─────────────────────────────────────────────────────────────────
function formatRp(v) { return 'Rp ' + parseFloat(v || 0).toLocaleString('id-ID', { minimumFractionDigits: 0 }); }
function formatDate(d) { return d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-'; }

function showToast(msg, type = 'success') {
    const id = 'toast-' + Date.now();
    $('#toastContainer').append(
        `<div id="${id}" class="toast align-items-center text-bg-${type} border-0 show">
            <div class="d-flex"><div class="toast-body">${msg}</div>
            <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>
        </div>`
    );
    setTimeout(() => $('#' + id).remove(), 4000);
}

function apiGet(endpoint, params = {}) {
    return $.ajax({ url: BASE + endpoint, method: 'GET', data: params, headers: authHeaders() });
}
function apiPost(endpoint, payload = {}) {
    return $.ajax({ url: BASE + endpoint, method: 'POST', contentType: 'application/json', data: JSON.stringify(payload), headers: authHeaders() });
}

const typeBadge = { asset: 'badge-asset', liability: 'badge-liability', equity: 'badge-equity', revenue: 'badge-revenue', expense: 'badge-expense' };
const typeLabel = { asset: 'Aset', liability: 'Kewajiban', equity: 'Ekuitas', revenue: 'Pendapatan', expense: 'Beban' };

// ─── CHART OF ACCOUNTS ───────────────────────────────────────────────────────
function loadCOA() {
    apiGet('/accounting.php', { action: 'get_coa' })
        .done(function (data) {
            if (!data.success) { showToast(data.message, 'danger'); return; }
            coaList = data.data;
            renderCOA(coaList);
            populateParentSelect(coaList);
        })
        .fail(function () { showToast('Gagal memuat bagan akun', 'danger'); });
}

function renderCOA(list) {
    if (!list.length) {
        $('#coaBody').html('<tr><td colspan="6" class="text-center text-muted py-4">Belum ada data akun</td></tr>');
        return;
    }
    $('#coaBody').html(list.map(a => `
        <tr>
            <td><code>${a.account_code}</code></td>
            <td>${a.account_name}${a.parent_id ? '' : ' <span class="badge bg-secondary">Header</span>'}</td>
            <td><span class="badge ${typeBadge[a.account_type] || 'bg-secondary'}">${typeLabel[a.account_type] || a.account_type}</span></td>
            <td class="text-capitalize">${a.normal_balance}</td>
            <td>${a.parent_name ? '<small class="text-muted">' + a.parent_name + '</small>' : '—'}</td>
            <td><button class="btn btn-sm btn-outline-primary" onclick='editCOA(${JSON.stringify(a)})'><i class="fas fa-edit"></i></button></td>
        </tr>`).join(''));
}

function openAddCOAModal() {
    $('#coaId').val('');
    $('#coaCode').val('').prop('disabled', false);
    $('#coaName,#coaNotes').val('');
    $('#coaModalTitle').text('Tambah Akun Baru');
    new bootstrap.Modal(document.getElementById('coaModal')).show();
}

function editCOA(a) {
    $('#coaId').val(a.id);
    $('#coaCode').val(a.account_code).prop('disabled', true);
    $('#coaName').val(a.account_name);
    $('#coaType').val(a.account_type);
    $('#coaNormal').val(a.normal_balance);
    $('#coaParent').val(a.parent_id || '');
    $('#coaNotes').val(a.notes || '');
    $('#coaModalTitle').text('Edit Akun: ' + a.account_code);
    new bootstrap.Modal(document.getElementById('coaModal')).show();
}

function populateParentSelect(list) {
    const opts = list.filter(a => !a.parent_id)
        .map(a => `<option value="${a.id}">${a.account_code} — ${a.account_name}</option>`).join('');
    $('#coaParent').html('<option value="">— Tidak ada —</option>' + opts);
}

function saveCOA() {
    const id = $('#coaId').val();
    const payload = {
        action: id ? 'update_coa' : 'create_coa', id,
        account_code: $('#coaCode').val().trim(), account_name: $('#coaName').val().trim(),
        account_type: $('#coaType').val(), normal_balance: $('#coaNormal').val(),
        parent_id: $('#coaParent').val() || null, notes: $('#coaNotes').val().trim(),
    };
    apiPost('/accounting.php', payload)
        .done(function (data) {
            showToast(data.message, data.success ? 'success' : 'danger');
            if (data.success) { bootstrap.Modal.getInstance(document.getElementById('coaModal')).hide(); loadCOA(); }
        })
        .fail(function () { showToast('Gagal menyimpan akun', 'danger'); });
}

// ─── JOURNAL ENTRIES ─────────────────────────────────────────────────────────
function loadJournals(page = 1) {
    const params = { action: 'get_journals', date_from: $('#jDateFrom').val(), date_to: $('#jDateTo').val(), page };
    apiGet('/accounting.php', params)
        .done(function (data) {
            if (!data.success) { showToast(data.message, 'danger'); return; }
            const { journals } = data.data;
            if (!journals.length) {
                $('#journalBody').html('<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada jurnal pada periode ini</td></tr>');
                return;
            }
            const statusBadge = { posted: 'bg-success', draft: 'bg-secondary', reversed: 'bg-warning text-dark' };
            $('#journalBody').html(journals.map(j => `
                <tr>
                    <td><code>${j.journal_number}</code></td>
                    <td>${formatDate(j.entry_date)}</td>
                    <td>${j.description}</td>
                    <td>${j.reference_type ? '<small class="badge bg-light text-dark border">' + j.reference_type + ' #' + j.reference_id + '</small>' : '—'}</td>
                    <td><span class="badge ${statusBadge[j.status] || 'bg-secondary'}">${j.status}</span></td>
                    <td><small>${j.created_by_name || '—'}</small></td>
                    <td>
                        <button class="btn btn-sm btn-outline-info py-0" onclick="viewJournal(${j.id})"><i class="fas fa-eye"></i></button>
                        ${j.status === 'posted' ? `<button class="btn btn-sm btn-outline-warning py-0 ms-1" onclick="confirmReverse(${j.id},'${j.journal_number}')"><i class="fas fa-undo"></i></button>` : ''}
                    </td>
                </tr>`).join(''));
        })
        .fail(function () { showToast('Gagal memuat jurnal', 'danger'); });
}

function openJournalModal() {
    $('#jEntryDate').val(new Date().toISOString().slice(0, 10));
    $('#jDescription').val('');
    $('#journalLinesBody').empty();
    addJournalLine(); addJournalLine();
    updateTotals();
    new bootstrap.Modal(document.getElementById('journalModal')).show();
}

function addJournalLine() {
    const options = coaList.filter(a => a.parent_id)
        .map(a => `<option value="${a.id}">${a.account_code} — ${a.account_name}</option>`).join('');
    $('#journalLinesBody').append(`
        <tr class="journal-line-row">
            <td><select class="form-select form-select-sm line-account"><option value="">— Pilih Akun —</option>${options}</select></td>
            <td><input type="text" class="form-control form-control-sm line-desc" placeholder="Keterangan baris"></td>
            <td><input type="number" class="form-control form-control-sm line-debit text-end" min="0" value="0"></td>
            <td><input type="number" class="form-control form-control-sm line-credit text-end" min="0" value="0"></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger py-0 btn-remove-line"><i class="fas fa-times"></i></button></td>
        </tr>`);
}

function updateTotals() {
    let debit = 0, credit = 0;
    $('.line-debit').each(function () { debit  += parseFloat($(this).val()) || 0; });
    $('.line-credit').each(function () { credit += parseFloat($(this).val()) || 0; });
    $('#totalDebitDisplay').text(formatRp(debit));
    $('#totalCreditDisplay').text(formatRp(credit));
    const balanced = Math.abs(debit - credit) < 0.01;
    $('#balanceStatus')
        .text(balanced ? '✅ Seimbang' : '❌ Tidak seimbang (selisih: ' + formatRp(Math.abs(debit - credit)) + ')')
        .attr('class', balanced ? 'text-success fw-bold' : 'text-danger fw-bold');
}

function saveJournal() {
    const date = $('#jEntryDate').val();
    const desc = $('#jDescription').val().trim();
    if (!date || !desc) { showToast('Tanggal dan keterangan wajib diisi', 'warning'); return; }

    const lines = [];
    $('#journalLinesBody tr').each(function () {
        const acct   = $(this).find('.line-account').val();
        const debit  = parseFloat($(this).find('.line-debit').val()) || 0;
        const credit = parseFloat($(this).find('.line-credit').val()) || 0;
        if (acct) lines.push({ account_id: acct, debit_amount: debit, credit_amount: credit, description: $(this).find('.line-desc').val() });
    });

    if (lines.length < 2) { showToast('Minimal 2 baris jurnal diperlukan', 'warning'); return; }
    const totalD = lines.reduce((s, l) => s + l.debit_amount, 0);
    const totalC = lines.reduce((s, l) => s + l.credit_amount, 0);
    if (Math.abs(totalD - totalC) > 0.01) { showToast('Total debit dan kredit harus seimbang', 'danger'); return; }

    apiPost('/accounting.php', { action: 'create_journal', entry_date: date, description: desc, lines })
        .done(function (data) {
            showToast(data.message, data.success ? 'success' : 'danger');
            if (data.success) { bootstrap.Modal.getInstance(document.getElementById('journalModal')).hide(); loadJournals(); }
        })
        .fail(function () { showToast('Gagal menyimpan jurnal', 'danger'); });
}

function viewJournal(id) {
    currentJournalId = id;
    apiGet('/accounting.php', { action: 'get_journal', id })
        .done(function (data) {
            if (!data.success) { showToast(data.message, 'danger'); return; }
            const j = data.data;
            const linesHtml = j.lines.map(l => `
                <tr>
                    <td><code>${l.account_code}</code> ${l.account_name}</td>
                    <td class="amount-col">${l.debit_amount > 0 ? formatRp(l.debit_amount) : ''}</td>
                    <td class="amount-col">${l.credit_amount > 0 ? '&nbsp;&nbsp;&nbsp;' + formatRp(l.credit_amount) : ''}</td>
                    <td class="text-muted small">${l.description || ''}</td>
                </tr>`).join('');

            $('#journalDetailTitle').text(j.journal_number);
            $('#journalDetailBody').html(`
                <div class="row g-2 mb-3">
                    <div class="col-6"><small class="text-muted">Tanggal</small><div><strong>${formatDate(j.entry_date)}</strong></div></div>
                    <div class="col-6"><small class="text-muted">Status</small><div>
                        <span class="badge ${j.status === 'posted' ? 'bg-success' : j.status === 'reversed' ? 'bg-warning text-dark' : 'bg-secondary'}">${j.status}</span>
                    </div></div>
                    <div class="col-12"><small class="text-muted">Keterangan</small><div>${j.description}</div></div>
                </div>
                <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light"><tr>
                        <th>Akun</th>
                        <th class="text-end" data-bs-toggle="tooltip" title="Debit: Uang/aset masuk atau bertambah">Debit (Rp)</th>
                        <th class="text-end" data-bs-toggle="tooltip" title="Kredit: Uang/aset keluar atau berkurang">Kredit (Rp)</th>
                        <th>Ket.</th>
                    </tr></thead>
                    <tbody>${linesHtml}</tbody>
                </table></div>`);

            $('#reverseJournalBtn').toggle(j.status === 'posted');
            new bootstrap.Modal(document.getElementById('journalDetailModal')).show();
        })
        .fail(function () { showToast('Gagal memuat detail jurnal', 'danger'); });
}

function confirmReverse(id, number) {
    if (confirm(`Balik jurnal ${number}?\nJurnal pembalik akan dibuat otomatis.`)) reverseJournal(id);
}

function reverseJournal(id) {
    const jid  = id || currentJournalId;
    const note = prompt('Catatan pembalikan (opsional):') || '';
    apiPost('/accounting.php', { action: 'reverse_journal', id: jid, note })
        .done(function (data) {
            showToast(data.message, data.success ? 'success' : 'danger');
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('journalDetailModal'))?.hide();
                loadJournals();
            }
        })
        .fail(function () { showToast('Gagal membalik jurnal', 'danger'); });
}

// ─── FINANCIAL REPORTS ───────────────────────────────────────────────────────
function updateReportForm() {
    const needsRange = ['trial_balance', 'income_statement', 'cash_flow'].includes($('#reportType').val());
    $('#fieldDateFrom, #fieldDateTo').toggle(needsRange);
    $('#fieldAsOf').toggle(!needsRange);
}

function loadReport() {
    const type = $('#reportType').val();
    const from = $('#rDateFrom').val();
    const to   = $('#rDateTo').val();
    const asOf = $('#rAsOf').val() || new Date().toISOString().slice(0, 10);

    const params = { action: type };
    if (['trial_balance', 'income_statement', 'cash_flow'].includes(type)) {
        params.date_from = from; params.date_to = to;
    } else {
        params.as_of = asOf;
    }

    apiGet('/accounting.php', params)
        .done(function (data) {
            if (!data.success) { showToast(data.message, 'danger'); return; }
            renderReport(type, data.data);
        })
        .fail(function () { showToast('Gagal memuat laporan', 'danger'); });
}

function renderReport(type, data) {
    $('#reportContainer').show();
    const titles = { trial_balance: 'Neraca Saldo', balance_sheet: 'Neraca', income_statement: 'Laba Rugi', cash_flow: 'Arus Kas' };
    $('#reportTitle').text(titles[type] || type);
    $('#reportSubtitle').text(data.period ? (data.period.from + ' s/d ' + data.period.to) : (data.as_of || ''));

    if (type === 'trial_balance') {
        $('#reportTableContainer').html(`<table class="table table-sm table-hover mb-0">
            <thead><tr><th>Kode</th><th>Nama Akun</th><th>Tipe</th><th class="text-end">Debit (Rp)</th><th class="text-end">Kredit (Rp)</th></tr></thead>
            <tbody>${data.rows.map(r => `<tr>
                <td><code>${r.account_code}</code></td><td>${r.account_name}</td>
                <td><span class="badge ${typeBadge[r.account_type] || 'bg-secondary'} small">${typeLabel[r.account_type] || ''}</span></td>
                <td class="amount-col">${r.total_debit > 0 ? formatRp(r.total_debit) : ''}</td>
                <td class="amount-col">${r.total_credit > 0 ? formatRp(r.total_credit) : ''}</td>
            </tr>`).join('')}
            <tr class="total-row"><td colspan="3"><strong>TOTAL</strong></td>
                <td class="amount-col">${formatRp(data.total_debit)}</td>
                <td class="amount-col">${formatRp(data.total_credit)}</td>
            </tr></tbody></table>`);
        $('#reportSummary').html(`<span class="badge ${data.is_balanced ? 'bg-success' : 'bg-danger'} fs-6">${data.is_balanced ? '✅ Neraca Seimbang' : '❌ Neraca Tidak Seimbang'}</span>`);

    } else if (type === 'balance_sheet') {
        const section = (label, rows, total) => `
            <div class="report-section mb-3"><h6 class="fw-bold text-uppercase">${label}</h6>
                <table class="table table-sm mb-1">
                    ${rows.map(r => `<tr><td style="padding-left:${r.parent_id ? '2rem' : '0'}">${r.account_name}</td><td class="amount-col">${formatRp(r.balance)}</td></tr>`).join('')}
                    <tr class="total-row"><td><strong>Total ${label}</strong></td><td class="amount-col"><strong>${formatRp(total)}</strong></td></tr>
                </table></div>`;
        $('#reportTableContainer').html(`<div class="p-3">
            ${section('Aset', data.assets, data.total_assets)}
            ${section('Kewajiban', data.liabilities, data.total_liabilities)}
            ${section('Ekuitas', data.equity, data.total_equity)}
        </div>`);
        $('#reportSummary').html(`<strong>Total Kewajiban + Ekuitas: ${formatRp(data.total_liabilities + data.total_equity)}</strong>
            &nbsp;<span class="badge ${data.is_balanced ? 'bg-success' : 'bg-danger'}">${data.is_balanced ? '✅ Seimbang' : '❌ Tidak Seimbang'}</span>`);

    } else if (type === 'income_statement') {
        $('#reportTableContainer').html(`<table class="table table-sm mb-0">
            <thead><tr><th>Akun</th><th>Nama</th><th class="text-end">Jumlah (Rp)</th></tr></thead>
            <tbody>
                <tr><td colspan="3" class="fw-bold bg-light">PENDAPATAN</td></tr>
                ${data.revenues.map(r => `<tr><td><code>${r.account_code}</code></td><td>${r.account_name}</td><td class="amount-col text-success">${formatRp(r.amount)}</td></tr>`).join('')}
                <tr class="total-row"><td colspan="2"><strong>Total Pendapatan</strong></td><td class="amount-col">${formatRp(data.total_revenue)}</td></tr>
                <tr><td colspan="3" class="fw-bold bg-light">BEBAN</td></tr>
                ${data.expenses.map(r => `<tr><td><code>${r.account_code}</code></td><td>${r.account_name}</td><td class="amount-col text-danger">${formatRp(r.amount)}</td></tr>`).join('')}
                <tr class="total-row"><td colspan="2"><strong>Total Beban</strong></td><td class="amount-col">${formatRp(data.total_expense)}</td></tr>
                <tr class="${data.net_income >= 0 ? 'table-success' : 'table-danger'}">
                    <td colspan="2"><strong>SHU / Laba Bersih</strong></td>
                    <td class="amount-col"><strong>${formatRp(data.net_income)}</strong></td>
                </tr>
            </tbody></table>`);
        $('#reportSummary').html(`<span class="fs-6 ${data.net_income >= 0 ? 'text-success' : 'text-danger'}">
            <strong>${data.net_income >= 0 ? '✅ Laba' : '❌ Rugi'}: ${formatRp(Math.abs(data.net_income))}</strong></span>`);

    } else if (type === 'cash_flow') {
        $('#reportTableContainer').html(`<table class="table table-sm mb-0">
            <thead><tr><th>Tanggal</th><th>No Jurnal</th><th>Keterangan</th><th>Akun</th><th class="text-end">Masuk (Rp)</th><th class="text-end">Keluar (Rp)</th></tr></thead>
            <tbody>
                ${data.rows.map(r => `<tr>
                    <td>${formatDate(r.entry_date)}</td><td><code>${r.journal_number}</code></td>
                    <td>${r.description}</td><td><small>${r.account_name}</small></td>
                    <td class="amount-col text-success">${r.debit_amount > 0 ? formatRp(r.debit_amount) : ''}</td>
                    <td class="amount-col text-danger">${r.credit_amount > 0 ? formatRp(r.credit_amount) : ''}</td>
                </tr>`).join('')}
                <tr class="total-row"><td colspan="4"><strong>TOTAL</strong></td>
                    <td class="amount-col">${formatRp(data.total_in)}</td>
                    <td class="amount-col">${formatRp(data.total_out)}</td>
                </tr>
            </tbody></table>`);
        $('#reportSummary').html(`<strong>Arus Kas Bersih: <span class="${data.net_cash >= 0 ? 'text-success' : 'text-danger'}">${formatRp(data.net_cash)}</span></strong>`);
    }
}

function exportReport(format) {
    const type  = $('#reportType').val();
    const from  = $('#rDateFrom').val();
    const to    = $('#rDateTo').val();
    const asOf  = $('#rAsOf').val() || new Date().toISOString().slice(0, 10);
    const token = getToken();
    let url = BASE + '/reports.php?format=' + format + '&report=' + type + '&token=' + token;
    if (['trial_balance', 'income_statement', 'cash_flow'].includes(type)) url += `&date_from=${from}&date_to=${to}`;
    else url += `&as_of=${asOf}`;
    window.open(url, '_blank');
}

// ─── Init ─────────────────────────────────────────────────────────────────────
$(document).ready(function () {
    const today        = new Date().toISOString().slice(0, 10);
    const firstOfMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0, 10);
    const firstOfYear  = new Date(new Date().getFullYear(), 0, 1).toISOString().slice(0, 10);
    $('#jDateFrom').val(firstOfMonth);
    $('#jDateTo').val(today);
    $('#rDateFrom').val(firstOfYear);
    $('#rDateTo').val(today);
    $('#rAsOf').val(today);

    loadCOA();
    loadJournals();
    updateReportForm();

    // Event delegation for dynamic remove-line buttons
    $(document).on('click', '.btn-remove-line', function () {
        $(this).closest('tr').remove();
        updateTotals();
    });
    // Update totals on input change (event delegation)
    $(document).on('input change', '.line-debit, .line-credit', updateTotals);

    // Tab switch → reload journals
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($(e.target).attr('href') === '#tab-journal') loadJournals();
    });
});
</script>
</body>
</html>
