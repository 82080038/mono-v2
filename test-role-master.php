<?php
/**
 * Test Role Master Integration
 */

echo "=== Testing Role Master Integration ===\n\n";

// Test login API endpoint
$apiUrl = 'http://localhost/mono-v2/api/auth.php';

// Test bos user
$postData = http_build_query([
    'action' => 'login',
    'username' => 'bos',
    'password' => 'bos'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $postData
    ]
]);

$response = @file_get_contents($apiUrl, false, $context);

if ($response) {
    $data = json_decode($response, true);
    
    if ($data && isset($data['success']) && $data['success']) {
        echo "✅ Login successful for BOS\n";
        echo "User Data:\n";
        echo "- ID: " . $data['user']['id'] . "\n";
        echo "- Username: " . $data['user']['username'] . "\n";
        echo "- Name: " . $data['user']['full_name'] . "\n";
        echo "- Role: " . $data['user']['role'] . "\n";
        echo "- Role Display Name: " . $data['user']['role_display_name'] . "\n";
        echo "- Email: " . $data['user']['email'] . "\n";
        echo "- Permissions: " . json_encode($data['user']['permissions'], JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "❌ Login failed\n";
    }
} else {
    echo "❌ No response\n";
}

echo "\n=== Database Verification ===\n";

// Test role data retrieval
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=gabe;charset=utf8mb4',
        'root',
        'root',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "\nUsers with Role Information:\n";
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.full_name, u.email,
               r.role_name, r.role_display_name, r.role_level
        FROM users u 
        JOIN role_master r ON u.role_id = r.id 
        ORDER BY r.role_level
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        echo "- {$user['username']} ({$user['role_name']}) - {$user['role_display_name']}\n";
    }
    
    echo "\nRole Master Data:\n";
    $stmt = $pdo->prepare("SELECT * FROM role_master ORDER BY role_level");
    $stmt->execute();
    $roles = $stmt->fetchAll();
    
    foreach ($roles as $role) {
        echo "- Level {$role['role_level']}: {$role['role_name']} - {$role['role_display_name']}\n";
        echo "  Permissions: " . $role['permissions'] . "\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\n=== Testing Complete ===\n";
?>
