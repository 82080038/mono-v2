<?php
/**
 * KSP Lam Gabe Jaya - Auth System
 * Complete authentication system with database support
 */

// Allow direct access for API calls
if (!defined('ALLOW_DIRECT_ACCESS') && !defined('APP_ROOT')) {
    define('ALLOW_DIRECT_ACCESS', true);
}

namespace Core\Auth;

class AuthSystem {
    private $db;
    
    public function __construct() {
        try {
            // Try socket connection first (XAMPP specific)
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            // Add socket if defined
            if (defined('DB_SOCKET')) {
                $dsn .= ";unix_socket=" . DB_SOCKET;
            }
            
            $this->db = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        } catch (Exception $e) {
            // Try without socket as fallback
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $this->db = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
            } catch (Exception $e2) {
                throw new Exception("Database connection failed: " . $e2->getMessage());
            }
        }
    }
    
    public function authenticate($username, $password) {
        try {
            // Try database authentication first
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username AND status = 'active'");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Update last login
                $updateStmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                $updateStmt->execute(['id' => $user['id']]);
                
                // Remove sensitive data
                unset($user['password']);
                return $user;
            }
            
            return null;
        } catch (Exception $e) {
            throw new Exception("Authentication failed: " . $e->getMessage());
        }
    }
    
    public function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }
    
    public function createSession($user) {
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();
    }
    
    public function destroySession() {
        session_unset();
        session_destroy();
    }
    
    public function hasPermission($role) {
        $user = $this->getCurrentUser();
        if (!$user) return false;
        
        $roleHierarchy = [
            'admin' => 4,
            'manager' => 3,
            'staff' => 2,
            'member' => 1
        ];
        
        return $roleHierarchy[$user['role']] >= $roleHierarchy[$role];
    }
}
?>
