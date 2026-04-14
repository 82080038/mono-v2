-- ============================================================
-- KSP Lam Gabe Jaya v2.0 - Phase 2 Migration
-- Akuntansi, Audit Trail, SHU
-- Run: /opt/lampp/bin/mysql -u root -proot ksp_lamgabejaya_v2 < database/phase2_migration.sql
-- ============================================================

USE ksp_lamgabejaya_v2;

-- ─── 1. AUDIT LOGS ────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id`          bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id`     int(11)    DEFAULT NULL,
  `action`      enum('CREATE','UPDATE','DELETE','APPROVE','REJECT','LOGIN','LOGOUT','EXPORT') NOT NULL,
  `table_name`  varchar(100) NOT NULL,
  `record_id`   int(11)    DEFAULT NULL,
  `old_values`  longtext   DEFAULT NULL COMMENT 'JSON sebelum perubahan',
  `new_values`  longtext   DEFAULT NULL COMMENT 'JSON sesudah perubahan',
  `ip_address`  varchar(45) DEFAULT NULL,
  `user_agent`  varchar(500) DEFAULT NULL,
  `description` text       DEFAULT NULL,
  `created_at`  timestamp  NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_user`   (`user_id`),
  KEY `idx_audit_action` (`action`),
  KEY `idx_audit_table`  (`table_name`),
  KEY `idx_audit_date`   (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 2. CHART OF ACCOUNTS (Bagan Akun) ───────────────────────────────────────

CREATE TABLE IF NOT EXISTS `chart_of_accounts` (
  `id`            int(11)     NOT NULL AUTO_INCREMENT,
  `account_code`  varchar(20) NOT NULL UNIQUE,
  `account_name`  varchar(150) NOT NULL,
  `account_type`  enum('asset','liability','equity','revenue','expense') NOT NULL,
  `parent_id`     int(11)    DEFAULT NULL COMMENT 'Untuk akun sub/detail',
  `normal_balance` enum('debit','credit') NOT NULL,
  `is_active`     tinyint(1) NOT NULL DEFAULT 1,
  `notes`         text       DEFAULT NULL,
  `created_at`    timestamp  NOT NULL DEFAULT current_timestamp(),
  `updated_at`    timestamp  NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_coa_code` (`account_code`),
  KEY `idx_coa_type` (`account_type`),
  CONSTRAINT `coa_parent_fk` FOREIGN KEY (`parent_id`) REFERENCES `chart_of_accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed akun dasar KSP
INSERT IGNORE INTO `chart_of_accounts` (`account_code`, `account_name`, `account_type`, `normal_balance`, `notes`) VALUES
-- ASET
('1-000', 'ASET',                         'asset',     'debit',  'Header'),
('1-100', 'Kas',                           'asset',     'debit',  NULL),
('1-110', 'Kas Teller',                    'asset',     'debit',  NULL),
('1-120', 'Kas Besar',                     'asset',     'debit',  NULL),
('1-200', 'Bank',                          'asset',     'debit',  NULL),
('1-210', 'Rekening Bank BRI',             'asset',     'debit',  NULL),
('1-300', 'Piutang',                       'asset',     'debit',  NULL),
('1-310', 'Piutang Pinjaman Konsumtif',    'asset',     'debit',  NULL),
('1-320', 'Piutang Pinjaman Produktif',    'asset',     'debit',  NULL),
('1-330', 'Penyisihan Kerugian Pinjaman',  'asset',     'credit', NULL),
('1-400', 'Aset Tetap',                    'asset',     'debit',  NULL),
('1-410', 'Inventaris & Peralatan',        'asset',     'debit',  NULL),
-- KEWAJIBAN
('2-000', 'KEWAJIBAN',                     'liability', 'credit', 'Header'),
('2-100', 'Simpanan Anggota',              'liability', 'credit', NULL),
('2-110', 'Simpanan Pokok',                'liability', 'credit', NULL),
('2-120', 'Simpanan Wajib',                'liability', 'credit', NULL),
('2-130', 'Simpanan Sukarela',             'liability', 'credit', NULL),
('2-140', 'Simpanan Berjangka',            'liability', 'credit', NULL),
('2-200', 'Hutang Lain-lain',              'liability', 'credit', NULL),
('2-300', 'Dana yang Harus Dibayar',       'liability', 'credit', NULL),
-- EKUITAS
('3-000', 'EKUITAS',                       'equity',    'credit', 'Header'),
('3-100', 'Modal Koperasi',                'equity',    'credit', NULL),
('3-200', 'Cadangan Umum',                 'equity',    'credit', NULL),
('3-300', 'SHU Belum Dibagi',              'equity',    'credit', NULL),
-- PENDAPATAN
('4-000', 'PENDAPATAN',                    'revenue',   'credit', 'Header'),
('4-100', 'Jasa Pinjaman',                 'revenue',   'credit', NULL),
('4-110', 'Jasa Pinjaman Konsumtif',       'revenue',   'credit', NULL),
('4-120', 'Jasa Pinjaman Produktif',       'revenue',   'credit', NULL),
('4-200', 'Biaya Administrasi Pinjaman',   'revenue',   'credit', NULL),
('4-300', 'Denda Keterlambatan',           'revenue',   'credit', NULL),
('4-400', 'Pendapatan Lain-lain',          'revenue',   'credit', NULL),
-- BEBAN
('5-000', 'BEBAN',                         'expense',   'debit',  'Header'),
('5-100', 'Beban Gaji & Tunjangan',        'expense',   'debit',  NULL),
('5-200', 'Beban Operasional Kantor',      'expense',   'debit',  NULL),
('5-300', 'Beban Bunga Simpanan',          'expense',   'debit',  NULL),
('5-400', 'Beban Penyusutan',              'expense',   'debit',  NULL),
('5-500', 'Beban Lain-lain',               'expense',   'debit',  NULL);

-- Update parent_id untuk sub-akun
UPDATE `chart_of_accounts` SET `parent_id` = (SELECT id FROM (SELECT id FROM `chart_of_accounts` WHERE account_code='1-000') t) WHERE account_code IN ('1-100','1-200','1-300','1-400');
UPDATE `chart_of_accounts` SET `parent_id` = (SELECT id FROM (SELECT id FROM `chart_of_accounts` WHERE account_code='1-100') t) WHERE account_code IN ('1-110','1-120');
UPDATE `chart_of_accounts` SET `parent_id` = (SELECT id FROM (SELECT id FROM `chart_of_accounts` WHERE account_code='1-200') t) WHERE account_code IN ('1-210');
UPDATE `chart_of_accounts` SET `parent_id` = (SELECT id FROM (SELECT id FROM `chart_of_accounts` WHERE account_code='1-300') t) WHERE account_code IN ('1-310','1-320','1-330');
UPDATE `chart_of_accounts` SET `parent_id` = (SELECT id FROM (SELECT id FROM `chart_of_accounts` WHERE account_code='2-000') t) WHERE account_code IN ('2-100','2-200','2-300');
UPDATE `chart_of_accounts` SET `parent_id` = (SELECT id FROM (SELECT id FROM `chart_of_accounts` WHERE account_code='2-100') t) WHERE account_code IN ('2-110','2-120','2-130','2-140');
UPDATE `chart_of_accounts` SET `parent_id` = (SELECT id FROM (SELECT id FROM `chart_of_accounts` WHERE account_code='3-000') t) WHERE account_code IN ('3-100','3-200','3-300');
UPDATE `chart_of_accounts` SET `parent_id` = (SELECT id FROM (SELECT id FROM `chart_of_accounts` WHERE account_code='4-000') t) WHERE account_code IN ('4-100','4-200','4-300','4-400');
UPDATE `chart_of_accounts` SET `parent_id` = (SELECT id FROM (SELECT id FROM `chart_of_accounts` WHERE account_code='4-100') t) WHERE account_code IN ('4-110','4-120');
UPDATE `chart_of_accounts` SET `parent_id` = (SELECT id FROM (SELECT id FROM `chart_of_accounts` WHERE account_code='5-000') t) WHERE account_code IN ('5-100','5-200','5-300','5-400','5-500');

-- ─── 3. JOURNAL ENTRIES (Jurnal Umum) ─────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `journal_entries` (
  `id`             int(11)      NOT NULL AUTO_INCREMENT,
  `journal_number` varchar(30)  NOT NULL UNIQUE COMMENT 'Format: JRN-YYYYMM-NNNN',
  `entry_date`     date         NOT NULL,
  `description`    text         NOT NULL,
  `reference_type` varchar(50)  DEFAULT NULL COMMENT 'loans, savings, manual, shu',
  `reference_id`   int(11)      DEFAULT NULL,
  `status`         enum('draft','posted','reversed') NOT NULL DEFAULT 'posted',
  `created_by`     int(11)      DEFAULT NULL,
  `posted_by`      int(11)      DEFAULT NULL,
  `posted_at`      timestamp    NULL DEFAULT NULL,
  `reversed_by`    int(11)      DEFAULT NULL,
  `reversed_at`    timestamp    NULL DEFAULT NULL,
  `reversal_note`  text         DEFAULT NULL,
  `created_at`     timestamp    NOT NULL DEFAULT current_timestamp(),
  `updated_at`     timestamp    NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_journal_date`   (`entry_date`),
  KEY `idx_journal_status` (`status`),
  KEY `idx_journal_ref`    (`reference_type`, `reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 4. JOURNAL ENTRY LINES (Detail Jurnal Debit/Kredit) ──────────────────────

CREATE TABLE IF NOT EXISTS `journal_entry_lines` (
  `id`                  int(11)       NOT NULL AUTO_INCREMENT,
  `journal_entry_id`    int(11)       NOT NULL,
  `account_id`          int(11)       NOT NULL,
  `debit_amount`        decimal(15,2) NOT NULL DEFAULT 0.00,
  `credit_amount`       decimal(15,2) NOT NULL DEFAULT 0.00,
  `description`         varchar(255)  DEFAULT NULL,
  `line_order`          int(11)       NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_jel_journal` (`journal_entry_id`),
  KEY `idx_jel_account` (`account_id`),
  CONSTRAINT `jel_journal_fk` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jel_account_fk` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 5. SHU PERIODS ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `shu_periods` (
  `id`                  int(11)       NOT NULL AUTO_INCREMENT,
  `period_year`         year(4)       NOT NULL,
  `total_revenue`       decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_expense`       decimal(15,2) NOT NULL DEFAULT 0.00,
  `gross_shu`           decimal(15,2) NOT NULL DEFAULT 0.00,
  `pct_member_savings`  decimal(5,2)  NOT NULL DEFAULT 30.00 COMMENT '% dari SHU untuk jasa simpanan',
  `pct_member_loans`    decimal(5,2)  NOT NULL DEFAULT 30.00 COMMENT '% dari SHU untuk jasa pinjaman',
  `pct_management`      decimal(5,2)  NOT NULL DEFAULT 10.00,
  `pct_education`       decimal(5,2)  NOT NULL DEFAULT 5.00,
  `pct_social`          decimal(5,2)  NOT NULL DEFAULT 5.00,
  `pct_reserve`         decimal(5,2)  NOT NULL DEFAULT 20.00,
  `status`              enum('draft','final') NOT NULL DEFAULT 'draft',
  `finalized_by`        int(11)       DEFAULT NULL,
  `finalized_at`        timestamp     NULL DEFAULT NULL,
  `notes`               text          DEFAULT NULL,
  `created_by`          int(11)       DEFAULT NULL,
  `created_at`          timestamp     NOT NULL DEFAULT current_timestamp(),
  `updated_at`          timestamp     NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_shu_year` (`period_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 6. SHU DISTRIBUTIONS ─────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `shu_distributions` (
  `id`               int(11)       NOT NULL AUTO_INCREMENT,
  `shu_period_id`    int(11)       NOT NULL,
  `member_id`        int(11)       NOT NULL,
  `savings_balance`  decimal(15,2) NOT NULL DEFAULT 0.00,
  `loan_principal`   decimal(15,2) NOT NULL DEFAULT 0.00,
  `savings_share`    decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Bagian SHU dari simpanan',
  `loan_share`       decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Bagian SHU dari pinjaman',
  `total_share`      decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_distributed`   tinyint(1)    NOT NULL DEFAULT 0,
  `distributed_at`   timestamp     NULL DEFAULT NULL,
  `distributed_by`   int(11)       DEFAULT NULL,
  `notes`            text          DEFAULT NULL,
  `created_at`       timestamp     NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_shu_member_period` (`shu_period_id`, `member_id`),
  KEY `idx_shu_dist_member` (`member_id`),
  CONSTRAINT `shu_dist_period_fk` FOREIGN KEY (`shu_period_id`) REFERENCES `shu_periods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 7. APPROVAL WORKFLOWS ────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `approval_workflows` (
  `id`              int(11)     NOT NULL AUTO_INCREMENT,
  `entity_type`     varchar(50) NOT NULL COMMENT 'loan, withdrawal, journal_entry, shu',
  `entity_id`       int(11)     NOT NULL,
  `level`           tinyint(4)  NOT NULL DEFAULT 1,
  `required_role`   varchar(50) NOT NULL,
  `status`          enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `actioned_by`     int(11)     DEFAULT NULL,
  `actioned_at`     timestamp   NULL DEFAULT NULL,
  `note`            text        DEFAULT NULL,
  `created_by`      int(11)     DEFAULT NULL,
  `created_at`      timestamp   NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_approval_entity` (`entity_type`, `entity_id`),
  KEY `idx_approval_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Done
SELECT 'Phase 2 migration completed.' AS status;
