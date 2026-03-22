<?php
/**
 * KSP Lam Gabe Jaya - Main Dashboard Page
 * Primary dashboard after successful login
 */

// Define access flag for constants
define('IN_MAIN_PHP', true);

// Security headers
header('Content-Type: text/html; charset=UTF-8');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Load required files
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/error-config.php';
// Don't load api/auth.php here - it's for API endpoints only

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication check
try {
    // Check session first
    if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
        header('Location: /mono-v2/login.php');
        exit;
    }
    
    $user = $_SESSION['user'];
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    
} catch (Exception $e) {
    error_log("Authentication error in main.php: " . $e->getMessage());
    header('Location: /mono-v2/login.php');
    exit;
}

// Get user role and permissions
$userRole = $user['role'] ?? 'nasabah';
$userName = $user['full_name'] ?? $user['username'];
$userDisplayName = $user['role_display_name'] ?? ucfirst($userRole);
$permissions = $user['permissions'] ?? [];

// Determine dashboard layout based on role
$dashboardLayout = getDashboardLayout($userRole);
$menuItems = getMenuItems($userRole);
$widgets = getDashboardWidgets($userRole);

// Page metadata
$pageTitle = 'Dashboard - ' . APP_NAME;
$pageDescription = 'Sistem Koperasi Digital Terpadu';

// Helper functions
function getDashboardLayout($role) {
    $layouts = [
        'bos' => 'bos',
        'admin' => 'admin',
        'teller' => 'teller',
        'collector' => 'collector',
        'nasabah' => 'nasabah'
    ];
    
    return $layouts[$role] ?? 'nasabah';
}

function getMenuItems($role) {
    $menus = [
        'bos' => [
            ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => '#dashboard'],
            ['key' => 'laporan', 'title' => 'Laporan Keuangan', 'icon' => 'fas fa-chart-line', 'url' => '#laporan'],
            ['key' => 'nasabah', 'title' => 'Data Nasabah', 'icon' => 'fas fa-users', 'url' => '#nasabah'],
            ['key' => 'pinjaman', 'title' => 'Pinjaman', 'icon' => 'fas fa-hand-holding-usd', 'url' => '#pinjaman'],
            ['key' => 'simpanan', 'title' => 'Simpanan', 'icon' => 'fas fa-piggy-bank', 'url' => '#simpanan'],
            ['key' => 'pengaturan', 'title' => 'Pengaturan', 'icon' => 'fas fa-cog', 'url' => '#pengaturan']
        ],
        'admin' => [
            ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => '#dashboard'],
            ['key' => 'nasabah', 'title' => 'Nasabah', 'icon' => 'fas fa-users', 'url' => '#nasabah'],
            ['key' => 'pinjaman', 'title' => 'Pinjaman', 'icon' => 'fas fa-hand-holding-usd', 'url' => '#pinjaman'],
            ['key' => 'simpanan', 'title' => 'Simpanan', 'icon' => 'fas fa-piggy-bank', 'url' => '#simpanan'],
            ['key' => 'transaksi', 'title' => 'Transaksi', 'icon' => 'fas fa-exchange-alt', 'url' => '#transaksi'],
            ['key' => 'laporan', 'title' => 'Laporan', 'icon' => 'fas fa-chart-bar', 'url' => '#laporan']
        ],
        'teller' => [
            ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => '#dashboard'],
            ['key' => 'nasabah', 'title' => 'Nasabah', 'icon' => 'fas fa-users', 'url' => '#nasabah'],
            ['key' => 'setoran', 'title' => 'Setoran', 'icon' => 'fas fa-plus-circle', 'url' => '#setoran'],
            ['key' => 'penarikan', 'title' => 'Penarikan', 'icon' => 'fas fa-minus-circle', 'url' => '#penarikan'],
            ['key' => 'pembayaran', 'title' => 'Pembayaran', 'icon' => 'fas fa-credit-card', 'url' => '#pembayaran'],
            ['key' => 'laporan_harian', 'title' => 'Laporan Harian', 'icon' => 'fas fa-clipboard-list', 'url' => '#laporan_harian']
        ],
        'collector' => [
            ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => '#dashboard'],
            ['key' => 'jadwal', 'title' => 'Jadwal Kutipan', 'icon' => 'fas fa-calendar-alt', 'url' => '#jadwal'],
            ['key' => 'rute', 'title' => 'Rute Hari Ini', 'icon' => 'fas fa-route', 'url' => '#rute'],
            ['key' => 'nasabah_kunjungan', 'title' => 'Nasabah Kunjungan', 'icon' => 'fas fa-user-friends', 'url' => '#nasabah_kunjungan'],
            ['key' => 'kutipan', 'title' => 'Kutipan', 'icon' => 'fas fa-money-bill-wave', 'url' => '#kutipan'],
            ['key' => 'gps_log', 'title' => 'GPS Log', 'icon' => 'fas fa-map-marked-alt', 'url' => '#gps_log']
        ],
        'nasabah' => [
            ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => '#dashboard'],
            ['key' => 'profil', 'title' => 'Profil Saya', 'icon' => 'fas fa-user', 'url' => '#profil'],
            ['key' => 'simpanan_saya', 'title' => 'Simpanan Saya', 'icon' => 'fas fa-piggy-bank', 'url' => '#simpanan_saya'],
            ['key' => 'pinjaman_saya', 'title' => 'Pinjaman Saya', 'icon' => 'fas fa-hand-holding-usd', 'url' => '#pinjaman_saya'],
            ['key' => 'riwayat', 'title' => 'Riwayat Transaksi', 'icon' => 'fas fa-history', 'url' => '#riwayat'],
            ['key' => 'pembayaran', 'title' => 'Pembayaran', 'icon' => 'fas fa-credit-card', 'url' => '#pembayaran']
        ]
    ];
    
    return $menus[$role] ?? $menus['nasabah'];
}

function getDashboardWidgets($role) {
    $widgets = [
        'bos' => [
            'overview_stats' => ['title' => 'Ringkasan Bisnis', 'type' => 'stats'],
            'financial_health' => ['title' => 'Kesehatan Keuangan', 'type' => 'chart'],
            'top_performers' => ['title' => 'Petugas Terbaik', 'type' => 'performance'],
            'business_alerts' => ['title' => 'Alert Bisnis', 'type' => 'alerts']
        ],
        'admin' => [
            'overview_stats' => ['title' => 'Ringkasan Operasional', 'type' => 'stats'],
            'member_stats' => ['title' => 'Statistik Nasabah', 'type' => 'chart'],
            'loan_portfolio' => ['title' => 'Portfolio Pinjaman', 'type' => 'chart'],
            'recent_activity' => ['title' => 'Aktivitas Terbaru', 'type' => 'activity'],
            'quick_actions' => ['title' => 'Aksi Cepat', 'type' => 'actions'],
            'notifications' => ['title' => 'Notifikasi', 'type' => 'notifications']
        ],
        'teller' => [
            'daily_summary' => ['title' => 'Ringkasan Harian', 'type' => 'stats'],
            'transaction_queue' => ['title' => 'Antrian Transaksi', 'type' => 'queue'],
            'cash_balance' => ['title' => 'Saldo Kas', 'type' => 'balance'],
            'recent_transactions' => ['title' => 'Transaksi Terbaru', 'type' => 'transactions']
        ],
        'collector' => [
            'daily_target' => ['title' => 'Target Harian', 'type' => 'target'],
            'collection_status' => ['title' => 'Status Kutipan', 'type' => 'collection'],
            'route_progress' => ['title' => 'Progress Rute', 'type' => 'route'],
            'member_visits' => ['title' => 'Kunjungan Hari Ini', 'type' => 'visits'],
            'gps_tracking' => ['title' => 'GPS Tracking', 'type' => 'gps']
        ],
        'nasabah' => [
            'account_summary' => ['title' => 'Ringkasan Akun', 'type' => 'summary'],
            'savings_balance' => ['title' => 'Saldo Simpanan', 'type' => 'balance'],
            'loan_status' => ['title' => 'Status Pinjaman', 'type' => 'loan'],
            'payment_schedule' => ['title' => 'Jadwal Pembayaran', 'type' => 'schedule'],
            'recent_transactions' => ['title' => 'Transaksi Terbaru', 'type' => 'transactions']
        ]
    ];
    
    return $widgets[$role] ?? $widgets['nasabah'];
}

