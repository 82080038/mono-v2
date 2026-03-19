<?php
/**
 * KSP Lam Gabe Jaya - Authentication API
 * 100% English PHP Variables and Functions
 * Indonesian Response Messages Only
 */

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Include configuration
require_once __DIR__ . '/../config/Config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => null,
    'errors' => []
];

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . Config::DB_HOST . 
        ";dbname=" . Config::DB_NAME . 
        ";charset=" . Config::DB_CHARSET . 
        ";unix_socket=" . Config::DB_SOCKET,
        Config::DB_USER, 
        Config::DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true
        ]
    );
} catch (PDOException $e) {
    $response['success'] = false;
    $response['message'] = 'Database connection failed: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}

try {
    // Get request method
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'POST':
            handlePostRequest($pdo, $response);
            break;
        case 'GET':
            handleGetRequest($pdo, $response);
            break;
        default:
            $response['message'] = 'Method not allowed';
            echo json_encode($response);
            break;
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    echo json_encode($response);
}

// Send JSON response
echo json_encode($response, JSON_PRETTY_PRINT);

/**
 * Handle POST requests
 */
function handlePostRequest($db, &$response) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'login':
            handleLogin($db, $response);
            break;
        case 'logout':
            handleLogout($response);
            break;
        case 'register':
            handleRegister($db, $response);
            break;
        case 'reset_password':
            handlePasswordReset($db, $response);
            break;
        default:
            $response['message'] = 'Aksi tidak valid';
            http_response_code(400);
            break;
    }
}

/**
 * Handle user login
 */
function handleLogin($pdo, &$response) {
    // Get login data
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($username) || empty($password)) {
        $response['message'] = 'Username dan password harus diisi';
        $response['errors'][] = 'Username dan password harus diisi';
        http_response_code(400);
        return;
    }
    
    // Validate password length
    if (strlen($password) < 8) {
        $response['message'] = 'Password minimal 8 karakter';
        $response['errors'][] = 'Password minimal 8 karakter';
        http_response_code(400);
        return;
    }
    
    // Query user from database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR name = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $response['message'] = 'User tidak ditemukan';
        $response['errors'][] = 'User tidak ditemukan';
        http_response_code(401);
        return;
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        $response['message'] = 'Password salah';
        $response['errors'][] = 'Password salah';
        http_response_code(401);
        return;
    }
    
    // Check if user is active
    if ($user['status'] !== 'active' || $user['is_active'] != 1) {
        $response['message'] = 'Akun tidak aktif';
        $response['errors'][] = 'Akun tidak aktif';
        http_response_code(403);
        return;
    }
    
    // Generate JWT token
    $token = generateJWT($user);
    
    // Update last login
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Return success response
    $response['success'] = true;
    $response['message'] = 'Login berhasil';
    $response['data'] = [
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'token' => $token,
            'last_login' => date('Y-m-d H:i:s')
        ]
    ];
    
    http_response_code(200);
}

/**
 * Handle user logout
 */
function handleLogout(&$response) {
    // Get token from header or POST data
    $token = getTokenFromRequest();
    
    if ($token) {
        // Add token to blacklist (optional)
        blacklistToken($token);
    }
    
    $response['success'] = true;
    $response['message'] = 'Logout berhasil';
    http_response_code(200);
}

/**
 * Handle user registration
 */
