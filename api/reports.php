<?php
/**
 * KSP Lam Gabe Jaya - Reports Export API (Phase 2)
 * Generates PDF and Excel reports using TCPDF / PhpSpreadsheet.
 * Falls back to HTML-formatted table if libraries not installed.
 */

require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../core/Middleware.php';
require_once __DIR__ . '/../core/AuditLogger.php';

// Detect available libraries
$tcpdfAvailable       = file_exists(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php');
$spreadsheetAvailable = file_exists(__DIR__ . '/../vendor/phpoffice/phpspreadsheet/src/Bootstrap.php');

if ($tcpdfAvailable)       require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
if ($spreadsheetAvailable) require_once __DIR__ . '/../vendor/autoload.php';

try {
    $user   = Middleware::requireAuth();
    $pdo    = Config::getDatabase();

    $format = strtolower($_GET['format'] ?? 'pdf');   // pdf | excel | csv
    $report = strtolower($_GET['report'] ?? '');       // trial_balance | balance_sheet | income_statement | loans | savings | members | shu
    $from   = $_GET['date_from'] ?? date('Y-01-01');
    $to     = $_GET['date_to']   ?? date('Y-m-d');
    $asOf   = $_GET['as_of']     ?? date('Y-m-d');
    $year   = (int)($_GET['year'] ?? date('Y'));

    if (!$report) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Parameter report diperlukan']);
        exit;
    }

    // Fetch report data
    $data = fetchReportData($pdo, $report, $from, $to, $asOf, $year);

    // Log export
    AuditLogger::log('EXPORT', 'reports', null, [], ['report' => $report, 'format' => $format],
        Middleware::getCurrentUserId(), "Export $report as $format");

    // Output
    switch ($format) {
        case 'excel': outputExcel($data, $report, $spreadsheetAvailable); break;
        case 'csv':   outputCSV($data, $report); break;
        default:      outputPDF($data, $report, $from, $to, $asOf, $year, $tcpdfAvailable); break;
    }

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// ─── DATA FETCHER ─────────────────────────────────────────────────────────────

function fetchReportData($pdo, $report, $from, $to, $asOf, $year): array {
    switch ($report) {
        case 'trial_balance':
            return fetchTrialBalance($pdo, $from, $to);
        case 'balance_sheet':
            return fetchBalanceSheet($pdo, $asOf);
        case 'income_statement':
            return fetchIncomeStatement($pdo, $from, $to);
        case 'loans':
            return fetchLoansReport($pdo, $from, $to);
        case 'savings':
            return fetchSavingsReport($pdo, $from, $to);
        case 'members':
            return fetchMembersReport($pdo);
        case 'shu':
            return fetchSHUReport($pdo, $year);
        case 'audit_logs':
            return fetchAuditLogsReport($pdo, $from, $to);
        default:
            throw new Exception("Laporan '$report' tidak dikenal");
    }
}

function fetchTrialBalance($pdo, $from, $to): array {
    $stmt = $pdo->prepare("
        SELECT c.account_code, c.account_name, c.account_type,
               COALESCE(SUM(l.debit_amount),0)  AS total_debit,
               COALESCE(SUM(l.credit_amount),0) AS total_credit
        FROM chart_of_accounts c
        LEFT JOIN journal_entry_lines l ON c.id = l.account_id
        LEFT JOIN journal_entries j ON l.journal_entry_id = j.id AND j.status='posted' AND j.entry_date BETWEEN ? AND ?
        WHERE c.is_active = 1
        GROUP BY c.id ORDER BY c.account_code
    ");
    $stmt->execute([$from, $to]);
    return [
        'title'   => 'NERACA SALDO',
        'subtitle'=> "Periode: " . formatDate($from) . " s/d " . formatDate($to),
        'headers' => ['Kode Akun', 'Nama Akun', 'Tipe', 'Debit (Rp)', 'Kredit (Rp)'],
        'rows'    => array_map(fn($r) => [
            $r['account_code'], $r['account_name'], ucfirst($r['account_type']),
            formatRp($r['total_debit']), formatRp($r['total_credit'])
        ], $stmt->fetchAll() ?: []),
    ];
}

function fetchBalanceSheet($pdo, $asOf): array {
    $stmt = $pdo->prepare("
        SELECT c.account_code, c.account_name, c.account_type,
            CASE c.normal_balance
                WHEN 'debit'  THEN COALESCE(SUM(l.debit_amount),0)-COALESCE(SUM(l.credit_amount),0)
                WHEN 'credit' THEN COALESCE(SUM(l.credit_amount),0)-COALESCE(SUM(l.debit_amount),0)
            END AS balance
        FROM chart_of_accounts c
        LEFT JOIN journal_entry_lines l ON c.id=l.account_id
        LEFT JOIN journal_entries j ON l.journal_entry_id=j.id AND j.status='posted' AND j.entry_date<=?
        WHERE c.account_type IN ('asset','liability','equity') AND c.is_active=1
        GROUP BY c.id ORDER BY c.account_code
    ");
    $stmt->execute([$asOf]);
    return [
        'title'   => 'NERACA',
        'subtitle'=> "Per tanggal: " . formatDate($asOf),
        'headers' => ['Kode Akun', 'Nama Akun', 'Tipe', 'Saldo (Rp)'],
        'rows'    => array_map(fn($r) => [
            $r['account_code'], $r['account_name'], ucfirst($r['account_type']),
            formatRp($r['balance'])
        ], $stmt->fetchAll() ?: []),
    ];
}

function fetchIncomeStatement($pdo, $from, $to): array {
    $stmt = $pdo->prepare("
        SELECT c.account_code, c.account_name, c.account_type,
            CASE c.normal_balance
                WHEN 'credit' THEN COALESCE(SUM(l.credit_amount),0)-COALESCE(SUM(l.debit_amount),0)
                WHEN 'debit'  THEN COALESCE(SUM(l.debit_amount),0)-COALESCE(SUM(l.credit_amount),0)
            END AS amount
        FROM chart_of_accounts c
        LEFT JOIN journal_entry_lines l ON c.id=l.account_id
        LEFT JOIN journal_entries j ON l.journal_entry_id=j.id AND j.status='posted' AND j.entry_date BETWEEN ? AND ?
        WHERE c.account_type IN ('revenue','expense') AND c.is_active=1
        GROUP BY c.id HAVING amount!=0 ORDER BY c.account_code
    ");
    $stmt->execute([$from, $to]);
    return [
        'title'   => 'LAPORAN LABA RUGI',
        'subtitle'=> "Periode: " . formatDate($from) . " s/d " . formatDate($to),
        'headers' => ['Kode Akun', 'Nama Akun', 'Tipe', 'Jumlah (Rp)'],
        'rows'    => array_map(fn($r) => [
            $r['account_code'], $r['account_name'], ucfirst($r['account_type']),
            formatRp($r['amount'])
        ], $stmt->fetchAll() ?: []),
    ];
}

function fetchLoansReport($pdo, $from, $to): array {
    $stmt = $pdo->prepare("
        SELECT m.member_number, m.full_name, l.loan_number, l.loan_type_id AS loan_type,
               l.amount AS loan_amount, l.outstanding_balance, l.interest_rate,
               l.status, l.disbursement_date, l.maturity_date
        FROM loans l
        JOIN members m ON l.member_id = m.id
        WHERE (l.created_at BETWEEN ? AND ? OR l.disbursement_date BETWEEN ? AND ?)
        ORDER BY l.disbursement_date DESC
    ");
    $stmt->execute([$from, $to, $from, $to]);
    return [
        'title'   => 'LAPORAN PINJAMAN',
        'subtitle'=> "Periode: " . formatDate($from) . " s/d " . formatDate($to),
        'headers' => ['No Anggota', 'Nama', 'No Pinjaman', 'Jenis', 'Jumlah (Rp)', 'Sisa (Rp)', 'Bunga %', 'Status', 'Cair', 'Jatuh Tempo'],
        'rows'    => array_map(fn($r) => [
            $r['member_number'], $r['full_name'], $r['loan_number'], $r['loan_type'],
            formatRp($r['loan_amount']), formatRp($r['outstanding_balance']),
            $r['interest_rate'] . '%', $r['status'],
            $r['disbursement_date'] ?? '-', $r['maturity_date'] ?? '-'
        ], $stmt->fetchAll() ?: []),
    ];
}

function fetchSavingsReport($pdo, $from, $to): array {
    $stmt = $pdo->prepare("
        SELECT m.member_number, m.full_name, a.account_number, a.account_type_id AS account_type,
               a.balance, a.interest_rate, a.status, a.opening_date AS opened_date
        FROM accounts a
        JOIN members m ON a.member_id = m.id
        ORDER BY m.member_number, a.account_type
    ");
    $stmt->execute();
    return [
        'title'   => 'LAPORAN SIMPANAN ANGGOTA',
        'subtitle'=> "Per tanggal: " . formatDate(date('Y-m-d')),
        'headers' => ['No Anggota', 'Nama', 'No Rekening', 'Jenis', 'Saldo (Rp)', 'Bunga %', 'Status', 'Dibuka'],
        'rows'    => array_map(fn($r) => [
            $r['member_number'], $r['full_name'], $r['account_number'], $r['account_type'],
            formatRp($r['balance']), $r['interest_rate'] . '%', $r['status'], $r['opened_date'] ?? '-'
        ], $stmt->fetchAll() ?: []),
    ];
}

function fetchMembersReport($pdo): array {
    $stmt = $pdo->query("
        SELECT member_number, full_name, email, phone_number,
               member_type_id AS member_type, status, created_at AS join_date
        FROM members ORDER BY member_number
    ");
    return [
        'title'   => 'DAFTAR ANGGOTA',
        'subtitle'=> "Per tanggal: " . formatDate(date('Y-m-d')),
        'headers' => ['No Anggota', 'Nama Lengkap', 'Email', 'Telepon', 'Tipe', 'Status', 'Tanggal Bergabung'],
        'rows'    => array_map(fn($r) => [
            $r['member_number'], $r['full_name'], $r['email'] ?? '-', $r['phone_number'] ?? '-',
            $r['member_type'] ?? '-', $r['status'], $r['join_date'] ?? '-'
        ], $stmt->fetchAll() ?: []),
    ];
}

function fetchSHUReport($pdo, $year): array {
    $period = $pdo->prepare("SELECT * FROM shu_periods WHERE period_year = ?");
    $period->execute([$year]); $p = $period->fetch();
    if (!$p) return ['title' => "SHU $year", 'subtitle' => 'Data tidak tersedia', 'headers' => [], 'rows' => []];

    $stmt = $pdo->prepare("
        SELECT m.member_number, m.full_name,
               d.savings_balance, d.loan_principal, d.savings_share, d.loan_share, d.total_share, d.is_distributed
        FROM shu_distributions d
        JOIN members m ON d.member_id = m.id
        WHERE d.shu_period_id = ?
        ORDER BY d.total_share DESC
    ");
    $stmt->execute([$p['id']]);
    return [
        'title'   => "LAPORAN SHU TAHUN $year",
        'subtitle'=> "Total SHU: " . formatRp($p['gross_shu']) . " | Status: " . strtoupper($p['status']),
        'headers' => ['No Anggota', 'Nama', 'Saldo Simpanan', 'Saldo Pinjaman', 'Bagian Simpanan', 'Bagian Pinjaman', 'Total SHU', 'Sudah Dibagi'],
        'rows'    => array_map(fn($r) => [
            $r['member_number'], $r['full_name'],
            formatRp($r['savings_balance']), formatRp($r['loan_principal']),
            formatRp($r['savings_share']),   formatRp($r['loan_share']),
            formatRp($r['total_share']),     $r['is_distributed'] ? 'Ya' : 'Belum'
        ], $stmt->fetchAll() ?: []),
    ];
}

function fetchAuditLogsReport($pdo, $from, $to): array {
    $stmt = $pdo->prepare("
        SELECT a.created_at, u.full_name AS user_name, a.action, a.table_name,
               a.record_id, a.ip_address, a.description
        FROM audit_logs a
        LEFT JOIN users u ON a.user_id = u.id
        WHERE DATE(a.created_at) BETWEEN ? AND ?
        ORDER BY a.created_at DESC
        LIMIT 5000
    ");
    $stmt->execute([$from, $to]);
    return [
        'title'   => 'LAPORAN AUDIT TRAIL',
        'subtitle'=> "Periode: " . formatDate($from) . " s/d " . formatDate($to),
        'headers' => ['Waktu', 'User', 'Action', 'Tabel', 'Record ID', 'IP Address', 'Keterangan'],
        'rows'    => array_map(fn($r) => [
            $r['created_at'], $r['user_name'] ?? '—', $r['action'],
            $r['table_name'], $r['record_id'] ?? '—',
            $r['ip_address'] ?? '—', $r['description'] ?? '—',
        ], $stmt->fetchAll() ?: []),
    ];
}

// ─── OUTPUT FORMATS ───────────────────────────────────────────────────────────

function outputPDF(array $data, string $report, string $from, string $to, string $asOf, int $year, bool $tcpdfAvailable): void {
    if ($tcpdfAvailable) {
        outputPDFViaTCPDF($data, $report);
    } else {
        // Fallback: HTML suitable for print / browser Save as PDF
        $filename = "laporan-$report-" . date('Ymd') . '.html';
        header('Content-Type: text/html; charset=utf-8');
        header("Content-Disposition: inline; filename=\"$filename\"");
        echo buildHTMLReport($data);
    }
}

function outputPDFViaTCPDF(array $data, string $report): void {
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('KSP Lam Gabe Jaya v2.0');
    $pdf->SetAuthor('KSP Lam Gabe Jaya');
    $pdf->SetTitle($data['title']);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(10, 10, 10);
    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'KSP LAM GABE JAYA', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8,  $data['title'],    0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6,  $data['subtitle'], 0, 1, 'C');
    $pdf->Ln(4);

    // Header row
    $colWidth = 270 / max(1, count($data['headers']));
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(41, 128, 185);
    $pdf->SetTextColor(255, 255, 255);
    foreach ($data['headers'] as $h) {
        $pdf->Cell($colWidth, 7, $h, 1, 0, 'C', true);
    }
    $pdf->Ln();

    // Data rows
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(0, 0, 0);
    $fill = false;
    foreach ($data['rows'] as $row) {
        $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
        foreach ($row as $cell) {
            $pdf->Cell($colWidth, 6, $cell, 1, 0, 'L', true);
        }
        $pdf->Ln();
        $fill = !$fill;
    }

    $filename = 'laporan-' . $report . '-' . date('Ymd') . '.pdf';
    $pdf->Output($filename, 'D');
}

function outputExcel(array $data, string $report, bool $available): void {
    if (!$available) {
        // Fallback to CSV
        outputCSV($data, $report);
        return;
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle(substr($data['title'], 0, 31));

    // Title
    $sheet->setCellValue('A1', 'KSP LAM GABE JAYA');
    $sheet->setCellValue('A2', $data['title']);
    $sheet->setCellValue('A3', $data['subtitle']);

    // Headers
    $col = 'A'; $row = 5;
    foreach ($data['headers'] as $h) {
        $sheet->setCellValue($col . $row, $h);
        $col++;
    }

    // Data
    $row = 6;
    foreach ($data['rows'] as $dataRow) {
        $col = 'A';
        foreach ($dataRow as $cell) {
            $sheet->setCellValue($col . $row, $cell);
            $col++;
        }
        $row++;
    }

    $filename = 'laporan-' . $report . '-' . date('Ymd') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Cache-Control: max-age=0');
    (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
}

function outputCSV(array $data, string $report): void {
    $filename = 'laporan-' . $report . '-' . date('Ymd') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"$filename\"");

    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel
    fputcsv($out, ['KSP LAM GABE JAYA']);
    fputcsv($out, [$data['title']]);
    fputcsv($out, [$data['subtitle']]);
    fputcsv($out, []);
    fputcsv($out, $data['headers']);
    foreach ($data['rows'] as $row) fputcsv($out, $row);
    fclose($out);
}

function buildHTMLReport(array $data): string {
    $rows = '';
    foreach ($data['rows'] as $r) {
        $cells = implode('', array_map(fn($c) => "<td>$c</td>", $r));
        $rows .= "<tr>$cells</tr>";
    }
    $headers = implode('', array_map(fn($h) => "<th>$h</th>", $data['headers']));
    return <<<HTML
<!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><title>{$data['title']}</title>
<style>
  body{font-family:Arial,sans-serif;font-size:12px;margin:20px}
  h2,h3,p{text-align:center;margin:4px}
  table{width:100%;border-collapse:collapse;margin-top:12px}
  th{background:#2980b9;color:#fff;padding:6px 4px;border:1px solid #ccc}
  td{padding:5px 4px;border:1px solid #ccc}
  tr:nth-child(even){background:#f5f5f5}
  @media print{button{display:none}}
</style>
</head><body>
<h2>KSP LAM GABE JAYA</h2>
<h3>{$data['title']}</h3>
<p>{$data['subtitle']}</p>
<p style="margin-top:8px"><button onclick="window.print()">🖨️ Cetak / Simpan PDF</button></p>
<table><thead><tr>$headers</tr></thead><tbody>$rows</tbody></table>
</body></html>
HTML;
}

// ─── HELPERS ──────────────────────────────────────────────────────────────────

function formatRp($amount): string {
    return 'Rp ' . number_format((float)$amount, 0, ',', '.');
}

function formatDate(string $date): string {
    $months = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    [$y, $m, $d] = explode('-', $date);
    return (int)$d . ' ' . ($months[(int)$m] ?? $m) . ' ' . $y;
}
