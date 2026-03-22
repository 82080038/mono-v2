<?php
/**
 * KSP Lam Gabe Jaya - Application Bootstrap
 * Core Application Initialization
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not allowed');
}

// Define application constants
define('APP_START_TIME', microtime(true));
define('APP_MEMORY_START', memory_get_usage());

// Error reporting
if (APP_MODE === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
}

// Load core configuration
require_once APP_ROOT . '/core/Config/constants.php';

// Load core classes
require_once APP_ROOT . '/core/Database/Database.php';
require_once APP_ROOT . '/core/Auth/Auth.php';
require_once APP_ROOT . '/core/Cache/Cache.php';
require_once APP_ROOT . '/core/Logger/Logger.php';

// Load application classes
require_once APP_ROOT . '/app/Controllers/Controller.php';
require_once APP_ROOT . '/app/Models/Model.php';
require_once APP_ROOT . '/app/Services/Service.php';

// Autoloader
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $file = APP_ROOT . '/' . str_replace('\\', '/', $class) . '.php';
    
    // Check if file exists
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    // Try to find in app or core directories
    $paths = [
        'app/Controllers/' . $class . '.php',
        'app/Models/' . $class . '.php',
        'app/Services/' . $class . '.php',
        'app/Middleware/' . $class . '.php',
        'core/Database/' . $class . '.php',
        'core/Auth/' . $class . '.php',
        'core/Cache/' . $class . '.php',
        'core/Logger/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists(APP_ROOT . '/' . $path)) {
            require_once APP_ROOT . '/' . $path;
            return true;
        }
    }
    
    return false;
});

/**
 * Application Class
 * Main application controller
 */
class Application {
    private $router;
    private $request;
    private $response;
    
    public function __construct() {
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router();
        
        // Load routes
        $this->loadRoutes();
    }
    
    /**
     * Run the application
     */
    public function run() {
        try {
            // Handle CORS
            $this->handleCORS();
            
            // Route the request
            $route = $this->router->dispatch($this->request);
            
            // Execute middleware
            $this->executeMiddleware($route);
            
            // Execute controller
            $this->executeController($route);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Load routes
     */
    private function loadRoutes() {
        // Load web routes
        require_once APP_ROOT . '/app/Config/web.php';
        
        // Load API routes
        require_once APP_ROOT . '/app/Config/api.php';
    }
    
    /**
     * Handle CORS
     */
    private function handleCORS() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        if ($this->request->getMethod() === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
    
    /**
     * Execute middleware
     */
    private function executeMiddleware($route) {
        if (isset($route['middleware'])) {
            foreach ($route['middleware'] as $middleware) {
                $class = 'App\\Middleware\\' . $middleware;
                if (class_exists($class)) {
                    $instance = new $class();
                    $instance->handle($this->request, $this->response);
                }
            }
        }
    }
    
    /**
     * Execute controller
     */
    private function executeController($route) {
        $controller = 'App\\Controllers\\' . $route['controller'];
        $method = $route['method'];
        
        if (!class_exists($controller)) {
            throw new Exception("Controller not found: $controller");
        }
        
        $instance = new $controller();
        
        if (!method_exists($instance, $method)) {
            throw new Exception("Method not found: $method");
        }
        
        // Execute controller method
        $result = $instance->$method($this->request, $this->response);
        
        // Send response
        if ($result !== null) {
            $this->response->send($result);
        }
    }
    
    /**
     * Handle exceptions
     */
    private function handleException(Exception $e) {
        if (APP_MODE === 'development') {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]);
        }
        
        http_response_code(500);
    }
}

/**
 * Request Class
 * Handle HTTP requests
 */
class Request {
    private $method;
    private $uri;
    private $params;
    
    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = $this->getUri();
        $this->params = $this->getParams();
    }
    
    public function getMethod() {
        return $this->method;
    }
    
    public function getUri() {
        return $this->uri;
    }
    
    public function getParams() {
        return $this->params;
    }
    
    public function get($key, $default = null) {
        return $this->params[$key] ?? $default;
    }
    
    private function getUri() {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = strtok($uri, '?');
        $uri = trim($uri, '/');
        return $uri ?: '/';
    }
    
    private function getParams() {
        $params = [];
        
        switch ($this->method) {
            case 'GET':
                $params = $_GET;
                break;
            case 'POST':
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
}

/**
 * Response Class
 * Handle HTTP responses
 */
class Response {
    private $headers = [];
    
    public function setHeader($name, $value) {
        $this->headers[$name] = $value;
    }
    
    public function json($data, $statusCode = 200) {
        $this->setHeader('Content-Type', 'application/json');
        http_response_code($statusCode);
        
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        
        echo json_encode($data);
        exit;
    }
    
    public function view($template, $data = []) {
        extract($data);
        
        $templatePath = APP_ROOT . '/app/Views/' . $template . '.php';
        
        if (file_exists($templatePath)) {
            require_once $templatePath;
        } else {
            throw new Exception("View not found: $template");
        }
    }
    
    public function send($data) {
        if (is_array($data) || is_object($data)) {
            $this->json($data);
        } else {
            echo $data;
        }
    }
}

/**
 * Router Class
 * Handle URL routing
 */
class Router {
    private $routes = [];
    
    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }
    
    public function put($path, $handler) {
        $this->addRoute('PUT', $path, $handler);
    }
    
    public function delete($path, $handler) {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    public function addRoute($method, $path, $handler) {
        $this->routes[$method][$path] = $handler;
    }
    
    public function dispatch($request) {
        $method = $request->getMethod();
        $uri = $request->getUri();
        
        if (!isset($this->routes[$method])) {
            throw new Exception("Method not allowed: $method");
        }
        
        foreach ($this->routes[$method] as $path => $handler) {
            if ($this->matchPath($path, $uri)) {
                return $handler;
            }
        }
        
        throw new Exception("Route not found: $uri");
    }
    
    private function matchPath($route, $uri) {
        $route = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
        $route = str_replace('/', '\/', $route);
        $route = '/^' . $route . '$/';
        
        return preg_match($route, $uri, $matches);
    }
}
?>
