<?php
/**
 * Database Test Script
 * Verifies database connection and setup
 */

require_once __DIR__ . '/utils/Database.php';

echo "=== KSP Lam Gabe Jaya Database Test ===\n\n";

try {
    $db = Database::getInstance();
    echo "✅ Database connection successful!\n\n";
    
    // Test basic query
    $result = $db->fetchOne("SELECT COUNT(*) as count FROM users");
    echo "📊 Users in database: " . $result['count'] . "\n";
    
    // Check member types
    $result = $db->fetchAll("SELECT * FROM member_types");
    echo "📋 Member Types: " . count($result) . "\n";
    foreach ($result as $type) {
        echo "   - " . $type['name'] . ": " . $type['description'] . "\n";
    }
    
    // Check account types
    $result = $db->fetchAll("SELECT * FROM account_types");
    echo "💳 Account Types: " . count($result) . "\n";
    foreach ($result as $type) {
        echo "   - " . $type['name'] . " (" . $type['code'] . "): " . ($type['interest_rate']*100) . "%\n";
    }
    
    // Check loan types
    $result = $db->fetchAll("SELECT * FROM loan_types");
    echo "💰 Loan Types: " . count($result) . "\n";
    foreach ($result as $type) {
        echo "   - " . $type['name'] . " (" . $type['code'] . "): " . ($type['interest_rate']*100) . "%\n";
    }
    
    // Check system settings
    $result = $db->fetchAll("SELECT * FROM system_settings");
    echo "⚙️ System Settings: " . count($result) . "\n";
    
    echo "\n✅ Database setup verification completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Database test failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
