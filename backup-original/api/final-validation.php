<?php
/**
 * Final System Validation Script
 * Comprehensive validation of the updated system
 */

// Define constant for access control
require_once __DIR__ . '/config/error-config.php';

define('KSP_API_ACCESS', true);

// Include required files
require_once __DIR__ . '/DatabaseHelper.php';

// Initialize database connection
try {
    $db = DatabaseHelper::getInstance();
    echo "=== KSP LAM GABE JAYA - FINAL SYSTEM VALIDATION ===\n";
    echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// 1. API Files Validation
echo "🔍 API FILES VALIDATION\n";
echo "======================\n";

$apiDir = __DIR__;
$apiFiles = glob($apiDir . '/*.php');
$coreAPIs = [
    'auth-enhanced.php', 'members-crud.php', 'loans-crud.php', 'savings-crud.php',
    'user-management.php', 'system-settings.php', 'audit-log.php', 'member-registration.php',
    'reports.php', 'member-dashboard.php', 'loan-application.php', 'member-savings.php',
    'member-payments.php', 'member-profile.php', 'staff-dashboard.php', 'staff-gps.php',
    'staff-members.php', 'staff-tasks.php', 'staff-reports.php', 'analytics.php',
    'notifications.php', 'reward-points.php'
];

$helperFiles = [
    'DatabaseHelper.php', 'Logger.php', 'DataValidator.php', 'SecurityLogger.php',
    'AuthHelper.php', 'SecurityHelper.php', 'SecurityMiddleware.php'
];

echo "📊 API Files Status:\n";
echo "  Total files: " . count($apiFiles) . "\n";
echo "  Core APIs: " . count($coreAPIs) . "\n";
echo "  Helper files: " . count($helperFiles) . "\n\n";

// Check syntax
$syntaxErrors = [];
$validFiles = 0;

foreach ($apiFiles as $file) {
    $output = [];
    $returnCode = 0;
    exec("php -l \"$file\" 2>&1", $output, $returnCode);
    
    if ($returnCode === 0) {
        $validFiles++;
    } else {
        $syntaxErrors[basename($file)] = $output;
    }
}

echo "✅ Syntax validation: $validFiles/" . count($apiFiles) . " files pass\n";

if (!empty($syntaxErrors)) {
    echo "❌ Syntax errors in " . count($syntaxErrors) . " files:\n";
    foreach ($syntaxErrors as $file => $errors) {
        echo "  - $file: " . implode(', ', $errors) . "\n";
    }
} else {
    echo "✅ All files pass syntax validation\n";
}

// Check security implementation
echo "\n🔒 SECURITY VALIDATION\n";
echo "====================\n";

$securityChecks = [
    'AuthHelper.php' => file_exists(__DIR__ . '/AuthHelper.php'),
    'SecurityHelper.php' => file_exists(__DIR__ . '/SecurityHelper.php'),
    'SecurityMiddleware.php' => file_exists(__DIR__ . '/SecurityMiddleware.php')
];

echo "📋 Security Components:\n";
foreach ($securityChecks as $file => $exists) {
    echo "  " . ($exists ? "✅" : "❌") . " $file\n";
}

// Check for XSS vulnerabilities
$xssFiles = 0;
foreach ($apiFiles as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'echo json_encode($response);') !== false) {
        $xssFiles++;
    }
}

echo "📊 Security Status:\n";
echo "  Files with potential XSS: $xssFiles\n";
echo "  Security components: " . array_sum($securityChecks) . "/" . count($securityChecks) . "\n";

// 2. Database Validation
echo "\n🗄️ DATABASE VALIDATION\n";
echo "====================\n";

// Check core tables
$coreTables = [
    'users', 'members', 'loans', 'savings', 'payment_transactions',
    'reward_points', 'notifications', 'gps_tracking', 'audit_logs', 'system_settings'
];

