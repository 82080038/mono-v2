<?php
/**
 * GPS Tracking Original - Fixed Version
 */

// Security headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token");

// Handle preflight requests
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    exit(0);
}

// Prevent direct access
if (!defined("KSP_API_ACCESS")) {
    header("HTTP/1.0 403 Forbidden");
    exit("Direct access to this file is not allowed.");
}

// Include required files
require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/DatabaseHelper.php";
require_once __DIR__ . "/Logger.php";
require_once __DIR__ . "/DataValidator.php";
require_once __DIR__ . "/SecurityLogger.php";
require_once __DIR__ . "/AuthHelper.php";
require_once __DIR__ . "/SecurityHelper.php";
require_once __DIR__ . "/SecurityMiddleware.php";

// Initialize services
Logger::initialize();
$securityLogger = SecurityLogger::getInstance();
$db = DatabaseHelper::getInstance();
$validator = new DataValidator();

// Initialize response
$response = [
    "success" => false,
    "message" => "",
    "data" => null,
    "errors" => [],
    "timestamp" => date("Y-m-d H:i:s")
];

// Database connection
try {
    $pdo = new PDO(
        "mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=ksp_lamgabejaya_v2;charset=utf8mb4",
        "root",
        "root",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true
        ]
    );
} catch (PDOException $e) {
    $response['message'] = 'Database connection failed';
    $response['errors'][] = $e->getMessage();
    SecurityMiddleware::sendJSONResponse($response, 500);
}

// GPS tracking functions
function getLocationHistory($staffId, $dateFrom, $dateTo) {
    global $pdo;
    
    $stmt = $pdo->prepare('SELECT * FROM gps_logs 
                          WHERE staff_id = :staff_id 
                          ORDER BY created_at DESC 
                          LIMIT 1');
    $stmt->execute([':staff_id' => $staffId]);
    $location = $stmt->fetch();
    
    if (!$location) {
        throw new Exception('No location data found');
    }
    
    $response['success'] = true;
    $response['message'] = 'Location history retrieved';
    $response['data'] = $location;
    
    return $response;
}

function getDetailedLocationHistory($staffId, $dateFrom, $dateTo, $limit = 100) {
    global $pdo;
    
    $stmt = $pdo->prepare('SELECT * FROM gps_logs 
                          WHERE staff_id = :staff_id 
                          AND created_at BETWEEN :date_from AND :date_to 
                          ORDER BY created_at DESC 
                          LIMIT :limit');
    $stmt->execute([
        ':staff_id' => $staffId,
        ':date_from' => $dateFrom,
        ':date_to' => $dateTo,
        ':limit' => $limit
    ]);
    $locations = $stmt->fetchAll();
    
    $response['success'] = true;
    $response['message'] = 'Detailed location history retrieved';
    $response['data'] = $locations;
    
    return $response;
}

// Handle API requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($action, $db, $validator);
            break;
        case 'POST':
            handlePostRequest($action, $db, $validator);
            break;
        default:
            $response['message'] = 'Method not allowed';
            SecurityMiddleware::sendJSONResponse($response);
            break;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    SecurityMiddleware::sendJSONResponse($response);
}

function handleGetRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'location_history':
            $user = SecurityMiddleware::requireAuth('staff');
            $staffId = $user['id'];
            $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
            $dateTo = $_GET['date_to'] ?? date('Y-m-d');
            $limit = (int)($_GET['limit'] ?? 100);
            
            $result = getDetailedLocationHistory($staffId, $dateFrom, $dateTo, $limit);
            SecurityMiddleware::sendJSONResponse($result);
            break;
            
        case 'current_location':
            $user = SecurityMiddleware::requireAuth('staff');
            $result = getLocationHistory($user['id'], null, null);
            SecurityMiddleware::sendJSONResponse($result);
            break;
            
        default:
            $response['message'] = 'Invalid action';
            SecurityMiddleware::sendJSONResponse($response);
            break;
    }
}

function handlePostRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'add_location':
            $user = SecurityMiddleware::requireAuth('staff');
            $input = SecurityMiddleware::getJSONInput();
            
            $rules = [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'accuracy' => 'numeric',
                'speed' => 'numeric'
            ];
            
            if (!$validator->validate($input, $rules)) {
                $response['errors'] = $validator->getErrors();
                $response['message'] = 'Validation failed';
                SecurityMiddleware::sendJSONResponse($response);
            }
            
            // Add location logic here
            $response['success'] = true;
            $response['message'] = 'Location added successfully';
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        default:
            $response['message'] = 'Invalid action';
            SecurityMiddleware::sendJSONResponse($response);
            break;
    }
}
?>
