<?php
/**
 * Comprehensive Testing Audit Tool
 * Audit complete system before testing
 */

require_once __DIR__ . '/config/error-config.php';

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/DatabaseHelper.php';

class TestingAudit {
    private $db;
    private $results = [];
    
    public function __construct() {
        $this->db = DatabaseHelper::getInstance();
    }
    
    public function runComprehensiveAudit() {
        echo "=== KSP LAM GABE JAYA - COMPREHENSIVE TESTING AUDIT ===\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        $this->auditAPIFiles();
        $this->auditDatabase();
        $this->auditDependencies();
        $this->auditSecurity();
        $this->auditConfiguration();
        $this->auditFrontend();
        $this->auditMiddleware();
        
        $this->generateAuditReport();
        return $this->results;
    }
    
    private function auditAPIFiles() {
        echo "🔍 AUDIT: API FILES\n";
        echo "==================\n";
        
        $apiDir = __DIR__;
        $files = glob($apiDir . '/*.php');
        
        $this->results['api_files'] = [
            'total' => count($files),
            'syntax_valid' => 0,
            'syntax_errors' => [],
            'missing_auth' => [],
            'security_issues' => [],
            'large_files' => []
        ];
        
        foreach ($files as $file) {
            $filename = basename($file);
            
            // Syntax check
            $output = [];
            $returnCode = 0;
            exec("php -l \"$file\" 2>&1", $output, $returnCode);
            
            if ($returnCode === 0) {
                $this->results['api_files']['syntax_valid']++;
            } else {
                $this->results['api_files']['syntax_errors'][$filename] = $output;
            }
            
            // Check file size
            $size = filesize($file);
            if ($size > 50000) {
                $this->results['api_files']['large_files'][$filename] = round($size/1024, 2) . 'KB';
            }
            
            // Security checks
            $content = file_get_contents($file);
            if (strpos($content, 'echo json_encode($response);') !== false) {
                $this->results['api_files']['security_issues'][$filename] = 'Potential XSS vulnerability';
            }
            
            if (strpos($content, 'requireAuth') === false && strpos($content, 'SecurityMiddleware::requireAuth') === false) {
                if (strpos($content, 'member') !== false || strpos($content, 'staff') !== false) {
                    $this->results['api_files']['missing_auth'][$filename] = 'Missing authentication';
                }
            }
        }
        
        echo "📊 API Files Status:\n";
        echo "  Total Files: " . $this->results['api_files']['total'] . "\n";
        echo "  Syntax Valid: " . $this->results['api_files']['syntax_valid'] . "\n";
        echo "  Syntax Errors: " . count($this->results['api_files']['syntax_errors']) . "\n";
        echo "  Security Issues: " . count($this->results['api_files']['security_issues']) . "\n";
        echo "  Large Files: " . count($this->results['api_files']['large_files']) . "\n";
        echo "\n";
    }
    
    private function auditDatabase() {
        echo "🗄️ AUDIT: DATABASE\n";
        echo "==================\n";
        
        $tables = $this->db->fetchAll("SHOW TABLES");
        $tableList = [];
        foreach ($tables as $table) {
            $tableList[] = array_values($table)[0];
        }
        
        $coreTables = ['users', 'members', 'loans', 'savings', 'payment_transactions', 'reward_points', 'notifications', 'gps_tracking', 'audit_logs', 'system_settings'];
        
        $this->results['database'] = [
            'total_tables' => count($tables),
            'core_tables_present' => 0,
            'missing_core_tables' => [],
            'table_records' => [],
            'indexes' => 0,
            'issues' => []
        ];
        
        foreach ($coreTables as $table) {
            if (in_array($table, $tableList)) {
                $this->results['database']['core_tables_present']++;
                try {
                    $count = $this->db->fetchOne("SELECT COUNT(*) as count FROM $table")['count'];
                    $this->results['database']['table_records'][$table] = $count;
                    
                    // Check indexes
                    $indexes = $this->db->fetchAll("SHOW INDEX FROM $table");
                    $this->results['database']['indexes'] += count($indexes);
                } catch (Exception $e) {
                    $this->results['database']['issues'][] = "Error accessing table $table: " . $e->getMessage();
                }
            } else {
                $this->results['database']['missing_core_tables'][] = $table;
            }
        }
        
        echo "📊 Database Status:\n";
        echo "  Total Tables: " . $this->results['database']['total_tables'] . "\n";
        echo "  Core Tables Present: " . $this->results['database']['core_tables_present'] . "/" . count($coreTables) . "\n";
        echo "  Total Indexes: " . $this->results['database']['indexes'] . "\n";
        echo "  Issues: " . count($this->results['database']['issues']) . "\n";
        echo "\n";
    }
    
