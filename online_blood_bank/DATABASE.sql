-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2026 at 10:43 AM
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
-- Database: `online_blood_bank`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_notifications`
--

INSERT INTO `admin_notifications` (`id`, `message`, `created_at`) VALUES
(1, 'New donation booking by jithesh on 2026-06-12 at 09:00 AM - 11:00 AM', '2026-04-20 07:56:27'),
(2, 'New donation booking by jithesh on 2026-10-12 at 09:00 AM - 11:00 AM', '2026-04-20 08:05:55'),
(3, 'New donation booking by dhyan on 2026-10-12 at 09:00 AM - 11:00 AM', '2026-04-20 08:31:45');

-- --------------------------------------------------------

--
-- Table structure for table `blood_availability`
--

CREATE TABLE `blood_availability` (
  `id` int(11) NOT NULL,
  `blood_type` varchar(5) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_availability`
--

INSERT INTO `blood_availability` (`id`, `blood_type`, `quantity`) VALUES
(1, 'A+', 100),
(2, 'A-', 27),
(3, 'B+', 20),
(4, 'B-', 20),
(5, 'AB-', 20),
(6, 'AB+', 20),
(7, 'O+', 20),
(8, 'O-', 20);

-- --------------------------------------------------------

--
-- Table structure for table `blood_inventory`
--

