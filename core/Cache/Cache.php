<?php
/**
 * KSP Lam Gabe Jaya - Cache System
 * Simple file-based caching with TTL support
 */

namespace Core\Cache;

class Cache {
    private static $instance = null;
    private $cacheDir;
    private $defaultTtl;
    
    private function __construct() {
        $this->cacheDir = STORAGE_PATH . '/cache/';
        $this->defaultTtl = 3600; // 1 hour
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function set($key, $value, $ttl = null) {
        $ttl = $ttl ?? $this->defaultTtl;
        $filename = $this->getFilename($key);
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        $serialized = serialize($data);
        $result = file_put_contents($filename, $serialized, LOCK_EX);
        
        return $result !== false;
    }
    
    public function get($key, $default = null) {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return $default;
        }
        
        $data = unserialize(file_get_contents($filename));
        
        if ($data === false || time() > $data['expires']) {
            unlink($filename);
            return $default;
        }
        
        return $data['value'];
    }
    
    public function delete($key) {
        $filename = $this->getFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }
    
    public function clear($pattern = null) {
        if ($pattern === null) {
            // Clear all cache
            $files = glob($this->cacheDir . '*.cache');
            foreach ($files as $file) {
                unlink($file);
            }
            return true;
        }
        
        // Clear matching pattern
        $files = glob($this->cacheDir . $pattern . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    public function has($key) {
        return $this->get($key) !== null;
    }
    
    public function remember($key, $callback, $ttl = null) {
        $value = $this->get($key);
        
        if ($value === null) {
            $value = call_user_func($callback);
            $this->set($key, $value, $ttl);
        }
        
        return $value;
    }
    
    public function increment($key, $step = 1) {
        $value = $this->get($key, 0);
        $value += $step;
        $this->set($key, $value);
        return $value;
    }
    
    public function decrement($key, $step = 1) {
        return $this->increment($key, -$step);
    }
    
    public function flush() {
        return $this->clear();
    }
    
    public function getStats() {
        $files = glob($this->cacheDir . '*.cache');
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
    
    public function cleanup() {
        $files = glob($this->cacheDir . '*.cache');
        $now = time();
        $cleaned = 0;
        
        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            
            if ($data !== false && $now > $data['expires']) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    private function getFilename($key) {
        $safeKey = preg_replace('/[^a-zA-Z0-9_]/', '_', $key);
        return $this->cacheDir . $safeKey . '.cache';
    }
}
?>
