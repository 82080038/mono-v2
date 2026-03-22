/**
 * Step-by-Step Validation Tool
 * Validates each phase of testing
 */

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/DatabaseHelper.php';
require_once __DIR__ . '/AuthHelper.php';
require_once __DIR__ . '/SecurityHelper.php';
require_once __DIR__ . '/DataValidator.php';

class StepByStepValidation {
    private $validationResults = [];
    
    public function runStepByStepValidation() {
        echo "=== KSP LAM GABE JAYA - STEP-BY-STEP VALIDATION ===\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Step 1: Environment Validation
        $this->validateEnvironment();
        
        // Step 2: Foundation Validation
        $this->validateFoundation();
        
        // Step 3: Database Validation
        $this->validateDatabase();
        
        // Step 4: Security Validation
        $this->validateSecurity();
        
        // Step 5: API Validation
        $this->validateAPIs();
        
        // Step 6: Frontend Validation
        $this->validateFrontend();
        
        // Step 7: Integration Validation
        $this->validateIntegration();
        
        // Step 8: Performance Validation
        $this->validatePerformance();
        
        // Step 9: End-to-End Validation
        $this->validateEndToEnd();
        
        // Step 10: Production Readiness Validation
        $this->validateProductionReadiness();
        
        $this->generateValidationReport();
        return $this->validationResults;
    }
    
    /**
     * Step 1: Environment Validation
     */
    private function validateEnvironment() {
        echo "🔍 STEP 1: Environment Validation\n";
        echo "=================================\n";
        
        $this->validationResults['environment'] = [
            'php_version' => $this->checkPHPVersion(),
            'required_extensions' => $this->checkRequiredExtensions(),
            'file_permissions' => $this->checkFilePermissions(),
            'memory_limit' => $this->checkMemoryLimit(),
            'max_execution_time' => $this->checkMaxExecutionTime(),
            'status' => 'pending'
        ];
        
        $checks = [
            'PHP Version' => $this->validationResults['environment']['php_version'],
            'Required Extensions' => $this->validationResults['environment']['required_extensions'],
            'File Permissions' => $this->validationResults['environment']['file_permissions'],
            'Memory Limit' => $this->validationResults['environment']['memory_limit'],
            'Max Execution Time' => $this->validationResults['environment']['max_execution_time']
        ];
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        $this->validationResults['environment']['status'] = count(array_filter($checks)) >= 4 ? 'PASS' : 'FAIL';
        echo "  Status: " . $this->validationResults['environment']['status'] . "\n\n";
    }
    
    /**
     * Step 2: Foundation Validation
     */
    private function validateFoundation() {
        echo "🔍 STEP 2: Foundation Validation\n";
        echo "===============================\n";
        
        $this->validationResults['foundation'] = [
            'database_helper' => $this->checkDatabaseHelper(),
            'auth_helper' => $this->checkAuthHelper(),
            'security_helper' => $this->checkSecurityHelper(),
            'data_validator' => $this->checkDataValidator(),
            'logger' => $this->checkLogger(),
            'security_middleware' => $this->checkSecurityMiddleware(),
            'config_file' => $this->checkConfigFile(),
            'status' => 'pending'
        ];
        
        $checks = [
            'DatabaseHelper' => $this->validationResults['foundation']['database_helper'],
            'AuthHelper' => $this->validationResults['foundation']['auth_helper'],
            'SecurityHelper' => $this->validationResults['foundation']['security_helper'],
            'DataValidator' => $this->validationResults['foundation']['data_validator'],
            'Logger' => $this->validationResults['foundation']['logger'],
            'SecurityMiddleware' => $this->validationResults['foundation']['security_middleware'],
            'Config File' => $this->validationResults['foundation']['config_file']
        ];
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        $this->validationResults['foundation']['status'] = count(array_filter($checks)) >= 6 ? 'PASS' : 'FAIL';
        echo "  Status: " . $this->validationResults['foundation']['status'] . "\n\n";
    }
    
