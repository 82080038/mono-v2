<?php
/**
 * Test XAMPP Database Connection
 */

echo "=== XAMPP Database Test ===\n\n";

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=ksp_lamgabejaya_v2;charset=utf8mb4;unix_socket=/opt/lampp/var/mysql/mysql.sock",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Database connection successful!\n\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $result = $stmt->fetch();
    echo "📊 Users: " . $result['total'] . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM member_types");
    $result = $stmt->fetch();
    echo "📋 Member Types: " . $result['total'] . "\n";
    
    echo "\n✅ XAMPP database test completed!\n";
    
} catch (Exception $e) {
    echo "❌ Database test failed: " . $e->getMessage() . "\n";
}
?>
