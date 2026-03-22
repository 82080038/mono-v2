<?php
/**
 * Comprehensive Gap Analysis Tool
 * Identifikasi gap dalam aplikasi KSP Lam Gabe Jaya
 */

require_once __DIR__ . '/config/error-config.php';

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/DatabaseHelper.php';

class GapAnalysis {
    private $db;
    private $gaps = [];
    
    public function __construct() {
        $this->db = DatabaseHelper::getInstance();
    }
    
    public function runComprehensiveGapAnalysis() {
        echo "=== KSP LAM GABE JAYA - COMPREHENSIVE GAP ANALYSIS ===\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        $this->analyzeRoleHierarchyGaps();
        $this->analyzeFeatureGaps();
        $this->analyzeDatabaseGaps();
        $this->analyzeAPIGaps();
        $this->analyzeFrontendGaps();
        $this->analyzeBusinessLogicGaps();
        $this->analyzeSecurityGaps();
        $this->analyzeIntegrationGaps();
        $this->analyzePerformanceGaps();
        
        $this->generateGapReport();
        return $this->gaps;
    }
    
    /**
     * Analisis gap role hierarchy
     */
    private function analyzeRoleHierarchyGaps() {
        echo "🔍 ANALYZING: Role Hierarchy Gaps\n";
        echo "=================================\n";
        
        $expectedRoles = ['owner', 'super_admin', 'admin', 'manager', 'teller', 'staff', 'member'];
        $currentRoles = $this->getCurrentRoles();
        $missingRoles = array_diff($expectedRoles, $currentRoles);
        
        $this->gaps['role_hierarchy'] = [
            'expected_roles' => $expectedRoles,
            'current_roles' => $currentRoles,
            'missing_roles' => $missingRoles,
            'gap_count' => count($missingRoles),
            'severity' => count($missingRoles) > 3 ? 'HIGH' : 'MEDIUM'
        ];
        
        echo "📊 Role Hierarchy Analysis:\n";
        echo "  Expected Roles: " . implode(', ', $expectedRoles) . "\n";
        echo "  Current Roles: " . implode(', ', $currentRoles) . "\n";
        echo "  Missing Roles: " . (empty($missingRoles) ? 'None' : implode(', ', $missingRoles)) . "\n";
        echo "  Gap Count: " . count($missingRoles) . "\n";
        echo "  Severity: " . $this->gaps['role_hierarchy']['severity'] . "\n\n";
    }
    
    /**
     * Analisis gap fitur
     */
    private function analyzeFeatureGaps() {
        echo "🔍 ANALYZING: Feature Gaps\n";
        echo "=========================\n";
        
        $expectedFeatures = [
            'owner_dashboard' => 'Owner-specific dashboard',
            'super_admin_tools' => 'Super admin technical tools',
            'manager_reports' => 'Manager-level reporting',
            'teller_operations' => 'Teller counter operations',
            'ai_risk_assessment' => 'AI-powered risk assessment',
            'circular_funding' => 'Circular funding system',
            'guarantee_management' => 'Guarantee management system',
            'bi_analytics' => 'Business intelligence analytics',
            'mobile_app' => 'Mobile PWA application',
            'whatsapp_integration' => 'WhatsApp API integration'
        ];
        
        $currentFeatures = $this->getCurrentFeatures();
        $missingFeatures = array_diff_key($expectedFeatures, $currentFeatures);
        
        $this->gaps['features'] = [
            'expected_features' => $expectedFeatures,
            'current_features' => $currentFeatures,
            'missing_features' => $missingFeatures,
            'gap_count' => count($missingFeatures),
            'severity' => count($missingFeatures) > 5 ? 'HIGH' : 'MEDIUM'
        ];
        
        echo "📊 Feature Analysis:\n";
        echo "  Expected Features: " . count($expectedFeatures) . "\n";
        echo "  Current Features: " . count($currentFeatures) . "\n";
        echo "  Missing Features: " . count($missingFeatures) . "\n";
        echo "  Gap Count: " . count($missingFeatures) . "\n";
        echo "  Severity: " . $this->gaps['features']['severity'] . "\n";
        
        if (!empty($missingFeatures)) {
            echo "  Missing Details:\n";
            foreach ($missingFeatures as $key => $description) {
                echo "    ❌ $key: $description\n";
            }
        }
        echo "\n";
    }
    
