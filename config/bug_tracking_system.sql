-- phpMyAdmin SQL Dump
-- version 5.2.3deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 02, 2026 at 09:07 PM
-- Server version: 11.8.6-MariaDB-2 from Debian
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bug_tracking_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `ticket_id` int(11) DEFAULT NULL,
  `action_details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action_type`, `description`, `created_at`, `ticket_id`, `action_details`) VALUES
(1, 10, 'TICKET_ASSIGNED', 'Ticket #3 deployed to developer Limo by Admin (ID: 10)', '2026-04-02 16:40:21', NULL, NULL),
(2, 10, 'TICKET_OVERRIDE', 'Admin #10 updated Ticket #4 to closed (high)', '2026-04-02 16:55:02', 4, NULL),
(3, 10, 'TICKET_OVERRIDE', 'Admin #10 updated Ticket #4 to on-hold (high)', '2026-04-02 17:02:17', 4, NULL),
(4, 10, 'TICKET_OVERRIDE', 'Admin #10 updated Ticket #4 to in-progress (high)', '2026-04-02 17:02:31', 4, NULL),
(5, 10, 'TICKET_OVERRIDE', 'Admin #10 updated Ticket #4 to resolved (high)', '2026-04-02 17:02:41', 4, NULL),
(6, 10, 'TICKET_OVERRIDE', 'Admin #10 updated Ticket #4 to closed (high)', '2026-04-02 17:02:51', 4, NULL),
(7, 10, 'TICKET_OVERRIDE', 'Admin #10 updated Ticket #4 to on-hold (high)', '2026-04-02 17:02:59', 4, NULL),
(8, 10, 'PROJECT_CREATE', 'Admin #10 initialized new Project Node #4 (Cafeteria Management System).', '2026-04-02 18:18:32', NULL, NULL),
(9, 9, 'TICKET_STATUS_UPDATE', 'Developer developer Limo (ID: 9) updated Ticket #4 to open.', '2026-04-02 19:34:41', 4, NULL),
(10, 9, 'TICKET_DEPLOY', 'Dev developer Limo deployed Ticket #5 (Unassigned)', '2026-04-02 19:51:25', 5, NULL),
(11, 9, 'TICKET_CLAIM', 'Developer #9 claimed ownership of Ticket #5', '2026-04-02 19:55:56', 5, NULL),
(12, 9, 'TICKET_STATUS_UPDATE', 'Developer developer Limo (ID: 9) updated Ticket #5 to resolved.', '2026-04-02 19:56:10', 5, NULL),
(13, 9, 'COMMENT_PUSH', '[developer Limo] logged a technical update on Ticket #5', '2026-04-02 19:56:29', 5, NULL),
(14, 10, 'TICKET_OVERRIDE', 'Admin #10 updated Ticket #6 to on-hold (medium)', '2026-04-02 20:40:19', 6, NULL),
(15, 10, 'TICKET_UPDATE', 'Admin #10 synced Ticket #6: [on-hold | medium] Assigned to: Dev #9', '2026-04-02 20:48:19', 6, NULL),
(16, 10, 'TICKET_UPDATE', 'Admin #10 synced Ticket #6: [open | medium] Assigned to: Dev #9, Class: Category #2', '2026-04-02 20:51:57', 6, NULL),
(17, 10, 'TICKET_UPDATE', 'Admin #10 synced Ticket #3: [in-progress | medium] Assigned to: Dev #9, Class: Category #1', '2026-04-02 20:52:11', 3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Categories`
--

CREATE TABLE `Categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `Categories`
--

INSERT INTO `Categories` (`id`, `name`) VALUES
(1, 'Bug'),
(2, 'Feature Request'),
(3, 'UI/UX Improvement'),
(4, 'Security'),
(5, 'Documentation');

-- --------------------------------------------------------

--
-- Table structure for table `Comments`
--

CREATE TABLE `Comments` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `Comments`
--

INSERT INTO `Comments` (`id`, `ticket_id`, `user_id`, `comment`, `created_at`) VALUES
(1, 1, 2, 'i have resolved the issue, its working now', '2026-03-09 13:16:49'),
(2, 1, 1, 'thanks', '2026-03-09 13:20:33'),
(3, 2, 1, 'when can you finish', '2026-03-09 13:47:34'),
(4, 3, 8, 'i made it', '2026-03-29 10:26:55'),
(5, 4, 9, 'notes update', '2026-03-29 11:01:03'),
(6, 5, 9, 'i have finished the login problem', '2026-04-02 19:56:29'),
(7, 3, 8, 'nice job', '2026-04-02 20:16:48');

-- --------------------------------------------------------

--
-- Table structure for table `Projects`
--

CREATE TABLE `Projects` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','active','completed') DEFAULT 'pending',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `Projects`
--

INSERT INTO `Projects` (`id`, `name`, `description`, `status`, `created_by`, `created_at`) VALUES
(1, 'Bug Tracking System', 'To track bugs for projects', 'active', 1, '2026-03-09 13:05:21'),
(2, 'computer Programming', 'create a program', 'active', 10, '2026-03-28 11:53:21'),
(3, 'Hotel Management system', 'Ui', 'active', 10, '2026-03-29 10:22:03'),
(4, 'Cafeteria Management System', 'to create cafeteria management system for Laikipia university', 'active', 10, '2026-04-02 18:18:32');

-- --------------------------------------------------------

--
-- Table structure for table `Tickets`
--

CREATE TABLE `Tickets` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('open','in-progress','resolved','closed','on-hold') DEFAULT 'open',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `project_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `Tickets`
--

INSERT INTO `Tickets` (`id`, `title`, `description`, `status`, `priority`, `project_id`, `created_by`, `created_at`, `assigned_by`, `assigned_to`, `category_id`, `deleted_at`) VALUES
(1, 'Login Not Working', 'API Working', 'resolved', 'medium', 1, 3, '2026-03-09 13:10:13', 2, 2, NULL, NULL),
(2, 'Register not responding', 'UI not responding', 'open', 'medium', 1, 3, '2026-03-09 13:18:40', NULL, 5, NULL, NULL),
(3, 'Web design', 'UI Behavior', 'in-progress', 'medium', 1, 8, '2026-03-28 10:43:38', 10, 9, 1, NULL),
(4, 'backend api', 'hhhh', 'open', 'high', 2, 10, '2026-03-29 09:53:25', 10, 9, NULL, NULL),
(5, 'Login', 'Ui not working', 'resolved', 'high', 4, 9, '2026-04-02 19:51:25', NULL, 9, 1, NULL),
(6, 'Payments not working', 'when i pay i dont get a response', 'open', 'medium', 4, 8, '2026-04-02 20:22:09', 10, 9, 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','developer','admin') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Eliud Kiprotich', 'ronoeliud@gmail.com', '$2y$12$ut/N6KRzZZtUMyG30G8UhO75t61b51gxsmuIRCwWpdoJoXVoWQXDu', 'admin', '2026-03-09 12:59:28'),
(2, 'Elkana BT', 'elkana@gmail.com', '$2y$12$0rAC6JWZnF6voaIQwHpDFuTagGKfL6tS80VeukczElDW5JzMjDFCq', 'developer', '2026-03-09 13:01:59'),
(3, 'yvonne US', 'yvonne@gmail.com', '$2y$12$RDuF9J3wFoK.q6V7YuixvuA9xlqQ4Molr2HpBZTgiDaQbuLoXOeZy', 'user', '2026-03-09 13:03:38'),
(5, 'Lee DEV', 'lee@gmail.com', '$2y$12$lm4bGl25R13bWUAqC3pcLezXBW8Ni0Y3QBXiuxnnc8WqaAh7seAkO', 'developer', '2026-03-09 13:43:16'),
(7, 'Reporter', 'reporter@gmail.com', '$2y$12$deyUygGfT6B.iDRZLMyPzeMTykMKyj0OoYxhqRCpkhC05YXB7pnCO', 'user', '2026-03-28 10:24:33'),
(8, 'Boaz Kipchirchir', 'boazlimo07@gmail.com', '$2y$12$yQqgA0.KfJcWWDltWu1kGexy5k.G3TwpBz//rtXE/bJA1su4dildG', 'user', '2026-03-28 10:26:19'),
(9, 'developer Limo', 'developer@gmail.com', '$2y$12$pQiQjeISB277mTr/4DTd5.Pduz5NuAfMh0cLXztQ2NeQbVuvCGeti', 'developer', '2026-03-28 10:55:33'),
(10, 'admin lim', 'adminlim@gmail.com', '$2y$12$BjmcvFEcvfdBGOo/WFcJUettR9JS0uPTLszCXHgKdg0hGvvsxSGMW', 'admin', '2026-03-28 11:31:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Categories`
--
ALTER TABLE `Categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Comments`
--
ALTER TABLE `Comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Projects`
--
ALTER TABLE `Projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `Tickets`
--
ALTER TABLE `Tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `fk_assigned_by` (`assigned_by`),
  ADD KEY `fk_assigned_to` (`assigned_to`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `Categories`
--
ALTER TABLE `Categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Comments`
--
ALTER TABLE `Comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `Projects`
--
ALTER TABLE `Projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Tickets`
--
ALTER TABLE `Tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`),
  ADD CONSTRAINT `activity_log_ibfk_2` FOREIGN KEY (`ticket_id`) REFERENCES `Tickets` (`id`),
  ADD CONSTRAINT `activity_log_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

--
-- Constraints for table `Comments`
--
ALTER TABLE `Comments`
  ADD CONSTRAINT `Comments_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `Tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `Projects`
--
ALTER TABLE `Projects`
  ADD CONSTRAINT `Projects_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `Users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `Tickets`
--
ALTER TABLE `Tickets`
  ADD CONSTRAINT `Tickets_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `Projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Tickets_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `Users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Tickets_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `Categories` (`id`),
  ADD CONSTRAINT `fk_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `Users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `Users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
