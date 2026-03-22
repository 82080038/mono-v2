<?php
/**
 * KSP Lam Gabe Jaya - Authentication API
 * Working authentication endpoint
 */

// Allow direct access
define('ALLOW_DIRECT_ACCESS', true);

// Load constants
if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__ . '/..');
}

// Define constants if not already defined
if (!defined('DB_HOST')) {
    require_once APP_ROOT . '/core/Config/constants.php';
}

// Fallback constants
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'gabe');
    define('DB_USER', 'root');
    define('DB_PASSWORD', 'root');
    define('DB_CHARSET', 'utf8mb4');
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header
header('Content-Type: application/json');

// Handle requests
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
        // Parse input for POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            
            // Handle JSON content
            if (strpos($contentType, 'application/json') !== false) {
                $json = file_get_contents('php://input');
                $data = json_decode($json, true) ?: [];
                $_POST = array_merge($_POST, $data);
            }
            // Handle form-urlencoded content (already in $_POST)
            elseif (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
                // Data is already in $_POST, no need to parse
                // Just ensure we have the data we need
            }
        }

        // Handle login
        if (($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') && 
            (isset($_POST['username']) || isset($_GET['username'])) && 
            (isset($_POST['password']) || isset($_GET['password']))) {
            
            $username = $_POST['username'] ?? $_GET['username'];
            $password = $_POST['password'] ?? $_GET['password'];
            
            // Try database authentication first
            $user = authenticateWithDatabase($username, $password);
            
            if ($user) {
                // Create session
                $_SESSION['user'] = $user;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_time'] = time();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => $user,
                    'token' => session_id(),
                    'redirect' => '/mono-v2/?page=dashboard'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid username or password'
                ]);
            }
            exit;
        }
        
        // Handle session check (GET)
        elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'check_session') {
            $user = $_SESSION['user'] ?? null;
            
            echo json_encode([
                'success' => true,
                'authenticated' => ($user !== null),
                'user' => $user
            ]);
            exit;
        }
        
        // Handle logout
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
            session_destroy();
            
            echo json_encode([
                'success' => true,
                'message' => 'Logout successful',
                'redirect' => '/mono-v2/?page=login'
            ]);
            exit;
        }
        
        // Handle session check (POST)
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'check_session') {
            $user = $_SESSION['user'] ?? null;
            
            echo json_encode([
                'success' => true,
                'authenticated' => ($user !== null),
                'user' => $user
            ]);
            exit;
        }
        
        else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid request method or missing parameters'
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method. Use POST for login or GET/POST for session check'
        ]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
}

/**
 * Authenticate with database
 */
function authenticateWithDatabase($username, $password) {
    // Try database connection if constants are available
    if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASSWORD')) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            if (defined('DB_SOCKET')) {
                $dsn .= ";unix_socket=" . DB_SOCKET;
            }
            
            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            // Query user with role information
            $stmt = $pdo->prepare("
                SELECT u.id, u.username, u.full_name, u.email, u.status, u.last_login, u.created_at,
                       r.role_name, r.role_display_name, r.permissions
                FROM users u 
                JOIN role_master r ON u.role_id = r.id 
                WHERE u.username = :username AND u.status = 'active' AND r.is_active = TRUE
            ");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Get password for verification
                $pwdStmt = $pdo->prepare("SELECT password FROM users WHERE username = :username");
                $pwdStmt->execute(['username' => $username]);
                $pwdData = $pwdStmt->fetch();
                
                if ($pwdData && password_verify($password, $pwdData['password'])) {
                    // Update last login
                    $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                    $updateStmt->execute(['id' => $user['id']]);
                    
                    // Add role data to user object
                    $user['role'] = $user['role_name'];
                    $user['permissions'] = json_decode($user['permissions'], true);
                    
                    return $user;
                }
            }
            
        } catch (Exception $e) {
            // Log error but continue with fallback
            error_log("Database authentication failed: " . $e->getMessage());
        }
    }
    
    // Fallback authentication for testing (remove in production)
    $fallbackUsers = [
        'bos' => ['id' => 1, 'username' => 'bos', 'full_name' => 'Pemilik Koperasi', 'role' => 'bos', 'role_display_name' => 'Bos/Pemilik Koperasi'],
        'admin' => ['id' => 2, 'username' => 'admin', 'full_name' => 'Administrator', 'role' => 'admin', 'role_display_name' => 'Administrator'],
        'teller' => ['id' => 3, 'username' => 'teller', 'full_name' => 'Petugas Teller', 'role' => 'teller', 'role_display_name' => 'Petugas Teller'],
        'collector' => ['id' => 4, 'username' => 'collector', 'full_name' => 'Petugas Lapangan', 'role' => 'collector', 'role_display_name' => 'Petugas Lapangan'],
        'nasabah' => ['id' => 5, 'username' => 'nasabah', 'full_name' => 'Nasabah', 'role' => 'nasabah', 'role_display_name' => 'Nasabah/Anggota']
    ];
    
    if (isset($fallbackUsers[$username]) && $password === $username) {
        $user = $fallbackUsers[$username];
        $user['status'] = 'active';
        $user['email'] = $username . '@ksplamgabejaya.co.id';
        $user['last_login'] = date('Y-m-d H:i:s');
        $user['created_at'] = date('Y-m-d H:i:s');
        $user['permissions'] = [];
        return $user;
    }
    
    return null;
}
?>
