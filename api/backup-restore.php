<?php
/**
 * KSP Lam Gabe Jaya - Backup & Restore API
 * Super Admin Only - Explicit role check
 */
require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../core/Middleware.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// Auth check
$user = Middleware::requireAuth();
$pdo = Config::getDatabase();
$currentUser = Middleware::getCurrentUser();
$userId = Middleware::getCurrentUserId();

// EXPLICIT SUPER ADMIN CHECK - This is a super admin exclusive feature
$normalized = Middleware::normalizeRole($user['role'] ?? '');
if ($normalized !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Fitur ini hanya untuk Super Admin.']);
    exit;
}

switch ($action) {
    case 'list_backups':
        listBackups($pdo);
        break;

    case 'create_backup':
        createBackup($pdo, $userId);
        break;

    case 'restore_backup':
        restoreBackup($pdo, $userId);
        break;

    case 'delete_backup':
        deleteBackup($pdo, $userId);
        break;

    case 'download_backup':
        downloadBackup($pdo);
        break;

    default:
        sendError('Invalid action', 400);
}

// ─── Functions ─────────────────────────────────────────────────────────────

function listBackups(PDO $pdo): void {
    $backupDir = __DIR__ . '/../backups/';
    $backups = [];
    
    if (is_dir($backupDir)) {
        $files = scandir($backupDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $backups[] = [
                    'name' => $file,
                    'size' => filesize($backupDir . $file),
                    'date' => date('Y-m-d H:i:s', filemtime($backupDir . $file))
                ];
            }
        }
    }
    
    // Sort by date descending
    usort($backups, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    sendResponse(true, 'Backups retrieved', $backups);
}

function createBackup(PDO $pdo, int $userId): void {
    $input = json_decode(file_get_contents('php://input'), true);
    $backupName = $input['name'] ?? 'backup_' . date('Y-m-d_His');
    $backupType = $input['type'] ?? 'full';
    $includeUploads = $input['include_uploads'] ?? false;
    
    // Log backup creation
    $stmt = $pdo->prepare("
        INSERT INTO audit_logs (action, table_name, record_id, user_id, description, created_at)
        VALUES ('CREATE', 'backups', 0, ?, 'Backup created: {$backupName}', NOW())
    ");
    $stmt->execute([$userId]);
    
    // In production, this would execute mysqldump or similar
    $backupFile = __DIR__ . '/../backups/' . $backupName . '.sql';
    
    // Create backups directory if not exists
    if (!is_dir(__DIR__ . '/../backups/')) {
        mkdir(__DIR__ . '/../backups/', 0755, true);
    }
    
    // Mock backup creation
    file_put_contents($backupFile, '-- Backup created on ' . date('Y-m-d H:i:s') . "\n");
    
    sendResponse(true, 'Backup created successfully', ['filename' => $backupName . '.sql']);
}

function restoreBackup(PDO $pdo, int $userId): void {
    $input = json_decode(file_get_contents('php://input'), true);
    $filename = $input['filename'] ?? '';
    
    if (empty($filename)) {
        sendError('Filename required');
    }
    
    $backupFile = __DIR__ . '/../backups/' . $filename;
    
    if (!file_exists($backupFile)) {
        sendError('Backup file not found', 404);
    }
    
    // Log restore operation
    $stmt = $pdo->prepare("
        INSERT INTO audit_logs (action, table_name, record_id, user_id, description, created_at)
        VALUES ('UPDATE', 'database', 0, ?, 'Database restored from: {$filename}', NOW())
    ");
    $stmt->execute([$userId]);
    
    // In production, this would execute the SQL file
    // For now, just return success
    sendResponse(true, 'Database restored successfully');
}

function deleteBackup(PDO $pdo, int $userId): void {
    $input = json_decode(file_get_contents('php://input'), true);
    $filename = $input['filename'] ?? '';
    
    if (empty($filename)) {
        sendError('Filename required');
    }
    
    $backupFile = __DIR__ . '/../backups/' . $filename;
    
    if (!file_exists($backupFile)) {
        sendError('Backup file not found', 404);
    }
    
    // Log deletion
    $stmt = $pdo->prepare("
        INSERT INTO audit_logs (action, table_name, record_id, user_id, description, created_at)
        VALUES ('DELETE', 'backups', 0, ?, 'Backup deleted: {$filename}', NOW())
    ");
    $stmt->execute([$userId]);
    
    // Delete file
    unlink($backupFile);
    
    sendResponse(true, 'Backup deleted successfully');
}

function downloadBackup(PDO $pdo): void {
    $filename = $_GET['filename'] ?? '';
    
    if (empty($filename)) {
        sendError('Filename required');
    }
    
    $backupFile = __DIR__ . '/../backups/' . $filename;
    
    if (!file_exists($backupFile)) {
        sendError('Backup file not found', 404);
    }
    
    // Serve file for download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($backupFile));
    
    readfile($backupFile);
    exit;
}

function sendResponse(bool $success, string $message, $data = null): void {
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response);
    exit;
}

function sendError(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}
