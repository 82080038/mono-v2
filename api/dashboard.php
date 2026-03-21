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

require_once 'auth_helper.php';

// Check authentication
$token = $_REQUEST['token'] ?? '';
if (empty($token)) {
    echo json_encode(["success" => false, "error" => "Token required"]);
    exit();
}

$user = validateToken($token);
if (!$user) {
    echo json_encode(["success" => false, "error" => "Invalid token"]);
    exit();
}

// Database connections
$pdo_ksp = getDatabaseConnection();
$pdo_orang = new PDO("mysql:host=localhost;unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=orang", "root", "root");
$pdo_orang->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
        $stmt = $pdo_ksp->query("SELECT COUNT(*) as count FROM members WHERE status = 'Active'");
        $stats['total_members'] = $stmt->fetchColumn();
        
        // Get active loans
        $stmt = $pdo_ksp->query("SELECT COUNT(*) as count FROM loans WHERE status = 'Active'");
        $stats['active_loans'] = $stmt->fetchColumn();
        
        // Get total savings (using payments table as proxy)
        $stmt = $pdo_ksp->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE type = 'deposit' AND status = 'paid'");
        $stats['total_savings'] = $stmt->fetchColumn();
        
        // Get total guarantees (using total loans count as proxy)
        $stmt = $pdo_ksp->query("SELECT COUNT(*) as count FROM loans WHERE status = 'Active'");
        $stats['total_guarantees'] = $stmt->fetchColumn();
        
        // Get high risk count (using loans with high risk indicators)
        $stmt = $pdo_ksp->query("SELECT COUNT(*) as count FROM loans WHERE status = 'Default'");
        $stats['risk_count'] = $stmt->fetchColumn();
        
        // Get total persons from orang database
        $stmt = $pdo_orang->query("SELECT COUNT(*) as count FROM persons WHERE is_active = 1");
        $stats['total_persons'] = $stmt->fetchColumn();
        
        echo json_encode([
            "success" => true,
            "data" => $stats
        ]);
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
        
        echo json_encode([
            "success" => true,
            "data" => $stats
        ]);
        break;
        
    default:
        echo json_encode([
            "success" => false,
            "error" => "Unknown action"
        ]);
}
?>
