<?php
/**
 * Audit Logging System
 */
class AuditLogger {
    public static function log($user_id, $action, $module, $details = '') {
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user_id, action, module, details, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $user_id,
            $action,
            $module,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
    
    public static function getLogs($limit = 100) {
        $stmt = $pdo->prepare("
            SELECT al.*, u.name as user_name 
            FROM audit_logs al 
            LEFT JOIN users u ON al.user_id = u.id 
            ORDER BY al.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>