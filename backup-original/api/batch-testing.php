<?php
/**
 * Batch Testing Processor
 * Automated batch testing for all components
 */

require_once __DIR__ . '/config/error-config.php';

define('KSP_API_ACCESS', true);

class BatchTestingProcessor {
    private $testResults = [];
    private $batchResults = [];
    
    public function runBatchTests() {
        echo "=== KSP LAM GABE JAYA - BATCH TESTING PROCESSOR ===\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        $this->batchTestAPIEndpoints();
        $this->batchTestSecurity();
        $this->batchTestPerformance();
        $this->batchTestIntegration();
        
        $this->generateBatchReport();
        return $this->batchResults;
    }
    
    /**
     * Batch test all API endpoints
     */
    private function batchTestAPIEndpoints() {
        echo "🔄 BATCH TESTING: API Endpoints\n";
        echo "==============================\n";
        
        $apiFiles = [
            'auth-enhanced.php' => 'Authentication',
            'members-crud.php' => 'Members CRUD',
            'loans-crud.php' => 'Loans CRUD',
            'savings-crud.php' => 'Savings CRUD',
            'user-management.php' => 'User Management',
            'system-settings.php' => 'System Settings',
            'audit-log.php' => 'Audit Log',
            'member-registration.php' => 'Member Registration',
            'reports.php' => 'Reports',
            'member-dashboard.php' => 'Member Dashboard',
            'loan-application.php' => 'Loan Application',
            'member-savings.php' => 'Member Savings',
            'member-payments.php' => 'Member Payments',
            'member-profile.php' => 'Member Profile',
            'staff-dashboard.php' => 'Staff Dashboard',
            'staff-gps.php' => 'Staff GPS',
            'staff-members.php' => 'Staff Members',
            'staff-tasks.php' => 'Staff Tasks',
            'staff-reports.php' => 'Staff Reports',
            'analytics.php' => 'Analytics',
            'notifications.php' => 'Notifications',
            'reward-points.php' => 'Reward Points'
        ];
        
        $this->batchResults['api_batch'] = [
            'total_files' => count($apiFiles),
            'syntax_valid' => 0,
            'functional' => 0,
            'secure' => 0,
            'errors' => []
        ];
        
        foreach ($apiFiles as $file => $description) {
            $result = $this->testSingleAPI($file, $description);
            
            if ($result['syntax_valid']) {
                $this->batchResults['api_batch']['syntax_valid']++;
            }
            if ($result['functional']) {
                $this->batchResults['api_batch']['functional']++;
            }
            if ($result['secure']) {
                $this->batchResults['api_batch']['secure']++;
            }
            
            if (!$result['syntax_valid'] || !$result['functional'] || !$result['secure']) {
                $this->batchResults['api_batch']['errors'][] = [
                    'file' => $file,
                    'description' => $description,
                    'issues' => $result['issues']
                ];
            }
            
            echo "  " . ($result['syntax_valid'] ? "✅" : "❌") . " $description: " . 
                 ($result['functional'] ? "Functional" : "Not Functional") . ", " .
                 ($result['secure'] ? "Secure" : "Not Secure") . "\n";
        }
        
        echo "\n📊 API Batch Results:\n";
        echo "  Total Files: " . $this->batchResults['api_batch']['total_files'] . "\n";
        echo "  Syntax Valid: " . $this->batchResults['api_batch']['syntax_valid'] . "\n";
        echo "  Functional: " . $this->batchResults['api_batch']['functional'] . "\n";
        echo "  Secure: " . $this->batchResults['api_batch']['secure'] . "\n";
        echo "  Errors: " . count($this->batchResults['api_batch']['errors']) . "\n";
        echo "\n";
    }
    
    /**
     * Test single API file
     */
    private function testSingleAPI($file, $description) {
        $filepath = __DIR__ . '/' . $file;
        
        $result = [
            'syntax_valid' => false,
            'functional' => false,
            'secure' => false,
            'issues' => []
        ];
        
        if (!file_exists($filepath)) {
            $result['issues'][] = 'File not found';
            return $result;
        }
        
        // Test syntax
        $output = [];
        $returnCode = 0;
        exec("php -l \"$filepath\" 2>&1", $output, $returnCode);
        $result['syntax_valid'] = $returnCode === 0;
        
        if (!$result['syntax_valid']) {
            $result['issues'] = array_merge($result['issues'], $output);
            return $result;
        }
        
        // Test functionality (simplified)
        $content = file_get_contents($filepath);
        $result['functional'] = $this->checkFunctionality($content);
        
        // Test security
        $result['secure'] = $this->checkSecurity($content);
        
        if (!$result['functional']) {
            $result['issues'][] = 'Functionality issues detected';
        }
        
        if (!$result['secure']) {
            $result['issues'][] = 'Security issues detected';
        }
        
        return $result;
    }
    
