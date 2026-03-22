<?php
/**
 * Staff GPS Tracking API
 * Handles GPS tracking and visit management for staff
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
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

// Initialize services
Logger::initialize();
$securityLogger = SecurityLogger::getInstance();
$db = DatabaseHelper::getInstance();
$validator = new DataValidator();

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'data' => null,
    'errors' => [],
    'timestamp' => date('Y-m-d H:i:s')
];

// Authentication middleware
function requireAuth($role = 'staff') {
    global $db;
    
    $token = getTokenFromRequest();
    if (!$token) {
        throw new Exception('Authentication required');
    }
    
    $tokenData = validateJWTToken($token);
    if (!$tokenData) {
        throw new Exception('Invalid token');
    }
    
    $user = $db->fetchOne(
        "SELECT * FROM users WHERE id = ? AND is_active = 1",
        [$tokenData['user_id']]
    );
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    if ($user['role'] !== $role && $user['role'] !== 'admin') {
        throw new Exception('Staff access required');
    }
    
    return array_merge($user, $tokenData);
}

function getTokenFromRequest() {
    $headers = getallheaders();
    return $headers['Authorization'] ?? $headers['authorization'] ?? $_REQUEST['token'] ?? null;
}

function validateJWTToken($token) {
    if (!$token) {
        return null;
    }
    
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }
    
    $payload = base64_decode($parts[1]);
    $payloadData = json_decode($payload, true);
    
    if (!$payloadData || $payloadData['exp'] < time()) {
        return null;
    }
    
    return $payloadData;
}

function getCurrentUser() {
    global $db;
    
    $token = getTokenFromRequest();
    if (!$token) {
        return null;
    }
    
    $tokenData = validateJWTToken($token);
    if (!$tokenData) {
        return null;
    }
    
    return $db->fetchOne(
        "SELECT * FROM users WHERE id = ? AND is_active = 1",
        [$tokenData['user_id']]
    );
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
        case 'PUT':
            handlePutRequest($action, $db, $validator);
            break;
        case 'DELETE':
            handleDeleteRequest($action, $db, $validator);
            break;
        default:
            $response['message'] = 'Method not allowed';
            echo json_encode($response);
            break;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    echo json_encode($response);
}

function handleGetRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'tracking_sessions':
            handleGetTrackingSessions($db, $validator);
            break;
        case 'session_detail':
            handleGetSessionDetail($db, $validator);
            break;
        case 'gps_logs':
            handleGetGpsLogs($db, $validator);
            break;
        case 'geofence_areas':
            handleGetGeofenceAreas($db, $validator);
            break;
        case 'member_locations':
            handleGetMemberLocations($db, $validator);
            break;
        case 'live_tracking':
            handleGetLiveTracking($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handlePostRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'start_tracking':
            handleStartTracking($db, $validator);
            break;
        case 'stop_tracking':
            handleStopTracking($db, $validator);
            break;
        case 'add_location':
            handleAddLocation($db, $validator);
            break;
        case 'visit_member':
            handleVisitMember($db, $validator);
            break;
        case 'create_geofence':
            handleCreateGeofence($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handlePutRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'update_session':
            handleUpdateSession($db, $validator);
            break;
        case 'update_geofence':
            handleUpdateGeofence($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleDeleteRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'delete_session':
            handleDeleteSession($db, $validator);
            break;
        case 'delete_geofence':
            handleDeleteGeofence($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleGetTrackingSessions($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $status = $_GET['status'] ?? '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = ["gt.staff_id = ?"];
    $params = [$user['id']];
    
    if (!empty($status)) {
        $whereConditions[] = "gt.status = ?";
        $params[] = $status;
    }
    
    if (!empty($dateFrom)) {
        $whereConditions[] = "gt.created_at >= ?";
        $params[] = $dateFrom . ' 00:00:00';
    }
    
    if (!empty($dateTo)) {
        $whereConditions[] = "gt.created_at <= ?";
        $params[] = $dateTo . ' 23:59:59';
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM gps_tracking gt $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get tracking sessions
    $sql = "SELECT gt.*, 
                    m.full_name as member_name, 
                    m.member_number, 
                    m.phone as member_phone,
                    (SELECT COUNT(*) FROM gps_logs WHERE tracking_id = gt.id) as location_count,
                    CASE 
                        WHEN gt.status = 'completed' THEN 'Completed'
                        WHEN gt.status = 'active' THEN 'In Progress'
                        WHEN gt.status = 'scheduled' THEN 'Scheduled'
                        ELSE gt.status
                    END as status_display
             FROM gps_tracking gt 
             LEFT JOIN members m ON gt.member_id = m.id 
             $whereClause
             ORDER BY gt.created_at DESC 
             LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $sessions = $db->fetchAll($sql, $params);
    
    // Add session metadata
    foreach ($sessions as &$session) {
        $session['duration_formatted'] = formatDuration($session['duration_minutes']);
        $session['distance_formatted'] = number_format($session['distance_km'], 2, ',', '.') . ' km';
    }
    
    $response['success'] = true;
    $response['message'] = 'Tracking sessions retrieved successfully';
    $response['data'] = [
        'sessions' => $sessions,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    echo json_encode($response);
}

function handleGetSessionDetail($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $sessionId = (int)($_GET['id'] ?? 0);
    
    if ($sessionId <= 0) {
        $response['message'] = 'Session ID required';
        echo json_encode($response);
        return;
    }
    
    // Get session details
    $session = $db->fetchOne(
        "SELECT gt.*, 
                m.full_name as member_name, 
                m.member_number, 
                m.phone as member_phone,
                m.address as member_address
         FROM gps_tracking gt 
         LEFT JOIN members m ON gt.member_id = m.id 
         WHERE gt.id = ? AND gt.staff_id = ?",
        [$sessionId, $user['id']]
    );
    
    if (!$session) {
        $response['message'] = 'Session not found';
        echo json_encode($response);
        return;
    }
    
    // Get GPS logs for this session
    $gpsLogs = $db->fetchAll(
        "SELECT * FROM gps_logs WHERE tracking_id = ? ORDER BY timestamp ASC",
        [$sessionId]
    );
    
    // Calculate session statistics
    $sessionStats = [
        'total_locations' => count($gpsLogs),
        'total_distance' => $session['distance_km'],
        'duration_minutes' => $session['duration_minutes'],
        'average_speed' => $session['duration_minutes'] > 0 ? ($session['distance_km'] / ($session['duration_minutes'] / 60)) : 0,
        'start_time' => $session['started_at'],
        'end_time' => $session['ended_at']
    ];
    
    // Get geofence breaches if any
    $geofenceBreaches = $db->fetchAll(
        "SELECT * FROM geofence_breaches WHERE tracking_id = ? ORDER BY created_at DESC",
        [$sessionId]
    );
    
    $response['success'] = true;
    $response['message'] = 'Session detail retrieved successfully';
    $response['data'] = [
        'session' => $session,
        'gps_logs' => $gpsLogs,
        'statistics' => $sessionStats,
        'geofence_breaches' => $geofenceBreaches
    ];
    
    echo json_encode($response);
}

function handleGetGpsLogs($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 50);
    $trackingId = (int)($_GET['tracking_id'] ?? 0);
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = ["gl.staff_id = ?"];
    $params = [$user['id']];
    
    if ($trackingId > 0) {
        $whereConditions[] = "gl.tracking_id = ?";
        $params[] = $trackingId;
    }
    
    if (!empty($dateFrom)) {
        $whereConditions[] = "gl.timestamp >= ?";
        $params[] = $dateFrom . ' 00:00:00';
    }
    
    if (!empty($dateTo)) {
        $whereConditions[] = "gl.timestamp <= ?";
        $params[] = $dateTo . ' 23:59:59';
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM gps_logs gl $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get GPS logs
    $sql = "SELECT gl.*, 
                    gt.purpose as session_purpose,
                    gt.status as session_status
             FROM gps_logs gl 
             LEFT JOIN gps_tracking gt ON gl.tracking_id = gt.id 
             $whereClause
             ORDER BY gl.timestamp DESC 
             LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $logs = $db->fetchAll($sql, $params);
    
    // Add location metadata
    foreach ($logs as &$log) {
        $log['location_formatted'] = formatLocation($log['latitude'], $log['longitude']);
        $log['timestamp_formatted'] = date('Y-m-d H:i:s', strtotime($log['timestamp']));
    }
    
    $response['success'] = true;
    $response['message'] = 'GPS logs retrieved successfully';
    $response['data'] = [
        'logs' => $logs,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    echo json_encode($response);
}

function handleGetGeofenceAreas($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $areas = $db->fetchAll(
        "SELECT * FROM geofence_areas WHERE is_active = 1 ORDER BY name"
    );
    
    // Add area metadata
    foreach ($areas as &$area) {
        $area['radius_formatted'] = number_format($area['radius'], 2, ',', '.') . ' m';
        $area['location_formatted'] = formatLocation($area['latitude'], $area['longitude']);
    }
    
    $response['success'] = true;
    $response['message'] = 'Geofence areas retrieved successfully';
    $response['data'] = $areas;
    
    echo json_encode($response);
}

function handleGetMemberLocations($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $memberId = (int)($_GET['member_id'] ?? 0);
    
    if ($memberId <= 0) {
        $response['message'] = 'Member ID required';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne("SELECT * FROM members WHERE id = ? AND status = 'Active'", [$memberId]);
    
    if (!$member) {
        $response['message'] = 'Member not found';
        echo json_encode($response);
        return;
    }
    
    // Get member's last known location
    $lastLocation = $db->fetchOne(
        "SELECT latitude, longitude, accuracy, timestamp 
         FROM gps_logs 
         WHERE tracking_id IN (SELECT id FROM gps_tracking WHERE member_id = ?) 
         ORDER BY timestamp DESC 
         LIMIT 1",
        [$memberId]
    );
    
    // Get recent visits to this member
    $recentVisits = $db->fetchAll(
        "SELECT gt.*, 
                u.full_name as staff_name,
                gt.created_at as visit_date
         FROM gps_tracking gt 
         LEFT JOIN users u ON gt.staff_id = u.id 
         WHERE gt.member_id = ? 
         ORDER BY gt.created_at DESC 
         LIMIT 5",
        [$memberId]
    );
    
    $memberData = [
        'member' => $member,
        'last_location' => $lastLocation,
        'recent_visits' => $recentVisits,
        'total_visits' => $db->fetchOne("SELECT COUNT(*) as count FROM gps_tracking WHERE member_id = ?", [$memberId])['count']
    ];
    
    $response['success'] = true;
    $response['message'] = 'Member location data retrieved successfully';
    $response['data'] = $memberData;
    
    echo json_encode($response);
}

function handleGetLiveTracking($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    // Get active tracking session
    $activeSession = $db->fetchOne(
        "SELECT gt.*, 
                m.full_name as member_name, 
                m.member_number
         FROM gps_tracking gt 
         LEFT JOIN members m ON gt.member_id = m.id 
         WHERE gt.staff_id = ? AND gt.status = 'active'
         ORDER BY gt.created_at DESC 
         LIMIT 1",
        [$user['id']]
    );
    
    if (!$activeSession) {
        $response['message'] = 'No active tracking session';
        echo json_encode($response);
        return;
    }
    
    // Get recent GPS logs for active session
    $recentLogs = $db->fetchAll(
        "SELECT * FROM gps_logs WHERE tracking_id = ? ORDER BY timestamp DESC LIMIT 10",
        [$activeSession['id']]
    );
    
    // Check for geofence breaches
    $breaches = $db->fetchAll(
        "SELECT * FROM geofence_breaches WHERE tracking_id = ? ORDER BY created_at DESC LIMIT 5",
        [$activeSession['id']]
    );
    
    $liveData = [
        'session' => $activeSession,
        'recent_logs' => $recentLogs,
        'breaches' => $breaches,
        'current_location' => end($recentLogs),
        'session_duration' => calculateSessionDuration($activeSession['started_at'])
    ];
    
    $response['success'] = true;
    $response['message'] = 'Live tracking data retrieved successfully';
    $response['data'] = $liveData;
    
    echo json_encode($response);
}

function handleStartTracking($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'member_id' => 'required|integer',
        'purpose' => 'required|string|min:5',
        'route_plan' => 'string',
        'latitude' => 'numeric',
        'longitude' => 'numeric'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Verify member exists
    $member = $db->fetchOne("SELECT * FROM members WHERE id = ? AND status = 'Active'", [$input['member_id']]);
    
    if (!$member) {
        $response['message'] = 'Member not found or inactive';
        echo json_encode($response);
        return;
    }
    
    // Check if there's already an active session
    $activeSession = $db->fetchOne("SELECT id FROM gps_tracking WHERE staff_id = ? AND status = 'active'", [$user['id']]);
    
    if ($activeSession) {
        $response['message'] = 'You already have an active tracking session';
        echo json_encode($response);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Create tracking session
        $trackingData = [
            'staff_id' => $user['id'],
            'member_id' => $input['member_id'],
            'purpose' => $input['purpose'],
            'route_plan' => $input['route_plan'] ?? '',
            'status' => 'active',
            'started_at' => date('Y-m-d H:i:s'),
            'start_latitude' => $input['latitude'] ?? null,
            'start_longitude' => $input['longitude'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $trackingId = $db->insert('gps_tracking', $trackingData);
        
        // Add initial GPS log
        if (!empty($input['latitude']) && !empty($input['longitude'])) {
            $logData = [
                'staff_id' => $user['id'],
                'tracking_id' => $trackingId,
                'latitude' => $input['latitude'],
                'longitude' => $input['longitude'],
                'timestamp' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->insert('gps_logs', $logData);
        }
        
        // Log activity
        $db->insert('staff_activities', [
            'staff_id' => $user['id'],
            'activity_type' => 'tracking_started',
            'description' => 'Started tracking: ' . $input['purpose'],
            'reference_id' => $trackingId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $db->commit();
        
        $response['success'] = true;
        $response['message'] = 'Tracking session started successfully';
        $response['data'] = [
            'tracking_id' => $trackingId,
            'member' => $member,
            'purpose' => $input['purpose']
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleStopTracking($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'tracking_id' => 'required|integer',
        'latitude' => 'numeric',
        'longitude' => 'numeric',
        'notes' => 'string'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get tracking session
    $session = $db->fetchOne(
        "SELECT * FROM gps_tracking WHERE id = ? AND staff_id = ? AND status = 'active'",
        [$input['tracking_id'], $user['id']]
    );
    
    if (!$session) {
        $response['message'] = 'Active tracking session not found';
        echo json_encode($response);
        return;
    }
    
    // Get final location
    $finalLocation = $db->fetchOne(
        "SELECT * FROM gps_logs WHERE tracking_id = ? ORDER BY timestamp DESC LIMIT 1",
        [$input['tracking_id']]
    );
    
    $db->beginTransaction();
    
    try {
        // Update tracking session
        $updateData = [
            'status' => 'completed',
            'ended_at' => date('Y-m-d H:i:s'),
            'end_latitude' => $input['latitude'] ?? ($finalLocation['latitude'] ?? null),
            'end_longitude' => $input['longitude'] ?? ($finalLocation['longitude'] ?? null),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $db->update('gps_tracking', $updateData, 'id = ?', [$input['tracking_id']]);
        
        // Add final GPS log if provided
        if (!empty($input['latitude']) && !empty($input['longitude'])) {
            $logData = [
                'staff_id' => $user['id'],
                'tracking_id' => $input['tracking_id'],
                'latitude' => $input['latitude'],
                'longitude' => $input['longitude'],
                'timestamp' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->insert('gps_logs', $logData);
        }
        
        // Log activity
        $db->insert('staff_activities', [
            'staff_id' => $user['id'],
            'activity_type' => 'tracking_completed',
            'description' => 'Completed tracking session',
            'reference_id' => $input['tracking_id'],
            'notes' => $input['notes'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $db->commit();
        
        $response['success'] = true;
        $response['message'] = 'Tracking session stopped successfully';
        $response['data'] = $updateData;
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleAddLocation($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'tracking_id' => 'required|integer',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'accuracy' => 'numeric',
        'speed' => 'numeric',
        'heading' => 'numeric'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Verify tracking session ownership
    $session = $db->fetchOne(
        "SELECT * FROM gps_tracking WHERE id = ? AND staff_id = ? AND status = 'active'",
        [$input['tracking_id'], $user['id']]
    );
    
    if (!$session) {
        $response['message'] = 'Active tracking session not found';
        echo json_encode($response);
        return;
    }
    
    // Check for geofence breach
    $breach = checkGeofenceBreach($db, $input['latitude'], $input['longitude']);
    
    $db->beginTransaction();
    
    try {
        // Add GPS log
        $logData = [
            'staff_id' => $user['id'],
            'tracking_id' => $input['tracking_id'],
            'latitude' => $input['latitude'],
            'longitude' => $input['longitude'],
            'accuracy' => $input['accuracy'] ?? null,
            'speed' => $input['speed'] ?? null,
            'heading' => $input['heading'] ?? null,
            'timestamp' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $logId = $db->insert('gps_logs', $logData);
        
        // Calculate distance from previous location
        $previousLog = $db->fetchOne(
            "SELECT latitude, longitude FROM gps_logs WHERE tracking_id = ? ORDER BY timestamp DESC LIMIT 1",
            [$input['tracking_id']]
        );
        
        if ($previousLog) {
            $distance = calculateDistance($previousLog['latitude'], $previousLog['longitude'], $input['latitude'], $input['longitude']);
            
            // Update tracking session
            $db->update('gps_tracking', [
                'distance_km' => $session['distance_km'] + $distance,
                'duration_minutes' => $session['duration_minutes'] + 1,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$input['tracking_id']]);
        }
        
        // Log geofence breach if detected
        if ($breach) {
            $breachData = [
                'tracking_id' => $input['tracking_id'],
                'geofence_id' => $breach['id'],
                'latitude' => $input['latitude'],
                'longitude' => $input['longitude'],
                'breach_type' => $breach['type'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->insert('geofence_breaches', $breachData);
        }
        
        $db->commit();
        
        $response['success'] = true;
        $response['message'] = 'Location added successfully';
        $response['data'] = [
            'log_id' => $logId,
            'geofence_breach' => $breach,
            'distance_from_previous' => $distance ?? 0
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleVisitMember($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'member_id' => 'required|integer',
        'visit_type' => 'required|in:scheduled,unscheduled',
        'purpose' => 'required|string|min:5',
        'notes' => 'string',
        'latitude' => 'numeric',
        'longitude' => 'numeric'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Verify member exists
    $member = $db->fetchOne("SELECT * FROM members WHERE id = ? AND status = 'Active'", [$input['member_id']]);
    
    if (!$member) {
        $response['message'] = 'Member not found or inactive';
        echo json_encode($response);
        return;
    }
    
    // Create visit record
    $visitData = [
        'staff_id' => $user['id'],
        'member_id' => $input['member_id'],
        'visit_type' => $input['visit_type'],
        'purpose' => $input['purpose'],
        'notes' => $input['notes'] ?? '',
        'latitude' => $input['latitude'] ?? null,
        'longitude' => $input['longitude'] ?? null,
        'visit_date' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $visitId = $db->insert('member_visits', $visitData);
    
    $response['success'] = true;
    $response['message'] = 'Visit recorded successfully';
    $response['data'] = [
        'visit_id' => $visitId,
        'member' => $member
    ];
    
    echo json_encode($response);
}

function handleCreateGeofence($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'name' => 'required|string|min:3',
        'description' => 'string',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'radius' => 'required|numeric|min:50',
        'type' => 'required|in:office,branch,member_area,restricted,safe_zone'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    $geofenceData = [
        'name' => $input['name'],
        'description' => $input['description'] ?? '',
        'latitude' => $input['latitude'],
        'longitude' => $input['longitude'],
        'radius' => $input['radius'],
        'type' => $input['type'],
        'is_active' => true,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $geofenceId = $db->insert('geofence_areas', $geofenceData);
    
    $response['success'] = true;
    $response['message'] = 'Geofence area created successfully';
    $response['data'] = ['geofence_id' => $geofenceId];
    
    echo json_encode($response);
}

function handleUpdateSession($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'tracking_id' => 'required|integer',
        'purpose' => 'string|min:5',
        'route_plan' => 'string'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Verify session ownership
    $session = $db->fetchOne(
        "SELECT * FROM gps_tracking WHERE id = ? AND staff_id = ?",
        [$input['tracking_id'], $user['id']]
    );
    
    if (!$session) {
        $response['message'] = 'Tracking session not found';
        echo json_encode($response);
        return;
    }
    
    $updateData = [
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if (isset($input['purpose'])) {
        $updateData['purpose'] = $input['purpose'];
    }
    
    if (isset($input['route_plan'])) {
        $updateData['route_plan'] = $input['route_plan'];
    }
    
    $db->update('gps_tracking', $updateData, 'id = ?', [$input['tracking_id']]);
    
    $response['success'] = true;
    $response['message'] = 'Session updated successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleUpdateGeofence($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'geofence_id' => 'required|integer',
        'name' => 'string|min:3',
        'description' => 'string',
        'radius' => 'numeric|min:50',
        'is_active' => 'boolean'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Verify geofence exists
    $geofence = $db->fetchOne("SELECT * FROM geofence_areas WHERE id = ?", [$input['geofence_id']]);
    
    if (!$geofence) {
        $response['message'] = 'Geofence not found';
        echo json_encode($response);
        return;
    }
    
    $updateData = [
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if (isset($input['name'])) {
        $updateData['name'] = $input['name'];
    }
    
    if (isset($input['description'])) {
        $updateData['description'] = $input['description'];
    }
    
    if (isset($input['radius'])) {
        $updateData['radius'] = $input['radius'];
    }
    
    if (isset($input['is_active'])) {
        $updateData['is_active'] = $input['is_active'];
    }
    
    $db->update('geofence_areas', $updateData, 'id = ?', [$input['geofence_id']]);
    
    $response['success'] = true;
    $response['message'] = 'Geofence updated successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleDeleteSession($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $sessionId = (int)($_GET['id'] ?? 0);
    
    if ($sessionId <= 0) {
        $response['message'] = 'Session ID required';
        echo json_encode($response);
        return;
    }
    
    // Verify session ownership
    $session = $db->fetchOne("SELECT * FROM gps_tracking WHERE id = ? AND staff_id = ?", [$sessionId, $user['id']]);
    
    if (!$session) {
        $response['message'] = 'Tracking session not found';
        echo json_encode($response);
        return;
    }
    
    // Soft delete session
    $db->update('gps_tracking', ['status' => 'deleted', 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$sessionId]);
    
    $response['success'] = true;
    $response['message'] = 'Tracking session deleted successfully';
    
    echo json_encode($response);
}

function handleDeleteGeofence($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $geofenceId = (int)($_GET['id'] ?? 0);
    
    if ($geofenceId <= 0) {
        $response['message'] = 'Geofence ID required';
        echo json_encode($response);
        return;
    }
    
    // Soft delete geofence
    $db->update('geofence_areas', ['is_active' => false, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$geofenceId]);
    
    $response['success'] = true;
    $response['message'] = 'Geofence deleted successfully';
    
    echo json_encode($response);
}

// Helper functions
function formatDuration($minutes) {
    if ($minutes < 60) {
        return $minutes . ' min';
    } elseif ($minutes < 1440) {
        return round($minutes / 60, 1) . ' hours';
    } else {
        return round($minutes / 1440, 1) . ' days';
    }
}

function formatLocation($latitude, $longitude) {
    return round($latitude, 6) . ', ' . round($longitude, 6);
}

function calculateSessionDuration($startTime) {
    $duration = (strtotime(date('Y-m-d H:i:s')) - strtotime($startTime)) / 60;
    return formatDuration($duration);
}

function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Earth's radius in kilometers
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}

function checkGeofenceBreach($db, $latitude, $longitude) {
    $areas = $db->fetchAll("SELECT * FROM geofence_areas WHERE is_active = 1");
    
    foreach ($areas as $area) {
        $distance = calculateDistance($area['latitude'], $area['longitude'], $latitude, $longitude);
        
        if ($distance <= $area['radius']) {
            return [
                'id' => $area['id'],
                'name' => $area['name'],
                'type' => 'entry',
                'distance' => $distance
            ];
        }
    }
    
    return null;
}
?>
