<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Role & Akses - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        #mainContent{margin-left:0}
    </style>
</head>
<body>
<?php $activePage = 'role-access'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">Role &amp; Akses</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Role &amp; Akses</li></ol></nav></div>
    </div>
<div class="page-body">

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Daftar Role</h6>
                    <button class="btn btn-sm btn-primary" onclick="showAddRoleModal()">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="card-body p-2">
                    <div class="list-group list-group-flush" id="roleList">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0" id="roleDetailTitle">Pilih role untuk melihat detail</h6>
                </div>
                <div class="card-body" id="roleDetailContent">
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-user-shield fa-3x mb-3"></i>
                        <p>Pilih role dari daftar sebelah kiri untuk melihat dan mengedit permission</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Permission Matrix</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Permission</th>
                                    <th>Super Admin</th>
                                    <th>Admin</th>
                                    <th>Manager</th>
                                    <th>Teller</th>
                                    <th>Staff</th>
                                </tr>
                            </thead>
                            <tbody id="permissionMatrix">
                                <tr><td colspan="6" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add/Edit Role -->
<div class="modal fade" id="roleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleModalTitle">Tambah Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="roleForm">
                    <input type="hidden" id="roleId">
                    <div class="mb-3">
                        <label class="form-label">Role Code</label>
                        <input type="text" class="form-control" id="roleCode" placeholder="contoh: super_admin">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="roleName" placeholder="contoh: Super Administrator">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" id="roleCategory">
                            <option value="management">Management</option>
                            <option value="staff">Staff</option>
                            <option value="field">Field</option>
                            <option value="member">Member</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="roleDescription" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveRole()">Simpan</button>
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
(function(){
    const u=JSON.parse(localStorage.getItem('userData')||'{}');
    const r=(u.role||'').toLowerCase().replace(' ', '_');
    if(!u.token||r!=='super_admin') window.location.href='../../login.html';
    $('#sidebarUserName').text(u.name||'Admin');
    $('#sidebarUserRole').text(u.role||'');
})();

const BASE=(window.APP_CONFIG?.baseUrl||'')+'/api';
let currentRoleId = null;
let roleModal = null;

function getToken(){ return JSON.parse(localStorage.getItem('userData')||'{}').token||''; }
function authH(){ return { 'Authorization': 'Bearer '+getToken() }; }
function logout(){ if(confirm('Keluar?')){localStorage.removeItem('userData');window.location.href='../../login.html';} }

function showToast(msg,type='success'){
    const id='t'+Date.now();
    $('#toastContainer').append(`<div id="${id}" class="toast align-items-center text-bg-${type} border-0 show"><div class="d-flex"><div class="toast-body">${msg}</div><button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`);
    setTimeout(()=>$('#'+id).remove(),4000);
}

$(document).ready(function(){
    roleModal = new bootstrap.Modal(document.getElementById('roleModal'));
    loadRoles();
    loadPermissionMatrix();
});

function loadRoles(){
    $.ajax({
        url: BASE+'/user-roles.php?action=list_roles',
        method: 'GET',
        headers: authH()
    }).done(function(res){
        if(res.success){
            renderRoles(res.data);
        }
    }).fail(function(){
        showToast('Gagal memuat role','danger');
    });
}

function renderRoles(roles){
    let html = '';
    roles.forEach(r => {
        const colorClass = r.role_code === 'super_admin' ? 'danger' : 
                          r.role_code === 'admin' ? 'warning' : 
                          r.role_code === 'manager' ? 'info' : 'primary';
        html += `
            <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="selectRole(${r.id}, '${r.role_code}')">
                <div>
                    <span class="badge bg-${colorClass} me-2">${r.role_name}</span>
                    <small class="text-muted">${r.category}</small>
                </div>
                <i class="fas fa-chevron-right text-muted"></i>
            </button>
        `;
    });
    $('#roleList').html(html);
}

function selectRole(roleId, roleCode){
    currentRoleId = roleId;
    $('#roleDetailTitle').text('Detail Role: ' + roleCode);
    
    // Load role details and permissions
    loadRolePermissions(roleId);
}

function loadRolePermissions(roleId){
    $('#roleDetailContent').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>');
    
    // This would call an API to get role-specific permissions
    setTimeout(() => {
        $('#roleDetailContent').html(`
            <div class="mb-3">
                <label class="form-label fw-bold">Permissions</label>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-success">users</span>
                    <span class="badge bg-success">members</span>
                    <span class="badge bg-success">loans</span>
                    <span class="badge bg-success">reports</span>
                    <span class="badge bg-success">settings</span>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Menu Access</label>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i>Dashboard</li>
                    <li><i class="fas fa-check text-success me-2"></i>Manajemen Anggota</li>
                    <li><i class="fas fa-check text-success me-2"></i>Manajemen Pinjaman</li>
                    <li><i class="fas fa-check text-success me-2"></i>Laporan</li>
                </ul>
            </div>
            <button class="btn btn-sm btn-outline-primary" onclick="editRole(${roleId})">
                <i class="fas fa-edit me-1"></i>Edit Role
            </button>
        `);
    }, 300);
}

function loadPermissionMatrix(){
    setTimeout(() => {
        const permissions = [
            {name: 'Users', super: true, admin: true, manager: false, teller: false, staff: false},
            {name: 'Members', super: true, admin: true, manager: true, teller: true, staff: true},
            {name: 'Loans', super: true, admin: true, manager: true, teller: true, staff: false},
            {name: 'Reports', super: true, admin: true, manager: true, teller: false, staff: false},
            {name: 'Settings', super: true, admin: true, manager: true, teller: false, staff: false},
            {name: 'Approvals', super: true, admin: false, manager: true, teller: false, staff: false}
        ];
        
        let html = '';
        permissions.forEach(p => {
            html += `
                <tr>
                    <td>${p.name}</td>
                    <td>${p.super ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'}</td>
                    <td>${p.admin ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'}</td>
                    <td>${p.manager ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'}</td>
                    <td>${p.teller ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'}</td>
                    <td>${p.staff ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'}</td>
                </tr>
            `;
        });
        $('#permissionMatrix').html(html);
    }, 300);
}

function showAddRoleModal(){
    $('#roleModalTitle').text('Tambah Role');
    $('#roleForm')[0].reset();
    $('#roleId').val('');
    roleModal.show();
}

function editRole(roleId){
    $('#roleModalTitle').text('Edit Role');
    $('#roleId').val(roleId);
    // Load role data into form
    roleModal.show();
}

function saveRole(){
    showToast('Role berhasil disimpan','success');
    roleModal.hide();
    loadRoles();
}
</script>
</body>
</html>
