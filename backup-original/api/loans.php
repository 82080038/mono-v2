<?php
/**
 * Loans API
 */

require_once __DIR__ . '/config/error-config.php';

define('KSP_API_ACCESS', true);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once 'DatabaseHelper.php';
require_once 'AuthHelper.php';
require_once 'SecurityHelper.php';

$action = $_REQUEST["action"] ?? "list";

try {
    $db = DatabaseHelper::getInstance();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

switch ($action) {
    case "list":
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        
        // Get total count
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM loans");
        $stmt->execute();
        $total = $stmt->fetchColumn();
        
        // Get loans
        $stmt = $db->prepare("SELECT l.*, m.full_name as member_name FROM loans l LEFT JOIN members m ON l.member_id = m.id ORDER BY l.created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $loans,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
        break;
        
    case "get":
        // Get single loan
        $id = $_GET['id'] ?? 0;
        if ($id == 0) {
            echo json_encode(['success' => false, 'message' => 'Loan ID required']);
            exit();
        }
        
        $stmt = $db->prepare("SELECT l.*, m.full_name as member_name FROM loans l LEFT JOIN members m ON l.member_id = m.id WHERE l.id = ?");
        $stmt->execute([$id]);
        $loan = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($loan) {
            echo json_encode(['success' => true, 'data' => $loan]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Loan not found']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
?>
