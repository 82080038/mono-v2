<?php
/**
 * KSP Lam Gabe Jaya - Enhanced Database Class
 * Based on database best practices from documentation
 */

class Database {
    private static $instance = null;
    private $pdo;
    private $host;
    private $name;
    private $user;
    private $password;
    private $charset;
    private $connected = false;
    
    /**
     * Singleton pattern implementation
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Private constructor for singleton
     */
    private function __construct() {
        $this->host = DB_HOST ?? 'localhost';
        $this->name = DB_NAME ?? 'gabe';
        $this->user = DB_USER ?? 'root';
        $this->password = DB_PASSWORD ?? '';
        $this->charset = DB_CHARSET ?? 'utf8mb4';
        
        $this->connect();
    }
    
    /**
     * Establish database connection
     * @throws PDOException
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->name};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true, // Connection pooling
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
            ];
            
            $this->pdo = new PDO($dsn, $this->user, $this->password, $options);
            $this->connected = true;
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new DatabaseException("Failed to connect to database: " . $e->getMessage());
        }
    }
    
    /**
     * Get PDO instance
     * @return PDO
     */
    public function getConnection() {
        if (!$this->connected) {
            $this->connect();
        }
        return $this->pdo;
    }
    
    /**
     * Execute query with parameters
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     * @throws DatabaseException
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage() . " SQL: " . $sql);
            throw new DatabaseException("Query execution failed: " . $e->getMessage());
        }
    }
    
    /**
     * Fetch single record
     * @param string $sql
     * @param array $params
     * @return array|null
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }
    
    /**
     * Fetch all records
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Fetch single column
     * @param string $sql
     * @param array $params
     * @param int $column
     * @return mixed
     */
    public function fetchColumn($sql, $params = [], $column = 0) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn($column);
    }
    
    /**
     * Insert record
     * @param string $table
     * @param array $data
     * @return int Last insert ID
     */
    public function insert($table, $data) {
        if (empty($data)) {
            throw new InvalidArgumentException("Data array cannot be empty");
        }
        
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->query($sql, array_values($data));
        return $this->getConnection()->lastInsertId();
    }
    
    /**
     * Update record
     * @param string $table
     * @param array $data
     * @param string $where
     * @param array $whereParams
     * @return int Number of affected rows
     */
    public function update($table, $data, $where, $whereParams = []) {
        if (empty($data)) {
            throw new InvalidArgumentException("Data array cannot be empty");
        }
        
        $set = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $set[] = "{$column} = ?";
            $params[] = $value;
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE {$where}";
        $params = array_merge($params, $whereParams);
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Delete record
     * @param string $table
     * @param string $where
     * @param array $params
     * @return int Number of affected rows
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        $this->getConnection()->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        $this->getConnection()->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        $this->getConnection()->rollBack();
    }
    
    /**
     * Check if transaction is active
     * @return bool
     */
    public function inTransaction() {
        return $this->getConnection()->inTransaction();
    }
    
    /**
     * Get last insert ID
     * @return string
     */
    public function lastInsertId() {
        return $this->getConnection()->lastInsertId();
    }
    
    /**
     * Get affected rows from last query
     * @return int
     */
    public function affectedRows() {
        return $this->getConnection()->rowCount();
    }
    
    /**
     * Execute multiple queries in transaction
     * @param array $queries
     * @return bool
     */
    public function transactionalQuery($queries) {
        try {
            $this->beginTransaction();
            
            foreach ($queries as $query) {
                if (!isset($query['sql']) || !isset($query['params'])) {
                    throw new InvalidArgumentException("Each query must have 'sql' and 'params' keys");
                }
                
                $this->query($query['sql'], $query['params']);
            }
            
            $this->commit();
            return true;
            
        } catch (Exception $e) {
            $this->rollback();
            throw new DatabaseException("Transaction failed: " . $e->getMessage());
        }
    }
    
    /**
     * Build WHERE clause from array
     * @param array $conditions
     * @return array ['where' => string, 'params' => array]
     */
    public function buildWhereClause($conditions) {
        if (empty($conditions)) {
            return ['where' => '1=1', 'params' => []];
        }
        
        $where = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                // Handle IN clause
                $placeholders = implode(',', array_fill(0, count($value), '?'));
                $where[] = "{$column} IN ({$placeholders})";
                $params = array_merge($params, $value);
            } elseif ($value === null) {
                // Handle IS NULL
                $where[] = "{$column} IS NULL";
            } else {
                // Handle regular comparison
                $where[] = "{$column} = ?";
                $params[] = $value;
            }
        }
        
        return [
            'where' => implode(' AND ', $where),
            'params' => $params
        ];
    }
    
    /**
     * Paginate results
     * @param string $sql
     * @param array $params
     * @param int $page
     * @param int $perPage
     * @return array ['data' => array, 'total' => int, 'page' => int, 'perPage' => int, 'totalPages' => int]
     */
    public function paginate($sql, $params = [], $page = 1, $perPage = 10) {
        // Count total records
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as count_query";
        $total = $this->fetchOne($countSql, $params)['total'];
        
        // Calculate pagination
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        // Get paginated data
        $paginatedSql = "{$sql} LIMIT ? OFFSET ?";
        $data = $this->fetchAll($paginatedSql, array_merge($params, [$perPage, $offset]));
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'hasNext' => $page < $totalPages,
            'hasPrev' => $page > 1
        ];
    }
    
    /**
     * Log query for debugging
     * @param string $sql
     * @param array $params
     * @param float $executionTime
     */
    public function logQuery($sql, $params, $executionTime) {
        $logData = [
            'sql' => $sql,
            'params' => $params,
            'execution_time' => $executionTime,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        error_log("Database Query: " . json_encode($logData));
    }
    
    /**
     * Close connection
     */
    public function close() {
        $this->pdo = null;
        $this->connected = false;
    }
    
    /**
     * Destructor
     */
    public function __destruct() {
        $this->close();
    }
}

