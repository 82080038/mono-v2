<?php
/**
 * Phase 1 API - Member Management
 * KSP Lam Gabe Jaya v2.0
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/Config.php';

try {
    $db = Config::getDatabase();
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_member_types':
            getMemberTypes($db);
            break;
            
        case 'register_member':
            registerMember($db);
            break;
            
        case 'get_members':
            getMembers($db);
            break;
            
        case 'get_member':
            getMember($db);
            break;
            
        case 'update_member':
            updateMember($db);
            break;
            
        case 'upload_document':
            uploadDocument($db);
            break;
            
        case 'get_member_documents':
            getMemberDocuments($db);
            break;
            
        default:
            sendResponse(false, 'Invalid action', null, 400);
    }
    
} catch (Exception $e) {
    sendResponse(false, 'Server error: ' . $e->getMessage(), null, 500);
}

/**
 * Get member types
 */
function getMemberTypes($db) {
    $stmt = $db->prepare("SELECT * FROM member_types WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $memberTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse(true, 'Member types retrieved', $memberTypes);
}

/**
 * Register new member
 */
function registerMember($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['member_type_id', 'full_name', 'id_number', 'address', 'phone_number'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendResponse(false, "Field '$field' is required", null, 400);
            return;
        }
    }
    
    try {
        $db->beginTransaction();
        
        // Generate member number
        $memberNumber = generateMemberNumber($db);
        
        // Insert member
        $stmt = $db->prepare("
            INSERT INTO members (
                member_number, member_type_id, title, full_name, place_of_birth, 
                date_of_birth, gender, id_number, family_card_number, tax_id_number,
                phone_number, mobile_number, email, address, village, district, 
                city, province, postal_code, occupation, company_name, monthly_income,
                marital_status, spouse_name, spouse_phone, emergency_contact_name,
                emergency_contact_phone, emergency_contact_relation, registration_date,
                status, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_DATE, 'Active', ?)
        ");
        
        $stmt->execute([
            $memberNumber,
            $data['member_type_id'],
            $data['title'] ?? null,
            $data['full_name'],
            $data['place_of_birth'] ?? null,
            $data['date_of_birth'] ?? null,
            $data['gender'] ?? null,
            $data['id_number'],
            $data['family_card_number'] ?? null,
            $data['tax_id_number'] ?? null,
            $data['phone_number'],
            $data['mobile_number'] ?? null,
            $data['email'] ?? null,
            $data['address'],
            $data['village'] ?? null,
            $data['district'] ?? null,
            $data['city'] ?? null,
            $data['province'] ?? null,
            $data['postal_code'] ?? null,
            $data['occupation'] ?? null,
            $data['company_name'] ?? null,
            $data['monthly_income'] ?? null,
            $data['marital_status'] ?? null,
            $data['spouse_name'] ?? null,
            $data['spouse_phone'] ?? null,
            $data['emergency_contact_name'] ?? null,
            $data['emergency_contact_phone'] ?? null,
            $data['emergency_contact_relation'] ?? null,
            1 // created_by
        ]);
        
        $memberId = $db->lastInsertId();
        
        // Create default savings accounts
        createDefaultAccounts($db, $memberId, $data['member_type_id']);
        
        $db->commit();
        
        sendResponse(true, 'Member registered successfully', [
            'member_id' => $memberId,
            'member_number' => $memberNumber
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        sendResponse(false, 'Registration failed: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Get members list
 */
function getMembers($db) {
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $memberType = $_GET['member_type_id'] ?? '';
    
    $where = [];
    $params = [];
    
    if (!empty($search)) {
        $where[] = "(m.full_name LIKE ? OR m.member_number LIKE ? OR m.id_number LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($status)) {
        $where[] = "m.status = ?";
        $params[] = $status;
    }
    
    if (!empty($memberType)) {
        $where[] = "m.member_type_id = ?";
        $params[] = $memberType;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM members m $whereClause";
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get members
    $sql = "
        SELECT m.*, mt.name as member_type_name,
               COUNT(DISTINCT a.id) as total_accounts,
               COALESCE(SUM(a.balance), 0) as total_savings
        FROM members m
        LEFT JOIN member_types mt ON m.member_type_id = mt.id
        LEFT JOIN accounts a ON m.id = a.member_id AND a.status = 'Active'
        $whereClause
        GROUP BY m.id
        ORDER BY m.registration_date DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $db->prepare($sql);
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse(true, 'Members retrieved', [
        'data' => $members,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

/**
 * Get member details
 */
function getMember($db) {
    $memberId = intval($_GET['id'] ?? 0);
    
    if ($memberId <= 0) {
        sendResponse(false, 'Invalid member ID', null, 400);
        return;
    }
    
    $stmt = $db->prepare("
        SELECT m.*, mt.name as member_type_name
        FROM members m
        LEFT JOIN member_types mt ON m.member_type_id = mt.id
        WHERE m.id = ?
    ");
    $stmt->execute([$memberId]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        sendResponse(false, 'Member not found', null, 404);
        return;
    }
    
    // Get accounts
    $stmt = $db->prepare("
        SELECT a.*, at.name as account_type_name
        FROM accounts a
        LEFT JOIN account_types at ON a.account_type_id = at.id
        WHERE a.member_id = ?
        ORDER BY a.opening_date DESC
    ");
    $stmt->execute([$memberId]);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get loans
    $stmt = $db->prepare("
        SELECT l.*, lt.name as loan_type_name
        FROM loans l
        LEFT JOIN loan_types lt ON l.loan_type_id = lt.id
        WHERE l.member_id = ?
        ORDER BY l.application_date DESC
    ");
    $stmt->execute([$memberId]);
    $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $member['accounts'] = $accounts;
    $member['loans'] = $loans;
    
    sendResponse(true, 'Member details retrieved', $member);
}

/**
 * Update member
 */
function updateMember($db) {
    $memberId = intval($_GET['id'] ?? 0);
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($memberId <= 0) {
        sendResponse(false, 'Invalid member ID', null, 400);
        return;
    }
    
    // Check if member exists
    $stmt = $db->prepare("SELECT id FROM members WHERE id = ?");
    $stmt->execute([$memberId]);
    if (!$stmt->fetch()) {
        sendResponse(false, 'Member not found', null, 404);
        return;
    }
    
    // Build update query
    $updateFields = [];
    $params = [];
    
    $allowedFields = [
        'title', 'full_name', 'place_of_birth', 'date_of_birth', 'gender',
        'family_card_number', 'tax_id_number', 'phone_number', 'mobile_number',
        'email', 'address', 'village', 'district', 'city', 'province',
        'postal_code', 'occupation', 'company_name', 'monthly_income',
        'marital_status', 'spouse_name', 'spouse_phone',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation'
    ];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    
    if (empty($updateFields)) {
        sendResponse(false, 'No fields to update', null, 400);
        return;
    }
    
    $params[] = $memberId;
    
    $sql = "UPDATE members SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    sendResponse(true, 'Member updated successfully');
}

/**
 * Upload member document
 */
function uploadDocument($db) {
    $memberId = intval($_POST['member_id'] ?? 0);
    $documentType = $_POST['document_type'] ?? '';
    $documentNumber = $_POST['document_number'] ?? '';
    
    if ($memberId <= 0 || empty($documentType)) {
        sendResponse(false, 'Invalid member ID or document type', null, 400);
        return;
    }
    
    if (!isset($_FILES['document'])) {
        sendResponse(false, 'No file uploaded', null, 400);
        return;
    }
    
    $file = $_FILES['document'];
    $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Validate file
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);
    
    if (!in_array($extension, $allowedTypes)) {
        sendResponse(false, 'Invalid file type', null, 400);
        return;
    }
    
    if ($file['size'] > $maxSize) {
        sendResponse(false, 'File too large', null, 400);
        return;
    }
    
    // Generate unique filename
    $filename = 'member_' . $memberId . '_' . $documentType . '_' . time() . '.' . $extension;
    $uploadPath = '../uploads/documents/' . $filename;
    
    // Create directory if not exists
    if (!is_dir('../uploads/documents/')) {
        mkdir('../uploads/documents/', 0755, true);
    }
    
    // Upload file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        sendResponse(false, 'File upload failed', null, 500);
        return;
    }
    
    // Save to database
    $stmt = $db->prepare("
        INSERT INTO member_documents (
            member_id, document_type, document_number, document_path, 
            file_name, file_size, mime_type, upload_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_DATE)
    ");
    
    $stmt->execute([
        $memberId,
        $documentType,
        $documentNumber,
        $filename,
        $file['name'],
        $file['size'],
        $file['type']
    ]);
    
    sendResponse(true, 'Document uploaded successfully', [
        'document_id' => $db->lastInsertId(),
        'filename' => $filename
    ]);
}

/**
 * Get member documents
 */
function getMemberDocuments($db) {
    $memberId = intval($_GET['member_id'] ?? 0);
    
    if ($memberId <= 0) {
        sendResponse(false, 'Invalid member ID', null, 400);
        return;
    }
    
    $stmt = $db->prepare("
        SELECT * FROM member_documents 
        WHERE member_id = ? 
        ORDER BY upload_date DESC
    ");
    $stmt->execute([$memberId]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse(true, 'Documents retrieved', $documents);
}

/**
 * Generate member number
 */
function generateMemberNumber($db) {
    $year = date('Y');
    $month = date('m');
    
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM members 
        WHERE YEAR(registration_date) = ? AND MONTH(registration_date) = ?
    ");
    $stmt->execute([$year, $month]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    return 'KSP' . $year . $month . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}

/**
 * Create default accounts for new member
 */
function createDefaultAccounts($db, $memberId, $memberTypeId) {
    // Get member type requirements
    $stmt = $db->prepare("SELECT * FROM member_types WHERE id = ?");
    $stmt->execute([$memberTypeId]);
    $memberType = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get account types
    $stmt = $db->prepare("SELECT * FROM account_types WHERE is_active = 1");
    $stmt->execute();
    $accountTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($accountTypes as $accountType) {
        // Check if this account type should be created automatically
        if ($accountType['code'] === 'SA_POKOK' && $memberType['min_savings_pokok'] > 0) {
            createAccount($db, $memberId, $accountType['id'], $memberType['min_savings_pokok']);
        } elseif ($accountType['code'] === 'SA_WAJIB' && $memberType['min_savings_wajib'] > 0) {
            createAccount($db, $memberId, $accountType['id'], $memberType['min_savings_wajib']);
        }
    }
}

/**
 * Create account
 */
function createAccount($db, $memberId, $accountTypeId, $initialBalance = 0) {
    $accountNumber = generateAccountNumber($db);
    
    $stmt = $db->prepare("
        INSERT INTO accounts (
            account_number, member_id, account_type_id, account_name, 
            balance, available_balance, opening_date, status
        ) VALUES (?, ?, ?, ?, ?, ?, CURRENT_DATE, 'Active')
    ");
    
    $stmt->execute([
        $accountNumber,
        $memberId,
        $accountTypeId,
        'Account ' . $accountNumber,
        $initialBalance,
        $initialBalance
    ]);
    
    // Create initial transaction if balance > 0
    if ($initialBalance > 0) {
        $accountId = $db->lastInsertId();
        
        $stmt = $db->prepare("
            INSERT INTO account_transactions (
                account_id, transaction_type, amount, balance_before, balance_after,
                description, transaction_date
            ) VALUES (?, 'Deposit', ?, 0, ?, 'Initial deposit', CURRENT_TIMESTAMP)
        ");
        
        $stmt->execute([$accountId, $initialBalance, $initialBalance]);
    }
}

/**
 * Generate account number
 */
function generateAccountNumber($db) {
    $year = date('Y');
    
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM accounts a
        INNER JOIN account_types at ON a.account_type_id = at.id
        WHERE YEAR(a.opening_date) = ?
    ");
    $stmt->execute([$year]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    return 'ACC' . $year . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
}

/**
 * Send JSON response
 */
function sendResponse($success, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if (!$success) {
        $response['errors'] = [];
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}
