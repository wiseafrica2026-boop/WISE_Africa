-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2026 at 05:52 PM
-- Server version: 10.1.19-MariaDB
-- PHP Version: 7.0.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wise_africa`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','staff') DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Super Admin', 'admin@wiseafrica.org', '$2y$10$Y6EqUw/8AzKLQGnX7x/yy.TKImXvUx5mw3YcX26BXX6hFAMUqck9i', 'super_admin', '2026-04-13 09:46:20');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `organization_name` varchar(255) NOT NULL,
  `organization_type` varchar(50) NOT NULL,
  `location` varchar(255) NOT NULL,
  `contact_person` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `size_count` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `organization_name`, `organization_type`, `location`, `contact_person`, `email`, `phone`, `size_count`, `status`, `applied_at`) VALUES
(1, 'Elimu Smart Academy', '', 'Nairobi', 'Benardson KK', 'elimusmart@gmail.com', '0787797800', 320, 'approved', '2026-04-13 08:45:21'),
(2, 'Burning Bush Gospel Church', 'Church', 'Embu', 'John John', 'burningbush@gmail.com', '0787797800', 250, 'approved', '2026-04-13 08:59:24');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `organization_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `organization_name`, `email`, `phone`, `password`, `status`, `created_at`) VALUES
(1, 'Burning Bush Gospel Church', 'burningbush@gmail.com', '0787797800', '$2y$10$7/bxkbxO4UcLZh3pMtkhweOmL6crLBGAzs0SLitjHX3DBR2HGxtH6', 'active', '2026-04-13 09:48:03'),
(2, 'Elimu Smart Academy', 'elimusmart@gmail.com', '0787797800', '$2y$10$PqGDGRnLc8p9.vCzN0JitO3hz3/V8y4jViTalGT93/M8HQZr9DM5m', 'active', '2026-04-13 11:13:31');

-- --------------------------------------------------------

--
-- Table structure for table `payment_logs`
--

CREATE TABLE `payment_logs` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `amount` decimal(10,2) DEFAULT '0.00',
  `payment_date` date NOT NULL,
  `next_due_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `payment_logs`
--

INSERT INTO `payment_logs` (`id`, `client_id`, `service_type`, `amount`, `payment_date`, `next_due_date`, `created_at`) VALUES
(1, 2, 'seo', '0.00', '2026-04-13', '2026-05-13', '2026-04-13 14:06:07'),
(2, 2, 'maintenance', '0.00', '2026-04-13', '2026-05-13', '2026-04-13 14:06:18'),
(3, 2, 'domain', '0.00', '2026-04-13', '2027-04-13', '2026-04-13 14:06:28'),
(4, 2, 'hosting', '0.00', '2026-04-13', '2027-04-13', '2026-04-13 14:06:41');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `description` text,
  `status` enum('in_progress','active','inactive','completed') DEFAULT 'in_progress',
  `progress_percentage` int(3) DEFAULT '0',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `client_id`, `project_name`, `description`, `status`, `progress_percentage`, `start_date`, `end_date`, `created_at`) VALUES
(1, 1, 'Burning Bush Church Management System', 'Church management System', 'in_progress', 30, NULL, NULL, '2026-04-13 10:04:06'),
(2, 2, 'Elimu Smart Academy Web Infrastructure', 'A School web infrastructure', 'in_progress', 10, NULL, NULL, '2026-04-13 14:10:29');

-- --------------------------------------------------------

--
-- Table structure for table `project_updates`
--

CREATE TABLE `project_updates` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `update_message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `project_updates`
--

INSERT INTO `project_updates` (`id`, `project_id`, `update_message`, `created_at`) VALUES
(1, 1, 'Completed the Initial Stage\r\nIn progress with the designing stage', '2026-04-13 10:06:17'),
(2, 2, 'Completed the initial UI Mockups', '2026-04-13 14:11:57');

-- --------------------------------------------------------

--
-- Table structure for table `service_tracking`
--

CREATE TABLE `service_tracking` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `domain_status` varchar(50) DEFAULT 'pending',
  `domain_start_date` date DEFAULT NULL,
  `domain_expiry_date` date DEFAULT NULL,
  `hosting_status` varchar(50) DEFAULT 'pending',
  `hosting_start_date` date DEFAULT NULL,
  `hosting_expiry_date` date DEFAULT NULL,
  `seo_status` varchar(50) DEFAULT 'not_started',
  `seo_last_payment_date` date DEFAULT NULL,
  `seo_next_due_date` date DEFAULT NULL,
  `maintenance_status` varchar(50) DEFAULT 'inactive',
  `maintenance_last_payment_date` date DEFAULT NULL,
  `maintenance_next_due_date` date DEFAULT NULL,
  `last_checked` timestamp NULL DEFAULT NULL,
  `notes` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `service_tracking`
--

INSERT INTO `service_tracking` (`id`, `client_id`, `domain_status`, `domain_start_date`, `domain_expiry_date`, `hosting_status`, `hosting_start_date`, `hosting_expiry_date`, `seo_status`, `seo_last_payment_date`, `seo_next_due_date`, `maintenance_status`, `maintenance_last_payment_date`, `maintenance_next_due_date`, `last_checked`, `notes`, `updated_at`) VALUES
(1, 2, 'active', '2026-04-13', '2027-04-13', 'active', '2026-04-13', '2027-04-13', 'active', '2026-04-13', '2026-05-13', 'active', '2026-04-13', '2026-05-13', NULL, NULL, '2026-04-13 14:06:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `project_updates`
--
ALTER TABLE `project_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `service_tracking`
--
ALTER TABLE `service_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `payment_logs`
--
ALTER TABLE `payment_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `project_updates`
--
ALTER TABLE `project_updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `service_tracking`
--
ALTER TABLE `service_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD CONSTRAINT `payment_logs_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_updates`
--
ALTER TABLE `project_updates`
  ADD CONSTRAINT `project_updates_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_tracking`
--
ALTER TABLE `service_tracking`
  ADD CONSTRAINT `service_tracking_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
