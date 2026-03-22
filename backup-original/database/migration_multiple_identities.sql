-- Migration untuk Multiple Identitas dan Enhanced Address Database
-- KSP Lam Gabe Jaya v2.0

-- 1. Tabel untuk Multiple Identitas
CREATE TABLE IF NOT EXISTS `member_identities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `identity_type` enum('KTP','SIM','PASSPORT','NPWP','BPJS','KK') NOT NULL,
  `identity_number` varchar(50) NOT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `status` enum('Active','Expired','Revoked') DEFAULT 'Active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_member_id` (`member_id`),
  KEY `idx_identity_type_number` (`identity_type`, `identity_number`),
  CONSTRAINT `fk_member_identities_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel untuk Enhanced Address dengan Database Integration
CREATE TABLE IF NOT EXISTS `member_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `address_type` enum('Residence','Business','Mailing','Other') DEFAULT 'Residence',
  `province_id` int(11) DEFAULT NULL,
  `province_name` varchar(100) DEFAULT NULL,
  `regency_id` int(11) DEFAULT NULL,
  `regency_name` varchar(100) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `district_name` varchar(100) DEFAULT NULL,
  `village_id` int(11) DEFAULT NULL,
  `village_name` varchar(100) DEFAULT NULL,
  `rt` varchar(10) DEFAULT NULL,
  `rw` varchar(10) DEFAULT NULL,
  `full_address` text DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_member_id` (`member_id`),
  KEY `idx_address_type` (`address_type`),
  KEY `idx_location` (`province_id`, `regency_id`, `district_id`, `village_id`),
  CONSTRAINT `fk_member_addresses_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel untuk Loan Applicants (Non-Members)
CREATE TABLE IF NOT EXISTS `loan_applicants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_number` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('L','P') DEFAULT NULL,
  `phone_number` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `monthly_income` decimal(15,2) DEFAULT NULL,
  `marital_status` enum('Single','Married','Divorced','Widowed') DEFAULT NULL,
  `dependents` int(11) DEFAULT 0,
  `reference_name` varchar(100) DEFAULT NULL,
  `reference_phone` varchar(20) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Withdrawn') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_application_number` (`application_number`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel untuk Loan Applicant Identities
CREATE TABLE IF NOT EXISTS `loan_applicant_identities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `applicant_id` int(11) NOT NULL,
  `identity_type` enum('KTP','SIM','PASSPORT','NPWP','BPJS','KK') NOT NULL,
  `identity_number` varchar(50) NOT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `status` enum('Active','Expired','Revoked') DEFAULT 'Active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_applicant_id` (`applicant_id`),
  KEY `idx_identity_type_number` (`identity_type`, `identity_number`),
  CONSTRAINT `fk_loan_applicant_identities_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `loan_applicants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabel untuk Loan Applicant Addresses
CREATE TABLE IF NOT EXISTS `loan_applicant_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `applicant_id` int(11) NOT NULL,
  `address_type` enum('Residence','Business','Mailing','Other') DEFAULT 'Residence',
  `province_id` int(11) DEFAULT NULL,
  `province_name` varchar(100) DEFAULT NULL,
  `regency_id` int(11) DEFAULT NULL,
  `regency_name` varchar(100) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `district_name` varchar(100) DEFAULT NULL,
  `village_id` int(11) DEFAULT NULL,
  `village_name` varchar(100) DEFAULT NULL,
  `rt` varchar(10) DEFAULT NULL,
  `rw` varchar(10) DEFAULT NULL,
  `full_address` text DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_applicant_id` (`applicant_id`),
  KEY `idx_address_type` (`address_type`),
  KEY `idx_location` (`province_id`, `regency_id`, `district_id`, `village_id`),
  CONSTRAINT `fk_loan_applicant_addresses_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `loan_applicants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Enhanced Collateral Management
