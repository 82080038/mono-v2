<?php
/**
 * Simple Structure Check
 * Check actual function structure without complex regex
 */

$content = file_get_contents(__DIR__ . '/main.php');

// Extract JavaScript part
$startPos = strpos($content, '<script>');
$endPos = strrpos($content, '</script>');
$jsContent = substr($content, $startPos, $endPos - $startPos + 9);

echo "=== SIMPLE STRUCTURE CHECK ===\n\n";

// Check generateLaporanContent specifically
echo "🔍 CHECKING generateLaporanContent:\n";

$findText = 'function generateLaporanContent';
$pos = strpos($jsContent, $findText);

if ($pos !== false) {
    echo "✅ Function found at position: {$pos}\n";
    
    // Get 500 characters after function start
    $snippet = substr($jsContent, $pos, 500);
    echo "📄 First 500 characters:\n";
    echo wordwrap($snippet, 80, "\n   ") . "\n\n";
    
    // Look for the return statement
    $returnPos = strpos($snippet, 'return `');
    if ($returnPos !== false) {
        echo "✅ Return statement found at position: {$returnPos}\n";
        
        // Get 100 characters after return
        $afterReturn = substr($snippet, $returnPos + 7, 100);
        echo "📄 100 characters after return:\n";
        echo wordwrap($afterReturn, 80, "\n   ") . "\n\n";
        
        // Look for closing backtick
        $closingPos = strpos($afterReturn, '`');
        if ($closingPos !== false) {
            echo "✅ Closing backtick found at position: {$closingPos}\n";
            
            $template = substr($afterReturn, 0, $closingPos);
            echo "📄 Template content (first 200 chars):\n";
            echo wordwrap(substr($template, 0, 200), 80, "\n   ") . "\n\n";
        } else {
            echo "❌ Closing backtick NOT found in first 100 chars\n";
            
            // Look further
            $moreContent = substr($jsContent, $pos + $returnPos + 7, 1000);
            $closingPos = strpos($moreContent, '`');
            if ($closingPos !== false) {
                echo "✅ Closing backtick found at position: {$closingPos} (in extended search)\n";
                
                $template = substr($moreContent, 0, $closingPos);
                echo "📄 Template content (first 200 chars):\n";
                echo wordwrap(substr($template, 0, 200), 80, "\n   ") . "\n\n";
            } else {
                echo "❌ Closing backtick NOT found in extended search either\n";
            }
        }
    } else {
        echo "❌ Return statement NOT found\n";
    }
} else {
    echo "❌ Function NOT found\n";
}

// Check overall structure
echo "🔍 OVERALL STRUCTURE CHECK:\n";

// Count all occurrences of key patterns
$patterns = [
    'function generateLaporanContent' => 'generateLaporanContent function',
    'return `' => 'Return with backtick',
    '`;' => 'Closing backtick with semicolon',
    '`\n' => 'Closing backtick with newline',
    '`\s*\n\s*}' => 'Closing backtick followed by closing brace'
];

foreach ($patterns as $pattern => $description) {
    $count = substr_count($jsContent, $pattern);
    echo "📊 {$description}: {$count}\n";
}

echo "\n🔍 LOOKING FOR SPECIFIC ISSUES:\n";

// Check for specific problematic patterns
$problemPatterns = [
    '/return `\s*$/' => 'Return with backtick at end of line',
    '/return `[^`]*$/' => 'Return with unclosed template literal',
    '/\$\{[^}]*$/' => 'Unclosed template expression'
];

foreach ($problemPatterns as $pattern => $description) {
    if (preg_match($pattern, $jsContent)) {
        echo "❌ {$description}: FOUND\n";
    } else {
        echo "✅ {$description}: Not found\n";
    }
}

echo "\n🔧 RECOMMENDATIONS:\n";
echo "1. Check the actual structure of generateLaporanContent\n";
echo "2. Verify template literal is properly closed\n";
echo "3. Ensure function has proper closing brace\n";
echo "4. Test in browser for actual runtime errors\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "🚀 SIMPLE STRUCTURE CHECK COMPLETE\n";
echo str_repeat("=", 60) . "\n";
?>
