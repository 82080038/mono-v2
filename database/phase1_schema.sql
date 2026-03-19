-- KSP Lam Gabe Jaya v2.0 - Phase 1 Database Schema
-- Core Koperasi Foundation

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS ksp_lamgabejaya_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ksp_lamgabejaya_v2;

-- ========================================
-- 1. MEMBER MANAGEMENT TABLES
-- ========================================

-- Member types configuration
CREATE TABLE member_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    min_savings_pokok DECIMAL(15,2) DEFAULT 0,
    min_savings_wajib DECIMAL(15,2) DEFAULT 0,
    max_loan_amount DECIMAL(15,2) DEFAULT 0,
    max_concurrent_loans INT DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default member types
INSERT INTO member_types (name, description, min_savings_pokok, min_savings_wajib, max_loan_amount) VALUES
('Regular', 'Anggota Biasa', 100000, 50000, 5000000),
('Premium', 'Anggota Prioritas', 250000, 100000, 10000000),
('Board', 'Pengurus Koperasi', 500000, 200000, 20000000),
('Honorary', 'Anggota Kehormatan', 0, 0, 0),
('Associate', 'Anggota Associate', 50000, 25000, 2500000);

-- Members table
CREATE TABLE members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_number VARCHAR(20) NOT NULL UNIQUE,
    member_type_id INT NOT NULL,
    title VARCHAR(10),
    full_name VARCHAR(100) NOT NULL,
    place_of_birth VARCHAR(50),
    date_of_birth DATE,
    gender ENUM('L', 'P') NOT NULL,
    id_number VARCHAR(50) NOT NULL UNIQUE,
    family_card_number VARCHAR(50),
    tax_id_number VARCHAR(30),
    phone_number VARCHAR(20),
    mobile_number VARCHAR(20),
    email VARCHAR(100),
    address TEXT NOT NULL,
    village VARCHAR(50),
    district VARCHAR(50),
    city VARCHAR(50),
    province VARCHAR(50),
    postal_code VARCHAR(10),
    occupation VARCHAR(100),
    company_name VARCHAR(100),
    monthly_income DECIMAL(15,2),
    marital_status ENUM('Single', 'Married', 'Divorced', 'Widowed'),
    spouse_name VARCHAR(100),
    spouse_phone VARCHAR(20),
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    emergency_contact_relation VARCHAR(50),
    photo_path VARCHAR(255),
    signature_path VARCHAR(255),
    status ENUM('Active', 'Inactive', 'Suspended', 'Blacklisted', 'Resigned', 'Deceased') DEFAULT 'Active',
    registration_date DATE NOT NULL,
    activation_date DATE,
    deactivation_date DATE,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (member_type_id) REFERENCES member_types(id),
    INDEX idx_member_number (member_number),
    INDEX idx_id_number (id_number),
    INDEX idx_phone (phone_number),
    INDEX idx_email (email),
    INDEX idx_status (status)
);

-- Member documents
CREATE TABLE member_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    document_type ENUM('KTP', 'KK', 'NPWP', 'Slip Gaji', 'Surat Nikah', 'Other') NOT NULL,
    document_number VARCHAR(50),
    document_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATE,
    is_verified BOOLEAN DEFAULT FALSE,
    verified_by INT,
    verified_date TIMESTAMP NULL,
    notes TEXT,
    
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    INDEX idx_member_document (member_id, document_type)
);

-- Member status history
CREATE TABLE member_status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    old_status ENUM('Active', 'Inactive', 'Suspended', 'Blacklisted', 'Resigned', 'Deceased'),
    new_status ENUM('Active', 'Inactive', 'Suspended', 'Blacklisted', 'Resigned', 'Deceased') NOT NULL,
    reason TEXT,
    changed_by INT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    INDEX idx_member_status (member_id, changed_at)
);

-- ========================================
-- 2. SAVINGS MANAGEMENT TABLES
-- ========================================

