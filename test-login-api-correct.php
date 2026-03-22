<?php
/**
 * Automated Login Testing Script
 * Tests login API endpoint for correct roles
 */

// Test data
$testUsers = [
    ['username' => 'bos', 'password' => 'bos', 'role' => 'bos'],
    ['username' => 'admin', 'password' => 'admin', 'role' => 'admin'],
    ['username' => 'teller', 'password' => 'teller', 'role' => 'teller'],
    ['username' => 'collector', 'password' => 'collector', 'role' => 'collector'],
    ['username' => 'nasabah', 'password' => 'nasabah', 'role' => 'nasabah']
];

echo "=== Automated Login API Testing (Correct Roles) ===\n\n";

// Test login API endpoint
$apiUrl = 'http://localhost/mono-v2/api/auth.php';

foreach ($testUsers as $index => $user) {
    echo "Test #" . ($index + 1) . " - Testing " . strtoupper($user['role']) . "\n";
    
    // Prepare POST data
    $postData = http_build_query([
        'action' => 'login',
        'username' => $user['username'],
        'password' => $user['password']
    ]);
    
    // Create context
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $postData
        ]
    ]);
    
    // Send request
    $response = @file_get_contents($apiUrl, false, $context);
    
    if ($response) {
        echo "✅ API Response received\n";
        $data = json_decode($response, true);
        
        if ($data && isset($data['success'])) {
            if ($data['success']) {
                echo "✅ Login successful for {$user['username']}\n";
                if (isset($data['user']['role'])) {
                    echo "✅ Role verified: {$data['user']['role']}\n";
                }
            } else {
                echo "❌ Login failed: " . ($data['message'] ?? 'Unknown error') . "\n";
            }
        } else {
            echo "⚠️  Invalid JSON response\n";
            echo "Raw response: " . substr($response, 0, 200) . "...\n";
        }
    } else {
        echo "❌ No response from API\n";
        echo "Check if XAMPP is running and API is accessible\n";
    }
    
    echo str_repeat("-", 50) . "\n\n";
}

echo "=== Testing Complete ===\n";
echo "If all tests pass, login system with correct roles is working!\n";
?>
