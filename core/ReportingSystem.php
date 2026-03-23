<?php
/**
 * Reporting System for BOS (Board of Directors)
 * Generates comprehensive reports and dashboards
 */

// Prevent direct access
define('IN_REPORTING_SYSTEM', true);

class ReportingSystem {
    private $db;
    private $user;
    
    public function __construct($database, $user) {
        $this->db = $database;
        $this->user = $user;
    }
    
    /**
     * Get executive dashboard data
     */
    public function getExecutiveDashboard() {
        $dashboard = [];
        
        // Overall statistics
        $dashboard['overview'] = $this->getOverviewStats();
        
        // Financial performance
        $dashboard['financial'] = $this->getFinancialPerformance();
        
        // Member statistics
        $dashboard['members'] = $this->getMemberStatistics();
        
        // Loan portfolio
        $dashboard['loans'] = $this->getLoanPortfolio();
        
        // Recent activities
        $dashboard['activities'] = $this->getRecentActivities();
        
        // Risk indicators
        $dashboard['risk'] = $this->getRiskIndicators();
        
        return $dashboard;
    }
    
    /**
     * Get overview statistics
     */
    public function getOverviewStats() {
        return [
            'total_members' => $this->db->fetchOne("SELECT COUNT(*) as count FROM members WHERE status = 'active'")['count'],
            'total_deposits' => $this->db->fetchOne("SELECT SUM(balance) as total FROM accounts WHERE account_type = 'simpanan' AND status = 'active'")['total'],
            'total_loans' => $this->db->fetchOne("SELECT SUM(loan_amount) as total FROM loans WHERE status = 'active'")['total'],
            'total_users' => $this->db->fetchOne("SELECT COUNT(*) as count FROM users")['count'],
            'today_transactions' => $this->db->fetchOne("SELECT COUNT(*) as count FROM transactions WHERE DATE(created_at) = CURDATE()")['count'],
            'monthly_revenue' => $this->db->fetchOne("SELECT SUM(amount * 0.02) as total FROM transactions WHERE transaction_type = 'debit' AND MONTH(created_at) = MONTH(CURDATE())")['total']
        ];
    }
    
    /**
     * Get financial performance metrics
     */
    public function getFinancialPerformance() {
        // Monthly trends (last 12 months)
        $monthlyTrends = $this->db->fetchAll(
            "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END) as deposits,
                SUM(CASE WHEN transaction_type = 'debit' THEN amount ELSE 0 END) as withdrawals,
                SUM(CASE WHEN transaction_type = 'debit' THEN amount * 0.02 ELSE 0 END) as revenue
             FROM transactions 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY month"
        );
        
        // Account balances by type
        $accountBalances = $this->db->fetchAll(
            "SELECT 
                account_type,
                COUNT(*) as account_count,
                SUM(balance) as total_balance,
                AVG(balance) as average_balance
             FROM accounts 
             WHERE status = 'active'
             GROUP BY account_type"
        );
        
        return [
            'monthly_trends' => $monthlyTrends,
            'account_balances' => $accountBalances,
            'profit_margin' => $this->calculateProfitMargin(),
            'growth_rate' => $this->calculateGrowthRate()
        ];
    }
    
    /**
     * Get member statistics
     */
    public function getMemberStatistics() {
        // Member growth over time
        $memberGrowth = $this->db->fetchAll(
            "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as new_members,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_members
             FROM members 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY month"
        );
        
        // Member demographics
        $memberDemographics = $this->db->fetchAll(
            "SELECT 
                'Total Members' as label,
                COUNT(*) as value
             FROM members
             UNION ALL
             SELECT 
                'Active Members' as label,
                COUNT(*) as value
             FROM members WHERE status = 'active'
             UNION ALL
             SELECT 
                'New Members (This Month)' as label,
                COUNT(*) as value
             FROM members WHERE MONTH(created_at) = MONTH(CURDATE())"
        );
        
        return [
            'growth' => $memberGrowth,
            'demographics' => $memberDemographics,
            'retention_rate' => $this->calculateRetentionRate(),
            'average_member_age' => $this->calculateAverageMemberAge()
        ];
    }
    
