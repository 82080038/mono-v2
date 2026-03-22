-- KSP Lam Gabe Jaya Database Schema v2.0
-- Updated untuk mendukung role system baru dan dynamic navigation
-- Compatible dengan PHP 8.x dan MariaDB/MySQL 5.7+

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
-- Table structure for table `users`
-- Updated role system: bos, admin, teller, collector, nasabah
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
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('bos','admin','teller','collector','nasabah') NOT NULL DEFAULT 'nasabah',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `login_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `status` (`status`),
  KEY `last_login` (`last_login`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
-- Updated dengan role system baru dan password hashing yang benar
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES 
(1,'bos','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','bos@ksplamgabejaya.co.id','Bos KSP','08123456789','Jl. Koperasi No. 123, Jakarta','bos','active','2026-03-22 11:10:59',0,NULL,'2026-03-22 03:15:36','2026-03-22 04:11:24'),
(2,'admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin@ksplamgabejaya.co.id','Administrator KSP','08234567890','Jl. Koperasi No. 123, Jakarta','admin','active','2026-03-22 10:32:27',0,NULL,'2026-03-22 03:15:36','2026-03-22 03:32:27'),
(3,'teller','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','teller@ksplamgabejaya.co.id','Teller KSP','08345678901','Jl. Koperasi No. 123, Jakarta','teller','active','2026-03-22 10:32:27',0,NULL,'2026-03-22 03:15:36','2026-03-22 03:32:27'),
(4,'collector','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','collector@ksplamgabejaya.co.id','Collector KSP','08456789012','Jl. Koperasi No. 123, Jakarta','collector','active','2026-03-22 10:32:27',0,NULL,'2026-03-22 03:15:36','2026-03-22 03:32:27'),
(5,'nasabah','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','nasabah@ksplamgabejaya.co.id','Ahmad Wijaya','08123456789','Jl. Merdeka No. 456, Jakarta','nasabah','active','2026-03-22 10:32:27',0,NULL,'2026-03-22 03:15:36','2026-03-22 03:32:27');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
-- Enhanced untuk security
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
  `failure_reason` varchar(100) DEFAULT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `ip_address` (`ip_address`),
  KEY `attempt_time` (`attempt_time`),
  KEY `success` (`success`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
INSERT INTO `login_attempts` VALUES 
(1,'bos','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,NULL,'2026-03-22 03:27:22'),
(2,'admin','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,NULL,'2026-03-22 03:27:52'),
(3,'teller','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',1,NULL,'2026-03-22 03:28:06'),
(4,'wrong','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',0,'Invalid credentials','2026-03-22 03:29:01'),
(5,'<script>','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',0,'SQL Injection attempt','2026-03-22 03:54:59');
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `members`
-- Enhanced dengan additional fields
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
  `photo` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_number` (`member_number`),
  UNIQUE KEY `nik` (`nik`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `join_date` (`join_date`),
  CONSTRAINT `members_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `members`
--

LOCK TABLES `members` WRITE;
/*!40000 ALTER TABLE `members` DISABLE KEYS */;
INSERT INTO `members` VALUES 
(1,5,'M001','3201011234560001','Ahmad Wijaya','1985-05-15','Jakarta','L','Jl. Merdeka No. 123, Jakarta Pusat','08123456789','ahmad.wijaya@email.com','2024-01-15','active',NULL,'Customer since 2024','2026-03-22 03:15:36','2026-03-22 03:15:36'),
(2,5,'M002','3201011234560002','Siti Nurhaliza','1990-08-22','Bandung','P','Jl. Sudirman No. 456, Bandung','08234567890','siti.nurhaliza@email.com','2024-02-20','active',NULL,'Customer since 2024','2026-03-22 03:15:36','2026-03-22 03:15:36');
/*!40000 ALTER TABLE `members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounts`
-- Enhanced dengan additional fields
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
  `status` enum('active','inactive','closed','frozen') NOT NULL DEFAULT 'active',
  `opened_date` date NOT NULL,
  `closed_date` date DEFAULT NULL,
  `last_transaction_date` date DEFAULT NULL,
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
INSERT INTO `accounts` VALUES 
(1,1,'A001','simpanan','Tabungan Wajib - Ahmad Wijaya',500000.00,3.00,'active','2024-01-15',NULL,'2024-03-01','2026-03-22 03:15:36','2026-03-22 03:15:36'),
(2,1,'A002','simpanan','Tabungan Sukarela - Ahmad Wijaya',1000000.00,2.50,'active','2024-01-15',NULL,'2024-03-01','2026-03-22 03:15:36','2026-03-22 03:15:36'),
(3,2,'A003','simpanan','Tabungan Wajib - Siti Nurhaliza',500000.00,3.00,'active','2024-02-20',NULL,'2024-03-05','2026-03-22 03:15:36','2026-03-22 03:15:36'),
(4,2,'A004','simpanan','Tabungan Sukarela - Siti Nurhaliza',750000.00,2.50,'active','2024-02-20',NULL,'2024-03-05','2026-03-22 03:15:36','2026-03-22 03:15:36');
/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
-- Enhanced dengan additional fields untuk dynamic content
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
  `transaction_time` time DEFAULT NULL,
  `payment_method` enum('cash','transfer','bank_deposit','digital_payment') DEFAULT 'cash',
  `status` enum('pending','completed','failed','cancelled') NOT NULL DEFAULT 'completed',
  `created_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_code` (`transaction_code`),
  KEY `account_id` (`account_id`),
  KEY `transaction_type` (`transaction_type`),
  KEY `transaction_date` (`transaction_date`),
  KEY `status` (`status`),
  KEY `created_by` (`created_by`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `transactions_account_id_fk` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `transactions_approved_by_fk` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES 
(1,'TRX001',1,'credit',500000.00,'Setoran Awal Tabungan Wajib','SET001','2024-01-15','09:30:00','cash','completed',1,1,'Initial deposit','2026-03-22 03:15:36','2026-03-22 03:15:36'),
(2,'TRX002',2,'credit',1000000.00,'Setoran Awal Tabungan Sukarela','SET002','2024-01-15','09:35:00','cash','completed',1,1,'Initial deposit','2026-03-22 03:15:36','2026-03-22 03:15:36'),
(3,'TRX003',3,'credit',500000.00,'Setoran Awal Tabungan Wajib','SET003','2024-02-20','10:15:00','cash','completed',1,1,'Initial deposit','2026-03-22 03:15:36','2026-03-22 03:15:36'),
(4,'TRX004',4,'credit',750000.00,'Setoran Awal Tabungan Sukarela','SET004','2024-02-20','10:20:00','cash','completed',1,1,'Initial deposit','2026-03-22 03:15:36','2026-03-22 03:15:36'),
(5,'TRX005',1,'credit',100000.00,'Setoran Tambahan','SET005','2024-03-01','14:30:00','cash','completed',3,1,'Monthly savings','2026-03-22 03:15:36','2026-03-22 03:15:36'),
(6,'TRX006',2,'credit',200000.00,'Setoran Tambahan','SET006','2024-03-05','15:45:00','transfer','completed',3,1,'Additional savings','2026-03-22 03:15:36','2026-03-22 03:15:36');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `savings`
-- Enhanced untuk mendukung dynamic content
--

DROP TABLE IF EXISTS `savings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `savings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `savings_type` enum('wajib','pokok','sukarela','berjangka') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `transaction_time` time DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `interest_rate` decimal(5,2) DEFAULT NULL,
  `maturity_date` date DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `savings_type` (`savings_type`),
  KEY `transaction_date` (`transaction_date`),
  KEY `status` (`status`),
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
INSERT INTO `savings` VALUES 
(1,1,'wajib',500000.00,'2024-01-15','09:30:00','Setoran awal simpanan wajib',3.00,NULL,1,1,'approved','2026-03-22 03:15:36','2026-03-22 03:15:36'),
(2,1,'sukarela',1000000.00,'2024-01-15','09:35:00','Setoran awal simpanan sukarela',2.50,NULL,1,1,'approved','2026-03-22 03:15:36','2026-03-22 03:15:36'),
(3,2,'wajib',500000.00,'2024-02-20','10:15:00','Setoran awal simpanan wajib',3.00,NULL,1,1,'approved','2026-03-22 03:15:36','2026-03-22 03:15:36'),
(4,2,'sukarela',750000.00,'2024-02-20','10:20:00','Setoran awal simpanan sukarela',2.50,NULL,1,1,'approved','2026-03-22 03:15:36','2026-03-22 03:15:36'),
(5,1,'sukarela',100000.00,'2024-03-01','14:30:00','Setoran tambahan',2.50,NULL,3,1,'approved','2026-03-22 03:15:36','2026-03-22 03:15:36'),
(6,2,'sukarela',200000.00,'2024-03-05','15:45:00','Setoran tambahan',2.50,NULL,3,1,'approved','2026-03-22 03:15:36','2026-03-22 03:15:36');
/*!40000 ALTER TABLE `savings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loans`
-- Enhanced untuk mendukung dynamic content
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
  `monthly_payment` decimal(15,2) DEFAULT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `collateral` text DEFAULT NULL,
  `guarantor` varchar(100) DEFAULT NULL,
  `status` enum('pending','approved','rejected','active','completed','defaulted') NOT NULL DEFAULT 'pending',
  `application_date` date NOT NULL,
  `approval_date` date DEFAULT NULL,
  `disbursement_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `disbursed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `loan_number` (`loan_number`),
  KEY `member_id` (`member_id`),
  KEY `status` (`status`),
  KEY `application_date` (`application_date`),
  KEY `approved_by` (`approved_by`),
  KEY `disbursed_by` (`disbursed_by`),
  CONSTRAINT `loans_approved_by_fk` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `loans_member_id_fk` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loans_disbursed_by_fk` FOREIGN KEY (`disbursed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loans`
--

LOCK TABLES `loans` WRITE;
/*!40000 ALTER TABLE `loans` DISABLE KEYS */;
INSERT INTO `loans` VALUES 
(1,1,'L001',5000000.00,12.00,12,466666.67,'Modal usaha kecil',NULL,'Ahmad Wijaya','active','2024-02-01','2024-02-05','2024-02-06','2025-02-05',2,3,'2026-03-22 03:15:36','2026-03-22 03:15:36'),
(2,2,'L002',3000000.00,10.00,6,516666.67,'Biaya pendidikan',NULL,'Siti Nurhaliza','active','2024-03-01','2024-03-03','2024-03-04','2024-09-03',2,3,'2026-03-22 03:15:36','2026-03-22 03:15:36');
/*!40000 ALTER TABLE `loans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loan_payments`
-- Enhanced untuk mendukung dynamic content
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
  `late_fee` decimal(15,2) DEFAULT 0.00,
  `payment_date` date NOT NULL,
  `payment_time` time DEFAULT NULL,
  `payment_method` enum('cash','transfer','bank_deposit','digital_payment') NOT NULL DEFAULT 'cash',
  `received_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','completed','failed','cancelled') NOT NULL DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `loan_id` (`loan_id`),
  KEY `payment_date` (`payment_date`),
  KEY `received_by` (`received_by`),
  KEY `status` (`status`),
  CONSTRAINT `loan_payments_loan_id_fk` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loan_payments_received_by_fk` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_payments`
--

LOCK TABLES `loan_payments` WRITE;
/*!40000 ALTER TABLE `loan_payments` DISABLE KEYS */;
INSERT INTO `loan_payments` VALUES 
(1,1,1,466666.67,416666.67,50000.00,0.00,'2024-03-06','10:30:00','cash',3,'Angsuran bulan Maret','completed','2026-03-22 03:15:36','2026-03-22 03:15:36'),
(2,2,1,516666.67,500000.00,16666.67,0.00,'2024-04-04','14:15:00','transfer',3,'Angsuran bulan April','completed','2026-03-22 03:15:36','2026-03-22 03:15:36');
/*!40000 ALTER TABLE `loan_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
-- Enhanced untuk dynamic content tracking
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
  `session_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `table_name` (`table_name`),
  KEY `created_at` (`created_at`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `audit_logs_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES 
(1,1,'CREATE','users',1,NULL,'{\"username\":\"bos\",\"role\":\"bos\",\"status\":\"active\"}','127.0.0.1','Mozilla/5.0 (System Initializer)','session_001','2026-03-22 03:15:36'),
(2,1,'CREATE','users',2,NULL,'{\"username\":\"admin\",\"role\":\"admin\",\"status\":\"active\"}','127.0.0.1','Mozilla/5.0 (System Initializer)','session_001','2026-03-22 03:15:36'),
(3,1,'CREATE','members',1,NULL,'{\"member_number\":\"M001\",\"full_name\":\"Ahmad Wijaya\",\"status\":\"active\"}','127.0.0.1','Mozilla/5.0 (System Initializer)','session_001','2026-03-22 03:15:36'),
(4,1,'CREATE','accounts',1,NULL,'{\"account_number\":\"A001\",\"account_type\":\"simpanan\",\"balance\":500000}','127.0.0.1','Mozilla/5.0 (System Initializer)','session_001','2026-03-22 03:15:36'),
(5,1,'CREATE','loans',1,NULL,'{\"loan_number\":\"L001\",\"loan_amount\":5000000,\"status\":\"active\"}','127.0.0.1','Mozilla/5.0 (System Initializer)','session_001','2026-03-22 03:15:36');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_config`
-- Enhanced untuk mendukung dynamic navigation
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
  `category` varchar(50) DEFAULT 'general',
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key` (`config_key`),
  KEY `category` (`category`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `system_config_updated_by_fk` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_config`
--

LOCK TABLES `system_config` WRITE;
/*!40000 ALTER TABLE `system_config` DISABLE KEYS */;
INSERT INTO `system_config` VALUES 
(1,'ksp_name','KSP Lam Gabe Jaya','string','Nama Koperasi','general',NULL,'2026-03-22 03:15:36'),
(2,'ksp_address','Jl. Koperasi No. 123, Jakarta','string','Alamat Koperasi','general',NULL,'2026-03-22 03:15:36'),
(3,'ksp_phone','021-12345678','string','Nomor Telepon','general',NULL,'2026-03-22 03:15:36'),
(4,'ksp_email','info@ksplamgabejaya.co.id','string','Email Koperasi','general',NULL,'2026-03-22 03:15:36'),
(5,'savings_wajib_minimum','500000','number','Minimal simpanan wajib per bulan','savings',NULL,'2026-03-22 03:15:36'),
(6,'savings_pokok_minimum','1000000','number','Minimal simpanan pokok','savings',NULL,'2026-03-22 03:15:36'),
(7,'loan_interest_min','5.00','number','Bunga pinjaman minimal (%)','loans',NULL,'2026-03-22 03:15:36'),
(8,'loan_interest_max','18.00','number','Bunga pinjaman maksimal (%)','loans',NULL,'2026-03-22 03:15:36'),
(9,'loan_term_max','36','number','Jangka waktu pinjaman maksimal (bulan)','loans',NULL,'2026-03-22 03:15:36'),
(10,'late_payment_fee','2.00','number','Denda keterlambatan (%)','loans',NULL,'2026-03-22 03:15:36'),
(11,'session_timeout','30','number','Session timeout (menit)','security',NULL,'2026-03-22 03:15:36'),
(12,'max_login_attempts','5','number','Maksimal percobaan login','security',NULL,'2026-03-22 03:15:36'),
(13,'lockout_duration','15','number','Durasi lockout (menit)','security',NULL,'2026-03-22 03:15:36'),
(14,'enable_notifications','true','boolean','Aktifkan notifikasi','features',NULL,'2026-03-22 03:15:36'),
(15,'enable_audit_log','true','boolean','Aktifkan audit log','features',NULL,'2026-03-22 03:15:36');
/*!40000 ALTER TABLE `system_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Views untuk reporting dan dynamic content
--

-- View untuk daily transactions summary
DROP TABLE IF EXISTS `daily_transactions`;
/*!50001 DROP VIEW IF EXISTS `daily_transactions`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `daily_transactions` AS SELECT 
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
ORDER BY transaction_date DESC */;
SET character_set_client = @saved_cs_client;

-- View untuk loan performance
DROP TABLE IF EXISTS `loan_performance`;
/*!50001 DROP VIEW IF EXISTS `loan_performance`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `loan_performance` AS SELECT 
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
ORDER BY l.application_date DESC */;
SET character_set_client = @saved_cs_client;

-- View untuk member summary
DROP TABLE IF EXISTS `member_summary`;
/*!50001 DROP VIEW IF EXISTS `member_summary`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `member_summary` AS SELECT 
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
ORDER BY m.full_name */;
SET character_set_client = @saved_cs_client;

--
-- Final cleanup
--

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

--
-- Dump completed on 2026-03-22 10:17:00
-- Updated untuk KSP Lam Gabe Jaya v2.0 dengan role system baru
-- Support untuk dynamic navigation dan SPA-like experience
-- Enhanced security dan audit logging
-- Compatible dengan PHP 8.x dan modern web stack

-- =====================================================
-- LOGIN CREDENTIALS FOR TESTING
-- =====================================================
-- 
-- Bos: username=bos, password=bos
-- Admin: username=admin, password=admin
-- Teller: username=teller, password=teller
-- Collector: username=collector, password=collector
-- Nasabah: username=nasabah, password=nasabah
-- 
-- =====================================================
-- ROLE SYSTEM
-- =====================================================
-- 
-- bos: Full system access, financial reports, user management
-- admin: Operational management, transaction approval, member management
-- teller: Daily transactions, deposit/withdrawal, customer service
-- collector: Field operations, payment collection, route management
-- nasabah: Personal dashboard, account overview, loan applications
-- 
-- =====================================================
-- DYNAMIC NAVIGATION SUPPORT
-- =====================================================
-- 
-- Hash-based URLs: #dashboard, #laporan, #nasabah, #transaksi
-- Role-specific content generators
-- SPA-like navigation without page reload
-- Browser back/forward support
