<?php
/**
 * KSP Lam Gabe Jaya - Database Configuration
 * 100% English PHP Variables and Functions
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'ksp_lamgabejaya_v2');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_CHARSET', 'utf8mb4');

// Database connection class
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    private $pdo;
    private $error;

    /**
     * Database constructor - Establish connection
     */
    public function __construct() {
        $this->connect();
    }

    /**
     * Establish database connection
     */
    private function connect() {
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
        ];

        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            throw new Exception("Database connection failed: " . $this->error);
        }
    }

    /**
     * Get PDO instance
     */
    public function getConnection() {
        return $this->pdo;
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollback();
    }

    /**
     * Execute query with parameters
     */
    public function executeQuery($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }

    /**
     * Fetch single record
     */
    public function fetch($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        return $stmt->fetch();
    }

    /**
     * Fetch multiple records
     */
    public function fetchAll($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        return $stmt->fetchAll();
    }

    /**
     * Insert record
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(array_values($data));
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Insert failed: " . $e->getMessage());
        }
    }

    /**
     * Update record
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setClause = [];
        $values = [];
        
        foreach ($data as $column => $value) {
            $setClause[] = "{$column} = ?";
            $values[] = $value;
        }
        
        $setClause = implode(', ', $setClause);
        $values = array_merge($values, $whereParams);
        
        $query = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($values);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Update failed: " . $e->getMessage());
        }
    }

    /**
     * Delete record
     */
    public function delete($table, $where, $params = []) {
        $query = "DELETE FROM {$table} WHERE {$where}";
        
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Delete failed: " . $e->getMessage());
        }
    }

    /**
     * Check if table exists
     */
    public function tableExists($tableName) {
        $query = "SHOW TABLES LIKE '$tableName'";
        $stmt = $this->pdo->query($query);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get table columns
     */
    public function getTableColumns($tableName) {
        $query = "DESCRIBE {$tableName}";
        $stmt = $this->executeQuery($query);
        return $stmt->fetchAll();
    }

    /**
     * Create table if not exists
     */
    public function createTable($tableName, $columns) {
        $columnDefinitions = [];
        
        foreach ($columns as $name => $definition) {
            $columnDefinitions[] = "`{$name}` {$definition}";
        }
        
        $columnDefinitions = implode(', ', $columnDefinitions);
        $query = "CREATE TABLE IF NOT EXISTS `{$tableName}` ({$columnDefinitions}) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $this->executeQuery($query);
            return true;
        } catch (PDOException $e) {
            throw new Exception("Table creation failed: " . $e->getMessage());
        }
    }

    /**
     * Drop table if exists
     */
    public function dropTable($tableName) {
        $query = "DROP TABLE IF EXISTS `{$tableName}`";
        
        try {
            $this->executeQuery($query);
            return true;
        } catch (PDOException $e) {
            throw new Exception("Table drop failed: " . $e->getMessage());
        }
    }

    /**
     * Get last inserted ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Get affected rows count
     */
    public function rowCount($stmt) {
        return $stmt->rowCount();
    }

    /**
     * Escape string for SQL
     */
    public function escape($string) {
        return $this->pdo->quote($string);
    }

    /**
     * Get database version
     */
    public function getVersion() {
        $query = "SELECT VERSION() as version";
        $result = $this->fetch($query);
        return $result['version'] ?? 'Unknown';
    }

    /**
     * Test database connection
     */
    public function testConnection() {
        try {
            $query = "SELECT 1";
            $this->executeQuery($query);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get database info
     */
    public function getDatabaseInfo() {
        $info = [];
        
        // Get database size
        $query = "SELECT table_schema AS 'database', 
                         ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb'
                  FROM information_schema.tables 
                  WHERE table_schema = ?";
        $result = $this->fetch($query, [$this->db_name]);
        $info['size_mb'] = $result['size_mb'] ?? 0;
        
        // Get table count
        $query = "SELECT COUNT(*) as table_count 
                  FROM information_schema.tables 
                  WHERE table_schema = ?";
        $result = $this->fetch($query, [$this->db_name]);
        $info['table_count'] = $result['table_count'] ?? 0;
        
        // Get connection info
        $info['host'] = $this->host;
        $info['database'] = $this->db_name;
        $info['charset'] = $this->charset;
        $info['version'] = $this->getVersion();
        
        return $info;
    }

    /**
     * Close connection
     */
    public function close() {
        $this->pdo = null;
    }

    /**
     * Destructor - Close connection
     */
    public function __destruct() {
        $this->close();
    }
}

/**
 * Database migration helper
 */
class DatabaseMigration {
    private $database;
    
    public function __construct($database) {
        $this->database = $database;
    }

    /**
     * Run database migrations
     */
    public function migrate() {
        $migrations = $this->getMigrations();
        
        foreach ($migrations as $migration) {
            if (!$this->migrationExists($migration['name'])) {
                $this->runMigration($migration);
            }
        }
    }

    /**
     * Get migration files
     */
    private function getMigrations() {
        // This would read migration files from a directory
        // For now, return basic migrations
        return [
            [
                'name' => 'create_users_table',
                'sql' => $this->getUsersTableSQL()
            ],
            [
                'name' => 'create_members_table',
                'sql' => $this->getMembersTableSQL()
            ],
            [
                'name' => 'create_loans_table',
                'sql' => $this->getLoansTableSQL()
            ],
            [
                'name' => 'create_savings_table',
                'sql' => $this->getSavingsTableSQL()
            ],
            [
                'name' => 'create_transactions_table',
                'sql' => $this->getTransactionsTableSQL()
            ]
        ];
    }

    /**
     * Check if migration exists
     */
    private function migrationExists($migrationName) {
        if (!$this->database->tableExists('migrations')) {
            return false;
        }
        
        $query = "SELECT COUNT(*) as count FROM migrations WHERE name = ?";
        $result = $this->database->fetch($query, [$migrationName]);
        return $result['count'] > 0;
    }

    /**
     * Run migration
     */
    private function runMigration($migration) {
        try {
            $this->database->beginTransaction();
            
            // Run migration SQL
            $this->database->executeQuery($migration['sql']);
            
            // Record migration
            $this->recordMigration($migration['name']);
            
            $this->database->commit();
            
            error_log("Migration {$migration['name']} completed successfully");
        } catch (Exception $e) {
            $this->database->rollback();
            error_log("Migration {$migration['name']} failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Record migration
     */
    private function recordMigration($migrationName) {
        if (!$this->database->tableExists('migrations')) {
            $this->createMigrationsTable();
        }
        
        $query = "INSERT INTO migrations (name, executed_at) VALUES (?, NOW())";
        $this->database->executeQuery($query, [$migrationName]);
    }

    /**
     * Create migrations table
     */
    private function createMigrationsTable() {
        $sql = "CREATE TABLE migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->database->executeQuery($sql);
    }

    /**
     * Get users table SQL
     */
    private function getUsersTableSQL() {
        return "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            role ENUM('super_admin', 'admin', 'mantri', 'member') DEFAULT 'member',
            is_active BOOLEAN DEFAULT TRUE,
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }

    /**
     * Get members table SQL
     */
    private function getMembersTableSQL() {
        return "CREATE TABLE members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            member_number VARCHAR(50) NOT NULL UNIQUE,
            id_number VARCHAR(50) NOT NULL UNIQUE,
            birth_date DATE,
            address TEXT,
            city VARCHAR(100),
            postal_code VARCHAR(10),
            occupation VARCHAR(100),
            monthly_income DECIMAL(12, 2),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }

    /**
     * Get loans table SQL
     */
    private function getLoansTableSQL() {
        return "CREATE TABLE loans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            member_id INT NOT NULL,
            loan_number VARCHAR(50) NOT NULL UNIQUE,
            amount DECIMAL(12, 2) NOT NULL,
            interest_rate DECIMAL(5, 2) DEFAULT 1.00,
            term_months INT NOT NULL,
            monthly_payment DECIMAL(12, 2) NOT NULL,
            purpose TEXT,
            status ENUM('pending', 'approved', 'rejected', 'disbursed', 'completed', 'defaulted') DEFAULT 'pending',
            approved_by INT NULL,
            approved_at TIMESTAMP NULL,
            disbursed_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL,
            FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
            FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }

    /**
     * Get savings table SQL
     */
    private function getSavingsTableSQL() {
        return "CREATE TABLE savings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            member_id INT NOT NULL,
            account_number VARCHAR(50) NOT NULL UNIQUE,
            account_type ENUM('mandatory', 'voluntary', 'fixed_deposit') DEFAULT 'mandatory',
            balance DECIMAL(12, 2) DEFAULT 0.00,
            interest_rate DECIMAL(5, 2) DEFAULT 0.00,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL,
            FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }

    /**
     * Get transactions table SQL
     */
    private function getTransactionsTableSQL() {
        return "CREATE TABLE transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_number VARCHAR(50) NOT NULL UNIQUE,
            member_id INT NOT NULL,
            type ENUM('deposit', 'withdrawal', 'loan_payment', 'loan_disbursement', 'interest_payment') NOT NULL,
            amount DECIMAL(12, 2) NOT NULL,
            description TEXT,
            reference_id INT NULL,
            reference_type VARCHAR(50) NULL,
            status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
            processed_by INT NULL,
            processed_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL,
            FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
            FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
}
?>
