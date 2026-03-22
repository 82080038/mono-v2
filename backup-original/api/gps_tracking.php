<?php
/**
 * batch-update-legacy.php - Updated with Security
 * Auto-generated security update
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

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Include required files
// Initialize logging
Logger::initialize();

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => null,
    'errors' => [],
    'timestamp' => date('Y-m-d H:i:s')
];

// Database connection
try {
    $db = DatabaseHelper::getInstance();
} catch (PDOException $e) {
    $response['message'] = 'Database connection failed';
    $response['errors'][] = $e->getMessage();
    SecurityMiddleware::sendJSONResponse($response);
    exit;
}

// Initialize validator
$validator = new DataValidator();

// Handle API requests
$method = $_SERVER['REQUEST_METHOD'];

// For POST requests, get endpoint from JSON body
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $endpoint = $input['endpoint'] ?? '';
    $resourceId = $input['resourceId'] ?? '';
    $action = $input['action'] ?? '';
} else {
    // For GET requests, parse URL
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));
    
    // Remove 'api' from path if present
    if ($pathParts[0] === 'api') {
        array_shift($pathParts);
    }
    
    $endpoint = $pathParts[0] ?? '';
    $resourceId = $pathParts[1] ?? '';
    $action = $pathParts[2] ?? '';
}

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($endpoint, $resourceId, $action, $db, $validator);
            break;
        case 'POST':
            handlePostRequest($endpoint, $resourceId, $action, $db, $validator);
            break;
        case 'PUT':
            handlePutRequest($endpoint, $resourceId, $action, $db, $validator);
            break;
        case 'DELETE':
            handleDeleteRequest($endpoint, $resourceId, $action, $db, $validator);
            break;
        default:
            $response['success'] = false;
            $response['message'] = 'Method not allowed';
            SecurityMiddleware::sendJSONResponse($response);
            break;
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    SecurityMiddleware::sendJSONResponse($response);
}

function handleGetRequest($endpoint, $resourceId, $action, $db, $validator) {
    global $response;
    
    try {
        switch ($endpoint) {
            case 'gps':
                if ($resourceId == 'location') {
                    if ($action == 'current') {
                        getCurrentLocation($db, $validator);
                    } elseif ($action == 'history') {
                        getLocationHistory($db, $validator);
                    } else {
                        getLocation($db, $validator);
                    }
                } elseif ($resourceId == 'tracking') {
                    if ($action == 'active') {
                        getActiveTracking($db, $validator);
                    } else {
                        getTrackingData($db, $validator);
                    }
                } elseif ($resourceId == 'geofence') {
                    getGeofenceAreas($db, $validator);
                } else {
                    throw new Exception('Invalid GPS endpoint');
                }
                break;
                
            default:
                throw new Exception('Endpoint not found');
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        SecurityMiddleware::sendJSONResponse($response);
    }
}

function getCurrentLocation($db, $validator) {
    global $response;
    
    $staffId = $_GET['staff_id'] ?? '';
    
    if (empty($staffId)) {
        throw new Exception('Staff ID required');
    }
    
    $location = $db->fetchOne(
        "SELECT * FROM gps_logs 
         WHERE staff_id = ? 
         ORDER BY created_at DESC 
         LIMIT 1",
        [$staffId]
    );
    
    if (!$location) {
        throw new Exception('No location data found');
    }
    
    $response['success'] = true;
    $response['message'] = 'Current location retrieved successfully';
    $response['data'] = $location;
    SecurityMiddleware::sendJSONResponse($response);
}

function getLocationHistory($db, $validator) {
    global $response;
    
    $staffId = $_GET['staff_id'] ?? '';
    $dateFrom = $_GET['date_from'] ?? date('Y-m-d 00:00:00');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d 23:59:59');
    $limit = $_GET['limit'] ?? 100;
    
    if (empty($staffId)) {
        throw new Exception('Staff ID required');
    }
    
    $locations = $db->fetchAll(
        "SELECT * FROM gps_logs 
         WHERE staff_id = ? AND created_at BETWEEN ? AND ?
         ORDER BY created_at DESC
         LIMIT ?",
        [$staffId, $dateFrom, $dateTo, $limit]
    );
    
    $response['success'] = true;
    $response['message'] = 'Location history retrieved successfully';
    $response['data'] = [
        'locations' => $locations,
        'total' => count($locations)
    ];
    SecurityMiddleware::sendJSONResponse($response);
}

function getGeofenceAreas($db, $validator) {
    global $response;
    
    $geofences = $db->fetchAll("SELECT * FROM geofence_areas WHERE is_active = 1 ORDER BY name");
    
    $response['success'] = true;
    $response['message'] = 'Geofence areas retrieved successfully';
    $response['data'] = $geofences;
    SecurityMiddleware::sendJSONResponse($response);
}

function handlePostRequest($endpoint, $resourceId, $action, $db, $validator) {
    global $response;
    
    try {
        // Re-read input to get the actual data
        $input = json_decode(file_get_contents('php://input'), true);
        
        switch ($endpoint) {
            case 'gps':
                if ($resourceId == 'location') {
                    addLocation($db, $validator, $input);
                } elseif ($resourceId == 'geofence') {
                    getGeofenceAreas($db, $validator);
                } else {
                    throw new Exception('Invalid GPS endpoint');
                }
                break;
                
            default:
                throw new Exception('Endpoint not found');
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        SecurityMiddleware::sendJSONResponse($response);
    }
}

function addLocation($db, $validator, $input) {
    global $response;
    
    $rules = [
        'staff_id' => 'required|integer',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'accuracy' => 'numeric',
        'altitude' => 'numeric',
        'speed' => 'numeric',
        'heading' => 'numeric'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        throw new Exception('Validation failed');
    }
    
    // Validate coordinates
    $coordErrors = $validator->validateCoordinates($input['latitude'], $input['longitude']);
    if (!empty($coordErrors)) {
        $response['errors'] = ['coordinates' => $coordErrors];
        throw new Exception('Invalid coordinates');
    }
    
    $locationId = $db->insert('gps_logs', [
        'staff_id' => $input['staff_id'],
        'latitude' => $input['latitude'],
        'longitude' => $input['longitude'],
        'accuracy' => $input['accuracy'] ?? null,
        'altitude' => $input['altitude'] ?? null,
        'speed' => $input['speed'] ?? null,
        'heading' => $input['heading'] ?? null,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    $response['success'] = true;
    $response['message'] = 'Location added successfully';
    $response['data'] = ['location_id' => $locationId];
    SecurityMiddleware::sendJSONResponse($response);
}

function handlePutRequest($endpoint, $resourceId, $action, $db, $validator) {
    global $response;
    
    $response['success'] = false;
    $response['message'] = 'PUT method not implemented for GPS tracking';
    SecurityMiddleware::sendJSONResponse($response);
}

function handleDeleteRequest($endpoint, $resourceId, $action, $db, $validator) {
    global $response;
    
    $response['success'] = false;
    $response['message'] = 'DELETE method not implemented for GPS tracking';
    SecurityMiddleware::sendJSONResponse($response);
}
?>
