-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: gabe
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts` (
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_number` (`account_number`),
  KEY `member_id` (`member_id`),
  KEY `account_type` (`account_type`),
  KEY `status` (`status`),
  CONSTRAINT `accounts_member_id_fk` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts`
--

LOCK TABLES `accounts` WRITE;
/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
INSERT INTO `accounts` VALUES (1,1,'A001','simpanan','Tabungan Wajib - Ahmad Wijaya',500000.00,3.00,'active','2024-01-15',NULL,'2026-03-22 03:15:36','2026-03-22 03:15:36'),(2,1,'A002','simpanan','Tabungan Sukarela - Ahmad Wijaya',1000000.00,2.50,'active','2024-01-15',NULL,'2026-03-22 03:15:36','2026-03-22 03:15:36'),(3,2,'A003','simpanan','Tabungan Wajib - Siti Nurhaliza',500000.00,3.00,'active','2024-02-20',NULL,'2026-03-22 03:15:36','2026-03-22 03:15:36'),(4,2,'A004','simpanan','Tabungan Sukarela - Siti Nurhaliza',750000.00,2.50,'active','2024-02-20',NULL,'2026-03-22 03:15:36','2026-03-22 03:15:36');
/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `table_name` (`table_name`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `audit_logs_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'CREATE','users',1,NULL,'{\"username\":\"admin\",\"role\":\"admin\",\"status\":\"active\"}','127.0.0.1','Mozilla/5.0 (System Initializer)','2026-03-22 03:15:36'),(2,1,'CREATE','members',1,NULL,'{\"member_number\":\"M001\",\"full_name\":\"Ahmad Wijaya\",\"status\":\"active\"}','127.0.0.1','Mozilla/5.0 (System Initializer)','2026-03-22 03:15:36'),(3,1,'CREATE','accounts',1,NULL,'{\"account_number\":\"A001\",\"account_type\":\"simpanan\",\"balance\":500000}','127.0.0.1','Mozilla/5.0 (System Initializer)','2026-03-22 03:15:36'),(4,1,'CREATE','loans',1,NULL,'{\"loan_number\":\"L001\",\"loan_amount\":5000000,\"status\":\"active\"}','127.0.0.1','Mozilla/5.0 (System Initializer)','2026-03-22 03:15:36'),(5,2,'UPDATE','loans',1,NULL,'{\"status\":\"approved\",\"approved_by\":2}','127.0.0.1','Mozilla/5.0 (Manager Browser)','2026-03-22 03:15:36');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `daily_transactions`
--

