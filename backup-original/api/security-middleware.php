<?php
/**
 * Security Middleware for KSP Lam Gabe Jaya API
 * Provides comprehensive security protection for all API endpoints
 */

class SecurityMiddleware {
    
    /**
     * Apply security measures to API requests
     */
    public static function apply($options = []) {
        $defaultOptions = [
            'rate_limit' => ['limit' => 1000, 'window' => 3600],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'require_auth' => false,
            'csrf_protection' => true,
            'input_validation' => true,
            'security_headers' => true
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // Apply security headers
        if ($options['security_headers']) {
            self::applySecurityHeaders();
        }
        
        // Validate request method
        self::validateRequestMethod($options['allowed_methods']);
        
        // Apply rate limiting
        self::applyRateLimit($options['rate_limit']);
        
        // CSRF protection
        if ($options['csrf_protection'] && in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'])) {
            self::validateCSRFToken();
        }
        
        // Authentication requirement
        if ($options['require_auth']) {
            self::validateAuthentication();
        }
        
        // Input validation
        if ($options['input_validation']) {
            self::validateInputs();
        }
    }
    
    /**
     * Apply security headers
     */
    private static function applySecurityHeaders() {
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net");
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
    }
    
    /**
     * Validate request method
     */
    private static function validateRequestMethod($allowedMethods) {
        $method = $_SERVER['REQUEST_METHOD'];
        
        if (!in_array($method, $allowedMethods)) {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed',
                'error_code' => 'METHOD_NOT_ALLOWED'
            ]);
            exit();
        }
    }
    
    /**
     * Apply rate limiting
     */
    private static function applyRateLimit($config) {
        $clientIp = self::getClientIp();
        $key = 'rate_limit_' . md5($clientIp);
        $current = self::getCache($key) ?: 0;
        
        if ($current >= $config['limit']) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'message' => 'Too many requests',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
                'retry_after' => $config['window']
            ]);
            exit();
        }
        
        self::setCache($key, $current + 1, $config['window']);
    }
    
    /**
     * Validate CSRF token
     */
    private static function validateCSRFToken() {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        
        if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid CSRF token',
                'error_code' => 'CSRF_INVALID'
            ]);
            exit();
        }
    }
    
    /**
     * Validate authentication
     */
    private static function validateAuthentication() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $authHeader);
        
        if (!$token) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required',
                'error_code' => 'AUTH_REQUIRED'
            ]);
            exit();
        }
        
        // Validate JWT token
        if (!self::validateJWT($token)) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid or expired token',
                'error_code' => 'TOKEN_INVALID'
            ]);
            exit();
        }
    }
    
    /**
     * Validate JWT token
     */
    private static function validateJWT($token) {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return false;
            }
            
            $header = json_decode(base64_decode($parts[0]), true);
            $payload = json_decode(base64_decode($parts[1]), true);
            $signature = $parts[2];
            
            // Check expiration
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return false;
            }
            
            // Verify signature (simplified - in production use proper JWT library)
            $expectedSignature = hash_hmac('sha256', $parts[0] . '.' . $parts[1], getenv('JWT_SECRET'), true);
            $expectedSignature = base64_encode($expectedSignature);
            
            return hash_equals($expectedSignature, $signature);
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Validate input data
     */
    private static function validateInputs() {
        // Check for common attack patterns
        $patterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
            '/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/mi',
            '/<embed\b[^<]*(?:(?!<\/embed>)<[^<]*)*<\/embed>/mi'
        ];
        
        foreach ($_REQUEST as $key => $value) {
            if (is_string($value)) {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'message' => 'Invalid input detected',
                            'error_code' => 'INVALID_INPUT',
                            'field' => $key
                        ]);
                        exit();
                    }
                }
            }
        }
        
        // Check for SQL injection patterns
        $sqlPatterns = [
            '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION)\b)/i',
            '/(\b(OR|AND)\s+\d+\s*=\s*\d+)/i',
            '/(\'\s*;\s*\w+)/i',
            '/(\'\s*OR\s*\'.*\'.*\')/i'
        ];
        
        foreach ($_REQUEST as $key => $value) {
            if (is_string($value)) {
                foreach ($sqlPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        // Log potential SQL injection attempt
                        self::logSecurityEvent('SQL_INJECTION_ATTEMPT', [
                            'ip' => self::getClientIp(),
                            'field' => $key,
                            'value' => substr($value, 0, 100)
                        ]);
                        
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'message' => 'Invalid input detected',
                            'error_code' => 'SQL_INJECTION_ATTEMPT'
                        ]);
                        exit();
                    }
                }
            }
        }
    }
    
    /**
     * Get client IP address
     */
    private static function getClientIp() {
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
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Simple cache implementation
     */
    private static function getCache($key) {
        $file = sys_get_temp_dir() . '/ksp_cache_' . md5($key);
        if (!file_exists($file)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($file));
        if ($data['expires'] < time()) {
            unlink($file);
            return null;
        }
        
        return $data['value'];
    }
    
    private static function setCache($key, $value, $ttl) {
        $file = sys_get_temp_dir() . '/ksp_cache_' . md5($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        file_put_contents($file, serialize($data));
    }
    
    /**
     * Log security events
     */
    private static function logSecurityEvent($event, $data) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $data['ip'] ?? self::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'data' => $data
        ];
        
        $logFile = __DIR__ . '/../logs/security.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Generate CSRF token
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
     * Get current security status
     */
    public static function getStatus() {
        return [
            'security_headers' => headers_sent() ? 'Applied' : 'Pending',
            'rate_limiting' => 'Active',
            'csrf_protection' => 'Active',
            'input_validation' => 'Active',
            'auth_required' => 'Configurable',
            'client_ip' => self::getClientIp(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

// Auto-start session for CSRF protection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
