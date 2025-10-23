-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 12, 2025 at 07:44 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `students_data`
--

-- --------------------------------------------------------

--
-- Table structure for table `academicyear`
--

DROP TABLE IF EXISTS `academicyear`;
CREATE TABLE IF NOT EXISTS `academicyear` (
  `id` int NOT NULL AUTO_INCREMENT,
  `academicyear` varchar(25) NOT NULL,
  `isActive` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `coursecode` varchar(10) NOT NULL,
  `coursename` varchar(255) NOT NULL,
  `details` text,
  `isActive` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`coursecode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coursesyllabus`
--

DROP TABLE IF EXISTS `coursesyllabus`;
CREATE TABLE IF NOT EXISTS `coursesyllabus` (
  `course` int NOT NULL,
  `courseunit` int NOT NULL,
  `year` int NOT NULL,
  `semester` int NOT NULL,
  `credits` int NOT NULL,
  `isElective` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`course`,`courseunit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courseunits`
--

DROP TABLE IF EXISTS `courseunits`;
CREATE TABLE IF NOT EXISTS `courseunits` (
  `courseunitcode` varchar(11) NOT NULL,
  `courseunitname` varchar(255) NOT NULL,
  `isActive` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`courseunitcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rolename` varchar(255) NOT NULL,
  `permissions` text NOT NULL,
  `isActive` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scores`
--

DROP TABLE IF EXISTS `scores`;
CREATE TABLE IF NOT EXISTS `scores` (
  `studentid` int NOT NULL,
  `courseunitid` int NOT NULL,
  `cwmarks` int NOT NULL,
  `finalexam` int NOT NULL,
  `academicyear` int NOT NULL,
  `createdon` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedon` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `academicyear` (`academicyear`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `studentcourse`
--

DROP TABLE IF EXISTS `studentcourse`;
CREATE TABLE IF NOT EXISTS `studentcourse` (
  `studentid` int NOT NULL,
  `courseid` varchar(10) NOT NULL,
  `enrolyear` int NOT NULL,
  `academicyear` int NOT NULL,
  `isCurrentEnrolment` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`studentid`,`courseid`,`enrolyear`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `userdetails`
--

DROP TABLE IF EXISTS `userdetails`;
CREATE TABLE IF NOT EXISTS `userdetails` (
  `id` int NOT NULL,
  `names` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `gender` varchar(10) NOT NULL,
  `address` text,
  `roleid` int NOT NULL,
  `isStudent` int NOT NULL DEFAULT '1',
  `isActive` int NOT NULL DEFAULT '1',
  `regno` int DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `createdon` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedon` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `roleid` (`roleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