/**
 * Custom Database Exception
 */
class DatabaseException extends Exception {
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
    
    public function getDetails() {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString()
        ];
    }
}

/**
 * Database Query Builder
 * Helps build complex queries safely
 */
class QueryBuilder {
    private $db;
    private $table;
    private $select = '*';
    private $where = [];
    private $orderBy = [];
    private $limit = null;
    private $offset = null;
    private $joins = [];
    
    public function __construct($table, Database $db = null) {
        $this->table = $table;
        $this->db = $db ?: Database::getInstance();
    }
    
    /**
     * Select columns
     * @param array|string $columns
     * @return self
     */
    public function select($columns) {
        if (is_array($columns)) {
            $this->select = implode(', ', $columns);
        } else {
            $this->select = $columns;
        }
        return $this;
    }
    
    /**
     * Add where condition
     * @param string $column
     * @param mixed $value
     * @param string $operator
     * @return self
     */
    public function where($column, $value = null, $operator = '=') {
        $this->where[] = [
            'column' => $column,
            'value' => $value,
            'operator' => $operator
        ];
        return $this;
    }
    
    /**
     * Add where in condition
     * @param string $column
     * @param array $values
     * @return self
     */
    public function whereIn($column, $values) {
        $this->where[] = [
            'column' => $column,
            'value' => $values,
            'operator' => 'IN'
        ];
        return $this;
    }
    
    /**
     * Add order by
     * @param string $column
     * @param string $direction
     * @return self
     */
    public function orderBy($column, $direction = 'ASC') {
        $this->orderBy[] = [
            'column' => $column,
            'direction' => strtoupper($direction)
        ];
        return $this;
    }
    
    /**
     * Set limit
     * @param int $limit
     * @return self
     */
    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }
    
    /**
     * Set offset
     * @param int $offset
     * @return self
     */
    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }
    
    /**
     * Join table
     * @param string $table
     * @param string $on
     * @param string $type
     * @return self
     */
    public function join($table, $on, $type = 'INNER') {
        $this->joins[] = [
            'table' => $table,
            'on' => $on,
            'type' => $type
        ];
        return $this;
    }
    
    /**
     * Build and execute query
     * @return array
     */
    public function get() {
        $sql = $this->buildQuery();
        $params = $this->buildParams();
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Build and execute query for single record
     * @return array|null
     */
    public function first() {
        $this->limit(1);
        $sql = $this->buildQuery();
        $params = $this->buildParams();
        
        return $this->db->fetchOne($sql, $params);
    }
    
    /**
     * Build SQL query
     * @return string
     */
    private function buildQuery() {
        $sql = "SELECT {$this->select} FROM {$this->table}";
        
        // Add joins
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['on']}";
        }
        
        // Add where conditions
        if (!empty($this->where)) {
            $whereConditions = [];
            foreach ($this->where as $condition) {
                if ($condition['operator'] === 'IN') {
                    $placeholders = implode(',', array_fill(0, count($condition['value']), '?'));
                    $whereConditions[] = "{$condition['column']} {$condition['operator']} ({$placeholders})";
                } else {
                    $whereConditions[] = "{$condition['column']} {$condition['operator']} ?";
                }
            }
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        // Add order by
        if (!empty($this->orderBy)) {
            $orderClauses = [];
            foreach ($this->orderBy as $order) {
                $orderClauses[] = "{$order['column']} {$order['direction']}";
            }
            $sql .= " ORDER BY " . implode(', ', $orderClauses);
        }
        
        // Add limit and offset
        if ($this->limit !== null) {
            $sql .= " LIMIT ?";
            if ($this->offset !== null) {
                $sql .= " OFFSET ?";
            }
        }
        
        return $sql;
    }
    
    /**
     * Build parameters for query
     * @return array
     */
    private function buildParams() {
        $params = [];
        
        foreach ($this->where as $condition) {
            if ($condition['operator'] === 'IN') {
                $params = array_merge($params, $condition['value']);
            } else {
                $params[] = $condition['value'];
            }
        }
        
        if ($this->limit !== null) {
            $params[] = $this->limit;
            if ($this->offset !== null) {
                $params[] = $this->offset;
            }
        }
        
        return $params;
    }
}
?>
