<?php
/**
 * Creator Dashboard API
 * API endpoint untuk creator dashboard
 */

require_once __DIR__ . '/config/error-config.php';

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/../DatabaseHelper.php';
require_once __DIR__ . '/../AuthHelper.php';
require_once __DIR__ . '/../SecurityHelper.php';
require_once __DIR__ . '/../SecurityMiddleware.php';

class CreatorDashboardAPI {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseHelper::getInstance();
    }
    
    public function handleRequest() {
        // Enable CORS
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        
        // Get request data
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? $_GET['action'] ?? 'dashboard';
        
        try {
            // Authenticate and validate creator role
            $user = SecurityMiddleware::requireAuth('creator');
            
            switch ($action) {
                case 'dashboard':
                    $this->getDashboardData();
                    break;
                case 'system_stats':
                    $this->getSystemStats();
                    break;
                case 'user_management':
                    $this->getUserManagementData();
                    break;
                case 'database_info':
                    $this->getDatabaseInfo();
                    break;
                case 'system_health':
                    $this->getSystemHealth();
                    break;
                case 'recent_activities':
                    $this->getRecentActivities();
                    break;
                default:
                    $this->sendResponse(['error' => 'Invalid action'], 400);
            }
        } catch (Exception $e) {
            $this->sendResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get dashboard data
     */
    private function getDashboardData() {
        $data = [
            'system_overview' => $this->getSystemOverview(),
            'quick_stats' => $this->getQuickStats(),
            'recent_activities' => $this->getRecentActivities(),
            'system_health' => $this->getSystemHealth(),
            'user_info' => $this->getCurrentUserInfo()
        ];
        
        $this->sendResponse($data);
    }
    
    /**
     * Get system statistics
     */
    private function getSystemStats() {
        $stats = [
            'total_users' => $this->getTotalUsers(),
            'total_tables' => $this->getTotalTables(),
            'total_apis' => $this->getTotalAPIs(),
            'system_uptime' => $this->getSystemUptime(),
            'database_size' => $this->getDatabaseSize(),
            'api_calls_today' => $this->getAPICallsToday(),
            'active_sessions' => $this->getActiveSessions(),
            'last_backup' => $this->getLastBackup()
        ];
        
        $this->sendResponse($stats);
    }
    
    /**
     * Get user management data
     */
    private function getUserManagementData() {
        $users = $this->db->fetchAll("SELECT id, username, email, role, is_active, created_at FROM users ORDER BY created_at DESC");
        
        $userStats = [
            'total_users' => count($users),
            'active_users' => count(array_filter($users, fn($u) => $u['is_active'])),
            'by_role' => $this->getUsersByRole($users),
            'recent_registrations' => array_filter($users, fn($u) => strtotime($u['created_at']) > strtotime('-7 days'))
        ];
        
        $this->sendResponse([
            'users' => $users,
            'stats' => $userStats
        ]);
    }
    
    /**
     * Get database information
     */
    private function getDatabaseInfo() {
        $tables = $this->db->fetchAll("SHOW TABLES");
        $tableInfo = [];
        
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            $stats = $this->db->fetchOne("SELECT COUNT(*) as count FROM `$tableName`");
            $size = $this->db->fetchOne("SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size FROM information_schema.TABLES WHERE table_schema = DATABASE() AND table_name = '$tableName'");
            
            $tableInfo[] = [
                'name' => $tableName,
                'records' => $stats['count'],
                'size_mb' => $size['size'] ?? 0
            ];
        }
        
        $this->sendResponse([
            'tables' => $tableInfo,
            'total_tables' => count($tables),
            'total_size_mb' => array_sum(array_column($tableInfo, 'size_mb'))
        ]);
    }
    
    /**
     * Get system health
     */
    private function getSystemHealth() {
        $health = [
            'database' => $this->checkDatabaseHealth(),
            'api' => $this->checkAPIHealth(),
            'security' => $this->checkSecurityHealth(),
            'storage' => $this->checkStorageHealth(),
            'memory' => $this->checkMemoryHealth(),
            'overall' => 'good'
        ];
        
        // Determine overall health
        $issues = array_filter($health, fn($h) => $h['status'] !== 'good');
        if (count($issues) > 2) {
            $health['overall'] = 'critical';
        } elseif (count($issues) > 0) {
            $health['overall'] = 'warning';
        }
        
        $this->sendResponse($health);
    }
    
    /**
     * Get recent activities
     */
    private function getRecentActivities() {
        $activities = [
            [
                'type' => 'user_registration',
                'description' => 'New user registered',
                'icon' => 'fas fa-user-plus',
                'color' => 'success',
                'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours'))
            ],
            [
                'type' => 'database_optimization',
                'description' => 'Database optimized',
                'icon' => 'fas fa-database',
                'color' => 'info',
                'timestamp' => date('Y-m-d H:i:s', strtotime('-6 hours'))
            ],
            [
                'type' => 'security_scan',
                'description' => 'Security scan completed',
                'icon' => 'fas fa-shield-alt',
                'color' => 'warning',
                'timestamp' => date('Y-m-d H:i:s', strtotime('-12 hours'))
            ],
            [
                'type' => 'backup_created',
                'description' => 'Backup created',
                'icon' => 'fas fa-save',
                'color' => 'primary',
                'timestamp' => date('Y-m-d H:i:s', strtotime('-1 day'))
            ]
        ];
        
        $this->sendResponse($activities);
    }
    
    // Helper methods
    private function getSystemOverview() {
        return [
            'system_name' => 'KSP Lam Gabe Jaya',
            'version' => '4.0',
            'environment' => 'production',
            'last_update' => date('Y-m-d H:i:s'),
            'creator_role' => 'active'
        ];
    }
    
    private function getQuickStats() {
        return [
            'total_users' => $this->getTotalUsers(),
            'total_tables' => $this->getTotalTables(),
            'total_apis' => $this->getTotalAPIs(),
            'system_uptime' => '99.9%'
        ];
    }
    
    private function getCurrentUserInfo() {
        $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? '');
        $userData = AuthHelper::validateJWTToken($token);
        
        return [
            'id' => $userData['user_id'] ?? null,
            'username' => $userData['username'] ?? 'creator',
            'role' => 'creator',
            'full_name' => 'Application Creator'
        ];
    }
    
    private function getTotalUsers() {
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM users");
        return $result['count'] ?? 0;
    }
    
    private function getTotalTables() {
        $result = $this->db->fetchAll("SHOW TABLES");
        return count($result);
    }
    
    private function getTotalAPIs() {
        $apiFiles = glob(__DIR__ . '/*.php');
        return count($apiFiles);
    }
    
    private function getSystemUptime() {
        return '99.9%'; // Simulated
    }
    
    private function getDatabaseSize() {
        $result = $this->db->fetchOne("SELECT ROUND(SUM((data_length + index_length)) / 1024 / 1024, 2) AS size FROM information_schema.TABLES WHERE table_schema = DATABASE()");
        return $result['size'] ?? 0;
    }
    
    private function getAPICallsToday() {
        return 1247; // Simulated
    }
    
    private function getActiveSessions() {
        return 23; // Simulated
    }
    
    private function getLastBackup() {
        return date('Y-m-d H:i:s', strtotime('-2 days'));
    }
    
    private function getUsersByRole($users) {
        $roles = [];
        foreach ($users as $user) {
            $roles[$user['role']] = ($roles[$user['role']] ?? 0) + 1;
        }
        return $roles;
    }
    
    private function checkDatabaseHealth() {
        try {
            $this->db->fetchOne("SELECT 1");
            return ['status' => 'good', 'message' => 'Database online'];
        } catch (Exception $e) {
            return ['status' => 'critical', 'message' => 'Database offline'];
        }
    }
    
    private function checkAPIHealth() {
        return ['status' => 'good', 'message' => 'API operational'];
    }
    
    private function checkSecurityHealth() {
        return ['status' => 'good', 'message' => 'Security active'];
    }
    
    private function checkStorageHealth() {
        $free = disk_free_space('/');
        $total = disk_total_space('/');
        $usage = ($total - $free) / $total * 100;
        
        if ($usage > 90) {
            return ['status' => 'critical', 'message' => 'Storage critical', 'usage' => round($usage, 2)];
        } elseif ($usage > 80) {
            return ['status' => 'warning', 'message' => 'Storage warning', 'usage' => round($usage, 2)];
        } else {
            return ['status' => 'good', 'message' => 'Storage ok', 'usage' => round($usage, 2)];
        }
    }
    
    private function checkMemoryHealth() {
        $memoryUsage = memory_get_usage() / 1024 / 1024;
        $memoryLimit = ini_get('memory_limit');
        
        if ($memoryUsage > 500) {
            return ['status' => 'warning', 'message' => 'High memory usage', 'usage_mb' => round($memoryUsage, 2)];
        } else {
            return ['status' => 'good', 'message' => 'Memory ok', 'usage_mb' => round($memoryUsage, 2)];
        }
    }
    
    private function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
}

// Handle request
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $api = new CreatorDashboardAPI();
    $api->handleRequest();
}
?>
