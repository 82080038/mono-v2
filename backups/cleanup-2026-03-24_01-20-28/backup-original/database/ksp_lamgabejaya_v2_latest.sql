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
-- Table structure for table `ai_models`
--

DROP TABLE IF EXISTS `ai_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(100) NOT NULL,
  `model_type` varchar(50) NOT NULL,
  `version` varchar(20) NOT NULL,
  `accuracy` decimal(5,4) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_models`
--

LOCK TABLES `ai_models` WRITE;
/*!40000 ALTER TABLE `ai_models` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_models` ENABLE KEYS */;
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
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_action` (`user_id`,`action`),
  KEY `idx_table_record` (`table_name`,`record_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_audit_logs_user_id` (`user_id`),
  KEY `idx_audit_logs_action` (`action`),
  KEY `idx_audit_logs_table_name` (`table_name`),
  KEY `idx_audit_logs_record_id` (`record_id`),
  KEY `idx_audit_logs_ip_address` (`ip_address`),
  KEY `idx_audit_logs_created_at` (`created_at`),
  KEY `idx_audit_logs_user_action` (`user_id`,`action`),
  KEY `idx_audit_logs_table_record` (`table_name`,`record_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs_enhanced`
--

DROP TABLE IF EXISTS `audit_logs_enhanced`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs_enhanced` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_logs_enhanced_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs_enhanced`
--

LOCK TABLES `audit_logs_enhanced` WRITE;
/*!40000 ALTER TABLE `audit_logs_enhanced` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs_enhanced` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_jobs`
--

DROP TABLE IF EXISTS `backup_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_name` varchar(100) NOT NULL,
  `backup_type` enum('full','incremental','differential') NOT NULL,
  `status` enum('pending','running','completed','failed') DEFAULT 'pending',
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_jobs`
--

