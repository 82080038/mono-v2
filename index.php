<?php
/**
 * KSP Lam Gabe Jaya - Main Application Entry Point
 * Original application with fixes
 */

// Prevent direct access guard
define('IN_INDEX_PHP', true);

// Load constants
require_once __DIR__ . '/config/constants.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

// Check user role
function hasRole($role) {
    if (!isLoggedIn()) return false;
    $user = $_SESSION['user'];
    return $user['role'] === $role;
}

// Redirect to login if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /mono-v2/login.php');
        exit;
    }
}

// Get current page
$page = $_GET['page'] ?? 'dashboard';

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: /mono-v2/login.php');
    exit;
}

// Route to appropriate page
switch ($page) {
    case 'login':
        if (isLoggedIn()) {
            header('Location: /mono-v2/?page=dashboard');
            exit;
        }
        include __DIR__ . '/login.php';
        break;
        
    case 'dashboard':
        requireLogin();
        include __DIR__ . '/main.php';
        break;
        
    case 'members':
        requireLogin();
        if (!hasRole('admin') && !hasRole('manager')) {
            header('Location: /mono-v2/?page=dashboard');
            exit;
        }
        include __DIR__ . '/pages/admin/members.html';
        break;
        
    case 'accounts':
        requireLogin();
        include __DIR__ . '/pages/admin/accounts.html';
        break;
        
    case 'transactions':
        requireLogin();
        include __DIR__ . '/pages/admin/transactions.html';
        break;
        
    case 'loans':
        requireLogin();
        include __DIR__ . '/pages/admin/loans.html';
        break;
        
    case 'reports':
        requireLogin();
        include __DIR__ . '/pages/admin/reports.html';
        break;
        
    default:
        if (isLoggedIn()) {
            include __DIR__ . '/main.php';
        } else {
            include __DIR__ . '/login.php';
        }
        break;
}
?>
