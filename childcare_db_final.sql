-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 18, 2025 at 01:17 PM
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
-- Database: `childcare_db_bk`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `child_id` int(11) DEFAULT NULL,
  `activity_type_id` int(11) DEFAULT NULL,
  `date_recorded` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `child_id`, `activity_type_id`, `date_recorded`) VALUES
(5, 1, 1, '2024-12-04 18:34:06'),
(6, 3, 1, '2024-12-04 19:22:08'),
(7, 1, 1, '2024-12-04 19:22:21'),
(8, 2, 1, '2024-12-10 19:20:54'),
(9, 3, 1, '2024-12-10 19:31:27'),
(10, 2, 1, '2024-12-10 19:35:04'),
(11, 2, 1, '2024-12-10 19:36:05'),
(12, 2, 1, '2024-12-10 19:47:02'),
(13, 2, 1, '2024-12-10 19:47:16'),
(14, 1, 1, '2024-12-17 19:39:32'),
(15, 2, 1, '2024-12-22 15:39:14'),
(16, 2, 1, '2024-12-27 12:16:01'),
(17, 2, 1, '2024-12-28 10:20:22'),
(18, 1, 1, '2024-12-29 13:09:46'),
(19, 1, 1, '2024-12-29 19:17:48'),
(20, 1, 1, '2024-12-30 12:08:06'),
(21, 1, 1, '2024-12-31 08:17:22'),
(22, 1, 1, '2024-12-31 08:17:30'),
(23, 3, 1, '2024-12-31 08:17:34'),
(24, 2, 1, '2024-12-31 09:11:17'),
(25, 1, 1, '2025-01-02 12:52:04'),
(26, 3, 1, '2025-01-16 20:01:50'),
(27, 1, 1, '2025-01-16 22:49:20');

-- --------------------------------------------------------

--
-- Table structure for table `activities_activity`
--

