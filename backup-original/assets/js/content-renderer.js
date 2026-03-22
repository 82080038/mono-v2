/**
 * SPA-like Content Renderer for Admin Dashboard
 * Renders different pages without page reload
 */
class ContentRenderer {
    constructor() {
        this.currentContent = 'dashboard';
        this.init();
    }

    init() {
        // Override navigation link clicks
        document.addEventListener('DOMContentLoaded', () => {
            this.setupNavigation();
            this.loadInitialContent();
        });
    }

    setupNavigation() {
        // Intercept navigation clicks
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const href = link.getAttribute('href');
                if (href && href !== '#') {
                    this.loadContent(href);
                    this.updateActiveNav(link);
                }
            });
        });
    }

    updateActiveNav(activeLink) {
        // Remove active class from all nav links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        // Add active class to clicked link
        activeLink.classList.add('active');
    }

    async loadContent(page) {
        try {
            // Show loading
            this.showLoading();
            
            // Get content based on page
            const content = await this.getPageContent(page);
            
            // Render content
            this.renderContent(content);
            
            // Update current content
            this.currentContent = page;
            
            // Update page title
            this.updatePageTitle(page);
            
            // Initialize page-specific functions
            this.initializePage(page);
            
        } catch (error) {
            console.error('Error loading content:', error);
            this.showError('Gagal memuat halaman');
        }
    }

    async getPageContent(page) {
        const contentMap = {
            'dashboard.html': this.getDashboardContent(),
            'members.html': this.getMembersContent(),
            'loans.html': this.getLoansContent(),
            'savings.html': this.getSavingsContent(),
            'transactions.html': this.getTransactionsContent(),
            'reports.html': this.getReportsContent(),
            'npl.html': this.getNPLContent(),
            'risk-fraud.html': this.getRiskFraudContent(),
            'settings.html': this.getSettingsContent(),
            'role-access.html': this.getRoleAccessContent(),
            'guarantee-management.html': this.getGuaranteeManagementContent(),
            'database-management.html': this.getDatabaseManagementContent()
        };

        const pageName = page.split('/').pop() || page;
        return contentMap[pageName] || this.getNotFoundContent();
    }

    renderContent(content) {
        const container = document.getElementById('dashboard-content');
        if (container) {
            container.innerHTML = content;
            this.initializePage(this.currentContent);
        } else {
            console.error('Dashboard content container not found');
        }
    }

    showLoading() {
        const dashboardContent = document.getElementById('dashboard-content');
        if (dashboardContent) {
            dashboardContent.innerHTML = `
                <div class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Loading content...</p>
                    </div>
                </div>
            `;
        }
    }

    showError(message) {
        const dashboardContent = document.getElementById('dashboard-content');
        if (dashboardContent) {
            dashboardContent.innerHTML = `
                <div class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
                    <div class="text-center">
                        <div class="text-danger mb-3">
                            <i class="fas fa-exclamation-triangle fa-3x"></i>
                        </div>
                        <h5>Error</h5>
                        <p class="text-muted">${message}</p>
                        <button class="btn btn-primary" onclick="location.reload()">Refresh</button>
                    </div>
                </div>
            `;
        }
    }

    updatePageTitle(page) {
        const titleMap = {
            'dashboard.html': 'Dashboard Admin',
            'members.html': 'Manajemen Anggota',
            'loans.html': 'Manajemen Pinjaman',
            'savings.html': 'Manajemen Simpanan',
            'transactions.html': 'Transaksi',
            'reports.html': 'Laporan',
            'npl.html': 'Manajemen NPL',
            'risk-fraud.html': 'Risiko & Fraud',
            'settings.html': 'Pengaturan',
            'role-access.html': 'Role & Akses',
            'guarantee-management.html': 'Manajemen Penjaminan',
            'database-management.html': 'Administrasi Database'
        };

        const pageName = page.split('/').pop() || page;
        const title = titleMap[pageName] || 'Admin Dashboard';
        
        // Update header title
        const headerTitle = document.querySelector('.navbar-brand');
        if (headerTitle) {
            headerTitle.textContent = title;
        }
    }

    initializePage(page) {
        const pageName = page.split('/').pop() || page;
        
        try {
            // Initialize page-specific functions
            switch(pageName) {
                case 'dashboard.html':
                    // Dashboard doesn't need special initialization
                    break;
                case 'members.html':
                    this.initializeMembersPage();
                    break;
                case 'loans.html':
                    this.initializeLoansPage();
                    break;
                case 'savings.html':
                    this.initializeSavingsPage();
                    break;
                case 'transactions.html':
                    this.initializeTransactionsPage();
                    break;
                case 'reports.html':
                    this.initializeReportsPage();
                    break;
                case 'npl.html':
                    this.initializeNPLPage();
                    break;
                case 'risk-fraud.html':
                    this.initializeRiskFraudPage();
                    break;
                case 'settings.html':
                    this.initializeSettingsPage();
                    break;
                case 'role-access.html':
                    this.initializeRoleAccessPage();
                    break;
                case 'guarantee-management.html':
                    this.initializeGuaranteeManagementPage();
                    break;
                case 'database-management.html':
                    this.initializeDatabaseManagementPage();
                    break;
                default:
                    console.log(`No specific initializer for page: ${pageName}`);
                    break;
            }
        } catch (error) {
            console.error(`Error initializing page ${pageName}:`, error);
        }
    }

    // Content generators
    getDashboardContent() {
        return `
            <div id="dashboard-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard Admin</h2>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt me-1"></i>
                            Refresh
                        </button>
                        <button class="btn btn-primary btn-sm" data-action="add-member">
                            <i class="fas fa-plus me-1"></i>
                            New Member
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-1"></i>
                                Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" data-action="generate-report" data-report-type="pdf"><i class="fas fa-file-pdf me-2"></i>PDF Report</a></li>
                                <li><a class="dropdown-item" data-action="generate-report" data-report-type="excel"><i class="fas fa-file-excel me-2"></i>Excel Data</a></li>
                                <li><a class="dropdown-item" data-action="generate-report" data-report-type="csv"><i class="fas fa-file-csv me-2"></i>CSV Export</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="stat-card card border-0 shadow-sm h-100" id="card-members">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                            <i class="fas fa-users text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0 text-muted">Total Anggota</h6>
                                        <div class="h4 mb-0" id="totalMembers">150</div>
                                        <small class="text-success"><i class="fas fa-arrow-up"></i> 12%</small>
                                    </div>
                                </div>
                                <div class="mini-chart mt-3" style="height: 60px;">
                                    <canvas id="card-members-chart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="stat-card card border-0 shadow-sm h-100" id="card-loans">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                            <i class="fas fa-hand-holding-usd text-success"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0 text-muted">Pinjaman Aktif</h6>
                                        <div class="h4 mb-0" id="activeLoans">45</div>
                                        <small class="text-success"><i class="fas fa-arrow-up"></i> 8%</small>
                                    </div>
                                </div>
                                <div class="mini-chart mt-3" style="height: 60px;">
                                    <canvas id="card-loans-chart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="stat-card card border-0 shadow-sm h-100" id="card-savings">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                            <i class="fas fa-piggy-bank text-info"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0 text-muted">Simpanan</h6>
                                        <div class="h4 mb-0" id="totalSavings">Rp 250Jt</div>
                                        <small class="text-success"><i class="fas fa-arrow-up"></i> 15%</small>
                                    </div>
                                </div>
                                <div class="mini-chart mt-3" style="height: 60px;">
                                    <canvas id="card-savings-chart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="stat-card card border-0 shadow-sm h-100" id="card-guarantees">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                            <i class="fas fa-shield-alt text-warning"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0 text-muted">Penjaminan</h6>
                                        <div class="h4 mb-0" id="totalGuarantees">12</div>
                                        <small class="text-warning"><i class="fas fa-minus"></i> 0%</small>
                                    </div>
                                </div>
                                <div class="mini-chart mt-3" style="height: 60px;">
                                    <canvas id="card-guarantees-chart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="stat-card card border-0 shadow-sm h-100" id="card-risk">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                                            <i class="fas fa-exclamation-triangle text-danger"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0 text-muted">Risiko</h6>
                                        <div class="h4 mb-0" id="riskCount">3</div>
                                        <small class="text-danger"><i class="fas fa-arrow-up"></i> 2</small>
                                    </div>
                                </div>
                                <div class="mini-chart mt-3" style="height: 60px;">
                                    <canvas id="card-risk-chart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="stat-card card border-0 shadow-sm h-100" id="card-database">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-secondary bg-opacity-10 rounded-3 p-3">
                                            <i class="fas fa-database text-secondary"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0 text-muted">Database</h6>
                                        <div class="h4 mb-0" id="dbStatus">3 DB</div>
                                        <small class="text-success"><i class="fas fa-check-circle"></i> Online</small>
                                    </div>
                                </div>
                                <div class="mini-chart mt-3" style="height: 60px;">
                                    <canvas id="card-database-chart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Charts Section -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Grafik Performa Bulanan</h6>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary active">Minggu</button>
                                        <button type="button" class="btn btn-outline-primary">Bulan</button>
                                        <button type="button" class="btn btn-outline-primary">Tahun</button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="performanceChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0">
                                <h6 class="mb-0">Distribusi Pinjaman</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="loanDistributionChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Progress Bars -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0">
                                <h6 class="mb-0">Target Bulanan</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold">Pencairan Pinjaman</span>
                                        <span class="badge bg-primary">75%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                             role="progressbar" style="width: 75%" 
                                             aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">Rp 375M dari Rp 500M target</small>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold">Pengumpulan Simpanan</span>
                                        <span class="badge bg-success">60%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: 60%">
                                        </div>
                                    </div>
                                    <small class="text-muted">Rp 150M dari Rp 250M target</small>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold">Menunggu Verifikasi</span>
                                        <span class="badge bg-warning">25%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-warning" style="width: 25%">
                                        </div>
                                    </div>
                                    <small class="text-muted">25 aplikasi menunggu</small>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold">Tunggakan</span>
                                        <span class="badge bg-danger">15%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-danger" style="width: 15%">
                                        </div>
                                    </div>
                                    <small class="text-muted">Rp 45M tunggakan</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0">
                                <h6 class="mb-0">Kinerja Staff</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold">Target Kunjungan</span>
                                        <span class="badge bg-info">85%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-info" style="width: 85%">
                                        </div>
                                    </div>
                                    <small class="text-muted">85 dari 100 kunjungan</small>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold">Target Transaksi</span>
                                        <span class="badge bg-success">92%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: 92%">
                                        </div>
                                    </div>
                                    <small class="text-muted">184 dari 200 transaksi</small>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold">Target Penagihan</span>
                                        <span class="badge bg-warning">78%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-warning" style="width: 78%">
                                        </div>
                                    </div>
                                    <small class="text-muted">Rp 39M dari Rp 50M</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Tables Section -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Anggota Terbaru</h6>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-plus me-1"></i>Tambah
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="recentMembersTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nama</th>
                                                <th>Status</th>
                                                <th>Bergabung</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                            <small>JD</small>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">John Doe</div>
                                                            <small class="text-muted">john@example.com</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-success">Aktif</span></td>
                                                <td>2024-01-15</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary me-1" data-action="view-member" data-member-id="001" data-member-name="John Doe">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning me-1" data-action="edit-member" data-member-id="001" data-member-name="John Doe">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" data-action="delete-member" data-member-id="001" data-member-name="John Doe">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                            <small>JS</small>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">Jane Smith</div>
                                                            <small class="text-muted">jane@example.com</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-warning">Menunggu</span></td>
                                                <td>2024-01-18</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary me-1" data-action="view-member" data-member-id="001" data-member-name="John Doe">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning me-1" data-action="edit-member" data-member-id="001" data-member-name="John Doe">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" data-action="delete-member" data-member-id="001" data-member-name="John Doe">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                            <small>RJ</small>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">Robert Johnson</div>
                                                            <small class="text-muted">robert@example.com</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-success">Aktif</span></td>
                                                <td>2024-01-20</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary me-1" data-action="view-member" data-member-id="001" data-member-name="John Doe">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning me-1" data-action="edit-member" data-member-id="001" data-member-name="John Doe">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" data-action="delete-member" data-member-id="001" data-member-name="John Doe">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Aktivitas Terkini</h6>
                                    <button class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Anggota Baru Terdaftar</h6>
                                            <p class="text-muted mb-1">John Doe bergabung sebagai anggota baru</p>
                                            <small class="text-muted">2 menit yang lalu</small>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-primary"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Pinjaman Disetujui</h6>
                                            <p class="text-muted mb-1">Pinjaman Rp 50M disetujui untuk Jane Smith</p>
                                            <small class="text-muted">15 menit yang lalu</small>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-warning"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Pembayaran Terlambat</h6>
                                            <p class="text-muted mb-1">3 pinjaman memiliki pembayaran terlambat</p>
                                            <small class="text-muted">1 jam yang lalu</small>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Laporan Bulanan Selesai</h6>
                                            <p class="text-muted mb-1">Laporan bulan Januari 2024 selesai dibuat</p>
                                            <small class="text-muted">2 jam yang lalu</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    getMembersContent() {
        return `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Manajemen Anggota</h2>
                <button class="btn btn-primary" onclick="showAddMemberModal()">
                    <i class="fas fa-plus me-2"></i>Tambah Anggota
                </button>
            </div>

            <!-- Search and Filter -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Cari anggota..." id="searchMember">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterStatus">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Tidak Aktif</option>
                        <option value="pending">Menunggu</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterRole">
                        <option value="">Semua Role</option>
                        <option value="member">Anggota</option>
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>

            <!-- Members Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Telepon</th>
                                    <th>Status</th>
                                    <th>Role</th>
                                    <th>Bergabung</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="membersTableBody">
                                <tr>
                                    <td>001</td>
                                    <td>John Doe</td>
                                    <td>john_doe</td>
                                    <td>john@example.com</td>
                                    <td>08123456789</td>
                                    <td><span class="badge bg-success">Aktif</span></td>
                                    <td><span class="badge bg-primary">Anggota</span></td>
                                    <td>2024-01-15</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>002</td>
                                    <td>Jane Smith</td>
                                    <td>jane_smith</td>
                                    <td>jane@example.com</td>
                                    <td>08123456790</td>
                                    <td><span class="badge bg-success">Aktif</span></td>
                                    <td><span class="badge bg-success">Staff</span></td>
                                    <td>2024-01-10</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    }

    getNPLContent() {
        return `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Manajemen NPL (Non-Performing Loans)</h2>
                <div>
                    <button class="btn btn-sm btn-outline-success me-2" onclick="exportNPLData()">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshNPLData()">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- NPL Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total NPL</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">Rp 450.000.000</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">NPL Ratio</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">12.5%</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-percentage fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Jumlah Debitur</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">15</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Ditagihkan</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">Rp 125.000.000</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NPL Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Pinjaman Bermasalah</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Pinjaman</th>
                                    <th>Debitur</th>
                                    <th>Plafond</th>
                                    <th>Tunggakan</th>
                                    <th>Hari Terlambat</th>
                                    <th>Risiko</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>PNJ-2024-001</td>
                                    <td>John Doe</td>
                                    <td>Rp 50.000.000</td>
                                    <td class="text-danger">Rp 12.500.000</td>
                                    <td><span class="badge bg-danger">45 Hari</span></td>
                                    <td><span class="badge bg-warning">Sedang</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-1" onclick="viewNPLDetails('PNJ-2024-001')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="addCollectionAction('PNJ-2024-001')">
                                            <i class="fas fa-hand-holding-usd"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    }

    getNotFoundContent() {
        return `
            <div class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
                <div class="text-center">
                    <i class="fas fa-search text-muted fa-3x mb-3"></i>
                    <h5>Halaman Tidak Ditemukan</h5>
                    <p class="text-muted">Halaman yang Anda cari tidak tersedia.</p>
                    <button class="btn btn-primary" onclick="contentRenderer.loadContent('dashboard.html')">
                        <i class="fas fa-home me-2"></i>Kembali ke Dashboard
                    </button>
                </div>
            </div>
        `;
    }

    // Add more content generators as needed
    getLoansContent() { return '<h2>Manajemen Pinjaman</h2><p>Content untuk manajemen pinjaman...</p>'; }
    getSavingsContent() { return '<h2>Manajemen Simpanan</h2><p>Content untuk manajemen simpanan...</p>'; }
    getTransactionsContent() { return '<h2>Transaksi</h2><p>Content untuk transaksi...</p>'; }
    getReportsContent() { return '<h2>Laporan</h2><p>Content untuk laporan...</p>'; }
    getRiskFraudContent() { return '<h2>Risiko & Fraud</h2><p>Content untuk risiko dan fraud...</p>'; }
    getSettingsContent() { return '<h2>Pengaturan</h2><p>Content untuk pengaturan...</p>'; }
    getRoleAccessContent() { return '<h2>Role & Akses</h2><p>Content untuk role dan akses...</p>'; }
    getGuaranteeManagementContent() { return '<h2>Manajemen Penjaminan</h2><p>Content untuk manajemen penjaminan...</p>'; }
    getDatabaseManagementContent() { return '<h2>Administrasi Database</h2><p>Content untuk administrasi database...</p>'; }

    // Page initializers
    initializeMembersPage() {
        // Initialize search functionality
        const searchInput = document.getElementById('searchMember');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('#membersTableBody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
    }

    initializeNPLPage() {
        console.log('NPL Page initialized');
    }

    initializeLoansPage() {
        console.log('Loans Page initialized');
        
        // Initialize search functionality if exists
        const searchInput = document.getElementById('searchLoan');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('#loansTableBody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }

        // Initialize filter functionality if exists
        const filterStatus = document.getElementById('filterLoanStatus');
        if (filterStatus) {
            filterStatus.addEventListener('change', (e) => {
                const filterValue = e.target.value;
                const rows = document.querySelectorAll('#loansTableBody tr');
                rows.forEach(row => {
                    if (!filterValue) {
                        row.style.display = '';
                    } else {
                        const statusCell = row.querySelector('td:nth-child(5)');
                        const status = statusCell?.textContent.toLowerCase();
                        row.style.display = status?.includes(filterValue.toLowerCase()) ? '' : 'none';
                    }
                });
            });
        }
    }

    initializeSavingsPage() {
        console.log('Savings Page initialized');
        
        // Initialize search functionality
        const searchInput = document.getElementById('searchSavings');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('#savingsTableBody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
    }

    initializeTransactionsPage() {
        console.log('Transactions Page initialized');
        
        // Initialize date filter if exists
        const dateFilter = document.getElementById('dateFilter');
        if (dateFilter) {
            dateFilter.addEventListener('change', (e) => {
                const filterDate = e.target.value;
                // Filter logic here
                console.log('Filter by date:', filterDate);
            });
        }
    }

    initializeReportsPage() {
        console.log('Reports Page initialized');
        
        // Initialize report generation
        const generateBtn = document.getElementById('generateReport');
        if (generateBtn) {
            generateBtn.addEventListener('click', () => {
                alert('Laporan sedang dibuat...');
            });
        }
    }

    initializeSettingsPage() {
        console.log('Settings Page initialized');
        
        // Initialize settings form
        const settingsForm = document.getElementById('settingsForm');
        if (settingsForm) {
            settingsForm.addEventListener('submit', (e) => {
                e.preventDefault();
                alert('Pengaturan disimpan!');
            });
        }
    }

    initializeRiskFraudPage() {
        console.log('Risk & Fraud Page initialized');
        
        // Initialize risk assessment
        const riskBtn = document.getElementById('assessRisk');
        if (riskBtn) {
            riskBtn.addEventListener('click', () => {
                alert('Risk assessment sedang diproses...');
            });
        }
    }

    initializeRoleAccessPage() {
        console.log('Role Access Page initialized');
        
        // Initialize role assignment
        const assignBtn = document.getElementById('assignRole');
        if (assignBtn) {
            assignBtn.addEventListener('click', () => {
                const user = document.getElementById('userSelect')?.value;
                const role = document.getElementById('roleSelect')?.value;
                if (user && role) {
                    alert(`Role ${role} berhasil di-assign ke ${user}`);
                }
            });
        }
    }

    initializeGuaranteeManagementPage() {
        console.log('Guarantee Management Page initialized');
        
        // Initialize guarantee search
        const searchInput = document.getElementById('searchGuarantee');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('#guaranteeTableBody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
    }

    initializeDatabaseManagementPage() {
        console.log('Database Management Page initialized');
        
        // Initialize database operations
        const backupBtn = document.getElementById('backupDatabase');
        if (backupBtn) {
            backupBtn.addEventListener('click', () => {
                alert('Database backup sedang dibuat...');
            });
        }
    }

    loadInitialContent() {
        // Load dashboard content by default
        this.loadContent('dashboard.html');
    }
}

// Initialize the content renderer
window.contentRenderer = new ContentRenderer();

// Load initial content when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (window.contentRenderer) {
        window.contentRenderer.loadInitialContent();
    }
});