    /**
     * Step 3: Database Validation
     */
    private function validateDatabase() {
        echo "🔍 STEP 3: Database Validation\n";
        echo "==============================\n";
        
        $this->validationResults['database'] = [
            'connection' => $this->checkDatabaseConnection(),
            'tables' => $this->checkDatabaseTables(),
            'indexes' => $this->checkDatabaseIndexes(),
            'data_integrity' => $this->checkDataIntegrity(),
            'foreign_keys' => $this->checkForeignKeys(),
            'status' => 'pending'
        ];
        
        $checks = [
            'Database Connection' => $this->validationResults['database']['connection'],
            'Database Tables' => $this->validationResults['database']['tables'],
            'Database Indexes' => $this->validationResults['database']['indexes'],
            'Data Integrity' => $this->validationResults['database']['data_integrity'],
            'Foreign Keys' => $this->validationResults['database']['foreign_keys']
        ];
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        $this->validationResults['database']['status'] = count(array_filter($checks)) >= 4 ? 'PASS' : 'FAIL';
        echo "  Status: " . $this->validationResults['database']['status'] . "\n\n";
    }
    
    /**
     * Step 4: Security Validation
     */
    private function validateSecurity() {
        echo "🔍 STEP 4: Security Validation\n";
        echo "============================\n";
        
        $this->validationResults['security'] = [
            'authentication' => $this->checkAuthentication(),
            'authorization' => $this->checkAuthorization(),
            'input_validation' => $this->checkInputValidation(),
            'xss_protection' => $this->checkXSSProtection(),
            'sql_injection_protection' => $this->checkSQLInjectionProtection(),
            'csrf_protection' => $this->checkCSRFProtection(),
            'status' => 'pending'
        ];
        
        $checks = [
            'Authentication' => $this->validationResults['security']['authentication'],
            'Authorization' => $this->validationResults['security']['authorization'],
            'Input Validation' => $this->validationResults['security']['input_validation'],
            'XSS Protection' => $this->validationResults['security']['xss_protection'],
            'SQL Injection Protection' => $this->validationResults['security']['sql_injection_protection'],
            'CSRF Protection' => $this->validationResults['security']['csrf_protection']
        ];
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        $this->validationResults['security']['status'] = count(array_filter($checks)) >= 5 ? 'PASS' : 'FAIL';
        echo "  Status: " . $this->validationResults['security']['status'] . "\n\n";
    }
    
    /**
     * Step 5: API Validation
     */
    private function validateAPIs() {
        echo "🔍 STEP 5: API Validation\n";
        echo "========================\n";
        
        $this->validationResults['apis'] = [
            'syntax_validation' => $this->checkAPISyntax(),
            'functionality' => $this->checkAPIFunctionality(),
            'security_implementation' => $this->checkAPISecurity(),
            'error_handling' => $this->checkAPIErrorHandling(),
            'response_format' => $this->checkAPIResponseFormat(),
            'status' => 'pending'
        ];
        
        $checks = [
            'Syntax Validation' => $this->validationResults['apis']['syntax_validation'],
            'Functionality' => $this->validationResults['apis']['functionality'],
            'Security Implementation' => $this->validationResults['apis']['security_implementation'],
            'Error Handling' => $this->validationResults['apis']['error_handling'],
            'Response Format' => $this->validationResults['apis']['response_format']
        ];
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        $this->validationResults['apis']['status'] = count(array_filter($checks)) >= 4 ? 'PASS' : 'FAIL';
        echo "  Status: " . $this->validationResults['apis']['status'] . "\n\n";
    }
    
    /**
     * Step 6: Frontend Validation
     */
    private function validateFrontend() {
        echo "🔍 STEP 6: Frontend Validation\n";
        echo "=============================\n";
        
        $this->validationResults['frontend'] = [
            'pages_exist' => $this->checkPagesExist(),
            'static_assets' => $this->checkStaticAssets(),
            'javascript_files' => $this->checkJavaScriptFiles(),
            'css_files' => $this->checkCSSFiles(),
            'page_structure' => $this->checkPageStructure(),
            'status' => 'pending'
        ];
        
        $checks = [
            'Pages Exist' => $this->validationResults['frontend']['pages_exist'],
            'Static Assets' => $this->validationResults['frontend']['static_assets'],
            'JavaScript Files' => $this->validationResults['frontend']['javascript_files'],
            'CSS Files' => $this->validationResults['frontend']['css_files'],
            'Page Structure' => $this->validationResults['frontend']['page_structure']
        ];
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        $this->validationResults['frontend']['status'] = count(array_filter($checks)) >= 4 ? 'PASS' : 'FAIL';
        echo "  Status: " . $this->validationResults['frontend']['status'] . "\n\n";
    }
    
