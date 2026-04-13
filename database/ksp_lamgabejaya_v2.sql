-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: ksp_lamgabejaya_v2
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `account_transactions`
--

DROP TABLE IF EXISTS `account_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `transaction_type` enum('Deposit','Withdrawal','Transfer In','Transfer Out','Interest','Fee','Tax') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `balance_before` decimal(15,2) NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `teller_id` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `is_reversed` tinyint(1) DEFAULT 0,
  `reversed_by` int(11) DEFAULT NULL,
  `reversed_date` timestamp NULL DEFAULT NULL,
  `reversal_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_account_transaction` (`account_id`,`transaction_date`),
  KEY `idx_transaction_date` (`transaction_date`),
  KEY `idx_reference_number` (`reference_number`),
  KEY `idx_transactions_amount` (`amount`),
  CONSTRAINT `account_transactions_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_transactions`
--

LOCK TABLES `account_transactions` WRITE;
/*!40000 ALTER TABLE `account_transactions` DISABLE KEYS */;
INSERT INTO `account_transactions` (`id`, `account_id`, `transaction_type`, `amount`, `balance_before`, `balance_after`, `description`, `reference_number`, `transaction_date`, `teller_id`, `approved_by`, `approved_date`, `is_reversed`, `reversed_by`, `reversed_date`, `reversal_reason`, `notes`) VALUES (1,1,'Deposit',100000.00,0.00,100000.00,'Setoran simpanan pokok','TRX-20240115-001','2024-01-15 02:00:00',2,NULL,NULL,0,NULL,NULL,NULL,NULL),(2,2,'Deposit',50000.00,0.00,50000.00,'Setoran simpanan wajib Januari','TRX-20240115-002','2024-01-15 02:05:00',2,NULL,NULL,0,NULL,NULL,NULL,NULL),(3,3,'Deposit',500000.00,0.00,500000.00,'Setoran simpanan sukarela','TRX-20240115-003','2024-01-15 02:10:00',2,NULL,NULL,0,NULL,NULL,NULL,NULL),(4,2,'Deposit',50000.00,50000.00,100000.00,'Setoran simpanan wajib Februari','TRX-20240215-001','2024-02-15 03:00:00',2,NULL,NULL,0,NULL,NULL,NULL,NULL),(5,3,'Deposit',1000000.00,500000.00,1500000.00,'Setoran tabungan','TRX-20240301-001','2024-03-01 03:30:00',2,NULL,NULL,0,NULL,NULL,NULL,NULL),(6,3,'Deposit',1000000.00,1500000.00,2500000.00,'Setoran tabungan','TRX-20240401-001','2024-04-01 04:00:00',2,NULL,NULL,0,NULL,NULL,NULL,NULL),(7,7,'Deposit',50000.00,0.00,50000.00,'Setoran simpanan wajib Maret','TRX-20240305-001','2024-03-05 02:00:00',2,NULL,NULL,0,NULL,NULL,NULL,NULL),(8,8,'Deposit',2000000.00,0.00,2000000.00,'Setoran simpanan sukarela','TRX-20240305-002','2024-03-05 02:10:00',2,NULL,NULL,0,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `account_transactions` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_account_balance` AFTER INSERT ON `account_transactions` FOR EACH ROW BEGIN
    UPDATE accounts 
    SET balance = NEW.balance_after,
        available_balance = CASE 
            WHEN NEW.transaction_type IN ('Withdrawal', 'Transfer Out') 
            THEN balance - hold_amount 
            ELSE available_balance 
        END,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = NEW.account_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `account_types`
--

DROP TABLE IF EXISTS `account_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(15) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `interest_rate` decimal(5,4) DEFAULT 0.0000,
  `minimum_balance` decimal(15,2) DEFAULT 0.00,
  `minimum_deposit` decimal(15,2) DEFAULT 0.00,
  `maximum_deposit` decimal(15,2) DEFAULT 0.00,
  `withdrawal_fee` decimal(15,2) DEFAULT 0.00,
  `is_taxable` tinyint(1) DEFAULT 0,
  `tax_rate` decimal(5,4) DEFAULT 0.0000,
  `requires_approval` tinyint(1) DEFAULT 0,
  `auto_debit_enabled` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_types`
--