LOCK TABLES `backup_jobs` WRITE;
/*!40000 ALTER TABLE `backup_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_schedules`
--

DROP TABLE IF EXISTS `backup_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_name` varchar(100) NOT NULL,
  `schedule_type` enum('daily','weekly','monthly') NOT NULL,
  `schedule_time` time NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_run` timestamp NULL DEFAULT NULL,
  `next_run` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_job_schedule` (`job_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_schedules`
--

LOCK TABLES `backup_schedules` WRITE;
/*!40000 ALTER TABLE `backup_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `external_services`
--

DROP TABLE IF EXISTS `external_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `external_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(50) NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `api_endpoint` varchar(500) DEFAULT NULL,
  `api_key_encrypted` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_verified` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_service_name` (`service_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `external_services`
--

LOCK TABLES `external_services` WRITE;
/*!40000 ALTER TABLE `external_services` DISABLE KEYS */;
/*!40000 ALTER TABLE `external_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fraud_detection`
--

DROP TABLE IF EXISTS `fraud_detection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fraud_detection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `fraud_score` decimal(5,2) NOT NULL,
  `risk_factors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`risk_factors`)),
  `status` enum('safe','suspicious','blocked') DEFAULT 'safe',
  `detected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fraud_detection_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fraud_detection`
--

LOCK TABLES `fraud_detection` WRITE;
/*!40000 ALTER TABLE `fraud_detection` DISABLE KEYS */;
/*!40000 ALTER TABLE `fraud_detection` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Table structure for table `fund_transfers`
--

DROP TABLE IF EXISTS `fund_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fund_transfers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_staff_id` int(11) NOT NULL,
  `to_staff_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `transferred_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `from_staff_id` (`from_staff_id`),
  KEY `to_staff_id` (`to_staff_id`),
  CONSTRAINT `fund_transfers_ibfk_1` FOREIGN KEY (`from_staff_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fund_transfers_ibfk_2` FOREIGN KEY (`to_staff_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fund_transfers`
--

LOCK TABLES `fund_transfers` WRITE;
/*!40000 ALTER TABLE `fund_transfers` DISABLE KEYS */;
/*!40000 ALTER TABLE `fund_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geofence_areas`
--

DROP TABLE IF EXISTS `geofence_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geofence_areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `radius` decimal(8,2) NOT NULL DEFAULT 100.00,
  `type` enum('office','branch','member_area','restricted','safe_zone') DEFAULT 'office',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geofence_areas`
--

LOCK TABLES `geofence_areas` WRITE;
/*!40000 ALTER TABLE `geofence_areas` DISABLE KEYS */;
/*!40000 ALTER TABLE `geofence_areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gps_logs`
--

DROP TABLE IF EXISTS `gps_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gps_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `tracking_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `accuracy` decimal(8,2) DEFAULT NULL,
  `altitude` decimal(8,2) DEFAULT NULL,
  `speed` decimal(6,2) DEFAULT NULL,
  `heading` decimal(5,2) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_staff_timestamp` (`staff_id`,`timestamp`),
  KEY `idx_coordinates` (`latitude`,`longitude`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_gps_logs_staff_id` (`staff_id`),
  KEY `idx_gps_logs_tracking_id` (`tracking_id`),
  KEY `idx_gps_logs_timestamp` (`timestamp`),
  KEY `idx_gps_logs_created_at` (`created_at`),
  KEY `idx_gps_logs_staff_timestamp` (`staff_id`,`timestamp`),
  KEY `idx_gps_logs_tracking_timestamp` (`tracking_id`,`timestamp`),
  CONSTRAINT `gps_logs_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gps_logs_ibfk_2` FOREIGN KEY (`tracking_id`) REFERENCES `gps_tracking` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gps_logs`
--

LOCK TABLES `gps_logs` WRITE;
/*!40000 ALTER TABLE `gps_logs` DISABLE KEYS */;
INSERT INTO `gps_logs` VALUES (1,2,NULL,-6.20880000,106.84560000,NULL,NULL,NULL,NULL,'2026-03-21 14:30:51','2026-03-21 20:30:51','2026-03-21 20:30:51'),(2,2,NULL,-6.20880000,106.84560000,NULL,NULL,NULL,NULL,'2026-03-21 14:31:41','2026-03-21 20:31:41','2026-03-21 20:31:41'),(3,2,NULL,-6.20880000,106.84560000,NULL,NULL,NULL,NULL,'2026-03-21 14:31:44','2026-03-21 20:31:44','2026-03-21 20:31:44'),(4,2,NULL,-6.20880000,106.84560000,NULL,NULL,NULL,NULL,'2026-03-21 14:32:54','2026-03-21 20:32:54','2026-03-21 20:32:54');
/*!40000 ALTER TABLE `gps_logs` ENABLE KEYS */;
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
  `member_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `route_plan` text DEFAULT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `location_name` varchar(255) DEFAULT NULL,
  `visit_type` enum('member_visit','collection','survey','other') DEFAULT 'member_visit',
  `visit_purpose` text DEFAULT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ended_at` timestamp NULL DEFAULT NULL,
  `start_latitude` decimal(10,8) DEFAULT NULL,
  `start_longitude` decimal(11,8) DEFAULT NULL,
  `end_latitude` decimal(10,8) DEFAULT NULL,
  `end_longitude` decimal(11,8) DEFAULT NULL,
  `distance_km` decimal(8,3) DEFAULT 0.000,
  `duration_minutes` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_gps_tracking_staff_id` (`staff_id`),
  KEY `idx_gps_tracking_member_id` (`member_id`),
  KEY `idx_gps_tracking_status` (`status`),
  KEY `idx_gps_tracking_created_at` (`created_at`),
  KEY `idx_gps_tracking_started_at` (`started_at`),
  KEY `idx_gps_tracking_ended_at` (`ended_at`),
  KEY `idx_gps_tracking_staff_status` (`staff_id`,`status`),
  KEY `idx_gps_tracking_member_status` (`member_id`,`status`),
  CONSTRAINT `gps_tracking_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`),
  CONSTRAINT `gps_tracking_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gps_tracking`
--

LOCK TABLES `gps_tracking` WRITE;
/*!40000 ALTER TABLE `gps_tracking` DISABLE KEYS */;
INSERT INTO `gps_tracking` VALUES (1,2,NULL,-6.20880000,NULL,NULL,106.84560000,'Jakarta Pusat','member_visit','Collection visit','active','2026-03-21 20:36:05',NULL,NULL,NULL,NULL,NULL,0.000,0,'2026-03-20 22:26:38','2026-03-20 22:26:38'),(2,2,NULL,-6.17510000,NULL,NULL,106.86500000,'Jakarta Utara','member_visit','Loan follow-up','active','2026-03-21 20:36:05',NULL,NULL,NULL,NULL,NULL,0.000,0,'2026-03-20 22:26:38','2026-03-20 22:26:38'),(3,2,NULL,-6.22970000,NULL,NULL,106.82950000,'Jakarta Barat','collection','Payment collection','active','2026-03-21 20:36:05',NULL,NULL,NULL,NULL,NULL,0.000,0,'2026-03-20 22:26:38','2026-03-20 22:26:38');
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
-- Table structure for table `integration_logs`
--

DROP TABLE IF EXISTS `integration_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `integration_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `integration_name` varchar(50) NOT NULL,
  `request_type` varchar(20) NOT NULL,
  `request_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_data`)),
  `response_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_data`)),
  `status` enum('success','failed') NOT NULL,
  `error_message` text DEFAULT NULL,
  `execution_time_ms` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `integration_logs`
--

LOCK TABLES `integration_logs` WRITE;
/*!40000 ALTER TABLE `integration_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `integration_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loan_applicant_addresses`
--

