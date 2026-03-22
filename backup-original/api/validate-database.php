<?php
/**
 * Database Structure Validator
 * Validates database schema and data integrity
 */

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

echo "=== DATABASE STRUCTURE VALIDATION ===\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Check required tables
echo "📋 REQUIRED TABLES VALIDATION\n";
echo "==============================\n";

$requiredTables = [
    'users' => 'User accounts and authentication',
    'members' => 'Member information and profiles',
    'loans' => 'Loan applications and management',
    'savings' => 'Savings accounts and balances',
    'payment_transactions' => 'Payment records and history',
    'reward_points' => 'Points earning and redemption',
    'notifications' => 'User notifications',
    'gps_tracking' => 'GPS tracking for staff',
    'audit_logs' => 'System audit trail',
    'system_settings' => 'System configuration'
];

$existingTables = $db->fetchAll("SHOW TABLES");
$tableList = [];
foreach ($existingTables as $table) {
    $tableList[] = array_values($table)[0];
}

echo "📊 Table Status:\n";
foreach ($requiredTables as $table => $description) {
    $exists = in_array($table, $tableList);
    echo "  " . ($exists ? "✅" : "❌") . " $table - $description\n";
}
echo "\n";

// 2. Check table structures
echo "📊 TABLE STRUCTURE VALIDATION\n";
echo "==============================\n";

$tableStructures = [
    'users' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'username' => 'VARCHAR(50) UNIQUE',
        'email' => 'VARCHAR(100) UNIQUE',
        'password' => 'VARCHAR(255)',
        'full_name' => 'VARCHAR(100)',
        'phone' => 'VARCHAR(20)',
        'role' => 'ENUM("admin","staff","member")',
        'is_active' => 'BOOLEAN DEFAULT TRUE',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ],
    'members' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'user_id' => 'INT',
        'member_number' => 'VARCHAR(20) UNIQUE',
        'full_name' => 'VARCHAR(100)',
        'email' => 'VARCHAR(100)',
        'phone' => 'VARCHAR(20)',
        'address' => 'TEXT',
        'birth_date' => 'DATE',
        'id_number' => 'VARCHAR(20)',
        'join_date' => 'DATE',
        'membership_type' => 'ENUM("Regular","Premium","VIP")',
        'credit_score' => 'DECIMAL(5,2) DEFAULT 50.00',
        'status' => 'ENUM("Active","Inactive","Suspended")',
        'is_active' => 'BOOLEAN DEFAULT TRUE',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ],
    'loans' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'member_id' => 'INT',
        'loan_number' => 'VARCHAR(20) UNIQUE',
        'amount' => 'DECIMAL(12,2)',
        'interest_rate' => 'DECIMAL(5,2)',
        'term_months' => 'INT',
        'purpose' => 'TEXT',
        'status' => 'ENUM("Applied","Approved","Disbursed","Active","Completed","Default","Cancelled")',
        'monthly_payment' => 'DECIMAL(12,2)',
        'total_interest' => 'DECIMAL(12,2)',
        'remaining_balance' => 'DECIMAL(12,2)',
        'next_payment_date' => 'DATE',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ],
    'savings' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'member_id' => 'INT',
        'account_number' => 'VARCHAR(20) UNIQUE',
        'amount' => 'DECIMAL(12,2)',
        'type' => 'ENUM("Regular","Fixed","Special","Emergency")',
        'interest_rate' => 'DECIMAL(5,2)',
        'status' => 'ENUM("Active","Inactive","Closed")',
        'balance' => 'DECIMAL(12,2)',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ],
    'payment_transactions' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'member_id' => 'INT',
        'loan_id' => 'INT',
        'savings_id' => 'INT',
        'transaction_number' => 'VARCHAR(30) UNIQUE',
        'amount' => 'DECIMAL(12,2)',
        'type' => 'ENUM("Loan Payment","Savings Deposit","Savings Withdrawal","Fee","Fine")',
        'payment_method' => 'ENUM("Cash","Bank Transfer","Digital Wallet","Auto Debit")',
        'status' => 'ENUM("Pending","Completed","Failed","Cancelled")',
        'description' => 'TEXT',
        'processed_by' => 'INT',
        'processed_at' => 'TIMESTAMP NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ],
    'reward_points' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'member_id' => 'INT',
        'points' => 'INT',
        'category' => 'ENUM("loan_payment","savings_deposit","referral","login","milestone","bonus","penalty","reward_redemption","points_transfer","adjustment")',
        'description' => 'TEXT',
        'reference_id' => 'INT',
        'expires_at' => 'DATE',
        'is_active' => 'BOOLEAN DEFAULT TRUE',
        'created_by' => 'INT',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ],
    'notifications' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'user_id' => 'INT',
        'title' => 'VARCHAR(200)',
        'message' => 'TEXT',
        'type' => 'ENUM("info","success","warning","error","loan","savings","payment","system")',
        'reference_id' => 'INT',
        'is_read' => 'BOOLEAN DEFAULT FALSE',
        'is_deleted' => 'BOOLEAN DEFAULT FALSE',
        'read_at' => 'TIMESTAMP NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ],
    'gps_tracking' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'staff_id' => 'INT',
        'member_id' => 'INT',
        'purpose' => 'TEXT',
        'status' => 'ENUM("scheduled","active","completed","cancelled")',
        'start_latitude' => 'DECIMAL(10,8)',
        'start_longitude' => 'DECIMAL(11,8)',
        'end_latitude' => 'DECIMAL(10,8)',
        'end_longitude' => 'DECIMAL(11,8)',
        'distance_km' => 'DECIMAL(8,3)',
        'duration_minutes' => 'INT',
        'started_at' => 'TIMESTAMP NULL',
        'ended_at' => 'TIMESTAMP NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ],
    'audit_logs' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'user_id' => 'INT',
        'action' => 'VARCHAR(50)',
        'table_name' => 'VARCHAR(50)',
        'record_id' => 'INT',
        'old_values' => 'JSON',
        'new_values' => 'JSON',
        'description' => 'TEXT',
        'ip_address' => 'VARCHAR(45)',
        'user_agent' => 'TEXT',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ],
    'system_settings' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'key' => 'VARCHAR(100) UNIQUE',
        'value' => 'TEXT',
        'type' => 'ENUM("string","number","boolean","json")',
        'description' => 'TEXT',
        'is_public' => 'BOOLEAN DEFAULT FALSE',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ]
];

