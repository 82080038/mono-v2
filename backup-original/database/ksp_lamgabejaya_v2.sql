-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 19 Mar 2026 pada 10.11
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ksp_lamgabejaya_v2`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `account_number` varchar(20) NOT NULL,
  `member_id` int(11) NOT NULL,
  `account_type_id` int(11) NOT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `available_balance` decimal(15,2) DEFAULT 0.00,
  `hold_amount` decimal(15,2) DEFAULT 0.00,
  `interest_rate` decimal(5,4) DEFAULT NULL,
  `opening_date` date NOT NULL,
  `maturity_date` date DEFAULT NULL,
  `last_interest_date` date DEFAULT NULL,
  `status` enum('Active','Dormant','Frozen','Closed') DEFAULT 'Active',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_transactions`
--

CREATE TABLE `account_transactions` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `transaction_type` enum('Deposit','Withdrawal','Transfer In','Transfer Out','Interest','Fee','Tax') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `balance_before` decimal(15,2) NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `teller_id` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `is_reversed` tinyint(1) DEFAULT 0,
  `reversed_by` int(11) DEFAULT NULL,
  `reversed_date` timestamp NULL DEFAULT NULL,
  `reversal_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Trigger `account_transactions`
--
DELIMITER $$
CREATE TRIGGER `update_account_balance` AFTER INSERT ON `account_transactions` FOR EACH ROW BEGIN
    UPDATE accounts 
    SET balance = NEW.balance_after,
        available_balance = CASE 
            WHEN NEW.transaction_type IN ('Withdrawal', 'Transfer Out') 
            THEN balance - hold_amount 
            ELSE available_balance 
        END,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = NEW.account_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_types`
--

