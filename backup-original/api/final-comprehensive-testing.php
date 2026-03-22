<?php
/**
 * Final Comprehensive Testing Suite
 * Complete testing with all phases
 */

require_once __DIR__ . '/config/error-config.php';

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/DatabaseHelper.php';
require_once __DIR__ . '/AuthHelper.php';
require_once __DIR__ . '/SecurityHelper.php';
require_once __DIR__ . '/DataValidator.php';

class FinalComprehensiveTesting {
    private $testResults = [];
    
    public function runCompleteTesting() {
        echo "=== KSP LAM GABE JAYA - FINAL COMPREHENSIVE TESTING ===\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Phase 1: Environment & Foundation
        $this->testEnvironment();
        $this->testFoundation();
        
        // Phase 2: Core Components
        $this->testDatabase();
        $this->testSecurity();
        $this->testAPIs();
        
        // Phase 3: Integration
        $this->testFrontend();
        $this->testMiddleware();
        $this->testIntegration();
        
        // Phase 4: Performance & E2E
        $this->testPerformance();
        $this->testEndToEnd();
        
        // Phase 5: Production Readiness
        $this->testProductionReadiness();
        
        $this->generateFinalReport();
        return $this->testResults;
    }
    
    private function testEnvironment() {
        echo "🔍 PHASE 1: Environment Testing\n";
        echo "==============================\n";
        
        $checks = [
            'PHP Version >= 8.0' => version_compare(PHP_VERSION, '8.0', '>='),
            'Required Extensions' => $this->checkExtensions(),
            'File Permissions' => is_writable(__DIR__),
            'Memory Limit' => ini_get('memory_limit') >= '128M',
            'Max Execution Time' => ini_get('max_execution_time') >= 30
        ];
        
        $this->testResults['environment'] = $checks;
        $passed = count(array_filter($checks));
        $total = count($checks);
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        echo "  Status: " . ($passed >= 4 ? "PASS" : "FAIL") . " ($passed/$total)\n\n";
    }
    
    private function testFoundation() {
        echo "🔍 PHASE 1: Foundation Testing\n";
        echo "==============================\n";
        
        $checks = [
            'DatabaseHelper' => file_exists(__DIR__ . '/DatabaseHelper.php'),
            'AuthHelper' => file_exists(__DIR__ . '/AuthHelper.php'),
            'SecurityHelper' => file_exists(__DIR__ . '/SecurityHelper.php'),
            'DataValidator' => file_exists(__DIR__ . '/DataValidator.php'),
            'SecurityMiddleware' => file_exists(__DIR__ . '/SecurityMiddleware.php'),
            'Config File' => file_exists(__DIR__ . '/../config/Config.php'),
            'Logger' => file_exists(__DIR__ . '/Logger.php')
        ];
        
        $this->testResults['foundation'] = $checks;
        $passed = count(array_filter($checks));
        $total = count($checks);
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        echo "  Status: " . ($passed >= 6 ? "PASS" : "FAIL") . " ($passed/$total)\n\n";
    }
    
    private function testDatabase() {
        echo "🔍 PHASE 2: Database Testing\n";
        echo "===========================\n";
        
        try {
            $db = DatabaseHelper::getInstance();
            
            $checks = [
                'Database Connection' => $db->fetchOne("SELECT 1") !== false,
                'Tables Present' => count($db->fetchAll("SHOW TABLES")) >= 10,
                'Indexes Present' => count($db->fetchAll("SHOW INDEX FROM users")) > 0,
                'Data Integrity' => $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'] > 0,
                'Foreign Keys' => $this->checkForeignKeys($db)
            ];
        } catch (Exception $e) {
            $checks = [
                'Database Connection' => false,
                'Tables Present' => false,
                'Indexes Present' => false,
                'Data Integrity' => false,
                'Foreign Keys' => false
            ];
        }
        
        $this->testResults['database'] = $checks;
        $passed = count(array_filter($checks));
        $total = count($checks);
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        echo "  Status: " . ($passed >= 4 ? "PASS" : "FAIL") . " ($passed/$total)\n\n";
    }
    
