# MySQL & PHP PDO Complete Guide

## 🎯 Overview

MySQL adalah sistem database relasional yang populer, dan PDO (PHP Data Objects) adalah extension PHP yang menyediakan interface universal untuk mengakses database. PDO mendukung multiple database drivers.

## 🔌 PDO Connection

### **Basic Connection**
```php
<?php
try {
    // Database connection parameters
    $host = 'localhost';
    $dbname = 'ksp_lamgabejaya_v2';
    $username = 'root';
    $password = 'root';
    $charset = 'utf8mb4';
    
    // DSN (Data Source Name)
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    
    // PDO options
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => true
    ];
    
    // Create PDO instance
    $pdo = new PDO($dsn, $username, $password, $options);
    
    echo "Database connected successfully!";
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

### **Connection Class**
```php
<?php
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

// Usage
$db = Database::getInstance()->getConnection();
?>
```

## 📋 CRUD Operations

### **Create (INSERT)**
```php
<?php
// Basic insert
$sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute(['john_doe', 'john@example.com', 'hashed_password']);

// Insert with named parameters
$sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'username' => 'jane_doe',
    'email' => 'jane@example.com',
    'password' => 'hashed_password'
]);

// Get last insert ID
$userId = $pdo->lastInsertId();

// Insert multiple records
$sql = "INSERT INTO users (username, email) VALUES (?, ?)";
$stmt = $pdo->prepare($sql);

$users = [
    ['user1', 'user1@example.com'],
    ['user2', 'user2@example.com'],
    ['user3', 'user3@example.com']
];

foreach ($users as $user) {
    $stmt->execute($user);
}
?>
```

### **Read (SELECT)**
```php
<?php
// Select single record
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([1]);
$user = $stmt->fetch();

// Select multiple records
$sql = "SELECT * FROM users WHERE status = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(['active']);
$users = $stmt->fetchAll();

// Select with LIKE
$sql = "SELECT * FROM users WHERE username LIKE ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(['%john%']);
$users = $stmt->fetchAll();

// Select with IN clause
$sql = "SELECT * FROM users WHERE id IN (?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([1, 2, 3]);
$users = $stmt->fetchAll();

// Select with pagination
$page = 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM users LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();
?>
```

### **Update (UPDATE)**
```php
<?php
// Update single record
$sql = "UPDATE users SET email = ? WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(['newemail@example.com', 1]);

// Update with named parameters
$sql = "UPDATE users SET username = :username, email = :email WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'username' => 'new_username',
    'email' => 'newemail@example.com',
    'id' => 1
]);

// Update multiple records
$sql = "UPDATE users SET status = ? WHERE created_at < ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(['inactive', '2024-01-01']);

// Get affected rows
$affectedRows = $stmt->rowCount();
echo "Updated $affectedRows records";
?>
```

### **Delete (DELETE)**
```php
<?php
// Delete single record
$sql = "DELETE FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([1]);

// Delete with condition
$sql = "DELETE FROM users WHERE status = ? AND created_at < ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(['inactive', '2024-01-01']);

// Delete all (use with caution)
$sql = "DELETE FROM users";
$stmt = $pdo->prepare($sql);
$stmt->execute();

// Get affected rows
$affectedRows = $stmt->rowCount();
echo "Deleted $affectedRows records";
?>
```

## 🔒 Prepared Statements

### **Why Use Prepared Statements?**
1. **Security** - Prevents SQL injection
2. **Performance** - Better for repeated queries
3. **Flexibility** - Can bind different values

### **Prepared Statement Examples**
```php
<?php
// Prepare statement once
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");

// Execute multiple times
$usernames = ['john', 'jane', 'bob'];
foreach ($usernames as $username) {
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    // Process user
}

// Named parameters
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND status = :status");
$stmt->execute([
    'username' => 'john',
    'status' => 'active'
]);
$user = $stmt->fetch();

