<?php
/**
 * Payment Gateway Controller
 * Handles payment processing, gateway integration, and callbacks
 */

class PaymentsController {
    private $db;
    private $gatewayConfig;
    
    public function __construct($database) {
        $this->db = $database;
        $this->gatewayConfig = $this->loadGatewayConfig();
    }
    
    /**
     * Process payment
     */
    public function processPayment($data) {
        try {
            // Validate data
            if (!$this->validatePaymentData($data)) {
                return ['success' => false, 'message' => 'Invalid payment data'];
            }
            
            // Generate transaction ID
            $transactionId = $this->generateTransactionId();
            
            // Create payment record
            $query = "INSERT INTO payments (transaction_id, member_id, type, amount, method, description, status, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                $transactionId,
                $data['member_id'],
                $data['type'],
                $data['amount'],
                $data['method'],
                $data['description'] ?? ''
            ]);
            
            if (!$result) {
                return ['success' => false, 'message' => 'Failed to create payment record'];
            }
            
            $paymentId = $this->db->lastInsertId();
            
            // Process payment based on method
            $gatewayResult = $this->processGatewayPayment($transactionId, $data);
            
            if ($gatewayResult['success']) {
                // Update payment status
                $this->updatePaymentStatus($paymentId, 'success', $gatewayResult['gateway_response']);
                
                // Update related records (loan, savings, etc.)
                $this->updateRelatedRecords($data);
                
                return [
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'transaction_id' => $transactionId,
                    'payment_id' => $paymentId,
                    'gateway_response' => $gatewayResult['gateway_response']
                ];
            } else {
                // Update payment status to failed
                $this->updatePaymentStatus($paymentId, 'failed', $gatewayResult['error']);
                
                return [
                    'success' => false,
                    'message' => 'Payment failed: ' . $gatewayResult['error'],
                    'transaction_id' => $transactionId,
                    'payment_id' => $paymentId
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error processing payment: " . $e->getMessage());
            return ['success' => false, 'message' => 'Payment processing failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Handle payment callback from gateway
     */
    public function handleCallback($gateway, $callbackData) {
        try {
            // Verify callback signature
            if (!$this->verifyCallbackSignature($gateway, $callbackData)) {
                return ['success' => false, 'message' => 'Invalid callback signature'];
            }
            
            $transactionId = $callbackData['transaction_id'] ?? '';
            $status = $callbackData['status'] ?? '';
            
            if (empty($transactionId)) {
                return ['success' => false, 'message' => 'Transaction ID not found'];
            }
            
            // Get payment record
            $payment = $this->getPaymentByTransactionId($transactionId);
            if (!$payment) {
                return ['success' => false, 'message' => 'Payment not found'];
            }
            
            // Update payment status
            $newStatus = $this->mapGatewayStatus($status);
            $this->updatePaymentStatus($payment['id'], $newStatus, json_encode($callbackData));
            
            // If payment is successful, update related records
            if ($newStatus === 'success') {
                $this->processSuccessfulPayment($payment);
            }
            
            return [
                'success' => true,
                'message' => 'Callback processed successfully',
                'status' => $newStatus
            ];
            
        } catch (Exception $e) {
            error_log("Error handling callback: " . $e->getMessage());
            return ['success' => false, 'message' => 'Callback processing failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get payment statistics
     */
    public function getPaymentStats($filters = []) {
        $stats = [];
        
        try {
            // Total transactions
            $query = "SELECT COUNT(*) as total FROM payments";
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $query .= " WHERE created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $query .= (empty($params) ? " WHERE" : " AND") . " created_at <= ?";
                $params[] = $filters['date_to'];
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $stats['total_transactions'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Success rate
            $query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful
                     FROM payments";
            
            $params = [];
            if (!empty($filters['date_from'])) {
                $query .= " WHERE created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $query .= (empty($params) ? " WHERE" : " AND") . " created_at <= ?";
                $params[] = $filters['date_to'];
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stats['successful'] = $result['successful'];
            $stats['success_rate'] = $result['total'] > 0 ? round(($result['successful'] / $result['total']) * 100, 2) : 0;
            $stats['failed'] = $result['total'] - $result['successful'];
            $stats['pending'] = $this->getPendingCount($filters);
            
            // Total amount
            $query = "SELECT SUM(amount) as total_amount FROM payments WHERE status = 'success'";
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $query .= " AND created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $query .= " AND created_at <= ?";
                $params[] = $filters['date_to'];
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $stats['total_amount'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_amount'] ?? 0;
            
            // Payment methods distribution
            $query = "SELECT method, COUNT(*) as count FROM payments GROUP BY method";
            $stmt = $this->db->query($query);
            $stats['by_method'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Error getting payment stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent transactions
     */
    public function getRecentTransactions($limit = 50) {
        $query = "SELECT p.*, m.name as member_name, u.name as processed_by_name 
                 FROM payments p 
                 LEFT JOIN members m ON p.member_id = m.id 
                 LEFT JOIN users u ON p.processed_by = u.id 
                 ORDER BY p.created_at DESC 
                 LIMIT ?";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting recent transactions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get payment by transaction ID
     */
    public function getPaymentByTransactionId($transactionId) {
        $query = "SELECT * FROM payments WHERE transaction_id = ?";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$transactionId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting payment by transaction ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Configure payment gateway
     */
    public function configureGateway($config) {
        try {
            // Validate configuration
            if (!$this->validateGatewayConfig($config)) {
                return ['success' => false, 'message' => 'Invalid gateway configuration'];
            }
            
            // Save configuration
            $query = "INSERT INTO payment_gateways (provider, merchant_id, api_key, server_key, environment, config, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, NOW()) 
                     ON DUPLICATE KEY UPDATE 
                     merchant_id = VALUES(merchant_id), 
                     api_key = VALUES(api_key), 
                     server_key = VALUES(server_key), 
                     environment = VALUES(environment), 
                     config = VALUES(config)";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                $config['provider'],
                $config['merchant_id'],
                $config['api_key'],
                $config['server_key'],
                $config['environment'],
                json_encode($config)
            ]);
            
            if ($result) {
                // Refresh gateway config
                $this->gatewayConfig = $this->loadGatewayConfig();
                
                return ['success' => true, 'message' => 'Gateway configured successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to configure gateway'];
            }
            
        } catch (Exception $e) {
            error_log("Error configuring gateway: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gateway configuration failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get gateway status
     */
    public function getGatewayStatus() {
        $status = [];
        
        try {
            $query = "SELECT provider, environment, last_check, status FROM payment_gateways";
            $stmt = $this->db->query($query);
            $gateways = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($gateways as $gateway) {
                $status[$gateway['provider']] = [
                    'environment' => $gateway['environment'],
                    'status' => $gateway['status'],
                    'last_check' => $gateway['last_check']
                ];
            }
            
            return $status;
            
        } catch (PDOException $e) {
            error_log("Error getting gateway status: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Private methods
     */
    
    private function validatePaymentData($data) {
        $required = ['member_id', 'type', 'amount', 'method'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        
        // Validate amount
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            return false;
        }
        
        // Validate payment method
        $allowedMethods = ['bank_transfer', 'gopay', 'ovo', 'dana', 'credit_card', 'qris', 'cash', 'debit_card'];
        if (!in_array($data['method'], $allowedMethods)) {
            return false;
        }
        
        return true;
    }
    
    private function generateTransactionId() {
        return 'PAY' . date('YmdHis') . mt_rand(1000, 9999);
    }
    
    private function processGatewayPayment($transactionId, $data) {
        $method = $data['method'];
        
        // Mock gateway processing - in production, integrate with actual payment gateways
        switch ($method) {
            case 'bank_transfer':
                return $this->processBankTransfer($transactionId, $data);
            case 'gopay':
            case 'ovo':
            case 'dana':
                return $this->processEwallet($transactionId, $data);
            case 'credit_card':
            case 'debit_card':
                return $this->processCardPayment($transactionId, $data);
            case 'qris':
                return $this->processQRIS($transactionId, $data);
            case 'cash':
                return $this->processCashPayment($transactionId, $data);
            default:
                return ['success' => false, 'error' => 'Unsupported payment method'];
        }
    }
    
    private function processBankTransfer($transactionId, $data) {
        // Mock bank transfer processing
        $virtualAccount = $this->generateVirtualAccount($transactionId);
        
        return [
            'success' => true,
            'gateway_response' => [
                'virtual_account' => $virtualAccount,
                'amount' => $data['amount'],
                'status' => 'success'
            ]
        ];
    }
    
    private function processEwallet($transactionId, $data) {
        // Mock e-wallet processing
        return [
            'success' => true,
            'gateway_response' => [
                'payment_url' => 'https://mock-ewallet.com/pay/' . $transactionId,
                'amount' => $data['amount'],
                'status' => 'success'
            ]
        ];
    }
    
    private function processCardPayment($transactionId, $data) {
        // Mock card payment processing
        return [
            'success' => true,
            'gateway_response' => [
                'authorization_code' => 'AUTH' . mt_rand(100000, 999999),
                'amount' => $data['amount'],
                'status' => 'success'
            ]
        ];
    }
    
    private function processQRIS($transactionId, $data) {
        // Mock QRIS processing
        $qrCode = $this->generateQRCode($transactionId, $data['amount']);
        
        return [
            'success' => true,
            'gateway_response' => [
                'qr_code' => $qrCode,
                'amount' => $data['amount'],
                'status' => 'success'
            ]
        ];
    }
    
    private function processCashPayment($transactionId, $data) {
        // Mock cash processing
        return [
            'success' => true,
            'gateway_response' => [
                'receipt_number' => 'RCP' . date('YmdHis') . mt_rand(100, 999),
                'amount' => $data['amount'],
                'status' => 'success'
            ]
        ];
    }
    
    private function generateVirtualAccount($transactionId) {
        return '886' . str_pad(mt_rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
    }
    
    private function generateQRCode($transactionId, $amount) {
        return 'QRIS' . date('YmdHis') . mt_rand(1000, 9999) . '|' . $amount;
    }
    
    private function updatePaymentStatus($paymentId, $status, $gatewayResponse = null) {
        $query = "UPDATE payments SET status = ?, gateway_response = ?, updated_at = NOW() WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$status, $gatewayResponse, $paymentId]);
        } catch (PDOException $e) {
            error_log("Error updating payment status: " . $e->getMessage());
            return false;
        }
    }
    
    private function updateRelatedRecords($data) {
        // Update related records based on payment type
        switch ($data['type']) {
            case 'loan_payment':
                return $this->processLoanPayment($data);
            case 'savings_deposit':
                return $this->processSavingsDeposit($data);
            case 'loan_disbursement':
                return $this->processLoanDisbursement($data);
            default:
                return true;
        }
    }
    
    private function processLoanPayment($data) {
        // Update loan payment
        $query = "UPDATE loans SET balance = balance - ?, last_payment = NOW() WHERE member_id = ? AND balance > 0";
        
        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$data['amount'], $data['member_id']]);
        } catch (PDOException $e) {
            error_log("Error processing loan payment: " . $e->getMessage());
            return false;
        }
    }
    
    private function processSavingsDeposit($data) {
        // Update savings balance
        $query = "UPDATE savings SET balance = balance + ?, last_deposit = NOW() WHERE member_id = ?";
        
        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$data['amount'], $data['member_id']]);
        } catch (PDOException $e) {
            error_log("Error processing savings deposit: " . $e->getMessage());
            return false;
        }
    }
    
    private function processLoanDisbursement($data) {
        // Update loan disbursement
        $query = "UPDATE loans SET disbursed = disbursed + ?, disbursement_date = NOW() WHERE member_id = ?";
        
        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$data['amount'], $data['member_id']]);
        } catch (PDOException $e) {
            error_log("Error processing loan disbursement: " . $e->getMessage());
            return false;
        }
    }
    
    private function verifyCallbackSignature($gateway, $data) {
        // Mock signature verification - in production, implement actual verification
        return true;
    }
    
    private function mapGatewayStatus($gatewayStatus) {
        // Map gateway status to internal status
        $statusMap = [
            'success' => 'success',
            'completed' => 'success',
            'paid' => 'success',
            'failed' => 'failed',
            'cancelled' => 'failed',
            'expired' => 'failed',
            'pending' => 'pending',
            'processing' => 'pending'
        ];
        
        return $statusMap[$gatewayStatus] ?? 'pending';
    }
    
    private function processSuccessfulPayment($payment) {
        // Additional processing for successful payments
        error_log("Payment successful: {$payment['transaction_id']}");
        return true;
    }
    
    private function getPendingCount($filters = []) {
        $query = "SELECT COUNT(*) as count FROM payments WHERE status = 'pending'";
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $query .= " AND created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (PDOException $e) {
            error_log("Error getting pending count: " . $e->getMessage());
            return 0;
        }
    }
    
    private function validateGatewayConfig($config) {
        $required = ['provider', 'merchant_id', 'api_key', 'environment'];
        
        foreach ($required as $field) {
            if (!isset($config[$field]) || empty($config[$field])) {
                return false;
            }
        }
        
        return true;
    }
    
    private function loadGatewayConfig() {
        // Load gateway configuration from database
        try {
            $query = "SELECT * FROM payment_gateways WHERE status = 'active'";
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error loading gateway config: " . $e->getMessage());
            return [];
        }
    }
}
?>
