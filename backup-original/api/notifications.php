<?php
/**
 * Notifications API
 * Handles notification system for all user roles
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
            handleGetNotifications($db, $validator);
            break;
        case 'unread':
            handleGetUnreadNotifications($db, $validator);
            break;
        case 'count':
            handleGetNotificationCount($db, $validator);
            break;
        case 'detail':
            handleGetNotificationDetail($db, $validator);
            break;
        case 'preferences':
            handleGetNotificationPreferences($db, $validator);
            break;
        case 'templates':
            handleGetNotificationTemplates($db, $validator);
            break;
        case 'history':
            handleGetNotificationHistory($db, $validator);
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
            handleCreateNotification($db, $validator);
            break;
        case 'bulk':
            handleBulkNotifications($db, $validator);
            break;
        case 'broadcast':
            handleBroadcastNotification($db, $validator);
            break;
        case 'send_email':
            handleSendEmailNotification($db, $validator);
            break;
        case 'send_sms':
            handleSendSMSNotification($db, $validator);
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
        case 'mark_read':
            handleMarkAsRead($db, $validator);
            break;
        case 'mark_all_read':
            handleMarkAllAsRead($db, $validator);
            break;
        case 'update_preferences':
            handleUpdatePreferences($db, $validator);
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
            handleDeleteNotification($db, $validator);
            break;
        case 'clear_all':
            handleClearAllNotifications($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleGetNotifications($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $type = $_GET['type'] ?? '';
    $status = $_GET['status'] ?? '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = ["user_id = ?"];
    $params = [$user['id']];
    
    if (!empty($type)) {
        $whereConditions[] = "type = ?";
        $params[] = $type;
    }
    
    if (!empty($status)) {
        $whereConditions[] = "is_read = ?";
        $params[] = $status === 'read' ? 1 : 0;
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
    $countSql = "SELECT COUNT(*) as total FROM notifications $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get notifications
    $sql = "SELECT * FROM notifications $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $notifications = $db->fetchAll($sql, $params);
    
    // Add notification metadata
    foreach ($notifications as &$notification) {
        $notification['type_display'] = getNotificationTypeDisplay($notification['type']);
        $notification['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($notification['created_at']));
        $notification['time_ago'] = getTimeAgo($notification['created_at']);
        
        // Get related data if available
        if ($notification['reference_id']) {
            $notification['related_data'] = getRelatedNotificationData($notification['reference_id'], $notification['type']);
        }
    }
    
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

function handleGetUnreadNotifications($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $notifications = $db->fetchAll(
        "SELECT * FROM notifications 
         WHERE user_id = ? AND is_read = 0 
         ORDER BY created_at DESC 
         LIMIT 50",
        [$user['id']]
    );
    
    foreach ($notifications as &$notification) {
        $notification['type_display'] = getNotificationTypeDisplay($notification['type']);
        $notification['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($notification['created_at']));
        $notification['time_ago'] = getTimeAgo($notification['created_at']);
        
        if ($notification['reference_id']) {
            $notification['related_data'] = getRelatedNotificationData($notification['reference_id'], $notification['type']);
        }
    }
    
    $response['success'] = true;
    $response['message'] = 'Unread notifications retrieved successfully';
    $response['data'] = $notifications;
    
    echo json_encode($response);
}

function handleGetNotificationCount($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $counts = [
        'total' => $db->fetchOne("SELECT COUNT(*) as count FROM notifications WHERE user_id = ?", [$user['id']])['count'],
        'unread' => $db->fetchOne("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0", [$user['id']])['count'],
        'by_type' => $db->fetchAll(
            "SELECT type, COUNT(*) as count 
             FROM notifications 
             WHERE user_id = ? 
             GROUP BY type",
            [$user['id']]
        ),
        'by_status' => [
            'read' => $db->fetchOne("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 1", [$user['id']])['count'],
            'unread' => $db->fetchOne("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0", [$user['id']])['count']
        ]
    ];
    
    $response['success'] = true;
    $response['message'] = 'Notification counts retrieved successfully';
    $response['data'] = $counts;
    
    echo json_encode($response);
}

function handleGetNotificationDetail($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $notificationId = (int)($_GET['id'] ?? 0);
    
    if ($notificationId <= 0) {
        $response['message'] = 'Notification ID required';
        echo json_encode($response);
        return;
    }
    
    $notification = $db->fetchOne(
        "SELECT * FROM notifications WHERE id = ? AND user_id = ?",
        [$notificationId, $user['id']]
    );
    
    if (!$notification) {
        $response['message'] = 'Notification not found';
        echo json_encode($response);
        return;
    }
    
    $notification['type_display'] = getNotificationTypeDisplay($notification['type']);
    $notification['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($notification['created_at']));
    $notification['time_ago'] = getTimeAgo($notification['created_at']);
    
    if ($notification['reference_id']) {
        $notification['related_data'] = getRelatedNotificationData($notification['reference_id'], $notification['type']);
    }
    
    // Get notification history
    $notification['history'] = $db->fetchAll(
        "SELECT * FROM notification_history WHERE notification_id = ? ORDER BY created_at DESC",
        [$notificationId]
    );
    
    $response['success'] = true;
    $response['message'] = 'Notification detail retrieved successfully';
    $response['data'] = $notification;
    
    echo json_encode($response);
}

function handleGetNotificationPreferences($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $preferences = $db->fetchOne(
        "SELECT * FROM notification_preferences WHERE user_id = ?",
        [$user['id']]
    );
    
    if (!$preferences) {
        // Create default preferences
        $defaultPreferences = [
            'user_id' => $user['id'],
            'email_notifications' => true,
            'sms_notifications' => true,
            'push_notifications' => true,
            'loan_notifications' => true,
            'savings_notifications' => true,
            'payment_notifications' => true,
            'system_notifications' => true,
            'marketing_notifications' => false,
            'daily_summary' => false,
            'weekly_summary' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $db->insert('notification_preferences', $defaultPreferences);
        $preferences = $defaultPreferences;
    }
    
    $response['success'] = true;
    $response['message'] = 'Notification preferences retrieved successfully';
    $response['data'] = $preferences;
    
    echo json_encode($response);
}

function handleGetNotificationTemplates($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $templates = $db->fetchAll(
        "SELECT * FROM notification_templates WHERE is_active = 1 ORDER BY category, name"
    );
    
    $response['success'] = true;
    $response['message'] = 'Notification templates retrieved successfully';
    $response['data'] = $templates;
    
    echo json_encode($response);
}

function handleGetNotificationHistory($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = ["user_id = ?"];
    $params = [$user['id']];
    
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
    $countSql = "SELECT COUNT(*) as total FROM notification_history $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get history
    $sql = "SELECT * FROM notification_history $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $history = $db->fetchAll($sql, $params);
    
    foreach ($history as &$item) {
        $item['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($item['created_at']));
        $item['status_display'] = ucfirst($item['status']);
    }
    
    $response['success'] = true;
    $response['message'] = 'Notification history retrieved successfully';
    $response['data'] = [
        'history' => $history,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    echo json_encode($response);
}

function handleCreateNotification($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'user_id' => 'required|integer',
        'title' => 'required|string|min:3',
        'message' => 'required|string|min:10',
        'type' => 'required|in:info,success,warning,error,loan,savings,payment,system',
        'reference_id' => 'integer',
        'priority' => 'in:low,medium,high',
        'expires_at' => 'date'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Verify target user exists
    $targetUser = $db->fetchOne("SELECT * FROM users WHERE id = ? AND is_active = 1", [$input['user_id']]);
    
    if (!$targetUser) {
        $response['message'] = 'Target user not found or inactive';
        echo json_encode($response);
        return;
    }
    
    $notificationData = [
        'user_id' => $input['user_id'],
        'title' => $input['title'],
        'message' => $input['message'],
        'type' => $input['type'],
        'reference_id' => $input['reference_id'] ?? null,
        'priority' => $input['priority'] ?? 'medium',
        'expires_at' => $input['expires_at'] ?? null,
        'is_read' => false,
        'created_by' => $user['id'],
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $notificationId = $db->insert('notifications', $notificationData);
    
    // Create notification history
    $db->insert('notification_history', [
        'notification_id' => $notificationId,
        'user_id' => $input['user_id'],
        'action' => 'created',
        'status' => 'pending',
        'created_by' => $user['id'],
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    // Check user preferences for sending notifications
    $preferences = $db->fetchOne("SELECT * FROM notification_preferences WHERE user_id = ?", [$input['user_id']]);
    
    if ($preferences) {
        // Send email notification if enabled
        if ($preferences['email_notifications'] && shouldSendEmailNotification($input['type'], $preferences)) {
            sendEmailNotification($targetUser['email'], $input['title'], $input['message']);
        }
        
        // Send SMS notification if enabled
        if ($preferences['sms_notifications'] && shouldSendSMSNotification($input['type'], $preferences)) {
            sendSMSNotification($targetUser['phone'], $input['title'], $input['message']);
        }
    }
    
    $response['success'] = true;
    $response['message'] = 'Notification created successfully';
    $response['data'] = ['notification_id' => $notificationId];
    
    echo json_encode($response);
}

function handleBulkNotifications($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'recipients' => 'required|array',
        'title' => 'required|string|min:3',
        'message' => 'required|string|min:10',
        'type' => 'required|in:info,success,warning,error,loan,savings,payment,system',
        'reference_id' => 'integer'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    $createdNotifications = [];
    $errors = [];
    
    foreach ($input['recipients'] as $userId) {
        try {
            // Verify user exists
            $targetUser = $db->fetchOne("SELECT * FROM users WHERE id = ? AND is_active = 1", [$userId]);
            
            if (!$targetUser) {
                $errors[] = "User ID $userId not found or inactive";
                continue;
            }
            
            $notificationData = [
                'user_id' => $userId,
                'title' => $input['title'],
                'message' => $input['message'],
                'type' => $input['type'],
                'reference_id' => $input['reference_id'] ?? null,
                'priority' => 'medium',
                'is_read' => false,
                'created_by' => $user['id'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $notificationId = $db->insert('notifications', $notificationData);
            
            $createdNotifications[] = [
                'user_id' => $userId,
                'notification_id' => $notificationId
            ];
            
        } catch (Exception $e) {
            $errors[] = "Failed to create notification for user ID $userId: " . $e->getMessage();
        }
    }
    
    $response['success'] = true;
    $response['message'] = 'Bulk notifications processed';
    $response['data'] = [
        'created' => $createdNotifications,
        'errors' => $errors,
        'total_processed' => count($input['recipients']),
        'successful' => count($createdNotifications),
        'failed' => count($errors)
    ];
    
    echo json_encode($response);
}

function handleBroadcastNotification($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'title' => 'required|string|min:3',
        'message' => 'required|string|min:10',
        'type' => 'required|in:info,success,warning,error,loan,savings,payment,system',
        'target_role' => 'required|in:all,admin,staff,member',
        'target_status' => 'in:active,inactive,all'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Build recipient query
    $whereConditions = [];
    $params = [];
    
    if ($input['target_role'] !== 'all') {
        $whereConditions[] = "role = ?";
        $params[] = $input['target_role'];
    }
    
    if ($input['target_status'] === 'active') {
        $whereConditions[] = "is_active = 1";
    } elseif ($input['target_status'] === 'inactive') {
        $whereConditions[] = "is_active = 0";
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get target users
    $targetUsers = $db->fetchAll("SELECT id FROM users $whereClause", $params);
    
    if (empty($targetUsers)) {
        $response['message'] = 'No target users found';
        echo json_encode($response);
        return;
    }
    
    $createdNotifications = [];
    $errors = [];
    
    foreach ($targetUsers as $targetUser) {
        try {
            $notificationData = [
                'user_id' => $targetUser['id'],
                'title' => $input['title'],
                'message' => $input['message'],
                'type' => $input['type'],
                'priority' => 'high',
                'is_read' => false,
                'created_by' => $user['id'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $notificationId = $db->insert('notifications', $notificationData);
            $createdNotifications[] = $notificationId;
            
        } catch (Exception $e) {
            $errors[] = "Failed to create notification for user ID {$targetUser['id']}: " . $e->getMessage();
        }
    }
    
    $response['success'] = true;
    $response['message'] = 'Broadcast notification sent';
    $response['data'] = [
        'total_recipients' => count($targetUsers),
        'successful' => count($createdNotifications),
        'failed' => count($errors),
        'errors' => $errors
    ];
    
    echo json_encode($response);
}

function handleSendEmailNotification($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'to_email' => 'required|email',
        'subject' => 'required|string|min:3',
        'message' => 'required|string|min:10',
        'template_id' => 'integer'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get template if specified
    $template = null;
    if (!empty($input['template_id'])) {
        $template = $db->fetchOne("SELECT * FROM notification_templates WHERE id = ? AND is_active = 1", [$input['template_id']]);
    }
    
    $emailContent = $template ? processTemplate($template, $input) : $input['message'];
    
    // Send email (this would integrate with actual email service)
    $emailResult = sendEmail($input['to_email'], $input['subject'], $emailContent);
    
    if ($emailResult['success']) {
        $response['success'] = true;
        $response['message'] = 'Email notification sent successfully';
        $response['data'] = [
            'message_id' => $emailResult['message_id'],
            'sent_to' => $input['to_email']
        ];
    } else {
        $response['message'] = 'Failed to send email: ' . $emailResult['error'];
    }
    
    echo json_encode($response);
}

function handleSendSMSNotification($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'to_phone' => 'required|string|min:10',
        'message' => 'required|string|min:5|max:160',
        'template_id' => 'integer'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get template if specified
    $template = null;
    if (!empty($input['template_id'])) {
        $template = $db->fetchOne("SELECT * FROM notification_templates WHERE id = ? AND is_active = 1 AND type = 'sms'", [$input['template_id']]);
    }
    
    $smsContent = $template ? processTemplate($template, $input) : $input['message'];
    
    // Send SMS (this would integrate with actual SMS service)
    $smsResult = sendSMS($input['to_phone'], $smsContent);
    
    if ($smsResult['success']) {
        $response['success'] = true;
        $response['message'] = 'SMS notification sent successfully';
        $response['data'] = [
            'message_id' => $smsResult['message_id'],
            'sent_to' => $input['to_phone']
        ];
    } else {
        $response['message'] = 'Failed to send SMS: ' . $smsResult['error'];
    }
    
    echo json_encode($response);
}

function handleMarkAsRead($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $notificationId = (int)($input['notification_id'] ?? 0);
    
    if ($notificationId <= 0) {
        $response['message'] = 'Notification ID required';
        echo json_encode($response);
        return;
    }
    
    // Verify notification ownership
    $notification = $db->fetchOne(
        "SELECT * FROM notifications WHERE id = ? AND user_id = ?",
        [$notificationId, $user['id']]
    );
    
    if (!$notification) {
        $response['message'] = 'Notification not found';
        echo json_encode($response);
        return;
    }
    
    // Update notification
    $db->update('notifications', ['is_read' => true, 'read_at' => date('Y-m-d H:i:s')], 'id = ?', [$notificationId]);
    
    // Create notification history
    $db->insert('notification_history', [
        'notification_id' => $notificationId,
        'user_id' => $user['id'],
        'action' => 'read',
        'status' => 'completed',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $response['success'] = true;
    $response['message'] = 'Notification marked as read';
    
    echo json_encode($response);
}

function handleMarkAllAsRead($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    // Get unread notifications
    $unreadNotifications = $db->fetchAll(
        "SELECT id FROM notifications WHERE user_id = ? AND is_read = 0",
        [$user['id']]
    );
    
    $updatedCount = 0;
    foreach ($unreadNotifications as $notification) {
        $db->update('notifications', ['is_read' => true, 'read_at' => date('Y-m-d H:i:s')], 'id = ?', [$notification['id']]);
        
        $db->insert('notification_history', [
            'notification_id' => $notification['id'],
            'user_id' => $user['id'],
            'action' => 'read',
            'status' => 'completed',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $updatedCount++;
    }
    
    $response['success'] = true;
    $response['message'] = 'All notifications marked as read';
    $response['data'] = ['updated_count' => $updatedCount];
    
    echo json_encode($response);
}

function handleUpdatePreferences($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'loan_notifications' => 'boolean',
        'savings_notifications' => 'boolean',
        'payment_notifications' => 'boolean',
        'system_notifications' => 'boolean',
        'marketing_notifications' => 'boolean',
        'daily_summary' => 'boolean',
        'weekly_summary' => 'boolean'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get existing preferences
    $existing = $db->fetchOne("SELECT * FROM notification_preferences WHERE user_id = ?", [$user['id']]);
    
    if ($existing) {
        // Update existing preferences
        $updateData = array_merge($input, ['updated_at' => date('Y-m-d H:i:s')]);
        $db->update('notification_preferences', $updateData, 'user_id = ?', [$user['id']]);
    } else {
        // Create new preferences
        $preferenceData = array_merge([
            'user_id' => $user['id'],
            'email_notifications' => true,
            'sms_notifications' => true,
            'push_notifications' => true,
            'loan_notifications' => true,
            'savings_notifications' => true,
            'payment_notifications' => true,
            'system_notifications' => true,
            'marketing_notifications' => false,
            'daily_summary' => false,
            'weekly_summary' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], $input);
        
        $db->insert('notification_preferences', $preferenceData);
    }
    
    $response['success'] = true;
    $response['message'] = 'Notification preferences updated successfully';
    
    echo json_encode($response);
}

function handleDeleteNotification($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $notificationId = (int)($_GET['id'] ?? 0);
    
    if ($notificationId <= 0) {
        $response['message'] = 'Notification ID required';
        echo json_encode($response);
        return;
    }
    
    // Verify notification ownership
    $notification = $db->fetchOne(
        "SELECT * FROM notifications WHERE id = ? AND user_id = ?",
        [$notificationId, $user['id']]
    );
    
    if (!$notification) {
        $response['message'] = 'Notification not found';
        echo json_encode($response);
        return;
    }
    
    // Soft delete notification
    $db->update('notifications', ['is_deleted' => true, 'deleted_at' => date('Y-m-d H:i:s')], 'id = ?', [$notificationId]);
    
    $response['success'] = true;
    $response['message'] = 'Notification deleted successfully';
    
    echo json_encode($response);
}

function handleClearAllNotifications($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    // Soft delete all notifications for user
    $db->update('notifications', ['is_deleted' => true, 'deleted_at' => date('Y-m-d H:i:s')], 'user_id = ?', [$user['id']]);
    
    $response['success'] = true;
    $response['message'] = 'All notifications cleared';
    
    echo json_encode($response);
}

// Helper functions
function getNotificationTypeDisplay($type) {
    $displays = [
        'info' => 'Information',
        'success' => 'Success',
        'warning' => 'Warning',
        'error' => 'Error',
        'loan' => 'Loan',
        'savings' => 'Savings',
        'payment' => 'Payment',
        'system' => 'System'
    ];
    
    return $displays[$type] ?? $type;
}

function getTimeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 2592000) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return date('M j, Y', $time);
    }
}

function getRelatedNotificationData($referenceId, $type) {
    global $db;
    
    switch ($type) {
        case 'loan':
            return $db->fetchOne("SELECT loan_number, amount, status FROM loans WHERE id = ?", [$referenceId]);
        case 'savings':
            return $db->fetchOne("SELECT account_number, balance, type FROM savings WHERE id = ?", [$referenceId]);
        case 'payment':
            return $db->fetchOne("SELECT transaction_number, amount, type FROM payment_transactions WHERE id = ?", [$referenceId]);
        case 'member':
            return $db->fetchOne("SELECT full_name, member_number FROM members WHERE id = ?", [$referenceId]);
        default:
            return null;
    }
}

function shouldSendEmailNotification($type, $preferences) {
    $typeMapping = [
        'loan' => $preferences['loan_notifications'],
        'savings' => $preferences['savings_notifications'],
        'payment' => $preferences['payment_notifications'],
        'system' => $preferences['system_notifications']
    ];
    
    return $typeMapping[$type] ?? true;
}

function shouldSendSMSNotification($type, $preferences) {
    // SMS typically for urgent notifications only
    $urgentTypes = ['error', 'warning', 'payment'];
    
    return in_array($type, $urgentTypes) && $preferences['sms_notifications'];
}

function sendEmail($to, $subject, $message) {
    // This would integrate with actual email service (SMTP, SendGrid, etc.)
    // For now, return mock success
    return [
        'success' => true,
        'message_id' => 'email_' . uniqid()
    ];
}

function sendSMS($to, $message) {
    // This would integrate with actual SMS service (Twilio, etc.)
    // For now, return mock success
    return [
        'success' => true,
        'message_id' => 'sms_' . uniqid()
    ];
}

function processTemplate($template, $data) {
    // Simple template processing
    $content = $template['content'];
    
    foreach ($data as $key => $value) {
        $content = str_replace('{{' . $key . '}}', $value, $content);
    }
    
    return $content;
}
?>
