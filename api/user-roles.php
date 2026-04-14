<?php
/**
 * KSP Lam Gabe Jaya - User Roles API
 * Dynamic field staff role management (many-to-many)
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

// Role check: only admin, super admin, manager can manage roles
Middleware::requireRole(['admin', 'super_admin', 'manager']);

switch ($action) {
    case 'list_roles':
        // List all available roles
        listRoles($pdo);
        break;

    case 'get_user_roles':
        // Get roles for a specific user
        $targetUserId = (int)($_GET['user_id'] ?? 0);
        if (!$targetUserId) sendError('user_id required');
        getUserRoles($pdo, $targetUserId);
        break;

    case 'assign_role':
        // Assign role to user
        $input = json_decode(file_get_contents('php://input'), true);
        assignRole($pdo, $input, $userId);
        break;

    case 'remove_role':
        // Remove role from user
        $input = json_decode(file_get_contents('php://input'), true);
        removeRole($pdo, $input, $userId);
        break;

    case 'list_users_with_roles':
        // List users with their roles
        listUsersWithRoles($pdo);
        break;

    default:
        sendError('Invalid action', 400);
}

// ─── Functions ─────────────────────────────────────────────────────────────

function listRoles(PDO $pdo): void {
    $stmt = $pdo->query("
        SELECT id, role_code, role_name, category, description, is_active
        FROM roles
        ORDER BY category, role_name
    ");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(true, 'Roles retrieved', $roles);
}

function getUserRoles(PDO $pdo, int $targetUserId): void {
    // Get main role from users table
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$targetUserId]);
    $mainRole = $stmt->fetchColumn();

    // Get additional roles from user_roles
    $stmt = $pdo->prepare("
        SELECT r.id, r.role_code, r.role_name, r.category, ur.assigned_at,
               u.full_name AS assigned_by_name
        FROM user_roles ur
        JOIN roles r ON r.id = ur.role_id
        LEFT JOIN users u ON u.id = ur.assigned_by
        WHERE ur.user_id = ? AND ur.is_active = 1
        ORDER BY r.role_name
    ");
    $stmt->execute([$targetUserId]);
    $additionalRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(true, 'User roles retrieved', [
        'user_id' => $targetUserId,
        'main_role' => $mainRole,
        'additional_roles' => $additionalRoles
    ]);
}

function assignRole(PDO $pdo, array $input, int $assignedBy): void {
    $targetUserId = (int)($input['user_id'] ?? 0);
    $roleId = (int)($input['role_id'] ?? 0);

    if (!$targetUserId || !$roleId) {
        sendError('user_id and role_id required');
    }

    // Check if role already assigned
    $stmt = $pdo->prepare("
        SELECT id FROM user_roles 
        WHERE user_id = ? AND role_id = ?
    ");
    $stmt->execute([$targetUserId, $roleId]);

    if ($stmt->fetch()) {
        // Reactivate if exists but inactive
        $stmt = $pdo->prepare("
            UPDATE user_roles 
            SET is_active = 1, assigned_by = ?, assigned_at = NOW()
            WHERE user_id = ? AND role_id = ?
        ");
        $stmt->execute([$assignedBy, $targetUserId, $roleId]);
    } else {
        // Insert new assignment
        $stmt = $pdo->prepare("
            INSERT INTO user_roles (user_id, role_id, assigned_by, assigned_at, is_active)
            VALUES (?, ?, ?, NOW(), 1)
        ");
        $stmt->execute([$targetUserId, $roleId, $assignedBy]);
    }

    sendResponse(true, 'Role assigned successfully');
}

function removeRole(PDO $pdo, array $input, int $removedBy): void {
    $targetUserId = (int)($input['user_id'] ?? 0);
    $roleId = (int)($input['role_id'] ?? 0);

    if (!$targetUserId || !$roleId) {
        sendError('user_id and role_id required');
    }

    // Soft delete (set is_active = 0)
    $stmt = $pdo->prepare("
        UPDATE user_roles 
        SET is_active = 0
        WHERE user_id = ? AND role_id = ?
    ");
    $stmt->execute([$targetUserId, $roleId]);

    if ($stmt->rowCount() === 0) {
        sendError('Role assignment not found', 404);
    }

    sendResponse(true, 'Role removed successfully');
}

function listUsersWithRoles(PDO $pdo): void {
    $stmt = $pdo->query("
        SELECT u.id, u.username, u.full_name, u.role AS main_role,
               GROUP_CONCAT(
                   CONCAT(r.role_code, ' (', r.role_name, ')') 
                   ORDER BY r.role_name 
                   SEPARATOR ', '
               ) AS additional_roles
        FROM users u
        LEFT JOIN user_roles ur ON ur.user_id = u.id AND ur.is_active = 1
        LEFT JOIN roles r ON r.id = ur.role_id AND r.is_active = 1
        WHERE u.is_active = 1
        GROUP BY u.id, u.username, u.full_name, u.role
        ORDER BY u.full_name
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(true, 'Users with roles retrieved', $users);
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
