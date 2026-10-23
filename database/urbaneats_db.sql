-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2026 at 10:52 PM
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
-- Database: `urbaneats_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `log_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `details` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'LOGIN', 'User logged in successfully', '192.168.1.10', '2026-09-28 04:57:46'),
(2, 2, 'REGISTER', 'New customer account created', '192.168.1.11', '2026-09-29 04:57:46'),
(3, 3, 'LOGOUT', 'User logged out', '192.168.1.12', '2026-09-30 04:57:46'),
(4, 4, 'PROFILE_UPDATE', 'Profile details updated', '192.168.1.13', '2026-10-01 04:57:46'),
(5, 5, 'PASSWORD_CHANGE', 'Password changed', '192.168.1.14', '2026-10-02 04:57:46'),
(6, 1, 'ROLE_CHANGE', 'Role changed by administrator', '192.168.1.15', '2026-10-03 04:57:46'),
(7, 1, 'USER_DEACTIVATED', 'Account deactivated by administrator', '192.168.1.16', '2026-10-04 04:57:46'),
(8, 1, 'LOGIN', 'User logged in successfully', '192.168.1.17', '2026-10-05 04:57:46'),
(9, 2, 'LOGIN', 'Staff member logged in', '192.168.1.18', '2026-10-06 04:57:46'),
(10, 3, 'LOGOUT', 'User logged out', '192.168.1.19', '2026-10-07 04:57:46'),
(11, 5, 'PROFILE_UPDATE', 'Profile details updated', '192.168.1.20', '2026-10-08 04:57:46'),
(12, 5, 'PASSWORD_CHANGE', 'Password changed', '192.168.1.21', '2026-10-09 04:57:46'),
(13, 1, 'ROLE_CHANGE', 'Role changed by administrator', '192.168.1.22', '2026-10-10 04:57:46'),
(14, 1, 'USER_ACTIVATED', 'Account activated by administrator', '192.168.1.23', '2026-10-11 04:57:46'),
(15, 5, 'LOGIN', 'User logged in successfully', '192.168.1.24', '2026-10-12 04:57:46'),
(16, 2, 'LOGIN', 'Staff member logged in', '192.168.1.25', '2026-10-13 04:57:46'),
(17, 5, 'LOGOUT', 'User logged out', '192.168.1.26', '2026-10-14 04:57:46'),
(18, 5, 'PROFILE_UPDATE', 'Profile details updated', '192.168.1.27', '2026-10-15 04:57:46'),
(19, 5, 'PASSWORD_CHANGE', 'Password changed', '192.168.1.28', '2026-10-16 04:57:46'),
(20, 1, 'ROLE_CHANGE', 'Role changed by administrator', '192.168.1.29', '2026-10-17 04:57:46'),
(21, 1, 'LOGIN', 'User logged in successfully', '192.168.1.30', '2026-10-18 04:57:46'),
(22, 2, 'LOGIN', 'User logged in successfully', '192.168.1.31', '2026-10-19 04:57:46'),
(23, 5, 'LOGIN', 'New customer logged in', '192.168.1.32', '2026-10-20 04:57:46'),
(24, 5, 'LOGOUT', 'User logged out', '192.168.1.33', '2026-10-21 04:57:46'),
(25, NULL, 'LOGIN_FAILED', 'Failed login attempt for admin@gmail.com', '::1', '2026-10-22 18:31:24'),
(26, NULL, 'LOGIN_FAILED', 'Failed login attempt for admin@gmail.com', '::1', '2026-10-22 18:32:23'),
(27, 6, 'REGISTER', 'New customer registration: nikinit2917@gmail.com', '::1', '2026-10-22 18:34:40'),
(28, NULL, 'LOGIN_FAILED', 'Failed login attempt for admin@gmail.com', '::1', '2026-10-22 18:34:46'),
(29, 6, 'LOGIN', 'User logged in successfully', '::1', '2026-10-22 18:35:06'),
(30, 6, 'ACCESS_DENIED', 'Tried to open /UrbanEats/UrbanEats/public/menu/manage-stock.php', '::1', '2026-10-22 18:48:31');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `created_at`) VALUES
(1, 'Sri Lankan', 'Traditional authentic Sri Lankan spices and rice dishes.', '2026-10-22 18:42:28'),
(2, 'Fast Food', 'Quick bites, burgers, kottu and short eats.', '2026-10-22 18:42:28'),
(3, 'Beverages', 'Fresh juices, iced tea and soft drinks.', '2026-10-22 18:42:28');