    /**
     * Get loan portfolio analysis
     */
    public function getLoanPortfolio() {
        // Loan status distribution
        $loanStatus = $this->db->fetchAll(
            "SELECT 
                status,
                COUNT(*) as count,
                SUM(loan_amount) as total_amount,
                AVG(loan_amount) as average_amount
             FROM loans 
             GROUP BY status"
        );
        
        // Loan performance by month
        $loanPerformance = $this->db->fetchAll(
            "SELECT 
                DATE_FORMAT(application_date, '%Y-%m') as month,
                COUNT(*) as new_loans,
                SUM(loan_amount) as total_disbursed,
                SUM(CASE WHEN status = 'completed' THEN loan_amount ELSE 0 END) as total_repaid
             FROM loans 
             WHERE application_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(application_date, '%Y-%m')
             ORDER BY month"
        );
        
        // Delinquency analysis
        $delinquency = $this->db->fetchAll(
            "SELECT 
                CASE 
                    WHEN DATEDIFF(CURDATE(), due_date) <= 0 THEN 'Current'
                    WHEN DATEDIFF(CURDATE(), due_date) <= 30 THEN '1-30 Days Late'
                    WHEN DATEDIFF(CURDATE(), due_date) <= 60 THEN '31-60 Days Late'
                    WHEN DATEDIFF(CURDATE(), due_date) <= 90 THEN '61-90 Days Late'
                    ELSE '90+ Days Late'
                END as aging_bucket,
                COUNT(*) as count,
                SUM(loan_amount) as total_amount
             FROM loans 
             WHERE status = 'active' AND due_date < CURDATE()
             GROUP BY aging_bucket
             ORDER BY 
                 CASE 
                     WHEN DATEDIFF(CURDATE(), due_date) <= 0 THEN 1
                     WHEN DATEDIFF(CURDATE(), due_date) <= 30 THEN 2
                     WHEN DATEDIFF(CURDATE(), due_date) <= 60 THEN 3
                     WHEN DATEDIFF(CURDATE(), due_date) <= 90 THEN 4
                     ELSE 5
                 END"
        );
        
        return [
            'status_distribution' => $loanStatus,
            'performance' => $loanPerformance,
            'delinquency' => $delinquency,
            'portfolio_quality' => $this->calculatePortfolioQuality()
        ];
    }
    
    /**
     * Get recent activities
     */
    public function getRecentActivities($limit = 20) {
        return $this->db->fetchAll(
            "SELECT 
                'Transaction' as type,
                t.transaction_code as reference,
                t.amount,
                t.created_at,
                m.full_name as member_name,
                u.name as created_by_name
             FROM transactions t
             JOIN accounts a ON t.account_id = a.id
             JOIN members m ON a.member_id = m.id
             JOIN users u ON t.created_by = u.id
             ORDER BY t.created_at DESC
             LIMIT ?
             UNION ALL
             SELECT 
                'Loan Application' as type,
                l.loan_number as reference,
                l.loan_amount as amount,
                l.application_date as created_at,
                m.full_name as member_name,
                'System' as created_by_name
             FROM loans l
             JOIN members m ON l.member_id = m.id
             ORDER BY l.application_date DESC
             LIMIT ?",
            [$limit, $limit]
        );
    }
    
    /**
     * Get risk indicators
     */
    public function getRiskIndicators() {
        return [
            'npl_ratio' => $this->calculateNPLRatio(),
            'liquidity_ratio' => $this->calculateLiquidityRatio(),
            'capital_adequacy' => $this->calculateCapitalAdequacy(),
            'concentration_risk' => $this->calculateConcentrationRisk(),
            'credit_risk' => $this->calculateCreditRisk()
        ];
    }
    
