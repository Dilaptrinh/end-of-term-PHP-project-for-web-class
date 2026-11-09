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
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dang_ky_goi`
--

LOCK TABLES `dang_ky_goi` WRITE;
/*!40000 ALTER TABLE `dang_ky_goi` DISABLE KEYS */;
INSERT INTO `dang_ky_goi` VALUES (1,1,3,'2026-08-12','2027-02-12',2100000,'da_thanh_toan','Chuyển khoản','2026-08-12 09:14:00',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(2,2,2,'2026-09-20','2026-12-20',1200000,'chua_thanh_toan',NULL,NULL,NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(3,3,3,'2026-07-05','2027-01-05',2100000,'da_thanh_toan','Tiền mặt','2026-07-05 16:40:00',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(4,4,1,'2026-10-01','2026-11-01',500000,'chua_thanh_toan',NULL,NULL,NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(5,7,3,'2026-09-22','2027-03-22',2100000,'da_thanh_toan','Thẻ','2026-09-22 10:05:00',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(6,8,4,'2026-10-02','2026-11-02',3000000,'da_thanh_toan','Chuyển khoản','2026-10-02 14:22:00',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(7,6,1,'2026-06-10','2026-07-10',500000,'da_huy',NULL,NULL,NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31');
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dang_ky_lop`
--

LOCK TABLES `dang_ky_lop` WRITE;
/*!40000 ALTER TABLE `dang_ky_lop` DISABLE KEYS */;
INSERT INTO `dang_ky_lop` VALUES (1,1,1,'2026-09-02','dang_ky','2026-11-09 08:50:31','2026-11-09 08:50:31'),(2,1,2,'2026-09-05','dang_ky','2026-11-09 08:50:31','2026-11-09 08:50:31'),(3,3,1,'2026-09-03','dang_ky','2026-11-09 08:50:31','2026-11-09 08:50:31'),(4,4,4,'2026-09-06','dang_ky','2026-11-09 08:50:31','2026-11-09 08:50:31'),(5,2,6,'2026-09-14','dang_ky','2026-11-09 08:50:31','2026-11-09 08:50:31'),(6,5,3,'2026-09-08','da_huy','2026-11-09 08:50:31','2026-11-09 08:50:31'),(7,7,5,'2026-09-01','dang_ky','2026-11-09 08:50:31','2026-11-09 08:50:31'),(8,8,3,'2026-09-10','dang_ky','2026-11-09 08:50:31','2026-11-09 08:50:31');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `giao_vien`
--

