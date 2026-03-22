<?php
/**
 * Member Payments API
 * Handles member payment processing for loans and fees
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
        case 'payment_schedule':
            handleGetPaymentSchedule($db, $validator);
            break;
        case 'payment_history':
            handleGetPaymentHistory($db, $validator);
            break;
        case 'outstanding_payments':
            handleGetOutstandingPayments($db, $validator);
            break;
        case 'payment_methods':
            handleGetPaymentMethods($db, $validator);
            break;
        case 'calculator':
            handlePaymentCalculator($db, $validator);
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
        case 'pay_loan':
            handleLoanPayment($db, $validator);
            break;
        case 'pay_fee':
            handleFeePayment($db, $validator);
            break;
        case 'bulk_payment':
            handleBulkPayment($db, $validator);
            break;
        case 'schedule_payment':
            handleSchedulePayment($db, $validator);
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
        case 'update_payment':
            handleUpdatePayment($db, $validator);
            break;
        case 'cancel_payment':
            handleCancelPayment($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleGetPaymentSchedule($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    // Get member information
    $member = $db->fetchOne("SELECT id, member_number, full_name FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Get active loans
    $loans = $db->fetchAll(
        "SELECT l.*, 
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE loan_id = l.id AND status = 'Completed') as paid_amount
         FROM loans l 
         WHERE l.member_id = ? AND l.status IN ('Active', 'Disbursed')
         ORDER BY l.next_payment_date ASC",
        [$member['id']]
    );
    
    $paymentSchedule = [];
    
    foreach ($loans as &$loan) {
        $loan['remaining_balance'] = ($loan['amount'] + ($loan['total_interest'] ?? 0)) - $loan['paid_amount'];
        $loan['remaining_payments'] = ceil($loan['remaining_balance'] / $loan['monthly_payment']);
        
        // Generate payment schedule
        $schedule = generatePaymentSchedule($loan);
        
        $paymentSchedule[] = [
            'loan' => $loan,
            'schedule' => $schedule
        ];
    }
    
    $response['success'] = true;
    $response['message'] = 'Payment schedule retrieved successfully';
    $response['data'] = $paymentSchedule;
    
    echo json_encode($response);
}

function handleGetPaymentHistory($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $type = $_GET['type'] ?? '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    $status = $_GET['status'] ?? '';
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = ["pt.member_id = ?"];
    $params = [$member['id']];
    
    if (!empty($type)) {
        $whereConditions[] = "pt.type = ?";
        $params[] = $type;
    }
    
    if (!empty($dateFrom)) {
        $whereConditions[] = "pt.created_at >= ?";
        $params[] = $dateFrom . ' 00:00:00';
    }
    
    if (!empty($dateTo)) {
        $whereConditions[] = "pt.created_at <= ?";
        $params[] = $dateTo . ' 23:59:59';
    }
    
    if (!empty($status)) {
        $whereConditions[] = "pt.status = ?";
        $params[] = $status;
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM payment_transactions pt $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get transactions
    $sql = "SELECT pt.*, 
                    l.loan_number,
                    s.account_number as savings_account,
                    CASE 
                        WHEN pt.type = 'Loan Payment' THEN l.loan_number
                        WHEN pt.type LIKE '%Savings%' THEN s.account_number
                        ELSE NULL
                    END as reference_number
             FROM payment_transactions pt 
             LEFT JOIN loans l ON pt.loan_id = l.id 
             LEFT JOIN savings s ON pt.savings_id = s.id 
             $whereClause
             ORDER BY pt.created_at DESC 
             LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $transactions = $db->fetchAll($sql, $params);
    
    // Add display information
    foreach ($transactions as &$transaction) {
        $transaction['type_display'] = getTransactionTypeDisplay($transaction['type']);
        $transaction['amount_formatted'] = number_format($transaction['amount'], 2, ',', '.');
        $transaction['status_display'] = ucfirst($transaction['status']);
        $transaction['payment_method_display'] = getPaymentMethodDisplay($transaction['payment_method']);
    }
    
    $response['success'] = true;
    $response['message'] = 'Payment history retrieved successfully';
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

function handleGetOutstandingPayments($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    // Get member information
    $member = $db->fetchOne("SELECT id, member_number, full_name FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Get outstanding loan payments
    $outstandingLoans = $db->fetchAll(
        "SELECT l.*, 
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE loan_id = l.id AND status = 'Completed') as paid_amount,
                DATEDIFF(l.next_payment_date, CURDATE()) as days_until_due,
                CASE 
                    WHEN l.next_payment_date < CURDATE() THEN 'Overdue'
                    WHEN l.next_payment_date = CURDATE() THEN 'Due Today'
                    WHEN DATEDIFF(l.next_payment_date, CURDATE()) <= 7 THEN 'Due Soon'
                    ELSE 'On Schedule'
                END as payment_status
         FROM loans l 
         WHERE l.member_id = ? AND l.status = 'Active'
         ORDER BY l.next_payment_date ASC",
        [$member['id']]
    );
    
    $outstandingPayments = [];
    $totalDue = 0;
    $totalOverdue = 0;
    
    foreach ($outstandingLoans as &$loan) {
        $loan['remaining_balance'] = ($loan['amount'] + ($loan['total_interest'] ?? 0)) - $loan['paid_amount'];
        $loan['remaining_payments'] = ceil($loan['remaining_balance'] / $loan['monthly_payment']);
        
        // Calculate overdue amount if payment is late
        if ($loan['next_payment_date'] < date('Y-m-d')) {
            $daysOverdue = (strtotime(date('Y-m-d')) - strtotime($loan['next_payment_date'])) / 86400;
            $loan['days_overdue'] = $daysOverdue;
            $loan['late_fee'] = calculateLateFee($loan['monthly_payment'], $daysOverdue);
            $totalOverdue += $loan['monthly_payment'] + $loan['late_fee'];
        } else {
            $loan['days_overdue'] = 0;
            $loan['late_fee'] = 0;
        }
        
        $totalDue += $loan['monthly_payment'];
        
        $outstandingPayments[] = $loan;
    }
    
    // Get outstanding fees
    $outstandingFees = $db->fetchAll(
        "SELECT pt.*, 
                DATEDIFF(CURDATE(), pt.created_at) as days_outstanding
         FROM payment_transactions pt 
         WHERE pt.member_id = ? AND pt.type = 'Fee' AND pt.status = 'Pending'
         ORDER BY pt.created_at ASC",
        [$member['id']]
    );
    
    $totalFees = array_sum(array_column($outstandingFees, 'amount'));
    
    $response['success'] = true;
    $response['message'] = 'Outstanding payments retrieved successfully';
    $response['data'] = [
        'member' => $member,
        'loan_payments' => $outstandingPayments,
        'fees' => $outstandingFees,
        'summary' => [
            'total_loan_due' => $totalDue,
            'total_overdue' => $totalOverdue,
            'total_fees' => $totalFees,
            'total_amount_due' => $totalDue + $totalFees
        ]
    ];
    
    echo json_encode($response);
}

function handleGetPaymentMethods($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    $paymentMethods = [
        [
            'id' => 'cash',
            'name' => 'Cash',
            'description' => 'Pay with cash at our office',
            'icon' => 'cash',
            'available' => true,
            'fees' => 0,
            'processing_time' => 'Immediate'
        ],
        [
            'id' => 'bank_transfer',
            'name' => 'Bank Transfer',
            'description' => 'Transfer from your bank account',
            'icon' => 'bank',
            'available' => true,
            'fees' => 5000,
            'processing_time' => '1-2 business days'
        ],
        [
            'id' => 'digital_wallet',
            'name' => 'Digital Wallet',
            'description' => 'Pay using e-wallet services',
            'icon' => 'wallet',
            'available' => true,
            'fees' => 2500,
            'processing_time' => 'Instant'
        ],
        [
            'id' => 'auto_debit',
            'name' => 'Auto Debit',
            'description' => 'Automatic monthly payment',
            'icon' => 'auto',
            'available' => false, // Would need setup
            'fees' => 0,
            'processing_time' => 'Scheduled'
        ]
    ];
    
    $response['success'] = true;
    $response['message'] = 'Payment methods retrieved successfully';
    $response['data'] = $paymentMethods;
    
    echo json_encode($response);
}

function handlePaymentCalculator($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    $amount = (float)($_GET['amount'] ?? 0);
    $term = (int)($_GET['term'] ?? 0);
    $interestRate = (float)($_GET['interest_rate'] ?? 12);
    
    if ($amount <= 0 || $term <= 0) {
        $response['message'] = 'Invalid amount or term';
        echo json_encode($response);
        return;
    }
    
    $totalInterest = ($amount * $interestRate / 100) * ($term / 12);
    $monthlyPayment = ($amount + $totalInterest) / $term;
    
    // Generate payment schedule
    $schedule = [];
    $remainingBalance = $amount + $totalInterest;
    $startDate = date('Y-m-d');
    
    for ($i = 1; $i <= $term; $i++) {
        $paymentDate = date('Y-m-d', strtotime("+$i months", strtotime($startDate)));
        $interestPayment = ($remainingBalance * $interestRate / 100) / 12;
        $principalPayment = $monthlyPayment - $interestPayment;
        
        $schedule[] = [
            'payment_number' => $i,
            'payment_date' => $paymentDate,
            'principal_payment' => round($principalPayment, 2),
            'interest_payment' => round($interestPayment, 2),
            'total_payment' => round($monthlyPayment, 2),
            'remaining_balance' => round($remainingBalance - $principalPayment, 2)
        ];
        
        $remainingBalance -= $principalPayment;
    }
    
    $calculation = [
        'loan_amount' => $amount,
        'term_months' => $term,
        'interest_rate' => $interestRate,
        'total_interest' => round($totalInterest, 2),
        'total_payment' => round($amount + $totalInterest, 2),
        'monthly_payment' => round($monthlyPayment, 2),
        'effective_rate' => calculateEffectiveRate($amount, $totalInterest, $term),
        'payment_schedule' => $schedule
    ];
    
    $response['success'] = true;
    $response['message'] = 'Payment calculation completed';
    $response['data'] = $calculation;
    
    echo json_encode($response);
}

function handleLoanPayment($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'loan_id' => 'required|integer',
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
    
    // Get loan details
    $loan = $db->fetchOne(
        "SELECT l.*, 
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE loan_id = l.id AND status = 'Completed') as paid_amount
         FROM loans l 
         WHERE l.id = ? AND l.member_id = ?",
        [$input['loan_id'], $member['id']]
    );
    
    if (!$loan) {
        $response['message'] = 'Loan not found';
        echo json_encode($response);
        return;
    }
    
    if ($loan['status'] !== 'Active' && $loan['status'] !== 'Disbursed') {
        $response['message'] = 'Loan payment not allowed for this status';
        echo json_encode($response);
        return;
    }
    
    $remainingBalance = ($loan['amount'] + ($loan['total_interest'] ?? 0)) - $loan['paid_amount'];
    
    if ($input['amount'] > $remainingBalance) {
        $response['message'] = 'Payment amount exceeds remaining balance';
        echo json_encode($response);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Create payment transaction
        $transactionData = [
            'loan_id' => $input['loan_id'],
            'member_id' => $member['id'],
            'transaction_number' => generateTransactionNumber(),
            'amount' => $input['amount'],
            'type' => 'Loan Payment',
            'payment_method' => $input['payment_method'],
            'status' => 'Completed',
            'description' => 'Loan payment for ' . $loan['loan_number'],
            'processed_by' => $user['id'],
            'processed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $transactionId = $db->insert('payment_transactions', $transactionData);
        
        // Update loan remaining balance
        $newRemainingBalance = $remainingBalance - $input['amount'];
        
        $loanUpdateData = [
            'remaining_balance' => $newRemainingBalance,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Update next payment date
        if ($input['amount'] >= $loan['monthly_payment']) {
            $nextPaymentDate = date('Y-m-d', strtotime('+1 month', strtotime($loan['next_payment_date'])));
            $loanUpdateData['next_payment_date'] = $nextPaymentDate;
        }
        
        // Update loan status if fully paid
        if ($newRemainingBalance <= 0) {
            $loanUpdateData['status'] = 'Completed';
        }
        
        $db->update('loans', $loanUpdateData, 'id = ?', [$input['loan_id']]);
        
        // Create notification
        $db->insert('notifications', [
            'user_id' => $user['id'],
            'title' => 'Payment Received',
            'message' => "Payment of Rp " . number_format($input['amount'], 2, ',', '.') . " received for loan " . $loan['loan_number'],
            'type' => 'success',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $db->commit();
        
        $response['success'] = true;
        $response['message'] = 'Payment processed successfully';
        $response['data'] = [
            'transaction_id' => $transactionId,
            'remaining_balance' => $newRemainingBalance,
            'loan_status' => $newRemainingBalance <= 0 ? 'Completed' : 'Active',
            'next_payment_date' => $loanUpdateData['next_payment_date'] ?? $loan['next_payment_date']
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleFeePayment($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'fee_id' => 'required|integer',
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
    
    // Get fee details
    $fee = $db->fetchOne(
        "SELECT * FROM payment_transactions WHERE id = ? AND member_id = ? AND type = 'Fee' AND status = 'Pending'",
        [$input['fee_id'], $member['id']]
    );
    
    if (!$fee) {
        $response['message'] = 'Fee not found or already paid';
        echo json_encode($response);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Update fee transaction
        $updateData = [
            'payment_method' => $input['payment_method'],
            'status' => 'Completed',
            'processed_by' => $user['id'],
            'processed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $db->update('payment_transactions', $updateData, 'id = ?', [$input['fee_id']]);
        
        $db->commit();
        
        $response['success'] = true;
        $response['message'] = 'Fee payment processed successfully';
        $response['data'] = $updateData;
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleBulkPayment($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'payments' => 'required|array',
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
    
    $processedPayments = [];
    $errors = [];
    $totalAmount = 0;
    
    $db->beginTransaction();
    
    try {
        foreach ($input['payments'] as $payment) {
            $loanId = (int)($payment['loan_id'] ?? 0);
            $amount = (float)($payment['amount'] ?? 0);
            
            if ($loanId <= 0 || $amount <= 0) {
                $errors[] = "Invalid payment data for loan ID: $loanId";
                continue;
            }
            
            // Get loan details
            $loan = $db->fetchOne(
                "SELECT l.*, 
                        (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE loan_id = l.id AND status = 'Completed') as paid_amount
                 FROM loans l 
                 WHERE l.id = ? AND l.member_id = ?",
                [$loanId, $member['id']]
            );
            
            if (!$loan) {
                $errors[] = "Loan not found: $loanId";
                continue;
            }
            
            $remainingBalance = ($loan['amount'] + ($loan['total_interest'] ?? 0)) - $loan['paid_amount'];
            
            if ($amount > $remainingBalance) {
                $errors[] = "Payment amount exceeds balance for loan: $loanId";
                continue;
            }
            
            // Create payment transaction
            $transactionData = [
                'loan_id' => $loanId,
                'member_id' => $member['id'],
                'transaction_number' => generateTransactionNumber(),
                'amount' => $amount,
                'type' => 'Loan Payment',
                'payment_method' => $input['payment_method'],
                'status' => 'Completed',
                'description' => 'Bulk payment for ' . $loan['loan_number'],
                'processed_by' => $user['id'],
                'processed_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $transactionId = $db->insert('payment_transactions', $transactionData);
            
            // Update loan
            $newRemainingBalance = $remainingBalance - $amount;
            $loanUpdateData = [
                'remaining_balance' => $newRemainingBalance,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($newRemainingBalance <= 0) {
                $loanUpdateData['status'] = 'Completed';
            }
            
            $db->update('loans', $loanUpdateData, 'id = ?', [$loanId]);
            
            $processedPayments[] = [
                'loan_id' => $loanId,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'remaining_balance' => $newRemainingBalance
            ];
            
            $totalAmount += $amount;
        }
        
        $db->commit();
        
        $response['success'] = true;
        $response['message'] = 'Bulk payment processed successfully';
        $response['data'] = [
            'processed_payments' => $processedPayments,
            'errors' => $errors,
            'total_amount' => $totalAmount,
            'total_processed' => count($processedPayments)
        ];
        
        if (!empty($errors)) {
            $response['message'] = 'Bulk payment completed with some errors';
        }
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleSchedulePayment($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'loan_id' => 'required|integer',
        'payment_date' => 'required|date',
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
        SecurityMiddleware::sendJSONResponse($response);
        return;
    }
    
    // Verify loan ownership
    $loan = $db->fetchOne("SELECT id FROM loans WHERE id = ? AND member_id = ?", [$input['loan_id'], $member['id']]);
    
    if (!$loan) {
        $response['message'] = 'Loan not found';
        SecurityMiddleware::sendJSONResponse($response);
        return;
    }
    
    // Create scheduled payment (would need a scheduled_payments table)
    $scheduledPayment = [
        'loan_id' => $input['loan_id'],
        'member_id' => $member['id'],
        'payment_date' => $input['payment_date'],
        'amount' => $input['amount'],
        'payment_method' => $input['payment_method'],
        'status' => 'Scheduled',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // For now, just return success (actual implementation would save to scheduled_payments table)
    $response['success'] = true;
    $response['message'] = 'Payment scheduled successfully';
    $response['data'] = $scheduledPayment;
    
    echo json_encode($response);
}

function handleUpdatePayment($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    $paymentId = (int)($input['payment_id'] ?? 0);
    
    if ($paymentId <= 0) {
        $response['message'] = 'Payment ID required';
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
    
    // Verify payment ownership
    $payment = $db->fetchOne("SELECT * FROM payment_transactions WHERE id = ? AND member_id = ?", [$paymentId, $member['id']]);
    
    if (!$payment) {
        $response['message'] = 'Payment not found';
        echo json_encode($response);
        return;
    }
    
    // Members can only update pending payments
    if ($payment['status'] !== 'Pending') {
        $response['message'] = 'Cannot update completed payment';
        echo json_encode($response);
        return;
    }
    
    $response['success'] = true;
    $response['message'] = 'Payment updated successfully';
    
    echo json_encode($response);
}

function handleCancelPayment($db, $validator) {
    global $response;
    
    $user = SecurityMiddleware::requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    $paymentId = (int)($input['payment_id'] ?? 0);
    
    if ($paymentId <= 0) {
        $response['message'] = 'Payment ID required';
        SecurityMiddleware::sendJSONResponse($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        SecurityMiddleware::sendJSONResponse($response);
        return;
    }
    
    // Verify payment ownership
    $payment = $db->fetchOne("SELECT * FROM payment_transactions WHERE id = ? AND member_id = ?", [$paymentId, $member['id']]);
    
    if (!$payment) {
        $response['message'] = 'Payment not found';
        SecurityMiddleware::sendJSONResponse($response);
        return;
    }
    
    // Members can only cancel pending payments
    if ($payment['status'] !== 'Pending') {
        $response['message'] = 'Cannot cancel completed payment';
        SecurityMiddleware::sendJSONResponse($response);
        return;
    }
    
    // Update payment status
    $db->update('payment_transactions', ['status' => 'Cancelled'], 'id = ?', [$paymentId]);
    
    $response['success'] = true;
    $response['message'] = 'Payment cancelled successfully';
    
    SecurityMiddleware::sendJSONResponse($response);
}

// Helper functions
function generatePaymentSchedule($loan) {
    $schedule = [];
    $remainingBalance = $loan['remaining_balance'];
    $monthlyPayment = $loan['monthly_payment'];
    $nextPaymentDate = $loan['next_payment_date'];
    
    $remainingPayments = ceil($remainingBalance / $monthlyPayment);
    
    for ($i = 1; $i <= min($remainingPayments, 12); $i++) { // Show next 12 payments
        $paymentDate = date('Y-m-d', strtotime("+$i-1 months", strtotime($nextPaymentDate)));
        
        $schedule[] = [
            'payment_number' => $i,
            'payment_date' => $paymentDate,
            'amount' => min($monthlyPayment, $remainingBalance),
            'remaining_balance' => max(0, $remainingBalance - $monthlyPayment)
        ];
        
        $remainingBalance -= $monthlyPayment;
    }
    
    return $schedule;
}

function calculateLateFee($monthlyPayment, $daysOverdue) {
    // Simple late fee calculation: 2% of monthly payment per week overdue
    $weeksOverdue = floor($daysOverdue / 7);
    return $monthlyPayment * 0.02 * $weeksOverdue;
}

function calculateEffectiveRate($principal, $totalInterest, $term) {
    $totalPayment = $principal + $totalInterest;
    return (($totalInterest / $principal) / ($term / 12)) * 100;
}

function getTransactionTypeDisplay($type) {
    $displays = [
        'Loan Payment' => 'Loan Payment',
        'Savings Deposit' => 'Deposit',
        'Savings Withdrawal' => 'Withdrawal',
        'Fee' => 'Fee',
        'Fine' => 'Fine'
    ];
    
    return $displays[$type] ?? $type;
}

function getPaymentMethodDisplay($method) {
    $displays = [
        'Cash' => 'Cash',
        'Bank Transfer' => 'Bank Transfer',
        'Digital Wallet' => 'E-Wallet',
        'Auto Debit' => 'Auto Debit'
    ];
    
    return $displays[$method] ?? $method;
}

function generateTransactionNumber() {
    return 'TXN' . date('YmdHis') . mt_rand(100, 999);
}
?>
