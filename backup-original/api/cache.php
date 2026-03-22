<?php
/**
 * batch-update-legacy.php - Updated with Security
 * Auto-generated security update
 */

// Security headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token");

// Handle preflight requests
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    exit(0);
}

// Prevent direct access
if (!defined("KSP_API_ACCESS")) {
    header("HTTP/1.0 403 Forbidden");
    exit("Direct access to this file is not allowed.");
}

// Include required files
require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/DatabaseHelper.php";
require_once __DIR__ . "/Logger.php";
require_once __DIR__ . "/DataValidator.php";
require_once __DIR__ . "/SecurityLogger.php";
require_once __DIR__ . "/AuthHelper.php";
require_once __DIR__ . "/SecurityHelper.php";
require_once __DIR__ . "/SecurityMiddleware.php";

// Initialize services
Logger::initialize();
$securityLogger = SecurityLogger::getInstance();
$db = DatabaseHelper::getInstance();
$validator = new DataValidator();

// Initialize response
$response = [
    "success" => false,
    "message" => "",
    "data" => null,
    "errors" => [],
    "timestamp" => date("Y-m-d H:i:s")
];

/**
 * Simple Caching System
 * File-based caching for KSP Lam Gabe Jaya
 */

class Cache {
    private static $cacheDir = null;
    private static $defaultTtl = 3600; // 1 hour
    
    public static function init() {
        self::$cacheDir = __DIR__ . '/../cache';
        self::$defaultTtl = $_ENV['CACHE_TTL'] ?? 3600;
        
        // Create cache directory if not exists
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }
    
    /**
     * Get cached data
     */
    public static function get($key, $default = null) {
        if (!self::$cacheDir) {
            self::init();
        }
        
        $filename = self::getFilename($key);
        
        if (!file_exists($filename)) {
            return $default;
        }
        
        $data = unserialize(file_get_contents($filename));
        
        // Check if cache is expired
        if (time() > $data['expires']) {
            self::delete($key);
            return $default;
        }
        
        return $data['value'];
    }
    
    /**
     * Set cached data
     */
    public static function set($key, $value, $ttl = null) {
        if (!self::$cacheDir) {
            self::init();
        }
        
        $ttl = $ttl ?? self::$defaultTtl;
        $filename = self::getFilename($key);
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        file_put_contents($filename, serialize($data), LOCK_EX);
        return true;
    }
    
    /**
     * Delete cached data
     */
    public static function delete($key) {
        if (!self::$cacheDir) {
            self::init();
        }
        
        $filename = self::getFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }
    
    /**
     * Clear all cache
     */
    public static function clear() {
        if (!self::$cacheDir) {
            self::init();
        }
        
        $files = glob(self::$cacheDir . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        return true;
    }
    
    /**
     * Check if key exists and is not expired
     */
    public static function has($key) {
        return self::get($key) !== null;
    }
    
    /**
     * Remember value with callback
     */
    public static function remember($key, $callback, $ttl = null) {
        $value = self::get($key);
        
        if ($value === null) {
            $value = $callback();
            self::set($key, $value, $ttl);
        }
        
        return $value;
    }
    
    /**
     * Increment numeric value
     */
    public static function increment($key, $step = 1) {
        $value = self::get($key, 0);
        $value += $step;
        self::set($key, $value);
        return $value;
    }
    
    /**
     * Decrement numeric value
     */
    public static function decrement($key, $step = 1) {
        return self::increment($key, -$step);
    }
    
    /**
     * Get cache filename
     */
    private static function getFilename($key) {
        return self::$cacheDir . '/' . md5($key) . '.cache';
    }
    
    /**
     * Clean expired cache files
     */
    public static function cleanExpired() {
        if (!self::$cacheDir) {
            self::init();
        }
        
        $files = glob(self::$cacheDir . '/*.cache');
        $cleaned = 0;
        
        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            
            if (time() > $data['expires']) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Get cache statistics
     */
    public static function getStats() {
        if (!self::$cacheDir) {
            self::init();
        }
        
        $files = glob(self::$cacheDir . '/*.cache');
        $total = count($files);
        $expired = 0;
        $size = 0;
        
        foreach ($files as $file) {
            $size += filesize($file);
            
            $data = unserialize(file_get_contents($file));
            if (time() > $data['expires']) {
                $expired++;
            }
        }
        
        return [
            'total_files' => $total,
            'expired_files' => $expired,
            'valid_files' => $total - $expired,
            'total_size' => $size,
            'cache_dir' => self::$cacheDir
        ];
    }
}

/**
 * Database Query Cache
 */
class QueryCache {
    private static $enabled = true;
    private static $ttl = 300; // 5 minutes
    
    /**
     * Execute cached query
     */
    public static function query($pdo, $sql, $params = [], $ttl = null) {
        if (!self::$enabled) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        }
        
        $key = 'query:' . md5($sql . serialize($params));
        
        return Cache::remember($key, function() use ($pdo, $sql, $params) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }, $ttl ?? self::$ttl);
    }
    
    /**
     * Invalidate query cache for table
     */
    public static function invalidateTable($table) {
        // This is a simple implementation
        // In production, you might want to track which queries affect which tables
        Cache::clear();
    }
    
    /**
     * Enable/disable query cache
     */
    public static function setEnabled($enabled) {
        self::$enabled = $enabled;
    }
}

/**
 * API Response Cache
 */
class ApiCache {
    private static $ttl = 60; // 1 minute for API responses
    
    /**
     * Cache API response
     */
    public static function remember($key, $callback, $ttl = null) {
        return Cache::remember("api:$key", $callback, $ttl ?? self::$ttl);
    }
    
    /**
     * Get cache key for API request
     */
    public static function getKey($endpoint, $method, $params = []) {
        return $method . ':' . $endpoint . ':' . md5(serialize($params));
    }
}

/**
 * Session Cache
 */
class SessionCache {
    /**
     * Store data in session
     */
    public static function set($key, $value) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['cache'][$key] = [
            'value' => $value,
            'created' => time()
        ];
    }
    
    /**
     * Get data from session
     */
    public static function get($key, $default = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['cache'][$key]['value'] ?? $default;
    }
    
    /**
     * Check if key exists in session
     */
    public static function has($key) {
        return self::get($key) !== null;
    }
    
    /**
     * Delete data from session
     */
    public static function delete($key) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        unset($_SESSION['cache'][$key]);
    }
    
    /**
     * Clear all session cache
     */
    public static function clear() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['cache'] = [];
    }
}

// Auto-initialize
Cache::init();
?>
