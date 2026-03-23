<?php
// Test database connection with socket
echo "Testing database connection with socket...\n";

try {
    $dsn = "mysql:host=localhost;dbname=gabe;charset=utf8mb4;unix_socket=/opt/lampp/var/mysql/mysql.sock";
    $pdo = new PDO($dsn, 'root', 'root', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✅ Database connection successful!\n";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "📊 Users table count: " . $result['count'] . "\n";
    
    // Test admin user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ Admin user found!\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        echo "Status: " . $user['status'] . "\n";
        echo "Password Hash: " . (empty($user['password']) ? 'EMPTY' : 'SET') . "\n";
        
        // Test password verification
        if (password_verify('admin', $user['password'])) {
            echo "✅ Password verification successful!\n";
        } else {
            echo "❌ Password verification failed!\n";
            echo "Hashed password: " . $user['password'] . "\n";
        }
    } else {
        echo "❌ Admin user not found!\n";
        
        // Check tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "Available tables: " . implode(', ', $tables) . "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
?>
