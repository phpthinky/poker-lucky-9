-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 22, 2026 at 07:57 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u506534245_gamedb`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_feed`
--

CREATE TABLE `activity_feed` (
  `id` int(11) NOT NULL,
  `round_id` int(11) DEFAULT NULL,
  `player_id` int(11) NOT NULL,
  `player_name` varchar(50) NOT NULL,
  `activity_type` varchar(30) NOT NULL,
  `amount` int(11) DEFAULT 0,
  `message` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_feed`
--

INSERT INTO `activity_feed` (`id`, `round_id`, `player_id`, `player_name`, `activity_type`, `amount`, `message`, `created_at`) VALUES
(1, NULL, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-16 13:09:14'),
(2, NULL, 1, 'Guest3449', 'won', 100, 'Guest3449 won 100 WPUFF!', '2026-02-16 13:09:27'),
(3, NULL, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-16 13:09:38'),
(4, NULL, 1, 'Guest3449', 'won', 400, 'Guest3449 won 400 WPUFF!', '2026-02-16 13:09:52'),
(5, NULL, 1, 'Guest3449', 'placed_bet', 500, 'Guest3449 bet 500 WPUFF', '2026-02-16 13:10:01'),
(6, NULL, 1, 'Guest3449', 'lost', 7500, 'Guest3449 lost 7500 WPUFF', '2026-02-16 13:10:17'),
(7, NULL, 1, 'Guest3449', 'placed_bet', 500, 'Guest3449 bet 500 WPUFF', '2026-02-16 13:11:43'),
(8, NULL, 1, 'Guest3449', 'won', 3000, 'Guest3449 won 3000 WPUFF!', '2026-02-16 13:11:57'),
(9, NULL, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-16 14:00:53'),
(10, NULL, 1, 'Guest3449', 'won', 5200, 'Guest3449 won 5200 WPUFF!', '2026-02-16 14:01:13'),
(11, NULL, 1, 'Guest3449', 'placed_bet', 500, 'Guest3449 bet 500 WPUFF', '2026-02-16 14:01:27'),
(12, NULL, 1, 'Guest3449', 'lost', -2000, 'Guest3449 lost 2000 WPUFF', '2026-02-16 14:01:47'),
(13, 1, 1, 'Guest3449', 'placed_bet', 50, 'Guest3449 bet 50 WPUFF', '2026-02-16 23:57:53'),
(14, 1, 1, 'Guest3449', 'won', 50, 'Guest3449 won 50 WPUFF!', '2026-02-16 23:58:13'),
(15, 2, 2, 'Guest8619', 'placed_bet', 100, 'Guest8619 bet 100 WPUFF', '2026-02-16 23:59:15'),
(16, 2, 2, 'Guest8619', 'won', 100, 'Guest8619 won 100 WPUFF!', '2026-02-16 23:59:35'),
(17, 3, 2, 'Guest8619', 'placed_bet', 200, 'Guest8619 bet 200 WPUFF', '2026-02-16 23:59:49'),
(18, 3, 2, 'Guest8619', 'won', 200, 'Guest8619 won 200 WPUFF!', '2026-02-17 00:00:09'),
(19, 4, 2, 'Guest8619', 'placed_bet', 500, 'Guest8619 bet 500 WPUFF', '2026-02-17 00:06:47'),
(20, 4, 2, 'Guest8619', 'won', 1000, 'Guest8619 won 1000 WPUFF!', '2026-02-17 00:07:07'),
(21, 5, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-17 00:07:21'),
(22, 5, 1, 'Guest3449', 'won', 100, 'Guest3449 won 100 WPUFF!', '2026-02-17 00:07:41'),
(23, 6, 2, 'Guest8619', 'placed_bet', 500, 'Guest8619 bet 500 WPUFF', '2026-02-17 00:12:49'),
(24, 6, 2, 'Guest8619', 'won', 500, 'Guest8619 won 500 WPUFF!', '2026-02-17 00:13:09'),
(25, 7, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-17 00:13:24'),
(26, 7, 1, 'Guest3449', 'won', 100, 'Guest3449 won 100 WPUFF!', '2026-02-17 00:13:44'),
(27, 8, 2, 'Guest8619', 'placed_bet', 500, 'Guest8619 bet 500 WPUFF', '2026-02-17 00:16:49'),
(28, 8, 2, 'Guest8619', 'won', 1000, 'Guest8619 won 1000 WPUFF!', '2026-02-17 00:17:09'),
(29, 9, 2, 'Guest8619', 'placed_bet', 1500, 'Guest8619 bet 1500 WPUFF', '2026-02-17 00:17:24'),
(30, 9, 2, 'Guest8619', 'lost', 500, 'Guest8619 lost 500 WPUFF', '2026-02-17 00:17:44'),
(31, 10, 2, 'Guest8619', 'placed_bet', 1000, 'Guest8619 bet 1000 WPUFF', '2026-02-17 00:18:07'),
(32, 10, 2, 'Guest8619', 'returned', 0, 'Guest8619 tied', '2026-02-17 00:18:27'),
(33, 11, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-17 00:20:00'),
(34, 11, 1, 'Guest3449', 'won', 200, 'Guest3449 won 200 WPUFF!', '2026-02-17 00:20:20'),
(35, 12, 1, 'Guest3449', 'placed_bet', 300, 'Guest3449 bet 300 WPUFF', '2026-02-17 00:20:38'),
(36, 12, 1, 'Guest3449', 'lost', 100, 'Guest3449 lost 100 WPUFF', '2026-02-17 00:20:58'),
(37, 13, 2, 'Guest8619', 'placed_bet', 100, 'Guest8619 bet 100 WPUFF', '2026-02-17 00:24:04'),
(38, 13, 2, 'Guest8619', 'won', 1200, 'Guest8619 won 1200 WPUFF!', '2026-02-17 00:24:24'),
(39, 14, 2, 'Guest8619', 'placed_bet', 1700, 'Guest8619 bet 1700 WPUFF', '2026-02-17 00:24:40'),
(40, 14, 2, 'Guest8619', 'lost', 500, 'Guest8619 lost 500 WPUFF', '2026-02-17 00:25:00'),
(41, 15, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-17 00:37:23'),
(42, 15, 1, 'Guest3449', 'placed_bet', 200, 'Guest3449 bet 200 WPUFF', '2026-02-17 00:37:24'),
(43, 15, 1, 'Guest3449', 'lost', 200, 'Guest3449 lost 200 WPUFF', '2026-02-17 00:37:43'),
(44, 16, 1, 'Guest3449', 'placed_bet', 300, 'Guest3449 bet 300 WPUFF', '2026-02-17 00:38:09'),
(45, 16, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-17 00:38:16'),
(46, 16, 1, 'Guest3449', 'won', 100, 'Guest3449 won 100 WPUFF!', '2026-02-17 00:38:29'),
(47, 17, 2, 'Guest8619', 'placed_bet', 500, 'Guest8619 bet 500 WPUFF', '2026-02-17 00:52:40'),
(48, 17, 2, 'Guest8619', 'placed_bet', 1000, 'Guest8619 bet 1000 WPUFF', '2026-02-17 00:52:41'),
(49, 17, 2, 'Guest8619', 'placed_bet', 1500, 'Guest8619 bet 1500 WPUFF', '2026-02-17 00:52:41'),
(50, 17, 2, 'Guest8619', 'placed_bet', 2000, 'Guest8619 bet 2000 WPUFF', '2026-02-17 00:52:41'),
(51, 17, 2, 'Guest8619', 'placed_bet', 2500, 'Guest8619 bet 2500 WPUFF', '2026-02-17 00:52:41'),
(52, 17, 2, 'Guest8619', 'lost', 2500, 'Guest8619 lost 2500 WPUFF', '2026-02-17 00:53:00'),
(53, 18, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-17 00:53:17'),
(54, 18, 2, 'Guest8619', 'placed_bet', 500, 'Guest8619 bet 500 WPUFF', '2026-02-17 00:53:19'),
(55, 18, 1, 'Guest3449', 'lost', 100, 'Guest3449 lost 100 WPUFF', '2026-02-17 00:53:37'),
(56, 18, 2, 'Guest8619', 'won', 500, 'Guest8619 won 500 WPUFF!', '2026-02-17 00:53:37'),
(57, 19, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-17 01:05:18'),
(58, 19, 1, 'Guest3449', 'won', 100, 'Guest3449 won 100 WPUFF!', '2026-02-17 01:05:38'),
(59, 20, 2, 'Guest8619', 'placed_bet', 500, 'Guest8619 bet 500 WPUFF', '2026-02-17 01:06:02'),
(60, 20, 2, 'Guest8619', 'won', 500, 'Guest8619 won 500 WPUFF!', '2026-02-17 01:06:22'),
(61, 21, 3, 'Guest3477', 'placed_bet', 100, 'Guest3477 bet 100 WPUFF', '2026-02-17 01:23:31'),
(62, 21, 3, 'Guest3477', 'placed_bet', 200, 'Guest3477 bet 200 WPUFF', '2026-02-17 01:23:31'),
(63, 21, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-17 01:23:37'),
(64, 21, 1, 'Guest3449', 'placed_bet', 200, 'Guest3449 bet 200 WPUFF', '2026-02-17 01:23:38'),
(65, 21, 1, 'Guest3449', 'won', 200, 'Guest3449 won 200 WPUFF!', '2026-02-17 01:23:51'),
(66, 21, 3, 'Guest3477', 'won', 200, 'Guest3477 won 200 WPUFF!', '2026-02-17 01:23:51'),
(67, 22, 1, 'Guest3449', 'placed_bet', 50, 'Guest3449 bet 50 WPUFF', '2026-02-17 01:51:11'),
(68, 22, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-17 01:51:14'),
(69, 22, 1, 'Guest3449', 'placed_bet', 150, 'Guest3449 bet 150 WPUFF', '2026-02-17 01:51:15'),
(70, 22, 1, 'Guest3449', 'placed_bet', 200, 'Guest3449 bet 200 WPUFF', '2026-02-17 01:51:15'),
(71, 22, 1, 'Guest3449', 'placed_bet', 250, 'Guest3449 bet 250 WPUFF', '2026-02-17 01:51:16'),
(72, 22, 1, 'Guest3449', 'placed_bet', 300, 'Guest3449 bet 300 WPUFF', '2026-02-17 01:51:16'),
(73, 22, 1, 'Guest3449', 'placed_bet', 350, 'Guest3449 bet 350 WPUFF', '2026-02-17 01:51:16'),
(74, 22, 1, 'Guest3449', 'placed_bet', 400, 'Guest3449 bet 400 WPUFF', '2026-02-17 01:51:16'),
(75, 22, 1, 'Guest3449', 'won', 400, 'Guest3449 won 400 WPUFF!', '2026-02-17 01:51:31'),
(76, 23, 1, 'Guest3449', 'placed_bet', 50, 'Guest3449 bet 50 WPUFF', '2026-02-17 01:51:57'),
(77, 23, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-17 01:52:01'),
(78, 23, 1, 'Guest3449', 'placed_bet', 150, 'Guest3449 bet 150 WPUFF', '2026-02-17 01:52:03'),
(79, 23, 1, 'Guest3449', 'lost', 150, 'Guest3449 lost 150 WPUFF', '2026-02-17 01:52:17'),
(80, 24, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-17 01:55:26'),
(81, 24, 1, 'Guest3449', 'placed_bet', 200, 'Guest3449 bet 200 WPUFF', '2026-02-17 01:55:32'),
(82, 24, 1, 'Guest3449', 'placed_bet', 300, 'Guest3449 bet 300 WPUFF', '2026-02-17 01:55:33'),
(83, 24, 1, 'Guest3449', 'lost', 300, 'Guest3449 lost 300 WPUFF', '2026-02-17 01:55:46'),
(84, 25, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-17 01:57:33'),
(85, 25, 1, 'Guest3449', 'placed_bet', 200, 'Guest3449 bet 200 WPUFF', '2026-02-17 01:57:33'),
(86, 25, 1, 'Guest3449', 'won', 200, 'Guest3449 won 200 WPUFF!', '2026-02-17 01:57:53'),
(87, 26, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-17 02:07:14'),
(88, 26, 1, 'Guest3449', 'placed_bet', 200, 'Guest3449 bet 200 WPUFF', '2026-02-17 02:07:16'),
(89, 26, 1, 'Guest3449', 'won', 200, 'Guest3449 won 200 WPUFF!', '2026-02-17 02:07:34'),
(90, 27, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-17 02:08:31'),
(91, 27, 1, 'Guest3449', 'placed_bet', 200, 'Guest3449 bet 200 WPUFF', '2026-02-17 02:08:33'),
(92, 27, 1, 'Guest3449', 'placed_bet', 300, 'Guest3449 bet 300 WPUFF', '2026-02-17 02:08:33'),
(93, 27, 1, 'Guest3449', 'placed_bet', 400, 'Guest3449 bet 400 WPUFF', '2026-02-17 02:08:33'),
(94, 27, 1, 'Guest3449', 'placed_bet', 500, 'Guest3449 bet 500 WPUFF', '2026-02-17 02:08:33'),
(95, 27, 1, 'Guest3449', 'placed_bet', 600, 'Guest3449 bet 600 WPUFF', '2026-02-17 02:08:34'),
(96, 27, 1, 'Guest3449', 'won', 600, 'Guest3449 won 600 WPUFF!', '2026-02-17 02:08:51'),
(97, 28, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-17 02:10:33'),
(98, 28, 1, 'Guest3449', 'placed_bet', 200, 'Guest3449 bet 200 WPUFF', '2026-02-17 02:10:33'),
(99, 28, 1, 'Guest3449', 'won', 200, 'Guest3449 won 200 WPUFF!', '2026-02-17 02:10:53'),
(100, 29, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-17 04:48:38'),
(101, 29, 1, 'Guest3449', 'placed_bet', 200, 'Guest3449 bet 200 WPUFF', '2026-02-17 04:48:41'),
(102, 29, 1, 'Guest3449', 'placed_bet', 300, 'Guest3449 bet 300 WPUFF', '2026-02-17 04:48:42'),
(103, 29, 1, 'Guest3449', 'won', 300, 'Guest3449 won 300 WPUFF!', '2026-02-17 04:48:58'),
(104, 30, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-17 04:49:13'),
(105, 30, 1, 'Guest3449', 'placed_bet', 200, 'Guest3449 bet 200 WPUFF', '2026-02-17 04:49:13'),
(106, 30, 1, 'Guest3449', 'placed_bet', 700, 'Guest3449 bet 700 WPUFF', '2026-02-17 04:49:19'),
(107, 30, 1, 'Guest3449', 'placed_bet', 1200, 'Guest3449 bet 1200 WPUFF', '2026-02-17 04:49:22'),
(108, 30, 1, 'Guest3449', 'lost', 1200, 'Guest3449 lost 1200 WPUFF', '2026-02-17 04:49:33'),
(109, 31, 1, 'Guest3449', 'placed_bet', 500, 'Guest3449 bet 500 WPUFF', '2026-02-17 09:27:37'),
(110, 31, 1, 'Guest3449', 'won', 500, 'Guest3449 won 500 WPUFF!', '2026-02-17 09:27:57'),
(111, 32, 2, 'Guest8619', 'placed_bet', 100, 'Guest8619 bet 100 WPUFF', '2026-02-21 10:46:38'),
(112, 32, 2, 'Guest8619', 'placed_bet', 600, 'Guest8619 bet 600 WPUFF', '2026-02-21 10:46:39'),
(113, 32, 2, 'Guest8619', 'lost', 600, 'Guest8619 lost 600 WPUFF', '2026-02-21 10:46:58'),
(114, 33, 2, 'Guest8619', 'placed_bet', 500, 'Guest8619 bet 500 WPUFF', '2026-02-21 10:47:19'),
(115, 33, 2, 'Guest8619', 'placed_bet', 1000, 'Guest8619 bet 1000 WPUFF', '2026-02-21 10:47:30'),
(116, 33, 2, 'Guest8619', 'lost', 1000, 'Guest8619 lost 1000 WPUFF', '2026-02-21 10:47:39'),
(117, 34, 2, 'Guest8619', 'placed_bet', 500, 'Guest8619 bet 500 WPUFF', '2026-02-21 10:48:05'),
(118, 34, 2, 'Guest8619', 'won', 500, 'Guest8619 won 500 WPUFF!', '2026-02-21 10:48:25'),
(119, 35, 2, 'Guest8619', 'placed_bet', 500, 'Guest8619 bet 500 WPUFF', '2026-02-21 10:49:22'),
(120, 35, 2, 'Guest8619', 'won', 500, 'Guest8619 won 500 WPUFF!', '2026-02-21 10:49:42'),
(121, 36, 2, 'Guest8619', 'placed_bet', 500, 'Guest8619 bet 500 WPUFF', '2026-02-21 10:50:10'),
(122, 36, 2, 'Guest8619', 'placed_bet', 1000, 'Guest8619 bet 1000 WPUFF', '2026-02-21 10:50:12'),
(123, 36, 2, 'Guest8619', 'returned', 0, 'Guest8619 tied', '2026-02-21 10:50:30'),
(124, 37, 2, 'Guest8619', 'placed_bet', 500, 'Guest8619 bet 500 WPUFF', '2026-02-21 10:50:48'),
(125, 37, 2, 'Guest8619', 'placed_bet', 1000, 'Guest8619 bet 1000 WPUFF', '2026-02-21 10:50:49'),
(126, 37, 2, 'Guest8619', 'placed_bet', 1500, 'Guest8619 bet 1500 WPUFF', '2026-02-21 10:50:52'),
(127, 37, 2, 'Guest8619', 'lost', 1500, 'Guest8619 lost 1500 WPUFF', '2026-02-21 10:51:08'),
(128, 38, 2, 'Guest8619', 'placed_bet', 500, 'Guest8619 bet 500 WPUFF', '2026-02-21 10:51:27'),
(129, 38, 2, 'Guest8619', 'placed_bet', 500, 'Guest8619 bet 500 WPUFF', '2026-02-21 10:51:30'),
(130, 38, 2, 'Guest8619', 'lost', 500, 'Guest8619 lost 500 WPUFF', '2026-02-21 10:51:47'),
(131, 39, 2, 'Guest8619', 'placed_bet', 500, 'Guest8619 bet 500 WPUFF', '2026-02-21 10:55:56'),
(132, 39, 2, 'Guest8619', 'lost', 500, 'Guest8619 lost 500 WPUFF', '2026-02-21 10:56:16'),
(133, 40, 1, 'Guest3449', 'placed_bet', 50, 'Guest3449 bet 50 WPUFF', '2026-02-22 07:51:52'),
(134, 40, 1, 'Guest3449', 'placed_bet', 100, 'Guest3449 bet 100 WPUFF', '2026-02-22 07:51:53'),
(135, 40, 1, 'Guest3449', 'lost', 100, 'Guest3449 lost 100 WPUFF', '2026-02-22 07:52:12');

-- --------------------------------------------------------

--
-- Table structure for table `bonus_claims`
--

CREATE TABLE `bonus_claims` (
  `id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `bonus_amount` int(11) NOT NULL,
  `claimed_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bonus_claims`
--

INSERT INTO `bonus_claims` (`id`, `player_id`, `bonus_amount`, `claimed_at`) VALUES
(1, 1, 50, '2026-02-16 23:57:46'),
(2, 2, 50, '2026-02-16 23:59:08'),
(3, 1, 20, '2026-02-17 04:49:03'),
(4, 1, 40, '2026-02-17 09:27:26'),
(5, 1, 50, '2026-02-19 08:15:32'),
(6, 2, 50, '2026-02-21 10:40:12'),
(7, 1, 50, '2026-02-22 07:51:39');

-- --------------------------------------------------------

--
-- Table structure for table `game_history`
--

CREATE TABLE `game_history` (
  `id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `bet_player` int(11) DEFAULT 0,
  `bet_banker` int(11) DEFAULT 0,
  `bet_tie` int(11) DEFAULT 0,
  `bet_player_pair` int(11) DEFAULT 0,
  `bet_banker_pair` int(11) DEFAULT 0,
  `total_bet` int(11) NOT NULL,
  `total_won` int(11) NOT NULL,
  `player_hand_total` int(11) DEFAULT NULL,
  `banker_hand_total` int(11) DEFAULT NULL,
  `result` varchar(20) DEFAULT NULL,
  `played_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `game_rounds`
--

CREATE TABLE `game_rounds` (
  `id` int(11) NOT NULL,
  `round_status` varchar(20) NOT NULL DEFAULT 'betting',
  `timer_remaining` int(11) DEFAULT 20,
  `player_cards` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`player_cards`)),
  `banker_cards` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`banker_cards`)),
  `player_total` int(11) DEFAULT 0,
  `banker_total` int(11) DEFAULT 0,
  `result` varchar(50) DEFAULT NULL,
  `is_player_pair` tinyint(1) DEFAULT 0,
  `is_banker_pair` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `started_at` timestamp NULL DEFAULT NULL,
  `dealing_ends_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `round_number` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `game_rounds`
--

INSERT INTO `game_rounds` (`id`, `round_status`, `timer_remaining`, `player_cards`, `banker_cards`, `player_total`, `banker_total`, `result`, `is_player_pair`, `is_banker_pair`, `created_at`, `started_at`, `dealing_ends_at`, `finished_at`, `round_number`) VALUES
(1, 'finished', 20, '[{\"suit\":\"\\u2665\",\"value\":\"6\",\"display\":\"6\\u2665\"},{\"suit\":\"\\u2663\",\"value\":\"9\",\"display\":\"9\\u2663\"},{\"suit\":\"\\u2660\",\"value\":\"A\",\"display\":\"A\\u2660\"}]', '[{\"suit\":\"\\u2663\",\"value\":\"A\",\"display\":\"A\\u2663\"},{\"suit\":\"\\u2663\",\"value\":\"J\",\"display\":\"J\\u2663\"},{\"suit\":\"\\u2666\",\"value\":\"9\",\"display\":\"9\\u2666\"}]', 6, 0, 'PLAYER_WINS', 0, 0, '2026-02-16 23:56:49', '2026-02-16 23:57:53', '2026-02-16 23:58:18', '2026-02-16 23:58:19', 1),
(2, 'finished', 20, '[{\"suit\":\"\\u2660\",\"value\":\"9\",\"display\":\"9\\u2660\"},{\"suit\":\"\\u2666\",\"value\":\"J\",\"display\":\"J\\u2666\"}]', '[{\"suit\":\"\\u2666\",\"value\":\"10\",\"display\":\"10\\u2666\"},{\"suit\":\"\\u2660\",\"value\":\"5\",\"display\":\"5\\u2660\"},{\"suit\":\"\\u2660\",\"value\":\"A\",\"display\":\"A\\u2660\"}]', 9, 6, 'PLAYER_WINS', 0, 0, '2026-02-16 23:58:25', '2026-02-16 23:59:15', '2026-02-16 23:59:40', '2026-02-16 23:59:40', 1),
(3, 'finished', 20, '[{\"suit\":\"\\u2660\",\"value\":\"2\",\"display\":\"2\\u2660\"},{\"suit\":\"\\u2666\",\"value\":\"6\",\"display\":\"6\\u2666\"}]', '[{\"suit\":\"\\u2663\",\"value\":\"4\",\"display\":\"4\\u2663\"},{\"suit\":\"\\u2660\",\"value\":\"7\",\"display\":\"7\\u2660\"},{\"suit\":\"\\u2660\",\"value\":\"Q\",\"display\":\"Q\\u2660\"}]', 8, 1, 'PLAYER_WINS', 0, 0, '2026-02-16 23:59:45', '2026-02-16 23:59:49', '2026-02-17 00:00:14', '2026-02-17 00:00:14', 1),
(4, 'finished', 20, '[{\"suit\":\"\\u2660\",\"value\":\"3\",\"display\":\"3\\u2660\"},{\"suit\":\"\\u2665\",\"value\":\"6\",\"display\":\"6\\u2665\"}]', '[{\"suit\":\"\\u2660\",\"value\":\"7\",\"display\":\"7\\u2660\"},{\"suit\":\"\\u2666\",\"value\":\"Q\",\"display\":\"Q\\u2666\"}]', 9, 7, 'PLAYER_WINS', 0, 0, '2026-02-17 00:00:19', '2026-02-17 00:06:47', '2026-02-17 00:07:12', '2026-02-17 00:07:12', 1),
(5, 'finished', 20, '[{\"suit\":\"\\u2666\",\"value\":\"4\",\"display\":\"4\\u2666\"},{\"suit\":\"\\u2666\",\"value\":\"K\",\"display\":\"K\\u2666\"},{\"suit\":\"\\u2660\",\"value\":\"8\",\"display\":\"8\\u2660\"}]', '[{\"suit\":\"\\u2660\",\"value\":\"6\",\"display\":\"6\\u2660\"},{\"suit\":\"\\u2663\",\"value\":\"A\",\"display\":\"A\\u2663\"}]', 2, 7, 'BANKER_WINS', 0, 0, '2026-02-17 00:07:17', '2026-02-17 00:07:21', '2026-02-17 00:07:46', '2026-02-17 00:07:46', 1),
(6, 'finished', 20, '[{\"suit\":\"\\u2666\",\"value\":\"5\",\"display\":\"5\\u2666\"},{\"suit\":\"\\u2663\",\"value\":\"8\",\"display\":\"8\\u2663\"},{\"suit\":\"\\u2660\",\"value\":\"3\",\"display\":\"3\\u2660\"}]', '[{\"suit\":\"\\u2663\",\"value\":\"10\",\"display\":\"10\\u2663\"},{\"suit\":\"\\u2666\",\"value\":\"2\",\"display\":\"2\\u2666\"},{\"suit\":\"\\u2666\",\"value\":\"Q\",\"display\":\"Q\\u2666\"}]', 6, 2, 'PLAYER_WINS', 0, 0, '2026-02-17 00:07:51', '2026-02-17 00:12:49', '2026-02-17 00:13:14', '2026-02-17 00:13:14', 1),
(7, 'finished', 20, '[{\"suit\":\"\\u2660\",\"value\":\"A\",\"display\":\"A\\u2660\"},{\"suit\":\"\\u2665\",\"value\":\"10\",\"display\":\"10\\u2665\"},{\"suit\":\"\\u2660\",\"value\":\"8\",\"display\":\"8\\u2660\"}]', '[{\"suit\":\"\\u2660\",\"value\":\"Q\",\"display\":\"Q\\u2660\"},{\"suit\":\"\\u2663\",\"value\":\"J\",\"display\":\"J\\u2663\"},{\"suit\":\"\\u2666\",\"value\":\"8\",\"display\":\"8\\u2666\"}]', 9, 8, 'PLAYER_WINS', 0, 0, '2026-02-17 00:13:19', '2026-02-17 00:13:24', '2026-02-17 00:13:49', '2026-02-17 00:13:49', 1),
(8, 'finished', 20, '[{\"suit\":\"\\u2666\",\"value\":\"5\",\"display\":\"5\\u2666\"},{\"suit\":\"\\u2663\",\"value\":\"3\",\"display\":\"3\\u2663\"}]', '[{\"suit\":\"\\u2666\",\"value\":\"K\",\"display\":\"K\\u2666\"},{\"suit\":\"\\u2666\",\"value\":\"A\",\"display\":\"A\\u2666\"},{\"suit\":\"\\u2666\",\"value\":\"9\",\"display\":\"9\\u2666\"}]', 8, 0, 'PLAYER_WINS', 0, 0, '2026-02-17 00:13:54', '2026-02-17 00:16:49', '2026-02-17 00:17:14', '2026-02-17 00:17:14', 1),
(9, 'finished', 20, '[{\"suit\":\"\\u2660\",\"value\":\"J\",\"display\":\"J\\u2660\"},{\"suit\":\"\\u2665\",\"value\":\"Q\",\"display\":\"Q\\u2665\"},{\"suit\":\"\\u2665\",\"value\":\"J\",\"display\":\"J\\u2665\"}]', '[{\"suit\":\"\\u2665\",\"value\":\"K\",\"display\":\"K\\u2665\"},{\"suit\":\"\\u2660\",\"value\":\"7\",\"display\":\"7\\u2660\"}]', 0, 7, 'BANKER_WINS', 0, 0, '2026-02-17 00:17:19', '2026-02-17 00:17:24', '2026-02-17 00:17:49', '2026-02-17 00:17:49', 1),
(10, 'finished', 20, '[{\"suit\":\"\\u2663\",\"value\":\"9\",\"display\":\"9\\u2663\"},{\"suit\":\"\\u2665\",\"value\":\"6\",\"display\":\"6\\u2665\"},{\"suit\":\"\\u2665\",\"value\":\"5\",\"display\":\"5\\u2665\"}]', '[{\"suit\":\"\\u2660\",\"value\":\"3\",\"display\":\"3\\u2660\"},{\"suit\":\"\\u2665\",\"value\":\"J\",\"display\":\"J\\u2665\"},{\"suit\":\"\\u2665\",\"value\":\"7\",\"display\":\"7\\u2665\"}]', 0, 0, 'TIE', 0, 0, '2026-02-17 00:17:54', '2026-02-17 00:18:07', '2026-02-17 00:18:32', '2026-02-17 00:18:32', 1),
(11, 'finished', 20, '[{\"suit\":\"\\u2665\",\"value\":\"5\",\"display\":\"5\\u2665\"},{\"suit\":\"\\u2666\",\"value\":\"2\",\"display\":\"2\\u2666\"}]', '[{\"suit\":\"\\u2666\",\"value\":\"A\",\"display\":\"A\\u2666\"},{\"suit\":\"\\u2660\",\"value\":\"A\",\"display\":\"A\\u2660\"},{\"suit\":\"\\u2665\",\"value\":\"8\",\"display\":\"8\\u2665\"}]', 7, 0, 'PLAYER_WINS', 0, 1, '2026-02-17 00:18:37', '2026-02-17 00:20:00', '2026-02-17 00:20:25', '2026-02-17 00:20:25', 1),
(12, 'finished', 20, '[{\"suit\":\"\\u2660\",\"value\":\"5\",\"display\":\"5\\u2660\"},{\"suit\":\"\\u2666\",\"value\":\"6\",\"display\":\"6\\u2666\"},{\"suit\":\"\\u2665\",\"value\":\"A\",\"display\":\"A\\u2665\"}]', '[{\"suit\":\"\\u2665\",\"value\":\"3\",\"display\":\"3\\u2665\"},{\"suit\":\"\\u2666\",\"value\":\"A\",\"display\":\"A\\u2666\"},{\"suit\":\"\\u2663\",\"value\":\"5\",\"display\":\"5\\u2663\"}]', 2, 9, 'BANKER_WINS', 0, 0, '2026-02-17 00:20:30', '2026-02-17 00:20:38', '2026-02-17 00:21:03', '2026-02-17 00:21:03', 1),
(13, 'finished', 20, '[{\"suit\":\"\\u2663\",\"value\":\"7\",\"display\":\"7\\u2663\"},{\"suit\":\"\\u2660\",\"value\":\"4\",\"display\":\"4\\u2660\"},{\"suit\":\"\\u2660\",\"value\":\"6\",\"display\":\"6\\u2660\"}]', '[{\"suit\":\"\\u2663\",\"value\":\"Q\",\"display\":\"Q\\u2663\"},{\"suit\":\"\\u2665\",\"value\":\"10\",\"display\":\"10\\u2665\"},{\"suit\":\"\\u2666\",\"value\":\"2\",\"display\":\"2\\u2666\"}]', 7, 2, 'PLAYER_WINS', 0, 0, '2026-02-17 00:21:08', '2026-02-17 00:24:04', '2026-02-17 00:24:29', '2026-02-17 00:24:29', 1),
(14, 'finished', 20, '[{\"suit\":\"\\u2665\",\"value\":\"A\",\"display\":\"A\\u2665\"},{\"suit\":\"\\u2665\",\"value\":\"5\",\"display\":\"5\\u2665\"}]', '[{\"suit\":\"\\u2660\",\"value\":\"K\",\"display\":\"K\\u2660\"},{\"suit\":\"\\u2663\",\"value\":\"K\",\"display\":\"K\\u2663\"},{\"suit\":\"\\u2666\",\"value\":\"8\",\"display\":\"8\\u2666\"}]', 6, 8, 'BANKER_WINS', 0, 1, '2026-02-17 00:24:34', '2026-02-17 00:24:40', '2026-02-17 00:25:05', '2026-02-17 00:25:05', 1),
(15, 'finished', 20, '[{\"suit\":\"\\u2666\",\"value\":\"10\",\"display\":\"10\\u2666\"},{\"suit\":\"\\u2666\",\"value\":\"3\",\"display\":\"3\\u2666\"},{\"suit\":\"\\u2665\",\"value\":\"J\",\"display\":\"J\\u2665\"}]', '[{\"suit\":\"\\u2663\",\"value\":\"Q\",\"display\":\"Q\\u2663\"},{\"suit\":\"\\u2663\",\"value\":\"7\",\"display\":\"7\\u2663\"}]', 3, 7, 'BANKER_WINS', 0, 0, '2026-02-17 00:25:10', '2026-02-17 00:37:23', '2026-02-17 00:37:48', '2026-02-17 00:37:48', 1),
(16, 'finished', 20, '[{\"suit\":\"\\u2663\",\"value\":\"4\",\"display\":\"4\\u2663\"},{\"suit\":\"\\u2665\",\"value\":\"6\",\"display\":\"6\\u2665\"},{\"suit\":\"\\u2666\",\"value\":\"9\",\"display\":\"9\\u2666\"}]', '[{\"suit\":\"\\u2663\",\"value\":\"K\",\"display\":\"K\\u2663\"},{\"suit\":\"\\u2665\",\"value\":\"5\",\"display\":\"5\\u2665\"},{\"suit\":\"\\u2665\",\"value\":\"2\",\"display\":\"2\\u2665\"}]', 9, 7, 'PLAYER_WINS', 0, 0, '2026-02-17 00:37:53', '2026-02-17 00:38:09', '2026-02-17 00:38:34', '2026-02-17 00:38:34', 1),
(17, 'finished', 20, '[{\"suit\":\"\\u2666\",\"value\":\"8\",\"display\":\"8\\u2666\"},{\"suit\":\"\\u2660\",\"value\":\"5\",\"display\":\"5\\u2660\"},{\"suit\":\"\\u2666\",\"value\":\"9\",\"display\":\"9\\u2666\"}]', '[{\"suit\":\"\\u2666\",\"value\":\"2\",\"display\":\"2\\u2666\"},{\"suit\":\"\\u2666\",\"value\":\"5\",\"display\":\"5\\u2666\"}]', 2, 7, 'BANKER_WINS', 0, 0, '2026-02-17 00:38:39', '2026-02-17 00:52:40', '2026-02-17 00:53:05', '2026-02-17 00:53:05', 1),
(18, 'finished', 20, '[{\"suit\":\"\\u2665\",\"value\":\"A\",\"display\":\"A\\u2665\"},{\"suit\":\"\\u2660\",\"value\":\"3\",\"display\":\"3\\u2660\"},{\"suit\":\"\\u2660\",\"value\":\"8\",\"display\":\"8\\u2660\"}]', '[{\"suit\":\"\\u2666\",\"value\":\"4\",\"display\":\"4\\u2666\"},{\"suit\":\"\\u2665\",\"value\":\"3\",\"display\":\"3\\u2665\"}]', 2, 7, 'BANKER_WINS', 0, 0, '2026-02-17 00:53:10', '2026-02-17 00:53:17', '2026-02-17 00:53:42', '2026-02-17 00:53:42', 1),
(19, 'finished', 20, '[{\"suit\":\"\\u2663\",\"value\":\"8\",\"display\":\"8\\u2663\"},{\"suit\":\"\\u2666\",\"value\":\"7\",\"display\":\"7\\u2666\"},{\"suit\":\"\\u2666\",\"value\":\"2\",\"display\":\"2\\u2666\"}]', '[{\"suit\":\"\\u2666\",\"value\":\"10\",\"display\":\"10\\u2666\"},{\"suit\":\"\\u2666\",\"value\":\"6\",\"display\":\"6\\u2666\"}]', 7, 6, 'PLAYER_WINS', 0, 0, '2026-02-17 00:53:47', '2026-02-17 01:05:18', '2026-02-17 01:05:43', '2026-02-17 01:05:43', 1),
(20, 'finished', 20, '[{\"suit\":\"\\u2663\",\"value\":\"10\",\"display\":\"10\\u2663\"},{\"suit\":\"\\u2660\",\"value\":\"9\",\"display\":\"9\\u2660\"}]', '[{\"suit\":\"\\u2666\",\"value\":\"4\",\"display\":\"4\\u2666\"},{\"suit\":\"\\u2665\",\"value\":\"4\",\"display\":\"4\\u2665\"}]', 9, 8, 'PLAYER_WINS', 0, 1, '2026-02-17 01:05:48', '2026-02-17 01:06:02', '2026-02-17 01:06:27', '2026-02-17 01:06:27', 1),
(21, 'finished', 20, '[{\"suit\":\"\\u2663\",\"value\":\"9\",\"display\":\"9\\u2663\"},{\"suit\":\"\\u2666\",\"value\":\"10\",\"display\":\"10\\u2666\"}]', '[{\"suit\":\"\\u2660\",\"value\":\"Q\",\"display\":\"Q\\u2660\"},{\"suit\":\"\\u2660\",\"value\":\"3\",\"display\":\"3\\u2660\"},{\"suit\":\"\\u2666\",\"value\":\"5\",\"display\":\"5\\u2666\"}]', 9, 8, 'PLAYER_WINS', 0, 0, '2026-02-17 01:06:32', '2026-02-17 01:23:31', '2026-02-17 01:23:56', '2026-02-17 01:23:56', 1),
(22, 'finished', 20, '[{\"suit\":\"\\u2665\",\"value\":\"10\",\"display\":\"10\\u2665\"},{\"suit\":\"\\u2660\",\"value\":\"7\",\"display\":\"7\\u2660\"}]', '[{\"suit\":\"\\u2666\",\"value\":\"J\",\"display\":\"J\\u2666\"},{\"suit\":\"\\u2663\",\"value\":\"J\",\"display\":\"J\\u2663\"},{\"suit\":\"\\u2665\",\"value\":\"2\",\"display\":\"2\\u2665\"}]', 7, 2, 'PLAYER_WINS', 0, 1, '2026-02-17 01:24:01', '2026-02-17 01:51:11', '2026-02-17 01:51:36', '2026-02-17 01:51:36', 1),
(23, 'finished', 20, '[{\"suit\":\"\\u2660\",\"value\":\"8\",\"display\":\"8\\u2660\"},{\"suit\":\"\\u2665\",\"value\":\"Q\",\"display\":\"Q\\u2665\"}]', '[{\"suit\":\"\\u2665\",\"value\":\"9\",\"display\":\"9\\u2665\"},{\"suit\":\"\\u2663\",\"value\":\"K\",\"display\":\"K\\u2663\"}]', 8, 9, 'BANKER_WINS', 0, 0, '2026-02-17 01:51:41', '2026-02-17 01:51:57', '2026-02-17 01:52:22', '2026-02-17 01:52:22', 1),
(24, 'finished', 20, '[{\"suit\":\"\\u2665\",\"value\":\"6\",\"display\":\"6\\u2665\"},{\"suit\":\"\\u2660\",\"value\":\"7\",\"display\":\"7\\u2660\"},{\"suit\":\"\\u2665\",\"value\":\"8\",\"display\":\"8\\u2665\"}]', '[{\"suit\":\"\\u2663\",\"value\":\"9\",\"display\":\"9\\u2663\"},{\"suit\":\"\\u2663\",\"value\":\"Q\",\"display\":\"Q\\u2663\"}]', 1, 9, 'BANKER_WINS', 0, 0, '2026-02-17 01:52:27', '2026-02-17 01:55:26', '2026-02-17 01:55:51', '2026-02-17 01:55:51', 1),
(25, 'finished', 20, '[{\"suit\":\"\\u2666\",\"value\":\"4\",\"display\":\"4\\u2666\"},{\"suit\":\"\\u2663\",\"value\":\"7\",\"display\":\"7\\u2663\"},{\"suit\":\"\\u2665\",\"value\":\"8\",\"display\":\"8\\u2665\"}]', '[{\"suit\":\"\\u2666\",\"value\":\"K\",\"display\":\"K\\u2666\"},{\"suit\":\"\\u2663\",\"value\":\"5\",\"display\":\"5\\u2663\"},{\"suit\":\"\\u2666\",\"value\":\"9\",\"display\":\"9\\u2666\"}]', 9, 4, 'PLAYER_WINS', 0, 0, '2026-02-17 01:55:56', '2026-02-17 01:57:33', '2026-02-17 01:57:58', '2026-02-17 01:57:58', 1),
(26, 'finished', 20, '[{\"suit\":\"\\u2666\",\"value\":\"K\",\"display\":\"K\\u2666\"},{\"suit\":\"\\u2665\",\"value\":\"3\",\"display\":\"3\\u2665\"},{\"suit\":\"\\u2663\",\"value\":\"2\",\"display\":\"2\\u2663\"}]', '[{\"suit\":\"\\u2665\",\"value\":\"K\",\"display\":\"K\\u2665\"},{\"suit\":\"\\u2666\",\"value\":\"J\",\"display\":\"J\\u2666\"},{\"suit\":\"\\u2665\",\"value\":\"2\",\"display\":\"2\\u2665\"}]', 5, 2, 'PLAYER_WINS', 0, 0, '2026-02-17 01:58:04', '2026-02-17 02:07:14', '2026-02-17 02:07:39', '2026-02-17 02:07:39', 1),
(27, 'finished', 20, '[{\"suit\":\"\\u2660\",\"value\":\"3\",\"display\":\"3\\u2660\"},{\"suit\":\"\\u2663\",\"value\":\"J\",\"display\":\"J\\u2663\"},{\"suit\":\"\\u2663\",\"value\":\"3\",\"display\":\"3\\u2663\"}]', '[{\"suit\":\"\\u2666\",\"value\":\"6\",\"display\":\"6\\u2666\"},{\"suit\":\"\\u2666\",\"value\":\"5\",\"display\":\"5\\u2666\"},{\"suit\":\"\\u2660\",\"value\":\"A\",\"display\":\"A\\u2660\"}]', 6, 2, 'PLAYER_WINS', 0, 0, '2026-02-17 02:07:44', '2026-02-17 02:08:31', '2026-02-17 02:08:56', '2026-02-17 02:08:56', 1),
(28, 'finished', 20, '[{\"suit\":\"\\u2663\",\"value\":\"8\",\"display\":\"8\\u2663\"},{\"suit\":\"\\u2665\",\"value\":\"Q\",\"display\":\"Q\\u2665\"}]', '[{\"suit\":\"\\u2663\",\"value\":\"6\",\"display\":\"6\\u2663\"},{\"suit\":\"\\u2665\",\"value\":\"6\",\"display\":\"6\\u2665\"},{\"suit\":\"\\u2660\",\"value\":\"3\",\"display\":\"3\\u2660\"}]', 8, 5, 'PLAYER_WINS', 0, 1, '2026-02-17 02:09:01', '2026-02-17 02:10:33', '2026-02-17 02:10:58', '2026-02-17 02:10:58', 1),
(29, 'finished', 20, '[{\"suit\":\"\\u2660\",\"value\":\"5\",\"display\":\"5\\u2660\"},{\"suit\":\"\\u2660\",\"value\":\"3\",\"display\":\"3\\u2660\"}]', '[{\"suit\":\"\\u2666\",\"value\":\"4\",\"display\":\"4\\u2666\"},{\"suit\":\"\\u2665\",\"value\":\"J\",\"display\":\"J\\u2665\"},{\"suit\":\"\\u2665\",\"value\":\"3\",\"display\":\"3\\u2665\"}]', 8, 7, 'PLAYER_WINS', 0, 0, '2026-02-17 02:11:03', '2026-02-17 04:48:38', '2026-02-17 04:49:03', '2026-02-17 04:49:03', 1),
(30, 'finished', 20, '[{\"suit\":\"\\u2663\",\"value\":\"7\",\"display\":\"7\\u2663\"},{\"suit\":\"\\u2660\",\"value\":\"Q\",\"display\":\"Q\\u2660\"}]', '[{\"suit\":\"\\u2666\",\"value\":\"Q\",\"display\":\"Q\\u2666\"},{\"suit\":\"\\u2663\",\"value\":\"9\",\"display\":\"9\\u2663\"}]', 7, 9, 'BANKER_WINS', 0, 0, '2026-02-17 04:49:11', '2026-02-17 04:49:13', '2026-02-17 04:49:38', '2026-02-17 04:49:38', 1),
(31, 'finished', 20, '[{\"suit\":\"\\u2663\",\"value\":\"5\",\"display\":\"5\\u2663\"},{\"suit\":\"\\u2665\",\"value\":\"8\",\"display\":\"8\\u2665\"},{\"suit\":\"\\u2663\",\"value\":\"6\",\"display\":\"6\\u2663\"}]', '[{\"suit\":\"\\u2665\",\"value\":\"3\",\"display\":\"3\\u2665\"},{\"suit\":\"\\u2663\",\"value\":\"J\",\"display\":\"J\\u2663\"},{\"suit\":\"\\u2660\",\"value\":\"2\",\"display\":\"2\\u2660\"}]', 9, 5, 'PLAYER_WINS', 0, 0, '2026-02-17 04:49:43', '2026-02-17 09:27:37', '2026-02-17 09:28:02', '2026-02-17 09:28:02', 1),
(32, 'finished', 20, '[{\"suit\":\"\\u2663\",\"value\":\"10\",\"display\":\"10\\u2663\"},{\"suit\":\"\\u2666\",\"value\":\"J\",\"display\":\"J\\u2666\"},{\"suit\":\"\\u2665\",\"value\":\"10\",\"display\":\"10\\u2665\"}]', '[{\"suit\":\"\\u2663\",\"value\":\"6\",\"display\":\"6\\u2663\"},{\"suit\":\"\\u2666\",\"value\":\"7\",\"display\":\"7\\u2666\"},{\"suit\":\"\\u2660\",\"value\":\"3\",\"display\":\"3\\u2660\"}]', 0, 6, 'BANKER_WINS', 0, 0, '2026-02-17 09:28:07', '2026-02-21 10:46:38', '2026-02-21 10:47:03', '2026-02-21 10:47:03', 1),
(33, 'finished', 20, '[{\"suit\":\"\\u2660\",\"value\":\"7\",\"display\":\"7\\u2660\"},{\"suit\":\"\\u2663\",\"value\":\"10\",\"display\":\"10\\u2663\"}]', '[{\"suit\":\"\\u2660\",\"value\":\"6\",\"display\":\"6\\u2660\"},{\"suit\":\"\\u2665\",\"value\":\"8\",\"display\":\"8\\u2665\"},{\"suit\":\"\\u2660\",\"value\":\"4\",\"display\":\"4\\u2660\"}]', 7, 8, 'BANKER_WINS', 0, 0, '2026-02-21 10:47:08', '2026-02-21 10:47:19', '2026-02-21 10:47:44', '2026-02-21 10:47:44', 1),
(34, 'finished', 20, '[{\"suit\":\"\\u2663\",\"value\":\"J\",\"display\":\"J\\u2663\"},{\"suit\":\"\\u2663\",\"value\":\"Q\",\"display\":\"Q\\u2663\"},{\"suit\":\"\\u2665\",\"value\":\"5\",\"display\":\"5\\u2665\"}]', '[{\"suit\":\"\\u2666\",\"value\":\"2\",\"display\":\"2\\u2666\"},{\"suit\":\"\\u2665\",\"value\":\"3\",\"display\":\"3\\u2665\"},{\"suit\":\"\\u2666\",\"value\":\"5\",\"display\":\"5\\u2666\"}]', 5, 0, 'PLAYER_WINS', 0, 0, '2026-02-21 10:47:49', '2026-02-21 10:48:05', '2026-02-21 10:48:30', '2026-02-21 10:48:30', 1),
(35, 'finished', 20, '[{\"suit\":\"\\u2663\",\"value\":\"5\",\"display\":\"5\\u2663\"},{\"suit\":\"\\u2666\",\"value\":\"2\",\"display\":\"2\\u2666\"}]', '[{\"suit\":\"\\u2666\",\"value\":\"6\",\"display\":\"6\\u2666\"},{\"suit\":\"\\u2665\",\"value\":\"5\",\"display\":\"5\\u2665\"},{\"suit\":\"\\u2660\",\"value\":\"A\",\"display\":\"A\\u2660\"}]', 7, 2, 'PLAYER_WINS', 0, 0, '2026-02-21 10:48:35', '2026-02-21 10:49:22', '2026-02-21 10:49:47', '2026-02-21 10:49:47', 1),
(36, 'finished', 20, '[{\"suit\":\"\\u2666\",\"value\":\"3\",\"display\":\"3\\u2666\"},{\"suit\":\"\\u2663\",\"value\":\"4\",\"display\":\"4\\u2663\"}]', '[{\"suit\":\"\\u2663\",\"value\":\"10\",\"display\":\"10\\u2663\"},{\"suit\":\"\\u2666\",\"value\":\"6\",\"display\":\"6\\u2666\"}]', 7, 6, 'PLAYER_WINS', 0, 0, '2026-02-21 10:49:52', '2026-02-21 10:50:10', '2026-02-21 10:50:35', '2026-02-21 10:50:35', 1),
(37, 'finished', 20, '[{\"suit\":\"\\u2665\",\"value\":\"3\",\"display\":\"3\\u2665\"},{\"suit\":\"\\u2665\",\"value\":\"10\",\"display\":\"10\\u2665\"},{\"suit\":\"\\u2660\",\"value\":\"9\",\"display\":\"9\\u2660\"}]', '[{\"suit\":\"\\u2666\",\"value\":\"3\",\"display\":\"3\\u2666\"},{\"suit\":\"\\u2666\",\"value\":\"10\",\"display\":\"10\\u2666\"},{\"suit\":\"\\u2666\",\"value\":\"J\",\"display\":\"J\\u2666\"}]', 2, 3, 'BANKER_WINS', 0, 0, '2026-02-21 10:50:40', '2026-02-21 10:50:48', '2026-02-21 10:51:13', '2026-02-21 10:51:13', 1),
(38, 'finished', 20, '[{\"suit\":\"\\u2665\",\"value\":\"5\",\"display\":\"5\\u2665\"},{\"suit\":\"\\u2666\",\"value\":\"Q\",\"display\":\"Q\\u2666\"},{\"suit\":\"\\u2666\",\"value\":\"5\",\"display\":\"5\\u2666\"}]', '[{\"suit\":\"\\u2666\",\"value\":\"7\",\"display\":\"7\\u2666\"},{\"suit\":\"\\u2665\",\"value\":\"J\",\"display\":\"J\\u2665\"}]', 0, 7, 'BANKER_WINS', 0, 0, '2026-02-21 10:51:18', '2026-02-21 10:51:27', '2026-02-21 10:51:52', '2026-02-21 10:51:52', 1),
(39, 'finished', 20, '[{\"suit\":\"\\u2665\",\"value\":\"10\",\"display\":\"10\\u2665\"},{\"suit\":\"\\u2666\",\"value\":\"5\",\"display\":\"5\\u2666\"},{\"suit\":\"\\u2666\",\"value\":\"K\",\"display\":\"K\\u2666\"}]', '[{\"suit\":\"\\u2665\",\"value\":\"K\",\"display\":\"K\\u2665\"},{\"suit\":\"\\u2663\",\"value\":\"4\",\"display\":\"4\\u2663\"},{\"suit\":\"\\u2665\",\"value\":\"5\",\"display\":\"5\\u2665\"}]', 5, 9, 'BANKER_WINS', 0, 0, '2026-02-21 10:51:57', '2026-02-21 10:55:56', '2026-02-21 10:56:21', '2026-02-21 10:56:21', 1),
(40, 'finished', 20, '[{\"suit\":\"\\u2660\",\"value\":\"7\",\"display\":\"7\\u2660\"},{\"suit\":\"\\u2660\",\"value\":\"4\",\"display\":\"4\\u2660\"},{\"suit\":\"\\u2665\",\"value\":\"6\",\"display\":\"6\\u2665\"}]', '[{\"suit\":\"\\u2663\",\"value\":\"7\",\"display\":\"7\\u2663\"},{\"suit\":\"\\u2665\",\"value\":\"4\",\"display\":\"4\\u2665\"},{\"suit\":\"\\u2666\",\"value\":\"7\",\"display\":\"7\\u2666\"}]', 7, 8, 'BANKER_WINS', 0, 0, '2026-02-22 07:51:39', '2026-02-22 07:51:52', '2026-02-22 07:52:17', '2026-02-22 07:52:17', 1),
(41, 'waiting', 0, NULL, NULL, 0, 0, NULL, 0, 0, '2026-02-22 07:52:22', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `id` int(11) NOT NULL,
  `guest_id` varchar(100) NOT NULL,
  `player_name` varchar(50) NOT NULL,
  `balance` int(11) NOT NULL DEFAULT 1000,
  `last_visit` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `total_games_played` int(11) DEFAULT 0,
  `total_winnings` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`id`, `guest_id`, `player_name`, `balance`, `last_visit`, `created_at`, `updated_at`, `total_games_played`, `total_winnings`) VALUES
(1, 'guest_1771214769015_4927', 'Guest3449', 1620, '2026-02-22 07:52:16', '2026-02-16 10:17:06', '2026-02-22 07:52:16', 50, -34350),
(2, 'guest_1771214793015_881', 'Guest8619', 810, '2026-02-21 10:56:20', '2026-02-16 10:22:44', '2026-02-21 10:56:20', 26, -1450),
(3, 'guest_1771291394774_651', 'Guest3477', 1200, '2026-02-17 01:23:56', '2026-02-17 01:23:14', '2026-02-17 01:23:56', 1, 200);

-- --------------------------------------------------------

--
-- Table structure for table `round_bets`
--

CREATE TABLE `round_bets` (
  `id` int(11) NOT NULL,
  `round_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `player_name` varchar(50) NOT NULL,
  `bet_player` int(11) DEFAULT 0,
  `bet_banker` int(11) DEFAULT 0,
  `bet_tie` int(11) DEFAULT 0,
  `bet_player_pair` int(11) DEFAULT 0,
  `bet_banker_pair` int(11) DEFAULT 0,
  `total_bet` int(11) NOT NULL,
  `total_won` int(11) DEFAULT 0,
  `placed_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `round_bets`
--

INSERT INTO `round_bets` (`id`, `round_id`, `player_id`, `player_name`, `bet_player`, `bet_banker`, `bet_tie`, `bet_player_pair`, `bet_banker_pair`, `total_bet`, `total_won`, `placed_at`) VALUES
(1, 1, 1, 'Guest3449', 50, 0, 0, 0, 0, 50, 100, '2026-02-16 23:57:53'),
(2, 2, 2, 'Guest8619', 100, 0, 0, 0, 0, 100, 200, '2026-02-16 23:59:15'),
(3, 3, 2, 'Guest8619', 200, 0, 0, 0, 0, 200, 400, '2026-02-16 23:59:49'),
(4, 4, 2, 'Guest8619', 1000, 0, 0, 0, 0, 1000, 2000, '2026-02-17 00:06:47'),
(5, 5, 1, 'Guest3449', 0, 100, 0, 0, 0, 100, 200, '2026-02-17 00:07:21'),
(6, 6, 2, 'Guest8619', 500, 0, 0, 0, 0, 500, 1000, '2026-02-17 00:12:49'),
(7, 7, 1, 'Guest3449', 100, 0, 0, 0, 0, 100, 200, '2026-02-17 00:13:24'),
(8, 8, 2, 'Guest8619', 1000, 0, 0, 0, 0, 1000, 2000, '2026-02-17 00:16:49'),
(9, 9, 2, 'Guest8619', 500, 0, 0, 0, 0, 500, 0, '2026-02-17 00:17:24'),
(10, 10, 2, 'Guest8619', 500, 0, 0, 0, 0, 500, 500, '2026-02-17 00:18:07'),
(11, 11, 1, 'Guest3449', 200, 0, 0, 0, 0, 200, 400, '2026-02-17 00:20:00'),
(12, 12, 1, 'Guest3449', 100, 0, 0, 0, 0, 100, 0, '2026-02-17 00:20:38'),
(13, 13, 2, 'Guest8619', 1200, 0, 0, 0, 0, 1200, 2400, '2026-02-17 00:24:04'),
(14, 14, 2, 'Guest8619', 500, 0, 0, 0, 0, 500, 0, '2026-02-17 00:24:40'),
(15, 15, 1, 'Guest3449', 200, 0, 0, 0, 0, 200, 0, '2026-02-17 00:37:23'),
(16, 16, 1, 'Guest3449', 100, 0, 0, 0, 0, 100, 200, '2026-02-17 00:38:09'),
(17, 17, 2, 'Guest8619', 2500, 0, 0, 0, 0, 2500, 0, '2026-02-17 00:52:40'),
(18, 18, 1, 'Guest3449', 100, 0, 0, 0, 0, 100, 0, '2026-02-17 00:53:17'),
(19, 18, 2, 'Guest8619', 0, 500, 0, 0, 0, 500, 1000, '2026-02-17 00:53:19'),
(20, 19, 1, 'Guest3449', 100, 0, 0, 0, 0, 100, 200, '2026-02-17 01:05:18'),
(21, 20, 2, 'Guest8619', 500, 0, 0, 0, 0, 500, 1000, '2026-02-17 01:06:02'),
(22, 21, 3, 'Guest3477', 200, 0, 0, 0, 0, 200, 400, '2026-02-17 01:23:31'),
(23, 21, 1, 'Guest3449', 200, 0, 0, 0, 0, 200, 400, '2026-02-17 01:23:37'),
(24, 22, 1, 'Guest3449', 400, 0, 0, 0, 0, 400, 800, '2026-02-17 01:51:11'),
(25, 23, 1, 'Guest3449', 150, 0, 0, 0, 0, 150, 0, '2026-02-17 01:51:57'),
(26, 24, 1, 'Guest3449', 300, 0, 0, 0, 0, 300, 0, '2026-02-17 01:55:26'),
(27, 25, 1, 'Guest3449', 200, 0, 0, 0, 0, 200, 400, '2026-02-17 01:57:33'),
(28, 26, 1, 'Guest3449', 200, 0, 0, 0, 0, 200, 400, '2026-02-17 02:07:14'),
(29, 27, 1, 'Guest3449', 600, 0, 0, 0, 0, 600, 1200, '2026-02-17 02:08:31'),
(30, 28, 1, 'Guest3449', 200, 0, 0, 0, 0, 200, 400, '2026-02-17 02:10:33'),
(31, 29, 1, 'Guest3449', 300, 0, 0, 0, 0, 300, 600, '2026-02-17 04:48:38'),
(32, 30, 1, 'Guest3449', 1200, 0, 0, 0, 0, 1200, 0, '2026-02-17 04:49:13'),
(33, 31, 1, 'Guest3449', 500, 0, 0, 0, 0, 500, 1000, '2026-02-17 09:27:37'),
(34, 32, 2, 'Guest8619', 600, 0, 0, 0, 0, 600, 0, '2026-02-21 10:46:38'),
(35, 33, 2, 'Guest8619', 1000, 0, 0, 0, 0, 1000, 0, '2026-02-21 10:47:19'),
(36, 34, 2, 'Guest8619', 500, 0, 0, 0, 0, 500, 1000, '2026-02-21 10:48:05'),
(37, 35, 2, 'Guest8619', 500, 0, 0, 0, 0, 500, 1000, '2026-02-21 10:49:22'),
(38, 36, 2, 'Guest8619', 500, 0, 500, 0, 0, 1000, 1000, '2026-02-21 10:50:10'),
(39, 37, 2, 'Guest8619', 1000, 0, 500, 0, 0, 1500, 0, '2026-02-21 10:50:48'),
(40, 38, 2, 'Guest8619', 500, 0, 0, 0, 0, 500, 0, '2026-02-21 10:51:27'),
(41, 39, 2, 'Guest8619', 500, 0, 0, 0, 0, 500, 0, '2026-02-21 10:55:56'),
(42, 40, 1, 'Guest3449', 50, 0, 50, 0, 0, 100, 0, '2026-02-22 07:51:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_feed`
--
ALTER TABLE `activity_feed`
  ADD PRIMARY KEY (`id`),
  ADD KEY `player_id` (`player_id`),
  ADD KEY `round_id` (`round_id`),
  ADD KEY `idx_created` (`created_at` DESC);

