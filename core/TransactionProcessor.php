<?php
/**
 * Transaction Processing Controller for Teller
 * Handles all deposit, withdrawal, and payment transactions
 */

// Prevent direct access
define('IN_TRANSACTION_PROCESSOR', true);

class TransactionProcessor {
    private $db;
    private $user;
    
    public function __construct($database, $user) {
        $this->db = $database;
        $this->user = $user;
    }
    
    /**
     * Process deposit transaction
     */
    public function processDeposit($data) {
        try {
            // Validate input
            $required = ['member_id', 'amount', 'account_type', 'payment_method'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => "Field $field is required"];
                }
            }
            
            // Validate amount
            if ($data['amount'] <= 0) {
                return ['success' => false, 'message' => 'Amount must be greater than 0'];
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            // Generate transaction code
            $transactionCode = $this->generateTransactionCode('DEP');
            
            // Get account details
            $account = $this->getAccount($data['member_id'], $data['account_type']);
            if (!$account) {
                throw new Exception('Account not found');
            }
            
            // Insert transaction record
            $this->db->query(
                "INSERT INTO transactions (
                    transaction_code, account_id, transaction_type, amount, 
                    description, payment_method, status, created_by, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, 'completed', ?, NOW())",
                [
                    $transactionCode,
                    $account['id'],
                    'credit',
                    $data['amount'],
                    $data['description'] ?? 'Setoran ' . $data['account_type'],
                    $data['payment_method'],
                    $this->user['id']
                ]
            );
            
            // Update account balance
            $newBalance = $account['balance'] + $data['amount'];
            $this->db->query(
                "UPDATE accounts SET balance = ?, last_transaction_date = CURDATE() WHERE id = ?",
                [$newBalance, $account['id']]
            );
            
            // Create receipt data
            $receipt = [
                'transaction_code' => $transactionCode,
                'member_name' => $this->getMemberName($data['member_id']),
                'account_type' => $data['account_type'],
                'amount' => $data['amount'],
                'new_balance' => $newBalance,
                'payment_method' => $data['payment_method'],
                'timestamp' => date('Y-m-d H:i:s'),
                'teller' => $this->user['name']
            ];
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Deposit processed successfully',
                'data' => $receipt
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Process withdrawal transaction
     */
    public function processWithdrawal($data) {
        try {
            // Validate input
            $required = ['member_id', 'amount', 'account_type', 'payment_method'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => "Field $field is required"];
                }
            }
            
            // Validate amount
            if ($data['amount'] <= 0) {
                return ['success' => false, 'message' => 'Amount must be greater than 0'];
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            // Generate transaction code
            $transactionCode = $this->generateTransactionCode('WD');
            
            // Get account details
            $account = $this->getAccount($data['member_id'], $data['account_type']);
            if (!$account) {
                throw new Exception('Account not found');
            }
            
            // Check sufficient balance
            if ($account['balance'] < $data['amount']) {
                throw new Exception('Insufficient balance');
            }
            
            // Insert transaction record
            $this->db->query(
                "INSERT INTO transactions (
                    transaction_code, account_id, transaction_type, amount, 
                    description, payment_method, status, created_by, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, 'completed', ?, NOW())",
                [
                    $transactionCode,
                    $account['id'],
                    'debit',
                    $data['amount'],
                    $data['description'] ?? 'Penarikan ' . $data['account_type'],
                    $data['payment_method'],
                    $this->user['id']
                ]
            );
            
            // Update account balance
            $newBalance = $account['balance'] - $data['amount'];
            $this->db->query(
                "UPDATE accounts SET balance = ?, last_transaction_date = CURDATE() WHERE id = ?",
                [$newBalance, $account['id']]
            );
            
            // Create receipt data
            $receipt = [
                'transaction_code' => $transactionCode,
                'member_name' => $this->getMemberName($data['member_id']),
                'account_type' => $data['account_type'],
                'amount' => $data['amount'],
                'new_balance' => $newBalance,
                'payment_method' => $data['payment_method'],
                'timestamp' => date('Y-m-d H:i:s'),
                'teller' => $this->user['name']
            ];
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Withdrawal processed successfully',
                'data' => $receipt
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Process loan payment
     */
    public function processLoanPayment($data) {
        try {
            // Validate input
            $required = ['loan_id', 'amount', 'payment_method'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => "Field $field is required"];
                }
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            // Generate transaction code
            $transactionCode = $this->generateTransactionCode('PAY');
            
            // Get loan details
            $loan = $this->getLoan($data['loan_id']);
            if (!$loan) {
                throw new Exception('Loan not found');
            }
            
            // Calculate payment breakdown
            $payment = $this->calculatePaymentBreakdown($loan, $data['amount']);
            
            // Insert payment record
            $this->db->query(
                "INSERT INTO loan_payments (
                    loan_id, payment_number, amount, principal_amount, 
                    interest_amount, payment_date, payment_method, 
                    received_by, status, created_at
                ) VALUES (?, ?, ?, ?, ?, CURDATE(), ?, ?, 'completed', NOW())",
                [
                    $loan['id'],
                    $this->getNextPaymentNumber($loan['id']),
                    $data['amount'],
                    $payment['principal'],
                    $payment['interest'],
                    $data['payment_method'],
                    $this->user['id']
                ]
            );
            