    /**
     * Analisis gap database
     */
    private function analyzeDatabaseGaps() {
        echo "🔍 ANALYZING: Database Gaps\n";
        echo "========================\n";
        
        $expectedTables = [
            'owners', 'super_admins', 'managers', 'tellers',
            'loan_applications', 'loan_approvals', 'loan_disbursements',
            'fund_transfers', 'staff_balances', 'circular_funds',
            'ai_models', 'risk_scores', 'fraud_detection',
            'bi_reports', 'analytics_data', 'performance_metrics'
        ];
        
        $currentTables = $this->getCurrentTables();
        $missingTables = array_diff($expectedTables, $currentTables);
        
        $this->gaps['database'] = [
            'expected_tables' => $expectedTables,
            'current_tables' => $currentTables,
            'missing_tables' => $missingTables,
            'gap_count' => count($missingTables),
            'severity' => count($missingTables) > 8 ? 'HIGH' : 'MEDIUM'
        ];
        
        echo "📊 Database Analysis:\n";
        echo "  Expected Tables: " . count($expectedTables) . "\n";
        echo "  Current Tables: " . count($currentTables) . "\n";
        echo "  Missing Tables: " . count($missingTables) . "\n";
        echo "  Gap Count: " . count($missingTables) . "\n";
        echo "  Severity: " . $this->gaps['database']['severity'] . "\n\n";
    }
    
    /**
     * Analisis gap API
     */
    private function analyzeAPIGaps() {
        echo "🔍 ANALYZING: API Gaps\n";
        echo "=====================\n";
        
        $expectedAPIs = [
            'owner-dashboard.php' => 'Owner dashboard API',
            'super-admin-tools.php' => 'Super admin tools API',
            'manager-reports.php' => 'Manager reports API',
            'teller-operations.php' => 'Teller operations API',
            'ai-risk-assessment.php' => 'AI risk assessment API',
            'circular-funding.php' => 'Circular funding API',
            'guarantee-management.php' => 'Guarantee management API',
            'bi-analytics.php' => 'Business intelligence API',
            'whatsapp-api.php' => 'WhatsApp integration API',
            'mobile-sync.php' => 'Mobile sync API'
        ];
        
        $currentAPIs = $this->getCurrentAPIs();
        $missingAPIs = array_diff_key($expectedAPIs, $currentAPIs);
        
        $this->gaps['api'] = [
            'expected_apis' => $expectedAPIs,
            'current_apis' => $currentAPIs,
            'missing_apis' => $missingAPIs,
            'gap_count' => count($missingAPIs),
            'severity' => count($missingAPIs) > 5 ? 'HIGH' : 'MEDIUM'
        ];
        
        echo "📊 API Analysis:\n";
        echo "  Expected APIs: " . count($expectedAPIs) . "\n";
        echo "  Current APIs: " . count($currentAPIs) . "\n";
        echo "  Missing APIs: " . count($missingAPIs) . "\n";
        echo "  Gap Count: " . count($missingAPIs) . "\n";
        echo "  Severity: " . $this->gaps['api']['severity'] . "\n\n";
    }
    
