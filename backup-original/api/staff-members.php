<?php
/**
 * Staff Member Management API
 * Handles member management for staff members
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
function requireAuth($role = 'staff') {
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
    
    if ($user['role'] !== $role && $user['role'] !== 'admin') {
        throw new Exception('Staff access required');
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
            handleGetMemberDetail($db, $validator);
            break;
        case 'search':
            handleSearchMembers($db, $validator);
            break;
        case 'assigned_members':
            handleGetAssignedMembers($db, $validator);
            break;
        case 'member_loans':
            handleGetMemberLoans($db, $validator);
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
        case 'create_member':
            handleCreateMember($db, $validator);
            break;
        case 'add_note':
            handleAddNote($db, $validator);
            break;
        case 'schedule_visit':
            handleScheduleVisit($db, $validator);
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
        case 'update_member':
            handleUpdateMember($db, $validator);
            break;
        case 'update_status':
            handleUpdateMemberStatus($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleListMembers($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $membershipType = $_GET['membership_type'] ?? '';
    $sortBy = $_GET['sort_by'] ?? 'created_at';
    $sortOrder = $_GET['sort_order'] ?? 'DESC';
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = [];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(m.full_name LIKE ? OR m.email LIKE ? OR m.phone LIKE ? OR m.member_number LIKE ?)";
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
    
    // Validate sort field
    $allowedSortFields = ['full_name', 'member_number', 'join_date', 'credit_score', 'created_at'];
    if (!in_array($sortBy, $allowedSortFields)) {
        $sortBy = 'created_at';
    }
    
    $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM members m $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get members
    $sql = "SELECT m.*, 
                    u.username, 
                    u.email as user_email,
                    (SELECT COUNT(*) FROM loans WHERE member_id = m.id AND status IN ('Active', 'Disbursed')) as active_loans,
                    (SELECT COALESCE(SUM(balance), 0) FROM savings WHERE member_id = m.id AND status = 'Active') as total_savings,
                    (SELECT COUNT(*) FROM gps_tracking WHERE member_id = m.id AND DATE(created_at) = CURDATE()) as visits_today
             FROM members m 
             LEFT JOIN users u ON m.user_id = u.id 
             $whereClause
             ORDER BY m.$sortBy $sortOrder 
             LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $members = $db->fetchAll($sql, $params);
    
    // Add member metadata
    foreach ($members as &$member) {
        $member['credit_score_display'] = getCreditScoreDisplay($member['credit_score']);
        $member['membership_level'] = getMembershipLevel($member['membership_type']);
        $member['last_visit'] = $db->fetchOne(
            "SELECT created_at FROM gps_tracking WHERE member_id = ? ORDER BY created_at DESC LIMIT 1",
            [$member['id']]
        )['created_at'] ?? null;
    }
    
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

function handleGetMemberDetail($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $memberId = (int)($_GET['id'] ?? 0);
    
    if ($memberId <= 0) {
        $response['message'] = 'Member ID required';
        echo json_encode($response);
        return;
    }
    
    // Get member details
    $member = $db->fetchOne(
        "SELECT m.*, 
                u.username, 
                u.email as user_email,
                u.last_login
         FROM members m 
         LEFT JOIN users u ON m.user_id = u.id 
         WHERE m.id = ?",
        [$memberId]
    );
    
    if (!$member) {
        $response['message'] = 'Member not found';
        echo json_encode($response);
        return;
    }
    
    // Get member's loans
    $loans = $db->fetchAll(
        "SELECT l.*, 
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE loan_id = l.id AND status = 'Completed') as paid_amount
         FROM loans l 
         WHERE l.member_id = ? 
         ORDER BY l.created_at DESC 
         LIMIT 5",
        [$memberId]
    );
    
    foreach ($loans as &$loan) {
        $loan['remaining_balance'] = ($loan['amount'] + ($loan['total_interest'] ?? 0)) - $loan['paid_amount'];
        $loan['status_display'] = getLoanStatusDisplay($loan['status']);
    }
    
    // Get member's savings
    $savings = $db->fetchAll(
        "SELECT s.*, 
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE savings_id = s.id AND type = 'Savings Deposit' AND status = 'Completed') as total_deposits,
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE savings_id = s.id AND type = 'Savings Withdrawal' AND status = 'Completed') as total_withdrawals
         FROM savings s 
         WHERE s.member_id = ? 
         ORDER BY s.created_at DESC",
        [$memberId]
    );
    
    foreach ($savings as &$saving) {
        $saving['current_balance'] = $saving['total_deposits'] - $saving['total_withdrawals'];
    }
    
    // Get member's addresses
    $addresses = $db->fetchAll(
        "SELECT * FROM member_addresses WHERE member_id = ? ORDER BY is_primary DESC, created_at DESC",
        [$memberId]
    );
    
    // Get member's identities
    $identities = $db->fetchAll(
        "SELECT * FROM member_identities WHERE member_id = ? ORDER BY created_at DESC",
        [$memberId]
    );
    
    // Get recent visits
    $visits = $db->fetchAll(
        "SELECT gt.*, u.full_name as staff_name
         FROM gps_tracking gt 
         LEFT JOIN users u ON gt.staff_id = u.id 
         WHERE gt.member_id = ? 
         ORDER BY gt.created_at DESC 
         LIMIT 5",
        [$memberId]
    );
    
    // Get staff notes
    $notes = $db->fetchAll(
        "SELECT sn.*, u.full_name as staff_name
         FROM staff_notes sn 
         LEFT JOIN users u ON sn.staff_id = u.id 
         WHERE sn.member_id = ? 
         ORDER BY sn.created_at DESC 
         LIMIT 5",
        [$memberId]
    );
    
    $memberDetail = [
        'basic_info' => $member,
        'financial_info' => [
            'active_loans' => count(array_filter($loans, fn($l) => in_array($l['status'], ['Active', 'Disbursed']))),
            'total_savings' => array_sum(array_column($savings, 'current_balance')),
            'credit_score' => $member['credit_score'],
            'credit_score_display' => getCreditScoreDisplay($member['credit_score'])
        ],
        'loans' => $loans,
        'savings' => $savings,
        'addresses' => $addresses,
        'identities' => $identities,
        'visits' => $visits,
        'notes' => $notes,
        'statistics' => [
            'total_visits' => $db->fetchOne("SELECT COUNT(*) as count FROM gps_tracking WHERE member_id = ?", [$memberId])['count'],
            'last_visit' => $db->fetchOne("SELECT created_at FROM gps_tracking WHERE member_id = ? ORDER BY created_at DESC LIMIT 1", [$memberId])['created_at'],
            'membership_days' => calculateMembershipDays($member['join_date'])
        ]
    ];
    
    $response['success'] = true;
    $response['message'] = 'Member detail retrieved successfully';
    $response['data'] = $memberDetail;
    
    echo json_encode($response);
}

function handleSearchMembers($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $query = $_GET['q'] ?? '';
    
    if (empty($query)) {
        $response['message'] = 'Search query required';
        echo json_encode($response);
        return;
    }
    
    $searchTerm = "%$query%";
    
    $members = $db->fetchAll(
        "SELECT m.id, m.full_name, m.member_number, m.phone, m.email, m.address, m.credit_score, m.membership_type,
                u.username,
                (SELECT COUNT(*) FROM loans WHERE member_id = m.id AND status IN ('Active', 'Disbursed')) as active_loans,
                (SELECT COALESCE(SUM(balance), 0) FROM savings WHERE member_id = m.id AND status = 'Active') as total_savings
         FROM members m 
         LEFT JOIN users u ON m.user_id = u.id 
         WHERE m.status = 'Active' AND (
             m.full_name LIKE ? OR 
             m.member_number LIKE ? OR 
             m.phone LIKE ? OR 
             m.email LIKE ? OR 
             u.username LIKE ?
         )
         ORDER BY m.full_name
         LIMIT 20",
        [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]
    );
    
    foreach ($members as &$member) {
        $member['credit_score_display'] = getCreditScoreDisplay($member['credit_score']);
        $member['membership_level'] = getMembershipLevel($member['membership_type']);
    }
    
    $response['success'] = true;
    $response['message'] = 'Search completed successfully';
    $response['data'] = [
        'members' => $members,
        'total' => count($members)
    ];
    
    echo json_encode($response);
}

function handleGetAssignedMembers($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $status = $_GET['status'] ?? 'active';
    
    $offset = ($page - 1) * $limit;
    
    // Get members assigned to this staff
    $whereConditions = ["m.status = 'Active'"];
    $params = [];
    
    // Add any additional filtering logic based on staff assignments
    // This could be based on geographic areas, member types, etc.
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM members m $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get assigned members
    $sql = "SELECT m.*, 
                    u.username,
                    (SELECT COUNT(*) FROM loans WHERE member_id = m.id AND status IN ('Active', 'Disbursed')) as active_loans,
                    (SELECT COALESCE(SUM(balance), 0) FROM savings WHERE member_id = m.id AND status = 'Active') as total_savings,
                    (SELECT COUNT(*) FROM gps_tracking WHERE member_id = m.id AND DATE(created_at) = CURDATE()) as visits_today
             FROM members m 
             LEFT JOIN users u ON m.user_id = u.id 
             $whereClause
             ORDER BY m.full_name ASC 
             LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $members = $db->fetchAll($sql, $params);
    
    foreach ($members as &$member) {
        $member['credit_score_display'] = getCreditScoreDisplay($member['credit_score']);
        $member['membership_level'] = getMembershipLevel($member['membership_type']);
        
        // Get last visit
        $lastVisit = $db->fetchOne(
            "SELECT created_at FROM gps_tracking WHERE member_id = ? ORDER BY created_at DESC LIMIT 1",
            [$member['id']]
        );
        $member['last_visit'] = $lastVisit['created_at'] ?? null;
        $member['days_since_last_visit'] = $member['last_visit'] ? 
            (strtotime(date('Y-m-d')) - strtotime($member['last_visit'])) / 86400 : null;
    }
    
    $response['success'] = true;
    $response['message'] = 'Assigned members retrieved successfully';
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

function handleGetMemberLoans($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $memberId = (int)($_GET['member_id'] ?? 0);
    
    if ($memberId <= 0) {
        $response['message'] = 'Member ID required';
        echo json_encode($response);
        return;
    }
    
    $loans = $db->fetchAll(
        "SELECT l.*, 
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE loan_id = l.id AND status = 'Completed') as paid_amount,
                (SELECT COUNT(*) FROM payment_transactions WHERE loan_id = l.id AND status = 'Completed') as payment_count,
                (SELECT MAX(created_at) FROM payment_transactions WHERE loan_id = l.id AND status = 'Completed') as last_payment_date
         FROM loans l 
         WHERE l.member_id = ? 
         ORDER BY l.created_at DESC",
        [$memberId]
    );
    
    foreach ($loans as &$loan) {
        $loan['remaining_balance'] = ($loan['amount'] + ($loan['total_interest'] ?? 0)) - $loan['paid_amount'];
        $loan['status_display'] = getLoanStatusDisplay($loan['status']);
        $loan['payment_progress'] = $loan['amount'] > 0 ? ($loan['paid_amount'] / ($loan['amount'] + ($loan['total_interest'] ?? 0))) * 100 : 0;
        $loan['days_overdue'] = $loan['next_payment_date'] && $loan['next_payment_date'] < date('Y-m-d') ? 
            (strtotime(date('Y-m-d')) - strtotime($loan['next_payment_date'])) / 86400 : 0;
    }
    
    $response['success'] = true;
    $response['message'] = 'Member loans retrieved successfully';
    $response['data'] = $loans;
    
    echo json_encode($response);
}

function handleCreateMember($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'full_name' => 'required|string|min:3',
        'email' => 'required|email',
        'phone' => 'required|string|min:10',
        'birth_date' => 'required|date',
        'id_number' => 'required|string|min:16',
        'address' => 'required|string|min:10',
        'membership_type' => 'required|in:Regular,Premium,VIP',
        'create_user_account' => 'boolean',
        'initial_deposit' => 'numeric|min:100000'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Check if member with same email or ID number already exists
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
                'created_by' => $user['id'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $userId = $db->insert('users', $userData);
        }
        
        // Generate member number
        $memberNumber = generateMemberNumber();
        
        // Create member record
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
            'created_by' => $user['id'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $memberId = $db->insert('members', $memberData);
        
        // Create default savings account
        $savingsData = [
            'member_id' => $memberId,
            'account_number' => 'SAV' . $memberNumber,
            'amount' => $input['initial_deposit'] ?? 0,
            'type' => 'Regular',
            'interest_rate' => getSavingsInterestRate($input['membership_type'], 50),
            'status' => 'Active',
            'balance' => $input['initial_deposit'] ?? 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $savingsId = $db->insert('savings', $savingsData);
        
        // Create initial deposit transaction if deposit amount > 0
        if (!empty($input['initial_deposit']) && $input['initial_deposit'] > 0) {
            $transactionData = [
                'savings_id' => $savingsId,
                'member_id' => $memberId,
                'transaction_number' => generateTransactionNumber(),
                'amount' => $input['initial_deposit'],
                'type' => 'Savings Deposit',
                'payment_method' => 'Cash',
                'status' => 'Completed',
                'description' => 'Initial deposit for new member',
                'processed_by' => $user['id'],
                'processed_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->insert('payment_transactions', $transactionData);
        }
        
        // Log activity
        $db->insert('staff_activities', [
            'staff_id' => $user['id'],
            'activity_type' => 'member_created',
            'description' => 'Created new member: ' . $input['full_name'],
            'reference_id' => $memberId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $db->commit();
        
        $response['success'] = true;
        $response['message'] = 'Member created successfully';
        $response['data'] = [
            'member_id' => $memberId,
            'member_number' => $memberNumber,
            'savings_account_id' => $savingsId,
            'user_credentials' => $userId ? ['username' => $username, 'password' => $password] : null,
            'initial_balance' => $input['initial_deposit'] ?? 0
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleAddNote($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'member_id' => 'required|integer',
        'note_type' => 'required|in:general,visit,loan,savings,complaint,other',
        'content' => 'required|string|min:5',
        'is_private' => 'boolean'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Verify member exists
    $member = $db->fetchOne("SELECT * FROM members WHERE id = ? AND status = 'Active'", [$input['member_id']]);
    
    if (!$member) {
        $response['message'] = 'Member not found or inactive';
        echo json_encode($response);
        return;
    }
    
    $noteData = [
        'staff_id' => $user['id'],
        'member_id' => $input['member_id'],
        'note_type' => $input['note_type'],
        'content' => $input['content'],
        'is_private' => $input['is_private'] ?? false,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $noteId = $db->insert('staff_notes', $noteData);
    
    $response['success'] = true;
    $response['message'] = 'Note added successfully';
    $response['data'] = ['note_id' => $noteId];
    
    echo json_encode($response);
}

function handleScheduleVisit($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'member_id' => 'required|integer',
        'visit_date' => 'required|date',
        'purpose' => 'required|string|min:5',
        'notes' => 'string',
        'priority' => 'in:low,medium,high'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Verify member exists
    $member = $db->fetchOne("SELECT * FROM members WHERE id = ? AND status = 'Active'", [$input['member_id']]);
    
    if (!$member) {
        $response['message'] = 'Member not found or inactive';
        echo json_encode($response);
        return;
    }
    
    $visitData = [
        'staff_id' => $user['id'],
        'member_id' => $input['member_id'],
        'visit_date' => $input['visit_date'],
        'purpose' => $input['purpose'],
        'notes' => $input['notes'] ?? '',
        'priority' => $input['priority'] ?? 'medium',
        'status' => 'scheduled',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $visitId = $db->insert('member_visits', $visitData);
    
    $response['success'] = true;
    $response['message'] = 'Visit scheduled successfully';
    $response['data'] = [
        'visit_id' => $visitId,
        'member' => $member
    ];
    
    echo json_encode($response);
}

function handleUpdateMember($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    $memberId = (int)($input['member_id'] ?? 0);
    
    if ($memberId <= 0) {
        $response['message'] = 'Member ID required';
        echo json_encode($response);
        return;
    }
    
    // Verify member exists
    $member = $db->fetchOne("SELECT * FROM members WHERE id = ?", [$memberId]);
    
    if (!$member) {
        $response['message'] = 'Member not found';
        echo json_encode($response);
        return;
    }
    
    // Update only allowed fields for staff
    $allowedFields = ['phone', 'address', 'latitude', 'longitude', 'notes'];
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
    
    $db->update('members', $updateData, 'id = ?', [$memberId]);
    
    // Log activity
    $db->insert('staff_activities', [
        'staff_id' => $user['id'],
        'activity_type' => 'member_updated',
        'description' => 'Updated member: ' . $member['full_name'],
        'reference_id' => $memberId,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $response['success'] = true;
    $response['message'] = 'Member updated successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleUpdateMemberStatus($db, $validator) {
    global $response;
    
    $user = requireAuth('staff');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'member_id' => 'required|integer',
        'status' => 'required|in:Active,Inactive,Suspended',
        'reason' => 'string'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Verify member exists
    $member = $db->fetchOne("SELECT * FROM members WHERE id = ?", [$input['member_id']]);
    
    if (!$member) {
        $response['message'] = 'Member not found';
        echo json_encode($response);
        return;
    }
    
    $updateData = [
        'status' => $input['status'],
        'is_active' => $input['status'] === 'Active',
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $db->update('members', $updateData, 'id = ?', [$input['member_id']]);
    
    // Log activity
    $db->insert('staff_activities', [
        'staff_id' => $user['id'],
        'activity_type' => 'member_status_updated',
        'description' => "Updated member status to {$input['status']}: " . $member['full_name'],
        'reference_id' => $input['member_id'],
        'notes' => $input['reason'] ?? '',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $response['success'] = true;
    $response['message'] = 'Member status updated successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

// Helper functions
function getCreditScoreDisplay($score) {
    if ($score >= 80) {
        return ['level' => 'Excellent', 'color' => 'green'];
    } elseif ($score >= 60) {
        return ['level' => 'Good', 'color' => 'blue'];
    } elseif ($score >= 40) {
        return ['level' => 'Fair', 'color' => 'orange'];
    } else {
        return ['level' => 'Poor', 'color' => 'red'];
    }
}

function getMembershipLevel($type) {
    $levels = [
        'Regular' => 'Bronze',
        'Premium' => 'Silver',
        'VIP' => 'Gold'
    ];
    
    return $levels[$type] ?? 'Bronze';
}

function getLoanStatusDisplay($status) {
    $displays = [
        'Applied' => 'Applied',
        'Approved' => 'Approved',
        'Disbursed' => 'Active',
        'Active' => 'Active',
        'Completed' => 'Completed',
        'Default' => 'Default',
        'Cancelled' => 'Cancelled'
    ];
    
    return $displays[$status] ?? $status;
}

function calculateMembershipDays($joinDate) {
    return (strtotime(date('Y-m-d')) - strtotime($joinDate)) / 86400;
}

function getSavingsInterestRate($membershipType, $creditScore) {
    $baseRates = [
        'Regular' => 5.0,
        'Premium' => 6.0,
        'VIP' => 7.0
    ];
    
    $baseRate = $baseRates[$membershipType] ?? 5.0;
    
    // Adjust based on credit score
    if ($creditScore >= 80) {
        return $baseRate + 1.0;
    } elseif ($creditScore >= 60) {
        return $baseRate + 0.5;
    } else {
        return $baseRate;
    }
}

function generateMemberNumber() {
    return 'M' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function generateRandomPassword() {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
}

function generateTransactionNumber() {
    return 'TXN' . date('YmdHis') . mt_rand(100, 999);
}
?>
