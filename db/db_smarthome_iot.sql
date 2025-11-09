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


-- Dumping database structure for db_smarthome_iot
CREATE DATABASE IF NOT EXISTS `db_smarthome_iot` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `db_smarthome_iot`;

-- Dumping structure for table db_smarthome_iot.aktivitas
CREATE TABLE IF NOT EXISTS `aktivitas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `waktu` datetime NOT NULL,
  `lokasi` varchar(100) NOT NULL,
  `status_rumah` enum('kosong','ada orang') NOT NULL,
  `durasi` int NOT NULL,
  `klasifikasi` varchar(20) NOT NULL,
  `deskripsi_ai` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_smarthome_iot.aktivitas: ~4 rows (approximately)
REPLACE INTO `aktivitas` (`id`, `waktu`, `lokasi`, `status_rumah`, `durasi`, `klasifikasi`, `deskripsi_ai`) VALUES
	(13, '2025-07-07 08:54:13', 'Depan Rumah', 'kosong', 15, 'aman', 'Tidak terdeteksi gerakan mencurigakan.'),
	(14, '2025-07-07 08:54:13', 'Belakang Rumah', 'ada orang', 20, 'mencurigakan', 'Gerakan mendekati jendela belakang.'),
	(15, '2025-07-07 08:54:13', 'Teras', 'kosong', 10, 'aman', 'Anjing peliharaan terdeteksi.'),
	(16, '2025-07-07 08:54:13', 'Garasi', 'ada orang', 30, 'mencurigakan', 'Objek tidak dikenal berada di area garasi.');

-- Dumping structure for table db_smarthome_iot.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_smarthome_iot.users: ~0 rows (approximately)
REPLACE INTO `users` (`id`, `username`, `password`) VALUES
	(1, 'admin', 'admin123');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