    /**
     * Analisis gap frontend
     */
    private function analyzeFrontendGaps() {
        echo "🔍 ANALYZING: Frontend Gaps\n";
        echo "========================\n";
        
        $expectedPages = [
            'owner' => ['dashboard.html', 'reports.html', 'settings.html'],
            'super_admin' => ['dashboard.html', 'system-tools.html', 'security.html'],
            'manager' => ['dashboard.html', 'reports.html', 'team-management.html'],
            'teller' => ['dashboard.html', 'transactions.html', 'customers.html'],
            'mobile_app' => ['manifest.json', 'service-worker.js', 'offline.html']
        ];
        
        $currentPages = $this->getCurrentPages();
        $missingPages = [];
        
        foreach ($expectedPages as $role => $pages) {
            $rolePath = __DIR__ . '/../pages/' . $role;
            if (!is_dir($rolePath)) {
                $missingPages[$role] = $pages;
            } else {
                foreach ($pages as $page) {
                    if (!file_exists($rolePath . '/' . $page)) {
                        $missingPages[$role][] = $page;
                    }
                }
            }
        }
        
        $this->gaps['frontend'] = [
            'expected_pages' => $expectedPages,
            'current_pages' => $currentPages,
            'missing_pages' => $missingPages,
            'gap_count' => array_sum(array_map('count', $missingPages)),
            'severity' => array_sum(array_map('count', $missingPages)) > 10 ? 'HIGH' : 'MEDIUM'
        ];
        
        echo "📊 Frontend Analysis:\n";
        echo "  Expected Pages: " . array_sum(array_map('count', $expectedPages)) . "\n";
        echo "  Current Pages: " . count($currentPages) . "\n";
        echo "  Missing Pages: " . array_sum(array_map('count', $missingPages)) . "\n";
        echo "  Gap Count: " . array_sum(array_map('count', $missingPages)) . "\n";
        echo "  Severity: " . $this->gaps['frontend']['severity'] . "\n\n";
    }
    
    /**
     * Analisis gap business logic
     */
    private function analyzeBusinessLogicGaps() {
        echo "🔍 ANALYZING: Business Logic Gaps\n";
        echo "===============================\n";
        
        $expectedLogic = [
            'loan_approval_workflow' => 'Multi-level loan approval',
            'circular_funding_algorithm' => 'Circular funding calculations',
            'risk_scoring_model' => 'AI risk scoring algorithm',
            'guarantee_enforcement' => 'Guarantee enforcement logic',
            'collection_optimization' => 'Collection route optimization',
            'interest_calculation' => 'Complex interest calculation',
            'profit_distribution' => 'SHU calculation algorithm',
            'fraud_detection_rules' => 'Fraud detection logic'
        ];
        
        $currentLogic = $this->getCurrentBusinessLogic();
        $missingLogic = array_diff_key($expectedLogic, $currentLogic);
        
        $this->gaps['business_logic'] = [
            'expected_logic' => $expectedLogic,
            'current_logic' => $currentLogic,
            'missing_logic' => $missingLogic,
            'gap_count' => count($missingLogic),
            'severity' => count($missingLogic) > 4 ? 'HIGH' : 'MEDIUM'
        ];
        
        echo "📊 Business Logic Analysis:\n";
        echo "  Expected Logic: " . count($expectedLogic) . "\n";
        echo "  Current Logic: " . count($currentLogic) . "\n";
        echo "  Missing Logic: " . count($missingLogic) . "\n";
        echo "  Gap Count: " . count($missingLogic) . "\n";
        echo "  Severity: " . $this->gaps['business_logic']['severity'] . "\n\n";
    }
    
    /**
     * Analisis gap security
     */
    private function analyzeSecurityGaps() {
        echo "🔍 ANALYZING: Security Gaps\n";
        echo "========================\n";
        
        $expectedSecurity = [
            'multi_factor_auth' => 'Multi-factor authentication',
            'role_based_permissions' => 'Granular role permissions',
            'data_encryption' => 'Data encryption at rest',
            'api_rate_limiting' => 'API rate limiting',
            'audit_logging' => 'Comprehensive audit logging',
            'session_management' => 'Secure session management',
            'input_validation' => 'Input validation & sanitization',
            'csrf_protection' => 'CSRF token protection'
        ];
        
        $currentSecurity = $this->getCurrentSecurity();
        $missingSecurity = array_diff_key($expectedSecurity, $currentSecurity);
        
        $this->gaps['security'] = [
            'expected_security' => $expectedSecurity,
            'current_security' => $currentSecurity,
            'missing_security' => $missingSecurity,
            'gap_count' => count($missingSecurity),
            'severity' => count($missingSecurity) > 3 ? 'HIGH' : 'MEDIUM'
        ];
        
        echo "📊 Security Analysis:\n";
        echo "  Expected Security: " . count($expectedSecurity) . "\n";
        echo "  Current Security: " . count($currentSecurity) . "\n";
        echo "  Missing Security: " . count($missingSecurity) . "\n";
        echo "  Gap Count: " . count($missingSecurity) . "\n";
        echo "  Severity: " . $this->gaps['security']['severity'] . "\n\n";
    }
    
