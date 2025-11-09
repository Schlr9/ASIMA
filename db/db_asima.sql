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


-- Dumping database structure for db_asima
CREATE DATABASE IF NOT EXISTS `db_asima` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `db_asima`;

-- Dumping structure for table db_asima.clusters
CREATE TABLE IF NOT EXISTS `clusters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_cluster` varchar(100) NOT NULL,
  `deskripsi` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_asima.clusters: ~2 rows (approximately)
INSERT INTO `clusters` (`id`, `nama_cluster`, `deskripsi`) VALUES
	(4, 'Kompetensi Perlu Peningkatan', NULL),
	(5, 'Kompetensi Tinggi', NULL);

-- Dumping structure for table db_asima.conversations
CREATE TABLE IF NOT EXISTS `conversations` (
  `id` varchar(255) NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `messages` longtext NOT NULL,
  `test_result_json` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_m` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_asima.conversations: ~0 rows (approximately)

-- Dumping structure for table db_asima.test_questions
CREATE TABLE IF NOT EXISTS `test_questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kategori` enum('linguistik','logis_matematis','spasial','kinestetik','musikal','interpersonal','intrapersonal','naturalis') NOT NULL,
  `pernyataan` text NOT NULL COMMENT 'Soal diubah menjadi format pernyataan',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_asima.test_questions: ~80 rows (approximately)
INSERT INTO `test_questions` (`id`, `kategori`, `pernyataan`) VALUES
	(1, 'linguistik', 'Saya lebih suka menyusun proposal atau laporan tertulis yang detail untuk menyampaikan ide-ide penting.'),
	(2, 'linguistik', 'Saat rapat, saya sering menjadi orang yang merangkum diskusi dan memperjelas poin-poin yang ada.'),
	(3, 'linguistik', 'Saya menikmati permainan kata atau humor verbal dan sering menggunakannya untuk mencairkan suasana.'),
	(4, 'linguistik', 'Saya dapat dengan mudah memahami dan menjelaskan isi dari dokumen teknis atau kontrak yang rumit.'),
	(5, 'linguistik', 'Saya merasa percaya diri saat harus berbicara di depan umum atau melakukan presentasi.'),
	(6, 'linguistik', 'Menulis email yang persuasif atau jelas adalah salah satu keunggulan saya.'),
	(7, 'linguistik', 'Saya sering mengingat informasi dengan lebih baik setelah mendengarkannya (misalnya dari podcast atau audiobook).'),
	(8, 'linguistik', 'Saya gemar mempelajari istilah-istilah baru yang berkaitan dengan industri atau bidang pekerjaan saya.'),
	(9, 'linguistik', 'Orang lain mengatakan bahwa saya adalah pendongeng yang baik, mampu membuat sebuah narasi menjadi menarik.'),
	(10, 'linguistik', 'Saya sering diminta untuk menjadi juru bicara atau perwakilan tim karena kemampuan komunikasi saya.'),
	(11, 'logis_matematis', 'Saya secara alami mencari pola, keteraturan, atau urutan logis dalam data atau informasi.'),
	(12, 'logis_matematis', 'Sebelum mengambil keputusan besar, saya selalu mencoba menganalisis pro dan kontra secara sistematis.'),
	(13, 'logis_matematis', 'Saya menikmati tugas-tugas yang melibatkan pembuatan anggaran, analisis data, atau metrik performa.'),
	(14, 'logis_matematis', 'Saya merasa nyaman bekerja dengan bahasa pemrograman, formula spreadsheet, atau sistem berbasis aturan lainnya.'),
	(15, 'logis_matematis', 'Saya sering mengajukan pertanyaan "mengapa" untuk memahami sebab-akibat di balik sebuah masalah.'),
	(16, 'logis_matematis', 'Saya dapat dengan cepat mengidentifikasi kelemahan atau inkonsistensi dalam sebuah argumen atau rencana.'),
	(17, 'logis_matematis', 'Saya lebih suka merencanakan proyek dengan membuat jadwal yang detail dan terstruktur.'),
	(18, 'logis_matematis', 'Saya tertarik dengan perkembangan terbaru di bidang sains dan teknologi.'),
	(19, 'logis_matematis', 'Memecahkan teka-teki logika atau brainteaser adalah kegiatan yang saya nikmati.'),
	(20, 'logis_matematis', 'Saya percaya bahwa sebagian besar masalah memiliki penjelasan yang rasional dan dapat dipecahkan.'),
	(21, 'spasial', 'Saya sering memvisualisasikan hasil akhir sebuah proyek dalam pikiran saya bahkan sebelum dimulai.'),
	(22, 'spasial', 'Saya lebih suka belajar dari diagram, grafik, atau video daripada membaca teks panjang.'),
	(23, 'spasial', 'Saya memiliki kepekaan terhadap estetika, seperti desain, warna, dan komposisi visual.'),
	(24, 'spasial', 'Saya dapat dengan mudah membayangkan bagaimana sebuah objek terlihat dari sudut pandang yang berbeda.'),
	(25, 'spasial', 'Saya sering membuat coretan (doodle) atau sketsa saat sedang berpikir atau dalam rapat.'),
	(26, 'spasial', 'Memberikan arahan jalan atau menata perabotan di ruangan adalah hal yang mudah bagi saya.'),
	(27, 'spasial', 'Saya pandai merakit sesuatu hanya dengan melihat diagram, tanpa perlu membaca instruksi tertulis.'),
	(28, 'spasial', 'Saya menikmati penggunaan software desain grafis, presentasi visual, atau aplikasi pemodelan 3D.'),
	(29, 'spasial', 'Saya sering menggunakan kode warna untuk mengatur file, catatan, atau jadwal saya.'),
	(30, 'spasial', 'Saya sering melihat gambaran visual yang jelas saat saya memejamkan mata untuk berpikir.'),
	(31, 'kinestetik', 'Saya memahami sebuah proses dengan lebih baik ketika saya bisa terlibat langsung secara fisik.'),
	(32, 'kinestetik', 'Saya tidak suka duduk diam terlalu lama dan membutuhkan jeda untuk bergerak.'),
	(33, 'kinestetik', 'Saya mendapatkan ide-ide terbaik saat sedang berjalan, berolahraga, atau melakukan aktivitas fisik lainnya.'),
	(34, 'kinestetik', 'Saya memiliki koordinasi tangan-mata yang baik untuk tugas-tugas seperti mengetik cepat, menggunakan peralatan, atau merakit.'),
	(35, 'kinestetik', 'Saya lebih suka mendemonstrasikan cara melakukan sesuatu daripada hanya menjelaskannya dengan kata-kata.'),
	(36, 'kinestetik', 'Saya sering menggunakan gestur atau bahasa tubuh yang ekspresif saat berkomunikasi.'),
	(37, 'kinestetik', 'Pekerjaan yang melibatkan kunjungan lapangan, kerja di workshop, atau interaksi fisik lebih menarik bagi saya.'),
	(38, 'kinestetik', 'Saya belajar keterampilan baru dengan cara mempraktikkannya berulang kali.'),
	(39, 'kinestetik', 'Saya dapat merasakan "firasat" atau sensasi fisik yang terkait dengan pengambilan keputusan.'),
	(40, 'kinestetik', 'Saya menikmati pekerjaan yang hasilnya nyata dan bisa disentuh atau dipegang.'),
	(41, 'musikal', 'Saya dapat dengan mudah mengenali ketika sebuah nada fals atau irama tidak pas.'),
	(42, 'musikal', 'Saya sering memiliki lagu atau melodi yang terngiang-ngiang di kepala saya saat bekerja.'),
	(43, 'musikal', 'Saya dapat mengingat informasi lebih baik jika disajikan dalam bentuk ritmis atau seperti lagu.'),
	(44, 'musikal', 'Saya sering mengetuk-ngetuk jari atau kaki mengikuti irama tertentu saat berpikir.'),
	(45, 'musikal', 'Saya peka terhadap suara-suara di lingkungan sekitar (misalnya, dengungan AC, suara printer).'),
	(46, 'musikal', 'Suasana hati saya mudah dipengaruhi oleh musik yang sedang diputar di latar belakang.'),
	(47, 'musikal', 'Saya menggunakan musik untuk membantu saya fokus, mendapatkan energi, atau bersantai saat bekerja.'),
	(48, 'musikal', 'Saya dapat mengapresiasi struktur dan pola dalam sebuah komposisi musik.'),
	(49, 'musikal', 'Saya dapat dengan mudah meniru atau mengulang sebuah pola ritmis setelah mendengarnya.'),
	(50, 'musikal', 'Saya bisa merasakan "aliran" atau ritme dalam sebuah presentasi atau pidato yang bagus.'),
	(51, 'interpersonal', 'Saya pandai merasakan suasana hati atau dinamika dalam sebuah rapat atau kelompok.'),
	(52, 'interpersonal', 'Saya secara alami mengambil peran sebagai pemimpin atau fasilitator dalam sebuah tim.'),
	(53, 'interpersonal', 'Saya lebih suka bertukar pikiran (brainstorming) dengan orang lain daripada berpikir sendirian.'),
	(54, 'interpersonal', 'Saya mampu memberikan kritik atau umpan balik yang membangun tanpa menyinggung perasaan orang lain.'),
	(55, 'interpersonal', 'Saya menikmati berkolaborasi dalam proyek dan merasa energi saya meningkat saat bekerja dengan tim.'),
	(56, 'interpersonal', 'Saya mudah berempati dan memahami sudut pandang rekan kerja saya.'),
	(57, 'interpersonal', 'Saya suka mengajar atau menjadi mentor bagi rekan kerja yang lebih junior.'),
	(58, 'interpersonal', 'Saya dapat membangun hubungan kerja yang kuat dan positif dengan berbagai macam orang.'),
	(59, 'interpersonal', 'Saya adalah pendengar yang baik; orang lain sering merasa nyaman berbagi masalah dengan saya.'),
	(60, 'interpersonal', 'Saya sering menjadi penengah ketika ada perbedaan pendapat atau konflik dalam tim.'),
	(61, 'intrapersonal', 'Saya sangat menyadari kekuatan, kelemahan, dan batasan profesional saya.'),
	(62, 'intrapersonal', 'Saya memiliki motivasi internal yang kuat dan tidak terlalu bergantung pada pujian dari luar.'),
	(63, 'intrapersonal', 'Saya secara sadar menetapkan tujuan untuk pengembangan diri dan karir saya.'),
	(64, 'intrapersonal', 'Saya membutuhkan waktu untuk refleksi pribadi agar dapat memproses pengalaman dan membuat keputusan terbaik.'),
	(65, 'intrapersonal', 'Saya lebih suka bekerja pada proyek-proyek yang selaras dengan nilai-nilai dan minat pribadi saya.'),
	(66, 'intrapersonal', 'Saya mampu mengelola emosi dan tetap tenang di bawah tekanan.'),
	(67, 'intrapersonal', 'Saya belajar dari kesalahan dan kegagalan saya di masa lalu untuk menjadi lebih baik.'),
	(68, 'intrapersonal', 'Saya memiliki rasa percaya diri yang kuat dan tidak mudah terpengaruh oleh pendapat orang lain.'),
	(69, 'intrapersonal', 'Saya lebih memilih otonomi dan kebebasan dalam cara saya menyelesaikan pekerjaan.'),
	(70, 'intrapersonal', 'Saya sering menulis jurnal atau membuat catatan pribadi untuk menjernihkan pikiran saya.'),
	(71, 'naturalis', 'Saya pandai mengenali pola dan mengkategorikan informasi yang tampaknya tidak berhubungan.'),
	(72, 'naturalis', 'Saya sering menggunakan analogi dari alam untuk menjelaskan konsep bisnis atau teknis.'),
	(73, 'naturalis', 'Saya mampu melihat bagaimana perubahan kecil dalam satu bagian sistem dapat memengaruhi keseluruhan.'),
	(74, 'naturalis', 'Saya tertarik pada asal-usul bahan baku atau siklus hidup produk yang dikerjakan perusahaan saya.'),
	(75, 'naturalis', 'Saya merasa terganggu jika lingkungan kerja saya tidak teratur atau tidak seimbang.'),
	(76, 'naturalis', 'Saya menikmati pekerjaan yang berhubungan dengan sistem yang hidup, seperti pertanian, bioteknologi, atau ilmu lingkungan.'),
	(77, 'naturalis', 'Saat melakukan analisis, saya secara alami mengelompokkan item ke dalam taksonomi atau hierarki.'),
	(78, 'naturalis', 'Saya sering mengamati interaksi dan hubungan dalam "ekosistem" kantor atau pasar.'),
	(79, 'naturalis', 'Saya bisa membedakan produk atau layanan yang serupa berdasarkan fitur-fitur kecil yang membedakannya.'),
	(80, 'naturalis', 'Saya merasa ide-ide baru sering muncul saat saya berada di lingkungan alami, seperti taman atau pantai.');

-- Dumping structure for table db_asima.test_results
CREATE TABLE IF NOT EXISTS `test_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `skor_linguistik` int NOT NULL DEFAULT '0',
  `skor_logis_matematis` int NOT NULL DEFAULT '0',
  `skor_spasial` int NOT NULL DEFAULT '0',
  `skor_kinestetik` int NOT NULL DEFAULT '0',
  `skor_musikal` int NOT NULL DEFAULT '0',
  `skor_interpersonal` int NOT NULL DEFAULT '0',
  `skor_intrapersonal` int NOT NULL DEFAULT '0',
  `skor_naturalis` int NOT NULL DEFAULT '0',
  `rekomendasi_teks` text,
  `cluster_id` int DEFAULT NULL,
  `tanggal_tes` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `test_results_ibfk_2` (`cluster_id`),
  CONSTRAINT `test_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_m` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `test_results_ibfk_2` FOREIGN KEY (`cluster_id`) REFERENCES `clusters` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_asima.test_results: ~50 rows (approximately)
INSERT INTO `test_results` (`id`, `user_id`, `skor_linguistik`, `skor_logis_matematis`, `skor_spasial`, `skor_kinestetik`, `skor_musikal`, `skor_interpersonal`, `skor_intrapersonal`, `skor_naturalis`, `rekomendasi_teks`, `cluster_id`, `tanggal_tes`) VALUES
	(53, 19, 1, 3, 2, 6, 9, 5, 5, 4, NULL, 5, '2025-10-25 19:41:41'),
	(54, 20, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 4, '2025-10-25 19:41:41'),
	(55, 21, 2, 2, 2, 2, 1, 1, 1, 2, NULL, 4, '2025-10-25 19:41:41'),
	(56, 22, 6, 8, 6, 7, 9, 6, 5, 8, NULL, 5, '2025-10-25 19:41:41'),
	(57, 23, 6, 10, 10, 9, 10, 8, 10, 9, NULL, 5, '2025-10-25 19:41:41'),
	(58, 24, 1, 1, 3, 8, 4, 3, 3, 3, NULL, 4, '2025-10-25 19:41:41'),
	(59, 25, 5, 7, 6, 5, 6, 4, 5, 6, NULL, 5, '2025-10-25 19:41:41'),
	(60, 26, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 4, '2025-10-25 19:41:41'),
	(61, 27, 2, 1, 2, 2, 2, 2, 2, 1, NULL, 4, '2025-10-25 19:41:41'),
	(62, 28, 1, 1, 1, 1, 1, 1, 2, 1, NULL, 4, '2025-10-25 19:41:41'),
	(63, 29, 1, 2, 1, 4, 2, 3, 4, 4, NULL, 4, '2025-10-25 19:41:41'),
	(64, 30, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 4, '2025-10-25 19:41:41'),
	(65, 31, 4, 4, 3, 4, 3, 3, 3, 3, NULL, 4, '2025-10-25 19:41:41'),
	(66, 32, 5, 5, 1, 3, 1, 1, 1, 1, NULL, 4, '2025-10-25 19:41:41'),
	(67, 33, 5, 9, 4, 6, 5, 6, 9, 9, NULL, 5, '2025-10-25 19:41:41'),
	(68, 34, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 4, '2025-10-25 19:41:41'),
	(69, 35, 6, 5, 7, 7, 7, 7, 8, 8, NULL, 5, '2025-10-25 19:41:41'),
	(70, 36, 2, 3, 2, 2, 3, 2, 2, 1, NULL, 4, '2025-10-25 19:41:41'),
	(71, 37, 1, 1, 2, 1, 1, 1, 1, 1, NULL, 4, '2025-10-25 19:41:41'),
	(72, 38, 1, 1, 1, 1, 1, 1, 4, 3, NULL, 4, '2025-10-25 19:41:41'),
	(73, 39, 6, 8, 7, 6, 5, 4, 5, 4, NULL, 5, '2025-10-25 19:41:41'),
	(74, 40, 7, 10, 8, 9, 8, 6, 7, 8, NULL, 5, '2025-10-25 19:41:41'),
	(75, 41, 1, 3, 2, 3, 3, 2, 4, 3, NULL, 4, '2025-10-25 19:41:41'),
	(76, 42, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 4, '2025-10-25 19:41:41'),
	(77, 43, 4, 5, 6, 4, 4, 3, 3, 3, NULL, 4, '2025-10-25 19:41:41'),
	(78, 44, 3, 2, 2, 3, 2, 2, 2, 1, NULL, 4, '2025-10-25 19:41:41'),
	(79, 45, 2, 2, 2, 2, 4, 3, 2, 2, NULL, 4, '2025-10-25 19:41:41'),
	(80, 46, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 4, '2025-10-25 19:41:41'),
	(81, 47, 1, 2, 1, 1, 2, 1, 2, 3, NULL, 4, '2025-10-25 19:41:41'),
	(82, 48, 3, 4, 3, 3, 3, 2, 2, 2, NULL, 4, '2025-10-25 19:41:41'),
	(83, 49, 9, 9, 10, 8, 9, 8, 10, 9, NULL, 5, '2025-10-25 19:41:41'),
	(84, 50, 4, 9, 5, 5, 5, 3, 7, 3, NULL, 5, '2025-10-25 19:41:41'),
	(85, 51, 1, 2, 1, 1, 1, 1, 1, 1, NULL, 4, '2025-10-25 19:41:41'),
	(86, 52, 2, 1, 1, 1, 1, 1, 1, 1, NULL, 4, '2025-10-25 19:41:41'),
	(87, 53, 6, 5, 6, 7, 6, 4, 6, 5, NULL, 5, '2025-10-25 19:41:41'),
	(88, 54, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 4, '2025-10-25 19:41:41'),
	(89, 55, 1, 1, 1, 1, 2, 2, 3, 3, NULL, 4, '2025-10-25 19:41:41'),
	(90, 56, 6, 8, 7, 9, 6, 7, 5, 5, NULL, 5, '2025-10-25 19:41:41'),
	(91, 57, 4, 2, 3, 4, 3, 3, 3, 1, NULL, 4, '2025-10-25 19:41:41'),
	(92, 58, 1, 2, 1, 1, 1, 2, 2, 2, NULL, 4, '2025-10-25 19:41:41'),
	(93, 59, 4, 5, 3, 5, 2, 5, 7, 7, NULL, 5, '2025-10-25 19:41:41'),
	(94, 60, 5, 8, 9, 7, 5, 6, 8, 4, NULL, 5, '2025-10-25 19:41:41'),
	(95, 61, 3, 3, 2, 2, 3, 3, 2, 2, NULL, 4, '2025-10-25 19:41:41'),
	(96, 62, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 4, '2025-10-25 19:41:41'),
	(97, 63, 3, 3, 3, 5, 1, 1, 5, 3, NULL, 4, '2025-10-25 19:41:41'),
	(98, 64, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 4, '2025-10-25 19:41:41'),
	(99, 65, 3, 8, 5, 3, 6, 2, 6, 4, NULL, 5, '2025-10-25 19:41:41'),
	(100, 66, 6, 5, 5, 8, 6, 4, 6, 5, NULL, 5, '2025-10-25 19:41:41'),
	(101, 67, 1, 1, 1, 1, 1, 2, 1, 1, NULL, 4, '2025-10-25 19:41:41'),
	(102, 68, 6, 9, 2, 4, 4, 5, 8, 5, NULL, 5, '2025-10-25 19:41:41');

-- Dumping structure for table db_asima.users_m
CREATE TABLE IF NOT EXISTS `users_m` (
  `id` int NOT NULL AUTO_INCREMENT,
  `npm` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nama_lengkap` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `fakultas` varchar(20) NOT NULL,
  `prodi` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nim` (`npm`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_asima.users_m: ~50 rows (approximately)
INSERT INTO `users_m` (`id`, `npm`, `nama_lengkap`, `fakultas`, `prodi`, `password`) VALUES
	(19, '2106700001', 'Fazrian Dwiana ', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(20, '2106700002', 'Kharisma Aulia NI', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(21, '2160700084', 'Deni Ridwan ', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(22, '2106700004', 'Yela Aitna Fatanan', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(23, '2269700034', 'Cindy Silvia Nuraeni ', 'Fakultas Teknik', 'Teknik Komputer Dan Jaringan', '123456'),
	(24, '2306700024', 'Syaiful Mukmin', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(25, '2306700027', 'Prima Kharisma G', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(26, '2106700022', 'Krisna Tri Wahyudin', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(27, '2106700008', 'Nindya Ayu Fadillah ', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(28, '2106700005', 'Nurul Hanifah', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(29, '2106700015', 'Dinda Nur Fitriani', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(30, '2306700071', 'Didan Sihab Robbani', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(31, '2306700007', 'Subagja Adiguna', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(32, '2406700058', 'Kzhiu Elharie', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(33, '2206700027', 'Dhea Fitri Setianingrum', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(34, '2106700018', 'Azhar Gumilar Pratama', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(35, '2306700082', 'Fadhillah Fahreza', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(36, '2306700006', 'Fahmi Faadhillah', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(37, '2306700061', 'muhammad ridho russardi', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(38, '2306700039', 'Mochamad Pradhana AP', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(39, '2406700049', 'Naufal Dery Fadhillah ', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(40, '2469700025', 'Dimas Pangestu Husaeni', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(41, '2469700006', 'Deta Bayu Prabowo ', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(42, '2306700009', 'Rizky adhitya rukmana', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(43, '2106700020', 'Rini Rosdianti ', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(44, '2106700036', 'Alfine Ghuna Kurniawan', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(45, '2111700015', 'Siska Amalia Junianti', 'Fakultas Teknik', 'Sistem Informasi', '123456'),
	(46, '2106700030', 'Fa\'ul Rizal Medika Fadli ', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(47, '2106700017', 'Jasika Putri', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(48, '2269700042', 'Tegar wahid alfasa', 'Fakultas Teknik', 'Teknik Komputer Dan Jaringan', '123456'),
	(49, '2211700005', 'Maulana abdul azis', 'Fakultas Teknik', 'Sistem Informasi', '123456'),
	(50, '2206700015', 'Bagus Nurlana', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(51, '2211700020', 'Selsa Fetra Apriliyani', 'Fakultas Teknik', 'Sistem Informasi', '123456'),
	(52, '2106700011', 'Kiki Sukwandi', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(53, '2106700013', 'Agi Rivaldy', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(54, '2106700023', 'Annisa Vira Nurhaliza ', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(55, '2106700071', 'Fuji Muhammad Ikhwan ', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(56, '2206700011', 'Regina maydilla', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(57, '2411700026', 'Tiana Oktaviani ', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(58, '2306700053', 'Faisal Akhbar', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(59, '2206700002', 'Salma Oktarina', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(60, '2311700003', 'Reffa Simla Melati Putri', 'Fakultas Teknik', 'Sistem Informasi', '123456'),
	(61, '2106700024', 'M. Alvin Mauliddin ', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(62, '2106700006', 'Fujiawati Astuti', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(63, '2106700073', 'Rahmat Subagja', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(64, '2106700068', 'Salsa Nur Avifah', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(65, '2106700019', 'Bima Pratama ', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(66, '2306700085', 'Indriyani', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(67, '2106700012', 'Vania Annisa Aulia', 'Fakultas Teknik', 'Teknik Informatika', '123456'),
	(68, '2106700014', 'Alva Radian', 'Fakultas Teknik', 'Teknik Informatika', '123456');

-- Dumping structure for table db_asima.user_questions_log
CREATE TABLE IF NOT EXISTS `user_questions_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `question_text` text NOT NULL,
  `response_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_m` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_asima.user_questions_log: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
