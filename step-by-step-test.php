<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing step by step...\n";

// Step 1: Test constants
echo "Step 1: Testing constants...\n";
try {
    define('IN_DEBUG_PHP', true);
    require_once 'config/constants.php';
    echo "✅ Constants loaded\n";
} catch (Exception $e) {
    echo "❌ Constants failed: " . $e->getMessage() . "\n";
    exit;
}

// Step 2: Test Database class
echo "Step 2: Testing Database class...\n";
try {
    require_once 'core/Database.php';
    $db = Database::getInstance();
    echo "✅ Database class loaded\n";
} catch (Exception $e) {
    echo "❌ Database class failed: " . $e->getMessage() . "\n";
    exit;
}

// Step 3: Test Auth class
echo "Step 3: Testing Auth class...\n";
try {
    require_once 'core/Auth.php';
    $auth = new Auth();
    echo "✅ Auth class loaded\n";
} catch (Exception $e) {
    echo "❌ Auth class failed: " . $e->getMessage() . "\n";
    exit;
}

// Step 4: Test login
echo "Step 4: Testing login...\n";
try {
    $result = $auth->login('admin', 'admin');
    if ($result['success']) {
        echo "✅ Login successful!\n";
        print_r($result['user']);
    } else {
        echo "❌ Login failed: " . $result['message'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ Login failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "Test completed.\n";
?>
