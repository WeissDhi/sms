-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 09, 2025 at 03:10 PM
-- Server version: 8.2.0
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sms`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `first_name`, `last_name`, `username`, `password`) VALUES
(1, 'Admin', '', 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

DROP TABLE IF EXISTS `blogs`;
CREATE TABLE IF NOT EXISTS `blogs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `category_id` int DEFAULT NULL,
  `author_id` int DEFAULT NULL,
  `author_type` enum('admin','penulis') DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `views` int NOT NULL DEFAULT '0',
  `slug` varchar(191) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`id`, `title`, `content`, `image`, `created_at`, `category_id`, `author_id`, `author_type`, `status`, `views`, `slug`) VALUES
(16, '<p>asdasdasd</p>', '<p>asdasdasd</p>', '1752072028_cropped_image.jpg', '2025-07-09 14:40:28', 11, 1, 'admin', 'published', 2, 'asdasdasd');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(127) NOT NULL,
  `parent_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `category`, `parent_id`) VALUES
(1, 'Resensi', NULL),
(2, 'Buku', 1),
(3, 'Film', 1),
(4, 'Sastra', NULL),
(5, 'Cerpen', 4),
(6, 'Puisi', 4),
(7, 'Warta', NULL),
(8, 'Opini', NULL),
(9, 'Esai', NULL),
(10, 'Islamologi', NULL),
(11, 'Humaniora', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

DROP TABLE IF EXISTS `comment`;
CREATE TABLE IF NOT EXISTS `comment` (
  `comment_id` int NOT NULL AUTO_INCREMENT,
  `comment` text NOT NULL,
  `penulis_id` int DEFAULT NULL,
  `pengguna_id` int DEFAULT NULL,
  `blog_id` int NOT NULL,
  `parent_id` int DEFAULT NULL,
  `author_type` enum('admin','penulis','pengguna') DEFAULT NULL,
  `status` enum('active','deleted') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`),
  KEY `blog_id` (`blog_id`),
  KEY `penulis_id` (`penulis_id`),
  KEY `pengguna_id` (`pengguna_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comment`
--

INSERT INTO `comment` (`comment_id`, `comment`, `penulis_id`, `pengguna_id`, `blog_id`, `parent_id`, `author_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'tes komentar sebagai penulis', 19, NULL, 12, NULL, 'penulis', 'active', '2025-07-09 02:04:56', NULL),
(2, 'teasasdasd', 19, NULL, 11, NULL, 'penulis', 'active', '2025-07-09 02:07:25', NULL),
(3, 'balas komentar sebagai penulis', 19, NULL, 12, 1, 'penulis', 'active', '2025-07-09 02:09:14', NULL),
(4, 'bamm', 1, NULL, 12, 1, 'admin', 'active', '2025-07-09 02:25:09', NULL),
(5, 'KOMENTAR SEBAGAI USER', NULL, 1, 12, NULL, 'pengguna', 'active', '2025-07-09 02:47:35', NULL),
(6, 'ini gua yang bales', 19, NULL, 12, 5, 'penulis', 'active', '2025-07-09 02:49:44', NULL),
(7, 'lagu lu tong', 1, NULL, 12, 5, 'admin', 'active', '2025-07-09 02:50:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
CREATE TABLE IF NOT EXISTS `documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `blog_id` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `blog_id` (`blog_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `blog_id`, `file_name`, `file_type`, `uploaded_at`) VALUES
(1, 1, '1751994284_Pertemuan4__Teuku_Ardhi_Firmansyah_Al_Ghozali_LSP.pdf', 'application/pdf', '2025-07-08 17:04:44'),
(2, 2, '1751995003_RangkumanNilai__2_.pdf', 'application/pdf', '2025-07-08 17:16:43'),
(3, 6, '1751997201_Pointer_Kasudin_FLS3N_Jenjang_SD_Tahun_2025.pdf', 'application/pdf', '2025-07-08 17:53:21'),
(4, 7, '1751997419_Undangan_Pengarahan_CPNS_2025.pdf', 'application/pdf', '2025-07-08 17:56:59'),
(5, 8, '1751998125_Pertemuan4__Teuku_Ardhi_Firmansyah_Al_Ghozali_LSP.pdf', 'application/pdf', '2025-07-08 18:08:45'),
(6, 9, '1751998580_Pert2_Act1_MochammadKhalishMulyadi_14118187.pdf', 'application/pdf', '2025-07-08 18:16:20'),
(7, 10, '1751999312_Pert2_Act1_MochammadKhalishMulyadi_14118187.pdf', 'application/pdf', '2025-07-08 18:28:32'),
(8, 11, '1752001038_Undangan_Kegiatan_Rakor_KP_7-7-2025.pdf', 'application/pdf', '2025-07-08 18:57:18'),
(9, 12, '1752001200_UTS_MEETING_12__BAHASA_INGGRIS_BISNIS_1-22-01-2025.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2025-07-08 19:00:00'),
(10, 13, '1752001690_Undangan_Rapat_Strakom_dan_Tim_RSS_2025.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2025-07-08 19:08:10'),
(11, 17, '1752072041_Undangan_Pengarahan_CPNS_2025.pdf', 'application/pdf', '2025-07-09 14:40:41'),
(12, 18, '1752072411_Undangan_Fasil_Dinas_Pendidikan.pdf', 'application/pdf', '2025-07-09 14:46:51');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

DROP TABLE IF EXISTS `pengguna`;
CREATE TABLE IF NOT EXISTS `pengguna` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `username` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id`, `nama`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'Pengguna1', 'pengguna', 'ardhi123', 'teukuardhi228@gmail.com', '2025-07-08 18:42:35'),
(2, 'Ahmad Laahiq', 'ahmd', 'ardhi123', 'ahmadlaahiq@gmail.com', '2025-07-08 20:05:45');

-- --------------------------------------------------------

--
-- Table structure for table `penulis`
--

DROP TABLE IF EXISTS `penulis`;
CREATE TABLE IF NOT EXISTS `penulis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fname` varchar(255) NOT NULL,
  `username` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penulis`
--

INSERT INTO `penulis` (`id`, `fname`, `username`, `password`) VALUES
(18, 'Teuku Ardhi', 'teukuardhi', 'ardhi95342607'),
(19, 'penulis ganteng', 'penulis', 'penulis123');

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

DROP TABLE IF EXISTS `post`;
CREATE TABLE IF NOT EXISTS `post` (
  `post_id` int NOT NULL AUTO_INCREMENT,
  `post_title` varchar(127) NOT NULL,
  `post_text` text NOT NULL,
  `category` int NOT NULL,
  `publish` int NOT NULL DEFAULT '1',
  `cover_url` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `crated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_like`
--

DROP TABLE IF EXISTS `post_like`;
CREATE TABLE IF NOT EXISTS `post_like` (
  `like_id` int NOT NULL AUTO_INCREMENT,
  `liked_by` int NOT NULL,
  `post_id` int NOT NULL,
  `liked_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`like_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_articles`
--

DROP TABLE IF EXISTS `saved_articles`;
CREATE TABLE IF NOT EXISTS `saved_articles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pengguna_id` int NOT NULL,
  `blog_id` int NOT NULL,
  `saved_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pengguna_id` (`pengguna_id`),
  KEY `blog_id` (`blog_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saved_articles`
--

INSERT INTO `saved_articles` (`id`, `pengguna_id`, `blog_id`, `saved_at`) VALUES
(1, 1, 13, '2025-07-08 19:53:35'),
(2, 1, 12, '2025-07-08 19:53:47'),
(3, 1, 11, '2025-07-08 19:53:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fname` varchar(255) NOT NULL,
  `username` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fname`, `username`, `password`) VALUES
(3, 'Khalid Jemal', 'khalid', '$2y$10$LoZNyJVQpBu/M7BEQdUmlOVVXaV65TxZwLAFejNBdD5a/JxjJAEwG'),
(6, 'John Jr', 'jr_john', '$2y$10$KpVvp9ixSCn/9FMR3k0tn.0Oul5lf2jGaCGPOgKyyxQTdyMk8xtlG'),
(7, 'tes', 'tes', '$2y$10$afa9//nv4BGru.yJ3RvI/O7VZcUm9RtSh1vQdIngJLWovX8ba8jgK'),
(8, 'Ibra Zaki', 'ibra', 'ibra123'),
(9, 'Hanif Penjudi', 'hanif', 'hanif123'),
(12, 'laahiq', 'laahiq', 'laahiq123'),
(17, 'Teuku ARdhi', 'ardhi', 'ardhi123'),
(18, 'Ahmad Laahiq', 'ahmadlaahiq', 'lahiq123');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
