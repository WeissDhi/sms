-- phpMyAdmin SQL Dump
-- version 5.2.1
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 08, 2025 at 10:34 AM

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: `blog_db`
-- --------------------------------------------------------

-- Table structure for table `admin`
DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `admin` (`id`, `first_name`, `last_name`, `username`, `password`) VALUES
(1, 'Admin', '', 'admin', 'admin123');

-- Table structure for table `penulis`
DROP TABLE IF EXISTS `penulis`;
CREATE TABLE IF NOT EXISTS `penulis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fname` varchar(255) NOT NULL,
  `username` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
);

INSERT INTO `penulis` (`id`, `fname`, `username`, `password`) VALUES
(3, 'Khalid Jemal', 'khalid', '$2y$10$LoZNyJVQpBu/M7BEQdUmlOVVXaV65TxZwLAFejNBdD5a/JxjJAEwG'),
(6, 'John Jr', 'jr_john', '$2y$10$KpVvp9ixSCn/9FMR3k0tn.0Oul5lf2jGaCGPOgKyyxQTdyMk8xtlG'),
(7, 'tes', 'tes', '$2y$10$afa9//nv4BGru.yJ3RvI/O7VZcUm9RtSh1vQdIngJLWovX8ba8jgK'),
(8, 'Ibra Zaki', 'ibra', 'ibra123'),
(9, 'Hanif Penjudi', 'hanif', 'hanif123'),
(12, 'laahiq', 'laahiq', 'laahiq123'),
(17, 'Teuku ARdhi', 'ardhi', 'ardhi123');

-- Table structure for table `blogs`
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
);

-- Table structure for table `category`
DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(127) NOT NULL,
  `parent_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
);

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

-- Table structure for table `pengguna`
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
);

-- Table structure for table `saved_articles`
DROP TABLE IF EXISTS `saved_articles`;
CREATE TABLE IF NOT EXISTS `saved_articles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pengguna_id` int NOT NULL,
  `blog_id` int NOT NULL,
  `saved_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pengguna_id` (`pengguna_id`),
  KEY `blog_id` (`blog_id`),
  CONSTRAINT `fk_saved_pengguna` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_saved_blog` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE
);

-- Table structure for table `comment`
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
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `fk_comment_penulis` FOREIGN KEY (`penulis_id`) REFERENCES `penulis` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comment_pengguna` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
);

-- Table structure for table `documents`
DROP TABLE IF EXISTS `documents`;
CREATE TABLE IF NOT EXISTS `documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `blog_id` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `blog_id` (`blog_id`)
);

-- Table structure for table `post`
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
);

-- Table structure for table `post_like`
DROP TABLE IF EXISTS `post_like`;
CREATE TABLE IF NOT EXISTS `post_like` (
  `like_id` int NOT NULL AUTO_INCREMENT,
  `liked_by` int NOT NULL,
  `post_id` int NOT NULL,
  `liked_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`like_id`)
);

-- Constraints for table `category`
ALTER TABLE `category`
  ADD CONSTRAINT `category_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `category` (`id`) ON DELETE CASCADE;

COMMIT;