echo "📋 Structure Validation:\n";
foreach ($tableStructures as $table => $expectedColumns) {
    if (!in_array($table, $tableList)) {
        echo "  ❌ $table - Table missing\n";
        continue;
    }
    
    $actualColumns = $db->fetchAll("SHOW COLUMNS FROM $table");
    $actualColumnNames = [];
    foreach ($actualColumns as $column) {
        $actualColumnNames[] = $column['Field'];
    }
    
    $missingColumns = [];
    foreach ($expectedColumns as $columnName => $expectedType) {
        if (!in_array($columnName, $actualColumnNames)) {
            $missingColumns[] = $columnName;
        }
    }
    
    if (empty($missingColumns)) {
        echo "  ✅ $table - Structure OK\n";
    } else {
        echo "  ❌ $table - Missing columns: " . implode(', ', $missingColumns) . "\n";
    }
}
echo "\n";

// 3. Check foreign key constraints
echo "🔗 FOREIGN KEY VALIDATION\n";
echo "========================\n";

$expectedForeignKeys = [
    'members.user_id' => 'users.id',
    'loans.member_id' => 'members.id',
    'savings.member_id' => 'members.id',
    'payment_transactions.member_id' => 'members.id',
    'payment_transactions.loan_id' => 'loans.id',
    'payment_transactions.savings_id' => 'savings.id',
    'reward_points.member_id' => 'members.id',
    'notifications.user_id' => 'users.id',
    'gps_tracking.staff_id' => 'users.id',
    'gps_tracking.member_id' => 'members.id',
    'audit_logs.user_id' => 'users.id'
];

echo "📋 Foreign Key Status:\n";
foreach ($expectedForeignKeys as $fk => $reference) {
    list($table, $column) = explode('.', $fk);
    
    if (!in_array($table, $tableList)) {
        echo "  ❌ $table.$column - Table missing\n";
        continue;
    }
    
    $constraints = $db->fetchAll(
        "SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
         FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
         WHERE TABLE_SCHEMA = 'ksp_lamgabejaya_v2' AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL",
        [$table, $column]
    );
    
    if (!empty($constraints)) {
        echo "  ✅ $table.$column -> $reference\n";
    } else {
        echo "  ❌ $table.$column -> $reference - Missing constraint\n";
    }
}
echo "\n";

// 4. Check data integrity
echo "🔍 DATA INTEGRITY VALIDATION\n";
echo "============================\n";

// Check for orphaned records
echo "📋 Orphaned Records:\n";

// Check members without users
$orphanedMembers = $db->fetchOne(
    "SELECT COUNT(*) as count FROM members m LEFT JOIN users u ON m.user_id = u.id WHERE u.id IS NULL"
)['count'];
echo "  " . ($orphanedMembers > 0 ? "❌" : "✅") . " Members without users: $orphanedMembers\n";

// Check loans without members
$orphanedLoans = $db->fetchOne(
    "SELECT COUNT(*) as count FROM loans l LEFT JOIN members m ON l.member_id = m.id WHERE m.id IS NULL"
)['count'];
echo "  " . ($orphanedLoans > 0 ? "❌" : "✅") . " Loans without members: $orphanedLoans\n";

// Check savings without members
$orphanedSavings = $db->fetchOne(
    "SELECT COUNT(*) as count FROM savings s LEFT JOIN members m ON s.member_id = m.id WHERE m.id IS NULL"
)['count'];
echo "  " . ($orphanedSavings > 0 ? "❌" : "✅") . " Savings without members: $orphanedSavings\n";

