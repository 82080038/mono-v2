<?php
/**
 * Test Auth API Direct Connection
 */

header("Content-Type: application/json; charset=UTF-8");

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=ksp_lamgabejaya_v2;charset=utf8mb4",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Test query
    $stmt = $pdo->query("SELECT * FROM users WHERE username = 'admin'");
    $user = $stmt->fetch();
    
    if ($user && password_verify('password123', $user['password'])) {
        echo json_encode([
            'success' => true,
            'message' => 'Login successful - direct test',
            'user' => $user
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