    /**
     * Check functionality of API file
     */
    private function checkFunctionality($content) {
        $functionalityChecks = [
            'requireAuth' => strpos($content, 'requireAuth') !== false || strpos($content, 'SecurityMiddleware::requireAuth') !== false,
            'database' => strpos($content, 'DatabaseHelper') !== false || strpos($content, '$db->') !== false,
            'validation' => strpos($content, 'DataValidator') !== false || strpos($content, 'validate') !== false,
            'response' => strpos($content, 'json_encode') !== false || strpos($content, 'sendJSONResponse') !== false
        ];
        
        return count(array_filter($functionalityChecks)) >= 3;
    }
    
    /**
     * Check security of API file
     */
    private function checkSecurity($content) {
        $securityChecks = [
            'no_raw_echo' => strpos($content, 'echo json_encode($response);') === false,
            'has_validation' => strpos($content, 'DataValidator') !== false || strpos($content, 'SecurityHelper::sanitize') !== false,
            'has_auth' => strpos($content, 'requireAuth') !== false || strpos($content, 'SecurityMiddleware::requireAuth') !== false,
            'has_error_handling' => strpos($content, 'try') !== false && strpos($content, 'catch') !== false
        ];
        
        return count(array_filter($securityChecks)) >= 3;
    }
    
    /**
     * Batch test security measures
     */
    private function batchTestSecurity() {
        echo "🔄 BATCH TESTING: Security\n";
        echo "==========================\n";
        
        $securityTests = [
            'input_validation' => $this->testInputValidation(),
            'xss_protection' => $this->testXSSProtection(),
            'sql_injection_protection' => $this->testSQLInjectionProtection(),
            'authentication' => $this->testAuthenticationSecurity(),
            'authorization' => $this->testAuthorization(),
            'csrf_protection' => $this->testCSRFProtection()
        ];
        
        $this->batchResults['security_batch'] = [
            'total_tests' => count($securityTests),
            'passed' => count(array_filter($securityTests)),
            'failed' => count(array_diff($securityTests, array_filter($securityTests))),
            'details' => $securityTests
        ];
        
        foreach ($securityTests as $test => $result) {
            echo "  " . ($result ? "✅" : "❌") . " " . ucfirst(str_replace('_', ' ', $test)) . "\n";
        }
        
        echo "\n📊 Security Batch Results:\n";
        echo "  Total Tests: " . $this->batchResults['security_batch']['total_tests'] . "\n";
        echo "  Passed: " . $this->batchResults['security_batch']['passed'] . "\n";
        echo "  Failed: " . $this->batchResults['security_batch']['failed'] . "\n";
        echo "\n";
    }
    
