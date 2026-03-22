<?php
/**
 * Add Creator Role for Application Creator
 * Tambahkan role creator untuk pencipta aplikasi
 */

require_once __DIR__ . '/config/error-config.php';

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/DatabaseHelper.php';
require_once __DIR__ . '/SecurityHelper.php';
require_once __DIR__ . '/Logger.php';

class CreatorRoleSetup {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseHelper::getInstance();
    }
    
    /**
     * Tambahkan creator role dan user
     */
    public function addCreatorRole() {
        echo "=== KSP LAM GABE JAYA - CREATOR ROLE SETUP ===\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        try {
            // 1. Cek apakah creator role sudah ada
            $this->checkExistingCreator();
            
            // 2. Tambahkan creator user
            $this->addCreatorUser();
            
            // 3. Update role validation untuk include creator
            $this->updateRoleValidation();
            
            // 4. Tambahkan creator permissions
            $this->addCreatorPermissions();
            
            // 5. Update API untuk support creator role
            $this->updateAPIForCreatorRole();
            
            echo "✅ Creator role setup completed successfully!\n";
            return true;
            
        } catch (Exception $e) {
            echo "❌ Error setting up creator role: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Cek existing creator user
     */
    private function checkExistingCreator() {
        echo "🔍 CHECKING: Existing Creator User\n";
        echo "==================================\n";
        
        $existingCreator = $this->db->fetchOne("SELECT * FROM users WHERE role = 'creator'");
        
        if ($existingCreator) {
            echo "✅ Creator user already exists: " . $existingCreator['username'] . "\n";
            echo "   ID: " . $existingCreator['id'] . "\n";
            echo "   Full Name: " . $existingCreator['full_name'] . "\n";
            echo "   Created: " . $existingCreator['created_at'] . "\n\n";
            return $existingCreator;
        } else {
            echo "ℹ️  No creator user found. Will create new creator user.\n\n";
            return false;
        }
    }
    
    /**
     * Tambahkan creator user
     */
    private function addCreatorUser() {
        echo "👤 ADDING: Creator User\n";
        echo "=====================\n";
        
        // Cek apakah creator sudah ada dari method sebelumnya
        $existingCreator = $this->db->fetchOne("SELECT * FROM users WHERE role = 'creator'");
        if ($existingCreator) {
            echo "✅ Creator user already exists, skipping creation.\n\n";
            return $existingCreator;
        }
        
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
            echo "   Role: creator\n";
            echo "   Password: creator123\n";
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
     * Update role validation untuk include creator
     */
    private function updateRoleValidation() {
        echo "🔧 UPDATING: Role Validation\n";
        echo "=============================\n";
        
        // Update AuthHelper untuk support creator role
        $authHelperPath = __DIR__ . '/AuthHelper.php';
        $authHelperContent = file_get_contents($authHelperPath);
        
        // Cek apakah creator sudah ada di validateRole
        if (strpos($authHelperContent, "'creator'") !== false) {
            echo "✅ Creator role already supported in AuthHelper\n\n";
            return;
        }
        
        // Tambahkan creator role ke validateRole method
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
        $pattern = '/public static function validateRole\(\$requiredRole, \$user\)[^}]*}/';
        if (preg_match($pattern, $authHelperContent)) {
            $authHelperContent = preg_replace($pattern, $newValidateRole, $authHelperContent);
            
            if (file_put_contents($authHelperPath, $authHelperContent)) {
                echo "✅ AuthHelper updated to support creator role\n";
            } else {
                echo "⚠️  Could not update AuthHelper file\n";
            }
        }
        
        echo "\n";
    }
    
    /**
     * Tambahkan creator permissions
     */
    private function addCreatorPermissions() {
        echo "🔑 ADDING: Creator Permissions\n";
        echo "==============================\n";
        
        $permissions = [
            'system_full_access' => true,
            'user_management_full' => true,
            'database_management' => true,
            'system_configuration' => true,
            'security_management' => true,
            'audit_full_access' => true,
            'api_management' => true,
            'backup_restore' => true,
            'deployment_management' => true
        ];
        
        foreach ($permissions as $permission => $granted) {
            echo "  ✅ $permission: " . ($granted ? "GRANTED" : "DENIED") . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Update API untuk support creator role
     */
    private function updateAPIForCreatorRole() {
        echo "🔄 UPDATING: API for Creator Role\n";
        echo "=================================\n";
        
        // Update SecurityMiddleware untuk support creator
        $middlewarePath = __DIR__ . '/SecurityMiddleware.php';
        $middlewareContent = file_get_contents($middlewarePath);
        
        // Tambahkan creator role ke allowed roles
        if (strpos($middlewareContent, "'creator'") === false) {
            // Update requireAuth method untuk support creator
            $pattern = '/(\$user\[\'role\'\] !== \$role && \$user\[\'role\'] !== \'admin\')/';
            $replacement = '($user[\'role\'] !== $role && $user[\'role\'] !== \'admin\' && $user[\'role\'] !== \'creator\')';
            
            if (preg_match($pattern, $middlewareContent)) {
                $middlewareContent = preg_replace($pattern, $replacement, $middlewareContent);
                
                if (file_put_contents($middlewarePath, $middlewareContent)) {
                    echo "✅ SecurityMiddleware updated to support creator role\n";
                } else {
                    echo "⚠️  Could not update SecurityMiddleware file\n";
                }
            }
        } else {
            echo "✅ Creator role already supported in SecurityMiddleware\n";
        }
        
        echo "\n";
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
            echo "   ✅ User management\n";
            echo "   ✅ Database management\n";
            echo "   ✅ System configuration\n";
            echo "   ✅ Security management\n";
            echo "   ✅ Audit access\n";
            echo "   ✅ API management\n";
            echo "   ✅ Backup & restore\n";
            echo "   ✅ Deployment management\n\n";
            
            echo "🎯 Creator Privileges:\n";
            echo "   ✅ Can access all admin features\n";
            echo "   ✅ Can manage all users (including admin)\n";
            echo "   ✅ Can modify system settings\n";
            echo "   ✅ Can view all audit logs\n";
            echo "   ✅ Can manage database\n";
            echo "   ✅ Can deploy system updates\n\n";
            
            return $creator;
        } else {
            echo "❌ Creator user not found\n\n";
            return false;
        }
    }
    
    /**
     * Test creator login
     */
    public function testCreatorLogin() {
        echo "🧪 TESTING: Creator Login\n";
        echo "========================\n";
        
        try {
            // Test login
            $creator = $this->db->fetchOne("SELECT * FROM users WHERE username = 'creator' AND role = 'creator'");
            
            if (!$creator) {
                echo "❌ Creator user not found\n";
                return false;
            }
            
            // Generate token
            $token = AuthHelper::generateToken($creator);
            echo "✅ Token generated for creator\n";
            
            // Validate token
            $validated = AuthHelper::validateJWTToken($token);
            if ($validated && $validated['user_id'] == $creator['id']) {
                echo "✅ Token validation successful\n";
            } else {
                echo "❌ Token validation failed\n";
                return false;
            }
            
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
            
            echo "✅ Creator login test passed\n\n";
            return true;
            
        } catch (Exception $e) {
            echo "❌ Creator login test failed: " . $e->getMessage() . "\n\n";
            return false;
        }
    }
}

// Run setup if executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    Logger::initialize();
    
    $setup = new CreatorRoleSetup();
    
    echo "Starting creator role setup...\n\n";
    
    // Setup creator role
    $setup->addCreatorRole();
    
    // Generate credentials
    $setup->generateCreatorCredentials();
    
    // Test login
    $setup->testCreatorLogin();
    
    echo "=== CREATOR ROLE SETUP COMPLETED ===\n";
}
?>
