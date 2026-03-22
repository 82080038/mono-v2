<?php
/**
 * Unified Router API
 * Handle all AJAX requests for unified dashboard
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/error-config.php';
require_once __DIR__ . '/auth.php';

// Start session
session_start();

// Authenticate user
function authenticateUser() {
    $token = $_POST['token'] ?? $_GET['token'] ?? $_SESSION['authToken'] ?? null;
    
    if (!$token) {
        throw new Exception('Authentication required');
    }
    
    try {
        $auth = new AuthSystem();
        $userData = $auth->validateToken($token);
        if (!$userData) {
            throw new Exception('Invalid token');
        }
        return $userData;
    } catch (Exception $e) {
        throw new Exception('Authentication failed: ' . $e->getMessage());
    }
}

// Get user role
function getUserRole($user) {
    return $user['role'] ?? 'member';
}

// Handle different actions
function handleRequest($action, $user) {
    $role = getUserRole($user);
    
    switch ($action) {
        case 'get_page_content':
            return handleGetPageContent($_POST['page'] ?? 'dashboard', $role, $user);
            
        case 'get_navigation':
            return handleGetNavigation($role);
            
        case 'get_dashboard_stats':
            return handleGetDashboardStats($role);
            
        case 'get_members':
            return handleGetMembers($role, $_POST);
            
        case 'get_loans':
            return handleGetLoans($role, $_POST);
            
        case 'get_member_detail':
            return handleGetMemberDetail($_POST['member_id'] ?? 0, $role);
            
        case 'get_loan_detail':
            return handleGetLoanDetail($_POST['loan_id'] ?? 0, $role);
            
        case 'save_member':
            return handleSaveMember($_POST, $role);
            
        case 'save_loan':
            return handleSaveLoan($_POST, $role);
            
        case 'delete_member':
            return handleDeleteMember($_POST['member_id'] ?? 0, $role);
            
        case 'delete_loan':
            return handleDeleteLoan($_POST['loan_id'] ?? 0, $role);
            
        default:
            throw new Exception('Unknown action: ' . $action);
    }
}

// Page content handlers
function handleGetPageContent($page, $role, $user) {
    // Include the unified dashboard file to reuse render functions
    include_once __DIR__ . '/../pages/unified-dashboard.php';
    
    return [
        'success' => true,
        'data' => [
            'content' => renderPageContent($page, $role, $user),
            'title' => ucfirst($page)
        ]
    ];
}

function handleGetNavigation($role) {
    include_once __DIR__ . '/../pages/unified-dashboard.php';
    
    return [
        'success' => true,
        'data' => [
            'navigation' => getMenusByRole($role)
        ]
    ];
}

function handleGetDashboardStats($role) {
    // Simulasi data - implementasi dengan query database
    return [
        'success' => true,
        'data' => [
            'total_members' => 150,
            'active_loans' => 45,
            'total_savings' => 250000000,
            'npl_count' => 3,
            'monthly_growth' => [
                'members' => 12,
                'loans' => 8,
                'savings' => 15
            ]
        ]
    ];
}

function handleGetMembers($role, $filters) {
    // Simulasi data - implementasi dengan query database
    $members = [
        [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'member_number' => '001',
            'status' => 'active',
            'join_date' => '2024-01-15',
            'phone' => '08123456789'
        ],
        [
            'id' => 2,
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'member_number' => '002',
            'status' => 'active',
            'join_date' => '2024-01-18',
            'phone' => '08123456790'
        ]
    ];
    
    // Apply filters
    $search = $filters['search'] ?? '';
    $status = $filters['status'] ?? 'all';
    
    if ($search) {
        $members = array_filter($members, function($member) use ($search) {
            return stripos($member['name'], $search) !== false || 
                   stripos($member['email'], $search) !== false ||
                   stripos($member['member_number'], $search) !== false;
        });
    }
    
    if ($status !== 'all') {
        $members = array_filter($members, function($member) use ($status) {
            return $member['status'] === $status;
        });
    }
    
    return [
        'success' => true,
        'data' => [
            'members' => array_values($members),
            'total' => count($members)
        ]
    ];
}

function handleGetLoans($role, $filters) {
    // Simulasi data
    $loans = [
        [
            'id' => 1,
            'loan_number' => 'LN001',
            'member_name' => 'John Doe',
            'amount' => 10000000,
            'status' => 'active',
            'application_date' => '2024-01-15',
            'disbursement_date' => '2024-01-16',
            'monthly_payment' => 500000
        ],
        [
            'id' => 2,
            'loan_number' => 'LN002',
            'member_name' => 'Jane Smith',
            'amount' => 15000000,
            'status' => 'pending',
            'application_date' => '2024-01-18',
            'disbursement_date' => null,
            'monthly_payment' => 750000
        ]
    ];
    
    // Apply filters
    $search = $filters['search'] ?? '';
    $status = $filters['status'] ?? 'all';
    
    if ($search) {
        $loans = array_filter($loans, function($loan) use ($search) {
            return stripos($loan['loan_number'], $search) !== false || 
                   stripos($loan['member_name'], $search) !== false;
        });
    }
    
    if ($status !== 'all') {
        $loans = array_filter($loans, function($loan) use ($status) {
            return $loan['status'] === $status;
        });
    }
    
    return [
        'success' => true,
        'data' => [
            'loans' => array_values($loans),
            'total' => count($loans)
        ]
    ];
}

function handleGetMemberDetail($memberId, $role) {
    // Simulasi data member detail
    $member = [
        'id' => $memberId,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'member_number' => '001',
        'status' => 'active',
        'join_date' => '2024-01-15',
        'phone' => '08123456789',
        'address' => 'Jl. Contoh No. 123, Jakarta',
        'birth_date' => '1990-01-01',
        'id_number' => '1234567890123456',
        'savings_balance' => 5000000,
        'active_loans' => 1,
        'total_loan_amount' => 10000000
    ];
    
    return [
        'success' => true,
        'data' => $member
    ];
}

function handleGetLoanDetail($loanId, $role) {
    // Simulasi data loan detail
    $loan = [
        'id' => $loanId,
        'loan_number' => 'LN001',
        'member_id' => 1,
        'member_name' => 'John Doe',
        'amount' => 10000000,
        'interest_rate' => 2.5,
        'term_months' => 24,
        'monthly_payment' => 500000,
        'status' => 'active',
        'application_date' => '2024-01-15',
        'disbursement_date' => '2024-01-16',
        'purpose' => 'Modal usaha',
        'collateral' => 'BPKB Motor',
        'guarantor' => 'Jane Smith',
        'paid_amount' => 1000000,
        'remaining_balance' => 9000000
    ];
    
    return [
        'success' => true,
        'data' => $loan
    ];
}

function handleSaveMember($data, $role) {
    // Validasi dan simpan member
    if (!in_array($role, ['admin', 'super_admin'])) {
        throw new Exception('Unauthorized');
    }
    
    $memberId = $data['member_id'] ?? null;
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    
    if (empty($name) || empty($email)) {
        throw new Exception('Name and email are required');
    }
    
    // Simulasi penyimpanan
    $success = true; // Implementasi penyimpanan ke database
    
    if ($success) {
        return [
            'success' => true,
            'message' => $memberId ? 'Member updated successfully' : 'Member added successfully',
            'data' => ['member_id' => $memberId ?: rand(1000, 9999)]
        ];
    } else {
        throw new Exception('Failed to save member');
    }
}

function handleSaveLoan($data, $role) {
    // Validasi dan simpan loan
    if (!in_array($role, ['admin', 'super_admin'])) {
        throw new Exception('Unauthorized');
    }
    
    $loanId = $data['loan_id'] ?? null;
    $memberId = $data['member_id'] ?? '';
    $amount = $data['amount'] ?? 0;
    
    if (empty($memberId) || $amount <= 0) {
        throw new Exception('Member and amount are required');
    }
    
    // Simulasi penyimpanan
    $success = true; // Implementasi penyimpanan ke database
    
    if ($success) {
        return [
            'success' => true,
            'message' => $loanId ? 'Loan updated successfully' : 'Loan added successfully',
            'data' => ['loan_id' => $loanId ?: 'LN' . rand(1000, 9999)]
        ];
    } else {
        throw new Exception('Failed to save loan');
    }
}

function handleDeleteMember($memberId, $role) {
    if (!in_array($role, ['admin', 'super_admin'])) {
        throw new Exception('Unauthorized');
    }
    
    if (empty($memberId)) {
        throw new Exception('Member ID is required');
    }
    
    // Simulasi penghapusan
    $success = true; // Implementasi penghapusan dari database
    
    if ($success) {
        return [
            'success' => true,
            'message' => 'Member deleted successfully'
        ];
    } else {
        throw new Exception('Failed to delete member');
    }
}

function handleDeleteLoan($loanId, $role) {
    if (!in_array($role, ['admin', 'super_admin'])) {
        throw new Exception('Unauthorized');
    }
    
    if (empty($loanId)) {
        throw new Exception('Loan ID is required');
    }
    
    // Simulasi penghapusan
    $success = true; // Implementasi penghapusan dari database
    
    if ($success) {
        return [
            'success' => true,
            'message' => 'Loan deleted successfully'
        ];
    } else {
        throw new Exception('Failed to delete loan');
    }
}

// Main execution
try {
    // Check if this is an AJAX request
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
        throw new Exception('This endpoint accepts AJAX requests only');
    }
    
    // Authenticate user
    $user = authenticateUser();
    
    // Get action
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    if (empty($action)) {
        throw new Exception('Action is required');
    }
    
    // Handle request
    $result = handleRequest($action, $user);
    
    // Return success response
    echo json_encode($result);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
