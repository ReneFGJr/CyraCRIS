-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 15, 2025 at 03:12 PM
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
  `c_class_main` varchar(20) NOT NULL DEFAULT '',
  `c_type` char(1) NOT NULL,
  `c_description` text NOT NULL,
  `c_url` char(100) NOT NULL,
  `c_url_update` date NOT NULL DEFAULT '1900-01-01',
  `c_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rdf_class`
--

INSERT INTO `rdf_class` (`id_c`, `c_class`, `c_equivalent`, `c_prefix`, `c_class_main`, `c_type`, `c_description`, `c_url`, `c_url_update`, `c_created`) VALUES
(1, 'CorporateBody', 0, 0, 'Rf5YbTJXoizKnsEjJoit', 'C', '', 'http://webprotege.stanford.edu//Rf5YbTJXoizKnsEjJoitrY', '1900-01-01', '2025-08-15 14:32:26'),
(2, 'Literal', 0, 0, 'RFUuKDO9SgNtuaWzb92J', 'C', '', 'http://webprotege.stanford.edu//RFUuKDO9SgNtuaWzb92Jt9', '1900-01-01', '2025-08-15 14:32:26'),
(3, 'hasROR_ID', 0, 0, 'RDHr4SjH2Lf6fiEPA2gZ', 'P', '', 'http://webprotege.stanford.edu//RDHr4SjH2Lf6fiEPA2gZ6vG', '1900-01-01', '2025-08-15 14:32:26'),
(4, 'hasDOI', 0, 0, 'RCyQAWBfmVuOIOsjWOFV', 'P', '', 'http://webprotege.stanford.edu//RCyQAWBfmVuOIOsjWOFVNQU', '1900-01-01', '2025-08-15 14:32:26'),
(5, 'hasName', 0, 0, 'R7JWxdMsAY6cGuZnFfpw', 'P', '', 'http://webprotege.stanford.edu//R7JWxdMsAY6cGuZnFfpwddc', '1900-01-01', '2025-08-15 14:32:26'),
(6, 'hasCapesID', 0, 0, 'Rrirnc3EFkovAQd6EoXN', 'P', '', 'http://webprotege.stanford.edu//Rrirnc3EFkovAQd6EoXN8Z', '1900-01-01', '2025-08-15 14:32:26'),
(7, 'hasAcronym', 0, 0, 'RDWu6iU80HEkUhNHU2lN', 'P', '', 'http://webprotege.stanford.edu//RDWu6iU80HEkUhNHU2lNKBI', '1900-01-01', '2025-08-15 14:32:26'),
(8, 'hasISNI_ID', 0, 0, 'R8e8tNPbbdtZg5NcIGIQ', 'P', '', 'http://webprotege.stanford.edu//R8e8tNPbbdtZg5NcIGIQjcc', '1900-01-01', '2025-08-15 14:32:26'),
(9, 'Agente', 0, 0, 'R7T7So7pdDB3gJ7fTl2X', 'P', '', 'http://webprotege.stanford.edu//R7T7So7pdDB3gJ7fTl2XyRH', '1900-01-01', '2025-08-15 14:32:26');

-- --------------------------------------------------------

--
-- Table structure for table `rdf_class_domain`
--

