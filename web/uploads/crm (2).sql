-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 02, 2025 at 07:45 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crm`
--

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `source` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `sales_rep_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sales_rep` (`sales_rep_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `company`, `phone`, `email`, `source`, `created_at`, `sales_rep_id`) VALUES
(1, 'John Carter', 'ABC Co', '70112233', 'john@abc.com', 'website', '2025-11-20 02:49:53', 3),
(2, 'Maya Farhat', 'Maya Designs', '76334455', 'maya@designs.com', 'referral', '2025-11-20 02:49:53', 7),
(3, 'Karim Ali', 'TechSoft', '70889955', 'karim@tech.com', 'instagram', '2025-11-20 02:49:53', 8);

-- --------------------------------------------------------

--
-- Table structure for table `deals`
--

DROP TABLE IF EXISTS `deals`;
CREATE TABLE IF NOT EXISTS `deals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contact_id` int DEFAULT NULL,
  `title` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `value` decimal(10,2) DEFAULT NULL,
  `stage` enum('new','proposal','negotiation','won','lost') COLLATE utf8mb4_general_ci DEFAULT 'new',
  `owner_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deals`
--

INSERT INTO `deals` (`id`, `contact_id`, `title`, `value`, `stage`, `owner_id`, `created_at`) VALUES
(1, 1, 'Website Development Deal', 1500.00, 'proposal', 2, '2025-11-20 02:51:04'),
(2, 2, 'Brand Identity Deal', 900.00, 'negotiation', 3, '2025-11-20 02:51:04'),
(3, 3, 'Mobile App UI/UX', 2000.00, 'won', 2, '2025-11-20 02:51:04');

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE IF NOT EXISTS `groups` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `manager_id` int UNSIGNED NOT NULL,
  `sales_per_id` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_manager` (`manager_id`),
  KEY `fk_salesrep` (`sales_per_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `manager_id`, `sales_per_id`) VALUES
(13, 9, 12),
(11, 2, 7),
(10, 2, 8),
(12, 9, 6),
(8, 9, 3);

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

DROP TABLE IF EXISTS `leads`;
CREATE TABLE IF NOT EXISTS `leads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contact_id` int DEFAULT NULL,
  `title` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('new','contacted','qualified','lost','won') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'new',
  `owner_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `rating` tinyint(1) DEFAULT '0',
  `notes` longtext COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `contact_id`, `title`, `status`, `owner_id`, `created_at`, `rating`, `notes`) VALUES
(1, 1, 'Website Development Inquiry', 'contacted', 2, '2025-11-20 02:50:27', 2, '<p>see doc &nbsp;next</p>'),
(2, 2, 'Branding Package Lead', 'contacted', 3, '2025-11-20 02:50:27', 5, NULL),
(3, 3, 'Mobile App Proposal', 'contacted', 2, '2025-11-20 02:50:27', 3, NULL),
(4, 1, 'crm', 'won', 2, '2025-11-26 12:22:17', 1, NULL),
(5, 1, 'planmanaj', 'lost', 3, '2025-11-26 12:22:17', 0, NULL),
(6, 1, 'yyyyy', 'won', 2, '2025-11-26 12:30:51', 5, NULL),
(8, 1, 'yyytdxxj;pby', 'won', 2, '2025-11-26 12:31:59', 2, NULL),
(9, 1, 'new new ', 'qualified', 3, '2025-11-26 12:31:59', 4, NULL),
(10, 1, 'tit12ljh g6nhydc89 cbdhd67', 'qualified', 2, '2025-11-26 12:32:32', 5, '<p>start net month&nbsp;</p>');

-- --------------------------------------------------------

--
-- Table structure for table `lead_activities`
--

DROP TABLE IF EXISTS `lead_activities`;
CREATE TABLE IF NOT EXISTS `lead_activities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lead_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `type` enum('note','message','call','whatsapp','meeting','document','stage','assignment','system') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'note',
  `title` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `body` longtext COLLATE utf8mb4_general_ci,
  `meta` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lead_created` (`lead_id`,`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lead_activities`
--

INSERT INTO `lead_activities` (`id`, `lead_id`, `user_id`, `type`, `title`, `body`, `meta`, `created_at`) VALUES
(1, 1, 1, 'note', 'call him at 9', 'no', NULL, '2025-12-02 09:11:52'),
(2, 1, 1, 'call', '9', NULL, NULL, '2025-12-02 09:12:38'),
(3, 1, 1, 'message', 'ahmad do that', NULL, NULL, '2025-12-02 09:13:02');

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
CREATE TABLE IF NOT EXISTS `notes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content` text COLLATE utf8mb4_general_ci,
  `created_by` int DEFAULT NULL,
  `related_type` enum('contact','lead','deal') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `related_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `content`, `created_by`, `related_type`, `related_id`, `created_at`) VALUES
