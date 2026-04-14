<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        #mainContent{margin-left:0}
        .page-header{background:linear-gradient(135deg,#1a1a2e,#16213e);color:#fff;padding:1.5rem;border-radius:12px;margin-bottom:1.5rem;}
        .action-badge-CREATE{background:#198754;} .action-badge-UPDATE{background:#0d6efd;}
        .action-badge-DELETE{background:#dc3545;} .action-badge-APPROVE{background:#20c997;}
        .action-badge-REJECT{background:#fd7e14;} .action-badge-LOGIN{background:#6f42c1;}
        .action-badge-LOGOUT{background:#6c757d;} .action-badge-EXPORT{background:#0dcaf0;color:#000;}
        .json-preview{font-size:.75rem;max-height:120px;overflow-y:auto;background:#f8f9fa;padding:.5rem;border-radius:4px;}
    </style>
</head>
<body>
<?php $activePage = 'audit-log'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">Audit Trail</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Audit Trail</li></ol></nav></div>
        <div class="ms-auto"><button class="btn btn-sm btn-outline-secondary" onclick="exportLogs()"><i class="fas fa-file-csv me-1"></i>Export CSV</button></div>
    </div>
<div class="page-body">

    <!-- Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-2"><label class="form-label small">Dari Tanggal</label><input type="date" id="fDateFrom" class="form-control form-control-sm"></div>
                <div class="col-md-2"><label class="form-label small">Sampai Tanggal</label><input type="date" id="fDateTo" class="form-control form-control-sm"></div>
                <div class="col-md-2"><label class="form-label small">Action</label>
                    <select id="fAction" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option>CREATE</option><option>UPDATE</option><option>DELETE</option>
                        <option>APPROVE</option><option>REJECT</option><option>LOGIN</option>
                        <option>LOGOUT</option><option>EXPORT</option>
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label small">Tabel</label><input type="text" id="fTable" class="form-control form-control-sm" placeholder="mis: loans"></div>
                <div class="col-md-2 d-flex align-items-end"><button class="btn btn-sm btn-primary w-100" onclick="loadLogs()"><i class="fas fa-search me-1"></i>Cari</button></div>
                <div class="col-md-2 d-flex align-items-end"><button class="btn btn-sm btn-outline-secondary w-100" onclick="exportLogs()"><i class="fas fa-file-csv me-1"></i>Export CSV</button></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between">
            <h6 class="mb-0"><i class="fas fa-list me-2 text-secondary"></i>Log Aktivitas</h6>
            <span id="logCount" class="badge bg-secondary">0 record</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead><tr><th>Waktu</th><th>User</th><th>Action</th><th>Tabel</th><th>Record ID</th><th>IP</th><th>Keterangan</th><th></th></tr></thead>
                    <tbody id="logBody"><tr><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Memuat...</td></tr></tbody>
                </table>
            </div>
            <div id="logPagination" class="d-flex justify-content-between align-items-center px-3 py-2 border-top"></div>
        </div>
    </div>
</div>

<!-- Modal: Detail Log -->
<div class="modal fade" id="logDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Detail Audit Log</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="logDetailBody"></div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>
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
(function(){
    const u=JSON.parse(localStorage.getItem('userData')||'{}');
    const r=(u.role||'').toLowerCase().replace(' ', '_');
    if(!u.token||!['admin','super_admin','manager'].includes(r)) window.location.href='../../login.html';
    $('#sidebarUserName').text(u.name||'Admin');
    $('#sidebarUserRole').text(u.role||'');
})();

const BASE=(window.APP_CONFIG?.baseUrl||'')+'/api';
let currentPage=1, totalPages=1;

function getToken(){ return JSON.parse(localStorage.getItem('userData')||'{}').token||''; }
function authH()   { return { 'Authorization': 'Bearer '+getToken() }; }
function logout()  { if(confirm('Keluar?')){localStorage.removeItem('userData');window.location.href='../../login.html';} }
function showToast(msg,type='success'){
    const id='t'+Date.now();
    $('#toastContainer').append(`<div id="${id}" class="toast align-items-center text-bg-${type} border-0 show"><div class="d-flex"><div class="toast-body">${msg}</div><button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`);
    setTimeout(()=>$('#'+id).remove(),4000);
}
function apiGet(ep,params){ return $.ajax({url:BASE+ep,method:'GET',data:params,headers:authH()}); }

function loadLogs(page=1) {
    currentPage=page;
    const params={
        action:'get_audit_logs',
        date_from:$('#fDateFrom').val(), date_to:$('#fDateTo').val(),
        action_filter:$('#fAction').val(), table_name:$('#fTable').val(), page
    };
    apiGet('/audit.php',params).done(function(data){
        if(!data.success){showToast(data.message,'danger');return;}
        const {logs,total,pages}=data.data;
        totalPages=pages||1;
        $('#logCount').text(total+' record');
        if(!logs.length){
            $('#logBody').html('<tr><td colspan="8" class="text-center text-muted py-4">Tidak ada log pada filter ini</td></tr>');
            renderPagination(0,0,0); return;
        }
        $('#logBody').html(logs.map(l=>{
            const bc='action-badge-'+(l.action||'');
            const t=new Date(l.created_at).toLocaleString('id-ID',{day:'2-digit',month:'2-digit',year:'2-digit',hour:'2-digit',minute:'2-digit'});
            return `<tr>
                <td><small>${t}</small></td>
                <td><small>${l.user_name||'—'}</small></td>
                <td><span class="badge ${bc}">${l.action}</span></td>
                <td><code class="small">${l.table_name}</code></td>
                <td>${l.record_id||'—'}</td>
                <td><small class="text-muted">${l.ip_address||'—'}</small></td>
                <td><small>${(l.description||'').substring(0,60)}${(l.description||'').length>60?'...':''}</small></td>
                <td><button class="btn btn-sm btn-outline-info py-0" onclick='viewLog(${JSON.stringify(l)})'><i class="fas fa-eye"></i></button></td>
            </tr>`;
        }).join(''));
        renderPagination(page,totalPages,total);
    }).fail(()=>showToast('Gagal memuat log','danger'));
}

function renderPagination(page,pages,total){
    $('#logPagination').html(`<small class="text-muted">Hal ${page} dari ${pages} (${total} record)</small>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary" onclick="loadLogs(${page-1})" ${page<=1?'disabled':''}>‹ Prev</button>
            <button class="btn btn-outline-secondary" onclick="loadLogs(${page+1})" ${page>=pages?'disabled':''}>Next ›</button>
        </div>`);
}

function viewLog(log){
    let oldHtml='—', newHtml='—';
    try{if(log.old_values) oldHtml=`<pre class="json-preview">${JSON.stringify(JSON.parse(log.old_values),null,2)}</pre>`;}catch{}
    try{if(log.new_values) newHtml=`<pre class="json-preview">${JSON.stringify(JSON.parse(log.new_values),null,2)}</pre>`;}catch{}
    $('#logDetailBody').html(`
        <dl class="row">
            <dt class="col-4">Waktu</dt><dd class="col-8">${new Date(log.created_at).toLocaleString('id-ID')}</dd>
            <dt class="col-4">User</dt><dd class="col-8">${log.user_id||'—'} ${log.user_name?'('+log.user_name+')':''}</dd>
            <dt class="col-4">Action</dt><dd class="col-8"><span class="badge action-badge-${log.action}">${log.action}</span></dd>
            <dt class="col-4">Tabel</dt><dd class="col-8"><code>${log.table_name}</code></dd>
            <dt class="col-4">Record ID</dt><dd class="col-8">${log.record_id||'—'}</dd>
            <dt class="col-4">IP Address</dt><dd class="col-8">${log.ip_address||'—'}</dd>
            <dt class="col-4">User Agent</dt><dd class="col-8"><small class="text-muted">${(log.user_agent||'').substring(0,80)}</small></dd>
            <dt class="col-4">Keterangan</dt><dd class="col-8">${log.description||'—'}</dd>
            <dt class="col-4">Nilai Sebelum</dt><dd class="col-8">${oldHtml}</dd>
            <dt class="col-4">Nilai Sesudah</dt><dd class="col-8">${newHtml}</dd>
        </dl>`);
    new bootstrap.Modal(document.getElementById('logDetailModal')).show();
}

function exportLogs(){
    window.open(BASE+'/reports.php?format=csv&report=audit_logs&date_from='+$('#fDateFrom').val()+'&date_to='+$('#fDateTo').val()+'&token='+getToken(),'_blank');
}

$(document).ready(function(){
    const today=new Date().toISOString().slice(0,10);
    const weekAgo=new Date(Date.now()-7*864e5).toISOString().slice(0,10);
    $('#fDateFrom').val(weekAgo); $('#fDateTo').val(today);
    loadLogs();
});
</script>
</body>
</html>
