-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jul 04, 2017 at 02:42 PM
-- Server version: 10.1.8-MariaDB
-- PHP Version: 5.6.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `address1` varchar(255) NOT NULL,
  `address2` varchar(255) NOT NULL,
  `near_by` varchar(255) NOT NULL,
  `county_id` int(11) NOT NULL,
  `state_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `pincode` int(11) NOT NULL,
  `is_active` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `address1`, `address2`, `near_by`, `county_id`, `state_id`, `city_id`, `pincode`, `is_active`) VALUES
(1, 'AA 21, Salt Lake,', 'Sector 1', 'PNB more', 1, 1, 1, 457899, 0);

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `state_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `state_code` char(2) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `iso` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nicename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso3` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numcode` int(11) NOT NULL,
  `phonecode` int(11) NOT NULL,
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `fee` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `modefied` datetime NOT NULL,
  `modefied_by` int(11) DEFAULT NULL,
  `is_deleted` enum('true','false') NOT NULL DEFAULT 'false'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `fees`
--

INSERT INTO `fees` (`id`, `school_id`, `student_id`, `class_id`, `section_id`, `year`, `month`, `fee`, `created`, `created_by`, `modefied`, `modefied_by`, `is_deleted`) VALUES
(1, 5, 1, 1, 1, 2017, 3, 500, '2017-07-04 10:14:12', 5, '2017-07-04 10:14:12', NULL, 'false'),
(2, 5, 1, 1, 1, 2017, 3, 500, '2017-07-04 10:28:01', 5, '2017-07-04 10:28:01', NULL, 'false'),
(3, 5, 1, 1, 2, 2017, 5, 25, '2017-07-04 11:00:50', 5, '2017-07-04 11:00:50', NULL, 'false'),
(4, 5, 1, 1, 2, 2017, 5, 25, '2017-07-04 11:19:39', 5, '2017-07-04 11:19:39', NULL, 'false');

-- --------------------------------------------------------

--
-- Table structure for table `fee_structures`
--

CREATE TABLE `fee_structures` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `fee` int(11) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) NOT NULL,
  `is_active` enum('true','false') NOT NULL DEFAULT 'true',
  `is_deleted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `fee_structures`
--

INSERT INTO `fee_structures` (`id`, `school_id`, `class_id`, `section_id`, `fee`, `created`, `created_by`, `is_active`, `is_deleted`) VALUES
(1, 5, 1, 2, 25, '2017-07-04 10:37:25', 5, 'true', 0);

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email_verification_code` varchar(255) NOT NULL,
  `is_email_verified` int(11) NOT NULL DEFAULT '0',
  `created_by` int(11) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_by` int(11) DEFAULT NULL,
  `modified_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` int(11) NOT NULL DEFAULT '0',
  `is_deleted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`id`, `name`, `email`, `password`, `email_verification_code`, `is_email_verified`, `created_by`, `created`, `modified_by`, `modified_date`, `is_active`, `is_deleted`) VALUES
(5, 'Randhir Jha', 'randhir@isisdsn.net', '$2y$10$aXLIIKXAr8uScBMwkABat.HNv3hy2evtdWb.0aLM3tqUVEPuNR5fW', 'c8029b3dd2326ebe0b9dd4a50bcc91d4', 0, 1, '2017-05-31 11:50:22', NULL, '2017-05-31 11:50:22', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middel_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `father_name` varchar(255) NOT NULL,
  `mother_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` int(11) NOT NULL,
  `p_address_id` int(11) NOT NULL,
  `c_address_id` int(11) NOT NULL,
  `current_class_id` int(11) NOT NULL,
  `admission_class_id` int(11) NOT NULL,
  `gender` enum('M','F','O') NOT NULL DEFAULT 'M',
  `password` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_by` int(11) NOT NULL,
  `modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` int(11) NOT NULL,
  `is_deleted` int(11) NOT NULL DEFAULT '0',
  `is_email_verified` int(11) NOT NULL DEFAULT '0',
  `email_verification_code` varchar(255) DEFAULT NULL,
  `class_roll_number` int(11) NOT NULL,
  `current_section_id` int(11) NOT NULL,
  `admission_section_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `school_id`, `first_name`, `middel_name`, `last_name`, `father_name`, `mother_name`, `email`, `phone`, `p_address_id`, `c_address_id`, `current_class_id`, `admission_class_id`, `gender`, `password`, `created_by`, `created`, `modified_by`, `modified`, `is_active`, `is_deleted`, `is_email_verified`, `email_verification_code`, `class_roll_number`, `current_section_id`, `admission_section_id`) VALUES
(1, 5, 'Don', '', 'Kumar', 'Don', 'Don', 'don@yopmail.com', 1234567890, 1, 1, 1, 1, 'M', 0, 0, '2017-07-04 12:22:45', 0, '2017-07-04 12:22:45', 0, 0, 0, NULL, 0, 1, 1),
(2, 5, 'Don', '', 'Kumar', 'Don', 'Don', 'don@yopmail.com', 1234567890, 1, 1, 1, 1, 'M', 0, 0, '2017-07-04 12:39:52', 0, '2017-07-04 12:39:52', 0, 0, 0, '083bc27d5a7384023118301b8524dc79', 0, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `is_active` int(11) NOT NULL,
  `school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email_verification_code` varchar(255) NOT NULL,
  `is_email_verified` int(11) NOT NULL DEFAULT '0',
  `created_by` int(11) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_by` int(11) DEFAULT NULL,
  `modified_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` int(11) NOT NULL DEFAULT '0',
  `is_deleted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `email_verification_code`, `is_email_verified`, `created_by`, `created`, `modified_by`, `modified_date`, `is_active`, `is_deleted`) VALUES
(5, 'Randhir Jha', 'randhir@isisdsn.net', '$2y$10$aXLIIKXAr8uScBMwkABat.HNv3hy2evtdWb.0aLM3tqUVEPuNR5fW', 'c8029b3dd2326ebe0b9dd4a50bcc91d4', 0, 1, '2017-05-31 11:50:22', NULL, '2017-05-31 11:50:22', 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `fee_structures`
--
ALTER TABLE `fee_structures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