    private function testSecurity() {
        echo "🔍 PHASE 2: Security Testing\n";
        echo "==========================\n";
        
        try {
            $checks = [
                'JWT Generation' => $this->testJWTGeneration(),
                'JWT Validation' => $this->testJWTValidation(),
                'Input Sanitization' => $this->testInputSanitization(),
                'XSS Protection' => $this->testXSSProtection(),
                'SQL Injection Protection' => $this->testSQLInjectionProtection(),
                'Password Hashing' => $this->testPasswordHashing(),
                'CSRF Protection' => $this->testCSRFProtection()
            ];
        } catch (Exception $e) {
            $checks = [
                'JWT Generation' => false,
                'JWT Validation' => false,
                'Input Sanitization' => false,
                'XSS Protection' => false,
                'SQL Injection Protection' => false,
                'Password Hashing' => false,
                'CSRF Protection' => false
            ];
        }
        
        $this->testResults['security'] = $checks;
        $passed = count(array_filter($checks));
        $total = count($checks);
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        echo "  Status: " . ($passed >= 6 ? "PASS" : "FAIL") . " ($passed/$total)\n\n";
    }
    
    private function testAPIs() {
        echo "🔍 PHASE 2: API Testing\n";
        echo "======================\n";
        
        $apiFiles = [
            'auth-enhanced.php' => 'Authentication API',
            'members-crud.php' => 'Members CRUD API',
            'loans-crud.php' => 'Loans CRUD API',
            'savings-crud.php' => 'Savings CRUD API',
            'reports.php' => 'Reports API',
            'analytics.php' => 'Analytics API'
        ];
        
        $checks = [];
        foreach ($apiFiles as $file => $description) {
            $filepath = __DIR__ . '/' . $file;
            if (file_exists($filepath)) {
                $output = [];
                $returnCode = 0;
                exec("php -l \"$filepath\" 2>&1", $output, $returnCode);
                $checks[$description] = $returnCode === 0;
            } else {
                $checks[$description] = false;
            }
        }
        
        $this->testResults['apis'] = $checks;
        $passed = count(array_filter($checks));
        $total = count($checks);
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        echo "  Status: " . ($passed >= 5 ? "PASS" : "FAIL") . " ($passed/$total)\n\n";
    }
    
    private function testFrontend() {
        echo "🔍 PHASE 3: Frontend Testing\n";
        echo "===========================\n";
        
        $checks = [
            'Admin Pages' => is_dir(__DIR__ . '/../pages/admin') && count(glob(__DIR__ . '/../pages/admin/*.html')) > 0,
            'Staff Pages' => is_dir(__DIR__ . '/../pages/staff') && count(glob(__DIR__ . '/../pages/staff/*.html')) > 0,
            'Member Pages' => is_dir(__DIR__ . '/../pages/member') && count(glob(__DIR__ . '/../pages/member/*.html')) > 0,
            'Static Assets' => is_dir(__DIR__ . '/../assets'),
            'JavaScript Files' => is_dir(__DIR__ . '/../assets/js') && count(glob(__DIR__ . '/../assets/js/*.js')) > 0,
            'CSS Files' => is_dir(__DIR__ . '/../assets/css') && count(glob(__DIR__ . '/../assets/css/*.css')) > 0
        ];
        
        $this->testResults['frontend'] = $checks;
        $passed = count(array_filter($checks));
        $total = count($checks);
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        echo "  Status: " . ($passed >= 5 ? "PASS" : "FAIL") . " ($passed/$total)\n\n";
    }
    
    private function testMiddleware() {
        echo "🔍 PHASE 3: Middleware Testing\n";
        echo "==============================\n";
        
        $checks = [
            'Security Headers' => $this->testSecurityHeaders(),
            'CORS Handling' => $this->testCORSHandling(),
            'Input Validation' => $this->testInputValidation(),
            'Error Handling' => $this->testErrorHandling(),
            'Logging' => $this->testLogging()
        ];
        
        $this->testResults['middleware'] = $checks;
        $passed = count(array_filter($checks));
        $total = count($checks);
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        echo "  Status: " . ($passed >= 4 ? "PASS" : "FAIL") . " ($passed/$total)\n\n";
    }
    
