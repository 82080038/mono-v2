<?php
/**
 * Audit Log API for Admin
 * Handles system audit trail management
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
            handleListAuditLogs($db, $validator);
            break;
        case 'detail':
            handleGetAuditLog($db, $validator);
            break;
        case 'statistics':
            handleAuditStatistics($db, $validator);
            break;
        case 'export':
            handleExportAuditLogs($db, $validator);
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
        case 'search':
            handleSearchAuditLogs($db, $validator);
            break;
        case 'create':
            handleCreateAuditLog($db, $validator);
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
        case 'clear':
            handleClearAuditLogs($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleListAuditLogs($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 50);
    $action = $_GET['action_filter'] ?? '';
    $table = $_GET['table'] ?? '';
    $userId = (int)($_GET['user_id'] ?? 0);
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = [];
    $params = [];
    
    if (!empty($action)) {
        $whereConditions[] = "al.action LIKE ?";
        $params[] = "%$action%";
    }
    
    if (!empty($table)) {
        $whereConditions[] = "al.table_name = ?";
        $params[] = $table;
    }
    
    if ($userId > 0) {
        $whereConditions[] = "al.user_id = ?";
        $params[] = $userId;
    }
    
    if (!empty($dateFrom)) {
        $whereConditions[] = "al.created_at >= ?";
        $params[] = $dateFrom . ' 00:00:00';
    }
    
    if (!empty($dateTo)) {
        $whereConditions[] = "al.created_at <= ?";
        $params[] = $dateTo . ' 23:59:59';
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get audit logs
    $sql = "SELECT al.*, u.username, u.full_name as user_name
            FROM audit_logs al 
            LEFT JOIN users u ON al.user_id = u.id 
            $whereClause
            ORDER BY al.created_at DESC 
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $logs = $db->fetchAll($sql, $params);
    
    // Parse old and new values
    foreach ($logs as &$log) {
        $log['old_values'] = $log['old_values'] ? json_decode($log['old_values'], true) : null;
        $log['new_values'] = $log['new_values'] ? json_decode($log['new_values'], true) : null;
    }
    
    $response['success'] = true;
    $response['message'] = 'Audit logs retrieved successfully';
    $response['data'] = [
        'logs' => $logs,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    echo json_encode($response);
}

function handleGetAuditLog($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $logId = (int)($_GET['id'] ?? 0);
    
    if ($logId <= 0) {
        $response['message'] = 'Log ID required';
        echo json_encode($response);
        return;
    }
    
    $log = $db->fetchOne(
        "SELECT al.*, u.username, u.full_name as user_name
         FROM audit_logs al 
         LEFT JOIN users u ON al.user_id = u.id 
         WHERE al.id = ?",
        [$logId]
    );
    
    if (!$log) {
        $response['message'] = 'Audit log not found';
        echo json_encode($response);
        return;
    }
    
    // Parse old and new values
    $log['old_values'] = $log['old_values'] ? json_decode($log['old_values'], true) : null;
    $log['new_values'] = $log['new_values'] ? json_decode($log['new_values'], true) : null;
    
    $response['success'] = true;
    $response['message'] = 'Audit log retrieved successfully';
    $response['data'] = $log;
    
    echo json_encode($response);
}

function handleSearchAuditLogs($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'query' => 'required|string|min:2',
        'search_type' => 'in:action,table,user,values'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    $query = $input['query'];
    $searchType = $input['search_type'] ?? 'action';
    $limit = (int)($input['limit'] ?? 50);
    
    $whereConditions = [];
    $params = [];
    
    switch ($searchType) {
        case 'action':
            $whereConditions[] = "al.action LIKE ?";
            $params[] = "%$query%";
            break;
            
        case 'table':
            $whereConditions[] = "al.table_name LIKE ?";
            $params[] = "%$query%";
            break;
            
        case 'user':
            $whereConditions[] = "(u.username LIKE ? OR u.full_name LIKE ?)";
            $queryParam = "%$query%";
            $params = [$queryParam, $queryParam];
            break;
            
        case 'values':
            $whereConditions[] = "(al.old_values LIKE ? OR al.new_values LIKE ?)";
            $queryParam = "%$query%";
            $params = [$queryParam, $queryParam];
            break;
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    $sql = "SELECT al.*, u.username, u.full_name as user_name
            FROM audit_logs al 
            LEFT JOIN users u ON al.user_id = u.id 
            $whereClause
            ORDER BY al.created_at DESC 
            LIMIT ?";
    
    $params[] = $limit;
    
    $logs = $db->fetchAll($sql, $params);
    
    // Parse old and new values
    foreach ($logs as &$log) {
        $log['old_values'] = $log['old_values'] ? json_decode($log['old_values'], true) : null;
        $log['new_values'] = $log['new_values'] ? json_decode($log['new_values'], true) : null;
    }
    
    $response['success'] = true;
    $response['message'] = 'Search completed successfully';
    $response['data'] = [
        'logs' => $logs,
        'total' => count($logs)
    ];
    
    echo json_encode($response);
}

function handleAuditStatistics($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    // Basic statistics
    $stats = [
        'total_logs' => $db->fetchOne("SELECT COUNT(*) as count FROM audit_logs")['count'],
        'logs_today' => $db->fetchOne("SELECT COUNT(*) as count FROM audit_logs WHERE DATE(created_at) = CURDATE()")['count'],
        'logs_this_week' => $db->fetchOne("SELECT COUNT(*) as count FROM audit_logs WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")['count'],
        'logs_this_month' => $db->fetchOne("SELECT COUNT(*) as count FROM audit_logs WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")['count']
    ];
    
    // Action breakdown
    $actionBreakdown = $db->fetchAll(
        "SELECT action, COUNT(*) as count 
         FROM audit_logs 
         GROUP BY action 
         ORDER BY count DESC 
         LIMIT 10"
    );
    $stats['action_breakdown'] = $actionBreakdown;
    
    // Table breakdown
    $tableBreakdown = $db->fetchAll(
        "SELECT table_name, COUNT(*) as count 
         FROM audit_logs 
         WHERE table_name IS NOT NULL
         GROUP BY table_name 
         ORDER BY count DESC 
         LIMIT 10"
    );
    $stats['table_breakdown'] = $tableBreakdown;
    
    // User breakdown
    $userBreakdown = $db->fetchAll(
        "SELECT u.username, u.full_name, COUNT(*) as count 
         FROM audit_logs al 
         LEFT JOIN users u ON al.user_id = u.id 
         WHERE al.user_id IS NOT NULL
         GROUP BY al.user_id 
         ORDER BY count DESC 
         LIMIT 10"
    );
    $stats['user_breakdown'] = $userBreakdown;
    
    // Recent activity
    $recentActivity = $db->fetchAll(
        "SELECT al.*, u.username, u.full_name as user_name
         FROM audit_logs al 
         LEFT JOIN users u ON al.user_id = u.id 
         ORDER BY al.created_at DESC 
         LIMIT 5"
    );
    
    foreach ($recentActivity as &$activity) {
        $activity['old_values'] = $activity['old_values'] ? json_decode($activity['old_values'], true) : null;
        $activity['new_values'] = $activity['new_values'] ? json_decode($activity['new_values'], true) : null;
    }
    
    $stats['recent_activity'] = $recentActivity;
    
    $response['success'] = true;
    $response['message'] = 'Audit statistics retrieved successfully';
    $response['data'] = $stats;
    
    echo json_encode($response);
}

function handleExportAuditLogs($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
    $dateTo = $_GET['date_to'] ?? date('Y-m-t'); // Last day of current month
    $format = $_GET['format'] ?? 'json';
    
    $logs = $db->fetchAll(
        "SELECT al.*, u.username, u.full_name as user_name
         FROM audit_logs al 
         LEFT JOIN users u ON al.user_id = u.id 
         WHERE al.created_at BETWEEN ? AND ?
         ORDER BY al.created_at ASC",
        [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']
    );
    
    // Parse old and new values
    foreach ($logs as &$log) {
        $log['old_values'] = $log['old_values'] ? json_decode($log['old_values'], true) : null;
        $log['new_values'] = $log['new_values'] ? json_decode($log['new_values'], true) : null;
    }
    
    $exportData = [
        'export_info' => [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total_records' => count($logs),
            'exported_by' => $user['username'],
            'export_date' => date('Y-m-d H:i:s')
        ],
        'logs' => $logs
    ];
    
    if ($format === 'csv') {
        // Convert to CSV format
        $csv = "ID,User,Action,Table,Record ID,IP Address,User Agent,Created At\n";
        
        foreach ($logs as $log) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s,%s\n",
                $log['id'],
                $log['user_name'] ?? 'System',
                $log['action'],
                $log['table_name'] ?? '',
                $log['record_id'] ?? '',
                $log['ip_address'] ?? '',
                str_replace(["\n", "\r", ","], [" ", " ", ";"], $log['user_agent'] ?? ''),
                $log['created_at']
            );
        }
        
        $response['success'] = true;
        $response['message'] = 'Audit logs exported successfully';
        $response['data'] = [
            'format' => 'csv',
            'content' => base64_encode($csv),
            'filename' => 'audit_logs_' . $dateFrom . '_to_' . $dateTo . '.csv'
        ];
    } else {
        $response['success'] = true;
        $response['message'] = 'Audit logs exported successfully';
        $response['data'] = $exportData;
    }
    
    echo json_encode($response);
}

function handleCreateAuditLog($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'action' => 'required|string|min:2',
        'table_name' => 'string',
        'record_id' => 'integer',
        'old_values' => 'array',
        'new_values' => 'array'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    $auditData = [
        'user_id' => $user['id'],
        'action' => $input['action'],
        'table_name' => $input['table_name'] ?? null,
        'record_id' => $input['record_id'] ?? null,
        'old_values' => isset($input['old_values']) ? json_encode($input['old_values']) : null,
        'new_values' => isset($input['new_values']) ? json_encode($input['new_values']) : null,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $logId = $db->insert('audit_logs', $auditData);
    
    $response['success'] = true;
    $response['message'] = 'Audit log created successfully';
    $response['data'] = ['log_id' => $logId];
    
    echo json_encode($response);
}

function handleClearAuditLogs($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $days = (int)($_GET['days'] ?? 90); // Default to 90 days
    
    if ($days < 30) {
        $response['message'] = 'Cannot delete logs newer than 30 days';
        echo json_encode($response);
        return;
    }
    
    $cutoffDate = date('Y-m-d H:i:s', strtotime("-$days days"));
    
    // Get count of logs to be deleted
    $count = $db->fetchOne("SELECT COUNT(*) as count FROM audit_logs WHERE created_at < ?", [$cutoffDate])['count'];
    
    if ($count === 0) {
        $response['message'] = 'No logs to delete';
        echo json_encode($response);
        return;
    }
    
    // Create audit log for this action
    $db->insert('audit_logs', [
        'user_id' => $user['id'],
        'action' => 'BULK_DELETE',
        'table_name' => 'audit_logs',
        'record_id' => null,
        'old_values' => null,
        'new_values' => json_encode(['deleted_count' => $count, 'cutoff_date' => $cutoffDate]),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    // Delete old logs
    $db->delete('audit_logs', 'created_at < ?', [$cutoffDate]);
    
    $response['success'] = true;
    $response['message'] = "$count audit logs older than $days days deleted successfully";
    $response['data'] = ['deleted_count' => $count];
    
    echo json_encode($response);
}
?>