-- Account types configuration
CREATE TABLE account_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(15) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    interest_rate DECIMAL(5,4) DEFAULT 0,
    minimum_balance DECIMAL(15,2) DEFAULT 0,
    minimum_deposit DECIMAL(15,2) DEFAULT 0,
    maximum_deposit DECIMAL(15,2) DEFAULT 0,
    withdrawal_fee DECIMAL(15,2) DEFAULT 0,
    is_taxable BOOLEAN DEFAULT FALSE,
    tax_rate DECIMAL(5,4) DEFAULT 0,
    requires_approval BOOLEAN DEFAULT FALSE,
    auto_debit_enabled BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default account types
INSERT INTO account_types (code, name, description, interest_rate, minimum_balance, minimum_deposit, auto_debit_enabled) VALUES
('SA_POKOK', 'Simpanan Pokok', 'Simpanan wajib satu kali', 0, 0, 100000, FALSE),
('SA_WAJIB', 'Simpanan Wajib', 'Simpanan wajib bulanan', 0.002, 0, 50000, TRUE),
('SA_SUKARELA', 'Simpanan Sukarela', 'Simpanan fleksibel', 0.003, 10000, 10000, FALSE),
('SA_BERJANGKA', 'Simpanan Berjangka', 'Simpanan dengan tenor tetap', 0.004, 100000, 100000, FALSE),
('SA_HARI_RAYA', 'Simpanan Hari Raya', 'Simpanan untuk hari raya', 0.003, 50000, 50000, FALSE);

-- Accounts table
CREATE TABLE accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_number VARCHAR(20) NOT NULL UNIQUE,
    member_id INT NOT NULL,
    account_type_id INT NOT NULL,
    account_name VARCHAR(100),
    balance DECIMAL(15,2) DEFAULT 0,
    available_balance DECIMAL(15,2) DEFAULT 0,
    hold_amount DECIMAL(15,2) DEFAULT 0,
    interest_rate DECIMAL(5,4),
    opening_date DATE NOT NULL,
    maturity_date DATE,
    last_interest_date DATE,
    status ENUM('Active', 'Dormant', 'Frozen', 'Closed') DEFAULT 'Active',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (account_type_id) REFERENCES account_types(id),
    INDEX idx_account_number (account_number),
    INDEX idx_member_account (member_id, account_type_id),
    INDEX idx_status (status)
);

-- Account transactions
CREATE TABLE account_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_id INT NOT NULL,
    transaction_type ENUM('Deposit', 'Withdrawal', 'Transfer In', 'Transfer Out', 'Interest', 'Fee', 'Tax') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    balance_before DECIMAL(15,2) NOT NULL,
    balance_after DECIMAL(15,2) NOT NULL,
    description TEXT,
    reference_number VARCHAR(50),
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    teller_id INT,
    approved_by INT,
    approved_date TIMESTAMP NULL,
    is_reversed BOOLEAN DEFAULT FALSE,
    reversed_by INT,
    reversed_date TIMESTAMP NULL,
    reversal_reason TEXT,
    notes TEXT,
    
    FOREIGN KEY (account_id) REFERENCES accounts(id),
    INDEX idx_account_transaction (account_id, transaction_date),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_reference_number (reference_number)
);

-- Auto-debit configuration
CREATE TABLE auto_debit_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    account_id INT NOT NULL,
    debit_amount DECIMAL(15,2) NOT NULL,
    debit_day INT NOT NULL, -- 1-31
    start_date DATE NOT NULL,
    end_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    last_debit_date DATE,
    next_debit_date DATE,
    failed_attempts INT DEFAULT 0,
    max_failed_attempts INT DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (account_id) REFERENCES accounts(id),
    INDEX idx_member_debit (member_id),
    INDEX idx_next_debit (next_debit_date)
);

-- ========================================
-- 3. LOAN MANAGEMENT TABLES
-- ========================================