    /**
     * Generate monthly report
     */
    public function generateMonthlyReport($year, $month) {
        $report = [];
        
        // Basic statistics
        $report['period'] = "$year-$month";
        $report['generated_at'] = date('Y-m-d H:i:s');
        $report['generated_by'] = $this->user['name'];
        
        // Financial summary
        $report['financial_summary'] = $this->getMonthlyFinancialSummary($year, $month);
        
        // Operations summary
        $report['operations_summary'] = $this->getMonthlyOperationsSummary($year, $month);
        
        // Compliance summary
        $report['compliance_summary'] = $this->getMonthlyComplianceSummary($year, $month);
        
        return $report;
    }
    
    /**
     * Export report to Excel/CSV
     */
    public function exportReport($reportType, $format = 'csv') {
        switch ($reportType) {
            case 'financial':
                $data = $this->getFinancialPerformance();
                break;
            case 'loans':
                $data = $this->getLoanPortfolio();
                break;
            case 'members':
                $data = $this->getMemberStatistics();
                break;
            default:
                $data = $this->getExecutiveDashboard();
        }
        
        if ($format === 'csv') {
            return $this->convertToCSV($data);
        } elseif ($format === 'excel') {
            return $this->convertToExcel($data);
        }
        
        return $data;
    }
    
    // Helper methods for calculations
    private function calculateProfitMargin() {
        $revenue = $this->db->fetchOne("SELECT SUM(amount * 0.02) as total FROM transactions WHERE transaction_type = 'debit' AND MONTH(created_at) = MONTH(CURDATE())")['total'];
        $expenses = $this->db->fetchOne("SELECT SUM(amount * 0.01) as total FROM transactions WHERE transaction_type = 'credit' AND MONTH(created_at) = MONTH(CURDATE())")['total'];
        
        return $revenue > 0 ? (($revenue - $expenses) / $revenue) * 100 : 0;
    }
    
    private function calculateGrowthRate() {
        $currentMonth = $this->db->fetchOne("SELECT SUM(balance) as total FROM accounts WHERE status = 'active' AND MONTH(updated_at) = MONTH(CURDATE())")['total'];
        $lastMonth = $this->db->fetchOne("SELECT SUM(balance) as total FROM accounts WHERE status = 'active' AND MONTH(updated_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH))")['total'];
        
        return $lastMonth > 0 ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : 0;
    }
    
    private function calculateRetentionRate() {
        $totalMembers = $this->db->fetchOne("SELECT COUNT(*) as count FROM members WHERE created_at <= DATE_SUB(NOW(), INTERVAL 12 MONTH)")['count'];
        $activeMembers = $this->db->fetchOne("SELECT COUNT(*) as count FROM members WHERE status = 'active' AND created_at <= DATE_SUB(NOW(), INTERVAL 12 MONTH)")['count'];
        
        return $totalMembers > 0 ? ($activeMembers / $totalMembers) * 100 : 0;
    }
    
    private function calculateAverageMemberAge() {
        return $this->db->fetchOne("SELECT AVG(DATEDIFF(CURDATE(), created_at)) as avg_age FROM members")['avg_age'];
    }
    
    private function calculatePortfolioQuality() {
        $totalLoans = $this->db->fetchOne("SELECT SUM(loan_amount) as total FROM loans WHERE status = 'active'")['total'];
        $badLoans = $this->db->fetchOne("SELECT SUM(loan_amount) as total FROM loans WHERE status = 'active' AND due_date < DATE_SUB(CURDATE(), INTERVAL 90 DAY)")['total'];
        
        return $totalLoans > 0 ? (($totalLoans - $badLoans) / $totalLoans) * 100 : 100;
    }
    
    private function calculateNPLRatio() {
        $totalLoans = $this->db->fetchOne("SELECT SUM(loan_amount) as total FROM loans WHERE status = 'active'")['total'];
        $nonPerformingLoans = $this->db->fetchOne("SELECT SUM(loan_amount) as total FROM loans WHERE status = 'active' AND due_date < DATE_SUB(CURDATE(), INTERVAL 90 DAY)")['total'];
        
        return $totalLoans > 0 ? ($nonPerformingLoans / $totalLoans) * 100 : 0;
    }
    
