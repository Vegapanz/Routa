-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2025 at 03:01 PM
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

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `cleanup_ws_data` ()   BEGIN
    
    DELETE FROM ws_connections 
    WHERE last_activity < DATE_SUB(NOW(), INTERVAL 1 HOUR);
    
    
    DELETE FROM ws_message_queue 
    WHERE is_delivered = 1 AND delivered_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
    
    
    DELETE FROM ws_message_queue 
    WHERE is_delivered = 0 AND attempts > 10 AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);
    
    
    UPDATE users 
    SET ws_token = NULL, ws_token_expires = NULL 
    WHERE ws_token_expires IS NOT NULL AND ws_token_expires < NOW();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `generate_ws_token` (IN `p_user_id` INT, OUT `p_token` VARCHAR(255))   BEGIN
    DECLARE v_token VARCHAR(255);
    
    
    SET v_token = SHA2(CONCAT(p_user_id, UNIX_TIMESTAMP(), RAND()), 256);
    
    
    UPDATE users 
    SET ws_token = v_token, 
        ws_token_expires = DATE_ADD(NOW(), INTERVAL 7 DAY)
    WHERE id = p_user_id;
    
    SET p_token = v_token;
END$$

DELIMITER ;

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
-- Table structure for table `driver_applications`
--

CREATE TABLE `driver_applications` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `zip_code` varchar(10) NOT NULL,
  `license_number` varchar(50) NOT NULL,
  `license_expiry` date NOT NULL,
  `driving_experience` varchar(20) NOT NULL,
  `emergency_name` varchar(100) NOT NULL,
  `emergency_phone` varchar(20) NOT NULL,
  `relationship` varchar(50) NOT NULL,
  `previous_experience` text DEFAULT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `franchise_number` varchar(50) NOT NULL,
  `vehicle_make` varchar(50) NOT NULL,
  `vehicle_model` varchar(50) NOT NULL,
  `vehicle_year` varchar(10) NOT NULL,
  `license_document` varchar(255) DEFAULT NULL,
  `government_id_document` varchar(255) DEFAULT NULL,
  `registration_document` varchar(255) DEFAULT NULL,
  `franchise_document` varchar(255) DEFAULT NULL,
  `insurance_document` varchar(255) DEFAULT NULL,
  `clearance_document` varchar(255) DEFAULT NULL,
  `photo_document` varchar(255) DEFAULT NULL,
  `status` enum('pending','under_review','approved','rejected') DEFAULT 'pending',
  `application_date` datetime DEFAULT current_timestamp(),
  `reviewed_date` datetime DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `driver_applications`
--

INSERT INTO `driver_applications` (`id`, `first_name`, `middle_name`, `last_name`, `date_of_birth`, `phone`, `email`, `password`, `address`, `barangay`, `city`, `zip_code`, `license_number`, `license_expiry`, `driving_experience`, `emergency_name`, `emergency_phone`, `relationship`, `previous_experience`, `vehicle_type`, `plate_number`, `franchise_number`, `vehicle_make`, `vehicle_model`, `vehicle_year`, `license_document`, `government_id_document`, `registration_document`, `franchise_document`, `insurance_document`, `clearance_document`, `photo_document`, `status`, `application_date`, `reviewed_date`, `reviewed_by`, `rejection_reason`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'asdasda', '', 'asdas', '2007-01-01', '09123456789', 'asada@email.com', '', 'asdasdasdasdas', 'asdas', 'asdsda', '1111', 'N01-12-345678', '2025-11-19', '2', 'asdfasdfa', '09123456789', 'parent', '', 'car', 'ASD1234', 'AS-DASD-ADASD', 'asdad', 'asdadsa', '2000', 'uploads/driver_applications/license_1763050132_69160294bb070.jpg', 'uploads/driver_applications/govid_1763050132_69160294bb4ef.jpg', 'uploads/driver_applications/registration_1763050132_69160294bb974.jpg', 'uploads/driver_applications/franchise_1763050132_69160294bbd68.jpg', 'uploads/driver_applications/insurance_1763050132_69160294bc527.jpg', 'uploads/driver_applications/clearance_1763050132_69160294bc9b1.jpg', 'uploads/driver_applications/photo_1763050132_69160294bd1e0.jpg', 'rejected', '2025-11-14 00:08:52', NULL, NULL, NULL, NULL, '2025-11-13 16:08:52', '2025-11-17 16:55:09'),
(2, 'Jade', '', 'Orense', '2003-02-06', '09123456789', 'jade@email.com', '', 'asdasasdada', 'asdas', 'asdasdasd', '1233', 'N01-12-345678', '2025-11-19', '3', 'aasdasda', '09123456789', 'sibling', '', 'van', 'ASD2134', '', 'asdasd', 'asdasda', '2000', 'uploads/driver_applications/license_1763050496_69160400ba2d4.jpg', 'uploads/driver_applications/govid_1763050496_69160400ba5b2.jpg', 'uploads/driver_applications/registration_1763050496_69160400ba8c9.jpg', 'uploads/driver_applications/franchise_1763050496_69160400baca5.jpg', 'uploads/driver_applications/insurance_1763050496_69160400bb056.jpg', 'uploads/driver_applications/clearance_1763050496_69160400bb91e.jpg', 'uploads/driver_applications/photo_1763050496_69160400bbbfc.jpg', 'rejected', '2025-11-14 00:14:56', NULL, NULL, NULL, NULL, '2025-11-13 16:14:56', '2025-11-13 17:51:14'),
(3, 'asdasda', '', 'asdasdasd', '2006-11-30', '09123456789', 'sample@email.com', '', '1231adsasdadasasdasd', 'asdasdas', 'adasdasd', '1231', 'A01-12-345678', '2025-11-19', '2', 'asdasdas', '09123485902', 'parent', '', 'motorcycle', 'ASD1234', '', 'asd', 'asda', '2000', 'uploads/driver_applications/license_1763051079_691606475cf37.jpg', 'uploads/driver_applications/govid_1763051079_691606475d429.jpg', 'uploads/driver_applications/registration_1763051079_691606475d910.jpg', 'uploads/driver_applications/franchise_1763051079_691606475dd6d.jpg', 'uploads/driver_applications/insurance_1763051079_6916064761158.jpg', 'uploads/driver_applications/clearance_1763051079_69160647616f6.jpg', 'uploads/driver_applications/photo_1763051079_6916064761bf5.jpg', 'rejected', '2025-11-14 00:24:39', NULL, NULL, NULL, NULL, '2025-11-13 16:24:39', '2025-11-17 16:55:00'),
(4, 'asd', '', 'asd', '2006-10-14', '09123123141', '123123@email.com', '', '123123141234dasd', 'asdadasd', 'asdasdasd', '1231', 'A21-30-345678', '2029-11-24', '2', 'asdasdasd', '09123456789', 'friend', '', 'tricycle', 'ASD3541', '', 'asdasda', 'asdasda', '2000', 'uploads/driver_applications/license_1763051596_6916084c79030.jpg', 'uploads/driver_applications/govid_1763051596_6916084c793f1.jpg', 'uploads/driver_applications/registration_1763051596_6916084c7978c.jpg', 'uploads/driver_applications/franchise_1763051596_6916084c79b83.jpg', 'uploads/driver_applications/insurance_1763051596_6916084c79e65.jpg', 'uploads/driver_applications/clearance_1763051596_6916084c7a111.jpg', 'uploads/driver_applications/photo_1763051596_6916084c7a367.jpg', 'rejected', '2025-11-14 00:33:16', NULL, NULL, NULL, NULL, '2025-11-13 16:33:16', '2025-11-13 17:50:55'),
(5, 'asdasd', '', 'asdada', '2006-12-27', '09231231231', 'asd@email.com', '', 'asdasdasda', 'asdasdasda', 'dasdasda', '1312', 'A21-31-412351', '2025-12-06', '2', 'adasdada', '09312312313', 'sibling', '', 'car', 'ASD1234', '', 'asdada', 'asdasdsad', '2000', NULL, NULL, NULL, NULL, 'uploads/driver_applications/insurance_1763053136_69160e50815a7.jpg', NULL, NULL, 'rejected', '2025-11-14 00:58:56', NULL, NULL, NULL, NULL, '2025-11-13 16:58:56', '2025-11-13 17:50:43'),
(6, 'Michael', '', 'Cabs', '2006-12-14', '09231231312', 'michael@email.com', '', '131231231asdasda', 'adasda', 'asdada', '1231', 'A51-23-132456', '2025-11-27', '3', 'asdasdada', '09231231313', 'sibling', '', 'motorcycle', 'SNP1552', '', 'Yamaha', 'Sniper', '2010', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'rejected', '2025-11-14 01:02:09', NULL, NULL, NULL, NULL, '2025-11-13 17:02:09', '2025-11-13 17:49:52'),
(7, 'asdad', '', 'adasda', '2006-12-26', '09321231312', 'mic@email.com', '', 'asdasdaasdasda', 'asdasdad', 'adsas', '2312', 'A23-12-312313', '2025-11-29', '2', 'asdad', '09231231231', 'other', '', 'motorcycle', 'ADS3131', '', 'asdasda', 'asda', '2025', 'uploads/driver_applications/license_1763053503_69160fbf103c2.jpg', 'uploads/driver_applications/govid_1763053503_69160fbf10745.jpg', 'uploads/driver_applications/registration_1763053503_69160fbf10aca.jpg', 'uploads/driver_applications/franchise_1763053503_69160fbf1155b.jpg', 'uploads/driver_applications/insurance_1763053503_69160fbf118b8.jpg', 'uploads/driver_applications/clearance_1763053503_69160fbf11c86.jpg', 'uploads/driver_applications/photo_1763053503_69160fbf12152.jpg', 'rejected', '2025-11-14 01:05:03', NULL, NULL, NULL, NULL, '2025-11-13 17:05:03', '2025-11-13 17:40:20'),
(8, 'asdas', '', 'asdasdads', '2006-12-28', '09231231231', 'mica@email.com', '$2y$10$ufvsbWQAJotxWb0jtSHnYONdacxpy.ldWqrWuEbpdmYKHeZv7DqAG', '1231214jkasjda', 'adasasd', 'asdadas', '1231', 'A41-31-231451', '2025-12-03', '2', 'asdasdasd', '09231231414', 'friend', '', 'tricycle', 'ASD3124', '', 'adasdasd', 'adasdada', '2025', 'uploads/driver_applications/license_1763055058_691615d248d8a.jpg', 'uploads/driver_applications/govid_1763055058_691615d2491d1.jpg', 'uploads/driver_applications/registration_1763055058_691615d2497e2.jpg', 'uploads/driver_applications/franchise_1763055058_691615d249c0e.jpg', 'uploads/driver_applications/insurance_1763055058_691615d24a077.jpg', 'uploads/driver_applications/clearance_1763055058_691615d24a559.jpg', 'uploads/driver_applications/photo_1763055058_691615d24a951.jpg', 'approved', '2025-11-14 01:30:58', NULL, NULL, NULL, NULL, '2025-11-13 17:30:58', '2025-11-13 17:37:51'),
(9, 'Jade', '', 'Orense', '2006-12-14', '09123123123', 'jadeorense@email.com', '$2y$10$jgFVkETju7amengkR39rceZG.9bEk3DJEbfAu8pTAOjCuE3OjuXoe', 'Zone 1 Bayan', 'Zone I', 'Dasmarinas', '4114', 'A91-32-131512', '2025-11-26', '2', 'Orense', '09235123154', 'sibling', '', 'tricycle', 'ASD1231', '', 'Honda', 'Click 125', '2018', 'uploads/driver_applications/license_1763110152_6916ed0867fb6.jpg', 'uploads/driver_applications/govid_1763110152_6916ed0868750.jpg', 'uploads/driver_applications/registration_1763110152_6916ed0868abc.jpg', 'uploads/driver_applications/franchise_1763110152_6916ed0868d83.jpg', 'uploads/driver_applications/insurance_1763110152_6916ed0868fd5.jpg', 'uploads/driver_applications/clearance_1763110152_6916ed086925d.jpg', 'uploads/driver_applications/photo_1763110152_6916ed0869649.jpg', 'approved', '2025-11-14 16:49:12', NULL, NULL, NULL, NULL, '2025-11-14 08:49:12', '2025-11-14 08:49:35'),
(10, 'mema', '', 'lang', '2000-01-21', '09123456789', 'jpmonroyo@kld.edu.ph', '$2y$10$7xFl578Hb4j5XZKeRRP/2ujMfioKj8Ha9cAeXoqhtO4z7ak0khGgS', 'asdadasda', 'adasdas', 'adsafasd', '2131', 'A23-12-312314', '2026-01-12', '1', 'asda', '09231312312', 'sibling', '', 'tricycle', 'ASD1231', '', 'honda', 'click', '2000', 'uploads/driver_applications/license_1763397374_691b4efe2cb2d.jpg', 'uploads/driver_applications/govid_1763397374_691b4efe2ce53.jpg', 'uploads/driver_applications/registration_1763397374_691b4efe2d14f.png', 'uploads/driver_applications/franchise_1763397374_691b4efe2d43d.png', 'uploads/driver_applications/insurance_1763397374_691b4efe2d6fb.png', 'uploads/driver_applications/clearance_1763397374_691b4efe2d98a.png', 'uploads/driver_applications/photo_1763397374_691b4efe2dc42.png', 'rejected', '2025-11-18 00:36:14', NULL, NULL, NULL, NULL, '2025-11-17 16:36:14', '2025-11-17 16:40:00'),
(11, 'lolipop', '', 'dwayne', '2000-03-12', '09231231233', '67@mailinator.com', '$2y$10$f9yQ3dvXlY9PpP6N.zXZhu.LrSo27sR1q51C2.kipCdkTY6eOpmYm', 'asdadawdasdasd', 'asdasdasd', 'asdasdasd', '3123', 'A12-31-312333', '2222-03-12', '2', 'asddsasda', '09231231231', 'child', '', 'tricycle', 'ASX1351', '', 'adasd', 'sadasdasd', '2025', 'uploads/driver_applications/license_1763400434_691b5af2457e3.png', 'uploads/driver_applications/govid_1763400434_691b5af245b50.png', 'uploads/driver_applications/registration_1763400434_691b5af245dde.png', 'uploads/driver_applications/franchise_1763400434_691b5af24608d.png', 'uploads/driver_applications/insurance_1763400434_691b5af24636b.png', 'uploads/driver_applications/clearance_1763400434_691b5af246694.png', 'uploads/driver_applications/photo_1763400434_691b5af246954.png', 'approved', '2025-11-18 01:27:14', NULL, NULL, NULL, NULL, '2025-11-17 17:27:14', '2025-11-17 17:27:54'),
(12, 'Dansel', '', 'Fox', '2000-05-13', '09345374234', 'dansel@email.com', '$2y$10$oVaDruDmuLU162eIndoR.uf4dR80TqLckHC25cviDpectX2637wYK', 'kasdlkajskldjl', 'ahdklfghaskjlf', 'aksjdfaslkdf', '4141', 'F21-31-234124', '2026-02-02', '2', 'asdasda', '09235124353', 'parent', '', 'tricycle', 'AVZ5753', '', 'Honda', 'Click', '2025', 'uploads/driver_applications/license_1763480883_691c9533303eb.png', 'uploads/driver_applications/govid_1763480883_691c953346000.png', 'uploads/driver_applications/registration_1763480883_691c953346342.png', 'uploads/driver_applications/franchise_1763480883_691c9533467e7.png', 'uploads/driver_applications/insurance_1763480883_691c953346b34.png', 'uploads/driver_applications/clearance_1763480883_691c953347315.png', 'uploads/driver_applications/photo_1763480883_691c95334788b.png', 'rejected', '2025-11-18 23:48:03', NULL, NULL, NULL, NULL, '2025-11-18 15:48:03', '2025-11-18 16:08:03'),
(13, 'Jadeski', '', 'Orense', '2000-01-05', '09345463452', 'jrorense@kld.edu.ph', '$2y$10$is3xIWOislkD5T5ajuUJb.oApELd5PyvpuyVe.7P0H7.g5.SZScEe', '1adasdagvasdasda', 'asdasdasd', 'asdadsasda', '1312', 'A51-25-681235', '2025-12-05', '2', 'adasdasdasd', '09512341512', 'friend', '', 'tricycle', 'ASB2513', '', 'Gonda', 'click', '2025', 'uploads/driver_applications/license_1763481139_691c96339408a.png', 'uploads/driver_applications/govid_1763481139_691c9633948d4.png', 'uploads/driver_applications/registration_1763481139_691c963394b81.png', 'uploads/driver_applications/franchise_1763481139_691c963395026.png', 'uploads/driver_applications/insurance_1763481139_691c9633953b1.png', 'uploads/driver_applications/clearance_1763481139_691c9633956e8.png', 'uploads/driver_applications/photo_1763481139_691c963395989.png', 'approved', '2025-11-18 23:52:19', NULL, NULL, NULL, NULL, '2025-11-18 15:52:19', '2025-11-18 15:53:24');

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
(4, 1, 17, 99.40, 19.88, 79.52, 'pending', NULL, '2025-11-12 16:36:18'),
(5, 1, 18, 64.20, 12.84, 51.36, 'pending', NULL, '2025-11-13 16:55:07'),
(6, 1, 25, 51.50, 10.30, 41.20, 'pending', NULL, '2025-11-14 03:43:14'),
(7, 1, 27, 50.00, 10.00, 40.00, 'pending', NULL, '2025-11-14 04:54:34'),
(8, 1, 30, 70.25, 14.05, 56.20, 'pending', NULL, '2025-11-14 05:35:39'),
(9, 1, 46, 70.25, 14.05, 56.20, 'pending', NULL, '2025-11-14 05:58:52'),
(10, 1, 52, 58.60, 11.72, 46.88, 'pending', NULL, '2025-11-14 06:22:35'),
(11, 1, 54, 63.00, 12.60, 50.40, 'pending', NULL, '2025-11-14 06:34:22'),
(12, 1, 55, 70.70, 14.14, 56.56, 'pending', NULL, '2025-11-14 06:40:43'),
(13, 1, 56, 63.00, 12.60, 50.40, 'pending', NULL, '2025-11-14 06:48:15'),
(14, 1, 57, 70.25, 14.05, 56.20, 'pending', NULL, '2025-11-14 06:52:01'),
(15, 1, 58, 63.00, 12.60, 50.40, 'pending', NULL, '2025-11-14 06:53:48'),
(16, 1, 59, 51.65, 10.33, 41.32, 'pending', NULL, '2025-11-14 06:57:57'),
(17, 1, 61, 70.25, 14.05, 56.20, 'pending', NULL, '2025-11-14 07:06:31'),
(18, 1, 63, 69.35, 13.87, 55.48, 'pending', NULL, '2025-11-14 07:09:56'),
(19, 1, 66, 63.00, 12.60, 50.40, 'pending', NULL, '2025-11-14 07:13:41'),
(20, 1, 70, 72.65, 14.53, 58.12, 'pending', NULL, '2025-11-14 07:24:00'),
(21, 1, 72, 78.40, 15.68, 62.72, 'pending', NULL, '2025-11-14 07:32:21'),
(22, 1, 73, 1238.25, 247.65, 990.60, 'pending', NULL, '2025-11-14 07:37:28'),
(23, 1, 82, 7546.85, 1509.37, 6037.48, 'pending', NULL, '2025-11-14 08:08:36'),
(24, 1, 83, 189.75, 37.95, 151.80, 'pending', NULL, '2025-11-14 08:13:09'),
(25, 1, 84, 917.50, 183.50, 734.00, 'pending', NULL, '2025-11-14 08:20:43'),
(26, 1, 86, 2372.25, 474.45, 1897.80, 'pending', NULL, '2025-11-14 08:27:03'),
(27, 6, 87, 2137.00, 427.40, 1709.60, 'pending', NULL, '2025-11-14 08:44:41'),
(28, 7, 88, 741.05, 148.21, 592.84, 'pending', NULL, '2025-11-14 08:52:36'),
(29, 7, 89, 1828.95, 365.79, 1463.16, 'pending', NULL, '2025-11-14 14:24:19'),
(30, 7, 90, 548.10, 109.62, 438.48, 'pending', NULL, '2025-11-14 14:30:29'),
(31, 7, 91, 271.65, 54.33, 217.32, 'pending', NULL, '2025-11-14 14:33:54'),
(32, 7, 92, 98.50, 19.70, 78.80, 'pending', NULL, '2025-11-14 14:38:09'),
(33, 7, 93, 1812.35, 362.47, 1449.88, 'pending', NULL, '2025-11-14 14:43:25'),
(34, 7, 94, 507.00, 101.40, 405.60, 'pending', NULL, '2025-11-14 14:49:53'),
(35, 7, 95, 1828.95, 365.79, 1463.16, 'pending', NULL, '2025-11-14 14:53:00'),
(36, 7, 104, 111.35, 22.27, 89.08, 'pending', NULL, '2025-11-18 15:40:16'),
(37, 11, 105, 455.95, 91.19, 364.76, 'pending', NULL, '2025-11-18 15:56:40'),
(38, 1, 111, 211.35, 42.27, 169.08, 'pending', NULL, '2025-11-19 11:57:19'),
(39, 1, 112, 211.35, 42.27, 169.08, 'pending', NULL, '2025-11-19 12:24:32'),
(40, 1, 113, 792.40, 158.48, 633.92, 'pending', NULL, '2025-11-23 13:53:14');

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
(4, 3, 14.5896000, 120.9812000, 0.00, 0.00, '2025-11-12 15:21:41');

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
(3, '+639123456789', '959821', 1, '2025-11-14 22:00:18', '2025-11-14 13:55:18'),
(6, '+639123123113', '264168', 0, '2025-11-14 22:03:14', '2025-11-14 13:58:14'),
(7, '+639123123412', '470929', 1, '2025-11-14 22:11:31', '2025-11-14 14:06:31'),
(9, '+639231324151', '681640', 0, '2025-11-18 01:18:35', '2025-11-17 17:13:35'),
(10, '+639123123123', '579197', 0, '2025-11-18 01:21:50', '2025-11-17 17:16:50'),
(12, '+639123123131', '619434', 0, '2025-11-18 01:27:15', '2025-11-17 17:22:15'),
(16, '+639231231233', '284194', 1, '2025-11-18 01:30:11', '2025-11-17 17:25:11'),
(21, '+639231321312', '660534', 0, '2025-11-18 23:01:35', '2025-11-18 14:56:35'),
(22, '+639312312312', '195299', 0, '2025-11-18 23:02:46', '2025-11-18 14:57:46'),
(24, '+639231231231', '624530', 1, '2025-11-18 23:48:21', '2025-11-18 15:43:21'),
(25, '+639345374234', '744043', 1, '2025-11-18 23:51:38', '2025-11-18 15:46:38'),
(26, '+639345463452', '410237', 1, '2025-11-18 23:55:37', '2025-11-18 15:50:37');

-- --------------------------------------------------------

--
-- Table structure for table `realtime_connections`
--

