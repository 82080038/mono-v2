<?php
/**
 * Test All Roles JavaScript Consistency
 * Verify all roles have proper JavaScript variables
 */

echo "=== ALL ROLES JAVASCRIPT CONSISTENCY TEST ===\n\n";

$roles = [
    ['username' => 'bos', 'password' => 'bos', 'expected_role' => 'bos'],
    ['username' => 'admin', 'password' => 'admin', 'expected_role' => 'admin'],
    ['username' => 'teller', 'password' => 'teller', 'expected_role' => 'teller'],
    ['username' => 'collector', 'password' => 'collector', 'expected_role' => 'collector'],
    ['username' => 'nasabah', 'password' => 'nasabah', 'expected_role' => 'nasabah']
];

$apiUrl = 'http://localhost/mono-v2/api/auth.php';
$dashboardUrl = 'http://localhost/mono-v2/?page=dashboard';

$allRolesPassed = true;

foreach ($roles as $role) {
    echo "🔍 Testing {$role['username']} ({$role['expected_role']}):\n";
    
    // Step 1: Login
    $postData = http_build_query([
        'action' => 'login',
        'username' => $role['username'],
        'password' => $role['password']
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $postData
        ]
    ]);
    
    $response = @file_get_contents($apiUrl, false, $context);
    
    if ($response) {
        $data = json_decode($response, true);
        
        if ($data && isset($data['success']) && $data['success']) {
            echo "✅ Login successful\n";
            
            // Save cookies
            file_put_contents('temp_cookies.txt', '');
            
            // Step 2: Get dashboard
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $dashboardUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIEJAR, 'temp_cookies.txt');
            curl_setopt($ch, CURLOPT_COOKIEFILE, 'temp_cookies.txt');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            
            $dashboardContent = curl_exec($ch);
            curl_close($ch);
            
            if ($dashboardContent) {
                // Check JavaScript variables
                if (preg_match('/let userRole = "(.*?)";/', $dashboardContent, $matches)) {
                    $actualRole = $matches[1];
                    echo "✅ userRole found: \"$actualRole\"\n";
                    
                    if ($actualRole === $role['expected_role']) {
                        echo "✅ Role verification passed\n";
                    } else {
                        echo "❌ Role mismatch: expected \"{$role['expected_role']}\", got \"$actualRole\"\n";
                        $allRolesPassed = false;
                    }
                } else {
                    echo "❌ userRole variable not found or malformed\n";
                    $allRolesPassed = false;
                }
                
                // Check currentUser object
                if (strpos($dashboardContent, 'let currentUser = {') !== false) {
                    echo "✅ currentUser object found\n";
                } else {
                    echo "❌ currentUser object not found\n";
                    $allRolesPassed = false;
                }
                
                // Check for potential JavaScript errors
                $potentialErrors = [
                    $role['expected_role'] . ' is not defined',
                    'undefined variable',
                    'ReferenceError'
                ];
                
                $foundErrors = [];
                foreach ($potentialErrors as $error) {
                    if (strpos($dashboardContent, $error) !== false) {
                        $foundErrors[] = $error;
                    }
                }
                
                if (empty($foundErrors)) {
                    echo "✅ No JavaScript errors detected\n";
                } else {
                    echo "❌ Potential JavaScript errors: " . implode(', ', $foundErrors) . "\n";
                    $allRolesPassed = false;
                }
                
                // Check dashboard content
                if (strpos($dashboardContent, 'Dashboard - KSP Lam Gabe Jaya') !== false) {
                    echo "✅ Dashboard title present\n";
                }
                
                if (strpos($dashboardContent, 'Selamat Datang') !== false) {
                    echo "✅ Welcome message present\n";
                }
                
            } else {
                echo "❌ Failed to get dashboard content\n";
                $allRolesPassed = false;
            }
            
            unlink('temp_cookies.txt');
            
        } else {
            echo "❌ Login failed: " . ($data['message'] ?? 'Unknown error') . "\n";
            $allRolesPassed = false;
        }
    } else {
        echo "❌ No response from API\n";
        $allRolesPassed = false;
    }
    
    echo str_repeat("-", 60) . "\n\n";
}

echo "📊 CONSISTENCY TEST RESULTS:\n";
if ($allRolesPassed) {
    echo "✅ ALL ROLES PASSED - JavaScript variables are consistent!\n";
    echo "✅ No 'role is not defined' errors found\n";
    echo "✅ All role assignments properly quoted\n";
} else {
    echo "❌ Some roles failed - Check individual results above\n";
}

echo "\n🎯 EXPECTED JAVASCRIPT FOR ALL ROLES:\n";
foreach ($roles as $role) {
    echo "• {$role['username']}: let userRole = \"{$role['expected_role']}\";\n";
}

echo "\n🚀 CONSISTENCY VERIFICATION COMPLETE!\n";
?>
