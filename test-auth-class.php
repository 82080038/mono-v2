<?php
// Test Auth class
echo "Testing Auth class...\n";

// Define access flag
define('IN_DEBUG_PHP', true);

// Load required files
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Auth.php';

try {
    $auth = new Auth();
    echo "✅ Auth class instantiated successfully!\n";
    
    // Test admin login
    $result = $auth->login('admin', 'admin');
    if ($result['success']) {
        echo "✅ Admin login successful!\n";
        echo "User ID: " . $result['user']['id'] . "\n";
        echo "Username: " . $result['user']['username'] . "\n";
        echo "Role: " . $result['user']['role'] . "\n";
        echo "Status: " . $result['user']['status'] . "\n";
        echo "Permissions: " . json_encode($result['user']['permissions']) . "\n";
    } else {
        echo "❌ Admin login failed: " . $result['message'] . "\n";
    }
    
    // Test other users
    $users = ['bos', 'teller', 'collector', 'nasabah'];
    foreach ($users as $username) {
        $result = $auth->login($username, $username);
        if ($result['success']) {
            echo "✅ $username login successful!\n";
        } else {
            echo "❌ $username login failed: " . $result['message'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Auth class failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
