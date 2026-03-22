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
                SELECT l.*, m.full_name, m.member_number, lt.name as loan_type_name 
                FROM loans l 
                LEFT JOIN members m ON l.member_id = m.id 
                LEFT JOIN loan_types lt ON l.loan_type_id = lt.id 
                ORDER BY l.created_at DESC
            ");
            $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "data" => $loans]);
            break;
            
        case "create":
            if (!in_array($user['role'], ['Super Admin', 'Admin', 'Manager', 'Owner'])) {
                echo json_encode(["success" => false, "error" => "Access denied"]);
                break;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $loanNumber = 'L' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            
            $stmt = $pdo->prepare("
                INSERT INTO loans (loan_number, member_id, loan_type_id, application_date, amount, interest_rate, 
                    term_months, calculation_method, monthly_installment, total_interest, total_payment, 
                    outstanding_balance, purpose, status, approved_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $loanNumber,
                $data['member_id'],
                $data['loan_type_id'],
                $data['application_date'],
                $data['amount'],
                $data['interest_rate'],
                $data['term_months'],
                $data['calculation_method'],
                $data['monthly_installment'],
                $data['total_interest'],
                $data['total_payment'],
                $data['total_payment'],
                $data['purpose'],
                $data['status'] ?? 'Applied',
                $userId
            ]);
            
            echo json_encode(["success" => true, "loan_id" => $pdo->lastInsertId(), "loan_number" => $loanNumber]);
            break;
            
        case "update":
            if (!in_array($user['role'], ['Super Admin', 'Admin', 'Manager', 'Owner'])) {
                echo json_encode(["success" => false, "error" => "Access denied"]);
                break;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $loanId = $data['id'] ?? 0;
            
            $stmt = $pdo->prepare("
                UPDATE loans SET 
                    member_id = ?, loan_type_id = ?, amount = ?, interest_rate = ?, 
                    term_months = ?, calculation_method = ?, monthly_installment = ?, 
                    total_interest = ?, total_payment = ?, purpose = ?, status = ?,
                    approval_date = ?, approved_by = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['member_id'],
                $data['loan_type_id'],
                $data['amount'],
                $data['interest_rate'],
                $data['term_months'],
                $data['calculation_method'],
                $data['monthly_installment'],
                $data['total_interest'],
                $data['total_payment'],
                $data['purpose'],
                $data['status'],
                $data['status'] === 'Approved' ? date('Y-m-d') : null,
                $userId,
                $loanId
            ]);
            
            echo json_encode(["success" => true]);
            break;
            
        case "delete":
            if (!in_array($user['role'], ['Super Admin'])) {
                echo json_encode(["success" => false, "error" => "Access denied"]);
                break;
            }
            
            $loanId = $_REQUEST['id'] ?? 0;
            $stmt = $pdo->prepare("DELETE FROM loans WHERE id = ?");
            $stmt->execute([$loanId]);
            
            echo json_encode(["success" => true]);
            break;
            
        case "approve":
            if (!in_array($user['role'], ['Super Admin', 'Admin', 'Manager', 'Owner'])) {
                echo json_encode(["success" => false, "error" => "Access denied"]);
                break;
            }
            
            $loanId = $_REQUEST['id'] ?? 0;
            $stmt = $pdo->prepare("
                UPDATE loans SET 
                    status = 'Approved', 
                    approval_date = ?, 
                    approved_by = ?
                WHERE id = ?
            ");
            
            $stmt->execute([date('Y-m-d'), $userId, $loanId]);
            
            echo json_encode(["success" => true]);
            break;
            
        default:
            echo json_encode(["success" => false, "error" => "Invalid action"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>