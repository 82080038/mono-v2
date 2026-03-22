<?php
/**
 * Enhanced Members API with CRUD operations
 * Supports member management with proper authentication
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
function requireAuth($role = null) {
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
    
    if ($role && $user['role'] !== $role && $user['role'] !== 'admin') {
        throw new Exception('Insufficient privileges');
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

function generateJWTToken($user) {
    $payload = [
        'user_id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'iat' => time(),
        'exp' => time() + Config::JWT_EXPIRY
    ];
    
    $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
    $payload = base64_encode(json_encode($payload));
    $signature = hash_hmac('sha256', "$header.$payload", Config::JWT_SECRET, true);
    $signature = base64_encode($signature);
    
    return "$header.$payload.$signature";
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
            handleListMembers($db, $validator);
            break;
        case 'detail':
            handleGetMember($db, $validator);
            break;
        case 'search':
            handleSearchMembers($db, $validator);
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
            handleCreateMember($db, $validator);
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
            handleUpdateMember($db, $validator);
            break;
        case 'status':
            handleUpdateMemberStatus($db, $validator);
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
            handleDeleteMember($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleListMembers($db, $validator) {
    global $response;
    
    $user = requireAuth(); // Any authenticated user can view members
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $membershipType = $_GET['membership_type'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = [];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(m.full_name LIKE ? OR m.member_number LIKE ? OR m.email LIKE ? OR m.phone LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    }
    
    if (!empty($status)) {
        $whereConditions[] = "m.status = ?";
        $params[] = $status;
    }
    
    if (!empty($membershipType)) {
        $whereConditions[] = "m.membership_type = ?";
        $params[] = $membershipType;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM members m LEFT JOIN users u ON m.user_id = u.id $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get members
    $sql = "SELECT m.*, u.username, u.email as user_email, u.role, u.last_login
            FROM members m 
            LEFT JOIN users u ON m.user_id = u.id 
            $whereClause
            ORDER BY m.created_at DESC 
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $members = $db->fetchAll($sql, $params);
    
    $response['success'] = true;
    $response['message'] = 'Members retrieved successfully';
    $response['data'] = [
        'members' => $members,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    echo json_encode($response);
}

function handleGetMember($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $memberId = (int)($_GET['id'] ?? 0);
    
    if ($memberId <= 0) {
        $response['message'] = 'Member ID required';
        echo json_encode($response);
        return;
    }
    
    // Check permissions
    if ($user['role'] === 'member' && $user['user_id'] !== $memberId) {
        // Members can only view their own profile
        $member = $db->fetchOne(
            "SELECT m.*, u.username, u.email, u.role, u.last_login 
             FROM members m 
             JOIN users u ON m.user_id = u.id 
             WHERE m.id = ? AND m.user_id = ?",
            [$memberId, $user['user_id']]
        );
    } else {
        // Admin and staff can view any member
        $member = $db->fetchOne(
            "SELECT m.*, u.username, u.email, u.role, u.last_login 
             FROM members m 
             LEFT JOIN users u ON m.user_id = u.id 
             WHERE m.id = ?",
            [$memberId]
        );
    }
    
    if (!$member) {
        $response['message'] = 'Member not found';
        echo json_encode($response);
        return;
    }
    
    // Get additional data
    $member['addresses'] = $db->fetchAll(
        "SELECT * FROM member_addresses WHERE member_id = ? ORDER BY is_primary DESC",
        [$memberId]
    );
    
    $member['identities'] = $db->fetchAll(
        "SELECT * FROM member_identities WHERE member_id = ?",
        [$memberId]
    );
    
    $member['loans'] = $db->fetchAll(
        "SELECT * FROM loans WHERE member_id = ? ORDER BY created_at DESC LIMIT 5",
        [$memberId]
    );
    
    $member['savings'] = $db->fetchAll(
        "SELECT * FROM savings WHERE member_id = ? ORDER BY created_at DESC",
        [$memberId]
    );
    
    $response['success'] = true;
    $response['message'] = 'Member retrieved successfully';
    $response['data'] = $member;
    
    echo json_encode($response);
}

function handleCreateMember($db, $validator) {
    global $response;
    
    $user = requireAuth('admin'); // Only admin can create members
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'full_name' => 'required|string|min:3',
        'email' => 'required|email',
        'phone' => 'required|string|min:10',
        'address' => 'required|string|min:10',
        'birth_date' => 'required|date',
        'id_number' => 'required|string|min:16',
        'membership_type' => 'required|in:Regular,Premium,VIP',
        'create_user_account' => 'boolean'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Check if member with same email or ID number exists
    $existing = $db->fetchOne(
        "SELECT id FROM members WHERE email = ? OR id_number = ?",
        [$input['email'], $input['id_number']]
    );
    
    if ($existing) {
        $response['message'] = 'Member with this email or ID number already exists';
        echo json_encode($response);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Create user account if requested
        $userId = null;
        if ($input['create_user_account']) {
            $username = strtolower(str_replace(' ', '', $input['full_name'])) . mt_rand(100, 999);
            $password = generateRandomPassword();
            
            $userData = [
                'username' => $username,
                'email' => $input['email'],
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'full_name' => $input['full_name'],
                'phone' => $input['phone'],
                'role' => 'member',
                'status' => 'Active',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $userId = $db->insert('users', $userData);
        }
        
        // Generate member number
        $memberNumber = generateMemberNumber();
        
        // Create member
        $memberData = [
            'user_id' => $userId,
            'member_number' => $memberNumber,
            'full_name' => $input['full_name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'address' => $input['address'],
            'birth_date' => $input['birth_date'],
            'id_number' => $input['id_number'],
            'join_date' => date('Y-m-d'),
            'membership_type' => $input['membership_type'],
            'status' => 'Active',
            'is_active' => true,
            'credit_score' => 50.00,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $memberId = $db->insert('members', $memberData);
        
        // Create default savings account
        $savingsData = [
            'member_id' => $memberId,
            'account_number' => 'SAV' . $memberNumber,
            'amount' => 0,
            'type' => 'Regular',
            'interest_rate' => 5.00,
            'status' => 'Active',
            'balance' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $db->insert('savings', $savingsData);
        
        $db->commit();
        
        logAudit('CREATE', 'members', $memberId, null, $memberData);
        
        $response['success'] = true;
        $response['message'] = 'Member created successfully';
        $response['data'] = [
            'member_id' => $memberId,
            'member_number' => $memberNumber,
            'user_credentials' => $userId ? ['username' => $username, 'password' => $password] : null
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleUpdateMember($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    $memberId = (int)($input['id'] ?? 0);
    
    if ($memberId <= 0) {
        $response['message'] = 'Member ID required';
        echo json_encode($response);
        return;
    }
    
    // Check permissions
    if ($user['role'] === 'member') {
        // Members can only update their own profile
        $member = $db->fetchOne("SELECT user_id FROM members WHERE id = ?", [$memberId]);
        if (!$member || $member['user_id'] !== $user['user_id']) {
            $response['message'] = 'Unauthorized';
            echo json_encode($response);
            return;
        }
    }
    
    // Get current member data
    $currentMember = $db->fetchOne("SELECT * FROM members WHERE id = ?", [$memberId]);
    if (!$currentMember) {
        $response['message'] = 'Member not found';
        echo json_encode($response);
        return;
    }
    
    $rules = [
        'full_name' => 'string|min:3',
        'email' => 'email',
        'phone' => 'string|min:10',
        'address' => 'string|min:10',
        'membership_type' => 'in:Regular,Premium,VIP'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Update only allowed fields
    $allowedFields = ['full_name', 'email', 'phone', 'address', 'membership_type'];
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
    if (isset($updateData['email']) && $updateData['email'] !== $currentMember['email']) {
        $existing = $db->fetchOne("SELECT id FROM members WHERE email = ? AND id != ?", [$updateData['email'], $memberId]);
        if ($existing) {
            $response['message'] = 'Email already exists';
            echo json_encode($response);
            return;
        }
    }
    
    $db->update('members', $updateData, 'id = ?', [$memberId]);
    
    logAudit('UPDATE', 'members', $memberId, $currentMember, $updateData);
    
    $response['success'] = true;
    $response['message'] = 'Member updated successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleUpdateMemberStatus($db, $validator) {
    global $response;
    
    $user = requireAuth('admin'); // Only admin can change status
    $input = json_decode(file_get_contents('php://input'), true);
    $memberId = (int)($input['id'] ?? 0);
    $status = $input['status'] ?? '';
    
    if ($memberId <= 0 || !in_array($status, ['Active', 'Inactive', 'Suspended'])) {
        $response['message'] = 'Invalid member ID or status';
        echo json_encode($response);
        return;
    }
    
    $currentMember = $db->fetchOne("SELECT * FROM members WHERE id = ?", [$memberId]);
    if (!$currentMember) {
        $response['message'] = 'Member not found';
        echo json_encode($response);
        return;
    }
    
    $updateData = [
        'status' => $status,
        'is_active' => $status === 'Active',
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $db->update('members', $updateData, 'id = ?', [$memberId]);
    
    // Also update user status if user exists
    if ($currentMember['user_id']) {
        $db->update('users', ['status' => $status, 'is_active' => $status === 'Active'], 'id = ?', [$currentMember['user_id']]);
    }
    
    logAudit('STATUS_CHANGE', 'members', $memberId, $currentMember, $updateData);
    
    $response['success'] = true;
    $response['message'] = 'Member status updated successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleDeleteMember($db, $validator) {
    global $response;
    
    $user = requireAuth('admin'); // Only admin can delete members
    $memberId = (int)($_GET['id'] ?? 0);
    
    if ($memberId <= 0) {
        $response['message'] = 'Member ID required';
        echo json_encode($response);
        return;
    }
    
    $currentMember = $db->fetchOne("SELECT * FROM members WHERE id = ?", [$memberId]);
    if (!$currentMember) {
        $response['message'] = 'Member not found';
        echo json_encode($response);
        return;
    }
    
    // Check if member has active loans
    $activeLoans = $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status IN ('Active', 'Disbursed')", [$memberId]);
    if ($activeLoans['count'] > 0) {
        $response['message'] = 'Cannot delete member with active loans';
        echo json_encode($response);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Soft delete member
        $db->update('members', ['status' => 'Deleted', 'is_active' => false], 'id = ?', [$memberId]);
        
        // Soft delete user if exists
        if ($currentMember['user_id']) {
            $db->update('users', ['status' => 'Deleted', 'is_active' => false], 'id = ?', [$currentMember['user_id']]);
        }
        
        $db->commit();
        
        logAudit('DELETE', 'members', $memberId, $currentMember, ['status' => 'Deleted']);
        
        $response['success'] = true;
        $response['message'] = 'Member deleted successfully';
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleSearchMembers($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $query = $_GET['q'] ?? '';
    
    if (strlen($query) < 2) {
        $response['message'] = 'Search query must be at least 2 characters';
        echo json_encode($response);
        return;
    }
    
    $searchParam = "%$query%";
    
    $members = $db->fetchAll(
        "SELECT m.id, m.member_number, m.full_name, m.email, m.phone, m.membership_type, m.status
         FROM members m 
         WHERE (m.full_name LIKE ? OR m.member_number LIKE ? OR m.email LIKE ? OR m.phone LIKE ?)
         AND m.status != 'Deleted'
         ORDER BY m.full_name
         LIMIT 20",
        [$searchParam, $searchParam, $searchParam, $searchParam]
    );
    
    $response['success'] = true;
    $response['message'] = 'Search results retrieved';
    $response['data'] = $members;
    
    echo json_encode($response);
}

function generateMemberNumber() {
    return 'M' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function generateRandomPassword() {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
}
?>
