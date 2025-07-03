-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 27, 2025 at 07:51 AM
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
-- Database: `pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `inventory_logs`
--

DROP TABLE IF EXISTS `inventory_logs`;
CREATE TABLE IF NOT EXISTS `inventory_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `action` varchar(50) NOT NULL,
  `quantity` int NOT NULL,
  `old_stock` int NOT NULL,
  `new_stock` int NOT NULL,
  `user` varchar(50) DEFAULT 'Admin',
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `inventory_logs`
--

INSERT INTO `inventory_logs` (`id`, `product_id`, `action`, `quantity`, `old_stock`, `new_stock`, `user`, `timestamp`) VALUES
(1, 4, 'Stock Added', 20, 10, 30, 'Admin', '2025-06-25 01:11:37'),
(2, 1, 'Stock Added', 10, 96, 106, 'Admin', '2025-06-25 01:12:56'),
(3, 2, 'Stock Added', 20, 40, 60, 'Admin', '2025-06-25 01:13:04'),
(4, 3, 'Stock Added', 10, 15, 25, 'Admin', '2025-06-25 01:13:12'),
(5, 5, 'Stock Added', 10, 15, 25, 'Admin', '2025-06-25 01:13:18'),
(6, 6, 'Stock Added', 10, 8, 18, 'Admin', '2025-06-25 01:13:34'),
(7, 6, 'Stock Added', 10, 18, 28, 'Admin', '2025-06-25 01:15:41'),
(8, 6, 'Stock Added', 10, 28, 38, 'Admin', '2025-06-25 01:23:04'),
(9, 6, 'Stock Added', 10, 38, 48, 'Admin', '2025-06-25 01:28:41'),
(10, 6, 'Stock Added', 10, 48, 58, 'Admin', '2025-06-25 01:29:07'),
(11, 5, 'Stock Added', 10, 25, 35, 'Admin', '2025-06-25 01:31:10'),
(12, 7, 'Stock Added', 28, 12, 40, 'Admin', '2025-06-25 02:16:39'),
(13, 7, 'Stock Added', 28, 40, 68, 'Admin', '2025-06-25 02:17:30'),
(14, 7, 'Stock Added', 28, 68, 96, 'Admin', '2025-06-25 02:21:39'),
(15, 7, 'Stock Added', 28, 96, 124, 'Admin', '2025-06-25 02:23:17'),
(16, 7, 'Stock Added', 28, 124, 152, 'Admin', '2025-06-25 02:24:10'),
(17, 7, 'Stock Added', 28, 152, 180, 'Admin', '2025-06-25 02:24:49'),
(18, 7, 'Stock Added', 28, 180, 208, 'Admin', '2025-06-25 02:25:45'),
(19, 8, 'Stock Added', 8, 12, 20, 'Admin', '2025-06-25 02:41:54'),
(20, 8, 'Stock Added', 8, 20, 28, 'Admin', '2025-06-25 02:42:03'),
(21, 8, 'Stock Added', 8, 28, 36, 'Admin', '2025-06-25 02:44:25'),
(22, 8, 'Stock Added', 8, 36, 44, 'Admin', '2025-06-25 02:51:02'),
(23, 8, 'Stock Added', 6, 44, 50, 'Admin', '2025-06-25 03:03:32'),
(24, 6, 'Supplied', 10, 58, 68, 'Admin', '2025-06-25 21:29:11'),
(25, 10, 'Stock Added', 5, 10, 15, 'Admin', '2025-06-27 08:17:13'),
(26, 10, 'Supplied', 5, 15, 20, 'Admin', '2025-06-27 08:20:16');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `customer_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `customer_phone` int NOT NULL,
  `quantity` int NOT NULL,
  `payment_method` enum('Mpesa','Cash','Unpaid','') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `discounts` int NOT NULL,
  `status` enum('delivered','cancelled','pending') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=341 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `product_id`, `customer_name`, `customer_phone`, `quantity`, `payment_method`, `user_id`, `created_at`, `discounts`, `status`) VALUES
(288, 4, 'justin', 432432432, 3, 'Mpesa', 1, '2025-06-23 13:42:27', 10, 'delivered'),
(287, 3, 'lik', 76567654, 54, 'Unpaid', 1, '2025-06-22 20:41:38', 0, 'delivered'),
(286, 2, 'rishi', 2147483647, 34, 'Mpesa', 1, '2025-06-22 20:40:45', 34, 'delivered'),
(285, 1, 'rer', 16789089, 11, '', 1, '2025-06-22 20:38:19', 0, 'pending'),
(284, 1, 'kiki', 16789089, 11, '', 1, '2025-06-22 20:30:03', 0, 'pending'),
(283, 4, 'viji', 16789089, 11, '', 1, '2025-06-22 20:29:28', 0, 'pending'),
(282, 4, 'viji', 16789089, 11, 'Cash', 1, '2025-06-22 20:27:24', 0, 'pending'),
(281, 2, 'palmer', 11111111, 11, 'Cash', 1, '2025-06-22 19:55:35', 10, 'pending'),
(280, 2, 'vishi snaku', 32132111, 12, 'Mpesa', 1, '2025-06-22 19:50:35', 0, 'cancelled'),
(279, 1, 'sunak', 32132111, 12, 'Cash', 1, '2025-06-22 19:50:15', 0, 'delivered'),
(278, 1, 'sunak', 32132111, 12, '', 1, '2025-06-22 19:47:13', 0, 'cancelled'),
(289, 3, 'Blake', 0, 0, 'Unpaid', 0, '2025-06-24 10:25:45', 0, 'cancelled'),
(290, 4, 'Musa', 0, 0, 'Cash', 0, '2025-06-24 10:28:16', 0, 'cancelled'),
(291, 1, 'Slade', 2147483647, 12, 'Cash', 1, '2025-06-24 15:49:50', 0, 'delivered'),
(292, 8, 'Addi', 32132111, 20, 'Cash', 1, '2025-06-25 10:10:01', 0, 'delivered'),
(293, 1, 'MT', 2121212121, 1, 'Cash', 1, '2025-06-26 07:43:13', 0, 'pending'),
(294, 1, 'Tim', 1111122222, 1, 'Cash', 1, '2025-06-26 07:53:36', 0, 'delivered'),
(295, 2, 'Tim', 1111122222, 1, 'Cash', 1, '2025-06-26 07:53:36', 0, 'delivered'),
(296, 4, 'Tim', 1111122222, 1, 'Cash', 1, '2025-06-26 07:53:36', 0, 'delivered'),
(297, 2, 'Kim', 2147483647, 1, 'Mpesa', 1, '2025-06-26 20:49:32', 0, 'pending'),
(298, 9, 'Kim', 2147483647, 1, 'Mpesa', 1, '2025-06-26 20:49:32', 0, 'pending'),
(299, 2, 'Kim', 1212121212, 1, 'Cash', 1, '2025-06-26 20:50:11', 0, 'pending'),
(300, 2, 'TJ', 2147483647, 1, 'Cash', 1, '2025-06-26 20:51:25', 0, 'pending'),
(301, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:13:59', 0, 'cancelled'),
(302, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:00', 0, 'cancelled'),
(303, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:01', 0, 'cancelled'),
(304, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:04', 0, 'cancelled'),
(305, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:04', 0, 'cancelled'),
(306, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:04', 0, 'cancelled'),
(307, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:05', 0, 'cancelled'),
(308, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:05', 0, 'cancelled'),
(309, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:05', 0, 'cancelled'),
(310, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:05', 0, 'cancelled'),
(311, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:05', 0, 'cancelled'),
(312, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:06', 0, 'cancelled'),
(313, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:06', 0, 'cancelled'),
(314, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:06', 0, 'cancelled'),
(315, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:06', 0, 'cancelled'),
(316, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:06', 0, 'cancelled'),
(317, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:07', 0, 'cancelled'),
(318, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:07', 0, 'cancelled'),
(319, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:07', 0, 'cancelled'),
(320, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:07', 0, 'cancelled'),
(321, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:09', 0, 'cancelled'),
(322, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:10', 0, 'cancelled'),
(323, 0, 'Kim', 2147483647, 0, 'Cash', 1, '2025-06-26 21:14:10', 0, 'cancelled'),
(324, 8, 'Kim', 2147483647, 2, 'Cash', 1, '2025-06-26 21:14:23', 0, 'pending'),
(325, 4, 'Kim', 2147483647, 4, 'Cash', 1, '2025-06-26 21:14:23', 0, 'pending'),
(326, 9, 'Don', 2147483647, 2, 'Cash', 1, '2025-06-27 05:13:41', 0, 'pending'),
(327, 4, 'Mwithalie', 454545445, 1, 'Cash', 1, '2025-06-27 05:22:29', 0, 'delivered'),
(328, 1, 'Joy', 2147483647, 1, 'Cash', 1, '2025-06-27 05:27:08', 0, 'delivered'),
(329, 1, 'Joy', 2147483647, 1, 'Cash', 1, '2025-06-27 05:27:08', 0, 'delivered'),
(330, 1, 'Don', 2147483647, 2, 'Mpesa', 1, '2025-06-27 05:28:43', 0, 'pending'),
(331, 2, 'Don', 46657575, 2, 'Cash', 1, '2025-06-27 05:30:44', 0, 'delivered'),
(332, 1, 'Wang', 567567567, 1, 'Cash', 1, '2025-06-27 05:44:40', 0, 'delivered'),
(333, 10, 'Wang', 567567567, 1, 'Cash', 1, '2025-06-27 05:44:40', 0, 'delivered'),
(334, 1, 'Lee', 8908908, 5, 'Mpesa', 1, '2025-06-27 05:50:37', 0, 'pending'),
(335, 1, 'Lee', 8908908, 5, 'Cash', 1, '2025-06-27 05:56:10', 0, 'pending'),
(336, 1, 'Su', 65765765, 5, 'Cash', 1, '2025-06-27 05:58:20', 0, 'pending'),
(337, 1, 'Ahmed', 978978978, 5, 'Cash', 1, '2025-06-27 06:00:14', 0, 'delivered'),
(338, 1, 'Ali', 978978978, 5, 'Cash', 1, '2025-06-27 06:01:53', 0, 'delivered'),
(339, 1, 'Ali', 67867867, 5, 'Cash', 1, '2025-06-27 06:05:25', 0, 'delivered'),
(340, 1, 'Yu', 123123123, 5, 'Mpesa', 1, '2025-06-27 06:33:41', 0, 'delivered');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `price` int NOT NULL,
  `stock` int NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT 'placeholder.jpg',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tax` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`product_name`),
  UNIQUE KEY `product_name` (`product_name`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `price`, `stock`, `image`, `created_at`, `tax`) VALUES
