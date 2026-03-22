<?php
/**
 * batch-update-legacy.php - Updated with Security
 * Auto-generated security update
 */

// Security headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token");

// Handle preflight requests
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    exit(0);
}

// Prevent direct access
if (!defined("KSP_API_ACCESS")) {
    header("HTTP/1.0 403 Forbidden");
    exit("Direct access to this file is not allowed.");
}

// Include required files
require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/DatabaseHelper.php";
require_once __DIR__ . "/Logger.php";
require_once __DIR__ . "/DataValidator.php";
require_once __DIR__ . "/SecurityLogger.php";
require_once __DIR__ . "/AuthHelper.php";
require_once __DIR__ . "/SecurityHelper.php";
require_once __DIR__ . "/SecurityMiddleware.php";

// Initialize services
Logger::initialize();
$securityLogger = SecurityLogger::getInstance();
$db = DatabaseHelper::getInstance();
$validator = new DataValidator();

// Initialize response
$response = [
    "success" => false,
    "message" => "",
    "data" => null,
    "errors" => [],
    "timestamp" => date("Y-m-d H:i:s")
];

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=ksp_lamgabejaya_v2", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $action = $_REQUEST["action"] ?? "";
    
    if ($action === "request_reset") {
        $email = $_POST["email"] ?? "";
        
        if (empty($email)) {
            SecurityMiddleware::sendJSONResponse($response);
            exit();
        }
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE email = ? AND status = 'Active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            SecurityMiddleware::sendJSONResponse($response);
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
        
        SecurityMiddleware::sendJSONResponse($response);
        
    } elseif ($action === "reset_password") {
        $token = $_POST["token"] ?? "";
        $new_password = $_POST["new_password"] ?? "";
        
        if (empty($token) || empty($new_password)) {
            SecurityMiddleware::sendJSONResponse($response);
            exit();
        }
        
        // Validate token
        $stmt = $pdo->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expiry > NOW() AND used = 0");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reset) {
            SecurityMiddleware::sendJSONResponse($response);
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
        
        SecurityMiddleware::sendJSONResponse($response);
        
    } elseif ($action === "validate_token") {
        $token = $_REQUEST["token"] ?? "";
        
        if (empty($token)) {
            SecurityMiddleware::sendJSONResponse($response);
            exit();
        }
        
        $stmt = $pdo->prepare("SELECT user_id, expiry FROM password_resets WHERE token = ? AND expiry > NOW() AND used = 0");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reset) {
            SecurityMiddleware::sendJSONResponse($response);
        } else {
            SecurityMiddleware::sendJSONResponse($response);
        }
        
    } else {
        SecurityMiddleware::sendJSONResponse($response);
    }
    
} catch (Exception $e) {
    error_log("Password reset error: " . $e->getMessage());
    SecurityMiddleware::sendJSONResponse($response);
}
?>
