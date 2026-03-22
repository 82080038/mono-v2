-- KSP Lam Gabe Jaya - Initial Data
-- Database: gabe

-- ========================================
-- 1. INSERT INITIAL USERS
-- ========================================
INSERT INTO `users` (`username`, `password`, `email`, `full_name`, `role`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@ksplamgabejaya.co.id', 'Administrator KSP', 'admin', 'active'),
('manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager@ksplamgabejaya.co.id', 'Manager KSP', 'manager', 'active'),
('staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff@ksplamgabejaya.co.id', 'Staff KSP', 'staff', 'active'),
('member001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member001@ksplamgabejaya.co.id', 'Ahmad Wijaya', 'member', 'active'),
('member002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member002@ksplamgabejaya.co.id', 'Siti Nurhaliza', 'member', 'active');

-- ========================================
-- 2. INSERT MEMBERS
-- ========================================
INSERT INTO `members` (`user_id`, `member_number`, `nik`, `full_name`, `birth_date`, `birth_place`, `gender`, `address`, `phone`, `email`, `join_date`, `status`) VALUES
(4, 'M001', '3201011234560001', 'Ahmad Wijaya', '1985-05-15', 'Jakarta', 'L', 'Jl. Merdeka No. 123, Jakarta Pusat', '08123456789', 'member001@ksplamgabejaya.co.id', '2024-01-15', 'active'),
(5, 'M002', '3201011234560002', 'Siti Nurhaliza', '1990-08-22', 'Bandung', 'P', 'Jl. Sudirman No. 456, Bandung', '08234567890', 'member002@ksplamgabejaya.co.id', '2024-02-20', 'active');

-- ========================================
-- 3. INSERT ACCOUNTS
-- ========================================
INSERT INTO `accounts` (`member_id`, `account_number`, `account_type`, `account_name`, `balance`, `interest_rate`, `status`, `opened_date`) VALUES
(1, 'A001', 'simpanan', 'Tabungan Wajib - Ahmad Wijaya', 500000.00, 3.00, 'active', '2024-01-15'),
(1, 'A002', 'simpanan', 'Tabungan Sukarela - Ahmad Wijaya', 1000000.00, 2.50, 'active', '2024-01-15'),
(2, 'A003', 'simpanan', 'Tabungan Wajib - Siti Nurhaliza', 500000.00, 3.00, 'active', '2024-02-20'),
(2, 'A004', 'simpanan', 'Tabungan Sukarela - Siti Nurhaliza', 750000.00, 2.50, 'active', '2024-02-20');

-- ========================================
-- 4. INSERT SAMPLE TRANSACTIONS
-- ========================================
INSERT INTO `transactions` (`transaction_code`, `account_id`, `transaction_type`, `amount`, `description`, `reference_number`, `transaction_date`, `created_by`) VALUES
('TRX001', 1, 'credit', 500000.00, 'Setoran Awal Tabungan Wajib', 'SET001', '2024-01-15', 1),
('TRX002', 2, 'credit', 1000000.00, 'Setoran Awal Tabungan Sukarela', 'SET002', '2024-01-15', 1),
('TRX003', 3, 'credit', 500000.00, 'Setoran Awal Tabungan Wajib', 'SET003', '2024-02-20', 1),
('TRX004', 4, 'credit', 750000.00, 'Setoran Awal Tabungan Sukarela', 'SET004', '2024-02-20', 1),
('TRX005', 1, 'credit', 100000.00, 'Setoran Tambahan', 'SET005', '2024-03-01', 1),
('TRX006', 2, 'credit', 200000.00, 'Setoran Tambahan', 'SET006', '2024-03-05', 1);

-- ========================================
-- 5. INSERT SAMPLE LOANS
-- ========================================
INSERT INTO `loans` (`member_id`, `loan_number`, `loan_amount`, `interest_rate`, `loan_term`, `purpose`, `status`, `application_date`, `approval_date`, `disbursement_date`, `due_date`, `approved_by`) VALUES
(1, 'L001', 5000000.00, 12.00, 12, 'Modal usaha kecil', 'active', '2024-02-01', '2024-02-05', '2024-02-06', '2025-02-05', 2),
(2, 'L002', 3000000.00, 10.00, 6, 'Biaya pendidikan', 'active', '2024-03-01', '2024-03-03', '2024-03-04', '2024-09-03', 2);

-- ========================================
-- 6. INSERT LOAN PAYMENTS
-- ========================================
INSERT INTO `loan_payments` (`loan_id`, `payment_number`, `amount`, `principal_amount`, `interest_amount`, `payment_date`, `payment_method`, `received_by`, `notes`) VALUES
(1, 1, 466666.67, 416666.67, 50000.00, '2024-03-06', 'cash', 3, 'Angsuran bulan Maret'),
(2, 1, 516666.67, 500000.00, 16666.67, '2024-04-04', 'transfer', 3, 'Angsuran bulan April');

-- ========================================
-- 7. INSERT SAVINGS RECORDS
-- ========================================
INSERT INTO `savings` (`member_id`, `savings_type`, `amount`, `transaction_date`, `description`, `created_by`) VALUES
(1, 'wajib', 500000.00, '2024-01-15', 'Setoran awal simpanan wajib', 1),
(1, 'sukarela', 1000000.00, '2024-01-15', 'Setoran awal simpanan sukarela', 1),
(2, 'wajib', 500000.00, '2024-02-20', 'Setoran awal simpanan wajib', 1),
(2, 'sukarela', 750000.00, '2024-02-20', 'Setoran awal simpanan sukarela', 1),
(1, 'sukarela', 100000.00, '2024-03-01', 'Setoran tambahan', 1),
(2, 'sukarela', 200000.00, '2024-03-05', 'Setoran tambahan', 1);

-- ========================================
-- 8. INSERT SYSTEM CONFIGURATION
-- ========================================
INSERT INTO `system_config` (`config_key`, `config_value`, `config_type`, `description`) VALUES
('ksp_name', 'KSP Lam Gabe Jaya', 'string', 'Nama Koperasi'),
('ksp_address', 'Jl. Koperasi No. 123, Jakarta', 'string', 'Alamat Koperasi'),
('ksp_phone', '021-12345678', 'string', 'Nomor Telepon'),
('ksp_email', 'info@ksplamgabejaya.co.id', 'string', 'Email Koperasi'),
('savings_wajib_minimum', 500000, 'number', 'Minimal simpanan wajib per bulan'),
('savings_pokok_minimum', 1000000, 'number', 'Minimal simpanan pokok'),
('loan_interest_min', 5.00, 'number', 'Bunga pinjaman minimal (%)'),
('loan_interest_max', 18.00, 'number', 'Bunga pinjaman maksimal (%)'),
('loan_term_max', 36, 'number', 'Jangka waktu pinjaman maksimal (bulan)'),
('late_payment_fee', 2.00, 'number', 'Denda keterlambatan (%)'),
('session_timeout', 30, 'number', 'Session timeout (menit)'),
('max_login_attempts', 5, 'number', 'Maksimal percobaan login'),
('lockout_duration', 15, 'number', 'Durasi lockout (menit)'),
('enable_notifications', 'true', 'boolean', 'Aktifkan notifikasi'),
('enable_audit_log', 'true', 'boolean', 'Aktifkan audit log');

-- ========================================
-- 9. INSERT SAMPLE AUDIT LOGS
-- ========================================
INSERT INTO `audit_logs` (`user_id`, `action`, `table_name`, `record_id`, `new_values`, `ip_address`, `user_agent`) VALUES
(1, 'CREATE', 'users', 1, '{"username":"admin","role":"admin","status":"active"}', '127.0.0.1', 'Mozilla/5.0 (System Initializer)'),
(1, 'CREATE', 'members', 1, '{"member_number":"M001","full_name":"Ahmad Wijaya","status":"active"}', '127.0.0.1', 'Mozilla/5.0 (System Initializer)'),
(1, 'CREATE', 'accounts', 1, '{"account_number":"A001","account_type":"simpanan","balance":500000}', '127.0.0.1', 'Mozilla/5.0 (System Initializer)'),
(1, 'CREATE', 'loans', 1, '{"loan_number":"L001","loan_amount":5000000,"status":"active"}', '127.0.0.1', 'Mozilla/5.0 (System Initializer)'),
(2, 'UPDATE', 'loans', 1, '{"status":"approved","approved_by":2}', '127.0.0.1', 'Mozilla/5.0 (Manager Browser)');

-- ========================================
-- 10. CREATE VIEWS FOR REPORTING
-- ========================================

-- View for member summary
CREATE OR REPLACE VIEW `member_summary` AS
SELECT 
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
    COALESCE(SUM(l.loan_amount), 0) as total_loan_amount
FROM members m
LEFT JOIN accounts a ON m.id = a.member_id AND a.status = 'active'
LEFT JOIN loans l ON m.id = l.member_id AND l.status IN ('active', 'completed')
GROUP BY m.id, m.member_number, m.full_name, m.phone, m.email, m.join_date, m.status;

-- View for daily transactions
CREATE OR REPLACE VIEW `daily_transactions` AS
SELECT 
    DATE(t.transaction_date) as transaction_date,
    COUNT(*) as total_transactions,
    SUM(CASE WHEN t.transaction_type = 'credit' THEN t.amount ELSE 0 END) as total_credits,
    SUM(CASE WHEN t.transaction_type = 'debit' THEN t.amount ELSE 0 END) as total_debits,
    SUM(t.amount) as net_amount
FROM transactions t
GROUP BY DATE(t.transaction_date)
ORDER BY transaction_date DESC;

-- View for loan performance
CREATE OR REPLACE VIEW `loan_performance` AS
SELECT 
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
    (l.loan_amount - COALESCE(SUM(lp.amount), 0)) as remaining_balance,
    CASE 
        WHEN l.status = 'completed' THEN 'Lunas'
        WHEN l.status = 'active' AND l.due_date < CURDATE() THEN 'Macet'
        WHEN l.status = 'active' THEN 'Aktif'
        ELSE l.status
    END as payment_status
FROM loans l
JOIN members m ON l.member_id = m.id
LEFT JOIN loan_payments lp ON l.id = lp.loan_id
GROUP BY l.id, l.loan_number, m.full_name, l.loan_amount, l.interest_rate, l.loan_term, l.status, l.application_date, l.disbursement_date;
