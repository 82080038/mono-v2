<?php
/**
 * Database Index Optimization Script
 * Creates necessary indexes for better performance
 */

// Define constant for access control
require_once __DIR__ . '/config/error-config.php';

define('KSP_API_ACCESS', true);

// Include required files
require_once __DIR__ . '/DatabaseHelper.php';

// Initialize database connection
try {
    $db = DatabaseHelper::getInstance();
    echo "✅ Database connection successful\n\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "=== DATABASE INDEX OPTIMIZATION ===\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

// Define indexes to create
$indexes = [
    // Users table
    'users' => [
        'idx_users_username' => 'CREATE INDEX idx_users_username ON users(username)',
        'idx_users_email' => 'CREATE INDEX idx_users_email ON users(email)',
        'idx_users_role' => 'CREATE INDEX idx_users_role ON users(role)',
        'idx_users_is_active' => 'CREATE INDEX idx_users_is_active ON users(is_active)',
        'idx_users_created_at' => 'CREATE INDEX idx_users_created_at ON users(created_at)',
        'idx_users_last_activity' => 'CREATE INDEX idx_users_last_activity ON users(last_activity)'
    ],
    
    // Members table
    'members' => [
        'idx_members_user_id' => 'CREATE INDEX idx_members_user_id ON members(user_id)',
        'idx_members_member_number' => 'CREATE INDEX idx_members_member_number ON members(member_number)',
        'idx_members_email' => 'CREATE INDEX idx_members_email ON members(email)',
        'idx_members_phone' => 'CREATE INDEX idx_members_phone ON members(phone)',
        'idx_members_status' => 'CREATE INDEX idx_members_status ON members(status)',
        'idx_members_is_active' => 'CREATE INDEX idx_members_is_active ON members(is_active)',
        'idx_members_join_date' => 'CREATE INDEX idx_members_join_date ON members(join_date)',
        'idx_members_membership_type' => 'CREATE INDEX idx_members_membership_type ON members(membership_type)',
        'idx_members_credit_score' => 'CREATE INDEX idx_members_credit_score ON members(credit_score)',
        'idx_members_created_at' => 'CREATE INDEX idx_members_created_at ON members(created_at)'
    ],
    
    // Loans table
    'loans' => [
        'idx_loans_member_id' => 'CREATE INDEX idx_loans_member_id ON loans(member_id)',
        'idx_loans_loan_number' => 'CREATE INDEX idx_loans_loan_number ON loans(loan_number)',
        'idx_loans_status' => 'CREATE INDEX idx_loans_status ON loans(status)',
        'idx_loans_purpose' => 'CREATE INDEX idx_loans_purpose ON loans(purpose)',
        'idx_loans_amount' => 'CREATE INDEX idx_loans_amount ON loans(amount)',
        'idx_loans_term_months' => 'CREATE INDEX idx_loans_term_months ON loans(term_months)',
        'idx_loans_interest_rate' => 'CREATE INDEX idx_loans_interest_rate ON loans(interest_rate)',
        'idx_loans_next_payment_date' => 'CREATE INDEX idx_loans_next_payment_date ON loans(next_payment_date)',
        'idx_loans_created_at' => 'CREATE INDEX idx_loans_created_at ON loans(created_at)',
        'idx_loans_updated_at' => 'CREATE INDEX idx_loans_updated_at ON loans(updated_at)',
        'idx_loans_member_status' => 'CREATE INDEX idx_loans_member_status ON loans(member_id, status)'
    ],
    
    // Savings table
    'savings' => [
        'idx_savings_member_id' => 'CREATE INDEX idx_savings_member_id ON savings(member_id)',
        'idx_savings_account_number' => 'CREATE INDEX idx_savings_account_number ON savings(account_number)',
        'idx_savings_type' => 'CREATE INDEX idx_savings_type ON savings(type)',
        'idx_savings_status' => 'CREATE INDEX idx_savings_status ON savings(status)',
        'idx_savings_balance' => 'CREATE INDEX idx_savings_balance ON savings(balance)',
        'idx_savings_interest_rate' => 'CREATE INDEX idx_savings_interest_rate ON savings(interest_rate)',
        'idx_savings_created_at' => 'CREATE INDEX idx_savings_created_at ON savings(created_at)',
        'idx_savings_updated_at' => 'CREATE INDEX idx_savings_updated_at ON savings(updated_at)',
        'idx_savings_member_status' => 'CREATE INDEX idx_savings_member_status ON savings(member_id, status)'
    ],
    
    // Payment transactions table
    'payment_transactions' => [
        'idx_payment_transactions_member_id' => 'CREATE INDEX idx_payment_transactions_member_id ON payment_transactions(member_id)',
        'idx_payment_transactions_loan_id' => 'CREATE INDEX idx_payment_transactions_loan_id ON payment_transactions(loan_id)',
        'idx_payment_transactions_savings_id' => 'CREATE INDEX idx_payment_transactions_savings_id ON payment_transactions(savings_id)',
        'idx_payment_transactions_transaction_number' => 'CREATE INDEX idx_payment_transactions_transaction_number ON payment_transactions(transaction_number)',
        'idx_payment_transactions_type' => 'CREATE INDEX idx_payment_transactions_type ON payment_transactions(type)',
        'idx_payment_transactions_payment_method' => 'CREATE INDEX idx_payment_transactions_payment_method ON payment_transactions(payment_method)',
        'idx_payment_transactions_status' => 'CREATE INDEX idx_payment_transactions_status ON payment_transactions(status)',
        'idx_payment_transactions_amount' => 'CREATE INDEX idx_payment_transactions_amount ON payment_transactions(amount)',
        'idx_payment_transactions_created_at' => 'CREATE INDEX idx_payment_transactions_created_at ON payment_transactions(created_at)',
        'idx_payment_transactions_processed_at' => 'CREATE INDEX idx_payment_transactions_processed_at ON payment_transactions(processed_at)',
        'idx_payment_transactions_member_type' => 'CREATE INDEX idx_payment_transactions_member_type ON payment_transactions(member_id, type)',
        'idx_payment_transactions_member_status' => 'CREATE INDEX idx_payment_transactions_member_status ON payment_transactions(member_id, status)'
    ],
    
    // Reward points table
    'reward_points' => [
        'idx_reward_points_member_id' => 'CREATE INDEX idx_reward_points_member_id ON reward_points(member_id)',
        'idx_reward_points_category' => 'CREATE INDEX idx_reward_points_category ON reward_points(category)',
        'idx_reward_points_points' => 'CREATE INDEX idx_reward_points_points ON reward_points(points)',
        'idx_reward_points_expires_at' => 'CREATE INDEX idx_reward_points_expires_at ON reward_points(expires_at)',
        'idx_reward_points_is_active' => 'CREATE INDEX idx_reward_points_is_active ON reward_points(is_active)',
        'idx_reward_points_created_at' => 'CREATE INDEX idx_reward_points_created_at ON reward_points(created_at)',
        'idx_reward_points_member_category' => 'CREATE INDEX idx_reward_points_member_category ON reward_points(member_id, category)',
        'idx_reward_points_member_active' => 'CREATE INDEX idx_reward_points_member_active ON reward_points(member_id, is_active)'
    ],
    
    // Notifications table
    'notifications' => [
        'idx_notifications_user_id' => 'CREATE INDEX idx_notifications_user_id ON notifications(user_id)',
        'idx_notifications_type' => 'CREATE INDEX idx_notifications_type ON notifications(type)',
        'idx_notifications_is_read' => 'CREATE INDEX idx_notifications_is_read ON notifications(is_read)',
        'idx_notifications_is_deleted' => 'CREATE INDEX idx_notifications_is_deleted ON notifications(is_deleted)',
        'idx_notifications_created_at' => 'CREATE INDEX idx_notifications_created_at ON notifications(created_at)',
        'idx_notifications_user_read' => 'CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read)',
        'idx_notifications_user_type' => 'CREATE INDEX idx_notifications_user_type ON notifications(user_id, type)'
    ],
    
    // GPS tracking table
    'gps_tracking' => [
        'idx_gps_tracking_staff_id' => 'CREATE INDEX idx_gps_tracking_staff_id ON gps_tracking(staff_id)',
        'idx_gps_tracking_member_id' => 'CREATE INDEX idx_gps_tracking_member_id ON gps_tracking(member_id)',
        'idx_gps_tracking_status' => 'CREATE INDEX idx_gps_tracking_status ON gps_tracking(status)',
        'idx_gps_tracking_created_at' => 'CREATE INDEX idx_gps_tracking_created_at ON gps_tracking(created_at)',
        'idx_gps_tracking_started_at' => 'CREATE INDEX idx_gps_tracking_started_at ON gps_tracking(started_at)',
        'idx_gps_tracking_ended_at' => 'CREATE INDEX idx_gps_tracking_ended_at ON gps_tracking(ended_at)',
        'idx_gps_tracking_staff_status' => 'CREATE INDEX idx_gps_tracking_staff_status ON gps_tracking(staff_id, status)',
        'idx_gps_tracking_member_status' => 'CREATE INDEX idx_gps_tracking_member_status ON gps_tracking(member_id, status)'
    ],
    
    // GPS logs table
    'gps_logs' => [
        'idx_gps_logs_staff_id' => 'CREATE INDEX idx_gps_logs_staff_id ON gps_logs(staff_id)',
        'idx_gps_logs_tracking_id' => 'CREATE INDEX idx_gps_logs_tracking_id ON gps_logs(tracking_id)',
        'idx_gps_logs_timestamp' => 'CREATE INDEX idx_gps_logs_timestamp ON gps_logs(timestamp)',
        'idx_gps_logs_created_at' => 'CREATE INDEX idx_gps_logs_created_at ON gps_logs(created_at)',
        'idx_gps_logs_staff_timestamp' => 'CREATE INDEX idx_gps_logs_staff_timestamp ON gps_logs(staff_id, timestamp)',
        'idx_gps_logs_tracking_timestamp' => 'CREATE INDEX idx_gps_logs_tracking_timestamp ON gps_logs(tracking_id, timestamp)'
    ],
    
    // Audit logs table
    'audit_logs' => [
        'idx_audit_logs_user_id' => 'CREATE INDEX idx_audit_logs_user_id ON audit_logs(user_id)',
        'idx_audit_logs_action' => 'CREATE INDEX idx_audit_logs_action ON audit_logs(action)',
        'idx_audit_logs_table_name' => 'CREATE INDEX idx_audit_logs_table_name ON audit_logs(table_name)',
        'idx_audit_logs_record_id' => 'CREATE INDEX idx_audit_logs_record_id ON audit_logs(record_id)',
        'idx_audit_logs_ip_address' => 'CREATE INDEX idx_audit_logs_ip_address ON audit_logs(ip_address)',
        'idx_audit_logs_created_at' => 'CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at)',
        'idx_audit_logs_user_action' => 'CREATE INDEX idx_audit_logs_user_action ON audit_logs(user_id, action)',
        'idx_audit_logs_table_record' => 'CREATE INDEX idx_audit_logs_table_record ON audit_logs(table_name, record_id)'
    ],
    
    // System settings table
    'system_settings' => [
        'idx_system_settings_key' => 'CREATE INDEX idx_system_settings_key ON system_settings(key)',
        'idx_system_settings_type' => 'CREATE INDEX idx_system_settings_type ON system_settings(type)',
        'idx_system_settings_is_public' => 'CREATE INDEX idx_system_settings_is_public ON system_settings(is_public)',
        'idx_system_settings_created_at' => 'CREATE INDEX idx_system_settings_created_at ON system_settings(created_at)',
        'idx_system_settings_updated_at' => 'CREATE INDEX idx_system_settings_updated_at ON system_settings(updated_at)'
    ]
];

$createdIndexes = [];
$existingIndexes = [];
$errors = [];

foreach ($indexes as $table => $tableIndexes) {
    echo "📊 Processing table: $table\n";
    
    // Check if table exists
    try {
        $tableExists = $db->fetchOne("SHOW TABLES LIKE '$table'");
        if (!$tableExists) {
            echo "  ❌ Table does not exist: $table\n";
            continue;
        }
    } catch (Exception $e) {
        echo "  ❌ Error checking table $table: " . $e->getMessage() . "\n";
        continue;
    }
    
    // Get existing indexes
    try {
        $existingIndexes[$table] = $db->fetchAll("SHOW INDEX FROM $table");
    } catch (Exception $e) {
        echo "  ❌ Error getting indexes for $table: " . $e->getMessage() . "\n";
        continue;
    }
    
    foreach ($tableIndexes as $indexName => $indexSql) {
        try {
            // Check if index already exists
            $indexExists = false;
            foreach ($existingIndexes[$table] as $existingIndex) {
                if ($existingIndex['Key_name'] === $indexName) {
                    $indexExists = true;
                    break;
                }
            }
            
            if ($indexExists) {
                echo "  ⚠️  Index already exists: $indexName\n";
                continue;
            }
            
            // Create index
            $db->query($indexSql);
            $createdIndexes[] = $indexName;
            echo "  ✅ Created index: $indexName\n";
            
        } catch (Exception $e) {
            $errors[] = [
                'table' => $table,
                'index' => $indexName,
                'error' => $e->getMessage()
            ];
            echo "  ❌ Failed to create index $indexName: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
}

// Summary
echo "=== INDEX CREATION SUMMARY ===\n";
echo "Total indexes created: " . count($createdIndexes) . "\n";
echo "Total errors: " . count($errors) . "\n";

if (!empty($createdIndexes)) {
    echo "\n✅ Created indexes:\n";
    foreach ($createdIndexes as $index) {
        echo "  - $index\n";
    }
}

if (!empty($errors)) {
    echo "\n❌ Errors:\n";
    foreach ($errors as $error) {
        echo "  - {$error['table']}.{$error['index']}: {$error['error']}\n";
    }
}

// Analyze tables for optimization
echo "\n=== TABLE ANALYSIS ===\n";
$analyzeTables = ['users', 'members', 'loans', 'savings', 'payment_transactions', 'reward_points', 'notifications', 'gps_tracking', 'gps_logs', 'audit_logs'];

foreach ($analyzeTables as $table) {
    try {
        $db->query("ANALYZE TABLE $table");
        echo "✅ Analyzed table: $table\n";
    } catch (Exception $e) {
        echo "❌ Failed to analyze table $table: " . $e->getMessage() . "\n";
    }
}

// Optimize tables
echo "\n=== TABLE OPTIMIZATION ===\n";
foreach ($analyzeTables as $table) {
    try {
        $db->query("OPTIMIZE TABLE $table");
        echo "✅ Optimized table: $table\n";
    } catch (Exception $e) {
        echo "❌ Failed to optimize table $table: " . $e->getMessage() . "\n";
    }
}

echo "\n=== INDEX OPTIMIZATION COMPLETED ===\n";

// Define constant for access control
define('KSP_API_ACCESS', true);
?>
