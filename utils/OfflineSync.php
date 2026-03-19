<?php
/**
 * Offline Synchronization System
 * Handles data synchronization for offline operations
 */

class OfflineSync {
    private $db;
    private $syncQueue = [];
    private $lastSyncTime;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->lastSyncTime = $this->getLastSyncTime();
    }
    
    /**
     * Add data to sync queue
     */
    public function addToSyncQueue($data) {
        $syncItem = [
            'id' => uniqid(),
            'type' => $data['type'], // 'create', 'update', 'delete'
            'table' => $data['table'],
            'data' => json_encode($data['data']),
            'user_id' => $data['user_id'],
            'timestamp' => time(),
            'synced' => 0,
            'retry_count' => 0
        ];
        
        $this->db->insert('sync_queue', $syncItem);
        return $syncItem['id'];
    }
    
    /**
     * Process sync queue when online
     */
    public function processSyncQueue() {
        $queueItems = $this->db->fetchAll(
            "SELECT * FROM sync_queue WHERE synced = 0 AND retry_count < 5 ORDER BY timestamp ASC"
        );
        
        foreach ($queueItems as $item) {
            $success = $this->processSyncItem($item);
            
            if ($success) {
                $this->markAsSynced($item['id']);
            } else {
                $this->incrementRetryCount($item['id']);
            }
        }
        
        return [
            'total_items' => count($queueItems),
            'synced' => $this->getSyncedCount(),
            'failed' => $this->getFailedCount()
        ];
    }
    
    /**
     * Process individual sync item
     */
    private function processSyncItem($item) {
        try {
            $data = json_decode($item['data'], true);
            
            switch ($item['type']) {
                case 'create':
                    return $this->syncCreate($item['table'], $data);
                case 'update':
                    return $this->syncUpdate($item['table'], $data);
                case 'delete':
                    return $this->syncDelete($item['table'], $data);
                default:
                    return false;
            }
        } catch (Exception $e) {
            error_log("Sync error for item {$item['id']}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sync create operation
     */
    private function syncCreate($table, $data) {
        // Check if record already exists
        $existing = $this->db->fetchOne(
            "SELECT id FROM {$table} WHERE id = ?",
            [$data['id']]
        );
        
        if (!$existing) {
            $this->db->insert($table, $data);
            return true;
        }
        
        return false;
    }
    
    /**
     * Sync update operation
     */
    private function syncUpdate($table, $data) {
        $id = $data['id'];
        unset($data['id']);
        
        $affected = $this->db->update($table, $data, 'id = ?', [$id]);
        return $affected > 0;
    }
    
    /**
     * Sync delete operation
     */
    private function syncDelete($table, $data) {
        $affected = $this->db->delete($table, 'id = ?', [$data['id']]);
        return $affected > 0;
    }
    
    /**
     * Get data for offline storage
     */
    public function getOfflineData($userId, $lastSync = null) {
        $lastSync = $lastSync ?: $this->lastSyncTime;
        
        $data = [
            'members' => $this->getMembersForOffline($userId, $lastSync),
            'loans' => $this->getLoansForOffline($userId, $lastSync),
            'savings' => $this->getSavingsForOffline($userId, $lastSync),
            'transactions' => $this->getTransactionsForOffline($userId, $lastSync),
            'collection_queue' => $this->getCollectionQueueForOffline($userId, $lastSync),
            'last_sync' => time()
        ];
        
        return $data;
    }
    
    /**
     * Get members data for offline
     */
    private function getMembersForOffline($userId, $lastSync) {
        $sql = "SELECT * FROM members WHERE updated_at >= ? OR created_at >= ?";
        return $this->db->fetchAll($sql, [$lastSync, $lastSync]);
    }
    
    /**
     * Get loans data for offline
     */
    private function getLoansForOffline($userId, $lastSync) {
        $sql = "SELECT * FROM loans WHERE updated_at >= ? OR created_at >= ?";
        return $this->db->fetchAll($sql, [$lastSync, $lastSync]);
    }
    
    /**
     * Get savings data for offline
     */
    private function getSavingsForOffline($userId, $lastSync) {
        $sql = "SELECT * FROM savings WHERE updated_at >= ? OR created_at >= ?";
        return $this->db->fetchAll($sql, [$lastSync, $lastSync]);
    }
    
    /**
     * Get transactions data for offline
     */
    private function getTransactionsForOffline($userId, $lastSync) {
        $sql = "SELECT * FROM transactions WHERE created_at >= ?";
        return $this->db->fetchAll($sql, [$lastSync]);
    }
    
    /**
     * Get collection queue for offline
     */
    private function getCollectionQueueForOffline($userId, $lastSync) {
        $sql = "SELECT cq.*, m.name as member_name, m.member_number 
                FROM collection_queue cq 
                LEFT JOIN members m ON cq.member_id = m.id 
                WHERE cq.updated_at >= ? OR cq.created_at >= ?";
        return $this->db->fetchAll($sql, [$lastSync, $lastSync]);
    }
    
    /**
     * Mark item as synced
     */
    private function markAsSynced($itemId) {
        $this->db->update('sync_queue', [
            'synced' => 1,
            'synced_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$itemId]);
    }
    
    /**
     * Increment retry count
     */
    private function incrementRetryCount($itemId) {
        $this->db->query(
            "UPDATE sync_queue SET retry_count = retry_count + 1 WHERE id = ?",
            [$itemId]
        );
    }
    
    /**
     * Get last sync time
     */
    private function getLastSyncTime() {
        $result = $this->db->fetchOne("SELECT MAX(synced_at) as last_sync FROM sync_queue");
        return $result['last_sync'] ? strtotime($result['last_sync']) : 0;
    }
    
    /**
     * Get synced count
     */
    private function getSyncedCount() {
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM sync_queue WHERE synced = 1");
        return $result['count'];
    }
    
    /**
     * Get failed count
     */
    private function getFailedCount() {
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM sync_queue WHERE retry_count >= 5");
        return $result['count'];
    }
    
    /**
     * Check connection status
     */
    public function isOnline() {
        try {
            $this->db->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get sync status
     */
    public function getSyncStatus() {
        $pending = $this->db->fetchOne("SELECT COUNT(*) as count FROM sync_queue WHERE synced = 0");
        $failed = $this->db->fetchOne("SELECT COUNT(*) as count FROM sync_queue WHERE retry_count >= 5");
        
        return [
            'online' => $this->isOnline(),
            'pending_items' => $pending['count'],
            'failed_items' => $failed['count'],
            'last_sync' => $this->lastSyncTime,
            'sync_queue_size' => $this->getSyncQueueSize()
        ];
    }
    
    /**
     * Get sync queue size
     */
    private function getSyncQueueSize() {
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM sync_queue");
        return $result['count'];
    }
}

?>
