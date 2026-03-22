<?php
/**
 * KSP Lam Gabe Jaya - Members API
 * Handle member management operations
 */

require_once __DIR__ . '/BaseAPI.php';

class MembersAPI extends BaseAPI {
    
    protected function processRequest() {
        switch ($this->method) {
            case 'GET':
                $this->handleGet();
                break;
            case 'POST':
                $this->handlePost();
                break;
            case 'PUT':
                $this->handlePut();
                break;
            case 'DELETE':
                $this->handleDelete();
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }
    
    /**
     * GET /api/members.php - Get members list or single member
     */
    private function handleGet() {
        $this->requireAuth();
        
        if (isset($this->params['id'])) {
            $this->getMember($this->params['id']);
        } else {
            $this->getMembers();
        }
    }
    
    /**
     * Get members list with pagination and filtering
     */
    private function getMembers() {
        $pagination = $this->getPaginationParams();
        $search = $this->sanitize($this->params['search'] ?? '');
        $status = $this->sanitize($this->params['status'] ?? '');
        
        // Build query
        $sql = "SELECT m.*, u.username, u.email, u.status as user_status 
                FROM members m 
                LEFT JOIN users u ON m.user_id = u.id 
                WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (m.full_name LIKE :search OR m.member_number LIKE :search OR m.nik LIKE :search)";
            $params['search'] = "%$search%";
        }
        
        if ($status) {
            $sql .= " AND m.status = :status";
            $params['status'] = $status;
        }
        
        // Get total count
        $countSql = str_replace("m.*, u.username, u.email, u.status as user_status", "COUNT(*)", $sql);
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get paginated results
        $sql .= " ORDER BY m.created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':limit', $pagination['limit'], PDO::PARAM_INT);
        $stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
        $stmt->execute();
        $members = $stmt->fetchAll();
        
        // Format data
        foreach ($members as &$member) {
            $member['join_date'] = date('Y-m-d', strtotime($member['join_date']));
            $member['birth_date'] = $member['birth_date'] ? date('Y-m-d', strtotime($member['birth_date'])) : null;
            unset($member['password_changed_at']);
        }
        
        $response = $this->buildPaginationResponse($members, $total, $pagination);
        $this->sendSuccess('Members retrieved successfully', $response);
    }
    
