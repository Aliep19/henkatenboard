-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table henkaten.hkt_form
CREATE TABLE IF NOT EXISTS `hkt_form` (
  `id_hkt` int NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `to_date` date NOT NULL,
  `id_bagian` int NOT NULL,
  `id_line` int NOT NULL,
  `id_shifft` int NOT NULL,
  `output_target` int NOT NULL,
  `foreman` int NOT NULL,
  `foreman_2` int NOT NULL,
  `line_guide` int NOT NULL,
  `line_guide2` int NOT NULL,
  PRIMARY KEY (`id_hkt`),
  KEY `idx_hkt_form_id_line_date` (`id_line`,`date`)
) ENGINE=InnoDB AUTO_INCREMENT=178 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table henkaten.machine
CREATE TABLE IF NOT EXISTS `machine` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `shift` varchar(1) NOT NULL,
  `proses` varchar(255) NOT NULL,
  `mesin` varchar(255) NOT NULL,
  `kode` varchar(2) NOT NULL,
  `alasan` text NOT NULL,
  `sebelum` text NOT NULL,
  `saatini` text NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table henkaten.material
CREATE TABLE IF NOT EXISTS `material` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `shift` varchar(1) NOT NULL,
  `no_material` varchar(255) NOT NULL,
  `nama_material` varchar(255) NOT NULL,
  `kode` varchar(2) NOT NULL,
  `alasan` text NOT NULL,
  `sebelum` text NOT NULL,
  `saatini` text NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table henkaten.method
CREATE TABLE IF NOT EXISTS `method` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `shift` varchar(1) NOT NULL,
  `proses` varchar(255) NOT NULL,
  `kode` varchar(2) NOT NULL,
  `alasan` text NOT NULL,
  `sebelum` text NOT NULL,
  `saatini` text NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table henkaten.mp_procees
CREATE TABLE IF NOT EXISTS `mp_procees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_hkt` int NOT NULL,
  `id_proses` int NOT NULL,
  `man_power` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `absen` int NOT NULL,
  `mp_pengganti` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_hkt` (`id_hkt`),
  KEY `id_hkt_2` (`id_hkt`),
  KEY `idx_mp_procees_id_hkt` (`id_hkt`),
  KEY `idx_id_hkt` (`id_hkt`),
  KEY `idx_man_power` (`man_power`),
  KEY `idx_id_proses` (`id_proses`)
) ENGINE=InnoDB AUTO_INCREMENT=1896 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table henkaten.news_images
CREATE TABLE IF NOT EXISTS `news_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` enum('production','qa') NOT NULL,
  `filename` varchar(255) NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table henkaten.notification_log
CREATE TABLE IF NOT EXISTS `notification_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_wa` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_hkt` int DEFAULT NULL,
  `notification_type` enum('alert_absensi','peringatan_absensi','perubahan_mp') NOT NULL DEFAULT 'perubahan_mp',
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_notification_type` (`notification_type`),
  KEY `idx_id_hkt` (`id_hkt`),
  CONSTRAINT `notification_log_ibfk_1` FOREIGN KEY (`id_hkt`) REFERENCES `hkt_form` (`id_hkt`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1029 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table henkaten.otp
CREATE TABLE IF NOT EXISTS `otp` (
  `npk` varchar(6) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `send` varchar(50) NOT NULL,
  `use` varchar(50) NOT NULL,
  KEY `npk` (`npk`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table henkaten.perubahan
CREATE TABLE IF NOT EXISTS `perubahan` (
  `id_perubahan` int NOT NULL AUTO_INCREMENT,
  `id_proses` int NOT NULL,
  `mp_awal` varchar(11) NOT NULL,
  `reason` int NOT NULL,
  `mp_pengganti` varchar(11) NOT NULL,
  `id_shift` int NOT NULL,
  `tanggal` date NOT NULL,
  PRIMARY KEY (`id_perubahan`),
  KEY `id_proses` (`id_proses`),
  KEY `id_shift` (`id_shift`)
) ENGINE=InnoDB AUTO_INCREMENT=1324 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table henkaten.shift
CREATE TABLE IF NOT EXISTS `shift` (
  `id_shift` int NOT NULL AUTO_INCREMENT,
  `shift` varchar(20) NOT NULL,
  `jam_kerja` varchar(50) NOT NULL,
  PRIMARY KEY (`id_shift`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
