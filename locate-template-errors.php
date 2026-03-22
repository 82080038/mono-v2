<?php
/**
 * Template Literal Error Locator
 * Finds specific locations of template literal issues
 */

$content = file_get_contents(__DIR__ . '/main.php');

// Extract JavaScript part
$startPos = strpos($content, '<script>');
$endPos = strrpos($content, '</script>');
$jsContent = substr($content, $startPos, $endPos - $startPos + 9);

echo "=== TEMPLATE LITERAL ERROR LOCATOR ===\n\n";

// Find all function definitions with template literals
preg_match_all('/function\s+(\w+)\([^)]*\)\s*\{[^}]*return\s*`([^`]*(?:`[^`]*)*`/s', $jsContent, $matches);

echo "🔍 ANALYZING FUNCTIONS WITH TEMPLATE LITERALS:\n";

$issues = [];
foreach ($matches[1] as $index => $functionName) {
    $functionContent = $matches[0][$index];
    $templateContent = $matches[2][$index];
    
    echo "\n📝 Function: {$functionName}\n";
    
    // Count backticks in template
    $backtickCount = substr_count($templateContent, '`');
    echo "   Template backticks: {$backtickCount}\n";
    
    // Check for nested backticks
    $nestedBackticks = 0;
    $inTemplate = false;
    $depth = 0;
    
    for ($i = 0; $i < strlen($templateContent); $i++) {
        $char = $templateContent[$i];
        if ($char === '`') {
            if ($i === 0 || $templateContent[$i-1] !== '\\') {
                $nestedBackticks++;
                $inTemplate = !$inTemplate;
                if ($inTemplate) $depth++;
            }
        }
    }
    
    echo "   Nested backticks: {$nestedBackticks}\n";
    echo "   Depth: {$depth}\n";
    
    // Check for common template literal issues
    $hasUnclosedBraces = false;
    $hasUnclosedParens = false;
    $hasUnclosedBrackets = false;
    
    // Count braces within template
    $openBraces = substr_count($templateContent, '{');
    $closeBraces = substr_count($templateContent, '}');
    if ($openBraces !== $closeBraces) {
        $hasUnclosedBraces = true;
        echo "   ❌ Unclosed braces in template: {$openBraces} vs {$closeBraces}\n";
    }
    
    // Count parentheses within template
    $openParens = substr_count($templateContent, '(');
    $closeParens = substr_count($templateContent, ')');
    if ($openParens !== $closeParens) {
        $hasUnclosedParens = true;
        echo "   ❌ Unclosed parentheses in template: {$openParens} vs {$closeParens}\n";
    }
    
    // Count brackets within template
    $openBrackets = substr_count($templateContent, '[');
    $closeBrackets = substr_count($templateContent, ']');
    if ($openBrackets !== $closeBrackets) {
        $hasUnclosedBrackets = true;
        echo "   ❌ Unclosed brackets in template: {$openBrackets} vs {$closeBrackets}\n";
    }
    
    // Check for JavaScript expressions within template
    $hasExpressions = preg_match('/\$\{[^}]*\}/', $templateContent);
    if ($hasExpressions) {
        echo "   ✅ Has template expressions\n";
    }
    
    // Check for potential issues
    if ($hasUnclosedBraces || $hasUnclosedParens || $hasUnclosedBrackets) {
        $issues[] = [
            'function' => $functionName,
            'braces' => $openBraces - $closeBraces,
            'parens' => $openParens - $closeParens,
            'brackets' => $openBrackets - $closeBrackets
        ];
        echo "   ❌ ISSUES DETECTED\n";
    } else {
        echo "   ✅ Template appears balanced\n";
    }
    
    // Show first few lines of template for context
    $templateLines = explode("\n", $templateContent);
    echo "   Preview (first 3 lines):\n";
    for ($i = 0; $i < min(3, count($templateLines)); $i++) {
        echo "     " . trim($templateLines[$i]) . "\n";
    }
}

echo "\n📊 SUMMARY OF ISSUES:\n";

if (empty($issues)) {
    echo "✅ No template literal issues found\n";
    echo "🚀 All templates appear to be syntactically correct\n";
} else {
    echo "❌ Found " . count($issues) . " functions with template issues:\n";
    
    foreach ($issues as $issue) {
        echo "   • {$issue['function']}: ";
        $problems = [];
        if ($issue['braces'] !== 0) $problems[] = "braces ({$issue['braces']})";
        if ($issue['parens'] !== 0) $problems[] = "parens ({$issue['parens']})";
        if ($issue['brackets'] !== 0) $problems[] = "brackets ({$issue['brackets']})";
        echo implode(", ", $problems) . "\n";
    }
    
    echo "\n🔧 RECOMMENDATIONS:\n";
    echo "1. Add missing closing braces/parentheses/brackets\n";
    echo "2. Check for escaped backticks within templates\n";
    echo "3. Verify template expressions are properly closed\n";
    echo "4. Test each function individually\n";
}

echo "\n🔍 LOOKING FOR SPECIFIC PATTERNS:\n";

// Check for common problematic patterns
$patterns = [
    '/\$\{[^}]*$/s' => 'Unclosed template expression',
    '/`[^`]*$/s' => 'Unclosed template literal',
    '/\{[^}]*$/s' => 'Unclosed brace',
    '/\([^)]*$/s' => 'Unclosed parenthesis',
    '/\[[^\]]*$/s' => 'Unclosed bracket'
];

foreach ($patterns as $pattern => $description) {
    if (preg_match($pattern, $jsContent)) {
        echo "❌ {$description}: Found\n";
    } else {
        echo "✅ {$description}: Not found\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🚀 TEMPLATE LITERAL ERROR LOCATOR COMPLETE\n";
echo str_repeat("=", 60) . "\n";
?>
