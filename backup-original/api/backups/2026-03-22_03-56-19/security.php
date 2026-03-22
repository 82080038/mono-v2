<?php
/**
 * Security Enhancement System
 * Advanced security features for KSP Lam Gabe Jaya
 */

require_once 'cache.php';

class Security {
    private static $rateLimit = [];
    private static $csrfTokens = [];
    
    /**
     * Rate limiting
     */
    public static function rateLimit($identifier, $limit = 100, $window = 3600) {
        $key = 'rate_limit:' . $identifier;
        $current = Cache::get($key, ['count' => 0, 'reset_time' => time() + $window]);
        
        // Reset if window expired
        if (time() > $current['reset_time']) {
            $current = ['count' => 0, 'reset_time' => time() + $window];
        }
        
        // Check limit
        if ($current['count'] >= $limit) {
            return false;
        }
        
        // Increment counter
        $current['count']++;
        Cache::set($key, $current, $window);
        
        return true;
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken($sessionKey = 'csrf_token') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION[$sessionKey] = $token;
        self::$csrfTokens[$sessionKey] = $token;
        
        return $token;
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCsrfToken($token, $sessionKey = 'csrf_token') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return hash_equals($_SESSION[$sessionKey] ?? '', $token);
    }
    
    /**
     * Input sanitization
     */
    public static function sanitize($input, $type = 'string') {
        switch ($type) {
            case 'string':
                return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
                
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
                
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
                
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                
            case 'raw':
                return $input;
                
            default:
                return trim($input);
        }
    }
    
    /**
     * XSS protection
     */
    public static function xssClean($string) {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * SQL injection protection (using prepared statements is better)
     */
    public static function escapeSql($string) {
        return addslashes($string);
    }
    
    /**
     * Password hashing
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 1
        ]);
    }
    
    /**
     * Password verification
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate secure random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Validate password strength
     */
    public static function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return $errors;
    }
    
    /**
     * Check for suspicious activity
     */
    public static function detectSuspiciousActivity($action, $context = []) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Log suspicious activities
        $suspiciousPatterns = [
            'multiple_failed_logins',
            'rapid_requests',
            'invalid_parameters',
            'sql_injection_attempts',
            'xss_attempts'
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (self::matchesPattern($action, $pattern, $context)) {
                SystemLogger::security("Suspicious activity detected: $pattern", [
                    'ip' => $ip,
                    'user_agent' => $userAgent,
                    'action' => $action,
                    'context' => $context
                ]);
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if activity matches suspicious pattern
     */
    private static function matchesPattern($action, $pattern, $context) {
        switch ($pattern) {
            case 'multiple_failed_logins':
                return $action === 'login_failed' && self::getFailedLoginCount() > 5;
                
            case 'rapid_requests':
                $key = 'requests:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
                return !self::rateLimit($key, 100, 60); // 100 requests per minute
                
            case 'invalid_parameters':
                return isset($context['invalid_params']) && count($context['invalid_params']) > 3;
                
            case 'sql_injection_attempts':
                $sqlPatterns = ['union', 'select', 'drop', 'insert', 'delete', 'update', '--', '/*', '*/'];
                $contextStr = json_encode($context);
                foreach ($sqlPatterns as $pattern) {
                    if (stripos($contextStr, $pattern) !== false) {
                        return true;
                    }
                }
                return false;
                
            case 'xss_attempts':
                $xssPatterns = ['<script', 'javascript:', 'onload=', 'onerror=', 'onclick='];
                $contextStr = json_encode($context);
                foreach ($xssPatterns as $pattern) {
                    if (stripos($contextStr, $pattern) !== false) {
                        return true;
                    }
                }
                return false;
                
            default:
                return false;
        }
    }
    
    /**
     * Get failed login count for IP
     */
    private static function getFailedLoginCount() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'failed_logins:' . $ip;
        return Cache::get($key, 0);
    }
    
    /**
     * Increment failed login count
     */
    public static function incrementFailedLogin() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'failed_logins:' . $ip;
        Cache::increment($key);
        Cache::set($key, Cache::get($key, 0), 3600); // Reset after 1 hour
    }
    
    /**
     * Reset failed login count
     */
    public static function resetFailedLogin() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'failed_logins:' . $ip;
        Cache::delete($key);
    }
    
    /**
     * Check if IP is blocked
     */
    public static function isIpBlocked($ip = null) {
        $ip = $ip ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'blocked_ip:' . $ip;
        return Cache::has($key);
    }
    
    /**
     * Block IP for specified duration
     */
    public static function blockIp($ip = null, $duration = 3600) {
        $ip = $ip ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'blocked_ip:' . $ip;
        Cache::set($key, true, $duration);
        
        SystemLogger::security("IP blocked: $ip", ['duration' => $duration]);
    }
    
    /**
     * Unblock IP
     */
    public static function unblockIp($ip = null) {
        $ip = $ip ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'blocked_ip:' . $ip;
        Cache::delete($key);
        
        SystemLogger::security("IP unblocked: $ip");
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = 10485760) {
        $errors = [];
        
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'Invalid file upload';
            return $errors;
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $errors[] = 'File size exceeds maximum allowed size';
        }
        
        // Check file type
        if (!empty($allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $errors[] = 'File type not allowed';
            }
        }
        
        // Check file extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'File extension not allowed';
        }
        
        return $errors;
    }
    
    /**
     * Secure file upload
     */
    public static function secureFileUpload($file, $uploadDir, $allowedTypes = [], $maxSize = 10485760) {
        $errors = self::validateFileUpload($file, $allowedTypes, $maxSize);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Generate secure filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;
        
        // Move file to secure location
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'errors' => ['Failed to move uploaded file']];
        }
        
        // Set secure permissions
        chmod($filepath, 0644);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'original_name' => $file['name']
        ];
    }
}

/**
 * Security Middleware
 */
class SecurityMiddleware {
    /**
     * Apply security headers
     */
    public static function applyHeaders() {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';");
        
        // Strict Transport Security (HTTPS only)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
    
    /**
     * Check IP blocking
     */
    public static function checkIpBlocking() {
        if (Security::isIpBlocked()) {
            ApiResponse::forbidden('Your IP address has been blocked');
        }
    }
    
    /**
     * Apply rate limiting
     */
    public static function applyRateLimit($limit = 100, $window = 3600) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        if (!Security::rateLimit($ip, $limit, $window)) {
            ApiResponse::error('Rate limit exceeded', 429);
        }
    }
    
    /**
     * Validate request method
     */
    public static function validateMethod($allowedMethods) {
        $method = $_SERVER['REQUEST_METHOD'];
        
        if (!in_array($method, $allowedMethods)) {
            ApiResponse::error('Method not allowed', 405);
        }
    }
    
    /**
     * Apply all security checks
     */
    public static function apply($options = []) {
        self::applyHeaders();
        self::checkIpBlocking();
        
        if (isset($options['rate_limit'])) {
            self::applyRateLimit($options['rate_limit']['limit'] ?? 100, $options['rate_limit']['window'] ?? 3600);
        }
        
        if (isset($options['allowed_methods'])) {
            self::validateMethod($options['allowed_methods']);
        }
    }
}
?>
