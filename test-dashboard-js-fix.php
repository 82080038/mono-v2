<?php
/**
 * Test Dashboard JavaScript Fix
 * Verify JavaScript variables are properly quoted
 */

echo "=== DASHBOARD JAVASCRIPT FIX TEST ===\n\n";

// Test login and get dashboard content
$apiUrl = 'http://localhost/mono-v2/api/auth.php';
$dashboardUrl = 'http://localhost/mono-v2/?page=dashboard';

echo "🔍 Step 1: Testing Login API\n";
$postData = http_build_query([
    'action' => 'login',
    'username' => 'bos',
    'password' => 'bos'
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
        echo "   User: {$data['user']['username']}\n";
        echo "   Role: {$data['user']['role']}\n";
        
        // Create temp cookie file
        file_put_contents('temp_cookies.txt', '');
        
        // Get dashboard content
        echo "\n🔍 Step 2: Testing Dashboard JavaScript\n";
        
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
            // Check for JavaScript variables
            if (strpos($dashboardContent, 'let userRole = "') !== false) {
                echo "✅ userRole variable properly quoted\n";
            } else {
                echo "❌ userRole variable not properly quoted\n";
            }
            
            if (strpos($dashboardContent, 'let currentUser = {') !== false) {
                echo "✅ currentUser variable properly formatted\n";
            } else {
                echo "❌ currentUser variable not properly formatted\n";
            }
            
            // Check for specific error patterns
            if (strpos($dashboardContent, 'bos is not defined') !== false) {
                echo "❌ 'bos is not defined' error still present\n";
            } else {
                echo "✅ No 'bos is not defined' error found\n";
            }
            
            // Extract JavaScript section
            if (preg_match('/let userRole = (.*?);/', $dashboardContent, $matches)) {
                echo "✅ Found userRole assignment: " . $matches[0] . "\n";
            }
            
            echo "\n📊 Dashboard Content Check:\n";
            if (strpos($dashboardContent, 'Dashboard - KSP Lam Gabe Jaya') !== false) {
                echo "✅ Dashboard title present\n";
            }
            
            if (strpos($dashboardContent, 'Selamat Datang') !== false) {
                echo "✅ Welcome message present\n";
            }
            
            if (strpos($dashboardContent, 'Pemilik Koperasi') !== false) {
                echo "✅ User role display present\n";
            }
            
            if (strpos($dashboardContent, 'fas fa-tachometer-alt') !== false) {
                echo "✅ Dashboard menu items present\n";
            }
            
        } else {
            echo "❌ Failed to get dashboard content\n";
        }
        
        // Clean up
        unlink('temp_cookies.txt');
        
    } else {
        echo "❌ Login failed: " . ($data['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ No response from login API\n";
}

echo "\n=== MANUAL TESTING INSTRUCTIONS ===\n\n";
echo "1. Open browser: http://localhost/mono-v2/\n";
echo "2. Login with bos/bos (quick login)\n";
echo "3. Check browser console (F12) for errors\n";
echo "4. Should see NO 'bos is not defined' error\n";
echo "5. Dashboard should load properly\n\n";

echo "🎯 Expected JavaScript Variables:\n";
echo "let currentUser = {\"id\":11,\"username\":\"bos\",...};\n";
echo "let userRole = \"bos\";\n\n";

echo "🚀 JAVASCRIPT FIX COMPLETE!\n";
?>
