<?php
/**
 * Comprehensive Error Handler for KSP Lam Gabe Jaya API
 * Provides centralized error handling and logging
 */

class ErrorHandler {
    
    private static $initialized = false;
    private static $errorLog = null;
    
    /**
     * Initialize error handler
     */
    public static function initialize() {
        if (self::$initialized) {
            return;
        }
        
        self::$initialized = true;
        
        // Set error reporting
        error_reporting(E_ALL);
        
        // Set custom error handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
        
        // Initialize error log
        self::$errorLog = __DIR__ . '/../logs/errors.log';
        self::ensureLogDirectory();
    }
    
    /**
     * Handle PHP errors
     */
    public static function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorType = self::getErrorType($severity);
        $errorData = [
            'type' => $errorType,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'timestamp' => date('Y-m-d H:i:s'),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'ip' => self::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ];
        
        self::logError($errorData);
        
        // Don't show errors in production
        if (getenv('APP_ENV') === 'production') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle uncaught exceptions
     */
    public static function handleException($exception) {
        $errorData = [
            'type' => 'EXCEPTION',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'timestamp' => date('Y-m-d H:i:s'),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'ip' => self::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'stack_trace' => $exception->getTraceAsString()
        ];
        
        self::logError($errorData);
        
        // Send appropriate response
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json');
        }
        
        echo json_encode([
            'success' => false,
            'message' => getenv('APP_ENV') === 'production' 
                ? 'Internal server error' 
                : $exception->getMessage(),
            'error_code' => 'INTERNAL_ERROR',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        exit();
    }
    
    /**
     * Handle fatal errors
     */
    public static function handleShutdown() {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $errorData = [
                'type' => 'FATAL_ERROR',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'timestamp' => date('Y-m-d H:i:s'),
                'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
                'method' => $_SERVER['REQUEST_METHOD'] ?? '',
                'ip' => self::getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ];
            
            self::logError($errorData);
            
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json');
            }
            
            echo json_encode([
                'success' => false,
                'message' => 'Fatal error occurred',
                'error_code' => 'FATAL_ERROR',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    /**
     * Log error to file
     */
    private static function logError($errorData) {
        $logEntry = json_encode($errorData) . "\n";
        
        // Write to error log
        file_put_contents(self::$errorLog, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Also write to daily log for rotation
        $dailyLog = str_replace('.log', '_' . date('Y-m-d') . '.log', self::$errorLog);
        file_put_contents($dailyLog, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Send to external monitoring if configured
        if (getenv('ERROR_TRACKING_ENABLED') === 'true') {
            self::sendToMonitoring($errorData);
        }
    }
    
    /**
     * Send error to external monitoring service
     */
    private static function sendToMonitoring($errorData) {
        $webhookUrl = getenv('ERROR_WEBHOOK_URL');
        
        if (!$webhookUrl) {
            return;
        }
        
        $payload = [
            'service' => 'ksp-lamgabejaya-api',
            'environment' => getenv('APP_ENV', 'development'),
            'error' => $errorData,
            'timestamp' => date('c')
        ];
        
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        curl_exec($ch);
        curl_close($ch);
    }
    
    /**
     * Get error type description
     */
    private static function getErrorType($severity) {
        switch ($severity) {
            case E_ERROR:
                return 'E_ERROR';
            case E_WARNING:
                return 'E_WARNING';
            case E_PARSE:
                return 'E_PARSE';
            case E_NOTICE:
                return 'E_NOTICE';
            case E_CORE_ERROR:
                return 'E_CORE_ERROR';
            case E_CORE_WARNING:
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR:
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING:
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR:
                return 'E_USER_ERROR';
            case E_USER_WARNING:
                return 'E_USER_WARNING';
            case E_USER_NOTICE:
                return 'E_USER_NOTICE';
            case E_STRICT:
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR:
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED:
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED:
                return 'E_USER_DEPRECATED';
            default:
                return 'UNKNOWN';
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
     * Ensure log directory exists
     */
    private static function ensureLogDirectory() {
        $logDir = dirname(self::$errorLog);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Protect log files
        $htaccess = $logDir . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all\n");
        }
    }
    
    /**
     * Get error statistics
     */
    public static function getErrorStats($days = 7) {
        $stats = [];
        $endDate = new DateTime();
        $startDate = new DateTime();
        $startDate->sub(new DateInterval("P{$days}D"));
        
        while ($startDate <= $endDate) {
            $date = $startDate->format('Y-m-d');
            $dailyLog = str_replace('.log', '_' . $date . '.log', self::$errorLog);
            
            if (file_exists($dailyLog)) {
                $content = file_get_contents($dailyLog);
                $lines = array_filter(explode("\n", $content));
                $stats[$date] = count($lines);
            } else {
                $stats[$date] = 0;
            }
            
            $startDate->add(new DateInterval('P1D'));
        }
        
        return $stats;
    }
    
    /**
     * Clean old log files
     */
    public static function cleanOldLogs($days = 30) {
        $logDir = dirname(self::$errorLog);
        $files = glob($logDir . '/*.log');
        $cutoff = time() - ($days * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
    
    /**
     * Handle API errors with proper response format
     */
    public static function handleApiError($message, $code = 400, $errorCode = 'API_ERROR', $details = []) {
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: application/json');
        }
        
        $response = [
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if (!empty($details)) {
            $response['details'] = $details;
        }
        
        // Log the error
        self::logError([
            'type' => 'API_ERROR',
            'message' => $message,
            'code' => $code,
            'error_code' => $errorCode,
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s'),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'ip' => self::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        echo json_encode($response);
        exit();
    }
    
    /**
     * Validate API response format
     */
    public static function validateApiResponse($data) {
        if (!is_array($data)) {
            return false;
        }
        
        $required = ['success', 'message'];
        foreach ($required as $field) {
            if (!array_key_exists($field, $data)) {
                return false;
            }
        }
        
        return true;
    }
}

// Initialize error handler
ErrorHandler::initialize();
?>
