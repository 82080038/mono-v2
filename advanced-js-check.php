<?php
/**
 * Advanced JavaScript Syntax Checker
 * Checks for various JavaScript syntax issues
 */

$content = file_get_contents(__DIR__ . '/main.php');

// Extract JavaScript part
$startPos = strpos($content, '<script>');
$endPos = strrpos($content, '</script>');
$jsContent = substr($content, $startPos, $endPos - $startPos + 9);

echo "=== ADVANCED JAVASCRIPT SYNTAX CHECKER ===\n\n";

// Remove HTML tags to get pure JavaScript
$jsOnly = preg_replace('/<[^>]*>/', '', $jsContent);

// 1. Check for unclosed braces
echo "🔍 CHECKING BRACES:\n";
$openBraces = substr_count($jsOnly, '{');
$closeBraces = substr_count($jsOnly, '}');

echo "Open braces: {$openBraces}\n";
echo "Close braces: {$closeBraces}\n";

if ($openBraces === $closeBraces) {
    echo "✅ Braces are balanced\n";
} else {
    echo "❌ Braces are NOT balanced (difference: " . ($openBraces - $closeBraces) . ")\n";
}

// 2. Check for unclosed parentheses
echo "\n🔍 CHECKING PARENTHESES:\n";
$openParens = substr_count($jsOnly, '(');
$closeParens = substr_count($jsOnly, ')');

echo "Open parentheses: {$openParens}\n";
echo "Close parentheses: {$closeParens}\n";

if ($openParens === $closeParens) {
    echo "✅ Parentheses are balanced\n";
} else {
    echo "❌ Parentheses are NOT balanced (difference: " . ($openParens - $closeParens) . ")\n";
}

// 3. Check for unclosed brackets
echo "\n🔍 CHECKING BRACKETS:\n";
$openBrackets = substr_count($jsOnly, '[');
$closeBrackets = substr_count($jsOnly, ']');

echo "Open brackets: {$openBrackets}\n";
echo "Close brackets: {$closeBrackets}\n";

if ($openBrackets === $closeBrackets) {
    echo "✅ Brackets are balanced\n";
} else {
    echo "❌ Brackets are NOT balanced (difference: " . ($openBrackets - $closeBrackets) . ")\n";
}

// 4. Check for template literals
echo "\n🔍 CHECKING TEMPLATE LITERALS:\n";
$templateLiterals = substr_count($jsOnly, '`');
echo "Template literal backticks: {$templateLiterals}\n";

if ($templateLiterals % 2 === 0) {
    echo "✅ Template literals appear balanced\n";
} else {
    echo "❌ Template literals may be unbalanced\n";
}

// 5. Check for common syntax errors
echo "\n🔍 CHECKING COMMON SYNTAX ERRORS:\n";

$errors = [];

// Check for missing semicolons
preg_match_all('/\n\s*[^}\s\n][^;]*\n/', $jsOnly, $matches);
if (!empty($matches[0])) {
    $errors[] = "Possible missing semicolons: " . count($matches[0]) . " instances";
}

// Check for trailing commas
preg_match_all('/,\s*[}\]]/', $jsOnly, $matches);
if (!empty($matches[0])) {
    $errors[] = "Trailing commas found: " . count($matches[0]) . " instances";
}

// Check for undefined variables (basic check)
preg_match_all('/\b[a-zA-Z_][a-zA-Z0-9_]*\s*=/', $jsOnly, $matches);
$variables = [];
foreach ($matches[0] as $match) {
    $var = trim($match, ' =');
    if (!in_array($var, ['const', 'let', 'var']) && !in_array($var, $variables)) {
        $variables[] = $var;
    }
}

if (empty($errors)) {
    echo "✅ No obvious syntax errors found\n";
} else {
    foreach ($errors as $error) {
        echo "⚠️  {$error}\n";
    }
}

// 6. Check function definitions
echo "\n🔍 CHECKING FUNCTION DEFINITIONS:\n";
preg_match_all('/function\s+(\w+)\s*\([^)]*\)/', $jsOnly, $matches);
$functions = $matches[1];

echo "Functions found: " . count($functions) . "\n";
foreach ($functions as $index => $func) {
    echo ($index + 1) . ". {$func}\n";
}

// Check for specific functions that should exist
$requiredFunctions = ['navigateTo', 'logout', 'showNotification', 'generateDashboardContent'];
echo "\n🔍 CHECKING REQUIRED FUNCTIONS:\n";

foreach ($requiredFunctions as $func) {
    if (in_array($func, $functions)) {
        echo "✅ {$func} found\n";
    } else {
        echo "❌ {$func} NOT found\n";
    }
}

// 7. Check for event listeners
echo "\n🔍 CHECKING EVENT LISTENERS:\n";
preg_match_all('/addEventListener\s*\(/', $jsOnly, $matches);
echo "Event listeners: " . count($matches[0]) . "\n";

// 8. Check for DOM ready handlers
echo "\n🔍 CHECKING DOM READY HANDLERS:\n";
$domReadyPatterns = [
    '/DOMContentLoaded/',
    '/document\.ready/',
    '/window\.onload/'
];

$foundHandlers = [];
foreach ($domReadyPatterns as $pattern) {
    if (preg_match($pattern, $jsOnly)) {
        $foundHandlers[] = $pattern;
    }
}

if (!empty($foundHandlers)) {
    echo "✅ DOM ready handlers found: " . count($foundHandlers) . "\n";
} else {
    echo "⚠️  No DOM ready handlers found\n";
}

// 9. Overall assessment
echo "\n📊 OVERALL ASSESSMENT:\n";

$totalIssues = count($errors);
if ($openBraces !== $closeBraces) $totalIssues++;
if ($openParens !== $closeParens) $totalIssues++;
if ($openBrackets !== $closeBrackets) $totalIssues++;
if ($templateLiterals % 2 !== 0) $totalIssues++;

if ($totalIssues === 0) {
    echo "✅ EXCELLENT - No syntax issues detected\n";
    echo "🚀 JavaScript code appears syntactically correct\n";
} elseif ($totalIssues <= 2) {
    echo "⚠️  GOOD - Minor issues detected\n";
    echo "🔧 Review and fix the issues above\n";
} else {
    echo "❌ NEEDS ATTENTION - Multiple issues detected\n";
    echo "🔧 Fix all issues before production\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🚀 ADVANCED JAVASCRIPT CHECK COMPLETE\n";
echo str_repeat("=", 60) . "\n";
?>
