<?php
/**
 * Offline Synchronization API Endpoint
 * Handles offline data sync for field operations
 */

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/OfflineSync.php';

// Initialize offline sync
$offlineSync = new OfflineSync();

// Get request method and data
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($method == 'OPTIONS') {
    exit(0);
}

// Route requests
$endpoint = $_GET['action'] ?? $input['action'] ?? '';

switch ($endpoint) {
    case 'sync_status':
        handleSyncStatus();
        break;
        
    case 'get_offline_data':
        handleGetOfflineData();
        break;
        
    case 'sync_queue':
        handleSyncQueue();
        break;
        
    case 'add_to_queue':
        handleAddToQueue();
        break;
        
    case 'process_sync':
        handleProcessSync();
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint tidak ditemukan'
        ]);
}

/**
 * Get sync status
 */
function handleSyncStatus() {
    global $offlineSync;
    
    $status = $offlineSync->getSyncStatus();
    
    echo json_encode([
        'success' => true,
        'data' => $status,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Get offline data for mobile
 */
function handleGetOfflineData() {
    global $offlineSync;
    
    $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
    $lastSync = $_GET['last_sync'] ?? $_POST['last_sync'] ?? null;
    
    if (!$userId) {
        echo json_encode([
            'success' => false,
            'message' => 'User ID diperlukan'
        ]);
        return;
    }
    
    $offlineData = $offlineSync->getOfflineData($userId, $lastSync);
    
    echo json_encode([
        'success' => true,
        'data' => $offlineData,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Handle sync queue operations
 */
function handleSyncQueue() {
    global $offlineSync;
    
    $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
    
    if (!$userId) {
        echo json_encode([
            'success' => false,
            'message' => 'User ID diperlukan'
        ]);
        return;
    }
    
    $db = Database::getInstance();
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        // Get sync queue for user
        $queue = $db->fetchAll(
            "SELECT * FROM sync_queue WHERE user_id = ? ORDER BY timestamp DESC",
            [$userId]
        );
        
        echo json_encode([
            'success' => true,
            'data' => $queue,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Add item to sync queue
 */
function handleAddToQueue() {
    global $offlineSync;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['type']) || !isset($data['table']) || !isset($data['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Data tidak lengkap'
        ]);
        return;
    }
    
    try {
        $queueId = $offlineSync->addToSyncQueue($data);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'queue_id' => $queueId,
                'message' => 'Data ditambahkan ke sync queue'
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

/**
 * Process sync queue
 */
function handleProcessSync() {
    global $offlineSync;
    
    if (!$offlineSync->isOnline()) {
        echo json_encode([
            'success' => false,
            'message' => 'Tidak ada koneksi internet'
        ]);
        return;
    }
    
    try {
        $result = $offlineSync->processSyncQueue();
        
        echo json_encode([
            'success' => true,
            'data' => $result,
            'message' => 'Sync queue diproses',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

?>