// Get dashboard data (would typically come from database)
function getDashboardData($role, $userId) {
    // Simulasi data - implementasi dengan query database
    $data = [
        'overview_stats' => [
            'total_members' => 150,
            'active_loans' => 45,
            'total_savings' => 250000000,
            'monthly_growth' => 12
        ],
        'account_summary' => [
            'savings_balance' => 5000000,
            'active_loan' => 10000000,
            'monthly_payment' => 500000,
            'next_payment_date' => '2024-02-01'
        ],
        'recent_activity' => [
            ['type' => 'member', 'title' => 'Anggota baru ditambahkan', 'user' => 'John Doe', 'time' => '2 jam yang lalu'],
            ['type' => 'loan', 'title' => 'Pinjaman disetujui', 'user' => 'Jane Smith', 'time' => '5 jam yang lalu'],
            ['type' => 'payment', 'title' => 'Pembayaran diterima', 'user' => 'Robert Johnson', 'time' => '1 hari yang lalu']
        ]
    ];
    
    return $data[$role] ?? $data['overview_stats'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#007bff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="KSP">
    <link rel="manifest" href="/manifest.json">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (reliable version with fallback) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" onerror="this.href='/mono-v2/assets/css/fontawesome-fallback.css';" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/mono-v2/assets/css/dashboard.css">
    
    <style>
        :root {
            --primary-color: #007bff;
            --primary-dark: #0056b3;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --sidebar-width: 280px;
            --header-height: 60px;
            --border-radius: 0.5rem;
            --box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fc;
            color: var(--dark-color);
            overflow-x: hidden;
        }
        
        /* Header */
        .app-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid #e9ecef;
            z-index: 1030;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            box-shadow: var(--box-shadow);
        }
        
        .app-header .brand {
            display: flex;
            align-items: center;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .app-header .brand i {
            margin-right: 0.5rem;
            font-size: 1.5rem;
        }
        
        .app-header .header-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-dropdown {
            position: relative;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }
        
        /* Sidebar */
        .app-sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: white;
            border-right: 1px solid #e9ecef;
            overflow-y: auto;
            z-index: 1020;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .menu-section {
            margin-bottom: 2rem;
        }
        
        .menu-section-title {
            padding: 0 1.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--secondary-color);
            letter-spacing: 0.05em;
        }
        
        .menu-item {
            display: block;
            padding: 0.75rem 1.5rem;
            color: var(--dark-color);
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }
        
        .menu-item:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }
        
        .menu-item.active {
            background-color: rgba(0, 123, 255, 0.1);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            font-weight: 500;
        }
        
        .menu-item i {
            width: 20px;
            margin-right: 0.75rem;
            text-align: center;
        }
        
        /* Main Content */
        .app-main {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 2rem;
            min-height: calc(100vh - var(--header-height));
        }
        
        /* Dashboard Widgets */
        .dashboard-header {
            margin-bottom: 2rem;
        }
        
        .dashboard-header h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .dashboard-header p {
            color: var(--secondary-color);
            margin: 0;
        }
        
        .widget-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .widget {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px solid #e9ecef;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        
        .widget:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .widget-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            background: linear-gradient(135deg, var(--light-color) 0%, white 100%);
        }
        
        .widget-header h6 {
            font-weight: 600;
            margin: 0;
            color: var(--dark-color);
        }
        
        .widget-body {
            padding: 1.5rem;
        }
        
        /* Stats Widget */
        .stats-widget {
            text-align: center;
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            color: var(--secondary-color);
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        
        .stats-change {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .stats-change.positive {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .stats-change.negative {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }
        
        /* Activity Widget */
        .activity-item {
            display: flex;
            align-items: flex-start;
            padding: 1rem 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .activity-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        
        .activity-item:first-child {
            padding-top: 0;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 0.875rem;
            flex-shrink: 0;
        }
        
        .activity-icon.member {
            background-color: rgba(0, 123, 255, 0.1);
            color: var(--primary-color);
        }
        
        .activity-icon.loan {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .activity-icon.payment {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }
        
        .activity-content {
            flex-grow: 1;
        }
        
        .activity-title {
            font-weight: 500;
            margin-bottom: 0.25rem;
            color: var(--dark-color);
        }
        
        .activity-meta {
            font-size: 0.875rem;
            color: var(--secondary-color);
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
        }
        
        .quick-action {
            text-align: center;
            padding: 1.5rem 1rem;
            border: 2px dashed #e9ecef;
            border-radius: var(--border-radius);
            text-decoration: none;
            color: var(--dark-color);
            transition: all 0.2s ease;
        }
        
        .quick-action:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background-color: rgba(0, 123, 255, 0.05);
        }
        
        .quick-action i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .quick-action span {
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        /* Notifications */
        .notification-item {
            display: flex;
            align-items: flex-start;
            padding: 1rem 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .notification-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        
        .notification-item:first-child {
            padding-top: 0;
        }
        
        .notification-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-top: 0.5rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .notification-dot.unread {
            background-color: var(--primary-color);
        }
        
        .notification-dot.read {
            background-color: #e9ecef;
        }
        
        .notification-content {
            flex-grow: 1;
        }
        
        .notification-title {
            font-weight: 500;
            margin-bottom: 0.25rem;
            color: var(--dark-color);
        }
        
        .notification-time {
            font-size: 0.75rem;
            color: var(--secondary-color);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .app-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .app-sidebar.show {
                transform: translateX(0);
            }
            
            .app-main {
                margin-left: 0;
            }
            
            .widget-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        /* Loading Spinner */
        .loading-spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid #f3f3f3;
            border-top: 2px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark-color);
            cursor: pointer;
            padding: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="app-header">
        <button class="mobile-menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        
        <a href="#dashboard" class="brand" onclick="navigateTo('dashboard', event)">
            <i class="fas fa-university"></i>
            <?php echo APP_NAME; ?>
        </a>
        
        <div class="header-actions">
            <button class="btn btn-outline-primary btn-sm" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt me-1"></i>
                Refresh
            </button>
            
            <div class="user-dropdown">
                <div class="user-avatar" onclick="toggleUserMenu()">
                    <?php echo strtoupper(substr($userName, 0, 2)); ?>
                </div>
                
                <div class="dropdown-menu dropdown-menu-end" id="userMenu" style="display: none;">
                    <div class="dropdown-item-text">
                        <strong><?php echo htmlspecialchars($userName); ?></strong>
                        <br>
                        <small class="text-muted"><?php echo htmlspecialchars(ucfirst($userRole)); ?></small>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="showProfile()">
                        <i class="fas fa-user me-2"></i>Profil
                    </a>
                    <a class="dropdown-item" href="#" onclick="showSettings()">
                        <i class="fas fa-cog me-2"></i>Pengaturan
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="logout()">
                        <i class="fas fa-sign-out-alt me-2"></i>Keluar
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Sidebar -->
    <aside class="app-sidebar" id="sidebar">
        <nav class="sidebar-menu">
            <?php foreach ($menuItems as $item): ?>
                <a href="<?php echo $item['url']; ?>" class="menu-item" onclick="navigateTo('<?php echo $item['key']; ?>', event)">
                    <i class="<?php echo $item['icon']; ?>"></i>
                    <?php echo $item['title']; ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="app-main">
        <div class="dashboard-header">
            <h1>Selamat Datang, <?php echo htmlspecialchars($userName); ?>!</h1>
            <p>Dashboard <?php echo htmlspecialchars(ucfirst($userRole)); ?> - <?php echo date('d F Y'); ?></p>
        </div>
        
        <div class="widget-grid" id="dashboardWidgets">
            <!-- Widgets will be loaded here -->
        </div>
    </main>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Global variables
        let currentUser = <?php echo json_encode($user); ?>;
        let userRole = <?php echo json_encode($userRole); ?>;
        
        // Check session timeout
        function checkSessionTimeout() {
            const sessionTimeout = 30 * 60 * 1000; // 30 minutes in milliseconds
            const loginTime = <?php echo time() * 1000; ?>;
            const currentTime = Date.now();
            
            if (currentTime - loginTime > sessionTimeout) {
                showNotification('Session expired. Please login again.', 'warning');
                setTimeout(() => {
                    logout();
                }, 3000);
            }
        }
        
        // Show notification function
        function showNotification(message, type = 'info') {
            // Create notification container if it doesn't exist
            let notificationContainer = document.getElementById('notification-container');
            if (!notificationContainer) {
                notificationContainer = document.createElement('div');
                notificationContainer.id = 'notification-container';
                notificationContainer.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    max-width: 350px;
                `;
                document.body.appendChild(notificationContainer);
            }
            
            // Create notification element
            const notification = document.createElement('div');
            const notificationId = 'notification-' + Date.now();
            notification.id = notificationId;
            
            // Set notification styles based on type
            const typeStyles = {
                success: {
                    bg: '#28a745',
                    border: '#1e7e34',
                    icon: 'fa-check-circle'
                },
                error: {
                    bg: '#dc3545',
                    border: '#bd2130',
                    icon: 'fa-exclamation-circle'
                },
                warning: {
                    bg: '#ffc107',
                    border: '#e0a800',
                    icon: 'fa-exclamation-triangle'
                },
                info: {
                    bg: '#17a2b8',
                    border: '#117a8b',
                    icon: 'fa-info-circle'
                }
            };
            
            const style = typeStyles[type] || typeStyles.info;
            
            notification.style.cssText = `
                background-color: ${style.bg};
                border: 1px solid ${style.border};
                color: white;
                padding: 12px 16px;
                margin-bottom: 10px;
                border-radius: 6px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                display: flex;
                align-items: center;
                font-size: 14px;
                animation: slideInRight 0.3s ease-out;
                cursor: pointer;
            `;
            
            notification.innerHTML = `
                <i class="fas ${style.icon} me-2"></i>
                <span>${message}</span>
                <button class="ms-auto btn btn-sm btn-link text-white p-0" style="opacity: 0.8;">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            // Add CSS animation if not already added
            if (!document.getElementById('notification-styles')) {
                const styleSheet = document.createElement('style');
                styleSheet.id = 'notification-styles';
                styleSheet.textContent = `
                    @keyframes slideInRight {
                        from {
                            transform: translateX(100%);
                            opacity: 0;
                        }
                        to {
                            transform: translateX(0);
                            opacity: 1;
                        }
                    }
                    @keyframes slideOutRight {
                        from {
                            transform: translateX(0);
                            opacity: 1;
                        }
                        to {
                            transform: translateX(100%);
                            opacity: 0;
                        }
                    }
                `;
                document.head.appendChild(styleSheet);
            }
            
            // Add click handler to close notification
            notification.addEventListener('click', function() {
                removeNotification(notificationId);
            });
            
            // Add to container
            notificationContainer.appendChild(notification);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                removeNotification(notificationId);
            }, 5000);
        }
        
        // Remove notification function
        function removeNotification(notificationId) {
            const notification = document.getElementById(notificationId);
            if (notification) {
                notification.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }
        }
        
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardWidgets();
            initializeEventListeners();
            checkSessionTimeout();
        });
        
        // Load dashboard widgets
        function loadDashboardWidgets() {
            const widgetsContainer = document.getElementById('dashboardWidgets');
            widgetsContainer.innerHTML = '<div class="text-center"><div class="loading-spinner"></div></div>';
            
            // Simulate loading widgets
            setTimeout(() => {
                const widgets = <?php echo json_encode($widgets); ?>;
                let widgetsHTML = '';
                
                for (const [key, widget] of Object.entries(widgets)) {
                    widgetsHTML += generateWidgetHTML(key, widget);
                }
                
                widgetsContainer.innerHTML = widgetsHTML;
            }, 500);
        }
        
        // Generate widget HTML based on type
        function generateWidgetHTML(key, widget) {
            switch (widget.type) {
                case 'stats':
                    return generateStatsWidget(key, widget);
                case 'activity':
                    return generateActivityWidget(key, widget);
                case 'actions':
                    return generateActionsWidget(key, widget);
                case 'notifications':
                    return generateNotificationsWidget(key, widget);
                default:
                    return generateDefaultWidget(key, widget);
            }
        }
        
        // Generate stats widget
        function generateStatsWidget(key, widget) {
            // Role-based statistics
            const userRole = '<?php echo $userRole; ?>';
            
            const roleStats = {
                'bos': {
                    'overview_stats': [
                        { label: 'Total Anggota', value: '150', change: '+12%', positive: true },
                        { label: 'Pinjaman Aktif', value: '45', change: '+8%', positive: true },
                        { label: 'Total Simpanan', value: 'Rp 250Jt', change: '+15%', positive: true },
                        { label: 'Total Omzet', value: 'Rp 450Jt', change: '+18%', positive: true }
                    ],
                    'account_summary': [
                        { label: 'Total Aset', value: 'Rp 500Jt', change: '+22%', positive: true },
                        { label: 'Laba Bulanan', value: 'Rp 25Jt', change: '+5%', positive: true },
                        { label: 'NPL Ratio', value: '2.3%', change: '-0.5%', positive: true }
                    ]
                },
                'admin': {
                    'overview_stats': [
                        { label: 'Anggota Aktif', value: '125', change: '+8%', positive: true },
                        { label: 'Pinjaman Pending', value: '12', change: '-15%', positive: false },
                        { label: 'Simpanan Baru', value: '8', change: '+25%', positive: true },
                        { label: 'Transaksi Hari Ini', value: '45', change: '+10%', positive: true }
                    ],
                    'account_summary': [
                        { label: 'User Terdaftar', value: '180', change: '+6%', positive: true },
                        { label: 'Role Aktif', value: '5', change: '0%', positive: false },
                        { label: 'System Uptime', value: '99.8%', change: '+0.2%', positive: true }
                    ]
                },
                'teller': {
                    'overview_stats': [
                        { label: 'Transaksi Hari Ini', value: '28', change: '+12%', positive: true },
                        { label: 'Setoran', value: 'Rp 15Jt', change: '+8%', positive: true },
                        { label: 'Penarikan', value: 'Rp 8Jt', change: '-5%', positive: false },
                        { label: 'Nasabah Dilayani', value: '35', change: '+15%', positive: true }
                    ],
                    'account_summary': [
                        { label: 'Saldo Kas', value: 'Rp 50Jt', change: '+3%', positive: true },
                        { label: 'Pending Setoran', value: '5', change: '-20%', positive: false },
                        { label: 'Form Hari Ini', value: '18', change: '+10%', positive: true }
                    ]
                },
                'collector': {
                    'overview_stats': [
                        { label: 'Target Hari Ini', value: '15', change: '0%', positive: false },
                        { label: 'Kunjungan Selesai', value: '8', change: '+53%', positive: true },
                        { label: 'Kutipan Terkumpul', value: 'Rp 2.5Jt', change: '+18%', positive: true },
                        { label: 'Nasabah Dikunjungi', value: '12', change: '+80%', positive: true }
                    ],
                    'account_summary': [
                        { label: 'Rute Selesai', value: '65%', change: '+15%', positive: true },
                        { label: 'GPS Points', value: '24', change: '+20%', positive: true },
                        { label: 'Efisiensi', value: '85%', change: '+5%', positive: true }
                    ]
                },
                'nasabah': {
                    'overview_stats': [
                        { label: 'Saldo Simpanan', value: 'Rp 5Jt', change: '+2%', positive: true },
                        { label: 'Pinjaman Aktif', value: 'Rp 10Jt', change: '0%', positive: false },
                        { label: 'Cicilan Bulanan', value: 'Rp 500rb', change: '-', positive: false },
                        { label: 'Total Transaksi', value: '245', change: '+8%', positive: true }
                    ],
                    'account_summary': [
                        { label: 'Simpanan Wajib', value: 'Rp 2Jt', change: '+5%', positive: true },
                        { label: 'Simpanan Sukarela', value: 'Rp 3Jt', change: '+1%', positive: true },
                        { label: 'Sisa Pinjaman', value: 'Rp 8.5Jt', change: '-2%', positive: true }
                    ]
                }
            };
            
            // Get role-specific stats or default to bos stats
            const stats = roleStats[userRole] || roleStats['bos'];
            const widgetStats = stats[key] || stats['overview_stats'];
            
            let html = '<div class="widget stats-widget">';
            html += '<div class="widget-header"><h6>' + widget.title + '</h6></div>';
            html += '<div class="widget-body">';
            
            widgetStats.forEach(stat => {
                html += '<div class="mb-3">';
                html += '<div class="stats-number">' + stat.value + '</div>';
                html += '<div class="stats-label">' + stat.label + '</div>';
                if (stat.change !== '-') {
                    html += '<div class="stats-change ' + (stat.positive ? 'positive' : 'negative') + '">' + stat.change + '</div>';
                }
                html += '</div>';
            });
            
            html += '</div></div>';
            return html;
        }
        
        // Generate activity widget
        function generateActivityWidget(key, widget) {
            const activities = [
                { type: 'member', title: 'Anggota baru ditambahkan', user: 'John Doe', time: '2 jam yang lalu' },
                { type: 'loan', title: 'Pinjaman disetujui', user: 'Jane Smith', time: '5 jam yang lalu' },
                { type: 'payment', title: 'Pembayaran diterima', user: 'Robert Johnson', time: '1 hari yang lalu' }
            ];
            
            let html = '<div class="widget">';
            html += '<div class="widget-header"><h6>' + widget.title + '</h6></div>';
            html += '<div class="widget-body">';
            
            activities.forEach(activity => {
                html += '<div class="activity-item">';
                html += '<div class="activity-icon ' + activity.type + '">';
                html += '<i class="fas fa-' + getActivityIcon(activity.type) + '"></i>';
                html += '</div>';
                html += '<div class="activity-content">';
                html += '<div class="activity-title">' + activity.title + '</div>';
                html += '<div class="activity-meta">' + activity.user + ' • ' + activity.time + '</div>';
                html += '</div>';
                html += '</div>';
            });
            
            html += '</div></div>';
            return html;
        }
        
        // Generate quick actions widget
        function generateActionsWidget(key, widget) {
            const actions = {
                'admin': [
                    { icon: 'fas fa-user-plus', label: 'Tambah Anggota' },
                    { icon: 'fas fa-hand-holding-usd', label: 'Ajukan Pinjaman' },
                    { icon: 'fas fa-plus', label: 'Setoran Baru' },
                    { icon: 'fas fa-chart-bar', label: 'Lihat Laporan' }
                ],
                'member': [
                    { icon: 'fas fa-plus-circle', label: 'Ajukan Pinjaman' },
                    { icon: 'fas fa-piggy-bank', label: 'Tambah Simpanan' },
                    { icon: 'fas fa-credit-card', label: 'Bayar Cicilan' },
                    { icon: 'fas fa-download', label: 'Download Laporan' }
                ]
            };
            
            const widgetActions = (userRole === 'bos' || userRole === 'admin') ? actions['admin'] : actions['member'];
            
            let html = '<div class="widget">';
            html += '<div class="widget-header"><h6>' + widget.title + '</h6></div>';
            html += '<div class="widget-body">';
            html += '<div class="quick-actions">';
            
            widgetActions.forEach(action => {
                html += '<a href="#" class="quick-action" onclick="handleQuickAction(\'' + action.label + '\')">';
                html += '<i class="' + action.icon + '"></i>';
                html += '<span>' + action.label + '</span>';
                html += '</a>';
            });
            
            html += '</div></div></div>';
            return html;
        }
        
        // Handle quick action clicks
        function handleQuickAction(actionLabel) {
            showNotification('Quick action: ' + actionLabel, 'info');
            // TODO: Implement actual action handling
            console.log('Quick action clicked:', actionLabel);
        }
        
        // Refresh dashboard
        function refreshDashboard() {
            showNotification('Dashboard refreshed', 'success');
            loadDashboardWidgets();
            // Reload current page content
            const currentPage = window.location.hash.substring(1) || 'dashboard';
            loadPageContent(currentPage);
        }
        
        // Generate notifications widget
        function generateNotificationsWidget(key, widget) {
            const notifications = [
                { title: 'Pengajuan pinjaman baru', time: '30 menit yang lalu', unread: true },
                { title: 'Jadwal pembayaran cicilan', time: '2 jam yang lalu', unread: true },
                { title: 'Update sistem', time: '1 hari yang lalu', unread: false }
            ];
            
            let html = '<div class="widget">';
            html += '<div class="widget-header"><h6>' + widget.title + '</h6></div>';
            html += '<div class="widget-body">';
            
            notifications.forEach(notification => {
                html += '<div class="notification-item">';
                html += '<div class="notification-dot ' + (notification.unread ? 'unread' : 'read') + '"></div>';
                html += '<div class="notification-content">';
                html += '<div class="notification-title">' + notification.title + '</div>';
                html += '<div class="notification-time">' + notification.time + '</div>';
                html += '</div>';
                html += '</div>';
            });
            
            html += '</div></div>';
            return html;
        }
        
        // Generate default widget
        function generateDefaultWidget(key, widget) {
            let html = '<div class="widget">';
            html += '<div class="widget-header"><h6>' + widget.title + '</h6></div>';
            html += '<div class="widget-body">';
            html += '<p>Widget content for ' + widget.title + '</p>';
            html += '</div></div>';
            return html;
        }
        
        // Get activity icon
        function getActivityIcon(type) {
            const icons = {
                'member': 'user',
                'loan': 'hand-holding-usd',
                'payment': 'money-bill-wave'
            };
            return icons[type] || 'circle';
        }
        
        // Initialize event listeners
        function initializeEventListeners() {
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                const userMenu = document.getElementById('userMenu');
                if (userMenu && !e.target.closest('.user-dropdown')) {
                    userMenu.style.display = 'none';
                }
            });
            
            // Handle keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl+R for refresh
                if (e.ctrlKey && e.key === 'r') {
                    e.preventDefault();
                    refreshDashboard();
                }
                
                // Ctrl+L for logout
                if (e.ctrlKey && e.key === 'l') {
                    e.preventDefault();
                    logout();
                }
            });
        }
        
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }
        
        // Toggle user menu
        function toggleUserMenu() {
            const userMenu = document.getElementById('userMenu');
            userMenu.style.display = userMenu.style.display === 'none' ? 'block' : 'none';
        }
        
        // Navigate to page
        function navigateTo(page, event) {
            if (event) {
                event.preventDefault();
            }
            
            // Remove active class from all menu items
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to clicked item
            if (event && event.target) {
                event.target.closest('.menu-item').classList.add('active');
            }
            
            // Load page content dynamically
            loadPageContent(page);
            
            // Update URL hash without page reload
            window.location.hash = page;
        }
        
        // Load page content dynamically
        function loadPageContent(page) {
            const appMain = document.querySelector('.app-main');
            const widgetsContainer = document.getElementById('dashboardWidgets');
            
            // Show loading state
            if (widgetsContainer) {
                widgetsContainer.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><div class="mt-3">Memuat halaman...</div></div>';
            }
            
            // Page content mapping
            const pageContents = {
                'dashboard': {
                    title: 'Dashboard',
                    subtitle: 'Dashboard <?php echo htmlspecialchars(ucfirst($userRole)); ?> - <?php echo date("d F Y"); ?>',
                    content: generateDashboardContent()
                },
                'laporan': {
                    title: 'Laporan Keuangan',
                    subtitle: 'Analisis dan laporan keuangan koperasi',
                    content: generateLaporanContent()
                },
                'nasabah': {
                    title: 'Data Nasabah',
                    subtitle: 'Manajemen data anggota koperasi',
                    content: generateNasabahContent()
                },
                'pinjaman': {
                    title: 'Pinjaman',
                    subtitle: 'Manajemen pinjaman dan angsuran',
                    content: generatePinjamanContent()
                },
                'simpanan': {
                    title: 'Simpanan',
                    subtitle: 'Manajemen simpanan anggota',
                    content: generateSimpananContent()
                },
                'pengaturan': {
                    title: 'Pengaturan',
                    subtitle: 'Pengaturan sistem koperasi',
                    content: generatePengaturanContent()
                },
                'setoran': {
                    title: 'Setoran',
                    subtitle: 'Proses setoran simpanan anggota',
                    content: generateSetoranContent()
                },
                'penarikan': {
                    title: 'Penarikan',
                    subtitle: 'Proses penarikan simpanan',
                    content: generatePenarikanContent()
                },
                'pembayaran': {
                    title: 'Pembayaran',
                    subtitle: 'Pembayaran angsuran pinjaman',
                    content: generatePembayaranContent()
                },
                'profil': {
                    title: 'Profil Saya',
                    subtitle: 'Data pribadi dan informasi akun',
                    content: generateProfilContent()
                },
                'transaksi': {
                    title: 'Transaksi',
                    subtitle: 'Manajemen transaksi harian',
                    content: generateTransaksiContent()
                },
                'laporan_harian': {
                    title: 'Laporan Harian',
                    subtitle: 'Laporan transaksi harian',
                    content: generateLaporanHarianContent()
                },
                'simpanan_saya': {
                    title: 'Simpanan Saya',
                    subtitle: 'Informasi simpanan pribadi',
                    content: generateSimpananSayaContent()
                },
                'pinjaman_saya': {
                    title: 'Pinjaman Saya',
                    subtitle: 'Status pinjaman pribadi',
                    content: generatePinjamanSayaContent()
                },
                'riwayat': {
                    title: 'Riwayat Transaksi',
                    subtitle: 'Histori transaksi pribadi',
                    content: generateRiwayatContent()
                },
                'jadwal': {
                    title: 'Jadwal Kutipan',
                    subtitle: 'Jadwal kunjungan harian',
                    content: generateJadwalContent()
                },
                'rute': {
                    title: 'Rute Hari Ini',
                    subtitle: 'Rute kunjungan petugas lapangan',
                    content: generateRuteContent()
                },
                'nasabah_kunjungan': {
                    title: 'Nasabah Kunjungan',
                    subtitle: 'Daftar nasabah yang akan dikunjungi',
                    content: generateNasabahKunjunganContent()
                },
                'kutipan': {
                    title: 'Kutipan',
                    subtitle: 'Form kutipan pembayaran',
                    content: generateKutipanContent()
                },
                'gps_log': {
                    title: 'GPS Log',
                    subtitle: 'Riwayat lokasi kunjungan',
                    content: generateGpsLogContent()
                }
            };
            
            // Get page content or default to dashboard
            const pageData = pageContents[page] || pageContents['dashboard'];
            
            // Update dashboard header
            const dashboardHeader = appMain.querySelector('.dashboard-header');
            if (dashboardHeader) {
                dashboardHeader.innerHTML = `
                    <h1>${pageData.title}</h1>
                    <p>${pageData.subtitle}</p>
                `;
            }
            
            // Update main content
            if (widgetsContainer) {
                widgetsContainer.innerHTML = pageData.content;
            }
            
            // Show notification
            showNotification(`Halaman ${pageData.title} dimuat`, 'success');
        }
        
        // Generate content functions
        function generateDashboardContent() {
            // Generate proper dashboard content (not from existing widgets)
            const userRole = '<?php echo $userRole; ?>';
            
            const dashboardContent = {
                'bos': `
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Anggota</h5>
                                    <h3>150</h3>
                                    <small>+12% dari bulan lalu</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Simpanan</h5>
                                    <h3>Rp 250Jt</h3>
                                    <small>+15% dari bulan lalu</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Pinjaman Aktif</h5>
                                    <h3>45</h3>
                                    <small>+8% dari bulan lalu</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Omzet</h5>
                                    <h3>Rp 450Jt</h3>
                                    <small>+18% dari bulan lalu</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Grafik Pertumbuhan</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="growthChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Aktivitas Terbaru</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item">
                                            <small class="text-muted">2 jam lalu</small><br>
                                            Anggota baru: Budi Santoso
                                        </div>
                                        <div class="list-group-item">
                                            <small class="text-muted">3 jam lalu</small><br>
                                            Pinjaman disetujui: Rp 10Jt
                                        </div>
                                        <div class="list-group-item">
                                            <small class="text-muted">5 jam lalu</small><br>
                                            Simpanan masuk: Rp 5Jt
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                'admin': `
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Anggota Aktif</h5>
                                    <h3>125</h3>
                                    <small>+8% dari bulan lalu</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Transaksi Hari Ini</h5>
                                    <h3>45</h3>
                                    <small>+10% dari kemarin</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Pinjaman Pending</h5>
                                    <h3>12</h3>
                                    <small>-15% dari minggu lalu</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">User Terdaftar</h5>
                                    <h3>180</h3>
                                    <small>+6% dari bulan lalu</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Statistik Operasional</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="operationalChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Task List</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item">
                                            <input type="checkbox" class="form-check-input me-2"> Review 3 pinjaman pending
                                        </div>
                                        <div class="list-group-item">
                                            <input type="checkbox" class="form-check-input me-2"> Update data anggota baru
                                        </div>
                                        <div class="list-group-item">
                                            <input type="checkbox" class="form-check-input me-2"> Backup database mingguan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                'teller': `
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Transaksi Hari Ini</h5>
                                    <h3>28</h3>
                                    <small>+12% dari kemarin</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Setoran</h5>
                                    <h3>Rp 15Jt</h3>
                                    <small>+8% dari kemarin</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Penarikan</h5>
                                    <h3>Rp 8Jt</h3>
                                    <small>-5% dari kemarin</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Nasabah Dilayani</h5>
                                    <h3>35</h3>
                                    <small>+15% dari kemarin</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Transaksi Terkini</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Waktu</th>
                                                    <th>Nasabah</th>
                                                    <th>Jenis</th>
                                                    <th>Jumlah</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>09:15</td>
                                                    <td>Ahmad</td>
                                                    <td><span class="badge bg-success">Setoran</span></td>
                                                    <td>Rp 2Jt</td>
                                                </tr>
                                                <tr>
                                                    <td>09:30</td>
                                                    <td>Siti</td>
                                                    <td><span class="badge bg-danger">Penarikan</span></td>
                                                    <td>Rp 500rb</td>
                                                </tr>
                                                <tr>
                                                    <td>10:00</td>
                                                    <td>Budi</td>
                                                    <td><span class="badge bg-success">Setoran</span></td>
                                                    <td>Rp 1Jt</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-primary" onclick="navigateTo('setoran')">
                                            <i class="fas fa-plus me-2"></i>Setoran Baru
                                        </button>
                                        <button class="btn btn-warning" onclick="navigateTo('penarikan')">
                                            <i class="fas fa-minus me-2"></i>Penarikan Baru
                                        </button>
                                        <button class="btn btn-success" onclick="navigateTo('pembayaran')">
                                            <i class="fas fa-money-bill me-2"></i>Pembayaran
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                'collector': `
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Target Hari Ini</h5>
                                    <h3>15</h3>
                                    <small>0% dari target</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Kunjungan Selesai</h5>
                                    <h3>8</h3>
                                    <small>+53% dari kemarin</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Kutipan Terkumpul</h5>
                                    <h3>Rp 2.5Jt</h3>
                                    <small>+18% dari kemarin</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Efisiensi</h5>
                                    <h3>85%</h3>
                                    <small>+5% dari kemarin</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Rute Hari Ini</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Rute A - Kelurahan A</h6>
                                                    <small>8 nasabah • 5 selesai • 3 tersisa</small>
                                                </div>
                                                <div class="progress" style="width: 100px; height: 20px;">
                                                    <div class="progress-bar bg-success" style="width: 62.5%"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Rute B - Kelurahan B</h6>
                                                    <small>7 nasabah • 3 selesai • 4 tersisa</small>
                                                </div>
                                                <div class="progress" style="width: 100px; height: 20px;">
                                                    <div class="progress-bar bg-warning" style="width: 42.8%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Next Action</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        <strong>Nasabah Berikutnya:</strong><br>
                                        Pak Haji Ahmad<br>
                                        <small>Jl. Merdeka No. 45 • 500m</small>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-primary" onclick="navigateTo('kutipan')">
                                            <i class="fas fa-money-bill-wave me-2"></i>Form Kutipan
                                        </button>
                                        <button class="btn btn-success" onclick="navigateTo('rute')">
                                            <i class="fas fa-route me-2"></i>Lihat Rute
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                'nasabah': `
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Saldo Simpanan</h5>
                                    <h3>Rp 5Jt</h3>
                                    <small>+2% dari bulan lalu</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Simpanan Wajib</h5>
                                    <h3>Rp 2Jt</h3>
                                    <small>+5% dari bulan lalu</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Pinjaman Aktif</h5>
                                    <h3>Rp 10Jt</h3>
                                    <small>0% dari bulan lalu</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Cicilan Bulanan</h5>
                                    <h3>Rp 500rb</h3>
                                    <small>Due: 25 Maret</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Ringkasan Akun</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Jenis Simpanan</th>
                                                    <th>Saldo</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Simpanan Pokok</td>
                                                    <td>Rp 100rb</td>
                                                    <td><span class="badge bg-success">Aktif</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Simpanan Wajib</td>
                                                    <td>Rp 2Jt</td>
                                                    <td><span class="badge bg-success">Aktif</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Simpanan Sukarela</td>
                                                    <td>Rp 2.9Jt</td>
                                                    <td><span class="badge bg-success">Aktif</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-primary" onclick="navigateTo('simpanan_saya')">
                                            <i class="fas fa-piggy-bank me-2"></i>Detail Simpanan
                                        </button>
                                        <button class="btn btn-warning" onclick="navigateTo('pinjaman_saya')">
                                            <i class="fas fa-hand-holding-usd me-2"></i>Detail Pinjaman
                                        </button>
                                        <button class="btn btn-success" onclick="navigateTo('riwayat')">
                                            <i class="fas fa-history me-2"></i>Riwayat Transaksi
                                        </button>
                                        <button class="btn btn-info" onclick="navigateTo('pembayaran')">
                                            <i class="fas fa-credit-card me-2"></i>Bayar Cicilan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `
            };
            
            return dashboardContent[userRole] || dashboardContent['bos'];
        }
        
        function generateLaporanContent() {
            return `
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="fas fa-chart-line me-2"></i>Laporan Keuangan</h5>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary active">Bulanan</button>
                                    <button class="btn btn-outline-primary">Tahunan</button>
                                    <button class="btn btn-outline-primary">Custom</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body text-center">
                                                <h6>Pendapatan Bulan Ini</h6>
                                                <h3>Rp 45.2Jt</h3>
                                                <small>+12% dari bulan lalu</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h6>Total Simpanan</h6>
                                                <h3>Rp 250Jt</h3>
                                                <small>+8% dari bulan lalu</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body text-center">
                                                <h6>Pinjaman Disalurkan</h6>
                                                <h3>Rp 180Jt</h3>
                                                <small>+5% dari bulan lalu</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-info text-white">
                                            <div class="card-body text-center">
                                                <h6>Laba Bersih</h6>
                                                <h3>Rp 12.5Jt</h3>
                                                <small>+15% dari bulan lalu</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6>Grafik Pertumbuhan Keuangan</h6>
                                        <div class="card">
                                            <div class="card-body">
                                                <canvas id="financialChart" height="100"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Distribusi Aset</h6>
                                        <div class="card">
                                            <div class="card-body">
                                                <canvas id="assetChart" height="100"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6>Detail Laporan Keuangan</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Kategori</th>
                                                                <th>Januari</th>
                                                                <th>Februari</th>
                                                                <th>Maret</th>
                                                                <th>Total Q1</th>
                                                                <th>% Growth</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>Simpanan Anggota</td>
                                                                <td>Rp 200Jt</td>
                                                                <td>Rp 225Jt</td>
                                                                <td>Rp 250Jt</td>
                                                                <td>Rp 675Jt</td>
                                                                <td class="text-success">+25%</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Pinjaman Beredar</td>
                                                                <td>Rp 150Jt</td>
                                                                <td>Rp 165Jt</td>
                                                                <td>Rp 180Jt</td>
                                                                <td>Rp 495Jt</td>
                                                                <td class="text-success">+20%</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Pendapatan Bunga</td>
                                                                <td>Rp 8.5Jt</td>
                                                                <td>Rp 9.2Jt</td>
                                                                <td>Rp 10.1Jt</td>
                                                                <td>Rp 27.8Jt</td>
                                                                <td class="text-success">+18%</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Biaya Operasional</td>
                                                                <td>Rp 3.2Jt</td>
                                                                <td>Rp 3.5Jt</td>
                                                                <td>Rp 3.8Jt</td>
                                                                <td>Rp 10.5Jt</td>
                                                                <td class="text-danger">+19%</td>
                                                            </tr>
                                                            <tr class="table-success">
                                                                <td><strong>Laba Bersih</strong></td>
                                                                <td><strong>Rp 5.3Jt</strong></td>
                                                                <td><strong>Rp 5.7Jt</strong></td>
                                                                <td><strong>Rp 6.3Jt</strong></td>
                                                                <td><strong>Rp 17.3Jt</strong></td>
                                                                <td class="text-success"><strong>+19%</strong></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-4">
                                    <div class="col-md-12 text-center">
                                        <button class="btn btn-primary me-2">
                                            <i class="fas fa-download me-2"></i>Export PDF
                                        </button>
                                        <button class="btn btn-success me-2">
                                            <i class="fas fa-file-excel me-2"></i>Export Excel
                                        </button>
                                        <button class="btn btn-info">
                                            <i class="fas fa-print me-2"></i>Cetak Laporan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateNasabahContent() {
            return `
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="fas fa-users me-2"></i>Data Nasabah</h5>
                                <div class="d-flex gap-2">
                                    <input type="text" class="form-control form-control-sm" placeholder="Cari nasabah..." style="width: 200px;">
                                    <button class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i>Tambah Nasabah
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body text-center">
                                                <h6>Total Nasabah</h6>
                                                <h3>150</h3>
                                                <small>+12% dari bulan lalu</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h6>Nasabah Aktif</h6>
                                                <h3>125</h3>
                                                <small>+8% dari bulan lalu</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body text-center">
                                                <h6>Nasabah Baru</h6>
                                                <h3>8</h3>
                                                <small>+25% dari bulan lalu</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-info text-white">
                                            <div class="card-body text-center">
                                                <h6>Pinjaman Aktif</h6>
                                                <h3>45</h3>
                                                <small>+5% dari bulan lalu</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nama</th>
                                                <th>Alamat</th>
                                                <th>Telepon</th>
                                                <th>Status</th>
                                                <th>Simpanan</th>
                                                <th>Pinjaman</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>NSB001</td>
                                                <td>Budi Santoso</td>
                                                <td>Jl. Merdeka No. 45</td>
                                                <td>08123456789</td>
                                                <td><span class="badge bg-success">Aktif</span></td>
                                                <td>Rp 5Jt</td>
                                                <td>Rp 10Jt</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary"><i class="fas fa-eye"></i></button>
                                                        <button class="btn btn-outline-warning"><i class="fas fa-edit"></i></button>
                                                        <button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>NSB002</td>
                                                <td>Siti Nurhaliza</td>
                                                <td>Jl. Sudirman No. 12</td>
                                                <td>08234567890</td>
                                                <td><span class="badge bg-success">Aktif</span></td>
                                                <td>Rp 3Jt</td>
                                                <td>Rp 0</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary"><i class="fas fa-eye"></i></button>
                                                        <button class="btn btn-outline-warning"><i class="fas fa-edit"></i></button>
                                                        <button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>NSB003</td>
                                                <td>Ahmad Fauzi</td>
                                                <td>Jl. Gatot Subroto No. 78</td>
                                                <td>08345678901</td>
                                                <td><span class="badge bg-warning">Tidak Aktif</span></td>
                                                <td>Rp 2Jt</td>
                                                <td>Rp 5Jt</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary"><i class="fas fa-eye"></i></button>
                                                        <button class="btn btn-outline-warning"><i class="fas fa-edit"></i></button>
                                                        <button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <nav>
                                            <ul class="pagination pagination-sm">
                                                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                                <li class="page-item"><a class="page-link" href="#">Next</a></li>
                                            </ul>
                                        </nav>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <button class="btn btn-success me-2">
                                            <i class="fas fa-file-excel me-2"></i>Export Excel
                                        </button>
                                        <button class="btn btn-primary">
                                            <i class="fas fa-download me-2"></i>Export PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generatePinjamanContent() {
            return `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Coming Soon:</strong> Halaman pinjaman sedang dalam pengembangan.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-hand-holding-usd me-2"></i>Manajemen Pinjaman</h5>
                            </div>
                            <div class="card-body">
                                <p>Fitur yang akan tersedia:</p>
                                <ul>
                                    <li>Pengajuan pinjaman</li>
                                    <li>Approval pinjaman</li>
                                    <li>Jadwal angsuran</li>
                                    <li>Pelunasan pinjaman</li>
                                    <li>Laporan pinjaman</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateSimpananContent() {
            return `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Coming Soon:</strong> Halaman simpanan sedang dalam pengembangan.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-piggy-bank me-2"></i>Manajemen Simpanan</h5>
                            </div>
                            <div class="card-body">
                                <p>Fitur yang akan tersedia:</p>
                                <ul>
                                    <li>Jenis simpanan (wajib, sukarela, berjangka)</li>
                                    <li>Setoran dan penarikan</li>
                                    <li>Bunga simpanan</li>
                                    <li>Laporan simpanan</li>
                                    <li>Analisis simpanan</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generatePengaturanContent() {
            return `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Coming Soon:</strong> Halaman pengaturan sedang dalam pengembangan.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-cog me-2"></i>Pengaturan Sistem</h5>
                            </div>
                            <div class="card-body">
                                <p>Fitur yang akan tersedia:</p>
                                <ul>
                                    <li>Pengaturan aplikasi</li>
                                    <li>Manajemen user</li>
                                    <li>Backup & restore</li>
                                    <li>System configuration</li>
                                    <li>Audit log</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateSetoranContent() {
            return `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Coming Soon:</strong> Halaman setoran sedang dalam pengembangan.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-plus-circle me-2"></i>Form Setoran</h5>
                            </div>
                            <div class="card-body">
                                <p>Fitur yang akan tersedia:</p>
                                <ul>
                                    <li>Pencarian anggota berdasarkan nomor rekening atau nama</li>
                                    <li>Form setoran dengan validasi otomatis</li>
                                    <li>Cetak bukti setoran</li>
                                    <li>Integrasi dengan sistem kas</li>
                                    <li>Laporan transaksi harian</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generatePenarikanContent() {
            return `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Coming Soon:</strong> Halaman penarikan sedang dalam pengembangan.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-minus-circle me-2"></i>Form Penarikan</h5>
                            </div>
                            <div class="card-body">
                                <p>Fitur yang akan tersedia:</p>
                                <ul>
                                    <li>Validasi saldo penarikan</li>
                                    <li>Otorisasi penarikan</li>
                                    <li>Cetak bukti penarikan</li>
                                    <li>Batas penarikan harian</li>
                                    <li>Laporan penarikan</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generatePembayaranContent() {
            return `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Coming Soon:</strong> Halaman pembayaran sedang dalam pengembangan.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-credit-card me-2"></i>Pembayaran Angsuran</h5>
                            </div>
                            <div class="card-body">
                                <p>Fitur yang akan tersedia:</p>
                                <ul>
                                    <li>Pembayaran angsuran pinjaman</li>
                                    <li>Perhitungan denda</li>
                                    <li>Cetak bukti pembayaran</li>
                                    <li>Update jadwal angsuran</li>
                                    <li>Laporan pembayaran</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateProfilContent() {
            return `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Coming Soon:</strong> Halaman profil sedang dalam pengembangan.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-user me-2"></i>Informasi Profil</h5>
                            </div>
                            <div class="card-body">
                                <p>Fitur yang akan tersedia:</p>
                                <ul>
                                    <li>Data pribadi lengkap</li>
                                    <li>Informasi rekening simpanan</li>
                                    <li>Status pinjaman aktif</li>
                                    <li>Update profil dan password</li>
                                    <li>Dokumen pribadi</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Additional content generators for all roles
        function generateTransaksiContent() {
            return `
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="fas fa-exchange-alt me-2"></i>Manajemen Transaksi</h5>
                                <div class="d-flex gap-2">
                                    <select class="form-select form-select-sm" style="width: 150px;">
                                        <option>Semua Jenis</option>
                                        <option>Setoran</option>
                                        <option>Penarikan</option>
                                        <option>Pinjaman</option>
                                        <option>Pembayaran</option>
                                    </select>
                                    <input type="date" class="form-control form-control-sm" style="width: 150px;">
                                    <button class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i>Transaksi Baru
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h6>Transaksi Hari Ini</h6>
                                                <h3>28</h3>
                                                <small>+12% dari kemarin</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body text-center">
                                                <h6>Total Setoran</h6>
                                                <h3>Rp 15Jt</h3>
                                                <small>+8% dari kemarin</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body text-center">
                                                <h6>Total Penarikan</h6>
                                                <h3>Rp 8Jt</h3>
                                                <small>-5% dari kemarin</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-info text-white">
                                            <div class="card-body text-center">
                                                <h6>Nilai Transaksi</h6>
                                                <h3>Rp 23Jt</h3>
                                                <small>+3% dari kemarin</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Waktu</th>
                                                <th>Nasabah</th>
                                                <th>Jenis</th>
                                                <th>Jumlah</th>
                                                <th>Metode</th>
                                                <th>Kasir</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>TRX001</td>
                                                <td>09:15</td>
                                                <td>Budi Santoso</td>
                                                <td><span class="badge bg-success">Setoran</span></td>
                                                <td>Rp 2.000.000</td>
                                                <td>Tunai</td>
                                                <td>Teller 1</td>
                                                <td><span class="badge bg-success">Sukses</span></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary"><i class="fas fa-receipt"></i></button>
                                                        <button class="btn btn-outline-info"><i class="fas fa-print"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>TRX002</td>
                                                <td>09:30</td>
                                                <td>Siti Nurhaliza</td>
                                                <td><span class="badge bg-danger">Penarikan</span></td>
                                                <td>Rp 500.000</td>
                                                <td>Transfer</td>
                                                <td>Teller 2</td>
                                                <td><span class="badge bg-success">Sukses</span></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary"><i class="fas fa-receipt"></i></button>
                                                        <button class="btn btn-outline-info"><i class="fas fa-print"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>TRX003</td>
                                                <td>10:00</td>
                                                <td>Ahmad Fauzi</td>
                                                <td><span class="badge bg-warning">Pinjaman</span></td>
                                                <td>Rp 5.000.000</td>
                                                <td>Transfer</td>
                                                <td>Teller 1</td>
                                                <td><span class="badge bg-warning">Pending</span></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-success"><i class="fas fa-check"></i></button>
                                                        <button class="btn btn-outline-danger"><i class="fas fa-times"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <nav>
                                            <ul class="pagination pagination-sm">
                                                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                                <li class="page-item"><a class="page-link" href="#">Next</a></li>
                                            </ul>
                                        </nav>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <button class="btn btn-success me-2">
                                            <i class="fas fa-file-excel me-2"></i>Export Excel
                                        </button>
                                        <button class="btn btn-primary">
                                            <i class="fas fa-download me-2"></i>Export PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateLaporanHarianContent() {
            return `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Coming Soon:</strong> Halaman laporan harian sedang dalam pengembangan.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-clipboard-list me-2"></i>Laporan Harian</h5>
                            </div>
                            <div class="card-body">
                                <p>Fitur yang akan tersedia:</p>
                                <ul>
                                    <li>Ringkasan transaksi harian</li>
                                    <li>Laporan kas harian</li>
                                    <li>Statistik transaksi</li>
                                    <li>Export laporan harian</li>
                                    <li>Grafik transaksi</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateSimpananSayaContent() {
            return `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Coming Soon:</strong> Halaman simpanan pribadi sedang dalam pengembangan.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-piggy-bank me-2"></i>Simpanan Saya</h5>
                            </div>
                            <div class="card-body">
                                <p>Fitur yang akan tersedia:</p>
                                <ul>
                                    <li>Saldo simpanan</li>
                                    <li>Riwayat setoran/penarikan</li>
                                    <li>Bunga simpanan</li>
                                    <li>Permintaan penarikan online</li>
                                    <li>Laporan simpanan pribadi</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generatePinjamanSayaContent() {
            return `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Coming Soon:</strong> Halaman pinjaman pribadi sedang dalam pengembangan.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-hand-holding-usd me-2"></i>Pinjaman Saya</h5>
                            </div>
                            <div class="card-body">
                                <p>Fitur yang akan tersedia:</p>
                                <ul>
                                    <li>Status pinjaman aktif</li>
                                    <li>Jadwal angsuran</li>
                                    <li>Riwayat pembayaran</li>
                                    <li>Pengajuan pinjaman online</li>
                                    <li>Laporan pinjaman pribadi</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateRiwayatContent() {
            return `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Coming Soon:</strong> Halaman riwayat transaksi sedang dalam pengembangan.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-history me-2"></i>Riwayat Transaksi</h5>
                            </div>
                            <div class="card-body">
                                <p>Fitur yang akan tersedia:</p>
                                <ul>
                                    <li>Histori semua transaksi</li>
                                    <li>Filter berdasarkan tanggal</li>
                                    <li>Filter berdasarkan jenis transaksi</li>
                                    <li>Export riwayat transaksi</li>
                                    <li>Cetak bukti transaksi</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateJadwalContent() {
            return `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Coming Soon:</strong> Halaman jadwal kutipan sedang dalam pengembangan.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-calendar-alt me-2"></i>Jadwal Kutipan</h5>
                            </div>
                            <div class="card-body">
                                <p>Fitur yang akan tersedia:</p>
                                <ul>
                                    <li>Jadwal kunjungan harian</li>
                                    <li>Daftar nasabah yang akan dikunjungi</li>
                                    <li>Optimasi rute kunjungan</li>
                                    <li>Notifikasi jadwal</li>
                                    <li>Laporan kehadiran</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateRuteContent() {
            return `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Coming Soon:</strong> Halaman rute kunjungan sedang dalam pengembangan.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-route me-2"></i>Rute Hari Ini</h5>
                            </div>
                            <div class="card-body">
                                <p>Fitur yang akan tersedia:</p>
                                <ul>
                                    <li>Peta rute kunjungan</li>
                                    <li>Informasi lokasi nasabah</li>
                                    <li>Estimasi waktu perjalanan</li>
                                    <li>Progress kunjungan</li>
                                    <li>GPS tracking real-time</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateNasabahKunjunganContent() {
            return `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Coming Soon:</strong> Halaman nasabah kunjungan sedang dalam pengembangan.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-user-friends me-2"></i>Nasabah Kunjungan</h5>
                            </div>
                            <div class="card-body">
                                <p>Fitur yang akan tersedia:</p>
                                <ul>
                                    <li>Daftar nasabah yang akan dikunjungi</li>
                                    <li>Informasi kontak nasabah</li>
                                    <li>Catatan kunjungan</li>
                                    <li>Status pembayaran</li>
                                    <li>Histori kunjungan</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateKutipanContent() {
            return `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Coming Soon:</strong> Halaman kutipan sedang dalam pengembangan.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-money-bill-wave me-2"></i>Form Kutipan</h5>
                            </div>
                            <div class="card-body">
                                <p>Fitur yang akan tersedia:</p>
                                <ul>
                                    <li>Form pembayaran angsuran</li>
                                    <li>Validasi pembayaran</li>
                                    <li>Cetak bukti pembayaran</li>
                                    <li>Update jadwal angsuran</li>
                                    <li>Laporan kutipan harian</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateGpsLogContent() {
            return `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Coming Soon:</strong> Halaman GPS log sedang dalam pengembangan.
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-map-marked-alt me-2"></i>GPS Log</h5>
                            </div>
                            <div class="card-body">
                                <p>Fitur yang akan tersedia:</p>
                                <ul>
                                    <li>Riwayat lokasi kunjungan</li>
                                    <li>Peta jejak perjalanan</li>
                                    <li>Waktu kunjungan per nasabah</li>
                                    <li>Export GPS data</li>
                                    <li>Laporan kehadiran</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Logout function
        function logout() {
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                // Send logout request to server
                fetch('api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=logout'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect to login page
                        window.location.href = 'login.php';
                    } else {
                        // Show error message
                        showNotification('Logout gagal: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    // Fallback: redirect anyway
                    window.location.href = 'login.php';
                });
            }
        }
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.getElementById('sidebar').classList.remove('show');
            }
        });
    </script>
</body>
</html>
