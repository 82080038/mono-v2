<?php
/**
 * KSP Lam Gabe Jaya - Authentication System
 * Comprehensive authentication and authorization system
 */

// Allow direct access for API requests and debugging
define('ALLOW_DIRECT_ACCESS', true);

// Load required files
if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__ . '/..');
}

// Define constants if not already defined
if (!defined('DB_HOST')) {
    require_once APP_ROOT . '/core/Config/constants.php';
}

// Load error config if exists
if (file_exists(APP_ROOT . '/core/Config/error-config.php')) {
    require_once APP_ROOT . '/core/Config/error-config.php';
}

// Fallback constants if still not defined
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

// ========================================
// AUTHENTICATION CONSTANTS
// ========================================

// Login attempt limits
if (!defined('MAX_LOGIN_ATTEMPTS')) {
    define('MAX_LOGIN_ATTEMPTS', 5);
}
if (!defined('LOGIN_ATTEMPT_TIMEOUT')) {
    define('LOGIN_ATTEMPT_TIMEOUT', 15); // minutes
}
if (!defined('LOGIN_LOCKOUT_TIME')) {
    define('LOGIN_LOCKOUT_TIME', 15); // minutes
}

// Database constants (if not defined)
if (!defined('DB_PERSISTENT')) {
    define('DB_PERSISTENT', true);
}
if (!defined('DB_FETCH_MODE')) {
    define('DB_FETCH_MODE', PDO::FETCH_ASSOC);
}
if (!defined('DB_EMULATE_PREPARES')) {
    define('DB_EMULATE_PREPARES', false);
}

// ========================================
// AUTHENTICATION SYSTEM CLASS
// ========================================

class AuthSystem {
    private $db;
    private $user;
    private $tokenBlacklist;
    
    public function __construct() {
        $this->db = $this->getDatabaseConnection();
        $this->tokenBlacklist = new TokenBlacklist();
    }
    
