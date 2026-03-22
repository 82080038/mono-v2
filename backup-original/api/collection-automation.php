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

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

// Check authentication
$token = $_REQUEST['token'] ?? '';
if (empty($token)) {
    SecurityMiddleware::sendJSONResponse($response);
    exit();
}

$user = validateToken($token);
if (!$user) {
    SecurityMiddleware::sendJSONResponse($response);
    exit();
}

try {
    $pdo = getDatabaseConnection();
    
    $action = $_REQUEST["action"] ?? "list";
    
    switch ($action) {
        case "create_collection_schedule":
            if (!in_array($user['role'], ['Super Admin', 'Admin', 'Manager', 'Owner'])) {
                SecurityMiddleware::sendJSONResponse($response);
                break;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                INSERT INTO collection_schedules (member_id, staff_id, collection_date, 
                    expected_amount, collection_type, status, notes, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['member_id'],
                $data['staff_id'],
                $data['collection_date'],
                $data['expected_amount'],
                $data['collection_type'],
                $data['status'] ?? 'scheduled',
                $data['notes'] ?? null,
                $user['id']
            ]);
            
            echo json_encode([
                "success" => true, 
                "schedule_id" => $pdo->lastInsertId()
            ]);
            break;
            
        case "get_staff_routes":
            $staffId = $_REQUEST['staff_id'] ?? $user['id'];
            $date = $_REQUEST['date'] ?? date('Y-m-d');
            
            $stmt = $pdo->prepare("
                SELECT cs.*, m.full_name as member_name, m.address, m.phone_number,
                       m.latitude, m.longitude, l.loan_number, l.monthly_installment
                FROM collection_schedules cs
                LEFT JOIN members m ON cs.member_id = m.id
                LEFT JOIN loans l ON cs.member_id = l.member_id AND l.status = 'Active'
                WHERE cs.staff_id = ? AND cs.collection_date = ? AND cs.status != 'cancelled'
                ORDER BY cs.priority ASC, cs.collection_time ASC
            ");
            $stmt->execute([$staffId, $date]);
            $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Optimize route using simple distance calculation
            $optimizedRoutes = optimizeCollectionRoute($routes);
            
            echo json_encode([
                "success" => true,
                "routes" => $optimizedRoutes,
                "total_collections" => count($optimizedRoutes),
                "total_expected" => array_sum(array_column($optimizedRoutes, 'expected_amount'))
            ]);
            break;
            
        case "update_collection_status":
            $scheduleId = $_REQUEST['schedule_id'] ?? 0;
            $status = $_REQUEST['status'] ?? '';
            $actualAmount = $_REQUEST['actual_amount'] ?? 0;
            $notes = $_REQUEST['notes'] ?? '';
            $latitude = $_REQUEST['latitude'] ?? null;
            $longitude = $_REQUEST['longitude'] ?? null;
            
            $stmt = $pdo->prepare("
                UPDATE collection_schedules 
                SET status = ?, actual_amount = ?, notes = ?, 
                    collected_at = NOW(), latitude = ?, longitude = ?
                WHERE id = ? AND staff_id = ?
            ");
            
            $stmt->execute([$status, $actualAmount, $notes, $latitude, $longitude, $scheduleId, $user['id']]);
            
            // If collected, create payment record
            if ($status === 'collected' && $actualAmount > 0) {
                $stmt = $pdo->prepare("SELECT member_id FROM collection_schedules WHERE id = ?");
                $stmt->execute([$scheduleId]);
                $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stmt = $pdo->prepare("
                    INSERT INTO payments (member_id, payment_type, amount, payment_method, 
                        status, created_by, payment_date, notes) 
                    VALUES (?, 'loan_payment', ?, 'cash', 'completed', ?, NOW(), ?)
                ");
                $stmt->execute([
                    $schedule['member_id'],
                    $actualAmount,
                    $user['id'],
                    "Collection from schedule #$scheduleId"
                ]);
            }
            
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        case "get_staff_performance":
            $staffId = $_REQUEST['staff_id'] ?? $user['id'];
            $startDate = $_REQUEST['start_date'] ?? date('Y-m-01');
            $endDate = $_REQUEST['end_date'] ?? date('Y-m-t');
            
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_schedules,
                    SUM(CASE WHEN status = 'collected' THEN 1 ELSE 0 END) as collected,
                    SUM(CASE WHEN status = 'missed' THEN 1 ELSE 0 END) as missed,
                    SUM(CASE WHEN status = 'postponed' THEN 1 ELSE 0 END) as postponed,
                    SUM(expected_amount) as total_expected,
                    SUM(actual_amount) as total_collected,
                    ROUND((SUM(actual_amount) / SUM(expected_amount)) * 100, 2) as collection_rate
                FROM collection_schedules 
                WHERE staff_id = ? AND collection_date BETWEEN ? AND ?
            ");
            $stmt->execute([$staffId, $startDate, $endDate]);
            $performance = $stmt->fetch(PDO::FETCH_ASSOC);
            
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        case "generate_daily_schedule":
            if (!in_array($user['role'], ['Super Admin', 'Admin', 'Manager', 'Owner'])) {
                SecurityMiddleware::sendJSONResponse($response);
                break;
            }
            
            $date = $_REQUEST['date'] ?? date('Y-m-d');
            
            // Get all active loans with payment schedules
            $stmt = $pdo->prepare("
                SELECT DISTINCT l.member_id, l.monthly_installment, m.full_name, m.address,
                       m.latitude, m.longitude, m.phone_number
                FROM loans l
                LEFT JOIN members m ON l.member_id = m.id
                WHERE l.status = 'Active' AND l.next_payment_date <= ?
                ORDER BY m.latitude, m.longitude
            ");
            $stmt->execute([$date]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Assign to available staff
            $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE role IN ('Staff', 'Teller') AND is_active = 1");
            $stmt->execute();
            $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $schedules = [];
            $staffIndex = 0;
            
            foreach ($members as $member) {
                $assignedStaff = $staff[$staffIndex % count($staff)];
                
                $stmt = $pdo->prepare("
                    INSERT INTO collection_schedules (member_id, staff_id, collection_date, 
                        expected_amount, collection_type, status, priority, created_by)
                    VALUES (?, ?, ?, ?, 'loan_payment', 'scheduled', 1, ?)
                ");
                
                $stmt->execute([
                    $member['member_id'],
                    $assignedStaff['id'],
                    $date,
                    $member['monthly_installment'],
                    $user['id']
                ]);
                
                $schedules[] = [
                    'member_name' => $member['full_name'],
                    'staff_name' => $assignedStaff['full_name'],
                    'expected_amount' => $member['monthly_installment']
                ];
                
                $staffIndex++;
            }
            
            echo json_encode([
                "success" => true,
                "schedules_created" => count($schedules),
                "date" => $date,
                "preview" => array_slice($schedules, 0, 10)
            ]);
            break;
            
        case "get_collection_summary":
            $date = $_REQUEST['date'] ?? date('Y-m-d');
            
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_schedules,
                    SUM(CASE WHEN status = 'collected' THEN 1 ELSE 0 END) as collected,
                    SUM(CASE WHEN status = 'missed' THEN 1 ELSE 0 END) as missed,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(expected_amount) as total_expected,
                    SUM(actual_amount) as total_collected,
                    COUNT(DISTINCT staff_id) as active_staff
                FROM collection_schedules 
                WHERE collection_date = ?
            ");
            $stmt->execute([$date]);
            $summary = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                "success" => true,
                "summary" => $summary,
                "collection_rate" => $summary['total_expected'] > 0 ? 
                    round(($summary['total_collected'] / $summary['total_expected']) * 100, 2) : 0
            ]);
            break;
            
        default:
            SecurityMiddleware::sendJSONResponse($response);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

// Helper function to optimize collection route
function optimizeCollectionRoute($routes) {
    if (empty($routes)) return $routes;
    
    $optimized = [];
    $remaining = $routes;
    $currentLat = null;
    $currentLng = null;
    
    while (!empty($remaining)) {
        $nearest = null;
        $nearestDistance = PHP_FLOAT_MAX;
        $nearestIndex = -1;
        
        foreach ($remaining as $index => $route) {
            if ($currentLat === null || $currentLng === null) {
                // First location
                $distance = 0;
            } else {
                // Calculate distance from current location
                $distance = calculateDistance(
                    $currentLat, $currentLng,
                    $route['latitude'], $route['longitude']
                );
            }
            
            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearest = $route;
                $nearestIndex = $index;
            }
        }
        
        if ($nearest) {
            $optimized[] = $nearest;
            $currentLat = $nearest['latitude'];
            $currentLng = $nearest['longitude'];
            unset($remaining[$nearestIndex]);
            $remaining = array_values($remaining);
        }
    }
    
    return $optimized;
}

function calculateDistance($lat1, $lng1, $lat2, $lng2) {
    if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) {
        return 0;
    }
    
    $earthRadius = 6371; // Earth's radius in kilometers
    
    $dLat = deg2rad_custom($lat2 - $lat1);
    $dLng = deg2rad_custom($lng2 - $lng1);
    
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad_custom($lat1)) * cos(deg2rad_custom($lat2)) *
         sin($dLng/2) * sin($dLng/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}

function deg2rad_custom($deg) {
    return $deg * (M_PI/180);
}
?>