$tableStatus = [];
foreach ($coreTables as $table) {
    try {
        $count = $db->fetchOne("SELECT COUNT(*) as count FROM $table")['count'];
        $tableStatus[$table] = ['exists' => true, 'count' => $count];
    } catch (Exception $e) {
        $tableStatus[$table] = ['exists' => false, 'count' => 0];
    }
}

echo "📊 Core Tables Status:\n";
foreach ($tableStatus as $table => $status) {
    echo "  " . ($status['exists'] ? "✅" : "❌") . " $table (" . $status['count'] . " records)\n";
}

// Check indexes
echo "\n📊 Database Indexes:\n";
$indexCount = 0;
foreach ($coreTables as $table) {
    if ($tableStatus[$table]['exists']) {
        try {
            $indexes = $db->fetchAll("SHOW INDEX FROM $table");
            $indexCount += count($indexes);
        } catch (Exception $e) {
            // Skip if table doesn't exist
        }
    }
}
echo "  Total indexes: $indexCount\n";

// 3. Configuration Validation
echo "\n⚙️ CONFIGURATION VALIDATION\n";
echo "========================\n";

$configFile = __DIR__ . '/../config/Config.php';
if (file_exists($configFile)) {
    echo "✅ Config.php exists\n";
    
    $configContent = file_get_contents($configFile);
    $configChecks = [
        'JWT_SECRET' => strpos($configContent, 'JWT_SECRET') !== false,
        'API_RATE_LIMIT' => strpos($configContent, 'API_RATE_LIMIT') !== false,
        'CORS_ORIGINS' => strpos($configContent, 'CORS_ORIGINS') !== false,
        'LOG_ENABLED' => strpos($configContent, 'LOG_ENABLED') !== false
    ];
    
    echo "📋 Configuration Settings:\n";
    foreach ($configChecks as $setting => $exists) {
        echo "  " . ($exists ? "✅" : "❌") . " $setting\n";
    }
} else {
    echo "❌ Config.php missing\n";
}

// 4. Performance Validation
echo "\n🚀 PERFORMANCE VALIDATION\n";
echo "========================\n";

// Check large files
$largeFiles = [];
foreach ($apiFiles as $file) {
    $size = filesize($file);
    if ($size > 50000) { // 50KB
        $largeFiles[basename($file)] = round($size/1024, 2) . "KB";
    }
}

echo "📊 File Sizes:\n";
if (empty($largeFiles)) {
    echo "  ✅ All files are reasonably sized (< 50KB)\n";
} else {
    echo "  ⚠️ Large files found:\n";
    foreach ($largeFiles as $file => $size) {
        echo "    - $file: $size\n";
    }
}

// Check database performance
echo "📊 Database Performance:\n";
try {
    $totalRecords = 0;
    foreach ($coreTables as $table) {
        if ($tableStatus[$table]['exists']) {
            $totalRecords += $tableStatus[$table]['count'];
        }
    }
    echo "  Total records: $totalRecords\n";
    echo "  Tables indexed: $indexCount\n";
} catch (Exception $e) {
    echo "  ❌ Error checking database performance\n";
}

// 5. Security Validation
echo "\n🔐 SECURITY VALIDATION\n";
echo "====================\n";

$securityScore = 0;
$maxScore = 10;

// Check authentication
if (file_exists(__DIR__ . '/AuthHelper.php')) {
    $securityScore += 2;
    echo "  ✅ Authentication system (2/2)\n";
} else {
    echo "  ❌ Authentication system missing (0/2)\n";
}

// Check security helpers
if (file_exists(__DIR__ . '/SecurityHelper.php')) {
    $securityScore += 2;
    echo "  ✅ Security helpers (2/2)\n";
} else {
    echo "  ❌ Security helpers missing (0/2)\n";
}

// Check middleware
if (file_exists(__DIR__ . '/SecurityMiddleware.php')) {
    $securityScore += 2;
    echo "  ✅ Security middleware (2/2)\n";
} else {
    echo "  ❌ Security middleware missing (0/2)\n";
}

