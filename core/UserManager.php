<?php
/**
 * KSP Lam Gabe Jaya - User Management Class
 * Based on OOP best practices from documentation
 */

class UserManager {
    private $db;
    private $table = 'users';
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get user by ID with validation
     * @param int $userId
     * @return array|null
     */
    public function getUserById($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new InvalidArgumentException("Invalid user ID");
        }
        
        $sql = "SELECT id, username, email, role, full_name, status, created_at, last_login 
                FROM {$this->table} 
                WHERE id = ? AND status = 'active'";
        
        return $this->db->fetchOne($sql, [$userId]);
    }
    
    /**
     * Update user last activity
     * @param int $userId
     * @return bool
     */
    public function updateLastActivity($userId) {
        $sql = "UPDATE {$this->table} 
                SET last_activity = NOW() 
                WHERE id = ?";
        
        try {
            $this->db->query($sql, [$userId]);
            return true;
        } catch (Exception $e) {
            error_log("Failed to update last activity: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user permissions based on role
     * @param string $role
     * @return array
     */
    public function getRolePermissions($role) {
        $permissions = [
            'bos' => ['all'],
            'admin' => ['manage_users', 'view_reports', 'manage_data', 'approve_loans'],
            'teller' => ['process_transactions', 'view_customers', 'manage_deposits'],
            'collector' => ['manage_collections', 'view_routes', 'track_payments'],
            'nasabah' => ['view_own_data', 'make_transactions', 'view_loans']
        ];
        
        return $permissions[$role] ?? [];
    }
    
    /**
     * Validate user session
     * @param array $session
     * @return array
     */
    public function validateSession($session) {
        if (!isset($session['user']) || !isset($session['last_activity'])) {
            return ['valid' => false, 'reason' => 'Session incomplete'];
        }
        
        $userId = $session['user']['id'] ?? null;
        $lastActivity = $session['last_activity'] ?? 0;
        
        // Check session timeout (30 minutes)
        if (time() - $lastActivity > 1800) {
            return ['valid' => false, 'reason' => 'Session expired'];
        }
        
        // Validate user still exists and is active
        $user = $this->getUserById($userId);
        if (!$user) {
            return ['valid' => false, 'reason' => 'User not found or inactive'];
        }
        
        // Update last activity
        $this->updateLastActivity($userId);
        
        return ['valid' => true, 'user' => $user];
    }
}

/**
 * KSP Lam Gabe Jaya - Dashboard Manager Class
 * Handles dashboard logic and widget generation
 */

class DashboardManager {
    private $userManager;
    private $user;
    
    public function __construct($user) {
        $this->userManager = new UserManager();
        $this->user = $user;
    }
    
    /**
     * Get dashboard layout configuration
     * @param string $role
     * @return array
     */
    public function getDashboardLayout($role) {
        $layouts = [
            'bos' => [
                'columns' => 4,
                'widget_size' => 'col-md-3',
                'show_analytics' => true,
                'show_system_info' => true
            ],
            'admin' => [
                'columns' => 3,
                'widget_size' => 'col-md-4',
                'show_analytics' => true,
                'show_system_info' => false
            ],
            'teller' => [
                'columns' => 3,
                'widget_size' => 'col-md-4',
                'show_analytics' => false,
                'show_system_info' => false
            ],
            'collector' => [
                'columns' => 2,
                'widget_size' => 'col-md-6',
                'show_analytics' => false,
                'show_system_info' => false
            ],
            'nasabah' => [
                'columns' => 2,
                'widget_size' => 'col-md-6',
                'show_analytics' => false,
                'show_system_info' => false
            ]
        ];
        
        return $layouts[$role] ?? $layouts['nasabah'];
    }
    
    /**
     * Get menu items for specific role
     * @param string $role
     * @return array
     */
    public function getMenuItems($role) {
        $menus = [
            'bos' => [
                ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => '#dashboard'],
                ['key' => 'laporan', 'title' => 'Laporan Keuangan', 'icon' => 'fas fa-chart-line', 'url' => '#laporan'],
                ['key' => 'nasabah', 'title' => 'Data Nasabah', 'icon' => 'fas fa-users', 'url' => '#nasabah'],
                ['key' => 'pinjaman', 'title' => 'Pinjaman', 'icon' => 'fas fa-hand-holding-usd', 'url' => '#pinjaman'],
                ['key' => 'simpanan', 'title' => 'Simpanan', 'icon' => 'fas fa-piggy-bank', 'url' => '#simpanan'],
                ['key' => 'pengaturan', 'title' => 'Pengaturan', 'icon' => 'fas fa-cog', 'url' => '#pengaturan']
            ],
            'admin' => [
                ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => '#dashboard'],
                ['key' => 'nasabah', 'title' => 'Nasabah', 'icon' => 'fas fa-users', 'url' => '#nasabah'],
                ['key' => 'pinjaman', 'title' => 'Pinjaman', 'icon' => 'fas fa-hand-holding-usd', 'url' => '#pinjaman'],
                ['key' => 'simpanan', 'title' => 'Simpanan', 'icon' => 'fas fa-piggy-bank', 'url' => '#simpanan'],
                ['key' => 'transaksi', 'title' => 'Transaksi', 'icon' => 'fas fa-exchange-alt', 'url' => '#transaksi'],
                ['key' => 'laporan', 'title' => 'Laporan', 'icon' => 'fas fa-chart-bar', 'url' => '#laporan']
            ],
            'teller' => [
                ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => '#dashboard'],
                ['key' => 'nasabah', 'title' => 'Nasabah', 'icon' => 'fas fa-users', 'url' => '#nasabah'],
                ['key' => 'setoran', 'title' => 'Setoran', 'icon' => 'fas fa-plus-circle', 'url' => '#setoran'],
                ['key' => 'penarikan', 'title' => 'Penarikan', 'icon' => 'fas fa-minus-circle', 'url' => '#penarikan'],
                ['key' => 'pembayaran', 'title' => 'Pembayaran', 'icon' => 'fas fa-credit-card', 'url' => '#pembayaran'],
                ['key' => 'laporan_harian', 'title' => 'Laporan Harian', 'icon' => 'fas fa-clipboard-list', 'url' => '#laporan_harian']
            ],
            'collector' => [
                ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => '#dashboard'],
                ['key' => 'rute', 'title' => 'Rute Kunjungan', 'icon' => 'fas fa-route', 'url' => '#rute'],
                ['key' => 'jadwal', 'title' => 'Jadwal Kunjungan', 'icon' => 'fas fa-calendar', 'url' => '#jadwal'],
                ['key' => 'nasabah_kunjungan', 'title' => 'Nasabah Kunjungan', 'icon' => 'fas fa-users', 'url' => '#nasabah_kunjungan'],
                ['key' => 'kutipan', 'title' => 'Kutipan', 'icon' => 'fas fa-money-bill', 'url' => '#kutipan'],
                ['key' => 'gps_log', 'title' => 'GPS Log', 'icon' => 'fas fa-map-marker-alt', 'url' => '#gps_log']
            ],
            'nasabah' => [
                ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => '#dashboard'],
                ['key' => 'profil', 'title' => 'Profil Saya', 'icon' => 'fas fa-user', 'url' => '#profil'],
                ['key' => 'simpanan_saya', 'title' => 'Simpanan Saya', 'icon' => 'fas fa-piggy-bank', 'url' => '#simpanan_saya'],
                ['key' => 'pinjaman_saya', 'title' => 'Pinjaman Saya', 'icon' => 'fas fa-hand-holding-usd', 'url' => '#pinjaman_saya'],
                ['key' => 'riwayat', 'title' => 'Riwayat Transaksi', 'icon' => 'fas fa-history', 'url' => '#riwayat'],
                ['key' => 'pembayaran', 'title' => 'Pembayaran', 'icon' => 'fas fa-credit-card', 'url' => '#pembayaran']
            ]
        ];
        
        return $menus[$role] ?? $menus['nasabah'];
    }
    
    /**
     * Get dashboard widgets for role
     * @param string $role
     * @return array
     */
    public function getDashboardWidgets($role) {
        $widgetConfig = $this->getDashboardLayout($role);
        
        $widgets = [];
        
        // Common widgets for all roles
        $widgets['overview_stats'] = [
            'type' => 'stats',
            'title' => 'Overview',
            'size' => $widgetConfig['widget_size'],
            'data' => $this->getOverviewStats($role)
        ];
        
        // Role-specific widgets
        switch ($role) {
            case 'bos':
                $widgets['financial_summary'] = [
                    'type' => 'stats',
                    'title' => 'Ringkasan Keuangan',
                    'size' => $widgetConfig['widget_size'],
                    'data' => $this->getFinancialSummary()
                ];
                $widgets['system_info'] = [
                    'type' => 'info',
                    'title' => 'Informasi Sistem',
                    'size' => $widgetConfig['widget_size'],
                    'data' => $this->getSystemInfo()
                ];
                break;
                
            case 'admin':
                $widgets['member_stats'] = [
                    'type' => 'stats',
                    'title' => 'Statistik Anggota',
                    'size' => $widgetConfig['widget_size'],
                    'data' => $this->getMemberStats()
                ];
                $widgets['recent_activity'] = [
                    'type' => 'activity',
                    'title' => 'Aktivitas Terbaru',
                    'size' => $widgetConfig['widget_size'],
                    'data' => $this->getRecentActivity()
                ];
                break;
                
            case 'teller':
                $widgets['today_transactions'] = [
                    'type' => 'stats',
                    'title' => 'Transaksi Hari Ini',
                    'size' => $widgetConfig['widget_size'],
                    'data' => $this->getTodayTransactions()
                ];
                $widgets['quick_actions'] = [
                    'type' => 'actions',
                    'title' => 'Aksi Cepat',
                    'size' => $widgetConfig['widget_size'],
                    'data' => $this->getQuickActions('teller')
                ];
                break;
                
            case 'collector':
                $widgets['collection_stats'] = [
                    'type' => 'stats',
                    'title' => 'Statistik Kutipan',
                    'size' => $widgetConfig['widget_size'],
                    'data' => $this->getCollectionStats()
                ];
                $widgets['today_schedule'] = [
                    'type' => 'schedule',
                    'title' => 'Jadwal Hari Ini',
                    'size' => $widgetConfig['widget_size'],
                    'data' => $this->getTodaySchedule()
                ];
                break;
                
            case 'nasabah':
                $widgets['account_summary'] = [
                    'type' => 'account',
                    'title' => 'Ringkasan Akun',
                    'size' => $widgetConfig['widget_size'],
                    'data' => $this->getAccountSummary()
                ];
                $widgets['recent_transactions'] = [
                    'type' => 'transactions',
                    'title' => 'Transaksi Terbaru',
                    'size' => $widgetConfig['widget_size'],
                    'data' => $this->getRecentTransactions()
                ];
                break;
        }
        
        return $widgets;
    }
    
    /**
     * Get overview statistics
     * @param string $role
     * @return array
     */
    private function getOverviewStats($role) {
        $db = new Database();
        
        switch ($role) {
            case 'bos':
                return [
                    'total_members' => $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE status = 'active'")['count'],
                    'total_deposits' => $db->fetchOne("SELECT SUM(amount) as total FROM deposits WHERE status = 'active'")['total'],
                    'total_loans' => $db->fetchOne("SELECT SUM(amount) as total FROM loans WHERE status = 'active'")['total'],
                    'total_users' => $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count']
                ];
                
            case 'admin':
                return [
                    'active_members' => $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE status = 'active'")['count'],
                    'pending_loans' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status = 'pending'")['count'],
                    'today_transactions' => $db->fetchOne("SELECT COUNT(*) as count FROM transactions WHERE DATE(created_at) = CURDATE()")['count'],
                    'total_balance' => $db->fetchOne("SELECT SUM(balance) as total FROM accounts")['total']
                ];
                
            case 'teller':
                return [
                    'today_transactions' => $db->fetchOne("SELECT COUNT(*) as count FROM transactions WHERE DATE(created_at) = CURDATE() AND teller_id = ?", [$this->user['id']])['count'],
                    'total_amount' => $db->fetchOne("SELECT SUM(amount) as total FROM transactions WHERE DATE(created_at) = CURDATE() AND teller_id = ?", [$this->user['id']])['total'],
                    'pending_verifications' => $db->fetchOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'pending' AND teller_id = ?", [$this->user['id']])['count'],
                    'new_members_today' => $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE DATE(created_at) = CURDATE()")['count']
                ];
                
            default:
                return [
                    'account_balance' => $db->fetchOne("SELECT balance FROM accounts WHERE member_id = ?", [$this->user['member_id']])['balance'],
                    'active_loans' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status = 'active'", [$this->user['member_id']])['count'],
                    'pending_payments' => $db->fetchOne("SELECT COUNT(*) as count FROM loan_payments WHERE member_id = ? AND status = 'pending'", [$this->user['member_id']])['count'],
                    'last_transaction' => $db->fetchOne("SELECT created_at FROM transactions WHERE member_id = ? ORDER BY created_at DESC LIMIT 1", [$this->user['member_id']])['created_at']
                ];
        }
    }
    
    // Additional private methods for other widget data...
    private function getFinancialSummary() {
        // Implementation for financial summary
        return [];
    }
    
    private function getSystemInfo() {
        // Implementation for system information
        return [];
    }
    
    private function getMemberStats() {
        // Implementation for member statistics
        return [];
    }
    
    private function getRecentActivity() {
        // Implementation for recent activity
        return [];
    }
    
    private function getTodayTransactions() {
        // Implementation for today's transactions
        return [];
    }
    
    private function getQuickActions($role) {
        // Implementation for quick actions
        return [];
    }
    
    private function getCollectionStats() {
        // Implementation for collection statistics
        return [];
    }
    
    private function getTodaySchedule() {
        // Implementation for today's schedule
        return [];
    }
    
    private function getAccountSummary() {
        // Implementation for account summary
        return [];
    }
    
    private function getRecentTransactions() {
        // Implementation for recent transactions
        return [];
    }
}
?>
