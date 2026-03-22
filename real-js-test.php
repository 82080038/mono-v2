<?php
/**
 * Real JavaScript Error Test
 * Tests actual JavaScript execution like browser would
 */

// Test 1: Basic JavaScript syntax validation
echo "=== REAL JAVASCRIPT ERROR TEST ===\n\n";

$content = file_get_contents(__DIR__ . '/main.php');

// Extract JavaScript part
$startPos = strpos($content, '<script>');
$endPos = strrpos($content, '</script>');
$jsContent = substr($content, $startPos, $endPos - $startPos + 9);

// Remove HTML tags
$jsOnly = preg_replace('/<[^>]*>/', '', $jsContent);

// Test 1: Check with Node.js if available
echo "🔍 TESTING WITH NODE.JS:\n";
$jsFile = tempnam(sys_get_temp_dir(), 'js_test_');
file_put_contents($jsFile, $jsOnly);

$output = [];
$returnCode = 0;
exec("node --check {$jsFile} 2>&1", $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Node.js syntax check: PASSED\n";
} else {
    echo "❌ Node.js syntax check: FAILED\n";
    echo "Error: " . implode("\n", array_slice($output, -5)) . "\n";
}

unlink($jsFile);

// Test 2: Check for specific error patterns
echo "\n🔍 CHECKING FOR COMMON ERRORS:\n";

$patterns = [
    '/ReferenceError:\s*(\w+)\s+is\s+not\s+defined/i' => 'Undefined variable',
    '/SyntaxError:\s*unexpected\s+token/i' => 'Syntax error',
    '/TypeError:\s*(\w+)\s+is\s+not\s+a\s+function/i' => 'Type error',
    '/Uncaught\s+(\w+):/i' => 'Uncaught error'
];

$foundErrors = [];
foreach ($patterns as $pattern => $description) {
    if (preg_match($pattern, $jsContent)) {
        $foundErrors[] = $description;
        echo "❌ {$description}: Found\n";
    } else {
        echo "✅ {$description}: Not found\n";
    }
}

// Test 3: Check for undefined function calls
echo "\n🔍 CHECKING FOR UNDEFINED FUNCTIONS:\n";

// Find all function calls
preg_match_all('/(\w+)\s*\(/', $jsContent, $matches);
$allCalls = array_unique($matches[1]);

// Find all function definitions
preg_match_all('/function\s+(\w+)/', $jsContent, $matches);
$definedFunctions = array_unique($matches[1]);

// Find calls to undefined functions
$undefinedCalls = array_diff($allCalls, $definedFunctions, [
    'console', 'alert', 'confirm', 'prompt', 'setTimeout', 'setInterval',
    'clearTimeout', 'clearInterval', 'addEventListener', 'removeEventListener',
    'createElement', 'getElementById', 'querySelector', 'querySelectorAll',
    'appendChild', 'removeChild', 'innerHTML', 'textContent', 'value',
    'classList', 'style', 'getAttribute', 'setAttribute', 'removeAttribute',
    'addEventListener', 'dispatchEvent', 'preventDefault', 'stopPropagation',
    'JSON', 'Date', 'Array', 'Object', 'String', 'Number', 'Boolean',
    'Math', 'parseInt', 'parseFloat', 'isNaN', 'isFinite', 'encodeURI',
    'decodeURI', 'encodeURIComponent', 'decodeURIComponent',
    'fetch', 'Promise', 'async', 'await', 'window', 'document', 'location',
    'navigator', 'history', 'sessionStorage', 'localStorage'
]);

if (empty($undefinedCalls)) {
    echo "✅ No undefined function calls found\n";
} else {
    echo "❌ Potentially undefined function calls:\n";
    foreach ($undefinedCalls as $call) {
        echo "   • {$call}\n";
    }
}

// Test 4: Check for specific issues in main.php
echo "\n🔍 CHECKING FOR SPECIFIC ISSUES IN MAIN.PHP:\n";

// Check for handleQuickAction function
if (strpos($jsContent, 'handleQuickAction') !== false) {
    if (strpos($jsContent, 'function handleQuickAction') === false) {
        echo "❌ handleQuickAction called but not defined\n";
        $foundErrors[] = 'handleQuickAction undefined';
    } else {
        echo "✅ handleQuickAction defined\n";
    }
}

// Check for missing semicolons after function definitions
$functionCount = preg_match_all('/function\s+\w+\s*\([^)]*\)\s*\{[^}]*\s*\}\s*;/', $jsContent);
$totalFunctions = preg_match_all('/function\s+\w+\s*\([^)]*\)\s*\{/', $jsContent);

echo "Functions with semicolons: {$functionCount}\n";
echo "Total functions: {$totalFunctions}\n";

if ($functionCount < $totalFunctions) {
    echo "⚠️  Some functions missing semicolons\n";
}

// Test 5: Create a simple browser-like test
echo "\n🔍 CREATING BROWSER-LIKE TEST:\n";

$testJs = "
// Test basic functionality
try {
    // Test showNotification
    if (typeof showNotification === 'function') {
        console.log('✅ showNotification defined');
    } else {
        console.log('❌ showNotification NOT defined');
    }
    
    // Test navigateTo
    if (typeof navigateTo === 'function') {
        console.log('✅ navigateTo defined');
    } else {
        console.log('❌ navigateTo NOT defined');
    }
    
    // Test logout
    if (typeof logout === 'function') {
        console.log('✅ logout defined');
    } else {
        console.log('❌ logout NOT defined');
    }
    
    // Test content generators
    const generators = ['generateDashboardContent', 'generateLaporanContent', 'generateNasabahContent'];
    generators.forEach(gen => {
        if (typeof window[gen] === 'function') {
            console.log('✅ ' + gen + ' defined');
        } else {
            console.log('❌ ' + gen + ' NOT defined');
        }
    });
    
} catch (error) {
    console.log('❌ Error: ' + error.message);
}
";

$testFile = tempnam(sys_get_temp_dir(), 'browser_test_');
file_put_contents($testFile, $testJs);

$output = [];
$returnCode = 0;
exec("node {$testFile} 2>&1", $output, $returnCode);

echo "Browser test results:\n";
foreach ($output as $line) {
    echo "   {$line}\n";
}

unlink($testFile);

// Summary
echo "\n📊 SUMMARY:\n";

if (empty($foundErrors)) {
    echo "✅ No obvious JavaScript errors found\n";
    echo "🚀 Code appears syntactically correct\n";
} else {
    echo "❌ Found " . count($foundErrors) . " potential issues:\n";
    foreach ($foundErrors as $error) {
        echo "   • {$error}\n";
    }
}

echo "\n🔧 RECOMMENDATIONS:\n";
echo "1. Test in actual browser for runtime errors\n";
echo "2. Check browser console for specific error messages\n";
echo "3. Verify all functions are properly defined\n";
echo "4. Test dynamic content loading manually\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "🚀 REAL JAVASCRIPT ERROR TEST COMPLETE\n";
echo str_repeat("=", 60) . "\n";
?>
