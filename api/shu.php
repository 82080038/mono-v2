<?php
/**
 * KSP Lam Gabe Jaya - SHU (Sisa Hasil Usaha) API (Phase 2)
 */

header("Access-Control-Allow-Origin: http://localhost");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../core/Middleware.php';
require_once __DIR__ . '/../core/AuditLogger.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    $user  = Middleware::requireAuth();
    $pdo   = Config::getDatabase();

    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $_GET['action'] ?? $_POST['action'] ?? $body['action'] ?? '';
    $input  = array_merge($_GET, $_POST, $body);

    switch ($action) {
        case 'get_periods':        getPeriods($pdo, $response); break;
        case 'get_period':         getPeriod($pdo, $input, $response); break;
        case 'calculate':          Middleware::requireRole(['admin']); calculateSHU($pdo, $input, $user, $response); break;
        case 'save_period':        Middleware::requireRole(['admin']); savePeriod($pdo, $input, $user, $response); break;
        case 'get_distributions':  getDistributions($pdo, $input, $response); break;
        case 'distribute':         Middleware::requireRole(['admin']); distribute($pdo, $input, $user, $response); break;
        case 'finalize':           Middleware::requireRole(['admin']); finalizePeriod($pdo, $input, $user, $response); break;
        default:
            $response['message'] = 'Action tidak ditemukan';
            http_response_code(400);
    }
} catch (Exception $e) {
    $response['message'] = 'Server error: ' . $e->getMessage();
    http_response_code(500);
    error_log('[shu.php] ' . $e->getMessage());
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// ─── HANDLERS ─────────────────────────────────────────────────────────────────

function getPeriods($pdo, &$response) {
    $stmt = $pdo->query("
        SELECT s.*, u.full_name AS finalized_by_name,
               COUNT(d.id) AS member_count
        FROM shu_periods s
        LEFT JOIN users u ON s.finalized_by = u.id
        LEFT JOIN shu_distributions d ON s.id = d.shu_period_id
        GROUP BY s.id
        ORDER BY s.period_year DESC
    ");
    $response['success'] = true;
    $response['data']    = $stmt->fetchAll();
}

function getPeriod($pdo, $input, &$response) {
    $id   = (int)($input['id'] ?? 0);
    $year = (int)($input['year'] ?? 0);
    if (!$id && !$year) { $response['message'] = 'id atau year diperlukan'; http_response_code(400); return; }

    $where = $id ? 'id = ?' : 'period_year = ?';
    $param = $id ?: $year;
    $stmt  = $pdo->prepare("SELECT * FROM shu_periods WHERE $where");
    $stmt->execute([$param]); $period = $stmt->fetch();
    if (!$period) { $response['message'] = 'Periode tidak ditemukan'; http_response_code(404); return; }

    $response['success'] = true;
    $response['data']    = $period;
}

/**
 * Calculate SHU for a given year from journal entries.
 */
function calculateSHU($pdo, $input, $user, &$response) {
    $year = (int)($input['year'] ?? date('Y'));
    $from = "$year-01-01";
    $to   = "$year-12-31";

    // Sum revenue accounts (4-xxx)
    $revStmt = $pdo->prepare("
        SELECT COALESCE(SUM(l.credit_amount - l.debit_amount), 0) AS total
        FROM journal_entry_lines l
        JOIN journal_entries j ON l.journal_entry_id = j.id
        JOIN chart_of_accounts c ON l.account_id = c.id
        WHERE c.account_type = 'revenue' AND j.status = 'posted'
          AND j.entry_date BETWEEN ? AND ?
    ");
    $revStmt->execute([$from, $to]);
    $totalRevenue = (float)$revStmt->fetchColumn();

    // Sum expense accounts (5-xxx)
    $expStmt = $pdo->prepare("
        SELECT COALESCE(SUM(l.debit_amount - l.credit_amount), 0) AS total
        FROM journal_entry_lines l
        JOIN journal_entries j ON l.journal_entry_id = j.id
        JOIN chart_of_accounts c ON l.account_id = c.id
        WHERE c.account_type = 'expense' AND j.status = 'posted'
          AND j.entry_date BETWEEN ? AND ?
    ");
    $expStmt->execute([$from, $to]);
    $totalExpense = (float)$expStmt->fetchColumn();

    $grossSHU = $totalRevenue - $totalExpense;

    // Fetch total member savings balance
    $savingsTotal = $pdo->query("SELECT COALESCE(SUM(balance), 0) FROM accounts WHERE status = 'active'")->fetchColumn();
    // Fetch total loan principal outstanding
    $loanTotal    = $pdo->query("SELECT COALESCE(SUM(outstanding_balance), 0) FROM loans WHERE status IN ('active','disbursed')")->fetchColumn();

    $response['success'] = true;
    $response['data'] = [
        'year'              => $year,
        'total_revenue'     => $totalRevenue,
        'total_expense'     => $totalExpense,
        'gross_shu'         => $grossSHU,
        'total_savings'     => (float)$savingsTotal,
        'total_loans'       => (float)$loanTotal,
    ];
}

function savePeriod($pdo, $input, $user, &$response) {
    $year   = (int)($input['year'] ?? 0);
    $rev    = (float)($input['total_revenue'] ?? 0);
    $exp    = (float)($input['total_expense'] ?? 0);
    $pcts   = [
        'pct_member_savings' => (float)($input['pct_member_savings'] ?? 30),
        'pct_member_loans'   => (float)($input['pct_member_loans']   ?? 30),
        'pct_management'     => (float)($input['pct_management']     ?? 10),
        'pct_education'      => (float)($input['pct_education']      ?? 5),
        'pct_social'         => (float)($input['pct_social']         ?? 5),
        'pct_reserve'        => (float)($input['pct_reserve']        ?? 20),
    ];
    if (!$year) { $response['message'] = 'Tahun diperlukan'; http_response_code(400); return; }
    $totalPct = array_sum($pcts);
    if (abs($totalPct - 100) > 0.01) {
        $response['message'] = "Total persentase harus 100% (saat ini: $totalPct%)";
        http_response_code(400); return;
    }

    $grossSHU = $rev - $exp;
    $stmt = $pdo->prepare("
        INSERT INTO shu_periods (period_year, total_revenue, total_expense, gross_shu,
            pct_member_savings, pct_member_loans, pct_management, pct_education, pct_social, pct_reserve,
            status, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?)
        ON DUPLICATE KEY UPDATE
            total_revenue=VALUES(total_revenue), total_expense=VALUES(total_expense), gross_shu=VALUES(gross_shu),
            pct_member_savings=VALUES(pct_member_savings), pct_member_loans=VALUES(pct_member_loans),
            pct_management=VALUES(pct_management), pct_education=VALUES(pct_education),
            pct_social=VALUES(pct_social), pct_reserve=VALUES(pct_reserve), updated_at=NOW()
    ");
    $stmt->execute([
        $year, $rev, $exp, $grossSHU,
        $pcts['pct_member_savings'], $pcts['pct_member_loans'],
        $pcts['pct_management'], $pcts['pct_education'],
        $pcts['pct_social'], $pcts['pct_reserve'],
        Middleware::getCurrentUserId()
    ]);

    AuditLogger::log('CREATE', 'shu_periods', null, [], ['year' => $year, 'gross_shu' => $grossSHU],
        Middleware::getCurrentUserId(), "Simpan periode SHU tahun $year");

    $response['success'] = true;
    $response['message'] = "Periode SHU tahun $year berhasil disimpan";
    $response['data']    = ['year' => $year, 'gross_shu' => $grossSHU];
}

function getDistributions($pdo, $input, &$response) {
    $periodId = (int)($input['period_id'] ?? 0);
    if (!$periodId) { $response['message'] = 'period_id diperlukan'; http_response_code(400); return; }

    $stmt = $pdo->prepare("
        SELECT d.*, m.full_name AS member_name, m.member_number
        FROM shu_distributions d
        JOIN members m ON d.member_id = m.id
        WHERE d.shu_period_id = ?
        ORDER BY d.total_share DESC
    ");
    $stmt->execute([$periodId]);
    $response['success'] = true;
    $response['data']    = $stmt->fetchAll();
}

/**
 * Calculate and save per-member SHU distribution.
 */
function distribute($pdo, $input, $user, &$response) {
    $periodId = (int)($input['period_id'] ?? 0);
    if (!$periodId) { $response['message'] = 'period_id diperlukan'; http_response_code(400); return; }

    $period = $pdo->prepare("SELECT * FROM shu_periods WHERE id = ? AND status = 'draft'");
    $period->execute([$periodId]); $p = $period->fetch();
    if (!$p) { $response['message'] = 'Periode tidak ditemukan atau sudah final'; http_response_code(404); return; }

    $shuForSavings = $p['gross_shu'] * ($p['pct_member_savings'] / 100);
    $shuForLoans   = $p['gross_shu'] * ($p['pct_member_loans']   / 100);

    // Total simpanan & pinjaman semua anggota
    $totalSavings = (float)$pdo->query("SELECT COALESCE(SUM(balance),0) FROM accounts WHERE status='active'")->fetchColumn();
    $totalLoans   = (float)$pdo->query("SELECT COALESCE(SUM(outstanding_balance),0) FROM loans WHERE status IN ('active','disbursed')")->fetchColumn();

    // Get all active members with savings/loans
    $members = $pdo->query("
        SELECT m.id AS member_id,
               COALESCE(SUM(a.balance), 0) AS savings_balance
        FROM members m
        LEFT JOIN accounts a ON m.id = a.member_id AND a.status = 'active'
        WHERE m.status = 'active'
        GROUP BY m.id
    ")->fetchAll();

    $pdo->beginTransaction();
    try {
        $pdo->prepare("DELETE FROM shu_distributions WHERE shu_period_id = ?")->execute([$periodId]);

        $insertStmt = $pdo->prepare("
            INSERT INTO shu_distributions
                (shu_period_id, member_id, savings_balance, loan_principal, savings_share, loan_share, total_share)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $count = 0;
        foreach ($members as $m) {
            $loanBalance = (float)$pdo->prepare("
                SELECT COALESCE(SUM(outstanding_balance),0)
                FROM loans WHERE member_id = ? AND status IN ('active','disbursed')
            ")->execute([$m['member_id']]) ? $pdo->prepare("
                SELECT COALESCE(SUM(outstanding_balance),0) FROM loans WHERE member_id = ? AND status IN ('active','disbursed')
            ")->execute([$m['member_id']]) : 0;

            // Recalculate properly
            $loanStmt = $pdo->prepare("SELECT COALESCE(SUM(outstanding_balance),0) as lb FROM loans WHERE member_id=? AND status IN ('active','disbursed')");
            $loanStmt->execute([$m['member_id']]);
            $loanBalance = (float)$loanStmt->fetchColumn();

            $savingsShare = $totalSavings > 0 ? ($m['savings_balance'] / $totalSavings) * $shuForSavings : 0;
            $loanShare    = $totalLoans   > 0 ? ($loanBalance / $totalLoans) * $shuForLoans : 0;
            $totalShare   = $savingsShare + $loanShare;

            if ($totalShare > 0) {
                $insertStmt->execute([
                    $periodId, $m['member_id'],
                    $m['savings_balance'], $loanBalance,
                    $savingsShare, $loanShare, $totalShare
                ]);
                $count++;
            }
        }

        AuditLogger::log('CREATE', 'shu_distributions', $periodId, [], ['member_count' => $count],
            Middleware::getCurrentUserId(), "Hitung distribusi SHU periode id=$periodId");

        $pdo->commit();
        $response['success'] = true;
        $response['message'] = "Distribusi SHU berhasil dihitung untuk $count anggota";
        $response['data']    = ['member_count' => $count, 'shu_for_savings' => $shuForSavings, 'shu_for_loans' => $shuForLoans];
    } catch (Exception $e) {
        $pdo->rollBack(); throw $e;
    }
}

function finalizePeriod($pdo, $input, $user, &$response) {
    $periodId = (int)($input['period_id'] ?? 0);
    if (!$periodId) { $response['message'] = 'period_id diperlukan'; http_response_code(400); return; }

    $count = $pdo->prepare("SELECT COUNT(*) FROM shu_distributions WHERE shu_period_id = ?");
    $count->execute([$periodId]);
    if ($count->fetchColumn() == 0) {
        $response['message'] = 'Hitung distribusi terlebih dahulu sebelum finalisasi';
        http_response_code(400); return;
    }

    $pdo->prepare("UPDATE shu_periods SET status='final', finalized_by=?, finalized_at=NOW() WHERE id=?")
        ->execute([Middleware::getCurrentUserId(), $periodId]);

    AuditLogger::log('APPROVE', 'shu_periods', $periodId, [], ['status' => 'final'],
        Middleware::getCurrentUserId(), "Finalisasi periode SHU id=$periodId");

    $response['success'] = true;
    $response['message'] = 'Periode SHU berhasil difinalisasi';
}
