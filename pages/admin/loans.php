<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portofolio Pinjaman - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        #mainContent{margin-left:0}
    </style>
</head>
<body>
<?php $activePage = 'loans'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">Portofolio Pinjaman</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Pinjaman</li></ol></nav></div>
        <div class="ms-auto"><a href="loan-management.php" class="btn btn-primary btn-sm"><i class="fas fa-tasks me-1"></i>Kelola Pinjaman</a></div>
    </div>
    <div class="page-body">
        <!-- KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body d-flex align-items-center gap-3"><div style="width:48px;height:48px;border-radius:12px;background:#dbeafe;display:flex;align-items:center;justify-content:center"><i class="fas fa-file-invoice text-primary fs-5"></i></div><div><div class="text-muted small">Total Pinjaman</div><div class="fs-4 fw-bold" id="kTotal">—</div></div></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body d-flex align-items-center gap-3"><div style="width:48px;height:48px;border-radius:12px;background:#dcfce7;display:flex;align-items:center;justify-content:center"><i class="fas fa-coins text-success fs-5"></i></div><div><div class="text-muted small">Total Pokok</div><div class="fs-6 fw-bold text-success" id="kAmount">—</div></div></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body d-flex align-items-center gap-3"><div style="width:48px;height:48px;border-radius:12px;background:#fef9c3;display:flex;align-items:center;justify-content:center"><i class="fas fa-clock text-warning fs-5"></i></div><div><div class="text-muted small">Sisa Pokok</div><div class="fs-6 fw-bold text-warning" id="kOutstanding">—</div></div></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body d-flex align-items-center gap-3"><div style="width:48px;height:48px;border-radius:12px;background:#fee2e2;display:flex;align-items:center;justify-content:center"><i class="fas fa-exclamation-circle text-danger fs-5"></i></div><div><div class="text-muted small">Macet</div><div class="fs-4 fw-bold text-danger" id="kNpl">—</div></div></div></div></div>
        </div>
        <!-- Filter -->
        <div class="card border-0 shadow-sm mb-3"><div class="card-body py-2"><div class="row g-2 align-items-center">
            <div class="col-md-4"><div class="input-group input-group-sm"><span class="input-group-text"><i class="fas fa-search"></i></span><input type="text" id="searchInput" class="form-control" placeholder="Cari nama anggota, no. pinjaman..." oninput="debounce()"></div></div>
            <div class="col-md-2"><select id="filterStatus" class="form-select form-select-sm" onchange="load()"><option value="">Semua Status</option><option value="pending">Pending</option><option value="approved">Disetujui</option><option value="disbursed">Cair</option><option value="active">Aktif</option><option value="completed">Lunas</option><option value="overdue">Telat</option><option value="npl">NPL</option></select></div>
            <div class="col-md-2"><select id="filterType" class="form-select form-select-sm" onchange="load()"><option value="">Semua Tipe</option></select></div>
        </div></div></div>
        <!-- Table -->
        <div class="card border-0 shadow-sm"><div class="card-body p-0"><div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>No. Pinjaman</th><th>Anggota</th><th>Tipe</th><th>Pokok</th><th>Sisa</th><th>Tenor</th><th>Status</th><th></th></tr></thead>
                <tbody id="tableBody"><tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Memuat...</td></tr></tbody>
            </table>
        </div></div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center"><span class="text-muted small" id="pageInfo">—</span><div id="pagination" class="d-flex gap-1"></div></div></div>
    </div>
</div>
<div class="modal fade" id="detailModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-hand-holding-usd me-2"></i>Detail Pinjaman</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="detailBody"><div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i></div></div>
</div></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="../../assets/js/config.js"></script>
<script src="../../assets/js/auth-fixed.js"></script>
<script>
(function(){const u=JSON.parse(localStorage.getItem('userData')||'{}');if(!u.token){window.location.href='../../login.html';return;}const r=(u.role||'').toLowerCase().replace(' ','_');if(!['admin','super_admin','manager'].includes(r)){window.location.href='../../login.html';return;}$('#sidebarUserName').text(u.name||'Admin');$('#sidebarUserRole').text(u.role||'');})();
const BASE=(window.APP_CONFIG?.baseUrl||'')+'/api';
function getToken(){return JSON.parse(localStorage.getItem('userData')||'{}').token||'';}
function authH(){return{'Authorization':'Bearer '+getToken()};}
function logout(){if(confirm('Keluar?')){localStorage.removeItem('userData');window.location.href='../../login.html';}}
let pg=1,dTimer;
function debounce(){clearTimeout(dTimer);dTimer=setTimeout(()=>{pg=1;load();},400);}
function fmt(n){return'Rp '+parseFloat(n||0).toLocaleString('id-ID');}
const statusBadge={pending:'<span class="badge bg-secondary">Pending</span>',approved:'<span class="badge bg-info">Disetujui</span>',disbursed:'<span class="badge bg-primary">Cair</span>',active:'<span class="badge bg-success">Aktif</span>',completed:'<span class="badge bg-dark">Lunas</span>',overdue:'<span class="badge bg-warning text-dark">Telat</span>',npl:'<span class="badge bg-danger">NPL</span>'};