DROP TABLE IF EXISTS `daily_transactions`;
/*!50001 DROP VIEW IF EXISTS `daily_transactions`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `daily_transactions` AS SELECT 
 1 AS `transaction_date`,
 1 AS `total_transactions`,
 1 AS `total_credits`,
 1 AS `total_debits`,
 1 AS `net_amount`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `loan_payments`
--

DROP TABLE IF EXISTS `loan_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_payments` (
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `loan_id` (`loan_id`),
  KEY `payment_date` (`payment_date`),
  KEY `received_by` (`received_by`),
  CONSTRAINT `loan_payments_loan_id_fk` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loan_payments_received_by_fk` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_payments`
--

LOCK TABLES `loan_payments` WRITE;
/*!40000 ALTER TABLE `loan_payments` DISABLE KEYS */;
INSERT INTO `loan_payments` VALUES (1,1,1,466666.67,416666.67,50000.00,'2024-03-06','cash',3,'Angsuran bulan Maret','2026-03-22 03:15:36'),(2,2,1,516666.67,500000.00,16666.67,'2024-04-04','transfer',3,'Angsuran bulan April','2026-03-22 03:15:36');
/*!40000 ALTER TABLE `loan_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `loan_performance`
--

DROP TABLE IF EXISTS `loan_performance`;
/*!50001 DROP VIEW IF EXISTS `loan_performance`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `loan_performance` AS SELECT 
 1 AS `id`,
 1 AS `loan_number`,
 1 AS `member_name`,
 1 AS `loan_amount`,
 1 AS `interest_rate`,
 1 AS `loan_term`,
 1 AS `status`,
 1 AS `application_date`,
 1 AS `disbursement_date`,
 1 AS `total_paid`,
 1 AS `remaining_balance`,
 1 AS `payment_status`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `loans`
--

DROP TABLE IF EXISTS `loans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loans` (
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `loan_number` (`loan_number`),
  KEY `member_id` (`member_id`),
  KEY `status` (`status`),
  KEY `application_date` (`application_date`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `loans_approved_by_fk` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `loans_member_id_fk` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loans`
--

LOCK TABLES `loans` WRITE;
/*!40000 ALTER TABLE `loans` DISABLE KEYS */;
INSERT INTO `loans` VALUES (1,1,'L001',5000000.00,12.00,12,'Modal usaha kecil',NULL,'active','2024-02-01','2024-02-05','2024-02-06','2025-02-05',2,'2026-03-22 03:15:36','2026-03-22 03:15:36'),(2,2,'L002',3000000.00,10.00,6,'Biaya pendidikan',NULL,'active','2024-03-01','2024-03-03','2024-03-04','2024-09-03',2,'2026-03-22 03:15:36','2026-03-22 03:15:36');
/*!40000 ALTER TABLE `loans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `ip_address` (`ip_address`),
  KEY `attempt_time` (`attempt_time`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
INSERT INTO `login_attempts` VALUES (3,'bos','127.0.0.1',NULL,0,'2026-03-22 03:27:22'),(4,'bos','127.0.0.1',NULL,0,'2026-03-22 03:27:52'),(5,'bos','127.0.0.1',NULL,0,'2026-03-22 03:28:06'),(6,'wrong','::1',NULL,0,'2026-03-22 03:29:01'),(7,'wrong','::1',NULL,0,'2026-03-22 03:50:05'),(8,'wrong','::1',NULL,0,'2026-03-22 03:52:51'),(9,'wrong','::1',NULL,0,'2026-03-22 03:53:40'),(10,'wrong','::1',NULL,0,'2026-03-22 03:54:35'),(11,'<script>','::1',NULL,0,'2026-03-22 03:54:59'),(12,'bos','127.0.0.1',NULL,0,'2026-03-22 04:03:19');
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `member_summary`
--

DROP TABLE IF EXISTS `member_summary`;
/*!50001 DROP VIEW IF EXISTS `member_summary`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `member_summary` AS SELECT 
 1 AS `id`,
 1 AS `member_number`,
 1 AS `full_name`,
 1 AS `phone`,
 1 AS `email`,
 1 AS `join_date`,
 1 AS `status`,
 1 AS `total_accounts`,
 1 AS `total_balance`,
 1 AS `total_loans`,
 1 AS `total_loan_amount`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `members` (
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_number` (`member_number`),
  UNIQUE KEY `nik` (`nik`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  CONSTRAINT `members_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `members`
--

LOCK TABLES `members` WRITE;
/*!40000 ALTER TABLE `members` DISABLE KEYS */;
INSERT INTO `members` VALUES (1,4,'M001','3201011234560001','Ahmad Wijaya','1985-05-15','Jakarta','L','Jl. Merdeka No. 123, Jakarta Pusat','08123456789','member001@ksplamgabejaya.co.id','2024-01-15','active','2026-03-22 03:15:36','2026-03-22 03:15:36'),(2,5,'M002','3201011234560002','Siti Nurhaliza','1990-08-22','Bandung','P','Jl. Sudirman No. 456, Bandung','08234567890','member002@ksplamgabejaya.co.id','2024-02-20','active','2026-03-22 03:15:36','2026-03-22 03:15:36');
/*!40000 ALTER TABLE `members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `savings`
--

DROP TABLE IF EXISTS `savings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `savings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `savings_type` enum('wajib','pokok','sukarela') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `savings_type` (`savings_type`),
  KEY `transaction_date` (`transaction_date`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `savings_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `savings_member_id_fk` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `savings`
--

LOCK TABLES `savings` WRITE;
/*!40000 ALTER TABLE `savings` DISABLE KEYS */;
INSERT INTO `savings` VALUES (1,1,'wajib',500000.00,'2024-01-15','Setoran awal simpanan wajib',1,'2026-03-22 03:15:36'),(2,1,'sukarela',1000000.00,'2024-01-15','Setoran awal simpanan sukarela',1,'2026-03-22 03:15:36'),(3,2,'wajib',500000.00,'2024-02-20','Setoran awal simpanan wajib',1,'2026-03-22 03:15:36'),(4,2,'sukarela',750000.00,'2024-02-20','Setoran awal simpanan sukarela',1,'2026-03-22 03:15:36'),(5,1,'sukarela',100000.00,'2024-03-01','Setoran tambahan',1,'2026-03-22 03:15:36'),(6,2,'sukarela',200000.00,'2024-03-05','Setoran tambahan',1,'2026-03-22 03:15:36');
/*!40000 ALTER TABLE `savings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_config`
--

DROP TABLE IF EXISTS `system_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL,
  `config_value` text DEFAULT NULL,
  `config_type` enum('string','number','boolean','json') NOT NULL DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key` (`config_key`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `system_config_updated_by_fk` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_config`
--

LOCK TABLES `system_config` WRITE;
/*!40000 ALTER TABLE `system_config` DISABLE KEYS */;
INSERT INTO `system_config` VALUES (1,'ksp_name','KSP Lam Gabe Jaya','string','Nama Koperasi',NULL,'2026-03-22 03:15:36'),(2,'ksp_address','Jl. Koperasi No. 123, Jakarta','string','Alamat Koperasi',NULL,'2026-03-22 03:15:36'),(3,'ksp_phone','021-12345678','string','Nomor Telepon',NULL,'2026-03-22 03:15:36'),(4,'ksp_email','info@ksplamgabejaya.co.id','string','Email Koperasi',NULL,'2026-03-22 03:15:36'),(5,'savings_wajib_minimum','500000','number','Minimal simpanan wajib per bulan',NULL,'2026-03-22 03:15:36'),(6,'savings_pokok_minimum','1000000','number','Minimal simpanan pokok',NULL,'2026-03-22 03:15:36'),(7,'loan_interest_min','5.00','number','Bunga pinjaman minimal (%)',NULL,'2026-03-22 03:15:36'),(8,'loan_interest_max','18.00','number','Bunga pinjaman maksimal (%)',NULL,'2026-03-22 03:15:36'),(9,'loan_term_max','36','number','Jangka waktu pinjaman maksimal (bulan)',NULL,'2026-03-22 03:15:36'),(10,'late_payment_fee','2.00','number','Denda keterlambatan (%)',NULL,'2026-03-22 03:15:36'),(11,'session_timeout','30','number','Session timeout (menit)',NULL,'2026-03-22 03:15:36'),(12,'max_login_attempts','5','number','Maksimal percobaan login',NULL,'2026-03-22 03:15:36'),(13,'lockout_duration','15','number','Durasi lockout (menit)',NULL,'2026-03-22 03:15:36'),(14,'enable_notifications','true','boolean','Aktifkan notifikasi',NULL,'2026-03-22 03:15:36'),(15,'enable_audit_log','true','boolean','Aktifkan audit log',NULL,'2026-03-22 03:15:36');
/*!40000 ALTER TABLE `system_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_code` varchar(20) NOT NULL,
  `account_id` int(11) NOT NULL,
  `transaction_type` enum('debit','credit') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_code` (`transaction_code`),
  KEY `account_id` (`account_id`),
  KEY `transaction_type` (`transaction_type`),
  KEY `transaction_date` (`transaction_date`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `transactions_account_id_fk` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,'TRX001',1,'credit',500000.00,'Setoran Awal Tabungan Wajib','SET001','2024-01-15',1,'2026-03-22 03:15:36'),(2,'TRX002',2,'credit',1000000.00,'Setoran Awal Tabungan Sukarela','SET002','2024-01-15',1,'2026-03-22 03:15:36'),(3,'TRX003',3,'credit',500000.00,'Setoran Awal Tabungan Wajib','SET003','2024-02-20',1,'2026-03-22 03:15:36'),(4,'TRX004',4,'credit',750000.00,'Setoran Awal Tabungan Sukarela','SET004','2024-02-20',1,'2026-03-22 03:15:36'),(5,'TRX005',1,'credit',100000.00,'Setoran Tambahan','SET005','2024-03-01',1,'2026-03-22 03:15:36'),(6,'TRX006',2,'credit',200000.00,'Setoran Tambahan','SET006','2024-03-05',1,'2026-03-22 03:15:36');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','manager','staff','member') NOT NULL DEFAULT 'member',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin@ksplamgabejaya.co.id','Administrator KSP','admin','active','2026-03-22 11:10:59','2026-03-22 03:15:36','2026-03-22 04:11:24'),(2,'manager','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','manager@ksplamgabejaya.co.id','Manager KSP','manager','active','2026-03-22 10:32:27','2026-03-22 03:15:36','2026-03-22 03:32:27'),(3,'staff','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','staff@ksplamgabejaya.co.id','Staff KSP','staff','active','2026-03-22 10:32:27','2026-03-22 03:15:36','2026-03-22 03:32:27'),(4,'member001','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','member001@ksplamgabejaya.co.id','Ahmad Wijaya','member','active','2026-03-22 10:32:27','2026-03-22 03:15:36','2026-03-22 03:32:27'),(5,'member002','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','member002@ksplamgabejaya.co.id','Siti Nurhaliza','member','active',NULL,'2026-03-22 03:15:36','2026-03-22 03:15:36');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'gabe'
--
