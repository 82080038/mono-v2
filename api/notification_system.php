<?php
/**
 * Notification System API
 * Multi-channel Notification Management for Koperasi SaaS
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Get endpoint
$endpoint = $_GET['endpoint'] ?? $_POST['endpoint'] ?? null;

// Load database
try {
    require_once __DIR__ . '/../config/Config.php';
    $db = Config::getDatabase();
    $dbConnected = true;
} catch (Exception $e) {
    $dbConnected = false;
    $dbError = $e->getMessage();
}

// Route to appropriate handler
switch ($endpoint) {
    case 'send_notification':
        if ($dbConnected) {
            try {
                $notificationData = $_GET['notification_data'] ?? $_POST['notification_data'] ?? null;
                
                if ($notificationData) {
                    $result = sendNotification($db, $notificationData);
                    echo json_encode([
                        'success' => true,
                        'data' => $result,
                        'message' => 'Notification sent successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Notification data is required'
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error sending notification: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'send_sms':
        if ($dbConnected) {
            try {
                $phoneNumber = $_GET['phone_number'] ?? $_POST['phone_number'] ?? null;
                $message = $_GET['message'] ?? $_POST['message'] ?? null;
                
                if ($phoneNumber && $message) {
                    $result = sendSMSNotification($phoneNumber, $message);
                    echo json_encode([
                        'success' => true,
                        'data' => $result,
                        'message' => 'SMS notification sent successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Phone number and message are required'
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error sending SMS: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'send_email':
        if ($dbConnected) {
            try {
                $email = $_GET['email'] ?? $_POST['email'] ?? null;
                $subject = $_GET['subject'] ?? $_POST['subject'] ?? null;
                $message = $_GET['message'] ?? $_POST['message'] ?? null;
                
                if ($email && $subject && $message) {
                    $result = sendEmailNotification($email, $subject, $message);
                    echo json_encode([
                        'success' => true,
                        'data' => $result,
                        'message' => 'Email notification sent successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Email, subject, and message are required'
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error sending email: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'send_whatsapp':
        if ($dbConnected) {
            try {
                $phoneNumber = $_GET['phone_number'] ?? $_POST['phone_number'] ?? null;
                $message = $_GET['message'] ?? $_POST['message'] ?? null;
                
                if ($phoneNumber && $message) {
                    $result = sendWhatsAppNotification($phoneNumber, $message);
                    echo json_encode([
                        'success' => true,
                        'data' => $result,
                        'message' => 'WhatsApp notification sent successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Phone number and message are required'
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error sending WhatsApp: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'send_push_notification':
        if ($dbConnected) {
            try {
                $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
                $title = $_GET['title'] ?? $_POST['title'] ?? null;
                $message = $_GET['message'] ?? $_POST['message'] ?? null;
                
                if ($userId && $title && $message) {
                    $result = sendPushNotification($userId, $title, $message);
                    echo json_encode([
                        'success' => true,
                        'data' => $result,
                        'message' => 'Push notification sent successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'User ID, title, and message are required'
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error sending push notification: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'get_notifications':
        if ($dbConnected) {
            try {
                $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
                $limit = $_GET['limit'] ?? $_POST['limit'] ?? 50;
                
                $notifications = getUserNotifications($db, $userId, $limit);
                echo json_encode([
                    'success' => true,
                    'data' => $notifications,
                    'message' => 'Notifications retrieved successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving notifications: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'mark_notification_read':
        if ($dbConnected) {
            try {
                $notificationId = $_GET['notification_id'] ?? $_POST['notification_id'] ?? null;
                
                if ($notificationId) {
                    $result = markNotificationAsRead($db, $notificationId);
                    echo json_encode([
                        'success' => true,
                        'data' => $result,
                        'message' => 'Notification marked as read'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Notification ID is required'
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error marking notification as read: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'notification_templates':
        try {
            $templates = getNotificationTemplates();
            echo json_encode([
                'success' => true,
                'data' => $templates,
                'message' => 'Notification templates retrieved successfully'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error retrieving notification templates: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'notification_settings':
        if ($dbConnected) {
            try {
                $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
                
                if ($userId) {
                    $settings = getNotificationSettings($db, $userId);
                    echo json_encode([
                        'success' => true,
                        'data' => $settings,
                        'message' => 'Notification settings retrieved successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'User ID is required'
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving notification settings: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'update_notification_settings':
        if ($dbConnected) {
            try {
                $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
                $settings = $_GET['settings'] ?? $_POST['settings'] ?? null;
                
                if ($userId && $settings) {
                    $result = updateNotificationSettings($db, $userId, $settings);
                    echo json_encode([
                        'success' => true,
                        'data' => $result,
                        'message' => 'Notification settings updated successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'User ID and settings are required'
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error updating notification settings: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'notification_analytics':
        if ($dbConnected) {
            try {
                $analytics = getNotificationAnalytics($db);
                echo json_encode([
                    'success' => true,
                    'data' => $analytics,
                    'message' => 'Notification analytics retrieved successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving notification analytics: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Notification endpoint not found',
            'available_endpoints' => [
                'send_notification',
                'send_sms',
                'send_email',
                'send_whatsapp',
                'send_push_notification',
                'get_notifications',
                'mark_notification_read',
                'notification_templates',
                'notification_settings',
                'update_notification_settings',
                'notification_analytics'
            ]
        ]);
        break;
}

// Notification System Functions
function sendNotification($db, $notificationData) {
    $notificationId = generateNotificationId();
    $timestamp = date('Y-m-d H:i:s');
    
    // Parse notification data
    $userId = $notificationData['user_id'] ?? null;
    $type = $notificationData['type'] ?? 'general';
    $title = $notificationData['title'] ?? 'Notifikasi';
    $message = $notificationData['message'] ?? '';
    $channels = $notificationData['channels'] ?? ['in_app'];
    $priority = $notificationData['priority'] ?? 'normal';
    
    // Store notification in database
    $storedNotification = storeNotification($db, [
        'id' => $notificationId,
        'user_id' => $userId,
        'type' => $type,
        'title' => $title,
        'message' => $message,
        'channels' => json_encode($channels),
        'priority' => $priority,
        'created_at' => $timestamp,
        'status' => 'sent'
    ]);
    
    // Send notifications through different channels
    $results = [];
    foreach ($channels as $channel) {
        switch ($channel) {
            case 'sms':
                if (isset($notificationData['phone_number'])) {
                    $results['sms'] = sendSMSNotification($notificationData['phone_number'], $message);
                }
                break;
            case 'email':
                if (isset($notificationData['email'])) {
                    $results['email'] = sendEmailNotification($notificationData['email'], $title, $message);
                }
                break;
            case 'whatsapp':
                if (isset($notificationData['phone_number'])) {
                    $results['whatsapp'] = sendWhatsAppNotification($notificationData['phone_number'], $message);
                }
                break;
            case 'push':
                $results['push'] = sendPushNotification($userId, $title, $message);
                break;
            case 'in_app':
                $results['in_app'] = ['status' => 'delivered', 'message' => 'In-app notification stored'];
                break;
        }
    }
    
    return [
        'notification_id' => $notificationId,
        'timestamp' => $timestamp,
        'channels_used' => $channels,
        'results' => $results,
        'delivery_status' => calculateDeliveryStatus($results)
    ];
}

function sendSMSNotification($phoneNumber, $message) {
    // Mock implementation - integrate with SMS gateway
    $smsId = generateSMSId();
    
    // Validate phone number
    if (!isValidPhoneNumber($phoneNumber)) {
        return [
            'status' => 'failed',
            'error' => 'Invalid phone number',
            'sms_id' => $smsId
        ];
    }
    
    // Send SMS (mock)
    $sent = true; // Mock success
    
    return [
        'sms_id' => $smsId,
        'phone_number' => $phoneNumber,
        'status' => $sent ? 'sent' : 'failed',
        'sent_at' => date('Y-m-d H:i:s'),
        'delivery_report' => $sent ? 'delivered' : 'failed',
        'cost' => 500 // in Rupiah
    ];
}

function sendEmailNotification($email, $subject, $message) {
    // Mock implementation - integrate with email service
    $emailId = generateEmailId();
    
    // Validate email
    if (!isValidEmail($email)) {
        return [
            'status' => 'failed',
            'error' => 'Invalid email address',
            'email_id' => $emailId
        ];
    }
    
    // Send email (mock)
    $sent = true; // Mock success
    
    return [
        'email_id' => $emailId,
        'to' => $email,
        'subject' => $subject,
        'status' => $sent ? 'sent' : 'failed',
        'sent_at' => date('Y-m-d H:i:s'),
        'delivery_report' => $sent ? 'delivered' : 'failed'
    ];
}

function sendWhatsAppNotification($phoneNumber, $message) {
    // Mock implementation - integrate with WhatsApp API
    $whatsappId = generateWhatsAppId();
    
    // Validate phone number
    if (!isValidPhoneNumber($phoneNumber)) {
        return [
            'status' => 'failed',
            'error' => 'Invalid phone number',
            'whatsapp_id' => $whatsappId
        ];
    }
    
    // Send WhatsApp message (mock)
    $sent = true; // Mock success
    
    return [
        'whatsapp_id' => $whatsappId,
        'phone_number' => $phoneNumber,
        'status' => $sent ? 'sent' : 'failed',
        'sent_at' => date('Y-m-d H:i:s'),
        'delivery_report' => $sent ? 'delivered' : 'failed',
        'message_type' => 'text'
    ];
}

function sendPushNotification($userId, $title, $message) {
    // Mock implementation - integrate with push notification service
    $pushId = generatePushId();
    
    // Get user device tokens
    $deviceTokens = getUserDeviceTokens($userId);
    
    if (empty($deviceTokens)) {
        return [
            'status' => 'failed',
            'error' => 'No device tokens found',
            'push_id' => $pushId
        ];
    }
    
    // Send push notification (mock)
    $sent = true; // Mock success
    
    return [
        'push_id' => $pushId,
        'user_id' => $userId,
        'device_count' => count($deviceTokens),
        'status' => $sent ? 'sent' : 'failed',
        'sent_at' => date('Y-m-d H:i:s'),
        'delivery_report' => $sent ? 'delivered' : 'failed'
    ];
}

function getUserNotifications($db, $userId, $limit = 50) {
    // Mock implementation
    return [
        [
            'id' => 'NOTIF_001',
            'type' => 'loan_approval',
            'title' => 'Pinjaman Disetujui',
            'message' => 'Pengajuan pinjaman Anda telah disetujui',
            'status' => 'unread',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            'priority' => 'high'
        ],
        [
            'id' => 'NOTIF_002',
            'type' => 'payment_reminder',
            'title' => 'Pengingat Pembayaran',
            'message' => 'Jangan lupa melakukan pembayaran cicilan bulan ini',
            'status' => 'read',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'priority' => 'normal'
        ],
        [
            'id' => 'NOTIF_003',
            'type' => 'system_update',
            'title' => 'Update Sistem',
            'message' => 'Sistem akan melakukan maintenance pada hari Sabtu',
            'status' => 'unread',
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
            'priority' => 'low'
        ]
    ];
}

function markNotificationAsRead($db, $notificationId) {
    // Mock implementation
    return [
        'notification_id' => $notificationId,
        'status' => 'read',
        'read_at' => date('Y-m-d H:i:s')
    ];
}

function getNotificationTemplates() {
    return [
        'loan_approval' => [
            'title' => 'Pinjaman Disetujui',
            'message' => 'Hai {member_name}, pengajuan pinjaman Anda sebesar {loan_amount} telah disetujui.',
            'channels' => ['sms', 'email', 'in_app'],
            'variables' => ['member_name', 'loan_amount']
        ],
        'loan_rejection' => [
            'title' => 'Pinjaman Ditolak',
            'message' => 'Hai {member_name}, pengajuan pinjaman Anda ditolak karena {reason}.',
            'channels' => ['sms', 'email', 'in_app'],
            'variables' => ['member_name', 'reason']
        ],
        'payment_reminder' => [
            'title' => 'Pengingat Pembayaran',
            'message' => 'Hai {member_name}, jangan lupa melakukan pembayaran cicilan sebesar {amount} sebelum {due_date}.',
            'channels' => ['sms', 'whatsapp', 'in_app'],
            'variables' => ['member_name', 'amount', 'due_date']
        ],
        'overdue_notice' => [
            'title' => 'Notifikasi Tunggakan',
            'message' => 'Hai {member_name}, Anda memiliki tunggakan sebesar {overdue_amount}. Segera lakukan pembayaran.',
            'channels' => ['sms', 'whatsapp', 'email'],
            'variables' => ['member_name', 'overdue_amount']
        ],
        'welcome_message' => [
            'title' => 'Selamat Datang',
            'message' => 'Selamat datang di Koperasi {koperasi_name}, {member_name}! Akun Anda telah aktif.',
            'channels' => ['sms', 'email', 'in_app'],
            'variables' => ['member_name', 'koperasi_name']
        ],
        'system_maintenance' => [
            'title' => 'Maintenance Sistem',
            'message' => 'Sistem akan melakukan maintenance pada {maintenance_date} dari {start_time} hingga {end_time}.',
            'channels' => ['email', 'in_app'],
            'variables' => ['maintenance_date', 'start_time', 'end_time']
        ]
    ];
}

function getNotificationSettings($db, $userId) {
    // Mock implementation
    return [
        'user_id' => $userId,
        'preferences' => [
            'sms_enabled' => true,
            'email_enabled' => true,
            'whatsapp_enabled' => false,
            'push_enabled' => true,
            'in_app_enabled' => true
        ],
        'categories' => [
            'loan_notifications' => true,
            'payment_reminders' => true,
            'system_updates' => false,
            'promotional' => false
        ],
        'quiet_hours' => [
            'enabled' => true,
            'start_time' => '22:00',
            'end_time' => '07:00'
        ],
        'frequency' => [
            'daily_digest' => false,
            'weekly_summary' => true,
            'instant_notifications' => true
        ]
    ];
}

function updateNotificationSettings($db, $userId, $settings) {
    // Mock implementation
    return [
        'user_id' => $userId,
        'settings_updated' => true,
        'updated_at' => date('Y-m-d H:i:s')
    ];
}

function getNotificationAnalytics($db) {
    // Mock implementation
    return [
        'total_notifications' => 1250,
        'notifications_today' => 45,
        'delivery_rate' => 96.5,
        'open_rate' => 78.2,
        'click_rate' => 12.4,
        'channel_performance' => [
            'sms' => [
                'sent' => 450,
                'delivered' => 435,
                'delivery_rate' => 96.7
            ],
            'email' => [
                'sent' => 380,
                'delivered' => 365,
                'delivery_rate' => 96.1
            ],
            'whatsapp' => [
                'sent' => 220,
                'delivered' => 210,
                'delivery_rate' => 95.5
            ],
            'push' => [
                'sent' => 200,
                'delivered' => 195,
                'delivery_rate' => 97.5
            ]
        ],
        'category_performance' => [
            'loan_notifications' => [
                'sent' => 350,
                'open_rate' => 85.2,
                'click_rate' => 15.8
            ],
            'payment_reminders' => [
                'sent' => 420,
                'open_rate' => 92.1,
                'click_rate' => 8.3
            ],
            'system_updates' => [
                'sent' => 180,
                'open_rate' => 65.4,
                'click_rate' => 5.2
            ]
        ],
        'trends' => [
            'daily' => [
                'mon' => 42, 'tue' => 38, 'wed' => 45, 'thu' => 48,
                'fri' => 52, 'sat' => 35, 'sun' => 30
            ],
            'weekly' => [
                'week1' => 280, 'week2' => 295, 'week3' => 310, 'week4' => 325
            ]
        ]
    ];
}

// Helper functions
function generateNotificationId() {
    return 'NOTIF_' . date('YmdHis') . '_' . rand(1000, 9999);
}

function generateSMSId() {
    return 'SMS_' . date('YmdHis') . '_' . rand(1000, 9999);
}

function generateEmailId() {
    return 'EMAIL_' . date('YmdHis') . '_' . rand(1000, 9999);
}

function generateWhatsAppId() {
    return 'WA_' . date('YmdHis') . '_' . rand(1000, 9999);
}

function generatePushId() {
    return 'PUSH_' . date('YmdHis') . '_' . rand(1000, 9999);
}

function storeNotification($db, $notificationData) {
    // Mock implementation
    return $notificationData;
}

function calculateDeliveryStatus($results) {
    $total = count($results);
    $successful = count(array_filter($results, function($r) { 
        return isset($r['status']) && $r['status'] === 'sent'; 
    }));
    
    return [
        'total_channels' => $total,
        'successful_deliveries' => $successful,
        'delivery_rate' => $total > 0 ? ($successful / $total) * 100 : 0,
        'overall_status' => $successful === $total ? 'success' : ($successful > 0 ? 'partial' : 'failed')
    ];
}

function isValidPhoneNumber($phoneNumber) {
    // Simple validation
    return preg_match('/^[0-9]{10,15}$/', $phoneNumber);
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function getUserDeviceTokens($userId) {
    // Mock implementation
    return [
        'token1_' . $userId,
        'token2_' . $userId
    ];
}

?>
