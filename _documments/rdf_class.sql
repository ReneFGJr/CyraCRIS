-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 15, 2025 at 12:46 PM
-- Server version: 10.11.13-MariaDB-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cyraCRIS`
--

-- --------------------------------------------------------

--
-- Table structure for table `rdf_class`
--

CREATE TABLE `rdf_class` (
  `id_c` bigint(20) UNSIGNED NOT NULL,
  `c_class` varchar(200) NOT NULL,
  `c_equivalent` int(11) NOT NULL DEFAULT 0,
  `c_prefix` int(11) NOT NULL DEFAULT 0,
  `c_class_main` int(11) NOT NULL DEFAULT 0,
  `c_type` char(1) NOT NULL,
  `c_description` text NOT NULL,
  `c_url` char(100) NOT NULL,
  `c_url_update` date NOT NULL DEFAULT '1900-01-01',
  `c_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `rdf_class`
--
ALTER TABLE `rdf_class`
  ADD UNIQUE KEY `id_c` (`id_c`),
  ADD UNIQUE KEY `classes` (`c_class`(30),`c_prefix`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `rdf_class`
--
ALTER TABLE `rdf_class`
  MODIFY `id_c` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