// Bind values explicitly
$stmt = $pdo->prepare("INSERT INTO users (username, age) VALUES (?, ?)");
$stmt->bindValue(1, 'john');
$stmt->bindValue(2, 25, PDO::PARAM_INT);
$stmt->execute();
?>
```

### **Advanced Prepared Statements**
```php
<?php
// Dynamic WHERE clause
$conditions = [];
$params = [];

if (!empty($username)) {
    $conditions[] = "username = ?";
    $params[] = $username;
}

if (!empty($email)) {
    $conditions[] = "email = ?";
    $params[] = $email;
}

if (!empty($status)) {
    $conditions[] = "status = ?";
    $params[] = $status;
}

$sql = "SELECT * FROM users";
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Dynamic INSERT
$fields = ['username', 'email', 'password'];
$values = ['john', 'john@example.com', 'hashed'];

$fieldList = implode(', ', $fields);
$placeholders = implode(', ', array_fill(0, count($fields), '?'));

$sql = "INSERT INTO users ($fieldList) VALUES ($placeholders)";
$stmt = $pdo->prepare($sql);
$stmt->execute($values);
?>
```

## 📊 Fetch Modes

### **Different Fetch Styles**
```php
<?php
// Fetch as associative array (default)
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch as numeric array
$users = $stmt->fetchAll(PDO::FETCH_NUM);

// Fetch as both (associative and numeric)
$users = $stmt->fetchAll(PDO::FETCH_BOTH);

// Fetch as object
$users = $stmt->fetchAll(PDO::FETCH_OBJ);

// Fetch into class
class User {
    public $id;
    public $username;
    public $email;
}

$users = $stmt->fetchAll(PDO::FETCH_CLASS, 'User');

// Fetch column
$usernames = $stmt->fetchAll(PDO::FETCH_COLUMN, 1); // Column index 1

// Fetch key-value pairs
$userMap = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch unique values
$usernames = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_COLUMN);

// Fetch grouped
$grouped = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
?>
```

### **Fetch Methods**
```php
<?php
$stmt = $pdo->query("SELECT * FROM users");

// fetch() - Get next row
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// fetchAll() - Get all rows
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fetchColumn() - Get single column value
$count = $stmt->fetchColumn();

// fetchObject() - Get as object
$user = $stmt->fetchObject('User');

// Iterate through results
while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $user['username'] . "\n";
}
?>
```

## 🔄 Transactions

### **Basic Transaction**
```php
<?php
try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Update user balance
    $sql1 = "UPDATE accounts SET balance = balance - 100 WHERE user_id = ?";
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute([1]);
    
    // Update recipient balance
    $sql2 = "UPDATE accounts SET balance = balance + 100 WHERE user_id = ?";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([2]);
    
    // Record transaction
    $sql3 = "INSERT INTO transactions (from_user, to_user, amount) VALUES (?, ?, ?)";
    $stmt3 = $pdo->prepare($sql3);
    $stmt3->execute([1, 2, 100]);
    
    // Commit transaction
    $pdo->commit();
    echo "Transaction completed successfully!";
    
} catch (Exception $e) {
    // Rollback on error
    $pdo->rollBack();
    echo "Transaction failed: " . $e->getMessage();
}
?>
```

### **Transaction with Savepoints**
```php
<?php
try {
    $pdo->beginTransaction();
    
    // First operation
    $pdo->exec("INSERT INTO users (username) VALUES ('user1')");
    
    // Create savepoint
    $pdo->exec("SAVEPOINT savepoint1");
    
    // Second operation (might fail)
    try {
        $pdo->exec("INSERT INTO users (username) VALUES ('user2')");
    } catch (Exception $e) {
        // Rollback to savepoint
        $pdo->exec("ROLLBACK TO savepoint1");
        echo "Rolled back to savepoint, continuing with first operation";
    }
    
    // Continue with other operations
    $pdo->exec("INSERT INTO users (username) VALUES ('user3')");
    
    $pdo->commit();
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Transaction failed: " . $e->getMessage();
}
?>
```

## 🔍 Error Handling

### **Exception Handling**
```php
<?php
try {
    $stmt = $pdo->prepare("SELECT * FROM non_existent_table");
    $stmt->execute();
} catch (PDOException $e) {
    // Log error
    error_log("Database error: " . $e->getMessage());
    
    // Show user-friendly message
    echo "Database error occurred. Please try again later.";
    
    // For development, show detailed error
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo "Error: " . $e->getMessage();
    }
}
?>
```

### **Custom Error Handler**
```php
<?php
class DatabaseException extends Exception {
    private $sql;
    private $params;
    
