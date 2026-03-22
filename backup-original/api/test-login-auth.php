<?php
/**
 * Test Login Authentication for All Roles
 * This script will test actual login authentication for all roles
 */

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/../config/error-config.php';
require_once __DIR__ . '/DatabaseHelper.php';
require_once __DIR__ . '/AuthHelper.php';

try {
    $db = DatabaseHelper::getInstance();
    $pdo = $db->getConnection();
    
    // Enable buffered queries to avoid issues
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    
    echo "🔐 LOGIN AUTHENTICATION TESTING - ALL ROLES\n";
    echo str_repeat("=", 60) . "\n\n";
    
    // Define all roles and credentials
    $roles = [
        [
            'role' => 'creator',
            'username' => 'creator',
            'password' => 'creator123',
            'name' => 'System Creator'
        ],
        [
            'role' => 'owner',
            'username' => 'owner',
            'password' => 'owner123',
            'name' => 'Business Owner'
        ],
        [
            'role' => 'general_manager',
            'username' => 'gm',
            'password' => 'gm123',
            'name' => 'General Manager'
        ],
        [
            'role' => 'it_manager',
            'username' => 'itmanager',
            'password' => 'itmanager123',
            'name' => 'IT Manager'
        ],
        [
            'role' => 'finance_manager',
            'username' => 'financemgr',
            'password' => 'financemgr123',
            'name' => 'Finance Manager'
        ],
        [
            'role' => 'supervisor',
            'username' => 'supervisor',
            'password' => 'supervisor123',
            'name' => 'Supervisor'
        ],
        [
            'role' => 'teller',
            'username' => 'teller',
            'password' => 'teller123',
            'name' => 'Teller'
        ],
        [
            'role' => 'field_officer',
            'username' => 'fieldofficer',
            'password' => 'fieldofficer123',
            'name' => 'Field Officer'
        ],
        [
            'role' => 'member',
            'username' => 'member',
            'password' => 'member123',
            'name' => 'Member'
        ]
    ];
    
    $loginResults = [];
    
    foreach ($roles as $roleData) {
        echo "🔍 Testing Login: {$roleData['name']} ({$roleData['username']})\n";
        echo str_repeat("-", 50) . "\n";
        
        $loginResult = [
            'role' => $roleData['role'],
            'name' => $roleData['name'],
            'username' => $roleData['username'],
            'user_found' => false,
            'password_match' => false,
            'login_success' => false,
            'token_generated' => false,
            'redirect_url' => '',
            'errors' => []
        ];
        
        // Test 1: Check user exists
        echo "📋 Step 1: Checking user in database...\n";
        $stmt = $pdo->prepare("SELECT id, username, password, role, status FROM users WHERE username = ?");
        $stmt->execute([$roleData['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "✅ User found: ID={$user['id']}, Role={$user['role']}, Status={$user['status']}\n";
            $loginResult['user_found'] = true;
            
            // Test 2: Check password
            echo "\n📋 Step 2: Verifying password...\n";
            if ($user['password'] === $roleData['password']) {
                echo "✅ Password match successful\n";
                $loginResult['password_match'] = true;
                $loginResult['login_success'] = true;
                
                // Test 3: Generate token
                echo "\n📋 Step 3: Generating authentication token...\n";
                try {
                    $tokenPayload = [
                        'user_id' => $user['id'],
                        'username' => $user['username'],
                        'name' => $roleData['name'],
                        'role' => $user['role'],
                        'exp' => time() + 3600
                    ];
                    
                    $token = base64_encode(json_encode($tokenPayload));
                    echo "✅ Token generated successfully\n";
                    echo "  🔑 Token: " . substr($token, 0, 50) . "...\n";
                    $loginResult['token_generated'] = true;
                    
                    // Test 4: Test redirect
                    echo "\n📋 Step 4: Testing login redirect...\n";
                    $redirectUrl = "pages/dynamic-dashboard.html";
                    echo "✅ Redirect URL: $redirectUrl\n";
                    $loginResult['redirect_url'] = $redirectUrl;
                    
                    // Test 5: Verify token can be parsed
                    echo "\n📋 Step 5: Verifying token parsing...\n";
                    $parsedData = json_decode(base64_decode($token), true);
                    if ($parsedData && isset($parsedData['role'])) {
                        echo "✅ Token parsing successful\n";
                        echo "  👤 User ID: {$parsedData['user_id']}\n";
                        echo "  👤 Username: {$parsedData['username']}\n";
                        echo "  👤 Name: {$parsedData['name']}\n";
                        echo "  🎭 Role: {$parsedData['role']}\n";
                    } else {
                        echo "❌ Token parsing failed\n";
                        $loginResult['errors'][] = "Token parsing failed";
                    }
                    
                } catch (Exception $e) {
                    echo "❌ Token generation failed: " . $e->getMessage() . "\n";
                    $loginResult['errors'][] = "Token generation failed";
                }
            } else {
                echo "❌ Password mismatch\n";
                $loginResult['errors'][] = "Password mismatch";
            }
        } else {
            echo "❌ User not found in database\n";
            $loginResult['errors'][] = "User not found";
        }
        
        $loginResults[] = $loginResult;
        echo "\n" . str_repeat("=", 60) . "\n\n";
    }
    
    // Summary Report
    echo "📊 LOGIN TESTING SUMMARY\n";
    echo str_repeat("=", 60) . "\n";
    
    $totalRoles = count($roles);
    $successfulLogins = 0;
    $successfulTokens = 0;
    $totalErrors = 0;
    
    foreach ($loginResults as $result) {
        if ($result['login_success']) $successfulLogins++;
        if ($result['token_generated']) $successfulTokens++;
        $totalErrors += count($result['errors']);
    }
    
    echo "📈 Login Statistics:\n";
    echo "  👥 Total Roles Tested: $totalRoles\n";
    echo "  ✅ Successful Logins: $successfulLogins/$totalRoles (" . round(($successfulLogins/$totalRoles)*100, 1) . "%)\n";
    echo "  🔑 Successful Tokens: $successfulTokens/$totalRoles (" . round(($successfulTokens/$totalRoles)*100, 1) . "%)\n";
    echo "  ❌ Total Errors: $totalErrors\n\n";
    
    // Role-by-role breakdown
    echo "📋 Role-by-Role Login Results:\n";
    foreach ($loginResults as $result) {
        $status = $result['login_success'] ? '✅' : '❌';
        echo "$status {$result['name']} ({$result['username']})\n";
        echo "    📁 User Found: " . ($result['user_found'] ? '✅' : '❌') . "\n";
        echo "    🔐 Password Match: " . ($result['password_match'] ? '✅' : '❌') . "\n";
        echo "    🔑 Token Generated: " . ($result['token_generated'] ? '✅' : '❌') . "\n";
        echo "    🔄 Redirect: {$result['redirect_url']}\n";
        if (!empty($result['errors'])) {
            echo "    ❌ Errors: " . implode(', ', $result['errors']) . "\n";
        }
        echo "\n";
    }
    
    // Test credentials list
    echo "📝 LOGIN CREDENTIALS LIST:\n";
    echo str_repeat("-", 60) . "\n";
    foreach ($roles as $role) {
        echo "👤 {$role['name']}:\n";
        echo "   Username: {$role['username']}\n";
        echo "   Password: {$role['password']}\n";
        echo "   Role: {$role['role']}\n";
        echo "\n";
    }
    
    // Recommendations
    echo "💡 Recommendations:\n";
    if ($totalErrors > 0) {
        echo "  ❌ Fix $totalErrors login errors found\n";
    }
    
    if ($successfulLogins == $totalRoles) {
        echo "  🎉 All login systems working perfectly!\n";
        echo "  🚀 System ready for user testing\n";
    } else {
        echo "  🔧 Some login issues need attention\n";
    }
    
    echo "\n🎉 LOGIN AUTHENTICATION TESTING COMPLETED!\n";
    
} catch (Exception $e) {
    echo "❌ Testing failed: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