    /**
     * Analisis gap integration
     */
    private function analyzeIntegrationGaps() {
        echo "🔍 ANALYZING: Integration Gaps\n";
        echo "==========================\n";
        
        $expectedIntegrations = [
            'people_database' => 'People management database',
            'address_database' => 'Address validation database',
            'payment_gateway' => 'Payment gateway integration',
            'banking_api' => 'Banking API integration',
            'whatsapp_api' => 'WhatsApp API integration',
            'sms_gateway' => 'SMS gateway integration',
            'email_service' => 'Email service integration',
            'bi_service' => 'Business intelligence service'
        ];
        
        $currentIntegrations = $this->getCurrentIntegrations();
        $missingIntegrations = array_diff_key($expectedIntegrations, $currentIntegrations);
        
        $this->gaps['integration'] = [
            'expected_integrations' => $expectedIntegrations,
            'current_integrations' => $currentIntegrations,
            'missing_integrations' => $missingIntegrations,
            'gap_count' => count($missingIntegrations),
            'severity' => count($missingIntegrations) > 4 ? 'HIGH' : 'MEDIUM'
        ];
        
        echo "📊 Integration Analysis:\n";
        echo "  Expected Integrations: " . count($expectedIntegrations) . "\n";
        echo "  Current Integrations: " . count($currentIntegrations) . "\n";
        echo "  Missing Integrations: " . count($missingIntegrations) . "\n";
        echo "  Gap Count: " . count($missingIntegrations) . "\n";
        echo "  Severity: " . $this->gaps['integration']['severity'] . "\n\n";
    }
    
    /**
     * Analisis gap performance
     */
    private function analyzePerformanceGaps() {
        echo "🔍 ANALYZING: Performance Gaps\n";
        echo "===========================\n";
        
        $expectedPerformance = [
            'database_optimization' => 'Query optimization',
            'caching_system' => 'Caching mechanism',
            'load_balancing' => 'Load balancing',
            'monitoring_system' => 'Performance monitoring',
            'backup_system' => 'Automated backup',
            'error_logging' => 'Error logging',
            'metrics_collection' => 'Performance metrics'
        ];
        
        $currentPerformance = $this->getCurrentPerformance();
        $missingPerformance = array_diff_key($expectedPerformance, $currentPerformance);
        
        $this->gaps['performance'] = [
            'expected_performance' => $expectedPerformance,
            'current_performance' => $currentPerformance,
            'missing_performance' => $missingPerformance,
            'gap_count' => count($missingPerformance),
            'severity' => count($missingPerformance) > 3 ? 'HIGH' : 'MEDIUM'
        ];
        
        echo "📊 Performance Analysis:\n";
        echo "  Expected Performance: " . count($expectedPerformance) . "\n";
        echo "  Current Performance: " . count($currentPerformance) . "\n";
        echo "  Missing Performance: " . count($missingPerformance) . "\n";
        echo "  Gap Count: " . count($missingPerformance) . "\n";
        echo "  Severity: " . $this->gaps['performance']['severity'] . "\n\n";
    }
    
    // Helper methods untuk mendapatkan data saat ini
    private function getCurrentRoles() {
        try {
            $roles = $this->db->fetchAll("SELECT DISTINCT role FROM users");
            return array_map(fn($r) => $r['role'], $roles);
        } catch (Exception $e) {
            return ['creator', 'admin', 'staff', 'member'];
        }
    }
    
    private function getCurrentFeatures() {
        $features = [];
        
        // Check API files
        $apiFiles = glob(__DIR__ . '/*.php');
        foreach ($apiFiles as $file) {
            $filename = basename($file);
            $features[$filename] = 'API endpoint';
        }
        
        // Check dashboard files
        $dashboardFiles = [
            'creator-dashboard.php' => 'Creator dashboard',
            'ai-risk-assessment.php' => 'AI risk assessment',
            'guarantee-risk-management.php' => 'Guarantee management'
        ];
        
        foreach ($dashboardFiles as $file => $description) {
            if (file_exists(__DIR__ . '/' . $file)) {
                $features[$file] = $description;
            }
        }
        
        return $features;
    }
    
