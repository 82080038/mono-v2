<?php
/**
 * KSP Lam Gabe Jaya - Approval Workflow API (Phase 2)
 * Endpoints: list, get, approve, reject, create
 */

header("Access-Control-Allow-Origin: http://localhost");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../core/Middleware.php';
require_once __DIR__ . '/../core/AuditLogger.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    $user = Middleware::requireAuth();
    $pdo  = Config::getDatabase();

    $action = $_SERVER['REQUEST_METHOD'] === 'POST'
        ? (json_decode(file_get_contents('php://input'), true)['action'] ?? '')
        : ($_GET['action'] ?? '');

    switch ($action) {
        case 'list':        listApprovals($pdo, $user, $response); break;
        case 'get':         getApproval($pdo, $user, $response);   break;
        case 'approve':     actionApproval($pdo, $user, 'approved', $response); break;
        case 'reject':      actionApproval($pdo, $user, 'rejected', $response); break;
        case 'create':      createApproval($pdo, $user, $response); break;
        case 'stats':       getStats($pdo, $user, $response);       break;
        default:
            $response['message'] = 'Action tidak ditemukan';
            http_response_code(400);
    }
} catch (Exception $e) {
    $response['message'] = 'Server error: ' . $e->getMessage();
    http_response_code(500);
    error_log('[approvals.php] ' . $e->getMessage());
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// ─────────────────────────────────────────────────────────────────────────────

function listApprovals(PDO $pdo, array $user, array &$response): void {
    $status      = $_GET['status']      ?? '';
    $entityType  = $_GET['entity_type'] ?? '';
    $page        = max(1, (int)($_GET['page'] ?? 1));
    $limit       = 20;
    $offset      = ($page - 1) * $limit;

    $where  = ['1=1'];
    $params = [];

    $normalized = Middleware::normalizeRole($user['role'] ?? '');
    // Non-super-admin only see approvals for their role
    if ($normalized !== 'super_admin') {
        $where[]  = 'w.required_role = ?';
        $params[] = strtolower($user['role'] ?? '');
    }
    if ($status) { $where[] = 'w.status = ?'; $params[] = $status; }
    if ($entityType) { $where[] = 'w.entity_type = ?'; $params[] = $entityType; }

    $whereStr = implode(' AND ', $where);

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM approval_workflows w WHERE $whereStr");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT w.*,
               u_created.full_name  AS created_by_name,
               u_actioned.full_name AS actioned_by_name
        FROM approval_workflows w
        LEFT JOIN users u_created  ON w.created_by   = u_created.id
        LEFT JOIN users u_actioned ON w.actioned_by  = u_actioned.id
        WHERE $whereStr
        ORDER BY w.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $params[] = $limit; $params[] = $offset;
    $stmt->execute($params);

    $response['success'] = true;
    $response['data'] = [
        'approvals' => $stmt->fetchAll(),
        'total'     => $total,
        'page'      => $page,
        'pages'     => (int)ceil($total / $limit),
    ];
}

function getApproval(PDO $pdo, array $user, array &$response): void {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { $response['message'] = 'ID diperlukan'; http_response_code(400); return; }

    $stmt = $pdo->prepare("
        SELECT w.*,
               u_created.full_name  AS created_by_name,
               u_actioned.full_name AS actioned_by_name
        FROM approval_workflows w
        LEFT JOIN users u_created  ON w.created_by   = u_created.id
        LEFT JOIN users u_actioned ON w.actioned_by  = u_actioned.id
        WHERE w.id = ?
    ");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) { $response['message'] = 'Data tidak ditemukan'; http_response_code(404); return; }

    // Fetch referenced entity summary
    $row['entity_summary'] = fetchEntitySummary($pdo, $row['entity_type'], $row['entity_id']);

    $response['success'] = true;
    $response['data']    = $row;
}

function actionApproval(PDO $pdo, array $user, string $status, array &$response): void {
    $body = json_decode(file_get_contents('php://input'), true);
    $id   = (int)($body['id'] ?? 0);
    $note = trim($body['note'] ?? '');

    if (!$id) { $response['message'] = 'ID diperlukan'; http_response_code(400); return; }

    $stmt = $pdo->prepare("SELECT * FROM approval_workflows WHERE id = ? AND status = 'pending'");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) { $response['message'] = 'Persetujuan tidak ditemukan atau sudah diproses'; http_response_code(404); return; }

    // Role check — Super Admin can approve anything
    $normalized = Middleware::normalizeRole($user['role'] ?? '');
    if ($normalized !== 'super_admin') {
        $allowed = array_map('strtolower', [$row['required_role']]);
        $myRole  = strtolower($user['role'] ?? '');
        if (!in_array($myRole, $allowed)) {
            $response['message'] = 'Role Anda tidak berwenang memproses persetujuan ini';
            http_response_code(403); return;
        }
    }

    $userId = Middleware::getCurrentUserId();
    $upd = $pdo->prepare("
        UPDATE approval_workflows SET status=?, actioned_by=?, actioned_at=NOW(), note=? WHERE id=?
    ");
    $upd->execute([$status, $userId, $note, $id]);

    AuditLogger::log(strtoupper($status), 'approval_workflows', $id,
        ['status' => 'pending'], ['status' => $status, 'note' => $note], $userId);

    $label = $status === 'approved' ? 'disetujui' : 'ditolak';
    $response['success'] = true;
    $response['message'] = "Permohonan berhasil $label";
}

function createApproval(PDO $pdo, array $user, array &$response): void {
    Middleware::requireRole(['admin', 'super_admin', 'manager']);

    $body = json_decode(file_get_contents('php://input'), true);
    $entityType  = trim($body['entity_type']  ?? '');
    $entityId    = (int)($body['entity_id']   ?? 0);
    $level       = (int)($body['level']       ?? 1);
    $requiredRole= trim($body['required_role']?? '');

    if (!$entityType || !$entityId || !$requiredRole) {
        $response['message'] = 'entity_type, entity_id, required_role wajib diisi';
        http_response_code(400); return;
    }

    $userId = Middleware::getCurrentUserId();
    $stmt = $pdo->prepare("
        INSERT INTO approval_workflows (entity_type, entity_id, level, required_role, created_by)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$entityType, $entityId, $level, strtolower($requiredRole), $userId]);
    $newId = $pdo->lastInsertId();

    AuditLogger::log('CREATE', 'approval_workflows', $newId,
        [], ['entity_type' => $entityType, 'entity_id' => $entityId, 'required_role' => $requiredRole], $userId);

    $response['success'] = true;
    $response['message'] = 'Permohonan persetujuan berhasil dibuat';
    $response['data']    = ['id' => $newId];
}

function getStats(PDO $pdo, array $user, array &$response): void {
    $normalized = Middleware::normalizeRole($user['role'] ?? '');
    $roleFilter = $normalized !== 'super_admin'
        ? " AND required_role = '" . strtolower($user['role'] ?? '') . "'"
        : '';

    $rows = $pdo->query("
        SELECT status, entity_type, COUNT(*) AS total
        FROM approval_workflows
        WHERE 1=1 $roleFilter
        GROUP BY status, entity_type
        ORDER BY status, entity_type
    ")->fetchAll();

    $pending = $approved = $rejected = 0;
    $byType  = [];
    foreach ($rows as $r) {
        if ($r['status'] === 'pending')  $pending  += $r['total'];
        if ($r['status'] === 'approved') $approved += $r['total'];
        if ($r['status'] === 'rejected') $rejected += $r['total'];
        $byType[$r['entity_type']][$r['status']] = $r['total'];
    }

    $response['success'] = true;
    $response['data']    = compact('pending', 'approved', 'rejected', 'byType');
}

function fetchEntitySummary(PDO $pdo, string $type, int $id): array {
    try {
        switch ($type) {
            case 'loan':
                $s = $pdo->prepare("SELECT l.loan_number, m.full_name, l.amount AS loan_amount, l.status FROM loans l JOIN members m ON l.member_id=m.id WHERE l.id=?");
                $s->execute([$id]); return $s->fetch() ?: [];
            case 'member':
                $s = $pdo->prepare("SELECT member_number, full_name, status FROM members WHERE id=?");
                $s->execute([$id]); return $s->fetch() ?: [];
            case 'journal':
                $s = $pdo->prepare("SELECT journal_number, entry_date, description, status FROM journal_entries WHERE id=?");
                $s->execute([$id]); return $s->fetch() ?: [];
            case 'savings_withdrawal':
                $s = $pdo->prepare("SELECT t.id, m.full_name, t.amount, t.transaction_type FROM account_transactions t JOIN accounts a ON t.account_id=a.id JOIN members m ON a.member_id=m.id WHERE t.id=?");
                $s->execute([$id]); return $s->fetch() ?: [];
            default:
                return ['id' => $id, 'type' => $type];
        }
    } catch (Exception $e) {
        return ['id' => $id, 'type' => $type, 'error' => $e->getMessage()];
    }
}
