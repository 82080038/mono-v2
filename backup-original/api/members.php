<?php
/**
 * Members API
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
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM members");
        $stmt->execute();
        $total = $stmt->fetchColumn();
        
        // Get members
        $stmt = $db->prepare("SELECT * FROM members ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $members,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
        break;
        
    case "get":
        // Get single member
        $id = $_GET['id'] ?? 0;
        if ($id == 0) {
            echo json_encode(['success' => false, 'message' => 'Member ID required']);
            exit();
        }
        
        $stmt = $db->prepare("SELECT * FROM members WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($member) {
            echo json_encode(['success' => true, 'data' => $member]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Member not found']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
?>
