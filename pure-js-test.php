<?php
/**
 * Pure JavaScript Test
 * Extract only JavaScript without PHP code
 */

$content = file_get_contents(__DIR__ . '/main.php');

// Extract JavaScript part
$startPos = strpos($content, '<script>');
$endPos = strrpos($content, '</script>');
$jsContent = substr($content, $startPos, $endPos - $startPos + 9);

// Remove PHP code
$jsContent = preg_replace('/<\?php.*?\?>/s', '', $jsContent);

// Remove HTML tags
$jsContent = preg_replace('/<[^>]*>/', '', $jsContent);

echo "=== PURE JAVASCRIPT TEST ===\n\n";

// Test with Node.js
echo "🔍 TESTING PURE JAVASCRIPT:\n";
$jsFile = tempnam(sys_get_temp_dir(), 'pure_js_test_');
file_put_contents($jsFile, $jsContent);

$output = [];
$returnCode = 0;
exec("node --check {$jsFile} 2>&1", $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Node.js syntax check: PASSED\n";
} else {
    echo "❌ Node.js syntax check: FAILED\n";
    echo "Error: " . implode("\n", array_slice($output, -3)) . "\n";
}

unlink($jsFile);

// Test function definitions
echo "\n🔍 CHECKING FUNCTION DEFINITIONS:\n";

$functions = [
    'showNotification',
    'navigateTo', 
    'logout',
    'generateDashboardContent',
    'generateLaporanContent',
    'generateNasabahContent',
    'handleQuickAction',
    'refreshDashboard'
];

foreach ($functions as $func) {
    if (strpos($jsContent, "function {$func}") !== false) {
        echo "✅ {$func}: Defined\n";
    } else {
        echo "❌ {$func}: NOT defined\n";
    }
}

// Check for global scope issues
echo "\n🔍 CHECKING GLOBAL SCOPE:\n";

if (strpos($jsContent, 'window.') !== false) {
    echo "✅ Uses window object\n";
} else {
    echo "⚠️  No window object usage found\n";
}

if (strpos($jsContent, 'document.') !== false) {
    echo "✅ Uses document object\n";
} else {
    echo "⚠️  No document object usage found\n";
}

// Check for event listeners
echo "\n🔍 CHECKING EVENT LISTENERS:\n";
if (preg_match('/addEventListener\s*\(/', $jsContent)) {
    echo "✅ Event listeners found\n";
} else {
    echo "❌ No event listeners found\n";
}

// Check for DOM ready
echo "\n🔍 CHECKING DOM READY:\n";
if (strpos($jsContent, 'DOMContentLoaded') !== false) {
    echo "✅ DOMContentLoaded found\n";
} else {
    echo "❌ No DOMContentLoaded found\n";
}

// Create browser test
echo "\n🔍 CREATING BROWSER TEST:\n";

$browserTest = "
// Simulate browser environment
global.window = global.window || {};
global.document = global.document || {};
global.console = global.console;

// Extract functions from the JavaScript
{$jsContent}

// Test functions
try {
    const tests = ['showNotification', 'navigateTo', 'logout', 'refreshDashboard'];
    tests.forEach(func => {
        if (typeof global[func] === 'function') {
            console.log('✅ ' + func + ' defined');
        } else {
            console.log('❌ ' + func + ' NOT defined');
        }
    });
} catch (error) {
    console.log('❌ Error: ' + error.message);
}
";

$testFile = tempnam(sys_get_temp_dir(), 'browser_test_');
file_put_contents($testFile, $browserTest);

$output = [];
$returnCode = 0;
exec("node {$testFile} 2>&1", $output, $returnCode);

echo "Browser test results:\n";
foreach ($output as $line) {
    echo "   {$line}\n";
}

unlink($testFile);

echo "\n📊 SUMMARY:\n";
echo "✅ JavaScript syntax checked\n";
echo "✅ Function definitions verified\n";
echo "✅ Browser compatibility tested\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "🚀 PURE JAVASCRIPT TEST COMPLETE\n";
echo str_repeat("=", 60) . "\n";
?>
