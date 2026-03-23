<?php
/**
 * Mobile Content API for Nasabah
 * Provides AJAX content for mobile dashboard
 */

// Prevent direct access
define('IN_MOBILE_API', true);

// Include necessary files
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../core/Database.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$user = $_SESSION['user'];
$page = $_GET['page'] ?? 'dashboard';

// Get member data
$memberData = null;
if ($user['role'] === 'nasabah' && isset($user['member_id'])) {
    $db = Database::getInstance();
    $memberData = $db->fetchOne(
        "SELECT m.*, u.name, u.email 
         FROM members m 
         JOIN users u ON m.user_id = u.id 
         WHERE m.id = ? AND m.status = 'active'",
        [$user['member_id']]
    );
}

// Generate content based on page
switch ($page) {
    case 'accounts':
        generateAccountsContent();
        break;
    case 'loan':
        generateLoanContent();
        break;
    case 'profile':
        generateProfileContent();
        break;
    case 'history':
        generateHistoryContent();
        break;
    default:
        generateDashboardContent();
        break;
}

function generateAccountsContent() {
    global $memberData;
    
    if (!$memberData) {
        echo '<div class="text-center p-5"><p>Data anggota tidak ditemukan</p></div>';
        return;
    }
    
    $db = Database::getInstance();
    $accounts = $db->fetchAll(
        "SELECT * FROM accounts WHERE member_id = ? AND status = 'active'",
        [$memberData['id']]
    );
    
    ob_start();
    ?>
    <div class="mobile-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Rekening Saya</h5>
                <small>Total <?php echo count($accounts); ?> rekening</small>
            </div>
            <button class="btn btn-sm btn-light" onclick="navigateTo('dashboard')">
                <i class="fas fa-arrow-left"></i>
            </button>
        </div>
    </div>
    
    <div class="p-3">
        <?php foreach ($accounts as $account): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title"><?php echo htmlspecialchars($account['account_name']); ?></h6>
                            <p class="card-text text-muted">No. <?php echo htmlspecialchars($account['account_number']); ?></p>
                        </div>
                        <div class="text-end">
                            <h5 class="text-primary">Rp <?php echo number_format($account['balance'], 0, ',', '.'); ?></h5>
                            <small class="text-success">Aktif</small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-primary me-2" onclick="showTransactionHistory('<?php echo $account['id']; ?>')">
                            <i class="fas fa-history me-1"></i>Riwayat
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="showAccountDetails('<?php echo $account['id']; ?>')">
                            <i class="fas fa-info-circle me-1"></i>Detail
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    echo ob_get_clean();
}

function generateLoanContent() {
    global $memberData;
    
    if (!$memberData) {
        echo '<div class="text-center p-5"><p>Data anggota tidak ditemukan</p></div>';
        return;
    }
    
    $db = Database::getInstance();
    $loans = $db->fetchAll(
        "SELECT * FROM loans WHERE member_id = ? ORDER BY application_date DESC",
        [$memberData['id']]
    );
    
    ob_start();
    ?>
    <div class="mobile-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Pinjaman Saya</h5>
                <small><?php echo count($loans); ?> pinjaman</small>
            </div>
            <button class="btn btn-sm btn-light" onclick="navigateTo('dashboard')">
                <i class="fas fa-arrow-left"></i>
            </button>
        </div>
    </div>
    
    <div class="p-3">
        <div class="text-center mb-4">
            <button class="btn btn-primary" onclick="showLoanApplication()">
                <i class="fas fa-plus me-2"></i>Ajukan Pinjaman Baru
            </button>
        </div>
        
        <?php foreach ($loans as $loan): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title"><?php echo htmlspecialchars($loan['loan_number']); ?></h6>
                            <p class="card-text text-muted">
                                <?php echo date('d M Y', strtotime($loan['application_date'])); ?>
                            </p>
                        </div>
                        <span class="badge bg-<?php echo $loan['status'] === 'active' ? 'success' : 'secondary'; ?>">
                            <?php echo ucfirst($loan['status']); ?>
                        </span>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-6">
                            <small class="text-muted">Jumlah Pinjaman</small><br>
                            <strong>Rp <?php echo number_format($loan['loan_amount'], 0, ',', '.'); ?></strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Sisa Pinjaman</small><br>
                            <strong class="text-warning">Rp <?php echo number_format(getLoanBalance($loan['id']), 0, ',', '.'); ?></strong>
                        </div>
                    </div>
                    
                    <?php if ($loan['status'] === 'active'): ?>
                        <div class="mt-3">
                            <button class="btn btn-sm btn-success me-2" onclick="makePayment('<?php echo $loan['id']; ?>')">
                                <i class="fas fa-credit-card me-1"></i>Bayar
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="showLoanSchedule('<?php echo $loan['id']; ?>')">
                                <i class="fas fa-calendar me-1"></i>Jadwal
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    echo ob_get_clean();
}

