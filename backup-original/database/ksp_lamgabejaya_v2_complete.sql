-- =====================================================
-- KSP LAM GABE JAYA - COMPLETE DATABASE SCHEMA
-- Latest Version with Dynamic Dashboard System
-- =====================================================
-- Generated: 2026-03-22
-- Compatible with: MySQL 8.0+ / MariaDB 10.4+
-- =====================================================

-- Drop existing tables (clean install)
DROP DATABASE IF EXISTS `ksp_lamgabejaya_v2`;
CREATE DATABASE `ksp_lamgabejaya_v2` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ksp_lamgabejaya_v2`;

-- =====================================================
-- CORE SYSTEM TABLES
-- =====================================================

-- Users table (authentication & role management)
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'member',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_email` (`email`),
  KEY `idx_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Members table (customer data)
CREATE TABLE `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_number` varchar(20) NOT NULL UNIQUE,
  `full_name` varchar(100) NOT NULL,
  `id_number` varchar(50) NOT NULL UNIQUE,
  `birth_date` date DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `province_id` int(11) DEFAULT NULL,
  `regency_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `village_id` int(11) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `member_status` enum('active','inactive','blacklisted') DEFAULT 'active',
  `registration_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_members_member_number` (`member_number`),
  KEY `idx_members_id_number` (`id_number`),
  KEY `idx_members_status` (`member_status`),
  KEY `idx_members_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Loans table (loan portfolio)
CREATE TABLE `loans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_number` varchar(20) NOT NULL UNIQUE,
  `member_id` int(11) NOT NULL,
  `loan_type` enum('personal','business','emergency','education') DEFAULT 'personal',
  `principal_amount` decimal(15,2) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL DEFAULT 2.50,
  `loan_term` int(11) NOT NULL COMMENT 'in months',
  `monthly_payment` decimal(15,2) NOT NULL,
  `total_payment` decimal(15,2) NOT NULL,
  `purpose` text DEFAULT NULL,
  `collateral_type` varchar(50) DEFAULT NULL,
  `collateral_value` decimal(15,2) DEFAULT NULL,
  `guarantor_id` int(11) DEFAULT NULL,
  `loan_status` enum('pending','approved','disbursed','active','completed','defaulted') DEFAULT 'pending',
  `application_date` date DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `disbursement_date` date DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_loans_loan_number` (`loan_number`),
  KEY `idx_loans_member_id` (`member_id`),
  KEY `idx_loans_status` (`loan_status`),
  KEY `idx_loans_staff_id` (`staff_id`),
  KEY `idx_loans_application_date` (`application_date`),
  CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `loans_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Savings table (customer deposits)
CREATE TABLE `savings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_number` varchar(20) NOT NULL UNIQUE,
  `member_id` int(11) NOT NULL,
  `savings_type` enum('regular','mandatory','fixed_deposit') DEFAULT 'regular',
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `interest_rate` decimal(5,2) DEFAULT 0.00,
  `minimum_balance` decimal(15,2) DEFAULT 0.00,
  `account_status` enum('active','inactive','frozen') DEFAULT 'active',
  `opening_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_savings_account_number` (`account_number`),
  KEY `idx_savings_member_id` (`member_id`),
  KEY `idx_savings_status` (`account_status`),
  CONSTRAINT `savings_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment transactions table
