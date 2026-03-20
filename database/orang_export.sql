-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: orang
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
-- Dumping data for table `person_addresses`
--

LOCK TABLES `person_addresses` WRITE;
/*!40000 ALTER TABLE `person_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `person_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `person_contacts`
--

LOCK TABLES `person_contacts` WRITE;
/*!40000 ALTER TABLE `person_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `person_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `person_documents`
--

LOCK TABLES `person_documents` WRITE;
/*!40000 ALTER TABLE `person_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `person_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `person_relations`
--

LOCK TABLES `person_relations` WRITE;
/*!40000 ALTER TABLE `person_relations` DISABLE KEYS */;
/*!40000 ALTER TABLE `person_relations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `persons`
--

LOCK TABLES `persons` WRITE;
/*!40000 ALTER TABLE `persons` DISABLE KEYS */;
INSERT INTO `persons` (`id`, `nik`, `paspor`, `first_name`, `last_name`, `gender`, `birth_date`, `birth_place`, `phone`, `email`, `photo_url`, `is_active`, `notes`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (1,NULL,NULL,'John','Doe','male','1990-01-01','Jakarta','08123456789','john.doe@example.com',NULL,1,NULL,'2026-03-20 21:42:59','2026-03-20 21:42:59',NULL,NULL);
/*!40000 ALTER TABLE `persons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super_admin','Super Administrator','Akses penuh ke seluruh sistem',9,1,1,'2026-03-20 21:40:16','2026-03-20 21:40:16'),(2,'admin','Administrator','Administrator sistem',8,1,1,'2026-03-20 21:40:16','2026-03-20 21:40:16'),(3,'manager','Manager','Manager operasional',7,1,1,'2026-03-20 21:40:16','2026-03-20 21:40:16'),(4,'supervisor','Supervisor','Supervisor tim',6,1,1,'2026-03-20 21:40:16','2026-03-20 21:40:16'),(5,'staff','Staff','Staf administrasi',5,1,1,'2026-03-20 21:40:16','2026-03-20 21:40:16'),(6,'sales','Sales','Tim penjualan',4,1,1,'2026-03-20 21:40:16','2026-03-20 21:40:16'),(7,'warehouse','Warehouse Staff','Staf gudang',3,1,1,'2026-03-20 21:40:16','2026-03-20 21:40:16'),(8,'customer','Customer','Pelanggan',2,1,1,'2026-03-20 21:40:16','2026-03-20 21:40:16'),(9,'supplier','Supplier','Pemasok',2,1,1,'2026-03-20 21:40:16','2026-03-20 21:40:16'),(10,'guest','Guest','Tamu dengan akses terbatas',1,1,1,'2026-03-20 21:40:16','2026-03-20 21:40:16');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `user_permissions`
--

LOCK TABLES `user_permissions` WRITE;
/*!40000 ALTER TABLE `user_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `user_sessions`
--

LOCK TABLES `user_sessions` WRITE;
/*!40000 ALTER TABLE `user_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
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

-- Dump completed on 2026-03-21  5:13:32
