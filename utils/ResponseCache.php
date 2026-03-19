<?php
/**
 * Response Caching System
 */
class ResponseCache {
    private static $cache_dir = '/var/www/html/mono/cache';
    
    public static function get($key) {
        $file = self::$cache_dir . '/' . md5($key) . '.cache';
        if (file_exists($file) && (time() - filemtime($file)) < 3600) {
            return unserialize(file_get_contents($file));
        }
        return null;
    }
    
    public static function set($key, $data) {
        $file = self::$cache_dir . '/' . md5($key) . '.cache';
        file_put_contents($file, serialize($data));
    }
}
?>