CREATE TABLE `payment_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_number` varchar(20) NOT NULL UNIQUE,
  `member_id` int(11) NOT NULL,
  `loan_id` int(11) DEFAULT NULL,
  `savings_id` int(11) DEFAULT NULL,
  `transaction_type` enum('loan_payment','savings_deposit','savings_withdrawal','loan_disbursement','fee_payment','penalty_payment') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` enum('cash','transfer','digital_wallet','bank_transfer') DEFAULT 'cash',
  `payment_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `transaction_status` enum('pending','completed','failed','cancelled') DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_transactions_number` (`transaction_number`),
  KEY `idx_transactions_member_id` (`member_id`),
  KEY `idx_transactions_loan_id` (`loan_id`),
  KEY `idx_transactions_savings_id` (`savings_id`),
  KEY `idx_transactions_type` (`transaction_type`),
  KEY `idx_transactions_date` (`payment_date`),
  KEY `idx_transactions_staff_id` (`staff_id`),
  CONSTRAINT `payment_transactions_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `payment_transactions_ibfk_2` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`),
  CONSTRAINT `payment_transactions_ibfk_3` FOREIGN KEY (`savings_id`) REFERENCES `savings` (`id`),
  CONSTRAINT `payment_transactions_ibfk_4` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIELD OPERATIONS TABLES
-- =====================================================

-- GPS tracking table
CREATE TABLE `gps_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `accuracy` decimal(5,2) DEFAULT NULL,
  `altitude` decimal(8,2) DEFAULT NULL,
  `speed` decimal(5,2) DEFAULT NULL,
  `heading` decimal(5,2) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `battery_level` int(11) DEFAULT NULL,
  `location_source` enum('gps','network','passive') DEFAULT 'gps',
  `visit_purpose` varchar(100) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_gps_staff_id` (`staff_id`),
  KEY `idx_gps_timestamp` (`timestamp`),
  KEY `idx_gps_member_id` (`member_id`),
  CONSTRAINT `gps_tracking_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`),
  CONSTRAINT `gps_tracking_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Field visits table
CREATE TABLE `field_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `visit_date` date NOT NULL,
  `visit_time` time DEFAULT NULL,
  `visit_type` enum('collection','follow_up','survey','new_member','documentation') DEFAULT 'collection',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `visit_notes` text DEFAULT NULL,
  `collection_amount` decimal(15,2) DEFAULT NULL,
  `next_visit_date` date DEFAULT NULL,
  `visit_status` enum('scheduled','completed','cancelled','rescheduled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_visits_staff_id` (`staff_id`),
  KEY `idx_visits_member_id` (`member_id`),
  KEY `idx_visits_date` (`visit_date`),
  KEY `idx_visits_status` (`visit_status`),
  CONSTRAINT `field_visits_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`),
  CONSTRAINT `field_visits_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DYNAMIC DASHBOARD SYSTEM TABLES
-- =====================================================

-- Dashboard pages configuration
CREATE TABLE `dashboard_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_key` varchar(100) NOT NULL UNIQUE,
  `page_title` varchar(200) NOT NULL,
  `page_url` varchar(500) NOT NULL,
  `page_category` varchar(50) NOT NULL DEFAULT 'general',
  `page_icon` varchar(100) DEFAULT NULL,
  `page_description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_pages_key` (`page_key`),
  KEY `idx_pages_category` (`page_category`),
  KEY `idx_pages_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Navigation menu configuration
