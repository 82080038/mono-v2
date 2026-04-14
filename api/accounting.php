<?php
/**
 * KSP Lam Gabe Jaya - Accounting API (Phase 2)
 * Chart of Accounts, Journal Entries, Financial Reports
 */

header("Access-Control-Allow-Origin: http://localhost");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../core/Middleware.php';
require_once __DIR__ . '/../core/AuditLogger.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    $user = Middleware::requireAuth();
    $pdo  = Config::getDatabase();

    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    if (!$action) {
        $input  = json_decode(file_get_contents('php://input'), true) ?? [];
        $action = $input['action'] ?? '';
    } else {
        $input = array_merge($_GET, $_POST);
    }

    // ─── ROUTER ───────────────────────────────────────────────────────────────
    switch ($action) {
        // Chart of Accounts
        case 'get_coa':            getCOA($pdo, $response); break;
        case 'create_coa':         Middleware::requireRole(['admin']); createCOA($pdo, $input, $user, $response); break;
        case 'update_coa':         Middleware::requireRole(['admin']); updateCOA($pdo, $input, $user, $response); break;

        // Journal Entries
        case 'get_journals':       getJournals($pdo, $input, $response); break;
        case 'get_journal':        getJournal($pdo, $input, $response); break;
        case 'create_journal':     Middleware::requireRole(['admin','kasir']); createJournal($pdo, $input, $user, $response); break;
        case 'reverse_journal':    Middleware::requireRole(['admin']); reverseJournal($pdo, $input, $user, $response); break;

        // Financial Reports
        case 'trial_balance':      getTrialBalance($pdo, $input, $response); break;
        case 'balance_sheet':      getBalanceSheet($pdo, $input, $response); break;
        case 'income_statement':   getIncomeStatement($pdo, $input, $response); break;
        case 'cash_flow':          getCashFlow($pdo, $input, $response); break;
        case 'general_ledger':     getGeneralLedger($pdo, $input, $response); break;

        default:
            $response['message'] = 'Action tidak ditemukan: ' . htmlspecialchars($action);
            http_response_code(400);
    }
} catch (Exception $e) {
    $response['message'] = 'Server error: ' . $e->getMessage();
    http_response_code(500);
    error_log('[accounting.php] ' . $e->getMessage());
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// ─── CHART OF ACCOUNTS ────────────────────────────────────────────────────────

function getCOA($pdo, &$response) {
    $stmt = $pdo->query("
        SELECT c.*, p.account_name AS parent_name
        FROM chart_of_accounts c
        LEFT JOIN chart_of_accounts p ON c.parent_id = p.id
        ORDER BY c.account_code
    ");
    $response['success'] = true;
    $response['data']    = $stmt->fetchAll();
}

function createCOA($pdo, $input, $user, &$response) {
    $code = trim($input['account_code'] ?? '');
    $name = trim($input['account_name'] ?? '');
    $type = $input['account_type'] ?? '';
    $normalBalance = $input['normal_balance'] ?? '';

    if (!$code || !$name || !$type || !$normalBalance) {
        $response['message'] = 'Kode akun, nama, tipe, dan saldo normal wajib diisi';
        http_response_code(400); return;
    }
    $validTypes   = ['asset','liability','equity','revenue','expense'];
    $validNormal  = ['debit','credit'];
    if (!in_array($type, $validTypes) || !in_array($normalBalance, $validNormal)) {
        $response['message'] = 'Tipe atau saldo normal tidak valid';
        http_response_code(400); return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO chart_of_accounts (account_code, account_name, account_type, parent_id, normal_balance, notes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$code, $name, $type, $input['parent_id'] ?: null, $normalBalance, $input['notes'] ?? null]);
    $newId = (int)$pdo->lastInsertId();

    AuditLogger::logCreate('chart_of_accounts', $newId,
        ['code' => $code, 'name' => $name, 'type' => $type],
        Middleware::getCurrentUserId(), "Buat akun baru: $code - $name"
    );

    $response['success'] = true;
    $response['message'] = 'Akun berhasil dibuat';
    $response['data']    = ['id' => $newId];
}

function updateCOA($pdo, $input, $user, &$response) {
    $id   = (int)($input['id'] ?? 0);
    $name = trim($input['account_name'] ?? '');
    if (!$id || !$name) {
        $response['message'] = 'ID dan nama akun wajib diisi';
        http_response_code(400); return;
    }
    $stmt = $pdo->prepare("SELECT * FROM chart_of_accounts WHERE id = ?");
    $stmt->execute([$id]); $old = $stmt->fetch();
    if (!$old) { $response['message'] = 'Akun tidak ditemukan'; http_response_code(404); return; }

    $pdo->prepare("UPDATE chart_of_accounts SET account_name=?, notes=?, updated_at=NOW() WHERE id=?")
        ->execute([$name, $input['notes'] ?? $old['notes'], $id]);

    AuditLogger::logUpdate('chart_of_accounts', $id,
        ['name' => $old['account_name']], ['name' => $name],
        Middleware::getCurrentUserId()
    );
    $response['success'] = true;
    $response['message'] = 'Akun berhasil diperbarui';
}

// ─── JOURNAL ENTRIES ──────────────────────────────────────────────────────────

function getJournals($pdo, $input, &$response) {
    $dateFrom = $input['date_from'] ?? date('Y-m-01');
    $dateTo   = $input['date_to']   ?? date('Y-m-d');
    $page     = max(1, (int)($input['page'] ?? 1));
    $limit    = 20;
    $offset   = ($page - 1) * $limit;

    $stmt = $pdo->prepare("
        SELECT j.*, u.full_name AS created_by_name
        FROM journal_entries j
        LEFT JOIN users u ON j.created_by = u.id
        WHERE j.entry_date BETWEEN ? AND ?
        ORDER BY j.entry_date DESC, j.id DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$dateFrom, $dateTo, $limit, $offset]);
    $journals = $stmt->fetchAll();

    $total = $pdo->prepare("SELECT COUNT(*) FROM journal_entries WHERE entry_date BETWEEN ? AND ?");
    $total->execute([$dateFrom, $dateTo]);

    $response['success'] = true;
    $response['data']    = [
        'journals'   => $journals,
        'total'      => (int)$total->fetchColumn(),
        'page'       => $page,
        'date_from'  => $dateFrom,
        'date_to'    => $dateTo,
    ];
}

function getJournal($pdo, $input, &$response) {
    $id = (int)($input['id'] ?? 0);
    if (!$id) { $response['message'] = 'ID jurnal diperlukan'; http_response_code(400); return; }

    $stmt = $pdo->prepare("SELECT j.*, u.full_name AS created_by_name FROM journal_entries j LEFT JOIN users u ON j.created_by = u.id WHERE j.id = ?");
    $stmt->execute([$id]); $journal = $stmt->fetch();
    if (!$journal) { $response['message'] = 'Jurnal tidak ditemukan'; http_response_code(404); return; }

    $linesStmt = $pdo->prepare("
        SELECT l.*, c.account_code, c.account_name, c.account_type, c.normal_balance
        FROM journal_entry_lines l
        JOIN chart_of_accounts c ON l.account_id = c.id
        WHERE l.journal_entry_id = ?
        ORDER BY l.line_order
    ");
    $linesStmt->execute([$id]);
    $journal['lines'] = $linesStmt->fetchAll();

    $response['success'] = true;
    $response['data']    = $journal;
}

function createJournal($pdo, $input, $user, &$response) {
    $date  = $input['entry_date']  ?? date('Y-m-d');
    $desc  = trim($input['description'] ?? '');
    $lines = $input['lines'] ?? [];
    $refType = $input['reference_type'] ?? null;
    $refId   = $input['reference_id']   ?? null;

    if (!$desc || count($lines) < 2) {
        $response['message'] = 'Deskripsi dan minimal 2 baris jurnal diperlukan';
        http_response_code(400); return;
    }

    // Validate debit == credit
    $totalDebit = $totalCredit = 0;
    foreach ($lines as $l) {
        $totalDebit  += (float)($l['debit_amount']  ?? 0);
        $totalCredit += (float)($l['credit_amount'] ?? 0);
    }
    if (abs($totalDebit - $totalCredit) > 0.01) {
        $response['message'] = 'Total debit dan kredit harus seimbang (debit: ' . number_format($totalDebit,2) . ', kredit: ' . number_format($totalCredit,2) . ')';
        http_response_code(400); return;
    }

    $pdo->beginTransaction();
    try {
        // Generate journal number
        $ym  = date('Ym', strtotime($date));
        $seq = $pdo->query("SELECT COUNT(*)+1 FROM journal_entries WHERE DATE_FORMAT(entry_date,'%Y%m') = '$ym'")->fetchColumn();
        $journalNumber = sprintf('JRN-%s-%04d', $ym, $seq);

        $stmt = $pdo->prepare("
            INSERT INTO journal_entries (journal_number, entry_date, description, reference_type, reference_id, status, created_by)
            VALUES (?, ?, ?, ?, ?, 'posted', ?)
        ");
        $stmt->execute([$journalNumber, $date, $desc, $refType, $refId, Middleware::getCurrentUserId()]);
        $journalId = (int)$pdo->lastInsertId();

        $lineStmt = $pdo->prepare("
            INSERT INTO journal_entry_lines (journal_entry_id, account_id, debit_amount, credit_amount, description, line_order)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        foreach ($lines as $i => $l) {
            $lineStmt->execute([
                $journalId,
                (int)$l['account_id'],
                (float)($l['debit_amount']  ?? 0),
                (float)($l['credit_amount'] ?? 0),
                $l['description'] ?? null,
                $i + 1
            ]);
        }

        AuditLogger::logCreate('journal_entries', $journalId,
            ['number' => $journalNumber, 'date' => $date, 'desc' => $desc],
            Middleware::getCurrentUserId(), "Buat jurnal: $journalNumber"
        );

        $pdo->commit();
        $response['success'] = true;
        $response['message'] = 'Jurnal berhasil disimpan';
        $response['data']    = ['id' => $journalId, 'journal_number' => $journalNumber];
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function reverseJournal($pdo, $input, $user, &$response) {
    $id   = (int)($input['id'] ?? 0);
    $note = trim($input['note'] ?? '');
    if (!$id) { $response['message'] = 'ID jurnal diperlukan'; http_response_code(400); return; }

    $stmt = $pdo->prepare("SELECT * FROM journal_entries WHERE id = ? AND status = 'posted'");
    $stmt->execute([$id]); $journal = $stmt->fetch();
    if (!$journal) { $response['message'] = 'Jurnal tidak ditemukan atau sudah dibalik'; http_response_code(404); return; }

    $linesStmt = $pdo->prepare("SELECT * FROM journal_entry_lines WHERE journal_entry_id = ? ORDER BY line_order");
    $linesStmt->execute([$id]); $lines = $linesStmt->fetchAll();

    $pdo->beginTransaction();
    try {
        // Mark original as reversed
        $pdo->prepare("UPDATE journal_entries SET status='reversed', reversed_by=?, reversed_at=NOW(), reversal_note=? WHERE id=?")
            ->execute([Middleware::getCurrentUserId(), $note, $id]);

        // Create reversal entry
        $ym  = date('Ym');
        $seq = $pdo->query("SELECT COUNT(*)+1 FROM journal_entries WHERE DATE_FORMAT(entry_date,'%Y%m') = '$ym'")->fetchColumn();
        $revNumber = sprintf('JRN-%s-%04d', $ym, $seq);

        $revStmt = $pdo->prepare("
            INSERT INTO journal_entries (journal_number, entry_date, description, reference_type, reference_id, status, created_by)
            VALUES (?, NOW(), ?, 'reversal', ?, 'posted', ?)
        ");
        $revStmt->execute([$revNumber, 'REVERSAL: ' . $journal['description'], $id, Middleware::getCurrentUserId()]);
        $revId = (int)$pdo->lastInsertId();

        $lineStmt = $pdo->prepare("
            INSERT INTO journal_entry_lines (journal_entry_id, account_id, debit_amount, credit_amount, description, line_order)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        foreach ($lines as $i => $l) {
            // Swap debit and credit for reversal
            $lineStmt->execute([$revId, $l['account_id'], $l['credit_amount'], $l['debit_amount'], $l['description'], $i + 1]);
        }

        AuditLogger::logCreate('journal_entries', $revId,
            ['reversal_of' => $journal['journal_number']],
            Middleware::getCurrentUserId(), "Balik jurnal: {$journal['journal_number']}"
        );

        $pdo->commit();
        $response['success'] = true;
        $response['message'] = 'Jurnal berhasil dibalik';
        $response['data']    = ['reversal_id' => $revId, 'reversal_number' => $revNumber];
    } catch (Exception $e) {
        $pdo->rollBack(); throw $e;
    }
}

// ─── FINANCIAL REPORTS ────────────────────────────────────────────────────────

function getTrialBalance($pdo, $input, &$response) {
    $dateFrom = $input['date_from'] ?? date('Y-01-01');
    $dateTo   = $input['date_to']   ?? date('Y-m-d');

    $stmt = $pdo->prepare("
        SELECT
            c.account_code, c.account_name, c.account_type, c.normal_balance,
            COALESCE(SUM(l.debit_amount), 0)  AS total_debit,
            COALESCE(SUM(l.credit_amount), 0) AS total_credit,
            CASE c.normal_balance
                WHEN 'debit'  THEN COALESCE(SUM(l.debit_amount),0) - COALESCE(SUM(l.credit_amount),0)
                WHEN 'credit' THEN COALESCE(SUM(l.credit_amount),0) - COALESCE(SUM(l.debit_amount),0)
            END AS balance
        FROM chart_of_accounts c
        LEFT JOIN journal_entry_lines l ON c.id = l.account_id
        LEFT JOIN journal_entries j ON l.journal_entry_id = j.id
            AND j.status = 'posted'
            AND j.entry_date BETWEEN ? AND ?
        WHERE c.is_active = 1
        GROUP BY c.id
        ORDER BY c.account_code
    ");
    $stmt->execute([$dateFrom, $dateTo]);
    $rows = $stmt->fetchAll();

    $totalDebit = $totalCredit = 0;
    foreach ($rows as $r) { $totalDebit += $r['total_debit']; $totalCredit += $r['total_credit']; }

    $response['success'] = true;
    $response['data'] = [
        'rows'         => $rows,
        'total_debit'  => $totalDebit,
        'total_credit' => $totalCredit,
        'is_balanced'  => abs($totalDebit - $totalCredit) < 0.01,
        'period'       => ['from' => $dateFrom, 'to' => $dateTo],
    ];
}

function getBalanceSheet($pdo, $input, &$response) {
    $asOf = $input['as_of'] ?? date('Y-m-d');

    $stmt = $pdo->prepare("
        SELECT
            c.account_code, c.account_name, c.account_type, c.normal_balance, c.parent_id,
            CASE c.normal_balance
                WHEN 'debit'  THEN COALESCE(SUM(l.debit_amount),0) - COALESCE(SUM(l.credit_amount),0)
                WHEN 'credit' THEN COALESCE(SUM(l.credit_amount),0) - COALESCE(SUM(l.debit_amount),0)
            END AS balance
        FROM chart_of_accounts c
        LEFT JOIN journal_entry_lines l ON c.id = l.account_id
        LEFT JOIN journal_entries j ON l.journal_entry_id = j.id
            AND j.status = 'posted' AND j.entry_date <= ?
        WHERE c.account_type IN ('asset','liability','equity') AND c.is_active = 1
        GROUP BY c.id
        ORDER BY c.account_code
    ");
    $stmt->execute([$asOf]);
    $rows = $stmt->fetchAll();

    $grouped = ['asset' => [], 'liability' => [], 'equity' => []];
    $totals  = ['asset' => 0, 'liability' => 0, 'equity' => 0];
    foreach ($rows as $r) {
        $grouped[$r['account_type']][] = $r;
        $totals[$r['account_type']] += $r['balance'];
    }

    $response['success'] = true;
    $response['data'] = [
        'as_of'     => $asOf,
        'assets'    => $grouped['asset'],
        'liabilities' => $grouped['liability'],
        'equity'    => $grouped['equity'],
        'total_assets'       => $totals['asset'],
        'total_liabilities'  => $totals['liability'],
        'total_equity'       => $totals['equity'],
        'is_balanced'        => abs($totals['asset'] - ($totals['liability'] + $totals['equity'])) < 0.01,
    ];
}

function getIncomeStatement($pdo, $input, &$response) {
    $dateFrom = $input['date_from'] ?? date('Y-01-01');
    $dateTo   = $input['date_to']   ?? date('Y-m-d');

    $stmt = $pdo->prepare("
        SELECT
            c.account_code, c.account_name, c.account_type, c.normal_balance,
            CASE c.normal_balance
                WHEN 'credit' THEN COALESCE(SUM(l.credit_amount),0) - COALESCE(SUM(l.debit_amount),0)
                WHEN 'debit'  THEN COALESCE(SUM(l.debit_amount),0) - COALESCE(SUM(l.credit_amount),0)
            END AS amount
        FROM chart_of_accounts c
        LEFT JOIN journal_entry_lines l ON c.id = l.account_id
        LEFT JOIN journal_entries j ON l.journal_entry_id = j.id
            AND j.status = 'posted' AND j.entry_date BETWEEN ? AND ?
        WHERE c.account_type IN ('revenue','expense') AND c.is_active = 1
        GROUP BY c.id
        HAVING amount != 0
        ORDER BY c.account_code
    ");
    $stmt->execute([$dateFrom, $dateTo]);
    $rows = $stmt->fetchAll();

    $revenues = $expenses = [];
    $totalRevenue = $totalExpense = 0;
    foreach ($rows as $r) {
        if ($r['account_type'] === 'revenue') { $revenues[] = $r; $totalRevenue += $r['amount']; }
        else                                  { $expenses[] = $r; $totalExpense += $r['amount']; }
    }

    $response['success'] = true;
    $response['data'] = [
        'period'         => ['from' => $dateFrom, 'to' => $dateTo],
        'revenues'       => $revenues,
        'expenses'       => $expenses,
        'total_revenue'  => $totalRevenue,
        'total_expense'  => $totalExpense,
        'net_income'     => $totalRevenue - $totalExpense,
    ];
}

function getCashFlow($pdo, $input, &$response) {
    $dateFrom = $input['date_from'] ?? date('Y-01-01');
    $dateTo   = $input['date_to']   ?? date('Y-m-d');

    // Simplified: track kas & bank accounts
    $stmt = $pdo->prepare("
        SELECT
            j.entry_date, j.description, j.journal_number,
            l.debit_amount, l.credit_amount,
            c.account_name
        FROM journal_entry_lines l
        JOIN chart_of_accounts c ON l.account_id = c.id
        JOIN journal_entries j   ON l.journal_entry_id = j.id
        WHERE c.account_code IN ('1-110','1-120','1-210')
          AND j.status = 'posted'
          AND j.entry_date BETWEEN ? AND ?
        ORDER BY j.entry_date, j.id
    ");
    $stmt->execute([$dateFrom, $dateTo]);
    $rows = $stmt->fetchAll();

    $totalIn = $totalOut = 0;
    foreach ($rows as $r) { $totalIn += $r['debit_amount']; $totalOut += $r['credit_amount']; }

    $response['success'] = true;
    $response['data'] = [
        'period'      => ['from' => $dateFrom, 'to' => $dateTo],
        'rows'        => $rows,
        'total_in'    => $totalIn,
        'total_out'   => $totalOut,
        'net_cash'    => $totalIn - $totalOut,
    ];
}

function getGeneralLedger($pdo, $input, &$response) {
    $accountId = (int)($input['account_id'] ?? 0);
    $dateFrom  = $input['date_from'] ?? date('Y-01-01');
    $dateTo    = $input['date_to']   ?? date('Y-m-d');

    if (!$accountId) { $response['message'] = 'account_id diperlukan'; http_response_code(400); return; }

    $stmt = $pdo->prepare("
        SELECT l.*, j.journal_number, j.entry_date, j.description AS journal_desc
        FROM journal_entry_lines l
        JOIN journal_entries j ON l.journal_entry_id = j.id
        WHERE l.account_id = ? AND j.status = 'posted' AND j.entry_date BETWEEN ? AND ?
        ORDER BY j.entry_date, j.id
    ");
    $stmt->execute([$accountId, $dateFrom, $dateTo]);
    $lines = $stmt->fetchAll();

    $acct = $pdo->prepare("SELECT * FROM chart_of_accounts WHERE id = ?");
    $acct->execute([$accountId]);

    $response['success'] = true;
    $response['data'] = [
        'account' => $acct->fetch(),
        'lines'   => $lines,
        'period'  => ['from' => $dateFrom, 'to' => $dateTo],
    ];
}
