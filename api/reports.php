<?php
/**
 * KSP Lam Gabe Jaya - Reports API
 * Handle reporting operations
 */

require_once __DIR__ . '/BaseAPI.php';

class ReportsAPI extends BaseAPI {
    
    protected function processRequest() {
        switch ($this->method) {
            case 'GET':
                $this->handleGet();
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }
    
    /**
     * GET /api/reports.php - Generate various reports
     */
    private function handleGet() {
        $this->requireAuth();
        
        $type = $this->sanitize($this->params['type'] ?? '');
        
        switch ($type) {
            case 'dashboard':
                $this->getDashboardReport();
                break;
            case 'members':
                $this->getMembersReport();
                break;
            case 'accounts':
                $this->getAccountsReport();
                break;
            case 'loans':
                $this->getLoansReport();
                break;
            case 'transactions':
                $this->getTransactionsReport();
                break;
            case 'savings':
                $this->getSavingsReport();
                break;
            default:
                $this->sendError('Invalid report type. Available: dashboard, members, accounts, loans, transactions, savings');
        }
    }
    
    /**
     * Dashboard summary report
     */
    private function getDashboardReport() {
        $date_from = $this->params['date_from'] ?? date('Y-m-01');
        $date_to = $this->params['date_to'] ?? date('Y-m-d');
        
        if (!$this->validateDate($date_from) || !$this->validateDate($date_to)) {
            $this->sendError('Invalid date format');
        }
        
        $report = [];
        
        // Total members
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                   SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                   SUM(CASE WHEN DATE(join_date) BETWEEN :date_from AND :date_to THEN 1 ELSE 0 END) as new_members
            FROM members
        ");
        $stmt->execute(['date_from' => $date_from, 'date_to' => $date_to]);
        $report['members'] = $stmt->fetch();
        
        // Total accounts
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                   SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
                   SUM(balance) as total_balance,
                   SUM(CASE WHEN account_type = 'simpanan' THEN balance ELSE 0 END) as savings_balance,
                   SUM(CASE WHEN account_type = 'pinjaman' THEN balance ELSE 0 END) as loan_balance
            FROM accounts
        ");
        $stmt->execute();
        $report['accounts'] = $stmt->fetch();
        
        // Format balances
        $report['accounts']['total_balance'] = floatval($report['accounts']['total_balance']);
        $report['accounts']['savings_balance'] = floatval($report['accounts']['savings_balance']);
        $report['accounts']['loan_balance'] = floatval($report['accounts']['loan_balance']);
        
        // Loans summary
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                   SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                   SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                   SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                   SUM(CASE WHEN status = 'defaulted' THEN 1 ELSE 0 END) as defaulted,
                   SUM(loan_amount) as total_loan_amount,
                   SUM(CASE WHEN status IN ('active', 'completed') THEN loan_amount ELSE 0 END) as disbursed_amount
            FROM loans
        ");
        $stmt->execute();
        $report['loans'] = $stmt->fetch();
        
        // Format amounts
        $report['loans']['total_loan_amount'] = floatval($report['loans']['total_loan_amount']);
        $report['loans']['disbursed_amount'] = floatval($report['loans']['disbursed_amount']);
        
        // Transactions summary
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_transactions,
                   SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END) as total_credits,
                   SUM(CASE WHEN transaction_type = 'debit' THEN amount ELSE 0 END) as total_debits,
                   SUM(amount) as net_amount,
                   COUNT(CASE WHEN DATE(transaction_date) BETWEEN :date_from AND :date_to THEN 1 END) as period_transactions
            FROM transactions
            WHERE DATE(transaction_date) BETWEEN :date_from AND :date_to
        ");
        $stmt->execute(['date_from' => $date_from, 'date_to' => $date_to]);
        $report['transactions'] = $stmt->fetch();
        
        // Format amounts
        $report['transactions']['total_credits'] = floatval($report['transactions']['total_credits']);
        $report['transactions']['total_debits'] = floatval($report['transactions']['total_debits']);
        $report['transactions']['net_amount'] = floatval($report['transactions']['net_amount']);
        
        // Recent activities
        $stmt = $this->db->prepare("
            SELECT al.*, u.username 
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC 
            LIMIT 10
        ");
        $stmt->execute();
        $report['recent_activities'] = $stmt->fetchAll();
        
        foreach ($report['recent_activities'] as &$activity) {
            $activity['created_at'] = date('Y-m-d H:i:s', strtotime($activity['created_at']));
        }
        
        $this->sendSuccess('Dashboard report generated successfully', $report);
    }
    
    /**
     * Members report
     */
    private function getMembersReport() {
        $date_from = $this->params['date_from'] ?? date('Y-m-01');
        $date_to = $this->params['date_to'] ?? date('Y-m-d');
        $status = $this->sanitize($this->params['status'] ?? '');
        
        if (!$this->validateDate($date_from) || !$this->validateDate($date_to)) {
            $this->sendError('Invalid date format');
        }
        
        // Members by status
        $sql = "
            SELECT m.*, 
                   (SELECT COUNT(*) FROM accounts WHERE member_id = m.id AND status = 'active') as active_accounts,
                   (SELECT SUM(balance) FROM accounts WHERE member_id = m.id AND status = 'active') as total_balance,
                   (SELECT COUNT(*) FROM loans WHERE member_id = m.id AND status IN ('active', 'completed')) as total_loans
            FROM members m
            WHERE DATE(m.join_date) BETWEEN :date_from AND :date_to
        ";
        
        $params = ['date_from' => $date_from, 'date_to' => $date_to];
        
        if ($status) {
            $sql .= " AND m.status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY m.join_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $members = $stmt->fetchAll();
        
        // Format data
        foreach ($members as &$member) {
            $member['total_balance'] = floatval($member['total_balance']);
            $member['join_date'] = date('Y-m-d', strtotime($member['join_date']));
            $member['birth_date'] = $member['birth_date'] ? date('Y-m-d', strtotime($member['birth_date'])) : null;
        }
        
        // Summary statistics
        $summary = [
            'total_members' => count($members),
            'total_balance' => array_sum(array_column($members, 'total_balance')),
            'new_members_this_period' => count($members)
        ];
        
        $report = [
            'summary' => $summary,
            'members' => $members,
            'period' => [
                'date_from' => $date_from,
                'date_to' => $date_to
            ]
        ];
        
        $this->sendSuccess('Members report generated successfully', $report);
    }
    
    /**
     * Accounts report
     */
    private function getAccountsReport() {
        $account_type = $this->sanitize($this->params['account_type'] ?? '');
        $status = $this->sanitize($this->params['status'] ?? '');
        
        $sql = "
            SELECT a.*, m.full_name as member_name, m.member_number,
                   (SELECT COUNT(*) FROM transactions WHERE account_id = a.id) as transaction_count
            FROM accounts a
            LEFT JOIN members m ON a.member_id = m.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($account_type) {
            $sql .= " AND a.account_type = :account_type";
            $params['account_type'] = $account_type;
        }
        
        if ($status) {
            $sql .= " AND a.status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY a.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $accounts = $stmt->fetchAll();
        
        // Format data
        foreach ($accounts as &$account) {
            $account['balance'] = floatval($account['balance']);
            $account['interest_rate'] = $account['interest_rate'] ? floatval($account['interest_rate']) : null;
            $account['opened_date'] = date('Y-m-d', strtotime($account['opened_date']));
            $account['closed_date'] = $account['closed_date'] ? date('Y-m-d', strtotime($account['closed_date'])) : null;
        }
        
        // Summary statistics
        $summary = [
            'total_accounts' => count($accounts),
            'total_balance' => array_sum(array_column($accounts, 'balance')),
            'active_accounts' => count(array_filter($accounts, fn($a) => $a['status'] === 'active')),
            'closed_accounts' => count(array_filter($accounts, fn($a) => $a['status'] === 'closed'))
        ];
        
        $report = [
            'summary' => $summary,
            'accounts' => $accounts
        ];
        
        $this->sendSuccess('Accounts report generated successfully', $report);
    }
    
    /**
     * Loans report
     */
    private function getLoansReport() {
        $status = $this->sanitize($this->params['status'] ?? '');
        $date_from = $this->params['date_from'] ?? date('Y-m-01');
        $date_to = $this->params['date_to'] ?? date('Y-m-d');
        
        if (!$this->validateDate($date_from) || !$this->validateDate($date_to)) {
            $this->sendError('Invalid date format');
        }
        
        $sql = "
            SELECT l.*, m.full_name as member_name, m.member_number,
                   (SELECT SUM(amount) FROM loan_payments WHERE loan_id = l.id) as total_paid,
                   (l.loan_amount - COALESCE((SELECT SUM(amount) FROM loan_payments WHERE loan_id = l.id), 0)) as remaining_balance
            FROM loans l
            LEFT JOIN members m ON l.member_id = m.id
            WHERE DATE(l.application_date) BETWEEN :date_from AND :date_to
        ";
        
        $params = ['date_from' => $date_from, 'date_to' => $date_to];
        
        if ($status) {
            $sql .= " AND l.status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY l.application_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $loans = $stmt->fetchAll();
        
        // Format data
        foreach ($loans as &$loan) {
            $loan['loan_amount'] = floatval($loan['loan_amount']);
            $loan['interest_rate'] = floatval($loan['interest_rate']);
            $loan['total_paid'] = floatval($loan['total_paid']);
            $loan['remaining_balance'] = floatval($loan['remaining_balance']);
            $loan['application_date'] = date('Y-m-d', strtotime($loan['application_date']));
            $loan['approval_date'] = $loan['approval_date'] ? date('Y-m-d', strtotime($loan['approval_date'])) : null;
            $loan['disbursement_date'] = $loan['disbursement_date'] ? date('Y-m-d', strtotime($loan['disbursement_date'])) : null;
            $loan['due_date'] = $loan['due_date'] ? date('Y-m-d', strtotime($loan['due_date'])) : null;
        }
        
        // Summary statistics
        $summary = [
            'total_loans' => count($loans),
            'total_loan_amount' => array_sum(array_column($loans, 'loan_amount')),
            'total_paid' => array_sum(array_column($loans, 'total_paid')),
            'total_remaining' => array_sum(array_column($loans, 'remaining_balance')),
            'pending_loans' => count(array_filter($loans, fn($l) => $l['status'] === 'pending')),
            'active_loans' => count(array_filter($loans, fn($l) => $l['status'] === 'active')),
            'completed_loans' => count(array_filter($loans, fn($l) => $l['status'] === 'completed'))
        ];
        
        $report = [
            'summary' => $summary,
            'loans' => $loans,
            'period' => [
                'date_from' => $date_from,
                'date_to' => $date_to
            ]
        ];
        
        $this->sendSuccess('Loans report generated successfully', $report);
    }
    
    /**
     * Transactions report
     */
    private function getTransactionsReport() {
        $date_from = $this->params['date_from'] ?? date('Y-m-01');
        $date_to = $this->params['date_to'] ?? date('Y-m-d');
        $transaction_type = $this->sanitize($this->params['transaction_type'] ?? '');
        
        if (!$this->validateDate($date_from) || !$this->validateDate($date_to)) {
            $this->sendError('Invalid date format');
        }
        
        $sql = "
            SELECT t.*, a.account_number, a.account_name, m.full_name as member_name, m.member_number
            FROM transactions t
            LEFT JOIN accounts a ON t.account_id = a.id
            LEFT JOIN members m ON a.member_id = m.id
            WHERE DATE(t.transaction_date) BETWEEN :date_from AND :date_to
        ";
        
        $params = ['date_from' => $date_from, 'date_to' => $date_to];
        
        if ($transaction_type) {
            $sql .= " AND t.transaction_type = :transaction_type";
            $params['transaction_type'] = $transaction_type;
        }
        
        $sql .= " ORDER BY t.transaction_date DESC, t.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll();
        
        // Format data
        foreach ($transactions as &$transaction) {
            $transaction['amount'] = floatval($transaction['amount']);
            $transaction['transaction_date'] = date('Y-m-d', strtotime($transaction['transaction_date']));
            $transaction['created_at'] = date('Y-m-d H:i:s', strtotime($transaction['created_at']));
        }
        
        // Summary statistics
        $summary = [
            'total_transactions' => count($transactions),
            'total_credits' => array_sum(array_filter(array_column($transactions, 'amount'), fn($a, $k) => $transactions[$k]['transaction_type'] === 'credit')),
            'total_debits' => array_sum(array_filter(array_column($transactions, 'amount'), fn($a, $k) => $transactions[$k]['transaction_type'] === 'debit')),
            'net_amount' => array_sum(array_column($transactions, 'amount'))
        ];
        
        $report = [
            'summary' => $summary,
            'transactions' => $transactions,
            'period' => [
                'date_from' => $date_from,
                'date_to' => $date_to
            ]
        ];
        
        $this->sendSuccess('Transactions report generated successfully', $report);
    }
    
    /**
     * Savings report
     */
    private function getSavingsReport() {
        $date_from = $this->params['date_from'] ?? date('Y-m-01');
        $date_to = $this->params['date_to'] ?? date('Y-m-d');
        $savings_type = $this->sanitize($this->params['savings_type'] ?? '');
        
        if (!$this->validateDate($date_from) || !$this->validateDate($date_to)) {
            $this->sendError('Invalid date format');
        }
        
        $sql = "
            SELECT s.*, m.full_name as member_name, m.member_number
            FROM savings s
            LEFT JOIN members m ON s.member_id = m.id
            WHERE DATE(s.transaction_date) BETWEEN :date_from AND :date_to
        ";
        
        $params = ['date_from' => $date_from, 'date_to' => $date_to];
        
        if ($savings_type) {
            $sql .= " AND s.savings_type = :savings_type";
            $params['savings_type'] = $savings_type;
        }
        
        $sql .= " ORDER BY s.transaction_date DESC, s.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $savings = $stmt->fetchAll();
        
        // Format data
        foreach ($savings as &$saving) {
            $saving['amount'] = floatval($saving['amount']);
            $saving['transaction_date'] = date('Y-m-d', strtotime($saving['transaction_date']));
            $saving['created_at'] = date('Y-m-d H:i:s', strtotime($saving['created_at']));
        }
        
        // Summary statistics
        $summary = [
            'total_savings' => count($savings),
            'total_amount' => array_sum(array_column($savings, 'amount')),
            'by_type' => []
        ];
        
        // Group by type
        foreach ($savings as $saving) {
            $type = $saving['savings_type'];
            if (!isset($summary['by_type'][$type])) {
                $summary['by_type'][$type] = ['count' => 0, 'amount' => 0];
            }
            $summary['by_type'][$type]['count']++;
            $summary['by_type'][$type]['amount'] += $saving['amount'];
        }
        
        $report = [
            'summary' => $summary,
            'savings' => $savings,
            'period' => [
                'date_from' => $date_from,
                'date_to' => $date_to
            ]
        ];
        
        $this->sendSuccess('Savings report generated successfully', $report);
    }
}

// Handle request
$api = new ReportsAPI();
$api->handleRequest();
?>
