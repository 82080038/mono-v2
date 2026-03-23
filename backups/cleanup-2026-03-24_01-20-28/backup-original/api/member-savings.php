<?php
/**
 * Member Savings API
 * Handles member savings account management
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
function requireAuth($role = 'member') {
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
    
    if ($user['role'] !== $role && $user['role'] !== 'admin') {
        throw new Exception('Member access required');
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
        case 'accounts':
            handleGetSavingsAccounts($db, $validator);
            break;
        case 'account_detail':
            handleGetSavingsAccount($db, $validator);
            break;
        case 'balance':
            handleGetBalance($db, $validator);
            break;
        case 'transactions':
            handleGetTransactions($db, $validator);
            break;
        case 'statement':
            handleGetStatement($db, $validator);
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
        case 'create_account':
            handleCreateSavingsAccount($db, $validator);
            break;
        case 'deposit':
            handleDeposit($db, $validator);
            break;
        case 'withdraw':
            handleWithdraw($db, $validator);
            break;
        case 'transfer':
            handleTransfer($db, $validator);
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
        case 'update_account':
            handleUpdateSavingsAccount($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleGetSavingsAccounts($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    // Get member information
    $member = $db->fetchOne("SELECT id, member_number, full_name FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    $savingsAccounts = $db->fetchAll(
        "SELECT s.*, 
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE savings_id = s.id AND type = 'Savings Deposit' AND status = 'Completed') as total_deposits,
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE savings_id = s.id AND type = 'Savings Withdrawal' AND status = 'Completed') as total_withdrawals,
                (SELECT COUNT(*) FROM payment_transactions WHERE savings_id = s.id AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())) as monthly_transactions
         FROM savings s 
         WHERE s.member_id = ? 
         ORDER BY s.created_at DESC",
        [$member['id']]
    );
    
    // Calculate current balance for each account
    $totalBalance = 0;
    foreach ($savingsAccounts as &$savings) {
        $savings['current_balance'] = $savings['total_deposits'] - $savings['total_withdrawals'];
        $totalBalance += $savings['current_balance'];
        
        // Calculate monthly interest (simplified)
        $savings['monthly_interest'] = ($savings['current_balance'] * $savings['interest_rate'] / 100) / 12;
        
        // Get last transaction date
        $lastTransaction = $db->fetchOne(
            "SELECT created_at FROM payment_transactions WHERE savings_id = ? ORDER BY created_at DESC LIMIT 1",
            [$savings['id']]
        );
        $savings['last_transaction_date'] = $lastTransaction['created_at'] ?? null;
    }
    
    // Get available account types for new accounts
    $availableTypes = getAvailableAccountTypes($member);
    
    $response['success'] = true;
    $response['message'] = 'Savings accounts retrieved successfully';
    $response['data'] = [
        'member' => $member,
        'accounts' => $savingsAccounts,
        'summary' => [
            'total_accounts' => count($savingsAccounts),
            'total_balance' => $totalBalance,
            'active_accounts' => count(array_filter($savingsAccounts, fn($s) => $s['status'] === 'Active'))
        ],
        'available_types' => $availableTypes
    ];
    
    echo json_encode($response);
}

function handleGetSavingsAccount($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $accountId = (int)($_GET['id'] ?? 0);
    
    if ($accountId <= 0) {
        $response['message'] = 'Account ID required';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Get account details
    $account = $db->fetchOne(
        "SELECT s.*, 
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE savings_id = s.id AND type = 'Savings Deposit' AND status = 'Completed') as total_deposits,
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE savings_id = s.id AND type = 'Savings Withdrawal' AND status = 'Completed') as total_withdrawals
         FROM savings s 
         WHERE s.id = ? AND s.member_id = ?",
        [$accountId, $member['id']]
    );
    
    if (!$account) {
        $response['message'] = 'Account not found';
        echo json_encode($response);
        return;
    }
    
    $account['current_balance'] = $account['total_deposits'] - $account['total_withdrawals'];
    
    // Get recent transactions
    $account['recent_transactions'] = $db->fetchAll(
        "SELECT * FROM payment_transactions 
         WHERE savings_id = ? 
         ORDER BY created_at DESC 
         LIMIT 10",
        [$accountId]
    );
    
    // Calculate monthly statistics
    $account['monthly_stats'] = [
        'deposits' => $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total, COUNT(*) as count 
             FROM payment_transactions 
             WHERE savings_id = ? AND type = 'Savings Deposit' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status = 'Completed'",
            [$accountId]
        ),
        'withdrawals' => $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total, COUNT(*) as count 
             FROM payment_transactions 
             WHERE savings_id = ? AND type = 'Savings Withdrawal' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status = 'Completed'",
            [$accountId]
        )
    ];
    
    $response['success'] = true;
    $response['message'] = 'Account details retrieved successfully';
    $response['data'] = $account;
    
    echo json_encode($response);
}

function handleGetBalance($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $accountId = (int)($_GET['account_id'] ?? 0);
    
    if ($accountId <= 0) {
        $response['message'] = 'Account ID required';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Verify account ownership
    $account = $db->fetchOne("SELECT * FROM savings WHERE id = ? AND member_id = ?", [$accountId, $member['id']]);
    
    if (!$account) {
        $response['message'] = 'Account not found';
        echo json_encode($response);
        return;
    }
    
    // Calculate current balance
    $totalDeposits = $db->fetchOne(
        "SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE savings_id = ? AND type = 'Savings Deposit' AND status = 'Completed'",
        [$accountId]
    )['total'];
    
    $totalWithdrawals = $db->fetchOne(
        "SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE savings_id = ? AND type = 'Savings Withdrawal' AND status = 'Completed'",
        [$accountId]
    )['total'];
    
    $currentBalance = $totalDeposits - $totalWithdrawals;
    
    $balanceData = [
        'account_id' => $accountId,
        'account_number' => $account['account_number'],
        'account_type' => $account['type'],
        'current_balance' => $currentBalance,
        'total_deposits' => $totalDeposits,
        'total_withdrawals' => $totalWithdrawals,
        'interest_rate' => $account['interest_rate'],
        'monthly_interest' => ($currentBalance * $account['interest_rate'] / 100) / 12,
        'last_updated' => date('Y-m-d H:i:s')
    ];
    
    $response['success'] = true;
    $response['message'] = 'Balance retrieved successfully';
    $response['data'] = $balanceData;
    
    echo json_encode($response);
}

function handleGetTransactions($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $accountId = (int)($_GET['account_id'] ?? 0);
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $type = $_GET['type'] ?? '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    
    if ($accountId <= 0) {
        $response['message'] = 'Account ID required';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Verify account ownership
    $account = $db->fetchOne("SELECT id FROM savings WHERE id = ? AND member_id = ?", [$accountId, $member['id']]);
    
    if (!$account) {
        $response['message'] = 'Account not found';
        echo json_encode($response);
        return;
    }
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = ["savings_id = ? AND status = 'Completed'"];
    $params = [$accountId];
    
    if (!empty($type)) {
        $whereConditions[] = "type = ?";
        $params[] = $type;
    }
    
    if (!empty($dateFrom)) {
        $whereConditions[] = "created_at >= ?";
        $params[] = $dateFrom . ' 00:00:00';
    }
    
    if (!empty($dateTo)) {
        $whereConditions[] = "created_at <= ?";
        $params[] = $dateTo . ' 23:59:59';
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM payment_transactions $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get transactions
    $sql = "SELECT * FROM payment_transactions $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $transactions = $db->fetchAll($sql, $params);
    
    // Add transaction metadata
    foreach ($transactions as &$transaction) {
        $transaction['type_display'] = getTransactionTypeDisplay($transaction['type']);
        $transaction['amount_formatted'] = number_format($transaction['amount'], 2, ',', '.');
        $transaction['status_display'] = ucfirst($transaction['status']);
    }
    
    $response['success'] = true;
    $response['message'] = 'Transactions retrieved successfully';
    $response['data'] = [
        'transactions' => $transactions,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    echo json_encode($response);
}

function handleGetStatement($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $accountId = (int)($_GET['account_id'] ?? 0);
    $month = (int)($_GET['month'] ?? date('n'));
    $year = (int)($_GET['year'] ?? date('Y'));
    
    if ($accountId <= 0) {
        $response['message'] = 'Account ID required';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Verify account ownership
    $account = $db->fetchOne("SELECT * FROM savings WHERE id = ? AND member_id = ?", [$accountId, $member['id']]);
    
    if (!$account) {
        $response['message'] = 'Account not found';
        echo json_encode($response);
        return;
    }
    
    // Get statement period
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate = date('Y-m-t', strtotime($startDate));
    
    // Get opening balance
    $openingBalance = $db->fetchOne(
        "SELECT COALESCE(SUM(CASE WHEN type = 'Savings Deposit' THEN amount ELSE -amount END), 0) as balance 
         FROM payment_transactions 
         WHERE savings_id = ? AND status = 'Completed' AND created_at < ?",
        [$accountId, $startDate . ' 00:00:00']
    )['balance'];
    
    // Get transactions for the period
    $transactions = $db->fetchAll(
        "SELECT * FROM payment_transactions 
         WHERE savings_id = ? AND status = 'Completed' AND created_at BETWEEN ? AND ? 
         ORDER BY created_at ASC",
        [$accountId, $startDate . ' 00:00:00', $endDate . ' 23:59:59']
    );
    
    // Calculate running balance
    $runningBalance = $openingBalance;
    foreach ($transactions as &$transaction) {
        if ($transaction['type'] === 'Savings Deposit') {
            $runningBalance += $transaction['amount'];
        } else {
            $runningBalance -= $transaction['amount'];
        }
        $transaction['running_balance'] = $runningBalance;
        $transaction['type_display'] = getTransactionTypeDisplay($transaction['type']);
    }
    
    // Get closing balance
    $closingBalance = $runningBalance;
    
    // Calculate monthly interest
    $monthlyInterest = ($closingBalance * $account['interest_rate'] / 100) / 12;
    
    $statement = [
        'account' => [
            'account_number' => $account['account_number'],
            'account_type' => $account['type'],
            'member_name' => $member['full_name']
        ],
        'period' => [
            'month' => $month,
            'year' => $year,
            'start_date' => $startDate,
            'end_date' => $endDate
        ],
        'balances' => [
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'monthly_interest' => $monthlyInterest
        ],
        'summary' => [
            'total_deposits' => array_sum(array_column(array_filter($transactions, fn($t) => $t['type'] === 'Savings Deposit'), 'amount')),
            'total_withdrawals' => array_sum(array_column(array_filter($transactions, fn($t) => $t['type'] === 'Savings Withdrawal'), 'amount')),
            'transaction_count' => count($transactions)
        ],
        'transactions' => $transactions
    ];
    
    $response['success'] = true;
    $response['message'] = 'Statement generated successfully';
    $response['data'] = $statement;
    
    echo json_encode($response);
}

function handleCreateSavingsAccount($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'type' => 'required|in:Regular,Fixed,Special,Emergency',
        'initial_deposit' => 'required|numeric|min:100000'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne("SELECT id, member_number, full_name, membership_type, credit_score FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Check if member already has account of this type
    $existing = $db->fetchOne(
        "SELECT id FROM savings WHERE member_id = ? AND type = ? AND status = 'Active'",
        [$member['id'], $input['type']]
    );
    
    if ($existing) {
        $response['message'] = 'You already have an active savings account of this type';
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
            'member_id' => $member['id'],
            'account_number' => $accountNumber,
            'amount' => $input['initial_deposit'],
            'type' => $input['type'],
            'interest_rate' => $interestRate,
            'status' => 'Active',
            'balance' => $input['initial_deposit'],
            'last_deposit_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $savingsId = $db->insert('savings', $savingsData);
        
        // Create initial deposit transaction
        $transactionData = [
            'savings_id' => $savingsId,
            'member_id' => $member['id'],
            'transaction_number' => generateTransactionNumber(),
            'amount' => $input['initial_deposit'],
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
        
        $response['success'] = true;
        $response['message'] = 'Savings account created successfully';
        $response['data'] = [
            'savings_id' => $savingsId,
            'account_number' => $accountNumber,
            'interest_rate' => $interestRate,
            'initial_balance' => $input['initial_deposit']
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleDeposit($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
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
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Verify account ownership
    $savings = $db->fetchOne("SELECT * FROM savings WHERE id = ? AND member_id = ?", [$input['savings_id'], $member['id']]);
    
    if (!$savings) {
        $response['message'] = 'Savings account not found';
        echo json_encode($response);
        return;
    }
    
    if ($savings['status'] !== 'Active') {
        $response['message'] = 'Cannot deposit to inactive account';
        echo json_encode($response);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Create deposit transaction
        $transactionData = [
            'savings_id' => $input['savings_id'],
            'member_id' => $member['id'],
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
    
    $user = requireAuth('member');
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
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Verify account ownership
    $savings = $db->fetchOne("SELECT * FROM savings WHERE id = ? AND member_id = ?", [$input['savings_id'], $member['id']]);
    
    if (!$savings) {
        $response['message'] = 'Savings account not found';
        echo json_encode($response);
        return;
    }
    
    if ($savings['status'] !== 'Active') {
        $response['message'] = 'Cannot withdraw from inactive account';
        echo json_encode($response);
        return;
    }
    
    // Check sufficient balance
    $totalDeposits = $db->fetchOne(
        "SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE savings_id = ? AND type = 'Savings Deposit' AND status = 'Completed'",
        [$input['savings_id']]
    )['total'];
    
    $totalWithdrawals = $db->fetchOne(
        "SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE savings_id = ? AND type = 'Savings Withdrawal' AND status = 'Completed'",
        [$input['savings_id']]
    )['total'];
    
    $currentBalance = $totalDeposits - $totalWithdrawals;
    
    if ($input['amount'] > $currentBalance) {
        $response['message'] = 'Insufficient balance';
        echo json_encode($response);
        return;
    }
    
    // For fixed savings, check if withdrawal is allowed
    if ($savings['type'] === 'Fixed') {
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
            'member_id' => $member['id'],
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
        $newBalance = $currentBalance - $input['amount'];
        
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

function handleTransfer($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'from_savings_id' => 'required|integer',
        'to_member_number' => 'required|string',
        'amount' => 'required|numeric|min:10000',
        'description' => 'string'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Verify from account ownership
    $fromSavings = $db->fetchOne("SELECT * FROM savings WHERE id = ? AND member_id = ?", [$input['from_savings_id'], $member['id']]);
    
    if (!$fromSavings) {
        $response['message'] = 'Source savings account not found';
        echo json_encode($response);
        return;
    }
    
    // Get target member
    $toMember = $db->fetchOne("SELECT id, full_name FROM members WHERE member_number = ? AND status = 'Active'", [$input['to_member_number']]);
    
    if (!$toMember) {
        $response['message'] = 'Target member not found';
        echo json_encode($response);
        return;
    }
    
    // Get target savings account (default to first active account)
    $toSavings = $db->fetchOne("SELECT * FROM savings WHERE member_id = ? AND status = 'Active' ORDER BY created_at ASC LIMIT 1", [$toMember['id']]);
    
    if (!$toSavings) {
        $response['message'] = 'Target member has no active savings account';
        echo json_encode($response);
        return;
    }
    
    // Check sufficient balance
    $totalDeposits = $db->fetchOne(
        "SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE savings_id = ? AND type = 'Savings Deposit' AND status = 'Completed'",
        [$input['from_savings_id']]
    )['total'];
    
    $totalWithdrawals = $db->fetchOne(
        "SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE savings_id = ? AND type = 'Savings Withdrawal' AND status = 'Completed'",
        [$input['from_savings_id']]
    )['total'];
    
    $currentBalance = $totalDeposits - $totalWithdrawals;
    
    if ($input['amount'] > $currentBalance) {
        $response['message'] = 'Insufficient balance';
        echo json_encode($response);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Create withdrawal transaction
        $withdrawalData = [
            'savings_id' => $input['from_savings_id'],
            'member_id' => $member['id'],
            'transaction_number' => generateTransactionNumber(),
            'amount' => $input['amount'],
            'type' => 'Savings Withdrawal',
            'payment_method' => 'Transfer',
            'status' => 'Completed',
            'description' => 'Transfer to ' . $toMember['full_name'] . ' (' . $input['to_member_number'] . ')',
            'processed_by' => $user['id'],
            'processed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $withdrawalId = $db->insert('payment_transactions', $withdrawalData);
        
        // Create deposit transaction for target
        $depositData = [
            'savings_id' => $toSavings['id'],
            'member_id' => $toMember['id'],
            'transaction_number' => generateTransactionNumber(),
            'amount' => $input['amount'],
            'type' => 'Savings Deposit',
            'payment_method' => 'Transfer',
            'status' => 'Completed',
            'description' => 'Transfer from ' . $member['full_name'] . ' (' . $member['member_number'] . ')',
            'processed_by' => $user['id'],
            'processed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $depositId = $db->insert('payment_transactions', $depositData);
        
        // Update from account balance
        $fromNewBalance = $currentBalance - $input['amount'];
        $db->update('savings', ['balance' => $fromNewBalance, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$input['from_savings_id']]);
        
        // Update to account balance
        $toTotalDeposits = $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE savings_id = ? AND type = 'Savings Deposit' AND status = 'Completed'",
            [$toSavings['id']]
        )['total'];
        
        $toTotalWithdrawals = $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE savings_id = ? AND type = 'Savings Withdrawal' AND status = 'Completed'",
            [$toSavings['id']]
        )['total'];
        
        $toNewBalance = $toTotalDeposits - $toTotalWithdrawals + $input['amount'];
        $db->update('savings', ['balance' => $toNewBalance, 'last_deposit_date' => date('Y-m-d'), 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$toSavings['id']]);
        
        $db->commit();
        
        $response['success'] = true;
        $response['message'] = 'Transfer processed successfully';
        $response['data'] = [
            'withdrawal_id' => $withdrawalId,
            'deposit_id' => $depositId,
            'from_balance' => $fromNewBalance,
            'to_balance' => $toNewBalance,
            'target_member' => $toMember['full_name']
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleUpdateSavingsAccount($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    $accountId = (int)($input['id'] ?? 0);
    
    if ($accountId <= 0) {
        $response['message'] = 'Account ID required';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Verify account ownership
    $account = $db->fetchOne("SELECT * FROM savings WHERE id = ? AND member_id = ?", [$accountId, $member['id']]);
    
    if (!$account) {
        $response['message'] = 'Account not found';
        echo json_encode($response);
        return;
    }
    
    // Members can only update limited fields
    $allowedFields = []; // No fields currently allowed for member updates
    
    if (empty($allowedFields)) {
        $response['message'] = 'No fields can be updated by members';
        echo json_encode($response);
        return;
    }
    
    $response['success'] = true;
    $response['message'] = 'Account updated successfully';
    
    echo json_encode($response);
}

// Helper functions
function getAvailableAccountTypes($member) {
    $types = [
        'Regular' => [
            'name' => 'Regular Savings',
            'description' => 'Standard savings account with basic interest',
            'min_deposit' => 100000,
            'interest_rate' => 5.0,
            'withdrawal_rules' => 'Anytime'
        ],
        'Fixed' => [
            'name' => 'Fixed Deposit',
            'description' => 'Higher interest rate with 6-month lock period',
            'min_deposit' => 500000,
            'interest_rate' => 7.0,
            'withdrawal_rules' => 'After 6 months'
        ],
        'Special' => [
            'name' => 'Special Savings',
            'description' => 'Premium account with enhanced benefits',
            'min_deposit' => 1000000,
            'interest_rate' => 8.0,
            'withdrawal_rules' => 'Anytime'
        ],
        'Emergency' => [
            'name' => 'Emergency Fund',
            'description' => 'Quick access for emergency needs',
            'min_deposit' => 50000,
            'interest_rate' => 3.0,
            'withdrawal_rules' => 'Anytime'
        ]
    ];
    
    // Adjust interest rates based on membership type and credit score
    foreach ($types as $type => &$details) {
        $details['interest_rate'] = getSavingsInterestRate($type, $member['credit_score']);
    }
    
    return $types;
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

function getTransactionTypeDisplay($type) {
    $displays = [
        'Savings Deposit' => 'Deposit',
        'Savings Withdrawal' => 'Withdrawal',
        'Loan Payment' => 'Loan Payment',
        'Fee' => 'Fee',
        'Fine' => 'Fine'
    ];
    
    return $displays[$type] ?? $type;
}

function generateSavingsAccountNumber($type) {
    $prefix = strtoupper(substr($type, 0, 1));
    return $prefix . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function generateTransactionNumber() {
    return 'TXN' . date('YmdHis') . mt_rand(100, 999);
}
?>
