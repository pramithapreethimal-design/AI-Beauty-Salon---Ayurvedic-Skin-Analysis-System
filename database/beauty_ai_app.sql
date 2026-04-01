-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2026 at 12:39 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `beauty_ai_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_time` time DEFAULT NULL,
  `status` enum('Pending','Approved','Completed') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `skin_type` enum('Dry','Oily','Normal') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(100) DEFAULT NULL,
  `skin_type` enum('Dry','Oily','Normal') DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skin_analysis`
--

CREATE TABLE `skin_analysis` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `skin_type` enum('Dry','Oily','Normal') DEFAULT NULL,
  `confidence` decimal(5,2) DEFAULT 0.00,
  `image_path` varchar(255) DEFAULT NULL,
  `analyzed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `oily_level` decimal(5,2) DEFAULT 0.00,
  `dry_level` decimal(5,2) DEFAULT 0.00,
  `normal_level` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skin_analysis`
--

INSERT INTO `skin_analysis` (`id`, `user_id`, `skin_type`, `confidence`, `image_path`, `analyzed_at`, `oily_level`, `dry_level`, `normal_level`, `created_at`) VALUES
(24, 1, 'Oily', 52.58, '1768414792_test.jpg', '2026-01-14 18:19:55', 52.58, 2.54, 44.88, '2026-01-14 18:22:36'),
(25, 3, 'Dry', 75.41, '1768416946_test11.jpeg', '2026-01-14 18:55:49', 6.92, 75.41, 17.67, '2026-01-14 18:55:49'),
(26, 3, 'Dry', 75.41, '1768418376_test11.jpeg', '2026-01-14 19:19:41', 6.92, 75.41, 17.67, '2026-01-14 19:19:41'),
(29, 4, 'Dry', 75.41, '1768420503_test11.jpeg', '2026-01-14 19:55:06', 6.92, 75.41, 17.67, '2026-01-14 19:55:06'),
(30, 1, 'Dry', 80.84, '1768452521_test13.jpg', '2026-01-15 04:48:44', 3.46, 80.84, 15.70, '2026-01-15 04:48:44'),
(31, 1, 'Dry', 75.41, '1768452554_test11.jpeg', '2026-01-15 04:49:16', 6.92, 75.41, 17.67, '2026-01-15 04:49:16'),
(32, 1, 'Oily', 39.74, '1768452578_test4.jpeg', '2026-01-15 04:49:40', 39.74, 38.35, 21.90, '2026-01-15 04:49:40'),
(33, 1, 'Normal', 38.60, '1768452695_test15.jpeg', '2026-01-15 04:51:38', 31.81, 29.60, 38.60, '2026-01-15 04:51:38'),
(34, 1, 'Dry', 81.68, '1768452849_test16.jpeg', '2026-01-15 04:54:12', 0.92, 81.68, 17.40, '2026-01-15 04:54:12'),
(35, 1, 'Dry', 63.18, '1768453039_shiny-oily-skin-closeup-detailed-600nw-2553005379.jpg', '2026-01-15 04:57:21', 33.34, 63.18, 3.48, '2026-01-15 04:57:21'),
(37, 1, 'Oily', 83.35, '1768453938_Normal-skin.jpg', '2026-01-15 05:12:26', 83.35, 11.02, 5.63, '2026-01-15 05:12:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'pramitha', 'pramithapreethimal@gmail.com', '$2y$10$t6FwW8nVw2MU6.fDRuzsuupyGIpRxeoIHypUdRnXQLG3QIb9Ee4BW', 'user', '2026-01-14 13:58:11'),
(2, 'nadeesah', 'nadeesha@gmail.com', '$2y$10$ZkPeqWInbQTS4x8KXOG6ueL52pl0ptw3X1gMSCAPnGzXSbgCFN78q', 'user', '2026-01-14 13:59:02'),
(3, 'sahan', 'sahan@gmailcom', '$2y$10$5UCCcSsMMDMtKJ3CkXXZ4ezhnZzh2klqgffg6YGKQk6Vaqq9k4WfG', 'user', '2026-01-14 18:36:32'),
(4, 'pramitha sahan preethimal', 'pramitha@gmail.com', '$2y$10$soYbXxxJTTEwCNtk6buJMuTnE6x/RGwunhcTaMsxU7kYv1kIparP2', 'user', '2026-01-14 19:54:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `skin_analysis`
--
ALTER TABLE `skin_analysis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skin_analysis`
--
ALTER TABLE `skin_analysis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `skin_analysis`
--
ALTER TABLE `skin_analysis`
  ADD CONSTRAINT `skin_analysis_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
