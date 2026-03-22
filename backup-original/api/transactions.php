<?php
/**
 * Transactions API
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
        // Get transactions list
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        
        // Get total count
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM payment_transactions");
        $stmt->execute();
        $total = $stmt->fetchColumn();
        
        // Get transactions
        $stmt = $db->prepare("SELECT * FROM payment_transactions ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $transactions,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
        break;
        
    case "get":
        // Get single transaction
        $id = $_GET['id'] ?? 0;
        if ($id == 0) {
            echo json_encode(['success' => false, 'message' => 'Transaction ID required']);
            exit();
        }
        
        $stmt = $db->prepare("SELECT * FROM payment_transactions WHERE id = ?");
        $stmt->execute([$id]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($transaction) {
            echo json_encode(['success' => true, 'data' => $transaction]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Transaction not found']);
        }
        break;
        
    case "create":
        // Create new transaction
        $data = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $db->prepare("INSERT INTO payment_transactions (member_id, amount, type, status, created_at) VALUES (?, ?, ?, ?, NOW())");
        $result = $stmt->execute([
            $data['member_id'],
            $data['amount'],
            $data['type'],
            $data['status']
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Transaction created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create transaction']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
?>