    /**
     * Get database connection
     */
    private function getDatabaseConnection() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_PERSISTENT => DB_PERSISTENT,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => DB_FETCH_MODE,
                PDO::ATTR_EMULATE_PREPARES => DB_EMULATE_PREPARES
            ];
            
            return new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new DatabaseException("Database connection failed", ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Authenticate user with username and password
     */
    public function authenticate($username, $password, $remember = false) {
        try {
            // Rate limiting check
            $this->checkRateLimit($username);
            
            // Get user from database
            $user = $this->getUserByUsername($username);
            
            if (!$user) {
                $this->recordFailedAttempt($username);
                throw new AuthenticationException("Username atau password salah");
            }
            
            // Verify password
            if (!$this->verifyPassword($password, $user['password'])) {
                $this->recordFailedAttempt($username);
                throw new AuthenticationException("Username atau password salah");
            }
            
            // Check if user is active
            if ($user['status'] !== 'active') {
                throw new AuthenticationException("Akun tidak aktif. Silakan hubungi admin.");
            }
            
            // Clear failed attempts
            $this->clearFailedAttempts($username);
            
            // Update last login
            $this->updateLastLogin($user['id']);
            
            // Generate tokens
            $tokens = $this->generateTokens($user);
            
            // Set session
            $this->setUserSession($user);
            
            // Set remember me cookie if requested
            if ($remember) {
                $this->setRememberMeCookie($tokens['refresh_token']);
            }
            
            logInfo("User authenticated: {$username}");
            
            return [
                'success' => true,
                'user' => $this->sanitizeUserData($user),
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'expires_in' => JWT_EXPIRY
            ];
            
        } catch (Exception $e) {
            error_log("Authentication failed for {$username}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Validate JWT token
     */
    public function validateToken($token) {
        try {
            // Check if token is blacklisted
            if ($this->tokenBlacklist->isBlacklisted($token)) {
                throw new AuthenticationException("Token has been revoked");
            }
            
            // Decode JWT token
            $payload = $this->decodeJWT($token);
            
            // Check token expiration
            if ($payload['exp'] < time()) {
                throw new AuthenticationException("Token has expired");
            }
            
            // Get user from database
            $user = $this->getUserById($payload['user_id']);
            
            if (!$user || $user['status'] !== 'active') {
                throw new AuthenticationException("Invalid user");
            }
            
            // Check if token was issued before last password change
            if (isset($payload['iat']) && $user['password_changed_at'] && 
                $payload['iat'] < strtotime($user['password_changed_at'])) {
                throw new AuthenticationException("Token expired due to password change");
            }
            
            return $this->sanitizeUserData($user);
            
        } catch (Exception $e) {
            error_log("Token validation failed: " . $e->getMessage());
            throw new AuthenticationException("Invalid token");
        }
    }
    
    /**
     * Refresh access token
     */
    public function refreshToken($refreshToken) {
        try {
            // Validate refresh token
            $payload = $this->decodeJWT($refreshToken);
            
            if ($payload['type'] !== 'refresh') {
                throw new AuthenticationException("Invalid refresh token");
            }
            
            // Get user
            $user = $this->getUserById($payload['user_id']);
            
            if (!$user || $user['status'] !== 'active') {
                throw new AuthenticationException("Invalid user");
            }
            
            // Generate new tokens
            $tokens = $this->generateTokens($user);
            
            // Blacklist old refresh token
            $this->tokenBlacklist->blacklist($refreshToken);
            
            return [
                'success' => true,
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'expires_in' => JWT_EXPIRY
            ];
            
        } catch (Exception $e) {
            error_log("Token refresh failed: " . $e->getMessage());
            throw new AuthenticationException("Token refresh failed");
        }
    }
    
    /**
     * Logout user
     */
    public function logout($accessToken = null, $refreshToken = null) {
        try {
            // Blacklist tokens if provided
            if ($accessToken) {
                $this->tokenBlacklist->blacklist($accessToken);
            }
            
            if ($refreshToken) {
                $this->tokenBlacklist->blacklist($refreshToken);
            }
            
            // Clear session
            session_destroy();
            
            // Clear remember me cookie
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            
            logInfo("User logged out");
            
            return ['success' => true, 'message' => 'Logged out successfully'];
            
        } catch (Exception $e) {
            error_log("Logout failed: " . $e->getMessage());
            throw new AuthenticationException("Logout failed");
        }
    }
    
    /**
     * Check if user has permission
     */
    public function hasPermission($userId, $resource, $action = PERMISSION_READ) {
        try {
            $user = $this->getUserById($userId);
            
            if (!$user) {
                return false;
            }
            
            // Super admin has all permissions
            if ($user['role'] <= ROLE_SUPER_ADMIN) {
                return true;
            }
            
            // Check role-based permissions
            return $this->checkRolePermission($user['role'], $resource, $action);
            
        } catch (Exception $e) {
            error_log("Permission check failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get current authenticated user
     */
    public function getCurrentUser() {
        if ($this->user) {
            return $this->user;
        }
        
        // Check session
        if (isset($_SESSION['user'])) {
            $this->user = $_SESSION['user'];
            return $this->user;
        }
        
        // Check token
        $token = $this->getTokenFromRequest();
        if ($token) {
            $this->user = $this->validateToken($token);
            return $this->user;
        }
        
        return null;
    }
    
    /**
     * Require authentication
     */
    public function requireAuth() {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            throw new AuthenticationException("Authentication required");
        }
        
        return $user;
    }
    
    /**
     * Require specific role
     */
    public function requireRole($requiredRole) {
        $user = $this->requireAuth();
        
        if ($user['role'] > $requiredRole) {
            throw new AuthorizationException("Insufficient privileges");
        }
        
        return $user;
    }
    
    /**
     * Get user by username
     */
    private function getUserByUsername($username) {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE username = :username AND status = 'active'
        ");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }
    
    /**
     * Get user by ID
     */
    private function getUserById($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE id = :user_id AND status = 'active'
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }
    
    /**
     * Verify password
     */
    private function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Hash password
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 1
        ]);
    }
    
    /**
     * Generate JWT tokens
     */
    private function generateTokens($user) {
        $now = time();
        
        // Access token (short-lived)
        $accessPayload = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'iat' => $now,
            'exp' => $now + JWT_EXPIRY,
            'type' => 'access'
        ];
        
        // Refresh token (long-lived)
        $refreshPayload = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'iat' => $now,
            'exp' => $now + JWT_REFRESH_EXPIRY,
            'type' => 'refresh'
        ];
        
        return [
            'access_token' => $this->encodeJWT($accessPayload),
            'refresh_token' => $this->encodeJWT($refreshPayload)
        ];
    }
    
    /**
     * Encode JWT
     */
    private function encodeJWT($payload) {
        $header = base64_encode(json_encode(['alg' => JWT_ALGORITHM, 'typ' => 'JWT']));
        $payload = base64_encode(json_encode($payload));
        $signature = base64_encode(hash_hmac('sha256', $header . '.' . $payload, JWT_SECRET_KEY, true));
        
        return $header . '.' . $payload . '.' . $signature;
    }
    
    /**
     * Decode JWT
     */
    private function decodeJWT($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            throw new AuthenticationException("Invalid token format");
        }
        
        $header = json_decode(base64_decode($parts[0]), true);
        $payload = json_decode(base64_decode($parts[1]), true);
        $signature = $parts[2];
        
        if (!$header || !$payload) {
            throw new AuthenticationException("Invalid token structure");
        }
        
        // Verify signature
        $expectedSignature = base64_encode(hash_hmac('sha256', $parts[0] . '.' . $parts[1], JWT_SECRET_KEY, true));
        
        if (!hash_equals($expectedSignature, $signature)) {
            throw new AuthenticationException("Invalid token signature");
        }
        
        return $payload;
    }
    
    /**
     * Set user session
     */
    private function setUserSession($user) {
        $_SESSION['user'] = $this->sanitizeUserData($user);
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Set remember me cookie
     */
    private function setRememberMeCookie($refreshToken) {
        setcookie('remember_token', $refreshToken, time() + JWT_REFRESH_EXPIRY, '/', '', false, true);
    }
    
    /**
     * Get token from request
     */
    private function getTokenFromRequest() {
        // Check Authorization header
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (strpos($authHeader, 'Bearer ') === 0) {
                return substr($authHeader, 7);
            }
        }
        
        // Check POST data
        if (isset($_POST['token'])) {
            return $_POST['token'];
        }
        
        // Check GET data
        if (isset($_GET['token'])) {
            return $_GET['token'];
        }
        
        // Check cookie
        if (isset($_COOKIE['remember_token'])) {
            return $_COOKIE['remember_token'];
        }
        
        return null;
    }
    
    /**
     * Sanitize user data for output
     */
    private function sanitizeUserData($user) {
        unset($user['password']);
        unset($user['password_changed_at']);
        
        return $user;
    }
    
    /**
     * Update last login
     */
    private function updateLastLogin($userId) {
        $stmt = $this->db->prepare("
            UPDATE users SET 
                last_login = NOW(),
                updated_at = NOW()
            WHERE id = :user_id
        ");
        $stmt->execute([
            'user_id' => $userId
        ]);
    }
    
    /**
     * Check rate limiting
     */
    private function checkRateLimit($username) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as attempts FROM login_attempts 
            WHERE username = :username AND attempt_time > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)
        ");
        $stmt->execute([
            'username' => $username,
            'minutes' => LOGIN_ATTEMPT_TIMEOUT
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['attempts'] >= MAX_LOGIN_ATTEMPTS) {
            throw new RateLimitException("Too many login attempts. Please try again later.", LOGIN_LOCKOUT_TIME);
        }
        return $result['attempts'] >= MAX_LOGIN_ATTEMPTS;
    }

    /**
     * Record failed attempt
    // ... (rest of the code remains the same)
     */
    private function recordFailedAttempt($username) {
        $stmt = $this->db->prepare("
            INSERT INTO login_attempts (username, ip_address, attempt_time) 
            VALUES (:username, :ip, NOW())
        ");
        $stmt->execute([
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ]);
    }
    
    /**
     * Clear failed attempts
     */
    private function clearFailedAttempts($username) {
        $stmt = $this->db->prepare("
            DELETE FROM login_attempts WHERE username = :username
        ");
        $stmt->execute(['username' => $username]);
    }
    
    /**
     * Check role permission
     */
    private function checkRolePermission($role, $resource, $action) {
        // Simplified permission check - can be enhanced with database-driven permissions
        $permissions = [
            ROLE_ADMIN => ['*'],
            ROLE_MANAGER => ['members', 'loans', 'savings', 'reports'],
            ROLE_SUPERVISOR => ['members', 'loans', 'savings'],
            ROLE_TELLER => ['members', 'transactions'],
            ROLE_STAFF => ['members', 'transactions'],
            ROLE_MEMBER => ['profile', 'savings', 'loans']
        ];
        
        $rolePermissions = $permissions[$role] ?? [];
        
        // Wildcard permission
        if (in_array('*', $rolePermissions)) {
            return true;
        }
        
        // Resource-specific permission
        if (in_array($resource, $rolePermissions)) {
            return true;
        }
        
        return false;
    }
}

// ========================================
// TOKEN BLACKLIST CLASS
// ========================================

class TokenBlacklist {
    private $db;
    
    public function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];
            
            $this->db = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            error_log("Blacklist database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Add token to blacklist
     */
    public function blacklist($token) {
        if (!$this->db) return;
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO token_blacklist (token, expires_at, created_at) 
                VALUES (:token, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())
            ");
            $stmt->execute(['token' => $token]);
        } catch (PDOException $e) {
            error_log("Failed to blacklist token: " . $e->getMessage());
        }
    }
    
    /**
     * Check if token is blacklisted
     */
    public function isBlacklisted($token) {
        if (!$this->db) return false;
        
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM token_blacklist 
                WHERE token = :token AND expires_at > NOW()
            ");
            $stmt->execute(['token' => $token]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Failed to check blacklist: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean expired tokens
     */
    public function cleanup() {
        if (!$this->db) return;
        
        try {
            $stmt = $this->db->prepare("
                DELETE FROM token_blacklist WHERE expires_at <= NOW()
            ");
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Failed to cleanup blacklist: " . $e->getMessage());
        }
    }
}

// ========================================
// AUTHENTICATION HELPERS
// ========================================

/**
 * Get current authenticated user
 */
function getCurrentUser() {
    static $auth = null;
    if ($auth === null) {
        $auth = new AuthSystem();
    }
    return $auth->getCurrentUser();
}

/**
 * Require authentication
 */
function requireAuth() {
    static $auth = null;
    if ($auth === null) {
        $auth = new AuthSystem();
    }
    return $auth->requireAuth();
}

/**
 * Require specific role
 */
function requireRole($role) {
    static $auth = null;
    if ($auth === null) {
        $auth = new AuthSystem();
    }
    return $auth->requireRole($role);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return getCurrentUser() !== null;
}

/**
 * Get user role
 */
function getUserRole() {
    $user = getCurrentUser();
    return $user ? $user['role'] : null;
}

/**
 * Check if current user has permission
 */
if (!function_exists('hasPermission')) {
    function hasPermission($resource, $action = PERMISSION_READ) {
        $user = getCurrentUser();
        if (!$user) return false;
    
        static $auth = null;
        if ($auth === null) {
            $auth = new AuthSystem();
        }
        
        return $auth->hasPermission($user['id'], $resource, $action);
    }
}

// ========================================
// END OF AUTHENTICATION SYSTEM
// ========================================

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');

    try {
        // Parse JSON input for POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'application/json') !== false) {
                $json = file_get_contents('php://input');
                $data = json_decode($json, true) ?: [];
                // Merge JSON data with POST data
                $_POST = array_merge($_POST, $data);
            }
        }

        $auth = new AuthSystem();

        // Handle login - support both POST and GET for testing
        if (($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') && 
            (isset($_POST['username']) || isset($_GET['username'])) && 
            (isset($_POST['password']) || isset($_GET['password']))) {
            
            $username = $_POST['username'] ?? $_GET['username'];
            $password = $_POST['password'] ?? $_GET['password'];
            
            $user = $auth->authenticate($username, $password);

            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => $user,
                'token' => session_id(),
                'redirect' => '/mono-v2/public/dashboard'
            ]);
            exit;
        }
        // Handle session check (GET)
        elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'check_session') {
            $user = $auth->getCurrentUser();
            
            echo json_encode([
                'success' => true,
                'authenticated' => ($user !== null),
                'user' => $user
            ]);
            exit;
        }
        // Handle session check (POST)
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'check_session') {
            $user = $auth->getCurrentUser();
            
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
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Use POST for login or GET/POST for session check'
    ]);
    exit;
}
?>
