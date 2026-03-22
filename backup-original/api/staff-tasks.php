<?php
/**
 * Staff Tasks API
 * Handles task management for staff members
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
            handleListTasks($db, $validator);
            break;
        case 'detail':
            handleGetTaskDetail($db, $validator);
            break;
        case 'my_tasks':
            handleGetMyTasks($db, $validator);
            break;
        case 'calendar':
            handleGetTaskCalendar($db, $validator);
            break;
        case 'statistics':
            handleGetTaskStatistics($db, $validator);
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
            handleCreateTask($db, $validator);
            break;
        case 'assign':
            handleAssignTask($db, $validator);
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
            handleUpdateTask($db, $validator);
            break;
        case 'complete':
            handleCompleteTask($db, $validator);
            break;
        case 'status':
            handleUpdateTaskStatus($db, $validator);
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
        case 'delete':
            handleDeleteTask($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleListTasks($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $status = $_GET['status'] ?? '';
    $priority = $_GET['priority'] ?? '';
    $assignedTo = (int)($_GET['assigned_to'] ?? 0);
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = [];
    $params = [];
    
    if (!empty($status)) {
        $whereConditions[] = "st.status = ?";
        $params[] = $status;
    }
    
    if (!empty($priority)) {
        $whereConditions[] = "st.priority = ?";
        $params[] = $priority;
    }
    
    if ($assignedTo > 0) {
        $whereConditions[] = "st.assigned_to = ?";
        $params[] = $assignedTo;
    }
    
    if (!empty($dateFrom)) {
        $whereConditions[] = "st.due_date >= ?";
        $params[] = $dateFrom;
    }
    
    if (!empty($dateTo)) {
        $whereConditions[] = "st.due_date <= ?";
        $params[] = $dateTo;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM staff_tasks st $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get tasks
    $sql = "SELECT st.*, 
                    u1.full_name as created_by_name,
                    u2.full_name as assigned_to_name,
                    CASE 
                        WHEN st.due_date < CURDATE() AND st.status = 'pending' THEN 'overdue'
                        WHEN st.due_date = CURDATE() AND st.status = 'pending' THEN 'due_today'
                        WHEN st.due_date > CURDATE() AND st.status = 'pending' THEN 'upcoming'
                        ELSE st.status
                    END as task_status,
                    DATEDIFF(st.due_date, CURDATE()) as days_until_due
             FROM staff_tasks st 
             LEFT JOIN users u1 ON st.created_by = u1.id 
             LEFT JOIN users u2 ON st.assigned_to = u2.id 
             $whereClause
             ORDER BY st.due_date ASC, st.priority DESC, st.created_at DESC 
             LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $tasks = $db->fetchAll($sql, $params);
    
    // Add task metadata
    foreach ($tasks as &$task) {
        $task['priority_display'] = ucfirst($task['priority']);
        $task['status_display'] = ucfirst($task['status']);
        $task['due_date_formatted'] = date('Y-m-d', strtotime($task['due_date']));
        
        // Add related member info if applicable
        if ($task['member_id']) {
            $member = $db->fetchOne("SELECT full_name, member_number FROM members WHERE id = ?", [$task['member_id']]);
            $task['member'] = $member;
        }
        
        // Add related loan info if applicable
        if ($task['loan_id']) {
            $loan = $db->fetchOne("SELECT loan_number, amount FROM loans WHERE id = ?", [$task['loan_id']]);
            $task['loan'] = $loan;
        }
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

function handleGetTaskDetail($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $taskId = (int)($_GET['id'] ?? 0);
    
    if ($taskId <= 0) {
        $response['message'] = 'Task ID required';
        echo json_encode($response);
        return;
    }
    
    // Get task details
    $task = $db->fetchOne(
        "SELECT st.*, 
                u1.full_name as created_by_name,
                u2.full_name as assigned_to_name
         FROM staff_tasks st 
         LEFT JOIN users u1 ON st.created_by = u1.id 
         LEFT JOIN users u2 ON st.assigned_to = u2.id 
         WHERE st.id = ?",
        [$taskId]
    );
    
    if (!$task) {
        $response['message'] = 'Task not found';
        echo json_encode($response);
        return;
    }
    
    // Add related information
    if ($task['member_id']) {
        $task['member'] = $db->fetchOne("SELECT * FROM members WHERE id = ?", [$task['member_id']]);
    }
    
    if ($task['loan_id']) {
        $task['loan'] = $db->fetchOne("SELECT * FROM loans WHERE id = ?", [$task['loan_id']]);
    }
    
    // Get task history
    $task['history'] = $db->fetchAll(
        "SELECT * FROM task_history WHERE task_id = ? ORDER BY created_at DESC",
        [$taskId]
    );
    
    $response['success'] = true;
    $response['message'] = 'Task detail retrieved successfully';
    $response['data'] = $task;
    
    echo json_encode($response);
}

function handleGetMyTasks($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $status = $_GET['status'] ?? '';
    $priority = $_GET['priority'] ?? '';
    
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
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM staff_tasks $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get tasks
    $sql = "SELECT st.*, 
                    u.full_name as created_by_name,
                    CASE 
                        WHEN st.due_date < CURDATE() AND st.status = 'pending' THEN 'overdue'
                        WHEN st.due_date = CURDATE() AND st.status = 'pending' THEN 'due_today'
                        WHEN st.due_date > CURDATE() AND st.status = 'pending' THEN 'upcoming'
                        ELSE st.status
                    END as task_status,
                    DATEDIFF(st.due_date, CURDATE()) as days_until_due
             FROM staff_tasks st 
             LEFT JOIN users u ON st.created_by = u.id 
             $whereClause
             ORDER BY st.due_date ASC, st.priority DESC, st.created_at DESC 
             LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $tasks = $db->fetchAll($sql, $params);
    
    // Add task metadata
    foreach ($tasks as &$task) {
        $task['priority_display'] = ucfirst($task['priority']);
        $task['status_display'] = ucfirst($task['status']);
        
        // Add related member info if applicable
        if ($task['member_id']) {
            $member = $db->fetchOne("SELECT full_name, member_number FROM members WHERE id = ?", [$task['member_id']]);
            $task['member'] = $member;
        }
    }
    
    $response['success'] = true;
    $response['message'] = 'My tasks retrieved successfully';
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

function handleGetTaskCalendar($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $month = (int)($_GET['month'] ?? date('n'));
    $year = (int)($_GET['year'] ?? date('Y'));
    
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate = date('Y-m-t', strtotime($startDate));
    
    // Get tasks for the month
    $tasks = $db->fetchAll(
        "SELECT st.*, 
                u.full_name as assigned_to_name,
                m.full_name as member_name,
                m.member_number
         FROM staff_tasks st 
         LEFT JOIN users u ON st.assigned_to = u.id 
         LEFT JOIN members m ON st.member_id = m.id 
         WHERE st.due_date BETWEEN ? AND ?
         ORDER BY st.due_date ASC, st.priority DESC",
        [$startDate, $endDate]
    );
    
    // Organize tasks by date
    $calendar = [];
    foreach ($tasks as $task) {
        $date = date('Y-m-d', strtotime($task['due_date']));
        if (!isset($calendar[$date])) {
            $calendar[$date] = [];
        }
        $calendar[$date][] = $task;
    }
    
    $response['success'] = true;
    $response['message'] = 'Task calendar retrieved successfully';
    $response['data'] = [
        'month' => $month,
        'year' => $year,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'calendar' => $calendar,
        'summary' => [
            'total_tasks' => count($tasks),
            'pending_tasks' => count(array_filter($tasks, fn($t) => $t['status'] === 'pending')),
            'completed_tasks' => count(array_filter($tasks, fn($t) => $t['status'] === 'completed')),
            'overdue_tasks' => count(array_filter($tasks, fn($t) => $t['due_date'] < date('Y-m-d') && $t['status'] === 'pending'))
        ]
    ];
    
    echo json_encode($response);
}

function handleGetTaskStatistics($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $period = $_GET['period'] ?? 'month'; // day, week, month, year
    
    $dateCondition = getDateCondition($period);
    
    $statistics = [
        'overview' => [
            'total_tasks' => $db->fetchOne("SELECT COUNT(*) as count FROM staff_tasks WHERE assigned_to = ? AND $dateCondition", [$user['id']])['count'],
            'completed_tasks' => $db->fetchOne("SELECT COUNT(*) as count FROM staff_tasks WHERE assigned_to = ? AND status = 'completed' AND $dateCondition", [$user['id']])['count'],
            'pending_tasks' => $db->fetchOne("SELECT COUNT(*) as count FROM staff_tasks WHERE assigned_to = ? AND status = 'pending' AND $dateCondition", [$user['id']])['count'],
            'overdue_tasks' => $db->fetchOne("SELECT COUNT(*) as count FROM staff_tasks WHERE assigned_to = ? AND status = 'pending' AND due_date < CURDATE() AND $dateCondition", [$user['id']])['count']
        ],
        'by_priority' => $db->fetchAll(
            "SELECT priority, COUNT(*) as count, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
             FROM staff_tasks 
             WHERE assigned_to = ? AND $dateCondition
             GROUP BY priority
             ORDER BY FIELD(priority, 'high', 'medium', 'low')",
            [$user['id']]
        ),
        'by_type' => $db->fetchAll(
            "SELECT task_type, COUNT(*) as count, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
             FROM staff_tasks 
             WHERE assigned_to = ? AND $dateCondition
             GROUP BY task_type
             ORDER BY count DESC",
            [$user['id']]
        ),
        'completion_trend' => $db->fetchAll(
            "SELECT DATE(completed_at) as date, COUNT(*) as completed
             FROM staff_tasks 
             WHERE assigned_to = ? AND status = 'completed' AND completed_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY DATE(completed_at)
             ORDER BY date",
            [$user['id']]
        ),
        'performance' => [
            'completion_rate' => 0,
            'average_completion_time' => 0,
            'on_time_completion_rate' => 0
        ]
    ];
    
    // Calculate performance metrics
    $totalTasks = $statistics['overview']['total_tasks'];
    $completedTasks = $statistics['overview']['completed_tasks'];
    
    if ($totalTasks > 0) {
        $statistics['performance']['completion_rate'] = round(($completedTasks / $totalTasks) * 100, 2);
    }
    
    // Average completion time
    $avgTime = $db->fetchOne(
        "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_hours 
         FROM staff_tasks 
         WHERE assigned_to = ? AND status = 'completed' AND $dateCondition",
        [$user['id']]
    )['avg_hours'];
    
    $statistics['performance']['average_completion_time'] = round($avgTime ?? 0, 2);
    
    // On-time completion rate
    $onTimeCompleted = $db->fetchOne(
        "SELECT COUNT(*) as count 
         FROM staff_tasks 
         WHERE assigned_to = ? AND status = 'completed' AND completed_at <= due_date AND $dateCondition",
        [$user['id']]
    )['count'];
    
    $totalCompleted = $statistics['overview']['completed_tasks'];
    if ($totalCompleted > 0) {
        $statistics['performance']['on_time_completion_rate'] = round(($onTimeCompleted / $totalCompleted) * 100, 2);
    }
    
    $response['success'] = true;
    $response['message'] = 'Task statistics retrieved successfully';
    $response['data'] = $statistics;
    
    echo json_encode($response);
}

function handleCreateTask($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'title' => 'required|string|min:3',
        'description' => 'required|string|min:10',
        'task_type' => 'required|in:visit,call,follow_up,documentation,loan_processing,member_service,other',
        'priority' => 'required|in:low,medium,high',
        'assigned_to' => 'required|integer',
        'due_date' => 'required|date',
        'member_id' => 'integer',
        'loan_id' => 'integer'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Verify assigned staff exists
    $assignedStaff = $db->fetchOne("SELECT * FROM users WHERE id = ? AND is_active = 1", [$input['assigned_to']]);
    
    if (!$assignedStaff) {
        $response['message'] = 'Assigned staff not found or inactive';
        echo json_encode($response);
        return;
    }
    
    // Verify member exists if provided
    if (!empty($input['member_id'])) {
        $member = $db->fetchOne("SELECT * FROM members WHERE id = ? AND status = 'Active'", [$input['member_id']]);
        
        if (!$member) {
            $response['message'] = 'Member not found or inactive';
            echo json_encode($response);
            return;
        }
    }
    
    // Verify loan exists if provided
    if (!empty($input['loan_id'])) {
        $loan = $db->fetchOne("SELECT * FROM loans WHERE id = ?", [$input['loan_id']]);
        
        if (!$loan) {
            $response['message'] = 'Loan not found';
            echo json_encode($response);
            return;
        }
    }
    
    $taskData = [
        'title' => $input['title'],
        'description' => $input['description'],
        'task_type' => $input['task_type'],
        'priority' => $input['priority'],
        'assigned_to' => $input['assigned_to'],
        'created_by' => $user['id'],
        'member_id' => $input['member_id'] ?? null,
        'loan_id' => $input['loan_id'] ?? null,
        'due_date' => $input['due_date'],
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $taskId = $db->insert('staff_tasks', $taskData);
    
    // Create task history entry
    $db->insert('task_history', [
        'task_id' => $taskId,
        'action' => 'created',
        'description' => 'Task created by ' . $user['full_name'],
        'user_id' => $user['id'],
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    // Create notification for assigned staff
    $db->insert('notifications', [
        'user_id' => $input['assigned_to'],
        'title' => 'New Task Assigned',
        'message' => "You have been assigned a new task: " . $input['title'],
        'type' => 'info',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $response['success'] = true;
    $response['message'] = 'Task created successfully';
    $response['data'] = ['task_id' => $taskId];
    
    echo json_encode($response);
}

function handleAssignTask($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'task_id' => 'required|integer',
        'assigned_to' => 'required|integer',
        'notes' => 'string'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get current task
    $task = $db->fetchOne("SELECT * FROM staff_tasks WHERE id = ?", [$input['task_id']]);
    
    if (!$task) {
        $response['message'] = 'Task not found';
        echo json_encode($response);
        return;
    }
    
    // Verify assigned staff exists
    $assignedStaff = $db->fetchOne("SELECT * FROM users WHERE id = ? AND is_active = 1", [$input['assigned_to']]);
    
    if (!$assignedStaff) {
        $response['message'] = 'Assigned staff not found or inactive';
        echo json_encode($response);
        return;
    }
    
    // Update task assignment
    $updateData = [
        'assigned_to' => $input['assigned_to'],
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $db->update('staff_tasks', $updateData, 'id = ?', [$input['task_id']]);
    
    // Create task history entry
    $db->insert('task_history', [
        'task_id' => $input['task_id'],
        'action' => 'assigned',
        'description' => 'Task assigned to ' . $assignedStaff['full_name'] . ' by ' . $user['full_name'],
        'user_id' => $user['id'],
        'notes' => $input['notes'] ?? '',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    // Create notification for assigned staff
    $db->insert('notifications', [
        'user_id' => $input['assigned_to'],
        'title' => 'Task Assigned',
        'message' => "You have been assigned task: " . $task['title'],
        'type' => 'info',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $response['success'] = true;
    $response['message'] = 'Task assigned successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleUpdateTask($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'task_id' => 'required|integer',
        'title' => 'string|min:3',
        'description' => 'string|min:10',
        'priority' => 'in:low,medium,high',
        'due_date' => 'date',
        'notes' => 'string'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get current task
    $task = $db->fetchOne("SELECT * FROM staff_tasks WHERE id = ?", [$input['task_id']]);
    
    if (!$task) {
        $response['message'] = 'Task not found';
        echo json_encode($response);
        return;
    }
    
    // Only allow update if assigned to current user or created by current user
    if ($task['assigned_to'] !== $user['id'] && $task['created_by'] !== $user['id']) {
        $response['message'] = 'Not authorized to update this task';
        echo json_encode($response);
        return;
    }
    
    // Update only allowed fields
    $allowedFields = ['title', 'description', 'priority', 'due_date'];
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
    
    $db->update('staff_tasks', $updateData, 'id = ?', [$input['task_id']]);
    
    // Create task history entry
    $db->insert('task_history', [
        'task_id' => $input['task_id'],
        'action' => 'updated',
        'description' => 'Task updated by ' . $user['full_name'],
        'user_id' => $user['id'],
        'notes' => $input['notes'] ?? '',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $response['success'] = true;
    $response['message'] = 'Task updated successfully';
    $response['data'] = $updateData;
    
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
    
    // Get current task
    $task = $db->fetchOne("SELECT * FROM staff_tasks WHERE id = ?", [$input['task_id']]);
    
    if (!$task) {
        $response['message'] = 'Task not found';
        echo json_encode($response);
        return;
    }
    
    // Only allow completion if assigned to current user
    if ($task['assigned_to'] !== $user['id']) {
        $response['message'] = 'Not authorized to complete this task';
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
    
    // Create task history entry
    $db->insert('task_history', [
        'task_id' => $input['task_id'],
        'action' => 'completed',
        'description' => 'Task completed by ' . $user['full_name'],
        'user_id' => $user['id'],
        'notes' => $input['completion_notes'] ?? '',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
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

function handleUpdateTaskStatus($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'task_id' => 'required|integer',
        'status' => 'required|in:pending,in_progress,completed,cancelled',
        'notes' => 'string'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get current task
    $task = $db->fetchOne("SELECT * FROM staff_tasks WHERE id = ?", [$input['task_id']]);
    
    if (!$task) {
        $response['message'] = 'Task not found';
        echo json_encode($response);
        return;
    }
    
    // Only allow status update if assigned to current user or created by current user
    if ($task['assigned_to'] !== $user['id'] && $task['created_by'] !== $user['id']) {
        $response['message'] = 'Not authorized to update this task';
        echo json_encode($response);
        return;
    }
    
    $updateData = [
        'status' => $input['status'],
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if ($input['status'] === 'completed') {
        $updateData['completed_at'] = date('Y-m-d H:i:s');
    }
    
    $db->update('staff_tasks', $updateData, 'id = ?', [$input['task_id']]);
    
    // Create task history entry
    $db->insert('task_history', [
        'task_id' => $input['task_id'],
        'action' => 'status_changed',
        'description' => 'Status changed to ' . $input['status'] . ' by ' . $user['full_name'],
        'user_id' => $user['id'],
        'notes' => $input['notes'] ?? '',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $response['success'] = true;
    $response['message'] = 'Task status updated successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleDeleteTask($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $taskId = (int)($_GET['id'] ?? 0);
    
    if ($taskId <= 0) {
        $response['message'] = 'Task ID required';
        echo json_encode($response);
        return;
    }
    
    // Get current task
    $task = $db->fetchOne("SELECT * FROM staff_tasks WHERE id = ?", [$taskId]);
    
    if (!$task) {
        $response['message'] = 'Task not found';
        echo json_encode($response);
        return;
    }
    
    // Only allow deletion if created by current user
    if ($task['created_by'] !== $user['id']) {
        $response['message'] = 'Not authorized to delete this task';
        echo json_encode($response);
        return;
    }
    
    // Soft delete task
    $db->update('staff_tasks', ['status' => 'deleted', 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$taskId]);
    
    // Create task history entry
    $db->insert('task_history', [
        'task_id' => $taskId,
        'action' => 'deleted',
        'description' => 'Task deleted by ' . $user['full_name'],
        'user_id' => $user['id'],
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $response['success'] = true;
    $response['message'] = 'Task deleted successfully';
    
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
?>
