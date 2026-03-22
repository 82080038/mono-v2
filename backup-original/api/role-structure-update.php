<?php
/**
 * Role Structure Update - Implement 9-Level Role System
 */

define('KSP_API_ACCESS', true);

require_once 'DatabaseHelper.php';
require_once 'AuthHelper.php';
require_once 'SecurityHelper.php';

try {
    $db = DatabaseHelper::getInstance();
    $pdo = $db->getConnection();
    
    // Enable buffered queries to avoid issues
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    
    echo "🚀 Starting Role Structure Update...\n\n";
    
    // 1. Create necessary tables first
    echo "📋 Step 1: Creating role tables...\n";
    
    // Create role_definitions table
    $createRoleDefs = "
        CREATE TABLE IF NOT EXISTS role_definitions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role_key VARCHAR(50) UNIQUE NOT NULL,
            role_name VARCHAR(100) NOT NULL,
            role_description TEXT,
            level_order INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    $pdo->exec($createRoleDefs);
    echo "✅ role_definitions table ready\n";
    
    // Create permissions table
    $createPerms = "
        CREATE TABLE IF NOT EXISTS permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            permission_key VARCHAR(100) UNIQUE NOT NULL,
            permission_name VARCHAR(200) NOT NULL,
            permission_description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    $pdo->exec($createPerms);
    echo "✅ permissions table ready\n";
    
    // Create role_permissions table
    $createRolePerms = "
        CREATE TABLE IF NOT EXISTS role_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role_key VARCHAR(50) NOT NULL,
            permission_key VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_role_perm (role_key, permission_key)
        )
    ";
    $pdo->exec($createRolePerms);
    echo "✅ role_permissions table ready\n";
    
    // Create role_hierarchy table
    $createHierarchy = "
        CREATE TABLE IF NOT EXISTS role_hierarchy (
            id INT AUTO_INCREMENT PRIMARY KEY,
            parent_role VARCHAR(50) NOT NULL,
            child_role VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hierarchy (parent_role, child_role)
        )
    ";
    $pdo->exec($createHierarchy);
    echo "✅ role_hierarchy table ready\n";
    
    // 2. Update users table with new role structure
    echo "\n📋 Step 2: Updating users table...\n";
    
    $updateUsers = "
        UPDATE users SET 
            role = CASE 
                WHEN role = 'creator' THEN 'creator'
                WHEN role = 'owner' THEN 'owner' 
                WHEN role = 'admin' THEN 'general_manager'
                WHEN role = 'super_admin' THEN 'it_manager'
                WHEN role = 'manager' THEN 'supervisor'
                WHEN role = 'teller' THEN 'teller'
                WHEN role = 'staff' THEN 'field_officer'
                WHEN role = 'member' THEN 'member'
                ELSE 'member'
            END
        WHERE 1=1
    ";
    
    $pdo->exec($updateUsers);
    echo "✅ Users table updated\n";
    
    // 3. Insert new role definitions
    echo "\n📋 Step 3: Creating role definitions...\n";
    
    // Clear existing roles first
    $pdo->exec("DELETE FROM role_definitions WHERE 1=1");
    
    $roles = [
        ['creator', 'Creator', 'System Creator', 0],
        ['owner', 'Owner', 'Business Owner', 1],
        ['general_manager', 'General Manager', 'Operational Manager', 2],
        ['it_manager', 'IT Manager', 'Technical Manager', 3],
        ['finance_manager', 'Finance Manager', 'Financial Manager', 4],
        ['supervisor', 'Supervisor', 'Field Supervisor', 5],
        ['teller', 'Teller', 'Counter Teller', 6],
        ['field_officer', 'Field Officer', 'Door-to-Door Officer', 7],
        ['member', 'Member', 'Customer Member', 8]
    ];
    
    foreach ($roles as $role) {
        $insertRole = $pdo->prepare("INSERT INTO role_definitions (role_key, role_name, role_description, level_order) VALUES (?, ?, ?, ?)");
        $insertRole->execute($role);
        echo "✅ Created role: {$role[1]}\n";
    }
    
    echo "\n📋 Step 4: Creating permission matrix...\n";
    
    // Clear existing permissions first
    $pdo->exec("DELETE FROM permissions WHERE 1=1");
    $pdo->exec("DELETE FROM role_permissions WHERE 1=1");
    
    // 4. Create comprehensive permission matrix
    $permissions = [
        // Database & System Management
        ['database_access', 'Database Access', ['creator', 'it_manager']],
        ['system_config', 'System Configuration', ['creator', 'it_manager']],
        ['api_management', 'API Management', ['creator', 'it_manager']],
        ['security_settings', 'Security Settings', ['creator', 'it_manager']],
        
        // Business Management
        ['business_control', 'Business Control', ['owner', 'general_manager']],
        ['strategic_planning', 'Strategic Planning', ['owner', 'general_manager']],
        ['budget_planning', 'Budget Planning', ['owner', 'finance_manager']],
        ['revenue_analytics', 'Revenue Analytics', ['owner', 'general_manager', 'finance_manager']],
        
        // User & Role Management
        ['user_management', 'User Management', ['creator', 'owner', 'general_manager', 'it_manager']],
        ['role_management', 'Role Management', ['creator', 'owner']],
        ['staff_management', 'Staff Management', ['general_manager', 'supervisor']],
        ['member_management', 'Member Management', ['general_manager', 'supervisor', 'teller', 'field_officer']],
        
        // Financial Management
        ['financial_overview', 'Financial Overview', ['owner', 'general_manager', 'finance_manager']],
        ['loan_approval', 'Loan Approval', ['owner', 'general_manager', 'finance_manager']],
        ['loan_disbursement', 'Loan Disbursement', ['finance_manager', 'teller']],
        ['savings_management', 'Savings Management', ['finance_manager', 'teller', 'field_officer']],
        ['payment_processing', 'Payment Processing', ['finance_manager', 'teller', 'field_officer']],
        
        // Field Operations
        ['gps_tracking', 'GPS Tracking', ['general_manager', 'supervisor', 'field_officer']],
        ['route_planning', 'Route Planning', ['supervisor', 'field_officer']],
        ['visit_management', 'Visit Management', ['supervisor', 'field_officer']],
        ['target_monitoring', 'Target Monitoring', ['general_manager', 'supervisor']],
        
        // Security & Compliance
        ['security_monitoring', 'Security Monitoring', ['creator', 'it_manager']],
        ['audit_logs', 'Audit Logs', ['creator', 'owner', 'general_manager', 'it_manager', 'finance_manager']],
        ['risk_assessment', 'Risk Assessment', ['owner', 'general_manager', 'finance_manager', 'it_manager']],
        ['fraud_detection', 'Fraud Detection', ['owner', 'general_manager', 'finance_manager', 'it_manager']],
        
        // Reporting & Analytics
        ['business_intelligence', 'Business Intelligence', ['owner', 'general_manager', 'finance_manager']],
        ['financial_reports', 'Financial Reports', ['owner', 'general_manager', 'finance_manager']],
        ['operational_reports', 'Operational Reports', ['general_manager', 'supervisor', 'field_officer']],
        ['system_analytics', 'System Analytics', ['creator', 'it_manager']],
        
        // AI & Advanced Features
        ['ai_risk_assessment', 'AI Risk Assessment', ['general_manager', 'finance_manager', 'it_manager']],
        ['predictive_analytics', 'Predictive Analytics', ['owner', 'general_manager', 'it_manager']],
        ['smart_recommendations', 'Smart Recommendations', ['general_manager', 'finance_manager', 'it_manager']],
        
        // Member Access
        ['personal_account', 'Personal Account', ['member']],
        ['loan_application', 'Loan Application', ['member', 'field_officer']],
        ['member_savings', 'Member Savings', ['member', 'teller', 'field_officer']],
        ['transaction_history', 'Transaction History', ['member', 'teller', 'field_officer']]
    ];
    
    foreach ($permissions as $permission) {
        $insertPerm = $pdo->prepare("INSERT INTO permissions (permission_key, permission_name) VALUES (?, ?)");
        $insertPerm->execute([$permission[0], $permission[1]]);
        
        // Assign to roles
        foreach ($permission[2] as $roleKey) {
            $assignPerm = $pdo->prepare("INSERT INTO role_permissions (role_key, permission_key) VALUES (?, ?)");
            $assignPerm->execute([$roleKey, $permission[0]]);
        }
        echo "✅ Created permission: {$permission[1]}\n";
    }
    
    echo "\n📋 Step 5: Updating login credentials...\n";
    
    // 5. Update login credentials for new roles
    $credentials = [
        ['creator', 'creator123', 'creator'],
        ['owner', 'owner123', 'owner'],
        ['gm', 'gm123', 'general_manager'],
        ['itmanager', 'itmanager123', 'it_manager'],
        ['financemgr', 'financemgr123', 'finance_manager'],
        ['supervisor', 'supervisor123', 'supervisor'],
        ['teller', 'teller123', 'teller'],
        ['fieldofficer', 'fieldofficer123', 'field_officer']
    ];
    
    foreach ($credentials as $cred) {
        $checkUser = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $checkUser->execute([$cred[0]]);
        $checkUser->fetch(); // Fetch to clear buffer
        
        if ($checkUser->fetchColumn() == 0) {
            $insertUser = $pdo->prepare("INSERT INTO users (username, email, password, role, status, created_at) VALUES (?, ?, ?, ?, 'Active', NOW())");
            $insertUser->execute([$cred[0], $cred[0] . '@example.com', password_hash($cred[1], PASSWORD_DEFAULT), $cred[2]]);
            echo "✅ Created user: {$cred[0]} ({$cred[2]})\n";
        } else {
            // User exists, just show info
            echo "ℹ️  User exists: {$cred[0]} ({$cred[2]})\n";
        }
    }
    
    echo "\n📋 Step 6: Creating role hierarchy...\n";
    
    // Clear existing hierarchy first
    $pdo->exec("DELETE FROM role_hierarchy WHERE 1=1");
    
    // 6. Create role hierarchy for easy access control
    $hierarchy = [
        ['creator', 'owner'],
        ['owner', 'general_manager'],
        ['owner', 'it_manager'],
        ['owner', 'finance_manager'],
        ['general_manager', 'supervisor'],
        ['general_manager', 'teller'],
        ['supervisor', 'field_officer'],
        ['field_officer', 'member'],
        ['teller', 'member']
    ];
    
    foreach ($hierarchy as $h) {
        $checkH = $pdo->prepare("SELECT COUNT(*) FROM role_hierarchy WHERE parent_role = ? AND child_role = ?");
        $checkH->execute([$h[0], $h[1]]);
        $checkH->fetch(); // Fetch to clear buffer
        
        if ($checkH->fetchColumn() == 0) {
            $insertH = $pdo->prepare("INSERT INTO role_hierarchy (parent_role, child_role) VALUES (?, ?)");
            $insertH->execute($h);
            echo "✅ Created hierarchy: {$h[0]} → {$h[1]}\n";
        }
    }
    
    echo "\n🎉 Role Structure Update Completed Successfully!\n";
    echo "📊 Summary:\n";
    echo "• 9 Roles implemented\n";
    echo "• " . count($permissions) . " Permissions created\n";
    echo "• " . count($credentials) . " User credentials updated\n";
    echo "• Role hierarchy established\n";
    echo "• Permission matrix configured\n\n";
    
    echo "🔑 New Login Credentials:\n";
    foreach ($credentials as $cred) {
        echo "• {$cred[0]} / {$cred[1]} ({$cred[2]})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
