<?php
/**
 * Database Helper Class
 * Provides database methods for CompleteAPIHandlers
 */

class DatabaseHelper {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function fetchOne($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function execute($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function lastInsertId() {
        return $this->db->lastInsertId();
    }
    
    public function checkConnection() {
        try {
            $this->db->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

?>