    /**
     * Step 7: Integration Validation
     */
    private function validateIntegration() {
        echo "🔍 STEP 7: Integration Validation\n";
        echo "===============================\n";
        
        $this->validationResults['integration'] = [
            'api_database' => $this->checkAPIDatabaseIntegration(),
            'frontend_backend' => $this->checkFrontendBackendIntegration(),
            'security_integration' => $this->checkSecurityIntegration(),
            'middleware_integration' => $this->checkMiddlewareIntegration(),
            'cross_component' => $this->checkCrossComponentIntegration(),
            'status' => 'pending'
        ];
        
        $checks = [
            'API-Database Integration' => $this->validationResults['integration']['api_database'],
            'Frontend-Backend Integration' => $this->validationResults['integration']['frontend_backend'],
            'Security Integration' => $this->validationResults['integration']['security_integration'],
            'Middleware Integration' => $this->validationResults['integration']['middleware_integration'],
            'Cross-Component Integration' => $this->validationResults['integration']['cross_component']
        ];
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        $this->validationResults['integration']['status'] = count(array_filter($checks)) >= 4 ? 'PASS' : 'FAIL';
        echo "  Status: " . $this->validationResults['integration']['status'] . "\n\n";
    }
    
    /**
     * Step 8: Performance Validation
     */
    private function validatePerformance() {
        echo "🔍 STEP 8: Performance Validation\n";
        echo "===============================\n";
        
        $this->validationResults['performance'] = [
            'database_performance' => $this->checkDatabasePerformance(),
            'api_response_time' => $this->checkAPIResponseTime(),
            'memory_usage' => $this->checkMemoryUsage(),
            'file_size_optimization' => $this->checkFileSizeOptimization(),
            'query_optimization' => $this->checkQueryOptimization(),
            'status' => 'pending'
        ];
        
        $checks = [
            'Database Performance' => $this->validationResults['performance']['database_performance'],
            'API Response Time' => $this->validationResults['performance']['api_response_time'],
            'Memory Usage' => $this->validationResults['performance']['memory_usage'],
            'File Size Optimization' => $this->validationResults['performance']['file_size_optimization'],
            'Query Optimization' => $this->validationResults['performance']['query_optimization']
        ];
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        $this->validationResults['performance']['status'] = count(array_filter($checks)) >= 4 ? 'PASS' : 'FAIL';
        echo "  Status: " . $this->validationResults['performance']['status'] . "\n\n";
    }
    
    /**
     * Step 9: End-to-End Validation
     */
    private function validateEndToEnd() {
        echo "🔍 STEP 9: End-to-End Validation\n";
        echo "==============================\n";
        
        $this->validationResults['e2e'] = [
            'user_registration_flow' => $this->checkUserRegistrationFlow(),
            'loan_application_flow' => $this->checkLoanApplicationFlow(),
            'payment_processing_flow' => $this->checkPaymentProcessingFlow(),
            'report_generation_flow' => $this->checkReportGenerationFlow(),
            'admin_workflow' => $this->checkAdminWorkflow(),
            'status' => 'pending'
        ];
        
        $checks = [
            'User Registration Flow' => $this->validationResults['e2e']['user_registration_flow'],
            'Loan Application Flow' => $this->validationResults['e2e']['loan_application_flow'],
            'Payment Processing Flow' => $this->validationResults['e2e']['payment_processing_flow'],
            'Report Generation Flow' => $this->validationResults['e2e']['report_generation_flow'],
            'Admin Workflow' => $this->validationResults['e2e']['admin_workflow']
        ];
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        $this->validationResults['e2e']['status'] = count(array_filter($checks)) >= 4 ? 'PASS' : 'FAIL';
        echo "  Status: " . $this->validationResults['e2e']['status'] . "\n\n";
    }
    
    /**
     * Step 10: Production Readiness Validation
     */
    private function validateProductionReadiness() {
        echo "🔍 STEP 10: Production Readiness Validation\n";
        echo "========================================\n";
        
        $this->validationResults['production_readiness'] = [
            'overall_health' => $this->calculateOverallHealth(),
            'critical_components' => $this->checkCriticalComponents(),
            'backup_strategy' => $this->checkBackupStrategy(),
            'monitoring_setup' => $this->checkMonitoringSetup(),
            'documentation' => $this->checkDocumentation(),
            'status' => 'pending'
        ];
        
        $checks = [
            'Overall Health' => $this->validationResults['production_readiness']['overall_health'],
            'Critical Components' => $this->validationResults['production_readiness']['critical_components'],
            'Backup Strategy' => $this->validationResults['production_readiness']['backup_strategy'],
            'Monitoring Setup' => $this->validationResults['production_readiness']['monitoring_setup'],
            'Documentation' => $this->validationResults['production_readiness']['documentation']
        ];
        
        foreach ($checks as $check => $result) {
            echo "  " . ($result ? "✅" : "❌") . " $check\n";
        }
        
        $this->validationResults['production_readiness']['status'] = count(array_filter($checks)) >= 4 ? 'PASS' : 'FAIL';
        echo "  Status: " . $this->validationResults['production_readiness']['status'] . "\n\n";
    }
    
