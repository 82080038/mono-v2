<?php

class Logger {
    private static $instance = null;
    private $logFile;
    private $logLevel;
    
    private function __construct() {
        $this->logFile = __DIR__ . '/../logs/system.log';
        $this->logLevel = 'INFO';
        $this->ensureLogDirectory();
    }
    
    public static function initialize() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::initialize();
        }
        return self::$instance;
    }
    
    private function ensureLogDirectory() {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    public function log($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = empty($context) ? '' : ' | ' . json_encode($context);
        $logEntry = "[$timestamp] $level: $message$contextStr" . PHP_EOL;
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    public static function info($message, $context = []) {
        self::getInstance()->log('INFO', $message, $context);
    }
    
    public static function error($message, $context = []) {
        self::getInstance()->log('ERROR', $message, $context);
    }
    
    public static function warning($message, $context = []) {
        self::getInstance()->log('WARNING', $message, $context);
    }
    
    public static function debug($message, $context = []) {
        self::getInstance()->log('DEBUG', $message, $context);
    }
    
    public static function logApiCall($endpoint, $method, $params = [], $response = []) {
        $context = [
            'endpoint' => $endpoint,
            'method' => $method,
            'params' => $params,
            'response_status' => $response['success'] ?? false,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        if ($response['success'] ?? false) {
            self::info("API Call: $endpoint $method", $context);
        } else {
            self::error("API Call Failed: $endpoint $method", $context);
        }
    }
    
    public static function logDatabaseQuery($query, $params = [], $executionTime = 0) {
        $context = [
            'query' => $query,
            'params' => $params,
            'execution_time_ms' => $executionTime * 1000,
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
        ];
        
        self::debug("Database Query", $context);
    }
    
    public static function logSecurityEvent($event, $details = []) {
        $context = array_merge([
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => time()
        ], $details);
        
        self::warning("Security Event: $event", $context);
    }
    
    public function getLogs($limit = 100, $level = null) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $logs = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $logs = array_reverse($logs); // Most recent first
        
        if ($level) {
            $logs = array_filter($logs, function($log) use ($level) {
                return strpos($log, strtoupper($level)) !== false;
            });
        }
        
        return array_slice($logs, 0, $limit);
    }
    
    public function clearLogs() {
        if (file_exists($this->logFile)) {
            return unlink($this->logFile);
        }
        return true;
    }
}