(1, 'Coca-Cola 500ml', 100, 95, 'uploads/685d225497467_cc.jpg', '2025-06-19 13:47:39', 10),
(2, 'Redbull', 300, 60, 'uploads/685db1f03c8f1_rb.jpg', '2025-06-19 17:07:13', 15),
(3, 'Milk 1L', 50, 25, 'uploads/685db427d4a80_m.jpg', '2025-06-19 17:07:13', 10),
(4, 'Bison Grass', 150, 30, 'uploads/685db55347bc1_BG.jpg', '2025-06-19 17:07:13', 15),
(5, 'Mixed Fruit', 100, 35, 'uploads/685db61a83dcd_fs.jpg', '2025-06-19 17:07:13', 10),
(6, '5pc Cup Cakes', 200, 68, 'uploads/685db712e5c90_cap.jpg', '2025-06-19 17:07:13', 10),
(7, 'Monster 300ml', 200, 208, 'uploads/685db6c8ba8c9_mons.jpg', '2025-06-19 17:07:13', 10),
(8, 'Punch', 50, 50, 'uploads/685db5bd69879_FP.jpg', '2025-06-19 18:56:58', 5),
(9, 'Guiness', 260, 50, 'uploads/685db08e33b8e_gino.jpg', '2025-06-26 07:57:17', 5),
(10, 'Sprite', 100, 19, 'uploads/685e2d9a12705_sp.jpg', '2025-06-27 05:15:52', 2);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `company_name` varchar(100) NOT NULL,
  `address` text,
  `notes` text,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `supplier_name`, `contact_person`, `email`, `phone`, `company_name`, `address`, `notes`, `status`, `created_at`) VALUES
