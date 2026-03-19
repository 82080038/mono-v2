<?php
/**
 * Payment Gateway API Endpoints
 */

// Include required files
require_once 'PaymentsController.php';

// Initialize controller
$paymentsController = new PaymentsController($db);

// Handle API requests
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        handleGetRequest();
        break;
    case 'POST':
        handlePostRequest();
        break;
    case 'PUT':
        handlePutRequest();
        break;
    default:
        sendJsonResponse(['error' => 'Method not allowed'], 405);
}

function handleGetRequest() {
    global $paymentsController;
    
    $path = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
    $endpoint = $path[0] ?? '';
    
    switch ($endpoint) {
        case 'payments':
            if (isset($path[1]) && $path[1] === 'stats') {
                // Get payment statistics
                $filters = [
                    'date_from' => $_GET['date_from'] ?? '',
                    'date_to' => $_GET['date_to'] ?? ''
                ];
                
                $stats = $paymentsController->getPaymentStats($filters);
                sendJsonResponse($stats);
            } elseif (isset($path[1]) && $path[1] === 'recent') {
                // Get recent transactions
                $limit = $_GET['limit'] ?? 50;
                $transactions = $paymentsController->getRecentTransactions($limit);
                sendJsonResponse($transactions);
            } elseif (isset($path[1]) && is_numeric($path[1])) {
                // Get single payment
                $payment = $paymentsController->getPaymentByTransactionId($path[1]);
                if ($payment) {
                    sendJsonResponse($payment);
                } else {
                    sendJsonResponse(['error' => 'Payment not found'], 404);
                }
            } else {
                // Get payments with filters
                $filters = [
                    'date_from' => $_GET['date_from'] ?? '',
                    'date_to' => $_GET['date_to'] ?? '',
                    'status' => $_GET['status'] ?? '',
                    'method' => $_GET['method'] ?? '',
                    'limit' => $_GET['limit'] ?? 50
                ];
                
                $payments = $paymentsController->getPayments($filters);
                sendJsonResponse($payments);
            }
            break;
            
        case 'gateways':
            if (isset($path[1]) && $path[1] === 'status') {
                // Get gateway status
                $status = $paymentsController->getGatewayStatus();
                sendJsonResponse($status);
            } else {
                // Get gateway configurations
                $gateways = $paymentsController->getGateways();
                sendJsonResponse($gateways);
            }
            break;
            
        case 'methods':
            // Get available payment methods
            $methods = $paymentsController->getPaymentMethods();
            sendJsonResponse($methods);
            break;
            
        default:
            sendJsonResponse(['error' => 'Endpoint not found'], 404);
    }
}

function handlePostRequest() {
    global $paymentsController;
    
    $path = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
    $endpoint = $path[0] ?? '';
    
    switch ($endpoint) {
        case 'payments':
            // Process payment
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                sendJsonResponse(['error' => 'Invalid JSON'], 400);
            }
            
            $data = [
                'member_id' => $input['member_id'] ?? 0,
                'type' => $input['type'] ?? '',
                'amount' => $input['amount'] ?? 0,
                'method' => $input['method'] ?? '',
                'description' => $input['description'] ?? ''
            ];
            
            $result = $paymentsController->processPayment($data);
            sendJsonResponse($result);
            break;
            
        case 'callbacks':
            // Handle payment callback
            $gateway = $path[1] ?? '';
            $callbackData = json_decode(file_get_contents('php://input'), true);
            
            if (!$gateway || !$callbackData) {
                sendJsonResponse(['error' => 'Invalid callback data'], 400);
            }
            
            $result = $paymentsController->handleCallback($gateway, $callbackData);
            sendJsonResponse($result);
            break;
            
        case 'gateways':
            // Configure payment gateway
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                sendJsonResponse(['error' => 'Invalid JSON'], 400);
            }
            
            $config = [
                'provider' => $input['provider'] ?? '',
                'merchant_id' => $input['merchant_id'] ?? '',
                'api_key' => $input['api_key'] ?? '',
                'server_key' => $input['server_key'] ?? '',
                'environment' => $input['environment'] ?? 'sandbox'
            ];
            
            $result = $paymentsController->configureGateway($config);
            sendJsonResponse($result);
            break;
            
        case 'verify':
            // Verify payment status
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                sendJsonResponse(['error' => 'Invalid JSON'], 400);
            }
            
            $transactionId = $input['transaction_id'] ?? '';
            $result = $paymentsController->verifyPayment($transactionId);
            sendJsonResponse($result);
            break;
            
        case 'refund':
            // Process refund
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                sendJsonResponse(['error' => 'Invalid JSON'], 400);
            }
            
            $data = [
                'transaction_id' => $input['transaction_id'] ?? '',
                'amount' => $input['amount'] ?? 0,
                'reason' => $input['reason'] ?? ''
            ];
            
            $result = $paymentsController->processRefund($data);
            sendJsonResponse($result);
            break;
            
        default:
            sendJsonResponse(['error' => 'Endpoint not found'], 404);
    }
}

function handlePutRequest() {
    global $paymentsController;
    
    $path = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
    $endpoint = $path[0] ?? '';
    
    switch ($endpoint) {
        case 'payments':
            if (!isset($path[1]) || !is_numeric($path[1])) {
                sendJsonResponse(['error' => 'Payment ID required'], 400);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                sendJsonResponse(['error' => 'Invalid JSON'], 400);
            }
            
            // Update payment (mock implementation)
            $result = ['success' => true, 'message' => 'Payment updated successfully'];
            sendJsonResponse($result);
            break;
            
        default:
            sendJsonResponse(['error' => 'Endpoint not found'], 404);
    }
}

function sendJsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}
?>
