-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 29, 2025 at 07:34 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `osbuzz`
--
CREATE DATABASE IF NOT EXISTS `osbuzz` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `osbuzz`;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `size` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `quantity`, `size`, `created_at`, `updated_at`) VALUES
(9, 18, 1, 1, '36', '2025-08-26 17:31:59', '2025-08-26 17:31:59');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `category_slug` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`, `category_slug`, `description`, `banner_image`, `created_at`) VALUES
(1, 'Running', 'running', 'Professional running shoes for athletes and fitness enthusiasts', 'running-banner.svg', '2025-08-28 16:05:36'),
(2, 'Casual', 'casual', 'Comfortable casual shoes for everyday wear', '2-1756482111.jpg', '2025-08-28 16:05:36'),
(3, 'Formal', 'formal', 'Elegant formal shoes for business and special occasions', 'formal-banner.svg', '2025-08-28 16:05:36'),
(4, 'Basketball', 'basketball', 'High-performance basketball shoes for court sports', '4-1756482725.jpg', '2025-08-28 16:05:36'),
(5, 'Other', 'other', 'Various other styles and specialty footwear', 'other-banner.svg', '2025-08-28 16:05:36'),
(6, 'Soccer', 'soccer', 'Professional soccer cleats and football boots', '6-1756406257.jpg', '2025-08-28 16:29:37'),
(7, 'Lifestyle', 'lifestyle', 'Trendy lifestyle sneakers for fashion-conscious individuals', '7-1756405346.webp', '2025-08-28 16:29:37'),
(8, 'Kids', 'kids', 'Comfortable and durable shoes designed specifically for children', 'kids-banner.svg', '2025-08-28 16:29:37'),
(9, 'Sandals', 'sandals', 'Comfortable sandals and slides for casual wear', NULL, '2025-08-28 16:29:37');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(7, 33, 'tanes-wp23@student.tarc.edu.my', 'a80c7735e1db98f5f13f9a7864fdc62a7a20d98d517af91a655792170e14e09c', '2025-08-30 01:36:49', '2025-08-29 17:31:49');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT 'Product visibility status: active=visible to users, inactive=hidden from users'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_name`, `brand`, `category_id`, `price`, `description`, `photo`, `created_at`, `status`) VALUES
(1, 'Way Of Wade 10', 'Lining', 4, 699.00, '', '68addf803e726.jpg', '2025-08-10 15:32:28', 'active'),
(2, 'Ultraboost', 'Adidas', 3, 459.00, 'Comfortable running shoes', NULL, '2025-08-10 15:32:28', 'inactive'),
(3, 'Classic Sneakers', 'Nike', 4, 399.00, 'Classic style everyday sneakers', NULL, '2025-08-10 15:32:28', 'active'),
(4, 'Kids Runner', 'Puma', 5, 199.00, 'Lightweight kids running shoes', NULL, '2025-08-10 15:32:28', 'inactive'),
(21, 'Air Max 270', 'Nike', 1, 899.00, 'Nike Air Max 270 features a large Air unit heel for maximum comfort and style. Perfect for running and casual wear.', 'nike_airmax270.jpg', '2025-08-15 02:00:00', 'active'),
(22, 'Gel-Kayano 30', 'ASICS', 1, 1199.00, 'ASICS Gel-Kayano 30 provides superior stability and cushioning for long-distance running.', 'asics_gelkayano30.jpg', '2025-08-16 03:30:00', 'active'),
(23, 'Fresh Foam X', 'New Balance', 1, 799.00, 'New Balance Fresh Foam X delivers plush comfort with responsive energy return.', 'nb_freshfoam.jpg', '2025-08-17 01:15:00', 'active'),
(24, 'Stan Smith', 'Adidas', 2, 599.00, 'The iconic Adidas Stan Smith - a timeless classic that never goes out of style.', 'adidas_stansmith.jpg', '2025-08-18 06:20:00', 'active'),
(25, 'Chuck Taylor All Star', 'Converse', 2, 449.00, 'Classic Converse Chuck Taylor All Star high-top sneakers. A cultural icon since 1917.', 'converse_chuck.jpg', '2025-08-19 08:45:00', 'active'),
(26, 'Old Skool', 'Vans', 2, 389.00, 'Vans Old Skool - the original skate shoe with the iconic side stripe.', 'vans_oldskool.jpg', '2025-08-20 04:10:00', 'active'),
(27, 'Oxford Classic', 'Clarks', 3, 1299.00, 'Premium leather Oxford shoes perfect for business meetings and formal occasions.', 'clarks_oxford.jpg', '2025-08-21 00:30:00', 'active'),
(28, 'Derby Elegance', 'Cole Haan', 3, 1599.00, 'Sophisticated Derby shoes combining traditional craftsmanship with modern comfort.', 'colehaan_derby.jpg', '2025-08-22 07:00:00', 'active'),
(29, 'LeBron 21', 'Nike', 4, 1399.00, 'Nike LeBron 21 - engineered for explosive performance on the basketball court.', 'nike_lebron21.jpg', '2025-08-23 05:25:00', 'active'),
(30, 'Dame 8', 'Adidas', 4, 1199.00, 'Adidas Dame 8 - Damian Lillard signature shoe with responsive Bounce cushioning.', 'adidas_dame8.jpg', '2025-08-24 02:50:00', 'active'),
(31, 'Way of Wade 11', 'Li-Ning', 4, 799.00, 'Li-Ning Way of Wade 11 - Dwyane Wade signature basketball shoe with Cloud technology.', 'lining_wade11.jpg', '2025-08-25 09:30:00', 'active'),
(32, 'Mercurial Vapor', 'Nike', 5, 1599.00, 'Nike Mercurial Vapor - lightweight soccer cleats for explosive speed on the pitch.', 'nike_mercurial.jpg', '2025-08-26 03:15:00', 'active'),
(33, 'Predator Elite', 'Adidas', 5, 1799.00, 'Adidas Predator Elite - precision and power for the modern footballer.', 'adidas_predator.jpg', '2025-08-27 06:40:00', 'active'),
(34, 'Yeezy Boost 350', 'Adidas', 6, 1999.00, 'Adidas Yeezy Boost 350 V2 - the iconic lifestyle sneaker designed by Kanye West.', 'yeezy_350.jpg', '2025-08-28 01:20:00', 'active'),
(35, 'Air Force 1', 'Nike', 6, 699.00, 'Nike Air Force 1 - the legendary basketball shoe that became a street style icon.', 'nike_af1.jpg', '2025-08-29 04:05:00', 'active'),
(36, 'Kids Runner', 'Puma', 7, 299.00, 'Puma Kids Runner - lightweight and colorful shoes perfect for active children.', 'puma_kidsrunner.jpg', '2025-08-15 08:30:00', 'active'),
(37, 'Mickey Mouse Sneakers', 'Adidas', 7, 399.00, 'Fun Mickey Mouse themed sneakers that kids will love to wear every day.', 'adidas_mickey.jpg', '2025-08-16 05:45:00', 'active'),
(38, 'Benassi Slides', 'Nike', 8, 199.00, 'Nike Benassi slides - comfortable and easy to wear for post-workout relaxation.', 'nike_benassi.jpg', '2025-08-17 07:20:00', 'active'),
(39, 'Adilette Comfort', 'Adidas', 8, 179.00, 'Adidas Adilette Comfort slides with cloudfoam footbed for superior comfort.', 'adidas_adilette.jpg', '2025-08-18 02:35:00', 'active'),
(40, 'Timberland 6-Inch', 'Timberland', 9, 1299.00, 'Classic Timberland 6-inch boots - durable, waterproof, and iconic.', 'timberland_6inch.jpg', '2025-08-19 06:15:00', 'active'),
(41, 'Doc Martens 1460', 'Dr. Martens', 9, 1199.00, 'Dr. Martens 1460 - the original Doc Martens boot with air-cushioned sole.', 'drmartens_1460.jpg', '2025-08-20 03:50:00', 'active'),
(42, 'Air Max 270', 'Nike', 1, 899.00, 'Nike Air Max 270 features a large Air unit heel for maximum comfort and style. Perfect for running and casual wear.', 'nike_airmax270.jpg', '2025-08-15 02:00:00', 'active'),
(43, 'Gel-Kayano 30', 'ASICS', 1, 1199.00, 'ASICS Gel-Kayano 30 provides superior stability and cushioning for long-distance running.', 'asics_gelkayano30.jpg', '2025-08-16 03:30:00', 'active'),
(44, 'Fresh Foam X', 'New Balance', 1, 799.00, 'New Balance Fresh Foam X delivers plush comfort with responsive energy return.', 'nb_freshfoam.jpg', '2025-08-17 01:15:00', 'active'),
(45, 'Stan Smith', 'Adidas', 2, 599.00, 'The iconic Adidas Stan Smith - a timeless classic that never goes out of style.', 'adidas_stansmith.jpg', '2025-08-18 06:20:00', 'active'),
(46, 'Chuck Taylor All Star', 'Converse', 2, 449.00, 'Classic Converse Chuck Taylor All Star high-top sneakers. A cultural icon since 1917.', 'converse_chuck.jpg', '2025-08-19 08:45:00', 'active'),
(47, 'Old Skool', 'Vans', 2, 389.00, 'Vans Old Skool - the original skate shoe with the iconic side stripe.', 'vans_oldskool.jpg', '2025-08-20 04:10:00', 'active'),
(48, 'Oxford Classic', 'Clarks', 3, 1299.00, 'Premium leather Oxford shoes perfect for business meetings and formal occasions.', 'clarks_oxford.jpg', '2025-08-21 00:30:00', 'active'),
(49, 'Derby Elegance', 'Cole Haan', 3, 1599.00, 'Sophisticated Derby shoes combining traditional craftsmanship with modern comfort.', 'colehaan_derby.jpg', '2025-08-22 07:00:00', 'active'),
(50, 'LeBron 21', 'Nike', 4, 1399.00, 'Nike LeBron 21 - engineered for explosive performance on the basketball court.', 'nike_lebron21.jpg', '2025-08-23 05:25:00', 'active'),
(51, 'Dame 8', 'Adidas', 4, 1199.00, 'Adidas Dame 8 - Damian Lillard signature shoe with responsive Bounce cushioning.', 'adidas_dame8.jpg', '2025-08-24 02:50:00', 'active'),
(52, 'Way of Wade 11', 'Li-Ning', 4, 799.00, 'Li-Ning Way of Wade 11 - Dwyane Wade signature basketball shoe with Cloud technology.', 'lining_wade11.jpg', '2025-08-25 09:30:00', 'active'),
(53, 'Mercurial Vapor', 'Nike', 5, 1599.00, 'Nike Mercurial Vapor - lightweight soccer cleats for explosive speed on the pitch.', 'nike_mercurial.jpg', '2025-08-26 03:15:00', 'active'),
(54, 'Predator Elite', 'Adidas', 5, 1799.00, 'Adidas Predator Elite - precision and power for the modern footballer.', 'adidas_predator.jpg', '2025-08-27 06:40:00', 'active'),
(55, 'Yeezy Boost 350', 'Adidas', 6, 1999.00, 'Adidas Yeezy Boost 350 V2 - the iconic lifestyle sneaker designed by Kanye West.', 'yeezy_350.jpg', '2025-08-28 01:20:00', 'active'),
(56, 'Air Force 1', 'Nike', 6, 699.00, 'Nike Air Force 1 - the legendary basketball shoe that became a street style icon.', 'nike_af1.jpg', '2025-08-29 04:05:00', 'active'),
(57, 'Kids Runner', 'Puma', 7, 299.00, 'Puma Kids Runner - lightweight and colorful shoes perfect for active children.', 'puma_kidsrunner.jpg', '2025-08-15 08:30:00', 'active'),
(58, 'Mickey Mouse Sneakers', 'Adidas', 7, 399.00, 'Fun Mickey Mouse themed sneakers that kids will love to wear every day.', 'adidas_mickey.jpg', '2025-08-16 05:45:00', 'active'),
(59, 'Benassi Slides', 'Nike', 8, 199.00, 'Nike Benassi slides - comfortable and easy to wear for post-workout relaxation.', 'nike_benassi.jpg', '2025-08-17 07:20:00', 'active'),
(60, 'Adilette Comfort', 'Adidas', 8, 179.00, 'Adidas Adilette Comfort slides with cloudfoam footbed for superior comfort.', 'adidas_adilette.jpg', '2025-08-18 02:35:00', 'active'),
(61, 'Timberland 6-Inch', 'Timberland', 9, 1299.00, 'Classic Timberland 6-inch boots - durable, waterproof, and iconic.', 'timberland_6inch.jpg', '2025-08-19 06:15:00', 'active'),
(62, 'Doc Martens 1460', 'Dr. Martens', 9, 1199.00, 'Dr. Martens 1460 - the original Doc Martens boot with air-cushioned sole.', 'drmartens_1460.jpg', '2025-08-20 03:50:00', 'active'),
(63, 'Air Max 270', 'Nike', 1, 899.00, 'Nike Air Max 270 features a large Air unit heel for maximum comfort and style. Perfect for running and casual wear.', 'nike_airmax270.jpg', '2025-08-15 02:00:00', 'active'),
(64, 'Gel-Kayano 30', 'ASICS', 1, 1199.00, 'ASICS Gel-Kayano 30 provides superior stability and cushioning for long-distance running.', 'asics_gelkayano30.jpg', '2025-08-16 03:30:00', 'active'),
(65, 'Fresh Foam X', 'New Balance', 1, 799.00, 'New Balance Fresh Foam X delivers plush comfort with responsive energy return.', 'nb_freshfoam.jpg', '2025-08-17 01:15:00', 'active'),
(66, 'Stan Smith', 'Adidas', 2, 599.00, 'The iconic Adidas Stan Smith - a timeless classic that never goes out of style.', 'adidas_stansmith.jpg', '2025-08-18 06:20:00', 'active'),
(67, 'Chuck Taylor All Star', 'Converse', 2, 449.00, 'Classic Converse Chuck Taylor All Star high-top sneakers. A cultural icon since 1917.', 'converse_chuck.jpg', '2025-08-19 08:45:00', 'active'),
(68, 'Old Skool', 'Vans', 2, 389.00, 'Vans Old Skool - the original skate shoe with the iconic side stripe.', 'vans_oldskool.jpg', '2025-08-20 04:10:00', 'active'),
(69, 'Oxford Classic', 'Clarks', 3, 1299.00, 'Premium leather Oxford shoes perfect for business meetings and formal occasions.', 'clarks_oxford.jpg', '2025-08-21 00:30:00', 'active'),
(70, 'Derby Elegance', 'Cole Haan', 3, 1599.00, 'Sophisticated Derby shoes combining traditional craftsmanship with modern comfort.', 'colehaan_derby.jpg', '2025-08-22 07:00:00', 'active'),
(71, 'LeBron 21', 'Nike', 4, 1399.00, 'Nike LeBron 21 - engineered for explosive performance on the basketball court.', 'nike_lebron21.jpg', '2025-08-23 05:25:00', 'active'),
(72, 'Dame 8', 'Adidas', 4, 1199.00, 'Adidas Dame 8 - Damian Lillard signature shoe with responsive Bounce cushioning.', 'adidas_dame8.jpg', '2025-08-24 02:50:00', 'active'),
(73, 'Way of Wade 11', 'Li-Ning', 4, 799.00, 'Li-Ning Way of Wade 11 - Dwyane Wade signature basketball shoe with Cloud technology.', 'lining_wade11.jpg', '2025-08-25 09:30:00', 'active'),
(74, 'Mercurial Vapor', 'Nike', 5, 1599.00, 'Nike Mercurial Vapor - lightweight soccer cleats for explosive speed on the pitch.', 'nike_mercurial.jpg', '2025-08-26 03:15:00', 'active'),
(75, 'Predator Elite', 'Adidas', 5, 1799.00, 'Adidas Predator Elite - precision and power for the modern footballer.', 'adidas_predator.jpg', '2025-08-27 06:40:00', 'active'),
(76, 'Yeezy Boost 350', 'Adidas', 6, 1999.00, 'Adidas Yeezy Boost 350 V2 - the iconic lifestyle sneaker designed by Kanye West.', 'yeezy_350.jpg', '2025-08-28 01:20:00', 'active'),
(77, 'Air Force 1', 'Nike', 6, 699.00, 'Nike Air Force 1 - the legendary basketball shoe that became a street style icon.', 'nike_af1.jpg', '2025-08-29 04:05:00', 'active'),
(78, 'Kids Runner', 'Puma', 7, 299.00, 'Puma Kids Runner - lightweight and colorful shoes perfect for active children.', 'puma_kidsrunner.jpg', '2025-08-15 08:30:00', 'active'),
(79, 'Mickey Mouse Sneakers', 'Adidas', 7, 399.00, 'Fun Mickey Mouse themed sneakers that kids will love to wear every day.', 'adidas_mickey.jpg', '2025-08-16 05:45:00', 'active'),
(80, 'Benassi Slides', 'Nike', 8, 199.00, 'Nike Benassi slides - comfortable and easy to wear for post-workout relaxation.', 'nike_benassi.jpg', '2025-08-17 07:20:00', 'active'),
(81, 'Adilette Comfort', 'Adidas', 8, 179.00, 'Adidas Adilette Comfort slides with cloudfoam footbed for superior comfort.', 'adidas_adilette.jpg', '2025-08-18 02:35:00', 'active'),
(82, 'Timberland 6-Inch', 'Timberland', 9, 1299.00, 'Classic Timberland 6-inch boots - durable, waterproof, and iconic.', 'timberland_6inch.jpg', '2025-08-19 06:15:00', 'active'),
(83, 'Doc Martens 1460', 'Dr. Martens', 9, 1199.00, 'Dr. Martens 1460 - the original Doc Martens boot with air-cushioned sole.', 'drmartens_1460.jpg', '2025-08-20 03:50:00', 'active'),
(84, 'Air Max 270', 'Nike', 1, 899.00, 'Nike Air Max 270 features a large Air unit heel for maximum comfort and style. Perfect for running and casual wear.', 'nike_airmax270.jpg', '2025-08-15 02:00:00', 'active'),
(85, 'Gel-Kayano 30', 'ASICS', 1, 1199.00, 'ASICS Gel-Kayano 30 provides superior stability and cushioning for long-distance running.', 'asics_gelkayano30.jpg', '2025-08-16 03:30:00', 'active'),
(86, 'Fresh Foam X', 'New Balance', 1, 799.00, 'New Balance Fresh Foam X delivers plush comfort with responsive energy return.', 'nb_freshfoam.jpg', '2025-08-17 01:15:00', 'active'),
(87, 'Stan Smith', 'Adidas', 2, 599.00, 'The iconic Adidas Stan Smith - a timeless classic that never goes out of style.', 'adidas_stansmith.jpg', '2025-08-18 06:20:00', 'active'),
(88, 'Chuck Taylor All Star', 'Converse', 2, 449.00, 'Classic Converse Chuck Taylor All Star high-top sneakers. A cultural icon since 1917.', 'converse_chuck.jpg', '2025-08-19 08:45:00', 'active'),
(89, 'Old Skool', 'Vans', 2, 389.00, 'Vans Old Skool - the original skate shoe with the iconic side stripe.', 'vans_oldskool.jpg', '2025-08-20 04:10:00', 'active'),
(90, 'Oxford Classic', 'Clarks', 3, 1299.00, 'Premium leather Oxford shoes perfect for business meetings and formal occasions.', 'clarks_oxford.jpg', '2025-08-21 00:30:00', 'active'),
(91, 'Derby Elegance', 'Cole Haan', 3, 1599.00, 'Sophisticated Derby shoes combining traditional craftsmanship with modern comfort.', 'colehaan_derby.jpg', '2025-08-22 07:00:00', 'active'),
(92, 'LeBron 21', 'Nike', 4, 1399.00, 'Nike LeBron 21 - engineered for explosive performance on the basketball court.', 'nike_lebron21.jpg', '2025-08-23 05:25:00', 'active'),
(93, 'Dame 8', 'Adidas', 4, 1199.00, 'Adidas Dame 8 - Damian Lillard signature shoe with responsive Bounce cushioning.', 'adidas_dame8.jpg', '2025-08-24 02:50:00', 'active'),
(94, 'Way of Wade 11', 'Li-Ning', 4, 799.00, 'Li-Ning Way of Wade 11 - Dwyane Wade signature basketball shoe with Cloud technology.', 'lining_wade11.jpg', '2025-08-25 09:30:00', 'active'),
(95, 'Mercurial Vapor', 'Nike', 5, 1599.00, 'Nike Mercurial Vapor - lightweight soccer cleats for explosive speed on the pitch.', 'nike_mercurial.jpg', '2025-08-26 03:15:00', 'active'),
(105, '1', 'Lining', 4, 699.00, 'jisbai', '68b091bd89963.jpg', '2025-08-28 17:28:29', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `product_photos`
--

CREATE TABLE `product_photos` (
  `photo_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `photo_filename` varchar(255) NOT NULL,
  `is_main_photo` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_photos`
--

INSERT INTO `product_photos` (`photo_id`, `product_id`, `photo_filename`, `is_main_photo`, `display_order`, `created_at`) VALUES
(1, 1, '68addf803e726.jpg', 1, 0, '2025-08-26 11:40:04'),
(10, 105, '68b091bd89963.jpg', 1, 0, '2025-08-28 17:28:29'),
(11, 1, '68b1cbde5f0bd.webp', 0, 1, '2025-08-29 15:48:46'),
(12, 1, '68b1cbde6a14c.webp', 0, 2, '2025-08-29 15:48:46'),
(13, 1, '68b1cbde71c9f.webp', 0, 3, '2025-08-29 15:48:46');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `variant_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(10) NOT NULL,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`variant_id`, `product_id`, `size`, `stock`) VALUES
(2, 2, '43', 3),
(3, 2, '42', 7),
(5, 2, '42', 6),
(12, 1, '36', 2),
(13, 3, '37', 0),
(15, 4, '40', 4),
(599, 105, '38', 2);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `role` enum('Admin','Member') NOT NULL DEFAULT 'Member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password`, `name`, `address`, `phone`, `photo`, `role`, `created_at`) VALUES
(13, 'eason', 'taneason1111@gmail.com', '7c222fb2927d828af22f592134e8932480637c0d', 'Tan Ea Son', 'No 6 Jalan Indah 10/5Taman Pertama', '0122226133', '6898d553e9814.jpg', 'Admin', '2025-08-28 16:22:07'),
(15, 'Tan1221', 'taneason0000@gmail.com', '7c222fb2927d828af22f592134e8932480637c0d', '', '', '', '687ce54d6c885.jpg', 'Member', '2025-08-28 16:22:07'),
(18, 'taneason', 'taneason0912@gmail.com', '208352c22d8f058ceaeed06305c7600fc4935a2d', '', '', '', '', 'Member', '2025-08-28 16:22:07'),
(19, 'admin', 'admin@osbuzz.com', '356a192b7913b04c54574d18c28d46e6395428ab', 'System Administrator', '123 Admin Street, Admin City, AC 12345', '01-234-5678', '', 'Admin', '2025-08-28 16:22:07'),
(20, 'manager', 'manager@osbuzz.com', 'da4b9237bacccdf19c0760cab7aec4a8359010b0', 'Store Manager', '456 Manager Ave, Business District, BD 67890', '01-345-6789', '', 'Admin', '2025-08-28 16:22:07'),
(21, 'john_doe', 'john.doe@email.com', '77de68daecd823babbb58edb1c8e14d7106e83bb', 'John Doe', '789 Main Street, Suburbia, SB 11111', '01-456-7890', '', 'Member', '2025-08-28 16:22:07'),
(22, 'jane_smith', 'jane.smith@email.com', '1b6453892473a467d07372d45eb05abc2031647a', 'Jane Smith', '321 Oak Avenue, Riverside, RS 22222', '01-567-8901', '', 'Member', '2025-08-28 16:22:07'),
(23, 'mike_wilson', 'mike.wilson@email.com', 'ac3478d69a3c81fa62e60f5c3696165a4e5e6ac4', 'Mike Wilson', '654 Pine Road, Hillside, HS 33333', '01-678-9012', '', 'Member', '2025-08-28 16:22:07'),
(24, 'sarah_brown', 'sarah.brown@email.com', 'c1dfd96eea8cc2b62785275bca38ac261256e278', 'Sarah Brown', '987 Elm Street, Downtown, DT 44444', '01-789-0123', '', 'Member', '2025-08-28 16:22:07'),
(25, 'david_lee', 'david.lee@email.com', '902ba3cda1883801594b6e1b452790cc53948fda', 'David Lee', '147 Maple Drive, Greenwood, GW 55555', '01-890-1234', '', 'Member', '2025-08-28 16:22:07'),
(26, 'lisa_taylor', 'lisa.taylor@email.com', 'fe5dbbcea5ce7e2988b8c69bcfdfde8904aabc1f', 'Lisa Taylor', '258 Cedar Lane, Lakeside, LS 66666', '01-901-2345', '', 'Member', '2025-08-28 16:22:07'),
(27, 'alex_johnson', 'alex.johnson@email.com', '0ade7c2cf97f75d009975f4d720d1fa6c19f4897', 'Alex Johnson', '369 Birch Court, Mountain View, MV 77777', '01-012-3456', '', 'Member', '2025-08-28 16:22:07'),
(28, 'emily_davis', 'emily.davis@email.com', 'b1d5781111d84f7b3fe45a0852e59758cd7a87e5', 'Emily Davis', '741 Willow Street, Valley Heights, VH 88888', '01-123-4567', '', 'Member', '2025-08-28 16:22:07'),
(29, 'testuser1', 'test1@test.com', '356a192b7913b04c54574d18c28d46e6395428ab', 'Test User One', 'Test Address 1', '01-111-1111', '', 'Member', '2025-08-28 16:22:07'),
(30, 'testuser2', 'test2@test.com', 'da4b9237bacccdf19c0760cab7aec4a8359010b0', 'Test User Two', 'Test Address 2', '01-222-2222', '', 'Member', '2025-08-28 16:22:07'),
(31, 'customer123', 'customer@shop.com', '77de68daecd823babbb58edb1c8e14d7106e83bb', 'Regular Customer', '999 Customer Boulevard, Shopping District, SD 99999', '01-999-9999', '', 'Member', '2025-08-28 16:22:07'),
(32, 'jieying', 'jieying@gmail.com', '7c222fb2927d828af22f592134e8932480637c0d', 'JIE YING', '', '', '', 'Admin', '2025-08-28 16:24:04'),
(33, 'Taneason_1221', 'tanes-wp23@student.tarc.edu.my', '697f6f62764c05183042401e6bc74c6704a3da7d', '', '', '', '', 'Member', '2025-08-29 16:32:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`,`size`),
  ADD KEY `fk_cart_user` (`user_id`),
  ADD KEY `fk_cart_product` (`product_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_slug` (`category_slug`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_unique` (`token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `email` (`email`),
  ADD KEY `token` (`token`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `fk_product_category` (`category_id`),
  ADD KEY `idx_product_status` (`status`);

--
-- Indexes for table `product_photos`
--
ALTER TABLE `product_photos`
  ADD PRIMARY KEY (`photo_id`),
  ADD KEY `fk_product_photos_product` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD KEY `color_id` (`product_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_user_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `product_photos`
--
ALTER TABLE `product_photos`
  MODIFY `photo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=600;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Constraints for table `product_photos`
--
ALTER TABLE `product_photos`
  ADD CONSTRAINT `fk_product_photos_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
