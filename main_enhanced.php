<?php
/**
 * KSP Lam Gabe Jaya - Enhanced Main Dashboard Page
 * Based on OOP best practices and documentation
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
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/UserManager.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize authentication
$auth = new Auth();

// Authentication check with enhanced security
try {
    $authCheck = $auth->checkAuth();
    
    if (!$authCheck['authenticated']) {
        error_log("Authentication failed in main.php: " . $authCheck['reason']);
        header('Location: /mono-v2/login.php');
        exit;
    }
    
    $user = $authCheck['user'];
    
} catch (Exception $e) {
    error_log("Authentication error in main.php: " . $e->getMessage());
    header('Location: /mono-v2/login.php');
    exit;
}

// Initialize managers
$userManager = new UserManager();
$dashboardManager = new DashboardManager($user);

// Get user data and permissions
$userRole = $user['role'];
$userName = $user['full_name'] ?? $user['username'];
$userDisplayName = $user['role_display_name'];
$permissions = $user['permissions'];

// Get dashboard configuration
$dashboardLayout = $dashboardManager->getDashboardLayout($userRole);
$menuItems = $dashboardManager->getMenuItems($userRole);
$widgets = $dashboardManager->getDashboardWidgets($userRole);

// Page metadata
$pageTitle = 'Dashboard - ' . APP_NAME;
$pageDescription = 'Sistem Koperasi Digital Terpadu';

// Get additional data for dashboard
$recentActivities = $userManager->getRecentActivities($user['id']);
$notifications = $userManager->getNotifications($user['id']);
$systemInfo = $userManager->getSystemInfo();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/dashboard.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.webmanifest">
    <meta name="theme-color" content="#007bff">
    
    <!-- Security Headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' https://cdn.jsdelivr.net; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;">
</head>
<body>
    <!-- Header -->
    <header class="dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <div class="brand">
                        <img src="assets/img/logo.png" alt="KSP Lam Gabe Jaya" class="brand-logo">
                        <span class="brand-name">KSP Lam Gabe Jaya</span>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="header-search">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Cari data...">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="header-actions">
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                        
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <?php if (!empty($notifications)): ?>
                                    <span class="badge bg-danger"><?php echo count($notifications); ?></span>
                                <?php endif; ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (empty($notifications)): ?>
                                    <li><span class="dropdown-item-text">Tidak ada notifikasi</span></li>
                                <?php else: ?>
                                    <?php foreach ($notifications as $notification): ?>
                                        <li>
                                            <a class="dropdown-item" href="#">
                                                <i class="fas fa-<?php echo $notification['icon']; ?> me-2"></i>
                                                <?php echo htmlspecialchars($notification['message']); ?>
                                                <small class="text-muted d-block"><?php echo $notification['time']; ?></small>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                        
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars($userName); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header"><?php echo htmlspecialchars($userDisplayName); ?></h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#profil">
                                    <i class="fas fa-user me-2"></i>Profil
                                </a></li>
                                <li><a class="dropdown-item" href="#pengaturan">
                                    <i class="fas fa-cog me-2"></i>Pengaturan
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="logout()">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <ul class="nav flex-column">
                    <?php foreach ($menuItems as $menu): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $menu['key'] === 'dashboard' ? 'active' : ''; ?>" 
                               href="<?php echo htmlspecialchars($menu['url']); ?>" 
                               onclick="navigateTo('<?php echo $menu['key']; ?>', event)">
                                <i class="<?php echo htmlspecialchars($menu['icon']); ?> me-2"></i>
                                <?php echo htmlspecialchars($menu['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            
            <!-- Sidebar Footer -->
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($userName); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($userDisplayName); ?></div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Dashboard Header -->
            <div class="dashboard-header-content">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="dashboard-title">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard <?php echo htmlspecialchars(ucfirst($userRole)); ?>
                        </h1>
                        <p class="dashboard-subtitle">
                            Selamat datang, <?php echo htmlspecialchars($userName); ?>!
                        </p>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="dashboard-stats-summary">
                            <div class="row">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo date('d'); ?></div>
                                        <div class="stat-label">Hari</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo date('M'); ?></div>
                                        <div class="stat-label">Bulan</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo date('Y'); ?></div>
                                        <div class="stat-label">Tahun</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Content Area -->
            <div id="app-main" class="app-main">
                <!-- Content will be loaded here -->
                <div class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Toast Container -->
    <div id="notification-container"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Global variables from PHP
        const currentUser = <?php echo json_encode($user); ?>;
        const userRole = '<?php echo $userRole; ?>';
        const permissions = <?php echo json_encode($permissions); ?>;
        const menuItems = <?php echo json_encode($menuItems); ?>;
        const widgets = <?php echo json_encode($widgets); ?>;
        
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
        
        // Navigate to page
        function navigateTo(page, event) {
            if (event) {
                event.preventDefault();
            }
            
            // Remove active class from all menu items
            document.querySelectorAll('.nav-link').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to clicked item
            if (event && event.target) {
                event.target.classList.add('active');
            } else {
                // Find menu item by href
                document.querySelector(`[href="#${page}"]`)?.classList.add('active');
            }
            
            // Load page content
            loadPageContent(page);
        }
        
        // Load page content
        function loadPageContent(page) {
            const appMain = document.getElementById('app-main');
            
            // Show loading
            appMain.innerHTML = `
                <div class="d-flex justify-content-center align-items-center" style="height: 400px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Load content based on page
            setTimeout(() => {
                const content = generateContentForPage(page);
                appMain.innerHTML = content;
                
                // Show notification
                showNotification(`Halaman ${page} dimuat`, 'success');
            }, 500);
        }
        
        // Generate content for page
        function generateContentForPage(page) {
            const pageGenerators = {
                'dashboard': generateDashboardContent,
                'laporan': generateLaporanContent,
                'nasabah': generateNasabahContent,
                'pinjaman': generatePinjamanContent,
                'simpanan': generateSimpananContent,
                'transaksi': generateTransaksiContent,
                'setoran': generateSetoranContent,
                'penarikan': generatePenarikanContent,
                'pembayaran': generatePembayaranContent,
                'profil': generateProfilContent,
                'pengaturan': generatePengaturanContent
            };
            
            const generator = pageGenerators[page];
            return generator ? generator() : generateDefaultContent();
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
        
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardWidgets();
            initializeEventListeners();
            checkSessionTimeout();
        });
        
        // Load dashboard widgets
        function loadDashboardWidgets() {
            const widgetsContainer = document.getElementById('dashboardWidgets');
            if (widgetsContainer) {
                // Simulate loading widgets
                setTimeout(() => {
                    let widgetsHTML = '';
                    
                    for (const [key, widget] of Object.entries(widgets)) {
                        widgetsHTML += generateWidgetHTML(key, widget);
                    }
                    
                    widgetsContainer.innerHTML = widgetsHTML;
                }, 500);
            }
        }
        
        // Initialize event listeners
        function initializeEventListeners() {
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    document.querySelector('.sidebar')?.classList.remove('show');
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
        
        // Generate widget HTML
        function generateWidgetHTML(key, widget) {
            // Implementation for generating widget HTML
            return `<div class="widget" id="widget-${key}">
                <h6>${widget.title}</h6>
                <div class="widget-content">
                    <!-- Widget content will be loaded here -->
                </div>
            </div>`;
        }
        
        // Content generation functions (placeholders for now)
        function generateDashboardContent() {
            return `
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Dashboard Overview</h5>
                            </div>
                            <div class="card-body">
                                <p>Welcome to your dashboard!</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateDefaultContent() {
            return `
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <h4>Halaman dalam pengembangan</h4>
                                <p>Halaman ini sedang dalam proses pengembangan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Placeholder functions for other content generators
        function generateLaporanContent() { return generateDefaultContent(); }
        function generateNasabahContent() { return generateDefaultContent(); }
        function generatePinjamanContent() { return generateDefaultContent(); }
        function generateSimpananContent() { return generateDefaultContent(); }
        function generateTransaksiContent() { return generateDefaultContent(); }
        function generateSetoranContent() { return generateDefaultContent(); }
        function generatePenarikanContent() { return generateDefaultContent(); }
        function generatePembayaranContent() { return generateDefaultContent(); }
        function generateProfilContent() { return generateDefaultContent(); }
        function generatePengaturanContent() { return generateDefaultContent(); }
    </script>
</body>
</html>
