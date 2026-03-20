// Enhanced User Management System
class EnhancedUserManagement {
    constructor() {
        this.users = [];
        this.roles = [];
        this.currentUser = null;
        this.isInitialized = false;
    }

    // Initialize user management
    async initialize() {
        await this.loadUserDashboard();
        await this.loadRoles();
        this.setupEventListeners();
        this.isInitialized = true;
    }

    // Load user dashboard
    async loadUserDashboard() {
        try {
            const response = await fetch('/api/enhanced-user-management.php?action=dashboard');
            const result = await response.json();
            
            if (result.success) {
                this.updateDashboardDisplay(result.data);
            }
        } catch (error) {
            console.error('Error loading user dashboard:', error);
        }
    }

    // Load roles
    async loadRoles() {
        try {
            const response = await fetch('/api/enhanced-user-management.php?action=role_permissions');
            const result = await response.json();
            
            if (result.success) {
                this.roles = result.data;
                this.updateRolesDisplay();
            }
        } catch (error) {
            console.error('Error loading roles:', error);
        }
    }

    // Update dashboard display
    updateDashboardDisplay(data) {
        const container = document.getElementById('user-management-dashboard');
        if (!container) return;

        container.innerHTML = `
            <div class="user-management-header">
                <h5>👥 Manajemen Pengguna</h5>
                <div class="user-status">
                    <span class="status-indicator active"></span>
                    <span>System Active</span>
                </div>
            </div>
            
            <div class="user-summary-grid">
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Total Pengguna</h6>
                        <h3>${data.user_summary.total_users}</h3>
                        <small>${data.user_summary.active_users} aktif</small>
                    </div>
                </div>
                
                <div class="summary-card owner">
                    <div class="summary-icon">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Pemilik Aplikasi</h6>
                        <h3>${data.user_summary.owner_users}</h3>
                        <small>Owner role</small>
                    </div>
                </div>
                
                <div class="summary-card admin">
                    <div class="summary-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Administrator</h6>
                        <h3>${data.user_summary.admin_users}</h3>
                        <small>Admin role</small>
                    </div>
                </div>
                
                <div class="summary-card staff">
                    <div class="summary-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Staff</h6>
                        <h3>${data.user_summary.staff_users}</h3>
                        <small>Staff role</small>
                    </div>
                </div>
                
                <div class="summary-card member">
                    <div class="summary-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Nasabah</h6>
                        <h3>${data.user_summary.member_users}</h3>
                        <small>Member role</small>
                    </div>
                </div>
                
                <div class="summary-card active">
                    <div class="summary-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Aktif Hari Ini</h6>
                        <h3>${data.user_summary.active_today}</h3>
                        <small>Login hari ini</small>
                    </div>
                </div>
            </div>
            
            <div class="quick-actions">
                <button class="btn btn-primary" onclick="userManagement.createUser()">
                    <i class="fas fa-plus"></i> Tambah User
                </button>
                <button class="btn btn-info" onclick="userManagement.viewUsers()">
                    <i class="fas fa-list"></i> Daftar User
                </button>
                <button class="btn btn-success" onclick="userManagement.viewRoles()">
                    <i class="fas fa-user-tag"></i> Role & Permission
                </button>
                <button class="btn btn-warning" onclick="userManagement.viewActivities()">
                    <i class="fas fa-history"></i> Aktivitas
                </button>
                <button class="btn btn-danger" onclick="userManagement.ownerDashboard()">
                    <i class="fas fa-crown"></i> Owner Dashboard
                </button>
            </div>
            
            <div class="recent-activities">
                <h6>📋 Aktivitas Terkini</h6>
                <div class="activities-list">
                    ${data.recent_activities.map(activity => `
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas ${this.getActivityIcon(activity.activity_type)}"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-header">
                                    <span class="activity-user">${activity.user_name}</span>
                                    <span class="activity-role">${activity.user_role}</span>
                                    <span class="activity-time">${new Date(activity.created_at).toLocaleString()}</span>
                                </div>
                                <div class="activity-description">
                                    <p>${activity.activity_description}</p>
                                    ${activity.module ? `<small>Module: ${activity.module}</small>` : ''}
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div class="role-distribution">
                <h6>📊 Distribusi Role</h6>
                <div class="distribution-chart">
                    ${data.role_distribution.map(role => `
                        <div class="role-item">
                            <div class="role-info">
                                <span class="role-name">${role.role}</span>
                                <span class="role-count">${role.count}</span>
                            </div>
                            <div class="role-bar">
                                <div class="role-progress" style="width: ${(role.count / data.user_summary.total_users) * 100}%"></div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    // Update roles display
    updateRolesDisplay() {
        const container = document.getElementById('roles-display');
        if (!container) return;

        container.innerHTML = `
            <div class="roles-header">
                <h6>👥 Role & Permission</h6>
            </div>
            
            <div class="roles-list">
                ${this.roles.map(role => `
                    <div class="role-item">
                        <div class="role-header">
                            <div class="role-info">
                                <h6>${role.role_name}</h6>
                                <p>${role.role_description}</p>
                            </div>
                            <div class="role-status">
                                <span class="badge bg-${role.is_active ? 'success' : 'secondary'}">${role.is_active ? 'Active' : 'Inactive'}</span>
                            </div>
                        </div>
                        <div class="role-permissions">
                            <h7>Permissions:</h7>
                            <div class="permissions-list">
                                ${Object.entries(JSON.parse(role.permissions || '{}')).map(([module, permissions]) => `
                                    <div class="permission-group">
                                        <span class="module-name">${module}:</span>
                                        <div class="permission-items">
                                            ${permissions.map(permission => `
                                                <span class="permission-item">${permission}</span>
                                            `).join(', ')}
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    // Get activity icon
    getActivityIcon(activityType) {
        const icons = {
            'user_created': 'fa-user-plus',
            'user_updated': 'fa-user-edit',
            'user_deleted': 'fa-user-minus',
            'user_login': 'fa-sign-in-alt',
            'user_logout': 'fa-sign-out-alt',
            'journal_created': 'fa-book',
            'journal_posted': 'fa-check-circle',
            'loan_created': 'fa-hand-holding-usd',
            'payment_made': 'fa-money-bill-wave',
            'system_access': 'fa-key',
            'settings_updated': 'fa-cog'
        };
        return icons[activityType] || 'fa-circle';
    }

    // Create user
    createUser() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah User Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="create-user-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="username">Username</label>
                                        <input type="text" class="form-control" id="username" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" class="form-control" id="password" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="confirm-password">Konfirmasi Password</label>
                                        <input type="password" class="form-control" id="confirm-password" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="role">Role</label>
                                        <select class="form-control" id="role" required>
                                            <option value="">Pilih Role</option>
                                            <option value="owner">Owner</option>
                                            <option value="admin">Admin</option>
                                            <option value="staff">Staff</option>
                                            <option value="member">Member</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Telepon</label>
                                        <input type="tel" class="form-control" id="phone">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="address">Alamat</label>
                                        <textarea class="form-control" id="address" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" onclick="userManagement.saveUser()">Simpan</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Save user
    async saveUser() {
        try {
            const formData = new FormData();
            formData.append('username', document.getElementById('username').value);
            formData.append('email', document.getElementById('email').value);
            formData.append('password', document.getElementById('password').value);
            formData.append('name', document.getElementById('name').value);
            formData.append('role', document.getElementById('role').value);
            formData.append('phone', document.getElementById('phone').value);
            formData.append('address', document.getElementById('address').value);
            formData.append('created_by', getCurrentUserId());

            const response = await fetch('/api/enhanced-user-management.php?action=create_user', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Success', 'User berhasil dibuat', 'success');
                this.loadUserDashboard();
                
                // Close modal
                const modal = document.querySelector('.modal.show');
                if (modal) {
                    bootstrap.Modal.getInstance(modal).hide();
                }
            } else {
                throw new Error(result.error || 'Failed to create user');
            }
        } catch (error) {
            console.error('Error saving user:', error);
            this.showNotification('Error', 'Gagal membuat user', 'error');
        }
    }

    // View users
    async viewUsers() {
        try {
            const response = await fetch('/api/enhanced-user-management.php?action=list_users');
            const result = await response.json();
            
            if (result.success) {
                this.showUsersModal(result.data);
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    // Show users modal
    showUsersModal(users) {
        const modal = document.createElement('div');
        modal.className = 'modal fade modal-lg';
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Daftar Pengguna</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="users-filter">
                            <div class="filter-controls">
                                <select class="form-select" id="role-filter" onchange="userManagement.filterUsers()">
                                    <option value="all">Semua Role</option>
                                    <option value="owner">Owner</option>
                                    <option value="admin">Admin</option>
                                    <option value="staff">Staff</option>
                                    <option value="member">Member</option>
                                </select>
                                <select class="form-select" id="status-filter" onchange="userManagement.filterUsers()">
                                    <option value="all">Semua Status</option>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak Aktif</option>
                                </select>
                                <input type="text" class="form-control" id="search-filter" placeholder="Cari user..." onkeyup="userManagement.filterUsers()">
                            </div>
                        </div>
                        
                        <div class="users-table">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Terakhir Login</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${users.map(user => `
                                        <tr>
                                            <td>${user.id}</td>
                                            <td>${user.username}</td>
                                            <td>${user.name}</td>
                                            <td>${user.email}</td>
                                            <td><span class="badge bg-${this.getRoleBadgeColor(user.role)}">${user.role}</span></td>
                                            <td><span class="badge bg-${user.is_active ? 'success' : 'secondary'}">${user.is_active ? 'Aktif' : 'Tidak Aktif'}</span></td>
                                            <td>${user.last_login ? new Date(user.last_login).toLocaleString() : 'Belum pernah login'}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="userManagement.viewUserDetails(${user.id})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning" onclick="userManagement.editUser(${user.id})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                ${user.role !== 'owner' ? `
                                                    <button class="btn btn-sm btn-outline-danger" onclick="userManagement.deleteUser(${user.id})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                ` : ''}
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" onclick="userManagement.exportUsers()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Get role badge color
    getRoleBadgeColor(role) {
        const colors = {
            'owner': 'danger',
            'admin': 'warning',
            'staff': 'info',
            'member' => 'secondary'
        };
        return colors[role] || 'secondary';
    }

    // View user details
    async viewUserDetails(userId) {
        try {
            const response = await fetch(`/api/enhanced-user-management.php?action=user_details&user_id=${userId}`);
            const result = await response.json();
            
            if (result.success) {
                this.showUserDetailsModal(result.data);
            }
        } catch (error) {
            console.error('Error loading user details:', error);
        }
    }

    // Show user details modal
    showUserDetailsModal(data) {
        const modal = document.createElement('div');
        modal.className = 'modal fade modal-lg';
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail User - ${data.user.name}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="user-details-tabs">
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#user-info">Info User</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#user-permissions">Permissions</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#user-activities">Aktivitas</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#user-sessions">Sessions</a>
                                </li>
                            </ul>
                            
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="user-info">
                                    <div class="user-info-section">
                                        <div class="user-avatar">
                                            <img src="${data.user.avatar || '/assets/images/default-avatar.png'}" alt="Avatar">
                                        </div>
                                        <div class="user-basic-info">
                                            <h6>${data.user.name}</h6>
                                            <p><strong>Username:</strong> ${data.user.username}</p>
                                            <p><strong>Email:</strong> ${data.user.email}</p>
                                            <p><strong>Role:</strong> <span class="badge bg-${this.getRoleBadgeColor(data.user.role)}">${data.user.role}</span></p>
                                            <p><strong>Status:</strong> <span class="badge bg-${data.user.is_active ? 'success' : 'secondary'}">${data.user.is_active ? 'Aktif' : 'Tidak Aktif'}</span></p>
                                            <p><strong>Telepon:</strong> ${data.user.phone || '-'}</p>
                                            <p><strong>Alamat:</strong> ${data.user.address || '-'}</p>
                                            <p><strong>Dibuat:</strong> ${new Date(data.user.created_at).toLocaleString()}</p>
                                            <p><strong>Terakhir Login:</strong> ${data.user.last_login ? new Date(data.user.last_login).toLocaleString() : 'Belum pernah login'}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="tab-pane fade" id="user-permissions">
                                    <div class="permissions-section">
                                        <h6>Role Permissions</h6>
                                        <div class="permissions-list">
                                            ${data.user.role_permissions ? Object.entries(JSON.parse(data.user.role_permissions)).map(([module, permissions]) => `
                                                <div class="permission-group">
                                                    <h7>${module}</h7>
                                                    <div class="permission-items">
                                                        ${permissions.map(permission => `
                                                            <span class="permission-item">${permission}</span>
                                                        `).join(', ')}
                                                    </div>
                                                </div>
                                            `).join('') : '<p>No permissions data available</p>'}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="tab-pane fade" id="user-activities">
                                    <div class="activities-section">
                                        <h6>Riwayat Aktivitas</h6>
                                        <div class="activities-timeline">
                                            ${data.activities.map(activity => `
                                                <div class="activity-item">
                                                    <div class="activity-icon">
                                                        <i class="fas ${this.getActivityIcon(activity.activity_type)}"></i>
                                                    </div>
                                                    <div class="activity-content">
                                                        <div class="activity-time">${new Date(activity.created_at).toLocaleString()}</div>
                                                        <div class="activity-type">${activity.activity_type}</div>
                                                        <div class="activity-description">${activity.activity_description}</div>
                                                        ${activity.module ? `<small>Module: ${activity.module}</small>` : ''}
                                                    </div>
                                                </div>
                                            `).join('')}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="tab-pane fade" id="user-sessions">
                                    <div class="sessions-section">
                                        <h6>Aktif Sessions</h6>
                                        <div class="sessions-list">
                                            ${data.sessions.map(session => `
                                                <div class="session-item">
                                                    <div class="session-info">
                                                        <p><strong>Device:</strong> ${session.device_info ? JSON.parse(session.device_info).device || 'Unknown' : 'Unknown'}</p>
                                                        <p><strong>IP Address:</strong> ${session.ip_address}</p>
                                                        <p><strong>User Agent:</strong> ${session.user_agent}</p>
                                                        <p><strong>Created:</strong> ${new Date(session.created_at).toLocaleString()}</p>
                                                        <p><strong>Expires:</strong> ${new Date(session.expires_at).toLocaleString()}</p>
                                                    </div>
                                                </div>
                                            `).join('')}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-warning" onclick="userManagement.editUser(${data.user.id})">
                            <i class="fas fa-edit"></i> Edit User
                        </button>
                        ${data.user.role !== 'owner' ? `
                            <button type="button" class="btn btn-danger" onclick="userManagement.deleteUser(${data.user.id})">
                                <i class="fas fa-trash"></i> Hapus User
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Owner dashboard
    async ownerDashboard() {
        try {
            const response = await fetch('/api/enhanced-user-management.php?action=owner_dashboard&owner_id=' + getCurrentUserId());
            const result = await response.json();
            
            if (result.success) {
                this.showOwnerDashboardModal(result.data);
            }
        } catch (error) {
            console.error('Error loading owner dashboard:', error);
        }
    }

    // Show owner dashboard modal
    showOwnerDashboardModal(data) {
        const modal = document.createElement('div');
        modal.className = 'modal fade modal-lg';
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">👑 Owner Dashboard</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="owner-welcome">
                            <h6>Selamat datang, ${data.owner.name}! 👑</h6>
                            <p>Anda adalah pemilik aplikasi ini dengan akses penuh ke seluruh sistem.</p>
                        </div>
                        
                        <div class="owner-stats">
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h6>Total Users</h6>
                                        <h3>${data.system_stats.total_users}</h3>
                                        <small>${data.system_stats.active_users} aktif</small>
                                    </div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h6>Aktif Minggu Ini</h6>
                                        <h3>${data.system_stats.active_this_week}</h3>
                                        <small>Login minggu ini</small>
                                    </div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h6>Admin</h6>
                                        <h3>${data.system_stats.admin_count}</h3>
                                        <small>Administrator</small>
                                    </div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h6>Staff</h6>
                                        <h3>${data.system_stats.staff_count}</h3>
                                        <small>Staff lapangan</small>
                                    </div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h6>Nasabah</h6>
                                        <h3>${data.system_stats.member_count}</h3>
                                        <small>Pengguna nasabah</small>
                                    </div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h6>Aktif Hari Ini</h6>
                                        <h3>${data.system_stats.active_today}</h3>
                                        <small>Login hari ini</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        ${data.tenant_info.total_tenants ? `
                            <div class="tenant-info">
                                <h6>🏢 Multi-Tenant Information</h6>
                                <div class="tenant-stats">
                                    <div class="tenant-item">
                                        <span class="tenant-label">Total Tenants:</span>
                                        <span class="tenant-value">${data.tenant_info.total_tenants}</span>
                                    </div>
                                    <div class="tenant-item">
                                        <span class="tenant-label">Active Tenants:</span>
                                        <span class="tenant-value">${data.tenant_info.active_tenants}</span>
                                    </div>
                                    <div class="tenant-item">
                                        <span class="tenant-label">Suspended Tenants:</span>
                                        <span class="tenant-value">${data.tenant_info.suspended_tenants}</span>
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                        
                        <div class="owner-actions">
                            <h6>🔧 Owner Actions</h6>
                            <div class="actions-grid">
                                <button class="btn btn-primary" onclick="userManagement.createUser()">
                                    <i class="fas fa-plus"></i> Tambah User
                                </button>
                                <button class="btn btn-info" onclick="userManagement.viewUsers()">
                                    <i class="fas fa-list"></i> Daftar User
                                </button>
                                <button class="btn btn-success" onclick="userManagement.systemOverview()">
                                    <i class="fas fa-chart-bar"></i> System Overview
                                </button>
                                <button class="btn btn-warning" onclick="userManagement.multiTenantSetup()">
                                    <i class="fas fa-building"></i> Multi-Tenant Setup
                                </button>
                                <button class="btn btn-danger" onclick="userManagement.systemSettings()">
                                    <i class="fas fa-cogs"></i> System Settings
                                </button>
                            </div>
                        </div>
                        
                        <div class="recent-activities">
                            <h6>📋 Aktivitas Terkini</h6>
                            <div class="activities-list">
                                ${data.recent_activities.map(activity => `
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fas ${this.getActivityIcon(activity.activity_type)}"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-header">
                                                <span class="activity-user">${activity.user_name}</span>
                                                <span class="activity-role">${activity.user_role}</span>
                                                <span class="activity-time">${new Date(activity.created_at).toLocaleString()}</span>
                                            </div>
                                            <div class="activity-description">
                                                <p>${activity.activity_description}</p>
                                                ${activity.module ? `<small>Module: ${activity.module}</small>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" onclick="userManagement.exportSystemData()">
                            <i class="fas fa-download"></i> Export System Data
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Setup event listeners
    setupEventListeners() {
        // Auto-refresh dashboard data
        setInterval(() => {
            this.loadUserDashboard();
        }, 60000); // Refresh every minute
    }

    // Show notification
    showNotification(title, message, type) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.innerHTML = `
            <strong>${title}:</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.getElementById('notifications');
        if (container) {
            container.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
    }
}

// Initialize user management when page loads
let userManagement = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('user-management-dashboard')) {
        userManagement = new EnhancedUserManagement();
        userManagement.initialize();
    }
});

// Helper function to get current user ID
function getCurrentUserId() {
    const currentUser = JSON.parse(localStorage.getItem('currentUser') || '{}');
    return currentUser.id || 1;
}