CREATE TABLE `blood_inventory` (
  `id` int(11) NOT NULL,
  `blood_group` varchar(5) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `expiry_date` date NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_inventory`
--

INSERT INTO `blood_inventory` (`id`, `blood_group`, `quantity`, `expiry_date`, `added_at`) VALUES
(1, 'A+', 20, '2026-05-23', '2026-04-18 14:40:34'),
(2, 'A+', 40, '2026-05-23', '2026-04-18 15:10:15'),
(3, 'A-', 20, '2026-05-23', '2026-04-18 15:10:30');

-- --------------------------------------------------------

--
-- Table structure for table `blood_requests`
--

CREATE TABLE `blood_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `blood_group` varchar(5) NOT NULL,
  `units_required` int(11) NOT NULL,
  `hospital_name` varchar(150) NOT NULL,
  `city` varchar(100) NOT NULL,
  `urgency` enum('Normal','Urgent','Emergency') NOT NULL DEFAULT 'Normal',
  `status` enum('Pending','Approved','Completed','Rejected') NOT NULL DEFAULT 'Pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_requests`
--

INSERT INTO `blood_requests` (`id`, `user_id`, `patient_name`, `blood_group`, `units_required`, `hospital_name`, `city`, `urgency`, `status`, `request_date`) VALUES
(28, 15, 'dhyan', 'AB+', 1, 'aj hospital mangluru', 'manglore', 'Emergency', 'Approved', '2026-04-20 08:35:17');

-- --------------------------------------------------------

--
-- Table structure for table `blood_stock`
--

CREATE TABLE `blood_stock` (
  `id` int(11) NOT NULL,
  `blood_group` varchar(5) NOT NULL,
  `units` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_stock`
--

INSERT INTO `blood_stock` (`id`, `blood_group`, `units`) VALUES
(1, 'A+', 50),
(2, 'A-', 10),
(3, 'B+', 40),
(4, 'B-', 5),
(5, 'AB+', 20),
(6, 'AB-', 12),
(7, 'O+', 60),
(8, 'O-', 8);

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `donation_date` date NOT NULL,
  `time_slot` varchar(50) NOT NULL,
  `health_status` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Completed') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `hospital` varchar(150) DEFAULT NULL,
  `is_seen` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `user_id`, `donation_date`, `time_slot`, `health_status`, `status`, `created_at`, `hospital`, `is_seen`) VALUES
(15, 13, '2026-06-12', '09:00 AM - 11:00 AM', NULL, 'Rejected', '2026-04-20 07:43:38', NULL, 0),
(16, 13, '2026-06-12', '09:00 AM - 11:00 AM', NULL, 'Rejected', '2026-04-20 07:56:27', NULL, 0),
(17, 13, '2026-10-12', '09:00 AM - 11:00 AM', NULL, 'Rejected', '2026-04-20 08:05:55', NULL, 0),
(18, 14, '2026-10-12', '09:00 AM - 11:00 AM', NULL, 'Approved', '2026-04-20 08:31:45', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobilenumber` varchar(15) NOT NULL,
  `message` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `name`, `email`, `mobilenumber`, `message`) VALUES
(1, 8, 'jithess', 'jitheshdevadiga6362@gmail.com', '6362337985', 'super'),
(2, 11, 'jithess', 'jitheshdevadiga6362@gmail.com', '6362337985', 'gggggg'),
(3, NULL, 'jithess', 'jitheshdevadiga6362@gmail.com', '6362337985', 'ygyuggfy');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(19, 13, 'Your donation appointment has been booked successfully.', 1, '2026-04-20 07:43:38'),
(21, 13, 'Your donation appointment has been booked successfully.', 1, '2026-04-20 07:56:27'),
(22, 13, 'Your request for 2 units of O+ blood has been Approved', 1, '2026-04-20 08:04:35'),
(23, 13, 'Your donation appointment has been booked successfully.', 1, '2026-04-20 08:05:55'),
(24, 14, 'Your donation appointment has been booked successfully.', 1, '2026-04-20 08:31:45'),
(25, 15, 'Your request for 1 units of AB+ blood has been Approved', 1, '2026-04-20 08:36:07');

-- --------------------------------------------------------

--
-- Table structure for table `register`
--

CREATE TABLE `register` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `bloodgroup` varchar(5) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `unit` int(11) NOT NULL DEFAULT 0,
  `role` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `register`
--

INSERT INTO `register` (`id`, `fullname`, `email`, `username`, `password`, `gender`, `bloodgroup`, `contact`, `unit`, `role`) VALUES
(1, 'System Administrator', 'admin@bloodbank.com', 'admin', 'admin123', 'Other', 'O+', '0000000000', 0, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `request`
--

CREATE TABLE `request` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `blood_group` varchar(5) NOT NULL,
  `unit` int(11) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `hospital_name` varchar(150) NOT NULL,
  `city` varchar(100) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `urgency` varchar(50) NOT NULL DEFAULT 'Normal',
  `status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request`
--

INSERT INTO `request` (`id`, `name`, `blood_group`, `unit`, `contact`, `hospital_name`, `city`, `gender`, `urgency`, `status`) VALUES
(1, 'Test User', 'A+', 2, '1234567890', 'City Hospital', 'YourCity', 'Male', 'Emergency', 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `blood_group` varchar(5) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `city` varchar(100) NOT NULL,
  `role` enum('Donor','Recipient') NOT NULL,
  `last_donation_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `username`, `password`, `gender`, `blood_group`, `contact`, `city`, `role`, `last_donation_date`, `created_at`) VALUES
(13, 'jithesh', 'jitheshdevadiga6362@gmail.com', 'jithesh', '$2y$10$nKiY9C6XD3Ef1ustcuxfX.58.tbSF5oRw/0jlfDAFqX9nxs0gBd8K', 'Male', 'O+', '6362337985', 'jokatte', 'Donor', '2026-10-12', '2026-04-20 07:40:11'),
(14, 'dhyan', 'dhyanganiga@6362gmail.com', 'admin', '$2y$10$fBW7ha3pay4OCkUcp8J5e.tUJOH/cqcG3I/sAaA493mgUFY82BZ9C', 'Male', 'A-', '2266887799', 'manglore', 'Donor', '2026-10-12', '2026-04-20 08:28:25'),
(15, 'anikleth', 'arun@gmail.com', 'aniketh', '$2y$10$8UUwQsFGBNBW/u0x.riVe.Ck6iufhfT/ik1Zyw07RSfYvGgGPinve', 'Male', 'A-', '7788996633', 'udupi', 'Recipient', NULL, '2026-04-20 08:29:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blood_availability`
--
ALTER TABLE `blood_availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blood_type` (`blood_type`);

--
-- Indexes for table `blood_inventory`
--
ALTER TABLE `blood_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `blood_stock`
--
ALTER TABLE `blood_stock`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `register`
--
ALTER TABLE `register`
  ADD PRIMARY KEY (`id`,`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `blood_availability`
--
ALTER TABLE `blood_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `blood_inventory`
--
ALTER TABLE `blood_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `blood_requests`
--
ALTER TABLE `blood_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `blood_stock`
--
ALTER TABLE `blood_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `register`
--
ALTER TABLE `register`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `request`
--
ALTER TABLE `request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD CONSTRAINT `blood_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
