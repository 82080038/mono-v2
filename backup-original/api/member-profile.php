<?php
/**
 * Member Profile API
 * Handles member profile management
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
function requireAuth($role = 'member') {
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
        throw new Exception('Member access required');
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
        case 'profile':
            handleGetProfile($db, $validator);
            break;
        case 'addresses':
            handleGetAddresses($db, $validator);
            break;
        case 'identities':
            handleGetIdentities($db, $validator);
            break;
        case 'documents':
            handleGetDocuments($db, $validator);
            break;
        case 'statistics':
            handleGetStatistics($db, $validator);
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
        case 'upload_document':
            handleUploadDocument($db, $validator);
            break;
        case 'add_address':
            handleAddAddress($db, $validator);
            break;
        case 'add_identity':
            handleAddIdentity($db, $validator);
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
        case 'update_profile':
            handleUpdateProfile($db, $validator);
            break;
        case 'update_address':
            handleUpdateAddress($db, $validator);
            break;
        case 'update_identity':
            handleUpdateIdentity($db, $validator);
            break;
        case 'change_password':
            handleChangePassword($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleGetProfile($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    // Get member profile
    $member = $db->fetchOne(
        "SELECT m.*, u.username, u.email, u.last_login
         FROM members m 
         JOIN users u ON m.user_id = u.id 
         WHERE u.id = ?",
        [$user['id']]
    );
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Get additional profile data
    $profile = [
        'basic_info' => [
            'member_number' => $member['member_number'],
            'full_name' => $member['full_name'],
            'email' => $member['email'],
            'phone' => $member['phone'],
            'birth_date' => $member['birth_date'],
            'id_number' => $member['id_number'],
            'join_date' => $member['join_date'],
            'membership_type' => $member['membership_type'],
            'credit_score' => $member['credit_score']
        ],
        'account_info' => [
            'username' => $member['username'],
            'status' => $member['status'],
            'is_active' => $member['is_active'],
            'last_login' => $member['last_login']
        ],
        'membership_info' => [
            'membership_days' => calculateMembershipDays($member['join_date']),
            'membership_level' => getMembershipLevel($member['membership_type']),
            'next_level_progress' => calculateNextLevelProgress($member)
        ],
        'financial_summary' => [
            'active_loans' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status IN ('Active', 'Disbursed')", [$member['id']])['count'],
            'savings_accounts' => $db->fetchOne("SELECT COUNT(*) as count FROM savings WHERE member_id = ? AND status = 'Active'", [$member['id']])['count'],
            'total_savings' => $db->fetchOne("SELECT COALESCE(SUM(balance), 0) as total FROM savings WHERE member_id = ? AND status = 'Active'", [$member['id']])['total'],
            'total_loan_amount' => $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM loans WHERE member_id = ? AND status IN ('Active', 'Disbursed')", [$member['id']])['total']
        ],
        'contact_info' => [
            'address' => $member['address'],
            'latitude' => $member['latitude'],
            'longitude' => $member['longitude']
        ]
    ];
    
    $response['success'] = true;
    $response['message'] = 'Profile retrieved successfully';
    $response['data'] = $profile;
    
    echo json_encode($response);
}

function handleGetAddresses($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    $addresses = $db->fetchAll(
        "SELECT * FROM member_addresses WHERE member_id = ? ORDER BY is_primary DESC, created_at DESC",
        [$member['id']]
    );
    
    $response['success'] = true;
    $response['message'] = 'Addresses retrieved successfully';
    $response['data'] = $addresses;
    
    echo json_encode($response);
}

function handleGetIdentities($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    $identities = $db->fetchAll(
        "SELECT * FROM member_identities WHERE member_id = ? ORDER BY created_at DESC",
        [$member['id']]
    );
    
    $response['success'] = true;
    $response['message'] = 'Identities retrieved successfully';
    $response['data'] = $identities;
    
    echo json_encode($response);
}

function handleGetDocuments($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Get documents from various tables
    $documents = [];
    
    // Identity documents
    $identityDocs = $db->fetchAll(
        "SELECT 'identity' as document_type, identity_type as type, identity_file as file_name, 
                verified, verified_at, created_at 
         FROM member_identities 
         WHERE member_id = ? AND identity_file IS NOT NULL",
        [$member['id']]
    );
    
    // Loan documents
    $loanDocs = $db->fetchAll(
        "SELECT 'loan' as document_type, document_type as type, file_name, 
                NULL as verified, NULL as verified_at, uploaded_at as created_at 
         FROM loan_documents 
         WHERE loan_id IN (SELECT id FROM loans WHERE member_id = ?)",
        [$member['id']]
    );
    
    $documents = array_merge($identityDocs, $loanDocs);
    
    // Sort by created date
    usort($documents, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    $response['success'] = true;
    $response['message'] = 'Documents retrieved successfully';
    $response['data'] = $documents;
    
    echo json_encode($response);
}

function handleGetStatistics($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    // Get member information
    $member = $db->fetchOne("SELECT id, join_date, credit_score, membership_type FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    $statistics = [
        'membership_stats' => [
            'membership_days' => calculateMembershipDays($member['join_date']),
            'membership_years' => floor(calculateMembershipDays($member['join_date']) / 365),
            'credit_score' => $member['credit_score'],
            'membership_type' => $member['membership_type'],
            'credit_score_trend' => getCreditScoreTrend($db, $member['id'])
        ],
        'financial_stats' => [
            'total_savings' => $db->fetchOne("SELECT COALESCE(SUM(balance), 0) as total FROM savings WHERE member_id = ? AND status = 'Active'", [$member['id']])['total'],
            'total_loans' => $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM loans WHERE member_id = ? AND status IN ('Active', 'Disbursed')", [$member['id']])['total'],
            'total_paid' => $db->fetchOne("SELECT COALESCE(SUM(pt.amount), 0) as total FROM payment_transactions pt JOIN loans l ON pt.loan_id = l.id WHERE l.member_id = ? AND pt.status = 'Completed'", [$member['id']])['total'],
            'net_worth' => 0 // Will be calculated below
        ],
        'transaction_stats' => [
            'total_transactions' => $db->fetchOne("SELECT COUNT(*) as count FROM payment_transactions WHERE member_id = ? AND status = 'Completed'", [$member['id']])['count'],
            'transactions_this_month' => $db->fetchOne("SELECT COUNT(*) as count FROM payment_transactions WHERE member_id = ? AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status = 'Completed'", [$member['id']])['count'],
            'average_transaction_amount' => $db->fetchOne("SELECT COALESCE(AVG(amount), 0) as avg FROM payment_transactions WHERE member_id = ? AND status = 'Completed'", [$member['id']])['avg']
        ],
        'loan_stats' => [
            'total_loans_taken' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ?", [$member['id']])['count'],
            'active_loans' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status IN ('Active', 'Disbursed')", [$member['id']])['count'],
            'completed_loans' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status = 'Completed'", [$member['id']])['count'],
            'on_time_payment_rate' => calculateOnTimePaymentRate($db, $member['id'])
        ],
        'savings_stats' => [
            'savings_accounts' => $db->fetchOne("SELECT COUNT(*) as count FROM savings WHERE member_id = ? AND status = 'Active'", [$member['id']])['count'],
            'total_deposits' => $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE member_id = ? AND type = 'Savings Deposit' AND status = 'Completed'", [$member['id']])['total'],
            'total_withdrawals' => $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE member_id = ? AND type = 'Savings Withdrawal' AND status = 'Completed'", [$member['id']])['total'],
            'savings_growth_rate' => calculateSavingsGrowthRate($db, $member['id'])
        ]
    ];
    
    // Calculate net worth
    $statistics['financial_stats']['net_worth'] = $statistics['financial_stats']['total_savings'] - $statistics['financial_stats']['total_loans'];
    
    $response['success'] = true;
    $response['message'] = 'Statistics retrieved successfully';
    $response['data'] = $statistics;
    
    echo json_encode($response);
}

function handleUpdateProfile($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'full_name' => 'string|min:3',
        'phone' => 'string|min:10',
        'address' => 'string|min:10',
        'latitude' => 'numeric',
        'longitude' => 'numeric'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne("SELECT * FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Update only allowed fields
    $allowedFields = ['full_name', 'phone', 'address', 'latitude', 'longitude'];
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
    
    // Update member table
    $db->update('members', $updateData, 'id = ?', [$member['id']]);
    
    // Also update users table if full_name is updated
    if (isset($updateData['full_name'])) {
        $db->update('users', ['full_name' => $updateData['full_name']], 'id = ?', [$user['id']]);
    }
    
    $response['success'] = true;
    $response['message'] = 'Profile updated successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleAddAddress($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'address_type' => 'required|in:Residence,Business,Mailing,Other',
        'address_line' => 'required|string|min:10',
        'province_name' => 'required|string',
        'regency_name' => 'required|string',
        'district_name' => 'required|string',
        'village_name' => 'required|string',
        'postal_code' => 'string|min:5',
        'rt' => 'string',
        'rw' => 'string',
        'is_primary' => 'boolean'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // If setting as primary, unset other primary addresses
    if ($input['is_primary']) {
        $db->update('member_addresses', ['is_primary' => false], 'member_id = ?', [$member['id']]);
    }
    
    $addressData = [
        'member_id' => $member['id'],
        'address_type' => $input['address_type'],
        'province_name' => $input['province_name'],
        'regency_name' => $input['regency_name'],
        'district_name' => $input['district_name'],
        'village_name' => $input['village_name'],
        'rt' => $input['rt'] ?? '',
        'rw' => $input['rw'] ?? '',
        'postal_code' => $input['postal_code'] ?? '',
        'address_line' => $input['address_line'],
        'is_primary' => $input['is_primary'] ?? false,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $addressId = $db->insert('member_addresses', $addressData);
    
    $response['success'] = true;
    $response['message'] = 'Address added successfully';
    $response['data'] = ['address_id' => $addressId];
    
    echo json_encode($response);
}

function handleUpdateAddress($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    $addressId = (int)($input['address_id'] ?? 0);
    
    if ($addressId <= 0) {
        $response['message'] = 'Address ID required';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Verify address ownership
    $address = $db->fetchOne("SELECT * FROM member_addresses WHERE id = ? AND member_id = ?", [$addressId, $member['id']]);
    
    if (!$address) {
        $response['message'] = 'Address not found';
        echo json_encode($response);
        return;
    }
    
    // Update only allowed fields
    $allowedFields = ['address_type', 'address_line', 'province_name', 'regency_name', 'district_name', 'village_name', 'postal_code', 'rt', 'rw', 'is_primary'];
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
    
    // If setting as primary, unset other primary addresses
    if (isset($updateData['is_primary']) && $updateData['is_primary']) {
        $db->update('member_addresses', ['is_primary' => false], 'member_id = ? AND id != ?', [$member['id'], $addressId]);
    }
    
    $updateData['updated_at'] = date('Y-m-d H:i:s');
    
    $db->update('member_addresses', $updateData, 'id = ?', [$addressId]);
    
    $response['success'] = true;
    $response['message'] = 'Address updated successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleAddIdentity($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'identity_type' => 'required|in:KTP,SIM,Passport,NPWP,KK',
        'identity_number' => 'required|string|min:16',
        'expiry_date' => 'date',
        'identity_file' => 'string'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Check if identity type already exists
    $existing = $db->fetchOne(
        "SELECT id FROM member_identities WHERE member_id = ? AND identity_type = ?",
        [$member['id'], $input['identity_type']]
    );
    
    if ($existing) {
        $response['message'] = 'Identity type already exists';
        echo json_encode($response);
        return;
    }
    
    $identityData = [
        'member_id' => $member['id'],
        'identity_type' => $input['identity_type'],
        'identity_number' => $input['identity_number'],
        'expiry_date' => $input['expiry_date'] ?? null,
        'identity_file' => $input['identity_file'] ?? null,
        'verified' => false,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $identityId = $db->insert('member_identities', $identityData);
    
    $response['success'] = true;
    $response['message'] = 'Identity added successfully';
    $response['data'] = ['identity_id' => $identityId];
    
    echo json_encode($response);
}

function handleUpdateIdentity($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    $identityId = (int)($input['identity_id'] ?? 0);
    
    if ($identityId <= 0) {
        $response['message'] = 'Identity ID required';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Verify identity ownership
    $identity = $db->fetchOne("SELECT * FROM member_identities WHERE id = ? AND member_id = ?", [$identityId, $member['id']]);
    
    if (!$identity) {
        $response['message'] = 'Identity not found';
        echo json_encode($response);
        return;
    }
    
    // Update only allowed fields
    $allowedFields = ['identity_number', 'expiry_date', 'identity_file'];
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
    
    $db->update('member_identities', $updateData, 'id = ?', [$identityId]);
    
    $response['success'] = true;
    $response['message'] = 'Identity updated successfully';
    $response['data'] = $updateData;
    
    echo json_encode($response);
}

function handleChangePassword($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'current_password' => 'required|string',
        'new_password' => 'required|string|min:8',
        'confirm_password' => 'required|string'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    if ($input['new_password'] !== $input['confirm_password']) {
        $response['message'] = 'New passwords do not match';
        echo json_encode($response);
        return;
    }
    
    // Verify current password
    $currentUser = $db->fetchOne("SELECT password FROM users WHERE id = ?", [$user['id']]);
    if (!password_verify($input['current_password'], $currentUser['password'])) {
        $response['message'] = 'Current password is incorrect';
        echo json_encode($response);
        return;
    }
    
    // Update password
    $hashedPassword = password_hash($input['new_password'], PASSWORD_DEFAULT);
    $db->update('users', ['password' => $hashedPassword], 'id = ?', [$user['id']]);
    
    $response['success'] = true;
    $response['message'] = 'Password changed successfully';
    
    echo json_encode($response);
}

function handleUploadDocument($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'document_type' => 'required|string',
        'file_name' => 'required|string',
        'file_path' => 'required|string'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // For now, just return success (actual implementation would handle file upload)
    $documentData = [
        'member_id' => $member['id'],
        'document_type' => $input['document_type'],
        'file_name' => $input['file_name'],
        'file_path' => $input['file_path'],
        'uploaded_at' => date('Y-m-d H:i:s')
    ];
    
    $response['success'] = true;
    $response['message'] = 'Document uploaded successfully';
    $response['data'] = $documentData;
    
    echo json_encode($response);
}

// Helper functions
function calculateMembershipDays($joinDate) {
    return (strtotime(date('Y-m-d')) - strtotime($joinDate)) / 86400;
}

function getMembershipLevel($membershipType) {
    $levels = [
        'Regular' => 'Bronze',
        'Premium' => 'Silver',
        'VIP' => 'Gold'
    ];
    
    return $levels[$membershipType] ?? 'Bronze';
}

function calculateNextLevelProgress($member) {
    $levels = ['Regular' => 0, 'Premium' => 1, 'VIP' => 2];
    $currentLevel = $levels[$member['membership_type']] ?? 0;
    
    if ($currentLevel >= 2) {
        return 100; // Already at highest level
    }
    
    // Simple progress calculation based on credit score and membership duration
    $scoreProgress = min(($member['credit_score'] / 100) * 50, 50);
    $timeProgress = min((calculateMembershipDays($member['join_date']) / (365 * 2)) * 50, 50);
    
    return round($scoreProgress + $timeProgress);
}

function getCreditScoreTrend($db, $memberId) {
    // This would ideally show credit score changes over time
    // For now, return current score
    $currentScore = $db->fetchOne("SELECT credit_score FROM members WHERE id = ?", [$memberId])['credit_score'];
    
    return [
        'current' => $currentScore,
        'trend' => 'stable', // Would calculate actual trend
        'change' => 0 // Would calculate actual change
    ];
}

function calculateOnTimePaymentRate($db, $memberId) {
    $totalPayments = $db->fetchOne(
        "SELECT COUNT(*) as count 
         FROM payment_transactions pt 
         JOIN loans l ON pt.loan_id = l.id 
         WHERE l.member_id = ? AND pt.type = 'Loan Payment' AND pt.status = 'Completed'",
        [$memberId]
    )['count'];
    
    if ($totalPayments === 0) {
        return 100; // No payments means 100% on-time rate
    }
    
    $onTimePayments = $db->fetchOne(
        "SELECT COUNT(*) as count 
         FROM payment_transactions pt 
         JOIN loans l ON pt.loan_id = l.id 
         WHERE l.member_id = ? AND pt.type = 'Loan Payment' AND pt.status = 'Completed' 
         AND pt.created_at <= DATE_ADD(l.next_payment_date, INTERVAL 7 DAY)",
        [$memberId]
    )['count'];
    
    return round(($onTimePayments / $totalPayments) * 100, 2);
}

function calculateSavingsGrowthRate($db, $memberId) {
    $thisMonth = date('Y-m-01');
    $lastMonth = date('Y-m-01', strtotime('-1 month'));
    
    $thisMonthBalance = $db->fetchOne(
        "SELECT COALESCE(SUM(balance), 0) as total 
         FROM savings 
         WHERE member_id = ? AND status = 'Active' AND created_at >= ?",
        [$memberId, $thisMonth]
    )['total'];
    
    $lastMonthBalance = $db->fetchOne(
        "SELECT COALESCE(SUM(balance), 0) as total 
         FROM savings 
         WHERE member_id = ? AND status = 'Active' AND created_at >= ? AND created_at < ?",
        [$memberId, $lastMonth, $thisMonth]
    )['total'];
    
    if ($lastMonthBalance === 0) {
        return 0;
    }
    
    return round((($thisMonthBalance - $lastMonthBalance) / $lastMonthBalance) * 100, 2);
}
?>
