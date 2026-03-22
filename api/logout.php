<?php
/**
 * KSP Lam Gabe Jaya - Logout Handler
 * Secure logout with token blacklisting and session cleanup
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__ . '/..');
}

// Load required files
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/config/error-config.php';
require_once APP_ROOT . '/api/auth.php';

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========================================
// LOGOUT HANDLER CLASS
// ========================================

class LogoutHandler {
    private $auth;
    
    public function __construct() {
        $this->auth = new AuthSystem();
    }
    
    /**
     * Handle logout request
     */
    public function handleLogout() {
        try {
            // Get tokens from various sources
            $accessToken = $this->getAccessToken();
            $refreshToken = $this->getRefreshToken();
            
            // Get current user for logging
            $user = $this->auth->getCurrentUser();
            
            // Perform logout
            $result = $this->auth->logout($accessToken, $refreshToken);
            
            // Additional cleanup
            $this->cleanupSession();
            $this->cleanupCookies();
            
            // Log logout
            if ($user) {
                logInfo("User logged out: {$user['username']} (ID: {$user['id']})");
            } else {
                logInfo("Anonymous logout");
            }
            
            // Return success response
            echo json_encode([
                'success' => true,
                'message' => 'Logged out successfully',
                'redirect' => '/mono-v2/login.php'
            ]);
            
        } catch (Exception $e) {
            logError("Logout failed: " . $e->getMessage());
            
            // Still try to cleanup locally even if server-side logout fails
            $this->cleanupSession();
            $this->cleanupCookies();
            
            echo json_encode([
                'success' => true, // Still return success to avoid client issues
                'message' => 'Logged out (with warnings)',
                'redirect' => '/mono-v2/login.php'
            ]);
        }
    }
    
    /**
     * Handle session cleanup only (for AJAX requests)
     */
    public function handleSessionCleanup() {
        try {
            $this->cleanupSession();
            $this->cleanupCookies();
            
            echo json_encode([
                'success' => true,
                'message' => 'Session cleaned up'
            ]);
            
        } catch (Exception $e) {
            logError("Session cleanup failed: " . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'error' => 'Session cleanup failed'
            ]);
        }
    }
    
    /**
     * Handle logout from all devices
     */
    public function handleLogoutAllDevices() {
        try {
            $user = $this->auth->requireAuth();
            
            // Blacklist all user tokens (would need database implementation)
            $this->blacklistAllUserTokens($user['id']);
            
            // Perform logout
            $result = $this->auth->logout();
            
            // Additional cleanup
            $this->cleanupSession();
            $this->cleanupCookies();
            
            logInfo("User logged out from all devices: {$user['username']} (ID: {$user['id']})");
            
            echo json_encode([
                'success' => true,
                'message' => 'Logged out from all devices successfully',
                'redirect' => '/mono-v2/login.php'
            ]);
            
        } catch (Exception $e) {
            logError("Logout all devices failed: " . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'error' => 'Logout from all devices failed'
            ]);
        }
    }
    
    /**
     * Get access token from request
     */
    private function getAccessToken() {
        // Check Authorization header
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (strpos($authHeader, 'Bearer ') === 0) {
                return substr($authHeader, 7);
            }
        }
        
        // Check POST data
        if (isset($_POST['access_token'])) {
            return $_POST['access_token'];
        }
        
        // Check GET data
        if (isset($_GET['access_token'])) {
            return $_GET['access_token'];
        }
        
        // Check session
        if (isset($_SESSION['access_token'])) {
            return $_SESSION['access_token'];
        }
        
        return null;
    }
    
    /**
     * Get refresh token from request
     */
    private function getRefreshToken() {
        // Check POST data
        if (isset($_POST['refresh_token'])) {
            return $_POST['refresh_token'];
        }
        
        // Check GET data
        if (isset($_GET['refresh_token'])) {
            return $_GET['refresh_token'];
        }
        
        // Check cookie
        if (isset($_COOKIE['remember_token'])) {
            return $_COOKIE['remember_token'];
        }
        
        // Check session
        if (isset($_SESSION['refresh_token'])) {
            return $_SESSION['refresh_token'];
        }
        
        return null;
    }
    
    /**
     * Cleanup session data
     */
    private function cleanupSession() {
        // Unset all session variables
        $_SESSION = [];
        
        // Destroy session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
    }
    
    /**
     * Cleanup authentication cookies
     */
    private function cleanupCookies() {
        $cookies = [
            'remember_token',
            'auth_token',
            'access_token',
            'refresh_token',
            'user_session'
        ];
        
        foreach ($cookies as $cookie) {
            if (isset($_COOKIE[$cookie])) {
                setcookie($cookie, '', time() - 3600, '/', '', false, true);
                unset($_COOKIE[$cookie]);
            }
        }
    }
    
    /**
     * Blacklist all user tokens (placeholder for database implementation)
     */
    private function blacklistAllUserTokens($userId) {
        // This would typically involve:
        // 1. Updating user record to force token invalidation
        // 2. Blacklisting all active tokens for this user
        // 3. Notifying other systems to invalidate sessions
        
        try {
            // Update user's token version
            $db = $this->getDatabaseConnection();
            $stmt = $db->prepare("
                UPDATE users SET 
                    token_version = token_version + 1,
                    updated_at = NOW()
                WHERE id = :user_id
            ");
            $stmt->execute(['user_id' => $userId]);
            
        } catch (Exception $e) {
            logError("Failed to blacklist all user tokens: " . $e->getMessage());
        }
    }
    
    /**
     * Get database connection
     */
    private function getDatabaseConnection() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];
            
            return new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            throw new DatabaseException("Database connection failed");
        }
    }
}

// ========================================
// LOGOUT ROUTER
// ========================================

try {
    // Check if this is an AJAX request
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    $logoutHandler = new LogoutHandler();
    
    // Get action from request
    $action = $_POST['action'] ?? $_GET['action'] ?? 'logout';
    
    switch ($action) {
        case 'logout':
            $logoutHandler->handleLogout();
            break;
            
        case 'cleanup':
            $logoutHandler->handleSessionCleanup();
            break;
            
        case 'logout_all':
            $logoutHandler->handleLogoutAllDevices();
            break;
            
        default:
            throw new Exception('Unknown logout action: ' . $action);
    }
    
} catch (Exception $e) {
    logError("Logout handler error: " . $e->getMessage());
    
    // Always try to cleanup locally
    try {
        $logoutHandler = new LogoutHandler();
        $logoutHandler->cleanupSession();
        $logoutHandler->cleanupCookies();
    } catch (Exception $cleanupError) {
        logError("Emergency cleanup failed: " . $cleanupError->getMessage());
    }
    
    if (isset($isAjax) && $isAjax) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    } else {
        // For non-AJAX requests, redirect to login
        header('Location: /mono-v2/login.php');
        exit;
    }
}

// ========================================
// END OF LOGOUT HANDLER
// ========================================

?>
