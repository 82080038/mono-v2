<?php
/**
 * KSP Lam Gabe Jaya - API Base Class
 * Base API functionality with common methods
 */

// Allow direct access for API requests
define('ALLOW_DIRECT_ACCESS', true);

// Load required files
define('APP_ROOT', __DIR__ . '/..');
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/config/error-config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========================================
// API BASE CLASS
// ========================================

abstract class BaseAPI {
    protected $db;
    protected $method;
    protected $endpoint;
    protected $params;
    protected $user;
    
    public function __construct() {
        $this->db = $this->getDatabaseConnection();
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->endpoint = $this->getEndpoint();
        $this->params = $this->getParams();
        $this->user = $this->getCurrentUser();
    }
    
    /**
     * Get database connection
     */
    protected function getDatabaseConnection() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            return new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            $this->sendError('Database connection failed', 500);
        }
    }
    
    /**
     * Get API endpoint
     */
    protected function getEndpoint() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return basename($path, '.php');
    }
    
    /**
     * Get request parameters
     */
    protected function getParams() {
        $params = [];
        
        switch ($this->method) {
            case 'GET':
                $params = $_GET;
                break;
            case 'POST':
                $params = $_POST;
                break;
            case 'PUT':
            case 'DELETE':
                parse_str(file_get_contents('php://input'), $params);
                break;
        }
        
        return $params;
    }
    
    /**
     * Get current user
     */
    protected function getCurrentUser() {
        if (isset($_SESSION['user'])) {
            return $_SESSION['user'];
        }
        
        // Check for token in headers
        $token = $this->getTokenFromHeader();
        if ($token) {
            return $this->validateToken($token);
        }
        
        return null;
    }
    
    /**
     * Get token from header
     */
    protected function getTokenFromHeader() {
        $headers = getallheaders();
        return $headers['Authorization'] ?? $headers['X-Auth-Token'] ?? null;
    }
    
    /**
     * Validate token
     */
    protected function validateToken($token) {
        // Simple token validation (enhance with JWT in production)
        if (isset($_SESSION['auth_token']) && $_SESSION['auth_token'] === $token) {
            return $_SESSION['user'] ?? null;
        }
        return null;
    }
    
    /**
     * Require authentication
     */
    protected function requireAuth() {
        if (!$this->user) {
            $this->sendError('Authentication required', 401);
        }
    }
    
    /**
     * Require specific role
     */
    protected function requireRole($requiredRole) {
        if (!$this->user || $this->user['role'] !== $requiredRole) {
            $this->sendError('Insufficient permissions', 403);
        }
    }
    
    /**
     * Send JSON response
     */
    protected function sendResponse($data, $statusCode = 200) {
        $this->setHeaders();
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
    
    /**
     * Send error response
     */
    protected function sendError($message, $statusCode = 400, $data = null) {
        $response = [
            'success' => false,
            'message' => $message,
            'status_code' => $statusCode
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $this->sendResponse($response, $statusCode);
    }
    
    /**
     * Send success response
     */
    protected function sendSuccess($message, $data = null, $statusCode = 200) {
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $this->sendResponse($response, $statusCode);
    }
    
    /**
     * Set common headers
     */
    protected function setHeaders() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-Token');
        
        if ($this->method === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
    
    /**
     * Validate required parameters
     */
    protected function validateRequired($required) {
        $missing = [];
        foreach ($required as $field) {
            if (!isset($this->params[$field]) || empty($this->params[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            $this->sendError('Missing required parameters: ' . implode(', ', $missing));
        }
    }
    
    /**
     * Sanitize input
     */
    protected function sanitize($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitize'], $input);
        }
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate email
     */
    protected function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate date
     */
    protected function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Validate numeric
     */
    protected function validateNumeric($value) {
        return is_numeric($value) && $value >= 0;
    }
    
    /**
     * Pagination helper
     */
    protected function getPaginationParams($defaultLimit = 20, $maxLimit = 100) {
        $page = max(1, intval($this->params['page'] ?? 1));
        $limit = min($maxLimit, max(1, intval($this->params['limit'] ?? $defaultLimit)));
        $offset = ($page - 1) * $limit;
        
        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset
        ];
    }
    
    /**
     * Build pagination response
     */
    protected function buildPaginationResponse($items, $total, $pagination) {
        return [
            'items' => $items,
            'pagination' => [
                'page' => $pagination['page'],
                'limit' => $pagination['limit'],
                'total' => $total,
                'pages' => ceil($total / $pagination['limit']),
                'has_next' => ($pagination['page'] * $pagination['limit']) < $total,
                'has_prev' => $pagination['page'] > 1
            ]
        ];
    }
    
    /**
     * Log API activity
     */
    protected function logActivity($action, $details = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO audit_logs (user_id, action, table_name, record_id, new_values, ip_address, user_agent)
                VALUES (:user_id, :action, :table_name, :record_id, :new_values, :ip_address, :user_agent)
            ");
            
            $stmt->execute([
                'user_id' => $this->user['id'] ?? null,
                'action' => $action,
                'table_name' => $this->endpoint,
                'record_id' => $this->params['id'] ?? null,
                'new_values' => json_encode($details),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
        } catch (Exception $e) {
            // Log error but don't break API response
            error_log("API Activity Log Error: " . $e->getMessage());
        }
    }
    
    /**
     * Abstract method for processing requests
     */
    abstract protected function processRequest();
    
    /**
     * Handle request
     */
    public function handleRequest() {
        try {
            $this->processRequest();
        } catch (Exception $e) {
            $this->sendError('Internal server error', 500, [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
}
?>