    private function calculateLiquidityRatio() {
        $liquidAssets = $this->db->fetchOne("SELECT SUM(balance) as total FROM accounts WHERE account_type = 'simpanan' AND status = 'active'")['total'];
        $totalLiabilities = $this->db->fetchOne("SELECT SUM(balance) as total FROM accounts WHERE account_type = 'pinjaman' AND status = 'active'")['total'];
        
        return $totalLiabilities > 0 ? ($liquidAssets / $totalLiabilities) * 100 : 0;
    }
    
    private function calculateCapitalAdequacy() {
        // Simplified calculation
        $capital = 100000000; // Assume fixed capital
        $riskWeightedAssets = $this->db->fetchOne("SELECT SUM(loan_amount) as total FROM loans WHERE status = 'active'")['total'];
        
        return $riskWeightedAssets > 0 ? ($capital / $riskWeightedAssets) * 100 : 100;
    }
    
    private function calculateConcentrationRisk() {
        $largestLoan = $this->db->fetchOne("SELECT MAX(loan_amount) as max_loan FROM loans WHERE status = 'active'")['max_loan'];
        $totalLoans = $this->db->fetchOne("SELECT SUM(loan_amount) as total FROM loans WHERE status = 'active'")['total'];
        
        return $totalLoans > 0 ? ($largestLoan / $totalLoans) * 100 : 0;
    }
    
    private function calculateCreditRisk() {
        $overdueLoans = $this->db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status = 'active' AND due_date < CURDATE()")['count'];
        $totalLoans = $this->db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status = 'active'")['count'];
        
        return $totalLoans > 0 ? ($overdueLoans / $totalLoans) * 100 : 0;
    }
    
    private function getMonthlyFinancialSummary($year, $month) {
        return $this->db->fetchOne(
            "SELECT 
                SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END) as total_deposits,
                SUM(CASE WHEN transaction_type = 'debit' THEN amount ELSE 0 END) as total_withdrawals,
                SUM(CASE WHEN transaction_type = 'debit' THEN amount * 0.02 ELSE 0 END) as revenue,
                COUNT(*) as transaction_count
             FROM transactions 
             WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?",
            [$year, $month]
        );
    }
    
    private function getMonthlyOperationsSummary($year, $month) {
        return [
            'new_members' => $this->db->fetchOne("SELECT COUNT(*) as count FROM members WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?", [$year, $month])['count'],
            'new_loans' => $this->db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE YEAR(application_date) = ? AND MONTH(application_date) = ?", [$year, $month])['count'],
            'loan_disbursements' => $this->db->fetchOne("SELECT SUM(loan_amount) as total FROM loans WHERE YEAR(application_date) = ? AND MONTH(application_date) = ? AND status = 'approved'", [$year, $month])['total']
        ];
    }
    
    private function getMonthlyComplianceSummary($year, $month) {
        return [
            'pending_approvals' => $this->db->fetchOne("SELECT COUNT(*) as count FROM members WHERE status = 'pending' AND YEAR(created_at) = ? AND MONTH(created_at) = ?", [$year, $month])['count'],
            'overdue_loans' => $this->db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status = 'active' AND due_date < CURDATE() AND YEAR(due_date) = ? AND MONTH(due_date) = ?", [$year, $month])['count']
        ];
    }
    
    private function convertToCSV($data) {
        // Simplified CSV conversion
        $csv = '';
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $csv .= "$key," . implode(',', $value) . "\n";
            } else {
                $csv .= "$key,$value\n";
            }
        }
        return $csv;
    }
    
    private function convertToExcel($data) {
        // This would typically use a library like PHPExcel
        // For now, return CSV format
        return $this->convertToCSV($data);
    }
}
