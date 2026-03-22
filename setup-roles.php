<?php
// Check roles table
echo "Checking roles table...\n";

try {
    $dsn = "mysql:host=localhost;dbname=gabe;charset=utf8mb4;unix_socket=/opt/lampp/var/mysql/mysql.sock";
    $pdo = new PDO($dsn, 'root', 'root', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Check if roles table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'roles'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Roles table exists\n";
        
        // Get roles data
        $stmt = $pdo->query("SELECT * FROM roles");
        $roles = $stmt->fetchAll();
        
        echo "Available roles:\n";
        foreach ($roles as $role) {
            echo "- ID: " . $role['id'] . ", Role: " . $role['role_name'] . "\n";
        }
    } else {
        echo "❌ Roles table not found\n";
        
        // Create roles table
        $sql = "CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role_name VARCHAR(50) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        echo "✅ Created roles table\n";
        
        // Insert basic roles
        $roles = [
            ['role_name' => 'bos', 'description' => 'Pemilik Koperasi'],
            ['role_name' => 'admin', 'description' => 'Administrator'],
            ['role_name' => 'teller', 'description' => 'Teller'],
            ['role_name' => 'collector', 'description' => 'Collector'],
            ['role_name' => 'nasabah', 'description' => 'Nasabah']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO roles (role_name, description) VALUES (?, ?)");
        foreach ($roles as $role) {
            $stmt->execute([$role['role_name'], $role['description']]);
        }
        
        echo "✅ Inserted basic roles\n";
        
        // Update users table to map role_id to roles
        $roleMapping = [
            1 => 'bos',
            2 => 'admin', 
            3 => 'teller',
            4 => 'collector',
            5 => 'nasabah'
        ];
        
        foreach ($roleMapping as $roleId => $roleName) {
            $stmt = $pdo->prepare("UPDATE roles SET role_name = ? WHERE id = ?");
            $stmt->execute([$roleName, $roleId]);
        }
        
        echo "✅ Updated role mappings\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
