<?php
/**
 * API Endpoints for Critical Gap Implementation
 * PWA, MFA, Payment Gateway, and Security features
 */

// Include necessary files
require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../helpers/MFA_System.php';
require_once __DIR__ . '/../helpers/PaymentGateway.php';
require_once __DIR__ . '/../helpers/SecurityAudit.php';

// Start session
session_start();

// Get action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Initialize response
$response = [
    'success' => false,
    'data' => null,
    'message' => 'Unknown action',
    'timestamp' => date('Y-m-d H:i:s')
];

try {
    switch ($action) {
        case 'enable_mfa':
            $response = enableMFA();
            break;
            
        case 'generate_otp':
            $response = generateOTP();
            break;
            
        case 'verify_otp':
            $response = verifyOTP();
            break;
            
        case 'create_qris_payment':
            $response = createQRISPayment();
            break;
            
        case 'create_ewallet_payment':
            $response = createEwalletPayment();
            break;
            
        case 'payment_status':
            $response = getPaymentStatus();
            break;
            
        case 'payment_callback':
            $response = processPaymentCallback();
            break;
            
        case 'user_balance':
            $response = getUserBalance();
            break;
            
        case 'security_audit':
            $response = getSecurityAudit();
            break;
            
        case 'security_alerts':
            $response = getSecurityAlerts();
            break;
            
        case 'sync_offline':
            $response = syncOfflineData();
            break;
            
        case 'app_installation':
            $response = trackAppInstallation();
            break;
            
        case 'push_notifications':
            $response = getPushNotifications();
            break;
            
        case 'mark_notification_read':
            $response = markNotificationRead();
            break;
            
        default:
            $response['message'] = 'Invalid action';
            break;
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'data' => null,
        'message' => 'Error: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);

/**
 * Enable MFA for user
 */
function enableMFA() {
    if (!isset($_SESSION['user_id'])) {
        return ['success' => false, 'message' => 'User not logged in'];
    }
    
    $user_id = $_SESSION['user_id'];
    $methods = $_POST['methods'] ?? ['sms', 'email'];
    
    $database = new Database();
    $mfa = new MFA_System($database);
    
    if ($mfa->enableMFA($user_id, $methods)) {
        return [
            'success' => true,
            'message' => 'MFA enabled successfully',
            'data' => ['methods' => $methods]
        ];
    }
    
    return ['success' => false, 'message' => 'Failed to enable MFA'];
}

/**
 * Generate OTP
 */
function generateOTP() {
    if (!isset($_SESSION['user_id'])) {
        return ['success' => false, 'message' => 'User not logged in'];
    }
    
    $user_id = $_SESSION['user_id'];
    $type = $_POST['type'] ?? 'sms';
    
    $database = new Database();
    $mfa = new MFA_System($database);
    
    $result = $mfa->generateOTP($user_id, $type);
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'OTP generated successfully',
            'data' => ['type' => $type, 'sent' => true]
        ];
    }
    
    return ['success' => false, 'message' => 'Failed to generate OTP'];
}

/**
 * Verify OTP
 */
function verifyOTP() {
    if (!isset($_SESSION['user_id'])) {
        return ['success' => false, 'message' => 'User not logged in'];
    }
    
    $user_id = $_SESSION['user_id'];
    $otp = $_POST['otp'] ?? '';
    $type = $_POST['type'] ?? 'sms';
    
    if (empty($otp)) {
        return ['success' => false, 'message' => 'OTP is required'];
    }
    
    $database = new Database();
    $mfa = new MFA_System($database);
    
    if ($mfa->verifyOTP($user_id, $otp, $type)) {
        return [
            'success' => true,
            'message' => 'OTP verified successfully',
            'data' => ['verified' => true]
        ];
    }
    
    return ['success' => false, 'message' => 'Invalid OTP'];
}

/**
 * Create QRIS Payment
 */
