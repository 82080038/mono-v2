<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Anggota - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
         #mainContent { margin-left: 0; } }
    </style>
</head>
<body>
<?php $activePage = 'members'; require __DIR__ . '/partials/sidebar.php'; ?>

<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="$('#sidebar').toggleClass('show')"><i class="fas fa-bars"></i></button>
        <div>
            <div style="font-weight:700;font-size:1.1rem;color:#1e293b">Daftar Anggota</div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Anggota</li>
            </ol></nav>
        </div>
        <div class="ms-auto">
            <button class="btn btn-primary btn-sm" onclick="showAddMemberModal()"><i class="fas fa-plus me-1"></i>Tambah Anggota</button>
        </div>
    </div>

    <div class="page-body">
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div style="width:48px;height:48px;border-radius:12px;background:#dbeafe;display:flex;align-items:center;justify-content:center">
                            <i class="fas fa-users text-primary fs-5"></i>
                        </div>
                        <div><div class="text-muted small">Total Anggota</div><div class="fs-4 fw-bold" id="statTotal">—</div></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div style="width:48px;height:48px;border-radius:12px;background:#dcfce7;display:flex;align-items:center;justify-content:center">
                            <i class="fas fa-user-check text-success fs-5"></i>
                        </div>
                        <div><div class="text-muted small">Aktif</div><div class="fs-4 fw-bold text-success" id="statActive">—</div></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div style="width:48px;height:48px;border-radius:12px;background:#fef9c3;display:flex;align-items:center;justify-content:center">
                            <i class="fas fa-user-clock text-warning fs-5"></i>
                        </div>
                        <div><div class="text-muted small">Baru (30 hari)</div><div class="fs-4 fw-bold text-warning" id="statNew">—</div></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div style="width:48px;height:48px;border-radius:12px;background:#fee2e2;display:flex;align-items:center;justify-content:center">
                            <i class="fas fa-user-times text-danger fs-5"></i>
                        </div>
                        <div><div class="text-muted small">Tidak Aktif</div><div class="fs-4 fw-bold text-danger" id="statInactive">—</div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari nama, NIK, no. anggota..." oninput="debounceLoad()">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select id="filterStatus" class="form-select form-select-sm" onchange="loadMembers()">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="filterType" class="form-select form-select-sm" onchange="loadMembers()">
                            <option value="">Semua Tipe</option>
                        </select>
                    </div>
                    <div class="col-md-auto ms-auto">
                        <span class="text-muted small" id="recordInfo"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="membersTable">
                        <thead class="table-light">
                            <tr>
                                <th>No. Anggota</th>
                                <th>Nama Lengkap</th>
                                <th>NIK</th>
                                <th>Tipe</th>
                                <th>Telepon</th>
                                <th>Terdaftar</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="membersBody">
                            <tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex align-items-center justify-content-between">
                <span class="text-muted small" id="pageInfo">—</span>
                <div id="pagination" class="d-flex gap-1"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Anggota -->
<div class="modal fade" id="memberDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user me-2"></i>Detail Anggota</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="memberDetailBody">
                <div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i></div>
            </div>
        </div>
    </div>
</div>

<div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index:9999"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="../../assets/js/config.js"></script>
<script src="../../assets/js/auth-fixed.js"></script>
<script>
(function(){
    const u=JSON.parse(localStorage.getItem('userData')||'{}');
    if(!u.token) { window.location.href='../../login.html'; return; }
    const r=(u.role||'').toLowerCase().replace(' ','_');
    if(!['admin','super_admin','manager'].includes(r)) { window.location.href='../../login.html'; return; }
    $('#sidebarUserName').text(u.name||'Admin');
    $('#sidebarUserRole').text(u.role||'');
})();

const BASE = (window.APP_CONFIG?.baseUrl||'') + '/api';
function getToken(){ return JSON.parse(localStorage.getItem('userData')||'{}').token||''; }
function authH(){ return { 'Authorization': 'Bearer '+getToken() }; }
function logout(){ if(confirm('Keluar?')){ localStorage.removeItem('userData'); window.location.href='../../login.html'; } }

let currentPage = 1;
let debounceTimer;

function debounceLoad(){
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(()=>{ currentPage=1; loadMembers(); }, 400);
}

function showToast(msg, type='success'){
    const id='t'+Date.now();
    $('#toastContainer').append(`<div id="${id}" class="toast align-items-center text-bg-${type} border-0 show"><div class="d-flex"><div class="toast-body">${msg}</div><button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`);
    setTimeout(()=>$('#'+id).remove(), 4000);
}

function loadMemberTypes(){
    $.get(BASE+'/members.php', { action:'get_member_types' }, null, 'json')
    .done(res => {
        if(res.success && res.data){
            const sel = $('#filterType');
            res.data.forEach(t => sel.append(`<option value="${t.id}">${t.name}</option>`));
        }
    });
}

