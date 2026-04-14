-- ============================================================
-- KSP Lam Gabe Jaya v2.0 — Phase 3 Migration
-- Real-time Notifications
-- ============================================================

CREATE TABLE IF NOT EXISTS notifications (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NULL COMMENT 'NULL = broadcast ke semua admin',
    type        ENUM('approval_pending','approval_done','loan_applied','payment_due',
                     'system','member_registered','report_ready') NOT NULL,
    title       VARCHAR(150) NOT NULL,
    message     TEXT NOT NULL,
    link        VARCHAR(255) NULL COMMENT 'URL halaman terkait',
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    entity_type VARCHAR(50) NULL,
    entity_id   INT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Dynamic Role System (Many-to-Many)
-- ============================================================

CREATE TABLE IF NOT EXISTS roles (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    role_code   VARCHAR(50) NOT NULL UNIQUE COMMENT 'e.g. mantri, collector, surveyor',
    role_name   VARCHAR(100) NOT NULL,
    category    ENUM('office','field') NOT NULL DEFAULT 'field',
    description TEXT NULL,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role_code (role_code),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_roles (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    role_id     INT NOT NULL,
    assigned_by INT NOT NULL COMMENT 'User ID yang assign role ini',
    assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uk_user_role (user_id, role_id),
    INDEX idx_user_id (user_id),
    INDEX idx_role_id (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed field staff roles
INSERT INTO roles (role_code, role_name, category, description) VALUES
('mantri', 'Mantri / Credit Officer', 'field', 'Petugas lapangan untuk survey dan follow-up pinjaman'),
('collector', 'Collector / Penagih', 'field', 'Petugas penagihan tunggakan angsuran'),
('surveyor', 'Surveyor', 'field', 'Petugas survei lokasi dan jaminan pinjaman')
ON DUPLICATE KEY UPDATE role_name=VALUES(role_name);
