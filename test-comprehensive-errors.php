<?php
/**
 * Comprehensive Error Check for All Roles and Pages
 * Test JavaScript errors, navigation issues, and content problems
 */

echo "=== COMPREHENSIVE ERROR CHECK - ALL ROLES & PAGES ===\n\n";

echo "🔧 TESTING SCOPE:\n";
echo "• All user roles: bos, admin, teller, collector, nasabah\n";
echo "• All accessible pages per role\n";
echo "• JavaScript syntax errors\n";
echo "• Navigation functionality\n";
echo "• Content loading issues\n";
echo "• Template literal problems\n\n";

$roles = [
    [
        'name' => 'bos',
        'username' => 'bos',
        'password' => 'bos',
        'pages' => ['dashboard', 'laporan', 'nasabah', 'transaksi', 'pinjaman', 'simpanan', 'pengaturan']
    ],
    [
        'name' => 'admin',
        'username' => 'admin',
        'password' => 'admin',
        'pages' => ['dashboard', 'laporan', 'nasabah', 'transaksi', 'pinjaman', 'simpanan', 'pengaturan']
    ],
    [
        'name' => 'teller',
        'username' => 'teller',
        'password' => 'teller',
        'pages' => ['dashboard', 'setoran', 'penarikan', 'transaksi', 'pembayaran']
    ],
    [
        'name' => 'collector',
        'username' => 'collector',
        'password' => 'collector',
        'pages' => ['dashboard', 'rute', 'jadwal', 'nasabah_kunjungan', 'kutipan', 'gps_log']
    ],
    [
        'name' => 'nasabah',
        'username' => 'nasabah',
        'password' => 'nasabah',
        'pages' => ['dashboard', 'profil', 'simpanan_saya', 'pinjaman_saya', 'riwayat', 'pembayaran']
    ]
];

$apiUrl = 'http://localhost/mono-v2/api/auth.php';
$baseUrl = 'http://localhost/mono-v2';

$totalTests = 0;
$passedTests = 0;
$errors = [];

foreach ($roles as $role) {
    echo "🎯 TESTING ROLE: " . strtoupper($role['name']) . "\n";
    echo str_repeat("-", 50) . "\n";
    
    // Login
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
    
    $loginResponse = @file_get_contents($apiUrl, false, $context);
    
    if ($loginResponse) {
        $data = json_decode($loginResponse, true);
        
        if ($data && isset($data['success']) && $data['success']) {
            echo "✅ Login successful\n";
            
            foreach ($role['pages'] as $page) {
                $totalTests++;
                echo "\n📄 Testing page: {$page}\n";
                
                // Get page content
                $pageContext = stream_context_create([
                    'http' => ['method' => 'GET']
                ]);
                
                $pageResponse = @file_get_contents($baseUrl . '/?page=' . $page, false, $pageContext);
                
                if ($pageResponse) {
                    // Syntax checks
                    $syntaxChecks = [
                        'Template literal closure' => substr_count($pageResponse, '`') % 2 === 0,
                        'Script tags balanced' => substr_count($pageResponse, '<script>') === substr_count($pageResponse, '</script>'),
                        'No unclosed braces' => substr_count($pageResponse, '{') === substr_count($pageResponse, '}'),
                        'No unclosed parentheses' => substr_count($pageResponse, '(') === substr_count($pageResponse, ')'),
                        'navigateTo function exists' => strpos($pageResponse, 'function navigateTo') !== false,
                        'Page content mapping exists' => strpos($pageResponse, 'pageContents[') !== false
                    ];
                    
                    $pageErrors = [];
                    foreach ($syntaxChecks as $check => $passed) {
                        if (!$passed) {
                            $pageErrors[] = $check;
                        }
                    }
                    
                    if (empty($pageErrors)) {
                        echo "✅ All syntax checks passed\n";
                        $passedTests++;
                    } else {
                        echo "❌ Syntax errors: " . implode(', ', $pageErrors) . "\n";
                        $errors[] = "{$role['name']}/{$page}: " . implode(', ', $pageErrors);
                    }
                    
                    // Content checks
                    $contentChecks = [
                        'Page title present' => strpos($pageResponse, '<h1>') !== false || strpos($pageResponse, '<title>') !== false,
                        'Content generator exists' => strpos($pageResponse, 'generate' . ucfirst(str_replace('_', '', $page)) . 'Content') !== false,
                        'No generic dashboard' => strpos($pageResponse, 'loadDashboardWidgets()') === false || $page === 'dashboard',
                        'Navigation links work' => strpos($pageResponse, 'navigateTo(') !== false
                    ];
                    
                    $contentErrors = [];
                    foreach ($contentChecks as $check => $passed) {
                        if (!$passed) {
                            $contentErrors[] = $check;
                        }
                    }
                    
                    if (empty($contentErrors)) {
                        echo "✅ All content checks passed\n";
                    } else {
                        echo "⚠️  Content issues: " . implode(', ', $contentErrors) . "\n";
                    }
                    
                    // Check for specific JavaScript errors
                    $jsErrorPatterns = [
                        '/function\s+\w+\s*\([^)]*\)\s*\{[^}]*$/s' => 'Unclosed function',
                        '/`[^`]*$/s' => 'Unclosed template literal',
                        '/\{[^}]*$/s' => 'Unclosed brace',
                        '/\([^)]*$/s' => 'Unclosed parenthesis'
                    ];
                    
                    foreach ($jsErrorPatterns as $pattern => $error) {
                        if (preg_match($pattern, $pageResponse)) {
                            echo "❌ JavaScript error detected: {$error}\n";
                            $errors[] = "{$role['name']}/{$page}: {$error}";
                        }
                    }
                    
                } else {
                    echo "❌ Failed to load page content\n";
                    $errors[] = "{$role['name']}/{$page}: Failed to load";
                }
            }
            
        } else {
            echo "❌ Login failed\n";
            $errors[] = "{$role['name']}: Login failed";
        }
    } else {
        echo "❌ No response from API\n";
        $errors[] = "{$role['name']}: No API response";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n\n";
}

echo "📊 TEST SUMMARY:\n";
echo "Total tests: {$totalTests}\n";
echo "Passed: {$passedTests}\n";
echo "Failed: " . ($totalTests - $passedTests) . "\n";
echo "Success rate: " . round(($passedTests / $totalTests) * 100, 2) . "%\n\n";

if (!empty($errors)) {
    echo "🚨 ERRORS FOUND:\n";
    foreach ($errors as $error) {
        echo "❌ {$error}\n";
    }
} else {
    echo "✅ NO ERRORS DETECTED!\n";
}

echo "\n🎯 RECOMMENDATIONS:\n";
echo "1. Fix template literal syntax errors\n";
echo "2. Ensure all functions are properly closed\n";
echo "3. Verify navigation handlers are bound correctly\n";
echo "4. Check content generators for all pages\n";
echo "5. Test dynamic content loading\n\n";

echo "🚀 COMPREHENSIVE TEST COMPLETE!\n";
?>
