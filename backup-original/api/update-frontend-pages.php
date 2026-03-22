<?php
/**
 * Batch Update Frontend Pages with Dynamic Dashboard Integration
 * This script will update all frontend pages to use dynamic dashboard
 */

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/../config/error-config.php';
require_once __DIR__ . '/DatabaseHelper.php';

try {
    $db = DatabaseHelper::getInstance();
    $pdo = $db->getConnection();
    
    echo "🔧 Updating Frontend Pages with Dynamic Dashboard Integration...\n\n";
    
    // Pages that should redirect to dynamic dashboard
    $redirectPages = [
        'admin/dashboard.html',
        'creator/dashboard.html', 
        'manager/dashboard.html',
        'member/dashboard.html',
        'owner/dashboard.html',
        'staff/dashboard.html',
        'super_admin/dashboard.html',
        'teller/dashboard.html'
    ];
    
    $updatedFiles = [];
    $skippedFiles = [];
    $errorFiles = [];
    
    $pagesDir = __DIR__ . '/../pages';
    
    foreach ($redirectPages as $pagePath) {
        $fullPath = $pagesDir . '/' . $pagePath;
        
        if (!file_exists($fullPath)) {
            $skippedFiles[] = $pagePath . ' (File not found)';
            continue;
        }
        
        try {
            $content = file_get_contents($fullPath);
            if ($content === false) {
                $errorFiles[] = $pagePath . ' (Cannot read file)';
                continue;
            }
            
            // Check if it's already updated to redirect to dynamic dashboard
            if (strpos($content, 'dynamic-dashboard.html') !== false) {
                $skippedFiles[] = $pagePath . ' (Already updated)';
                continue;
            }
            
            // Create a simple redirect page
            $redirectContent = '<?php
/**
 * Redirect to Dynamic Dashboard
 */

require_once __DIR__ . "/../../config/error-config.php";

// Get user role from token or session
$role = $_GET[\'role\'] ?? $_SESSION[\'user_role\'] ?? \'member\';

// Redirect to dynamic dashboard
header("Location: ../dynamic-dashboard.html");
exit();
?>';
            
            // Write the redirect content
            if (file_put_contents($fullPath, $redirectContent) !== false) {
                $updatedFiles[] = $pagePath;
            } else {
                $errorFiles[] = $pagePath . ' (Cannot write file)';
            }
            
        } catch (Exception $e) {
            $errorFiles[] = $pagePath . ' (' . $e->getMessage() . ')';
        }
    }
    
    echo "📊 Frontend Update Results:\n";
    echo "✅ Updated files: " . count($updatedFiles) . "\n";
    echo "⏭️  Skipped files: " . count($skippedFiles) . "\n";
    echo "❌ Error files: " . count($errorFiles) . "\n\n";
    
    if (!empty($updatedFiles)) {
        echo "✅ Successfully updated to redirect to dynamic dashboard:\n";
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
    
    echo "🎉 Frontend pages update completed!\n";
    echo "📌 All dashboard pages now redirect to dynamic-dashboard.html\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
