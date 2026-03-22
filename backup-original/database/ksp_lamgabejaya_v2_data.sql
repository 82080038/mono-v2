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
-- Dumping data for table `fund_requests`
--

LOCK TABLES `fund_requests` WRITE;
/*!40000 ALTER TABLE `fund_requests` DISABLE KEYS */;
INSERT INTO `fund_requests` VALUES (1,1,5000000.00,'circular_fund','Modal usaha','approved',NULL,NULL,NULL,'2026-03-20 22:26:45','2026-03-20 22:26:45'),(2,2,3000000.00,'circular_fund','Kebutuhan rumah','approved',NULL,NULL,NULL,'2026-03-20 22:26:45','2026-03-20 22:26:45'),(3,3,10000000.00,'emergency_fund','Darurat medis','pending',NULL,NULL,NULL,'2026-03-20 22:26:45','2026-03-20 22:26:45');
/*!40000 ALTER TABLE `fund_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gps_tracking`
--

LOCK TABLES `gps_tracking` WRITE;
/*!40000 ALTER TABLE `gps_tracking` DISABLE KEYS */;
INSERT INTO `gps_tracking` VALUES (1,2,-6.20880000,106.84560000,'Jakarta Pusat','member_visit','Collection visit','active','2026-03-20 22:26:38','2026-03-20 22:26:38'),(2,2,-6.17510000,106.86500000,'Jakarta Utara','member_visit','Loan follow-up','active','2026-03-20 22:26:38','2026-03-20 22:26:38'),(3,2,-6.22970000,106.82950000,'Jakarta Barat','collection','Payment collection','active','2026-03-20 22:26:38','2026-03-20 22:26:38');
/*!40000 ALTER TABLE `gps_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `guarantee_payment_tracking`
--

LOCK TABLES `guarantee_payment_tracking` WRITE;
/*!40000 ALTER TABLE `guarantee_payment_tracking` DISABLE KEYS */;
INSERT INTO `guarantee_payment_tracking` VALUES (1,1,500000.00,'2024-01-15','principal','paid','Monthly payment','2026-03-20 22:18:47','2026-03-20 22:18:47'),(2,2,300000.00,'2024-01-15','principal','paid','Monthly payment','2026-03-20 22:18:47','2026-03-20 22:18:47'),(3,3,1000000.00,'2024-01-20','principal','pending','Upcoming payment','2026-03-20 22:18:47','2026-03-20 22:18:47');
/*!40000 ALTER TABLE `guarantee_payment_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `guarantee_relationships`
--

LOCK TABLES `guarantee_relationships` WRITE;
/*!40000 ALTER TABLE `guarantee_relationships` DISABLE KEYS */;
INSERT INTO `guarantee_relationships` VALUES (1,1,2,'family','strong','Active','2026-03-20 22:18:39','2026-03-20 22:18:39'),(2,2,3,'friend','moderate','Active','2026-03-20 22:18:39','2026-03-20 22:18:39'),(3,3,1,'business','strong','Active','2026-03-20 22:18:39','2026-03-20 22:18:39');
/*!40000 ALTER TABLE `guarantee_relationships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `guarantee_risk_assessments`
--

LOCK TABLES `guarantee_risk_assessments` WRITE;
/*!40000 ALTER TABLE `guarantee_risk_assessments` DISABLE KEYS */;
INSERT INTO `guarantee_risk_assessments` VALUES (1,1,NULL,'low',25.50,'Good credit history, stable income','Completed','2026-03-20 22:18:35','2026-03-20 22:18:35'),(2,2,NULL,'medium',55.75,'Moderate risk, needs monitoring','Completed','2026-03-20 22:18:35','2026-03-20 22:18:35'),(3,3,NULL,'high',78.25,'High risk due to large amount','Active','2026-03-20 22:18:35','2026-03-20 22:18:35');
/*!40000 ALTER TABLE `guarantee_risk_assessments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `loan_guarantees`
--

LOCK TABLES `loan_guarantees` WRITE;
/*!40000 ALTER TABLE `loan_guarantees` DISABLE KEYS */;
INSERT INTO `loan_guarantees` VALUES (1,'GR001',1,2,5000000.00,'personal','Guarantee for John Doe loan','Active','2026-03-20 22:18:29','2026-03-20 22:18:29'),(2,'GR002',2,3,3000000.00,'personal','Guarantee for Jane Smith loan','Active','2026-03-20 22:18:29','2026-03-20 22:18:29'),(3,'GR003',3,1,10000000.00,'corporate','Guarantee for Budi Santoso loan','Pending','2026-03-20 22:18:29','2026-03-20 22:18:29');
/*!40000 ALTER TABLE `loan_guarantees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `loans`
--

LOCK TABLES `loans` WRITE;
/*!40000 ALTER TABLE `loans` DISABLE KEYS */;
INSERT INTO `loans` VALUES (1,1,5000000.00,12.00,12,'Modal Usaha','Active',NULL,'2026-03-20 22:16:49','2026-03-20 22:16:49'),(2,2,3000000.00,10.00,6,'Kebutuhan Rumah','Active',NULL,'2026-03-20 22:16:49','2026-03-20 22:16:49'),(3,3,10000000.00,15.00,24,'Ekspansi Bisnis','Pending',NULL,'2026-03-20 22:16:49','2026-03-20 22:16:49');
/*!40000 ALTER TABLE `loans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `members`
--

LOCK TABLES `members` WRITE;
/*!40000 ALTER TABLE `members` DISABLE KEYS */;
INSERT INTO `members` VALUES (1,'John Doe','john@example.com','08123456789',NULL,'Active',NULL,'2026-03-20 22:16:33','2026-03-20 22:16:33'),(2,'Jane Smith','jane@example.com','08198765432',NULL,'Active',NULL,'2026-03-20 22:16:33','2026-03-20 22:16:33'),(3,'Budi Santoso','budi@example.com','08234567890',NULL,'Active',NULL,'2026-03-20 22:16:33','2026-03-20 22:16:33');
/*!40000 ALTER TABLE `members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `migration_batches`
--

LOCK TABLES `migration_batches` WRITE;
/*!40000 ALTER TABLE `migration_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `migration_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `migration_logs`
--

LOCK TABLES `migration_logs` WRITE;
/*!40000 ALTER TABLE `migration_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `migration_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `migration_templates`
--

LOCK TABLES `migration_templates` WRITE;
/*!40000 ALTER TABLE `migration_templates` DISABLE KEYS */;
INSERT INTO `migration_templates` VALUES (1,'Member Import Template','members','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":false,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"address\",\"required\":true,\"type\":\"string\"},{\"name\":\"nik\",\"required\":false,\"type\":\"string\"},{\"name\":\"birth_date\",\"required\":false,\"type\":\"date\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"}]}','2026-03-21 05:24:20'),(2,'Loan Import Template','loans','{\"columns\":[{\"name\":\"member_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"interest_rate\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"loan_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"due_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"purpose\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:24:20'),(3,'Payment Import Template','payments','{\"columns\":[{\"name\":\"loan_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"payment_amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"payment_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"payment_method\",\"required\":false,\"type\":\"string\"},{\"name\":\"notes\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:24:20'),(4,'Staff Import Template','staff','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":true,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"position\",\"required\":true,\"type\":\"string\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"salary\",\"required\":false,\"type\":\"decimal\"}]}','2026-03-21 05:24:20'),(5,'Member Import Template','members','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":false,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"address\",\"required\":true,\"type\":\"string\"},{\"name\":\"nik\",\"required\":false,\"type\":\"string\"},{\"name\":\"birth_date\",\"required\":false,\"type\":\"date\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"}]}','2026-03-21 05:33:15'),(6,'Loan Import Template','loans','{\"columns\":[{\"name\":\"member_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"interest_rate\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"loan_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"due_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"purpose\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:33:15'),(7,'Payment Import Template','payments','{\"columns\":[{\"name\":\"loan_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"payment_amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"payment_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"payment_method\",\"required\":false,\"type\":\"string\"},{\"name\":\"notes\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:33:15'),(8,'Staff Import Template','staff','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":true,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"position\",\"required\":true,\"type\":\"string\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"salary\",\"required\":false,\"type\":\"decimal\"}]}','2026-03-21 05:33:15'),(9,'Member Import Template','members','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":false,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"address\",\"required\":true,\"type\":\"string\"},{\"name\":\"nik\",\"required\":false,\"type\":\"string\"},{\"name\":\"birth_date\",\"required\":false,\"type\":\"date\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"}]}','2026-03-21 05:35:17'),(10,'Loan Import Template','loans','{\"columns\":[{\"name\":\"member_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"interest_rate\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"loan_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"due_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"purpose\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:35:17'),(11,'Payment Import Template','payments','{\"columns\":[{\"name\":\"loan_id\",\"required\":true,\"type\":\"integer\"},{\"name\":\"payment_amount\",\"required\":true,\"type\":\"decimal\"},{\"name\":\"payment_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"payment_method\",\"required\":false,\"type\":\"string\"},{\"name\":\"notes\",\"required\":false,\"type\":\"string\"}]}','2026-03-21 05:35:17'),(12,'Staff Import Template','staff','{\"columns\":[{\"name\":\"name\",\"required\":true,\"type\":\"string\"},{\"name\":\"email\",\"required\":true,\"type\":\"email\"},{\"name\":\"phone\",\"required\":true,\"type\":\"phone\"},{\"name\":\"position\",\"required\":true,\"type\":\"string\"},{\"name\":\"join_date\",\"required\":true,\"type\":\"date\"},{\"name\":\"salary\",\"required\":false,\"type\":\"decimal\"}]}','2026-03-21 05:35:17');
/*!40000 ALTER TABLE `migration_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payment_transactions`
--

LOCK TABLES `payment_transactions` WRITE;
/*!40000 ALTER TABLE `payment_transactions` DISABLE KEYS */;
INSERT INTO `payment_transactions` VALUES (1,1,1,500000.00,'loan_payment','cash','2026-03-21','completed',NULL,'2026-03-20 22:26:31','2026-03-20 22:26:31'),(2,2,2,300000.00,'loan_payment','bank_transfer','2026-03-21','completed',NULL,'2026-03-20 22:26:31','2026-03-20 22:26:31'),(3,1,NULL,200000.00,'savings_deposit','cash','2026-03-21','completed',NULL,'2026-03-20 22:26:31','2026-03-20 22:26:31');
/*!40000 ALTER TABLE `payment_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `savings`
--

LOCK TABLES `savings` WRITE;
/*!40000 ALTER TABLE `savings` DISABLE KEYS */;
INSERT INTO `savings` VALUES (1,1,2000000.00,'Regular',5.00,'Active','2026-03-20 22:16:56','2026-03-20 22:16:56'),(2,2,1500000.00,'Fixed',7.00,'Active','2026-03-20 22:16:56','2026-03-20 22:16:56'),(3,3,5000000.00,'Special',8.00,'Active','2026-03-20 22:16:56','2026-03-20 22:16:56');
/*!40000 ALTER TABLE `savings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'cooperative_name','KSP Lam Gabe Jaya','Nama Koperasi','string','general',0,'2026-03-20 22:17:20','2026-03-20 22:17:20'),(2,'interest_rate','12.00','Suku Bunga Default','string','general',0,'2026-03-20 22:17:20','2026-03-20 22:17:20'),(3,'max_loan_amount','50000000','Maksimal Pinjaman','string','general',0,'2026-03-20 22:17:20','2026-03-20 22:17:20'),(4,'min_savings_amount','100000','Minimal Simpanan','string','general',0,'2026-03-20 22:17:20','2026-03-20 22:17:20');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (1,'Admin','System Administrator','[\"all\"]','Active','2026-03-20 19:46:10','2026-03-20 19:46:10'),(2,'Staff','Staff User','[\"members\",\"loans\",\"savings\",\"gps\"]','Active','2026-03-20 19:46:10','2026-03-20 19:46:10'),(3,'Member','Member User','[\"view_own_data\",\"apply_loan\",\"view_savings\"]','Active','2026-03-20 19:46:10','2026-03-20 19:46:10');
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'admin','admin@ksp.com','$2y$10$RWCUAv.s7A9Le7pFHQ5ZR.YShnoZWHN8h54MirOTS8.sV34E015Ly','System Administrator','08123456789','admin','Active',NULL,'2026-03-20 19:46:10','2026-03-20 21:07:33','Administrator'),(2,NULL,'staff','staff@ksp.com','$2y$10$a/Zh3rbr4pq8pm1jeyFcSeq572U3L3BxbiUvMX140EEsKcKl2mLEm','Staff User','08123456790','staff','Active',NULL,'2026-03-20 19:46:10','2026-03-20 21:07:33','Staff Member'),(3,NULL,'member','member@ksp.com','$2y$10$6KgBPRrPFKaQacQPTfF3j.e/KSXMl2jPlJM35epLmKw4ihk1E4X5K','Member User','08123456791','member','Active',NULL,'2026-03-20 19:46:10','2026-03-20 21:07:33','Member User');
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

-- Dump completed on 2026-03-21  5:40:13
