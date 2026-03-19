<?php
/**
 * Minimal working API - Test each component individually
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get endpoint
$endpoint = $_GET['endpoint'] ?? 'test';

// Test database connection
try {
    require_once __DIR__ . '/../config/Config.php';
    $db = Config::getDatabase();
    $dbConnected = true;
} catch (Exception $e) {
    $dbConnected = false;
    $dbError = $e->getMessage();
}

switch ($endpoint) {
    case 'users':
        if ($dbConnected) {
            try {
                $sql = "SELECT 
                            u.id,
                            u.name,
                            u.email,
                            u.is_active as status,
                            u.last_login_at as last_login,
                            u.created_at
                        FROM users u
                        WHERE u.deleted_at IS NULL
                        ORDER BY u.created_at DESC
                        LIMIT 10";
                
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => $users,
                    'count' => count($users),
                    'message' => 'Users retrieved successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving users: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'settings':
        if ($dbConnected) {
            try {
                $sql = "SELECT key_name, value, description, category 
                        FROM system_settings 
                        ORDER BY category, key_name";
                
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Group by category
                $groupedSettings = [];
                foreach ($settings as $setting) {
                    $groupedSettings[$setting['category']][] = $setting;
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $groupedSettings,
                    'message' => 'Settings retrieved successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving settings: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'system_health':
        try {
            $data = [
                'server_status' => 'healthy',
                'database_status' => $dbConnected ? 'connected' : 'disconnected',
                'api_status' => 'operational',
                'php_version' => phpversion(),
                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            if ($dbConnected) {
                // Get user count
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL AND is_active = 1");
                $stmt->execute();
                $result = $stmt->fetch();
                $data['active_users'] = $result['count'];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $data,
                'message' => 'System health retrieved successfully'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error retrieving system health: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'test':
        echo json_encode([
            'success' => true,
            'message' => 'API test successful',
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => phpversion(),
            'database_connected' => $dbConnected
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found',
            'available_endpoints' => ['users', 'settings', 'system_health', 'test']
        ]);
        break;
}

?>
