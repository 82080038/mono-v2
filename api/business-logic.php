<?php
/**
 * API Endpoints for KSP System
 * Handles AJAX requests for all core business logic
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

// Check authentication
function isAuthenticated() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

// Send JSON response
function sendResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Main API router
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!isAuthenticated()) {
    sendResponse(['success' => false, 'message' => 'Unauthorized'], 401);
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
                sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $processor = new TransactionProcessor($db, $user);
            $depositData = [
                'member_id' => $_POST['member_id'] ?? '',
                'amount' => floatval($_POST['amount'] ?? 0),
                'account_type' => $_POST['account_type'] ?? '',
                'payment_method' => $_POST['payment_method'] ?? 'cash',
                'description' => $_POST['description'] ?? ''
            ];
            
            $result = $processor->processDeposit($depositData);
            sendResponse($result);
            break;
            
        case 'process_withdrawal':
            // Only teller, admin, and bos can process withdrawals
            if (!in_array($user['role'], ['teller', 'admin', 'bos'])) {
                sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $processor = new TransactionProcessor($db, $user);
            $withdrawalData = [
                'member_id' => $_POST['member_id'] ?? '',
                'amount' => floatval($_POST['amount'] ?? 0),
                'account_type' => $_POST['account_type'] ?? '',
                'payment_method' => $_POST['payment_method'] ?? 'cash',
                'description' => $_POST['description'] ?? ''
            ];
            
            $result = $processor->processWithdrawal($withdrawalData);
            sendResponse($result);
            break;
            
        case 'process_loan_payment':
            // Only teller, admin, and bos can process payments
            if (!in_array($user['role'], ['teller', 'admin', 'bos'])) {
                sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $processor = new TransactionProcessor($db, $user);
            $paymentData = [
                'loan_id' => $_POST['loan_id'] ?? '',
                'amount' => floatval($_POST['amount'] ?? 0),
                'payment_method' => $_POST['payment_method'] ?? 'cash'
            ];
            
            $result = $processor->processLoanPayment($paymentData);
            sendResponse($result);
            break;
            
        case 'get_today_transactions':
            // Only teller, admin, and bos can view transactions
            if (!in_array($user['role'], ['teller', 'admin', 'bos'])) {
                sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $processor = new TransactionProcessor($db, $user);
            $transactions = $processor->getTodayTransactions();
            sendResponse(['success' => true, 'data' => $transactions]);
            break;
            
        case 'get_today_summary':
            // Only teller, admin, and bos can view summary
            if (!in_array($user['role'], ['teller', 'admin', 'bos'])) {
                sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $processor = new TransactionProcessor($db, $user);
            $summary = $processor->getTodaySummary();
            sendResponse(['success' => true, 'data' => $summary]);
            break;
            
        // === APPROVAL WORKFLOW (ADMIN) ===
        
        case 'get_pending_approvals':
            // Only admin and bos can view pending approvals
            if (!in_array($user['role'], ['admin', 'bos'])) {
                sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $workflow = new ApprovalWorkflow($db, $user);
            $approvals = $workflow->getPendingApprovals();
            sendResponse(['success' => true, 'data' => $approvals]);
            break;
            
        case 'approve_member':
            // Only admin and bos can approve members
            if (!in_array($user['role'], ['admin', 'bos'])) {
                sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $workflow = new ApprovalWorkflow($db, $user);
            $memberId = $_POST['member_id'] ?? '';
            $notes = $_POST['notes'] ?? '';
            
            $result = $workflow->approveMemberRegistration($memberId, $notes);
            sendResponse($result);
            break;
            
        case 'reject_member':
            // Only admin and bos can reject members
            if (!in_array($user['role'], ['admin', 'bos'])) {
                sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $workflow = new ApprovalWorkflow($db, $user);
            $memberId = $_POST['member_id'] ?? '';
            $reason = $_POST['reason'] ?? '';
            
            $result = $workflow->rejectMemberRegistration($memberId, $reason);
            sendResponse($result);
            break;
            
        case 'approve_loan':
            // Only admin and bos can approve loans
            if (!in_array($user['role'], ['admin', 'bos'])) {
                sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $workflow = new ApprovalWorkflow($db, $user);
            $loanId = $_POST['loan_id'] ?? '';
            $approvedAmount = floatval($_POST['approved_amount'] ?? 0) ?: null;
            $notes = $_POST['notes'] ?? '';
            
            $result = $workflow->approveLoanApplication($loanId, $approvedAmount, $notes);
            sendResponse($result);
            break;
            
        case 'reject_loan':
            // Only admin and bos can reject loans
            if (!in_array($user['role'], ['admin', 'bos'])) {
                sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $workflow = new ApprovalWorkflow($db, $user);
            $loanId = $_POST['loan_id'] ?? '';
            $reason = $_POST['reason'] ?? '';
            
            $result = $workflow->rejectLoanApplication($loanId, $reason);
            sendResponse($result);
            break;
            
        case 'get_approval_stats':
            // Only admin and bos can view approval stats
            if (!in_array($user['role'], ['admin', 'bos'])) {
                sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $workflow = new ApprovalWorkflow($db, $user);
            $stats = $workflow->getApprovalStats();
            sendResponse(['success' => true, 'data' => $stats]);
            break;
            
        // === REPORTING SYSTEM (BOS) ===
        
        case 'get_executive_dashboard':
            // Only bos can view executive dashboard
            if ($user['role'] !== 'bos') {
                sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $reporting = new ReportingSystem($db, $user);
            $dashboard = $reporting->getExecutiveDashboard();
            sendResponse(['success' => true, 'data' => $dashboard]);
            break;
            
        case 'get_overview_stats':
            // Only bos and admin can view overview stats
            if (!in_array($user['role'], ['bos', 'admin'])) {
                sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $reporting = new ReportingSystem($db, $user);
            $stats = $reporting->getOverviewStats();
            sendResponse(['success' => true, 'data' => $stats]);
            break;
            
        case 'get_financial_performance':
            // Only bos can view financial performance
            if ($user['role'] !== 'bos') {
                sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $reporting = new ReportingSystem($db, $user);
            $financial = $reporting->getFinancialPerformance();
            sendResponse(['success' => true, 'data' => $financial]);
            break;
            
        case 'get_loan_portfolio':
            // Only bos can view loan portfolio
            if ($user['role'] !== 'bos') {
                sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $reporting = new ReportingSystem($db, $user);
            $portfolio = $reporting->getLoanPortfolio();
            sendResponse(['success' => true, 'data' => $portfolio]);
            break;
            
        case 'generate_monthly_report':
            // Only bos can generate reports
            if ($user['role'] !== 'bos') {
                sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $year = intval($_POST['year'] ?? date('Y'));
            $month = intval($_POST['month'] ?? date('m'));
            
            $reporting = new ReportingSystem($db, $user);
            $report = $reporting->generateMonthlyReport($year, $month);
            sendResponse(['success' => true, 'data' => $report]);
            break;
            
        case 'export_report':
            // Only bos can export reports
            if ($user['role'] !== 'bos') {
                sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $reportType = $_POST['report_type'] ?? '';
            $format = $_POST['format'] ?? 'csv';
            
            $reporting = new ReportingSystem($db, $user);
            $exportData = $reporting->exportReport($reportType, $format);
            
            if ($format === 'csv') {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $reportType . '_report.csv"');
                echo $exportData;
                exit;
            } else {
                sendResponse(['success' => true, 'data' => $exportData]);
            }
            break;
            
        // === MEMBER SERVICES (ALL ROLES) ===
        
        case 'search_member':
            // All authenticated users can search members
            $searchTerm = $_GET['q'] ?? '';
            $members = $db->fetchAll(
                "SELECT id, member_number, full_name, phone, email, status 
                 FROM members 
                 WHERE (member_number LIKE ? OR full_name LIKE ? OR phone LIKE ?) 
                 AND status = 'active'
                 LIMIT 10",
                ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%"]
            );
            sendResponse(['success' => true, 'data' => $members]);
            break;
            
        case 'get_member_accounts':
            // All authenticated users can view member accounts
            $memberId = $_GET['member_id'] ?? '';
            $accounts = $db->fetchAll(
                "SELECT a.*, m.full_name 
                 FROM accounts a
                 JOIN members m ON a.member_id = m.id
                 WHERE a.member_id = ? AND a.status = 'active'",
                [$memberId]
            );
            sendResponse(['success' => true, 'data' => $accounts]);
            break;
            
        case 'get_member_loans':
            // All authenticated users can view member loans
            $memberId = $_GET['member_id'] ?? '';
            $loans = $db->fetchAll(
                "SELECT l.*, m.full_name 
                 FROM loans l
                 JOIN members m ON l.member_id = m.id
                 WHERE l.member_id = ? AND l.status IN ('active', 'completed')
                 ORDER BY l.application_date DESC",
                [$memberId]
            );
            sendResponse(['success' => true, 'data' => $loans]);
            break;
            
        case 'get_transaction_history':
            // All authenticated users can view transaction history
            $memberId = $_GET['member_id'] ?? '';
            $limit = intval($_GET['limit'] ?? 50);
            
            $transactions = $db->fetchAll(
                "SELECT t.*, a.account_type, a.account_number
                 FROM transactions t
                 JOIN accounts a ON t.account_id = a.id
                 WHERE a.member_id = ?
                 ORDER BY t.created_at DESC
                 LIMIT ?",
                [$memberId, $limit]
            );
            sendResponse(['success' => true, 'data' => $transactions]);
            break;
            
        // === DASHBOARD DATA (ROLE-SPECIFIC) ===
        
        case 'get_dashboard_widgets':
            // Get role-specific dashboard widgets
            $userManager = new UserManager($db);
            $widgets = $userManager->getDashboardWidgets($user['role']);
            sendResponse(['success' => true, 'data' => $widgets]);
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
                ]
            ]);
            break;
            
        default:
            sendResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    sendResponse(['success' => false, 'message' => 'Internal server error'], 500);
}
?>
