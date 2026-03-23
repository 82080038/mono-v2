<?php
/**
 * KSP Lam Gabe Jaya - Logger System
 * Comprehensive logging with multiple handlers
 */

namespace Core\Logger;

class Logger {
    private static $instance = null;
    private $logFile;
    private $logLevel;
    
    const LEVEL_DEBUG = 'DEBUG';
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_CRITICAL = 'CRITICAL';
    
    private function __construct() {
        $this->logFile = STORAGE_PATH . '/logs/app_' . date('Y-m-d') . '.log';
        $this->logLevel = defined('LOG_LEVEL') ? LOG_LEVEL : 'INFO';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function debug($message, $context = []) {
        $this->log(self::LEVEL_DEBUG, $message, $context);
    }
    
    public function info($message, $context = []) {
        $this->log(self::LEVEL_INFO, $message, $context);
    }
    
    public function warning($message, $context = []) {
        $this->log(self::LEVEL_WARNING, $message, $context);
    }
    
    public function error($message, $context = []) {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }
    
    public function critical($message, $context = []) {
        $this->log(self::LEVEL_CRITICAL, $message, $context);
    }
    
    private function log($level, $message, $context = []) {
        if (!$this->shouldLog($level)) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = empty($context) ? '' : ' | ' . json_encode($context);
        $user = $_SESSION['username'] ?? 'Guest';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        $logEntry = "[{$timestamp}] [{$level}] [{$user}] [{$ip}] {$message}{$contextStr}" . PHP_EOL;
        
        // Write to file
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Also write to error log for critical errors
        if ($level === self::LEVEL_ERROR || $level === self::LEVEL_CRITICAL) {
            error_log("KSP Lam Gabe Jaya: [{$level}] {$message}");
        }
    }
    
    private function shouldLog($level) {
        $levels = [
            self::LEVEL_DEBUG => 0,
            self::LEVEL_INFO => 1,
            self::LEVEL_WARNING => 2,
            self::LEVEL_ERROR => 3,
            self::LEVEL_CRITICAL => 4
        ];
        
        return $levels[$level] >= $levels[$this->logLevel];
    }
    
    public function rotateLogs() {
        $logDir = STORAGE_PATH . '/logs/';
        $files = glob($logDir . 'app_*.log');
        
        // Keep only last 30 days
        $cutoffDate = date('Y-m-d', strtotime('-30 days'));
        
        foreach ($files as $file) {
            $fileDate = substr(basename($file, '.log'), 4);
            if ($fileDate < $cutoffDate) {
                unlink($file);
            }
        }
    }
    
    public function getLogStats() {
        $logDir = STORAGE_PATH . '/logs/';
        $files = glob($logDir . 'app_*.log');
        
        $totalSize = 0;
        $fileCount = count($files);
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
        }
        
        return [
            'file_count' => $fileCount,
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2)
        ];
    }
}
?>
