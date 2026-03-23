<?php
/**
 * KSP Lam Gabe Jaya - Main Application Entry Point
 * Original application with fixes
 */

// Prevent direct access guard
define('IN_INDEX_PHP', true);

// Load constants
require_once __DIR__ . '/config/constants.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

// Check user role
function hasRole($requiredRole) {
    if (!isLoggedIn()) return false;
    $user = $_SESSION['user'];
    return $user['role'] === $requiredRole;
}

// Redirect to login if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /mono-v2/login.php');
        exit;
    }
}

// Get current page
$page = $_GET['page'] ?? 'dashboard';

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: /mono-v2/login.php');
    exit;
}

// Route to appropriate page
switch ($page) {
    case 'login':
        if (isLoggedIn()) {
            header('Location: /mono-v2/?page=dashboard');
            exit;
        }
        include __DIR__ . '/login.php';
        break;
        
    case 'dashboard':
        requireLogin();
        if ($userRole === 'bos') {
            include __DIR__ . '/pages/bos/dashboard.php';
        } else {
            include __DIR__ . '/main.php';
        }
        break;
        
    case 'register':
        requireLogin();
        include __DIR__ . '/pages/member/registration.php';
        break;
        
    case 'members':
        requireLogin();
        if (!hasRole('bos') && !hasRole('admin')) {
            header('Location: /mono-v2/?page=dashboard');
            exit;
        }
        include __DIR__ . '/pages/admin/members.html';
        break;
        
    case 'accounts':
        requireLogin();
        include __DIR__ . '/pages/admin/accounts.html';
        break;
        
    case 'transactions':
        requireLogin();
        include __DIR__ . '/pages/admin/transactions.html';
        break;
        
    case 'loans':
        requireLogin();
        include __DIR__ . '/pages/admin/loans.html';
        break;
        
    case 'reports':
        requireLogin();
        include __DIR__ . '/pages/admin/reports.html';
        break;
        
    // BOS Routes
    case 'laporan':
        requireLogin();
        if (!hasRole('bos')) {
            header('Location: /mono-v2/?page=dashboard');
            exit;
        }
        include __DIR__ . '/pages/bos/laporan.php';
        break;
        
    case 'pengaturan':
        requireLogin();
        if (!hasRole('bos')) {
            header('Location: /mono-v2/?page=dashboard');
            exit;
        }
        include __DIR__ . '/pages/bos/pengaturan.php';
        break;
        
    // Teller Routes
    case 'setoran':
        requireLogin();
        if (!hasRole('teller') && !hasRole('admin') && !hasRole('bos')) {
            header('Location: /mono-v2/?page=dashboard');
            exit;
        }
        include __DIR__ . '/pages/teller/setoran-enhanced.php';
        break;
        
    case 'penarikan':
        requireLogin();
        if (!hasRole('teller') && !hasRole('admin') && !hasRole('bos')) {
            header('Location: /mono-v2/?page=dashboard');
            exit;
        }
        include __DIR__ . '/pages/teller/penarikan.php';
        break;
        
    case 'pembayaran':
        requireLogin();
        if (!hasRole('teller') && !hasRole('admin') && !hasRole('bos') && !hasRole('nasabah')) {
            header('Location: /mono-v2/?page=dashboard');
            exit;
        }
        include __DIR__ . '/pages/teller/pembayaran.php';
        break;
        
    case 'laporan_harian':
        requireLogin();
        if (!hasRole('teller') && !hasRole('admin') && !hasRole('bos')) {
            header('Location: /mono-v2/?page=dashboard');
            exit;
        }
        include __DIR__ . '/pages/teller/laporan_harian.php';
        break;
        
    // Collector Routes
    case 'jadwal':
        requireLogin();
        if (!hasRole('collector') && !hasRole('admin') && !hasRole('bos')) {
            header('Location: /mono-v2/?page=dashboard');
            exit;
        }
        include __DIR__ . '/pages/collector/jadwal.php';
        break;
        
    case 'rute':
        requireLogin();
        if (!hasRole('collector') && !hasRole('admin') && !hasRole('bos')) {
            header('Location: /mono-v2/?page=dashboard');
            exit;
        }
        include __DIR__ . '/pages/collector/rute.php';
        break;
        
    case 'nasabah_kunjungan':
        requireLogin();
        if (!hasRole('collector') && !hasRole('admin') && !hasRole('bos')) {
            header('Location: /mono-v2/?page=dashboard');
            exit;
        }
        include __DIR__ . '/pages/collector/nasabah_kunjungan.php';
        break;
        
    case 'kutipan':
        requireLogin();
        if (!hasRole('collector') && !hasRole('admin') && !hasRole('bos')) {
            header('Location: /mono-v2/?page=dashboard');
            exit;
        }
        include __DIR__ . '/pages/collector/kutipan.php';
        break;
        
    case 'gps_log':
        requireLogin();
        if (!hasRole('collector') && !hasRole('admin') && !hasRole('bos')) {
            header('Location: /mono-v2/?page=dashboard');
            exit;
        }
        include __DIR__ . '/pages/collector/gps_log.php';
        break;
        
    // Nasabah Routes
    case 'profil':
        requireLogin();
        // Use mobile dashboard for nasabah
        if ($user['role'] === 'nasabah') {
            include __DIR__ . '/pages/nasabah/mobile-dashboard.php';
        } else {
            include __DIR__ . '/pages/nasabah/profil.php';
        }
        break;
        
    case 'simpanan_saya':
        requireLogin();
        include __DIR__ . '/pages/nasabah/simpanan_saya.php';
        break;
        
    case 'pinjaman_saya':
        requireLogin();
        include __DIR__ . '/pages/nasabah/pinjaman_saya.php';
        break;
        
    case 'riwayat':
        requireLogin();
        include __DIR__ . '/pages/nasabah/riwayat.php';
        break;
        
    default:
        if (isLoggedIn()) {
            include __DIR__ . '/main.php';
        } else {
            include __DIR__ . '/login.php';
        }
        break;
}
?>
