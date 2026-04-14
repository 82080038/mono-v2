<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekening Simpanan - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        #mainContent{margin-left:0}
    </style>
</head>
<body>
<?php $activePage = 'savings'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div>
            <div style="font-weight:700;font-size:1.1rem;color:#1e293b">Rekening Simpanan</div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Simpanan</li>
            </ol></nav>
        </div>
        <div class="ms-auto"><a href="savings-management.php" class="btn btn-primary btn-sm"><i class="fas fa-exchange-alt me-1"></i>Transaksi Simpanan</a></div>
    </div>
    <div class="page-body">
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body d-flex align-items-center gap-3">
                    <div style="width:48px;height:48px;border-radius:12px;background:#dbeafe;display:flex;align-items:center;justify-content:center"><i class="fas fa-piggy-bank text-primary fs-5"></i></div>
                    <div><div class="text-muted small">Total Rekening</div><div class="fs-4 fw-bold" id="statTotal">—</div></div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body d-flex align-items-center gap-3">
                    <div style="width:48px;height:48px;border-radius:12px;background:#dcfce7;display:flex;align-items:center;justify-content:center"><i class="fas fa-coins text-success fs-5"></i></div>
                    <div><div class="text-muted small">Total Dana</div><div class="fs-5 fw-bold text-success" id="statFund">—</div></div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body d-flex align-items-center gap-3">
                    <div style="width:48px;height:48px;border-radius:12px;background:#dcfce7;display:flex;align-items:center;justify-content:center"><i class="fas fa-check-circle text-success fs-5"></i></div>
                    <div><div class="text-muted small">Aktif</div><div class="fs-4 fw-bold text-success" id="statActive">—</div></div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body d-flex align-items-center gap-3">
                    <div style="width:48px;height:48px;border-radius:12px;background:#fef9c3;display:flex;align-items:center;justify-content:center"><i class="fas fa-pause-circle text-warning fs-5"></i></div>
                    <div><div class="text-muted small">Dormant</div><div class="fs-4 fw-bold text-warning" id="statDormant">—</div></div>
                </div></div>
            </div>
        </div>
        <div class="card border-0 shadow-sm mb-3"><div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-4"><div class="input-group input-group-sm"><span class="input-group-text"><i class="fas fa-search"></i></span><input type="text" id="searchInput" class="form-control" placeholder="Cari nama anggota, no. rekening..." oninput="debounce()"></div></div>
                <div class="col-md-2"><select id="filterType" class="form-select form-select-sm" onchange="load()"><option value="">Semua Tipe</option></select></div>
                <div class="col-md-2"><select id="filterStatus" class="form-select form-select-sm" onchange="load()"><option value="">Semua Status</option><option value="active">Aktif</option><option value="dormant">Dormant</option><option value="closed">Ditutup</option></select></div>
            </div>
        </div></div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>No. Rekening</th><th>Anggota</th><th>Tipe Simpanan</th><th>Saldo</th><th>Tgl Buka</th><th>Status</th><th></th></tr></thead>
                    <tbody id="tableBody"><tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Memuat...</td></tr></tbody>
                </table>
            </div></div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <span class="text-muted small" id="pageInfo">—</span>
                <div id="pagination" class="d-flex gap-1"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="detailModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-piggy-bank me-2"></i>Detail Rekening</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
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

function loadTypes(){$.get(BASE+'/savings.php',{action:'get_account_types'}).done(res=>{if(res.success&&res.data)res.data.forEach(t=>$('#filterType').append(`<option value="${t.id}">${t.name}</option>`));});}

