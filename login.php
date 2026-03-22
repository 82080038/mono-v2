<?php
/**
 * KSP Lam Gabe Jaya - Login Page
 * Dedicated login handler with enhanced security and features
 */

// Define access flag for constants
define('IN_LOGIN_PHP', true);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security headers
header('Content-Type: text/html; charset=UTF-8');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Include configuration constants
require_once __DIR__ . '/config/constants.php';

// Start session
session_start();

// Check if user is already logged in
function checkExistingAuth() {
    // Check session
    if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    
    // Check token from various sources
    $token = null;
    
    // Check POST data
    if (isset($_POST['authToken'])) {
        $token = $_POST['authToken'];
    }
    
    // Check GET data
    if (isset($_GET['token'])) {
        $token = $_GET['token'];
    }
    
    // Check Authorization header
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        if (strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
        }
    }
    
    // Validate token if present
    if ($token) {
        try {
            $userData = validateToken($token);
            if ($userData) {
                $_SESSION['user'] = $userData;
                return $userData;
            }
        } catch (Exception $e) {
            error_log("Token validation failed: " . $e->getMessage());
        }
    }
    
    return null;
}

// Simple token validation (will be enhanced with proper auth system)
function validateToken($token) {
    // Check for JWT format
    if (strpos($token, '.') !== false && count(explode('.', $token)) === 3) {
        try {
            $parts = explode('.', $token);
            $payload = json_decode(base64_decode($parts[1]), true);
            
            if ($payload && isset($payload['user_id']) && isset($payload['exp'])) {
                // Check expiration
                if ($payload['exp'] > time()) {
                    return $payload;
                }
            }
        } catch (Exception $e) {
            error_log("JWT decode failed: " . $e->getMessage());
        }
    }
    
    // Check for demo token
    if (strpos($token, 'demo-token-') === 0) {
        return [
            'user_id' => 1,
            'username' => 'admin',
            'name' => 'Administrator',
            'role' => 'admin',
            'exp' => time() + 3600 // 1 hour
        ];
    }
    
    return null;
}

// Handle login request
function handleLogin() {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validate input
    if (empty($username) || empty($password)) {
        return [
            'success' => false,
            'message' => 'Username dan password harus diisi'
        ];
    }
    
    // Sanitize input
    $username = trim($username);
    $password = trim($password);
    
    // Rate limiting check (simple implementation)
    $rateLimitKey = 'login_attempts_' . $_SERVER['REMOTE_ADDR'];
    $attempts = $_SESSION[$rateLimitKey] ?? 0;
    
    if ($attempts >= 5) {
        return [
            'success' => false,
            'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit.'
        ];
    }
    
    // Authenticate user (will be enhanced with database)
    $authResult = authenticateUser($username, $password);
    
    if ($authResult['success']) {
        // Reset rate limiting on successful login
        unset($_SESSION[$rateLimitKey]);
        
        // Set session
        $_SESSION['user'] = $authResult['user'];
        
        // Generate token
        $token = generateToken($authResult['user']);
        
        // Set remember me cookie if requested
        if ($remember) {
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        }
        
        return [
            'success' => true,
            'message' => 'Login berhasil',
            'user' => $authResult['user'],
            'token' => $token,
            'redirect' => getRedirectUrl($authResult['user']['role'])
        ];
    } else {
        // Increment rate limiting
        $_SESSION[$rateLimitKey] = $attempts + 1;
        
        return [
            'success' => false,
            'message' => $authResult['message'] ?? 'Username atau password salah'
        ];
    }
}

