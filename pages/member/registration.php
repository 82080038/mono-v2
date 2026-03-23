<?php
/**
 * Member Registration Form
 * Allows new members to register for approval
 */

// Prevent direct access
define('IN_MEMBER_REGISTRATION', true);

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance();
        
        // Validate required fields
        $required = ['full_name', 'nik', 'birth_date', 'address', 'phone'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field $field is required");
            }
        }
        
        // Check if NIK already exists
        $existingMember = $db->fetchOne("SELECT id FROM members WHERE nik = ?", [$_POST['nik']]);
        if ($existingMember) {
            throw new Exception("NIK sudah terdaftar");
        }
        
        // Generate member number
        $memberNumber = generateMemberNumber();
        
        // Insert new member
        $db->query(
            "INSERT INTO members (
                user_id, member_number, nik, full_name, birth_date, birth_place,
                gender, address, phone, email, join_date, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'pending', NOW())",
            [
                $user['id'] ?? null, // For member self-registration
                $memberNumber,
                $_POST['nik'],
                $_POST['full_name'],
                $_POST['birth_date'],
                $_POST['birth_place'] ?? '',
                $_POST['gender'] ?? '',
                $_POST['address'],
                $_POST['phone'],
                $_POST['email'] ?? '',
            ]
        );
        
        $success = true;
        $message = "Pendaftaran berhasil! Nomor anggota Anda: $memberNumber. Menunggu persetujuan admin.";
        
    } catch (Exception $e) {
        $success = false;
        $message = $e->getMessage();
    }
}

function generateMemberNumber() {
    $db = Database::getInstance();
    
    $date = date('Ymd');
    $sequence = $db->fetchOne("SELECT COUNT(*) + 1 as count FROM members WHERE DATE(created_at) = CURDATE()")['count'];
    
    return 'MBR' . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
}

$pageTitle = 'Pendaftaran Anggota - ' . APP_NAME;
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
    
    <style>
        .registration-form {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .form-section h6 {
            color: var(--primary-color, #007bff);
            margin-bottom: 1rem;
        }
        
        .required-field::after {
            content: " *";
            color: red;
        }
        
        .alert-success {
            border-left: 4px solid #28a745;
        }
        
        .btn-primary {
            background: var(--primary-color, #007bff);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark, #0056b3);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,123,255,0.3);
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
                        <?php echo strtoupper(substr($user['name'] ?? 'User', 0, 2)); ?>
                    </div>
                    
                    <div class="dropdown-menu dropdown-menu-end" id="userMenu" style="display: none;">
                        <div class="dropdown-item-text">
                            <strong><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></strong>
                            <br>
                            <small class="text-muted"><?php echo htmlspecialchars(ucfirst($user['role'] ?? 'user')); ?></small>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/mono-v2/?page=dashboard">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="dropdown-item" href="#" onclick="logout()">
                            <i class="fas fa-sign-out-alt me-2"></i>Keluar
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="app-main">
            <div class="dashboard-header">
                <h1>Pendaftaran Anggota Baru</h1>
                <p>Formulir pendaftaran anggota koperasi</p>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?> alert-dismissible fade show">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                
                <?php if ($success): ?>
                    <div class="text-center mt-3">
                        <a href="/mono-v2/?page=dashboard" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Kembali ke Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
            
            <div class="registration-form">
                <form method="POST" id="registrationForm">
                    <!-- Personal Information -->
                    <div class="form-section">
                        <h6><i class="fas fa-user me-2"></i>Informasi Pribadi</h6>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control required-field" name="full_name" id="full_name" required>
                                    <label for="full_name">Nama Lengkap</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control required-field" name="nik" id="nik" maxlength="16" required>
                                    <label for="nik">NIK</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control required-field" name="birth_date" id="birth_date" required>
                                    <label for="birth_date">Tanggal Lahir</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" name="birth_place" id="birth_place">
                                    <label for="birth_place">Tempat Lahir</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" name="gender" id="gender">
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                    <label for="gender">Jenis Kelamin</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" name="email" id="email">
                                    <label for="email">Email (Opsional)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="form-section">
                        <h6><i class="fas fa-address-book me-2"></i>Informasi Kontak</h6>
                        
                        <div class="form-floating mb-3">
                            <textarea class="form-control required-field" name="address" id="address" style="height: 100px" required></textarea>
                            <label for="address">Alamat Lengkap</label>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="tel" class="form-control required-field" name="phone" id="phone" required>
                                    <label for="phone">No. Telepon/HP</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" name="emergency_contact" id="emergency_contact">
                                    <label for="emergency_contact">Kontak Darurat (Opsional)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Agreement -->
                    <div class="form-section">
                        <h6><i class="fas fa-file-contract me-2"></i>Persetujuan</h6>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="agreement" required>
                            <label class="form-check-label" for="agreement">
                                Saya menyatakan bahwa data yang diisi adalah benar dan saya setuju dengan <a href="#" onclick="showTerms()">syarat dan ketentuan</a> koperasi.
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="privacy" required>
                            <label class="form-check-label" for="privacy">
                                Saya setuju dengan <a href="#" onclick="showPrivacy()">kebijakan privasi</a> koperasi.
                            </label>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="fas fa-user-plus me-2"></i>Daftar Sebagai Anggota
                        </button>
                    </div>
                </form>
            </div>
            
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
        });
        
        // NIK validation
        document.getElementById('nik').addEventListener('input', function(e) {
            const value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
        });
        
        // Phone validation
        document.getElementById('phone').addEventListener('input', function(e) {
            const value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
        });
        
        // User menu functions
        function toggleUserMenu() {
            const userMenu = document.getElementById('userMenu');
            if (userMenu.style.display === 'none' || userMenu.style.display === '') {
                userMenu.style.display = 'block';
                userMenu.classList.add('show');
            } else {
                userMenu.style.display = 'none';
                userMenu.classList.remove('show');
            }
        }
        
        function logout() {
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                window.location.href = '/mono-v2/?action=logout';
            }
        }
        
        function showTerms() {
            alert('Syarat dan ketentuan koperasi akan ditampilkan di sini.');
        }
        
        function showPrivacy() {
            alert('Kebijakan privasi koperasi akan ditampilkan di sini.');
        }
        
        // Close user menu when clicking outside
        document.addEventListener('click', function(e) {
            const userMenu = document.getElementById('userMenu');
            if (userMenu && !e.target.closest('.user-dropdown')) {
                userMenu.style.display = 'none';
                userMenu.classList.remove('show');
            }
        });
    </script>
</body>
</html>
