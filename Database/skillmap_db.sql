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

-- Dumping structure for table skillmap_db.department
CREATE TABLE IF NOT EXISTS `department` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dept_name` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.ehs
CREATE TABLE IF NOT EXISTS `ehs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `workstation_id` int NOT NULL,
  `name` varchar(48) NOT NULL,
  `min_skill` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.karyawan
CREATE TABLE IF NOT EXISTS `karyawan` (
  `npk` varchar(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `role` int NOT NULL,
  `date_joined` date NOT NULL,
  `shift` int NOT NULL,
  PRIMARY KEY (`npk`),
  KEY `role` (`role`),
  KEY `idx_npk` (`npk`),
  KEY `idx_karyawan_npk` (`npk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.karyawan_workstation
CREATE TABLE IF NOT EXISTS `karyawan_workstation` (
  `npk` varchar(11) NOT NULL,
  `workstation_id` int NOT NULL,
  UNIQUE KEY `uniq` (`npk`,`workstation_id`),
  KEY `workstation_id` (`workstation_id`),
  KEY `npk` (`npk`),
  KEY `idx_npk` (`npk`),
  KEY `idx_npk_workstation` (`npk`,`workstation_id`),
  CONSTRAINT `karyawan_workstation_ibfk_1` FOREIGN KEY (`workstation_id`) REFERENCES `sub_workstations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.mp_category
CREATE TABLE IF NOT EXISTS `mp_category` (
  `codename` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.mp_file_proof
CREATE TABLE IF NOT EXISTS `mp_file_proof` (
  `id` int NOT NULL AUTO_INCREMENT,
  `npk` varchar(11) NOT NULL,
  `mp_category` varchar(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `filename` varchar(32) NOT NULL,
  `description` varchar(64) DEFAULT NULL,
  `posted_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.mp_scores
CREATE TABLE IF NOT EXISTS `mp_scores` (
  `npk` varchar(11) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `msk` int NOT NULL,
  `kt` int NOT NULL,
  `png` int NOT NULL,
  `pssp` int NOT NULL,
  `fivejq` int NOT NULL,
  `kao` int NOT NULL,
  PRIMARY KEY (`npk`),
  UNIQUE KEY `npk_3` (`npk`),
  KEY `npk` (`npk`),
  KEY `npk_2` (`npk`),
  KEY `msk` (`msk`,`kt`,`png`,`pssp`,`fivejq`,`kao`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.otp
CREATE TABLE IF NOT EXISTS `otp` (
  `OTP_ID` int NOT NULL AUTO_INCREMENT,
  `NPK` varchar(6) NOT NULL,
  `NO_OTP` varchar(255) NOT NULL,
  `EXP_DATE` datetime DEFAULT NULL,
  `SEND` int DEFAULT NULL,
  `SEND_DATE` datetime DEFAULT NULL,
  `USE_DATE` datetime DEFAULT NULL,
  PRIMARY KEY (`OTP_ID`),
  KEY `NPK` (`NPK`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.process
CREATE TABLE IF NOT EXISTS `process` (
  `id` int NOT NULL AUTO_INCREMENT,
  `workstation_id` int NOT NULL,
  `s_process_id` int NOT NULL,
  `status` int NOT NULL,
  `name` varchar(48) NOT NULL,
  `min_skill` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `workstation_id` (`workstation_id`),
  KEY `idx_process_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=734 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.qualifications
CREATE TABLE IF NOT EXISTS `qualifications` (
  `process_id` int NOT NULL,
  `npk` varchar(11) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `value` int NOT NULL,
  `status` int DEFAULT NULL,
  `jadwal_training_process` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  KEY `npk` (`npk`),
  KEY `process_id` (`process_id`),
  KEY `idx_npk` (`npk`),
  KEY `idx_qual_npk_process` (`npk`,`process_id`),
  CONSTRAINT `qualifications_ibfk_1` FOREIGN KEY (`process_id`) REFERENCES `process` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.qualifications_ehs
CREATE TABLE IF NOT EXISTS `qualifications_ehs` (
  `ehs_id` int NOT NULL,
  `npk` varchar(11) NOT NULL,
  `value` int NOT NULL,
  `jadwal_training_ehs` date DEFAULT NULL,
  `status` int DEFAULT NULL,
  `created_at` datetime NOT NULL,
  KEY `idx_npk` (`npk`),
  KEY `idx_npk_qualifications_ehs` (`npk`),
  KEY `ehs_id` (`ehs_id`),
  CONSTRAINT `qualifications_ehs_ibfk_1` FOREIGN KEY (`ehs_id`) REFERENCES `ehs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.qualifications_ehs_history
CREATE TABLE IF NOT EXISTS `qualifications_ehs_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ehs_id` int NOT NULL,
  `old_value` int NOT NULL,
  `new_value` int NOT NULL,
  `npk` varchar(20) NOT NULL,
  `jadwal_training_ehs` date NOT NULL,
  `status` int NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ehs_id` (`ehs_id`),
  CONSTRAINT `qualifications_ehs_history_ibfk_1` FOREIGN KEY (`ehs_id`) REFERENCES `qualifications_ehs` (`ehs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.qualifications_history
CREATE TABLE IF NOT EXISTS `qualifications_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `process_id` int NOT NULL,
  `old_value` int NOT NULL,
  `new_value` int NOT NULL,
  `npk` varchar(20) NOT NULL,
  `jadwal_training_process` date NOT NULL,
  `status` int NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `process_id` (`process_id`),
  CONSTRAINT `qualifications_history_ibfk_1` FOREIGN KEY (`process_id`) REFERENCES `qualifications` (`process_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.qualifications_quality
CREATE TABLE IF NOT EXISTS `qualifications_quality` (
  `quality_id` int NOT NULL,
  `npk` varchar(11) NOT NULL,
  `value` int NOT NULL,
  `jadwal_training_quality` date DEFAULT NULL,
  `status` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  KEY `idx_npk` (`npk`),
  KEY `idx_npk_qualifications_quality` (`npk`),
  KEY `quality_id` (`quality_id`),
  CONSTRAINT `qualifications_quality_ibfk_1` FOREIGN KEY (`quality_id`) REFERENCES `quality` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.qualifications_quality_history
CREATE TABLE IF NOT EXISTS `qualifications_quality_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `quality_id` int NOT NULL,
  `old_value` int NOT NULL,
  `new_value` int NOT NULL,
  `npk` varchar(20) NOT NULL,
  `jadwal_training_quality` date NOT NULL,
  `status` int NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `quality_id` (`quality_id`),
  CONSTRAINT `qualifications_quality_history_ibfk_1` FOREIGN KEY (`quality_id`) REFERENCES `qualifications_quality` (`quality_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.quality
CREATE TABLE IF NOT EXISTS `quality` (
  `id` int NOT NULL AUTO_INCREMENT,
  `workstation_id` int NOT NULL,
  `name` varchar(48) NOT NULL,
  `min_skill` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.relocate_history
CREATE TABLE IF NOT EXISTS `relocate_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `npk` varchar(11) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_time` timestamp NULL DEFAULT NULL,
  `subworkstation_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.role
CREATE TABLE IF NOT EXISTS `role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `npk` varchar(11) NOT NULL,
  `role` varchar(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL,
  `name` varchar(16) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.sub_workstations
CREATE TABLE IF NOT EXISTS `sub_workstations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(48) NOT NULL,
  `workstation_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `workstation_id` (`workstation_id`),
  KEY `idx_id_sub_workstations` (`id`),
  CONSTRAINT `sub_workstations_ibfk_1` FOREIGN KEY (`workstation_id`) REFERENCES `workstations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.s_process
CREATE TABLE IF NOT EXISTS `s_process` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(48) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.s_process_certification
CREATE TABLE IF NOT EXISTS `s_process_certification` (
  `id` int NOT NULL AUTO_INCREMENT,
  `npk` varchar(11) NOT NULL,
  `s_process_id` int NOT NULL,
  `process_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `npk_s_process_id` (`npk`,`s_process_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6702 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.s_process_workstation
CREATE TABLE IF NOT EXISTS `s_process_workstation` (
  `id_s_process` int NOT NULL,
  `workstation_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.users
CREATE TABLE IF NOT EXISTS `users` (
  `username` varchar(12) NOT NULL,
  `password` varchar(64) NOT NULL,
  `role` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table skillmap_db.workstations
CREATE TABLE IF NOT EXISTS `workstations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dept_id` int NOT NULL,
  `name` varchar(48) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `dept_id` (`dept_id`),
  KEY `idx_dept_id` (`dept_id`),
  CONSTRAINT `workstations_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `department` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
