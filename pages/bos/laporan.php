<?php
/**
 * KSP Lam Gabe Jaya - Laporan Keuangan (BOS)
 * BOS-only financial reports page
 */

// Security headers
header('Content-Type: text/html; charset=UTF-8');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

// Load required files
require_once __DIR__ . '/../../config/constants.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication check
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header('Location: /mono-v2/login.php');
    exit;
}

$user = $_SESSION['user'];
$userRole = $user['role'] ?? 'nasabah';
$userName = $user['full_name'] ?? $user['username'];

// Role check
if ($userRole !== 'bos') {
    header('Location: /mono-v2/?page=dashboard');
    exit;
}

$pageTitle = 'Laporan Keuangan - ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/mono-v2/assets/css/dashboard.css">
    
    <!-- Security Headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' 'unsafe-inline' 'unsafe-eval'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/; img-src 'self' data: https:; connect-src 'self' https://cdn.jsdelivr.net; base-uri 'self'; form-action 'self'">
    
    <style>
        .user-dropdown {
            position: relative;
        }
        
        .user-dropdown .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            min-width: 200px;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            z-index: 1050;
            margin-top: 8px;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.2s ease;
            pointer-events: none;
        }
        
        .user-dropdown .dropdown-menu.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }
        
        /* Smart positioning for small screens */
        @media (max-width: 768px) {
            .user-dropdown .dropdown-menu {
                right: -50px;
                min-width: 180px;
            }
        }
        
        @media (max-width: 480px) {
            .user-dropdown .dropdown-menu {
                right: -80px;
                min-width: 160px;
            }
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary-color, #007bff);
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
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <div class="header-left">
                <button class="btn btn-link" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <a href="/mono-v2/?page=dashboard" class="brand">
                    <i class="fas fa-university"></i>
                    KSP Lam Gabe Jaya
                </a>
            </div>
            
            <div class="header-right">
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
                <a href="/mono-v2/?page=dashboard" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="/mono-v2/?page=laporan" class="menu-item active">
                    <i class="fas fa-chart-line"></i>
                    Laporan Keuangan
                </a>
                <a href="/mono-v2/?page=nasabah" class="menu-item">
                    <i class="fas fa-users"></i>
                    Data Nasabah
                </a>
                <a href="/mono-v2/?page=pinjaman" class="menu-item">
                    <i class="fas fa-hand-holding-usd"></i>
                    Pinjaman
                </a>
                <a href="/mono-v2/?page=simpanan" class="menu-item">
                    <i class="fas fa-piggy-bank"></i>
                    Simpanan
                </a>
                <a href="/mono-v2/?page=pengaturan" class="menu-item">
                    <i class="fas fa-cog"></i>
                    Pengaturan
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="app-main">
            <div class="dashboard-header">
                <h1>Laporan Keuangan</h1>
                <p>Analisis dan laporan keuangan koperasi</p>
            </div>
            
            <div class="content-section">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Coming Soon:</strong> Halaman laporan keuangan sedang dalam pengembangan.
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-line me-2"></i>Laporan Keuangan</h5>
                            </div>
                            <div class="card-body">
                                <p>Fitur yang akan tersedia:</p>
                                <ul>
                                    <li>Laporan laba/rugi bulanan</li>
                                    <li>Laporan neraca</li>
                                    <li>Laporan arus kas</li>
                                    <li>Analisis rasio keuangan</li>
                                    <li>Grafik dan visualisasi data</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle user menu with smart positioning
        function toggleUserMenu() {
            const userMenu = document.getElementById('userMenu');
            const userDropdown = document.querySelector('.user-dropdown');
            
            if (userMenu.style.display === 'none' || userMenu.style.display === '') {
                // Show menu with smart positioning
                userMenu.style.display = 'block';
                userMenu.classList.add('show');
                
                // Check if menu is outside viewport and adjust
                setTimeout(() => {
                    const menuRect = userMenu.getBoundingClientRect();
                    const viewportWidth = window.innerWidth;
                    
                    // If menu goes beyond right edge
                    if (menuRect.right > viewportWidth) {
                        const overflow = menuRect.right - viewportWidth;
                        userMenu.style.right = 'auto';
                        userMenu.style.left = `-${overflow + 10}px`;
                    }
                    
                    // If menu goes beyond left edge
                    if (menuRect.left < 0) {
                        userMenu.style.left = '0';
                        userMenu.style.right = 'auto';
                    }
                }, 10);
            } else {
                // Hide menu
                userMenu.style.display = 'none';
                userMenu.classList.remove('show');
            }
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            const userMenu = document.getElementById('userMenu');
            if (userMenu && !e.target.closest('.user-dropdown')) {
                userMenu.style.display = 'none';
                userMenu.classList.remove('show');
            }
        });
        
        // Reposition dropdown on window resize
        window.addEventListener('resize', function() {
            const userMenu = document.getElementById('userMenu');
            if (userMenu && userMenu.style.display === 'block') {
                // Temporarily hide and show to reposition
                userMenu.style.display = 'none';
                setTimeout(() => {
                    toggleUserMenu();
                    toggleUserMenu();
                }, 10);
            }
        });
        
        // Placeholder functions
        function showProfile() {
            alert('Fitur profil akan segera tersedia');
        }
        
        function showSettings() {
            alert('Fitur pengaturan akan segera tersedia');
        }
        
        function logout() {
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                window.location.href = '/mono-v2/?action=logout';
            }
        }
    </script>
</body>
</html>
