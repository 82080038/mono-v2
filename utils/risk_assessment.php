<?php
/**
 * RISK ASSESSMENT TOOLS
 * Calculate and manage credit risk for members
 */

class RiskAssessment {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function calculateMemberRisk($memberId) {
        try {
            // Get member data
            $member = $this->getMemberData($memberId);
            
            $score = 100; // Start with perfect score
            $factors = [];
            
            // Check payment history
            $latePayments = $this->getLatePaymentCount($memberId);
            if ($latePayments > 0) {
                $score -= $latePayments * 10;
                $factors[] = 'Late payments: ' . $latePayments;
            }
            
            // Check loan history
            $activeLoans = $this->getActiveLoanCount($memberId);
            if ($activeLoans > 2) {
                $score -= 15;
                $factors[] = 'Multiple active loans: ' . $activeLoans;
            }
            
            // Check savings consistency
            $savingsScore = $this->getSavingsScore($memberId);
            if ($savingsScore < 50) {
                $score -= 10;
                $factors[] = 'Low savings consistency';
            }
            
            // Check membership duration
            $membershipMonths = $this->getMembershipDuration($memberId);
            if ($membershipMonths < 6) {
                $score -= 5;
                $factors[] = 'New member (less than 6 months)';
            }
            
            // Determine risk level
            $riskLevel = $score >= 80 ? 'LOW' : ($score >= 60 ? 'MEDIUM' : 'HIGH');
            
            // Save assessment
            $this->saveAssessment($memberId, $score, $riskLevel, $factors);
            
            return [
                'score' => max(0, min(100, $score)),
                'risk_level' => $riskLevel,
                'factors' => $factors,
                'recommendation' => $this->getRecommendation($riskLevel)
            ];
            
        } catch (Exception $e) {
            error_log("Risk assessment failed: " . $e->getMessage());
            return [
                'score' => 50,
                'risk_level' => 'MEDIUM',
                'factors' => ['Assessment error'],
                'recommendation' => 'Manual review required'
            ];
        }
    }
    
    private function getMemberData($memberId) {
        $stmt = $this->db->prepare("SELECT * FROM members WHERE id = ?");
        $stmt->execute([$memberId]);
        return $stmt->fetch();
    }
    
    private function getLatePaymentCount($memberId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM loan_payments lp
            JOIN loans l ON lp.loan_id = l.id
            WHERE l.member_id = ? AND lp.due_date < lp.paid_date AND lp.paid_date IS NOT NULL
            AND lp.paid_date > DATE_SUB(lp.due_date, INTERVAL 7 DAY)
        ");
        $stmt->execute([$memberId]);
        return $stmt->fetch()['count'];
    }
    
    private function getActiveLoanCount($memberId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM loans 
            WHERE member_id = ? AND status = 'active'
        ");
        $stmt->execute([$memberId]);
        return $stmt->fetch()['count'];
    }
    
    private function getSavingsScore($memberId) {
        $stmt = $this->db->prepare("
            SELECT AVG(balance) as avg_balance, 
                   COUNT(*) as transaction_count
            FROM savings_transactions st
            JOIN savings_accounts sa ON st.account_number = sa.account_number
            WHERE sa.member_id = ? 
            AND st.transaction_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
        ");
        $stmt->execute([$memberId]);
        $result = $stmt->fetch();
        
        // Simple scoring based on transaction consistency
        return $result['transaction_count'] > 0 ? min(100, $result['transaction_count'] * 5) : 0;
    }
    
    private function getMembershipDuration($memberId) {
        $stmt = $this->db->prepare("
            SELECT DATEDIFF(NOW(), registration_date) as days 
            FROM members 
            WHERE id = ?
        ");
        $stmt->execute([$memberId]);
        $days = $stmt->fetch()['days'];
        return floor($days / 30); // Convert to months
    }
    
    private function saveAssessment($memberId, $score, $riskLevel, $factors) {
        $stmt = $this->db->prepare("
            INSERT INTO risk_assessments (uuid, member_id, assessment_type, score, risk_level, factors, assessed_by, created_at)
            VALUES (UUID(), ?, 'automatic', ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            score = VALUES(score),
            risk_level = VALUES(risk_level),
            factors = VALUES(factors),
            assessed_at = NOW()
        ");
        
        return $stmt->execute([
            $memberId,
            $score,
            $riskLevel,
            json_encode($factors),
            $_SESSION['user_id'] ?? 1
        ]);
    }
    
    private function getRecommendation($riskLevel) {
        switch ($riskLevel) {
            case 'LOW':
                return 'Member is eligible for standard loan terms';
            case 'MEDIUM':
                return 'Consider lower loan amount or additional collateral';
            case 'HIGH':
                return 'Require additional guarantees or reconsider application';
            default:
                return 'Manual review required';
        }
    }
    
    public function getMemberRiskHistory($memberId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT * FROM risk_assessments 
            WHERE member_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$memberId, $limit]);
        
        $assessments = $stmt->fetchAll();
        
        // Decode JSON factors
        foreach ($assessments as &$assessment) {
            $assessment['factors'] = json_decode($assessment['factors'], true) ?: [];
        }
        
        return $assessments;
    }
    
    public function getHighRiskMembers($threshold = 60) {
        $stmt = $this->db->prepare("
            SELECT ra.*, m.name, m.member_number, m.email
            FROM risk_assessments ra
            JOIN members m ON ra.member_id = m.id
            WHERE ra.score < ? 
            AND ra.created_at = (
                SELECT MAX(created_at) 
                FROM risk_assessments ra2 
                WHERE ra2.member_id = ra.member_id
            )
            ORDER BY ra.score ASC
        ");
        $stmt->execute([$threshold]);
        
        return $stmt->fetchAll();
    }
    
    public function generateRiskReport() {
        $report = [
            'total_members' => 0,
            'risk_distribution' => [
                'LOW' => 0,
                'MEDIUM' => 0,
                'HIGH' => 0
            ],
            'high_risk_members' => [],
            'average_score' => 0,
            'generated_at' => date('Y-m-d H:i:s')
        ];
        
        // Get all members with latest assessment
        $stmt = $this->db->prepare("
            SELECT ra.*, m.name, m.member_number
            FROM risk_assessments ra
            JOIN members m ON ra.member_id = m.id
            WHERE ra.created_at = (
                SELECT MAX(created_at) 
                FROM risk_assessments ra2 
                WHERE ra2.member_id = ra.member_id
            )
        ");
        $stmt->execute();
        $assessments = $stmt->fetchAll();
        
        $report['total_members'] = count($assessments);
        $totalScore = 0;
        
        foreach ($assessments as $assessment) {
            $report['risk_distribution'][$assessment['risk_level']]++;
            $totalScore += $assessment['score'];
            
            if ($assessment['risk_level'] === 'HIGH') {
                $report['high_risk_members'][] = [
                    'name' => $assessment['name'],
                    'member_number' => $assessment['member_number'],
                    'score' => $assessment['score'],
                    'factors' => json_decode($assessment['factors'], true)
                ];
            }
        }
        
        $report['average_score'] = $report['total_members'] > 0 ? round($totalScore / $report['total_members'], 2) : 0;
        
        return $report;
    }
}

// Helper functions
function assessMemberRisk($memberId) {
    global $db;
    $riskAssessment = new RiskAssessment($db);
    return $riskAssessment->calculateMemberRisk($memberId);
}

function getRiskReport() {
    global $db;
    $riskAssessment = new RiskAssessment($db);
    return $riskAssessment->generateRiskReport();
}

?>
