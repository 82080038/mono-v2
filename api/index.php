<?php
/**
 * KSP Lam Gabe Jaya - API Router
 * Central API router for handling all API requests
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

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-Token');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// API Router Class
class APIRouter {
    private $routes = [];
    private $method;
    private $endpoint;
    private $params;
    
    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->endpoint = $this->getEndpoint();
        $this->params = $this->getParams();
        
        // Define API routes
        $this->defineRoutes();
    }
    
    /**
     * Get API endpoint from URL
     */
    private function getEndpoint() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = str_replace('/api/', '', $path);
        $path = str_replace('.php', '', $path);
        
        // Handle root API path
        if (empty($path) || $path === '/') {
            return 'index';
        }
        
        return $path;
    }
    
    /**
     * Get request parameters
     */
    private function getParams() {
        $params = [];
        
        switch ($this->method) {
            case 'GET':
                $params = $_GET;
                break;
            case 'POST':
                // Check if JSON content
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                if (strpos($contentType, 'application/json') !== false) {
                    $json = file_get_contents('php://input');
                    $params = json_decode($json, true) ?: [];
                } else {
                    $params = $_POST;
                }
                break;
            case 'PUT':
            case 'DELETE':
                $json = file_get_contents('php://input');
                $params = json_decode($json, true) ?: [];
                break;
        }
        
        return $params;
    }
    
    /**
     * Define API routes
     */
    private function defineRoutes() {
        $this->routes = [
            // Authentication
            'auth' => [
                'file' => 'auth.php',
                'methods' => ['POST']
            ],
            
            // Members
            'members' => [
                'file' => 'members.php',
                'methods' => ['GET', 'POST', 'PUT', 'DELETE']
            ],
            
            // Accounts
            'accounts' => [
                'file' => 'accounts.php',
                'methods' => ['GET', 'POST', 'PUT', 'DELETE']
            ],
            
            // Transactions
            'transactions' => [
                'file' => 'transactions.php',
                'methods' => ['GET', 'POST', 'PUT']
            ],
            
            // Loans
            'loans' => [
                'file' => 'loans.php',
                'methods' => ['GET', 'POST', 'PUT']
            ],
            
            // Reports
            'reports' => [
                'file' => 'reports.php',
                'methods' => ['GET']
            ],
            
            // Logout
            'logout' => [
                'file' => 'logout.php',
                'methods' => ['POST']
            ]
        ];
    }
    
    /**
     * Route request to appropriate handler
     */
    public function route() {
        try {
            // Check if endpoint exists
            if (!isset($this->routes[$this->endpoint])) {
                $this->sendError('Endpoint not found', 404);
            }
            
            $route = $this->routes[$this->endpoint];
            
            // Check if method is allowed
            if (!in_array($this->method, $route['methods'])) {
                $this->sendError('Method not allowed', 405);
            }
            
            // Include and execute the API file
            $apiFile = __DIR__ . '/' . $route['file'];
            if (file_exists($apiFile)) {
                include_once $apiFile;
            } else {
                $this->sendError('API file not found', 500);
            }
            
        } catch (Exception $e) {
            $this->sendError('Internal server error', 500, [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
    
    /**
     * Get current endpoint
     */
    public function getEndpoint() {
        return $this->endpoint;
    }
    
    /**
     * Send error response
     */
    private function sendError($message, $statusCode = 400, $data = null) {
        http_response_code($statusCode);
        
        $response = [
            'success' => false,
            'message' => $message,
            'status_code' => $statusCode,
            'endpoint' => $this->endpoint,
            'method' => $this->method
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response);
        exit;
    }
    
    /**
     * Get API info
     */
    public function getAPIInfo() {
        return [
            'name' => 'KSP Lam Gabe Jaya API',
            'version' => '1.0.0',
            'description' => 'Complete API system for KSP management',
            'endpoints' => array_keys($this->routes),
            'methods' => $this->routes,
            'base_url' => (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/api/',
            'documentation' => '/api/API_DOCUMENTATION.md'
        ];
    }
}

// Handle API request
if (basename($_SERVER['PHP_SELF']) === 'index.php') {
    $router = new APIRouter();
    
    // Handle API info request
    if (isset($_GET['info']) || $router->getEndpoint() === 'index') {
        echo json_encode([
            'success' => true,
            'message' => 'API Information',
            'data' => $router->getAPIInfo()
        ]);
        exit;
    }
    
    // Route the request
    $router->route();
}
?>
