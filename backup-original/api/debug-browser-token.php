<?php
/**
 * Debug Browser Token Issues
 * This script will help debug why browser tokens fail while terminal tokens work
 */

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/../config/error-config.php';
require_once __DIR__ . '/DatabaseHelper.php';

try {
    $db = DatabaseHelper::getInstance();
    $pdo = $db->getConnection();
    
    echo "🔍 DEBUGGING BROWSER TOKEN ISSUES\n";
    echo str_repeat("=", 80) . "\n\n";
    
    // Test different token scenarios
    $testScenarios = [
        'empty_token' => '',
        'null_token' => null,
        'whitespace_token' => '   ',
        'browser_btoa_token' => base64_encode(json_encode(['user_id' => 8, 'username' => 'member', 'name' => 'Test User', 'role' => 'member'])),
        'browser_btoa_with_newline' => base64_encode(json_encode(['user_id' => 8, 'username' => 'member', 'name' => 'Test User', 'role' => 'member'])) . "\n",
        'browser_btoa_with_spaces' => base64_encode(json_encode(['user_id' => 8, 'username' => 'member', 'name' => 'Test User', 'role' => 'member'])) . "  ",
        'url_encoded_token' => urlencode(base64_encode(json_encode(['user_id' => 8, 'username' => 'member', 'name' => 'Test User', 'role' => 'member']))),
        'double_encoded_token' => base64_encode(base64_encode(json_encode(['user_id' => 8, 'username' => 'member', 'name' => 'Test User', 'role' => 'member']))),
    ];
    
    foreach ($testScenarios as $scenario => $token) {
        echo "🔍 Testing Scenario: $scenario\n";
        echo "Token: " . var_export($token, true) . "\n";
        
        try {
            // Simulate API call
            $_REQUEST['action'] = 'get_dashboard';
            $_REQUEST['token'] = $token;
            
            if (empty($token)) {
                echo "❌ Empty token detected\n";
            } else {
                // Clean token (remove whitespace, newlines)
                $cleanToken = trim($token);
                echo "🧹 Cleaned token: " . substr($cleanToken, 0, 50) . "...\n";
                
                // Try to decode
                $userData = json_decode(base64_decode($cleanToken), true);
                
                if ($userData) {
                    echo "✅ Token decoded successfully\n";
                    echo "  👤 User ID: " . ($userData['user_id'] ?? 'N/A') . "\n";
                    echo "  👤 Username: " . ($userData['username'] ?? 'N/A') . "\n";
                    echo "  👤 Name: " . ($userData['name'] ?? 'N/A') . "\n";
                    echo "  🎭 Role: " . ($userData['role'] ?? 'N/A') . "\n";
                    
                    if (isset($userData['role'])) {
                        echo "✅ Role found: " . $userData['role'] . "\n";
                        
                        // Test database query
                        $stmt = $pdo->prepare("
                            SELECT COUNT(*) as count
                            FROM dashboard_pages dp
                            JOIN role_dashboard_pages rdp ON dp.page_key = rdp.page_key
                            WHERE rdp.role_key = ? AND rdp.is_visible = 1 AND dp.is_active = 1
                        ");
                        $stmt->execute([$userData['role']]);
                        $count = $stmt->fetchColumn();
                        
                        echo "📊 Dashboard pages available: $count\n";
                    } else {
                        echo "❌ Role missing in token\n";
                    }
                } else {
                    echo "❌ Failed to decode token\n";
                    echo "  Base64 decode error\n";
                    echo "  JSON decode error\n";
                }
            }
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n" . str_repeat("-", 80) . "\n\n";
    }
    
    // Test actual browser token format
    echo "🌐 TESTING ACTUAL BROWSER TOKEN FORMAT\n";
    echo str_repeat("-", 80) . "\n";
    
    // Simulate browser btoa() behavior
    $browserToken = base64_encode(json_encode([
        'user_id' => 8,
        'username' => 'member',
        'name' => 'Test User',
        'role' => 'member'
    ]));
    
    echo "Browser btoa() token: $browserToken\n";
    echo "Token length: " . strlen($browserToken) . "\n";
    echo "Token contains spaces: " . (strpos($browserToken, ' ') !== false ? 'Yes' : 'No') . "\n";
    echo "Token contains newlines: " . (strpos($browserToken, "\n") !== false ? 'Yes' : 'No') . "\n";
    
    // Test URL encoding
    $urlEncodedToken = urlencode($browserToken);
    echo "URL encoded token: $urlEncodedToken\n";
    
    // Test API call with browser token
    echo "\n📋 Testing API with browser token:\n";
    $_REQUEST['action'] = 'get_dashboard';
    $_REQUEST['token'] = $browserToken;
    
    try {
        $userData = json_decode(base64_decode($browserToken), true);
        if ($userData && isset($userData['role'])) {
            echo "✅ API call successful with browser token\n";
            echo "  🎭 Role: {$userData['role']}\n";
        } else {
            echo "❌ API call failed with browser token\n";
        }
    } catch (Exception $e) {
        echo "❌ API call error: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎯 DEBUGGING RECOMMENDATIONS:\n";
    echo str_repeat("-", 80) . "\n";
    echo "1. Check browser console for token content\n";
    echo "2. Verify token is properly stored in localStorage\n";
    echo "3. Check for whitespace or newline characters in token\n";
    echo "4. Verify token is properly URL encoded in API calls\n";
    echo "5. Check browser network tab for actual API requests\n";
    echo "6. Verify token is not modified by browser extensions\n";
    
    echo "\n🔧 BROWSER DEBUGGING STEPS:\n";
    echo "1. Open browser console\n";
    echo "2. Run: localStorage.getItem('authToken')\n";
    echo "3. Run: atob(localStorage.getItem('authToken'))\n";
    echo "4. Run: JSON.parse(atob(localStorage.getItem('authToken')))\n";
    echo "5. Check if role field exists\n";
    
    echo "\n🎉 DEBUGGING COMPLETED!\n";
    
} catch (Exception $e) {
    echo "❌ Debug failed: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
