<?php
/**
 * NOTIFICATION SYSTEM
 * Handle user notifications and alerts
 */

class NotificationSystem {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function createNotification($userId, $title, $message, $type = 'info') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (uuid, user_id, title, message, type)
                VALUES (UUID(), ?, ?, ?, ?)
            ");
            
            return $stmt->execute([$userId, $title, $message, $type]);
        } catch (Exception $e) {
            error_log("Notification creation failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUnreadNotifications($userId, $limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notifications 
                WHERE user_id = ? AND is_read = FALSE 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get notifications failed: " . $e->getMessage());
            return [];
        }
    }
    
    public function markAsRead($notificationId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET is_read = TRUE, read_at = NOW() 
                WHERE id = ?
            ");
            
            return $stmt->execute([$notificationId]);
        } catch (Exception $e) {
            error_log("Mark notification as read failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function markAllAsRead($userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET is_read = TRUE, read_at = NOW() 
                WHERE user_id = ? AND is_read = FALSE
            ");
            
            return $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Mark all notifications as read failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function getNotificationCount($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM notifications 
                WHERE user_id = ? AND is_read = FALSE
            ");
            $stmt->execute([$userId]);
            
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            error_log("Get notification count failed: " . $e->getMessage());
            return 0;
        }
    }
    
    public function createBulkNotification($userIds, $title, $message, $type = 'info') {
        $success = true;
        foreach ($userIds as $userId) {
            if (!$this->createNotification($userId, $title, $message, $type)) {
                $success = false;
            }
        }
        return $success;
    }
    
    // Predefined notification types
    public function notifyLoanApproval($userId, $loanAmount) {
        $title = "Pinjaman Disetujui";
        $message = "Selamat! Pinjaman sebesar Rp " . number_format($loanAmount, 0, ',', '.') . " telah disetujui.";
        return $this->createNotification($userId, $title, $message, 'success');
    }
    
    public function notifyLoanRejection($userId, $reason) {
        $title = "Pinjaman Ditolak";
        $message = "Mohon maaf, pengajuan pinjaman Anda ditolak. Alasan: " . $reason;
        return $this->createNotification($userId, $title, $message, 'warning');
    }
    
    public function notifyNewMessage($userId, $senderName) {
        $title = "Pesan Baru";
        $message = "Anda memiliki pesan baru dari " . $senderName;
        return $this->createNotification($userId, $title, $message, 'info');
    }
    
    public function notifySystemUpdate($title, $message) {
        // Send to all users
        $stmt = $this->db->prepare("SELECT id FROM users WHERE is_active = 1");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        $userIds = array_column($users, 'id');
        return $this->createBulkNotification($userIds, $title, $message, 'info');
    }
}

// Helper functions for easy notification creation
function notifyUser($userId, $title, $message, $type = 'info') {
    global $db;
    $notificationSystem = new NotificationSystem($db);
    return $notificationSystem->createNotification($userId, $title, $message, $type);
}

function notifyAllUsers($title, $message, $type = 'info') {
    global $db;
    $notificationSystem = new NotificationSystem($db);
    
    $stmt = $db->prepare("SELECT id FROM users WHERE is_active = 1");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    $userIds = array_column($users, 'id');
    return $notificationSystem->createBulkNotification($userIds, $title, $message, $type);
}

?>
