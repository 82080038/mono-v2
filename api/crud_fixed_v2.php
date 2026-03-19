<?php
/**
 * API Endpoints untuk Modal CRUD Operations (Fixed Version)
 * Menggunakan AJAX calls dari frontend dengan complete handlers
 */

// Include security helper
require_once __DIR__ . '/../security_fixes.php';

// Initialize security
SecurityHelper::init();

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($path, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Remove 'api' from path parts
if ($pathParts[0] === 'api') {
    array_shift($pathParts);
}

// API routing
$endpoint = $pathParts[0] ?? '';
$resourceId = $pathParts[1] ?? null;
$action = $pathParts[2] ?? null;

// Load handlers only when needed
if (in_array($endpoint, ['users', 'settings', 'system_health', 'members', 'loans', 'reports'])) {
    try {
        // Include required files
        require_once __DIR__ . '/../config/Config.php';
        require_once __DIR__ . '/DatabaseHelper.php';
        require_once __DIR__ . '/complete_handlers.php';
        
        // Initialize handlers
        $handlers = new CompleteAPIHandlers();
        
        // Route to appropriate handler
        switch ($endpoint) {
            case 'users':
                $handlers->handleUserManagement();
                break;
                
            case 'settings':
                $handlers->handleSystemSettings();
                break;
                
            case 'system_health':
                $handlers->handleSystemHealth();
                break;
                
            case 'members':
                $handlers->handleMemberManagement();
                break;
                
            case 'loans':
                $handlers->handleLoanManagement();
                break;
                
            case 'reports':
                $handlers->handleReports();
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Endpoint not found'
                ]);
                break;
        }
        
    } catch (Exception $e) {
        error_log("API Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error',
            'error' => 'API_ERROR'
        ]);
    }
} else {
    // Return available endpoints
    echo json_encode([
        'success' => false,
        'message' => 'Endpoint not found',
        'error' => 'INVALID_ENDPOINT',
        'available_endpoints' => [
            'users',
            'settings',
            'system_health',
            'members',
            'loans',
            'reports',
            'profile',
            'accounts',
            'transactions',
            'payments',
            'cash',
            'credit',
            'field_data',
            'gps_tracking',
            'collection',
            'overdue',
            'collection_reports',
            'surveys',
            'verification'
        ]
    ]);
}

?>
