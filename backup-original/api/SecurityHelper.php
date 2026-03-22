<?php
/**
 * Security Helper Class
 * Centralized security functions for XSS protection and input sanitization
 */

// Prevent direct access
if (!defined('KSP_API_ACCESS')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access to this file is not allowed.');
}

/**
 * Security Helper Class
 */
class SecurityHelper {
    
    /**
     * Sanitize output for XSS protection
     * @param mixed $data Data to sanitize
     * @param string $context Context (html, js, css, url)
     * @return mixed Sanitized data
     */
    public static function sanitize($data, $context = 'html') {
        if (is_array($data)) {
            return array_map(function($item) use ($context) {
                return self::sanitize($item, $context);
            }, $data);
        } elseif (is_string($data)) {
            switch ($context) {
                case 'html':
                    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                case 'js':
                    return json_encode($data);
                case 'css':
                    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
                case 'url':
                    return urlencode($data);
                default:
                    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            }
        }
        
        return $data;
    }
    
    /**
     * Sanitize input from user
     * @param mixed $data Input data
     * @return mixed Sanitized data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        } elseif (is_string($data)) {
            return trim(htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8'));
        }
        
        return $data;
    }
    
    /**
     * Validate email format
     * @param string $email Email to validate
     * @return bool Email is valid
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (Indonesian format)
     * @param string $phone Phone number
     * @return bool Phone number is valid
     */
    public static function validatePhone($phone) {
        // Remove all non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Check Indonesian phone number format
        return preg_match('/^(08|62)[0-9]{8,13}$/', $cleaned);
    }
    
    /**
     * Validate Indonesian ID number (NIK)
     * @param string $nik ID number
     * @return bool ID number is valid
     */
    public static function validateNIK($nik) {
        // Remove spaces and hyphens
        $cleaned = preg_replace('/[\s-]/', '', $nik);
        
        // Check if it's 16 digits
        return preg_match('/^[0-9]{16}$/', $cleaned);
    }
    
    /**
     * Validate numeric input
     * @param mixed $value Value to validate
     * @param float $min Minimum value
     * @param float $max Maximum value
     * @return bool Value is valid
     */
    public static function validateNumeric($value, $min = null, $max = null) {
        if (!is_numeric($value)) {
            return false;
        }
        
        $numValue = (float) $value;
        
        if ($min !== null && $numValue < $min) {
            return false;
        }
        
        if ($max !== null && $numValue > $max) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate date format
     * @param string $date Date string
     * @param string $format Expected format (Y-m-d, d/m/Y, etc.)
     * @return bool Date is valid
     */
    public static function validateDate($date, $format = 'Y-m-d') {
        $dateObj = DateTime::createFromFormat($format, $date);
        return $dateObj && $dateObj->format($format) === $date;
    }
    
    /**
     * Validate password strength
     * @param string $password Password to validate
     * @param int $minLength Minimum length
     * @return array Validation result
     */
    public static function validatePassword($password, $minLength = 8) {
        $errors = [];
        
        if (strlen($password) < $minLength) {
            $errors[] = "Password must be at least $minLength characters long";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Generate secure random string
     * @param int $length String length
     * @return string Random string
     */
    public static function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Generate secure token
     * @param int $length Token length
     * @return string Secure token
     */
    public static function generateToken($length = 64) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Hash password securely
     * @param string $password Plain password
     * @return string Hashed password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 1
        ]);
    }
    
