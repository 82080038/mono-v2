<?php
/**
 * KSP Lam Gabe Jaya - Loans API
 * Handle loan management operations
 */

require_once __DIR__ . '/BaseAPI.php';

class LoansAPI extends BaseAPI {
    
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
     * GET /api/loans.php - Get loans list or single loan
     */
    private function handleGet() {
        $this->requireAuth();
        
        if (isset($this->params['id'])) {
            $this->getLoan($this->params['id']);
        } else {
            $this->getLoans();
        }
    }
    
    /**
     * Get loans list with pagination and filtering
     */
    private function getLoans() {
        $pagination = $this->getPaginationParams();
        $search = $this->sanitize($this->params['search'] ?? '');
        $status = $this->sanitize($this->params['status'] ?? '');
        $member_id = $this->params['member_id'] ?? null;
        
        // Build query
        $sql = "SELECT l.*, m.full_name as member_name, m.member_number,
                       u.username as approved_by_name,
                       (SELECT SUM(amount) FROM loan_payments WHERE loan_id = l.id) as total_paid,
                       (l.loan_amount - COALESCE((SELECT SUM(amount) FROM loan_payments WHERE loan_id = l.id), 0)) as remaining_balance
                FROM loans l 
                LEFT JOIN members m ON l.member_id = m.id 
                LEFT JOIN users u ON l.approved_by = u.id 
                WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (l.loan_number LIKE :search OR l.purpose LIKE :search OR m.full_name LIKE :search)";
            $params['search'] = "%$search%";
        }
        
        if ($status) {
            $sql .= " AND l.status = :status";
            $params['status'] = $status;
        }
        
        if ($member_id) {
            $sql .= " AND l.member_id = :member_id";
            $params['member_id'] = $member_id;
        }
        
        // Get total count
        $countSql = str_replace("l.*, m.full_name as member_name, m.member_number, u.username as approved_by_name, (SELECT SUM(amount) FROM loan_payments WHERE loan_id = l.id) as total_paid, (l.loan_amount - COALESCE((SELECT SUM(amount) FROM loan_payments WHERE loan_id = l.id), 0)) as remaining_balance", "COUNT(*)", $sql);
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get paginated results
        $sql .= " ORDER BY l.application_date DESC, l.created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':limit', $pagination['limit'], PDO::PARAM_INT);
        $stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
        $stmt->execute();
        $loans = $stmt->fetchAll();
        
        // Format data
        foreach ($loans as &$loan) {
            $loan['loan_amount'] = floatval($loan['loan_amount']);
            $loan['interest_rate'] = floatval($loan['interest_rate']);
            $loan['total_paid'] = floatval($loan['total_paid']);
            $loan['remaining_balance'] = floatval($loan['remaining_balance']);
            $loan['application_date'] = date('Y-m-d', strtotime($loan['application_date']));
            $loan['approval_date'] = $loan['approval_date'] ? date('Y-m-d', strtotime($loan['approval_date'])) : null;
            $loan['disbursement_date'] = $loan['disbursement_date'] ? date('Y-m-d', strtotime($loan['disbursement_date'])) : null;
            $loan['due_date'] = $loan['due_date'] ? date('Y-m-d', strtotime($loan['due_date'])) : null;
        }
        
