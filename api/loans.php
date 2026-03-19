<?php
/**
 * Phase 1 API - Loan Management
 * KSP Lam Gabe Jaya v2.0
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/Config.php';

try {
    $db = Config::getDatabase();
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_loan_types':
            getLoanTypes($db);
            break;
            
        case 'get_loans':
            getLoans($db);
            break;
            
        case 'get_loan':
            getLoan($db);
            break;
            
        case 'apply_loan':
            applyLoan($db);
            break;
            
        case 'approve_loan':
            approveLoan($db);
            break;
            
        case 'disburse_loan':
            disburseLoan($db);
            break;
            
        case 'get_loan_installments':
            getLoanInstallments($db);
            break;
            
        case 'make_payment':
            makePayment($db);
            break;
            
        case 'get_loan_portfolio':
            getLoanPortfolio($db);
            break;
            
        case 'calculate_credit_score':
            calculateCreditScore($db);
            break;
            
        case 'get_collateral':
            getCollateral($db);
            break;
            
        case 'add_collateral':
            addCollateral($db);
            break;
            
        default:
            sendResponse(false, 'Invalid action', null, 400);
    }
    
} catch (Exception $e) {
    sendResponse(false, 'Server error: ' . $e->getMessage(), null, 500);
}

/**
 * Get loan types
 */
