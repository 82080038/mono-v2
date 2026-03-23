<?php
/**
 * Enhanced API Endpoints for KSP System
 * Fixed: Content-Type headers, proper JSON responses, error handling
 */

// Prevent direct access
define('IN_API', true);

// Include necessary files
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/UserManager.php';
require_once __DIR__ . '/../core/TransactionProcessor.php';
require_once __DIR__ . '/../core/ApprovalWorkflow.php';
require_once __DIR__ . '/../core/ReportingSystem.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enhanced response function with proper headers
function sendResponse($data, $statusCode = 200) {
    // Clear any previous output
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Set proper headers
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    
    // Set HTTP status code
    http_response_code($statusCode);
    
    // Send JSON response
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Handle preflight OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendResponse(['success' => true, 'message' => 'CORS preflight successful'], 200);
}

// Check authentication
function isAuthenticated() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

// Enhanced error handler
function handleError($message, $statusCode = 400, $errorCode = null) {
    $response = [
        'success' => false,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s'),
        'status_code' => $statusCode
    ];
    
    if ($errorCode) {
        $response['error_code'] = $errorCode;
    }
    
    sendResponse($response, $statusCode);
}

// Validate input data
function validateInput($data, $rules = []) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? null;
        
        // Required validation
        if (isset($rule['required']) && $rule['required'] && empty($value)) {
            $errors[$field] = "Field $field is required";
            continue;
        }
        
        // Skip further validation if field is empty and not required
        if (empty($value)) continue;
        
        // Type validation
        if (isset($rule['type'])) {
            switch ($rule['type']) {
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = "Invalid email format";
                    }
                    break;
                case 'number':
                    if (!is_numeric($value)) {
                        $errors[$field] = "Must be a number";
                    }
                    break;
                case 'int':
                    if (!filter_var($value, FILTER_VALIDATE_INT)) {
                        $errors[$field] = "Must be an integer";
                    }
                    break;
            }
        }
        
        // Length validation
        if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
            $errors[$field] = "Minimum length is {$rule['min_length']} characters";
        }
        
        if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
            $errors[$field] = "Maximum length is {$rule['max_length']} characters";
        }
        
        // Range validation for numbers
        if (isset($rule['min']) && is_numeric($value) && $value < $rule['min']) {
            $errors[$field] = "Minimum value is {$rule['min']}";
        }
        
        if (isset($rule['max']) && is_numeric($value) && $value > $rule['max']) {
            $errors[$field] = "Maximum value is {$rule['max']}";
        }
    }
    
    return $errors;
}

// Main API router
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!isAuthenticated()) {
    handleError('Unauthorized - Please login first', 401, 'AUTH_REQUIRED');
}

// Get current user
$user = $_SESSION['user'];
$db = Database::getInstance();

