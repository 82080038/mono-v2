# PHP OOP Complete Guide

## 🎯 Overview

Object-Oriented Programming (OOP) dalam PHP memungkinkan pembuatan aplikasi yang lebih terstruktur, modular, dan mudah dikelola. PHP 5+ mendukung fitur OOP lengkap.

## 🏗️ Classes & Objects

### **Basic Class Definition**
```php
<?php
class User {
    // Properties
    public $username;
    private $email;
    protected $age;
    
    // Constructor
    public function __construct($username, $email, $age) {
        $this->username = $username;
        $this->email = $email;
        $this->age = $age;
    }
    
    // Methods
    public function getDetails() {
        return "Username: {$this->username}, Email: {$this->email}, Age: {$this->age}";
    }
    
    // Getters and Setters
    public function getEmail() {
        return $this->email;
    }
    
    public function setEmail($email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->email = $email;
            return true;
        }
        return false;
    }
    
    public function getAge() {
        return $this->age;
    }
    
    public function setAge($age) {
        if ($age >= 0 && $age <= 150) {
            $this->age = $age;
            return true;
        }
        return false;
    }
}

// Create object
$user = new User('john_doe', 'john@example.com', 25);
echo $user->getDetails(); // Output: Username: john_doe, Email: john@example.com, Age: 25
?>
```

### **Access Modifiers**
```php
<?php
class Example {
    public $publicProperty = 'Public';      // Accessible from anywhere
    private $privateProperty = 'Private';    // Accessible only within class
    protected $protectedProperty = 'Protected'; // Accessible within class and child classes
    
    public function publicMethod() {
        return 'Public method';
    }
    
    private function privateMethod() {
        return 'Private method';
    }
    
    protected function protectedMethod() {
        return 'Protected method';
    }
    
    public function demonstrate() {
        echo $this->publicProperty;     // ✅ Works
        echo $this->privateProperty;   // ✅ Works
        echo $this->protectedProperty;  // ✅ Works
        
        $this->publicMethod();        // ✅ Works
        $this->privateMethod();       // ✅ Works
        $this->protectedMethod();     // ✅ Works
    }
}

$example = new Example();
echo $example->publicProperty;     // ✅ Works
echo $example->privateProperty;   // ❌ Fatal error
echo $example->protectedProperty;  // ❌ Fatal error
?>
```

### **Static Properties & Methods**
```php
<?php
class Counter {
    private static $count = 0;
    public static $instances = 0;
    
    public function __construct() {
        self::$count++;
        self::$instances++;
    }
    
    public static function getCount() {
        return self::$count;
    }
    
    public static function reset() {
        self::$count = 0;
        self::$instances = 0;
    }
    
    public function getInstanceCount() {
        return self::$instances;
    }
}

// Static methods can be called without instantiation
echo Counter::getCount(); // 0

// Create instances
$c1 = new Counter();
$c2 = new Counter();

echo Counter::getCount(); // 2
echo Counter::$instances;  // 2
?>
```

## 🧬 Inheritance

### **Basic Inheritance**
```php
<?php
class Person {
    protected $name;
    protected $age;
    
    public function __construct($name, $age) {
        $this->name = $name;
        $this->age = $age;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getAge() {
        return $this->age;
    }
    
    public function introduce() {
        return "Hi, I'm {$this->name} and I'm {$this->age} years old.";
    }
}

class Student extends Person {
    private $studentId;
    private $grade;
    
    public function __construct($name, $age, $studentId, $grade) {
        // Call parent constructor
        parent::__construct($name, $age);
        $this->studentId = $studentId;
        $this->grade = $grade;
    }
    
    public function getStudentId() {
        return $this->studentId;
    }
    
    public function getGrade() {
        return $this->grade;
    }
    
    // Override parent method
    public function introduce() {
        $parentIntro = parent::introduce();
        return $parentIntro . " I'm a student with ID {$this->studentId}.";
    }
    
    // New method
    public function study() {
        return "{$this->name} is studying hard!";
    }
}

$student = new Student('Alice', 20, 'STU001', 'A');
echo $student->introduce(); // Uses overridden method
echo $student->study();     // Uses new method
?>
```