function getLoanTypes($db) {
    $stmt = $db->prepare("SELECT * FROM loan_types WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $loanTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse(true, 'Loan types retrieved', $loanTypes);
}

/**
 * Get loans list
 */
function getLoans($db) {
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $loanType = $_GET['loan_type_id'] ?? '';
    $memberId = $_GET['member_id'] ?? '';
    
    $where = [];
    $params = [];
    
    if (!empty($search)) {
        $where[] = "(l.loan_number LIKE ? OR m.full_name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($status)) {
        $where[] = "l.status = ?";
        $params[] = $status;
    }
    
    if (!empty($loanType)) {
        $where[] = "l.loan_type_id = ?";
        $params[] = $loanType;
    }
    
    if (!empty($memberId)) {
        $where[] = "l.member_id = ?";
        $params[] = $memberId;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM loans l LEFT JOIN members m ON l.member_id = m.id $whereClause";
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get loans
    $sql = "
        SELECT l.*, lt.name as loan_type_name, m.full_name, m.member_number,
               DATEDIFF(CURRENT_DATE, l.next_payment_date) as days_overdue,
               CASE 
                   WHEN l.status = 'Active' AND DATEDIFF(CURRENT_DATE, l.next_payment_date) > 0 THEN 'Late'
                   WHEN l.status = 'Active' AND DATEDIFF(CURRENT_DATE, l.next_payment_date) <= 0 THEN 'Current'
                   ELSE l.status
               END as payment_status
        FROM loans l
        LEFT JOIN loan_types lt ON l.loan_type_id = lt.id
        LEFT JOIN members m ON l.member_id = m.id
        $whereClause
        ORDER BY l.application_date DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $db->prepare($sql);
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse(true, 'Loans retrieved', [
        'data' => $loans,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

/**
 * Get loan details
 */
function getLoan($db) {
    $loanId = intval($_GET['id'] ?? 0);
    
    if ($loanId <= 0) {
        sendResponse(false, 'Invalid loan ID', null, 400);
        return;
    }
    
    $stmt = $db->prepare("
        SELECT l.*, lt.name as loan_type_name, m.full_name, m.member_number
        FROM loans l
        LEFT JOIN loan_types lt ON l.loan_type_id = lt.id
        LEFT JOIN members m ON l.member_id = m.id
        WHERE l.id = ?
    ");
    $stmt->execute([$loanId]);
    $loan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$loan) {
        sendResponse(false, 'Loan not found', null, 404);
        return;
    }
    
    // Get installments
    $stmt = $db->prepare("
        SELECT * FROM loan_installments 
        WHERE loan_id = ?
        ORDER BY installment_number ASC
    ");
    $stmt->execute([$loanId]);
    $installments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get payments
    $stmt = $db->prepare("
        SELECT * FROM loan_payments 
        WHERE loan_id = ?
        ORDER BY payment_date DESC
    ");
    $stmt->execute([$loanId]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get collateral
    $stmt = $db->prepare("
        SELECT * FROM collateral 
        WHERE loan_id = ? AND status = 'Active'
    ");
    $stmt->execute([$loanId]);
    $collateral = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $loan['installments'] = $installments;
    $loan['payments'] = $payments;
    $loan['collateral'] = $collateral;
    
    sendResponse(true, 'Loan details retrieved', $loan);
}

/**
 * Apply for loan
 */
function applyLoan($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['member_id', 'loan_type_id', 'amount', 'term_months', 'purpose'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendResponse(false, "Field '$field' is required", null, 400);
            return;
        }
    }
    
    try {
        $db->beginTransaction();
        
        // Check member exists and is active
        $stmt = $db->prepare("SELECT * FROM members WHERE id = ? AND status = 'Active'");
        $stmt->execute([$data['member_id']]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$member) {
            sendResponse(false, 'Member not found or inactive', null, 400);
            return;
        }
        
        // Get loan type
        $stmt = $db->prepare("SELECT * FROM loan_types WHERE id = ? AND is_active = 1");
        $stmt->execute([$data['loan_type_id']]);
        $loanType = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$loanType) {
            sendResponse(false, 'Loan type not found or inactive', null, 400);
            return;
        }
        
        // Validate loan amount
        $amount = floatval($data['amount']);
        $termMonths = intval($data['term_months']);
        
        if ($amount < $loanType['minimum_amount'] || $amount > $loanType['maximum_amount']) {
            sendResponse(false, 'Loan amount outside allowed range', null, 400);
            return;
        }
        
        if ($termMonths < $loanType['minimum_term_months'] || $termMonths > $loanType['maximum_term_months']) {
            sendResponse(false, 'Loan term outside allowed range', null, 400);
            return;
        }
        
        // Check existing loans
        $stmt = $db->prepare("
            SELECT COUNT(*) as active_loans 
            FROM loans 
            WHERE member_id = ? AND status IN ('Active', 'Late', 'Default')
        ");
        $stmt->execute([$data['member_id']]);
        $activeLoans = $stmt->fetch(PDO::FETCH_ASSOC)['active_loans'];
        
        $stmt = $db->prepare("SELECT max_concurrent_loans FROM member_types WHERE id = ?");
        $stmt->execute([$member['member_type_id']]);
        $maxConcurrentLoans = $stmt->fetch(PDO::FETCH_ASSOC)['max_concurrent_loans'];
        
        if ($activeLoans >= $maxConcurrentLoans) {
            sendResponse(false, 'Maximum concurrent loans reached', null, 400);
            return;
        }
        
        // Calculate loan details
        $interestRate = $loanType['interest_rate'];
        $adminFeeRate = $loanType['admin_fee_rate'];
        $calculationMethod = $loanType['calculation_method'];
        
        $loanCalculations = calculateLoan($amount, $interestRate, $adminFeeRate, $termMonths, $calculationMethod);
        
        // Generate loan number
        $loanNumber = generateLoanNumber($db);
        
        // Insert loan application
        $stmt = $db->prepare("
            INSERT INTO loans (
                loan_number, member_id, loan_type_id, application_date, amount,
                interest_rate, admin_fee, term_months, calculation_method,
                monthly_installment, total_interest, total_payment,
                outstanding_balance, purpose, status, created_by
            ) VALUES (?, ?, ?, CURRENT_DATE, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Applied', ?)
        ");
        
        $stmt->execute([
            $loanNumber,
            $data['member_id'],
            $data['loan_type_id'],
            $amount,
            $interestRate,
            $loanCalculations['admin_fee'],
            $termMonths,
            $calculationMethod,
            $loanCalculations['monthly_installment'],
            $loanCalculations['total_interest'],
            $loanCalculations['total_payment'],
            $loanCalculations['total_payment'], // outstanding balance initially equals total payment
            $data['purpose'],
            1 // created_by
        ]);
        
        $loanId = $db->lastInsertId();
        
        // Calculate credit score
        $creditScore = calculateMemberCreditScore($db, $data['member_id'], $loanId);
        
        // Create installments schedule
        createInstallmentSchedule($db, $loanId, $loanCalculations);
        
        $db->commit();
        
        sendResponse(true, 'Loan application submitted successfully', [
            'loan_id' => $loanId,
            'loan_number' => $loanNumber,
            'credit_score' => $creditScore,
            'loan_details' => $loanCalculations
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        sendResponse(false, 'Loan application failed: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Approve loan
 */
function approveLoan($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $loanId = intval($data['loan_id'] ?? 0);
    $approved = $data['approved'] ?? false;
    $reason = $data['reason'] ?? '';
    
    if ($loanId <= 0) {
        sendResponse(false, 'Invalid loan ID', null, 400);
        return;
    }
    
    try {
        $db->beginTransaction();
        
        // Get loan details
        $stmt = $db->prepare("SELECT * FROM loans WHERE id = ? AND status = 'Applied'");
        $stmt->execute([$loanId]);
        $loan = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$loan) {
            sendResponse(false, 'Loan not found or not in Applied status', null, 404);
            return;
        }
        
        if ($approved) {
            // Approve loan
            $stmt = $db->prepare("
                UPDATE loans 
                SET status = 'Approved', approval_date = CURRENT_DATE, 
                    approved_by = ?, notes = ?
                WHERE id = ?
            ");
            $stmt->execute([1, $reason, $loanId]);
            
            sendResponse(true, 'Loan approved successfully');
        } else {
            // Reject loan
            $stmt = $db->prepare("
                UPDATE loans 
                SET status = 'Rejected', rejection_reason = ?, approved_by = ?
                WHERE id = ?
            ");
            $stmt->execute([$reason, 1, $loanId]);
            
            sendResponse(true, 'Loan rejected');
        }
        
        $db->commit();
        
    } catch (Exception $e) {
        $db->rollBack();
        sendResponse(false, 'Loan approval failed: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Disburse loan
 */
function disburseLoan($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $loanId = intval($data['loan_id'] ?? 0);
    $disbursementMethod = $data['disbursement_method'] ?? 'Bank Transfer';
    
    if ($loanId <= 0) {
        sendResponse(false, 'Invalid loan ID', null, 400);
        return;
    }
    
    try {
        $db->beginTransaction();
        
        // Get loan details
        $stmt = $db->prepare("SELECT * FROM loans WHERE id = ? AND status = 'Approved'");
        $stmt->execute([$loanId]);
        $loan = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$loan) {
            sendResponse(false, 'Loan not found or not approved', null, 404);
            return;
        }
        
        // Update loan status
        $maturityDate = date('Y-m-d', strtotime("+$loan[term_months] months"));
        $nextPaymentDate = date('Y-m-d', strtotime('+1 month'));
        
        $stmt = $db->prepare("
            UPDATE loans 
            SET status = 'Disbursed', disbursement_date = CURRENT_DATE, 
                next_payment_date = ?, maturity_date = ?
            WHERE id = ?
        ");
        $stmt->execute([$nextPaymentDate, $maturityDate, $loanId]);
        
        // Create loan payment record for disbursement
        $stmt = $db->prepare("
            INSERT INTO loan_payments (
                loan_id, payment_amount, principal_portion, interest_portion,
                payment_date, payment_method, reference_number, teller_id
            ) VALUES (?, 0, 0, 0, CURRENT_TIMESTAMP, ?, ?, ?)
        ");
        
        $referenceNumber = 'DSP' . date('YmdHis') . str_pad($loanId, 4, '0', STR_PAD_LEFT);
        
        $stmt->execute([
            $loanId,
            $disbursementMethod,
            $referenceNumber,
            1 // teller_id
        ]);
        
        $db->commit();
        
        sendResponse(true, 'Loan disbursed successfully', [
            'disbursement_date' => date('Y-m-d'),
            'reference_number' => $referenceNumber
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        sendResponse(false, 'Loan disbursement failed: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Get loan installments
 */
function getLoanInstallments($db) {
    $loanId = intval($_GET['loan_id'] ?? 0);
    
    if ($loanId <= 0) {
        sendResponse(false, 'Invalid loan ID', null, 400);
        return;
    }
    
    $stmt = $db->prepare("
        SELECT * FROM loan_installments 
        WHERE loan_id = ?
        ORDER BY installment_number ASC
    ");
    $stmt->execute([$loanId]);
    $installments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse(true, 'Loan installments retrieved', $installments);
}

/**
 * Make loan payment
 */
function makePayment($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['loan_id', 'payment_amount'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendResponse(false, "Field '$field' is required", null, 400);
            return;
        }
    }
    
    $loanId = intval($data['loan_id']);
    $paymentAmount = floatval($data['payment_amount']);
    
    if ($paymentAmount <= 0) {
        sendResponse(false, 'Payment amount must be greater than 0', null, 400);
        return;
    }
    
    try {
        $db->beginTransaction();
        
        // Get loan details
        $stmt = $db->prepare("SELECT * FROM loans WHERE id = ? AND status IN ('Active', 'Late')");
        $stmt->execute([$loanId]);
        $loan = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$loan) {
            sendResponse(false, 'Loan not found or not active', null, 404);
            return;
        }
        
        // Get outstanding installments
        $stmt = $db->prepare("
            SELECT * FROM loan_installments 
            WHERE loan_id = ? AND status = 'Pending'
            ORDER BY due_date ASC
        ");
        $stmt->execute([$loanId]);
        $installments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($installments)) {
            sendResponse(false, 'No outstanding installments found', null, 400);
            return;
        }
        
        // Process payment
        $remainingAmount = $paymentAmount;
        $totalPrincipal = 0;
        $totalInterest = 0;
        $totalLateFee = 0;
        
        foreach ($installments as $installment) {
            if ($remainingAmount <= 0) break;
            
            $installmentAmount = $installment['total_amount'];
            $lateFee = $installment['late_fee'];
            $totalDue = $installmentAmount + $lateFee;
            
            if ($remainingAmount >= $totalDue) {
                // Full payment
                $principalPaid = $installment['principal_amount'];
                $interestPaid = $installment['interest_amount'];
                $lateFeePaid = $lateFee;
                
                // Update installment
                $stmt = $db->prepare("
                    UPDATE loan_installments 
                    SET paid_amount = total_amount, paid_date = CURRENT_DATE, 
                        status = 'Paid', payment_method = ?, receipt_number = ?
                    WHERE id = ?
                ");
                
                $receiptNumber = 'PAY' . date('YmdHis') . str_pad($installment['id'], 4, '0', STR_PAD_LEFT);
                
                $stmt->execute([
                    $data['payment_method'] ?? 'Cash',
                    $receiptNumber,
                    $installment['id']
                ]);
                
                $remainingAmount -= $totalDue;
                $totalPrincipal += $principalPaid;
                $totalInterest += $interestPaid;
                $totalLateFee += $lateFeePaid;
                
            } else {
                // Partial payment
                $principalRatio = $installment['principal_amount'] / $installmentAmount;
                $interestRatio = $installment['interest_amount'] / $installmentAmount;
                
                $principalPaid = $remainingAmount * $principalRatio;
                $interestPaid = $remainingAmount * $interestRatio;
                $lateFeePaid = min($remainingAmount - ($principalPaid + $interestPaid), $lateFee);
                
                // Update installment with partial payment
                $stmt = $db->prepare("
                    UPDATE loan_installments 
                    SET paid_amount = paid_amount + ?, status = 'Late'
                    WHERE id = ?
                ");
                $stmt->execute([$remainingAmount, $installment['id']]);
                
                $totalPrincipal += $principalPaid;
                $totalInterest += $interestPaid;
                $totalLateFee += $lateFeePaid;
                
                $remainingAmount = 0;
                break;
            }
        }
        
        // Create payment record
        $stmt = $db->prepare("
            INSERT INTO loan_payments (
                loan_id, payment_amount, principal_portion, interest_portion,
                late_fee_portion, payment_method, reference_number, teller_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $referenceNumber = 'PAY' . date('YmdHis') . str_pad($loanId, 4, '0', STR_PAD_LEFT);
        
        $stmt->execute([
            $loanId,
            $paymentAmount,
            $totalPrincipal,
            $totalInterest,
            $totalLateFee,
            $data['payment_method'] ?? 'Cash',
            $referenceNumber,
            1 // teller_id
        ]);
        
        // Update loan outstanding balance
        $newOutstanding = $loan['outstanding_balance'] - $totalPrincipal;
        
        $stmt = $db->prepare("
            UPDATE loans 
            SET outstanding_balance = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$newOutstanding, $loanId]);
        
        // Check if loan is fully paid
        if ($newOutstanding <= 0) {
            $stmt = $db->prepare("
                UPDATE loans 
                SET status = 'Paid Off', updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$loanId]);
        }
        
        $db->commit();
        
        sendResponse(true, 'Payment processed successfully', [
            'payment_id' => $db->lastInsertId(),
            'reference_number' => $referenceNumber,
            'principal_paid' => $totalPrincipal,
            'interest_paid' => $totalInterest,
            'late_fee_paid' => $totalLateFee,
            'new_outstanding_balance' => $newOutstanding
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        sendResponse(false, 'Payment processing failed: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Get loan portfolio
 */
function getLoanPortfolio($db) {
    $stmt = $db->prepare("
        SELECT 
            lt.name as loan_type,
            COUNT(l.id) as total_loans,
            SUM(l.amount) as total_disbursed,
            SUM(l.outstanding_balance) as total_outstanding,
            AVG(l.interest_rate) as avg_interest_rate,
            COUNT(CASE WHEN l.status = 'Late' THEN 1 END) as late_loans,
            COUNT(CASE WHEN l.status = 'Default' THEN 1 END) as default_loans,
            SUM(CASE WHEN l.status = 'Late' OR l.status = 'Default' THEN l.outstanding_balance ELSE 0 END) as npl_amount
        FROM loans l
        LEFT JOIN loan_types lt ON l.loan_type_id = lt.id
        WHERE l.status IN ('Active', 'Late', 'Default')
        GROUP BY lt.id, lt.name
        ORDER BY total_disbursed DESC
    ");
    $stmt->execute();
    $portfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $totalLoans = array_sum(array_column($portfolio, 'total_loans'));
    $totalDisbursed = array_sum(array_column($portfolio, 'total_disbursed'));
    $totalOutstanding = array_sum(array_column($portfolio, 'total_outstanding'));
    $totalLate = array_sum(array_column($portfolio, 'late_loans'));
    $totalDefault = array_sum(array_column($portfolio, 'default_loans'));
    $totalNPL = array_sum(array_column($portfolio, 'npl_amount'));
    
    $nplRatio = $totalOutstanding > 0 ? ($totalNPL / $totalOutstanding) * 100 : 0;
    
    sendResponse(true, 'Loan portfolio retrieved', [
        'by_loan_type' => $portfolio,
        'summary' => [
            'total_loans' => $totalLoans,
            'total_disbursed' => $totalDisbursed,
            'total_outstanding' => $totalOutstanding,
            'late_loans' => $totalLate,
            'default_loans' => $totalDefault,
            'npl_amount' => $totalNPL,
            'npl_ratio' => round($nplRatio, 2)
        ]
    ]);
}

/**
 * Calculate loan details
 */
function calculateLoan($principal, $interestRate, $adminFeeRate, $termMonths, $method) {
    $adminFee = $principal * $adminFeeRate;
    
    switch ($method) {
        case 'Flat':
            $totalInterest = $principal * $interestRate * $termMonths;
            $totalPayment = $principal + $totalInterest + $adminFee;
            $monthlyInstallment = $totalPayment / $termMonths;
            break;
            
        case 'Effective':
            $totalPayment = $principal;
            $remainingBalance = $principal;
            $totalInterest = 0;
            
            for ($i = 1; $i <= $termMonths; $i++) {
                $monthlyInterest = $remainingBalance * $interestRate;
                $principalPayment = $principal / $termMonths;
                $totalInterest += $monthlyInterest;
                $remainingBalance -= $principalPayment;
            }
            
            $totalPayment = $principal + $totalInterest + $adminFee;
            $monthlyInstallment = $totalPayment / $termMonths;
            break;
            
        case 'Anuitas':
            // Annuity formula
            $r = $interestRate;
            $n = $termMonths;
            $monthlyInstallment = $principal * ($r * pow(1 + $r, $n)) / (pow(1 + $r, $n) - 1);
            $totalPayment = ($monthlyInstallment * $termMonths) + $adminFee;
            $totalInterest = ($monthlyInstallment * $termMonths) - $principal;
            break;
            
        default:
            throw new Exception('Invalid calculation method');
    }
    
    return [
        'admin_fee' => $adminFee,
        'total_interest' => $totalInterest,
        'total_payment' => $totalPayment,
        'monthly_installment' => $monthlyInstallment
    ];
}

/**
 * Generate loan number
 */
function generateLoanNumber($db) {
    $year = date('Y');
    
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM loans 
        WHERE YEAR(application_date) = ?
    ");
    $stmt->execute([$year]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    return 'LOAN' . $year . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}

/**
 * Calculate member credit score
 */
function calculateMemberCreditScore($db, $memberId, $loanApplicationId = null) {
    // Get member details
    $stmt = $db->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$memberId]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        return 0;
    }
    
    $scores = [];
    
    // Get scoring criteria
    $stmt = $db->prepare("SELECT * FROM credit_scoring_criteria WHERE is_active = 1");
    $stmt->execute();
    $criteria = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($criteria as $criterion) {
        $score = 0;
        
        switch ($criterion['name']) {
            case 'Membership Duration':
                $registrationDate = new DateTime($member['registration_date']);
                $currentDate = new DateTime();
                $months = $registrationDate->diff($currentDate)->m + ($registrationDate->diff($currentDate)->y * 12);
                $score = min(100, ($months / 12) * 100); // Max 100 points for 12+ months
                break;
                
            case 'Savings History':
                $stmt = $db->prepare("
                    SELECT SUM(balance) as total_savings, COUNT(*) as account_count
                    FROM accounts 
                    WHERE member_id = ? AND status = 'Active'
                ");
                $stmt->execute([$memberId]);
                $savings = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($savings['total_savings'] > 0) {
                    $score = min(100, ($savings['total_savings'] / 1000000) * 100); // Max 100 points for 1M+ savings
                }
                break;
                
            case 'Previous Loans':
                $stmt = $db->prepare("
                    SELECT COUNT(*) as total_loans, 
                           COUNT(CASE WHEN status = 'Paid Off' THEN 1 END) as paid_loans,
                           COUNT(CASE WHEN status IN ('Default', 'Late') THEN 1 END) as problem_loans
                    FROM loans 
                    WHERE member_id = ?
                ");
                $stmt->execute([$memberId]);
                $loanHistory = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($loanHistory['total_loans'] > 0) {
                    $paidRatio = $loanHistory['paid_loans'] / $loanHistory['total_loans'];
                    $score = $paidRatio * 100;
                    
                    // Penalty for problem loans
                    if ($loanHistory['problem_loans'] > 0) {
                        $score -= ($loanHistory['problem_loans'] * 20);
                    }
                }
                break;
                
            case 'Income Stability':
                if ($member['monthly_income'] > 0) {
                    $score = min(100, ($member['monthly_income'] / 5000000) * 100); // Max 100 points for 5M+ income
                }
                break;
                
            case 'Collateral Value':
                // This would be calculated based on collateral for this loan application
                $score = 50; // Default score
                break;
        }
        
        $scores[] = [
            'criteria_id' => $criterion['id'],
            'score' => max(0, min(100, $score))
        ];
    }
    
    // Calculate weighted average
    $totalScore = 0;
    $totalWeight = 0;
    
    foreach ($criteria as $criterion) {
        $weight = $criterion['weight'];
        $score = 0;
        
        foreach ($scores as $result) {
            if ($result['criteria_id'] == $criterion['id']) {
                $score = $result['score'];
                break;
            }
        }
        
        $totalScore += ($score * $weight);
        $totalWeight += $weight;
    }
    
    $finalScore = $totalWeight > 0 ? ($totalScore / $totalWeight) : 0;
    
    // Determine risk level
    $riskLevel = 'Very High';
    if ($finalScore >= 80) $riskLevel = 'Low';
    elseif ($finalScore >= 60) $riskLevel = 'Medium';
    elseif ($finalScore >= 40) $riskLevel = 'High';
    
    // Determine recommendation
    $recommendation = 'Reject';
    if ($finalScore >= 70) $recommendation = 'Approve';
    elseif ($finalScore >= 50) $recommendation = 'Manual Review';
    
    // Save credit scoring result
    $stmt = $db->prepare("
        INSERT INTO credit_scoring_results (
            member_id, loan_application_id, total_score, risk_level, 
            recommendation, scored_by
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $memberId,
        $loanApplicationId,
        $finalScore,
        $riskLevel,
        $recommendation,
        1 // scored_by
    ]);
    
    $scoringResultId = $db->lastInsertId();
    
    // Save scoring details
    foreach ($scores as $score) {
        $stmt = $db->prepare("
            INSERT INTO credit_scoring_details (scoring_result_id, criteria_id, score)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$scoringResultId, $score['criteria_id'], $score['score']]);
    }
    
    return $finalScore;
}

/**
 * Create installment schedule
 */
function createInstallmentSchedule($db, $loanId, $loanCalculations) {
    $stmt = $db->prepare("SELECT term_months, application_date FROM loans WHERE id = ?");
    $stmt->execute([$loanId]);
    $loan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $monthlyInstallment = $loanCalculations['monthly_installment'];
    $principalAmount = $loanCalculations['total_payment'] - $loanCalculations['total_interest'];
    $monthlyPrincipal = $principalAmount / $loan['term_months'];
    $monthlyInterest = $loanCalculations['total_interest'] / $loan['term_months'];
    
    for ($i = 1; $i <= $loan['term_months']; $i++) {
        $dueDate = date('Y-m-d', strtotime("+$i months", strtotime($loan['application_date'])));
        
        $stmt = $db->prepare("
            INSERT INTO loan_installments (
                loan_id, installment_number, due_date, principal_amount, 
                interest_amount, total_amount, status
            ) VALUES (?, ?, ?, ?, ?, ?, 'Pending')
        ");
        
        $stmt->execute([
            $loanId,
            $i,
            $dueDate,
            $monthlyPrincipal,
            $monthlyInterest,
            $monthlyInstallment
        ]);
    }
}

/**
 * Send JSON response
 */
function sendResponse($success, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if (!$success) {
        $response['errors'] = [];
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}
