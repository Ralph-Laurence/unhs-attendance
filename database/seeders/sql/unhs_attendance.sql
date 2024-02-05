-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 21, 2024 at 10:20 AM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `unhs_attendance`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `emp_fk_id` bigint(20) UNSIGNED NOT NULL,
  `time_in` varchar(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lunch_start` varchar(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lunch_end` varchar(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_out` varchar(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` varchar(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `undertime` varchar(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `overtime` varchar(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `late` varchar(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `week_no` int(11) NOT NULL DEFAULT 3,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `emp_fk_id`, `time_in`, `lunch_start`, `lunch_end`, `time_out`, `status`, `duration`, `undertime`, `overtime`, `late`, `week_no`, `created_at`, `updated_at`) VALUES
(1, 1, '2024-01-04 07:15:35', '2024-01-04 12:26:13', '2024-01-04 13:05:18', '2024-01-04 17:10:23', 'Present', '9Hr 54Mins 48Secs', NULL, '10Mins 23Secs', NULL, 1, '2024-01-03 07:15:35', '2024-01-03 17:10:23'),
(2, 2, '2024-01-04 07:12:35', '2024-01-04 12:16:13', '2024-01-04 13:09:18', '2024-01-04 17:12:23', 'Present', '10Mins 48Secs', NULL, '12Mins 23Secs', NULL, 1, '2024-01-03 07:12:35', '2024-01-03 17:12:23'),
(3, 3, '2024-01-04 07:34:35', '2024-01-04 12:01:13', '2024-01-04 13:23:18', '2024-01-04 14:12:23', 'Present', '6Hr 37Mins 48Secs', '2Hr 47Mins 37Secs', NULL, '4Mins 35Secs', 1, '2024-01-03 07:34:35', '2024-01-03 14:12:23'),
(4, 4, '2024-01-04 07:19:35', '2024-01-04 12:06:13', '2024-01-04 13:02:18', '2024-01-04 17:30:23', 'Present', '110Mins 48Secs', NULL, '30Mins 23Secs', NULL, 1, '2024-01-03 07:19:35', '2024-01-03 17:30:23'),
(5, 5, '2024-01-04 07:34:35', '2024-01-04 12:01:13', '2024-01-04 13:23:18', '2024-01-04 14:12:23', 'Present', '6Hr 37Mins 48Secs', '2Hr 47Mins 37Secs', NULL, '4Mins 35Secs', 1, '2024-01-03 07:34:35', '2024-01-03 14:12:23'),
(6, 6, '2024-01-04 07:15:35', '2024-01-04 12:26:13', '2024-01-04 13:05:18', '2024-01-04 17:10:23', 'Present', '9Hr 54Mins 48Secs', NULL, '10Mins 23Secs', NULL, 1, '2024-01-03 07:15:35', '2024-01-03 17:10:23'),
(7, 7, '2024-01-04 07:34:35', '2024-01-04 12:01:13', '2024-01-04 13:23:18', '2024-01-04 14:12:23', 'Present', '6Hr 37Mins 48Secs', '2Hr 47Mins 37Secs', NULL, '4Mins 35Secs', 1, '2024-01-03 07:34:35', '2024-01-03 14:12:23'),
(8, 1, '2024-01-05 07:16:35', '2024-01-05 12:06:13', '2024-01-05 13:03:18', '2024-01-05 17:19:23', 'Present', '9Hr 54Mins 48Secs', NULL, '10Mins 23Secs', NULL, 1, '2024-01-04 07:16:35', '2024-01-04 17:19:23'),
(9, 2, '2024-01-05 07:02:35', '2024-01-05 12:02:13', '2024-01-05 13:04:18', '2024-01-05 17:22:23', 'Present', '10Mins 48Secs', NULL, '12Mins 23Secs', NULL, 1, '2024-01-04 07:02:35', '2024-01-04 17:22:23'),
(10, 3, '2024-01-05 07:14:35', '2024-01-05 12:21:13', '2024-01-05 13:23:18', '2024-01-05 15:11:23', 'Present', '6Hr 37Mins 48Secs', '2Hr 47Mins 37Secs', NULL, '4Mins 35Secs', 1, '2024-01-04 07:14:35', '2024-01-04 15:11:23'),
(11, 4, '2024-01-05 07:19:35', '2024-01-05 12:06:13', '2024-01-05 13:02:18', '2024-01-05 17:30:23', 'Present', '110Mins 48Secs', NULL, '30Mins 23Secs', NULL, 1, '2024-01-04 07:19:35', '2024-01-04 17:30:23'),
(12, 5, '2024-01-05 07:34:35', '2024-01-05 12:01:13', '2024-01-05 13:23:18', '2024-01-05 14:12:23', 'Present', '6Hr 37Mins 48Secs', '2Hr 47Mins 37Secs', NULL, '4Mins 35Secs', 1, '2024-01-04 07:34:35', '2024-01-04 14:12:23'),
(13, 6, '2024-01-05 07:15:35', '2024-01-05 12:26:13', '2024-01-05 13:05:18', '2024-01-05 17:10:23', 'Present', '9Hr 54Mins 48Secs', NULL, '10Mins 23Secs', NULL, 1, '2024-01-04 07:15:35', '2024-01-04 17:10:23'),
(14, 7, '2024-01-05 07:34:35', '2024-01-05 12:01:13', '2024-01-05 13:23:18', '2024-01-05 14:12:23', 'Present', '6Hr 37Mins 48Secs', '2Hr 47Mins 37Secs', NULL, '4Mins 35Secs', 1, '2024-01-04 07:34:35', '2024-01-04 14:12:23'),
(15, 2, '2024-01-07 07:06:35', '2024-01-07 12:06:13', '2024-01-07 13:10:18', '2024-01-07 17:35:23', 'Present', '10Mins 48Secs', NULL, '12Mins 23Secs', NULL, 1, '2024-01-06 07:06:35', '2024-01-06 17:35:23'),
(16, 3, '2024-01-07 07:34:35', '2024-01-07 12:01:13', '2024-01-07 13:23:18', '2024-01-07 14:12:23', 'Present', '6Hr 37Mins 48Secs', '2Hr 47Mins 37Secs', NULL, '4Mins 35Secs', 1, '2024-01-06 07:34:35', '2024-01-06 14:12:23'),
(17, 4, '2024-01-07 07:19:35', '2024-01-07 12:06:13', '2024-01-07 13:02:18', '2024-01-07 17:30:23', 'Present', '110Mins 48Secs', NULL, '30Mins 23Secs', NULL, 1, '2024-01-06 07:19:35', '2024-01-06 17:30:23'),
(18, 5, '2024-01-07 07:34:35', '2024-01-07 12:01:13', '2024-01-07 13:23:18', '2024-01-07 14:12:23', 'Present', '6Hr 37Mins 48Secs', '2Hr 47Mins 37Secs', NULL, '4Mins 35Secs', 1, '2024-01-06 07:34:35', '2024-01-06 14:12:23'),
(19, 6, '2024-01-07 07:15:35', '2024-01-07 12:26:13', '2024-01-07 13:05:18', '2024-01-07 17:10:23', 'Present', '9Hr 54Mins 48Secs', NULL, '10Mins 23Secs', NULL, 1, '2024-01-06 07:15:35', '2024-01-06 17:10:23'),
(20, 7, '2024-01-07 07:34:35', '2024-01-07 12:01:13', '2024-01-07 13:23:18', '2024-01-07 14:12:23', 'Present', '6Hr 37Mins 48Secs', '2Hr 47Mins 37Secs', NULL, '4Mins 35Secs', 1, '2024-01-06 07:34:35', '2024-01-06 14:12:23'),
(21, 6, '2024-01-08 07:15:35', '2024-01-08 12:26:13', '2024-01-08 13:05:18', '2024-01-08 17:10:23', 'Present', '9Hr 54Mins 48Secs', NULL, '10Mins 23Secs', NULL, 1, '2024-01-07 07:15:35', '2024-01-07 17:10:23'),
(22, 7, '2024-01-08 07:34:35', '2024-01-08 12:01:13', '2024-01-08 13:23:18', '2024-01-08 14:12:23', 'Present', '6Hr 37Mins 48Secs', '2Hr 47Mins 37Secs', NULL, '4Mins 35Secs', 1, '2024-01-07 07:34:35', '2024-01-07 14:12:23'),
(23, 3, '2024-01-08 07:12:35', '2024-01-08 12:16:13', '2024-01-08 13:09:18', '2024-01-08 17:12:23', 'Present', '10Mins 48Secs', NULL, '12Mins 23Secs', NULL, 2, '2024-01-07 07:12:35', '2024-01-07 17:12:23'),
(24, 6, '2024-01-08 07:15:35', '2024-01-08 12:26:13', '2024-01-08 13:05:18', '2024-01-08 17:10:23', 'Present', '9Hr 54Mins 48Secs', NULL, '10Mins 23Secs', NULL, 2, '2024-01-07 07:15:35', '2024-01-07 17:10:23'),
(25, 3, '2024-01-09 07:12:35', '2024-01-09 12:16:13', '2024-01-09 13:09:18', '2024-01-09 17:12:23', 'Present', '10Mins 48Secs', NULL, '12Mins 23Secs', NULL, 2, '2024-01-08 07:12:35', '2024-01-08 17:12:23'),
(26, 6, '2024-01-09 07:15:35', '2024-01-09 12:26:13', '2024-01-09 13:05:18', '2024-01-09 17:10:23', 'Present', '9Hr 54Mins 48Secs', NULL, '10Mins 23Secs', NULL, 2, '2024-01-08 07:15:35', '2024-01-08 17:10:23'),
(27, 1, '2024-01-15 18:48:05', '2024-01-15 18:48:29', '2024-01-15 18:48:34', '2024-01-15 18:48:40', 'Present', '30secs', '', '', '11Hrs 18mins 5secs', 3, '2024-01-14 18:48:05', '2024-01-14 18:48:40'),
(28, 1, NULL, NULL, NULL, NULL, 'Absent', NULL, NULL, NULL, NULL, 3, '2024-01-15 18:00:01', '2024-01-15 18:00:01'),
(29, 2, NULL, NULL, NULL, NULL, 'Absent', NULL, NULL, NULL, NULL, 3, '2024-01-15 18:00:01', '2024-01-15 18:00:01'),
(30, 3, NULL, NULL, NULL, NULL, 'Absent', NULL, NULL, NULL, NULL, 3, '2024-01-15 18:00:01', '2024-01-15 18:00:01'),
(31, 4, NULL, NULL, NULL, NULL, 'Absent', NULL, NULL, NULL, NULL, 3, '2024-01-15 18:00:01', '2024-01-15 18:00:01'),
(32, 5, NULL, NULL, NULL, NULL, 'Absent', NULL, NULL, NULL, NULL, 3, '2024-01-15 18:00:01', '2024-01-15 18:00:01'),
(33, 6, NULL, NULL, NULL, NULL, 'Absent', NULL, NULL, NULL, NULL, 3, '2024-01-15 18:00:01', '2024-01-15 18:00:01'),
(34, 7, NULL, NULL, NULL, NULL, 'Absent', NULL, NULL, NULL, NULL, 3, '2024-01-15 18:00:01', '2024-01-15 18:00:01'),
(35, 8, NULL, NULL, NULL, NULL, 'Absent', NULL, NULL, NULL, NULL, 3, '2024-01-15 18:00:01', '2024-01-15 18:00:01'),
(36, 9, NULL, NULL, NULL, NULL, 'Absent', NULL, NULL, NULL, NULL, 3, '2024-01-15 18:00:01', '2024-01-15 18:00:01'),
(37, 10, NULL, NULL, NULL, NULL, 'Absent', NULL, NULL, NULL, NULL, 3, '2024-01-15 18:00:01', '2024-01-15 18:00:01'),
(38, 1, '2024-01-17 11:28:56', '2024-01-17 11:29:06', NULL, NULL, 'Lunch', NULL, NULL, NULL, '3Hrs 58mins 55secs', 3, '2024-01-16 11:28:56', '2024-01-16 11:29:06'),
(39, 2, '2024-01-17 11:28:59', NULL, NULL, NULL, 'Present', NULL, NULL, NULL, '3Hrs 58mins 59secs', 3, '2024-01-16 11:28:59', '2024-01-16 11:28:59'),
(40, 1, '2024-01-21 17:18:16', '2024-01-21 17:19:49', NULL, NULL, 'Lunch', NULL, NULL, NULL, '9Hrs 48mins 15secs', 3, '2024-01-21 09:18:16', '2024-01-21 09:19:49');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `emp_no` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstname` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middlename` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` tinyint(4) NOT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'On Duty',
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pin_flag` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'off',
  `pin_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `emp_no`, `firstname`, `middlename`, `lastname`, `contact`, `email`, `position`, `status`, `photo`, `pin_flag`, `pin_code`, `created_at`, `updated_at`) VALUES
(1, '00001', 'Volodymyr', 'Oleksandrovych', 'Zelenskyy', '09100000001', 'zelenskyy@ukraini.ur', 1, 'On Duty', '', 'off', 'eyJpdiI6IjV4Nk1aNFIzUEsxcFFlWE43MzhTb0E9PSIsInZhbHVlIjoiZnZhSFgzRlZSczlEL1lkZ2o1aVR4Zz09IiwibWFjIjoiYzg3YTEyYmMwYjM1ZTc0NWNhMjEyZDJmNDMyN2Q2NjQ5MTc5NWY4MDdmYjI0YjgzOTlmNjFlNmVmZTg0M2YzYiIsInRhZyI6IiJ9', '2024-01-28 03:37:59', '2024-01-28 03:37:59'),
(2, '00002', 'Barrack', 'Hussein', 'Obama', '09100000002', 'obama@washington.usa', 1, 'On Duty', '', 'off', 'eyJpdiI6ImpWdnFkOUNhSG9HRlZnb3JvSkEzN2c9PSIsInZhbHVlIjoiUU5Ub2w3Z1lFQkJlYzJwTXBlUkVKUT09IiwibWFjIjoiN2EwZTg4NjgyYWZlYzFiYjJhMzU2Y2MzMWRjMjQ0NDM2YjRkZGVmZTEwMWEwY2I5YzA0MjUxNzMxNmE4ODg2ZiIsInRhZyI6IiJ9', '2024-01-28 03:37:59', '2024-01-28 03:37:59'),
(3, '00003', 'Kim', 'Jong', 'Un', '09100000003', 'nuke@facility.nk', 1, 'On Duty', '', 'off', 'eyJpdiI6ImZlNXNzY0JwU0FVWEQ4emdmNFFXMVE9PSIsInZhbHVlIjoiSFFqTWZiaWlhNnE1dW9rZmhvb2Vkdz09IiwibWFjIjoiNjRhNzQyNjQ3NDlmYzJhMDQ0ZjkyMDRhM2E2NTU1MTAwMWQ0MjZiYTJhYTBiMmQyNDk3Yjg1N2M0ZmI2ODE0YSIsInRhZyI6IiJ9', '2024-01-28 03:37:59', '2024-01-28 03:37:59'),
(4, '00004', 'Donald', 'J', 'Trump', '09100000004', 'political@snowman.usa', 1, 'On Duty', '', 'off', 'eyJpdiI6IngzVHdCWllzTEl2Q1JhZWlHaEtMQ0E9PSIsInZhbHVlIjoib3BJVlZFdEszK3Q4VlMwTS9uQ05vdz09IiwibWFjIjoiZmYxZDc4MTIwMGFmNjg4OGE1YzcyZTkwOWQ0ODA0NGFmMDE1NDY2OTFmYmIzNWE2YmEwYTBkYTBiMzVhOGFkNiIsInRhZyI6IiJ9', '2024-01-28 03:37:59', '2024-01-28 03:37:59'),
(5, '00005', 'Vladimir', 'V', 'Putin', '09100000005', 'ruskie@rusland.ru', 1, 'On Duty', '', 'off', 'eyJpdiI6Im54MGJvNFJ4ZllhVGhEVmVFcitVWVE9PSIsInZhbHVlIjoiNkpYY05IZ09WMFBjc1kyZkMvbitEUT09IiwibWFjIjoiOTQ4YTQ4NjdmMTAzYzk3ZDhhNWY0ZjA5MDhmNzYyNjQwODI0ZWFkMTQzNjRhYTg4OWZmNTU3ZjM4MzBjNTljMSIsInRhZyI6IiJ9', '2024-01-28 03:37:59', '2024-01-28 03:37:59'),
(6, '00006', 'Jeff', 'Preston', 'Bezos', '09100000006', 'jeff@amazon.com', 2, 'On Duty', '', 'off', 'eyJpdiI6Ilc1Qkh5U0M5RGlQNzRjUUg5eDlBblE9PSIsInZhbHVlIjoiUjgxUjdUamg0eDRzNHJxazFsRU5oUT09IiwibWFjIjoiNjMyOTU5YWI4YzM3NjkzYzIzZTFmNmUyMjhiZGZhODAxMGU3ODRiNGIyZDZlYzNhYTVmMmM5MzA3NzJjODc0ZCIsInRhZyI6IiJ9', '2024-01-28 03:37:59', '2024-01-28 03:37:59'),
(7, '00007', 'Elon', 'Reeves', 'Musk', '09100000007', 'elon@tesla.motors', 2, 'On Duty', '', 'off', 'eyJpdiI6IlB1YTYzb3pYWEp4eENWekZRMktXcFE9PSIsInZhbHVlIjoiZDZSYy9KTTFEWGF3a2xOclplazhkZz09IiwibWFjIjoiNjFhYjRkNmZkMjQ1MTQ2NWZjZTQzOGRhYTkzZmZkZGU3MTVhOWQ5YWFmZjkzOWZlMTcxY2NmOWRmNjU4Y2FmMiIsInRhZyI6IiJ9', '2024-01-28 03:37:59', '2024-01-28 03:37:59'),
(8, '00008', 'Steve', 'Paul', 'Jobs', '09100000008', 'apple@ios.os', 2, 'On Duty', '', 'off', 'eyJpdiI6Im9CRzZvQmVkdmVpKzREYzlhMGJyelE9PSIsInZhbHVlIjoiS2Jvb2F6bExmT3VVZXljZDdkTXRmdz09IiwibWFjIjoiZmFiOGQzOTU3NzE0ODMzNDNkNjE2YWE4ZDBkZDQ4NWI4MTg2NzMyYzU0MWYyZDQ1NmRjNDEwMzhhOWQ5N2M5ZCIsInRhZyI6IiJ9', '2024-01-28 03:37:59', '2024-01-28 03:37:59'),
(9, '00009', 'Bill', 'Henry', 'Gates', '09100000009', 'windows@microsoft.net', 2, 'On Duty', '', 'off', 'eyJpdiI6InBpZndjU2lSN0M5OUUzZHkzYmZTVFE9PSIsInZhbHVlIjoia1EyWDJqWUdxSzRpMTBFZHJIeWZNQT09IiwibWFjIjoiZDhlNzlmNDQ1YzEyM2E5ZjI3OGRhYzUzYTU1NmM4NjhjZGQ4ZmFlNzYwOTI4YzU2YjZmMTdmYTBiNGQ3OGU3ZiIsInRhZyI6IiJ9', '2024-01-28 03:37:59', '2024-01-28 03:37:59'),
(10, '00010', 'Mark', 'Elliott', 'Zuckerburg', '09100000010', 'mark@fb.com', 2, 'On Duty', '', 'off', 'eyJpdiI6IlpUaHp0N3dNTTZjaTcrcytyWGNwSHc9PSIsInZhbHVlIjoiVDdEeHMwMjI5RkxzaFEvZ1dnM2ppUT09IiwibWFjIjoiMGJmNmIxNWUzYTM1MzdhZjlmNTUzNmU4YzM4ZGM2OTRiNmQ3NTMxYzIwZTc4M2I5ZTc5YWE3OGZiY2RmOTYzOSIsInRhZyI6IiJ9', '2024-01-28 03:37:59', '2024-01-28 03:37:59');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `emp_fk_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `leave_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `leave_reason` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2014_10_12_200000_add_two_factor_columns_to_users_table', 1),
(4, '2019_08_19_000000_create_failed_jobs_table', 1),
(5, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(6, '2023_12_22_155148_create_employees_table', 1),
(7, '2023_12_22_155215_create_attendances_table', 1),
(8, '2024_01_04_200420_create_leave_requests_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `firstname` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `username`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Sudo', 'Admin', 'sudo', 'ptlangsakalam@gmail.com', NULL, '$2y$10$9.oYZefQvssceQYI3LDcC.pKVvmY.rwgxo745DVqkb2NhzLOBdZdi', NULL, NULL, NULL, NULL, NULL),
(2, 'Mark', 'Cortes', 'mark', 'laramailer.dev@gmail.com', NULL, '$2y$10$q2IMmRMIroMOHK73AgpEsemdewpEhReeKU9tYhF41//uSFYtJy0mG', NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendances_emp_fk_id_foreign` (`emp_fk_id`),
  ADD KEY `attendances_created_at_index` (`created_at`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employees_emp_no_unique` (`emp_no`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_emp_fk_id_foreign` FOREIGN KEY (`emp_fk_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
