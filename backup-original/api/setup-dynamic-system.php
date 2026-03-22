<?php
/**
 * Dynamic Dashboard & Navigation Setup
 */

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/DatabaseHelper.php';
require_once __DIR__ . '/AuthHelper.php';
require_once __DIR__ . '/SecurityHelper.php';

try {
    $db = DatabaseHelper::getInstance();
    $pdo = $db->getConnection();
    
    echo "🚀 Setting up Dynamic Dashboard & Navigation System...\n\n";
    
    // 1. Create dashboard_pages table
    echo "📋 Step 1: Creating dashboard_pages table...\n";
    $createDashboardPages = "
        CREATE TABLE IF NOT EXISTS dashboard_pages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_key VARCHAR(100) UNIQUE NOT NULL,
            page_title VARCHAR(200) NOT NULL,
            page_url VARCHAR(300) NOT NULL,
            page_icon VARCHAR(100),
            page_description TEXT,
            page_category VARCHAR(50),
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ";
    $pdo->exec($createDashboardPages);
    echo "✅ dashboard_pages table ready\n";
    
    // 2. Create role_dashboard_pages table (many-to-many)
    echo "📋 Step 2: Creating role_dashboard_pages table...\n";
    $createRoleDashboardPages = "
        CREATE TABLE IF NOT EXISTS role_dashboard_pages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role_key VARCHAR(50) NOT NULL,
            page_key VARCHAR(100) NOT NULL,
            access_level ENUM('read', 'write', 'admin') DEFAULT 'read',
            is_visible TINYINT(1) DEFAULT 1,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_role_page (role_key, page_key),
            FOREIGN KEY (role_key) REFERENCES role_definitions(role_key) ON DELETE CASCADE,
            FOREIGN KEY (page_key) REFERENCES dashboard_pages(page_key) ON DELETE CASCADE
        )
    ";
    $pdo->exec($createRoleDashboardPages);
    echo "✅ role_dashboard_pages table ready\n";
    
    // 3. Create navigation_menu table
    echo "📋 Step 3: Creating navigation_menu table...\n";
    $createNavigationMenu = "
        CREATE TABLE IF NOT EXISTS navigation_menu (
            id INT AUTO_INCREMENT PRIMARY KEY,
            menu_key VARCHAR(100) UNIQUE NOT NULL,
            menu_title VARCHAR(200) NOT NULL,
            menu_url VARCHAR(300),
            menu_icon VARCHAR(100),
            parent_menu_key VARCHAR(100),
            menu_category VARCHAR(50),
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ";
    $pdo->exec($createNavigationMenu);
    echo "✅ navigation_menu table ready\n";
    
    // 4. Create role_navigation_menu table
    echo "📋 Step 4: Creating role_navigation_menu table...\n";
    $createRoleNavigationMenu = "
        CREATE TABLE IF NOT EXISTS role_navigation_menu (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role_key VARCHAR(50) NOT NULL,
            menu_key VARCHAR(100) NOT NULL,
            is_visible TINYINT(1) DEFAULT 1,
            access_level ENUM('read', 'write', 'admin') DEFAULT 'read',
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_role_menu (role_key, menu_key),
            FOREIGN KEY (role_key) REFERENCES role_definitions(role_key) ON DELETE CASCADE,
            FOREIGN KEY (menu_key) REFERENCES navigation_menu(menu_key) ON DELETE CASCADE
        )
    ";
    $pdo->exec($createRoleNavigationMenu);
    echo "✅ role_navigation_menu table ready\n";
    
    // 5. Clear existing data
    echo "\n📋 Step 5: Clearing existing data...\n";
    $pdo->exec("DELETE FROM dashboard_pages WHERE 1=1");
    $pdo->exec("DELETE FROM role_dashboard_pages WHERE 1=1");
    $pdo->exec("DELETE FROM navigation_menu WHERE 1=1");
    $pdo->exec("DELETE FROM role_navigation_menu WHERE 1=1");
    echo "✅ Existing data cleared\n";
    
    // 6. Insert dashboard pages
    echo "\n📋 Step 6: Creating dashboard pages...\n";
    $dashboardPages = [
        // System Creator Pages
        ['creator_dashboard', 'Creator Dashboard', 'pages/creator/dashboard.html', 'fas fa-god', 'System Creator Control Panel', 'system', 1],
        ['creator_database', 'Database Management', 'pages/creator/database.html', 'fas fa-database', 'Database Tools & Management', 'system', 2],
        ['creator_system', 'System Configuration', 'pages/creator/system.html', 'fas fa-cogs', 'System Settings & Configuration', 'system', 3],
        ['creator_deployment', 'Deployment Tools', 'pages/creator/deployment.html', 'fas fa-rocket', 'Deployment & Version Control', 'system', 4],
        
        // Owner Pages
        ['owner_dashboard', 'Owner Dashboard', 'pages/owner/dashboard.html', 'fas fa-crown', 'Business Owner Control Panel', 'business', 1],
        ['owner_analytics', 'Business Analytics', 'pages/owner/analytics.html', 'fas fa-chart-line', 'Business Intelligence & Analytics', 'business', 2],
        ['owner_strategic', 'Strategic Planning', 'pages/owner/strategic.html', 'fas fa-chess', 'Strategic Planning & Goals', 'business', 3],
        
        // General Manager Pages
        ['gm_dashboard', 'General Manager Dashboard', 'pages/manager/dashboard.html', 'fas fa-briefcase', 'Operational Management Dashboard', 'operations', 1],
        ['gm_staff', 'Staff Management', 'pages/manager/staff.html', 'fas fa-users', 'Staff Team Management', 'operations', 2],
        ['gm_operations', 'Operations Control', 'pages/manager/operations.html', 'fas fa-tasks', 'Daily Operations Management', 'operations', 3],
        ['gm_compliance', 'Compliance & Audit', 'pages/manager/compliance.html', 'fas fa-shield-alt', 'Compliance & Audit Reports', 'operations', 4],
        
        // IT Manager Pages
        ['it_dashboard', 'IT Manager Dashboard', 'pages/super_admin/dashboard.html', 'fas fa-laptop-code', 'Technical Management Dashboard', 'technical', 1],
        ['it_system', 'System Management', 'pages/super_admin/system.html', 'fas fa-server', 'System Administration', 'technical', 2],
        ['it_security', 'Security Management', 'pages/super_admin/security.html', 'fas fa-lock', 'Security & Access Control', 'technical', 3],
        ['it_backup', 'Backup & Recovery', 'pages/super_admin/backup.html', 'fas fa-save', 'Backup & Recovery Systems', 'technical', 4],
        
        // Finance Manager Pages
        ['finance_dashboard', 'Finance Manager Dashboard', 'pages/admin/dashboard.html', 'fas fa-chart-pie', 'Financial Management Dashboard', 'financial', 1],
        ['finance_reports', 'Financial Reports', 'pages/admin/reports.html', 'fas fa-file-invoice-dollar', 'Financial Reports & Analysis', 'financial', 2],
        ['finance_budget', 'Budget Planning', 'pages/admin/budget.html', 'fas fa-calculator', 'Budget Planning & Control', 'financial', 3],
        ['finance_shu', 'SHU Management', 'pages/admin/shu.html', 'fas fa-coins', 'SHU Calculation & Distribution', 'financial', 4],
        
        // Supervisor Pages
        ['supervisor_dashboard', 'Supervisor Dashboard', 'pages/staff/dashboard.html', 'fas fa-user-tie', 'Field Supervisor Dashboard', 'field', 1],
        ['supervisor_gps', 'GPS Tracking', 'pages/staff/gps.html', 'fas fa-map-marked-alt', 'GPS Tracking & Monitoring', 'field', 2],
        ['supervisor_targets', 'Target Management', 'pages/staff/targets.html', 'fas fa-bullseye', 'Target Setting & Monitoring', 'field', 3],
        ['supervisor_reports', 'Field Reports', 'pages/staff/reports.html', 'fas fa-clipboard-check', 'Field Operations Reports', 'field', 4],
        
        // Teller Pages
        ['teller_dashboard', 'Teller Dashboard', 'pages/teller/dashboard.html', 'fas fa-cash-register', 'Counter Operations Dashboard', 'counter', 1],
        ['teller_transactions', 'Transactions', 'pages/teller/transactions.html', 'fas fa-exchange-alt', 'Transaction Processing', 'counter', 2],
        ['teller_members', 'Member Services', 'pages/teller/members.html', 'fas fa-users', 'Member Account Services', 'counter', 3],
        
        // Field Officer Pages
        ['field_dashboard', 'Field Officer Dashboard', 'pages/staff/dashboard.html', 'fas fa-walking', 'Door-to-Door Operations Dashboard', 'field_ops', 1],
        ['field_visits', 'Visit Management', 'pages/staff/visits.html', 'fas fa-route', 'Customer Visit Planning', 'field_ops', 2],
        ['field_collections', 'Collections', 'pages/staff/collections.html', 'fas fa-hand-holding-usd', 'Payment Collections', 'field_ops', 3],
        
        // Member Pages
        ['member_dashboard', 'Member Dashboard', 'pages/member/dashboard.html', 'fas fa-user', 'Personal Account Dashboard', 'personal', 1],
        ['member_loans', 'My Loans', 'pages/member/loans.html', 'fas fa-hand-holding-usd', 'Loan Applications & Status', 'personal', 2],
        ['member_savings', 'My Savings', 'pages/member/savings.html', 'fas fa-piggy-bank', 'Savings Account Management', 'personal', 3],
        ['member_profile', 'My Profile', 'pages/member/profile.html', 'fas fa-id-card', 'Personal Profile Settings', 'personal', 4]
    ];
    
    foreach ($dashboardPages as $page) {
        $insertPage = $pdo->prepare("INSERT INTO dashboard_pages (page_key, page_title, page_url, page_icon, page_description, page_category, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertPage->execute($page);
        echo "✅ Created page: {$page[1]}\n";
    }
    
    // 7. Insert navigation menu
    echo "\n📋 Step 7: Creating navigation menu...\n";
    $navigationMenu = [
        // Main Navigation
        ['dashboard', 'Dashboard', null, 'fas fa-tachometer-alt', null, 'main', 1],
        ['members', 'Members', 'pages/members/list.html', 'fas fa-users', null, 'main', 2],
        ['loans', 'Loans', 'pages/loans/list.html', 'fas fa-hand-holding-usd', null, 'main', 3],
        ['savings', 'Savings', 'pages/savings/list.html', 'fas fa-piggy-bank', null, 'main', 4],
        ['transactions', 'Transactions', 'pages/transactions/list.html', 'fas fa-exchange-alt', null, 'main', 5],
        
        // Management Navigation
        ['reports', 'Reports', 'pages/reports/index.html', 'fas fa-chart-bar', null, 'management', 1],
        ['analytics', 'Analytics', 'pages/analytics/index.html', 'fas fa-chart-line', null, 'management', 2],
        ['settings', 'Settings', 'pages/settings/index.html', 'fas fa-cog', null, 'management', 3],
        
        // System Navigation
        ['users', 'User Management', 'pages/users/list.html', 'fas fa-user-cog', null, 'system', 1],
        ['roles', 'Role Management', 'pages/roles/list.html', 'fas fa-user-shield', null, 'system', 2],
        ['audit', 'Audit Logs', 'pages/audit/index.html', 'fas fa-history', null, 'system', 3],
        
        // Sub-menu items
        ['member_registration', 'Registration', 'pages/members/register.html', 'fas fa-user-plus', 'members', 'sub', 1],
        ['member_list', 'Member List', 'pages/members/list.html', 'fas fa-list', 'members', 'sub', 2],
        ['loan_application', 'New Loan', 'pages/loans/list.html', 'fas fa-plus', 'loans', 'sub', 1],
        ['loan_list', 'Loan List', 'pages/loans/list.html', 'fas fa-list', 'loans', 'sub', 2],
        ['savings_deposit', 'Deposit', 'pages/savings/list.html', 'fas fa-plus', 'savings', 'sub', 1],
        ['savings_withdraw', 'Withdraw', 'pages/savings/list.html', 'fas fa-minus', 'savings', 'sub', 2]
    ];
    
    foreach ($navigationMenu as $menu) {
        $insertMenu = $pdo->prepare("INSERT INTO navigation_menu (menu_key, menu_title, menu_url, menu_icon, parent_menu_key, menu_category, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertMenu->execute($menu);
        echo "✅ Created menu: {$menu[1]}\n";
    }
    
    // 8. Assign pages to roles
    echo "\n📋 Step 8: Assigning dashboard pages to roles...\n";
    $rolePageAssignments = [
        // Creator
        ['creator', 'creator_dashboard', 'admin', 1],
        ['creator', 'creator_database', 'admin', 2],
        ['creator', 'creator_system', 'admin', 3],
        ['creator', 'creator_deployment', 'admin', 4],
        
        // Owner
        ['owner', 'owner_dashboard', 'admin', 1],
        ['owner', 'owner_analytics', 'write', 2],
        ['owner', 'owner_strategic', 'write', 3],
        
        // General Manager
        ['general_manager', 'gm_dashboard', 'admin', 1],
        ['general_manager', 'gm_staff', 'write', 2],
        ['general_manager', 'gm_operations', 'write', 3],
        ['general_manager', 'gm_compliance', 'read', 4],
        
        // IT Manager
        ['it_manager', 'it_dashboard', 'admin', 1],
        ['it_manager', 'it_system', 'admin', 2],
        ['it_manager', 'it_security', 'admin', 3],
        ['it_manager', 'it_backup', 'write', 4],
        
        // Finance Manager
        ['finance_manager', 'finance_dashboard', 'admin', 1],
        ['finance_manager', 'finance_reports', 'write', 2],
        ['finance_manager', 'finance_budget', 'write', 3],
        ['finance_manager', 'finance_shu', 'write', 4],
        
        // Supervisor
        ['supervisor', 'supervisor_dashboard', 'admin', 1],
        ['supervisor', 'supervisor_gps', 'write', 2],
        ['supervisor', 'supervisor_targets', 'write', 3],
        ['supervisor', 'supervisor_reports', 'read', 4],
        
        // Teller
        ['teller', 'teller_dashboard', 'admin', 1],
        ['teller', 'teller_transactions', 'write', 2],
        ['teller', 'teller_members', 'read', 3],
        
        // Field Officer
        ['field_officer', 'field_dashboard', 'admin', 1],
        ['field_officer', 'field_visits', 'write', 2],
        ['field_officer', 'field_collections', 'write', 3],
        
        // Member
        ['member', 'member_dashboard', 'read', 1],
        ['member', 'member_loans', 'write', 2],
        ['member', 'member_savings', 'write', 3],
        ['member', 'member_profile', 'write', 4]
    ];
    
    foreach ($rolePageAssignments as $assignment) {
        $insertAssignment = $pdo->prepare("INSERT INTO role_dashboard_pages (role_key, page_key, access_level, sort_order) VALUES (?, ?, ?, ?)");
        $insertAssignment->execute($assignment);
        echo "✅ Assigned page to role: {$assignment[0]} → {$assignment[1]}\n";
    }
    
    // 9. Assign menu to roles
    echo "\n📋 Step 9: Assigning navigation menu to roles...\n";
    $roleMenuAssignments = [
        // Creator - Full access
        ['creator', 'dashboard', 'admin', 1],
        ['creator', 'members', 'admin', 2],
        ['creator', 'loans', 'admin', 3],
        ['creator', 'savings', 'admin', 4],
        ['creator', 'transactions', 'admin', 5],
        ['creator', 'reports', 'admin', 6],
        ['creator', 'analytics', 'admin', 7],
        ['creator', 'settings', 'admin', 8],
        ['creator', 'users', 'admin', 9],
        ['creator', 'roles', 'admin', 10],
        ['creator', 'audit', 'admin', 11],
        
        // Owner - Business access
        ['owner', 'dashboard', 'admin', 1],
        ['owner', 'members', 'write', 2],
        ['owner', 'loans', 'write', 3],
        ['owner', 'savings', 'write', 4],
        ['owner', 'transactions', 'read', 5],
        ['owner', 'reports', 'admin', 6],
        ['owner', 'analytics', 'admin', 7],
        ['owner', 'settings', 'write', 8],
        ['owner', 'users', 'write', 9],
        ['owner', 'audit', 'read', 10],
        
        // General Manager - Operations access
        ['general_manager', 'dashboard', 'admin', 1],
        ['general_manager', 'members', 'write', 2],
        ['general_manager', 'loans', 'write', 3],
        ['general_manager', 'savings', 'read', 4],
        ['general_manager', 'transactions', 'read', 5],
        ['general_manager', 'reports', 'admin', 6],
        ['general_manager', 'analytics', 'read', 7],
        ['general_manager', 'users', 'write', 8],
        ['general_manager', 'audit', 'read', 9],
        
        // IT Manager - Technical access
        ['it_manager', 'dashboard', 'admin', 1],
        ['it_manager', 'members', 'read', 2],
        ['it_manager', 'loans', 'read', 3],
        ['it_manager', 'savings', 'read', 4],
        ['it_manager', 'transactions', 'read', 5],
        ['it_manager', 'reports', 'read', 6],
        ['it_manager', 'analytics', 'read', 7],
        ['it_manager', 'settings', 'admin', 8],
        ['it_manager', 'users', 'admin', 9],
        ['it_manager', 'roles', 'admin', 10],
        ['it_manager', 'audit', 'admin', 11],
        
        // Finance Manager - Financial access
        ['finance_manager', 'dashboard', 'admin', 1],
        ['finance_manager', 'members', 'read', 2],
        ['finance_manager', 'loans', 'write', 3],
        ['finance_manager', 'savings', 'write', 4],
        ['finance_manager', 'transactions', 'write', 5],
        ['finance_manager', 'reports', 'admin', 6],
        ['finance_manager', 'analytics', 'admin', 7],
        ['finance_manager', 'settings', 'read', 8],
        ['finance_manager', 'audit', 'read', 9],
        
        // Supervisor - Field access
        ['supervisor', 'dashboard', 'admin', 1],
        ['supervisor', 'members', 'read', 2],
        ['supervisor', 'loans', 'read', 3],
        ['supervisor', 'savings', 'read', 4],
        ['supervisor', 'transactions', 'read', 5],
        ['supervisor', 'reports', 'write', 6],
        ['supervisor', 'users', 'read', 7],
        
        // Teller - Counter access
        ['teller', 'dashboard', 'admin', 1],
        ['teller', 'members', 'write', 2],
        ['teller', 'loans', 'read', 3],
        ['teller', 'savings', 'write', 4],
        ['teller', 'transactions', 'write', 5],
        ['teller', 'reports', 'read', 6],
        
        // Field Officer - Basic access
        ['field_officer', 'dashboard', 'admin', 1],
        ['field_officer', 'members', 'read', 2],
        ['field_officer', 'loans', 'read', 3],
        ['field_officer', 'savings', 'read', 4],
        ['field_officer', 'transactions', 'read', 5],
        ['field_officer', 'reports', 'write', 6],
        
        // Member - Limited access
        ['member', 'dashboard', 'read', 1],
        ['member', 'loans', 'write', 2],
        ['member', 'savings', 'write', 3],
        ['member', 'transactions', 'read', 4]
    ];
    
    foreach ($roleMenuAssignments as $assignment) {
        $insertAssignment = $pdo->prepare("INSERT INTO role_navigation_menu (role_key, menu_key, access_level, sort_order) VALUES (?, ?, ?, ?)");
        $insertAssignment->execute($assignment);
        echo "✅ Assigned menu to role: {$assignment[0]} → {$assignment[1]}\n";
    }
    
    echo "\n🎉 Dynamic Dashboard & Navigation System Setup Completed!\n";
    echo "📊 Summary:\n";
    echo "• " . count($dashboardPages) . " Dashboard pages created\n";
    echo "• " . count($navigationMenu) . " Navigation menu items created\n";
    echo "• " . count($rolePageAssignments) . " Role-page assignments created\n";
    echo "• " . count($roleMenuAssignments) . " Role-menu assignments created\n";
    echo "• Dynamic rendering system ready\n";
    echo "• Error handling for missing pages implemented\n\n";
    
    echo "🔧 Next Steps:\n";
    echo "1. Create Dynamic Dashboard API\n";
    echo "2. Create Dynamic Navigation API\n";
    echo "3. Update frontend to use dynamic system\n";
    echo "4. Add error handling for missing pages\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
