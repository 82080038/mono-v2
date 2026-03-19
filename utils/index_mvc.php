<?php
/**
 * MVC Application Entry Point
 * Bootstrap aplikasi dengan MVC pattern
 */

// Load security fixes
require_once 'security_fixes.php';

// Load core classes
require_once 'app/core/Database.php';
require_once 'app/core/Model.php';
require_once 'app/core/View.php';
require_once 'app/core/Controller.php';
require_once 'app/core/Router.php';
require_once 'app/core/Validator.php';

// Load middleware
require_once 'app/middleware/AuthMiddleware.php';
require_once 'app/middleware/CSRFMiddleware.php';

// Load controllers
require_once 'app/controllers/HomeController.php';

// Load models
require_once 'app/models/User.php';

// Initialize router
$router = new Router();

// Define routes
$router->get('/', 'HomeController', 'index');
$router->get('/dashboard', 'HomeController', 'dashboard');
$router->get('/profile', 'HomeController', 'profile');
$router->post('/profile', 'HomeController', 'profile');
$router->get('/settings', 'HomeController', 'settings');
$router->post('/settings', 'HomeController', 'settings');
$router->get('/logout', 'HomeController', 'logout');

// API routes
$router->get('/api/dashboard', 'HomeController', 'apiDashboard');
$router->get('/api/notifications', 'HomeController', 'apiNotifications');
$router->get('/api/search', 'HomeController', 'apiSearch');

// Dispatch request
try {
    $requestUri = $_SERVER['REQUEST_URI'];
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    
    $router->dispatch($requestUri, $requestMethod);
} catch (Exception $e) {
    // Handle exceptions
    error_log('Router error: ' . $e->getMessage());
    
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        // AJAX request - return JSON error
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred',
            'error' => $_ENV['APP_DEBUG'] ? $e->getMessage() : 'Internal server error'
        ]);
    } else {
        // Regular request - show error page
        http_response_code(500);
        echo '<h1>500 Internal Server Error</h1>';
        echo '<p>Something went wrong. Please try again later.</p>';
        
        if ($_ENV['APP_DEBUG']) {
            echo '<p><strong>Error:</strong> ' . $e->getMessage() . '</p>';
        }
    }
}

?>