LOCK TABLES `giao_vien` WRITE;
/*!40000 ALTER TABLE `giao_vien` DISABLE KEYS */;
INSERT INTO `giao_vien` VALUES (1,2,'Trần Thu Hà','0911222333','thuha@fitzone.vn',NULL,'dang_lam',NULL,'2026-11-09 08:50:30','2026-11-09 08:50:32'),(2,3,'Nguyễn Đức Anh','0912333444','ducanh@fitzone.vn',NULL,'dang_lam',NULL,'2026-11-09 08:50:30','2026-11-09 08:50:32'),(3,4,'Lê Ngọc Mai','0913444555','ngocmai@fitzone.vn',NULL,'dang_lam',NULL,'2026-11-09 08:50:30','2026-11-09 08:50:32'),(4,5,'Phạm Quốc Bảo','0914555666','quocbao@fitzone.vn',NULL,'dang_lam',NULL,'2026-11-09 08:50:30','2026-11-09 08:50:32'),(5,6,'Đặng Hồng Nhung','0915666777','hongnhung@fitzone.vn',NULL,'nghi',NULL,'2026-11-09 08:50:30','2026-11-09 08:50:32');
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
INSERT INTO `giao_vien_chuyen_mon` VALUES (1,1),(1,2),(2,3),(2,4),(3,2),(3,7),(4,5),(5,6);
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
INSERT INTO `goi_tap` VALUES (1,'Basic 1 tháng',30,500000,'Truy cập phòng gym, tủ đồ, phòng tắm trong 30 ngày.','dang_ban',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(2,'Standard 3 tháng',90,1200000,'Toàn bộ quyền lợi Basic + tham gia lớp nhóm không giới hạn.','dang_ban',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(3,'Premium 6 tháng',180,2100000,'Bao gồm Standard + 1 buổi PT/tháng và tư vấn dinh dưỡng.','dang_ban',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(4,'PT 1-1 (10 buổi)',30,3000000,'10 buổi tập cá nhân cùng huấn luyện viên chuyên trách.','dang_ban',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(5,'Basic 1 tháng (cũ)',30,450000,'Gói đã ngừng bán, giữ lại cho lịch sử.','ngung_ban',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31');
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hoi_vien`
--

LOCK TABLES `hoi_vien` WRITE;
/*!40000 ALTER TABLE `hoi_vien` DISABLE KEYS */;
INSERT INTO `hoi_vien` VALUES (1,7,'Lê Hoàng Nam','0901234567','hoangnam@gmail.com','nam','1998-04-12',NULL,NULL,172.0,68.5,'Duy trì thể lực','hoat_dong',NULL,'2026-08-12 00:00:00','2026-11-09 08:50:32'),(2,NULL,'Phạm Anh Tuấn','0902345678','anhtuan@gmail.com','nam','2000-09-03',NULL,NULL,175.0,72.0,'Tăng cơ','hoat_dong',NULL,'2026-09-20 00:00:00','2026-11-09 08:50:30'),(3,NULL,'Nguyễn Thị Mai','0903456789','thimai@gmail.com','nu','1996-01-25',NULL,NULL,160.0,55.0,'Giảm cân','hoat_dong',NULL,'2026-07-05 00:00:00','2026-11-09 08:50:30'),(4,NULL,'Trần Quốc Huy','0904567890','quochuy@gmail.com','nam','1994-11-18',NULL,NULL,178.0,80.0,'Tăng sức bền','hoat_dong',NULL,'2026-10-01 00:00:00','2026-11-09 08:50:30'),(5,NULL,'Đỗ Thu Trang','0905678901','thutrang@gmail.com','nu','2001-06-30',NULL,NULL,158.0,52.0,'Dẻo dai','hoat_dong',NULL,'2026-08-15 00:00:00','2026-11-09 08:50:30'),(6,NULL,'Vũ Minh Khang','0906789012','minhkhang@gmail.com','nam','1999-02-14',NULL,NULL,176.0,75.0,'Giảm cân','ngung_hoat_dong',NULL,'2026-06-10 00:00:00','2026-11-09 08:50:30'),(7,NULL,'Bùi Ngọc Hân','0907890123','ngochan@gmail.com','nu','2002-12-08',NULL,NULL,157.0,50.0,'Sức khỏe','hoat_dong',NULL,'2026-09-22 00:00:00','2026-11-09 08:50:30'),(8,NULL,'Hoàng Gia Bảo','0908901234','giabao@gmail.com','nam','1997-03-21',NULL,NULL,173.0,70.0,'Tăng cơ','hoat_dong',NULL,'2026-10-02 00:00:00','2026-11-09 08:50:30'),(9,NULL,'Ngô Thanh Vân','0909012345','thanhvan@gmail.com','nu','1995-07-17',NULL,NULL,162.0,54.0,'Duy trì thể lực','hoat_dong',NULL,'2026-07-18 00:00:00','2026-11-09 08:50:30'),(10,NULL,'Lý Tuấn Kiệt','0910123456','tuankiet@gmail.com','nam','2003-05-05',NULL,NULL,170.0,66.0,'Tăng cân','ngung_hoat_dong',NULL,'2026-05-01 00:00:00','2026-11-09 08:50:30');
/*!40000 ALTER TABLE `hoi_vien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lich_ranh`
--

DROP TABLE IF EXISTS `lich_ranh`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lich_ranh` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `giao_vien_id` int(10) unsigned NOT NULL,
  `ngay` date NOT NULL,
  `gio_bat_dau` time NOT NULL,
  `gio_ket_thuc` time NOT NULL,
  `trang_thai` varchar(20) NOT NULL DEFAULT 'cho_duyet',
  `ghi_chu` varchar(255) DEFAULT NULL,
  `nguoi_duyet_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_lich_ranh_giao_vien` (`giao_vien_id`),
  KEY `fk_lich_ranh_nguoi_duyet` (`nguoi_duyet_id`),
  CONSTRAINT `fk_lich_ranh_giao_vien` FOREIGN KEY (`giao_vien_id`) REFERENCES `giao_vien` (`id`),
  CONSTRAINT `fk_lich_ranh_nguoi_duyet` FOREIGN KEY (`nguoi_duyet_id`) REFERENCES `tai_khoan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lich_ranh`
--

LOCK TABLES `lich_ranh` WRITE;
/*!40000 ALTER TABLE `lich_ranh` DISABLE KEYS */;
INSERT INTO `lich_ranh` VALUES (1,1,'2026-10-26','07:00:00','09:00:00','cho_duyet','Có thể nhận lớp Yoga bổ sung',NULL,'2026-11-09 08:50:31'),(2,1,'2026-10-27','18:00:00','20:00:00','da_duyet','Lịch dạy Yoga cơ bản',NULL,'2026-11-09 08:50:31'),(3,2,'2026-10-27','06:00:00','07:30:00','cho_duyet','HIIT sáng sớm',NULL,'2026-11-09 08:50:31'),(4,3,'2026-10-28','17:00:00','19:00:00','cho_duyet','Pilates bổ sung',NULL,'2026-11-09 08:50:31'),(5,1,'2026-10-24','09:00:00','11:00:00','da_duyet','Yoga nâng cao',NULL,'2026-11-09 08:50:31'),(6,4,'2026-10-23','18:00:00','19:30:00','tu_choi','Trùng lịch lớp Gym nâng cao',NULL,'2026-11-09 08:50:31'),(7,1,'2026-10-29','19:00:00','20:00:00','da_huy','Giáo viên đã hủy yêu cầu',NULL,'2026-11-09 08:50:31');
/*!40000 ALTER TABLE `lich_ranh` ENABLE KEYS */;
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
  `trang_thai` varchar(20) NOT NULL DEFAULT 'dang_mo',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_lop_hoc_giao_vien` (`giao_vien_id`),
  CONSTRAINT `fk_lop_hoc_giao_vien` FOREIGN KEY (`giao_vien_id`) REFERENCES `giao_vien` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lop_hoc`
--

LOCK TABLES `lop_hoc` WRITE;
/*!40000 ALTER TABLE `lop_hoc` DISABLE KEYS */;
INSERT INTO `lop_hoc` VALUES (1,'Yoga cơ bản',1,1,'18:00:00','19:00:00','2026-09-01','2026-11-30',24,'P.101','Làm quen hơi thở, tư thế nền tảng và thư giãn sâu.','Cơ bản','dang_mo',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(2,'HIIT đốt mỡ',2,2,'06:00:00','07:00:00','2026-09-01','2026-12-31',16,'P.202','Bài tập cường độ cao ngắt quãng, đốt cháy calo tối đa.','Trung cấp','dang_mo',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(3,'Gym & Strength',4,3,'17:30:00','18:30:00','2026-09-05','2026-12-30',20,'Khu A','Kỹ thuật nâng tạ cơ bản, xây dựng sức mạnh toàn thân.','Cơ bản','dang_mo',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(4,'Zumba Dance',1,4,'19:00:00','20:00:00','2026-09-01','2026-11-30',30,'P.101','Kết hợp âm nhạc và vũ đạo Latin, đốt calo vui vẻ.','Mọi trình độ','dang_mo',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(5,'Pilates kiểm soát',3,5,'07:00:00','08:00:00','2026-09-01','2026-10-31',15,'P.103','Tăng cường core, cải thiện tư thế và độ dẻo dai.','Trung cấp','dang_mo',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(6,'Boxing cơ bản',2,6,'08:00:00','09:00:00','2026-09-12','2026-12-30',18,'Khu B','Làm quen kỹ thuật đấm, di chuyển và phản xạ.','Cơ bản','dang_mo',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(7,'Yoga nâng cao',1,6,'09:30:00','10:30:00','2026-09-01','2026-11-30',20,'P.101','Chuỗi tư thế nâng cao, giữ thăng bằng và kiểm soát hơi thở.','Nâng cao','dang_mo',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(8,'Cardio & Core',3,7,'16:00:00','17:00:00','2026-09-01','2026-12-31',25,'P.202','Cardio nhẹ nhàng kết hợp bài tập săn chắc vùng bụng.','Mọi trình độ','dang_mo',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31'),(9,'Gym nâng cao',4,5,'18:00:00','19:30:00','2026-10-01','2026-12-31',12,'Khu A','Chương trình sức mạnh chuyên sâu cho học viên lâu năm.','Nâng cao','da_dong',NULL,'2026-11-09 08:50:31','2026-11-09 08:50:31');
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tai_khoan`
--

LOCK TABLES `tai_khoan` WRITE;
/*!40000 ALTER TABLE `tai_khoan` DISABLE KEYS */;
INSERT INTO `tai_khoan` VALUES (1,'admin','$2y$10$8rZrVPf8WmreyidGggoN..rqfs86n.PnRbXQtumTXOAjlMzu95oTS','Quản trị viên','admin',1,'2026-11-09 08:50:32'),(2,'thuha','$2y$10$/i0aCA0ydaJA74r084OaIOUK1zxkcwfPYsrJAtcGVk2o9PGlaDaia','Trần Thu Hà','instructor',1,'2026-11-09 08:50:32'),(3,'ducanh','$2y$10$o2AIx0lf1E4Tg03lq5XDvelxXI1GVJCy2GpHbt0xWluga0B8C4g.e','Nguyễn Đức Anh','instructor',1,'2026-11-09 08:50:32'),(4,'ngocmai','$2y$10$FU2IK7l2gjacF5FaQhzxK.e2E4j.tr4mIKLWVQxibZUF1RlPPt0Tm','Lê Ngọc Mai','instructor',1,'2026-11-09 08:50:32'),(5,'quocbao','$2y$10$x.UqNIZ.lW6hfLXtYRwFcuaiNK8ZhEb2XJX1YD95KPJsG5KdN1wgO','Phạm Quốc Bảo','instructor',1,'2026-11-09 08:50:32'),(6,'hongnhung','$2y$10$PMnVk2isSl/SDaWvfjAuYOyMMf3IB2fo4JqNi09jCqseR.7AdxRju','Đặng Hồng Nhung','instructor',1,'2026-11-09 08:50:32'),(7,'hoangnam','$2y$10$f6CbQJiyoYyTs70FQo8jI.MtyhnXaWIiPEG/Nv6gwVbSCLt5P.T.a','Lê Hoàng Nam','customer',1,'2026-11-09 08:50:32');
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

-- Dump completed on 2026-11-09  9:32:22
