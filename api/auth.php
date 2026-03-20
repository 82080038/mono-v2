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
    
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";
    
    if ($username === "admin" && $password === "admin123") {
        echo json_encode([
            "success" => true,
            "token" => "admin-token",
            "user" => ["id" => 1, "username" => "admin", "role" => "admin"]
        ]);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid credentials"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>