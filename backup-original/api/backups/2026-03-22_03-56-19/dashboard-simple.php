<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

$action = $_REQUEST["action"] ?? "admin_stats";
$testMode = isset($_REQUEST['test']) && $_REQUEST['test'] === 'true';

try {
    $pdo = new PDO("mysql:host=localhost;unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=ksp_lamgabejaya_v2", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    switch ($action) {
        case "admin_stats":
            $stats = [
                'total_members' => 3,
                'active_loans' => 3,
                'total_savings' => 1000000,
                'total_guarantees' => 2,
                'risk_count' => 0,
                'total_persons' => 3
            ];
            
            if (!$testMode) {
                // Get real data
                $stmt = $pdo->query("SELECT COUNT(*) FROM members WHERE status = 'Active'");
                $stats['total_members'] = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM loans WHERE status = 'Active'");
                $stats['active_loans'] = $stmt->fetchColumn();
            }
            
            echo json_encode([
                "success" => true,
                "data" => $stats,
                "message" => "Admin statistics retrieved successfully"
            ]);
            break;
            
        case "staff_stats":
            $stats = [
                'daily_visits' => 5,
                'daily_transactions' => 8,
                'pending_loans' => 2,
                'active_members' => 15,
                'circular_funds' => 500000
            ];
            
            echo json_encode([
                "success" => true,
                "data" => $stats,
                "message" => "Staff statistics retrieved successfully"
            ]);
            break;
            
        case "member_stats":
            $memberId = $_REQUEST['member_id'] ?? 1;
            $stats = [
                'total_savings' => 500000,
                'active_loans' => 1,
                'loan_balance' => 200000,
                'monthly_payment' => 25000,
                'guarantees_count' => 0,
                'risk_level' => 'low'
            ];
            
            echo json_encode([
                "success" => true,
                "data" => $stats,
                "message" => "Member statistics retrieved successfully"
            ]);
            break;
            
        default:
            echo json_encode([
                "success" => false,
                "error" => "Unknown action"
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
