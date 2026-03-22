# Database Guide

## 🎯 Overview

Dokumentasi lengkap untuk database KSP Lam Gabe Jaya. Guide ini mencakup desain database, struktur tabel, relasi data, optimasi performa, backup & recovery, dan best practices.

## 📋 Table of Contents

- [Database Design](#database-design)
- [Table Structure](#table-structure)
- [Relationships](#relationships)
- [Data Types](#data-types)
- [Indexes](#indexes)
- [Stored Procedures](#stored-procedures)
- [Triggers](#triggers)
- [Views](#views)
- [Performance Optimization](#performance-optimization)
- [Backup & Recovery](#backup--recovery)
- [Migration](#migration)
- [Security](#security)
- [Monitoring](#monitoring)

---

## 🏗️ Database Design

### **Database Schema Overview**

```
ksp_lamgabejaya_v2/
├── users/                    # User management
├── members/                  # Member data
├── accounts/                 # Account information
├── transactions/             # Financial transactions
├── loans/                    # Loan management
├── deposits/                 # Deposit management
├── payments/                 # Payment records
├── reports/                  # Report data
├── audit_logs/               # Audit trail
├── system_logs/              # System logs
└── configurations/           # System configuration
```

### **ERD Overview**

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│    Users     │    │   Members   │    │  Accounts   │
├─────────────┤    ├─────────────┤    ├─────────────┤
│ id (PK)     │───<│ id (PK)     │───<│ id (PK)     │
│ username   │    │ user_id (FK)│    │ member_id(FK)│
│ email      │    │ nik         │    │ account_no  │
│ password   │    │ name        │    │ balance     │
│ role       │    │ phone       │    │ status      │
│ status     │    │ email       │    │ created_at  │
│ created_at │    │ address     │    │ updated_at  │
│ updated_at │    │ status      │    └─────────────┘
└─────────────┘    │ created_at │
                   │ updated_at │
                   └─────────────┘
                          │
                          │
                   ┌─────────────┐
                   │Transactions │
                   ├─────────────┤
                   │ id (PK)     │
                   │ account_id(FK)│
                   │ type        │
                   │ amount      │
                   │ description │
                   │ status      │
                   │ created_by  │
                   │ created_at  │
                   └─────────────┘
```

---

## 📊 Table Structure

### **Users Table**

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'staff', 'member') NOT NULL DEFAULT 'member',
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    last_login DATETIME NULL,
    failed_login_attempts INT DEFAULT 0,
    locked_until DATETIME NULL,
    email_verified_at DATETIME NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### **Members Table**

```sql
CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NULL,
    nik VARCHAR(16) UNIQUE NOT NULL,
    member_no VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) UNIQUE NULL,
    birth_date DATE NULL,
    birth_place VARCHAR(100) NULL,
    gender ENUM('Laki-laki', 'Perempuan') NULL,
    marital_status ENUM('Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati') NULL,
    occupation VARCHAR(100) NULL,
    education VARCHAR(50) NULL,
    address TEXT NULL,
    village VARCHAR(100) NULL,
    district VARCHAR(100) NULL,
    city VARCHAR(100) NULL,
    province VARCHAR(100) NULL,
    postal_code VARCHAR(5) NULL,
    photo_path VARCHAR(255) NULL,
    id_card_path VARCHAR(255) NULL,
    status ENUM('active', 'inactive', 'pending', 'blacklisted') NOT NULL DEFAULT 'active',
    join_date DATE NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_nik (nik),
    INDEX idx_member_no (member_no),
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### **Accounts Table**

```sql
CREATE TABLE accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    account_no VARCHAR(20) UNIQUE NOT NULL,
    account_type ENUM('simpanan_wajib', 'simpanan_sukarela', 'simpanan_berjangka') NOT NULL,
    balance DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    frozen_balance DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    interest_rate DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
    maturity_date DATE NULL,
    status ENUM('active', 'inactive', 'frozen', 'closed') NOT NULL DEFAULT 'active',
    opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL,
    last_transaction_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    INDEX idx_member_id (member_id),
    INDEX idx_account_no (account_no),
    INDEX idx_account_type (account_type),
    INDEX idx_status (status),
    INDEX idx_opened_at (opened_at),
    INDEX idx_balance (balance)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### **Transactions Table**

```sql
CREATE TABLE transactions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    transaction_code VARCHAR(50) UNIQUE NOT NULL,
    account_id INT NOT NULL,
    transaction_type ENUM('deposit', 'withdrawal', 'transfer_in', 'transfer_out', 'loan_disbursement', 'loan_payment', 'interest_payment', 'fee_payment', 'penalty_payment') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description VARCHAR(255) NULL,
    reference_no VARCHAR(100) NULL,
    status ENUM('pending', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_by INT NOT NULL,
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    processed_at TIMESTAMP NULL,
    notes TEXT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_transaction_code (transaction_code),
    INDEX idx_account_id (account_id),
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_status (status),
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at),
    INDEX idx_amount (amount),
    INDEX idx_reference_no (reference_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### **Loans Table**

```sql
CREATE TABLE loans (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    loan_code VARCHAR(50) UNIQUE NOT NULL,
    member_id INT NOT NULL,
    account_id INT NOT NULL,
    principal_amount DECIMAL(15,2) NOT NULL,
    interest_rate DECIMAL(5,4) NOT NULL,
    tenure_months INT NOT NULL,
    monthly_payment DECIMAL(15,2) NOT NULL,
    total_payment DECIMAL(15,2) NOT NULL,
    total_interest DECIMAL(15,2) NOT NULL,
    purpose VARCHAR(255) NULL,
    collateral VARCHAR(255) NULL,
    collateral_value DECIMAL(15,2) NULL,
    guarantor_name VARCHAR(255) NULL,
    guarantor_phone VARCHAR(20) NULL,
    guarantor_address TEXT NULL,
    status ENUM('pending', 'approved', 'active', 'completed', 'defaulted', 'cancelled') NOT NULL DEFAULT 'pending',
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    disbursed_at TIMESTAMP NULL,
    first_payment_date DATE NULL,
    last_payment_date DATE NULL,
    next_payment_date DATE NULL,
    overdue_days INT DEFAULT 0,
    penalty_rate DECIMAL(5,4) DEFAULT 0.0000,
    penalty_amount DECIMAL(15,2) DEFAULT 0.00,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE RESTRICT,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_loan_code (loan_code),
    INDEX idx_member_id (member_id),
    INDEX idx_account_id (account_id),
    INDEX idx_status (status),
    INDEX idx_approved_at (approved_at),
    INDEX idx_next_payment_date (next_payment_date),
    INDEX idx_overdue_days (overdue_days),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### **Loan Payments Table**

```sql
CREATE TABLE loan_payments (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    loan_id BIGINT NOT NULL,
    payment_no INT NOT NULL,
    due_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    principal_amount DECIMAL(15,2) NOT NULL,
    interest_amount DECIMAL(15,2) NOT NULL,
    penalty_amount DECIMAL(15,2) DEFAULT 0.00,
    paid_amount DECIMAL(15,2) DEFAULT 0.00,
    paid_principal DECIMAL(15,2) DEFAULT 0.00,
    paid_interest DECIMAL(15,2) DEFAULT 0.00,
    paid_penalty DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('pending', 'partial', 'paid', 'overdue') NOT NULL DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    paid_by INT NULL,
    payment_method VARCHAR(50) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE,
    FOREIGN KEY (paid_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_loan_id (loan_id),
    INDEX idx_payment_no (payment_no),
    INDEX idx_due_date (due_date),
    INDEX idx_status (status),
    INDEX idx_paid_at (paid_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### **Audit Logs Table**

```sql
CREATE TABLE audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100) NULL,
    record_id VARCHAR(50) NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_table_name (table_name),
    INDEX idx_record_id (record_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### **System Configuration Table**

```sql
CREATE TABLE system_configurations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT NULL,
    config_type ENUM('string', 'number', 'boolean', 'json') NOT NULL DEFAULT 'string',
    description TEXT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_config_key (config_key),
    INDEX idx_is_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🔗 Relationships

### **Primary Relationships**

```sql
-- User to Member (1:1)
ALTER TABLE members ADD CONSTRAINT fk_members_user_id 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Member to Account (1:N)
ALTER TABLE accounts ADD CONSTRAINT fk_accounts_member_id 
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE;

-- Account to Transaction (1:N)
ALTER TABLE transactions ADD CONSTRAINT fk_transactions_account_id 
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE RESTRICT;

-- Member to Loan (1:N)
ALTER TABLE loans ADD CONSTRAINT fk_loans_member_id 
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE RESTRICT;

-- Loan to Loan Payments (1:N)
ALTER TABLE loan_payments ADD CONSTRAINT fk_loan_payments_loan_id 
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE;
```

### **Referential Integrity Rules**

| Relationship | Parent | Child | Delete Rule | Update Rule |
|--------------|--------|-------|-------------|-------------|
| User → Member | users | members | SET NULL | CASCADE |
| Member → Account | members | accounts | CASCADE | CASCADE |
| Account → Transaction | accounts | transactions | RESTRICT | CASCADE |
| Member → Loan | members | loans | RESTRICT | CASCADE |
| Loan → Loan Payments | loans | loan_payments | CASCADE | CASCADE |

---

## 📋 Data Types

### **Recommended Data Types**

| Data Type | Usage | Example |
|-----------|--------|---------|
| `INT` | IDs, small numbers | `id`, `user_id` |
| `BIGINT` | Large IDs, transaction IDs | `transaction_id`, `loan_id` |
| `VARCHAR(16)` | NIK, short codes | `nik`, `postal_code` |
| `VARCHAR(20)` | Phone numbers, account numbers | `phone`, `account_no` |
| `VARCHAR(50)` | Names, usernames | `name`, `username` |
| `VARCHAR(100)` | Addresses, descriptions | `address`, `purpose` |
| `VARCHAR(255)` | Emails, URLs, paths | `email`, `photo_path` |
| `TEXT` | Long descriptions, notes | `notes`, `user_agent` |
| `DECIMAL(15,2)` | Money amounts | `balance`, `amount` |
| `DECIMAL(5,4)` | Interest rates | `interest_rate` |
| `DATE` | Dates without time | `birth_date`, `due_date` |
| `DATETIME/TIMESTAMP` | Dates with time | `created_at`, `approved_at` |
| `ENUM` | Fixed options | `status`, `gender` |
| `JSON` | Structured data | `metadata`, `old_values` |
| `BOOLEAN` | True/false values | `is_public`, `email_verified` |

### **Data Type Best Practices**

#### **Numeric Types**
```sql
-- Use appropriate precision for money
DECIMAL(15,2)  -- Up to 999,999,999,999.99
DECIMAL(10,2)  -- Up to 99,999,999.99
DECIMAL(8,2)   -- Up to 999,999.99

-- Use INT for IDs (up to 2.1 billion)
INT UNSIGNED   -- 0 to 4,294,967,295

-- Use BIGINT for large transaction IDs
BIGINT UNSIGNED -- 0 to 18,446,744,073,709,551,615
```

#### **String Types**
```sql
-- Use fixed length for known lengths
CHAR(2)        -- Province codes
CHAR(5)        -- Postal codes

-- Use VARCHAR for variable length
VARCHAR(16)    -- NIK (exactly 16 chars)
VARCHAR(20)    -- Phone numbers
VARCHAR(50)    -- Names, usernames
VARCHAR(100)   -- Addresses
VARCHAR(255)   -- Emails, URLs
```

#### **Date/Time Types**
```sql
-- Use DATE for dates only
DATE           -- '2024-03-22'

-- Use DATETIME for specific moments
DATETIME       -- '2024-03-22 15:30:00'

-- Use TIMESTAMP for auto-updating
TIMESTAMP      -- Auto CURRENT_TIMESTAMP
```

---

## 📈 Indexes

### **Primary Indexes**

```sql
-- Unique indexes for unique constraints
CREATE UNIQUE INDEX idx_users_username ON users(username);
CREATE UNIQUE INDEX idx_users_email ON users(email);
CREATE UNIQUE INDEX idx_members_nik ON members(nik);
CREATE UNIQUE INDEX idx_members_member_no ON members(member_no);
CREATE UNIQUE INDEX idx_accounts_account_no ON accounts(account_no);
CREATE UNIQUE INDEX idx_transactions_code ON transactions(transaction_code);
CREATE UNIQUE INDEX idx_loans_code ON loans(loan_code);
```

### **Performance Indexes**

```sql
-- Composite indexes for common queries
CREATE INDEX idx_transactions_account_type_date ON transactions(account_id, transaction_type, created_at);
CREATE INDEX idx_loan_payments_loan_due_status ON loan_payments(loan_id, due_date, status);
CREATE INDEX idx_members_status_created ON members(status, created_at);
CREATE INDEX idx_accounts_member_type_status ON accounts(member_id, account_type, status);

-- Covering indexes for frequent queries
CREATE INDEX idx_transactions_covering ON transactions(account_id, transaction_type, amount, status, created_at);
CREATE INDEX idx_loan_payments_covering ON loan_payments(loan_id, status, due_date, amount, paid_amount);
```

### **Full-Text Indexes**

```sql
-- For search functionality
CREATE FULLTEXT INDEX idx_members_search ON members(name, address);
CREATE FULLTEXT INDEX idx_transactions_search ON transactions(description, notes);
```

---

## 🔄 Stored Procedures

### **Account Balance Update**

```sql
DELIMITER //
CREATE PROCEDURE UpdateAccountBalance(
    IN p_account_id INT,
    IN p_amount DECIMAL(15,2),
    IN p_transaction_type VARCHAR(50)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Update account balance based on transaction type
    IF p_transaction_type IN ('deposit', 'transfer_in', 'loan_disbursement') THEN
        UPDATE accounts 
        SET balance = balance + p_amount,
            last_transaction_at = CURRENT_TIMESTAMP
        WHERE id = p_account_id;
    ELSEIF p_transaction_type IN ('withdrawal', 'transfer_out', 'loan_payment') THEN
        UPDATE accounts 
        SET balance = balance - p_amount,
            last_transaction_at = CURRENT_TIMESTAMP
        WHERE id = p_account_id;
    END IF;
    
    -- Check for insufficient funds
    IF p_transaction_type IN ('withdrawal', 'transfer_out', 'loan_payment') THEN
        DECLARE current_balance DECIMAL(15,2);
        SELECT balance INTO current_balance FROM accounts WHERE id = p_account_id;
        
        IF current_balance < 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Insufficient funds';
        END IF;
    END IF;
    
    COMMIT;
END //
DELIMITER ;
```

### **Generate Member Number**

```sql
DELIMITER //
CREATE PROCEDURE GenerateMemberNumber(
    OUT p_member_no VARCHAR(20)
)
BEGIN
    DECLARE v_year INT;
    DECLARE v_month INT;
    DECLARE v_sequence INT;
    DECLARE v_prefix VARCHAR(10);
    
    SET v_year = YEAR(CURRENT_DATE);
    SET v_month = MONTH(CURRENT_DATE);
    SET v_prefix = CONCAT('MB', v_year, LPAD(v_month, 2, '0'));
    
    -- Get last sequence number
    SELECT COALESCE(MAX(CAST(SUBSTRING(member_no, 9) AS UNSIGNED)), 0) + 1
    INTO v_sequence
    FROM members
    WHERE member_no LIKE CONCAT(v_prefix, '%')
    AND YEAR(created_at) = v_year
    AND MONTH(created_at) = v_month;
    
    SET p_member_no = CONCAT(v_prefix, LPAD(v_sequence, 4, '0'));
END //
DELIMITER ;
```

### **Calculate Loan Interest**

```sql
DELIMITER //
CREATE PROCEDURE CalculateLoanInterest(
    IN p_loan_id BIGINT,
    OUT p_interest_amount DECIMAL(15,2)
)
BEGIN
    DECLARE v_principal DECIMAL(15,2);
    DECLARE v_rate DECIMAL(5,4);
    DECLARE v_days INT;
    DECLARE v_daily_rate DECIMAL(10,8);
    
    -- Get loan details
    SELECT principal_amount, interest_rate
    INTO v_principal, v_rate
    FROM loans
    WHERE id = p_loan_id;
    
    -- Calculate daily interest rate
    SET v_daily_rate = v_rate / 365;
    
    -- Calculate days since last payment
    SELECT DATEDIFF(CURRENT_DATE, COALESCE(last_payment_date, approved_at))
    INTO v_days
    FROM loans
    WHERE id = p_loan_id;
    
    -- Calculate interest amount
    SET p_interest_amount = v_principal * v_daily_rate * v_days;
END //
DELIMITER ;
```

---

## 🔧 Triggers

### **Audit Trail Trigger**

```sql
DELIMITER //
CREATE TRIGGER audit_users_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (action, table_name, record_id, new_values, created_at)
    VALUES ('INSERT', 'users', NEW.id, JSON_OBJECT(
        'username', NEW.username,
        'email', NEW.email,
        'role', NEW.role,
        'status', NEW.status
    ), CURRENT_TIMESTAMP);
END //

CREATE TRIGGER audit_users_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (action, table_name, record_id, old_values, new_values, created_at)
    VALUES ('UPDATE', 'users', NEW.id, JSON_OBJECT(
        'username', OLD.username,
        'email', OLD.email,
        'role', OLD.role,
        'status', OLD.status
    ), JSON_OBJECT(
        'username', NEW.username,
        'email', NEW.email,
        'role', NEW.role,
        'status', NEW.status
    ), CURRENT_TIMESTAMP);
END //

CREATE TRIGGER audit_users_delete
AFTER DELETE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (action, table_name, record_id, old_values, created_at)
    VALUES ('DELETE', 'users', OLD.id, JSON_OBJECT(
        'username', OLD.username,
        'email', OLD.email,
        'role', OLD.role,
        'status', OLD.status
    ), CURRENT_TIMESTAMP);
END //
DELIMITER ;
```

### **Transaction Balance Trigger**

```sql
DELIMITER //
CREATE TRIGGER update_account_balance_after_transaction
AFTER INSERT ON transactions
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' THEN
        -- Update account balance
        IF NEW.transaction_type IN ('deposit', 'transfer_in', 'loan_disbursement') THEN
            UPDATE accounts 
            SET balance = balance + NEW.amount,
                last_transaction_at = NEW.created_at
            WHERE id = NEW.account_id;
        ELSEIF NEW.transaction_type IN ('withdrawal', 'transfer_out', 'loan_payment') THEN
            UPDATE accounts 
            SET balance = balance - NEW.amount,
                last_transaction_at = NEW.created_at
            WHERE id = NEW.account_id;
        END IF;
    END IF;
END //
DELIMITER ;
```

---

## 👁️ Views

### **Member Summary View**

```sql
CREATE VIEW member_summary AS
SELECT 
    m.id,
    m.member_no,
    m.nik,
    m.name,
    m.phone,
    m.email,
    m.status,
    m.join_date,
    COALESCE(SUM(a.balance), 0) AS total_balance,
    COUNT(DISTINCT a.id) AS account_count,
    COALESCE(l.active_loans, 0) AS active_loans,
    COALESCE(l.total_loan_amount, 0) AS total_loan_amount,
    m.created_at,
    m.updated_at
FROM members m
LEFT JOIN accounts a ON m.id = a.member_id AND a.status = 'active'
LEFT JOIN (
    SELECT 
        member_id,
        COUNT(*) AS active_loans,
        SUM(principal_amount) AS total_loan_amount
    FROM loans 
    WHERE status = 'active'
    GROUP BY member_id
) l ON m.id = l.member_id
WHERE m.deleted_at IS NULL
GROUP BY m.id, m.member_no, m.nik, m.name, m.phone, m.email, m.status, m.join_date, m.created_at, m.updated_at, l.active_loans, l.total_loan_amount;
```

### **Transaction Summary View**

```sql
CREATE VIEW transaction_summary AS
SELECT 
    DATE(t.created_at) AS transaction_date,
    t.transaction_type,
    COUNT(*) AS transaction_count,
    SUM(t.amount) AS total_amount,
    AVG(t.amount) AS average_amount,
    COUNT(DISTINCT t.account_id) AS unique_accounts,
    COUNT(DISTINCT t.created_by) AS unique_users
FROM transactions t
WHERE t.status = 'completed'
  AND t.deleted_at IS NULL
GROUP BY DATE(t.created_at), t.transaction_type
ORDER BY transaction_date DESC, transaction_type;
```

### **Loan Performance View**

```sql
CREATE VIEW loan_performance AS
SELECT 
    l.id,
    l.loan_code,
    l.member_id,
    m.name AS member_name,
    l.principal_amount,
    l.total_payment,
    l.total_interest,
    l.status,
    l.approved_at,
    l.disbursed_at,
    DATEDIFF(CURRENT_DATE, l.disbursed_at) AS days_active,
    COALESCE(paid.total_paid, 0) AS total_paid,
    COALESCE(paid.payments_count, 0) AS payments_count,
    CASE 
        WHEN l.status = 'completed' THEN 100
        WHEN l.status = 'active' THEN (COALESCE(paid.total_paid, 0) / l.total_payment) * 100
        ELSE 0
    END AS completion_percentage,
    CASE 
        WHEN l.next_payment_date < CURRENT_DATE AND l.status = 'active' THEN DATEDIFF(CURRENT_DATE, l.next_payment_date)
        ELSE 0
    END AS days_overdue
FROM loans l
JOIN members m ON l.member_id = m.id
LEFT JOIN (
    SELECT 
        loan_id,
        SUM(paid_amount) AS total_paid,
        COUNT(*) AS payments_count
    FROM loan_payments 
    WHERE status = 'paid'
    GROUP BY loan_id
) paid ON l.id = paid.loan_id
WHERE l.deleted_at IS NULL;
```

---

## ⚡ Performance Optimization

### **Query Optimization**

#### **Slow Query Analysis**
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;
SET GLOBAL slow_query_log_file = '/var/log/mysql/slow.log';

-- Analyze slow queries
SELECT 
    query_time,
    lock_time,
    rows_sent,
    rows_examined,
    sql_text
FROM mysql.slow_log 
ORDER BY query_time DESC 
LIMIT 10;
```

#### **Index Usage Analysis**
```sql
-- Check index usage
SELECT 
    table_schema,
    table_name,
    index_name,
    cardinality,
    sub_part,
    packed,
    nullable,
    index_type
FROM information_schema.statistics 
WHERE table_schema = 'ksp_lamgabejaya_v2'
ORDER BY table_name, index_name;

-- Check unused indexes
SELECT 
    table_schema,
    table_name,
    index_name,
    cardinality
FROM information_schema.statistics 
WHERE table_schema = 'ksp_lamgabejaya_v2'
  AND index_name NOT IN ('PRIMARY')
  AND cardinality = 0;
```

#### **Query Execution Plan**
```sql
-- Analyze query execution
EXPLAIN SELECT 
    m.name,
    a.account_no,
    a.balance,
    t.amount,
    t.created_at
FROM members m
JOIN accounts a ON m.id = a.member_id
JOIN transactions t ON a.id = t.account_id
WHERE m.status = 'active'
  AND t.created_at >= '2024-01-01'
ORDER BY t.created_at DESC
LIMIT 100;
```

### **Database Configuration**

#### **MySQL Performance Tuning**
```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf
[mysqld]
# Memory Settings
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
innodb_log_buffer_size = 64M
key_buffer_size = 256M

# Connection Settings
max_connections = 200
max_connect_errors = 1000
wait_timeout = 60
interactive_timeout = 60

# Query Cache (MySQL 5.7)
query_cache_type = 1
query_cache_size = 256M
query_cache_limit = 2M

# InnoDB Settings
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
innodb_file_per_table = 1
innodb_read_io_threads = 8
innodb_write_io_threads = 8

# Binary Logging
log_bin = /var/log/mysql/mysql-bin.log
binlog_format = ROW
expire_logs_days = 7
max_binlog_size = 100M

# Performance Schema
performance_schema = ON
performance_schema_max_table_instances = 12500
performance_schema_max_table_handles = 4000
```

---

## 💾 Backup & Recovery

### **Backup Strategy**

#### **Full Backup Script**
```bash
#!/bin/bash
# scripts/backup_database.sh

# Configuration
DB_NAME="ksp_lamgabejaya_v2"
DB_USER="backup_user"
DB_PASS="backup_password"
BACKUP_DIR="/var/backups/mysql"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_backup_$DATE.sql"
COMPRESSED_FILE="$BACKUP_FILE.gz"

# Create backup directory
mkdir -p $BACKUP_DIR

# Perform backup
mysqldump --user=$DB_USER --password=$DB_PASS \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --hex-blob \
    --default-character-set=utf8mb4 \
    $DB_NAME > $BACKUP_FILE

# Compress backup
gzip $BACKUP_FILE

# Remove old backups (keep 30 days)
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete

# Log backup
echo "Backup completed: $COMPRESSED_FILE" >> $BACKUP_DIR/backup.log

# Verify backup
if [ -f "$COMPRESSED_FILE" ]; then
    echo "Backup verification: SUCCESS" >> $BACKUP_DIR/backup.log
else
    echo "Backup verification: FAILED" >> $BACKUP_DIR/backup.log
fi
```

#### **Incremental Backup**
```bash
#!/bin/bash
# scripts/incremental_backup.sh

DB_NAME="ksp_lamgabejaya_v2"
DB_USER="backup_user"
DB_PASS="backup_password"
BACKUP_DIR="/var/backups/mysql/incremental"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Get binary log position
mysql --user=$DB_USER --password=$DB_PASS -e "SHOW MASTER STATUS;" > $BACKUP_DIR/master_status_$DATE.txt

# Flush logs
mysql --user=$DB_USER --password=$DB_PASS -e "FLUSH LOGS;"

# Copy binary logs
cp /var/log/mysql/mysql-bin.* $BACKUP_DIR/

# Clean old incremental backups
find $BACKUP_DIR -name "mysql-bin.*" -mtime +7 -delete
```

### **Recovery Procedures**

#### **Full Recovery**
```bash
#!/bin/bash
# scripts/restore_database.sh

BACKUP_FILE=$1
DB_NAME="ksp_lamgabejaya_v2"
DB_USER="root"
DB_PASS=""

if [ -z "$BACKUP_FILE" ]; then
    echo "Usage: $0 <backup_file>"
    exit 1
fi

# Extract if compressed
if [[ $BACKUP_FILE == *.gz ]]; then
    gunzip -c $BACKUP_FILE | mysql --user=$DB_USER --password=$DB_PASS $DB_NAME
else
    mysql --user=$DB_USER --password=$DB_PASS $DB_NAME < $BACKUP_FILE
fi

echo "Database restored from: $BACKUP_FILE"
```

#### **Point-in-Time Recovery**
```bash
#!/bin/bash
# scripts/pitr_recovery.sh

FULL_BACKUP=$1
TARGET_TIME=$2
DB_NAME="ksp_lamgabejaya_v2"
DB_USER="root"
DB_PASS=""

# Restore full backup
mysql --user=$DB_USER --password=$DB_PASS $DB_NAME < $FULL_BACKUP

# Apply binary logs up to target time
mysqlbinlog --start-datetime="$TARGET_TIME" \
    --stop-datetime="$TARGET_TIME" \
    /var/log/mysql/mysql-bin.* | \
    mysql --user=$DB_USER --password=$DB_PASS $DB_NAME

echo "Point-in-time recovery completed to: $TARGET_TIME"
```

---

## 🔄 Migration

### **Migration System**

#### **Migration Class**
```php
<?php
// database/Migration.php
abstract class Migration {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    abstract public function up();
    abstract public function down();
    
    protected function execute($sql, $params = []) {
        return $this->db->query($sql, $params);
    }
    
    protected function createTable($tableName, $columns, $options = '') {
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (";
        $columnDefinitions = [];
        
        foreach ($columns as $name => $definition) {
            $columnDefinitions[] = "$name $definition";
        }
        
        $sql .= implode(', ', $columnDefinitions);
        $sql .= ") $options";
        
        return $this->execute($sql);
    }
    
    protected function addColumn($tableName, $columnName, $definition) {
        $sql = "ALTER TABLE $tableName ADD COLUMN $columnName $definition";
        return $this->execute($sql);
    }
    
    protected function dropColumn($tableName, $columnName) {
        $sql = "ALTER TABLE $tableName DROP COLUMN $columnName";
        return $this->execute($sql);
    }
    
    protected function addIndex($tableName, $indexName, $columns, $unique = false) {
        $indexType = $unique ? 'UNIQUE INDEX' : 'INDEX';
        $columnList = is_array($columns) ? implode(', ', $columns) : $columns;
        $sql = "CREATE $indexType $indexName ON $tableName ($columnList)";
        return $this->execute($sql);
    }
    
    protected function dropIndex($tableName, $indexName) {
        $sql = "DROP INDEX $indexName ON $tableName";
        return $this->execute($sql);
    }
}
?>
```

#### **Sample Migration**
```php
<?php
// database/migrations/001_create_members_table.php
class CreateMembersTable extends Migration {
    public function up() {
        $this->createTable('members', [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'user_id INT UNIQUE NULL',
            'nik VARCHAR(16) UNIQUE NOT NULL',
            'member_no VARCHAR(20) UNIQUE NOT NULL',
            'name VARCHAR(255) NOT NULL',
            'phone VARCHAR(20) NULL',
            'email VARCHAR(255) UNIQUE NULL',
            'birth_date DATE NULL',
            'gender ENUM("Laki-laki", "Perempuan") NULL',
            'address TEXT NULL',
            'status ENUM("active", "inactive", "pending") NOT NULL DEFAULT "active"',
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at TIMESTAMP NULL'
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        
        $this->addIndex('members', 'idx_nik', 'nik', true);
        $this->addIndex('members', 'idx_email', 'email', true);
        $this->addIndex('members', 'idx_status', 'status');
        $this->addIndex('members', 'idx_created_at', 'created_at');
    }
    
    public function down() {
        $this->execute('DROP TABLE IF EXISTS members');
    }
}
?>
```

---

## 🔒 Security

### **Database Security**

#### **User Permissions**
```sql
-- Create application user with limited permissions
CREATE USER 'ksp_app'@'localhost' IDENTIFIED BY 'secure_password';

-- Grant necessary permissions
GRANT SELECT, INSERT, UPDATE, DELETE ON ksp_lamgabejaya_v2.* TO 'ksp_app'@'localhost';
GRANT EXECUTE ON ksp_lamgabejaya_v2.* TO 'ksp_app'@'localhost';

-- Create backup user
CREATE USER 'ksp_backup'@'localhost' IDENTIFIED BY 'backup_password';
GRANT SELECT, LOCK TABLES, SHOW VIEW ON ksp_lamgabejaya_v2.* TO 'ksp_backup'@'localhost';
GRANT RELOAD ON *.* TO 'ksp_backup'@'localhost';

-- Create read-only user for reports
CREATE USER 'ksp_reports'@'localhost' IDENTIFIED BY 'reports_password';
GRANT SELECT ON ksp_lamgabejaya_v2.* TO 'ksp_reports'@'localhost';
```

#### **Data Encryption**
```sql
-- Encrypt sensitive columns
ALTER TABLE members 
ADD COLUMN encrypted_nik VARBINARY(255),
ADD COLUMN encrypted_phone VARBINARY(255);

-- Update encrypted data
UPDATE members 
SET encrypted_nik = AES_ENCRYPT(nik, 'encryption_key'),
    encrypted_phone = AES_ENCRYPT(phone, 'encryption_key');

-- Decrypt for queries
SELECT 
    name,
    AES_DECRYPT(encrypted_nik, 'encryption_key') AS nik,
    AES_DECRYPT(encrypted_phone, 'encryption_key') AS phone
FROM members;
```

---

## 📊 Monitoring

### **Performance Monitoring**

#### **Database Metrics Query**
```sql
-- Database size
SELECT 
    table_schema,
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS "Size (MB)"
FROM information_schema.tables 
WHERE table_schema = 'ksp_lamgabejaya_v2'
GROUP BY table_schema;

-- Table sizes
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS "Size (MB)"
FROM information_schema.tables 
WHERE table_schema = 'ksp_lamgabejaya_v2'
ORDER BY (data_length + index_length) DESC;

-- Row counts
SELECT 
    table_name,
    table_rows
FROM information_schema.tables 
WHERE table_schema = 'ksp_lamgabejaya_v2'
ORDER BY table_rows DESC;

-- Index usage
SELECT 
    table_name,
    index_name,
    cardinality,
    sub_part,
    packed,
    nullable,
    index_type
FROM information_schema.statistics 
WHERE table_schema = 'ksp_lamgabejaya_v2'
ORDER BY table_name, index_name;
```

#### **Health Check Script**
```bash
#!/bin/bash
# scripts/database_health_check.sh

DB_NAME="ksp_lamgabejaya_v2"
DB_USER="health_check"
DB_PASS="health_password"

echo "=== Database Health Check ==="
echo "Timestamp: $(date)"
echo ""

# Connection test
if mysql --user=$DB_USER --password=$DB_PASS -e "SELECT 1;" $DB_NAME > /dev/null 2>&1; then
    echo "✅ Database Connection: OK"
else
    echo "❌ Database Connection: FAILED"
    exit 1
fi

# Table count
TABLE_COUNT=$(mysql --user=$DB_USER --password=$DB_PASS -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME';" -s -N $DB_NAME)
echo "📊 Total Tables: $TABLE_COUNT"

# Total records
RECORD_COUNT=$(mysql --user=$DB_USER --password=$DB_PASS -e "SELECT SUM(table_rows) FROM information_schema.tables WHERE table_schema='$DB_NAME';" -s -N $DB_NAME)
echo "📈 Total Records: $RECORD_COUNT"

# Database size
DB_SIZE=$(mysql --user=$DB_USER --password=$DB_PASS -e "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) FROM information_schema.tables WHERE table_schema='$DB_NAME';" -s -N $DB_NAME)
echo "💾 Database Size: ${DB_SIZE}MB"

# Slow queries (last 24 hours)
SLOW_QUERIES=$(mysql --user=$DB_USER --password=$DB_PASS -e "SELECT COUNT(*) FROM mysql.slow_log WHERE start_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR);" -s -N $DB_NAME 2>/dev/null || echo "0")
echo "🐌 Slow Queries (24h): $SLOW_QUERIES"

echo ""
echo "=== Health Check Complete ==="
```

---

## 📚 Database Checklist

### **Pre-Deployment Checklist**

#### **Schema Design**
- [ ] All tables have primary keys
- [ ] Foreign key constraints defined
- [ ] Appropriate indexes created
- [ ] Data types optimized for storage
- [ ] Character set and collation consistent
- [ ] Audit trail tables implemented

#### **Performance**
- [ ] Query execution plans reviewed
- [ ] Slow query log enabled
- [ ] Database configuration optimized
- [ ] Connection pooling configured
- [ ] Caching strategy implemented

#### **Security**
- [ ] User permissions properly set
- [ ] Sensitive data encrypted
- [ ] Backup user with limited permissions
- [ ] Audit logging enabled
- [ ] Password policies enforced

#### **Backup & Recovery**
- [ ] Automated backup schedule
- [ ] Backup retention policy
- [ ] Recovery procedures tested
- [ ] Point-in-time recovery capability
- [ ] Off-site backup storage

---

**🎯 **Database Guide ini menyediakan panduan lengkap untuk design, implementasi, optimasi, dan maintenance database KSP Lam Gabe Jaya dengan best practices untuk performa dan keamanan!**
