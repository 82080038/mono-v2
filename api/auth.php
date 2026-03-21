<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=ksp_lamgabejaya_v2", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $action = $_REQUEST["action"] ?? "";
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";
    
    if ($action === "login") {
        // Query user from database
        $stmt = $pdo->prepare("SELECT id, username, email, full_name, role, password FROM users WHERE username = ? OR email = ? AND is_active = 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            // Generate token
            $token = base64_encode($user['id'] . ':' . $user['username'] . ':' . time());
            
            echo json_encode([
                "success" => true,
                "token" => $token,
                "user" => [
                    "id" => $user['id'],
                    "username" => $user['username'],
                    "email" => $user['email'],
                    "full_name" => $user['full_name'],
                    "role" => $user['role']
                ]
            ]);
        } else {
            echo json_encode(["success" => false, "error" => "Invalid credentials"]);
        }
    } elseif ($action === "validate") {
        $token = $_GET["token"] ?? "";
        // Simple token validation
        echo json_encode(["success" => true, "valid" => !empty($token)]);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid action"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>