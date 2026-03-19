<?php
/**
 * Analytics Controller
 * Handles business intelligence, reporting, and data analytics
 */

class AnalyticsController {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get dashboard analytics
     */
    public function getDashboardAnalytics($filters = []) {
        $analytics = [];
        
        try {
            // Total assets
            $analytics['total_assets'] = $this->getTotalAssets($filters);
            
            // Active members
            $analytics['active_members'] = $this->getActiveMembers($filters);
            
            // Loan portfolio
            $analytics['loan_portfolio'] = $this->getLoanPortfolio($filters);
            
            // Profit margin
            $analytics['profit_margin'] = $this->getProfitMargin($filters);
            
            // Growth metrics
            $analytics['growth_metrics'] = $this->getGrowthMetrics($filters);
            
            // Performance trends
            $analytics['performance_trends'] = $this->getPerformanceTrends($filters);
            
            return $analytics;
            
        } catch (Exception $e) {
            error_log("Error getting dashboard analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generate custom report
     */
    public function generateReport($reportConfig) {
        try {
            $reportType = $reportConfig['type'];
            $startDate = $reportConfig['start_date'];
            $endDate = $reportConfig['end_date'];
            $format = $reportConfig['format'];
            
            // Generate report data based on type
            switch ($reportType) {
                case 'financial':
                    $data = $this->generateFinancialReport($startDate, $endDate);
                    break;
                case 'member':
                    $data = $this->generateMemberReport($startDate, $endDate);
                    break;
                case 'loan':
                    $data = $this->generateLoanReport($startDate, $endDate);
                    break;
                case 'performance':
                    $data = $this->generatePerformanceReport($startDate, $endDate);
                    break;
                case 'custom':
                    $data = $this->generateCustomReport($reportConfig);
                    break;
                default:
                    return ['success' => false, 'message' => 'Invalid report type'];
            }
            
            // Save report record
            $reportId = $this->saveReportRecord($reportConfig, $data);
            
            // Export report in requested format
            $exportResult = $this->exportReport($data, $format, $reportType);
            
            return [
                'success' => true,
                'message' => 'Report generated successfully',
                'report_id' => $reportId,
                'data' => $data,
                'export_path' => $exportResult['path']
            ];
            
        } catch (Exception $e) {
            error_log("Error generating report: " . $e->getMessage());
            return ['success' => false, 'message' => 'Report generation failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get revenue trend data
     */
    public function getRevenueTrend($period = 'monthly') {
        try {
            $query = $this->getRevenueQuery($period);
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting revenue trend: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get member analytics
     */
    public function getMemberAnalytics($filters = []) {
        $analytics = [];
        
        try {
            // Member growth
            $analytics['member_growth'] = $this->getMemberGrowth($filters);
            
            // Member demographics
            $analytics['demographics'] = $this->getMemberDemographics($filters);
            
            // Member activity
            $analytics['activity'] = $this->getMemberActivity($filters);
            
            // Member retention
            $analytics['retention'] = $this->getMemberRetention($filters);
            
            return $analytics;
            
        } catch (Exception $e) {
            error_log("Error getting member analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get loan analytics
     */
    public function getLoanAnalytics($filters = []) {
        $analytics = [];
        
        try {
            // Loan portfolio performance
            $analytics['portfolio_performance'] = $this->getLoanPortfolioPerformance($filters);
            
            // Loan distribution
            $analytics['distribution'] = $this->getLoanDistribution($filters);
            
            // Loan quality
            $analytics['quality_metrics'] = $this->getLoanQualityMetrics($filters);
            
            // Loan trends
            $analytics['trends'] = $this->getLoanTrends($filters);
            
            return $analytics;
            
        } catch (Exception $e) {
            error_log("Error getting loan analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get KPI metrics
     */
    public function getKPIMetrics($period = 'monthly') {
        $metrics = [];
        
        try {
            // Financial KPIs
            $metrics['financial'] = $this->getFinancialKPIs($period);
            
            // Operational KPIs
            $metrics['operational'] = $this->getOperationalKPIs($period);
            
            // Member KPIs
            $metrics['member'] = $this->getMemberKPIs($period);
            
            // Risk KPIs
            $metrics['risk'] = $this->getRiskKPIs($period);
            
            return $metrics;
            
        } catch (Exception $e) {
            error_log("Error getting KPI metrics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get top performers
     */
    public function getTopPerformers($metric = 'transactions', $limit = 10) {
        try {
            $query = $this->getTopPerformersQuery($metric);
            
            if ($limit > 0) {
                $query .= " LIMIT ?";
            }
            
            $stmt = $this->db->prepare($query);
            $params = $limit > 0 ? [$limit] : [];
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting top performers: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent reports
     */
    public function getRecentReports($limit = 20) {
        $query = "SELECT r.*, u.name as generated_by_name 
                 FROM analytics_reports r 
                 LEFT JOIN users u ON r.generated_by = u.id 
                 ORDER BY r.created_at DESC 
                 LIMIT ?";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting recent reports: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Private helper methods
     */
    
    private function getTotalAssets($filters) {
        $query = "SELECT SUM(s.balance) as total_assets 
                 FROM savings s 
                 LEFT JOIN members m ON s.member_id = m.id 
                 WHERE m.status = 'active'";
        
        $params = [];
        if (!empty($filters['date'])) {
            $query .= " AND s.created_at <= ?";
            $params[] = $filters['date'];
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'value' => $result['total_assets'] ?? 0,
            'change' => $this->calculateChange('total_assets', $filters),
            'trend' => $this->getTrend('total_assets', $filters)
        ];
    }
    
    private function getActiveMembers($filters) {
        $query = "SELECT COUNT(*) as active_members 
                 FROM members 
                 WHERE status = 'active'";
        
        $params = [];
        if (!empty($filters['date'])) {
            $query .= " AND created_at <= ?";
            $params[] = $filters['date'];
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'value' => $result['active_members'] ?? 0,
            'change' => $this->calculateChange('active_members', $filters),
            'trend' => $this->getTrend('active_members', $filters)
        ];
    }
    
    private function getLoanPortfolio($filters) {
        $query = "SELECT SUM(balance) as loan_portfolio 
                 FROM loans 
                 WHERE status = 'active'";
        
        $params = [];
        if (!empty($filters['date'])) {
            $query .= " AND created_at <= ?";
            $params[] = $filters['date'];
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'value' => $result['loan_portfolio'] ?? 0,
            'change' => $this->calculateChange('loan_portfolio', $filters),
            'trend' => $this->getTrend('loan_portfolio', $filters)
        ];
    }
    
    private function getProfitMargin($filters) {
        $query = "SELECT 
                    (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success AND type = 'savings_deposit') as income,
                    (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success AND type = 'loan_disbursement') as expenses";
        
        $params = [];
        if (!empty($filters['date'])) {
            $query = "SELECT 
                        (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success AND type = 'savings_deposit' AND created_at <= ?) as income,
                        (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success AND type = 'loan_disbursement' AND created_at <= ?) as expenses";
            $params = [$filters['date'], $filters['date']];
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $income = $result['income'] ?? 0;
        $expenses = $result['expenses'] ?? 0;
        $margin = $income > 0 ? round(($income - $expenses) / $income * 100, 2) : 0;
        
        return [
            'value' => $margin,
            'change' => $this->calculateChange('profit_margin', $filters),
            'trend' => $this->getTrend('profit_margin', $filters)
        ];
    }
    
    private function getGrowthMetrics($filters) {
        $metrics = [];
        
        // Member growth
        $metrics['member_growth'] = $this->calculateGrowthRate('members', $filters);
        
        // Revenue growth
        $metrics['revenue_growth'] = $this->calculateGrowthRate('payments', $filters);
        
        // Loan portfolio growth
        $metrics['loan_growth'] = $this->calculateGrowthRate('loans', $filters);
        
        return $metrics;
    }
    
    private function getPerformanceTrends($filters) {
        $trends = [];
        
        // Monthly trends
        $trends['monthly'] = $this->getMonthlyTrends($filters);
        
        // Quarterly trends
        $trends['quarterly'] = $this->getQuarterlyTrends($filters);
        
        // Yearly trends
        $trends['yearly'] = $this->getYearlyTrends($filters);
        
        return $trends;
    }
    
    private function calculateChange($metric, $filters) {
        // Calculate change compared to previous period
        $currentValue = $this->getCurrentValue($metric, $filters);
        $previousValue = $this->getPreviousValue($metric, $filters);
        
        if ($previousValue == 0) {
            return $currentValue > 0 ? 100 : 0;
        }
        
        return round((($currentValue - $previousValue) / $previousValue) * 100, 2);
    }
    
    private function getTrend($metric, $filters) {
        // Determine trend based on change
        $change = $this->calculateChange($metric, $filters);
        
        if ($change > 5) {
            return 'up';
        } elseif ($change < -5) {
            return 'down';
        } else {
            return 'stable';
        }
    }
    
    private function getCurrentValue($metric, $filters) {
        // Get current period value
        switch ($metric) {
            case 'total_assets':
                $query = "SELECT SUM(s.balance) as value FROM savings s LEFT JOIN members m ON s.member_id = m.id WHERE m.status = 'active'";
                break;
            case 'active_members':
                $query = "SELECT COUNT(*) as value FROM members WHERE status = 'active'";
                break;
            case 'loan_portfolio':
                $query = "SELECT SUM(balance) as value FROM loans WHERE status = 'active'";
                break;
            default:
                return 0;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['value'] ?? 0;
    }
    
    private function getPreviousValue($metric, $filters) {
        // Get previous period value (mock implementation)
        return $this->getCurrentValue($metric, $filters) * 0.9; // Mock 10% growth
    }
    
    private function calculateGrowthRate($table, $filters) {
        // Calculate growth rate for a specific table
        $query = "SELECT COUNT(*) as current FROM {$table}";
        $params = [];
        
        if (!empty($filters['date'])) {
            $query .= " WHERE created_at <= ?";
            $params[] = $filters['date'];
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $current = $stmt->fetch(PDO::FETCH_ASSOC)['current'];
        
        // Get previous period (mock implementation)
        $previous = $current * 0.85; // Mock 15% growth
        
        return $previous > 0 ? round((($current - $previous) / $previous) * 100, 2) : 0;
    }
    
    private function getMonthlyTrends($filters) {
        $query = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as period,
                    COUNT(*) as count
                 FROM payments 
                 WHERE status = 'success'
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                 ORDER BY period DESC
                 LIMIT 12";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getQuarterlyTrends($filters) {
        $query = "SELECT 
                    YEAR(created_at) as year,
                    QUARTER(created_at) as quarter,
                    COUNT(*) as count
                 FROM payments 
                 WHERE status = 'success'
                 GROUP BY YEAR(created_at), QUARTER(created_at)
                 ORDER BY year DESC, quarter DESC
                 LIMIT 8";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getYearlyTrends($filters) {
        $query = "SELECT 
                    YEAR(created_at) as year,
                    COUNT(*) as count
                 FROM payments 
                 WHERE status = 'success'
                 GROUP BY YEAR(created_at)
                 ORDER BY year DESC
                 LIMIT 5";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getRevenueQuery($period) {
        switch ($period) {
            case 'daily':
                return "SELECT DATE(created_at) as period, SUM(amount) as revenue 
                        FROM payments WHERE status = 'success' 
                        GROUP BY DATE(created_at) ORDER BY period DESC LIMIT 30";
            case 'monthly':
                return "SELECT DATE_FORMAT(created_at, '%Y-%m') as period, SUM(amount) as revenue 
                        FROM payments WHERE status = 'success' 
                        GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY period DESC LIMIT 12";
            case 'yearly':
                return "SELECT YEAR(created_at) as period, SUM(amount) as revenue 
                        FROM payments WHERE status = 'success' 
                        GROUP BY YEAR(created_at) ORDER BY period DESC LIMIT 5";
            default:
                return "SELECT DATE_FORMAT(created_at, '%Y-%m') as period, SUM(amount) as revenue 
                        FROM payments WHERE status = 'success' 
                        GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY period DESC LIMIT 12";
        }
    }
    
    private function getTopPerformersQuery($metric) {
        switch ($metric) {
            case 'transactions':
                return "SELECT u.name, COUNT(p.id) as count 
                        FROM users u 
                        LEFT JOIN payments p ON u.id = p.processed_by 
                        GROUP BY u.id, u.name 
                        ORDER BY count DESC";
            case 'amount':
                return "SELECT u.name, SUM(p.amount) as total 
                        FROM users u 
                        LEFT JOIN payments p ON u.id = p.processed_by 
                        WHERE p.status = 'success' 
                        GROUP BY u.id, u.name 
                        ORDER BY total DESC";
            case 'members':
                return "SELECT u.name, COUNT(m.id) as count 
                        FROM users u 
                        LEFT JOIN members m ON u.id = m.created_by 
                        GROUP BY u.id, u.name 
                        ORDER BY count DESC";
            default:
                return "SELECT u.name, COUNT(p.id) as count 
                        FROM users u 
                        LEFT JOIN payments p ON u.id = p.processed_by 
                        GROUP BY u.id, u.name 
                        ORDER BY count DESC";
        }
    }
    
    private function saveReportRecord($config, $data) {
        $query = "INSERT INTO analytics_reports (type, start_date, end_date, format, config, data, generated_by, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $config['type'],
            $config['start_date'],
            $config['end_date'],
            $config['format'],
            json_encode($config),
            json_encode($data),
            $config['generated_by'] ?? 1
        ]);
        
        return $this->db->lastInsertId();
    }
    
    private function exportReport($data, $format, $type) {
        $filename = $type . '_report_' . date('Y-m-d_H-i-s');
        
        switch ($format) {
            case 'pdf':
                return $this->exportToPDF($data, $filename);
            case 'excel':
                return $this->exportToExcel($data, $filename);
            case 'csv':
                return $this->exportToCSV($data, $filename);
            default:
                return ['success' => false, 'message' => 'Unsupported format'];
        }
    }
    
    private function exportToPDF($data, $filename) {
        // Mock PDF export
        $path = __DIR__ . '/../exports/' . $filename . '.pdf';
        file_put_contents($path, json_encode($data));
        
        return [
            'success' => true,
            'path' => $path,
            'format' => 'pdf'
        ];
    }
    
    private function exportToExcel($data, $filename) {
        // Mock Excel export
        $path = __DIR__ . '/../exports/' . $filename . '.xlsx';
        file_put_contents($path, json_encode($data));
        
        return [
            'success' => true,
            'path' => $path,
            'format' => 'excel'
        ];
    }
    
    private function exportToCSV($data, $filename) {
        // Mock CSV export
        $path = __DIR__ . '/../exports/' . $filename . '.csv';
        file_put_contents($path, json_encode($data));
        
        return [
            'success' => true,
            'path' => $path,
            'format' => 'csv'
        ];
    }
    
    private function generateFinancialReport($startDate, $endDate) {
        // Mock financial report generation
        return [
            'type' => 'financial',
            'period' => $startDate . ' to ' . $endDate,
            'revenue' => 25000000,
            'expenses' => 18000000,
            'profit' => 7000000,
            'profit_margin' => 28.0
        ];
    }
    
    private function generateMemberReport($startDate, $endDate) {
        // Mock member report generation
        return [
            'type' => 'member',
            'period' => $startDate . ' to ' . $endDate,
            'total_members' => 1245,
            'new_members' => 45,
            'active_members' => 1180,
            'retention_rate' => 94.8
        ];
    }
    
    private function generateLoanReport($startDate, $endDate) {
        // Mock loan report generation
        return [
            'type' => 'loan',
            'period' => $startDate . ' to ' . $endDate,
            'total_loans' => 234,
            'outstanding_amount' => 28300000,
            'default_rate' => 2.5,
            'average_loan_size' => 1200000
        ];
    }
    
    private function generatePerformanceReport($startDate, $endDate) {
        // Mock performance report generation
        return [
            'type' => 'performance',
            'period' => $startDate . ' to ' . $endDate,
            'transaction_volume' => 1234,
            'average_transaction_time' => 45,
            'customer_satisfaction' => 4.5,
            'productivity_index' => 87.3
        ];
    }
    
    private function generateCustomReport($config) {
        // Mock custom report generation
        return [
            'type' => 'custom',
            'config' => $config,
            'data' => 'Custom report data based on configuration'
        ];
    }
}
?>
