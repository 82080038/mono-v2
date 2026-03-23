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
-- Table structure for table `person_addresses`
--

DROP TABLE IF EXISTS `person_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `person_addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint(20) unsigned NOT NULL,
  `address_type` enum('home','office','billing','shipping','warehouse','other') NOT NULL,
  `village_id` int(10) unsigned DEFAULT NULL COMMENT 'Link ke alamat_db.villages',
  `district_id` int(10) unsigned DEFAULT NULL COMMENT 'Link ke alamat_db.districts',
  `regency_id` int(10) unsigned DEFAULT NULL COMMENT 'Link ke alamat_db.regencies',
  `province_id` int(10) unsigned DEFAULT NULL COMMENT 'Link ke alamat_db.provinces',
  `postal_code` varchar(10) DEFAULT NULL COMMENT 'Kode pos',
  `address_line` varchar(255) NOT NULL COMMENT 'Alamat lengkap',
  `address_line2` varchar(255) DEFAULT NULL COMMENT 'Alamat tambahan',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'Koordinat latitude',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'Koordinat longitude',
  `is_primary` tinyint(1) DEFAULT 0 COMMENT 'Alamat utama',
  `is_active` tinyint(1) DEFAULT 1,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_person_id` (`person_id`),
  KEY `idx_address_type` (`address_type`),
  KEY `idx_village_id` (`village_id`),
  KEY `idx_district_id` (`district_id`),
  KEY `idx_regency_id` (`regency_id`),
  KEY `idx_province_id` (`province_id`),
  KEY `idx_postal_code` (`postal_code`),
  KEY `idx_is_primary` (`is_primary`),
  CONSTRAINT `person_addresses_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Alamat-alamat person';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `person_contacts`
--

DROP TABLE IF EXISTS `person_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `person_contacts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint(20) unsigned NOT NULL,
  `contact_type` enum('phone','email','whatsapp','telegram','social_media','other') NOT NULL,
  `contact_value` varchar(255) NOT NULL COMMENT 'Nilai kontak',
  `is_primary` tinyint(1) DEFAULT 0 COMMENT 'Kontak utama',
  `is_active` tinyint(1) DEFAULT 1,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_person_id` (`person_id`),
  KEY `idx_contact_type` (`contact_type`),
  KEY `idx_contact_value` (`contact_value`),
  KEY `idx_is_primary` (`is_primary`),
  CONSTRAINT `person_contacts_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kontak multiple untuk setiap person';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `person_documents`
--

DROP TABLE IF EXISTS `person_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `person_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint(20) unsigned NOT NULL,
  `document_type` enum('ktp','npwp','sim','passport','bpjs','bank_account','certificate','other') NOT NULL,
  `document_number` varchar(50) DEFAULT NULL COMMENT 'Nomor dokumen',
  `document_name` varchar(255) NOT NULL COMMENT 'Nama dokumen',
  `file_url` varchar(500) DEFAULT NULL COMMENT 'URL file dokumen',
  `file_path` varchar(500) DEFAULT NULL COMMENT 'Path file di server',
  `file_size` bigint(20) DEFAULT NULL COMMENT 'Ukuran file dalam bytes',
  `mime_type` varchar(100) DEFAULT NULL COMMENT 'MIME type file',
  `issued_date` date DEFAULT NULL COMMENT 'Tanggal terbit',
  `expired_date` date DEFAULT NULL COMMENT 'Tanggal kadaluarsa',
  `issuing_authority` varchar(255) DEFAULT NULL COMMENT 'Penerbit',
  `is_verified` tinyint(1) DEFAULT 0 COMMENT 'Status verifikasi',
  `verification_notes` text DEFAULT NULL COMMENT 'Catatan verifikasi',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_person_id` (`person_id`),
  KEY `idx_document_type` (`document_type`),
  KEY `idx_document_number` (`document_number`),
  KEY `idx_expired_date` (`expired_date`),
  KEY `idx_is_verified` (`is_verified`),
  CONSTRAINT `person_documents_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_documents_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dokumen-dokumen pribadi';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `person_relations`
--

