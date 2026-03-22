<?php
/**
 * Unified Dashboard - Single Page Application
 * Semua halaman di-render dalam satu file PHP
 */

require_once __DIR__ . "/../config/error-config.php";
require_once __DIR__ . "/../api/auth.php";

// Start session for server-side state
session_start();

// Authentication check
function isAuthenticated() {
    $token = $_GET['token'] ?? $_POST['token'] ?? $_SESSION['authToken'] ?? null;
    if (!$token) {
        // Check localStorage simulation via POST
        $token = $_POST['authToken'] ?? null;
    }
    
    if ($token) {
        try {
            // Validate token
            $auth = new AuthSystem();
            $userData = $auth->validateToken($token);
            if ($userData) {
                $_SESSION['user'] = $userData;
                return $userData;
            }
        } catch (Exception $e) {
            error_log("Token validation failed: " . $e->getMessage());
        }
    }
    
    return null;
}

// Get current page from request
function getCurrentPage() {
    return $_GET['page'] ?? $_POST['page'] ?? 'dashboard';
}

// Get user role
function getUserRole($user) {
    return $user['role'] ?? 'member';
}

// Render page content based on page and role
function renderPageContent($page, $role, $user) {
    switch ($page) {
        case 'dashboard':
            return renderDashboard($role, $user);
        case 'members':
            return renderMembers($role, $user);
        case 'loans':
            return renderLoans($role, $user);
        case 'savings':
            return renderSavings($role, $user);
        case 'transactions':
            return renderTransactions($role, $user);
        case 'reports':
            return renderReports($role, $user);
        case 'npl':
            return renderNPL($role, $user);
        case 'risk-fraud':
            return renderRiskFraud($role, $user);
        case 'settings':
            return renderSettings($role, $user);
        case 'profile':
            return renderProfile($role, $user);
        default:
            return renderNotFound($page);
    }
}

// Render navigation menu based on role
function renderNavigation($role, $currentPage) {
    $menus = getMenusByRole($role);
    $navHtml = '<ul class="nav flex-column">';
    
    foreach ($menus as $menu) {
        if ($menu['key'] === 'divider') {
            $navHtml .= '<li class="nav-item"><hr class="nav-divider"></li>';
            continue;
        }
        
        $activeClass = ($menu['key'] === $currentPage) ? 'active' : '';
        $url = $menu['url'] ?? '#' . $menu['key'];
        $onclick = $menu['onclick'] ?? '';
        $class = $menu['class'] ?? '';
        
        $navHtml .= sprintf(
            '<li class="nav-item">
                <a class="nav-link %s %s" href="%s" data-page="%s" onclick="%s">
                    <i class="%s me-2"></i>%s
                </a>
            </li>',
            $activeClass,
            $class,
            $url,
            $menu['key'],
            $onclick,
            $menu['icon'],
            $menu['title']
        );
    }
    
    $navHtml .= '</ul>';
    return $navHtml;
}

// Get menus by role
function getMenusByRole($role) {
    $menuFile = __DIR__ . '/../assets/config/menus.json';
    if (file_exists($menuFile)) {
        $menus = json_decode(file_get_contents($menuFile), true);
        return $menus[$role] ?? $menus['member'] ?? [];
    }
    return [];
}

