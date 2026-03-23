<?php
/**
 * KSP Lam Gabe Jaya - Enhanced Login Page
 * Using OOP Auth class with enhanced security
 */

// Define access flag for constants
define('IN_LOGIN_PHP', true);

// Security headers
header('Content-Type: text/html; charset=UTF-8');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://fonts.gstatic.com; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src \'self\' \'unsafe-inline\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src \'self\' https://cdnjs.cloudflare.com https://fonts.gstatic.com; img-src \'self\' data: https:; connect-src \'self\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; base-uri \'self\'; form-action \'self\';');

// Include required files
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Auth.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize authentication
$auth = new Auth();

// Check if user is already logged in
$authCheck = $auth->checkAuth();
if ($authCheck['authenticated']) {
    header('Location: /mono-v2/index.php?page=dashboard');
    exit;
}

// Handle login request
$loginError = '';
$loginSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validate input
    if (empty($username) || empty($password)) {
        $loginError = 'Username dan password harus diisi';
    } else {
        // Attempt login with enhanced security
        $result = $auth->login($username, $password, $remember);
        
        if ($result['success']) {
            $loginSuccess = true;
            // Redirect to dashboard
            header('Location: /mono-v2/index.php?page=dashboard');
            exit;
        } else {
            $loginError = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KSP Lam Gabe Jaya</title>
    <meta name="description" content="Sistem Koperasi Digital Terpadu">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/login.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <!-- Logo Section -->
            <div class="login-header">
                <div class="logo">
                    <img src="assets/img/logo.png" alt="KSP Lam Gabe Jaya" class="logo-img">
                </div>
                <h1 class="login-title">KSP Lam Gabe Jaya</h1>
                <p class="login-subtitle">Sistem Koperasi Digital Terpadu</p>
            </div>
            
            <!-- Login Form -->
            <div class="login-body">
                <?php if ($loginError): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($loginError); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($loginSuccess): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        Login berhasil! Mengalihkan ke dashboard...
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="login-form">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="fas fa-user me-2"></i>Username
                        </label>
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Masukkan username" required
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Masukkan password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Ingat saya
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>
                
                <div class="login-footer">
                    <div class="text-center">
                        <small class="text-muted">
                            Lupa password? <a href="#reset-password">Reset di sini</a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Login Buttons -->
        <div class="quick-login">
            <h6>Quick Login:</h6>
            <div class="quick-login-buttons">
                <button type="button" class="btn btn-outline-primary btn-sm quick-login-btn" data-username="bos" data-password="bos">
                    <i class="fas fa-crown me-1"></i>BOS
                </button>
                <button type="button" class="btn btn-outline-success btn-sm quick-login-btn" data-username="admin" data-password="admin">
                    <i class="fas fa-user-shield me-1"></i>Admin
                </button>
                <button type="button" class="btn btn-outline-info btn-sm quick-login-btn" data-username="teller" data-password="teller">
                    <i class="fas fa-cash-register me-1"></i>Teller
                </button>
                <button type="button" class="btn btn-outline-warning btn-sm quick-login-btn" data-username="collector" data-password="collector">
                    <i class="fas fa-route me-1"></i>Collector
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm quick-login-btn" data-username="nasabah" data-password="nasabah">
                    <i class="fas fa-user me-1"></i>Nasabah
                </button>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Quick Login functionality
        document.querySelectorAll('.quick-login-btn').forEach(button => {
            button.addEventListener('click', function() {
                const username = this.dataset.username;
                const password = this.dataset.password;
                
                // Show loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Logging in...';
                this.disabled = true;
                
                // Fill form and submit
                document.getElementById('username').value = username;
                document.getElementById('password').value = password;
                
                // Submit the form
                const form = document.querySelector('.login-form');
                form.submit();
            });
        });
        
        // Auto-focus username field
        document.getElementById('username').focus();
        
        // Handle form submission with loading state
        document.querySelector('.login-form').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Masuk...';
        });
        
        // Handle reset password link
        document.querySelector('a[href="#reset-password"]').addEventListener('click', function(e) {
            e.preventDefault();
            alert('Fitur reset password akan segera tersedia.');
        });
    </script>
</body>
</html>