function createQRISPayment() {
    if (!isset($_SESSION['user_id'])) {
        return ['success' => false, 'message' => 'User not logged in'];
    }
    
    $order_data = [
        'order_id' => 'QRIS_' . $_SESSION['user_id'] . '_' . time(),
        'amount' => $_POST['amount'] ?? 0,
        'description' => $_POST['description'] ?? 'Pembayaran QRIS',
        'customer_name' => $_SESSION['user_name'] ?? '',
        'customer_email' => $_SESSION['user_email'] ?? ''
    ];
    
    $database = new Database();
    $payment = new PaymentGateway($database);
    
    $result = $payment->createQRISPayment($order_data);
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'QRIS payment created successfully',
            'data' => $result
        ];
    }
    
    return ['success' => false, 'message' => 'Failed to create QRIS payment'];
}

/**
 * Create E-wallet Payment
 */
function createEwalletPayment() {
    if (!isset($_SESSION['user_id'])) {
        return ['success' => false, 'message' => 'User not logged in'];
    }
    
    $wallet_type = $_POST['wallet_type'] ?? '';
    $order_data = [
        'order_id' => $wallet_type . '_' . $_SESSION['user_id'] . '_' . time(),
        'amount' => $_POST['amount'] ?? 0,
        'description' => $_POST['description'] ?? 'Pembayaran E-wallet',
        'customer_name' => $_SESSION['user_name'] ?? '',
        'customer_email' => $_SESSION['user_email'] ?? '',
        'customer_phone' => $_SESSION['user_phone'] ?? ''
    ];
    
    $database = new Database();
    $payment = new PaymentGateway($database);
    
    $result = $payment->createEwalletPayment($order_data, $wallet_type);
    
    if ($result && !isset($result['error'])) {
        return [
            'success' => true,
            'message' => 'E-wallet payment created successfully',
            'data' => $result
        ];
    }
    
    return ['success' => false, 'message' => 'Failed to create E-wallet payment'];
}

/**
 * Get Payment Status
 */
function getPaymentStatus() {
    $payment_id = $_GET['payment_id'] ?? '';
    
    if (empty($payment_id)) {
        return ['success' => false, 'message' => 'Payment ID is required'];
    }
    
    $database = new Database();
    $payment = new PaymentGateway($database);
    
    $status = $payment->getPaymentStatus($payment_id);
    
    if ($status) {
        return [
            'success' => true,
            'message' => 'Payment status retrieved',
            'data' => $status
        ];
    }
    
    return ['success' => false, 'message' => 'Payment not found'];
}

/**
 * Process Payment Callback
 */
function processPaymentCallback() {
    $payment_type = $_GET['type'] ?? '';
    $callback_data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($payment_type) || empty($callback_data)) {
        return ['success' => false, 'message' => 'Invalid callback data'];
    }
    
    $database = new Database();
    $payment = new PaymentGateway($database);
    
    $result = $payment->processPaymentCallback($payment_type, $callback_data);
    
    if ($result && $result['success']) {
        return [
            'success' => true,
            'message' => 'Payment callback processed',
            'data' => $result
        ];
    }
    
    return ['success' => false, 'message' => 'Failed to process callback'];
}

/**
 * Get User Balance
 */
function getUserBalance() {
    if (!isset($_SESSION['user_id'])) {
        return ['success' => false, 'message' => 'User not logged in'];
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Get user balance from database
    $database = new Database();
    $stmt = $database->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN type = 'DEPOSIT' THEN amount ELSE -amount END), 0) as balance
        FROM transactions 
        WHERE user_id = ? AND status = 'COMPLETED'
    ");
    $stmt->execute([$user_id]);
    $balance = $stmt->fetchColumn();
    
    return [
        'success' => true,
        'message' => 'Balance retrieved',
        'data' => ['balance' => $balance]
    ];
}

/**
 * Get Security Audit
 */
function getSecurityAudit() {
    if (!isset($_SESSION['user_id'])) {
        return ['success' => false, 'message' => 'User not logged in'];
    }
    
    $user_id = $_SESSION['user_id'];
    
    $database = new Database();
    $security = new SecurityAudit($database);
    
    $profile = $security->getUserSecurityProfile($user_id);
    
    return [
        'success' => true,
        'message' => 'Security profile retrieved',
        'data' => $profile
    ];
}

