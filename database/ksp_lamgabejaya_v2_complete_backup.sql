-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: ksp_lamgabejaya_v2
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
-- Table structure for table `fund_requests`
--

DROP TABLE IF EXISTS `fund_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fund_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `request_amount` decimal(12,2) NOT NULL,
  `request_type` enum('circular_fund','emergency_fund','special_fund') DEFAULT 'circular_fund',
  `purpose` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','disbursed') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `fund_requests_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `fund_requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fund_requests`
--

LOCK TABLES `fund_requests` WRITE;
/*!40000 ALTER TABLE `fund_requests` DISABLE KEYS */;
INSERT INTO `fund_requests` VALUES (1,1,5000000.00,'circular_fund','Modal usaha','approved',NULL,NULL,NULL,'2026-03-20 22:26:45','2026-03-20 22:26:45'),(2,2,3000000.00,'circular_fund','Kebutuhan rumah','approved',NULL,NULL,NULL,'2026-03-20 22:26:45','2026-03-20 22:26:45'),(3,3,10000000.00,'emergency_fund','Darurat medis','pending',NULL,NULL,NULL,'2026-03-20 22:26:45','2026-03-20 22:26:45');
/*!40000 ALTER TABLE `fund_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gps_tracking`
--

DROP TABLE IF EXISTS `gps_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gps_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `location_name` varchar(255) DEFAULT NULL,
  `visit_type` enum('member_visit','collection','survey','other') DEFAULT 'member_visit',
  `visit_purpose` text DEFAULT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `gps_tracking_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gps_tracking`
--

LOCK TABLES `gps_tracking` WRITE;
/*!40000 ALTER TABLE `gps_tracking` DISABLE KEYS */;
INSERT INTO `gps_tracking` VALUES (1,2,-6.20880000,106.84560000,'Jakarta Pusat','member_visit','Collection visit','active','2026-03-20 22:26:38','2026-03-20 22:26:38'),(2,2,-6.17510000,106.86500000,'Jakarta Utara','member_visit','Loan follow-up','active','2026-03-20 22:26:38','2026-03-20 22:26:38'),(3,2,-6.22970000,106.82950000,'Jakarta Barat','collection','Payment collection','active','2026-03-20 22:26:38','2026-03-20 22:26:38');
/*!40000 ALTER TABLE `gps_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guarantee_payment_tracking`
--

DROP TABLE IF EXISTS `guarantee_payment_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guarantee_payment_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guarantee_id` int(11) NOT NULL,
  `payment_amount` decimal(12,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_type` enum('principal','interest','penalty') DEFAULT 'principal',
  `payment_status` enum('paid','pending','overdue') DEFAULT 'paid',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `guarantee_id` (`guarantee_id`),
  CONSTRAINT `guarantee_payment_tracking_ibfk_1` FOREIGN KEY (`guarantee_id`) REFERENCES `loan_guarantees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guarantee_payment_tracking`
--

LOCK TABLES `guarantee_payment_tracking` WRITE;
/*!40000 ALTER TABLE `guarantee_payment_tracking` DISABLE KEYS */;
INSERT INTO `guarantee_payment_tracking` VALUES (1,1,500000.00,'2024-01-15','principal','paid','Monthly payment','2026-03-20 22:18:47','2026-03-20 22:18:47'),(2,2,300000.00,'2024-01-15','principal','paid','Monthly payment','2026-03-20 22:18:47','2026-03-20 22:18:47'),(3,3,1000000.00,'2024-01-20','principal','pending','Upcoming payment','2026-03-20 22:18:47','2026-03-20 22:18:47');
/*!40000 ALTER TABLE `guarantee_payment_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guarantee_relationships`
--

DROP TABLE IF EXISTS `guarantee_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guarantee_relationships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guarantee_id` int(11) NOT NULL,
  `related_person_id` int(11) NOT NULL,
  `relationship_type` enum('family','friend','colleague','business') DEFAULT 'family',
  `relationship_strength` enum('weak','moderate','strong') DEFAULT 'moderate',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `guarantee_id` (`guarantee_id`),
  CONSTRAINT `guarantee_relationships_ibfk_1` FOREIGN KEY (`guarantee_id`) REFERENCES `loan_guarantees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guarantee_relationships`
--

LOCK TABLES `guarantee_relationships` WRITE;
/*!40000 ALTER TABLE `guarantee_relationships` DISABLE KEYS */;
INSERT INTO `guarantee_relationships` VALUES (1,1,2,'family','strong','Active','2026-03-20 22:18:39','2026-03-20 22:18:39'),(2,2,3,'friend','moderate','Active','2026-03-20 22:18:39','2026-03-20 22:18:39'),(3,3,1,'business','strong','Active','2026-03-20 22:18:39','2026-03-20 22:18:39');
/*!40000 ALTER TABLE `guarantee_relationships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guarantee_risk_assessments`
--

DROP TABLE IF EXISTS `guarantee_risk_assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guarantee_risk_assessments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guarantee_id` int(11) NOT NULL,
  `person_id` int(11) DEFAULT NULL,
  `risk_level` enum('low','medium','high') DEFAULT 'medium',
  `risk_score` decimal(5,2) DEFAULT 50.00,
  `assessment_details` text DEFAULT NULL,
  `status` enum('Active','Completed','Cancelled') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `guarantee_id` (`guarantee_id`),
  CONSTRAINT `guarantee_risk_assessments_ibfk_1` FOREIGN KEY (`guarantee_id`) REFERENCES `loan_guarantees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guarantee_risk_assessments`
--

LOCK TABLES `guarantee_risk_assessments` WRITE;
/*!40000 ALTER TABLE `guarantee_risk_assessments` DISABLE KEYS */;
INSERT INTO `guarantee_risk_assessments` VALUES (1,1,NULL,'low',25.50,'Good credit history, stable income','Completed','2026-03-20 22:18:35','2026-03-20 22:18:35'),(2,2,NULL,'medium',55.75,'Moderate risk, needs monitoring','Completed','2026-03-20 22:18:35','2026-03-20 22:18:35'),(3,3,NULL,'high',78.25,'High risk due to large amount','Active','2026-03-20 22:18:35','2026-03-20 22:18:35');
/*!40000 ALTER TABLE `guarantee_risk_assessments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loan_guarantees`
--

DROP TABLE IF EXISTS `loan_guarantees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_guarantees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guarantee_id` varchar(50) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `guarantor_id` int(11) NOT NULL,
  `loan_amount` decimal(12,2) NOT NULL,
  `guarantee_type` enum('personal','corporate','collateral') DEFAULT 'personal',
  `description` text DEFAULT NULL,
  `status` enum('Active','Pending','Expired','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `guarantee_id` (`guarantee_id`),
  KEY `borrower_id` (`borrower_id`),
  KEY `guarantor_id` (`guarantor_id`),
  CONSTRAINT `loan_guarantees_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `members` (`id`),
  CONSTRAINT `loan_guarantees_ibfk_2` FOREIGN KEY (`guarantor_id`) REFERENCES `members` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_guarantees`
--

LOCK TABLES `loan_guarantees` WRITE;
/*!40000 ALTER TABLE `loan_guarantees` DISABLE KEYS */;
INSERT INTO `loan_guarantees` VALUES (1,'GR001',1,2,5000000.00,'personal','Guarantee for John Doe loan','Active','2026-03-20 22:18:29','2026-03-20 22:18:29'),(2,'GR002',2,3,3000000.00,'personal','Guarantee for Jane Smith loan','Active','2026-03-20 22:18:29','2026-03-20 22:18:29'),(3,'GR003',3,1,10000000.00,'corporate','Guarantee for Budi Santoso loan','Pending','2026-03-20 22:18:29','2026-03-20 22:18:29');
/*!40000 ALTER TABLE `loan_guarantees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loans`
--

DROP TABLE IF EXISTS `loans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `interest_rate` decimal(5,2) DEFAULT 0.00,
  `term_months` int(11) DEFAULT 12,
  `purpose` text DEFAULT NULL,
  `status` enum('Pending','Active','Completed','Defaulted') DEFAULT 'Pending',
  `person_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loans`
--

LOCK TABLES `loans` WRITE;
/*!40000 ALTER TABLE `loans` DISABLE KEYS */;
INSERT INTO `loans` VALUES (1,1,5000000.00,12.00,12,'Modal Usaha','Active',NULL,'2026-03-20 22:16:49','2026-03-20 22:16:49'),(2,2,3000000.00,10.00,6,'Kebutuhan Rumah','Active',NULL,'2026-03-20 22:16:49','2026-03-20 22:16:49'),(3,3,10000000.00,15.00,24,'Ekspansi Bisnis','Pending',NULL,'2026-03-20 22:16:49','2026-03-20 22:16:49');
/*!40000 ALTER TABLE `loans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('Active','Inactive','Pending') DEFAULT 'Active',
  `person_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `members`
--

LOCK TABLES `members` WRITE;
/*!40000 ALTER TABLE `members` DISABLE KEYS */;
INSERT INTO `members` VALUES (1,'John Doe','john@example.com','08123456789',NULL,'Active',NULL,'2026-03-20 22:16:33','2026-03-20 22:16:33'),(2,'Jane Smith','jane@example.com','08198765432',NULL,'Active',NULL,'2026-03-20 22:16:33','2026-03-20 22:16:33'),(3,'Budi Santoso','budi@example.com','08234567890',NULL,'Active',NULL,'2026-03-20 22:16:33','2026-03-20 22:16:33');
/*!40000 ALTER TABLE `members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migration_batches`
--

DROP TABLE IF EXISTS `migration_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migration_batches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_name` varchar(255) NOT NULL,
  `batch_type` enum('members','loans','payments','staff') NOT NULL,
  `total_records` int(11) DEFAULT 0,
  `success_records` int(11) DEFAULT 0,
  `failed_records` int(11) DEFAULT 0,
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`batch_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration_batches`
--

LOCK TABLES `migration_batches` WRITE;
/*!40000 ALTER TABLE `migration_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `migration_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migration_logs`
--

DROP TABLE IF EXISTS `migration_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migration_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_id` int(11) NOT NULL,
  `row_number` int(11) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `status` enum('success','failed','skipped') NOT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_batch` (`batch_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `migration_logs_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `migration_batches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration_logs`
--

LOCK TABLES `migration_logs` WRITE;
/*!40000 ALTER TABLE `migration_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `migration_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migration_templates`
--

DROP TABLE IF EXISTS `migration_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migration_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(255) NOT NULL,
  `template_type` enum('members','loans','payments','staff') NOT NULL,
  `template_structure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`template_structure`)),
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`template_type`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration_templates`
--

LOCK TABLES `migration_templates` WRITE;
/*!40000 ALTER TABLE `migration_templates` DISABLE KEYS */;
INSERT INTO `migration_templates` VALUES (1,'Member Import Template','members','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":false,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"address\",\"required\":true,\"type\":\"string\"},{\"name\":\"nik\",\"required\":false,\"type\":\"string\"},{\"name\":\"birth_date\",\"required\":false,\"type\":\"date\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"}]}','2026-03-21 05:24:20'),(2,'Loan Import Template','loans','{\"columns\":[{\"name\":\"member_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"interest_rate\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"loan_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"due_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"purpose\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:24:20'),(3,'Payment Import Template','payments','{\"columns\":[{\"name\":\"loan_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"payment_amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"payment_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"payment_method\",\"required\":false,\"type\":\"string\"},{\"name\":\"notes\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:24:20'),(4,'Staff Import Template','staff','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":true,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"position\",\"required\":true,\"type\":\"string\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"salary\",\"required\":false,\"type\":\"decimal\"}]}','2026-03-21 05:24:20'),(5,'Member Import Template','members','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":false,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"address\",\"required\":true,\"type\":\"string\"},{\"name\":\"nik\",\"required\":false,\"type\":\"string\"},{\"name\":\"birth_date\",\"required\":false,\"type\":\"date\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"}]}','2026-03-21 05:33:15'),(6,'Loan Import Template','loans','{\"columns\":[{\"name\":\"member_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"interest_rate\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"loan_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"due_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"purpose\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:33:15'),(7,'Payment Import Template','payments','{\"columns\":[{\"name\":\"loan_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"payment_amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"payment_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"payment_method\",\"required\":false,\"type\":\"string\"},{\"name\":\"notes\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:33:15'),(8,'Staff Import Template','staff','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":true,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"position\",\"required\":true,\"type\":\"string\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"salary\",\"required\":false,\"type\":\"decimal\"}]}','2026-03-21 05:33:15'),(9,'Member Import Template','members','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":false,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"address\",\"required\":true,\"type\":\"string\"},{\"name\":\"nik\",\"required\":false,\"type\":\"string\"},{\"name\":\"birth_date\",\"required\":false,\"type\":\"date\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"}]}','2026-03-21 05:35:17'),(10,'Loan Import Template','loans','{\"columns\":[{\"name\":\"member_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"interest_rate\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"loan_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"due_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"purpose\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:35:17'),(11,'Payment Import Template','payments','{\"columns\":[{\"name\":\"loan_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"payment_amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"payment_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"payment_method\",\"required\":false,\"type\":\"string\"},{\"name\":\"notes\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:35:17'),(12,'Staff Import Template','staff','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":true,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"position\",\"required\":true,\"type\":\"string\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"salary\",\"required\":false,\"type\":\"decimal\"}]}','2026-03-21 05:35:17');
/*!40000 ALTER TABLE `migration_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_transactions`
--

DROP TABLE IF EXISTS `payment_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `loan_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_type` enum('loan_payment','savings_deposit','loan_disbursement','fee') DEFAULT 'loan_payment',
  `payment_method` enum('cash','bank_transfer','digital_wallet','check') DEFAULT 'cash',
  `payment_date` date NOT NULL,
  `status` enum('pending','completed','failed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `loan_id` (`loan_id`),
  CONSTRAINT `payment_transactions_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `payment_transactions_ibfk_2` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_transactions`
--

LOCK TABLES `payment_transactions` WRITE;
/*!40000 ALTER TABLE `payment_transactions` DISABLE KEYS */;
INSERT INTO `payment_transactions` VALUES (1,1,1,500000.00,'loan_payment','cash','2026-03-21','completed',NULL,'2026-03-20 22:26:31','2026-03-20 22:26:31'),(2,2,2,300000.00,'loan_payment','bank_transfer','2026-03-21','completed',NULL,'2026-03-20 22:26:31','2026-03-20 22:26:31'),(3,1,NULL,200000.00,'savings_deposit','cash','2026-03-21','completed',NULL,'2026-03-20 22:26:31','2026-03-20 22:26:31');
/*!40000 ALTER TABLE `payment_transactions` ENABLE KEYS */;
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
  `amount` decimal(12,2) NOT NULL,
  `type` enum('Regular','Fixed','Special') DEFAULT 'Regular',
  `interest_rate` decimal(5,2) DEFAULT 0.00,
  `status` enum('Active','Inactive','Closed') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `savings_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `savings`
--

LOCK TABLES `savings` WRITE;
/*!40000 ALTER TABLE `savings` DISABLE KEYS */;
INSERT INTO `savings` VALUES (1,1,2000000.00,'Regular',5.00,'Active','2026-03-20 22:16:56','2026-03-20 22:16:56'),(2,2,1500000.00,'Fixed',7.00,'Active','2026-03-20 22:16:56','2026-03-20 22:16:56'),(3,3,5000000.00,'Special',8.00,'Active','2026-03-20 22:16:56','2026-03-20 22:16:56');
/*!40000 ALTER TABLE `savings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `type` enum('string','number','boolean','json') DEFAULT 'string',
  `category` varchar(50) DEFAULT 'general',
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `category` (`category`),
  KEY `is_public` (`is_public`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'cooperative_name','KSP Lam Gabe Jaya','Nama Koperasi','string','general',0,'2026-03-20 22:17:20','2026-03-20 22:17:20'),(2,'interest_rate','12.00','Suku Bunga Default','string','general',0,'2026-03-20 22:17:20','2026-03-20 22:17:20'),(3,'max_loan_amount','50000000','Maksimal Pinjaman','string','general',0,'2026-03-20 22:17:20','2026-03-20 22:17:20'),(4,'min_savings_amount','100000','Minimal Simpanan','string','general',0,'2026-03-20 22:17:20','2026-03-20 22:17:20');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (1,'Admin','System Administrator','[\"all\"]','Active','2026-03-20 19:46:10','2026-03-20 19:46:10'),(2,'Staff','Staff User','[\"members\",\"loans\",\"savings\",\"gps\"]','Active','2026-03-20 19:46:10','2026-03-20 19:46:10'),(3,'Member','Member User','[\"view_own_data\",\"apply_loan\",\"view_savings\"]','Active','2026-03-20 19:46:10','2026-03-20 19:46:10');
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` bigint(20) unsigned DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `role` enum('admin','staff','member') DEFAULT 'member',
  `status` enum('Active','Inactive','Suspended') DEFAULT 'Active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `name` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `status` (`status`),
  KEY `idx_person_id` (`person_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'admin','admin@ksp.com','$2y$10$RWCUAv.s7A9Le7pFHQ5ZR.YShnoZWHN8h54MirOTS8.sV34E015Ly','System Administrator','08123456789','admin','Active',NULL,'2026-03-20 19:46:10','2026-03-20 21:07:33','Administrator'),(2,NULL,'staff','staff@ksp.com','$2y$10$a/Zh3rbr4pq8pm1jeyFcSeq572U3L3BxbiUvMX140EEsKcKl2mLEm','Staff User','08123456790','staff','Active',NULL,'2026-03-20 19:46:10','2026-03-20 21:07:33','Staff Member'),(3,NULL,'member','member@ksp.com','$2y$10$6KgBPRrPFKaQacQPTfF3j.e/KSXMl2jPlJM35epLmKw4ihk1E4X5K','Member User','08123456791','member','Active',NULL,'2026-03-20 19:46:10','2026-03-20 21:07:33','Member User');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'ksp_lamgabejaya_v2'
--