CREATE TABLE IF NOT EXISTS `loan_collaterals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) DEFAULT NULL,
  `applicant_id` int(11) DEFAULT NULL,
  `collateral_type` enum('Tanah','Bangunan','Kendaraan','Deposito','Mesin','Inventaris','Lainnya','Tanpa Jaminan') NOT NULL,
  `collateral_value` decimal(15,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `certificate_number` varchar(100) DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `location_address` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `ownership_status` enum('Milik Sendiri','Sewa','Keluarga','Lainnya') DEFAULT NULL,
  `assessment_date` date DEFAULT NULL,
  `assessor_id` int(11) DEFAULT NULL,
  `status` enum('Active','Released','Sold','Repossessed') DEFAULT 'Active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_loan_id` (`loan_id`),
  KEY `idx_applicant_id` (`applicant_id`),
  KEY `idx_collateral_type` (`collateral_type`),
  CONSTRAINT `fk_loan_collaterals_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_loan_collaterals_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `loan_applicants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Update existing loans table untuk applicant support
ALTER TABLE `loans` 
ADD COLUMN `applicant_id` int(11) DEFAULT NULL AFTER `member_id`,
ADD COLUMN `payment_method` enum('Angsuran Bulanan','Angsuran Mingguan','Jatuh Tempo') DEFAULT 'Angsuran Bulanan' AFTER `purpose`,
ADD INDEX `idx_applicant_id` (`applicant_id`);

-- 8. Update existing members table untuk enhanced address support
ALTER TABLE `members` 
ADD COLUMN `birth_place` varchar(50) DEFAULT NULL AFTER `date_of_birth`,
ADD COLUMN `rt` varchar(10) DEFAULT NULL AFTER `postal_code`,
ADD COLUMN `rw` varchar(10) DEFAULT NULL AFTER `rt`,
ADD COLUMN `latitude` decimal(10,8) DEFAULT NULL AFTER `rw`,
ADD COLUMN `longitude` decimal(11,8) DEFAULT NULL AFTER `latitude`;

-- 9. Update member_documents untuk support lebih banyak jenis dokumen
ALTER TABLE `member_documents` 
MODIFY COLUMN `document_type` enum('KTP','KK','NPWP','SIM','PASSPORT','BPJS','Slip Gaji','Surat Nikah','Ijasah','Surat Kerja','Foto','Tanda Tangan','Lainnya') NOT NULL,
ADD COLUMN `issue_date` date DEFAULT NULL AFTER `document_number`,
ADD COLUMN `expiry_date` date DEFAULT NULL AFTER `issue_date`,
ADD COLUMN `is_primary` tinyint(1) DEFAULT 0 AFTER `expiry_date`,
ADD COLUMN `status` enum('Active','Expired','Revoked') DEFAULT 'Active' AFTER `is_primary`;

-- Trigger untuk auto-generate application number
DELIMITER //
CREATE TRIGGER IF NOT EXISTS `generate_application_number` 
BEFORE INSERT ON `loan_applicants`
FOR EACH ROW
BEGIN
    IF NEW.application_number IS NULL OR NEW.application_number = '' THEN
        SET NEW.application_number = CONCAT('LA', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD(FLOOR(RAND() * 1000), 3, '0'));
    END IF;
END//
DELIMITER ;

-- Trigger untuk auto-generate member number (jika belum ada)
DELIMITER //
CREATE TRIGGER IF NOT EXISTS `generate_member_number` 
BEFORE INSERT ON `members`
FOR EACH ROW
BEGIN
    IF NEW.member_number IS NULL OR NEW.member_number = '' THEN
        SET NEW.member_number = CONCAT('M', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD(FLOOR(RAND() * 1000), 3, '0'));
    END IF;
END//
DELIMITER ;

-- Sample data untuk testing (opsional)
INSERT IGNORE INTO `member_identities` (`member_id`, `identity_type`, `identity_number`, `is_primary`) VALUES
(1, 'KTP', '3201011234560001', 1),
(1, 'NPWP', '12.345.678.9-123.000', 0);

INSERT IGNORE INTO `member_addresses` (`member_id`, `province_name`, `regency_name`, `district_name`, `village_name`, `rt`, `rw`, `full_address`, `postal_code`, `is_primary`) VALUES
(1, 'DKI Jakarta', 'Jakarta Pusat', 'Menteng', 'Menteng', '01', '02', 'Jl. Test No. 123', '10310', 1);

COMMIT;
