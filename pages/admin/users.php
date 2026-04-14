<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - KSP Lam Gabe Jaya</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
</head>
<body>
<?php $activePage = 'users'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">Manajemen Pengguna</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Manajemen User</li></ol></nav></div>
        <div class="ms-auto"><button class="btn btn-sm btn-outline-primary" onclick="loadUsers()"><i class="fas fa-sync-alt me-1"></i>Refresh</button></div>
    </div>
<div class="page-body">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <i class="fas fa-users-cog me-2"></i>
                    <h5 class="mb-0 d-inline">Manajemen Pengguna</h5>
                </div>
                <button class="btn btn-primary btn-sm" onclick="loadUsers()">
                    <i class="fas fa-sync-alt me-1"></i>Refresh
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Role Utama</th>
                                <th>Role Tambahan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="usersTable">
                            <tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div></div>
    <!-- Modal Manajemen Role -->
    <div class="modal fade" id="roleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manajemen Role: <span id="modalUserName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modalUserId">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Role Utama</label>
                        <p class="text-muted" id="modalMainRole">-</p>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Role Tambahan</label>
                        <div id="additionalRolesList" class="mb-2"></div>
                        
                        <div class="input-group">
                            <select class="form-select" id="roleSelect">
                                <option value="">Pilih role...</option>
                            </select>
                            <button class="btn btn-success" onclick="assignRole()">
                                <i class="fas fa-plus me-1"></i>Tambah
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/config.js"></script>
    <script src="../../assets/js/auth-fixed.js"></script>
    <script>
        let currentUser = null;
        let roleModal = null;

        (function(){
            const u=JSON.parse(localStorage.getItem('userData')||'{}');
            const r=(u.role||'').toLowerCase().replace(' ','_');
            if(!u.token||!['admin','super_admin','manager'].includes(r)){window.location.href='../../login.html';return;}
            $('#sidebarUserName').text(u.name||'Admin');
            $('#sidebarUserRole').text(u.role||'');
        })();

        function getToken(){ return JSON.parse(localStorage.getItem('userData')||'{}').token||''; }
        function authH() { return { 'Authorization': 'Bearer '+getToken() }; }
        function logout(){ if(confirm('Keluar?')){localStorage.removeItem('userData');window.location.href='../../login.html';} }

        $(document).ready(function() {
            currentUser = JSON.parse(localStorage.getItem('userData')||'{}');
            roleModal = new bootstrap.Modal(document.getElementById('roleModal'));
            loadUsers();
            loadAvailableRoles();
        });

        function loadUsers() {
            $('#usersTable').html('<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>');
            
            $.ajax({
                url: API_BASE + '/user-roles.php?action=list_users_with_roles',
                method: 'GET',
                headers: authH()
            }).done(function(res) {
                if (res.success) {
                    renderUsers(res.data);
                } else {
                    $('#usersTable').html('<tr><td colspan="7" class="text-center text-danger py-4">' + res.message + '</td></tr>');
                }
            }).fail(function() {
                $('#usersTable').html('<tr><td colspan="7" class="text-center text-danger py-4">Gagal memuat data</td></tr>');
            });
        }

        function renderUsers(users) {
            if (!users || users.length === 0) {
                $('#usersTable').html('<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data pengguna</td></tr>');
                return;
            }

            let html = '';
            users.forEach(u => {
                const additional = u.additional_roles || '-';
                const statusBadge = u.is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>';
                
                html += `
                    <tr>
                        <td>${u.id}</td>
                        <td>${u.username}</td>
                        <td>${u.full_name}</td>
                        <td><span class="badge bg-primary">${u.main_role}</span></td>
                        <td><small class="text-muted">${additional}</small></td>
                        <td>${statusBadge}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="openRoleModal(${u.id}, '${u.full_name}', '${u.main_role}')">
                                <i class="fas fa-user-tag me-1"></i>Role
                            </button>
                        </td>
                    </tr>
                `;
            });
            $('#usersTable').html(html);
        }

        function loadAvailableRoles() {
            $.ajax({
                url: API_BASE + '/user-roles.php?action=list_roles',
                method: 'GET',
                headers: authH()
            }).done(function(res) {
                if (res.success) {
                    let options = '<option value="">Pilih role...</option>';
                    res.data.forEach(r => {
                        if (r.is_active) {
                            options += `<option value="${r.id}">${r.role_name} (${r.category})</option>`;
                        }
                    });
                    $('#roleSelect').html(options);
                }
            });
        }

        function openRoleModal(userId, userName, mainRole) {
            $('#modalUserId').val(userId);
            $('#modalUserName').text(userName);
            $('#modalMainRole').text(mainRole);
            
            loadUserRoles(userId);
            roleModal.show();
        }

        function loadUserRoles(userId) {
            $('#additionalRolesList').html('<div class="spinner-border spinner-border-sm text-primary"></div>');
            
            $.ajax({
                url: API_BASE + '/user-roles.php?action=get_user_roles&user_id=' + userId,
                method: 'GET',
                headers: authH()
            }).done(function(res) {
                if (res.success) {
                    renderAdditionalRoles(res.data.additional_roles);
                }
            });
        }

        function renderAdditionalRoles(roles) {
            if (!roles || roles.length === 0) {
                $('#additionalRolesList').html('<p class="text-muted mb-0">Tidak ada role tambahan</p>');
                return;
            }

            let html = '<div class="d-flex flex-wrap gap-2">';
            roles.forEach(r => {
                html += `
                    <span class="badge bg-info text-dark">
                        ${r.role_name}
                        <button class="btn-close btn-close-white ms-1" style="font-size: 0.6rem;" onclick="removeRole(${r.id})"></button>
                    </span>
                `;
            });
            html += '</div>';
            $('#additionalRolesList').html(html);
        }

        function assignRole() {
            const userId = $('#modalUserId').val();
            const roleId = $('#roleSelect').val();

            if (!roleId) {
                alert('Pilih role terlebih dahulu');
                return;
            }

            $.ajax({
                url: API_BASE + '/user-roles.php?action=assign_role',
                method: 'POST',
                headers: authH(),
                contentType: 'application/json',
                data: JSON.stringify({ user_id: userId, role_id: roleId })
            }).done(function(res) {
                if (res.success) {
                    alert('Role berhasil ditambahkan');
                    loadUserRoles(userId);
                    loadUsers();
                } else {
                    alert(res.message);
                }
            }).fail(function() {
                alert('Gagal menambahkan role');
            });
        }

        function removeRole(assignmentId) {
            if (!confirm('Hapus role ini?')) return;

            const userId = $('#modalUserId').val();
            
            // Need to get role_id from the assignment
            $.ajax({
                url: API_BASE + '/user-roles.php?action=remove_role',
                method: 'POST',
                headers: authH(),
                contentType: 'application/json',
                data: JSON.stringify({ user_id: userId, role_id: assignmentId })
            }).done(function(res) {
                if (res.success) {
                    loadUserRoles(userId);
                    loadUsers();
                } else {
                    alert(res.message);
                }
            }).fail(function() {
                alert('Gagal menghapus role');
            });
        }
    </script>
</body>
</html>
