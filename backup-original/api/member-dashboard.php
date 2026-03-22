<?php
/**
 * Member Dashboard API
 * Provides personalized dashboard data for members
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
        case 'overview':
            handleDashboardOverview($db, $validator);
            break;
        case 'financial_summary':
            handleFinancialSummary($db, $validator);
            break;
        case 'recent_activities':
            handleRecentActivities($db, $validator);
            break;
        case 'notifications':
            handleNotifications($db, $validator);
            break;
        case 'quick_actions':
            handleQuickActions($db, $validator);
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
        case 'mark_notification_read':
            handleMarkNotificationRead($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleDashboardOverview($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    // Get member information
    $member = $db->fetchOne(
        "SELECT m.*, u.username, u.email 
         FROM members m 
         JOIN users u ON m.user_id = u.id 
         WHERE u.id = ?",
        [$user['id']]
    );
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Get savings accounts
    $savingsAccounts = $db->fetchAll(
        "SELECT s.*, 
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE savings_id = s.id AND type = 'Savings Deposit' AND status = 'Completed') as total_deposits,
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE savings_id = s.id AND type = 'Savings Withdrawal' AND status = 'Completed') as total_withdrawals
         FROM savings s 
         WHERE s.member_id = ? AND s.status = 'Active'",
        [$member['id']]
    );
    
    // Calculate total savings balance
    $totalSavings = 0;
    foreach ($savingsAccounts as &$savings) {
        $savings['current_balance'] = $savings['total_deposits'] - $savings['total_withdrawals'];
        $totalSavings += $savings['current_balance'];
    }
    
    // Get loans
    $loans = $db->fetchAll(
        "SELECT l.*, 
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE loan_id = l.id AND status = 'Completed') as paid_amount
         FROM loans l 
         WHERE l.member_id = ? 
         ORDER BY l.created_at DESC",
        [$member['id']]
    );
    
    // Calculate loan statistics
    $activeLoans = 0;
    $totalLoanAmount = 0;
    $totalPaid = 0;
    $totalRemaining = 0;
    
    foreach ($loans as &$loan) {
        $loan['remaining_balance'] = ($loan['amount'] + ($loan['total_interest'] ?? 0)) - $loan['paid_amount'];
        $totalLoanAmount += $loan['amount'];
        $totalPaid += $loan['paid_amount'];
        $totalRemaining += $loan['remaining_balance'];
        
        if (in_array($loan['status'], ['Active', 'Disbursed'])) {
            $activeLoans++;
        }
    }
    
    // Get recent transactions
    $recentTransactions = $db->fetchAll(
        "SELECT pt.*, s.account_number as savings_account, l.loan_number
         FROM payment_transactions pt 
         LEFT JOIN savings s ON pt.savings_id = s.id 
         LEFT JOIN loans l ON pt.loan_id = l.id 
         WHERE pt.member_id = ? AND pt.status = 'Completed'
         ORDER BY pt.created_at DESC 
         LIMIT 5",
        [$member['id']]
    );
    
    // Get unread notifications count
    $unreadNotifications = $db->fetchOne(
        "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0",
        [$user['id']]
    )['count'];
    
    // Get reward points
    $rewardPoints = $db->fetchOne(
        "SELECT COALESCE(SUM(CASE WHEN transaction_type = 'Earned' THEN points ELSE -points END), 0) as total_points
         FROM reward_points 
         WHERE member_id = ?",
        [$member['id']]
    )['total_points'];
    
    // Get next payment due
    $nextPayment = $db->fetchOne(
        "SELECT l.loan_number, l.next_payment_date, l.monthly_payment
         FROM loans l 
         WHERE l.member_id = ? AND l.status = 'Active' 
         ORDER BY l.next_payment_date ASC 
         LIMIT 1",
        [$member['id']]
    );
    
    $dashboard = [
        'member' => [
            'id' => $member['id'],
            'member_number' => $member['member_number'],
            'full_name' => $member['full_name'],
            'email' => $member['email'],
            'phone' => $member['phone'],
            'membership_type' => $member['membership_type'],
            'credit_score' => $member['credit_score'],
            'join_date' => $member['join_date']
        ],
        'financial_summary' => [
            'total_savings' => $totalSavings,
            'active_savings_accounts' => count($savingsAccounts),
            'active_loans' => $activeLoans,
            'total_loan_amount' => $totalLoanAmount,
            'total_paid' => $totalPaid,
            'total_remaining' => $totalRemaining,
            'reward_points' => $rewardPoints
        ],
        'savings_accounts' => $savingsAccounts,
        'loans' => array_slice($loans, 0, 5), // Limit to 5 most recent loans
        'recent_transactions' => $recentTransactions,
        'next_payment' => $nextPayment,
        'unread_notifications' => $unreadNotifications,
        'quick_stats' => [
            'total_transactions_this_month' => $db->fetchOne(
                "SELECT COUNT(*) as count 
                 FROM payment_transactions 
                 WHERE member_id = ? AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())",
                [$member['id']]
            )['count'],
            'last_login' => $user['last_login'],
            'membership_days' => (strtotime(date('Y-m-d')) - strtotime($member['join_date'])) / 86400
        ]
    ];
    
    $response['success'] = true;
    $response['message'] = 'Dashboard overview retrieved successfully';
    $response['data'] = $dashboard;
    
    echo json_encode($response);
}

function handleFinancialSummary($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    // Get member information
    $member = $db->fetchOne(
        "SELECT id, member_number, full_name 
         FROM members 
         WHERE user_id = ?",
        [$user['id']]
    );
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Get detailed savings information
    $savingsAccounts = $db->fetchAll(
        "SELECT s.*, 
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE savings_id = s.id AND type = 'Savings Deposit' AND status = 'Completed') as total_deposits,
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE savings_id = s.id AND type = 'Savings Withdrawal' AND status = 'Completed') as total_withdrawals,
                (SELECT COUNT(*) FROM payment_transactions WHERE savings_id = s.id AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())) as monthly_transactions
         FROM savings s 
         WHERE s.member_id = ? AND s.status = 'Active'",
        [$member['id']]
    );
    
    $totalSavings = 0;
    $monthlyDeposits = 0;
    $monthlyWithdrawals = 0;
    
    foreach ($savingsAccounts as &$savings) {
        $savings['current_balance'] = $savings['total_deposits'] - $savings['total_withdrawals'];
        $totalSavings += $savings['current_balance'];
        
        // Calculate monthly deposits/withdrawals
        $monthlyDeposits += $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total 
             FROM payment_transactions 
             WHERE savings_id = ? AND type = 'Savings Deposit' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status = 'Completed'",
            [$savings['id']]
        )['total'];
        
        $monthlyWithdrawals += $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total 
             FROM payment_transactions 
             WHERE savings_id = ? AND type = 'Savings Withdrawal' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status = 'Completed'",
            [$savings['id']]
        )['total'];
    }
    
    // Get detailed loan information
    $loans = $db->fetchAll(
        "SELECT l.*, 
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE loan_id = l.id AND status = 'Completed') as paid_amount,
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE loan_id = l.id AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status = 'Completed') as monthly_payments
         FROM loans l 
         WHERE l.member_id = ? 
         ORDER BY l.created_at DESC",
        [$member['id']]
    );
    
    $activeLoans = 0;
    $totalLoanAmount = 0;
    $totalPaid = 0;
    $totalRemaining = 0;
    $monthlyPayments = 0;
    
    foreach ($loans as &$loan) {
        $loan['remaining_balance'] = ($loan['amount'] + ($loan['total_interest'] ?? 0)) - $loan['paid_amount'];
        $totalLoanAmount += $loan['amount'];
        $totalPaid += $loan['paid_amount'];
        $totalRemaining += $loan['remaining_balance'];
        $monthlyPayments += $loan['monthly_payments'];
        
        if (in_array($loan['status'], ['Active', 'Disbursed'])) {
            $activeLoans++;
        }
    }
    
    // Get transaction trends for last 6 months
    $transactionTrends = $db->fetchAll(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                type, 
                COUNT(*) as count, 
                COALESCE(SUM(amount), 0) as total
         FROM payment_transactions 
         WHERE member_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND status = 'Completed'
         GROUP BY DATE_FORMAT(created_at, '%Y-%m'), type
         ORDER BY month",
        [$member['id']]
    );
    
    // Organize transaction trends by month
    $trendsByMonth = [];
    foreach ($transactionTrends as $trend) {
        if (!isset($trendsByMonth[$trend['month']])) {
            $trendsByMonth[$trend['month']] = [
                'deposits' => 0,
                'withdrawals' => 0,
                'loan_payments' => 0,
                'fees' => 0
            ];
        }
        
        switch ($trend['type']) {
            case 'Savings Deposit':
                $trendsByMonth[$trend['month']]['deposits'] = $trend['total'];
                break;
            case 'Savings Withdrawal':
                $trendsByMonth[$trend['month']]['withdrawals'] = $trend['total'];
                break;
            case 'Loan Payment':
                $trendsByMonth[$trend['month']]['loan_payments'] = $trend['total'];
                break;
            case 'Fee':
                $trendsByMonth[$trend['month']]['fees'] = $trend['total'];
                break;
        }
    }
    
    $financialSummary = [
        'member' => $member,
        'savings' => [
            'total_balance' => $totalSavings,
            'active_accounts' => count($savingsAccounts),
            'monthly_deposits' => $monthlyDeposits,
            'monthly_withdrawals' => $monthlyWithdrawals,
            'accounts' => $savingsAccounts
        ],
        'loans' => [
            'active_loans' => $activeLoans,
            'total_amount' => $totalLoanAmount,
            'total_paid' => $totalPaid,
            'total_remaining' => $totalRemaining,
            'monthly_payments' => $monthlyPayments,
            'details' => $loans
        ],
        'transaction_trends' => $trendsByMonth,
        'summary' => [
            'net_worth' => $totalSavings - $totalRemaining,
            'monthly_net_change' => ($monthlyDeposits + $monthlyPayments) - $monthlyWithdrawals,
            'debt_to_savings_ratio' => $totalSavings > 0 ? ($totalRemaining / $totalSavings) : 0
        ]
    ];
    
    $response['success'] = true;
    $response['message'] = 'Financial summary retrieved successfully';
    $response['data'] = $financialSummary;
    
    echo json_encode($response);
}

function handleRecentActivities($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    $activities = [];
    
    // Get recent transactions
    $transactions = $db->fetchAll(
        "SELECT 'transaction' as activity_type, pt.*, 
                CASE 
                    WHEN pt.type = 'Savings Deposit' THEN 'Made a deposit'
                    WHEN pt.type = 'Savings Withdrawal' THEN 'Made a withdrawal'
                    WHEN pt.type = 'Loan Payment' THEN 'Made a loan payment'
                    ELSE 'Financial transaction'
                END as description
         FROM payment_transactions pt 
         WHERE pt.member_id = ? AND pt.status = 'Completed'
         ORDER BY pt.created_at DESC 
         LIMIT 10",
        [$member['id']]
    );
    
    foreach ($transactions as &$transaction) {
        $transaction['icon'] = getTransactionIcon($transaction['type']);
        $transaction['color'] = getTransactionColor($transaction['type']);
    }
    
    // Get recent loan applications
    $loanApplications = $db->fetchAll(
        "SELECT 'loan_application' as activity_type, l.*, 'Applied for a loan' as description
         FROM loans l 
         WHERE l.member_id = ? 
         ORDER BY l.created_at DESC 
         LIMIT 5",
        [$member['id']]
    );
    
    foreach ($loanApplications as &$loan) {
        $loan['icon'] = 'loan';
        $loan['color'] = 'blue';
    }
    
    // Get recent savings account openings
    $savingsOpenings = $db->fetchAll(
        "SELECT 'savings_opening' as activity_type, s.*, 'Opened savings account' as description
         FROM savings s 
         WHERE s.member_id = ? 
         ORDER BY s.created_at DESC 
         LIMIT 3",
        [$member['id']]
    );
    
    foreach ($savingsOpenings as &$savings) {
        $savings['icon'] = 'savings';
        $savings['color'] = 'green';
    }
    
    // Combine and sort all activities
    $allActivities = array_merge($transactions, $loanApplications, $savingsOpenings);
    
    // Sort by created_at
    usort($allActivities, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // Limit to 15 most recent
    $activities = array_slice($allActivities, 0, 15);
    
    $response['success'] = true;
    $response['message'] = 'Recent activities retrieved successfully';
    $response['data'] = $activities;
    
    echo json_encode($response);
}

function handleNotifications($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $unread = $_GET['unread'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = ["user_id = ?"];
    $params = [$user['id']];
    
    if ($unread === 'true') {
        $whereConditions[] = "is_read = 0";
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM notifications $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get notifications
    $sql = "SELECT * FROM notifications $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $notifications = $db->fetchAll($sql, $params);
    
    $response['success'] = true;
    $response['message'] = 'Notifications retrieved successfully';
    $response['data'] = [
        'notifications' => $notifications,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    echo json_encode($response);
}

function handleQuickActions($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    // Get member information
    $member = $db->fetchOne("SELECT id, membership_type, credit_score FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Determine available quick actions based on member status
    $quickActions = [
        [
            'id' => 'apply_loan',
            'title' => 'Apply for Loan',
            'description' => 'Submit a new loan application',
            'icon' => 'loan',
            'color' => 'blue',
            'available' => true,
            'url' => '/member/ajukan-pinjaman.html'
        ],
        [
            'id' => 'deposit_savings',
            'title' => 'Deposit Savings',
            'description' => 'Add money to your savings account',
            'icon' => 'deposit',
            'color' => 'green',
            'available' => true,
            'url' => '/member/my-savings.html'
        ],
        [
            'id' => 'make_payment',
            'title' => 'Make Payment',
            'description' => 'Pay your loan installments',
            'icon' => 'payment',
            'color' => 'orange',
            'available' => false,
            'url' => '/member/pembayaran.html'
        ],
        [
            'id' => 'view_transactions',
            'title' => 'Transaction History',
            'description' => 'View your transaction history',
            'icon' => 'history',
            'color' => 'purple',
            'available' => true,
            'url' => '/member/transaction-history.html'
        ],
        [
            'id' => 'update_profile',
            'title' => 'Update Profile',
            'description' => 'Update your personal information',
            'icon' => 'profile',
            'color' => 'indigo',
            'available' => true,
            'url' => '/member/my-profile.html'
        ]
    ];
    
    // Check if member has active loans
    $activeLoans = $db->fetchOne(
        "SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status IN ('Active', 'Disbursed')",
        [$member['id']]
    )['count'];
    
    if ($activeLoans > 0) {
        // Enable payment action
        foreach ($quickActions as &$action) {
            if ($action['id'] === 'make_payment') {
                $action['available'] = true;
            }
        }
    }
    
    // Check if member has savings accounts
    $savingsAccounts = $db->fetchOne(
        "SELECT COUNT(*) as count FROM savings WHERE member_id = ? AND status = 'Active'",
        [$member['id']]
    )['count'];
    
    if ($savingsAccounts === 0) {
        // Disable deposit action if no savings accounts
        foreach ($quickActions as &$action) {
            if ($action['id'] === 'deposit_savings') {
                $action['available'] = false;
                $action['description'] = 'No active savings accounts';
            }
        }
    }
    
    $response['success'] = true;
    $response['message'] = 'Quick actions retrieved successfully';
    $response['data'] = $quickActions;
    
    echo json_encode($response);
}

function handleMarkNotificationRead($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $notificationId = (int)($input['notification_id'] ?? 0);
    $markAll = (bool)($input['mark_all'] ?? false);
    
    if ($markAll) {
        // Mark all notifications as read
        $db->update('notifications', ['is_read' => true, 'read_at' => date('Y-m-d H:i:s')], 'user_id = ?', [$user['id']]);
        
        $response['message'] = 'All notifications marked as read';
    } elseif ($notificationId > 0) {
        // Mark specific notification as read
        $db->update('notifications', ['is_read' => true, 'read_at' => date('Y-m-d H:i:s')], 'id = ? AND user_id = ?', [$notificationId, $user['id']]);
        
        $response['message'] = 'Notification marked as read';
    } else {
        $response['message'] = 'Invalid notification ID';
        echo json_encode($response);
        return;
    }
    
    $response['success'] = true;
    echo json_encode($response);
}

function getTransactionIcon($type) {
    switch ($type) {
        case 'Savings Deposit':
            return 'deposit';
        case 'Savings Withdrawal':
            return 'withdrawal';
        case 'Loan Payment':
            return 'payment';
        case 'Fee':
            return 'fee';
        default:
            return 'transaction';
    }
}

function getTransactionColor($type) {
    switch ($type) {
        case 'Savings Deposit':
            return 'green';
        case 'Savings Withdrawal':
            return 'red';
        case 'Loan Payment':
            return 'blue';
        case 'Fee':
            return 'orange';
        default:
            return 'gray';
    }
}
?>
