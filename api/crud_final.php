<?php
/**
 * API Endpoints untuk Modal CRUD Operations (Final Fixed Version)
 * Menggunakan AJAX calls dari frontend dengan complete handlers
 */

// Include security helper
require_once __DIR__ . '/../security_fixes.php';

// Include complete handlers
require_once __DIR__ . '/complete_handlers.php';

// Initialize security
SecurityHelper::init();

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
        
    case 'profile':
        $handlers->handleProfile();
        break;
        
    case 'accounts':
        $handlers->handleAccounts();
        break;
        
    case 'transactions':
        $handlers->handleTransactions();
        break;
        
    case 'payments':
        $handlers->handlePayments();
        break;
        
    case 'cash':
        $handlers->handleCashManagement();
        break;
        
    case 'credit':
        $handlers->handleCredit();
        break;
        
    case 'field_data':
        $handlers->handleFieldData();
        break;
        
    case 'gps_tracking':
        $handlers->handleGpsTracking();
        break;
        
    case 'collection':
        $handlers->handleCollection();
        break;
        
    case 'overdue':
        $handlers->handleOverdueAccounts();
        break;
        
    case 'collection_reports':
        $handlers->handleCollectionReports();
        break;
        
    case 'surveys':
        $handlers->handleSurveys();
        break;
        
    case 'verification':
        $handlers->handleVerification();
        break;
        
    default:
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
        break;
}

?>