function loadTypes(){$.ajax({url:BASE+'/loans.php',method:'GET',data:{action:'get_loan_types'},headers:authH()}).done(res=>{if(res.success&&res.data)res.data.forEach(t=>$('#filterType').append(`<option value="${t.id}">${t.name}</option>`));});}

function loadPortfolio(){$.ajax({url:BASE+'/loans.php',method:'GET',data:{action:'get_loan_portfolio'},headers:authH()}).done(res=>{if(!res.success)return;const d=res.data;$('#kTotal').text(d.total_loans||'—');$('#kAmount').text(fmt(d.total_amount));$('#kOutstanding').text(fmt(d.total_outstanding));$('#kNpl').text(d.npl_count||0);});}

function load(){
    const params={action:'get_loans',page:pg,limit:15,search:$('#searchInput').val(),status:$('#filterStatus').val(),loan_type_id:$('#filterType').val()};
    $('#tableBody').html('<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Memuat...</td></tr>');
    $.ajax({url:BASE+'/loans.php',method:'GET',data:params,headers:authH()}).done(res=>{
        if(!res.success){$('#tableBody').html(`<tr><td colspan="8" class="text-center py-4 text-danger">${res.message}</td></tr>`);return;}
        const rows=res.data?.loans||res.data||[];
        if(!rows.length){$('#tableBody').html('<tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data pinjaman</td></tr>');return;}
        $('#tableBody').html(rows.map(l=>`<tr>
            <td><span class="badge bg-light text-dark border">${l.loan_number||l.id}</span></td>
            <td><strong>${l.member_name||l.full_name||'-'}</strong></td>
            <td><span class="badge bg-info bg-opacity-20 text-info">${l.loan_type||l.type_name||'-'}</span></td>
            <td>${fmt(l.amount||l.principal_amount)}</td>
            <td class="text-warning fw-semibold">${fmt(l.outstanding_balance||l.remaining_balance)}</td>
            <td class="text-muted small">${l.tenor||l.duration_months||'—'} bln</td>
            <td>${statusBadge[l.status]||`<span class="badge bg-secondary">${l.status}</span>`}</td>
            <td><button class="btn btn-sm btn-outline-primary py-0 px-2" onclick="showDetail(${l.id})"><i class="fas fa-eye"></i></button></td>
        </tr>`).join(''));
        const meta=res.data?.pagination||{};const tot=meta.total||rows.length;
        $('#pageInfo').text(`${tot} pinjaman`);renderPag(meta.total_pages||1);
    }).fail(()=>$('#tableBody').html('<tr><td colspan="8" class="text-center py-4 text-danger">Gagal memuat data</td></tr>'));
}
function renderPag(tot){if(tot<=1){$('#pagination').html('');return;}let h='';for(let i=1;i<=Math.min(tot,7);i++)h+=`<button class="btn btn-sm ${i===pg?'btn-primary':'btn-outline-secondary'}" onclick="goPg(${i})">${i}</button>`;$('#pagination').html(h);}
function goPg(p){pg=p;load();}
function showDetail(id){
    $('#detailBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i></div>');
    new bootstrap.Modal(document.getElementById('detailModal')).show();
    $.ajax({url:BASE+'/loans.php',method:'GET',data:{action:'get_loan',id},headers:authH()}).done(res=>{
        if(!res.success){$('#detailBody').html(`<div class="alert alert-danger">${res.message}</div>`);return;}
        const l=res.data;
        $('#detailBody').html(`<div class="row g-3"><div class="col-md-6"><table class="table table-sm table-borderless">
            <tr><td class="text-muted fw-semibold" width="45%">No. Pinjaman</td><td>${l.loan_number||l.id}</td></tr>
            <tr><td class="text-muted fw-semibold">Anggota</td><td>${l.member_name||l.full_name||'-'}</td></tr>
            <tr><td class="text-muted fw-semibold">Tipe</td><td>${l.loan_type||'-'}</td></tr>
            <tr><td class="text-muted fw-semibold">Pokok</td><td class="fw-bold">${fmt(l.amount||l.principal_amount)}</td></tr>
            <tr><td class="text-muted fw-semibold">Sisa</td><td class="text-warning fw-bold">${fmt(l.outstanding_balance||l.remaining_balance)}</td></tr>
        </table></div><div class="col-md-6"><table class="table table-sm table-borderless">
            <tr><td class="text-muted fw-semibold" width="45%">Bunga/bln</td><td>${l.interest_rate||'-'}%</td></tr>
            <tr><td class="text-muted fw-semibold">Tenor</td><td>${l.tenor||l.duration_months||'-'} bulan</td></tr>
            <tr><td class="text-muted fw-semibold">Cair</td><td>${(l.disbursement_date||l.created_at||'').substring(0,10)}</td></tr>
            <tr><td class="text-muted fw-semibold">Status</td><td>${statusBadge[l.status]||l.status}</td></tr>
            <tr><td class="text-muted fw-semibold">Tujuan</td><td>${l.purpose||'-'}</td></tr>
        </table></div></div>`);
    }).fail(()=>$('#detailBody').html('<div class="alert alert-danger">Gagal memuat detail</div>'));
}
$(function(){loadTypes();loadPortfolio();load();});
</script>
</body>
</html>
