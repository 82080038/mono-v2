<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing without constants check...\n";

// Define database constants manually
define('DB_HOST', 'localhost');
define('DB_NAME', 'gabe');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_PASSWORD', 'root');
define('DB_CHARSET', 'utf8mb4');

// Step 1: Test Database class
echo "Step 1: Testing Database class...\n";
try {
    require_once 'core/Database.php';
    $db = Database::getInstance();
    echo "✅ Database class loaded\n";
    
    // Test query
    $result = $db->fetchOne("SELECT COUNT(*) as count FROM users");
    echo "📊 Users count: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Database class failed: " . $e->getMessage() . "\n";
    exit;
}

// Step 2: Test Auth class
echo "Step 2: Testing Auth class...\n";
try {
    require_once 'core/Auth.php';
    $auth = new Auth();
    echo "✅ Auth class loaded\n";
} catch (Exception $e) {
    echo "❌ Auth class failed: " . $e->getMessage() . "\n";
    exit;
}

// Step 3: Test login
echo "Step 3: Testing login...\n";
try {
    $result = $auth->login('admin', 'admin');
    if ($result['success']) {
        echo "✅ Login successful!\n";
        echo "User ID: " . $result['user']['id'] . "\n";
        echo "Username: " . $result['user']['username'] . "\n";
        echo "Role: " . $result['user']['role'] . "\n";
        echo "Status: " . $result['user']['status'] . "\n";
    } else {
        echo "❌ Login failed: " . $result['message'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ Login failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "Test completed.\n";
?>
