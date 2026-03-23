<?php
/**
 * Approval Workflow System for Admin
 * Handles transaction approvals, member registrations, and loan applications
 */

// Prevent direct access
define('IN_APPROVAL_WORKFLOW', true);

class ApprovalWorkflow {
    private $db;
    private $user;
    
    public function __construct($database, $user) {
        $this->db = $database;
        $this->user = $user;
    }
    
    /**
     * Get pending approvals for admin
     */
    public function getPendingApprovals() {
        $approvals = [];
        
        // Pending member registrations
        $approvals['members'] = $this->db->fetchAll(
            "SELECT m.*, 'member_registration' as type, m.created_at as submitted_date
             FROM members m 
             WHERE m.status = 'pending'
             ORDER BY m.created_at DESC"
        );
        
        // Pending loan applications
        $approvals['loans'] = $this->db->fetchAll(
            "SELECT l.*, m.full_name as member_name, 'loan_application' as type, l.application_date as submitted_date
             FROM loans l
             JOIN members m ON l.member_id = m.id
             WHERE l.status = 'pending'
             ORDER BY l.application_date DESC"
        );
        
        // Pending large transactions (above threshold)
        $approvals['transactions'] = $this->db->fetchAll(
            "SELECT t.*, m.full_name as member_name, a.account_number, 'large_transaction' as type, t.created_at as submitted_date
             FROM transactions t
             JOIN accounts a ON t.account_id = a.id
             JOIN members m ON a.member_id = m.id
             WHERE t.status = 'pending' AND t.amount > 10000000
             ORDER BY t.created_at DESC"
        );
        
        return $approvals;
    }
    
