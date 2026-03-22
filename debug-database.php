<?php
/**
 * Database Connection Debug Script
 * Based on documentation best practices
 */

// Define access flag
define('IN_DEBUG_PHP', true);

// Load required files
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/Database.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>🔍 Database Connection Debug</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .info { color: blue; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
</style>";

// Test 1: Check constants
echo "<div class='section'>";
echo "<h2>📋 Database Constants Check</h2>";
echo "<pre>";
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "\n";
echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "\n";
echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'NOT DEFINED') . "\n";
echo "DB_PASS: " . (defined('DB_PASS') ? (empty(DB_PASS) ? 'EMPTY' : 'SET') : 'NOT DEFINED') . "\n";
echo "DB_PASSWORD: " . (defined('DB_PASSWORD') ? (empty(DB_PASSWORD) ? 'EMPTY' : 'SET') : 'NOT DEFINED') . "\n";
echo "DB_CHARSET: " . (defined('DB_CHARSET') ? DB_CHARSET : 'NOT DEFINED') . "\n";
echo "</pre>";
echo "</div>";

// Test 2: Direct PDO Connection
echo "<div class='section'>";
echo "<h2>🔗 Direct PDO Connection Test</h2>";
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "<p class='success'>✅ Direct PDO connection successful!</p>";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p class='info'>📊 Users table count: " . $result['count'] . "</p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Direct PDO connection failed: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 3: Database Class
echo "<div class='section'>";
echo "<h2>🏗️ Database Class Test</h2>";
try {
    $db = Database::getInstance();
    echo "<p class='success'>✅ Database class instantiated successfully!</p>";
    
    // Test fetchOne method
    $result = $db->fetchOne("SELECT COUNT(*) as count FROM users");
    echo "<p class='info'>📊 Users count via Database class: " . $result['count'] . "</p>";
    
    // Test specific user
    $user = $db->fetchOne("SELECT * FROM users WHERE username = ?", ['admin']);
    if ($user) {
        echo "<p class='success'>✅ Admin user found!</p>";
        echo "<pre>";
        echo "ID: " . $user['id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        echo "Status: " . $user['status'] . "\n";
        echo "Password Hash: " . (empty($user['password']) ? 'EMPTY' : 'SET') . "\n";
        echo "</pre>";
    } else {
        echo "<p class='error'>❌ Admin user not found!</p>";
        
        // Check if users table exists
        $tables = $db->fetchAll("SHOW TABLES");
        echo "<p class='info'>📋 Available tables:</p>";
        echo "<pre>";
        foreach ($tables as $table) {
            echo "- " . implode(", ", $table) . "\n";
        }
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Database class failed: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 4: Auth Class
echo "<div class='section'>";
echo "<h2>🔐 Auth Class Test</h2>";
try {
    $auth = new Auth();
    echo "<p class='success'>✅ Auth class instantiated successfully!</p>";
    
    // Test login
    $result = $auth->login('admin', 'admin');
    if ($result['success']) {
        echo "<p class='success'>✅ Admin login successful!</p>";
        echo "<pre>";
        echo "User ID: " . $result['user']['id'] . "\n";
        echo "Username: " . $result['user']['username'] . "\n";
        echo "Role: " . $result['user']['role'] . "\n";
        echo "</pre>";
    } else {
        echo "<p class='error'>❌ Admin login failed: " . $result['message'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Auth class failed: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 5: Session Check
echo "<div class='section'>";
echo "<h2>🔒 Session Check</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "Session Data: " . print_r($_SESSION, true) . "\n";
echo "</pre>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>🎯 Recommendations</h2>";
echo "<ul>";
echo "<li>If database connection fails, check MySQL service and credentials</li>";
echo "<li>If users table is empty, run the database setup script</li>";
echo "<li>If Auth class fails, check Database class implementation</li>";
echo "<li>If login fails, verify user data and password hashing</li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='login.php'>🔙 Back to Login</a></p>";
?>