### **Method Overriding**
```php
<?php
class Animal {
    protected $name;
    
    public function __construct($name) {
        $this->name = $name;
    }
    
    public function makeSound() {
        return "Some generic animal sound";
    }
    
    public function getName() {
        return $this->name;
    }
}

class Dog extends Animal {
    public function makeSound() {
        return "Woof! Woof!";
    }
    
    public function wagTail() {
        return "{$this->name} is wagging tail happily!";
    }
}

class Cat extends Animal {
    public function makeSound() {
        return "Meow! Meow!";
    }
    
    public function purr() {
        return "{$this->name} is purring contentedly!";
    }
}

$dog = new Dog('Buddy');
$cat = new Cat('Whiskers');

echo $dog->makeSound(); // Woof! Woof!
echo $cat->makeSound(); // Meow! Meow!
?>
```

### **Parent Keyword**
```php
<?php
class Vehicle {
    protected $brand;
    protected $model;
    
    public function __construct($brand, $model) {
        $this->brand = $brand;
        $this->model = $model;
    }
    
    public function getInfo() {
        return "Brand: {$this->brand}, Model: {$this->model}";
    }
    
    protected function getBrand() {
        return $this->brand;
    }
}

class Car extends Vehicle {
    private $doors;
    
    public function __construct($brand, $model, $doors) {
        parent::__construct($brand, $model); // Call parent constructor
        $this->doors = $doors;
    }
    
    public function getInfo() {
        $parentInfo = parent::getInfo(); // Call parent method
        return $parentInfo . ", Doors: {$this->doors}";
    }
    
    public function getDetailedInfo() {
        return "This is a " . parent::getBrand() . " car with {$this->doors} doors";
    }
}

$car = new Car('Toyota', 'Camry', 4);
echo $car->getInfo(); // Brand: Toyota, Model: Camry, Doors: 4
?>
```

## 🎭 Polymorphism

### **Method Overriding**
```php
<?php
abstract class Shape {
    abstract public function getArea();
    abstract public function getPerimeter();
    
    public function describe() {
        return "This is a shape with area: " . $this->getArea();
    }
}

class Circle extends Shape {
    private $radius;
    
    public function __construct($radius) {
        $this->radius = $radius;
    }
    
    public function getArea() {
        return pi() * $this->radius * $this->radius;
    }
    
    public function getPerimeter() {
        return 2 * pi() * $this->radius;
    }
}

class Rectangle extends Shape {
    private $width;
    private $height;
    
    public function __construct($width, $height) {
        $this->width = $width;
        $this->height = $height;
    }
    
    public function getArea() {
        return $this->width * $this->height;
    }
    
    public function getPerimeter() {
        return 2 * ($this->width + $this->height);
    }
}

// Polymorphism in action
$shapes = [
    new Circle(5),
    new Rectangle(4, 6),
    new Circle(3)
];

foreach ($shapes as $shape) {
    echo $shape->describe() . "\n";
    echo "Perimeter: " . $shape->getPerimeter() . "\n\n";
}
?>
```

### **Interface Implementation**
```php
<?php
interface Loggable {
    public function log($message);
}

interface Cacheable {
    public function cache($key, $data);
    public function getCache($key);
}

class FileLogger implements Loggable {
    private $logFile;
    
    public function __construct($logFile) {
        $this->logFile = $logFile;
    }
    
    public function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $message\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
}

class DatabaseLogger implements Loggable {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function log($message) {
        $sql = "INSERT INTO logs (message, created_at) VALUES (?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$message]);
    }
}

class UserService implements Loggable, Cacheable {
    private $logger;
    private $cache = [];
    
    public function __construct(Loggable $logger) {
        $this->logger = $logger;
    }
    
    public function log($message) {
        $this->logger->log($message);
    }
    
    public function cache($key, $data) {
        $this->cache[$key] = $data;
    }
    
    public function getCache($key) {
        return $this->cache[$key] ?? null;
    }
    
    public function createUser($userData) {
        $this->log("Creating user: " . $userData['username']);
        // User creation logic
        $this->cache('user_' . $userData['username'], $userData);
    }
}
?>
```

