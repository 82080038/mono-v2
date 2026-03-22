<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

$action = $_REQUEST["action"] ?? "";

try {
    $pdo = new PDO("mysql:host=localhost;unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=ksp_lamgabejaya_v2", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($action === "login") {
        $username = $_POST["username"] ?? "";
        $password = $_POST["password"] ?? "";
        
        // Simple validation
        if (empty($username) || empty($password)) {
            echo json_encode([
                "success" => false,
                "error" => "Username and password required"
            ]);
            exit();
        }
        
        // Query user
        $stmt = $pdo->prepare("SELECT id, username, email, full_name, role, password FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
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
            echo json_encode([
                "success" => false,
                "error" => "Invalid credentials"
            ]);
        }
    } elseif ($action === "validate") {
        $token = $_REQUEST["token"] ?? "";
        
        echo json_encode([
            "success" => true,
            "valid" => !empty($token)
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Invalid action"
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
