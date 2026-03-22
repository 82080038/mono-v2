<?php
/**
 * Role Permissions Management
 */
require_once __DIR__ . '/config/error-config.php';

define('KSP_API_ACCESS', true);

class RolePermissions {
    private static $permissions = [
        "owner" => ["all"],
        "super_admin" => ["system_management", "user_management", "database_management"],
        "admin" => ["user_management", "reports", "settings"],
        "manager" => ["staff_management", "loan_approval", "reports"],
        "teller" => ["transactions", "customer_service"],
        "staff" => ["field_operations", "customer_service"],
        "member" => ["account_management", "loan_application", "payments"]
    ];
    
    public static function hasPermission($role, $permission) {
        if (!isset(self::$permissions[$role])) {
            return false;
        }
        
        return in_array("all", self::$permissions[$role]) || 
               in_array($permission, self::$permissions[$role]);
    }
}
?>