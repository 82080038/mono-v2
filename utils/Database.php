<?php
/**
 * Database Connection Class
 * Handles all database operations with proper error handling
 */

class Database {
    private static $instance = null;
    private $connection;
    private $config;
    
    private function __construct() {
        require_once __DIR__ . '/../config/Config.php';
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect() {
        try {
            if (!empty(Config::DB_SOCKET)) {
                $dsn = "mysql:unix_socket=" . Config::DB_SOCKET . 
                       ";dbname=" . Config::DB_NAME . 
                       ";charset=" . Config::DB_CHARSET;
            } else {
                $dsn = "mysql:host=" . Config::DB_HOST . 
                       ";dbname=" . Config::DB_NAME . 
                       ";charset=" . Config::DB_CHARSET;
            }
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true
            ];
            
            $this->connection = new PDO($dsn, Config::DB_USER, Config::DB_PASS, $options);
            
            // Set charset after connection
            $this->connection->exec("SET NAMES utf8mb4");
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Koneksi database gagal: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage() . " SQL: " . $sql);
            throw new Exception("Query gagal: " . $e->getMessage());
        }
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->query($sql, $data);
        
        return $this->connection->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $setClause = [];
        foreach ($data as $key => $value) {
            $setClause[] = "$key = :$key";
        }
        
        $sql = "UPDATE $table SET " . implode(', ', $setClause) . " WHERE $where";
        $params = array_merge($data, $whereParams);
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function checkConnection() {
        try {
            $this->connection->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}

?>
