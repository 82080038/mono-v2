<?php
/**
 * Dynamic Dashboard API
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

$action = $_REQUEST["action"] ?? "get_dashboard";

try {
    $db = DatabaseHelper::getInstance();
    $pdo = $db->getConnection();
    
    switch ($action) {
        case "get_dashboard":
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
            
            // Get dashboard pages for this role
            $stmt = $pdo->prepare("
                SELECT dp.*, rdp.access_level, rdp.sort_order as role_sort_order
                FROM dashboard_pages dp
                JOIN role_dashboard_pages rdp ON dp.page_key = rdp.page_key
                WHERE rdp.role_key = ? AND rdp.is_visible = 1 AND dp.is_active = 1
                ORDER BY rdp.sort_order ASC, dp.sort_order ASC
            ");
            $stmt->execute([$role]);
            $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Check if pages exist and files are accessible
            $accessiblePages = [];
            $missingPages = [];
            
            foreach ($pages as $page) {
                $filePath = __DIR__ . '/../' . $page['page_url'];
                
                if (file_exists($filePath)) {
                    $accessiblePages[] = [
                        'page_key' => $page['page_key'],
                        'page_title' => $page['page_title'],
                        'page_url' => $page['page_url'],
                        'page_icon' => $page['page_icon'],
                        'page_description' => $page['page_description'],
                        'page_category' => $page['page_category'],
                        'access_level' => $page['access_level'],
                        'sort_order' => $page['role_sort_order'],
                        'file_exists' => true
                    ];
                } else {
                    $missingPages[] = [
                        'page_key' => $page['page_key'],
                        'page_title' => $page['page_title'],
                        'page_url' => $page['page_url'],
                        'error' => 'File not found: ' . $page['page_url']
                    ];
                }
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'role' => $role,
                    'pages' => $accessiblePages,
                    'missing_pages' => $missingPages,
                    'total_pages' => count($pages),
                    'accessible_pages' => count($accessiblePages),
                    'missing_count' => count($missingPages)
                ]
            ]);
            break;
            
        case "get_page_content":
            // Get page content with error handling
            $pageKey = $_GET['page_key'] ?? '';
            if (empty($pageKey)) {
                echo json_encode(['success' => false, 'message' => 'Page key required']);
                exit();
            }
            
            // Get page info
            $stmt = $pdo->prepare("SELECT * FROM dashboard_pages WHERE page_key = ? AND is_active = 1");
            $stmt->execute([$pageKey]);
            $page = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$page) {
                echo json_encode(['success' => false, 'message' => 'Page not found']);
                exit();
            }
            
            $filePath = __DIR__ . '/../' . $page['page_url'];
            
            if (!file_exists($filePath)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Page file not found',
                    'error_type' => 'file_missing',
                    'page_info' => $page,
                    'expected_path' => $page['page_url']
                ]);
                exit();
            }
            
            // Check if user has access
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
                SELECT access_level FROM role_dashboard_pages 
                WHERE role_key = ? AND page_key = ? AND is_visible = 1
            ");
            $stmt->execute([$role, $pageKey]);
            $access = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$access) {
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit();
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'page' => $page,
                    'access_level' => $access['access_level'],
                    'file_exists' => true,
                    'file_path' => $page['page_url']
                ]
            ]);
            break;
            
        case "check_page_exists":
            // Check if page exists
            $pageUrl = $_GET['page_url'] ?? '';
            if (empty($pageUrl)) {
                echo json_encode(['success' => false, 'message' => 'Page URL required']);
                exit();
            }
            
            $filePath = __DIR__ . '/../' . $pageUrl;
            $exists = file_exists($filePath);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'page_url' => $pageUrl,
                    'file_exists' => $exists,
                    'file_path' => $filePath,
                    'is_readable' => $exists ? is_readable($filePath) : false
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
