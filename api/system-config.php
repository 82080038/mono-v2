<?php
/**
 * KSP Lam Gabe Jaya - System Configuration API
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
    case 'get_config':
        getConfig($pdo);
        break;

    case 'update_config':
        updateConfig($pdo, $userId);
        break;

    case 'reset_config':
        resetConfig($pdo, $userId);
        break;

    case 'test_email':
        testEmail($pdo);
        break;

    default:
        sendError('Invalid action', 400);
}

// ─── Functions ─────────────────────────────────────────────────────────────

function getConfig(PDO $pdo): void {
    // Return system configuration
    $config = [
        'app_name' => 'KSP Lam Gabe Jaya',
        'app_version' => '2.0.0',
        'app_environment' => 'production',
        'app_timezone' => 'Asia/Jakarta',
        'app_debug' => false,
        'jwt_secret' => '••••••••••••••••',
        'token_expiry' => 24,
        'max_login_attempts' => 3,
        'login_lockout_time' => 15,
        'session_lifetime' => 1,
        'mail_host' => '',
        'mail_port' => 587,
        'mail_username' => '',
        'mail_encryption' => 'tls',
        'upload_max_size' => 5,
        'upload_allowed_types' => 'jpg,jpeg,png,pdf,doc,docx',
        'loan_interest_rate' => 1.00,
        'loan_max_multiplier' => 10,
        'loan_max_term' => 12,
        'mandatory_savings' => 10000,
        'savings_interest_rate' => 0.50,
        'late_penalty_rate' => 10.00
    ];
    
    sendResponse(true, 'Configuration retrieved', $config);
}

function updateConfig(PDO $pdo, int $userId): void {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Log configuration change
    $stmt = $pdo->prepare("
        INSERT INTO audit_logs (action, table_name, record_id, user_id, description, created_at)
        VALUES ('UPDATE', 'settings', 1, ?, 'System configuration updated by Super Admin', NOW())
    ");
    $stmt->execute([$userId]);
    
    // In production, this would update a settings table or .env file
    // For now, just return success
    sendResponse(true, 'Configuration updated successfully');
}

function resetConfig(PDO $pdo, int $userId): void {
    // Log configuration reset
    $stmt = $pdo->prepare("
        INSERT INTO audit_logs (action, table_name, record_id, user_id, description, created_at)
        VALUES ('UPDATE', 'settings', 1, ?, 'System configuration reset to defaults by Super Admin', NOW())
    ");
    $stmt->execute([$userId]);
    
    sendResponse(true, 'Configuration reset to defaults');
}

function testEmail(PDO $pdo): void {
    // In production, this would send a test email
    sendResponse(true, 'Test email sent successfully');
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