## 🎯 Abstract Classes

### **Abstract Class Definition**
```php
<?php
abstract class DatabaseConnection {
    protected $connection;
    protected $host;
    protected $database;
    
    abstract public function connect();
    abstract public function disconnect();
    abstract public function query($sql);
    
    public function __construct($host, $database) {
        $this->host = $host;
        $this->database = $database;
    }
    
    public function getHost() {
        return $this->host;
    }
    
    public function getDatabase() {
        return $this->database;
    }
    
    // Concrete method
    public function testConnection() {
        try {
            $this->connect();
            $result = $this->query("SELECT 1");
            $this->disconnect();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

class MySQLConnection extends DatabaseConnection {
    private $username;
    private $password;
    
    public function __construct($host, $database, $username, $password) {
        parent::__construct($host, $database);
        $this->username = $username;
        $this->password = $password;
    }
    
    public function connect() {
        $dsn = "mysql:host={$this->host};dbname={$this->database}";
        $this->connection = new PDO($dsn, $this->username, $this->password);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function disconnect() {
        $this->connection = null;
    }
    
    public function query($sql) {
        return $this->connection->query($sql);
    }
}

class PostgreSQLConnection extends DatabaseConnection {
    private $username;
    private $password;
    
    public function __construct($host, $database, $username, $password) {
        parent::__construct($host, $database);
        $this->username = $username;
        $this->password = $password;
    }
    
    public function connect() {
        $dsn = "pgsql:host={$this->host};dbname={$this->database}";
        $this->connection = new PDO($dsn, $this->username, $this->password);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function disconnect() {
        $this->connection = null;
    }
    
    public function query($sql) {
        return $this->connection->query($sql);
    }
}
?>
```

## 🎲 Traits

### **Trait Definition**
```php
<?php
trait Loggable {
    protected $logFile = 'app.log';
    
    public function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] " . get_class($this) . ": $message\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
    
    public function logError($error) {
        $this->log("ERROR: " . $error);
    }
    
    public function logInfo($info) {
        $this->log("INFO: " . $info);
    }
}

trait Cacheable {
    protected $cache = [];
    
    public function cache($key, $value, $ttl = 3600) {
        $this->cache[$key] = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
    }
    
    public function getCache($key) {
        if (isset($this->cache[$key])) {
            $item = $this->cache[$key];
            if ($item['expires'] > time()) {
                return $item['value'];
            }
            unset($this->cache[$key]);
        }
        return null;
    }
    
    public function clearCache() {
        $this->cache = [];
    }
}

trait Validatable {
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public function validateRequired($data, $fields) {
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("Field $field is required");
            }
        }
        return true;
    }
}

class UserService {
    use Loggable, Cacheable, Validatable;
    
    public function createUser($userData) {
        $this->validateRequired($userData, ['username', 'email']);
        
        if (!$this->validateEmail($userData['email'])) {
            throw new InvalidArgumentException("Invalid email format");
        }
        
        $this->logInfo("Creating user: " . $userData['username']);
        
        // Simulate user creation
        $user = [
            'id' => rand(1, 1000),
            'username' => $userData['username'],
            'email' => $userData['email'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->cache('user_' . $user['username'], $user);
        
        return $user;
    }
    
    public function getUser($username) {
        $user = $this->getCache('user_' . $username);
        
        if ($user === null) {
            $this->logInfo("Cache miss for user: $username");
            // Simulate database fetch
            $user = ['username' => $username, 'email' => 'user@example.com'];
            $this->cache('user_' . $username, $user);
        }
        
        return $user;
    }
}
?>
```

## 🪄 Magic Methods