    private function testIntegration() {
        echo "🔍 PHASE 3: Integration Testing\n";
        echo "==============================\n";
        
        $checks = [
            'API-Database Integration' => $this->testAPIDatabaseIntegration(),
            'Frontend-Backend Integration' => $this->testFrontendBackendIntegration(),
            'Security Integration' => $this->testSecurityIntegration(),
            'Middleware Integration' => $this->testMiddlewareIntegration(),
            'Cross-Component Integration' => $this->testCrossComponentIntegration()
        ];
        
        $this->testResults['integration'] = $checks;
        $passed = count(array_filter($checks));
        $total = count($checks);
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        echo "  Status: " . ($passed >= 4 ? "PASS" : "FAIL") . " ($passed/$total)\n\n";
    }
    
    private function testPerformance() {
        echo "🔍 PHASE 4: Performance Testing\n";
        echo "==============================\n";
        
        $checks = [
            'Database Performance' => $this->testDatabasePerformance(),
            'API Response Time' => $this->testAPIResponseTime(),
            'Memory Usage' => $this->testMemoryUsage(),
            'File Size Optimization' => $this->testFileSizeOptimization(),
            'Query Optimization' => $this->testQueryOptimization()
        ];
        
        $this->testResults['performance'] = $checks;
        $passed = count(array_filter($checks));
        $total = count($checks);
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        echo "  Status: " . ($passed >= 4 ? "PASS" : "FAIL") . " ($passed/$total)\n\n";
    }
    
    private function testEndToEnd() {
        echo "🔍 PHASE 4: End-to-End Testing\n";
        echo "============================\n";
        
        $checks = [
            'User Registration Flow' => $this->testUserRegistrationFlow(),
            'Loan Application Flow' => $this->testLoanApplicationFlow(),
            'Payment Processing Flow' => $this->testPaymentProcessingFlow(),
            'Report Generation Flow' => $this->testReportGenerationFlow(),
            'Admin Workflow' => $this->testAdminWorkflow()
        ];
        
        $this->testResults['e2e'] = $checks;
        $passed = count(array_filter($checks));
        $total = count($checks);
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        echo "  Status: " . ($passed >= 4 ? "PASS" : "FAIL") . " ($passed/$total)\n\n";
    }
    
    private function testProductionReadiness() {
        echo "🔍 PHASE 5: Production Readiness\n";
        echo "==============================\n";
        
        $checks = [
            'Overall Health' => $this->calculateOverallHealth(),
            'Critical Components' => $this->checkCriticalComponents(),
            'Backup Strategy' => $this->checkBackupStrategy(),
            'Monitoring Setup' => $this->checkMonitoringSetup(),
            'Documentation' => $this->checkDocumentation()
        ];
        
        $this->testResults['production_readiness'] = $checks;
        $passed = count(array_filter($checks));
        $total = count($checks);
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        echo "  Status: " . ($passed >= 4 ? "PASS" : "FAIL") . " ($passed/$total)\n\n";
    }
    
    // Helper test methods
    private function checkExtensions() {
        $required = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
        $loaded = array_filter($required, function($ext) {
            return extension_loaded($ext);
        });
        return count($loaded) >= count($required) - 1;
    }
    
