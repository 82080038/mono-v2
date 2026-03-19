<?php
/**
 * Role Manager Class
 * 
 * Manages role definitions, permissions, and workflows from JSON configuration
 * Provides caching and database integration for dynamic role management
 * 
 * @author KSP Lam Gabe Jaya Development Team
 * @version 1.0
 */

class RoleManager {
    private static $instance = null;
    private $roleDefinitions = null;
    private $cacheFile = '/var/www/html/mono/cache/role_definitions.cache';
    private $jsonFile = '/var/www/html/mono/config/roles.json';
    private $cacheExpiry = 3600; // 1 hour
    
    /**
     * Singleton pattern implementation
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        $this->loadRoleDefinitions();
    }
    
    /**
     * Load role definitions from JSON with caching
     */
    private function loadRoleDefinitions() {
        // Check if cache exists and is valid
        if (file_exists($this->cacheFile) && (time() - filemtime($this->cacheFile) < $this->cacheExpiry)) {
            $this->roleDefinitions = json_decode(file_get_contents($this->cacheFile), true);
            return;
        }
        
        // Load from JSON file
        if (file_exists($this->jsonFile)) {
            $jsonContent = file_get_contents($this->jsonFile);
            $this->roleDefinitions = json_decode($jsonContent, true);
            
            // Save to cache
            $this->saveToCache();
        } else {
            throw new Exception("Role definitions file not found: " . $this->jsonFile);
        }
    }
    
