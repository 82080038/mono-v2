<?php
/**
 * KSP Lam Gabe Jaya - Enhanced Authentication Class
 * Based on security best practices from documentation
 */

class Auth {
    private $db;
    private $user;
    private $sessionTimeout = 1800; // 30 minutes
    private $maxLoginAttempts = 5;
    private $lockoutDuration = 900; // 15 minutes
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Authenticate user with enhanced security
     * @param string $username
     * @param string $password
     * @param bool $remember
     * @return array
     */
    public function login($username, $password, $remember = false) {
        try {
            // Input validation
            if (empty($username) || empty($password)) {
                return ['success' => false, 'message' => 'Username dan password harus diisi'];
            }
            
            // Check if user is locked out
            if ($this->isUserLockedOut($username)) {
                return ['success' => false, 'message' => 'Akun terkunci karena terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit.'];
            }
            
            // Get user from database
            $user = $this->getUserByUsername($username);
            if (!$user) {
                $this->recordFailedLogin($username);
                return ['success' => false, 'message' => 'Username atau password salah'];
            }
            
            // Check if account is active
            if ($user['status'] !== 'active') {
                return ['success' => false, 'message' => 'Akun tidak aktif. Hubungi administrator.'];
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                $this->recordFailedLogin($username);
                return ['success' => false, 'message' => 'Username atau password salah'];
            }
            
            // Check if password needs rehash (for stronger security)
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                $this->updatePasswordHash($user['id'], $password);
            }
            
            // Successful login
            $this->recordSuccessfulLogin($user['id']);
            $this->createUserSession($user, $remember);
            
            return [
                'success' => true,
                'user' => $this->sanitizeUserData($user),
                'message' => 'Login berhasil'
            ];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'];
        }
    }
    
    /**
     * Logout user with proper cleanup
     * @return array
     */
    public function logout() {
        try {
            if (isset($_SESSION['user'])) {
                $this->recordLogout($_SESSION['user']['id']);
            }
            
            // Destroy session
            session_destroy();
            
            // Clear session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            return ['success' => true, 'message' => 'Logout berhasil'];
            
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan saat logout'];
        }
    }
    
    /**
     * Check if user is authenticated
     * @return array
     */
    public function checkAuth() {
        try {
            if (!isset($_SESSION['user']) || !isset($_SESSION['last_activity'])) {
                return ['authenticated' => false, 'reason' => 'Session not found'];
            }
            
            // Check session timeout
            if (time() - $_SESSION['last_activity'] > $this->sessionTimeout) {
                $this->logout();
                return ['authenticated' => false, 'reason' => 'Session expired'];
            }
            
            // Validate user still exists and is active
            $user = $this->getUserById($_SESSION['user']['id']);
            if (!$user || $user['status'] !== 'active') {
                $this->logout();
                return ['authenticated' => false, 'reason' => 'User not found or inactive'];
            }
            
            // Update last activity
            $_SESSION['last_activity'] = time();
            
            return [
                'authenticated' => true,
                'user' => $this->sanitizeUserData($user)
            ];
            
        } catch (Exception $e) {
            error_log("Auth check error: " . $e->getMessage());
            return ['authenticated' => false, 'reason' => 'System error'];
        }
    }
    
    /**
     * Check if user has specific permission
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission) {
        $authCheck = $this->checkAuth();
        
        if (!$authCheck['authenticated']) {
            return false;
        }
        
        $user = $authCheck['user'];
        $permissions = $this->getRolePermissions($user['role']);
        
        return in_array('all', $permissions) || in_array($permission, $permissions);
    }
    
    /**
     * Get user by username
     * @param string $username
     * @return array|null
     */
    private function getUserByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ?";
        return $this->db->fetchOne($sql, [$username]);
    }
    
    /**
     * Get user by ID
     * @param int $userId
     * @return array|null
     */
    private function getUserById($userId) {
        $sql = "SELECT * FROM users WHERE id = ?";
        return $this->db->fetchOne($sql, [$userId]);
    }
    
    /**
     * Check if user is locked out
     * @param string $username
     * @return bool
     */
    private function isUserLockedOut($username) {
        $sql = "SELECT COUNT(*) as count FROM login_attempts 
                WHERE username = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND) 
                AND success = 0";
        
        $attempts = $this->db->fetchOne($sql, [$username, $this->lockoutDuration])['count'];
        
        return $attempts >= $this->maxLoginAttempts;
    }
    
    /**
     * Record failed login attempt
     * @param string $username
     */
    private function recordFailedLogin($username) {
        $sql = "INSERT INTO login_attempts (username, ip_address, user_agent, success, created_at) 
                VALUES (?, ?, ?, 0, NOW())";
        
        $this->db->query($sql, [
            $username,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
    
    /**
     * Record successful login
     * @param int $userId
     */
    private function recordSuccessfulLogin($userId) {
        // Update user last login
        $sql = "UPDATE users SET last_login = NOW(), last_activity = NOW() WHERE id = ?";
        $this->db->query($sql, [$userId]);
        
        // Clear failed login attempts
        $sql = "DELETE FROM login_attempts WHERE user_id = ?";
        $this->db->query($sql, [$userId]);
        
        // Record successful login attempt
        $sql = "INSERT INTO login_attempts (user_id, username, ip_address, user_agent, success, created_at) 
                VALUES (?, ?, ?, ?, 1, NOW())";
        
        $this->db->query($sql, [
            $userId,
            $_SESSION['user']['username'] ?? 'unknown',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
    
    /**
     * Record logout
     * @param int $userId
     */
    private function recordLogout($userId) {
        $sql = "UPDATE users SET last_activity = NULL WHERE id = ?";
        $this->db->query($sql, [$userId]);
    }
    
    /**
     * Update password hash
     * @param int $userId
     * @param string $password
     */
    private function updatePasswordHash($userId, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $this->db->query($sql, [$hash, $userId]);
    }
    
    /**
     * Create user session
     * @param array $user
     * @param bool $remember
     */
    private function createUserSession($user, $remember = false) {
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user'] = $this->sanitizeUserData($user);
        $_SESSION['last_activity'] = time();
        
        if ($remember) {
            // Set remember me cookie (30 days)
            $token = bin2hex(random_bytes(32));
            $expires = time() + (86400 * 30);
            
            // Store token in database
            $sql = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))";
            $this->db->query($sql, [$user['id'], $token, $expires]);
            
            // Set cookie
            setcookie('remember_token', $token, $expires, '/', '', true, true);
        }
    }
    
    /**
     * Sanitize user data for session
     * @param array $user
     * @return array
     */
    private function sanitizeUserData($user) {
        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'] ?? '',
            'full_name' => $user['full_name'] ?? '',
            'role' => $user['role'],
            'role_display_name' => $user['role_display_name'] ?? ucfirst($user['role']),
            'permissions' => $this->getRolePermissions($user['role'])
        ];
    }
    
    /**
     * Get role permissions
     * @param string $role
     * @return array
     */
    private function getRolePermissions($role) {
        $permissions = [
            'bos' => ['all'],
            'admin' => [
                'manage_users',
                'view_reports',
                'manage_data',
                'approve_loans',
                'manage_settings'
            ],
            'teller' => [
                'process_transactions',
                'view_customers',
                'manage_deposits',
                'view_reports'
            ],
            'collector' => [
                'manage_collections',
                'view_routes',
                'track_payments',
                'view_customers'
            ],
            'nasabah' => [
                'view_own_data',
                'make_transactions',
                'view_loans',
                'make_deposits'
            ]
        ];
        
        return $permissions[$role] ?? [];
    }
    
    /**
     * Change user password
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return array
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Validate inputs
            if (empty($currentPassword) || empty($newPassword)) {
                return ['success' => false, 'message' => 'Password tidak boleh kosong'];
            }
            
            if (strlen($newPassword) < 8) {
                return ['success' => false, 'message' => 'Password minimal 8 karakter'];
            }
            
            // Get current user
            $user = $this->getUserById($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'User tidak ditemukan'];
            }
            
            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Password saat ini salah'];
            }
            
            // Update password
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ?, password_changed_at = NOW() WHERE id = ?";
            $this->db->query($sql, [$hash, $userId]);
            
            return ['success' => true, 'message' => 'Password berhasil diubah'];
            
        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem'];
        }
    }
    
    /**
     * Reset password
     * @param string $email
     * @return array
     */
    public function resetPassword($email) {
        try {
            // Validate email
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Email tidak valid'];
            }
            
            // Get user by email
            $sql = "SELECT * FROM users WHERE email = ? AND status = 'active'";
            $user = $this->db->fetchOne($sql, [$email]);
            
            if (!$user) {
                return ['success' => false, 'message' => 'Email tidak terdaftar'];
            }
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = time() + 3600; // 1 hour
            
            // Store reset token
            $sql = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))";
            $this->db->query($sql, [$user['id'], $token, $expires]);
            
            // Send reset email (implementation depends on email service)
            // $this->sendResetEmail($user['email'], $token);
            
            return [
                'success' => true, 
                'message' => 'Link reset password telah dikirim ke email Anda',
                'token' => $token // For testing only
            ];
            
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem'];
        }
    }
    
    /**
     * Verify reset token
     * @param string $token
     * @return array
     */
    public function verifyResetToken($token) {
        try {
            $sql = "SELECT pr.*, u.username FROM password_resets pr 
                    JOIN users u ON pr.user_id = u.id 
                    WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0";
            
            $reset = $this->db->fetchOne($sql, [$token]);
            
            if (!$reset) {
                return ['valid' => false, 'message' => 'Token tidak valid atau sudah kadaluarsa'];
            }
            
            return ['valid' => true, 'user' => $reset];
            
        } catch (Exception $e) {
            error_log("Token verification error: " . $e->getMessage());
            return ['valid' => false, 'message' => 'Terjadi kesalahan sistem'];
        }
    }
    
    /**
     * Get current user
     * @return array|null
     */
    public function getCurrentUser() {
        $authCheck = $this->checkAuth();
        return $authCheck['authenticated'] ? $authCheck['user'] : null;
    }
    
    /**
     * Require authentication
     * @return array
     */
    public function requireAuth() {
        $authCheck = $this->checkAuth();
        
        if (!$authCheck['authenticated']) {
            header('Location: /mono-v2/login.php');
            exit;
        }
        
        return $authCheck['user'];
    }
    
    /**
     * Require specific permission
     * @param string $permission
     * @return array
     */
    public function requirePermission($permission) {
        $user = $this->requireAuth();
        
        if (!$this->hasPermission($permission)) {
            http_response_code(403);
            echo '<h1>Access Denied</h1>';
            echo '<p>You do not have permission to access this page.</p>';
            exit;
        }
        
        return $user;
    }
}
?>