CREATE TABLE `realtime_connections` (
  `user_id` int(11) NOT NULL,
  `role` enum('admin','driver','rider') NOT NULL,
  `connected_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `realtime_connections`
--

INSERT INTO `realtime_connections` (`user_id`, `role`, `connected_at`) VALUES
(1, 'admin', '2025-11-23 21:56:50'),
(2, 'rider', '2025-11-14 13:04:29'),
(6, 'rider', '2025-11-23 21:53:22');

-- --------------------------------------------------------

--
-- Table structure for table `realtime_notifications`
--

CREATE TABLE `realtime_notifications` (
  `id` int(11) NOT NULL,
  `target_type` enum('user','role') NOT NULL,
  `target_id` varchar(50) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `sent_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `realtime_notifications`
--

INSERT INTO `realtime_notifications` (`id`, `target_type`, `target_id`, `data`, `status`, `created_at`, `sent_at`) VALUES
(1, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"31\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Test Pickup Location\",\"lat\":14.5995,\"lng\":120.9842},\"dropoff\":{\"address\":\"Test Dropoff Location\",\"lat\":14.6042,\"lng\":120.9822},\"fare\":50,\"distance\":\"2.5 km\",\"payment_method\":\"cash\",\"timestamp\":1763098198}', 'sent', '2025-11-14 13:29:58', '2025-11-14 13:29:58'),
(2, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"32\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Test Pickup Location\",\"lat\":14.5995,\"lng\":120.9842},\"dropoff\":{\"address\":\"Test Dropoff Location\",\"lat\":14.6042,\"lng\":120.9822},\"fare\":50,\"distance\":\"2.5 km\",\"payment_method\":\"cash\",\"timestamp\":1763098262}', 'sent', '2025-11-14 13:31:02', '2025-11-14 13:31:02'),
(3, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"33\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Test Pickup Location\",\"lat\":14.5995,\"lng\":120.9842},\"dropoff\":{\"address\":\"Test Dropoff Location\",\"lat\":14.6042,\"lng\":120.9822},\"fare\":50,\"distance\":\"2.5 km\",\"payment_method\":\"cash\",\"timestamp\":1763098273}', 'sent', '2025-11-14 13:31:13', '2025-11-14 13:31:14'),
(4, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"34\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Test Pickup Location\",\"lat\":14.5995,\"lng\":120.9842},\"dropoff\":{\"address\":\"Test Dropoff Location\",\"lat\":14.6042,\"lng\":120.9822},\"fare\":50,\"distance\":\"2.5 km\",\"payment_method\":\"cash\",\"timestamp\":1763098361}', 'sent', '2025-11-14 13:32:41', '2025-11-14 13:32:41'),
(5, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"35\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Test Pickup Location\",\"lat\":14.5995,\"lng\":120.9842},\"dropoff\":{\"address\":\"Test Dropoff Location\",\"lat\":14.6042,\"lng\":120.9822},\"fare\":50,\"distance\":\"2.5 km\",\"payment_method\":\"cash\",\"timestamp\":1763098582}', 'sent', '2025-11-14 13:36:22', '2025-11-14 13:36:22'),
(6, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"36\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Test Pickup Location\",\"lat\":14.5995,\"lng\":120.9842},\"dropoff\":{\"address\":\"Test Dropoff Location\",\"lat\":14.6042,\"lng\":120.9822},\"fare\":50,\"distance\":\"2.5 km\",\"payment_method\":\"cash\",\"timestamp\":1763098638}', 'sent', '2025-11-14 13:37:18', '2025-11-14 13:37:18'),
(7, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"37\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Test Pickup Location\",\"lat\":14.5995,\"lng\":120.9842},\"dropoff\":{\"address\":\"Test Dropoff Location\",\"lat\":14.6042,\"lng\":120.9822},\"fare\":50,\"distance\":\"2.5 km\",\"payment_method\":\"cash\",\"timestamp\":1763098741}', 'sent', '2025-11-14 13:39:01', '2025-11-14 13:39:01'),
(8, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"38\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Test Pickup Location\",\"lat\":14.5995,\"lng\":120.9842},\"dropoff\":{\"address\":\"Test Dropoff Location\",\"lat\":14.6042,\"lng\":120.9822},\"fare\":50,\"distance\":\"2.5 km\",\"payment_method\":\"cash\",\"timestamp\":1763098793}', 'sent', '2025-11-14 13:39:53', '2025-11-14 13:39:53'),
(9, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"39\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Test Pickup Location\",\"lat\":14.5995,\"lng\":120.9842},\"dropoff\":{\"address\":\"Test Dropoff Location\",\"lat\":14.6042,\"lng\":120.9822},\"fare\":50,\"distance\":\"2.5 km\",\"payment_method\":\"cash\",\"timestamp\":1763098803}', 'sent', '2025-11-14 13:40:03', '2025-11-14 13:40:03'),
(10, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"40\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Test Pickup Location\",\"lat\":14.5995,\"lng\":120.9842},\"dropoff\":{\"address\":\"Test Dropoff Location\",\"lat\":14.6042,\"lng\":120.9822},\"fare\":50,\"distance\":\"2.5 km\",\"payment_method\":\"cash\",\"timestamp\":1763098803}', 'sent', '2025-11-14 13:40:03', '2025-11-14 13:40:04'),
(11, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"41\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Test Pickup Location\",\"lat\":14.5995,\"lng\":120.9842},\"dropoff\":{\"address\":\"Test Dropoff Location\",\"lat\":14.6042,\"lng\":120.9822},\"fare\":50,\"distance\":\"2.5 km\",\"payment_method\":\"cash\",\"timestamp\":1763098804}', 'sent', '2025-11-14 13:40:04', '2025-11-14 13:40:04'),
(12, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"42\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Test Pickup Location\",\"lat\":14.5995,\"lng\":120.9842},\"dropoff\":{\"address\":\"Test Dropoff Location\",\"lat\":14.6042,\"lng\":120.9822},\"fare\":50,\"distance\":\"2.5 km\",\"payment_method\":\"cash\",\"timestamp\":1763098804}', 'sent', '2025-11-14 13:40:04', '2025-11-14 13:40:04'),
(13, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"43\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Test Pickup Location\",\"lat\":14.5995,\"lng\":120.9842},\"dropoff\":{\"address\":\"Test Dropoff Location\",\"lat\":14.6042,\"lng\":120.9822},\"fare\":50,\"distance\":\"2.5 km\",\"payment_method\":\"cash\",\"timestamp\":1763098804}', 'sent', '2025-11-14 13:40:04', '2025-11-14 13:40:04'),
(14, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"44\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Test Pickup Location\",\"lat\":14.5995,\"lng\":120.9842},\"dropoff\":{\"address\":\"Test Dropoff Location\",\"lat\":14.6042,\"lng\":120.9822},\"fare\":50,\"distance\":\"2.5 km\",\"payment_method\":\"cash\",\"timestamp\":1763098804}', 'sent', '2025-11-14 13:40:04', '2025-11-14 13:40:05'),
(15, 'role', 'admin', '{\"type\":\"test_notification\",\"message\":\"This is a test notification\",\"timestamp\":1763099563}', 'sent', '2025-11-14 13:52:43', '2025-11-14 13:52:43'),
(16, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"46\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.60351485,\"lng\":120.98356189824217},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"fare\":70.25,\"distance\":\"0.95 km\",\"payment_method\":\"cash\",\"timestamp\":1763099812}', 'sent', '2025-11-14 13:56:52', '2025-11-14 13:56:53'),
(17, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"46\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":\"14.6035149\",\"lng\":\"120.9835619\"},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"fare\":\"70.25\",\"distance\":\"0.95 km\",\"timestamp\":1763099823}', 'sent', '2025-11-14 13:57:03', '2025-11-14 13:57:03'),
(18, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"46\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763099823}', 'sent', '2025-11-14 13:57:03', '2025-11-14 13:57:03'),
(19, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"47\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.60351485,\"lng\":120.98356189824217},\"dropoff\":{\"address\":\"UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6108779,\"lng\":120.9884947},\"fare\":70.7,\"distance\":\"0.98 km\",\"payment_method\":\"cash\",\"timestamp\":1763100003}', 'sent', '2025-11-14 14:00:03', '2025-11-14 14:00:03'),
(20, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"47\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":\"14.6035149\",\"lng\":\"120.9835619\"},\"dropoff\":{\"address\":\"UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6108779\",\"lng\":\"120.9884947\"},\"fare\":\"70.70\",\"distance\":\"0.98 km\",\"timestamp\":1763100008}', 'sent', '2025-11-14 14:00:08', '2025-11-14 14:00:08'),
(21, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"47\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763100008}', 'sent', '2025-11-14 14:00:08', '2025-11-14 14:00:08'),
(22, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"48\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.60351485,\"lng\":120.98356189824217},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"fare\":70.25,\"distance\":\"0.95 km\",\"payment_method\":\"cash\",\"timestamp\":1763100324}', 'sent', '2025-11-14 14:05:24', '2025-11-14 14:05:24'),
(23, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"49\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.60351485,\"lng\":120.98356189824217},\"dropoff\":{\"address\":\"C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.603003999999999,\"lng\":120.98547785},\"fare\":55.15,\"distance\":\"0.21 km\",\"payment_method\":\"cash\",\"timestamp\":1763100494}', 'sent', '2025-11-14 14:08:14', '2025-11-14 14:08:14'),
(24, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"50\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.60351485,\"lng\":120.98356189824217},\"dropoff\":{\"address\":\"C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.603003999999999,\"lng\":120.98547785},\"fare\":55.15,\"distance\":\"0.21 km\",\"payment_method\":\"cash\",\"timestamp\":1763100778}', 'sent', '2025-11-14 14:12:58', '2025-11-14 14:12:58'),
(25, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"51\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"San Sebastian College - Recoletos, C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.600191500000001,\"lng\":120.98934455910262},\"dropoff\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.60351485,\"lng\":120.98356189824217},\"fare\":64.8,\"distance\":\"0.72 km\",\"payment_method\":\"cash\",\"timestamp\":1763101027}', 'sent', '2025-11-14 14:17:07', '2025-11-14 14:17:07'),
(26, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"52\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.60351485,\"lng\":120.98356189824217},\"dropoff\":{\"address\":\"Rex Book Store, Nicanor Reyes Street, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6032904,\"lng\":120.98767855310275},\"fare\":58.6,\"distance\":\"0.44 km\",\"payment_method\":\"cash\",\"timestamp\":1763101295}', 'sent', '2025-11-14 14:21:35', '2025-11-14 14:21:35'),
(27, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"52\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":\"14.6035149\",\"lng\":\"120.9835619\"},\"dropoff\":{\"address\":\"Rex Book Store, Nicanor Reyes Street, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6032904\",\"lng\":\"120.9876786\"},\"fare\":\"58.60\",\"distance\":\"0.44 km\",\"timestamp\":1763101301}', 'sent', '2025-11-14 14:21:41', '2025-11-14 14:21:41'),
(28, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"52\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763101301}', 'sent', '2025-11-14 14:21:41', '2025-11-14 14:21:41'),
(29, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"53\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.60351485,\"lng\":120.98356189824217},\"dropoff\":{\"address\":\"Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines\",\"lat\":14.60475195,\"lng\":120.97815645},\"fare\":63,\"distance\":\"0.60 km\",\"payment_method\":\"cash\",\"timestamp\":1763101372}', 'sent', '2025-11-14 14:22:52', '2025-11-14 14:22:52'),
(30, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"53\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":\"14.6035149\",\"lng\":\"120.9835619\"},\"dropoff\":{\"address\":\"Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines\",\"lat\":\"14.6047520\",\"lng\":\"120.9781565\"},\"fare\":\"63.00\",\"distance\":\"0.60 km\",\"timestamp\":1763101399}', 'sent', '2025-11-14 14:23:19', '2025-11-14 14:23:19'),
(31, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"53\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763101399}', 'sent', '2025-11-14 14:23:19', '2025-11-14 14:23:19'),
(32, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"54\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.60351485,\"lng\":120.98356189824217},\"dropoff\":{\"address\":\"Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines\",\"lat\":14.60475195,\"lng\":120.97815645},\"fare\":63,\"distance\":\"0.60 km\",\"payment_method\":\"cash\",\"timestamp\":1763102008}', 'sent', '2025-11-14 14:33:28', '2025-11-14 14:33:28'),
(33, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"54\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":\"14.6035149\",\"lng\":\"120.9835619\"},\"dropoff\":{\"address\":\"Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines\",\"lat\":\"14.6047520\",\"lng\":\"120.9781565\"},\"fare\":\"63.00\",\"distance\":\"0.60 km\",\"timestamp\":1763102012}', 'sent', '2025-11-14 14:33:32', '2025-11-14 14:33:32'),
(34, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"54\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763102012}', 'sent', '2025-11-14 14:33:32', '2025-11-14 14:33:32'),
(35, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":54,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763102042}', 'sent', '2025-11-14 14:34:02', '2025-11-14 14:34:02'),
(36, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"54\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763102053}', 'sent', '2025-11-14 14:34:13', '2025-11-14 14:34:13'),
(37, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"54\",\"status\":\"completed\",\"fare\":\"63.00\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763102063}', 'sent', '2025-11-14 14:34:23', '2025-11-14 14:34:23'),
(38, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"55\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.60351485,\"lng\":120.98356189824217},\"dropoff\":{\"address\":\"UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6108779,\"lng\":120.9884947},\"fare\":70.7,\"distance\":\"0.98 km\",\"payment_method\":\"cash\",\"timestamp\":1763102383}', 'sent', '2025-11-14 14:39:43', '2025-11-14 14:39:43'),
(39, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"55\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":\"14.6035149\",\"lng\":\"120.9835619\"},\"dropoff\":{\"address\":\"UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6108779\",\"lng\":\"120.9884947\"},\"fare\":\"70.70\",\"distance\":\"0.98 km\",\"timestamp\":1763102405}', 'sent', '2025-11-14 14:40:05', '2025-11-14 14:40:05'),
(40, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"55\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763102405}', 'sent', '2025-11-14 14:40:05', '2025-11-14 14:40:05'),
(41, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":55,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763102429}', 'sent', '2025-11-14 14:40:29', '2025-11-14 14:40:29'),
(42, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"55\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763102437}', 'sent', '2025-11-14 14:40:37', '2025-11-14 14:40:38'),
(43, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"55\",\"status\":\"completed\",\"fare\":\"70.70\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763102444}', 'sent', '2025-11-14 14:40:44', '2025-11-14 14:40:44'),
(44, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"56\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.60351485,\"lng\":120.98356189824217},\"dropoff\":{\"address\":\"Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines\",\"lat\":14.60475195,\"lng\":120.97815645},\"fare\":63,\"distance\":\"0.60 km\",\"payment_method\":\"cash\",\"timestamp\":1763102852}', 'sent', '2025-11-14 14:47:32', '2025-11-14 14:47:32'),
(45, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"56\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":\"14.6035149\",\"lng\":\"120.9835619\"},\"dropoff\":{\"address\":\"Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines\",\"lat\":\"14.6047520\",\"lng\":\"120.9781565\"},\"fare\":\"63.00\",\"distance\":\"0.60 km\",\"timestamp\":1763102858}', 'sent', '2025-11-14 14:47:38', '2025-11-14 14:47:39'),
(46, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"56\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763102858}', 'sent', '2025-11-14 14:47:38', '2025-11-14 14:47:39'),
(47, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":56,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763102875}', 'sent', '2025-11-14 14:47:55', '2025-11-14 14:47:55'),
(48, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"56\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763102882}', 'sent', '2025-11-14 14:48:02', '2025-11-14 14:48:02'),
(49, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"56\",\"status\":\"completed\",\"fare\":\"63.00\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763102896}', 'sent', '2025-11-14 14:48:16', '2025-11-14 14:48:16'),
(50, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"57\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.60351485,\"lng\":120.98356189824217},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"fare\":70.25,\"distance\":\"0.95 km\",\"payment_method\":\"cash\",\"timestamp\":1763103080}', 'sent', '2025-11-14 14:51:20', '2025-11-14 14:51:20'),
(51, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"57\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":\"14.6035149\",\"lng\":\"120.9835619\"},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"fare\":\"70.25\",\"distance\":\"0.95 km\",\"timestamp\":1763103085}', 'sent', '2025-11-14 14:51:25', '2025-11-14 14:51:25'),
(52, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"57\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763103085}', 'sent', '2025-11-14 14:51:25', '2025-11-14 14:51:25'),
(53, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":57,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763103109}', 'sent', '2025-11-14 14:51:49', '2025-11-14 14:51:49'),
(54, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"57\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763103117}', 'sent', '2025-11-14 14:51:57', '2025-11-14 14:51:57'),
(55, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"57\",\"status\":\"completed\",\"fare\":\"70.25\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763103122}', 'sent', '2025-11-14 14:52:02', '2025-11-14 14:52:02'),
(56, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"58\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.60351485,\"lng\":120.98356189824217},\"dropoff\":{\"address\":\"Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines\",\"lat\":14.60475195,\"lng\":120.97815645},\"fare\":63,\"distance\":\"0.60 km\",\"payment_method\":\"cash\",\"timestamp\":1763103182}', 'sent', '2025-11-14 14:53:02', '2025-11-14 14:53:02'),
(57, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"58\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":\"14.6035149\",\"lng\":\"120.9835619\"},\"dropoff\":{\"address\":\"Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines\",\"lat\":\"14.6047520\",\"lng\":\"120.9781565\"},\"fare\":\"63.00\",\"distance\":\"0.60 km\",\"timestamp\":1763103201}', 'sent', '2025-11-14 14:53:21', '2025-11-14 14:53:21'),
(58, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"58\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763103201}', 'sent', '2025-11-14 14:53:21', '2025-11-14 14:53:21'),
(59, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":58,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763103216}', 'sent', '2025-11-14 14:53:36', '2025-11-14 14:53:36'),
(60, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"58\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763103223}', 'sent', '2025-11-14 14:53:43', '2025-11-14 14:53:43'),
(61, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"58\",\"status\":\"completed\",\"fare\":\"63.00\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763103228}', 'sent', '2025-11-14 14:53:48', '2025-11-14 14:53:48'),
(62, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"59\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"dropoff\":{\"address\":\"UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6108779,\"lng\":120.9884947},\"fare\":51.65,\"distance\":\"0.11 km\",\"payment_method\":\"cash\",\"timestamp\":1763103432}', 'sent', '2025-11-14 14:57:12', '2025-11-14 14:57:12'),
(63, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"59\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"dropoff\":{\"address\":\"UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6108779\",\"lng\":\"120.9884947\"},\"fare\":\"51.65\",\"distance\":\"0.11 km\",\"timestamp\":1763103446}', 'sent', '2025-11-14 14:57:26', '2025-11-14 14:57:26'),
(64, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"59\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763103446}', 'sent', '2025-11-14 14:57:26', '2025-11-14 14:57:26'),
(65, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":59,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763103467}', 'sent', '2025-11-14 14:57:47', '2025-11-14 14:57:47'),
(66, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"59\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763103473}', 'sent', '2025-11-14 14:57:53', '2025-11-14 14:57:53'),
(67, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"59\",\"status\":\"completed\",\"fare\":\"51.65\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763103477}', 'sent', '2025-11-14 14:57:57', '2025-11-14 14:57:57'),
(68, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"60\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Serahh\'s Aircon Repair, Kamias Road, Quezon City, Project 1, Metro Manila, Philippines\",\"lat\":14.6315841,\"lng\":121.0482572},\"dropoff\":{\"address\":\"Dangwa Flower Market, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.61495385,\"lng\":120.9885809848713},\"fare\":190.2,\"distance\":\"6.68 km\",\"payment_method\":\"cash\",\"timestamp\":1763103524}', 'sent', '2025-11-14 14:58:44', '2025-11-14 14:58:44'),
(69, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"60\",\"pickup\":{\"address\":\"Serahh\'s Aircon Repair, Kamias Road, Quezon City, Project 1, Metro Manila, Philippines\",\"lat\":\"14.6315841\",\"lng\":\"121.0482572\"},\"dropoff\":{\"address\":\"Dangwa Flower Market, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":\"14.6149539\",\"lng\":\"120.9885810\"},\"fare\":\"190.20\",\"distance\":\"6.68 km\",\"timestamp\":1763103564}', 'sent', '2025-11-14 14:59:24', '2025-11-14 14:59:24'),
(70, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"60\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763103564}', 'sent', '2025-11-14 14:59:24', '2025-11-14 14:59:24'),
(71, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"61\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.60351485,\"lng\":120.98356189824217},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"fare\":70.25,\"distance\":\"0.95 km\",\"payment_method\":\"cash\",\"timestamp\":1763103928}', 'sent', '2025-11-14 15:05:28', '2025-11-14 15:05:28'),
(72, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"61\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":\"14.6035149\",\"lng\":\"120.9835619\"},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"fare\":\"70.25\",\"distance\":\"0.95 km\",\"timestamp\":1763103958}', 'sent', '2025-11-14 15:05:58', '2025-11-14 15:05:58'),
(73, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"61\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763103958}', 'sent', '2025-11-14 15:05:58', '2025-11-14 15:05:58'),
(74, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":61,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763103981}', 'sent', '2025-11-14 15:06:21', '2025-11-14 15:06:21'),
(75, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"61\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763103987}', 'sent', '2025-11-14 15:06:27', '2025-11-14 15:06:27'),
(76, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"61\",\"status\":\"completed\",\"fare\":\"70.25\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763103991}', 'sent', '2025-11-14 15:06:31', '2025-11-14 15:06:31'),
(77, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"62\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Embassy of the Philippines, Unioninkatu, Helsinki, Kluuvi, Uusimaa, Finland\",\"lat\":60.168486,\"lng\":24.9506363},\"dropoff\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, Bedford Street, Dasmari\\u00f1as, Cavite, Philippines\",\"lat\":14.33402235,\"lng\":120.95126220986008},\"fare\":188114.05,\"distance\":\"8955.47 km\",\"payment_method\":\"cash\",\"timestamp\":1763104072}', 'sent', '2025-11-14 15:07:52', '2025-11-14 15:07:52'),
(78, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"62\",\"pickup\":{\"address\":\"Embassy of the Philippines, Unioninkatu, Helsinki, Kluuvi, Uusimaa, Finland\",\"lat\":\"60.1684860\",\"lng\":\"24.9506363\"},\"dropoff\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, Bedford Street, Dasmari\\u00f1as, Cavite, Philippines\",\"lat\":\"14.3340224\",\"lng\":\"120.9512622\"},\"fare\":\"188114.05\",\"distance\":\"8955.47 km\",\"timestamp\":1763104100}', 'sent', '2025-11-14 15:08:20', '2025-11-14 15:08:20'),
(79, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"62\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763104100}', 'sent', '2025-11-14 15:08:20', '2025-11-14 15:08:20'),
(80, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"63\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.603003999999999,\"lng\":120.98547785},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"fare\":69.35,\"distance\":\"0.89 km\",\"payment_method\":\"cash\",\"timestamp\":1763104126}', 'sent', '2025-11-14 15:08:46', '2025-11-14 15:08:46'),
(81, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"63\",\"pickup\":{\"address\":\"C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6030040\",\"lng\":\"120.9854779\"},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"fare\":\"69.35\",\"distance\":\"0.89 km\",\"timestamp\":1763104139}', 'sent', '2025-11-14 15:08:59', '2025-11-14 15:08:59'),
(82, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"63\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763104139}', 'sent', '2025-11-14 15:08:59', '2025-11-14 15:09:01'),
(83, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":63,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763104180}', 'sent', '2025-11-14 15:09:40', '2025-11-14 15:09:40'),
(84, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"63\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763104187}', 'sent', '2025-11-14 15:09:47', '2025-11-14 15:09:47'),
(85, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"63\",\"status\":\"completed\",\"fare\":\"69.35\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763104197}', 'sent', '2025-11-14 15:09:57', '2025-11-14 15:09:57'),
(86, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"64\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Santo Tomas de Villa Nueva Cemetery, Padre Lupo Street, Pasig, Pasig Second District, Metro Manila, Philippines\",\"lat\":14.61013515,\"lng\":121.09331832452774},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"fare\":286.15,\"distance\":\"11.21 km\",\"payment_method\":\"cash\",\"timestamp\":1763104220}', 'sent', '2025-11-14 15:10:20', '2025-11-14 15:10:20'),
(87, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"64\",\"pickup\":{\"address\":\"Santo Tomas de Villa Nueva Cemetery, Padre Lupo Street, Pasig, Pasig Second District, Metro Manila, Philippines\",\"lat\":\"14.6101352\",\"lng\":\"121.0933183\"},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"fare\":\"286.15\",\"distance\":\"11.21 km\",\"timestamp\":1763104259}', 'sent', '2025-11-14 15:10:59', '2025-11-14 15:11:00'),
(88, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"64\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763104259}', 'sent', '2025-11-14 15:10:59', '2025-11-14 15:11:00'),
(89, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":64,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763104279}', 'sent', '2025-11-14 15:11:19', '2025-11-14 15:11:19'),
(90, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"65\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.60351485,\"lng\":120.98356189824217},\"dropoff\":{\"address\":\"Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines\",\"lat\":14.60475195,\"lng\":120.97815645},\"fare\":63,\"distance\":\"0.60 km\",\"payment_method\":\"cash\",\"timestamp\":1763104322}', 'sent', '2025-11-14 15:12:02', '2025-11-14 15:12:02'),
(91, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"65\",\"pickup\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":\"14.6035149\",\"lng\":\"120.9835619\"},\"dropoff\":{\"address\":\"Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines\",\"lat\":\"14.6047520\",\"lng\":\"120.9781565\"},\"fare\":\"63.00\",\"distance\":\"0.60 km\",\"timestamp\":1763104363}', 'sent', '2025-11-14 15:12:43', '2025-11-14 15:12:43'),
(92, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"65\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763104363}', 'sent', '2025-11-14 15:12:43', '2025-11-14 15:12:43'),
(93, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"66\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines\",\"lat\":14.60475195,\"lng\":120.97815645},\"dropoff\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.60351485,\"lng\":120.98356189824217},\"fare\":63,\"distance\":\"0.60 km\",\"payment_method\":\"cash\",\"timestamp\":1763104386}', 'sent', '2025-11-14 15:13:06', '2025-11-14 15:13:07'),
(94, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"66\",\"pickup\":{\"address\":\"Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines\",\"lat\":\"14.6047520\",\"lng\":\"120.9781565\"},\"dropoff\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":\"14.6035149\",\"lng\":\"120.9835619\"},\"fare\":\"63.00\",\"distance\":\"0.60 km\",\"timestamp\":1763104392}', 'sent', '2025-11-14 15:13:12', '2025-11-14 15:13:12'),
(95, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"66\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763104392}', 'sent', '2025-11-14 15:13:12', '2025-11-14 15:13:12'),
(96, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":66,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763104412}', 'sent', '2025-11-14 15:13:32', '2025-11-14 15:13:33'),
(97, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"66\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763104417}', 'sent', '2025-11-14 15:13:37', '2025-11-14 15:13:37'),
(98, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"66\",\"status\":\"completed\",\"fare\":\"63.00\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763104421}', 'sent', '2025-11-14 15:13:41', '2025-11-14 15:13:41'),
(99, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"67\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"dropoff\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":14.60351485,\"lng\":120.98356189824217},\"fare\":70.25,\"distance\":\"0.95 km\",\"payment_method\":\"cash\",\"timestamp\":1763104458}', 'sent', '2025-11-14 15:14:18', '2025-11-14 15:14:18'),
(100, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"67\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"dropoff\":{\"address\":\"Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines\",\"lat\":\"14.6035149\",\"lng\":\"120.9835619\"},\"fare\":\"70.25\",\"distance\":\"0.95 km\",\"timestamp\":1763104463}', 'sent', '2025-11-14 15:14:23', '2025-11-14 15:14:24'),
(101, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"67\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763104463}', 'sent', '2025-11-14 15:14:23', '2025-11-14 15:14:24'),
(102, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"68\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"UST Faculty of Medicine and Surgery, Micsino Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6117871,\"lng\":120.9893875},\"dropoff\":{\"address\":\"Ystilo Salon, South Wing, Pasay, Zone 10, Metro Manila, Philippines\",\"lat\":14.5339796,\"lng\":120.9820274},\"fare\":232.35,\"distance\":\"8.69 km\",\"payment_method\":\"cash\",\"timestamp\":1763104485}', 'sent', '2025-11-14 15:14:45', '2025-11-14 15:14:45'),
(103, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"68\",\"pickup\":{\"address\":\"UST Faculty of Medicine and Surgery, Micsino Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6117871\",\"lng\":\"120.9893875\"},\"dropoff\":{\"address\":\"Ystilo Salon, South Wing, Pasay, Zone 10, Metro Manila, Philippines\",\"lat\":\"14.5339796\",\"lng\":\"120.9820274\"},\"fare\":\"232.35\",\"distance\":\"8.69 km\",\"timestamp\":1763104489}', 'sent', '2025-11-14 15:14:49', '2025-11-14 15:14:49'),
(104, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"68\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763104489}', 'sent', '2025-11-14 15:14:49', '2025-11-14 15:14:50'),
(105, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"69\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"UST Faculty of Medicine and Surgery, Micsino Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6117871,\"lng\":120.9893875},\"dropoff\":{\"address\":\"UST Faculty of Medicine and Surgery, Micsino Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6117871,\"lng\":120.9893875},\"fare\":50,\"distance\":\"0.00 km\",\"payment_method\":\"cash\",\"timestamp\":1763104597}', 'sent', '2025-11-14 15:16:37', '2025-11-14 15:16:38'),
(106, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"69\",\"pickup\":{\"address\":\"UST Faculty of Medicine and Surgery, Micsino Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6117871\",\"lng\":\"120.9893875\"},\"dropoff\":{\"address\":\"UST Faculty of Medicine and Surgery, Micsino Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6117871\",\"lng\":\"120.9893875\"},\"fare\":\"50.00\",\"distance\":\"0.00 km\",\"timestamp\":1763104723}', 'sent', '2025-11-14 15:18:43', '2025-11-14 15:18:43'),
(107, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"69\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763104723}', 'sent', '2025-11-14 15:18:43', '2025-11-14 15:18:43'),
(108, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"70\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"San Sebastian College - Recoletos, C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.600191500000001,\"lng\":120.98934455910262},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"fare\":72.65,\"distance\":\"1.11 km\",\"payment_method\":\"cash\",\"timestamp\":1763104932}', 'sent', '2025-11-14 15:22:12', '2025-11-14 15:22:12'),
(109, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"70\",\"pickup\":{\"address\":\"San Sebastian College - Recoletos, C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6001915\",\"lng\":\"120.9893446\"},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"fare\":\"72.65\",\"distance\":\"1.11 km\",\"timestamp\":1763104969}', 'sent', '2025-11-14 15:22:49', '2025-11-14 15:22:49'),
(110, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"70\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763104969}', 'sent', '2025-11-14 15:22:49', '2025-11-14 15:22:49'),
(111, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":70,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763105029}', 'sent', '2025-11-14 15:23:49', '2025-11-14 15:23:50'),
(112, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"70\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763105033}', 'sent', '2025-11-14 15:23:53', '2025-11-14 15:23:53'),
(113, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"70\",\"status\":\"completed\",\"fare\":\"72.65\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763105040}', 'sent', '2025-11-14 15:24:00', '2025-11-14 15:24:00'),
(114, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"71\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"San Sebastian College - Recoletos, C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.600191500000001,\"lng\":120.98934455910262},\"dropoff\":{\"address\":\"Ersao, Abad Santos Avenue, Manila, Second District, Metro Manila, Philippines\",\"lat\":14.6083376,\"lng\":120.9757635},\"fare\":85.8,\"distance\":\"1.72 km\",\"payment_method\":\"cash\",\"timestamp\":1763105065}', 'sent', '2025-11-14 15:24:25', '2025-11-14 15:24:25'),
(115, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"71\",\"pickup\":{\"address\":\"San Sebastian College - Recoletos, C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6001915\",\"lng\":\"120.9893446\"},\"dropoff\":{\"address\":\"Ersao, Abad Santos Avenue, Manila, Second District, Metro Manila, Philippines\",\"lat\":\"14.6083376\",\"lng\":\"120.9757635\"},\"fare\":\"85.80\",\"distance\":\"1.72 km\",\"timestamp\":1763105096}', 'sent', '2025-11-14 15:24:56', '2025-11-14 15:24:56'),
(116, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"71\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763105096}', 'sent', '2025-11-14 15:24:56', '2025-11-14 15:24:56'),
(117, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"72\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Youth Gospel Center of the Philippines, Masangkay Street, Manila, Second District, Metro Manila, Philippines\",\"lat\":14.60636665,\"lng\":120.97774252851306},\"dropoff\":{\"address\":\"CRS Fastfood, Dalupan Street, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6035364,\"lng\":120.9900159},\"fare\":78.4,\"distance\":\"1.36 km\",\"payment_method\":\"cash\",\"timestamp\":1763105466}', 'sent', '2025-11-14 15:31:06', '2025-11-14 15:31:06'),
(118, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"72\",\"pickup\":{\"address\":\"Youth Gospel Center of the Philippines, Masangkay Street, Manila, Second District, Metro Manila, Philippines\",\"lat\":\"14.6063667\",\"lng\":\"120.9777425\"},\"dropoff\":{\"address\":\"CRS Fastfood, Dalupan Street, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6035364\",\"lng\":\"120.9900159\"},\"fare\":\"78.40\",\"distance\":\"1.36 km\",\"timestamp\":1763105488}', 'sent', '2025-11-14 15:31:28', '2025-11-14 15:31:28'),
(119, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"72\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763105488}', 'sent', '2025-11-14 15:31:28', '2025-11-14 15:31:28'),
(120, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":72,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763105530}', 'sent', '2025-11-14 15:32:10', '2025-11-14 15:32:10'),
(121, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"72\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763105536}', 'sent', '2025-11-14 15:32:16', '2025-11-14 15:32:16'),
(122, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"72\",\"status\":\"completed\",\"fare\":\"78.40\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763105541}', 'sent', '2025-11-14 15:32:21', '2025-11-14 15:32:21');
INSERT INTO `realtime_notifications` (`id`, `target_type`, `target_id`, `data`, `status`, `created_at`, `sent_at`) VALUES
(123, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"73\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Ride, Banawe Street, Quezon City, Santa Mesa Heights, Metro Manila, Philippines\",\"lat\":14.63211055,\"lng\":121.00154465243091},\"dropoff\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":14.9094432,\"lng\":120.5606322},\"fare\":1238.25,\"distance\":\"56.55 km\",\"payment_method\":\"cash\",\"timestamp\":1763105732}', 'sent', '2025-11-14 15:35:32', '2025-11-14 15:35:32'),
(124, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"73\",\"pickup\":{\"address\":\"Ride, Banawe Street, Quezon City, Santa Mesa Heights, Metro Manila, Philippines\",\"lat\":\"14.6321106\",\"lng\":\"121.0015447\"},\"dropoff\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":\"14.9094432\",\"lng\":\"120.5606322\"},\"fare\":\"1238.25\",\"distance\":\"56.55 km\",\"timestamp\":1763105817}', 'sent', '2025-11-14 15:36:57', '2025-11-14 15:36:58'),
(125, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"73\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763105817}', 'sent', '2025-11-14 15:36:57', '2025-11-14 15:36:58'),
(126, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":73,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763105835}', 'sent', '2025-11-14 15:37:15', '2025-11-14 15:37:15'),
(127, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"73\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763105841}', 'sent', '2025-11-14 15:37:21', '2025-11-14 15:37:22'),
(128, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"73\",\"status\":\"completed\",\"fare\":\"1238.25\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763105849}', 'sent', '2025-11-14 15:37:29', '2025-11-14 15:37:29'),
(129, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"74\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Serafia Street, Valenzuela, 1st District, Metro Manila, Philippines\",\"lat\":14.696186663280487,\"lng\":120.96612600856523},\"dropoff\":{\"address\":\"Serahh\'s Aircon Repair, Kamias Road, Quezon City, Project 1, Metro Manila, Philippines\",\"lat\":14.6315841,\"lng\":121.0482572},\"fare\":288.85,\"distance\":\"11.39 km\",\"payment_method\":\"cash\",\"timestamp\":1763105879}', 'sent', '2025-11-14 15:37:59', '2025-11-14 15:38:00'),
(130, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"74\",\"pickup\":{\"address\":\"Serafia Street, Valenzuela, 1st District, Metro Manila, Philippines\",\"lat\":\"14.6961867\",\"lng\":\"120.9661260\"},\"dropoff\":{\"address\":\"Serahh\'s Aircon Repair, Kamias Road, Quezon City, Project 1, Metro Manila, Philippines\",\"lat\":\"14.6315841\",\"lng\":\"121.0482572\"},\"fare\":\"288.85\",\"distance\":\"11.39 km\",\"timestamp\":1763105883}', 'sent', '2025-11-14 15:38:03', '2025-11-14 15:38:03'),
(131, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"74\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763105883}', 'sent', '2025-11-14 15:38:03', '2025-11-14 15:38:03'),
(132, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"75\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Grace and Sasa\'s Flower Shop, J. P. Carigma Street, Antipolo, Rizal, Philippines\",\"lat\":14.5866932,\"lng\":121.1751943},\"dropoff\":{\"address\":\"Adasa Ancestral House, Manuel L. Quezon Avenue, Dapitan, Zamboanga del Norte, Philippines\",\"lat\":8.65686065,\"lng\":123.4244132},\"fare\":14820.4,\"distance\":\"703.36 km\",\"payment_method\":\"cash\",\"timestamp\":1763106145}', 'sent', '2025-11-14 15:42:25', '2025-11-14 15:42:25'),
(133, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"75\",\"pickup\":{\"address\":\"Grace and Sasa\'s Flower Shop, J. P. Carigma Street, Antipolo, Rizal, Philippines\",\"lat\":\"14.5866932\",\"lng\":\"121.1751943\"},\"dropoff\":{\"address\":\"Adasa Ancestral House, Manuel L. Quezon Avenue, Dapitan, Zamboanga del Norte, Philippines\",\"lat\":\"8.6568607\",\"lng\":\"123.4244132\"},\"fare\":\"14820.40\",\"distance\":\"703.36 km\",\"timestamp\":1763106165}', 'sent', '2025-11-14 15:42:45', '2025-11-14 15:42:45'),
(134, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"75\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763106165}', 'sent', '2025-11-14 15:42:45', '2025-11-14 15:42:45'),
(135, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"76\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Rewa Road, San Fernando, Pampanga, Philippines\",\"lat\":15.119278617331615,\"lng\":120.60856119694247},\"dropoff\":{\"address\":\"Asdum Barangay Hall, J. P. Rizal Street, San Vicente, Camarines Norte, Philippines\",\"lat\":14.1194299,\"lng\":122.8703555},\"fare\":5669.25,\"distance\":\"267.55 km\",\"payment_method\":\"cash\",\"timestamp\":1763106228}', 'sent', '2025-11-14 15:43:48', '2025-11-14 15:43:48'),
(136, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"77\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Asdum, Camarines Norte, Philippines\",\"lat\":14.1193515,\"lng\":122.8703828},\"dropoff\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":14.9094432,\"lng\":120.5606322},\"fare\":5587.5,\"distance\":\"263.70 km\",\"payment_method\":\"cash\",\"timestamp\":1763106404}', 'sent', '2025-11-14 15:46:44', '2025-11-14 15:46:44'),
(137, 'user', '6', '{\"type\":\"booking_rejected\",\"booking_id\":\"77\",\"message\":\"Your booking request has been rejected. Please try booking again.\",\"timestamp\":1763106428}', 'sent', '2025-11-14 15:47:08', '2025-11-14 15:47:08'),
(138, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"78\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":14.9094432,\"lng\":120.5606322},\"dropoff\":{\"address\":\"Asgard Enterprises, Caimito Road, Caloocan, University Hills, Metro Manila, Philippines\",\"lat\":14.6587707,\"lng\":120.9839076},\"fare\":1170.55,\"distance\":\"53.37 km\",\"payment_method\":\"cash\",\"timestamp\":1763106441}', 'sent', '2025-11-14 15:47:21', '2025-11-14 15:47:21'),
(139, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"78\",\"pickup\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":\"14.9094432\",\"lng\":\"120.5606322\"},\"dropoff\":{\"address\":\"Asgard Enterprises, Caimito Road, Caloocan, University Hills, Metro Manila, Philippines\",\"lat\":\"14.6587707\",\"lng\":\"120.9839076\"},\"fare\":\"1170.55\",\"distance\":\"53.37 km\",\"timestamp\":1763106446}', 'sent', '2025-11-14 15:47:26', '2025-11-14 15:47:26'),
(140, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"78\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763106446}', 'sent', '2025-11-14 15:47:26', '2025-11-14 15:47:26'),
(141, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"79\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Asdum, Camarines Norte, Philippines\",\"lat\":14.1193515,\"lng\":122.8703828},\"dropoff\":{\"address\":\"Aquino Assassination site, NAIA Road, Para\\u00f1aque, Para\\u00f1aque District 1, Metro Manila, Philippines\",\"lat\":14.503548,\"lng\":121.0041099},\"fare\":4367.55,\"distance\":\"205.57 km\",\"payment_method\":\"cash\",\"timestamp\":1763106697}', 'sent', '2025-11-14 15:51:37', '2025-11-14 15:51:37'),
(142, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"79\",\"pickup\":{\"address\":\"Asdum, Camarines Norte, Philippines\",\"lat\":\"14.1193515\",\"lng\":\"122.8703828\"},\"dropoff\":{\"address\":\"Aquino Assassination site, NAIA Road, Para\\u00f1aque, Para\\u00f1aque District 1, Metro Manila, Philippines\",\"lat\":\"14.5035480\",\"lng\":\"121.0041099\"},\"fare\":\"4367.55\",\"distance\":\"205.57 km\",\"timestamp\":1763106702}', 'sent', '2025-11-14 15:51:42', '2025-11-14 15:51:42'),
(143, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"79\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763106702}', 'sent', '2025-11-14 15:51:42', '2025-11-14 15:51:43'),
(144, 'user', '6', '{\"type\":\"driver_rejected\",\"ride_id\":79,\"message\":\"Driver declined your booking. Searching for another driver...\",\"reason\":\"Driver declined\",\"timestamp\":1763106718}', 'sent', '2025-11-14 15:51:58', '2025-11-14 15:51:58'),
(145, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"80\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":14.9094432,\"lng\":120.5606322},\"dropoff\":{\"address\":\"Aquino Assassination site, NAIA Road, Para\\u00f1aque, Para\\u00f1aque District 1, Metro Manila, Philippines\",\"lat\":14.503548,\"lng\":121.0041099},\"fare\":1429.05,\"distance\":\"65.67 km\",\"payment_method\":\"cash\",\"timestamp\":1763106975}', 'sent', '2025-11-14 15:56:15', '2025-11-14 15:56:15'),
(146, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"80\",\"pickup\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":\"14.9094432\",\"lng\":\"120.5606322\"},\"dropoff\":{\"address\":\"Aquino Assassination site, NAIA Road, Para\\u00f1aque, Para\\u00f1aque District 1, Metro Manila, Philippines\",\"lat\":\"14.5035480\",\"lng\":\"121.0041099\"},\"fare\":\"1429.05\",\"distance\":\"65.67 km\",\"timestamp\":1763106980}', 'sent', '2025-11-14 15:56:20', '2025-11-14 15:56:20'),
(147, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"80\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763106980}', 'sent', '2025-11-14 15:56:20', '2025-11-14 15:56:20'),
(148, 'user', '6', '{\"type\":\"driver_rejected\",\"ride_id\":80,\"message\":\"Driver declined your booking. Admin will assign another driver shortly...\",\"reason\":\"Driver declined\",\"timestamp\":1763107014}', 'sent', '2025-11-14 15:56:54', '2025-11-14 15:56:54'),
(149, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"80\",\"pickup\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":\"14.9094432\",\"lng\":\"120.5606322\"},\"dropoff\":{\"address\":\"Aquino Assassination site, NAIA Road, Para\\u00f1aque, Para\\u00f1aque District 1, Metro Manila, Philippines\",\"lat\":\"14.5035480\",\"lng\":\"121.0041099\"},\"fare\":\"1429.05\",\"distance\":\"65.67 km\",\"timestamp\":1763107037}', 'sent', '2025-11-14 15:57:17', '2025-11-14 15:57:17'),
(150, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"80\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763107037}', 'sent', '2025-11-14 15:57:17', '2025-11-14 15:57:17'),
(151, 'user', '6', '{\"type\":\"driver_rejected\",\"ride_id\":80,\"message\":\"Driver declined your booking. Admin will assign another driver shortly...\",\"reason\":\"Driver declined\",\"timestamp\":1763107153}', 'sent', '2025-11-14 15:59:13', '2025-11-14 15:59:13'),
(152, 'user', '1', '{\"type\":\"driver_rejected\",\"booking_id\":80,\"driver_id\":1,\"message\":\"Driver rejected booking #80. Booking returned to pending.\",\"reason\":\"Driver declined\",\"timestamp\":1763107153}', 'sent', '2025-11-14 15:59:13', '2025-11-14 15:59:14'),
(153, 'user', '6', '{\"type\":\"booking_rejected\",\"booking_id\":\"80\",\"message\":\"Your booking request has been rejected. Please try booking again.\",\"timestamp\":1763107166}', 'sent', '2025-11-14 15:59:26', '2025-11-14 15:59:27'),
(154, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"81\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"YSA, Rizal Drive, Taguig, Taguig District 2, Metro Manila, Philippines\",\"lat\":14.55221,\"lng\":121.0450098},\"dropoff\":{\"address\":\"Yssa Mae\'s Sportss & Music Enterprises, Gonzalo Puyat Street, Manila, Quiapo, Metro Manila, Philippines\",\"lat\":14.6007338,\"lng\":120.9833501},\"fare\":230.25,\"distance\":\"8.55 km\",\"payment_method\":\"cash\",\"timestamp\":1763107206}', 'sent', '2025-11-14 16:00:06', '2025-11-14 16:00:06'),
(155, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"81\",\"pickup\":{\"address\":\"YSA, Rizal Drive, Taguig, Taguig District 2, Metro Manila, Philippines\",\"lat\":\"14.5522100\",\"lng\":\"121.0450098\"},\"dropoff\":{\"address\":\"Yssa Mae\'s Sportss & Music Enterprises, Gonzalo Puyat Street, Manila, Quiapo, Metro Manila, Philippines\",\"lat\":\"14.6007338\",\"lng\":\"120.9833501\"},\"fare\":\"230.25\",\"distance\":\"8.55 km\",\"timestamp\":1763107212}', 'sent', '2025-11-14 16:00:12', '2025-11-14 16:00:12'),
(156, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"81\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763107212}', 'sent', '2025-11-14 16:00:12', '2025-11-14 16:00:12'),
(157, 'user', '6', '{\"type\":\"driver_rejected\",\"ride_id\":81,\"message\":\"Driver declined your booking. Admin will assign another driver shortly...\",\"reason\":\"Driver declined\",\"timestamp\":1763107250}', 'sent', '2025-11-14 16:00:50', '2025-11-14 16:00:50'),
(158, 'user', '1', '{\"type\":\"driver_rejected\",\"booking_id\":81,\"driver_id\":1,\"message\":\"Driver rejected booking #81. Booking returned to pending.\",\"reason\":\"Driver declined\",\"timestamp\":1763107250}', 'sent', '2025-11-14 16:00:50', '2025-11-14 16:00:50'),
(159, 'user', '6', '{\"type\":\"booking_rejected\",\"booking_id\":\"81\",\"message\":\"Your booking request has been rejected. Please try booking again.\",\"timestamp\":1763107275}', 'sent', '2025-11-14 16:01:15', '2025-11-14 16:01:15'),
(160, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"82\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Assassi, Cagayan, Philippines\",\"lat\":17.9022253,\"lng\":121.7721766},\"dropoff\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":14.9094432,\"lng\":120.5606322},\"fare\":7546.85,\"distance\":\"356.99 km\",\"payment_method\":\"cash\",\"timestamp\":1763107632}', 'sent', '2025-11-14 16:07:12', '2025-11-14 16:07:12'),
(161, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"82\",\"pickup\":{\"address\":\"Assassi, Cagayan, Philippines\",\"lat\":\"17.9022253\",\"lng\":\"121.7721766\"},\"dropoff\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":\"14.9094432\",\"lng\":\"120.5606322\"},\"fare\":\"7546.85\",\"distance\":\"356.99 km\",\"timestamp\":1763107646}', 'sent', '2025-11-14 16:07:26', '2025-11-14 16:07:26'),
(162, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"82\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763107646}', 'sent', '2025-11-14 16:07:26', '2025-11-14 16:07:26'),
(163, 'user', '6', '{\"type\":\"driver_rejected\",\"ride_id\":82,\"message\":\"Driver declined your booking. Admin will assign another driver shortly...\",\"reason\":\"Driver declined\",\"timestamp\":1763107670}', 'sent', '2025-11-14 16:07:50', '2025-11-14 16:07:50'),
(164, 'user', '1', '{\"type\":\"driver_rejected\",\"booking_id\":82,\"driver_id\":1,\"message\":\"Driver rejected booking #82. Booking returned to pending.\",\"reason\":\"Driver declined\",\"timestamp\":1763107670}', 'sent', '2025-11-14 16:07:50', '2025-11-14 16:07:51'),
(165, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"82\",\"pickup\":{\"address\":\"Assassi, Cagayan, Philippines\",\"lat\":\"17.9022253\",\"lng\":\"121.7721766\"},\"dropoff\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":\"14.9094432\",\"lng\":\"120.5606322\"},\"fare\":\"7546.85\",\"distance\":\"356.99 km\",\"timestamp\":1763107688}', 'sent', '2025-11-14 16:08:08', '2025-11-14 16:08:08'),
(166, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"82\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763107688}', 'sent', '2025-11-14 16:08:08', '2025-11-14 16:08:08'),
(167, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":82,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763107705}', 'sent', '2025-11-14 16:08:25', '2025-11-14 16:08:25'),
(168, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"82\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763107711}', 'sent', '2025-11-14 16:08:31', '2025-11-14 16:08:31'),
(169, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"82\",\"status\":\"completed\",\"fare\":\"7546.85\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763107716}', 'sent', '2025-11-14 16:08:36', '2025-11-14 16:08:37'),
(170, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"83\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"dropoff\":{\"address\":\"Tsakos Maritime Philippines Inc., Esteban Street, Makati, District I, Metro Manila, Philippines\",\"lat\":14.5569098,\"lng\":121.0172368},\"fare\":189.75,\"distance\":\"6.65 km\",\"payment_method\":\"cash\",\"timestamp\":1763107940}', 'sent', '2025-11-14 16:12:20', '2025-11-14 16:12:20'),
(171, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"83\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"dropoff\":{\"address\":\"Tsakos Maritime Philippines Inc., Esteban Street, Makati, District I, Metro Manila, Philippines\",\"lat\":\"14.5569098\",\"lng\":\"121.0172368\"},\"fare\":\"189.75\",\"distance\":\"6.65 km\",\"timestamp\":1763107959}', 'sent', '2025-11-14 16:12:40', '2025-11-14 16:12:40'),
(172, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"83\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763107960}', 'sent', '2025-11-14 16:12:40', '2025-11-14 16:12:40'),
(173, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":83,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763107979}', 'sent', '2025-11-14 16:12:59', '2025-11-14 16:12:59'),
(174, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"83\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763107985}', 'sent', '2025-11-14 16:13:05', '2025-11-14 16:13:05'),
(175, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"83\",\"status\":\"completed\",\"fare\":\"189.75\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763107989}', 'sent', '2025-11-14 16:13:09', '2025-11-14 16:13:09'),
(176, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"84\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Caloocan, Metro Manila, Philippines\",\"lat\":14.651348,\"lng\":120.9724002},\"dropoff\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, Bedford Street, Dasmari\\u00f1as, Cavite, Philippines\",\"lat\":14.33402235,\"lng\":120.95126220986008},\"fare\":917.5,\"distance\":\"41.30 km\",\"payment_method\":\"cash\",\"timestamp\":1763108299}', 'sent', '2025-11-14 16:18:19', '2025-11-14 16:18:19'),
(177, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"84\",\"pickup\":{\"address\":\"Caloocan, Metro Manila, Philippines\",\"lat\":\"14.6513480\",\"lng\":\"120.9724002\"},\"dropoff\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, Bedford Street, Dasmari\\u00f1as, Cavite, Philippines\",\"lat\":\"14.3340224\",\"lng\":\"120.9512622\"},\"fare\":\"917.50\",\"distance\":\"41.30 km\",\"timestamp\":1763108314}', 'sent', '2025-11-14 16:18:34', '2025-11-14 16:18:34'),
(178, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"84\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763108314}', 'sent', '2025-11-14 16:18:34', '2025-11-14 16:18:34'),
(179, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":84,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763108342}', 'sent', '2025-11-14 16:19:02', '2025-11-14 16:19:02'),
(180, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"84\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763108355}', 'sent', '2025-11-14 16:19:15', '2025-11-14 16:19:15'),
(181, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"84\",\"status\":\"completed\",\"fare\":\"917.50\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763108443}', 'sent', '2025-11-14 16:20:43', '2025-11-14 16:20:43'),
(182, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"85\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Tarlac Street, Dasmari\\u00f1as, Bagong Bayan, Cavite, Philippines\",\"lat\":14.308172521274868,\"lng\":120.97416752979365},\"dropoff\":{\"address\":\"Caloocan, Metro Manila, Philippines\",\"lat\":14.651348,\"lng\":120.9724002},\"fare\":1071.9,\"distance\":\"48.66 km\",\"payment_method\":\"cash\",\"timestamp\":1763108550}', 'sent', '2025-11-14 16:22:30', '2025-11-14 16:22:30'),
(183, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"85\",\"pickup\":{\"address\":\"Tarlac Street, Dasmari\\u00f1as, Bagong Bayan, Cavite, Philippines\",\"lat\":\"14.3081725\",\"lng\":\"120.9741675\"},\"dropoff\":{\"address\":\"Caloocan, Metro Manila, Philippines\",\"lat\":\"14.6513480\",\"lng\":\"120.9724002\"},\"fare\":\"1071.90\",\"distance\":\"48.66 km\",\"timestamp\":1763108565}', 'sent', '2025-11-14 16:22:45', '2025-11-14 16:22:45'),
(184, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"85\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763108565}', 'sent', '2025-11-14 16:22:45', '2025-11-14 16:22:45'),
(185, 'user', '6', '{\"type\":\"driver_rejected\",\"ride_id\":85,\"message\":\"Driver declined your booking. Admin will assign another driver shortly...\",\"reason\":\"Driver declined\",\"timestamp\":1763108718}', 'sent', '2025-11-14 16:25:18', '2025-11-14 16:25:18'),
(186, 'user', '1', '{\"type\":\"driver_rejected\",\"booking_id\":85,\"driver_id\":1,\"message\":\"Driver rejected booking #85. Booking returned to pending.\",\"reason\":\"Driver declined\",\"timestamp\":1763108718}', 'sent', '2025-11-14 16:25:18', '2025-11-14 16:25:19'),
(187, 'user', '6', '{\"type\":\"booking_rejected\",\"booking_id\":\"85\",\"message\":\"Your booking request has been rejected. Please try booking again.\",\"timestamp\":1763108745}', 'sent', '2025-11-14 16:25:45', '2025-11-14 16:25:45'),
(188, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"86\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":14.9094432,\"lng\":120.5606322},\"dropoff\":{\"address\":\"Ashford Street, Muntinlupa, Muntinlupa District 2, Metro Manila, Philippines\",\"lat\":14.438575303895577,\"lng\":121.03721718676617},\"fare\":2372.25,\"distance\":\"110.55 km\",\"payment_method\":\"cash\",\"timestamp\":1763108765}', 'sent', '2025-11-14 16:26:05', '2025-11-14 16:26:05'),
(189, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"86\",\"pickup\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":\"14.9094432\",\"lng\":\"120.5606322\"},\"dropoff\":{\"address\":\"Ashford Street, Muntinlupa, Muntinlupa District 2, Metro Manila, Philippines\",\"lat\":\"14.4385753\",\"lng\":\"121.0372172\"},\"fare\":\"2372.25\",\"distance\":\"110.55 km\",\"timestamp\":1763108774}', 'sent', '2025-11-14 16:26:14', '2025-11-14 16:26:14'),
(190, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"86\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763108774}', 'sent', '2025-11-14 16:26:14', '2025-11-14 16:26:15'),
(191, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":86,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763108802}', 'sent', '2025-11-14 16:26:42', '2025-11-14 16:26:42'),
(192, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"86\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763108819}', 'sent', '2025-11-14 16:26:59', '2025-11-14 16:26:59'),
(193, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"86\",\"status\":\"completed\",\"fare\":\"2372.25\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763108824}', 'sent', '2025-11-14 16:27:04', '2025-11-14 16:27:04'),
(194, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"87\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Dasmari\\u00f1as, Makati, District I, Metro Manila, Philippines\",\"lat\":14.5369453,\"lng\":121.0302771},\"dropoff\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":14.9094432,\"lng\":120.5606322},\"fare\":2137,\"distance\":\"99.40 km\",\"payment_method\":\"cash\",\"timestamp\":1763109839}', 'sent', '2025-11-14 16:43:59', '2025-11-14 16:43:59'),
(195, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"87\",\"pickup\":{\"address\":\"Dasmari\\u00f1as, Makati, District I, Metro Manila, Philippines\",\"lat\":\"14.5369453\",\"lng\":\"121.0302771\"},\"dropoff\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":\"14.9094432\",\"lng\":\"120.5606322\"},\"fare\":\"2137.00\",\"distance\":\"99.40 km\",\"timestamp\":1763109846}', 'sent', '2025-11-14 16:44:06', '2025-11-14 16:44:06'),
(196, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"87\",\"driver_id\":\"6\",\"driver_name\":\"asdas asdasdads\",\"driver_phone\":\"09231231231\",\"tricycle_number\":\"TRY-006\",\"timestamp\":1763109846}', 'sent', '2025-11-14 16:44:06', '2025-11-14 16:44:06'),
(197, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":87,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763109868}', 'sent', '2025-11-14 16:44:28', '2025-11-14 16:44:28'),
(198, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"87\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763109873}', 'sent', '2025-11-14 16:44:34', '2025-11-14 16:44:34'),
(199, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"87\",\"status\":\"completed\",\"fare\":\"2137.00\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763109882}', 'sent', '2025-11-14 16:44:42', '2025-11-14 16:44:42'),
(200, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"88\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, Bedford Street, Dasmari\\u00f1as, Cavite, Philippines\",\"lat\":14.33402235,\"lng\":120.95126220986008},\"dropoff\":{\"address\":\"Manila, Metro Manila, Philippines\",\"lat\":14.5904492,\"lng\":120.9803621},\"fare\":741.05,\"distance\":\"32.87 km\",\"payment_method\":\"cash\",\"timestamp\":1763110270}', 'sent', '2025-11-14 16:51:10', '2025-11-14 16:51:10'),
(201, 'user', '7', '{\"type\":\"booking_assigned\",\"booking_id\":\"88\",\"pickup\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, Bedford Street, Dasmari\\u00f1as, Cavite, Philippines\",\"lat\":\"14.3340224\",\"lng\":\"120.9512622\"},\"dropoff\":{\"address\":\"Manila, Metro Manila, Philippines\",\"lat\":\"14.5904492\",\"lng\":\"120.9803621\"},\"fare\":\"741.05\",\"distance\":\"32.87 km\",\"timestamp\":1763110299}', 'sent', '2025-11-14 16:51:39', '2025-11-14 16:51:39'),
(202, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"88\",\"driver_id\":\"7\",\"driver_name\":\"Jade Orense\",\"driver_phone\":\"09123123123\",\"tricycle_number\":\"TRY-007\",\"timestamp\":1763110299}', 'sent', '2025-11-14 16:51:39', '2025-11-14 16:51:39'),
(203, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":88,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763110339}', 'sent', '2025-11-14 16:52:19', '2025-11-14 16:52:19'),
(204, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"88\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763110349}', 'sent', '2025-11-14 16:52:29', '2025-11-14 16:52:29'),
(205, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"88\",\"status\":\"completed\",\"fare\":\"741.05\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763110357}', 'sent', '2025-11-14 16:52:37', '2025-11-14 16:52:37'),
(206, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"89\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"dropoff\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":14.9094432,\"lng\":120.5606322},\"fare\":1828.95,\"distance\":\"84.73 km\",\"payment_method\":\"cash\",\"timestamp\":1763130164}', 'sent', '2025-11-14 22:22:44', '2025-11-14 22:22:44'),
(207, 'user', '7', '{\"type\":\"booking_assigned\",\"booking_id\":\"89\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"dropoff\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":\"14.9094432\",\"lng\":\"120.5606322\"},\"fare\":\"1828.95\",\"distance\":\"84.73 km\",\"timestamp\":1763130190}', 'sent', '2025-11-14 22:23:10', '2025-11-14 22:23:10'),
(208, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"89\",\"driver_id\":\"7\",\"driver_name\":\"Jade Orense\",\"driver_phone\":\"09123123123\",\"tricycle_number\":\"TRY-007\",\"timestamp\":1763130190}', 'sent', '2025-11-14 22:23:10', '2025-11-14 22:23:10'),
(209, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":89,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763130246}', 'sent', '2025-11-14 22:24:06', '2025-11-14 22:24:07'),
(210, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"89\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763130256}', 'sent', '2025-11-14 22:24:16', '2025-11-14 22:24:17'),
(211, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"89\",\"status\":\"completed\",\"fare\":\"1828.95\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763130260}', 'sent', '2025-11-14 22:24:20', '2025-11-14 22:24:20'),
(212, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"90\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Caloocan, NLEX Harbor Link Segment 10, Malabon, Metro Manila, Philippines\",\"lat\":14.6569248,\"lng\":120.9738786},\"dropoff\":{\"address\":\"CAVITEX\\u2013C-5 Link, Pasay, Zone 20, Metro Manila, Philippines\",\"lat\":14.5074499,\"lng\":121.02640385},\"fare\":548.1,\"distance\":\"23.74 km\",\"payment_method\":\"cash\",\"timestamp\":1763130592}', 'sent', '2025-11-14 22:29:52', '2025-11-14 22:29:52'),
(213, 'user', '7', '{\"type\":\"booking_assigned\",\"booking_id\":\"90\",\"pickup\":{\"address\":\"Caloocan, NLEX Harbor Link Segment 10, Malabon, Metro Manila, Philippines\",\"lat\":\"14.6569248\",\"lng\":\"120.9738786\"},\"dropoff\":{\"address\":\"CAVITEX\\u2013C-5 Link, Pasay, Zone 20, Metro Manila, Philippines\",\"lat\":\"14.5074499\",\"lng\":\"121.0264039\"},\"fare\":\"548.10\",\"distance\":\"23.74 km\",\"timestamp\":1763130607}', 'sent', '2025-11-14 22:30:07', '2025-11-14 22:30:07'),
(214, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"90\",\"driver_id\":\"7\",\"driver_name\":\"Jade Orense\",\"driver_phone\":\"09123123123\",\"tricycle_number\":\"TRY-007\",\"timestamp\":1763130607}', 'sent', '2025-11-14 22:30:07', '2025-11-14 22:30:08'),
(215, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":90,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763130620}', 'sent', '2025-11-14 22:30:20', '2025-11-14 22:30:20'),
(216, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"90\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763130624}', 'sent', '2025-11-14 22:30:24', '2025-11-14 22:30:24'),
(217, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"90\",\"status\":\"completed\",\"fare\":\"548.10\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763130629}', 'sent', '2025-11-14 22:30:29', '2025-11-14 22:30:29'),
(218, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"91\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Sases Basketball Court, Perlita Street, Manila, San Andres Bukid, Metro Manila, Philippines\",\"lat\":14.57171015,\"lng\":121.00070135},\"dropoff\":{\"address\":\"Asian Development Bank, ADB Avenue, Mandaluyong, Metro Manila, Philippines\",\"lat\":14.58807565,\"lng\":121.05830089349516},\"fare\":271.65,\"distance\":\"10.51 km\",\"payment_method\":\"cash\",\"timestamp\":1763130798}', 'sent', '2025-11-14 22:33:18', '2025-11-14 22:33:19'),
(219, 'user', '7', '{\"type\":\"booking_assigned\",\"booking_id\":\"91\",\"pickup\":{\"address\":\"Sases Basketball Court, Perlita Street, Manila, San Andres Bukid, Metro Manila, Philippines\",\"lat\":\"14.5717102\",\"lng\":\"121.0007014\"},\"dropoff\":{\"address\":\"Asian Development Bank, ADB Avenue, Mandaluyong, Metro Manila, Philippines\",\"lat\":\"14.5880757\",\"lng\":\"121.0583009\"},\"fare\":\"271.65\",\"distance\":\"10.51 km\",\"timestamp\":1763130817}', 'sent', '2025-11-14 22:33:37', '2025-11-14 22:33:37'),
(220, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"91\",\"driver_id\":\"7\",\"driver_name\":\"Jade Orense\",\"driver_phone\":\"09123123123\",\"tricycle_number\":\"TRY-007\",\"timestamp\":1763130817}', 'sent', '2025-11-14 22:33:37', '2025-11-14 22:33:38'),
(221, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":91,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763130823}', 'sent', '2025-11-14 22:33:43', '2025-11-14 22:33:43'),
(222, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"91\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763130828}', 'sent', '2025-11-14 22:33:48', '2025-11-14 22:33:48'),
(223, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"91\",\"status\":\"completed\",\"fare\":\"271.65\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763130834}', 'sent', '2025-11-14 22:33:54', '2025-11-14 22:33:54'),
(224, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"92\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Bulacan Street, Manila, Second District, Metro Manila, Philippines\",\"lat\":14.6271945,\"lng\":120.97705540000001},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"fare\":98.5,\"distance\":\"2.30 km\",\"payment_method\":\"cash\",\"timestamp\":1763131037}', 'sent', '2025-11-14 22:37:17', '2025-11-14 22:37:17'),
(225, 'user', '7', '{\"type\":\"booking_assigned\",\"booking_id\":\"92\",\"pickup\":{\"address\":\"Bulacan Street, Manila, Second District, Metro Manila, Philippines\",\"lat\":\"14.6271945\",\"lng\":\"120.9770554\"},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"fare\":\"98.50\",\"distance\":\"2.30 km\",\"timestamp\":1763131053}', 'sent', '2025-11-14 22:37:33', '2025-11-14 22:37:34'),
(226, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"92\",\"driver_id\":\"7\",\"driver_name\":\"Jade Orense\",\"driver_phone\":\"09123123123\",\"tricycle_number\":\"TRY-007\",\"timestamp\":1763131053}', 'sent', '2025-11-14 22:37:33', '2025-11-14 22:37:34'),
(227, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":92,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763131061}', 'sent', '2025-11-14 22:37:41', '2025-11-14 22:37:41'),
(228, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"92\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763131080}', 'sent', '2025-11-14 22:38:00', '2025-11-14 22:38:00'),
(229, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"92\",\"status\":\"completed\",\"fare\":\"98.50\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763131089}', 'sent', '2025-11-14 22:38:09', '2025-11-14 22:38:09'),
(230, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"93\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Dakila Bridge, MacArthur Highway, Malolos, Sumapang Matanda, Bulacan, Philippines\",\"lat\":14.8513249,\"lng\":120.81796245},\"dropoff\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, Bedford Street, Dasmari\\u00f1as, Cavite, Philippines\",\"lat\":14.33402235,\"lng\":120.95126220986008},\"fare\":1812.35,\"distance\":\"83.89 km\",\"payment_method\":\"cash\",\"timestamp\":1763131350}', 'sent', '2025-11-14 22:42:30', '2025-11-14 22:42:30'),
(231, 'user', '7', '{\"type\":\"booking_assigned\",\"booking_id\":\"93\",\"pickup\":{\"address\":\"Dakila Bridge, MacArthur Highway, Malolos, Sumapang Matanda, Bulacan, Philippines\",\"lat\":\"14.8513249\",\"lng\":\"120.8179625\"},\"dropoff\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, Bedford Street, Dasmari\\u00f1as, Cavite, Philippines\",\"lat\":\"14.3340224\",\"lng\":\"120.9512622\"},\"fare\":\"1812.35\",\"distance\":\"83.89 km\",\"timestamp\":1763131380}', 'sent', '2025-11-14 22:43:00', '2025-11-14 22:43:00'),
(232, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"93\",\"driver_id\":\"7\",\"driver_name\":\"Jade Orense\",\"driver_phone\":\"09123123123\",\"tricycle_number\":\"TRY-007\",\"timestamp\":1763131380}', 'sent', '2025-11-14 22:43:00', '2025-11-14 22:43:01'),
(233, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":93,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763131389}', 'sent', '2025-11-14 22:43:09', '2025-11-14 22:43:09'),
(234, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"93\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763131392}', 'sent', '2025-11-14 22:43:12', '2025-11-14 22:43:12'),
(235, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"93\",\"status\":\"completed\",\"fare\":\"1812.35\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763131405}', 'sent', '2025-11-14 22:43:25', '2025-11-14 22:43:25'),
(236, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"94\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Asgard Corrogated Box Manufacturing Corporation, Pablo dela Cruz Street, Quezon City, 5th District, Metro Manila, Philippines\",\"lat\":14.7118418,\"lng\":121.02671920995225},\"dropoff\":{\"address\":\"Valenzuela Linear Park, A. Bonifacio Street, Makati, District I, Metro Manila, Philippines\",\"lat\":14.573973800000001,\"lng\":121.02552582556905},\"fare\":507,\"distance\":\"21.80 km\",\"payment_method\":\"cash\",\"timestamp\":1763131743}', 'sent', '2025-11-14 22:49:03', '2025-11-14 22:49:03'),
(237, 'user', '7', '{\"type\":\"booking_assigned\",\"booking_id\":\"94\",\"pickup\":{\"address\":\"Asgard Corrogated Box Manufacturing Corporation, Pablo dela Cruz Street, Quezon City, 5th District, Metro Manila, Philippines\",\"lat\":\"14.7118418\",\"lng\":\"121.0267192\"},\"dropoff\":{\"address\":\"Valenzuela Linear Park, A. Bonifacio Street, Makati, District I, Metro Manila, Philippines\",\"lat\":\"14.5739738\",\"lng\":\"121.0255258\"},\"fare\":\"507.00\",\"distance\":\"21.80 km\",\"timestamp\":1763131759}', 'sent', '2025-11-14 22:49:19', '2025-11-14 22:49:19'),
(238, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"94\",\"driver_id\":\"7\",\"driver_name\":\"Jade Orense\",\"driver_phone\":\"09123123123\",\"tricycle_number\":\"TRY-007\",\"timestamp\":1763131759}', 'sent', '2025-11-14 22:49:19', '2025-11-14 22:49:19'),
(239, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":94,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763131779}', 'sent', '2025-11-14 22:49:39', '2025-11-14 22:49:39'),
(240, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"94\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763131785}', 'sent', '2025-11-14 22:49:45', '2025-11-14 22:49:45'),
(241, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"94\",\"status\":\"completed\",\"fare\":\"507.00\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763131794}', 'sent', '2025-11-14 22:49:54', '2025-11-14 22:49:54'),
(242, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"95\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"dropoff\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":14.9094432,\"lng\":120.5606322},\"fare\":1828.95,\"distance\":\"84.73 km\",\"payment_method\":\"cash\",\"timestamp\":1763131862}', 'sent', '2025-11-14 22:51:02', '2025-11-14 22:51:02'),
(243, 'user', '7', '{\"type\":\"booking_assigned\",\"booking_id\":\"95\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"dropoff\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":\"14.9094432\",\"lng\":\"120.5606322\"},\"fare\":\"1828.95\",\"distance\":\"84.73 km\",\"timestamp\":1763131940}', 'sent', '2025-11-14 22:52:20', '2025-11-14 22:52:20'),
(244, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"95\",\"driver_id\":\"7\",\"driver_name\":\"Jade Orense\",\"driver_phone\":\"09123123123\",\"tricycle_number\":\"TRY-007\",\"timestamp\":1763131940}', 'sent', '2025-11-14 22:52:20', '2025-11-14 22:52:20'),
(245, 'user', '6', '{\"type\":\"driver_rejected\",\"ride_id\":95,\"message\":\"Driver declined your booking. Admin will assign another driver shortly...\",\"reason\":\"Driver declined\",\"timestamp\":1763131945}', 'sent', '2025-11-14 22:52:25', '2025-11-14 22:52:25'),
(246, 'user', '1', '{\"type\":\"driver_rejected\",\"booking_id\":95,\"driver_id\":7,\"message\":\"Driver rejected booking #95. Booking returned to pending.\",\"reason\":\"Driver declined\",\"timestamp\":1763131945}', 'sent', '2025-11-14 22:52:25', '2025-11-14 22:52:25'),
(247, 'user', '7', '{\"type\":\"booking_assigned\",\"booking_id\":\"95\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"dropoff\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":\"14.9094432\",\"lng\":\"120.5606322\"},\"fare\":\"1828.95\",\"distance\":\"84.73 km\",\"timestamp\":1763131964}', 'sent', '2025-11-14 22:52:44', '2025-11-14 22:52:44'),
(248, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"95\",\"driver_id\":\"7\",\"driver_name\":\"Jade Orense\",\"driver_phone\":\"09123123123\",\"tricycle_number\":\"TRY-007\",\"timestamp\":1763131964}', 'sent', '2025-11-14 22:52:44', '2025-11-14 22:52:44'),
(249, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":95,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763131969}', 'sent', '2025-11-14 22:52:49', '2025-11-14 22:52:49'),
(250, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"95\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763131975}', 'sent', '2025-11-14 22:52:55', '2025-11-14 22:52:55'),
(251, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"95\",\"status\":\"completed\",\"fare\":\"1828.95\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763131980}', 'sent', '2025-11-14 22:53:00', '2025-11-14 22:53:00'),
(252, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"96\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Xanderella, Locsin Street, Bacolod, Bacolod-1, Negros Island Region, Philippines\",\"lat\":10.6700618,\"lng\":122.9492528},\"dropoff\":{\"address\":\"Caloocan, Metro Manila, Philippines\",\"lat\":14.651348,\"lng\":120.9724002},\"fare\":15160.5,\"distance\":\"719.50 km\",\"payment_method\":\"cash\",\"timestamp\":1763132386}', 'sent', '2025-11-14 22:59:46', '2025-11-14 22:59:47'),
(253, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"97\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Chanarian, Basco, Batanes, Philippines\",\"lat\":20.433883,\"lng\":121.9599427},\"dropoff\":{\"address\":\"Bakkaan, Banguingui, Sulu, Philippines\",\"lat\":5.9948121,\"lng\":121.5233654},\"fare\":33781.6,\"distance\":\"1606.24 km\",\"payment_method\":\"cash\",\"timestamp\":1763132821}', 'sent', '2025-11-14 23:07:01', '2025-11-14 23:07:01'),
(254, 'role', 'admin', '{\"type\":\"booking_cancelled\",\"booking_id\":\"97\",\"status\":\"cancelled\",\"cancelled_by\":\"user\",\"timestamp\":1763132830}', 'sent', '2025-11-14 23:07:10', '2025-11-14 23:07:10'),
(255, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"98\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"dropoff\":{\"address\":\"UAP United Architects of the Philippines, Scout Rallos Street, Quezon City, Diliman, Metro Manila, Philippines\",\"lat\":14.6344568,\"lng\":121.033952},\"fare\":202,\"distance\":\"7.20 km\",\"payment_method\":\"cash\",\"timestamp\":1763132875}', 'sent', '2025-11-14 23:07:55', '2025-11-14 23:07:55'),
(256, 'user', '6', '{\"type\":\"booking_rejected\",\"booking_id\":\"98\",\"message\":\"Your booking request has been rejected. Please try booking again.\",\"timestamp\":1763133016}', 'sent', '2025-11-14 23:10:16', '2025-11-14 23:10:16'),
(257, 'role', 'admin', '{\"type\":\"booking_rejected\",\"booking_id\":\"98\",\"status\":\"rejected\",\"timestamp\":1763133016}', 'sent', '2025-11-14 23:10:16', '2025-11-14 23:10:16');
INSERT INTO `realtime_notifications` (`id`, `target_type`, `target_id`, `data`, `status`, `created_at`, `sent_at`) VALUES
(258, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"99\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Asdum, Camarines Norte, Philippines\",\"lat\":14.1193515,\"lng\":122.8703828},\"dropoff\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":14.9094432,\"lng\":120.5606322},\"fare\":5587.5,\"distance\":\"263.70 km\",\"payment_method\":\"cash\",\"timestamp\":1763133773}', 'sent', '2025-11-14 23:22:53', '2025-11-14 23:22:53'),
(259, 'role', 'admin', '{\"type\":\"booking_cancelled\",\"booking_id\":\"99\",\"status\":\"cancelled\",\"cancelled_by\":\"user\",\"timestamp\":1763133800}', 'sent', '2025-11-14 23:23:20', '2025-11-14 23:23:20'),
(260, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"100\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"ASF Laundry Shop, J. P. Rizal Street, Marikina, District I, Metro Manila, Philippines\",\"lat\":14.6445615,\"lng\":121.0958872},\"dropoff\":{\"address\":\"Asian Development Bank, ADB Avenue, Mandaluyong, Metro Manila, Philippines\",\"lat\":14.58807565,\"lng\":121.05830089349516},\"fare\":261.2,\"distance\":\"10.08 km\",\"payment_method\":\"cash\",\"timestamp\":1763133897}', 'sent', '2025-11-14 23:24:57', '2025-11-14 23:24:57'),
(261, 'role', 'admin', '{\"type\":\"booking_cancelled\",\"booking_id\":\"100\",\"status\":\"cancelled\",\"cancelled_by\":\"user\",\"timestamp\":1763133999}', 'sent', '2025-11-14 23:26:39', '2025-11-14 23:26:39'),
(262, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"101\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":14.9094432,\"lng\":120.5606322},\"dropoff\":{\"address\":\"GSA Academic Regalia, Ipil Street, Marikina, District II, Metro Manila, Philippines\",\"lat\":14.6488451,\"lng\":121.1201487},\"fare\":2050.3,\"distance\":\"95.22 km\",\"payment_method\":\"cash\",\"timestamp\":1763134018}', 'sent', '2025-11-14 23:26:58', '2025-11-14 23:26:58'),
(263, 'role', 'admin', '{\"type\":\"booking_cancelled\",\"booking_id\":\"101\",\"status\":\"cancelled\",\"cancelled_by\":\"user\",\"timestamp\":1763134064}', 'sent', '2025-11-14 23:27:44', '2025-11-14 23:27:44'),
(264, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"102\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"UTS Building, Taft Avenue, Manila, Malate, Metro Manila, Philippines\",\"lat\":14.57557795,\"lng\":120.98900627257922},\"dropoff\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":14.9094432,\"lng\":120.5606322},\"fare\":1934.7,\"distance\":\"89.78 km\",\"payment_method\":\"cash\",\"timestamp\":1763134083}', 'sent', '2025-11-14 23:28:03', '2025-11-14 23:28:04'),
(265, 'role', 'admin', '{\"type\":\"booking_cancelled\",\"booking_id\":\"102\",\"status\":\"cancelled\",\"cancelled_by\":\"user\",\"timestamp\":1763134154}', 'sent', '2025-11-14 23:29:14', '2025-11-14 23:29:14'),
(266, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"103\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines\",\"lat\":14.9094432,\"lng\":120.5606322},\"dropoff\":{\"address\":\"Gasdam, Mabalacat, Pampanga, Philippines\",\"lat\":15.1880948,\"lng\":120.5839502},\"fare\":702.35,\"distance\":\"31.09 km\",\"payment_method\":\"cash\",\"timestamp\":1763134171}', 'sent', '2025-11-14 23:29:31', '2025-11-14 23:29:32'),
(267, 'role', 'admin', '{\"type\":\"booking_cancelled\",\"booking_id\":\"103\",\"status\":\"cancelled\",\"cancelled_by\":\"user\",\"timestamp\":1763134200}', 'sent', '2025-11-14 23:30:00', '2025-11-14 23:30:00'),
(268, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"104\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"dropoff\":{\"address\":\"C. M. Recto Avenue, Divisoria, Tondo, First District, Manila, Capital District, Metro Manila, 1012, Philippines\",\"lat\":14.6040106,\"lng\":120.968591},\"fare\":111.35,\"distance\":\"2.89 km\",\"payment_method\":\"cash\",\"timestamp\":1763480334}', 'sent', '2025-11-18 23:38:54', '2025-11-18 23:38:54'),
(269, 'user', '7', '{\"type\":\"booking_assigned\",\"booking_id\":\"104\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"dropoff\":{\"address\":\"C. M. Recto Avenue, Divisoria, Tondo, First District, Manila, Capital District, Metro Manila, 1012, Philippines\",\"lat\":\"14.6040106\",\"lng\":\"120.9685910\"},\"fare\":\"111.35\",\"distance\":\"2.89 km\",\"timestamp\":1763480354}', 'sent', '2025-11-18 23:39:14', '2025-11-18 23:39:14'),
(270, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"104\",\"driver_id\":\"7\",\"driver_name\":\"Jade Orense\",\"driver_phone\":\"09123123123\",\"tricycle_number\":\"TRY-007\",\"timestamp\":1763480354}', 'sent', '2025-11-18 23:39:14', '2025-11-18 23:39:14'),
(271, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":104,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763480395}', 'sent', '2025-11-18 23:39:55', '2025-11-18 23:39:56'),
(272, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"104\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763480408}', 'sent', '2025-11-18 23:40:08', '2025-11-18 23:40:08'),
(273, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"104\",\"status\":\"completed\",\"fare\":\"111.35\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763480416}', 'sent', '2025-11-18 23:40:16', '2025-11-18 23:40:17'),
(274, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"105\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, Bedford Street, Dasmari\\u00f1as, Cavite, Philippines\",\"lat\":14.33402235,\"lng\":120.95126220986008},\"dropoff\":{\"address\":\"Sta. Cristina, Las Pi\\u00f1as, 1st District, Metro Manila, Philippines\",\"lat\":14.452,\"lng\":120.979591},\"fare\":455.95,\"distance\":\"19.33 km\",\"payment_method\":\"cash\",\"timestamp\":1763481349}', 'sent', '2025-11-18 23:55:49', '2025-11-18 23:55:49'),
(275, 'user', '11', '{\"type\":\"booking_assigned\",\"booking_id\":\"105\",\"pickup\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, Bedford Street, Dasmari\\u00f1as, Cavite, Philippines\",\"lat\":\"14.3340224\",\"lng\":\"120.9512622\"},\"dropoff\":{\"address\":\"Sta. Cristina, Las Pi\\u00f1as, 1st District, Metro Manila, Philippines\",\"lat\":\"14.4520000\",\"lng\":\"120.9795910\"},\"fare\":\"455.95\",\"distance\":\"19.33 km\",\"timestamp\":1763481358}', 'sent', '2025-11-18 23:55:58', '2025-11-18 23:55:58'),
(276, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"105\",\"driver_id\":\"11\",\"driver_name\":\"Jadeski Orense\",\"driver_phone\":\"09345463452\",\"tricycle_number\":\"TRY-009\",\"timestamp\":1763481358}', 'sent', '2025-11-18 23:55:58', '2025-11-18 23:55:58'),
(277, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":105,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763481387}', 'sent', '2025-11-18 23:56:27', '2025-11-18 23:56:27'),
(278, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"105\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763481392}', 'sent', '2025-11-18 23:56:32', '2025-11-18 23:56:33'),
(279, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"105\",\"status\":\"completed\",\"fare\":\"455.95\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763481401}', 'sent', '2025-11-18 23:56:41', '2025-11-18 23:56:41'),
(280, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"106\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"dropoff\":{\"address\":\"C. M. Recto Avenue, Divisoria, Tondo, First District, Manila, Capital District, Metro Manila, 1012, Philippines\",\"lat\":14.6040106,\"lng\":120.968591},\"fare\":111.35,\"distance\":\"2.89 km\",\"payment_method\":\"cash\",\"timestamp\":1763481520}', 'sent', '2025-11-18 23:58:40', '2025-11-18 23:58:40'),
(281, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"106\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"dropoff\":{\"address\":\"C. M. Recto Avenue, Divisoria, Tondo, First District, Manila, Capital District, Metro Manila, 1012, Philippines\",\"lat\":\"14.6040106\",\"lng\":\"120.9685910\"},\"fare\":\"111.35\",\"distance\":\"2.89 km\",\"timestamp\":1763481547}', 'sent', '2025-11-18 23:59:07', '2025-11-18 23:59:07'),
(282, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"106\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763481547}', 'sent', '2025-11-18 23:59:07', '2025-11-18 23:59:07'),
(283, 'user', '6', '{\"type\":\"driver_rejected\",\"ride_id\":106,\"message\":\"Driver declined your booking. Admin will assign another driver shortly...\",\"reason\":\"Driver declined\",\"timestamp\":1763481560}', 'sent', '2025-11-18 23:59:20', '2025-11-18 23:59:20'),
(284, 'user', '1', '{\"type\":\"driver_rejected\",\"booking_id\":106,\"driver_id\":1,\"message\":\"Driver rejected booking #106. Booking returned to pending.\",\"reason\":\"Driver declined\",\"timestamp\":1763481560}', 'sent', '2025-11-18 23:59:20', '2025-11-18 23:59:20'),
(285, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"106\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"dropoff\":{\"address\":\"C. M. Recto Avenue, Divisoria, Tondo, First District, Manila, Capital District, Metro Manila, 1012, Philippines\",\"lat\":\"14.6040106\",\"lng\":\"120.9685910\"},\"fare\":\"111.35\",\"distance\":\"2.89 km\",\"timestamp\":1763481570}', 'sent', '2025-11-18 23:59:30', '2025-11-18 23:59:30'),
(286, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"106\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763481570}', 'sent', '2025-11-18 23:59:30', '2025-11-18 23:59:30'),
(287, 'user', '6', '{\"type\":\"driver_rejected\",\"ride_id\":106,\"message\":\"Driver declined your booking. Admin will assign another driver shortly...\",\"reason\":\"Driver declined\",\"timestamp\":1763481621}', 'sent', '2025-11-19 00:00:21', '2025-11-19 00:00:21'),
(288, 'user', '1', '{\"type\":\"driver_rejected\",\"booking_id\":106,\"driver_id\":1,\"message\":\"Driver rejected booking #106. Booking returned to pending.\",\"reason\":\"Driver declined\",\"timestamp\":1763481621}', 'sent', '2025-11-19 00:00:21', '2025-11-19 00:00:21'),
(289, 'user', '6', '{\"type\":\"booking_rejected\",\"booking_id\":\"106\",\"message\":\"Your booking request has been rejected. Please try booking again.\",\"timestamp\":1763481789}', 'sent', '2025-11-19 00:03:09', '2025-11-19 00:03:09'),
(290, 'role', 'admin', '{\"type\":\"booking_rejected\",\"booking_id\":\"106\",\"status\":\"rejected\",\"timestamp\":1763481789}', 'sent', '2025-11-19 00:03:09', '2025-11-19 00:03:09'),
(291, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"107\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"SYS Marketing Appliances, P. Zamora Street, 16, Downtown, Tacloban, Eastern Visayas, 6500, Philippines\",\"lat\":11.2451388,\"lng\":125.0018515},\"dropoff\":{\"address\":\"Sigma, Capiz, Western Visayas, 5816, Philippines\",\"lat\":11.4211672,\"lng\":122.6658275},\"fare\":12381.7,\"distance\":\"587.18 km\",\"payment_method\":\"cash\",\"timestamp\":1763482096}', 'sent', '2025-11-19 00:08:16', '2025-11-19 00:08:16'),
(292, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"107\",\"pickup\":{\"address\":\"SYS Marketing Appliances, P. Zamora Street, 16, Downtown, Tacloban, Eastern Visayas, 6500, Philippines\",\"lat\":\"11.2451388\",\"lng\":\"125.0018515\"},\"dropoff\":{\"address\":\"Sigma, Capiz, Western Visayas, 5816, Philippines\",\"lat\":\"11.4211672\",\"lng\":\"122.6658275\"},\"fare\":\"12381.70\",\"distance\":\"587.18 km\",\"timestamp\":1763482106}', 'sent', '2025-11-19 00:08:26', '2025-11-19 00:08:26'),
(293, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"107\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763482106}', 'sent', '2025-11-19 00:08:26', '2025-11-19 00:08:26'),
(294, 'user', '6', '{\"type\":\"driver_rejected\",\"ride_id\":107,\"message\":\"Driver declined your booking. Admin will assign another driver shortly...\",\"reason\":\"Driver declined\",\"timestamp\":1763482128}', 'sent', '2025-11-19 00:08:48', '2025-11-19 00:08:49'),
(295, 'user', '1', '{\"type\":\"driver_rejected\",\"booking_id\":107,\"driver_id\":1,\"message\":\"Driver rejected booking #107. Booking returned to pending.\",\"reason\":\"Driver declined\",\"timestamp\":1763482128}', 'sent', '2025-11-19 00:08:48', '2025-11-19 00:08:49'),
(296, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"107\",\"pickup\":{\"address\":\"SYS Marketing Appliances, P. Zamora Street, 16, Downtown, Tacloban, Eastern Visayas, 6500, Philippines\",\"lat\":\"11.2451388\",\"lng\":\"125.0018515\"},\"dropoff\":{\"address\":\"Sigma, Capiz, Western Visayas, 5816, Philippines\",\"lat\":\"11.4211672\",\"lng\":\"122.6658275\"},\"fare\":\"12381.70\",\"distance\":\"587.18 km\",\"timestamp\":1763482136}', 'sent', '2025-11-19 00:08:56', '2025-11-19 00:08:56'),
(297, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"107\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763482136}', 'sent', '2025-11-19 00:08:56', '2025-11-19 00:08:57'),
(298, 'user', '6', '{\"type\":\"driver_rejected\",\"ride_id\":107,\"message\":\"Driver declined your booking. Admin will assign another driver shortly...\",\"reason\":\"Driver declined\",\"timestamp\":1763482575}', 'sent', '2025-11-19 00:16:15', '2025-11-19 00:16:16'),
(299, 'user', '1', '{\"type\":\"driver_rejected\",\"booking_id\":107,\"driver_id\":1,\"message\":\"Driver rejected booking #107. Booking returned to pending.\",\"reason\":\"Driver declined\",\"timestamp\":1763482576}', 'sent', '2025-11-19 00:16:16', '2025-11-19 00:16:16'),
(300, 'user', '6', '{\"type\":\"booking_rejected\",\"booking_id\":\"107\",\"message\":\"Your booking request has been rejected. Please try booking again.\",\"timestamp\":1763482582}', 'sent', '2025-11-19 00:16:22', '2025-11-19 00:16:22'),
(301, 'role', 'admin', '{\"type\":\"booking_rejected\",\"booking_id\":\"107\",\"status\":\"rejected\",\"timestamp\":1763482582}', 'sent', '2025-11-19 00:16:22', '2025-11-19 00:16:23'),
(302, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"108\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, San Manuel Street, San Manuel 2, DBB-1, Dasmari\\u00f1as, Cavite, Calabarzon, 4114, Philippines\",\"lat\":14.3340224,\"lng\":120.9512622},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"fare\":792.4,\"distance\":\"35.36 km\",\"payment_method\":\"cash\",\"timestamp\":1763482622}', 'sent', '2025-11-19 00:17:02', '2025-11-19 00:17:02'),
(303, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"108\",\"pickup\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, San Manuel Street, San Manuel 2, DBB-1, Dasmari\\u00f1as, Cavite, Calabarzon, 4114, Philippines\",\"lat\":\"14.3340224\",\"lng\":\"120.9512622\"},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"fare\":\"792.40\",\"distance\":\"35.36 km\",\"timestamp\":1763482626}', 'sent', '2025-11-19 00:17:06', '2025-11-19 00:17:06'),
(304, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"108\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763482626}', 'sent', '2025-11-19 00:17:06', '2025-11-19 00:17:06'),
(305, 'user', '6', '{\"type\":\"driver_rejected\",\"ride_id\":108,\"message\":\"Driver declined your booking. Admin will assign another driver shortly...\",\"reason\":\"Driver declined\",\"timestamp\":1763482632}', 'sent', '2025-11-19 00:17:12', '2025-11-19 00:17:12'),
(306, 'user', '1', '{\"type\":\"driver_rejected\",\"booking_id\":108,\"driver_id\":1,\"message\":\"Driver rejected booking #108. Booking returned to pending.\",\"reason\":\"Driver declined\",\"timestamp\":1763482632}', 'sent', '2025-11-19 00:17:12', '2025-11-19 00:17:13'),
(307, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"108\",\"pickup\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, San Manuel Street, San Manuel 2, DBB-1, Dasmari\\u00f1as, Cavite, Calabarzon, 4114, Philippines\",\"lat\":\"14.3340224\",\"lng\":\"120.9512622\"},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"fare\":\"792.40\",\"distance\":\"35.36 km\",\"timestamp\":1763482637}', 'sent', '2025-11-19 00:17:17', '2025-11-19 00:17:18'),
(308, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"108\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763482637}', 'sent', '2025-11-19 00:17:17', '2025-11-19 00:17:18'),
(309, 'user', '6', '{\"type\":\"driver_rejected\",\"ride_id\":108,\"message\":\"Driver declined your booking. Admin will assign another driver shortly...\",\"reason\":\"Driver declined\",\"timestamp\":1763482654}', 'sent', '2025-11-19 00:17:34', '2025-11-19 00:17:35'),
(310, 'user', '1', '{\"type\":\"driver_rejected\",\"booking_id\":108,\"driver_id\":1,\"message\":\"Driver rejected booking #108. Booking returned to pending.\",\"reason\":\"Driver declined\",\"timestamp\":1763482655}', 'sent', '2025-11-19 00:17:35', '2025-11-19 00:17:35'),
(311, 'user', '6', '{\"type\":\"booking_rejected\",\"booking_id\":\"108\",\"message\":\"Your booking request has been rejected. Please try booking again.\",\"timestamp\":1763482671}', 'sent', '2025-11-19 00:17:51', '2025-11-19 00:17:51'),
(312, 'role', 'admin', '{\"type\":\"booking_rejected\",\"booking_id\":\"108\",\"status\":\"rejected\",\"timestamp\":1763482671}', 'sent', '2025-11-19 00:17:51', '2025-11-19 00:17:51'),
(313, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"109\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"dropoff\":{\"address\":\"Dasmari\\u00f1as, Makati, District I, Metro Manila, Philippines\",\"lat\":14.5369453,\"lng\":121.0302771},\"fare\":315.3,\"distance\":\"12.62 km\",\"payment_method\":\"cash\",\"timestamp\":1763482702}', 'sent', '2025-11-19 00:18:22', '2025-11-19 00:18:22'),
(314, 'user', '6', '{\"type\":\"booking_rejected\",\"booking_id\":\"109\",\"message\":\"Your booking request has been rejected. Please try booking again.\",\"timestamp\":1763482774}', 'sent', '2025-11-19 00:19:34', '2025-11-19 00:19:34'),
(315, 'role', 'admin', '{\"type\":\"booking_rejected\",\"booking_id\":\"109\",\"status\":\"rejected\",\"timestamp\":1763482774}', 'sent', '2025-11-19 00:19:34', '2025-11-19 00:19:34'),
(316, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"110\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, Bedford Street, Dasmari\\u00f1as, Cavite, Philippines\",\"lat\":14.33402235,\"lng\":120.95126220986008},\"dropoff\":{\"address\":\"Lola Nena\'s, Adelina Street, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6060614,\"lng\":120.9884691},\"fare\":786.5,\"distance\":\"35.10 km\",\"payment_method\":\"cash\",\"timestamp\":1763482913}', 'sent', '2025-11-19 00:21:53', '2025-11-19 00:21:53'),
(317, 'user', '6', '{\"type\":\"booking_rejected\",\"booking_id\":\"110\",\"message\":\"Your booking request has been rejected. Please try booking again.\",\"timestamp\":1763482976}', 'sent', '2025-11-19 00:22:56', '2025-11-19 00:22:56'),
(318, 'role', 'admin', '{\"type\":\"booking_rejected\",\"booking_id\":\"110\",\"status\":\"rejected\",\"timestamp\":1763482976}', 'sent', '2025-11-19 00:22:56', '2025-11-19 00:22:56'),
(319, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"111\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, Bedford Street, Dasmari\\u00f1as, Cavite, Philippines\",\"lat\":14.33402235,\"lng\":120.95126220986008},\"dropoff\":{\"address\":\"Paliparan 2, Dasmari\\u00f1as, Paliparan, Cavite, Philippines\",\"lat\":14.3033636,\"lng\":120.9925479},\"fare\":211.35,\"distance\":\"7.69 km\",\"payment_method\":\"cash\",\"timestamp\":1763553408}', 'sent', '2025-11-19 19:56:48', '2025-11-19 19:56:48'),
(320, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"111\",\"pickup\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, Bedford Street, Dasmari\\u00f1as, Cavite, Philippines\",\"lat\":\"14.3340224\",\"lng\":\"120.9512622\"},\"dropoff\":{\"address\":\"Paliparan 2, Dasmari\\u00f1as, Paliparan, Cavite, Philippines\",\"lat\":\"14.3033636\",\"lng\":\"120.9925479\"},\"fare\":\"211.35\",\"distance\":\"7.69 km\",\"timestamp\":1763553414}', 'sent', '2025-11-19 19:56:54', '2025-11-19 19:56:54'),
(321, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"111\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763553414}', 'sent', '2025-11-19 19:56:54', '2025-11-19 19:56:54'),
(322, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":111,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763553426}', 'sent', '2025-11-19 19:57:07', '2025-11-19 19:57:07'),
(323, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"111\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763553433}', 'sent', '2025-11-19 19:57:13', '2025-11-19 19:57:13'),
(324, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"111\",\"status\":\"completed\",\"fare\":\"211.35\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763553440}', 'sent', '2025-11-19 19:57:20', '2025-11-19 19:57:20'),
(325, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"112\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, Bedford Street, Dasmari\\u00f1as, Cavite, Philippines\",\"lat\":14.33402235,\"lng\":120.95126220986008},\"dropoff\":{\"address\":\"Paliparan 2, Dasmari\\u00f1as, Paliparan, Cavite, Philippines\",\"lat\":14.3033636,\"lng\":120.9925479},\"fare\":211.35,\"distance\":\"7.69 km\",\"payment_method\":\"cash\",\"timestamp\":1763555039}', 'sent', '2025-11-19 20:23:59', '2025-11-19 20:23:59'),
(326, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"112\",\"pickup\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, Bedford Street, Dasmari\\u00f1as, Cavite, Philippines\",\"lat\":\"14.3340224\",\"lng\":\"120.9512622\"},\"dropoff\":{\"address\":\"Paliparan 2, Dasmari\\u00f1as, Paliparan, Cavite, Philippines\",\"lat\":\"14.3033636\",\"lng\":\"120.9925479\"},\"fare\":\"211.35\",\"distance\":\"7.69 km\",\"timestamp\":1763555044}', 'sent', '2025-11-19 20:24:04', '2025-11-19 20:24:04'),
(327, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"112\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763555044}', 'sent', '2025-11-19 20:24:04', '2025-11-19 20:24:05'),
(328, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":112,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763555055}', 'sent', '2025-11-19 20:24:15', '2025-11-19 20:24:15'),
(329, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"112\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763555061}', 'sent', '2025-11-19 20:24:21', '2025-11-19 20:24:22'),
(330, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"112\",\"status\":\"completed\",\"fare\":\"211.35\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763555072}', 'sent', '2025-11-19 20:24:32', '2025-11-19 20:24:32'),
(331, 'role', 'admin', '{\"type\":\"new_booking\",\"booking_id\":\"113\",\"user_id\":6,\"user_name\":\"Patrick\",\"user_phone\":\"+639123456789\",\"user_email\":\"patrick@email.com\",\"pickup\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, Bedford Street, Dasmari\\u00f1as, Cavite, Philippines\",\"lat\":14.33402235,\"lng\":120.95126220986008},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":14.6101615,\"lng\":120.9891706},\"fare\":792.4,\"distance\":\"35.36 km\",\"payment_method\":\"cash\",\"timestamp\":1763905964}', 'sent', '2025-11-23 21:52:44', '2025-11-23 21:52:44'),
(332, 'user', '1', '{\"type\":\"booking_assigned\",\"booking_id\":\"113\",\"pickup\":{\"address\":\"Kolehiyo ng Lungsod ng Dasmari\\u00f1as, Bedford Street, Dasmari\\u00f1as, Cavite, Philippines\",\"lat\":\"14.3340224\",\"lng\":\"120.9512622\"},\"dropoff\":{\"address\":\"UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines\",\"lat\":\"14.6101615\",\"lng\":\"120.9891706\"},\"fare\":\"792.40\",\"distance\":\"35.36 km\",\"timestamp\":1763905971}', 'sent', '2025-11-23 21:52:51', '2025-11-23 21:52:51'),
(333, 'user', '6', '{\"type\":\"booking_assigned\",\"booking_id\":\"113\",\"driver_id\":\"1\",\"driver_name\":\"Pedro Santos\",\"driver_phone\":\"+63 917 111 2222\",\"tricycle_number\":\"TRY-001\",\"timestamp\":1763905971}', 'sent', '2025-11-23 21:52:51', '2025-11-23 21:52:51'),
(334, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":113,\"status\":\"confirmed\",\"message\":\"Driver is on the way!\",\"timestamp\":1763905977}', 'sent', '2025-11-23 21:52:57', '2025-11-23 21:52:57'),
(335, 'user', '6', '{\"type\":\"status_update\",\"ride_id\":\"113\",\"status\":\"in_progress\",\"message\":\"Your trip has started!\",\"timestamp\":1763905987}', 'sent', '2025-11-23 21:53:07', '2025-11-23 21:53:07'),
(336, 'user', '6', '{\"type\":\"ride_completed\",\"ride_id\":\"113\",\"status\":\"completed\",\"fare\":\"792.40\",\"message\":\"Trip completed! Please rate your driver.\",\"timestamp\":1763905995}', 'sent', '2025-11-23 21:53:15', '2025-11-23 21:53:15');

-- --------------------------------------------------------

--
-- Table structure for table `ride_history`
--

CREATE TABLE `ride_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
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

INSERT INTO `ride_history` (`id`, `user_id`, `driver_id`, `assigned_at`, `accepted_at`, `started_at`, `driver_name`, `pickup_location`, `destination`, `pickup_lat`, `pickup_lng`, `dropoff_lat`, `dropoff_lng`, `fare`, `distance`, `payment_method`, `estimated_duration`, `status`, `user_rating`, `user_review`, `driver_rating`, `driver_review`, `driver_arrival_time`, `trip_start_time`, `trip_end_time`, `cancelled_by`, `cancel_reason`, `created_at`, `completed_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL, NULL, 'Pedro Santos', 'SM City Manila', 'Divisoria', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 85.00, '2.5 km', 'cash', NULL, 'completed', 5, 'Excellent service! Very friendly driver.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-12 12:21:40', '2025-11-12 13:21:40', '2025-11-12 15:21:40'),
(2, 2, 2, NULL, NULL, NULL, 'Jose Reyes', 'Quiapo Church', 'Recto Avenue', 14.5989000, 120.9831000, 14.6026000, 120.9831000, 60.00, '1.8 km', 'cash', NULL, 'completed', 4, 'Good driver, arrived on time.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-12 09:21:40', '2025-11-12 10:21:40', '2025-11-12 15:21:40'),
(4, 1, 4, NULL, NULL, NULL, 'Ricardo Lopez', 'Binondo Church', 'Lucky Chinatown', 14.5975000, 120.9739000, 14.5965000, 120.9785000, 55.00, '1.5 km', 'cash', NULL, 'completed', 5, 'Highly recommended!', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-10 15:21:40', '2025-11-10 15:21:40', '2025-11-12 15:21:40'),
(5, 4, 1, NULL, NULL, NULL, 'Pedro Santos', 'Intramuros', 'Rizal Park', 14.5897000, 120.9752000, 14.5833000, 120.9778000, 50.00, '1.0 km', 'cash', NULL, 'completed', 4, 'Nice ride.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-09 15:21:40', '2025-11-09 15:21:40', '2025-11-12 15:21:40'),
(6, 2, 1, NULL, NULL, NULL, 'Pedro Santos', 'Manila City Hall', 'San Miguel Church', 14.5919000, 120.9799000, 14.5901000, 120.9734000, 65.00, '1.8 km', 'cash', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-12 15:21:40', NULL, '2025-11-12 15:29:43'),
(7, 5, NULL, NULL, NULL, NULL, NULL, 'LRT Carriedo Station', 'Divisoria Mall', 14.5991000, 120.9815000, 14.6045000, 120.9801000, 70.00, '2.0 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-12 15:21:40', NULL, '2025-11-12 15:39:45'),
(8, 1, 2, NULL, NULL, NULL, NULL, 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6035149, 120.9835619, 50.00, '0.00 km', 'cash', '0 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-12 15:28:37', NULL, '2025-11-12 15:48:46'),
(9, 6, 4, NULL, NULL, NULL, NULL, 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'Lyceum of the Philippines University, Real Street, Manila, Fifth District, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.5915681, 120.9778248, 79.90, '1.46 km', 'cash', '4 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-12 15:31:36', NULL, '2025-11-12 15:32:47'),
(10, 1, NULL, NULL, NULL, NULL, NULL, 'Quiapo Church', 'Divisoria Market', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 55.00, '2.5 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-12 15:40:24', NULL, '2025-11-12 15:42:10'),
(11, 6, 4, NULL, NULL, NULL, 'Ricardo Lopez', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'Manila, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.5904492, 120.9803621, 80.35, '1.49 km', 'cash', '4 mins', '', NULL, NULL, NULL, NULL, '2025-11-12 15:43:06', NULL, NULL, NULL, NULL, '2025-11-12 15:42:35', NULL, '2025-11-12 15:43:11'),
(12, 2, NULL, NULL, NULL, NULL, NULL, 'SM Manila', 'Divisoria', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 60.00, '3.0 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-12 15:47:13', NULL, '2025-11-12 16:09:22'),
(13, 1, 2, NULL, NULL, NULL, 'Jose Reyes', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6035149, 120.9835619, 50.00, '0.00 km', 'cash', '0 mins', 'confirmed', NULL, NULL, NULL, NULL, '2025-11-12 16:02:22', NULL, NULL, NULL, NULL, '2025-11-12 16:00:37', NULL, '2025-11-12 16:02:22'),
(14, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'University of Santo Tomas, España Boulevard, Manila, Sampaloc, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6098426, 120.9894657, 70.25, '0.95 km', 'cash', '3 mins', 'completed', 4, '', NULL, NULL, '2025-11-12 16:10:06', '2025-11-12 16:10:17', '2025-11-12 16:10:30', NULL, NULL, '2025-11-12 16:08:14', '2025-11-12 16:10:30', '2025-11-12 16:10:52'),
(15, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Youniversity Suites, Manila, Sampaloc, Metro Manila, Philippines', 'University of Santo Tomas, España Boulevard, Manila, Sampaloc, Metro Manila, Philippines', 14.6011298, 120.9902032, 14.6098426, 120.9894657, 70.55, '0.97 km', 'cash', '3 mins', 'completed', NULL, NULL, NULL, NULL, '2025-11-12 16:25:31', '2025-11-12 16:25:37', '2025-11-12 16:25:43', NULL, NULL, '2025-11-12 16:20:39', '2025-11-12 16:25:43', '2025-11-12 16:25:43'),
(16, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Adamson University, D. Romualdez Sr. Street, Manila, Paco, Metro Manila, Philippines', 'Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 14.5862636, 120.9863598, 14.3340224, 120.9512622, 644.50, '28.30 km', 'cash', '85 mins', 'completed', 4, '', NULL, NULL, '2025-11-12 16:31:50', '2025-11-12 16:31:57', '2025-11-12 16:32:03', NULL, NULL, '2025-11-12 16:31:01', '2025-11-12 16:32:03', '2025-11-12 16:32:51'),
(17, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 'Santa Cristina 1, Dasmariñas, DBB-C, Cavite, Philippines', 14.3340224, 120.9512622, 14.3229859, 120.9699719, 99.40, '2.36 km', 'cash', '7 mins', 'completed', 3, 'ambobo mo mag drive', NULL, NULL, '2025-11-12 16:36:01', '2025-11-12 16:36:09', '2025-11-12 16:36:18', NULL, NULL, '2025-11-12 16:35:21', '2025-11-12 16:36:18', '2025-11-12 16:36:53'),
(18, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'University of the East, San Sebastian Street, Manila, Quiapo, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6021352, 120.9896724, 64.20, '0.68 km', 'cash', '2 mins', 'completed', 3, 'tangina mo', NULL, NULL, '2025-11-13 16:54:47', '2025-11-13 16:55:00', '2025-11-13 16:55:07', NULL, NULL, '2025-11-13 16:54:06', '2025-11-13 16:55:07', '2025-11-13 16:55:35'),
(19, 6, NULL, NULL, NULL, NULL, NULL, 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 14.6035149, 120.9835619, 14.3340224, 120.9512622, 684.55, '30.17 km', 'cash', '91 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 02:53:27', NULL, '2025-11-14 02:56:12'),
(20, 6, NULL, NULL, NULL, NULL, NULL, 'University of Santo Tomas, España Boulevard, Manila, Sampaloc, Metro Manila, Philippines', 'University of the Philippines Diliman, Magsaysay Avenue, Quezon City, Diliman, Metro Manila, Philippines', 14.6098426, 120.9894657, 14.6547213, 121.0663102, 252.90, '9.66 km', 'cash', '29 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 03:05:36', NULL, '2025-11-14 03:06:07'),
(21, 6, NULL, NULL, NULL, NULL, NULL, 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 'University of the Philippines Diliman, Magsaysay Avenue, Quezon City, Diliman, Metro Manila, Philippines', 14.6101615, 120.9891706, 14.6547213, 121.0663102, 253.05, '9.67 km', 'cash', '29 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 03:07:32', NULL, '2025-11-14 03:10:22'),
(22, 6, NULL, NULL, NULL, NULL, NULL, 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6101615, 120.9891706, 70.25, '0.95 km', 'cash', '3 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 03:18:45', NULL, '2025-11-14 03:22:57'),
(23, 6, NULL, NULL, NULL, NULL, NULL, 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6101615, 120.9891706, 70.25, '0.95 km', 'cash', '3 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 03:18:53', NULL, '2025-11-14 03:22:12'),
(24, 6, NULL, NULL, NULL, NULL, NULL, 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 'UST Faculty of Medicine and Surgery, Micsino Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6101615, 120.9891706, 14.6117871, 120.9893875, 54.70, '0.18 km', 'cash', '1 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 03:24:00', NULL, '2025-11-14 03:27:15'),
(25, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 'UST Hiraya, España Boulevard, Manila, Sampaloc, Metro Manila, Philippines', 14.6101615, 120.9891706, 14.6096209, 120.9883831, 51.50, '0.10 km', 'cash', '0 mins', 'completed', 4, 'sheesh', NULL, NULL, '2025-11-14 03:42:33', '2025-11-14 03:42:52', '2025-11-14 03:43:14', NULL, NULL, '2025-11-14 03:38:18', '2025-11-14 03:43:14', '2025-11-14 03:45:30'),
(26, 6, NULL, NULL, NULL, NULL, NULL, 'University of the East, San Sebastian Street, Manila, Quiapo, Metro Manila, Philippines', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 14.6021352, 120.9896724, 14.6035149, 120.9835619, 64.20, '0.68 km', 'cash', '2 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 03:48:28', NULL, '2025-11-14 04:08:53'),
(27, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6035149, 120.9835619, 50.00, '0.00 km', 'cash', '0 mins', 'completed', NULL, NULL, NULL, NULL, '2025-11-14 04:53:30', '2025-11-14 04:54:09', '2025-11-14 04:54:33', NULL, NULL, '2025-11-14 04:52:32', '2025-11-14 04:54:33', '2025-11-14 04:54:33'),
(28, 6, 2, NULL, NULL, NULL, 'Jose Reyes', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6108779, 120.9884947, 70.70, '0.98 km', 'cash', '3 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 05:15:44', NULL, '2025-11-14 05:18:36'),
(29, 6, NULL, NULL, NULL, NULL, NULL, 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6101615, 120.9891706, 70.25, '0.95 km', 'cash', '3 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 05:18:57', NULL, '2025-11-14 05:24:57'),
(30, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6101615, 120.9891706, 70.25, '0.95 km', 'cash', '3 mins', 'completed', NULL, NULL, NULL, NULL, '2025-11-14 05:27:37', '2025-11-14 05:27:57', '2025-11-14 05:35:39', NULL, NULL, '2025-11-14 05:25:48', '2025-11-14 05:35:39', '2025-11-14 05:35:39'),
(31, 6, NULL, NULL, NULL, NULL, NULL, 'Test Pickup Location', 'Test Dropoff Location', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 50.00, '2.5 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 05:29:58', NULL, '2025-11-14 05:31:50'),
(32, 6, NULL, NULL, NULL, NULL, NULL, 'Test Pickup Location', 'Test Dropoff Location', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 50.00, '2.5 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 05:31:02', NULL, '2025-11-14 05:31:47'),
(33, 6, NULL, NULL, NULL, NULL, NULL, 'Test Pickup Location', 'Test Dropoff Location', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 50.00, '2.5 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 05:31:13', NULL, '2025-11-14 05:31:44'),
(34, 6, NULL, NULL, NULL, NULL, NULL, 'Test Pickup Location', 'Test Dropoff Location', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 50.00, '2.5 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 05:32:41', NULL, '2025-11-14 05:36:13'),
(35, 6, NULL, NULL, NULL, NULL, NULL, 'Test Pickup Location', 'Test Dropoff Location', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 50.00, '2.5 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 05:36:22', NULL, '2025-11-14 05:38:55'),
(36, 6, NULL, NULL, NULL, NULL, NULL, 'Test Pickup Location', 'Test Dropoff Location', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 50.00, '2.5 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 05:37:18', NULL, '2025-11-14 05:38:52'),
(37, 6, NULL, NULL, NULL, NULL, NULL, 'Test Pickup Location', 'Test Dropoff Location', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 50.00, '2.5 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 05:39:01', NULL, '2025-11-14 05:44:41'),
(38, 6, NULL, NULL, NULL, NULL, NULL, 'Test Pickup Location', 'Test Dropoff Location', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 50.00, '2.5 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 05:39:53', NULL, '2025-11-14 05:44:38'),
(39, 6, NULL, NULL, NULL, NULL, NULL, 'Test Pickup Location', 'Test Dropoff Location', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 50.00, '2.5 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 05:40:03', NULL, '2025-11-14 05:44:34'),
(40, 6, NULL, NULL, NULL, NULL, NULL, 'Test Pickup Location', 'Test Dropoff Location', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 50.00, '2.5 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 05:40:03', NULL, '2025-11-14 05:44:31'),
(41, 6, NULL, NULL, NULL, NULL, NULL, 'Test Pickup Location', 'Test Dropoff Location', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 50.00, '2.5 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 05:40:04', NULL, '2025-11-14 05:44:29'),
(42, 6, NULL, NULL, NULL, NULL, NULL, 'Test Pickup Location', 'Test Dropoff Location', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 50.00, '2.5 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 05:40:04', NULL, '2025-11-14 05:44:26'),
(43, 6, NULL, NULL, NULL, NULL, NULL, 'Test Pickup Location', 'Test Dropoff Location', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 50.00, '2.5 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 05:40:04', NULL, '2025-11-14 05:44:23'),
(44, 6, NULL, NULL, NULL, NULL, NULL, 'Test Pickup Location', 'Test Dropoff Location', 14.5995000, 120.9842000, 14.6042000, 120.9822000, 50.00, '2.5 km', 'cash', NULL, 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 05:40:04', NULL, '2025-11-14 05:44:20'),
(45, 6, NULL, NULL, NULL, NULL, NULL, 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6101615, 120.9891706, 70.25, '0.95 km', 'cash', '3 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 05:45:09', NULL, '2025-11-14 05:55:25'),
(46, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6101615, 120.9891706, 70.25, '0.95 km', 'cash', '3 mins', 'completed', NULL, NULL, NULL, NULL, '2025-11-14 05:57:41', '2025-11-14 05:58:36', '2025-11-14 05:58:52', NULL, NULL, '2025-11-14 05:56:52', '2025-11-14 05:58:52', '2025-11-14 05:58:52'),
(47, 6, 2, NULL, NULL, NULL, 'Pedro Santos', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6108779, 120.9884947, 70.70, '0.98 km', 'cash', '3 mins', 'driver_found', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 06:00:03', NULL, '2025-11-14 06:04:59'),
(48, 6, NULL, NULL, NULL, NULL, NULL, 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6101615, 120.9891706, 70.25, '0.95 km', 'cash', '3 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 06:05:24', NULL, '2025-11-14 06:06:18'),
(49, 6, NULL, NULL, NULL, NULL, NULL, 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6030040, 120.9854779, 55.15, '0.21 km', 'cash', '1 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 06:08:14', NULL, '2025-11-14 06:11:51'),
(50, 6, NULL, NULL, NULL, NULL, NULL, 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6030040, 120.9854779, 55.15, '0.21 km', 'cash', '1 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 06:12:58', NULL, '2025-11-14 06:16:33'),
(51, 6, NULL, NULL, NULL, NULL, NULL, 'San Sebastian College - Recoletos, C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 14.6001915, 120.9893446, 14.6035149, 120.9835619, 64.80, '0.72 km', 'cash', '2 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 06:17:07', NULL, '2025-11-14 06:19:41'),
(52, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'Rex Book Store, Nicanor Reyes Street, Manila, Sampaloc, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6032904, 120.9876786, 58.60, '0.44 km', 'cash', '1 mins', 'completed', NULL, NULL, NULL, NULL, '2025-11-14 06:22:08', '2025-11-14 06:22:15', '2025-11-14 06:22:35', NULL, NULL, '2025-11-14 06:21:35', '2025-11-14 06:22:35', '2025-11-14 06:22:35'),
(53, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6047520, 120.9781565, 63.00, '0.60 km', 'cash', '2 mins', 'cancelled', NULL, NULL, NULL, NULL, '2025-11-14 06:23:38', NULL, NULL, 'user', 'User cancelled', '2025-11-14 06:22:52', NULL, '2025-11-14 06:33:04'),
(54, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6047520, 120.9781565, 63.00, '0.60 km', 'cash', '2 mins', 'completed', NULL, NULL, NULL, NULL, '2025-11-14 06:34:02', '2025-11-14 06:34:13', '2025-11-14 06:34:22', NULL, NULL, '2025-11-14 06:33:28', '2025-11-14 06:34:22', '2025-11-14 06:34:22'),
(55, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6108779, 120.9884947, 70.70, '0.98 km', 'cash', '3 mins', 'completed', NULL, NULL, NULL, NULL, '2025-11-14 06:40:29', '2025-11-14 06:40:37', '2025-11-14 06:40:43', NULL, NULL, '2025-11-14 06:39:42', '2025-11-14 06:40:43', '2025-11-14 06:40:43'),
(56, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6047520, 120.9781565, 63.00, '0.60 km', 'cash', '2 mins', 'completed', NULL, NULL, NULL, NULL, '2025-11-14 06:47:54', '2025-11-14 06:48:02', '2025-11-14 06:48:15', NULL, NULL, '2025-11-14 06:47:32', '2025-11-14 06:48:15', '2025-11-14 06:48:15'),
(57, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6101615, 120.9891706, 70.25, '0.95 km', 'cash', '3 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 06:51:49', '2025-11-14 06:51:56', '2025-11-14 06:52:01', NULL, NULL, '2025-11-14 06:51:20', '2025-11-14 06:52:01', '2025-11-14 06:52:10'),
(58, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6047520, 120.9781565, 63.00, '0.60 km', 'cash', '2 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 06:53:36', '2025-11-14 06:53:43', '2025-11-14 06:53:48', NULL, NULL, '2025-11-14 06:53:02', '2025-11-14 06:53:48', '2025-11-14 06:53:54'),
(59, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 'UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6101615, 120.9891706, 14.6108779, 120.9884947, 51.65, '0.11 km', 'cash', '0 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 06:57:47', '2025-11-14 06:57:53', '2025-11-14 06:57:57', NULL, NULL, '2025-11-14 06:57:12', '2025-11-14 06:57:57', '2025-11-14 06:58:03'),
(60, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Serahh\'s Aircon Repair, Kamias Road, Quezon City, Project 1, Metro Manila, Philippines', 'Dangwa Flower Market, Manila, Santa Cruz, Metro Manila, Philippines', 14.6315841, 121.0482572, 14.6149539, 120.9885810, 190.20, '6.68 km', 'cash', '20 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 06:58:44', NULL, '2025-11-14 06:59:32'),
(61, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6101615, 120.9891706, 70.25, '0.95 km', 'cash', '3 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 07:06:20', '2025-11-14 07:06:27', '2025-11-14 07:06:30', NULL, NULL, '2025-11-14 07:05:28', '2025-11-14 07:06:30', '2025-11-14 07:06:35'),
(62, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Embassy of the Philippines, Unioninkatu, Helsinki, Kluuvi, Uusimaa, Finland', 'Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 60.1684860, 24.9506363, 14.3340224, 120.9512622, 188114.05, '8955.47 km', 'cash', '26866 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 07:07:52', NULL, '2025-11-14 07:08:30'),
(63, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6030040, 120.9854779, 14.6101615, 120.9891706, 69.35, '0.89 km', 'cash', '3 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 07:09:39', '2025-11-14 07:09:47', '2025-11-14 07:09:56', NULL, NULL, '2025-11-14 07:08:45', '2025-11-14 07:09:56', '2025-11-14 07:10:03'),
(64, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Santo Tomas de Villa Nueva Cemetery, Padre Lupo Street, Pasig, Pasig Second District, Metro Manila, Philippines', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6101352, 121.0933183, 14.6101615, 120.9891706, 286.15, '11.21 km', 'cash', '34 mins', 'cancelled', NULL, NULL, NULL, NULL, '2025-11-14 07:11:19', NULL, NULL, 'user', 'User cancelled', '2025-11-14 07:10:20', NULL, '2025-11-14 07:11:25'),
(65, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 'Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines', 14.6035149, 120.9835619, 14.6047520, 120.9781565, 63.00, '0.60 km', 'cash', '2 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 07:12:01', NULL, '2025-11-14 07:12:58'),
(66, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 14.6047520, 120.9781565, 14.6035149, 120.9835619, 63.00, '0.60 km', 'cash', '2 mins', 'completed', 5, '', NULL, NULL, '2025-11-14 07:13:32', '2025-11-14 07:13:37', '2025-11-14 07:13:40', NULL, NULL, '2025-11-14 07:13:06', '2025-11-14 07:13:40', '2025-11-14 07:13:46'),
(67, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 'Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 14.6101615, 120.9891706, 14.6035149, 120.9835619, 70.25, '0.95 km', 'cash', '3 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 07:14:18', NULL, '2025-11-14 07:14:33'),
(68, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'UST Faculty of Medicine and Surgery, Micsino Drive, Manila, Sampaloc, Metro Manila, Philippines', 'Ystilo Salon, South Wing, Pasay, Zone 10, Metro Manila, Philippines', 14.6117871, 120.9893875, 14.5339796, 120.9820274, 232.35, '8.69 km', 'cash', '26 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 07:14:43', NULL, '2025-11-14 07:16:27'),
(69, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'UST Faculty of Medicine and Surgery, Micsino Drive, Manila, Sampaloc, Metro Manila, Philippines', 'UST Faculty of Medicine and Surgery, Micsino Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6117871, 120.9893875, 14.6117871, 120.9893875, 50.00, '0.00 km', 'cash', '0 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 07:16:37', NULL, '2025-11-14 07:19:05'),
(70, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'San Sebastian College - Recoletos, C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6001915, 120.9893446, 14.6101615, 120.9891706, 72.65, '1.11 km', 'cash', '3 mins', 'completed', 4, '', NULL, NULL, '2025-11-14 07:23:49', '2025-11-14 07:23:53', '2025-11-14 07:24:00', NULL, NULL, '2025-11-14 07:22:12', '2025-11-14 07:24:00', '2025-11-14 07:24:05'),
(71, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'San Sebastian College - Recoletos, C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines', 'Ersao, Abad Santos Avenue, Manila, Second District, Metro Manila, Philippines', 14.6001915, 120.9893446, 14.6083376, 120.9757635, 85.80, '1.72 km', 'cash', '5 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 07:24:25', NULL, '2025-11-14 07:26:05'),
(72, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Youth Gospel Center of the Philippines, Masangkay Street, Manila, Second District, Metro Manila, Philippines', 'CRS Fastfood, Dalupan Street, Manila, Sampaloc, Metro Manila, Philippines', 14.6063667, 120.9777425, 14.6035364, 120.9900159, 78.40, '1.36 km', 'cash', '4 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 07:32:10', '2025-11-14 07:32:16', '2025-11-14 07:32:20', NULL, NULL, '2025-11-14 07:31:06', '2025-11-14 07:32:20', '2025-11-14 07:32:25'),
(73, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Ride, Banawe Street, Quezon City, Santa Mesa Heights, Metro Manila, Philippines', 'ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 14.6321106, 121.0015447, 14.9094432, 120.5606322, 1238.25, '56.55 km', 'cash', '170 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 07:37:15', '2025-11-14 07:37:19', '2025-11-14 07:37:27', NULL, NULL, '2025-11-14 07:35:32', '2025-11-14 07:37:27', '2025-11-14 07:37:33'),
(74, 6, NULL, NULL, NULL, NULL, 'Pedro Santos', 'Serafia Street, Valenzuela, 1st District, Metro Manila, Philippines', 'Serahh\'s Aircon Repair, Kamias Road, Quezon City, Project 1, Metro Manila, Philippines', 14.6961867, 120.9661260, 14.6315841, 121.0482572, 288.85, '11.39 km', 'cash', '34 mins', 'searching', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 07:37:59', NULL, '2025-11-14 07:38:27'),
(75, 6, NULL, NULL, NULL, NULL, 'Pedro Santos', 'Grace and Sasa\'s Flower Shop, J. P. Carigma Street, Antipolo, Rizal, Philippines', 'Adasa Ancestral House, Manuel L. Quezon Avenue, Dapitan, Zamboanga del Norte, Philippines', 14.5866932, 121.1751943, 8.6568607, 123.4244132, 14820.40, '703.36 km', 'cash', '2110 mins', 'searching', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 07:42:25', NULL, '2025-11-14 07:43:16'),
(76, 6, NULL, NULL, NULL, NULL, NULL, 'Rewa Road, San Fernando, Pampanga, Philippines', 'Asdum Barangay Hall, J. P. Rizal Street, San Vicente, Camarines Norte, Philippines', 15.1192786, 120.6085612, 14.1194299, 122.8703555, 5669.25, '267.55 km', 'cash', '803 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 07:43:48', NULL, '2025-11-14 07:45:58'),
(77, 6, NULL, NULL, NULL, NULL, NULL, 'Asdum, Camarines Norte, Philippines', 'ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 14.1193515, 122.8703828, 14.9094432, 120.5606322, 5587.50, '263.70 km', 'cash', '791 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 07:46:44', NULL, '2025-11-14 07:47:08'),
(78, 6, NULL, NULL, NULL, NULL, 'Pedro Santos', 'ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 'Asgard Enterprises, Caimito Road, Caloocan, University Hills, Metro Manila, Philippines', 14.9094432, 120.5606322, 14.6587707, 120.9839076, 1170.55, '53.37 km', 'cash', '160 mins', 'searching', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 07:47:20', NULL, '2025-11-14 07:47:42'),
(79, 6, NULL, NULL, NULL, NULL, 'Pedro Santos', 'Asdum, Camarines Norte, Philippines', 'Aquino Assassination site, NAIA Road, Parañaque, Parañaque District 1, Metro Manila, Philippines', 14.1193515, 122.8703828, 14.5035480, 121.0041099, 4367.55, '205.57 km', 'cash', '617 mins', 'searching', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 07:51:37', NULL, '2025-11-14 07:51:58'),
(80, 6, NULL, NULL, NULL, NULL, 'Pedro Santos', 'ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 'Aquino Assassination site, NAIA Road, Parañaque, Parañaque District 1, Metro Manila, Philippines', 14.9094432, 120.5606322, 14.5035480, 121.0041099, 1429.05, '65.67 km', 'cash', '197 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 07:56:15', NULL, '2025-11-14 07:59:26'),
(81, 6, NULL, NULL, NULL, NULL, 'Pedro Santos', 'YSA, Rizal Drive, Taguig, Taguig District 2, Metro Manila, Philippines', 'Yssa Mae\'s Sportss & Music Enterprises, Gonzalo Puyat Street, Manila, Quiapo, Metro Manila, Philippines', 14.5522100, 121.0450098, 14.6007338, 120.9833501, 230.25, '8.55 km', 'cash', '26 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 08:00:06', NULL, '2025-11-14 08:01:15'),
(82, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Assassi, Cagayan, Philippines', 'ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 17.9022253, 121.7721766, 14.9094432, 120.5606322, 7546.85, '356.99 km', 'cash', '1071 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 08:08:24', '2025-11-14 08:08:31', '2025-11-14 08:08:36', NULL, NULL, '2025-11-14 08:07:11', '2025-11-14 08:08:36', '2025-11-14 08:08:42'),
(83, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 'Tsakos Maritime Philippines Inc., Esteban Street, Makati, District I, Metro Manila, Philippines', 14.6101615, 120.9891706, 14.5569098, 121.0172368, 189.75, '6.65 km', 'cash', '20 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 08:12:59', '2025-11-14 08:13:05', '2025-11-14 08:13:09', NULL, NULL, '2025-11-14 08:12:20', '2025-11-14 08:13:09', '2025-11-14 08:13:13'),
(84, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Caloocan, Metro Manila, Philippines', 'Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 14.6513480, 120.9724002, 14.3340224, 120.9512622, 917.50, '41.30 km', 'cash', '124 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 08:19:02', '2025-11-14 08:19:15', '2025-11-14 08:20:43', NULL, NULL, '2025-11-14 08:18:19', '2025-11-14 08:20:43', '2025-11-14 08:20:56'),
(85, 6, NULL, NULL, NULL, NULL, 'Pedro Santos', 'Tarlac Street, Dasmariñas, Bagong Bayan, Cavite, Philippines', 'Caloocan, Metro Manila, Philippines', 14.3081725, 120.9741675, 14.6513480, 120.9724002, 1071.90, '48.66 km', 'cash', '146 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 08:22:30', NULL, '2025-11-14 08:25:43'),
(86, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 'Ashford Street, Muntinlupa, Muntinlupa District 2, Metro Manila, Philippines', 14.9094432, 120.5606322, 14.4385753, 121.0372172, 2372.25, '110.55 km', 'cash', '332 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 08:26:42', '2025-11-14 08:26:59', '2025-11-14 08:27:03', NULL, NULL, '2025-11-14 08:26:04', '2025-11-14 08:27:03', '2025-11-14 08:27:12'),
(87, 6, 6, NULL, NULL, NULL, 'asdas asdasdads', 'Dasmariñas, Makati, District I, Metro Manila, Philippines', 'ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 14.5369453, 121.0302771, 14.9094432, 120.5606322, 2137.00, '99.40 km', 'cash', '298 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 08:44:26', '2025-11-14 08:44:32', '2025-11-14 08:44:40', NULL, NULL, '2025-11-14 08:43:58', '2025-11-14 08:44:40', '2025-11-14 08:44:49'),
(88, 6, 7, NULL, NULL, NULL, 'Jade Orense', 'Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 'Manila, Metro Manila, Philippines', 14.3340224, 120.9512622, 14.5904492, 120.9803621, 741.05, '32.87 km', 'cash', '99 mins', 'completed', 1, 'ambobo mo', NULL, NULL, '2025-11-14 08:52:19', '2025-11-14 08:52:29', '2025-11-14 08:52:36', NULL, NULL, '2025-11-14 08:51:10', '2025-11-14 08:52:36', '2025-11-14 08:52:45'),
(89, 6, 7, NULL, NULL, NULL, 'Jade Orense', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 'ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 14.6101615, 120.9891706, 14.9094432, 120.5606322, 1828.95, '84.73 km', 'cash', '254 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 14:24:06', '2025-11-14 14:24:16', '2025-11-14 14:24:19', NULL, NULL, '2025-11-14 14:22:44', '2025-11-14 14:24:19', '2025-11-14 14:28:57'),
(90, 6, 7, NULL, NULL, NULL, 'Jade Orense', 'Caloocan, NLEX Harbor Link Segment 10, Malabon, Metro Manila, Philippines', 'CAVITEX–C-5 Link, Pasay, Zone 20, Metro Manila, Philippines', 14.6569248, 120.9738786, 14.5074499, 121.0264039, 548.10, '23.74 km', 'cash', '71 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 14:30:20', '2025-11-14 14:30:24', '2025-11-14 14:30:28', NULL, NULL, '2025-11-14 14:29:52', '2025-11-14 14:30:28', '2025-11-14 14:30:34'),
(91, 6, 7, NULL, NULL, NULL, 'Jade Orense', 'Sases Basketball Court, Perlita Street, Manila, San Andres Bukid, Metro Manila, Philippines', 'Asian Development Bank, ADB Avenue, Mandaluyong, Metro Manila, Philippines', 14.5717102, 121.0007014, 14.5880757, 121.0583009, 271.65, '10.51 km', 'cash', '32 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 14:33:43', '2025-11-14 14:33:47', '2025-11-14 14:33:54', NULL, NULL, '2025-11-14 14:33:18', '2025-11-14 14:33:54', '2025-11-14 14:33:58'),
(92, 6, 7, NULL, NULL, NULL, 'Jade Orense', 'Bulacan Street, Manila, Second District, Metro Manila, Philippines', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.6271945, 120.9770554, 14.6101615, 120.9891706, 98.50, '2.30 km', 'cash', '7 mins', 'completed', 3, 'ambobo mo', NULL, NULL, '2025-11-14 14:37:41', '2025-11-14 14:38:00', '2025-11-14 14:38:08', NULL, NULL, '2025-11-14 14:37:17', '2025-11-14 14:38:08', '2025-11-14 14:38:16'),
(93, 6, 7, NULL, NULL, NULL, 'Jade Orense', 'Dakila Bridge, MacArthur Highway, Malolos, Sumapang Matanda, Bulacan, Philippines', 'Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 14.8513249, 120.8179625, 14.3340224, 120.9512622, 1812.35, '83.89 km', 'cash', '252 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 14:43:08', '2025-11-14 14:43:12', '2025-11-14 14:43:25', NULL, NULL, '2025-11-14 14:42:30', '2025-11-14 14:43:25', '2025-11-14 14:43:42'),
(94, 6, 7, NULL, NULL, NULL, 'Jade Orense', 'Asgard Corrogated Box Manufacturing Corporation, Pablo dela Cruz Street, Quezon City, 5th District, Metro Manila, Philippines', 'Valenzuela Linear Park, A. Bonifacio Street, Makati, District I, Metro Manila, Philippines', 14.7118418, 121.0267192, 14.5739738, 121.0255258, 507.00, '21.80 km', 'cash', '65 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 14:49:39', '2025-11-14 14:49:45', '2025-11-14 14:49:53', NULL, NULL, '2025-11-14 14:49:03', '2025-11-14 14:49:53', '2025-11-14 14:50:10'),
(95, 6, 7, NULL, NULL, NULL, 'Jade Orense', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 'ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 14.6101615, 120.9891706, 14.9094432, 120.5606322, 1828.95, '84.73 km', 'cash', '254 mins', 'completed', 3, '', NULL, NULL, '2025-11-14 14:52:48', '2025-11-14 14:52:55', '2025-11-14 14:52:59', NULL, NULL, '2025-11-14 14:51:02', '2025-11-14 14:52:59', '2025-11-14 14:53:08'),
(96, 6, NULL, NULL, NULL, NULL, NULL, 'Xanderella, Locsin Street, Bacolod, Bacolod-1, Negros Island Region, Philippines', 'Caloocan, Metro Manila, Philippines', 10.6700618, 122.9492528, 14.6513480, 120.9724002, 15160.50, '719.50 km', 'cash', '2159 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 14:59:46', NULL, '2025-11-14 15:00:04'),
(97, 6, NULL, NULL, NULL, NULL, NULL, 'Chanarian, Basco, Batanes, Philippines', 'Bakkaan, Banguingui, Sulu, Philippines', 20.4338830, 121.9599427, 5.9948121, 121.5233654, 33781.60, '1606.24 km', 'cash', '4819 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 15:07:01', NULL, '2025-11-14 15:07:09'),
(98, 6, NULL, NULL, NULL, NULL, NULL, 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 'UAP United Architects of the Philippines, Scout Rallos Street, Quezon City, Diliman, Metro Manila, Philippines', 14.6101615, 120.9891706, 14.6344568, 121.0339520, 202.00, '7.20 km', 'cash', '22 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-14 15:07:55', NULL, '2025-11-14 15:10:15'),
(99, 6, NULL, NULL, NULL, NULL, NULL, 'Asdum, Camarines Norte, Philippines', 'ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 14.1193515, 122.8703828, 14.9094432, 120.5606322, 5587.50, '263.70 km', 'cash', '791 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 15:22:53', NULL, '2025-11-14 15:23:20'),
(100, 6, NULL, NULL, NULL, NULL, NULL, 'ASF Laundry Shop, J. P. Rizal Street, Marikina, District I, Metro Manila, Philippines', 'Asian Development Bank, ADB Avenue, Mandaluyong, Metro Manila, Philippines', 14.6445615, 121.0958872, 14.5880757, 121.0583009, 261.20, '10.08 km', 'cash', '30 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 15:24:57', NULL, '2025-11-14 15:26:39'),
(101, 6, NULL, NULL, NULL, NULL, NULL, 'ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 'GSA Academic Regalia, Ipil Street, Marikina, District II, Metro Manila, Philippines', 14.9094432, 120.5606322, 14.6488451, 121.1201487, 2050.30, '95.22 km', 'cash', '286 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 15:26:57', NULL, '2025-11-14 15:27:44'),
(102, 6, NULL, NULL, NULL, NULL, NULL, 'UTS Building, Taft Avenue, Manila, Malate, Metro Manila, Philippines', 'ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 14.5755780, 120.9890063, 14.9094432, 120.5606322, 1934.70, '89.78 km', 'cash', '269 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 15:28:03', NULL, '2025-11-14 15:29:14'),
(103, 6, NULL, NULL, NULL, NULL, NULL, 'ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 'Gasdam, Mabalacat, Pampanga, Philippines', 14.9094432, 120.5606322, 15.1880948, 120.5839502, 702.35, '31.09 km', 'cash', '93 mins', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'User cancelled', '2025-11-14 15:29:31', NULL, '2025-11-14 15:30:00'),
(104, 6, 7, NULL, NULL, NULL, 'Jade Orense', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 'C. M. Recto Avenue, Divisoria, Tondo, First District, Manila, Capital District, Metro Manila, 1012, Philippines', 14.6101615, 120.9891706, 14.6040106, 120.9685910, 111.35, '2.89 km', 'cash', '9 mins', 'completed', 3, '', NULL, NULL, '2025-11-18 15:39:55', '2025-11-18 15:40:08', '2025-11-18 15:40:16', NULL, NULL, '2025-11-18 15:38:54', '2025-11-18 15:40:16', '2025-11-18 15:40:22'),
(105, 6, 11, NULL, NULL, NULL, 'Jadeski Orense', 'Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 'Sta. Cristina, Las Piñas, 1st District, Metro Manila, Philippines', 14.3340224, 120.9512622, 14.4520000, 120.9795910, 455.95, '19.33 km', 'cash', '58 mins', 'completed', 3, 'ambobo mp jade', NULL, NULL, '2025-11-18 15:56:27', '2025-11-18 15:56:32', '2025-11-18 15:56:40', NULL, NULL, '2025-11-18 15:55:49', '2025-11-18 15:56:40', '2025-11-18 15:56:50'),
(106, 6, NULL, NULL, NULL, NULL, 'Pedro Santos', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 'C. M. Recto Avenue, Divisoria, Tondo, First District, Manila, Capital District, Metro Manila, 1012, Philippines', 14.6101615, 120.9891706, 14.6040106, 120.9685910, 111.35, '2.89 km', 'cash', '9 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 15:58:40', NULL, '2025-11-18 16:03:09'),
(107, 6, NULL, NULL, NULL, NULL, 'Pedro Santos', 'SYS Marketing Appliances, P. Zamora Street, 16, Downtown, Tacloban, Eastern Visayas, 6500, Philippines', 'Sigma, Capiz, Western Visayas, 5816, Philippines', 11.2451388, 125.0018515, 11.4211672, 122.6658275, 12381.70, '587.18 km', 'cash', '1762 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 16:08:16', NULL, '2025-11-18 16:16:21'),
(108, 6, NULL, NULL, NULL, NULL, 'Pedro Santos', 'Kolehiyo ng Lungsod ng Dasmariñas, San Manuel Street, San Manuel 2, DBB-1, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.3340224, 120.9512622, 14.6101615, 120.9891706, 792.40, '35.36 km', 'cash', '106 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 16:17:02', NULL, '2025-11-18 16:17:51'),
(109, 6, NULL, NULL, NULL, NULL, NULL, 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 'Dasmariñas, Makati, District I, Metro Manila, Philippines', 14.6101615, 120.9891706, 14.5369453, 121.0302771, 315.30, '12.62 km', 'cash', '38 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 16:18:21', NULL, '2025-11-18 16:19:34'),
(110, 6, NULL, NULL, NULL, NULL, NULL, 'Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 'Lola Nena\'s, Adelina Street, Manila, Sampaloc, Metro Manila, Philippines', 14.3340224, 120.9512622, 14.6060614, 120.9884691, 786.50, '35.10 km', 'cash', '105 mins', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 16:21:53', NULL, '2025-11-18 16:22:56'),
(111, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 'Paliparan 2, Dasmariñas, Paliparan, Cavite, Philippines', 14.3340224, 120.9512622, 14.3033636, 120.9925479, 211.35, '7.69 km', 'cash', '23 mins', 'completed', 3, 'ambobo mo mag drive', NULL, NULL, '2025-11-19 11:57:06', '2025-11-19 11:57:13', '2025-11-19 11:57:19', NULL, NULL, '2025-11-19 11:56:47', '2025-11-19 11:57:19', '2025-11-19 11:57:28'),
(112, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 'Paliparan 2, Dasmariñas, Paliparan, Cavite, Philippines', 14.3340224, 120.9512622, 14.3033636, 120.9925479, 211.35, '7.69 km', 'cash', '23 mins', 'completed', 5, 'ambobo mo', NULL, NULL, '2025-11-19 12:24:15', '2025-11-19 12:24:21', '2025-11-19 12:24:31', NULL, NULL, '2025-11-19 12:23:59', '2025-11-19 12:24:31', '2025-11-19 12:24:40'),
(113, 6, 1, NULL, NULL, NULL, 'Pedro Santos', 'Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 'UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 14.3340224, 120.9512622, 14.6101615, 120.9891706, 792.40, '35.36 km', 'cash', '106 mins', 'completed', 3, '', NULL, NULL, '2025-11-23 13:52:57', '2025-11-23 13:53:06', '2025-11-23 13:53:14', NULL, NULL, '2025-11-23 13:52:44', '2025-11-23 13:53:14', '2025-11-23 13:53:19');

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
(4, 9, 6, 'user', 'driver_assigned', 'Driver Ricardo Lopez has been assigned to your ride', 1, '2025-11-12 15:31:36'),
(5, 9, 4, 'driver', 'ride_cancelled', 'Ride cancelled by passenger', 0, '2025-11-12 15:32:48'),
(6, 11, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Manila, Metro Manila, Philippines', 0, '2025-11-12 15:42:36'),
(7, 11, 4, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Manila, Metro Manila, Philippines', 0, '2025-11-12 15:42:52'),
(8, 11, 6, 'user', 'driver_assigned', 'Driver Ricardo Lopez has been assigned. Waiting for driver confirmation...', 1, '2025-11-12 15:42:52'),
(9, 11, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 1, '2025-11-12 15:43:06'),
(10, 8, 2, 'driver', 'ride_cancelled', 'Ride cancelled by passenger', 0, '2025-11-12 15:48:47'),
(11, 13, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 0, '2025-11-12 16:00:37'),
(12, 13, 2, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 0, '2025-11-12 16:01:06'),
(13, 13, 1, 'user', 'driver_assigned', 'Driver Jose Reyes has been assigned. Waiting for driver confirmation...', 0, '2025-11-12 16:01:06'),
(14, 13, 1, 'user', 'driver_confirmed', 'Your driver is on the way!', 1, '2025-11-12 16:02:22'),
(15, 14, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to University of Santo Tomas, España Boulevard, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-12 16:08:15'),
(16, 14, 1, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to University of Santo Tomas, España Boulevard, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-12 16:09:16'),
(17, 14, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 1, '2025-11-12 16:09:16'),
(18, 14, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 1, '2025-11-12 16:10:06'),
(19, 14, 6, 'user', 'trip_started', 'Your trip has started!', 1, '2025-11-12 16:10:17'),
(20, 14, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 1, '2025-11-12 16:10:30'),
(21, 14, 1, 'driver', 'rating_received', 'You received a 4-star rating from a passenger!', 0, '2025-11-12 16:10:53'),
(22, 15, 1, 'admin', 'new_booking', 'New booking request from Youniversity Suites, Manila, Sampaloc, Metro Manila, Philippines to University of Santo Tomas, España Boulevard, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-12 16:20:39'),
(23, 15, 1, 'driver', 'new_ride', 'New ride assigned: Youniversity Suites, Manila, Sampaloc, Metro Manila, Philippines to University of Santo Tomas, España Boulevard, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-12 16:21:02'),
(24, 15, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 1, '2025-11-12 16:21:02'),
(25, 15, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 1, '2025-11-12 16:25:32'),
(26, 15, 6, 'user', 'trip_started', 'Your trip has started!', 1, '2025-11-12 16:25:38'),
(27, 15, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 1, '2025-11-12 16:25:43'),
(28, 16, 1, 'admin', 'new_booking', 'New booking request from Adamson University, D. Romualdez Sr. Street, Manila, Paco, Metro Manila, Philippines to Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 0, '2025-11-12 16:31:01'),
(29, 16, 1, 'driver', 'new_ride', 'New ride assigned: Adamson University, D. Romualdez Sr. Street, Manila, Paco, Metro Manila, Philippines to Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 0, '2025-11-12 16:31:16'),
(30, 16, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 1, '2025-11-12 16:31:16'),
(31, 16, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 1, '2025-11-12 16:31:50'),
(32, 16, 6, 'user', 'trip_started', 'Your trip has started!', 1, '2025-11-12 16:31:57'),
(33, 16, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 1, '2025-11-12 16:32:04'),
(34, 16, 1, 'driver', 'rating_received', 'You received a 4-star rating from a passenger!', 0, '2025-11-12 16:32:51'),
(35, 17, 1, 'admin', 'new_booking', 'New booking request from Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines to Santa Cristina 1, Dasmariñas, DBB-C, Cavite, Philippines', 0, '2025-11-12 16:35:21'),
(36, 17, 1, 'driver', 'new_ride', 'New ride assigned: Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines to Santa Cristina 1, Dasmariñas, DBB-C, Cavite, Philippines', 0, '2025-11-12 16:35:40'),
(37, 17, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 1, '2025-11-12 16:35:40'),
(38, 17, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 1, '2025-11-12 16:36:01'),
(39, 17, 6, 'user', 'trip_started', 'Your trip has started!', 1, '2025-11-12 16:36:09'),
(40, 17, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 1, '2025-11-12 16:36:18'),
(41, 17, 1, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-12 16:36:53'),
(42, 18, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to University of the East, San Sebastian Street, Manila, Quiapo, Metro Manila, Philippines', 0, '2025-11-13 16:54:06'),
(43, 18, 1, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to University of the East, San Sebastian Street, Manila, Quiapo, Metro Manila, Philippines', 0, '2025-11-13 16:54:28'),
(44, 18, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 1, '2025-11-13 16:54:28'),
(45, 18, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 1, '2025-11-13 16:54:47'),
(46, 18, 6, 'user', 'trip_started', 'Your trip has started!', 1, '2025-11-13 16:55:00'),
(47, 18, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 1, '2025-11-13 16:55:07'),
(48, 18, 1, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-13 16:55:35'),
(49, 19, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 0, '2025-11-14 02:53:27'),
(50, 20, 1, 'admin', 'new_booking', 'New booking request from University of Santo Tomas, España Boulevard, Manila, Sampaloc, Metro Manila, Philippines to University of the Philippines Diliman, Magsaysay Avenue, Quezon City, Diliman, Metro Manila, Philippines', 0, '2025-11-14 03:05:37'),
(51, 21, 1, 'admin', 'new_booking', 'New booking request from UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to University of the Philippines Diliman, Magsaysay Avenue, Quezon City, Diliman, Metro Manila, Philippines', 0, '2025-11-14 03:07:33'),
(52, 22, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 03:18:45'),
(53, 23, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 03:18:54'),
(54, 24, 1, 'admin', 'new_booking', 'New booking request from UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to UST Faculty of Medicine and Surgery, Micsino Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 03:24:01'),
(55, 25, 1, 'admin', 'new_booking', 'New booking request from UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to UST Hiraya, España Boulevard, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 03:38:18'),
(56, 25, 1, 'driver', 'new_ride', 'New ride assigned: UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to UST Hiraya, España Boulevard, Manila, Sampaloc, Metro Manila, Philippines', 1, '2025-11-14 03:41:40'),
(57, 25, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 1, '2025-11-14 03:41:40'),
(58, 25, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 1, '2025-11-14 03:42:33'),
(59, 25, 6, 'user', 'trip_started', 'Your trip has started!', 1, '2025-11-14 03:42:52'),
(60, 25, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 1, '2025-11-14 03:43:14'),
(61, 25, 1, 'driver', 'rating_received', 'You received a 4-star rating from a passenger!', 1, '2025-11-14 03:45:30'),
(62, 26, 1, 'admin', 'new_booking', 'New booking request from University of the East, San Sebastian Street, Manila, Quiapo, Metro Manila, Philippines to Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 0, '2025-11-14 03:48:28'),
(63, 27, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 0, '2025-11-14 04:52:32'),
(64, 27, 1, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 0, '2025-11-14 04:52:41'),
(65, 27, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 04:52:41'),
(66, 27, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 04:53:30'),
(67, 27, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 04:54:09'),
(68, 27, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 04:54:34'),
(69, 28, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 05:15:44'),
(70, 28, 2, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 05:18:04'),
(71, 28, 6, 'user', 'driver_assigned', 'Driver Jose Reyes has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 05:18:04'),
(72, 28, 2, 'driver', 'ride_cancelled', 'Ride cancelled by passenger', 0, '2025-11-14 05:18:36'),
(73, 29, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 05:18:57'),
(74, 30, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 05:25:48'),
(75, 30, 1, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 05:26:47'),
(76, 30, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 05:26:47'),
(77, 30, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 05:27:37'),
(78, 30, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 05:27:57'),
(79, 30, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 05:35:40'),
(80, 45, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 05:45:09'),
(81, 46, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 05:56:53'),
(82, 46, 1, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 05:57:03'),
(83, 46, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 05:57:03'),
(84, 46, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 05:57:41'),
(85, 46, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 05:58:36'),
(86, 46, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 05:58:52'),
(87, 47, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 06:00:03'),
(88, 47, 1, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 06:00:08'),
(89, 47, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 06:00:08'),
(90, 47, 6, 'user', 'driver_declined', 'Driver declined. Searching for another driver...', 0, '2025-11-14 06:04:59'),
(91, 47, 2, 'driver', 'new_ride', 'New ride request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 0, '2025-11-14 06:04:59'),
(92, 48, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 06:05:24'),
(93, 49, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 06:08:14'),
(94, 50, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 06:12:58'),
(95, 51, 1, 'admin', 'new_booking', 'New booking request from San Sebastian College - Recoletos, C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines to Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 0, '2025-11-14 06:17:07'),
(96, 52, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Rex Book Store, Nicanor Reyes Street, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 06:21:35'),
(97, 52, 1, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Rex Book Store, Nicanor Reyes Street, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 06:21:41'),
(98, 52, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 06:21:41'),
(99, 52, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 06:22:08'),
(100, 52, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 06:22:15'),
(101, 52, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 06:22:35'),
(102, 53, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines', 0, '2025-11-14 06:22:52'),
(103, 53, 1, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines', 0, '2025-11-14 06:23:18'),
(104, 53, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 06:23:18'),
(105, 53, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 06:23:39'),
(106, 53, 1, 'driver', 'ride_cancelled', 'Ride cancelled by passenger', 0, '2025-11-14 06:33:04'),
(107, 54, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines', 0, '2025-11-14 06:33:28'),
(108, 54, 1, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines', 0, '2025-11-14 06:33:32'),
(109, 54, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 06:33:32'),
(110, 54, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 06:34:02'),
(111, 54, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 06:34:13'),
(112, 54, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 06:34:22'),
(113, 55, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 06:39:43'),
(114, 55, 1, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 06:40:05'),
(115, 55, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 06:40:05'),
(116, 55, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 06:40:29'),
(117, 55, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 06:40:37'),
(118, 55, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 06:40:43'),
(119, 56, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines', 0, '2025-11-14 06:47:32'),
(120, 56, 1, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines', 0, '2025-11-14 06:47:38'),
(121, 56, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 06:47:38'),
(122, 56, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 06:47:54'),
(123, 56, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 06:48:02'),
(124, 56, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 06:48:16'),
(125, 57, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 06:51:20'),
(126, 57, 1, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 06:51:25'),
(127, 57, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 06:51:25'),
(128, 57, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 06:51:49'),
(129, 57, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 06:51:56'),
(130, 57, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 06:52:01'),
(131, 57, 1, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 06:52:10'),
(132, 58, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines', 0, '2025-11-14 06:53:02'),
(133, 58, 1, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines', 0, '2025-11-14 06:53:20'),
(134, 58, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 06:53:20'),
(135, 58, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 06:53:36'),
(136, 58, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 06:53:43'),
(137, 58, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 06:53:48'),
(138, 58, 1, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 06:53:54'),
(139, 59, 1, 'admin', 'new_booking', 'New booking request from UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 06:57:12'),
(140, 59, 1, 'driver', 'new_ride', 'New ride assigned: UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to UST Main Library, Albert Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 06:57:26'),
(141, 59, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 06:57:26'),
(142, 59, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 06:57:47'),
(143, 59, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 06:57:53'),
(144, 59, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 06:57:57'),
(145, 59, 1, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 06:58:03'),
(146, 59, 1, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 06:58:03'),
(147, 60, 1, 'admin', 'new_booking', 'New booking request from Serahh\'s Aircon Repair, Kamias Road, Quezon City, Project 1, Metro Manila, Philippines to Dangwa Flower Market, Manila, Santa Cruz, Metro Manila, Philippines', 0, '2025-11-14 06:58:44'),
(148, 60, 1, 'driver', 'new_ride', 'New ride assigned: Serahh\'s Aircon Repair, Kamias Road, Quezon City, Project 1, Metro Manila, Philippines to Dangwa Flower Market, Manila, Santa Cruz, Metro Manila, Philippines', 0, '2025-11-14 06:59:24'),
(149, 60, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 06:59:24'),
(150, 60, 1, 'driver', 'ride_cancelled', 'Ride cancelled by passenger', 0, '2025-11-14 06:59:32'),
(151, 61, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 07:05:28'),
(152, 61, 1, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 07:05:57'),
(153, 61, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:05:57'),
(154, 61, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 07:06:20'),
(155, 61, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 07:06:27'),
(156, 61, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 07:06:31'),
(157, 61, 1, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 07:06:35'),
(158, 62, 1, 'admin', 'new_booking', 'New booking request from Embassy of the Philippines, Unioninkatu, Helsinki, Kluuvi, Uusimaa, Finland to Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 0, '2025-11-14 07:07:52'),
(159, 62, 1, 'driver', 'new_ride', 'New ride assigned: Embassy of the Philippines, Unioninkatu, Helsinki, Kluuvi, Uusimaa, Finland to Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 0, '2025-11-14 07:08:20'),
(160, 62, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:08:20'),
(161, 62, 1, 'driver', 'ride_cancelled', 'Ride cancelled by passenger', 0, '2025-11-14 07:08:30'),
(162, 63, 1, 'admin', 'new_booking', 'New booking request from C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 07:08:46'),
(163, 63, 1, 'driver', 'new_ride', 'New ride assigned: C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 07:08:59'),
(164, 63, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:08:59'),
(165, 63, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 07:09:39'),
(166, 63, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 07:09:47'),
(167, 63, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 07:09:56'),
(168, 63, 1, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 07:10:03'),
(169, 64, 1, 'admin', 'new_booking', 'New booking request from Santo Tomas de Villa Nueva Cemetery, Padre Lupo Street, Pasig, Pasig Second District, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 07:10:20'),
(170, 64, 1, 'driver', 'new_ride', 'New ride assigned: Santo Tomas de Villa Nueva Cemetery, Padre Lupo Street, Pasig, Pasig Second District, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 07:10:59'),
(171, 64, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:10:59'),
(172, 64, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 07:11:19'),
(173, 64, 1, 'driver', 'ride_cancelled', 'Ride cancelled by passenger', 0, '2025-11-14 07:11:25'),
(174, 65, 1, 'admin', 'new_booking', 'New booking request from Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines', 0, '2025-11-14 07:12:02'),
(175, 65, 1, 'driver', 'new_ride', 'New ride assigned: Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines to Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines', 0, '2025-11-14 07:12:43'),
(176, 65, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:12:43'),
(177, 65, 1, 'driver', 'ride_cancelled', 'Ride cancelled by passenger', 0, '2025-11-14 07:12:58'),
(178, 66, 1, 'admin', 'new_booking', 'New booking request from Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines to Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 0, '2025-11-14 07:13:07'),
(179, 66, 1, 'driver', 'new_ride', 'New ride assigned: Claro M. Recto Bridge II, C. M. Recto Avenue, Manila, Binondo, Metro Manila, Philippines to Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 0, '2025-11-14 07:13:12'),
(180, 66, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:13:12'),
(181, 66, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 07:13:32'),
(182, 66, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 07:13:37'),
(183, 66, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 07:13:41'),
(184, 66, 1, 'driver', 'rating_received', 'You received a 5-star rating from a passenger!', 0, '2025-11-14 07:13:46'),
(185, 67, 1, 'admin', 'new_booking', 'New booking request from UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 0, '2025-11-14 07:14:18'),
(186, 67, 1, 'driver', 'new_ride', 'New ride assigned: UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to Recto, C. M. Recto Avenue, Manila, Santa Cruz, Metro Manila, Philippines', 0, '2025-11-14 07:14:22'),
(187, 67, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:14:22'),
(188, 67, 1, 'driver', 'ride_cancelled', 'Ride cancelled by passenger', 0, '2025-11-14 07:14:33'),
(189, 68, 1, 'admin', 'new_booking', 'New booking request from UST Faculty of Medicine and Surgery, Micsino Drive, Manila, Sampaloc, Metro Manila, Philippines to Ystilo Salon, South Wing, Pasay, Zone 10, Metro Manila, Philippines', 0, '2025-11-14 07:14:45'),
(190, 68, 1, 'driver', 'new_ride', 'New ride assigned: UST Faculty of Medicine and Surgery, Micsino Drive, Manila, Sampaloc, Metro Manila, Philippines to Ystilo Salon, South Wing, Pasay, Zone 10, Metro Manila, Philippines', 0, '2025-11-14 07:14:49'),
(191, 68, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:14:49'),
(192, 68, 1, 'driver', 'ride_cancelled', 'Ride cancelled by passenger', 0, '2025-11-14 07:16:27'),
(193, 69, 1, 'admin', 'new_booking', 'New booking request from UST Faculty of Medicine and Surgery, Micsino Drive, Manila, Sampaloc, Metro Manila, Philippines to UST Faculty of Medicine and Surgery, Micsino Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 07:16:38'),
(194, 69, 1, 'driver', 'new_ride', 'New ride assigned: UST Faculty of Medicine and Surgery, Micsino Drive, Manila, Sampaloc, Metro Manila, Philippines to UST Faculty of Medicine and Surgery, Micsino Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 07:18:43'),
(195, 69, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:18:43'),
(196, 69, 1, 'driver', 'ride_cancelled', 'Ride cancelled by passenger', 0, '2025-11-14 07:19:06'),
(197, 70, 1, 'admin', 'new_booking', 'New booking request from San Sebastian College - Recoletos, C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 07:22:12'),
(198, 70, 1, 'driver', 'new_ride', 'New ride assigned: San Sebastian College - Recoletos, C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 07:22:49'),
(199, 70, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:22:49'),
(200, 70, 6, 'user', 'driver_declined', 'Driver declined. Searching for another driver...', 0, '2025-11-14 07:23:29'),
(201, 70, 1, 'driver', 'new_ride', 'New ride request from San Sebastian College - Recoletos, C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 07:23:29'),
(202, 70, 6, 'user', 'driver_declined', 'Driver declined. Searching for another driver...', 0, '2025-11-14 07:23:43'),
(203, 70, 1, 'driver', 'new_ride', 'New ride request from San Sebastian College - Recoletos, C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 07:23:43'),
(204, 70, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 07:23:49'),
(205, 70, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 07:23:53'),
(206, 70, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 07:24:00'),
(207, 70, 1, 'driver', 'rating_received', 'You received a 4-star rating from a passenger!', 0, '2025-11-14 07:24:05'),
(208, 70, 1, 'driver', 'rating_received', 'You received a 4-star rating from a passenger!', 0, '2025-11-14 07:24:05'),
(209, 71, 1, 'admin', 'new_booking', 'New booking request from San Sebastian College - Recoletos, C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines to Ersao, Abad Santos Avenue, Manila, Second District, Metro Manila, Philippines', 0, '2025-11-14 07:24:25'),
(210, 71, 1, 'driver', 'new_ride', 'New ride assigned: San Sebastian College - Recoletos, C. M. Recto Avenue, Manila, Sampaloc, Metro Manila, Philippines to Ersao, Abad Santos Avenue, Manila, Second District, Metro Manila, Philippines', 0, '2025-11-14 07:24:56'),
(211, 71, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:24:56'),
(212, 71, 1, 'driver', 'ride_cancelled', 'Ride cancelled by passenger', 0, '2025-11-14 07:26:06'),
(213, 72, 1, 'admin', 'new_booking', 'New booking request from Youth Gospel Center of the Philippines, Masangkay Street, Manila, Second District, Metro Manila, Philippines to CRS Fastfood, Dalupan Street, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 07:31:06'),
(214, 72, 1, 'driver', 'new_ride', 'New ride assigned: Youth Gospel Center of the Philippines, Masangkay Street, Manila, Second District, Metro Manila, Philippines to CRS Fastfood, Dalupan Street, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 07:31:28'),
(215, 72, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:31:28'),
(216, 72, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 07:32:10'),
(217, 72, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 07:32:16'),
(218, 72, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 07:32:21'),
(219, 72, 1, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 07:32:26'),
(220, 73, 1, 'admin', 'new_booking', 'New booking request from Ride, Banawe Street, Quezon City, Santa Mesa Heights, Metro Manila, Philippines to ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 0, '2025-11-14 07:35:32'),
(221, 73, 1, 'driver', 'new_ride', 'New ride assigned: Ride, Banawe Street, Quezon City, Santa Mesa Heights, Metro Manila, Philippines to ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 0, '2025-11-14 07:36:57'),
(222, 73, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:36:57'),
(223, 73, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 07:37:15'),
(224, 73, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 07:37:21'),
(225, 73, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 07:37:29'),
(226, 73, 1, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 07:37:34'),
(227, 74, 1, 'admin', 'new_booking', 'New booking request from Serafia Street, Valenzuela, 1st District, Metro Manila, Philippines to Serahh\'s Aircon Repair, Kamias Road, Quezon City, Project 1, Metro Manila, Philippines', 0, '2025-11-14 07:37:59'),
(228, 74, 1, 'driver', 'new_ride', 'New ride assigned: Serafia Street, Valenzuela, 1st District, Metro Manila, Philippines to Serahh\'s Aircon Repair, Kamias Road, Quezon City, Project 1, Metro Manila, Philippines', 0, '2025-11-14 07:38:03'),
(229, 74, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:38:03'),
(230, 74, 6, 'user', 'driver_declined', 'Driver declined. Searching for another driver...', 0, '2025-11-14 07:38:28'),
(231, 75, 1, 'admin', 'new_booking', 'New booking request from Grace and Sasa\'s Flower Shop, J. P. Carigma Street, Antipolo, Rizal, Philippines to Adasa Ancestral House, Manuel L. Quezon Avenue, Dapitan, Zamboanga del Norte, Philippines', 0, '2025-11-14 07:42:25'),
(232, 75, 1, 'driver', 'new_ride', 'New ride assigned: Grace and Sasa\'s Flower Shop, J. P. Carigma Street, Antipolo, Rizal, Philippines to Adasa Ancestral House, Manuel L. Quezon Avenue, Dapitan, Zamboanga del Norte, Philippines', 0, '2025-11-14 07:42:45'),
(233, 75, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:42:45'),
(234, 75, 6, 'user', 'driver_declined', 'Driver declined. Searching for another driver...', 0, '2025-11-14 07:43:17'),
(235, 76, 1, 'admin', 'new_booking', 'New booking request from Rewa Road, San Fernando, Pampanga, Philippines to Asdum Barangay Hall, J. P. Rizal Street, San Vicente, Camarines Norte, Philippines', 0, '2025-11-14 07:43:48'),
(236, 77, 1, 'admin', 'new_booking', 'New booking request from Asdum, Camarines Norte, Philippines to ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 0, '2025-11-14 07:46:44'),
(237, 78, 1, 'admin', 'new_booking', 'New booking request from ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines to Asgard Enterprises, Caimito Road, Caloocan, University Hills, Metro Manila, Philippines', 0, '2025-11-14 07:47:21'),
(238, 78, 1, 'driver', 'new_ride', 'New ride assigned: ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines to Asgard Enterprises, Caimito Road, Caloocan, University Hills, Metro Manila, Philippines', 0, '2025-11-14 07:47:24'),
(239, 78, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:47:24'),
(240, 78, 6, 'user', 'driver_declined', 'Driver declined. Searching for another driver...', 0, '2025-11-14 07:47:42'),
(241, 79, 1, 'admin', 'new_booking', 'New booking request from Asdum, Camarines Norte, Philippines to Aquino Assassination site, NAIA Road, Parañaque, Parañaque District 1, Metro Manila, Philippines', 0, '2025-11-14 07:51:37'),
(242, 79, 1, 'driver', 'new_ride', 'New ride assigned: Asdum, Camarines Norte, Philippines to Aquino Assassination site, NAIA Road, Parañaque, Parañaque District 1, Metro Manila, Philippines', 0, '2025-11-14 07:51:42'),
(243, 79, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:51:42'),
(244, 79, 6, 'user', 'driver_declined', 'Driver declined. Searching for another driver...', 0, '2025-11-14 07:51:58'),
(245, 80, 1, 'admin', 'new_booking', 'New booking request from ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines to Aquino Assassination site, NAIA Road, Parañaque, Parañaque District 1, Metro Manila, Philippines', 0, '2025-11-14 07:56:15'),
(246, 80, 1, 'driver', 'new_ride', 'New ride assigned: ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines to Aquino Assassination site, NAIA Road, Parañaque, Parañaque District 1, Metro Manila, Philippines', 0, '2025-11-14 07:56:20'),
(247, 80, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:56:20'),
(248, 80, 6, 'user', 'driver_declined', 'Driver declined. Your booking is being reassigned...', 0, '2025-11-14 07:56:54'),
(249, 80, 1, 'driver', 'new_ride', 'New ride assigned: ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines to Aquino Assassination site, NAIA Road, Parañaque, Parañaque District 1, Metro Manila, Philippines', 0, '2025-11-14 07:57:17'),
(250, 80, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 07:57:17'),
(251, 80, 6, 'user', 'driver_declined', 'Driver declined. Your booking is being reassigned...', 0, '2025-11-14 07:59:13'),
(252, 81, 1, 'admin', 'new_booking', 'New booking request from YSA, Rizal Drive, Taguig, Taguig District 2, Metro Manila, Philippines to Yssa Mae\'s Sportss & Music Enterprises, Gonzalo Puyat Street, Manila, Quiapo, Metro Manila, Philippines', 0, '2025-11-14 08:00:06'),
(253, 81, 1, 'driver', 'new_ride', 'New ride assigned: YSA, Rizal Drive, Taguig, Taguig District 2, Metro Manila, Philippines to Yssa Mae\'s Sportss & Music Enterprises, Gonzalo Puyat Street, Manila, Quiapo, Metro Manila, Philippines', 0, '2025-11-14 08:00:12'),
(254, 81, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 08:00:12'),
(255, 81, 6, 'user', 'driver_declined', 'Driver declined. Your booking is being reassigned...', 0, '2025-11-14 08:00:50'),
(256, 82, 1, 'admin', 'new_booking', 'New booking request from Assassi, Cagayan, Philippines to ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 0, '2025-11-14 08:07:12'),
(257, 82, 1, 'driver', 'new_ride', 'New ride assigned: Assassi, Cagayan, Philippines to ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 0, '2025-11-14 08:07:26'),
(258, 82, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 08:07:26'),
(259, 82, 6, 'user', 'driver_declined', 'Driver declined. Your booking is being reassigned...', 0, '2025-11-14 08:07:50'),
(260, 82, 1, 'driver', 'new_ride', 'New ride assigned: Assassi, Cagayan, Philippines to ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 0, '2025-11-14 08:08:08'),
(261, 82, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 08:08:08'),
(262, 82, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 08:08:25'),
(263, 82, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 08:08:31'),
(264, 82, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 08:08:36'),
(265, 82, 1, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 08:08:42'),
(266, 83, 1, 'admin', 'new_booking', 'New booking request from UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to Tsakos Maritime Philippines Inc., Esteban Street, Makati, District I, Metro Manila, Philippines', 0, '2025-11-14 08:12:20'),
(267, 83, 1, 'driver', 'new_ride', 'New ride assigned: UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to Tsakos Maritime Philippines Inc., Esteban Street, Makati, District I, Metro Manila, Philippines', 0, '2025-11-14 08:12:39'),
(268, 83, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 08:12:39'),
(269, 83, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 08:12:59'),
(270, 83, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 08:13:05'),
(271, 83, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 08:13:09'),
(272, 83, 1, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 08:13:13'),
(273, 84, 1, 'admin', 'new_booking', 'New booking request from Caloocan, Metro Manila, Philippines to Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 0, '2025-11-14 08:18:19'),
(274, 84, 1, 'driver', 'new_ride', 'New ride assigned: Caloocan, Metro Manila, Philippines to Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 0, '2025-11-14 08:18:34'),
(275, 84, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 08:18:34'),
(276, 84, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 08:19:02'),
(277, 84, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 08:19:15'),
(278, 84, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 08:20:43'),
(279, 84, 1, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 08:20:56'),
(280, 85, 1, 'admin', 'new_booking', 'New booking request from Tarlac Street, Dasmariñas, Bagong Bayan, Cavite, Philippines to Caloocan, Metro Manila, Philippines', 0, '2025-11-14 08:22:30'),
(281, 85, 1, 'driver', 'new_ride', 'New ride assigned: Tarlac Street, Dasmariñas, Bagong Bayan, Cavite, Philippines to Caloocan, Metro Manila, Philippines', 0, '2025-11-14 08:22:45'),
(282, 85, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 08:22:45'),
(283, 85, 6, 'user', 'driver_declined', 'Driver declined. Your booking is being reassigned...', 0, '2025-11-14 08:25:18'),
(284, 86, 1, 'admin', 'new_booking', 'New booking request from ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines to Ashford Street, Muntinlupa, Muntinlupa District 2, Metro Manila, Philippines', 0, '2025-11-14 08:26:05'),
(285, 86, 1, 'driver', 'new_ride', 'New ride assigned: ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines to Ashford Street, Muntinlupa, Muntinlupa District 2, Metro Manila, Philippines', 0, '2025-11-14 08:26:14'),
(286, 86, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 08:26:14'),
(287, 86, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 08:26:42'),
(288, 86, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 08:26:59'),
(289, 86, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 08:27:04'),
(290, 86, 1, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 08:27:13'),
(291, 87, 1, 'admin', 'new_booking', 'New booking request from Dasmariñas, Makati, District I, Metro Manila, Philippines to ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 0, '2025-11-14 08:43:59'),
(292, 87, 6, 'driver', 'new_ride', 'New ride assigned: Dasmariñas, Makati, District I, Metro Manila, Philippines to ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 0, '2025-11-14 08:44:05'),
(293, 87, 6, 'user', 'driver_assigned', 'Driver asdas asdasdads has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 08:44:05'),
(294, 87, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 08:44:27'),
(295, 87, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 08:44:33'),
(296, 87, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 08:44:42'),
(297, 87, 6, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 08:44:49'),
(298, 88, 1, 'admin', 'new_booking', 'New booking request from Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines to Manila, Metro Manila, Philippines', 0, '2025-11-14 08:51:10'),
(299, 88, 7, 'driver', 'new_ride', 'New ride assigned: Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines to Manila, Metro Manila, Philippines', 0, '2025-11-14 08:51:39'),
(300, 88, 6, 'user', 'driver_assigned', 'Driver Jade Orense has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 08:51:39'),
(301, 88, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 08:52:19'),
(302, 88, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 08:52:29'),
(303, 88, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 08:52:37'),
(304, 88, 7, 'driver', 'rating_received', 'You received a 1-star rating from a passenger!', 0, '2025-11-14 08:52:45');
INSERT INTO `ride_notifications` (`id`, `ride_id`, `recipient_id`, `recipient_type`, `notification_type`, `message`, `is_read`, `created_at`) VALUES
(305, 89, 1, 'admin', 'new_booking', 'New booking request from UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 0, '2025-11-14 14:22:44'),
(306, 89, 7, 'driver', 'new_ride', 'New ride assigned: UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 0, '2025-11-14 14:23:10'),
(307, 89, 6, 'user', 'driver_assigned', 'Driver Jade Orense has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 14:23:10'),
(308, 89, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 14:24:06'),
(309, 89, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 14:24:16'),
(310, 89, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 14:24:20'),
(311, 89, 7, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 14:28:57'),
(312, 90, 1, 'admin', 'new_booking', 'New booking request from Caloocan, NLEX Harbor Link Segment 10, Malabon, Metro Manila, Philippines to CAVITEX–C-5 Link, Pasay, Zone 20, Metro Manila, Philippines', 0, '2025-11-14 14:29:52'),
(313, 90, 7, 'driver', 'new_ride', 'New ride assigned: Caloocan, NLEX Harbor Link Segment 10, Malabon, Metro Manila, Philippines to CAVITEX–C-5 Link, Pasay, Zone 20, Metro Manila, Philippines', 0, '2025-11-14 14:30:07'),
(314, 90, 6, 'user', 'driver_assigned', 'Driver Jade Orense has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 14:30:07'),
(315, 90, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 14:30:20'),
(316, 90, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 14:30:24'),
(317, 90, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 14:30:29'),
(318, 90, 7, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 14:30:34'),
(319, 91, 1, 'admin', 'new_booking', 'New booking request from Sases Basketball Court, Perlita Street, Manila, San Andres Bukid, Metro Manila, Philippines to Asian Development Bank, ADB Avenue, Mandaluyong, Metro Manila, Philippines', 0, '2025-11-14 14:33:19'),
(320, 91, 7, 'driver', 'new_ride', 'New ride assigned: Sases Basketball Court, Perlita Street, Manila, San Andres Bukid, Metro Manila, Philippines to Asian Development Bank, ADB Avenue, Mandaluyong, Metro Manila, Philippines', 0, '2025-11-14 14:33:37'),
(321, 91, 6, 'user', 'driver_assigned', 'Driver Jade Orense has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 14:33:37'),
(322, 91, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 14:33:43'),
(323, 91, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 14:33:47'),
(324, 91, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 14:33:54'),
(325, 91, 7, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 14:33:58'),
(326, 92, 1, 'admin', 'new_booking', 'New booking request from Bulacan Street, Manila, Second District, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 14:37:17'),
(327, 92, 7, 'driver', 'new_ride', 'New ride assigned: Bulacan Street, Manila, Second District, Metro Manila, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-14 14:37:33'),
(328, 92, 6, 'user', 'driver_assigned', 'Driver Jade Orense has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 14:37:33'),
(329, 92, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 14:37:41'),
(330, 92, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 14:38:00'),
(331, 92, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 14:38:09'),
(332, 92, 7, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 14:38:16'),
(333, 93, 1, 'admin', 'new_booking', 'New booking request from Dakila Bridge, MacArthur Highway, Malolos, Sumapang Matanda, Bulacan, Philippines to Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 0, '2025-11-14 14:42:30'),
(334, 93, 7, 'driver', 'new_ride', 'New ride assigned: Dakila Bridge, MacArthur Highway, Malolos, Sumapang Matanda, Bulacan, Philippines to Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines', 0, '2025-11-14 14:43:00'),
(335, 93, 6, 'user', 'driver_assigned', 'Driver Jade Orense has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 14:43:00'),
(336, 93, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 14:43:09'),
(337, 93, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 14:43:12'),
(338, 93, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 14:43:25'),
(339, 93, 7, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 14:43:42'),
(340, 94, 1, 'admin', 'new_booking', 'New booking request from Asgard Corrogated Box Manufacturing Corporation, Pablo dela Cruz Street, Quezon City, 5th District, Metro Manila, Philippines to Valenzuela Linear Park, A. Bonifacio Street, Makati, District I, Metro Manila, Philippines', 0, '2025-11-14 14:49:03'),
(341, 94, 7, 'driver', 'new_ride', 'New ride assigned: Asgard Corrogated Box Manufacturing Corporation, Pablo dela Cruz Street, Quezon City, 5th District, Metro Manila, Philippines to Valenzuela Linear Park, A. Bonifacio Street, Makati, District I, Metro Manila, Philippines', 0, '2025-11-14 14:49:19'),
(342, 94, 6, 'user', 'driver_assigned', 'Driver Jade Orense has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 14:49:19'),
(343, 94, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 14:49:39'),
(344, 94, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 14:49:45'),
(345, 94, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 14:49:54'),
(346, 94, 7, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 14:50:10'),
(347, 95, 1, 'admin', 'new_booking', 'New booking request from UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 0, '2025-11-14 14:51:02'),
(348, 95, 7, 'driver', 'new_ride', 'New ride assigned: UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 0, '2025-11-14 14:52:20'),
(349, 95, 6, 'user', 'driver_assigned', 'Driver Jade Orense has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 14:52:20'),
(350, 95, 6, 'user', 'driver_declined', 'Driver declined. Your booking is being reassigned...', 0, '2025-11-14 14:52:25'),
(351, 95, 7, 'driver', 'new_ride', 'New ride assigned: UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 0, '2025-11-14 14:52:44'),
(352, 95, 6, 'user', 'driver_assigned', 'Driver Jade Orense has been assigned. Waiting for driver confirmation...', 0, '2025-11-14 14:52:44'),
(353, 95, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-14 14:52:49'),
(354, 95, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-14 14:52:55'),
(355, 95, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-14 14:53:00'),
(356, 95, 7, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-14 14:53:08'),
(357, 96, 1, 'admin', 'new_booking', 'New booking request from Xanderella, Locsin Street, Bacolod, Bacolod-1, Negros Island Region, Philippines to Caloocan, Metro Manila, Philippines', 0, '2025-11-14 14:59:47'),
(358, 97, 1, 'admin', 'new_booking', 'New booking request from Chanarian, Basco, Batanes, Philippines to Bakkaan, Banguingui, Sulu, Philippines', 0, '2025-11-14 15:07:01'),
(359, 98, 1, 'admin', 'new_booking', 'New booking request from UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to UAP United Architects of the Philippines, Scout Rallos Street, Quezon City, Diliman, Metro Manila, Philippines', 0, '2025-11-14 15:07:55'),
(360, 99, 1, 'admin', 'new_booking', 'New booking request from Asdum, Camarines Norte, Philippines to ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 0, '2025-11-14 15:22:53'),
(361, 100, 1, 'admin', 'new_booking', 'New booking request from ASF Laundry Shop, J. P. Rizal Street, Marikina, District I, Metro Manila, Philippines to Asian Development Bank, ADB Avenue, Mandaluyong, Metro Manila, Philippines', 0, '2025-11-14 15:24:57'),
(362, 101, 1, 'admin', 'new_booking', 'New booking request from ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines to GSA Academic Regalia, Ipil Street, Marikina, District II, Metro Manila, Philippines', 0, '2025-11-14 15:26:58'),
(363, 102, 1, 'admin', 'new_booking', 'New booking request from UTS Building, Taft Avenue, Manila, Malate, Metro Manila, Philippines to ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines', 0, '2025-11-14 15:28:04'),
(364, 103, 1, 'admin', 'new_booking', 'New booking request from ASD Battery and Vulcanizing Shop, Jose Abad Santos Avenue, Lubao, Balantacan, Pampanga, Philippines to Gasdam, Mabalacat, Pampanga, Philippines', 0, '2025-11-14 15:29:32'),
(365, 104, 1, 'admin', 'new_booking', 'New booking request from UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to C. M. Recto Avenue, Divisoria, Tondo, First District, Manila, Capital District, Metro Manila, 1012, Philippines', 0, '2025-11-18 15:38:54'),
(366, 104, 7, 'driver', 'new_ride', 'New ride assigned: UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to C. M. Recto Avenue, Divisoria, Tondo, First District, Manila, Capital District, Metro Manila, 1012, Philippines', 0, '2025-11-18 15:39:14'),
(367, 104, 6, 'user', 'driver_assigned', 'Driver Jade Orense has been assigned. Waiting for driver confirmation...', 0, '2025-11-18 15:39:14'),
(368, 104, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-18 15:39:55'),
(369, 104, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-18 15:40:08'),
(370, 104, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-18 15:40:16'),
(371, 104, 7, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-18 15:40:22'),
(372, 105, 1, 'admin', 'new_booking', 'New booking request from Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines to Sta. Cristina, Las Piñas, 1st District, Metro Manila, Philippines', 0, '2025-11-18 15:55:49'),
(373, 105, 11, 'driver', 'new_ride', 'New ride assigned: Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines to Sta. Cristina, Las Piñas, 1st District, Metro Manila, Philippines', 0, '2025-11-18 15:55:58'),
(374, 105, 6, 'user', 'driver_assigned', 'Driver Jadeski Orense has been assigned. Waiting for driver confirmation...', 0, '2025-11-18 15:55:58'),
(375, 105, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-18 15:56:27'),
(376, 105, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-18 15:56:32'),
(377, 105, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-18 15:56:41'),
(378, 105, 11, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-18 15:56:50'),
(379, 105, 11, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-18 15:56:50'),
(380, 106, 1, 'admin', 'new_booking', 'New booking request from UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to C. M. Recto Avenue, Divisoria, Tondo, First District, Manila, Capital District, Metro Manila, 1012, Philippines', 0, '2025-11-18 15:58:40'),
(381, 106, 1, 'driver', 'new_ride', 'New ride assigned: UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to C. M. Recto Avenue, Divisoria, Tondo, First District, Manila, Capital District, Metro Manila, 1012, Philippines', 0, '2025-11-18 15:59:07'),
(382, 106, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-18 15:59:07'),
(383, 106, 6, 'user', 'driver_declined', 'Driver declined. Your booking is being reassigned...', 0, '2025-11-18 15:59:20'),
(384, 106, 1, 'driver', 'new_ride', 'New ride assigned: UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to C. M. Recto Avenue, Divisoria, Tondo, First District, Manila, Capital District, Metro Manila, 1012, Philippines', 0, '2025-11-18 15:59:30'),
(385, 106, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-18 15:59:30'),
(386, 106, 6, 'user', 'driver_declined', 'Driver declined. Your booking is being reassigned...', 0, '2025-11-18 16:00:21'),
(387, 107, 1, 'admin', 'new_booking', 'New booking request from SYS Marketing Appliances, P. Zamora Street, 16, Downtown, Tacloban, Eastern Visayas, 6500, Philippines to Sigma, Capiz, Western Visayas, 5816, Philippines', 0, '2025-11-18 16:08:16'),
(388, 107, 1, 'driver', 'new_ride', 'New ride assigned: SYS Marketing Appliances, P. Zamora Street, 16, Downtown, Tacloban, Eastern Visayas, 6500, Philippines to Sigma, Capiz, Western Visayas, 5816, Philippines', 0, '2025-11-18 16:08:26'),
(389, 107, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-18 16:08:26'),
(390, 107, 6, 'user', 'driver_declined', 'Driver declined. Your booking is being reassigned...', 0, '2025-11-18 16:08:48'),
(391, 107, 1, 'driver', 'new_ride', 'New ride assigned: SYS Marketing Appliances, P. Zamora Street, 16, Downtown, Tacloban, Eastern Visayas, 6500, Philippines to Sigma, Capiz, Western Visayas, 5816, Philippines', 0, '2025-11-18 16:08:56'),
(392, 107, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-18 16:08:56'),
(393, 107, 6, 'user', 'driver_declined', 'Driver declined. Your booking is being reassigned...', 0, '2025-11-18 16:16:15'),
(394, 108, 1, 'admin', 'new_booking', 'New booking request from Kolehiyo ng Lungsod ng Dasmariñas, San Manuel Street, San Manuel 2, DBB-1, Dasmariñas, Cavite, Calabarzon, 4114, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-18 16:17:02'),
(395, 108, 1, 'driver', 'new_ride', 'New ride assigned: Kolehiyo ng Lungsod ng Dasmariñas, San Manuel Street, San Manuel 2, DBB-1, Dasmariñas, Cavite, Calabarzon, 4114, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-18 16:17:06'),
(396, 108, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-18 16:17:06'),
(397, 108, 6, 'user', 'driver_declined', 'Driver declined. Your booking is being reassigned...', 0, '2025-11-18 16:17:12'),
(398, 108, 1, 'driver', 'new_ride', 'New ride assigned: Kolehiyo ng Lungsod ng Dasmariñas, San Manuel Street, San Manuel 2, DBB-1, Dasmariñas, Cavite, Calabarzon, 4114, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-18 16:17:17'),
(399, 108, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-18 16:17:17'),
(400, 108, 6, 'user', 'driver_declined', 'Driver declined. Your booking is being reassigned...', 0, '2025-11-18 16:17:34'),
(401, 109, 1, 'admin', 'new_booking', 'New booking request from UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines to Dasmariñas, Makati, District I, Metro Manila, Philippines', 0, '2025-11-18 16:18:22'),
(402, 110, 1, 'admin', 'new_booking', 'New booking request from Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines to Lola Nena\'s, Adelina Street, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-18 16:21:53'),
(403, 111, 1, 'admin', 'new_booking', 'New booking request from Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines to Paliparan 2, Dasmariñas, Paliparan, Cavite, Philippines', 0, '2025-11-19 11:56:48'),
(404, 111, 1, 'driver', 'new_ride', 'New ride assigned: Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines to Paliparan 2, Dasmariñas, Paliparan, Cavite, Philippines', 0, '2025-11-19 11:56:54'),
(405, 111, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-19 11:56:54'),
(406, 111, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-19 11:57:06'),
(407, 111, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-19 11:57:13'),
(408, 111, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-19 11:57:20'),
(409, 111, 1, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-19 11:57:28'),
(410, 112, 1, 'admin', 'new_booking', 'New booking request from Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines to Paliparan 2, Dasmariñas, Paliparan, Cavite, Philippines', 0, '2025-11-19 12:23:59'),
(411, 112, 1, 'driver', 'new_ride', 'New ride assigned: Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines to Paliparan 2, Dasmariñas, Paliparan, Cavite, Philippines', 0, '2025-11-19 12:24:04'),
(412, 112, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-19 12:24:04'),
(413, 112, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-19 12:24:15'),
(414, 112, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-19 12:24:21'),
(415, 112, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-19 12:24:32'),
(416, 112, 1, 'driver', 'rating_received', 'You received a 5-star rating from a passenger!', 0, '2025-11-19 12:24:41'),
(417, 113, 1, 'admin', 'new_booking', 'New booking request from Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-23 13:52:44'),
(418, 113, 1, 'driver', 'new_ride', 'New ride assigned: Kolehiyo ng Lungsod ng Dasmariñas, Bedford Street, Dasmariñas, Cavite, Philippines to UST Museum, Ampuero Drive, Manila, Sampaloc, Metro Manila, Philippines', 0, '2025-11-23 13:52:51'),
(419, 113, 6, 'user', 'driver_assigned', 'Driver Pedro Santos has been assigned. Waiting for driver confirmation...', 0, '2025-11-23 13:52:51'),
(420, 113, 6, 'user', 'driver_confirmed', 'Your driver is on the way!', 0, '2025-11-23 13:52:57'),
(421, 113, 6, 'user', 'trip_started', 'Your trip has started!', 0, '2025-11-23 13:53:07'),
(422, 113, 6, 'user', 'trip_completed', 'Trip completed! Please rate your driver.', 0, '2025-11-23 13:53:14'),
(423, 113, 1, 'driver', 'rating_received', 'You received a 3-star rating from a passenger!', 0, '2025-11-23 13:53:19');

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
  `tricycle_number` varchar(20) DEFAULT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `current_latitude` decimal(10,8) DEFAULT NULL,
  `current_longitude` decimal(11,8) DEFAULT NULL,
  `location_updated_at` timestamp NULL DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tricycle driver accounts';

--
-- Dumping data for table `tricycle_drivers`
--

INSERT INTO `tricycle_drivers` (`id`, `name`, `email`, `password`, `phone`, `plate_number`, `tricycle_number`, `tricycle_model`, `license_number`, `is_verified`, `rating`, `average_rating`, `total_ratings`, `current_lat`, `current_lng`, `last_location_update`, `status`, `total_trips_completed`, `total_earnings`, `acceptance_rate`, `cancellation_rate`, `created_at`, `updated_at`, `current_latitude`, `current_longitude`, `location_updated_at`, `is_online`) VALUES
(1, 'Pedro Santos', 'pedro@driver.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', '+63 917 111 2222', 'TRY-123', 'TRY-001', 'Honda TMX', 'LIC-001', 1, 3.48, 3.48, 23, 14.5995000, 120.9842000, NULL, 'available', 29, 12371.56, 100.00, 0.00, '2025-11-12 15:21:39', '2025-11-23 13:53:19', NULL, NULL, NULL, 0),
(2, 'Jose Reyes', 'jose@driver.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', '+63 917 222 3333', 'TRY-456', 'TRY-002', 'Kawasaki', 'LIC-002', 1, 4.90, 4.90, 0, 14.6042000, 120.9822000, NULL, 'available', 0, 0.00, 100.00, 0.00, '2025-11-12 15:21:39', '2025-11-13 17:42:23', NULL, NULL, NULL, 0),
(3, 'Antonio Cruz', 'antonio@driver.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', '+63 917 333 4444', 'TRY-789', 'TRY-003', 'Yamaha', 'LIC-003', 1, 4.70, 4.70, 0, 14.5896000, 120.9812000, NULL, 'offline', 0, 0.00, 100.00, 0.00, '2025-11-12 15:21:39', '2025-11-13 17:42:23', NULL, NULL, NULL, 0),
(4, 'Ricardo Lopez', 'ricardo@driver.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', '+63 917 444 5555', 'TRY-321', 'TRY-004', 'Honda', 'LIC-004', 1, 5.00, 5.00, 0, 14.5933000, 120.9771000, NULL, 'on_trip', 0, 0.00, 100.00, 0.00, '2025-11-12 15:21:39', '2025-11-13 17:42:23', NULL, NULL, NULL, 0),
(6, 'asdas asdasdads', 'mica@email.com', '$2y$10$ufvsbWQAJotxWb0jtSHnYONdacxpy.ldWqrWuEbpdmYKHeZv7DqAG', '09231231231', 'ASD3124', 'TRY-006', NULL, 'A41-31-231451', 0, 3.00, 3.00, 1, NULL, NULL, NULL, 'available', 1, 1709.60, 100.00, 0.00, '2025-11-13 17:37:51', '2025-11-14 08:44:49', NULL, NULL, NULL, 0),
(7, 'Jade Orense', 'jadeorense@email.com', '$2y$10$jgFVkETju7amengkR39rceZG.9bEk3DJEbfAu8pTAOjCuE3OjuXoe', '09123123123', 'ASD1231', 'TRY-007', NULL, 'A91-32-131512', 0, 2.78, 2.78, 9, NULL, NULL, NULL, 'available', 9, 6198.32, 100.00, 0.00, '2025-11-14 08:49:35', '2025-11-18 15:40:22', NULL, NULL, NULL, 0),
(10, 'lolipop dwayne', '67@mailinator.com', '$2y$10$f9yQ3dvXlY9PpP6N.zXZhu.LrSo27sR1q51C2.kipCdkTY6eOpmYm', '09231231233', 'ASX1351', 'TRY-008', NULL, 'A12-31-312333', 0, 5.00, 5.00, 0, NULL, NULL, NULL, 'offline', 0, 0.00, 100.00, 0.00, '2025-11-17 17:27:54', '2025-11-17 17:27:54', NULL, NULL, NULL, 0),
(11, 'Jadeski Orense', 'jrorense@kld.edu.ph', '$2y$10$is3xIWOislkD5T5ajuUJb.oApELd5PyvpuyVe.7P0H7.g5.SZScEe', '09345463452', 'ASB2513', 'TRY-009', NULL, 'A51-25-681235', 0, 3.00, 3.00, 1, NULL, NULL, NULL, 'available', 1, 364.76, 100.00, 0.00, '2025-11-18 15:53:24', '2025-11-18 15:56:50', NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ws_token` varchar(255) DEFAULT NULL,
  `ws_token_expires` timestamp NULL DEFAULT NULL,
  `current_latitude` decimal(10,8) DEFAULT NULL,
  `current_longitude` decimal(11,8) DEFAULT NULL,
  `location_updated_at` timestamp NULL DEFAULT NULL,
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

INSERT INTO `users` (`id`, `name`, `email`, `password`, `ws_token`, `ws_token_expires`, `current_latitude`, `current_longitude`, `location_updated_at`, `phone`, `phone_verified`, `google_id`, `facebook_id`, `email_verified`, `created_at`, `updated_at`) VALUES
(1, 'Juan Dela Cruz', 'juan@email.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', 'f90d5c9ad3ef7d7786603d8fa6d3e7e1d033bc700d45a47f594f2e51b559304f', '2025-11-21 04:40:04', NULL, NULL, NULL, '+63 912 345 6789', 1, NULL, NULL, 1, '2025-11-12 15:21:39', '2025-11-14 04:40:04'),
(2, 'Maria Garcia', 'maria@email.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', '323bfb19299d6aba19e71cc3fb9d44fcb391c1ecba7238d424cbd0add6f3b022', '2025-11-21 04:40:04', 14.60123068, 120.99378980, '2025-11-14 05:04:40', '+63 923 456 7890', 1, NULL, NULL, 1, '2025-11-12 15:21:39', '2025-11-14 05:04:40'),
(4, 'Anna Bautista', 'anna@email.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', '6a91291ef63f53e2611e941fd6bb38b973aebd2139aa15c80f66ff82bf782f9f', '2025-11-21 04:40:04', NULL, NULL, NULL, '+63 945 678 9012', 1, NULL, NULL, 1, '2025-11-12 15:21:39', '2025-11-14 04:40:04'),
(5, 'Miguel Torres', 'miguel@email.com', '$2y$10$B5n.rUoOhrVNvnP6lCpBqectTv031mVIOg98jmPU9Fczcy15QdfSy', '297479bd6656bf5f58d6d17683775c242899509a438d389f75842fa97ee0266a', '2025-11-21 04:40:04', NULL, NULL, NULL, '+63 956 789 0123', 1, NULL, NULL, 1, '2025-11-12 15:21:39', '2025-11-14 04:40:04'),
(6, 'Patrick', 'patrick@email.com', '$2y$10$Pu56TIDhYUU3wE0YpHUvEOwVpQxGW80r5WGidISHf5xuDUbMCZ6he', 'e04e9dd722de17bf65a9534bab6aa7c44304e12d354a7eba2f70c85d09cfa37b', '2025-11-21 04:40:05', NULL, NULL, NULL, '+639123456789', 1, NULL, NULL, 0, '2025-11-12 15:31:03', '2025-11-14 04:40:05'),
(7, 'Arcane Legends', 'newaccarcanelegends@gmail.com', '$2y$10$pku9eRgtxE92hA8v.zHW5OctRL3Hyb8ONIYKfdWxB4pzZYED3OxHe', '2961ba37aaae6b076bacd3c21b055f75ee7099de88d27e6a5b29a2275c076a0c', '2025-11-21 04:40:05', NULL, NULL, NULL, NULL, 0, '111873639730778643903', NULL, 1, '2025-11-12 15:36:04', '2025-11-14 04:40:05'),
(8, 'Datu Ismael', 'datuismael123@gmail.com', '$2y$10$KCZajFXXGnr6cXLeEznUOOXfb91qzoPd7mdZoHwzLzAO8IGQv5lM6', NULL, NULL, NULL, NULL, NULL, '09123123412', 0, '109009373760843575075', NULL, 1, '2025-11-14 13:40:51', '2025-11-14 14:06:46');

-- --------------------------------------------------------

--
-- Stand-in structure for view `ws_active_connections_summary`
-- (See below for the actual view)
--
CREATE TABLE `ws_active_connections_summary` (
`role` enum('admin','driver','rider')
,`active_count` bigint(21)
,`latest_activity` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `ws_connections`
--

CREATE TABLE `ws_connections` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('admin','driver','rider') NOT NULL,
  `connection_id` varchar(100) NOT NULL,
  `connected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ws_message_queue`
--

CREATE TABLE `ws_message_queue` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('admin','driver','rider') NOT NULL,
  `message_type` varchar(50) NOT NULL,
  `message_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`message_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivered_at` timestamp NULL DEFAULT NULL,
  `is_delivered` tinyint(1) DEFAULT 0,
  `attempts` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ws_stats`
--

CREATE TABLE `ws_stats` (
  `id` int(11) NOT NULL,
  `stat_date` date NOT NULL,
  `total_connections` int(11) DEFAULT 0,
  `total_messages` int(11) DEFAULT 0,
  `avg_response_time_ms` int(11) DEFAULT 0,
  `peak_concurrent_users` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ws_tokens`
--

CREATE TABLE `ws_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `role` enum('admin','driver','rider') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure for view `active_rides`
--
DROP TABLE IF EXISTS `active_rides`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_rides`  AS SELECT `r`.`id` AS `id`, `r`.`user_id` AS `user_id`, `r`.`driver_id` AS `driver_id`, `r`.`pickup_location` AS `pickup_location`, `r`.`destination` AS `destination`, `r`.`pickup_lat` AS `pickup_lat`, `r`.`pickup_lng` AS `pickup_lng`, `r`.`dropoff_lat` AS `dropoff_lat`, `r`.`dropoff_lng` AS `dropoff_lng`, `r`.`fare` AS `fare`, `r`.`status` AS `status`, `r`.`payment_method` AS `payment_method`, `r`.`distance` AS `distance`, `r`.`created_at` AS `created_at`, `r`.`updated_at` AS `updated_at`, `u`.`name` AS `user_name`, `u`.`phone` AS `user_phone`, `u`.`email` AS `user_email`, `d`.`name` AS `driver_name`, `d`.`phone` AS `driver_phone`, `d`.`plate_number` AS `plate_number`, `d`.`current_lat` AS `driver_lat`, `d`.`current_lng` AS `driver_lng`, `d`.`rating` AS `driver_rating` FROM ((`ride_history` `r` left join `users` `u` on(`r`.`user_id` = `u`.`id`)) left join `tricycle_drivers` `d` on(`r`.`driver_id` = `d`.`id`)) WHERE `r`.`status` in ('pending','searching','driver_found','confirmed','arrived','in_progress') ;

-- --------------------------------------------------------

--
-- Structure for view `ws_active_connections_summary`
--
DROP TABLE IF EXISTS `ws_active_connections_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `ws_active_connections_summary`  AS SELECT `ws_connections`.`role` AS `role`, count(0) AS `active_count`, max(`ws_connections`.`last_activity`) AS `latest_activity` FROM `ws_connections` WHERE `ws_connections`.`last_activity` > current_timestamp() - interval 5 minute GROUP BY `ws_connections`.`role` ;

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
-- Indexes for table `driver_applications`
--
ALTER TABLE `driver_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_application_date` (`application_date`);

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
  ADD KEY `idx_driver_updated` (`driver_id`,`updated_at`),
  ADD KEY `idx_updated` (`updated_at`);

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
-- Indexes for table `realtime_connections`
--
ALTER TABLE `realtime_connections`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_connected` (`connected_at`);

--
-- Indexes for table `realtime_notifications`
--
ALTER TABLE `realtime_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`,`created_at`),
  ADD KEY `idx_target` (`target_type`,`target_id`);

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
  ADD KEY `idx_completed_status` (`status`,`completed_at`),
  ADD KEY `idx_driver_status` (`driver_id`,`status`),
  ADD KEY `idx_status_created` (`status`,`created_at`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `ride_notifications`
--
ALTER TABLE `ride_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ride_id` (`ride_id`),
  ADD KEY `idx_recipient` (`recipient_id`,`recipient_type`,`is_read`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_recipient_read` (`recipient_id`,`recipient_type`,`is_read`),
  ADD KEY `idx_created_read` (`created_at`,`is_read`);

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
  ADD UNIQUE KEY `tricycle_number` (`tricycle_number`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_status_location` (`status`,`current_lat`,`current_lng`),
  ADD KEY `idx_rating` (`average_rating`),
  ADD KEY `idx_plate` (`plate_number`),
  ADD KEY `idx_tricycle_number` (`tricycle_number`),
  ADD KEY `idx_online` (`is_online`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD UNIQUE KEY `facebook_id` (`facebook_id`),
  ADD UNIQUE KEY `ws_token` (`ws_token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_google_id` (`google_id`),
  ADD KEY `idx_facebook_id` (`facebook_id`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_ws_token` (`ws_token`),
  ADD KEY `idx_location` (`current_latitude`,`current_longitude`);

--
-- Indexes for table `ws_connections`
--
ALTER TABLE `ws_connections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_connection` (`connection_id`);

--
-- Indexes for table `ws_message_queue`
--
ALTER TABLE `ws_message_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_pending` (`user_id`,`is_delivered`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `ws_stats`
--
ALTER TABLE `ws_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stat_date` (`stat_date`),
  ADD KEY `idx_date` (`stat_date`);

--
-- Indexes for table `ws_tokens`
--
ALTER TABLE `ws_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_user_role` (`user_id`,`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `driver_applications`
--
ALTER TABLE `driver_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `driver_earnings`
--
ALTER TABLE `driver_earnings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `driver_locations`
--
ALTER TABLE `driver_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `fare_settings`
--
ALTER TABLE `fare_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `realtime_notifications`
--
ALTER TABLE `realtime_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=337;

--
-- AUTO_INCREMENT for table `ride_history`
--
ALTER TABLE `ride_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `ride_notifications`
--
ALTER TABLE `ride_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=424;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tricycle_drivers`
--
ALTER TABLE `tricycle_drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ws_connections`
--
ALTER TABLE `ws_connections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ws_message_queue`
--
ALTER TABLE `ws_message_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ws_stats`
--
ALTER TABLE `ws_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ws_tokens`
--
ALTER TABLE `ws_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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

--
-- Constraints for table `ws_message_queue`
--
ALTER TABLE `ws_message_queue`
  ADD CONSTRAINT `ws_message_queue_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ws_tokens`
--
ALTER TABLE `ws_tokens`
  ADD CONSTRAINT `ws_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `daily_ws_cleanup` ON SCHEDULE EVERY 1 DAY STARTS '2025-11-14 12:04:37' ON COMPLETION NOT PRESERVE ENABLE DO CALL cleanup_ws_data()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