LOCK TABLES `account_types` WRITE;
/*!40000 ALTER TABLE `account_types` DISABLE KEYS */;
INSERT INTO `account_types` (`id`, `code`, `name`, `description`, `interest_rate`, `minimum_balance`, `minimum_deposit`, `maximum_deposit`, `withdrawal_fee`, `is_taxable`, `tax_rate`, `requires_approval`, `auto_debit_enabled`, `is_active`, `created_at`, `updated_at`) VALUES (1,'SA_POKOK','Simpanan Pokok','Simpanan wajib satu kali',0.0000,0.00,100000.00,0.00,0.00,0,0.0000,0,0,1,'2026-03-19 08:48:08','2026-03-19 08:48:08'),(2,'SA_WAJIB','Simpanan Wajib','Simpanan wajib bulanan',0.0020,0.00,50000.00,0.00,0.00,0,0.0000,0,1,1,'2026-03-19 08:48:08','2026-03-19 08:48:08'),(3,'SA_SUKARELA','Simpanan Sukarela','Simpanan fleksibel',0.0030,10000.00,10000.00,0.00,0.00,0,0.0000,0,0,1,'2026-03-19 08:48:08','2026-03-19 08:48:08'),(4,'SA_BERJANGKA','Simpanan Berjangka','Simpanan dengan tenor tetap',0.0040,100000.00,100000.00,0.00,0.00,0,0.0000,0,0,1,'2026-03-19 08:48:08','2026-03-19 08:48:08'),(5,'SA_HARI_RAYA','Simpanan Hari Raya','Simpanan untuk hari raya',0.0030,50000.00,50000.00,0.00,0.00,0,0.0000,0,0,1,'2026-03-19 08:48:08','2026-03-19 08:48:08');
/*!40000 ALTER TABLE `account_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_number` varchar(20) NOT NULL,
  `member_id` int(11) NOT NULL,
  `account_type_id` int(11) NOT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `available_balance` decimal(15,2) DEFAULT 0.00,
  `hold_amount` decimal(15,2) DEFAULT 0.00,
  `interest_rate` decimal(5,4) DEFAULT NULL,
  `opening_date` date NOT NULL,
  `maturity_date` date DEFAULT NULL,
  `last_interest_date` date DEFAULT NULL,
  `status` enum('Active','Dormant','Frozen','Closed') DEFAULT 'Active',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_number` (`account_number`),
  KEY `account_type_id` (`account_type_id`),
  KEY `idx_account_number` (`account_number`),
  KEY `idx_member_account` (`member_id`,`account_type_id`),
  KEY `idx_status` (`status`),
  KEY `idx_accounts_balance` (`balance`),
  CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `accounts_ibfk_2` FOREIGN KEY (`account_type_id`) REFERENCES `account_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts`
--

LOCK TABLES `accounts` WRITE;
/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
INSERT INTO `accounts` (`id`, `account_number`, `member_id`, `account_type_id`, `account_name`, `balance`, `available_balance`, `hold_amount`, `interest_rate`, `opening_date`, `maturity_date`, `last_interest_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES (1,'SA-POK-001',1,1,'Simpanan Pokok - Ahmad Wijaya',100000.00,100000.00,0.00,NULL,'2024-01-15',NULL,NULL,'Active',NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:30:08'),(2,'SA-WAJ-001',1,2,'Simpanan Wajib - Ahmad Wijaya',100000.00,600000.00,0.00,NULL,'2024-01-15',NULL,NULL,'Active',NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:30:08'),(3,'SA-SUK-001',1,3,'Simpanan Sukarela - Ahmad Wijaya',2500000.00,2500000.00,0.00,NULL,'2024-01-15',NULL,NULL,'Active',NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:30:08'),(4,'SA-POK-002',2,1,'Simpanan Pokok - Siti Nurhaliza',100000.00,100000.00,0.00,NULL,'2024-02-10',NULL,NULL,'Active',NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:29:48'),(5,'SA-WAJ-002',2,2,'Simpanan Wajib - Siti Nurhaliza',550000.00,550000.00,0.00,NULL,'2024-02-10',NULL,NULL,'Active',NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:29:48'),(6,'SA-POK-003',3,1,'Simpanan Pokok - Budi Santoso',100000.00,100000.00,0.00,NULL,'2024-03-05',NULL,NULL,'Active',NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:29:48'),(7,'SA-WAJ-003',3,2,'Simpanan Wajib - Budi Santoso',50000.00,700000.00,0.00,NULL,'2024-03-05',NULL,NULL,'Active',NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:30:08'),(8,'SA-SUK-003',3,3,'Simpanan Sukarela - Budi Santoso',2000000.00,5000000.00,0.00,NULL,'2024-03-05',NULL,NULL,'Active',NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:30:08'),(9,'SA-POK-004',4,1,'Simpanan Pokok - Dewi Lestari',100000.00,100000.00,0.00,NULL,'2024-04-20',NULL,NULL,'Active',NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:29:48'),(10,'SA-WAJ-004',4,2,'Simpanan Wajib - Dewi Lestari',400000.00,400000.00,0.00,NULL,'2024-04-20',NULL,NULL,'Active',NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:29:48'),(11,'SA-POK-005',5,1,'Simpanan Pokok - Eko Prasetyo',500000.00,500000.00,0.00,NULL,'2023-06-05',NULL,NULL,'Active',NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:29:48'),(12,'SA-WAJ-005',5,2,'Simpanan Wajib - Eko Prasetyo',1500000.00,1500000.00,0.00,NULL,'2023-06-05',NULL,NULL,'Active',NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:29:48'),(13,'SA-BRJ-005',5,4,'Simpanan Berjangka - Eko Prasetyo',10000000.00,10000000.00,0.00,NULL,'2023-06-05',NULL,NULL,'Active',NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:29:48');
/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_action` (`user_id`,`action`),
  KEY `idx_table_record` (`table_name`,`record_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (1,1,'CREATE','members',1,NULL,'{\"member_number\":\"MEM-2026-001\",\"full_name\":\"Ahmad Wijaya\"}','127.0.0.1',NULL,'2026-04-13 02:30:08'),(2,1,'CREATE','members',2,NULL,'{\"member_number\":\"MEM-2026-002\",\"full_name\":\"Siti Nurhaliza\"}','127.0.0.1',NULL,'2026-04-13 02:30:08'),(3,1,'CREATE','loans',1,NULL,'{\"loan_number\":\"PIN-2024-001\",\"amount\":3000000}','127.0.0.1',NULL,'2026-04-13 02:30:08'),(4,2,'LOGIN','users',2,NULL,'{\"username\":\"teller1\"}','127.0.0.1',NULL,'2026-04-13 02:30:08');
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auto_debit_config`
--

DROP TABLE IF EXISTS `auto_debit_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auto_debit_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `debit_amount` decimal(15,2) NOT NULL,
  `debit_day` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_debit_date` date DEFAULT NULL,
  `next_debit_date` date DEFAULT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `max_failed_attempts` int(11) DEFAULT 3,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `idx_member_debit` (`member_id`),
  KEY `idx_next_debit` (`next_debit_date`),
  CONSTRAINT `auto_debit_config_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `auto_debit_config_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auto_debit_config`
--

LOCK TABLES `auto_debit_config` WRITE;
/*!40000 ALTER TABLE `auto_debit_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `auto_debit_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collateral`
--

DROP TABLE IF EXISTS `collateral`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collateral` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) DEFAULT NULL,
  `member_id` int(11) NOT NULL,
  `collateral_type` enum('Property','Vehicle','Savings','Guarantor','Other') NOT NULL,
  `description` text NOT NULL,
  `estimated_value` decimal(15,2) DEFAULT NULL,
  `appraisal_value` decimal(15,2) DEFAULT NULL,
  `appraisal_date` date DEFAULT NULL,
  `location` text DEFAULT NULL,
  `documents` text DEFAULT NULL,
  `status` enum('Active','Released','Sold','Expired') DEFAULT 'Active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_loan_collateral` (`loan_id`),
  KEY `idx_member_collateral` (`member_id`),
  CONSTRAINT `collateral_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`),
  CONSTRAINT `collateral_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collateral`
--

LOCK TABLES `collateral` WRITE;
/*!40000 ALTER TABLE `collateral` DISABLE KEYS */;
/*!40000 ALTER TABLE `collateral` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credit_scoring_criteria`
--

DROP TABLE IF EXISTS `credit_scoring_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credit_scoring_criteria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `weight` decimal(5,3) NOT NULL,
  `max_score` decimal(5,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credit_scoring_criteria`
--

LOCK TABLES `credit_scoring_criteria` WRITE;
/*!40000 ALTER TABLE `credit_scoring_criteria` DISABLE KEYS */;
INSERT INTO `credit_scoring_criteria` (`id`, `name`, `description`, `weight`, `max_score`, `is_active`, `created_at`) VALUES (1,'Membership Duration','Lama keanggotaan',0.150,100.00,1,'2026-03-19 08:48:12'),(2,'Savings History','Riwayat simpanan',0.200,100.00,1,'2026-03-19 08:48:12'),(3,'Previous Loans','Riwayat pinjaman sebelumnya',0.250,100.00,1,'2026-03-19 08:48:12'),(4,'Income Stability','Stabilitas pendapatan',0.200,100.00,1,'2026-03-19 08:48:12'),(5,'Collateral Value','Nilai jaminan',0.200,100.00,1,'2026-03-19 08:48:12');
/*!40000 ALTER TABLE `credit_scoring_criteria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credit_scoring_details`
--

DROP TABLE IF EXISTS `credit_scoring_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credit_scoring_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scoring_result_id` int(11) NOT NULL,
  `criteria_id` int(11) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scoring_result_id` (`scoring_result_id`),
  KEY `criteria_id` (`criteria_id`),
  CONSTRAINT `credit_scoring_details_ibfk_1` FOREIGN KEY (`scoring_result_id`) REFERENCES `credit_scoring_results` (`id`),
  CONSTRAINT `credit_scoring_details_ibfk_2` FOREIGN KEY (`criteria_id`) REFERENCES `credit_scoring_criteria` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credit_scoring_details`
--

LOCK TABLES `credit_scoring_details` WRITE;
/*!40000 ALTER TABLE `credit_scoring_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `credit_scoring_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credit_scoring_results`
--

DROP TABLE IF EXISTS `credit_scoring_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credit_scoring_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `loan_application_id` int(11) DEFAULT NULL,
  `total_score` decimal(5,2) NOT NULL,
  `risk_level` enum('Low','Medium','High','Very High') NOT NULL,
  `recommendation` enum('Approve','Reject','Manual Review') NOT NULL,
  `scoring_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `scored_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_member_scoring` (`member_id`),
  KEY `idx_scoring_date` (`scoring_date`),
  CONSTRAINT `credit_scoring_results_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credit_scoring_results`
--

LOCK TABLES `credit_scoring_results` WRITE;
/*!40000 ALTER TABLE `credit_scoring_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `credit_scoring_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `daily_transactions`
--

DROP TABLE IF EXISTS `daily_transactions`;
/*!50001 DROP VIEW IF EXISTS `daily_transactions`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `daily_transactions` AS SELECT
 1 AS `transaction_date`,
  1 AS `total_transactions`,
  1 AS `total_deposits`,
  1 AS `total_withdrawals`,
  1 AS `active_accounts` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `loan_installments`
--

DROP TABLE IF EXISTS `loan_installments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loan_installments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) NOT NULL,
  `installment_number` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `principal_amount` decimal(15,2) NOT NULL,
  `interest_amount` decimal(15,2) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `paid_date` date DEFAULT NULL,
  `late_fee` decimal(15,2) DEFAULT 0.00,
  `status` enum('Pending','Paid','Late','Overdue') DEFAULT 'Pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_loan_installment` (`loan_id`,`installment_number`),
  KEY `idx_due_date` (`due_date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `loan_installments_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_installments`
--

LOCK TABLES `loan_installments` WRITE;
/*!40000 ALTER TABLE `loan_installments` DISABLE KEYS */;
INSERT INTO `loan_installments` (`id`, `loan_id`, `installment_number`, `due_date`, `principal_amount`, `interest_amount`, `total_amount`, `paid_amount`, `paid_date`, `late_fee`, `status`, `payment_method`, `receipt_number`, `notes`, `created_at`, `updated_at`) VALUES (1,1,1,'2024-04-07',250000.00,45000.00,295000.00,295000.00,'2024-04-05',0.00,'Paid',NULL,NULL,NULL,'2026-04-13 02:30:08','2026-04-13 02:30:08'),(2,1,2,'2024-05-07',250000.00,45000.00,295000.00,295000.00,'2024-05-06',0.00,'Paid',NULL,NULL,NULL,'2026-04-13 02:30:08','2026-04-13 02:30:08'),(3,1,3,'2024-06-07',250000.00,45000.00,295000.00,295000.00,'2024-06-05',0.00,'Paid',NULL,NULL,NULL,'2026-04-13 02:30:08','2026-04-13 02:30:08'),(4,1,4,'2024-07-07',250000.00,45000.00,295000.00,295000.00,'2024-07-06',0.00,'Paid',NULL,NULL,NULL,'2026-04-13 02:30:08','2026-04-13 02:30:08'),(5,1,5,'2024-08-07',250000.00,45000.00,295000.00,295000.00,'2024-08-05',0.00,'Paid',NULL,NULL,NULL,'2026-04-13 02:30:08','2026-04-13 02:30:08'),(6,1,6,'2024-09-07',250000.00,45000.00,295000.00,295000.00,'2024-09-04',0.00,'Paid',NULL,NULL,NULL,'2026-04-13 02:30:08','2026-04-13 02:30:08'),(7,1,7,'2024-10-07',250000.00,45000.00,295000.00,295000.00,'2024-10-05',0.00,'Paid',NULL,NULL,NULL,'2026-04-13 02:30:08','2026-04-13 02:30:08'),(8,1,8,'2024-11-07',250000.00,45000.00,295000.00,295000.00,'2024-11-06',0.00,'Paid',NULL,NULL,NULL,'2026-04-13 02:30:08','2026-04-13 02:30:08'),(9,1,9,'2024-12-07',250000.00,45000.00,295000.00,0.00,NULL,0.00,'Pending',NULL,NULL,NULL,'2026-04-13 02:30:08','2026-04-13 02:30:08'),(10,1,10,'2025-01-07',250000.00,45000.00,295000.00,0.00,NULL,0.00,'Pending',NULL,NULL,NULL,'2026-04-13 02:30:08','2026-04-13 02:30:08'),(11,1,11,'2025-02-07',250000.00,45000.00,295000.00,0.00,NULL,0.00,'Pending',NULL,NULL,NULL,'2026-04-13 02:30:08','2026-04-13 02:30:08'),(12,1,12,'2025-03-07',250000.00,45000.00,295000.00,0.00,NULL,0.00,'Pending',NULL,NULL,NULL,'2026-04-13 02:30:08','2026-04-13 02:30:08');
/*!40000 ALTER TABLE `loan_installments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loan_payments`
--

DROP TABLE IF EXISTS `loan_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loan_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) NOT NULL,
  `payment_amount` decimal(15,2) NOT NULL,
  `principal_portion` decimal(15,2) NOT NULL,
  `interest_portion` decimal(15,2) NOT NULL,
  `late_fee_portion` decimal(15,2) DEFAULT 0.00,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` enum('Cash','Bank Transfer','Auto Debit','Digital Payment') NOT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `teller_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_loan_payment` (`loan_id`,`payment_date`),
  KEY `idx_payment_date` (`payment_date`),
  KEY `idx_reference_number` (`reference_number`),
  CONSTRAINT `loan_payments_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_payments`
--

LOCK TABLES `loan_payments` WRITE;
/*!40000 ALTER TABLE `loan_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `loan_portfolio`
--

DROP TABLE IF EXISTS `loan_portfolio`;
/*!50001 DROP VIEW IF EXISTS `loan_portfolio`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `loan_portfolio` AS SELECT
 1 AS `loan_type`,
  1 AS `total_loans`,
  1 AS `total_disbursed`,
  1 AS `total_outstanding`,
  1 AS `avg_interest_rate`,
  1 AS `late_loans`,
  1 AS `default_loans` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `loan_types`
--

DROP TABLE IF EXISTS `loan_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loan_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(15) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `interest_rate` decimal(5,4) NOT NULL,
  `admin_fee_rate` decimal(5,4) DEFAULT 0.0000,
  `late_fee_rate` decimal(5,4) DEFAULT 0.0000,
  `minimum_amount` decimal(15,2) DEFAULT 0.00,
  `maximum_amount` decimal(15,2) DEFAULT 0.00,
  `minimum_term_months` int(11) DEFAULT 1,
  `maximum_term_months` int(11) DEFAULT 60,
  `collateral_required` tinyint(1) DEFAULT 0,
  `guarantee_required` tinyint(1) DEFAULT 0,
  `insurance_required` tinyint(1) DEFAULT 0,
  `calculation_method` enum('Flat','Effective','Anuitas') DEFAULT 'Flat',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_types`
--

LOCK TABLES `loan_types` WRITE;
/*!40000 ALTER TABLE `loan_types` DISABLE KEYS */;
INSERT INTO `loan_types` (`id`, `code`, `name`, `description`, `interest_rate`, `admin_fee_rate`, `late_fee_rate`, `minimum_amount`, `maximum_amount`, `minimum_term_months`, `maximum_term_months`, `collateral_required`, `guarantee_required`, `insurance_required`, `calculation_method`, `is_active`, `created_at`, `updated_at`) VALUES (1,'KONSUMTIF','Pinjaman Konsumtif','Pinjaman untuk kebutuhan konsumtif',0.0150,0.0100,0.0000,500000.00,5000000.00,1,60,1,0,0,'Flat',1,'2026-03-19 08:48:11','2026-03-19 08:48:11'),(2,'PRODUKTIF','Pinjaman Produktif','Pinjaman untuk usaha produktif',0.0120,0.0100,0.0000,1000000.00,20000000.00,1,60,1,0,0,'Flat',1,'2026-03-19 08:48:11','2026-03-19 08:48:11'),(3,'DARURAT','Pinjaman Darurat','Pinjaman darurat cepat cair',0.0200,0.0200,0.0000,250000.00,2000000.00,1,60,0,0,0,'Flat',1,'2026-03-19 08:48:11','2026-03-19 08:48:11'),(4,'ANGSURAN','Pinjaman Angsuran','Pinjaman dengan angsuran tetap',0.0130,0.0100,0.0000,500000.00,10000000.00,1,60,1,0,0,'Flat',1,'2026-03-19 08:48:11','2026-03-19 08:48:11');
/*!40000 ALTER TABLE `loan_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loans`
--

DROP TABLE IF EXISTS `loans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_number` varchar(20) NOT NULL,
  `member_id` int(11) NOT NULL,
  `loan_type_id` int(11) NOT NULL,
  `application_date` date NOT NULL,
  `approval_date` date DEFAULT NULL,
  `disbursement_date` date DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `interest_rate` decimal(5,4) NOT NULL,
  `admin_fee` decimal(15,2) DEFAULT 0.00,
  `insurance_fee` decimal(15,2) DEFAULT 0.00,
  `term_months` int(11) NOT NULL,
  `calculation_method` enum('Flat','Effective','Anuitas') NOT NULL,
  `monthly_installment` decimal(15,2) NOT NULL,
  `total_interest` decimal(15,2) NOT NULL,
  `total_payment` decimal(15,2) NOT NULL,
  `outstanding_balance` decimal(15,2) NOT NULL,
  `next_payment_date` date DEFAULT NULL,
  `maturity_date` date DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `status` enum('Applied','Approved','Rejected','Disbursed','Active','Late','Default','Restructured','Paid Off') DEFAULT 'Applied',
  `rejection_reason` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `loan_number` (`loan_number`),
  KEY `loan_type_id` (`loan_type_id`),
  KEY `idx_loan_number` (`loan_number`),
  KEY `idx_member_loan` (`member_id`),
  KEY `idx_status` (`status`),
  KEY `idx_application_date` (`application_date`),
  KEY `idx_loans_amount` (`amount`),
  CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`loan_type_id`) REFERENCES `loan_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loans`
--

LOCK TABLES `loans` WRITE;
/*!40000 ALTER TABLE `loans` DISABLE KEYS */;
INSERT INTO `loans` (`id`, `loan_number`, `member_id`, `loan_type_id`, `application_date`, `approval_date`, `disbursement_date`, `amount`, `interest_rate`, `admin_fee`, `insurance_fee`, `term_months`, `calculation_method`, `monthly_installment`, `total_interest`, `total_payment`, `outstanding_balance`, `next_payment_date`, `maturity_date`, `purpose`, `status`, `rejection_reason`, `approved_by`, `notes`, `created_at`, `updated_at`) VALUES (1,'PIN-2024-001',2,1,'2024-03-01','2024-03-05','2024-03-07',3000000.00,0.0150,30000.00,0.00,12,'Flat',292500.00,540000.00,3570000.00,2335000.00,'2026-04-07','2025-03-07','Kebutuhan konsumtif keluarga','Active',NULL,1,NULL,'2026-04-13 02:29:48','2026-04-13 02:29:48'),(2,'PIN-2024-002',3,2,'2024-04-10','2024-04-15','2024-04-17',10000000.00,0.0120,100000.00,0.00,24,'Flat',516667.00,2880000.00,12980000.00,7233338.00,'2026-04-17','2026-04-17','Modal usaha warung makan','Active',NULL,1,NULL,'2026-04-13 02:29:48','2026-04-13 02:29:48'),(3,'PIN-2024-003',1,3,'2024-06-01','2024-06-03','2024-06-05',1500000.00,0.0200,30000.00,0.00,6,'Flat',300000.00,180000.00,1710000.00,0.00,'2024-12-05','2024-12-05','Dana darurat','Paid Off',NULL,1,NULL,'2026-04-13 02:29:48','2026-04-13 02:29:48');
/*!40000 ALTER TABLE `loans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `attempts` int(11) DEFAULT 1,
  `lock_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_lock_until` (`lock_until`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_documents`
--

DROP TABLE IF EXISTS `member_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `document_type` enum('KTP','KK','NPWP','Slip Gaji','Surat Nikah','Other') NOT NULL,
  `document_number` varchar(50) DEFAULT NULL,
  `document_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry_date` date DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verified_by` int(11) DEFAULT NULL,
  `verified_date` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_member_document` (`member_id`,`document_type`),
  CONSTRAINT `member_documents_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_documents`
--

LOCK TABLES `member_documents` WRITE;
/*!40000 ALTER TABLE `member_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_status_history`
--

DROP TABLE IF EXISTS `member_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `old_status` enum('Active','Inactive','Suspended','Blacklisted','Resigned','Deceased') DEFAULT NULL,
  `new_status` enum('Active','Inactive','Suspended','Blacklisted','Resigned','Deceased') NOT NULL,
  `reason` text DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_member_status` (`member_id`,`changed_at`),
  CONSTRAINT `member_status_history_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_status_history`
--

LOCK TABLES `member_status_history` WRITE;
/*!40000 ALTER TABLE `member_status_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `member_summary`
--

DROP TABLE IF EXISTS `member_summary`;
/*!50001 DROP VIEW IF EXISTS `member_summary`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `member_summary` AS SELECT
 1 AS `id`,
  1 AS `member_number`,
  1 AS `full_name`,
  1 AS `member_type`,
  1 AS `phone_number`,
  1 AS `email`,
  1 AS `status`,
  1 AS `registration_date`,
  1 AS `total_accounts`,
  1 AS `total_savings`,
  1 AS `total_loans`,
  1 AS `total_outstanding` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `member_types`
--

DROP TABLE IF EXISTS `member_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `min_savings_pokok` decimal(15,2) DEFAULT 0.00,
  `min_savings_wajib` decimal(15,2) DEFAULT 0.00,
  `max_loan_amount` decimal(15,2) DEFAULT 0.00,
  `max_concurrent_loans` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_types`
--

LOCK TABLES `member_types` WRITE;
/*!40000 ALTER TABLE `member_types` DISABLE KEYS */;
INSERT INTO `member_types` (`id`, `name`, `description`, `min_savings_pokok`, `min_savings_wajib`, `max_loan_amount`, `max_concurrent_loans`, `is_active`, `created_at`, `updated_at`) VALUES (1,'Regular','Anggota Biasa',100000.00,50000.00,5000000.00,1,1,'2026-03-19 08:48:07','2026-03-19 08:48:07'),(2,'Premium','Anggota Prioritas',250000.00,100000.00,10000000.00,1,1,'2026-03-19 08:48:07','2026-03-19 08:48:07'),(3,'Board','Pengurus Koperasi',500000.00,200000.00,20000000.00,1,1,'2026-03-19 08:48:07','2026-03-19 08:48:07'),(4,'Honorary','Anggota Kehormatan',0.00,0.00,0.00,1,1,'2026-03-19 08:48:07','2026-03-19 08:48:07'),(5,'Associate','Anggota Associate',50000.00,25000.00,2500000.00,1,1,'2026-03-19 08:48:07','2026-03-19 08:48:07');
/*!40000 ALTER TABLE `member_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_number` varchar(20) NOT NULL,
  `member_type_id` int(11) NOT NULL,
  `title` varchar(10) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `place_of_birth` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('L','P') NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `family_card_number` varchar(50) DEFAULT NULL,
  `tax_id_number` varchar(30) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text NOT NULL,
  `village` varchar(50) DEFAULT NULL,
  `district` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `monthly_income` decimal(15,2) DEFAULT NULL,
  `marital_status` enum('Single','Married','Divorced','Widowed') DEFAULT NULL,
  `spouse_name` varchar(100) DEFAULT NULL,
  `spouse_phone` varchar(20) DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `emergency_contact_relation` varchar(50) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `signature_path` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive','Suspended','Blacklisted','Resigned','Deceased') DEFAULT 'Active',
  `registration_date` date NOT NULL,
  `activation_date` date DEFAULT NULL,
  `deactivation_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_number` (`member_number`),
  UNIQUE KEY `id_number` (`id_number`),
  KEY `member_type_id` (`member_type_id`),
  KEY `idx_member_number` (`member_number`),
  KEY `idx_id_number` (`id_number`),
  KEY `idx_phone` (`phone_number`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_members_full_name` (`full_name`),
  CONSTRAINT `members_ibfk_1` FOREIGN KEY (`member_type_id`) REFERENCES `member_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `members`
--

LOCK TABLES `members` WRITE;
/*!40000 ALTER TABLE `members` DISABLE KEYS */;
INSERT INTO `members` (`id`, `member_number`, `member_type_id`, `title`, `full_name`, `place_of_birth`, `date_of_birth`, `gender`, `id_number`, `family_card_number`, `tax_id_number`, `phone_number`, `mobile_number`, `email`, `address`, `village`, `district`, `city`, `province`, `postal_code`, `occupation`, `company_name`, `monthly_income`, `marital_status`, `spouse_name`, `spouse_phone`, `emergency_contact_name`, `emergency_contact_phone`, `emergency_contact_relation`, `photo_path`, `signature_path`, `status`, `registration_date`, `activation_date`, `deactivation_date`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES (1,'MEM-2026-001',1,NULL,'Ahmad Wijaya',NULL,NULL,'L','3171051203890001',NULL,NULL,'021-1234567','081234567890','ahmad.wijaya@email.com','Jl. Merdeka No. 1, RT 01/02',NULL,NULL,'Jakarta','DKI Jakarta',NULL,'Wiraswasta',NULL,5000000.00,'Married',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active','2024-01-10','2024-01-15',NULL,NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:29:48'),(2,'MEM-2026-002',1,NULL,'Siti Nurhaliza',NULL,NULL,'P','3271054505900002',NULL,NULL,'021-2345678','082345678901','siti.nurhaliza@email.com','Jl. Sudirman No. 2, RT 03/04',NULL,NULL,'Bandung','Jawa Barat',NULL,'Karyawan Swasta',NULL,4500000.00,'Married',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active','2024-02-05','2024-02-10',NULL,NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:29:48'),(3,'MEM-2026-003',2,NULL,'Budi Santoso',NULL,NULL,'L','3371051203910003',NULL,NULL,'021-3456789','083456789012','budi.santoso@email.com','Jl. Gatotkaca No. 3, RT 05/06',NULL,NULL,'Surabaya','Jawa Timur',NULL,'PNS',NULL,7000000.00,'Married',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active','2024-03-01','2024-03-05',NULL,NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:29:48'),(4,'MEM-2026-004',1,NULL,'Dewi Lestari',NULL,NULL,'P','3471056704920004',NULL,NULL,'021-4567890','084567890123','dewi.lestari@email.com','Jl. Pancasila No. 4, RT 07/08',NULL,NULL,'Yogyakarta','DI Yogyakarta',NULL,'Guru',NULL,4000000.00,'Single',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active','2024-04-15','2024-04-20',NULL,NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:29:48'),(5,'MEM-2026-005',3,NULL,'Eko Prasetyo',NULL,NULL,'L','3571058805930005',NULL,NULL,'021-5678901','085678901234','eko.prasetyo@email.com','Jl. Pahlawan No. 5, RT 09/10',NULL,NULL,'Semarang','Jawa Tengah',NULL,'Pengusaha',NULL,12000000.00,'Married',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active','2023-06-01','2023-06-05',NULL,NULL,NULL,'2026-04-13 02:29:48','2026-04-13 02:29:48');
/*!40000 ALTER TABLE `members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_token` (`token`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `data_type` enum('string','number','boolean','json') DEFAULT 'string',
  `is_editable` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `data_type`, `is_editable`, `created_at`, `updated_at`) VALUES (1,'company_name','KSP Lam Gabe Jaya','Nama perusahaan','string',1,'2026-03-19 08:48:16','2026-03-19 08:48:16'),(2,'company_address','Jl. Contoh No. 123, Jakarta','Alamat perusahaan','string',1,'2026-03-19 08:48:16','2026-03-19 08:48:16'),(3,'company_phone','+62-21-1234567','Nomor telepon','string',1,'2026-03-19 08:48:16','2026-03-19 08:48:16'),(4,'company_email','info@ksp-lamgabejaya.co.id','Email perusahaan','string',1,'2026-03-19 08:48:16','2026-03-19 08:48:16'),(5,'interest_savings_rate','0.003','Suku bunga simpanan default','number',1,'2026-03-19 08:48:16','2026-03-19 08:48:16'),(6,'late_fee_rate','0.001','Denda keterlambatan','number',1,'2026-03-19 08:48:16','2026-03-19 08:48:16'),(7,'min_credit_score','50','Skor kredit minimum','number',1,'2026-03-19 08:48:16','2026-03-19 08:48:16'),(8,'max_loan_amount','50000000','Maksimal pinjaman','number',1,'2026-03-19 08:48:16','2026-03-19 08:48:16');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `token_blacklist`
--

DROP TABLE IF EXISTS `token_blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `token_blacklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(600) NOT NULL,
  `blacklisted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`(255)),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `token_blacklist`
--

LOCK TABLES `token_blacklist` WRITE;
/*!40000 ALTER TABLE `token_blacklist` DISABLE KEYS */;
/*!40000 ALTER TABLE `token_blacklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('Super Admin','Admin','Manager','Teller','Staff') NOT NULL,
  `permissions` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `role`, `permissions`, `is_active`, `last_login`, `failed_login_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES (1,'admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin@ksp-lamgabejaya.co.id','Administrator','Super Admin',NULL,1,'2026-04-13 03:56:45',0,NULL,'2026-03-19 08:48:16','2026-04-13 03:56:45'),(2,'teller1','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','teller1@ksp-lamgabejaya.co.id','Teller Satu','Teller',NULL,1,NULL,0,NULL,'2026-03-19 08:48:16','2026-03-19 08:48:16'),(3,'manager1','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','manager1@ksp-lamgabejaya.co.id','Manager Satu','Manager',NULL,1,NULL,0,NULL,'2026-03-19 08:48:16','2026-03-19 08:48:16');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'ksp_lamgabejaya_v2'
--
