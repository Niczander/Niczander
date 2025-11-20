-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 19, 2025 at 09:33 AM
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
-- Database: `finalweb`
--

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
CREATE TABLE IF NOT EXISTS `branches` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `district` varchar(120) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `opening_hours` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `district`, `address`, `phone`, `opening_hours`) VALUES
(6, 'Entebbe branch', 'Entebbe', '0774562722', '0774562722', '14'),
(5, 'Nakawa-Ntinda branch', 'kampala', '0742505141', '0742505141', '24-7'),
(4, 'Nsambya branch', 'kampala', '0760845198', '0760845198', '12'),
(7, 'kawempe', 'Kawempe North', '0774562722', '0774562722', '14');

-- --------------------------------------------------------

--
-- Table structure for table `collections`
--

DROP TABLE IF EXISTS `collections`;
CREATE TABLE IF NOT EXISTS `collections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `reason` varchar(80) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `reason`, `message`, `created_at`) VALUES
(1, 'atibu', '', '0761097443', '', 'Orders & Delivery', 'well delivered', '2025-11-13 15:48:21'),
(2, 'atibu', '', '0761097443', '', 'Suppliers & Partnerships', 'can you make a branch in mbarara we also get a chance to get quality products', '2025-11-14 04:10:06');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_code` varchar(20) NOT NULL,
  `customer_name` varchar(120) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `payment_method` enum('MTN_MOMO','AIRTEL_MONEY','VISA') NOT NULL,
  `payment_reference` varchar(50) DEFAULT NULL,
  `status` enum('pending','awaiting_mobile_money','paid','failed','cancelled') NOT NULL DEFAULT 'pending',
  `total_amount` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_code` (`order_code`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `customer_name`, `customer_phone`, `customer_email`, `payment_method`, `payment_reference`, `status`, `total_amount`, `created_at`, `updated_at`) VALUES
(1, 'UHS-3FFA7BB3', 'atibu', '0761097443', 'wefamit417@fogdiver.com', 'AIRTEL_MONEY', NULL, 'awaiting_mobile_money', 250000, '2025-11-14 04:19:12', '2025-11-14 05:42:19'),
(2, 'UHS-E10F59C2', 'masiko nicholas', '0742505141', NULL, 'AIRTEL_MONEY', 'AIRTEL_MONEY-20251114060616-e42c66', 'paid', 250000, '2025-11-14 06:05:27', '2025-11-14 06:06:16');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `price` int NOT NULL,
  `qty` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `name`, `price`, `qty`) VALUES
(1, 1, 1, 'SanDisk 1TB Extreme SSD', 250000, 1),
(2, 2, 1, 'SanDisk 1TB Extreme SSD', 250000, 1);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `excerpt` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `excerpt`, `image_url`, `created_at`) VALUES
(3, 'XMASS bonanza', 'Get yourself goods at Uhome arenas for the festival season at a discount of 20%', '/finalweb/assets/uploads/post_1763108131_589ec7ce.jpg', '2025-11-14 08:15:31'),
(2, 'New Cargo landed home safely', 'your orders are valued with quality deliveries. Run for the new cargo', '/finalweb/assets/uploads/post_1763104037_f23cee2b.jpg', '2025-11-14 07:07:17'),
(4, 'Electronics sale off', 'Get yourself quality electronics through this season at a least price', '/finalweb/assets/uploads/post_1763108693_9be5881b.jpg', '2025-11-14 08:24:53');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `price` int NOT NULL DEFAULT '0',
  `image_url` varchar(255) NOT NULL,
  `collection_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_metrics`
--

DROP TABLE IF EXISTS `site_metrics`;
CREATE TABLE IF NOT EXISTS `site_metrics` (
  `metric_key` varchar(50) NOT NULL,
  `metric_value` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`metric_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `site_metrics`
--

INSERT INTO `site_metrics` (`metric_key`, `metric_value`) VALUES
('Products', 1),
('Branches', 3),
('Years of service', 3);

-- --------------------------------------------------------

--
-- Table structure for table `slides`
--

DROP TABLE IF EXISTS `slides`;
CREATE TABLE IF NOT EXISTS `slides` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `subtitle` varchar(255) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `slides`
--

INSERT INTO `slides` (`id`, `title`, `subtitle`, `image_url`, `video_url`) VALUES
(4, 'Electronics', 'quality and durability', '/finalweb/assets/uploads/slide_1763102941_26e441a6.jpg', NULL),
(5, 'Home Goods', 'Beautiful and impressive stay home', '/finalweb/assets/uploads/slide_1763107447_da57687b.jpg', NULL),
(6, 'Furniture at the peak', 'Top quality furniture found at Uhome supermarkets only', '/finalweb/assets/uploads/slide_1763109955_1b5445d7.jpg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'admin', '$2y$10$uh7z2b.EhgZT0LXHPcmDjuVnMcF6XUZ4MqaarJfpe7rXZh4G14f7a', '2025-11-13 14:49:49'),
(2, 'havertz', '$2y$10$Pt7kr3fk8/LxnwVAkZ3I8eiMoRTs2DOPCKxtN73JY/7Qv0wq6E6LG', '2025-11-13 16:19:28');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
