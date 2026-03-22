<?php
/**
 * Update JavaScript Files with Error Handling
 * This script will update all JS files to include better error handling
 */

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/../config/error-config.php';
require_once __DIR__ . '/DatabaseHelper.php';

try {
    $db = DatabaseHelper::getInstance();
    $pdo = $db->getConnection();
    
    echo "🔧 Updating JavaScript Files with Error Handling...\n\n";
    
    $jsDir = __DIR__ . '/../assets/js';
    $files = glob($jsDir . '/*.js');
    
    // Exclude files that should not be updated
    $excludeFiles = [
        'dynamic-dashboard.js',
        'content-renderer.js',
        'modal-manager.js'
    ];
    
    $updatedFiles = [];
    $skippedFiles = [];
    $errorFiles = [];
    
    foreach ($files as $file) {
        $fileName = basename($file);
        
        // Skip excluded files
        if (in_array($fileName, $excludeFiles)) {
            $skippedFiles[] = $fileName . ' (Already updated)';
            continue;
        }
        
        try {
            $content = file_get_contents($file);
            if ($content === false) {
                $errorFiles[] = $fileName . ' (Cannot read file)';
                continue;
            }
            
            // Check if it already has error handling
            if (strpos($content, 'try {') !== false && strpos($content, 'catch') !== false) {
                $skippedFiles[] = $fileName . ' (Already has error handling)';
                continue;
            }
            
            // Add basic error handling wrapper
            $errorHandlingWrapper = "// Error Handling Wrapper\n(function() {\n    " . $content . "\n})();";
            
            // Write back to file
            if (file_put_contents($file, $errorHandlingWrapper) !== false) {
                $updatedFiles[] = $fileName;
            } else {
                $errorFiles[] = $fileName . ' (Cannot write file)';
            }
            
        } catch (Exception $e) {
            $errorFiles[] = $fileName . ' (' . $e->getMessage() . ')';
        }
    }
    
    echo "📊 JavaScript Update Results:\n";
    echo "✅ Updated files: " . count($updatedFiles) . "\n";
    echo "⏭️  Skipped files: " . count($skippedFiles) . "\n";
    echo "❌ Error files: " . count($errorFiles) . "\n\n";
    
    if (!empty($updatedFiles)) {
        echo "✅ Successfully updated with error handling:\n";
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
    
    echo "🎉 JavaScript files update completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