    /**
     * Get single member
     */
    private function getMember($id) {
        $stmt = $this->db->prepare("
            SELECT m.*, u.username, u.email, u.status as user_status,
                   (SELECT COUNT(*) FROM accounts WHERE member_id = m.id AND status = 'active') as active_accounts,
                   (SELECT SUM(balance) FROM accounts WHERE member_id = m.id AND status = 'active') as total_balance,
                   (SELECT COUNT(*) FROM loans WHERE member_id = m.id AND status IN ('active', 'completed')) as total_loans
            FROM members m 
            LEFT JOIN users u ON m.user_id = u.id 
            WHERE m.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $member = $stmt->fetch();
        
        if (!$member) {
            $this->sendError('Member not found', 404);
        }
        
        // Format data
        $member['join_date'] = date('Y-m-d', strtotime($member['join_date']));
        $member['birth_date'] = $member['birth_date'] ? date('Y-m-d', strtotime($member['birth_date'])) : null;
        $member['total_balance'] = floatval($member['total_balance']);
        
        $this->sendSuccess('Member retrieved successfully', $member);
    }
    
    /**
     * POST /api/members.php - Create new member
     */
    private function handlePost() {
        $this->requireAuth();
        $this->requireRole('admin');
        
        $this->validateRequired(['full_name', 'nik', 'birth_date', 'gender', 'address']);
        
        $data = [
            'full_name' => $this->sanitize($this->params['full_name']),
            'nik' => $this->sanitize($this->params['nik']),
            'birth_date' => $this->params['birth_date'],
            'birth_place' => $this->sanitize($this->params['birth_place'] ?? ''),
            'gender' => $this->sanitize($this->params['gender']),
            'address' => $this->sanitize($this->params['address']),
            'phone' => $this->sanitize($this->params['phone'] ?? ''),
            'email' => $this->sanitize($this->params['email'] ?? ''),
            'join_date' => date('Y-m-d')
        ];
        
        // Validate NIK
        if (!$this->validateNIK($data['nik'])) {
            $this->sendError('Invalid NIK format');
        }
        
        // Validate date
        if (!$this->validateDate($data['birth_date'])) {
            $this->sendError('Invalid birth date format');
        }
        
        // Check if NIK already exists
        $stmt = $this->db->prepare("SELECT id FROM members WHERE nik = :nik");
        $stmt->execute(['nik' => $data['nik']]);
        if ($stmt->fetch()) {
            $this->sendError('NIK already exists');
        }
        
        // Generate member number
        $data['member_number'] = $this->generateMemberNumber();
        
        // Insert member
        $stmt = $this->db->prepare("
            INSERT INTO members (user_id, member_number, nik, full_name, birth_date, birth_place, 
                               gender, address, phone, email, join_date, status)
            VALUES (:user_id, :member_number, :nik, :full_name, :birth_date, :birth_place,
                    :gender, :address, :phone, :email, :join_date, 'active')
        ");
        
        $this->db->beginTransaction();
        try {
            $stmt->execute([
                'user_id' => $this->user['id'],
                'member_number' => $data['member_number'],
                'nik' => $data['nik'],
                'full_name' => $data['full_name'],
                'birth_date' => $data['birth_date'],
                'birth_place' => $data['birth_place'],
                'gender' => $data['gender'],
                'address' => $data['address'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'join_date' => $data['join_date']
            ]);
            
            $memberId = $this->db->lastInsertId();
            
            // Create default savings accounts
            $this->createDefaultAccounts($memberId);
            
            $this->db->commit();
            
            $this->logActivity('CREATE_MEMBER', ['member_id' => $memberId, 'member_number' => $data['member_number']]);
            
            $data['id'] = $memberId;
            $this->sendSuccess('Member created successfully', $data, 201);
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->sendError('Failed to create member: ' . $e->getMessage());
        }
    }
    
    /**
     * PUT /api/members.php - Update member
     */
    private function handlePut() {
        $this->requireAuth();
        
        if (!isset($this->params['id'])) {
            $this->sendError('Member ID required');
        }
        
        $id = $this->params['id'];
        $this->validateRequired(['full_name']);
        
        $data = [
            'full_name' => $this->sanitize($this->params['full_name']),
            'birth_place' => $this->sanitize($this->params['birth_place'] ?? ''),
            'address' => $this->sanitize($this->params['address']),
            'phone' => $this->sanitize($this->params['phone'] ?? ''),
            'email' => $this->sanitize($this->params['email'] ?? '')
        ];
        
        if (isset($this->params['status'])) {
            $data['status'] = $this->sanitize($this->params['status']);
        }
        
        // Check if member exists
        $stmt = $this->db->prepare("SELECT id FROM members WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) {
            $this->sendError('Member not found', 404);
        }
        
        // Update member
        $setClause = [];
        foreach ($data as $key => $value) {
            $setClause[] = "$key = :$key";
        }
        
        $sql = "UPDATE members SET " . implode(', ', $setClause) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $data['id'] = $id;
        $stmt->execute($data);
        
        $this->logActivity('UPDATE_MEMBER', ['member_id' => $id]);
        
        $this->sendSuccess('Member updated successfully', $data);
    }
    
    /**
     * DELETE /api/members.php - Delete member
     */
    private function handleDelete() {
        $this->requireAuth();
        $this->requireRole('admin');
        
        if (!isset($this->params['id'])) {
            $this->sendError('Member ID required');
        }
        
        $id = $this->params['id'];
        
        // Check if member exists
        $stmt = $this->db->prepare("SELECT id, member_number FROM members WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $member = $stmt->fetch();
        
        if (!$member) {
            $this->sendError('Member not found', 404);
        }
        
        // Check for active accounts or loans
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM accounts 
            WHERE member_id = :id AND status = 'active'
            UNION ALL
            SELECT COUNT(*) as count FROM loans 
            WHERE member_id = :id AND status IN ('active', 'pending')
        ");
        $stmt->execute(['id' => $id]);
        $results = $stmt->fetchAll();
        
        if ($results[0]['count'] > 0 || $results[1]['count'] > 0) {
            $this->sendError('Cannot delete member with active accounts or loans');
        }
        
        // Soft delete by updating status
        $stmt = $this->db->prepare("UPDATE members SET status = 'inactive' WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        $this->logActivity('DELETE_MEMBER', ['member_id' => $id, 'member_number' => $member['member_number']]);
        
        $this->sendSuccess('Member deleted successfully');
    }
    
    /**
     * Generate member number
     */
    private function generateMemberNumber() {
        $prefix = 'M';
        $year = date('Y');
        $month = date('m');
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM members 
            WHERE YEAR(join_date) = :year AND MONTH(join_date) = :month
        ");
        $stmt->execute(['year' => $year, 'month' => $month]);
        $count = $stmt->fetchColumn();
        
        return $prefix . $year . $month . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Create default accounts for new member
     */
    private function createDefaultAccounts($memberId) {
        $accounts = [
            ['type' => 'simpanan', 'name' => 'Tabungan Wajib', 'balance' => 500000],
            ['type' => 'simpanan', 'name' => 'Tabungan Pokok', 'balance' => 1000000]
        ];
        
        foreach ($accounts as $account) {
            $accountNumber = $this->generateAccountNumber();
            $stmt = $this->db->prepare("
                INSERT INTO accounts (member_id, account_number, account_type, account_name, balance, status, opened_date)
                VALUES (:member_id, :account_number, :account_type, :account_name, :balance, 'active', CURDATE())
            ");
            $stmt->execute([
                'member_id' => $memberId,
                'account_number' => $accountNumber,
                'account_type' => $account['type'],
                'account_name' => $account['name'],
                'balance' => $account['balance']
            ]);
        }
    }
    
    /**
     * Generate account number
     */
    private function generateAccountNumber() {
        $prefix = 'A';
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM accounts");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        return $prefix . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Validate NIK format
     */
    private function validateNIK($nik) {
        return preg_match('/^[0-9]{16}$/', $nik);
    }
}

// Handle request
$api = new MembersAPI();
$api->handleRequest();
?>