--
-- Indexes for table `bonus_claims`
--
ALTER TABLE `bonus_claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_player_claimed` (`player_id`,`claimed_at`);

--
-- Indexes for table `game_history`
--
ALTER TABLE `game_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_player_id` (`player_id`),
  ADD KEY `idx_played_at` (`played_at`);

--
-- Indexes for table `game_rounds`
--
ALTER TABLE `game_rounds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`round_status`),
  ADD KEY `idx_created` (`created_at` DESC);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guest_id` (`guest_id`),
  ADD KEY `idx_guest_id` (`guest_id`),
  ADD KEY `idx_balance` (`balance` DESC),
  ADD KEY `idx_last_visit` (`last_visit`);

--
-- Indexes for table `round_bets`
--
ALTER TABLE `round_bets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_player_round` (`round_id`,`player_id`),
  ADD KEY `player_id` (`player_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_feed`
--
ALTER TABLE `activity_feed`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `bonus_claims`
--
ALTER TABLE `bonus_claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `game_history`
--
ALTER TABLE `game_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `game_rounds`
--
ALTER TABLE `game_rounds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `round_bets`
--
ALTER TABLE `round_bets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_feed`
--
ALTER TABLE `activity_feed`
  ADD CONSTRAINT `activity_feed_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_feed_ibfk_2` FOREIGN KEY (`round_id`) REFERENCES `game_rounds` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bonus_claims`
--
ALTER TABLE `bonus_claims`
  ADD CONSTRAINT `bonus_claims_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `game_history`
--
ALTER TABLE `game_history`
  ADD CONSTRAINT `game_history_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `round_bets`
--
ALTER TABLE `round_bets`
  ADD CONSTRAINT `round_bets_ibfk_1` FOREIGN KEY (`round_id`) REFERENCES `game_rounds` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `round_bets_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