CREATE TABLE `activities_activity` (
  `id` int(11) NOT NULL,
  `activities_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities_activity`
--

INSERT INTO `activities_activity` (`id`, `activities_id`, `activity_id`) VALUES
(1, 5, 1),
(2, 5, 2),
(3, 6, 1),
(4, 7, 2),
(5, 8, 3),
(6, 9, 1),
(7, 10, 1),
(8, 11, 3),
(9, 12, 1),
(10, 13, 4),
(11, 14, 2),
(12, 15, 1),
(13, 15, 2),
(14, 15, 3),
(15, 15, 4),
(16, 16, 1),
(17, 17, 2),
(18, 18, 3),
(19, 19, 1),
(20, 20, 4),
(21, 21, 1),
(22, 22, 3),
(23, 23, 4),
(24, 24, 2),
(25, 25, 3),
(26, 26, 1),
(27, 26, 2),
(28, 27, 4);

-- --------------------------------------------------------

--
-- Table structure for table `activity_definitions`
--

CREATE TABLE `activity_definitions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_definitions`
--

INSERT INTO `activity_definitions` (`id`, `name`) VALUES
(1, 'Drawing'),
(2, 'Painting'),
(3, 'Sticking'),
(4, 'Sensory');

-- --------------------------------------------------------

--
-- Table structure for table `activity_types`
--

CREATE TABLE `activity_types` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_types`
--

INSERT INTO `activity_types` (`id`, `name`) VALUES
(1, 'Arts');

-- --------------------------------------------------------

--
-- Table structure for table `activity_type_links`
--

CREATE TABLE `activity_type_links` (
  `id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `activity_type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_type_links`
--

INSERT INTO `activity_type_links` (`id`, `activity_id`, `activity_type_id`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 1),
(4, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_absent` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `child_id`, `date`, `time_in`, `time_out`, `created_at`, `updated_at`, `is_absent`) VALUES
(1, 1, '2025-01-01', '10:15:00', '14:05:00', '2025-01-01 22:15:52', '2025-01-01 22:15:52', 0),
(2, 3, '2025-01-01', '08:10:00', '14:50:00', '2025-01-01 22:16:21', '2025-01-01 22:16:31', 0),
(4, 1, '2025-01-02', '08:49:00', '22:15:00', '2025-01-02 12:46:47', '2025-01-02 12:46:47', 0),
(5, 1, '2025-01-13', '08:50:00', '14:53:00', '2025-01-13 19:36:20', '2025-01-13 19:36:20', 0),
(6, 1, '2025-01-14', '09:15:00', '15:15:00', '2025-01-14 22:09:26', '2025-01-14 23:26:40', 0),
(7, 3, '2025-01-14', '08:10:00', '17:00:00', '2025-01-14 23:19:55', '2025-01-14 23:19:55', 0),
(8, 1, '2025-01-15', '08:00:00', '15:00:00', '2025-01-15 19:40:10', '2025-01-15 22:12:06', 1),
(9, 3, '2025-01-15', '09:45:00', '00:00:00', '2025-01-15 19:48:33', '2025-01-15 20:09:48', 1),
(11, 3, '2025-01-16', '08:15:00', NULL, '2025-01-16 19:23:34', '2025-01-16 22:48:50', 1),
(12, 1, '2025-01-16', '08:13:00', '15:15:00', '2025-01-16 19:28:38', '2025-01-16 22:48:55', 0);

-- --------------------------------------------------------

--
-- Table structure for table `children`
--

CREATE TABLE `children` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `parent_id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `photo` varchar(255) DEFAULT NULL,
  `allergies` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `children`
--

INSERT INTO `children` (`id`, `name`, `date_of_birth`, `parent_id`, `room_id`, `created_at`, `photo`, `allergies`) VALUES
(1, 'Timmy', '2002-02-02', 14, 1, '2024-10-14 18:39:42', '6786da007f87b_IMG_0507.JPG', 'NONE'),
(2, 'Johnny', '2022-05-06', 14, 2, '2024-10-28 16:20:41', NULL, NULL),
(3, 'Bea', '2023-11-11', 16, 1, '2024-10-28 16:22:22', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `child_gallery`
--

CREATE TABLE `child_gallery` (
  `id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `child_gallery`
--

INSERT INTO `child_gallery` (`id`, `child_id`, `photo`, `uploaded_at`) VALUES
(1, 1, '6773cdaa50fad_profile photo.jpg', '2024-12-31 10:55:38'),
(2, 1, '6773cdb0a6e5c_Dashboard.png', '2024-12-31 10:55:44'),
(3, 1, '6773cdba6bec6_profile photo.jpg', '2024-12-31 10:55:54'),
(4, 1, '6786d0952cb4a_profile photo.jpg', '2025-01-14 21:01:09'),
(5, 1, '6786da007f87b_IMG_0507.JPG', '2025-01-14 21:41:20');

-- --------------------------------------------------------

--
-- Table structure for table `diet`
--

CREATE TABLE `diet` (
  `id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `meal_type_id` int(11) NOT NULL,
  `date_recorded` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diet`
--

INSERT INTO `diet` (`id`, `child_id`, `meal_type_id`, `date_recorded`) VALUES
(16, 1, 1, '2024-11-24 15:25:26'),
(17, 2, 1, '2024-11-24 15:26:37'),
(18, 1, 2, '2024-11-24 16:18:15'),
(19, 3, 2, '2024-11-24 16:28:00'),
(20, 2, 3, '2024-12-10 19:29:21'),
(21, 1, 2, '2024-12-17 19:39:21'),
(22, 2, 2, '2024-12-22 15:39:09'),
(23, 1, 1, '2024-12-28 10:23:54'),
(24, 3, 2, '2024-12-28 10:24:04'),
(25, 1, 2, '2024-12-29 19:17:30'),
(26, 1, 1, '2024-12-30 12:07:40'),
(27, 2, 1, '2024-12-30 12:10:28'),
(28, 1, 2, '2024-12-30 12:12:47'),
(29, 1, 2, '2024-12-30 12:13:21'),
(30, 2, 1, '2024-12-30 12:21:34'),
(31, 2, 1, '2024-12-30 12:21:36'),
(32, 1, 2, '2024-12-30 23:10:07'),
(33, 1, 1, '2024-12-31 08:10:43'),
(34, 1, 2, '2024-12-31 08:13:21'),
(35, 3, 3, '2024-12-31 08:13:30'),
(36, 1, 3, '2024-12-31 08:51:53'),
(37, 2, 1, '2024-12-31 09:11:12'),
(38, 1, 1, '2025-01-02 12:51:53'),
(39, 1, 1, '2025-01-12 17:33:48'),
(40, 1, 1, '2025-01-16 19:23:09'),
(41, 1, 2, '2025-01-16 22:49:15');

-- --------------------------------------------------------

--
-- Table structure for table `diet_food_items`
--

CREATE TABLE `diet_food_items` (
  `id` int(11) NOT NULL,
  `diet_id` int(11) NOT NULL,
  `food_item_id` int(11) NOT NULL,
  `quantity` float NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diet_food_items`
--

INSERT INTO `diet_food_items` (`id`, `diet_id`, `food_item_id`, `quantity`) VALUES
(20, 16, 21, 0),
(21, 17, 23, 0),
(22, 18, 22, 0),
(23, 19, 21, 0),
(24, 19, 26, 0),
(25, 20, 24, 0),
(26, 21, 26, 0),
(27, 21, 28, 0),
(28, 22, 27, 0),
(29, 23, 23, 0),
(30, 24, 26, 0),
(31, 25, 26, 0),
(32, 26, 23, 0),
(33, 27, 28, 0),
(34, 28, 21, 0),
(35, 28, 22, 0),
(36, 28, 26, 0),
(37, 29, 21, 0),
(38, 29, 22, 0),
(39, 29, 26, 0),
(40, 30, 28, 0),
(41, 31, 28, 0),
(42, 32, 22, 0),
(43, 33, 28, 0),
(44, 34, 22, 0),
(45, 35, 24, 0),
(46, 36, 21, 0),
(47, 37, 21, 0),
(48, 38, 23, 0),
(49, 39, 23, 0),
(50, 40, 21, 0),
(51, 41, 22, 0);

-- --------------------------------------------------------

--
-- Table structure for table `food_items`
--

CREATE TABLE `food_items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_items`
--

INSERT INTO `food_items` (`id`, `name`) VALUES
(23, 'Banana'),
(21, 'Bread'),
(22, 'Chicken'),
(27, 'Curry'),
(25, 'Ice cream'),
(28, 'Juice'),
(26, 'Steak'),
(29, 'Water'),
(24, 'Yogurt');

-- --------------------------------------------------------

--
-- Table structure for table `food_item_meal_type`
--

CREATE TABLE `food_item_meal_type` (
  `id` int(11) NOT NULL,
  `food_item_id` int(11) NOT NULL,
  `meal_type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_item_meal_type`
--

INSERT INTO `food_item_meal_type` (`id`, `food_item_id`, `meal_type_id`) VALUES
(2, 21, 1),
(3, 21, 2),
(4, 21, 3),
(5, 22, 2),
(6, 23, 1),
(7, 24, 3),
(8, 25, 3),
(9, 26, 2),
(10, 27, 2),
(11, 28, 1),
(12, 28, 2),
(13, 28, 3),
(14, 29, 1),
(15, 29, 2),
(16, 29, 3);

-- --------------------------------------------------------

--
-- Table structure for table `health_entries`
--

CREATE TABLE `health_entries` (
  `id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `temperature` float NOT NULL,
  `date_recorded` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_entries`
--

INSERT INTO `health_entries` (`id`, `child_id`, `temperature`, `date_recorded`) VALUES
(1, 3, 37, '2024-12-11 20:03:45'),
(2, 2, 35.7, '2024-12-13 19:56:04'),
(3, 1, 36, '2024-12-13 19:56:28'),
(4, 3, 38, '2024-12-13 19:56:49'),
(5, 1, 34, '2024-12-17 19:39:56'),
(6, 2, 36, '2024-12-22 15:39:25'),
(7, 2, 37, '2024-12-28 10:20:31'),
(8, 2, 37, '2024-12-29 19:24:26'),
(9, 2, 36.4, '2024-12-30 12:06:53'),
(10, 1, 37, '2024-12-30 12:07:13'),
(11, 1, 37, '2024-12-30 12:13:39'),
(12, 1, 36, '2024-12-31 08:17:48'),
(13, 3, 37, '2024-12-31 08:17:54'),
(14, 2, 36, '2024-12-31 09:11:23'),
(15, 1, 37, '2025-01-02 12:47:34'),
(16, 1, 35, '2025-01-16 22:49:27');

-- --------------------------------------------------------

--
-- Table structure for table `health_medications`
--

CREATE TABLE `health_medications` (
  `id` int(11) NOT NULL,
  `health_entry_id` int(11) NOT NULL,
  `medication_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_medications`
--

INSERT INTO `health_medications` (`id`, `health_entry_id`, `medication_id`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 1),
(4, 4, 1),
(5, 5, 5),
(6, 7, 3),
(7, 8, 3),
(8, 9, 2),
(9, 10, 4),
(10, 11, 3),
(11, 12, 3),
(12, 13, 2),
(13, 14, 2),
(14, 15, 1),
(15, 16, 3);

-- --------------------------------------------------------

--
-- Table structure for table `health_symptoms`
--

CREATE TABLE `health_symptoms` (
  `id` int(11) NOT NULL,
  `health_entry_id` int(11) NOT NULL,
  `symptom_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_symptoms`
--

INSERT INTO `health_symptoms` (`id`, `health_entry_id`, `symptom_id`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 1),
(4, 4, 1),
(5, 5, 3),
(6, 6, 3),
(7, 7, 3),
(8, 8, 6),
(9, 9, 4),
(10, 10, 2),
(11, 11, 5),
(12, 12, 3),
(13, 13, 5),
(14, 14, 3),
(15, 15, 2),
(16, 16, 3);

-- --------------------------------------------------------

--
-- Table structure for table `meal_types`
--

CREATE TABLE `meal_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_types`
--

INSERT INTO `meal_types` (`id`, `name`) VALUES
(1, 'Breakfast'),
(2, 'Lunch'),
(3, 'Snack');

-- --------------------------------------------------------

--
-- Table structure for table `medications`
--

CREATE TABLE `medications` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medications`
--

INSERT INTO `medications` (`id`, `name`) VALUES
(2, 'Ibuprofen'),
(3, 'Lemsip'),
(4, 'Lupocet'),
(1, 'Paracetamol'),
(5, 'Pill'),
(6, 'Water');

-- --------------------------------------------------------

--
-- Table structure for table `nappy_entries`
--

CREATE TABLE `nappy_entries` (
  `id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `nappy_type_id` int(11) NOT NULL,
  `date_recorded` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nappy_entries`
--

INSERT INTO `nappy_entries` (`id`, `child_id`, `nappy_type_id`, `date_recorded`) VALUES
(1, 1, 1, '2024-12-22 09:35:12'),
(3, 2, 2, '2024-12-22 13:27:46'),
(4, 2, 2, '2024-12-22 13:29:57'),
(5, 2, 1, '2024-12-22 13:30:20'),
(6, 2, 3, '2024-12-22 13:37:30'),
(7, 2, 3, '2024-12-22 15:22:22'),
(8, 2, 3, '2024-12-22 15:25:56'),
(9, 2, 3, '2024-12-22 15:39:31'),
(10, 3, 1, '2024-12-28 10:22:29'),
(11, 1, 2, '2024-12-30 12:07:52'),
(12, 1, 3, '2024-12-31 08:18:04'),
(13, 1, 2, '2024-12-31 08:18:06'),
(14, 3, 2, '2024-12-31 08:18:09'),
(15, 2, 2, '2024-12-31 09:11:33'),
(16, 1, 2, '2025-01-02 12:47:42'),
(17, 1, 1, '2025-01-16 19:23:14'),
(18, 1, 2, '2025-01-16 22:49:30');

-- --------------------------------------------------------

--
-- Table structure for table `nappy_types`
--

CREATE TABLE `nappy_types` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nappy_types`
--

INSERT INTO `nappy_types` (`id`, `name`) VALUES
(1, 'wet'),
(2, 'soiled'),
(3, 'mixed');

-- --------------------------------------------------------

--
-- Table structure for table `private_chat`
--

CREATE TABLE `private_chat` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `conversation_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `private_chat`
--

INSERT INTO `private_chat` (`id`, `room_id`, `parent_id`, `sender_id`, `message`, `photo`, `sent_at`, `conversation_id`) VALUES
(1, 1, 14, 14, 'Hi Domi', NULL, '2025-01-15 22:17:57', 0),
(2, 1, 1, 1, 'Hi, what can I do for you?', NULL, '2025-01-15 22:23:03', 0),
(3, 1, 16, 16, 'Hi, this is Parent2', NULL, '2025-01-15 23:07:28', 0);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'admin'),
(3, 'parent'),
(2, 'provider');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Room 1', 'Room number one.', '2024-10-14 18:38:41'),
(2, 'Room 2', 'Second room', '2024-10-28 14:13:07'),
(3, 'Room 3', 'Third room', '2024-10-28 14:13:07');

-- --------------------------------------------------------

--
-- Table structure for table `room_chat`
--

CREATE TABLE `room_chat` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_chat`
--

INSERT INTO `room_chat` (`id`, `room_id`, `sender_id`, `message`, `photo`, `sent_at`) VALUES
(1, 1, 1, 'Hello World', NULL, '2025-01-15 20:51:25'),
(2, 1, 1, 'How are you?', NULL, '2025-01-15 22:11:16'),
(3, 1, 14, 'Good!', NULL, '2025-01-15 22:15:49'),
(4, 1, 1, 'Glad to hear it!', NULL, '2025-01-15 22:51:05'),
(5, 1, 1, 'How are you?', NULL, '2025-01-15 23:30:13'),
(6, 1, 1, 'Hi', NULL, '2025-01-15 23:33:14'),
(7, 1, 16, 'Hello!', NULL, '2025-01-15 23:33:34'),
(8, 1, 14, 'Good!', NULL, '2025-01-15 23:34:32'),
(9, 1, 1, 'Nice!', NULL, '2025-01-15 23:34:48'),
(10, 1, 1, 'Pic', NULL, '2025-01-15 23:37:59');

-- --------------------------------------------------------

--
-- Table structure for table `sleeping_entries`
--

CREATE TABLE `sleeping_entries` (
  `id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `sleep_start` datetime NOT NULL,
  `sleep_end` datetime DEFAULT NULL,
  `duration` int(11) GENERATED ALWAYS AS (timestampdiff(MINUTE,`sleep_start`,`sleep_end`)) STORED,
  `date_recorded` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sleeping_entries`
--

INSERT INTO `sleeping_entries` (`id`, `child_id`, `sleep_start`, `sleep_end`, `date_recorded`) VALUES
(10, 3, '2025-01-16 22:26:00', '2025-01-16 22:27:00', '2025-01-16'),
(11, 1, '2025-01-16 22:27:00', '2025-01-16 22:27:00', '2025-01-16');

-- --------------------------------------------------------

--
-- Table structure for table `symptoms`
--

CREATE TABLE `symptoms` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `symptoms`
--

INSERT INTO `symptoms` (`id`, `name`) VALUES
(3, 'Cough'),
(6, 'Cry'),
(2, 'Fever'),
(5, 'Red Cheeks'),
(1, 'Sore Throat'),
(4, 'Sweat');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role_id`, `created_at`) VALUES
(1, 'domi@gmail.com', '$2y$10$9cca4uWkjza9D8ooP.NY0uXfRS8VxOL0l/vFRUCGW/gmR8LnSny..', 1, '2024-10-14 18:43:05'),
(14, 'parent@gmail.com', '$2y$10$kvntZxL99lngohl5Ai6MZeNG3WTlgswzwgS7bK6jccBWCGTcG7Qo.', 3, '2024-10-28 14:19:03'),
(16, 'parent2@gmail.com', '$2y$10$tG4vEKHxYCAAWTKmUWrDnuG9zmiUZhOvRvHhEhWwsZITkOSzMgUue', 3, '2024-10-28 16:21:31'),
(17, 'dominik.klasic@gmail.com', '$2y$10$usr1xotYNHS./4GfH92ATOQp83nPg9NeD5y17Lm4g2stigTOtDVJm', 3, '2025-01-13 18:59:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_id` (`child_id`),
  ADD KEY `activity_type_id` (`activity_type_id`);

--
-- Indexes for table `activities_activity`
--
ALTER TABLE `activities_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activities_id` (`activities_id`),
  ADD KEY `activity_id` (`activity_id`);

--
-- Indexes for table `activity_definitions`
--
ALTER TABLE `activity_definitions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `activity_types`
--
ALTER TABLE `activity_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `activity_type_links`
--
ALTER TABLE `activity_type_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_id` (`activity_id`),
  ADD KEY `activity_type_id` (`activity_type_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`child_id`,`date`);

--
-- Indexes for table `children`
--
ALTER TABLE `children`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `child_gallery`
--
ALTER TABLE `child_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `diet`
--
ALTER TABLE `diet`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meal_type_id` (`meal_type_id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `diet_food_items`
--
ALTER TABLE `diet_food_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `diet_id` (`diet_id`),
  ADD KEY `food_item_id` (`food_item_id`);

--
-- Indexes for table `food_items`
--
ALTER TABLE `food_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `food_item_meal_type`
--
ALTER TABLE `food_item_meal_type`
  ADD PRIMARY KEY (`id`),
  ADD KEY `food_item_id` (`food_item_id`),
  ADD KEY `meal_type_id` (`meal_type_id`);

--
-- Indexes for table `health_entries`
--
ALTER TABLE `health_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `health_medications`
--
ALTER TABLE `health_medications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `health_entry_id` (`health_entry_id`),
  ADD KEY `medication_id` (`medication_id`);

--
-- Indexes for table `health_symptoms`
--
ALTER TABLE `health_symptoms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `health_entry_id` (`health_entry_id`),
  ADD KEY `symptom_id` (`symptom_id`);

--
-- Indexes for table `meal_types`
--
ALTER TABLE `meal_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `nappy_entries`
--
ALTER TABLE `nappy_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_id` (`child_id`),
  ADD KEY `nappy_type_id` (`nappy_type_id`);

--
-- Indexes for table `nappy_types`
--
ALTER TABLE `nappy_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `private_chat`
--
ALTER TABLE `private_chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `conversation_id` (`conversation_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room_chat`
--
ALTER TABLE `room_chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `sleeping_entries`
--
ALTER TABLE `sleeping_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `symptoms`
--
ALTER TABLE `symptoms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `activities_activity`
--
ALTER TABLE `activities_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `activity_definitions`
--
ALTER TABLE `activity_definitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `activity_types`
--
ALTER TABLE `activity_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `activity_type_links`
--
ALTER TABLE `activity_type_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `children`
--
ALTER TABLE `children`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `child_gallery`
--
ALTER TABLE `child_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `diet`
--
ALTER TABLE `diet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `diet_food_items`
--
ALTER TABLE `diet_food_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `food_items`
--
ALTER TABLE `food_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `food_item_meal_type`
--
ALTER TABLE `food_item_meal_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `health_entries`
--
ALTER TABLE `health_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `health_medications`
--
ALTER TABLE `health_medications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `health_symptoms`
--
ALTER TABLE `health_symptoms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `meal_types`
--
ALTER TABLE `meal_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `medications`
--
ALTER TABLE `medications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `nappy_entries`
--
ALTER TABLE `nappy_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `nappy_types`
--
ALTER TABLE `nappy_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `private_chat`
--
ALTER TABLE `private_chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `room_chat`
--
ALTER TABLE `room_chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sleeping_entries`
--
ALTER TABLE `sleeping_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `symptoms`
--
ALTER TABLE `symptoms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`),
  ADD CONSTRAINT `activities_ibfk_2` FOREIGN KEY (`activity_type_id`) REFERENCES `activity_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `activities_activity`
--
ALTER TABLE `activities_activity`
  ADD CONSTRAINT `activities_activity_ibfk_1` FOREIGN KEY (`activities_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activities_activity_ibfk_2` FOREIGN KEY (`activity_id`) REFERENCES `activity_definitions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `activity_type_links`
--
ALTER TABLE `activity_type_links`
  ADD CONSTRAINT `activity_type_links_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activity_definitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_type_links_ibfk_2` FOREIGN KEY (`activity_type_id`) REFERENCES `activity_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`);

--
-- Constraints for table `children`
--
ALTER TABLE `children`
  ADD CONSTRAINT `children_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `children_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `child_gallery`
--
ALTER TABLE `child_gallery`
  ADD CONSTRAINT `child_gallery_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `diet`
--
ALTER TABLE `diet`
  ADD CONSTRAINT `diet_ibfk_1` FOREIGN KEY (`meal_type_id`) REFERENCES `meal_types` (`id`),
  ADD CONSTRAINT `diet_ibfk_2` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`);

--
-- Constraints for table `diet_food_items`
--
ALTER TABLE `diet_food_items`
  ADD CONSTRAINT `diet_food_items_ibfk_1` FOREIGN KEY (`diet_id`) REFERENCES `diet` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `diet_food_items_ibfk_2` FOREIGN KEY (`food_item_id`) REFERENCES `food_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `food_item_meal_type`
--
ALTER TABLE `food_item_meal_type`
  ADD CONSTRAINT `food_item_meal_type_ibfk_1` FOREIGN KEY (`food_item_id`) REFERENCES `food_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `food_item_meal_type_ibfk_2` FOREIGN KEY (`meal_type_id`) REFERENCES `meal_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `health_entries`
--
ALTER TABLE `health_entries`
  ADD CONSTRAINT `health_entries_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `health_medications`
--
ALTER TABLE `health_medications`
  ADD CONSTRAINT `health_medications_ibfk_1` FOREIGN KEY (`health_entry_id`) REFERENCES `health_entries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `health_medications_ibfk_2` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `health_symptoms`
--
ALTER TABLE `health_symptoms`
  ADD CONSTRAINT `health_symptoms_ibfk_1` FOREIGN KEY (`health_entry_id`) REFERENCES `health_entries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `health_symptoms_ibfk_2` FOREIGN KEY (`symptom_id`) REFERENCES `symptoms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `nappy_entries`
--
ALTER TABLE `nappy_entries`
  ADD CONSTRAINT `nappy_entries_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nappy_entries_ibfk_2` FOREIGN KEY (`nappy_type_id`) REFERENCES `nappy_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `private_chat`
--
ALTER TABLE `private_chat`
  ADD CONSTRAINT `private_chat_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `private_chat_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `private_chat_ibfk_3` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `room_chat`
--
ALTER TABLE `room_chat`
  ADD CONSTRAINT `room_chat_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `room_chat_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `sleeping_entries`
--
ALTER TABLE `sleeping_entries`
  ADD CONSTRAINT `sleeping_entries_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