// Check payments without members
$orphanedPayments = $db->fetchOne(
    "SELECT COUNT(*) as count FROM payment_transactions pt LEFT JOIN members m ON pt.member_id = m.id WHERE m.id IS NULL"
)['count'];
echo "  " . ($orphanedPayments > 0 ? "❌" : "✅") . " Payments without members: $orphanedPayments\n";

echo "\n";

// 5. Check indexes
echo "🔍 INDEX VALIDATION\n";
echo "====================\n";

$expectedIndexes = [
    'users' => ['username', 'email', 'role'],
    'members' => ['user_id', 'member_number', 'email', 'status'],
    'loans' => ['member_id', 'loan_number', 'status'],
    'savings' => ['member_id', 'account_number', 'status'],
    'payment_transactions' => ['member_id', 'loan_id', 'savings_id', 'transaction_number', 'type', 'status'],
    'reward_points' => ['member_id', 'category', 'expires_at'],
    'notifications' => ['user_id', 'type', 'is_read'],
    'gps_tracking' => ['staff_id', 'member_id', 'status'],
    'audit_logs' => ['user_id', 'action', 'created_at'],
    'system_settings' => ['key']
];

echo "📋 Index Status:\n";
foreach ($expectedIndexes as $table => $expectedIndexes) {
    if (!in_array($table, $tableList)) {
        echo "  ❌ $table - Table missing\n";
        continue;
    }
    
    $actualIndexes = $db->fetchAll("SHOW INDEX FROM $table");
    $actualIndexNames = [];
    foreach ($actualIndexes as $index) {
        $actualIndexNames[] = $index['Key_name'];
    }
    
    $missingIndexes = [];
    foreach ($expectedIndexes as $index) {
        if (!in_array($index, $actualIndexNames)) {
            $missingIndexes[] = $index;
        }
    }
    
    if (empty($missingIndexes)) {
        echo "  ✅ $table - All indexes present\n";
    } else {
        echo "  ❌ $table - Missing indexes: " . implode(', ', $missingIndexes) . "\n";
    }
}
echo "\n";

// 6. Data statistics
echo "📊 DATA STATISTICS\n";
echo "==================\n";

foreach ($requiredTables as $table => $description) {
    if (!in_array($table, $tableList)) {
        continue;
    }
    
    try {
        $count = $db->fetchOne("SELECT COUNT(*) as count FROM $table")['count'];
        echo "  $table: $count records\n";
    } catch (Exception $e) {
        echo "  $table: Error - " . $e->getMessage() . "\n";
    }
}
echo "\n";

// 7. Configuration validation
echo "⚙️  CONFIGURATION VALIDATION\n";
echo "==========================\n";

// Check system settings
if (in_array('system_settings', $tableList)) {
    $settingsCount = $db->fetchOne("SELECT COUNT(*) as count FROM system_settings")['count'];
    echo "  System settings: $settingsCount records\n";
    
    // Check for critical settings
    $criticalSettings = [
        'company_name',
        'company_address',
        'company_phone',
        'company_email',
        'interest_rate_regular',
        'interest_rate_premium',
        'interest_rate_vip',
        'min_loan_amount',
        'max_loan_amount',
        'min_savings_deposit'
    ];
    
    echo "  Critical settings:\n";
    foreach ($criticalSettings as $setting) {
        $exists = $db->fetchOne("SELECT COUNT(*) as count FROM system_settings WHERE `key` = ?", [$setting])['count'];
        echo "    " . ($exists > 0 ? "✅" : "❌") . " $setting\n";
    }
} else {
    echo "  ❌ system_settings table missing\n";
}

echo "\n";

// 8. Summary
echo "📊 VALIDATION SUMMARY\n";
echo "====================\n";

$totalTables = count($requiredTables);
$existingTables = count(array_intersect(array_keys($requiredTables), $tableList));
$tableScore = round(($existingTables / $totalTables) * 100, 2);

echo "📁 Required Tables: $existingTables/$totalFiles ($tableScore%)\n";
echo "🔗 Foreign Keys: Validated\n";
echo "🔍 Data Integrity: " . ($orphanedMembers + $orphanedLoans + $orphanedSavings + $orphanedPayments == 0 ? "OK" : "Issues Found") . "\n";
echo "🔍 Indexes: Validated\n";
echo "⚙️  Configuration: " . ($settingsCount > 0 ? "OK" : "Missing") . "\n";

$overallScore = ($tableScore + ($orphanedMembers + $orphanedLoans + $orphanedSavings + $orphanedPayments == 0 ? 25 : 0)) / 2;
echo "🎯 Overall Database Health: " . round($overallScore, 2) . "%\n\n";

echo "=== DATABASE VALIDATION COMPLETED ===\n";
?>
