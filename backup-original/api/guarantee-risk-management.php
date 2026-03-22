<?php
/**
 * Guarantee Risk Management API
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
        // Get guarantee dashboard stats
        $stats = [
            'total_guarantees' => 25,
            'active_guarantees' => 18,
            'high_risk_guarantees' => 3,
            'pending_guarantees' => 4
        ];
        
        echo json_encode(['success' => true, 'data' => $stats]);
        break;
        
    case "get_guarantees":
        // Get guarantees list
        $guarantees = [
            ['id' => 1, 'borrower_name' => 'John Doe', 'guarantor_name' => 'Jane Smith', 'amount' => 5000000, 'status' => 'Active', 'risk_level' => 'Low'],
            ['id' => 2, 'borrower_name' => 'Bob Johnson', 'guarantor_name' => 'Alice Brown', 'amount' => 3000000, 'status' => 'Active', 'risk_level' => 'Medium'],
            ['id' => 3, 'borrower_name' => 'Mike Wilson', 'guarantor_name' => 'Sarah Davis', 'amount' => 7000000, 'status' => 'Pending', 'risk_level' => 'High']
        ];
        
        echo json_encode(['success' => true, 'data' => $guarantees]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
?>
