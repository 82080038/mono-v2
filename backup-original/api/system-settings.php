<?php
/**
 * System Settings API for Admin
 * Handles system configuration management
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Include required files
require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/DatabaseHelper.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/DataValidator.php';
require_once __DIR__ . '/SecurityLogger.php';

// Initialize services
Logger::initialize();
$securityLogger = SecurityLogger::getInstance();
$db = DatabaseHelper::getInstance();
$validator = new DataValidator();

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'data' => null,
    'errors' => [],
    'timestamp' => date('Y-m-d H:i:s')
];

// Authentication middleware
function requireAuth($role = 'admin') {
    global $db;
    
    $token = getTokenFromRequest();
    if (!$token) {
        throw new Exception('Authentication required');
    }
    
    $tokenData = validateJWTToken($token);
    if (!$tokenData) {
        throw new Exception('Invalid token');
    }
    
    $user = $db->fetchOne(
        "SELECT * FROM users WHERE id = ? AND is_active = 1",
        [$tokenData['user_id']]
    );
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    if ($user['role'] !== 'admin') {
        throw new Exception('Admin access required');
    }
    
    return array_merge($user, $tokenData);
}

function getTokenFromRequest() {
    $headers = getallheaders();
    return $headers['Authorization'] ?? $headers['authorization'] ?? $_REQUEST['token'] ?? null;
}

function validateJWTToken($token) {
    if (!$token) {
        return null;
    }
    
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }
    
    $payload = base64_decode($parts[1]);
    $payloadData = json_decode($payload, true);
    
    if (!$payloadData || $payloadData['exp'] < time()) {
        return null;
    }
    
    return $payloadData;
}

function logAudit($action, $table, $recordId, $oldValues = null, $newValues = null) {
    global $db, $securityLogger;
    
    $user = getCurrentUser();
    if ($user) {
        $db->insert('audit_logs', [
            'user_id' => $user['id'],
            'action' => $action,
            'table_name' => $table,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        $securityLogger->logDataModification($table, $action, $oldValues, $newValues);
    }
}

function getCurrentUser() {
    global $db;
    
    $token = getTokenFromRequest();
    if (!$token) {
        return null;
    }
    
    $tokenData = validateJWTToken($token);
    if (!$tokenData) {
        return null;
    }
    
    return $db->fetchOne(
        "SELECT * FROM users WHERE id = ? AND is_active = 1",
        [$tokenData['user_id']]
    );
}

// Handle API requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($action, $db, $validator);
            break;
        case 'POST':
            handlePostRequest($action, $db, $validator);
            break;
        case 'PUT':
            handlePutRequest($action, $db, $validator);
            break;
        case 'DELETE':
            handleDeleteRequest($action, $db, $validator);
            break;
        default:
            $response['message'] = 'Method not allowed';
            echo json_encode($response);
            break;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    echo json_encode($response);
}

function handleGetRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'list':
            handleListSettings($db, $validator);
            break;
        case 'detail':
            handleGetSetting($db, $validator);
            break;
        case 'public':
            handleGetPublicSettings($db, $validator);
            break;
        case 'backup':
            handleBackupSettings($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handlePostRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'create':
            handleCreateSetting($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handlePutRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'update':
            handleUpdateSetting($db, $validator);
            break;
        case 'bulk_update':
            handleBulkUpdateSettings($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleDeleteRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'delete':
            handleDeleteSetting($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleListSettings($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 50);
    $search = $_GET['search'] ?? '';
    $type = $_GET['type'] ?? '';
    $public = $_GET['public'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = [];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(setting_key LIKE ? OR description LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam]);
    }
    
    if (!empty($type)) {
        $whereConditions[] = "setting_type = ?";
        $params[] = $type;
    }
    
    if ($public !== '') {
        $whereConditions[] = "is_public = ?";
        $params[] = $public === 'true' ? 1 : 0;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM system_settings $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get settings
    $sql = "SELECT * FROM system_settings $whereClause ORDER BY setting_key LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $settings = $db->fetchAll($sql, $params);
    
    // Convert setting values based on type
    foreach ($settings as &$setting) {
        $setting['value'] = convertSettingValue($setting['setting_value'], $setting['setting_type']);
    }
    
    $response['success'] = true;
    $response['message'] = 'Settings retrieved successfully';
    $response['data'] = [
        'settings' => $settings,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    echo json_encode($response);
}

function handleGetSetting($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $settingKey = $_GET['key'] ?? '';
    
    if (empty($settingKey)) {
        $response['message'] = 'Setting key required';
        echo json_encode($response);
        return;
    }
    
    $setting = $db->fetchOne("SELECT * FROM system_settings WHERE setting_key = ?", [$settingKey]);
    
    if (!$setting) {
        $response['message'] = 'Setting not found';
        echo json_encode($response);
        return;
    }
    
    $setting['value'] = convertSettingValue($setting['setting_value'], $setting['setting_type']);
    
    $response['success'] = true;
    $response['message'] = 'Setting retrieved successfully';
    $response['data'] = $setting;
    
    echo json_encode($response);
}

function handleGetPublicSettings($db, $validator) {
    global $response;
    
    // Public settings don't require authentication
    $settings = $db->fetchAll("SELECT setting_key, setting_value, setting_type FROM system_settings WHERE is_public = 1 ORDER BY setting_key");
    
    // Convert setting values based on type
    $publicSettings = [];
    foreach ($settings as $setting) {
        $publicSettings[$setting['setting_key']] = convertSettingValue($setting['setting_value'], $setting['setting_type']);
    }
    
    $response['success'] = true;
    $response['message'] = 'Public settings retrieved successfully';
    $response['data'] = $publicSettings;
    
    echo json_encode($response);
}

function handleCreateSetting($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'setting_key' => 'required|string|min:3',
        'setting_value' => 'required|string',
        'setting_type' => 'required|in:string,number,boolean,json',
        'description' => 'string',
        'is_public' => 'boolean'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Check if setting key already exists
    $existing = $db->fetchOne("SELECT id FROM system_settings WHERE setting_key = ?", [$input['setting_key']]);
    if ($existing) {
        $response['message'] = 'Setting key already exists';
        echo json_encode($response);
        return;
    }
    
    // Validate and convert value based on type
    $validatedValue = validateSettingValue($input['setting_value'], $input['setting_type']);
    if ($validatedValue === null) {
        $response['message'] = 'Invalid setting value for type ' . $input['setting_type'];
        echo json_encode($response);
        return;
    }
    
    $settingData = [
        'setting_key' => $input['setting_key'],
        'setting_value' => $input['setting_value'],
        'setting_type' => $input['setting_type'],
        'description' => $input['description'] ?? '',
        'is_public' => $input['is_public'] ?? false,
        'updated_by' => $user['id'],
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $settingId = $db->insert('system_settings', $settingData);
    
    logAudit('CREATE', 'system_settings', $settingId, null, $settingData);
    
    $response['success'] = true;
    $response['message'] = 'Setting created successfully';
    $response['data'] = [
        'setting_id' => $settingId,
        'value' => convertSettingValue($settingData['setting_value'], $settingData['setting_type'])
    ];
    
    echo json_encode($response);
}

function handleUpdateSetting($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    $settingKey = $input['setting_key'] ?? '';
    
    if (empty($settingKey)) {
        $response['message'] = 'Setting key required';
        echo json_encode($response);
        return;
    }
    
    $currentSetting = $db->fetchOne("SELECT * FROM system_settings WHERE setting_key = ?", [$settingKey]);
    if (!$currentSetting) {
        $response['message'] = 'Setting not found';
        echo json_encode($response);
        return;
    }
    
    $rules = [
        'setting_value' => 'required|string',
        'setting_type' => 'in:string,number,boolean,json',
        'description' => 'string',
        'is_public' => 'boolean'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Validate and convert value based on type
    $settingType = $input['setting_type'] ?? $currentSetting['setting_type'];
    $validatedValue = validateSettingValue($input['setting_value'], $settingType);
    if ($validatedValue === null) {
        $response['message'] = 'Invalid setting value for type ' . $settingType;
        echo json_encode($response);
        return;
    }
    
    $updateData = [
        'setting_value' => $input['setting_value'],
        'setting_type' => $settingType,
        'description' => $input['description'] ?? $currentSetting['description'],
        'is_public' => $input['is_public'] ?? $currentSetting['is_public'],
        'updated_by' => $user['id'],
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $db->update('system_settings', $updateData, 'setting_key = ?', [$settingKey]);
    
    logAudit('UPDATE', 'system_settings', $currentSetting['id'], $currentSetting, $updateData);
    
    $response['success'] = true;
    $response['message'] = 'Setting updated successfully';
    $response['data'] = [
        'value' => convertSettingValue($updateData['setting_value'], $updateData['setting_type'])
    ];
    
    echo json_encode($response);
}

function handleBulkUpdateSettings($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    $settings = $input['settings'] ?? [];
    
    if (empty($settings) || !is_array($settings)) {
        $response['message'] = 'Settings array required';
        echo json_encode($response);
        return;
    }
    
    $updatedSettings = [];
    $errors = [];
    
    foreach ($settings as $settingData) {
        $settingKey = $settingData['setting_key'] ?? '';
        $settingValue = $settingData['setting_value'] ?? '';
        
        if (empty($settingKey) || $settingValue === '') {
            $errors[] = "Invalid setting data for key: $settingKey";
            continue;
        }
        
        $currentSetting = $db->fetchOne("SELECT * FROM system_settings WHERE setting_key = ?", [$settingKey]);
        if (!$currentSetting) {
            $errors[] = "Setting not found: $settingKey";
            continue;
        }
        
        // Validate value based on current type
        $validatedValue = validateSettingValue($settingValue, $currentSetting['setting_type']);
        if ($validatedValue === null) {
            $errors[] = "Invalid value for setting: $settingKey";
            continue;
        }
        
        $updateData = [
            'setting_value' => $settingValue,
            'updated_by' => $user['id'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $db->update('system_settings', $updateData, 'setting_key = ?', [$settingKey]);
        
        $updatedSettings[] = [
            'setting_key' => $settingKey,
            'old_value' => convertSettingValue($currentSetting['setting_value'], $currentSetting['setting_type']),
            'new_value' => convertSettingValue($settingValue, $currentSetting['setting_type'])
        ];
    }
    
    $response['success'] = true;
    $response['message'] = 'Settings updated successfully';
    $response['data'] = [
        'updated_settings' => $updatedSettings,
        'errors' => $errors
    ];
    
    if (!empty($errors)) {
        $response['message'] = 'Settings updated with some errors';
    }
    
    echo json_encode($response);
}

function handleDeleteSetting($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $settingKey = $_GET['key'] ?? '';
    
    if (empty($settingKey)) {
        $response['message'] = 'Setting key required';
        echo json_encode($response);
        return;
    }
    
    $currentSetting = $db->fetchOne("SELECT * FROM system_settings WHERE setting_key = ?", [$settingKey]);
    if (!$currentSetting) {
        $response['message'] = 'Setting not found';
        echo json_encode($response);
        return;
    }
    
    // Prevent deletion of critical system settings
    $criticalSettings = ['app_name', 'app_version', 'database_version'];
    if (in_array($settingKey, $criticalSettings)) {
        $response['message'] = 'Cannot delete critical system setting';
        echo json_encode($response);
        return;
    }
    
    $db->delete('system_settings', 'setting_key = ?', [$settingKey]);
    
    logAudit('DELETE', 'system_settings', $currentSetting['id'], $currentSetting, null);
    
    $response['success'] = true;
    $response['message'] = 'Setting deleted successfully';
    
    echo json_encode($response);
}

function handleBackupSettings($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $settings = $db->fetchAll("SELECT * FROM system_settings ORDER BY setting_key");
    
    $backupData = [
        'backup_date' => date('Y-m-d H:i:s'),
        'backup_by' => $user['username'],
        'settings' => []
    ];
    
    foreach ($settings as $setting) {
        $backupData['settings'][] = [
            'setting_key' => $setting['setting_key'],
            'setting_value' => $setting['setting_value'],
            'setting_type' => $setting['setting_type'],
            'description' => $setting['description'],
            'is_public' => $setting['is_public']
        ];
    }
    
    $response['success'] = true;
    $response['message'] = 'Settings backup created successfully';
    $response['data'] = $backupData;
    
    echo json_encode($response);
}

function validateSettingValue($value, $type) {
    switch ($type) {
        case 'boolean':
            if (is_bool($value)) return $value;
            if (is_string($value)) {
                $lower = strtolower($value);
                if ($lower === 'true' || $lower === '1' || $lower === 'on') return true;
                if ($lower === 'false' || $lower === '0' || $lower === 'off') return false;
            }
            if (is_numeric($value)) return (bool)$value;
            return null;
            
        case 'number':
            if (is_numeric($value)) return $value;
            return null;
            
        case 'json':
            if (is_string($value)) {
                json_decode($value);
                return json_last_error() === JSON_ERROR_NONE ? $value : null;
            }
            if (is_array($value) || is_object($value)) {
                return json_encode($value);
            }
            return null;
            
        case 'string':
        default:
            return is_string($value) ? $value : null;
    }
}

function convertSettingValue($value, $type) {
    switch ($type) {
        case 'boolean':
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            
        case 'number':
            return is_numeric($value) ? (float)$value : $value;
            
        case 'json':
            $decoded = json_decode($value, true);
            return $decoded !== null ? $decoded : $value;
            
        case 'string':
        default:
            return $value;
    }
}
?>
