<?php
/**
 * Loan Application API for Members
 * Handles member loan application process
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
        case 'eligibility':
            handleEligibilityCheck($db, $validator);
            break;
        case 'loan_types':
            handleGetLoanTypes($db, $validator);
            break;
        case 'calculator':
            handleLoanCalculator($db, $validator);
            break;
        case 'applications':
            handleGetApplications($db, $validator);
            break;
        case 'detail':
            handleGetApplication($db, $validator);
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
        case 'apply':
            handleLoanApplication($db, $validator);
            break;
        case 'upload_document':
            handleDocumentUpload($db, $validator);
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
            handleUpdateApplication($db, $validator);
            break;
        case 'cancel':
            handleCancelApplication($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleEligibilityCheck($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    // Get member information
    $member = $db->fetchOne(
        "SELECT m.*, COUNT(l.id) as active_loans, COALESCE(SUM(s.balance), 0) as total_savings
         FROM members m 
         LEFT JOIN loans l ON m.id = l.member_id AND l.status IN ('Active', 'Disbursed')
         LEFT JOIN savings s ON m.id = s.member_id AND s.status = 'Active'
         WHERE m.user_id = ? AND m.status = 'Active'",
        [$user['id']]
    );
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    $eligibility = [
        'eligible' => true,
        'reasons' => [],
        'limits' => [
            'max_loan_amount' => calculateMaxLoanAmount($member),
            'max_term_months' => calculateMaxTerm($member),
            'min_down_payment' => 0
        ],
        'member_info' => [
            'credit_score' => $member['credit_score'],
            'membership_type' => $member['membership_type'],
            'active_loans' => $member['active_loans'],
            'total_savings' => $member['total_savings'],
            'membership_months' => calculateMembershipMonths($member['join_date'])
        ]
    ];
    
    // Check eligibility criteria
    if ($member['active_loans'] > 0) {
        $eligibility['eligible'] = false;
        $eligibility['reasons'][] = 'You already have an active loan';
    }
    
    if ($member['credit_score'] < 30) {
        $eligibility['eligible'] = false;
        $eligibility['reasons'][] = 'Credit score too low';
    }
    
    if ($member['total_savings'] < 100000) {
        $eligibility['eligible'] = false;
        $eligibility['reasons'][] = 'Minimum savings balance required (Rp 100,000)';
    }
    
    if (calculateMembershipMonths($member['join_date']) < 3) {
        $eligibility['eligible'] = false;
        $eligibility['reasons'][] = 'Minimum membership period required (3 months)';
    }
    
    $response['success'] = true;
    $response['message'] = 'Eligibility check completed';
    $response['data'] = $eligibility;
    
    echo json_encode($response);
}

function handleGetLoanTypes($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    // Get member information for loan type eligibility
    $member = $db->fetchOne(
        "SELECT credit_score, membership_type, total_savings 
         FROM members m 
         LEFT JOIN (SELECT member_id, COALESCE(SUM(balance), 0) as total_savings 
                   FROM savings WHERE status = 'Active' GROUP BY member_id) s ON m.id = s.member_id 
         WHERE m.user_id = ?",
        [$user['id']]
    );
    
    $loanTypes = [
        [
            'id' => 'personal',
            'name' => 'Personal Loan',
            'description' => 'Personal loan for various purposes',
            'min_amount' => 1000000,
            'max_amount' => calculateMaxLoanAmount($member),
            'min_term' => 6,
            'max_term' => 24,
            'interest_rate' => calculateInterestRate($member['credit_score'], 'personal'),
            'requirements' => [
                'Minimum 3 months membership',
                'Credit score 30+',
                'Savings balance Rp 100,000+'
            ],
            'eligible' => isEligibleForLoanType($member, 'personal')
        ],
        [
            'id' => 'business',
            'name' => 'Business Loan',
            'description' => 'Loan for business development',
            'min_amount' => 5000000,
            'max_amount' => calculateMaxLoanAmount($member) * 2,
            'min_term' => 12,
            'max_term' => 36,
            'interest_rate' => calculateInterestRate($member['credit_score'], 'business'),
            'requirements' => [
                'Minimum 6 months membership',
                'Credit score 40+',
                'Savings balance Rp 500,000+',
                'Business plan required'
            ],
            'eligible' => isEligibleForLoanType($member, 'business')
        ],
        [
            'id' => 'emergency',
            'name' => 'Emergency Loan',
            'description' => 'Quick loan for emergency needs',
            'min_amount' => 500000,
            'max_amount' => 2000000,
            'min_term' => 3,
            'max_term' => 12,
            'interest_rate' => calculateInterestRate($member['credit_score'], 'emergency'),
            'requirements' => [
                'Minimum 1 month membership',
                'Credit score 20+',
                'Valid emergency reason'
            ],
            'eligible' => isEligibleForLoanType($member, 'emergency')
        ],
        [
            'id' => 'education',
            'name' => 'Education Loan',
            'description' => 'Loan for education expenses',
            'min_amount' => 2000000,
            'max_amount' => calculateMaxLoanAmount($member) * 1.5,
            'min_term' => 12,
            'max_term' => 48,
            'interest_rate' => calculateInterestRate($member['credit_score'], 'education'),
            'requirements' => [
                'Minimum 6 months membership',
                'Credit score 35+',
                'Proof of enrollment required'
            ],
            'eligible' => isEligibleForLoanType($member, 'education')
        ]
    ];
    
    $response['success'] = true;
    $response['message'] = 'Loan types retrieved successfully';
    $response['data'] = $loanTypes;
    
    echo json_encode($response);
}

function handleLoanCalculator($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    $amount = (float)($_GET['amount'] ?? 0);
    $term = (int)($_GET['term'] ?? 0);
    $loanType = $_GET['loan_type'] ?? 'personal';
    
    if ($amount <= 0 || $term <= 0) {
        $response['message'] = 'Invalid amount or term';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne(
        "SELECT credit_score, membership_type 
         FROM members m 
         WHERE m.user_id = ?",
        [$user['id']]
    );
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    $interestRate = calculateInterestRate($member['credit_score'], $loanType);
    $totalInterest = ($amount * $interestRate / 100) * ($term / 12);
    $monthlyPayment = ($amount + $totalInterest) / $term;
    
    $calculation = [
        'loan_amount' => $amount,
        'term_months' => $term,
        'interest_rate' => $interestRate,
        'total_interest' => $totalInterest,
        'monthly_payment' => $monthlyPayment,
        'total_payment' => $amount + $totalInterest,
        'effective_rate' => calculateEffectiveRate($amount, $totalInterest, $term),
        'loan_type' => $loanType,
        'member_credit_score' => $member['credit_score']
    ];
    
    $response['success'] = true;
    $response['message'] = 'Loan calculation completed';
    $response['data'] = $calculation;
    
    echo json_encode($response);
}

function handleLoanApplication($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'loan_type' => 'required|in:personal,business,emergency,education',
        'amount' => 'required|numeric|min:500000',
        'term_months' => 'required|integer|min:3|max:48',
        'purpose' => 'required|string|min:10',
        'collateral_type' => 'in:None,Property,Vehicle,Guarantor,Other',
        'collateral_value' => 'numeric|min:0',
        'guarantor_name' => 'string|min:3',
        'guarantor_phone' => 'string|min:10',
        'guarantor_relationship' => 'string|min:3',
        'documents' => 'array'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne(
        "SELECT m.*, COUNT(l.id) as active_loans, COALESCE(SUM(s.balance), 0) as total_savings
         FROM members m 
         LEFT JOIN loans l ON m.id = l.member_id AND l.status IN ('Active', 'Disbursed')
         LEFT JOIN savings s ON m.id = s.member_id AND s.status = 'Active'
         WHERE m.user_id = ? AND m.status = 'Active'",
        [$user['id']]
    );
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Check eligibility
    if ($member['active_loans'] > 0) {
        $response['message'] = 'You already have an active loan';
        echo json_encode($response);
        return;
    }
    
    if (!isEligibleForLoanType($member, $input['loan_type'])) {
        $response['message'] = 'Not eligible for this loan type';
        echo json_encode($response);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Generate loan number
        $loanNumber = generateLoanNumber();
        
        // Calculate interest and monthly payment
        $interestRate = calculateInterestRate($member['credit_score'], $input['loan_type']);
        $totalInterest = ($input['amount'] * $interestRate / 100) * ($input['term_months'] / 12);
        $monthlyPayment = ($input['amount'] + $totalInterest) / $input['term_months'];
        
        // Create loan application
        $loanData = [
            'member_id' => $member['id'],
            'loan_number' => $loanNumber,
            'amount' => $input['amount'],
            'interest_rate' => $interestRate,
            'term_months' => $input['term_months'],
            'purpose' => $input['purpose'],
            'collateral_type' => $input['collateral_type'] ?? 'None',
            'collateral_value' => $input['collateral_value'] ?? 0,
            'status' => 'Applied',
            'monthly_payment' => $monthlyPayment,
            'total_interest' => $totalInterest,
            'remaining_balance' => $input['amount'] + $totalInterest,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $loanId = $db->insert('loans', $loanData);
        
        // Add guarantor information if provided
        if (!empty($input['guarantor_name'])) {
            $guarantorData = [
                'loan_id' => $loanId,
                'guarantor_name' => $input['guarantor_name'],
                'guarantor_phone' => $input['guarantor_phone'],
                'guarantor_relationship' => $input['guarantor_relationship'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->insert('loan_guarantors', $guarantorData);
        }
        
        // Handle document uploads
        if (!empty($input['documents'])) {
            foreach ($input['documents'] as $doc) {
                $docData = [
                    'loan_id' => $loanId,
                    'document_type' => $doc['type'],
                    'file_name' => $doc['filename'],
                    'file_path' => $doc['path'],
                    'uploaded_at' => date('Y-m-d H:i:s')
                ];
                
                $db->insert('loan_documents', $docData);
            }
        }
        
        // Create notification for admin
        $adminUsers = $db->fetchAll("SELECT id FROM users WHERE role = 'admin' AND is_active = 1");
        foreach ($adminUsers as $admin) {
            $db->insert('notifications', [
                'user_id' => $admin['id'],
                'title' => 'New Loan Application',
                'message' => "New loan application from {$member['full_name']} ({$member['member_number']})",
                'type' => 'info',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        $db->commit();
        
        $response['success'] = true;
        $response['message'] = 'Loan application submitted successfully';
        $response['data'] = [
            'loan_id' => $loanId,
            'loan_number' => $loanNumber,
            'interest_rate' => $interestRate,
            'monthly_payment' => $monthlyPayment,
            'total_interest' => $totalInterest,
            'status' => 'Applied'
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleGetApplications($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $status = $_GET['status'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    $whereConditions = ["l.member_id = ?"];
    $params = [$member['id']];
    
    if (!empty($status)) {
        $whereConditions[] = "l.status = ?";
        $params[] = $status;
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM loans l $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get applications
    $sql = "SELECT l.*, 
                    (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE loan_id = l.id AND status = 'Completed') as paid_amount
             FROM loans l 
             $whereClause
             ORDER BY l.created_at DESC 
             LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $applications = $db->fetchAll($sql, $params);
    
    // Calculate remaining balance for each loan
    foreach ($applications as &$app) {
        $app['remaining_balance'] = ($app['amount'] + ($app['total_interest'] ?? 0)) - $app['paid_amount'];
    }
    
    $response['success'] = true;
    $response['message'] = 'Applications retrieved successfully';
    $response['data'] = [
        'applications' => $applications,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    echo json_encode($response);
}

function handleGetApplication($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $applicationId = (int)($_GET['id'] ?? 0);
    
    if ($applicationId <= 0) {
        $response['message'] = 'Application ID required';
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
    
    // Get application details
    $application = $db->fetchOne(
        "SELECT l.*, 
                (SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE loan_id = l.id AND status = 'Completed') as paid_amount
         FROM loans l 
         WHERE l.id = ? AND l.member_id = ?",
        [$applicationId, $member['id']]
    );
    
    if (!$application) {
        $response['message'] = 'Application not found';
        echo json_encode($response);
        return;
    }
    
    $application['remaining_balance'] = ($application['amount'] + ($application['total_interest'] ?? 0)) - $application['paid_amount'];
    
    // Get guarantor information
    $application['guarantor'] = $db->fetchOne(
        "SELECT * FROM loan_guarantors WHERE loan_id = ?",
        [$applicationId]
    );
    
    // Get documents
    $application['documents'] = $db->fetchAll(
        "SELECT * FROM loan_documents WHERE loan_id = ? ORDER BY uploaded_at DESC",
        [$applicationId]
    );
    
    // Get payment history
    $application['payment_history'] = $db->fetchAll(
        "SELECT * FROM payment_transactions WHERE loan_id = ? ORDER BY created_at DESC",
        [$applicationId]
    );
    
    $response['success'] = true;
    $response['message'] = 'Application retrieved successfully';
    $response['data'] = $application;
    
    echo json_encode($response);
}

function handleUpdateApplication($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    $applicationId = (int)($input['id'] ?? 0);
    
    if ($applicationId <= 0) {
        $response['message'] = 'Application ID required';
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
    
    // Get current application
    $currentApplication = $db->fetchOne(
        "SELECT * FROM loans WHERE id = ? AND member_id = ?",
        [$applicationId, $member['id']]
    );
    
    if (!$currentApplication) {
        $response['message'] = 'Application not found';
        echo json_encode($response);
        return;
    }
    
    // Only allow updating certain fields for applied status
    if ($currentApplication['status'] === 'Applied') {
        $allowedFields = ['purpose', 'collateral_type', 'collateral_value'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updateData[$field] = $input[$field];
            }
        }
        
        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            $db->update('loans', $updateData, 'id = ?', [$applicationId]);
        }
        
        $response['success'] = true;
        $response['message'] = 'Application updated successfully';
        $response['data'] = $updateData;
    } else {
        $response['message'] = 'Cannot update application in current status';
    }
    
    echo json_encode($response);
}

function handleCancelApplication($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    $applicationId = (int)($input['id'] ?? 0);
    $reason = $input['reason'] ?? '';
    
    if ($applicationId <= 0) {
        $response['message'] = 'Application ID required';
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
    
    // Get current application
    $currentApplication = $db->fetchOne(
        "SELECT * FROM loans WHERE id = ? AND member_id = ?",
        [$applicationId, $member['id']]
    );
    
    if (!$currentApplication) {
        $response['message'] = 'Application not found';
        echo json_encode($response);
        return;
    }
    
    // Only allow cancellation for Applied status
    if ($currentApplication['status'] !== 'Applied') {
        $response['message'] = 'Cannot cancel application in current status';
        echo json_encode($response);
        return;
    }
    
    $updateData = [
        'status' => 'Cancelled',
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $db->update('loans', $updateData, 'id = ?', [$applicationId]);
    
    $response['success'] = true;
    $response['message'] = 'Application cancelled successfully';
    
    echo json_encode($response);
}

function handleDocumentUpload($db, $validator) {
    global $response;
    
    $user = requireAuth('member');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'loan_id' => 'required|integer',
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
    
    // Verify loan ownership
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    $loan = $db->fetchOne("SELECT id FROM loans WHERE id = ? AND member_id = ?", [$input['loan_id'], $member['id']]);
    if (!$loan) {
        $response['message'] = 'Loan not found';
        echo json_encode($response);
        return;
    }
    
    $docData = [
        'loan_id' => $input['loan_id'],
        'document_type' => $input['document_type'],
        'file_name' => $input['file_name'],
        'file_path' => $input['file_path'],
        'uploaded_at' => date('Y-m-d H:i:s')
    ];
    
    $docId = $db->insert('loan_documents', $docData);
    
    $response['success'] = true;
    $response['message'] = 'Document uploaded successfully';
    $response['data'] = ['document_id' => $docId];
    
    echo json_encode($response);
}

// Helper functions
function calculateMaxLoanAmount($member) {
    $baseAmount = 50000000; // 50 million base
    
    // Adjust based on credit score
    if ($member['credit_score'] >= 80) {
        return $baseAmount * 2; // 100 million
    } elseif ($member['credit_score'] >= 60) {
        return $baseAmount * 1.5; // 75 million
    } elseif ($member['credit_score'] >= 40) {
        return $baseAmount; // 50 million
    } else {
        return $baseAmount * 0.5; // 25 million
    }
}

function calculateMaxTerm($member) {
    if ($member['membership_type'] === 'VIP') {
        return 48; // 4 years
    } elseif ($member['membership_type'] === 'Premium') {
        return 36; // 3 years
    } else {
        return 24; // 2 years
    }
}

function calculateMembershipMonths($joinDate) {
    return (strtotime(date('Y-m-d')) - strtotime($joinDate)) / (30 * 24 * 60 * 60);
}

function calculateInterestRate($creditScore, $loanType) {
    $baseRates = [
        'personal' => 12,
        'business' => 14,
        'emergency' => 15,
        'education' => 10
    ];
    
    $baseRate = $baseRates[$loanType] ?? 12;
    
    // Adjust based on credit score
    if ($creditScore >= 80) {
        return $baseRate - 3;
    } elseif ($creditScore >= 60) {
        return $baseRate - 2;
    } elseif ($creditScore >= 40) {
        return $baseRate - 1;
    } else {
        return $baseRate + 2;
    }
}

function isEligibleForLoanType($member, $loanType) {
    switch ($loanType) {
        case 'personal':
            return $member['credit_score'] >= 30 && 
                   $member['total_savings'] >= 100000 && 
                   calculateMembershipMonths($member['join_date']) >= 3;
            
        case 'business':
            return $member['credit_score'] >= 40 && 
                   $member['total_savings'] >= 500000 && 
                   calculateMembershipMonths($member['join_date']) >= 6;
            
        case 'emergency':
            return $member['credit_score'] >= 20 && 
                   calculateMembershipMonths($member['join_date']) >= 1;
            
        case 'education':
            return $member['credit_score'] >= 35 && 
                   $member['total_savings'] >= 200000 && 
                   calculateMembershipMonths($member['join_date']) >= 6;
            
        default:
            return false;
    }
}

function calculateEffectiveRate($principal, $totalInterest, $term) {
    $totalPayment = $principal + $totalInterest;
    return (($totalInterest / $principal) / ($term / 12)) * 100;
}

function generateLoanNumber() {
    return 'LN' . date('Ym') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}
?>
