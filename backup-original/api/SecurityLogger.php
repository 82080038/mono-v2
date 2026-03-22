<?php

class SecurityLogger {
    private static $instance = null;
    private $logFile;
    
    private function __construct() {
        $this->logFile = __DIR__ . '/../logs/security.log';
        $this->ensureLogDirectory();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function ensureLogDirectory() {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    public function logSecurityEvent($event, $details = []) {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $logEntry = [
            'timestamp' => $timestamp,
            'event' => $event,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'details' => $details
        ];
        
        $logLine = json_encode($logEntry) . PHP_EOL;
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    public static function logLoginAttempt($username, $success, $reason = null) {
        $logger = self::getInstance();
        $logger->logSecurityEvent('LOGIN_ATTEMPT', [
            'username' => $username,
            'success' => $success,
            'reason' => $reason
        ]);
    }
    
    public static function logUnauthorizedAccess($endpoint, $userId = null) {
        $logger = self::getInstance();
        $logger->logSecurityEvent('UNAUTHORIZED_ACCESS', [
            'endpoint' => $endpoint,
            'user_id' => $userId
        ]);
    }
    
    public static function logSuspiciousActivity($activity, $details = []) {
        $logger = self::getInstance();
        $logger->logSecurityEvent('SUSPICIOUS_ACTIVITY', array_merge([
            'activity' => $activity
        ], $details));
    }
    
    public static function logDataAccess($table, $action, $recordId = null) {
        $logger = self::getInstance();
        $logger->logSecurityEvent('DATA_ACCESS', [
            'table' => $table,
            'action' => $action,
            'record_id' => $recordId
        ]);
    }
    
    public static function logPasswordChange($userId, $success) {
        $logger = self::getInstance();
        $logger->logSecurityEvent('PASSWORD_CHANGE', [
            'user_id' => $userId,
            'success' => $success
        ]);
    }
    
    public static function logGPSAnomaly($staffId, $latitude, $longitude, $anomaly) {
        $logger = self::getInstance();
        $logger->logSecurityEvent('GPS_ANOMALY', [
            'staff_id' => $staffId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'anomaly' => $anomaly
        ]);
    }
    
    public static function logGeofenceBreach($staffId, $geofenceId, $action) {
        $logger = self::getInstance();
        $logger->logSecurityEvent('GEOFENCE_BREACH', [
            'staff_id' => $staffId,
            'geofence_id' => $geofenceId,
            'action' => $action // 'entry' or 'exit'
        ]);
    }
    
    public static function logAPIAbuse($endpoint, $reason, $details = []) {
        $logger = self::getInstance();
        $logger->logSecurityEvent('API_ABUSE', array_merge([
            'endpoint' => $endpoint,
            'reason' => $reason
        ], $details));
    }
    
    public static function logInvalidToken($token) {
        $logger = self::getInstance();
        $logger->logSecurityEvent('INVALID_TOKEN', [
            'token_hash' => md5($token)
        ]);
    }
    
    public static function logRateLimitExceeded($endpoint, $ip) {
        $logger = self::getInstance();
        $logger->logSecurityEvent('RATE_LIMIT_EXCEEDED', [
            'endpoint' => $endpoint,
            'ip' => $ip
        ]);
    }
    
    public static function logDataModification($table, $action, $oldData = null, $newData = null) {
        $logger = self::getInstance();
        $logger->logSecurityEvent('DATA_MODIFICATION', [
            'table' => $table,
            'action' => $action,
            'old_data' => $oldData,
            'new_data' => $newData
        ]);
    }
    
    public function getSecurityLogs($limit = 100, $event = null) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $logs = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $logs = array_reverse($logs); // Most recent first
        
        $filteredLogs = [];
        foreach ($logs as $log) {
            $logData = json_decode($log, true);
            if ($logData) {
                if (!$event || $logData['event'] === $event) {
                    $filteredLogs[] = $logData;
                }
                if (count($filteredLogs) >= $limit) {
                    break;
                }
            }
        }
        
        return $filteredLogs;
    }
    
    public function detectSuspiciousPatterns($hours = 24) {
        $logs = $this->getSecurityLogs(1000);
        $cutoffTime = time() - ($hours * 3600);
        
        $patterns = [
            'failed_logins' => 0,
            'unauthorized_access' => 0,
            'suspicious_activities' => 0,
            'api_abuse' => 0,
            'gps_anomalies' => 0
        ];
        
        foreach ($logs as $log) {
            $logTime = strtotime($log['timestamp']);
            if ($logTime < $cutoffTime) {
                continue;
            }
            
            switch ($log['event']) {
                case 'LOGIN_ATTEMPT':
                    if (!$log['details']['success']) {
                        $patterns['failed_logins']++;
                    }
                    break;
                case 'UNAUTHORIZED_ACCESS':
                    $patterns['unauthorized_access']++;
                    break;
                case 'SUSPICIOUS_ACTIVITY':
                    $patterns['suspicious_activities']++;
                    break;
                case 'API_ABUSE':
                    $patterns['api_abuse']++;
                    break;
                case 'GPS_ANOMALY':
                    $patterns['gps_anomalies']++;
                    break;
            }
        }
        
        return $patterns;
    }
}
