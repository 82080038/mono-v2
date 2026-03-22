<?php
/**
 * Debug Token Issues
 * This script will help debug token parsing issues
 */

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/../config/error-config.php';
require_once __DIR__ . '/DatabaseHelper.php';

try {
    $db = DatabaseHelper::getInstance();
    $pdo = $db->getConnection();
    
    echo "🔍 DEBUGGING TOKEN ISSUES\n";
    echo str_repeat("=", 60) . "\n\n";
    
    // Test different token formats
    $testTokens = [
        'empty' => '',
        'invalid' => 'invalid_token',
        'jwt_format' => 'eyJ1c2VyX2lkIjo4LCJ1c2VybmFtZSI6Im1lbWJlciIsIm5hbWUiOiJUZXN0IFVzZXIiLCJyb2xlIjoibWVtYmVyIn0=.signature.signature',
        'base64_json' => base64_encode(json_encode(['user_id' => 8, 'username' => 'member', 'name' => 'Test User', 'role' => 'member'])),
        'base64_json_with_newline' => base64_encode(json_encode(['user_id' => 8, 'username' => 'member', 'name' => 'Test User', 'role' => 'member'])) . "\n",
        'simple_string' => 'simple_token'
    ];
    
    foreach ($testTokens as $name => $token) {
        echo "🔍 Testing token format: $name\n";
        echo "Token: " . substr($token, 0, 50) . (strlen($token) > 50 ? '...' : '') . "\n";
        
        try {
            // Test token parsing
            $userData = null;
            
            if (empty($token)) {
                echo "❌ Empty token\n";
            } elseif (strpos($token, '.') !== false && count(explode('.', $token)) === 3) {
                echo "📋 JWT token format detected\n";
                $parts = explode('.', $token);
                $payload = $parts[1];
                $userData = json_decode(base64_decode($payload), true);
            } else {
                echo "📋 Simple token format detected\n";
                // Remove newlines and other whitespace
                $cleanToken = trim($token);
                $userData = json_decode(base64_decode($cleanToken), true);
            }
            
            if ($userData) {
                echo "✅ Token parsed successfully\n";
                echo "  👤 User ID: " . ($userData['user_id'] ?? 'N/A') . "\n";
                echo "  👤 Username: " . ($userData['username'] ?? 'N/A') . "\n";
                echo "  👤 Name: " . ($userData['name'] ?? 'N/A') . "\n";
                echo "  🎭 Role: " . ($userData['role'] ?? 'N/A') . "\n";
                
                if (isset($userData['role'])) {
                    echo "✅ Role found in token\n";
                } else {
                    echo "❌ Role missing in token\n";
                }
            } else {
                echo "❌ Failed to parse token\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error parsing token: " . $e->getMessage() . "\n";
        }
        
        echo "\n" . str_repeat("-", 60) . "\n\n";
    }
    
    // Test API with valid token
    echo "🌐 TESTING API WITH VALID TOKEN\n";
    echo str_repeat("-", 60) . "\n";
    
    $validToken = base64_encode(json_encode(['user_id' => 8, 'username' => 'member', 'name' => 'Test User', 'role' => 'member']));
    
    // Simulate API call
    $_REQUEST['action'] = 'get_dashboard';
    $_REQUEST['token'] = $validToken;
    
    echo "📋 Token: $validToken\n";
    echo "📋 Parsed data: " . json_encode(json_decode(base64_decode($validToken), true)) . "\n\n";
    
    // Test dashboard API logic
    try {
        $userData = json_decode(base64_decode($validToken), true);
        if ($userData && isset($userData['role'])) {
            $role = $userData['role'];
            echo "✅ Role extracted: $role\n";
            
            // Test database query
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM dashboard_pages dp
                JOIN role_dashboard_pages rdp ON dp.page_key = rdp.page_key
                WHERE rdp.role_key = ? AND rdp.is_visible = 1 AND dp.is_active = 1
            ");
            $stmt->execute([$role]);
            $count = $stmt->fetchColumn();
            
            echo "📊 Dashboard pages available: $count\n";
            
        } else {
            echo "❌ Invalid token - missing role\n";
        }
    } catch (Exception $e) {
        echo "❌ API test failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 DEBUGGING COMPLETED!\n";
    
} catch (Exception $e) {
    echo "❌ Debug failed: " . $e->getMessage() . "\n";
}
?>
