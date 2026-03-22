-- KSP Lam Gabe Jaya - Database Schema
-- Database: gabe
-- Character Set: utf8mb4
-- Collation: utf8mb4_unicode_ci

-- ========================================
-- 1. USERS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL,
    `email` varchar(100) DEFAULT NULL,
    `full_name` varchar(100) NOT NULL,
    `role` enum('admin','manager','staff','member') NOT NULL DEFAULT 'member',
    `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
    `last_login` datetime DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`),
    KEY `role` (`role`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 2. MEMBERS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS `members` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `member_number` varchar(20) NOT NULL,
    `nik` varchar(16) DEFAULT NULL,
    `full_name` varchar(100) NOT NULL,
    `birth_date` date DEFAULT NULL,
    `birth_place` varchar(100) DEFAULT NULL,
    `gender` enum('L','P') DEFAULT NULL,
    `address` text DEFAULT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `email` varchar(100) DEFAULT NULL,
    `join_date` date NOT NULL,
    `status` enum('active','inactive','blacklisted') NOT NULL DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `member_number` (`member_number`),
    UNIQUE KEY `nik` (`nik`),
    KEY `user_id` (`user_id`),
    KEY `status` (`status`),
    CONSTRAINT `members_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 3. ACCOUNTS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS `accounts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `member_id` int(11) NOT NULL,
    `account_number` varchar(20) NOT NULL,
    `account_type` enum('simpanan','pinjaman') NOT NULL,
    `account_name` varchar(100) NOT NULL,
    `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
    `interest_rate` decimal(5,2) DEFAULT NULL,
    `status` enum('active','inactive','closed') NOT NULL DEFAULT 'active',
    `opened_date` date NOT NULL,
    `closed_date` date DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `account_number` (`account_number`),
    KEY `member_id` (`member_id`),
    KEY `account_type` (`account_type`),
    KEY `status` (`status`),
    CONSTRAINT `accounts_member_id_fk` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 4. TRANSACTIONS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS `transactions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `transaction_code` varchar(20) NOT NULL,
    `account_id` int(11) NOT NULL,
    `transaction_type` enum('debit','credit') NOT NULL,
    `amount` decimal(15,2) NOT NULL,
    `description` varchar(255) DEFAULT NULL,
    `reference_number` varchar(50) DEFAULT NULL,
    `transaction_date` date NOT NULL,
    `created_by` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `transaction_code` (`transaction_code`),
    KEY `account_id` (`account_id`),
    KEY `transaction_type` (`transaction_type`),
    KEY `transaction_date` (`transaction_date`),
    KEY `created_by` (`created_by`),
    CONSTRAINT `transactions_account_id_fk` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `transactions_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 5. LOANS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS `loans` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `member_id` int(11) NOT NULL,
    `loan_number` varchar(20) NOT NULL,
    `loan_amount` decimal(15,2) NOT NULL,
    `interest_rate` decimal(5,2) NOT NULL,
    `loan_term` int(11) NOT NULL COMMENT 'jangka waktu dalam bulan',
    `purpose` varchar(255) DEFAULT NULL,
    `collateral` text DEFAULT NULL,
    `status` enum('pending','approved','rejected','active','completed','defaulted') NOT NULL DEFAULT 'pending',
    `application_date` date NOT NULL,
    `approval_date` date DEFAULT NULL,
    `disbursement_date` date DEFAULT NULL,
    `due_date` date DEFAULT NULL,
    `approved_by` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `loan_number` (`loan_number`),
    KEY `member_id` (`member_id`),
    KEY `status` (`status`),
    KEY `application_date` (`application_date`),
    KEY `approved_by` (`approved_by`),
    CONSTRAINT `loans_member_id_fk` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
    CONSTRAINT `loans_approved_by_fk` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 6. LOAN PAYMENTS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS `loan_payments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `loan_id` int(11) NOT NULL,
    `payment_number` int(11) NOT NULL,
    `amount` decimal(15,2) NOT NULL,
    `principal_amount` decimal(15,2) NOT NULL,
    `interest_amount` decimal(15,2) NOT NULL,
    `payment_date` date NOT NULL,
    `payment_method` enum('cash','transfer','bank_deposit') NOT NULL DEFAULT 'cash',
    `received_by` int(11) NOT NULL,
    `notes` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `loan_id` (`loan_id`),
    KEY `payment_date` (`payment_date`),
    KEY `received_by` (`received_by`),
    CONSTRAINT `loan_payments_loan_id_fk` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
    CONSTRAINT `loan_payments_received_by_fk` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 7. SAVINGS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS `savings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `member_id` int(11) NOT NULL,
    `savings_type` enum('wajib','pokok','sukarela') NOT NULL,
    `amount` decimal(15,2) NOT NULL,
    `transaction_date` date NOT NULL,
    `description` varchar(255) DEFAULT NULL,
    `created_by` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `member_id` (`member_id`),
    KEY `savings_type` (`savings_type`),
    KEY `transaction_date` (`transaction_date`),
    KEY `created_by` (`created_by`),
    CONSTRAINT `savings_member_id_fk` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
    CONSTRAINT `savings_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 8. LOGIN_ATTEMPTS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `ip_address` varchar(45) NOT NULL,
    `user_agent` varchar(255) DEFAULT NULL,
    `success` tinyint(1) NOT NULL DEFAULT 0,
    `attempt_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `username` (`username`),
    KEY `ip_address` (`ip_address`),
    KEY `attempt_time` (`attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 9. AUDIT_LOGS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `action` varchar(100) NOT NULL,
    `table_name` varchar(50) DEFAULT NULL,
    `record_id` int(11) DEFAULT NULL,
    `old_values` json DEFAULT NULL,
    `new_values` json DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` varchar(255) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `action` (`action`),
    KEY `table_name` (`table_name`),
    KEY `created_at` (`created_at`),
    CONSTRAINT `audit_logs_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 10. SYSTEM_CONFIG TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS `system_config` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `config_key` varchar(100) NOT NULL,
    `config_value` text DEFAULT NULL,
    `config_type` enum('string','number','boolean','json') NOT NULL DEFAULT 'string',
    `description` varchar(255) DEFAULT NULL,
    `updated_by` int(11) DEFAULT NULL,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `config_key` (`config_key`),
    KEY `updated_by` (`updated_by`),
    CONSTRAINT `system_config_updated_by_fk` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
