-- phpMyAdmin SQL Dump
-- version 5.2.3deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 28, 2026 at 07:58 AM
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
(3, 2, 1, 'when can you finish', '2026-03-09 13:47:34');

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
(1, 'Bug Tracking System', 'To track bugs for projects', 'active', 1, '2026-03-09 13:05:21');

-- --------------------------------------------------------

--
-- Table structure for table `Tickets`
--

CREATE TABLE `Tickets` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('open','in-progress','resolved','closed','on-hold') DEFAULT 'open',
  `project_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `Tickets`
--

INSERT INTO `Tickets` (`id`, `title`, `description`, `status`, `project_id`, `created_by`, `created_at`, `assigned_by`, `assigned_to`) VALUES
(1, 'Login Not Working', 'API Working', 'resolved', 1, 3, '2026-03-09 13:10:13', 2, 2),
(2, 'Register not responding', 'UI not responding', 'open', 1, 3, '2026-03-09 13:18:40', NULL, 5);

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
(5, 'Lee DEV', 'lee@gmail.com', '$2y$12$lm4bGl25R13bWUAqC3pcLezXBW8Ni0Y3QBXiuxnnc8WqaAh7seAkO', 'developer', '2026-03-09 13:43:16');

--
-- Indexes for dumped tables
--

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
  ADD KEY `fk_assigned_to` (`assigned_to`);

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
-- AUTO_INCREMENT for table `Comments`
--
ALTER TABLE `Comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Projects`
--
ALTER TABLE `Projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Tickets`
--
ALTER TABLE `Tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

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
  ADD CONSTRAINT `fk_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `Users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `Users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
