<?php
/**
 * KSP Lam Gabe Jaya - Accounts API
 * Handle account management operations
 */

require_once __DIR__ . '/BaseAPI.php';

class AccountsAPI extends BaseAPI {
    
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
     * GET /api/accounts.php - Get accounts list or single account
     */
    private function handleGet() {
        $this->requireAuth();
        
        if (isset($this->params['id'])) {
            $this->getAccount($this->params['id']);
        } else {
            $this->getAccounts();
        }
    }
    
    /**
     * Get accounts list with pagination and filtering
     */
    private function getAccounts() {
        $pagination = $this->getPaginationParams();
        $search = $this->sanitize($this->params['search'] ?? '');
        $account_type = $this->sanitize($this->params['account_type'] ?? '');
        $status = $this->sanitize($this->params['status'] ?? '');
        $member_id = $this->params['member_id'] ?? null;
        
        // Build query
        $sql = "SELECT a.*, m.full_name as member_name, m.member_number,
                       (SELECT COUNT(*) FROM transactions WHERE account_id = a.id) as transaction_count
                FROM accounts a 
                LEFT JOIN members m ON a.member_id = m.id 
                WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (a.account_number LIKE :search OR a.account_name LIKE :search OR m.full_name LIKE :search)";
            $params['search'] = "%$search%";
        }
        
        if ($account_type) {
            $sql .= " AND a.account_type = :account_type";
            $params['account_type'] = $account_type;
        }
        
        if ($status) {
            $sql .= " AND a.status = :status";
            $params['status'] = $status;
        }
        
        if ($member_id) {
            $sql .= " AND a.member_id = :member_id";
            $params['member_id'] = $member_id;
        }
        
        // Get total count
        $countSql = str_replace("a.*, m.full_name as member_name, m.member_number, (SELECT COUNT(*) FROM transactions WHERE account_id = a.id) as transaction_count", "COUNT(*)", $sql);
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get paginated results
        $sql .= " ORDER BY a.created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':limit', $pagination['limit'], PDO::PARAM_INT);
        $stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
        $stmt->execute();
        $accounts = $stmt->fetchAll();
        
        // Format data
        foreach ($accounts as &$account) {
            $account['balance'] = floatval($account['balance']);
            $account['interest_rate'] = $account['interest_rate'] ? floatval($account['interest_rate']) : null;
            $account['opened_date'] = date('Y-m-d', strtotime($account['opened_date']));
            $account['closed_date'] = $account['closed_date'] ? date('Y-m-d', strtotime($account['closed_date'])) : null;
        }
        