(1, 'Client asked for a revised proposal.', 2, 'deal', 1, '2025-11-20 02:51:39'),
(2, 'Lead is highly interested, follow-up needed.', 3, 'lead', 2, '2025-11-20 02:51:39'),
(3, 'Contact requested a demo session.', 2, 'contact', 3, '2025-11-20 02:51:39');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','done','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `assigned_to` int DEFAULT NULL,
  `related_type` enum('contact','lead','deal') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `related_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `assigned_to` (`assigned_to`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `due_date`, `status`, `assigned_to`, `related_type`, `related_id`, `created_at`) VALUES
(1, 'Call John regarding proposal', '2025-11-21', 'pending', 6, 'contact', 1, '2025-11-20 02:52:24'),
(2, 'Send branding samples to Maya', '2025-11-22', 'done', 3, 'lead', 2, '2025-11-20 02:52:24'),
(3, 'Prepare UI sketch for Karim', '2025-11-25', 'done', 2, 'deal', 3, '2025-11-20 02:52:24'),
(4, 'Follow up with Maya about next steps', '2025-11-24', 'cancelled', 7, 'contact', 2, '2025-11-20 02:52:24'),
(15, 'crm', '2025-12-06', 'pending', 6, 'lead', 3, '2025-11-25 11:35:36'),
(16, 'crm plan', '2025-12-06', 'done', 6, 'deal', 1, '2025-11-25 11:36:00'),
(17, 'ali', '2025-12-31', 'cancelled', 6, 'deal', 1, '2025-11-26 10:05:46'),
(18, 'tasks', '2026-01-07', 'pending', 6, 'contact', 1, '2025-11-26 10:06:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('admin','manager','sales') COLLATE utf8mb4_general_ci DEFAULT 'sales',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ustatus` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'offline',
  `last_activity` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `ustatus`, `last_activity`) VALUES
(1, 'douaam', 'douaa@gmail.com', '$2y$12$bGT.vhNPFuvYzST77FVONOdZPNQ5Tb5q7xhTHCLTsJ9T3oM5xNRVy', 'admin', '2025-11-18 11:24:52', 'online', '2025-12-02 06:50:54'),
(2, 'jad', 'jad@gmail.com', '$2y$12$JFEwO.ov7TeGDN7rdLq11OTkoOJdd/VpZhU2tsuTp1ddUBgbmKic.', 'manager', '2025-11-18 11:24:52', 'offline', '2025-11-27 11:13:47'),
(3, 'nour', 'nour@gmail.com', '$2y$12$JS4CCGy743DScwTkuBVKHetkP3uM7Fd0yPC1ysOVKkYHCzsUwDeGu', 'sales', '2025-11-19 17:49:52', 'offline', '2025-11-27 12:59:15'),
(6, 'ali', 'ali@gmail.com', '$2y$12$5Mm2srAn2YPtwGT4Q5ybu.QREXtkfgKsevuNGeYii6z/D79C9ABBa', 'sales', '2025-11-20 06:56:07', 'online', '2025-12-01 08:45:55'),
(7, 'jamal', 'jamal@gmail.com', '$2y$12$1KnA4iYRin3kUFZjgwapb.tGxT/N1fMboOJwfZtSec4/mx7Jd4E8e', 'sales', '2025-11-20 06:56:29', 'offline', NULL),
(8, 'rana', 'rana@gmail.com', '$2y$12$U5YTydEnyE8K2.LiyxTSCO2KPNkkX4qRy0/dJj/dUZoY0qQZFHF0G', 'sales', '2025-11-20 06:56:49', 'online', '0000-00-00 00:00:00'),
(9, 'hsen', 'hsen@gmail.com', '$2y$12$0wghJWr7bz82PeeOIO/4M.ZExDeS01B3O.M3e6j83LSJEkf8MDcGm', 'manager', '2025-11-20 07:00:18', 'offline', NULL),
(12, 'sales', 'sales@gmail.com', '$2y$10$y8iHYHZOL5TyMIuXPePZIur4NhJJ1lrIVNBq6bTu25p/GC8phniPm', 'sales', '2025-11-27 13:01:19', 'offline', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