try {
    switch ($action) {
        
        // === TRANSACTION PROCESSING (TELLER) ===
        
        case 'process_deposit':
            // Only teller, admin, and bos can process deposits
            if (!in_array($user['role'], ['teller', 'admin', 'bos'])) {
                handleError('Unauthorized - Insufficient permissions', 403, 'INSUFFICIENT_PERMISSIONS');
            }
            
            // Validate input
            $validationRules = [
                'member_id' => ['required' => true, 'type' => 'int'],
                'amount' => ['required' => true, 'type' => 'number', 'min' => 1000],
                'account_type' => ['required' => true, 'min_length' => 3],
                'payment_method' => ['required' => true, 'min_length' => 2]
            ];
            
            $depositData = [
                'member_id' => intval($_POST['member_id'] ?? 0),
                'amount' => floatval($_POST['amount'] ?? 0),
                'account_type' => $_POST['account_type'] ?? '',
                'payment_method' => $_POST['payment_method'] ?? 'cash',
                'description' => $_POST['description'] ?? ''
            ];
            
            $errors = validateInput($depositData, $validationRules);
            if (!empty($errors)) {
                handleError('Validation failed: ' . implode(', ', $errors), 400, 'VALIDATION_ERROR');
            }
            
            $processor = new TransactionProcessor($db, $user);
            $result = $processor->processDeposit($depositData);
            
            if ($result['success']) {
                sendResponse([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result['data'],
                    'timestamp' => date('Y-m-d H:i:s')
                ], 200);
            } else {
                handleError($result['message'], 400, 'TRANSACTION_FAILED');
            }
            break;
            
        case 'process_withdrawal':
            // Only teller, admin, and bos can process withdrawals
            if (!in_array($user['role'], ['teller', 'admin', 'bos'])) {
                handleError('Unauthorized - Insufficient permissions', 403, 'INSUFFICIENT_PERMISSIONS');
            }
            
            // Validate input
            $validationRules = [
                'member_id' => ['required' => true, 'type' => 'int'],
                'amount' => ['required' => true, 'type' => 'number', 'min' => 1000],
                'account_type' => ['required' => true, 'min_length' => 3],
                'payment_method' => ['required' => true, 'min_length' => 2]
            ];
            
            $withdrawalData = [
                'member_id' => intval($_POST['member_id'] ?? 0),
                'amount' => floatval($_POST['amount'] ?? 0),
                'account_type' => $_POST['account_type'] ?? '',
                'payment_method' => $_POST['payment_method'] ?? 'cash',
                'description' => $_POST['description'] ?? ''
            ];
            
            $errors = validateInput($withdrawalData, $validationRules);
            if (!empty($errors)) {
                handleError('Validation failed: ' . implode(', ', $errors), 400, 'VALIDATION_ERROR');
            }
            
            $processor = new TransactionProcessor($db, $user);
            $result = $processor->processWithdrawal($withdrawalData);
            
            if ($result['success']) {
                sendResponse([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result['data'],
                    'timestamp' => date('Y-m-d H:i:s')
                ], 200);
            } else {
                handleError($result['message'], 400, 'TRANSACTION_FAILED');
            }
            break;
            
        case 'process_loan_payment':
            // Only teller, admin, and bos can process payments
            if (!in_array($user['role'], ['teller', 'admin', 'bos'])) {
                handleError('Unauthorized - Insufficient permissions', 403, 'INSUFFICIENT_PERMISSIONS');
            }
            
            // Validate input
            $validationRules = [
                'loan_id' => ['required' => true, 'type' => 'int'],
                'amount' => ['required' => true, 'type' => 'number', 'min' => 1000],
                'payment_method' => ['required' => true, 'min_length' => 2]
            ];
            
            $paymentData = [
                'loan_id' => intval($_POST['loan_id'] ?? 0),
                'amount' => floatval($_POST['amount'] ?? 0),
                'payment_method' => $_POST['payment_method'] ?? 'cash'
            ];
            
            $errors = validateInput($paymentData, $validationRules);
            if (!empty($errors)) {
                handleError('Validation failed: ' . implode(', ', $errors), 400, 'VALIDATION_ERROR');
            }
            
            $processor = new TransactionProcessor($db, $user);
            $result = $processor->processLoanPayment($paymentData);
            
            if ($result['success']) {
                sendResponse([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result['data'],
                    'timestamp' => date('Y-m-d H:i:s')
                ], 200);
            } else {
                handleError($result['message'], 400, 'PAYMENT_FAILED');
            }
            break;
            
        case 'get_today_transactions':
            // Only teller, admin, and bos can view transactions
            if (!in_array($user['role'], ['teller', 'admin', 'bos'])) {
                handleError('Unauthorized - Insufficient permissions', 403, 'INSUFFICIENT_PERMISSIONS');
            }
            
            $processor = new TransactionProcessor($db, $user);
            $transactions = $processor->getTodayTransactions();
            
            sendResponse([
                'success' => true,
                'data' => $transactions,
                'count' => count($transactions),
                'timestamp' => date('Y-m-d H:i:s')
            ], 200);
            break;
            
        case 'get_today_summary':
            // Only teller, admin, and bos can view summary
            if (!in_array($user['role'], ['teller', 'admin', 'bos'])) {
                handleError('Unauthorized - Insufficient permissions', 403, 'INSUFFICIENT_PERMISSIONS');
            }
            
            $processor = new TransactionProcessor($db, $user);
            $summary = $processor->getTodaySummary();
            
            sendResponse([
                'success' => true,
                'data' => $summary,
                'timestamp' => date('Y-m-d H:i:s')
            ], 200);
            break;
            
        // === APPROVAL WORKFLOW (ADMIN) ===
        
        case 'get_pending_approvals':
            // Only admin and bos can view pending approvals
            if (!in_array($user['role'], ['admin', 'bos'])) {
                handleError('Unauthorized - Insufficient permissions', 403, 'INSUFFICIENT_PERMISSIONS');
            }
            
            $workflow = new ApprovalWorkflow($db, $user);
            $approvals = $workflow->getPendingApprovals();
            
            sendResponse([
                'success' => true,
                'data' => $approvals,
                'timestamp' => date('Y-m-d H:i:s')
            ], 200);
            break;
            
        case 'approve_member':
            // Only admin and bos can approve members
            if (!in_array($user['role'], ['admin', 'bos'])) {
                handleError('Unauthorized - Insufficient permissions', 403, 'INSUFFICIENT_PERMISSIONS');
            }
            
            // Validate input
            $validationRules = [
                'member_id' => ['required' => true, 'type' => 'int']
            ];
            
            $memberId = intval($_POST['member_id'] ?? 0);
            $notes = $_POST['notes'] ?? '';
            
            $errors = validateInput(['member_id' => $memberId], $validationRules);
            if (!empty($errors)) {
                handleError('Validation failed: ' . implode(', ', $errors), 400, 'VALIDATION_ERROR');
            }
            
            $workflow = new ApprovalWorkflow($db, $user);
            $result = $workflow->approveMemberRegistration($memberId, $notes);
            
            if ($result['success']) {
                sendResponse([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result['data'],
                    'timestamp' => date('Y-m-d H:i:s')
                ], 200);
            } else {
                handleError($result['message'], 400, 'APPROVAL_FAILED');
            }
            break;
            
        case 'reject_member':
            // Only admin and bos can reject members
            if (!in_array($user['role'], ['admin', 'bos'])) {
                handleError('Unauthorized - Insufficient permissions', 403, 'INSUFFICIENT_PERMISSIONS');
            }
            
            // Validate input
            $validationRules = [
                'member_id' => ['required' => true, 'type' => 'int'],
                'reason' => ['required' => true, 'min_length' => 5]
            ];
            
            $memberId = intval($_POST['member_id'] ?? 0);
            $reason = $_POST['reason'] ?? '';
            
            $errors = validateInput(['member_id' => $memberId, 'reason' => $reason], $validationRules);
            if (!empty($errors)) {
                handleError('Validation failed: ' . implode(', ', $errors), 400, 'VALIDATION_ERROR');
            }
            
            $workflow = new ApprovalWorkflow($db, $user);
            $result = $workflow->rejectMemberRegistration($memberId, $reason);
            
            if ($result['success']) {
                sendResponse([
                    'success' => true,
                    'message' => $result['message'],
                    'timestamp' => date('Y-m-d H:i:s')
                ], 200);
            } else {
                handleError($result['message'], 400, 'REJECTION_FAILED');
            }
            break;
            
        case 'get_approval_stats':
            // Only admin and bos can view approval stats
            if (!in_array($user['role'], ['admin', 'bos'])) {
                handleError('Unauthorized - Insufficient permissions', 403, 'INSUFFICIENT_PERMISSIONS');
            }
            
            $workflow = new ApprovalWorkflow($db, $user);
            $stats = $workflow->getApprovalStats();
            
            sendResponse([
                'success' => true,
                'data' => $stats,
                'timestamp' => date('Y-m-d H:i:s')
            ], 200);
            break;
            
        // === REPORTING SYSTEM (BOS) ===
        
        case 'get_executive_dashboard':
            // Only bos can view executive dashboard
            if ($user['role'] !== 'bos') {
                handleError('Unauthorized - Insufficient permissions', 403, 'INSUFFICIENT_PERMISSIONS');
            }
            
            $reporting = new ReportingSystem($db, $user);
            $dashboard = $reporting->getExecutiveDashboard();
            
            sendResponse([
                'success' => true,
                'data' => $dashboard,
                'timestamp' => date('Y-m-d H:i:s')
            ], 200);
            break;
            
        case 'get_overview_stats':
            // Only bos and admin can view overview stats
            if (!in_array($user['role'], ['bos', 'admin'])) {
                handleError('Unauthorized - Insufficient permissions', 403, 'INSUFFICIENT_PERMISSIONS');
            }
            
            $reporting = new ReportingSystem($db, $user);
            $stats = $reporting->getOverviewStats();
            
            sendResponse([
                'success' => true,
                'data' => $stats,
                'timestamp' => date('Y-m-d H:i:s')
            ], 200);
            break;
            
        case 'generate_monthly_report':
            // Only bos can generate reports
            if ($user['role'] !== 'bos') {
                handleError('Unauthorized - Insufficient permissions', 403, 'INSUFFICIENT_PERMISSIONS');
            }
            
            // Validate input
            $validationRules = [
                'year' => ['required' => true, 'type' => 'int', 'min' => 2020, 'max' => 2030],
                'month' => ['required' => true, 'type' => 'int', 'min' => 1, 'max' => 12]
            ];
            
            $year = intval($_POST['year'] ?? date('Y'));
            $month = intval($_POST['month'] ?? date('m'));
            
            $errors = validateInput(['year' => $year, 'month' => $month], $validationRules);
            if (!empty($errors)) {
                handleError('Validation failed: ' . implode(', ', $errors), 400, 'VALIDATION_ERROR');
            }
            
            $reporting = new ReportingSystem($db, $user);
            $report = $reporting->generateMonthlyReport($year, $month);
            
            sendResponse([
                'success' => true,
                'data' => $report,
                'timestamp' => date('Y-m-d H:i:s')
            ], 200);
            break;
            
        // === MEMBER SERVICES (ALL ROLES) ===
        
        case 'search_member':
            // All authenticated users can search members
            $searchTerm = $_GET['q'] ?? '';
            
            if (strlen($searchTerm) < 2) {
                handleError('Search term must be at least 2 characters', 400, 'INVALID_SEARCH');
            }
            
            try {
                $members = $db->fetchAll(
                    "SELECT id, member_number, full_name, phone, email, status 
                     FROM members 
                     WHERE (member_number LIKE ? OR full_name LIKE ? OR phone LIKE ?) 
                     AND status = 'active'
                     LIMIT 10",
                    ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%"]
                );
                
                sendResponse([
                    'success' => true,
                    'data' => $members,
                    'count' => count($members),
                    'search_term' => $searchTerm,
                    'timestamp' => date('Y-m-d H:i:s')
                ], 200);
            } catch (Exception $e) {
                handleError('Database error: ' . $e->getMessage(), 500, 'DB_ERROR');
            }
            break;
            
        case 'get_member_accounts':
            // All authenticated users can view member accounts
            $memberId = intval($_GET['member_id'] ?? 0);
            
            if ($memberId <= 0) {
                handleError('Invalid member ID', 400, 'INVALID_MEMBER_ID');
            }
            
            try {
                $accounts = $db->fetchAll(
                    "SELECT a.*, m.full_name 
                     FROM accounts a
                     JOIN members m ON a.member_id = m.id
                     WHERE a.member_id = ? AND a.status = 'active'",
                    [$memberId]
                );
                
                sendResponse([
                    'success' => true,
                    'data' => $accounts,
                    'count' => count($accounts),
                    'timestamp' => date('Y-m-d H:i:s')
                ], 200);
            } catch (Exception $e) {
                handleError('Database error: ' . $e->getMessage(), 500, 'DB_ERROR');
            }
            break;
            
        case 'get_member_loans':
            // All authenticated users can view member loans
            $memberId = intval($_GET['member_id'] ?? 0);
            
            if ($memberId <= 0) {
                handleError('Invalid member ID', 400, 'INVALID_MEMBER_ID');
            }
            
            try {
                $loans = $db->fetchAll(
                    "SELECT l.*, m.full_name 
                     FROM loans l
                     JOIN members m ON l.member_id = m.id
                     WHERE l.member_id = ? AND l.status IN ('active', 'completed')
                     ORDER BY l.application_date DESC",
                    [$memberId]
                );
                
                sendResponse([
                    'success' => true,
                    'data' => $loans,
                    'count' => count($loans),
                    'timestamp' => date('Y-m-d H:i:s')
                ], 200);
            } catch (Exception $e) {
                handleError('Database error: ' . $e->getMessage(), 500, 'DB_ERROR');
            }
            break;
            
        case 'get_transaction_history':
            // All authenticated users can view transaction history
            $memberId = intval($_GET['member_id'] ?? 0);
            $limit = min(50, max(1, intval($_GET['limit'] ?? 20)));
            
            if ($memberId <= 0) {
                handleError('Invalid member ID', 400, 'INVALID_MEMBER_ID');
            }
            
            try {
                $transactions = $db->fetchAll(
                    "SELECT t.*, a.account_type, a.account_number
                     FROM transactions t
                     JOIN accounts a ON t.account_id = a.id
                     WHERE a.member_id = ?
                     ORDER BY t.created_at DESC
                     LIMIT ?",
                    [$memberId, $limit]
                );
                
                sendResponse([
                    'success' => true,
                    'data' => $transactions,
                    'count' => count($transactions),
                    'limit' => $limit,
                    'timestamp' => date('Y-m-d H:i:s')
                ], 200);
            } catch (Exception $e) {
                handleError('Database error: ' . $e->getMessage(), 500, 'DB_ERROR');
            }
            break;
            
        // === DASHBOARD DATA (ROLE-SPECIFIC) ===
        
        case 'get_dashboard_widgets':
            // Get role-specific dashboard widgets
            try {
                $userManager = new UserManager($db);
                $widgets = $userManager->getDashboardWidgets($user['role']);
                
                sendResponse([
                    'success' => true,
                    'data' => $widgets,
                    'role' => $user['role'],
                    'timestamp' => date('Y-m-d H:i:s')
                ], 200);
            } catch (Exception $e) {
                handleError('Dashboard error: ' . $e->getMessage(), 500, 'DASHBOARD_ERROR');
            }
            break;
            
        case 'get_user_profile':
            // Get current user profile
            sendResponse([
                'success' => true,
                'data' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'email' => $user['email'] ?? ''
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ], 200);
            break;
            
        default:
            handleError('Invalid action or endpoint not found', 404, 'INVALID_ACTION');
    }
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    handleError('Internal server error', 500, 'INTERNAL_ERROR');
}
?>