### **Common Magic Methods**
```php
<?php
class User {
    private $data = [];
    
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    // Called when accessing inaccessible properties
    public function __get($name) {
        return $this->data[$name] ?? null;
    }
    
    // Called when setting inaccessible properties
    public function __set($name, $value) {
        $this->data[$name] = $value;
    }
    
    // Called when isset() or empty() is used on inaccessible properties
    public function __isset($name) {
        return isset($this->data[$name]);
    }
    
    // Called when unset() is used on inaccessible properties
    public function __unset($name) {
        unset($this->data[$name]);
    }
    
    // Called when object is treated as a string
    public function __toString() {
        return "User: " . ($this->data['username'] ?? 'Unknown');
    }
    
    // Called when object is invoked as a function
    public function __invoke($action) {
        return "User {$this->data['username']} is performing: $action";
    }
    
    // Called when object is cloned
    public function __clone() {
        $this->data['id'] = uniqid();
    }
    
    // Called when object is serialized
    public function __sleep() {
        return ['data']; // Only serialize the data property
    }
    
    // Called when object is unserialized
    public function __wakeup() {
        // Reinitialize any resources
    }
    
    // Called when object is used in a string context (PHP 8.0+)
    public function __serialize() {
        return $this->data;
    }
    
    public function __unserialize($data) {
        $this->data = $data;
    }
}

$user = new User(['username' => 'john', 'email' => 'john@example.com']);

echo $user->username; // Calls __get()
$user->age = 25;      // Calls __set()
isset($user->age);   // Calls __isset()
unset($user->age);   // Calls __unset()

echo $user;           // Calls __toString()
echo $user('login');   // Calls __invoke()

$clonedUser = clone $user; // Calls __clone()
?>
```

### **Static Magic Methods**
```php
<?php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        // Private constructor to prevent direct instantiation
        $this->connection = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
    }
    
    // Called when accessing inaccessible static methods
    public static function __callStatic($name, $arguments) {
        if ($name === 'getInstance') {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        throw new BadMethodCallException("Static method $name does not exist");
    }
    
    // Called when accessing inaccessible instance methods
    public function __call($name, $arguments) {
        if (strpos($name, 'get') === 0) {
            $property = strtolower(substr($name, 3));
            return $this->connection->$property ?? null;
        }
        
        if (strpos($name, 'set') === 0) {
            $property = strtolower(substr($name, 3));
            $this->connection->$property = $arguments[0] ?? null;
            return $this;
        }
        
        throw new BadMethodCallException("Method $name does not exist");
    }
}

$db = Database::getInstance(); // Calls __callStatic()
echo $db->getHost();         // Calls __call()
?>
```

## 📦 Namespaces & Autoloading

### **Namespace Usage**
```php
<?php
namespace App\Controllers;

class UserController {
    public function index() {
        return "User controller index";
    }
    
    public function show($id) {
        return "Showing user $id";
    }
}

namespace App\Models;

class User {
    public $id;
    public $name;
    
    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }
}

namespace App\Services;

use App\Models\User;
use App\Controllers\UserController;

class UserService {
    public function getUser($id) {
        return new User($id, "User $id");
    }
    
    public function getController() {
        return new UserController();
    }
}

// Usage
namespace {
    use App\Services\UserService;
    use function App\Helpers\formatDate;
    
    $service = new UserService();
    $user = $service->getUser(1);
    $controller = $service->getController();
}
?>
```

### **Autoloading**
```php
<?php
// Simple autoloader
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    
    // Check if file exists
    if (file_exists($file)) {
        require_once $file;
    }
});

// PSR-4 autoloader
spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/src/';
    
    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Get the relative class name
    $relative_class = substr($class, $len);
    
    // Replace namespace separators with directory separators
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

// Usage
$user = new App\Controllers\UserController();
?>
```

## 🏭 Design Patterns

### **Singleton Pattern**
```php
<?php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $this->connection = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

$db = Database::getInstance();
?>
```

### **Factory Pattern**
```php
<?php
interface Logger {
    public function log($message);
}

class FileLogger implements Logger {
    private $file;
    
    public function __construct($file) {
        $this->file = $file;
    }
    
    public function log($message) {
        file_put_contents($this->file, $message . "\n", FILE_APPEND);
    }
}

class DatabaseLogger implements Logger {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function log($message) {
        $sql = "INSERT INTO logs (message, created_at) VALUES (?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$message]);
    }
}

class LoggerFactory {
    public static function create($type, $config = []) {
        switch ($type) {
            case 'file':
                return new FileLogger($config['file'] ?? 'app.log');
            case 'database':
                return new DatabaseLogger($config['pdo']);
            default:
                throw new InvalidArgumentException("Unknown logger type: $type");
        }
    }
}

// Usage
$fileLogger = LoggerFactory::create('file', ['file' => 'debug.log']);
$dbLogger = LoggerFactory::create('database', ['pdo' => $pdo]);
?>
```

