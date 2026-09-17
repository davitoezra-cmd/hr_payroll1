/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.8-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: hr_pay
-- ------------------------------------------------------
-- Server version	8.0.46

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `attendance_qrs`
--

DROP TABLE IF EXISTS `attendance_qrs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_qrs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `expired_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendance_qrs_token_unique` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_qrs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `attendance_qrs` WRITE;
/*!40000 ALTER TABLE `attendance_qrs` DISABLE KEYS */;
INSERT INTO `attendance_qrs` VALUES
(1,'Kantor Pusat','4e44b6eb-bddb-4cc9-a5db-b8116aa68d05',NULL,1,NULL,'2026-08-12 10:08:44','2026-08-12 10:08:44');
/*!40000 ALTER TABLE `attendance_qrs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `attendances`
--

DROP TABLE IF EXISTS `attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `attendance_date` date NOT NULL,
  `image_selfie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `status` enum('hadir','terlambat') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'hadir',
  `metode` enum('selfie','qr') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'selfie',
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `checkout_latitude` decimal(10,7) DEFAULT NULL,
  `checkout_longitude` decimal(10,7) DEFAULT NULL,
  `check_in_limit` time DEFAULT NULL,
  `bonus_didapat` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendances_employee_id_foreign` (`employee_id`),
  CONSTRAINT `attendances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendances`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `attendances` WRITE;
/*!40000 ALTER TABLE `attendances` DISABLE KEYS */;
INSERT INTO `attendances` VALUES
(1,1,'2026-08-01',NULL,'08:42:00','17:40:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(2,1,'2026-08-02',NULL,'08:16:00','18:48:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(3,1,'2026-08-03',NULL,'07:40:00','19:26:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(4,1,'2026-08-04',NULL,'07:07:00','19:47:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(5,1,'2026-08-05',NULL,'07:28:00','17:12:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(6,1,'2026-08-06',NULL,'08:30:00','17:17:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(7,1,'2026-08-07',NULL,'07:29:00','17:19:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(8,1,'2026-08-08',NULL,'08:35:00','18:40:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(9,1,'2026-08-09',NULL,'07:09:00','19:54:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(10,1,'2026-08-10',NULL,'07:14:00','17:23:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(11,1,'2026-08-11',NULL,'07:23:00','18:46:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(12,1,'2026-08-12',NULL,'07:23:00','17:50:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(13,1,'2026-08-13',NULL,'08:15:00','17:52:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(14,1,'2026-08-14',NULL,'07:15:00','18:45:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(15,1,'2026-08-15',NULL,'08:09:00','18:43:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(16,1,'2026-08-16',NULL,'07:12:00','17:44:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(17,1,'2026-08-17',NULL,'07:47:00','19:52:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(18,1,'2026-08-18',NULL,'07:33:00','17:47:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(19,1,'2026-08-19',NULL,'07:24:00','18:55:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(20,1,'2026-08-20',NULL,'07:22:00','19:58:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(21,1,'2026-08-21',NULL,'08:41:00','19:53:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(22,1,'2026-08-22',NULL,'07:45:00','17:42:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(23,1,'2026-08-23',NULL,'07:54:00','18:05:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(24,1,'2026-08-24',NULL,'07:19:00','17:14:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(25,1,'2026-08-25',NULL,'07:44:00','19:25:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(26,1,'2026-08-26',NULL,'08:28:00','17:28:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(27,1,'2026-08-27',NULL,'07:52:00','19:15:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(28,1,'2026-08-28',NULL,'08:11:00','17:42:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(29,1,'2026-08-29',NULL,'08:16:00','19:14:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(30,1,'2026-08-30',NULL,'08:31:00','18:02:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(31,1,'2026-08-31',NULL,'08:10:00','19:53:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(32,2,'2026-08-01',NULL,'07:41:00','18:02:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(33,2,'2026-08-02',NULL,'07:24:00','19:01:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(34,2,'2026-08-03',NULL,'08:20:00','17:56:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(35,2,'2026-08-04',NULL,'07:16:00','19:32:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(36,2,'2026-08-05',NULL,'08:23:00','17:29:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(37,2,'2026-08-06',NULL,'08:59:00','19:45:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(38,2,'2026-08-07',NULL,'08:35:00','19:04:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(39,2,'2026-08-08',NULL,'07:17:00','17:31:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(40,2,'2026-08-09',NULL,'08:18:00','18:56:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(41,2,'2026-08-10',NULL,'08:53:00','19:38:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(42,2,'2026-08-11',NULL,'08:08:00','17:33:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(43,2,'2026-08-12',NULL,'08:19:00','18:02:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(44,2,'2026-08-13',NULL,'07:25:00','19:28:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(45,2,'2026-08-14',NULL,'08:24:00','19:18:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(46,2,'2026-08-15',NULL,'07:59:00','19:03:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(47,2,'2026-08-16',NULL,'07:17:00','17:14:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(48,2,'2026-08-17',NULL,'08:56:00','19:21:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(49,2,'2026-08-18',NULL,'08:26:00','17:53:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(50,2,'2026-08-19',NULL,'08:30:00','18:39:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(51,2,'2026-08-20',NULL,'08:31:00','18:02:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(52,2,'2026-08-21',NULL,'08:08:00','19:08:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(53,2,'2026-08-22',NULL,'07:28:00','17:55:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(54,2,'2026-08-23',NULL,'07:08:00','18:06:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(55,2,'2026-08-24',NULL,'08:51:00','17:35:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(56,2,'2026-08-25',NULL,'08:20:00','18:02:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(57,2,'2026-08-26',NULL,'08:43:00','19:51:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(58,2,'2026-08-27',NULL,'07:04:00','18:01:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(59,2,'2026-08-28',NULL,'08:04:00','17:28:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(60,2,'2026-08-29',NULL,'08:55:00','18:35:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(61,2,'2026-08-30',NULL,'07:44:00','18:11:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(62,2,'2026-08-31',NULL,'08:51:00','18:21:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(63,3,'2026-08-01',NULL,'07:11:00','19:21:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(64,3,'2026-08-02',NULL,'07:09:00','17:47:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(65,3,'2026-08-03',NULL,'07:02:00','19:46:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(66,3,'2026-08-04',NULL,'08:49:00','18:06:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(67,3,'2026-08-05',NULL,'08:10:00','19:51:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(68,3,'2026-08-06',NULL,'08:29:00','18:17:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(69,3,'2026-08-07',NULL,'07:21:00','19:32:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(70,3,'2026-08-08',NULL,'07:09:00','17:07:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(71,3,'2026-08-09',NULL,'08:29:00','19:24:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(72,3,'2026-08-10',NULL,'08:09:00','17:09:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(73,3,'2026-08-11',NULL,'08:57:00','19:23:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(74,3,'2026-08-12',NULL,'07:06:00','19:26:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(75,3,'2026-08-13',NULL,'08:26:00','17:37:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(76,3,'2026-08-14',NULL,'07:16:00','17:00:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(77,3,'2026-08-15',NULL,'07:11:00','17:55:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(78,3,'2026-08-16',NULL,'07:11:00','18:20:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(79,3,'2026-08-17',NULL,'07:21:00','18:00:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(80,3,'2026-08-18',NULL,'08:14:00','19:03:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(81,3,'2026-08-19',NULL,'08:40:00','17:24:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(82,3,'2026-08-20',NULL,'07:57:00','17:48:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(83,3,'2026-08-21',NULL,'08:44:00','18:47:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(84,3,'2026-08-22',NULL,'08:43:00','19:57:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(85,3,'2026-08-23',NULL,'08:20:00','19:00:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(86,3,'2026-08-24',NULL,'08:41:00','18:54:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(87,3,'2026-08-25',NULL,'08:47:00','19:10:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(88,3,'2026-08-26',NULL,'08:56:00','18:15:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(89,3,'2026-08-27',NULL,'08:28:00','17:45:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(90,3,'2026-08-28',NULL,'07:54:00','18:44:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(91,3,'2026-08-29',NULL,'07:35:00','19:44:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(92,3,'2026-08-30',NULL,'07:24:00','17:12:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(93,3,'2026-08-31',NULL,'07:30:00','19:46:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(94,4,'2026-08-01',NULL,'08:53:00','17:19:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(95,4,'2026-08-02',NULL,'07:36:00','17:12:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(96,4,'2026-08-03',NULL,'08:39:00','18:52:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(97,4,'2026-08-04',NULL,'08:58:00','17:06:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(98,4,'2026-08-05',NULL,'07:39:00','18:35:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(99,4,'2026-08-06',NULL,'07:37:00','19:55:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(100,4,'2026-08-07',NULL,'08:41:00','19:34:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(101,4,'2026-08-08',NULL,'08:27:00','19:45:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(102,4,'2026-08-09',NULL,'08:16:00','17:53:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(103,4,'2026-08-10',NULL,'08:15:00','17:58:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(104,4,'2026-08-11',NULL,'07:50:00','17:33:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(105,4,'2026-08-12',NULL,'08:37:00','17:28:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(106,4,'2026-08-13',NULL,'07:39:00','19:45:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(107,4,'2026-08-14',NULL,'07:00:00','18:08:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(108,4,'2026-08-15',NULL,'08:27:00','17:41:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(109,4,'2026-08-16',NULL,'07:56:00','17:54:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(110,4,'2026-08-17',NULL,'08:29:00','18:24:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(111,4,'2026-08-18',NULL,'08:59:00','17:12:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(112,4,'2026-08-19',NULL,'08:41:00','17:12:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(113,4,'2026-08-20',NULL,'08:46:00','19:28:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(114,4,'2026-08-21',NULL,'08:11:00','19:06:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(115,4,'2026-08-22',NULL,'07:46:00','19:32:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(116,4,'2026-08-23',NULL,'08:15:00','18:47:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(117,4,'2026-08-24',NULL,'07:37:00','19:05:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(118,4,'2026-08-25',NULL,'07:40:00','17:24:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(119,4,'2026-08-26',NULL,'08:03:00','18:36:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(120,4,'2026-08-27',NULL,'07:43:00','17:34:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(121,4,'2026-08-28',NULL,'07:48:00','19:59:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(122,4,'2026-08-29',NULL,'07:05:00','19:50:00','terlambat','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(123,4,'2026-08-30',NULL,'07:13:00','19:25:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(124,4,'2026-08-31',NULL,'07:58:00','17:26:00','hadir','selfie',NULL,NULL,NULL,NULL,NULL,0,'2026-08-12 10:08:45','2026-08-12 10:08:45');
/*!40000 ALTER TABLE `attendances` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `balance_transactions`
--

DROP TABLE IF EXISTS `balance_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `balance_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `payroll_id` bigint unsigned DEFAULT NULL,
  `type` enum('credit','debit') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `balance_transactions_employee_id_foreign` (`employee_id`),
  KEY `balance_transactions_payroll_id_foreign` (`payroll_id`),
  CONSTRAINT `balance_transactions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `balance_transactions_payroll_id_foreign` FOREIGN KEY (`payroll_id`) REFERENCES `payrolls` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `balance_transactions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `balance_transactions` WRITE;
/*!40000 ALTER TABLE `balance_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `balance_transactions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `balance_withdrawals`
--

DROP TABLE IF EXISTS `balance_withdrawals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `balance_withdrawals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `note` text COLLATE utf8mb4_unicode_ci,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `balance_withdrawals_employee_id_foreign` (`employee_id`),
  CONSTRAINT `balance_withdrawals_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `balance_withdrawals`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `balance_withdrawals` WRITE;
