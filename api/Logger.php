<?php
/**
 * Comprehensive Error Logging System
 * KSP Lam Gabe Jaya v2.0
 */

class Logger {
    
    private static $logDir = __DIR__ . '/../logs';
    private static $logFile = 'application.log';
    private static $errorFile = 'errors.log';
    private static $securityFile = 'security.log';
    
    /**
     * Initialize logging system
     */
    public static function initialize() {
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
    }
    
    /**
     * Log general application events
     */
    public static function log($message, $level = 'INFO', $context = []) {
        self::initialize();
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $logEntry = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
        
        file_put_contents(self::$logDir . '/' . self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log errors
     */
    public static function error($message, $context = []) {
        self::log($message, 'ERROR', $context);
        
        // Also log to separate error file
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $errorEntry = "[$timestamp] [ERROR] $message$contextStr" . PHP_EOL;
        
        file_put_contents(self::$logDir . '/' . self::$errorFile, $errorEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log security events
     */
    public static function security($message, $context = []) {
        self::log($message, 'SECURITY', $context);
        
        // Also log to separate security file
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $securityEntry = "[$timestamp] [SECURITY] $message$contextStr" . PHP_EOL;
        
        file_put_contents(self::$logDir . '/' . self::$securityFile, $securityEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log API requests
     */
    public static function api($endpoint, $method, $params = [], $response = [], $status = 200) {
        $context = [
            'endpoint' => $endpoint,
            'method' => $method,
            'params' => $params,
            'response_status' => $status,
            'response_size' => strlen(json_encode($response)),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $level = $status >= 400 ? 'ERROR' : 'INFO';
        self::log("API Request: $method $endpoint", $level, $context);
    }
    
    /**
     * Log database operations
     */
    public static function database($operation, $table, $query = '', $params = [], $executionTime = 0) {
        $context = [
            'operation' => $operation,
            'table' => $table,
            'query' => $query,
            'params' => $params,
            'execution_time_ms' => $executionTime * 1000
        ];
        
        self::log("Database: $operation on $table", 'DEBUG', $context);
    }
    
    /**
     * Log authentication events
     */
    public static function auth($event, $userId = null, $details = []) {
        $context = array_merge([
            'event' => $event,
            'user_id' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ], $details);
        
        $level = in_array($event, ['login_failed', 'logout', 'token_invalid']) ? 'SECURITY' : 'INFO';
        self::log("Auth: $event", $level, $context);
    }
    
    /**
     * Log performance metrics
     */
    public static function performance($metric, $value, $unit = 'ms', $context = []) {
        $context = array_merge([
            'metric' => $metric,
            'value' => $value,
            'unit' => $unit,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ], $context);
        
        self::log("Performance: $metric = $value $unit", 'DEBUG', $context);
    }
    
    /**
     * Get recent logs
     */
    public static function getRecentLogs($type = 'application', $lines = 100) {
        self::initialize();
        
        $file = self::$logDir . '/' . self::$logFile;
        if ($type === 'errors') {
            $file = self::$logDir . '/' . self::$errorFile;
        } elseif ($type === 'security') {
            $file = self::$logDir . '/' . self::$securityFile;
        }
        
        if (!file_exists($file)) {
            return [];
        }
        
        $logs = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_slice(array_reverse($logs), 0, $lines);
    }
    
    /**
     * Clear old logs
     */
    public static function clearOldLogs($days = 30) {
        self::initialize();
        
        $files = [
            self::$logDir . '/' . self::$logFile,
            self::$logDir . '/' . self::$errorFile,
            self::$logDir . '/' . self::$securityFile
        ];
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $lines = explode(PHP_EOL, $content);
                
                $filteredLines = array_filter($lines, function($line) use ($days) {
                    if (empty($line)) return false;
                    
                    // Extract timestamp from log line
                    preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches);
                    if (isset($matches[1])) {
                        $logDate = new DateTime($matches[1]);
                        $cutoffDate = (new DateTime())->sub(new DateInterval("P{$days}D"));
                        return $logDate >= $cutoffDate;
                    }
                    
                    return true; // Keep lines without timestamp
                });
                
                file_put_contents($file, implode(PHP_EOL, $filteredLines));
            }
        }
    }
    
    /**
     * Log rotation
     */
    public static function rotateLogs() {
        self::initialize();
        
        $files = [
            self::$logFile => 'application',
            self::$errorFile => 'errors',
            self::$securityFile => 'security'
        ];
        
        foreach ($files as $file => $type) {
            $filePath = self::$logDir . '/' . $file;
            
            if (file_exists($filePath) && filesize($filePath) > 10 * 1024 * 1024) { // 10MB
                $timestamp = date('Y-m-d_H-i-s');
                $backupFile = self::$logDir . "/{$type}_{$timestamp}.log";
                rename($filePath, $backupFile);
            }
        }
    }
    
    /**
     * Get log statistics
     */
    public static function getStatistics() {
        self::initialize();
        
        $stats = [
            'application_log_size' => 0,
            'error_log_size' => 0,
            'security_log_size' => 0,
            'total_entries' => 0,
            'error_count' => 0,
            'security_count' => 0
        ];
        
        $files = [
            'application' => self::$logDir . '/' . self::$logFile,
            'errors' => self::$logDir . '/' . self::$errorFile,
            'security' => self::$logDir . '/' . self::$securityFile
        ];
        
        foreach ($files as $type => $file) {
            if (file_exists($file)) {
                $size = filesize($file);
                $stats["{$type}_log_size"] = $size;
                
                $content = file_get_contents($file);
                $lines = explode(PHP_EOL, $content);
                $stats['total_entries'] += count(array_filter($lines));
                
                if ($type === 'errors') {
                    $stats['error_count'] = count(array_filter($lines, function($line) {
                        return strpos($line, '[ERROR]') !== false;
                    }));
                } elseif ($type === 'security') {
                    $stats['security_count'] = count(array_filter($lines, function($line) {
                        return strpos($line, '[SECURITY]') !== false;
                    }));
                }
            }
        }
        
        return $stats;
    }
}

// Auto-rotate logs on initialization
if (defined('AUTO_ROTATE_LOGS') && AUTO_ROTATE_LOGS) {
    Logger::rotateLogs();
}
