<?php
/**
 * AUDIT LOGGING SYSTEM
 * Track all user activities and data changes
 */

class AuditLogger {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function log($userId, $userName, $action, $resourceType, $resourceId = null, $oldData = null, $newData = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO audit_logs (uuid, user_id, user_name, action, resource_type, resource_id, old_data, new_data, ip_address, user_agent, created_at)
                VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([
                $userId,
                $userName,
                $action,
                $resourceType,
                $resourceId,
                $oldData ? json_encode($oldData) : null,
                $newData ? json_encode($newData) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            // Log error but don't break the application
            error_log("Audit log failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function getActivityLogs($userId = null, $resourceType = null, $limit = 100) {
        $sql = "
            SELECT * FROM audit_logs 
            WHERE 1=1
        ";
        $params = [];
        
        if ($userId) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        if ($resourceType) {
            $sql .= " AND resource_type = ?";
            $params[] = $resourceType;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function getRecentActivity($limit = 50) {
        $stmt = $this->db->prepare("
            SELECT al.*, 
                   CASE 
                       WHEN al.action LIKE '%CREATE%' THEN 'success'
                       WHEN al.action LIKE '%UPDATE%' THEN 'info'
                       WHEN al.action LIKE '%DELETE%' THEN 'danger'
                       ELSE 'secondary'
                   END as bootstrap_class
            FROM audit_logs al
            ORDER BY al.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    }
}

// Usage in CRUD operations
function logActivity($action, $resourceType, $oldData = null, $newData = null) {
    global $db;
    
    // Get user info from session or token
    $userId = $_SESSION['user_id'] ?? 1;
    $userName = $_SESSION['user_name'] ?? 'System';
    
    $auditLogger = new AuditLogger($db);
    $auditLogger->log($userId, $userName, $action, $resourceType, null, $oldData, $newData);
}

?>
