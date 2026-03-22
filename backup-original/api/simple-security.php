<?php
/**
 * Simple Security Implementation
 * Stable version without complex dependencies
 */

class SimpleSecurity {
    /**
     * Basic input sanitization
     */
    public static function sanitize($input, $type = 'string') {
        switch ($type) {
            case 'string':
                return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            default:
                return trim($input);
        }
    }
    
    /**
     * Basic XSS protection
     */
    public static function xssClean($string) {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Password hashing
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Password verification
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate secure token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Basic rate limiting (file-based)
     */
    public static function rateLimit($identifier, $limit = 100, $window = 3600) {
        $file = __DIR__ . '/../cache/rate_limit_' . md5($identifier) . '.txt';
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $data = [];
        if (file_exists($file)) {
            $data = unserialize(file_get_contents($file));
        }
        
        $now = time();
        $window_start = $now - $window;
        
        // Clean old entries
        $data = array_filter($data, function($timestamp) use ($window_start) {
            return $timestamp > $window_start;
        });
        
        // Check limit
        if (count($data) >= $limit) {
            return false;
        }
        
        // Add current request
        $data[] = $now;
        file_put_contents($file, serialize($data));
        
        return true;
    }
    
    /**
     * Basic CSRF protection
     */
    public static function generateCsrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        
        return $token;
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCsrfToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
    
    /**
     * Log security events
     */
    public static function logSecurity($event, $context = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'context' => $context,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $logFile = __DIR__ . '/../logs/security.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Simple Security Middleware
 */
class SimpleSecurityMiddleware {
    public static function apply($options = []) {
        // Apply rate limiting if enabled
        if (isset($options['rate_limit'])) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $limit = $options['rate_limit']['limit'] ?? 100;
            $window = $options['rate_limit']['window'] ?? 3600;
            
            if (!SimpleSecurity::rateLimit($ip, $limit, $window)) {
                http_response_code(429);
                echo json_encode([
                    'success' => false,
                    'error' => 'Rate limit exceeded',
                    'code' => 429
                ]);
                exit();
            }
        }
        
        // Apply CSRF protection if enabled
        if (isset($options['csrf']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!SimpleSecurity::validateCsrfToken($token)) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid CSRF token',
                    'code' => 403
                ]);
                exit();
            }
        }
        
        // Log security events
        if (isset($options['log_events']) && $options['log_events']) {
            SimpleSecurity::logSecurity('api_request', [
                'method' => $_SERVER['REQUEST_METHOD'],
                'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
            ]);
        }
    }
}
?>
