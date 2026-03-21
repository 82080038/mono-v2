#!/usr/bin/env node

/**
 * Unified Login Implementation
 * Convert multiple login pages to single unified login page
 */

const fs = require('fs');
const path = require('path');

class UnifiedLoginImplementer {
    constructor() {
        this.projectRoot = '/opt/lampp/htdocs/mono-v2';
        this.backupDir = path.join(this.projectRoot, 'backup-login-pages');
        this.unifiedLoginPath = path.join(this.projectRoot, 'login.html');
        this.changes = [];
    }

    async implementUnifiedLogin() {
        console.log('🚀 Starting Unified Login Implementation...\n');
        
        // Step 1: Create backup of existing login pages
        await this.createBackup();
        
        // Step 2: Create enhanced unified login page
        await this.createUnifiedLoginPage();
        
        // Step 3: Remove redundant login pages
        await this.removeRedundantPages();
        
        // Step 4: Update navigation links
        await this.updateNavigationLinks();
        
        // Step 5: Generate implementation report
        await this.generateReport();
        
        return this.changes;
    }

    async createBackup() {
        console.log('📦 Creating backup of existing login pages...');
        
        if (!fs.existsSync(this.backupDir)) {
            fs.mkdirSync(this.backupDir, { recursive: true });
        }
        
        const loginPages = [
            'login.html',
            'pages/admin/login.html',
            'pages/staff/login.html',
            'pages/member/login.html'
        ];
        
        for (const page of loginPages) {
            const sourcePath = path.join(this.projectRoot, page);
            const backupPath = path.join(this.backupDir, page.replace(/\//g, '_'));
            
            if (fs.existsSync(sourcePath)) {
                fs.copyFileSync(sourcePath, backupPath);
                this.changes.push({
                    type: 'backup',
                    file: page,
                    backupPath: backupPath
                });
                console.log(`   ✅ Backed up: ${page}`);
            }
        }
        
        console.log('   ✅ Backup completed\n');
    }

    async createUnifiedLoginPage() {
        console.log('🎨 Creating enhanced unified login page...');
        
        const unifiedLoginHTML = `<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <style>
        .login-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 2rem;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            opacity: 0.9;
            margin: 0;
            font-size: 0.95rem;
        }
        .login-body {
            padding: 2.5rem;
        }
        .role-selection {
            margin-bottom: 2rem;
        }
        .role-selection label {
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
            display: block;
        }
        .role-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .role-option {
            position: relative;
        }
        .role-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }
        .role-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem 0.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            margin: 0;
        }
        .role-option input[type="radio"]:checked + label {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            color: #667eea;
        }
        .role-option label:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }
        .role-option i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .role-option span {
            font-size: 0.85rem;
            font-weight: 500;
        }
        .form-floating {
            margin-bottom: 1.5rem;
        }
        .form-floating > label {
            color: #6c757d;
        }
        .form-control {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .btn-login:disabled {
            opacity: 0.6;
            transform: none;
        }
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
        }
        .password-toggle:hover {
            color: #667eea;
        }
        .quick-login-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e0e0e0;
        }
        .quick-login-section h6 {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 1rem;
            text-align: center;
        }
        .quick-login-btn {
            transition: all 0.3s ease;
            border-radius: 8px;
            font-weight: 500;
        }
        .quick-login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .quick-login-btn:active {
            transform: translateY(0);
        }
        .back-link {
            display: inline-block;
            margin-top: 1.5rem;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .back-link:hover {
            color: #764ba2;
        }
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            max-width: 400px;
        }
        @media (max-width: 768px) {
            .login-container {
                padding: 1rem;
            }
            .login-card {
                border-radius: 15px;
            }
            .login-header {
                padding: 1.5rem;
            }
            .login-body {
                padding: 2rem;
            }
            .role-options {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>
                    <i class="fas fa-piggy-bank me-2"></i>
                    KSP Lam Gabe Jaya
                </h1>
                <p>Sistem Koperasi Simpan Pinjam Digital</p>
            </div>
            
            <div class="login-body">
                <div id="alertContainer" class="alert-container"></div>
                
                <form id="loginForm">
                    <!-- Role Selection -->
                    <div class="role-selection">
                        <label>Pilih Role Anda:</label>
                        <div class="role-options">
                            <div class="role-option">
                                <input type="radio" id="roleAdmin" name="role" value="admin" checked>
                                <label for="roleAdmin">
                                    <i class="fas fa-user-shield"></i>
                                    <span>Admin</span>
                                </label>
                            </div>
                            <div class="role-option">
                                <input type="radio" id="roleStaff" name="role" value="staff">
                                <label for="roleStaff">
                                    <i class="fas fa-user-tie"></i>
                                    <span>Staff</span>
                                </label>
                            </div>
                            <div class="role-option">
                                <input type="radio" id="roleMember" name="role" value="member">
                                <label for="roleMember">
                                    <i class="fas fa-user"></i>
                                    <span>Member</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Username Field -->
                    <div class="form-floating">
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Username atau Email" required>
                        <label for="username">
                            <i class="fas fa-user me-2"></i>Username atau Email
                        </label>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="form-floating position-relative">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Kata Sandi" required>
                        <label for="password">
                            <i class="fas fa-lock me-2"></i>Kata Sandi
                        </label>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye" id="passwordIcon"></i>
                        </button>
                    </div>
                    
                    <!-- Remember Me -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">
                            Ingat saya
                        </label>
                    </div>
                    
                    <!-- Login Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-login" id="loginBtn">
                            <span id="loginBtnText">Masuk</span>
                            <span class="loading-spinner" style="display: none;">
                                <i class="fas fa-spinner fa-spin me-2"></i>Loading...
                            </span>
                        </button>
                    </div>
                </form>
                
                <!-- Quick Login Section -->
                <div class="quick-login-section">
                    <h6>
                        <i class="fas fa-rocket me-2"></i>
                        Quick Login (Development Mode)
                    </h6>
                    <div class="row g-2">
                        <div class="col-4">
                            <button type="button" class="btn btn-primary btn-sm w-100 quick-login-btn" 
                                    data-role="admin" data-username="admin" data-password="admin123">
                                <i class="fas fa-user-shield me-1"></i>
                                Admin
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-success btn-sm w-100 quick-login-btn" 
                                    data-role="staff" data-username="staff" data-password="staff123">
                                <i class="fas fa-user-tie me-1"></i>
                                Staff
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-info btn-sm w-100 quick-login-btn" 
                                    data-role="member" data-username="member" data-password="member123">
                                <i class="fas fa-user me-1"></i>
                                Member
                            </button>
                        </div>
                    </div>
                    <div class="alert alert-warning mt-3 mb-0 small" role="alert">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Development Only:</strong> Fitur ini hanya untuk testing development.
                    </div>
                </div>
                
                <!-- Forgot Password -->
                <div class="text-center mt-3">
                    <a href="#" class="text-decoration-none" onclick="showForgotPassword()">
                        <i class="fas fa-question-circle me-1"></i>Lupa kata sandi?
                    </a>
                </div>
                
                <!-- Back Link -->
                <div class="text-center">
                    <a href="index.html" class="back-link">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Password toggle functionality
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        });
        
        // Login form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const role = document.querySelector('input[name="role"]:checked').value;
            const rememberMe = document.getElementById('rememberMe').checked;
            
            // Show loading state
            const loginBtn = document.getElementById('loginBtn');
            const loginBtnText = document.getElementById('loginBtnText');
            const loadingSpinner = loginBtn.querySelector('.loading-spinner');
            
            loginBtn.disabled = true;
            loginBtnText.style.display = 'none';
            loadingSpinner.style.display = 'inline';
            
            // Call existing login function
            if (typeof window.simulateLoginAPI === "function") {
                window.simulateLoginAPI(username, password, role);
            } else {
                // Fallback login
                console.log("Login fallback for:", username, "role:", role);
                showAlert("Login functionality is being prepared...", "info");
                
                // Reset form state
                setTimeout(() => {
                    loginBtn.disabled = false;
                    loginBtnText.style.display = 'inline';
                    loadingSpinner.style.display = 'none';
                }, 2000);
            }
        });
        
        // Role selection change handler
        document.querySelectorAll('input[name="role"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Auto-fill credentials based on role (development mode)
                const role = this.value;
                const credentials = {
                    admin: { username: 'admin', password: 'admin123' },
                    staff: { username: 'staff', password: 'staff123' },
                    member: { username: 'member', password: 'member123' }
                };
                
                // Optional: Auto-fill for development
                // document.getElementById('username').value = credentials[role].username;
                // document.getElementById('password').value = credentials[role].password;
            });
        });
        
        function showAlert(message, type = "danger") {
            const alertContainer = document.getElementById("alertContainer");
            const alertHtml = \`
                <div class="alert alert-\${type} alert-dismissible fade show" role="alert">
                    <i class="fas fa-\${type === "success" ? "check-circle" : type === "danger" ? "exclamation-triangle" : "info-circle"} me-2"></i>
                    \${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            \`;
            
            alertContainer.innerHTML = alertHtml;
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                const alert = alertContainer.querySelector(".alert");
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        }
        
        function showForgotPassword() {
            showAlert("Fitur reset password akan segera tersedia", "info");
        }
        
        // Enhanced login success handler with smart redirect
        function handleLoginSuccess(response, role) {
            if (response.success) {
                // Store auth data
                localStorage.setItem('authToken', response.token);
                localStorage.setItem('userRole', role);
                localStorage.setItem('userName', response.user.name || response.user.username);
                
                // Smart redirect based on role
                const redirectUrls = {
                    admin: 'pages/admin/dashboard.html',
                    staff: 'pages/staff/dashboard.html',
                    member: 'pages/member/dashboard.html'
                };
                
                const redirectUrl = redirectUrls[role] || 'pages/admin/dashboard.html';
                
                // Show success message
                showAlert(\`Login berhasil sebagai \${role.charAt(0).toUpperCase() + role.slice(1)}!\`, 'success');
                
                // Redirect after short delay
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 1500);
            } else {
                showNotification(response.message || 'Login failed', 'danger');
            }
        }
        
        // Enhanced simulate login API with role parameter
        function simulateLoginAPI(username, password, role) {
            // Simulate successful login for testing
            setTimeout(() => {
                const response = {
                    success: true,
                    token: 'unified-login-token-' + Date.now(),
                    user: { 
                        name: username.charAt(0).toUpperCase() + username.slice(1), 
                        username: username, 
                        role: role,
                        id: 1
                    },
                    message: 'Login successful'
                };
                
                handleLoginSuccess(response, role);
            }, 1000);
        }
        
        // Quick Login Functionality
        function quickLogin(role, username, password) {
            console.log(\`Quick login: \${role} - \${username}\`);
            
            // Select role radio button
            document.querySelector(\`input[name="role"][value="\${role}"]\`).checked = true;
            
            // Show loading state
            const loginBtn = document.getElementById('loginBtn');
            const loginBtnText = document.getElementById('loginBtnText');
            const loadingSpinner = loginBtn.querySelector('.loading-spinner');
            
            loginBtn.disabled = true;
            loginBtnText.style.display = 'none';
            loadingSpinner.style.display = 'inline';
            
            // Fill form fields
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;
            
            // Simulate login process
            setTimeout(() => {
                const response = {
                    success: true,
                    token: 'quick-login-token-' + Date.now(),
                    user: { 
                        name: username.charAt(0).toUpperCase() + username.slice(1), 
                        username: username, 
                        role: role,
                        id: 1
                    },
                    message: 'Quick login successful'
                };
                
                handleLoginSuccess(response, role);
                
                // Show success message
                showAlert(\`Login berhasil sebagai \${role}!\`, 'success');
                
            }, 1000);
        }
        
        // Quick Login Button Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            const quickLoginBtns = document.querySelectorAll('.quick-login-btn');
            
            quickLoginBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const role = this.getAttribute('data-role');
                    const username = this.getAttribute('data-username');
                    const password = this.getAttribute('data-password');
                    
                    quickLogin(role, username, password);
                });
            });
            
            // Add hover effects to quick login buttons
            quickLoginBtns.forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05)';
                    this.style.transition = 'transform 0.2s ease';
                });
                
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
        
        // Reset form state on error
        window.addEventListener("loginError", function() {
            const loginBtn = document.getElementById("loginBtn");
            const loginBtnText = document.getElementById("loginBtnText");
            const loadingSpinner = loginBtn.querySelector(".loading-spinner");
            
            loginBtn.disabled = false;
            loginBtnText.style.display = "inline";
            loadingSpinner.style.display = "none";
        });
    </script>
</body>
</html>`;
        
        fs.writeFileSync(this.unifiedLoginPath, unifiedLoginHTML);
        
        this.changes.push({
            type: 'created',
            file: 'login.html',
            action: 'Enhanced unified login page with role selection'
        });
        
        console.log('   ✅ Enhanced unified login page created\n');
    }

    async removeRedundantPages() {
        console.log('🗑️ Removing redundant login pages...');
        
        const redundantPages = [
            'pages/admin/login.html',
            'pages/staff/login.html',
            'pages/member/login.html'
        ];
        
        for (const page of redundantPages) {
            const pagePath = path.join(this.projectRoot, page);
            
            if (fs.existsSync(pagePath)) {
                fs.unlinkSync(pagePath);
                this.changes.push({
                    type: 'removed',
                    file: page,
                    action: 'Removed redundant login page'
                });
                console.log(`   ✅ Removed: ${page}`);
            }
        }
        
        console.log('   ✅ Redundant pages removed\n');
    }

    async updateNavigationLinks() {
        console.log('🔗 Updating navigation links...');
        
        // Find all HTML files that might contain login links
        const htmlFiles = this.getAllHtmlFiles();
        
        for (const file of htmlFiles) {
            if (file.includes('login.html')) continue; // Skip login files
            
            try {
                let content = fs.readFileSync(file, 'utf8');
                let modified = false;
                
                // Update role-specific login links to unified login
                const linkUpdates = [
                    { from: 'pages/admin/login.html', to: 'login.html' },
                    { from: 'pages/staff/login.html', to: 'login.html' },
                    { from: 'pages/member/login.html', to: 'login.html' },
                    { from: '../pages/admin/login.html', to: 'login.html' },
                    { from: '../pages/staff/login.html', to: 'login.html' },
                    { from: '../pages/member/login.html', to: 'login.html' },
                    { from: '../../pages/admin/login.html', to: 'login.html' },
                    { from: '../../pages/staff/login.html', to: 'login.html' },
                    { from: '../../pages/member/login.html', to: 'login.html' }
                ];
                
                for (const update of linkUpdates) {
                    if (content.includes(update.from)) {
                        content = content.replace(new RegExp(update.from.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), update.to);
                        modified = true;
                    }
                }
                
                if (modified) {
                    fs.writeFileSync(file, content);
                    this.changes.push({
                        type: 'updated',
                        file: file,
                        action: 'Updated login links to unified login'
                    });
                    console.log(`   ✅ Updated: ${file}`);
                }
            } catch (error) {
                console.log(`   ⚠️ Skipped: ${file} (${error.message})`);
            }
        }
        
        console.log('   ✅ Navigation links updated\n');
    }

    getAllHtmlFiles() {
        const htmlFiles = [];
        
        function findHtmlFiles(dir) {
            try {
                const files = fs.readdirSync(dir);
                
                for (const file of files) {
                    const filePath = path.join(dir, file);
                    const stat = fs.statSync(filePath);
                    
                    if (stat.isDirectory()) {
                        findHtmlFiles(filePath);
                    } else if (file.endsWith('.html')) {
                        htmlFiles.push(filePath);
                    }
                }
            } catch (error) {
                // Skip directories that can't be read
            }
        }
        
        findHtmlFiles(this.projectRoot);
        return htmlFiles;
    }

    async generateReport() {
        console.log('📊 Generating implementation report...');
        
        const report = {
            summary: {
                totalChanges: this.changes.length,
                backupCreated: this.changes.filter(c => c.type === 'backup').length,
                filesCreated: this.changes.filter(c => c.type === 'created').length,
                filesRemoved: this.changes.filter(c => c.type === 'removed').length,
                filesUpdated: this.changes.filter(c => c.type === 'updated').length,
                timestamp: new Date().toISOString()
            },
            changes: this.changes,
            benefits: [
                'Single login page for all roles',
                'Role selection with visual interface',
                'Smart redirect to appropriate dashboard',
                'Reduced code duplication',
                'Easier maintenance',
                'Better user experience',
                'Consistent design across all login scenarios'
            ],
            nextSteps: [
                'Test all role login scenarios',
                'Verify dashboard redirects work correctly',
                'Update any remaining hardcoded links',
                'Test quick login functionality',
                'Verify responsive design on mobile'
            ]
        };
        
        // Save report
        const reportPath = path.join(this.projectRoot, 'unified-login-implementation-report.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        console.log(`✅ Report saved: ${reportPath}`);
        console.log(`📊 Total changes: ${report.summary.totalChanges}`);
        console.log(`📦 Backup files: ${report.summary.backupCreated}`);
        console.log(`📝 Files created: ${report.summary.filesCreated}`);
        console.log(`🗑️ Files removed: ${report.summary.filesRemoved}`);
        console.log(`🔗 Files updated: ${report.summary.filesUpdated}`);
        
        return report;
    }
}

// Main execution
async function main() {
    const implementer = new UnifiedLoginImplementer();
    
    try {
        const changes = await implementer.implementUnifiedLogin();
        
        console.log('\n🎉 UNIFIED LOGIN IMPLEMENTATION COMPLETED!');
        console.log('\n📋 SUMMARY:');
        console.log('✅ Single unified login page created');
        console.log('✅ Role selection interface added');
        console.log('✅ Smart redirect logic implemented');
        console.log('✅ Redundant login pages removed');
        console.log('✅ Navigation links updated');
        console.log('✅ Backup of original pages created');
        
        console.log('\n🎯 BENEFITS:');
        console.log('• One login URL for all roles');
        console.log('• Visual role selection');
        console.log('• Smart dashboard redirect');
        console.log('• Reduced code duplication');
        console.log('• Easier maintenance');
        
        return changes;
        
    } catch (error) {
        console.error('❌ Implementation failed:', error.message);
        throw error;
    }
}

// Run if called directly
if (require.main === module) {
    main().catch(console.error);
}

module.exports = UnifiedLoginImplementer;
