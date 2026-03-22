<?php
/**
 * Update Database Schema for Creator Role
 * Update struktur database untuk support creator role
 */

require_once __DIR__ . '/config/error-config.php';

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/DatabaseHelper.php';

class DatabaseSchemaUpdate {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseHelper::getInstance();
    }
    
    /**
     * Update database schema untuk support creator role
     */
    public function updateSchemaForCreator() {
        echo "=== DATABASE SCHEMA UPDATE FOR CREATOR ROLE ===\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        try {
            // 1. Update users table untuk support creator role
            $this->updateUsersTable();
            
            // 2. Tambahkan creator user
            $this->addCreatorUser();
            
            // 3. Update API untuk support creator role
            $this->updateAPIForCreator();
            
            // 4. Test creator role
            $this->testCreatorRole();
            
            echo "✅ Database schema update completed successfully!\n";
            return true;
            
        } catch (Exception $e) {
            echo "❌ Error updating database schema: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Update users table untuk support creator role
     */
    private function updateUsersTable() {
        echo "🔧 UPDATING: Users Table Schema\n";
        echo "===============================\n";
        
        // Cek current enum values
        $currentEnum = $this->getCurrentRoleEnum();
        echo "   Current role enum: $currentEnum\n";
        
        // Update enum untuk include creator
        $newEnum = "enum('admin','staff','member','creator')";
        
        try {
            $this->db->query("ALTER TABLE users MODIFY COLUMN role $newEnum DEFAULT 'member'");
            echo "✅ Users table updated to support creator role\n";
            
            // Verify update
            $updatedEnum = $this->getCurrentRoleEnum();
            echo "   Updated role enum: $updatedEnum\n";
            
        } catch (Exception $e) {
            echo "❌ Failed to update users table: " . $e->getMessage() . "\n";
            throw $e;
        }
        
        echo "\n";
    }
    
    /**
     * Get current role enum values
     */
    private function getCurrentRoleEnum() {
        try {
            $result = $this->db->fetchOne("SHOW COLUMNS FROM users WHERE Field = 'role'");
            return $result['Type'];
        } catch (Exception $e) {
            return "Unknown";
        }
    }
    
    /**
     * Tambahkan creator user
     */
    private function addCreatorUser() {
        echo "👤 ADDING: Creator User\n";
        echo "=====================\n";
        
        // Cek apakah creator sudah ada
        $existingCreator = $this->db->fetchOne("SELECT * FROM users WHERE role = 'creator'");
        
        if ($existingCreator) {
            echo "✅ Creator user already exists: " . $existingCreator['username'] . "\n";
            echo "   ID: " . $existingCreator['id'] . "\n";
            echo "   Full Name: " . $existingCreator['full_name'] . "\n\n";
            return $existingCreator;
        }
        
        require_once __DIR__ . '/SecurityHelper.php';
        
        // Data creator user
        $creatorData = [
            'username' => 'creator',
            'email' => 'creator@ksp-lamgabejaya.com',
            'password' => SecurityHelper::hashPassword('creator123'),
            'full_name' => 'Application Creator',
            'role' => 'creator',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert creator user
        $creatorId = $this->db->insert('users', $creatorData);
        
        if ($creatorId) {
            echo "✅ Creator user created successfully!\n";
            echo "   ID: $creatorId\n";
            echo "   Username: creator\n";
            echo "   Email: creator@ksp-lamgabejaya.com\n";
            echo "   Password: creator123\n";
            echo "   Role: creator\n";
            echo "   Full Name: Application Creator\n\n";
            
            // Log creation
            $this->db->insert('audit_logs', [
                'user_id' => $creatorId,
                'action' => 'creator_user_created',
                'table_name' => 'users',
                'description' => 'Creator user account created for application owner',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'System',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            return $creatorId;
        } else {
            throw new Exception("Failed to create creator user");
        }
    }
    
    /**
     * Update API untuk support creator role
     */
    private function updateAPIForCreator() {
        echo "🔄 UPDATING: API for Creator Role\n";
        echo "=================================\n";
        
        // Update AuthHelper
        $this->updateAuthHelper();
        
        // Update SecurityMiddleware
        $this->updateSecurityMiddleware();
        
        // Update other API files
        $this->updateOtherAPIs();
        
        echo "\n";
    }
    
    /**
     * Update AuthHelper untuk support creator
     */
    private function updateAuthHelper() {
        echo "   📝 Updating AuthHelper...\n";
        
        $authHelperPath = __DIR__ . '/AuthHelper.php';
        $authHelperContent = file_get_contents($authHelperPath);
        
        // Update validateRole method
        $newValidateRole = '
    /**
     * Validate user role
     * @param string $requiredRole Required role
     * @param array $user User data
     * @return bool User has required role
     */
    public static function validateRole($requiredRole, $user) {
        // Creator has access to everything
        if ($user[\'role\'] === \'creator\') {
            return true;
        }
        
        // Admin has access to everything except creator-only features
        if ($user[\'role\'] === \'admin\' && $requiredRole !== \'creator\') {
            return true;
        }
        
        return $user[\'role\'] === $requiredRole;
    }';
        
        // Replace existing validateRole method
        $pattern = '/public static function validateRole\(\$requiredRole, \$user\)[^}]*}/s';
        if (preg_match($pattern, $authHelperContent)) {
            $authHelperContent = preg_replace($pattern, $newValidateRole, $authHelperContent);
            
            if (file_put_contents($authHelperPath, $authHelperContent)) {
                echo "     ✅ AuthHelper updated\n";
            } else {
                echo "     ⚠️  Could not update AuthHelper file\n";
            }
        } else {
            echo "     ⚠️  validateRole method not found in AuthHelper\n";
        }
    }
    
    /**
     * Update SecurityMiddleware untuk support creator
     */
    private function updateSecurityMiddleware() {
        echo "   📝 Updating SecurityMiddleware...\n";
        
        $middlewarePath = __DIR__ . '/SecurityMiddleware.php';
        $middlewareContent = file_get_contents($middlewarePath);
        
        // Update requireAuth method untuk support creator
        $pattern = '/(\$user\[\'role\'] !== \$role && \$user\[\'role\'] !== \'admin\')/';
        $replacement = '($user[\'role\'] !== $role && $user[\'role\'] !== \'admin\' && $user[\'role\'] !== \'creator\')';
        
        if (preg_match($pattern, $middlewareContent)) {
            $middlewareContent = preg_replace($pattern, $replacement, $middlewareContent);
            
            if (file_put_contents($middlewarePath, $middlewareContent)) {
                echo "     ✅ SecurityMiddleware updated\n";
            } else {
                echo "     ⚠️  Could not update SecurityMiddleware file\n";
            }
        } else {
            echo "     ⚠️  Role validation pattern not found in SecurityMiddleware\n";
        }
    }
    
    /**
     * Update other API files
     */
    private function updateOtherAPIs() {
        echo "   📝 Updating other API files...\n";
        
        // Update user-management.php untuk support creator
        $this->updateUserManagementAPI();
        
        // Update system-settings.php untuk support creator
        $this->updateSystemSettingsAPI();
        
        echo "     ✅ Other API files updated\n";
    }
    
    /**
     * Update user-management API
     */
    private function updateUserManagementAPI() {
        $apiPath = __DIR__ . '/user-management.php';
        if (file_exists($apiPath)) {
            $content = file_get_contents($apiPath);
            
            // Add creator to allowed roles
            if (strpos($content, "'admin'") !== false && strpos($content, "'creator'") === false) {
                $content = str_replace("'admin'", "'admin', 'creator'", $content);
                file_put_contents($apiPath, $content);
                echo "     ✅ User Management API updated\n";
            }
        }
    }
    
    /**
     * Update system-settings API
     */
    private function updateSystemSettingsAPI() {
        $apiPath = __DIR__ . '/system-settings.php';
        if (file_exists($apiPath)) {
            $content = file_get_contents($apiPath);
            
            // Add creator to allowed roles
            if (strpos($content, "'admin'") !== false && strpos($content, "'creator'") === false) {
                $content = str_replace("'admin'", "'admin', 'creator'", $content);
                file_put_contents($apiPath, $content);
                echo "     ✅ System Settings API updated\n";
            }
        }
    }
    
    /**
     * Test creator role
     */
    private function testCreatorRole() {
        echo "🧪 TESTING: Creator Role\n";
        echo "=====================\n";
        
        try {
            require_once __DIR__ . '/AuthHelper.php';
            
            // Test creator user
            $creator = $this->db->fetchOne("SELECT * FROM users WHERE role = 'creator'");
            
            if (!$creator) {
                echo "❌ Creator user not found\n";
                return false;
            }
            
            echo "✅ Creator user found: " . $creator['username'] . "\n";
            
            // Test role validation
            if (AuthHelper::validateRole('admin', $creator)) {
                echo "✅ Creator can access admin features\n";
            } else {
                echo "❌ Creator cannot access admin features\n";
                return false;
            }
            
            if (AuthHelper::validateRole('creator', $creator)) {
                echo "✅ Creator can access creator features\n";
            } else {
                echo "❌ Creator cannot access creator features\n";
                return false;
            }
            
            echo "✅ Creator role test passed\n\n";
            return true;
            
        } catch (Exception $e) {
            echo "❌ Creator role test failed: " . $e->getMessage() . "\n\n";
            return false;
        }
    }
    
    /**
     * Generate creator credentials
     */
    public function generateCreatorCredentials() {
        echo "📋 CREATOR CREDENTIALS\n";
        echo "====================\n";
        
        $creator = $this->db->fetchOne("SELECT * FROM users WHERE role = 'creator'");
        
        if ($creator) {
            echo "👤 Creator Login Information:\n";
            echo "   Username: " . $creator['username'] . "\n";
            echo "   Email: " . $creator['email'] . "\n";
            echo "   Password: creator123\n";
            echo "   Role: " . $creator['role'] . "\n";
            echo "   Full Name: " . $creator['full_name'] . "\n";
            echo "   Status: " . ($creator['is_active'] ? 'Active' : 'Inactive') . "\n";
            echo "   User ID: " . $creator['id'] . "\n";
            echo "   Created: " . $creator['created_at'] . "\n\n";
            
            echo "🔑 Creator Permissions:\n";
            echo "   ✅ Full system access\n";
            echo "   ✅ User management (including admin)\n";
            echo "   ✅ Database management\n";
            echo "   ✅ System configuration\n";
            echo "   ✅ Security management\n";
            echo "   ✅ Audit access\n";
            echo "   ✅ API management\n";
            echo "   ✅ Backup & restore\n";
            echo "   ✅ Deployment management\n";
            echo "   ✅ All admin features\n";
            echo "   ✅ All staff features\n";
            echo "   ✅ All member features\n\n";
            
            echo "🎯 Creator Privileges:\n";
            echo "   ✅ Super admin access level\n";
            echo "   ✅ Can manage all users (including admin)\n";
            echo "   ✅ Can modify all system settings\n";
            echo "   ✅ Can view all audit logs\n";
            echo "   ✅ Can manage database structure\n";
            echo "   ✅ Can deploy system updates\n";
            echo "   ✅ Can access all APIs\n";
            echo "   ✅ Can override any restrictions\n\n";
            
            return $creator;
        } else {
            echo "❌ Creator user not found\n\n";
            return false;
        }
    }
}

// Run update if executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $update = new DatabaseSchemaUpdate();
    
    echo "Starting database schema update for creator role...\n\n";
    
    // Update schema
    $update->updateSchemaForCreator();
    
    // Generate credentials
    $update->generateCreatorCredentials();
    
    echo "=== DATABASE SCHEMA UPDATE COMPLETED ===\n";
}
?>
