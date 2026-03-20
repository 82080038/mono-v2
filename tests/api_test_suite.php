<?php
/**
 * Automated API Test Suite
 * KSP Lam Gabe Jaya v2.0
 */

class APITestSuite {
    
    private $baseUrl = 'http://localhost/mono-v2/api';
    private $testResults = [];
    private $totalTests = 0;
    private $passedTests = 0;
    private $failedTests = 0;
    
    public function runAllTests() {
        echo "=== API Test Suite Started ===\n\n";
        
        // Authentication Tests
        $this->testAuthentication();
        
        // Member Management Tests
        $this->testMemberManagement();
        
        // Loan Management Tests
        $this->testLoanManagement();
        
        // Security Tests
        $this->testSecurity();
        
        // Input Validation Tests
        $this->testInputValidation();
        
        $this->printSummary();
    }
    
    /**
     * Test Authentication Endpoints
     */
    private function testAuthentication() {
        echo "--- Authentication Tests ---\n";
        
        // Test valid login
        $this->makeRequest('POST', '/auth.php', [
            'action' => 'login',
            'username' => 'admin',
            'password' => 'password'
        ], 'Valid Login Test');
        
        // Test invalid login
        $this->makeRequest('POST', '/auth.php', [
            'action' => 'login',
            'username' => 'invalid',
            'password' => 'wrong'
        ], 'Invalid Login Test', 401);
        
        // Test missing parameters
        $this->makeRequest('POST', '/auth.php', [
            'action' => 'login',
            'username' => 'admin'
        ], 'Missing Password Test', 400);
        
        // Test invalid action
        $this->makeRequest('POST', '/auth.php', [
            'action' => 'invalid_action',
            'username' => 'admin',
            'password' => 'password'
        ], 'Invalid Action Test', 400);
        
        echo "\n";
    }
    
    /**
     * Test Member Management
     */
    private function testMemberManagement() {
        echo "--- Member Management Tests ---\n";
        
        // Test get members
        $this->makeRequest('GET', '/members.php', [
            'action' => 'get_members',
            'page' => 1,
            'limit' => 10
        ], 'Get Members Test');
        
        // Test get member types
        $this->makeRequest('GET', '/members.php', [
            'action' => 'get_member_types'
        ], 'Get Member Types Test');
        
        // Test search functionality
        $this->makeRequest('GET', '/members.php', [
            'action' => 'get_members',
            'search' => 'test'
        ], 'Search Members Test');
        
        echo "\n";
    }
    
    /**
     * Test Loan Management
     */
    private function testLoanManagement() {
        echo "--- Loan Management Tests ---\n";
        
        // Test get loans
        $this->makeRequest('GET', '/loans.php', [
            'action' => 'get_loans',
            'page' => 1,
            'limit' => 10
        ], 'Get Loans Test');
        
        // Test get loan types
        $this->makeRequest('GET', '/loans.php', [
            'action' => 'get_loan_types'
        ], 'Get Loan Types Test');
        
        // Test invalid action
        $this->makeRequest('GET', '/loans.php', [
            'action' => 'invalid_action'
        ], 'Invalid Loan Action Test', 400);
        
        echo "\n";
    }
    
    /**
     * Test Security Features
     */
    private function testSecurity() {
        echo "--- Security Tests ---\n";
        
        // Test SQL injection attempts
        $sqlInjectionAttempts = [
            "'; DROP TABLE members; --",
            "1' OR '1'='1",
            "admin'; UPDATE members SET password='hacked'; --"
        ];
        
        foreach ($sqlInjectionAttempts as $attempt) {
            $this->makeRequest('POST', '/auth.php', [
                'action' => 'login',
                'username' => $attempt,
                'password' => 'password'
            ], "SQL Injection Test: " . substr($attempt, 0, 20) . "...", 400);
        }
        
        // Test XSS attempts
        $xssAttempts = [
            "<script>alert('xss')</script>",
            "javascript:alert('xss')",
            "<img src=x onerror=alert('xss')>"
        ];
        
        foreach ($xssAttempts as $attempt) {
            $this->makeRequest('POST', '/auth.php', [
                'action' => 'login',
                'username' => $attempt,
                'password' => 'password'
            ], "XSS Test: " . substr($attempt, 0, 20) . "...", 400);
        }
        
        echo "\n";
    }
    
    /**
     * Test Input Validation
     */
    private function testInputValidation() {
        echo "--- Input Validation Tests ---\n";
        
        // Test invalid page numbers
        $this->makeRequest('GET', '/members.php', [
            'action' => 'get_members',
            'page' => -1
        ], 'Invalid Page Number Test', 400);
        
        // Test excessive limit
        $this->makeRequest('GET', '/members.php', [
            'action' => 'get_members',
            'limit' => 1000
        ], 'Excessive Limit Test', 400);
        
        // Test invalid characters in action
        $this->makeRequest('GET', '/members.php', [
            'action' => 'get_members<script>',
            'page' => 1
        ], 'Invalid Characters Test', 400);
        
        echo "\n";
    }
    
    /**
     * Make HTTP request and check response
     */
    private function makeRequest($method, $endpoint, $data = [], $testName = 'Test', $expectedStatus = 200) {
        $this->totalTests++;
        
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            $this->testResults[] = [
                'test' => $testName,
                'status' => 'FAILED',
                'reason' => "Curl Error: $error",
                'http_code' => $httpCode
            ];
            $this->failedTests++;
            echo "❌ $testName - FAILED: $error\n";
            return;
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode === $expectedStatus) {
            $this->testResults[] = [
                'test' => $testName,
                'status' => 'PASSED',
                'http_code' => $httpCode,
                'response_size' => strlen($response)
            ];
            $this->passedTests++;
            echo "✅ $testName - PASSED (HTTP $httpCode)\n";
        } else {
            $this->testResults[] = [
                'test' => $testName,
                'status' => 'FAILED',
                'reason' => "Expected HTTP $expectedStatus, got $httpCode",
                'http_code' => $httpCode,
                'response' => substr($response, 0, 200)
            ];
            $this->failedTests++;
            echo "❌ $testName - FAILED: Expected HTTP $expectedStatus, got $httpCode\n";
        }
    }
    
    /**
     * Print test summary
     */
    private function printSummary() {
        echo "=== Test Summary ===\n";
        echo "Total Tests: $this->totalTests\n";
        echo "Passed: $this->passedTests\n";
        echo "Failed: $this->failedTests\n";
        echo "Success Rate: " . round(($this->passedTests / $this->totalTests) * 100, 2) . "%\n\n";
        
        if ($this->failedTests > 0) {
            echo "Failed Tests:\n";
            foreach ($this->testResults as $result) {
                if ($result['status'] === 'FAILED') {
                    echo "- {$result['test']}: {$result['reason']}\n";
                }
            }
        }
        
        // Save results to file
        $this->saveTestResults();
    }
    
    /**
     * Save test results to file
     */
    private function saveTestResults() {
        $results = [
            'timestamp' => date('Y-m-d H:i:s'),
            'total_tests' => $this->totalTests,
            'passed' => $this->passedTests,
            'failed' => $this->failedTests,
            'success_rate' => round(($this->passedTests / $this->totalTests) * 100, 2),
            'results' => $this->testResults
        ];
        
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logDir . '/api_test_results.json', json_encode($results, JSON_PRETTY_PRINT));
        echo "\nTest results saved to: logs/api_test_results.json\n";
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $testSuite = new APITestSuite();
    $testSuite->runAllTests();
}