-- --------------------------------------------------------

--
-- Table structure for table `dishes`
--

CREATE TABLE `dishes` (
  `dish_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `dish_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT 'default-dish.jpg',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dishes`
--

INSERT INTO `dishes` (`dish_id`, `category_id`, `dish_name`, `description`, `price`, `image_url`, `is_active`, `created_at`) VALUES
(1, 2, 'Signature Chicken Kottu', 'Wok-tossed godamba roti with tender chicken, vegetables, and rich aromatic gravies.', 950.00, 'Chicken-Kottu.jpg', 1, '2026-10-22 18:43:14'),
(2, 1, 'Lamprais', 'Dutch-influenced Sri Lankan specialty: yellow rice, meat curry, ash plantain, and balachchi wrapped in banana leaf.', 1200.00, 'lamprise.jpg', 1, '2026-10-22 18:43:14'),
(3, 1, 'Pol Roti with Chicken Curry', 'Handmade fresh coconut rotis served with spicy, slow-cooked chicken curry.', 750.00, 'pol_roti.jpg', 1, '2026-10-22 18:43:14'),
(4, 2, 'Cheesy Seafood Pasta', 'Creamy pasta tossed with fresh prawns, cuttlefish, and melted mozzarella cheese.', 1400.00, 'pasta.jpg', 1, '2026-10-22 18:43:14'),
(5, 1, 'Chicken Fried Rice', 'Wok-tossed basmati rice with tender chicken, eggs, and fresh veggies', 1150.00, 'fried.jpg', 1, '2026-10-23 19:46:11'),
(6, 2, 'Beef Burger Special', 'Juicy beef patty with cheese, caramelized onions, and house sauce', 1350.00, 'burger.jpg', 1, '2026-10-23 19:46:11'),
(7, 2, 'Creamy Carbonara', 'Spaghetti tossed in rich creamy sauce with bacon and parmesan', 1250.00, 'carbonara.jpg', 1, '2026-10-23 19:46:11'),
(8, 2, 'Red Velvet Cake', 'Rich and moist red velvet sponge layered with smooth cream cheese frosting', 850.00, 'red.jpg', 1, '2026-10-23 19:46:11'),
(9, 2, 'Grilled Chicken Sub', 'Footlong sandwich stuffed with grilled chicken and fresh salad', 980.00, 'grilled-chicken.jpg', 1, '2026-10-23 19:46:11'),
(10, 2, 'Chocolate Lava Cake', 'Warm chocolate cake with a molten fudge center', 550.00, 'lavacake.jpg', 1, '2026-10-23 19:46:11'),
(15, 3, 'Fresh Iced Coffee', 'Chilled espresso blended with milk, ice, and a dash of vanilla', 450.00, 'Iced-Coffee.jpg', 1, '2026-10-23 20:41:03'),
(16, 3, 'Mango Mojito', 'Refreshing virgin mojito infused with fresh mango puree and mint', 550.00, 'mango-mojito.jpg', 1, '2026-10-23 20:41:03'),
(17, 3, 'Chocolate Milkshake', 'Creamy chocolate ice cream blended with rich cocoa and milk', 600.00, 'chocolate.jpg', 1, '2026-10-23 20:41:03'),
(18, 3, 'Lime Mint Cooler', 'Zesty fresh lime juice mixed with crushed mint leaves and soda', 380.00, 'mint.jpg', 1, '2026-10-23 20:41:03');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `quantity_in_stock` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) NOT NULL DEFAULT 5,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `dish_id`, `quantity_in_stock`, `reorder_level`, `last_updated`) VALUES
(1, 1, 25, 5, '2026-10-22 18:43:58'),
(2, 2, 15, 3, '2026-10-22 18:43:58'),
(3, 3, 30, 5, '2026-10-22 18:43:58'),
(4, 4, 10, 2, '2026-10-22 18:43:58'),
(5, 5, 50, 5, '2026-10-23 19:50:14'),
(6, 6, 40, 5, '2026-10-23 19:50:14'),
(7, 7, 35, 5, '2026-10-23 19:50:14'),
(8, 8, 25, 5, '2026-10-23 19:50:14'),
(9, 9, 30, 5, '2026-10-23 19:50:14'),
(10, 10, 45, 5, '2026-10-23 19:50:14'),
(11, 15, 50, 5, '2026-10-23 20:41:59'),
(12, 16, 40, 5, '2026-10-23 20:41:59'),
(13, 17, 35, 5, '2026-10-23 20:41:59'),
(14, 18, 60, 5, '2026-10-23 20:41:59');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `attempt_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(150) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `was_successful` tinyint(1) NOT NULL DEFAULT 0,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`attempt_id`, `email`, `ip_address`, `was_successful`, `attempted_at`) VALUES
(1, 'customer1@urbaneats.lk', '192.168.1.10', 1, '2026-09-22 04:57:46'),
(2, 'admin@urbaneats.lk', '192.168.1.11', 1, '2026-09-23 04:57:46'),
(3, 'staff1@urbaneats.lk', '192.168.1.12', 1, '2026-09-24 04:57:46'),
(4, 'customer1@urbaneats.lk', '192.168.1.13', 0, '2026-09-25 04:57:46'),
(5, 'customer1@urbaneats.lk', '192.168.1.14', 1, '2026-09-26 04:57:46'),
(6, 'staff2@urbaneats.lk', '192.168.1.15', 1, '2026-09-27 04:57:46'),
(7, 'staff3@urbaneats.lk', '192.168.1.16', 1, '2026-09-28 04:57:46'),
(8, 'admin@urbaneats.lk', '192.168.1.17', 0, '2026-09-29 04:57:46'),
(9, 'admin@urbaneats.lk', '192.168.1.18', 1, '2026-09-30 04:57:46'),
(10, 'customer1@urbaneats.lk', '192.168.1.19', 1, '2026-10-01 04:57:46'),
(11, 'staff1@urbaneats.lk', '192.168.1.20', 1, '2026-10-02 04:57:46'),
(12, 'customer1@urbaneats.lk', '192.168.1.21', 0, '2026-10-03 04:57:46'),
(13, 'customer1@urbaneats.lk', '192.168.1.22', 1, '2026-10-04 04:57:46'),
(14, 'staff2@urbaneats.lk', '192.168.1.23', 1, '2026-10-05 04:57:46'),
(15, 'admin@urbaneats.lk', '192.168.1.24', 1, '2026-10-06 04:57:46'),
(16, 'admin@urbaneats.lk', '192.168.1.25', 0, '2026-10-07 04:57:46'),
(17, 'staff3@urbaneats.lk', '192.168.1.26', 1, '2026-10-08 04:57:46'),
(18, 'customer1@urbaneats.lk', '192.168.1.27', 1, '2026-10-09 04:57:46'),
(19, 'staff1@urbaneats.lk', '192.168.1.28', 1, '2026-10-10 04:57:46'),
(20, 'customer1@urbaneats.lk', '192.168.1.29', 0, '2026-10-11 04:57:46'),
(21, 'unknown.person@gmail.com', '203.94.10.55', 0, '2026-10-20 04:57:46'),
(22, 'admin@gmail.com', '::1', 0, '2026-10-22 18:31:24'),
(23, 'admin@gmail.com', '::1', 0, '2026-10-22 18:32:22'),
(24, 'admin@gmail.com', '::1', 0, '2026-10-22 18:34:46'),
(25, 'nikinit2917@gmail.com', '::1', 1, '2026-10-22 18:35:06');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `reset_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` tinyint(3) UNSIGNED NOT NULL,
  `role_name` varchar(30) NOT NULL,
  `description` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `description`) VALUES
