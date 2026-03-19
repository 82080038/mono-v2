<?php
/**
 * Financial Reporting System
 */
class FinancialReporter {
    public static function generateBalanceSheet($date) {
        $stmt = $pdo->prepare("
            SELECT 
                'Assets' as category,
                SUM(s.amount) as amount
            FROM savings_accounts s
            WHERE s.created_at <= ?
            UNION ALL
            SELECT 
                'Liabilities' as category,
                SUM(l.amount - l.paid_amount) as amount
            FROM loans l
            WHERE l.created_at <= ?
            UNION ALL
            SELECT 
                'Equity' as category,
                SUM(s.amount) - SUM(l.amount - l.paid_amount) as amount
            FROM savings_accounts s
            CROSS JOIN loans l
            WHERE s.created_at <= ? AND l.created_at <= ?
        ");
        $stmt->execute([$date, $date, $date, $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function generateIncomeStatement($start_date, $end_date) {
        $stmt = $pdo->prepare("
            SELECT 
                'Interest Income' as category,
                SUM(l.interest_amount) as amount
            FROM loans l
            WHERE l.created_at BETWEEN ? AND ?
            UNION ALL
            SELECT 
                'Operating Expenses' as category,
                0 as amount
        ");
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>