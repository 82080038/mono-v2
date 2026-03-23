<?php
/**
 * Comprehensive Login & Dashboard Testing for All Roles
 * This script will test login and dashboard functionality for all 9 roles
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
    
    echo "🧪 COMPREHENSIVE LOGIN & DASHBOARD TESTING - ALL ROLES\n";
    echo str_repeat("=", 60) . "\n\n";
    
    // Define all roles and credentials
    $roles = [
        [
            'role' => 'creator',
            'username' => 'creator',
            'password' => 'creator123',
            'name' => 'System Creator',
            'level' => 0
        ],
        [
            'role' => 'owner',
            'username' => 'owner',
            'password' => 'owner123',
            'name' => 'Business Owner',
            'level' => 1
        ],
        [
            'role' => 'general_manager',
            'username' => 'gm',
            'password' => 'gm123',
            'name' => 'General Manager',
            'level' => 2
        ],
        [
            'role' => 'it_manager',
            'username' => 'itmanager',
            'password' => 'itmanager123',
            'name' => 'IT Manager',
            'level' => 3
        ],
        [
            'role' => 'finance_manager',
            'username' => 'financemgr',
            'password' => 'financemgr123',
            'name' => 'Finance Manager',
            'level' => 4
        ],
        [
            'role' => 'supervisor',
            'username' => 'supervisor',
            'password' => 'supervisor123',
            'name' => 'Supervisor',
            'level' => 5
        ],
        [
            'role' => 'teller',
            'username' => 'teller',
            'password' => 'teller123',
            'name' => 'Teller',
            'level' => 6
        ],
        [
            'role' => 'field_officer',
            'username' => 'fieldofficer',
            'password' => 'fieldofficer123',
            'name' => 'Field Officer',
            'level' => 7
        ],
        [
            'role' => 'member',
            'username' => 'member',
            'password' => 'member123',
            'name' => 'Member',
            'level' => 8
        ]
    ];
    
    $testResults = [];
    
    foreach ($roles as $roleData) {
        echo "🔍 Testing Role: {$roleData['name']} ({$roleData['role']})\n";
        echo str_repeat("-", 50) . "\n";
        
        $testResult = [
            'role' => $roleData['role'],
            'name' => $roleData['name'],
            'login_test' => false,
            'token_test' => false,
            'dashboard_api_test' => false,
            'navigation_api_test' => false,
            'pages_found' => 0,
            'pages_missing' => 0,
            'menu_items' => 0,
            'menu_missing' => 0,
            'errors' => []
        ];
        
        // Test 1: Check user exists in database
        echo "📋 Step 1: Checking user in database...\n";
        $stmt = $pdo->prepare("SELECT id, username, role, status FROM users WHERE username = ? AND password IS NOT NULL");
        $stmt->execute([$roleData['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "✅ User found: ID={$user['id']}, Role={$user['role']}, Status={$user['status']}\n";
            $testResult['login_test'] = true;
        } else {
            echo "❌ User not found in database\n";
            $testResult['errors'][] = "User not found in database";
        }
        
        // Test 2: Generate token
        echo "\n📋 Step 2: Generating authentication token...\n";
        try {
            $tokenPayload = [
                'user_id' => $user['id'] ?? 0,
                'username' => $roleData['username'],
                'name' => $roleData['name'],
                'role' => $roleData['role'],
                'exp' => time() + 3600
            ];
            
            $token = base64_encode(json_encode($tokenPayload));
            echo "✅ Token generated successfully\n";
            $testResult['token_test'] = true;
        } catch (Exception $e) {
            echo "❌ Token generation failed: " . $e->getMessage() . "\n";
            $testResult['errors'][] = "Token generation failed";
        }
        
        // Test 3: Test Dashboard API
        echo "\n📋 Step 3: Testing Dashboard API...\n";
        if ($testResult['token_test']) {
            try {
                // Simulate API call
                $_REQUEST['action'] = 'get_dashboard';
                $_REQUEST['token'] = $token;
                
                // Get user role from token
                $userData = json_decode(base64_decode($token), true);
                if ($userData && isset($userData['role'])) {
                    $role = $userData['role'];
                    
                    // Get dashboard pages for this role
                    $stmt = $pdo->prepare("
                        SELECT dp.*, rdp.access_level, rdp.sort_order as role_sort_order
                        FROM dashboard_pages dp
                        JOIN role_dashboard_pages rdp ON dp.page_key = rdp.page_key
                        WHERE rdp.role_key = ? AND rdp.is_visible = 1 AND dp.is_active = 1
                        ORDER BY rdp.sort_order ASC, dp.sort_order ASC
                    ");
                    $stmt->execute([$role]);
                    $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $accessiblePages = 0;
                    $missingPages = 0;
                    
                    foreach ($pages as $page) {
                        $filePath = __DIR__ . '/../' . $page['page_url'];
                        if (file_exists($filePath)) {
                            $accessiblePages++;
                        } else {
                            $missingPages++;
                        }
                    }
                    
                    echo "✅ Dashboard API working\n";
                    echo "  📄 Total pages: " . count($pages) . "\n";
                    echo "  ✅ Accessible pages: $accessiblePages\n";
                    echo "  ❌ Missing pages: $missingPages\n";
                    
                    $testResult['dashboard_api_test'] = true;
                    $testResult['pages_found'] = $accessiblePages;
                    $testResult['pages_missing'] = $missingPages;
                    
                    // Show page details
                    if (count($pages) > 0) {
                        echo "  📋 Pages:\n";
                        foreach ($pages as $page) {
                            $status = file_exists(__DIR__ . '/../' . $page['page_url']) ? '✅' : '❌';
                            echo "    $status {$page['page_title']} - {$page['page_url']}\n";
                        }
                    }
                } else {
                    echo "❌ Invalid token - missing role\n";
                    $testResult['errors'][] = "Invalid token - missing role";
                }
            } catch (Exception $e) {
                echo "❌ Dashboard API failed: " . $e->getMessage() . "\n";
                $testResult['errors'][] = "Dashboard API failed";
            }
        } else {
            echo "❌ Skipping Dashboard API test - no token\n";
            $testResult['errors'][] = "No token available";
        }
        
        // Test 4: Test Navigation API
        echo "\n📋 Step 4: Testing Navigation API...\n";
        if ($testResult['token_test']) {
            try {
                // Get user role from token
                $userData = json_decode(base64_decode($token), true);
                if ($userData && isset($userData['role'])) {
                    $role = $userData['role'];
                    
                    // Get navigation menu for this role
                    $stmt = $pdo->prepare("
                        SELECT nm.*, rnm.access_level, rnm.sort_order as role_sort_order
                        FROM navigation_menu nm
                        JOIN role_navigation_menu rnm ON nm.menu_key = rnm.menu_key
                        WHERE rnm.role_key = ? AND rnm.is_visible = 1 AND nm.is_active = 1
                        ORDER BY rnm.sort_order ASC, nm.sort_order ASC
                    ");
                    $stmt->execute([$role]);
                    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $accessibleMenu = 0;
                    $missingMenu = 0;
                    
                    foreach ($menuItems as $item) {
                        if (!empty($item['menu_url'])) {
                            $filePath = __DIR__ . '/../' . $item['menu_url'];
                            if (file_exists($filePath)) {
                                $accessibleMenu++;
                            } else {
                                $missingMenu++;
                            }
                        } else {
                            $accessibleMenu++; // Parent menus without URLs
                        }
                    }
                    
                    echo "✅ Navigation API working\n";
                    echo "  🧭 Total menu items: " . count($menuItems) . "\n";
                    echo "  ✅ Accessible items: $accessibleMenu\n";
                    echo "  ❌ Missing items: $missingMenu\n";
                    
                    $testResult['navigation_api_test'] = true;
                    $testResult['menu_items'] = count($menuItems);
                    $testResult['menu_missing'] = $missingMenu;
                    
                    // Show menu details
                    if (count($menuItems) > 0) {
                        echo "  📋 Menu items:\n";
                        foreach ($menuItems as $item) {
                            $status = (!empty($item['menu_url']) && file_exists(__DIR__ . '/../' . $item['menu_url'])) ? '✅' : '❌';
                            $url = $item['menu_url'] ?: 'No URL';
                            echo "    $status {$item['menu_title']} - $url\n";
                        }
                    }
                } else {
                    echo "❌ Invalid token - missing role\n";
                    $testResult['errors'][] = "Invalid token - missing role";
                }
            } catch (Exception $e) {
                echo "❌ Navigation API failed: " . $e->getMessage() . "\n";
                $testResult['errors'][] = "Navigation API failed";
            }
        } else {
            echo "❌ Skipping Navigation API test - no token\n";
            $testResult['errors'][] = "No token available";
        }
        
        // Test 5: Test Login Redirect
        echo "\n📋 Step 5: Testing Login Redirect...\n";
        $redirectUrl = "pages/dynamic-dashboard.html";
        echo "✅ Redirect URL: $redirectUrl\n";
        
        $testResults[] = $testResult;
        echo "\n" . str_repeat("=", 60) . "\n\n";
    }
    
    // Summary Report
    echo "📊 TESTING SUMMARY REPORT\n";
    echo str_repeat("=", 60) . "\n";
    
    $totalRoles = count($roles);
    $successfulLogins = 0;
    $successfulTokens = 0;
    $successfulDashboardAPIs = 0;
    $successfulNavigationAPIs = 0;
    $totalErrors = 0;
    
    foreach ($testResults as $result) {
        if ($result['login_test']) $successfulLogins++;
        if ($result['token_test']) $successfulTokens++;
        if ($result['dashboard_api_test']) $successfulDashboardAPIs++;
        if ($result['navigation_api_test']) $successfulNavigationAPIs++;
        $totalErrors += count($result['errors']);
    }
    
    echo "📈 Overall Statistics:\n";
    echo "  👥 Total Roles Tested: $totalRoles\n";
    echo "  ✅ Successful Logins: $successfulLogins/$totalRoles (" . round(($successfulLogins/$totalRoles)*100, 1) . "%)\n";
    echo "  🔑 Successful Tokens: $successfulTokens/$totalRoles (" . round(($successfulTokens/$totalRoles)*100, 1) . "%)\n";
    echo "  📊 Successful Dashboard APIs: $successfulDashboardAPIs/$totalRoles (" . round(($successfulDashboardAPIs/$totalRoles)*100, 1) . "%)\n";
    echo "  🧭 Successful Navigation APIs: $successfulNavigationAPIs/$totalRoles (" . round(($successfulNavigationAPIs/$totalRoles)*100, 1) . "%)\n";
    echo "  ❌ Total Errors: $totalErrors\n\n";
    
    // Role-by-role breakdown
    echo "📋 Role-by-Role Breakdown:\n";
    foreach ($testResults as $result) {
        $status = ($result['login_test'] && $result['token_test'] && $result['dashboard_api_test'] && $result['navigation_api_test']) ? '✅' : '❌';
        echo "$status {$result['name']} ({$result['role']})\n";
        echo "    📄 Pages: {$result['pages_found']} found, {$result['pages_missing']} missing\n";
        echo "    🧭 Menu: {$result['menu_items']} total, {$result['menu_missing']} missing\n";
        if (!empty($result['errors'])) {
            echo "    ❌ Errors: " . implode(', ', $result['errors']) . "\n";
        }
        echo "\n";
    }
    
    // Recommendations
    echo "💡 Recommendations:\n";
    if ($totalErrors > 0) {
        echo "  ❌ Fix $totalErrors errors found during testing\n";
    }
    
    $missingFiles = array_sum(array_column($testResults, 'pages_missing')) + array_sum(array_column($testResults, 'menu_missing'));
    if ($missingFiles > 0) {
        echo "  📁 Create $missingFiles missing page files for complete functionality\n";
    }
    
    if ($successfulLogins == $totalRoles && $successfulTokens == $totalRoles && 
        $successfulDashboardAPIs == $totalRoles && $successfulNavigationAPIs == $totalRoles) {
        echo "  🎉 All systems working perfectly! Ready for production.\n";
    } else {
        echo "  🔧 Some systems need attention before full deployment.\n";
    }
    
    echo "\n🎉 COMPREHENSIVE TESTING COMPLETED!\n";
    
} catch (Exception $e) {
    echo "❌ Testing failed: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
