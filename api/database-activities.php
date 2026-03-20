<?php
/**
 * Database Activities API
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Database connections
$pdo_ksp = new PDO("mysql:host=localhost;unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=ksp_lamgabejaya_v2", "root", "root");
$pdo_ksp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$action = $_GET["action"] ?? "activities";

switch ($action) {
    case "activities":
        // Get recent database activities
        $activities = [];
        
        // Mock activities for now - in real implementation, this would query audit logs
        $activities = [
            [
                'timestamp' => date('Y-m-d H:i:s'),
                'database' => 'ksp_lamgabejaya_v2',
                'action' => 'INSERT',
                'table' => 'members',
                'status' => 'Success',
                'details' => 'New member added'
            ],
            [
                'timestamp' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
                'database' => 'orang',
                'action' => 'UPDATE',
                'table' => 'persons',
                'status' => 'Success',
                'details' => 'Person information updated'
            ],
            [
                'timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'database' => 'ksp_lamgabejaya_v2',
                'action' => 'INSERT',
                'table' => 'loan_guarantees',
                'status' => 'Success',
                'details' => 'New guarantee created'
            ],
            [
                'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'database' => 'alamat_db',
                'action' => 'SELECT',
                'table' => 'villages',
                'status' => 'Success',
                'details' => 'Address validation performed'
            ],
            [
                'timestamp' => date('Y-m-d H:i:s', strtotime('-3 hours')),
                'database' => 'ksp_lamgabejaya_v2',
                'action' => 'UPDATE',
                'table' => 'loans',
                'status' => 'Success',
                'details' => 'Loan status updated'
            ],
            [
                'timestamp' => date('Y-m-d H:i:s', strtotime('-4 hours')),
                'database' => 'orang',
                'action' => 'INSERT',
                'table' => 'person_addresses',
                'status' => 'Success',
                'details' => 'New address added'
            ],
            [
                'timestamp' => date('Y-m-d H:i:s', strtotime('-5 hours')),
                'database' => 'ksp_lamgabejaya_v2',
                'action' => 'DELETE',
                'table' => 'guarantee_risk_assessments',
                'status' => 'Success',
                'details' => 'Risk assessment removed'
            ],
            [
                'timestamp' => date('Y-m-d H:i:s', strtotime('-6 hours')),
                'database' => 'alamat_db',
                'action' => 'SELECT',
                'table' => 'provinces',
                'status' => 'Success',
                'details' => 'Province data accessed'
            ]
        ];
        
        echo json_encode([
            "success" => true,
            "data" => $activities
        ]);
        break;
        
    case "log":
        // Log database activity
        $database = $_POST['database'] ?? '';
        $action_type = $_POST['action'] ?? '';
        $table = $_POST['table'] ?? '';
        $status = $_POST['status'] ?? 'Success';
        $details = $_POST['details'] ?? '';
        
        // In real implementation, this would insert into an audit log table
        echo json_encode([
            "success" => true,
            "message" => "Activity logged successfully"
        ]);
        break;
        
    default:
        echo json_encode([
            "success" => false,
            "error" => "Unknown action"
        ]);
}
?>
