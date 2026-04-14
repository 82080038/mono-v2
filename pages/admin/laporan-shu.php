<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan SHU - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        #mainContent{margin-left:0}
        .page-header{background:linear-gradient(135deg,#7b2d00,#bf4800);color:#fff;padding:1.5rem;border-radius:12px;margin-bottom:1.5rem;}
        .shu-stat-card{border-left:4px solid;border-radius:8px;padding:1rem;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.08);}
        .amount-col{text-align:right;font-family:monospace;}
    </style>
</head>
<body>
<?php $activePage = 'laporan-shu'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">SHU — Sisa Hasil Usaha</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Laporan SHU</li></ol></nav></div>
    </div>
<div class="page-body">

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label fw-semibold">Tahun Buku</label>
            <select id="shuYear" class="form-select"></select>
        </div>
        <div class="col-md-9 d-flex align-items-end gap-2">
            <button class="btn btn-primary" onclick="calculateSHU()"><i class="fas fa-calculator me-1"></i>Hitung dari Jurnal</button>
            <button class="btn btn-success" onclick="openSavePeriodModal()"><i class="fas fa-save me-1"></i>Simpan Periode</button>
            <button class="btn btn-info text-white" onclick="loadDistributions()"><i class="fas fa-list me-1"></i>Distribusi Anggota</button>
            <button class="btn btn-outline-secondary" onclick="exportSHU()"><i class="fas fa-file-csv me-1"></i>Export CSV</button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4" id="shuSummary" style="display:none">
        <div class="col-md-3"><div class="shu-stat-card" style="border-color:#0d6efd">
            <div class="text-muted small">Total Pendapatan</div><div class="fs-5 fw-bold text-primary" id="sRevenue">—</div></div></div>
        <div class="col-md-3"><div class="shu-stat-card" style="border-color:#dc3545">
            <div class="text-muted small">Total Beban</div><div class="fs-5 fw-bold text-danger" id="sExpense">—</div></div></div>
        <div class="col-md-3"><div class="shu-stat-card" style="border-color:#198754">
            <div class="text-muted small">Bruto SHU</div><div class="fs-5 fw-bold text-success" id="sGross">—</div></div></div>
        <div class="col-md-3"><div class="shu-stat-card" style="border-color:#fd7e14">
            <div class="text-muted small">Status Periode</div><div class="fs-5 fw-bold" id="sStatus">—</div></div></div>
    </div>

    <!-- Distribusi Table -->
    <div class="card shadow-sm" id="distCard" style="display:none">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-users me-2 text-warning"></i>Distribusi SHU per Anggota</h6>
            <div class="gap-2 d-flex">
                <button class="btn btn-sm btn-warning" onclick="computeDistributions()"><i class="fas fa-calculator me-1"></i>Hitung Ulang</button>
                <button class="btn btn-sm btn-success" onclick="finalizePeriod()"><i class="fas fa-lock me-1"></i>Finalisasi</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>No Anggota</th><th>Nama</th><th class="text-end">Saldo Simpanan</th><th class="text-end">Saldo Pinjaman</th><th class="text-end">Bagian Simpanan</th><th class="text-end">Bagian Pinjaman</th><th class="text-end">Total SHU</th><th>Sudah Dibagi</th></tr></thead>
                    <tbody id="distBody"><tr><td colspan="8" class="text-center text-muted py-3">Klik "Distribusi Anggota" untuk memuat data</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Periods History -->
    <div class="card shadow-sm mt-4">
        <div class="card-header"><h6 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Periode SHU</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Tahun</th><th class="text-end">Pendapatan</th><th class="text-end">Beban</th><th class="text-end">SHU Bruto</th><th>% Simpanan</th><th>% Pinjaman</th><th>Status</th><th>Finalisasi</th></tr></thead>
                    <tbody id="periodsBody"><tr><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Memuat...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div></div>
<!-- Modal: Simpan Periode -->
<div class="modal fade" id="savePeriodModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Simpan / Update Periode SHU</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6"><label class="form-label">Tahun</label><input type="number" id="spYear" class="form-control" min="2020" max="2099"></div>
                    <div class="col-6"><label class="form-label">Total Pendapatan (Rp)</label><input type="number" id="spRevenue" class="form-control" step="1"></div>
                    <div class="col-6"><label class="form-label">Total Beban (Rp)</label><input type="number" id="spExpense" class="form-control" step="1"></div>
                    <div class="col-6"><label class="form-label">% Jasa Simpanan</label><input type="number" id="spPctSavings" class="form-control" value="30" step="0.01" oninput="updatePctTotal()"></div>
                    <div class="col-6"><label class="form-label">% Jasa Pinjaman</label><input type="number" id="spPctLoans" class="form-control" value="30" step="0.01" oninput="updatePctTotal()"></div>
                    <div class="col-6"><label class="form-label">% Honorarium Mgmt</label><input type="number" id="spPctMgmt" class="form-control" value="10" step="0.01" oninput="updatePctTotal()"></div>
                    <div class="col-6"><label class="form-label">% Dana Pendidikan</label><input type="number" id="spPctEdu" class="form-control" value="5" step="0.01" oninput="updatePctTotal()"></div>
                    <div class="col-6"><label class="form-label">% Dana Sosial</label><input type="number" id="spPctSocial" class="form-control" value="5" step="0.01" oninput="updatePctTotal()"></div>
                    <div class="col-6"><label class="form-label">% Cadangan Risiko</label><input type="number" id="spPctReserve" class="form-control" value="20" step="0.01" oninput="updatePctTotal()"></div>
                    <div class="col-12"><div class="alert alert-info py-2 mb-0">Total %: <strong id="pctTotal">100%</strong></div></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="savePeriod()"><i class="fas fa-save me-1"></i>Simpan</button>
            </div>
        </div>
    </div>
</div>

<div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index:9999"></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/config.js"></script>
<script>
(function() {
    const u = JSON.parse(localStorage.getItem('userData')||'{}');
    const r=(u.role||'').toLowerCase().replace(' ', '_');
    if (!u.token || !['admin','super_admin','manager'].includes(r)) window.location.href='../../login.html';
})();

const BASE = (window.APP_CONFIG?.baseUrl||'') + '/api';
let currentPeriodId = null;

function getToken() { return JSON.parse(localStorage.getItem('userData')||'{}').token||''; }
function authH()    { return { 'Authorization': 'Bearer '+getToken() }; }
function logout()   { if(confirm('Keluar?')){localStorage.removeItem('userData');window.location.href='../../login.html';} }
function formatRp(v){ return 'Rp '+parseFloat(v||0).toLocaleString('id-ID',{minimumFractionDigits:0}); }
function showToast(msg,type='success'){
    const id='t'+Date.now();
    $('#toastContainer').append(`<div id="${id}" class="toast align-items-center text-bg-${type} border-0 show"><div class="d-flex"><div class="toast-body">${msg}</div><button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`);
    setTimeout(()=>$('#'+id).remove(),4000);
}
function apiGet(ep,params){ return $.ajax({url:BASE+ep,method:'GET',data:params,headers:authH()}); }
function apiPost(ep,data) { return $.ajax({url:BASE+ep,method:'POST',contentType:'application/json',data:JSON.stringify(data),headers:authH()}); }

function loadPeriods() {
    apiGet('/shu.php',{action:'get_periods'}).done(function(d){
        if(!d.success){showToast(d.message,'danger');return;}
        if(!d.data.length){$('#periodsBody').html('<tr><td colspan="8" class="text-center text-muted py-3">Belum ada data periode SHU</td></tr>');return;}
        $('#periodsBody').html(d.data.map(p=>`<tr>
            <td><strong>${p.period_year}</strong></td>
            <td class="text-end">${formatRp(p.total_revenue)}</td>
            <td class="text-end">${formatRp(p.total_expense)}</td>
            <td class="text-end fw-bold text-success">${formatRp(p.gross_shu)}</td>
            <td>${p.pct_member_savings}%</td><td>${p.pct_member_loans}%</td>
            <td><span class="badge ${p.status==='final'?'bg-success':'bg-secondary'}">${p.status}</span></td>
            <td><small>${p.finalized_at?new Date(p.finalized_at).toLocaleDateString('id-ID'):'-'}</small></td>
        </tr>`).join(''));
    }).fail(()=>showToast('Gagal memuat periode','danger'));
}

function calculateSHU() {
    const year = $('#shuYear').val();
    apiGet('/shu.php',{action:'calculate',year}).done(function(d){
        if(!d.success){showToast(d.message,'danger');return;}
        const r=d.data;
        $('#sRevenue').text(formatRp(r.total_revenue));
        $('#sExpense').text(formatRp(r.total_expense));
        $('#sGross').text(formatRp(r.gross_shu));
        $('#sStatus').text('Draft');
        $('#shuSummary').show();
        $('#spYear').val(year); $('#spRevenue').val(r.total_revenue); $('#spExpense').val(r.total_expense);
        showToast('Kalkulasi SHU selesai. Silakan simpan periode.');
    }).fail(()=>showToast('Gagal menghitung SHU','danger'));
}

function openSavePeriodModal() {
    updatePctTotal();
    new bootstrap.Modal(document.getElementById('savePeriodModal')).show();
}

function updatePctTotal() {
    const ids=['spPctSavings','spPctLoans','spPctMgmt','spPctEdu','spPctSocial','spPctReserve'];
    const total=ids.reduce((s,id)=>s+parseFloat($('#'+id).val()||0),0);
    $('#pctTotal').text(total.toFixed(2)+'%').attr('class',Math.abs(total-100)<0.01?'text-success fw-bold':'text-danger fw-bold');
}

function savePeriod() {
    const payload={
        action:'save_period', year:$('#spYear').val(),
        total_revenue:$('#spRevenue').val(), total_expense:$('#spExpense').val(),
        pct_member_savings:$('#spPctSavings').val(), pct_member_loans:$('#spPctLoans').val(),
        pct_management:$('#spPctMgmt').val(), pct_education:$('#spPctEdu').val(),
        pct_social:$('#spPctSocial').val(), pct_reserve:$('#spPctReserve').val(),
    };
    apiPost('/shu.php',payload).done(function(d){
        showToast(d.message,d.success?'success':'danger');
        if(d.success){bootstrap.Modal.getInstance(document.getElementById('savePeriodModal')).hide();loadPeriods();}
    }).fail(()=>showToast('Gagal menyimpan periode','danger'));
}

function loadDistributions() {
    const year=$('#shuYear').val();
    apiGet('/shu.php',{action:'get_period',year}).done(function(pData){
        if(!pData.success){showToast('Periode tahun '+year+' belum disimpan. Hitung dan simpan dulu.','warning');return;}
        currentPeriodId=pData.data.id;
        $('#distCard').show();
        $('#sStatus').text(pData.data.status);
        $('#sGross').text(formatRp(pData.data.gross_shu));
        $('#shuSummary').show();
        apiGet('/shu.php',{action:'get_distributions',period_id:currentPeriodId}).done(function(d){
            if(!d.success){showToast(d.message,'danger');return;}
            if(!d.data.length){$('#distBody').html('<tr><td colspan="8" class="text-center text-muted py-3">Belum ada distribusi. Klik "Hitung Ulang" untuk menghitung.</td></tr>');return;}
            $('#distBody').html(d.data.map(m=>`<tr>
                <td><code>${m.member_number}</code></td><td>${m.full_name}</td>
                <td class="text-end">${formatRp(m.savings_balance)}</td>
                <td class="text-end">${formatRp(m.loan_principal)}</td>
                <td class="text-end text-primary">${formatRp(m.savings_share)}</td>
                <td class="text-end text-info">${formatRp(m.loan_share)}</td>
                <td class="text-end fw-bold text-success">${formatRp(m.total_share)}</td>
                <td><span class="badge ${m.is_distributed?'bg-success':'bg-secondary'}">${m.is_distributed?'Ya':'Belum'}</span></td>
            </tr>`).join(''));
        }).fail(()=>showToast('Gagal memuat distribusi','danger'));
    }).fail(()=>showToast('Gagal memuat periode','warning'));
}

function computeDistributions() {
    if(!currentPeriodId){showToast('Muat distribusi dulu','warning');return;}
    apiPost('/shu.php',{action:'distribute',period_id:currentPeriodId}).done(function(d){
        showToast(d.message,d.success?'success':'danger');
        if(d.success) loadDistributions();
    }).fail(()=>showToast('Gagal menghitung distribusi','danger'));
}

function finalizePeriod() {
    if(!currentPeriodId){showToast('Muat distribusi dulu','warning');return;}
    if(!confirm('Finalisasi periode SHU? Data tidak dapat diubah setelah finalisasi.')) return;
    apiPost('/shu.php',{action:'finalize',period_id:currentPeriodId}).done(function(d){
        showToast(d.message,d.success?'success':'danger');
        if(d.success){loadPeriods();loadDistributions();}
    }).fail(()=>showToast('Gagal finalisasi','danger'));
}

function exportSHU() {
    window.open(BASE+'/reports.php?format=csv&report=shu&year='+$('#shuYear').val()+'&token='+getToken(),'_blank');
}

$(document).ready(function(){
    const cur=new Date().getFullYear();
    for(let y=cur;y>=cur-5;y--) $('#shuYear').append(`<option value="${y}">${y}</option>`);
    loadPeriods();
    $(document).on('input','#spPctSavings,#spPctLoans,#spPctMgmt,#spPctEdu,#spPctSocial,#spPctReserve',updatePctTotal);
});
</script>
</body>
</html>
