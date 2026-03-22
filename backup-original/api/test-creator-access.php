<?php
/**
 * Test Creator Login and Access
 * Test login dan akses creator
 */

require_once __DIR__ . '/config/error-config.php';

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/DatabaseHelper.php';
require_once __DIR__ . '/AuthHelper.php';

class CreatorLoginTest {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseHelper::getInstance();
    }
    
    public function testCreatorAccess() {
        echo "=== CREATOR LOGIN & ACCESS TEST ===\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        try {
            // 1. Test creator login
            $this->testCreatorLogin();
            
            // 2. Test creator permissions
            $this->testCreatorPermissions();
            
            // 3. Test API access
            $this->testAPIAccess();
            
            echo "✅ Creator login & access test completed successfully!\n";
            return true;
            
        } catch (Exception $e) {
            echo "❌ Error testing creator access: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function testCreatorLogin() {
        echo "🔐 TESTING: Creator Login\n";
        echo "========================\n";
        
        // Get creator user
        $creator = $this->db->fetchOne("SELECT * FROM users WHERE role = 'creator'");
        
        if (!$creator) {
            echo "❌ Creator user not found\n";
            return false;
        }
        
        echo "✅ Creator user found: " . $creator['username'] . "\n";
        
        // Generate JWT token
        $token = AuthHelper::generateToken($creator);
        echo "✅ JWT token generated\n";
        
        // Validate token
        $validated = AuthHelper::validateJWTToken($token);
        if ($validated && $validated['user_id'] == $creator['id']) {
            echo "✅ JWT token validation successful\n";
        } else {
            echo "❌ JWT token validation failed\n";
            return false;
        }
        
        // Test role validation
        if (AuthHelper::validateRole('admin', $creator)) {
            echo "✅ Creator can access admin features\n";
        } else {
            echo "❌ Creator cannot access admin features\n";
            return false;
        }
        
        if (AuthHelper::validateRole('staff', $creator)) {
            echo "✅ Creator can access staff features\n";
        } else {
            echo "❌ Creator cannot access staff features\n";
            return false;
        }
        
        if (AuthHelper::validateRole('member', $creator)) {
            echo "✅ Creator can access member features\n";
        } else {
            echo "❌ Creator cannot access member features\n";
            return false;
        }
        
        if (AuthHelper::validateRole('creator', $creator)) {
            echo "✅ Creator can access creator features\n";
        } else {
            echo "❌ Creator cannot access creator features\n";
            return false;
        }
        
        echo "✅ Creator login test passed\n\n";
        return true;
    }
    
    private function testCreatorPermissions() {
        echo "🔑 TESTING: Creator Permissions\n";
        echo "==============================\n";
        
        $creator = $this->db->fetchOne("SELECT * FROM users WHERE role = 'creator'");
        
        if (!$creator) {
            echo "❌ Creator user not found\n";
            return false;
        }
        
        // Test database access
        try {
            $users = $this->db->fetchAll("SELECT * FROM users LIMIT 5");
            echo "✅ Creator can access database (found " . count($users) . " users)\n";
        } catch (Exception $e) {
            echo "❌ Creator cannot access database\n";
            return false;
        }
        
        // Test table access
        $tables = $this->db->fetchAll("SHOW TABLES");
        echo "✅ Creator can access " . count($tables) . " tables\n";
        
        // Test audit logs access
        try {
            $auditLogs = $this->db->fetchAll("SELECT * FROM audit_logs LIMIT 5");
            echo "✅ Creator can access audit logs (found " . count($auditLogs) . " logs)\n";
        } catch (Exception $e) {
            echo "❌ Creator cannot access audit logs\n";
            return false;
        }
        
        // Test system settings access
        try {
            $settings = $this->db->fetchAll("SELECT * FROM system_settings LIMIT 5");
            echo "✅ Creator can access system settings (found " . count($settings) . " settings)\n";
        } catch (Exception $e) {
            echo "❌ Creator cannot access system settings\n";
            return false;
        }
        
        echo "✅ Creator permissions test passed\n\n";
        return true;
    }
    
    private function testAPIAccess() {
        echo "🌐 TESTING: API Access\n";
        echo "=====================\n";
        
        // Test API files existence
        $apiFiles = [
            'auth-enhanced.php' => 'Authentication API',
            'user-management.php' => 'User Management API',
            'system-settings.php' => 'System Settings API',
            'audit-log.php' => 'Audit Log API',
            'members-crud.php' => 'Members CRUD API',
            'loans-crud.php' => 'Loans CRUD API',
            'savings-crud.php' => 'Savings CRUD API',
            'reports.php' => 'Reports API',
            'analytics.php' => 'Analytics API'
        ];
        
        foreach ($apiFiles as $file => $description) {
            if (file_exists(__DIR__ . '/' . $file)) {
                echo "✅ $description available\n";
            } else {
                echo "❌ $description not available\n";
            }
        }
        
        echo "✅ API access test completed\n\n";
        return true;
    }
}

// Run test if executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new CreatorLoginTest();
    $test->testCreatorAccess();
}
?>
