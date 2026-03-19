<?php
/**
 * API Endpoint for Login
 * Simple API for development
 */

// Include security helper
require_once __DIR__ . '/../../security_fixes.php';

// Initialize security
SecurityHelper::init();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get and sanitize action
$action = SecurityHelper::sanitizeInput($_POST['action'] ?? $_GET['action'] ?? '');

// Handle login
if ($action === 'login' && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Accept both JSON and form data
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    
    // Sanitize and validate inputs
    $email = SecurityHelper::sanitizeInput($input['email'] ?? '');
    $password = SecurityHelper::sanitizeInput($input['password'] ?? '');
    
    // Validate CSRF token if provided
    if (isset($input['csrf_token'])) {
        if (!SecurityHelper::validateCSRFToken($input['csrf_token'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid CSRF token'
            ]);
            exit;
        }
    }
    
    // Test users for development
    $test_users = [
        'test_super_admin@lamabejaya.coop' => ['password' => 'password123', 'role' => 'super_admin', 'name' => 'Test Super Admin'],
        'test_admin@lamabejaya.coop' => ['password' => 'password123', 'role' => 'admin', 'name' => 'Test Admin'],
        'test_mantri@lamabejaya.coop' => ['password' => 'password123', 'role' => 'mantri', 'name' => 'Test Mantri'],
        'test_member@lamabejaya.coop' => ['password' => 'password123', 'role' => 'member', 'name' => 'Test Member'],
        'admin@lamabejaya.coop' => ['password' => 'admin123', 'role' => 'admin', 'name' => 'Administrator'],
        // New roles based on documentation analysis
        'test_kasir@lamabejaya.coop' => ['password' => 'password123', 'role' => 'kasir', 'name' => 'Test Kasir'],
        'test_teller@lamabejaya.coop' => ['password' => 'password123', 'role' => 'teller', 'name' => 'Test Teller'],
        'test_surveyor@lamabejaya.coop' => ['password' => 'password123', 'role' => 'surveyor', 'name' => 'Test Surveyor'],
        'test_collector@lamabejaya.coop' => ['password' => 'password123', 'role' => 'collector', 'name' => 'Test Collector'],
        // Additional roles from Admin Guide
        'test_manajer@lamabejaya.coop' => ['password' => 'password123', 'role' => 'manajer', 'name' => 'Test Manajer'],
        'test_akuntansi@lamabejaya.coop' => ['password' => 'password123', 'role' => 'akuntansi', 'name' => 'Test Akuntansi']
    ];
    
    // Log authentication attempt
    SecurityHelper::logAuthAttempt($email, false);
    
    // Debug logging
    error_log("Auth Debug: email=$email, password_length=" . strlen($password));
    error_log("Auth Debug: user_exists=" . (isset($test_users[$email]) ? 'yes' : 'no'));
    
    if (isset($test_users[$email]) && $test_users[$email]['password'] === $password) {
        $user = $test_users[$email];
        
        // Log successful authentication
        SecurityHelper::logAuthAttempt($email, true);
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'token' => 'dev_token_' . bin2hex(random_bytes(16)),
            'user' => [
                'id' => rand(1, 100),
                'name' => $user['name'],
                'email' => $email,
                'role' => $user['role']
            ]
        ]);
    } else {
        // Log failed authentication
        SecurityHelper::logAuthAttempt($email, false);
        
        echo json_encode([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
    }
} elseif ($action === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logout endpoint
    echo json_encode([
        'success' => true,
        'message' => 'Logout successful'
    ]);
} elseif ($action === 'validate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Token validation endpoint
    $input = json_decode(file_get_contents('php://input'), true);
    $token = SecurityHelper::sanitizeInput($input['token'] ?? '');
    
    if ($token && strpos($token, 'dev_token_') === 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Token valid',
            'valid' => true
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid token',
            'valid' => false
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Endpoint not found'
    ]);
}
?>
