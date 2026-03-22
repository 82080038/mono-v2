<?php
/**
 * KSP Lam Gabe Jaya - Setoran (Teller)
 * Teller transaction page for deposits
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
if (!in_array($userRole, ['teller', 'admin', 'bos'])) {
    header('Location: /mono-v2/?page=dashboard');
    exit;
}

$pageTitle = 'Setoran - ' . APP_NAME;
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
                    <span class="user-info">
                        <i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($userName); ?>
                        <small>(<?php echo htmlspecialchars(ucfirst($userRole)); ?>)</small>
                    </span>
                    <a href="/mono-v2/?action=logout" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
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
                <a href="/mono-v2/?page=nasabah" class="menu-item">
                    <i class="fas fa-users"></i>
                    Nasabah
                </a>
                <a href="/mono-v2/?page=setoran" class="menu-item active">
                    <i class="fas fa-plus-circle"></i>
                    Setoran
                </a>
                <a href="/mono-v2/?page=penarikan" class="menu-item">
                    <i class="fas fa-minus-circle"></i>
                    Penarikan
                </a>
                <a href="/mono-v2/?page=pembayaran" class="menu-item">
                    <i class="fas fa-credit-card"></i>
                    Pembayaran
                </a>
                <a href="/mono-v2/?page=laporan_harian" class="menu-item">
                    <i class="fas fa-clipboard-list"></i>
                    Laporan Harian
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="app-main">
            <div class="dashboard-header">
                <h1>Setoran</h1>
                <p>Proses setoran simpanan anggota koperasi</p>
            </div>
            
            <div class="content-section">
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
            </div>
        </main>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