    /**
     * Verify password
     * @param string $password Plain password
     * @param string $hash Hashed password
     * @return bool Password matches
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Check for SQL injection patterns
     * @param string $input Input string
     * @return bool Contains SQL injection patterns
     */
    public static function containsSQLInjection($input) {
        $patterns = [
            '/(\s|^)(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)(\s|$)/i',
            '/(\s|^)(OR|AND)\s+\d+\s*=\s*\d+/i',
            '/(\s|^)(OR|AND)\s+\'[^\']*\'\s*=\s*\'[^\']*\'/i',
            '/(\s|^)(--|#|\/\*|\*\/)/i',
            '/(\s|^)(xp_|sp_)/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for XSS patterns
     * @param string $input Input string
     * @return bool Contains XSS patterns
     */
    public static function containsXSS($input) {
        $patterns = [
            '/<script[^>]*>.*?<\/script>/si',
            '/<iframe[^>]*>.*?<\/iframe>/si',
            '/<object[^>]*>.*?<\/object>/si',
            '/<embed[^>]*>.*?<\/embed>/si',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i',
            '/onmouseover\s*=/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Validate file upload
     * @param array $file File data from $_FILES
     * @param array $allowedTypes Allowed MIME types
     * @param int $maxSize Maximum file size in bytes
     * @return array Validation result
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) {
        $errors = [];
        
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $errors[] = "Invalid file upload";
        }
        
        if ($file['size'] > $maxSize) {
            $errors[] = "File size exceeds maximum limit";
        }
        
        if (!empty($allowedTypes) && !in_array($file['type'], $allowedTypes)) {
            $errors[] = "File type not allowed";
        }
        
        // Check file extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = "File extension not allowed";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Sanitize filename
     * @param string $filename Original filename
     * @return string Sanitized filename
     */
    public static function sanitizeFilename($filename) {
        // Remove special characters except dots, hyphens, and underscores
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Remove consecutive dots
        $filename = preg_replace('/\.+/', '.', $filename);
        
        // Ensure filename is not empty
        if (empty($filename)) {
            $filename = 'file_' . time();
        }
        
        return $filename;
    }
    
    /**
     * Validate URL
     * @param string $url URL to validate
     * @return bool URL is valid
     */
    public static function validateURL($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Validate IP address
     * @param string $ip IP address
     * @return bool IP address is valid
     */
    public static function validateIP($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }
    
    /**
     * Check if request is from AJAX
     * @return bool Is AJAX request
     */
    public static function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Get secure headers
     * @return array Security headers
     */
    public static function getSecurityHeaders() {
        return [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'; connect-src 'self'",
            'Referrer-Policy' => 'strict-origin-when-cross-origin'
        ];
    }
    
    /**
     * Send security headers
     */
    public static function sendSecurityHeaders() {
        $headers = self::getSecurityHeaders();
        
        foreach ($headers as $header => $value) {
            header("$header: $value");
        }
    }
    
    /**
     * Log security event
     * @param string $event Event type
     * @param string $description Event description
     * @param array $context Additional context
     */
    public static function logSecurityEvent($event, $description, $context = []) {
        global $db;
        
        try {
            $db->insert('audit_logs', [
                'user_id' => $context['user_id'] ?? null,
                'action' => $event,
                'table_name' => 'security',
                'description' => $description,
                'old_values' => json_encode($context['old_values'] ?? []),
                'new_values' => json_encode($context['new_values'] ?? []),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Security logging failed: " . $e->getMessage());
        }
    }
    
    /**
     * Check rate limiting for security
     * @param string $identifier IP or user ID
     * @param int $maxAttempts Maximum attempts
     * @param int $timeWindow Time window in seconds
     * @return bool Rate limit not exceeded
     */
    public static function checkSecurityRateLimit($identifier, $maxAttempts = 10, $timeWindow = 300) {
        global $db;
        
        try {
            $attempts = $db->fetchOne(
                "SELECT COUNT(*) as count FROM audit_logs 
                 WHERE ip_address = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? SECOND) 
                 AND action IN ('login_failed', 'security_violation')",
                [$identifier, $timeWindow]
            )['count'];
            
            return $attempts < $maxAttempts;
        } catch (Exception $e) {
            return true;
        }
    }
    
    /**
     * Validate API request
     * @param array $request Request data
     * @return array Validation result
     */
    public static function validateAPIRequest($request) {
        $errors = [];
        
        // Check for SQL injection
        foreach ($request as $key => $value) {
            if (is_string($value) && self::containsSQLInjection($value)) {
                $errors[] = "Potential SQL injection detected in field: $key";
            }
            
            if (is_string($value) && self::containsXSS($value)) {
                $errors[] = "Potential XSS detected in field: $key";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Encrypt sensitive data
     * @param string $data Data to encrypt
     * @return string Encrypted data
     */
    public static function encrypt($data) {
        $key = hash('sha256', 'your-encryption-key');
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     * @param string $encryptedData Encrypted data
     * @return string Decrypted data
     */
    public static function decrypt($encryptedData) {
        $key = hash('sha256', 'your-encryption-key');
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
    
    /**
     * Generate CSRF token
     * @return string CSRF token
     */
    public static function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        
        return $token;
    }
    
    /**
     * Validate CSRF token
     * @param string $token Token to validate
     * @return bool Token is valid
     */
    public static function validateCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Clean expired sessions
     */
    public static function cleanExpiredSessions() {
        global $db;
        
        try {
            $db->query("DELETE FROM sessions WHERE expires < NOW()");
        } catch (Exception $e) {
            error_log("Session cleanup failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get client device information
     * @return array Device information
     */
    public static function getDeviceInfo() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $device = [
            'type' => 'unknown',
            'os' => 'unknown',
            'browser' => 'unknown'
        ];
        
        // Detect mobile
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/', $userAgent)) {
            $device['type'] = 'mobile';
        } elseif (preg_match('/Tablet/', $userAgent)) {
            $device['type'] = 'tablet';
        } else {
            $device['type'] = 'desktop';
        }
        
        // Detect OS
        if (preg_match('/Windows/', $userAgent)) {
            $device['os'] = 'Windows';
        } elseif (preg_match('/Mac/', $userAgent)) {
            $device['os'] = 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            $device['os'] = 'Linux';
        } elseif (preg_match('/Android/', $userAgent)) {
            $device['os'] = 'Android';
        } elseif (preg_match('/iOS/', $userAgent)) {
            $device['os'] = 'iOS';
        }
        
        // Detect browser
        if (preg_match('/Chrome/', $userAgent)) {
            $device['browser'] = 'Chrome';
        } elseif (preg_match('/Firefox/', $userAgent)) {
            $device['browser'] = 'Firefox';
        } elseif (preg_match('/Safari/', $userAgent)) {
            $device['browser'] = 'Safari';
        } elseif (preg_match('/Edge/', $userAgent)) {
            $device['browser'] = 'Edge';
        }
        
        return $device;
    }
    
    /**
     * Check if user agent is suspicious
     * @return bool User agent is suspicious
     */
    public static function isSuspiciousUserAgent() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $suspiciousPatterns = [
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scraper/i',
            '/curl/i',
            '/wget/i',
            '/python/i',
            '/java/i',
            '/perl/i',
            '/php/i'
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Validate request origin
     * @param string $origin Origin to validate
     * @return bool Origin is valid
     */
    public static function validateOrigin($origin) {
        $allowedOrigins = [
            'http://localhost',
            'https://localhost',
            'http://127.0.0.1',
            'https://127.0.0.1'
        ];
        
        foreach ($allowedOrigins as $allowedOrigin) {
            if (strpos($origin, $allowedOrigin) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get security score for user
     * @param int $userId User ID
     * @return array Security score
     */
    public static function getSecurityScore($userId) {
        global $db;
        
        try {
            $score = 100;
            
            // Check failed login attempts
            $failedLogins = $db->fetchOne(
                "SELECT COUNT(*) as count FROM audit_logs 
                 WHERE user_id = ? AND action = 'login_failed' 
                 AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
                [$userId]
            )['count'];
            
            $score -= min($failedLogins * 5, 50);
            
            // Check security violations
            $violations = $db->fetchOne(
                "SELECT COUNT(*) as count FROM audit_logs 
                 WHERE user_id = ? AND action = 'security_violation' 
                 AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
                [$userId]
            )['count'];
            
            $score -= min($violations * 10, 50);
            
            return [
                'score' => max(0, $score),
                'level' => $score >= 80 ? 'High' : ($score >= 60 ? 'Medium' : 'Low'),
                'failed_logins' => $failedLogins,
                'violations' => $violations
            ];
        } catch (Exception $e) {
            return [
                'score' => 50,
                'level' => 'Medium',
                'failed_logins' => 0,
                'violations' => 0
            ];
        }
    }
}

// Define constant for access control
define('KSP_API_ACCESS', true);
?>
