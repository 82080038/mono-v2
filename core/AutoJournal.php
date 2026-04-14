<?php
/**
 * AutoJournal — Phase 3
 * Otomatis membuat jurnal akuntansi (double-entry) setiap ada transaksi
 * simpanan (deposit/withdrawal) dan pinjaman (disbursement/payment).
 *
 * Dipanggil dari savings.php dan loans.php setelah $db->commit().
 * Setiap error di sini di-log dan TIDAK menggagalkan transaksi utama.
 */

require_once __DIR__ . '/../config/Config.php';

class AutoJournal
{
    /** Mapping nama account_type → account_code COA */
    private const SAVINGS_COA_MAP = [
        'simpanan pokok'      => '2-110',
        'simpanan wajib'      => '2-120',
        'simpanan sukarela'   => '2-130',
        'simpanan berjangka'  => '2-140',
        'simpanan hari raya'  => '2-130', // fallback ke sukarela
    ];

    /** Mapping kata kunci tipe pinjaman → COA piutang & jasa */
    private const LOAN_COA_MAP = [
        'konsumtif' => ['piutang' => '1-310', 'jasa' => '4-110'],
        'produktif'  => ['piutang' => '1-320', 'jasa' => '4-120'],
    ];

    private const KAS_TELLER_CODE = '1-110';

    // ─── Public API ──────────────────────────────────────────────────────────

    /**
     * Jurnal untuk setoran simpanan.
     * DR Kas Teller | CR Simpanan [type]
     *
     * @param int    $accountId      ID rekening simpanan
     * @param float  $amount         Nominal setoran
     * @param string $refNumber      Nomor referensi transaksi
     * @param string $description    Keterangan
     * @param int    $accountTypeId  ID tipe rekening
     * @param int|null $userId
     */
    public static function journalDeposit(
        int $accountId, float $amount, string $refNumber,
        string $description, int $accountTypeId, ?int $userId = null
    ): void {
        try {
            $pdo = Config::getDatabase();
            $savingsCode = self::resolveSavingsCode($pdo, $accountTypeId);
            $kasId       = self::getAccountId($pdo, self::KAS_TELLER_CODE);
            $savingsId   = self::getAccountId($pdo, $savingsCode);
            if (!$kasId || !$savingsId) return;

            $journalId = self::insertJournal($pdo, $refNumber, $description,
                'account_transaction', $accountId, $userId);
            self::insertLine($pdo, $journalId, $kasId,     $amount, 0,      'Penerimaan kas setoran');
            self::insertLine($pdo, $journalId, $savingsId, 0,      $amount, 'Simpanan anggota');
        } catch (Throwable $e) {
            error_log('[AutoJournal::journalDeposit] ' . $e->getMessage());
        }
    }

    /**
     * Jurnal untuk penarikan simpanan.
     * DR Simpanan [type] | CR Kas Teller
     *
     * @param int    $accountId
     * @param float  $amount         Nominal penarikan (termasuk fee)
     * @param string $refNumber
     * @param string $description
     * @param int    $accountTypeId
     * @param int|null $userId
     */
    public static function journalWithdrawal(
        int $accountId, float $amount, string $refNumber,
        string $description, int $accountTypeId, ?int $userId = null
    ): void {
        try {
            $pdo = Config::getDatabase();
            $savingsCode = self::resolveSavingsCode($pdo, $accountTypeId);
            $kasId       = self::getAccountId($pdo, self::KAS_TELLER_CODE);
            $savingsId   = self::getAccountId($pdo, $savingsCode);
            if (!$kasId || !$savingsId) return;

            $journalId = self::insertJournal($pdo, $refNumber, $description,
                'account_transaction', $accountId, $userId);
            self::insertLine($pdo, $journalId, $savingsId, $amount, 0,      'Pengurangan simpanan anggota');
            self::insertLine($pdo, $journalId, $kasId,     0,      $amount, 'Pengeluaran kas penarikan');
        } catch (Throwable $e) {
            error_log('[AutoJournal::journalWithdrawal] ' . $e->getMessage());
        }
    }

    /**
     * Jurnal untuk pencairan pinjaman.
     * DR Piutang Pinjaman [type] | CR Kas Teller
     *
     * @param int    $loanId
     * @param float  $amount         Pokok pinjaman yang dicairkan
     * @param string $refNumber
     * @param string $description
     * @param string $loanTypeName   Nama tipe pinjaman (konsumtif/produktif/...)
     * @param int|null $userId
     */
    public static function journalDisbursement(
        int $loanId, float $amount, string $refNumber,
        string $description, string $loanTypeName, ?int $userId = null
    ): void {
        try {
            $pdo = Config::getDatabase();
            [$piutangCode, ] = self::resolveLoanCodes($loanTypeName);
            $piutangId = self::getAccountId($pdo, $piutangCode);
            $kasId     = self::getAccountId($pdo, self::KAS_TELLER_CODE);
            if (!$piutangId || !$kasId) return;

            $journalId = self::insertJournal($pdo, $refNumber, $description,
                'loan', $loanId, $userId);
            self::insertLine($pdo, $journalId, $piutangId, $amount, 0,      'Pencairan pokok pinjaman');
            self::insertLine($pdo, $journalId, $kasId,     0,      $amount, 'Pengeluaran kas pencairan');
        } catch (Throwable $e) {
            error_log('[AutoJournal::journalDisbursement] ' . $e->getMessage());
        }
    }