    private function auditDependencies() {
        echo "🔗 AUDIT: DEPENDENCIES\n";
        echo "====================\n";
        
        $requiredFiles = [
            'DatabaseHelper.php',
            'Logger.php',
            'DataValidator.php',
            'SecurityLogger.php',
            'AuthHelper.php',
            'SecurityHelper.php',
            'SecurityMiddleware.php'
        ];
        
        $this->results['dependencies'] = [
            'required_files' => $requiredFiles,
            'present_files' => [],
            'missing_files' => [],
            'functional' => []
        ];
        
        foreach ($requiredFiles as $file) {
            $filepath = __DIR__ . '/' . $file;
            if (file_exists($filepath)) {
                $this->results['dependencies']['present_files'][] = $file;
                
                // Test if file is functional (no syntax errors)
                $output = [];
                $returnCode = 0;
                exec("php -l \"$filepath\" 2>&1", $output, $returnCode);
                
                if ($returnCode === 0) {
                    $this->results['dependencies']['functional'][] = $file;
                }
            } else {
                $this->results['dependencies']['missing_files'][] = $file;
            }
        }
        
        echo "📊 Dependencies Status:\n";
        echo "  Required Files: " . count($requiredFiles) . "\n";
        echo "  Present Files: " . count($this->results['dependencies']['present_files']) . "\n";
        echo "  Functional Files: " . count($this->results['dependencies']['functional']) . "\n";
        echo "  Missing Files: " . count($this->results['dependencies']['missing_files']) . "\n";
        echo "\n";
    }
    
    private function auditSecurity() {
        echo "🔒 AUDIT: SECURITY\n";
        echo "==================\n";
        
        $this->results['security'] = [
            'auth_helper' => file_exists(__DIR__ . '/AuthHelper.php'),
            'security_helper' => file_exists(__DIR__ . '/SecurityHelper.php'),
            'security_middleware' => file_exists(__DIR__ . '/SecurityMiddleware.php'),
            'data_validator' => file_exists(__DIR__ . '/DataValidator.php'),
            'security_logger' => file_exists(__DIR__ . '/SecurityLogger.php'),
            'config_complete' => false,
            'jwt_configured' => false,
            'cors_configured' => false
        ];
        
        // Check configuration
        $configFile = __DIR__ . '/../config/Config.php';
        if (file_exists($configFile)) {
            $configContent = file_get_contents($configFile);
            $this->results['security']['config_complete'] = true;
            $this->results['security']['jwt_configured'] = strpos($configContent, 'JWT_SECRET') !== false;
            $this->results['security']['cors_configured'] = strpos($configContent, 'CORS_ORIGINS') !== false;
        }
        
        echo "📊 Security Status:\n";
        echo "  AuthHelper: " . ($this->results['security']['auth_helper'] ? "✅" : "❌") . "\n";
        echo "  SecurityHelper: " . ($this->results['security']['security_helper'] ? "✅" : "❌") . "\n";
        echo "  SecurityMiddleware: " . ($this->results['security']['security_middleware'] ? "✅" : "❌") . "\n";
        echo "  DataValidator: " . ($this->results['security']['data_validator'] ? "✅" : "❌") . "\n";
        echo "  SecurityLogger: " . ($this->results['security']['security_logger'] ? "✅" : "❌") . "\n";
        echo "  Config Complete: " . ($this->results['security']['config_complete'] ? "✅" : "❌") . "\n";
        echo "  JWT Configured: " . ($this->results['security']['jwt_configured'] ? "✅" : "❌") . "\n";
        echo "  CORS Configured: " . ($this->results['security']['cors_configured'] ? "✅" : "❌") . "\n";
        echo "\n";
    }
    
