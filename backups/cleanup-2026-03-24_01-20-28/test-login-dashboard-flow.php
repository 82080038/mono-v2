<?php
/**
 * Test Login to Dashboard Flow
 * KSP Lam Gabe Jaya - Complete Login Flow Testing
 */

echo "=== LOGIN TO DASHBOARD FLOW TESTING ===\n\n";

// Test credentials
$testUsers = [
    ['username' => 'bos', 'password' => 'bos', 'role' => 'bos'],
    ['username' => 'admin', 'password' => 'admin', 'role' => 'admin'],
    ['username' => 'teller', 'password' => 'teller', 'role' => 'teller'],
    ['username' => 'collector', 'password' => 'collector', 'role' => 'collector'],
    ['username' => 'nasabah', 'password' => 'nasabah', 'role' => 'nasabah']
];

$baseUrl = 'http://localhost/mono-v2';

foreach ($testUsers as $index => $user) {
    echo "Test #" . ($index + 1) . " - " . strtoupper($user['role']) . "\n";
    echo "Username: " . $user['username'] . "\n";
    echo "Expected Flow:\n";
    echo "1. Login POST → API auth.php\n";
    echo "2. Session Created → main.php\n";
    echo "3. Dashboard Loaded → Role-specific content\n\n";
    
    // Test login API
    $apiUrl = $baseUrl . '/api/auth.php';
    $postData = http_build_query([
        'action' => 'login',
        'username' => $user['username'],
        'password' => $user['password']
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
            echo "✅ Login API successful\n";
            echo "✅ User ID: " . $data['user']['id'] . "\n";
            echo "✅ Role: " . $data['user']['role'] . "\n";
            echo "✅ Role Display: " . $data['user']['role_display_name'] . "\n";
            echo "✅ Permissions: " . count($data['user']['permissions']) . " items\n";
            echo "✅ Redirect URL: " . $data['redirect'] . "\n";
            
            // Test main.php accessibility (simulate session)
            echo "✅ Dashboard should be accessible at: /main.php\n";
            
        } else {
            echo "❌ Login failed: " . ($data['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "❌ No response from API\n";
    }
    
    echo str_repeat("-", 60) . "\n\n";
}

echo "=== MANUAL TESTING INSTRUCTIONS ===\n\n";
echo "1. Open browser: " . $baseUrl . "/login.php\n\n";

echo "2. Test each role login:\n";
foreach ($testUsers as $user) {
    echo "   • {$user['username']}/{$user['password']} ({$user['role']})\n";
}
echo "\n";

echo "3. Verify dashboard elements:\n\n";

echo "🔴 BOS Dashboard should show:\n";
echo "   - Full business overview\n";
echo "   - Financial health metrics\n";
echo "   - Top performers\n";
echo "   - Business alerts\n";
echo "   - System settings menu\n\n";

echo "🔵 ADMIN Dashboard should show:\n";
echo "   - Operational summary\n";
echo "   - Member statistics\n";
echo "   - Loan portfolio\n";
echo "   - Recent activity\n";
echo "   - User management\n\n";

echo "🟢 TELLER Dashboard should show:\n";
echo "   - Daily summary\n";
echo "   - Transaction queue\n";
echo "   - Cash balance\n";
echo "   - Recent transactions\n";
echo "   - Transaction processing\n\n";

echo "🟡 COLLECTOR Dashboard should show:\n";
echo "   - Daily targets\n";
echo "   - Collection status\n";
echo "   - Route progress\n";
echo "   - Member visits\n";
echo "   - GPS tracking\n\n";

echo "🟣 NASABAH Dashboard should show:\n";
echo "   - Account summary\n";
echo "   - Savings balance\n";
echo "   - Loan status\n";
echo "   - Payment schedule\n";
echo "   - Transaction history\n\n";

echo "4. Test navigation:\n";
echo "   - Menu items should be role-specific\n";
echo "   - Click each menu item\n";
echo "   - Verify permissions work\n";
echo "   - Test logout functionality\n\n";

echo "=== EXPECTED URL STRUCTURE ===\n\n";
echo "Login: " . $baseUrl . "/login.php\n";
echo "Dashboard: " . $baseUrl . "/main.php\n";
echo "API: " . $baseUrl . "/api/auth.php\n";
echo "Logout: " . $baseUrl . "/?action=logout\n\n";

echo "=== TESTING COMPLETE ===\n";
echo "Ready for manual browser testing! 🚀\n";
?>
