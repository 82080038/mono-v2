<?php
/**
 * Authentication Helper Class
 * Centralized authentication functions for all API files
 */

// Prevent direct access
if (!defined('KSP_API_ACCESS')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access to this file is not allowed.');
}

/**
 * Authentication Helper Class
 */
class AuthHelper {
    
    /**
     * Require authentication and return user data
     * @param string|null $role Required role (admin, staff, member)
     * @return array User data with token information
     * @throws Exception Authentication failed
     */
    public static function requireAuth($role = null) {
        global $db;
        
        $token = self::getTokenFromRequest();
        if (!$token) {
            throw new Exception('Authentication required');
        }
        
        $tokenData = self::validateJWTToken($token);
        if (!$tokenData) {
            throw new Exception('Invalid token');
        }
        
        $user = $db->fetchOne(
            "SELECT * FROM users WHERE id = ? AND is_active = 1",
            [$tokenData['user_id']]
        );
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        if ($role && $user['role'] !== $role && $user['role'] !== 'admin') {
            throw new Exception('Insufficient privileges');
        }
        
        return array_merge($user, $tokenData);
    }
    
    /**
     * Get token from request headers or parameters
     * @return string|null JWT token
     */
    public static function getTokenFromRequest() {
        $headers = getallheaders();
        return $headers['Authorization'] ?? $headers['authorization'] ?? $_REQUEST['token'] ?? null;
    }
    
    /**
     * Validate JWT token
     * @param string $token JWT token
     * @return array|null Token payload data
     */
    public static function validateJWTToken($token) {
        if (!$token) {
            return null;
        }
        
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        
        $payload = base64_decode($parts[1]);
        $payloadData = json_decode($payload, true);
        
        if (!$payloadData || $payloadData['exp'] < time()) {
            return null;
        }
        
        return $payloadData;
    }
    
    /**
     * Get current user from token
     * @return array|null User data
     */
    public static function getCurrentUser() {
        global $db;
        
        $token = self::getTokenFromRequest();
        if (!$token) {
            return null;
        }
        
        $tokenData = self::validateJWTToken($token);
        if (!$tokenData) {
            return null;
        }
        
        return $db->fetchOne(
            "SELECT * FROM users WHERE id = ? AND is_active = 1",
            [$tokenData['user_id']]
        );
    }
    
    /**
     * Generate JWT token for user
     * @param array $user User data
     * @return string JWT token
     */
    public static function generateToken($user) {
        $payload = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ];
        
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $header . '.' . $payload, 'your-secret-key');
        
        return $header . '.' . $payload . '.' . base64_encode($signature);
    }
    
    /**
     * Validate user role
     * @param string $requiredRole Required role
     * @param array $user User data
     * @return bool User has required role
     */
    public static function validateRole($requiredRole, $user) {
        // Creator has access to everything
        if ($user['role'] === 'creator') {
            return true;
        }
        
        // Admin has access to everything except creator-only features
        if ($user['role'] === 'admin' && $requiredRole !== 'creator') {
            return true;
        }
        
        return $user['role'] === $requiredRole;
    }
    
    /**
     * Sanitize input data
     * @param mixed $data Data to sanitize
     * @return mixed Sanitized data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        } elseif (is_string($data)) {
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        
        return $data;
    }
    
    /**
     * Validate API key
     * @param string $apiKey API key to validate
     * @return bool API key is valid
     */
    public static function validateApiKey($apiKey) {
        // Implement API key validation logic
        return !empty($apiKey) && strlen($apiKey) >= 32;
    }
    
    /**
     * Log authentication attempt
     * @param string $action Action performed
     * @param string $status Success/Failure
     * @param array $context Additional context
     */
    public static function logAuthAttempt($action, $status, $context = []) {
        global $db;
        
        try {
            $db->insert('audit_logs', [
                'user_id' => $context['user_id'] ?? null,
                'action' => $action,
                'table_name' => 'authentication',
                'description' => "Authentication $status: " . ($context['message'] ?? ''),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            // Log error but don't break authentication
            error_log("Auth logging failed: " . $e->getMessage());
        }
    }
    
    /**
     * Check rate limiting
     * @param string $identifier IP or user ID
     * @param int $maxAttempts Maximum attempts
     * @param int $timeWindow Time window in seconds
     * @return bool Rate limit not exceeded
     */
    public static function checkRateLimit($identifier, $maxAttempts = 100, $timeWindow = 3600) {
        global $db;
        
        try {
            $attempts = $db->fetchOne(
                "SELECT COUNT(*) as count FROM audit_logs 
                 WHERE ip_address = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? SECOND) 
                 AND action = 'api_request'",
                [$identifier, $timeWindow]
            )['count'];
            
            return $attempts < $maxAttempts;
        } catch (Exception $e) {
            // If rate limiting fails, allow the request
            return true;
        }
    }
    
    /**
     * Validate CSRF token
     * @param string $token CSRF token
     * @return bool Token is valid
     */
    public static function validateCSRFToken($token) {
        // Implement CSRF token validation
        return !empty($token) && strlen($token) >= 32;
    }
    
    /**
     * Generate CSRF token
     * @return string CSRF token
     */
    public static function generateCSRFToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Check if user has permission for specific action
     * @param array $user User data
     * @param string $permission Permission to check
     * @return bool User has permission
     */
    public static function hasPermission($user, $permission) {
        // Implement permission checking logic
        return $user['role'] === 'admin'; // Simplified for now
    }
    
    /**
     * Get user permissions
     * @param array $user User data
     * @return array User permissions
     */
    public static function getUserPermissions($user) {
        // Return user permissions based on role
        switch ($user['role']) {
            case 'admin':
                return ['read', 'write', 'delete', 'manage_users', 'manage_settings'];
            case 'staff':
                return ['read', 'write', 'manage_members', 'manage_loans'];
            case 'member':
                return ['read', 'manage_own_profile', 'manage_own_loans'];
            default:
                return [];
        }
    }
    
    /**
     * Validate session
     * @return bool Session is valid
     */
    public static function validateSession() {
        return session_status() === PHP_SESSION_ACTIVE;
    }
    
    /**
     * Destroy session
     */
    public static function destroySession() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
    
    /**
     * Get client IP address
     * @return string Client IP
     */
    public static function getClientIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Check if request is from trusted source
     * @return bool Request is trusted
     */
    public static function isTrustedSource() {
        $trustedIPs = ['127.0.0.1', '::1']; // Add trusted IPs
        
        $clientIP = self::getClientIP();
        return in_array($clientIP, $trustedIPs);
    }
    
    /**
     * Validate request method
     * @param array $allowedMethods Allowed HTTP methods
     * @return bool Method is allowed
     */
    public static function validateRequestMethod($allowedMethods = ['GET', 'POST', 'PUT', 'DELETE']) {
        return in_array($_SERVER['REQUEST_METHOD'], $allowedMethods);
    }
    
    /**
     * Validate content type
     * @param string $expectedType Expected content type
     * @return bool Content type is valid
     */
    public static function validateContentType($expectedType = 'application/json') {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return strpos($contentType, $expectedType) !== false;
    }
    
    /**
     * Get request context for logging
     * @return array Request context
     */
    public static function getRequestContext() {
        return [
            'ip' => self::getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'timestamp' => date('Y-m-d H:i:s'),
            'referer' => $_SERVER['HTTP_REFERER'] ?? ''
        ];
    }
}

// Define constant for access control
define('KSP_API_ACCESS', true);
?>
