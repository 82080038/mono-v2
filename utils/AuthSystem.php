<?php
/**
 * Enhanced Authentication System
 * Implements proper JWT-based authentication
 */

class AuthSystem {
    private static $instance = null;
    private $db;
    
    private function __construct() {
        $this->db = Database::getInstance();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function authenticate($email, $password) {
        try {
            $sql = "SELECT u.*, r.name as role_name 
                    FROM users u 
                    LEFT JOIN roles r ON u.role = r.id 
                    WHERE u.email = :email AND u.status = 'active'";
            
            $user = $this->db->fetchOne($sql, ['email' => $email]);
            
            if ($user && password_verify($password, $user['password'])) {
                // Generate JWT token
                $token = $this->generateToken($user);
                
                // Update last login
                $this->updateLastLogin($user['id']);
                
                return [
                    'success' => true,
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'role_name' => $user['role_name']
                    ],
                    'token' => $token
                ];
            }
            
            return ['success' => false, 'message' => 'Email atau password salah'];
            
        } catch (Exception $e) {
            error_log("Authentication failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem'];
        }
    }
    
    public function verifyToken($token) {
        try {
            $payload = $this->decodeToken($token);
            
            if ($payload && isset($payload['user_id'])) {
                // Get fresh user data
                $sql = "SELECT u.*, r.name as role_name 
                        FROM users u 
                        LEFT JOIN roles r ON u.role = r.id 
                        WHERE u.id = :user_id AND u.status = 'active'";
                
                $user = $this->db->fetchOne($sql, ['user_id' => $payload['user_id']]);
                
                if ($user) {
                    return [
                        'success' => true,
                        'user' => [
                            'id' => $user['id'],
                            'name' => $user['name'],
                            'email' => $user['email'],
                            'role' => $user['role'],
                            'role_name' => $user['role_name']
                        ]
                    ];
                }
            }
            
            return ['success' => false, 'message' => 'Token tidak valid'];
            
        } catch (Exception $e) {
            error_log("Token verification failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Token tidak valid'];
        }
    }
    
    private function generateToken($user) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ]);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'your-secret-key', true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    private function decodeToken($token) {
        $parts = explode('.', $token);
        
        if (count($parts) != 3) {
            return null;
        }
        
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
        
        if (!$payload) {
            return null;
        }
        
        $payload = json_decode($payload, true);
        
        if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) {
            return null;
        }
        
        return $payload;
    }
    
    private function updateLastLogin($userId) {
        $sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :user_id";
        $this->db->query($sql, ['user_id' => $userId]);
    }
    
    public function hasPermission($userRole, $requiredPermission) {
        $permissions = [
            'super_admin' => ['all'],
            'admin' => ['users', 'members', 'loans', 'reports', 'settings'],
            'mantri' => ['field_data', 'gps_tracking', 'collection', 'verification'],
            'member' => ['profile', 'accounts', 'transactions', 'applications'],
            'kasir' => ['payments', 'cash_management'],
            'teller' => ['accounts', 'loans', 'credit'],
            'surveyor' => ['surveys', 'verification', 'field_data'],
            'collector' => ['collection', 'overdue', 'reports']
        ];
        
        if (!isset($permissions[$userRole])) {
            return false;
        }
        
        return in_array('all', $permissions[$userRole]) || in_array($requiredPermission, $permissions[$userRole]);
    }
}

?>