// Authenticate user against database (demo implementation)
function authenticateUser($username, $password) {
    // Demo users (will be replaced with database query)
    $demoUsers = [
        'bos' => [
            'password' => 'bos',
            'user' => [
                'id' => 1,
                'username' => 'bos',
                'name' => 'Pemilik Koperasi',
                'role' => ROLE_BOS,
                'email' => 'bos@ksplamgabejaya.co.id',
                'last_login' => date('Y-m-d H:i:s')
            ]
        ],
        'admin' => [
            'password' => 'admin',
            'user' => [
                'id' => 2,
                'username' => 'admin',
                'name' => 'Administrator',
                'role' => ROLE_ADMIN,
                'email' => 'admin@ksplamgabejaya.co.id',
                'last_login' => date('Y-m-d H:i:s')
            ]
        ],
        'teller' => [
            'password' => 'teller',
            'user' => [
                'id' => 3,
                'username' => 'teller',
                'name' => 'Petugas Teller',
                'role' => ROLE_TELLER,
                'email' => 'teller@ksplamgabejaya.co.id',
                'last_login' => date('Y-m-d H:i:s')
            ]
        ],
        'collector' => [
            'password' => 'collector',
            'user' => [
                'id' => 4,
                'username' => 'collector',
                'name' => 'Petugas Lapangan',
                'role' => ROLE_FIELD_COLLECTOR,
                'email' => 'collector@ksplamgabejaya.co.id',
                'last_login' => date('Y-m-d H:i:s')
            ]
        ],
        'nasabah' => [
            'password' => 'nasabah',
            'user' => [
                'id' => 5,
                'username' => 'nasabah',
                'name' => 'Anggota Koperasi',
                'role' => ROLE_NASABAH,
                'email' => 'nasabah@ksplamgabejaya.co.id',
                'last_login' => date('Y-m-d H:i:s')
            ]
        ]
    ];
    
    if (isset($demoUsers[$username]) && $demoUsers[$username]['password'] === $password) {
        return [
            'success' => true,
            'user' => $demoUsers[$username]['user']
        ];
    }
    
    return [
        'success' => false,
        'message' => 'Username atau password salah'
    ];
}

// Generate JWT token (simplified)
function generateToken($user) {
    $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64_encode(json_encode([
        'user_id' => $user['id'],
        'username' => $user['username'],
        'name' => $user['name'],
        'role' => $user['role'],
        'iat' => time(),
        'exp' => time() + 3600 // 1 hour expiration
    ]));
    $signature = base64_encode(hash_hmac('sha256', $header . '.' . $payload, 'secret-key', true));
    
    return $header . '.' . $payload . '.' . $signature;
}

// Get redirect URL based on user role
function getRedirectUrl($role) {
    $redirects = [
        'admin' => '/dashboard',
        'super_admin' => '/dashboard',
        'manager' => '/dashboard',
        'staff' => '/dashboard',
        'member' => '/dashboard',
        'teller' => '/dashboard'
    ];
    
    return $redirects[$role] ?? '/dashboard';
}

// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'login':
            $result = handleLogin();
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
            
        case 'check_session':
            $user = checkExistingAuth();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'authenticated' => $user !== null,
                'user' => $user
            ]);
            exit;
            
        case 'logout':
            session_destroy();
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
            exit;
    }
}

// Check if user is already logged in
$currentUser = checkExistingAuth();
if ($currentUser) {
    // Redirect to dashboard
    header('Location: /dashboard');
    exit;
}

// Get login error from session
$loginError = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

