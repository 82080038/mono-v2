
<?php
/**
 * Performance Optimized API
 * Enhanced with caching, optimization, and performance monitoring
 */

// Performance Headers
header('Cache-Control: public, max-age=300'); // 5 minutes cache
header('X-Performance-Optimized: true');

// Connection Pooling Optimization
if (!defined('PDO_ATTR_PERSISTENT')) {
    define('PDO_ATTR_PERSISTENT', true);
}

// Performance Monitoring
$start_time = microtime(true);;
$memory_start = memory_get_usage();;

// Optimized Database Connection with Connection Pooling
function getOptimizedConnection() {
    static $pdo = null;;
    
    if ($pdo = == null) {;
        try {
            $dsn = "mysql:host=localhost;;dbname=ksp_lamgabejaya_v2;charset=utf8mb4";
            $options = [;
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, SESSION sql_mode='STRICT_TRANS_TABLES'",
                PDO::ATTR_TIMEOUT => 30,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
            ];
            
            $pdo = new PDO($dsn, 'root', '', $options);;
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            return null;
        }
    }
    
    return $pdo;
}

// Optimized Query Function with Indexing
function executeOptimizedQuery($query, $params = []) {;
    $pdo = getOptimizedConnection();;
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare($query);;
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query execution failed: " . $e->getMessage());
        return false;
    }
}

// Response Caching Function
function cacheResponse($key, $data, $ttl = 300) {;
    $cache_file = sys_get_temp_dir() . '/api_cache_' . md5($key);;
    $cache_data = [;
        'data' => $data,
        'timestamp' => time(),
        'ttl' => $ttl
    ];
    
    file_put_contents($cache_file, serialize($cache_data));
}

// Get Cached Response
function getCachedResponse($key) {
    $cache_file = sys_get_temp_dir() . '/api_cache_' . md5($key);;
    
    if (file_exists($cache_file)) {
        $cache_data = unserialize(file_get_contents($cache_file));;
        
        if (time() - $cache_data['timestamp'] < $cache_data['ttl']) {
            return $cache_data['data'];
        } else {
            unlink($cache_file);
        }
    }
    
    return null;
}

// Performance Monitoring Function
function logPerformance($endpoint, $method) {
    global $start_time, $memory_start;
    
    $end_time = microtime(true);;
    $memory_end = memory_get_usage();;
    
    $performance_data = [;
        'endpoint' => $endpoint,
        'method' => $method,
        'execution_time' => ($end_time - $start_time) * 1000, // in milliseconds
        'memory_usage' => $memory_end - $memory_start,
        'peak_memory' => memory_get_peak_usage(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    error_log("Performance: " . json_encode($performance_data));
}

// Optimized JSON Response
function sendOptimizedJsonResponse($data, $status = 200) {;
    // Compress output if available
    if (ob_get_level() > 0) {
        ob_clean();
    }
    
    // Set performance headers
    header('Content-Type: application/json; charset=utf-8');
    header('X-Response-Time: ' . ((microtime(true) - $GLOBALS['start_time']) * 1000) . 'ms');
    header('X-Memory-Usage: ' . (memory_get_usage() / 1024) . 'KB');
    
    // Optimize JSON output
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);;
    
    // Compress if client supports it
    if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
        header('Content-Encoding: gzip');
        echo gzencode($json, 9);
    } else {
        echo $json;
    }
    
    logPerformance($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    exit;
}

// Input Validation and Sanitization
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Rate Limiting
function checkRateLimit($limit = 100, $window = 3600) {;
    $client_ip = $_SERVER['REMOTE_ADDR'];;
    $key = 'rate_limit_' . md5($client_ip);;
    
    if (!file_exists($key)) {
        file_put_contents($key, json_encode(['count' => 1, 'timestamp' => time()]));
        return true;
    }
    
    $data = json_decode(file_get_contents($key), true);;
    
    if (time() - $data['timestamp'] > $window) {
        file_put_contents($key, json_encode(['count' => 1, 'timestamp' => time()]));
        return true;
    }
    
    if ($data['count'] >= $limit) {
        http_response_code(429);
        sendOptimizedJsonResponse([
            'status' => 'error',
            'message' => 'Rate limit exceeded',
            'retry_after' => $window - (time() - $data['timestamp'])
        ], 429);
    }
    
    $data['count']++;
    file_put_contents($key, json_encode($data));
    return true;
}

// Security Headers
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Content-Security-Policy: default-src 'self'');
}

