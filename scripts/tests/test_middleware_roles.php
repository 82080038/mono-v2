<?php
/**
 * Test Script: Middleware getUserRoles() with multiple roles
 */
require_once __DIR__ . '/config/Config.php';
require_once __DIR__ . '/core/Middleware.php';

echo "=== Testing Middleware getUserRoles() ===\n\n";

// Simulate user payload (teller1 with ID 2)
$mockUser = [
    'user_id' => 2,
    'username' => 'teller1',
    'role' => 'Teller',
    'full_name' => 'Teller Satu',
    'exp' => time() + 3600
];

// Set current user in Middleware
$reflection = new ReflectionClass('Middleware');
$property = $reflection->getProperty('currentUser');
$property->setAccessible(true);
$property->setValue(null, $mockUser);

// Test getUserRoles()
echo "Testing getUserRoles() for user ID 2 (teller1):\n";
echo "Main role from users table: Teller\n";
echo "Additional roles from user_roles table:\n";

$pdo = Config::getDatabase();
$stmt = $pdo->prepare("
    SELECT r.role_code, r.role_name 
    FROM user_roles ur
    JOIN roles r ON r.id = ur.role_id
    WHERE ur.user_id = ? AND ur.is_active = 1 AND r.is_active = 1
");
$stmt->execute([2]);
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($roles as $r) {
    echo "  - {$r['role_code']}: {$r['role_name']}\n";
}

// Test getUserRoles() method
echo "\nCalling Middleware::getUserRoles():\n";
$reflectionMethod = $reflection->getMethod('getUserRoles');
$reflectionMethod->setAccessible(true);
$userRoles = $reflectionMethod->invoke(null);

echo "Result: " . implode(', ', $userRoles) . "\n";

// Expected: ['teller', 'collector']
$expected = ['teller', 'collector'];
if (sort($userRoles) === sort($expected)) {
    echo "✅ PASS: getUserRoles() returns expected roles\n";
} else {
    echo "❌ FAIL: Expected ['teller', 'collector'], got [" . implode(', ', $userRoles) . "]\n";
}

// Test requireRole() with multiple roles
echo "\nTesting requireRole(['collector']):\n";
try {
    $reflectionMethod = $reflection->getMethod('requireRole');
    $reflectionMethod->setAccessible(true);
    $reflectionMethod->invoke(null, ['collector']);
    echo "✅ PASS: User with collector role can access\n";
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
}

echo "\nTesting requireRole(['surveyor']):\n";
try {
    $reflectionMethod = $reflection->getMethod('requireRole');
    $reflectionMethod->setAccessible(true);
    $reflectionMethod->invoke(null, ['surveyor']);
    echo "❌ FAIL: Should not have access (user doesn't have surveyor role)\n";
} catch (Exception $e) {
    echo "✅ PASS: Correctly denied access - " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