// Page renderers
function renderDashboard($role, $user) {
    ob_start();
    ?>
    <div class="dashboard-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dashboard</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" onclick="refreshDashboard()">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-users text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-muted">Total Anggota</h6>
                                <div class="h4 mb-0"><?php echo getMemberCount(); ?></div>
                                <small class="text-success"><i class="fas fa-arrow-up"></i> 12%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-hand-holding-usd text-success"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-muted">Pinjaman Aktif</h6>
                                <div class="h4 mb-0"><?php echo getActiveLoansCount(); ?></div>
                                <small class="text-success"><i class="fas fa-arrow-up"></i> 8%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-piggy-bank text-info"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-muted">Total Simpanan</h6>
                                <div class="h4 mb-0">Rp <?php echo number_format(getTotalSavings(), 0, ',', '.'); ?></div>
                                <small class="text-success"><i class="fas fa-arrow-up"></i> 15%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-muted">NPL</h6>
                                <div class="h4 mb-0"><?php echo getNPLCount(); ?></div>
                                <small class="text-warning"><i class="fas fa-minus"></i> 0%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0">Aktivitas Terbaru</h6>
                    </div>
                    <div class="card-body">
                        <div class="activity-feed">
                            <?php echo getRecentActivity(); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if (in_array($role, ['admin', 'super_admin'])): ?>
                            <button class="btn btn-primary" onclick="navigateToPage('members')">
                                <i class="fas fa-user-plus me-2"></i>Tambah Anggota
                            </button>
                            <button class="btn btn-success" onclick="navigateToPage('loans')">
                                <i class="fas fa-plus me-2"></i>Pengajuan Pinjaman
                            </button>
                            <?php endif; ?>
                            <button class="btn btn-info" onclick="navigateToPage('reports')">
                                <i class="fas fa-chart-bar me-2"></i>Lihat Laporan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderMembers($role, $user) {
    ob_start();
    ?>
    <div class="members-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manajemen Anggota</h2>
            <?php if (in_array($role, ['admin', 'super_admin'])): ?>
            <button class="btn btn-primary" onclick="showAddMemberModal()">
                <i class="fas fa-plus me-1"></i> Tambah Anggota
            </button>
            <?php endif; ?>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" placeholder="Cari anggota..." id="memberSearch">
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary active" onclick="filterMembers('all')">Semua</button>
                            <button class="btn btn-outline-primary" onclick="filterMembers('active')">Aktif</button>
                            <button class="btn btn-outline-primary" onclick="filterMembers('inactive')">Tidak Aktif</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>No. Anggota</th>
                                <th>Status</th>
                                <th>Bergabung</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="membersTableBody">
                            <?php echo getMembersTable($role); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderLoans($role, $user) {
    ob_start();
    ?>
    <div class="loans-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manajemen Pinjaman</h2>
            <?php if (in_array($role, ['admin', 'super_admin'])): ?>
            <button class="btn btn-success" onclick="showAddLoanModal()">
                <i class="fas fa-plus me-1"></i> Pengajuan Baru
            </button>
            <?php endif; ?>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" placeholder="Cari pinjaman..." id="loanSearch">
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary active" onclick="filterLoans('all')">Semua</button>
                            <button class="btn btn-outline-primary" onclick="filterLoans('pending')">Pending</button>
                            <button class="btn btn-outline-primary" onclick="filterLoans('approved')">Disetujui</button>
                            <button class="btn btn-outline-primary" onclick="filterLoans('active')">Aktif</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No. Pinjaman</th>
                                <th>Anggota</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="loansTableBody">
                            <?php echo getLoansTable($role); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderSavings($role, $user) {
    ob_start();
    ?>
    <div class="savings-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manajemen Simpanan</h2>
            <?php if (in_array($role, ['admin', 'super_admin'])): ?>
            <button class="btn btn-primary" onclick="showAddSavingsModal()">
                <i class="fas fa-plus me-1"></i> Setoran Baru
            </button>
            <?php endif; ?>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p>Konten manajemen simpanan akan ditampilkan di sini.</p>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderTransactions($role, $user) {
    ob_start();
    ?>
    <div class="transactions-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Transaksi</h2>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p>Konten transaksi akan ditampilkan di sini.</p>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderReports($role, $user) {
    ob_start();
    ?>
    <div class="reports-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Laporan</h2>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p>Konten laporan akan ditampilkan di sini.</p>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderNPL($role, $user) {
    ob_start();
    ?>
    <div class="npl-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Monitoring NPL</h2>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p>Konten monitoring NPL akan ditampilkan di sini.</p>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderRiskFraud($role, $user) {
    ob_start();
    ?>
    <div class="risk-fraud-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Risk & Fraud Detection</h2>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p>Konten risk & fraud akan ditampilkan di sini.</p>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderSettings($role, $user) {
    ob_start();
    ?>
    <div class="settings-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Pengaturan</h2>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p>Konten pengaturan akan ditampilkan di sini.</p>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderProfile($role, $user) {
    ob_start();
    ?>
    <div class="profile-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Profil Saya</h2>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 100px; height: 100px;">
                                <span style="font-size: 2rem;"><?php echo strtoupper(substr($user['name'], 0, 2)); ?></span>
                            </div>
                            <h5><?php echo htmlspecialchars($user['name']); ?></h5>
                            <p class="text-muted"><?php echo htmlspecialchars($user['username']); ?></p>
                            <span class="badge bg-primary"><?php echo ucfirst($user['role']); ?></span>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h6 class="mb-3">Informasi Akun</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td width="150">Nama Lengkap</td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                            </tr>
                            <tr>
                                <td>Username</td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                            </tr>
                            <tr>
                                <td>Role</td>
                                <td><?php echo ucfirst($user['role']); ?></td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td><span class="badge bg-success">Aktif</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderNotFound($page) {
    ob_start();
    ?>
    <div class="not-found-content">
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
            </div>
            <h3>Halaman Tidak Ditemukan</h3>
            <p class="text-muted">Halaman "<?php echo htmlspecialchars($page); ?>" tidak tersedia.</p>
            <button class="btn btn-primary" onclick="navigateToPage('dashboard')">
                <i class="fas fa-home me-2"></i>Kembali ke Dashboard
            </button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Database helper functions (simplified)
function getMemberCount() {
    // Simulasi - implementasi dengan query database sesungguhnya
    return 150;
}

function getActiveLoansCount() {
    return 45;
}

function getTotalSavings() {
    return 250000000;
}

function getNPLCount() {
    return 3;
}

function getRecentActivity() {
    return '
    <div class="activity-item d-flex align-items-center mb-3">
        <div class="flex-shrink-0">
            <div class="bg-success bg-opacity-10 rounded-3 p-2">
                <i class="fas fa-plus text-success"></i>
            </div>
        </div>
        <div class="flex-grow-1 ms-3">
            <div class="fw-semibold">Anggota baru ditambahkan</div>
            <small class="text-muted">John Doe - 2 jam yang lalu</small>
        </div>
    </div>
    <div class="activity-item d-flex align-items-center mb-3">
        <div class="flex-shrink-0">
            <div class="bg-primary bg-opacity-10 rounded-3 p-2">
                <i class="fas fa-hand-holding-usd text-primary"></i>
            </div>
        </div>
        <div class="flex-grow-1 ms-3">
            <div class="fw-semibold">Pinjaman disetujui</div>
            <small class="text-muted">Jane Smith - 5 jam yang lalu</small>
        </div>
    </div>';
}

function getMembersTable($role) {
    // Simulasi data - implementasi dengan query database
    return '
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
        <td>001</td>
        <td><span class="badge bg-success">Aktif</span></td>
        <td>2024-01-15</td>
        <td>
            <button class="btn btn-sm btn-outline-primary me-1" onclick="viewMember(1)">
                <i class="fas fa-eye"></i>
            </button>
            ' . (in_array($role, ['admin', 'super_admin']) ? '
            <button class="btn btn-sm btn-outline-warning me-1" onclick="editMember(1)">
                <i class="fas fa-edit"></i>
            </button>' : '') . '
        </td>
    </tr>';
}

function getLoansTable($role) {
    return '
    <tr>
        <td>LN001</td>
        <td>John Doe</td>
        <td>Rp 10.000.000</td>
        <td><span class="badge bg-success">Aktif</span></td>
        <td>2024-01-15</td>
        <td>
            <button class="btn btn-sm btn-outline-primary me-1" onclick="viewLoan(1)">
                <i class="fas fa-eye"></i>
            </button>
        </td>
    </tr>';
}

// Main execution
$user = isAuthenticated();
if (!$user) {
    // Redirect to login if not authenticated
    header('Location: ../login.html');
    exit;
}

$role = getUserRole($user);
$currentPage = getCurrentPage();
$pageContent = renderPageContent($currentPage, $role, $user);
$navigation = renderNavigation($role, $currentPage);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($currentPage); ?> - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="manifest" href="/mono-v2/manifest.json">
    <link href="/mono-v2/assets/css/main.css" rel="stylesheet">
    <style>
        .nav-link.active {
            background-color: #007bff;
            color: white !important;
        }
        .nav-divider {
            margin: 0.5rem 0;
            border-color: #dee2e6;
        }
        .content-area {
            min-height: calc(100vh - 200px);
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .avatar-lg {
            width: 100px;
            height: 100px;
        }
        .avatar-sm {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 mb-0">Loading...</p>
        </div>
    </div>

    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#" onclick="navigateToPage('dashboard')">
                <i class="fas fa-university me-2"></i>
                KSP Lam Gabe Jaya
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($user['name']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="navigateToPage('profile')">
                            <i class="fas fa-user me-2"></i>Profile
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="navigateToPage('settings')">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="logout()">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <?php echo $navigation; ?>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content-area">
                <div id="pageContent">
                    <?php echo $pageContent; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navigation functions
        function navigateToPage(page) {
            showLoading();
            
            // Update URL without page refresh
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            window.history.pushState({}, '', url);
            
            // Load new content via AJAX
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'page=' + page + '&ajax=1'
            })
            .then(response => response.text())
            .then(html => {
                // Extract content from response
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('pageContent');
                const newNav = doc.querySelector('.sidebar .position-sticky');
                
                if (newContent) {
                    document.getElementById('pageContent').innerHTML = newContent.innerHTML;
                }
                if (newNav) {
                    document.querySelector('.sidebar .position-sticky').innerHTML = newNav.innerHTML;
                }
                
                hideLoading();
            })
            .catch(error => {
                console.error('Error loading page:', error);
                hideLoading();
                showAlert('Gagal memuat halaman', 'danger');
            });
        }
        
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }
        
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
        
        function showAlert(message, type = 'info') {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', alertHtml);
        }
        
        function logout() {
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                // Clear session
                fetch('../api/auth.php?action=logout', {
                    method: 'POST'
                })
                .then(() => {
                    // Clear local storage
                    localStorage.removeItem('authToken');
                    sessionStorage.removeItem('authToken');
                    
                    // Redirect to login
                    window.location.href = '../login.html';
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    // Force redirect even if error
                    window.location.href = '../login.html';
                });
            }
        }
        
        // Handle browser back/forward buttons
        window.addEventListener('popstate', function(event) {
            const params = new URLSearchParams(window.location.search);
            const page = params.get('page') || 'dashboard';
            navigateToPage(page);
        });
        
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        
        // Placeholder functions for page-specific actions
        function refreshDashboard() {
            navigateToPage('dashboard');
        }
        
        function viewMember(id) {
            showAlert('View member: ' + id, 'info');
        }
        
        function editMember(id) {
            showAlert('Edit member: ' + id, 'info');
        }
        
        function viewLoan(id) {
            showAlert('View loan: ' + id, 'info');
        }
        
        function showAddMemberModal() {
            showAlert('Add member modal', 'info');
        }
        
        function showAddLoanModal() {
            showAlert('Add loan modal', 'info');
        }
        
        function showAddSavingsModal() {
            showAlert('Add savings modal', 'info');
        }
        
        function filterMembers(filter) {
            showAlert('Filter members: ' + filter, 'info');
        }
        
        function filterLoans(filter) {
            showAlert('Filter loans: ' + filter, 'info');
        }
    </script>
</body>
</html>
