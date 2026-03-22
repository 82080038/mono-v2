<?php
/**
 * Simple Template Literal Checker
 * Basic check for template literal issues
 */

$content = file_get_contents(__DIR__ . '/main.php');

// Extract JavaScript part
$startPos = strpos($content, '<script>');
$endPos = strrpos($content, '</script>');
$jsContent = substr($content, $startPos, $endPos - $startPos + 9);

echo "=== SIMPLE TEMPLATE LITERAL CHECKER ===\n\n";

// Count backticks
$backtickCount = substr_count($jsContent, '`');
echo "Total backticks: {$backtickCount}\n";

if ($backtickCount % 2 === 0) {
    echo "✅ Backticks appear balanced\n";
} else {
    echo "❌ Backticks are NOT balanced\n";
}

// Find all function definitions
preg_match_all('/function\s+(\w+)/', $jsContent, $matches);
$functions = $matches[1];

echo "\n🔍 CHECKING FUNCTIONS:\n";

$issues = [];
foreach ($functions as $function) {
    // Find function content
    $pattern = "/function\s+{$function}\s*\([^)]*\)\s*\{(.*?)\n\s*\}/s";
    if (preg_match($pattern, $jsContent, $match)) {
        $functionContent = $match[1];
        
        // Check if function has template literal
        if (strpos($functionContent, 'return `') !== false) {
            // Count backticks in this function
            $funcBackticks = substr_count($functionContent, '`');
            
            echo "📝 {$function}: {$funcBackticks} backticks\n";
            
            if ($funcBackticks % 2 !== 0) {
                $issues[] = $function;
                echo "   ❌ UNBALANCED\n";
            } else {
                echo "   ✅ Balanced\n";
            }
        }
    }
}

echo "\n📊 RESULTS:\n";

if (empty($issues)) {
    echo "✅ All template literals are balanced\n";
} else {
    echo "❌ Functions with issues: " . count($issues) . "\n";
    foreach ($issues as $issue) {
        echo "   • {$issue}\n";
    }
}

// Check for specific problematic patterns
echo "\n🔍 SPECIFIC PATTERNS:\n";

// Check for return ` without closing `
if (preg_match('/return\s*`[^`]*$/s', $jsContent)) {
    echo "❌ Found unclosed template literal after return\n";
} else {
    echo "✅ No unclosed template literals found\n";
}

// Check for ${} without closing }
if (preg_match('/\$\{[^}]*$/s', $jsContent)) {
    echo "❌ Found unclosed template expression\n";
} else {
    echo "✅ No unclosed template expressions found\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🚀 SIMPLE TEMPLATE LITERAL CHECK COMPLETE\n";
echo str_repeat("=", 50) . "\n";
?>
