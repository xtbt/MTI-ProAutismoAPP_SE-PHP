-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 08-11-2022 a las 13:54:01
-- Versión del servidor: 10.4.20-MariaDB
-- Versión de PHP: 8.0.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ProAutismoDB`
--
DROP DATABASE IF EXISTS `ProAutismoDB`;
CREATE DATABASE IF NOT EXISTS `ProAutismoDB` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ProAutismoDB`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblActivities`
--

DROP TABLE IF EXISTS `tblActivities`;
CREATE TABLE `tblActivities` (
  `ActivityId` mediumint(7) UNSIGNED NOT NULL,
  `UserProfileId` smallint(4) UNSIGNED NOT NULL,
  `TaskId` smallint(4) UNSIGNED NOT NULL,
  `ActivityDateTime` datetime NOT NULL,
  `ActivityStart` datetime NOT NULL,
  `ActivityEnd` datetime NOT NULL,
  `ActivityResults` text NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ActivityStatus` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='General Activities Table';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblTasks`
--

DROP TABLE IF EXISTS `tblTasks`;
CREATE TABLE `tblTasks` (
  `TaskId` smallint(4) UNSIGNED NOT NULL,
  `TaskType` tinyint(1) UNSIGNED NOT NULL,
  `TaskTitle` varchar(40) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `TaskStatus` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='General Tasks Table';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblTasksNodes`
--

DROP TABLE IF EXISTS `tblTasksNodes`;
CREATE TABLE `tblTasksNodes` (
  `TaskNodeId` smallint(4) UNSIGNED NOT NULL,
  `TaskId` smallint(4) UNSIGNED NOT NULL,
  `TaskNodeName` varchar(10) NOT NULL,
  `TaskNodeFatherId` smallint(4) UNSIGNED DEFAULT NULL,
  `TaskNodeDescription` varchar(50) NOT NULL,
  `TaskNodeStatus` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tasks Nodes Table';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblUsers`
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblUsersProfiles`
--

DROP TABLE IF EXISTS `tblUsersProfiles`;
CREATE TABLE `tblUsersProfiles` (
  `UserProfileId` smallint(4) UNSIGNED NOT NULL,
  `UserId` smallint(4) UNSIGNED NOT NULL,
  `FirstName` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `LastName` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `Token` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TokenExpiryDateTime` datetime DEFAULT NULL,
  `UserStatus` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Users table';

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tblActivities`
--
ALTER TABLE `tblActivities`
  ADD PRIMARY KEY (`ActivityId`),
  ADD KEY `INDEX_TASKID` (`TaskId`),
  ADD KEY `INDEX_USERID` (`UserProfileId`);

--
-- Indices de la tabla `tblTasks`
--
ALTER TABLE `tblTasks`
  ADD PRIMARY KEY (`TaskId`);

--
-- Indices de la tabla `tblTasksNodes`
--
ALTER TABLE `tblTasksNodes`
  ADD PRIMARY KEY (`TaskNodeId`),
  ADD KEY `INDEX_TASKID` (`TaskId`),
  ADD KEY `INDEX_TASKNODEFATHERID` (`TaskNodeFatherId`);

--
-- Indices de la tabla `tblUsers`
--
ALTER TABLE `tblUsers`
  ADD PRIMARY KEY (`UserId`),
  ADD UNIQUE KEY `UNIQUE_USER_NAME` (`Username`),
  ADD UNIQUE KEY `UNIQUE_USER_EMAIL` (`Email`);

--
-- Indices de la tabla `tblUsersProfiles`
--
ALTER TABLE `tblUsersProfiles`
  ADD PRIMARY KEY (`UserProfileId`),
  ADD KEY `INDEX_USERID` (`UserId`) USING BTREE;

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tblActivities`
--
ALTER TABLE `tblActivities`
  MODIFY `ActivityId` mediumint(7) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tblTasks`
--
ALTER TABLE `tblTasks`
  MODIFY `TaskId` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tblTasksNodes`
--
ALTER TABLE `tblTasksNodes`
  MODIFY `TaskNodeId` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tblUsers`
--
ALTER TABLE `tblUsers`
  MODIFY `UserId` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tblUsersProfiles`
--
ALTER TABLE `tblUsersProfiles`
  MODIFY `UserProfileId` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `tblActivities`
--
ALTER TABLE `tblActivities`
  ADD CONSTRAINT `FK_Activities__TaskId` FOREIGN KEY (`TaskId`) REFERENCES `tblTasks` (`TaskId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_Activities__UserProfileId` FOREIGN KEY (`UserProfileId`) REFERENCES `tblUsersProfiles` (`UserProfileId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tblTasksNodes`
--
ALTER TABLE `tblTasksNodes`
  ADD CONSTRAINT `FK_TasksNodes__TaskId` FOREIGN KEY (`TaskId`) REFERENCES `tblTasks` (`TaskId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tblUsersProfiles`
--
ALTER TABLE `tblUsersProfiles`
  ADD CONSTRAINT `FK_UsersProfiles__UserId` FOREIGN KEY (`UserId`) REFERENCES `tblUsers` (`UserId`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
