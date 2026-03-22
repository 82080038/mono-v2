<?php
/**
 * Staff Dashboard API
 * Provides dashboard data for staff members
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
        case 'overview':
            handleDashboardOverview($db, $validator);
            break;
        case 'tasks':
            handleGetTasks($db, $validator);
            break;
        case 'schedule':
            handleGetSchedule($db, $validator);
            break;
        case 'performance':
            handleGetPerformance($db, $validator);
            break;
        case 'notifications':
            handleGetNotifications($db, $validator);
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
        case 'complete_task':
            handleCompleteTask($db, $validator);
            break;
        case 'update_location':
            handleUpdateLocation($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleDashboardOverview($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    // Get staff information
    $staff = $db->fetchOne(
        "SELECT u.*, m.full_name as member_name, m.member_number 
         FROM users u 
         LEFT JOIN members m ON u.id = m.user_id 
         WHERE u.id = ?",
        [$user['id']]
    );
    
    // Dashboard statistics
    $dashboard = [
        'staff_info' => [
            'id' => $staff['id'],
            'username' => $staff['username'],
            'full_name' => $staff['full_name'],
            'email' => $staff['email'],
            'phone' => $staff['phone'],
            'last_login' => $staff['last_login']
        ],
        'today_stats' => [
            'visits_scheduled' => $db->fetchOne(
                "SELECT COUNT(*) as count FROM gps_tracking WHERE staff_id = ? AND DATE(created_at) = CURDATE()",
                [$user['id']]
            )['count'],
            'visits_completed' => $db->fetchOne(
                "SELECT COUNT(*) as count FROM gps_tracking WHERE staff_id = ? AND DATE(created_at) = CURDATE() AND status = 'completed'",
                [$user['id']]
            )['count'],
            'members_contacted' => $db->fetchOne(
                "SELECT COUNT(DISTINCT member_id) as count FROM gps_tracking WHERE staff_id = ? AND DATE(created_at) = CURDATE()",
                [$user['id']]
            )['count'],
            'distance_traveled' => $db->fetchOne(
                "SELECT COALESCE(SUM(distance_km), 0) as total FROM gps_tracking WHERE staff_id = ? AND DATE(created_at) = CURDATE()",
                [$user['id']]
            )['total']
        ],
        'week_stats' => [
            'visits_completed' => $db->fetchOne(
                "SELECT COUNT(*) as count FROM gps_tracking WHERE staff_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
                [$user['id']]
            )['count'],
            'members_contacted' => $db->fetchOne(
                "SELECT COUNT(DISTINCT member_id) as count FROM gps_tracking WHERE staff_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
                [$user['id']]
            )['count'],
            'distance_traveled' => $db->fetchOne(
                "SELECT COALESCE(SUM(distance_km), 0) as total FROM gps_tracking WHERE staff_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
                [$user['id']]
            )['total']
        ],
        'month_stats' => [
            'visits_completed' => $db->fetchOne(
                "SELECT COUNT(*) as count FROM gps_tracking WHERE staff_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
                [$user['id']]
            )['count'],
            'members_contacted' => $db->fetchOne(
                "SELECT COUNT(DISTINCT member_id) as count FROM gps_tracking WHERE staff_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
                [$user['id']]
            )['count'],
            'distance_traveled' => $db->fetchOne(
                "SELECT COALESCE(SUM(distance_km), 0) as total FROM gps_tracking WHERE staff_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
                [$user['id']]
            )['total']
        ],
        'pending_tasks' => [
            'total' => $db->fetchOne(
                "SELECT COUNT(*) as count FROM staff_tasks WHERE assigned_to = ? AND status = 'pending'",
                [$user['id']]
            )['count'],
            'urgent' => $db->fetchOne(
                "SELECT COUNT(*) as count FROM staff_tasks WHERE assigned_to = ? AND status = 'pending' AND priority = 'high'",
                [$user['id']]
            )['count'],
            'overdue' => $db->fetchOne(
                "SELECT COUNT(*) as count FROM staff_tasks WHERE assigned_to = ? AND status = 'pending' AND due_date < CURDATE()",
                [$user['id']]
            )['count']
        ],
        'recent_activities' => $db->fetchAll(
            "SELECT * FROM staff_activities WHERE staff_id = ? ORDER BY created_at DESC LIMIT 10",
            [$user['id']]
        ),
        'upcoming_visits' => $db->fetchAll(
            "SELECT gt.*, m.full_name as member_name, m.member_number, m.phone as member_phone, m.address as member_address
             FROM gps_tracking gt 
             LEFT JOIN members m ON gt.member_id = m.id 
             WHERE gt.staff_id = ? AND gt.status = 'scheduled' AND DATE(gt.created_at) <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
             ORDER BY gt.created_at ASC 
             LIMIT 5",
            [$user['id']]
        )
    ];
    
    $response['success'] = true;
    $response['message'] = 'Dashboard overview retrieved successfully';
    $response['data'] = $dashboard;
    
    echo json_encode($response);
}

function handleGetTasks($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $status = $_GET['status'] ?? '';
    $priority = $_GET['priority'] ?? '';
    $dueDate = $_GET['due_date'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = ["assigned_to = ?"];
    $params = [$user['id']];
    
    if (!empty($status)) {
        $whereConditions[] = "status = ?";
        $params[] = $status;
    }
    
    if (!empty($priority)) {
        $whereConditions[] = "priority = ?";
        $params[] = $priority;
    }
    
    if (!empty($dueDate)) {
        $whereConditions[] = "due_date = ?";
        $params[] = $dueDate;
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM staff_tasks $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get tasks
    $sql = "SELECT st.*, 
                    CASE 
                        WHEN st.due_date < CURDATE() AND st.status = 'pending' THEN 'overdue'
                        WHEN st.due_date = CURDATE() AND st.status = 'pending' THEN 'due_today'
                        WHEN st.due_date > CURDATE() AND st.status = 'pending' THEN 'upcoming'
                        ELSE st.status
                    END as task_status
             FROM staff_tasks st 
             $whereClause
             ORDER BY st.due_date ASC, st.priority DESC, st.created_at DESC 
             LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $tasks = $db->fetchAll($sql, $params);
    
    // Add task metadata
    foreach ($tasks as &$task) {
        $task['days_until_due'] = (strtotime($task['due_date']) - strtotime(date('Y-m-d'))) / 86400;
        $task['priority_display'] = ucfirst($task['priority']);
        $task['status_display'] = ucfirst($task['status']);
    }
    
    $response['success'] = true;
    $response['message'] = 'Tasks retrieved successfully';
    $response['data'] = [
        'tasks' => $tasks,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    echo json_encode($response);
}

function handleGetSchedule($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-t');
    
    // Get scheduled visits
    $visits = $db->fetchAll(
        "SELECT gt.*, 
                m.full_name as member_name, 
                m.member_number, 
                m.phone as member_phone, 
                m.address as member_address,
                CASE 
                    WHEN gt.status = 'completed' THEN 'completed'
                    WHEN gt.status = 'active' THEN 'in_progress'
                    WHEN gt.status = 'scheduled' THEN 'scheduled'
                    ELSE gt.status
                END as visit_status
         FROM gps_tracking gt 
         LEFT JOIN members m ON gt.member_id = m.id 
         WHERE gt.staff_id = ? AND DATE(gt.created_at) BETWEEN ? AND ?
         ORDER BY gt.created_at ASC",
        [$user['id'], $dateFrom, $dateTo]
    );
    
    // Get staff availability
    $availability = $db->fetchAll(
        "SELECT * FROM staff_availability 
         WHERE staff_id = ? AND date BETWEEN ? AND ? 
         ORDER BY date ASC",
        [$user['id'], $dateFrom, $dateTo]
    );
    
    $schedule = [
        'visits' => $visits,
        'availability' => $availability,
        'period' => [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ],
        'summary' => [
            'total_visits' => count($visits),
            'completed_visits' => count(array_filter($visits, fn($v) => $v['status'] === 'completed')),
            'scheduled_visits' => count(array_filter($visits, fn($v) => $v['status'] === 'scheduled')),
            'in_progress_visits' => count(array_filter($visits, fn($v) => $v['status'] === 'active'))
        ]
    ];
    
    $response['success'] = true;
    $response['message'] = 'Schedule retrieved successfully';
    $response['data'] = $schedule;
    
    echo json_encode($response);
}

function handleGetPerformance($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $period = $_GET['period'] ?? 'month'; // day, week, month, year
    
    $dateCondition = getDateCondition($period);
    
    $performance = [
        'visits' => [
            'total' => $db->fetchOne("SELECT COUNT(*) as count FROM gps_tracking WHERE staff_id = ? AND $dateCondition", [$user['id']])['count'],
            'completed' => $db->fetchOne("SELECT COUNT(*) as count FROM gps_tracking WHERE staff_id = ? AND status = 'completed' AND $dateCondition", [$user['id']])['count'],
            'completion_rate' => 0 // Will be calculated below
        ],
        'members' => [
            'total_contacted' => $db->fetchOne("SELECT COUNT(DISTINCT member_id) as count FROM gps_tracking WHERE staff_id = ? AND $dateCondition", [$user['id']])['count'],
            'new_members' => $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE created_by = ? AND $dateCondition", [$user['id']])['count']
        ],
        'distance' => [
            'total_km' => $db->fetchOne("SELECT COALESCE(SUM(distance_km), 0) as total FROM gps_tracking WHERE staff_id = ? AND $dateCondition", [$user['id']])['total'],
            'average_per_visit' => 0 // Will be calculated below
        ],
        'time' => [
            'total_hours' => $db->fetchOne("SELECT COALESCE(SUM(duration_minutes), 0) as total FROM gps_tracking WHERE staff_id = ? AND $dateCondition", [$user['id']])['total'] / 60,
            'average_per_visit' => 0 // Will be calculated below
        ],
        'tasks' => [
            'total_assigned' => $db->fetchOne("SELECT COUNT(*) as count FROM staff_tasks WHERE assigned_to = ? AND $dateCondition", [$user['id']])['count'],
            'completed' => $db->fetchOne("SELECT COUNT(*) as count FROM staff_tasks WHERE assigned_to = ? AND status = 'completed' AND $dateCondition", [$user['id']])['count'],
            'completion_rate' => 0 // Will be calculated below
        ]
    ];
    
    // Calculate rates and averages
    if ($performance['visits']['total'] > 0) {
        $performance['visits']['completion_rate'] = round(($performance['visits']['completed'] / $performance['visits']['total']) * 100, 2);
        $performance['distance']['average_per_visit'] = round($performance['distance']['total_km'] / $performance['visits']['total'], 2);
        $performance['time']['average_per_visit'] = round(($performance['time']['total_hours'] * 60) / $performance['visits']['total'], 2);
    }
    
    if ($performance['tasks']['total_assigned'] > 0) {
        $performance['tasks']['completion_rate'] = round(($performance['tasks']['completed'] / $performance['tasks']['total_assigned']) * 100, 2);
    }
    
    // Add comparison with previous period
    $performance['comparison'] = getPerformanceComparison($db, $user['id'], $period);
    
    $response['success'] = true;
    $response['message'] = 'Performance data retrieved successfully';
    $response['data'] = $performance;
    
    echo json_encode($response);
}

function handleGetNotifications($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
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

function handleCompleteTask($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'task_id' => 'required|integer',
        'completion_notes' => 'string',
        'completion_time' => 'date'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Verify task ownership
    $task = $db->fetchOne(
        "SELECT * FROM staff_tasks WHERE id = ? AND assigned_to = ?",
        [$input['task_id'], $user['id']]
    );
    
    if (!$task) {
        $response['message'] = 'Task not found or not assigned to you';
        echo json_encode($response);
        return;
    }
    
    if ($task['status'] !== 'pending') {
        $response['message'] = 'Task cannot be completed';
        echo json_encode($response);
        return;
    }
    
    // Update task
    $updateData = [
        'status' => 'completed',
        'completion_notes' => $input['completion_notes'] ?? '',
        'completed_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $db->update('staff_tasks', $updateData, 'id = ?', [$input['task_id']]);
    
    // Log activity
    $db->insert('staff_activities', [
        'staff_id' => $user['id'],
        'activity_type' => 'task_completed',
        'description' => 'Completed task: ' . $task['title'],
        'reference_id' => $input['task_id'],
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $response['success'] = true;
    $response['message'] = 'Task completed successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleUpdateLocation($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'accuracy' => 'numeric',
        'tracking_id' => 'integer'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Create GPS log
    $gpsLogData = [
        'staff_id' => $user['id'],
        'tracking_id' => $input['tracking_id'] ?? null,
        'latitude' => $input['latitude'],
        'longitude' => $input['longitude'],
        'accuracy' => $input['accuracy'] ?? null,
        'timestamp' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $logId = $db->insert('gps_logs', $gpsLogData);
    
    // Update tracking session if provided
    if (!empty($input['tracking_id'])) {
        $tracking = $db->fetchOne("SELECT * FROM gps_tracking WHERE id = ? AND staff_id = ?", [$input['tracking_id'], $user['id']]);
        
        if ($tracking) {
            // Calculate distance if previous location exists
            $lastLog = $db->fetchOne(
                "SELECT latitude, longitude FROM gps_logs WHERE tracking_id = ? ORDER BY timestamp DESC LIMIT 1",
                [$input['tracking_id']]
            );
            
            if ($lastLog) {
                $distance = calculateDistance($lastLog['latitude'], $lastLog['longitude'], $input['latitude'], $input['longitude']);
                
                // Update tracking session
                $db->update('gps_tracking', [
                    'end_latitude' => $input['latitude'],
                    'end_longitude' => $input['longitude'],
                    'distance_km' => $tracking['distance_km'] + $distance,
                    'duration_minutes' => $tracking['duration_minutes'] + 1, // Approximate
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [$input['tracking_id']]);
            }
        }
    }
    
    $response['success'] = true;
    $response['message'] = 'Location updated successfully';
    $response['data'] = ['log_id' => $logId];
    
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

function getPerformanceComparison($db, $staffId, $period) {
    $currentCondition = getDateCondition($period);
    $previousCondition = getPreviousPeriodCondition($period);
    
    $currentVisits = $db->fetchOne("SELECT COUNT(*) as count FROM gps_tracking WHERE staff_id = ? AND $currentCondition", [$staffId])['count'];
    $previousVisits = $db->fetchOne("SELECT COUNT(*) as count FROM gps_tracking WHERE staff_id = ? AND $previousCondition", [$staffId])['count'];
    
    $change = $previousVisits > 0 ? round((($currentVisits - $previousVisits) / $previousVisits) * 100, 2) : 0;
    
    return [
        'visits_change' => $change,
        'trend' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable'),
        'current_period' => $currentVisits,
        'previous_period' => $previousVisits
    ];
}

function getPreviousPeriodCondition($period) {
    switch ($period) {
        case 'day':
            return 'DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)';
        case 'week':
            return 'created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND created_at < DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
        case 'month':
            return 'created_at >= DATE_SUB(CURDATE(), INTERVAL 60 DAY) AND created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
        case 'year':
            return 'created_at >= DATE_SUB(CURDATE(), INTERVAL 730 DAY) AND created_at < DATE_SUB(CURDATE(), INTERVAL 365 DAY)';
        default:
            return 'created_at >= DATE_SUB(CURDATE(), INTERVAL 60 DAY) AND created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
    }
}

function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Earth's radius in kilometers
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}
?>
