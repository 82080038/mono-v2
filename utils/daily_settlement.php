<?php
/**
 * DAILY SETTLEMENT SYSTEM
 * Handle daily cash reconciliation for field officers
 */

class DailySettlement {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function createSettlement($mantriId, $settlementDate, $cashCollected, $cashDeposited, $notes = '') {
        try {
            $difference = $cashCollected - $cashDeposited;
            
            $stmt = $this->db->prepare("
                INSERT INTO daily_settlements (uuid, settlement_date, mantri_id, cash_collected, cash_deposited, difference, notes, created_by)
                VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $settlementDate,
                $mantriId,
                $cashCollected,
                $cashDeposited,
                $difference,
                $notes,
                $_SESSION['user_id'] ?? 1
            ]);
        } catch (Exception $e) {
            error_log("Create settlement failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function getPendingSettlements() {
        try {
            $stmt = $this->db->prepare("
                SELECT ds.*, u.name as mantri_name, u.email as mantri_email
                FROM daily_settlements ds
                JOIN users u ON ds.mantri_id = u.id
                WHERE ds.status = 'pending'
                ORDER BY ds.settlement_date DESC
            ");
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get pending settlements failed: " . $e->getMessage());
            return [];
        }
    }
    
    public function getMantriSettlements($mantriId, $limit = 30) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM daily_settlements 
                WHERE mantri_id = ? 
                ORDER BY settlement_date DESC 
                LIMIT ?
            ");
            $stmt->execute([$mantriId, $limit]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get mantri settlements failed: " . $e->getMessage());
            return [];
        }
    }
    
    public function approveSettlement($settlementId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE daily_settlements 
                SET status = 'approved', settled_by = ?, settled_at = NOW() 
                WHERE id = ?
            ");
            
            $result = $stmt->execute([$_SESSION['user_id'] ?? 1, $settlementId]);
            
            if ($result) {
                // Send notification to mantri
                $this->notifySettlementApproved($settlementId);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Approve settlement failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function rejectSettlement($settlementId, $reason) {
        try {
            $stmt = $this->db->prepare("
                UPDATE daily_settlements 
                SET status = 'rejected', notes = CONCAT(IFNULL(notes, ''), ' | Rejected: ', ?), settled_by = ?, settled_at = NOW() 
                WHERE id = ?
            ");
            
            $result = $stmt->execute([$reason, $_SESSION['user_id'] ?? 1, $settlementId]);
            
            if ($result) {
                // Send notification to mantri
                $this->notifySettlementRejected($settlementId, $reason);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Reject settlement failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function getSettlementSummary($startDate, $endDate, $mantriId = null) {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_settlements,
                    SUM(cash_collected) as total_collected,
                    SUM(cash_deposited) as total_deposited,
                    SUM(difference) as total_difference,
                    AVG(difference) as avg_difference,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count
                FROM daily_settlements 
                WHERE settlement_date BETWEEN ? AND ?
            ";
            
            $params = [$startDate, $endDate];
            
            if ($mantriId) {
                $sql .= " AND mantri_id = ?";
                $params[] = $mantriId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Get settlement summary failed: " . $e->getMessage());
            return null;
        }
    }
    
    public function autoCalculateExpectedCollection($mantriId, $date) {
        try {
            // Calculate expected collection based on loan schedules
            $stmt = $this->db->prepare("
                SELECT SUM(lp.amount) as expected_amount
                FROM loan_payments lp
                JOIN loans l ON lp.loan_id = l.id
                JOIN loan_applications la ON l.loan_application_id = la.id
                WHERE la.assigned_to = ? 
                AND lp.due_date = ?
                AND lp.status = 'pending'
            ");
            $stmt->execute([$mantriId, $date]);
            
            $result = $stmt->fetch();
            return $result['expected_amount'] ?? 0;
        } catch (Exception $e) {
            error_log("Calculate expected collection failed: " . $e->getMessage());
            return 0;
        }
    }
    
    private function notifySettlementApproved($settlementId) {
        // Get settlement details
        $stmt = $this->db->prepare("
            SELECT ds.*, u.name as mantri_name, u.id as mantri_id
            FROM daily_settlements ds
            JOIN users u ON ds.mantri_id = u.id
            WHERE ds.id = ?
        ");
        $stmt->execute([$settlementId]);
        $settlement = $stmt->fetch();
        
        if ($settlement) {
            // Send notification (requires notification system)
            if (class_exists('NotificationSystem')) {
                $notificationSystem = new NotificationSystem($this->db);
                $title = "Settlement Approved";
                $message = "Daily settlement for " . $settlement['settlement_date'] . " has been approved.";
                $notificationSystem->createNotification($settlement['mantri_id'], $title, $message, 'success');
            }
        }
    }
    
    private function notifySettlementRejected($settlementId, $reason) {
        // Get settlement details
        $stmt = $this->db->prepare("
            SELECT ds.*, u.name as mantri_name, u.id as mantri_id
            FROM daily_settlements ds
            JOIN users u ON ds.mantri_id = u.id
            WHERE ds.id = ?
        ");
        $stmt->execute([$settlementId]);
        $settlement = $stmt->fetch();
        
        if ($settlement) {
            // Send notification (requires notification system)
            if (class_exists('NotificationSystem')) {
                $notificationSystem = new NotificationSystem($this->db);
                $title = "Settlement Rejected";
                $message = "Daily settlement for " . $settlement['settlement_date'] . " was rejected. Reason: " . $reason;
                $notificationSystem->createNotification($settlement['mantri_id'], $title, $message, 'danger');
            }
        }
    }
    
    public function generateSettlementReport($date = null) {
        $date = $date ?: date('Y-m-d');
        
        $report = [
            'date' => $date,
            'total_mantris' => 0,
            'total_collected' => 0,
            'total_deposited' => 0,
            'total_difference' => 0,
            'pending_count' => 0,
            'approved_count' => 0,
            'rejected_count' => 0,
            'mantris' => []
        ];
        
        // Get all mantris
        $stmt = $this->db->prepare("
            SELECT u.id, u.name
            FROM users u
            JOIN user_assignments ua ON u.id = ua.user_id
            JOIN user_roles ur ON ua.role_id = ur.id
            WHERE ur.name = 'mantri' AND u.is_active = 1
        ");
        $stmt->execute();
        $mantris = $stmt->fetchAll();
        
        $report['total_mantris'] = count($mantris);
        
        foreach ($mantris as $mantri) {
            $settlement = $this->getMantriSettlement($mantri['id'], 1);
            
            if ($settlement && $settlement[0]['settlement_date'] === $date) {
                $settlement = $settlement[0];
                $report['total_collected'] += $settlement['cash_collected'];
                $report['total_deposited'] += $settlement['cash_deposited'];
                $report['total_difference'] += $settlement['difference'];
                
                switch ($settlement['status']) {
                    case 'pending':
                        $report['pending_count']++;
                        break;
                    case 'approved':
                        $report['approved_count']++;
                        break;
                    case 'rejected':
                        $report['rejected_count']++;
                        break;
                }
                
                $report['mantris'][] = [
                    'name' => $mantri['name'],
                    'cash_collected' => $settlement['cash_collected'],
                    'cash_deposited' => $settlement['cash_deposited'],
                    'difference' => $settlement['difference'],
                    'status' => $settlement['status']
                ];
            }
        }
        
        return $report;
    }
}

// Helper functions
function createDailySettlement($mantriId, $settlementDate, $cashCollected, $cashDeposited, $notes = '') {
    global $db;
    $dailySettlement = new DailySettlement($db);
    return $dailySettlement->createSettlement($mantriId, $settlementDate, $cashCollected, $cashDeposited, $notes);
}

function getPendingSettlements() {
    global $db;
    $dailySettlement = new DailySettlement($db);
    return $dailySettlement->getPendingSettlements();
}

function approveSettlement($settlementId) {
    global $db;
    $dailySettlement = new DailySettlement($db);
    return $dailySettlement->approveSettlement($settlementId);
}

?>
