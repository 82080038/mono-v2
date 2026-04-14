<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Berjenjang - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        #mainContent{margin-left:0}
        .step-badge{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem}
        .step-line{width:3px;background:#e5e7eb;margin:4px auto;height:32px}
    </style>
</head>
<body>
<?php $activePage = 'verifikasi'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">Verifikasi Berjenjang Pinjaman</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Verifikasi</li></ol></nav></div>
        <div class="ms-auto"><a href="approval-workflow.php" class="btn btn-primary btn-sm"><i class="fas fa-tasks me-1"></i>Approval Workflow</a></div>
    </div>
    <div class="page-body">
        <!-- Alur Verifikasi -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="fas fa-route me-2 text-primary"></i>Alur Verifikasi Pinjaman</h6>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="text-center"><div class="step-badge bg-primary text-white mx-auto">1</div><div class="small mt-1">Pengajuan</div></div>
                    <i class="fas fa-arrow-right text-muted"></i>
                    <div class="text-center"><div class="step-badge bg-info text-white mx-auto">2</div><div class="small mt-1">Survei</div></div>
                    <i class="fas fa-arrow-right text-muted"></i>
                    <div class="text-center"><div class="step-badge bg-warning text-dark mx-auto">3</div><div class="small mt-1">Analisa</div></div>
                    <i class="fas fa-arrow-right text-muted"></i>
                    <div class="text-center"><div class="step-badge bg-success text-white mx-auto">4</div><div class="small mt-1">Approve</div></div>
                    <i class="fas fa-arrow-right text-muted"></i>
                    <div class="text-center"><div class="step-badge bg-dark text-white mx-auto">5</div><div class="small mt-1">Pencairan</div></div>
                </div>
            </div>
        </div>
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Menunggu Survei</div><div class="fs-2 fw-bold text-info" id="kSurvei">—</div></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Dalam Analisa</div><div class="fs-2 fw-bold text-warning" id="kAnalisa">—</div></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Menunggu Approve</div><div class="fs-2 fw-bold text-primary" id="kApprove">—</div></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Siap Cair</div><div class="fs-2 fw-bold text-success" id="kCair">—</div></div></div></div>
        </div>
        <!-- Filter + Tabel -->
        <div class="card border-0 shadow-sm mb-3"><div class="card-body py-2">
            <div class="row g-2"><div class="col-md-4"><div class="input-group input-group-sm"><span class="input-group-text"><i class="fas fa-search"></i></span><input type="text" id="searchInput" class="form-control" placeholder="Cari nama anggota..." oninput="debounce()"></div></div>
            <div class="col-md-3"><select id="filterStage" class="form-select form-select-sm" onchange="load()"><option value="pending">Semua Tahap Pending</option><option value="pending">Pengajuan Baru</option><option value="approved">Disetujui</option><option value="disbursed">Cair</option></select></div></div>
        </div></div>
        <div class="card border-0 shadow-sm"><div class="card-body p-0"><div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>No. Pinjaman</th><th>Anggota</th><th>Jumlah</th><th>Tanggal Ajuan</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody id="tableBody"><tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Memuat...</td></tr></tbody>
            </table>
        </div></div></div>
    </div>
</div>
<!-- Modal Tindak -->
<div class="modal fade" id="actionModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title" id="actionTitle">Tindak Pinjaman</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label fw-semibold">Catatan / Keterangan</label><textarea class="form-control" id="actionNotes" rows="3" placeholder="Tuliskan catatan verifikasi..."></textarea></div>
        <input type="hidden" id="actionLoanId">
        <input type="hidden" id="actionType">
    </div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary" id="actionConfirmBtn" onclick="submitAction()">Konfirmasi</button></div>
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
const sBadge={pending:'<span class="badge bg-secondary">Pengajuan</span>',approved:'<span class="badge bg-info">Disetujui</span>',disbursed:'<span class="badge bg-primary">Cair</span>',active:'<span class="badge bg-success">Aktif</span>'};

function loadSummary(){
    $.ajax({url:BASE+'/loans.php',method:'GET',data:{action:'get_loans',status:'pending',limit:100},headers:authH()}).done(res=>{
        const rows=res.data?.loans||res.data||[];
        $('#kSurvei').text(rows.filter(l=>!l.survey_date).length);
        $('#kAnalisa').text(rows.filter(l=>l.survey_date&&!l.analysis_date).length);
        $('#kApprove').text(rows.filter(l=>l.analysis_date).length);
    });
    $.ajax({url:BASE+'/loans.php',method:'GET',data:{action:'get_loans',status:'approved',limit:100},headers:authH()}).done(res=>{
        $('#kCair').text((res.data?.loans||res.data||[]).length);
    });
}

function load(){
    const status=$('#filterStage').val()||'pending';
    const params={action:'get_loans',page:pg,limit:15,search:$('#searchInput').val(),status};
    $('#tableBody').html('<tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Memuat...</td></tr>');
    $.ajax({url:BASE+'/loans.php',method:'GET',data:params,headers:authH()}).done(res=>{
        if(!res.success){$('#tableBody').html(`<tr><td colspan="6" class="text-center py-4 text-danger">${res.message}</td></tr>`);return;}
        const rows=res.data?.loans||res.data||[];
        if(!rows.length){$('#tableBody').html('<tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada pengajuan</td></tr>');return;}
        $('#tableBody').html(rows.map(l=>`<tr>
            <td><span class="badge bg-light text-dark border">${l.loan_number||l.id}</span></td>
            <td><strong>${l.member_name||l.full_name||'-'}</strong></td>
            <td>${fmt(l.amount||l.principal_amount)}</td>
            <td class="text-muted small">${(l.created_at||'').substring(0,10)}</td>
            <td>${sBadge[l.status]||`<span class="badge bg-secondary">${l.status}</span>`}</td>
            <td>
                ${l.status==='pending'?`<button class="btn btn-sm btn-success py-0 me-1" onclick="openAction(${l.id},'approve')"><i class="fas fa-check"></i> Approve</button><button class="btn btn-sm btn-danger py-0" onclick="openAction(${l.id},'reject')"><i class="fas fa-times"></i> Tolak</button>`:''}
                ${l.status==='approved'?`<button class="btn btn-sm btn-primary py-0" onclick="openAction(${l.id},'disburse')"><i class="fas fa-money-bill-wave"></i> Cairkan</button>`:''}
            </td>
        </tr>`).join(''));
    }).fail(()=>$('#tableBody').html('<tr><td colspan="6" class="text-center py-4 text-danger">Gagal memuat data</td></tr>'));
}

function openAction(id,type){
    $('#actionLoanId').val(id);$('#actionType').val(type);
    const labels={approve:'Setujui Pinjaman',reject:'Tolak Pinjaman',disburse:'Cairkan Pinjaman'};
    $('#actionTitle').text(labels[type]||'Aksi');
    $('#actionConfirmBtn').removeClass('btn-primary btn-success btn-danger').addClass(type==='reject'?'btn-danger':type==='disburse'?'btn-info':'btn-success');
    new bootstrap.Modal(document.getElementById('actionModal')).show();
}

function submitAction(){
    const id=$('#actionLoanId').val();const type=$('#actionType').val();const notes=$('#actionNotes').val();
    const action=type==='approve'?'approve_loan':type==='reject'?'reject_loan':'disburse_loan';
    $.ajax({url:BASE+'/loans.php',method:'POST',data:{action,loan_id:id,notes},headers:authH()}).done(res=>{
        bootstrap.Modal.getInstance(document.getElementById('actionModal')).hide();
        if(res.success){load();loadSummary();}
        else alert('Gagal: '+(res.message||''));
    }).fail(()=>alert('Koneksi gagal'));
}
$(function(){loadSummary();load();});
</script>
</body>
</html>
