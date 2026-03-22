<?php
/**
 * Direct File Analysis
 * Analyze main.php directly for template literal issues
 */

$content = file_get_contents(__DIR__ . '/main.php');

echo "=== DIRECT FILE ANALYSIS ===\n\n";

// Extract JavaScript part
$startPos = strpos($content, '<script>');
$endPos = strrpos($content, '</script>');
$jsContent = substr($content, $startPos, $endPos - $startPos + 9);

// Find all content generator functions
$functions = [
    'generateDashboardContent',
    'generateLaporanContent', 
    'generateNasabahContent',
    'generateTransaksiContent',
    'generatePinjamanContent',
    'generateSimpananContent',
    'generatePengaturanContent',
    'generateSetoranContent',
    'generatePenarikanContent',
    'generatePembayaranContent',
    'generateProfilContent'
];

echo "🔍 ANALYZING CONTENT GENERATOR FUNCTIONS:\n\n";

$issues = [];

foreach ($functions as $func) {
    echo "📝 Function: {$func}\n";
    
    // Find function content
    $pattern = "/function\s+{$func}\s*\([^)]*\)\s*\{(.*?)\n\s*\}/s";
    if (preg_match($pattern, $jsContent, $match)) {
        $functionContent = $match[1];
        
        // Check for template literal
        if (strpos($functionContent, 'return `') !== false) {
            echo "   ✅ Has template literal\n";
            
            // Find the template literal content
            $templateStart = strpos($functionContent, 'return `');
            $templateContent = substr($functionContent, $templateStart + 7);
            
            // Count backticks
            $backticks = substr_count($templateContent, '`');
            echo "   📊 Backticks in template: {$backticks}\n";
            
            if ($backticks % 2 !== 0) {
                echo "   ❌ UNBALANCED BACKTICKS\n";
                $issues[] = $func;
                
                // Find where the template ends
                $lines = explode("\n", $templateContent);
                $totalBackticks = 0;
                $problemLine = -1;
                
                foreach ($lines as $lineNum => $line) {
                    $lineBackticks = substr_count($line, '`');
                    $totalBackticks += $lineBackticks;
                    
                    if ($totalBackticks % 2 !== 0 && $lineBackticks > 0) {
                        $problemLine = $lineNum;
                        break;
                    }
                }
                
                if ($problemLine >= 0) {
                    echo "   📍 Problem around line: " . ($problemLine + 1) . "\n";
                    echo "   📄 Content: " . trim($lines[$problemLine]) . "\n";
                }
            } else {
                echo "   ✅ Backticks balanced\n";
            }
            
            // Check for function closure
            $closingBrace = strrpos($functionContent, '}');
            if ($closingBrace === false) {
                echo "   ❌ Missing closing brace\n";
                $issues[] = $func . " (missing brace)";
            } else {
                echo "   ✅ Has closing brace\n";
            }
            
        } else {
            echo "   ❌ No template literal found\n";
        }
    } else {
        echo "   ❌ Function not found\n";
        $issues[] = $func . " (not found)";
    }
    
    echo "\n";
}

echo "📊 SUMMARY:\n";
if (empty($issues)) {
    echo "✅ All content generators appear correct\n";
} else {
    echo "❌ Issues found in " . count($issues) . " functions:\n";
    foreach ($issues as $issue) {
        echo "   • {$issue}\n";
    }
}

// Check for overall JavaScript structure
echo "\n🔍 OVERALL JAVASCRIPT STRUCTURE:\n";

// Count all backticks
$totalBackticks = substr_count($jsContent, '`');
echo "Total backticks: {$totalBackticks}\n";

if ($totalBackticks % 2 !== 0) {
    echo "❌ Overall backtick count is odd\n";
} else {
    echo "✅ Overall backtick count is even\n";
}

// Count braces
$openBraces = substr_count($jsContent, '{');
$closeBraces = substr_count($jsContent, '}');
echo "Open braces: {$openBraces}\n";
echo "Close braces: {$closeBraces}\n";

if ($openBraces !== $closeBraces) {
    echo "❌ Brace count mismatch: " . ($openBraces - $closeBraces) . "\n";
} else {
    echo "✅ Braces balanced\n";
}

// Look for specific patterns that might cause issues
echo "\n🔍 CHECKING FOR PROBLEMATIC PATTERNS:\n";

$patterns = [
    '/return\s*`[^`]*$/s' => 'Unclosed template literal after return',
    '/\$\{[^}]*$/s' => 'Unclosed template expression',
    '/\{[^}]*$/s' => 'Unclosed brace',
    '/\([^)]*$/s' => 'Unclosed parenthesis'
];

foreach ($patterns as $pattern => $description) {
    if (preg_match($pattern, $jsContent)) {
        echo "❌ {$description}: FOUND\n";
    } else {
        echo "✅ {$description}: Not found\n";
    }
}

echo "\n🔧 RECOMMENDATIONS:\n";
if (empty($issues)) {
    echo "✅ No obvious issues found in content generators\n";
    echo "🔍 Check other parts of JavaScript code\n";
    echo "🔍 Test in actual browser for runtime errors\n";
} else {
    echo "❌ Fix the issues identified above\n";
    echo "🔧 Add missing closing backticks\n";
    echo "🔧 Ensure proper function closure\n";
    echo "🔧 Test each function individually\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🚀 DIRECT FILE ANALYSIS COMPLETE\n";
echo str_repeat("=", 60) . "\n";
?>
