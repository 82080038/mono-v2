<?php
/**
 * Complete Security Audit System
 * Comprehensive security monitoring and auditing
 */

require_once __DIR__ . '/../config/Config.php';

class SecurityAudit {
    private $db;
    private $config;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->config = $this->loadSecurityConfig();
    }
    
    private function loadSecurityConfig() {
        return [
            'audit_retention_days' => 365,
            'failed_login_threshold' => 5,
            'session_timeout' => 1800, // 30 minutes
            'password_complexity' => [
                'min_length' => 8,
                'require_uppercase' => true,
                'require_lowercase' => true,
                'require_numbers' => true,
                'require_symbols' => true
            ],
            'security_alerts' => [
                'login_failed_multiple' => true,
                'unauthorized_access' => true,
                'privilege_escalation' => true,
                'data_breach_attempt' => true,
                'suspicious_activity' => true
            ]
        ];
    }
    
    /**
     * Log security event
     */
    public function logSecurityEvent($event_type, $description, $user_id = null, $ip_address = null, $additional_data = []) {
        $stmt = $this->db->prepare("
            INSERT INTO security_audit_log (event_type, description, user_id, ip_address, additional_data, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $additional_json = json_encode($additional_data);
        $stmt->execute([$event_type, $description, $user_id, $ip_address, $additional_json]);
        
        // Check for critical events
        if ($this->isCriticalEvent($event_type)) {
            $this->handleCriticalEvent($event_type, $description, $user_id, $ip_address);
        }
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Check if event is critical
     */
    private function isCriticalEvent($event_type) {
        $critical_events = [
            'LOGIN_FAILED_MULTIPLE',
            'UNAUTHORIZED_ACCESS',
            'PRIVILEGE_ESCALATION',
            'DATA_BREACH_ATTEMPT',
            'MALICIOUS_REQUEST',
            'SUSPICIOUS_ACTIVITY',
            'BRUTE_FORCE_ATTACK',
            'SQL_INJECTION_ATTEMPT',
            'XSS_ATTEMPT'
        ];
        
        return in_array($event_type, $critical_events);
    }
    
    /**
     * Handle critical security event
     */
    private function handleCriticalEvent($event_type, $description, $user_id, $ip_address) {
        // Create security alert
        $this->createSecurityAlert($event_type, $description, $user_id, $ip_address);
        
        // Block IP if necessary
        if ($this->shouldBlockIP($event_type, $ip_address)) {
            $this->blockIP($ip_address);
        }
        
        // Send notification to administrators
        $this->notifyAdministrators($event_type, $description, $user_id, $ip_address);
        
        // Log to system log
        error_log("CRITICAL SECURITY EVENT: {$event_type} - {$description} - User: {$user_id} - IP: {$ip_address}");
    }
    
    /**
     * Create security alert
     */
    private function createSecurityAlert($event_type, $description, $user_id, $ip_address) {
        $stmt = $this->db->prepare("
            INSERT INTO security_alerts (event_type, description, user_id, ip_address, status, created_at)
            VALUES (?, ?, ?, ?, 'ACTIVE', NOW())
        ");
        $stmt->execute([$event_type, $description, $user_id, $ip_address]);
    }
    
    /**
     * Check if IP should be blocked
     */
    private function shouldBlockIP($event_type, $ip_address) {
        // Check recent failed attempts from this IP
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as failed_attempts 
            FROM security_audit_log 
            WHERE ip_address = ? AND event_type = 'LOGIN_FAILED' 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$ip_address]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['failed_attempts'] >= $this->config['failed_login_threshold'];
    }
    
    /**
     * Block IP address
     */
    public function blockIP($ip_address, $reason = 'Security violation') {
        $stmt = $this->db->prepare("
            INSERT INTO blocked_ips (ip_address, reason, blocked_at, expires_at)
            VALUES (?, 'Multiple failed login attempts', NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR))
        ");
        $stmt->execute([$ip_address]);
        
        $this->logSecurityEvent('IP_BLOCKED', 'IP blocked due to security violation', null, $ip_address);
        
        return true;
    }
    
    /**
     * Check if IP is blocked
     */
    public function isIPBlocked($ip_address) {
        $stmt = $this->db->prepare("
            SELECT 1 FROM blocked_ips 
            WHERE ip_address = ? AND expires_at > NOW()
        ");
        $stmt->execute([$ip_address]);
        
        return $stmt->fetch() !== false;
    }
    
    /**
     * Audit user permissions
     */
    public function auditUserPermissions($user_id) {
        $stmt = $this->db->prepare("
            SELECT u.role, u.permissions, r.permissions as role_permissions
            FROM users u
            LEFT JOIN roles r ON u.role = r.name
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user_data) {
            $user_permissions = json_decode($user_data['permissions'] ?? '[]', true);
            $role_permissions = json_decode($user_data['role_permissions'] ?? '[]', true);
            
            $all_permissions = array_unique(array_merge($user_permissions, $role_permissions));
            
            // Log permission audit
            $this->logSecurityEvent(
                'PERMISSION_AUDIT',
                'User permissions audited: ' . json_encode($all_permissions),
                $user_id,
                $_SERVER['REMOTE_ADDR'] ?? null,
                ['permissions' => $all_permissions]
            );
            
            return $all_permissions;
        }
        
        return [];
    }
    
    /**
     * Audit session security
     */
    public function auditSessionSecurity($session_id, $user_id) {
        // Check session age
        $stmt = $this->db->prepare("
            SELECT created_at, last_activity, ip_address, user_agent
            FROM user_sessions
            WHERE session_id = ? AND user_id = ?
        ");
        $stmt->execute([$session_id, $user_id]);
        $session_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($session_data) {
            $current_time = time();
            $session_age = $current_time - strtotime($session_data['created_at']);
            $inactive_time = $current_time - strtotime($session_data['last_activity']);
            
            $issues = [];
            
            if ($session_age > $this->config['session_timeout']) {
                $issues[] = 'Session expired';
            }
            
            if ($inactive_time > $this->config['session_timeout']) {
                $issues[] = 'Session inactive too long';
            }
            
            if ($session_data['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
                $issues[] = 'IP address changed';
            }
            
            if (count($issues) > 0) {
                $this->logSecurityEvent(
                    'SESSION_SECURITY_ISSUE',
                    'Session security issues: ' . implode(', ', $issues),
                    $user_id,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $issues
                );
                
                // Invalidate session
                $this->invalidateSession($session_id);
            }
        }
    }
    
    /**
     * Audit API security
     */
    public function auditAPISecurity($endpoint, $method, $user_id, $request_data) {
        // Check for common attack patterns
        $suspicious_patterns = [
            '/(<script[^>]*>.*?<\/script>)/i',
            '/(union\s+select)/i',
            '/(drop\s+table)/i',
            '/(exec\s*\()/i',
            '/(eval\s*\()/i',
            '/(javascript:)/i',
            '/(onload|onerror)/i'
        ];
        
        $request_string = json_encode($request_data);
        $issues = [];
        
        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $request_string)) {
                $issues[] = 'Suspicious pattern detected: ' . $pattern;
            }
        }
        
        // Check for unusual request size
        if (strlen($request_string) > 10000) {
            $issues[] = 'Large request size';
        }
        
        // Check for unusual endpoint access
        if ($this->isRestrictedEndpoint($endpoint, $method, $user_id)) {
            $issues[] = 'Unauthorized endpoint access';
        }
        
        if (count($issues) > 0) {
            $this->logSecurityEvent(
                'API_SECURITY_ISSUE',
                'API security issues: ' . implode(', ', $issues),
                $user_id,
                $_SERVER['REMOTE_ADDR'] ?? null,
                [
                    'endpoint' => $endpoint,
                    'method' => $method,
                    'issues' => $issues
                ]
            );
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if endpoint is restricted
     */
    private function isRestrictedEndpoint($endpoint, $method, $user_id) {
        $restricted_endpoints = [
            '/core/api/admin/' => ['admin', 'super_admin'],
            '/core/api/system/' => ['super_admin'],
            '/core/api/security/' => ['super_admin']
        ];
        
        foreach ($restricted_endpoints as $pattern => $allowed_roles) {
            if (strpos($endpoint, $pattern) === 0) {
                $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user_role = $stmt->fetchColumn();
                
                return !in_array($user_role, $allowed_roles);
            }
        }
        
        return false;
    }
    
    /**
     * Generate security report
     */
    public function generateSecurityReport($date_range = 30) {
        $report = [];
        
        // Security events summary
        $stmt = $this->db->prepare("
            SELECT event_type, COUNT(*) as count, COUNT(DISTINCT user_id) as affected_users
            FROM security_audit_log
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY event_type
            ORDER BY count DESC
        ");
        $stmt->execute([$date_range]);
        $report['events_summary'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Active security alerts
        $stmt = $this->db->prepare("
            SELECT * FROM security_alerts
            WHERE status = 'ACTIVE'
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute();
        $report['active_alerts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Blocked IPs
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as blocked_count
            FROM blocked_ips
            WHERE expires_at > NOW()
        ");
        $stmt->execute();
        $report['blocked_ips'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Security score
        $report['security_score'] = $this->calculateSecurityScore($date_range);
        
        return $report;
    }
    
    /**
     * Calculate security score
     */
    private function calculateSecurityScore($date_range) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_events,
                SUM(CASE WHEN event_type LIKE '%FAILED%' THEN 1 ELSE 0 END) as failed_events,
                SUM(CASE WHEN event_type LIKE '%UNAUTHORIZED%' THEN 1 ELSE 0 END) as unauthorized_events
            FROM security_audit_log
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$date_range]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($stats['total_events'] == 0) {
            return 100;
        }
        
        $failure_rate = ($stats['failed_events'] + $stats['unauthorized_events']) / $stats['total_events'];
        $security_score = max(0, 100 - ($failure_rate * 100));
        
        return round($security_score);
    }
    
    /**
     * Get user security profile
     */
    public function getUserSecurityProfile($user_id) {
        $profile = [];
        
        // Recent security events
        $stmt = $this->db->prepare("
            SELECT event_type, description, created_at
            FROM security_audit_log
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$user_id]);
        $profile['recent_events'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Failed login attempts
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as failed_attempts
            FROM security_audit_log
            WHERE user_id = ? AND event_type = 'LOGIN_FAILED'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->execute([$user_id]);
        $profile['failed_attempts_24h'] = $stmt->fetch(PDO::FETCH_ASSOC)['failed_attempts'];
        
        // Security score
        $profile['security_score'] = $this->calculateUserSecurityScore($user_id);
        
        return $profile;
    }
    
    /**
     * Calculate user security score
     */
    private function calculateUserSecurityScore($user_id) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_events,
                SUM(CASE WHEN event_type LIKE '%FAILED%' THEN 1 ELSE 0 END) as failed_events
            FROM security_audit_log
            WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute([$user_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($stats['total_events'] == 0) {
            return 100;
        }
        
        $failure_rate = $stats['failed_events'] / $stats['total_events'];
        $security_score = max(0, 100 - ($failure_rate * 100));
        
        return round($security_score);
    }
    
    /**
     * Invalidate session
     */
    private function invalidateSession($session_id) {
        $stmt = $this->db->prepare("
            UPDATE user_sessions SET status = 'INVALIDATED' WHERE session_id = ?
        ");
        $stmt->execute([$session_id]);
    }
    
    /**
     * Notify administrators
     */
    private function notifyAdministrators($event_type, $description, $user_id, $ip_address) {
        // Get admin users
        $stmt = $this->db->prepare("
            SELECT email, name FROM users WHERE role IN ('admin', 'super_admin')
        ");
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($admins as $admin) {
            // Send email notification
            $subject = "Security Alert: " . $event_type;
            $message = "
                Dear {$admin['name']},
                
                A critical security event has been detected:
                
                Event Type: {$event_type}
                Description: {$description}
                User ID: {$user_id}
                IP Address: {$ip_address}
                Time: " . date('Y-m-d H:i:s') . "
                
                Please review this event and take appropriate action.
                
                Best regards,
                Koperasi SaaS Security System
            ";
            
            // Send email using your preferred email library
            error_log("Security email to {$admin['email']}: {$subject}");
        }
    }
    
    /**
     * Clean old audit logs
     */
    public function cleanOldLogs() {
        $stmt = $this->db->prepare("
            DELETE FROM security_audit_log
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$this->config['audit_retention_days']]);
        
        return $stmt->rowCount();
    }
    
    /**
     * Get security dashboard data
     */
    public function getSecurityDashboard() {
        $dashboard = [
            'security_score' => $this->calculateSecurityScore(30),
            'recent_alerts' => $this->getRecentAlerts(),
            'blocked_ips' => $this->getBlockedIPs(),
            'security_events' => $this->getSecurityEvents(7),
            'user_security_scores' => $this->getUserSecurityScores()
        ];
        
        return $dashboard;
    }
    
    /**
     * Get recent alerts
     */
    private function getRecentAlerts() {
        $stmt = $this->db->prepare("
            SELECT * FROM security_alerts
            WHERE status = 'ACTIVE'
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get blocked IPs
     */
    private function getBlockedIPs() {
        $stmt = $this->db->prepare("
            SELECT ip_address, reason, blocked_at, expires_at
            FROM blocked_ips
            WHERE expires_at > NOW()
            ORDER BY blocked_at DESC
            LIMIT 5
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get security events
     */
    private function getSecurityEvents($days) {
        $stmt = $this->db->prepare("
            SELECT event_type, COUNT(*) as count, created_at
            FROM security_audit_log
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY event_type, DATE(created_at)
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user security scores
     */
    private function getUserSecurityScores() {
        $stmt = $this->db->prepare("
            SELECT 
                u.id,
                u.name,
                u.email,
                COALESCECE(us.security_score, 100) as security_score,
                COUNT(sa.id) as total_events
            FROM users u
            LEFT JOIN (
                SELECT user_id, AVG(
                    CASE 
                        WHEN event_type LIKE '%FAILED%' THEN 0 
                        ELSE 100 
                    END
                ) as security_score,
                    COUNT(*) as total_events
                FROM security_audit_log
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY user_id
            ) us ON u.id = us.user_id
            GROUP BY u.id, u.name, u.email, us.security_score, us.total_events
            ORDER BY security_score ASC
            LIMIT 10
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>