<?php
/**
 * KSP Lam Gabe Jaya - Audit Log API (Phase 2)
 */

header("Access-Control-Allow-Origin: http://localhost");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../core/Middleware.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    Middleware::requireAuth();
    Middleware::requireRole(['admin']);

    $pdo = Config::getDatabase();

    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'get_audit_logs':
            getAuditLogs($pdo, $response);
            break;
        default:
            $response['message'] = 'Action tidak ditemukan';
            http_response_code(400);
    }
} catch (Exception $e) {
    $response['message'] = 'Server error: ' . $e->getMessage();
    http_response_code(500);
    error_log('[audit.php] ' . $e->getMessage());
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

function getAuditLogs($pdo, &$response) {
    $dateFrom     = $_GET['date_from']     ?? date('Y-m-d', strtotime('-7 days'));
    $dateTo       = $_GET['date_to']       ?? date('Y-m-d');
    $actionFilter = strtoupper($_GET['action_filter'] ?? '');
    $tableName    = trim($_GET['table_name'] ?? '');
    $page         = max(1, (int)($_GET['page'] ?? 1));
    $limit        = 50;
    $offset       = ($page - 1) * $limit;

    $where  = ["DATE(a.created_at) BETWEEN ? AND ?"];
    $params = [$dateFrom, $dateTo];

    if ($actionFilter) {
        $where[] = 'a.action = ?';
        $params[] = $actionFilter;
    }
    if ($tableName) {
        $where[] = 'a.table_name LIKE ?';
        $params[] = '%' . $tableName . '%';
    }

    $whereClause = implode(' AND ', $where);

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs a WHERE $whereClause");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT a.*, u.full_name AS user_name
        FROM audit_logs a
        LEFT JOIN users u ON a.user_id = u.id
        WHERE $whereClause
        ORDER BY a.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);

    $response['success'] = true;
    $response['data'] = [
        'logs'  => $stmt->fetchAll(),
        'total' => $total,
        'page'  => $page,
        'pages' => (int)ceil($total / $limit),
    ];
}
