<?php
/**
 * Comprehensive Template Literal Checker
 * Checks all template literals in main.php for proper closure
 */

$content = file_get_contents(__DIR__ . '/main.php');

echo "=== COMPREHENSIVE TEMPLATE LITERAL CHECKER ===\n\n";

// Find all function definitions that return template literals
preg_match_all('/function\s+(\w+)\([^)]*\)\s*\{\s*return\s*`/', $content, $matches);

echo "🔍 TEMPLATE LITERAL FUNCTIONS FOUND:\n";
$functions = $matches[1];

foreach ($functions as $index => $function) {
    echo ($index + 1) . ". {$function}\n";
}

echo "\n📊 CHECKING CLOSURE:\n";

// Check each function for proper closure
$issues = [];
foreach ($functions as $function) {
    // Find the function start and end
    $pattern = "/function\s+{$function}\([^)]*\)\s*\{(.*?)\n\s*\}/s";
    preg_match($pattern, $content, $match);
    
    if (isset($match[0])) {
        $functionContent = $match[0];
        
        // Count backticks
        $backtickCount = substr_count($functionContent, '`');
        
        // Check if even number of backticks (properly paired)
        if ($backtickCount % 2 !== 0) {
            $issues[] = [
                'function' => $function,
                'issue' => 'Odd number of backticks',
                'count' => $backtickCount
            ];
            echo "❌ {$function}: {$backtickCount} backticks (odd)\n";
        } else {
            echo "✅ {$function}: {$backtickCount} backticks (even)\n";
        }
        
        // Check for return statement with backticks
        if (strpos($functionContent, 'return `') !== false) {
            // Find the position after return `
            $returnPos = strpos($functionContent, 'return `');
            $afterReturn = substr($functionContent, $returnPos + 7);
            
            // Find the closing backtick
            $closingPos = strpos($afterReturn, '`');
            if ($closingPos !== false) {
                echo "  ✅ Closing backtick found\n";
            } else {
                echo "  ❌ Closing backtick NOT found\n";
                $issues[] = [
                    'function' => $function,
                    'issue' => 'Missing closing backtick',
                    'count' => $backtickCount
                ];
            }
        }
    } else {
        echo "❌ {$function}: Function pattern not found\n";
        $issues[] = [
            'function' => $function,
            'issue' => 'Function pattern not found',
            'count' => 0
        ];
    }
}

echo "\n🔍 DETAILED ANALYSIS:\n";

// Get line numbers for issues
foreach ($issues as $issue) {
    echo "\n❌ ISSUE: {$issue['function']}\n";
    echo "   Problem: {$issue['issue']}\n";
    echo "   Backticks: {$issue['count']}\n";
    
    // Find line number
    $lines = explode("\n", $content);
    $functionPattern = "/function\s+{$issue['function']}\(/";
    foreach ($lines as $lineNum => $line) {
        if (preg_match($functionPattern, $line)) {
            echo "   Starts at line: " . ($lineNum + 1) . "\n";
            break;
        }
    }
}

echo "\n🔧 RECOMMENDATIONS:\n";

if (empty($issues)) {
    echo "✅ All template literals are properly closed!\n";
    echo "🚀 No fixes needed\n";
} else {
    echo "❌ Found " . count($issues) . " issues with template literals\n";
    echo "🔧 Fix the following functions:\n";
    
    foreach ($issues as $issue) {
        echo "   • {$issue['function']}: {$issue['issue']}\n";
    }
    
    echo "\n📝 COMMON FIXES:\n";
    echo "1. Add missing closing backtick (`)\n";
    echo "2. Ensure proper function closure with }\n";
    echo "3. Check for escaped backticks within template\n";
    echo "4. Verify nested template literals are properly closed\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🚀 TEMPLATE LITERAL CHECK COMPLETE\n";
echo str_repeat("=", 60) . "\n";
?>
