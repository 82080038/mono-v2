<?php
/**
 * Fix User Passwords for All Roles
 * This script will update passwords for all role users
 */

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/../config/error-config.php';
require_once __DIR__ . '/DatabaseHelper.php';

try {
    $db = DatabaseHelper::getInstance();
    $pdo = $db->getConnection();
    
    // Enable buffered queries to avoid issues
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    
    echo "🔧 FIXING USER PASSWORDS - ALL ROLES\n";
    echo str_repeat("=", 60) . "\n\n";
    
    // Define all roles and credentials
    $roles = [
        [
            'role' => 'creator',
            'username' => 'creator',
            'password' => 'creator123',
            'name' => 'System Creator'
        ],
        [
            'role' => 'owner',
            'username' => 'owner',
            'password' => 'owner123',
            'name' => 'Business Owner'
        ],
        [
            'role' => 'general_manager',
            'username' => 'gm',
            'password' => 'gm123',
            'name' => 'General Manager'
        ],
        [
            'role' => 'it_manager',
            'username' => 'itmanager',
            'password' => 'itmanager123',
            'name' => 'IT Manager'
        ],
        [
            'role' => 'finance_manager',
            'username' => 'financemgr',
            'password' => 'financemgr123',
            'name' => 'Finance Manager'
        ],
        [
            'role' => 'supervisor',
            'username' => 'supervisor',
            'password' => 'supervisor123',
            'name' => 'Supervisor'
        ],
        [
            'role' => 'teller',
            'username' => 'teller',
            'password' => 'teller123',
            'name' => 'Teller'
        ],
        [
            'role' => 'field_officer',
            'username' => 'fieldofficer',
            'password' => 'fieldofficer123',
            'name' => 'Field Officer'
        ],
        [
            'role' => 'member',
            'username' => 'member',
            'password' => 'member123',
            'name' => 'Member'
        ]
    ];
    
    $updatedCount = 0;
    $errorCount = 0;
    
    foreach ($roles as $roleData) {
        echo "🔧 Updating password for: {$roleData['name']} ({$roleData['username']})\n";
        
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
            $stmt->execute([$roleData['username']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor(); // Close cursor to allow next query
            
            if ($user) {
                // Update password
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
                $result = $stmt->execute([$roleData['password'], $roleData['username']]);
                $stmt->closeCursor(); // Close cursor to allow next query
                
                if ($result) {
                    echo "✅ Password updated successfully\n";
                    echo "   Old password: '{$user['password']}'\n";
                    echo "   New password: '{$roleData['password']}'\n";
                    $updatedCount++;
                } else {
                    echo "❌ Failed to update password\n";
                    $errorCount++;
                }
            } else {
                echo "❌ User not found: {$roleData['username']}\n";
                $errorCount++;
            }
            
        } catch (Exception $e) {
            echo "❌ Error updating password: " . $e->getMessage() . "\n";
            $errorCount++;
        }
        
        echo "\n";
    }
    
    echo "📊 PASSWORD UPDATE SUMMARY\n";
    echo str_repeat("=", 60) . "\n";
    echo "✅ Successfully updated: $updatedCount users\n";
    echo "❌ Failed to update: $errorCount users\n";
    echo "📋 Total users processed: " . count($roles) . "\n\n";
    
    // Verify updates
    echo "🔍 VERIFYING PASSWORD UPDATES\n";
    echo str_repeat("-", 60) . "\n";
    
    foreach ($roles as $roleData) {
        $stmt = $pdo->prepare("SELECT username, password, role FROM users WHERE username = ?");
        $stmt->execute([$roleData['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor(); // Close cursor to allow next query
        
        if ($user) {
            $status = ($user['password'] === $roleData['password']) ? '✅' : '❌';
            echo "$status {$user['username']} - Password: '{$user['password']}'\n";
        } else {
            echo "❌ {$roleData['username']} - User not found\n";
        }
    }
    
    echo "\n🎉 PASSWORD UPDATE COMPLETED!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