DROP TABLE IF EXISTS `loan_applicant_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_applicant_addresses` (
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
  KEY `idx_location` (`province_id`,`regency_id`,`district_id`,`village_id`),
  CONSTRAINT `fk_loan_applicant_addresses_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `loan_applicants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_applicant_addresses`
--

LOCK TABLES `loan_applicant_addresses` WRITE;
/*!40000 ALTER TABLE `loan_applicant_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan_applicant_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loan_applicant_identities`
--

DROP TABLE IF EXISTS `loan_applicant_identities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_applicant_identities` (
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
  KEY `idx_identity_type_number` (`identity_type`,`identity_number`),
  CONSTRAINT `fk_loan_applicant_identities_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `loan_applicants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_applicant_identities`
--

LOCK TABLES `loan_applicant_identities` WRITE;
/*!40000 ALTER TABLE `loan_applicant_identities` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan_applicant_identities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loan_applicants`
--

DROP TABLE IF EXISTS `loan_applicants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_applicants` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_applicants`
--

LOCK TABLES `loan_applicants` WRITE;
/*!40000 ALTER TABLE `loan_applicants` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan_applicants` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER IF NOT EXISTS `generate_application_number` 
BEFORE INSERT ON `loan_applicants`
FOR EACH ROW
BEGIN
    IF NEW.application_number IS NULL OR NEW.application_number = '' THEN
        SET NEW.application_number = CONCAT('LA', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD(FLOOR(RAND() * 1000), 3, '0'));
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `loan_applications`
--

DROP TABLE IF EXISTS `loan_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `purpose` text NOT NULL,
  `term_months` int(11) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `status` enum('pending','approved','rejected','disbursed') DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `loan_applications_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_applications`
--

LOCK TABLES `loan_applications` WRITE;
/*!40000 ALTER TABLE `loan_applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan_applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loan_approvals`
--

DROP TABLE IF EXISTS `loan_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `status` enum('approved','rejected') NOT NULL,
  `notes` text DEFAULT NULL,
  `approved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `application_id` (`application_id`),
  KEY `approver_id` (`approver_id`),
  CONSTRAINT `loan_approvals_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `loan_applications` (`id`),
  CONSTRAINT `loan_approvals_ibfk_2` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_approvals`
--

LOCK TABLES `loan_approvals` WRITE;
/*!40000 ALTER TABLE `loan_approvals` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan_approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loan_collaterals`
--

DROP TABLE IF EXISTS `loan_collaterals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_collaterals` (
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
  CONSTRAINT `fk_loan_collaterals_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `loan_applicants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_loan_collaterals_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_collaterals`
--

LOCK TABLES `loan_collaterals` WRITE;
/*!40000 ALTER TABLE `loan_collaterals` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan_collaterals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loan_disbursements`
--

DROP TABLE IF EXISTS `loan_disbursements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_disbursements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `disbursement_method` varchar(50) NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `disbursed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `loan_id` (`loan_id`),
  CONSTRAINT `loan_disbursements_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_disbursements`
--

LOCK TABLES `loan_disbursements` WRITE;
/*!40000 ALTER TABLE `loan_disbursements` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan_disbursements` ENABLE KEYS */;
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
  `applicant_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `interest_rate` decimal(5,2) DEFAULT 0.00,
  `term_months` int(11) DEFAULT 12,
  `purpose` text DEFAULT NULL,
  `collateral_type` enum('None','Property','Vehicle','Guarantor','Other') DEFAULT 'None',
  `collateral_value` decimal(12,2) DEFAULT NULL,
  `payment_method` enum('Angsuran Bulanan','Angsuran Mingguan','Jatuh Tempo') DEFAULT 'Angsuran Bulanan',
  `status` enum('Pending','Active','Completed','Defaulted') DEFAULT 'Pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `disbursed_at` timestamp NULL DEFAULT NULL,
  `monthly_payment` decimal(12,2) DEFAULT NULL,
  `total_interest` decimal(12,2) DEFAULT NULL,
  `remaining_balance` decimal(12,2) DEFAULT NULL,
  `next_payment_date` date DEFAULT NULL,
  `person_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_applicant_id` (`applicant_id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_loans_member_id` (`member_id`),
  KEY `idx_loans_status` (`status`),
  KEY `idx_loans_purpose` (`purpose`(768)),
  KEY `idx_loans_amount` (`amount`),
  KEY `idx_loans_term_months` (`term_months`),
  KEY `idx_loans_interest_rate` (`interest_rate`),
  KEY `idx_loans_next_payment_date` (`next_payment_date`),
  KEY `idx_loans_created_at` (`created_at`),
  KEY `idx_loans_updated_at` (`updated_at`),
  KEY `idx_loans_member_status` (`member_id`,`status`),
  CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loans`
--

LOCK TABLES `loans` WRITE;
/*!40000 ALTER TABLE `loans` DISABLE KEYS */;
INSERT INTO `loans` VALUES (1,1,NULL,5000000.00,12.00,12,'Modal Usaha','None',NULL,'Angsuran Bulanan','Active',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-20 22:16:49','2026-03-20 22:16:49'),(2,2,NULL,3000000.00,10.00,6,'Kebutuhan Rumah','None',NULL,'Angsuran Bulanan','Active',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-20 22:16:49','2026-03-20 22:16:49'),(3,3,NULL,10000000.00,15.00,24,'Ekspansi Bisnis','None',NULL,'Angsuran Bulanan','Pending',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-20 22:16:49','2026-03-20 22:16:49');
/*!40000 ALTER TABLE `loans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_addresses`
--

DROP TABLE IF EXISTS `member_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `member_addresses` (
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
  KEY `idx_location` (`province_id`,`regency_id`,`district_id`,`village_id`),
  CONSTRAINT `fk_member_addresses_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_addresses`
--

LOCK TABLES `member_addresses` WRITE;
/*!40000 ALTER TABLE `member_addresses` DISABLE KEYS */;
INSERT INTO `member_addresses` VALUES (1,1,'Residence',11,'DKI Jakarta',71,'Jakarta Pusat',727,'Menteng',847,'Menteng','01','02','Jl. Test No. 123','10310',NULL,NULL,1,'Active','2026-03-21 18:46:13','2026-03-21 18:46:13'),(2,1,'Residence',NULL,'DKI Jakarta',NULL,'Jakarta Pusat',NULL,'Menteng',NULL,'Menteng','01','02','Jl. Test No. 123','10310',NULL,NULL,1,'Active','2026-03-21 18:46:16','2026-03-21 18:46:16');
/*!40000 ALTER TABLE `member_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_identities`
--

DROP TABLE IF EXISTS `member_identities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `member_identities` (
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
  KEY `idx_identity_type_number` (`identity_type`,`identity_number`),
  CONSTRAINT `fk_member_identities_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_identities`
--

LOCK TABLES `member_identities` WRITE;
/*!40000 ALTER TABLE `member_identities` DISABLE KEYS */;
INSERT INTO `member_identities` VALUES (1,1,'KTP','3201011234560001',NULL,NULL,NULL,1,'Active',NULL,'2026-03-21 18:46:13','2026-03-21 18:46:13'),(2,1,'NPWP','12.345.678.9-123.000',NULL,NULL,NULL,0,'Active',NULL,'2026-03-21 18:46:13','2026-03-21 18:46:13'),(3,1,'KTP','3201011234560001',NULL,NULL,NULL,1,'Active',NULL,'2026-03-21 18:46:16','2026-03-21 18:46:16'),(4,1,'NPWP','12.345.678.9-123.000',NULL,NULL,NULL,0,'Active',NULL,'2026-03-21 18:46:16','2026-03-21 18:46:16');
/*!40000 ALTER TABLE `member_identities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('Active','Inactive','Pending') DEFAULT 'Active',
  `is_active` tinyint(1) DEFAULT 1,
  `credit_score` decimal(5,2) DEFAULT 50.00,
  `membership_type` enum('Regular','Premium','VIP') DEFAULT 'Regular',
  `person_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_members_user_id` (`user_id`),
  KEY `idx_members_email` (`email`),
  KEY `idx_members_phone` (`phone`),
  KEY `idx_members_status` (`status`),
  KEY `idx_members_is_active` (`is_active`),
  KEY `idx_members_join_date` (`join_date`),
  KEY `idx_members_membership_type` (`membership_type`),
  KEY `idx_members_credit_score` (`credit_score`),
  KEY `idx_members_created_at` (`created_at`),
  CONSTRAINT `members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `members`
--

LOCK TABLES `members` WRITE;
/*!40000 ALTER TABLE `members` DISABLE KEYS */;
INSERT INTO `members` VALUES (1,NULL,'John Doe','john@example.com','08123456789',NULL,NULL,NULL,NULL,NULL,NULL,'Active',1,50.00,'Regular',NULL,'2026-03-20 22:16:33','2026-03-20 22:16:33'),(2,NULL,'Jane Smith','jane@example.com','08198765432',NULL,NULL,NULL,NULL,NULL,NULL,'Active',1,50.00,'Regular',NULL,'2026-03-20 22:16:33','2026-03-20 22:16:33'),(3,NULL,'Budi Santoso','budi@example.com','08234567890',NULL,NULL,NULL,NULL,NULL,NULL,'Active',1,50.00,'Regular',NULL,'2026-03-20 22:16:33','2026-03-20 22:16:33');
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration_templates`
--

LOCK TABLES `migration_templates` WRITE;
/*!40000 ALTER TABLE `migration_templates` DISABLE KEYS */;
INSERT INTO `migration_templates` VALUES (1,'Member Import Template','members','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":false,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"address\",\"required\":true,\"type\":\"string\"},{\"name\":\"nik\",\"required\":false,\"type\":\"string\"},{\"name\":\"birth_date\",\"required\":false,\"type\":\"date\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"}]}','2026-03-21 05:24:20'),(2,'Loan Import Template','loans','{\"columns\":[{\"name\":\"member_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"interest_rate\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"loan_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"due_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"purpose\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:24:20'),(3,'Payment Import Template','payments','{\"columns\":[{\"name\":\"loan_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"payment_amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"payment_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"payment_method\",\"required\":false,\"type\":\"string\"},{\"name\":\"notes\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:24:20'),(4,'Staff Import Template','staff','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":true,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"position\",\"required\":true,\"type\":\"string\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"salary\",\"required\":false,\"type\":\"decimal\"}]}','2026-03-21 05:24:20'),(5,'Member Import Template','members','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":false,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"address\",\"required\":true,\"type\":\"string\"},{\"name\":\"nik\",\"required\":false,\"type\":\"string\"},{\"name\":\"birth_date\",\"required\":false,\"type\":\"date\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"}]}','2026-03-21 05:33:15'),(6,'Loan Import Template','loans','{\"columns\":[{\"name\":\"member_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"interest_rate\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"loan_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"due_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"purpose\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:33:15'),(7,'Payment Import Template','payments','{\"columns\":[{\"name\":\"loan_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"payment_amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"payment_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"payment_method\",\"required\":false,\"type\":\"string\"},{\"name\":\"notes\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:33:15'),(8,'Staff Import Template','staff','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":true,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"position\",\"required\":true,\"type\":\"string\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"salary\",\"required\":false,\"type\":\"decimal\"}]}','2026-03-21 05:33:15'),(9,'Member Import Template','members','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":false,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"address\",\"required\":true,\"type\":\"string\"},{\"name\":\"nik\",\"required\":false,\"type\":\"string\"},{\"name\":\"birth_date\",\"required\":false,\"type\":\"date\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"}]}','2026-03-21 05:35:17'),(10,'Loan Import Template','loans','{\"columns\":[{\"name\":\"member_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"interest_rate\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"loan_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"due_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"purpose\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:35:17'),(11,'Payment Import Template','payments','{\"columns\":[{\"name\":\"loan_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"payment_amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"payment_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"payment_method\",\"required\":false,\"type\":\"string\"},{\"name\":\"notes\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:35:17'),(12,'Staff Import Template','staff','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":true,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"position\",\"required\":true,\"type\":\"string\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"salary\",\"required\":false,\"type\":\"decimal\"}]}','2026-03-21 05:35:17'),(13,'Member Import Template','members','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":false,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"address\",\"required\":true,\"type\":\"string\"},{\"name\":\"nik\",\"required\":false,\"type\":\"string\"},{\"name\":\"birth_date\",\"required\":false,\"type\":\"date\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"}]}','2026-03-21 23:30:02'),(14,'Loan Import Template','loans','{\"columns\":[{\"name\":\"member_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"interest_rate\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"loan_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"due_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"purpose\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 23:30:02'),(15,'Payment Import Template','payments','{\"columns\":[{\"name\":\"loan_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"payment_amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"payment_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"payment_method\",\"required\":false,\"type\":\"string\"},{\"name\":\"notes\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 23:30:02'),(16,'Staff Import Template','staff','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":true,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"position\",\"required\":true,\"type\":\"string\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"salary\",\"required\":false,\"type\":\"decimal\"}]}','2026-03-21 23:30:02'),(17,'Member Import Template','members','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":false,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"address\",\"required\":true,\"type\":\"string\"},{\"name\":\"nik\",\"required\":false,\"type\":\"string\"},{\"name\":\"birth_date\",\"required\":false,\"type\":\"date\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"}]}','2026-03-21 23:32:15'),(18,'Loan Import Template','loans','{\"columns\":[{\"name\":\"member_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"interest_rate\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"loan_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"due_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"purpose\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 23:32:15'),(19,'Payment Import Template','payments','{\"columns\":[{\"name\":\"loan_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"payment_amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"payment_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"payment_method\",\"required\":false,\"type\":\"string\"},{\"name\":\"notes\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 23:32:15'),(20,'Staff Import Template','staff','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":true,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"position\",\"required\":true,\"type\":\"string\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"salary\",\"required\":false,\"type\":\"decimal\"}]}','2026-03-21 23:32:15'),(21,'Member Import Template','members','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":false,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"address\",\"required\":true,\"type\":\"string\"},{\"name\":\"nik\",\"required\":false,\"type\":\"string\"},{\"name\":\"birth_date\",\"required\":false,\"type\":\"date\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"}]}','2026-03-21 23:36:23'),(22,'Loan Import Template','loans','{\"columns\":[{\"name\":\"member_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"interest_rate\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"loan_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"due_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"purpose\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 23:36:23'),(23,'Payment Import Template','payments','{\"columns\":[{\"name\":\"loan_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"payment_amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"payment_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"payment_method\",\"required\":false,\"type\":\"string\"},{\"name\":\"notes\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 23:36:23'),(24,'Staff Import Template','staff','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":true,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"position\",\"required\":true,\"type\":\"string\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"salary\",\"required\":false,\"type\":\"decimal\"}]}','2026-03-21 23:36:23'),(25,'Member Import Template','members','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":false,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"address\",\"required\":true,\"type\":\"string\"},{\"name\":\"nik\",\"required\":false,\"type\":\"string\"},{\"name\":\"birth_date\",\"required\":false,\"type\":\"date\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"}]}','2026-03-22 03:32:55'),(26,'Loan Import Template','loans','{\"columns\":[{\"name\":\"member_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"interest_rate\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"loan_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"due_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"purpose\",\"required\":false,\"type\":\"string\"}]}','2026-03-22 03:32:55'),(27,'Payment Import Template','payments','{\"columns\":[{\"name\":\"loan_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"payment_amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"payment_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"payment_method\",\"required\":false,\"type\":\"string\"},{\"name\":\"notes\",\"required\":false,\"type\":\"string\"}]}','2026-03-22 03:32:55'),(28,'Staff Import Template','staff','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":true,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"position\",\"required\":true,\"type\":\"string\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"salary\",\"required\":false,\"type\":\"decimal\"}]}','2026-03-22 03:32:55');
/*!40000 ALTER TABLE `migration_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_preferences`
--

DROP TABLE IF EXISTS `notification_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_notification` (`user_id`,`notification_type`),
  CONSTRAINT `notification_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_preferences`
--

LOCK TABLES `notification_preferences` WRITE;
/*!40000 ALTER TABLE `notification_preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','error','success') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_read` (`user_id`,`is_read`),
  KEY `idx_type` (`type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_notifications_user_id` (`user_id`),
  KEY `idx_notifications_type` (`type`),
  KEY `idx_notifications_is_read` (`is_read`),
  KEY `idx_notifications_created_at` (`created_at`),
  KEY `idx_notifications_user_read` (`user_id`,`is_read`),
  KEY `idx_notifications_user_type` (`user_id`,`type`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiry` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (1,1,'23b7eb0e6a775f81fd46e1ac576d2499a3d99be18611d4cc95f73c3ae0ad6d7e','2026-03-21 18:27:45',0,NULL,'2026-03-21 23:27:45'),(2,1,'4d67cd61f7b16e8717972a271cba6e5cc81132c7327f19d0eca075791a2dbefc','2026-03-21 18:28:02',0,NULL,'2026-03-21 23:28:02'),(3,1,'e5413a1a4455b4acd91b4e496fe8a34511e3955a194acb850f9301121422cbb0','2026-03-21 18:28:18',0,NULL,'2026-03-21 23:28:18');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
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
  `savings_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_type` enum('loan_payment','savings_deposit','loan_disbursement','fee') DEFAULT 'loan_payment',
  `payment_method` enum('cash','bank_transfer','digital_wallet','check') DEFAULT 'cash',
  `payment_date` date NOT NULL,
  `status` enum('pending','completed','failed','cancelled') DEFAULT 'pending',
  `description` text DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `processed_by` (`processed_by`),
  KEY `idx_payment_transactions_member_id` (`member_id`),
  KEY `idx_payment_transactions_loan_id` (`loan_id`),
  KEY `idx_payment_transactions_savings_id` (`savings_id`),
  KEY `idx_payment_transactions_payment_method` (`payment_method`),
  KEY `idx_payment_transactions_status` (`status`),
  KEY `idx_payment_transactions_amount` (`amount`),
  KEY `idx_payment_transactions_created_at` (`created_at`),
  KEY `idx_payment_transactions_processed_at` (`processed_at`),
  KEY `idx_payment_transactions_member_status` (`member_id`,`status`),
  CONSTRAINT `payment_transactions_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `payment_transactions_ibfk_2` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`),
  CONSTRAINT `payment_transactions_ibfk_3` FOREIGN KEY (`savings_id`) REFERENCES `savings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_transactions_ibfk_4` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_transactions`
--

LOCK TABLES `payment_transactions` WRITE;
/*!40000 ALTER TABLE `payment_transactions` DISABLE KEYS */;
INSERT INTO `payment_transactions` VALUES (1,1,1,NULL,500000.00,'loan_payment','cash','2026-03-21','completed',NULL,NULL,NULL,NULL,'2026-03-20 22:26:31','2026-03-20 22:26:31'),(2,2,2,NULL,300000.00,'loan_payment','bank_transfer','2026-03-21','completed',NULL,NULL,NULL,NULL,'2026-03-20 22:26:31','2026-03-20 22:26:31'),(3,1,NULL,NULL,200000.00,'savings_deposit','cash','2026-03-21','completed',NULL,NULL,NULL,NULL,'2026-03-20 22:26:31','2026-03-20 22:26:31');
/*!40000 ALTER TABLE `payment_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reward_points`
--

DROP TABLE IF EXISTS `reward_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reward_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `points` int(11) DEFAULT 0,
  `transaction_type` enum('Earned','Redeemed') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_member_points` (`member_id`,`points`),
  KEY `idx_transaction_type` (`transaction_type`),
  KEY `idx_reference` (`reference_type`,`reference_id`),
  KEY `idx_reward_points_member_id` (`member_id`),
  KEY `idx_reward_points_points` (`points`),
  KEY `idx_reward_points_created_at` (`created_at`),
  CONSTRAINT `reward_points_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reward_points`
--

LOCK TABLES `reward_points` WRITE;
/*!40000 ALTER TABLE `reward_points` DISABLE KEYS */;
/*!40000 ALTER TABLE `reward_points` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `risk_scores`
--

DROP TABLE IF EXISTS `risk_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `risk_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `loan_id` int(11) DEFAULT NULL,
  `score` decimal(5,2) NOT NULL,
  `risk_level` enum('low','medium','high') NOT NULL,
  `factors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`factors`)),
  `calculated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `loan_id` (`loan_id`),
  CONSTRAINT `risk_scores_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `risk_scores_ibfk_2` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `risk_scores`
--

LOCK TABLES `risk_scores` WRITE;
/*!40000 ALTER TABLE `risk_scores` DISABLE KEYS */;
/*!40000 ALTER TABLE `risk_scores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(50) NOT NULL,
  `permission` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_permission` (`role`,`permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_settings`
--

DROP TABLE IF EXISTS `role_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(50) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_setting` (`role`,`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_settings`
--

LOCK TABLES `role_settings` WRITE;
/*!40000 ALTER TABLE `role_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_settings` ENABLE KEYS */;
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
  KEY `idx_savings_member_id` (`member_id`),
  KEY `idx_savings_type` (`type`),
  KEY `idx_savings_status` (`status`),
  KEY `idx_savings_interest_rate` (`interest_rate`),
  KEY `idx_savings_created_at` (`created_at`),
  KEY `idx_savings_updated_at` (`updated_at`),
  KEY `idx_savings_member_status` (`member_id`,`status`),
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
-- Table structure for table `security_events`
--

DROP TABLE IF EXISTS `security_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(50) NOT NULL,
  `severity` enum('low','medium','high','critical') NOT NULL,
  `description` text DEFAULT NULL,
  `source_ip` varchar(45) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `security_events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_events`
--

LOCK TABLES `security_events` WRITE;
/*!40000 ALTER TABLE `security_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `security_events` ENABLE KEYS */;
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
-- Table structure for table `staff_balances`
--

DROP TABLE IF EXISTS `staff_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `available_balance` decimal(15,2) DEFAULT 0.00,
  `total_received` decimal(15,2) DEFAULT 0.00,
  `total_disbursed` decimal(15,2) DEFAULT 0.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_staff_balance` (`staff_id`),
  CONSTRAINT `staff_balances_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_balances`
--

LOCK TABLES `staff_balances` WRITE;
/*!40000 ALTER TABLE `staff_balances` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_setting_key` (`setting_key`),
  KEY `idx_public` (`is_public`),
  KEY `updated_by` (`updated_by`),
  KEY `idx_system_settings_is_public` (`is_public`),
  KEY `idx_system_settings_created_at` (`created_at`),
  KEY `idx_system_settings_updated_at` (`updated_at`),
  CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'app_name','KSP Lam Gabe Jaya','string','Application name',0,NULL,'2026-03-21 20:36:05','2026-03-21 20:36:05'),(2,'app_version','4.0','string','Application version',0,NULL,'2026-03-21 20:36:05','2026-03-21 20:36:05'),(3,'max_loan_amount','50000000','number','Maximum loan amount',0,NULL,'2026-03-21 20:36:05','2026-03-21 20:36:05'),(4,'default_interest_rate','12','number','Default interest rate (%)',0,NULL,'2026-03-21 20:36:05','2026-03-21 20:36:05'),(5,'min_savings_amount','100000','number','Minimum savings amount',0,NULL,'2026-03-21 20:36:05','2026-03-21 20:36:05'),(6,'reward_points_enabled','true','boolean','Enable reward points system',0,NULL,'2026-03-21 20:36:05','2026-03-21 20:36:05'),(7,'gps_tracking_enabled','true','boolean','Enable GPS tracking',0,NULL,'2026-03-21 20:36:05','2026-03-21 20:36:05'),(8,'notification_enabled','true','boolean','Enable notifications',0,NULL,'2026-03-21 20:36:05','2026-03-21 20:36:05');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `password_hash` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `role` enum('admin','staff','member','creator','owner','super_admin','manager','teller') DEFAULT 'member',
  `status` enum('Active','Inactive','Suspended') DEFAULT 'Active',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `name` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `status` (`status`),
  KEY `idx_person_id` (`person_id`),
  KEY `idx_users_username` (`username`),
  KEY `idx_users_email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_is_active` (`is_active`),
  KEY `idx_users_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'admin','admin@ksp.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'System Administrator','08123456789','admin','Active',1,NULL,'2026-03-20 19:46:10','2026-03-21 16:35:32','Administrator'),(2,NULL,'staff','staff@ksp.com','$2y$10$a/Zh3rbr4pq8pm1jeyFcSeq572U3L3BxbiUvMX140EEsKcKl2mLEm',NULL,'Staff User','08123456790','staff','Active',1,NULL,'2026-03-20 19:46:10','2026-03-20 21:07:33','Staff Member'),(3,NULL,'member','member@ksp.com','$2y$10$6KgBPRrPFKaQacQPTfF3j.e/KSXMl2jPlJM35epLmKw4ihk1E4X5K',NULL,'Member User','08123456791','member','Active',1,NULL,'2026-03-20 19:46:10','2026-03-20 21:07:33','Member User'),(7,NULL,'creator','creator@ksp-lamgabejaya.com','$argon2id$v=19$m=65536,t=4,p=1$RlVBQzlwT056R1c5dUZIRw$yIRvEBc1ZWI5RWc49r/CVtU7yYKjuvihsKQ7U2VM9ik',NULL,'Application Creator',NULL,'creator','Active',1,NULL,'2026-03-21 21:11:15','2026-03-21 21:11:15',''),(8,NULL,'owner','owner@ksp-lamgabejaya.com','$argon2id$v=19$m=65536,t=4,p=1$QTdSQy9temhoVVNMRUpVZA$tEhJSlpFrZML+yUFJdm6h/hxui3Wlkw0M/mSRRE/SdY',NULL,'Business Owner',NULL,'owner','Active',1,NULL,'2026-03-21 21:24:48','2026-03-21 21:24:48',''),(9,NULL,'superadmin','superadmin@ksp-lamgabejaya.com','$argon2id$v=19$m=65536,t=4,p=1$SmxtdTVvb1NFZlB0TU9MYg$5B+KkfAnLndMOjTZfBAKwM9W/x8Gk0re2oOzmLq2vrE',NULL,'Super Administrator',NULL,'super_admin','Active',1,NULL,'2026-03-21 21:24:48','2026-03-21 21:24:48',''),(10,NULL,'manager','manager@ksp-lamgabejaya.com','$argon2id$v=19$m=65536,t=4,p=1$WGVvd3BEUTltRjFuODBrSw$KitRU256s5YevPeOVYo5VchmS3CC9jj06uFNmsOWxkc',NULL,'Operations Manager',NULL,'manager','Active',1,NULL,'2026-03-21 21:24:48','2026-03-21 21:24:48',''),(11,NULL,'teller','teller@ksp-lamgabejaya.com','$argon2id$v=19$m=65536,t=4,p=1$NjV1c3VxSnBMZno5QUNFag$9BlyRDGuH8uF4P4as/5KEUWLvkJsUi3XvuLMIbJxsso',NULL,'Teller Staff',NULL,'teller','Active',1,NULL,'2026-03-21 21:24:49','2026-03-21 21:24:49','');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-22  4:29:26