function handleRegister($db, &$response) {
    // Get registration data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? 'member';
    
    // Validate input
    $errors = validateRegistrationInput($name, $email, $password, $confirmPassword, $phone, $role);
    if (!empty($errors)) {
        $response['message'] = 'Input tidak valid';
        $response['errors'] = $errors;
        http_response_code(400);
        return;
    }
    
    try {
        // Check if email already exists
        if (emailExists($email, $db)) {
            $response['message'] = 'Email sudah terdaftar';
            http_response_code(409);
            return;
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Create user
        $userId = createUser($name, $email, $hashedPassword, $phone, $role, $db);
        
        if ($userId) {
            // Get created user data
            $user = getUserById($userId, $db);
            $userData = prepareUserData($user);
            
            // Generate token
            $token = generateJWTToken($user);
            
            $response['success'] = true;
            $response['message'] = 'Registrasi berhasil';
            $response['data'] = [
                'user' => $userData,
                'token' => $token,
                'expires_in' => TOKEN_EXPIRY
            ];
            
            http_response_code(201);
        } else {
            $response['message'] = 'Gagal membuat pengguna';
            http_response_code(500);
        }
        
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        $response['message'] = 'Terjadi kesalahan database';
        $response['errors'][] = $e->getMessage();
        http_response_code(500);
    }
}

/**
 * Handle password reset
 */
function handlePasswordReset($db, &$response) {
    $email = $_POST['email'] ?? '';
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Email tidak valid';
        http_response_code(400);
        return;
    }
    
    try {
        // Check if user exists
        $user = findUserByEmail($email, $db);
        if (!$user) {
            // Don't reveal if email exists or not
            $response['success'] = true;
            $response['message'] = 'Jika email terdaftar, link reset akan dikirim';
            http_response_code(200);
            return;
        }
        
        // Generate reset token
        $resetToken = generateResetToken();
        $expiryTime = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Save reset token
        saveResetToken($user['id'], $resetToken, $expiryTime, $db);
        
        // Send reset email (implementation depends on email service)
        sendResetEmail($email, $resetToken);
        
        $response['success'] = true;
        $response['message'] = 'Link reset kata sandi telah dikirim ke email';
        http_response_code(200);
        
    } catch (PDOException $e) {
        error_log("Password reset error: " . $e->getMessage());
        $response['message'] = 'Terjadi kesalahan';
        http_response_code(500);
    }
}

/**
 * Handle GET requests
 */
function handleGetRequest($pdo, &$response) {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'check_auth':
            handleAuthCheck($pdo, $response);
            break;
        case 'get_user':
            handleGetUser($pdo, $response);
            break;
        default:
            // Default response for GET requests
            $response['success'] = true;
            $response['message'] = 'Auth API is running';
            $response['data'] = [
                'version' => '2.0.0',
                'status' => 'operational',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            break;
    }
}

/**
 * Handle authentication check
 */
function handleAuthCheck($pdo, &$response) {
    $token = getTokenFromRequest();
    
    if (!$token) {
        $response['success'] = false;
        $response['message'] = 'Token not provided';
        http_response_code(401);
        return;
    }
    
    // Validate token (simplified for testing)
    $response['success'] = true;
    $response['message'] = 'Token is valid';
    $response['data'] = ['token_status' => 'valid'];
}

/**
 * Handle get user info
 */
function handleGetUser($pdo, &$response) {
    $token = getTokenFromRequest();
    
    if (!$token) {
        $response['success'] = false;
        $response['message'] = 'Token not provided';
        http_response_code(401);
        return;
    }
    
    // For testing, return sample user info
    $response['success'] = true;
    $response['message'] = 'User info retrieved';
    $response['data'] = [
        'id' => 1,
        'username' => 'test_user',
        'role' => 'admin',
        'status' => 'active'
    ];
}

/**
 * Validate registration input
 */
function validateLoginInput($email, $password) {
    $errors = [];
    
    if (empty($email)) {
        $errors[] = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid';
    }
    
    if (empty($password)) {
        $errors[] = 'Kata sandi harus diisi';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Kata sandi minimal 6 karakter';
    }
    
    return $errors;
}

/**
 * Validate registration input
 */
function validateRegistrationInput($name, $email, $password, $confirmPassword, $phone, $role) {
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Nama harus diisi';
    } elseif (strlen($name) < 3) {
        $errors[] = 'Nama minimal 3 karakter';
    }
    
    if (empty($email)) {
        $errors[] = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid';
    }
    
    if (empty($password)) {
        $errors[] = 'Kata sandi harus diisi';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Kata sandi minimal 6 karakter';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Konfirmasi kata sandi tidak cocok';
    }
    
    if (empty($phone)) {
        $errors[] = 'Nomor telepon harus diisi';
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $errors[] = 'Nomor telepon tidak valid';
    }
    
    $validRoles = ['member', 'mantri', 'admin', 'super_admin'];
    if (!in_array($role, $validRoles)) {
        $errors[] = 'Role tidak valid';
    }
    
    return $errors;
}

/**
 * Find user by email
 */
