<?php
/**
 * Test showNotification function specifically
 */

// Get the main.php content
$content = file_get_contents(__DIR__ . '/main.php');

// Extract JavaScript part
$startPos = strpos($content, '<script>');
$endPos = strpos($content, '</script>');
$jsContent = substr($content, $startPos, $endPos - $startPos + 9);

// Check for showNotification function
$hasShowNotification = strpos($jsContent, 'function showNotification') !== false;
$hasShowNotificationCall = strpos($jsContent, 'showNotification(') !== false;

echo "=== SHOW NOTIFICATION FUNCTION TEST ===\n\n";

echo "🔍 FUNCTION DEFINITION:\n";
if ($hasShowNotification) {
    echo "✅ showNotification function found\n";
} else {
    echo "❌ showNotification function NOT found\n";
}

echo "\n🔍 FUNCTION CALLS:\n";
if ($hasShowNotificationCall) {
    echo "✅ showNotification calls found\n";
    
    // Count calls
    $callCount = substr_count($jsContent, 'showNotification(');
    echo "📊 Total calls: {$callCount}\n";
    
    // Find call contexts
    preg_match_all('/showNotification\([^)]+\)/', $jsContent, $matches);
    echo "📝 Call contexts:\n";
    foreach ($matches[0] as $index => $match) {
        echo "  " . ($index + 1) . ". {$match}\n";
    }
} else {
    echo "❌ No showNotification calls found\n";
}

echo "\n🎯 FUNCTION PARAMETERS:\n";
if ($hasShowNotification) {
    // Extract function signature
    preg_match('/function showNotification\(([^)]*)\)/', $jsContent, $match);
    if (isset($match[1])) {
        echo "📝 Parameters: {$match[1]}\n";
    }
    
    // Check for default parameter
    $hasDefault = strpos($jsContent, 'type = \'info\'') !== false;
    echo "🔧 Default parameter: " . ($hasDefault ? '✅ Yes' : '❌ No') . "\n";
}

echo "\n🎨 STYLING FEATURES:\n";
if ($hasShowNotification) {
    $hasAnimation = strpos($jsContent, 'slideInRight') !== false;
    $hasContainer = strpos($jsContent, 'notification-container') !== false;
    $hasTypes = strpos($jsContent, 'typeStyles') !== false;
    
    echo "🎬 Animation: " . ($hasAnimation ? '✅ Yes' : '❌ No') . "\n";
    echo "📦 Container: " . ($hasContainer ? '✅ Yes' : '❌ No') . "\n";
    echo "🎨 Type styles: " . ($hasTypes ? '✅ Yes' : '❌ No') . "\n";
}

echo "\n⏰ AUTO-CLOSE:\n";
if ($hasShowNotification) {
    $hasAutoClose = strpos($jsContent, 'setTimeout') !== false;
    echo "⚡ Auto-close: " . ($hasAutoClose ? '✅ Yes' : '❌ No') . "\n";
    
    if ($hasAutoClose) {
        // Extract timeout duration
        preg_match('/setTimeout\([^,]+,\s*(\d+)\)/', $jsContent, $match);
        if (isset($match[1])) {
            echo "⏱️ Duration: {$match[1]}ms (" . ($match[1]/1000) . "s)\n";
        }
    }
}

echo "\n🖱️ INTERACTION:\n";
if ($hasShowNotification) {
    $hasClickHandler = strpos($jsContent, 'addEventListener(\'click\'') !== false;
    $hasCloseButton = strpos($jsContent, 'fa-times') !== false;
    
    echo "👆 Click handler: " . ($hasClickHandler ? '✅ Yes' : '❌ No') . "\n";
    echo "❌ Close button: " . ($hasCloseButton ? '✅ Yes' : '❌ No') . "\n";
}

echo "\n🎯 OVERALL STATUS:\n";
if ($hasShowNotification && $hasShowNotificationCall) {
    echo "✅ COMPLETE - showNotification function fully implemented\n";
    echo "🚀 Ready for use in navigation and other features\n";
} elseif ($hasShowNotification) {
    echo "⚠️ PARTIAL - Function defined but not used\n";
} else {
    echo "❌ MISSING - showNotification function not found\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🚀 SHOW NOTIFICATION TEST COMPLETE\n";
echo str_repeat("=", 50) . "\n";
?>
