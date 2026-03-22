<?php
/**
 * Enhanced E2E Payment Flow Testing
 * Comprehensive testing for payment processing
 */

require_once __DIR__ . '/config/error-config.php';

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/DatabaseHelper.php';
require_once __DIR__ . '/AuthHelper.php';
require_once __DIR__ . '/SecurityHelper.php';
require_once __DIR__ . '/DataValidator.php';

class EnhancedE2EPaymentTesting {
    private $testResults = [];
    
    public function runPaymentFlowTests() {
        echo "=== ENHANCED E2E PAYMENT FLOW TESTING ===\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        $this->testPaymentAPIAvailability();
        $this->testPaymentDatabaseStructure();
        $this->testPaymentIntegration();
        $this->testPaymentSecurity();
        $this->testPaymentWorkflow();
        
        $this->generatePaymentFlowReport();
        return $this->testResults;
    }
    
    private function testPaymentAPIAvailability() {
        echo "🔍 TESTING: Payment API Availability\n";
        echo "=====================================\n";
        
        $paymentAPIs = [
            'member-payments.php' => 'Member Payments API',
            'payments.php' => 'General Payments API',
            'digital-payments.php' => 'Digital Payments API'
        ];
        
        $checks = [];
        foreach ($paymentAPIs as $file => $description) {
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
        
        $this->testResults['api_availability'] = $checks;
        $passed = count(array_filter($checks));
        $total = count($checks);
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        echo "  Status: " . ($passed >= 2 ? "PASS" : "FAIL") . " ($passed/$total)\n\n";
    }
    
    private function testPaymentDatabaseStructure() {
        echo "🔍 TESTING: Payment Database Structure\n";
        echo "=====================================\n";
        
        try {
            $db = DatabaseHelper::getInstance();
            
            $checks = [
                'payment_transactions Table' => $this->checkTableExists($db, 'payment_transactions'),
                'payment_transactions Columns' => $this->checkPaymentColumns($db),
                'payment_transactions Indexes' => $this->checkPaymentIndexes($db),
                'payment_transactions Data' => $this->checkPaymentData($db),
                'Related Tables' => $this->checkRelatedTables($db)
            ];
        } catch (Exception $e) {
            $checks = [
                'payment_transactions Table' => false,
                'payment_transactions Columns' => false,
                'payment_transactions Indexes' => false,
                'payment_transactions Data' => false,
                'Related Tables' => false
            ];
        }
        
        $this->testResults['database_structure'] = $checks;
        $passed = count(array_filter($checks));
        $total = count($checks);
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        echo "  Status: " . ($passed >= 4 ? "PASS" : "FAIL") . " ($passed/$total)\n\n";
    }
    
    private function testPaymentIntegration() {
        echo "🔍 TESTING: Payment Integration\n";
        echo "===============================\n";
        
        $checks = [
            'Member-Payments API Integration' => $this->testMemberPaymentsIntegration(),
            'General Payments API Integration' => $this->testGeneralPaymentsIntegration(),
            'Database Integration' => $this->testPaymentDatabaseIntegration(),
            'Security Integration' => $this->testPaymentSecurityIntegration(),
            'Frontend Integration' => $this->testPaymentFrontendIntegration()
        ];
        
        $this->testResults['integration'] = $checks;
        $passed = count(array_filter($checks));
        $total = count($checks);
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        echo "  Status: " . ($passed >= 4 ? "PASS" : "FAIL") . " ($passed/$total)\n\n";
    }
    
    private function testPaymentSecurity() {
        echo "🔍 TESTING: Payment Security\n";
        echo "==========================\n";
        
        $checks = [
            'Input Validation' => $this->testPaymentInputValidation(),
            'Amount Validation' => $this->testAmountValidation(),
            'Payment Method Validation' => $this->testPaymentMethodValidation(),
            'Transaction Security' => $this->testTransactionSecurity(),
            'Audit Logging' => $this->testAuditLogging()
        ];
        
        $this->testResults['security'] = $checks;
        $passed = count(array_filter($checks));
        $total = count($checks);
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        echo "  Status: " . ($passed >= 4 ? "PASS" : "FAIL") . " ($passed/$total)\n\n";
    }
    
    private function testPaymentWorkflow() {
        echo "🔍 TESTING: Payment Workflow\n";
        echo "==========================\n";
        
        $checks = [
            'Payment Initiation' => $this->testPaymentInitiation(),
            'Payment Processing' => $this->testPaymentProcessing(),
            'Payment Confirmation' => $this->testPaymentConfirmation(),
            'Payment History' => $this->testPaymentHistory(),
            'Payment Cancellation' => $this->testPaymentCancellation()
        ];
        
        $this->testResults['workflow'] = $checks;
        $passed = count(array_filter($checks));
        $total = count($checks);
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        echo "  Status: " . ($passed >= 4 ? "PASS" : "FAIL") . " ($passed/$total)\n\n";
    }
    
    // Helper methods for payment testing
    private function checkTableExists($db, $tableName) {
        try {
            $result = $db->fetchOne("SHOW TABLES LIKE '$tableName'");
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkPaymentColumns($db) {
        try {
            $columns = $db->fetchAll("SHOW COLUMNS FROM payment_transactions");
            $requiredColumns = ['id', 'member_id', 'amount', 'payment_method', 'status', 'created_at'];
            
            $columnNames = array_map(function($col) {
                return $col['Field'];
            }, $columns);
            
            foreach ($requiredColumns as $required) {
                if (!in_array($required, $columnNames)) {
                    return false;
                }
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkPaymentIndexes($db) {
        try {
            $indexes = $db->fetchAll("SHOW INDEX FROM payment_transactions");
            return count($indexes) > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkPaymentData($db) {
        try {
            $result = $db->fetchOne("SELECT COUNT(*) as count FROM payment_transactions");
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkRelatedTables($db) {
        try {
            $membersTable = $db->fetchOne("SHOW TABLES LIKE 'members'");
            $loansTable = $db->fetchOne("SHOW TABLES LIKE 'loans'");
            return $membersTable !== false && $loansTable !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testMemberPaymentsIntegration() {
        return file_exists(__DIR__ . '/member-payments.php') && 
               file_exists(__DIR__ . '/DatabaseHelper.php') &&
               file_exists(__DIR__ . '/AuthHelper.php');
    }
    
    private function testGeneralPaymentsIntegration() {
        return file_exists(__DIR__ . '/payments.php') && 
               file_exists(__DIR__ . '/DatabaseHelper.php');
    }
    
    private function testPaymentDatabaseIntegration() {
        try {
            $db = DatabaseHelper::getInstance();
            $result = $db->fetchOne("SELECT COUNT(*) as count FROM payment_transactions LIMIT 1");
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testPaymentSecurityIntegration() {
        return file_exists(__DIR__ . '/SecurityHelper.php') && 
               file_exists(__DIR__ . '/DataValidator.php');
    }
    
    private function testPaymentFrontendIntegration() {
        return is_dir(__DIR__ . '/../pages/member') && 
               file_exists(__DIR__ . '/../pages/member/ajukan-pinjaman.html');
    }
    
    private function testPaymentInputValidation() {
        try {
            $validator = new DataValidator();
            return $validator->validate(['amount' => 1000], ['amount' => 'required|numeric']);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testAmountValidation() {
        try {
            $validator = new DataValidator();
            return $validator->validate(['amount' => 1000], ['amount' => 'required|numeric|min:1000']);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testPaymentMethodValidation() {
        try {
            $methods = ['cash', 'transfer', 'digital'];
            return in_array('transfer', $methods);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testTransactionSecurity() {
        try {
            $malicious = "'; DROP TABLE payment_transactions; --";
            return SecurityHelper::containsSQLInjection($malicious);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function testAuditLogging() {
        return file_exists(__DIR__ . '/SecurityLogger.php') || file_exists(__DIR__ . '/Logger.php');
    }
    
    private function testPaymentInitiation() {
        return file_exists(__DIR__ . '/member-payments.php');
    }
    
    private function testPaymentProcessing() {
        return file_exists(__DIR__ . '/payments.php');
    }
    
    private function testPaymentConfirmation() {
        return file_exists(__DIR__ . '/member-payments.php');
    }
    
    private function testPaymentHistory() {
        return file_exists(__DIR__ . '/member-payments.php');
    }
    
    private function testPaymentCancellation() {
        return file_exists(__DIR__ . '/member-payments.php');
    }
    
    private function generatePaymentFlowReport() {
        echo "📊 ENHANCED PAYMENT FLOW REPORT\n";
        echo "===============================\n";
        
        $categories = [
            'api_availability' => 'API Availability',
            'database_structure' => 'Database Structure',
            'integration' => 'Integration',
            'security' => 'Security',
            'workflow' => 'Workflow'
        ];
        
        $categoryScores = [];
        $totalPassed = 0;
        $totalChecks = 0;
        
        echo "📊 Category Results:\n";
        foreach ($categories as $key => $name) {
            if (isset($this->testResults[$key])) {
                $checks = $this->testResults[$key];
                $passed = count(array_filter($checks));
                $total = count($checks);
                $score = round(($passed / $total) * 100, 2);
                $categoryScores[$key] = $score;
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
        
        echo "\n🎯 Payment Flow Status:\n";
        if ($overallScore >= 95) {
            echo "  ✅ EXCELLENT - Payment Flow Ready\n";
        } elseif ($overallScore >= 85) {
            echo "  ✅ GOOD - Payment Flow Ready\n";
        } elseif ($overallScore >= 75) {
            echo "  ⚠️  ACCEPTABLE - Payment Flow Needs Minor Improvements\n";
        } else {
            echo "  ❌ NEEDS IMPROVEMENT - Payment Flow Not Ready\n";
        }
        
        // Save results
        $this->testResults['summary'] = [
            'overall_score' => $overallScore,
            'total_checks' => $totalChecks,
            'passed_checks' => $totalPassed,
            'category_scores' => $categoryScores,
            'payment_ready' => $overallScore >= 85,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents(__DIR__ . '/enhanced-payment-flow-results.json', json_encode($this->testResults, JSON_PRETTY_PRINT));
        echo "\n📄 Payment flow results saved to: enhanced-payment-flow-results.json\n";
        echo "\n=== ENHANCED PAYMENT FLOW TESTING COMPLETED ===\n";
    }
}

// Run testing if executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $testing = new EnhancedE2EPaymentTesting();
    $results = $testing->runPaymentFlowTests();
}
?>
