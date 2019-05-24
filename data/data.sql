-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 23, 2019 at 02:40 PM
-- Server version: 10.1.34-MariaDB
-- PHP Version: 7.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `dotkernel`
--

--
-- Dumping data for table `oauth_clients`
--

INSERT INTO `oauth_clients` (`name`, `user_id`, `secret`, `redirect`, `personal_access_client`, `password_client`, `revoked`, `created_at`, `updated_at`) VALUES
('dotkernel', NULL, '$2y$10$5asVMXKmdptyrYZ82k7YcOPCSSFz7xSp5AxzxD3fsr.ZnbAztFW8u', '/redirect', 1, 1, NULL, NULL, NULL);

--
-- Dumping data for table `oauth_scopes`
--

INSERT INTO `oauth_scopes` (`id`) VALUES
('api');

--
-- Dumping data for table `user_role`
--

INSERT INTO `user_role` (`uuid`, `name`, `created`, `updated`) VALUES
(0x11e9650380c6d846818a00155daa5500, 'member', '2019-05-13 16:29:46', NULL),
(0x11e97666311dfbb6a76b00155daa5500, 'guest', '2019-05-17 11:53:27', NULL);
COMMIT;
