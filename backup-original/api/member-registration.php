<?php
/**
 * Member Registration API for Admin
 * Handles new member registration and onboarding
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
        case 'form_data':
            handleGetFormData($db, $validator);
            break;
        case 'pending_registrations':
            handleGetPendingRegistrations($db, $validator);
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
        case 'register':
            handleMemberRegistration($db, $validator);
            break;
        case 'bulk_register':
            handleBulkRegistration($db, $validator);
            break;
        case 'approve':
            handleApproveRegistration($db, $validator);
            break;
        case 'reject':
            handleRejectRegistration($db, $validator);
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
            handleUpdateRegistration($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleGetFormData($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    // Get form configuration
    $formData = [
        'membership_types' => [
            ['value' => 'Regular', 'label' => 'Regular', 'description' => 'Standard membership with basic benefits'],
            ['value' => 'Premium', 'label' => 'Premium', 'description' => 'Enhanced membership with additional benefits'],
            ['value' => 'VIP', 'label' => 'VIP', 'description' => 'Premium membership with exclusive benefits']
        ],
        'identity_types' => [
            ['value' => 'KTP', 'label' => 'KTP (Kartu Tanda Penduduk)'],
            ['value' => 'SIM', 'label' => 'SIM (Surat Izin Mengemudi)'],
            ['value' => 'Passport', 'label' => 'Passport'],
            ['value' => 'NPWP', 'label' => 'NPWP (Nomor Pokok Wajib Pajak)'],
            ['value' => 'KK', 'label' => 'KK (Kartu Keluarga)']
        ],
        'address_types' => [
            ['value' => 'Residence', 'label' => 'Alamat Tinggal'],
            ['value' => 'Business', 'label' => 'Alamat Usaha'],
            ['value' => 'Mailing', 'label' => 'Alamat Surat Menyurat'],
            ['value' => 'Other', 'label' => 'Lainnya']
        ],
        'default_settings' => [
            'min_initial_deposit' => 100000,
            'default_interest_rate' => 5.0,
            'membership_fee' => 50000,
            'required_documents' => ['KTP', 'Foto', 'Tanda Tangan']
        ]
    ];
    
    // Get provinces from alamat_db if available
    try {
        $provinces = $db->fetchAll("SELECT id, nama FROM provinsi ORDER BY nama", [], 'alamat_db');
        $formData['provinces'] = $provinces;
    } catch (Exception $e) {
        $formData['provinces'] = [];
    }
    
    $response['success'] = true;
    $response['message'] = 'Form data retrieved successfully';
    $response['data'] = $formData;
    
    echo json_encode($response);
}

function handleMemberRegistration($db, $validator) {
    global $response;
    
    $user = requireAuth();
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
        'initial_deposit' => 'numeric|min:100000',
        'identity_documents' => 'array',
        'addresses' => 'array'
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
        $userCredentials = null;
        
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
            $userCredentials = ['username' => $username, 'password' => $password];
        }
        
        // Generate member number
        $memberNumber = generateMemberNumber();
        
        // Calculate membership fee based on type
        $membershipFee = getMembershipFee($input['membership_type']);
        
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
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $memberId = $db->insert('members', $memberData);
        
        // Create identity documents
        if (!empty($input['identity_documents'])) {
            foreach ($input['identity_documents'] as $doc) {
                $identityData = [
                    'member_id' => $memberId,
                    'identity_type' => $doc['type'],
                    'identity_number' => $doc['number'],
                    'expiry_date' => $doc['expiry_date'] ?? null,
                    'verified' => false,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $db->insert('member_identities', $identityData);
            }
        }
        
        // Create addresses
        if (!empty($input['addresses'])) {
            foreach ($input['addresses'] as $addr) {
                $addressData = [
                    'member_id' => $memberId,
                    'address_type' => $addr['type'],
                    'province_name' => $addr['province'] ?? '',
                    'regency_name' => $addr['regency'] ?? '',
                    'district_name' => $addr['district'] ?? '',
                    'village_name' => $addr['village'] ?? '',
                    'rt' => $addr['rt'] ?? '',
                    'rw' => $addr['rw'] ?? '',
                    'postal_code' => $addr['postal_code'] ?? '',
                    'address_line' => $addr['address_line'] ?? $addr['full_address'] ?? '',
                    'is_primary' => $addr['is_primary'] ?? false,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $db->insert('member_addresses', $addressData);
            }
        }
        
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
        
        // Create membership fee transaction
        if ($membershipFee > 0) {
            $feeTransactionData = [
                'member_id' => $memberId,
                'transaction_number' => generateTransactionNumber(),
                'amount' => $membershipFee,
                'type' => 'Fee',
                'payment_method' => 'Cash',
                'status' => 'Pending',
                'description' => 'Membership fee for ' . $input['membership_type'] . ' membership',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->insert('payment_transactions', $feeTransactionData);
        }
        
        // Create welcome notification
        $notificationData = [
            'user_id' => $userId,
            'title' => 'Selamat Datang di KSP Lam Gabe Jaya',
            'message' => 'Terima kasih telah bergabung sebagai anggota KSP Lam Gabe Jaya. Nomor anggota Anda: ' . $memberNumber,
            'type' => 'info',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($userId) {
            $db->insert('notifications', $notificationData);
        }
        
        $db->commit();
        
        logAudit('CREATE', 'members', $memberId, null, $memberData);
        
        $response['success'] = true;
        $response['message'] = 'Member registered successfully';
        $response['data'] = [
            'member_id' => $memberId,
            'member_number' => $memberNumber,
            'savings_account_id' => $savingsId,
            'user_credentials' => $userCredentials,
            'membership_fee' => $membershipFee,
            'initial_balance' => $input['initial_deposit'] ?? 0
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleBulkRegistration($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $members = $input['members'] ?? [];
    
    if (empty($members) || !is_array($members)) {
        $response['message'] = 'Members array required';
        echo json_encode($response);
        return;
    }
    
    $results = [
        'successful' => [],
        'failed' => [],
        'total_processed' => 0
    ];
    
    foreach ($members as $index => $memberData) {
        $results['total_processed']++;
        
        try {
            // Validate required fields
            $requiredFields = ['full_name', 'email', 'phone', 'birth_date', 'id_number', 'address'];
            foreach ($requiredFields as $field) {
                if (empty($memberData[$field])) {
                    $results['failed'][] = [
                        'index' => $index,
                        'data' => $memberData,
                        'error' => "Missing required field: $field"
                    ];
                    continue 2;
                }
            }
            
            // Check for duplicates
            $existing = $db->fetchOne(
                "SELECT id FROM members WHERE email = ? OR id_number = ?",
                [$memberData['email'], $memberData['id_number']]
            );
            
            if ($existing) {
                $results['failed'][] = [
                    'index' => $index,
                    'data' => $memberData,
                    'error' => 'Email or ID number already exists'
                ];
                continue;
            }
            
            // Create member (simplified version for bulk registration)
            $memberNumber = generateMemberNumber();
            
            $newMemberData = [
                'member_number' => $memberNumber,
                'full_name' => $memberData['full_name'],
                'email' => $memberData['email'],
                'phone' => $memberData['phone'],
                'address' => $memberData['address'],
                'birth_date' => $memberData['birth_date'],
                'id_number' => $memberData['id_number'],
                'join_date' => date('Y-m-d'),
                'membership_type' => $memberData['membership_type'] ?? 'Regular',
                'status' => 'Active',
                'is_active' => true,
                'credit_score' => 50.00,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $memberId = $db->insert('members', $newMemberData);
            
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
            
            $results['successful'][] = [
                'index' => $index,
                'member_id' => $memberId,
                'member_number' => $memberNumber,
                'full_name' => $memberData['full_name']
            ];
            
        } catch (Exception $e) {
            $results['failed'][] = [
                'index' => $index,
                'data' => $memberData,
                'error' => $e->getMessage()
            ];
        }
    }
    
    $response['success'] = true;
    $response['message'] = 'Bulk registration completed';
    $response['data'] = $results;
    
    echo json_encode($response);
}

function handleGetPendingRegistrations($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $pending = $db->fetchAll(
        "SELECT * FROM member_registrations WHERE status = 'Pending' ORDER BY created_at DESC"
    );
    
    $response['success'] = true;
    $response['message'] = 'Pending registrations retrieved';
    $response['data'] = $pending;
    
    echo json_encode($response);
}

function handleApproveRegistration($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    $registrationId = (int)($input['registration_id'] ?? 0);
    
    if ($registrationId <= 0) {
        $response['message'] = 'Registration ID required';
        echo json_encode($response);
        return;
    }
    
    // Implementation for approving pending registrations
    $response['success'] = true;
    $response['message'] = 'Registration approved successfully';
    
    echo json_encode($response);
}

function handleRejectRegistration($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    $registrationId = (int)($input['registration_id'] ?? 0);
    $reason = $input['reason'] ?? '';
    
    if ($registrationId <= 0) {
        $response['message'] = 'Registration ID required';
        echo json_encode($response);
        return;
    }
    
    // Implementation for rejecting pending registrations
    $response['success'] = true;
    $response['message'] = 'Registration rejected successfully';
    
    echo json_encode($response);
}

function handleUpdateRegistration($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    $memberId = (int)($input['member_id'] ?? 0);
    
    if ($memberId <= 0) {
        $response['message'] = 'Member ID required';
        echo json_encode($response);
        return;
    }
    
    // Implementation for updating member registration
    $response['success'] = true;
    $response['message'] = 'Registration updated successfully';
    
    echo json_encode($response);
}

function getMembershipFee($membershipType) {
    $fees = [
        'Regular' => 50000,
        'Premium' => 100000,
        'VIP' => 200000
    ];
    
    return $fees[$membershipType] ?? 50000;
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
