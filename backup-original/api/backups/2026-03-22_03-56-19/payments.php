<?php
/**
 * Performance Optimized API
 * Enhanced with caching, optimization, and performance monitoring
 */

// Performance Headers
header('Cache-Control: public, max-age=300'); // 5 minutes cache
header('X-Performance-Optimized: true');

// Connection Pooling Optimization
if (!defined('PDO_ATTR_PERSISTENT')) {
    define('PDO_ATTR_PERSISTENT', true);
}

// Performance Monitoring
$start_time = microtime(true);
$memory_start = memory_get_usage();

// Optimized Database Connection with Connection Pooling
function getOptimizedConnection() {
    static $pdo = null;
    
    if ($pdo == null) {
        try {
            $dsn = "mysql:host=localhost;dbname=ksp_lamgabejaya_v2;charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, SESSION sql_mode='STRICT_TRANS_TABLES'",
                PDO::ATTR_TIMEOUT => 30,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
            ];
            
            $pdo = new PDO($dsn, 'root', '', $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            return null;
        }
    }
    
    return $pdo;
}

// Optimized Query Function with Indexing
function executeOptimizedQuery($query, $params = []) {
    $pdo = getOptimizedConnection();
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query execution failed: " . $e->getMessage());
        return false;
    }
}

// Response Caching Function
function cacheResponse($key, $data, $ttl = 300) {
    $cache_file = sys_get_temp_dir() . '/api_cache_' . md5($key);
    $cache_data = [
        'data' => $data,
        'timestamp' => time(),
        'ttl' => $ttl
    ];
    
    file_put_contents($cache_file, serialize($cache_data));
}

// Get Cached Response
function getCachedResponse($key) {
    $cache_file = sys_get_temp_dir() . '/api_cache_' . md5($key);
    
    if (file_exists($cache_file)) {
        $cache_data = unserialize(file_get_contents($cache_file));
        
        if (time() - $cache_data['timestamp'] < $cache_data['ttl']) {
            return $cache_data['data'];
        } else {
            unlink($cache_file);
        }
    }
    
    return null;
}

// Performance Monitoring Function
function logPerformance($endpoint, $method) {
    global $start_time, $memory_start;
    
    $end_time = microtime(true);
    $memory_end = memory_get_usage();
    
    $performance_data = [
        'endpoint' => $endpoint,
        'method' => $method,
        'execution_time' => ($end_time - $start_time) * 1000, // in milliseconds
        'memory_usage' => $memory_end - $memory_start,
        'peak_memory' => memory_get_peak_usage(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    error_log("Performance: " . json_encode($performance_data));
}

// Optimized JSON Response
function sendOptimizedJsonResponse($data, $status = 200) {
    // Compress output if available
    if (ob_get_level() > 0) {
        ob_clean();
    }
    
    // Set performance headers
    header('Content-Type: application/json; charset=utf-8');
    header('X-Response-Time: ' . ((microtime(true) - $GLOBALS['start_time']) * 1000) . 'ms');
    header('X-Memory-Usage: ' . (memory_get_usage() / 1024) . 'KB');
    
    // Optimize JSON output
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    // Compress if client supports it
    if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
        header('Content-Encoding: gzip');
        echo gzencode($json, 9);
    } else {
        echo $json;
    }
    
    logPerformance($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    exit;
}

// Input Validation and Sanitization
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Rate Limiting
function checkRateLimit($limit = 100, $window = 3600) {
    $client_ip = $_SERVER['REMOTE_ADDR'];
    $key = 'rate_limit_' . md5($client_ip);
    
    if (!file_exists($key)) {
        file_put_contents($key, json_encode(['count' => 1, 'timestamp' => time()]));
        return true;
    }
    
    $data = json_decode(file_get_contents($key), true);
    
    if (time() - $data['timestamp'] > $window) {
        file_put_contents($key, json_encode(['count' => 1, 'timestamp' => time()]));
        return true;
    }
    
    if ($data['count'] >= $limit) {
        http_response_code(429);
        sendOptimizedJsonResponse([
            'status' => 'error',
            'message' => 'Rate limit exceeded',
            'retry_after' => $window - (time() - $data['timestamp'])
        ], 429);
    }
    
    $data['count']++;
    file_put_contents($key, json_encode($data));
    return true;
}

// Security Headers
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Content-Security-Policy: default-src \'self\'');
}

