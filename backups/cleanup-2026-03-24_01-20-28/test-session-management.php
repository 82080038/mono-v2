<?php
/**
 * Session Management Testing
 * Tests session check and logout functionality
 */

echo "=== Session Management Testing ===\n\n";

// Test session check endpoint
$sessionCheckUrl = 'http://localhost/mono-v2/api/auth.php?action=check_session';

echo "1. Testing Session Check (without login)\n";
$response = @file_get_contents($sessionCheckUrl);

if ($response) {
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && !$data['success']) {
        echo "✅ Session check working correctly - no active session\n";
    } else {
        echo "⚠️  Unexpected session check response\n";
    }
} else {
    echo "❌ Session check API not responding\n";
}

echo "\n2. Testing Logout Endpoint\n";
$logoutUrl = 'http://localhost/mono-v2/api/auth.php?action=logout';

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded'
    ]
]);

$response = @file_get_contents($logoutUrl, false, $context);

if ($response) {
    $data = json_decode($response, true);
    if ($data && isset($data['success'])) {
        echo "✅ Logout endpoint responding correctly\n";
    } else {
        echo "⚠️  Unexpected logout response\n";
    }
} else {
    echo "❌ Logout API not responding\n";
}

echo "\n=== Browser Testing Instructions ===\n\n";
echo "Open browser and test the following URLs:\n\n";
echo "1. Main Application: http://localhost/mono-v2/\n";
echo "   - Should redirect to login page\n\n";
echo "2. Direct Login: http://localhost/mono-v2/login.php\n";
echo "   - Should show login form\n\n";
echo "3. Test Login with:\n";
echo "   - Username: admin, Password: password\n";
echo "   - Username: manager, Password: password\n";
echo "   - Username: staff, Password: password\n";
echo "   - Username: member001, Password: password\n\n";
echo "4. After successful login:\n";
echo "   - Should redirect to dashboard\n";
echo "   - User info should be displayed\n";
echo "   - Menu items should match user role\n\n";
echo "5. Test Logout:\n";
echo "   - Click logout button or access ?action=logout\n";
echo "   - Should redirect back to login page\n\n";

echo "=== Expected Dashboard per Role ===\n\n";

echo "🔴 ADMIN:\n";
echo "- Full dashboard with all statistics\n";
echo "- User management menu\n";
echo "- System settings menu\n";
echo "- Complete reports access\n\n";

echo "🟡 MANAGER:\n";
echo "- Dashboard with member/loan statistics\n";
echo "- Member management menu\n";
echo "- Loan approval menu\n";
echo "- Reports access (limited)\n\n";

echo "🔵 STAFF:\n";
echo "- Dashboard with transaction statistics\n";
echo "- Transaction processing menu\n";
echo "- Member view access\n";
echo "- Basic reports\n\n";

echo "🟢 MEMBER:\n";
echo "- Personal dashboard\n";
echo "- My profile menu\n";
echo "- My savings/loans menu\n";
echo "- Loan application menu\n\n";

echo "=== Testing Complete ===\n";
?>
