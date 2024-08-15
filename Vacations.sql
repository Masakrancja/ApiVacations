-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 15, 2024 at 12:51 PM
-- Server version: 8.0.39-0ubuntu0.22.04.1
-- PHP Version: 8.1.2-1ubuntu2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `Vacations`
--

-- --------------------------------------------------------

--
-- Table structure for table `Events`
--

CREATE TABLE `Events` (
  `id` int NOT NULL,
  `userId` int NOT NULL,
  `groupId` int NOT NULL,
  `reasonId` int NOT NULL,
  `dateFrom` date NOT NULL,
  `dateTo` date NOT NULL,
  `days` int NOT NULL,
  `status` enum('approved','pending','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notice` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `wantCancel` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Events`
--

INSERT INTO `Events` (`id`, `userId`, `groupId`, `reasonId`, `dateFrom`, `dateTo`, `days`, `status`, `notice`, `wantCancel`, `createdAt`, `updatedAt`) VALUES
(1, 31, 11, 4, '2024-07-01', '2024-07-02', 2, 'cancelled', 'test', 'no', '2024-07-31 16:56:03', '2024-08-07 12:48:07'),
(2, 32, 11, 1, '2024-08-05', '2024-08-12', 8, 'approved', 'Coś tam', 'no', '2024-08-01 12:05:25', '2024-08-13 11:59:38'),
(7, 31, 11, 1, '2024-08-20', '2024-08-23', 4, 'cancelled', 'dfasdfas', 'no', '2024-08-06 16:05:16', '2024-08-13 16:35:55'),
(40, 31, 11, 3, '2024-08-18', '2024-08-19', 2, 'approved', '', 'no', '2024-08-13 16:53:58', '2024-08-13 16:54:28'),
(42, 31, 11, 2, '2024-08-29', '2024-08-30', 2, 'approved', 'Test notatki', 'yes', '2024-08-15 10:41:50', '2024-08-15 12:50:07');

-- --------------------------------------------------------

--
-- Table structure for table `Groups`
--

CREATE TABLE `Groups` (
  `id` int NOT NULL,
  `userId` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `postalCode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nip` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Groups`
--

INSERT INTO `Groups` (`id`, `userId`, `name`, `address`, `postalCode`, `city`, `nip`, `createdAt`, `updatedAt`) VALUES
(11, 30, 'Firma Pana Jana', 'wesoła 23', '12-200', 'Pisz', '6793334455', '2024-07-30 12:35:05', '2024-07-30 12:35:05'),
(12, 36, 'WWa Co', 'wolkdka 3', '22-333', 'willka', '6753334422', '2024-08-06 16:52:50', '2024-08-06 16:52:50'),
(13, 38, 'nowa firma', 'konopna 23', '33-333', 'trzęboszów', '2222223333', '2024-08-14 12:50:11', '2024-08-14 12:50:11');

-- --------------------------------------------------------

--
-- Table structure for table `Reasons`
--

CREATE TABLE `Reasons` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Reasons`
--

INSERT INTO `Reasons` (`id`, `name`, `createdAt`, `updatedAt`) VALUES
(1, 'Choroba', '2024-06-03 14:04:50', '2024-06-03 14:04:50'),
(2, 'Zdarzenie losowe', '2024-06-03 14:05:08', '2024-06-03 14:05:08'),
(3, 'Bez powodu', '2024-07-30 12:15:49', '2024-07-30 12:15:49'),
(4, 'Urlop wypoczynkowy', '2024-07-30 12:16:44', '2024-07-30 12:16:44');

-- --------------------------------------------------------

--
-- Table structure for table `Tokens`
--

CREATE TABLE `Tokens` (
  `id` int NOT NULL,
  `userId` int NOT NULL,
  `token` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `validAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Tokens`
--

INSERT INTO `Tokens` (`id`, `userId`, `token`, `createdAt`, `updatedAt`, `validAt`) VALUES
(107, 30, '6d9a1cd7ff3a036879cde14746daf711', '2024-08-15 11:32:23', '2024-08-15 11:32:23', '2024-08-15 12:32:23'),
(109, 31, 'cd8e08b275d4c00d6cdfb2218a938e2e', '2024-08-15 12:33:41', '2024-08-15 12:33:41', '2024-08-15 13:33:41');

-- --------------------------------------------------------

--
-- Table structure for table `UserData`
--

CREATE TABLE `UserData` (
  `id` int NOT NULL,
  `userId` int NOT NULL,
  `firstName` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastName` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `postalCode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `UserData`
--

INSERT INTO `UserData` (`id`, `userId`, `firstName`, `lastName`, `address`, `postalCode`, `city`, `phone`, `email`, `createdAt`, `updatedAt`) VALUES
(1, 30, 'Jan', 'Nowak', 'Wesoła 23', '12-200', 'Pisz', '23-334-555-777', 'jan@jan.com', '2024-07-30 12:35:05', '2024-07-30 12:35:05'),
(2, 31, 'Waldemar', 'Kowal', 'Zabowa 12', '22-200', 'Włodawa', '34-222-333-444', 'waldek@waldek.com', '2024-07-30 12:38:32', '2024-08-14 16:08:17'),
(3, 32, 'Stefan', 'Wyszkowski', 'Podlaska 2', '11-200', 'Bartoszyce', '33-123-456-3', 'jak@co.tam', '2024-07-30 14:00:23', '2024-07-30 14:00:23'),
(4, 33, 'Jan', 'Kowalski', 'Wesoła 4', '11-222', 'Bartoszyce', '33-44-55-44-55', 'aa@aa.com', '2024-08-05 15:49:25', '2024-08-05 15:49:25'),
(7, 36, 'Eugen', 'Paliwo', 'ffkaa 3', '00234', 'Wwa', '99839948', 'ww@ww.com', '2024-08-06 16:52:50', '2024-08-06 16:52:50'),
(8, 37, 'Zenoncd', 'Miodek', 'Spokojna 12', '22-333', 'Leopolsow', '12344566', 'sdfasd@fasdfasd.sss', '2024-08-14 12:27:44', '2024-08-14 12:27:44'),
(9, 38, 'Zenoncd', 'Miodek', 'Spokojna 12', '22-333', 'Leopolsow', '12344566', 'sdfasd@fasdfasd.sss', '2024-08-14 12:50:11', '2024-08-14 12:50:11');

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `id` int NOT NULL,
  `groupId` int DEFAULT NULL,
  `login` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pass` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isActive` tinyint(1) NOT NULL DEFAULT '0',
  `isAdmin` tinyint(1) NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`id`, `groupId`, `login`, `pass`, `isActive`, `isAdmin`, `createdAt`, `updatedAt`) VALUES
(30, 11, 'admin1', 'e00cf25ad42683b3df678c61f42c6bda', 1, 1, '2024-07-30 12:35:05', '2024-07-30 12:35:05'),
(31, 11, 'worker1a', '9de1e89ad049b171d022e94417e78036', 1, 0, '2024-07-30 12:38:32', '2024-08-14 13:58:17'),
(32, 11, 'user1b', '3e40a5353fd0da540c9aed30ba36f9b4', 1, 0, '2024-07-30 14:00:23', '2024-08-13 12:08:50'),
(33, 11, 'user2', '7e58d63b60197ceb55a1c487989a3720', 1, 0, '2024-08-05 15:49:25', '2024-08-13 09:13:53'),
(36, 12, 'admin2', 'c84258e9c39059a89ab77d846ddab909', 1, 1, '2024-08-06 16:52:50', '2024-08-06 16:52:50'),
(37, 12, 'zenek', '40fb7e6030851ad33b9c770f8fe3b14d', 0, 0, '2024-08-14 12:27:44', '2024-08-14 12:27:44'),
(38, 13, 'admin3', '32cacb2f994f6b42183a1300d9a3e8d6', 1, 1, '2024-08-14 12:50:11', '2024-08-14 12:50:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Events`
--
ALTER TABLE `Events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`),
  ADD KEY `groupId` (`groupId`),
  ADD KEY `reasonId` (`reasonId`);

--
-- Indexes for table `Groups`
--
ALTER TABLE `Groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`userId`);

--
-- Indexes for table `Reasons`
--
ALTER TABLE `Reasons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Tokens`
--
ALTER TABLE `Tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_tokens` (`userId`);

--
-- Indexes for table `UserData`
--
ALTER TABLE `UserData`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`userId`) USING BTREE;

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`groupId`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Events`
--
ALTER TABLE `Events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `Groups`
--
ALTER TABLE `Groups`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `Reasons`
--
ALTER TABLE `Reasons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Tokens`
--
ALTER TABLE `Tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `UserData`
--
ALTER TABLE `UserData`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Events`
--
ALTER TABLE `Events`
  ADD CONSTRAINT `Events_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `Users` (`id`),
  ADD CONSTRAINT `Events_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `Groups` (`id`),
  ADD CONSTRAINT `Events_ibfk_3` FOREIGN KEY (`reasonId`) REFERENCES `Reasons` (`id`);

--
-- Constraints for table `Groups`
--
ALTER TABLE `Groups`
  ADD CONSTRAINT `Groups_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `Users` (`id`);

--
-- Constraints for table `Tokens`
--
ALTER TABLE `Tokens`
  ADD CONSTRAINT `users_tokens` FOREIGN KEY (`userId`) REFERENCES `Users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `UserData`
--
ALTER TABLE `UserData`
  ADD CONSTRAINT `UserData_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `Users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