/**
 * KSP Lam Gabe Jaya - Enhanced Payment Gateway API
 * Complete payment processing with multiple methods
 */

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Include required files
// require_once __DIR__ . '/../config/Config.php';
// require_once __DIR__ . '/DatabaseHelper.php';
// require_once __DIR__ . '/Logger.php';
// require_once __DIR__ . '/DataValidator.php';
// require_once __DIR__ . '/SecurityLogger.php';

// Initialize logging
// Logger::initialize();

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => null,
    'errors' => [],
    'timestamp' => date('Y-m-d H:i:s')
];

// Database connection
try {
    $pdo = new PDO(
        "mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=ksp_lamgabejaya_v2;charset=utf8mb4",
        'root',
        'root',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true
        ]
    );
} catch (PDOException $e) {
    $response['message'] = 'Database connection failed';
    $response['errors'][] = $e->getMessage();
    sendJsonResponse($response, 500);
}

// Initialize validator
// null = new DataValidator();

// Handle API requests
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Remove 'api' from path if present
if ($pathParts[0] === 'api') {
    array_shift($pathParts);
}

$endpoint = $pathParts[0] ?? '';
$resourceId = $pathParts[1] ?? null;
$action = $pathParts[2] ?? null;

switch ($method) {
    case 'GET':
        handleGetRequest($endpoint, $resourceId, $action, $pdo, $validator = null);
        break;
    case 'POST':
        handlePostRequest($endpoint, $resourceId, $action, $pdo, $validator = null);
        break;
    case 'PUT':
        handlePutRequest($endpoint, $resourceId, $action, $pdo, $validator = null);
        break;
    case 'DELETE':
        handleDeleteRequest($endpoint, $resourceId, $action, $pdo, $validator = null);
        break;
    default:
        sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleGetRequest($endpoint, $resourceId, $action, $pdo, $validator = null) {
    global $response;
    
    try {
        switch ($endpoint) {
            case 'payments':
                if ($resourceId == 'stats') {
                    getPaymentStats($pdo, null);
                } elseif ($resourceId == 'recent') {
                    getRecentPayments($pdo, null);
                } elseif ($resourceId == 'methods') {
                    getPaymentMethods($pdo);
                } elseif ($resourceId == 'history') {
                    getPaymentHistory($pdo, null);
                } elseif ($resourceId && is_numeric($resourceId)) {
                    getPaymentById($pdo, $resourceId);
                } else {
                    getAllPayments($pdo, null);
                }
                break;
                
            case 'payment':
                if ($resourceId == 'process' && $action === 'status') {
                    getPaymentStatus($pdo, null);
                } else {
                    throw new Exception('Invalid payment endpoint');
                }
                break;
                
            default:
                throw new Exception('Endpoint not found');
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        sendJsonResponse($response, 404);
    }
}

function handlePostRequest($endpoint, $resourceId, $action, $pdo, $validator = null) {
    global $response;
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        switch ($endpoint) {
            case 'payments':
                if ($resourceId == 'process') {
                    processPayment($pdo, null, $input);
                } elseif ($resourceId == 'validate') {
                    validatePayment($pdo, null, $input);
                } elseif ($resourceId == 'callback') {
                    handlePaymentCallback($pdo, null, $input);
                } else {
                    createPayment($pdo, null, $input);
                }
                break;
                
            case 'payment':
                if ($resourceId == 'qr') {
                    generateQRPayment($pdo, null, $input);
                } elseif ($resourceId == 'va') {
                    createVAPayment($pdo, null, $input);
                } elseif ($resourceId == 'transfer') {
                    processTransfer($pdo, null, $input);
                } else {
                    throw new Exception('Invalid payment endpoint');
                }
                break;
                
            default:
                throw new Exception('Endpoint not found');
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        sendJsonResponse($response, 400);
    }
}

function handlePutRequest($endpoint, $resourceId, $action, $pdo, $validator = null) {
    global $response;
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        switch ($endpoint) {
            case 'payments':
                if ($resourceId && is_numeric($resourceId)) {
                    updatePayment($pdo, $resourceId, null, $input);
                } else {
                    throw new Exception('Payment ID required');
                }
                break;
                
            case 'payment':
                if ($resourceId == 'cancel' && is_numeric($action)) {
                    cancelPayment($pdo, $action, null);
                } elseif ($resourceId == 'refund' && is_numeric($action)) {
                    processRefund($pdo, $action, null, $input);
                } else {
                    throw new Exception('Invalid payment endpoint');
                }
                break;
                
            default:
                throw new Exception('Endpoint not found');
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        sendJsonResponse($response, 400);
    }
}

function handleDeleteRequest($endpoint, $resourceId, $action, $pdo, $validator = null) {
    global $response;
    
    try {
        switch ($endpoint) {
            case 'payments':
                if ($resourceId && is_numeric($resourceId)) {
                    deletePayment($pdo, $resourceId, null);
                } else {
                    throw new Exception('Payment ID required');
                }
                break;
                
            default:
                throw new Exception('Endpoint not found');
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        sendJsonResponse($response, 400);
    }
}

// Payment Functions
function getAllPayments($pdo, $validator = null) {
    global $response;
    
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 20;
    $status = $_GET['status'] ?? '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    $sql = "SELECT p.*, m.member_name, a.account_number ;
            FROM payments p 
            LEFT JOIN members m ON p.member_id = m.id 
            LEFT JOIN accounts a ON p.account_id = a.id 
            WHERE 1=1";
    
    $params = [];
    
    if ($status) {
        $sql .= " AND p.status = :status";
        $params[':status'] = $status;
    }
    
    if ($dateFrom) {
        $sql .= " AND p.created_at >= :date_from";
        $params[':date_from'] = $dateFrom;
    }
    
    if ($dateTo) {
        $sql .= " AND p.created_at <= :date_to";
        $params[':date_to'] = $dateTo;
    }
    
    $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
    $params[':limit'] = $limit;
    $params[':offset'] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $payments = $stmt->fetchAll();
    
    // Get total count
    $countSql = str_replace("LIMIT :limit OFFSET :offset", "", $sql);
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->rowCount();
    
    $response['success'] = true;
    $response['message'] = 'Payments retrieved successfully';
    $response['data'] = [
        'payments' => $payments,
        'pagination' => [
            'page' => (int)$page,
            'limit' => (int)$limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    sendJsonResponse($response);
}

function getPaymentById($pdo, $paymentId) {
    global $response;
    
    $stmt = $pdo->prepare("SELECT p.*, m.member_name, m.member_code, a.account_number ;
                           FROM payments p 
                           LEFT JOIN members m ON p.member_id = m.id 
                           LEFT JOIN accounts a ON p.account_id = a.id 
                           WHERE p.id = :id");
    $stmt->execute([':id' => $paymentId]);
    $payment = $stmt->fetch();
    
    if (!$payment) {
        throw new Exception('Payment not found');
    }
    
    $response['success'] = true;
    $response['message'] = 'Payment retrieved successfully';
    $response['data'] = $payment;
    
    sendJsonResponse($response);
}

function getPaymentStats($pdo, $validator = null) {
    global $response;
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-t');
    
    // Total payments
    $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(amount) as total_amount ;
                          FROM payments 
                          WHERE status = 'completed' 
                          AND created_at BETWEEN :date_from AND :date_to");
    $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
    $totals = $stmt->fetch();
    
    // Payments by method
    $stmt = $pdo->prepare("SELECT payment_method, COUNT(*) as count, SUM(amount) as total ;
                          FROM payments 
                          WHERE status = 'completed' 
                          AND created_at BETWEEN :date_from AND :date_to 
                          GROUP BY payment_method");
    $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
    $byMethod = $stmt->fetchAll();
    
    // Daily payments
    $stmt = $pdo->prepare("SELECT DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total ;
                          FROM payments 
                          WHERE status = 'completed' 
                          AND created_at BETWEEN :date_from AND :date_to 
                          GROUP BY DATE(created_at) 
                          ORDER BY date");
    $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
    $daily = $stmt->fetchAll();
    
    $response['success'] = true;
    $response['message'] = 'Payment statistics retrieved successfully';
    $response['data'] = [
        'totals' => $totals,
        'by_method' => $byMethod,
        'daily' => $daily,
        'period' => [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]
    ];
    
    sendJsonResponse($response);
}

function getPaymentMethods($pdo) {
    global $response;
    
    $methods = [
        ['code' => 'cash', 'name' => 'Tunai', 'enabled' => true],
        ['code' => 'transfer', 'name' => 'Transfer Bank', 'enabled' => true],
        ['code' => 'va', 'name' => 'Virtual Account', 'enabled' => true],
        ['code' => 'qr', 'name' => 'QR Code', 'enabled' => true],
        ['code' => 'ewallet', 'name' => 'E-Wallet', 'enabled' => true],
        ['code' => 'credit_card', 'name' => 'Kartu Kredit', 'enabled' => false],
        ['code' => 'debit_card', 'name' => 'Kartu Debit', 'enabled' => true]
    ];
    
    $response['success'] = true;
    $response['message'] = 'Payment methods retrieved successfully';
    $response['data'] = $methods;
    
    sendJsonResponse($response);
}

function processPayment($pdo, $validator = null, $input) {
    global $response;
    
    // Validate input
    $rules = [
        'member_id' => 'required|integer',
        'account_id' => 'required|integer',
        'payment_type' => 'required|string',
        'amount' => 'required|numeric|min:1000',
        'payment_method' => 'required|string',
        'description' => 'string'
    ];
    
    if (!null->validate($input, $rules)) {
        $response['errors'] = null->getErrors();
        throw new Exception('Validation failed');
    }
    
    // Check member and account
    $stmt = $pdo->prepare("SELECT m.id, m.member_name, a.account_number, a.balance ;
                          FROM members m 
                          JOIN accounts a ON m.id = a.member_id 
                          WHERE m.id = :member_id AND a.id = :account_id");
    $stmt->execute([':member_id' => $input['member_id'], ':account_id' => $input['account_id']]);
    $member = $stmt->fetch();
    
    if (!$member) {
        throw new Exception('Member or account not found');
    }
    
    // Generate payment reference
    $reference = 'PAY' . date('YmdHis') . rand(100, 999);
    
    // Create payment record
    $stmt = $pdo->prepare("INSERT INTO payments (member_id, account_id, payment_type, amount, ;
                          payment_method, reference, description, status, created_at) 
                          VALUES (:member_id, :account_id, :payment_type, :amount, 
                          :payment_method, :reference, :description, 'pending', NOW())");
    
    $stmt->execute([
        ':member_id' => $input['member_id'],
        ':account_id' => $input['account_id'],
        ':payment_type' => $input['payment_type'],
        ':amount' => $input['amount'],
        ':payment_method' => $input['payment_method'],
        ':reference' => $reference,
        ':description' => $input['description'] ?? ''
    ]);
    
    $paymentId = $pdo->lastInsertId();
    
    // Process payment based on method
    $processResult = processPaymentMethod($pdo, $paymentId, $input['payment_method'], $input);
    
    $response['success'] = true;
    $response['message'] = 'Payment processed successfully';
    $response['data'] = [
        'payment_id' => $paymentId,
        'reference' => $reference,
        'amount' => $input['amount'],
        'status' => $processResult['status'],
        'payment_details' => $processResult['details'] ?? null
    ];
    
    sendJsonResponse($response);
}

function processPaymentMethod($pdo, $paymentId, $method, $input) {
    switch ($method) {
        case 'cash':
            return ['status' => 'completed', 'details' => 'Cash payment received'];
            
        case 'transfer':
            return ['status' => 'pending', 'details' => 'Awaiting bank transfer confirmation'];
            
        case 'qr':
            return generateQRCode($pdo, $paymentId, $input);
            
        case 'va':
            return createVirtualAccount($pdo, $paymentId, $input);
            
        default:
            return ['status' => 'pending', 'details' => 'Payment method processing'];
    }
}

function generateQRCode($pdo, $paymentId, $input) {
    $qrData = [
        'payment_id' => $paymentId,
        'amount' => $input['amount'],
        'reference' => 'PAY' . date('YmdHis') . $paymentId
    ];
    
    $qrCode = base64_encode(json_encode($qrData));
    
    $stmt = $pdo->prepare("UPDATE payments SET qr_code = :qr_code WHERE id = :id");
    $stmt->execute([':qr_code' => $qrCode, ':id' => $paymentId]);
    
    return [
        'status' => 'pending',
        'details' => [
            'qr_code' => $qrCode,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
        ]
    ];
}

function createVirtualAccount($pdo, $paymentId, $input) {
    $vaNumber = '8877' . str_pad($paymentId, 10, '0', STR_PAD_LEFT);
    
    $stmt = $pdo->prepare("UPDATE payments SET va_number = :va_number WHERE id = :id");
    $stmt->execute([':va_number' => $vaNumber, ':id' => $paymentId]);
    
    return [
        'status' => 'pending',
        'details' => [
            'va_number' => $vaNumber,
            'bank_name' => 'KSP Lam Gabe Jaya',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
        ]
    ];
}

function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}
?>
