<?php
/**
 * SHU (Sisa Hasil Usaha) Calculation System
 */
class SHUCalculation {
    public static function calculateSHU($total_income, $total_expenses) {
        return $total_income - $total_expenses;
    }
    
    public static function distributeSHU($shu_amount, $member_contributions) {
        $total_contributions = array_sum($member_contributions);
        $distributions = [];
        
        foreach ($member_contributions as $member_id => $contribution) {
            $percentage = ($contribution / $total_contributions) * 100;
            $distributions[$member_id] = ($shu_amount * $percentage) / 100;
        }
        
        return $distributions;
    }
    
    public static function generateSHUReport($year) {
        $stmt = $pdo->prepare("
            SELECT 
                m.id,
                m.name,
                SUM(s.amount) as total_savings,
                COUNT(l.id) as loan_count,
                SUM(l.amount) as total_loans
            FROM members m
            LEFT JOIN savings_accounts s ON m.id = s.member_id
            LEFT JOIN loans l ON m.id = l.member_id
            WHERE YEAR(s.created_at) = ? OR YEAR(l.created_at) = ?
            GROUP BY m.id, m.name
        ");
        $stmt->execute([$year, $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>