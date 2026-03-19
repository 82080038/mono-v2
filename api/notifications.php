<?php
/**
 * Push Notifications API Endpoint
 * Handles notifications and alerts for web application
 */

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/PushNotifications.php';

// Initialize push notifications
$notifications = new PushNotifications();

// Get request method and data
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($method == 'OPTIONS') {
    exit(0);
}

// Route requests
$endpoint = $_GET['action'] ?? $input['action'] ?? '';

switch ($endpoint) {
    case 'send_notification':
        handleSendNotification();
        break;
        
    case 'send_bulk':
        handleSendBulk();
        break;
        
    case 'schedule_notification':
        handleScheduleNotification();
        break;
        
    case 'process_scheduled':
        handleProcessScheduled();
        break;
        
    case 'get_notifications':
        handleGetNotifications();
        break;
        
    case 'mark_read':
        handleMarkAsRead();
        break;
        
    case 'subscribe_push':
        handleSubscribePush();
        break;
        
    case 'unsubscribe_push':
        handleUnsubscribePush();
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint tidak ditemukan'
        ]);
}

/**
 * Send notification
 */
function handleSendNotification() {
    global $notifications;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['user_id']) || !isset($data['title']) || !isset($data['message'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Data notifikasi tidak lengkap'
        ]);
        return;
    }
    
    $result = $notifications->sendNotification(
        $data['user_id'],
        $data['title'],
        $data['message'],
        $data['type'] ?? 'info',
        $data['channels'] ?? ['push'],
        $data['data'] ?? []
    );
    
    echo json_encode($result);
}

/**
 * Send bulk notification
 */
function handleSendBulk() {
    global $notifications;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['user_ids']) || !isset($data['title']) || !isset($data['message'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Data bulk notifikasi tidak lengkap'
        ]);
        return;
    }
    
    $result = $notifications->sendBulkNotification(
        $data['user_ids'],
        $data['title'],
        $data['message'],
        $data['type'] ?? 'info',
        $data['channels'] ?? ['push'],
        $data['data'] ?? []
    );
    
    echo json_encode($result);
}

/**
 * Schedule notification
 */
function handleScheduleNotification() {
    global $notifications;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['user_id']) || !isset($data['title']) || !isset($data['message']) || !isset($data['scheduled_at'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Data scheduled notifikasi tidak lengkap'
        ]);
        return;
    }
    
    $result = $notifications->scheduleNotification(
        $data['user_id'],
        $data['title'],
        $data['message'],
        $data['scheduled_at'],
        $data['type'] ?? 'info',
        $data['channels'] ?? ['push'],
        $data['data'] ?? []
    );
    
    echo json_encode($result);
}

/**
 * Process scheduled notifications
 */
function handleProcessScheduled() {
    global $notifications;
    
    $result = $notifications->processScheduledNotifications();
    echo json_encode($result);
}

/**
 * Get user notifications
 */
function handleGetNotifications() {
    global $notifications;
    
    $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
    $limit = $_GET['limit'] ?? $_POST['limit'] ?? 50;
    $offset = $_GET['offset'] ?? $_POST['offset'] ?? 0;
    
    if (!$userId) {
        echo json_encode([
            'success' => false,
            'message' => 'User ID diperlukan'
        ]);
        return;
    }
    
    $result = $notifications->getUserNotifications($userId, $limit, $offset);
    echo json_encode($result);
}

/**
 * Mark notification as read
 */
function handleMarkAsRead() {
    global $notifications;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['notification_id']) || !isset($data['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Data tidak lengkap'
        ]);
        return;
    }
    
    $result = $notifications->markAsRead($data['notification_id'], $data['user_id']);
    echo json_encode($result);
}

/**
 * Subscribe to push notifications
 */
function handleSubscribePush() {
    global $notifications;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['user_id']) || !isset($data['subscription'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Data subscription tidak lengkap'
        ]);
        return;
    }
    
    $db = Database::getInstance();
    
    try {
        // Remove existing subscription
        $db->delete('push_subscriptions', 'user_id = ?', [$data['user_id']]);
        
        // Add new subscription
        $subscriptionData = [
            'user_id' => $data['user_id'],
            'subscription' => json_encode($data['subscription']),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $db->insert('push_subscriptions', $subscriptionData);
        
        echo json_encode([
            'success' => true,
            'message' => 'Push subscription berhasil'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

/**
 * Unsubscribe from push notifications
 */
function handleUnsubscribePush() {
    global $notifications;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'User ID diperlukan'
        ]);
        return;
    }
    
    $db = Database::getInstance();
    
    try {
        $db->update('push_subscriptions', [
            'status' => 'inactive',
            'updated_at' => date('Y-m-d H:i:s')
        ], 'user_id = ?', [$data['user_id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Push subscription dibatalkan'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

?>
