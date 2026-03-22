<?php
/**
 * Staff Reports API
 * Handles reporting for staff members
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
function requireAuth($role = 'staff') {
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
        throw new Exception('Staff access required');
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
        case 'performance':
            handlePerformanceReport($db, $validator);
            break;
        case 'member_visits':
            handleMemberVisitsReport($db, $validator);
            break;
        case 'loan_portfolio':
            handleLoanPortfolioReport($db, $validator);
            break;
        case 'collection_report':
            handleCollectionReport($db, $validator);
            break;
        case 'activity_log':
            handleActivityLog($db, $validator);
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
        case 'generate':
            handleGenerateReport($db, $validator);
            break;
        case 'custom':
            handleCustomReport($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handlePerformanceReport($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $period = $_GET['period'] ?? 'month'; // day, week, month, year
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    
    // Set date range
    if (!empty($dateFrom) && !empty($dateTo)) {
        $dateCondition = "created_at BETWEEN '$dateFrom 00:00:00' AND '$dateTo 23:59:59'";
    } else {
        $dateCondition = getDateCondition($period);
    }
    
    // Performance metrics
    $performance = [
        'overview' => [
            'total_visits' => $db->fetchOne("SELECT COUNT(*) as count FROM gps_tracking WHERE staff_id = ? AND $dateCondition", [$user['id']])['count'],
            'completed_visits' => $db->fetchOne("SELECT COUNT(*) as count FROM gps_tracking WHERE staff_id = ? AND status = 'completed' AND $dateCondition", [$user['id']])['count'],
            'total_distance' => $db->fetchOne("SELECT COALESCE(SUM(distance_km), 0) as total FROM gps_tracking WHERE staff_id = ? AND $dateCondition", [$user['id']])['total'],
            'total_time' => $db->fetchOne("SELECT COALESCE(SUM(duration_minutes), 0) as total FROM gps_tracking WHERE staff_id = ? AND $dateCondition", [$user['id']])['total'],
            'members_contacted' => $db->fetchOne("SELECT COUNT(DISTINCT member_id) as count FROM gps_tracking WHERE staff_id = ? AND $dateCondition", [$user['id']])['count'],
            'tasks_completed' => $db->fetchOne("SELECT COUNT(*) as count FROM staff_tasks WHERE assigned_to = ? AND status = 'completed' AND $dateCondition", [$user['id']])['count']
        ],
        'efficiency' => [
            'visits_per_day' => 0,
            'distance_per_visit' => 0,
            'time_per_visit' => 0,
            'completion_rate' => 0
        ],
        'daily_breakdown' => $db->fetchAll(
            "SELECT DATE(created_at) as date, 
                    COUNT(*) as visits, 
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_visits,
                    COALESCE(SUM(distance_km), 0) as distance,
                    COALESCE(SUM(duration_minutes), 0) as time
             FROM gps_tracking 
             WHERE staff_id = ? AND $dateCondition
             GROUP BY DATE(created_at)
             ORDER BY date",
            [$user['id']]
        ),
        'member_breakdown' => $db->fetchAll(
            "SELECT m.full_name, m.member_number,
                    COUNT(gt.id) as visit_count,
                    MAX(gt.created_at) as last_visit
             FROM gps_tracking gt
             LEFT JOIN members m ON gt.member_id = m.id
             WHERE gt.staff_id = ? AND $dateCondition
             GROUP BY gt.member_id
             ORDER BY visit_count DESC
             LIMIT 10",
            [$user['id']]
        ),
        'task_breakdown' => $db->fetchAll(
            "SELECT task_type, COUNT(*) as count, 
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed
             FROM staff_tasks 
             WHERE assigned_to = ? AND $dateCondition
             GROUP BY task_type
             ORDER BY count DESC",
            [$user['id']]
        )
    ];
    
    // Calculate efficiency metrics
    $totalVisits = $performance['overview']['total_visits'];
    if ($totalVisits > 0) {
        $performance['efficiency']['visits_per_day'] = round($totalVisits / max(1, count($performance['daily_breakdown'])), 2);
        $performance['efficiency']['distance_per_visit'] = round($performance['overview']['total_distance'] / $totalVisits, 2);
        $performance['efficiency']['time_per_visit'] = round($performance['overview']['total_time'] / $totalVisits, 2);
    }
    
    $totalTasks = $db->fetchOne("SELECT COUNT(*) as count FROM staff_tasks WHERE assigned_to = ? AND $dateCondition", [$user['id']])['count'];
    if ($totalTasks > 0) {
        $performance['efficiency']['completion_rate'] = round(($performance['overview']['tasks_completed'] / $totalTasks) * 100, 2);
    }
    
    $response['success'] = true;
    $response['message'] = 'Performance report generated successfully';
    $response['data'] = $performance;
    
    echo json_encode($response);
}

function handleMemberVisitsReport($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $period = $_GET['period'] ?? 'month';
    $memberId = (int)($_GET['member_id'] ?? 0);
    $status = $_GET['status'] ?? '';
    
    $dateCondition = getDateCondition($period);
    
    $whereConditions = ["gt.staff_id = ? AND $dateCondition"];
    $params = [$user['id']];
    
    if ($memberId > 0) {
        $whereConditions[] = "gt.member_id = ?";
        $params[] = $memberId;
    }
    
    if (!empty($status)) {
        $whereConditions[] = "gt.status = ?";
        $params[] = $status;
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    $visits = $db->fetchAll(
        "SELECT gt.*, 
                m.full_name as member_name, 
                m.member_number, 
                m.phone as member_phone,
                m.address as member_address
         FROM gps_tracking gt 
         LEFT JOIN members m ON gt.member_id = m.id 
         $whereClause
         ORDER BY gt.created_at DESC",
        $params
    );
    
    // Add visit metadata
    foreach ($visits as &$visit) {
        $visit['duration_formatted'] = formatDuration($visit['duration_minutes']);
        $visit['distance_formatted'] = number_format($visit['distance_km'], 2, ',', '.') . ' km';
        $visit['status_display'] = ucfirst($visit['status']);
        
        // Get GPS logs for this visit
        $visit['gps_logs'] = $db->fetchAll(
            "SELECT * FROM gps_logs WHERE tracking_id = ? ORDER BY timestamp ASC",
            [$visit['id']]
        );
    }
    
    // Summary statistics
    $summary = [
        'total_visits' => count($visits),
        'completed_visits' => count(array_filter($visits, fn($v) => $v['status'] === 'completed')),
        'total_distance' => array_sum(array_column($visits, 'distance_km')),
        'total_time' => array_sum(array_column($visits, 'duration_minutes')),
        'unique_members' => count(array_unique(array_column($visits, 'member_id')))
    ];
    
    $response['success'] = true;
    $response['message'] = 'Member visits report generated successfully';
    $response['data'] = [
        'visits' => $visits,
        'summary' => $summary,
        'period' => $period
    ];
    
    echo json_encode($response);
}

function handleLoanPortfolioReport($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $period = $_GET['period'] ?? 'month';
    $dateCondition = getDateCondition($period);
    
    // Get loans for members visited by this staff
    $loans = $db->fetchAll(
        "SELECT l.*, 
                m.full_name as member_name, 
                m.member_number,
                (SELECT COUNT(*) FROM payment_transactions WHERE loan_id = l.id AND status = 'Completed') as payment_count,
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE loan_id = l.id AND status = 'Completed') as paid_amount,
                (SELECT COUNT(*) FROM gps_tracking WHERE member_id = l.member_id AND staff_id = ? AND $dateCondition) as visit_count
         FROM loans l 
         LEFT JOIN members m ON l.member_id = m.id 
         WHERE l.member_id IN (
             SELECT DISTINCT member_id FROM gps_tracking WHERE staff_id = ? AND $dateCondition
         )
         ORDER BY l.created_at DESC",
        [$user['id'], $user['id']]
    );
    
    foreach ($loans as &$loan) {
        $loan['remaining_balance'] = ($loan['amount'] + ($loan['total_interest'] ?? 0)) - $loan['paid_amount'];
        $loan['payment_progress'] = $loan['amount'] > 0 ? ($loan['paid_amount'] / ($loan['amount'] + ($loan['total_interest'] ?? 0))) * 100 : 0;
        $loan['status_display'] = getLoanStatusDisplay($loan['status']);
        $loan['days_overdue'] = $loan['next_payment_date'] && $loan['next_payment_date'] < date('Y-m-d') ? 
            (strtotime(date('Y-m-d')) - strtotime($loan['next_payment_date'])) / 86400 : 0;
    }
    
    // Portfolio statistics
    $statistics = [
        'total_loans' => count($loans),
        'active_loans' => count(array_filter($loans, fn($l) => in_array($l['status'], ['Active', 'Disbursed']))),
        'completed_loans' => count(array_filter($loans, fn($l) => $l['status'] === 'Completed')),
        'total_loan_amount' => array_sum(array_column($loans, 'amount')),
        'total_paid' => array_sum(array_column($loans, 'paid_amount')),
        'total_outstanding' => array_sum(array_column($loans, 'remaining_balance')),
        'overdue_loans' => count(array_filter($loans, fn($l) => $l['days_overdue'] > 0))
    ];
    
    // Loan status breakdown
    $statusBreakdown = [];
    foreach ($loans as $loan) {
        $status = $loan['status'];
        if (!isset($statusBreakdown[$status])) {
            $statusBreakdown[$status] = [
                'count' => 0,
                'total_amount' => 0,
                'total_outstanding' => 0
            ];
        }
        $statusBreakdown[$status]['count']++;
        $statusBreakdown[$status]['total_amount'] += $loan['amount'];
        $statusBreakdown[$status]['total_outstanding'] += $loan['remaining_balance'];
    }
    
    $response['success'] = true;
    $response['message'] = 'Loan portfolio report generated successfully';
    $response['data'] = [
        'loans' => $loans,
        'statistics' => $statistics,
        'status_breakdown' => $statusBreakdown,
        'period' => $period
    ];
    
    echo json_encode($response);
}

function handleCollectionReport($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $period = $_GET['period'] ?? 'month';
    $dateCondition = getDateCondition($period);
    
    // Get payment collections for members visited by this staff
    $payments = $db->fetchAll(
        "SELECT pt.*, 
                l.loan_number,
                l.amount as loan_amount,
                m.full_name as member_name, 
                m.member_number,
                (SELECT COUNT(*) FROM gps_tracking WHERE member_id = m.id AND staff_id = ? AND $dateCondition) as visit_count
         FROM payment_transactions pt 
         LEFT JOIN loans l ON pt.loan_id = l.id 
         LEFT JOIN members m ON pt.member_id = m.id 
         WHERE pt.member_id IN (
             SELECT DISTINCT member_id FROM gps_tracking WHERE staff_id = ? AND $dateCondition
         ) AND pt.status = 'Completed' AND pt.type = 'Loan Payment'
         ORDER BY pt.created_at DESC",
        [$user['id'], $user['id']]
    );
    
    // Add payment metadata
    foreach ($payments as &$payment) {
        $payment['amount_formatted'] = number_format($payment['amount'], 2, ',', '.');
        $payment['payment_method_display'] = getPaymentMethodDisplay($payment['payment_method']);
        
        // Calculate collection efficiency
        if ($payment['visit_count'] > 0) {
            $payment['collection_efficiency'] = round($payment['amount'] / $payment['visit_count'], 2);
        } else {
            $payment['collection_efficiency'] = 0;
        }
    }
    
    // Collection statistics
    $statistics = [
        'total_collections' => count($payments),
        'total_amount' => array_sum(array_column($payments, 'amount')),
        'average_collection' => count($payments) > 0 ? array_sum(array_column($payments, 'amount')) / count($payments) : 0,
        'unique_members' => count(array_unique(array_column($payments, 'member_id'))),
        'payment_methods' => array_count_values(array_column($payments, 'payment_method'))
    ];
    
    // Monthly trend
    $monthlyTrend = $db->fetchAll(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                COUNT(*) as count, 
                COALESCE(SUM(amount), 0) as total
         FROM payment_transactions 
         WHERE member_id IN (
             SELECT DISTINCT member_id FROM gps_tracking WHERE staff_id = ?
         ) AND status = 'Completed' AND type = 'Loan Payment' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
         GROUP BY DATE_FORMAT(created_at, '%Y-%m')
         ORDER BY month",
        [$user['id']]
    );
    
    $response['success'] = true;
    $response['message'] = 'Collection report generated successfully';
    $response['data'] = [
        'payments' => $payments,
        'statistics' => $statistics,
        'monthly_trend' => $monthlyTrend,
        'period' => $period
    ];
    
    echo json_encode($response);
}

function handleActivityLog($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 50);
    $activityType = $_GET['activity_type'] ?? '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = ["staff_id = ?"];
    $params = [$user['id']];
    
    if (!empty($activityType)) {
        $whereConditions[] = "activity_type = ?";
        $params[] = $activityType;
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
    $countSql = "SELECT COUNT(*) as total FROM staff_activities $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get activities
    $sql = "SELECT * FROM staff_activities $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $activities = $db->fetchAll($sql, $params);
    
    // Add activity metadata
    foreach ($activities as &$activity) {
        $activity['activity_type_display'] = getActivityTypeDisplay($activity['activity_type']);
        $activity['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($activity['created_at']));
        
        // Get related data if available
        if ($activity['reference_id']) {
            switch ($activity['activity_type']) {
                case 'member_created':
                case 'member_updated':
                    $activity['member'] = $db->fetchOne("SELECT full_name, member_number FROM members WHERE id = ?", [$activity['reference_id']]);
                    break;
                case 'tracking_started':
                case 'tracking_completed':
                    $activity['tracking'] = $db->fetchOne("SELECT purpose, status FROM gps_tracking WHERE id = ?", [$activity['reference_id']]);
                    break;
                case 'task_completed':
                    $activity['task'] = $db->fetchOne("SELECT title, status FROM staff_tasks WHERE id = ?", [$activity['reference_id']]);
                    break;
            }
        }
    }
    
    $response['success'] = true;
    $response['message'] = 'Activity log retrieved successfully';
    $response['data'] = [
        'activities' => $activities,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    echo json_encode($response);
}

function handleExportReport($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $reportType = $_GET['report_type'] ?? 'performance';
    $format = $_GET['format'] ?? 'json';
    $period = $_GET['period'] ?? 'month';
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-t');
    
    // Generate report data based on type
    switch ($reportType) {
        case 'performance':
            $reportData = generatePerformanceData($db, $user['id'], $period, $dateFrom, $dateTo);
            break;
        case 'visits':
            $reportData = generateVisitsData($db, $user['id'], $period, $dateFrom, $dateTo);
            break;
        case 'collections':
            $reportData = generateCollectionsData($db, $user['id'], $period, $dateFrom, $dateTo);
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

function handleGenerateReport($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $reportType = $input['report_type'] ?? 'performance';
    $parameters = $input['parameters'] ?? [];
    
    // Generate specific report based on type and parameters
    $report = [
        'report_type' => $reportType,
        'parameters' => $parameters,
        'data' => [],
        'generated_at' => date('Y-m-d H:i:s'),
        'generated_by' => $user['full_name']
    ];
    
    switch ($reportType) {
        case 'performance':
            $report['data'] = generatePerformanceData($db, $user['id'], $parameters['period'] ?? 'month');
            break;
        case 'visits':
            $report['data'] = generateVisitsData($db, $user['id'], $parameters['period'] ?? 'month');
            break;
        case 'collections':
            $report['data'] = generateCollectionsData($db, $user['id'], $parameters['period'] ?? 'month');
            break;
    }
    
    $response['success'] = true;
    $response['message'] = 'Report generated successfully';
    $response['data'] = $report;
    
    echo json_encode($response);
}

function handleCustomReport($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $reportConfig = $input['config'] ?? [];
    $filters = $input['filters'] ?? [];
    
    // Generate custom report based on configuration
    $customReport = [
        'title' => $reportConfig['title'] ?? 'Custom Report',
        'description' => $reportConfig['description'] ?? '',
        'data' => [],
        'generated_at' => date('Y-m-d H:i:s'),
        'generated_by' => $user['full_name']
    ];
    
    // This would implement custom report logic based on configuration
    // For now, return basic structure
    
    $response['success'] = true;
    $response['message'] = 'Custom report generated successfully';
    $response['data'] = $customReport;
    
    echo json_encode($response);
}

// Helper functions
function getDateCondition($period) {
    switch ($period) {
        case 'day':
            return 'DATE(created_at) = CURDATE()';
        case 'week':
            return 'created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
        case 'month':
            return 'created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
        case 'year':
            return 'created_at >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)';
        default:
            return 'created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
    }
}

function formatDuration($minutes) {
    if ($minutes < 60) {
        return $minutes . ' min';
    } elseif ($minutes < 1440) {
        return round($minutes / 60, 1) . ' hours';
    } else {
        return round($minutes / 1440, 1) . ' days';
    }
}

function getLoanStatusDisplay($status) {
    $displays = [
        'Applied' => 'Applied',
        'Approved' => 'Approved',
        'Disbursed' => 'Active',
        'Active' => 'Active',
        'Completed' => 'Completed',
        'Default' => 'Default',
        'Cancelled' => 'Cancelled'
    ];
    
    return $displays[$status] ?? $status;
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

function getActivityTypeDisplay($type) {
    $displays = [
        'member_created' => 'Member Created',
        'member_updated' => 'Member Updated',
        'member_status_updated' => 'Member Status Updated',
        'tracking_started' => 'Tracking Started',
        'tracking_completed' => 'Tracking Completed',
        'task_completed' => 'Task Completed',
        'visit_scheduled' => 'Visit Scheduled',
        'collection_made' => 'Collection Made'
    ];
    
    return $displays[$type] ?? $type;
}

function generatePerformanceData($db, $staffId, $period, $dateFrom = '', $dateTo = '') {
    // This would generate performance report data
    return [
        'summary' => 'Performance data would be generated here',
        'period' => $period,
        'date_range' => ['from' => $dateFrom, 'to' => $dateTo]
    ];
}

function generateVisitsData($db, $staffId, $period, $dateFrom = '', $dateTo = '') {
    // This would generate visits report data
    return [
        'summary' => 'Visits data would be generated here',
        'period' => $period,
        'date_range' => ['from' => $dateFrom, 'to' => $dateTo]
    ];
}

function generateCollectionsData($db, $staffId, $period, $dateFrom = '', $dateTo = '') {
    // This would generate collections report data
    return [
        'summary' => 'Collections data would be generated here',
        'period' => $period,
        'date_range' => ['from' => $dateFrom, 'to' => $dateTo]
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