    /**
     * Approve member registration
     */
    public function approveMemberRegistration($memberId, $notes = '') {
        try {
            $this->db->beginTransaction();
            
            // Get member details
            $member = $this->db->fetchOne("SELECT * FROM members WHERE id = ? AND status = 'pending'", [$memberId]);
            if (!$member) {
                throw new Exception('Member not found or already processed');
            }
            
            // Update member status
            $this->db->query(
                "UPDATE members SET status = 'active', updated_at = NOW() WHERE id = ?",
                [$memberId]
            );
            
            // Create default accounts for new member
            $this->createDefaultAccounts($memberId);
            
            // Log approval
            $this->logApproval('member_registration', $memberId, 'approved', $notes);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Member registration approved successfully',
                'data' => $member
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Approval failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Reject member registration
     */
    public function rejectMemberRegistration($memberId, $reason) {
        try {
            $this->db->beginTransaction();
            
            // Update member status
            $this->db->query(
                "UPDATE members SET status = 'rejected', updated_at = NOW() WHERE id = ?",
                [$memberId]
            );
            
            // Log rejection
            $this->logApproval('member_registration', $memberId, 'rejected', $reason);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Member registration rejected'
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Rejection failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Approve loan application
     */
    public function approveLoanApplication($loanId, $approvedAmount = null, $notes = '') {
        try {
            $this->db->beginTransaction();
            
            // Get loan details
            $loan = $this->db->fetchOne("SELECT * FROM loans WHERE id = ? AND status = 'pending'", [$loanId]);
            if (!$loan) {
                throw new Exception('Loan application not found or already processed');
            }
            
            // Use approved amount or original amount
            $finalAmount = $approvedAmount ?: $loan['loan_amount'];
            
            // Update loan status
            $this->db->query(
                "UPDATE loans SET 
                    status = 'approved', 
                    loan_amount = ?, 
                    approval_date = CURDATE(), 
                    approved_by = ?, 
                    updated_at = NOW() 
                 WHERE id = ?",
                [$finalAmount, $this->user['id'], $loanId]
            );
            
            // Create loan account if not exists
            $this->createLoanAccount($loan['member_id'], $finalAmount);
            
            // Log approval
            $this->logApproval('loan_application', $loanId, 'approved', $notes);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Loan application approved successfully',
                'data' => [
                    'loan_id' => $loanId,
                    'approved_amount' => $finalAmount,
                    'member_id' => $loan['member_id']
                ]
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Approval failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Reject loan application
     */
    public function rejectLoanApplication($loanId, $reason) {
        try {
            $this->db->beginTransaction();
            
            // Update loan status
            $this->db->query(
                "UPDATE loans SET status = 'rejected', updated_at = NOW() WHERE id = ?",
                [$loanId]
            );
            
            // Log rejection
            $this->logApproval('loan_application', $loanId, 'rejected', $reason);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Loan application rejected'
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Rejection failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Approve large transaction
     */
    public function approveTransaction($transactionId, $notes = '') {
        try {
            $this->db->beginTransaction();
            
            // Get transaction details
            $transaction = $this->db->fetchOne(
                "SELECT * FROM transactions WHERE id = ? AND status = 'pending'", 
                [$transactionId]
            );
            if (!$transaction) {
                throw new Exception('Transaction not found or already processed');
            }
            
            // Update transaction status
            $this->db->query(
                "UPDATE transactions SET status = 'completed', approved_by = ?, updated_at = NOW() WHERE id = ?",
                [$this->user['id'], $transactionId]
            );
            
            // Update account balance
            if ($transaction['transaction_type'] === 'credit') {
                $this->db->query(
                    "UPDATE accounts SET balance = balance + ? WHERE id = ?",
                    [$transaction['amount'], $transaction['account_id']]
                );
            } else {
                $this->db->query(
                    "UPDATE accounts SET balance = balance - ? WHERE id = ?",
                    [$transaction['amount'], $transaction['account_id']]
                );
            }
            
            // Log approval
            $this->logApproval('large_transaction', $transactionId, 'approved', $notes);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Transaction approved successfully'
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Approval failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get approval statistics
     */
    public function getApprovalStats() {
        $stats = [];
        
        // Member registration stats
        $stats['members'] = $this->db->fetchOne(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
             FROM members WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        
        // Loan application stats
        $stats['loans'] = $this->db->fetchOne(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
             FROM loans WHERE application_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        
        // Transaction stats
        $stats['transactions'] = $this->db->fetchOne(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as approved,
                SUM(amount) as total_amount
             FROM transactions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        
        return $stats;
    }
    
    /**
     * Get approval history
     */
    public function getApprovalHistory($limit = 50) {
        return $this->db->fetchAll(
            "SELECT 
                al.*,
                CASE 
                    WHEN al.item_type = 'member_registration' THEN (SELECT full_name FROM members WHERE id = al.item_id)
                    WHEN al.item_type = 'loan_application' THEN (SELECT loan_number FROM loans WHERE id = al.item_id)
                    WHEN al.item_type = 'large_transaction' THEN (SELECT transaction_code FROM transactions WHERE id = al.item_id)
                END as reference,
                u.name as admin_name
             FROM approval_log al
             JOIN users u ON al.approved_by = u.id
             ORDER BY al.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }
    
    // Helper methods
    private function createDefaultAccounts($memberId) {
        // Create simpanan pokok account
        $this->db->query(
            "INSERT INTO accounts (member_id, account_number, account_type, account_name, balance, status, opened_date) 
             VALUES (?, ?, 'simpanan', 'Simpanan Pokok', 0, 'active', CURDATE())",
            [$memberId, $this->generateAccountNumber($memberId, 'SP')]
        );
        
        // Create simpanan wajib account
        $this->db->query(
            "INSERT INTO accounts (member_id, account_number, account_type, account_name, balance, status, opened_date) 
             VALUES (?, ?, 'simpanan', 'Simpanan Wajib', 0, 'active', CURDATE())",
            [$memberId, $this->generateAccountNumber($memberId, 'SW')]
        );
        
        // Create simpanan sukarela account
        $this->db->query(
            "INSERT INTO accounts (member_id, account_number, account_type, account_name, balance, status, opened_date) 
             VALUES (?, ?, 'simpanan', 'Simpanan Sukarela', 0, 'active', CURDATE())",
            [$memberId, $this->generateAccountNumber($memberId, 'SS')]
        );
    }
    
    private function createLoanAccount($memberId, $loanAmount) {
        $this->db->query(
            "INSERT INTO accounts (member_id, account_number, account_type, account_name, balance, status, opened_date) 
             VALUES (?, ?, 'pinjaman', 'Pinjaman Aktif', ?, 'active', CURDATE())",
            [$memberId, $this->generateAccountNumber($memberId, 'PJ'), $loanAmount]
        );
    }
    
    private function generateAccountNumber($memberId, $prefix) {
        $memberCode = str_pad($memberId, 6, '0', STR_PAD_LEFT);
        $sequence = $this->db->fetchOne(
            "SELECT COUNT(*) + 1 as count FROM accounts WHERE member_id = ?",
            [$memberId]
        )['count'];
        
        return $prefix . $memberCode . str_pad($sequence, 2, '0', STR_PAD_LEFT);
    }
    
    private function logApproval($itemType, $itemId, $action, $notes = '') {
        $this->db->query(
            "INSERT INTO approval_log (item_type, item_id, action, notes, approved_by, created_at) 
             VALUES (?, ?, ?, ?, ?, NOW())",
            [$itemType, $itemId, $action, $notes, $this->user['id']]
        );
    }
}
