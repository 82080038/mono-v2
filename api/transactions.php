<?php
/**
 * KSP Lam Gabe Jaya - Transactions API
 * Handle transaction operations
 */

require_once __DIR__ . '/BaseAPI.php';

class TransactionsAPI extends BaseAPI {
    
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
            default:
                $this->sendError('Method not allowed', 405);
        }
    }
    
    /**
     * GET /api/transactions.php - Get transactions list or single transaction
     */
    private function handleGet() {
        $this->requireAuth();
        
        if (isset($this->params['id'])) {
            $this->getTransaction($this->params['id']);
        } else {
            $this->getTransactions();
        }
    }
    
    /**
     * Get transactions list with pagination and filtering
     */
    private function getTransactions() {
        $pagination = $this->getPaginationParams();
        $search = $this->sanitize($this->params['search'] ?? '');
        $transaction_type = $this->sanitize($this->params['transaction_type'] ?? '');
        $account_id = $this->params['account_id'] ?? null;
        $date_from = $this->params['date_from'] ?? null;
        $date_to = $this->params['date_to'] ?? null;
        
        // Build query
        $sql = "SELECT t.*, a.account_number, a.account_name, m.full_name as member_name,
                       CONCAT(m.member_number, ' - ', m.full_name) as member_info
                FROM transactions t 
                LEFT JOIN accounts a ON t.account_id = a.id 
                LEFT JOIN members m ON a.member_id = m.id 
                WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (t.transaction_code LIKE :search OR t.description LIKE :search OR a.account_number LIKE :search)";
            $params['search'] = "%$search%";
        }
        
        if ($transaction_type) {
            $sql .= " AND t.transaction_type = :transaction_type";
            $params['transaction_type'] = $transaction_type;
        }
        
        if ($account_id) {
            $sql .= " AND t.account_id = :account_id";
            $params['account_id'] = $account_id;
        }
        
        if ($date_from) {
            if (!$this->validateDate($date_from)) {
                $this->sendError('Invalid date_from format');
            }
            $sql .= " AND t.transaction_date >= :date_from";
            $params['date_from'] = $date_from;
        }
        
        if ($date_to) {
            if (!$this->validateDate($date_to)) {
                $this->sendError('Invalid date_to format');
            }
            $sql .= " AND t.transaction_date <= :date_to";
            $params['date_to'] = $date_to;
        }
        
        // Get total count
        $countSql = str_replace("t.*, a.account_number, a.account_name, m.full_name as member_name, CONCAT(m.member_number, ' - ', m.full_name) as member_info", "COUNT(*)", $sql);
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get paginated results
        $sql .= " ORDER BY t.transaction_date DESC, t.created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':limit', $pagination['limit'], PDO::PARAM_INT);
        $stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
        $stmt->execute();
        $transactions = $stmt->fetchAll();
        
        // Format data
        foreach ($transactions as &$transaction) {
            $transaction['amount'] = floatval($transaction['amount']);
            $transaction['transaction_date'] = date('Y-m-d', strtotime($transaction['transaction_date']));
            $transaction['created_at'] = date('Y-m-d H:i:s', strtotime($transaction['created_at']));
        }
        
        $response = $this->buildPaginationResponse($transactions, $total, $pagination);
        $this->sendSuccess('Transactions retrieved successfully', $response);
    }
    
    /**
     * Get single transaction
     */
    private function getTransaction($id) {
        $stmt = $this->db->prepare("
            SELECT t.*, a.account_number, a.account_name, m.full_name as member_name, m.member_number,
                   u.username as created_by_name
            FROM transactions t 
            LEFT JOIN accounts a ON t.account_id = a.id 
            LEFT JOIN members m ON a.member_id = m.id 
            LEFT JOIN users u ON t.created_by = u.id 
            WHERE t.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $transaction = $stmt->fetch();
        
        if (!$transaction) {
            $this->sendError('Transaction not found', 404);
        }
        
        // Format data
        $transaction['amount'] = floatval($transaction['amount']);
        $transaction['transaction_date'] = date('Y-m-d', strtotime($transaction['transaction_date']));
        $transaction['created_at'] = date('Y-m-d H:i:s', strtotime($transaction['created_at']));
        
        $this->sendSuccess('Transaction retrieved successfully', $transaction);
    }
    
    /**
     * POST /api/transactions.php - Create new transaction
     */
    private function handlePost() {
        $this->requireAuth();
        
        $this->validateRequired(['account_id', 'transaction_type', 'amount']);
        
        $data = [
            'account_id' => intval($this->params['account_id']),
            'transaction_type' => $this->sanitize($this->params['transaction_type']),
            'amount' => floatval($this->params['amount']),
            'description' => $this->sanitize($this->params['description'] ?? ''),
            'transaction_date' => $this->params['transaction_date'] ?? date('Y-m-d'),
            'reference_number' => $this->sanitize($this->params['reference_number'] ?? '')
        ];
        
        // Validate transaction type
        if (!in_array($data['transaction_type'], ['credit', 'debit'])) {
            $this->sendError('Invalid transaction type');
        }
        
        // Validate amount
        if ($data['amount'] <= 0) {
            $this->sendError('Amount must be greater than 0');
        }
        
        // Validate date
        if (!$this->validateDate($data['transaction_date'])) {
            $this->sendError('Invalid transaction date format');
        }
        
        // Check if account exists and is active
        $stmt = $this->db->prepare("SELECT id, balance, status FROM accounts WHERE id = :account_id");
        $stmt->execute(['account_id' => $data['account_id']]);
        $account = $stmt->fetch();
        
        if (!$account) {
            $this->sendError('Account not found', 404);
        }
        
        if ($account['status'] !== 'active') {
            $this->sendError('Cannot create transaction for inactive account');
        }
        
        // For debit transactions, check sufficient balance
        if ($data['transaction_type'] === 'debit' && $account['balance'] < $data['amount']) {
            $this->sendError('Insufficient balance');
        }
        
        // Generate transaction code
        $data['transaction_code'] = $this->generateTransactionCode();
        
        $this->db->beginTransaction();
        try {
            // Insert transaction
            $stmt = $this->db->prepare("
                INSERT INTO transactions (transaction_code, account_id, transaction_type, amount, description, reference_number, transaction_date, created_by)
                VALUES (:transaction_code, :account_id, :transaction_type, :amount, :description, :reference_number, :transaction_date, :created_by)
            ");
            
            $stmt->execute([
                'transaction_code' => $data['transaction_code'],
                'account_id' => $data['account_id'],
                'transaction_type' => $data['transaction_type'],
                'amount' => $data['amount'],
                'description' => $data['description'],
                'reference_number' => $data['reference_number'],
                'transaction_date' => $data['transaction_date'],
                'created_by' => $this->user['id']
            ]);
            
            $transactionId = $this->db->lastInsertId();
            
            // Update account balance
            $newBalance = $data['transaction_type'] === 'credit' 
                ? $account['balance'] + $data['amount']
                : $account['balance'] - $data['amount'];
            
            $stmt = $this->db->prepare("UPDATE accounts SET balance = :balance WHERE id = :account_id");
            $stmt->execute([
                'balance' => $newBalance,
                'account_id' => $data['account_id']
            ]);
            
            $this->db->commit();
            
            $this->logActivity('CREATE_TRANSACTION', [
                'transaction_id' => $transactionId,
                'transaction_code' => $data['transaction_code'],
                'account_id' => $data['account_id'],
                'amount' => $data['amount'],
                'type' => $data['transaction_type']
            ]);
            
            $data['id'] = $transactionId;
            $data['new_balance'] = $newBalance;
            
            $this->sendSuccess('Transaction created successfully', $data, 201);
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->sendError('Failed to create transaction: ' . $e->getMessage());
        }
    }
    
    /**
     * PUT /api/transactions.php - Update transaction (limited fields)
     */
    private function handlePut() {
        $this->requireAuth();
        $this->requireRole('admin');
        
        if (!isset($this->params['id'])) {
            $this->sendError('Transaction ID required');
        }
        
        $id = $this->params['id'];
        
        // Only allow updating description and reference number
        $data = [];
        if (isset($this->params['description'])) {
            $data['description'] = $this->sanitize($this->params['description']);
        }
        if (isset($this->params['reference_number'])) {
            $data['reference_number'] = $this->sanitize($this->params['reference_number']);
        }
        
        if (empty($data)) {
            $this->sendError('No data to update');
        }
        
        // Check if transaction exists
        $stmt = $this->db->prepare("SELECT id FROM transactions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) {
            $this->sendError('Transaction not found', 404);
        }
        
        // Update transaction
        $setClause = [];
        foreach ($data as $key => $value) {
            $setClause[] = "$key = :$key";
        }
        
        $sql = "UPDATE transactions SET " . implode(', ', $setClause) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $data['id'] = $id;
        $stmt->execute($data);
        
        $this->logActivity('UPDATE_TRANSACTION', ['transaction_id' => $id]);
        
        $this->sendSuccess('Transaction updated successfully', $data);
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
$api = new TransactionsAPI();
$api->handleRequest();
?>
