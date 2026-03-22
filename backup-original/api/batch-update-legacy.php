<?php
/**
 * Batch Update Script for Legacy API Files
 * Updates all legacy files with proper authentication and security
 */

// Define constant for access control
require_once __DIR__ . '/config/error-config.php';

define('KSP_API_ACCESS', true);

/**
 * Batch Update Class
 */
class BatchUpdate {
    
    private $legacyFiles = [];
    private $updatedFiles = [];
    private $errorFiles = [];
    
    /**
     * Initialize batch update
     */
    public function __construct() {
        $this->findLegacyFiles();
    }
    
    /**
     * Find all legacy API files
     */
    private function findLegacyFiles() {
        $apiDir = __DIR__;
        $files = glob($apiDir . '/*.php');
        
        // Exclude core files that are already updated
        $coreFiles = [
            'auth-enhanced.php',
            'members-crud.php',
            'loans-crud.php',
            'savings-crud.php',
            'user-management.php',
            'system-settings.php',
            'audit-log.php',
            'member-registration.php',
            'reports.php',
            'member-dashboard.php',
            'loan-application.php',
            'member-savings.php',
            'member-payments.php',
            'member-profile.php',
            'staff-dashboard.php',
            'staff-gps.php',
            'staff-members.php',
            'staff-tasks.php',
            'staff-reports.php',
            'analytics.php',
            'notifications.php',
            'reward-points.php',
            'DatabaseHelper.php',
            'Logger.php',
            'DataValidator.php',
            'SecurityLogger.php',
            'AuthHelper.php',
            'SecurityHelper.php',
            'SecurityMiddleware.php',
            'Config.php'
        ];
        
        foreach ($files as $file) {
            $filename = basename($file);
            
            if (!in_array($filename, $coreFiles) && 
                strpos($filename, 'audit-') !== 0 && 
                strpos($filename, 'validate-') !== 0 &&
                strpos($filename, 'tmp-') !== 0) {
                $this->legacyFiles[] = $file;
            }
        }
    }
    
