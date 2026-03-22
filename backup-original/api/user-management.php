<?php
/**
 * User Management API for Admin
 * Handles user account management with proper authentication
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Include required files
require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/DatabaseHelper.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/DataValidator.php';
require_once __DIR__ . '/SecurityLogger.php';

// Initialize services
Logger::initialize();
$securityLogger = SecurityLogger::getInstance();
$db = DatabaseHelper::getInstance();
$validator = new DataValidator();

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'data' => null,
    'errors' => [],
    'timestamp' => date('Y-m-d H:i:s')
];

// Authentication middleware
function requireAuth($role = 'admin') {
    global $db;
    
    $token = getTokenFromRequest();
    if (!$token) {
        throw new Exception('Authentication required');
    }
    
    $tokenData = validateJWTToken($token);
    if (!$tokenData) {
        throw new Exception('Invalid token');
    }
    
    $user = $db->fetchOne(
        "SELECT * FROM users WHERE id = ? AND is_active = 1",
        [$tokenData['user_id']]
    );
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    if ($user['role'] !== 'admin') {
        throw new Exception('Admin access required');
    }
    
    return array_merge($user, $tokenData);
}

function getTokenFromRequest() {
    $headers = getallheaders();
    return $headers['Authorization'] ?? $headers['authorization'] ?? $_REQUEST['token'] ?? null;
}

function validateJWTToken($token) {
    if (!$token) {
        return null;
    }
    
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }
    
    $payload = base64_decode($parts[1]);
    $payloadData = json_decode($payload, true);
    
    if (!$payloadData || $payloadData['exp'] < time()) {
        return null;
    }
    
    return $payloadData;
}

function logAudit($action, $table, $recordId, $oldValues = null, $newValues = null) {
    global $db, $securityLogger;
    
    $user = getCurrentUser();
    if ($user) {
        $db->insert('audit_logs', [
            'user_id' => $user['id'],
            'action' => $action,
            'table_name' => $table,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        $securityLogger->logDataModification($table, $action, $oldValues, $newValues);
    }
}

function getCurrentUser() {
    global $db;
    
    $token = getTokenFromRequest();
    if (!$token) {
        return null;
    }
    
    $tokenData = validateJWTToken($token);
    if (!$tokenData) {
        return null;
    }
    
    return $db->fetchOne(
        "SELECT * FROM users WHERE id = ? AND is_active = 1",
        [$tokenData['user_id']]
    );
}

// Handle API requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($action, $db, $validator);
            break;
        case 'POST':
            handlePostRequest($action, $db, $validator);
            break;
        case 'PUT':
            handlePutRequest($action, $db, $validator);
            break;
        case 'DELETE':
            handleDeleteRequest($action, $db, $validator);
            break;
        default:
            $response['message'] = 'Method not allowed';
            echo json_encode($response);
            break;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    echo json_encode($response);
}

function handleGetRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'list':
            handleListUsers($db, $validator);
            break;
        case 'detail':
            handleGetUser($db, $validator);
            break;
        case 'roles':
            handleGetRoles($db, $validator);
            break;
        case 'permissions':
            handleGetPermissions($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handlePostRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'create':
            handleCreateUser($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handlePutRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'update':
            handleUpdateUser($db, $validator);
            break;
        case 'status':
            handleUpdateUserStatus($db, $validator);
            break;
        case 'role':
            handleUpdateUserRole($db, $validator);
            break;
        case 'password':
            handleResetPassword($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleDeleteRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'delete':
            handleDeleteUser($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleListUsers($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $search = $_GET['search'] ?? '';
    $role = $_GET['role'] ?? '';
    $status = $_GET['status'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = [];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
    }
    
    if (!empty($role)) {
        $whereConditions[] = "u.role = ?";
        $params[] = $role;
    }
    
    if (!empty($status)) {
        $whereConditions[] = "u.status = ?";
        $params[] = $status;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM users u $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get users
    $sql = "SELECT u.*, m.member_number, m.membership_type
            FROM users u 
            LEFT JOIN members m ON u.id = m.user_id 
            $whereClause
            ORDER BY u.created_at DESC 
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $users = $db->fetchAll($sql, $params);
    
    // Remove sensitive data
    foreach ($users as &$userItem) {
        unset($userItem['password']);
    }
    
    $response['success'] = true;
    $response['message'] = 'Users retrieved successfully';
    $response['data'] = [
        'users' => $users,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    echo json_encode($response);
}

function handleGetUser($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $userId = (int)($_GET['id'] ?? 0);
    
    if ($userId <= 0) {
        $response['message'] = 'User ID required';
        echo json_encode($response);
        return;
    }
    
    $userDetail = $db->fetchOne(
        "SELECT u.*, m.member_number, m.full_name as member_name, m.membership_type, m.credit_score
         FROM users u 
         LEFT JOIN members m ON u.id = m.user_id 
         WHERE u.id = ?",
        [$userId]
    );
    
    if (!$userDetail) {
        $response['message'] = 'User not found';
        echo json_encode($response);
        return;
    }
    
    // Remove sensitive data
    unset($userDetail['password']);
    
    // Get user activity
    $userDetail['recent_activity'] = $db->fetchAll(
        "SELECT * FROM audit_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
        [$userId]
    );
    
    $response['success'] = true;
    $response['message'] = 'User retrieved successfully';
    $response['data'] = $userDetail;
    
    echo json_encode($response);
}

function handleCreateUser($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'username' => 'required|string|min:3',
        'email' => 'required|email',
        'password' => 'required|string|min:8',
        'full_name' => 'required|string|min:3',
        'phone' => 'required|string|min:10',
        'role' => 'required|in:admin,staff,member'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Check if username or email already exists
    $existing = $db->fetchOne(
        "SELECT id FROM users WHERE username = ? OR email = ?",
        [$input['username'], $input['email']]
    );
    
    if ($existing) {
        $response['message'] = 'Username or email already exists';
        echo json_encode($response);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Hash password
        $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
        
        // Create user
        $userData = [
            'username' => $input['username'],
            'email' => $input['email'],
            'password' => $hashedPassword,
            'full_name' => $input['full_name'],
            'phone' => $input['phone'],
            'role' => $input['role'],
            'status' => 'Active',
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $userId = $db->insert('users', $userData);
        
        // If member, create member record
        if ($input['role'] === 'member') {
            $memberData = [
                'user_id' => $userId,
                'member_number' => generateMemberNumber(),
                'full_name' => $input['full_name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'join_date' => date('Y-m-d'),
                'status' => 'Active',
                'is_active' => true,
                'membership_type' => 'Regular',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->insert('members', $memberData);
            
            // Create default savings account
            $savingsData = [
                'member_id' => $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$userId])['id'],
                'account_number' => 'SAV' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'amount' => 0,
                'type' => 'Regular',
                'interest_rate' => 5.00,
                'status' => 'Active',
                'balance' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->insert('savings', $savingsData);
        }
        
        $db->commit();
        
        logAudit('CREATE', 'users', $userId, null, $userData);
        
        $response['success'] = true;
        $response['message'] = 'User created successfully';
        $response['data'] = ['user_id' => $userId];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleUpdateUser($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = (int)($input['id'] ?? 0);
    
    if ($userId <= 0) {
        $response['message'] = 'User ID required';
        echo json_encode($response);
        return;
    }
    
    // Prevent admin from updating themselves without proper checks
    if ($userId === $user['id']) {
        $response['message'] = 'Cannot update your own account through this endpoint';
        echo json_encode($response);
        return;
    }
    
    $currentUser = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
    if (!$currentUser) {
        $response['message'] = 'User not found';
        echo json_encode($response);
        return;
    }
    
    $rules = [
        'full_name' => 'string|min:3',
        'email' => 'email',
        'phone' => 'string|min:10'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Update only allowed fields
    $allowedFields = ['full_name', 'email', 'phone'];
    $updateData = [];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateData[$field] = $input[$field];
        }
    }
    
    if (empty($updateData)) {
        $response['message'] = 'No valid fields to update';
        echo json_encode($response);
        return;
    }
    
    $updateData['updated_at'] = date('Y-m-d H:i:s');
    
    // Check if email is being changed and if it's already taken
    if (isset($updateData['email']) && $updateData['email'] !== $currentUser['email']) {
        $existing = $db->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$updateData['email'], $userId]);
        if ($existing) {
            $response['message'] = 'Email already exists';
            echo json_encode($response);
            return;
        }
    }
    
    $db->update('users', $updateData, 'id = ?', [$userId]);
    
    logAudit('UPDATE', 'users', $userId, $currentUser, $updateData);
    
    $response['success'] = true;
    $response['message'] = 'User updated successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleUpdateUserStatus($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = (int)($input['id'] ?? 0);
    $status = $input['status'] ?? '';
    
    if ($userId <= 0 || !in_array($status, ['Active', 'Inactive', 'Suspended'])) {
        $response['message'] = 'Invalid user ID or status';
        echo json_encode($response);
        return;
    }
    
    // Prevent admin from deactivating themselves
    if ($userId === $user['id']) {
        $response['message'] = 'Cannot change your own status';
        echo json_encode($response);
        return;
    }
    
    $currentUser = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
    if (!$currentUser) {
        $response['message'] = 'User not found';
        echo json_encode($response);
        return;
    }
    
    $updateData = [
        'status' => $status,
        'is_active' => $status === 'Active',
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $db->update('users', $updateData, 'id = ?', [$userId]);
    
    // Also update member status if user has member record
    if ($currentUser['role'] === 'member') {
        $db->update('members', ['status' => $status, 'is_active' => $status === 'Active'], 'user_id = ?', [$userId]);
    }
    
    logAudit('STATUS_CHANGE', 'users', $userId, $currentUser, $updateData);
    
    $response['success'] = true;
    $response['message'] = 'User status updated successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleUpdateUserRole($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = (int)($input['id'] ?? 0);
    $newRole = $input['role'] ?? '';
    
    if ($userId <= 0 || !in_array($newRole, ['admin', 'staff', 'member'])) {
        $response['message'] = 'Invalid user ID or role';
        echo json_encode($response);
        return;
    }
    
    // Prevent admin from changing their own role
    if ($userId === $user['id']) {
        $response['message'] = 'Cannot change your own role';
        echo json_encode($response);
        return;
    }
    
    $currentUser = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
    if (!$currentUser) {
        $response['message'] = 'User not found';
        echo json_encode($response);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        $updateData = [
            'role' => $newRole,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $db->update('users', $updateData, 'id = ?', [$userId]);
        
        // Handle member record creation/deletion
        if ($newRole === 'member' && $currentUser['role'] !== 'member') {
            // Create member record
            $memberData = [
                'user_id' => $userId,
                'member_number' => generateMemberNumber(),
                'full_name' => $currentUser['full_name'],
                'email' => $currentUser['email'],
                'phone' => $currentUser['phone'],
                'join_date' => date('Y-m-d'),
                'status' => 'Active',
                'is_active' => true,
                'membership_type' => 'Regular',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->insert('members', $memberData);
            
            // Create default savings account
            $memberId = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$userId])['id'];
            $savingsData = [
                'member_id' => $memberId,
                'account_number' => 'SAV' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'amount' => 0,
                'type' => 'Regular',
                'interest_rate' => 5.00,
                'status' => 'Active',
                'balance' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->insert('savings', $savingsData);
            
        } elseif ($newRole !== 'member' && $currentUser['role'] === 'member') {
            // Deactivate member record
            $db->update('members', ['status' => 'Inactive', 'is_active' => false], 'user_id = ?', [$userId]);
        }
        
        $db->commit();
        
        logAudit('ROLE_CHANGE', 'users', $userId, $currentUser, $updateData);
        
        $response['success'] = true;
        $response['message'] = 'User role updated successfully';
        $response['data'] = $updateData;
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleResetPassword($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = (int)($input['id'] ?? 0);
    $newPassword = $input['new_password'] ?? '';
    
    if ($userId <= 0 || strlen($newPassword) < 8) {
        $response['message'] = 'Invalid user ID or password';
        echo json_encode($response);
        return;
    }
    
    $currentUser = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
    if (!$currentUser) {
        $response['message'] = 'User not found';
        echo json_encode($response);
        return;
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $updateData = [
        'password' => $hashedPassword,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $db->update('users', $updateData, 'id = ?', [$userId]);
    
    logAudit('PASSWORD_RESET', 'users', $userId, $currentUser, ['password' => '***RESET***']);
    
    $response['success'] = true;
    $response['message'] = 'Password reset successfully';
    
    echo json_encode($response);
}

function handleDeleteUser($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $userId = (int)($_GET['id'] ?? 0);
    
    if ($userId <= 0) {
        $response['message'] = 'User ID required';
        echo json_encode($response);
        return;
    }
    
    // Prevent admin from deleting themselves
    if ($userId === $user['id']) {
        $response['message'] = 'Cannot delete your own account';
        echo json_encode($response);
        return;
    }
    
    $currentUser = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
    if (!$currentUser) {
        $response['message'] = 'User not found';
        echo json_encode($response);
        return;
    }
    
    // Check if user has active loans
    if ($currentUser['role'] === 'member') {
        $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$userId]);
        if ($member) {
            $activeLoans = $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status IN ('Active', 'Disbursed')", [$member['id']]);
            if ($activeLoans['count'] > 0) {
                $response['message'] = 'Cannot delete user with active loans';
                echo json_encode($response);
                return;
            }
        }
    }
    
    $db->beginTransaction();
    
    try {
        // Soft delete user
        $db->update('users', ['status' => 'Deleted', 'is_active' => false], 'id = ?', [$userId]);
        
        // Soft delete member record if exists
        if ($currentUser['role'] === 'member') {
            $db->update('members', ['status' => 'Deleted', 'is_active' => false], 'user_id = ?', [$userId]);
        }
        
        $db->commit();
        
        logAudit('DELETE', 'users', $userId, $currentUser, ['status' => 'Deleted']);
        
        $response['success'] = true;
        $response['message'] = 'User deleted successfully';
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleGetRoles($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $roles = [
        ['role' => 'admin', 'description' => 'System administrator with full access'],
        ['role' => 'staff', 'description' => 'Staff member with limited access'],
        ['role' => 'member', 'description' => 'Member with self-service access']
    ];
    
    $response['success'] = true;
    $response['message'] = 'Roles retrieved successfully';
    $response['data'] = $roles;
    
    echo json_encode($response);
}

function handleGetPermissions($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $permissions = [
        'admin' => [
            'users' => ['create', 'read', 'update', 'delete'],
            'members' => ['create', 'read', 'update', 'delete'],
            'loans' => ['create', 'read', 'update', 'delete'],
            'savings' => ['create', 'read', 'update', 'delete'],
            'payments' => ['create', 'read', 'update', 'delete'],
            'reports' => ['read'],
            'settings' => ['read', 'update']
        ],
        'staff' => [
            'members' => ['read', 'update'],
            'loans' => ['read', 'update'],
            'savings' => ['read', 'update'],
            'payments' => ['create', 'read'],
            'reports' => ['read']
        ],
        'member' => [
            'profile' => ['read', 'update'],
            'loans' => ['create', 'read'],
            'savings' => ['read', 'create'],
            'payments' => ['create', 'read']
        ]
    ];
    
    $response['success'] = true;
    $response['message'] = 'Permissions retrieved successfully';
    $response['data'] = $permissions;
    
    echo json_encode($response);
}

function generateMemberNumber() {
    return 'M' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}
?>
