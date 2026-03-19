<?php
/**
 * Phase 1 API - Savings Management
 * KSP Lam Gabe Jaya v2.0
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/Config.php';

try {
    $db = Config::getDatabase();
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_account_types':
            getAccountTypes($db);
            break;
            
        case 'get_accounts':
            getAccounts($db);
            break;
            
        case 'get_account':
            getAccount($db);
            break;
            
        case 'create_account':
            createAccount($db);
            break;
            
        case 'deposit':
            processDeposit($db);
            break;
            
        case 'withdraw':
            processWithdrawal($db);
            break;
            
        case 'get_transactions':
            getTransactions($db);
            break;
            
        case 'get_account_statement':
            getAccountStatement($db);
            break;
            
        case 'setup_auto_debit':
            setupAutoDebit($db);
            break;
            
        case 'get_auto_debit':
            getAutoDebit($db);
            break;
            
        default:
            sendResponse(false, 'Invalid action', null, 400);
    }
    
} catch (Exception $e) {
    sendResponse(false, 'Server error: ' . $e->getMessage(), null, 500);
}

/**
 * Get account types
 */
function getAccountTypes($db) {
    $stmt = $db->prepare("SELECT * FROM account_types WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $accountTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse(true, 'Account types retrieved', $accountTypes);
}

/**
 * Get accounts list
 */
function getAccounts($db) {
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    
    $search = $_GET['search'] ?? '';
    $accountType = $_GET['account_type_id'] ?? '';
    $status = $_GET['status'] ?? '';
    $memberId = $_GET['member_id'] ?? '';
    
    $where = [];
    $params = [];
    
    if (!empty($search)) {
        $where[] = "(a.account_number LIKE ? OR m.full_name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($accountType)) {
        $where[] = "a.account_type_id = ?";
        $params[] = $accountType;
    }
    
    if (!empty($status)) {
        $where[] = "a.status = ?";
        $params[] = $status;
    }
    
    if (!empty($memberId)) {
        $where[] = "a.member_id = ?";
        $params[] = $memberId;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM accounts a LEFT JOIN members m ON a.member_id = m.id $whereClause";
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get accounts
    $sql = "
        SELECT a.*, at.name as account_type_name, m.full_name, m.member_number,
               CASE 
                   WHEN a.balance > 0 AND a.status = 'Active' THEN 'Healthy'
                   WHEN a.balance = 0 AND a.status = 'Active' THEN 'Zero Balance'
                   WHEN a.status = 'Dormant' THEN 'Dormant'
                   WHEN a.status = 'Frozen' THEN 'Frozen'
                   ELSE a.status
               END as health_status
        FROM accounts a
        LEFT JOIN account_types at ON a.account_type_id = at.id
        LEFT JOIN members m ON a.member_id = m.id
        $whereClause
        ORDER BY a.opening_date DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $db->prepare($sql);
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse(true, 'Accounts retrieved', [
        'data' => $accounts,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

/**
 * Get account details
 */
function getAccount($db) {
    $accountId = intval($_GET['id'] ?? 0);
    
    if ($accountId <= 0) {
        sendResponse(false, 'Invalid account ID', null, 400);
        return;
    }
    
    $stmt = $db->prepare("
        SELECT a.*, at.name as account_type_name, m.full_name, m.member_number
        FROM accounts a
        LEFT JOIN account_types at ON a.account_type_id = at.id
        LEFT JOIN members m ON a.member_id = m.id
        WHERE a.id = ?
    ");
    $stmt->execute([$accountId]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$account) {
        sendResponse(false, 'Account not found', null, 404);
        return;
    }
    
    // Get recent transactions
    $stmt = $db->prepare("
        SELECT * FROM account_transactions 
        WHERE account_id = ?
        ORDER BY transaction_date DESC
        LIMIT 10
    ");
    $stmt->execute([$accountId]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $account['recent_transactions'] = $transactions;
    
    sendResponse(true, 'Account details retrieved', $account);
}

/**
 * Create new account
 */
function createAccount($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['member_id', 'account_type_id'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendResponse(false, "Field '$field' is required", null, 400);
            return;
        }
    }
    
    try {
        $db->beginTransaction();
        
        // Generate account number
        $accountNumber = generateAccountNumber($db);
        
        // Check member exists
        $stmt = $db->prepare("SELECT id FROM members WHERE id = ? AND status = 'Active'");
        $stmt->execute([$data['member_id']]);
        if (!$stmt->fetch()) {
            sendResponse(false, 'Member not found or inactive', null, 400);
            return;
        }
        
        // Check account type exists
        $stmt = $db->prepare("SELECT * FROM account_types WHERE id = ? AND is_active = 1");
        $stmt->execute([$data['account_type_id']]);
        $accountType = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$accountType) {
            sendResponse(false, 'Account type not found or inactive', null, 400);
            return;
        }
        
        // Check minimum balance requirement
        $initialBalance = floatval($data['initial_balance'] ?? 0);
        if ($initialBalance < $accountType['minimum_deposit']) {
            sendResponse(false, 'Initial balance below minimum requirement', null, 400);
            return;
        }
        
        // Create account
        $stmt = $db->prepare("
            INSERT INTO accounts (
                account_number, member_id, account_type_id, account_name, 
                balance, available_balance, interest_rate, opening_date, 
                maturity_date, status, notes, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', ?, ?)
        ");
        
        $stmt->execute([
            $accountNumber,
            $data['member_id'],
            $data['account_type_id'],
            $data['account_name'] ?? 'Account ' . $accountNumber,
            $initialBalance,
            $initialBalance,
            $accountType['interest_rate'],
            date('Y-m-d'),
            $data['maturity_date'] ?? null,
            1 // created_by
        ]);
        
        $accountId = $db->lastInsertId();
        
        // Create initial transaction if balance > 0
        if ($initialBalance > 0) {
            $stmt = $db->prepare("
                INSERT INTO account_transactions (
                    account_id, transaction_type, amount, balance_before, balance_after,
                    description, reference_number, transaction_date, teller_id
                ) VALUES (?, 'Deposit', ?, 0, ?, 'Initial deposit', ?, CURRENT_TIMESTAMP, ?)
            ");
            
            $referenceNumber = 'DEP' . date('YmdHis') . str_pad($accountId, 4, '0', STR_PAD_LEFT);
            
            $stmt->execute([
                $accountId,
                $initialBalance,
                $initialBalance,
                $referenceNumber,
                1 // teller_id
            ]);
        }
        
        $db->commit();
        
        sendResponse(true, 'Account created successfully', [
            'account_id' => $accountId,
            'account_number' => $accountNumber
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        sendResponse(false, 'Account creation failed: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Process deposit
 */
function processDeposit($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['account_id', 'amount'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendResponse(false, "Field '$field' is required", null, 400);
            return;
        }
    }
    
    $accountId = intval($data['account_id']);
    $amount = floatval($data['amount']);
    
    if ($amount <= 0) {
        sendResponse(false, 'Amount must be greater than 0', null, 400);
        return;
    }
    
    try {
        $db->beginTransaction();
        
        // Get account details
        $stmt = $db->prepare("SELECT * FROM accounts WHERE id = ? AND status = 'Active'");
        $stmt->execute([$accountId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$account) {
            sendResponse(false, 'Account not found or inactive', null, 404);
            return;
        }
        
        // Get account type for validation
        $stmt = $db->prepare("SELECT * FROM account_types WHERE id = ?");
        $stmt->execute([$account['account_type_id']]);
        $accountType = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check maximum deposit limit
        if ($accountType['maximum_deposit'] > 0 && $amount > $accountType['maximum_deposit']) {
            sendResponse(false, 'Amount exceeds maximum deposit limit', null, 400);
            return;
        }
        
        // Process deposit
        $balanceBefore = $account['balance'];
        $balanceAfter = $balanceBefore + $amount;
        
        // Insert transaction
        $stmt = $db->prepare("
            INSERT INTO account_transactions (
                account_id, transaction_type, amount, balance_before, balance_after,
                description, reference_number, transaction_date, teller_id
            ) VALUES (?, 'Deposit', ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?)
        ");
        
        $referenceNumber = 'DEP' . date('YmdHis') . str_pad($accountId, 4, '0', STR_PAD_LEFT);
        $description = $data['description'] ?? 'Deposit';
        
        $stmt->execute([
            $accountId,
            $amount,
            $balanceBefore,
            $balanceAfter,
            $description,
            $referenceNumber,
            1 // teller_id
        ]);
        
        // Update account balance (trigger will handle this)
        
        $db->commit();
        
        sendResponse(true, 'Deposit processed successfully', [
            'transaction_id' => $db->lastInsertId(),
            'reference_number' => $referenceNumber,
            'new_balance' => $balanceAfter
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        sendResponse(false, 'Deposit failed: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Process withdrawal
 */
function processWithdrawal($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['account_id', 'amount'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendResponse(false, "Field '$field' is required", null, 400);
            return;
        }
    }
    
    $accountId = intval($data['account_id']);
    $amount = floatval($data['amount']);
    
    if ($amount <= 0) {
        sendResponse(false, 'Amount must be greater than 0', null, 400);
        return;
    }
    
    try {
        $db->beginTransaction();
        
        // Get account details
        $stmt = $db->prepare("SELECT * FROM accounts WHERE id = ? AND status = 'Active'");
        $stmt->execute([$accountId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$account) {
            sendResponse(false, 'Account not found or inactive', null, 404);
            return;
        }
        
        // Check available balance
        if ($amount > $account['available_balance']) {
            sendResponse(false, 'Insufficient balance', null, 400);
            return;
        }
        
        // Get account type for validation
        $stmt = $db->prepare("SELECT * FROM account_types WHERE id = ?");
        $stmt->execute([$account['account_type_id']]);
        $accountType = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check minimum balance requirement
        $minBalance = $accountType['minimum_balance'];
        $newBalance = $account['balance'] - $amount;
        
        if ($newBalance < $minBalance) {
            sendResponse(false, 'Withdrawal would violate minimum balance requirement', null, 400);
            return;
        }
        
        // Check if approval is required
        $requiresApproval = $accountType['requires_approval'];
        $withdrawalFee = $accountType['withdrawal_fee'];
        $totalAmount = $amount + $withdrawalFee;
        
        if ($totalAmount > $account['available_balance']) {
            sendResponse(false, 'Insufficient balance including withdrawal fee', null, 400);
            return;
        }
        
        // Process withdrawal
        $balanceBefore = $account['balance'];
        $balanceAfter = $balanceBefore - $totalAmount;
        
        // Insert transaction
        $stmt = $db->prepare("
            INSERT INTO account_transactions (
                account_id, transaction_type, amount, balance_before, balance_after,
                description, reference_number, transaction_date, teller_id,
                approved_by, approved_date
            ) VALUES (?, 'Withdrawal', ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?, ?, ?)
        ");
        
        $referenceNumber = 'WDR' . date('YmdHis') . str_pad($accountId, 4, '0', STR_PAD_LEFT);
        $description = $data['description'] ?? 'Withdrawal';
        
        $stmt->execute([
            $accountId,
            $totalAmount,
            $balanceBefore,
            $balanceAfter,
            $description,
            $referenceNumber,
            1, // teller_id
            $requiresApproval ? null : 1, // approved_by
            $requiresApproval ? null : date('Y-m-d H:i:s') // approved_date
        ]);
        
        // Update account balance (trigger will handle this)
        
        $db->commit();
        
        sendResponse(true, 'Withdrawal processed successfully', [
            'transaction_id' => $db->lastInsertId(),
            'reference_number' => $referenceNumber,
            'amount' => $amount,
            'fee' => $withdrawalFee,
            'total' => $totalAmount,
            'new_balance' => $balanceAfter,
            'requires_approval' => $requiresApproval
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        sendResponse(false, 'Withdrawal failed: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Get transactions
 */
function getTransactions($db) {
    $accountId = intval($_GET['account_id'] ?? 0);
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    
    if ($accountId <= 0) {
        sendResponse(false, 'Invalid account ID', null, 400);
        return;
    }
    
    $transactionType = $_GET['transaction_type'] ?? '';
    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';
    
    $where = ["account_id = ?"];
    $params = [$accountId];
    
    if (!empty($transactionType)) {
        $where[] = "transaction_type = ?";
        $params[] = $transactionType;
    }
    
    if (!empty($startDate)) {
        $where[] = "DATE(transaction_date) >= ?";
        $params[] = $startDate;
    }
    
    if (!empty($endDate)) {
        $where[] = "DATE(transaction_date) <= ?";
        $params[] = $endDate;
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $where);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM account_transactions $whereClause";
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get transactions
    $sql = "
        SELECT * FROM account_transactions 
        $whereClause
        ORDER BY transaction_date DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $db->prepare($sql);
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse(true, 'Transactions retrieved', [
        'data' => $transactions,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

/**
 * Get account statement
 */
function getAccountStatement($db) {
    $accountId = intval($_GET['account_id'] ?? 0);
    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $endDate = $_GET['end_date'] ?? date('Y-m-t');
    
    if ($accountId <= 0) {
        sendResponse(false, 'Invalid account ID', null, 400);
        return;
    }
    
    // Get account details
    $stmt = $db->prepare("
        SELECT a.*, at.name as account_type_name, m.full_name, m.member_number
        FROM accounts a
        LEFT JOIN account_types at ON a.account_type_id = at.id
        LEFT JOIN members m ON a.member_id = m.id
        WHERE a.id = ?
    ");
    $stmt->execute([$accountId]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$account) {
        sendResponse(false, 'Account not found', null, 404);
        return;
    }
    
    // Get opening balance
    $stmt = $db->prepare("
        SELECT balance_after 
        FROM account_transactions 
        WHERE account_id = ? AND transaction_date < ?
        ORDER BY transaction_date DESC 
        LIMIT 1
    ");
    $stmt->execute([$accountId, $startDate . ' 00:00:00']);
    $openingBalance = $stmt->fetch(PDO::FETCH_ASSOC)['balance_after'] ?? 0;
    
    // Get transactions for the period
    $stmt = $db->prepare("
        SELECT * FROM account_transactions 
        WHERE account_id = ? AND DATE(transaction_date) BETWEEN ? AND ?
        ORDER BY transaction_date ASC
    ");
    $stmt->execute([$accountId, $startDate, $endDate]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate summary
    $totalDeposits = 0;
    $totalWithdrawals = 0;
    $totalFees = 0;
    $totalInterest = 0;
    
    foreach ($transactions as $transaction) {
        switch ($transaction['transaction_type']) {
            case 'Deposit':
            case 'Transfer In':
                $totalDeposits += $transaction['amount'];
                break;
            case 'Withdrawal':
            case 'Transfer Out':
                $totalWithdrawals += $transaction['amount'];
                break;
            case 'Fee':
                $totalFees += $transaction['amount'];
                break;
            case 'Interest':
                $totalInterest += $transaction['amount'];
                break;
        }
    }
    
    $closingBalance = $openingBalance + $totalDeposits - $totalWithdrawals + $totalInterest - $totalFees;
    
    sendResponse(true, 'Account statement retrieved', [
        'account' => $account,
        'period' => [
            'start_date' => $startDate,
            'end_date' => $endDate
        ],
        'summary' => [
            'opening_balance' => $openingBalance,
            'total_deposits' => $totalDeposits,
            'total_withdrawals' => $totalWithdrawals,
            'total_interest' => $totalInterest,
            'total_fees' => $totalFees,
            'closing_balance' => $closingBalance
        ],
        'transactions' => $transactions
    ]);
}

/**
 * Setup auto debit
 */
function setupAutoDebit($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['member_id', 'account_id', 'debit_amount', 'debit_day'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendResponse(false, "Field '$field' is required", null, 400);
            return;
        }
    }
    
    try {
        // Check if auto debit already exists
        $stmt = $db->prepare("
            SELECT id FROM auto_debit_config 
            WHERE member_id = ? AND account_id = ? AND is_active = 1
        ");
        $stmt->execute([$data['member_id'], $data['account_id']]);
        
        if ($stmt->fetch()) {
            sendResponse(false, 'Auto debit already exists for this account', null, 400);
            return;
        }
        
        // Calculate next debit date
        $nextDebitDate = calculateNextDebitDate($data['debit_day']);
        
        // Insert auto debit config
        $stmt = $db->prepare("
            INSERT INTO auto_debit_config (
                member_id, account_id, debit_amount, debit_day, start_date, 
                end_date, next_debit_date, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 1)
        ");
        
        $stmt->execute([
            $data['member_id'],
            $data['account_id'],
            $data['debit_amount'],
            $data['debit_day'],
            $data['start_date'] ?? date('Y-m-d'),
            $data['end_date'] ?? null,
            $nextDebitDate
        ]);
        
        sendResponse(true, 'Auto debit setup successful', [
            'auto_debit_id' => $db->lastInsertId(),
            'next_debit_date' => $nextDebitDate
        ]);
        
    } catch (Exception $e) {
        sendResponse(false, 'Auto debit setup failed: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Get auto debit configurations
 */
function getAutoDebit($db) {
    $memberId = intval($_GET['member_id'] ?? 0);
    
    if ($memberId <= 0) {
        sendResponse(false, 'Invalid member ID', null, 400);
        return;
    }
    
    $stmt = $db->prepare("
        SELECT adc.*, a.account_number, at.name as account_type_name
        FROM auto_debit_config adc
        LEFT JOIN accounts a ON adc.account_id = a.id
        LEFT JOIN account_types at ON a.account_type_id = at.id
        WHERE adc.member_id = ? AND adc.is_active = 1
        ORDER BY adc.created_at DESC
    ");
    $stmt->execute([$memberId]);
    $autoDebits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse(true, 'Auto debit configurations retrieved', $autoDebits);
}

/**
 * Generate account number
 */
function generateAccountNumber($db) {
    $year = date('Y');
    
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM accounts a
        INNER JOIN account_types at ON a.account_type_id = at.id
        WHERE YEAR(a.opening_date) = ?
    ");
    $stmt->execute([$year]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    return 'ACC' . $year . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
}

/**
 * Calculate next debit date
 */
function calculateNextDebitDate($debitDay) {
    $currentDate = new Date();
    $currentDay = (int)$currentDate->format('d');
    $currentMonth = (int)$currentDate->format('m');
    $currentYear = (int)$currentDate->format('Y');
    
    // If debit day for this month has passed, schedule for next month
    if ($currentDay > $debitDay) {
        if ($currentMonth == 12) {
            $currentMonth = 1;
            $currentYear++;
        } else {
            $currentMonth++;
        }
    }
    
    // Handle months with fewer days
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentYear, $currentMonth);
    $actualDebitDay = min($debitDay, $daysInMonth);
    
    return sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $actualDebitDay);
}

/**
 * Send JSON response
 */
function sendResponse($success, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if (!$success) {
        $response['errors'] = [];
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}
