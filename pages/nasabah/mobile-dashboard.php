<?php
/**
 * Mobile-Optimized Dashboard for Nasabah (Members)
 * Provides responsive interface for member self-service
 */

// Prevent direct access
define('IN_MEMBER_DASHBOARD', true);

// Include necessary files
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../core/Database.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header('Location: /mono-v2/login.php');
    exit;
}

$user = $_SESSION['user'];
$db = Database::getInstance();

// Get member data
$memberData = null;
if ($user['role'] === 'nasabah' && isset($user['member_id'])) {
    $memberData = $db->fetchOne(
        "SELECT m.*, u.name, u.email 
         FROM members m 
         JOIN users u ON m.user_id = u.id 
         WHERE m.id = ? AND m.status = 'active'",
        [$user['member_id']]
    );
}

// Get member accounts
$accounts = [];
if ($memberData) {
    $accounts = $db->fetchAll(
        "SELECT * FROM accounts WHERE member_id = ? AND status = 'active'",
        [$memberData['id']]
    );
}

// Get member loans
$loans = [];
if ($memberData) {
    $loans = $db->fetchAll(
        "SELECT * FROM loans WHERE member_id = ? AND status IN ('active', 'completed') ORDER BY application_date DESC",
        [$memberData['id']]
    );
}