CREATE TABLE `rdf_class_domain` (
  `id_cd` bigint(20) UNSIGNED NOT NULL,
  `cd_domain` int(11) NOT NULL,
  `cd_property` int(11) NOT NULL DEFAULT 0,
  `cd_range` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Table structure for table `rdf_concept`
--

CREATE TABLE `rdf_concept` (
  `id_cc` bigint(20) UNSIGNED NOT NULL,
  `cc_class` int(11) NOT NULL,
  `cc_use` int(11) NOT NULL DEFAULT 0,
  `c_equivalent` int(11) NOT NULL DEFAULT 0,
  `cc_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `cc_pref_term` int(11) NOT NULL,
  `cc_origin` char(20) NOT NULL,
  `cc_update` date NOT NULL,
  `cc_status` int(11) NOT NULL DEFAULT 0,
  `cc_version` int(11) NOT NULL DEFAULT 2
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rdf_data`
--

CREATE TABLE `rdf_data` (
  `id_d` bigint(20) UNSIGNED NOT NULL,
  `d_r1` int(11) NOT NULL,
  `d_p` int(11) NOT NULL,
  `d_r2` int(11) NOT NULL,
  `d_literal` int(11) NOT NULL DEFAULT 0,
  `d_c1` int(11) NOT NULL DEFAULT 0,
  `d_c2` int(11) NOT NULL DEFAULT 0,
  `d_creadted` timestamp NOT NULL DEFAULT current_timestamp(),
  `d_o` int(11) NOT NULL DEFAULT 0,
  `d_library` int(11) NOT NULL DEFAULT 0,
  `d_update` datetime DEFAULT NULL,
  `d_user` int(11) NOT NULL DEFAULT 0,
  `d_trust` int(11) NOT NULL DEFAULT 0,
  `d_ia` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rdf_form`
--

CREATE TABLE `rdf_form` (
  `id_rf` bigint(20) UNSIGNED NOT NULL,
  `rf_class` int(11) NOT NULL DEFAULT 0,
  `rf_group` char(10) NOT NULL,
  `rf_order` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rdf_literal`
--

CREATE TABLE `rdf_literal` (
  `id_n` bigint(20) UNSIGNED NOT NULL,
  `n_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `n_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `n_lock` int(11) NOT NULL DEFAULT 0,
  `n_lang` char(5) NOT NULL DEFAULT 'pt_BR',
  `n_md5` char(32) NOT NULL DEFAULT '',
  `n_delete` int(11) NOT NULL DEFAULT 0,
  `n_charset` varchar(10) NOT NULL DEFAULT 'NI'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rdf_prefix`
--

CREATE TABLE `rdf_prefix` (
  `id_prefix` bigint(20) UNSIGNED NOT NULL,
  `prefix_ref` char(30) NOT NULL,
  `prefix_url` char(250) NOT NULL,
  `prefix_ativo` int(11) NOT NULL DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rdt_temp_import`
--

CREATE TABLE `rdt_temp_import` (
  `id_ti` bigint(20) UNSIGNED NOT NULL,
  `ti_ID` int(11) NOT NULL,
  `ti_update` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

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
-- Indexes for table `rdf_class_domain`
--
ALTER TABLE `rdf_class_domain`
  ADD UNIQUE KEY `id_cd` (`id_cd`);

--
-- Indexes for table `rdf_concept`
--
ALTER TABLE `rdf_concept`
  ADD UNIQUE KEY `id_cc` (`id_cc`) USING BTREE,
  ADD UNIQUE KEY `class_cc` (`id_cc`,`cc_class`),
  ADD KEY `cc_term` (`cc_pref_term`),
  ADD KEY `cc_use` (`cc_use`),
  ADD KEY `cc_class` (`cc_class`,`cc_status`) USING BTREE;

--
-- Indexes for table `rdf_data`
--
ALTER TABLE `rdf_data`
  ADD UNIQUE KEY `id_d` (`id_d`),
  ADD KEY `rdf_data` (`d_r1`,`d_r2`,`d_p`,`d_literal`),
  ADD KEY `d_r1` (`d_r1`),
  ADD KEY `d_r2` (`d_r2`),
  ADD KEY `d_p` (`d_p`),
  ADD KEY `d_literal` (`d_literal`),
  ADD KEY `d_trust` (`d_trust`,`d_literal`,`d_r2`) USING BTREE,
  ADD KEY `c1` (`d_c1`),
  ADD KEY `c2` (`d_c2`);

--
-- Indexes for table `rdf_form`
--
ALTER TABLE `rdf_form`
  ADD UNIQUE KEY `id_rf` (`id_rf`);

--
-- Indexes for table `rdf_literal`
--
ALTER TABLE `rdf_literal`
  ADD UNIQUE KEY `id_n` (`id_n`),
  ADD KEY `n_md5` (`n_md5`) USING BTREE,
  ADD KEY `n_name` (`n_name`(20)),
  ADD KEY `Leter` (`n_name`(1),`n_lang`) USING BTREE,
  ADD KEY `n_charset` (`n_charset`);

--
-- Indexes for table `rdf_prefix`
--
ALTER TABLE `rdf_prefix`
  ADD UNIQUE KEY `id_prefix` (`id_prefix`);

--
-- Indexes for table `rdt_temp_import`
--
ALTER TABLE `rdt_temp_import`
  ADD UNIQUE KEY `id_ti` (`id_ti`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `rdf_class`
--
ALTER TABLE `rdf_class`
  MODIFY `id_c` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rdf_class_domain`
--
ALTER TABLE `rdf_class_domain`
  MODIFY `id_cd` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rdf_concept`
--
ALTER TABLE `rdf_concept`
  MODIFY `id_cc` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rdf_data`
--
ALTER TABLE `rdf_data`
  MODIFY `id_d` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rdf_form`
--
ALTER TABLE `rdf_form`
  MODIFY `id_rf` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rdf_literal`
--
ALTER TABLE `rdf_literal`
  MODIFY `id_n` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rdf_prefix`
--
ALTER TABLE `rdf_prefix`
  MODIFY `id_prefix` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rdt_temp_import`
--
ALTER TABLE `rdt_temp_import`
  MODIFY `id_ti` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