CREATE TABLE `navigation_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_key` varchar(100) NOT NULL UNIQUE,
  `menu_title` varchar(200) NOT NULL,
  `menu_url` varchar(500) DEFAULT NULL,
  `menu_category` varchar(50) NOT NULL DEFAULT 'general',
  `menu_icon` varchar(100) DEFAULT NULL,
  `parent_menu_key` varchar(100) DEFAULT NULL,
  `menu_description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_menu_key` (`menu_key`),
  KEY `idx_menu_category` (`menu_category`),
  KEY `idx_menu_active` (`is_active`),
  KEY `idx_menu_parent` (`parent_menu_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role-page assignments
CREATE TABLE `role_dashboard_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_key` varchar(50) NOT NULL,
  `page_key` varchar(100) NOT NULL,
  `access_level` enum('read','write','admin') DEFAULT 'read',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_role_page` (`role_key`,`page_key`),
  KEY `idx_role_key` (`role_key`),
  KEY `idx_page_key` (`page_key`),
  CONSTRAINT `role_dashboard_pages_ibfk_1` FOREIGN KEY (`page_key`) REFERENCES `dashboard_pages` (`page_key`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role-menu assignments
CREATE TABLE `role_navigation_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_key` varchar(50) NOT NULL,
  `menu_key` varchar(100) NOT NULL,
  `access_level` enum('read','write','admin') DEFAULT 'read',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_role_menu` (`role_key`,`menu_key`),
  KEY `idx_role_key` (`role_key`),
  KEY `idx_menu_key` (`menu_key`),
  CONSTRAINT `role_navigation_menu_ibfk_1` FOREIGN KEY (`menu_key`) REFERENCES `navigation_menu` (`menu_key`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- GUARANTEE & RISK MANAGEMENT
-- =====================================================

-- Guarantees table
CREATE TABLE `guarantees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guarantee_number` varchar(20) NOT NULL UNIQUE,
  `loan_id` int(11) NOT NULL,
  `guarantor_id` int(11) DEFAULT NULL,
  `guarantee_type` enum('personal','collateral','insurance') DEFAULT 'personal',
  `collateral_type` varchar(50) DEFAULT NULL,
  `collateral_value` decimal(15,2) DEFAULT NULL,
  `collateral_description` text DEFAULT NULL,
  `guarantee_amount` decimal(15,2) NOT NULL,
  `guarantee_status` enum('active','released','claimed') DEFAULT 'active',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_guarantees_loan_id` (`loan_id`),
  KEY `idx_guarantees_guarantor_id` (`guarantor_id`),
  KEY `idx_guarantees_status` (`guarantee_status`),
  CONSTRAINT `guarantees_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`),
  CONSTRAINT `guarantees_ibfk_2` FOREIGN KEY (`guarantor_id`) REFERENCES `members` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Risk assessment table
CREATE TABLE `risk_assessment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `loan_id` int(11) DEFAULT NULL,
  `assessment_date` date NOT NULL,
  `risk_score` decimal(5,2) NOT NULL,
  `risk_level` enum('low','medium','high','very_high') NOT NULL,
  `credit_history_score` decimal(5,2) DEFAULT NULL,
  `income_stability_score` decimal(5,2) DEFAULT NULL,
  `collateral_coverage` decimal(5,2) DEFAULT NULL,
  `payment_history_score` decimal(5,2) DEFAULT NULL,
  `assessment_notes` text DEFAULT NULL,
  `assessed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_risk_member_id` (`member_id`),
  KEY `idx_risk_loan_id` (`loan_id`),
  KEY `idx_risk_level` (`risk_level`),
  KEY `idx_risk_date` (`assessment_date`),
  CONSTRAINT `risk_assessment_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `risk_assessment_ibfk_2` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`),
  CONSTRAINT `risk_assessment_ibfk_3` FOREIGN KEY (`assessed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SYSTEM ADMINISTRATION
-- =====================================================

-- Audit logs
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_action` (`user_id`,`action`),
  KEY `idx_table_record` (`table_name`,`record_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System settings
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `setting_category` varchar(50) DEFAULT 'general',
  `description` text DEFAULT NULL,
  `is_editable` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_setting_key` (`setting_key`),
  KEY `idx_setting_category` (`setting_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI Models table
CREATE TABLE `ai_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(100) NOT NULL,
  `model_type` varchar(50) NOT NULL,
  `version` varchar(20) NOT NULL,
  `accuracy` decimal(5,4) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERT INITIAL DATA
-- =====================================================

-- Insert default users
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `role`) VALUES
('creator', 'creator@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Creator', 'creator'),
('owner', 'owner@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Business Owner', 'owner'),
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin'),
('supervisor', 'supervisor@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Field Supervisor', 'supervisor'),
('teller', 'teller@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Teller', 'teller'),
('member', 'member@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Regular Member', 'member');

-- Insert dashboard pages
INSERT INTO `dashboard_pages` (`page_key`, `page_title`, `page_url`, `page_category`, `page_icon`, `sort_order`) VALUES
('creator_dashboard', 'System Dashboard', 'pages/creator/dashboard.html', 'creator', 'fas fa-tachometer-alt', 1),
('creator_database', 'Database Management', 'pages/creator/database.html', 'creator', 'fas fa-database', 2),
('creator_system', 'System Configuration', 'pages/creator/system.html', 'creator', 'fas fa-cogs', 3),
('creator_deployment', 'Deployment Manager', 'pages/creator/deployment.html', 'creator', 'fas fa-rocket', 4),

('owner_dashboard', 'Business Dashboard', 'pages/owner/dashboard.html', 'owner', 'fas fa-chart-line', 1),
('owner_analytics', 'Business Analytics', 'pages/owner/analytics.html', 'owner', 'fas fa-chart-pie', 2),
('owner_strategic', 'Strategic Planning', 'pages/owner/strategic.html', 'owner', 'fas fa-chess', 3),

('manager_dashboard', 'Operations Dashboard', 'pages/manager/dashboard.html', 'manager', 'fas fa-tachometer-alt', 1),
('manager_staff', 'Staff Management', 'pages/manager/staff.html', 'manager', 'fas fa-users', 2),
('manager_operations', 'Operations Management', 'pages/manager/operations.html', 'manager', 'fas fa-cogs', 3),
('manager_compliance', 'Compliance & Risk', 'pages/manager/compliance.html', 'manager', 'fas fa-shield-alt', 4),

('super_admin_dashboard', 'IT Dashboard', 'pages/super_admin/dashboard.html', 'super_admin', 'fas fa-tachometer-alt', 1),
('super_admin_system', 'System Administration', 'pages/super_admin/system.html', 'super_admin', 'fas fa-server', 2),
('super_admin_security', 'Security Management', 'pages/super_admin/security.html', 'super_admin', 'fas fa-lock', 3),
('super_admin_backup', 'Backup & Recovery', 'pages/super_admin/backup.html', 'super_admin', 'fas fa-save', 4),

('finance_dashboard', 'Finance Dashboard', 'pages/admin/dashboard.html', 'admin', 'fas fa-tachometer-alt', 1),
('finance_reports', 'Financial Reports', 'pages/admin/reports.html', 'admin', 'fas fa-chart-bar', 2),
('finance_budget', 'Budget Management', 'pages/admin/budget.html', 'admin', 'fas fa-wallet', 3),
('finance_shu', 'SHU Distribution', 'pages/admin/shu.html', 'admin', 'fas fa-coins', 4),

('supervisor_dashboard', 'Supervisor Dashboard', 'pages/staff/dashboard.html', 'supervisor', 'fas fa-tachometer-alt', 1),
('supervisor_gps', 'GPS Tracking', 'pages/staff/gps.html', 'supervisor', 'fas fa-map-marked-alt', 2),
('supervisor_targets', 'Collection Targets', 'pages/staff/targets.html', 'supervisor', 'fas fa-bullseye', 3),
('supervisor_reports', 'Field Reports', 'pages/staff/reports.html', 'supervisor', 'fas fa-file-alt', 4),

('teller_dashboard', 'Teller Dashboard', 'pages/teller/dashboard.html', 'teller', 'fas fa-tachometer-alt', 1),
('teller_transactions', 'Transaction Processing', 'pages/teller/transactions.html', 'teller', 'fas fa-exchange-alt', 2),
('teller_members', 'Member Services', 'pages/teller/members.html', 'teller', 'fas fa-users', 3),

('member_dashboard', 'Member Dashboard', 'pages/member/dashboard.html', 'member', 'fas fa-tachometer-alt', 1),
('member_loans', 'My Loans', 'pages/member/loans.html', 'member', 'fas fa-hand-holding-usd', 2),
('member_savings', 'My Savings', 'pages/member/savings.html', 'member', 'fas fa-piggy-bank', 3),
('member_profile', 'My Profile', 'pages/member/profile.html', 'member', 'fas fa-user', 4);

-- Insert navigation menu
INSERT INTO `navigation_menu` (`menu_key`, `menu_title`, `menu_url`, `menu_category`, `menu_icon`, `sort_order`) VALUES
('dashboard', 'Dashboard', NULL, 'main', 'fas fa-tachometer-alt', 1),
('members', 'Members', 'pages/members/list.html', 'main', 'fas fa-users', 2),
('loans', 'Loans', 'pages/loans/list.html', 'main', 'fas fa-hand-holding-usd', 3),
('savings', 'Savings', 'pages/savings/list.html', 'main', 'fas fa-piggy-bank', 4),
('transactions', 'Transactions', 'pages/transactions/list.html', 'main', 'fas fa-exchange-alt', 5),
('reports', 'Reports', 'pages/reports/index.html', 'main', 'fas fa-chart-bar', 6),
('analytics', 'Analytics', 'pages/analytics/index.html', 'main', 'fas fa-chart-line', 7),
('settings', 'Settings', 'pages/settings/index.html', 'main', 'fas fa-cog', 8),
('users', 'User Management', 'pages/users/list.html', 'admin', 'fas fa-user-cog', 9),
('roles', 'Role Management', 'pages/roles/list.html', 'admin', 'fas fa-user-shield', 10),
('audit', 'Audit Logs', 'pages/audit/index.html', 'admin', 'fas fa-history', 11);

-- Insert role-page assignments
INSERT INTO `role_dashboard_pages` (`role_key`, `page_key`, `access_level`, `sort_order`) VALUES
-- Creator
('creator', 'creator_dashboard', 'admin', 1),
('creator', 'creator_database', 'admin', 2),
('creator', 'creator_system', 'admin', 3),
('creator', 'creator_deployment', 'admin', 4),

-- Owner
('owner', 'owner_dashboard', 'admin', 1),
('owner', 'owner_analytics', 'admin', 2),
('owner', 'owner_strategic', 'admin', 3),

-- Manager
('manager', 'manager_dashboard', 'admin', 1),
('manager', 'manager_staff', 'write', 2),
('manager', 'manager_operations', 'write', 3),
('manager', 'manager_compliance', 'read', 4),

-- Super Admin
('super_admin', 'super_admin_dashboard', 'admin', 1),
('super_admin', 'super_admin_system', 'admin', 2),
('super_admin', 'super_admin_security', 'admin', 3),
('super_admin', 'super_admin_backup', 'admin', 4),

-- Admin (Finance Manager)
('admin', 'finance_dashboard', 'admin', 1),
('admin', 'finance_reports', 'write', 2),
('admin', 'finance_budget', 'write', 3),
('admin', 'finance_shu', 'read', 4),

-- Supervisor
('supervisor', 'supervisor_dashboard', 'admin', 1),
('supervisor', 'supervisor_gps', 'write', 2),
('supervisor', 'supervisor_targets', 'read', 3),
('supervisor', 'supervisor_reports', 'write', 4),

-- Teller
('teller', 'teller_dashboard', 'admin', 1),
('teller', 'teller_transactions', 'write', 2),
('teller', 'teller_members', 'read', 3),

-- Member
('member', 'member_dashboard', 'admin', 1),
('member', 'member_loans', 'read', 2),
('member', 'member_savings', 'read', 3),
('member', 'member_profile', 'write', 4);

-- Insert role-menu assignments
INSERT INTO `role_navigation_menu` (`role_key`, `menu_key`, `access_level`, `sort_order`) VALUES
-- All roles get dashboard
('creator', 'dashboard', 'read', 1),
('owner', 'dashboard', 'read', 1),
('manager', 'dashboard', 'read', 1),
('super_admin', 'dashboard', 'read', 1),
('admin', 'dashboard', 'read', 1),
('supervisor', 'dashboard', 'read', 1),
('teller', 'dashboard', 'read', 1),
('member', 'dashboard', 'read', 1),

-- Creator gets all menus
('creator', 'members', 'admin', 2),
('creator', 'loans', 'admin', 3),
('creator', 'savings', 'admin', 4),
('creator', 'transactions', 'admin', 5),
('creator', 'reports', 'admin', 6),
('creator', 'analytics', 'admin', 7),
('creator', 'settings', 'admin', 8),
('creator', 'users', 'admin', 9),
('creator', 'roles', 'admin', 10),
('creator', 'audit', 'admin', 11),

-- Owner gets main menus
('owner', 'members', 'read', 2),
('owner', 'loans', 'read', 3),
('owner', 'savings', 'read', 4),
('owner', 'transactions', 'read', 5),
('owner', 'reports', 'admin', 6),
('owner', 'analytics', 'admin', 7),
('owner', 'settings', 'read', 8),

-- Admin gets operational menus
('admin', 'members', 'write', 2),
('admin', 'loans', 'write', 3),
('admin', 'savings', 'write', 4),
('admin', 'transactions', 'write', 5),
('admin', 'reports', 'admin', 6),
('admin', 'analytics', 'read', 7),
('admin', 'settings', 'write', 8),
('admin', 'users', 'admin', 9),
('admin', 'roles', 'read', 10),
('admin', 'audit', 'read', 11),

-- Supervisor gets operational menus
('supervisor', 'members', 'read', 2),
('supervisor', 'loans', 'read', 3),
('supervisor', 'savings', 'read', 4),
('supervisor', 'transactions', 'read', 5),
('supervisor', 'reports', 'write', 6),
('supervisor', 'analytics', 'read', 7),

-- Teller gets limited menus
('teller', 'members', 'read', 2),
('teller', 'loans', 'read', 3),
('teller', 'savings', 'read', 4),
('teller', 'transactions', 'write', 5),
('teller', 'reports', 'read', 6),

-- Member gets limited menus
('member', 'loans', 'read', 2),
('member', 'savings', 'read', 3),
('member', 'transactions', 'read', 4);

-- Insert system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_category`, `description`) VALUES
('app_name', 'KSP Lam Gabe Jaya', 'string', 'general', 'Application name'),
('app_version', '4.0', 'string', 'general', 'Application version'),
('default_interest_rate', '2.50', 'number', 'loans', 'Default loan interest rate (%)'),
('max_loan_amount', '10000000', 'number', 'loans', 'Maximum loan amount'),
('min_savings_deposit', '10000', 'number', 'savings', 'Minimum savings deposit'),
('gps_tracking_enabled', 'true', 'boolean', 'gps', 'Enable GPS tracking for field staff'),
('backup_retention_days', '30', 'number', 'backup', 'Number of days to keep backups'),
('session_timeout', '3600', 'number', 'security', 'Session timeout in seconds'),
('max_login_attempts', '5', 'number', 'security', 'Maximum login attempts before lockout'),
('password_min_length', '8', 'number', 'security', 'Minimum password length');

