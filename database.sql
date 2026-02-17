-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 17, 2026 at 12:46 AM
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
(46, 16, 1, 'Guest3449', 'won', 100, 'Guest3449 won 100 WPUFF!', '2026-02-17 00:38:29');

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
(2, 2, 50, '2026-02-16 23:59:08');

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
(17, 'waiting', 0, NULL, NULL, 0, 0, NULL, 0, 0, '2026-02-17 00:38:39', NULL, NULL, NULL, 1);

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
(1, 'guest_1771214769015_4927', 'Guest3449', 610, '2026-02-17 00:38:34', '2026-02-16 10:17:06', '2026-02-17 00:38:34', 36, -35200),
(2, 'guest_1771214793015_881', 'Guest8619', 5360, '2026-02-17 00:25:02', '2026-02-16 10:22:44', '2026-02-17 00:25:02', 15, 3150);

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
(16, 16, 1, 'Guest3449', 100, 0, 0, 0, 0, 100, 200, '2026-02-17 00:38:09');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `bonus_claims`
--
ALTER TABLE `bonus_claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `game_history`
--
ALTER TABLE `game_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `game_rounds`
--
ALTER TABLE `game_rounds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `round_bets`
--
ALTER TABLE `round_bets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
