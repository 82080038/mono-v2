<?php
/**
 * Targeted Template Literal Debug
 * Find exact location of template literal issues
 */

// Test specific pages that are failing
$testPages = ['dashboard', 'laporan', 'nasabah'];

foreach ($testPages as $page) {
    echo "=== TESTING PAGE: {$page} ===\n";
    
    // Simulate login and test page
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/mono-v2/api/auth.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'action=login&username=bos&password=bos');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, "cookies_{$page}.txt");
    curl_exec($ch);
    curl_close($ch);
    
    // Get page content
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost/mono-v2/?page={$page}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies_{$page}.txt");
    $response = curl_exec($ch);
    curl_close($ch);
    
    // Extract JavaScript part
    $startPos = strpos($response, '<script>');
    $endPos = strpos($response, '</script>');
    if ($startPos !== false && $endPos !== false) {
        $jsContent = substr($response, $startPos, $endPos - $startPos + 9);
        
        // Find the specific content generator for this page
        $generatorFunction = 'generate' . ucfirst($page) . 'Content';
        
        echo "🔍 Looking for function: {$generatorFunction}\n";
        
        if (strpos($jsContent, $generatorFunction) !== false) {
            echo "✅ Function found\n";
            
            // Extract the function
            $pattern = "/function\s+{$generatorFunction}\s*\([^)]*\)\s*\{(.*?)\n\s*\}/s";
            if (preg_match($pattern, $jsContent, $match)) {
                $functionContent = $match[1];
                
                // Check for template literal
                if (strpos($functionContent, 'return `') !== false) {
                    echo "✅ Has template literal\n";
                    
                    // Count backticks
                    $backticks = substr_count($functionContent, '`');
                    echo "📊 Backticks: {$backticks}\n";
                    
                    if ($backticks % 2 !== 0) {
                        echo "❌ UNBALANCED BACKTICKS\n";
                        
                        // Find the problematic line
                        $lines = explode("\n", $functionContent);
                        $inTemplate = false;
                        $templateStart = 0;
                        
                        foreach ($lines as $lineNum => $line) {
                            if (strpos($line, 'return `') !== false) {
                                $templateStart = $lineNum;
                                $inTemplate = true;
                                echo "📍 Template starts at line " . ($lineNum + 1) . "\n";
                            }
                            
                            if ($inTemplate && substr_count($line, '`') > 0) {
                                $backticksInLine = substr_count($line, '`');
                                if ($backticksInLine % 2 !== 0) {
                                    echo "❌ Problematic line: " . ($lineNum + 1) . "\n";
                                    echo "   Content: " . trim($line) . "\n";
                                }
                            }
                        }
                    } else {
                        echo "✅ Backticks balanced\n";
                    }
                } else {
                    echo "❌ No template literal found\n";
                }
            } else {
                echo "❌ Could not extract function\n";
            }
        } else {
            echo "❌ Function NOT found\n";
        }
    } else {
        echo "❌ No JavaScript found\n";
    }
    
    echo "\n";
}

echo "=== SUMMARY ===\n";
echo "🔧 Check the problematic lines identified above\n";
echo "🔧 Look for missing closing backticks\n";
echo "🔧 Ensure proper function closure\n";

// Clean up
foreach ($testPages as $page) {
    if (file_exists("cookies_{$page}.txt")) {
        unlink("cookies_{$page}.txt");
    }
}
?>
