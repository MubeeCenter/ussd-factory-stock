-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 31, 2026 at 04:26 PM
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
-- Database: `mubeetec_portfolio_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `factories`
--

CREATE TABLE `factories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `threshold` int(11) DEFAULT 50,
  `last_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `factory_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `code`, `name`, `quantity`, `threshold`, `last_updated`, `factory_id`) VALUES
(1, '101', 'Grade A Steel', 450, 100, '2026-08-29 14:21:38', 1),
(2, '102', 'Grade B Steel', 230, 80, '2026-08-29 14:21:38', 1),
(3, '201', 'Cement 50kg', 1200, 300, '2026-08-29 14:21:38', 1),
(4, '301', 'Industrial Paint (White)', 85, 40, '2026-08-29 14:21:38', 1);

-- --------------------------------------------------------

--
-- Table structure for table `restock_requests`
--

CREATE TABLE `restock_requests` (
  `id` int(11) NOT NULL,
  `material_code` varchar(20) NOT NULL,
  `requested_by` varchar(20) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `factory_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `restock_requests`
--

INSERT INTO `restock_requests` (`id`, `material_code`, `requested_by`, `status`, `created_at`, `updated_at`, `factory_id`) VALUES
(1, '101', '+2349034394409', 'approved', '2026-08-29 15:18:50', '2026-08-29 16:26:36', 1);

-- --------------------------------------------------------

--
-- Table structure for table `ussd_admins`
--

CREATE TABLE `ussd_admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `factory_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ussd_admins`
--

INSERT INTO `ussd_admins` (`id`, `username`, `password_hash`, `email`, `created_at`, `factory_id`) VALUES
(1, 'admin', '$2a$12$/nRU4jvV8LA6E18UmFnyRuynh0H1XGCLkJ27fCooTJD/G7lSvrWMe', 'admin@mubeetech.com.ng', '2026-08-29 15:55:51', 1);

-- --------------------------------------------------------

--
-- Table structure for table `ussd_sessions`
--

CREATE TABLE `ussd_sessions` (
  `session_id` varchar(50) NOT NULL,
  `response` text DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ussd_sessions`
--

INSERT INTO `ussd_sessions` (`session_id`, `response`, `phone_number`, `updated_at`) VALUES
('ATUid_669df0c34dc6c767137c48e1d70dff9f', '{\"state\":\"check_stock\"}', '+2349034394409', '2026-08-29 15:17:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `factories`
--
ALTER TABLE `factories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `restock_requests`
--
ALTER TABLE `restock_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ussd_admins`
--
ALTER TABLE `ussd_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `ussd_sessions`
--
ALTER TABLE `ussd_sessions`
  ADD PRIMARY KEY (`session_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `factories`
--
ALTER TABLE `factories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `restock_requests`
--
ALTER TABLE `restock_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ussd_admins`
--
ALTER TABLE `ussd_admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
