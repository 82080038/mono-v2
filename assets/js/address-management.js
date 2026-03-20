// Address Management System
class AddressManagement {
    constructor() {
        this.provinces = [];
        this.regencies = [];
        this.districts = [];
        this.villages = [];
        this.userAddresses = [];
        this.isInitialized = false;
    }

    // Initialize address management
    async initialize() {
        await this.loadAddressDashboard();
        await this.loadProvinces();
        this.setupEventListeners();
        this.isInitialized = true;
    }

    // Load address dashboard
    async loadAddressDashboard() {
        try {
            const response = await fetch('/api/address-management.php?action=dashboard');
            const result = await response.json();
            
            if (result.success) {
                this.updateDashboardDisplay(result.data);
            }
        } catch (error) {
            console.error('Error loading address dashboard:', error);
        }
    }

    // Load provinces
    async loadProvinces() {
        try {
            const response = await fetch('/api/address-management.php?action=get_provinces');
            const result = await response.json();
            
            if (result.success) {
                this.provinces = result.data;
                this.updateProvinceSelect();
            }
        } catch (error) {
            console.error('Error loading provinces:', error);
        }
    }

    // Update dashboard display
    updateDashboardDisplay(data) {
        const container = document.getElementById('address-dashboard');
        if (!container) return;

        container.innerHTML = `
            <div class="address-header">
                <h5>📍 Manajemen Alamat</h5>
                <div class="address-status">
                    <span class="status-indicator active"></span>
                    <span>Multi-Database Active</span>
                </div>
            </div>
            
            <div class="address-summary-grid">
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-map"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Database Alamat</h6>
                        <h3>${data.alamat_database_stats.provinces} Provinsi</h3>
                        <small>${data.alamat_database_stats.regencies} Kabupaten/Kota</small>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Wilayah Lengkap</h6>
                        <h3>${data.alamat_database_stats.districts} Kecamatan</h3>
                        <small>${data.alamat_database_stats.villages} Desa/Kelurahan</small>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Alamat Pengguna</h6>
                        <h3>${data.user_address_stats.total_user_addresses}</h3>
                        <small>${data.user_address_stats.primary_addresses} alamat utama</small>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Tipe Alamat</h6>
                        <h3>${data.user_address_stats.home_addresses}</h3>
                        <small>${data.user_address_stats.business_addresses} bisnis</small>
                    </div>
                </div>
            </div>
            
            <div class="quick-actions">
                <button class="btn btn-primary" onclick="addressManagement.addUserAddress()">
                    <i class="fas fa-plus"></i> Tambah Alamat
                </button>
                <button class="btn btn-info" onclick="addressManagement.searchAddress()">
                    <i class="fas fa-search"></i> Cari Alamat
                </button>
                <button class="btn btn-success" onclick="addressManagement.viewUserAddresses()">
                    <i class="fas fa-list"></i> Daftar Alamat
                </button>
                <button class="btn btn-warning" onclick="addressManagement.importAddresses()">
                    <i class="fas fa-upload"></i> Import Alamat
                </button>
            </div>
            
            <div class="address-features">
                <h6>🌍 Fitur Multi-Database</h6>
                <div class="features-grid">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="feature-content">
                            <h6>Database Terpisah</h6>
                            <p>Alamat tersimpan di database terpisah untuk optimasi</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-search-location"></i>
                        </div>
                        <div class="feature-content">
                            <h6>Pencarian Cepat</h6>
                            <p>Pencarian alamat lengkap di semua tingkatan</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div class="feature-content">
                            <h6>Validasi Hierarki</h6>
                            <p>Validasi hubungan provinsi-kabupaten-kecamatan-desa</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <div class="feature-content">
                            <h6>Multiple Alamat</h6>
                            <p>Setiap user bisa memiliki beberapa alamat</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Update province select
    updateProvinceSelect() {
        const selects = document.querySelectorAll('.province-select');
        selects.forEach(select => {
            select.innerHTML = '<option value="">Pilih Provinsi</option>' + 
                this.provinces.map(province => 
                    `<option value="${province.id}">${province.name}</option>`
                ).join('');
        });
    }

    // Add user address
    addUserAddress() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Alamat</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="add-address-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="user-select">Pengguna</label>
                                        <select class="form-select" id="user-select" required>
                                            <option value="">Pilih Pengguna</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="address-type">Tipe Alamat</label>
                                        <select class="form-select" id="address-type" required>
                                            <option value="">Pilih Tipe</option>
                                            <option value="home">Rumah</option>
                                            <option value="business">Bisnis</option>
                                            <option value="mailing">Surat Menyurat</option>
                                            <option value="other">Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="province-select">Provinsi</label>
                                        <select class="form-select province-select" id="province-select" required onchange="addressManagement.loadRegencies(this.value)">
                                            <option value="">Pilih Provinsi</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="regency-select">Kabupaten/Kota</label>
                                        <select class="form-select regency-select" id="regency-select" required onchange="addressManagement.loadDistricts(this.value)">
                                            <option value="">Pilih Kabupaten/Kota</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="district-select">Kecamatan</label>
                                        <select class="form-select district-select" id="district-select" required onchange="addressManagement.loadVillages(this.value)">
                                            <option value="">Pilih Kecamatan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="village-select">Desa/Kelurahan</label>
                                        <select class="form-select village-select" id="village-select" required>
                                            <option value="">Pilih Desa/Kelurahan</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="street-address">Alamat Lengkap</label>
                                        <textarea class="form-control" id="street-address" rows="2" placeholder="Jalan, RT/RW, No. Rumah"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="postal-code">Kode Pos</label>
                                        <input type="text" class="form-control" id="postal-code" placeholder="12345">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is-primary">
                                    <label class="form-check-label" for="is-primary">
                                        Jadikan sebagai alamat utama
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" onclick="addressManagement.saveAddress()">Simpan</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Load users
        this.loadUsers();

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Load users for select
    async loadUsers() {
        try {
            const response = await fetch('/api/enhanced-user-management.php?action=list_users&limit=1000');
            const result = await response.json();
            
            if (result.success) {
                const userSelect = document.getElementById('user-select');
                if (userSelect) {
                    userSelect.innerHTML = '<option value="">Pilih Pengguna</option>' + 
                        result.data.map(user => 
                            `<option value="${user.id}">${user.name} (${user.username})</option>`
                        ).join('');
                }
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    // Load regencies based on province
    async loadRegencies(provinceId) {
        if (!provinceId) {
            document.getElementById('regency-select').innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
            document.getElementById('district-select').innerHTML = '<option value="">Pilih Kecamatan</option>';
            document.getElementById('village-select').innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
            return;
        }

        try {
            const response = await fetch(`/api/address-management.php?action=get_regencies&province_id=${provinceId}`);
            const result = await response.json();
            
            if (result.success) {
                const regencySelect = document.getElementById('regency-select');
                regencySelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>' + 
                    result.data.map(regency => 
                        `<option value="${regency.id}">${regency.name}</option>`
                    ).join('');
            }
        } catch (error) {
            console.error('Error loading regencies:', error);
        }
    }

    // Load districts based on regency
    async loadDistricts(regencyId) {
        if (!regencyId) {
            document.getElementById('district-select').innerHTML = '<option value="">Pilih Kecamatan</option>';
            document.getElementById('village-select').innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
            return;
        }

        try {
            const response = await fetch(`/api/address-management.php?action=get_districts&regency_id=${regencyId}`);
            const result = await response.json();
            
            if (result.success) {
                const districtSelect = document.getElementById('district-select');
                districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>' + 
                    result.data.map(district => 
                        `<option value="${district.id}">${district.name}</option>`
                    ).join('');
            }
        } catch (error) {
            console.error('Error loading districts:', error);
        }
    }

    // Load villages based on district
    async loadVillages(districtId) {
        if (!districtId) {
            document.getElementById('village-select').innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
            return;
        }

        try {
            const response = await fetch(`/api/address-management.php?action=get_villages&district_id=${districtId}`);
            const result = await response.json();
            
            if (result.success) {
                const villageSelect = document.getElementById('village-select');
                villageSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>' + 
                    result.data.map(village => 
                        `<option value="${village.id}">${village.name}</option>`
                    ).join('');
            }
        } catch (error) {
            console.error('Error loading villages:', error);
        }
    }

    // Save address
    async saveAddress() {
        try {
            const formData = new FormData();
            formData.append('user_id', document.getElementById('user-select').value);
            formData.append('address_type', document.getElementById('address-type').value);
            formData.append('province_id', document.getElementById('province-select').value);
            formData.append('regency_id', document.getElementById('regency-select').value);
            formData.append('district_id', document.getElementById('district-select').value);
            formData.append('village_id', document.getElementById('village-select').value);
            formData.append('street_address', document.getElementById('street-address').value);
            formData.append('postal_code', document.getElementById('postal-code').value);
            formData.append('is_primary', document.getElementById('is-primary').checked);

            const response = await fetch('/api/address-management.php?action=save_user_address', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Success', 'Alamat berhasil disimpan', 'success');
                this.loadAddressDashboard();
                
                // Close modal
                const modal = document.querySelector('.modal.show');
                if (modal) {
                    bootstrap.Modal.getInstance(modal).hide();
                }
            } else {
                throw new Error(result.error || 'Failed to save address');
            }
        } catch (error) {
            console.error('Error saving address:', error);
            this.showNotification('Error', 'Gagal menyimpan alamat', 'error');
        }
    }

    // Search address
    searchAddress() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cari Alamat</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="search-form">
                            <div class="form-group">
                                <label for="search-query">Cari Alamat</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search-query" placeholder="Masukkan nama provinsi, kabupaten, kecamatan, atau desa...">
                                    <button class="btn btn-primary" onclick="addressManagement.performSearch()">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="search-results" id="search-results">
                            <div class="text-center text-muted">
                                <i class="fas fa-search fa-3x mb-3"></i>
                                <p>Masukkan kata kunci untuk mencari alamat</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Setup search on enter
        document.getElementById('search-query').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.performSearch();
            }
        });

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Perform search
    async performSearch() {
        const query = document.getElementById('search-query').value.trim();
        
        if (!query) {
            this.showNotification('Warning', 'Masukkan kata kunci pencarian', 'warning');
            return;
        }

        try {
            const response = await fetch(`/api/address-management.php?action=search_address&query=${encodeURIComponent(query)}`);
            const result = await response.json();
            
            if (result.success) {
                this.displaySearchResults(result.data);
            }
        } catch (error) {
            console.error('Error searching address:', error);
            this.showNotification('Error', 'Gagal mencari alamat', 'error');
        }
    }

    // Display search results
    displaySearchResults(results) {
        const resultsContainer = document.getElementById('search-results');
        
        if (results.length === 0) {
            resultsContainer.innerHTML = `
                <div class="text-center text-muted">
                    <i class="fas fa-search fa-2x mb-3"></i>
                    <p>Tidak ada hasil yang ditemukan</p>
                </div>
            `;
            return;
        }

        resultsContainer.innerHTML = `
            <div class="results-list">
                ${results.map(result => `
                    <div class="result-item" onclick="addressManagement.selectAddress(${JSON.stringify(result).replace(/"/g, '&quot;')})">
                        <div class="result-level">
                            <span class="badge bg-${this.getLevelBadgeColor(result.level)}">${result.level}</span>
                        </div>
                        <div class="result-content">
                            <div class="result-name">${result.name}</div>
                            <div class="result-code">Kode: ${result.code}</div>
                        </div>
                        <div class="result-action">
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-check"></i> Pilih
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    // Get level badge color
    getLevelBadgeColor(level) {
        const colors = {
            'province': 'danger',
            'regency': 'warning',
            'district': 'info',
            'village': 'success'
        };
        return colors[level] || 'secondary';
    }

    // Select address from search results
    selectAddress(address) {
        // This would populate the address form fields
        console.log('Selected address:', address);
        this.showNotification('Info', `Alamat dipilih: ${address.name}`, 'info');
    }

    // View user addresses
    async viewUserAddresses() {
        try {
            const response = await fetch('/api/enhanced-user-management.php?action=list_users&limit=1000');
            const result = await response.json();
            
            if (result.success) {
                this.showUserAddressesModal(result.data);
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    // Show user addresses modal
    showUserAddressesModal(users) {
        const modal = document.createElement('div');
        modal.className = 'modal fade modal-lg';
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Alamat Pengguna</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="user-filter">
                            <div class="filter-controls">
                                <select class="form-select" id="user-address-select" onchange="addressManagement.loadUserAddresses(this.value)">
                                    <option value="">Pilih Pengguna</option>
                                    ${users.map(user => 
                                        `<option value="${user.id}">${user.name} (${user.username})</option>`
                                    ).join('')}
                                </select>
                                <button class="btn btn-primary" onclick="addressManagement.addUserAddress()">
                                    <i class="fas fa-plus"></i> Tambah Alamat
                                </button>
                            </div>
                        </div>
                        
                        <div class="user-addresses-list" id="user-addresses-list">
                            <div class="text-center text-muted">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <p>Pilih pengguna untuk melihat alamatnya</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" onclick="addressManagement.exportAddresses()">
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

    // Load user addresses
    async loadUserAddresses(userId) {
        if (!userId) {
            document.getElementById('user-addresses-list').innerHTML = `
                <div class="text-center text-muted">
                    <i class="fas fa-users fa-3x mb-3"></i>
                    <p>Pilih pengguna untuk melihat alamatnya</p>
                </div>
            `;
            return;
        }

        try {
            const response = await fetch(`/api/address-management.php?action=get_user_addresses&user_id=${userId}`);
            const result = await response.json();
            
            if (result.success) {
                this.displayUserAddresses(result.data);
            }
        } catch (error) {
            console.error('Error loading user addresses:', error);
        }
    }

    // Display user addresses
    displayUserAddresses(addresses) {
        const container = document.getElementById('user-addresses-list');
        
        if (addresses.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted">
                    <i class="fas fa-map-marker-alt fa-2x mb-3"></i>
                    <p>Pengguna ini belum memiliki alamat</p>
                </div>
            `;
            return;
        }

        container.innerHTML = `
            <div class="addresses-list">
                ${addresses.map(address => `
                    <div class="address-item ${address.is_primary ? 'primary' : ''}">
                        <div class="address-header">
                            <div class="address-info">
                                <span class="address-type">${address.address_type}</span>
                                ${address.is_primary ? '<span class="badge bg-warning">Utama</span>' : ''}
                            </div>
                            <div class="address-actions">
                                <button class="btn btn-sm btn-outline-primary" onclick="addressManagement.editAddress(${address.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="addressManagement.deleteAddress(${address.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="address-content">
                            <p><strong>Alamat Lengkap:</strong> ${address.street_address || '-'}</p>
                            <p><strong>Provinsi:</strong> ${address.province_name}</p>
                            <p><strong>Kabupaten/Kota:</strong> ${address.regency_name}</p>
                            <p><strong>Kecamatan:</strong> ${address.district_name}</p>
                            <p><strong>Desa/Kelurahan:</strong> ${address.village_name}</p>
                            <p><strong>Kode Pos:</strong> ${address.postal_code || '-'}</p>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    // Setup event listeners
    setupEventListeners() {
        // Auto-refresh dashboard data
        setInterval(() => {
            this.loadAddressDashboard();
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

// Initialize address management when page loads
let addressManagement = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('address-dashboard')) {
        addressManagement = new AddressManagement();
        addressManagement.initialize();
    }
});
