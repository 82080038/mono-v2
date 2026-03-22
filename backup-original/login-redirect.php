<?php
/**
 * Login Redirect - Redirect users to proper dashboard based on role
 */

require_once __DIR__ . '/config/error-config.php';

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/AuthHelper.php';
require_once __DIR__ . '/DatabaseHelper.php';

// Check if user is logged in via token
$token = $_GET['token'] ?? '';
if ($token) {
    try {
        $userData = AuthHelper::validateJWTToken($token);
        if ($userData && isset($userData['role'])) {
            $role = $userData['role'];
            redirectBasedOnRole($role);
            exit();
        }
    } catch (Exception $e) {
        // Invalid token, continue with role parameter
    }
}

// If no token or invalid, use role parameter
$role = $_GET['role'] ?? 'member';
redirectBasedOnRole($role);

/**
 * Redirect user based on role
 */
function redirectBasedOnRole($role) {
    switch ($role) {
        case 'creator':
            header('Location: pages/dynamic-dashboard.html');
            break;
        case 'owner':
            header('Location: pages/dynamic-dashboard.html');
            break;
        case 'general_manager':
            header('Location: pages/dynamic-dashboard.html');
            break;
        case 'it_manager':
            header('Location: pages/dynamic-dashboard.html');
            break;
        case 'finance_manager':
            header('Location: pages/dynamic-dashboard.html');
            break;
        case 'supervisor':
            header('Location: pages/dynamic-dashboard.html');
            break;
        case 'teller':
            header('Location: pages/dynamic-dashboard.html');
            break;
        case 'field_officer':
            header('Location: pages/dynamic-dashboard.html');
            break;
        case 'member':
            header('Location: pages/dynamic-dashboard.html');
            break;
        default:
            header('Location: login.html');
            break;
    }
}
?>
