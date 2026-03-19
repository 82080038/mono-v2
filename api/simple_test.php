<?php
/**
 * Simple API Test - Minimal version to isolate the issue
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Get endpoint
$endpoint = $_GET['endpoint'] ?? 'test';

// Simple response
switch ($endpoint) {
    case 'users':
        echo json_encode([
            'success' => true,
            'data' => [
                ['id' => 1, 'name' => 'Test User 1', 'email' => 'test1@example.com'],
                ['id' => 2, 'name' => 'Test User 2', 'email' => 'test2@example.com']
            ],
            'message' => 'Users retrieved successfully'
        ]);
        break;
        
    case 'settings':
        echo json_encode([
            'success' => true,
            'data' => [
                'app_name' => 'KSP Lam Gabe Jaya',
                'app_version' => '2.0.0',
                'timezone' => 'Asia/Jakarta'
            ],
            'message' => 'Settings retrieved successfully'
        ]);
        break;
        
    case 'system_health':
        echo json_encode([
            'success' => true,
            'data' => [
                'server_status' => 'healthy',
                'database_status' => 'connected',
                'api_status' => 'operational',
                'uptime' => '15 days 4 hours'
            ],
            'message' => 'System health retrieved successfully'
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found',
            'available_endpoints' => ['users', 'settings', 'system_health']
        ]);
        break;
}

?>
