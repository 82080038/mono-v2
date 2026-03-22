<?php
/**
 * System Error Logger
 * Centralized logging system for KSP Lam Gabe Jaya
 */

class SystemLogger {
    private static $logFile = null;
    private static $logLevel = 'INFO';
    
    public static function init() {
        self::$logFile = __DIR__ . '/../logs/system.log';
        self::$logLevel = $_ENV['LOG_LEVEL'] ?? 'INFO';
        
        // Create logs directory if not exists
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Log message with context
     */
    public static function log($level, $message, $context = []) {
        if (!self::$logFile) {
            self::init();
        }
        
        $levels = ['DEBUG' => 0, 'INFO' => 1, 'WARNING' => 2, 'ERROR' => 3, 'CRITICAL' => 4];
        
        if ($levels[$level] < $levels[self::$logLevel]) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'CLI';
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'CLI';
        
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'request_uri' => $requestUri
        ];
        
        $logLine = json_encode($logEntry) . PHP_EOL;
        
        file_put_contents(self::$logFile, $logLine, FILE_APPEND | LOCK_EX);
        
        // Also log to PHP error log for critical errors
        if ($level === 'ERROR' || $level === 'CRITICAL') {
            error_log("[$level] $message - " . json_encode($context));
        }
    }
    
    public static function debug($message, $context = []) {
        self::log('DEBUG', $message, $context);
    }
    
    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }
    
    public static function warning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }
    
    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }
    
    public static function critical($message, $context = []) {
        self::log('CRITICAL', $message, $context);
    }
    
    /**
     * Log database operations
     */
    public static function database($operation, $table, $context = []) {
        self::info("Database operation: $operation on $table", $context);
    }
    
    /**
     * Log user activities
     */
    public static function userActivity($userId, $action, $context = []) {
        self::info("User activity: $action", array_merge(['user_id' => $userId], $context));
    }
    
    /**
     * Log API requests
     */
    public static function apiRequest($endpoint, $method, $responseCode, $context = []) {
        $level = $responseCode >= 400 ? 'WARNING' : 'INFO';
        self::log($level, "API request: $method $endpoint", array_merge([
            'response_code' => $responseCode
        ], $context));
    }
    
    /**
     * Log security events
     */
    public static function security($event, $context = []) {
        self::warning("Security event: $event", $context);
    }
    
    /**
     * Get recent log entries
     */
    public static function getRecentLogs($limit = 100, $level = null) {
        if (!self::$logFile) {
            self::init();
        }
        
        if (!file_exists(self::$logFile)) {
            return [];
        }
        
        $logs = [];
        $lines = array_reverse(file(self::$logFile));
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $logEntry = json_decode(trim($line), true);
            if (!$logEntry) continue;
            
            if ($level && $logEntry['level'] !== $level) continue;
            
            $logs[] = $logEntry;
            
            if (count($logs) >= $limit) break;
        }
        
        return $logs;
    }
    
    /**
     * Clear old logs
     */
    public static function clearOldLogs($days = 30) {
        if (!self::$logFile) {
            self::init();
        }
        
        if (!file_exists(self::$logFile)) {
            return;
        }
        
        $cutoffDate = date('Y-m-d', strtotime("-$days days"));
        $tempFile = self::$logFile . '.tmp';
        
        $input = fopen(self::$logFile, 'r');
        $output = fopen($tempFile, 'w');
        
        while (($line = fgets($input)) !== false) {
            $logEntry = json_decode(trim($line), true);
            if ($logEntry && $logEntry['timestamp'] >= $cutoffDate) {
                fwrite($output, $line);
            }
        }
        
        fclose($input);
        fclose($output);
        
        rename($tempFile, self::$logFile);
    }
}

// Auto-initialize
SystemLogger::init();
?>