        $response = $this->buildPaginationResponse($accounts, $total, $pagination);
        $this->sendSuccess('Accounts retrieved successfully', $response);
    }
    
    /**
     * Get single account with transactions
     */
    private function getAccount($id) {
        $stmt = $this->db->prepare("
            SELECT a.*, m.full_name as member_name, m.member_number,
                   (SELECT COUNT(*) FROM transactions WHERE account_id = a.id) as transaction_count
            FROM accounts a 
            LEFT JOIN members m ON a.member_id = m.id 
            WHERE a.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $account = $stmt->fetch();
        
        if (!$account) {
            $this->sendError('Account not found', 404);
        }
        
        // Get recent transactions
        $stmt = $this->db->prepare("
            SELECT * FROM transactions 
            WHERE account_id = :id 
            ORDER BY transaction_date DESC, created_at DESC 
            LIMIT 10
        ");
        $stmt->execute(['id' => $id]);
        $transactions = $stmt->fetchAll();
        
        // Format data
        $account['balance'] = floatval($account['balance']);
        $account['interest_rate'] = $account['interest_rate'] ? floatval($account['interest_rate']) : null;
        $account['opened_date'] = date('Y-m-d', strtotime($account['opened_date']));
        $account['closed_date'] = $account['closed_date'] ? date('Y-m-d', strtotime($account['closed_date'])) : null;
        
        foreach ($transactions as &$transaction) {
            $transaction['amount'] = floatval($transaction['amount']);
            $transaction['transaction_date'] = date('Y-m-d', strtotime($transaction['transaction_date']));
        }
        
        $account['recent_transactions'] = $transactions;
        
        $this->sendSuccess('Account retrieved successfully', $account);
    }
    
    /**
     * POST /api/accounts.php - Create new account
     */
    private function handlePost() {
        $this->requireAuth();
        $this->requireRole('admin');
        
        $this->validateRequired(['member_id', 'account_type', 'account_name']);
        
        $data = [
            'member_id' => intval($this->params['member_id']),
            'account_type' => $this->sanitize($this->params['account_type']),
            'account_name' => $this->sanitize($this->params['account_name']),
            'balance' => floatval($this->params['balance'] ?? 0),
            'interest_rate' => isset($this->params['interest_rate']) ? floatval($this->params['interest_rate']) : null
        ];
        
        // Validate member exists
        $stmt = $this->db->prepare("SELECT id, status FROM members WHERE id = :member_id");
        $stmt->execute(['member_id' => $data['member_id']]);
        $member = $stmt->fetch();
        
        if (!$member) {
            $this->sendError('Member not found', 404);
        }
        
        if ($member['status'] !== 'active') {
            $this->sendError('Cannot create account for inactive member');
        }
        
        // Generate account number
        $data['account_number'] = $this->generateAccountNumber();
        
        // Insert account
        $stmt = $this->db->prepare("
            INSERT INTO accounts (member_id, account_number, account_type, account_name, balance, interest_rate, status, opened_date)
            VALUES (:member_id, :account_number, :account_type, :account_name, :balance, :interest_rate, 'active', CURDATE())
        ");
        
        $this->db->beginTransaction();
        try {
            $stmt->execute([
                'member_id' => $data['member_id'],
                'account_number' => $data['account_number'],
                'account_type' => $data['account_type'],
                'account_name' => $data['account_name'],
                'balance' => $data['balance'],
                'interest_rate' => $data['interest_rate']
            ]);
            
            $accountId = $this->db->lastInsertId();
            
            // Create initial transaction if balance > 0
            if ($data['balance'] > 0) {
                $this->createTransaction($accountId, 'credit', $data['balance'], 'Initial deposit');
            }
            
            $this->db->commit();
            
            $this->logActivity('CREATE_ACCOUNT', ['account_id' => $accountId, 'account_number' => $data['account_number']]);
            
            $data['id'] = $accountId;
            $this->sendSuccess('Account created successfully', $data, 201);
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->sendError('Failed to create account: ' . $e->getMessage());
        }
    }
    
    /**
     * PUT /api/accounts.php - Update account
     */
    private function handlePut() {
        $this->requireAuth();
        
        if (!isset($this->params['id'])) {
            $this->sendError('Account ID required');
        }
        
        $id = $this->params['id'];
        
        $data = [];
        if (isset($this->params['account_name'])) {
            $data['account_name'] = $this->sanitize($this->params['account_name']);
        }
        if (isset($this->params['interest_rate'])) {
            $data['interest_rate'] = floatval($this->params['interest_rate']);
        }
        if (isset($this->params['status'])) {
            $data['status'] = $this->sanitize($this->params['status']);
        }
        
        if (empty($data)) {
            $this->sendError('No data to update');
        }
        
        // Check if account exists
        $stmt = $this->db->prepare("SELECT id, status FROM accounts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $account = $stmt->fetch();
        
        if (!$account) {
            $this->sendError('Account not found', 404);
        }
        
        // Update account
        $setClause = [];
        foreach ($data as $key => $value) {
            $setClause[] = "$key = :$key";
        }
        
        if (isset($data['status']) && $data['status'] === 'closed') {
            $setClause[] = "closed_date = CURDATE()";
        }
        
        $sql = "UPDATE accounts SET " . implode(', ', $setClause) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $data['id'] = $id;
        $stmt->execute($data);
        
        $this->logActivity('UPDATE_ACCOUNT', ['account_id' => $id]);
        
        $this->sendSuccess('Account updated successfully', $data);
    }
    
    /**
     * DELETE /api/accounts.php - Delete account
     */
    private function handleDelete() {
        $this->requireAuth();
        $this->requireRole('admin');
        
        if (!isset($this->params['id'])) {
            $this->sendError('Account ID required');
        }
        
        $id = $this->params['id'];
        
        // Check if account exists
        $stmt = $this->db->prepare("SELECT id, account_number, balance FROM accounts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $account = $stmt->fetch();
        
        if (!$account) {
            $this->sendError('Account not found', 404);
        }
        
        if ($account['balance'] > 0) {
            $this->sendError('Cannot delete account with non-zero balance');
        }
        
        // Soft delete by updating status
        $stmt = $this->db->prepare("UPDATE accounts SET status = 'closed', closed_date = CURDATE() WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        $this->logActivity('DELETE_ACCOUNT', ['account_id' => $id, 'account_number' => $account['account_number']]);
        
        $this->sendSuccess('Account deleted successfully');
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
     * Create transaction
     */
    private function createTransaction($accountId, $type, $amount, $description) {
        $transactionCode = $this->generateTransactionCode();
        
        $stmt = $this->db->prepare("
            INSERT INTO transactions (transaction_code, account_id, transaction_type, amount, description, transaction_date, created_by)
            VALUES (:transaction_code, :account_id, :transaction_type, :amount, :description, CURDATE(), :created_by)
        ");
        
        $stmt->execute([
            'transaction_code' => $transactionCode,
            'account_id' => $accountId,
            'transaction_type' => $type,
            'amount' => $amount,
            'description' => $description,
            'created_by' => $this->user['id']
        ]);
    }
    
    /**
     * Generate transaction code
     */
    private function generateTransactionCode() {
        $prefix = 'TRX';
        $date = date('Ymd');
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM transactions WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        return $prefix . $date . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }
}

// Handle request
$api = new AccountsAPI();
$api->handleRequest();
?>