-- Loan types configuration
CREATE TABLE loan_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(15) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    interest_rate DECIMAL(5,4) NOT NULL,
    admin_fee_rate DECIMAL(5,4) DEFAULT 0,
    late_fee_rate DECIMAL(5,4) DEFAULT 0,
    minimum_amount DECIMAL(15,2) DEFAULT 0,
    maximum_amount DECIMAL(15,2) DEFAULT 0,
    minimum_term_months INT DEFAULT 1,
    maximum_term_months INT DEFAULT 60,
    collateral_required BOOLEAN DEFAULT FALSE,
    guarantee_required BOOLEAN DEFAULT FALSE,
    insurance_required BOOLEAN DEFAULT FALSE,
    calculation_method ENUM('Flat', 'Effective', 'Anuitas') DEFAULT 'Flat',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default loan types
INSERT INTO loan_types (code, name, description, interest_rate, admin_fee_rate, minimum_amount, maximum_amount, collateral_required) VALUES
('KONSUMTIF', 'Pinjaman Konsumtif', 'Pinjaman untuk kebutuhan konsumtif', 0.015, 0.01, 500000, 5000000, TRUE),
('PRODUKTIF', 'Pinjaman Produktif', 'Pinjaman untuk usaha produktif', 0.012, 0.01, 1000000, 20000000, TRUE),
('DARURAT', 'Pinjaman Darurat', 'Pinjaman darurat cepat cair', 0.02, 0.02, 250000, 2000000, FALSE),
('ANGSURAN', 'Pinjaman Angsuran', 'Pinjaman dengan angsuran tetap', 0.013, 0.01, 500000, 10000000, TRUE);

-- Credit scoring criteria
CREATE TABLE credit_scoring_criteria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    weight DECIMAL(5,3) NOT NULL, -- Weight percentage (0-1)
    max_score DECIMAL(5,2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO credit_scoring_criteria (name, description, weight, max_score) VALUES
('Membership Duration', 'Lama keanggotaan', 0.15, 100),
('Savings History', 'Riwayat simpanan', 0.20, 100),
('Previous Loans', 'Riwayat pinjaman sebelumnya', 0.25, 100),
('Income Stability', 'Stabilitas pendapatan', 0.20, 100),
('Collateral Value', 'Nilai jaminan', 0.20, 100);

-- Loans table
CREATE TABLE loans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    loan_number VARCHAR(20) NOT NULL UNIQUE,
    member_id INT NOT NULL,
    loan_type_id INT NOT NULL,
    application_date DATE NOT NULL,
    approval_date DATE,
    disbursement_date DATE,
    amount DECIMAL(15,2) NOT NULL,
    interest_rate DECIMAL(5,4) NOT NULL,
    admin_fee DECIMAL(15,2) DEFAULT 0,
    insurance_fee DECIMAL(15,2) DEFAULT 0,
    term_months INT NOT NULL,
    calculation_method ENUM('Flat', 'Effective', 'Anuitas') NOT NULL,
    monthly_installment DECIMAL(15,2) NOT NULL,
    total_interest DECIMAL(15,2) NOT NULL,
    total_payment DECIMAL(15,2) NOT NULL,
    outstanding_balance DECIMAL(15,2) NOT NULL,
    next_payment_date DATE,
    maturity_date DATE,
    purpose TEXT,
    status ENUM('Applied', 'Approved', 'Rejected', 'Disbursed', 'Active', 'Late', 'Default', 'Restructured', 'Paid Off') DEFAULT 'Applied',
    rejection_reason TEXT,
    approved_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (loan_type_id) REFERENCES loan_types(id),
    INDEX idx_loan_number (loan_number),
    INDEX idx_member_loan (member_id),
    INDEX idx_status (status),
    INDEX idx_application_date (application_date)
);

-- Credit scoring results
CREATE TABLE credit_scoring_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    loan_application_id INT,
    total_score DECIMAL(5,2) NOT NULL,
    risk_level ENUM('Low', 'Medium', 'High', 'Very High') NOT NULL,
    recommendation ENUM('Approve', 'Reject', 'Manual Review') NOT NULL,
    scoring_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    scored_by INT,
    
    FOREIGN KEY (member_id) REFERENCES members(id),
    INDEX idx_member_scoring (member_id),
    INDEX idx_scoring_date (scoring_date)
);

