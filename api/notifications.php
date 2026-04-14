<?php
/**
 * KSP Lam Gabe Jaya — Notifications API (Phase 3)
 * Polling-based: GET list, mark read, mark all read, get unread count
 */

header("Access-Control-Allow-Origin: http://localhost");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../core/Middleware.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    $user = Middleware::requireAuth();
    $pdo  = Config::getDatabase();
    $userId = Middleware::getCurrentUserId();

    $action = $_SERVER['REQUEST_METHOD'] === 'POST'
        ? (json_decode(file_get_contents('php://input'), true)['action'] ?? '')
        : ($_GET['action'] ?? 'list');

    switch ($action) {
        case 'list':         listNotifications($pdo, $userId, $response); break;
        case 'unread_count': unreadCount($pdo, $userId, $response);       break;
        case 'mark_read':    markRead($pdo, $userId, $response);           break;
        case 'mark_all':     markAll($pdo, $userId, $response);            break;
        case 'poll':         pollUpdates($pdo, $userId, $user, $response); break;
        default:
            $response['message'] = 'Action tidak ditemukan';
            http_response_code(400);
    }
} catch (Exception $e) {
    $response['message'] = 'Server error: ' . $e->getMessage();
    http_response_code(500);
    error_log('[notifications.php] ' . $e->getMessage());
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// ─────────────────────────────────────────────────────────────────────────────

function listNotifications(PDO $pdo, int $userId, array &$response): void {
    $limit  = min(50, (int)($_GET['limit'] ?? 20));
    $onlyUnread = ($_GET['unread_only'] ?? '') === '1';

    $where = '(n.user_id = ? OR n.user_id IS NULL)';
    $params = [$userId];
    if ($onlyUnread) { $where .= ' AND n.is_read = 0'; }

    $stmt = $pdo->prepare("
        SELECT n.*, u.full_name AS sender_name
        FROM notifications n
        LEFT JOIN users u ON n.entity_id = u.id AND n.entity_type = 'user'
        WHERE $where
        ORDER BY n.created_at DESC
        LIMIT ?
    ");
    $params[] = $limit;
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $countStmt = $pdo->prepare("
        SELECT COUNT(*) FROM notifications WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0
    ");
    $countStmt->execute([$userId]);
    $unread = (int)$countStmt->fetchColumn();

    $response['success'] = true;
    $response['data']    = ['notifications' => $rows, 'unread_count' => $unread];
}

function unreadCount(PDO $pdo, int $userId, array &$response): void {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM notifications WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0
    ");
    $stmt->execute([$userId]);
    $response['success'] = true;
    $response['data']    = ['count' => (int)$stmt->fetchColumn()];
}

function markRead(PDO $pdo, int $userId, array &$response): void {
    $body = json_decode(file_get_contents('php://input'), true);
    $id   = (int)($body['id'] ?? 0);
    if (!$id) { $response['message'] = 'ID diperlukan'; http_response_code(400); return; }

    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND (user_id=? OR user_id IS NULL)")
        ->execute([$id, $userId]);
    $response['success'] = true;
    $response['message'] = 'Notifikasi ditandai sudah dibaca';
}

function markAll(PDO $pdo, int $userId, array &$response): void {
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE (user_id=? OR user_id IS NULL)")
        ->execute([$userId]);
    $response['success'] = true;
    $response['message'] = 'Semua notifikasi ditandai sudah dibaca';
}

/**
 * poll() — cek perubahan terbaru: pending approvals, new loans, dll.
 * Digunakan oleh frontend untuk polling setiap 30 detik.
 */
function pollUpdates(PDO $pdo, int $userId, array $user, array &$response): void {
    $normalized = Middleware::normalizeRole($user['role'] ?? '');

    // Auto-create notif untuk approval pending baru (dalam 5 menit terakhir)
    if (in_array($normalized, ['super_admin', 'admin', 'manager'])) {
        $recentPending = $pdo->query("
            SELECT COUNT(*) FROM approval_workflows
            WHERE status='pending' AND created_at >= NOW() - INTERVAL 5 MINUTE
        ")->fetchColumn();

        if ($recentPending > 0) {
            // Cek apakah notif sudah ada agar tidak duplikat
            $existing = $pdo->prepare("
                SELECT id FROM notifications
                WHERE type='approval_pending' AND is_read=0
                  AND created_at >= NOW() - INTERVAL 5 MINUTE
                  AND (user_id=? OR user_id IS NULL)
                LIMIT 1
            ");
            $existing->execute([$userId]);
            if (!$existing->fetch()) {
                $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link, entity_type)
                    VALUES (NULL, 'approval_pending', 'Persetujuan Baru Masuk',
                            ?, 'approval-workflow.html', 'approval_workflows')
                ")->execute(["$recentPending permohonan menunggu persetujuan"]);
            }
        }

        // Cek pinjaman applied baru
        $newLoans = $pdo->query("
            SELECT COUNT(*) FROM loans
            WHERE status='Applied' AND created_at >= NOW() - INTERVAL 5 MINUTE
        ")->fetchColumn();
        if ($newLoans > 0) {
            $existing2 = $pdo->prepare("
                SELECT id FROM notifications
                WHERE type='loan_applied' AND created_at >= NOW() - INTERVAL 5 MINUTE
                LIMIT 1
            ");
            $existing2->execute();
            if (!$existing2->fetch()) {
                $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link, entity_type)
                    VALUES (NULL, 'loan_applied', 'Pengajuan Pinjaman Baru',
                            ?, 'loans.html', 'loans')
                ")->execute(["$newLoans pengajuan pinjaman baru perlu ditinjau"]);
            }
        }
    }

    // Kembalikan unread count + notif terbaru
    $stmt = $pdo->prepare("
        SELECT * FROM notifications
        WHERE (user_id=? OR user_id IS NULL) AND is_read=0
        ORDER BY created_at DESC LIMIT 10
    ");
    $stmt->execute([$userId]);
    $unread = $stmt->fetchAll();

    $response['success'] = true;
    $response['data']    = [
        'unread_count'  => count($unread),
        'notifications' => $unread,
        'timestamp'     => date('c'),
    ];
}

// ─── Helper untuk dipanggil dari API lain ───────────────────────────────────
function createNotification(PDO $pdo, ?int $userId, string $type, string $title, string $message, string $link = '', string $entityType = '', int $entityId = 0): void {
    try {
        $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, link, entity_type, entity_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([$userId, $type, $title, $message, $link, $entityType ?: null, $entityId ?: null]);
    } catch (Exception $e) {
        error_log('[notifications] create failed: ' . $e->getMessage());
    }
}