    public function __construct($message, $sql = null, $params = [], $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->sql = $sql;
        $this->params = $params;
    }
    
    public function getSQL() {
        return $this->sql;
    }
    
    public function getParams() {
        return $this->params;
    }
}

function executeQuery($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        throw new DatabaseException(
            "Query execution failed: " . $e->getMessage(),
            $sql,
            $params,
            0,
            $e
        );
    }
}

// Usage
try {
    $stmt = executeQuery($pdo, "SELECT * FROM users WHERE id = ?", [1]);
    $user = $stmt->fetch();
} catch (DatabaseException $e) {
    error_log("SQL: " . $e->getSQL());
    error_log("Params: " . json_encode($e->getParams()));
    error_log("Error: " . $e->getMessage());
}
?>
```

## 📈 Performance Optimization

### **Connection Pooling**
```php
<?php
// Persistent connections
$options = [
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

$pdo = new PDO($dsn, $username, $password, $options);
?>
```

### **Batch Operations**
```php
<?php
// Batch insert
$sql = "INSERT INTO users (username, email) VALUES (?, ?)";
$stmt = $pdo->prepare($sql);

$pdo->beginTransaction();
try {
    foreach ($users as $user) {
        $stmt->execute([$user['username'], $user['email']]);
    }
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
}

// Batch update
$sql = "UPDATE users SET status = ? WHERE id = ?";
$stmt = $pdo->prepare($sql);

$pdo->beginTransaction();
try {
    foreach ($updates as $update) {
        $stmt->execute([$update['status'], $update['id']]);
    }
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
}
?>
```

### **Query Optimization**
```php
<?php
// Use EXPLAIN to analyze queries
$sql = "EXPLAIN SELECT * FROM users WHERE username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(['john']);
$explain = $stmt->fetchAll();

// Use LIMIT for large datasets
$sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT 100";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll();

// Use indexes effectively
$sql = "SELECT * FROM users WHERE status = ? AND created_at > ? ORDER BY id DESC LIMIT ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(['active', '2024-01-01', 50]);
$users = $stmt->fetchAll();
?>
```

## 🔐 Security Best Practices

### **Prevent SQL Injection**
```php
<?php
// Always use prepared statements
$sql = "SELECT * FROM users WHERE username = ? AND password = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username, $password]);

// Never do this (vulnerable):
// $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
?>
```

### **Password Hashing**
```php
<?php
// Hash password
$password = 'user_password';
$hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

// Verify password
if (password_verify($inputPassword, $hashedPassword)) {
    echo "Password correct";
} else {
    echo "Password incorrect";
}

// Rehash if needed
if (password_needs_rehash($hashedPassword, PASSWORD_ARGON2ID)) {
    $newHash = password_hash($password, PASSWORD_ARGON2ID);
    // Update in database
}
?>
```

### **Data Validation**
```php
<?php
// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate numeric
function validateNumeric($value, $min = null, $max = null) {
    $options = ['options' => ['default' => null]];
    if ($min !== null) $options['options']['min_range'] = $min;
    if ($max !== null) $options['options']['max_range'] = $max;
    
    return filter_var($value, FILTER_VALIDATE_INT, $options);
}

// Sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Usage
$email = sanitizeInput($_POST['email']);
if (!validateEmail($email)) {
    throw new InvalidArgumentException("Invalid email format");
}
?>
```

## 📊 Database Schema

### **Create Tables**
```php
<?php
// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user', 'guest') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$pdo->exec($sql);

// Create transactions table
$sql = "CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_user INT NOT NULL,
    to_user INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('deposit', 'withdrawal', 'transfer') NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_user) REFERENCES users(id),
    FOREIGN KEY (to_user) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$pdo->exec($sql);
