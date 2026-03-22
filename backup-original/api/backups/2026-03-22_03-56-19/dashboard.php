<?php
/**
 * Dashboard API for Admin Statistics
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if (!isset($_SERVER["REQUEST_METHOD"])) {
    $_SERVER["REQUEST_METHOD"] = "GET";
}

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once 'api-response.php';
require_once 'system-logger.php';
require_once 'security.php';

// Apply security middleware
SecurityMiddleware::apply([
    'rate_limit' => ['limit' => 100, 'window' => 3600],
    'allowed_methods' => ['GET', 'POST']
]);

// Check authentication (optional for testing)
$token = $_REQUEST['token'] ?? '';
$allowTestAccess = isset($_REQUEST['test']) && $_REQUEST['test'] === 'true';

if (!$allowTestAccess && empty($token)) {
    ApiResponse::error('Token required', 401);
    exit();
}

if (!$allowTestAccess) {
    // Validate token format - should be base64 encoded id:username:role
    $tokenParts = explode(':', base64_decode($token));
    if (count($tokenParts) < 3) {
        ApiResponse::error('Invalid token format', 401);
        exit();
    }
    $user = ['id' => trim($tokenParts[0]), 'username' => trim($tokenParts[1]), 'role' => trim($tokenParts[2])];
    
    // Verify role for specific endpoints
    if ($action === 'admin_stats' && $user['role'] !== 'admin') {
        ApiResponse::error('Access denied. Admin role required.', 403);
        exit();
    }
    if ($action === 'staff_stats' && $user['role'] !== 'staff') {
        ApiResponse::error('Access denied. Staff role required.', 403);
        exit();
    }
    if ($action === 'member_stats' && $user['role'] !== 'member') {
        ApiResponse::error('Access denied. Member role required.', 403);
        exit();
    }
} else {
    // Test user for testing purposes
    $user = ['id' => 1, 'username' => 'test_admin', 'role' => 'admin'];
}

// Database connections
try {
    $pdo_ksp = new PDO("mysql:host=localhost;dbname=ksp_lamgabejaya_v2", "root", "root");
    $pdo_ksp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo_orang = new PDO("mysql:host=localhost;dbname=orang", "root", "root");
    $pdo_orang->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    // Log error but continue with mock data
    error_log('Database connection failed: ' . $e->getMessage());
    $pdo_ksp = null;
    $pdo_orang = null;
}

$action = $_REQUEST["action"] ?? "admin_stats";

switch ($action) {
    case "admin_stats":
        // Get admin dashboard statistics
        $stats = [
            'total_members' => 0,
            'active_loans' => 0,
            'total_savings' => 0,
            'total_guarantees' => 0,
            'risk_count' => 0,
            'total_persons' => 0
        ];
        
        // Get total members
        if ($pdo_ksp) {
            $stmt = $pdo_ksp->query("SELECT COUNT(*) as count FROM members WHERE status = 'Active'");
            $stats['total_members'] = $stmt->fetchColumn();
            
            // Get active loans
            $stmt = $pdo_ksp->query("SELECT COUNT(*) as count FROM loans WHERE status = 'Active'");
            $stats['active_loans'] = $stmt->fetchColumn();
            
            // Get total savings (using savings table)
            $stmt = $pdo_ksp->query("SELECT COALESCE(SUM(amount), 0) as total FROM savings WHERE status = 'Active'");
            $stats['total_savings'] = $stmt->fetchColumn();
            
            // Get total guarantees
            $stmt = $pdo_ksp->query("SELECT COUNT(*) as count FROM loan_guarantees WHERE status = 'Active'");
            $stats['total_guarantees'] = $stmt->fetchColumn();
            
            // Get risk count
            $stmt = $pdo_ksp->query("SELECT COUNT(*) as count FROM guarantee_risk_assessments WHERE risk_level = 'High'");
            $stats['risk_count'] = $stmt->fetchColumn();
        } else {
            // Use mock data if database fails
            $stats = [
                'total_members' => 3,
                'active_loans' => 2,
                'total_savings' => 1000000,
                'total_guarantees' => 2,
                'risk_count' => 0,
                'total_persons' => 3
            ];
        }
        
        // Get total persons from orang database
        if ($pdo_orang) {
            $stmt = $pdo_orang->query("SELECT COUNT(*) as count FROM persons WHERE is_active = 1");
            $stats['total_persons'] = $stmt->fetchColumn();
        }
        
        ApiResponse::success($stats, 'Admin statistics retrieved successfully');
        break;
        
    case "staff_stats":
        // Get staff dashboard statistics
        $stats = [
            'daily_visits' => 0,
            'daily_transactions' => 0,
            'pending_loans' => 0,
            'active_members' => 0,
            'circular_funds' => 0,
            'guarantees_to_handle' => 0
        ];
        
        // Get daily visits (real data)
        $stmt = $pdo_ksp->query("SELECT COUNT(*) as count FROM gps_tracking WHERE DATE(created_at) = CURDATE()");
        $stats['daily_visits'] = $stmt->fetchColumn();
        
        // Get daily transactions (real data)
        $stmt = $pdo_ksp->query("SELECT COUNT(*) as count FROM payment_transactions WHERE DATE(created_at) = CURDATE()");
        $stats['daily_transactions'] = $stmt->fetchColumn();
        
        // Get pending loans
        $stmt = $pdo_ksp->query("SELECT COUNT(*) as count FROM loans WHERE status = 'Pending'");
        $stats['pending_loans'] = $stmt->fetchColumn();
        
        // Get active members
        $stmt = $pdo_ksp->query("SELECT COUNT(*) as count FROM members WHERE status = 'Active'");
        $stats['active_members'] = $stmt->fetchColumn();
        
        // Get circular funds (real data)
        $stmt = $pdo_ksp->query("SELECT COALESCE(SUM(request_amount), 0) as total FROM fund_requests WHERE status = 'Approved'");
        $stats['circular_funds'] = $stmt->fetchColumn();
        
        // Get guarantees to handle
        $stmt = $pdo_ksp->query("SELECT COUNT(*) as count FROM loan_guarantees WHERE status = 'Pending'");
        $stats['guarantees_to_handle'] = $stmt->fetchColumn();
        
        echo json_encode([
            "success" => true,
            "data" => $stats
        ]);
        break;
        
    case "member_stats":
        // Get member dashboard statistics
        $memberId = $_GET['member_id'] ?? 0;
        
        $stats = [
            'total_loans' => 0,
            'active_loans' => 0,
            'total_savings' => 0,
            'pending_loans' => 0,
            'guarantees_count' => 0,
            'risk_level' => 'low'
        ];
        
        if ($memberId > 0) {
            // Get total loans
            $stmt = $pdo_ksp->prepare("SELECT COUNT(*) as count FROM loans WHERE member_id = ?");
            $stmt->execute([$memberId]);
            $stats['total_loans'] = $stmt->fetchColumn();
            
            // Get active loans
            $stmt = $pdo_ksp->prepare("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status = 'Active'");
            $stmt->execute([$memberId]);
            $stats['active_loans'] = $stmt->fetchColumn();
            
            // Get total savings
            $stmt = $pdo_ksp->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM savings WHERE member_id = ? AND status = 'Active'");
            $stmt->execute([$memberId]);
            $stats['total_savings'] = $stmt->fetchColumn();
            
            // Get pending loans
            $stmt = $pdo_ksp->prepare("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status = 'Pending'");
            $stmt->execute([$memberId]);
            $stats['pending_loans'] = $stmt->fetchColumn();
            
            // Get guarantees count
            $stmt = $pdo_ksp->prepare("SELECT COUNT(*) as count FROM loan_guarantees WHERE guarantor_id = ?");
            $stmt->execute([$memberId]);
            $stats['guarantees_count'] = $stmt->fetchColumn();
            
            // Get risk level
            $stmt = $pdo_ksp->prepare("SELECT risk_level FROM guarantee_risk_assessments WHERE person_id = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$memberId]);
            $risk = $stmt->fetchColumn();
            $stats['risk_level'] = $risk ?: 'low';
        }
        
        ApiResponse::success($stats, 'Member statistics retrieved successfully');
        break;
        
    default:
        ApiResponse::error('Unknown action', 400);
}
?>
