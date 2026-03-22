<?php
/**
 * Minimal auth.php for testing
 */

// Allow direct access
define('ALLOW_DIRECT_ACCESS', true);

// Basic constants
define('APP_ROOT', __DIR__ . '/..');
define('DB_HOST', 'localhost');
define('DB_NAME', 'gabe');
define('DB_USER', 'root');
define('DB_PASSWORD', 'root');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Simple authentication
    if ($username === 'admin' && $password === 'password') {
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => 1,
                'username' => 'admin',
                'full_name' => 'Administrator',
                'role' => 'admin'
            ],
            'token' => session_id()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
    }
    exit;
}

// Session check
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'check_session') {
    echo json_encode([
        'success' => true,
        'authenticated' => isset($_SESSION['user']),
        'user' => $_SESSION['user'] ?? null
    ]);
    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'Invalid request method or missing parameters'
]);
?>
