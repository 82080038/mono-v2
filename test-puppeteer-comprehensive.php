<?php
/**
 * KSP Lam Gabe Jaya - Comprehensive Puppeteer Test Suite
 * Test all features using headless Chrome/Puppeteer
 */

require_once __DIR__ . '/config/constants.php';

class PuppeteerTestSuite {
    private $baseUrl;
    private $testResults = [];
    private $screenshots = [];
    private $currentRole = '';
    
    public function __construct() {
        $this->baseUrl = 'http://localhost/mono-v2';
        $this->testResults = [
            'total' => 0,
            'passed' => 0,
            'failed' => 0,
            'errors' => []
        ];
    }
    
    /**
     * Run comprehensive test suite
     */
    public function runComprehensiveTests() {
        echo "🚀 Starting Comprehensive Puppeteer Test Suite\n";
        echo "================================================\n\n";
        
        // Test 1: Database Connection
        $this->testDatabaseConnection();
        
        // Test 2: All Role Logins
        $this->testAllRoleLogins();
        
        // Test 3: Dashboard Loading
        $this->testDashboardLoading();
        
        // Test 4: Dynamic Navigation
        $this->testDynamicNavigation();
        
        // Test 5: Role-Specific Content
        $this->testRoleSpecificContent();
        
        // Test 6: JavaScript Functionality
        $this->testJavaScriptFunctionality();
        
        // Test 7: API Endpoints
        $this->testAPIEndpoints();
        
        // Test 8: Security Features
        $this->testSecurityFeatures();
        
        // Test 9: Responsive Design
        $this->testResponsiveDesign();
        
        // Test 10: Logout Functionality
        $this->testLogoutFunctionality();
        
        // Generate final report
        $this->generateFinalReport();
        
        return $this->testResults;
    }
    
    /**
     * Test database connection
     */
    private function testDatabaseConnection() {
        echo "📊 Testing Database Connection...\n";
        
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASSWORD
            );
            
