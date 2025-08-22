-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 22, 2025 at 09:22 PM
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
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `category` varchar(50) DEFAULT 'Shoes',
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_name`, `brand`, `category`, `price`, `description`, `photo`, `created_at`) VALUES
(1, 'Way Of Wade 10', 'Lining', 'Casual', 699.00, '', '68a58e71d37ce.jpg', '2025-08-10 15:32:28'),
(2, 'Ultraboost', 'Adidas', 'Formal', 459.00, 'Comfortable running shoes', NULL, '2025-08-10 15:32:28'),
(3, 'Classic Sneakers', 'Nike', 'Basketball', 399.00, 'Classic style everyday sneakers', NULL, '2025-08-10 15:32:28'),
(4, 'Kids Runner', 'Puma', 'Other', 199.00, 'Lightweight kids running shoes', NULL, '2025-08-10 15:32:28');

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
(13, 3, '37', 2),
(15, 4, '40', 4);

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
  `role` enum('Admin','Member') NOT NULL DEFAULT 'Member'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password`, `name`, `address`, `phone`, `photo`, `role`) VALUES
(13, 'eason', 'taneason1111@gmail.com', '7c222fb2927d828af22f592134e8932480637c0d', 'Tan Ea Son', 'No 6 Jalan Indah 10/5Taman Pertama', '0122226133', '6898d553e9814.jpg', 'Admin'),
(15, 'Tan1221', 'taneason0000@gmail.com', '7c222fb2927d828af22f592134e8932480637c0d', '', '', '', '687ce54d6c885.jpg', 'Member'),
(18, 'taneason', 'taneason0912@gmail.com', '208352c22d8f058ceaeed06305c7600fc4935a2d', '', '', '', '', 'Member'),
(19, 'admin', 'admin@osbuzz.com', '356a192b7913b04c54574d18c28d46e6395428ab', 'System Administrator', '123 Admin Street, Admin City, AC 12345', '01-234-5678', '', 'Admin'),
(20, 'manager', 'manager@osbuzz.com', 'da4b9237bacccdf19c0760cab7aec4a8359010b0', 'Store Manager', '456 Manager Ave, Business District, BD 67890', '01-345-6789', '', 'Admin'),
(21, 'john_doe', 'john.doe@email.com', '77de68daecd823babbb58edb1c8e14d7106e83bb', 'John Doe', '789 Main Street, Suburbia, SB 11111', '01-456-7890', '', 'Member'),
(22, 'jane_smith', 'jane.smith@email.com', '1b6453892473a467d07372d45eb05abc2031647a', 'Jane Smith', '321 Oak Avenue, Riverside, RS 22222', '01-567-8901', '', 'Member'),
(23, 'mike_wilson', 'mike.wilson@email.com', 'ac3478d69a3c81fa62e60f5c3696165a4e5e6ac4', 'Mike Wilson', '654 Pine Road, Hillside, HS 33333', '01-678-9012', '', 'Member'),
(24, 'sarah_brown', 'sarah.brown@email.com', 'c1dfd96eea8cc2b62785275bca38ac261256e278', 'Sarah Brown', '987 Elm Street, Downtown, DT 44444', '01-789-0123', '', 'Member'),
(25, 'david_lee', 'david.lee@email.com', '902ba3cda1883801594b6e1b452790cc53948fda', 'David Lee', '147 Maple Drive, Greenwood, GW 55555', '01-890-1234', '', 'Member'),
(26, 'lisa_taylor', 'lisa.taylor@email.com', 'fe5dbbcea5ce7e2988b8c69bcfdfde8904aabc1f', 'Lisa Taylor', '258 Cedar Lane, Lakeside, LS 66666', '01-901-2345', '', 'Member'),
(27, 'alex_johnson', 'alex.johnson@email.com', '0ade7c2cf97f75d009975f4d720d1fa6c19f4897', 'Alex Johnson', '369 Birch Court, Mountain View, MV 77777', '01-012-3456', '', 'Member'),
(28, 'emily_davis', 'emily.davis@email.com', 'b1d5781111d84f7b3fe45a0852e59758cd7a87e5', 'Emily Davis', '741 Willow Street, Valley Heights, VH 88888', '01-123-4567', '', 'Member'),
(29, 'testuser1', 'test1@test.com', '356a192b7913b04c54574d18c28d46e6395428ab', 'Test User One', 'Test Address 1', '01-111-1111', '', 'Member'),
(30, 'testuser2', 'test2@test.com', 'da4b9237bacccdf19c0760cab7aec4a8359010b0', 'Test User Two', 'Test Address 2', '01-222-2222', '', 'Member'),
(31, 'customer123', 'customer@shop.com', '77de68daecd823babbb58edb1c8e14d7106e83bb', 'Regular Customer', '999 Customer Boulevard, Shopping District, SD 99999', '01-999-9999', '', 'Member');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`);

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
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