?>
```

### **Indexes**
```php
<?php
// Create indexes
$indexes = [
    "CREATE INDEX idx_users_username ON users(username)",
    "CREATE INDEX idx_users_email ON users(email)",
    "CREATE INDEX idx_users_status ON users(status)",
    "CREATE INDEX idx_transactions_from_user ON transactions(from_user)",
    "CREATE INDEX idx_transactions_to_user ON transactions(to_user)",
    "CREATE INDEX idx_transactions_created_at ON transactions(created_at)"
];

foreach ($indexes as $sql) {
    $pdo->exec($sql);
}
?>
```

## 🎯 Common Patterns

### **Repository Pattern**
```php
<?php
class UserRepository {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    public function create($user) {
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $user['username'],
            $user['email'],
            password_hash($user['password'], PASSWORD_ARGON2ID),
            $user['role'] ?? 'user'
        ]);
        return $this->pdo->lastInsertId();
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }
        
        $params[] = $id;
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}

// Usage
$userRepo = new UserRepository($pdo);
$user = $userRepo->findById(1);
?>
```

### **Data Access Object (DAO)**
```php
<?php
abstract class BaseDAO {
    protected $pdo;
    protected $table;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAll() {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $fields = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));
        return $this->pdo->lastInsertId();
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }
        
        $params[] = $id;
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}

class UserDAO extends BaseDAO {
    protected $table = 'users';
    
    public function findByUsername($username) {
        $sql = "SELECT * FROM {$this->table} WHERE username = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
}

// Usage
$userDAO = new UserDAO($pdo);
$users = $userDAO->getAll();
?>
```

## 🔧 Debugging

### **Debug Mode**
```php
<?php
// Enable error reporting for development
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Log all queries
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Custom debug function
    function debugQuery($sql, $params = []) {
        echo "SQL: " . $sql . "\n";
        echo "Params: " . json_encode($params) . "\n";
    }
}
?>
```

### **Query Logging**
```php
<?php
class QueryLogger {
    private $pdo;
    private $logFile;
    
    public function __construct(PDO $pdo, $logFile = 'queries.log') {
        $this->pdo = $pdo;
        $this->logFile = $logFile;
    }
    
    public function execute($sql, $params = []) {
        $start = microtime(true);
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $end = microtime(true);
            $duration = round(($end - $start) * 1000, 2);
            
            $this->logQuery($sql, $params, $duration);
            
            return $stmt;
        } catch (PDOException $e) {
            $this->logError($sql, $params, $e);
            throw $e;
        }
    }
    
    private function logQuery($sql, $params, $duration) {
        $logEntry = sprintf(
            "[%s] Query (%.2fms): %s | Params: %s\n",
            date('Y-m-d H:i:s'),
            $duration,
            $sql,
            json_encode($params)
        );
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
    
    private function logError($sql, $params, $exception) {
        $logEntry = sprintf(
            "[%s] ERROR: %s | Params: %s | Error: %s\n",
            date('Y-m-d H:i:s'),
            $sql,
            json_encode($params),
            $exception->getMessage()
        );
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
}

// Usage
$logger = new QueryLogger($pdo);
$stmt = $logger->execute("SELECT * FROM users WHERE id = ?", [1]);
?>
```

---

**📚 Resources:**
- [PHP PDO Manual](https://www.php.net/manual/en/book.pdo.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [PDO Tutorial](https://www.phptutorial.net/php-pdo/)
