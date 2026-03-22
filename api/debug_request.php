<?php
// Debug request endpoint
header('Content-Type: application/json');

echo json_encode([
    'method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
    'post_data' => $_POST,
    'get_data' => $_GET,
    'raw_input' => file_get_contents('php://input'),
    'headers' => getallheaders()
]);
?>
