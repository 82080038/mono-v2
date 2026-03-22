<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once 'helpers.php';

try {
    $pdo = new PDO("mysql:host=localhost;unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=ksp_lamgabejaya_v2", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $action = $_REQUEST["action"] ?? "";
    
    if ($action === "request_reset") {
        $email = $_POST["email"] ?? "";
        
        if (empty($email)) {
            echo json_encode(["success" => false, "error" => "Email required"]);
            exit();
        }
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE email = ? AND status = 'Active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(["success" => false, "error" => "Email not found"]);
            exit();
        }
        
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store reset token
        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expiry, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user['id'], $token, $expiry]);
        
        // Log password reset request
        error_log("Password reset requested for user: " . $user['username'] . " (" . $email . ")");
        
        echo json_encode([
            "success" => true,
            "message" => "Password reset token generated",
            "token" => $token, // In production, send via email
            "expiry" => $expiry
        ]);
        
    } elseif ($action === "reset_password") {
        $token = $_POST["token"] ?? "";
        $new_password = $_POST["new_password"] ?? "";
        
        if (empty($token) || empty($new_password)) {
            echo json_encode(["success" => false, "error" => "Token and new password required"]);
            exit();
        }
        
        // Validate token
        $stmt = $pdo->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expiry > NOW() AND used = 0");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reset) {
            echo json_encode(["success" => false, "error" => "Invalid or expired token"]);
            exit();
        }
        
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$hashed_password, $reset['user_id']]);
        
        // Mark token as used
        $stmt = $pdo->prepare("UPDATE password_resets SET used = 1, used_at = NOW() WHERE token = ?");
        $stmt->execute([$token]);
        
        // Log password reset
        error_log("Password reset completed for user_id: " . $reset['user_id']);
        
        echo json_encode([
            "success" => true,
            "message" => "Password reset successfully"
        ]);
        
    } elseif ($action === "validate_token") {
        $token = $_REQUEST["token"] ?? "";
        
        if (empty($token)) {
            echo json_encode(["success" => false, "error" => "Token required"]);
            exit();
        }
        
        $stmt = $pdo->prepare("SELECT user_id, expiry FROM password_resets WHERE token = ? AND expiry > NOW() AND used = 0");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reset) {
            echo json_encode([
                "success" => true,
                "valid" => true,
                "expiry" => $reset['expiry']
            ]);
        } else {
            echo json_encode([
                "success" => true,
                "valid" => false
            ]);
        }
        
    } else {
        echo json_encode(["success" => false, "error" => "Invalid action"]);
    }
    
} catch (Exception $e) {
    error_log("Password reset error: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "error" => "System error occurred",
        "code" => 500
    ]);
}
?>
