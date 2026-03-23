<?php
/**
 * Testing Login for Correct Roles
 * KSP Lam Gabe Jaya - Login Testing Script
 */

// Test credentials for each role
$testUsers = [
    [
        'username' => 'bos',
        'password' => 'bos',
        'role' => 'bos',
        'expected_page' => 'dashboard'
    ],
    [
        'username' => 'admin', 
        'password' => 'admin',
        'role' => 'admin',
        'expected_page' => 'dashboard'
    ],
    [
        'username' => 'teller',
        'password' => 'teller', 
        'role' => 'teller',
        'expected_page' => 'dashboard'
    ],
    [
        'username' => 'collector',
        'password' => 'collector',
        'role' => 'collector',
        'expected_page' => 'dashboard'
    ],
    [
        'username' => 'nasabah',
        'password' => 'nasabah',
        'role' => 'nasabah', 
        'expected_page' => 'dashboard'
    ]
];

echo "=== KSP Lam Gabe Jaya - Login Testing (Correct Roles) ===\n\n";
echo "Testing login for all user roles...\n\n";

// Base URL
$baseUrl = 'http://localhost/mono-v2';

foreach ($testUsers as $index => $user) {
    echo "Test #" . ($index + 1) . " - Role: " . strtoupper($user['role']) . "\n";
    echo "Username: " . $user['username'] . "\n";
    echo "Password: " . $user['password'] . "\n";
    echo "Expected: Login success, redirect to " . $user['expected_page'] . "\n";
    echo "URL: " . $baseUrl . "/login.php\n";
    echo "Manual test required - Open browser and test with above credentials\n";
    echo str_repeat("-", 60) . "\n\n";
}

echo "=== Manual Testing Instructions ===\n\n";
echo "1. Open browser: " . $baseUrl . "\n";
echo "2. You will be redirected to login page\n";
echo "3. Test each user account above\n";
echo "4. Verify:\n";
echo "   - Login successful\n";
echo "   - Redirect to correct dashboard\n";
echo "   - Role-based menu items visible\n";
echo "   - User information displayed correctly\n\n";

echo "=== Expected Behavior per Role ===\n\n";

echo "🔴 BOS (Pemilik Koperasi):\n";
echo "- Full access to all features\n";
echo "- Can manage users, system settings\n";
echo "- Complete reports and analytics\n";
echo "- Financial oversight\n\n";

echo "🔵 ADMIN:\n";
echo "- System administration\n";
echo "- User management (except Bos)\n";
echo "- Database management\n";
echo "- Configuration settings\n\n";

echo "🟢 TELLER:\n";
echo "- Transaction processing\n";
echo "- Member account management\n";
echo "- Daily cash handling\n";
echo "- Basic reporting\n\n";

echo "🟡 COLLECTOR (Petugas Lapangan):\n";
echo "- Field collections\n";
echo "- Member visits\n";
echo "- Mobile transactions\n";
echo "- Location tracking\n\n";

echo "🟣 NASABAH:\n";
echo "- Personal dashboard\n";
echo "- View own accounts\n";
echo "- Loan applications\n";
echo "- Transaction history\n\n";

echo "=== Demo Account Quick Access ===\n\n";
echo "Use these credentials for quick testing:\n\n";
echo "• Bos/Pemilik:      bos/bos\n";
echo "• Admin:           admin/admin\n";
echo "• Teller:          teller/teller\n";
echo "• Petugas Lapangan: collector/collector\n";
echo "• Nasabah:         nasabah/nasabah\n\n";

echo "=== Testing Complete ===\n";
?>
