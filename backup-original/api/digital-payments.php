<?php
/**
 * batch-update-legacy.php - Updated with Security
 * Auto-generated security update
 */

// Security headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token");

// Handle preflight requests
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    exit(0);
}

// Prevent direct access
if (!defined("KSP_API_ACCESS")) {
    header("HTTP/1.0 403 Forbidden");
    exit("Direct access to this file is not allowed.");
}

// Include required files
require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/DatabaseHelper.php";
require_once __DIR__ . "/Logger.php";
require_once __DIR__ . "/DataValidator.php";
require_once __DIR__ . "/SecurityLogger.php";
require_once __DIR__ . "/AuthHelper.php";
require_once __DIR__ . "/SecurityHelper.php";
require_once __DIR__ . "/SecurityMiddleware.php";

// Initialize services
Logger::initialize();
$securityLogger = SecurityLogger::getInstance();
$db = DatabaseHelper::getInstance();
$validator = new DataValidator();

// Initialize response
$response = [
    "success" => false,
    "message" => "",
    "data" => null,
    "errors" => [],
    "timestamp" => date("Y-m-d H:i:s")
];

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

// Check authentication
$token = $_REQUEST['token'] ?? '';
if (empty($token)) {
    SecurityMiddleware::sendJSONResponse($response);
    exit();
}

$user = validateToken($token);
if (!$user) {
    SecurityMiddleware::sendJSONResponse($response);
    exit();
}

try {
    $pdo = getDatabaseConnection();
    
    $action = $_REQUEST["action"] ?? "list";
    
    switch ($action) {
        case "create_payment":
            if (!in_array($user['role'], ['Super Admin', 'Admin', 'Manager', 'Staff', 'Owner'])) {
                SecurityMiddleware::sendJSONResponse($response);
                break;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                INSERT INTO payments (member_id, type, gateway, amount, status, gateway_data, qr_code) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $gatewayData = json_encode([
                'reference_number' => $data['reference_number'] ?? null,
                'created_by' => $user['id']
            ]);
            
            $stmt->execute([
                $data['member_id'],
                $data['payment_type'] ?? 'loan_payment',
                $data['payment_method'],
                $data['amount'],
                $data['status'] ?? 'pending',
                $gatewayData,
                null
            ]);
            
            $paymentId = $pdo->lastInsertId();
            
            // Generate QRIS code if payment method is QRIS
            if ($data['payment_method'] === 'qris') {
                $qrisCode = generateQRISCode($paymentId, $data['amount']);
                SecurityMiddleware::sendJSONResponse($response);
            } else {
                SecurityMiddleware::sendJSONResponse($response);
            }
            break;
            
        case "process_qris_payment":
            $paymentId = $_REQUEST['payment_id'] ?? 0;
            $amount = $_REQUEST['amount'] ?? 0;
            
            // Simulate QRIS payment processing
            $paymentResult = processQRISPayment($paymentId, $amount);
            
            if ($paymentResult['success']) {
                // Update payment status
                $stmt = $pdo->prepare("
                    UPDATE payments SET status = 'completed', processed_at = NOW(), 
                        reference_number = ? WHERE id = ?
                ");
                $stmt->execute([$paymentResult['reference'], $paymentId]);
                
                // Update loan outstanding balance if applicable
                $stmt = $pdo->prepare("
                    UPDATE loans l 
                    INNER JOIN payments p ON l.id = p.loan_id 
                    SET l.outstanding_balance = l.outstanding_balance - p.amount 
                    WHERE p.id = ?
                ");
                $stmt->execute([$paymentId]);
                
                SecurityMiddleware::sendJSONResponse($response);
            } else {
                SecurityMiddleware::sendJSONResponse($response);
            }
            break;
            
        case "create_virtual_account":
            $paymentId = $_REQUEST['payment_id'] ?? 0;
            
            $vaData = generateVirtualAccount($paymentId);
            
            // Store VA details
            $stmt = $pdo->prepare("
                UPDATE payments SET reference_number = ?, va_account = ?, va_bank = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $vaData['reference'], 
                $vaData['account'], 
                $vaData['bank'], 
                $paymentId
            ]);
            
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        case "check_payment_status":
            $paymentId = $_REQUEST['payment_id'] ?? 0;
            
            $stmt = $pdo->prepare("
                SELECT p.*, m.full_name as member_name, l.loan_number 
                FROM payments p 
                LEFT JOIN members m ON p.member_id = m.id 
                LEFT JOIN loans l ON p.loan_id = l.id 
                WHERE p.id = ?
            ");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($payment) {
                SecurityMiddleware::sendJSONResponse($response);
            } else {
                SecurityMiddleware::sendJSONResponse($response);
            }
            break;
            
        case "list_payment_methods":
            // Return available payment methods
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        default:
            SecurityMiddleware::sendJSONResponse($response);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

// Helper functions
function generateQRISCode($paymentId, $amount) {
    // Generate QRIS code (simplified for demo)
    $merchantId = "1234567890123";
    $timestamp = time();
    $qrData = "{$merchantId}|{$paymentId}|{$amount}|{$timestamp}";
    
    // In production, use actual QRIS API
    return [
        "qr_string" => base64_encode($qrData),
        "qr_image" => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==",
        "expires_at" => date('Y-m-d H:i:s', strtotime('+1 hour'))
    ];
}

function processQRISPayment($paymentId, $amount) {
    // Simulate QRIS payment processing
    // In production, integrate with actual QRIS payment gateway
    
    $success = rand(1, 100) <= 95; // 95% success rate for demo
    
    if ($success) {
        return [
            "success" => true,
            "reference" => "QRIS" . date('YmdHis') . rand(1000, 9999),
            "timestamp" => date('Y-m-d H:i:s')
        ];
    } else {
        return [
            "success" => false,
            "error" => "Payment processing failed"
        ];
    }
}

function generateVirtualAccount($paymentId) {
    // Generate virtual account number
    $banks = ['BCA', 'Mandiri', 'BNI', 'BRI'];
    $bank = $banks[array_rand($banks)];
    
    $vaPrefix = [
        'BCA' => '88608',
        'Mandiri' => '88708',
        'BNI' => '88808',
        'BRI' => '88908'
    ];
    
    $prefix = $vaPrefix[$bank];
    $accountNumber = $prefix . str_pad($paymentId, 10, '0', STR_PAD_LEFT);
    
    return [
        "account" => $accountNumber,
        "bank" => $bank,
        "reference" => "VA" . date('YmdHis') . $paymentId,
        "expires_at" => date('Y-m-d H:i:s', strtotime('+24 hours'))
    ];
}
?>