        $response = $this->buildPaginationResponse($loans, $total, $pagination);
        $this->sendSuccess('Loans retrieved successfully', $response);
    }
    
    /**
     * Get single loan with payment history
     */
    private function getLoan($id) {
        $stmt = $this->db->prepare("
            SELECT l.*, m.full_name as member_name, m.member_number,
                   u.username as approved_by_name
            FROM loans l 
            LEFT JOIN members m ON l.member_id = m.id 
            LEFT JOIN users u ON l.approved_by = u.id 
            WHERE l.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $loan = $stmt->fetch();
        
        if (!$loan) {
            $this->sendError('Loan not found', 404);
        }
        
        // Get payment history
        $stmt = $this->db->prepare("
            SELECT * FROM loan_payments 
            WHERE loan_id = :id 
            ORDER BY payment_date DESC, created_at DESC
        ");
        $stmt->execute(['id' => $id]);
        $payments = $stmt->fetchAll();
        
        // Calculate totals
        $totalPaid = 0;
        foreach ($payments as $payment) {
            $totalPaid += floatval($payment['amount']);
            $payment['amount'] = floatval($payment['amount']);
            $payment['principal_amount'] = floatval($payment['principal_amount']);
            $payment['interest_amount'] = floatval($payment['interest_amount']);
            $payment['payment_date'] = date('Y-m-d', strtotime($payment['payment_date']));
        }
        
        // Format loan data
        $loan['loan_amount'] = floatval($loan['loan_amount']);
        $loan['interest_rate'] = floatval($loan['interest_rate']);
        $loan['total_paid'] = $totalPaid;
        $loan['remaining_balance'] = $loan['loan_amount'] - $totalPaid;
        $loan['application_date'] = date('Y-m-d', strtotime($loan['application_date']));
        $loan['approval_date'] = $loan['approval_date'] ? date('Y-m-d', strtotime($loan['approval_date'])) : null;
        $loan['disbursement_date'] = $loan['disbursement_date'] ? date('Y-m-d', strtotime($loan['disbursement_date'])) : null;
        $loan['due_date'] = $loan['due_date'] ? date('Y-m-d', strtotime($loan['due_date'])) : null;
        
        $loan['payments'] = $payments;
        
        $this->sendSuccess('Loan retrieved successfully', $loan);
    }
    
    /**
     * POST /api/loans.php - Create new loan application
     */
    private function handlePost() {
        $this->requireAuth();
        
        $this->validateRequired(['member_id', 'loan_amount', 'interest_rate', 'loan_term', 'purpose']);
        
        $data = [
            'member_id' => intval($this->params['member_id']),
            'loan_amount' => floatval($this->params['loan_amount']),
            'interest_rate' => floatval($this->params['interest_rate']),
            'loan_term' => intval($this->params['loan_term']),
            'purpose' => $this->sanitize($this->params['purpose']),
            'collateral' => $this->sanitize($this->params['collateral'] ?? ''),
            'application_date' => $this->params['application_date'] ?? date('Y-m-d')
        ];
        
        // Validate member exists and is active
        $stmt = $this->db->prepare("SELECT id, status FROM members WHERE id = :member_id");
        $stmt->execute(['member_id' => $data['member_id']]);
        $member = $stmt->fetch();
        
        if (!$member) {
            $this->sendError('Member not found', 404);
        }
        
        if ($member['status'] !== 'active') {
            $this->sendError('Cannot create loan for inactive member');
        }
        
        // Validate loan amount
        if ($data['loan_amount'] <= 0) {
            $this->sendError('Loan amount must be greater than 0');
        }
        
        // Validate interest rate
        if ($data['interest_rate'] < 0 || $data['interest_rate'] > 100) {
            $this->sendError('Interest rate must be between 0 and 100');
        }
        
        // Validate loan term
        if ($data['loan_term'] <= 0 || $data['loan_term'] > 60) {
            $this->sendError('Loan term must be between 1 and 60 months');
        }
        
        // Validate date
        if (!$this->validateDate($data['application_date'])) {
            $this->sendError('Invalid application date format');
        }
        
        // Check for existing active loans
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM loans 
            WHERE member_id = :member_id AND status IN ('pending', 'active')
        ");
        $stmt->execute(['member_id' => $data['member_id']]);
        $activeLoans = $stmt->fetchColumn();
        
        if ($activeLoans > 0) {
            $this->sendError('Member already has an active loan');
        }
        
        // Generate loan number
        $data['loan_number'] = $this->generateLoanNumber();
        
        // Insert loan application
        $stmt = $this->db->prepare("
            INSERT INTO loans (member_id, loan_number, loan_amount, interest_rate, loan_term, purpose, collateral, status, application_date)
            VALUES (:member_id, :loan_number, :loan_amount, :interest_rate, :loan_term, :purpose, :collateral, 'pending', :application_date)
        ");
        
        $stmt->execute([
            'member_id' => $data['member_id'],
            'loan_number' => $data['loan_number'],
            'loan_amount' => $data['loan_amount'],
            'interest_rate' => $data['interest_rate'],
            'loan_term' => $data['loan_term'],
            'purpose' => $data['purpose'],
            'collateral' => $data['collateral'],
            'application_date' => $data['application_date']
        ]);
        
        $loanId = $this->db->lastInsertId();
        
        $this->logActivity('CREATE_LOAN', [
            'loan_id' => $loanId,
            'loan_number' => $data['loan_number'],
            'member_id' => $data['member_id'],
            'amount' => $data['loan_amount']
        ]);
        
        $data['id'] = $loanId;
        $this->sendSuccess('Loan application created successfully', $data, 201);
    }
    
    /**
     * PUT /api/loans.php - Update loan (approve/reject/approve terms)
     */
    private function handlePut() {
        $this->requireAuth();
        
        if (!isset($this->params['id'])) {
            $this->sendError('Loan ID required');
        }
        
        $id = $this->params['id'];
        
        // Check if loan exists
        $stmt = $this->db->prepare("SELECT id, status, loan_amount FROM loans WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $loan = $stmt->fetch();
        
        if (!$loan) {
            $this->sendError('Loan not found', 404);
        }
        
        $action = $this->sanitize($this->params['action'] ?? '');
        
        switch ($action) {
            case 'approve':
                $this->approveLoan($id);
                break;
            case 'reject':
                $this->rejectLoan($id);
                break;
            case 'disburse':
                $this->disburseLoan($id);
                break;
            default:
                $this->sendError('Invalid action. Use: approve, reject, or disburse');
        }
    }
    
    /**
     * Approve loan
     */
    private function approveLoan($id) {
        $this->requireRole('manager');
        
        $stmt = $this->db->prepare("
            UPDATE loans 
            SET status = 'approved', approval_date = CURDATE(), approved_by = :user_id,
                due_date = DATE_ADD(CURDATE(), INTERVAL loan_term MONTH)
            WHERE id = :id AND status = 'pending'
        ");
        
        $stmt->execute([
            'id' => $id,
            'user_id' => $this->user['id']
        ]);
        
        if ($stmt->rowCount() === 0) {
            $this->sendError('Loan not found or not in pending status');
        }
        
        $this->logActivity('APPROVE_LOAN', ['loan_id' => $id]);
        
        $this->sendSuccess('Loan approved successfully');
    }
    
    /**
     * Reject loan
     */
    private function rejectLoan($id) {
        $this->requireRole('manager');
        
        $stmt = $this->db->prepare("
            UPDATE loans SET status = 'rejected' 
            WHERE id = :id AND status = 'pending'
        ");
        
        $stmt->execute(['id' => $id]);
        
        if ($stmt->rowCount() === 0) {
            $this->sendError('Loan not found or not in pending status');
        }
        
        $this->logActivity('REJECT_LOAN', ['loan_id' => $id]);
        
        $this->sendSuccess('Loan rejected successfully');
    }
    
    /**
     * Disburse loan
     */
    private function disburseLoan($id) {
        $this->requireRole('staff');
        
        // Get loan details
        $stmt = $this->db->prepare("
            SELECT id, member_id, loan_amount, status 
            FROM loans WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
        $loan = $stmt->fetch();
        
        if (!$loan || $loan['status'] !== 'approved') {
            $this->sendError('Loan not found or not approved');
        }
        
        $this->db->beginTransaction();
        try {
            // Update loan status
            $stmt = $this->db->prepare("
                UPDATE loans SET status = 'active', disbursement_date = CURDATE() 
                WHERE id = :id
            ");
            $stmt->execute(['id' => $id]);
            
            // Create loan account and disburse
            $accountNumber = $this->generateAccountNumber();
            $stmt = $this->db->prepare("
                INSERT INTO accounts (member_id, account_number, account_type, account_name, balance, status, opened_date)
                VALUES (:member_id, :account_number, 'pinjaman', :account_name, :balance, 'active', CURDATE())
            ");
            
            $stmt->execute([
                'member_id' => $loan['member_id'],
                'account_number' => $accountNumber,
                'account_name' => 'Pinjaman - ' . $accountNumber,
                'balance' => $loan['loan_amount']
            ]);
            
            $this->db->commit();
            
            $this->logActivity('DISBURSE_LOAN', [
                'loan_id' => $id,
                'member_id' => $loan['member_id'],
                'amount' => $loan['loan_amount']
            ]);
            
            $this->sendSuccess('Loan disbursed successfully');
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->sendError('Failed to disburse loan: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate loan number
     */
    private function generateLoanNumber() {
        $prefix = 'L';
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM loans");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        return $prefix . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
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
}

// Handle request
$api = new LoansAPI();
$api->handleRequest();
?>
