<?php
/**
 * Enhanced Loans API with CRUD operations
 * Supports loan management with proper authentication and validation
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Include required files
require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/DatabaseHelper.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/DataValidator.php';
require_once __DIR__ . '/SecurityLogger.php';

// Initialize services
Logger::initialize();
$securityLogger = SecurityLogger::getInstance();
$db = DatabaseHelper::getInstance();
$validator = new DataValidator();

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'data' => null,
    'errors' => [],
    'timestamp' => date('Y-m-d H:i:s')
];

// Authentication middleware
function requireAuth($role = null) {
    global $db;
    
    $token = getTokenFromRequest();
    if (!$token) {
        throw new Exception('Authentication required');
    }
    
    $tokenData = validateJWTToken($token);
    if (!$tokenData) {
        throw new Exception('Invalid token');
    }
    
    $user = $db->fetchOne(
        "SELECT * FROM users WHERE id = ? AND is_active = 1",
        [$tokenData['user_id']]
    );
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    if ($role && $user['role'] !== $role && $user['role'] !== 'admin') {
        throw new Exception('Insufficient privileges');
    }
    
    return array_merge($user, $tokenData);
}

function getTokenFromRequest() {
    $headers = getallheaders();
    return $headers['Authorization'] ?? $headers['authorization'] ?? $_REQUEST['token'] ?? null;
}

function validateJWTToken($token) {
    if (!$token) {
        return null;
    }
    
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }
    
    $payload = base64_decode($parts[1]);
    $payloadData = json_decode($payload, true);
    
    if (!$payloadData || $payloadData['exp'] < time()) {
        return null;
    }
    
    return $payloadData;
}

function logAudit($action, $table, $recordId, $oldValues = null, $newValues = null) {
    global $db, $securityLogger;
    
    $user = getCurrentUser();
    if ($user) {
        $db->insert('audit_logs', [
            'user_id' => $user['id'],
            'action' => $action,
            'table_name' => $table,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        $securityLogger->logDataModification($table, $action, $oldValues, $newValues);
    }
}

function getCurrentUser() {
    global $db;
    
    $token = getTokenFromRequest();
    if (!$token) {
        return null;
    }
    
    $tokenData = validateJWTToken($token);
    if (!$tokenData) {
        return null;
    }
    
    return $db->fetchOne(
        "SELECT * FROM users WHERE id = ? AND is_active = 1",
        [$tokenData['user_id']]
    );
}

// Handle API requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($action, $db, $validator);
            break;
        case 'POST':
            handlePostRequest($action, $db, $validator);
            break;
        case 'PUT':
            handlePutRequest($action, $db, $validator);
            break;
        case 'DELETE':
            handleDeleteRequest($action, $db, $validator);
            break;
        default:
            $response['message'] = 'Method not allowed';
            echo json_encode($response);
            break;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    echo json_encode($response);
}

function handleGetRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'list':
            handleListLoans($db, $validator);
            break;
        case 'detail':
            handleGetLoan($db, $validator);
            break;
        case 'member_loans':
            handleGetMemberLoans($db, $validator);
            break;
        case 'statistics':
            handleLoanStatistics($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handlePostRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'apply':
            handleLoanApplication($db, $validator);
            break;
        case 'approve':
            handleLoanApproval($db, $validator);
            break;
        case 'disburse':
            handleLoanDisbursement($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handlePutRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'update':
            handleUpdateLoan($db, $validator);
            break;
        case 'payment':
            handleLoanPayment($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleDeleteRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'cancel':
            handleLoanCancellation($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleListLoans($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $status = $_GET['status'] ?? '';
    $memberId = (int)($_GET['member_id'] ?? 0);
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = [];
    $params = [];
    
    if (!empty($status)) {
        $whereConditions[] = "l.status = ?";
        $params[] = $status;
    }
    
    if ($memberId > 0) {
        $whereConditions[] = "l.member_id = ?";
        $params[] = $memberId;
    }
    
    // Members can only see their own loans
    if ($user['role'] === 'member') {
        $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['user_id']]);
        if ($member) {
            $whereConditions[] = "l.member_id = ?";
            $params[] = $member['id'];
        }
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM loans l LEFT JOIN members m ON l.member_id = m.id $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get loans
    $sql = "SELECT l.*, m.full_name as member_name, m.member_number, u.username as approved_by_name
            FROM loans l 
            LEFT JOIN members m ON l.member_id = m.id 
            LEFT JOIN users u ON l.approved_by = u.id
            $whereClause
            ORDER BY l.created_at DESC 
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $loans = $db->fetchAll($sql, $params);
    
    $response['success'] = true;
    $response['message'] = 'Loans retrieved successfully';
    $response['data'] = [
        'loans' => $loans,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    echo json_encode($response);
}

function handleGetLoan($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $loanId = (int)($_GET['id'] ?? 0);
    
    if ($loanId <= 0) {
        $response['message'] = 'Loan ID required';
        echo json_encode($response);
        return;
    }
    
    // Check permissions
    if ($user['role'] === 'member') {
        $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['user_id']]);
        if ($member) {
            $loan = $db->fetchOne(
                "SELECT l.*, m.full_name as member_name, m.member_number, u.username as approved_by_name
                 FROM loans l 
                 LEFT JOIN members m ON l.member_id = m.id 
                 LEFT JOIN users u ON l.approved_by = u.id
                 WHERE l.id = ? AND l.member_id = ?",
                [$loanId, $member['id']]
            );
        }
    } else {
        $loan = $db->fetchOne(
            "SELECT l.*, m.full_name as member_name, m.member_number, u.username as approved_by_name
             FROM loans l 
             LEFT JOIN members m ON l.member_id = m.id 
             LEFT JOIN users u ON l.approved_by = u.id
             WHERE l.id = ?",
            [$loanId]
        );
    }
    
    if (!$loan) {
        $response['message'] = 'Loan not found';
        echo json_encode($response);
        return;
    }
    
    // Get payment history
    $loan['payment_history'] = $db->fetchAll(
        "SELECT * FROM payment_transactions WHERE loan_id = ? ORDER BY created_at DESC",
        [$loanId]
    );
    
    // Calculate remaining balance
    $paidAmount = $db->fetchOne(
        "SELECT COALESCE(SUM(amount), 0) as total_paid FROM payment_transactions WHERE loan_id = ? AND status = 'Completed'",
        [$loanId]
    )['total_paid'];
    
    $loan['paid_amount'] = $paidAmount;
    $loan['remaining_balance'] = $loan['amount'] + ($loan['total_interest'] ?? 0) - $paidAmount;
    
    $response['success'] = true;
    $response['message'] = 'Loan retrieved successfully';
    $response['data'] = $loan;
    
    echo json_encode($response);
}

function handleLoanApplication($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'amount' => 'required|numeric|min:100000',
        'purpose' => 'required|string|min:10',
        'term_months' => 'required|integer|min:1|max:60',
        'collateral_type' => 'in:None,Property,Vehicle,Guarantor,Other',
        'collateral_value' => 'numeric|min:0'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get member ID
    if ($user['role'] === 'member') {
        $member = $db->fetchOne("SELECT id, credit_score FROM members WHERE user_id = ? AND is_active = 1", [$user['user_id']]);
    } else {
        $memberId = (int)($input['member_id'] ?? 0);
        $member = $db->fetchOne("SELECT id, credit_score FROM members WHERE id = ? AND is_active = 1", [$memberId]);
    }
    
    if (!$member) {
        $response['message'] = 'Member not found or inactive';
        echo json_encode($response);
        return;
    }
    
    // Check for existing active loans
    $activeLoans = $db->fetchOne(
        "SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status IN ('Active', 'Disbursed')",
        [$member['id']]
    );
    
    if ($activeLoans['count'] > 0) {
        $response['message'] = 'Member already has active loans';
        echo json_encode($response);
        return;
    }
    
    // Calculate interest and monthly payment
    $interestRate = getInterestRate($input['amount'], $member['credit_score']);
    $totalInterest = ($input['amount'] * $interestRate / 100) * ($input['term_months'] / 12);
    $monthlyPayment = ($input['amount'] + $totalInterest) / $input['term_months'];
    
    $db->beginTransaction();
    
    try {
        // Generate loan number
        $loanNumber = generateLoanNumber();
        
        // Create loan application
        $loanData = [
            'member_id' => $member['id'],
            'loan_number' => $loanNumber,
            'amount' => $input['amount'],
            'interest_rate' => $interestRate,
            'term_months' => $input['term_months'],
            'purpose' => $input['purpose'],
            'collateral_type' => $input['collateral_type'] ?? 'None',
            'collateral_value' => $input['collateral_value'] ?? 0,
            'status' => 'Applied',
            'monthly_payment' => $monthlyPayment,
            'total_interest' => $totalInterest,
            'remaining_balance' => $input['amount'] + $totalInterest,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $loanId = $db->insert('loans', $loanData);
        
        $db->commit();
        
        logAudit('CREATE', 'loans', $loanId, null, $loanData);
        
        $response['success'] = true;
        $response['message'] = 'Loan application submitted successfully';
        $response['data'] = [
            'loan_id' => $loanId,
            'loan_number' => $loanNumber,
            'interest_rate' => $interestRate,
            'monthly_payment' => $monthlyPayment,
            'total_interest' => $totalInterest
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleLoanApproval($db, $validator) {
    global $response;
    
    $user = requireAuth('admin'); // Only admin can approve loans
    $input = json_decode(file_get_contents('php://input'), true);
    $loanId = (int)($input['loan_id'] ?? 0);
    $approved = (bool)($input['approved'] ?? false);
    $notes = $input['notes'] ?? '';
    
    if ($loanId <= 0) {
        $response['message'] = 'Loan ID required';
        echo json_encode($response);
        return;
    }
    
    $loan = $db->fetchOne("SELECT * FROM loans WHERE id = ?", [$loanId]);
    if (!$loan) {
        $response['message'] = 'Loan not found';
        echo json_encode($response);
        return;
    }
    
    if ($loan['status'] !== 'Applied') {
        $response['message'] = 'Loan can only be approved when in Applied status';
        echo json_encode($response);
        return;
    }
    
    $updateData = [
        'status' => $approved ? 'Approved' : 'Rejected',
        'approved_by' => $user['id'],
        'approved_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $db->update('loans', $updateData, 'id = ?', [$loanId]);
    
    logAudit('APPROVE', 'loans', $loanId, $loan, $updateData);
    
    $response['success'] = true;
    $response['message'] = $approved ? 'Loan approved successfully' : 'Loan rejected';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleLoanDisbursement($db, $validator) {
    global $response;
    
    $user = requireAuth('admin'); // Only admin can disburse loans
    $input = json_decode(file_get_contents('php://input'), true);
    $loanId = (int)($input['loan_id'] ?? 0);
    
    if ($loanId <= 0) {
        $response['message'] = 'Loan ID required';
        echo json_encode($response);
        return;
    }
    
    $loan = $db->fetchOne("SELECT * FROM loans WHERE id = ?", [$loanId]);
    if (!$loan) {
        $response['message'] = 'Loan not found';
        echo json_encode($response);
        return;
    }
    
    if ($loan['status'] !== 'Approved') {
        $response['message'] = 'Loan can only be disbursed when approved';
        echo json_encode($response);
        return;
    }
    
    $updateData = [
        'status' => 'Disbursed',
        'disbursed_at' => date('Y-m-d H:i:s'),
        'next_payment_date' => date('Y-m-d', strtotime('+1 month')),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $db->update('loans', $updateData, 'id = ?', [$loanId]);
    
    logAudit('DISBURSE', 'loans', $loanId, $loan, $updateData);
    
    $response['success'] = true;
    $response['message'] = 'Loan disbursed successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleLoanPayment($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    $loanId = (int)($input['loan_id'] ?? 0);
    $amount = (float)($input['amount'] ?? 0);
    $paymentMethod = $input['payment_method'] ?? 'Cash';
    
    if ($loanId <= 0 || $amount <= 0) {
        $response['message'] = 'Invalid loan ID or amount';
        echo json_encode($response);
        return;
    }
    
    $loan = $db->fetchOne("SELECT * FROM loans WHERE id = ?", [$loanId]);
    if (!$loan) {
        $response['message'] = 'Loan not found';
        echo json_encode($response);
        return;
    }
    
    if ($loan['status'] !== 'Disbursed' && $loan['status'] !== 'Active') {
        $response['message'] = 'Loan payment not allowed for this status';
        echo json_encode($response);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Create payment transaction
        $transactionData = [
            'loan_id' => $loanId,
            'member_id' => $loan['member_id'],
            'transaction_number' => generateTransactionNumber(),
            'amount' => $amount,
            'type' => 'Loan Payment',
            'payment_method' => $paymentMethod,
            'status' => 'Completed',
            'processed_by' => $user['id'],
            'processed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $transactionId = $db->insert('payment_transactions', $transactionData);
        
        // Update loan remaining balance
        $paidAmount = $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total_paid FROM payment_transactions WHERE loan_id = ? AND status = 'Completed'",
            [$loanId]
        )['total_paid'];
        
        $remainingBalance = ($loan['amount'] + $loan['total_interest']) - $paidAmount;
        
        $loanUpdateData = [
            'remaining_balance' => $remainingBalance,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Update loan status if fully paid
        if ($remainingBalance <= 0) {
            $loanUpdateData['status'] = 'Completed';
        }
        
        $db->update('loans', $loanUpdateData, 'id = ?', [$loanId]);
        
        $db->commit();
        
        logAudit('PAYMENT', 'payment_transactions', $transactionId, null, $transactionData);
        
        $response['success'] = true;
        $response['message'] = 'Payment processed successfully';
        $response['data'] = [
            'transaction_id' => $transactionId,
            'remaining_balance' => $remainingBalance,
            'loan_status' => $remainingBalance <= 0 ? 'Completed' : 'Active'
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleLoanStatistics($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    // Basic statistics
    $stats = [
        'total_loans' => $db->fetchOne("SELECT COUNT(*) as count FROM loans")['count'],
        'active_loans' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status IN ('Active', 'Disbursed')")['count'],
        'total_amount' => $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM loans")['total'],
        'total_disbursed' => $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM loans WHERE status IN ('Disbursed', 'Active', 'Completed')")['total'],
        'total_paid' => $db->fetchOne("SELECT COALESCE(SUM(pt.amount), 0) as total FROM payment_transactions pt JOIN loans l ON pt.loan_id = l.id WHERE pt.status = 'Completed'")['total']
    ];
    
    // Status breakdown
    $statusBreakdown = $db->fetchAll("SELECT status, COUNT(*) as count FROM loans GROUP BY status");
    $stats['status_breakdown'] = $statusBreakdown;
    
    // Recent loans
    $recentLoans = $db->fetchAll(
        "SELECT l.loan_number, l.amount, l.status, m.full_name as member_name, l.created_at 
         FROM loans l 
         LEFT JOIN members m ON l.member_id = m.id 
         ORDER BY l.created_at DESC 
         LIMIT 5"
    );
    $stats['recent_loans'] = $recentLoans;
    
    $response['success'] = true;
    $response['message'] = 'Loan statistics retrieved successfully';
    $response['data'] = $stats;
    
    echo json_encode($response);
}

function getInterestRate($amount, $creditScore) {
    // Base interest rate calculation based on amount and credit score
    $baseRate = 12; // 12% base rate
    
    // Adjust based on credit score
    if ($creditScore >= 80) {
        return $baseRate - 2; // 10%
    } elseif ($creditScore >= 60) {
        return $baseRate - 1; // 11%
    } elseif ($creditScore >= 40) {
        return $baseRate; // 12%
    } else {
        return $baseRate + 2; // 14%
    }
}

function generateLoanNumber() {
    return 'LN' . date('Ym') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function generateTransactionNumber() {
    return 'TXN' . date('YmdHis') . mt_rand(100, 999);
}

function handleLoanCancellation($db, $validator) {
    global $response;
    
    $user = requireAuth('admin');
    $loanId = (int)($_GET['id'] ?? 0);
    
    if ($loanId <= 0) {
        $response['message'] = 'Loan ID required';
        echo json_encode($response);
        return;
    }
    
    $loan = $db->fetchOne("SELECT * FROM loans WHERE id = ?", [$loanId]);
    if (!$loan) {
        $response['message'] = 'Loan not found';
        echo json_encode($response);
        return;
    }
    
    if (!in_array($loan['status'], ['Applied', 'Approved'])) {
        $response['message'] = 'Loan can only be cancelled when in Applied or Approved status';
        echo json_encode($response);
        return;
    }
    
    $updateData = [
        'status' => 'Cancelled',
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $db->update('loans', $updateData, 'id = ?', [$loanId]);
    
    logAudit('CANCEL', 'loans', $loanId, $loan, $updateData);
    
    $response['success'] = true;
    $response['message'] = 'Loan cancelled successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleGetMemberLoans($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $memberId = (int)($_GET['member_id'] ?? 0);
    
    if ($memberId <= 0) {
        $response['message'] = 'Member ID required';
        echo json_encode($response);
        return;
    }
    
    // Check permissions
    if ($user['role'] === 'member') {
        $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['user_id']]);
        if (!$member || $member['id'] !== $memberId) {
            $response['message'] = 'Unauthorized';
            echo json_encode($response);
            return;
        }
    }
    
    $loans = $db->fetchAll(
        "SELECT * FROM loans WHERE member_id = ? ORDER BY created_at DESC",
        [$memberId]
    );
    
    $response['success'] = true;
    $response['message'] = 'Member loans retrieved successfully';
    $response['data'] = $loans;
    
    echo json_encode($response);
}

function handleUpdateLoan($db, $validator) {
    global $response;
    
    $user = requireAuth('admin');
    $input = json_decode(file_get_contents('php://input'), true);
    $loanId = (int)($input['id'] ?? 0);
    
    if ($loanId <= 0) {
        $response['message'] = 'Loan ID required';
        echo json_encode($response);
        return;
    }
    
    $currentLoan = $db->fetchOne("SELECT * FROM loans WHERE id = ?", [$loanId]);
    if (!$currentLoan) {
        $response['message'] = 'Loan not found';
        echo json_encode($response);
        return;
    }
    
    // Only allow updating certain fields
    $allowedFields = ['purpose', 'collateral_type', 'collateral_value'];
    $updateData = [];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateData[$field] = $input[$field];
        }
    }
    
    if (empty($updateData)) {
        $response['message'] = 'No valid fields to update';
        echo json_encode($response);
        return;
    }
    
    $updateData['updated_at'] = date('Y-m-d H:i:s');
    
    $db->update('loans', $updateData, 'id = ?', [$loanId]);
    
    logAudit('UPDATE', 'loans', $loanId, $currentLoan, $updateData);
    
    $response['success'] = true;
    $response['message'] = 'Loan updated successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}
?>
