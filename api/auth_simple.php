<?php
/**
 * Ultra Simple Auth API - No validation, just return success
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Get action from GET or POST
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Always return success for testing
$response = [
    'success' => true,
    'message' => 'Login berhasil (simple version)',
    'data' => [
        'user' => [
            'id' => 1,
            'name' => 'Administrator',
            'email' => 'admin@ksp-lamabejaya.co.id',
            'role' => 'Super Admin',
            'token' => base64_encode(json_encode([
                'user_id' => 1,
                'name' => 'Administrator',
                'role' => 'Super Admin',
                'exp' => time() + 86400
            ])),
            'last_login' => date('Y-m-d H:i:s')
        ]
    ],
    'debug' => [
        'method' => $_SERVER['REQUEST_METHOD'],
        'action' => $action,
        'post_data' => $_POST,
        'get_data' => $_GET,
        'input_raw' => file_get_contents('php://input'),
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set'
    ]
];

// Handle token validation
if ($action == 'validate') {
    $token = $_GET['token'] ?? '';
    $response['success'] = true;
    $response['message'] = 'Token valid (simple version)';
    $response['data'] = [
        'user_id' => 1,
        'role' => 'Super Admin',
        'expires_at' => date('Y-m-d H:i:s', time() + 86400)
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
