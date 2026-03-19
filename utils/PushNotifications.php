<?php
/**
 * Push Notification System for Web Application
 * Handles alerts and reminders using various notification channels
 */

class PushNotifications {
    private $db;
    private $pushService;
    private $emailService;
    private $smsService;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->pushService = new PushService();
        $this->emailService = new EmailService();
        $this->smsService = new SMSService();
    }
    
    /**
     * Send notification to user
     */
    public function sendNotification($userId, $title, $message, $type = 'info', $channels = ['push'], $data = []) {
        $notification = [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'channels' => json_encode($channels),
            'data' => json_encode($data),
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 'pending'
        ];
        
        // Save notification to database
        $notificationId = $this->db->insert('notifications', $notification);
        
        $results = [];
        
        // Send through each channel
        foreach ($channels as $channel) {
            $result = $this->sendThroughChannel($channel, $userId, $title, $message, $type, $data);
            $results[$channel] = $result;
        }
        
        // Update notification status
        $success = !empty(array_filter($results, fn($r) => $r['success']));
        $this->db->update('notifications', [
            'status' => $success ? 'sent' : 'failed',
            'sent_at' => date('Y-m-d H:i:s'),
            'results' => json_encode($results)
        ], 'id = ?', [$notificationId]);
        
        return [
            'success' => $success,
            'notification_id' => $notificationId,
            'results' => $results,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Send notification through specific channel
     */
    private function sendThroughChannel($channel, $userId, $title, $message, $type, $data) {
        switch ($channel) {
            case 'push':
                return $this->sendPushNotification($userId, $title, $message, $type, $data);
            case 'email':
                return $this->sendEmailNotification($userId, $title, $message, $type, $data);
            case 'sms':
                return $this->sendSMSNotification($userId, $title, $message, $type, $data);
            case 'webhook':
                return $this->sendWebhookNotification($userId, $title, $message, $type, $data);
            default:
                return ['success' => false, 'message' => 'Unknown channel'];
        }
    }
    
    /**
     * Send push notification (web push)
     */
    private function sendPushNotification($userId, $title, $message, $type, $data) {
        try {
            // Get user's push subscription
            $subscription = $this->getUserPushSubscription($userId);
            
            if (!$subscription) {
                return ['success' => false, 'message' => 'No push subscription found'];
            }
            
            $payload = [
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'data' => $data,
                'icon' => '/assets/icons/icon-192x192.png',
                'badge' => '/assets/icons/badge-72x72.png',
                'timestamp' => time()
            ];
            
            $result = $this->pushService->send($subscription, $payload);
            
            return [
                'success' => $result,
                'message' => $result ? 'Push notification sent' : 'Failed to send push notification'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Send email notification
     */
    private function sendEmailNotification($userId, $title, $message, $type, $data) {
        try {
            $user = $this->getUser($userId);
            
            if (!$user || !$user['email']) {
                return ['success' => false, 'message' => 'User email not found'];
            }
            
            $subject = $title;
            $body = $this->generateEmailTemplate($title, $message, $type, $data);
            
            $result = $this->emailService->send($user['email'], $subject, $body);
            
            return [
                'success' => $result,
                'message' => $result ? 'Email sent' : 'Failed to send email'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Send SMS notification
     */
    private function sendSMSNotification($userId, $title, $message, $type, $data) {
        try {
            $user = $this->getUser($userId);
            
            if (!$user || !$user['phone']) {
                return ['success' => false, 'message' => 'User phone not found'];
            }
            
            $smsMessage = $this->generateSMSMessage($title, $message, $type, $data);
            
            $result = $this->smsService->send($user['phone'], $smsMessage);
            
            return [
                'success' => $result,
                'message' => $result ? 'SMS sent' : 'Failed to send SMS'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Send webhook notification
     */
    private function sendWebhookNotification($userId, $title, $message, $type, $data) {
        try {
            $webhookUrl = $this->getUserWebhook($userId);
            
            if (!$webhookUrl) {
                return ['success' => false, 'message' => 'No webhook URL found'];
            }
            
            $payload = [
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'data' => $data,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            $ch = curl_init($webhookUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $success = $httpCode >= 200 && $httpCode < 300;
            
            return [
                'success' => $success,
                'message' => $success ? 'Webhook sent' : 'Webhook failed',
                'http_code' => $httpCode
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Send bulk notification
     */
    public function sendBulkNotification($userIds, $title, $message, $type = 'info', $channels = ['push'], $data = []) {
        $results = [];
        
        foreach ($userIds as $userId) {
            $result = $this->sendNotification($userId, $title, $message, $type, $channels, $data);
            $results[$userId] = $result;
        }
        
        return [
            'success' => true,
            'total_users' => count($userIds),
            'results' => $results,
            'success_rate' => count(array_filter($results, fn($r) => $r['success'])) / count($userIds) * 100
        ];
    }
    
    /**
     * Send scheduled notification
     */
    public function scheduleNotification($userId, $title, $message, $scheduledAt, $type = 'info', $channels = ['push'], $data = []) {
        $schedule = [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'channels' => json_encode($channels),
            'data' => json_encode($data),
            'scheduled_at' => $scheduledAt,
            'status' => 'scheduled',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $scheduleId = $this->db->insert('scheduled_notifications', $schedule);
        
        return [
            'success' => true,
            'schedule_id' => $scheduleId,
            'scheduled_at' => $scheduledAt
        ];
    }
    
    /**
     * Process scheduled notifications
     */
    public function processScheduledNotifications() {
        $scheduled = $this->db->fetchAll(
            "SELECT * FROM scheduled_notifications 
             WHERE scheduled_at <= NOW() AND status = 'scheduled'
             ORDER BY scheduled_at ASC"
        );
        
        $processed = 0;
        $failed = 0;
        
        foreach ($scheduled as $notification) {
            try {
                $channels = json_decode($notification['channels'], true);
                $data = json_decode($notification['data'], true);
                
                $result = $this->sendNotification(
                    $notification['user_id'],
                    $notification['title'],
                    $notification['message'],
                    $notification['type'],
                    $channels,
                    $data
                );
                
                if ($result['success']) {
                    $this->db->update('scheduled_notifications', [
                        'status' => 'sent',
                        'sent_at' => date('Y-m-d H:i:s')
                    ], 'id = ?', [$notification['id']]);
                    $processed++;
                } else {
                    $this->db->update('scheduled_notifications', [
                        'status' => 'failed',
                        'sent_at' => date('Y-m-d H:i:s')
                    ], 'id = ?', [$notification['id']]);
                    $failed++;
                }
            } catch (Exception $e) {
                $this->db->update('scheduled_notifications', [
                    'status' => 'failed',
                    'sent_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [$notification['id']]);
                $failed++;
            }
        }
        
        return [
            'success' => true,
            'processed' => $processed,
            'failed' => $failed,
            'total' => $processed + $failed
        ];
    }
    
    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 50, $offset = 0) {
        $notifications = $this->db->fetchAll(
            "SELECT * FROM notifications 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
        
        return [
            'success' => true,
            'data' => $notifications,
            'total' => $this->getNotificationCount($userId)
        ];
    }
    
    /**
     * Get notification count
     */
    private function getNotificationCount($userId) {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM notifications WHERE user_id = ?",
            [$userId]
        );
        return $result['count'];
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId) {
        $affected = $this->db->update('notifications', [
            'read_at' => date('Y-m-d H:i:s'),
            'status' => 'read'
        ], 'id = ? AND user_id = ?', [$notificationId, $userId]);
        
        return [
            'success' => $affected > 0,
            'message' => $affected > 0 ? 'Marked as read' : 'Notification not found'
        ];
    }
    
    /**
     * Get user push subscription
     */
    private function getUserPushSubscription($userId) {
        return $this->db->fetchOne(
            "SELECT * FROM push_subscriptions WHERE user_id = ? AND status = 'active'",
            [$userId]
        );
    }
    
    /**
     * Get user data
     */
    private function getUser($userId) {
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE id = ?",
            [$userId]
        );
    }
    
    /**
     * Get user webhook
     */
    private function getUserWebhook($userId) {
        return $this->db->fetchOne(
            "SELECT webhook_url FROM user_webhooks WHERE user_id = ? AND status = 'active'",
            [$userId]
        );
    }
    
    /**
     * Generate email template
     */
    private function generateEmailTemplate($title, $message, $type, $data) {
        $template = "
        <html>
        <head>
            <title>{$title}</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .header { background: #ff6b35; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { padding: 20px; text-align: center; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>KSP Lam Gabe Jaya</h1>
                <h2>{$title}</h2>
            </div>
            <div class='content'>
                <p>{$message}</p>";
        
        if (!empty($data)) {
            $template .= "<h3>Detail Informasi:</h3><ul>";
            foreach ($data as $key => $value) {
                $template .= "<li><strong>{$key}:</strong> {$value}</li>";
            }
            $template .= "</ul>";
        }
        
        $template .= "
            </div>
            <div class='footer'>
                <p>Email ini dikirim otomatis oleh sistem KSP Lam Gabe Jaya</p>
                <p>Jika Anda tidak merasa melakukan permintaan ini, harap menghubungi admin.</p>
            </div>
        </body>
        </html>";
        
        return $template;
    }
    
    /**
     * Generate SMS message
     */
    private function generateSMSMessage($title, $message, $type, $data) {
        $smsMessage = "[KSP Lam Gabe Jaya]\n{$title}\n\n{$message}";
        
        if (!empty($data)) {
            $smsMessage .= "\n\nDetail:";
            foreach ($data as $key => $value) {
                $smsMessage .= "\n{$key}: {$value}";
            }
        }
        
        return $smsMessage;
    }
}

/**
 * Push Service (Web Push)
 */
class PushService {
    public function send($subscription, $payload) {
        // Simplified web push implementation
        // In production, would use proper Web Push Protocol
        
        error_log("Push notification sent: " . json_encode($payload));
        return true;
    }
}

/**
 * Email Service
 */
class EmailService {
    public function send($to, $subject, $body) {
        // Simplified email implementation
        // In production, would use proper email service
        
        error_log("Email sent to {$to}: {$subject}");
        return true;
    }
}

/**
 * SMS Service
 */
class SMSService {
    public function send($to, $message) {
        // Simplified SMS implementation
        // In production, would use proper SMS gateway
        
        error_log("SMS sent to {$to}: {$message}");
        return true;
    }
}

?>