function loadMembers(){
    const search = $('#searchInput').val();
    const status = $('#filterStatus').val();
    const memberType = $('#filterType').val();
    const params = { action:'get_members', page: currentPage, limit:15, search, status, member_type_id: memberType };

    $('#membersBody').html('<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Memuat...</td></tr>');

    $.ajax({ url: BASE+'/members.php', method:'GET', data: params, headers: authH() })
    .done(res => {
        if(!res.success){ $('#membersBody').html(`<tr><td colspan="8" class="text-center py-4 text-danger">${res.message}</td></tr>`); return; }
        const members = res.data?.members || res.data || [];
        const meta = res.data?.pagination || res.pagination || {};

        if(!members.length){ $('#membersBody').html('<tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data anggota</td></tr>'); return; }

        const rows = members.map(m => `
            <tr>
                <td><span class="badge bg-light text-dark border">${m.member_number||m.id}</span></td>
                <td><strong>${m.full_name||m.name||'-'}</strong><br><small class="text-muted">${m.email||''}</small></td>
                <td class="text-muted small">${m.nik||'-'}</td>
                <td><span class="badge bg-info bg-opacity-20 text-info">${m.member_type||m.type_name||'-'}</span></td>
                <td class="text-muted small">${m.phone||'-'}</td>
                <td class="text-muted small">${m.created_at ? m.created_at.substring(0,10) : '-'}</td>
                <td>${m.is_active||m.status==='active' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Tidak Aktif</span>'}</td>
                <td><button class="btn btn-sm btn-outline-primary py-0 px-2" onclick="showDetail(${m.id})"><i class="fas fa-eye"></i></button></td>
            </tr>`).join('');
        $('#membersBody').html(rows);

        const total = meta.total || members.length;
        const totalPages = meta.total_pages || meta.last_page || 1;
        const from = meta.from || ((currentPage-1)*15+1);
        const to = Math.min(meta.to || currentPage*15, total);
        $('#pageInfo').text(`Menampilkan ${from}–${to} dari ${total} anggota`);
        $('#recordInfo').text(`${total} data`);
        renderPagination(totalPages);
        loadStats(members, total);
    })
    .fail(()=>{ $('#membersBody').html('<tr><td colspan="8" class="text-center py-4 text-danger">Gagal memuat data</td></tr>'); });
}

function loadStats(members, total){
    $('#statTotal').text(total||members.length);
    const active = members.filter(m=>m.is_active||m.status==='active').length;
    $('#statActive').text(active);
    const thirtyDaysAgo = new Date(); thirtyDaysAgo.setDate(thirtyDaysAgo.getDate()-30);
    const newCount = members.filter(m=>m.created_at && new Date(m.created_at)>=thirtyDaysAgo).length;
    $('#statNew').text(newCount);
    $('#statInactive').text(members.length-active);
}

function renderPagination(totalPages){
    if(totalPages<=1){ $('#pagination').html(''); return; }
    let html='';
    for(let i=1;i<=Math.min(totalPages,7);i++){
        html+=`<button class="btn btn-sm ${i===currentPage?'btn-primary':'btn-outline-secondary'}" onclick="goPage(${i})">${i}</button>`;
    }
    $('#pagination').html(html);
}

function goPage(p){ currentPage=p; loadMembers(); }

function showDetail(id){
    $('#memberDetailBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i></div>');
    new bootstrap.Modal(document.getElementById('memberDetailModal')).show();
    $.ajax({ url: BASE+'/members.php', method:'GET', data:{ action:'get_member', id }, headers: authH() })
    .done(res=>{
        if(!res.success){ $('#memberDetailBody').html(`<div class="alert alert-danger">${res.message}</div>`); return; }
        const m = res.data;
        $('#memberDetailBody').html(`
            <div class="row g-3">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted fw-semibold" width="40%">No. Anggota</td><td>${m.member_number||m.id}</td></tr>
                        <tr><td class="text-muted fw-semibold">Nama</td><td>${m.full_name||m.name}</td></tr>
                        <tr><td class="text-muted fw-semibold">NIK</td><td>${m.nik||'-'}</td></tr>
                        <tr><td class="text-muted fw-semibold">Tipe</td><td>${m.member_type||'-'}</td></tr>
                        <tr><td class="text-muted fw-semibold">Email</td><td>${m.email||'-'}</td></tr>
                        <tr><td class="text-muted fw-semibold">Telepon</td><td>${m.phone||'-'}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted fw-semibold" width="40%">Alamat</td><td>${m.address||'-'}</td></tr>
                        <tr><td class="text-muted fw-semibold">Pekerjaan</td><td>${m.occupation||'-'}</td></tr>
                        <tr><td class="text-muted fw-semibold">Penghasilan</td><td>${m.monthly_income?'Rp '+parseFloat(m.monthly_income).toLocaleString('id-ID'):'-'}</td></tr>
                        <tr><td class="text-muted fw-semibold">Terdaftar</td><td>${m.created_at?m.created_at.substring(0,10):'-'}</td></tr>
                        <tr><td class="text-muted fw-semibold">Status</td><td>${m.is_active?'<span class="badge bg-success">Aktif</span>':'<span class="badge bg-secondary">Tidak Aktif</span>'}</td></tr>
                    </table>
                </div>
            </div>`);
    })
    .fail(()=>{ $('#memberDetailBody').html('<div class="alert alert-danger">Gagal memuat detail</div>'); });
}

function showAddMemberModal(){
    window.location.href = 'member-registration.php';
}

$(function(){
    loadMemberTypes();
    loadMembers();
});
</script>
</body>
</html>
