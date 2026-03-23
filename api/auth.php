<?php
/**
 * KSP Lam Gabe Jaya - Enhanced Authentication API
 * Using OOP Auth class with enhanced security
 */

// Allow direct access
define('ALLOW_DIRECT_ACCESS', true);

// Load required files
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON and security headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Initialize authentication
try {
    $auth = new Auth();
    
    // Get request method and action
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    // If no action, return help message
    if (empty($action)) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Action parameter required. Available actions: login, logout, check_session',
            'available_actions' => ['login', 'logout', 'check_session']
        ]);
        exit;
    }
    
    // Parse input for POST requests
    if ($method === 'POST') {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        // Handle JSON content
        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true) ?: [];
            $_POST = array_merge($_POST, $data);
        }
    }
    
    // Route requests
    switch ($action) {
        case 'login':
            handleLogin($auth);
            break;
            
        case 'logout':
            handleLogout($auth);
            break;
            
        case 'check_session':
            handleSessionCheck($auth);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

/**
 * Handle login request
 */
function handleLogin($auth) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validate input
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username and password required']);
        return;
    }
    
    // Attempt login
    $result = $auth->login($username, $password, $remember);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => $result['user']
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }
}

/**
 * Handle logout request
 */
function handleLogout($auth) {
    $result = $auth->logout();
    
    echo json_encode($result);
}

/**
 * Handle session check request
 */
function handleSessionCheck($auth) {
    $authCheck = $auth->checkAuth();
    
    echo json_encode([
        'success' => true,
        'authenticated' => $authCheck['authenticated'],
        'user' => $authCheck['authenticated'] ? $authCheck['user'] : null,
        'reason' => $authCheck['authenticated'] ? null : $authCheck['reason']
    ]);
}
?>