(1, 'Administrator', 'Manages users, roles, audit log and sales reports'),
(2, 'Kitchen Staff', 'Kitchen staff: dishes, stock and order processing'),
(3, 'Customer', 'Browses the menu, orders and tracks orders');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `movement_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `change_quantity` int(11) NOT NULL,
  `movement_type` enum('restock','wastage','sale') NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` tinyint(3) UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `role_id`, `full_name`, `email`, `password_hash`, `phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Nethmi Perera', 'admin@urbaneats.lk', '$2y$10$J/7RaRuXL9cwH2qZJsp73OmBjWS1tM7Xwo3WoaWTLOycIVKLQgNPG', '0711234501', 1, '2026-08-25 04:57:46', '2026-10-22 04:57:46'),
(2, 2, 'Kasun Fernando', 'staff1@urbaneats.lk', '$2y$10$AyOmp1vA5cIrIj4IMe2pDuDy/ocpDdNf8STExjU84PcXgxiGexhvy', '0711234502', 1, '2026-08-27 04:57:46', '2026-10-22 04:57:46'),
(3, 2, 'Dilani Jayasinghe', 'staff2@urbaneats.lk', '$2y$10$kgAv/QA852vY0QwOzb.ioee5z24erejcG7ObQS3ypEjRET4ApyU6C', '0711234503', 1, '2026-08-29 04:57:46', '2026-10-22 04:57:46'),
(4, 2, 'Ruwan Silva', 'staff3@urbaneats.lk', '$2y$10$Qo0ZeWpo96YqKzw1sOVaUOzByo8W60GhCP7BgDkA7Ped7FFUkcgxq', '0711234504', 1, '2026-08-31 04:57:46', '2026-10-22 04:57:46'),
(5, 3, 'Amaya Wickramasinghe', 'customer1@urbaneats.lk', '$2y$10$Q/ps6rR/Y2.sQobKITvcS.s2f3Co.rkK6YeaOojjKeMMfE3.jIOOO', '0723530829', 1, '2026-09-02 04:57:46', '2026-10-22 04:57:46'),
(6, 3, 'O.Nikini Thathsarani Perera', 'nikinit2917@gmail.com', '$2y$10$7sFY89poIrbTON1ftUFGpeLipMPMJ.szQ2nxGPdYiZIwqX95oa1yS', '+94781470454', 1, '2026-10-22 18:34:39', '2026-10-22 18:34:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_created` (`created_at`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `dishes`
--
ALTER TABLE `dishes`
  ADD PRIMARY KEY (`dish_id`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_price` (`price`),
  ADD KEY `idx_name` (`dish_name`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD UNIQUE KEY `dish_id` (`dish_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `idx_attempts_email_time` (`email`,`attempted_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`reset_id`),
  ADD UNIQUE KEY `uq_reset_token` (`token_hash`),
  ADD KEY `idx_reset_user` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `uq_roles_name` (`role_name`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`movement_id`),
  ADD KEY `fk_sm_dish` (`dish_id`),
  ADD KEY `fk_sm_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_role` (`role_id`),
  ADD KEY `idx_users_active` (`is_active`),
  ADD KEY `idx_users_name` (`full_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `dishes`
--
ALTER TABLE `dishes`
  MODIFY `dish_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `attempt_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `movement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `dishes`
--
ALTER TABLE `dishes`
  ADD CONSTRAINT `dishes_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`dish_id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `fk_sm_dish` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`dish_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