-- =====================================================
-- FINAL SETUP
-- =====================================================

-- Create indexes for better performance
CREATE INDEX idx_members_search ON members(full_name, member_number);
CREATE INDEX idx_loans_search ON loans(loan_number, member_id, loan_status);
CREATE INDEX idx_transactions_search ON payment_transactions(transaction_number, member_id, payment_date);
CREATE INDEX idx_gps_search ON gps_tracking(staff_id, timestamp);
CREATE INDEX idx_visits_search ON field_visits(staff_id, visit_date, visit_status);

-- Create view for loan summary
CREATE VIEW loan_summary AS
SELECT 
    l.loan_number,
    l.member_id,
    m.full_name as member_name,
    l.principal_amount,
    l.interest_rate,
    l.monthly_payment,
    l.loan_status,
    l.application_date,
    l.disbursement_date,
    (SELECT SUM(amount) FROM payment_transactions pt WHERE pt.loan_id = l.id AND pt.transaction_type = 'loan_payment') as total_paid,
    (l.total_payment - COALESCE((SELECT SUM(amount) FROM payment_transactions pt WHERE pt.loan_id = l.id AND pt.transaction_type = 'loan_payment'), 0)) as remaining_balance
FROM loans l
JOIN members m ON l.member_id = m.id;

-- Create view for member savings summary
CREATE VIEW member_savings_summary AS
SELECT 
    s.account_number,
    s.member_id,
    m.full_name as member_name,
    s.savings_type,
    s.balance,
    s.account_status,
    s.opening_date,
    (SELECT SUM(amount) FROM payment_transactions pt WHERE pt.savings_id = s.id AND pt.transaction_type = 'savings_deposit') as total_deposits,
    (SELECT SUM(amount) FROM payment_transactions pt WHERE pt.savings_id = s.id AND pt.transaction_type = 'savings_withdrawal') as total_withdrawals
FROM savings s
JOIN members m ON s.member_id = m.id;

-- =====================================================
-- COMPLETION MESSAGE
-- =====================================================

SELECT 'KSP Lam Gabe Jaya Database Setup Complete!' as status,
       COUNT(*) as total_tables
FROM information_schema.tables 
WHERE table_schema = 'ksp_lamgabejaya_v2';

SELECT 'Dashboard System Setup Complete!' as status,
       COUNT(*) as total_pages
FROM dashboard_pages;

SELECT 'Navigation System Setup Complete!' as status,
       COUNT(*) as total_menu_items
FROM navigation_menu;

SELECT 'Role Permission Setup Complete!' as status,
       COUNT(*) as total_assignments
FROM role_dashboard_pages
UNION ALL
SELECT 'Role Permission Setup Complete!' as status,
       COUNT(*) as total_assignments
FROM role_navigation_menu;
