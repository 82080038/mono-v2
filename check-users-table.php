<?php
// Check users table structure
echo "Checking users table structure...\n";

try {
    $dsn = "mysql:host=localhost;dbname=gabe;charset=utf8mb4;unix_socket=/opt/lampp/var/mysql/mysql.sock";
    $pdo = new PDO($dsn, 'root', 'root', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Get table structure
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    
    echo "Users table structure:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    // Get sample data
    $stmt = $pdo->query("SELECT * FROM users LIMIT 1");
    $user = $stmt->fetch();
    
    if ($user) {
        echo "\nSample user data:\n";
        foreach ($user as $key => $value) {
            echo "- $key: " . (is_null($value) ? 'NULL' : $value) . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
