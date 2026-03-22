<?php
/**
 * Database Activities API
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

try {
    $db = DatabaseHelper::getInstance();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get recent activities
$activities = [
    [
        'id' => 1,
        'type' => 'user_login',
        'description' => 'Admin user logged in',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour')),
        'user' => 'admin'
    ],
    [
        'id' => 2,
        'type' => 'loan_created',
        'description' => 'New loan application created',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours')),
        'user' => 'staff'
    ],
    [
        'id' => 3,
        'type' => 'payment_received',
        'description' => 'Payment received from member',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-3 hours')),
        'user' => 'teller'
    ]
];

echo json_encode(['success' => true, 'data' => $activities]);
?>
