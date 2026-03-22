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
require_once __DIR__ . '/api/auth.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication check
try {
    $auth = new AuthSystem();
    $user = $auth->getCurrentUser();
    
    if (!$user) {
        // Not authenticated, redirect to login
        header('Location: /login.php');
        exit;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    
} catch (Exception $e) {
    logError("Authentication error in main.php: " . $e->getMessage());
    header('Location: /login.php');
    exit;
}

// Get user role and permissions
$userRole = $user['role'];
$userName = $user['name'] ?? $user['username'];
$userAvatar = $user['avatar'] ?? null;

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
        ROLE_BOS => 'bos',
        ROLE_ADMIN => 'admin',
        ROLE_TELLER => 'teller',
        ROLE_FIELD_COLLECTOR => 'field_collector',
        ROLE_NASABAH => 'nasabah'
    ];
    
    return $layouts[$role] ?? 'nasabah';
}

function getMenuItems($role) {
    $menus = [
        'bos' => [
            ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => '#'],
            ['key' => 'laporan', 'title' => 'Laporan Keuangan', 'icon' => 'fas fa-chart-line', 'url' => '#'],
            ['key' => 'nasabah', 'title' => 'Data Nasabah', 'icon' => 'fas fa-users', 'url' => '#'],
            ['key' => 'pinjaman', 'title' => 'Pinjaman', 'icon' => 'fas fa-hand-holding-usd', 'url' => '#'],
            ['key' => 'simpanan', 'title' => 'Simpanan', 'icon' => 'fas fa-piggy-bank', 'url' => '#'],
            ['key' => 'pengaturan', 'title' => 'Pengaturan', 'icon' => 'fas fa-cog', 'url' => '#']
        ],
        'admin' => [
            ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => '#'],
            ['key' => 'nasabah', 'title' => 'Nasabah', 'icon' => 'fas fa-users', 'url' => '#'],
            ['key' => 'pinjaman', 'title' => 'Pinjaman', 'icon' => 'fas fa-hand-holding-usd', 'url' => '#'],
            ['key' => 'simpanan', 'title' => 'Simpanan', 'icon' => 'fas fa-piggy-bank', 'url' => '#'],
            ['key' => 'transaksi', 'title' => 'Transaksi', 'icon' => 'fas fa-exchange-alt', 'url' => '#'],
            ['key' => 'laporan', 'title' => 'Laporan', 'icon' => 'fas fa-chart-bar', 'url' => '#']
        ],
        'teller' => [
            ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => '#'],
            ['key' => 'nasabah', 'title' => 'Nasabah', 'icon' => 'fas fa-users', 'url' => '#'],
            ['key' => 'setoran', 'title' => 'Setoran', 'icon' => 'fas fa-plus-circle', 'url' => '#'],
            ['key' => 'penarikan', 'title' => 'Penarikan', 'icon' => 'fas fa-minus-circle', 'url' => '#'],
            ['key' => 'pembayaran', 'title' => 'Pembayaran', 'icon' => 'fas fa-credit-card', 'url' => '#'],
            ['key' => 'laporan_harian', 'title' => 'Laporan Harian', 'icon' => 'fas fa-clipboard-list', 'url' => '#']
        ],
        'field_collector' => [
            ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => '#'],
            ['key' => 'jadwal', 'title' => 'Jadwal Kutipan', 'icon' => 'fas fa-calendar-alt', 'url' => '#'],
            ['key' => 'rute', 'title' => 'Rute Hari Ini', 'icon' => 'fas fa-route', 'url' => '#'],
            ['key' => 'nasabah_kunjungan', 'title' => 'Nasabah Kunjungan', 'icon' => 'fas fa-user-friends', 'url' => '#'],
            ['key' => 'kutipan', 'title' => 'Kutipan', 'icon' => 'fas fa-money-bill-wave', 'url' => '#'],
            ['key' => 'gps_log', 'title' => 'GPS Log', 'icon' => 'fas fa-map-marked-alt', 'url' => '#']
        ],
        'nasabah' => [
            ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => '#'],
            ['key' => 'profil', 'title' => 'Profil Saya', 'icon' => 'fas fa-user', 'url' => '#'],
            ['key' => 'simpanan', 'title' => 'Simpanan Saya', 'icon' => 'fas fa-piggy-bank', 'url' => '#'],
            ['key' => 'pinjaman', 'title' => 'Pinjaman Saya', 'icon' => 'fas fa-hand-holding-usd', 'url' => '#'],
            ['key' => 'riwayat', 'title' => 'Riwayat Transaksi', 'icon' => 'fas fa-history', 'url' => '#'],
            ['key' => 'pembayaran', 'title' => 'Pembayaran', 'icon' => 'fas fa-credit-card', 'url' => '#']
        ]
    ];
    
    return $menus[$dashboardLayout] ?? $menus['nasabah'];
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
        'field_collector' => [
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
    
    return $widgets[$dashboardLayout] ?? $widgets['nasabah'];
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
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
        
        <a href="#" class="brand">
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
                        <small class="text-muted"><?php echo getRoleName($userRole); ?></small>
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
                <a href="<?php echo $item['url']; ?>" class="menu-item" onclick="navigateTo('<?php echo $item['key']; ?>')">
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
            <p>Dashboard <?php echo getRoleName($userRole); ?> - <?php echo date('d F Y'); ?></p>
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
        let userRole = <?php echo $userRole; ?>;
        
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
            const stats = {
                'overview_stats': [
                    { label: 'Total Anggota', value: '150', change: '+12%', positive: true },
                    { label: 'Pinjaman Aktif', value: '45', change: '+8%', positive: true },
                    { label: 'Total Simpanan', value: 'Rp 250Jt', change: '+15%', positive: true }
                ],
                'account_summary': [
                    { label: 'Saldo Simpanan', value: 'Rp 5Jt', change: '+2%', positive: true },
                    { label: 'Pinjaman Aktif', value: 'Rp 10Jt', change: '0%', positive: false },
                    { label: 'Cicilan Bulanan', value: 'Rp 500rb', change: '-', positive: false }
                ]
            };
            
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
            
            const widgetActions = actions[userRole <= ROLE_ADMIN ? 'admin' : 'member'] || actions['member'];
            
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
        function navigateTo(page) {
            // Remove active class from all menu items
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to clicked item
            event.target.classList.add('active');
            
            // Load page content (would typically use AJAX)
            console.log('Navigating to:', page);
            
            // For now, just show a message
            showNotification('Navigasi ke ' + page, 'info');
        }
        
        // Refresh dashboard
        function refreshDashboard() {
            loadDashboardWidgets();
            showNotification('Dashboard refreshed', 'success');
        }
        
        // Show profile
        function showProfile() {
            showNotification('Profile page coming soon', 'info');
        }
        
        // Show settings
        function showSettings() {
            showNotification('Settings page coming soon', 'info');
        }
        
        // Handle quick action
        function handleQuickAction(action) {
            showNotification('Action: ' + action, 'info');
        }
        
        // Logout
        function logout() {
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                // Send logout request
                fetch('/api/logout.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'action=logout'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear local storage
                        localStorage.removeItem('authToken');
                        sessionStorage.removeItem('authToken');
                        
                        // Redirect to login
                        window.location.href = '/login.php';
                    } else {
                        showNotification('Logout failed: ' + data.error, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    // Force redirect even on error
                    window.location.href = '/login.php';
                });
            }
        }
        
        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
            notification.style.zIndex = '9999';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
        
        // Check session timeout
        function checkSessionTimeout() {
            const sessionTimeout = 3600000; // 1 hour in milliseconds
            const lastActivity = <?php echo time(); ?> * 1000;
            
            setInterval(() => {
                const now = Date.now();
                if (now - lastActivity > sessionTimeout) {
                    showNotification('Session expired. Please login again.', 'warning');
                    setTimeout(() => {
                        window.location.href = '/login.php';
                    }, 3000);
                }
            }, 60000); // Check every minute
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
