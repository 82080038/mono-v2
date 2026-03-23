<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once 'auth_helper.php';

// Check authentication
$token = $_REQUEST['token'] ?? '';
if (empty($token)) {
    echo json_encode(["success" => false, "error" => "Token required"]);
    exit();
}

$user = validateToken($token);
if (!$user) {
    echo json_encode(["success" => false, "error" => "Invalid token"]);
    exit();
}

try {
    $pdo = getDatabaseConnection();
    
    $action = $_REQUEST["action"] ?? "list";
    
    switch ($action) {
        case "create_payment":
            if (!in_array($user['role'], ['Super Admin', 'Admin', 'Manager', 'Staff', 'Owner'])) {
                echo json_encode(["success" => false, "error" => "Access denied"]);
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
                echo json_encode([
                    "success" => true, 
                    "payment_id" => $paymentId,
                    "qris_code" => $qrisCode
                ]);
            } else {
                echo json_encode(["success" => true, "payment_id" => $paymentId]);
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
                
                echo json_encode(["success" => true, "reference" => $paymentResult['reference']]);
            } else {
                echo json_encode(["success" => false, "error" => "Payment failed"]);
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
            
            echo json_encode([
                "success" => true,
                "va_account" => $vaData['account'],
                "va_bank" => $vaData['bank'],
                "amount" => $vaData['amount']
            ]);
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
                echo json_encode([
                    "success" => true,
                    "payment" => [
                        "id" => $payment['id'],
                        "member_name" => $payment['member_name'],
                        "loan_number" => $payment['loan_number'],
                        "amount" => $payment['amount'],
                        "payment_method" => $payment['payment_method'],
                        "status" => $payment['status'],
                        "reference_number" => $payment['reference_number'],
                        "va_account" => $payment['va_account'],
                        "va_bank" => $payment['va_bank'],
                        "created_at" => $payment['created_at'],
                        "processed_at" => $payment['processed_at']
                    ]
                ]);
            } else {
                echo json_encode(["success" => false, "error" => "Payment not found"]);
            }
            break;
            
        case "list_payment_methods":
            // Return available payment methods
            echo json_encode([
                "success" => true,
                "methods" => [
                    [
                        "code" => "qris",
                        "name" => "QRIS",
                        "description" => "Quick Response Code Indonesia",
                        "icon" => "qrcode",
                        "enabled" => true,
                        "min_amount" => 1000,
                        "max_amount" => 5000000
                    ],
                    [
                        "code" => "va_bca",
                        "name" => "Virtual Account BCA",
                        "description" => "BCA Virtual Account",
                        "icon" => "university",
                        "enabled" => true,
                        "min_amount" => 10000,
                        "max_amount" => 10000000
                    ],
                    [
                        "code" => "va_mandiri",
                        "name" => "Virtual Account Mandiri",
                        "description" => "Mandiri Virtual Account",
                        "icon" => "building",
                        "enabled" => true,
                        "min_amount" => 10000,
                        "max_amount" => 10000000
                    ],
                    [
                        "code" => "transfer",
                        "name" => "Bank Transfer",
                        "description" => "Manual bank transfer",
                        "icon" => "exchange-alt",
                        "enabled" => true,
                        "min_amount" => 5000,
                        "max_amount" => 50000000
                    ],
                    [
                        "code" => "cash",
                        "name" => "Cash",
                        "description" => "Cash payment",
                        "icon" => "money-bill",
                        "enabled" => true,
                        "min_amount" => 1000,
                        "max_amount" => 10000000
                    ]
                ]
            ]);
            break;
            
        default:
            echo json_encode(["success" => false, "error" => "Invalid action"]);
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