$pageTitle = 'Dashboard Anggota - ' . APP_NAME;
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
    
    <!-- Mobile Optimization -->
    <meta name="theme-color" content="#007bff">
    <link rel="manifest" href="/manifest.json">
    
    <style>
        /* Mobile-first responsive design */
        .mobile-dashboard {
            padding-bottom: 80px; /* Space for bottom navigation */
        }
        
        .mobile-header {
            background: linear-gradient(135deg, var(--primary-color, #007bff) 0%, var(--primary-dark, #0056b3) 100%);
            color: white;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .balance-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .balance-amount {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color, #007bff);
        }
        
        .quick-action-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            transition: transform 0.2s;
            cursor: pointer;
            border: 1px solid #e9ecef;
        }
        
        .quick-action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .quick-action-card i {
            font-size: 2rem;
            color: var(--primary-color, #007bff);
            margin-bottom: 0.5rem;
        }
        
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #dee2e6;
            padding: 0.5rem 0;
            z-index: 1000;
        }
        
        .bottom-nav .nav-link {
            color: #6c757d;
            text-align: center;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .bottom-nav .nav-link.active {
            color: var(--primary-color, #007bff);
            background: rgba(0,123,255,0.1);
        }
        
        .bottom-nav .nav-link i {
            display: block;
            font-size: 1.2rem;
            margin-bottom: 0.25rem;
        }
        
        .bottom-nav .nav-link span {
            font-size: 0.75rem;
        }
        
        .transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .transaction-item:last-child {
            border-bottom: none;
        }
        
        .loan-status {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
        }
        
        .loan-active {
            background: #d4edda;
            color: #155724;
        }
        
        .loan-completed {
            background: #cce5ff;
            color: #004085;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .mobile-dashboard {
                padding: 0.5rem;
            }
            
            .balance-amount {
                font-size: 1.5rem;
            }
            
            .quick-action-card i {
                font-size: 1.5rem;
            }
        }
        
        /* Desktop fallback */
        @media (min-width: 769px) {
            .bottom-nav {
                display: none;
            }
            
            .mobile-dashboard {
                padding-bottom: 0;
            }
        }
    </style>
</head>
<body>
    <div class="mobile-dashboard">
        <!-- Mobile Header -->
        <div class="mobile-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Halo, <?php echo htmlspecialchars($memberData['full_name'] ?? $user['name']); ?>!</h5>
                    <small><?php echo date('d F Y'); ?></small>
                </div>
                <div class="user-avatar" onclick="toggleUserMenu()">
                    <?php echo strtoupper(substr($memberData['full_name'] ?? $user['name'], 0, 2)); ?>
                </div>
            </div>
        </div>
        
        <!-- Balance Overview -->
        <div class="balance-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h6 class="text-muted mb-2">Total Saldo Simpanan</h6>
                    <div class="balance-amount">
                        Rp <?php echo number_format(array_sum(array_column($accounts, 'balance')), 0, ',', '.'); ?>
                    </div>
                </div>
                <i class="fas fa-piggy-bank fa-2x text-primary"></i>
            </div>
            
            <?php if (!empty($accounts)): ?>
                <div class="row mt-3">
                    <?php foreach ($accounts as $account): ?>
                        <div class="col-6 mb-2">
                            <small class="text-muted"><?php echo htmlspecialchars($account['account_name']); ?></small><br>
                            <strong>Rp <?php echo number_format($account['balance'], 0, ',', '.'); ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="mb-4">
            <h6 class="mb-3">Aksi Cepat</h6>
            <div class="row g-2">
                <div class="col-4">
                    <div class="quick-action-card" onclick="navigateTo('deposit')">
                        <i class="fas fa-plus-circle"></i>
                        <small>Setoran</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="quick-action-card" onclick="navigateTo('withdrawal')">
                        <i class="fas fa-minus-circle"></i>
                        <small>Penarikan</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="quick-action-card" onclick="navigateTo('loan')">
                        <i class="fas fa-hand-holding-usd"></i>
                        <small>Pinjaman</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="quick-action-card" onclick="navigateTo('payment')">
                        <i class="fas fa-credit-card"></i>
                        <small>Pembayaran</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="quick-action-card" onclick="navigateTo('history')">
                        <i class="fas fa-history"></i>
                        <small>Riwayat</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="quick-action-card" onclick="navigateTo('profile')">
                        <i class="fas fa-user"></i>
                        <small>Profil</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Active Loans -->
        <?php if (!empty($loans)): ?>
        <div class="mb-4">
            <h6 class="mb-3">Pinjaman Aktif</h6>
            <div class="card">
                <div class="card-body p-0">
                    <?php foreach (array_slice($loans, 0, 3) as $loan): ?>
                        <div class="transaction-item">
                            <div>
                                <strong><?php echo htmlspecialchars($loan['loan_number']); ?></strong>
                                <br>
                                <small class="text-muted">Rp <?php echo number_format($loan['loan_amount'], 0, ',', '.'); ?></small>
                            </div>
                            <span class="loan-status loan-<?php echo $loan['status']; ?>">
                                <?php echo ucfirst($loan['status']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Recent Transactions -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Transaksi Terakhir</h6>
                <a href="#" onclick="navigateTo('history')" class="text-primary">
                    <small>Lihat semua</small>
                </a>
            </div>
            <div class="card">
                <div class="card-body p-0">
                    <?php
                    $recentTransactions = $db->fetchAll(
                        "SELECT t.*, a.account_type, a.account_number
                         FROM transactions t
                         JOIN accounts a ON t.account_id = a.id
                         WHERE a.member_id = ?
                         ORDER BY t.created_at DESC
                         LIMIT 5",
                        [$memberData['id'] ?? 0]
                    );
                    
                    if (empty($recentTransactions)): ?>
                        <div class="text-center p-3 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p class="mb-0">Belum ada transaksi</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentTransactions as $transaction): ?>
                            <div class="transaction-item">
                                <div>
                                    <strong><?php echo ucfirst($transaction['transaction_type']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo date('d M Y, H:i', strtotime($transaction['created_at'])); ?></small>
                                </div>
                                <div class="text-end">
                                    <div class="<?php echo $transaction['transaction_type'] === 'credit' ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo $transaction['transaction_type'] === 'credit' ? '+' : '-'; ?>
                                        Rp <?php echo number_format($transaction['amount'], 0, ',', '.'); ?>
                                    </div>
                                    <small class="text-muted"><?php echo $transaction['payment_method']; ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Bottom Navigation -->
        <nav class="bottom-nav">
            <div class="container">
                <div class="row">
                    <div class="col-3">
                        <a href="#" class="nav-link active" onclick="navigateTo('dashboard')">
                            <i class="fas fa-home"></i>
                            <span>Home</span>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="#" class="nav-link" onclick="navigateTo('accounts')">
                            <i class="fas fa-wallet"></i>
                            <span>Rekening</span>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="#" class="nav-link" onclick="navigateTo('loan')">
                            <i class="fas fa-hand-holding-usd"></i>
                            <span>Pinjaman</span>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="#" class="nav-link" onclick="navigateTo('profile')">
                            <i class="fas fa-user"></i>
                            <span>Profil</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Navigation functions
        function navigateTo(page) {
            // For mobile, use AJAX to load content
            if (window.innerWidth <= 768) {
                loadPageContent(page);
            } else {
                // For desktop, use regular navigation
                window.location.href = `/mono-v2/?page=${page}`;
            }
        }
        
        // Load page content via AJAX (mobile)
        async function loadPageContent(page) {
            try {
                // Show loading
                document.querySelector('.mobile-dashboard').innerHTML = `
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Memuat...</p>
                    </div>
                `;
                
                const response = await fetch(`/mono-v2/api/mobile-content.php?page=${page}`);
                const html = await response.text();
                
                document.querySelector('.mobile-dashboard').innerHTML = html;
                
                // Update active nav
                updateActiveNav(page);
                
            } catch (error) {
                console.error('Load page error:', error);
                // Fallback to regular navigation
                window.location.href = `/mono-v2/?page=${page}`;
            }
        }
        
        // Update active navigation
        function updateActiveNav(page) {
            document.querySelectorAll('.bottom-nav .nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            const pageMap = {
                'dashboard': 0,
                'accounts': 1,
                'loan': 2,
                'profile': 3
            };
            
            const index = pageMap[page];
            if (index !== undefined) {
                document.querySelectorAll('.bottom-nav .nav-link')[index].classList.add('active');
            }
        }
        
        // User menu functions
        function toggleUserMenu() {
            // Simple user menu for mobile
            const options = [
                { label: 'Profil', action: () => navigateTo('profile') },
                { label: 'Pengaturan', action: () => alert('Pengaturan akan segera tersedia') },
                { label: 'Keluar', action: () => logout() }
            ];
            
            const choice = confirm('Profil: OK, Pengaturan: Cancel, Keluar: Tutup dialog');
            if (choice) {
                navigateTo('profile');
            } else {
                alert('Pengaturan akan segera tersedia');
            }
        }
        
        function logout() {
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                window.location.href = '/mono-v2/?action=logout';
            }
        }
        
        // Pull to refresh (mobile)
        let startY = 0;
        let isPulling = false;
        
        document.addEventListener('touchstart', function(e) {
            if (window.scrollY === 0) {
                startY = e.touches[0].pageY;
                isPulling = true;
            }
        });
        
        document.addEventListener('touchmove', function(e) {
            if (!isPulling) return;
            
            const currentY = e.touches[0].pageY;
            const pullDistance = currentY - startY;
            
            if (pullDistance > 0 && pullDistance < 100) {
                document.querySelector('.mobile-dashboard').style.transform = `translateY(${pullDistance * 0.5}px)`;
            }
        });
        
        document.addEventListener('touchend', function(e) {
            if (!isPulling) return;
            
            const currentY = e.changedTouches[0].pageY;
            const pullDistance = currentY - startY;
            
            document.querySelector('.mobile-dashboard').style.transform = '';
            
            if (pullDistance > 80) {
                // Refresh content
                location.reload();
            }
            
            isPulling = false;
        });
        
        // Responsive handling
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                // Switch to desktop view
                window.location.href = '/mono-v2/?page=dashboard';
            }
        });
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Check if mobile
            if (window.innerWidth <= 768) {
                // Hide desktop elements
                const desktopElements = document.querySelectorAll('.app-sidebar, .app-header');
                desktopElements.forEach(el => el.style.display = 'none');
            }
        });
    </script>
</body>
</html>
