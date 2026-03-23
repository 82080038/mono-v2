-- KSP Lam Gabe Jaya Database Update Script
-- Update dari struktur lama ke v2.0 dengan role system baru
-- Run this script to update existing database

-- =====================================================
-- UPDATE USERS TABLE - New Role System
-- =====================================================

-- Update users table structure
ALTER TABLE `users` 
ADD COLUMN `phone` varchar(20) DEFAULT NULL AFTER `full_name`,
ADD COLUMN `address` text DEFAULT NULL AFTER `phone`,
ADD COLUMN `login_attempts` int(11) NOT NULL DEFAULT 0 AFTER `last_login`,
ADD COLUMN `locked_until` datetime DEFAULT NULL AFTER `login_attempts`,
MODIFY COLUMN `role` enum('bos','admin','teller','collector','nasabah') NOT NULL DEFAULT 'nasabah';

-- Update existing users to new role system
UPDATE `users` SET 
    `role` = CASE 
        WHEN `role` = 'admin' THEN 'admin'
        WHEN `role` = 'manager' THEN 'bos'
        WHEN `role` = 'staff' THEN 'teller'
        WHEN `role` = 'member' THEN 'nasabah'
        ELSE 'nasabah'
    END,
    `phone` = CASE 
        WHEN `username` = 'bos' THEN '08123456789'
        WHEN `username` = 'admin' THEN '08234567890'
        WHEN `username` = 'staff' THEN '08345678901'
        WHEN `username` = 'member001' THEN '08123456789'
        WHEN `username` = 'member002' THEN '08234567890'
        ELSE NULL
    END,
    `address` = 'Jl. Koperasi No. 123, Jakarta'
WHERE 1;