-- Credit scoring details
CREATE TABLE credit_scoring_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    scoring_result_id INT NOT NULL,
    criteria_id INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    notes TEXT,
    
    FOREIGN KEY (scoring_result_id) REFERENCES credit_scoring_results(id),
    FOREIGN KEY (criteria_id) REFERENCES credit_scoring_criteria(id)
);

-- Collateral management
CREATE TABLE collateral (
    id INT PRIMARY KEY AUTO_INCREMENT,
    loan_id INT,
    member_id INT NOT NULL,
    collateral_type ENUM('Property', 'Vehicle', 'Savings', 'Guarantor', 'Other') NOT NULL,
    description TEXT NOT NULL,
    estimated_value DECIMAL(15,2),
    appraisal_value DECIMAL(15,2),
    appraisal_date DATE,
    location TEXT,
    documents TEXT, -- JSON array of document paths
    status ENUM('Active', 'Released', 'Sold', 'Expired') DEFAULT 'Active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (loan_id) REFERENCES loans(id),
    FOREIGN KEY (member_id) REFERENCES members(id),
    INDEX idx_loan_collateral (loan_id),
    INDEX idx_member_collateral (member_id)
);

-- Loan installments
CREATE TABLE loan_installments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    loan_id INT NOT NULL,
    installment_number INT NOT NULL,
    due_date DATE NOT NULL,
    principal_amount DECIMAL(15,2) NOT NULL,
    interest_amount DECIMAL(15,2) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    paid_amount DECIMAL(15,2) DEFAULT 0,
    paid_date DATE,
    late_fee DECIMAL(15,2) DEFAULT 0,
    status ENUM('Pending', 'Paid', 'Late', 'Overdue') DEFAULT 'Pending',
    payment_method VARCHAR(50),
    receipt_number VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (loan_id) REFERENCES loans(id),
    INDEX idx_loan_installment (loan_id, installment_number),
    INDEX idx_due_date (due_date),
    INDEX idx_status (status)
);

-- Loan payments
CREATE TABLE loan_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    loan_id INT NOT NULL,
    payment_amount DECIMAL(15,2) NOT NULL,
    principal_portion DECIMAL(15,2) NOT NULL,
    interest_portion DECIMAL(15,2) NOT NULL,
    late_fee_portion DECIMAL(15,2) DEFAULT 0,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_method ENUM('Cash', 'Bank Transfer', 'Auto Debit', 'Digital Payment') NOT NULL,
    reference_number VARCHAR(50),
    teller_id INT,
    notes TEXT,
    
    FOREIGN KEY (loan_id) REFERENCES loans(id),
    INDEX idx_loan_payment (loan_id, payment_date),
    INDEX idx_payment_date (payment_date),
    INDEX idx_reference_number (reference_number)
);

-- ========================================
-- 4. SYSTEM TABLES
-- ========================================

-- Users table (extended)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('Super Admin', 'Admin', 'Manager', 'Teller', 'Staff') NOT NULL,
    permissions TEXT, -- JSON array of permissions
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    failed_login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Audit log
CREATE TABLE audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values TEXT, -- JSON
    new_values TEXT, -- JSON
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_action (user_id, action),
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_created_at (created_at)
);

-- System settings
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    data_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    is_editable BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key)
);

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description, data_type) VALUES
('company_name', 'KSP Lam Gabe Jaya', 'Nama perusahaan', 'string'),
('company_address', 'Jl. Contoh No. 123, Jakarta', 'Alamat perusahaan', 'string'),
('company_phone', '+62-21-1234567', 'Nomor telepon', 'string'),
('company_email', 'info@ksp-lamgabejaya.co.id', 'Email perusahaan', 'string'),
('interest_savings_rate', '0.003', 'Suku bunga simpanan default', 'number'),
('late_fee_rate', '0.001', 'Denda keterlambatan', 'number'),
('min_credit_score', '50', 'Skor kredit minimum', 'number'),
('max_loan_amount', '50000000', 'Maksimal pinjaman', 'number');

