-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 23, 2025 at 05:31 AM
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
-- Database: `student_data`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_code` varchar(20) NOT NULL,
  `name` varchar(200) NOT NULL,
  `credit_hours` int NOT NULL,
  `department_id` int DEFAULT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_code` (`course_code`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_code`, `name`, `credit_hours`, `department_id`, `description`, `created_at`, `updated_at`) VALUES
(1, 'CS201', 'Data Structures and Algorithms', 4, 1, 'data structures and algorithms', '2025-09-20 18:24:09', '2025-09-22 15:41:33'),
(2, 'CS202', 'Database Systems', 4, 1, 'Fundamentals of database design and implementation', '2025-09-20 18:24:09', '2025-09-20 18:24:09'),
(3, 'IT201', 'Web Technologies', 3, 2, 'Introduction to web development technologies', '2025-09-20 18:24:09', '2025-09-20 18:24:09'),
(4, 'IT202', 'Network Fundamentals', 3, 2, 'Introduction to computer networks', '2025-09-20 18:24:09', '2025-09-20 18:24:09'),
(5, 'IT203', 'Software Engineering Principles', 3, 1, 'Fundamentals of software engineering', '2025-09-20 18:24:09', '2025-09-22 14:26:39'),
(6, 'CS203', 'Object-Oriented Programming', 4, 1, 'Advanced object-oriented programming concepts', '2025-09-20 18:24:09', '2025-09-22 14:26:15'),
(7, 'CSIT201', 'Computer Organization', 3, 4, 'Computer organization and architecture', '2025-09-20 18:24:09', '2025-09-20 18:24:09'),
(8, 'CSIT202', 'Operating Systems', 4, 4, 'Principles of operating systems', '2025-09-20 18:24:09', '2025-09-20 18:24:09');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `code`, `description`, `created_at`) VALUES
(1, 'Computer Science', 'CS', 'Department of Computer Science', '2025-09-20 18:24:09'),
(2, 'Information Technology', 'IT', 'Department of Information Technology', '2025-09-20 18:24:09'),
(3, 'Software Engineering', 'SE', 'Department of Software Engineering', '2025-09-20 18:24:09'),
(4, 'Computer Science and IT', 'CSIT', 'Department of Computer Science and Information Technology', '2025-09-20 18:24:09');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
CREATE TABLE IF NOT EXISTS `staff` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` varchar(20) NOT NULL,
  `user_id` int DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `department_id` int DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `qualification` text,
  `date_joined` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_id` (`staff_id`),
  UNIQUE KEY `email` (`email`),
  KEY `user_id` (`user_id`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `staff_id`, `user_id`, `first_name`, `last_name`, `gender`, `date_of_birth`, `email`, `phone`, `address`, `department_id`, `position`, `qualification`, `date_joined`, `created_at`, `updated_at`) VALUES
(3, '1122', NULL, 'MR MWANJE', 'Nicholas', 'Male', NULL, 'masikonicholas98@gmail.com', '0760845198', NULL, 1, 'Senior Lecturer', NULL, NULL, '2025-09-22 14:37:20', '2025-09-23 05:26:02');

-- --------------------------------------------------------

--
-- Table structure for table `staff_courses`
--

DROP TABLE IF EXISTS `staff_courses`;
CREATE TABLE IF NOT EXISTS `staff_courses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `course_id` int NOT NULL,
  `semester` int NOT NULL,
  `academic_year` year NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_teaching_assignment` (`staff_id`,`course_id`,`semester`,`academic_year`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
CREATE TABLE IF NOT EXISTS `students` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reg_number` varchar(20) NOT NULL,
  `user_id` int DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `department_id` int DEFAULT NULL,
  `program` varchar(100) NOT NULL,
  `year_of_study` int NOT NULL DEFAULT '1',
  `semester` int NOT NULL DEFAULT '1',
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reg_number` (`reg_number`),
  UNIQUE KEY `email` (`email`),
  KEY `user_id` (`user_id`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `reg_number`, `user_id`, `first_name`, `last_name`, `gender`, `date_of_birth`, `email`, `phone`, `address`, `department_id`, `program`, `year_of_study`, `semester`, `guardian_name`, `guardian_phone`, `created_at`, `updated_at`) VALUES
(1, '00163', NULL, 'Wasswa', 'Atibu', 'Male', NULL, 'wasswaatib@gmail.com', '0760845198', NULL, NULL, '', 1, 1, NULL, NULL, '2025-09-20 19:12:30', '2025-09-20 19:12:30'),
(3, '00164', NULL, 'Birungi', 'Joan', 'Female', NULL, 'birungi@gmail.com', '0742505141', NULL, NULL, '', 2, 1, NULL, NULL, '2025-09-22 14:46:46', '2025-09-22 14:46:46');

-- --------------------------------------------------------

--
-- Table structure for table `student_courses`
--

DROP TABLE IF EXISTS `student_courses`;
CREATE TABLE IF NOT EXISTS `student_courses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `course_id` int NOT NULL,
  `semester` int NOT NULL,
  `academic_year` year NOT NULL,
  `grade` varchar(2) DEFAULT NULL,
  `points` decimal(3,2) DEFAULT NULL,
  `remarks` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_enrollment` (`student_id`,`course_id`,`semester`,`academic_year`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`, `updated_at`) VALUES
(6, 'Masiko Nicholas', '$2y$10$iaPSuV4WhHO/HhQXXYTTVug6NURjjwyd52Zfei1fr3iTNgAg.k51.', '', '', '2025-09-22 01:55:18', '2025-09-22 01:55:18'),
(10, 'kuteesa', '$2y$10$3k1s278JcNqRNSDM6Br5Euzi4HKd5i7ZtL4zASlVExq03qkCC0qZq', 'kuteesa@gmail.com', 'staff', '2025-09-22 13:56:32', '2025-09-22 13:56:32'),
(11, 'masiko', '$2y$10$CLR3GMRQ6rERM4W0GO9gt.wtOdkgm6NH0DOW5ZBoYXf0WM5DU7iQS', 'masikonicholas98@gmail.com', 'staff', '2025-09-22 14:40:46', '2025-09-22 14:40:46');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `staff_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `staff_courses`
--
ALTER TABLE `staff_courses`
  ADD CONSTRAINT `staff_courses_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD CONSTRAINT `student_courses_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
