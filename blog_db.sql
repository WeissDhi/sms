DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `first_name`, `last_name`, `username`, `password`) VALUES
(2, 'Elias', 'A', 'admin', 'admin123');

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
  `author_type` enum('admin','user') DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `views` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `blogs`
------------------------

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(127) COLLATE utf8mb4_general_ci NOT NULL,
  `parent_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `comment` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int NOT NULL,
  `post_id` int NOT NULL,
  `crated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comment`
--

INSERT INTO `comment` (`comment_id`, `comment`, `user_id`, `post_id`, `crated_at`) VALUES
(21, 'good', 4, 14, '2023-07-21 19:04:30'),
(22, 'thanks', 4, 12, '2023-07-21 19:04:46'),
(23, 'new comment', 2, 14, '2023-07-21 19:05:16'),
(24, 'Nice', 2, 14, '2023-07-21 19:05:20'),
(25, 'thanks', 2, 12, '2023-07-21 19:05:56'),
(26, 'thanks', 2, 15, '2023-07-21 19:11:38');

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

DROP TABLE IF EXISTS `post`;
CREATE TABLE IF NOT EXISTS `post` (
  `post_id` int NOT NULL AUTO_INCREMENT,
  `post_title` varchar(127) COLLATE utf8mb4_general_ci NOT NULL,
  `post_text` text COLLATE utf8mb4_general_ci NOT NULL,
  `category` int NOT NULL,
  `publish` int NOT NULL DEFAULT '1',
  `cover_url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'default.jpg',
  `crated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`post_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_like`
--

INSERT INTO `post_like` (`like_id`, `liked_by`, `post_id`, `liked_at`) VALUES
(61, 4, 14, '2023-07-21 19:04:18'),
(62, 4, 13, '2023-07-21 19:04:22'),
(63, 4, 12, '2023-07-21 19:04:24'),
(64, 2, 14, '2023-07-21 19:05:10'),
(66, 2, 12, '2023-07-21 19:05:47'),
(68, 2, 15, '2023-07-21 19:11:30'),
(69, 6, 15, '2023-07-21 19:17:00'),
(70, 6, 14, '2023-07-21 19:17:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fname` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fname`, `username`, `password`) VALUES
(2, 'Jhon Doe', 'john', '$2y$10$5KdCaBOhNE6HZOmn39jO4OOyKUI1xC1St51KH8DhtXGX8drct98/q'),
(3, 'Khalid Jemal', 'khalid', '$2y$10$LoZNyJVQpBu/M7BEQdUmlOVVXaV65TxZwLAFejNBdD5a/JxjJAEwG'),
(6, 'John Jr', 'jr_john', '$2y$10$KpVvp9ixSCn/9FMR3k0tn.0Oul5lf2jGaCGPOgKyyxQTdyMk8xtlG'),
(7, 'tes', 'tes', '$2y$10$afa9//nv4BGru.yJ3RvI/O7VZcUm9RtSh1vQdIngJLWovX8ba8jgK'),
(8, 'Ibra Zaki', 'ibra', 'ibra123'),
(9, 'Hanif Penjudi', 'hanif', 'hanif123'),
(10, 'ardhi', 'Teuku Ardh', '$2y$10$h8dM9ED.iKJZq.f7rwHwpOvVRws86s2DVOJNnCRzPs1VY4NfeosxC'),
(11, 'Teuku Ardhi', 'ardhi', '$2y$10$ZSiZ/wXzvoXtSmBod1992eI5cB1o8rra4805nFk6IWuZHXPCh0LrS'),
(12, 'laahiq', 'laahiq', 'laahiq123');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `category`
--
ALTER TABLE `category`
  ADD CONSTRAINT `category_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `category` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
