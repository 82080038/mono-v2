<?php
/**
 * API Endpoints untuk Modal CRUD Operations (Fixed)
 * Menggunakan AJAX calls dari frontend dengan complete handlers
 */

namespace API;

// Include security helper
require_once __DIR__ . '/../security_fixes.php';

// Include complete handlers
require_once __DIR__ . '/complete_handlers.php';

// Initialize security
\SecurityHelper::init();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Include required files
require_once __DIR__ . '/../config/Config.php';

// Initialize services
$db = \Config::getDatabase();

// Get and sanitize request path and method
$method = $_SERVER['REQUEST_METHOD'];
$path = \SecurityHelper::sanitizeInput($_GET['path'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
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
$handlers = new \CompleteAPIHandlers();

// Route to appropriate handler
switch ($endpoint) {
    case 'members':
        handleMemberRoutes($method, $resourceId, $action);
        break;
        
    case 'loans':
        handleLoanRoutes($method, $resourceId, $action);
        break;
        
    case 'savings':
        handleSavingsRoutes($method, $resourceId, $action);
        break;
        
    case 'users':
        handleUserRoutes($method, $resourceId, $action);
        break;
        
    case 'reports':
        handleReportRoutes($method, $resourceId, $action);
        break;
        
    case 'settings':
        handleSettingsRoutes($method, $resourceId, $action);
        break;
        
    case 'system_health':
        $handlers->handleSystemHealth();
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
                'members',
                'loans', 
                'savings',
                'users',
                'reports',
                'settings',
                'system_health',
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

/**
 * Member Routes
 */
function handleMemberRoutes($method, $memberId, $action) {
    $handlers = new \CompleteAPIHandlers();
    $handlers->handleMemberManagement();
}

/**
 * Loan Routes
 */
function handleLoanRoutes($method, $loanId, $action) {
    $handlers = new \CompleteAPIHandlers();
    $handlers->handleLoanManagement();
}

/**
 * Savings Routes
 */
function handleSavingsRoutes($method, $savingsId, $action) {
    global $db;
    
    switch ($method) {
        case 'GET':
            if ($savingsId) {
                // Get single savings account
                $stmt = $db->prepare("
                    SELECT s.*, m.name as member_name, m.member_number
                    FROM savings s
                    LEFT JOIN members m ON s.member_id = m.id
                    WHERE s.id = ?
                ");
                $stmt->execute([$savingsId]);
                $savings = $stmt->fetch();
                
                if ($savings) {
                    echo json_encode(['success' => true, 'data' => $savings]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Savings account not found']);
                }
            } else {
                // Get all savings accounts
                $stmt = $db->prepare("
                    SELECT s.*, m.name as member_name, m.member_number
                    FROM savings s
                    LEFT JOIN members m ON s.member_id = m.id
                    ORDER BY s.created_at DESC
                ");
                $stmt->execute();
                $savings = $stmt->fetchAll();
                
                echo json_encode(['success' => true, 'data' => $savings]);
            }
            break;
            
        case 'POST':
            // Create new savings account
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            
            if (empty($input) && $_POST) {
                // Sanitize POST inputs
                $sanitized_post = [];
                foreach ($_POST as $key => $value) {
                    $sanitized_post[$key] = \SecurityHelper::sanitizeInput($value);
                }
                $input = $sanitized_post;
            }
            
            // Validate required fields
            $required = ['member_id', 'account_type', 'initial_deposit'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                    return;
                }
            }
            
            try {
                // Generate account number
                $accountNumber = 'SAV' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                
                // Insert savings account
                $stmt = $db->prepare("
                    INSERT INTO savings (member_id, account_number, account_type, balance, interest_rate, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, 'active', NOW())
                ");
                $stmt->execute([
                    $input['member_id'],
                    $accountNumber,
                    $input['account_type'],
                    $input['initial_deposit'],
                    $input['interest_rate'] ?? 0.03
                ]);
                
                $newSavingsId = $db->lastInsertId();
                
                // Create transaction record
                $stmt = $db->prepare("
                    INSERT INTO transactions (account_id, transaction_type, amount, description, status, created_at) 
                    VALUES (?, 'deposit', ?, 'Initial deposit', 'completed', NOW())
                ");
                $stmt->execute([$newSavingsId, $input['initial_deposit']]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Savings account created successfully',
                    'savings_id' => $newSavingsId,
                    'account_number' => $accountNumber
                ]);
                
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error creating savings account: ' . $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
}

/**
 * User Routes
 */
function handleUserRoutes($method, $userId, $action) {
    $handlers = new \CompleteAPIHandlers();
    $handlers->handleUserManagement();
}

/**
 * Settings Routes
 */
function handleSettingsRoutes($method, $settingId, $action) {
    $handlers = new \CompleteAPIHandlers();
    $handlers->handleSystemSettings();
}

/**
 * Report Routes
 */
function handleReportRoutes($method, $reportType, $action) {
    $handlers = new \CompleteAPIHandlers();
    $handlers->handleReports();
}

?>