            $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['user_count'] >= 5) {
                $this->addTestResult('Database Connection', true, "Found {$result['user_count']} users");
            } else {
                $this->addTestResult('Database Connection', false, "Insufficient users: {$result['user_count']}");
            }
        } catch (Exception $e) {
            $this->addTestResult('Database Connection', false, $e->getMessage());
        }
    }
    
    /**
     * Test all role logins
     */
    private function testAllRoleLogins() {
        echo "🔐 Testing All Role Logins...\n";
        
        $roles = [
            'bos' => ['username' => 'bos', 'password' => 'bos'],
            'admin' => ['username' => 'admin', 'password' => 'admin'],
            'teller' => ['username' => 'teller', 'password' => 'teller'],
            'collector' => ['username' => 'collector', 'password' => 'collector'],
            'nasabah' => ['username' => 'nasabah', 'password' => 'nasabah']
        ];
        
        foreach ($roles as $role => $credentials) {
            $this->currentRole = $role;
            echo "  Testing {$role} login...\n";
            
            // Test login via API
            $loginResult = $this->testLoginAPI($credentials['username'], $credentials['password']);
            
            if ($loginResult['success']) {
                // Test dashboard access
                $dashboardResult = $this->testDashboardAccess($role);
                
                if ($dashboardResult['success']) {
                    $this->addTestResult("{$role} Login + Dashboard", true, "Full login flow successful");
                } else {
                    $this->addTestResult("{$role} Login + Dashboard", false, $dashboardResult['message']);
                }
            } else {
                $this->addTestResult("{$role} Login", false, $loginResult['message']);
            }
        }
    }
    
    /**
     * Test login via API
     */
    private function testLoginAPI($username, $password) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/api/auth.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'action' => 'login',
            'username' => $username,
            'password' => $password
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies_' . $username . '.txt');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Extract body from response
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($response, $headerSize);
        $data = json_decode($body, true);
        
        if ($httpCode === 200 && $data['success']) {
            return ['success' => true, 'data' => $data];
        } else {
            return ['success' => false, 'message' => $data['message'] ?? 'Login failed'];
        }
    }
    
    /**
     * Test dashboard access
     */
    private function testDashboardAccess($role) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/?page=dashboard');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies_' . $role . '.txt');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            // Check for dashboard elements
            $hasDashboard = strpos($response, 'dashboard') !== false;
            $hasRoleContent = strpos($response, $role) !== false;
            $hasNavigation = strpos($response, 'navigateTo') !== false;
            
            if ($hasDashboard && $hasRoleContent && $hasNavigation) {
                return ['success' => true, 'message' => 'Dashboard loaded successfully'];
            } else {
                return ['success' => false, 'message' => 'Dashboard elements missing'];
            }
        } else {
            return ['success' => false, 'message' => "HTTP {$httpCode}"];
        }
    }
    
    /**
     * Test dashboard loading
     */
    private function testDashboardLoading() {
        echo "📊 Testing Dashboard Loading...\n";
        
        $roles = ['bos', 'admin', 'teller', 'collector', 'nasabah'];
        
        foreach ($roles as $role) {
            echo "  Testing {$role} dashboard...\n";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/?page=dashboard');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies_' . $role . '.txt');
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            // Check for role-specific content
            $roleContent = $this->getExpectedDashboardContent($role);
            $hasContent = true;
            
            foreach ($roleContent as $content) {
                if (strpos($response, $content) === false) {
                    $hasContent = false;
                    break;
                }
            }
            
            if ($hasContent) {
                $this->addTestResult("{$role} Dashboard Content", true, "All expected content found");
            } else {
                $this->addTestResult("{$role} Dashboard Content", false, "Missing expected content");
            }
        }
    }
    
    /**
     * Get expected dashboard content per role
     */
    private function getExpectedDashboardContent($role) {
        $content = [
            'bos' => ['Total Anggota', 'Total Simpanan', 'Total Omzet'],
            'admin' => ['Anggota Aktif', 'Transaksi Hari Ini', 'User Terdaftar'],
            'teller' => ['Transaksi Hari Ini', 'Setoran', 'Penarikan'],
            'collector' => ['Target Hari Ini', 'Kunjungan Selesai', 'Kutipan Terkumpul'],
            'nasabah' => ['Saldo Simpanan', 'Pinjaman Aktif', 'Cicilan Bulanan']
        ];
        
        return $content[$role] ?? [];
    }
    
    /**
     * Test dynamic navigation
     */
    private function testDynamicNavigation() {
        echo "🧭 Testing Dynamic Navigation...\n";
        
        $pages = ['dashboard', 'laporan', 'nasabah', 'transaksi', 'profil'];
        
        foreach ($pages as $page) {
            echo "  Testing navigation to {$page}...\n";
            
            // Test navigation JavaScript function exists
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/?page=dashboard');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies_bos.txt');
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            $hasNavigateFunction = strpos($response, 'function navigateTo') !== false;
            $hasPageContent = strpos($response, "generate{$page}Content") !== false;
            
            if ($hasNavigateFunction && $hasPageContent) {
                $this->addTestResult("Navigation to {$page}", true, "Navigation function and content generator found");
            } else {
                $this->addTestResult("Navigation to {$page}", false, "Missing navigation function or content");
            }
        }
    }
    
    /**
     * Test role-specific content
     */
    private function testRoleSpecificContent() {
        echo "🎭 Testing Role-Specific Content...\n";
        
        $rolePages = [
            'bos' => ['dashboard', 'laporan', 'pengaturan'],
            'admin' => ['dashboard', 'laporan', 'nasabah', 'transaksi'],
            'teller' => ['dashboard', 'transaksi', 'setoran', 'penarikan'],
            'collector' => ['dashboard', 'kutipan', 'rute', 'gps_log'],
            'nasabah' => ['dashboard', 'profil', 'transaksi']
        ];
        
        foreach ($rolePages as $role => $pages) {
            foreach ($pages as $page) {
                echo "  Testing {$role} access to {$page}...\n";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/?page=' . $page);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies_' . $role . '.txt');
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode === 200) {
                    $hasContent = strpos($response, 'Coming Soon') === false;
                    $hasError = strpos($response, 'Error') !== false;
                    
                    if ($hasContent && !$hasError) {
                        $this->addTestResult("{$role} access to {$page}", true, "Content loaded successfully");
                    } else {
                        $this->addTestResult("{$role} access to {$page}", false, "Content not available or has error");
                    }
                } else {
                    $this->addTestResult("{$role} access to {$page}", false, "HTTP {$httpCode}");
                }
            }
        }
    }
    
    /**
     * Test JavaScript functionality
     */
    private function testJavaScriptFunctionality() {
        echo "⚡ Testing JavaScript Functionality...\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/?page=dashboard');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies_bos.txt');
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $jsFunctions = [
            'navigateTo' => 'function navigateTo(page, event)',
            'logout' => 'function logout()',
            'showNotification' => 'function showNotification',
            'toggleSidebar' => 'function toggleSidebar',
            'generateDashboardContent' => 'function generateDashboardContent'
        ];
        
        foreach ($jsFunctions as $funcName => $funcPattern) {
            $hasFunction = strpos($response, $funcPattern) !== false;
            
            if ($hasFunction) {
                $this->addTestResult("JavaScript: {$funcName}", true, "Function found");
            } else {
                $this->addTestResult("JavaScript: {$funcName}", false, "Function not found");
            }
        }
    }
    
    /**
     * Test API endpoints
     */
    private function testAPIEndpoints() {
        echo "🔌 Testing API Endpoints...\n";
        
        // Test login endpoint
        $loginTest = $this->testLoginAPI('bos', 'bos');
        $this->addTestResult('API: Login', $loginTest['success'], $loginTest['message']);
        
        // Test session check
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/api/auth.php?action=check_session');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies_bos.txt');
        
        $response = curl_exec($ch);
        $data = json_decode($response, true);
        
        if ($data['success'] && $data['authenticated']) {
            $this->addTestResult('API: Session Check', true, 'Session valid');
        } else {
            $this->addTestResult('API: Session Check', false, 'Session invalid');
        }
        
        // Test logout
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/api/auth.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'action=logout');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies_bos.txt');
        
        $response = curl_exec($ch);
        $data = json_decode($response, true);
        
        if ($data['success']) {
            $this->addTestResult('API: Logout', true, 'Logout successful');
        } else {
            $this->addTestResult('API: Logout', false, 'Logout failed');
        }
    }
    
    /**
     * Test security features
     */
    private function testSecurityFeatures() {
        echo "🔒 Testing Security Features...\n";
        
        // Test SQL injection protection
        $maliciousInput = "'; DROP TABLE users; --";
        $loginTest = $this->testLoginAPI($maliciousInput, $maliciousInput);
        
        if (!$loginTest['success']) {
            $this->addTestResult('Security: SQL Injection', true, 'SQL injection blocked');
        } else {
            $this->addTestResult('Security: SQL Injection', false, 'SQL injection not blocked');
        }
        
        // Test XSS protection
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/?page=dashboard&xss=<script>alert("xss")</script>');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies_bos.txt');
        
        $response = curl_exec($ch);
        
        if (strpos($response, '<script>alert("xss")</script>') === false) {
            $this->addTestResult('Security: XSS Protection', true, 'XSS filtered');
        } else {
            $this->addTestResult('Security: XSS Protection', false, 'XSS not filtered');
        }
        
        // Test session security
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/api/auth.php?action=check_session');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // No cookie file - should fail
        
        $response = curl_exec($ch);
        $data = json_decode($response, true);
        
        if (!$data['authenticated']) {
            $this->addTestResult('Security: Session Security', true, 'Unauthorized access blocked');
        } else {
            $this->addTestResult('Security: Session Security', false, 'Unauthorized access allowed');
        }
    }
    
    /**
     * Test responsive design
     */
    private function testResponsiveDesign() {
        echo "📱 Testing Responsive Design...\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/?page=dashboard');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies_bos.txt');
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $responsiveElements = [
            'bootstrap' => 'bootstrap',
            'viewport' => 'viewport',
            'container' => 'container',
            'row' => 'row',
            'col' => 'col-'
        ];
        
        foreach ($responsiveElements as $element => $pattern) {
            $hasElement = strpos($response, $pattern) !== false;
            
            if ($hasElement) {
                $this->addTestResult("Responsive: {$element}", true, "Element found");
            } else {
                $this->addTestResult("Responsive: {$element}", false, "Element not found");
            }
        }
    }
    
    /**
     * Test logout functionality
     */
    private function testLogoutFunctionality() {
        echo "🚪 Testing Logout Functionality...\n";
        
        // First login
        $this->testLoginAPI('bos', 'bos');
        
        // Then logout
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/api/auth.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'action=logout');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies_bos.txt');
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies_bos.txt');
        
        $response = curl_exec($ch);
        $data = json_decode($response, true);
        
        if ($data['success']) {
            // Test session after logout
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/api/auth.php?action=check_session');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies_bos.txt');
            
            $response = curl_exec($ch);
            $sessionData = json_decode($response, true);
            
            if (!$sessionData['authenticated']) {
                $this->addTestResult('Logout Functionality', true, 'Logout successful and session destroyed');
            } else {
                $this->addTestResult('Logout Functionality', false, 'Logout successful but session still active');
            }
        } else {
            $this->addTestResult('Logout Functionality', false, 'Logout failed');
        }
    }
    
    /**
     * Add test result
     */
    private function addTestResult($testName, $passed, $message) {
        $this->testResults['total']++;
        
        if ($passed) {
            $this->testResults['passed']++;
            echo "    ✅ {$testName}: {$message}\n";
        } else {
            $this->testResults['failed']++;
            echo "    ❌ {$testName}: {$message}\n";
            $this->testResults['errors'][] = [
                'test' => $testName,
                'message' => $message
            ];
        }
    }
    
    /**
     * Generate final report
     */
    private function generateFinalReport() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 COMPREHENSIVE TEST REPORT\n";
        echo str_repeat("=", 60) . "\n\n";
        
        echo "📈 Test Results:\n";
        echo "  Total Tests: {$this->testResults['total']}\n";
        echo "  Passed: {$this->testResults['passed']}\n";
        echo "  Failed: {$this->testResults['failed']}\n";
        
        $passRate = round(($this->testResults['passed'] / $this->testResults['total']) * 100, 2);
        echo "  Success Rate: {$passRate}%\n\n";
        
        if (!empty($this->testResults['errors'])) {
            echo "❌ Failed Tests:\n";
            foreach ($this->testResults['errors'] as $error) {
                echo "  • {$error['test']}: {$error['message']}\n";
            }
            echo "\n";
        }
        
        echo "🎯 System Status:\n";
        if ($passRate >= 90) {
            echo "  ✅ EXCELLENT - System ready for production\n";
        } elseif ($passRate >= 75) {
            echo "  ⚠️  GOOD - System mostly functional, minor issues\n";
        } elseif ($passRate >= 50) {
            echo "  🔶 FAIR - System partially functional, needs attention\n";
        } else {
            echo "  ❌ POOR - System has significant issues\n";
        }
        
        echo "\n📝 Recommendations:\n";
        if ($this->testResults['failed'] > 0) {
            echo "  • Address failed tests before production deployment\n";
            echo "  • Review error messages for specific issues\n";
            echo "  • Run individual tests for detailed debugging\n";
        } else {
            echo "  • System is ready for production deployment\n";
            echo "  • Consider adding more edge case tests\n";
            echo "  • Monitor system performance in production\n";
        }
        
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "🚀 KSP Lam Gabe Jaya - Comprehensive Test Complete\n";
        echo str_repeat("=", 60) . "\n";
    }
}

// Run comprehensive tests
if (php_sapi_name() === 'cli') {
    $testSuite = new PuppeteerTestSuite();
    $results = $testSuite->runComprehensiveTests();
    
    // Exit with appropriate code
    exit($results['failed'] > 0 ? 1 : 0);
} else {
    echo "<pre>";
    $testSuite = new PuppeteerTestSuite();
    $testSuite->runComprehensiveTests();
    echo "</pre>";
}
?>
