<?php
/**
 * Reports API for Admin
 * Handles comprehensive reporting for KSP operations
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
function requireAuth($role = 'admin') {
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
    
    if ($user['role'] !== 'admin') {
        throw new Exception('Admin access required');
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
        case 'dashboard':
            handleDashboardReport($db, $validator);
            break;
        case 'financial_summary':
            handleFinancialSummary($db, $validator);
            break;
        case 'loan_portfolio':
            handleLoanPortfolio($db, $validator);
            break;
        case 'savings_summary':
            handleSavingsSummary($db, $validator);
            break;
        case 'member_analysis':
            handleMemberAnalysis($db, $validator);
            break;
        case 'transaction_report':
            handleTransactionReport($db, $validator);
            break;
        case 'shu_calculation':
            handleSHUCalculation($db, $validator);
            break;
        case 'export':
            handleExportReport($db, $validator);
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
        case 'custom':
            handleCustomReport($db, $validator);
            break;
        case 'generate':
            handleGenerateReport($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleDashboardReport($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    // Get current date ranges
    $today = date('Y-m-d');
    $thisMonth = date('Y-m-01');
    $lastMonth = date('Y-m-01', strtotime('-1 month'));
    $thisYear = date('Y-01-01');
    
    // Basic counts
    $dashboard = [
        'totals' => [
            'members' => $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE status = 'Active'")['count'],
            'loans' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status IN ('Active', 'Disbursed')")['count'],
            'savings' => $db->fetchOne("SELECT COUNT(*) as count FROM savings WHERE status = 'Active'")['count'],
            'users' => $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE is_active = 1")['count']
        ],
        'financials' => [
            'total_savings' => $db->fetchOne("SELECT COALESCE(SUM(balance), 0) as total FROM savings WHERE status = 'Active'")['total'],
            'total_loans' => $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM loans WHERE status IN ('Active', 'Disbursed')")['total'],
            'total_paid' => $db->fetchOne("SELECT COALESCE(SUM(pt.amount), 0) as total FROM payment_transactions pt JOIN loans l ON pt.loan_id = l.id WHERE pt.status = 'Completed'")['total'],
            'outstanding_balance' => $db->fetchOne("SELECT COALESCE(SUM(remaining_balance), 0) as total FROM loans WHERE status IN ('Active', 'Disbursed')")['total']
        ],
        'monthly_stats' => [
            'new_members' => $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE created_at >= ?", [$thisMonth])['count'],
            'new_loans' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE created_at >= ?", [$thisMonth])['count'],
            'loan_disbursements' => $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM loans WHERE disbursed_at >= ?", [$thisMonth])['total'],
            'savings_deposits' => $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE type = 'Savings Deposit' AND created_at >= ? AND status = 'Completed'", [$thisMonth])['total']
        ],
        'performance' => [
            'npl_rate' => calculateNPLRate($db),
            'savings_growth' => calculateSavingsGrowth($db),
            'loan_portfolio_quality' => calculatePortfolioQuality($db),
            'member_retention' => calculateMemberRetention($db)
        ]
    ];
    
    // Recent activities
    $dashboard['recent_activities'] = $db->fetchAll(
        "SELECT al.*, u.username 
         FROM audit_logs al 
         LEFT JOIN users u ON al.user_id = u.id 
         ORDER BY al.created_at DESC 
         LIMIT 5"
    );
    
    // Top performers
    $dashboard['top_performers'] = [
        'top_savers' => $db->fetchAll(
            "SELECT m.full_name, m.member_number, COALESCE(SUM(s.balance), 0) as total_savings
             FROM members m 
             LEFT JOIN savings s ON m.id = s.member_id 
             WHERE m.status = 'Active'
             GROUP BY m.id 
             ORDER BY total_savings DESC 
             LIMIT 5"
        ),
        'top_borrowers' => $db->fetchAll(
            "SELECT m.full_name, m.member_number, COUNT(l.id) as loan_count, COALESCE(SUM(l.amount), 0) as total_loans
             FROM members m 
             LEFT JOIN loans l ON m.id = l.member_id 
             WHERE m.status = 'Active'
             GROUP BY m.id 
             ORDER BY total_loans DESC 
             LIMIT 5"
        )
    ];
    
    $response['success'] = true;
    $response['message'] = 'Dashboard report generated successfully';
    $response['data'] = $dashboard;
    
    echo json_encode($response);
}

function handleFinancialSummary($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    
    // Income statement
    $income = [
        'loan_interest' => $db->fetchOne(
            "SELECT COALESCE(SUM(l.total_interest), 0) as total 
             FROM loans l 
             WHERE l.created_at BETWEEN ? AND ?",
            [$dateFrom, $dateTo]
        )['total'],
        'membership_fees' => $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total 
             FROM payment_transactions 
             WHERE type = 'Fee' AND created_at BETWEEN ? AND ? AND status = 'Completed'",
            [$dateFrom, $dateTo]
        )['total'],
        'late_fees' => $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total 
             FROM payment_transactions 
             WHERE type = 'Fine' AND created_at BETWEEN ? AND ? AND status = 'Completed'",
            [$dateFrom, $dateTo]
        )['total']
    ];
    
    $totalIncome = array_sum($income);
    
    // Expenses (if any)
    $expenses = [
        'operational_costs' => 0, // Would come from expense tracking table
        'loan_losses' => $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total 
             FROM loans 
             WHERE status = 'Default' AND created_at BETWEEN ? AND ?",
            [$dateFrom, $dateTo]
        )['total']
    ];
    
    $totalExpenses = array_sum($expenses);
    
    // Balance sheet items
    $balanceSheet = [
        'assets' => [
            'cash' => 0, // Would come from cash management
            'savings_deposits' => $db->fetchOne("SELECT COALESCE(SUM(balance), 0) as total FROM savings WHERE status = 'Active'")['total'],
            'loans_outstanding' => $db->fetchOne("SELECT COALESCE(SUM(remaining_balance), 0) as total FROM loans WHERE status IN ('Active', 'Disbursed')")['total'],
            'fixed_assets' => 0 // Would come from asset tracking
        ],
        'liabilities' => [
            'savings_withdrawals' => 0, // Pending withdrawals
            'borrowed_funds' => 0 // External borrowing
        ],
        'equity' => [
            'share_capital' => 0, // Member shares
            'retained_earnings' => 0 // Accumulated profits
        ]
    ];
    
    $totalAssets = array_sum($balanceSheet['assets']);
    $totalLiabilities = array_sum($balanceSheet['liabilities']);
    $totalEquity = array_sum($balanceSheet['equity']);
    
    $financialSummary = [
        'period' => [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ],
        'income_statement' => [
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_income' => $totalIncome - $totalExpenses,
            'breakdown' => $income
        ],
        'balance_sheet' => [
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'breakdown' => $balanceSheet
        ],
        'key_ratios' => [
            'liquidity_ratio' => $totalLiabilities > 0 ? $totalAssets / $totalLiabilities : 0,
            'savings_to_loans' => $balanceSheet['assets']['loans_outstanding'] > 0 ? $balanceSheet['assets']['savings_deposits'] / $balanceSheet['assets']['loans_outstanding'] : 0,
            'profit_margin' => $totalIncome > 0 ? ($totalIncome - $totalExpenses) / $totalIncome : 0
        ]
    ];
    
    $response['success'] = true;
    $response['message'] = 'Financial summary generated successfully';
    $response['data'] = $financialSummary;
    
    echo json_encode($response);
}

function handleLoanPortfolio($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    
    // Portfolio overview
    $portfolio = [
        'overview' => [
            'total_loans' => $db->fetchOne("SELECT COUNT(*) as count FROM loans")['count'],
            'active_loans' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status IN ('Active', 'Disbursed')")['count'],
            'total_portfolio' => $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM loans")['total'],
            'outstanding_balance' => $db->fetchOne("SELECT COALESCE(SUM(remaining_balance), 0) as total FROM loans WHERE status IN ('Active', 'Disbursed')")['total']
        ],
        'by_status' => $db->fetchAll(
            "SELECT status, COUNT(*) as count, COALESCE(SUM(amount), 0) as total_amount 
             FROM loans 
             GROUP BY status"
        ),
        'by_term' => $db->fetchAll(
            "SELECT 
                CASE 
                    WHEN term_months <= 12 THEN 'Short Term (≤12)'
                    WHEN term_months <= 24 THEN 'Medium Term (13-24)'
                    ELSE 'Long Term (>24)'
                END as term_category,
                COUNT(*) as count,
                COALESCE(SUM(amount), 0) as total_amount
             FROM loans 
             GROUP BY term_category"
        ),
        'by_purpose' => $db->fetchAll(
            "SELECT purpose, COUNT(*) as count, COALESCE(SUM(amount), 0) as total_amount 
             FROM loans 
             WHERE purpose IS NOT NULL
             GROUP BY purpose 
             ORDER BY total_amount DESC 
             LIMIT 10"
        ),
        'performance' => [
            'npl_rate' => calculateNPLRate($db),
            'average_loan_size' => $db->fetchOne("SELECT COALESCE(AVG(amount), 0) as avg FROM loans")['avg'],
            'delinquency_rate' => calculateDelinquencyRate($db)
        ],
        'monthly_disbursements' => $db->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count, COALESCE(SUM(amount), 0) as total
             FROM loans 
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY month"
        ),
        'risk_analysis' => [
            'high_risk_loans' => $db->fetchOne(
                "SELECT COUNT(*) as count 
                 FROM loans l 
                 JOIN members m ON l.member_id = m.id 
                 WHERE l.status = 'Active' AND m.credit_score < 40"
            )['count'],
            'overdue_loans' => $db->fetchOne(
                "SELECT COUNT(*) as count 
                 FROM loans 
                 WHERE status = 'Active' AND next_payment_date < CURDATE()"
            )['count']
        ]
    ];
    
    $response['success'] = true;
    $response['message'] = 'Loan portfolio report generated successfully';
    $response['data'] = $portfolio;
    
    echo json_encode($response);
}

function handleSavingsSummary($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    
    $savings = [
        'overview' => [
            'total_accounts' => $db->fetchOne("SELECT COUNT(*) as count FROM savings")['count'],
            'active_accounts' => $db->fetchOne("SELECT COUNT(*) as count FROM savings WHERE status = 'Active'")['count'],
            'total_balance' => $db->fetchOne("SELECT COALESCE(SUM(balance), 0) as total FROM savings WHERE status = 'Active'")['total'],
            'average_balance' => $db->fetchOne("SELECT COALESCE(AVG(balance), 0) as avg FROM savings WHERE status = 'Active'")['avg']
        ],
        'by_type' => $db->fetchAll(
            "SELECT type, COUNT(*) as count, COALESCE(SUM(balance), 0) as total_balance, COALESCE(AVG(balance), 0) as avg_balance
             FROM savings 
             WHERE status = 'Active'
             GROUP BY type"
        ),
        'monthly_growth' => $db->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as new_accounts, COALESCE(SUM(amount), 0) as total_deposits
             FROM savings 
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY month"
        ),
        'deposit_withdrawal_trends' => [
            'monthly_deposits' => $db->fetchAll(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COALESCE(SUM(amount), 0) as total
                 FROM payment_transactions 
                 WHERE type = 'Savings Deposit' AND status = 'Completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                 ORDER BY month"
            ),
            'monthly_withdrawals' => $db->fetchAll(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COALESCE(SUM(amount), 0) as total
                 FROM payment_transactions 
                 WHERE type = 'Savings Withdrawal' AND status = 'Completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                 ORDER BY month"
            )
        ],
        'top_savers' => $db->fetchAll(
            "SELECT m.full_name, m.member_number, s.account_number, s.balance, s.type
             FROM savings s 
             JOIN members m ON s.member_id = m.id 
             WHERE s.status = 'Active'
             ORDER BY s.balance DESC 
             LIMIT 10"
        ),
        'inactive_accounts' => [
            'count' => $db->fetchOne("SELECT COUNT(*) as count FROM savings WHERE status = 'Inactive'")['count'],
            'details' => $db->fetchAll(
                "SELECT s.account_number, s.balance, s.last_deposit_date, m.full_name
                 FROM savings s 
                 JOIN members m ON s.member_id = m.id 
                 WHERE s.status = 'Inactive'
                 ORDER BY s.last_deposit_date DESC
                 LIMIT 10"
            )
        ]
    ];
    
    $response['success'] = true;
    $response['message'] = 'Savings summary generated successfully';
    $response['data'] = $savings;
    
    echo json_encode($response);
}

function handleMemberAnalysis($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    
    $analysis = [
        'overview' => [
            'total_members' => $db->fetchOne("SELECT COUNT(*) as count FROM members")['count'],
            'active_members' => $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE status = 'Active'")['count'],
            'new_members_this_month' => $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE created_at >= ?", [date('Y-m-01')])['count'],
            'average_credit_score' => $db->fetchOne("SELECT COALESCE(AVG(credit_score), 0) as avg FROM members WHERE status = 'Active'")['avg']
        ],
        'by_membership_type' => $db->fetchAll(
            "SELECT membership_type, COUNT(*) as count, COALESCE(AVG(credit_score), 0) as avg_credit_score
             FROM members 
             WHERE status = 'Active'
             GROUP BY membership_type"
        ),
        'by_join_date' => $db->fetchAll(
            "SELECT YEAR(join_date) as year, MONTH(join_date) as month, COUNT(*) as new_members
             FROM members 
             WHERE join_date >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
             GROUP BY YEAR(join_date), MONTH(join_date)
             ORDER BY year, month"
        ),
        'credit_score_distribution' => [
            'excellent' => $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE credit_score >= 80 AND status = 'Active'")['count'],
            'good' => $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE credit_score >= 60 AND credit_score < 80 AND status = 'Active'")['count'],
            'fair' => $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE credit_score >= 40 AND credit_score < 60 AND status = 'Active'")['count'],
            'poor' => $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE credit_score < 40 AND status = 'Active'")['count']
        ],
        'engagement_metrics' => [
            'average_savings_balance' => $db->fetchOne(
                "SELECT COALESCE(AVG(s.balance), 0) as avg 
                 FROM members m 
                 LEFT JOIN savings s ON m.id = s.member_id AND s.status = 'Active' 
                 WHERE m.status = 'Active'"
            )['avg'],
            'members_with_loans' => $db->fetchOne(
                "SELECT COUNT(DISTINCT member_id) as count 
                 FROM loans 
                 WHERE status IN ('Active', 'Disbursed', 'Completed')"
            )['count'],
            'members_with_savings' => $db->fetchOne(
                "SELECT COUNT(DISTINCT member_id) as count 
                 FROM savings 
                 WHERE status = 'Active'"
            )['count']
        ],
        'retention_analysis' => [
            'member_retention_rate' => calculateMemberRetention($db),
            'churned_members' => $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE status = 'Inactive'")['count'],
            'rejoining_members' => $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE status = 'Active' AND join_date < DATE_SUB(CURDATE(), INTERVAL 1 YEAR)")['count']
        ]
    ];
    
    $response['success'] = true;
    $response['message'] = 'Member analysis generated successfully';
    $response['data'] = $analysis;
    
    echo json_encode($response);
}

function handleTransactionReport($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    $transactionType = $_GET['transaction_type'] ?? '';
    
    $whereClause = "WHERE created_at BETWEEN ? AND ?";
    $params = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];
    
    if (!empty($transactionType)) {
        $whereClause .= " AND type = ?";
        $params[] = $transactionType;
    }
    
    $transactions = [
        'summary' => [
            'total_transactions' => $db->fetchOne("SELECT COUNT(*) as count FROM payment_transactions $whereClause", $params)['count'],
            'total_amount' => $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions $whereClause", $params)['total'],
            'completed_transactions' => $db->fetchOne("SELECT COUNT(*) as count FROM payment_transactions WHERE status = 'Completed' AND created_at BETWEEN ? AND ?", [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])['count']
        ],
        'by_type' => $db->fetchAll(
            "SELECT type, COUNT(*) as count, COALESCE(SUM(amount), 0) as total_amount, status
             FROM payment_transactions 
             WHERE created_at BETWEEN ? AND ?
             GROUP BY type, status
             ORDER BY total_amount DESC",
            [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']
        ),
        'by_payment_method' => $db->fetchAll(
            "SELECT payment_method, COUNT(*) as count, COALESCE(SUM(amount), 0) as total_amount
             FROM payment_transactions 
             WHERE created_at BETWEEN ? AND ?
             GROUP BY payment_method
             ORDER BY total_amount DESC",
            [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']
        ),
        'daily_trends' => $db->fetchAll(
            "SELECT DATE(created_at) as date, COUNT(*) as count, COALESCE(SUM(amount), 0) as total
             FROM payment_transactions 
             WHERE created_at BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY date",
            [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']
        ),
        'recent_transactions' => $db->fetchAll(
            "SELECT pt.*, m.full_name as member_name, m.member_number
             FROM payment_transactions pt
             LEFT JOIN savings s ON pt.savings_id = s.id
             LEFT JOIN members m ON s.member_id = m.id
             WHERE pt.created_at BETWEEN ? AND ?
             ORDER BY pt.created_at DESC
             LIMIT 20",
            [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']
        )
    ];
    
    $response['success'] = true;
    $response['message'] = 'Transaction report generated successfully';
    $response['data'] = $transactions;
    
    echo json_encode($response);
}

function handleSHUCalculation($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $year = (int)($_GET['year'] ?? date('Y'));
    
    // SHU calculation (simplified version)
    $shuCalculation = [
        'year' => $year,
        'period' => '1 Jan - 31 Dec ' . $year,
        'total_income' => 0, // Would be calculated from actual income sources
        'operational_expenses' => 0, // Would be calculated from actual expenses
        'net_shu' => 0, // Total SHU to be distributed
        'distribution_basis' => [
            'jasa_simpanan' => 40, // 40% from savings services
            'jasa_pinjaman' => 40, // 40% from loan services
            'pendapatan_lain' => 20  // 20% from other sources
        ],
        'member_eligibility' => [
            'active_members' => $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE status = 'Active' AND join_date <= ?", [$year . '-12-31'])['count'],
            'minimum_savings_months' => 6,
            'eligible_members' => 0 // Would be calculated based on criteria
        ],
        'calculation_method' => 'Proportional based on savings balance and loan participation'
    ];
    
    $response['success'] = true;
    $response['message'] = 'SHU calculation completed';
    $response['data'] = $shuCalculation;
    
    echo json_encode($response);
}

function handleExportReport($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $reportType = $_GET['report_type'] ?? 'dashboard';
    $format = $_GET['format'] ?? 'json';
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    
    // Generate report data based on type
    switch ($reportType) {
        case 'dashboard':
            $reportData = generateDashboardData($db);
            break;
        case 'financial':
            $reportData = generateFinancialData($db, $dateFrom, $dateTo);
            break;
        case 'loans':
            $reportData = generateLoanData($db, $dateFrom, $dateTo);
            break;
        case 'savings':
            $reportData = generateSavingsData($db, $dateFrom, $dateTo);
            break;
        default:
            $reportData = ['error' => 'Invalid report type'];
    }
    
    if ($format === 'csv') {
        // Convert to CSV format
        $csv = generateCSV($reportData, $reportType);
        
        $response['success'] = true;
        $response['message'] = 'Report exported successfully';
        $response['data'] = [
            'format' => 'csv',
            'content' => base64_encode($csv),
            'filename' => $reportType . '_report_' . $dateFrom . '_to_' . $dateTo . '.csv'
        ];
    } else {
        $response['success'] = true;
        $response['message'] = 'Report exported successfully';
        $response['data'] = $reportData;
    }
    
    echo json_encode($response);
}

function handleCustomReport($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $reportConfig = $input['config'] ?? [];
    $filters = $input['filters'] ?? [];
    
    // Generate custom report based on configuration
    $customReport = [
        'title' => $reportConfig['title'] ?? 'Custom Report',
        'description' => $reportConfig['description'] ?? '',
        'data' => [], // Would be populated based on custom query
        'generated_at' => date('Y-m-d H:i:s'),
        'generated_by' => $user['username']
    ];
    
    $response['success'] = true;
    $response['message'] = 'Custom report generated successfully';
    $response['data'] = $customReport;
    
    echo json_encode($response);
}

function handleGenerateReport($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $reportId = $input['report_id'] ?? '';
    $parameters = $input['parameters'] ?? [];
    
    // Generate specific report based on ID and parameters
    $report = [
        'report_id' => $reportId,
        'parameters' => $parameters,
        'data' => [], // Would be populated based on report configuration
        'generated_at' => date('Y-m-d H:i:s'),
        'generated_by' => $user['username']
    ];
    
    $response['success'] = true;
    $response['message'] = 'Report generated successfully';
    $response['data'] = $report;
    
    echo json_encode($response);
}

// Helper functions
function calculateNPLRate($db) {
    $totalLoans = $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status IN ('Active', 'Disbursed')")['count'];
    $nplLoans = $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status = 'Default'")['count'];
    
    return $totalLoans > 0 ? ($nplLoans / $totalLoans) * 100 : 0;
}

function calculateSavingsGrowth($db) {
    $thisMonth = date('Y-m-01');
    $lastMonth = date('Y-m-01', strtotime('-1 month'));
    
    $thisMonthBalance = $db->fetchOne("SELECT COALESCE(SUM(balance), 0) as total FROM savings WHERE created_at >= ?", [$thisMonth])['total'];
    $lastMonthBalance = $db->fetchOne("SELECT COALESCE(SUM(balance), 0) as total FROM savings WHERE created_at >= ? AND created_at < ?", [$lastMonth, $thisMonth])['total'];
    
    return $lastMonthBalance > 0 ? (($thisMonthBalance - $lastMonthBalance) / $lastMonthBalance) * 100 : 0;
}

function calculatePortfolioQuality($db) {
    $totalLoans = $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status IN ('Active', 'Disbursed')")['count'];
    $performingLoans = $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status IN ('Active', 'Disbursed') AND next_payment_date >= CURDATE()")['count'];
    
    return $totalLoans > 0 ? ($performingLoans / $totalLoans) * 100 : 0;
}

function calculateMemberRetention($db) {
    $totalMembers = $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE join_date <= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)")['count'];
    $activeMembers = $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE status = 'Active' AND join_date <= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)")['count'];
    
    return $totalMembers > 0 ? ($activeMembers / $totalMembers) * 100 : 0;
}

function calculateDelinquencyRate($db) {
    $totalLoans = $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status = 'Active'")['count'];
    $overdueLoans = $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status = 'Active' AND next_payment_date < CURDATE()")['count'];
    
    return $totalLoans > 0 ? ($overdueLoans / $totalLoans) * 100 : 0;
}

function generateDashboardData($db) {
    return [
        'summary' => 'Dashboard data would be generated here',
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

function generateFinancialData($db, $dateFrom, $dateTo) {
    return [
        'summary' => 'Financial data would be generated here',
        'period' => "$dateFrom to $dateTo"
    ];
}

function generateLoanData($db, $dateFrom, $dateTo) {
    return [
        'summary' => 'Loan data would be generated here',
        'period' => "$dateFrom to $dateTo"
    ];
}

function generateSavingsData($db, $dateFrom, $dateTo) {
    return [
        'summary' => 'Savings data would be generated here',
        'period' => "$dateFrom to $dateTo"
    ];
}

function generateCSV($data, $reportType) {
    $csv = "$reportType Report\n";
    $csv .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
    
    // This would be a proper CSV generation based on the data structure
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $csv .= "$key: " . json_encode($value) . "\n";
        } else {
            $csv .= "$key: $value\n";
        }
    }
    
    return $csv;
}
?>