/**
 * Get Security Alerts
 */
function getSecurityAlerts() {
    if (!in_array($_SESSION['user_role'] ?? '', ['admin', 'super_admin'])) {
        return ['success' => false, 'message' => 'Access denied'];
    }
    
    $database = new Database();
    $security = new SecurityAudit($database);
    
    $report = $security->generateSecurityReport(7); // Last 7 days
    
    return [
        'success' => true,
        'message' => 'Security report retrieved',
        'data' => $report
    ];
}

/**
 * Sync Offline Data
 */
function syncOfflineData() {
    if (!isset($_SESSION['user_id'])) {
        return ['success' => false, 'message' => 'User not logged in'];
    }
    
    $user_id = $_SESSION['user_id'];
    $device_id = $_POST['device_id'] ?? '';
    $transactions = $_POST['transactions'] ?? [];
    
    $database = new Database();
    $synced_count = 0;
    
    foreach ($transactions as $transaction) {
        try {
            $stmt = $database->prepare("
                INSERT INTO offline_transactions (user_id, transaction_data, device_id, status)
                VALUES (?, ?, ?, 'synced')
            ");
            $stmt->execute([$user_id, json_encode($transaction), $device_id]);
            $synced_count++;
        } catch (Exception $e) {
            // Log error but continue with other transactions
            error_log("Sync error: " . $e->getMessage());
        }
    }
    
    return [
        'success' => true,
        'message' => 'Offline data synced',
        'data' => ['synced_count' => $synced_count]
    ];
}

/**
 * Track App Installation
 */
function trackAppInstallation() {
    if (!isset($_SESSION['user_id'])) {
        return ['success' => false, 'message' => 'User not logged in'];
    }
    
    $user_id = $_SESSION['user_id'];
    $device_id = $_POST['device_id'] ?? '';
    $platform = $_POST['platform'] ?? 'desktop';
    $app_version = $_POST['app_version'] ?? '1.0.0';
    
    $database = new Database();
    
    try {
        $stmt = $database->prepare("
            INSERT INTO app_installations (user_id, device_id, platform, app_version)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                last_used = NOW(),
                app_version = VALUES(app_version)
        ");
        $stmt->execute([$user_id, $device_id, $platform, $app_version]);
        
        return [
            'success' => true,
            'message' => 'App installation tracked',
            'data' => ['tracked' => true]
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to track installation'];
    }
}

/**
 * Get Push Notifications
 */
function getPushNotifications() {
    if (!isset($_SESSION['user_id'])) {
        return ['success' => false, 'message' => 'User not logged in'];
    }
    
    $user_id = $_SESSION['user_id'];
    $limit = $_GET['limit'] ?? 10;
    
    $database = new Database();
    
    $stmt = $database->prepare("
        SELECT id, title, message, type, data, read, created_at
        FROM push_notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'success' => true,
        'message' => 'Notifications retrieved',
        'data' => ['notifications' => $notifications]
    ];
}

/**
 * Mark Notification as Read
 */
function markNotificationRead() {
    if (!isset($_SESSION['user_id'])) {
        return ['success' => false, 'message' => 'User not logged in'];
    }
    
    $user_id = $_SESSION['user_id'];
    $notification_id = $_POST['notification_id'] ?? '';
    
    if (empty($notification_id)) {
        return ['success' => false, 'message' => 'Notification ID is required'];
    }
    
    $database = new Database();
    
    try {
        $stmt = $database->prepare("
            UPDATE push_notifications 
            SET read = TRUE, read_at = NOW() 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$notification_id, $user_id]);
        
        return [
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => ['marked' => true]
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to mark notification'];
    }
}

/**
 * Send Push Notification (Helper function)
 */
function sendPushNotification($user_id, $title, $message, $type = 'system', $data = []) {
    $database = new Database();
    
    try {
        $stmt = $database->prepare("
            INSERT INTO push_notifications (user_id, title, message, type, data)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $title, $message, $type, json_encode($data)]);
        
        return true;
    } catch (Exception $e) {
        error_log("Push notification error: " . $e->getMessage());
        return false;
    }
}
?>
