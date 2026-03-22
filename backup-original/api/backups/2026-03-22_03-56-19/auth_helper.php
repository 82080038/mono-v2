<?php
// Database connection helper
function getDatabaseConnection() {
    try {
        $pdo = new PDO("mysql:host=localhost;unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=ksp_lamgabejaya_v2", "root", "root");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (Exception $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

// Authentication helper
function validateToken($token) {
    if (empty($token)) {
        return null;
    }
    
    try {
        $pdo = getDatabaseConnection();
        $tokenParts = explode(':', base64_decode($token));
        $userId = $tokenParts[0] ?? 0;
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user;
    } catch (Exception $e) {
        return null;
    }
}

// Role-based access check
function checkRoleAccess($userRole, $allowedRoles) {
    return in_array($userRole, $allowedRoles);
}
?>