            // Update loan status
            $this->updateLoanStatus($loan['id']);
            
            // Create receipt data
            $receipt = [
                'transaction_code' => $transactionCode,
                'member_name' => $this->getMemberName($loan['member_id']),
                'loan_number' => $loan['loan_number'],
                'amount' => $data['amount'],
                'principal' => $payment['principal'],
                'interest' => $payment['interest'],
                'remaining_balance' => $this->getLoanBalance($loan['id']),
                'payment_method' => $data['payment_method'],
                'timestamp' => date('Y-m-d H:i:s'),
                'teller' => $this->user['name']
            ];
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => $receipt
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Payment failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get today's transactions for teller
     */
    public function getTodayTransactions() {
        return $this->db->fetchAll(
            "SELECT t.*, m.full_name as member_name, a.account_number 
             FROM transactions t
             JOIN accounts a ON t.account_id = a.id
             JOIN members m ON a.member_id = m.id
             WHERE DATE(t.created_at) = CURDATE() AND t.created_by = ?
             ORDER BY t.created_at DESC",
            [$this->user['id']]
        );
    }
    
    /**
     * Get transaction summary for today
     */
    public function getTodaySummary() {
        $summary = $this->db->fetchOne(
            "SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END) as total_deposits,
                SUM(CASE WHEN transaction_type = 'debit' THEN amount ELSE 0 END) as total_withdrawals,
                SUM(amount) as total_amount
             FROM transactions 
             WHERE DATE(created_at) = CURDATE() AND created_by = ?",
            [$this->user['id']]
        );
        
        return $summary ?: [
            'total_transactions' => 0,
            'total_deposits' => 0,
            'total_withdrawals' => 0,
            'total_amount' => 0
        ];
    }
    
    // Helper methods
    private function generateTransactionCode($prefix) {
        $date = date('Ymd');
        $sequence = $this->db->fetchOne(
            "SELECT COUNT(*) + 1 as count FROM transactions WHERE DATE(created_at) = CURDATE()"
        )['count'];
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
    
    private function getAccount($memberId, $accountType) {
        return $this->db->fetchOne(
            "SELECT * FROM accounts WHERE member_id = ? AND account_type = ? AND status = 'active'",
            [$memberId, $accountType]
        );
    }
    
    private function getMemberName($memberId) {
        $member = $this->db->fetchOne("SELECT full_name FROM members WHERE id = ?", [$memberId]);
        return $member ? $member['full_name'] : 'Unknown';
    }
    
    private function getLoan($loanId) {
        return $this->db->fetchOne(
            "SELECT * FROM loans WHERE id = ? AND status IN ('active', 'completed')",
            [$loanId]
        );
    }
    
    private function calculatePaymentBreakdown($loan, $paymentAmount) {
        // Simple calculation - in real implementation, this would be more complex
        $interestRate = $loan['interest_rate'] / 100 / 12; // Monthly rate
        $interest = min($paymentAmount * 0.1, $this->getLoanBalance($loan['id']) * $interestRate);
        $principal = $paymentAmount - $interest;
        
        return [
            'principal' => $principal,
            'interest' => $interest
        ];
    }
    
    private function getNextPaymentNumber($loanId) {
        $lastPayment = $this->db->fetchOne(
            "SELECT MAX(payment_number) as last_number FROM loan_payments WHERE loan_id = ?",
            [$loanId]
        );
        
        return ($lastPayment ? $lastPayment['last_number'] : 0) + 1;
    }
    
    private function updateLoanStatus($loanId) {
        $balance = $this->getLoanBalance($loanId);
        $status = $balance <= 0 ? 'completed' : 'active';
        
        $this->db->query(
            "UPDATE loans SET status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $loanId]
        );
    }
    
    private function getLoanBalance($loanId) {
        $loan = $this->db->fetchOne("SELECT loan_amount FROM loans WHERE id = ?", [$loanId]);
        $paid = $this->db->fetchOne(
            "SELECT COALESCE(SUM(principal_amount), 0) as total_paid FROM loan_payments WHERE loan_id = ? AND status = 'completed'",
            [$loanId]
        );
        
        return $loan ? $loan['loan_amount'] - $paid['total_paid'] : 0;
    }
}
