<?php
/**
 * Enhanced Savings API with CRUD operations
 * Supports savings account management with proper authentication
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
            handleListSavings($db, $validator);
            break;
        case 'detail':
            handleGetSavings($db, $validator);
            break;
        case 'member_savings':
            handleGetMemberSavings($db, $validator);
            break;
        case 'statistics':
            handleSavingsStatistics($db, $validator);
            break;
        case 'balance':
            handleGetBalance($db, $validator);
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
        case 'create':
            handleCreateSavings($db, $validator);
            break;
        case 'deposit':
            handleDeposit($db, $validator);
            break;
        case 'withdraw':
            handleWithdraw($db, $validator);
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
            handleUpdateSavings($db, $validator);
            break;
        case 'status':
            handleUpdateSavingsStatus($db, $validator);
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
        case 'close':
            handleCloseSavings($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleListSavings($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $status = $_GET['status'] ?? '';
    $type = $_GET['type'] ?? '';
    $memberId = (int)($_GET['member_id'] ?? 0);
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = [];
    $params = [];
    
    if (!empty($status)) {
        $whereConditions[] = "s.status = ?";
        $params[] = $status;
    }
    
    if (!empty($type)) {
        $whereConditions[] = "s.type = ?";
        $params[] = $type;
    }
    
    if ($memberId > 0) {
        $whereConditions[] = "s.member_id = ?";
        $params[] = $memberId;
    }
    
    // Members can only see their own savings
    if ($user['role'] === 'member') {
        $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['user_id']]);
        if ($member) {
            $whereConditions[] = "s.member_id = ?";
            $params[] = $member['id'];
        }
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM savings s LEFT JOIN members m ON s.member_id = m.id $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get savings
    $sql = "SELECT s.*, m.full_name as member_name, m.member_number
            FROM savings s 
            LEFT JOIN members m ON s.member_id = m.id 
            $whereClause
            ORDER BY s.created_at DESC 
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $savings = $db->fetchAll($sql, $params);
    
    $response['success'] = true;
    $response['message'] = 'Savings retrieved successfully';
    $response['data'] = [
        'savings' => $savings,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    echo json_encode($response);
}

function handleGetSavings($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $savingsId = (int)($_GET['id'] ?? 0);
    
    if ($savingsId <= 0) {
        $response['message'] = 'Savings ID required';
        echo json_encode($response);
        return;
    }
    
    // Check permissions
    if ($user['role'] === 'member') {
        $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['user_id']]);
        if ($member) {
            $savings = $db->fetchOne(
                "SELECT s.*, m.full_name as member_name, m.member_number
                 FROM savings s 
                 LEFT JOIN members m ON s.member_id = m.id 
                 WHERE s.id = ? AND s.member_id = ?",
                [$savingsId, $member['id']]
            );
        }
    } else {
        $savings = $db->fetchOne(
            "SELECT s.*, m.full_name as member_name, m.member_number
             FROM savings s 
             LEFT JOIN members m ON s.member_id = m.id 
             WHERE s.id = ?",
            [$savingsId]
        );
    }
    
    if (!$savings) {
        $response['message'] = 'Savings not found';
        echo json_encode($response);
        return;
    }
    
    // Get transaction history
    $savings['transactions'] = $db->fetchAll(
        "SELECT * FROM payment_transactions WHERE savings_id = ? ORDER BY created_at DESC",
        [$savingsId]
    );
    
    // Calculate current balance
    $totalDeposits = $db->fetchOne(
        "SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE savings_id = ? AND type = 'Savings Deposit' AND status = 'Completed'",
        [$savingsId]
    )['total'];
    
    $totalWithdrawals = $db->fetchOne(
        "SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE savings_id = ? AND type = 'Savings Withdrawal' AND status = 'Completed'",
        [$savingsId]
    )['total'];
    
    $savings['total_deposits'] = $totalDeposits;
    $savings['total_withdrawals'] = $totalWithdrawals;
    $savings['current_balance'] = $totalDeposits - $totalWithdrawals;
    
    $response['success'] = true;
    $response['message'] = 'Savings retrieved successfully';
    $response['data'] = $savings;
    
    echo json_encode($response);
}

function handleCreateSavings($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'member_id' => 'required|integer',
        'type' => 'required|in:Regular,Fixed,Special,Emergency',
        'initial_amount' => 'required|numeric|min:100000'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Check member exists
    $member = $db->fetchOne("SELECT * FROM members WHERE id = ? AND is_active = 1", [$input['member_id']]);
    if (!$member) {
        $response['message'] = 'Member not found or inactive';
        echo json_encode($response);
        return;
    }
    
    // Check if member already has savings of this type
    $existing = $db->fetchOne(
        "SELECT id FROM savings WHERE member_id = ? AND type = ? AND status = 'Active'",
        [$input['member_id'], $input['type']]
    );
    
    if ($existing) {
        $response['message'] = 'Member already has an active savings account of this type';
        echo json_encode($response);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Generate account number
        $accountNumber = generateSavingsAccountNumber($input['type']);
        
        // Get interest rate based on type and member credit score
        $interestRate = getSavingsInterestRate($input['type'], $member['credit_score']);
        
        // Create savings account
        $savingsData = [
            'member_id' => $input['member_id'],
            'account_number' => $accountNumber,
            'amount' => $input['initial_amount'],
            'type' => $input['type'],
            'interest_rate' => $interestRate,
            'status' => 'Active',
            'balance' => $input['initial_amount'],
            'last_deposit_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $savingsId = $db->insert('savings', $savingsData);
        
        // Create initial deposit transaction
        $transactionData = [
            'savings_id' => $savingsId,
            'member_id' => $input['member_id'],
            'transaction_number' => generateTransactionNumber(),
            'amount' => $input['initial_amount'],
            'type' => 'Savings Deposit',
            'payment_method' => 'Cash',
            'status' => 'Completed',
            'description' => 'Initial deposit for ' . $input['type'] . ' savings account',
            'processed_by' => $user['id'],
            'processed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $db->insert('payment_transactions', $transactionData);
        
        $db->commit();
        
        logAudit('CREATE', 'savings', $savingsId, null, $savingsData);
        
        $response['success'] = true;
        $response['message'] = 'Savings account created successfully';
        $response['data'] = [
            'savings_id' => $savingsId,
            'account_number' => $accountNumber,
            'interest_rate' => $interestRate,
            'balance' => $input['initial_amount']
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleDeposit($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'savings_id' => 'required|integer',
        'amount' => 'required|numeric|min:10000',
        'payment_method' => 'required|in:Cash,Bank Transfer,Digital Wallet,Auto Debit'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    $savings = $db->fetchOne("SELECT * FROM savings WHERE id = ?", [$input['savings_id']]);
    if (!$savings) {
        $response['message'] = 'Savings account not found';
        echo json_encode($response);
        return;
    }
    
    if ($savings['status'] !== 'Active') {
        $response['message'] = 'Cannot deposit to inactive savings account';
        echo json_encode($response);
        return;
    }
    
    // Check permissions
    if ($user['role'] === 'member') {
        $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['user_id']]);
        if (!$member || $member['id'] !== $savings['member_id']) {
            $response['message'] = 'Unauthorized';
            echo json_encode($response);
            return;
        }
    }
    
    $db->beginTransaction();
    
    try {
        // Create deposit transaction
        $transactionData = [
            'savings_id' => $input['savings_id'],
            'member_id' => $savings['member_id'],
            'transaction_number' => generateTransactionNumber(),
            'amount' => $input['amount'],
            'type' => 'Savings Deposit',
            'payment_method' => $input['payment_method'],
            'status' => 'Completed',
            'description' => 'Deposit to ' . $savings['type'] . ' savings account',
            'processed_by' => $user['id'],
            'processed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $transactionId = $db->insert('payment_transactions', $transactionData);
        
        // Update savings balance
        $totalDeposits = $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE savings_id = ? AND type = 'Savings Deposit' AND status = 'Completed'",
            [$input['savings_id']]
        )['total'];
        
        $totalWithdrawals = $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE savings_id = ? AND type = 'Savings Withdrawal' AND status = 'Completed'",
            [$input['savings_id']]
        )['total'];
        
        $newBalance = $totalDeposits - $totalWithdrawals;
        
        $savingsUpdateData = [
            'balance' => $newBalance,
            'last_deposit_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $db->update('savings', $savingsUpdateData, 'id = ?', [$input['savings_id']]);
        
        $db->commit();
        
        logAudit('DEPOSIT', 'payment_transactions', $transactionId, null, $transactionData);
        
        $response['success'] = true;
        $response['message'] = 'Deposit processed successfully';
        $response['data'] = [
            'transaction_id' => $transactionId,
            'new_balance' => $newBalance
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleWithdraw($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'savings_id' => 'required|integer',
        'amount' => 'required|numeric|min:10000',
        'payment_method' => 'required|in:Cash,Bank Transfer,Digital Wallet'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    $savings = $db->fetchOne("SELECT * FROM savings WHERE id = ?", [$input['savings_id']]);
    if (!$savings) {
        $response['message'] = 'Savings account not found';
        echo json_encode($response);
        return;
    }
    
    if ($savings['status'] !== 'Active') {
        $response['message'] = 'Cannot withdraw from inactive savings account';
        echo json_encode($response);
        return;
    }
    
    // Check permissions
    if ($user['role'] === 'member') {
        $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['user_id']]);
        if (!$member || $member['id'] !== $savings['member_id']) {
            $response['message'] = 'Unauthorized';
            echo json_encode($response);
            return;
        }
    }
    
    // Check sufficient balance
    if ($input['amount'] > $savings['balance']) {
        $response['message'] = 'Insufficient balance';
        echo json_encode($response);
        return;
    }
    
    // For fixed savings, check if withdrawal is allowed (usually only after maturity)
    if ($savings['type'] === 'Fixed') {
        // Check if at least 6 months have passed
        $sixMonthsAgo = date('Y-m-d', strtotime('-6 months'));
        if ($savings['created_at'] > $sixMonthsAgo) {
            $response['message'] = 'Fixed savings cannot be withdrawn before 6 months';
            echo json_encode($response);
            return;
        }
    }
    
    $db->beginTransaction();
    
    try {
        // Create withdrawal transaction
        $transactionData = [
            'savings_id' => $input['savings_id'],
            'member_id' => $savings['member_id'],
            'transaction_number' => generateTransactionNumber(),
            'amount' => $input['amount'],
            'type' => 'Savings Withdrawal',
            'payment_method' => $input['payment_method'],
            'status' => 'Completed',
            'description' => 'Withdrawal from ' . $savings['type'] . ' savings account',
            'processed_by' => $user['id'],
            'processed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $transactionId = $db->insert('payment_transactions', $transactionData);
        
        // Update savings balance
        $newBalance = $savings['balance'] - $input['amount'];
        
        $savingsUpdateData = [
            'balance' => $newBalance,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Close account if balance is zero
        if ($newBalance <= 0) {
            $savingsUpdateData['status'] = 'Closed';
        }
        
        $db->update('savings', $savingsUpdateData, 'id = ?', [$input['savings_id']]);
        
        $db->commit();
        
        logAudit('WITHDRAW', 'payment_transactions', $transactionId, null, $transactionData);
        
        $response['success'] = true;
        $response['message'] = 'Withdrawal processed successfully';
        $response['data'] = [
            'transaction_id' => $transactionId,
            'new_balance' => $newBalance,
            'account_status' => $newBalance <= 0 ? 'Closed' : 'Active'
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleGetBalance($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $savingsId = (int)($_GET['savings_id'] ?? 0);
    
    if ($savingsId <= 0) {
        $response['message'] = 'Savings ID required';
        echo json_encode($response);
        return;
    }
    
    $savings = $db->fetchOne("SELECT * FROM savings WHERE id = ?", [$savingsId]);
    if (!$savings) {
        $response['message'] = 'Savings account not found';
        echo json_encode($response);
        return;
    }
    
    // Check permissions
    if ($user['role'] === 'member') {
        $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['user_id']]);
        if (!$member || $member['id'] !== $savings['member_id']) {
            $response['message'] = 'Unauthorized';
            echo json_encode($response);
            return;
        }
    }
    
    // Calculate current balance
    $totalDeposits = $db->fetchOne(
        "SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE savings_id = ? AND type = 'Savings Deposit' AND status = 'Completed'",
        [$savingsId]
    )['total'];
    
    $totalWithdrawals = $db->fetchOne(
        "SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE savings_id = ? AND type = 'Savings Withdrawal' AND status = 'Completed'",
        [$savingsId]
    )['total'];
    
    $currentBalance = $totalDeposits - $totalWithdrawals;
    
    $response['success'] = true;
    $response['message'] = 'Balance retrieved successfully';
    $response['data'] = [
        'account_number' => $savings['account_number'],
        'type' => $savings['type'],
        'interest_rate' => $savings['interest_rate'],
        'total_deposits' => $totalDeposits,
        'total_withdrawals' => $totalWithdrawals,
        'current_balance' => $currentBalance,
        'status' => $savings['status']
    ];
    
    echo json_encode($response);
}

function handleGetMemberSavings($db, $validator) {
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
    
    $savings = $db->fetchAll(
        "SELECT * FROM savings WHERE member_id = ? ORDER BY created_at DESC",
        [$memberId]
    );
    
    // Calculate total balance
    $totalBalance = 0;
    foreach ($savings as &$saving) {
        $totalDeposits = $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE savings_id = ? AND type = 'Savings Deposit' AND status = 'Completed'",
            [$saving['id']]
        )['total'];
        
        $totalWithdrawals = $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE savings_id = ? AND type = 'Savings Withdrawal' AND status = 'Completed'",
            [$saving['id']]
        )['total'];
        
        $saving['current_balance'] = $totalDeposits - $totalWithdrawals;
        $totalBalance += $saving['current_balance'];
    }
    
    $response['success'] = true;
    $response['message'] = 'Member savings retrieved successfully';
    $response['data'] = [
        'savings' => $savings,
        'total_balance' => $totalBalance
    ];
    
    echo json_encode($response);
}

function handleSavingsStatistics($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    // Basic statistics
    $stats = [
        'total_savings' => $db->fetchOne("SELECT COUNT(*) as count FROM savings")['count'],
        'active_savings' => $db->fetchOne("SELECT COUNT(*) as count FROM savings WHERE status = 'Active'")['count'],
        'total_balance' => $db->fetchOne("SELECT COALESCE(SUM(balance), 0) as total FROM savings")['total'],
        'total_deposits' => $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE type = 'Savings Deposit' AND status = 'Completed'")['total'],
        'total_withdrawals' => $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE type = 'Savings Withdrawal' AND status = 'Completed'")['total']
    ];
    
    // Type breakdown
    $typeBreakdown = $db->fetchAll("SELECT type, COUNT(*) as count, COALESCE(SUM(balance), 0) as total_balance FROM savings GROUP BY type");
    $stats['type_breakdown'] = $typeBreakdown;
    
    // Recent transactions
    $recentTransactions = $db->fetchAll(
        "SELECT pt.*, s.account_number, m.full_name as member_name 
         FROM payment_transactions pt 
         LEFT JOIN savings s ON pt.savings_id = s.id 
         LEFT JOIN members m ON s.member_id = m.id 
         WHERE pt.type LIKE '%Savings%' 
         ORDER BY pt.created_at DESC 
         LIMIT 5"
    );
    $stats['recent_transactions'] = $recentTransactions;
    
    $response['success'] = true;
    $response['message'] = 'Savings statistics retrieved successfully';
    $response['data'] = $stats;
    
    echo json_encode($response);
}

function handleUpdateSavings($db, $validator) {
    global $response;
    
    $user = requireAuth('admin');
    $input = json_decode(file_get_contents('php://input'), true);
    $savingsId = (int)($input['id'] ?? 0);
    
    if ($savingsId <= 0) {
        $response['message'] = 'Savings ID required';
        echo json_encode($response);
        return;
    }
    
    $currentSavings = $db->fetchOne("SELECT * FROM savings WHERE id = ?", [$savingsId]);
    if (!$currentSavings) {
        $response['message'] = 'Savings not found';
        echo json_encode($response);
        return;
    }
    
    // Only allow updating certain fields
    $allowedFields = ['type', 'interest_rate'];
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
    
    $db->update('savings', $updateData, 'id = ?', [$savingsId]);
    
    logAudit('UPDATE', 'savings', $savingsId, $currentSavings, $updateData);
    
    $response['success'] = true;
    $response['message'] = 'Savings updated successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleUpdateSavingsStatus($db, $validator) {
    global $response;
    
    $user = requireAuth('admin');
    $input = json_decode(file_get_contents('php://input'), true);
    $savingsId = (int)($input['id'] ?? 0);
    $status = $input['status'] ?? '';
    
    if ($savingsId <= 0 || !in_array($status, ['Active', 'Inactive', 'Frozen', 'Closed'])) {
        $response['message'] = 'Invalid savings ID or status';
        echo json_encode($response);
        return;
    }
    
    $currentSavings = $db->fetchOne("SELECT * FROM savings WHERE id = ?", [$savingsId]);
    if (!$currentSavings) {
        $response['message'] = 'Savings not found';
        echo json_encode($response);
        return;
    }
    
    $updateData = [
        'status' => $status,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $db->update('savings', $updateData, 'id = ?', [$savingsId]);
    
    logAudit('STATUS_CHANGE', 'savings', $savingsId, $currentSavings, $updateData);
    
    $response['success'] = true;
    $response['message'] = 'Savings status updated successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleCloseSavings($db, $validator) {
    global $response;
    
    $user = requireAuth('admin');
    $savingsId = (int)($_GET['id'] ?? 0);
    
    if ($savingsId <= 0) {
        $response['message'] = 'Savings ID required';
        echo json_encode($response);
        return;
    }
    
    $currentSavings = $db->fetchOne("SELECT * FROM savings WHERE id = ?", [$savingsId]);
    if (!$currentSavings) {
        $response['message'] = 'Savings not found';
        echo json_encode($response);
        return;
    }
    
    // Check if balance is zero
    if ($currentSavings['balance'] > 0) {
        $response['message'] = 'Cannot close savings account with positive balance';
        echo json_encode($response);
        return;
    }
    
    $updateData = [
        'status' => 'Closed',
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $db->update('savings', $updateData, 'id = ?', [$savingsId]);
    
    logAudit('CLOSE', 'savings', $savingsId, $currentSavings, $updateData);
    
    $response['success'] = true;
    $response['message'] = 'Savings account closed successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function generateSavingsAccountNumber($type) {
    $prefix = strtoupper(substr($type, 0, 1));
    return $prefix . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function getSavingsInterestRate($type, $creditScore) {
    $baseRates = [
        'Regular' => 5.0,
        'Fixed' => 7.0,
        'Special' => 8.0,
        'Emergency' => 3.0
    ];
    
    $baseRate = $baseRates[$type] ?? 5.0;
    
    // Adjust based on credit score
    if ($creditScore >= 80) {
        return $baseRate + 1.0;
    } elseif ($creditScore >= 60) {
        return $baseRate + 0.5;
    } else {
        return $baseRate;
    }
}

function generateTransactionNumber() {
    return 'TXN' . date('YmdHis') . mt_rand(100, 999);
}
?>
