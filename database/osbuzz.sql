-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 02, 2025 at 10:22 PM
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
(1, 'Running', 'running', 'Running Shoes', '1-1756843746.jpg', '2025-09-02 09:50:10'),
(2, 'Lifestyle', 'lifestyle', 'Lifestyle Shoes', '2-1756843708.jpg', '2025-09-02 13:07:18'),
(3, 'Sport', 'sport', 'Sport Shoes', '3-1756843856.jpg', '2025-09-02 17:56:56'),
(4, 'Occasions shoes', 'leather-shoes', 'Perfect for Every Special Moment', '4-1756843896.jpg', '2025-09-02 18:39:38');

-- --------------------------------------------------------

--
-- Table structure for table `customer_addresses`
--

CREATE TABLE `customer_addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_name` varchar(50) NOT NULL DEFAULT 'Address' COMMENT 'e.g. Home, Office, etc.',
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `company` varchar(100) DEFAULT NULL,
  `address_line_1` varchar(200) NOT NULL,
  `address_line_2` varchar(200) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'Malaysia',
  `phone` varchar(20) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL,
  `order_status` enum('pending','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_method` enum('paypal','credit_card','bank_transfer','cash_on_delivery') DEFAULT 'paypal',
  `payment_id` varchar(100) DEFAULT NULL COMMENT 'PayPal transaction ID or other payment reference',
  `shipping_address` text NOT NULL,
  `billing_address` text DEFAULT NULL,
  `customer_notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL COMMENT 'Snapshot of product name at time of order',
  `product_brand` varchar(50) NOT NULL COMMENT 'Snapshot of product brand at time of order',
  `size` varchar(10) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL COMMENT 'Price at time of order',
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL COMMENT 'price * quantity',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `history_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `old_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) NOT NULL,
  `changed_by` int(11) DEFAULT NULL COMMENT 'User ID who changed the status',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
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
(1, 'Trending Men\'s Designer Sneaker Breathable Hard Wearing Sponge Insole Sports Shoes Man Casual Runnin', 'Speedy Sorrce', 1, 25.00, '2025 Men’s Sports Running Shoes\r\nLightweight and breathable sneakers with a durable sponge insole for all-day comfort. Stylish gradient design with strong grip sole, perfect for running, workouts, or casual wear.', '68b6cba026792.png', '2025-09-02 10:04:15', 'active'),
(2, 'Weshine Sneaker Manufacture Custom Wholesale Men\'s Mesh Casual Sneakers Fly Knitting Upper Design Ru', 'Customization', 1, 49.99, 'Men’s Mesh Casual Running Shoes\r\nBreathable fly-knit upper with cushioned sole for comfort and support. Lightweight, stylish, and durable—perfect for running, training, or everyday wear.', '68b6d1f7f16aa.png', '2025-09-02 10:12:21', 'active'),
(3, 'Knitting Carbon Plate Casual Running Shoes Unisex Breathable & Non-Slip Sports Shoes Factory Direct', 'OEM ACCEPTABLE', 1, 69.90, 'Unisex Carbon Plate Running Shoes\r\nBreathable knit upper with carbon plate support for extra performance. Lightweight, non-slip sole for stability—ideal for running, training, and daily wear.', '68b6d20ed71c6.png', '2025-09-02 10:16:21', 'active'),
(4, 'High Quality Breathable Large Size Mens Running Shoes Ultra Lightweight Knit Upper MD Rubber Studs C', 'OEM ACCEPTABLE', 1, 50.99, 'Unisex Carbon Plate Running Shoes\r\nBreathable knit upper with carbon plate support for extra performance. Lightweight, non-slip sole for stability—ideal for running, training, and daily wear.', '68b6d22a62df2.png', '2025-09-02 10:20:24', 'active'),
(5, 'S TECTON X2 Breathable Outdoor Marathon Training Shoes Soft Rubber Insole Cushioning Cloud Running L', 'TECTON X2', 1, 100.00, 'The Tecton X2 is a lightweight, breathable running shoe designed for marathon training and leisure. It features a cushioned \"Cloud\" comfort system and a soft rubber insole for support during runs or everyday wear.', '68b6d2665a9a5.png', '2025-09-02 11:17:58', 'active'),
(6, 'Breathable Unisex Lightweight Gym Running Shoes Women Mesh Knitted Minimalist Shoes Wide Toe Barefoo', 'None', 1, 39.99, 'These are minimalist barefoot shoes designed for a natural, unrestricted feel. They feature a wide toe box for toe splay, a flexible zero-drop sole, and a breathable knit upper. Ideal for gym workouts, running, or casual wear, they promote natural foot movement and balance.', '68b6d38a70e25.png', '2025-09-02 11:22:50', 'active'),
(7, 'Men\'s Road Running Shoes Jogger Jogging Sneakers Track Trail Running Minimal Stretch Fabric Air Mesh', 'DQ', 1, 45.99, 'These are lightweight, low-top men\'s running shoes built for road, trail, and track. They feature a breathable air mesh and stretch fabric upper for comfort and ventilation, making them ideal for summer jogging and athletic activities.', '68b6d5238262e.png', '2025-09-02 11:29:39', 'active'),
(8, 'Plus Size Men\'s Casual Running Shoes New Breathable Sneakers Soft Bottom Lace-Up Summer Ready Runnin', 'OEM acceptable', 1, 20.00, 'These plus-size men\'s sneakers offer a comfortable and casual option for everyday wear. The breathable mesh upper keeps your feet cool, while the soft rubber sole provides cushioning for walking or light running. Their classic lace-up design ensures a secure and stylish fit.', '68b6d5dda7c99.png', '2025-09-02 11:32:45', 'active'),
(9, 'Men\'s New Lace up Running Shoes Are Lightweight and Comfortable Casual Sports Shoes for Men', 'SPORT', 1, 15.99, 'These men\'s lace-up shoes are a versatile choice for running or casual sports. They are built to be lightweight and comfortable, making them perfect for daily wear and athletic activities.', '68b6d6bc39c0c.png', '2025-09-02 11:36:28', 'active'),
(10, '2025 Running Sports Shoes for Men Mesh Breathable Sneakers Lightweight Fashion Outdoor Fitness Walki', 'YunBu', 1, 88.88, '', '68b6d75323e79.png', '2025-09-02 11:38:59', 'active'),
(11, 'High Quality Men\'s Sport Running Shoes Fashion Sneakers with Breathable Mesh Upper and TPU Outsole f', 'LB', 1, 10.99, 'These high-quality men’s running shoes blend performance and style with a breathable mesh upper and resilient TPU outsole. Built to last through spring and winter, they’re perfect for sports or everyday casual wear.', '68b6d7f2abe46.png', '2025-09-02 11:41:38', 'active'),
(12, '2025 Breathable Rubber Out-sole Men\'s Basketball Running Shoes Fashion Sports Gym Jogging Tennis Sho', 'OEM', 1, 39.99, 'These versatile 2025 sneakers are designed for various sports, including basketball, running, gym workouts, and tennis. They combine a breathable upper for comfort with a durable rubber outsole for traction and support, all in a fashionable athletic design.', '68b6d93ec8f74.png', '2025-09-02 11:47:10', 'active'),
(13, 'IRUNSVAN1.0 Full Length Carbon Plate Boosting Sports Shoes Marathon Racing Running Shoes', 'Flyingongrass', 1, 150.00, 'The IRUNSVAN 1.0 are elite marathon racing shoes engineered for speed. They feature a full-length carbon fiber plate for powerful propulsion and energy return, making them ideal for competitive runners seeking a performance boost. A top-selling model built for serious racing.', '68b6da9490f3c.png', '2025-09-02 11:52:52', 'active'),
(14, 'Casual Fitness Running Shoes for Men and Women Air-cushioned Canvas Mesh Walking Sports Shoes', 'None', 1, 29.90, 'Unisex casual running shoes featuring a comfortable air-cushioned sole and a breathable canvas and mesh upper. Perfect for fitness, walking, and everyday sports.', '68b6db239814c.png', '2025-09-02 11:55:15', 'active'),
(15, 'New Daily Running Sports Lace up Trendy Men\'s Shoes with Large Mesh Mesh and Breathable Low Cut Snea', 'OFF-WHITE', 1, 15.00, 'These are trendy, low-cut men\'s sneakers designed for daily running and sports. They feature a large mesh upper for maximum breathability and a secure lace-up design, offering a blend of style and functionality for casual and athletic wear.', '68b6dcc0498ac.png', '2025-09-02 12:02:08', 'active'),
(16, 'For HOKA Bondi 9 Lightweight Running Shoes for Men Women Carbon Outdoor Cushioned LifestyleTop for L', 'HOKA', 1, 129.90, 'These are HOKA Bondi 9-inspired running shoes designed for long-distance runners. They feature a lightweight build with a carbon-infused design and superior cushioning for maximum comfort and energy return. Ideal for outdoor running and a cushioned lifestyle top in spring.', '68b6e251cf684.png', '2025-09-02 12:25:53', 'active'),
(17, 'Latest Design Fashion Outdoor Casual Running Shoes Comfort Breathable Original Sports Shoes Made in ', 'MOBM', 1, 99.99, 'These are the latest fashion outdoor running shoes, designed for both casual and sports use. They offer superior comfort and breathability, making them ideal for everyday running and active wear.', '68b6e30b3b4ca.png', '2025-09-02 12:28:59', 'active'),
(18, '2025 Marathon Men\'s Women\'s Sporty New Fashion Lightweight Sneaker Breathable Mesh Lining Running Wa', 'HOME', 1, 135.00, 'These are 2025 unisex marathon running shoes, designed to be lightweight and sporty with a breathable mesh lining. They feature cushioned shock absorption for comfort during running and walking, making them a fashionable and functional choice for athletes.', '68b6e392cca78.png', '2025-09-02 12:31:14', 'active'),
(19, 'National Trend Men\'s & Women\'s Casual Running Shoes with Carbon Plate & Shock-Absorbing Thick-Soled ', 'HOBM', 1, 78.88, 'These are trendy unisex running shoes featuring a carbon plate for propulsion and a thick, shock-absorbing sole for maximum comfort. Designed for both casual wear and sports walking, they offer a stylish, national trend aesthetic with high-performance support.', '68b6e4614dc05.png', '2025-09-02 12:34:41', 'active'),
(20, 'Men\'s Women\'s Lightweight Road Running Sneakers Low-Top Jogger Athletic Shoes Platform Stretch Fabri', 'HOBM', 1, 199.99, 'Experience comfortable runs and walks with these lightweight unisex sneakers. Designed with a flexible stretch fabric upper and a secure lace-up style, they are perfect for road running, jogging, and everyday athletic activities.', '68b6e60d55617.png', '2025-09-02 12:41:49', 'active'),
(21, 'Manufacturers Direct Sale Fashion Lifestyle Leather Shoes Hot Selling Casual Shoes Man', 'OEM ACCEPTABLE', 2, 120.00, 'These are hot-selling, fashionable leather casual shoes sold directly from the manufacturer. Designed for men seeking a stylish and versatile everyday shoe.', '68b6edc80fdb1.png', '2025-09-02 13:14:48', 'active'),
(22, 'High-Quality Men\'s Sneakers Lightweight, Comfortable & Breathable Casual Shoes Perfect for Everyday ', 'WYPEX', 2, 99.99, 'Premium men\'s sneakers designed for an active lifestyle. These shoes are lightweight, comfortable, and breathable, making them ideal for everyday fashion and casual wear. A versatile choice from a verified custom manufacturer.', '68b6fca55b910.png', '2025-09-02 14:18:13', 'active'),
(23, 'Men\'s Retro Sport Shoes with TPU Lighted Running Casual Shoes for Active Lifestyle Winter Men Sports', 'None', 2, 59.99, 'Men\'s retro sport shoes with TPU construction and lighted design. Ideal for running, casual wear, and an active lifestyle, especially during winter.', '68b6fdac0ea85.png', '2025-09-02 14:22:36', 'active'),
(24, 'OEM High Quality Fashion Lifestyle Sneakers', 'OEM ACCEPTABLE', 2, 124.99, 'High-quality OEM lifestyle sneakers designed for year-round comfort. Features anti-slip soles and a secure lace-up design, ideal everyday walking in any season.', '68b6feb931fa9.png', '2025-09-02 14:27:05', 'active'),
(25, 'Adaptive Lifestyle Sporty Walking Shoes with Cloud Cushioning Slip-On Canvas Upper Cotton Lining for', 'LUOSHENGDU', 2, 29.90, 'Adaptive lifestyle walking shoes featuring cloud cushioning and a slip-on canvas upper with cotton lining. Perfect for comfortable city explorations during autumn.', '68b6ffd6adba5.png', '2025-09-02 14:31:50', 'active'),
(26, 'Chunky Sport Walking Shoes for Active Lifestyle Soft and Anti-Slippery for All Seasons-Summer Winter', 'Hellosport', 2, 119.99, 'Chunky sport walking shoes designed for an active lifestyle. Features a soft, anti-slip sole for reliable traction and comfort in all seasons—summer, winter, and spring.', '68b7005a8d020.png', '2025-09-02 14:34:02', 'active'),
(27, 'Women\'s High Quality ETPU Sole Casual Walking Shoes Fashion White Lifestyle Shoes Unisex', 'Mychonly', 2, 89.00, 'High-quality women’s casual walking shoes with a lightweight ETPU sole. Perfect for fashion and everyday lifestyle wear in a versatile unisex white design.', '68b702c0204f6.png', '2025-09-02 14:44:16', 'active'),
(28, 'Men\'s New Black Sports Casual Board Shoes for Summer for All Sports and Student Lifestyle Lace-Up Cl', 'XunYa', 2, 68.00, 'Summer-ready board shoes for sports and campus life! These black lace-up sneakers blend casual style with functionality for active days.', '68b703d23a971.png', '2025-09-02 14:48:50', 'active'),
(29, 'Men\'s Casual Canvas Sports Shoes for Active Lifestyle', 'OEM ACCEPTABLE', 2, 55.50, 'These are versatile men\'s casual shoes, crafted from canvas for a blend of style and functionality. Designed for an active lifestyle, they are ideal for sports and everyday wear, offering comfort and durability.', '68b7052e3f585.png', '2025-09-02 14:54:38', 'active'),
(30, 'Luxury Designer-Inspired Unisex Flat Sneaker Custom Logo Sport Casual Shoe Anti-Slip Comfortable Fla', 'Kick Ground', 2, 128.00, 'A luxury designer-inspired unisex sneaker featuring a comfortable flat sole and anti-slip design. This versatile slip-on is perfect for both sport and casual wear, and is available for customization with a logo.', '68b705d723c72.png', '2025-09-02 14:57:27', 'active'),
(31, 'Y 39-44 Men\'s New Sneakers Niche Design, Simple and Trendy, Everyday and Versatile, Youth Athleisure', 'Kick Ground', 2, 189.99, 'These men\'s sneakers feature a niche, minimalist design that is both simple and trendy. Versatile enough for everyday athleisure wear, they are a stylish and practical choice for a youthful, active lifestyle.', '68b706cff3426.png', '2025-09-02 15:01:36', 'active'),
(32, '2024 Fashion Trend Light Weight Custom Outdoor Walking Women Casual Shoes', 'OEM ACCEPTABLE', 2, 59.99, 'These 2024 fashion-forward women\'s casual shoes are designed for outdoor walking and everyday wear. They are exceptionally lightweight and customizable, offering a blend of style, comfort, and versatility for a modern, active lifestyle.', '68b7077babc88.png', '2025-09-02 15:04:27', 'active'),
(33, 'Fashion Chunky Platform Sneakers for Women with Leather and Mesh Upper EVA Sole Non Slip Stylish Wal', 'Z.NUO', 2, 258.00, 'These fashionable women\'s chunky platform sneakers combine a leather and mesh upper with a non-slip EVA sole. Stylish and practical, they are ideal for walking and make a bold fashion statement.', '68b70806038f4.png', '2025-09-02 15:06:46', 'active'),
(34, '2025 Trending Men\'s Casual Shoes Solid White Pure Black Stain-Resistant Versatile for Walking Style ', 'ZH', 2, 210.00, 'Trendy 2025 men\'s casual \"dad shoes\" in solid white or black. Designed to be stain-resistant and versatile, they are a popular influencer pick for everyday walking and style.', '68b70899e4044.png', '2025-09-02 15:09:13', 'active'),
(35, 'Luxury Custom Classic Style Casual Sports Walking Shoes High-End Quality TPU Outsole for Couples', 'None', 2, 88.80, 'High-end luxury walking shoes for couples, featuring a classic design and custom options. Crafted with a premium TPU outsole for durability and style, these shoes blend casual sports aesthetics with sophisticated quality.', '68b70953b780d.png', '2025-09-02 15:12:19', 'active'),
(37, 'Dropshipping Fashion Luxury Designer Shoes Original Casual Walking Style Shoes Skateboard Shoes', 'Kick Ground', 2, 299.99, 'Fashionable luxury designer shoes for dropshipping, blending casual walking style with a skateboard aesthetic. These original, high-quality sneakers offer a versatile and trendy look for everyday wear.', '68b70b8a00e16.png', '2025-09-02 15:21:46', 'active'),
(38, '3D men\'s women\'s shoes, latest 3DD models', 'Sport', 3, 199.00, '', '68b732171a927.webp', '2025-09-02 18:06:15', 'active'),
(39, 'Sonala Sport Shoes Running Shoes', 'Sonala', 3, 209.00, '', '68b7327a0479e.webp', '2025-09-02 18:07:54', 'active'),
(40, 'Sports Sketching Shoes Running', 'Gello', 3, 89.00, '', '68b732cf71771.webp', '2025-09-02 18:09:19', 'active'),
(41, 'ZXSM Men\'s Shoes', 'ZXSM', 3, 69.00, '', '68b7332083c9d.webp', '2025-09-02 18:10:40', 'active'),
(42, 'FSport shoes', 'FSport', 3, 58.00, '', '68b73361e984a.webp', '2025-09-02 18:11:45', 'active'),
(43, 'Sports Shoes Women Soft-Soled', 'GGbond', 3, 49.00, '', '68b733986eb03.webp', '2025-09-02 18:12:40', 'active'),
(44, 'Men Sport Shoes', 'HeartBeat', 3, 57.00, '', '68b747c283ebb.jpg', '2025-09-02 18:13:48', 'active'),
(45, 'Outdoor 808 Sport Shoes', 'BBJ', 3, 66.00, '', '68b73416c8ef2.webp', '2025-09-02 18:14:46', 'active'),
(46, 'Mountain Shoes', 'Kyo', 3, 89.00, '', '68b7345ea5d6f.webp', '2025-09-02 18:15:58', 'active'),
(47, 'Mesh breathable sports shoes,', 'Ultra Gangz', 3, 59.00, '', '68b747a9ebcb7.jpg', '2025-09-02 18:16:54', 'active'),
(48, 'afi 303 Sport shoes', 'ZXSM', 3, 66.00, '', '68b747e85fc0c.jpg', '2025-09-02 18:20:24', 'active'),
(49, 'Lightweight Sport Shoes Running Fitness Tennis Shoes Plus', 'Fashion', 3, 59.00, '', '68b735a81d33d.webp', '2025-09-02 18:21:28', 'active'),
(50, 'Stylish All-match Men\'s Shoes', 'ZXSM', 2, 39.00, '', '68b735e5aafcc.webp', '2025-09-02 18:22:29', 'active'),
(51, 'Kanvas casual Men Shoes', 'Kanvas', 2, 89.00, '', '68b736331ba60.webp', '2025-09-02 18:23:47', 'active'),
(52, 'Sneaker Men\'s', 'GGbond', 2, 49.00, '', '68b73691a7eae.webp', '2025-09-02 18:25:21', 'active'),
(53, 'Student Autumn and Spring New High-Top', 'Ultra Gangz', 2, 119.00, '', '68b736ef99af6.webp', '2025-09-02 18:26:55', 'active'),
(54, 'Gerlire Men\'s Sport\'s Shoes', 'Kyo', 3, 79.00, '', '68b7377a24eda.webp', '2025-09-02 18:29:14', 'active'),
(55, 'Mbase Sport Shoes', 'GGbond', 3, 39.00, '', '68b737b601b0b.webp', '2025-09-02 18:30:14', 'active'),
(56, 'Comfortable Leather Basketball Shoes', 'ZXSM', 3, 109.00, '', '68b73821c5f68.webp', '2025-09-02 18:32:01', 'active'),
(57, 'Sports Shoes All-Star Basketball Shoes', 'Kyo', 3, 89.00, '', '68b748062be9a.jpg', '2025-09-02 18:33:10', 'active'),
(58, 'Sports basketball shoes2024', 'Ultra Gangz', 3, 99.00, '', '68b738b798f74.webp', '2025-09-02 18:34:31', 'active'),
(59, 'High-quality Basketball Shoes Men\'s Practical', 'Konzo', 3, 89.00, '', '68b738fcd501d.webp', '2025-09-02 18:35:40', 'active'),
(60, 'Dragon Basketball shoes', 'DBZZ', 3, 119.00, '', '68b7393573f36.webp', '2025-09-02 18:36:37', 'active'),
(61, 'High Top Men Basketball Shoes Waterproof Leather Male Sport', 'HeartBeat', 3, 78.00, '', '68b739acc28fe.webp', '2025-09-02 18:38:36', 'active'),
(62, 'Soft leather men\'s shoes', 'Koko', 4, 69.00, '', '68b73a2d2a043.webp', '2025-09-02 18:40:45', 'active'),
(63, 'Men\'s Leather Shoes Slip-on Business', 'Gz', 4, 89.00, '', '68b73a6069b6f.webp', '2025-09-02 18:41:36', 'active'),
(64, 'New Style Loafers Shoes for Men Formal Work', 'Koko', 4, 69.00, '', '68b73a8f03c46.webp', '2025-09-02 18:42:23', 'active'),
(65, 'Business Leather Shoes', 'Bozy', 4, 79.00, '', '68b73abd01c24.webp', '2025-09-02 18:43:09', 'active'),
(66, 'Men\'s Casual Leather Shoes British Lace Up Shiny', 'Konzo', 4, 99.00, '', '68b73af3326f5.webp', '2025-09-02 18:44:03', 'active'),
(67, 'Shoes Men Business Shoes Wedding Formal Shoes', 'Gz', 4, 88.00, '', '68b73b340fb7c.webp', '2025-09-02 18:45:08', 'active'),
(68, 'Casual Student Leather Shoes', 'Bozy', 4, 69.00, '', '68b73b634e960.webp', '2025-09-02 18:45:55', 'active'),
(69, 'Big Size Elegant Cool Men Formal Brogues Shoes Business', 'Koko', 4, 49.00, '', '68b73bcc4cf5a.webp', '2025-09-02 18:47:40', 'active'),
(70, 'Bean Leather Genuine Leather Casual Men Shoes', 'Bozy', 4, 99.00, '', '68b73c065c367.webp', '2025-09-02 18:48:38', 'active'),
(71, 'Fashion Oxfords PU Leather Lace Up', 'Konzo', 4, 119.00, '', '68b73c465f8a5.webp', '2025-09-02 18:49:42', 'active'),
(72, 'British style formal thick heels office', 'ggwp', 4, 39.00, '', '68b73c89d1350.webp', '2025-09-02 18:50:49', 'active'),
(73, 'Pointed Toe Heels Women\'s Office Shoes', 'ggwp', 4, 49.00, '', '68b73cb313a1d.webp', '2025-09-02 18:51:31', 'active'),
(74, 'Elegant Classic Women\'s Casual Heels Sandal Lady High Heels', 'WQ', 4, 79.00, '', '68b73cdaa461e.webp', '2025-09-02 18:52:10', 'active'),
(75, 'Professional High-heeled Shoes', 'WQ', 4, 89.00, '', '68b73d10ee2f1.webp', '2025-09-02 18:53:04', 'active'),
(76, 'Office Wedding Shoes Heels', 'LWQ', 4, 119.00, '', '68b73d4a749aa.webp', '2025-09-02 18:54:02', 'active'),
(77, 'Thick Heel Women\'s Shoes', 'LWQ', 4, 109.00, '', '68b73d833a727.webp', '2025-09-02 18:54:59', 'active'),
(78, 'High-quality 7p gold pointed-toe', 'LWQ', 4, 159.00, '', '68b73db5e5b82.webp', '2025-09-02 18:55:49', 'active'),
(79, 'Pointed Toe Pearl Stiletto', 'Hazel', 4, 89.00, '', '68b73dfdd3799.webp', '2025-09-02 18:57:01', 'active'),
(80, 'Korean style high heels pointed toe', 'Hazel', 4, 99.00, '', '68b73e3761be1.webp', '2025-09-02 18:57:59', 'active'),
(81, 'Korean Fashion Girl New versatile French heeled sandals chunky', 'LWQ', 4, 99.00, '', '68b73e7375ea3.webp', '2025-09-02 18:58:59', 'active');

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
(1, 1, '68b6cba026792.png', 1, 0, '2025-09-02 10:04:15'),
(5, 2, '68b6d1f7f16aa.png', 1, 0, '2025-09-02 10:12:21'),
(9, 3, '68b6d20ed71c6.png', 1, 0, '2025-09-02 10:16:21'),
(12, 4, '68b6d22a62df2.png', 1, 0, '2025-09-02 10:20:24'),
(15, 1, '68b6cba04520c.png', 0, 1, '2025-09-02 10:49:04'),
(16, 1, '68b6cba0577eb.png', 0, 2, '2025-09-02 10:49:04'),
(17, 1, '68b6cba06ddce.png', 0, 3, '2025-09-02 10:49:04'),
(18, 2, '68b6d1f81accb.png', 0, 1, '2025-09-02 11:16:08'),
(19, 2, '68b6d1f833263.png', 0, 2, '2025-09-02 11:16:08'),
(20, 2, '68b6d1f84aac6.png', 0, 3, '2025-09-02 11:16:08'),
(21, 3, '68b6d20ef2e00.png', 0, 1, '2025-09-02 11:16:31'),
(22, 3, '68b6d20f173f6.png', 0, 2, '2025-09-02 11:16:31'),
(23, 4, '68b6d22a7e018.png', 0, 1, '2025-09-02 11:16:58'),
(24, 4, '68b6d22a975ba.png', 0, 2, '2025-09-02 11:16:58'),
(25, 5, '68b6d2665a9a5.png', 1, 0, '2025-09-02 11:17:58'),
(26, 5, '68b6d26673e5d.png', 0, 1, '2025-09-02 11:17:58'),
(27, 5, '68b6d26683c68.png', 0, 2, '2025-09-02 11:17:58'),
(28, 6, '68b6d38a70e25.png', 1, 0, '2025-09-02 11:22:50'),
(29, 6, '68b6d38a83f7f.png', 0, 1, '2025-09-02 11:22:50'),
(30, 6, '68b6d38a9508c.png', 0, 2, '2025-09-02 11:22:50'),
(31, 7, '68b6d5238262e.png', 1, 0, '2025-09-02 11:29:39'),
(32, 7, '68b6d52399e0a.png', 0, 1, '2025-09-02 11:29:39'),
(33, 7, '68b6d523a95df.png', 0, 2, '2025-09-02 11:29:39'),
(34, 8, '68b6d5dda7c99.png', 1, 0, '2025-09-02 11:32:45'),
(35, 8, '68b6d5ddc1ad3.png', 0, 1, '2025-09-02 11:32:45'),
(36, 8, '68b6d5ddd56fe.png', 0, 2, '2025-09-02 11:32:45'),
(37, 9, '68b6d6bc39c0c.png', 1, 0, '2025-09-02 11:36:28'),
(38, 9, '68b6d6bc52f10.png', 0, 1, '2025-09-02 11:36:28'),
(39, 9, '68b6d6bc63a0a.png', 0, 2, '2025-09-02 11:36:28'),
(40, 10, '68b6d75323e79.png', 1, 0, '2025-09-02 11:38:59'),
(41, 11, '68b6d7f2abe46.png', 1, 0, '2025-09-02 11:41:38'),
(42, 11, '68b6d7f2c1999.png', 0, 1, '2025-09-02 11:41:38'),
(43, 11, '68b6d7f2d4225.png', 0, 2, '2025-09-02 11:41:38'),
(44, 12, '68b6d93ec8f74.png', 1, 0, '2025-09-02 11:47:10'),
(46, 12, '68b6d97707d53.png', 0, 1, '2025-09-02 11:48:07'),
(47, 12, '68b6d9771cdb2.png', 0, 2, '2025-09-02 11:48:07'),
(48, 13, '68b6da9490f3c.png', 1, 0, '2025-09-02 11:52:52'),
(49, 13, '68b6da94a1823.png', 0, 1, '2025-09-02 11:52:52'),
(50, 14, '68b6db239814c.png', 1, 0, '2025-09-02 11:55:15'),
(51, 14, '68b6db23a73c5.png', 0, 1, '2025-09-02 11:55:15'),
(52, 14, '68b6db23b8444.png', 0, 2, '2025-09-02 11:55:15'),
(53, 15, '68b6dcc0498ac.png', 1, 0, '2025-09-02 12:02:08'),
(54, 15, '68b6dcc05e303.png', 0, 1, '2025-09-02 12:02:08'),
(55, 16, '68b6e251cf684.png', 1, 0, '2025-09-02 12:25:53'),
(56, 16, '68b6e251e73e9.png', 0, 1, '2025-09-02 12:25:54'),
(57, 16, '68b6e252044c6.png', 0, 2, '2025-09-02 12:25:54'),
(58, 16, '68b6e2521418b.png', 0, 3, '2025-09-02 12:25:54'),
(59, 17, '68b6e30b3b4ca.png', 1, 0, '2025-09-02 12:28:59'),
(60, 17, '68b6e30b50b71.png', 0, 1, '2025-09-02 12:28:59'),
(61, 17, '68b6e30b608cb.png', 0, 2, '2025-09-02 12:28:59'),
(62, 17, '68b6e30b750f7.png', 0, 3, '2025-09-02 12:28:59'),
(63, 18, '68b6e392cca78.png', 1, 0, '2025-09-02 12:31:14'),
(64, 18, '68b6e392e22db.png', 0, 1, '2025-09-02 12:31:14'),
(65, 18, '68b6e392f2963.png', 0, 2, '2025-09-02 12:31:15'),
(66, 19, '68b6e4614dc05.png', 1, 0, '2025-09-02 12:34:41'),
(67, 19, '68b6e46164ca9.png', 0, 1, '2025-09-02 12:34:41'),
(68, 19, '68b6e4617387e.png', 0, 2, '2025-09-02 12:34:41'),
(69, 20, '68b6e60d55617.png', 1, 0, '2025-09-02 12:41:49'),
(70, 20, '68b6e60d6d09d.png', 0, 1, '2025-09-02 12:41:49'),
(71, 20, '68b6e60d83d60.png', 0, 2, '2025-09-02 12:41:49'),
(72, 21, '68b6edc80fdb1.png', 1, 0, '2025-09-02 13:14:48'),
(73, 21, '68b6edc8185b6.png', 0, 1, '2025-09-02 13:14:48'),
(74, 21, '68b6edc81ef90.png', 0, 2, '2025-09-02 13:14:48'),
(75, 22, '68b6fca55b910.png', 1, 0, '2025-09-02 14:18:13'),
(76, 23, '68b6fdac0ea85.png', 1, 0, '2025-09-02 14:22:36'),
(77, 23, '68b6fdac1a491.png', 0, 1, '2025-09-02 14:22:36'),
(78, 23, '68b6fdac2273e.png', 0, 2, '2025-09-02 14:22:36'),
(79, 24, '68b6feb931fa9.png', 1, 0, '2025-09-02 14:27:05'),
(80, 24, '68b6feb93b9e7.png', 0, 1, '2025-09-02 14:27:05'),
(81, 24, '68b6feb945a28.png', 0, 2, '2025-09-02 14:27:05'),
(82, 24, '68b6feb94fe8b.png', 0, 3, '2025-09-02 14:27:05'),
(83, 25, '68b6ffd6adba5.png', 1, 0, '2025-09-02 14:31:50'),
(84, 25, '68b6ffd6b8825.png', 0, 1, '2025-09-02 14:31:50'),
(85, 26, '68b7005a8d020.png', 1, 0, '2025-09-02 14:34:02'),
(86, 26, '68b7005a9752d.png', 0, 1, '2025-09-02 14:34:02'),
(87, 26, '68b7005aa04b3.png', 0, 2, '2025-09-02 14:34:02'),
(88, 27, '68b702c0204f6.png', 1, 0, '2025-09-02 14:44:16'),
(89, 27, '68b702c027fb9.png', 0, 1, '2025-09-02 14:44:16'),
(90, 27, '68b702c030b6d.png', 0, 2, '2025-09-02 14:44:16'),
(91, 28, '68b703d23a971.png', 1, 0, '2025-09-02 14:48:50'),
(92, 28, '68b703d2431c9.png', 0, 1, '2025-09-02 14:48:50'),
(93, 28, '68b703d24a992.png', 0, 2, '2025-09-02 14:48:50'),
(94, 29, '68b7052e3f585.png', 1, 0, '2025-09-02 14:54:38'),
(95, 29, '68b7052e48c56.png', 0, 1, '2025-09-02 14:54:38'),
(96, 29, '68b7052e4e96f.png', 0, 2, '2025-09-02 14:54:38'),
(97, 30, '68b705d723c72.png', 1, 0, '2025-09-02 14:57:27'),
(98, 31, '68b706cff3426.png', 1, 0, '2025-09-02 15:01:36'),
(99, 31, '68b706d00731f.png', 0, 1, '2025-09-02 15:01:36'),
(100, 31, '68b706d00e68e.png', 0, 2, '2025-09-02 15:01:36'),
(101, 31, '68b706d0159f0.png', 0, 3, '2025-09-02 15:01:36'),
(102, 32, '68b7077babc88.png', 1, 0, '2025-09-02 15:04:27'),
(103, 32, '68b7077bb6bb2.png', 0, 1, '2025-09-02 15:04:27'),
(104, 33, '68b70806038f4.png', 1, 0, '2025-09-02 15:06:46'),
(105, 33, '68b70806127d5.png', 0, 1, '2025-09-02 15:06:46'),
(106, 33, '68b708061a0d2.png', 0, 2, '2025-09-02 15:06:46'),
(107, 33, '68b7080621df4.png', 0, 3, '2025-09-02 15:06:46'),
(108, 33, '68b7080628c3d.png', 0, 4, '2025-09-02 15:06:46'),
(109, 34, '68b70899e4044.png', 1, 0, '2025-09-02 15:09:13'),
(110, 34, '68b70899ed32d.png', 0, 1, '2025-09-02 15:09:13'),
(111, 34, '68b7089a00027.png', 0, 2, '2025-09-02 15:09:14'),
(112, 35, '68b70953b780d.png', 1, 0, '2025-09-02 15:12:19'),
(113, 35, '68b70953c0e67.png', 0, 1, '2025-09-02 15:12:19'),
(114, 35, '68b70953c83d8.png', 0, 2, '2025-09-02 15:12:19'),
(118, 37, '68b70b8a00e16.png', 1, 0, '2025-09-02 15:21:46'),
(119, 37, '68b70b8a08a62.png', 0, 1, '2025-09-02 15:21:46'),
(120, 37, '68b70b8a10294.png', 0, 2, '2025-09-02 15:21:46'),
(121, 38, '68b732171a927.webp', 1, 0, '2025-09-02 18:06:15'),
(122, 39, '68b7327a0479e.webp', 1, 0, '2025-09-02 18:07:54'),
(123, 40, '68b732cf71771.webp', 1, 0, '2025-09-02 18:09:19'),
(124, 41, '68b7332083c9d.webp', 1, 0, '2025-09-02 18:10:40'),
(125, 42, '68b73361e984a.webp', 1, 0, '2025-09-02 18:11:45'),
(126, 43, '68b733986eb03.webp', 1, 0, '2025-09-02 18:12:40'),
(127, 44, '68b747c283ebb.jpg', 1, 0, '2025-09-02 18:13:48'),
(128, 45, '68b73416c8ef2.webp', 1, 0, '2025-09-02 18:14:46'),
(129, 46, '68b7345ea5d6f.webp', 1, 0, '2025-09-02 18:15:58'),
(130, 47, '68b747a9ebcb7.jpg', 1, 0, '2025-09-02 18:16:54'),
(131, 48, '68b747e85fc0c.jpg', 1, 0, '2025-09-02 18:20:24'),
(132, 49, '68b735a81d33d.webp', 1, 0, '2025-09-02 18:21:28'),
(133, 50, '68b735e5aafcc.webp', 1, 0, '2025-09-02 18:22:29'),
(134, 51, '68b736331ba60.webp', 1, 0, '2025-09-02 18:23:47'),
(135, 52, '68b73691a7eae.webp', 1, 0, '2025-09-02 18:25:21'),
(136, 53, '68b736ef99af6.webp', 1, 0, '2025-09-02 18:26:55'),
(137, 54, '68b7377a24eda.webp', 1, 0, '2025-09-02 18:29:14'),
(138, 55, '68b737b601b0b.webp', 1, 0, '2025-09-02 18:30:14'),
(139, 56, '68b73821c5f68.webp', 1, 0, '2025-09-02 18:32:01'),
(140, 57, '68b748062be9a.jpg', 1, 0, '2025-09-02 18:33:10'),
(141, 58, '68b738b798f74.webp', 1, 0, '2025-09-02 18:34:31'),
(142, 59, '68b738fcd501d.webp', 1, 0, '2025-09-02 18:35:40'),
(143, 60, '68b7393573f36.webp', 1, 0, '2025-09-02 18:36:37'),
(144, 61, '68b739acc28fe.webp', 1, 0, '2025-09-02 18:38:36'),
(145, 62, '68b73a2d2a043.webp', 1, 0, '2025-09-02 18:40:45'),
(146, 63, '68b73a6069b6f.webp', 1, 0, '2025-09-02 18:41:36'),
(147, 64, '68b73a8f03c46.webp', 1, 0, '2025-09-02 18:42:23'),
(148, 65, '68b73abd01c24.webp', 1, 0, '2025-09-02 18:43:09'),
(149, 66, '68b73af3326f5.webp', 1, 0, '2025-09-02 18:44:03'),
(150, 67, '68b73b340fb7c.webp', 1, 0, '2025-09-02 18:45:08'),
(151, 68, '68b73b634e960.webp', 1, 0, '2025-09-02 18:45:55'),
(152, 69, '68b73bcc4cf5a.webp', 1, 0, '2025-09-02 18:47:40'),
(153, 70, '68b73c065c367.webp', 1, 0, '2025-09-02 18:48:38'),
(154, 71, '68b73c465f8a5.webp', 1, 0, '2025-09-02 18:49:42'),
(155, 72, '68b73c89d1350.webp', 1, 0, '2025-09-02 18:50:49'),
(156, 73, '68b73cb313a1d.webp', 1, 0, '2025-09-02 18:51:31'),
(157, 74, '68b73cdaa461e.webp', 1, 0, '2025-09-02 18:52:10'),
(158, 75, '68b73d10ee2f1.webp', 1, 0, '2025-09-02 18:53:04'),
(159, 76, '68b73d4a749aa.webp', 1, 0, '2025-09-02 18:54:02'),
(160, 77, '68b73d833a727.webp', 1, 0, '2025-09-02 18:54:59'),
(161, 78, '68b73db5e5b82.webp', 1, 0, '2025-09-02 18:55:49'),
(162, 79, '68b73dfdd3799.webp', 1, 0, '2025-09-02 18:57:01'),
(163, 80, '68b73e3761be1.webp', 1, 0, '2025-09-02 18:57:59'),
(164, 81, '68b73e7375ea3.webp', 1, 0, '2025-09-02 18:58:59');

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
(3, 1, '42', 2),
(4, 1, '36', 2),
(5, 1, '37', 3),
(6, 1, '45', 1),
(7, 1, '44', 4),
(8, 1, '43', 2),
(9, 1, '38', 2),
(10, 1, '39', 5),
(11, 1, '40', 3),
(12, 1, '41', 5),
(13, 2, '38', 2),
(14, 3, '36', 5),
(15, 4, '36', 3),
(16, 81, '37', 5),
(17, 81, '38', 6),
(18, 81, '39', 5),
(19, 80, '38', 10),
(20, 80, '37', 5),
(21, 79, '38', 2),
(22, 79, '36', 4),
(23, 79, '37', 5);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `role` enum('Admin','Member') NOT NULL DEFAULT 'Member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','banned') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password`, `photo`, `role`, `created_at`, `status`) VALUES
(1, 'admin', 'admin@osbuzz.com', 'f865b53623b121fd34ee5426c792e5c33af8c227', '', 'Admin', '2025-09-02 09:08:37', 'active'),
(2, 'eason', 'tanes-wp23@student.tarc.edu.my', '7c222fb2927d828af22f592134e8932480637c0d', '', 'Member', '2025-09-02 19:46:43', 'active');

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
-- Indexes for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `fk_addresses_user` (`user_id`),
  ADD KEY `idx_is_default` (`is_default`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `fk_orders_user` (`user_id`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_order_status` (`order_status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `fk_order_items_order` (`order_id`),
  ADD KEY `fk_order_items_product` (`product_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `fk_history_order` (`order_id`),
  ADD KEY `fk_history_user` (`changed_by`);

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
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `product_photos`
--
ALTER TABLE `product_photos`
  MODIFY `photo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=165;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- Constraints for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD CONSTRAINT `customer_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

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