    /**
     * Update all legacy files
     */
    public function updateAll() {
        echo "Starting batch update of legacy API files...\n";
        echo "Found " . count($this->legacyFiles) . " legacy files to update\n\n";
        
        foreach ($this->legacyFiles as $file) {
            echo "Updating: " . basename($file) . "... ";
            
            try {
                $this->updateFile($file);
                $this->updatedFiles[] = $file;
                echo "✅ SUCCESS\n";
            } catch (Exception $e) {
                $this->errorFiles[] = ['file' => $file, 'error' => $e->getMessage()];
                echo "❌ ERROR: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n=== BATCH UPDATE SUMMARY ===\n";
        echo "Total files: " . count($this->legacyFiles) . "\n";
        echo "Updated: " . count($this->updatedFiles) . "\n";
        echo "Errors: " . count($this->errorFiles) . "\n";
        
        if (!empty($this->errorFiles)) {
            echo "\nERRORS:\n";
            foreach ($this->errorFiles as $error) {
                echo "❌ " . basename($error['file']) . ": " . $error['error'] . "\n";
            }
        }
    }
    
    /**
     * Update individual file
     * @param string $file File path
     */
    private function updateFile($file) {
        $content = file_get_contents($file);
        
        if ($content === false) {
            throw new Exception("Cannot read file");
        }
        
        // Check if file already has security middleware
        if (strpos($content, 'SecurityMiddleware') !== false) {
            throw new Exception("File already updated");
        }
        
        // Add security headers and includes
        $securityHeaders = $this->getSecurityHeaders();
        $securityIncludes = $this->getSecurityIncludes();
        
        // Update the file content
        $updatedContent = $this->updateFileContent($content, $securityHeaders, $securityIncludes);
        
        // Write updated content
        $result = file_put_contents($file, $updatedContent);
        
        if ($result === false) {
            throw new Exception("Cannot write to file");
        }
    }
    
    /**
     * Get security headers
     * @return string Security headers code
     */
    private function getSecurityHeaders() {
        return '<?php
/**
 * ' . basename($this->getCurrentFile()) . ' - Updated with Security
 * Auto-generated security update
 */

// Security headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token");

// Handle preflight requests
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    exit(0);
}

// Prevent direct access
if (!defined("KSP_API_ACCESS")) {
    header("HTTP/1.0 403 Forbidden");
    exit("Direct access to this file is not allowed.");
}

';
    }
    
    /**
     * Get security includes
     * @return string Security includes code
     */
    private function getSecurityIncludes() {
        return '// Include required files
require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/DatabaseHelper.php";
require_once __DIR__ . "/Logger.php";
require_once __DIR__ . "/DataValidator.php";
require_once __DIR__ . "/SecurityLogger.php";
require_once __DIR__ . "/AuthHelper.php";
require_once __DIR__ . "/SecurityHelper.php";
require_once __DIR__ . "/SecurityMiddleware.php";

// Initialize services
Logger::initialize();
$securityLogger = SecurityLogger::getInstance();
$db = DatabaseHelper::getInstance();
$validator = new DataValidator();

// Initialize response
$response = [
    "success" => false,
    "message" => "",
    "data" => null,
    "errors" => [],
    "timestamp" => date("Y-m-d H:i:s")
];

';
    }
    
    /**
     * Update file content with security
     * @param string $content Original content
     * @param string $headers Security headers
     * @param string $includes Security includes
     * @return string Updated content
     */
    private function updateFileContent($content, $headers, $includes) {
        // Remove existing PHP tag and headers
        $content = preg_replace('/^<\?php\s*\n/', '', $content);
        
        // Remove existing header() calls
        $content = preg_replace('/header\s*\([^)]+\);\s*\n/', '', $content);
        
        // Remove existing includes
        $content = preg_replace('/require_once\s+[^;]+;\s*\n/', '', $content);
        $content = preg_replace('/include_once\s+[^;]+;\s*\n/', '', $content);
        $content = preg_replace('/require\s+[^;]+;\s*\n/', '', $content);
        $content = preg_replace('/include\s+[^;]+;\s*\n/', '', $content);
        
        // Remove existing response initialization
        $content = preg_replace('/\$response\s*=\s*\[[^\]]*\];\s*\n/', '', $content);
        
        // Update authentication function calls
        $content = preg_replace('/requireAuth\s*\([^)]*\)/', 'SecurityMiddleware::requireAuth', $content);
        $content = preg_replace('/getTokenFromRequest\s*\(\s*\)/', 'AuthHelper::getTokenFromRequest()', $content);
        $content = preg_replace('/validateJWTToken\s*\([^)]*\)/', 'AuthHelper::validateJWTToken', $content);
        $content = preg_replace('/getCurrentUser\s*\(\s*\)/', 'AuthHelper::getCurrentUser()', $content);
        
        // Update echo statements for security
        $content = preg_replace('/echo\s+json_encode\s*\([^)]+\);/', 'SecurityMiddleware::sendJSONResponse($response);', $content);
        
        // Update error handling
        $content = preg_replace('/\$response\[\'message\'\]\s*=\s*\$e->getMessage\(\);\s*\n\s*echo\s+json_encode\s*\([^)]+\);/', '$response["message"] = $e->getMessage(); SecurityMiddleware::sendJSONResponse($response, 500);', $content);
        
        // Add security headers and includes
        $updatedContent = $headers . $includes . $content;
        
        return $updatedContent;
    }
    
    /**
     * Get current file being processed
     * @return string File path
     */
    private function getCurrentFile() {
        return debug_backtrace()[2]['file'] ?? 'unknown.php';
    }
    
    /**
     * Create backup of original files
     */
    public function createBackups() {
        echo "Creating backups of original files...\n";
        
        $backupDir = __DIR__ . '/backups/' . date('Y-m-d_H-i-s');
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        foreach ($this->legacyFiles as $file) {
            $backupFile = $backupDir . '/' . basename($file);
            copy($file, $backupFile);
        }
        
        echo "Backups created in: $backupDir\n";
    }
    
    /**
     * Validate updated files
     */
    public function validateUpdates() {
        echo "Validating updated files...\n";
        
        $validationErrors = [];
        
        foreach ($this->updatedFiles as $file) {
            // Check syntax
            $output = [];
            $returnCode = 0;
            exec("php -l \"$file\" 2>&1", $output, $returnCode);
            
            if ($returnCode !== 0) {
                $validationErrors[] = [
                    'file' => $file,
                    'errors' => $output
                ];
            }
        }
        
        if (empty($validationErrors)) {
            echo "✅ All updated files pass syntax validation\n";
        } else {
            echo "❌ Syntax validation errors found:\n";
            foreach ($validationErrors as $error) {
                echo "❌ " . basename($error['file']) . ":\n";
                foreach ($error['errors'] as $line) {
                    echo "   $line\n";
                }
            }
        }
        
        return empty($validationErrors);
    }
    
    /**
     * Generate update report
     */
    public function generateReport() {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'total_files' => count($this->legacyFiles),
            'updated_files' => count($this->updatedFiles),
            'error_files' => count($this->errorFiles),
            'updated_list' => array_map('basename', $this->updatedFiles),
            'error_list' => array_map(function($error) {
                return [
                    'file' => basename($error['file']),
                    'error' => $error['error']
                ];
            }, $this->errorFiles)
        ];
        
        $reportContent = json_encode($report, JSON_PRETTY_PRINT);
        
        $reportFile = __DIR__ . '/batch-update-report-' . date('Y-m-d-H-i-s') . '.json';
        file_put_contents($reportFile, $reportContent);
        
        echo "Report generated: $reportFile\n";
        
        return $report;
    }
}

// Run batch update if executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $batchUpdate = new BatchUpdate();
    
    echo "=== KSP LAM GABE JAYA - BATCH SECURITY UPDATE ===\n";
    echo "This will update all legacy API files with proper security measures\n\n";
    
    // Create backups
    $batchUpdate->createBackups();
    
    // Update files
    $batchUpdate->updateAll();
    
    // Validate updates
    $isValid = $batchUpdate->validateUpdates();
    
    // Generate report
    $report = $batchUpdate->generateReport();
    
    echo "\n=== BATCH UPDATE COMPLETED ===\n";
    echo "Status: " . ($isValid ? "✅ SUCCESS" : "❌ ERRORS FOUND") . "\n";
    echo "Files Updated: {$report['updated_files']}/{$report['total_files']}\n";
    
    if (!$isValid) {
        echo "\n⚠️  Please fix the validation errors before proceeding.\n";
    }
}
?>