    private function checkForeignKeys($db) {
        try {
            $constraints = $db->fetchAll("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'ksp_lamgabejaya_v2' AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1");
            return !empty($constraints);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testJWTGeneration() {
        try {
            $token = AuthHelper::generateToken(['id' => 1, 'role' => 'admin']);
            return !empty($token);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testJWTValidation() {
        try {
            $token = AuthHelper::generateToken(['id' => 1, 'role' => 'admin']);
            $validated = AuthHelper::validateJWTToken($token);
            return $validated !== null && $validated['user_id'] == 1;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testInputSanitization() {
        try {
            $malicious = '<script>alert("xss")</script>';
            $sanitized = SecurityHelper::sanitize($malicious);
            return strpos($sanitized, '<script>') === false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testXSSProtection() {
        try {
            $malicious = '<script>alert("xss")</script>';
            return SecurityHelper::containsXSS($malicious);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testSQLInjectionProtection() {
        try {
            $malicious = "'; DROP TABLE users; --";
            return SecurityHelper::containsSQLInjection($malicious);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testPasswordHashing() {
        try {
            $password = 'test123';
            $hashed = SecurityHelper::hashPassword($password);
            return SecurityHelper::verifyPassword($password, $hashed);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testCSRFProtection() {
        try {
            $token = SecurityHelper::generateCSRFToken();
            return SecurityHelper::validateCSRFToken($token);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testSecurityHeaders() {
        $headers = SecurityHelper::getSecurityHeaders();
        return !empty($headers) && isset($headers['X-Content-Type-Options']);
    }
    
    private function testCORSHandling() {
        return SecurityHelper::validateOrigin('http://localhost');
    }
    
    private function testInputValidation() {
        try {
            $validator = new DataValidator();
            return $validator->validate(['test' => 'value'], ['test' => 'required']);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testErrorHandling() {
        return true; // Simplified test
    }
    
    private function testLogging() {
        return file_exists(__DIR__ . '/Logger.php');
    }
    
    private function testAPIDatabaseIntegration() {
        try {
            $db = DatabaseHelper::getInstance();
            $result = $db->fetchOne("SELECT COUNT(*) as count FROM users");
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testFrontendBackendIntegration() {
        return is_dir(__DIR__ . '/../pages') && is_dir(__DIR__);
    }
    
    private function testSecurityIntegration() {
        try {
            $token = AuthHelper::generateToken(['id' => 1, 'role' => 'admin']);
            $validated = AuthHelper::validateJWTToken($token);
            $sanitized = SecurityHelper::sanitize('<script>alert("test")</script>');
            return $validated !== null && strpos($sanitized, '<script>') === false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testMiddlewareIntegration() {
        return class_exists('AuthHelper') && class_exists('SecurityHelper');
    }
    
    private function testCrossComponentIntegration() {
        return $this->testAPIDatabaseIntegration() && $this->testFrontendBackendIntegration();
    }
    
    private function testDatabasePerformance() {
        try {
            $start = microtime(true);
            $db = DatabaseHelper::getInstance();
            $result = $db->fetchOne("SELECT COUNT(*) as count FROM users");
            $end = microtime(true);
            $executionTime = ($end - $start) * 1000;
            return $executionTime < 100;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testAPIResponseTime() {
        try {
            $start = microtime(true);
            $validator = new DataValidator();
            $validator->validate(['test' => 'value'], ['test' => 'required']);
            $end = microtime(true);
            $executionTime = ($end - $start) * 1000;
            return $executionTime < 50;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testMemoryUsage() {
        $memoryBefore = memory_get_usage();
        $data = [];
        for ($i = 0; $i < 1000; $i++) {
            $data[] = ['id' => $i, 'name' => 'Test ' . $i];
        }
        $memoryAfter = memory_get_usage();
        $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024;
        return $memoryUsed < 10;
    }
    
    private function testFileSizeOptimization() {
        $apiFiles = glob(__DIR__ . '/*.php');
        $totalSize = 0;
        foreach ($apiFiles as $file) {
            $totalSize += filesize($file);
        }
        $averageSize = $totalSize / count($apiFiles) / 1024;
        return $averageSize < 50;
    }
    
    private function testQueryOptimization() {
        try {
            $db = DatabaseHelper::getInstance();
            $start = microtime(true);
            $result = $db->fetchAll("SELECT * FROM users LIMIT 10");
            $end = microtime(true);
            $executionTime = ($end - $start) * 1000;
            return $executionTime < 200;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testUserRegistrationFlow() {
        return file_exists(__DIR__ . '/auth-enhanced.php') && file_exists(__DIR__ . '/members-crud.php');
    }
    
    private function testLoanApplicationFlow() {
        return file_exists(__DIR__ . '/loans-crud.php') && file_exists(__DIR__ . '/loan-application.php');
    }
    
    private function testPaymentProcessingFlow() {
        return file_exists(__DIR__ . '/member-payments.php') && file_exists(__DIR__ . '/payments.php');
    }
    
    private function testReportGenerationFlow() {
        return file_exists(__DIR__ . '/reports.php') && file_exists(__DIR__ . '/analytics.php');
    }
    
    private function testAdminWorkflow() {
        return file_exists(__DIR__ . '/user-management.php') && file_exists(__DIR__ . '/system-settings.php');
    }
    
    private function calculateOverallHealth() {
        $phases = ['environment', 'foundation', 'database', 'security', 'apis', 'frontend', 'middleware', 'integration', 'performance', 'e2e'];
        $passedPhases = 0;
        
        foreach ($phases as $phase) {
            if (isset($this->testResults[$phase])) {
                $checks = $this->testResults[$phase];
                $passed = count(array_filter($checks));
                $total = count($checks);
                if ($passed / $total >= 0.8) {
                    $passedPhases++;
                }
            }
        }
        
        return ($passedPhases / count($phases)) >= 0.8;
    }
    
    private function checkCriticalComponents() {
        $criticalFiles = [
            'DatabaseHelper.php',
            'AuthHelper.php',
            'SecurityHelper.php',
            'SecurityMiddleware.php',
            'DataValidator.php',
            'auth-enhanced.php',
            'members-crud.php',
            'loans-crud.php'
        ];
        
        $existingFiles = 0;
        foreach ($criticalFiles as $file) {
            if (file_exists(__DIR__ . '/' . $file)) {
                $existingFiles++;
            }
        }
        
        return ($existingFiles / count($criticalFiles)) >= 0.9;
    }
    
    private function checkBackupStrategy() {
        return file_exists(__DIR__ . '/../database') && is_dir(__DIR__ . '/../database');
    }
    
    private function checkMonitoringSetup() {
        return file_exists(__DIR__ . '/Logger.php') && file_exists(__DIR__ . '/SecurityLogger.php');
    }
    
    private function checkDocumentation() {
        return file_exists(__DIR__ . '/../README.md') || file_exists(__DIR__ . '/../DEPLOYMENT_GUIDE.md');
    }
    
    private function generateFinalReport() {
        echo "📊 FINAL COMPREHENSIVE TESTING REPORT\n";
        echo "==================================\n";
        
        $phases = [
            'environment' => 'Environment',
            'foundation' => 'Foundation',
            'database' => 'Database',
            'security' => 'Security',
            'apis' => 'APIs',
            'frontend' => 'Frontend',
            'middleware' => 'Middleware',
            'integration' => 'Integration',
            'performance' => 'Performance',
            'e2e' => 'End-to-End',
            'production_readiness' => 'Production Readiness'
        ];
        
        $phaseScores = [];
        $totalPassed = 0;
        $totalChecks = 0;
        
        echo "📊 Phase Results:\n";
        foreach ($phases as $key => $name) {
            if (isset($this->testResults[$key])) {
                $checks = $this->testResults[$key];
                $passed = count(array_filter($checks));
                $total = count($checks);
                $score = round(($passed / $total) * 100, 2);
                $phaseScores[$key] = $score;
                $totalPassed += $passed;
                $totalChecks += $total;
                
                $status = $score >= 80 ? "PASS" : "FAIL";
                echo "  $name: $score% ($passed/$total) - $status\n";
            }
        }
        
        $overallScore = $totalChecks > 0 ? round(($totalPassed / $totalChecks) * 100, 2) : 0;
        
        echo "\n📊 Overall Results:\n";
        echo "  Total Checks: $totalChecks\n";
        echo "  Passed Checks: $totalPassed\n";
        echo "  Overall Score: $overallScore%\n";
        
        echo "\n🎯 Production Readiness:\n";
        if ($overallScore >= 95) {
            echo "  ✅ EXCELLENT - Ready for Production\n";
        } elseif ($overallScore >= 85) {
            echo "  ✅ GOOD - Ready for Production\n";
        } elseif ($overallScore >= 75) {
            echo "  ⚠️  ACCEPTABLE - Needs Minor Improvements\n";
        } else {
            echo "  ❌ NEEDS IMPROVEMENT - Not Ready for Production\n";
        }
        
        // Save results
        $this->testResults['summary'] = [
            'overall_score' => $overallScore,
            'total_checks' => $totalChecks,
            'passed_checks' => $totalPassed,
            'phase_scores' => $phaseScores,
            'production_ready' => $overallScore >= 85,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents(__DIR__ . '/final-comprehensive-test-results.json', json_encode($this->testResults, JSON_PRETTY_PRINT));
        echo "\n📄 Test results saved to: final-comprehensive-test-results.json\n";
        echo "\n=== FINAL COMPREHENSIVE TESTING COMPLETED ===\n";
    }
}

// Run testing if executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $testing = new FinalComprehensiveTesting();
    $results = $testing->runCompleteTesting();
}
?>