    /**
     * Jurnal untuk pembayaran angsuran pinjaman.
     * DR Kas Teller | CR Piutang Pinjaman [type] (pokok) + CR Jasa Pinjaman (bunga)
     *
     * @param int    $loanId
     * @param float  $totalPayment
     * @param float  $principal       Porsi pokok
     * @param float  $interest        Porsi bunga/jasa
     * @param string $refNumber
     * @param string $description
     * @param string $loanTypeName
     * @param int|null $userId
     */
    public static function journalPayment(
        int $loanId, float $totalPayment, float $principal, float $interest,
        string $refNumber, string $description, string $loanTypeName, ?int $userId = null
    ): void {
        try {
            $pdo = Config::getDatabase();
            [$piutangCode, $jasaCode] = self::resolveLoanCodes($loanTypeName);
            $kasId      = self::getAccountId($pdo, self::KAS_TELLER_CODE);
            $piutangId  = self::getAccountId($pdo, $piutangCode);
            $jasaId     = self::getAccountId($pdo, $jasaCode);
            if (!$kasId || !$piutangId || !$jasaId) return;

            $journalId = self::insertJournal($pdo, $refNumber, $description,
                'loan', $loanId, $userId);
            self::insertLine($pdo, $journalId, $kasId,     $totalPayment, 0,         'Penerimaan angsuran');
            if ($principal > 0)
                self::insertLine($pdo, $journalId, $piutangId, 0, $principal, 'Pelunasan pokok pinjaman');
            if ($interest > 0)
                self::insertLine($pdo, $journalId, $jasaId,    0, $interest,  'Pendapatan jasa pinjaman');
        } catch (Throwable $e) {
            error_log('[AutoJournal::journalPayment] ' . $e->getMessage());
        }
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    private static function getAccountId(PDO $pdo, string $code): ?int {
        static $cache = [];
        if (isset($cache[$code])) return $cache[$code];
        $stmt = $pdo->prepare("SELECT id FROM chart_of_accounts WHERE account_code=? AND is_active=1 LIMIT 1");
        $stmt->execute([$code]);
        $id = $stmt->fetchColumn() ?: null;
        $cache[$code] = $id ? (int)$id : null;
        return $cache[$code];
    }

    private static function resolveSavingsCode(PDO $pdo, int $accountTypeId): string {
        $stmt = $pdo->prepare("SELECT name FROM account_types WHERE id=? LIMIT 1");
        $stmt->execute([$accountTypeId]);
        $name = strtolower(trim($stmt->fetchColumn() ?: ''));
        return self::SAVINGS_COA_MAP[$name] ?? '2-130'; // default: sukarela
    }

    /** @return array{0:string,1:string} [piutangCode, jasaCode] */
    private static function resolveLoanCodes(string $loanTypeName): array {
        $lower = strtolower($loanTypeName);
        foreach (self::LOAN_COA_MAP as $key => $codes) {
            if (str_contains($lower, $key)) {
                return [$codes['piutang'], $codes['jasa']];
            }
        }
        return ['1-310', '4-110']; // default: konsumtif
    }

    private static function generateJournalNumber(PDO $pdo): string {
        $prefix = 'AJ-' . date('Ymd');
        $stmt = $pdo->query("SELECT COUNT(*) FROM journal_entries WHERE journal_number LIKE '$prefix%'");
        $seq = ((int)$stmt->fetchColumn()) + 1;
        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    private static function insertJournal(
        PDO $pdo, string $ref, string $description,
        string $refType, int $refId, ?int $userId
    ): int {
        $jn = self::generateJournalNumber($pdo);
        $stmt = $pdo->prepare("
            INSERT INTO journal_entries
                (journal_number, entry_date, description, reference_type, reference_id,
                 status, created_by, posted_by, posted_at)
            VALUES (?, CURDATE(), ?, ?, ?, 'posted', ?, ?, NOW())
        ");
        $stmt->execute([$jn, $description . ' [Ref: ' . $ref . ']', $refType, $refId, $userId, $userId]);
        return (int)$pdo->lastInsertId();
    }

    /** line_order counter per journal — simple static map */
    private static array $lineOrder = [];

    private static function insertLine(
        PDO $pdo, int $journalId, int $accountId,
        float $debit, float $credit, string $desc
    ): void {
        if (!isset(self::$lineOrder[$journalId])) self::$lineOrder[$journalId] = 0;
        self::$lineOrder[$journalId]++;
        $pdo->prepare("
            INSERT INTO journal_entry_lines
                (journal_entry_id, account_id, debit_amount, credit_amount, description, line_order)
            VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([$journalId, $accountId, $debit, $credit, $desc, self::$lineOrder[$journalId]]);
    }
}
