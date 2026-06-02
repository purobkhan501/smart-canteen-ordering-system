-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2026 at 09:24 AM
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
-- Database: `smart_canteen`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `name`) VALUES
(9, 'Breakfast'),
(10, 'snacks'),
(11, 'Fast Food'),
(12, 'Rice'),
(13, 'Drinks'),
(14, 'Deals'),
(15, 'special');

-- --------------------------------------------------------

--
-- Table structure for table `discount`
--

CREATE TABLE `discount` (
  `discount_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `percentage` float DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_item`
--

CREATE TABLE `menu_item` (
  `item_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `is_special` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_item`
--

INSERT INTO `menu_item` (`item_id`, `category_id`, `name`, `price`, `image_url`, `quantity`, `is_available`, `is_special`) VALUES
(5, 9, 'Parata with Dal-Vaji', 40.00, 'parata-dalvaji.jpg', 50, 1, 0),
(6, 9, 'Egg Fry', 40.00, 'egg-fry.jpg', 40, 0, 0),
(7, 10, 'Singara', 10.00, 'singara.jpg', 100, 1, 0),
(8, 10, 'Jilapi', 10.00, 'jilapi.jpg', 80, 1, 0),
(9, 10, 'Dal Puri', 10.00, 'puri.jpg', 90, 1, 0),
(10, 10, 'Cake', 40.00, 'cake.jpg', 30, 1, 0),
(11, 11, 'Burger', 70.00, 'burger.jpg', 25, 1, 0),
(12, 11, 'Chicken Roll', 35.00, 'chicken-roll.jpg', 40, 1, 0),
(13, 11, 'Noodles', 35.00, 'noodles.jpg', 35, 1, 0),
(14, 11, 'Shawarma', 80.00, 'shawarma.jpg', 20, 1, 0),
(15, 11, 'Sandwich', 45.00, 'sandwich.jpg', 30, 1, 0),
(16, 11, 'Pizza Slice', 50.00, 'pizza.jpg', 25, 1, 0),
(17, 11, 'Hot Dog', 60.00, 'hot-dog.jpg', 20, 1, 0),
(18, 12, 'Fried Rice', 85.00, 'fried-rice.jpg', 30, 1, 0),
(19, 12, 'Rice with Chicken', 105.00, 'rice-chicken.jpg', 25, 1, 0),
(20, 12, 'Chicken Khichuri', 75.00, 'chicken-khichuri.jpg', 20, 1, 0),
(21, 12, 'Dim Khichuri', 60.00, 'dim-khichuri.jpg', 20, 1, 0),
(22, 13, 'Water', 20.00, 'water.jpg', 100, 1, 0),
(23, 13, 'Mojo', 20.00, 'mojo.jpg', 60, 1, 0),
(24, 13, '7 Up', 20.00, '7up.jpg', 60, 1, 0),
(25, 13, 'Fanta', 20.00, 'fanta.jpg', 60, 1, 0),
(26, 13, 'Tea', 10.00, 'tea.jpg', 100, 1, 0),
(27, 13, 'Lemon Tea', 10.00, 'lemon-tea.jpg', 80, 1, 0),
(28, 13, 'Coffee', 25.00, 'coffee.jpg', 50, 1, 0),
(29, 13, 'Black Coffee', 25.00, 'black-coffee.jpg', 40, 1, 0),
(30, 14, 'Premium Mutton Kacchi', 210.00, 'kacchi.jpg', 15, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `token_number` varchar(50) DEFAULT NULL,
  `pickup_time` datetime DEFAULT NULL,
  `tip_amount` decimal(10,2) DEFAULT NULL,
  `ready_time` datetime DEFAULT NULL,
  `cancel_time` datetime DEFAULT NULL,
  `is_cancelled` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `status`, `total_amount`, `payment_status`, `token_number`, `pickup_time`, `tip_amount`, `ready_time`, `cancel_time`, `is_cancelled`) VALUES
(1, 4, '2026-05-16 15:37:57', 'Pending', 120.00, 'Unpaid', NULL, NULL, NULL, NULL, NULL, 0),
(2, 4, '2026-05-16 15:44:11', 'Picked Up', 120.00, 'Paid', NULL, '2026-05-17 12:25:28', NULL, '2026-05-17 12:25:26', NULL, 0),
(3, 4, '2026-05-16 15:50:13', 'Picked Up', 120.00, 'Unpaid', 'T-0003', '2026-05-17 12:00:39', NULL, '2026-05-17 02:57:51', NULL, 0),
(4, 3, '2026-05-16 15:51:52', 'Picked Up', 120.00, 'Unpaid', 'T-0004', '2026-05-16 15:52:21', NULL, '2026-05-16 15:52:15', NULL, 0),
(5, 4, '2026-05-17 02:55:18', 'Picked Up', 40.00, 'Unpaid', 'T-0005', '2026-05-17 12:00:37', NULL, '2026-05-17 12:00:32', NULL, 0),
(6, 4, '2026-05-17 12:56:37', 'Picked Up', 75.00, 'Paid', 'T-0006', '2026-05-17 12:57:52', NULL, '2026-05-17 12:57:13', NULL, 0),
(7, 4, '2026-05-17 13:14:40', 'Picked Up', 40.00, 'Paid', 'T-0007', '2026-05-17 13:16:28', NULL, '2026-05-17 13:16:26', NULL, 0),
(8, 4, '2026-05-17 13:20:05', 'Pending', 185.00, 'Unpaid', 'T-0008', NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`order_item_id`, `order_id`, `item_id`, `quantity`, `unit_price`) VALUES
(5, 5, 5, 1, 40.00),
(6, 6, 12, 1, 35.00),
(7, 6, 5, 1, 40.00),
(8, 7, 5, 1, 40.00),
(9, 8, 5, 1, 40.00),
(10, 8, 6, 1, 40.00),
(11, 8, 11, 1, 70.00),
(12, 8, 12, 1, 35.00);

-- --------------------------------------------------------

--
-- Table structure for table `pre_order`
--

CREATE TABLE `pre_order` (
  `preorder_id` int(11) NOT NULL,
  `special_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `preorder_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `special_item`
--

CREATE TABLE `special_item` (
  `special_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `available_date` date DEFAULT NULL,
  `min_orders` int(11) DEFAULT NULL,
  `current_preorders` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','staff','admin') NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `name`, `email`) VALUES
(1, 'admin', '1234', 'admin', 'Main Admin', 'admin@gmail.com'),
(2, 'student1', '1234', 'student', 'Rahim', 'rahim@gmail.com'),
(3, 'staff1', '1234', 'staff', 'Karim', 'karim@gmail.com'),
(4, 'puja123', '1234', 'student', 'Puja', 'puja123@gmail.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `discount`
--
ALTER TABLE `discount`
  ADD PRIMARY KEY (`discount_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_item_id` (`order_item_id`);

--
-- Indexes for table `menu_item`
--
ALTER TABLE `menu_item`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `pre_order`
--
ALTER TABLE `pre_order`
  ADD PRIMARY KEY (`preorder_id`),
  ADD KEY `special_id` (`special_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `special_item`
--
ALTER TABLE `special_item`
  ADD PRIMARY KEY (`special_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `discount`
--
ALTER TABLE `discount`
  MODIFY `discount_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu_item`
--
ALTER TABLE `menu_item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pre_order`
--
ALTER TABLE `pre_order`
  MODIFY `preorder_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `special_item`
--
ALTER TABLE `special_item`
  MODIFY `special_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `discount`
--
ALTER TABLE `discount`
  ADD CONSTRAINT `discount_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `menu_item` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discount_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`order_item_id`) REFERENCES `order_item` (`order_item_id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_item`
--
ALTER TABLE `menu_item`
  ADD CONSTRAINT `menu_item_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_item` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `pre_order`
--
ALTER TABLE `pre_order`
  ADD CONSTRAINT `pre_order_ibfk_1` FOREIGN KEY (`special_id`) REFERENCES `special_item` (`special_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pre_order_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `special_item`
--
ALTER TABLE `special_item`
  ADD CONSTRAINT `special_item_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `menu_item` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `special_item_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