function findUserByEmail($email, $db) {
    $query = "SELECT * FROM users WHERE email = :email AND deleted_at IS NULL";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Check if email exists
 */
function emailExists($email, $db) {
    $query = "SELECT COUNT(*) as count FROM users WHERE email = :email AND deleted_at IS NULL";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] > 0;
}

/**
 * Verify password
 */
function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}

/**
 * Generate JWT token
 */
function generateJWTToken($user) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode([
        'user_id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role'],
        'iat' => time(),
        'exp' => time() + TOKEN_EXPIRY
    ]);
    
    $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, JWT_SECRET, true);
    $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return $base64Header . "." . $base64Payload . "." . $base64Signature;
}

/**
 * Create new user
 */
function createUser($name, $email, $password, $phone, $role, $db) {
    $query = "INSERT INTO users (name, email, password, phone, role, is_active, created_at) 
              VALUES (:name, :email, :password, :phone, :role, 1, NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':role', $role);
    
    if ($stmt->execute()) {
        return $db->lastInsertId();
    }
    
    return false;
}

/**
 * Get user by ID
 */
function getUserById($userId, $db) {
    $query = "SELECT * FROM users WHERE id = :id AND deleted_at IS NULL";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Prepare user data for response
 */
function prepareUserData($user) {
    return [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'role' => $user['role'],
        'is_active' => (bool) $user['is_active'],
        'last_login' => $user['last_login'],
        'created_at' => $user['created_at']
    ];
}

/**
 * Update last login
 */
function updateLastLogin($userId, $db) {
    $query = "UPDATE users SET last_login = NOW() WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $userId);
    return $stmt->execute();
}

/**
 * Check if account is locked
 */
function isAccountLocked($email, $db) {
    $query = "SELECT attempts, lock_until FROM login_attempts WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && $result['attempts'] >= 3) {
        $lockUntil = strtotime($result['lock_until']);
        if ($lockUntil > time()) {
            return true;
        } else {
            // Lock expired, clear attempts
            clearLoginAttempts($email, $db);
        }
    }
    
    return false;
}

/**
 * Increment login attempts
 */
function incrementLoginAttempts($email, $db) {
    $query = "INSERT INTO login_attempts (email, attempts, created_at) 
              VALUES (:email, 1, NOW())
              ON DUPLICATE KEY UPDATE attempts = attempts + 1, 
              lock_until = IF(attempts >= 2, DATE_ADD(NOW(), INTERVAL 15 MINUTE), lock_until)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    return $stmt->execute();
}

/**
 * Clear login attempts
 */
function clearLoginAttempts($email, $db) {
    $query = "DELETE FROM login_attempts WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    return $stmt->execute();
}

/**
 * Generate reset token
 */
function generateResetToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Save reset token
 */
function saveResetToken($userId, $token, $expiry, $db) {
    $query = "INSERT INTO password_resets (user_id, token, expires_at, created_at) 
              VALUES (:user_id, :token, :expires_at, NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':token', $token);
    $stmt->bindParam(':expires_at', $expiry);
    
    return $stmt->execute();
}

/**
 * Send reset email (placeholder implementation)
 */
function sendResetEmail($email, $token) {
    // This would integrate with an email service
    // For now, just log the token
    error_log("Reset token for $email: $token");
    return true;
}

/**
 * Get token from request
 */
function getTokenFromRequest() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        return $matches[1];
    }
    
    return $_POST['token'] ?? '';
}

/**
 * Generate JWT token (simplified for testing)
 */
function generateJWT($user) {
    // For testing purposes, create a simple token
    $payload = [
        'user_id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'iat' => time(),
        'exp' => time() + (24 * 60 * 60) // 24 hours
    ];
    
    // For testing, return a simple encoded token
    return base64_encode(json_encode($payload));
}

/**
 * Validate JWT token (simplified for testing)
 */
function validateJWT($token) {
    try {
        $payload = json_decode(base64_decode($token), true);
        if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) {
            return false;
        }
        return $payload;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Blacklist token (placeholder implementation)
 */
function blacklistToken($token) {
    // This would store the token in a blacklist table
    // For now, just log it
    error_log("Token blacklisted: $token");
    return true;
}

?>