    private function getCurrentTables() {
        try {
            $tables = $this->db->fetchAll("SHOW TABLES");
            return array_map(fn($t) => array_values($t)[0], $tables);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getCurrentAPIs() {
        $apiFiles = glob(__DIR__ . '/*.php');
        $apis = [];
        
        foreach ($apiFiles as $file) {
            $filename = basename($file);
            $apis[$filename] = 'API endpoint';
        }
        
        return $apis;
    }
    
    private function getCurrentPages() {
        $pages = [];
        $pageDirs = ['admin', 'staff', 'member', 'creator'];
        
        foreach ($pageDirs as $dir) {
            $dirPath = __DIR__ . '/../pages/' . $dir;
            if (is_dir($dirPath)) {
                $files = glob($dirPath . '/*.html');
                foreach ($files as $file) {
                    $filename = basename($file);
                    $pages[$dir . '/' . $filename] = 'Page';
                }
            }
        }
        
        return $pages;
    }
    
    private function getCurrentBusinessLogic() {
        return [
            'user_management' => 'User management logic',
            'loan_management' => 'Loan management logic',
            'payment_processing' => 'Payment processing logic',
            'gps_tracking' => 'GPS tracking logic'
        ];
    }
    
    private function getCurrentSecurity() {
        return [
            'authentication' => 'Authentication system',
            'authorization' => 'Role-based access',
            'input_validation' => 'Input validation',
            'security_logging' => 'Security logging'
        ];
    }
    
    private function getCurrentIntegrations() {
        return [
            'database_integration' => 'Database integration'
        ];
    }
    
    private function getCurrentPerformance() {
        return [
            'database_queries' => 'Database operations',
            'api_responses' => 'API operations'
        ];
    }
    
    /**
     * Generate gap report
     */
    private function generateGapReport() {
        echo "📊 COMPREHENSIVE GAP ANALYSIS REPORT\n";
        echo "===================================\n";
        
        $totalGaps = 0;
        $highSeverityGaps = 0;
        $mediumSeverityGaps = 0;
        
        foreach ($this->gaps as $category => $data) {
            $totalGaps += $data['gap_count'];
            if ($data['severity'] === 'HIGH') {
                $highSeverityGaps++;
            } else {
                $mediumSeverityGaps++;
            }
        }
        
        echo "📊 Overall Gap Summary:\n";
        echo "  Total Categories: " . count($this->gaps) . "\n";
        echo "  Total Gaps: $totalGaps\n";
        echo "  High Severity: $highSeverityGaps\n";
        echo "  Medium Severity: $mediumSeverityGaps\n";
        
        echo "\n📊 Category Breakdown:\n";
        foreach ($this->gaps as $category => $data) {
            echo "  $category: {$data['gap_count']} gaps ({$data['severity']})\n";
        }
        
        echo "\n🎯 Priority Recommendations:\n";
        if ($highSeverityGaps > 0) {
            echo "  🔴 CRITICAL: Address HIGH severity gaps first\n";
            echo "  📋 Focus on: Role hierarchy, Database structure, Core features\n";
        } else {
            echo "  🟡 MODERATE: System functional but needs improvements\n";
            echo "  📋 Focus on: Feature enhancement, Performance optimization\n";
        }
        
        // Save gap analysis results
        $this->gaps['summary'] = [
            'total_gaps' => $totalGaps,
            'high_severity' => $highSeverityGaps,
            'medium_severity' => $mediumSeverityGaps,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents(__DIR__ . '/gap-analysis-results.json', json_encode($this->gaps, JSON_PRETTY_PRINT));
        echo "\n📄 Gap analysis results saved to: gap-analysis-results.json\n";
        echo "\n=== GAP ANALYSIS COMPLETED ===\n";
    }
}

// Run analysis if executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $analysis = new GapAnalysis();
    $results = $analysis->runComprehensiveGapAnalysis();
}
?>
