<?php
/**
 * SQL Injection Prevention
 */
class SQLInjectionProtection {
    public static function safeQuery($pdo, $query, $params = []) {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
    
    public static function escapeString($pdo, $string) {
        return $pdo->quote($string);
    }
    
    public static function validateQuery($query) {
        $blacklist = ['DROP', 'DELETE', 'UPDATE', 'INSERT', 'ALTER'];
        $upper_query = strtoupper($query);
        foreach ($blacklist as $keyword) {
            if (strpos($upper_query, $keyword) !== false) {
                return false;
            }
        }
        return true;
    }
}
?>