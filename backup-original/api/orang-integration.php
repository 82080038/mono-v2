<?php
/**
 * Orang Integration API
 */

require_once __DIR__ . '/config/error-config.php';

define('KSP_API_ACCESS', true);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once 'DatabaseHelper.php';
require_once 'AuthHelper.php';
require_once 'SecurityHelper.php';

$action = $_REQUEST["action"] ?? "dashboard";

try {
    $db = DatabaseHelper::getInstance();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

switch ($action) {
    case "dashboard":
        // Get orang database dashboard stats
        $stats = [
            'total_persons' => 150,
            'active_persons' => 120,
            'recent_additions' => 5,
            'integration_status' => 'connected'
        ];
        
        echo json_encode(['success' => true, 'data' => $stats]);
        break;
        
    case "search_persons":
        // Search persons
        $query = $_GET['query'] ?? '';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
        
        // Mock data for now
        $persons = [
            ['id' => 1, 'name' => 'John Doe', 'nik' => '1234567890123456', 'phone' => '08123456789'],
            ['id' => 2, 'name' => 'Jane Smith', 'nik' => '2345678901234567', 'phone' => '08234567890'],
            ['id' => 3, 'name' => 'Bob Johnson', 'nik' => '3456789012345678', 'phone' => '08345678901']
        ];
        
        echo json_encode(['success' => true, 'data' => $persons]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
?>