(1, 'Rubicon', 'Odoyo Jewell', 'jewkajwang@gmail.com', '0791979364', 'RUBICON LTD.', '50424', 'Supplies Motor Spare Parts and Ammunition', 'Active', '2025-06-25 08:26:19'),
(2, 'Kenya Beverages', 'Ty', 'ty@gmail.com', '0799998888', 'KENYA BEVERAGES', '01001', 'Beverages Supplies', 'Active', '2025-06-25 11:46:12'),
(3, 'Kenya Bakeries', 'Mike', 'mike@gmail.com', '0799998888', 'KENYA BAKERIES', '012200', 'For fresh bakes', 'Active', '2025-06-25 16:49:21'),
(4, 'Don', 'Don', 'doni@kcau.ac.ke', '0799998888', 'DON CO.', '232323', 'Tech Supplies', 'Active', '2025-06-27 05:19:05');

-- --------------------------------------------------------

--
-- Table structure for table `supply_invoices`
--

DROP TABLE IF EXISTS `supply_invoices`;
CREATE TABLE IF NOT EXISTS `supply_invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `supplier_id` int NOT NULL,
  `invoice_date` date NOT NULL,
  `total_amount` int NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `supply_invoices`
--

INSERT INTO `supply_invoices` (`id`, `invoice_number`, `supplier_id`, `invoice_date`, `total_amount`, `created_by`, `created_at`) VALUES
(1, '1', 2, '2025-06-25', 0, NULL, '2025-06-25 11:57:20'),
(2, '2', 2, '2025-06-25', 0, NULL, '2025-06-25 16:34:26'),
(3, '3', 3, '2025-06-25', 0, NULL, '2025-06-25 16:49:37'),
(4, '4', 3, '2025-06-25', 0, NULL, '2025-06-25 16:50:43'),
(5, '5', 3, '2025-06-25', 0, NULL, '2025-06-25 18:14:46'),
(6, '5', 3, '2025-06-25', 2000, 'Admin', '2025-06-25 18:28:12'),
(7, '5', 3, '2025-06-25', 2000, 'Admin', '2025-06-25 18:29:11'),
(8, '6', 4, '2025-06-27', 1000000, 'Admin', '2025-06-27 05:20:16');

-- --------------------------------------------------------

--
-- Table structure for table `supply_invoice_items`
--

DROP TABLE IF EXISTS `supply_invoice_items`;
CREATE TABLE IF NOT EXISTS `supply_invoice_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `supply_invoice_items`
--

INSERT INTO `supply_invoice_items` (`id`, `invoice_id`, `product_id`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 7, 6, 10, 200.00, 2000.00),
(2, 8, 10, 5, 200000.00, 1000000.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','cashier') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'riley', 'riley@gmail.com', '$2y$10$gLGIwdFhoG2waW61KvKTyufO2uMVCENT3TEnlQfXOf6gr.JVRGgYe', 'admin', '2025-06-17 11:50:52');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
