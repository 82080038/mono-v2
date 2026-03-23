-- KSP Lam Gabe Jaya Database Schema
-- Generated on: 2026-03-24
-- Version: 2.0
-- Character Set: UTF8

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `ksp_lamgabejaya_v2` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `ksp_lamgabejaya_v2`;

-- Drop existing tables (for clean import)
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `loan_payments`;
DROP TABLE IF EXISTS `loans`;
DROP TABLE IF EXISTS `accounts`;
DROP TABLE IF EXISTS `members`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `system_settings`;

-- Users table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('bos','admin','teller','collector','nasabah') NOT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Members table
CREATE TABLE `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_number` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `birth_place` varchar(100) DEFAULT NULL,
  `gender` enum('L','P') DEFAULT NULL,
  `address` text,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `status` enum('active','inactive','pending','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_number` (`member_number`),
  KEY `idx_full_name` (`full_name`),
  KEY `idx_status` (`status`),
  KEY `idx_join_date` (`join_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Accounts table
CREATE TABLE `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_number` varchar(20) NOT NULL,
  `member_id` int(11) NOT NULL,
  `account_type` enum('simpanan_pokok','simpanan_wajib','simpanan_sukarela','deposito') NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `interest_rate` decimal(5,2) DEFAULT 0.00,
  `status` enum('active','inactive','frozen','closed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_number` (`account_number`),
  KEY `idx_member_id` (`member_id`),
  KEY `idx_account_type` (`account_type`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_accounts_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Loans table
CREATE TABLE `loans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_number` varchar(20) NOT NULL,
  `member_id` int(11) NOT NULL,
  `loan_type` enum('regular','emergency','investment') DEFAULT 'regular',
  `loan_amount` decimal(15,2) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `loan_period` int(11) NOT NULL,
  `purpose` text,
  `collateral` text,
  `status` enum('pending','approved','rejected','active','completed','defaulted') DEFAULT 'pending',
  `application_date` date NOT NULL,
  `approval_date` date DEFAULT NULL,
  `disbursement_date` date DEFAULT NULL,
  `maturity_date` date DEFAULT NULL,
  `monthly_installment` decimal(15,2) DEFAULT 0.00,
  `remaining_balance` decimal(15,2) DEFAULT 0.00,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `loan_number` (`loan_number`),
  KEY `idx_member_id` (`member_id`),
  KEY `idx_status` (`status`),
  KEY `idx_application_date` (`application_date`),
  CONSTRAINT `fk_loans_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_loans_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Loan payments table
CREATE TABLE `loan_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) NOT NULL,
  `payment_amount` decimal(15,2) NOT NULL,
  `principal` decimal(15,2) DEFAULT 0.00,
  `interest` decimal(15,2) DEFAULT 0.00,
  `penalty` decimal(15,2) DEFAULT 0.00,
  `payment_method` enum('cash','transfer','bank_deposit','digital_payment') DEFAULT 'cash',
  `payment_date` date NOT NULL,
  `receipt_number` varchar(20) DEFAULT NULL,
  `teller_id` int(11) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_loan_id` (`loan_id`),
  KEY `idx_payment_date` (`payment_date`),
  CONSTRAINT `fk_loan_payments_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_loan_payments_teller` FOREIGN KEY (`teller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Transactions table
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_code` varchar(20) NOT NULL,
  `account_id` int(11) NOT NULL,
  `transaction_type` enum('credit','debit') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text,
  `payment_method` enum('cash','transfer','bank_deposit','digital_payment') DEFAULT 'cash',
  `reference_number` varchar(50) DEFAULT NULL,
  `teller_id` int(11) DEFAULT NULL,
  `status` enum('pending','completed','failed','cancelled') DEFAULT 'completed',
  `transaction_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_code` (`transaction_code`),
  KEY `idx_account_id` (`account_id`),
  KEY `idx_transaction_type` (`transaction_type`),
  KEY `idx_transaction_date` (`transaction_date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_transactions_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_transactions_teller` FOREIGN KEY (`teller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Audit logs table
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text,
  `new_values` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_table_name` (`table_name`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- System settings table
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `description` text,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  CONSTRAINT `fk_system_settings_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Insert default users
INSERT INTO `users` (`username`, `password`, `name`, `email`, `role`, `status`) VALUES
('bos', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'BOS KSP', 'bos@ksp-lamgabejaya.com', 'bos', 'active'),
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@ksp-lamgabejaya.com', 'admin', 'active'),
('teller', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Teller', 'teller@ksp-lamgabejaya.com', 'teller', 'active'),
('collector', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Collector', 'collector@ksp-lamgabejaya.com', 'collector', 'active'),
('nasabah', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nasabah', 'nasabah@ksp-lamgabejaya.com', 'nasabah', 'active');

-- Insert sample members
INSERT INTO `members` (`member_number`, `full_name`, `nik`, `birth_date`, `birth_place`, `gender`, `address`, `phone`, `email`, `occupation`, `join_date`, `status`) VALUES
('MBR20240324001', 'Budi Santoso', '1234567890123456', '1990-01-15', 'Jakarta', 'L', 'Jl. Merdeka No. 45, Jakarta', '08123456789', 'budi.santoso@email.com', 'Pegawai Swasta', '2024-03-01', 'active'),
('MBR20240324002', 'Siti Nurhaliza', '2345678901234567', '1985-05-20', 'Bandung', 'P', 'Jl. Sudirman No. 12, Bandung', '08234567890', 'siti.nurhaliza@email.com', 'Wiraswasta', '2024-03-05', 'active'),
('MBR20240324003', 'Ahmad Fauzi', '3456789012345678', '1988-08-10', 'Surabaya', 'L', 'Jl. Gajah Mada No. 78, Surabaya', '08345678901', 'ahmad.fauzi@email.com', 'Guru', '2024-03-10', 'active'),
('MBR20240324004', 'Dewi Lestari', '4567890123456789', '1992-12-25', 'Yogyakarta', 'P', 'Jl. Malioboro No. 23, Yogyakarta', '08456789012', 'dewi.lestari@email.com', 'Ibu Rumah Tangga', '2024-03-15', 'active'),
('MBR20240324005', 'Eko Prasetyo', '5678901234567890', '1987-03-30', 'Semarang', 'L', 'Jl. Pemuda No. 56, Semarang', '08567890123', 'eko.prasetyo@email.com', 'Pedagang', '2024-03-20', 'pending');

-- Insert sample accounts
INSERT INTO `accounts` (`account_number`, `member_id`, `account_type`, `account_name`, `balance`, `interest_rate`, `status`) VALUES
('SP0010001', 1, 'simpanan_pokok', 'Simpanan Pokok', 100000.00, 0.00, 'active'),
('SW0010001', 1, 'simpanan_wajib', 'Simpanan Wajib', 500000.00, 3.00, 'active'),
('SS0010001', 1, 'simpanan_sukarela', 'Simpanan Sukarela', 2500000.00, 4.00, 'active'),
('SP0010002', 2, 'simpanan_pokok', 'Simpanan Pokok', 100000.00, 0.00, 'active'),
('SW0010002', 2, 'simpanan_wajib', 'Simpanan Wajib', 500000.00, 3.00, 'active'),
('SS0010002', 2, 'simpanan_sukarela', 'Simpanan Sukarela', 1500000.00, 4.00, 'active'),
('SP0010003', 3, 'simpanan_pokok', 'Simpanan Pokok', 100000.00, 0.00, 'active'),
('SW0010003', 3, 'simpanan_wajib', 'Simpanan Wajib', 500000.00, 3.00, 'active'),
('SS0010003', 3, 'simpanan_sukarela', 'Simpanan Sukarela', 3000000.00, 4.00, 'active'),
('SP0010004', 4, 'simpanan_pokok', 'Simpanan Pokok', 100000.00, 0.00, 'active'),
('SW0010004', 4, 'simpanan_wajib', 'Simpanan Wajib', 500000.00, 3.00, 'active'),
('SS0010004', 4, 'simpanan_sukarela', 'Simpanan Sukarela', 2000000.00, 4.00, 'active');

-- Insert sample loans
INSERT INTO `loans` (`loan_number`, `member_id`, `loan_type`, `loan_amount`, `interest_rate`, `loan_period`, `purpose`, `collateral`, `status`, `application_date`, `monthly_installment`, `remaining_balance`) VALUES
('LN20240324001', 1, 'regular', 10000000.00, 12.00, 12, 'Modal Usaha', 'BPKB Motor', 'active', '2024-03-15', 950000.00, 8550000.00),
('LN20240324002', 2, 'emergency', 5000000.00, 15.00, 6, 'Biaya Pendidikan', 'SK Penghasilan', 'active', '2024-03-18', 950000.00, 4750000.00),
('LN20240324003', 3, 'investment', 15000000.00, 10.00, 24, 'Ekspansi Usaha', 'Sertifikat Tanah', 'approved', '2024-03-20', 750000.00, 15000000.00),
('LN20240324004', 4, 'regular', 7500000.00, 12.00, 12, 'Renovasi Rumah', 'BPKB Mobil', 'pending', '2024-03-22', 712500.00, 7500000.00);

-- Insert sample transactions
INSERT INTO `transactions` (`transaction_code`, `account_id`, `transaction_type`, `amount`, `description`, `payment_method`, `teller_id`, `status`, `transaction_date`) VALUES
('TRX20240324001', 1, 'credit', 100000.00, 'Setoran Simpanan Pokok', 'cash', 3, 'completed', '2024-03-24 08:30:00'),
('TRX20240324002', 2, 'credit', 100000.00, 'Setoran Simpanan Pokok', 'cash', 3, 'completed', '2024-03-24 08:45:00'),
('TRX20240324003', 3, 'credit', 100000.00, 'Setoran Simpanan Pokok', 'cash', 3, 'completed', '2024-03-24 09:00:00'),
('TRX20240324004', 4, 'credit', 100000.00, 'Setoran Simpanan Pokok', 'cash', 3, 'completed', '2024-03-24 09:15:00'),
('TRX20240324005', 5, 'credit', 500000.00, 'Setoran Simpanan Wajib', 'transfer', 3, 'completed', '2024-03-24 09:30:00'),
('TRX20240324006', 6, 'credit', 500000.00, 'Setoran Simpanan Wajib', 'transfer', 3, 'completed', '2024-03-24 09:45:00'),
('TRX20240324007', 7, 'credit', 500000.00, 'Setoran Simpanan Wajib', 'bank_deposit', 3, 'completed', '2024-03-24 10:00:00'),
('TRX20240324008', 8, 'credit', 500000.00, 'Setoran Simpanan Wajib', 'bank_deposit', 3, 'completed', '2024-03-24 10:15:00'),
('TRX20240324009', 1, 'credit', 2500000.00, 'Setoran Simpanan Sukarela', 'cash', 3, 'completed', '2024-03-24 10:30:00'),
('TRX20240324010', 2, 'credit', 1500000.00, 'Setoran Simpanan Sukarela', 'transfer', 3, 'completed', '2024-03-24 10:45:00');

-- Insert sample loan payments
INSERT INTO `loan_payments` (`loan_id`, `payment_amount`, `principal`, `interest`, `payment_method`, `payment_date`, `receipt_number`, `teller_id`, `notes`) VALUES
(1, 950000.00, 850000.00, 100000.00, 'cash', '2024-03-24', 'RCP20240324001', 3, 'Angsuran bulan Maret'),
(2, 950000.00, 833333.33, 116666.67, 'transfer', '2024-03-24', 'RCP20240324002', 3, 'Angsuran bulan Maret');

-- Insert system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('company_name', 'KSP Lam Gabe Jaya', 'string', 'Nama perusahaan'),
('company_address', 'Jl. Raya No. 123, Jakarta', 'string', 'Alamat perusahaan'),
('company_phone', '021-12345678', 'string', 'Nomor telepon perusahaan'),
('company_email', 'info@ksp-lamgabejaya.com', 'string', 'Email perusahaan'),
('minimum_deposit', '1000', 'number', 'Minimal setoran'),
('maximum_loan', '50000000', 'number', 'Maksimal pinjaman'),
('interest_rate_savings', '4.00', 'number', 'Bunga simpanan (%)'),
('interest_rate_loan', '12.00', 'number', 'Bunga pinjaman (%)'),
('late_payment_penalty', '5.00', 'number', 'Denda keterlambatan (%)'),
('session_timeout', '30', 'number', 'Timeout sesi (menit)'),
('backup_enabled', 'true', 'boolean', 'Backup otomatis diaktifkan'),
('maintenance_mode', 'false', 'boolean', 'Mode pemeliharaan');

-- Create indexes for better performance
CREATE INDEX `idx_transactions_member` ON `transactions` (`account_id`);
CREATE INDEX `idx_loans_member_status` ON `loans` (`member_id`, `status`);
CREATE INDEX `idx_accounts_member_type` ON `accounts` (`member_id`, `account_type`);
CREATE INDEX `idx_loan_payments_loan_date` ON `loan_payments` (`loan_id`, `payment_date`);
CREATE INDEX `idx_audit_logs_user_date` ON `audit_logs` (`user_id`, `created_at`);

-- Create views for common queries
CREATE VIEW `v_member_summary` AS
SELECT 
    m.id,
    m.member_number,
    m.full_name,
    m.phone,
    m.email,
    m.status,
    COUNT(a.id) as total_accounts,
    COALESCE(SUM(a.balance), 0) as total_balance,
    COUNT(l.id) as total_loans,
    COALESCE(SUM(l.remaining_balance), 0) as total_loan_balance
FROM members m
LEFT JOIN accounts a ON m.id = a.member_id AND a.status = 'active'
LEFT JOIN loans l ON m.id = l.member_id AND l.status = 'active'
GROUP BY m.id, m.member_number, m.full_name, m.phone, m.email, m.status;

CREATE VIEW `v_transaction_summary` AS
SELECT 
    DATE(t.transaction_date) as transaction_date,
    t.transaction_type,
    COUNT(*) as total_transactions,
    SUM(t.amount) as total_amount
FROM transactions t
WHERE t.status = 'completed'
GROUP BY DATE(t.transaction_date), t.transaction_type
ORDER BY transaction_date DESC;

CREATE VIEW `v_loan_summary` AS
SELECT 
    DATE(l.application_date) as application_date,
    l.loan_type,
    l.status,
    COUNT(*) as total_loans,
    SUM(l.loan_amount) as total_amount,
    SUM(l.remaining_balance) as remaining_balance
FROM loans l
GROUP BY DATE(l.application_date), l.loan_type, l.status
ORDER BY application_date DESC;

-- Create stored procedures
DELIMITER //

CREATE PROCEDURE `sp_get_member_balance`(IN member_id INT)
BEGIN
    SELECT 
        a.account_number,
        a.account_type,
        a.account_name,
        a.balance,
        a.status
    FROM accounts a
    WHERE a.member_id = member_id AND a.status = 'active'
    ORDER BY a.account_type;
END //

CREATE PROCEDURE `sp_process_deposit`(IN account_id INT, IN amount DECIMAL(15,2), IN description TEXT, IN teller_id INT)
BEGIN
    DECLARE current_balance DECIMAL(15,2);
    DECLARE transaction_code VARCHAR(20);
    
    -- Get current balance
    SELECT balance INTO current_balance FROM accounts WHERE id = account_id;
    
    -- Generate transaction code
    SET transaction_code = CONCAT('DEP', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD((SELECT COUNT(*) + 1 FROM transactions WHERE DATE(transaction_date) = CURDATE()), 4, '0'));
    
    -- Start transaction
    START TRANSACTION;
    
    -- Update account balance
    UPDATE accounts SET balance = balance + amount WHERE id = account_id;
    
    -- Insert transaction record
    INSERT INTO transactions (transaction_code, account_id, transaction_type, amount, description, teller_id, status, transaction_date)
    VALUES (transaction_code, account_id, 'credit', amount, description, teller_id, 'completed', NOW());
    
    -- Log audit
    INSERT INTO audit_logs (user_id, action, table_name, record_id, new_values)
    VALUES (teller_id, 'DEPOSIT', 'accounts', account_id, CONCAT('amount:', amount, ',balance:', current_balance + amount));
    
    COMMIT;
    
    SELECT transaction_code, current_balance + amount as new_balance;
END //

CREATE PROCEDURE `sp_get_dashboard_stats`(IN user_role VARCHAR(20))
BEGIN
    IF user_role = 'bos' THEN
        SELECT 
            COUNT(DISTINCT m.id) as total_members,
            COUNT(DISTINCT CASE WHEN m.status = 'active' THEN m.id END) as active_members,
            COALESCE(SUM(a.balance), 0) as total_deposits,
            COALESCE(SUM(l.remaining_balance), 0) as total_loans,
            COUNT(DISTINCT CASE WHEN l.status = 'pending' THEN l.id END) as pending_loans
        FROM members m
        LEFT JOIN accounts a ON m.id = a.member_id AND a.status = 'active'
        LEFT JOIN loans l ON m.id = l.member_id;
    ELSEIF user_role = 'admin' THEN
        SELECT 
            COUNT(DISTINCT m.id) as total_members,
            COUNT(DISTINCT CASE WHEN m.status = 'pending' THEN m.id END) as pending_members,
            COUNT(DISTINCT CASE WHEN l.status = 'pending' THEN l.id END) as pending_loans,
            COUNT(DISTINCT CASE WHEN t.status = 'pending' THEN t.id END) as pending_transactions
        FROM members m
        LEFT JOIN loans l ON m.id = l.member_id
        LEFT JOIN transactions t ON 1=1;
    ELSEIF user_role = 'teller' THEN
        SELECT 
            COUNT(DISTINCT t.id) as today_transactions,
            COALESCE(SUM(CASE WHEN t.transaction_type = 'credit' THEN t.amount ELSE 0 END), 0) as total_deposits,
            COALESCE(SUM(CASE WHEN t.transaction_type = 'debit' THEN t.amount ELSE 0 END), 0) as total_withdrawals,
            COALESCE(SUM(t.amount), 0) as total_amount
        FROM transactions t
        WHERE DATE(t.transaction_date) = CURDATE() AND t.status = 'completed';
    END IF;
END //

DELIMITER ;

-- Database export completed
-- This file contains the complete KSP Lam Gabe Jaya database structure and sample data
-- Generated on: 2026-03-24
-- Version: 2.0