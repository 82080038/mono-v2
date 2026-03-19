<?php
/**
 * Performance Optimization
 */
class PerformanceOptimizer {
    public static function compressOutput($buffer) {
        return ob_gzhandler($buffer, 5);
    }
    
    public static function setCacheHeaders($seconds = 3600) {
        header("Cache-Control: public, max-age={$seconds}");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + $seconds) . " GMT");
    }
    
    public static function optimizeDatabase($pdo) {
        $pdo->exec("SET GLOBAL query_cache_size = 268435456");
        $pdo->exec("SET GLOBAL innodb_buffer_pool_size = 134217728");
    }
}
?>