DROP TABLE IF EXISTS `person_relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `person_relations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person1_id` bigint(20) unsigned NOT NULL COMMENT 'Person pertama',
  `person2_id` bigint(20) unsigned NOT NULL COMMENT 'Person kedua',
  `relation_type` enum('family','spouse','parent','child','sibling','friend','colleague','business_partner','other') NOT NULL,
  `relation_description` varchar(255) DEFAULT NULL COMMENT 'Deskripsi hubungan',
  `is_emergency_contact` tinyint(1) DEFAULT 0 COMMENT 'Kontak darurat',
  `is_active` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_relation` (`person1_id`,`person2_id`,`relation_type`),
  KEY `created_by` (`created_by`),
  KEY `idx_person1_id` (`person1_id`),
  KEY `idx_person2_id` (`person2_id`),
  KEY `idx_relation_type` (`relation_type`),
  KEY `idx_is_emergency_contact` (`is_emergency_contact`),
  CONSTRAINT `person_relations_ibfk_1` FOREIGN KEY (`person1_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_relations_ibfk_2` FOREIGN KEY (`person2_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_relations_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hubungan antar persons';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `persons`
--

DROP TABLE IF EXISTS `persons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `persons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nik` varchar(16) DEFAULT NULL COMMENT 'Nomor Induk Kependudukan',
  `paspor` varchar(20) DEFAULT NULL COMMENT 'Nomor Paspor',
  `first_name` varchar(100) NOT NULL COMMENT 'Nama depan',
  `last_name` varchar(100) DEFAULT NULL COMMENT 'Nama belakang',
  `full_name` varchar(255) GENERATED ALWAYS AS (concat(ifnull(`first_name`,''),' ',ifnull(`last_name`,''))) STORED,
  `gender` enum('male','female','other') DEFAULT NULL,
  `birth_date` date DEFAULT NULL COMMENT 'Tanggal lahir',
  `birth_place` varchar(100) DEFAULT NULL COMMENT 'Tempat lahir',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Nomor telepon utama',
  `email` varchar(255) DEFAULT NULL COMMENT 'Email utama',
  `photo_url` varchar(500) DEFAULT NULL COMMENT 'URL foto profil',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Status aktif',
  `notes` text DEFAULT NULL COMMENT 'Catatan tambahan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nik` (`nik`),
  UNIQUE KEY `paspor` (`paspor`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_nik` (`nik`),
  KEY `idx_full_name` (`full_name`),
  KEY `idx_phone` (`phone`),
  KEY `idx_email` (`email`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master data pribadi lengkap';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'Nama role',
  `display_name` varchar(100) NOT NULL COMMENT 'Nama tampilan',
  `description` text DEFAULT NULL COMMENT 'Deskripsi role',
  `level` tinyint(4) DEFAULT 0 COMMENT 'Level hierarki (0=lowest, 9=highest)',
  `is_system` tinyint(1) DEFAULT 0 COMMENT 'Role sistem (tidak bisa dihapus)',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Status aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_name` (`name`),
  KEY `idx_level` (`level`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Peran/role pengguna';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_permissions`
--

DROP TABLE IF EXISTS `user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `permission_name` varchar(100) NOT NULL COMMENT 'Nama permission',
  `resource` varchar(100) NOT NULL COMMENT 'Resource yang diakses',
  `action` enum('create','read','update','delete','approve','export','import') NOT NULL,
  `granted_by` bigint(20) unsigned DEFAULT NULL COMMENT 'Siapa yang memberikan',
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL COMMENT 'Kadaluarsa permission',
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_permission` (`user_id`,`permission_name`,`resource`,`action`),
  KEY `granted_by` (`granted_by`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_permission_name` (`permission_name`),
  KEY `idx_resource` (`resource`),
  KEY `idx_action` (`action`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_ibfk_2` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permission spesifik per user';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `assigned_by` bigint(20) unsigned DEFAULT NULL COMMENT 'Siapa yang assign',
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL COMMENT 'Kadaluarsa role',
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_role` (`user_id`,`role_id`),
  KEY `assigned_by` (`assigned_by`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hubungan user dengan role';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `session_id` varchar(255) NOT NULL COMMENT 'Session ID',
  `ip_address` varchar(45) NOT NULL COMMENT 'IP address',
  `user_agent` text DEFAULT NULL COMMENT 'User agent browser',
  `device_type` enum('desktop','mobile','tablet','other') DEFAULT 'desktop',
  `login_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `logout_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu logout',
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_last_activity` (`last_activity`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tracking sesi user';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint(20) unsigned NOT NULL COMMENT 'Link ke persons',
  `username` varchar(50) NOT NULL COMMENT 'Username login',
  `password_hash` varchar(255) NOT NULL COMMENT 'Hash password',
  `email` varchar(255) NOT NULL COMMENT 'Email login',
  `last_login` timestamp NULL DEFAULT NULL COMMENT 'Terakhir login',
  `login_attempts` tinyint(4) DEFAULT 0 COMMENT 'Jumlah percobaan login gagal',
  `locked_until` timestamp NULL DEFAULT NULL COMMENT 'Dikunci sampai',
  `password_changed_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Password terakhir diubah',
  `must_change_password` tinyint(1) DEFAULT 0 COMMENT 'Wajib ganti password',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Status aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `person_id` (`person_id`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_last_login` (`last_login`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Akun pengguna sistem';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary view structure for view `v_persons_main_address`
--

DROP TABLE IF EXISTS `v_persons_main_address`;
/*!50001 DROP VIEW IF EXISTS `v_persons_main_address`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_persons_main_address` AS SELECT 
 1 AS `id`,
 1 AS `nik`,
 1 AS `paspor`,
 1 AS `first_name`,
 1 AS `last_name`,
 1 AS `full_name`,
 1 AS `gender`,
 1 AS `birth_date`,
 1 AS `birth_place`,
 1 AS `phone`,
 1 AS `email`,
 1 AS `photo_url`,
 1 AS `is_active`,
 1 AS `notes`,
 1 AS `created_at`,
 1 AS `updated_at`,
 1 AS `created_by`,
 1 AS `updated_by`,
 1 AS `address_type`,
 1 AS `address_line`,
 1 AS `postal_code`,
 1 AS `village_name`,
 1 AS `district_name`,
 1 AS `regency_name`,
 1 AS `province_name`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_users_complete`
--

DROP TABLE IF EXISTS `v_users_complete`;
/*!50001 DROP VIEW IF EXISTS `v_users_complete`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_users_complete` AS SELECT 
 1 AS `user_id`,
 1 AS `username`,
 1 AS `email`,
 1 AS `last_login`,
 1 AS `user_active`,
 1 AS `person_id`,
 1 AS `nik`,
 1 AS `full_name`,
 1 AS `phone`,
 1 AS `gender`,
 1 AS `birth_date`,
 1 AS `roles`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `v_persons_main_address`
--

/*!50001 DROP VIEW IF EXISTS `v_persons_main_address`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_persons_main_address` AS select `p`.`id` AS `id`,`p`.`nik` AS `nik`,`p`.`paspor` AS `paspor`,`p`.`first_name` AS `first_name`,`p`.`last_name` AS `last_name`,`p`.`full_name` AS `full_name`,`p`.`gender` AS `gender`,`p`.`birth_date` AS `birth_date`,`p`.`birth_place` AS `birth_place`,`p`.`phone` AS `phone`,`p`.`email` AS `email`,`p`.`photo_url` AS `photo_url`,`p`.`is_active` AS `is_active`,`p`.`notes` AS `notes`,`p`.`created_at` AS `created_at`,`p`.`updated_at` AS `updated_at`,`p`.`created_by` AS `created_by`,`p`.`updated_by` AS `updated_by`,`pa`.`address_type` AS `address_type`,`pa`.`address_line` AS `address_line`,`pa`.`postal_code` AS `postal_code`,`v`.`name` AS `village_name`,`d`.`name` AS `district_name`,`reg`.`name` AS `regency_name`,`prov`.`name` AS `province_name` from (((((`orang`.`persons` `p` left join `orang`.`person_addresses` `pa` on(`p`.`id` = `pa`.`person_id` and `pa`.`is_primary` = 1 and `pa`.`is_active` = 1)) left join `alamat_db`.`villages` `v` on(`pa`.`village_id` = `v`.`id`)) left join `alamat_db`.`districts` `d` on(`pa`.`district_id` = `d`.`id`)) left join `alamat_db`.`regencies` `reg` on(`pa`.`regency_id` = `reg`.`id`)) left join `alamat_db`.`provinces` `prov` on(`pa`.`province_id` = `prov`.`id`)) where `p`.`is_active` = 1 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_users_complete`
--

/*!50001 DROP VIEW IF EXISTS `v_users_complete`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_users_complete` AS select `u`.`id` AS `user_id`,`u`.`username` AS `username`,`u`.`email` AS `email`,`u`.`last_login` AS `last_login`,`u`.`is_active` AS `user_active`,`p`.`id` AS `person_id`,`p`.`nik` AS `nik`,`p`.`full_name` AS `full_name`,`p`.`phone` AS `phone`,`p`.`gender` AS `gender`,`p`.`birth_date` AS `birth_date`,group_concat(`r`.`display_name` separator ',') AS `roles` from (((`users` `u` join `persons` `p` on(`u`.`person_id` = `p`.`id`)) left join `user_roles` `ur` on(`u`.`id` = `ur`.`user_id`)) left join `roles` `r` on(`ur`.`role_id` = `r`.`id` and `ur`.`is_active` = 1)) where `u`.`is_active` = 1 group by `u`.`id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-21  5:13:46
