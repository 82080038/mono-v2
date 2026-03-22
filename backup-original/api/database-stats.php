<?php
/**
 * Database Stats API
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

// Get database statistics
$stats = [
    'total_tables' => 0,
    'total_records' => 0,
    'database_size' => 0,
    'last_backup' => null,
    'tables' => []
];

// Get table list
$stmt = $db->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM `$table`");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    $stats['tables'][] = [
        'name' => $table,
        'records' => $count
    ];
    
    $stats['total_records'] += $count;
}

$stats['total_tables'] = count($tables);

echo json_encode(['success' => true, 'data' => $stats]);
?>