function load(){
    const params={action:'get_accounts',page:pg,limit:15,search:$('#searchInput').val(),account_type_id:$('#filterType').val(),status:$('#filterStatus').val()};
    $('#tableBody').html('<tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Memuat...</td></tr>');
    $.ajax({url:BASE+'/savings.php',method:'GET',data:params,headers:authH()}).done(res=>{
        if(!res.success){$('#tableBody').html(`<tr><td colspan="7" class="text-center py-4 text-danger">${res.message}</td></tr>`);return;}
        const rows=res.data?.accounts||res.data||[];
        if(!rows.length){$('#tableBody').html('<tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data rekening</td></tr>');return;}
        $('#tableBody').html(rows.map(a=>`<tr>
            <td><span class="badge bg-light text-dark border">${a.account_number||a.id}</span></td>
            <td><strong>${a.member_name||a.full_name||'-'}</strong></td>
            <td><span class="badge bg-info bg-opacity-20 text-info">${a.account_type||a.type_name||'-'}</span></td>
            <td class="fw-semibold text-success">${fmt(a.balance||a.current_balance)}</td>
            <td class="text-muted small">${(a.opening_date||a.created_at||'').substring(0,10)}</td>
            <td>${a.status==='active'||a.is_active?'<span class="badge bg-success">Aktif</span>':a.status==='dormant'?'<span class="badge bg-warning text-dark">Dormant</span>':'<span class="badge bg-secondary">Ditutup</span>'}</td>
            <td><button class="btn btn-sm btn-outline-primary py-0 px-2" onclick="showDetail(${a.id})"><i class="fas fa-eye"></i></button></td>
        </tr>`).join(''));
        const meta=res.data?.pagination||{};
        const tot=meta.total||rows.length;
        $('#pageInfo').text(`${tot} rekening`);
        $('#statTotal').text(tot);
        $('#statActive').text(rows.filter(a=>a.status==='active'||a.is_active).length);
        $('#statDormant').text(rows.filter(a=>a.status==='dormant').length);
        const totalFund=rows.reduce((s,a)=>s+parseFloat(a.balance||a.current_balance||0),0);
        $('#statFund').text(fmt(totalFund));
        renderPag(meta.total_pages||1);
    }).fail(()=>$('#tableBody').html('<tr><td colspan="7" class="text-center py-4 text-danger">Gagal memuat data</td></tr>'));
}

function renderPag(tot){if(tot<=1){$('#pagination').html('');return;}let h='';for(let i=1;i<=Math.min(tot,7);i++)h+=`<button class="btn btn-sm ${i===pg?'btn-primary':'btn-outline-secondary'}" onclick="goPg(${i})">${i}</button>`;$('#pagination').html(h);}
function goPg(p){pg=p;load();}

function showDetail(id){
    $('#detailBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i></div>');
    new bootstrap.Modal(document.getElementById('detailModal')).show();
    $.ajax({url:BASE+'/savings.php',method:'GET',data:{action:'get_account',id},headers:authH()}).done(res=>{
        if(!res.success){$('#detailBody').html(`<div class="alert alert-danger">${res.message}</div>`);return;}
        const a=res.data;
        const txns=(a.recent_transactions||[]).map(t=>`<tr><td>${(t.transaction_date||'').substring(0,10)}</td><td>${t.transaction_type}</td><td class="${t.transaction_type==='Deposit'?'text-success':'text-danger'}">${fmt(t.amount)}</td><td>${fmt(t.balance_after)}</td></tr>`).join('');
        $('#detailBody').html(`
            <div class="row g-3 mb-3">
                <div class="col-md-6"><table class="table table-sm table-borderless">
                    <tr><td class="text-muted fw-semibold" width="45%">No. Rekening</td><td>${a.account_number||a.id}</td></tr>
                    <tr><td class="text-muted fw-semibold">Anggota</td><td>${a.member_name||a.full_name||'-'}</td></tr>
                    <tr><td class="text-muted fw-semibold">Tipe</td><td>${a.account_type||'-'}</td></tr>
                    <tr><td class="text-muted fw-semibold">Saldo</td><td class="text-success fw-bold">${fmt(a.balance||a.current_balance)}</td></tr>
                </table></div>
                <div class="col-md-6"><table class="table table-sm table-borderless">
                    <tr><td class="text-muted fw-semibold" width="45%">Tgl Buka</td><td>${(a.opening_date||a.created_at||'').substring(0,10)}</td></tr>
                    <tr><td class="text-muted fw-semibold">Status</td><td>${a.status==='active'||a.is_active?'<span class="badge bg-success">Aktif</span>':'<span class="badge bg-secondary">Tidak Aktif</span>'}</td></tr>
                </table></div>
            </div>
            ${txns?`<h6 class="fw-semibold mb-2">Transaksi Terakhir</h6><div class="table-responsive"><table class="table table-sm"><thead class="table-light"><tr><th>Tanggal</th><th>Tipe</th><th>Jumlah</th><th>Saldo Akhir</th></tr></thead><tbody>${txns}</tbody></table></div>`:''}
        `);
    }).fail(()=>$('#detailBody').html('<div class="alert alert-danger">Gagal memuat detail</div>'));
}
$(function(){loadTypes();load();});
</script>
</body>
</html>