function generateProfileContent() {
    global $memberData;
    
    if (!$memberData) {
        echo '<div class="text-center p-5"><p>Data anggota tidak ditemukan</p></div>';
        return;
    }
    
    ob_start();
    ?>
    <div class="mobile-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Profil Saya</h5>
                <small>Data pribadi</small>
            </div>
            <button class="btn btn-sm btn-light" onclick="navigateTo('dashboard')">
                <i class="fas fa-arrow-left"></i>
            </button>
        </div>
    </div>
    
    <div class="p-3">
        <div class="text-center mb-4">
            <div class="user-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                <?php echo strtoupper(substr($memberData['full_name'], 0, 2)); ?>
            </div>
            <h5><?php echo htmlspecialchars($memberData['full_name']); ?></h5>
            <p class="text-muted">No. Anggota: <?php echo htmlspecialchars($memberData['member_number']); ?></p>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Informasi Pribadi</h6>
                
                <div class="mb-3">
                    <label class="text-muted">NIK</label>
                    <p><?php echo htmlspecialchars($memberData['nik']); ?></p>
                </div>
                
                <div class="mb-3">
                    <label class="text-muted">Tanggal Lahir</label>
                    <p><?php echo date('d F Y', strtotime($memberData['birth_date'])); ?></p>
                </div>
                
                <div class="mb-3">
                    <label class="text-muted">Alamat</label>
                    <p><?php echo nl2br(htmlspecialchars($memberData['address'])); ?></p>
                </div>
                
                <div class="mb-3">
                    <label class="text-muted">Telepon</label>
                    <p><?php echo htmlspecialchars($memberData['phone']); ?></p>
                </div>
                
                <?php if ($memberData['email']): ?>
                <div class="mb-3">
                    <label class="text-muted">Email</label>
                    <p><?php echo htmlspecialchars($memberData['email']); ?></p>
                </div>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <button class="btn btn-primary" onclick="editProfile()">
                        <i class="fas fa-edit me-2"></i>Edit Profil
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php
    echo ob_get_clean();
}

function generateHistoryContent() {
    global $memberData;
    
    if (!$memberData) {
        echo '<div class="text-center p-5"><p>Data anggota tidak ditemukan</p></div>';
        return;
    }
    
    $db = Database::getInstance();
    $transactions = $db->fetchAll(
        "SELECT t.*, a.account_type, a.account_number
         FROM transactions t
         JOIN accounts a ON t.account_id = a.id
         WHERE a.member_id = ?
         ORDER BY t.created_at DESC
         LIMIT 50",
        [$memberData['id']]
    );
    
    ob_start();
    ?>
    <div class="mobile-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Riwayat Transaksi</h5>
                <small><?php echo count($transactions); ?> transaksi</small>
            </div>
            <button class="btn btn-sm btn-light" onclick="navigateTo('dashboard')">
                <i class="fas fa-arrow-left"></i>
            </button>
        </div>
    </div>
    
    <div class="p-3">
        <?php if (empty($transactions)): ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">Belum ada transaksi</p>
            </div>
        <?php else: ?>
            <?php foreach ($transactions as $transaction): ?>
                <div class="card mb-2">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">
                                    <?php echo ucfirst($transaction['transaction_type']); ?>
                                    <small class="text-muted">-<?php echo htmlspecialchars($transaction['account_number']); ?></small>
                                </h6>
                                <small class="text-muted">
                                    <?php echo date('d M Y, H:i', strtotime($transaction['created_at'])); ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <div class="<?php echo $transaction['transaction_type'] === 'credit' ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $transaction['transaction_type'] === 'credit' ? '+' : '-'; ?>
                                    Rp <?php echo number_format($transaction['amount'], 0, ',', '.'); ?>
                                </div>
                                <small class="text-muted"><?php echo $transaction['payment_method']; ?></small>
                            </div>
                        </div>
                        
                        <?php if ($transaction['description']): ?>
                            <div class="mt-2">
                                <small class="text-muted"><?php echo htmlspecialchars($transaction['description']); ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
    echo ob_get_clean();
}

function generateDashboardContent() {
    // This would regenerate the main dashboard content
    // For simplicity, redirect to main dashboard
    echo '<script>window.location.href = "/mono-v2/?page=profil";</script>';
}

// Helper functions
function getLoanBalance($loanId) {
    $db = Database::getInstance();
    $loan = $db->fetchOne("SELECT loan_amount FROM loans WHERE id = ?", [$loanId]);
    $paid = $db->fetchOne(
        "SELECT COALESCE(SUM(principal_amount), 0) as total_paid FROM loan_payments WHERE loan_id = ? AND status = 'completed'",
        [$loanId]
    );
    
    return $loan ? $loan['loan_amount'] - $paid['total_paid'] : 0;
}
?>