-- Add new users for new role system
INSERT IGNORE INTO `users` (`username`, `password`, `email`, `full_name`, `phone`, `address`, `role`, `status`) VALUES
('teller', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teller@ksplamgabejaya.co.id', 'Teller KSP', '08345678901', 'Jl. Koperasi No. 123, Jakarta', 'teller', 'active'),
('collector', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'collector@ksplamgabejaya.co.id', 'Collector KSP', '08456789012', 'Jl. Koperasi No. 123, Jakarta', 'collector', 'active');

-- =====================================================
-- UPDATE MEMBERS TABLE - Enhanced Fields
-- =====================================================

-- Add new columns to members table
ALTER TABLE `members` 
ADD COLUMN `photo` varchar(255) DEFAULT NULL AFTER `status`,
ADD COLUMN `notes` text DEFAULT NULL AFTER `photo`;

-- =====================================================
-- UPDATE ACCOUNTS TABLE - Enhanced Fields
-- =====================================================

-- Add new columns to accounts table
ALTER TABLE `accounts` 
ADD COLUMN `last_transaction_date` date DEFAULT NULL AFTER `closed_date`,
MODIFY COLUMN `status` enum('active','inactive','closed','frozen') NOT NULL DEFAULT 'active';

-- =====================================================
-- UPDATE TRANSACTIONS TABLE - Enhanced Fields
-- =====================================================

-- Add new columns to transactions table
ALTER TABLE `transactions` 
ADD COLUMN `transaction_time` time DEFAULT NULL AFTER `transaction_date`,
ADD COLUMN `payment_method` enum('cash','transfer','bank_deposit','digital_payment') DEFAULT 'cash' AFTER `reference_number`,
ADD COLUMN `status` enum('pending','completed','failed','cancelled') NOT NULL DEFAULT 'completed' AFTER `payment_method`,
ADD COLUMN `approved_by` int(11) DEFAULT NULL AFTER `created_by`,
ADD COLUMN `notes` text DEFAULT NULL AFTER `approved_by`,
ADD KEY `status` (`status`),
ADD KEY `approved_by` (`approved_by`),
ADD CONSTRAINT `transactions_approved_by_fk` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

-- Update existing transactions
UPDATE `transactions` SET 
    `transaction_time` = '09:30:00',
    `status` = 'completed',
    `approved_by` = 1
WHERE `transaction_date` = '2024-01-15';

UPDATE `transactions` SET 
    `transaction_time` = '10:15:00',
    `status` = 'completed',
    `approved_by` = 1
WHERE `transaction_date` = '2024-02-20';

UPDATE `transactions` SET 
    `transaction_time` = '14:30:00',
    `payment_method` = 'cash',
    `status` = 'completed',
    `approved_by` = 3,
    `notes` = 'Monthly savings'
WHERE `id` IN (5, 6);

-- =====================================================
-- UPDATE SAVINGS TABLE - Enhanced Fields
-- =====================================================

-- Add new columns to savings table
ALTER TABLE `savings` 
ADD COLUMN `transaction_time` time DEFAULT NULL AFTER `transaction_date`,
ADD COLUMN `interest_rate` decimal(5,2) DEFAULT NULL AFTER `description`,
ADD COLUMN `maturity_date` date DEFAULT NULL AFTER `interest_rate`,
ADD COLUMN `approved_by` int(11) DEFAULT NULL AFTER `created_by`,
ADD COLUMN `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved' AFTER `approved_by`,
ADD KEY `status` (`status`),
ADD CONSTRAINT `savings_approved_by_fk` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

-- Update existing savings
UPDATE `savings` SET 
    `transaction_time` = CASE 
        WHEN `transaction_date` = '2024-01-15' THEN '09:30:00'
        WHEN `transaction_date` = '2024-02-20' THEN '10:15:00'
        WHEN `transaction_date` = '2024-03-01' THEN '14:30:00'
        WHEN `transaction_date` = '2024-03-05' THEN '15:45:00'
        ELSE '09:00:00'
    END,
    `interest_rate` = CASE 
        WHEN `savings_type` = 'wajib' THEN 3.00
        WHEN `savings_type` = 'sukarela' THEN 2.50
        ELSE NULL
    END,
    `approved_by` = 1,
    `status` = 'approved';

-- =====================================================
-- UPDATE LOANS TABLE - Enhanced Fields
-- =====================================================

-- Add new columns to loans table
ALTER TABLE `loans` 
ADD COLUMN `monthly_payment` decimal(15,2) DEFAULT NULL AFTER `loan_term`,
ADD COLUMN `guarantor` varchar(100) DEFAULT NULL AFTER `collateral`,
ADD COLUMN `disbursed_by` int(11) DEFAULT NULL AFTER `approved_by`,
ADD KEY `disbursed_by` (`disbursed_by`),
ADD CONSTRAINT `loans_disbursed_by_fk` FOREIGN KEY (`disbursed_by`) REFERENCES `users` (`id`);

-- Update existing loans
UPDATE `loans` SET 
    `monthly_payment` = CASE 
        WHEN `loan_amount` = 5000000 THEN 466666.67
        WHEN `loan_amount` = 3000000 THEN 516666.67
        ELSE NULL
    END,
    `guarantor` = CASE 
        WHEN `member_id` = 1 THEN 'Ahmad Wijaya'
        WHEN `member_id` = 2 THEN 'Siti Nurhaliza'
        ELSE NULL
    END,
    `disbursed_by` = 3;

-- =====================================================
-- UPDATE LOAN PAYMENTS TABLE - Enhanced Fields
-- =====================================================

-- Add new columns to loan_payments table
ALTER TABLE `loan_payments` 
ADD COLUMN `payment_time` time DEFAULT NULL AFTER `payment_date`,
ADD COLUMN `payment_method` enum('cash','transfer','bank_deposit','digital_payment') NOT NULL DEFAULT 'cash' AFTER `payment_time`,
ADD COLUMN `late_fee` decimal(15,2) DEFAULT 0.00 AFTER `interest_amount`,
ADD COLUMN `status` enum('pending','completed','failed','cancelled') NOT NULL DEFAULT 'completed' AFTER `received_by`),
ADD KEY `status` (`status`);

-- Update existing loan payments
UPDATE `loan_payments` SET 
    `payment_time` = CASE 
        WHEN `payment_date` = '2024-03-06' THEN '10:30:00'
        WHEN `payment_date` = '2024-04-04' THEN '14:15:00'
        ELSE '09:00:00'
    END,
    `status` = 'completed';

-- =====================================================
-- UPDATE LOGIN ATTEMPTS TABLE - Enhanced Fields
-- =====================================================

-- Add new column to login_attempts table
ALTER TABLE `login_attempts` 
ADD COLUMN `failure_reason` varchar(100) DEFAULT NULL AFTER `success`,
ADD KEY `success` (`success`);

-- Update existing login attempts
UPDATE `login_attempts` SET 
    `failure_reason` = CASE 
        WHEN `username` = 'wrong' THEN 'Invalid credentials'
        WHEN `username` = '<script>' THEN 'SQL Injection attempt'
        ELSE NULL
    END,
    `user_agent` = CASE 
        WHEN `user_agent` IS NULL THEN 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ELSE `user_agent`
    END;

-- =====================================================
-- UPDATE AUDIT LOGS TABLE - Enhanced Fields
-- =====================================================

-- Add new column to audit_logs table
ALTER TABLE `audit_logs` 
ADD COLUMN `session_id` varchar(255) DEFAULT NULL AFTER `user_agent`,
ADD KEY `session_id` (`session_id`);

-- Update existing audit logs
UPDATE `audit_logs` SET 
    `session_id` = CASE 
        WHEN `id` <= 5 THEN 'session_001'
        ELSE CONCAT('session_', `id`)
    END;

-- =====================================================
-- UPDATE SYSTEM CONFIG TABLE - Enhanced Fields
-- =====================================================

-- Add new column to system_config table
ALTER TABLE `system_config` 
ADD COLUMN `category` varchar(50) DEFAULT 'general' AFTER `description`,
ADD KEY `category` (`category`);

-- Update existing system config
UPDATE `system_config` SET 
    `category` = CASE 
        WHEN `config_key` IN ('ksp_name', 'ksp_address', 'ksp_phone', 'ksp_email') THEN 'general'
        WHEN `config_key` IN ('savings_wajib_minimum', 'savings_pokok_minimum') THEN 'savings'
        WHEN `config_key` IN ('loan_interest_min', 'loan_interest_max', 'loan_term_max', 'late_payment_fee') THEN 'loans'
        WHEN `config_key` IN ('session_timeout', 'max_login_attempts', 'lockout_duration') THEN 'security'
        WHEN `config_key` IN ('enable_notifications', 'enable_audit_log') THEN 'features'
        ELSE 'general'
    END;

-- =====================================================
-- CREATE VIEWS FOR REPORTING
-- =====================================================

-- Drop existing views if they exist
DROP TABLE IF EXISTS `daily_transactions`;
/*!50001 DROP VIEW IF EXISTS `daily_transactions`*/;
DROP TABLE IF EXISTS `loan_performance`;
/*!50001 DROP VIEW IF EXISTS `loan_performance`*/;
DROP TABLE IF EXISTS `member_summary`;
/*!50001 DROP VIEW IF EXISTS `member_summary`*/;

-- Create daily_transactions view
CREATE VIEW `daily_transactions` AS SELECT 
    DATE(t.transaction_date) as transaction_date,
    COUNT(*) as total_transactions,
    SUM(CASE WHEN t.transaction_type = 'credit' THEN 1 ELSE 0 END) as total_credits,
    SUM(CASE WHEN t.transaction_type = 'debit' THEN 1 ELSE 0 END) as total_debits,
    SUM(CASE WHEN t.transaction_type = 'credit' THEN t.amount ELSE -t.amount END) as net_amount,
    SUM(CASE WHEN t.transaction_type = 'credit' THEN t.amount ELSE 0 END) as total_credits_amount,
    SUM(CASE WHEN t.transaction_type = 'debit' THEN t.amount ELSE 0 END) as total_debits_amount
FROM transactions t
WHERE t.status = 'completed'
GROUP BY DATE(t.transaction_date)
ORDER BY transaction_date DESC;

-- Create loan_performance view
CREATE VIEW `loan_performance` AS SELECT 
    l.id,
    l.loan_number,
    m.full_name as member_name,
    l.loan_amount,
    l.interest_rate,
    l.loan_term,
    l.status,
    l.application_date,
    l.disbursement_date,
    COALESCE(SUM(lp.amount), 0) as total_paid,
    l.loan_amount - COALESCE(SUM(lp.amount), 0) as remaining_balance,
    CASE 
        WHEN l.loan_amount - COALESCE(SUM(lp.amount), 0) <= 0 THEN 'completed'
        WHEN l.due_date < CURDATE() AND l.loan_amount - COALESCE(SUM(lp.amount), 0) > 0 THEN 'overdue'
        ELSE 'active'
    END as payment_status
FROM loans l
LEFT JOIN members m ON l.member_id = m.id
LEFT JOIN loan_payments lp ON l.id = lp.loan_id AND lp.status = 'completed'
GROUP BY l.id, l.loan_number, m.full_name, l.loan_amount, l.interest_rate, l.loan_term, l.status, l.application_date, l.disbursement_date
ORDER BY l.application_date DESC;

-- Create member_summary view
CREATE VIEW `member_summary` AS SELECT 
    m.id,
    m.member_number,
    m.full_name,
    m.phone,
    m.email,
    m.join_date,
    m.status,
    COUNT(DISTINCT a.id) as total_accounts,
    COALESCE(SUM(a.balance), 0) as total_balance,
    COUNT(DISTINCT l.id) as total_loans,
    COALESCE(SUM(l.loan_amount), 0) as total_loan_amount,
    COALESCE(l.loan_amount - COALESCE(SUM(lp.amount), 0), 0) as outstanding_loans
FROM members m
LEFT JOIN accounts a ON m.id = a.member_id AND a.status = 'active'
LEFT JOIN loans l ON m.id = l.member_id AND l.status IN ('active', 'completed')
LEFT JOIN loan_payments lp ON l.id = lp.loan_id AND lp.status = 'completed'
GROUP BY m.id, m.member_number, m.full_name, m.phone, m.email, m.join_date, m.status
ORDER BY m.full_name;

-- =====================================================
-- INSERT SAMPLE DATA FOR NEW ROLES
-- =====================================================

-- Update member data to match new user system
UPDATE `members` SET `user_id` = 5 WHERE `member_number` = 'M001';
UPDATE `members` SET `user_id` = 5 WHERE `member_number` = 'M002';

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

-- Verify role system update
SELECT 'Role System Update' as update_type, 
       COUNT(*) as total_users,
       SUM(CASE WHEN role = 'bos' THEN 1 ELSE 0 END) as bos_count,
       SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
       SUM(CASE WHEN role = 'teller' THEN 1 ELSE 0 END) as teller_count,
       SUM(CASE WHEN role = 'collector' THEN 1 ELSE 0 END) as collector_count,
       SUM(CASE WHEN role = 'nasabah' THEN 1 ELSE 0 END) as nasabah_count
FROM users;

-- Verify enhanced tables
SELECT 'Enhanced Tables Update' as update_type,
       COUNT(*) as total_transactions,
       SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_transactions,
       SUM(CASE WHEN payment_method = 'cash' THEN 1 ELSE 0 END) as cash_transactions
FROM transactions;

-- Verify views creation
SELECT 'Views Created' as update_type,
       COUNT(*) as daily_transaction_days
FROM daily_transactions;

-- =====================================================
-- COMPLETION MESSAGE
-- =====================================================

SELECT 'Database Update Completed' as status,
       NOW() as completion_time,
       'KSP Lam Gabe Jaya v2.0' as version,
       'Role system enhanced with bos, admin, teller, collector, nasabah' as description;

-- =====================================================
-- NEXT STEPS
-- =====================================================

-- 1. Test login dengan semua role:
--    - bos/bos
--    - admin/admin  
--    - teller/teller
--    - collector/collector
--    - nasabah/nasabah

-- 2. Test dynamic navigation system

-- 3. Verify role-based dashboard content

-- 4. Test enhanced features (payment methods, status tracking, dll)

-- 5. Run comprehensive test suite
