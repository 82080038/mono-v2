<?php
/**
 * Security Audit Tool
 * KSP Lam Gabe Jaya v2.0
 */

class SecurityAudit {
    
    private $issues = [];
    private $recommendations = [];
    private $scanResults = [];
    
    public function runFullAudit() {
        echo "=== SECURITY AUDIT REPORT ===\n\n";
        
        $this->scanForHardcodedCredentials();
        $this->checkSqlInjectionVulnerabilities();
        $this->checkXssVulnerabilities();
        $this->checkFilePermissions();
        $this->checkErrorReporting();
        $this->checkSessionSecurity();
        $this->checkInputValidation();
        $this->checkDatabaseSecurity();
        $this->checkCorsConfiguration();
        $this->checkBackupFiles();
        
        $this->generateReport();
    }
    
    /**
     * Scan for hardcoded credentials
     */
    private function scanForHardcodedCredentials() {
        echo "Scanning for hardcoded credentials...\n";
        
        $patterns = [
            '/password\s*=\s*["\'][^"\']+["\']/i',
            '/secret\s*=\s*["\'][^"\']+["\']/i',
            '/api_key\s*=\s*["\'][^"\']+["\']/i',
            '/token\s*=\s*["\'][^"\']+["\']/i'
        ];
        
        $files = $this->getAllPhpFiles();
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $content, $matches)) {
                    $this->issues[] = [
                        'type' => 'HARDCODED_CREDENTIALS',
                        'file' => $file,
                        'line' => $this->findLineNumber($content, $matches[0]),
                        'description' => 'Potential hardcoded credentials found',
                        'code' => substr($matches[0], 0, 50) . '...'
                    ];
                }
            }
        }
        
        echo "✅ Credentials scan completed\n\n";
    }
    
    /**
     * Check for SQL injection vulnerabilities
     */
    private function checkSqlInjectionVulnerabilities() {
        echo "Checking for SQL injection vulnerabilities...\n";
        
        $vulnerablePatterns = [
            '/\$_GET\[[^\]]+\].*mysql_query/i',
            '/\$_POST\[[^\]]+\].*mysql_query/i',
            '/mysql_query.*\$_GET/i',
            '/mysql_query.*\$_POST/i',
            '/\$_GET\[[^\]]+\].*SELECT.*FROM/i',
            '/\$_POST\[[^\]]+\].*SELECT.*FROM/i'
        ];
        
        $files = $this->getAllPhpFiles();
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            foreach ($vulnerablePatterns as $pattern) {
                if (preg_match($pattern, $content, $matches)) {
                    $this->issues[] = [
                        'type' => 'SQL_INJECTION',
                        'severity' => 'HIGH',
                        'file' => $file,
                        'line' => $this->findLineNumber($content, $matches[0]),
                        'description' => 'Potential SQL injection vulnerability',
                        'code' => substr($matches[0], 0, 50) . '...'
                    ];
                }
            }
        }
        
        // Check for proper use of prepared statements
        $safePatterns = [
            '/prepare\(/i',
            '/bindValue\(/i',
            '/bindParam\(/i'
        ];
        
        $safeFiles = 0;
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $hasSafePattern = false;
            foreach ($safePatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    $hasSafePattern = true;
                    break;
                }
            }
            if ($hasSafePattern) $safeFiles++;
        }
        
        if ($safeFiles > 0) {
            $this->recommendations[] = "Found $safeFiles files using prepared statements - good practice!";
        }
        
        echo "✅ SQL injection scan completed\n\n";
    }
    
    /**
     * Check for XSS vulnerabilities
     */
    private function checkXssVulnerabilities() {
        echo "Checking for XSS vulnerabilities...\n";
        
        $vulnerablePatterns = [
            '/echo.*\$_GET/i',
            '/echo.*\$_POST/i',
            '/print.*\$_GET/i',
            '/print.*\$_POST/i',
            '/\$_GET.*echo/i',
            '/\$_POST.*echo/i'
        ];
        
        $files = $this->getAllPhpFiles();
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            foreach ($vulnerablePatterns as $pattern) {
                if (preg_match($pattern, $content, $matches)) {
                    // Check if output is properly escaped
                    if (!preg_match('/htmlspecialchars|htmlentities/', $content)) {
                        $this->issues[] = [
                            'type' => 'XSS_VULNERABILITY',
                            'severity' => 'HIGH',
                            'file' => $file,
                            'line' => $this->findLineNumber($content, $matches[0]),
                            'description' => 'Potential XSS vulnerability - unescaped output',
                            'code' => substr($matches[0], 0, 50) . '...'
                        ];
                    }
                }
            }
        }
        
        echo "✅ XSS scan completed\n\n";
    }
    
    /**
     * Check file permissions
     */
    private function checkFilePermissions() {
        echo "Checking file permissions...\n";
        
        $criticalFiles = [
            'config/Config.php',
            'api/auth.php',
            '.htaccess'
        ];
        
        foreach ($criticalFiles as $file) {
            if (file_exists($file)) {
                $perms = fileperms($file);
                $octal = substr(sprintf('%o', $perms), -4);
                
                if ($octal !== '0644' && $octal !== '0755') {
                    $this->issues[] = [
                        'type' => 'FILE_PERMISSIONS',
                        'severity' => 'MEDIUM',
                        'file' => $file,
                        'description' => 'Insecure file permissions',
                        'current_permissions' => $octal,
                        'recommended' => '0644 (files) or 0755 (directories)'
                    ];
                }
            }
        }
        
        echo "✅ File permissions check completed\n\n";
    }
    
    /**
     * Check error reporting settings
     */
    private function checkErrorReporting() {
        echo "Checking error reporting configuration...\n";
        
        $errorReporting = ini_get('error_reporting');
        $displayErrors = ini_get('display_errors');
        
        if ($displayErrors === '1') {
            $this->issues[] = [
                'type' => 'ERROR_REPORTING',
                'severity' => 'HIGH',
                'description' => 'Display errors is enabled - security risk in production',
                'current_setting' => 'display_errors = On',
                'recommended' => 'display_errors = Off'
            ];
        }
        
        if ($errorReporting === 'E_ALL') {
            $this->recommendations[] = 'Error reporting is set to E_ALL - good for development, ensure it\'s restricted in production';
        }
        
        echo "✅ Error reporting check completed\n\n";
    }
    
    /**
     * Check session security
     */
    private function checkSessionSecurity() {
        echo "Checking session security...\n";
        
        $sessionCookieParams = session_get_cookie_params();
        
        if ($sessionCookieParams['httponly'] !== true) {
            $this->issues[] = [
                'type' => 'SESSION_SECURITY',
                'severity' => 'MEDIUM',
                'description' => 'Session cookies are not HTTP-only',
                'recommended' => 'Set session.cookie_httponly = 1'
            ];
        }
        
        if ($sessionCookieParams['secure'] !== true) {
            $this->recommendations[] = 'Consider setting session.cookie_secure = 1 for HTTPS';
        }
        
        echo "✅ Session security check completed\n\n";
    }
    
    /**
     * Check input validation
     */
    private function checkInputValidation() {
        echo "Checking input validation...\n";
        
        $files = $this->getAllPhpFiles();
        $hasValidation = false;
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/filter_var|htmlspecialchars|preg_match/', $content)) {
                $hasValidation = true;
                break;
            }
        }
        
        if (!$hasValidation) {
            $this->issues[] = [
                'type' => 'INPUT_VALIDATION',
                'severity' => 'HIGH',
                'description' => 'No input validation found in codebase',
                'recommended' => 'Implement proper input validation using filter_var() or similar'
            ];
        } else {
            $this->recommendations[] = 'Input validation found in codebase - good practice!';
        }
        
        echo "✅ Input validation check completed\n\n";
    }
    
    /**
     * Check database security
     */
    private function checkDatabaseSecurity() {
        echo "Checking database security...\n";
        
        if (file_exists('config/Config.php')) {
            $content = file_get_contents('config/Config.php');
            
            if (preg_match('/root.*password.*=.*["\'].*["\']/', $content)) {
                $this->issues[] = [
                    'type' => 'DATABASE_SECURITY',
                    'severity' => 'HIGH',
                    'description' => 'Database credentials using root user',
                    'recommended' => 'Create dedicated database user with limited privileges'
                ];
            }
        }
        
        echo "✅ Database security check completed\n\n";
    }
    
    /**
     * Check CORS configuration
     */
    private function checkCorsConfiguration() {
        echo "Checking CORS configuration...\n";
        
        $files = $this->getAllPhpFiles();
        $hasCors = false;
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/Access-Control-Allow-Origin:\s*\*/', $content)) {
                $this->issues[] = [
                    'type' => 'CORS_SECURITY',
                    'severity' => 'MEDIUM',
                    'file' => $file,
                    'description' => 'CORS allows all origins (*)',
                    'recommended' => 'Restrict to specific domains in production'
                ];
                $hasCors = true;
            }
        }
        
        if (!$hasCors) {
            $this->recommendations[] = 'No CORS configuration found - consider implementing if needed';
        }
        
        echo "✅ CORS configuration check completed\n\n";
    }
    
    /**
     * Check for backup files
     */
    private function checkBackupFiles() {
        echo "Checking for backup files...\n";
        
        $backupPatterns = [
            '*.bak',
            '*.backup',
            '*.old',
            '*.orig',
            '*~'
        ];
        
        foreach ($backupPatterns as $pattern) {
            $files = glob($pattern);
            foreach ($files as $file) {
                $this->issues[] = [
                    'type' => 'BACKUP_FILES',
                    'severity' => 'MEDIUM',
                    'file' => $file,
                    'description' => 'Backup file exposed in web directory',
                    'recommended' => 'Move backup files outside web root or remove'
                ];
            }
        }
        
        echo "✅ Backup files check completed\n\n";
    }
    
    /**
     * Generate security report
     */
    private function generateReport() {
        echo "=== SECURITY AUDIT RESULTS ===\n\n";
        
        $highIssues = array_filter($this->issues, fn($i) => ($i['severity'] ?? 'MEDIUM') === 'HIGH');
        $mediumIssues = array_filter($this->issues, fn($i) => ($i['severity'] ?? 'MEDIUM') === 'MEDIUM');
        
        echo "SUMMARY:\n";
        echo "High Severity Issues: " . count($highIssues) . "\n";
        echo "Medium Severity Issues: " . count($mediumIssues) . "\n";
        echo "Recommendations: " . count($this->recommendations) . "\n\n";
        
        if (!empty($highIssues)) {
            echo "HIGH SEVERITY ISSUES:\n";
            foreach ($highIssues as $issue) {
                echo "- {$issue['type']}: {$issue['description']}\n";
                if (isset($issue['file'])) echo "  File: {$issue['file']}\n";
                if (isset($issue['recommended'])) echo "  Recommendation: {$issue['recommended']}\n";
                echo "\n";
            }
        }
        
        if (!empty($mediumIssues)) {
            echo "MEDIUM SEVERITY ISSUES:\n";
            foreach ($mediumIssues as $issue) {
                echo "- {$issue['type']}: {$issue['description']}\n";
                if (isset($issue['file'])) echo "  File: {$issue['file']}\n";
                if (isset($issue['recommended'])) echo "  Recommendation: {$issue['recommended']}\n";
                echo "\n";
            }
        }
        
        if (!empty($this->recommendations)) {
            echo "RECOMMENDATIONS:\n";
            foreach ($this->recommendations as $rec) {
                echo "- $rec\n";
            }
            echo "\n";
        }
        
        // Save detailed report
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'summary' => [
                'high_issues' => count($highIssues),
                'medium_issues' => count($mediumIssues),
                'recommendations' => count($this->recommendations)
            ],
            'issues' => $this->issues,
            'recommendations' => $this->recommendations
        ];
        
        file_put_contents('logs/security_audit_report.json', json_encode($report, JSON_PRETTY_PRINT));
        echo "Detailed report saved to: logs/security_audit_report.json\n";
    }
    
    /**
     * Get all PHP files recursively
     */
    private function getAllPhpFiles() {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('.'));
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    /**
     * Find line number of a pattern in content
     */
    private function findLineNumber($content, $pattern) {
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, $pattern) !== false) {
                return $lineNum + 1;
            }
        }
        return 0;
    }
}

// Run audit if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $audit = new SecurityAudit();
    $audit->runFullAudit();
}
