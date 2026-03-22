<?php
/**
 * Security Middleware
 * Centralized security enforcement for all API requests
 */

// Prevent direct access
if (!defined('KSP_API_ACCESS')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access to this file is not allowed.');
}

// Include required files
require_once __DIR__ . '/AuthHelper.php';
require_once __DIR__ . '/SecurityHelper.php';

/**
 * Security Middleware Class
 */
class SecurityMiddleware {
    
    /**
     * Initialize security middleware
     */
    public static function init() {
        // Send security headers
        SecurityHelper::sendSecurityHeaders();
        
        // Handle CORS
        self::handleCORS();
        
        // Validate request method
        self::validateRequestMethod();
        
        // Check rate limiting
        self::checkRateLimit();
        
        // Validate request origin
        self::validateOrigin();
        
        // Check for suspicious user agent
        self::checkSuspiciousUserAgent();
        
        // Validate input data
        self::validateInput();
    }
    
    /**
     * Handle CORS headers
     */
    private static function handleCORS() {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowedOrigins = Config::CORS_ORIGINS;
        
        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }
        
        header("Access-Control-Allow-Methods: " . implode(', ', Config::CORS_METHODS));
        header("Access-Control-Allow-Headers: " . implode(', ', Config::CORS_HEADERS));
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 3600");
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
    }
    
    /**
     * Validate request method
     */
    private static function validateRequestMethod() {
        $allowedMethods = Config::CORS_METHODS;
        
        if (!in_array($_SERVER['REQUEST_METHOD'], $allowedMethods)) {
            header('HTTP/1.0 405 Method Not Allowed');
            exit('Method not allowed');
        }
    }
    
    /**
     * Check rate limiting
     */
    private static function checkRateLimit() {
        $clientIP = AuthHelper::getClientIP();
        $maxRequests = Config::API_RATE_LIMIT;
        
        if (!AuthHelper::checkRateLimit($clientIP, $maxRequests, 3600)) {
            header('HTTP/1.0 429 Too Many Requests');
            SecurityHelper::logSecurityEvent('rate_limit_exceeded', "Rate limit exceeded for IP: $clientIP");
            exit('Rate limit exceeded');
        }
    }
    
    /**
     * Validate request origin
     */
    private static function validateOrigin() {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        
        if (!empty($origin) && !SecurityHelper::validateOrigin($origin)) {
            header('HTTP/1.0 403 Forbidden');
            SecurityHelper::logSecurityEvent('invalid_origin', "Invalid origin: $origin");
            exit('Invalid origin');
        }
        
        if (!empty($referer) && !SecurityHelper::validateOrigin($referer)) {
            header('HTTP/1.0 403 Forbidden');
            SecurityHelper::logSecurityEvent('invalid_referer', "Invalid referer: $referer");
            exit('Invalid referer');
        }
    }
    
    /**
     * Check for suspicious user agent
     */
    private static function checkSuspiciousUserAgent() {
        if (SecurityHelper::isSuspiciousUserAgent()) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            SecurityHelper::logSecurityEvent('suspicious_user_agent', "Suspicious user agent detected: $userAgent");
            
            // You might want to block or additional verification here
            // For now, just log it
        }
    }
    
    /**
     * Validate input data
     */
    private static function validateInput() {
        $request = array_merge($_GET, $_POST, $_REQUEST);
        
        $validation = SecurityHelper::validateAPIRequest($request);
        
        if (!$validation['valid']) {
            header('HTTP/1.0 400 Bad Request');
            SecurityHelper::logSecurityEvent('input_validation_failed', 'Input validation failed', ['errors' => $validation['errors']]);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid input data',
                'errors' => $validation['errors']
            ]);
            exit;
        }
    }
    
    /**
     * Validate authentication token
     * @param string|null $role Required role
     * @return array User data
     */
    public static function requireAuth($role = null) {
        try {
            return AuthHelper::requireAuth($role);
        } catch (Exception $e) {
            header('HTTP/1.0 401 Unauthorized');
            SecurityHelper::logSecurityEvent('authentication_failed', $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
    
    /**
     * Validate CSRF token
     * @param string $token CSRF token
     * @return bool Token is valid
     */
    public static function validateCSRFToken($token) {
        if (!SecurityHelper::validateCSRFToken($token)) {
            header('HTTP/1.0 403 Forbidden');
            SecurityHelper::logSecurityEvent('csrf_invalid', 'Invalid CSRF token');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid CSRF token'
            ]);
            exit;
        }
        
        return true;
    }
    
    /**
     * Sanitize and validate JSON input
     * @param string $json JSON string
     * @return array Sanitized data
     */
    public static function getJSONInput() {
        $input = file_get_contents('php://input');
        
        if (empty($input)) {
            return [];
        }
        
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid JSON input',
                'error' => json_last_error_msg()
            ]);
            exit;
        }
        
        return SecurityHelper::sanitizeInput($data);
    }
    
    /**
     * Validate file upload
     * @param array $file File data
     * @param array $allowedTypes Allowed MIME types
     * @param int $maxSize Maximum file size
     * @return array Validation result
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = null) {
        $maxSize = $maxSize ?? Config::API_MAX_FILE_SIZE;
        
        $validation = SecurityHelper::validateFileUpload($file, $allowedTypes, $maxSize);
        
        if (!$validation['valid']) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid file upload',
                'errors' => $validation['errors']
            ]);
            exit;
        }
        
        return $validation;
    }
    
    /**
     * Log API request
     * @param string $endpoint API endpoint
     * @param array $context Request context
     */
    public static function logAPIRequest($endpoint, $context = []) {
        try {
            global $db;
            
            $db->insert('audit_logs', [
                'user_id' => $context['user_id'] ?? null,
                'action' => 'api_request',
                'table_name' => 'api',
                'description' => "API request to: $endpoint",
                'old_values' => json_encode($context['old_values'] ?? []),
                'new_values' => json_encode($context['new_values'] ?? []),
                'ip_address' => AuthHelper::getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("API request logging failed: " . $e->getMessage());
        }
    }
    
    /**
     * Check session timeout
     * @param array $user User data
     * @return bool Session is valid
     */
    public static function checkSessionTimeout($user) {
        $lastActivity = $user['last_activity'] ?? $user['created_at'] ?? date('Y-m-d H:i:s');
        $timeout = Config::SESSION_TIMEOUT;
        
        $timeDiff = time() - strtotime($lastActivity);
        
        if ($timeDiff > $timeout) {
            SecurityHelper::logSecurityEvent('session_timeout', 'Session timed out', ['user_id' => $user['id']]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Update user activity
     * @param int $userId User ID
     */
    public static function updateUserActivity($userId) {
        try {
            global $db;
            
            $db->update('users', [
                'last_activity' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$userId]);
        } catch (Exception $e) {
            error_log("Activity update failed: " . $e->getMessage());
        }
    }
    
    /**
     * Validate API version
     * @param string $version API version
     * @return bool Version is valid
     */
    public static function validateAPIVersion($version) {
        $supportedVersions = ['v1'];
        
        if (!in_array($version, $supportedVersions)) {
            header('HTTP/1.0 426 Upgrade Required');
            echo json_encode([
                'success' => false,
                'message' => 'Unsupported API version',
                'supported_versions' => $supportedVersions
            ]);
            exit;
        }
        
        return true;
    }
    
    /**
     * Check maintenance mode
     */
    public static function checkMaintenanceMode() {
        try {
            global $db;
            
            $maintenance = $db->fetchOne("SELECT value FROM system_settings WHERE `key` = 'maintenance_mode'");
            
            if ($maintenance && $maintenance['value'] === 'true') {
                header('HTTP/1.0 503 Service Unavailable');
                echo json_encode([
                    'success' => false,
                    'message' => 'System is under maintenance',
                    'retry_after' => 300
                ]);
                exit;
            }
        } catch (Exception $e) {
            // Continue if maintenance check fails
        }
    }
    
    /**
     * Validate request size
     */
    public static function validateRequestSize() {
        $contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
        $maxSize = Config::API_MAX_FILE_SIZE;
        
        if ($contentLength > $maxSize) {
            header('HTTP/1.0 413 Payload Too Large');
            echo json_encode([
                'success' => false,
                'message' => 'Request size exceeds maximum limit',
                'max_size' => $maxSize
            ]);
            exit;
        }
    }
    
    /**
     * Initialize complete security check
     * @param string|null $role Required role
     * @return array User data
     */
    public static function secure($role = null) {
        // Initialize basic security
        self::init();
        
        // Check maintenance mode
        self::checkMaintenanceMode();
        
        // Validate request size
        self::validateRequestSize();
        
        // Validate API version
        $apiVersion = $_SERVER['HTTP_X_API_VERSION'] ?? 'v1';
        self::validateAPIVersion($apiVersion);
        
        // Require authentication
        $user = self::requireAuth($role);
        
        // Check session timeout
        if (!self::checkSessionTimeout($user)) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode([
                'success' => false,
                'message' => 'Session expired'
            ]);
            exit;
        }
        
        // Update user activity
        self::updateUserActivity($user['id']);
        
        return $user;
    }
    
    /**
     * Send secure JSON response
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     */
    public static function sendJSONResponse($data, $statusCode = 200) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code($statusCode);
        
        // Sanitize output
        $sanitizedData = SecurityHelper::sanitize($data);
        
        echo json_encode($sanitizedData);
        exit;
    }
    
    /**
     * Send error response
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $errors Additional errors
     */
    public static function sendErrorResponse($message, $statusCode = 400, $errors = []) {
        self::sendJSONResponse([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s')
        ], $statusCode);
    }
    
    /**
     * Send success response
     * @param mixed $data Response data
     * @param string $message Success message
     */
    public static function sendSuccessResponse($data = null, $message = 'Success') {
        self::sendJSONResponse([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Handle API request with full security
     * @param callable $handler Request handler function
     * @param string|null $role Required role
     */
    public static function handleRequest($handler, $role = null) {
        try {
            // Secure the request
            $user = self::secure($role);
            
            // Call the handler
            $result = $handler($user);
            
            // Send success response
            if (is_array($result) && isset($result['success']) && !$result['success']) {
                self::sendErrorResponse($result['message'], 400, $result['errors'] ?? []);
            } else {
                self::sendSuccessResponse($result);
            }
            
        } catch (Exception $e) {
            SecurityHelper::logSecurityEvent('api_error', $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            self::sendErrorResponse('Internal server error', 500);
        }
    }
}

// Define constant for access control
require_once __DIR__ . '/config/error-config.php';

define('KSP_API_ACCESS', true);
?>