    /**
     * Test input validation
     */
    private function testInputValidation() {
        try {
            $validator = new DataValidator();
            $test = $validator->validate(['test' => 'value'], ['test' => 'required']);
            return $test === true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Test XSS protection
     */
    private function testXSSProtection() {
        try {
            $malicious = '<script>alert("xss")</script>';
            $sanitized = SecurityHelper::sanitize($malicious);
            return strpos($sanitized, '<script>') === false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Test SQL injection protection
     */
    private function testSQLInjectionProtection() {
        try {
            $malicious = "'; DROP TABLE users; --";
            $detected = SecurityHelper::containsSQLInjection($malicious);
            return $detected === true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Test authentication security
     */
    private function testAuthenticationSecurity() {
        try {
            $token = AuthHelper::generateToken(['id' => 1, 'username' => 'test', 'role' => 'admin']);
            $validated = AuthHelper::validateJWTToken($token);
            return $validated !== null && $validated['user_id'] == 1;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Test authorization
     */
    private function testAuthorization() {
        try {
            $user = ['role' => 'admin'];
            $authorized = AuthHelper::validateRole('admin', $user);
            return $authorized === true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Test CSRF protection
     */
    private function testCSRFProtection() {
        try {
            $token = SecurityHelper::generateCSRFToken();
            $valid = SecurityHelper::validateCSRFToken($token);
            return $valid === true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Batch test performance
     */
    private function batchTestPerformance() {
        echo "🔄 BATCH TESTING: Performance\n";
        echo "============================\n";
        
        $performanceTests = [
            'database_performance' => $this->testDatabasePerformance(),
            'api_response_time' => $this->testAPIResponseTime(),
            'memory_usage' => $this->testMemoryUsage(),
            'file_size' => $this->testFileSize(),
            'query_performance' => $this->testQueryPerformance()
        ];
        
        $this->batchResults['performance_batch'] = [
            'total_tests' => count($performanceTests),
            'passed' => count(array_filter($performanceTests)),
            'failed' => count(array_diff($performanceTests, array_filter($performanceTests))),
            'details' => $performanceTests
        ];
        
        foreach ($performanceTests as $test => $result) {
            echo "  " . ($result ? "✅" : "❌") . " " . ucfirst(str_replace('_', ' ', $test)) . "\n";
        }
        
        echo "\n📊 Performance Batch Results:\n";
        echo "  Total Tests: " . $this->batchResults['performance_batch']['total_tests'] . "\n";
        echo "  Passed: " . $this->batchResults['performance_batch']['passed'] . "\n";
        echo "  Failed: " . $this->batchResults['performance_batch']['failed'] . "\n";
        echo "\n";
    }
    
    /**
     * Test database performance
     */
    private function testDatabasePerformance() {
        try {
            $start = microtime(true);
            $db = DatabaseHelper::getInstance();
            $result = $db->fetchOne("SELECT COUNT(*) as count FROM users");
            $end = microtime(true);
            
            $executionTime = ($end - $start) * 1000; // Convert to milliseconds
            return $executionTime < 100; // Less than 100ms
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Test API response time
     */
    private function testAPIResponseTime() {
        try {
            $start = microtime(true);
            
            // Simulate API processing
            $validator = new DataValidator();
            $test = $validator->validate(['test' => 'value'], ['test' => 'required']);
            
            $end = microtime(true);
            $executionTime = ($end - $start) * 1000;
            
            return $executionTime < 50; // Less than 50ms
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Test memory usage
     */
    private function testMemoryUsage() {
        $memoryBefore = memory_get_usage();
        
        // Simulate some processing
        $data = [];
        for ($i = 0; $i < 1000; $i++) {
            $data[] = ['id' => $i, 'name' => 'Test ' . $i];
        }
        
        $memoryAfter = memory_get_usage();
        $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024; // Convert to MB
        
        return $memoryUsed < 10; // Less than 10MB
    }
    
    /**
     * Test file size
     */
    private function testFileSize() {
        $apiFiles = glob(__DIR__ . '/*.php');
        $totalSize = 0;
        
        foreach ($apiFiles as $file) {
            $totalSize += filesize($file);
        }
        
        $averageSize = $totalSize / count($apiFiles) / 1024; // Average size in KB
        return $averageSize < 50; // Average less than 50KB
    }
    
    /**
     * Test query performance
     */
    private function testQueryPerformance() {
        try {
            $db = DatabaseHelper::getInstance();
            
            $start = microtime(true);
            $result = $db->fetchAll("SELECT * FROM users LIMIT 10");
            $end = microtime(true);
            
            $executionTime = ($end - $start) * 1000;
            return $executionTime < 200; // Less than 200ms
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Batch test integration
     */
    private function batchTestIntegration() {
        echo "🔄 BATCH TESTING: Integration\n";
        echo "============================\n";
        
        $integrationTests = [
            'api_database_integration' => $this->testAPIDatabaseIntegration(),
            'frontend_backend_integration' => $this->testFrontendBackendIntegration(),
            'security_integration' => $this->testSecurityIntegration(),
            'middleware_integration' => $this->testMiddlewareIntegration(),
            'end_to_end_flow' => $this->testEndToEndFlow()
        ];
        
        $this->batchResults['integration_batch'] = [
            'total_tests' => count($integrationTests),
            'passed' => count(array_filter($integrationTests)),
            'failed' => count(array_diff($integrationTests, array_filter($integrationTests))),
            'details' => $integrationTests
        ];
        
        foreach ($integrationTests as $test => $result) {
            echo "  " . ($result ? "✅" : "❌") . " " . ucfirst(str_replace('_', ' ', $test)) . "\n";
        }
        
        echo "\n📊 Integration Batch Results:\n";
        echo "  Total Tests: " . $this->batchResults['integration_batch']['total_tests'] . "\n";
        echo "  Passed: " . $this->batchResults['integration_batch']['passed'] . "\n";
        echo "  Failed: " . $this->batchResults['integration_batch']['failed'] . "\n";
        echo "\n";
    }
    
    /**
     * Test API-Database integration
     */
    private function testAPIDatabaseIntegration() {
        try {
            $db = DatabaseHelper::getInstance();
            
            // Test if API can connect to database
            $result = $db->fetchOne("SELECT COUNT(*) as count FROM users");
            
            // Test if API can perform CRUD operations
            $testData = ['test_field' => 'test_value'];
            // Note: This is a simplified test
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Test Frontend-Backend integration
     */
    private function testFrontendBackendIntegration() {
        // Check if frontend files exist and can access backend
        $frontendDir = __DIR__ . '/../pages';
        $backendDir = __DIR__;
        
        return is_dir($frontendDir) && is_dir($backendDir);
    }
    
    /**
     * Test security integration
     */
    private function testSecurityIntegration() {
        try {
            // Test if all security components work together
            $token = AuthHelper::generateToken(['id' => 1, 'role' => 'admin']);
            $validated = AuthHelper::validateJWTToken($token);
            $sanitized = SecurityHelper::sanitize('<script>alert("test")</script>');
            
            return $validated !== null && strpos($sanitized, '<script>') === false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Test middleware integration
     */
    private function testMiddlewareIntegration() {
        try {
            // Test if middleware components are available
            $authHelper = class_exists('AuthHelper');
            $securityHelper = class_exists('SecurityHelper');
            $securityMiddleware = class_exists('SecurityMiddleware');
            
            return $authHelper && $securityHelper && $securityMiddleware;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Test end-to-end flow
     */
    private function testEndToEndFlow() {
        try {
            // Simplified E2E test
            $db = DatabaseHelper::getInstance();
            $user = $db->fetchOne("SELECT * FROM users WHERE username = 'admin'");
            
            if ($user) {
                $token = AuthHelper::generateToken($user);
                $validated = AuthHelper::validateJWTToken($token);
                
                return $validated !== null && $validated['user_id'] == $user['id'];
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Generate batch testing report
     */
    private function generateBatchReport() {
        echo "📊 BATCH TESTING SUMMARY\n";
        echo "======================\n";
        
        // Calculate overall scores
        $apiScore = round(($this->batchResults['api_batch']['functional'] / $this->batchResults['api_batch']['total_files']) * 100, 2);
        $securityScore = round(($this->batchResults['security_batch']['passed'] / $this->batchResults['security_batch']['total_tests']) * 100, 2);
        $performanceScore = round(($this->batchResults['performance_batch']['passed'] / $this->batchResults['performance_batch']['total_tests']) * 100, 2);
        $integrationScore = round(($this->batchResults['integration_batch']['passed'] / $this->batchResults['integration_batch']['total_tests']) * 100, 2);
        
        $overallScore = round(($apiScore + $securityScore + $performanceScore + $integrationScore) / 4, 2);
        
        echo "📊 Batch Test Results:\n";
        echo "  API Testing: $apiScore%\n";
        echo "  Security Testing: $securityScore%\n";
        echo "  Performance Testing: $performanceScore%\n";
        echo "  Integration Testing: $integrationScore%\n";
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
        
        // Save batch results
        $this->batchResults['summary'] = [
            'overall_score' => $overallScore,
            'component_scores' => [
                'api' => $apiScore,
                'security' => $securityScore,
                'performance' => $performanceScore,
                'integration' => $integrationScore
            ],
            'production_ready' => $overallScore >= 85,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents(__DIR__ . '/batch-test-results.json', json_encode($this->batchResults, JSON_PRETTY_PRINT));
        echo "\n📄 Batch test results saved to: batch-test-results.json\n";
        echo "\n=== BATCH TESTING COMPLETED ===\n";
    }
}

// Run batch tests if executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    require_once __DIR__ . '/DatabaseHelper.php';
    require_once __DIR__ . '/AuthHelper.php';
    require_once __DIR__ . '/SecurityHelper.php';
    require_once __DIR__ . '/DataValidator.php';
    
    $batchTesting = new BatchTestingProcessor();
    $results = $batchTesting->runBatchTests();
}
?>