// Get success message from session
$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - <?php echo APP_NAME; ?></title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#007bff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="KSP Lam Gabe">
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
            --border-radius: 0.5rem;
            --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        /* Animated background */
        .bg-animation {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
        }
        
        .bg-animation span {
            position: absolute;
            display: block;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.1);
            animation: move 25s linear infinite;
            bottom: -150px;
        }
        
        .bg-animation span:nth-child(1) {
            left: 25%;
            width: 80px;
            height: 80px;
            animation-delay: 0s;
        }
        
        .bg-animation span:nth-child(2) {
            left: 10%;
            width: 20px;
            height: 20px;
            animation-delay: 2s;
            animation-duration: 12s;
        }
        
        .bg-animation span:nth-child(3) {
            left: 70%;
            width: 20px;
            height: 20px;
            animation-delay: 4s;
        }
        
        .bg-animation span:nth-child(4) {
            left: 40%;
            width: 60px;
            height: 60px;
            animation-delay: 0s;
            animation-duration: 18s;
        }
        
        .bg-animation span:nth-child(5) {
            left: 65%;
            width: 20px;
            height: 20px;
            animation-delay: 0s;
        }
        
        .bg-animation span:nth-child(6) {
            left: 75%;
            width: 110px;
            height: 110px;
            animation-delay: 3s;
        }
        
        .bg-animation span:nth-child(7) {
            left: 35%;
            width: 150px;
            height: 150px;
            animation-delay: 7s;
        }
        
        .bg-animation span:nth-child(8) {
            left: 50%;
            width: 25px;
            height: 25px;
            animation-delay: 15s;
            animation-duration: 45s;
        }
        
        .bg-animation span:nth-child(9) {
            left: 20%;
            width: 15px;
            height: 15px;
            animation-delay: 2s;
            animation-duration: 35s;
        }
        
        .bg-animation span:nth-child(10) {
            left: 85%;
            width: 150px;
            height: 150px;
            animation-delay: 0s;
            animation-duration: 11s;
        }
        
        @keyframes move {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
                border-radius: 0;
            }
            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
                border-radius: 50%;
            }
        }
        
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            animation: slideInUp 0.6s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .login-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            position: relative;
            z-index: 1;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-floating label {
            color: var(--secondary-color);
        }
        
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: var(--primary-color);
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            padding: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .input-group-text {
            background: var(--light-color);
            border: 2px solid #e9ecef;
            border-right: none;
            color: var(--secondary-color);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: var(--border-radius);
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 123, 255, 0.3);
            color: white;
        }
        
        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-login .spinner-border {
            width: 1.2rem;
            height: 1.2rem;
            margin-right: 0.5rem;
        }
        
        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e9ecef;
        }
        
        .divider span {
            background: rgba(255, 255, 255, 0.95);
            padding: 0 1rem;
            color: var(--secondary-color);
            font-size: 0.9rem;
        }
        
        .demo-credentials {
            background: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--info-color);
        }
        
        .demo-credentials h6 {
            color: var(--info-color);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .demo-credentials .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.25rem;
            font-size: 0.85rem;
        }
        
        .demo-credentials .credential-item:last-child {
            margin-bottom: 0;
        }
        
        .demo-credentials .badge {
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .demo-credentials .badge:hover {
            transform: scale(1.05);
        }
        
        .alert {
            border-radius: var(--border-radius);
            border: none;
            animation: slideInDown 0.3s ease-out;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-check {
            margin-bottom: 1rem;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .password-toggle {
            cursor: pointer;
            color: var(--secondary-color);
            transition: color 0.2s ease;
        }
        
        .password-toggle:hover {
            color: var(--primary-color);
        }
        
        .footer-links {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }
        
        .footer-links a {
            color: var(--secondary-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }
        
        .footer-links a:hover {
            color: var(--primary-color);
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .login-container {
                margin: 1rem;
            }
            
            .login-header {
                padding: 2rem 1.5rem;
            }
            
            .login-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animation">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
    
    <div class="login-container">
        <div class="login-card">
            <!-- Login Header -->
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-university"></i>
                </div>
                <h1><?php echo APP_NAME; ?></h1>
                <p>Sistem Koperasi Digital Terpadu</p>
            </div>
            
            <!-- Login Body -->
            <div class="login-body">
                <!-- Error/Success Messages -->
                <?php if ($loginError): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($loginError); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($successMessage): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($successMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Demo Credentials -->
                <div class="demo-credentials">
                    <h6><i class="fas fa-info-circle me-1"></i> Akun Demo</h6>
                    <div class="credential-item">
                        <span>Bos/Pemilik:</span>
                        <span class="badge bg-danger" onclick="fillDemo('bos')">bos/bos</span>
                    </div>
                    <div class="credential-item">
                        <span>Admin:</span>
                        <span class="badge bg-primary" onclick="fillDemo('admin')">admin/admin</span>
                    </div>
                    <div class="credential-item">
                        <span>Teller:</span>
                        <span class="badge bg-success" onclick="fillDemo('teller')">teller/teller</span>
                    </div>
                    <div class="credential-item">
                        <span>Petugas Lapangan:</span>
                        <span class="badge bg-warning" onclick="fillDemo('collector')">collector/collector</span>
                    </div>
                    <div class="credential-item">
                        <span>Nasabah:</span>
                        <span class="badge bg-info" onclick="fillDemo('nasabah')">nasabah/nasabah</span>
                    </div>
                </div>
                
                <!-- Login Form -->
                <form id="loginForm" method="POST">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-floating">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                        <label for="username"><i class="fas fa-user me-2"></i>Username</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                        <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                        <span class="password-toggle position-absolute end-0 top-50 translate-middle-y me-3" onclick="togglePassword()">
                            <i class="fas fa-eye" id="passwordIcon"></i>
                        </span>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Ingat saya
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-login w-100" id="loginBtn">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Masuk
                    </button>
                </form>
                
                <div class="divider">
                    <span>atau</span>
                </div>
                
                <!-- Footer Links -->
                <div class="footer-links">
                    <a href="#" onclick="showHelp()"><i class="fas fa-question-circle me-1"></i>Bantuan</a>
                    <span class="mx-2 text-muted">|</span>
                    <a href="/"><i class="fas fa-home me-1"></i>Kembali ke Beranda</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = e.target;
            const submitBtn = document.getElementById('loginBtn');
            const originalContent = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Masuk...';
            
            // Create FormData from form
            const formData = new FormData(form);
            
            // Convert FormData to URL-encoded string
            const urlEncodedData = new URLSearchParams(formData).toString();
            
            // Send AJAX request
            fetch('api/auth.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: urlEncodedData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Store token
                    if (data.token) {
                        localStorage.setItem('authToken', data.token);
                        sessionStorage.setItem('authToken', data.token);
                    }
                    
                    // Show success message
                    showAlert('success', data.message);
                    
                    // Redirect after delay
                    setTimeout(() => {
                        window.location.href = data.redirect || '/mono-v2/main.php';
                    }, 1000);
                } else {
                    // Show error
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Login error:', error);
                showAlert('danger', 'Terjadi kesalahan. Silakan coba lagi.');
            })
            .finally(() => {
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalContent;
            });
        });
        
        // Password toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        }
        
        // Fill demo credentials
        function fillDemo(type) {
            const credentials = {
                'bos': { username: 'bos', password: 'bos' },
                'admin': { username: 'admin', password: 'admin' },
                'teller': { username: 'teller', password: 'teller' },
                'collector': { username: 'collector', password: 'collector' },
                'nasabah': { username: 'nasabah', password: 'nasabah' }
            };
            
            const cred = credentials[type];
            if (cred) {
                document.getElementById('username').value = cred.username;
                document.getElementById('password').value = cred.password;
                
                // Focus on password field
                document.getElementById('password').focus();
                
                // Visual feedback
                const passwordField = document.getElementById('password');
                passwordField.style.borderColor = '#28a745';
                setTimeout(() => {
                    passwordField.style.borderColor = '';
                }, 1000);
            }
        }
        
        // Show alert
        function showAlert(type, message) {
            // Remove existing alerts
            const existingAlerts = document.querySelectorAll('.alert');
            existingAlerts.forEach(alert => alert.remove());
            
            // Create new alert
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            // Insert at the top of login body
            const loginBody = document.querySelector('.login-body');
            loginBody.insertBefore(alertDiv, loginBody.firstChild);
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
        
        // Show help
        function showHelp() {
            showAlert('info', 'Gunakan akun demo untuk mencoba sistem. Hubungi admin untuk akun production.');
        }
        
        // Check for existing session
        document.addEventListener('DOMContentLoaded', function() {
            // Check if user is already logged in
            fetch('api/auth.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=check_session'
            })
            .then(response => response.json())
            .then(data => {
                if (data.authenticated && data.user) {
                    // User is logged in, redirect
                    window.location.href = '/mono-v2/main.php';
                }
            })
            .catch(error => {
                console.log('Session check failed:', error);
            });
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+Enter or Cmd+Enter to submit form
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                const form = document.getElementById('loginForm');
                if (form.checkValidity()) {
                    form.dispatchEvent(new Event('submit'));
                }
            }
        });
        
        // Auto-focus username field
        document.getElementById('username').focus();
    </script>
</body>
</html>
