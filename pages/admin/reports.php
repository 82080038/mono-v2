<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Umum - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        #mainContent{margin-left:0}
        .report-card{border:2px solid #e5e7eb;border-radius:12px;padding:1.25rem;cursor:pointer;transition:all .2s}
        .report-card:hover{border-color:#2563eb;background:#f0f4ff}
        .report-card.selected{border-color:#2563eb;background:#eff6ff}
    </style>
</head>
<body>
<?php $activePage = 'reports'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">Ekspor Laporan</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Laporan Umum</li></ol></nav></div>
    </div>
    <div class="page-body">
        <!-- Pilih Jenis Laporan -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-list me-2 text-primary"></i>Pilih Jenis Laporan</div>
            <div class="card-body">
                <div class="row g-3" id="reportCards">
                    <div class="col-md-4 col-6"><div class="report-card" onclick="selectReport('members')" id="rc_members"><i class="fas fa-users text-primary fs-4 mb-2"></i><div class="fw-semibold">Daftar Anggota</div><div class="text-muted small">Rekap data seluruh anggota</div></div></div>
                    <div class="col-md-4 col-6"><div class="report-card" onclick="selectReport('savings')" id="rc_savings"><i class="fas fa-piggy-bank text-success fs-4 mb-2"></i><div class="fw-semibold">Rekap Simpanan</div><div class="text-muted small">Saldo & transaksi simpanan</div></div></div>
                    <div class="col-md-4 col-6"><div class="report-card" onclick="selectReport('loans')" id="rc_loans"><i class="fas fa-hand-holding-usd text-warning fs-4 mb-2"></i><div class="fw-semibold">Portofolio Pinjaman</div><div class="text-muted small">Outstanding & cicilan</div></div></div>
                    <div class="col-md-4 col-6"><div class="report-card" onclick="selectReport('trial_balance')" id="rc_trial_balance"><i class="fas fa-balance-scale text-info fs-4 mb-2"></i><div class="fw-semibold">Neraca Saldo</div><div class="text-muted small">Trial balance periode tertentu</div></div></div>
                    <div class="col-md-4 col-6"><div class="report-card" onclick="selectReport('income_statement')" id="rc_income_statement"><i class="fas fa-file-invoice-dollar text-danger fs-4 mb-2"></i><div class="fw-semibold">Laba Rugi</div><div class="text-muted small">Pendapatan vs pengeluaran</div></div></div>
                    <div class="col-md-4 col-6"><div class="report-card" onclick="selectReport('shu')" id="rc_shu"><i class="fas fa-award text-purple fs-4 mb-2"></i><div class="fw-semibold">SHU</div><div class="text-muted small">Sisa Hasil Usaha per tahun</div></div></div>
                </div>
            </div>
        </div>
        <!-- Parameter & Ekspor -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-sliders-h me-2 text-primary"></i>Parameter & Ekspor</div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Laporan Dipilih</label>
                        <input type="text" class="form-control" id="selectedReport" readonly placeholder="Pilih laporan di atas..." value="">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Dari Tanggal</label>
                        <input type="date" class="form-control" id="dateFrom" value="">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="dateTo" value="">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Tahun (SHU)</label>
                        <input type="number" class="form-control" id="yearInput" value="" placeholder="2025">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Format Ekspor</label>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-danger flex-fill" onclick="doExport('pdf')" title="Ekspor PDF"><i class="fas fa-file-pdf me-1"></i>PDF</button>
                            <button class="btn btn-outline-success flex-fill" onclick="doExport('excel')" title="Ekspor Excel"><i class="fas fa-file-excel me-1"></i>Excel</button>
                            <button class="btn btn-outline-secondary flex-fill" onclick="doExport('csv')" title="Ekspor CSV"><i class="fas fa-file-csv me-1"></i>CSV</button>
                        </div>
                    </div>
                </div>
                <div id="exportStatus" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="../../assets/js/config.js"></script>
<script src="../../assets/js/auth-fixed.js"></script>
<script>
(function(){const u=JSON.parse(localStorage.getItem('userData')||'{}');if(!u.token){window.location.href='../../login.html';return;}const r=(u.role||'').toLowerCase().replace(' ','_');if(!['admin','super_admin','manager'].includes(r)){window.location.href='../../login.html';return;}$('#sidebarUserName').text(u.name||'Admin');$('#sidebarUserRole').text(u.role||'');})();
function getToken(){return JSON.parse(localStorage.getItem('userData')||'{}').token||'';}
function logout(){if(confirm('Keluar?')){localStorage.removeItem('userData');window.location.href='../../login.html';}}
const BASE=window.APP_CONFIG?.baseUrl||'';
let selectedKey='';
const reportLabels={members:'Daftar Anggota',savings:'Rekap Simpanan',loans:'Portofolio Pinjaman',trial_balance:'Neraca Saldo',income_statement:'Laba Rugi',shu:'SHU'};

// Default dates
const now=new Date();$('#dateFrom').val(now.getFullYear()+'-01-01');$('#dateTo').val(now.toISOString().substring(0,10));$('#yearInput').val(now.getFullYear());

function selectReport(key){
    selectedKey=key;$('#selectedReport').val(reportLabels[key]||key);
    document.querySelectorAll('.report-card').forEach(c=>c.classList.remove('selected'));
    document.getElementById('rc_'+key)?.classList.add('selected');
}

function doExport(fmt){
    if(!selectedKey){$('#exportStatus').html('<div class="alert alert-warning py-2">Pilih jenis laporan terlebih dahulu.</div>');return;}
    const params=new URLSearchParams({report:selectedKey,format:fmt,date_from:$('#dateFrom').val(),date_to:$('#dateTo').val(),year:$('#yearInput').val(),token:getToken()});
    $('#exportStatus').html('<div class="alert alert-info py-2"><i class="fas fa-spinner fa-spin me-2"></i>Memproses ekspor...</div>');
    const url=`${BASE}/api/reports.php?${params.toString()}`;
    // Open in new tab for download
    const a=document.createElement('a');a.href=url;a.target='_blank';a.rel='noopener';document.body.appendChild(a);a.click();document.body.removeChild(a);
    setTimeout(()=>$('#exportStatus').html('<div class="alert alert-success py-2"><i class="fas fa-check me-2"></i>Permintaan ekspor dikirim. File akan diunduh segera.</div>'),1000);
}
</script>
</body>
</html>
