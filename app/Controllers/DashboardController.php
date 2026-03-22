<?php
/**
 * KSP Lam Gabe Jaya - Dashboard Controller
 * Handles dashboard and main application interface
 */

namespace App\Controllers;

class DashboardController extends Controller {
    
    /**
     * Show dashboard
     */
    public function index() {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        
        // Get dashboard statistics
        $stats = $this->getDashboardStats();
        
        return $this->view('dashboard/index', [
            'title' => 'Dashboard - KSP Lam Gabe Jaya',
            'user' => $user,
            'stats' => $stats
        ]);
    }
    
    /**
     * Get dashboard statistics
     */
    private function getDashboardStats() {
        try {
            $db = \Core\Database\Database::getInstance();
            
            // Total members
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM members WHERE status = 'active'");
            $stmt->execute();
            $totalMembers = $stmt->fetchColumn();
            
            // Total accounts
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM accounts WHERE status = 'active'");
            $stmt->execute();
            $totalAccounts = $stmt->fetchColumn();
            
            // Total savings
            $stmt = $db->prepare("SELECT SUM(balance) as total FROM accounts WHERE status = 'active' AND account_type = 'simpanan'");
            $stmt->execute();
            $totalSavings = $stmt->fetchColumn() ?: 0;
            
            // Total loans
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM loans WHERE status = 'active'");
            $stmt->execute();
            $totalLoans = $stmt->fetchColumn();
            
            // Total loan amount
            $stmt = $db->prepare("SELECT SUM(loan_amount) as total FROM loans WHERE status = 'active'");
            $stmt->execute();
            $totalLoanAmount = $stmt->fetchColumn() ?: 0;
            
            // Recent transactions
            $stmt = $db->prepare("
                SELECT t.*, a.account_number, m.full_name 
                FROM transactions t
                JOIN accounts a ON t.account_id = a.id
                JOIN members m ON a.member_id = m.id
                ORDER BY t.transaction_date DESC, t.created_at DESC
                LIMIT 5
            ");
            $stmt->execute();
            $recentTransactions = $stmt->fetchAll();
            
            // Recent loans
            $stmt = $db->prepare("
                SELECT l.*, m.full_name, m.member_number
                FROM loans l
                JOIN members m ON l.member_id = m.id
                ORDER BY l.application_date DESC
                LIMIT 5
            ");
            $stmt->execute();
            $recentLoans = $stmt->fetchAll();
            
            return [
                'total_members' => $totalMembers,
                'total_accounts' => $totalAccounts,
                'total_savings' => $totalSavings,
                'total_loans' => $totalLoans,
                'total_loan_amount' => $totalLoanAmount,
                'recent_transactions' => $recentTransactions,
                'recent_loans' => $recentLoans
            ];
            
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                throw $e;
            }
            
            return [
                'total_members' => 0,
                'total_accounts' => 0,
                'total_savings' => 0,
                'total_loans' => 0,
                'total_loan_amount' => 0,
                'recent_transactions' => [],
                'recent_loans' => []
            ];
        }
    }
}
?>
