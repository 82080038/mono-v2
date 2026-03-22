<?php
/**
 * Accurate Function Analysis
 * More precise analysis of function structure
 */

$content = file_get_contents(__DIR__ . '/main.php');

// Extract JavaScript part
$startPos = strpos($content, '<script>');
$endPos = strrpos($content, '</script>');
$jsContent = substr($content, $startPos, $endPos - $startPos + 9);

echo "=== ACCURATE FUNCTION ANALYSIS ===\n\n";

// Find all function definitions with better regex
preg_match_all('/function\s+(\w+)\s*\([^)]*\)\s*\{/', $jsContent, $matches);
$functions = $matches[1];

echo "🔍 FOUND " . count($functions) . " FUNCTIONS:\n";

$issues = [];

foreach ($functions as $index => $func) {
    echo ($index + 1) . ". {$func}\n";
    
    // Find the function start position
    $pattern = "/function\s+{$func}\s*\([^)]*\)\s*\{/";
    if (preg_match($pattern, $jsContent, $match, PREG_OFFSET_CAPTURE)) {
        $startPos = $match[0][1];
        
        // Find the matching closing brace
        $braceCount = 0;
        $inFunction = false;
        $endPos = $startPos;
        
        for ($i = $startPos; $i < strlen($jsContent); $i++) {
            $char = $jsContent[$i];
            
            if ($char === '{') {
                if (!$inFunction) {
                    $inFunction = true;
                }
                $braceCount++;
            } elseif ($char === '}') {
                $braceCount--;
                if ($braceCount === 0 && $inFunction) {
                    $endPos = $i;
                    break;
                }
            }
        }
        
        // Extract function content
        $functionContent = substr($jsContent, $startPos, $endPos - $startPos + 1);
        
        // Check for template literal
        if (strpos($functionContent, 'return `') !== false) {
            echo "   ✅ Has template literal\n";
            
            // Count backticks in function
            $backticks = substr_count($functionContent, '`');
            echo "   📊 Total backticks: {$backticks}\n";
            
            if ($backticks % 2 !== 0) {
                echo "   ❌ UNBALANCED BACKTICKS\n";
                $issues[] = $func . " (unbalanced backticks)";
            } else {
                echo "   ✅ Backticks balanced\n";
            }
        } else {
            echo "   ⚠️  No template literal\n";
        }
        
        // Check if function is properly closed
        if ($braceCount === 0) {
            echo "   ✅ Properly closed\n";
        } else {
            echo "   ❌ NOT properly closed (brace count: {$braceCount})\n";
            $issues[] = $func . " (not closed)";
        }
        
        // Check for semicolon after function
        $nextChar = substr($jsContent, $endPos + 1, 1);
        if ($nextChar === ';') {
            echo "   ✅ Has semicolon\n";
        } else {
            echo "   ⚠️  Missing semicolon\n";
        }
        
    } else {
        echo "   ❌ Pattern not found\n";
        $issues[] = $func . " (pattern error)";
    }
    
    echo "\n";
}

echo "📊 SUMMARY:\n";
if (empty($issues)) {
    echo "✅ All functions appear to be properly structured\n";
} else {
    echo "❌ Issues found in " . count($issues) . " functions:\n";
    foreach ($issues as $issue) {
        echo "   • {$issue}\n";
    }
}

// Let's specifically check the functions that were reported as problematic
echo "\n🔍 SPECIFIC CHECK FOR REPORTED FUNCTIONS:\n";

$problematicFunctions = [
    'generateLaporanContent',
    'generateNasabahContent', 
    'generateTransaksiContent'
];

foreach ($problematicFunctions as $func) {
    echo "\n📝 Checking: {$func}\n";
    
    // Find function with a more specific pattern
    $pattern = "/function\s+{$func}\s*\([^)]*\)\s*\{(.*?)\n\s*\}/s";
    if (preg_match($pattern, $jsContent, $match)) {
        $functionContent = $match[1];
        
        echo "   ✅ Function extracted successfully\n";
        echo "   📏 Length: " . strlen($functionContent) . " characters\n";
        
        // Check for return statement
        if (strpos($functionContent, 'return `') !== false) {
            echo "   ✅ Has return with template literal\n";
            
            // Find the template
            $returnPos = strpos($functionContent, 'return `');
            $templateStart = $returnPos + 7;
            $templateContent = substr($functionContent, $templateStart);
            
            // Find closing backtick
            $closingBacktick = strpos($templateContent, '`');
            if ($closingBacktick !== false) {
                echo "   ✅ Closing backtick found at position {$closingBacktick}\n";
                
                $template = substr($templateContent, 0, $closingBacktick);
                echo "   📏 Template length: " . strlen($template) . " characters\n";
                
                // Check template content
                if (strlen($template) > 0) {
                    echo "   ✅ Template has content\n";
                } else {
                    echo "   ❌ Template is empty\n";
                }
            } else {
                echo "   ❌ Closing backtick NOT found\n";
            }
        } else {
            echo "   ❌ No return with template literal found\n";
        }
        
    } else {
        echo "   ❌ Function extraction failed\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🚀 ACCURATE FUNCTION ANALYSIS COMPLETE\n";
echo str_repeat("=", 60) . "\n";
?>