CREATE TABLE `account_types` (
  `id` int(11) NOT NULL,
  `code` varchar(15) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `interest_rate` decimal(5,4) DEFAULT 0.0000,
  `minimum_balance` decimal(15,2) DEFAULT 0.00,
  `minimum_deposit` decimal(15,2) DEFAULT 0.00,
  `maximum_deposit` decimal(15,2) DEFAULT 0.00,
  `withdrawal_fee` decimal(15,2) DEFAULT 0.00,
  `is_taxable` tinyint(1) DEFAULT 0,
  `tax_rate` decimal(5,4) DEFAULT 0.0000,
  `requires_approval` tinyint(1) DEFAULT 0,
  `auto_debit_enabled` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `account_types`
--

INSERT INTO `account_types` (`id`, `code`, `name`, `description`, `interest_rate`, `minimum_balance`, `minimum_deposit`, `maximum_deposit`, `withdrawal_fee`, `is_taxable`, `tax_rate`, `requires_approval`, `auto_debit_enabled`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'SA_POKOK', 'Simpanan Pokok', 'Simpanan wajib satu kali', 0.0000, 0.00, 100000.00, 0.00, 0.00, 0, 0.0000, 0, 0, 1, '2026-03-19 08:48:08', '2026-03-19 08:48:08'),
(2, 'SA_WAJIB', 'Simpanan Wajib', 'Simpanan wajib bulanan', 0.0020, 0.00, 50000.00, 0.00, 0.00, 0, 0.0000, 0, 1, 1, '2026-03-19 08:48:08', '2026-03-19 08:48:08'),
(3, 'SA_SUKARELA', 'Simpanan Sukarela', 'Simpanan fleksibel', 0.0030, 10000.00, 10000.00, 0.00, 0.00, 0, 0.0000, 0, 0, 1, '2026-03-19 08:48:08', '2026-03-19 08:48:08'),
(4, 'SA_BERJANGKA', 'Simpanan Berjangka', 'Simpanan dengan tenor tetap', 0.0040, 100000.00, 100000.00, 0.00, 0.00, 0, 0.0000, 0, 0, 1, '2026-03-19 08:48:08', '2026-03-19 08:48:08'),
(5, 'SA_HARI_RAYA', 'Simpanan Hari Raya', 'Simpanan untuk hari raya', 0.0030, 50000.00, 50000.00, 0.00, 0.00, 0, 0.0000, 0, 0, 1, '2026-03-19 08:48:08', '2026-03-19 08:48:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `auto_debit_config`
--

CREATE TABLE `auto_debit_config` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `debit_amount` decimal(15,2) NOT NULL,
  `debit_day` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_debit_date` date DEFAULT NULL,
  `next_debit_date` date DEFAULT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `max_failed_attempts` int(11) DEFAULT 3,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `collateral`
--

CREATE TABLE `collateral` (
  `id` int(11) NOT NULL,
  `loan_id` int(11) DEFAULT NULL,
  `member_id` int(11) NOT NULL,
  `collateral_type` enum('Property','Vehicle','Savings','Guarantor','Other') NOT NULL,
  `description` text NOT NULL,
  `estimated_value` decimal(15,2) DEFAULT NULL,
  `appraisal_value` decimal(15,2) DEFAULT NULL,
  `appraisal_date` date DEFAULT NULL,
  `location` text DEFAULT NULL,
  `documents` text DEFAULT NULL,
  `status` enum('Active','Released','Sold','Expired') DEFAULT 'Active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `credit_scoring_criteria`
--

CREATE TABLE `credit_scoring_criteria` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `weight` decimal(5,3) NOT NULL,
  `max_score` decimal(5,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `credit_scoring_criteria`
--

INSERT INTO `credit_scoring_criteria` (`id`, `name`, `description`, `weight`, `max_score`, `is_active`, `created_at`) VALUES
(1, 'Membership Duration', 'Lama keanggotaan', 0.150, 100.00, 1, '2026-03-19 08:48:12'),
(2, 'Savings History', 'Riwayat simpanan', 0.200, 100.00, 1, '2026-03-19 08:48:12'),
(3, 'Previous Loans', 'Riwayat pinjaman sebelumnya', 0.250, 100.00, 1, '2026-03-19 08:48:12'),
(4, 'Income Stability', 'Stabilitas pendapatan', 0.200, 100.00, 1, '2026-03-19 08:48:12'),
(5, 'Collateral Value', 'Nilai jaminan', 0.200, 100.00, 1, '2026-03-19 08:48:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `credit_scoring_details`
--

CREATE TABLE `credit_scoring_details` (
  `id` int(11) NOT NULL,
  `scoring_result_id` int(11) NOT NULL,
  `criteria_id` int(11) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `credit_scoring_results`
--

CREATE TABLE `credit_scoring_results` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `loan_application_id` int(11) DEFAULT NULL,
  `total_score` decimal(5,2) NOT NULL,
  `risk_level` enum('Low','Medium','High','Very High') NOT NULL,
  `recommendation` enum('Approve','Reject','Manual Review') NOT NULL,
  `scoring_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `scored_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `daily_transactions`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `daily_transactions` (
`transaction_date` date
,`total_transactions` bigint(21)
,`total_deposits` decimal(37,2)
,`total_withdrawals` decimal(37,2)
,`active_accounts` bigint(21)
);

-- --------------------------------------------------------

--
-- Struktur dari tabel `loans`
--

CREATE TABLE `loans` (
  `id` int(11) NOT NULL,
  `loan_number` varchar(20) NOT NULL,
  `member_id` int(11) NOT NULL,
  `loan_type_id` int(11) NOT NULL,
  `application_date` date NOT NULL,
  `approval_date` date DEFAULT NULL,
  `disbursement_date` date DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `interest_rate` decimal(5,4) NOT NULL,
  `admin_fee` decimal(15,2) DEFAULT 0.00,
  `insurance_fee` decimal(15,2) DEFAULT 0.00,
  `term_months` int(11) NOT NULL,
  `calculation_method` enum('Flat','Effective','Anuitas') NOT NULL,
  `monthly_installment` decimal(15,2) NOT NULL,
  `total_interest` decimal(15,2) NOT NULL,
  `total_payment` decimal(15,2) NOT NULL,
  `outstanding_balance` decimal(15,2) NOT NULL,
  `next_payment_date` date DEFAULT NULL,
  `maturity_date` date DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `status` enum('Applied','Approved','Rejected','Disbursed','Active','Late','Default','Restructured','Paid Off') DEFAULT 'Applied',
  `rejection_reason` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `loan_installments`
--

CREATE TABLE `loan_installments` (
  `id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `installment_number` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `principal_amount` decimal(15,2) NOT NULL,
  `interest_amount` decimal(15,2) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `paid_date` date DEFAULT NULL,
  `late_fee` decimal(15,2) DEFAULT 0.00,
  `status` enum('Pending','Paid','Late','Overdue') DEFAULT 'Pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `loan_payments`
--

CREATE TABLE `loan_payments` (
  `id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `payment_amount` decimal(15,2) NOT NULL,
  `principal_portion` decimal(15,2) NOT NULL,
  `interest_portion` decimal(15,2) NOT NULL,
  `late_fee_portion` decimal(15,2) DEFAULT 0.00,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` enum('Cash','Bank Transfer','Auto Debit','Digital Payment') NOT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `teller_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `loan_portfolio`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `loan_portfolio` (
`loan_type` varchar(50)
,`total_loans` bigint(21)
,`total_disbursed` decimal(37,2)
,`total_outstanding` decimal(37,2)
,`avg_interest_rate` decimal(9,8)
,`late_loans` bigint(21)
,`default_loans` bigint(21)
);

-- --------------------------------------------------------

--
-- Struktur dari tabel `loan_types`
--

CREATE TABLE `loan_types` (
  `id` int(11) NOT NULL,
  `code` varchar(15) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `interest_rate` decimal(5,4) NOT NULL,
  `admin_fee_rate` decimal(5,4) DEFAULT 0.0000,
  `late_fee_rate` decimal(5,4) DEFAULT 0.0000,
  `minimum_amount` decimal(15,2) DEFAULT 0.00,
  `maximum_amount` decimal(15,2) DEFAULT 0.00,
  `minimum_term_months` int(11) DEFAULT 1,
  `maximum_term_months` int(11) DEFAULT 60,
  `collateral_required` tinyint(1) DEFAULT 0,
  `guarantee_required` tinyint(1) DEFAULT 0,
  `insurance_required` tinyint(1) DEFAULT 0,
  `calculation_method` enum('Flat','Effective','Anuitas') DEFAULT 'Flat',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `loan_types`
--

INSERT INTO `loan_types` (`id`, `code`, `name`, `description`, `interest_rate`, `admin_fee_rate`, `late_fee_rate`, `minimum_amount`, `maximum_amount`, `minimum_term_months`, `maximum_term_months`, `collateral_required`, `guarantee_required`, `insurance_required`, `calculation_method`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'KONSUMTIF', 'Pinjaman Konsumtif', 'Pinjaman untuk kebutuhan konsumtif', 0.0150, 0.0100, 0.0000, 500000.00, 5000000.00, 1, 60, 1, 0, 0, 'Flat', 1, '2026-03-19 08:48:11', '2026-03-19 08:48:11'),
(2, 'PRODUKTIF', 'Pinjaman Produktif', 'Pinjaman untuk usaha produktif', 0.0120, 0.0100, 0.0000, 1000000.00, 20000000.00, 1, 60, 1, 0, 0, 'Flat', 1, '2026-03-19 08:48:11', '2026-03-19 08:48:11'),
(3, 'DARURAT', 'Pinjaman Darurat', 'Pinjaman darurat cepat cair', 0.0200, 0.0200, 0.0000, 250000.00, 2000000.00, 1, 60, 0, 0, 0, 'Flat', 1, '2026-03-19 08:48:11', '2026-03-19 08:48:11'),
(4, 'ANGSURAN', 'Pinjaman Angsuran', 'Pinjaman dengan angsuran tetap', 0.0130, 0.0100, 0.0000, 500000.00, 10000000.00, 1, 60, 1, 0, 0, 'Flat', 1, '2026-03-19 08:48:11', '2026-03-19 08:48:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `member_number` varchar(20) NOT NULL,
  `member_type_id` int(11) NOT NULL,
  `title` varchar(10) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `place_of_birth` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('L','P') NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `family_card_number` varchar(50) DEFAULT NULL,
  `tax_id_number` varchar(30) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text NOT NULL,
  `village` varchar(50) DEFAULT NULL,
  `district` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `monthly_income` decimal(15,2) DEFAULT NULL,
  `marital_status` enum('Single','Married','Divorced','Widowed') DEFAULT NULL,
  `spouse_name` varchar(100) DEFAULT NULL,
  `spouse_phone` varchar(20) DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `emergency_contact_relation` varchar(50) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `signature_path` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive','Suspended','Blacklisted','Resigned','Deceased') DEFAULT 'Active',
  `registration_date` date NOT NULL,
  `activation_date` date DEFAULT NULL,
  `deactivation_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `member_documents`
--

CREATE TABLE `member_documents` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `document_type` enum('KTP','KK','NPWP','Slip Gaji','Surat Nikah','Other') NOT NULL,
  `document_number` varchar(50) DEFAULT NULL,
  `document_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry_date` date DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verified_by` int(11) DEFAULT NULL,
  `verified_date` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `member_status_history`
--

CREATE TABLE `member_status_history` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `old_status` enum('Active','Inactive','Suspended','Blacklisted','Resigned','Deceased') DEFAULT NULL,
  `new_status` enum('Active','Inactive','Suspended','Blacklisted','Resigned','Deceased') NOT NULL,
  `reason` text DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `member_summary`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `member_summary` (
`id` int(11)
,`member_number` varchar(20)
,`full_name` varchar(100)
,`member_type` varchar(50)
,`phone_number` varchar(20)
,`email` varchar(100)
,`status` enum('Active','Inactive','Suspended','Blacklisted','Resigned','Deceased')
,`registration_date` date
,`total_accounts` bigint(21)
,`total_savings` decimal(37,2)
,`total_loans` bigint(21)
,`total_outstanding` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Struktur dari tabel `member_types`
--

CREATE TABLE `member_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `min_savings_pokok` decimal(15,2) DEFAULT 0.00,
  `min_savings_wajib` decimal(15,2) DEFAULT 0.00,
  `max_loan_amount` decimal(15,2) DEFAULT 0.00,
  `max_concurrent_loans` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `member_types`
--

INSERT INTO `member_types` (`id`, `name`, `description`, `min_savings_pokok`, `min_savings_wajib`, `max_loan_amount`, `max_concurrent_loans`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Regular', 'Anggota Biasa', 100000.00, 50000.00, 5000000.00, 1, 1, '2026-03-19 08:48:07', '2026-03-19 08:48:07'),
(2, 'Premium', 'Anggota Prioritas', 250000.00, 100000.00, 10000000.00, 1, 1, '2026-03-19 08:48:07', '2026-03-19 08:48:07'),
(3, 'Board', 'Pengurus Koperasi', 500000.00, 200000.00, 20000000.00, 1, 1, '2026-03-19 08:48:07', '2026-03-19 08:48:07'),
(4, 'Honorary', 'Anggota Kehormatan', 0.00, 0.00, 0.00, 1, 1, '2026-03-19 08:48:07', '2026-03-19 08:48:07'),
(5, 'Associate', 'Anggota Associate', 50000.00, 25000.00, 2500000.00, 1, 1, '2026-03-19 08:48:07', '2026-03-19 08:48:07');

-- --------------------------------------------------------

--
-- Struktur dari tabel `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `data_type` enum('string','number','boolean','json') DEFAULT 'string',
  `is_editable` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `data_type`, `is_editable`, `created_at`, `updated_at`) VALUES
(1, 'company_name', 'KSP Lam Gabe Jaya', 'Nama perusahaan', 'string', 1, '2026-03-19 08:48:16', '2026-03-19 08:48:16'),
(2, 'company_address', 'Jl. Contoh No. 123, Jakarta', 'Alamat perusahaan', 'string', 1, '2026-03-19 08:48:16', '2026-03-19 08:48:16'),
(3, 'company_phone', '+62-21-1234567', 'Nomor telepon', 'string', 1, '2026-03-19 08:48:16', '2026-03-19 08:48:16'),
(4, 'company_email', 'info@ksp-lamgabejaya.co.id', 'Email perusahaan', 'string', 1, '2026-03-19 08:48:16', '2026-03-19 08:48:16'),
(5, 'interest_savings_rate', '0.003', 'Suku bunga simpanan default', 'number', 1, '2026-03-19 08:48:16', '2026-03-19 08:48:16'),
(6, 'late_fee_rate', '0.001', 'Denda keterlambatan', 'number', 1, '2026-03-19 08:48:16', '2026-03-19 08:48:16'),
(7, 'min_credit_score', '50', 'Skor kredit minimum', 'number', 1, '2026-03-19 08:48:16', '2026-03-19 08:48:16'),
(8, 'max_loan_amount', '50000000', 'Maksimal pinjaman', 'number', 1, '2026-03-19 08:48:16', '2026-03-19 08:48:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('Super Admin','Admin','Manager','Teller','Staff') NOT NULL,
  `permissions` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `role`, `permissions`, `is_active`, `last_login`, `failed_login_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@ksp-lamgabejaya.co.id', 'Administrator', 'Super Admin', NULL, 1, NULL, 0, NULL, '2026-03-19 08:48:16', '2026-03-19 08:48:16'),
(2, 'teller1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teller1@ksp-lamgabejaya.co.id', 'Teller Satu', 'Teller', NULL, 1, NULL, 0, NULL, '2026-03-19 08:48:16', '2026-03-19 08:48:16'),
(3, 'manager1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager1@ksp-lamgabejaya.co.id', 'Manager Satu', 'Manager', NULL, 1, NULL, 0, NULL, '2026-03-19 08:48:16', '2026-03-19 08:48:16');

-- --------------------------------------------------------

--
-- Struktur untuk view `daily_transactions`
--
DROP TABLE IF EXISTS `daily_transactions`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `daily_transactions`  AS SELECT cast(`at`.`transaction_date` as date) AS `transaction_date`, count(0) AS `total_transactions`, sum(case when `at`.`transaction_type` in ('Deposit','Transfer In') then `at`.`amount` else 0 end) AS `total_deposits`, sum(case when `at`.`transaction_type` in ('Withdrawal','Transfer Out') then `at`.`amount` else 0 end) AS `total_withdrawals`, count(distinct `at`.`account_id`) AS `active_accounts` FROM `account_transactions` AS `at` GROUP BY cast(`at`.`transaction_date` as date) ORDER BY cast(`at`.`transaction_date` as date) DESC ;

-- --------------------------------------------------------

--
-- Struktur untuk view `loan_portfolio`
--
DROP TABLE IF EXISTS `loan_portfolio`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `loan_portfolio`  AS SELECT `lt`.`name` AS `loan_type`, count(`l`.`id`) AS `total_loans`, sum(`l`.`amount`) AS `total_disbursed`, sum(`l`.`outstanding_balance`) AS `total_outstanding`, avg(`l`.`interest_rate`) AS `avg_interest_rate`, count(case when `l`.`status` = 'Late' then 1 end) AS `late_loans`, count(case when `l`.`status` = 'Default' then 1 end) AS `default_loans` FROM (`loans` `l` left join `loan_types` `lt` on(`l`.`loan_type_id` = `lt`.`id`)) WHERE `l`.`status` in ('Active','Late','Default') GROUP BY `lt`.`id`, `lt`.`name` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `member_summary`
--
DROP TABLE IF EXISTS `member_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `member_summary`  AS SELECT `m`.`id` AS `id`, `m`.`member_number` AS `member_number`, `m`.`full_name` AS `full_name`, `mt`.`name` AS `member_type`, `m`.`phone_number` AS `phone_number`, `m`.`email` AS `email`, `m`.`status` AS `status`, `m`.`registration_date` AS `registration_date`, count(distinct `a`.`id`) AS `total_accounts`, coalesce(sum(`a`.`balance`),0) AS `total_savings`, count(distinct `l`.`id`) AS `total_loans`, coalesce(sum(`l`.`outstanding_balance`),0) AS `total_outstanding` FROM (((`members` `m` left join `member_types` `mt` on(`m`.`member_type_id` = `mt`.`id`)) left join `accounts` `a` on(`m`.`id` = `a`.`member_id` and `a`.`status` = 'Active')) left join `loans` `l` on(`m`.`id` = `l`.`member_id` and `l`.`status` in ('Active','Late'))) GROUP BY `m`.`id` ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_number` (`account_number`),
  ADD KEY `account_type_id` (`account_type_id`),
  ADD KEY `idx_account_number` (`account_number`),
  ADD KEY `idx_member_account` (`member_id`,`account_type_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_accounts_balance` (`balance`);

--
-- Indeks untuk tabel `account_transactions`
--
ALTER TABLE `account_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_account_transaction` (`account_id`,`transaction_date`),
  ADD KEY `idx_transaction_date` (`transaction_date`),
  ADD KEY `idx_reference_number` (`reference_number`),
  ADD KEY `idx_transactions_amount` (`amount`);

--
-- Indeks untuk tabel `account_types`
--
ALTER TABLE `account_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indeks untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_action` (`user_id`,`action`),
  ADD KEY `idx_table_record` (`table_name`,`record_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `auto_debit_config`
--
ALTER TABLE `auto_debit_config`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `idx_member_debit` (`member_id`),
  ADD KEY `idx_next_debit` (`next_debit_date`);

--
-- Indeks untuk tabel `collateral`
--
ALTER TABLE `collateral`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loan_collateral` (`loan_id`),
  ADD KEY `idx_member_collateral` (`member_id`);

--
-- Indeks untuk tabel `credit_scoring_criteria`
--
ALTER TABLE `credit_scoring_criteria`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `credit_scoring_details`
--
ALTER TABLE `credit_scoring_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `scoring_result_id` (`scoring_result_id`),
  ADD KEY `criteria_id` (`criteria_id`);

--
-- Indeks untuk tabel `credit_scoring_results`
--
ALTER TABLE `credit_scoring_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_scoring` (`member_id`),
  ADD KEY `idx_scoring_date` (`scoring_date`);

--
-- Indeks untuk tabel `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `loan_number` (`loan_number`),
  ADD KEY `loan_type_id` (`loan_type_id`),
  ADD KEY `idx_loan_number` (`loan_number`),
  ADD KEY `idx_member_loan` (`member_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_application_date` (`application_date`),
  ADD KEY `idx_loans_amount` (`amount`);

--
-- Indeks untuk tabel `loan_installments`
--
ALTER TABLE `loan_installments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loan_installment` (`loan_id`,`installment_number`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `loan_payments`
--
ALTER TABLE `loan_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loan_payment` (`loan_id`,`payment_date`),
  ADD KEY `idx_payment_date` (`payment_date`),
  ADD KEY `idx_reference_number` (`reference_number`);

--
-- Indeks untuk tabel `loan_types`
--
ALTER TABLE `loan_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indeks untuk tabel `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `member_number` (`member_number`),
  ADD UNIQUE KEY `id_number` (`id_number`),
  ADD KEY `member_type_id` (`member_type_id`),
  ADD KEY `idx_member_number` (`member_number`),
  ADD KEY `idx_id_number` (`id_number`),
  ADD KEY `idx_phone` (`phone_number`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_members_full_name` (`full_name`);

--
-- Indeks untuk tabel `member_documents`
--
ALTER TABLE `member_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_document` (`member_id`,`document_type`);

--
-- Indeks untuk tabel `member_status_history`
--
ALTER TABLE `member_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_status` (`member_id`,`changed_at`);

--
-- Indeks untuk tabel `member_types`
--
ALTER TABLE `member_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_setting_key` (`setting_key`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `account_transactions`
--
ALTER TABLE `account_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `account_types`
--
ALTER TABLE `account_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `auto_debit_config`
--
ALTER TABLE `auto_debit_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `collateral`
--
ALTER TABLE `collateral`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `credit_scoring_criteria`
--
ALTER TABLE `credit_scoring_criteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `credit_scoring_details`
--
ALTER TABLE `credit_scoring_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `credit_scoring_results`
--
ALTER TABLE `credit_scoring_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `loan_installments`
--
ALTER TABLE `loan_installments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `loan_payments`
--
ALTER TABLE `loan_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `loan_types`
--
ALTER TABLE `loan_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `member_documents`
--
ALTER TABLE `member_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `member_status_history`
--
ALTER TABLE `member_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `member_types`
--
ALTER TABLE `member_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  ADD CONSTRAINT `accounts_ibfk_2` FOREIGN KEY (`account_type_id`) REFERENCES `account_types` (`id`);

--
-- Ketidakleluasaan untuk tabel `account_transactions`
--
ALTER TABLE `account_transactions`
  ADD CONSTRAINT `account_transactions_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`);

--
-- Ketidakleluasaan untuk tabel `auto_debit_config`
--
ALTER TABLE `auto_debit_config`
  ADD CONSTRAINT `auto_debit_config_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  ADD CONSTRAINT `auto_debit_config_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`);

--
-- Ketidakleluasaan untuk tabel `collateral`
--
ALTER TABLE `collateral`
  ADD CONSTRAINT `collateral_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`),
  ADD CONSTRAINT `collateral_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`);

--
-- Ketidakleluasaan untuk tabel `credit_scoring_details`
--
ALTER TABLE `credit_scoring_details`
  ADD CONSTRAINT `credit_scoring_details_ibfk_1` FOREIGN KEY (`scoring_result_id`) REFERENCES `credit_scoring_results` (`id`),
  ADD CONSTRAINT `credit_scoring_details_ibfk_2` FOREIGN KEY (`criteria_id`) REFERENCES `credit_scoring_criteria` (`id`);

--
-- Ketidakleluasaan untuk tabel `credit_scoring_results`
--
ALTER TABLE `credit_scoring_results`
  ADD CONSTRAINT `credit_scoring_results_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`);

--
-- Ketidakleluasaan untuk tabel `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  ADD CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`loan_type_id`) REFERENCES `loan_types` (`id`);

--
-- Ketidakleluasaan untuk tabel `loan_installments`
--
ALTER TABLE `loan_installments`
  ADD CONSTRAINT `loan_installments_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`);

--
-- Ketidakleluasaan untuk tabel `loan_payments`
--
ALTER TABLE `loan_payments`
  ADD CONSTRAINT `loan_payments_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`);

--
-- Ketidakleluasaan untuk tabel `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`member_type_id`) REFERENCES `member_types` (`id`);

--
-- Ketidakleluasaan untuk tabel `member_documents`
--
ALTER TABLE `member_documents`
  ADD CONSTRAINT `member_documents_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `member_status_history`
--
ALTER TABLE `member_status_history`
  ADD CONSTRAINT `member_status_history_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
