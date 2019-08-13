-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Aug 13, 2019 at 04:03 PM
-- Server version: 5.7.25
-- PHP Version: 7.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lch`
--

-- --------------------------------------------------------

--
-- Table structure for table `ifa`
--

CREATE TABLE `ifa` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `zip` varchar(63) NOT NULL,
  `reg_num` varchar(63) NOT NULL,
  `dob` varchar(63) NOT NULL,
  `nationality` varchar(63) NOT NULL,
  `id_number` varchar(63) NOT NULL,
  `arrival_date` varchar(63) NOT NULL,
  `departure_date` varchar(63) NOT NULL,
  `exemption` varchar(63) NOT NULL,
  `exemption_proof_type` varchar(63) NOT NULL,
  `exemption_proof_num` varchar(63) NOT NULL,
  `consent` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `hash` varchar(63) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ifa`
--
ALTER TABLE `ifa`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ifa`
--
ALTER TABLE `ifa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