    private function auditConfiguration() {
        echo "⚙️ AUDIT: CONFIGURATION\n";
        echo "====================\n";
        
        $configFile = __DIR__ . '/../config/Config.php';
        $this->results['configuration'] = [
            'file_exists' => file_exists($configFile),
            'syntax_valid' => false,
            'required_settings' => [],
            'missing_settings' => []
        ];
        
        if (file_exists($configFile)) {
            // Check syntax
            $output = [];
            $returnCode = 0;
            exec("php -l \"$configFile\" 2>&1", $output, $returnCode);
            $this->results['configuration']['syntax_valid'] = $returnCode === 0;
            
            // Check required settings
            $configContent = file_get_contents($configFile);
            $requiredSettings = [
                'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD',
                'JWT_SECRET', 'JWT_EXPIRY', 'API_RATE_LIMIT',
                'CORS_ORIGINS', 'LOG_ENABLED'
            ];
            
            foreach ($requiredSettings as $setting) {
                if (strpos($configContent, $setting) !== false) {
                    $this->results['configuration']['required_settings'][] = $setting;
                } else {
                    $this->results['configuration']['missing_settings'][] = $setting;
                }
            }
        }
        
        echo "📊 Configuration Status:\n";
        echo "  Config File Exists: " . ($this->results['configuration']['file_exists'] ? "✅" : "❌") . "\n";
        echo "  Syntax Valid: " . ($this->results['configuration']['syntax_valid'] ? "✅" : "❌") . "\n";
        echo "  Required Settings: " . count($this->results['configuration']['required_settings']) . "/9\n";
        echo "  Missing Settings: " . count($this->results['configuration']['missing_settings']) . "\n";
        echo "\n";
    }
    
    private function auditFrontend() {
        echo "🎨 AUDIT: FRONTEND\n";
        echo "==================\n";
        
        $frontendDir = __DIR__ . '/../pages';
        $this->results['frontend'] = [
            'pages_exist' => is_dir($frontendDir),
            'admin_pages' => 0,
            'staff_pages' => 0,
            'member_pages' => 0,
            'total_pages' => 0,
            'issues' => []
        ];
        
        if (is_dir($frontendDir)) {
            $adminDir = $frontendDir . '/admin';
            $staffDir = $frontendDir . '/staff';
            $memberDir = $frontendDir . '/member';
            
            if (is_dir($adminDir)) {
                $this->results['frontend']['admin_pages'] = count(glob($adminDir . '/*.html'));
            }
            
            if (is_dir($staffDir)) {
                $this->results['frontend']['staff_pages'] = count(glob($staffDir . '/*.html'));
            }
            
            if (is_dir($memberDir)) {
                $this->results['frontend']['member_pages'] = count(glob($memberDir . '/*.html'));
            }
            
            $this->results['frontend']['total_pages'] = $this->results['frontend']['admin_pages'] + 
                                                        $this->results['frontend']['staff_pages'] + 
                                                        $this->results['frontend']['member_pages'];
        } else {
            $this->results['frontend']['issues'][] = 'Pages directory not found';
        }
        
        echo "📊 Frontend Status:\n";
        echo "  Pages Directory: " . ($this->results['frontend']['pages_exist'] ? "✅" : "❌") . "\n";
        echo "  Admin Pages: " . $this->results['frontend']['admin_pages'] . "\n";
        echo "  Staff Pages: " . $this->results['frontend']['staff_pages'] . "\n";
        echo "  Member Pages: " . $this->results['frontend']['member_pages'] . "\n";
        echo "  Total Pages: " . $this->results['frontend']['total_pages'] . "\n";
        echo "  Issues: " . count($this->results['frontend']['issues']) . "\n";
        echo "\n";
    }
    
