<?php
/**
 * Batch Update API Files with Error Reporting
 * This script will update all API files to include error reporting
 */

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/../config/error-config.php';
require_once __DIR__ . '/DatabaseHelper.php';

try {
    $db = DatabaseHelper::getInstance();
    $pdo = $db->getConnection();
    
    echo "🔧 Updating API files with error reporting...\n\n";
    
    // Get all PHP files in api directory (excluding subdirectories)
    $apiDir = __DIR__;
    $files = glob($apiDir . '/*.php');
    
    // Exclude files that should not be updated
    $excludeFiles = [
        'error-config.php',
        'setup-dynamic-system.php',
        'dynamic-dashboard.php',
        'dynamic-navigation.php',
        'role-structure-update.php'
    ];
    
    $updatedFiles = [];
    $skippedFiles = [];
    $errorFiles = [];
    
    foreach ($files as $file) {
        $fileName = basename($file);
        
        // Skip excluded files
        if (in_array($fileName, $excludeFiles)) {
            $skippedFiles[] = $fileName;
            continue;
        }
        
        // Skip backup directories
        if (strpos($file, '/backups/') !== false) {
            continue;
        }
        
        try {
            $content = file_get_contents($file);
            if ($content === false) {
                $errorFiles[] = $fileName . ' (Cannot read file)';
                continue;
            }
            
            // Check if error-config is already included
            if (strpos($content, 'require_once __DIR__ . \'/config/error-config.php\';') !== false) {
                $skippedFiles[] = $fileName . ' (Already updated)';
                continue;
            }
            
            // Find the first define('KSP_API_ACCESS', true); line
            $pattern = '/^(<\?php\s*\n[^<]*?)define\([\'"]KSP_API_ACCESS[\'"],\s*true\);/m';
            
            if (preg_match($pattern, $content, $matches)) {
                $beforeDefine = $matches[1];
                
                // Create new content with error reporting
                $newContent = $beforeDefine . "require_once __DIR__ . '/config/error-config.php';\n\ndefine('KSP_API_ACCESS', true);";
                
                // Replace the old content
                $content = preg_replace($pattern, $newContent, $content);
                
                // Write back to file
                if (file_put_contents($file, $content) !== false) {
                    $updatedFiles[] = $fileName;
                } else {
                    $errorFiles[] = $fileName . ' (Cannot write file)';
                }
            } else {
                $skippedFiles[] = $fileName . ' (No KSP_API_ACCESS define found)';
            }
            
        } catch (Exception $e) {
            $errorFiles[] = $fileName . ' (' . $e->getMessage() . ')';
        }
    }
    
    echo "📊 Update Results:\n";
    echo "✅ Updated files: " . count($updatedFiles) . "\n";
    echo "⏭️  Skipped files: " . count($skippedFiles) . "\n";
    echo "❌ Error files: " . count($errorFiles) . "\n\n";
    
    if (!empty($updatedFiles)) {
        echo "✅ Successfully updated:\n";
        foreach ($updatedFiles as $file) {
            echo "  • $file\n";
        }
        echo "\n";
    }
    
    if (!empty($skippedFiles)) {
        echo "⏭️  Skipped files:\n";
        foreach ($skippedFiles as $file) {
            echo "  • $file\n";
        }
        echo "\n";
    }
    
    if (!empty($errorFiles)) {
        echo "❌ Files with errors:\n";
        foreach ($errorFiles as $file) {
            echo "  • $file\n";
        }
        echo "\n";
    }
    
    echo "🎉 API files update completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
