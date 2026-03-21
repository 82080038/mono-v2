<?php
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

// Validate token
$user = validateToken($token);

if (!$user) {
    echo json_encode(["success" => false, "error" => "Invalid token"]);
    exit();
}

try {
    $pdo = getDatabaseConnection();
    
    $action = $_REQUEST["action"] ?? "list";
    
    switch ($action) {
        case "list":
            $stmt = $pdo->query("
                SELECT m.*, mt.name as member_type_name 
                FROM members m 
                LEFT JOIN member_types mt ON m.member_type_id = mt.id 
                ORDER BY m.created_at DESC
            ");
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "data" => $members]);
            break;
            
        case "create":
            if (!in_array($user['role'], ['Super Admin', 'Admin', 'Manager', 'Owner'])) {
                echo json_encode(["success" => false, "error" => "Access denied"]);
                break;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $memberNumber = 'M' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            
            $stmt = $pdo->prepare("
                INSERT INTO members (member_number, member_type_id, full_name, gender, id_number, 
                    phone_number, email, address, city, province, occupation, monthly_income, 
                    marital_status, registration_date, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $memberNumber,
                $data['member_type_id'],
                $data['full_name'],
                $data['gender'],
                $data['id_number'],
                $data['phone_number'],
                $data['email'],
                $data['address'],
                $data['city'],
                $data['province'],
                $data['occupation'],
                $data['monthly_income'],
                $data['marital_status'],
                $data['registration_date'] ?? date('Y-m-d'),
                $userId
            ]);
            
            echo json_encode(["success" => true, "member_id" => $pdo->lastInsertId(), "member_number" => $memberNumber]);
            break;
            
        case "update":
            if (!in_array($user['role'], ['Super Admin', 'Admin', 'Manager', 'Owner'])) {
                echo json_encode(["success" => false, "error" => "Access denied"]);
                break;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $memberId = $data['id'] ?? 0;
            
            $stmt = $pdo->prepare("
                UPDATE members SET 
                    member_type_id = ?, full_name = ?, gender = ?, id_number = ?, 
                    phone_number = ?, email = ?, address = ?, city = ?, province = ?, 
                    occupation = ?, monthly_income = ?, marital_status = ?, status = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['member_type_id'],
                $data['full_name'],
                $data['gender'],
                $data['id_number'],
                $data['phone_number'],
                $data['email'],
                $data['address'],
                $data['city'],
                $data['province'],
                $data['occupation'],
                $data['monthly_income'],
                $data['marital_status'],
                $data['status'] ?? 'Active',
                $memberId
            ]);
            
            echo json_encode(["success" => true]);
            break;
            
        case "delete":
            if (!in_array($user['role'], ['Super Admin', 'Owner'])) {
                echo json_encode(["success" => false, "error" => "Access denied"]);
                break;
            }
            
            $memberId = $_REQUEST['id'] ?? 0;
            $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
            $stmt->execute([$memberId]);
            
            echo json_encode(["success" => true]);
            break;
            
        default:
            echo json_encode(["success" => false, "error" => "Invalid action"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>