    /**
     * Save role definitions to cache
     */
    private function saveToCache() {
        $cacheDir = dirname($this->cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        $jsonContent = json_encode($this->roleDefinitions);
        if ($jsonContent === false) {
            throw new Exception("Failed to encode role definitions to JSON");
        }
        file_put_contents($this->cacheFile, $jsonContent);
    }
    
    /**
     * Clear cache (useful after updates)
     */
    public function clearCache() {
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
        $this->loadRoleDefinitions();
    }
    
    /**
     * Get all role definitions
     */
    public function getAllRoles() {
        return $this->roleDefinitions['roles'] ?? [];
    }
    
    /**
     * Get specific role definition
     */
    public function getRole($roleName) {
        return $this->roleDefinitions['roles'][$roleName] ?? null;
    }
    
    /**
     * Get role display name
     */
    public function getRoleDisplayName($roleName) {
        $role = $this->getRole($roleName);
        return $role['display_name'] ?? $roleName;
    }
    
    /**
     * Get role responsibilities
     */
    public function getRoleResponsibilities($roleName) {
        $role = $this->getRole($roleName);
        return $role['responsibilities'] ?? [];
    }
    
    /**
     * Get role daily tasks
     */
    public function getRoleDailyTasks($roleName) {
        $role = $this->getRole($roleName);
        return $role['daily_tasks'] ?? [];
    }
    
    /**
     * Get role weekly tasks
     */
    public function getRoleWeeklyTasks($roleName) {
        $role = $this->getRole($roleName);
        return $role['weekly_tasks'] ?? [];
    }
    
    /**
     * Get role monthly tasks
     */
    public function getRoleMonthlyTasks($roleName) {
        $role = $this->getRole($roleName);
        return $role['monthly_tasks'] ?? [];
    }
    
    /**
     * Get role KPI metrics
     */
    public function getRoleKpiMetrics($roleName) {
        $role = $this->getRole($roleName);
        return $role['kpi_metrics'] ?? [];
    }
    
    /**
     * Get role permissions
     */
    public function getRolePermissions($roleName) {
        $role = $this->getRole($roleName);
        return $role['permissions'] ?? [];
    }
    
    /**
     * Get role menu items
     */
    public function getRoleMenuItems($roleName) {
        $role = $this->getRole($roleName);
        return $role['menu_items'] ?? [];
    }
    
    /**
     * Get role dashboard widgets
     */
    public function getRoleDashboardWidgets($roleName) {
        $role = $this->getRole($roleName);
        return $role['dashboard_widgets'] ?? [];
    }
    
    /**
     * Get role workflows
     */
    public function getRoleWorkflows($roleName) {
        $role = $this->getRole($roleName);
        return $role['workflows'] ?? [];
    }
    
    /**
     * Get role skills required
     */
    public function getRoleSkillsRequired($roleName) {
        $role = $this->getRole($roleName);
        return $role['skills_required'] ?? [];
    }
    
    /**
     * Get role tools access
     */
    public function getRoleToolsAccess($roleName) {
        $role = $this->getRole($roleName);
        return $role['tools_access'] ?? [];
    }
    
    /**
     * Check if role has specific permission
     */
    public function hasPermission($roleName, $category, $permission) {
        $rolePermissions = $this->getRolePermissions($roleName);
        
        // Check if role has all permissions
        if (isset($rolePermissions[$category]) && in_array('all', $rolePermissions[$category])) {
            return true;
        }
        
        // Check specific permission
        return isset($rolePermissions[$category]) && 
               in_array($permission, $rolePermissions[$category]);
    }
    
    /**
     * Get permission matrix
     */
    public function getPermissionMatrix() {
        return $this->roleDefinitions['permission_matrix'] ?? [];
    }
    
    /**
     * Check permission using matrix
     */
    public function checkPermission($roleName, $resource, $action) {
        $matrix = $this->getPermissionMatrix();
        
        if (!isset($matrix[$resource][$roleName])) {
            return false;
        }
        
        $permissions = $matrix[$resource][$roleName];
        
        // Check for all access
        if (in_array('all', $permissions)) {
            return true;
        }
        
        // Check specific action
        return in_array($action, $permissions);
    }
    
    /**
     * Get workflow templates
     */
    public function getWorkflowTemplates() {
        return $this->roleDefinitions['workflow_templates'] ?? [];
    }
    
    /**
     * Get specific workflow template
     */
    public function getWorkflowTemplate($workflowName) {
        $templates = $this->getWorkflowTemplates();
        return $templates[$workflowName] ?? null;
    }
    
    /**
     * Get skill matrix
     */
    public function getSkillMatrix() {
        return $this->roleDefinitions['skill_matrix'] ?? [];
    }
    
    /**
     * Get training programs
     */
    public function getTrainingPrograms() {
        return $this->roleDefinitions['training_programs'] ?? [];
    }
    
    /**
     * Get performance evaluation criteria
     */
    public function getPerformanceEvaluation() {
        return $this->roleDefinitions['performance_evaluation'] ?? [];
    }
    
    /**
     * Get role hierarchy (levels)
     */
    public function getRoleHierarchy() {
        $roles = $this->getAllRoles();
        $hierarchy = [];
        
        foreach ($roles as $roleName => $roleData) {
            $hierarchy[$roleName] = $roleData['level'] ?? 999;
        }
        
        asort($hierarchy);
        return $hierarchy;
    }
    
    /**
     * Check if role can manage another role
     */
    public function canManageRole($managerRole, $targetRole) {
        $hierarchy = $this->getRoleHierarchy();
        $managerLevel = $hierarchy[$managerRole] ?? 999;
        $targetLevel = $hierarchy[$targetRole] ?? 999;
        
        return $managerLevel < $targetLevel;
    }
    
    /**
     * Get roles by category
     */
    public function getRolesByCategory($category) {
        $roles = $this->getAllRoles();
        $filtered = [];
        
        foreach ($roles as $roleName => $roleData) {
            if (($roleData['category'] ?? '') === $category) {
                $filtered[$roleName] = $roleData;
            }
        }
        
        return $filtered;
    }
    
    /**
     * Get roles by level
     */
    public function getRolesByLevel($level) {
        $roles = $this->getAllRoles();
        $filtered = [];
        
        foreach ($roles as $roleName => $roleData) {
            if (($roleData['level'] ?? 999) === $level) {
                $filtered[$roleName] = $roleData;
            }
        }
        
        return $filtered;
    }
    
    /**
     * Search roles by keyword
     */
    public function searchRoles($keyword) {
        $roles = $this->getAllRoles();
        $results = [];
        
        foreach ($roles as $roleName => $roleData) {
            $searchText = json_encode($roleData);
            if (stripos($searchText, $keyword) !== false) {
                $results[$roleName] = $roleData;
            }
        }
        
        return $results;
    }
    
    /**
     * Validate role data structure
     */
    public function validateRoleData($roleData) {
        $required = ['id', 'name', 'display_name', 'level', 'description'];
        
        foreach ($required as $field) {
            if (!isset($roleData[$field])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Export role definitions to array
     */
    public function exportToArray() {
        return $this->roleDefinitions;
    }
    
    /**
     * Export role definitions to JSON
     */
    public function exportToJson() {
        return json_encode($this->roleDefinitions, JSON_PRETTY_PRINT);
    }
    
    /**
     * Get metadata
     */
    public function getMetadata() {
        return $this->roleDefinitions['metadata'] ?? [];
    }
    
    /**
     * Get total roles count
     */
    public function getTotalRoles() {
        $metadata = $this->getMetadata();
        return $metadata['total_roles'] ?? count($this->getAllRoles());
    }
    
    /**
     * Get last updated timestamp
     */
    public function getLastUpdated() {
        $metadata = $this->getMetadata();
        return $metadata['last_updated'] ?? null;
    }
    
    /**
     * Get version
     */
    public function getVersion() {
        $metadata = $this->getMetadata();
        return $metadata['version'] ?? '1.0';
    }
    
    /**
     * Debug method to print role structure
     */
    public function debugRoleStructure($roleName) {
        $role = $this->getRole($roleName);
        if (!$role) {
            echo "Role '$roleName' not found\n";
            return;
        }
        
        echo "=== Role: {$role['display_name']} ===\n";
        echo "Name: {$role['name']}\n";
        echo "Level: {$role['level']}\n";
        echo "Category: {$role['category']}\n";
        echo "Description: {$role['description']}\n";
        echo "\nResponsibilities:\n";
        foreach ($role['responsibilities'] as $resp) {
            echo "- {$resp['name']}: {$resp['description']}\n";
        }
        echo "\nDaily Tasks:\n";
        foreach ($role['daily_tasks'] as $task) {
            echo "- $task\n";
        }
        echo "\nPermissions:\n";
        foreach ($role['permissions'] as $category => $perms) {
            echo "- $category: " . implode(', ', $perms) . "\n";
        }
    }
}

/**
 * Utility function to get role manager instance
 */
function getRoleManager() {
    return RoleManager::getInstance();
}

/**
 * Example usage functions
 */

// Check if user has permission
function userHasPermission($userRole, $resource, $action) {
    $roleManager = getRoleManager();
    return $roleManager->checkPermission($userRole, $resource, $action);
}

// Get user menu items
function getUserMenuItems($userRole) {
    $roleManager = getRoleManager();
    return $roleManager->getRoleMenuItems($userRole);
}

// Get user dashboard widgets
function getUserDashboardWidgets($userRole) {
    $roleManager = getRoleManager();
    return $roleManager->getRoleDashboardWidgets($userRole);
}

// Get role responsibilities
function getRoleResponsibilities($roleName) {
    $roleManager = getRoleManager();
    return $roleManager->getRoleResponsibilities($roleName);
}

// Check if role can manage another role
function canManageRole($managerRole, $targetRole) {
    $roleManager = getRoleManager();
    return $roleManager->canManageRole($managerRole, $targetRole);
}

?>