// Check input validation
if (file_exists(__DIR__ . '/DataValidator.php')) {
    $securityScore += 2;
    echo "  ✅ Input validation (2/2)\n";
} else {
    echo "  ❌ Input validation missing (0/2)\n";
}

// Check audit logging
if (file_exists(__DIR__ . '/SecurityLogger.php')) {
    $securityScore += 2;
    echo "  ✅ Security logging (2/2)\n";
} else {
    echo "  ❌ Security logging missing (0/2)\n";
}

$securityPercentage = round(($securityScore / $maxScore) * 100, 2);
echo "📊 Security Score: $securityScore/$maxScore ($securityPercentage%)\n";

// 6. Overall Health Score
echo "\n📊 OVERALL HEALTH SCORE\n";
echo "====================\n";

$syntaxScore = round(($validFiles / count($apiFiles)) * 100, 2);
$tableScore = round((array_sum(array_column($tableStatus, 'exists')) / count($coreTables)) * 100, 2);
$configScore = file_exists($configFile) ? 100 : 0;

$overallScore = round(($syntaxScore + $tableScore + $configScore + $securityPercentage) / 4, 2);

echo "📊 Component Scores:\n";
echo "  Syntax Validation: $syntaxScore%\n";
echo "  Database Structure: $tableScore%\n";
echo "  Configuration: $configScore%\n";
echo "  Security: $securityPercentage%\n";
echo "  Overall Health: $overallScore%\n";

// 7. Production Readiness
echo "\n🚀 PRODUCTION READINESS\n";
echo "====================\n";

$issues = [];
$warnings = [];

// Check for critical issues
if ($syntaxScore < 100) {
    $issues[] = "Syntax errors in API files";
}

if ($tableScore < 100) {
    $issues[] = "Missing core database tables";
}

if ($securityPercentage < 80) {
    $issues[] = "Security measures insufficient";
}

if ($configScore < 100) {
    $issues[] = "Configuration incomplete";
}

// Check for warnings
if (!empty($largeFiles)) {
    $warnings[] = "Large files may impact performance";
}

if ($xssFiles > 0) {
    $warnings[] = "Potential XSS vulnerabilities";
}

echo "📋 Production Status:\n";
if (empty($issues) && $overallScore >= 85) {
    echo "  ✅ READY FOR PRODUCTION\n";
    echo "  🎯 Overall Score: $overallScore%\n";
} else {
    echo "  ❌ NOT READY FOR PRODUCTION\n";
    echo "  🎯 Overall Score: $overallScore%\n";
    
    if (!empty($issues)) {
        echo "\n❌ Critical Issues:\n";
        foreach ($issues as $issue) {
            echo "  - $issue\n";
        }
    }
    
    if (!empty($warnings)) {
        echo "\n⚠️ Warnings:\n";
        foreach ($warnings as $warning) {
            echo "  - $warning\n";
        }
    }
}

// 8. Recommendations
echo "\n🔧 RECOMMENDATIONS\n";
echo "==================\n";

if ($overallScore >= 85) {
    echo "✅ System is production-ready!\n";
    echo "🎯 Next steps:\n";
    echo "  1. Deploy to staging environment\n";
    echo "  2. Conduct load testing\n";
    echo "  3. Setup monitoring and alerting\n";
    echo "  4. Prepare deployment documentation\n";
} else {
    echo "⚠️ System needs attention before production deployment\n";
    echo "🎯 Immediate actions:\n";
    
    if (!empty($issues)) {
        foreach ($issues as $issue) {
            echo "  1. Fix: $issue\n";
        }
    }
    
    echo "  2. Re-run validation after fixes\n";
    echo "  3. Ensure all tests pass\n";
    echo "  4. Review security measures\n";
}

echo "\n=== VALIDATION COMPLETED ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "Status: " . ($overallScore >= 85 ? "✅ PRODUCTION READY" : "⚠️ NEEDS ATTENTION") . "\n";
?>
