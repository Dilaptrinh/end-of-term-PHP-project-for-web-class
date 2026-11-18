-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: phongtap
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
-- Current Database: `phongtap`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `phongtap` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `phongtap`;

--
-- Table structure for table `chuyen_mon`
--

DROP TABLE IF EXISTS `chuyen_mon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chuyen_mon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ten_chuyen_mon` varchar(100) NOT NULL,
  `trang_thai` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_chuyen_mon_ten` (`ten_chuyen_mon`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chuyen_mon`
--

LOCK TABLES `chuyen_mon` WRITE;
/*!40000 ALTER TABLE `chuyen_mon` DISABLE KEYS */;
INSERT INTO `chuyen_mon` VALUES (1,'Yoga',1),(2,'Pilates',1),(3,'HIIT',1),(4,'Boxing',1),(5,'Gym & Strength',1),(6,'Zumba',1),(7,'Cardio',1);
/*!40000 ALTER TABLE `chuyen_mon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dang_ky_goi`
--

DROP TABLE IF EXISTS `dang_ky_goi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dang_ky_goi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hoi_vien_id` int(10) unsigned NOT NULL,
  `goi_tap_id` int(10) unsigned NOT NULL,
  `ngay_bat_dau` date NOT NULL,
  `ngay_ket_thuc` date NOT NULL,
  `gia_tai_thoi_diem` bigint(20) NOT NULL,
  `trang_thai` varchar(30) NOT NULL DEFAULT 'chua_thanh_toan',
  `phuong_thuc` varchar(30) DEFAULT NULL,
  `ngay_thanh_toan` datetime DEFAULT NULL,
  `nhan_vien_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_dang_ky_goi_hoi_vien` (`hoi_vien_id`),
  KEY `fk_dang_ky_goi_goi_tap` (`goi_tap_id`),
  KEY `fk_dang_ky_goi_nhan_vien` (`nhan_vien_id`),
  CONSTRAINT `fk_dang_ky_goi_goi_tap` FOREIGN KEY (`goi_tap_id`) REFERENCES `goi_tap` (`id`),
  CONSTRAINT `fk_dang_ky_goi_hoi_vien` FOREIGN KEY (`hoi_vien_id`) REFERENCES `hoi_vien` (`id`),
  CONSTRAINT `fk_dang_ky_goi_nhan_vien` FOREIGN KEY (`nhan_vien_id`) REFERENCES `tai_khoan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dang_ky_goi`
--

LOCK TABLES `dang_ky_goi` WRITE;
/*!40000 ALTER TABLE `dang_ky_goi` DISABLE KEYS */;
INSERT INTO `dang_ky_goi` VALUES (1,1,3,'2026-08-12','2027-02-12',2100000,'da_thanh_toan','Chuyển khoản','2026-08-12 09:14:00',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(2,2,2,'2026-09-20','2026-12-20',1200000,'da_thanh_toan','tien_mat','2026-11-17 17:54:50',NULL,'2026-11-15 09:32:11','2026-11-17 17:54:50'),(3,3,3,'2026-07-05','2027-01-05',2100000,'da_thanh_toan','Tiền mặt','2026-07-05 16:40:00',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(4,4,1,'2026-10-01','2026-11-01',500000,'da_thanh_toan','tien_mat','2026-11-17 17:54:56',NULL,'2026-11-15 09:32:11','2026-11-17 17:54:56'),(5,7,3,'2026-09-22','2027-03-22',2100000,'da_thanh_toan','Thẻ','2026-09-22 10:05:00',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(6,8,3,'2026-10-02','2026-11-02',2100000,'da_thanh_toan','Chuyển khoản','2026-10-02 14:22:00',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(7,6,1,'2026-06-10','2026-07-10',500000,'da_huy',NULL,NULL,NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(8,9,1,'2026-10-15','2026-11-14',500000,'da_thanh_toan','Tiền mặt','2026-10-15 08:30:00',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(9,10,2,'2026-10-20','2027-01-18',1200000,'da_thanh_toan','Chuyển khoản','2026-10-20 11:10:00',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(10,11,3,'2026-11-01','2026-12-30',2100000,'da_thanh_toan','Tiền mặt','2026-11-01 09:00:00',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(11,12,1,'2026-11-10','2026-11-16',500000,'chua_thanh_toan',NULL,NULL,NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(12,13,2,'2026-08-01','2026-10-30',1200000,'da_huy',NULL,NULL,NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(13,14,3,'2026-11-05','2026-12-04',2100000,'chua_thanh_toan',NULL,NULL,NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(14,1,1,'2026-11-17','2026-12-16',500000,'chua_thanh_toan',NULL,NULL,NULL,'2026-11-17 14:51:24','2026-11-17 14:51:24'),(15,1,2,'2026-11-17','2027-02-14',1200000,'chua_thanh_toan',NULL,NULL,NULL,'2026-11-17 15:09:51','2026-11-17 15:09:51');
/*!40000 ALTER TABLE `dang_ky_goi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dang_ky_lop`
--

DROP TABLE IF EXISTS `dang_ky_lop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dang_ky_lop` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hoi_vien_id` int(10) unsigned NOT NULL,
  `lop_hoc_id` int(10) unsigned NOT NULL,
  `ngay_dang_ky` date NOT NULL,
  `trang_thai` varchar(20) NOT NULL DEFAULT 'dang_ky',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dang_ky_lop` (`hoi_vien_id`,`lop_hoc_id`),
  KEY `fk_dang_ky_lop_lop_hoc` (`lop_hoc_id`),
  CONSTRAINT `fk_dang_ky_lop_hoi_vien` FOREIGN KEY (`hoi_vien_id`) REFERENCES `hoi_vien` (`id`),
  CONSTRAINT `fk_dang_ky_lop_lop_hoc` FOREIGN KEY (`lop_hoc_id`) REFERENCES `lop_hoc` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dang_ky_lop`
--

LOCK TABLES `dang_ky_lop` WRITE;
/*!40000 ALTER TABLE `dang_ky_lop` DISABLE KEYS */;
INSERT INTO `dang_ky_lop` VALUES (1,1,1,'2026-09-02','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(2,1,2,'2026-09-05','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(3,3,1,'2026-09-03','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(4,4,4,'2026-09-06','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(5,2,6,'2026-09-14','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(6,5,3,'2026-09-08','da_huy','2026-11-15 09:32:11','2026-11-15 09:32:11'),(7,7,5,'2026-09-01','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(8,8,3,'2026-09-10','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(9,9,10,'2026-11-02','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(10,9,12,'2026-11-03','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(11,10,11,'2026-11-04','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(12,11,13,'2026-11-05','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(13,12,14,'2026-11-06','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(14,13,15,'2026-11-07','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(15,14,16,'2026-11-08','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(16,15,17,'2026-11-09','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(17,16,1,'2026-11-01','da_huy','2026-11-15 09:32:11','2026-11-15 09:32:11'),(18,17,3,'2026-11-02','da_huy','2026-11-15 09:32:11','2026-11-15 09:32:11'),(19,18,9,'2026-10-02','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(20,19,9,'2026-10-03','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(21,20,9,'2026-10-04','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(22,21,9,'2026-10-05','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(23,22,9,'2026-10-06','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(24,18,19,'2026-06-02','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(25,19,19,'2026-06-03','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(26,20,19,'2026-06-04','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(27,21,19,'2026-06-05','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(28,22,19,'2026-06-06','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(29,23,19,'2026-06-07','da_huy','2026-11-15 09:32:11','2026-11-15 09:32:11'),(30,24,20,'2026-07-02','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(31,25,20,'2026-07-03','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(32,26,20,'2026-07-04','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(33,27,20,'2026-07-05','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(34,28,20,'2026-07-06','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(35,29,20,'2026-07-07','da_huy','2026-11-15 09:32:11','2026-11-15 09:32:11'),(36,30,21,'2026-06-16','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(37,31,21,'2026-06-17','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(38,32,21,'2026-06-18','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(39,33,21,'2026-06-19','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(40,34,21,'2026-06-20','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(41,35,21,'2026-06-21','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(42,36,22,'2026-07-02','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(43,37,22,'2026-07-03','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(44,38,22,'2026-07-04','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(45,39,22,'2026-07-05','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(46,40,22,'2026-07-06','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(47,41,22,'2026-07-07','da_huy','2026-11-15 09:32:11','2026-11-15 09:32:11'),(48,42,23,'2026-07-02','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(49,43,23,'2026-07-03','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(50,44,23,'2026-07-04','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(51,45,23,'2026-07-05','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(52,46,23,'2026-07-06','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(53,47,24,'2026-07-02','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(54,48,24,'2026-07-03','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(55,49,24,'2026-07-04','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(56,50,24,'2026-07-05','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(57,18,24,'2026-07-06','dang_ky','2026-11-15 09:32:11','2026-11-15 09:32:11'),(58,1,11,'2026-11-17','dang_ky','2026-11-17 14:38:20','2026-11-17 14:38:20'),(59,1,6,'2026-11-17','dang_ky','2026-11-17 14:50:37','2026-11-17 14:50:37'),(60,1,4,'2026-11-17','dang_ky','2026-11-17 15:06:14','2026-11-17 15:06:14'),(61,1,3,'2026-11-17','dang_ky','2026-11-17 15:06:23','2026-11-17 15:06:23'),(62,1,13,'2026-11-17','dang_ky','2026-11-17 15:07:38','2026-11-17 15:07:38'),(63,1,17,'2026-11-17','dang_ky','2026-11-17 15:09:20','2026-11-17 15:09:20');
/*!40000 ALTER TABLE `dang_ky_lop` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doanh_thu`
--

DROP TABLE IF EXISTS `doanh_thu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doanh_thu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `thang` tinyint(4) NOT NULL,
  `nam` smallint(6) NOT NULL,
  `so_tien` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_doanh_thu_thang` (`thang`,`nam`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doanh_thu`
--

LOCK TABLES `doanh_thu` WRITE;
/*!40000 ALTER TABLE `doanh_thu` DISABLE KEYS */;
INSERT INTO `doanh_thu` VALUES (1,1,2026,42000000),(2,2,2026,38000000),(3,3,2026,51000000),(4,4,2026,47000000),(5,5,2026,58000000),(6,6,2026,63000000),(7,7,2026,55000000),(8,8,2026,71000000),(9,9,2026,78000000),(10,10,2026,86000000),(11,11,2026,0),(12,12,2026,0);
/*!40000 ALTER TABLE `doanh_thu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `giao_vien`
--

DROP TABLE IF EXISTS `giao_vien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `giao_vien` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tai_khoan_id` int(10) unsigned DEFAULT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `sdt` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `gioi_thieu` text DEFAULT NULL,
  `trang_thai` varchar(20) NOT NULL DEFAULT 'dang_lam',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_giao_vien_email` (`email`),
  UNIQUE KEY `uq_giao_vien_tai_khoan` (`tai_khoan_id`),
  CONSTRAINT `fk_giao_vien_tai_khoan` FOREIGN KEY (`tai_khoan_id`) REFERENCES `tai_khoan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `giao_vien`
--

LOCK TABLES `giao_vien` WRITE;
/*!40000 ALTER TABLE `giao_vien` DISABLE KEYS */;
INSERT INTO `giao_vien` VALUES (1,2,'Trần Thu Hà','0911222333','thuha@fitzone.vn',NULL,'dang_lam',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(2,3,'Nguyễn Đức Anh','0912333444','ducanh@fitzone.vn',NULL,'dang_lam',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(3,4,'Lê Ngọc Mai','0913444555','ngocmai@fitzone.vn',NULL,'dang_lam',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(4,5,'Phạm Quốc Bảo','0914555666','quocbao@fitzone.vn',NULL,'dang_lam',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(5,6,'Đặng Hồng Nhung','0915666777','hongnhung@fitzone.vn',NULL,'nghi',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(6,57,'Lê Hoàng Nam','0901234567','bnhutduydep@gmail.com',NULL,'dang_lam',NULL,'2026-11-15 10:23:53','2026-11-15 10:23:53');
/*!40000 ALTER TABLE `giao_vien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `giao_vien_chuyen_mon`
--

DROP TABLE IF EXISTS `giao_vien_chuyen_mon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `giao_vien_chuyen_mon` (
  `giao_vien_id` int(10) unsigned NOT NULL,
  `chuyen_mon_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`giao_vien_id`,`chuyen_mon_id`),
  KEY `fk_gvcm_chuyen_mon` (`chuyen_mon_id`),
  CONSTRAINT `fk_gvcm_chuyen_mon` FOREIGN KEY (`chuyen_mon_id`) REFERENCES `chuyen_mon` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_gvcm_giao_vien` FOREIGN KEY (`giao_vien_id`) REFERENCES `giao_vien` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `giao_vien_chuyen_mon`
--

LOCK TABLES `giao_vien_chuyen_mon` WRITE;
/*!40000 ALTER TABLE `giao_vien_chuyen_mon` DISABLE KEYS */;
INSERT INTO `giao_vien_chuyen_mon` VALUES (1,5),(2,3),(2,4),(3,2),(3,7),(4,5),(5,6);
/*!40000 ALTER TABLE `giao_vien_chuyen_mon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `goi_tap`
--

DROP TABLE IF EXISTS `goi_tap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `goi_tap` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ten_goi` varchar(100) NOT NULL,
  `thoi_han_ngay` int(11) NOT NULL,
  `gia` bigint(20) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `cap_do` varchar(20) NOT NULL DEFAULT 'basic',
  `trang_thai` varchar(20) NOT NULL DEFAULT 'dang_ban',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `goi_tap`
--

LOCK TABLES `goi_tap` WRITE;
/*!40000 ALTER TABLE `goi_tap` DISABLE KEYS */;
INSERT INTO `goi_tap` VALUES (1,'Basic 1 tháng',30,500000,'Truy cập phòng gym, tủ đồ, phòng tắm trong 30 ngày.','basic','dang_ban',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(2,'Standard 3 tháng',90,1200000,'Toàn bộ quyền lợi Basic + tham gia lớp cơ bản và trung cấp.','standard','dang_ban',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(3,'Premium 6 tháng',180,2100000,'Bao gồm Standard và được tham gia mọi lớp.','premium','dang_ban',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11');
/*!40000 ALTER TABLE `goi_tap` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hoi_vien`
--

DROP TABLE IF EXISTS `hoi_vien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hoi_vien` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tai_khoan_id` int(10) unsigned DEFAULT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `sdt` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `gioi_tinh` varchar(10) DEFAULT NULL,
  `ngay_sinh` date DEFAULT NULL,
  `dia_chi` varchar(255) DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `chieu_cao` decimal(5,1) DEFAULT NULL,
  `can_nang` decimal(5,1) DEFAULT NULL,
  `muc_tieu` varchar(100) DEFAULT NULL,
  `trang_thai` varchar(20) NOT NULL DEFAULT 'hoat_dong',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_hoi_vien_email` (`email`),
  KEY `fk_hoi_vien_tai_khoan` (`tai_khoan_id`),
  CONSTRAINT `fk_hoi_vien_tai_khoan` FOREIGN KEY (`tai_khoan_id`) REFERENCES `tai_khoan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hoi_vien`
--

LOCK TABLES `hoi_vien` WRITE;
/*!40000 ALTER TABLE `hoi_vien` DISABLE KEYS */;
INSERT INTO `hoi_vien` VALUES (1,7,'Lê Hoàng Nam','0901234561','hoangnam@gmail.com','nam','1998-04-12',NULL,NULL,172.0,68.5,'Duy trì thể lực','hoat_dong',NULL,'2026-08-12 00:00:00','2026-11-17 15:10:11'),(2,8,'Phạm Anh Tuấn','0902345678','anhtuan@gmail.com','nam','2000-09-03',NULL,NULL,175.0,72.0,'Tăng cơ','hoat_dong',NULL,'2026-09-20 00:00:00','2026-11-15 09:32:11'),(3,9,'Nguyễn Thị Mai','0903456789','thimai@gmail.com','nu','1996-01-25',NULL,NULL,160.0,55.0,'Giảm cân','hoat_dong',NULL,'2026-07-05 00:00:00','2026-11-15 09:32:11'),(4,10,'Trần Quốc Huy','0904567890','quochuy@gmail.com','nam','1994-11-18',NULL,NULL,178.0,80.0,'Tăng sức bền','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(5,11,'Đỗ Thu Trang','0905678901','thutrang@gmail.com','nu','2001-06-30',NULL,NULL,158.0,52.0,'Dẻo dai','hoat_dong',NULL,'2026-08-15 00:00:00','2026-11-15 09:32:11'),(6,12,'Vũ Minh Khang','0906789012','minhkhang@gmail.com','nam','1999-02-14',NULL,NULL,176.0,75.0,'Giảm cân','ngung_hoat_dong',NULL,'2026-06-10 00:00:00','2026-11-15 09:32:11'),(7,13,'Bùi Ngọc Hân','0907890123','ngochan@gmail.com','nu','2002-12-08',NULL,NULL,157.0,50.0,'Sức khỏe','hoat_dong',NULL,'2026-09-22 00:00:00','2026-11-15 09:32:11'),(8,14,'Hoàng Gia Bảo','0908901234','giabao@gmail.com','nam','1997-03-21',NULL,NULL,173.0,70.0,'Tăng cơ','hoat_dong',NULL,'2026-10-02 00:00:00','2026-11-15 09:32:11'),(9,15,'Ngô Thanh Vân','0909012345','thanhvan@gmail.com','nu','1995-07-17',NULL,NULL,162.0,54.0,'Duy trì thể lực','hoat_dong',NULL,'2026-07-18 00:00:00','2026-11-15 09:32:11'),(10,16,'Lý Tuấn Kiệt','0910123456','tuankiet@gmail.com','nam','2003-05-05',NULL,NULL,170.0,66.0,'Tăng cân','ngung_hoat_dong',NULL,'2026-05-01 00:00:00','2026-11-15 09:32:11'),(11,17,'Hội viên mẫu 11','0980000011','hoivien11@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(12,18,'Hội viên mẫu 12','0980000012','hoivien12@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(13,19,'Hội viên mẫu 13','0980000013','hoivien13@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(14,20,'Hội viên mẫu 14','0980000014','hoivien14@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(15,21,'Hội viên mẫu 15','0980000015','hoivien15@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(16,22,'Hội viên mẫu 16','0980000016','hoivien16@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(17,23,'Hội viên mẫu 17','0980000017','hoivien17@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(18,24,'Hội viên mẫu 18','0980000018','hoivien18@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(19,25,'Hội viên mẫu 19','0980000019','hoivien19@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(20,26,'Hội viên mẫu 20','0980000020','hoivien20@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','ngung_hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(21,27,'Hội viên mẫu 21','0980000021','hoivien21@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(22,28,'Hội viên mẫu 22','0980000022','hoivien22@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(23,29,'Hội viên mẫu 23','0980000023','hoivien23@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(24,30,'Hội viên mẫu 24','0980000024','hoivien24@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(25,31,'Hội viên mẫu 25','0980000025','hoivien25@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(26,32,'Hội viên mẫu 26','0980000026','hoivien26@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(27,33,'Hội viên mẫu 27','0980000027','hoivien27@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(28,34,'Hội viên mẫu 28','0980000028','hoivien28@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(29,35,'Hội viên mẫu 29','0980000029','hoivien29@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(30,36,'Hội viên mẫu 30','0980000030','hoivien30@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','ngung_hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(31,37,'Hội viên mẫu 31','0980000031','hoivien31@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(32,38,'Hội viên mẫu 32','0980000032','hoivien32@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(33,39,'Hội viên mẫu 33','0980000033','hoivien33@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(34,40,'Hội viên mẫu 34','0980000034','hoivien34@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(35,41,'Hội viên mẫu 35','0980000035','hoivien35@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(36,42,'Hội viên mẫu 36','0980000036','hoivien36@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(37,43,'Hội viên mẫu 37','0980000037','hoivien37@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(38,44,'Hội viên mẫu 38','0980000038','hoivien38@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(39,45,'Hội viên mẫu 39','0980000039','hoivien39@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(40,46,'Hội viên mẫu 40','0980000040','hoivien40@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','ngung_hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(41,47,'Hội viên mẫu 41','0980000041','hoivien41@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(42,48,'Hội viên mẫu 42','0980000042','hoivien42@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(43,49,'Hội viên mẫu 43','0980000043','hoivien43@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(44,50,'Hội viên mẫu 44','0980000044','hoivien44@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(45,51,'Hội viên mẫu 45','0980000045','hoivien45@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(46,52,'Hội viên mẫu 46','0980000046','hoivien46@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(47,53,'Hội viên mẫu 47','0980000047','hoivien47@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(48,54,'Hội viên mẫu 48','0980000048','hoivien48@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(49,55,'Hội viên mẫu 49','0980000049','hoivien49@fitzone.vn','nam','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(50,56,'Hội viên mẫu 50','0980000050','hoivien50@fitzone.vn','nu','1995-01-01',NULL,NULL,165.0,60.0,'Duy trì thể lực','ngung_hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-15 09:32:11'),(54,62,'Lê Hoàng Nam','0901234567','bnhutduydep@gmail.com','khac',NULL,NULL,NULL,NULL,NULL,NULL,'hoat_dong',NULL,'2026-11-17 17:16:17','2026-11-17 17:16:17');
/*!40000 ALTER TABLE `hoi_vien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lop_hoc`
--

DROP TABLE IF EXISTS `lop_hoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lop_hoc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ten_lop` varchar(100) NOT NULL,
  `giao_vien_id` int(10) unsigned NOT NULL,
  `thu_trong_tuan` tinyint(4) NOT NULL,
  `gio_bat_dau` time NOT NULL,
  `gio_ket_thuc` time NOT NULL,
  `ngay_bat_dau` date NOT NULL,
  `ngay_ket_thuc` date NOT NULL,
  `suc_chua` int(11) NOT NULL,
  `phong_hoc` varchar(50) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `trinh_do` varchar(50) DEFAULT NULL,
  `goi_tap_toi_thieu` varchar(20) NOT NULL DEFAULT 'basic',
  `trang_thai` varchar(20) NOT NULL DEFAULT 'dang_mo',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_lop_hoc_giao_vien` (`giao_vien_id`),
  CONSTRAINT `fk_lop_hoc_giao_vien` FOREIGN KEY (`giao_vien_id`) REFERENCES `giao_vien` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lop_hoc`
--

LOCK TABLES `lop_hoc` WRITE;
/*!40000 ALTER TABLE `lop_hoc` DISABLE KEYS */;
INSERT INTO `lop_hoc` VALUES (1,'Yoga cơ bản',1,1,'18:00:00','19:00:00','2026-09-01','2026-11-30',24,'P.101','Làm quen hơi thở, tư thế nền tảng và thư giãn sâu.','Cơ bản','basic','dang_mo',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(2,'HIIT đốt mỡ',2,2,'06:00:00','07:00:00','2026-09-01','2026-12-31',16,'P.202','Bài tập cường độ cao ngắt quãng, đốt cháy calo tối đa.','Trung cấp','standard','dang_mo',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(3,'Gym & Strength',4,3,'17:30:00','18:30:00','2026-09-05','2026-12-30',20,'Khu A','Kỹ thuật nâng tạ cơ bản, xây dựng sức mạnh toàn thân.','Cơ bản','basic','dang_mo',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(4,'Zumba Dance',1,4,'19:00:00','20:00:00','2026-09-01','2026-11-30',30,'P.101','Kết hợp âm nhạc và vũ đạo Latin, đốt calo vui vẻ.','Mọi trình độ','basic','dang_mo',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(5,'Pilates kiểm soát',3,5,'07:00:00','08:00:00','2026-09-01','2026-10-31',15,'P.103','Tăng cường core, cải thiện tư thế và độ dẻo dai.','Trung cấp','standard','dang_mo',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(6,'Boxing cơ bản',2,6,'08:00:00','09:00:00','2026-09-12','2026-12-30',18,'Khu B','Làm quen kỹ thuật đấm, di chuyển và phản xạ.','Cơ bản','basic','dang_mo',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(7,'Yoga nâng cao',1,6,'09:30:00','10:30:00','2026-09-01','2026-11-30',20,'P.101','Chuỗi tư thế nâng cao, giữ thăng bằng và kiểm soát hơi thở.','Nâng cao','premium','dang_mo',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(8,'Cardio & Core',3,7,'16:00:00','17:00:00','2026-09-01','2026-12-31',25,'P.202','Cardio nhẹ nhàng kết hợp bài tập săn chắc vùng bụng.','Mọi trình độ','basic','dang_mo',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(9,'Gym nâng cao',4,5,'18:00:00','19:30:00','2026-10-01','2026-12-31',12,'Khu A','Chương trình sức mạnh chuyên sâu cho hội viên lâu năm.','Nâng cao','premium','da_dong',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(10,'Mobility & Stretching',3,1,'07:30:00','08:30:00','2026-11-01','2027-01-31',20,'P.103','Giãn cơ và cải thiện khả năng vận động mỗi ngày.','Cơ bản','basic','dang_mo',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(11,'Kickboxing Fitness',2,2,'18:30:00','19:30:00','2026-11-01','2027-01-31',18,'Khu B','Kết hợp kỹ thuật kickboxing với bài tập thể lực.','Trung cấp','standard','dang_mo',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(12,'Pilates phục hồi',3,3,'09:00:00','10:00:00','2026-11-01','2027-01-31',16,'P.103','Bài tập nhẹ giúp phục hồi và tăng sức mạnh vùng core.','Cơ bản','basic','dang_mo',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(13,'Dance Cardio',1,4,'17:30:00','18:30:00','2026-11-01','2027-01-31',28,'P.101','Vận động theo nhạc, phù hợp với người mới bắt đầu.','Mọi trình độ','basic','dang_mo',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(14,'Functional Training',4,5,'19:00:00','20:00:00','2026-11-01','2027-01-31',15,'Khu A','Rèn sức mạnh và khả năng vận động trong đời sống.','Trung cấp','standard','dang_mo',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(15,'Boxing nâng cao',2,6,'17:00:00','18:30:00','2026-11-01','2027-01-31',12,'Khu B','Nâng cao kỹ thuật boxing và sức bền thi đấu.','Nâng cao','premium','dang_mo',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(16,'Thiền và hít thở',5,7,'08:00:00','09:00:00','2026-11-01','2027-01-31',20,'P.102','Thư giãn tinh thần, điều hòa hơi thở và giảm căng thẳng.','Cơ bản','basic','dang_mo',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(17,'Yoga trị liệu',1,2,'09:00:00','10:00:00','2026-11-15','2027-02-15',10,'P.101','Lớp chuyên sâu đã được admin duyệt, bắt đầu từ 15/11.','Nâng cao','premium','dang_mo',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(18,'Bootcamp cuối tuần',4,7,'16:00:00','17:30:00','2026-11-15','2027-02-15',25,'Sân ngoài trời','Lớp ngoài trời đã được admin duyệt, bắt đầu từ 15/11.','Trung cấp','standard','dang_mo',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(19,'Yoga Morning (finished)',1,2,'06:30:00','07:30:00','2026-06-01','2026-08-31',20,'P.101','Yoga morning class finished.','Beginner','basic','da_dong',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(20,'Pilates Basic (finished)',1,4,'18:00:00','19:00:00','2026-07-01','2026-09-30',18,'P.103','Pilates basic class finished.','Beginner','basic','da_dong',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(21,'HIIT Morning (finished)',2,3,'06:30:00','07:30:00','2026-06-15','2026-09-15',16,'P.202','HIIT morning class finished.','Intermediate','basic','da_dong',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(22,'Cardio Light (finished)',3,6,'08:30:00','09:30:00','2026-07-01','2026-10-15',22,'P.103','Cardio light class finished.','Beginner','basic','da_dong',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(23,'Strength Basic (finished)',4,5,'17:00:00','18:00:00','2026-06-01','2026-09-30',14,'Khu A','Strength basic class finished.','Beginner','basic','da_dong',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(24,'Zumba Weekend (finished)',5,7,'09:00:00','10:00:00','2026-07-05','2026-10-05',25,'P.101','Zumba weekend class finished.','Beginner','basic','da_dong',NULL,'2026-11-15 09:32:11','2026-11-15 09:32:11'),(25,'Yoga phục hồi',1,2,'07:00:00','08:00:00','2026-12-01','2027-02-28',15,NULL,'Lớp yoga nhẹ giúp phục hồi cơ thể và giảm căng thẳng.','Cơ bản','basic','da_huy',NULL,'2026-11-15 19:32:30','2026-11-15 21:52:35'),(26,'HIIT bùng nổ',2,4,'18:00:00','19:00:00','2026-12-01','2027-02-28',18,NULL,'Bài HIIT cường độ cao, đốt mỡ và tăng sức bền.','Nâng cao','premium','dang_mo',NULL,'2026-11-15 19:32:30','2026-11-18 20:10:33'),(27,'Pilates core',3,6,'09:00:00','10:00:00','2026-12-05','2027-03-05',16,NULL,'Tăng sức mạnh vùng core và cải thiện tư thế.','Cơ bản','basic','dang_mo',NULL,'2026-11-15 19:32:30','2026-11-18 20:10:33'),(28,'Gym sức mạnh',4,3,'17:30:00','18:30:00','2026-12-02','2027-02-28',20,NULL,'Giáo án sức mạnh toàn thân với tạ và bài tập bổ trợ.','Nâng cao','premium','dang_mo',NULL,'2026-11-15 19:32:30','2026-11-18 20:10:33'),(29,'Zumba năng lượng',5,5,'19:00:00','20:00:00','2026-12-04','2027-03-04',25,NULL,'Lớp Zumba sôi động kết hợp vũ đạo Latin và cardio.','Mọi trình độ','basic','dang_mo',NULL,'2026-11-15 19:32:30','2026-11-18 20:10:33'),(30,'cơ bản',1,1,'18:00:00','19:00:00','2026-10-26','2026-12-31',20,'232',NULL,'Cơ bản','basic','dang_mo',NULL,'2026-11-17 14:20:59','2026-11-17 14:20:59');
/*!40000 ALTER TABLE `lop_hoc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tai_khoan`
--

DROP TABLE IF EXISTS `tai_khoan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tai_khoan` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ten_dang_nhap` varchar(50) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `vai_tro` varchar(20) NOT NULL DEFAULT 'customer',
  `trang_thai` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tai_khoan_ten` (`ten_dang_nhap`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tai_khoan`
--

LOCK TABLES `tai_khoan` WRITE;
/*!40000 ALTER TABLE `tai_khoan` DISABLE KEYS */;
INSERT INTO `tai_khoan` VALUES (1,'admin','$2y$10$fjjcNZOYIeL.dR3xwQ//ROqaxu.xEcCgIzTMhiHrFcb5n1eY41DJ.','Quản trị viên','admin',1,'2026-11-15 09:32:11'),(2,'thuha','$2y$10$Qqdm.5JyHsu1XeuB6gS6xumm2uS50hNqx3ij8B7tzcTev0t7ntxDq','Trần Thu Hà','instructor',1,'2026-11-15 09:32:11'),(3,'ducanh','$2y$10$sTtxLbaadJKY60DqKAAjLeFhPALQeHCSrLYYBFfl/tEBwx088Fr9q','Nguyễn Đức Anh','instructor',1,'2026-11-15 09:32:11'),(4,'ngocmai','$2y$10$xToWVQP.GZ/Y2uOuw4K9B.9QKhtIimyPMh6JydXJwW9NLpsNRVwD6','Lê Ngọc Mai','instructor',1,'2026-11-15 09:32:11'),(5,'quocbao','$2y$10$XGG595KvKeDeOd0te1aFWOyQK8c8.Abx3tt366ZVKx4lkOhIlbMfC','Phạm Quốc Bảo','instructor',1,'2026-11-15 09:32:11'),(6,'hongnhung','$2y$10$cvCrHclk3kLVp6WITUHFbelE7iZ1IbtHF/S80jRdxPQ62aIwOxmYK','Đặng Hồng Nhung','instructor',1,'2026-11-15 09:32:11'),(7,'hoangnam','$2y$10$cfVLJQhHA0nIplLJ/p5KkeEkzT76x2n9F1Rp8ZMkCkNP7UiffxxZ6','Lê Hoàng Nam','customer',1,'2026-11-15 09:32:11'),(8,'hoivien02','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Phạm Anh Tuấn','customer',1,'2026-11-15 09:32:11'),(9,'hoivien03','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Nguyễn Thị Mai','customer',1,'2026-11-15 09:32:11'),(10,'hoivien04','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Trần Quốc Huy','customer',1,'2026-11-15 09:32:11'),(11,'hoivien05','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Đỗ Thu Trang','customer',1,'2026-11-15 09:32:11'),(12,'hoivien06','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Vũ Minh Khang','customer',1,'2026-11-15 09:32:11'),(13,'hoivien07','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Bùi Ngọc Hân','customer',1,'2026-11-15 09:32:11'),(14,'hoivien08','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hoàng Gia Bảo','customer',1,'2026-11-15 09:32:11'),(15,'hoivien09','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Ngô Thanh Vân','customer',1,'2026-11-15 09:32:11'),(16,'hoivien10','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Lý Tuấn Kiệt','customer',1,'2026-11-15 09:32:11'),(17,'hoivien11','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 11','customer',1,'2026-11-15 09:32:11'),(18,'hoivien12','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 12','customer',1,'2026-11-15 09:32:11'),(19,'hoivien13','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 13','customer',1,'2026-11-15 09:32:11'),(20,'hoivien14','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 14','customer',1,'2026-11-15 09:32:11'),(21,'hoivien15','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 15','customer',1,'2026-11-15 09:32:11'),(22,'hoivien16','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 16','customer',1,'2026-11-15 09:32:11'),(23,'hoivien17','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 17','customer',1,'2026-11-15 09:32:11'),(24,'hoivien18','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 18','customer',1,'2026-11-15 09:32:11'),(25,'hoivien19','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 19','customer',1,'2026-11-15 09:32:11'),(26,'hoivien20','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 20','customer',1,'2026-11-15 09:32:11'),(27,'hoivien21','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 21','customer',1,'2026-11-15 09:32:11'),(28,'hoivien22','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 22','customer',1,'2026-11-15 09:32:11'),(29,'hoivien23','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 23','customer',1,'2026-11-15 09:32:11'),(30,'hoivien24','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 24','customer',1,'2026-11-15 09:32:11'),(31,'hoivien25','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 25','customer',1,'2026-11-15 09:32:11'),(32,'hoivien26','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 26','customer',1,'2026-11-15 09:32:11'),(33,'hoivien27','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 27','customer',1,'2026-11-15 09:32:11'),(34,'hoivien28','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 28','customer',1,'2026-11-15 09:32:11'),(35,'hoivien29','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 29','customer',1,'2026-11-15 09:32:11'),(36,'hoivien30','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 30','customer',1,'2026-11-15 09:32:11'),(37,'hoivien31','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 31','customer',1,'2026-11-15 09:32:11'),(38,'hoivien32','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 32','customer',1,'2026-11-15 09:32:11'),(39,'hoivien33','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 33','customer',1,'2026-11-15 09:32:11'),(40,'hoivien34','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 34','customer',1,'2026-11-15 09:32:11'),(41,'hoivien35','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 35','customer',1,'2026-11-15 09:32:11'),(42,'hoivien36','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 36','customer',1,'2026-11-15 09:32:11'),(43,'hoivien37','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 37','customer',1,'2026-11-15 09:32:11'),(44,'hoivien38','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 38','customer',1,'2026-11-15 09:32:11'),(45,'hoivien39','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 39','customer',1,'2026-11-15 09:32:11'),(46,'hoivien40','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 40','customer',1,'2026-11-15 09:32:11'),(47,'hoivien41','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 41','customer',1,'2026-11-15 09:32:11'),(48,'hoivien42','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 42','customer',1,'2026-11-15 09:32:11'),(49,'hoivien43','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 43','customer',1,'2026-11-15 09:32:11'),(50,'hoivien44','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 44','customer',1,'2026-11-15 09:32:11'),(51,'hoivien45','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 45','customer',1,'2026-11-15 09:32:11'),(52,'hoivien46','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 46','customer',1,'2026-11-15 09:32:11'),(53,'hoivien47','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 47','customer',1,'2026-11-15 09:32:11'),(54,'hoivien48','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 48','customer',1,'2026-11-15 09:32:11'),(55,'hoivien49','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 49','customer',1,'2026-11-15 09:32:11'),(56,'hoivien50','$2y$10$0Iw/ZtGb6lxw4Xb03PJ/c.9/bVTv.KwCW7b074mhg7mIo6HAsRkM.','Hội viên mẫu 50','customer',1,'2026-11-15 09:32:11'),(57,'namm','$2y$10$R8je6wc6/i5kzZv5h.FTMeYW52esJCBuUCUP2tuoGAcU9xqVtcQbu','Lê Hoàng Nam','instructor',1,'2026-11-15 10:23:53'),(62,'nammm','$2y$10$BEi8em8ioFzWVaz8w52GLe8qLAT1y38XqZ5unYb5biO0wCld69Kbm','Lê Hoàng Nam','customer',1,'2026-11-17 17:16:17');
/*!40000 ALTER TABLE `tai_khoan` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-18 20:11:30
