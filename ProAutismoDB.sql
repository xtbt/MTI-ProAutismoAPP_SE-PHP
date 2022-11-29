-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Nov 29, 2022 at 05:21 PM
-- Server version: 5.7.32
-- PHP Version: 7.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ProAutismoDB`
--
DROP DATABASE IF EXISTS `ProAutismoDB`;
CREATE DATABASE IF NOT EXISTS `ProAutismoDB` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ProAutismoDB`;

-- --------------------------------------------------------

--
-- Table structure for table `tblActivities`
--

DROP TABLE IF EXISTS `tblActivities`;
CREATE TABLE `tblActivities` (
  `ActivityId` mediumint(7) UNSIGNED NOT NULL,
  `UserProfileId` smallint(4) UNSIGNED NOT NULL,
  `TaskId` smallint(4) UNSIGNED NOT NULL,
  `ActivityDateTime` datetime NOT NULL,
  `ActivityStart` datetime DEFAULT NULL,
  `ActivityEnd` datetime DEFAULT NULL,
  `ActivityResults` text,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ActivityStatus` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='General Activities Table';

--
-- Dumping data for table `tblActivities`
--

INSERT INTO `tblActivities` (`ActivityId`, `UserProfileId`, `TaskId`, `ActivityDateTime`, `ActivityStart`, `ActivityEnd`, `ActivityResults`, `CreatedAt`, `ActivityStatus`) VALUES
(1, 1, 1, '2022-11-23 08:00:00', NULL, NULL, NULL, '2022-11-23 18:05:07', 1),
(2, 1, 2, '2022-11-23 08:15:00', NULL, NULL, NULL, '2022-11-23 04:26:03', 1),
(3, 1, 3, '2022-11-29 09:30:00', NULL, NULL, NULL, '2022-11-29 17:12:50', 1),
(4, 2, 1, '2022-11-23 08:00:00', NULL, NULL, NULL, '2022-11-23 04:26:58', 1),
(5, 2, 2, '2022-11-23 08:15:00', NULL, NULL, NULL, '2022-11-23 04:27:13', 1),
(6, 2, 3, '2022-11-23 09:30:00', NULL, NULL, NULL, '2022-11-23 04:27:31', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tblTasks`
--

DROP TABLE IF EXISTS `tblTasks`;
CREATE TABLE `tblTasks` (
  `TaskId` smallint(4) UNSIGNED NOT NULL,
  `TaskType` tinyint(1) UNSIGNED NOT NULL,
  `TaskTitle` varchar(40) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `TaskStatus` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='General Tasks Table';

--
-- Dumping data for table `tblTasks`
--

INSERT INTO `tblTasks` (`TaskId`, `TaskType`, `TaskTitle`, `CreatedAt`, `TaskStatus`) VALUES
(1, 2, 'Tender La Cama', '2022-11-21 23:34:56', 1),
(2, 1, 'Vestirse', '2022-11-21 23:03:25', 1),
(3, 1, 'Lavarse Los Dientes', '2022-11-21 23:12:22', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tblTasksNodes`
--

DROP TABLE IF EXISTS `tblTasksNodes`;
CREATE TABLE `tblTasksNodes` (
  `TaskNodeId` smallint(4) UNSIGNED NOT NULL,
  `TaskId` smallint(4) UNSIGNED NOT NULL,
  `TaskNodeName` varchar(10) NOT NULL,
  `TaskNodeFatherId` smallint(4) UNSIGNED DEFAULT NULL,
  `TaskNodeOption` varchar(50) DEFAULT NULL,
  `TaskNodeDescription` varchar(50) NOT NULL,
  `TaskNodeStatus` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tasks Nodes Table';

--
-- Dumping data for table `tblTasksNodes`
--

INSERT INTO `tblTasksNodes` (`TaskNodeId`, `TaskId`, `TaskNodeName`, `TaskNodeFatherId`, `TaskNodeOption`, `TaskNodeDescription`, `TaskNodeStatus`) VALUES
(1, 1, '1', NULL, NULL, 'Pongamos la sábana', 1),
(2, 1, '2A', 1, 'Pone la sábana', 'Pongamos la cobija', 1),
(3, 1, '2B', 1, 'Pone la cobija', 'Pongamos la colcha', 1),
(4, 1, '2C', 1, 'Juega con la pelota', '[FINAL MALO]', 1),
(5, 1, '3AA', 2, 'Pone la cobija', 'Pongamos la colcha', 1),
(6, 1, '3AB', 2, 'Pone la almohada', '[FINAL REGULAR]', 1),
(7, 1, '3AC', 2, 'Juega con el osito de peluche', '[FINAL MALO]', 1),
(8, 1, '4AAA', 5, 'Se pone la colcha', 'Pongamos la almohada', 1),
(9, 1, '4AAB', 5, 'Se pone la almohada', '[FINAL REGULAR]', 1),
(10, 1, '4AAC', 5, 'Juega con el robot de juguete', '[FINAL MALO]', 1),
(11, 1, '5AAAA', 8, 'Pone la almohada', '[FINAL BUENO]', 1),
(12, 1, '5AAAB', 8, 'Juega con la pelota', '[FINAL MALO]', 1),
(13, 1, '5AAAC', 8, 'Juega con el osito de peluche', '[FINAL MALO]', 1),
(14, 1, '3BA', 3, 'Pone la colcha', 'Pongamos la almohada', 1),
(15, 1, '3BB', 3, 'Pone la almohada', '[FINAL REGULAR]', 1),
(16, 1, '3BC', 3, 'Juega con el robot de juguete', '[FINAL MALO]', 1),
(17, 1, '4BAA', 14, 'Pone la almohada', '[FINAL REGULAR]', 1),
(18, 1, '4BAB', 14, 'Juega con la pelota', '[FINAL MALO]', 1),
(19, 1, '4BAC', 14, 'Juega con el dinosaurio de juguete', '[FINAL MALO]', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tblUsers`
--

DROP TABLE IF EXISTS `tblUsers`;
CREATE TABLE `tblUsers` (
  `UserId` smallint(4) UNSIGNED NOT NULL,
  `Username` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `Password` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `UserType` tinyint(1) UNSIGNED NOT NULL,
  `Email` varchar(48) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PhoneNumber` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FirstName` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `LastName` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `Token` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TokenExpiryDateTime` datetime DEFAULT NULL,
  `UserStatus` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Users table';

--
-- Dumping data for table `tblUsers`
--

INSERT INTO `tblUsers` (`UserId`, `Username`, `Password`, `UserType`, `Email`, `PhoneNumber`, `FirstName`, `LastName`, `Token`, `TokenExpiryDateTime`, `UserStatus`) VALUES
(1, 'demo', '$2y$10$Day6sq3q9GSul/dKbGrP2upWOGNMgXSmapNzF0ucMAHJih8w.hENm', 1, 'demo@proautismoapp.com', '6641234567', 'Usuario', 'Demostración', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJNVEktMjAyMi0yIiwiZXhwIjoiMjAyMjExMTgxNjUyMDUiLCJqdGkiOiIxIn0.kNRp27GfUcPQJbu5wQHQXzgcOGgZlZwehDXQyrsG7q0', '2022-11-18 16:52:05', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tblUsersProfiles`
--

DROP TABLE IF EXISTS `tblUsersProfiles`;
CREATE TABLE `tblUsersProfiles` (
  `UserProfileId` smallint(4) UNSIGNED NOT NULL,
  `UserId` smallint(4) UNSIGNED NOT NULL,
  `FirstName` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `LastName` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `Token` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TokenExpiryDateTime` datetime DEFAULT NULL,
  `UserProfileStatus` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Users table';

--
-- Dumping data for table `tblUsersProfiles`
--

INSERT INTO `tblUsersProfiles` (`UserProfileId`, `UserId`, `FirstName`, `LastName`, `Token`, `TokenExpiryDateTime`, `UserProfileStatus`) VALUES
(1, 1, 'Juan', 'Pérez', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJNVEktMjAyMi0yIiwiZXhwIjoiMjAyMjExMTgxOTA0NTUiLCJqdGkiOiIxIn0.chbZ6O019_WKr4n6NXPMVORQ0iTPgeNfC9FK6XJIoWw', '2022-11-18 19:04:55', 1),
(2, 1, 'María', 'López', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJNVEktMjAyMi0yIiwiZXhwIjoiMjAyMjExMTgxOTA1MDgiLCJqdGkiOiIyIn0.yQNZYoU8jiKjFtJfj9Fc-7c5NZYsVysnSAl2AJGIM2o', '2022-11-18 19:05:08', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblActivities`
--
ALTER TABLE `tblActivities`
  ADD PRIMARY KEY (`ActivityId`),
  ADD KEY `INDEX_TASKID` (`TaskId`),
  ADD KEY `INDEX_USERID` (`UserProfileId`);

--
-- Indexes for table `tblTasks`
--
ALTER TABLE `tblTasks`
  ADD PRIMARY KEY (`TaskId`);

--
-- Indexes for table `tblTasksNodes`
--
ALTER TABLE `tblTasksNodes`
  ADD PRIMARY KEY (`TaskNodeId`),
  ADD KEY `INDEX_TASKID` (`TaskId`),
  ADD KEY `INDEX_TASKNODEFATHERID` (`TaskNodeFatherId`);

--
-- Indexes for table `tblUsers`
--
ALTER TABLE `tblUsers`
  ADD PRIMARY KEY (`UserId`),
  ADD UNIQUE KEY `UNIQUE_USER_NAME` (`Username`),
  ADD UNIQUE KEY `UNIQUE_USER_EMAIL` (`Email`);

--
-- Indexes for table `tblUsersProfiles`
--
ALTER TABLE `tblUsersProfiles`
  ADD PRIMARY KEY (`UserProfileId`),
  ADD KEY `INDEX_USERID` (`UserId`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblActivities`
--
ALTER TABLE `tblActivities`
  MODIFY `ActivityId` mediumint(7) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tblTasks`
--
ALTER TABLE `tblTasks`
  MODIFY `TaskId` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tblTasksNodes`
--
ALTER TABLE `tblTasksNodes`
  MODIFY `TaskNodeId` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tblUsers`
--
ALTER TABLE `tblUsers`
  MODIFY `UserId` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblUsersProfiles`
--
ALTER TABLE `tblUsersProfiles`
  MODIFY `UserProfileId` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblActivities`
--
ALTER TABLE `tblActivities`
  ADD CONSTRAINT `FK_Activities__TaskId` FOREIGN KEY (`TaskId`) REFERENCES `tblTasks` (`TaskId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_Activities__UserProfileId` FOREIGN KEY (`UserProfileId`) REFERENCES `tblUsersProfiles` (`UserProfileId`) ON UPDATE CASCADE;

--
-- Constraints for table `tblTasksNodes`
--
ALTER TABLE `tblTasksNodes`
  ADD CONSTRAINT `FK_TasksNodes__TaskId` FOREIGN KEY (`TaskId`) REFERENCES `tblTasks` (`TaskId`) ON UPDATE CASCADE;

--
-- Constraints for table `tblUsersProfiles`
--
ALTER TABLE `tblUsersProfiles`
  ADD CONSTRAINT `FK_UsersProfiles__UserId` FOREIGN KEY (`UserId`) REFERENCES `tblUsers` (`UserId`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
