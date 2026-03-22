<?php
/**
 * Dynamic Navigation API
 */

define('KSP_API_ACCESS', true);

require_once __DIR__ . '/DatabaseHelper.php';
require_once __DIR__ . '/AuthHelper.php';
require_once __DIR__ . '/SecurityHelper.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Handle OPTIONS request for CORS
if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

// Set request method if not set
if (!isset($_SERVER["REQUEST_METHOD"])) {
    $_SERVER["REQUEST_METHOD"] = "GET";
}

$action = $_REQUEST["action"] ?? "get_navigation";

try {
    $db = DatabaseHelper::getInstance();
    $pdo = $db->getConnection();
    
    switch ($action) {
        case "get_navigation":
            // Get user role from token
            $token = $_REQUEST['token'] ?? '';
            if (empty($token)) {
                echo json_encode(['success' => false, 'message' => 'Token required']);
                exit();
            }
            
            // Handle both JWT and simple tokens
            $userData = null;
            try {
                if (strpos($token, '.') !== false && count(explode('.', $token)) === 3) {
                    // JWT token format
                    $userData = AuthHelper::validateJWTToken($token);
                } else {
                    // Simple token format (base64 encoded JSON)
                    $userData = json_decode(base64_decode($token), true);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Invalid token format: ' . $e->getMessage()]);
                exit();
            }
            
            if (!$userData || !isset($userData['role'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid token - missing role']);
                exit();
            }
            
            $role = $userData['role'];
            
            // Get navigation menu for this role
            $stmt = $pdo->prepare("
                SELECT nm.*, rnm.access_level, rnm.sort_order as role_sort_order
                FROM navigation_menu nm
                JOIN role_navigation_menu rnm ON nm.menu_key = rnm.menu_key
                WHERE rnm.role_key = ? AND rnm.is_visible = 1 AND nm.is_active = 1
                ORDER BY rnm.sort_order ASC, nm.sort_order ASC
            ");
            $stmt->execute([$role]);
            $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Build hierarchical menu structure
            $menuTree = [];
            $parentItems = [];
            
            foreach ($menuItems as $item) {
                // Check if page exists (if URL is provided)
                $fileExists = true;
                $fileError = null;
                
                if (!empty($item['menu_url'])) {
                    $filePath = __DIR__ . '/../' . $item['menu_url'];
                    $fileExists = file_exists($filePath);
                    if (!$fileExists) {
                        $fileError = 'File not found: ' . $item['menu_url'];
                    }
                }
                
                $menuItem = [
                    'menu_key' => $item['menu_key'],
                    'menu_title' => $item['menu_title'],
                    'menu_url' => $item['menu_url'],
                    'menu_icon' => $item['menu_icon'],
                    'parent_menu_key' => $item['parent_menu_key'],
                    'menu_category' => $item['menu_category'],
                    'access_level' => $item['access_level'],
                    'sort_order' => $item['role_sort_order'],
                    'file_exists' => $fileExists,
                    'file_error' => $fileError,
                    'children' => []
                ];
                
                if (empty($item['parent_menu_key'])) {
                    // Parent menu item
                    $menuTree[$item['menu_key']] = $menuItem;
                } else {
                    // Child menu item
                    $parentItems[$item['parent_menu_key']][] = $menuItem;
                }
            }
            
            // Attach children to parents
            foreach ($parentItems as $parentKey => $children) {
                if (isset($menuTree[$parentKey])) {
                    $menuTree[$parentKey]['children'] = $children;
                }
            }
            
            // Group by category
            $categorizedMenu = [];
            foreach ($menuTree as $item) {
                $category = $item['menu_category'] ?? 'other';
                if (!isset($categorizedMenu[$category])) {
                    $categorizedMenu[$category] = [
                        'category' => $category,
                        'items' => []
                    ];
                }
                $categorizedMenu[$category]['items'][] = $item;
            }
            
            // Check for missing files
            $missingFiles = [];
            foreach ($menuItems as $item) {
                if (!empty($item['menu_url'])) {
                    $filePath = __DIR__ . '/../' . $item['menu_url'];
                    if (!file_exists($filePath)) {
                        $missingFiles[] = [
                            'menu_key' => $item['menu_key'],
                            'menu_title' => $item['menu_title'],
                            'menu_url' => $item['menu_url'],
                            'error' => 'File not found'
                        ];
                    }
                }
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'role' => $role,
                    'menu_tree' => array_values($menuTree),
                    'categorized_menu' => array_values($categorizedMenu),
                    'missing_files' => $missingFiles,
                    'total_items' => count($menuItems),
                    'accessible_items' => count($menuItems),
                    'missing_count' => count($missingFiles)
                ]
            ]);
            break;
            
        case "get_menu_by_category":
            // Get menu items by category
            $category = $_GET['category'] ?? '';
            $token = $_REQUEST['token'] ?? '';
            
            if (empty($token)) {
                echo json_encode(['success' => false, 'message' => 'Token required']);
                exit();
            }
            
            $userData = AuthHelper::validateJWTToken($token);
            if (!$userData || !isset($userData['role'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid token']);
                exit();
            }
            
            $role = $userData['role'];
            
            $stmt = $pdo->prepare("
                SELECT nm.*, rnm.access_level, rnm.sort_order as role_sort_order
                FROM navigation_menu nm
                JOIN role_navigation_menu rnm ON nm.menu_key = rnm.menu_key
                WHERE rnm.role_key = ? AND rnm.is_visible = 1 AND nm.is_active = 1 
                AND nm.menu_category = ?
                ORDER BY rnm.sort_order ASC, nm.sort_order ASC
            ");
            $stmt->execute([$role, $category]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'category' => $category,
                    'items' => $items,
                    'count' => count($items)
                ]
            ]);
            break;
            
        case "check_access":
            // Check if user has access to specific menu
            $menuKey = $_GET['menu_key'] ?? '';
            $token = $_REQUEST['token'] ?? '';
            
            if (empty($menuKey) || empty($token)) {
                echo json_encode(['success' => false, 'message' => 'Menu key and token required']);
                exit();
            }
            
            $userData = AuthHelper::validateJWTToken($token);
            if (!$userData || !isset($userData['role'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid token']);
                exit();
            }
            
            $role = $userData['role'];
            
            $stmt = $pdo->prepare("
                SELECT rnm.access_level, nm.menu_title, nm.menu_url
                FROM role_navigation_menu rnm
                JOIN navigation_menu nm ON rnm.menu_key = nm.menu_key
                WHERE rnm.role_key = ? AND rnm.menu_key = ? AND rnm.is_visible = 1 AND nm.is_active = 1
            ");
            $stmt->execute([$role, $menuKey]);
            $access = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$access) {
                echo json_encode(['success' => false, 'message' => 'Access denied or menu not found']);
                exit();
            }
            
            // Check if file exists
            $fileExists = true;
            $fileError = null;
            
            if (!empty($access['menu_url'])) {
                $filePath = __DIR__ . '/../' . $access['menu_url'];
                $fileExists = file_exists($filePath);
                if (!$fileExists) {
                    $fileError = 'File not found: ' . $access['menu_url'];
                }
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'menu_key' => $menuKey,
                    'menu_title' => $access['menu_title'],
                    'menu_url' => $access['menu_url'],
                    'access_level' => $access['access_level'],
                    'file_exists' => $fileExists,
                    'file_error' => $fileError
                ]
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
