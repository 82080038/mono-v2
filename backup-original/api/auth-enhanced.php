<?php
/**
 * Enhanced Authentication System
 * Supports JWT tokens, role-based access control, and session management
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

// Handle API requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? '';

try {
    switch ($method) {
        case 'POST':
            handlePostRequest($action, $db, $validator);
            break;
        case 'GET':
            handleGetRequest($action, $db, $validator);
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

function handlePostRequest($action, $db, $validator) {
    global $response, $securityLogger;
    
    switch ($action) {
        case 'login':
            handleLogin($db, $validator);
            break;
        case 'register':
            handleRegistration($db, $validator);
            break;
        case 'logout':
            handleLogout($db, $validator);
            break;
        case 'refresh_token':
            handleTokenRefresh($db, $validator);
            break;
        case 'forgot_password':
            handleForgotPassword($db, $validator);
            break;
        case 'reset_password':
            handleResetPassword($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleGetRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'verify_token':
            handleTokenVerification($db, $validator);
            break;
        case 'profile':
            handleGetProfile($db, $validator);
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
        case 'change_password':
            handleChangePassword($db, $validator);
            break;
        case 'update_profile':
            handleUpdateProfile($db, $validator);
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
        case 'delete_account':
            handleDeleteAccount($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleLogin($db, $validator) {
    global $response, $securityLogger;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'username' => 'required|string|min:3',
        'password' => 'required|string|min:6'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    $username = $input['username'];
    $password = $input['password'];
    
    // Get user from database
    $user = $db->fetchOne(
        "SELECT u.*, m.member_number, m.full_name as member_name, m.membership_type 
         FROM users u 
         LEFT JOIN members m ON u.id = m.user_id 
         WHERE u.username = ? AND u.status = 'Active'",
        [$username]
    );
    
    if (!$user) {
        $securityLogger->logLoginAttempt($username, false, 'User not found');
        $response['message'] = 'Invalid credentials';
        echo json_encode($response);
        return;
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        $securityLogger->logLoginAttempt($username, false, 'Invalid password');
        $response['message'] = 'Invalid credentials';
        echo json_encode($response);
        return;
    }
    
    // Generate JWT token
    $token = generateJWTToken($user);
    
    // Update last login
    $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
    
    // Log successful login
    $securityLogger->logLoginAttempt($username, true);
    
    // Remove sensitive data
    unset($user['password']);
    
    $response['success'] = true;
    $response['message'] = 'Login successful';
    $response['data'] = [
        'user' => $user,
        'token' => $token,
        'expires_in' => Config::JWT_EXPIRY
    ];
    
    echo json_encode($response);
}

function handleRegistration($db, $validator) {
    global $response, $securityLogger;
    
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
    }
    
    // Log registration
    $securityLogger->logSecurityEvent('USER_REGISTRATION', [
        'user_id' => $userId,
        'username' => $input['username'],
        'email' => $input['email'],
        'role' => $input['role']
    ]);
    
    $response['success'] = true;
    $response['message'] = 'Registration successful';
    $response['data'] = ['user_id' => $userId];
    
    echo json_encode($response);
}

function handleLogout($db, $validator) {
    global $response;
    
    $token = getTokenFromRequest();
    if ($token) {
        // In a real implementation, you would blacklist the token
        // For now, we'll just return success
        $response['success'] = true;
        $response['message'] = 'Logout successful';
    } else {
        $response['message'] = 'No token provided';
    }
    
    echo json_encode($response);
}

function handleTokenVerification($db, $validator) {
    global $response;
    
    $token = getTokenFromRequest();
    if (!$token) {
        $response['message'] = 'Token required';
        echo json_encode($response);
        return;
    }
    
    $user = validateJWTToken($token);
    if (!$user) {
        $response['message'] = 'Invalid token';
        echo json_encode($response);
        return;
    }
    
    $response['success'] = true;
    $response['message'] = 'Token is valid';
    $response['data'] = ['user' => $user];
    
    echo json_encode($response);
}

function handleGetProfile($db, $validator) {
    global $response;
    
    $user = getCurrentUser($db);
    if (!$user) {
        $response['message'] = 'Unauthorized';
        echo json_encode($response);
        return;
    }
    
    // Get full profile with member data if applicable
    $profile = $db->fetchOne(
        "SELECT u.*, m.member_number, m.full_name as member_name, m.membership_type, 
                m.credit_score, m.join_date, m.birth_date, m.id_number
         FROM users u 
         LEFT JOIN members m ON u.id = m.user_id 
         WHERE u.id = ?",
        [$user['id']]
    );
    
    if ($profile) {
        unset($profile['password']);
        $response['success'] = true;
        $response['message'] = 'Profile retrieved successfully';
        $response['data'] = $profile;
    } else {
        $response['message'] = 'Profile not found';
    }
    
    echo json_encode($response);
}

function generateJWTToken($user) {
    $payload = [
        'user_id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'iat' => time(),
        'exp' => time() + Config::JWT_EXPIRY
    ];
    
    // Simple JWT implementation (in production, use a proper JWT library)
    $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
    $payload = base64_encode(json_encode($payload));
    $signature = hash_hmac('sha256', "$header.$payload", Config::JWT_SECRET, true);
    $signature = base64_encode($signature);
    
    return "$header.$payload.$signature";
}

function validateJWTToken($token) {
    if (!$token) {
        return null;
    }
    
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }
    
    $header = base64_decode($parts[0]);
    $payload = base64_decode($parts[1]);
    $signature = $parts[2];
    
    $payloadData = json_decode($payload, true);
    if (!$payloadData || $payloadData['exp'] < time()) {
        return null;
    }
    
    // Verify signature
    $expectedSignature = hash_hmac('sha256', "$parts[0].$parts[1]", Config::JWT_SECRET, true);
    $expectedSignature = base64_encode($expectedSignature);
    
    if ($signature !== $expectedSignature) {
        return null;
    }
    
    return $payloadData;
}

function getCurrentUser($db) {
    $token = getTokenFromRequest();
    if (!$token) {
        return null;
    }
    
    $tokenData = validateJWTToken($token);
    if (!$tokenData) {
        return null;
    }
    
    // Get fresh user data
    $user = $db->fetchOne(
        "SELECT * FROM users WHERE id = ? AND is_active = 1",
        [$tokenData['user_id']]
    );
    
    if ($user) {
        unset($user['password']);
        return array_merge($user, $tokenData);
    }
    
    return null;
}

function getTokenFromRequest() {
    $headers = getallheaders();
    return $headers['Authorization'] ?? $headers['authorization'] ?? $_REQUEST['token'] ?? null;
}

function generateMemberNumber() {
    return 'M' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function handleChangePassword($db, $validator) {
    global $response;
    
    $user = getCurrentUser($db);
    if (!$user) {
        $response['message'] = 'Unauthorized';
        echo json_encode($response);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'current_password' => 'required|string',
        'new_password' => 'required|string|min:8'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
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
    $newHashedPassword = password_hash($input['new_password'], PASSWORD_DEFAULT);
    $db->update('users', ['password' => $newHashedPassword], 'id = ?', [$user['id']]);
    
    $response['success'] = true;
    $response['message'] = 'Password changed successfully';
    
    echo json_encode($response);
}

function handleForgotPassword($db, $validator) {
    global $response;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'email' => 'required|email'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    $user = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$input['email']]);
    if (!$user) {
        $response['message'] = 'Email not found';
        echo json_encode($response);
        return;
    }
    
    // Generate reset token
    $resetToken = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Delete existing reset tokens
    $db->delete('password_resets', 'user_id = ?', [$user['id']]);
    
    // Insert new reset token
    $db->insert('password_resets', [
        'user_id' => $user['id'],
        'token' => $resetToken,
        'expires_at' => $expiry,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    // In a real implementation, send email here
    // For now, just return success
    
    $response['success'] = true;
    $response['message'] = 'Password reset link sent to your email';
    $response['data'] = ['reset_token' => $resetToken]; // Only for testing
    
    echo json_encode($response);
}

function handleResetPassword($db, $validator) {
    global $response;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'token' => 'required|string',
        'new_password' => 'required|string|min:8'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Verify reset token
    $reset = $db->fetchOne(
        "SELECT pr.*, u.id as user_id FROM password_resets pr 
         JOIN users u ON pr.user_id = u.id 
         WHERE pr.token = ? AND pr.expires_at > NOW()",
        [$input['token']]
    );
    
    if (!$reset) {
        $response['message'] = 'Invalid or expired token';
        echo json_encode($response);
        return;
    }
    
    // Update password
    $hashedPassword = password_hash($input['new_password'], PASSWORD_DEFAULT);
    $db->update('users', ['password' => $hashedPassword], 'id = ?', [$reset['user_id']]);
    
    // Delete reset token
    $db->delete('password_resets', 'token = ?', [$input['token']]);
    
    $response['success'] = true;
    $response['message'] = 'Password reset successful';
    
    echo json_encode($response);
}
?>
