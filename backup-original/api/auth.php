<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once 'api-response.php';
require_once 'system-logger.php';
require_once 'security.php';

// Apply security middleware
SecurityMiddleware::apply([
    'rate_limit' => ['limit' => 100, 'window' => 3600],
    'allowed_methods' => ['GET', 'POST']
]);

try {
    $pdo = new PDO("mysql:host=localhost;unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=ksp_lamgabejaya_v2", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $action = Security::sanitize($_REQUEST["action"] ?? "", 'string');
    $username = Security::sanitize($_POST["username"] ?? "", 'string');
    $password = $_POST["password"] ?? ""; // Don't sanitize password
    
    // Detect suspicious activity
    Security::detectSuspiciousActivity('auth_request', [
        'action' => $action,
        'username' => $username
    ]);
    
    SystemLogger::apiRequest('/api/auth.php', $_SERVER['REQUEST_METHOD'], 200, [
        'action' => $action,
        'username' => $username
    ]);
    
    if ($action === "login") {
        // Validate input
        ApiValidator::required(['username', 'password'], $_POST);
        if (!empty(ApiValidator::getErrors())) {
            SystemLogger::warning('Login validation failed', ApiValidator::getErrors());
            ApiResponse::validationError(ApiValidator::getErrors());
        }
        
        // Query user from database
        $stmt = $pdo->prepare("SELECT id, username, email, full_name, role, password FROM users WHERE username = ? OR email = ? AND is_active = 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        SystemLogger::database('SELECT', 'users', ['username' => $username]);
        
        if ($user && Security::verifyPassword($password, $user['password'])) {
            // Reset failed login attempts
            Security::resetFailedLogin();
            // Update last login
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            SystemLogger::database('UPDATE', 'users', ['user_id' => $user['id']]);
            SystemLogger::userActivity($user['id'], 'login', ['username' => $user['username']]);
            
            // Generate token
            $token = base64_encode($user['id'] . ':' . $user['username'] . ':' . time());
            
            ApiResponse::success([
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role']
                ]
            ], 'Login successful');
        } else {
            // Increment failed login attempts
            Security::incrementFailedLogin();
            
            // Block IP after too many failed attempts
            if (Security::getFailedLoginCount() > 10) {
                Security::blockIp(null, 3600); // Block for 1 hour
            }
            
            SystemLogger::security('Login attempt failed', ['username' => $username, 'ip' => $_SERVER['REMOTE_ADDR']]);
            ApiResponse::error('Invalid credentials', 401);
        }
    } elseif ($action === "validate") {
        $token = $_REQUEST["token"] ?? "";
        
        if (empty($token)) {
            ApiResponse::error('Token required', 400);
        }
        
        // Simple token validation
        $isValid = !empty($token);
        
        ApiResponse::success(['valid' => $isValid], 'Token validation completed');
    } else {
        SystemLogger::warning('Invalid API action requested', ['action' => $action]);
        ApiResponse::error('Invalid action', 400);
    }
} catch (Exception $e) {
    SystemLogger::error('Auth system error', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    ApiResponse::serverError('System error occurred');
}
?>