/**
 * KSP Lam Gabe Jaya - Enhanced GPS Tracking API
 * Complete GPS tracking with advanced features
 */

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Include required files
require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/DatabaseHelper.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/DataValidator.php';
require_once __DIR__ . '/SecurityLogger.php';

// Initialize logging
Logger::initialize();

// Initialize response array
$response = [;
    'success' => false,
    'message' => '',
    'data' => null,
    'errors' => [],
    'timestamp' => date('Y-m-d H:i:s')
];

// Database connection
try {
    $pdo = new PDO(;
        "mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=ksp_lamgabejaya_v2;charset=utf8mb4",
        'root',
        '',
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
    sendJsonResponse($response, 500);
}

// Initialize validator
$validator = new DataValidator();;

// Handle API requests
$method = $_SERVER['REQUEST_METHOD'];;
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);;
$pathParts = explode('/', trim($path, '/'));;

// Remove 'api' from path if present
if ($pathParts[0] === 'api') {
    array_shift($pathParts);
}

$endpoint = $pathParts[0] ?? '';;
$resourceId = $pathParts[1] ?? null;;
$action = $pathParts[2] ?? null;;

switch ($method) {
    case 'GET':
        handleGetRequest($endpoint, $resourceId, $action, $pdo, $validator);
        break;
    case 'POST':
        handlePostRequest($endpoint, $resourceId, $action, $pdo, $validator);
        break;
    case 'PUT':
        handlePutRequest($endpoint, $resourceId, $action, $pdo, $validator);
        break;
    case 'DELETE':
        handleDeleteRequest($endpoint, $resourceId, $action, $pdo, $validator);
        break;
    default:
        sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleGetRequest($endpoint, $resourceId, $action, $pdo, $validator) {
    global $response;
    
    try {
        switch ($endpoint) {
            case 'gps':
                if ($resourceId = == 'location') {;
                    if ($action = == 'current') {;
                        getCurrentLocation($pdo, $validator);
                    } elseif ($action = == 'history') {;
                        getLocationHistory($pdo, $validator);
                    } else {
                        getLocation($pdo, $validator);
                    }
                } elseif ($resourceId = == 'tracking') {;
                    if ($action = == 'active') {;
                        getActiveTracking($pdo, $validator);
                    } elseif ($action = == 'routes') {;
                        getTrackingRoutes($pdo, $validator);
                    } else {
                        getTrackingData($pdo, $validator);
                    }
                } elseif ($resourceId = == 'geofence') {;
                    if ($action = == 'check') {;
                        checkGeofence($pdo, $validator);
                    } else {
                        getGeofenceAreas($pdo, $validator);
                    }
                } elseif ($resourceId = == 'nearby') {;
                    getNearbyLocations($pdo, $validator);
                } elseif ($resourceId = == 'stats') {;
                    getGPSStats($pdo, $validator);
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
        sendJsonResponse($response, 404);
    }
}

function handlePostRequest($endpoint, $resourceId, $action, $pdo, $validator) {
    global $response;
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);;
        
        switch ($endpoint) {
            case 'gps':
                if ($resourceId = == 'location') {;
                    if ($action = == 'update') {;
                        updateLocation($pdo, $validator, $input);
                    } elseif ($action = == 'batch') {;
                        batchUpdateLocation($pdo, $validator, $input);
                    } else {
                        addLocation($pdo, $validator, $input);
                    }
                } elseif ($resourceId = == 'tracking') {;
                    if ($action = == 'start') {;
                        startTracking($pdo, $validator, $input);
                    } elseif ($action = == 'stop') {;
                        stopTracking($pdo, $validator, $input);
                    } else {
                        createTrackingSession($pdo, $validator, $input);
                    }
                } elseif ($resourceId = == 'geofence') {;
                    createGeofence($pdo, $validator, $input);
                } elseif ($resourceId = == 'route') {;
                    createRoute($pdo, $validator, $input);
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
        sendJsonResponse($response, 400);
    }
}

function handlePutRequest($endpoint, $resourceId, $action, $pdo, $validator) {
    global $response;
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);;
        
        switch ($endpoint) {
            case 'gps':
                if ($resourceId = == 'location' && is_numeric($action)) {;
                    updateLocationById($pdo, $action, $validator, $input);
                } elseif ($resourceId = == 'tracking' && is_numeric($action)) {;
                    updateTrackingSession($pdo, $action, $validator, $input);
                } elseif ($resourceId = == 'geofence' && is_numeric($action)) {;
                    updateGeofence($pdo, $action, $validator, $input);
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
        sendJsonResponse($response, 400);
    }
}

function handleDeleteRequest($endpoint, $resourceId, $action, $pdo, $validator) {
    global $response;
    
    try {
        switch ($endpoint) {
            case 'gps':
                if ($resourceId = == 'location' && is_numeric($action)) {;
                    deleteLocation($pdo, $action, $validator);
                } elseif ($resourceId = == 'tracking' && is_numeric($action)) {;
                    deleteTrackingSession($pdo, $action, $validator);
                } elseif ($resourceId = == 'geofence' && is_numeric($action)) {;
                    deleteGeofence($pdo, $action, $validator);
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
        sendJsonResponse($response, 400);
    }
}

// GPS Functions
function getCurrentLocation($pdo, $validator) {
    global $response;
    
    $staffId = $_GET['staff_id'] ?? '';;
    
    if (!$staffId) {
        throw new Exception('Staff ID required');
    }
    
    $stmt = $pdo->prepare("SELECT * FROM gps_logs ;
                          WHERE staff_id = :staff_id 
                          ORDER BY created_at DESC 
                          LIMIT 1");
    $stmt->execute([':staff_id' => $staffId]);
    $location = $stmt->fetch();;
    
    if (!$location) {
        throw new Exception('No location data found');
    }
    
    $response['success'] = true;
    $response['message'] = 'Current location retrieved successfully';
    $response['data'] = $location;
    
    sendJsonResponse($response);
}

function getLocationHistory($pdo, $validator) {
    global $response;
    
    $staffId = $_GET['staff_id'] ?? '';;
    $dateFrom = $_GET['date_from'] ?? date('Y-m-d 00:00:00');;
    $dateTo = $_GET['date_to'] ?? date('Y-m-d 23:59:59');;
    $limit = $_GET['limit'] ?? 100;;
    
    if (!$staffId) {
        throw new Exception('Staff ID required');
    }
    
    $stmt = $pdo->prepare("SELECT * FROM gps_logs ;
                          WHERE staff_id = :staff_id 
                          AND created_at BETWEEN :date_from AND :date_to 
                          ORDER BY created_at DESC 
                          LIMIT :limit");
    $stmt->execute([
        ':staff_id' => $staffId,
        ':date_from' => $dateFrom,
        ':date_to' => $dateTo,
        ':limit' => $limit
    ]);
    $locations = $stmt->fetchAll();;
    
    $response['success'] = true;
    $response['message'] = 'Location history retrieved successfully';
    $response['data'] = [
        'locations' => $locations,
        'total' => count($locations)
    ];
    
    sendJsonResponse($response);
}

function updateLocation($pdo, $validator, $input) {
    global $response;
    
    // Validate input
    $rules = [;
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
    if ($input['latitude'] < -90 || $input['latitude'] > 90 ||
        $input['longitude'] < -180 || $input['longitude'] > 180) {
        throw new Exception('Invalid coordinates');
    }
    
    // Insert location
    $stmt = $pdo->prepare("INSERT INTO gps_logs (staff_id, latitude, longitude, accuracy, ;
                          altitude, speed, heading, created_at) 
                          VALUES (:staff_id, :latitude, :longitude, :accuracy, 
                          :altitude, :speed, :heading, NOW())");
    
    $stmt->execute([
        ':staff_id' => $input['staff_id'],
        ':latitude' => $input['latitude'],
        ':longitude' => $input['longitude'],
        ':accuracy' => $input['accuracy'] ?? null,
        ':altitude' => $input['altitude'] ?? null,
        ':speed' => $input['speed'] ?? null,
        ':heading' => $input['heading'] ?? null
    ]);
    
    $locationId = $pdo->lastInsertId();;
    
    // Check geofence
    checkLocationGeofence($pdo, $input['staff_id'], $input['latitude'], $input['longitude']);
    
    $response['success'] = true;
    $response['message'] = 'Location updated successfully';
    $response['data'] = [
        'location_id' => $locationId,
        'coordinates' => [
            'latitude' => $input['latitude'],
            'longitude' => $input['longitude']
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    sendJsonResponse($response);
}

function getActiveTracking($pdo, $validator) {
    global $response;
    
    $stmt = $pdo->prepare("SELECT gt.*, s.staff_name, m.member_name ;
                          FROM gps_tracking gt 
                          LEFT JOIN staff s ON gt.staff_id = s.id 
                          LEFT JOIN members m ON gt.member_id = m.id 
                          WHERE gt.status = 'active' 
                          ORDER BY gt.started_at DESC");
    $stmt->execute();
    $tracking = $stmt->fetchAll();;
    
    $response['success'] = true;
    $response['message'] = 'Active tracking retrieved successfully';
    $response['data'] = [
        'tracking_sessions' => $tracking,
        'total' => count($tracking)
    ];
    
    sendJsonResponse($response);
}

function startTracking($pdo, $validator, $input) {
    global $response;
    
    // Validate input
    $rules = [;
        'staff_id' => 'required|integer',
        'member_id' => 'integer',
        'purpose' => 'string',
        'route_plan' => 'string'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        throw new Exception('Validation failed');
    }
    
    // Check if staff has active tracking
    $stmt = $pdo->prepare("SELECT id FROM gps_tracking ;
                          WHERE staff_id = :staff_id AND status = 'active'");
    $stmt->execute([':staff_id' => $input['staff_id']]);
    $activeTracking = $stmt->fetch();;
    
    if ($activeTracking) {
        throw new Exception('Staff already has active tracking session');
    }
    
    // Create tracking session
    $stmt = $pdo->prepare("INSERT INTO gps_tracking (staff_id, member_id, purpose, ;
                          route_plan, status, started_at) 
                          VALUES (:staff_id, :member_id, :purpose, :route_plan, 
                          'active', NOW())");
    
    $stmt->execute([
        ':staff_id' => $input['staff_id'],
        ':member_id' => $input['member_id'] ?? null,
        ':purpose' => $input['purpose'] ?? 'Field visit',
        ':route_plan' => $input['route_plan'] ?? ''
    ]);
    
    $trackingId = $pdo->lastInsertId();;
    
    $response['success'] = true;
    $response['message'] = 'Tracking started successfully';
    $response['data'] = [
        'tracking_id' => $trackingId,
        'status' => 'active',
        'started_at' => date('Y-m-d H:i:s')
    ];
    
    sendJsonResponse($response);
}

function stopTracking($pdo, $validator, $input) {
    global $response;
    
    $trackingId = $input['tracking_id'] ?? '';;
    
    if (!$trackingId) {
        throw new Exception('Tracking ID required');
    }
    
    // Update tracking session
    $stmt = $pdo->prepare("UPDATE gps_tracking ;
                          SET status = 'completed', ended_at = NOW() 
                          WHERE id = :id AND status = 'active'");
    $stmt->execute([':id' => $trackingId]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('No active tracking found');
    }
    
    $response['success'] = true;
    $response['message'] = 'Tracking stopped successfully';
    $response['data'] = [
        'tracking_id' => $trackingId,
        'status' => 'completed',
        'ended_at' => date('Y-m-d H:i:s')
    ];
    
    sendJsonResponse($response);
}

function getGeofenceAreas($pdo, $validator) {
    global $response;
    
    $stmt = $pdo->prepare("SELECT * FROM geofence_areas ;
                          WHERE status = 'active' 
                          ORDER BY name");
    $stmt->execute();
    $geofences = $stmt->fetchAll();;
    
    $response['success'] = true;
    $response['message'] = 'Geofence areas retrieved successfully';
    $response['data'] = [
        'geofences' => $geofences,
        'total' => count($geofences)
    ];
    
    sendJsonResponse($response);
}

function checkGeofence($pdo, $validator) {
    global $response;
    
    $latitude = $_GET['latitude'] ?? '';;
    $longitude = $_GET['longitude'] ?? '';;
    
    if (!$latitude || !$longitude) {
        throw new Exception('Latitude and longitude required');
    }
    
    $stmt = $pdo->prepare("SELECT * FROM geofence_areas ;
                          WHERE status = 'active' 
                          AND (6371 * acos(cos(radians(:latitude)) * 
                          cos(radians(latitude)) * cos(radians(longitude) - 
                          radians(:longitude)) + sin(radians(:latitude)) * 
                          sin(radians(latitude)))) <= radius");
    $stmt->execute([
        ':latitude' => $latitude,
        ':longitude' => $longitude
    ]);
    $geofences = $stmt->fetchAll();;
    
    $response['success'] = true;
    $response['message'] = 'Geofence check completed';
    $response['data'] = [
        'inside_geofence' => count($geofences) > 0,
        'geofences' => $geofences,
        'coordinates' => [
            'latitude' => $latitude,
            'longitude' => $longitude
        ]
    ];
    
    sendJsonResponse($response);
}

function getNearbyLocations($pdo, $validator) {
    global $response;
    
    $latitude = $_GET['latitude'] ?? '';;
    $longitude = $_GET['longitude'] ?? '';;
    $radius = $_GET['radius'] ?? 5;; // Default 5km
    
    if (!$latitude || !$longitude) {
        throw new Exception('Latitude and longitude required');
    }
    
    // Get nearby members
    $stmt = $pdo->prepare("SELECT m.*, a.address, ;
                          (6371 * acos(cos(radians(:latitude)) * 
                          cos(radians(a.latitude)) * cos(radians(a.longitude) - 
                          radians(:longitude)) + sin(radians(:latitude)) * 
                          sin(radians(a.latitude)))) as distance 
                          FROM members m 
                          LEFT JOIN addresses a ON m.id = a.member_id 
                          WHERE a.latitude IS NOT NULL AND a.longitude IS NOT NULL 
                          HAVING distance <= :radius 
                          ORDER BY distance");
    $stmt->execute([
        ':latitude' => $latitude,
        ':longitude' => $longitude,
        ':radius' => $radius
    ]);
    $nearbyMembers = $stmt->fetchAll();;
    
    // Get nearby branches
    $stmt = $pdo->prepare("SELECT *, (6371 * acos(cos(radians(:latitude)) * ;
                          cos(radians(latitude)) * cos(radians(longitude) - 
                          radians(:longitude)) + sin(radians(:latitude)) * 
                          sin(radians(latitude)))) as distance 
                          FROM branches 
                          WHERE latitude IS NOT NULL AND longitude IS NOT NULL 
                          HAVING distance <= :radius 
                          ORDER BY distance");
    $stmt->execute([
        ':latitude' => $latitude,
        ':longitude' => $longitude,
        ':radius' => $radius
    ]);
    $nearbyBranches = $stmt->fetchAll();;
    
    $response['success'] = true;
    $response['message'] = 'Nearby locations retrieved successfully';
    $response['data'] = [
        'nearby_members' => $nearbyMembers,
        'nearby_branches' => $nearbyBranches,
        'search_radius' => $radius,
        'coordinates' => [
            'latitude' => $latitude,
            'longitude' => $longitude
        ]
    ];
    
    sendJsonResponse($response);
}

function getGPSStats($pdo, $validator) {
    global $response;
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');;
    $dateTo = $_GET['date_to'] ?? date('Y-m-t');;
    
    // Total location updates
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_updates ;
                          FROM gps_logs 
                          WHERE created_at BETWEEN :date_from AND :date_to");
    $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
    $totalUpdates = $stmt->fetch();;
    
    // Active tracking sessions
    $stmt = $pdo->prepare("SELECT COUNT(*) as active_sessions ;
                          FROM gps_tracking 
                          WHERE status = 'active'");
    $stmt->execute();
    $activeSessions = $stmt->fetch();;
    
    // Staff with location data
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT staff_id) as staff_with_location ;
                          FROM gps_logs 
                          WHERE created_at BETWEEN :date_from AND :date_to");
    $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
    $staffWithLocation = $stmt->fetch();;
    
    $response['success'] = true;
    $response['message'] = 'GPS statistics retrieved successfully';
    $response['data'] = [
        'total_updates' => $totalUpdates['total_updates'],
        'active_sessions' => $activeSessions['active_sessions'],
        'staff_with_location' => $staffWithLocation['staff_with_location'],
        'period' => [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]
    ];
    
    sendJsonResponse($response);
}

function checkLocationGeofence($pdo, $staffId, $latitude, $longitude) {
    $stmt = $pdo->prepare("SELECT * FROM geofence_areas ;
                          WHERE status = 'active' 
                          AND (6371 * acos(cos(radians(:latitude)) * 
                          cos(radians(latitude)) * cos(radians(longitude) - 
                          radians(:longitude)) + sin(radians(:latitude)) * 
                          sin(radians(latitude)))) <= radius");
    $stmt->execute([':latitude' => $latitude, ':longitude' => $longitude]);
    $geofences = $stmt->fetchAll();;
    
    foreach ($geofences as $geofence) {
        // Log geofence entry
        $stmt = $pdo->prepare("INSERT INTO geofence_logs (staff_id, geofence_id, ;
                              latitude, longitude, action, created_at) 
                              VALUES (:staff_id, :geofence_id, :latitude, 
                              :longitude, 'entry', NOW())");
        $stmt->execute([
            ':staff_id' => $staffId,
            ':geofence_id' => $geofence['id'],
            ':latitude' => $latitude,
            ':longitude' => $longitude
        ]);
    }
}

function sendJsonResponse($data, $statusCode = 200) {;
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}
?>