-- ========================================
-- 5. VIEWS FOR REPORTING
-- ========================================

-- Member summary view
CREATE VIEW member_summary AS
SELECT 
    m.id,
    m.member_number,
    m.full_name,
    mt.name as member_type,
    m.phone_number,
    m.email,
    m.status,
    m.registration_date,
    COUNT(DISTINCT a.id) as total_accounts,
    COALESCE(SUM(a.balance), 0) as total_savings,
    COUNT(DISTINCT l.id) as total_loans,
    COALESCE(SUM(l.outstanding_balance), 0) as total_outstanding
FROM members m
LEFT JOIN member_types mt ON m.member_type_id = mt.id
LEFT JOIN accounts a ON m.id = a.member_id AND a.status = 'Active'
LEFT JOIN loans l ON m.id = l.member_id AND l.status IN ('Active', 'Late')
GROUP BY m.id;

-- Loan portfolio view
CREATE VIEW loan_portfolio AS
SELECT 
    lt.name as loan_type,
    COUNT(l.id) as total_loans,
    SUM(l.amount) as total_disbursed,
    SUM(l.outstanding_balance) as total_outstanding,
    AVG(l.interest_rate) as avg_interest_rate,
    COUNT(CASE WHEN l.status = 'Late' THEN 1 END) as late_loans,
    COUNT(CASE WHEN l.status = 'Default' THEN 1 END) as default_loans
FROM loans l
LEFT JOIN loan_types lt ON l.loan_type_id = lt.id
WHERE l.status IN ('Active', 'Late', 'Default')
GROUP BY lt.id, lt.name;

-- Daily transactions view
CREATE VIEW daily_transactions AS
SELECT 
    DATE(at.transaction_date) as transaction_date,
    COUNT(*) as total_transactions,
    SUM(CASE WHEN at.transaction_type IN ('Deposit', 'Transfer In') THEN at.amount ELSE 0 END) as total_deposits,
    SUM(CASE WHEN at.transaction_type IN ('Withdrawal', 'Transfer Out') THEN at.amount ELSE 0 END) as total_withdrawals,
    COUNT(DISTINCT at.account_id) as active_accounts
FROM account_transactions at
GROUP BY DATE(at.transaction_date)
ORDER BY transaction_date DESC;

-- ========================================
-- 6. TRIGGERS
-- ========================================

-- Trigger to update account balance
DELIMITER //
CREATE TRIGGER update_account_balance 
AFTER INSERT ON account_transactions
FOR EACH ROW
BEGIN
    UPDATE accounts 
    SET balance = NEW.balance_after,
        available_balance = CASE 
            WHEN NEW.transaction_type IN ('Withdrawal', 'Transfer Out') 
            THEN balance - hold_amount 
            ELSE available_balance 
        END,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = NEW.account_id;
END//
DELIMITER ;

-- ========================================
-- 7. SAMPLE DATA (for testing)
-- ========================================

-- Insert sample users
INSERT INTO users (username, password, email, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@ksp-lamgabejaya.co.id', 'Administrator', 'Super Admin'),
('teller1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teller1@ksp-lamgabejaya.co.id', 'Teller Satu', 'Teller'),
('manager1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager1@ksp-lamgabejaya.co.id', 'Manager Satu', 'Manager');

-- Create indexes for performance
CREATE INDEX idx_members_full_name ON members(full_name);
CREATE INDEX idx_accounts_balance ON accounts(balance);
CREATE INDEX idx_loans_amount ON loans(amount);
CREATE INDEX idx_transactions_amount ON account_transactions(amount);

-- Set foreign key checks
SET FOREIGN_KEY_CHECKS = 0;
SET FOREIGN_KEY_CHECKS = 1;
