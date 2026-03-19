<?php
/**
 * GPS Integration API Endpoint
 * Handles GPS tracking for web application
 */

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/GPSIntegration.php';

// Initialize GPS integration
$gps = new GPSIntegration();

// Get request method and data
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($method == 'OPTIONS') {
    exit(0);
}

// Route requests
$endpoint = $_GET['action'] ?? $input['action'] ?? '';

switch ($endpoint) {
    case 'get_location':
        handleGetLocation();
        break;
        
    case 'update_location':
        handleUpdateLocation();
        break;
        
    case 'location_history':
        handleLocationHistory();
        break;
        
    case 'check_geofence':
        handleCheckGeofence();
        break;
        
    case 'nearby_locations':
        handleNearbyLocations();
        break;
        
    case 'get_route':
        handleGetRoute();
        break;
        
    case 'sync_offline':
        handleSyncOffline();
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint tidak ditemukan'
        ]);
}

/**
 * Get current location
 */
function handleGetLocation() {
    global $gps;
    
    $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
    
    if (!$userId) {
        echo json_encode([
            'success' => false,
            'message' => 'User ID diperlukan'
        ]);
        return;
    }
    
    $result = $gps->getCurrentLocation($userId);
    echo json_encode($result);
}

/**
 * Update location
 */
function handleUpdateLocation() {
    global $gps;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['user_id']) || !isset($data['latitude']) || !isset($data['longitude'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Data lokasi tidak lengkap'
        ]);
        return;
    }
    
    $result = $gps->updateLocation(
        $data['user_id'],
        $data['latitude'],
        $data['longitude'],
        $data['accuracy'] ?? null,
        $data['address'] ?? null
    );
    
    echo json_encode($result);
}

/**
 * Get location history
 */
function handleLocationHistory() {
    global $gps;
    
    $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
    $dateFrom = $_GET['date_from'] ?? $_POST['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? $_POST['date_to'] ?? null;
    
    if (!$userId) {
        echo json_encode([
            'success' => false,
            'message' => 'User ID diperlukan'
        ]);
        return;
    }
    
    $result = $gps->getLocationHistory($userId, $dateFrom, $dateTo);
    echo json_encode($result);
}

/**
 * Check geofence
 */
function handleCheckGeofence() {
    global $gps;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['user_id']) || !isset($data['target_lat']) || !isset($data['target_lng'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Data geofence tidak lengkap'
        ]);
        return;
    }
    
    $result = $gps->checkGeofence(
        $data['user_id'],
        $data['target_lat'],
        $data['target_lng'],
        $data['radius'] ?? 100
    );
    
    echo json_encode($result);
}

/**
 * Get nearby locations
 */
function handleNearbyLocations() {
    global $gps;
    
    $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
    $radius = $_GET['radius'] ?? $_POST['radius'] ?? 1000;
    
    if (!$userId) {
        echo json_encode([
            'success' => false,
            'message' => 'User ID diperlukan'
        ]);
        return;
    }
    
    $result = $gps->getNearbyLocations($userId, $radius);
    echo json_encode($result);
}

/**
 * Get route
 */
function handleGetRoute() {
    global $gps;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['user_id']) || !isset($data['member_ids'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Data route tidak lengkap'
        ]);
        return;
    }
    
    $result = $gps->getRoute($data['user_id'], $data['member_ids']);
    echo json_encode($result);
}

/**
 * Sync offline GPS data
 */
function handleSyncOffline() {
    global $gps;
    
    $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
    
    if (!$userId) {
        echo json_encode([
            'success' => false,
            'message' => 'User ID diperlukan'
        ]);
        return;
    }
    
    $result = $gps->syncOfflineData($userId);
    echo json_encode($result);
}

?>
