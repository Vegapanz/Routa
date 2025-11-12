-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 12, 2025 at 05:54 PM
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
-- Database: `routa_db`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `active_rides`
-- (See below for the actual view)
--
CREATE TABLE `active_rides` (
`id` int(11)
,`user_id` int(11)
,`driver_id` int(11)
,`pickup_location` varchar(255)
,`destination` varchar(255)
,`pickup_lat` decimal(10,7)
,`pickup_lng` decimal(10,7)
,`dropoff_lat` decimal(10,7)
,`dropoff_lng` decimal(10,7)
,`fare` decimal(10,2)
,`status` enum('pending','searching','driver_found','confirmed','arrived','in_progress','completed','cancelled','rejected')
,`payment_method` varchar(50)
,`distance` varchar(50)
,`created_at` timestamp
,`updated_at` timestamp
,`user_name` varchar(100)
,`user_phone` varchar(25)
,`user_email` varchar(100)
,`driver_name` varchar(100)
,`driver_phone` varchar(25)
,`plate_number` varchar(50)
,`driver_lat` decimal(10,7)
,`driver_lng` decimal(10,7)
,`driver_rating` decimal(3,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'superadmin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Admin accounts';

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@routa.com', '$2y$10$VJMbwgICeaZvpmq2DL6C3OQiwLtRWqHKBlmJKb5gA.MR1hvaKSSBS', 'superadmin', '2025-11-12 15:21:40', '2025-11-12 15:27:17');

-- --------------------------------------------------------

--
-- Table structure for table `driver_earnings`
--

CREATE TABLE `driver_earnings` (
  `id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `ride_id` int(11) NOT NULL,
  `gross_fare` decimal(10,2) NOT NULL,
  `platform_commission` decimal(10,2) DEFAULT 0.00,
  `net_earnings` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `payout_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Driver earnings and commission tracking';

--
-- Dumping data for table `driver_earnings`
--

INSERT INTO `driver_earnings` (`id`, `driver_id`, `ride_id`, `gross_fare`, `platform_commission`, `net_earnings`, `payment_status`, `payout_date`, `created_at`) VALUES
(1, 1, 14, 70.25, 14.05, 56.20, 'pending', NULL, '2025-11-12 16:10:30'),
(2, 1, 15, 70.55, 14.11, 56.44, 'pending', NULL, '2025-11-12 16:25:43'),
(3, 1, 16, 644.50, 128.90, 515.60, 'pending', NULL, '2025-11-12 16:32:04'),
(4, 1, 17, 99.40, 19.88, 79.52, 'pending', NULL, '2025-11-12 16:36:18');

-- --------------------------------------------------------

--
-- Table structure for table `driver_locations`
--

CREATE TABLE `driver_locations` (
  `id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `heading` decimal(5,2) DEFAULT NULL,
  `speed` decimal(5,2) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Real-time driver GPS locations';

--
-- Dumping data for table `driver_locations`
--

INSERT INTO `driver_locations` (`id`, `driver_id`, `latitude`, `longitude`, `heading`, `speed`, `updated_at`) VALUES
(1, 4, 14.5933000, 120.9771000, 0.00, 0.00, '2025-11-12 15:21:41'),
(2, 1, 14.5995000, 120.9842000, 0.00, 0.00, '2025-11-12 15:21:41'),
(3, 2, 14.6042000, 120.9822000, 0.00, 0.00, '2025-11-12 15:21:41'),
(4, 3, 14.5896000, 120.9812000, 0.00, 0.00, '2025-11-12 15:21:41'),
(5, 5, 14.6091000, 120.9895000, 0.00, 0.00, '2025-11-12 15:21:41');

-- --------------------------------------------------------

--
-- Table structure for table `fare_settings`
--

CREATE TABLE `fare_settings` (
  `id` int(11) NOT NULL,
  `base_fare` decimal(10,2) DEFAULT 50.00,
  `per_km_rate` decimal(10,2) DEFAULT 15.00,
  `per_minute_rate` decimal(10,2) DEFAULT 2.00,
  `minimum_fare` decimal(10,2) DEFAULT 50.00,
  `booking_fee` decimal(10,2) DEFAULT 10.00,
  `surge_multiplier` decimal(3,2) DEFAULT 1.00,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Pricing and fare configuration';

--
-- Dumping data for table `fare_settings`
--

INSERT INTO `fare_settings` (`id`, `base_fare`, `per_km_rate`, `per_minute_rate`, `minimum_fare`, `booking_fee`, `surge_multiplier`, `active`, `created_at`, `updated_at`) VALUES
(1, 50.00, 15.00, 2.00, 50.00, 10.00, 1.00, 1, '2025-11-12 15:21:40', '2025-11-12 15:21:40');

-- --------------------------------------------------------

--
-- Table structure for table `otp_verifications`
--

CREATE TABLE `otp_verifications` (
  `id` int(11) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='OTP codes for phone verification';

--
-- Dumping data for table `otp_verifications`
--

INSERT INTO `otp_verifications` (`id`, `phone`, `otp_code`, `is_verified`, `expires_at`, `created_at`) VALUES
(1, '+639123456789', '688580', 1, '2025-11-12 23:35:44', '2025-11-12 15:30:44');

-- --------------------------------------------------------

--
-- Table structure for table `ride_history`
--

CREATE TABLE `ride_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `pickup_lat` decimal(10,7) DEFAULT NULL,
  `pickup_lng` decimal(10,7) DEFAULT NULL,
  `dropoff_lat` decimal(10,7) DEFAULT NULL,
  `dropoff_lng` decimal(10,7) DEFAULT NULL,
  `fare` decimal(10,2) NOT NULL,
  `distance` varchar(50) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'cash',
  `estimated_duration` varchar(50) DEFAULT NULL,
  `status` enum('pending','searching','driver_found','confirmed','arrived','in_progress','completed','cancelled','rejected') NOT NULL DEFAULT 'pending',
  `user_rating` int(11) DEFAULT NULL CHECK (`user_rating` between 1 and 5),
  `user_review` text DEFAULT NULL,
  `driver_rating` int(11) DEFAULT NULL CHECK (`driver_rating` between 1 and 5),
  `driver_review` text DEFAULT NULL,
  `driver_arrival_time` timestamp NULL DEFAULT NULL,
  `trip_start_time` timestamp NULL DEFAULT NULL,
  `trip_end_time` timestamp NULL DEFAULT NULL,
  `cancelled_by` enum('user','driver','admin','system') DEFAULT NULL,
  `cancel_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='All ride bookings and history';

--
-- Dumping data for table `ride_history`
--

INSERT INTO `ride_history` (`id`, `user_id`, `driver_id`, `driver_name`, `pickup_location`, `destination`, `pickup_lat`, `pickup_lng`, `dropoff_lat`, `dropoff_lng`, `fare`, `distance`, `payment_method`, `estimated_duration`, `status`, `user_rating`, `user_review`, `driver_rating`, `driver_review`, `driver_arrival_time`, `trip_start_time`, `trip_end_time`, `cancelled_by`, `cancel_reason`, `created_at`, `completed_at`, `updated_at`) VALUES
(1, 1, 1, 'Pedro Santos', 'SM City Manila', 'Divisoria', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 85.00, '2.5 km', 'cash', NULL, 'completed', 5, 'Excellent service! Very friendly driver.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-12 12:21:40', '2025-11-12 13:21:40', '2025-11-12 15:21:40'),
(2, 2, 2, 'Jose Reyes', 'Quiapo Church', 'Recto Avenue', 14.5989000, 120.9831000, 14.6026000, 120.9831000, 60.00, '1.8 km', 'cash', NULL, 'completed', 4, 'Good driver, arrived on time.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-12 09:21:40', '2025-11-12 10:21:40', '2025-11-12 15:21:40'),
(3, 3, 3, 'Antonio Cruz', 'UST Main Building', 'Espa?a Boulevard', 14.6091000, 120.9895000, 14.6052000, 120.9921000, 50.00, '1.2 km', 'cash', NULL, 'completed', 5, 'Very safe driver!', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-11 15:21:40', '2025-11-11 15:21:40', '2025-11-12 15:21:40'),
(4, 1, 4, 'Ricardo Lopez', 'Binondo Church', 'Lucky Chinatown', 14.5975000, 120.9739000, 14.5965000, 120.9785000, 55.00, '1.5 km', 'cash', NULL, 'completed', 5, 'Highly recommended!', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-10 15:21:40', '2025-11-10 15:21:40', '2025-11-12 15:21:40'),
(5, 4, 1, 'Pedro Santos', 'Intramuros', 'Rizal Park', 14.5897000, 120.9752000, 14.5833000, 120.9778000, 50.00, '1.0 km', 'cash', NULL, 'completed', 4, 'Nice ride.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-09 15:21:40', '2025-11-09 15:21:40', '2025-11-12 15:21:40'),
(6, 2, 1, 'Pedro Santos', 'Manila City Hall', 'San Miguel Church', 14.5919000, 120.9799000, 14.5901000, 120.9734000, 65.00, '1.8 km', 'cash', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-12 15:21:40', NULL, '2025-11-12 15:29:43'),
(7, 5, NULL, NULL, 'LRT Carriedo Station', 'Divisoria Mall', 14.5991000, 120.9815000, 14.6045000, 120.9801000, 70.00, '2.0 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-12 15:21:40', NULL, '2025-11-12 15:39:45'),
(8, 1, 2, NULL, 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6035149, 120.9835619, 50.00, '0.00 km', 'cash', '0 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-12 15:28:37', NULL, '2025-11-12 15:48:46'),
(9, 6, 4, NULL, 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'Lyceum of the Philippines University, Real Street, Manila, Fifth District, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.5915681, 120.9778248, 79.90, '1.46 km', 'cash', '4 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-12 15:31:36', NULL, '2025-11-12 15:32:47'),
(10, 1, NULL, NULL, 'Quiapo Church', 'Divisoria Market', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 55.00, '2.5 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-12 15:40:24', NULL, '2025-11-12 15:42:10'),
(11, 6, 4, 'Ricardo Lopez', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'Manila, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.5904492, 120.9803621, 80.35, '1.49 km', 'cash', '4 mins', '', NULL, NULL, NULL, NULL, '2025-11-12 15:43:06', NULL, NULL, NULL, NULL, '2025-11-12 15:42:35', NULL, '2025-11-12 15:43:11'),
(12, 2, NULL, NULL, 'SM Manila', 'Divisoria', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 60.00, '3.0 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-12 15:47:13', NULL, '2025-11-12 16:09:22'),
(13, 1, 2, 'Jose Reyes', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6035149, 120.9835619, 50.00, '0.00 km', 'cash', '0 mins', 'confirmed', NULL, NULL, NULL, NULL, '2025-11-12 16:02:22', NULL, NULL, NULL, NULL, '2025-11-12 16:00:37', NULL, '2025-11-12 16:02:22'),
(14, 6, 1, 'Pedro Santos', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'University of Santo Tomas, España Boulevard, Manila, Sampaloc, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6098426, 120.9894657, 70.25, '0.95 km', 'cash', '3 mins', 'completed', 4, '', NULL, NULL, '2025-11-12 16:10:06', '2025-11-12 16:10:17', '2025-11-12 16:10:30', NULL, NULL, '2025-11-12 16:08:14', '2025-11-12 16:10:30', '2025-11-12 16:10:52'),
(15, 6, 1, 'Pedro Santos', 'Youniversity Suites, Manila, Sampaloc, Metro Manila, Philippines', 'University of Santo Tomas, España Boulevard, Manila, Sampaloc, Metro Manila, Philippines', 14.6011298, 120.9902032, 14.6098426, 120.9894657, 70.55, '0.97 km', 'cash', '3 mins', 'completed', NULL, NULL, NULL, NULL, '2025-11-12 16:25:31', '2025-11-12 16:25:37', '2025-11-12 16:25:43', NULL, NULL, '2025-11-12 16:20:39', '2025-11-12 16:25:43', '2025-11-12 16:25:43'),
(16, 6, 1, 'Pedro Santos', 'Adamson University, D. Romualdez Sr. Street, Manila, Paco, Metro Manila, Philippines', 'Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 14.5862636, 120.9863598, 14.3340224, 120.9512622, 644.50, '28.30 km', 'cash', '85 mins', 'completed', 4, '', NULL, NULL, '2025-11-12 16:31:50', '2025-11-12 16:31:57', '2025-11-12 16:32:03', NULL, NULL, '2025-11-12 16:31:01', '2025-11-12 16:32:03', '2025-11-12 16:32:51'),
(17, 6, 1, 'Pedro Santos', 'Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 'Santa Cristina 1, Dasmariñas, DBB-C, Cavite, Philippines', 14.3340224, 120.9512622, 14.3229859, 120.9699719, 99.40, '2.36 km', 'cash', '7 mins', 'completed', 3, 'ambobo mo mag drive', NULL, NULL, '2025-11-12 16:36:01', '2025-11-12 16:36:09', '2025-11-12 16:36:18', NULL, NULL, '2025-11-12 16:35:21', '2025-11-12 16:36:18', '2025-11-12 16:36:53');

-- --------------------------------------------------------

--
-- Table structure for table `ride_notifications`
--

CREATE TABLE `ride_notifications` (
  `id` int(11) NOT NULL,
  `ride_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `recipient_type` enum('user','driver','admin') NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Push notifications for rides';

--
-- Dumping data for table `ride_notifications`
--

INSERT INTO `ride_notifications` (`id`, `ride_id`, `recipient_id`, `recipient_type`, `notification_type`, `message`, `is_read`, `created_at`) VALUES
(1, 8, 2, 'driver', 'new_ride', 'New ride request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 0, '2025-11-12 15:28:37'),
(2, 8, 1, 'user', 'driver_assigned', 'Driver Jose Reyes has been assigned to your ride', 0, '2025-11-12 15:28:38'),
(3, 9, 4, 'driver', 'new_ride', 'New ride request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Lyceum of the Philippines University, Real Street, Manila, Fifth District, Metro Manila, Philippines', 0, '2025-11-12 15:31:36'),
(4, 9, 6, 'user', 'driver_assigned', 'Driver Ricardo Lopez has been assigned to your ride', 0, '2025-11-12 15:31:36'),
(5, 9, 4, 'driver', 'ride_cancelled', 'Ride cancelled by passenger', 0, '2025-11-12 15:32:48'),
(6, 11, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Manila, Metro Manila, Philippines', 0, '2025-11-12 15:42:36'),
(7, 11, 4, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Manila, Metro Manila, Philippines', 0, '2025-11-12 15:42:52'),
(8, 11, 6, 'user', 'driver_assigned', 'Driver Ricardo Lopez has been assigned. Waiting for driver confirmation...', 0, '2025-11-12 15:42:52'),
(9, 11, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-12 15:43:06'),
(10, 8, 2, 'driver', 'ride_cancelled', 'Ride cancelled by passenger', 0, '2025-11-12 15:48:47'),
(11, 13, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 0, '2025-11-12 16:00:37'),
(12, 13, 2, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 0, '2025-11-12 16:01:06'),
(13, 13, 1, 'user', 'driver_assigned', 'Driver Jose Reyes has been assigned. Waiting for driver confirmation...', 0, '2025-11-12 16:01:06'),
(14, 13, 1, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-12 16:02:22'),
(15, 14, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to University of Santo Tomas, España Boulevard, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-12 16:08:15'),
(16, 14, 1, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to University of Santo Tomas, España Boulevard, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-12 16:09:16'),
(17, 14, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-12 16:09:16'),
(18, 14, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-12 16:10:06'),
(19, 14, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-12 16:10:17'),
(20, 14, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-12 16:10:30'),
(21, 14, 1, 'driver', 'rating_received', 'You received a 4-star rating from a passenger!', 0, '2025-11-12 16:10:53'),
(22, 15, 1, 'admin', 'new_booking', 'New booking request from Youniversity Suites, Manila, Sampaloc, Metro Manila, Philippines to University of Santo Tomas, España Boulevard, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-12 16:20:39'),
(23, 15, 1, 'driver', 'new_ride', 'New ride assigned: Youniversity Suites, Manila, Sampaloc, Metro Manila, Philippines to University of Santo Tomas, España Boulevard, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-12 16:21:02'),
(24, 15, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-12 16:21:02'),
(25, 15, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-12 16:25:32'),
(26, 15, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-12 16:25:38'),
(27, 15, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-12 16:25:43'),
(28, 16, 1, 'admin', 'new_booking', 'New booking request from Adamson University, D. Romualdez Sr. Street, Manila, Paco, Metro Manila, Philippines to Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 0, '2025-11-12 16:31:01'),
(29, 16, 1, 'driver', 'new_ride', 'New ride assigned: Adamson University, D. Romualdez Sr. Street, Manila, Paco, Metro Manila, Philippines to Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 0, '2025-11-12 16:31:16'),
(30, 16, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-12 16:31:16'),
(31, 16, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-12 16:31:50'),
(32, 16, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-12 16:31:57'),
(33, 16, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-12 16:32:04'),
(34, 16, 1, 'driver', 'rating_received', 'You received a 4-star rating from a passenger!', 0, '2025-11-12 16:32:51'),
(35, 17, 1, 'admin', 'new_booking', 'New booking request from Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines to Santa Cristina 1, Dasmariñas, DBB-C, Cavite, Philippines', 0, '2025-11-12 16:35:21'),
(36, 17, 1, 'driver', 'new_ride', 'New ride assigned: Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines to Santa Cristina 1, Dasmariñas, DBB-C, Cavite, Philippines', 0, '2025-11-12 16:35:40'),
(37, 17, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-12 16:35:40'),
(38, 17, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-12 16:36:01'),
(39, 17, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-12 16:36:09'),
(40, 17, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-12 16:36:18'),
(41, 17, 1, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-12 16:36:53');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Active user sessions';

-- --------------------------------------------------------

--
-- Table structure for table `tricycle_drivers`
--

CREATE TABLE `tricycle_drivers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `plate_number` varchar(50) NOT NULL,
  `tricycle_model` varchar(100) DEFAULT NULL,
  `license_number` varchar(100) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 5.00,
  `average_rating` decimal(3,2) DEFAULT 5.00,
  `total_ratings` int(11) DEFAULT 0,
  `current_lat` decimal(10,7) DEFAULT NULL,
  `current_lng` decimal(10,7) DEFAULT NULL,
  `last_location_update` timestamp NULL DEFAULT NULL,
  `status` enum('available','on_trip','offline') DEFAULT 'offline',
  `total_trips_completed` int(11) DEFAULT 0,
  `total_earnings` decimal(10,2) DEFAULT 0.00,
  `acceptance_rate` decimal(5,2) DEFAULT 100.00,
  `cancellation_rate` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tricycle driver accounts';

--
-- Dumping data for table `tricycle_drivers`
--

INSERT INTO `tricycle_drivers` (`id`, `name`, `email`, `password`, `phone`, `plate_number`, `tricycle_model`, `license_number`, `is_verified`, `rating`, `average_rating`, `total_ratings`, `current_lat`, `current_lng`, `last_location_update`, `status`, `total_trips_completed`, `total_earnings`, `acceptance_rate`, `cancellation_rate`, `created_at`, `updated_at`) VALUES
(1, 'Pedro Santos', 'pedro@driver.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', '+63 917 111 2222', 'TRY-123', 'Honda TMX', 'LIC-001', 1, 4.00, 4.00, 5, 14.5995000, 120.9842000, NULL, 'available', 4, 707.76, 100.00, 0.00, '2025-11-12 15:21:39', '2025-11-12 16:36:53'),
(2, 'Jose Reyes', 'jose@driver.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', '+63 917 222 3333', 'TRY-456', 'Kawasaki', 'LIC-002', 1, 4.90, 4.90, 0, 14.6042000, 120.9822000, NULL, 'on_trip', 0, 0.00, 100.00, 0.00, '2025-11-12 15:21:39', '2025-11-12 16:02:22'),
(3, 'Antonio Cruz', 'antonio@driver.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', '+63 917 333 4444', 'TRY-789', 'Yamaha', 'LIC-003', 1, 4.70, 4.70, 0, 14.5896000, 120.9812000, NULL, 'offline', 0, 0.00, 100.00, 0.00, '2025-11-12 15:21:39', '2025-11-12 15:27:17'),
(4, 'Ricardo Lopez', 'ricardo@driver.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', '+63 917 444 5555', 'TRY-321', 'Honda', 'LIC-004', 1, 5.00, 5.00, 0, 14.5933000, 120.9771000, NULL, 'on_trip', 0, 0.00, 100.00, 0.00, '2025-11-12 15:21:39', '2025-11-12 15:43:06'),
(5, 'Ramon Silva', 'ramon@driver.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', '+63 917 555 6666', 'TRY-654', 'Kawasaki', 'LIC-005', 1, 4.85, 4.85, 0, 14.6091000, 120.9895000, NULL, 'offline', 0, 0.00, 100.00, 0.00, '2025-11-12 15:21:39', '2025-11-12 15:27:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `phone_verified` tinyint(1) DEFAULT 0,
  `google_id` varchar(255) DEFAULT NULL,
  `facebook_id` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Passenger/Customer accounts';

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `phone_verified`, `google_id`, `facebook_id`, `email_verified`, `created_at`, `updated_at`) VALUES
(1, 'Juan Dela Cruz', 'juan@email.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', '+63 912 345 6789', 1, NULL, NULL, 1, '2025-11-12 15:21:39', '2025-11-12 15:27:17'),
(2, 'Maria Garcia', 'maria@email.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', '+63 923 456 7890', 1, NULL, NULL, 1, '2025-11-12 15:21:39', '2025-11-12 15:27:17'),
(3, 'Carlos Mendoza', 'carlos@email.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', '+63 934 567 8901', 1, NULL, NULL, 1, '2025-11-12 15:21:39', '2025-11-12 15:27:17'),
(4, 'Anna Bautista', 'anna@email.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', '+63 945 678 9012', 1, NULL, NULL, 1, '2025-11-12 15:21:39', '2025-11-12 15:27:17'),
(5, 'Miguel Torres', 'miguel@email.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', '+63 956 789 0123', 1, NULL, NULL, 1, '2025-11-12 15:21:39', '2025-11-12 15:27:17'),
(6, 'Patrick', 'patrick@email.com', '$2y$10$Pu56TIDhYUU3wE0YpHUvEOwVpQxGW80r5WGidISHf5xuDUbMCZ6he', '+639123456789', 1, NULL, NULL, 0, '2025-11-12 15:31:03', '2025-11-12 15:31:03'),
(7, 'Arcane Legends', 'newaccarcanelegends@gmail.com', '$2y$10$pku9eRgtxE92hA8v.zHW5OctRL3Hyb8ONIYKfdWxB4pzZYED3OxHe', NULL, 0, '111873639730778643903', NULL, 1, '2025-11-12 15:36:04', '2025-11-12 15:36:04');

-- --------------------------------------------------------

--
-- Structure for view `active_rides`
--
DROP TABLE IF EXISTS `active_rides`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_rides`  AS SELECT `r`.`id` AS `id`, `r`.`user_id` AS `user_id`, `r`.`driver_id` AS `driver_id`, `r`.`pickup_location` AS `pickup_location`, `r`.`destination` AS `destination`, `r`.`pickup_lat` AS `pickup_lat`, `r`.`pickup_lng` AS `pickup_lng`, `r`.`dropoff_lat` AS `dropoff_lat`, `r`.`dropoff_lng` AS `dropoff_lng`, `r`.`fare` AS `fare`, `r`.`status` AS `status`, `r`.`payment_method` AS `payment_method`, `r`.`distance` AS `distance`, `r`.`created_at` AS `created_at`, `r`.`updated_at` AS `updated_at`, `u`.`name` AS `user_name`, `u`.`phone` AS `user_phone`, `u`.`email` AS `user_email`, `d`.`name` AS `driver_name`, `d`.`phone` AS `driver_phone`, `d`.`plate_number` AS `plate_number`, `d`.`current_lat` AS `driver_lat`, `d`.`current_lng` AS `driver_lng`, `d`.`rating` AS `driver_rating` FROM ((`ride_history` `r` left join `users` `u` on(`r`.`user_id` = `u`.`id`)) left join `tricycle_drivers` `d` on(`r`.`driver_id` = `d`.`id`)) WHERE `r`.`status` in ('pending','searching','driver_found','confirmed','arrived','in_progress') ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `driver_earnings`
--
ALTER TABLE `driver_earnings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_driver_id` (`driver_id`),
  ADD KEY `idx_ride_id` (`ride_id`),
  ADD KEY `idx_driver_status` (`driver_id`,`payment_status`),
  ADD KEY `idx_payout_date` (`payout_date`);

--
-- Indexes for table `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_driver_id` (`driver_id`),
  ADD KEY `idx_driver_updated` (`driver_id`,`updated_at`);

--
-- Indexes for table `fare_settings`
--
ALTER TABLE `fare_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_otp_code` (`otp_code`),
  ADD KEY `idx_phone_verified` (`phone`,`is_verified`);

--
-- Indexes for table `ride_history`
--
ALTER TABLE `ride_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_driver_id` (`driver_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_status_driver` (`status`,`driver_id`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_user_rating` (`user_rating`),
  ADD KEY `idx_driver_rating` (`driver_rating`),
  ADD KEY `idx_completed_status` (`status`,`completed_at`);

--
-- Indexes for table `ride_notifications`
--
ALTER TABLE `ride_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ride_id` (`ride_id`),
  ADD KEY `idx_recipient` (`recipient_id`,`recipient_type`,`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_user_expires` (`user_id`,`expires_at`);

--
-- Indexes for table `tricycle_drivers`
--
ALTER TABLE `tricycle_drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `plate_number` (`plate_number`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_status_location` (`status`,`current_lat`,`current_lng`),
  ADD KEY `idx_rating` (`average_rating`),
  ADD KEY `idx_plate` (`plate_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD UNIQUE KEY `facebook_id` (`facebook_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_google_id` (`google_id`),
  ADD KEY `idx_facebook_id` (`facebook_id`),
  ADD KEY `idx_phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `driver_earnings`
--
ALTER TABLE `driver_earnings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `driver_locations`
--
ALTER TABLE `driver_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `fare_settings`
--
ALTER TABLE `fare_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ride_history`
--
ALTER TABLE `ride_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `ride_notifications`
--
ALTER TABLE `ride_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tricycle_drivers`
--
ALTER TABLE `tricycle_drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `driver_earnings`
--
ALTER TABLE `driver_earnings`
  ADD CONSTRAINT `driver_earnings_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `tricycle_drivers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `driver_earnings_ibfk_2` FOREIGN KEY (`ride_id`) REFERENCES `ride_history` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD CONSTRAINT `driver_locations_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `tricycle_drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ride_history`
--
ALTER TABLE `ride_history`
  ADD CONSTRAINT `ride_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ride_history_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `tricycle_drivers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ride_notifications`
--
ALTER TABLE `ride_notifications`
  ADD CONSTRAINT `ride_notifications_ibfk_1` FOREIGN KEY (`ride_id`) REFERENCES `ride_history` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