/*!40000 ALTER TABLE `balance_withdrawals` DISABLE KEYS */;
/*!40000 ALTER TABLE `balance_withdrawals` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `bpjs_payment_proofs`
--

DROP TABLE IF EXISTS `bpjs_payment_proofs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bpjs_payment_proofs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `finance_id` bigint unsigned DEFAULT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `bpjs_type` enum('kesehatan','ketenagakerjaan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `period` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bpjs_payment_proofs_finance_id_foreign` (`finance_id`),
  KEY `bpjs_payment_proofs_employee_id_foreign` (`employee_id`),
  CONSTRAINT `bpjs_payment_proofs_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bpjs_payment_proofs_finance_id_foreign` FOREIGN KEY (`finance_id`) REFERENCES `finances` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bpjs_payment_proofs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `bpjs_payment_proofs` WRITE;
/*!40000 ALTER TABLE `bpjs_payment_proofs` DISABLE KEYS */;
INSERT INTO `bpjs_payment_proofs` VALUES
(1,1,1,'kesehatan','2026-08','BPJS Kesehatan Agustus 2026','bpjs/bpjs_kesehatan_agustus_2026.pdf','Pembayaran BPJS bulan Agustus.','2026-08-12 10:08:45','2026-08-12 10:08:45'),
(2,1,2,'kesehatan','2026-08','BPJS Kesehatan Agustus 2026','bpjs/bpjs_kesehatan_agustus_2026.pdf','Pembayaran BPJS bulan Agustus.','2026-08-12 10:08:45','2026-08-12 10:08:45'),
(3,1,3,'kesehatan','2026-08','BPJS Kesehatan Agustus 2026','bpjs/bpjs_kesehatan_agustus_2026.pdf','Pembayaran BPJS bulan Agustus.','2026-08-12 10:08:45','2026-08-12 10:08:45'),
(4,1,4,'kesehatan','2026-08','BPJS Kesehatan Agustus 2026','bpjs/bpjs_kesehatan_agustus_2026.pdf','Pembayaran BPJS bulan Agustus.','2026-08-12 10:08:45','2026-08-12 10:08:45');
/*!40000 ALTER TABLE `bpjs_payment_proofs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `business_trips`
--

DROP TABLE IF EXISTS `business_trips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `business_trips` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `trip_date` date NOT NULL,
  `destination` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_note` text COLLATE utf8mb4_unicode_ci,
  `check_in` time DEFAULT NULL,
  `check_in_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `check_in_latitude` decimal(10,7) DEFAULT NULL,
  `check_in_longitude` decimal(10,7) DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `check_out_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `check_out_latitude` decimal(10,7) DEFAULT NULL,
  `check_out_longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_trips_employee_id_foreign` (`employee_id`),
  KEY `business_trips_approved_by_foreign` (`approved_by`),
  CONSTRAINT `business_trips_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `business_trips_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_trips`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `business_trips` WRITE;
/*!40000 ALTER TABLE `business_trips` DISABLE KEYS */;
/*!40000 ALTER TABLE `business_trips` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES
('laravel-cache-356a192b7913b04c54574d18c28d46e6395428ab','i:2;',1786529439),
('laravel-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer','i:1786529439;',1786529439),
('laravel-cache-ee0a35550119087fda01e1fd69e20c4364e76f12','i:14;',1786529520),
('laravel-cache-ee0a35550119087fda01e1fd69e20c4364e76f12:timer','i:1786529520;',1786529520),
('laravel-cache-f4fc893cf4785d358755095f33df40a49d2ae94a','i:7;',1786529466),
('laravel-cache-f4fc893cf4785d358755095f33df40a49d2ae94a:timer','i:1786529466;',1786529466),
('laravel-cache-face-login:e93974b23757ad6ef4693bc8ee426df61abec9703dae3b0794582e172414019c:cooldown','i:1786529459743;',1786529460),
('laravel-cache-face-login:f846475297714123739bd8fdef40a34964faf7fb4c08566d046abefe494fb71c:cooldown','i:1786529512254;',1786529513);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cash_advances`
--

DROP TABLE IF EXISTS `cash_advances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_advances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_note` text COLLATE utf8mb4_unicode_ci,
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `is_deducted` tinyint(1) NOT NULL DEFAULT '0',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_advances_employee_id_foreign` (`employee_id`),
  KEY `cash_advances_approved_by_foreign` (`approved_by`),
  CONSTRAINT `cash_advances_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cash_advances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_advances`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cash_advances` WRITE;
/*!40000 ALTER TABLE `cash_advances` DISABLE KEYS */;
INSERT INTO `cash_advances` VALUES
(1,1,55926.00,'Keperluan keluarga','rejected',NULL,NULL,NULL,1,0,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(2,2,98349.00,'Biaya kesehatan','pending',NULL,NULL,NULL,0,0,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(3,3,66281.00,'Keperluan mendesak','pending',NULL,NULL,NULL,0,1,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(4,4,31629.00,'Biaya transportasi','pending',NULL,NULL,NULL,0,1,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45');
/*!40000 ALTER TABLE `cash_advances` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `employee_balances`
--

DROP TABLE IF EXISTS `employee_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_balances_employee_id_unique` (`employee_id`),
  CONSTRAINT `employee_balances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_balances`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `employee_balances` WRITE;
/*!40000 ALTER TABLE `employee_balances` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_balances` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `employee_face_templates`
--

DROP TABLE IF EXISTS `employee_face_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_face_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `embedding` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `engine` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'insightface',
  `engine_version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `embedding_dimension` smallint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `enrolled_at` timestamp NULL DEFAULT NULL,
  `last_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_face_templates_employee_id_unique` (`employee_id`),
  CONSTRAINT `employee_face_templates_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_face_templates`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `employee_face_templates` WRITE;
/*!40000 ALTER TABLE `employee_face_templates` DISABLE KEYS */;
INSERT INTO `employee_face_templates` VALUES
(1,1,'eyJpdiI6IjgzQ3IydkhZVW54Y20rZEsrMy9ZS3c9PSIsInZhbHVlIjoicUlObVMybktWQXA0c2hDZ1JQZ1pjdTdIODZDUEp2QnNhbnVmL3FsTlNpK0V4UnJTL1Y1OVlNM3lRblZ6SFVLRldTM1czRys4Ky84K3I1c0lLK1lRZHlyK0V3QlJxYUxmZEY1UkpPVUFXekJOS1NTZHd0UkZiNVRsV3ZQb0podit6ZGhVNDR4ajlVU1Y1UG5jd3lBeFF1aS9hZnZuOG1ja2lubVN1Qy9OYVpCWGZZamk4MC9qOG1aNzVYdEpWcmJZekg0Q3d6UUd2NDh0UVBCRnFJT3J2VjVFM0hocTR3MjA3dlg4M1dIQ2hTUGNhMHFMQThta0ZPQUowTlJtVXI0bUFSR0JEWVl4cStxTkxiZ3VmZkNCN1c4ZWpHSmYxOVEvcnVKbHJtOU1qT255Q1lIVzZrT3RLUjZuaHMrZDdiZlZOaEc2d3dMN3ZMaHFPMHMvb3lGOTNtMGlRSlZWbEpkcVVYNFJuNTlJMUlsYVNpcEpaRXJndjd6ZUd6WVROZ3c1MkJqN0hoVnRwNG96VVd4Ky9EbmlUU2MvK0F2MUFmTzVvdzBUZVNYc3N0bXp2endhajJaT3VjanAvRXJNSVlQNmxJMkdEaS9qbDR2THhxQkJTdVorLzBtR1lvMFhxWFUyV1FXMUNHUVJ2cWQwNlZtdFRoOXp2V1pPWUVoYUVhYUsvTlMvSFgwN2E0M0VocS9OR2VVcElpQ2ZWQ0ExOUV2M1FEbHZOUEt1am81VXpNazZaYUk5V1ZHaVFIU2JtZGp3SSt5NFA5cldXWWVjRVQ5QWdONTZYTERNMVZkYS8wcC9sdXZPYUtKVktTdCtoTEhKc01XOTMvOUNMbFN0dWZaNHNYV2MyeTdidmFxLzRtMDdTTThYNWdwMW01UVFyNFVTenFWS3ZjbUVDaFJYY0VFcVhJTlRzOVRONzNIL3NzRHZkcXVWY0NaaEE4YmhvOW5TNVRjTWJMQWpuYlBQVjVTT0ZtR3JaN1dLMDlvK1hqVTRoaTN1bG1hMzIvb0cxQSs3TG1sdGZvcDdzZEYxdzk4VTRzNTZsTnJSZGE5TENFQUw4SEVxRUExcWVQdmFqR3RKRnRZY3h4eWJJUnZKNDltUVBiVllScnFQOFpNNFlVZGxteXVLbWpDZ2hKVllmc0p4anROK2pBcUtncjB4Z3lxQmI2b1JncHBCWThmY2dnQVZQL00vMklCa3JHbDdiaEpSOXc0djdnUnM1ZFBxTFBKaWhLSXNYSC9ZNXE5a1FGWGl4QW9RWEUzcEY3THJRSWZzWmVzWHlwZjViL1dmV2NBaXFoYkx2amN4OC84UHkzQ0FHV1dSNG5RQ1F0YUJwRWNFRE9QTmhPSmNDNEgrZEJPeEg4MStnNVVJN3BiUm5kNVF2alc3NHZILzBCdW1LK3VZck5RekVENHVZdDBUaDJUNHVEd1EzSzJ0YkpUZXo1cDV3eDhLTGpLNmV5aThkSlY1L3BuM1MydHNlMENmK0xVcmZja1lnaTFNWTA0cHFBb1Z2RElHdUtMY0RpWWIyVmgrVkIyOW5YQzFMM0dHMVJUVHFGWDhyNm9keHY4K1FGdlN5M2w5Z1B6Q3Z5UEdJNzlyaTl4WVZhQTZXMisvaXpZM05zcXRSaCtidWZENTMxSVRXVW40U0xsSm5EeGZQMGhvSCtJVlVoc2lld2NwT25zU25OOEhrTC9JdGRQRmpUS3o4bi85YzJVUHc5RTIrNU1SR2dtbFRCSlozcVlSK2hVVVNKRG96Vys1OGFUL1YxVHlXRnZkS1ZaWlI1SDBneWIxK1BzZnZscFBmNkhFWWdCNUFibCtMNFpCNXNmWk8zK0ZoU0tSL3JXbXNwdGFINjR3a0pXQzJmRlBscTh0ZUM2VUc2dW82VkplK0Y3QlEwSnFOaHBsbm50anNnNUFrY0Y0UEZITUhsV3dPVWZEYUR6V2s2UzEzMFFRVmRsenVKdkNtSUJsekNZb0ZuNCtad3Z4c1BjN3dQck56SFBZZFZBWTlidFlBelRNS1EwWGJ1Z2V6bWFkb0FGSmZoKytiNEtnZkFwZmxpaVZPaGZzNEMyMSttbHMvRkRBdUJXdWZ0UlhQeXdUc2R0dnI5azlXWHBDdmdOdThCVkFPR3NYM1hTRkJGVWJ2eXJPRXJqUEwrVmhVbWVjTGhHRUMzL2ovZTdJVHNDOXQzNHZqdVR4QnQxYmZQYkhUdGxQa0Nac2JmZkd3U2ZuR0w4OG56ckpuend0Q1Z1S1FmSWsrN1NQR2VBR3pSUCtWbWRoUDh3ZG5aM2VpbkJnVVJWcUxONVMzUXQ4dkZnWWE0d1lmTG1JREpaZVV3NmdqL1Yvd1FIV1JLYmp5enRkUFZseW9pSzJLUHduTlpwczR1MnpLb1lyUDRIQmIrUGRWVW1HR3pQTElmNzFLVXdHdm90czA4dlhzVSsvdFhvT0pUN3ZZVmgrVUZib095SkpHU2IwQytvR1pxQTVEUGR6TkllNmhNUjZpNFk1M3JTbXVicjFvbzVIWWttRlFoRkRZNklzcnlVcTltdjFncEJpR1JtRW5kRWN2OWtDMUd3a1piT0MvYzhGWlA4d25pV0JaUWZZVDlxTnlsTGxBUFhOYlZKMS9hbUxFM3pOVEF1UTB6SWVVMUhDMEVGSUJ2RGRwZnBNa2xBbElwTWloQWQzUkhPTFZwUy9UWk5scmsyRTVGNmtXM1BUSEtMeEpmbU0rbitCL1RKTm9uM0hFaU1JNHE0bnNzeVZVaEdCOHBubmMraGNhZmlpTFljMUpuQk53UDB0WE1HNWZENWlHdzF6RDk3K3hBQkJmR3psSnZ4SWJDd3VZRU5QS05zSWFRS3FEVzlodUtEV25kR2Z4RWZiVjFDNWxkN3pOYlF0Smt2Vkh4cU0xZWQwa3psZll1bE5Cc3BNL1BuQ2p0R0ZMS0JrdHNHclJpVnZZZXZqSXowb2xva09FRDlHYnBTMGhKTHVUYmhmK0x3VG9COVJnS0R2MjBvWXQ4cUFzK0NGVFJvTzJjNml0bGZtV3I0WjM3RmtZbEwyYjZTWHR2c0MrdnNSZWh1M0tmK2RGbS9DNHFRSTJsOTg5c1hkajI1Q01CcHhiVkRmSmlNdlJ4dWtOQzNTMmRVWXFYbi83L0l0eTRxSXU5MjFoQld4elZZVHEwRHRNb1hUREg2cEZaSDhZNHBwcldmczhrUmFWcS96dWIwVkk3ZHhTRFV4TGtyV0U2RVhDQzhrdDVNN0JERHhRQXczTityZHZzM0NqNXJ2ME4rb1M4b295UjU1a2tsc2JkSDdxaXRPUFBmMTlrRnJQNllaL3FTNGp4NHU1ZVVLRGdIWnpvVHBLTVhOTjY1aytxYXU4RGxGRHpodmVCS1RKWUhuTktkSUhFN0s3dmorTTlxajFXUUhXMUYrZC8vYlJDM2JmQkl1SHJYVHB2a2VOZGdLS201QytHMFBaSWFZMU1XZ0VMU1EvYmxrcDhPRkNWUnFxRUpwc3RaaG5jMk5wcTYvVFdpQW1lRUZtaHBuRFZxR1llUm4xV3h1V3pLZ2FVWE9aU0FmV3JyWDlVZ2FiNEF6Z2VJVVc5UkFlZ253NkZsSTJQajh2dU8vS24vQkIrc0wxLytVUERQMmVvcmk0dWNvYlhEM0RkS0JBU0o3MHBpUHljQlNZVksxU3llQkxMdjNTSlBZSTFPU2JOZFlucS9wTHdwY3ZLNjdFQ05yRFgwa1dObkRlMUlIa3YvU0p1alZDbkxqU0F6L2srZ0FueWNpQ1dDamFOMVZvQWJYMUxrdDBzZnRJUVZpcTByd0VJaDR4NSt6WVBDREtJR0hwRWdRYWE3cklnME1VOVdldHZCNTZjU1NyeEEvT0cvc2o0bEpvaGc0YlZtNnlwNmJITTRDRUlzNmIxR2NIZ25XV3lHUlhoYktUSjdNWGRUNjkrNUp4U29QdTlzM2picTYrQTRrYUxGK3NuREN1SDdqa0ZCWTRLSGFTejM4bjRiODdwb1puTkNQRlFTSFR6MC9ISk9jZEZvM0JkVXUyMHgxVGp0UkpGM1hHVjA3cG81YTF1Wm1aYTlLaXJzTVRpSmwwRjEwS3k2QjkyWkVsVXA5blU0dXdyY0ZscjBtVXZwOFE5TmozWVJPZnNvSXRjYmJWbXhXSVpJV29DZmx4Nk9ScFArekh4QnY3SERMU2lSbzE2ZjFTSC9SaXgrNmI4WWpxSi8xRWZJL0RRaVVqMkI4dTM1OTgwTW5jZitFM2NHUUsvTXRPTi9VeE5CbzRySmlLT25uMGsyL2hNY1RaN280SGVrakpSTmhFZFptektKL0JacU0xdG5UNWdBakZLd3BMeUdMTkM1cnFRODBySDZFN1FJMHdzWTZPeFYyTUh0c0xOankyS09md2JteVQ4N0wrTGROUzM3M1E5RWtLaGgwZXhaQ2RJcFNYem96a1V6cmRQRXBnWWlqTHFjZmFXTE04Z1l6ZGFhQ0FOUk1wa3RSNWVmeWxqZ3MzaUEvbS9iWTU2dExvOExVK1ZhN0Q5OHhrWWxmUVIvU2xPNXE3TlN0ZGF2L09RSHdaVXdqY3laNitaNzBvdkIvWjA2d3JhRmVzYWZGVnhvUEpFRUtERkVFcHFlUmtsRWRabXdlNlV4YXNYRGc5aFdhZnJQMmVmNVBKRE4xS1NhZU5uWTgrQlpweXRvUTlCYUViY250L0NyTnp4Rm5JVUhiR0lBU01PV1RIZ3VyVVlaNXB0MVB4MmtXdjNMV2lSYms3SXcvbGYyaUhrdXJ1OThGcWdvaW1pMDlMRWpHZFF0OFNVSitLa0VRZXpFUFVzMWpXdnh4OVZwV0lNajdRSWowcmlPcSs4b2RmUVZ6alJNcjhCc2dNeGlvSzRORHNtcDBNaER5YW01VEljeTNXMFU2NzEwamNJU3UxeFpRZWh5SFloSSs3bkxxcTJaWkNTQi96bEtDNG14L0kxMVc4T2VuNUFWSmRqS2s1dGJhVk1zd21zK0YyeWFsVElsekJiWWdUOUkxTmZBcTdIQjBNdFVESG1iOEVGVU1neDlMNllmTURTOHhQZ3dwTXZLcGNtU2lzY1NJOTAzb1ArUmlmaEpIQkNzQ2dOazlzODlNU29JdE5lYWVRMm0rMGsvS0d5U3NMUXE3RlpBYUE2cXVRZmZlTTVYS2d2dVJSU09paG04NlZhVDZlN041WmlaTnNHZDdRNTNBSFJHM0M1MTNIMTI3T0NJZzZRQWlVd3hLanBQUVN4TlROMzg1TVo5ajBhOFlRRlNvWmZqRGIwcXFIQWpqZTFkdEFRVkNXM1l5d2RUT0k3bzVwRzFNdUFxUnh4aGJqWC81RmtUOEJTNWpIMVJBcFB4dlVVbVQzbUREaXF0amJRSDdycEMvOWt4b2hlYmtnZXE2VExUOFhpWWpwK0N2dkxaLzIzNWd5bkQ3YWErcStsOFdOMW9sbU9kTDZnTVpFVm92aTR4MjFsK1pPVERzdU5lNHRORTNEVEhKUC9iNGF2RXBuMHUvNTNnTWRJbktjbFUwb2g4ZGh2aWdtWHBYTmFKVytjZTV6MWtqc3BGNjRNN0h3TXZjZEdqUmNwODN4SjkxLytBUzhwNFZpMW5mUFB4VTcxOWdPS3IvQXBhdUo4SEFHdGlnRUg4Y2p0NTQwSzNCUU9KME9jUG5oaWw2V2xrcEdWQ3VTYjBIYkZYQ0E5OTY4M0F1aXBjQUgyYXFOY2tiRHJDVHVDeXVEdUxqMjRHZnpveEhwMk42cmU0YWdpU3o1YnpFRTE4aytSelZTZ0V0RzVublc5Mk5XbENReHl3NXJhdGVURVlUOVg4cGgxU0MrcVY4cEZGTTFHMEZOZTQ5SEh0VkM3U0tzc0M0N3c5L3NXek5kUHcrTGloT09EWWZXd0poZGJLb2hnLzIvMTBacmlPYzVDSGhGQ2NVV2o4UUMxTU5hTFhicWxteUY3aC9zZzBQS0lhd3JhNmZydVd2a3FMZjZma2x6c0NmemQ4NW5YZWFVTzBhQ0treVZqUXZ6S3dqTDVyWW1HSkJyRkRRTVZ3Nm4xUFpBSzFuWTc2dzdWMzloN0E5NTlORGZmVUxFQlk3VVpRNk9nckl6d3BUNkFidGgyOXNJeFhDV2pBbHVVTUVUTXBmTS9UOGRHbm02TkRpT3dyNXp6aFhtaExkREc5Z2pabHdDS2NVUHlnQ0gvNTF0Y0lpU3ZPVEpkQkpFVytoTS9uUXQvTDRpUHVPdm40NHJ2SkFDWDVIb3BZSHhFVldSYVV3Q1BZTURCanBzdW5OVVhwQk85aHBMUnJxc2V6QWxxbmt4Zm5IU05uOHNOUFAzZHJZRUpscDcveEh0QlNjT2RCWkMySW9VLzlOYncxRHJsV1RTenRtNEN6Znh3R1BDdXQ5MXpYMEs2OXhEMmVyME41dm1HUEl2bzhxQzNUUEliNXE1bEljdEtQL29FblB5Q0J0Ym14TVBNOWh5SmRyVEMzVDN0elgvRWdodGJXY1RKRHRIbFBnWURMbkl2b0ZVNlhFNUFTRUxYOHdMYkF0V202ZEorMkp2aVprUWFzcTBBTzg5M1l2MXloYUZDZDRyTEMrZWhaMjFQK3g3Mmg5SDVJZUtnMkRoVTRtTElrVkJhNmRuaStpZzNYZlJ1WjdxNUFZVW5OQkMzdEdVVDc4Zk1aTkNVZVFUR1l1YnEvZm51d1Nab2ZkU1A0ZURRMk9hTmRUaDNLbkFHV2VFS3FzTER6eU5pbFJhWm9nUVFjSFFNNkRUMG5FOThONUFzNjBqQ2lLZzYvc2pxWmx5SDFhc3hpUmsrbno4bWs3d2hHVTFUdDhnb1FoN1I3RmtsblVpNEZrcG1VdUFtTkhTUXRFcFNZMWl5dGlaWEdTM2RWRThYdlkyZFJEYWZMYzB2MFAxOEJhSysyUWVEeVU1VEFUcWdRRFlCaWJKSzZ5Nm51UU00VUx0aUFGN081b0haU2hTU2RvVThJNm9ENHkxUEtnSlZJR2RSRndvU2NmMEh3QnBqTWRlVkx0UnFaZkRPSmpTRDFQeU1JekV3RnhRdHRjQ2lYRE9WdEFST2ZDVXFaY1ZMTzJUTFBYRlFMUnZDcWFTMW1scCtBSmhxVjY5VHc0ZDFNZWVtZWp6V0tIVkNjOTVwK3ZuaWI2UTdZUFNWelBNMkNrK2J4TmV2RzJjWjJPcWJ6dlVlWUZ0eTk4V2pLK2NsOUZhejJuWW1HTkFWdlY1L3J6V1ZGMmtLR1JiaEx0R21NUncydmxodU54MFJpaExjYWJUU0NjdWltZzh0ZFdYZDk2Z2YxaEpVbnhPamRBU2x5bW85QjFhTmo0b0Y0a25kM1hKR2VFZUxrMlNZRXFWVTBScm4wcEl5OUxSWlZKUEFIYjhoZUlmVDZjazVPTXhyNmo3VVVPWWgzT3dxR01NRHVWUzZwQ3YrS1h1MkxBZE9tbjU5ZHZTeVJRek4yN3N4NkcyMDAwTEJNSkhDR1hFOGVSUklxdjZmNjJrVWZ0M01mL2I3Vmdzcmtxa3UzV3lTMGd4NzlzTGM0Y0VoL3BmQnZSZ3RUYzl2VXFSeUwwNmo1d2VHTHBhNk94UENIaDFEb00vdW4vM3hNTzhzcUhkVlJXb21PV1RnOFJZMzFGcThZb0FOSDNwc0l1Ui9jcUFXY1hPazRjdVlVWWJucU9kVE40L0xheWlNVXpBWnhtcmNJS2dqalVzZzdwdmZhbHl5RC9JelE3T1dlcVhSY1NUK3JXSEZCbXNGWVpkcXUzRGc5aWNXZ1BWcmpicjJQdDNKVEVGTFlsZFYvZUMwVjExWHpMSHpFaERvNWFXbndzTFc5U2JaR1dTT0dRTVQ3bkMvNzl1eTRLaDRHQThSaERVTmQwSFAyTzk3UFJLMXowUTRwMHhuVitZV1FzRXVSVTQ4c2ZZS01oR3ZXRDA0dG4xRGEreUoxbkVnaVkxbzdZamxqOGhFUnlncWk5NHF2RnJTMm43OTgvd3htYVdUQWhld21laXpjVTFMNGJacHpVREpWVVFLZUxoWTNtakRSVkVocGRWZG1PU2Q5RjhERk14ejhoSHlMWlpTQ1YvbkllNGdpMnpkdUxudXZvZmR2VnR2TmhSQTd3ZVdmL1I5dThlb3lUUkNVWjFQZ0tFaG9leE9IV1dvUzM3alQvM3ZGL1M2V1hJcnJMZURIbGVWR3NxNjRzQkpmVmZqV2pTcTJrOGp5Y0Z3QnRVUjRjbUZqem9NSnl0Y1lmMDluUDl2ZDFSejlFOEVQWHFmVXFqaE51amh0aEV2TGMxczJIYjhDY2JmZzFRa0VyekhSdTJHb1hhWGlzVGVGbVhIeENUai9jbVVFWThwM25LenZHSlZ3Smx2S3MzZTRTc0RwenBQdHB5U3NTNzdoNXAzbFVpUlhLdlNiZzRHbTMvT2JHMWl2L1VZc3FaV0p1SmY1U0pVcWNXSjA1dTlVT1FRWXRROXdCYlFtQm50VnNNQnJpbWk0ZGdhUzFOTy9UdFNoT1JlbFRMaEMwamhkNTh4NjFvRFVjZmZNOWxGWmRPdm1BOTV0dGxFZzJMY3BnZDFZN0pZRFM5VFBXOEUrdUlaMy9GeWtIRTEvQU9rMy9NN09FK0VsaHBWdWFOTDlIUTV4dzRGeHJBNVVQYXFrU1Ivdi9kZ3o4T05WaXRIaXAvRzNBR0JESS90UW1leWg3UWNQV1BkREl6dVh1dksvMEt6OTdGVTBWcXdnMG1iK2RET2JEajNQci9rZEhsSVZYSy9ydXhPVzc4ZS9vd04rUXk4djBGcWZSRU9hV21BRkZMN2luTTNmNW5YTUVIdElkQWM1ZGlGaDNtd3p1LzN5bDVKR1FORHNkKy9rV0orc1cwR0E0dGcvK1BFODVoOTJKaGlGL0owWDZSNXNKcjF1NU9XTTlieG10K1BkaklBeUxaVmx1UUFhQXBZd3JHb0hDNFMxazEwT1JNWFF2VWZabHlQcUlDN1FseDNOMk5xSVZTNDQzaUY5aitCUWxHMlRQbVlFMVJLdkYvd013b0s1TGJNTHBjSmxwZVlzTXVVVC9SOGdYRHZPeU9WcjhpdlVkbXgxa2N3aWltWWhreDBJSDR6OUQxWTNXOC82OTBjNGZreGpkbVZmVkdON3Fjc2NQTzRuYkxGaGxPNmVXMDdOU2tqMFMwdmJyLytBZGphYXh0YUVLNGZVR3hKQUdKSkdqbzN3c2tGNjdyNTQ0OFhyWHNuaGhtRE1XSWcwUFRHV00xdnlrWmwvM0VSdFp5c1pMS2RJaWhLNW9zWk1PT1ZReTFVdDlSeVk2Sm5oY0pxWWoybWtySXdPUUVnYUdOcmRlaW9KckhWYllCSk05dWVUckJLNTRBY2tjTFp5UnFBUXRVdnVnaGd4a3M4ZW5uNExEMmtDbzhrcGk0STMvdWVRRlJWZ1FJRTBPbkh0YURzMFN4QlpWbUJBWGRZMG92TzVxNmtIbHFGZUVuNEc1WkdGZXZMdUdqMStpZUdlUmxWRHlQWStxMk1LY0I1RHViMXUyNWFBNGNiejhzRVRCaW1ndlR1YVQ1MzZITklJc2FiMEE3SjZNUE9xNlVRR2FST0dDcytaZ0svNGtqaWExVGhuanNLMkpHcGFoaXNSMkp5RWRFNG02OUNqQmZZb2Nsd3c2RnVnTjJyblFBV2pHUkdXMGpWWmlNSTNwbzdtY1V3ekVKSFZkdXYwR2pMUzJPT3RCaFpVVEhUNHFFN1duMERGS0NsSEdrdGNOaHhIVHExK3VMZ2dxQ3Fjcm1SQ3VpczVzVE11ZytJUnRXVEJxSXB4NHdxajVwNTlTbWJodEM5T3MyKzhadmV1OFdJVnA0dmlCZ2lOQ0NaVGM1M3FmOUZQbWhLa0J1SlRCZ016M1JXa3lUTHFRQSs5Mk9YZldISWVJcmNwNzhJcHovUHYwVTRjMmo2OS93N0ZMVlJMOXZSMnVrazF4WUw5ZGZIVmJpdXZQcWpQQTVtWk5qZ05UaCttUzBNeU1HYzd1cUluRTBhTkIzbXl1SlhHY2Nrb2VhSHBjTzJINTBZd2lvVVFDUGVMR0FnMkQxUGVVNnp5OGVwc2VFNE1oUEtzbko3cHhlTC9WZTVNTWI0YlEyc1NIN0MxVEJJSDZNRHJZV0ZEdlpoY0xvSTlSbW1MUUlCVEZudkNVVFpxWkt6SDIyeDR6N3k3MStKTjhhcjdZVmJtZ0Rvenp5OEdCUFR1ZllxNzZnT2ErUDVtaGxjQmczVVJSODkvSUwrYkxzdnluY2JFZDM5RDhSRGhBZm1yRS8ycE41emp1TktCZFdHUnQzcEZ2dVZQSko3WGYybXg0aHBNTHpLK253Qno5SFFVa3lLNjk3eWRZUVF4b1dvSmh1K0srd3pyR3ZoUnJZTkpLWnB0UHVDTmNibmdZUlhRMUhyWGFqWk80eG1mU1ZwdHJrYnNocEhZSU5vMGo0S3UrWHhmWlBCaG1xa3FRNmNMZ2NKTWhlY2VDNjhRU1kvd0h6R2RuUXRuSjZpOWFKSThCSnhrdTFqUlc5eWlhSGFobjJMbFovUk5CWFl4WFNqSHdLS1lBWXJpdFlyb2tlOGRDd1ljZ1JCR2RFazRnMURSSkI1emF1THJDelBBaXd1d2xQT0YyUXZ2aW40NXhBU3VYdnlmbW0vRG0vTUk1NXZzRFFUZ0VBaW9zdG45MnFSTE4rTGJsT3dtdjVlckJ6S2VSS0J1YjdEc3B2LzBxVWxLOEczN2dUT2tNU1JCTU1RSVpoSjJrb2RwQ29FSnhybndXeTYwTGgzdzFid0VER0VYRGRYYzY5UUtubHp4RisrS21xalBSL0tDMHh6N1FuNHFxcTUvM1BwUWFENWRqYW1SSi9xSHExcG1vM25RZ3BSNW8xZ0UyNUxVdWdZZmQzR2txQ25aOWowVjBlb0h6ZmdxUEFrb2d5ejVPRm5leUtZSFBZakFsYnd0L0dFNGxhLzlzTzN2UDU4WUdnYklKVDhiUWNzMDg2QkExY1pSaU5GNXMzcy9qSzYyYmRzQytyT1VwdWpoL1hnRTJGYm9pbVFYaHpkZHdCME85WVJsTHE0OHpUTmIrVXg4bUxWcEt2cGtucWd3WkJPTGRMZzZCNmw3M05LK3A3NUo1RldaV3gxSmhyNW1pVEVGMWFGd2twTGZJdFdyZlVGZFJHbURjTmI2dXMvSS9aV09SbUlWY2ozYzFaQnBYOVR3V09oVFhyRUs2NEd5RHU0cU14SzV2OXlhdnBMUzc2cGNuOVlaRW5yYjQ2WldPZ1E2dWFsWFFCUnEzNlJkQ0RjMzhnYjd4eDVtZVdYSFN2SVJjOHBzb0RXZUJjY0J3K3BKclV6dFk2c0R6RDdneC8xQ05OSnlDZVlNWTFiTWV0U2NPdWVaODUrTUxkOGw4blRnb25meWVvT241SW9ISXMxUEJTRk85djNHNnZFU1l0TkYyeVdJbTN5bFkwcVVXenpWT0ZmblROekd3RGRUYzQ3bndFQ3hEbHZFU214dnRha0ZtZzB3aFM1N0E2YWZaRHN6OGdEL291WmpaZzVudEMrODM0OVNhYUNmUU9Dc3VaWXpIV1dKSmRYdXpTQSt2UzNKSW1CLzc2K053dWRmOVpMYlowRkhNWDlRR29sZUYyQTVDb0QyU1JWQitINkVZakh2SUJoMGlMcW9LeHFXM1FFeHFYVTQ4SFVaVHZQWGo2d2FoZEpqL3g5WllmVThJVVR4WUMwMzRqVjJwT0svTHdXa09hVHFuaEpUek5RdThlTDQ1UllqLzZuVU5rc2tmS0NCTWxpeTBRYm13WlVTUWkyeGZQZ3EwbmZSUEs2cHB1aFI1UnBaNHRDOHNqUkp2ZWhyR1F5aHZJaWVKWktoNzhqcHBVelJ6ZWFzMHRTUzhLWUV1R2dFODRTaTd6cVIrc05Sb2FMbnlDSmJnS283K29qbitWeTFaNjFadm02ci84blZpcFgwSGlRTVd3K3BuK3dqNml3bFhBSFZVSWFkNTBBdWZQQ1ZvcWxBZWd3NHBmeDBqUm5wdVdkSVlCLy9UMGoyL2VQMUdrUWNJNW95Nk96K04zTENlaDcyN21zcERlTm9wc0xCZWtjblB3bGwxaTB1TTdFSCt0YjJLQXFEeXNhMlZhT0wvY1lDNXFJejFCL2Urd082dFZ2cUdvNVFKNVNYK0g5ajhTekpZTlNaQkFVMmRmU3FtM2tKdldtY3J1c2EvUGFOK09nSjZPVGE5czJLQ2pqNzkxMTFlekJJQzM4bDZqMzJoTEpSd04wdUY1VXl2Z01LMDVGalgyZy9TUFloOEJrMTRPc2NBYzZOUGdJVERBQlVYUXdTTURIT24weHlvaUd2a1NrdzNkMDlQRWFaTStBQzdudExPTnBydGhoQ2gramhPVzl3MmtIMS9PK0lac3V0eEU2bHdXWmxjZFNqNXkzdVVjcGdwLzhEOFlWRjA4WVd5WEVJRmQwYmlCcm1YVmorODQwUTRqUytaQlA4ejVFb3ZnYTEyRThrN2lFQWFyUFRwWFFzMjgrb1U3UDlrYWdoTG5yejh6ZWl4WThZaDdGdWJmODFqMUMyTXRTeUUrZVdGZ0pFUGNoREk2emQ4Y0QyeE9jQVFCV05ueFRLVVlOdGxQWjF4TEpPM3RWTzNrZkUxUk1ndzNWU2J4L2hPUFhWNDJhUnVYRDg0QzFnWFNVNnpUblFTaEh5MkUwUGxjZmQ1VGlvRk9vODlhYnVSVFlvYmQ0OCtlSXV6TlYwWnVuRGx5VXBZWVR0RmZCZ2oxdlVWQmpuQUkwTmt1dVp4VE4zL1dPeXpsdFlxaGVXZ29FcWJMa1VrRzM1LzlaMldFcGNHOHZJN0k3THA3bWkveC9HaU1PaVFHVEFNZVpXQ2pkS0pNVEg4Smx6Y0daT2JkR0lobllybUdzUExpSDNEZzNXaythMzR1WWt5dWViWHlTbC9nRVR4a2RMSHpyeHRqck9Ib3Bvb0hWNituejJETlFaL1kwVWV6Qy9NbEhoamJrZGs5YjErSUhKdWVVU0k0dldMeXhyTG1wQmdBalpMWkhpcFFLalQ0bDQvTG50Vi9KL1ErS2QvTEhuWGk4VzZaTHEvSHkyWWh4OEsrOXdtYUhMQXpRZHRFUWlsbUNxMTB1enNCL1pmK3d1VkZVbXdXVkpZR0phNXVrTWNzNHNmemhKRmx1ZkNsejRRK2xWS00yRzV3WnFhQ2J6SllyYlhVbzVwRlBQTTA5WWFVMFpWNWZYNWU2eEhzeExSZUE5cFRVREkrVlIxRmNLejdyVkNHckM0L3pYVWlKWTE2aUVtVW9SQkFCZ0VFeHNNWFp1bitCUGpFQW5qQzNSYUVzRmJLRVJOMVZrbHZsMllUaXlBVXlRaTFPbS80UXNidDBWNVVWVisxS3Ura2lhUCtwYS83Ui9tRDFjNHlocDlFQ29TdjlvYVozVEtwMjlINUNQNlM2eFNCVHZYZDByclF6NTUxNWpzV0xUaFBnUGtNV1F6T0JmSUluK2RFQ0UvT1dpYk5sV3FRUGFEeHk0d3FGcCt2NlVkL256cjJJMU5xcUFJaGpSNVFWalNFZXFKLzBxMGFjbm5BVHNhditDMG5nemY5cThPMUhvd2pSR2lSdTdoMHgwdUs0ckZSK2FtdHIyWTNEdURZSktwZEx6eWM3bWhSR2V4b3B4Q1h1alpNUWtwUnVYNDk1OHZwOEIxWFQ5Z0t6WUF5QUZGd1pOcFJCM1RQOXQybGhzdnVWdDlVc3ZCQXdyNWxyNHovTERhMmlRRnlxQXd6ejdHQzFLYlhtcHVYRjdyWVRUa1d1NFRRR0lkTklPTkwzeVE4ditreFY3UzRSelBUSHRuWHU3VDZwM3Fmd3ZPb0FWRElxblp4SExheXB4SmZxWFhDVEY4VTc3NTkrb0Qrc3pjWlJwODBjUWlhWjM3di9kNjc5N2V5cS94OUZ0WjltMUx2RW9xRUtjVkhjeFgzOU1hUVhtYkJhamwxNzdSTiszOERkcURSNWhubS8zNnhFRHFkWDBrM3FJOW5rcFB2ZU9tYm11b0R0Z3puVkVLZ3JTeUZaM3dFMnFjdlJkODRvKys0dVVVMkRsWGZ2WUcza08wTWVlYktXVjlhWjBrM3NtZFpPUzNhSmFDRkNuTSt2L0FkdFloNzcrRjEzR2IzV3RYTzRDL2JmNEpWb2JEZEFISGdETHFuWUt0LzdEekVsL1h4eHVlSjVSSzN4WFVDR0RQRHlJNXpxSTlNRjZoK0xGd2FrM2xPUGZaMnJia3ZhYW5ET0hwUzByRlF6c1NjVSt2dDdvUm0xYUF1SVZpdjVmWjVza0hUV0xNTzVFOGNJRWJieXEzVHVZUWtzUnJjMVdldVhldDRScGRyRktQRW9wdXFid0hlOXBEZC9BZEFISVlqK0NMdjRsL0tKUVJveDZOcUd2OHhEdCtKTUl4T25zaktoQWpuc2s5NTBWVUU5MVVva1U4cERtTFRXNTByQjlOQmF0aEw4RFg5K210cHF6YVppK1NlMXJKMGQ0ZlROU1h2N0VyMEJNaWIrL1A2UEp3RzZadHc2Z2FzNUx6ZHdCNWVUK1piUWduOVJIUnFYSWlraTV0VnZyNnZGTUR5V0RIdGszdjFDZ2p3eXBMTlFQVzRFajl4SnlZUW1zVW95b1JxTW9RejFiWGtxd2lPMXQrZFN0WHBBYWFYU3pGUFo4TzRvRHpJTlYxd253NjRQbG9ITzNNRC9NYVRmZ1cwWmVpaWVpWTMxTHhXdmM5TWlTZkN0ZEM4L0hNV1ZrVitrLyt0czR0QUdmRDZTdk5wMGFaT3RhSmtDaUIxeWZiRkhrOHNqZlhXZWU3OExqUysvWTk2Y1RqbzNLZi9QTGJYMGdDNG9EQjVoODlMNU5oZlhIeXQ2TGhKS0Q4cGRHQ1lQZXg5ZkhjUUhFRUN0UU44ZWM1bTN0TGpyVFNXZzZBbDVnNDlML205b054bmNYT1hNYmFZMEZXRVh4TEl2cktPNlR6aUN4c1l6emloZGJaM05YbTNHcFVFWjlVMGZ2OStqL3BhUG1KVHNrZDFjOVNQQkRjRUo5cWtDTVQzRFdHUUd2QmNYVUF4WHd0WWVjVXc0NWNiNEo5Q2oraGs4M0R3a21HckdMSytVMXoyUUJVVlA2VXVWNzdqa1RaSlFJc25IS2MycXlYRDBzeUxCUnpYRlgrcjdZaURzUHEwRTIzNHJIZEtuVkxqSmtuN3QxeEwzYzRveWw0czU0Zll2bGFPRUFVcXhDYTJaUy9oM0NXbFpHWlN4NVlrWU1UUUwwcFI0UVM1L2pQZVJLOGhVMk1BYUFBRThWMmFQN3ljN2pLSWh1YTVEUWhCOWVKOXBhandMUkF0dHBiMXY0aGp0UTAzaTIxa2NlYm5GeWZydWxCakxsbldkTC9GNlVHQjRmRVFudWJzTW04SjE3NzZzSkZjci8vejlQYnJWL0Q3OU5CWk4wSVZXSVBBTFlPTEVjZ0N0ZXRuNGJ1M05lbVFUdENCZldTbmtZKzJyQTgwZ2lIdlFUSi9ITHNTL21lUXpQSHFkd3c4b0hRczQxUXYrak9RZ3pRZTduVmRYT2RvK214aGpIY3NMczMxQ0ZXWHNWTjRCY0x4aEdxeWh2NS9ueW5BVUZEeHM2UUJ6NkpOWk5ldnJjekhGc1AvbGJ6QU40TkpvdHNRSnNYTFdOaWlrMVpYSDNiQTRKeU1mbEEzZTllRHBKSllnNXBkRjRhaFVLa3pBZ0JRaDJIK2VBekdzc2hRQ1dRZHJnNTJraEF2SjFwTUJNWmdMT0FRMHFCK2Q5dUVMcmp4dE5mam45WWtqWE9WY2N1SlFVZlE5eTBEaFc0WHg3bmhLNVJReTAzeXIzSDNKMm1tSGh2Q2dlaTJaUTVBOGlMNnRFeXAvamFoTzA1a1FVN0hKR2F1UjFVd1ZzaXp6dlFlb2ticHdOaERjYnljRkxuWC9UK1llTnlDZE9qYlUxY0VEdEVTdW8wN3g4NUU5SXBuS0FCZUlNZG5jRDl0VEhMZDRLY0pMSGlINTN6YTR4cG5uSzUzZ25wYUtKcUd6bjIwaXgrd25CYjZCZjRLWlZQcjBpQzJXakptTk4xNlkwenRSSVhLbU5ydFgzbDVrTkJYZmV2V1RQS0ZMTWV5Wm1FRWxTN1VNVEV6OVR6ZUJTN2NYcXpXZVFtUWJmUWxOdmZNTkRVWmU4MHdsaE52VnpMZGRjSmozOWpDNUtNMzBZWGNyb2VqS0NBZmdwZ3psbngxaEpwT2VHcVhOVmRXVjdBd2tna2RDbzNlOWpjWXVHVm13eTZyUjhETThnVjF4R25rYlQrcHhzVi9lYjBRZ0s4cXkzcUlFb0pycld3UlNCOVgwL09yWGxPRVlZUWhLZkppU0RoalZnNjhJdlZmcFJmMkZDTlN1bWZHWmxzb202V0JFN3lrT25nZndyaERlRGVGUzhyWjVzdTIxd1ZQSkFXS2FqanF4RHVJMldVSDRwOUZ0MlNHOThvZC9XeU5STTJobCswNFp2ZDUxRU5WQ0xueDVQRTZrK251Y2IyUWFKQllBcGJWaW10dS9QOVozVThadmgxS3M5SkNGR2xIZDZWcFMzZVZaRThOK1UwRHQ3bG1Vbkk0b0dmRk5XcnpVK05xMTAzZmVEeDdIVVlwNWVXR0tKVE9KU2ZwRXhkajJDWXRqcmJxeDVvOWtzc0FwQUx6d1pYRUgyOXUwcEhQRU5kTmVnSEpyUWV6M2g1a1dLV09KRjR6alVseGU0SnRBVHQ0NkJEczZlRXpIcndjMGNqMlI0UTRxTXhBNklsYTh5MzYybmZ4SjEyNmZZUm1yZVNiN2YzeFlYeGNwLzJ0Znh5QW5uU1lDcjNkY1J2Z1gyL0EyWGhmdy9vV2NoL3dTNmR0WFRXNEdxTVRpNVNqaURObVRmb0szMVgvNzJoZGJIK3c0VXE3SExhZFc3algxVksvOGZpa25pdXZKRWtNYnExVldOSGR1TUVIOWp5NUdyeXpzMkNHWitIR0IzRWR6a0M4K29PVmQ2UDdNNjh0aWFyMEZoNVh3amJKVFF4dHFtRmpTUlRwZlNUcnRkWWRIVkVwL1QzeHg5azR1OGhCN1p2T1ZlUk1oQk9IVGw1UmlQS2NYbHV6SWZQRjRmb3ZVcVVYNTBTMTY0TExGT1JWS2szcWRCWmlKTjV0LzE4UmxoVFdwek5UZktmdll5Q0RLT0ZrRXlXclRXSDdRdlpHWHplZU1rVWFLOXlLb0FWOGZubldJdDExcDRjTG9ua29vQmp6ZzJ6YnNFQnM3K0pvRTVUNk1vYkczbUNjQmQ0NEcrR1EwQ0x5UzFuZ3ZlR0JtaHlFZlFtaFVVdGhFbUEvOUptZWpqVUtKd2VvbTAvNVp0MGxYUHcvRDFNandjUm1GMHpkZDRLQStHL1hTVjZvZTQwenBlbE5oSWRxVVhMRWRxN0I3blhST052NU90L1I2TTUvK0dlQWNXVXlJWDhXeDVKZ3ZnektuaTA5T0hWWHNqQ0QrSC9kaW5CTGkxeFo0ekJMaGJ3Nis0ZkJUU1RTTHlnWUZJWXN1aE13WVBRNVpKSDN5ZnVURU5QZHFZZXZIWHQrakg2OXVoU2UvaDEza2J2VEgybWRVT3d2bFNtbi96YytqK1VpUGQ2UTJhRlBhbkJzZkNGT1l6V1I5UzZsYnlFRG40YmQ3T2w3UnlDOFlYYys3KzJZQlhFclZTYnh6VTJLUjAyR0owV1R0bU5GQ2xISGZSRjU2d1NFZU5IYkF0MVBrdzVlR0F6ZzlVNTlLcEJLK1AxUzdIWVlRZk1TU0xidUV1R1VwN3NBemd0MUY5L3daa2NoeDJJTkxFVVpiWjBaOUhQc3BjZG5GWkpneFZCTzd0WVFucEY3UkJSNjdzREJRN0toMDNlWWVoNjI2WnJUZi9XNXFZZlBBZTQyemhWVURHOFpxN0U4L2NvUmlXUmowamh6VS9SZzNoa0RSd01tdnRJR3p0RHRUbmd4SW52KytaT0NoQmRKeWFvcnkvZ2pSSGM3dEVxV2hHSjBZbko2ODlZUzN5SFlJNW94UkFHR0VHb1dGVlJiQTJoMkk3OU85NU1mZHBpZExLWTRPb3Rsbzc4NERLNEgvK2ZVcDJjc0VMZDY5cytrSnJvK241NGozUG0rTUxNOERGZFNBNWFGZEFvVGJ1ZlFub3pTcHpTRHlMeG1OUzZtUTRQQi91YjZXRy9tS0RGT09YSHJpK1kvWW1SeFVIS1BaMDdsYlhha0FyRG9BN21XcnZKLzBTWFFsTkFUQTFTZ1NudFdNMytXb2ppMTRuY0ZMOStBTkFqeHFuV0wzNTNZQThLTW41VGVHQ0I0N2NzQ2JNbVVOUi9VNkNnTmJLR1VhdE5hYU56cTI1aHlmZkpmRFpCU2RtbzFaN1JkcmlLdHhYdGdoOEtjTHRMencxWlFMaU9hT3dkUzhXOVpyZzc3TythZCsrU2piZXEzd3ROemRBN09BaW1QSEcvZWhVQnU0NHVlaTdLcXc5ejhXRllaVk1DcEo0YkJFUGtYa3pFUGVYT0pVU0Y5VDdJNDF2dDJ3QTZaU3UwREdDUTlYYU1zN0NBcldLWWlUQlZpWTcyTVB1TjVkQkRLZkdndWVobHgyNjlxeUFFYUl3Y1pTanBQVGQzUUFtelVwVnNQUmxyb21rQnlCWTVGSy9mZ01Ga0JSVWxmK01IcEtBeURFNzZMNjdVWkVVWk9mblB5N2FWZHpETng5RkY0aVhQR0FQZk5OQ3dyZlFqbzVHamtKZUhzU091OEZ3Y2RFVXhnNVFJanNtUXBDdXZ3MFBSKzU5V0pobXMySHNzOCtDeDhFWlR1NWJZamZmS0R4R1E3Q1QrclZPS29LZFBJSHZ3a1hiMnhnbFRpaloyZTEybDBJajdEQ2taZHZFYVlVM0kyQWw0QllTU3VpUm1Mb2lkU2t6Q1k4QnBROHlWSG1FV3E2dVAyMUpGcm9XNXgrSlVxYUZxdnpoNFc3UjI1KzVlNlR4RWd1YjlhRnQ1TGlTYW8wSVlDUW1jL3BxdUhxa0tIeG1pMTkzZDErWTNYd3oraEIvQ3RwTTBiQlZTblF3Z1dNSTBjSXRlYWMwd2p2dktiNndkRXRPd3J6Z0VaWFZjTUhEcTRyMUtrUkw2UmFmM3FRPT0iLCJtYWMiOiJjMmQwZTU4NjEwZTU3MThkMjdmYTM2MWJiNzRkNDgwYzFkMWM2OTJjMGYwZTY1ZmUyMTRmZjU4ZGNkMWI0MTZlIiwidGFnIjoiIn0=','insightface','1.0.1','buffalo_l',512,1,'2026-08-12 10:09:57','2026-08-12 10:10:56','2026-08-12 10:09:57','2026-08-12 10:10:56');
/*!40000 ALTER TABLE `employee_face_templates` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `employee_payroll_settings`
--

DROP TABLE IF EXISTS `employee_payroll_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_payroll_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `gaji_harian` decimal(15,2) NOT NULL DEFAULT '0.00',
  `jam_masuk` time DEFAULT NULL,
  `batas_telat` time DEFAULT NULL,
  `mulai_bonus_datang` time DEFAULT NULL,
  `bonus_datang_awal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `jam_pulang` time DEFAULT NULL,
  `mulai_lembur` time DEFAULT NULL,
  `tarif_lembur` decimal(15,2) NOT NULL DEFAULT '0.00',
  `potongan_terlambat` decimal(15,2) NOT NULL DEFAULT '0.00',
  `jatah_hari_libur` tinyint unsigned NOT NULL DEFAULT '0',
  `jatah_cuti` tinyint unsigned NOT NULL DEFAULT '12',
  `potongan_izin` decimal(15,2) NOT NULL DEFAULT '0.00',
  `potongan_cuti` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tanggal_gajian` tinyint unsigned NOT NULL DEFAULT '25',
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_payroll_settings_employee_id_unique` (`employee_id`),
  CONSTRAINT `employee_payroll_settings_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_payroll_settings`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `employee_payroll_settings` WRITE;
/*!40000 ALTER TABLE `employee_payroll_settings` DISABLE KEYS */;
INSERT INTO `employee_payroll_settings` VALUES
(1,1,100000.00,'08:00:00','08:15:00','07:30:00',20000.00,'17:00:00','17:30:00',25000.00,10000.00,4,12,50000.00,0.00,25,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(2,2,100000.00,'08:00:00','08:15:00','07:30:00',20000.00,'17:00:00','17:30:00',25000.00,10000.00,4,12,50000.00,0.00,25,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(3,3,100000.00,'08:00:00','08:15:00','07:30:00',20000.00,'17:00:00','17:30:00',25000.00,10000.00,4,12,50000.00,0.00,25,1,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(4,4,100000.00,'08:00:00','08:15:00','07:30:00',20000.00,'17:00:00','17:30:00',25000.00,10000.00,4,12,50000.00,0.00,25,1,'2026-08-12 10:08:45','2026-08-12 10:08:45');
/*!40000 ALTER TABLE `employee_payroll_settings` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `employee_performances`
--

DROP TABLE IF EXISTS `employee_performances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_performances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `supervisor_id` bigint unsigned NOT NULL,
  `employee_target_id` bigint unsigned NOT NULL,
  `score` bigint unsigned NOT NULL,
  `grade` enum('A','B','C','D','E') COLLATE utf8mb4_unicode_ci NOT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_performances_employee_id_foreign` (`employee_id`),
  KEY `employee_performances_supervisor_id_foreign` (`supervisor_id`),
  KEY `employee_performances_employee_target_id_foreign` (`employee_target_id`),
  CONSTRAINT `employee_performances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_performances_employee_target_id_foreign` FOREIGN KEY (`employee_target_id`) REFERENCES `employee_targets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_performances_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `supervisors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_performances`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `employee_performances` WRITE;
/*!40000 ALTER TABLE `employee_performances` DISABLE KEYS */;
INSERT INTO `employee_performances` VALUES
(1,1,1,1,75,'C','Est tenetur earum expedita rerum ipsam quia assumenda.','2026-08-12 10:08:45','2026-08-12 10:08:45'),
(2,2,1,2,75,'C','Neque est explicabo repellat praesentium impedit autem voluptatibus omnis delectus qui.','2026-08-12 10:08:45','2026-08-12 10:08:45'),
(3,3,1,3,83,'B','At enim ea ut dolorum molestiae facere provident.','2026-08-12 10:08:45','2026-08-12 10:08:45'),
(4,4,1,4,87,'B','Quaerat omnis deserunt nam excepturi nobis necessitatibus nisi dicta et voluptatem sed.','2026-08-12 10:08:46','2026-08-12 10:08:46');
/*!40000 ALTER TABLE `employee_performances` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `employee_targets`
--

DROP TABLE IF EXISTS `employee_targets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_targets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `supervisor_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('ongoing','completed','not_achieved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ongoing',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `target_value` int NOT NULL DEFAULT '0',
  `current_value` int NOT NULL DEFAULT '0',
  `progress_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_targets_employee_id_foreign` (`employee_id`),
  KEY `employee_targets_supervisor_id_foreign` (`supervisor_id`),
  CONSTRAINT `employee_targets_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_targets_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `supervisors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_targets`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `employee_targets` WRITE;
/*!40000 ALTER TABLE `employee_targets` DISABLE KEYS */;
INSERT INTO `employee_targets` VALUES
(1,1,1,'Target Servis Bulanan','Menyelesaikan seluruh pekerjaan servis sesuai jadwal.','Servis','2026-08-01','2026-08-31','completed','Target bulanan teknisi yang ditetapkan supervisor.',25,25,100.00,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(2,2,1,'Target Instalasi','Menyelesaikan instalasi perangkat sesuai target.','Instalasi','2026-08-01','2026-08-31','ongoing','Target bulanan teknisi yang ditetapkan supervisor.',15,12,80.00,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(3,3,1,'Target Maintenance','Melakukan maintenance rutin kepada pelanggan.','Maintenance','2026-08-01','2026-08-31','not_achieved','Target bulanan teknisi yang ditetapkan supervisor.',30,5,50.00,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(4,4,1,'Target Perbaikan','Menyelesaikan pekerjaan perbaikan dengan kualitas terbaik.','Perbaikan','2026-08-01','2026-08-31','completed','Target bulanan teknisi yang ditetapkan supervisor.',20,25,100.00,'2026-08-12 10:08:45','2026-08-12 10:08:45');
/*!40000 ALTER TABLE `employee_targets` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_bank` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_rekening` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_rekening` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `check_in_limit` time DEFAULT NULL,
  `bonus_didapat` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_employee_code_unique` (`employee_code`),
  UNIQUE KEY `employees_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES
(1,'EMP001','Admin 1','admin1@gembok.com',NULL,NULL,NULL,'081222222222','$2y$12$FvGpRB9JnnRi/xgX2IrtmOCWjk0BhOZogqM/Jhhp/4n.Gn7ZWQVrW',1,NULL,0,'2026-08-12 10:08:44','2026-08-12 10:08:44'),
(2,'EMP002','Budi Santoso','budi.tech@gembok.com',NULL,NULL,NULL,'081234567890','$2y$12$SgnZxoB0nr8R4lOx7CBEX.TW4I5vwCmnMv4KHaQ/3BT0zkLL5tfw.',1,NULL,0,'2026-08-12 10:08:44','2026-08-12 10:08:44'),
(3,'EMP003','Asep Hidayat','asep.tech@gembok.com',NULL,NULL,NULL,'081234567891','$2y$12$Q4EzNDztyLY2REvAZbTHmOE1CbDcoBfDM4QLuEMbElkSmjZX3ZypS',1,NULL,0,'2026-08-12 10:08:44','2026-08-12 10:08:44'),
(4,'EMP004','Dedi Kurniawan','dedi.install@gembok.com',NULL,NULL,NULL,'081234567892','$2y$12$iEnAIde0seIHHzevYxUav.GcsLTWNrFDSxCHowER88GrPjdmtsVyO',1,NULL,0,'2026-08-12 10:08:44','2026-08-12 10:08:44');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `finances`
--

DROP TABLE IF EXISTS `finances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `finances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `finances_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `finances`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `finances` WRITE;
/*!40000 ALTER TABLE `finances` DISABLE KEYS */;
INSERT INTO `finances` VALUES
(1,'Finance 1','finance1@gmail.com','$2y$12$ybuMn9MAiYCjKs3F4p4LgOSvi8kJzg.1c7WtPwoXWDVkBIbJpZsQq',NULL,1,'2026-08-12 10:08:44','2026-08-12 10:08:44');
/*!40000 ALTER TABLE `finances` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `leave_requests`
--

DROP TABLE IF EXISTS `leave_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `type` enum('cuti','izin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_requests_employee_id_foreign` (`employee_id`),
  KEY `leave_requests_approved_by_foreign` (`approved_by`),
  CONSTRAINT `leave_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_requests`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `leave_requests` WRITE;
/*!40000 ALTER TABLE `leave_requests` DISABLE KEYS */;
INSERT INTO `leave_requests` VALUES
(1,1,'izin','2026-08-11','2026-08-11','Keperluan keluarga',NULL,'approved',NULL,NULL,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(2,1,'cuti','2026-08-12','2026-08-13','Liburan',NULL,'approved',NULL,NULL,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(3,2,'izin','2026-08-11','2026-08-11','Keperluan keluarga',NULL,'approved',NULL,NULL,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(4,2,'cuti','2026-08-12','2026-08-13','Liburan',NULL,'approved',NULL,NULL,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(5,3,'izin','2026-08-11','2026-08-11','Keperluan keluarga',NULL,'approved',NULL,NULL,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(6,3,'cuti','2026-08-12','2026-08-13','Liburan',NULL,'approved',NULL,NULL,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(7,4,'izin','2026-08-11','2026-08-11','Keperluan keluarga',NULL,'approved',NULL,NULL,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(8,4,'cuti','2026-08-12','2026-08-13','Liburan',NULL,'approved',NULL,NULL,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45');
/*!40000 ALTER TABLE `leave_requests` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `medical_leaves`
--

DROP TABLE IF EXISTS `medical_leaves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `medical_leaves` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `sick_date` date NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `doctor_note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medical_leaves_employee_id_foreign` (`employee_id`),
  KEY `medical_leaves_approved_by_foreign` (`approved_by`),
  CONSTRAINT `medical_leaves_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medical_leaves_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medical_leaves`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `medical_leaves` WRITE;
/*!40000 ALTER TABLE `medical_leaves` DISABLE KEYS */;
INSERT INTO `medical_leaves` VALUES
(1,1,'2026-08-14','Batuk',NULL,'rejected',NULL,NULL,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(2,2,'2026-08-14','Batuk',NULL,'rejected',NULL,NULL,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(3,3,'2026-08-14','Sakit kepala',NULL,'pending',NULL,NULL,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(4,4,'2026-08-14','Cedera ringan',NULL,'pending',NULL,NULL,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45');
/*!40000 ALTER TABLE `medical_leaves` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2026_07_21_061901_create_employees_table',1),
(5,'2026_07_21_062852_create_personal_access_tokens_table',1),
(6,'2026_07_21_072237_create_attendances_table',1),
(7,'2026_07_22_061402_create_attendance_qrs_table',1),
(8,'2026_07_27_062018_create_leave_requests_table',1),
(9,'2026_07_27_081442_create_medical_leaves_table',1),
(10,'2026_07_27_101611_create_overtime_requests_table',1),
(11,'2026_07_28_060706_create_cash_advances_table',1),
(12,'2026_07_28_073037_create_business_trips_table',1),
(13,'2026_07_29_061452_create_finances_table',1),
(14,'2026_07_29_084010_create_employe_payroll_settings_table',1),
(15,'2026_07_29_085927_create_payrolls_table',1),
(16,'2026_07_31_065230_create_payroll_corrections_table',1),
(17,'2026_08_01_064430_create_supervisors_table',1),
(18,'2026_08_05_052546_create_employee_targets_table',1),
(19,'2026_08_05_060136_create_employee_performances_table',1),
(20,'2026_08_05_094636_create_bpjs_payment_proofs_table',1),
(21,'2026_08_07_052355_create_employee_balances_table',1),
(22,'2026_08_07_052601_create_balance_transactions_table',1),
(23,'2026_08_07_062026_create_balance_withdrawals_table',1),
(24,'2026_08_10_070000_create_teams_table',1),
(25,'2026_08_10_070100_create_team_members_table',1),
(26,'2026_08_10_070811_create_task_assignments_table',1),
(27,'2026_08_11_163000_create_employee_face_templates_table',1),
(28,'2026_08_12_180000_create_role_face_templates_tables',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `overtime_requests`
--

DROP TABLE IF EXISTS `overtime_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `overtime_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `overtime_date` date NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `overtime_requests_employee_id_foreign` (`employee_id`),
  KEY `overtime_requests_approved_by_foreign` (`approved_by`),
  CONSTRAINT `overtime_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `overtime_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `overtime_requests`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `overtime_requests` WRITE;
/*!40000 ALTER TABLE `overtime_requests` DISABLE KEYS */;
INSERT INTO `overtime_requests` VALUES
(1,1,'2026-08-10','Menyelesaikan proyek','rejected',NULL,NULL,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(2,2,'2026-08-10','Persiapan meeting','pending',NULL,NULL,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(3,3,'2026-08-10','Menyelesaikan proyek','approved',NULL,NULL,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45'),
(4,4,'2026-08-10','Pekerjaan menumpuk','approved',NULL,NULL,NULL,'2026-08-12 10:08:45','2026-08-12 10:08:45');
/*!40000 ALTER TABLE `overtime_requests` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `payroll_corrections`
--

DROP TABLE IF EXISTS `payroll_corrections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll_corrections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payroll_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `finance_id` bigint unsigned DEFAULT NULL,
  `type` enum('addition','deduction') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_corrections_payroll_id_foreign` (`payroll_id`),
  KEY `payroll_corrections_employee_id_foreign` (`employee_id`),
  KEY `payroll_corrections_finance_id_foreign` (`finance_id`),
  CONSTRAINT `payroll_corrections_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_corrections_finance_id_foreign` FOREIGN KEY (`finance_id`) REFERENCES `finances` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_corrections_payroll_id_foreign` FOREIGN KEY (`payroll_id`) REFERENCES `payrolls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_corrections`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `payroll_corrections` WRITE;
/*!40000 ALTER TABLE `payroll_corrections` DISABLE KEYS */;
/*!40000 ALTER TABLE `payroll_corrections` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `payrolls`
--

DROP TABLE IF EXISTS `payrolls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payrolls` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `bulan` tinyint unsigned NOT NULL,
  `tahun` smallint unsigned NOT NULL,
  `gaji_harian` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_gaji_dasar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `bonus_datang_awal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_lembur` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_potongan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_kasbon` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_koreksi` decimal(15,2) NOT NULL DEFAULT '0.00',
  `take_home_pay` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_hadir` int unsigned NOT NULL DEFAULT '0',
  `total_terlambat` int unsigned NOT NULL DEFAULT '0',
  `total_izin` int unsigned NOT NULL DEFAULT '0',
  `total_cuti` int unsigned NOT NULL DEFAULT '0',
  `total_sakit` int unsigned NOT NULL DEFAULT '0',
  `potongan_terlambat` decimal(15,2) NOT NULL DEFAULT '0.00',
  `potongan_izin` decimal(15,2) NOT NULL DEFAULT '0.00',
  `potongan_cuti` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('draft','generated','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `payment_method` enum('cash','transfer') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `finance_id` bigint unsigned DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payrolls_employee_id_bulan_tahun_unique` (`employee_id`,`bulan`,`tahun`),
  KEY `payrolls_finance_id_foreign` (`finance_id`),
  CONSTRAINT `payrolls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payrolls_finance_id_foreign` FOREIGN KEY (`finance_id`) REFERENCES `finances` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payrolls`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `payrolls` WRITE;
/*!40000 ALTER TABLE `payrolls` DISABLE KEYS */;
/*!40000 ALTER TABLE `payrolls` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES
(3,'App\\Models\\Employee',1,'employee-face-token','c63b6f98dd005dfd9ceeffee82399d7f7dbc7f167ab63d732b673a4fc186fa90','[\"*\"]','2026-08-12 10:10:57',NULL,'2026-08-12 10:10:56','2026-08-12 10:10:57');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `supervisors`
--

DROP TABLE IF EXISTS `supervisors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `supervisors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supervisors_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supervisors`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `supervisors` WRITE;
/*!40000 ALTER TABLE `supervisors` DISABLE KEYS */;
INSERT INTO `supervisors` VALUES
(1,'Supervisor 1','supervisor1@gmail.com',NULL,'$2y$12$I1KV6/q2HopeztJiCdiOSOwm4PiU4hDpAJgqaIMG9Vzq2/wLUSTU.',1,'2026-08-12 10:08:45','2026-08-12 10:08:45');
/*!40000 ALTER TABLE `supervisors` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `task_assignments`
--

DROP TABLE IF EXISTS `task_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assigned_by` bigint unsigned NOT NULL,
  `team_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `deadline` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `completion_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_assignments_assigned_by_index` (`assigned_by`),
  KEY `task_assignments_team_id_index` (`team_id`),
  KEY `task_assignments_status_index` (`status`),
  KEY `task_assignments_deadline_index` (`deadline`),
  CONSTRAINT `task_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_assignments_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_assignments`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `task_assignments` WRITE;
/*!40000 ALTER TABLE `task_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_assignments` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `team_members`
--

DROP TABLE IF EXISTS `team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned NOT NULL,
  `member_id` bigint unsigned NOT NULL,
  `member_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team_members_team_id_member_id_member_type_unique` (`team_id`,`member_id`,`member_type`),
  KEY `team_members_member_type_member_id_index` (`member_type`,`member_id`),
  KEY `team_members_team_id_index` (`team_id`),
  CONSTRAINT `team_members_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_members`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `team_members` WRITE;
/*!40000 ALTER TABLE `team_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_members` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `teams` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teams_created_by_index` (`created_by`),
  KEY `teams_is_active_index` (`is_active`),
  CONSTRAINT `teams_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Super Admin','superadmin@gmail.com','$2y$12$Xqbn3FjQN0BoX16cl6DIGuvf7kphAN0SwP7TbPj9Ug5zAgttSPuJq',NULL,'2026-08-12 10:08:44','2026-08-12 10:08:44');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Multi-role face template tables added 2026-08-12
--
DROP TABLE IF EXISTS `user_face_templates`;
CREATE TABLE `user_face_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `embedding` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `engine` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'insightface',
  `engine_version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `embedding_dimension` smallint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `enrolled_at` timestamp NULL DEFAULT NULL,
  `last_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_face_templates_user_id_unique` (`user_id`),
  CONSTRAINT `user_face_templates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `supervisor_face_templates`;
CREATE TABLE `supervisor_face_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supervisor_id` bigint unsigned NOT NULL,
  `embedding` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `engine` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'insightface',
  `engine_version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `embedding_dimension` smallint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `enrolled_at` timestamp NULL DEFAULT NULL,
  `last_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supervisor_face_templates_supervisor_id_unique` (`supervisor_id`),
  CONSTRAINT `supervisor_face_templates_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `supervisors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `finance_face_templates`;
CREATE TABLE `finance_face_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `finance_id` bigint unsigned NOT NULL,
  `embedding` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `engine` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'insightface',
  `engine_version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `embedding_dimension` smallint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `enrolled_at` timestamp NULL DEFAULT NULL,
  `last_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `finance_face_templates_finance_id_unique` (`finance_id`),
  CONSTRAINT `finance_face_templates_finance_id_foreign` FOREIGN KEY (`finance_id`) REFERENCES `finances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Dumping routines for database 'hr_pay'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-08-12 17:13:36