### **Repository Pattern**
```php
<?php
interface UserRepositoryInterface {
    public function findById($id);
    public function findByEmail($email);
    public function save(User $user);
    public function delete(User $user);
}

class UserRepository implements UserRepositoryInterface {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        if ($data) {
            return new User($data['id'], $data['username'], $data['email']);
        }
        
        return null;
    }
    
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        $data = $stmt->fetch();
        
        if ($data) {
            return new User($data['id'], $data['username'], $data['email']);
        }
        
        return null;
    }
    
    public function save(User $user) {
        if ($user->getId()) {
            // Update
            $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user->getUsername(), $user->getEmail(), $user->getId()]);
        } else {
            // Insert
            $sql = "INSERT INTO users (username, email) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user->getUsername(), $user->getEmail()]);
            $user->setId($this->pdo->lastInsertId());
        }
        
        return $user;
    }
    
    public function delete(User $user) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user->getId()]);
    }
}
?>
```

## 🎯 Best Practices

### **1. Single Responsibility Principle**
```php
<?php
// Bad - Class doing too much
class UserManager {
    public function createUser($data) { /* ... */ }
    public function sendEmail($user) { /* ... */ }
    public function logActivity($user) { /* ... */ }
    public function validateUser($user) { /* ... */ }
}

// Good - Each class has one responsibility
class UserRepository {
    public function save(User $user) { /* ... */ }
}

class EmailService {
    public function sendWelcomeEmail(User $user) { /* ... */ }
}

class ActivityLogger {
    public function logUserActivity(User $user, $action) { /* ... */ }
}

class UserValidator {
    public function validate(User $user) { /* ... */ }
}
?>
```

### **2. Dependency Injection**
```php
<?php
// Bad - Hardcoded dependency
class UserService {
    public function createUser($data) {
        $db = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
        // Use database...
    }
}

// Good - Dependency injection
class UserService {
    private $database;
    private $logger;
    
    public function __construct(DatabaseInterface $database, LoggerInterface $logger) {
        $this->database = $database;
        $this->logger = $logger;
    }
    
    public function createUser($data) {
        // Use injected dependencies
        $this->database->insert('users', $data);
        $this->logger->log('User created');
    }
}
?>
```

### **3. Type Hinting**
```php
<?php
// PHP 7.4+ type declarations
class UserService {
    private UserRepositoryInterface $repository;
    private LoggerInterface $logger;
    
    public function __construct(
        UserRepositoryInterface $repository,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->logger = $logger;
    }
    
    public function createUser(array $userData): User {
        $user = new User($userData);
        $this->repository->save($user);
        $this->logger->log('User created');
        return $user;
    }
    
    public function findUser(int $id): ?User {
        return $this->repository->findById($id);
    }
}
?>
```

### **4. Error Handling**
```php
<?php
class UserService {
    public function createUser(array $userData): User {
        try {
            $this->validateUserData($userData);
            $user = new User($userData);
            $this->repository->save($user);
            
            return $user;
        } catch (ValidationException $e) {
            throw new UserServiceException('Invalid user data: ' . $e->getMessage(), 0, $e);
        } catch (DatabaseException $e) {
            throw new UserServiceException('Failed to save user: ' . $e->getMessage(), 0, $e);
        }
    }
    
    private function validateUserData(array $userData): void {
        if (empty($userData['username'])) {
            throw new ValidationException('Username is required');
        }
        
        if (!filter_var($userData['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email format');
        }
    }
}
?>
```

---

**📚 Resources:**
- [PHP OOP Manual](https://www.php.net/manual/en/book.oop.php)
- [PHP OOP Tutorial](https://www.phptutorial.net/php-oop/)
- [Design Patterns in PHP](https://refactoring.guru/design-patterns/php)