    private function auditMiddleware() {
        echo "🔧 AUDIT: MIDDLEWARE\n";
        echo "===================\n";
        
        $this->results['middleware'] = [
            'security_middleware' => file_exists(__DIR__ . '/SecurityMiddleware.php'),
            'auth_helper' => file_exists(__DIR__ . '/AuthHelper.php'),
            'security_helper' => file_exists(__DIR__ . '/SecurityHelper.php'),
            'data_validator' => file_exists(__DIR__ . '/DataValidator.php'),
            'logger' => file_exists(__DIR__ . '/Logger.php'),
            'functional' => []
        ];
        
        $middlewareFiles = [
            'SecurityMiddleware.php',
            'AuthHelper.php',
            'SecurityHelper.php',
            'DataValidator.php',
            'Logger.php'
        ];
        
        foreach ($middlewareFiles as $file) {
            $filepath = __DIR__ . '/' . $file;
            if (file_exists($filepath)) {
                $output = [];
                $returnCode = 0;
                exec("php -l \"$filepath\" 2>&1", $output, $returnCode);
                
                if ($returnCode === 0) {
                    $this->results['middleware']['functional'][] = $file;
                }
            }
        }
        
        echo "📊 Middleware Status:\n";
        echo "  SecurityMiddleware: " . ($this->results['middleware']['security_middleware'] ? "✅" : "❌") . "\n";
        echo "  AuthHelper: " . ($this->results['middleware']['auth_helper'] ? "✅" : "❌") . "\n";
        echo "  SecurityHelper: " . ($this->results['middleware']['security_helper'] ? "✅" : "❌") . "\n";
        echo "  DataValidator: " . ($this->results['middleware']['data_validator'] ? "✅" : "❌") . "\n";
        echo "  Logger: " . ($this->results['middleware']['logger'] ? "✅" : "❌") . "\n";
        echo "  Functional Components: " . count($this->results['middleware']['functional']) . "/5\n";
        echo "\n";
    }
    
    private function generateAuditReport() {
        echo "📊 AUDIT SUMMARY\n";
        echo "================\n";
        
        // Calculate scores
        $apiScore = round(($this->results['api_files']['syntax_valid'] / $this->results['api_files']['total']) * 100, 2);
        $dbScore = round(($this->results['database']['core_tables_present'] / 10) * 100, 2);
        $depScore = round((count($this->results['dependencies']['functional']) / count($this->results['dependencies']['required_files'])) * 100, 2);
        $securityScore = round((array_sum($this->results['security']) / 8) * 100, 2);
        $configScore = $this->results['configuration']['syntax_valid'] ? 100 : 0;
        $middlewareScore = round((count($this->results['middleware']['functional']) / 5) * 100, 2);
        
        $overallScore = round(($apiScore + $dbScore + $depScore + $securityScore + $configScore + $middlewareScore) / 6, 2);
        
        echo "📊 Component Scores:\n";
        echo "  API Files: $apiScore%\n";
        echo "  Database: $dbScore%\n";
        echo "  Dependencies: $depScore%\n";
        echo "  Security: $securityScore%\n";
        echo "  Configuration: $configScore%\n";
        echo "  Middleware: $middlewareScore%\n";
        echo "  Overall Health: $overallScore%\n";
        
        echo "\n🎯 Testing Readiness:\n";
        if ($overallScore >= 90) {
            echo "  ✅ READY FOR COMPREHENSIVE TESTING\n";
        } elseif ($overallScore >= 75) {
            echo "  ⚠️  NEEDS MINOR FIXES BEFORE TESTING\n";
        } else {
            echo "  ❌ NOT READY FOR TESTING - NEEDS MAJOR FIXES\n";
        }
        
        // Save audit results
        $this->results['summary'] = [
            'overall_score' => $overallScore,
            'component_scores' => [
                'api' => $apiScore,
                'database' => $dbScore,
                'dependencies' => $depScore,
                'security' => $securityScore,
                'configuration' => $configScore,
                'middleware' => $middlewareScore
            ],
            'testing_ready' => $overallScore >= 90,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents(__DIR__ . '/testing-audit-results.json', json_encode($this->results, JSON_PRETTY_PRINT));
        echo "\n📄 Audit results saved to: testing-audit-results.json\n";
        echo "\n=== AUDIT COMPLETED ===\n";
    }
}

// Run audit if executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $audit = new TestingAudit();
    $results = $audit->runComprehensiveAudit();
}
?>
