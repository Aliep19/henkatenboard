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

-- Dumping structure for table lembur.ct_users
CREATE TABLE IF NOT EXISTS `ct_users` (
  `npk` varchar(5) NOT NULL,
  `full_name` varchar(50) DEFAULT NULL,
  `pwd` varchar(220) DEFAULT NULL,
  `dept` varchar(50) DEFAULT NULL,
  `sect` int DEFAULT NULL,
  `subsect` int DEFAULT NULL,
  `golongan` int DEFAULT NULL,
  `acting` int DEFAULT NULL,
  `no_telp` char(15) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`npk`),
  KEY `sect` (`sect`),
  KEY `subsect` (`subsect`),
  CONSTRAINT `ct_users_ibfk_2` FOREIGN KEY (`subsect`) REFERENCES `hrd_subsect` (`subsect`),
  CONSTRAINT `ct_users_ibfk_3` FOREIGN KEY (`sect`) REFERENCES `hrd_sect` (`sect`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Data exporting was unselected.

-- Dumping structure for table lembur.hrd_sect
CREATE TABLE IF NOT EXISTS `hrd_sect` (
  `sect` int NOT NULL,
  `desc` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`sect`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table lembur.hrd_so
CREATE TABLE IF NOT EXISTS `hrd_so` (
  `npk` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `tipe` int NOT NULL,
  UNIQUE KEY `npk` (`npk`) USING BTREE,
  CONSTRAINT `hrd_so_ibfk_1` FOREIGN KEY (`npk`) REFERENCES `ct_users` (`npk`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table lembur.hrd_subsect
CREATE TABLE IF NOT EXISTS `hrd_subsect` (
  `subsect` int NOT NULL,
  `sect` int DEFAULT NULL,
  `desc` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`subsect`),
  KEY `sect` (`sect`),
  CONSTRAINT `hrd_subsect_ibfk_1` FOREIGN KEY (`sect`) REFERENCES `hrd_sect` (`sect`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
