-- Migration untuk Multiple Identitas dan Enhanced Address Database
-- KSP Lam Gabe Jaya v2.0 - 3 Database Integration
-- Database: ksp_lamgabejaya_v2, alamat_db, orang

-- ==================================================
-- DATABASE: ksp_lamgabejaya_v2
-- ==================================================

-- 1. Tabel untuk Multiple Identitas (di ksp_lamgabejaya_v2)
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

-- 2. Tabel untuk Enhanced Address dengan Database Integration (di ksp_lamgabejaya_v2)
CREATE TABLE IF NOT EXISTS `member_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `address_type` enum('Residence','Business','Mailing','Other') DEFAULT 'Residence',
  `province_id` int(11) unsigned DEFAULT NULL COMMENT 'Link ke alamat_db.provinces',
  `province_name` varchar(100) DEFAULT NULL,
  `regency_id` int(11) unsigned DEFAULT NULL COMMENT 'Link ke alamat_db.regencies',
  `regency_name` varchar(100) DEFAULT NULL,
  `district_id` int(11) unsigned DEFAULT NULL COMMENT 'Link ke alamat_db.districts',
  `district_name` varchar(100) DEFAULT NULL,
  `village_id` int(11) unsigned DEFAULT NULL COMMENT 'Link ke alamat_db.villages',
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

-- 3. Tabel untuk Loan Applicants (Non-Members) (di ksp_lamgabejaya_v2)
CREATE TABLE IF NOT EXISTS `loan_applicants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_number` varchar(20) NOT NULL,
  `person_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Link ke orang.persons',
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
  KEY `idx_person_id` (`person_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_loan_applicants_person` FOREIGN KEY (`person_id`) REFERENCES `orang.persons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel untuk Loan Applicant Identities (di ksp_lamgabejaya_v2)
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

-- 5. Tabel untuk Loan Applicant Addresses (di ksp_lamgabejaya_v2)
CREATE TABLE IF NOT EXISTS `loan_applicant_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `applicant_id` int(11) NOT NULL,
  `address_type` enum('Residence','Business','Mailing','Other') DEFAULT 'Residence',
  `province_id` int(11) unsigned DEFAULT NULL COMMENT 'Link ke alamat_db.provinces',
  `province_name` varchar(100) DEFAULT NULL,
  `regency_id` int(11) unsigned DEFAULT NULL COMMENT 'Link ke alamat_db.regencies',
  `regency_name` varchar(100) DEFAULT NULL,
  `district_id` int(11) unsigned DEFAULT NULL COMMENT 'Link ke alamat_db.districts',
  `district_name` varchar(100) DEFAULT NULL,
  `village_id` int(11) unsigned DEFAULT NULL COMMENT 'Link ke alamat_db.villages',
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

-- 6. Enhanced Collateral Management (di ksp_lamgabejaya_v2)
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

-- 7. Update existing members table untuk enhanced address support (di ksp_lamgabejaya_v2)
ALTER TABLE `members` 
ADD COLUMN IF NOT EXISTS `birth_place` varchar(50) DEFAULT NULL AFTER `date_of_birth`,
ADD COLUMN IF NOT EXISTS `rt` varchar(10) DEFAULT NULL AFTER `postal_code`,
ADD COLUMN IF NOT EXISTS `rw` varchar(10) DEFAULT NULL AFTER `rt`,
ADD COLUMN IF NOT EXISTS `latitude` decimal(10,8) DEFAULT NULL AFTER `rw`,
ADD COLUMN IF NOT EXISTS `longitude` decimal(11,8) DEFAULT NULL AFTER `latitude`;

-- 8. Update existing loans table untuk applicant support (di ksp_lamgabejaya_v2)
ALTER TABLE `loans` 
ADD COLUMN IF NOT EXISTS `applicant_id` int(11) DEFAULT NULL AFTER `member_id`,
ADD COLUMN IF NOT EXISTS `payment_method` enum('Angsuran Bulanan','Angsuran Mingguan','Jatuh Tempo') DEFAULT 'Angsuran Bulanan' AFTER `purpose`,
ADD INDEX IF NOT EXISTS `idx_applicant_id` (`applicant_id`);

-- 9. Update member_documents untuk support lebih banyak jenis dokumen (di ksp_lamgabejaya_v2)
ALTER TABLE `member_documents` 
MODIFY COLUMN `document_type` enum('KTP','KK','NPWP','SIM','PASSPORT','BPJS','Slip Gaji','Surat Nikah','Ijasah','Surat Kerja','Foto','Tanda Tangan','Lainnya') NOT NULL,
ADD COLUMN IF NOT EXISTS `issue_date` date DEFAULT NULL AFTER `document_number`,
ADD COLUMN IF NOT EXISTS `expiry_date` date DEFAULT NULL AFTER `issue_date`,
ADD COLUMN IF NOT EXISTS `is_primary` tinyint(1) DEFAULT 0 AFTER `expiry_date`,
ADD COLUMN IF NOT EXISTS `status` enum('Active','Expired','Revoked') DEFAULT 'Active' AFTER `is_primary`;

-- ==================================================
-- DATABASE: orang (Person Management)
-- ==================================================

-- 10. Tabel untuk Multiple Person Identities (di orang)
CREATE TABLE IF NOT EXISTS `person_identities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint(20) unsigned NOT NULL,
  `identity_type` enum('KTP','SIM','PASSPORT','NPWP','BPJS','KK','AKTA_LAHIR','AKTA_KAWIN','IJAZAH') NOT NULL,
  `identity_number` varchar(50) NOT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `issuing_authority` varchar(100) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `status` enum('Active','Expired','Revoked','Lost') DEFAULT 'Active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_person_id` (`person_id`),
  KEY `idx_identity_type_number` (`identity_type`, `identity_number`),
  CONSTRAINT `fk_person_identities_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Update persons table untuk enhanced identity support (di orang)
ALTER TABLE `persons` 
ADD COLUMN IF NOT EXISTS `birth_place` varchar(100) DEFAULT NULL AFTER `place_of_birth`,
ADD COLUMN IF NOT EXISTS `mother_name` varchar(100) DEFAULT NULL AFTER `blood_type`,
ADD COLUMN IF NOT EXISTS `education_level` enum('SD','SMP','SMA','D3','S1','S2','S3','Lainnya') DEFAULT NULL AFTER `mother_name`,
ADD COLUMN IF NOT EXISTS `profession` varchar(100) DEFAULT NULL AFTER `education_level`,
ADD COLUMN IF NOT EXISTS `monthly_income` decimal(15,2) DEFAULT NULL AFTER `profession`,
ADD COLUMN IF NOT EXISTS `marital_status` enum('Single','Married','Divorced','Widowed') DEFAULT NULL AFTER `monthly_income`,
ADD COLUMN IF NOT EXISTS `spouse_name` varchar(100) DEFAULT NULL AFTER `marital_status`,
ADD COLUMN IF NOT EXISTS `dependents_count` int(11) DEFAULT 0 AFTER `spouse_name`;

-- ==================================================
-- TRIGGERS (di ksp_lamgabejaya_v2)
-- ==================================================

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

-- ==================================================
-- VIEWS untuk Integration (di ksp_lamgabejaya_v2)
-- ==================================================

-- View untuk member dengan complete address dan identity info
CREATE OR REPLACE VIEW `v_members_complete` AS
SELECT 
    m.*,
    mt.name as member_type_name,
    GROUP_CONCAT(DISTINCT CONCAT(mi.identity_type, ':', mi.identity_number) 
        ORDER BY mi.is_primary DESC, mi.identity_type 
        SEPARATOR ';') as identities,
    ma.province_name,
    ma.regency_name,
    ma.district_name,
    ma.village_name,
    ma.full_address as complete_address,
    ma.rt,
    ma.rw,
    ma.postal_code,
    ma.latitude,
    ma.longitude
FROM members m 
LEFT JOIN member_types mt ON m.member_type_id = mt.id 
LEFT JOIN member_identities mi ON m.id = mi.member_id AND mi.status = 'Active'
LEFT JOIN member_addresses ma ON m.id = ma.member_id AND ma.is_primary = 1 AND ma.status = 'Active'
GROUP BY m.id;

-- ==================================================
-- STORED PROCEDURES untuk Address Integration
-- ==================================================

DELIMITER //
CREATE PROCEDURE IF NOT EXISTS `GetAddressHierarchy`(IN province_id INT, IN regency_id INT, IN district_id INT, IN village_id INT)
BEGIN
    SELECT 
        p.name as province_name,
        r.name as regency_name,
        d.name as district_name,
        v.name as village_name
    FROM alamat_db.provinces p
    LEFT JOIN alamat_db.regencies r ON r.province_id = p.id AND (regency_id IS NULL OR r.id = regency_id)
    LEFT JOIN alamat_db.districts d ON d.regency_id = r.id AND (district_id IS NULL OR d.id = district_id)
    LEFT JOIN alamat_db.villages v ON v.district_id = d.id AND (village_id IS NULL OR v.id = village_id)
    WHERE p.id = province_id;
END//
DELIMITER ;

-- ==================================================
-- Sample Data untuk Testing (opsional)
-- ==================================================

-- Insert sample member identities
INSERT IGNORE INTO `member_identities` (`member_id`, `identity_type`, `identity_number`, `is_primary`) VALUES
(1, 'KTP', '3201011234560001', 1),
(1, 'NPWP', '12.345.678.9-123.000', 0);

-- Insert sample member address
INSERT IGNORE INTO `member_addresses` (`member_id`, `province_id`, `province_name`, `regency_id`, `regency_name`, `district_id`, `district_name`, `village_id`, `village_name`, `rt`, `rw`, `full_address`, `postal_code`, `is_primary`) VALUES
(1, 11, 'DKI Jakarta', 71, 'Jakarta Pusat', 727, 'Menteng', 847, 'Menteng', '01', '02', 'Jl. Test No. 123', '10310', 1);

COMMIT;