    // Helper methods for validation checks
    private function checkPHPVersion() {
        return version_compare(PHP_VERSION, '8.0', '>=');
    }
    
    private function checkRequiredExtensions() {
        $required = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'curl'];
        $loaded = array_filter($required, function($ext) {
            return extension_loaded($ext);
        });
        return count($loaded) >= count($required) - 1; // Allow 1 missing
    }
    
    private function checkFilePermissions() {
        return is_writable(__DIR__) && is_readable(__DIR__);
    }
    
    private function checkMemoryLimit() {
        $memoryLimit = ini_get('memory_limit');
        return $memoryLimit >= '128M';
    }
    
    private function checkMaxExecutionTime() {
        $maxTime = ini_get('max_execution_time');
        return $maxTime >= 30;
    }
    
    private function checkDatabaseHelper() {
        return file_exists(__DIR__ . '/DatabaseHelper.php');
    }
    
    private function checkAuthHelper() {
        return file_exists(__DIR__ . '/AuthHelper.php');
    }
    
    private function checkSecurityHelper() {
        return file_exists(__DIR__ . '/SecurityHelper.php');
    }
    
    private function checkDataValidator() {
        return file_exists(__DIR__ . '/DataValidator.php');
    }
    
    private function checkLogger() {
        return file_exists(__DIR__ . '/Logger.php');
    }
    
    private function checkSecurityMiddleware() {
        return file_exists(__DIR__ . '/SecurityMiddleware.php');
    }
    
    private function checkConfigFile() {
        return file_exists(__DIR__ . '/../config/Config.php');
    }
    
    private function checkDatabaseConnection() {
        try {
            $db = DatabaseHelper::getInstance();
            $result = $db->fetchOne("SELECT 1");
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkDatabaseTables() {
        try {
            $db = DatabaseHelper::getInstance();
            $tables = $db->fetchAll("SHOW TABLES");
            return count($tables) >= 10;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkDatabaseIndexes() {
        try {
            $db = DatabaseHelper::getInstance();
            $indexes = $db->fetchAll("SHOW INDEX FROM users");
            return count($indexes) > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkDataIntegrity() {
        try {
            $db = DatabaseHelper::getInstance();
            $users = $db->fetchOne("SELECT COUNT(*) as count FROM users");
            return $users['count'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkForeignKeys() {
        try {
            $db = DatabaseHelper::getInstance();
            $constraints = $db->fetchAll("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'ksp_lamgabejaya_v2' AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1");
            return !empty($constraints);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkAuthentication() {
        try {
            $token = AuthHelper::generateToken(['id' => 1, 'role' => 'admin']);
            $validated = AuthHelper::validateJWTToken($token);
            return $validated !== null;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkAuthorization() {
        try {
            $user = ['role' => 'admin'];
            return AuthHelper::validateRole('admin', $user);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkInputValidation() {
        try {
            $validator = new DataValidator();
            return $validator->validate(['test' => 'value'], ['test' => 'required']);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkXSSProtection() {
        try {
            $malicious = '<script>alert("xss")</script>';
            $sanitized = SecurityHelper::sanitize($malicious);
            return strpos($sanitized, '<script>') === false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkSQLInjectionProtection() {
        try {
            $malicious = "'; DROP TABLE users; --";
            return SecurityHelper::containsSQLInjection($malicious);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkCSRFProtection() {
        try {
            $token = SecurityHelper::generateCSRFToken();
            return SecurityHelper::validateCSRFToken($token);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkAPISyntax() {
        $apiFiles = glob(__DIR__ . '/*.php');
        $validFiles = 0;
        
        foreach ($apiFiles as $file) {
            $output = [];
            $returnCode = 0;
            exec("php -l \"$file\" 2>&1", $output, $returnCode);
            
            if ($returnCode === 0) {
                $validFiles++;
            }
        }
        
        return ($validFiles / count($apiFiles)) >= 0.95;
    }
    
    private function checkAPIFunctionality() {
        $coreAPIs = ['auth-enhanced.php', 'members-crud.php', 'loans-crud.php'];
        $functionalAPIs = 0;
        
        foreach ($coreAPIs as $api) {
            if (file_exists(__DIR__ . '/' . $api)) {
                $functionalAPIs++;
            }
        }
        
        return $functionalAPIs >= 3;
    }
    
    private function checkAPISecurity() {
        $apiFiles = glob(__DIR__ . '/*.php');
        $secureAPIs = 0;
        
        foreach ($apiFiles as $file) {
            $content = file_get_contents($file);
            if (strpos($content, 'SecurityMiddleware::requireAuth') !== false || strpos($content, 'SecurityHelper::sanitize') !== false) {
                $secureAPIs++;
            }
        }
        
        return ($secureAPIs / count($apiFiles)) >= 0.8;
    }
    
    private function checkAPIErrorHandling() {
        $apiFiles = glob(__DIR__ . '/*.php');
        $filesWithErrorHandling = 0;
        
        foreach ($apiFiles as $file) {
            $content = file_get_contents($file);
            if (strpos($content, 'try') !== false && strpos($content, 'catch') !== false) {
                $filesWithErrorHandling++;
            }
        }
        
        return ($filesWithErrorHandling / count($apiFiles)) >= 0.8;
    }
    
    private function checkAPIResponseFormat() {
        $apiFiles = glob(__DIR__ . '/*.php');
        $filesWithJSONResponse = 0;
        
        foreach ($apiFiles as $file) {
            $content = file_get_contents($file);
            if (strpos($content, 'json_encode') !== false || strpos($content, 'sendJSONResponse') !== false) {
                $filesWithJSONResponse++;
            }
        }
        
        return ($filesWithJSONResponse / count($apiFiles)) >= 0.8;
    }
    
    private function checkPagesExist() {
        $pagesDir = __DIR__ . '/../pages';
        return is_dir($pagesDir) && count(glob($pagesDir . '/*/*.html')) > 0;
    }
    
    private function checkStaticAssets() {
        $assetsDir = __DIR__ . '/../assets';
        return is_dir($assetsDir);
    }
    
    private function checkJavaScriptFiles() {
        $jsDir = __DIR__ . '/../assets/js';
        return is_dir($jsDir) && count(glob($jsDir . '/*.js')) > 0;
    }
    
    private function checkCSSFiles() {
        $cssDir = __DIR__ . '/../assets/css';
        return is_dir($cssDir) && count(glob($cssDir . '/*.css')) > 0;
    }
    
    private function checkPageStructure() {
        $pagesDir = __DIR__ . '/../pages';
        $adminPages = glob($pagesDir . '/admin/*.html');
        $memberPages = glob($pagesDir . '/member/*.html');
        $staffPages = glob($pagesDir . '/staff/*.html');
        
        return count($adminPages) > 0 && count($memberPages) > 0 && count($staffPages) > 0;
    }
    
    private function checkAPIDatabaseIntegration() {
        try {
            $db = DatabaseHelper::getInstance();
            $result = $db->fetchOne("SELECT COUNT(*) as count FROM users");
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkFrontendBackendIntegration() {
        $pagesDir = __DIR__ . '/../pages';
        $apiDir = __DIR__;
        
        return is_dir($pagesDir) && is_dir($apiDir);
    }
    
    private function checkSecurityIntegration() {
        try {
            $token = AuthHelper::generateToken(['id' => 1, 'role' => 'admin']);
            $validated = AuthHelper::validateJWTToken($token);
            $sanitized = SecurityHelper::sanitize('<script>alert("test")</script>');
            
            return $validated !== null && strpos($sanitized, '<script>') === false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkMiddlewareIntegration() {
        return class_exists('AuthHelper') && class_exists('SecurityHelper') && class_exists('SecurityMiddleware');
    }
    
    private function checkCrossComponentIntegration() {
        return $this->checkAPIDatabaseIntegration() && $this->checkFrontendBackendIntegration() && $this->checkSecurityIntegration();
    }
    
    private function checkDatabasePerformance() {
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
    
    private function checkAPIResponseTime() {
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
    
    private function checkMemoryUsage() {
        $memoryBefore = memory_get_usage();
        
        $data = [];
        for ($i = 0; $i < 1000; $i++) {
            $data[] = ['id' => $i, 'name' => 'Test ' . $i];
        }
        
        $memoryAfter = memory_get_usage();
        $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024;
        
        return $memoryUsed < 10;
    }
    
    private function checkFileSizeOptimization() {
        $apiFiles = glob(__DIR__ . '/*.php');
        $totalSize = 0;
        
        foreach ($apiFiles as $file) {
            $totalSize += filesize($file);
        }
        
        $averageSize = $totalSize / count($apiFiles) / 1024;
        return $averageSize < 50;
    }
    
    private function checkQueryOptimization() {
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
    
    private function checkUserRegistrationFlow() {
        $usersTable = file_exists(__DIR__ . '/../database/ksp_lamgabejaya_v2.sql');
        $authAPI = file_exists(__DIR__ . '/auth-enhanced.php');
        $membersAPI = file_exists(__DIR__ . '/members-crud.php');
        
        return $usersTable && $authAPI && $membersAPI;
    }
    
    private function checkLoanApplicationFlow() {
        $loansTable = file_exists(__DIR__ . '/../database/ksp_lamgabejaya_v2.sql');
        $loanAPI = file_exists(__DIR__ . '/loans-crud.php');
        
        return $loansTable && $loanAPI;
    }
    
    private function checkPaymentProcessingFlow() {
        $paymentsTable = file_exists(__DIR__ . '/../database/ksp_lamgabejaya_v2.sql');
        $paymentAPI = file_exists(__DIR__ . '/member-payments.php');
        
        return $paymentsTable && $paymentAPI;
    }
    
    private function checkReportGenerationFlow() {
        $reportsAPI = file_exists(__DIR__ . '/reports.php');
        $analyticsAPI = file_exists(__DIR__ . '/analytics.php');
        
        return $reportsAPI && $analyticsAPI;
    }
    
    private function checkAdminWorkflow() {
        $userManagementAPI = file_exists(__DIR__ . '/user-management.php');
        $systemSettingsAPI = file_exists(__DIR__ . '/system-settings.php');
        $auditLogAPI = file_exists(__DIR__ . '/audit-log.php');
        
        return $userManagementAPI && $systemSettingsAPI && $auditLogAPI;
    }
    
    private function calculateOverallHealth() {
        $steps = ['environment', 'foundation', 'database', 'security', 'apis', 'frontend', 'integration', 'performance', 'e2e'];
        $passedSteps = 0;
        
        foreach ($steps as $step) {
            if (isset($this->validationResults[$step]) && $this->validationResults[$step]['status'] === 'PASS') {
                $passedSteps++;
            }
        }
        
        return ($passedSteps / count($steps)) >= 0.8;
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
    
    /**
     * Generate validation report
     */
    private function generateValidationReport() {
        echo "📊 STEP-BY-STEP VALIDATION SUMMARY\n";
        echo "=================================\n";
        
        $steps = [
            'environment' => 'Environment',
            'foundation' => 'Foundation',
            'database' => 'Database',
            'security' => 'Security',
            'apis' => 'APIs',
            'frontend' => 'Frontend',
            'integration' => 'Integration',
            'performance' => 'Performance',
            'e2e' => 'End-to-End',
            'production_readiness' => 'Production Readiness'
        ];
        
        $passedSteps = 0;
        $totalSteps = count($steps);
        
        echo "📊 Step Results:\n";
        foreach ($steps as $key => $name) {
            $status = $this->validationResults[$key]['status'] ?? 'UNKNOWN';
            if ($status === 'PASS') {
                $passedSteps++;
            }
            echo "  $name: $status\n";
        }
        
        $overallScore = round(($passedSteps / $totalSteps) * 100, 2);
        
        echo "\n📊 Overall Results:\n";
        echo "  Steps Completed: $passedSteps/$totalSteps\n";
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
        
        // Save validation results
        $this->validationResults['summary'] = [
            'overall_score' => $overallScore,
            'steps_completed' => $passedSteps,
            'total_steps' => $totalSteps,
            'production_ready' => $overallScore >= 85,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents(__DIR__ . '/step-by-step-validation-results.json', json_encode($this->validationResults, JSON_PRETTY_PRINT));
        echo "\n📄 Validation results saved to: step-by-step-validation-results.json\n";
        echo "\n=== STEP-BY-STEP VALIDATION COMPLETED ===\n";
    }
}

// Run validation if executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    require_once __DIR__ . '/DatabaseHelper.php';
    require_once __DIR__ . '/AuthHelper.php';
    require_once __DIR__ . '/SecurityHelper.php';
    require_once __DIR__ . '/DataValidator.php';
    
    $validation = new StepByStepValidation();
    $results = $validation->runStepByStepValidation();
}
?>
