<?php
/**
 * Database Connection Pool
 */
class DatabasePool {
    private static $pool = [];
    private static $max_connections = 10;
    
    public static function getConnection() {
        if (count(self::$pool) < self::$max_connections) {
            $conn = new PDO("mysql:host=localhost;dbname=ksp_lamgabejaya", "root", "");
            self::$pool[] = $conn;
            return $conn;
        }
        return array_pop(self::$pool);
    }
    
    public static function releaseConnection($conn) {
        self::$pool[] = $conn;
    }
}
?>