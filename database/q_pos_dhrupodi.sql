-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 17, 2026 at 12:05 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `q_pos_dhrupodi`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `parent_id` int DEFAULT NULL,
  `account_code` int DEFAULT NULL,
  `account_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_type` enum('Asset','Liability','Income','Expense','Equity') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_group` tinyint NOT NULL DEFAULT '0',
  `reference_type` enum('Client','Supplier','UplinkProvider','Employee','System') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_key_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_fund_account` tinyint DEFAULT NULL,
  `opening_balance` decimal(12,2) DEFAULT '0.00',
  `opening_balance_type` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `parent_id`, `account_code`, `account_name`, `account_type`, `is_group`, `reference_type`, `reference_id`, `system_key_name`, `is_fund_account`, `opening_balance`, `opening_balance_type`, `created_at`, `updated_at`, `deleted_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(21, NULL, 1000, 'Assets', 'Asset', 1, 'System', '', NULL, NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-01-09 10:40:11', NULL, 'active', 0, 0, 0, 0),
(22, 21, 1100, 'Cash', 'Asset', 1, 'System', NULL, 'Cash', 1, 200000.00, 'Debit', '2026-01-08 10:37:00', '2026-01-11 03:41:01', NULL, 'active', 0, 0, 0, 0),
(23, 22, 1110, 'Petty Cash', 'Asset', 0, 'System', NULL, NULL, 1, 5000.00, 'Debit', '2026-01-08 16:37:00', '2026-01-13 22:07:20', NULL, 'active', 0, 0, 0, 0),
(24, 21, 1120, 'Bank', 'Asset', 0, 'System', NULL, 'Bank,Cheque,BankTransfer', 1, 5000.00, 'Debit', '2026-01-07 22:37:00', '2026-02-12 15:48:45', NULL, 'active', 0, 0, 0, 0),
(25, 21, 1130, 'bKash/Nagad/Rocket', 'Asset', 0, 'System', '', 'MobileBanking', NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-02-13 15:48:48', NULL, 'active', 0, 0, 0, 0),
(26, 21, 1200, 'Accounts Receivable', 'Asset', 0, 'System', '', 'accounts-receivable', NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-01-15 03:05:49', NULL, 'active', 0, 0, 0, 0),
(27, 21, 1300, 'Inventory/Stock', 'Asset', 0, 'System', '', NULL, NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-01-09 22:37:00', NULL, 'active', 0, 0, 0, 0),
(28, 21, 1400, 'Office Equipment', 'Asset', 0, 'System', '', NULL, NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-01-09 22:37:00', NULL, 'active', 0, 0, 0, 0),
(29, 21, 1500, 'Furniture & Fixtures', 'Asset', 0, 'System', '', NULL, NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-01-09 22:37:00', NULL, 'active', 0, 0, 0, 0),
(30, NULL, 2000, 'Liabilities', 'Liability', 1, 'System', '', NULL, NULL, 0.00, 'Credit', '2026-01-08 22:37:00', '2026-01-09 10:40:16', NULL, 'active', 0, 0, 0, 0),
(31, 30, 2100, 'Accounts Payable', 'Liability', 0, 'System', '', 'accounts-payable', NULL, 0.00, 'Credit', '2026-01-08 22:37:00', '2026-01-15 03:05:55', NULL, 'active', 0, 0, 0, 0),
(32, 30, 2200, 'Salary Payable', 'Liability', 0, 'System', '', 'salary-payable', NULL, 0.00, 'Credit', '2026-01-08 22:37:00', '2026-01-15 02:36:03', NULL, 'active', 0, 0, 0, 0),
(33, 30, 2300, 'Bonus Payable', 'Liability', 0, 'System', '', 'bonus-payable', NULL, 0.00, 'Credit', '2026-01-08 22:37:00', '2026-02-06 13:22:24', NULL, 'active', 0, 0, 0, 0),
(34, 30, 2400, 'TAX/VAT Payable', 'Liability', 0, 'System', '', NULL, NULL, 0.00, 'Credit', '2026-01-08 22:37:00', '2026-01-09 22:37:00', NULL, 'active', 0, 0, 0, 0),
(35, NULL, 3000, 'Equity', 'Equity', 1, 'System', '', NULL, NULL, 0.00, 'Credit', '2026-01-08 22:37:00', '2026-01-09 10:40:20', NULL, 'active', 0, 0, 0, 0),
(36, 35, 3100, 'Owner Capital', 'Equity', 0, 'System', '', NULL, NULL, 0.00, 'Credit', '2026-01-08 22:37:00', '2026-01-09 22:37:00', NULL, 'active', 0, 0, 0, 0),
(37, 35, 3200, 'Owner Withdraw', 'Equity', 0, 'System', '', 'owner-withdraw', NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-02-15 13:36:08', NULL, 'active', 0, 0, 0, 0),
(38, 35, 3300, 'Retained Earnings', 'Equity', 0, 'System', '', NULL, NULL, 0.00, 'Credit', '2026-01-08 22:37:00', '2026-01-09 22:37:00', NULL, 'active', 0, 0, 0, 0),
(39, NULL, 4000, 'Income', 'Income', 1, 'System', '', NULL, NULL, 0.00, 'Credit', '2026-01-08 22:37:00', '2026-01-09 10:40:25', NULL, 'active', 0, 0, 0, 0),
(40, 39, 4100, 'Sales Revenue', 'Income', 0, 'System', '', 'sales-revenue', NULL, 0.00, 'Credit', '2026-01-08 22:37:00', '2026-01-15 05:41:52', NULL, 'active', 0, 0, 0, 0),
(41, 39, 4200, 'Service Income', 'Income', 0, 'System', '', NULL, NULL, 0.00, 'Credit', '2026-01-08 22:37:00', '2026-01-09 22:37:00', NULL, 'active', 0, 0, 0, 0),
(42, 39, 4300, 'Commission Income', 'Income', 0, 'System', '', NULL, NULL, 0.00, 'Credit', '2026-01-08 22:37:00', '2026-01-09 22:37:00', NULL, 'active', 0, 0, 0, 0),
(43, 39, 4400, 'Other Income', 'Income', 0, 'System', '', NULL, NULL, 0.00, 'Credit', '2026-01-08 22:37:00', '2026-01-09 22:37:00', NULL, 'active', 0, 0, 0, 0),
(44, NULL, 5000, 'Expenses', 'Expense', 1, 'System', '', NULL, NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-01-09 10:40:29', NULL, 'active', 0, 0, 0, 0),
(45, 44, 5100, 'Purchase', 'Expense', 0, 'System', '', 'purchase', NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-01-15 02:27:59', NULL, 'active', 0, 0, 0, 0),
(46, 44, 5200, 'Salary Expense', 'Expense', 0, 'System', '', 'SalaryExpense', NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-01-12 06:31:28', NULL, 'active', 0, 0, 0, 0),
(47, 44, 5300, 'Rent Expense', 'Expense', 0, 'System', '', NULL, NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-01-09 22:37:00', NULL, 'active', 0, 0, 0, 0),
(48, 44, 5400, 'Utility Bills', 'Expense', 0, 'System', '', NULL, NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-01-09 22:37:00', NULL, 'active', 0, 0, 0, 0),
(49, 44, 5500, 'Internet/Mobile Bill', 'Expense', 0, 'System', '', NULL, NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-01-09 22:37:00', NULL, 'active', 0, 0, 0, 0),
(50, 44, 5600, 'Office Supplies', 'Expense', 0, 'System', '', 'office-supplies-expense', NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-01-31 03:48:11', NULL, 'active', 0, 0, 0, 0),
(51, 44, 5700, 'Advertisement & Marketing Expense', 'Expense', 0, 'System', '', NULL, NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-01-09 22:37:00', NULL, 'active', 0, 0, 0, 0),
(52, 44, 5800, 'Travel Expense', 'Expense', 0, 'System', '', NULL, NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-01-09 22:37:00', NULL, 'active', 0, 0, 0, 0),
(53, 44, 5900, 'Bank Charges', 'Expense', 0, 'System', '', NULL, NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-01-09 22:37:00', NULL, 'active', 0, 0, 0, 0),
(54, 44, 5910, 'bKash/Nagad Charges', 'Expense', 0, 'System', '', NULL, NULL, 0.00, 'Debit', '2026-01-08 22:37:00', '2026-01-09 22:37:00', NULL, 'active', 0, 0, 0, 0),
(55, 44, 1001, 'Discount', 'Expense', 0, 'System', NULL, NULL, NULL, 0.00, NULL, '2026-01-09 11:22:50', '2026-01-09 11:22:50', NULL, 'active', 1, 1, 1, 1),
(66, 44, 1003, 'Conveyance', 'Expense', 0, 'System', NULL, NULL, NULL, 0.00, NULL, '2026-01-10 05:33:37', '2026-01-10 05:33:37', NULL, 'active', 1, 1, 1, 1),
(68, 31, 1004, '111 - Chester Long', 'Liability', 0, 'Supplier', '1', NULL, NULL, 0.00, 'Debit', '2026-01-10 12:11:55', '2026-01-10 12:11:55', NULL, 'active', 1, 1, 1, 1),
(69, 44, 1005, 'Bandwidth Expense', 'Expense', 0, 'System', NULL, 'bandwidth-expense', NULL, 0.00, NULL, '2026-01-10 12:27:26', '2026-01-12 09:15:05', NULL, 'active', 1, 1, 1, 1),
(70, 39, 1006, 'Bandwidth Income', 'Income', 0, 'System', NULL, 'bandwidth-income', NULL, 0.00, NULL, '2026-01-10 13:22:43', '2026-01-15 05:40:49', NULL, 'active', 1, 1, 1, 1),
(71, 44, 1007, 'Office Loan', 'Expense', 0, 'System', NULL, 'Loan', NULL, 0.00, NULL, '2026-01-11 07:11:41', '2026-01-11 15:44:52', NULL, 'active', 1, 1, 1, 1),
(72, 44, 1008, 'Advance Salary', 'Expense', 0, 'System', NULL, 'Advance', NULL, 0.00, NULL, '2026-01-11 07:12:03', '2026-01-11 15:46:03', NULL, 'active', 1, 1, 1, 1),
(73, 44, 1009, 'Bonus', 'Expense', 0, 'System', NULL, 'bonus-expense', NULL, 0.00, NULL, '2026-01-12 06:32:57', '2026-01-12 06:33:34', NULL, 'active', 1, 1, 1, 1),
(74, 44, 1010, 'Commission Expense', 'Expense', 0, 'System', NULL, 'commission-expense', NULL, 0.00, NULL, '2026-01-12 06:34:55', '2026-01-12 06:35:14', NULL, 'active', 1, 1, 1, 1),
(75, 21, 1011, 'Employee Loan Receivable', 'Asset', 0, 'System', NULL, 'employee-loan-receivable', NULL, 0.00, NULL, '2026-01-12 06:40:54', '2026-01-12 06:41:28', NULL, 'active', 1, 1, 1, 1),
(76, 39, 1012, 'Employee Salary Deduction', 'Asset', 0, 'System', NULL, 'employee-salary-deduction', NULL, 0.00, NULL, '2026-01-12 06:44:21', '2026-02-06 13:09:12', NULL, 'active', 1, 1, 1, 1),
(77, 44, 1013, 'Bannar Printing', 'Expense', 0, 'System', NULL, NULL, NULL, 0.00, NULL, '2026-02-01 20:34:46', '2026-02-01 20:34:46', NULL, 'active', 1, 1, 1, 1),
(78, 44, 1014, 'Leaflet Printing', 'Expense', 0, 'System', NULL, NULL, NULL, 0.00, NULL, '2026-02-01 20:35:17', '2026-02-01 20:35:17', NULL, 'active', 1, 1, 1, 1),
(79, 30, 1015, 'Commission Payable', 'Liability', 0, 'System', NULL, 'commission-payable', NULL, 0.00, NULL, '2026-02-06 13:03:05', '2026-02-06 13:03:48', NULL, 'active', 1, 1, 1, 1),
(80, 30, 1015, 'Expense Payable', 'Liability', 0, 'System', NULL, 'expense-payable', NULL, 0.00, NULL, '2026-02-06 13:03:05', '2026-02-06 13:03:48', NULL, 'active', 1, 1, 1, 1),
(81, 21, 1016, 'Bank Asia', 'Asset', 0, 'System', NULL, NULL, 1, 1000.00, NULL, '2026-07-11 12:12:31', '2026-07-11 12:12:31', NULL, 'active', 1, 1, 1, 1),
(82, 35, 1017, 'Opening Balance Equity', 'Equity', 0, 'System', NULL, 'opening-balance-equity', 0, NULL, NULL, '2026-07-14 15:39:30', '2026-07-15 03:48:27', NULL, 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint UNSIGNED NOT NULL,
  `role_id` tinyint NOT NULL,
  `name` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `gender` enum('male','female','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `state` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `road_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `block` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=Active, 1=Deactive',
  `status` enum('active','deactive','trash') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` bigint UNSIGNED NOT NULL,
  `full_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organization` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agents`
--

INSERT INTO `agents` (`id`, `full_name`, `mobile`, `organization`, `designation`, `image`, `address`, `status`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Maxwell Hebert', '95278961574', 'Snow and Gonzalez Trading', 'Similique iste susci', 'upload/agent/260811063600-8584IMG_20220115_081504.jpg', 'Quisquam sint et fu', 'active', NULL, NULL, NULL, '2026-08-11 12:36:00', '2026-08-11 12:36:00'),
(2, 'Brian Gibbs', '99773522604', 'Pope and Ewing Inc', 'Dolorem ut in animi', 'upload/agent/260811072856-7756.png', 'Qui exercitation dol', 'active', NULL, NULL, NULL, '2026-08-11 13:28:56', '2026-08-11 13:28:56');

-- --------------------------------------------------------

--
-- Table structure for table `areas`
--

CREATE TABLE `areas` (
  `id` bigint UNSIGNED NOT NULL,
  `district_id` int NOT NULL,
  `area_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `areas`
--

INSERT INTO `areas` (`id`, `district_id`, `area_name`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 13, 'Sector 7, Uttara', '2026-01-26 18:29:42', '2026-01-26 18:29:42', 'active', 1, 1, 1, 1),
(2, 13, 'Sector 8, Uttara', '2026-01-26 18:29:57', '2026-01-26 18:29:57', 'active', 1, 1, 1, 1),
(3, 13, 'Sector 1, Uttara', '2026-01-26 18:30:14', '2026-01-26 18:30:14', 'active', 1, 1, 1, 1),
(4, 13, 'Sector 3, Uttara', '2026-01-26 12:30:46', '2026-01-26 12:30:46', 'active', 1, 1, 1, 1),
(5, 13, 'Sector 2, Uttara', '2026-01-26 18:31:05', '2026-01-26 18:31:05', 'active', 1, 1, 1, 1),
(6, 13, 'Sector 4, Uttara', '2026-01-26 18:31:24', '2026-01-26 18:31:24', 'active', 1, 1, 1, 1),
(7, 13, 'Sector 5, Uttara', '2026-01-26 18:31:39', '2026-01-26 18:31:39', 'active', 1, 1, 1, 1),
(8, 13, 'Sector 6, Uttara', '2026-01-26 18:31:56', '2026-01-26 18:31:56', 'active', 1, 1, 1, 1),
(9, 13, 'Sector 9, Uttara', '2026-01-26 18:32:26', '2026-01-26 18:32:26', 'active', 1, 1, 1, 1),
(10, 13, 'Sector 10, Uttara', '2026-01-26 18:32:43', '2026-01-26 18:32:43', 'active', 1, 1, 1, 1),
(11, 13, 'Sector 11, Uttara', '2026-01-26 18:32:55', '2026-01-26 18:32:55', 'active', 1, 1, 1, 1),
(12, 13, 'Sector 15, Uttara', '2026-01-26 18:33:05', '2026-01-26 18:33:05', 'active', 1, 1, 1, 1),
(13, 13, 'Sector 16, Uttara', '2026-01-26 18:33:15', '2026-01-26 18:33:15', 'active', 1, 1, 1, 1),
(14, 13, 'Sector 17, Uttara', '2026-01-26 18:33:26', '2026-01-26 18:33:26', 'active', 1, 1, 1, 1),
(15, 13, 'Sector 18, Uttara', '2026-01-26 18:33:37', '2026-01-26 18:33:37', 'active', 1, 1, 1, 1),
(16, 13, 'Khilkhet', '2026-01-26 18:34:13', '2026-01-26 18:34:13', 'active', 1, 1, 1, 1),
(17, 13, 'Nikunjo 1', '2026-01-26 18:34:26', '2026-01-26 18:34:26', 'active', 1, 1, 1, 1),
(18, 13, 'Nikunjo 2', '2026-01-26 18:34:38', '2026-01-26 18:34:38', 'active', 1, 1, 1, 1),
(19, 13, 'Banani', '2026-01-26 18:34:57', '2026-01-26 18:34:57', 'active', 1, 1, 1, 1),
(20, 13, 'Gulshan 1', '2026-01-26 18:35:07', '2026-01-26 18:35:07', 'active', 1, 1, 1, 1),
(21, 13, 'Gulshan 2', '2026-01-26 18:35:24', '2026-01-26 18:35:24', 'active', 1, 1, 1, 1),
(22, 28, 'Bhirab Town', '2026-01-26 18:36:07', '2026-01-26 18:36:07', 'active', 1, 1, 1, 1),
(23, 5, 'Lalmohon', '2026-01-26 18:36:23', '2026-01-26 18:36:23', 'active', 1, 1, 1, 1),
(24, 43, 'Palash', '2026-01-26 18:36:59', '2026-01-26 18:36:59', 'active', 1, 1, 1, 1),
(25, 43, 'Narshindi Sadar', '2026-01-26 18:37:28', '2026-01-26 18:37:28', 'active', 1, 1, 1, 1),
(26, 43, 'Chinispur', '2026-01-29 12:47:30', '2026-01-29 12:47:30', 'active', 1, 1, 1, 1),
(27, 42, 'Cawla', '2026-01-29 12:47:42', '2026-01-29 12:47:42', 'active', 1, 1, 1, 1),
(28, 43, 'Birpur', '2026-01-29 12:47:53', '2026-01-29 12:47:53', 'active', 1, 1, 1, 1),
(29, 18, 'College get, Tongi', '2026-01-29 12:49:38', '2026-01-29 12:49:38', 'active', 1, 1, 1, 1),
(30, 18, 'Kashimpur', '2026-01-29 12:49:53', '2026-01-29 12:49:53', 'active', 1, 1, 1, 1),
(31, 18, 'Shilmon', '2026-01-29 12:55:31', '2026-01-29 12:55:31', 'active', 1, 1, 1, 1),
(32, 18, 'Khilkur', '2026-01-29 06:58:00', '2026-01-29 06:58:00', 'active', 1, 1, 1, 1),
(33, 18, 'Boadbazar', '2026-01-29 19:40:11', '2026-01-29 19:40:11', 'active', 1, 1, 1, 1),
(34, 43, 'Hasnabad', '2026-01-31 17:52:12', '2026-01-31 17:52:12', 'active', 1, 1, 1, 1),
(35, 4, 'barishal Sadar', '2026-02-02 10:32:59', '2026-02-02 10:32:59', 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `banks`
--

CREATE TABLE `banks` (
  `id` bigint UNSIGNED NOT NULL,
  `bank_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banks`
--

INSERT INTO `banks` (`id`, `bank_name`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 'Bank Asia', '2026-01-31 05:56:33', '2026-01-31 05:56:33', 'active', 1, 1, 1, 1),
(2, 'Mutual Trust Bank', '2026-01-31 05:58:19', '2026-01-31 05:58:19', 'active', 1, 1, 1, 1),
(3, 'City Bank', '2026-01-31 05:59:07', '2026-01-31 05:59:07', 'active', 1, 1, 1, 1),
(4, 'Dutch Bangla Bank', '2026-02-01 16:09:36', '2026-02-01 16:09:36', 'active', 1, 1, 1, 1),
(5, 'Brac bank PLC', '2026-02-01 19:19:27', '2026-02-01 19:19:27', 'active', 1, 1, 1, 1),
(6, 'Prime bank Limited', '2026-02-01 20:48:06', '2026-02-01 20:48:06', 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `district_id` int DEFAULT NULL,
  `area_id` int NOT NULL,
  `latitude` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitude` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `branch_name`, `mobile`, `email`, `address`, `district_id`, `area_id`, `latitude`, `longitude`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 'Uttara Head Office', '01626222777', 'info@beeonline.com.bd', 'House 9, Road 2, Sector 7, Uttara,', 13, 1, NULL, NULL, '2026-01-26 18:38:20', '2026-01-26 18:38:20', 'active', 1, 1, 1, 1),
(2, 'Khilkhet POP', '01626222777', 'info@beeonline.com.bd', 'Khikhet bazar, Khikhet Dhaka', 13, 16, NULL, NULL, '2026-01-26 18:39:20', '2026-01-26 18:39:20', 'active', 1, 1, 1, 1),
(3, 'Lalmohon POP', '01719264379', 'test@beeonline.com.bd', 'Talukder Boarding, Lalmohon, Bhola', 5, 23, '22.34005333515656', '90.7344859374057', '2026-01-29 19:33:40', '2026-01-29 19:33:40', 'active', 1, 1, 1, 1),
(4, 'Boadbazar POP', '01926596398', 'test@beeonline.com.bd', 'Block B, Bottola Road, Boadbazar, Gazipur', 18, 33, '23°56\'46.12\"N', '90°23\'4.22\"E', '2026-01-29 19:36:55', '2026-01-29 19:40:42', 'active', 1, 1, 1, 1),
(5, 'College get', '01817040723', 'test@beeonline.com.bd', 'kafil Uddin Road, tongi, Gazipur', 18, 29, '23.911515585201908', '90.39745633667522', '2026-01-29 19:39:41', '2026-01-29 19:39:41', 'active', 1, 1, 1, 1),
(6, 'Bhirab New Town', '01635273581', 'test@beeonline.com.bd', 'Bhirab Bus Stand, Bhirab, Kishoregong', 28, 22, '24.055756514825077', '90.98057244695606', '2026-01-29 19:43:23', '2026-01-29 19:43:23', 'active', 1, 1, 1, 1),
(7, 'Banani POP', '01626222777', 'info@beeonline.com.bd', 'undefined', 13, 19, NULL, NULL, '2026-01-31 01:39:35', '2026-01-31 01:39:35', 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `module_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sorting` int NOT NULL DEFAULT '0',
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `module_name`, `title`, `slug`, `sorting`, `status`, `created_at`, `updated_at`) VALUES
(3, 'Bandwidth', 'INTERNET', 'internet', 1, 'active', '2025-12-25 03:38:54', '2025-12-25 03:38:54'),
(4, 'Bandwidth', 'FNA', 'facebook', 2, 'active', '2025-12-25 03:39:08', '2025-12-25 03:39:08'),
(5, 'Bandwidth', 'Fiberathome_NTTN_DATA', 'gpt', 3, 'active', '2025-12-24 15:39:26', '2025-12-24 15:39:26'),
(6, 'Item', 'Networking', 'networking', 4, 'active', '2025-12-27 07:05:01', '2025-12-27 07:05:01'),
(7, 'Bandwidth', 'GGC', 'ggc', 5, 'active', '2026-01-26 18:19:29', '2026-01-26 18:19:29'),
(8, 'Bandwidth', 'BDIX', 'bdix', 6, 'active', '2026-01-26 18:19:49', '2026-01-26 18:19:49'),
(9, 'Bandwidth', 'Summit_NTTN_DATA', 'summit-nttn-data', 7, 'active', '2026-01-26 18:21:28', '2026-01-26 18:21:28'),
(17, 'Item', 'OLT', 'olt', 15, 'active', '2026-01-26 19:10:30', '2026-01-26 19:10:30'),
(18, 'Item', 'Patch Cord', 'patch-cord', 16, 'active', '2026-01-26 19:10:43', '2026-01-26 19:10:43'),
(19, 'Item', 'Slipper', 'slipper', 17, 'active', '2026-01-26 19:11:28', '2026-01-26 19:11:28'),
(20, 'Item', 'TJ BOX 2 WAY', 'tj-box-2-way', 18, 'active', '2026-01-26 19:11:47', '2026-01-26 19:11:47'),
(21, 'Item', 'TJ BOX 4 WAY', 'tj-box-4-way', 19, 'active', '2026-01-26 19:11:57', '2026-01-26 19:11:57'),
(22, 'Item', 'TJ BOX 8 WAY', 'tj-box-8-way', 20, 'active', '2026-01-26 19:12:06', '2026-01-26 19:12:06'),
(23, 'Item', 'Crimping tools', 'crimping-tools', 21, 'active', '2026-01-26 19:15:47', '2026-01-26 19:15:47'),
(24, 'Item', 'Cable Testers', 'cable-testers', 22, 'active', '2026-01-26 19:16:06', '2026-01-26 19:16:06'),
(25, 'Bandwidth', 'CDN', 'data', 23, 'active', '2026-01-28 10:47:38', '2026-01-28 10:47:38'),
(27, 'Bandwidth', 'Bison CDN', 'bison-cdn', 24, 'active', '2026-01-29 15:37:02', '2026-01-29 15:37:02');

-- --------------------------------------------------------

--
-- Table structure for table `challans`
--

CREATE TABLE `challans` (
  `id` bigint UNSIGNED NOT NULL,
  `workorder_id` int NOT NULL,
  `challan_no` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `challan_date` date NOT NULL,
  `client_id` int NOT NULL,
  `delivery_location` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `receive_by` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `receive_by_designation` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `receive_by_mobile` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivery_by` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivery_by_mobile` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `challans`
--

INSERT INTO `challans` (`id`, `workorder_id`, `challan_no`, `challan_date`, `client_id`, `delivery_location`, `receive_by`, `receive_by_designation`, `receive_by_mobile`, `delivery_by`, `delivery_by_mobile`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 1, '111', '2026-03-27', 1, 'Quod non officiis ex', 'Oliver Tyler', '', '01436642787', '', '', '2026-03-26 15:28:57', '2026-03-26 15:28:57', 'active', 1, 1, 1, 1),
(3, 2, '112', '2026-03-28', 1, 'undefined', 'Oliver Tyler', '', '01436642787', '', '', '2026-03-28 04:42:26', '2026-03-28 04:42:26', 'active', 1, 1, 1, 1),
(4, 3, '113', '2026-07-13', 1, 'Dhaka Badda -1205', 'Oliver Tyler', '', '01436642787', '', '', '2026-07-13 11:03:45', '2026-07-13 11:03:45', 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `challan_details`
--

CREATE TABLE `challan_details` (
  `id` bigint UNSIGNED NOT NULL,
  `challan_id` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `workorder_detail_id` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_id` int DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` decimal(8,2) NOT NULL,
  `receive_qty` decimal(8,2) NOT NULL,
  `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `challan_details`
--

INSERT INTO `challan_details` (`id`, `challan_id`, `workorder_detail_id`, `item_id`, `description`, `qty`, `receive_qty`, `remark`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, '1', '1', NULL, 'Tempor consectetur', 550.00, 250.00, '', '2026-03-27 03:28:57', '2026-03-27 03:32:16', 'active', 1, 1, 1, 1),
(2, '1', '2', NULL, 'Cloth cutter blade', 300.00, 300.00, '', '2026-03-27 03:28:58', '2026-03-27 03:29:41', 'active', 1, 1, 1, 1),
(5, '3', '3', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01J-101-C8\nCom style number - BSFJA01J-101\nColor - OFF WHITE\nSize - 2T\nUpc/ean (gtin) - 198740903450\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:26', '2026-03-28 04:42:26', 'active', 1, 1, 1, 1),
(6, '3', '4', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01J-101-C8\nCom style number - BSFJA01J-101\nColor - OFF WHITE\nSize - 3T\nUpc/ean (gtin) - 198740903467\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:26', '2026-03-28 04:42:26', 'active', 1, 1, 1, 1),
(7, '3', '5', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01J-101-C8\nCom style number - BSFJA01J-101\nColor - OFF WHITE\nSize - 4\nUpc/ean (gtin) - 198740903474\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:26', '2026-03-28 04:42:26', 'active', 1, 1, 1, 1),
(8, '3', '6', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01J-101-C8\nCom style number - BSFJA01J-101\nColor - OFF WHITE\nSize - 5\nUpc/ean (gtin) - 198740903481\nLabel id - DIL', 308.00, 0.00, '', '2026-03-28 04:42:26', '2026-03-28 04:42:26', 'active', 1, 1, 1, 1),
(9, '3', '7', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01J-101-C8\nCom style number - BSFJA01J-101\nColor - OFF WHITE\nSize - 6\nUpc/ean (gtin) - 198740903498\nLabel id - DIL', 308.00, 0.00, '', '2026-03-28 04:42:26', '2026-03-28 04:42:26', 'active', 1, 1, 1, 1),
(10, '3', '8', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01J-101-C8\nCom style number - BSFJA01J-101\nColor - OFF WHITE\nSize - 7\nUpc/ean (gtin) - 198740903504\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:26', '2026-03-28 04:42:26', 'active', 1, 1, 1, 1),
(11, '3', '9', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03J-401-C8\nCom style number - BSFJA03J-401\nColor - DARK DENIM HEATHER\nSize - 2T\nUpc/ean (gtin) - 198740900091\nLabel id - DIL', 101.00, 0.00, '', '2026-03-28 04:42:26', '2026-03-28 04:42:26', 'active', 1, 1, 1, 1),
(12, '3', '10', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03J-401-C8\nCom style number - BSFJA03J-401\nColor - DARK DENIM HEATHER\nSize - 3T\nUpc/ean (gtin) - 198740903405\nLabel id - DIL', 101.00, 0.00, '', '2026-03-28 04:42:26', '2026-03-28 04:42:26', 'active', 1, 1, 1, 1),
(13, '3', '11', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03J-401-C8\nCom style number - BSFJA03J-401\nColor - DARK DENIM HEATHER\nSize - 4\nUpc/ean (gtin) - 198740903412\nLabel id - DIL', 101.00, 0.00, '', '2026-03-28 04:42:26', '2026-03-28 04:42:26', 'active', 1, 1, 1, 1),
(14, '3', '12', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03J-401-C8\nCom style number - BSFJA03J-401\nColor - DARK DENIM HEATHER\nSize - 5\nUpc/ean (gtin) - 198740903429\nLabel id - DIL', 182.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(15, '3', '13', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03J-401-C8\nCom style number - BSFJA03J-401\nColor - DARK DENIM HEATHER\nSize - 6\nUpc/ean (gtin) - 198740903436\nLabel id - DIL', 182.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(16, '3', '14', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03J-401-C8\nCom style number - BSFJA03J-401\nColor - DARK DENIM HEATHER\nSize - 7\nUpc/ean (gtin) - 198740903443\nLabel id - DIL', 101.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(17, '3', '15', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05J-415-C8\nCom style number - BSFJA05J-415\nColor - ODYSSEY GREY\nSize - 2T\nUpc/ean (gtin) - 198740903634\nLabel id - DIL', 134.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(18, '3', '16', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05J-415-C8\nCom style number - BSFJA05J-415\nColor - ODYSSEY GREY\nSize - 3T\nUpc/ean (gtin) - 198740903641\nLabel id - DIL', 134.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(19, '3', '17', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05J-415-C8\nCom style number - BSFJA05J-415\nColor - ODYSSEY GREY\nSize - 4\nUpc/ean (gtin) - 198740903658\nLabel id - DIL', 134.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(20, '3', '18', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05J-415-C8\nCom style number - BSFJA05J-415\nColor - ODYSSEY GREY\nSize - 5\nUpc/ean (gtin) - 198740903665\nLabel id - DIL', 248.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(21, '3', '19', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05J-415-C8\nCom style number - BSFJA05J-415\nColor - ODYSSEY GREY\nSize - 6\nUpc/ean (gtin) - 198740903672\nLabel id - DIL', 248.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(22, '3', '20', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05J-415-C8\nCom style number - BSFJA05J-415\nColor - ODYSSEY GREY\nSize - 7\nUpc/ean (gtin) - 198740903689\nLabel id - DIL', 134.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(23, '3', '21', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06J-436-C8\nCom style number - BSFJA06J-436\nColor - TRUE BLUE HEATHER\nSize - 2T\nUpc/ean (gtin) - 198740903573\nLabel id - DIL', 146.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(24, '3', '22', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06J-436-C8\nCom style number - BSFJA06J-436\nColor - TRUE BLUE HEATHER\nSize - 3T\nUpc/ean (gtin) - 198740903580\nLabel id - DIL', 146.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(25, '3', '23', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06J-436-C8\nCom style number - BSFJA06J-436\nColor - TRUE BLUE HEATHER\nSize - 4\nUpc/ean (gtin) - 198740903597\nLabel id - DIL', 146.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(26, '3', '24', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06J-436-C8\nCom style number - BSFJA06J-436\nColor - TRUE BLUE HEATHER\nSize - 5\nUpc/ean (gtin) - 198740903603\nLabel id - DIL', 272.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(27, '3', '25', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06J-436-C8\nCom style number - BSFJA06J-436\nColor - TRUE BLUE HEATHER\nSize - 6\nUpc/ean (gtin) - 198740903610\nLabel id - DIL', 272.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(28, '3', '26', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06J-436-C8\nCom style number - BSFJA06J-436\nColor - TRUE BLUE HEATHER\nSize - 7\nUpc/ean (gtin) - 198740903627\nLabel id - DIL', 146.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(29, '3', '27', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA07B\nMaterial number # - 25BSFJA07J-103-C8\nCom style number - BSFJA07J-103\nColor - OATMEAL\nSize - 2T\nUpc/ean (gtin) - 198740903511\nLabel id - DIL', 125.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(30, '3', '28', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA07B\nMaterial number # - 25BSFJA07J-103-C8\nCom style number - BSFJA07J-103\nColor - OATMEAL\nSize - 3T\nUpc/ean (gtin) - 198740903528\nLabel id - DIL', 125.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(31, '3', '29', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA07B\nMaterial number # - 25BSFJA07J-103-C8\nCom style number - BSFJA07J-103\nColor - OATMEAL\nSize - 4\nUpc/ean (gtin) - 198740903535\nLabel id - DIL', 125.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(32, '3', '30', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA07B\nMaterial number # - 25BSFJA07J-103-C8\nCom style number - BSFJA07J-103\nColor - OATMEAL\nSize - 5\nUpc/ean (gtin) - 198740903542\nLabel id - DIL', 230.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(33, '3', '31', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA07B\nMaterial number # - 25BSFJA07J-103-C8\nCom style number - BSFJA07J-103\nColor - OATMEAL\nSize - 6\nUpc/ean (gtin) - 198740903559\nLabel id - DIL', 230.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(34, '3', '32', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA07B\nMaterial number # - 25BSFJA07J-103-C8\nCom style number - BSFJA07J-103\nColor - OATMEAL\nSize - 7\nUpc/ean (gtin) - 198740903566\nLabel id - DIL', 125.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(35, '3', '33', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08J-440-C8\nCom style number - BSFJA08J-440\nColor - BLUE GRASS HEATHER\nSize - 2T\nUpc/ean (gtin) - 198740903252\nLabel id - DIL', 113.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(36, '3', '34', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08J-440-C8\nCom style number - BSFJA08J-440\nColor - BLUE GRASS HEATHER\nSize - 3T\nUpc/ean (gtin) - 198740903269\nLabel id - DIL', 113.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(37, '3', '35', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08J-440-C8\nCom style number - BSFJA08J-440\nColor - BLUE GRASS HEATHER\nSize - 4\nUpc/ean (gtin) - 198740903276\nLabel id - DIL', 113.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(38, '3', '36', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08J-440-C8\nCom style number - BSFJA08J-440\nColor - BLUE GRASS HEATHER\nSize - 5\nUpc/ean (gtin) - 198740903283\nLabel id - DIL', 206.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(39, '3', '37', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08J-440-C8\nCom style number - BSFJA08J-440\nColor - BLUE GRASS HEATHER\nSize - 6\nUpc/ean (gtin) - 198740903290\nLabel id - DIL', 206.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(40, '3', '38', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08J-440-C8\nCom style number - BSFJA08J-440\nColor - BLUE GRASS HEATHER\nSize - 7\nUpc/ean (gtin) - 198740903306\nLabel id - DIL', 113.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(41, '3', '39', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA10B\nMaterial number # - 25BSFJA10J-431-C8\nCom style number - BSFJA10J-431\nColor - BLUE RADIANCE\nSize - 2T\nUpc/ean (gtin) - 198740903696\nLabel id - DIL', 116.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(42, '3', '40', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA10B\nMaterial number # - 25BSFJA10J-431-C8\nCom style number - BSFJA10J-431\nColor - BLUE RADIANCE\nSize - 3T\nUpc/ean (gtin) - 198740903702\nLabel id - DIL', 116.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(43, '3', '41', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA10B\nMaterial number # - 25BSFJA10J-431-C8\nCom style number - BSFJA10J-431\nColor - BLUE RADIANCE\nSize - 4\nUpc/ean (gtin) - 198740903719\nLabel id - DIL', 116.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(44, '3', '42', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA10B\nMaterial number # - 25BSFJA10J-431-C8\nCom style number - BSFJA10J-431\nColor - BLUE RADIANCE\nSize - 5\nUpc/ean (gtin) - 198740903726\nLabel id - DIL', 212.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(45, '3', '43', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA10B\nMaterial number # - 25BSFJA10J-431-C8\nCom style number - BSFJA10J-431\nColor - BLUE RADIANCE\nSize - 6\nUpc/ean (gtin) - 198740903733\nLabel id - DIL', 212.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(46, '3', '44', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA10B\nMaterial number # - 25BSFJA10J-431-C8\nCom style number - BSFJA10J-431\nColor - BLUE RADIANCE\nSize - 7\nUpc/ean (gtin) - 198740903740\nLabel id - DIL', 116.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(47, '3', '45', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA12B\nMaterial number # - 25BSFJA12J-832-C8\nCom style number - BSFJA12J-832\nColor - PEACH\nSize - 2T\nUpc/ean (gtin) - 198740903818\nLabel id - DIL', 89.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(48, '3', '46', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA12B\nMaterial number # - 25BSFJA12J-832-C8\nCom style number - BSFJA12J-832\nColor - PEACH\nSize - 3T\nUpc/ean (gtin) - 198740903825\nLabel id - DIL', 89.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(49, '3', '47', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA12B\nMaterial number # - 25BSFJA12J-832-C8\nCom style number - BSFJA12J-832\nColor - PEACH\nSize - 4\nUpc/ean (gtin) - 198740903832\nLabel id - DIL', 89.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(50, '3', '48', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA12B\nMaterial number # - 25BSFJA12J-832-C8\nCom style number - BSFJA12J-832\nColor - PEACH\nSize - 5\nUpc/ean (gtin) - 198740903849\nLabel id - DIL', 158.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(51, '3', '49', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA12B\nMaterial number # - 25BSFJA12J-832-C8\nCom style number - BSFJA12J-832\nColor - PEACH\nSize - 6\nUpc/ean (gtin) - 198740903856\nLabel id - DIL', 158.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(52, '3', '50', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA12B\nMaterial number # - 25BSFJA12J-832-C8\nCom style number - BSFJA12J-832\nColor - PEACH\nSize - 7\nUpc/ean (gtin) - 198740903863\nLabel id - DIL', 89.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(53, '3', '51', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13J-421-C8\nCom style number - BSFJA13J-421\nColor - CHINA BLUE\nSize - 2T\nUpc/ean (gtin) - 198740903757\nLabel id - DIL', 146.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(54, '3', '52', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13J-421-C8\nCom style number - BSFJA13J-421\nColor - CHINA BLUE\nSize - 3T\nUpc/ean (gtin) - 198740903764\nLabel id - DIL', 146.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(55, '3', '53', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13J-421-C8\nCom style number - BSFJA13J-421\nColor - CHINA BLUE\nSize - 4\nUpc/ean (gtin) - 198740903771\nLabel id - DIL', 146.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(56, '3', '54', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13J-421-C8\nCom style number - BSFJA13J-421\nColor - CHINA BLUE\nSize - 5\nUpc/ean (gtin) - 198740903788\nLabel id - DIL', 272.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(57, '3', '55', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13J-421-C8\nCom style number - BSFJA13J-421\nColor - CHINA BLUE\nSize - 6\nUpc/ean (gtin) - 198740903795\nLabel id - DIL', 272.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(58, '3', '56', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13J-421-C8\nCom style number - BSFJA13J-421\nColor - CHINA BLUE\nSize - 7\nUpc/ean (gtin) - 198740903801\nLabel id - DIL', 146.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(59, '3', '57', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16J-002-C8\nCom style number - BSFJA16J-002\nColor - BLACK HEATHER\nSize - 2T\nUpc/ean (gtin) - 198740903870\nLabel id - DIL', 146.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(60, '3', '58', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16J-002-C8\nCom style number - BSFJA16J-002\nColor - BLACK HEATHER\nSize - 3T\nUpc/ean (gtin) - 198740903887\nLabel id - DIL', 146.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(61, '3', '59', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16J-002-C8\nCom style number - BSFJA16J-002\nColor - BLACK HEATHER\nSize - 4\nUpc/ean (gtin) - 198740903894\nLabel id - DIL', 146.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(62, '3', '60', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16J-002-C8\nCom style number - BSFJA16J-002\nColor - BLACK HEATHER\nSize - 5\nUpc/ean (gtin) - 198740903900\nLabel id - DIL', 272.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(63, '3', '61', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16J-002-C8\nCom style number - BSFJA16J-002\nColor - BLACK HEATHER\nSize - 6\nUpc/ean (gtin) - 198740903917\nLabel id - DIL', 272.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(64, '3', '62', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16J-002-C8\nCom style number - BSFJA16J-002\nColor - BLACK HEATHER\nSize - 7\nUpc/ean (gtin) - 198740903924\nLabel id - DIL', 146.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(65, '3', '63', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17J-030-C8\nCom style number - BSFJA17J-030\nColor - GREY HEATHER\nSize - 2T\nUpc/ean (gtin) - 198740903993\nLabel id - DIL', 185.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(66, '3', '64', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17J-030-C8\nCom style number - BSFJA17J-030\nColor - GREY HEATHER\nSize - 3T\nUpc/ean (gtin) - 198740904006\nLabel id - DIL', 185.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(67, '3', '65', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17J-030-C8\nCom style number - BSFJA17J-030\nColor - GREY HEATHER\nSize - 4\nUpc/ean (gtin) - 198740904013\nLabel id - DIL', 185.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(68, '3', '66', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17J-030-C8\nCom style number - BSFJA17J-030\nColor - GREY HEATHER\nSize - 5\nUpc/ean (gtin) - 198740904020\nLabel id - DIL', 350.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(69, '3', '67', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17J-030-C8\nCom style number - BSFJA17J-030\nColor - GREY HEATHER\nSize - 6\nUpc/ean (gtin) - 198740904037\nLabel id - DIL', 350.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(70, '3', '68', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17J-030-C8\nCom style number - BSFJA17J-030\nColor - GREY HEATHER\nSize - 7\nUpc/ean (gtin) - 198740904044\nLabel id - DIL', 185.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(71, '3', '69', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18J-001-C8\nCom style number - BSFJA18J-001\nColor - BLACK\nSize - 2T\nUpc/ean (gtin) - 198740903931\nLabel id - DIL', 86.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(72, '3', '70', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18J-001-C8\nCom style number - BSFJA18J-001\nColor - BLACK\nSize - 3T\nUpc/ean (gtin) - 198740903948\nLabel id - DIL', 86.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(73, '3', '71', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18J-001-C8\nCom style number - BSFJA18J-001\nColor - BLACK\nSize - 4\nUpc/ean (gtin) - 198740903955\nLabel id - DIL', 86.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(74, '3', '72', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18J-001-C8\nCom style number - BSFJA18J-001\nColor - BLACK\nSize - 5\nUpc/ean (gtin) - 198740903962\nLabel id - DIL', 152.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(75, '3', '73', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18J-001-C8\nCom style number - BSFJA18J-001\nColor - BLACK\nSize - 6\nUpc/ean (gtin) - 198740903979\nLabel id - DIL', 152.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(76, '3', '74', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18J-001-C8\nCom style number - BSFJA18J-001\nColor - BLACK\nSize - 7\nUpc/ean (gtin) - 198740903986\nLabel id - DIL', 86.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(77, '3', '75', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22J-100-C8\nCom style number - BSFJA22J-100\nColor - WHITE\nSize - 2T\nUpc/ean (gtin) - 198740904051\nLabel id - DIL', 128.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(78, '3', '76', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22J-100-C8\nCom style number - BSFJA22J-100\nColor - WHITE\nSize - 3T\nUpc/ean (gtin) - 198740904068\nLabel id - DIL', 128.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(79, '3', '77', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22J-100-C8\nCom style number - BSFJA22J-100\nColor - WHITE\nSize - 4\nUpc/ean (gtin) - 198740904075\nLabel id - DIL', 128.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(80, '3', '78', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22J-100-C8\nCom style number - BSFJA22J-100\nColor - WHITE\nSize - 5\nUpc/ean (gtin) - 198740904082\nLabel id - DIL', 236.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(81, '3', '79', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22J-100-C8\nCom style number - BSFJA22J-100\nColor - WHITE\nSize - 6\nUpc/ean (gtin) - 198740904099\nLabel id - DIL', 236.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(82, '3', '80', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22J-100-C8\nCom style number - BSFJA22J-100\nColor - WHITE\nSize - 7\nUpc/ean (gtin) - 198740904105\nLabel id - DIL', 128.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(83, '3', '81', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA00B\nMaterial number # - 25BSFJA00F-311-PC\nCom style number - BSFJA00F-311\nColor - LAUREL WREATH\nSize - XL18/20\nUpc/ean (gtin) - 198740882847\nLabel id - DIL', 140.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(84, '3', '82', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA00B\nMaterial number # - 25BSFJA00F-311-PC\nCom style number - BSFJA00F-311\nColor - LAUREL WREATH\nSize - L14/16\nUpc/ean (gtin) - 198740882816\nLabel id - DIL', 260.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(85, '3', '83', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA00B\nMaterial number # - 25BSFJA00F-311-PC\nCom style number - BSFJA00F-311\nColor - LAUREL WREATH\nSize - M10/12\nUpc/ean (gtin) - 198740882823\nLabel id - DIL', 260.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(86, '3', '84', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA00B\nMaterial number # - 25BSFJA00F-311-PC\nCom style number - BSFJA00F-311\nColor - LAUREL WREATH\nSize - S8\nUpc/ean (gtin) - 198740882830\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(87, '3', '85', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01F-101-PC\nCom style number - BSFJA01F-101\nColor - OFF WHITE\nSize - XL18/20\nUpc/ean (gtin) - 198740882922\nLabel id - DIL', 92.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(88, '3', '86', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01F-101-PC\nCom style number - BSFJA01F-101\nColor - OFF WHITE\nSize - L14/16\nUpc/ean (gtin) - 198740882892\nLabel id - DIL', 140.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(89, '3', '87', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01F-101-PC\nCom style number - BSFJA01F-101\nColor - OFF WHITE\nSize - S8\nUpc/ean (gtin) - 198740882915\nLabel id - DIL', 92.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(90, '3', '88', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01F-101-PC\nCom style number - BSFJA01F-101\nColor - OFF WHITE\nSize - M10/12\nUpc/ean (gtin) - 198740882908\nLabel id - DIL', 140.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(91, '3', '89', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA02B\nMaterial number # - 25BSFJA02F-030-PC\nCom style number - BSFJA02F-030\nColor - GREY HEATHER\nSize - S8\nUpc/ean (gtin) - 198740882878\nLabel id - DIL', 116.00, 0.00, '', '2026-03-28 04:42:27', '2026-03-28 04:42:27', 'active', 1, 1, 1, 1),
(92, '3', '90', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA02B\nMaterial number # - 25BSFJA02F-030-PC\nCom style number - BSFJA02F-030\nColor - GREY HEATHER\nSize - M10/12\nUpc/ean (gtin) - 198740882861\nLabel id - DIL', 212.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(93, '3', '91', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA02B\nMaterial number # - 25BSFJA02F-030-PC\nCom style number - BSFJA02F-030\nColor - GREY HEATHER\nSize - XL18/20\nUpc/ean (gtin) - 198740882885\nLabel id - DIL', 116.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(94, '3', '92', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA02B\nMaterial number # - 25BSFJA02F-030-PC\nCom style number - BSFJA02F-030\nColor - GREY HEATHER\nSize - L14/16\nUpc/ean (gtin) - 198740882854\nLabel id - DIL', 212.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(95, '3', '93', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03F-401-PC\nCom style number - BSFJA03F-401\nColor - DARK DENIM HEATHER\nSize - S8\nUpc/ean (gtin) - 198740882953\nLabel id - DIL', 116.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(96, '3', '94', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03F-401-PC\nCom style number - BSFJA03F-401\nColor - DARK DENIM HEATHER\nSize - M10/12\nUpc/ean (gtin) - 198740882946\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(97, '3', '95', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03F-401-PC\nCom style number - BSFJA03F-401\nColor - DARK DENIM HEATHER\nSize - L14/16\nUpc/ean (gtin) - 198740882939\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(98, '3', '96', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03F-401-PC\nCom style number - BSFJA03F-401\nColor - DARK DENIM HEATHER\nSize - XL18/20\nUpc/ean (gtin) - 198740882960\nLabel id - DIL', 92.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(99, '3', '97', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA04B\nMaterial number # - 25BSFJA04F-278-PC\nCom style number - BSFJA04F-278\nColor - PALE KHAKI\nSize - XL18/20\nUpc/ean (gtin) - 198740883004\nLabel id - DIL', 116.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(100, '3', '98', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA04B\nMaterial number # - 25BSFJA04F-278-PC\nCom style number - BSFJA04F-278\nColor - PALE KHAKI\nSize - L14/16\nUpc/ean (gtin) - 198740882977\nLabel id - DIL', 188.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(101, '3', '99', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA04B\nMaterial number # - 25BSFJA04F-278-PC\nCom style number - BSFJA04F-278\nColor - PALE KHAKI\nSize - S8\nUpc/ean (gtin) - 198740882991\nLabel id - DIL', 116.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(102, '3', '100', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA04B\nMaterial number # - 25BSFJA04F-278-PC\nCom style number - BSFJA04F-278\nColor - PALE KHAKI\nSize - M10/12\nUpc/ean (gtin) - 198740882984\nLabel id - DIL', 188.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(103, '3', '101', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05F-415-PC\nCom style number - BSFJA05F-415\nColor - ODYSSEY GREY\nSize - XL18/20\nUpc/ean (gtin) - 198740883042\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(104, '3', '102', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05F-415-PC\nCom style number - BSFJA05F-415\nColor - ODYSSEY GREY\nSize - L14/16\nUpc/ean (gtin) - 198740883035\nLabel id - DIL', 284.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(105, '3', '103', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05F-415-PC\nCom style number - BSFJA05F-415\nColor - ODYSSEY GREY\nSize - M10/12\nUpc/ean (gtin) - 198740883011\nLabel id - DIL', 284.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(106, '3', '104', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05F-415-PC\nCom style number - BSFJA05F-415\nColor - ODYSSEY GREY\nSize - S8\nUpc/ean (gtin) - 198740883028\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(107, '3', '105', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06F-436-PC\nCom style number - BSFJA06F-436\nColor - TRUE BLUE HEATHER\nSize - S8\nUpc/ean (gtin) - 198740883158\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(108, '3', '106', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06F-436-PC\nCom style number - BSFJA06F-436\nColor - TRUE BLUE HEATHER\nSize - M10/12\nUpc/ean (gtin) - 198740883141\nLabel id - DIL', 236.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(109, '3', '107', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06F-436-PC\nCom style number - BSFJA06F-436\nColor - TRUE BLUE HEATHER\nSize - L14/16\nUpc/ean (gtin) - 198740883134\nLabel id - DIL', 236.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(110, '3', '108', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06F-436-PC\nCom style number - BSFJA06F-436\nColor - TRUE BLUE HEATHER\nSize - XL18/20\nUpc/ean (gtin) - 198740883165\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(111, '3', '109', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08F-440-PC\nCom style number - BSFJA08F-440\nColor - BLUE GRASS HEATHER\nSize - XL18/20\nUpc/ean (gtin) - 198740883080\nLabel id - DIL', 116.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(112, '3', '110', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08F-440-PC\nCom style number - BSFJA08F-440\nColor - BLUE GRASS HEATHER\nSize - L14/16\nUpc/ean (gtin) - 198740883059\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(113, '3', '111', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08F-440-PC\nCom style number - BSFJA08F-440\nColor - BLUE GRASS HEATHER\nSize - M10/12\nUpc/ean (gtin) - 198740883066\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(114, '3', '112', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08F-440-PC\nCom style number - BSFJA08F-440\nColor - BLUE GRASS HEATHER\nSize - S8\nUpc/ean (gtin) - 198740883073\nLabel id - DIL', 116.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(115, '3', '113', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA09B\nMaterial number # - 25BSFJA09F-473-PC\nCom style number - BSFJA09F-473\nColor - TRUE NAVY\nSize - L14/16\nUpc/ean (gtin) - 198740883097\nLabel id - DIL', 284.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(116, '3', '114', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA09B\nMaterial number # - 25BSFJA09F-473-PC\nCom style number - BSFJA09F-473\nColor - TRUE NAVY\nSize - XL18/20\nUpc/ean (gtin) - 198740883127\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(117, '3', '115', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA09B\nMaterial number # - 25BSFJA09F-473-PC\nCom style number - BSFJA09F-473\nColor - TRUE NAVY\nSize - M10/12\nUpc/ean (gtin) - 198740883103\nLabel id - DIL', 284.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(118, '3', '116', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA09B\nMaterial number # - 25BSFJA09F-473-PC\nCom style number - BSFJA09F-473\nColor - TRUE NAVY\nSize - S8\nUpc/ean (gtin) - 198740883110\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(119, '3', '117', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA11B\nMaterial number # - 25BSFJA11F-415-PC\nCom style number - BSFJA11F-415\nColor - ODYSSEY GREY\nSize - S8\nUpc/ean (gtin) - 198740883271\nLabel id - DIL', 92.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(120, '3', '118', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA11B\nMaterial number # - 25BSFJA11F-415-PC\nCom style number - BSFJA11F-415\nColor - ODYSSEY GREY\nSize - M10/12\nUpc/ean (gtin) - 198740883264\nLabel id - DIL', 140.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(121, '3', '119', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA11B\nMaterial number # - 25BSFJA11F-415-PC\nCom style number - BSFJA11F-415\nColor - ODYSSEY GREY\nSize - L14/16\nUpc/ean (gtin) - 198740883257\nLabel id - DIL', 140.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(122, '3', '120', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA11B\nMaterial number # - 25BSFJA11F-415-PC\nCom style number - BSFJA11F-415\nColor - ODYSSEY GREY\nSize - XL18/20\nUpc/ean (gtin) - 198740883288\nLabel id - DIL', 92.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(123, '3', '121', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13F-421-PC\nCom style number - BSFJA13F-421\nColor - CHINA BLUE\nSize - XL18/20\nUpc/ean (gtin) - 198740883363\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(124, '3', '122', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13F-421-PC\nCom style number - BSFJA13F-421\nColor - CHINA BLUE\nSize - L14/16\nUpc/ean (gtin) - 198740883332\nLabel id - DIL', 284.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(125, '3', '123', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13F-421-PC\nCom style number - BSFJA13F-421\nColor - CHINA BLUE\nSize - M10/12\nUpc/ean (gtin) - 198740883349\nLabel id - DIL', 284.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(126, '3', '124', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13F-421-PC\nCom style number - BSFJA13F-421\nColor - CHINA BLUE\nSize - S8\nUpc/ean (gtin) - 198740883356\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(127, '3', '125', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA15B\nMaterial number # - 25BSFJA15F-450-PC\nCom style number - BSFJA15F-450\nColor - BLUE SURF\nSize - XL18/20\nUpc/ean (gtin) - 198740883448\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(128, '3', '126', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA15B\nMaterial number # - 25BSFJA15F-450-PC\nCom style number - BSFJA15F-450\nColor - BLUE SURF\nSize - L14/16\nUpc/ean (gtin) - 198740883417\nLabel id - DIL', 284.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(129, '3', '127', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA15B\nMaterial number # - 25BSFJA15F-450-PC\nCom style number - BSFJA15F-450\nColor - BLUE SURF\nSize - M10/12\nUpc/ean (gtin) - 198740883424\nLabel id - DIL', 284.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(130, '3', '128', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA15B\nMaterial number # - 25BSFJA15F-450-PC\nCom style number - BSFJA15F-450\nColor - BLUE SURF\nSize - S8\nUpc/ean (gtin) - 198740883431\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(131, '3', '129', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16F-002-PC\nCom style number - BSFJA16F-002\nColor - BLACK HEATHER\nSize - XL18/20\nUpc/ean (gtin) - 198740883486\nLabel id - DIL', 92.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(132, '3', '130', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16F-002-PC\nCom style number - BSFJA16F-002\nColor - BLACK HEATHER\nSize - L14/16\nUpc/ean (gtin) - 198740883455\nLabel id - DIL', 140.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(133, '3', '131', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16F-002-PC\nCom style number - BSFJA16F-002\nColor - BLACK HEATHER\nSize - M10/12\nUpc/ean (gtin) - 198740883462\nLabel id - DIL', 140.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(134, '3', '132', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16F-002-PC\nCom style number - BSFJA16F-002\nColor - BLACK HEATHER\nSize - S8\nUpc/ean (gtin) - 198740883479\nLabel id - DIL', 92.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(135, '3', '133', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17F-030-PC\nCom style number - BSFJA17F-030\nColor - GREY HEATHER\nSize - S8\nUpc/ean (gtin) - 198740883516\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(136, '3', '134', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17F-030-PC\nCom style number - BSFJA17F-030\nColor - GREY HEATHER\nSize - M10/12\nUpc/ean (gtin) - 198740883509\nLabel id - DIL', 212.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(137, '3', '135', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17F-030-PC\nCom style number - BSFJA17F-030\nColor - GREY HEATHER\nSize - L14/16\nUpc/ean (gtin) - 198740883493\nLabel id - DIL', 236.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(138, '3', '136', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17F-030-PC\nCom style number - BSFJA17F-030\nColor - GREY HEATHER\nSize - XL18/20\nUpc/ean (gtin) - 198740883523\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(139, '3', '137', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18F-001-PC\nCom style number - BSFJA18F-001\nColor - BLACK\nSize - S8\nUpc/ean (gtin) - 198740883608\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(140, '3', '138', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18F-001-PC\nCom style number - BSFJA18F-001\nColor - BLACK\nSize - M10/12\nUpc/ean (gtin) - 198740883585\nLabel id - DIL', 284.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(141, '3', '139', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18F-001-PC\nCom style number - BSFJA18F-001\nColor - BLACK\nSize - L14/16\nUpc/ean (gtin) - 198740883578\nLabel id - DIL', 284.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(142, '3', '140', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18F-001-PC\nCom style number - BSFJA18F-001\nColor - BLACK\nSize - XL18/20\nUpc/ean (gtin) - 198740883592\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(143, '3', '141', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA19B\nMaterial number # - 25BSFJA19F-740-PC\nCom style number - BSFJA19F-740\nColor - RAFFIA\nSize - S8\nUpc/ean (gtin) - 198740883554\nLabel id - DIL', 92.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(144, '3', '142', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA19B\nMaterial number # - 25BSFJA19F-740-PC\nCom style number - BSFJA19F-740\nColor - RAFFIA\nSize - M10/12\nUpc/ean (gtin) - 198740883547\nLabel id - DIL', 140.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(145, '3', '143', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA19B\nMaterial number # - 25BSFJA19F-740-PC\nCom style number - BSFJA19F-740\nColor - RAFFIA\nSize - L14/16\nUpc/ean (gtin) - 198740883530\nLabel id - DIL', 140.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(146, '3', '144', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA19B\nMaterial number # - 25BSFJA19F-740-PC\nCom style number - BSFJA19F-740\nColor - RAFFIA\nSize - XL18/20\nUpc/ean (gtin) - 198740883561\nLabel id - DIL', 92.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(147, '3', '145', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA20B\nMaterial number # - 25BSFJA20F-512-PC\nCom style number - BSFJA20F-512\nColor - PURPLE ASH\nSize - M10/12\nUpc/ean (gtin) - 198740883622\nLabel id - DIL', 212.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(148, '3', '146', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA20B\nMaterial number # - 25BSFJA20F-512-PC\nCom style number - BSFJA20F-512\nColor - PURPLE ASH\nSize - S8\nUpc/ean (gtin) - 198740883639\nLabel id - DIL', 140.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(149, '3', '147', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA20B\nMaterial number # - 25BSFJA20F-512-PC\nCom style number - BSFJA20F-512\nColor - PURPLE ASH\nSize - L14/16\nUpc/ean (gtin) - 198740883615\nLabel id - DIL', 212.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(150, '3', '148', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA20B\nMaterial number # - 25BSFJA20F-512-PC\nCom style number - BSFJA20F-512\nColor - PURPLE ASH\nSize - XL18/20\nUpc/ean (gtin) - 198740883646\nLabel id - DIL', 140.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(151, '3', '149', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA21B\nMaterial number # - 25BSFJA21F-001-PC\nCom style number - BSFJA21F-001\nColor - BLACK\nSize - S8\nUpc/ean (gtin) - 198740883677\nLabel id - DIL', 212.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(152, '3', '150', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA21B\nMaterial number # - 25BSFJA21F-001-PC\nCom style number - BSFJA21F-001\nColor - BLACK\nSize - M10/12\nUpc/ean (gtin) - 198740883660\nLabel id - DIL', 236.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(153, '3', '151', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA21B\nMaterial number # - 25BSFJA21F-001-PC\nCom style number - BSFJA21F-001\nColor - BLACK\nSize - L14/16\nUpc/ean (gtin) - 198740883653\nLabel id - DIL', 236.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(154, '3', '152', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA21B\nMaterial number # - 25BSFJA21F-001-PC\nCom style number - BSFJA21F-001\nColor - BLACK\nSize - XL18/20\nUpc/ean (gtin) - 198740883684\nLabel id - DIL', 188.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(155, '3', '153', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22F-100-PC\nCom style number - BSFJA22F-100\nColor - WHITE\nSize - XL18/20\nUpc/ean (gtin) - 198740883769\nLabel id - DIL', 116.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(156, '3', '154', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22F-100-PC\nCom style number - BSFJA22F-100\nColor - WHITE\nSize - L14/16\nUpc/ean (gtin) - 198740883738\nLabel id - DIL', 188.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1);
INSERT INTO `challan_details` (`id`, `challan_id`, `workorder_detail_id`, `item_id`, `description`, `qty`, `receive_qty`, `remark`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(157, '3', '155', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22F-100-PC\nCom style number - BSFJA22F-100\nColor - WHITE\nSize - M10/12\nUpc/ean (gtin) - 198740883745\nLabel id - DIL', 188.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(158, '3', '156', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22F-100-PC\nCom style number - BSFJA22F-100\nColor - WHITE\nSize - S8\nUpc/ean (gtin) - 198740883752\nLabel id - DIL', 116.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(159, '3', '157', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA23B\nMaterial number # - 25BSFJA23F-441-PC\nCom style number - BSFJA23F-441\nColor - JADE GREY\nSize - XL18/20\nUpc/ean (gtin) - 198740883721\nLabel id - DIL', 92.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(160, '3', '158', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA23B\nMaterial number # - 25BSFJA23F-441-PC\nCom style number - BSFJA23F-441\nColor - JADE GREY\nSize - L14/16\nUpc/ean (gtin) - 198740883691\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(161, '3', '159', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA23B\nMaterial number # - 25BSFJA23F-441-PC\nCom style number - BSFJA23F-441\nColor - JADE GREY\nSize - M10/12\nUpc/ean (gtin) - 198740883707\nLabel id - DIL', 188.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(162, '3', '160', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA23B\nMaterial number # - 25BSFJA23F-441-PC\nCom style number - BSFJA23F-441\nColor - JADE GREY\nSize - S8\nUpc/ean (gtin) - 198740883714\nLabel id - DIL', 116.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(163, '3', '161', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA24B\nMaterial number # - 25BSFJA24F-270-PC\nCom style number - BSFJA24F-270\nColor - SAND KHAKI\nSize - XL18/20\nUpc/ean (gtin) - 198740883844\nLabel id - DIL', 92.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(164, '3', '162', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA24B\nMaterial number # - 25BSFJA24F-270-PC\nCom style number - BSFJA24F-270\nColor - SAND KHAKI\nSize - L14/16\nUpc/ean (gtin) - 198740883813\nLabel id - DIL', 140.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(165, '3', '163', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA24B\nMaterial number # - 25BSFJA24F-270-PC\nCom style number - BSFJA24F-270\nColor - SAND KHAKI\nSize - M10/12\nUpc/ean (gtin) - 198740883820\nLabel id - DIL', 140.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(166, '3', '164', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA24B\nMaterial number # - 25BSFJA24F-270-PC\nCom style number - BSFJA24F-270\nColor - SAND KHAKI\nSize - S8\nUpc/ean (gtin) - 198740883837\nLabel id - DIL', 92.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(167, '3', '165', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJB20B\nMaterial number # - 25BSFJB20F-001-PC\nCom style number - BSFJB20F-001\nColor - BLACK\nSize - S8\nUpc/ean (gtin) - 198740882793\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:28', '2026-03-28 04:42:28', 'active', 1, 1, 1, 1),
(168, '3', '166', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJB20B\nMaterial number # - 25BSFJB20F-001-PC\nCom style number - BSFJB20F-001\nColor - BLACK\nSize - M10/12\nUpc/ean (gtin) - 198740882786\nLabel id - DIL', 260.00, 0.00, '', '2026-03-28 04:42:29', '2026-03-28 04:42:29', 'active', 1, 1, 1, 1),
(169, '3', '167', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJB20B\nMaterial number # - 25BSFJB20F-001-PC\nCom style number - BSFJB20F-001\nColor - BLACK\nSize - L14/16\nUpc/ean (gtin) - 198740882779\nLabel id - DIL', 260.00, 0.00, '', '2026-03-28 04:42:29', '2026-03-28 04:42:29', 'active', 1, 1, 1, 1),
(170, '3', '168', NULL, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJB20B\nMaterial number # - 25BSFJB20F-001-PC\nCom style number - BSFJB20F-001\nColor - BLACK\nSize - XL18/20\nUpc/ean (gtin) - 198740882809\nLabel id - DIL', 164.00, 0.00, '', '2026-03-28 04:42:29', '2026-03-28 04:42:29', 'active', 1, 1, 1, 1),
(171, '4', '502', 1, 'RJ45 connector for cable', 250.00, 0.00, '', '2026-07-13 11:03:45', '2026-07-13 12:17:13', 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint UNSIGNED NOT NULL,
  `clientid` int NOT NULL,
  `reg_date` date NOT NULL,
  `name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district_id` int DEFAULT NULL,
  `area_id` int DEFAULT NULL,
  `nid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `previous_due` decimal(12,2) DEFAULT NULL,
  `account_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_id` int DEFAULT NULL,
  `branch` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `clientid`, `reg_date`, `name`, `org_name`, `mobile`, `email`, `address`, `district_id`, `area_id`, `nid`, `note`, `previous_due`, `account_name`, `account_no`, `bank_id`, `branch`, `vat`, `created_at`, `updated_at`, `deleted_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 111, '2026-03-07', 'Oliver Tyler', 'SHANTA EXPRESSION LTD', '01436642787', 'doduxez@mailinator.com', 'Rerum fugiat quod ve', 13, 1, '27746', NULL, NULL, 'Ebony French', '48356', NULL, 'Culpa in quia rerum', 0, '2026-03-06 23:19:37', '2026-04-27 16:45:28', NULL, 'active', 1, 1, 1, 1),
(2, 112, '2026-03-30', 'Vielka Berger', 'Mcconnell Walls Traders', '01166554896', 'dipuhyn@mailinator.com', 'Mollitia do voluptat', 13, 2, '13492', 'Aut et laboris in ve', NULL, 'Dolan Finch', '52419', 1, 'Quidem quaerat inven', 0, '2026-03-30 01:14:24', '2026-04-22 03:01:52', '2026-04-22 03:01:52', 'active', 1, 1, 1, 1),
(3, 111, '2026-04-22', 'Jonas Woods', 'Macias and Harper Co', '01229759873', 'tobufyxy@mailinator.com', 'Culpa id eum quia t', NULL, NULL, '58377', 'Deserunt veritatis t', NULL, 'Tyler England', '6182', NULL, 'Ducimus ratione dol', 0, '2026-04-22 03:09:09', '2026-04-22 03:09:09', NULL, 'active', 1, 1, 1, 1),
(4, 112, '2026-04-22', 'Inga Key', 'Durham Kennedy Plc', '01817522661', 'lituseboky@mailinator.com', 'Qui natus earum magn', NULL, NULL, '69769', 'Ut nihil sint numqua', NULL, 'Patricia Manning', '915', NULL, 'Et consequat Laudan', 0, '2026-04-22 03:20:52', '2026-04-22 03:20:52', NULL, 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `colors`
--

CREATE TABLE `colors` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(225) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commissions`
--

CREATE TABLE `commissions` (
  `id` bigint UNSIGNED NOT NULL,
  `is_employee` tinyint DEFAULT NULL,
  `employee_id` int DEFAULT NULL,
  `agent_id` bigint UNSIGNED DEFAULT NULL,
  `workorder_id` int DEFAULT NULL,
  `client_id` int DEFAULT NULL,
  `reference_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_mobile` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `reference_nid` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `percentage` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(8,2) NOT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_closed` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `approved_by` int DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `commissions`
--

INSERT INTO `commissions` (`id`, `is_employee`, `employee_id`, `agent_id`, `workorder_id`, `client_id`, `reference_name`, `reference_mobile`, `reference_address`, `reference_nid`, `percentage`, `amount`, `remarks`, `is_closed`, `created_at`, `updated_at`, `deleted_at`, `status`, `approved_by`, `approved_date`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, 0, '2026-03-07 05:19:37', '2026-04-28 01:52:42', '2026-04-28 07:52:42', 'active', NULL, NULL, 1, 1, 1, 1),
(2, 0, NULL, 1, 3, 1, 'Maxwell Hebert', '95278961574', 'Quisquam sint et fu', NULL, '0', 500.00, NULL, 0, '2026-08-11 13:38:21', '2026-08-11 13:54:09', NULL, 'active', 28, '2026-08-11', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sorting` int NOT NULL DEFAULT '0',
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contents`
--

CREATE TABLE `contents` (
  `id` bigint UNSIGNED NOT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_meta` tinyint(1) NOT NULL DEFAULT '0',
  `meta_tag` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `meta_description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sorting` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contents`
--

INSERT INTO `contents` (`id`, `slug`, `title`, `url`, `description`, `image`, `status`, `is_meta`, `meta_tag`, `meta_description`, `sorting`, `created_at`, `updated_at`) VALUES
(33, 'mollitia-id-officia', 'Mollitia id officia', '', '<p>Mollitia id officia is a phrase that may evoke thoughts about duties and responsibilities related to kindness, care, or taking responsibility for one&#39;s actions. In various contexts, such a phrase could relate to the importance of compassion in professional settings, emphasizing the need for ethical practices and understanding in the workplace.</p>\r\n\r\n<p>In a business environment, it is crucial to foster a culture that prioritizes kindness and empathy among employees. This can lead to strengthened team dynamics, increased morale, and ultimately, enhanced productivity. When individuals feel valued and understood, they are more likely to contribute positively to the organization&#39;s goals.</p>\r\n\r\n<p>Moreover, in customer service, the concept of being mindful and considerate, as suggested by the phrase, highlights the necessity of treating customers with respect and attentiveness. It serves as a reminder that every interaction can significantly impact client satisfaction and loyalty.</p>\r\n\r\n<p>In personal life, embodying the principles behind &quot;mollitia id officia&quot; encourages us to approach relationships with openness and understanding. It invites us to consider the implications of our actions and to nurture bonds that promote mutual respect and support.</p>\r\n\r\n<p>Overall, whether in professional or personal spheres, the essence of this phrase can serve as a guiding principle for fostering a more compassionate and responsible world.</p>', '{\"600x600\":\"upload\\/content\\/600x600\\/e7551e58-34d1-49db-bd55-8c853bbb0d92.avif\",\"200x200\":\"upload\\/content\\/200x200\\/5cf58c69-801c-4049-bb95-bf426d524cd3.avif\",\"50x50\":\"upload\\/content\\/50x50\\/065593cd-214b-4b6b-b82f-d61651f811a7.avif\",\"original\":\"upload\\/content\\/original\\/54af1d8f-0f8c-43f3-ba2a-e6c34b5af9dc.avif\"}', 'active', 0, '[\"test\"]', 'For more information about our award-winning products and services, please visit our website at [insert website URL]', 2, '2024-10-08 05:07:00', '2025-03-03 07:08:07'),
(35, 'about-us', 'About us', NULL, '<p data-end=\"576\" data-start=\"153\"><strong data-end=\"195\" data-start=\"153\">অ্যাপার্টমেন্ট ম্যানেজমেন্ট সফটওয়্যার</strong>&nbsp;(BD RESIDENCE) হলো একটি স্মার্ট সমাধান, যা অ্যাপার্টমেন্ট বা ভবনের ভাড়া ব্যবস্থাপনা, ফান্ড ম্যানেজমেন্ট এবং বাসিন্দাদের তথ্য সংরক্ষণকে আরও সহজ ও সুশৃঙ্খল করে তোলে। এই সফটওয়্যার ব্যবহার করে আপনি স্বয়ংক্রিয়ভাবে ইনভয়েস তৈরি করতে পারবেন, পেমেন্ট রিসিভ হলে ভাড়াটিয়াদের কাছে এসএমএস নোটিফিকেশন পাঠাতে পারবেন এবং নির্ধারিত দিনে বকেয়া ভাড়াটিয়াদের কাছে স্বয়ংক্রিয়ভাবে রিমাইন্ডার এসএমএস পাঠানো যাবে।</p>\r\n\r\n<p data-end=\"843\" data-start=\"578\">এছাড়া প্রতিটি ফ্ল্যাটের জন্য ভাড়া ও সার্ভিস চার্জ সেটআপ করার সুবিধা রয়েছে। ইউটিলিটি বিল যেমন বিদ্যুৎ, গ্যাস, পানি ইত্যাদি সহজেই যোগ করা যাবে এবং চাইলে ইনভয়েসের সাথে যুক্ত করা যাবে। ভাড়াটিয়া এসএমএস সুবিধা না নিলে তারা ইনভয়েস ও মানি রিসিট প্রিন্ট আকারে নিতে পারবেন।</p>\r\n\r\n<p data-end=\"1096\" data-start=\"845\">ভবনের রক্ষণাবেক্ষণ খরচ ও সম্পূর্ণ হিসাব সিস্টেমে সংরক্ষিত থাকবে, ফলে যেকোনো সময় মেইনটেন্যান্স হিস্ট্রি চেক করা যাবে। এছাড়াও, সফটওয়্যারটি সব ধরনের আয়-ব্যয় ও রিপোর্ট এক ক্লিকেই তৈরি করতে পারে, যা ভবন মালিক বা ম্যানেজমেন্ট কমিটির জন্য অত্যন্ত সহায়ক।</p>', '{\"636x610\":\"upload\\/content\\/636x610\\/5eb81fd3-919d-448e-aea7-c0e81ad6c7b5.avif\",\"583x559\":\"upload\\/content\\/583x559\\/4a426a83-1c3e-4e53-992d-dfc537fb534c.avif\",\"200x192\":\"upload\\/content\\/200x192\\/e66c828f-9eec-4026-929e-d47bdc439f2f.avif\",\"original\":\"upload\\/content\\/original\\/799057e2-09f7-48dc-947c-74d2b18686a4.avif\"}', 'active', 0, '[]', NULL, 3, '2025-07-13 11:59:45', '2025-09-20 04:19:27'),
(36, 'terms-condition', 'Terms & Conditions', 'https://nogorsolutions.com/', '<p>Donec pretium ornare neque, ac luctus orci ultrices at. Sed blandit eleifend mi, a consectetur tortor varius in. Sed tincidunt aliquet lorem, eget tincidunt est molestie non. Sed tincidunt aliquet lorem, eget tincidunt est molestie nonac luctus orci ultrices at.</p>\r\n\r\n<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid, qui perferendis officia ex, reprehenderit quibusdam corrupti voluptas ut consequatur, ratione soluta! Vero quam sit rem tenetur dicta eligendi fugit quisquam quidem quaerat blanditiis quae temporibus, enim, nesciunt ut quasi facere consequuntur eos soluta eius placeat fugiat libero tempore itaque. Necessitatibus?</p>\r\n\r\n<ul>\r\n	<li>Pellentesque et felis elementum, varius tellus a, eleifend elit.</li>\r\n	<li>Sed ullamcorper enim ut maximus accumsan.</li>\r\n	<li>Cras tristique lacus at gravida vulputate.</li>\r\n	<li>Fusce consectetur purus ac aliquam aliquet.</li>\r\n	<li>Fusce consectetur purus ac aliquam aliquet.</li>\r\n</ul>\r\n\r\n<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid, qui perferendis officia ex, reprehenderit quibusdam corrupti voluptas ut consequatur, ratione soluta! Vero quam sit rem tenetur dicta eligendi fugit quisquam quidem quaerat blanditiis quae temporibus, enim, nesciunt ut quasi facere consequuntur eos soluta eius placeat fugiat libero tempore itaque. Necessitatibus?</p>\r\n\r\n<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid, qui perferendis officia ex, reprehenderit quibusdam corrupti voluptas ut consequatur, ratione soluta! Vero quam sit rem tenetur dicta eligendi fugit quisquam quidem quaerat blanditiis quae temporibus, enim, nesciunt ut quasi facere consequuntur eos soluta eius placeat fugiat libero tempore itaque. Necessitatibus?</p>\r\n\r\n<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid, qui perferendis officia ex, reprehenderit quibusdam corrupti voluptas ut consequatur, ratione soluta! Vero quam sit rem tenetur dicta eligendi fugit quisquam quidem quaerat blanditiis quae temporibus, enim, nesciunt ut quasi facere consequuntur eos soluta eius placeat fugiat libero tempore itaque. Necessitatibus?</p>\r\n\r\n<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid, qui perferendis officia ex, reprehenderit quibusdam corrupti voluptas ut consequatur, ratione soluta! Vero quam sit rem tenetur dicta eligendi fugit quisquam quidem quaerat blanditiis quae temporibus, enim, nesciunt ut quasi facere consequuntur eos soluta eius placeat fugiat libero tempore itaque. Necessitatibus?</p>', 'null', 'active', 0, '[]', NULL, 4, '2025-07-16 10:31:36', '2025-07-17 08:54:55'),
(37, 'privacy-policy', 'Privacy Policy', 'https://nogorsolutions.com/', '<p>Donec pretium ornare neque, ac luctus orci ultrices at. Sed blandit eleifend mi, a consectetur tortor varius in. Sed tincidunt aliquet lorem, eget tincidunt est molestie non. Sed tincidunt aliquet lorem, eget tincidunt est molestie nonac luctus orci ultrices at.</p>\r\n\r\n<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid, qui perferendis officia ex, reprehenderit quibusdam corrupti voluptas ut consequatur, ratione soluta! Vero quam sit rem tenetur dicta eligendi fugit quisquam quidem quaerat blanditiis quae temporibus, enim, nesciunt ut quasi facere consequuntur eos soluta eius placeat fugiat libero tempore itaque. Necessitatibus?</p>\r\n\r\n<ul>\r\n	<li>Pellentesque et felis elementum, varius tellus a, eleifend elit.</li>\r\n	<li>Sed ullamcorper enim ut maximus accumsan.</li>\r\n	<li>Cras tristique lacus at gravida vulputate.</li>\r\n	<li>Fusce consectetur purus ac aliquam aliquet.</li>\r\n	<li>Fusce consectetur purus ac aliquam aliquet.1</li>\r\n</ul>\r\n\r\n<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid, qui perferendis officia ex, reprehenderit quibusdam corrupti voluptas ut consequatur, ratione soluta! Vero quam sit rem tenetur dicta eligendi fugit quisquam quidem quaerat blanditiis quae temporibus, enim, nesciunt ut quasi facere consequuntur eos soluta eius placeat fugiat libero tempore itaque. Necessitatibus?</p>\r\n\r\n<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid, qui perferendis officia ex, reprehenderit quibusdam corrupti voluptas ut consequatur, ratione soluta! Vero quam sit rem tenetur dicta eligendi fugit quisquam quidem quaerat blanditiis quae temporibus, enim, nesciunt ut quasi facere consequuntur eos soluta eius placeat fugiat libero tempore itaque. Necessitatibus?</p>\r\n\r\n<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid, qui perferendis officia ex, reprehenderit quibusdam corrupti voluptas ut consequatur, ratione soluta! Vero quam sit rem tenetur dicta eligendi fugit quisquam quidem quaerat blanditiis quae temporibus, enim, nesciunt ut quasi facere consequuntur eos soluta eius placeat fugiat libero tempore itaque. Necessitatibus?</p>\r\n\r\n<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid, qui perferendis officia ex, reprehenderit quibusdam corrupti voluptas ut consequatur, ratione soluta! Vero quam sit rem tenetur dicta eligendi fugit quisquam quidem quaerat blanditiis quae temporibus, enim, nesciunt ut quasi facere consequuntur eos soluta eius placeat fugiat libero tempore itaque. Necessitatibus?</p>', 'null', 'active', 0, '[]', NULL, 5, '2025-07-16 10:32:06', '2025-07-17 08:55:22');

-- --------------------------------------------------------

--
-- Table structure for table `content_files`
--

CREATE TABLE `content_files` (
  `id` bigint UNSIGNED NOT NULL,
  `content_id` bigint UNSIGNED NOT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sorting` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `content_files`
--

INSERT INTO `content_files` (`id`, `content_id`, `title`, `file`, `sorting`, `created_at`, `updated_at`) VALUES
(12, 33, 'Distinctio Voluptat', 'upload/content-files/250115060125-5627Parking Area Management System.docx', NULL, '2024-10-08 05:07:01', '2025-01-15 12:01:25'),
(15, 33, 'kgkjg', 'upload/content-files/250115060125-5303Parking Area Management System.docx', NULL, '2025-01-15 12:01:25', '2025-01-15 12:01:25');

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`id`, `title`, `short_name`, `symbol`, `is_default`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 'Taka', 'BDT', 'Tk', 1, '2026-03-05 09:07:50', '2026-03-05 09:07:50', 'active', 1, 1, 1, 1),
(2, 'Dollar', 'USD', '$', 0, '2026-03-06 04:35:38', '2026-03-06 04:35:38', 'active', 1, 1, 1, 1),
(3, 'Euro', 'EUR', '€', 0, '2026-03-06 04:37:09', '2026-03-06 04:37:09', 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `designations`
--

CREATE TABLE `designations` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `basic` decimal(12,2) DEFAULT '0.00',
  `house_rent` decimal(12,2) DEFAULT '0.00',
  `medical` decimal(12,2) DEFAULT '0.00',
  `other` decimal(12,2) DEFAULT '0.00',
  `total_salary` decimal(12,2) DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `designations`
--

INSERT INTO `designations` (`id`, `title`, `basic`, `house_rent`, `medical`, `other`, `total_salary`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 'Manager', 10000.00, 5000.00, 3000.00, 2000.00, 20000.00, '2025-12-17 12:37:12', '2025-12-17 12:37:12', 'active', 1, 1, 1, 1),
(2, 'Accounts Officer', 8000.00, 4000.00, 2000.00, 2000.00, 16000.00, '2025-12-17 12:39:11', '2025-12-17 12:39:11', 'active', 1, 1, 1, 1),
(3, 'Executive Support & Services', 0.00, 0.00, 0.00, 0.00, NULL, '2026-01-26 19:03:59', '2026-01-26 19:03:59', 'active', 1, 1, 1, 1),
(4, 'Cable Technician', 0.00, 0.00, 0.00, 0.00, 0.00, '2026-01-26 19:04:58', '2026-01-26 19:04:58', 'active', 1, 1, 1, 1),
(5, 'Senior Cable Technician', 0.00, 0.00, 0.00, 0.00, 0.00, '2026-01-26 19:05:19', '2026-01-26 19:05:19', 'active', 1, 1, 1, 1),
(6, 'Senior Executive Support & Services', 0.00, 0.00, 0.00, 0.00, 0.00, '2026-01-26 19:05:58', '2026-01-26 19:05:58', 'active', 1, 1, 1, 1),
(7, 'Executive Accounts', 0.00, 0.00, 0.00, 0.00, 0.00, '2026-01-26 19:06:22', '2026-01-26 19:06:22', 'active', 1, 1, 1, 1),
(8, 'Accounts Manager', 0.00, 0.00, 0.00, 0.00, 0.00, '2026-01-26 19:06:37', '2026-01-26 19:06:37', 'active', 1, 1, 1, 1),
(9, 'System Administrator', 0.00, 0.00, 0.00, 0.00, 0.00, '2026-01-26 19:07:06', '2026-01-26 19:07:06', 'active', 1, 1, 1, 1),
(10, 'Chief Technical Officer', 0.00, 0.00, 0.00, 0.00, 0.00, '2026-01-26 19:07:28', '2026-01-26 19:07:28', 'active', 1, 1, 1, 1),
(11, 'Manager Support', 0.00, 0.00, 0.00, 0.00, 0.00, '2026-01-26 19:07:41', '2026-01-26 19:07:41', 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `districts`
--

CREATE TABLE `districts` (
  `id` bigint UNSIGNED NOT NULL,
  `district_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `districts`
--

INSERT INTO `districts` (`id`, `district_name`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 'Bagerhat', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(2, 'Bandarban', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(3, 'Barguna', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(4, 'Barisal', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(5, 'Bhola', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(6, 'Bogra', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(7, 'Brahmanbaria', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(8, 'Chandpur', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(9, 'Chittagong', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(10, 'Chuadanga', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(11, 'Comilla', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(12, 'Cox\'s Bazar', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(13, 'Dhaka', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(14, 'Dinajpur', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(15, 'Faridpur', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(16, 'Feni', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(17, 'Gaibandha', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(18, 'Gazipur', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(19, 'Gopalganj', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(20, 'Habiganj', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(21, 'Jamalpur', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(22, 'Jessore', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(23, 'Jhalokati', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(24, 'Jhenaidah', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(25, 'Joypurhat', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(26, 'Khagrachhari', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(27, 'Khulna', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(28, 'Kishoreganj', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(29, 'Kurigram', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(30, 'Kushtia', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(31, 'Lakshmipur', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(32, 'Lalmonirhat', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(33, 'Madaripur', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(34, 'Magura', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(35, 'Manikganj', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(36, 'Meherpur', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(37, 'Moulvibazar', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(38, 'Munshiganj', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(39, 'Mymensingh', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(40, 'Naogaon', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(41, 'Narail', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(42, 'Narayanganj', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(43, 'Narsingdi', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(44, 'Natore', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(45, 'Nawabganj', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(46, 'Netrakona', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(47, 'Nilphamari', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(48, 'Noakhali', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(49, 'Pabna', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(50, 'Panchagarh', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(51, 'Patuakhali', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(52, 'Pirojpur', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(53, 'Rajbari', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(54, 'Rajshahi', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(55, 'Rangamati', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(56, 'Rangpur', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(57, 'Satkhira', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(58, 'Shariatpur', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(59, 'Sherpur', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(60, 'Sirajganj', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(61, 'Sunamganj', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(62, 'Sylhet', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(63, 'Tangail', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1),
(64, 'Thakurgaon', '2025-12-17 02:45:27', '2025-12-17 02:45:27', 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint UNSIGNED NOT NULL,
  `empid` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `joining_date` date NOT NULL,
  `designation_id` int DEFAULT NULL,
  `salary` decimal(12,2) NOT NULL DEFAULT '0.00',
  `full_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dob` date DEFAULT NULL,
  `nid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_id` int DEFAULT NULL,
  `branch` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_occupation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_nid` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_mobile` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_occupation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_nid` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_mobile` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spouse_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spouse_mobile` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch_id` int DEFAULT NULL,
  `image` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `resign_date` date DEFAULT NULL,
  `resign_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_salaries`
--

CREATE TABLE `employee_salaries` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` int NOT NULL,
  `salary_parameter_id` int NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint UNSIGNED NOT NULL,
  `expenseid` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expense_date` date NOT NULL,
  `workorder_id` int NOT NULL,
  `employee_id` int DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `approved_by` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `expenseid`, `expense_date`, `workorder_id`, `employee_id`, `total_amount`, `approved_by`, `approved_date`, `created_at`, `updated_at`, `deleted_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, '111', '2026-04-26', 1, NULL, 5000.00, NULL, NULL, '2026-04-26 08:25:56', '2026-04-26 13:40:57', NULL, 'active', 1, 1, 1, 1),
(2, '112', '2026-04-26', 1, NULL, 8000.00, '28', '2026-04-26', '2026-04-26 13:51:42', '2026-04-26 14:03:52', NULL, 'active', 1, 1, 1, 1),
(5, '113', '2026-04-26', 1, NULL, 500.00, NULL, NULL, '2026-04-26 16:43:21', '2026-04-26 16:43:21', NULL, 'active', 1, 1, 1, 1),
(6, '114', '2026-04-27', 1, NULL, 10000.00, '28', '2026-07-29', '2026-04-27 13:16:28', '2026-07-29 13:40:08', NULL, 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `expense_details`
--

CREATE TABLE `expense_details` (
  `id` bigint UNSIGNED NOT NULL,
  `expense_id` int NOT NULL,
  `account_id` int NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `narration` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_closed` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expense_details`
--

INSERT INTO `expense_details` (`id`, `expense_id`, `account_id`, `amount`, `narration`, `is_closed`, `created_at`, `updated_at`, `deleted_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 1, 48, 5000.00, 'Ubill', 0, '2026-04-26 08:25:56', '2026-04-26 08:25:56', NULL, 'active', 1, 1, 1, 1),
(2, 2, 50, 5000.00, 'ffff', 1, '2026-04-26 13:51:42', '2026-07-11 12:11:04', NULL, 'active', 1, 1, 1, 1),
(3, 2, 46, 3000.00, 'sdsd', 0, '2026-04-26 13:51:42', '2026-04-26 13:51:42', NULL, 'active', 1, 1, 1, 1),
(4, 5, 52, 500.00, NULL, 0, '2026-04-26 16:43:21', '2026-04-26 16:43:21', NULL, 'active', 1, 1, 1, 1),
(5, 6, 45, 10000.00, NULL, 0, '2026-04-27 13:16:28', '2026-04-27 13:16:28', NULL, 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `financial_years`
--

CREATE TABLE `financial_years` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint NOT NULL,
  `is_closed` tinyint NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `financial_years`
--

INSERT INTO `financial_years` (`id`, `title`, `start_date`, `end_date`, `is_current`, `is_closed`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 'FY 2020 - 2021', '2020-07-01', '2021-06-30', 0, 1, '2025-12-23 21:35:37', '2025-12-23 21:35:37', 'active', 1, 1, 1, 1),
(2, 'FY 2021-2022', '2021-07-01', '2022-06-30', 0, 1, '2025-12-24 03:40:03', '2025-12-24 03:40:03', 'active', 1, 1, 1, 1),
(3, 'FY 2022-2023', '2022-07-01', '2023-06-30', 0, 1, '2025-12-24 03:40:59', '2025-12-24 03:40:59', 'active', 1, 1, 1, 1),
(4, 'FY 2023-2024', '2023-07-01', '2024-06-30', 0, 0, '2025-12-24 03:41:55', '2025-12-24 03:41:55', 'active', 1, 1, 1, 1),
(5, 'FY 2024-2025', '2024-07-01', '2025-06-30', 0, 0, '2025-12-24 03:42:48', '2025-12-24 03:42:48', 'active', 1, 1, 1, 1),
(6, 'FY 2025-2026', '2025-07-01', '2026-06-30', 1, 0, '2025-12-24 03:43:29', '2025-12-24 03:43:29', 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `fund_account_balance`
-- (See below for the actual view)
--
CREATE TABLE `fund_account_balance` (
`account_id` bigint unsigned
,`account_name` varchar(150)
,`current_balance` decimal(33,2)
,`opening_balance` decimal(12,2)
,`opening_balance_type` varchar(225)
,`total_expense` decimal(30,2)
,`total_income` decimal(31,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `fund_transfers`
--

CREATE TABLE `fund_transfers` (
  `id` bigint UNSIGNED NOT NULL,
  `transfer_date` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_account_id` int NOT NULL,
  `to_account_id` int NOT NULL,
  `amount` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remarks` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `help_infos`
--

CREATE TABLE `help_infos` (
  `id` bigint UNSIGNED NOT NULL,
  `model_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page_type` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sorting` int NOT NULL DEFAULT '0',
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `help_infos`
--

INSERT INTO `help_infos` (`id`, `model_name`, `page_type`, `description`, `sorting`, `status`, `created_at`, `updated_at`) VALUES
(3, 'SiteSetting', 'view', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed venenatis ligula vel arcu pharetra, ac fermentum sapien consectetur. Maecenas ut aliquet odio. Nunc id aliquam dolor. Vestibulum tincidunt nisi et nunc molestie blandit. Duis sagittis vel ex et eleifend. Sed vitae massa ac libero pharetra egestas. Cras justo diam, cursus id tortor nec, ultrices interdum erat. Praesent et cursus diam, id volutpat lorem.</p>\r\n\r\n<p>Fusce tempus, justo a interdum mattis, quam ipsum interdum eros, sit amet placerat leo magna in risus. Curabitur venenatis vel sapien id posuere. Sed porta gravida dignissim. Morbi vitae ante vel nulla porta scelerisque. Duis ac nisi ut ex sodales cursus et et orci. Curabitur non porttitor sem. Donec fringilla erat ac est suscipit, eu interdum nibh lacinia. Morbi ut libero imperdiet ipsum placerat accumsan. Quisque mattis lacus sed elit suscipit viverra. Integer id tristique sapien.</p>\r\n\r\n<p>Phasellus lobortis massa ac condimentum interdum. Etiam sagittis ante ac magna pretium, a eleifend neque aliquet. Curabitur dolor odio, ullamcorper vitae tincidunt et, cursus quis lectus. Integer lobortis magna eget orci tempus facilisis. Nullam nec interdum nulla, in pellentesque nisl. Suspendisse potenti. Mauris turpis velit, imperdiet nec elit a, iaculis sollicitudin lorem. Aliquam euismod velit mattis, scelerisque dolor id, convallis magna.</p>\r\n\r\n<p>Vivamus fermentum accumsan metus, id pharetra ipsum vehicula sit amet. Quisque vestibulum orci non est blandit, et posuere dui tristique. Morbi egestas sem ac tellus rhoncus maximus. Nam rutrum mattis erat, eget dapibus felis faucibus a. Ut eget erat et elit sodales congue a maximus augue. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nulla facilisi. Vivamus ultrices orci accumsan, auctor ipsum efficitur, convallis nulla. Maecenas nibh orci, efficitur eu finibus sit amet, ornare quis magna. Curabitur facilisis dictum pretium. Sed porta augue velit, ac tempor ligula placerat venenatis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse porttitor pharetra mauris, at vehicula lorem blandit vitae.</p>\r\n\r\n<p>Phasellus quis odio accumsan, consequat ante a, dapibus arcu. Aenean sed turpis ligula. Integer vitae mauris purus. Suspendisse condimentum in risus quis elementum. Ut sit amet quam vulputate, scelerisque nibh eu, feugiat lorem. In ac faucibus odio. Suspendisse ultrices egestas leo. In rhoncus sollicitudin viverra. Suspendisse at commodo nunc, a mattis sapien.</p>\r\n\r\n<p>Quisque aliquam eget sem in sollicitudin. Donec quis nulla id est semper luctus. Nam tincidunt sit amet leo eget dapibus. Mauris faucibus fermentum rhoncus. Nunc ut auctor libero, vitae suscipit eros. Mauris auctor, nulla non rutrum semper, diam leo ultrices enim, et volutpat arcu velit vitae justo. Donec et elit eu metus gravida feugiat vel id ipsum. Nullam magna turpis, laoreet non nisl cursus, consequat tempus libero. Praesent dui odio, faucibus fringilla elit ac, euismod mattis lorem. Duis sagittis ut nisi eget aliquet.</p>\r\n\r\n<p>Phasellus quam diam, dignissim dictum bibendum et, maximus eget odio. Donec vitae metus et enim condimentum sodales ut et tortor. Donec neque libero, luctus id porta sed, mollis eu sapien. Vivamus sit amet nisl vel nulla molestie commodo nec a massa. Sed at urna semper, fermentum libero vitae, condimentum sapien. Sed interdum dui eget neque feugiat, in malesuada felis scelerisque. Curabitur dapibus massa sed ex consequat, in dignissim lectus lobortis. Nullam elit ipsum, vestibulum vel feugiat ut, aliquet quis sapien. Vestibulum vulputate urna eu rhoncus consequat. Phasellus dictum luctus augue non porttitor. Sed suscipit mauris quis bibendum elementum. Nunc dapibus elit at pellentesque sodales. Sed nec dolor rutrum, posuere justo at, accumsan augue.</p>\r\n\r\n<p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nulla iaculis lectus eu odio tempus, non bibendum sapien suscipit. Vivamus viverra lacinia sem eget mollis. Pellentesque iaculis leo ut tellus tristique mattis. Sed id condimentum quam, ut commodo odio. Phasellus et turpis eget risus lacinia vestibulum sit amet quis sapien. Duis sit amet metus sed diam lacinia elementum vitae vitae dolor. In quis ligula lorem. Quisque at leo tellus. Maecenas in fermentum lorem. Aliquam et nulla ligula.</p>\r\n\r\n<p>Nam commodo sit amet neque fringilla interdum. Nam semper, ante ut faucibus ullamcorper, ipsum arcu ultrices sapien, sit amet rhoncus magna neque sed lectus. Fusce sit amet libero nec sem gravida porttitor. Duis arcu justo, facilisis eget faucibus et, lacinia commodo dui. Suspendisse quis euismod ipsum. Etiam vel maximus urna. Integer hendrerit varius cursus. Curabitur in tellus convallis ex consequat varius ut vitae eros. Pellentesque vel eros faucibus, malesuada nunc id, efficitur ligula. Quisque eu sagittis ante, nec lacinia tortor. Proin blandit mattis dapibus. Aliquam dapibus dolor sed felis imperdiet aliquet. Praesent a lacinia neque, id ultricies ligula.</p>\r\n\r\n<p>Aliquam venenatis tortor et ultricies faucibus. Fusce et metus id purus tempor accumsan ut a orci. Integer nunc ex, molestie vel neque in, dictum finibus metus. Cras porta mauris non sodales vehicula. Donec ac magna hendrerit sem iaculis faucibus. Suspendisse potenti. Proin nec metus urna.</p>\r\n\r\n<p>In consectetur, turpis non lobortis hendrerit, nibh diam eleifend tellus, sed accumsan erat orci ac leo. Fusce feugiat ac enim non mollis. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam suscipit fermentum quam, eu convallis ante. Fusce sed quam tincidunt justo tristique rutrum luctus ac quam. Sed blandit imperdiet mi, non fermentum orci pellentesque a. Vivamus vel commodo ligula.</p>\r\n\r\n<p>Vestibulum ut pellentesque nulla. Aliquam pharetra quis arcu ut egestas. Cras semper felis consectetur fringilla dictum. Proin vel pharetra risus. Aliquam erat volutpat. Morbi et ligula nisl. Sed maximus massa et tempus porta. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Duis metus elit, dictum vel libero tincidunt, vestibulum feugiat metus. Praesent fringilla laoreet quam, rhoncus dignissim risus bibendum a. Pellentesque malesuada leo nec nisi laoreet iaculis. Aliquam lacinia leo ac mattis iaculis. Donec eu iaculis dolor.</p>\r\n\r\n<p>Nulla mollis condimentum risus. Nulla gravida dui mauris, at congue enim mattis a. Vivamus ut accumsan libero. Aenean dui justo, suscipit nec fringilla at, commodo eget nibh. Nam viverra iaculis nunc quis sagittis. Mauris laoreet massa ac dolor dapibus, nec interdum tortor iaculis. In est mauris, malesuada eget facilisis at, gravida vel lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse vehicula facilisis velit sodales pellentesque. Phasellus at maximus erat. Proin ac dapibus elit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Pellentesque et elit pretium, semper mauris a, commodo enim. Suspendisse volutpat tempus massa non iaculis.</p>\r\n\r\n<p>Morbi pharetra pulvinar ante volutpat vestibulum. Donec ut elit malesuada, imperdiet nunc nec, egestas dolor. Sed sollicitudin vitae nulla eget fermentum. Mauris vehicula est quis elit fermentum molestie non eu massa. Quisque ac magna interdum est congue finibus. Phasellus pretium diam quam, a vestibulum lectus accumsan at. Aenean justo elit, eleifend vulputate sodales commodo, dapibus et metus. Fusce sed orci elit. Praesent eget purus vel purus interdum sodales at dapibus ante. Nunc in neque massa. Cras nec quam consequat, semper tellus porttitor, sollicitudin felis. Cras tincidunt vel libero quis ultricies. Nullam non diam ac ligula finibus accumsan. Etiam tempor nisl ac viverra faucibus. Sed vitae rutrum diam, at rhoncus metus. Etiam fermentum ac dolor id dapibus.</p>\r\n\r\n<p>Aliquam ullamcorper erat in fringilla imperdiet. In congue convallis dapibus. Nam ac vehicula eros, quis tristique magna. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Vivamus elementum erat id libero faucibus egestas vitae vitae diam. Etiam dapibus sed justo vel ornare. Cras aliquet orci lacinia iaculis ornare. Pellentesque tempor ut massa quis lacinia. Nulla rutrum quam at nisl dictum cursus. Aenean accumsan mi sit amet risus convallis, id dignissim massa aliquam. In ullamcorper tortor a dui consectetur, ut maximus turpis elementum. Quisque luctus nisi vitae dolor gravida ornare. Fusce eu velit sit amet ligula consequat semper ac sed dolor. Nam efficitur sagittis libero sit amet hendrerit.</p>\r\n\r\n<p>Etiam felis justo, viverra at felis vitae, semper ornare nisl. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Morbi massa ex, sagittis in arcu vel, lobortis lobortis odio. Morbi velit nisi, tempor ac orci ut, fermentum auctor metus. Etiam lacinia nec nisl id interdum. Praesent in ante dapibus, eleifend velit a, laoreet metus. Sed porttitor lorem vitae ante malesuada, id aliquet metus imperdiet.</p>\r\n\r\n<p>Curabitur id semper enim, eu posuere diam. Aliquam erat volutpat. Fusce efficitur erat et magna laoreet venenatis. Duis vulputate in felis fringilla tempus. Donec sed imperdiet nisi, et porttitor purus. Nunc eleifend sapien vel urna pretium, vitae dignissim orci sagittis. Mauris fermentum sagittis nisi eget lacinia. Nam id nisi sollicitudin, ultricies dui eleifend, eleifend nisl. Maecenas in ex purus. Sed interdum augue leo, quis ultricies felis laoreet sit amet. Pellentesque augue orci, tincidunt quis porttitor eu, iaculis eu quam. In hac habitasse platea dictumst. Nulla aliquam eros at feugiat vehicula. Donec congue eget est quis vulputate. Morbi et libero facilisis, egestas lectus a, dignissim felis.</p>\r\n\r\n<p>Quisque eget quam venenatis, consectetur eros laoreet, tempor orci. Maecenas eget augue quis purus suscipit porttitor ut nec nulla. In hac habitasse platea dictumst. Aliquam sodales feugiat quam, et porta leo. Donec ultrices id justo eget rhoncus. Phasellus maximus erat sit amet mattis euismod. Quisque dolor sapien, scelerisque ac nisl a, volutpat dignissim eros. Aliquam egestas quis nisl sit amet mattis. Sed porta, odio sed mollis tristique, felis massa dictum erat, ac ultrices enim metus vitae justo. Praesent a congue lectus. Curabitur vel ultricies risus, nec maximus lectus. Pellentesque in vehicula metus, id finibus enim. Quisque pellentesque sem pretium, venenatis nisl quis, semper mauris.</p>\r\n\r\n<p>Integer imperdiet odio vitae fermentum blandit. Mauris vestibulum sodales lectus non tempus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec vitae eros ante. In et bibendum odio, a euismod libero. Sed in nisi eget mi imperdiet varius. Sed condimentum posuere magna, non laoreet leo maximus in. Aliquam erat volutpat. Maecenas sollicitudin dignissim ligula vitae dictum. Vivamus tempus tellus id magna commodo, non ultricies ligula molestie. Duis et lacus scelerisque, pretium felis sed, tempus augue. Integer bibendum augue et leo lobortis, nec laoreet lorem porttitor. Nulla facilisi.</p>', 8, 'active', '2024-09-18 00:08:08', '2024-09-21 18:27:17'),
(7, 'Page', 'create', '<ul>\r\n	<li>Mauris sit amet orci et mi malesuada vestibulum.</li>\r\n	<li>Donec id est quis elit finibus luctus et vitae magna.</li>\r\n	<li>Fusce a nulla vestibulum velit aliquam ultrices vel et nibh.</li>\r\n	<li>Proin pretium nulla ut consectetur hendrerit.</li>\r\n	<li>Curabitur id justo vehicula, lacinia justo tempus, tincidunt leo.</li>\r\n</ul>', 6, 'active', '2024-09-19 01:06:13', '2024-10-02 20:16:09'),
(12, 'Admin', 'Create', '<p><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">An admin manages system settings and overall platform operations to ensure smooth functionality and security.</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Profile Image (Required):</strong> Upload or select a profile picture.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Email:</strong>Enter the admin&#39;s valid email address.Example:User123@gmail.com</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Password:</strong> Set a secure password for login.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Role:</strong> Choose an admin role from the dropdown.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Mobile:</strong> Input the admin&rsquo;s contact mobile number.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Sorting: </strong>Set the display order for the admin.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Status Switch: </strong>Toggle to activate or deactivate the account.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Submit: </strong>Save all information to create the admin.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<h3 style=\"text-align: justify;\"><br />\r\n&nbsp;</h3>', 1, 'active', '2024-10-06 19:35:20', '2024-10-09 21:31:54'),
(14, 'Admin', 'view', '<ul>\r\n	<li>Print</li>\r\n</ul>', 10, 'active', '2024-10-07 23:32:30', '2024-10-07 23:32:30'),
(15, 'Category', 'Create', '<p><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">This section provides guidance on creating a new category using the <strong>&quot;Add&quot;</strong> button:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Module Name (Required):</strong> Select the relevant module (e.g., News, Event) for the new category.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Title (Required):</strong> Enter a descriptive name for the category.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Sorting:</strong> Set the order in which this category will appear (lower numbers show up earlier).</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Status Button (Switch)</strong>: Toggle to activate or deactivate the category. Activated categories are visible, while deactivated ones remain hidden.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Submit Button: </strong>After completing all fields, click Submit to save the new category.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p>&nbsp;</p>', 10, 'active', '2024-10-10 06:08:58', '2024-10-10 06:15:35'),
(16, 'Category', 'Index', '<p><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">This section assists with navigating the Admin List, offering various search and filter functionalities:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Dropdown Menu for Searching: Allows users to search by </span></span></span></span><span style=\"color:#2980b9;\"><strong><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Title<span style=\"color:#3498db;\"><strong><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"> </span></span></span></strong></span>and<span style=\"color:#3498db;\"><strong><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"> </span></span></span></strong></span>Module</span></span></span></strong></span><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"> for quick access to specific admin records.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Advanced Search: Offers filters for refining results by Status to locate admins based on permissions and activity.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Action Buttons (View, Edit, Delete): Each admin in the list has buttons to:</span></span></span></span></span></span>\r\n	<ul>\r\n		<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">View: Display detailed information.</span></span></span></span></span></span></li>\r\n		<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Edit: Update admin details.</span></span></span></span></span></span></li>\r\n		<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Delete: Remove the admin from the system.</span></span></span></span></span></span></li>\r\n	</ul>\r\n	</li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Download Options: The admin list can be downloaded in various formats such as Print, Excel, and PDF for reporting or record-keeping purposes.</span></span></span></span></span></span></li>\r\n</ul>', 11, 'active', '2024-10-10 06:19:34', '2024-10-10 06:22:57'),
(18, 'Admin', 'Index', '<p style=\"text-align: justify;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">This section assists in using the Admin List, including search and filtering options to manage admin records efficiently. Below are the list types for each variable, along with user-friendly descriptions of what each filter does:</span></span></span></span></span></span></p>\r\n\r\n<p style=\"line-height:1.656; margin-top:16px; margin-bottom:16px\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Dropdown Menu for Searching</span></span></span></span></span></span></p>\r\n\r\n<p style=\"line-height:1.656; margin-top:16px; margin-bottom:16px\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Search By:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Name (Text):</strong> Enter the admin&#39;s name to find specific individuals.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Email (Text):</strong> Search by email address to locate admins quickly.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Mobile (Number):</strong> Find admins using their mobile phone number.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height:1.656; margin-top:16px; margin-bottom:16px\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Advanced Search Options</span></span></span></span></span></span></p>\r\n\r\n<p style=\"line-height:1.656; margin-top:16px; margin-bottom:16px\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Filter By:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Role (Dropdown):</strong> Filter admins based on their role (e.g., Admin, Editor, Manager), to view users with specific permissions.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Status (Dropdown):</strong> Filter by active or inactive status to see only active users or all users regardless of status.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height:1.656; margin-top:16px; margin-bottom:16px\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Action Buttons:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>View (Button):</strong> View detailed information about a specific admin.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Edit (Button):</strong> Modify the details of an admin user.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Delete (Button):</strong> Remove the admin from the list.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height:1.656; margin-top:16px; margin-bottom:16px\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Download Options:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Print (Button):</strong> Print the admin list for offline use or record-keeping.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Excel (Button):</strong> Download the list in Excel format for further data manipulation.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>PDF (Button):</strong> Export the admin list as a PDF document for easy sharing or storage.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height: 1.656; margin-top: 16px; margin-bottom: 16px; text-align: justify;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Each of these options helps users filter and manage the admin list more effectively, providing control over how data is searched, displayed, and downloaded.</span></span></span></span></span></span></p>\r\n\r\n<p>&nbsp;</p>', 2, 'active', '2024-10-14 23:08:29', '2024-10-15 00:24:29'),
(19, 'Pageseo', 'Index', '<p style=\"text-align: justify;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">This section provides assistance for navigating and managing the Page SEO List, including search, filter, sorting, and action tools. Below are the list types for each variable, along with user-friendly descriptions:</span></span></span></span></span></span></p>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Dropdown Menu for Searching</span></span></span></span></span></span></p>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Search By:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Meta Description (Text):</strong> Enter part or all of the meta description to locate specific SEO metadata for a page.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Advanced Search Options</span></span></span></span></span></span></p>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Filter By:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Select Page (Dropdown):</strong> Choose a page from the list to view or modify its SEO data.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Status (Dropdown)</strong>: Filter by active or inactive status to display only active SEO records or both.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Action Buttons:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>View (Button)</strong>: Open detailed SEO data for the selected page.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Edit (Button): </strong>Modify the SEO information, such as meta titles or descriptions.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Delete (Button):</strong> Remove the SEO data related to the page from the system.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Sorting Options:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Click Sorting Icon (Header): </strong>Click on the column headers (e.g., Meta Description, Status) to sort the list in ascending or descending order.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Sorting Number (Input Field):</strong> Enter a number in the sorting field to manually reorder the pages based on priority.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Download Options:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Print (Button): </strong>Print the SEO list for record-keeping or offline review.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Excel (Button):</strong> Download the list as an Excel file for further editing or analysis.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>PDF (Button): </strong>Export the SEO list as a PDF for sharing or documentation purposes.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height: 1.38; text-align: justify;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Each filter, search, and sorting function is designed to help users quickly locate and manage SEO information across various pages, ensuring easy access and organization.</span></span></span></span></span></span></p>\r\n\r\n<p>&nbsp;</p>', 12, 'active', '2024-10-14 23:25:57', '2024-10-15 00:24:59'),
(20, 'Page', 'Index', '<p style=\"text-align: justify;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">This section provides assistance for managing the Page List, which includes search, filter, and action options. Below are the list types for each variable, along with a brief, user-friendly description:</span></span></span></span></span></span></p>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Dropdown Menu for Searching</span></span></span></span></span></span></p>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Search By: </span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Title (Text):</strong> Enter part or all of the page title to find specific pages in the list.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Advanced Search Options</span></span></span></span></span></span></p>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Filter By: </span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Status (Dropdown): </strong>Filter pages by their active or inactive status to display either only active pages or both active and inactive ones.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Action Buttons for Each User:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>View (Button):</strong> Open the page to view its details and content.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Edit (Button):</strong> Modify the page content, title, or settings.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Delete (Button):</strong> Remove the page from the list and the system.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Sorting Options:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Click Sorting Icon (Header):</strong> Click on the column headers (e.g., Title, Status) to sort the list in ascending or descending order.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Download Options:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Print (Button):</strong> Print the page list for offline review or documentation.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Excel (Button):</strong> Download the list as an Excel file for further data manipulation.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>PDF (Button):</strong> Export the list as a PDF document for record-keeping or sharing.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height: 1.38; text-align: justify;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">These options enable users to efficiently manage and organize pages, providing flexibility in how pages are searched, filtered, and downloaded.</span></span></span></span></span></span></p>\r\n\r\n<p>&nbsp;</p>', 9, 'active', '2024-10-14 23:36:51', '2024-10-15 00:07:47');
INSERT INTO `help_infos` (`id`, `model_name`, `page_type`, `description`, `sorting`, `status`, `created_at`, `updated_at`) VALUES
(21, 'Pagesection', 'Index', '<p style=\"text-align: justify;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">This section provides assistance for managing the Page Section List, which includes various search, filter, sorting, and action options. Below are the list types for each variable, along with a brief, user-friendly description:</span></span></span></span></span></span></p>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Dropdown Menu for Searching</span></span></span></span></span></span></p>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Search By:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Title (Text):</strong> Enter part or all of the section title to find specific page sections.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Advanced Search Options</span></span></span></span></span></span></p>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Filter By:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Select Page (Dropdown):</strong> Choose the relevant page to view the sections associated with it.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Status (Dropdown):</strong> Filter by active or inactive status to display either only active sections or both active and inactive sections.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Action Buttons:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>View (Button): </strong>View the section details for a specific page.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Edit (Button):</strong> Modify the section content or settings.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Delete (Button):</strong> Remove the section from the list and system.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Sorting Options:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Click Sorting Icon (Header):</strong> Sort the list by clicking on column headers, such as Title or Status, to arrange sections in ascending or descending order.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Sorting Number (Input Field):</strong> Adjust the display order of sections by entering a custom number to prioritize certain sections.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height:1.38\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:700\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">Download Options:</span></span></span></span></span></span></p>\r\n\r\n<ul>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Print (Button): </strong>Print the page section list for offline use or documentation.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>Excel (Button): </strong>Download the list in Excel format for further editing or analysis.</span></span></span></span></span></span></li>\r\n	<li style=\"line-height: 1.38;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\"><strong>PDF (Button):</strong> Export the list as a PDF for sharing or archiving.</span></span></span></span></span></span></li>\r\n</ul>\r\n\r\n<p style=\"line-height: 1.38; text-align: justify;\"><span style=\"font-size:11pt; font-variant:normal; white-space:pre-wrap\"><span style=\"font-family:Arial,sans-serif\"><span style=\"color:#000000\"><span style=\"font-weight:400\"><span style=\"font-style:normal\"><span style=\"text-decoration:none\">These options help users efficiently manage page sections, providing control over how sections are searched, filtered, sorted, and downloaded for organization and editing.</span></span></span></span></span></span></p>\r\n\r\n<p>&nbsp;</p>', 10, 'active', '2024-10-15 00:15:43', '2024-10-15 00:24:43');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` int NOT NULL,
  `invoice_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_date` date NOT NULL,
  `original_amount` decimal(12,2) NOT NULL,
  `discount` decimal(12,2) DEFAULT NULL,
  `vat` decimal(12,2) NOT NULL DEFAULT '0.00',
  `amount` decimal(8,2) NOT NULL,
  `paid_amount` decimal(8,2) DEFAULT NULL,
  `is_previous_due` tinyint NOT NULL DEFAULT '0',
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `deleted_at` datetime DEFAULT NULL,
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `client_id`, `invoice_no`, `invoice_date`, `original_amount`, `discount`, `vat`, `amount`, `paid_amount`, `is_previous_due`, `is_closed`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `deleted_at`, `created_ip`, `updated_ip`) VALUES
(1, 1, '111', '2026-03-28', 239750.00, 0.00, 0.00, 239750.00, 239750.00, 0, 1, '2026-03-28 17:02:30', '2026-03-29 13:11:46', 'active', 1, 1, NULL, 1, 1),
(2, 1, '112', '2026-03-28', 250000.00, 0.00, 0.00, 250000.00, NULL, 0, 0, '2026-03-28 17:03:32', '2026-03-29 02:42:37', 'active', 1, 1, '2026-03-29 08:42:37', 1, 1),
(3, 4, '112', '2026-07-29', 3000.00, 0.00, 0.00, 3000.00, 3000.00, 0, 1, '2026-07-29 13:15:03', '2026-07-29 13:15:44', 'active', 1, 1, NULL, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `invoice_details`
--

CREATE TABLE `invoice_details` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `workorder_id` int NOT NULL,
  `item_id` int DEFAULT NULL,
  `color_id` int DEFAULT NULL,
  `size_id` int DEFAULT NULL,
  `account_id` int NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `qty` decimal(12,2) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency_id` int DEFAULT NULL,
  `currency_rate` decimal(12,2) DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `deleted_at` datetime DEFAULT NULL,
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_details`
--

INSERT INTO `invoice_details` (`id`, `invoice_id`, `reference`, `workorder_id`, `item_id`, `color_id`, `size_id`, `account_id`, `description`, `qty`, `amount`, `currency_id`, `currency_rate`, `total_amount`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `deleted_at`, `created_ip`, `updated_ip`) VALUES
(1, '1', NULL, 1, NULL, NULL, NULL, 0, 'Order No: 39221', 1550.00, 1918.00, 2, 125.00, 239750.00, '2026-03-28 17:02:31', '2026-03-28 17:02:31', 'active', 1, 1, NULL, 1, 1),
(4, '3', '39221 / 24506', 1, NULL, NULL, NULL, 0, 'Order No: 39221', 1550.00, 3000.00, 2, 0.00, 3000.00, '2026-07-29 13:15:04', '2026-07-29 13:15:04', 'active', 1, 1, NULL, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `issues`
--

CREATE TABLE `issues` (
  `id` bigint UNSIGNED NOT NULL,
  `issueno` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `branch_id` int NOT NULL,
  `department` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue_date` date NOT NULL,
  `employee_id` int DEFAULT NULL,
  `client_id` int DEFAULT NULL,
  `remarks` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `issue_details`
--

CREATE TABLE `issue_details` (
  `id` bigint UNSIGNED NOT NULL,
  `issue_id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `item_id` int NOT NULL,
  `qty` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` bigint UNSIGNED NOT NULL,
  `barcode` int NOT NULL,
  `category_id` int NOT NULL,
  `title` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_id` int NOT NULL,
  `opening_qty` int DEFAULT NULL,
  `opening_rate` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `barcode`, `category_id`, `title`, `image`, `description`, `unit_id`, `opening_qty`, `opening_rate`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 111, 6, 'RJ45', 'upload/item/251228065554-8835591730373_1179176734426113_5920043827941920060_n.jpg', 'n/a', 4, 10, 200.00, '2025-12-28 00:55:54', '2026-01-02 12:59:23', 'active', 1, 1, 1, 1),
(2, 112, 6, 'Cable', 'upload/item/251228072427-8892591730373_1179176734426113_5920043827941920060_n.jpg', 'undefined', 2, 500, 200.00, '2025-12-28 01:24:27', '2026-01-02 12:59:28', 'active', 1, 1, 1, 1),
(3, 113, 6, 'Crimp Tool for Fiber', 'upload/item/260115015745-5979111.jpeg', 'undefined', 3, NULL, 0.00, '2026-01-15 07:57:45', '2026-01-15 07:57:45', 'active', 1, 1, 1, 1),
(4, 114, 6, '5\" Full Flush Cut Side Cutting Pliers', 'upload/item/260115015917-9024112.jpeg', 'undefined', 3, NULL, NULL, '2026-01-15 07:59:17', '2026-01-15 07:59:17', 'active', 1, 1, 1, 1),
(5, 115, 6, 'Cable 2 Core', NULL, 'undefined', 2, NULL, NULL, '2026-01-29 20:14:30', '2026-01-29 20:14:30', 'active', 1, 1, 1, 1),
(6, 116, 6, 'Cable 4 Core', NULL, 'undefined', 2, NULL, NULL, '2026-01-29 20:14:42', '2026-01-29 20:14:42', 'active', 1, 1, 1, 1),
(7, 117, 6, 'Cable 6 Core', NULL, 'undefined', 2, NULL, NULL, '2026-01-29 20:14:58', '2026-01-29 20:14:58', 'active', 1, 1, 1, 1),
(8, 118, 6, 'Cable 12 Core', NULL, 'undefined', 2, NULL, NULL, '2026-01-29 20:15:11', '2026-01-29 20:15:11', 'active', 1, 1, 1, 1),
(9, 119, 6, 'Cable 24 Core', NULL, 'undefined', 2, NULL, NULL, '2026-01-29 20:15:23', '2026-01-29 20:15:23', 'active', 1, 1, 1, 1),
(10, 120, 6, 'OLT', NULL, 'undefined', 3, NULL, NULL, '2026-02-01 19:22:34', '2026-02-01 19:22:34', 'active', 1, 1, 1, 1),
(11, 121, 6, 'PON MODULE', NULL, 'undefined', 3, NULL, NULL, '2026-02-01 19:23:50', '2026-02-01 19:23:50', 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `item_prices`
--

CREATE TABLE `item_prices` (
  `id` bigint UNSIGNED NOT NULL,
  `item_id` bigint NOT NULL,
  `color_id` int DEFAULT NULL,
  `size_id` int DEFAULT NULL,
  `purchase_price` decimal(8,2) NOT NULL,
  `selling_price` decimal(8,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `item_stock_summaries`
-- (See below for the actual view)
--
CREATE TABLE `item_stock_summaries` (
`current_stock` decimal(33,0)
,`item_id` int
,`total_qty_in` decimal(32,0)
,`total_qty_out` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Table structure for table `loan_infos`
--

CREATE TABLE `loan_infos` (
  `id` bigint UNSIGNED NOT NULL,
  `trnsid` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `trns_date` date NOT NULL,
  `employee_id` int NOT NULL,
  `trns_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `total_installment` int NOT NULL,
  `installment_amount` int NOT NULL,
  `deduct_from_salary` tinyint(1) NOT NULL,
  `schedule_day` int DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `closing_date` date DEFAULT NULL,
  `due_amount` decimal(8,2) NOT NULL,
  `reason` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_paid` tinyint NOT NULL DEFAULT '0',
  `is_closed` tinyint NOT NULL DEFAULT '0',
  `approved_by` int DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_installments`
--

CREATE TABLE `loan_installments` (
  `id` int NOT NULL,
  `employee_id` int DEFAULT NULL,
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `deduct_from_salary` tinyint NOT NULL DEFAULT '0',
  `loan_info_id` int NOT NULL,
  `instalment_date` date NOT NULL,
  `is_closed` tinyint NOT NULL DEFAULT '0',
  `amount` decimal(12,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` int NOT NULL,
  `updated_by` int NOT NULL,
  `created_ip` int NOT NULL,
  `updated_ip` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_validators`
--

CREATE TABLE `media_validators` (
  `id` bigint UNSIGNED NOT NULL,
  `model_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `max_size` int NOT NULL,
  `min_width` int DEFAULT NULL,
  `min_height` int DEFAULT NULL,
  `resize_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sorting` int NOT NULL DEFAULT '0',
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media_validators`
--

INSERT INTO `media_validators` (`id`, `model_name`, `field_name`, `field_type`, `max_size`, `min_width`, `min_height`, `resize_value`, `sorting`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`, `created_at`, `updated_at`) VALUES
(2, 'Admin', 'profile', 'image', 5000, 600, 600, '[\"600x600\",\"300x300\",\"50x50\"]', 0, 'active', NULL, NULL, NULL, NULL, '2024-09-09 18:36:15', '2024-09-10 18:50:34'),
(5, 'Album', 'image', 'image', 5120, 600, 600, '[\"600x600\",\"340x340\",\"242x242\",\"50x50\"]', 0, 'active', NULL, NULL, NULL, NULL, '2024-09-09 21:46:00', '2024-09-10 17:29:38'),
(6, 'Content', 'image', 'image', 5000, 636, 610, '[\"636x610\",\"583x559\",\"200x192\"]', 0, 'active', NULL, NULL, NULL, NULL, '2024-09-10 16:41:03', '2024-09-10 23:47:48'),
(9, 'News', 'image', 'image', 5000, 856, 526, '[\"856x526\",\"416x256\",\"110x67\"]', 0, 'active', NULL, NULL, NULL, NULL, '2024-09-14 05:18:50', '2024-09-14 05:18:50'),
(10, 'Video', 'thumbnail', 'image', 500, 400, 400, '[\"300x300\",\"200x200\",\"50x50\"]', 0, 'active', NULL, NULL, NULL, NULL, '2024-09-14 17:45:05', '2024-09-14 17:45:05'),
(11, 'PageSection', 'image', 'image', 500, 64, 64, '[\"64x64\"]', 0, 'active', NULL, NULL, NULL, NULL, '2024-09-14 12:48:48', '2024-09-14 12:48:48'),
(12, 'Events', 'image', 'image', 500, 600, 600, '[\"500x500\",\"300x300\",\"50x50\"]', 0, 'active', NULL, NULL, NULL, NULL, '2024-09-14 18:58:35', '2024-09-14 18:58:35'),
(13, 'PageSeo', 'image', 'image', 500, 600, 600, '[\"600x600\",\"200x200\",\"50x50\"]', 0, 'active', NULL, NULL, NULL, NULL, '2024-09-14 21:50:42', '2024-09-14 21:50:42'),
(14, 'SiteSetting', 'logo_small', 'image', 500, 261, 90, '[\"261x90\"]', 0, 'active', NULL, NULL, NULL, NULL, '2024-09-18 04:14:40', '2024-09-30 06:29:53'),
(15, 'SiteSetting', 'logo', 'image', 500, 300, 90, '[\"300x90\"]', 0, 'active', NULL, NULL, NULL, NULL, '2024-09-18 04:15:23', '2024-09-30 06:30:00'),
(18, 'Service', 'image', 'image', 5000, 1076, 541, '[\"1076x541\"]', 0, 'active', NULL, NULL, NULL, NULL, '2025-06-24 22:13:20', '2025-06-24 22:13:20'),
(19, 'Service', 'image_small', 'image', 5000, 52, 52, '[\"52x52\"]', 0, 'active', NULL, NULL, NULL, NULL, '2025-06-24 22:14:05', '2025-06-24 22:14:05'),
(20, 'Project', 'image', 'image', 5000, 856, 526, '[\"856x526\",\"416x256\"]', 0, 'active', NULL, NULL, NULL, NULL, '2025-07-14 04:02:11', '2025-07-14 04:02:11'),
(21, 'Client', 'image', 'image', 5000, 400, 348, '[\"400x348\"]', 0, 'active', NULL, NULL, NULL, NULL, '2025-07-13 16:45:41', '2025-07-13 16:45:41'),
(22, 'Partnership', 'image', 'image', 5000, 150, 150, '[\"150x150\"]', 0, 'active', NULL, NULL, NULL, NULL, '2025-07-13 23:15:56', '2025-07-13 23:15:56');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` bigint UNSIGNED NOT NULL,
  `parent_id` int DEFAULT NULL,
  `menu_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `module_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `route_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `params` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sorting` int NOT NULL DEFAULT '0',
  `show_dasboard` tinyint NOT NULL DEFAULT '0',
  `show_profile` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `parent_id`, `menu_name`, `module_name`, `icon`, `route_name`, `params`, `sorting`, `show_dasboard`, `show_profile`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Dashboard', NULL, '<i class=\'fas fa-cog spin\'></i>', 'dashboard.index', NULL, 0, 0, 0, NULL, NULL),
(2, NULL, 'Admin', NULL, '<i class=\'fa fa-user\'></i>', 'admin.index', NULL, 25, 0, 0, NULL, '2025-12-27 19:01:56'),
(3, NULL, 'Master Setup', NULL, '<i class=\'fab fa-mastodon\'></i>', NULL, NULL, 2, 0, 0, NULL, NULL),
(4, 3, 'Category', '\\App\\Models\\Category', '<i class=\'fa fa-list text-aqua\'></i>', 'category.index', NULL, 0, 0, 0, NULL, '2024-09-08 18:22:16'),
(13, NULL, 'Profile', NULL, '<i class=\'fa fa-user-o\'></i>', 'profile.index', NULL, 5, 0, 0, NULL, NULL),
(19, NULL, 'System Settings', NULL, '<i class=\'fas fa-cogs\'></i>', NULL, NULL, 25, 0, 0, NULL, '2025-12-17 06:02:07'),
(20, 19, 'User Role', '\\App\\Models\\System\\Role', '<i class=\'fa fa-list text-aqua\'></i>', 'role.index', NULL, 0, 0, 0, NULL, '2024-09-09 19:42:11'),
(21, 19, 'Backend Menu List', NULL, '<i class=\'fa fa-list text-aqua\'></i>', 'menu.index', NULL, 1, 0, 0, NULL, '2024-08-26 16:22:42'),
(23, 19, 'Site Settings', NULL, '<i class=\'fa fa-cog text-aqua\'></i>', 'siteSetting.show', NULL, 3, 0, 1, NULL, '2024-10-01 15:09:43'),
(25, 19, 'Module Create', NULL, '<i class=\'fa fa-folder text-aqua\'></i>', 'module.create', NULL, 5, 0, 0, NULL, NULL),
(27, NULL, 'Log', NULL, '<i class=\'fas fa-align-center\'></i>', NULL, NULL, 26, 0, 0, '2024-07-14 21:20:47', '2024-10-01 21:17:07'),
(28, 27, 'Activity Log', '\\App\\Models\\UserLoginHistory', '<i class=\'fas fa-air-freshener\'></i>', 'activityLog.index', 'sdsd', 27, 0, 0, '2024-07-14 09:21:24', '2024-09-09 07:40:34'),
(46, 3, 'Media Validator', NULL, '<i class=\'fas fa-image\'></i>', 'mediaValidator.index', NULL, 32, 0, 0, '2024-09-09 00:41:49', '2024-09-10 00:50:30'),
(49, 47, 'Robots', NULL, '<i class=\'fas fa-align-left\'></i>', 'robots.index', NULL, 35, 0, 0, '2024-09-09 18:42:45', '2024-09-09 18:42:45'),
(52, 3, 'Help Info', NULL, '<i class=\'fas fa-hands-helping\'></i>', 'helpInfo.index', NULL, 34, 0, 0, '2024-09-18 15:57:28', '2024-09-21 19:35:41'),
(59, 3, 'Service', NULL, '<i class=\'fas fa-server\'></i>', 'service.index', NULL, 10, 0, 0, '2025-06-23 15:03:29', '2025-06-23 15:03:29'),
(68, NULL, 'Proforma Invoices', NULL, '<i class=\'fas fa-file-invoice\'></i>', 'invoice.index', NULL, 16, 0, 0, '2025-08-14 23:06:31', '2025-12-16 12:01:40'),
(74, 3, 'Unit', NULL, '<i class=\'fas fa-balance-scale\'></i>', 'unit.index', NULL, 8, 0, 0, '2025-12-15 18:24:41', '2025-12-15 18:24:41'),
(76, NULL, 'Client', NULL, '<i class=\'fas fa-user-friends\'></i>', 'client.index', NULL, 10, 0, 0, '2025-12-16 14:34:29', '2025-12-16 14:34:29'),
(77, 3, 'District', NULL, '<i class=\'fas fa-archway\'></i>', 'district.index', NULL, 7, 0, 0, '2025-12-16 14:44:25', '2025-12-16 14:44:25'),
(78, 3, 'Area', NULL, '<i class=\'fas fa-arrows-alt\'></i>', 'area.index', NULL, 6, 0, 0, '2025-12-16 14:49:19', '2025-12-16 14:49:19'),
(79, 3, 'Branch', NULL, '<i class=\'fab fa-umbraco\'></i>', 'branch.index', NULL, 5, 0, 0, '2025-12-16 20:59:04', '2025-12-16 20:59:04'),
(80, 3, 'Designation', NULL, '<i class=\'fab fa-slideshare\'></i>', 'designation.index', NULL, 9, 0, 0, '2025-12-17 00:20:28', '2025-12-17 00:20:28'),
(81, NULL, 'Employees', '\\App\\Models\\Employee', '<i class=\'fas fa-user-secret\'></i>', 'employee.index', NULL, 16, 1, 0, '2025-12-17 00:57:02', '2025-12-17 00:57:02'),
(83, 100, 'Chart of Account', NULL, '<i class=\'fas fa-money-check-alt\'></i>', 'account.index', NULL, 6, 0, 0, '2025-12-21 18:50:56', '2025-12-21 18:50:56'),
(84, NULL, 'Suppliers', NULL, '<i class=\'fas fa-layer-group\'></i>', 'supplier.index', NULL, 10, 0, 0, '2025-12-23 02:22:46', '2025-12-23 02:22:46'),
(85, 3, 'FinancialYear', NULL, '<i class=\'fas fa-calendar-alt\'></i>', 'financialYear.index', NULL, 2, 0, 0, '2025-12-23 15:15:02', '2025-12-23 15:15:02'),
(88, 100, 'Voucher', NULL, '<i class=\'fas fa-file-invoice-dollar\'></i>', 'voucher.index', NULL, 10, 0, 0, '2025-12-23 10:29:26', '2025-12-23 10:29:26'),
(90, 100, 'Expense', NULL, '<i class=\'fas fa-money-bill-wave\'></i>', 'expense.index', NULL, 7, 0, 0, '2025-12-25 18:13:03', '2025-12-25 18:13:03'),
(91, NULL, 'Purchase', '\\App\\Models\\Purchase', '<i class=\'fas fa-cart-plus\'></i>', 'purchase.index', NULL, 12, 1, 0, '2025-12-26 09:27:43', '2025-12-26 09:27:43'),
(92, 3, 'Items', NULL, '<i class=\'fas fa-prescription-bottle\'></i>', 'item.index', NULL, 11, 0, 0, '2025-12-26 18:26:56', '2025-12-26 18:26:56'),
(93, NULL, 'Issue', '\\App\\Models\\Issue', '<i class=\'fas fa-network-wired\'></i>', 'issue.index', NULL, 18, 1, 0, '2025-12-27 12:53:28', '2025-12-27 12:53:28'),
(94, NULL, 'Reports', NULL, '<i class=\'fas fa-list-alt\'></i>', NULL, NULL, 21, 0, 0, '2026-01-03 05:10:49', '2026-01-12 22:47:08'),
(95, 94, 'Item Ladger', NULL, '<i class=\'fas fa-store\'></i>', 'report.itemladger', NULL, 0, 0, 0, '2026-01-02 17:12:01', '2026-01-02 17:12:01'),
(96, 94, 'Available Stock', NULL, '<i class=\'fas fa-warehouse\'></i>', 'report.availablestock', NULL, 37, 0, 0, '2026-01-03 22:15:38', '2026-01-03 22:15:38'),
(97, 100, 'Loan Info', NULL, '<i class=\'fas fa-money-check\'></i>', 'loanInfo.index', NULL, 15, 0, 0, '2026-01-03 17:11:20', '2026-01-03 17:11:20'),
(98, 100, 'SalarySheet', NULL, '<i class=\'fas fa-money-check-alt\'></i>', 'salarySheet.index', NULL, 17, 0, 0, '2026-01-04 20:08:54', '2026-01-04 20:08:54'),
(99, 100, 'Payment', '\\App\\Models\\Payment', '<i class=\'fas fa-money-bill-alt\'></i>', 'payment.index', NULL, 10, 1, 0, '2026-01-06 09:26:20', '2026-01-06 09:26:20'),
(100, NULL, 'Accounts', NULL, '<i class=\'fab fa-accusoft\'></i>', NULL, NULL, 19, 0, 0, '2026-01-06 15:37:59', '2026-01-06 15:37:59'),
(101, 94, 'Profit Loss', NULL, '<i class=\'fas fa-th-list\'></i>', 'report.incomestatement', NULL, 3, 0, 0, '2026-01-12 18:30:29', '2026-01-12 18:30:29'),
(102, 94, 'Expense Statement', NULL, '<i class=\'fas fa-list-alt\'></i>', 'report.expensestatement', NULL, 4, 0, 0, '2026-01-13 03:27:02', '2026-01-13 03:27:02'),
(103, 94, 'Cash Book', NULL, '<i class=\'fas fa-book-reader\'></i>', 'report.cashbook', NULL, 5, 0, 0, '2026-01-13 06:16:32', '2026-01-13 06:16:32'),
(104, 94, 'Account Ledger', NULL, '<i class=\'fas fa-balance-scale\'></i>', 'report.ledger', NULL, 5, 0, 0, '2026-01-13 10:32:32', '2026-01-13 10:32:32'),
(105, 94, 'Trial Balance', NULL, '<i class=\'fas fa-adjust\'></i>', 'report.trialbalance', NULL, 6, 0, 0, '2026-01-13 10:33:45', '2026-01-13 10:33:45'),
(106, 94, 'Receivable', NULL, '<i class=\'fas fa-recycle\'></i>', 'report.receivable', NULL, 7, 0, 0, '2026-01-13 10:35:34', '2026-01-13 10:35:34'),
(107, 94, 'Payable', NULL, '<i class=\'fab fa-cc-paypal\'></i>', 'report.payable', NULL, 8, 0, 0, '2026-01-13 10:37:21', '2026-01-13 10:37:21'),
(108, 94, 'Balancesheet', NULL, '<i class=\'fas fa-balance-scale\'></i>', 'report.balancesheet', NULL, 9, 0, 0, '2026-01-13 10:38:25', '2026-01-13 10:38:25'),
(109, NULL, 'Bulk Data Import', NULL, '<i class=\'fas fa-file-import\'></i>', NULL, NULL, 20, 0, 0, '2026-01-18 23:55:16', '2026-01-18 23:57:20'),
(110, 109, 'Employee Import', NULL, '<i class=\'fas fa-file-import\'></i>', 'bulkdataimport.employee', NULL, 0, 0, 0, '2026-01-18 17:56:44', '2026-01-18 17:56:44'),
(111, 3, 'Salary Parameter', NULL, '<i class=\'fas fa-money-check\'></i>', 'salaryParameter.index', NULL, 5, 0, 0, '2026-01-19 17:30:59', '2026-01-19 17:30:59'),
(112, 100, 'Commission', NULL, '<i class=\'fas fa-biohazard\'></i>', 'commission.index', NULL, 4, 0, 0, '2026-01-20 17:23:06', '2026-01-20 17:23:06'),
(114, 109, 'Client Import', NULL, '<i class=\'fas fa-file-import\'></i>', 'bulkdataimport.client', NULL, 2, 0, 0, '2026-01-26 04:11:50', '2026-01-26 04:11:50'),
(115, 3, 'Banks', NULL, '<i class=\'fas fa-piggy-bank\'></i>', 'bank.index', NULL, 5, 0, 0, '2026-02-01 10:08:32', '2026-02-01 10:08:32'),
(116, 117, 'Client Leger', NULL, '<i class=\'fas fa-user-tie\'></i>', 'report.clientledger', NULL, 20, 0, 0, '2026-02-02 10:54:47', '2026-02-02 10:54:47'),
(117, NULL, 'Individual Ledger', NULL, '<i class=\'fas fa-atlas\'></i>', NULL, NULL, 20, 0, 0, '2026-02-03 18:24:02', '2026-02-03 18:27:42'),
(119, 117, 'Supplier', NULL, '<i class=\'fas fa-sticky-note\'></i>', 'report.supplierledger', NULL, 3, 0, 0, '2026-02-04 02:05:03', '2026-02-04 02:05:03'),
(120, 117, 'Employee', NULL, '<i class=\'fas fa-user-tie\'></i>', 'report.employeeledger', NULL, 4, 0, 0, '2026-02-04 04:13:25', '2026-02-04 04:13:25'),
(121, 100, 'FundTransfer', NULL, '<i class=\'fas fa-share-square\'></i>', 'fundTransfer.index', NULL, 20, 0, 0, '2026-02-14 13:30:25', '2026-02-14 13:30:25'),
(122, 100, 'Withdrawl', NULL, '<i class=\'fas fa-bullseye\'></i>', 'withdrawl.index', NULL, 21, 0, 0, '2026-02-14 13:42:43', '2026-02-14 13:42:43'),
(123, 94, 'Funds', NULL, '<i class=\'fas fa-money-check-alt\'></i>', 'report.funds', NULL, 21, 0, 0, '2026-02-21 20:38:33', '2026-02-21 20:38:33'),
(129, NULL, 'Workorder', NULL, '<i class=\'fas fa-list-alt\'></i>', 'workorder.index', NULL, 18, 0, 0, '2026-03-05 04:29:49', '2026-03-05 20:54:34'),
(130, 3, 'Currency', NULL, '<i class=\'fas fa-money-bill\'></i>', 'currency.index', NULL, 10, 0, 0, '2026-03-05 14:58:15', '2026-03-05 14:58:15'),
(131, NULL, 'Challan', NULL, '<i class=\'fas fa-clipboard-list\'></i>', 'challan.index', NULL, 19, 0, 0, '2026-03-25 01:05:37', '2026-03-25 03:21:27'),
(132, 100, 'Agent', 'App\\Models\\Agent', '<i class=\"fas fa-user-tie\"></i>', 'agent.index', NULL, 5, 1, 0, '2026-08-11 04:49:55', '2026-08-11 04:49:55'),
(133, 3, 'Agents', NULL, '<i class=\'fas fa-user-cog\'></i>', 'agent.index', NULL, 38, 0, 0, '2026-08-11 06:29:02', '2026-08-11 06:29:02'),
(134, 117, 'Agent', NULL, '<i class=\'fas fa-sticky-note\'></i>', 'report.agentledger', NULL, 5, 0, 0, '2026-08-11 08:18:50', '2026-08-11 08:18:50'),
(135, 117, 'Agent Ledger', NULL, '<i class=\'fab fa-magento\'></i>', 'report.agentledger', NULL, 39, 0, 0, '2026-08-11 09:27:55', '2026-08-11 09:27:55'),
(136, 3, 'Color', NULL, '<i class=\'fas fa-paint-brush\'></i>', 'color.index', NULL, 0, 0, 0, '2026-08-16 08:20:17', '2026-08-16 08:20:17'),
(137, 3, 'Size', NULL, '<i class=\'fas fa-ankh\'></i>', 'size.index', NULL, 0, 0, 0, '2026-08-16 08:24:46', '2026-08-16 08:24:46');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2020_05_26_071839_create_roles_table', 1),
(6, '2020_06_01_101148_create_site_settings_table', 1),
(7, '2020_06_01_102821_create_contents_table', 1),
(8, '2020_06_02_044523_create_content_files_table', 1),
(9, '2020_06_08_140638_create_sliders_table', 1),
(10, '2020_06_09_051430_create_albums_table', 1),
(11, '2020_06_09_053929_create_photos_table', 1),
(12, '2020_06_09_072128_create_videos_table', 1),
(13, '2020_06_10_120859_create_front_menus_table', 1),
(14, '2021_02_22_050702_create_permissions_table', 1),
(15, '2021_02_22_050735_create_role_permissions_table', 1),
(16, '2021_02_22_062756_create_menus_table', 1),
(17, '2021_02_26_100135_create_news_table', 1),
(18, '2022_06_23_053641_create_admins_table', 1),
(19, '2022_07_16_095130_create_activity_log_table', 1),
(20, '2022_12_11_104109_create_plugins_table', 1),
(21, '2022_12_21_083741_create_event_schedules_table', 1),
(22, '2023_02_08_033828_create_profiles_table', 1),
(23, '2023_05_04_112200_create_slider_details_table', 1),
(24, '2023_05_11_110836_create_user_login_infos_table', 1),
(25, '2023_06_01_055529_create_notices_table', 1),
(26, '2023_06_05_105945_create_categories_table', 1),
(27, '2023_06_05_111140_create_events_table', 1),
(28, '2023_06_06_122755_create_contacts_table', 1),
(29, '2023_06_07_041032_create_faqs_table', 1),
(30, '2023_06_14_045514_create_countries_table', 1),
(31, '2024_01_01_102809_create_employees_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `notice_date` date NOT NULL,
  `notice_end` date DEFAULT NULL,
  `type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sorting` int NOT NULL DEFAULT '0',
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`id`, `title`, `slug`, `category_id`, `notice_date`, `notice_end`, `type`, `file`, `description`, `sorting`, `status`, `created_at`, `updated_at`, `created_by`, `created_ip`, `updated_by`, `updated_ip`) VALUES
(16, 'Scheduled System Maintenance', 'scheduled-system-maintenance', 21, '2024-10-07', '2024-10-09', 'content', NULL, '<p><strong>Date:</strong>&nbsp;[Insert Date]<br />\r\n<strong>Time:</strong>&nbsp;[Insert Time]<br />\r\n<strong>Duration:</strong>&nbsp;Approximately [Insert Duration]</p>\r\n\r\n<p><strong>Attention Team,</strong></p>\r\n\r\n<p>Please be informed that we will be performing scheduled system maintenance on [insert date] from [insert start time] to [insert end time]. During this period, access to our software applications and internal systems may be limited or temporarily unavailable.</p>\r\n\r\n<p>This maintenance is essential for improving system performance and ensuring the security and reliability of our services. We recommend that you save your work and log out of relevant systems before the maintenance window begins.</p>\r\n\r\n<p>Thank you for your understanding as we work to enhance our software capabilities.</p>\r\n\r\n<p>Best regards,<br />\r\n[Your Name]<br />\r\n[Your Position]<br />\r\n[Company Name]</p>', 1, 'active', '2024-10-08 03:40:05', '2024-10-08 03:40:05', 'Nogor Solutions Limited', '221.120.98.178', NULL, NULL),
(17, 'HR Policy Update', 'hr-policy-update', 22, '2024-10-08', '2024-10-09', 'file', 'upload/notice/241008094128-9737events.pdf', NULL, 2, 'active', '2024-10-08 03:41:28', '2024-10-08 03:41:28', 'Nogor Solutions Limited', '221.120.98.178', NULL, NULL),
(18, 'fgfdgfddsfds', 'fgfdgfd', 21, '2025-03-04', NULL, 'file', 'upload/notice/250303012620-4431pastPresident.pdf', NULL, 3, 'active', '2025-03-03 07:25:53', '2025-03-03 07:26:20', 'Nogor Solutions Limited', '221.120.98.178', 'Nogor Solutions Limited', '221.120.98.178');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint UNSIGNED NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `created_at`) VALUES
(2, 'leo@nogorsolutions.com', '$2y$10$R1x3zFolj4JjoxQbigah1Og/O.S5lulQ7r5G57wMT8BGb.FSRCy5q', '2025-01-02 06:32:03'),
(3, 'nsl@gmail.com', '$2y$10$Lu5xNLN.FbY9VuDZvGQLKelx4QsRQwedgJyV7Im5Mf8lffW.7C7CC', '2025-05-06 05:54:24'),
(7, 'mehedihasan33x@gmail.com', '$2y$10$j1/2d5yes2IxIogbMXIku.7QzGbFwFv5uIOneRej7B3CVGwSGRWny', '2025-05-06 06:06:50'),
(8, 'masudul.islam@beeonline.com.bd', '$2y$10$.UWQlL3ud1qSSyyabdBtGevnwFRdsqDjXQ6tBKJCe9EQea0dawq12', '2026-01-29 06:14:49');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint UNSIGNED NOT NULL,
  `payslipno` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_type` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` int DEFAULT NULL,
  `supplier_id` int DEFAULT NULL,
  `uplink_provider_id` int DEFAULT NULL,
  `office_expense` tinyint DEFAULT NULL,
  `employee_id` int DEFAULT NULL,
  `payment_date` date NOT NULL,
  `discount` decimal(8,2) NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `fund_account_id` int NOT NULL,
  `payment_method` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mbanking_type` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chequeno` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_id` int DEFAULT NULL,
  `branch` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accountno` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trxid` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` date DEFAULT NULL,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `payslipno`, `payment_type`, `client_id`, `supplier_id`, `uplink_provider_id`, `office_expense`, `employee_id`, `payment_date`, `discount`, `amount`, `fund_account_id`, `payment_method`, `mbanking_type`, `chequeno`, `bank_id`, `branch`, `account_name`, `accountno`, `trxid`, `created_at`, `updated_at`, `deleted_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, '111', 'Receive', 1, NULL, NULL, 0, NULL, '2026-03-29', 0.00, 239750.00, 0, 'Bank', NULL, NULL, NULL, 'Culpa in quia rerum', 'Ebony French', '48356', NULL, '2026-03-29 13:11:46', '2026-03-29 13:11:46', NULL, 'active', 1, 1, 1, 1),
(2, '112', 'Pay', NULL, NULL, NULL, 1, NULL, '2026-07-11', 0.00, 5000.00, 0, 'Bank', NULL, NULL, NULL, 'Qui ipsam laboriosam', 'Kevyn Ellison', '61663', NULL, '2026-07-11 12:11:03', '2026-07-11 12:11:03', NULL, 'active', 1, 1, 1, 1),
(3, '113', 'Receive', 4, NULL, NULL, NULL, NULL, '2026-07-29', 0.00, 3000.00, 22, 'Bank', NULL, NULL, NULL, 'Et consequat Laudan', 'Patricia Manning', '915', NULL, '2026-07-29 13:15:44', '2026-07-29 13:15:44', NULL, 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `payment_details`
--

CREATE TABLE `payment_details` (
  `id` bigint UNSIGNED NOT NULL,
  `payment_id` int NOT NULL,
  `reference_type` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` int NOT NULL,
  `account_id` int DEFAULT NULL,
  `amount` decimal(8,2) NOT NULL,
  `is_closed` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` date DEFAULT NULL,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_details`
--

INSERT INTO `payment_details` (`id`, `payment_id`, `reference_type`, `reference_id`, `account_id`, `amount`, `is_closed`, `created_at`, `updated_at`, `deleted_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 1, 'Invoice', 1, 26, 239750.00, 1, '2026-03-29 13:11:46', '2026-03-29 13:11:46', NULL, 'active', 1, 1, 1, 1),
(2, 2, 'ExpenseDetail', 2, 50, 5000.00, 1, '2026-07-11 12:11:03', '2026-07-11 12:11:03', NULL, 'active', 1, 1, 1, 1),
(3, 3, 'Invoice', 3, 26, 3000.00, 1, '2026-07-29 13:15:44', '2026-07-29 13:15:44', NULL, 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `route` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `route`, `parent_id`) VALUES
(1, 'ActivityLogController', NULL, NULL),
(2, 'sitemapData', 'sitemapData.sitemapData', 1),
(3, 'index', 'activityLog.index', 1),
(4, 'show', 'activityLog.show', 1),
(5, 'allRead', 'activityLog.allRead', 1),
(6, 'destroy', 'activityLog.destroy', 1),
(7, 'sitemap', 'sitemap.sitemap', 1),
(8, 'DashboardController', NULL, NULL),
(9, 'index', 'dashboard.index', 8),
(26, 'destroy', 'album.destroy', 19),
(75, 'CategoryController', NULL, NULL),
(76, 'index', 'category.index', 75),
(77, 'create', 'category.create', 75),
(78, 'store', 'category.store', 75),
(79, 'show', 'category.show', 75),
(80, 'edit', 'category.edit', 75),
(81, 'update', 'category.update', 75),
(82, 'destroy', 'category.destroy', 75),
(115, 'ModuleController', NULL, NULL),
(116, 'create', 'module.create', 115),
(117, 'RoleController', NULL, NULL),
(118, 'index', 'role.index', 117),
(119, 'create', 'role.create', 117),
(120, 'store', 'role.store', 117),
(121, 'show', 'role.show', 117),
(122, 'edit', 'role.edit', 117),
(123, 'update', 'role.update', 117),
(124, 'destroy', 'role.destroy', 117),
(125, 'MenuController', NULL, NULL),
(126, 'index', 'menu.index', 125),
(127, 'create', 'menu.create', 125),
(128, 'store', 'menu.store', 125),
(129, 'show', 'menu.show', 125),
(130, 'edit', 'menu.edit', 125),
(131, 'update', 'menu.update', 125),
(132, 'destroy', 'menu.destroy', 125),
(133, 'SiteSettingController', NULL, NULL),
(134, 'index', 'siteSetting.index', 133),
(135, 'create', 'siteSetting.create', 133),
(136, 'store', 'siteSetting.store', 133),
(137, 'show', 'siteSetting.show', 133),
(138, 'edit', 'siteSetting.edit', 133),
(139, 'update', 'siteSetting.update', 133),
(140, 'destroy', 'siteSetting.destroy', 133),
(141, 'AdminController', NULL, NULL),
(142, 'index', 'admin.index', 141),
(143, 'create', 'admin.create', 141),
(144, 'store', 'admin.store', 141),
(145, 'show', 'admin.show', 141),
(146, 'edit', 'admin.edit', 141),
(147, 'update', 'admin.update', 141),
(148, 'destroy', 'admin.destroy', 141),
(165, 'profileDetails', 'profile.profileDetails', 141),
(183, 'ImageResizerController', NULL, NULL),
(184, 'index', 'imageResizer.index', 183),
(185, 'create', 'imageResizer.create', 183),
(186, 'store', 'imageResizer.store', 183),
(187, 'show', 'imageResizer.show', 183),
(188, 'edit', 'imageResizer.edit', 183),
(189, 'update', 'imageResizer.update', 183),
(190, 'destroy', 'imageResizer.destroy', 183),
(191, 'MediaValidatorController', NULL, NULL),
(192, 'index', 'mediaValidator.index', 191),
(193, 'create', 'mediaValidator.create', 191),
(194, 'store', 'mediaValidator.store', 191),
(195, 'show', 'mediaValidator.show', 191),
(196, 'edit', 'mediaValidator.edit', 191),
(197, 'update', 'mediaValidator.update', 191),
(198, 'destroy', 'mediaValidator.destroy', 191),
(199, 'restore', 'activityLog.restore', 1),
(200, 'HelpInfoController', NULL, NULL),
(201, 'index', 'helpInfo.index', 200),
(202, 'create', 'helpInfo.create', 200),
(203, 'store', 'helpInfo.store', 200),
(204, 'show', 'helpInfo.show', 200),
(205, 'edit', 'helpInfo.edit', 200),
(206, 'update', 'helpInfo.update', 200),
(207, 'destroy', 'helpInfo.destroy', 200),
(208, 'updateProfile', 'profile.updateProfile', 141),
(209, 'ThemeController', NULL, NULL),
(210, 'index', 'theme.index', 209),
(211, 'create', 'theme.create', 209),
(212, 'store', 'theme.store', 209),
(213, 'show', 'theme.show', 209),
(214, 'edit', 'theme.edit', 209),
(215, 'update', 'theme.update', 209),
(216, 'destroy', 'theme.destroy', 209),
(217, 'ServiceController', NULL, NULL),
(218, 'index', 'service.index', 217),
(219, 'create', 'service.create', 217),
(220, 'store', 'service.store', 217),
(221, 'show', 'service.show', 217),
(222, 'edit', 'service.edit', 217),
(223, 'update', 'service.update', 217),
(224, 'destroy', 'service.destroy', 217),
(225, 'ClientController', NULL, NULL),
(226, 'index', 'client.index', 225),
(227, 'create', 'client.create', 225),
(228, 'store', 'client.store', 225),
(229, 'show', 'client.show', 225),
(230, 'edit', 'client.edit', 225),
(231, 'update', 'client.update', 225),
(232, 'destroy', 'client.destroy', 225),
(281, 'PackageController', NULL, NULL),
(282, 'index', 'package.index', 281),
(283, 'create', 'package.create', 281),
(284, 'store', 'package.store', 281),
(285, 'show', 'package.show', 281),
(286, 'edit', 'package.edit', 281),
(287, 'update', 'package.update', 281),
(288, 'destroy', 'package.destroy', 281),
(289, 'InvoiceController', NULL, NULL),
(290, 'index', 'invoice.index', 289),
(291, 'create', 'invoice.create', 289),
(292, 'store', 'invoice.store', 289),
(293, 'show', 'invoice.show', 289),
(294, 'edit', 'invoice.edit', 289),
(295, 'update', 'invoice.update', 289),
(296, 'destroy', 'invoice.destroy', 289),
(327, 'bill', 'invoice.bill', 289),
(328, 'moneyrecpit', 'invoice.moneyrecpit', 289),
(329, 'moneyrecipt', 'invoice.moneyrecipt', 289),
(330, 'moneyreceipt', 'invoice.moneyreceipt', 289),
(331, 'UnitController', NULL, NULL),
(332, 'index', 'unit.index', 331),
(333, 'create', 'unit.create', 331),
(334, 'store', 'unit.store', 331),
(335, 'show', 'unit.show', 331),
(336, 'edit', 'unit.edit', 331),
(337, 'update', 'unit.update', 331),
(338, 'destroy', 'unit.destroy', 331),
(339, 'DistrictController', NULL, NULL),
(340, 'index', 'district.index', 339),
(341, 'create', 'district.create', 339),
(342, 'store', 'district.store', 339),
(343, 'show', 'district.show', 339),
(344, 'edit', 'district.edit', 339),
(345, 'update', 'district.update', 339),
(346, 'destroy', 'district.destroy', 339),
(347, 'AreaController', NULL, NULL),
(348, 'index', 'area.index', 347),
(349, 'create', 'area.create', 347),
(350, 'store', 'area.store', 347),
(351, 'show', 'area.show', 347),
(352, 'edit', 'area.edit', 347),
(353, 'update', 'area.update', 347),
(354, 'destroy', 'area.destroy', 347),
(355, 'BranchController', NULL, NULL),
(356, 'index', 'branch.index', 355),
(357, 'create', 'branch.create', 355),
(358, 'store', 'branch.store', 355),
(359, 'show', 'branch.show', 355),
(360, 'edit', 'branch.edit', 355),
(361, 'update', 'branch.update', 355),
(362, 'destroy', 'branch.destroy', 355),
(363, 'DesignationController', NULL, NULL),
(364, 'index', 'designation.index', 363),
(365, 'create', 'designation.create', 363),
(366, 'store', 'designation.store', 363),
(367, 'show', 'designation.show', 363),
(368, 'edit', 'designation.edit', 363),
(369, 'update', 'designation.update', 363),
(370, 'destroy', 'designation.destroy', 363),
(371, 'EmployeeController', NULL, NULL),
(372, 'index', 'employee.index', 371),
(373, 'create', 'employee.create', 371),
(374, 'store', 'employee.store', 371),
(375, 'show', 'employee.show', 371),
(376, 'edit', 'employee.edit', 371),
(377, 'update', 'employee.update', 371),
(378, 'destroy', 'employee.destroy', 371),
(387, 'AccountController', NULL, NULL),
(388, 'index', 'account.index', 387),
(389, 'create', 'account.create', 387),
(390, 'store', 'account.store', 387),
(391, 'show', 'account.show', 387),
(392, 'edit', 'account.edit', 387),
(393, 'update', 'account.update', 387),
(394, 'destroy', 'account.destroy', 387),
(395, 'SupplierController', NULL, NULL),
(396, 'index', 'supplier.index', 395),
(397, 'create', 'supplier.create', 395),
(398, 'store', 'supplier.store', 395),
(399, 'show', 'supplier.show', 395),
(400, 'edit', 'supplier.edit', 395),
(401, 'update', 'supplier.update', 395),
(402, 'destroy', 'supplier.destroy', 395),
(403, 'FinancialYearController', NULL, NULL),
(404, 'index', 'financialYear.index', 403),
(405, 'create', 'financialYear.create', 403),
(406, 'store', 'financialYear.store', 403),
(407, 'show', 'financialYear.show', 403),
(408, 'edit', 'financialYear.edit', 403),
(409, 'update', 'financialYear.update', 403),
(410, 'destroy', 'financialYear.destroy', 403),
(411, 'VoucherController', NULL, NULL),
(412, 'index', 'voucher.index', 411),
(413, 'create', 'voucher.create', 411),
(414, 'store', 'voucher.store', 411),
(415, 'show', 'voucher.show', 411),
(416, 'edit', 'voucher.edit', 411),
(417, 'update', 'voucher.update', 411),
(418, 'destroy', 'voucher.destroy', 411),
(427, 'ExpenseController', NULL, NULL),
(428, 'index', 'expense.index', 427),
(429, 'create', 'expense.create', 427),
(430, 'store', 'expense.store', 427),
(431, 'show', 'expense.show', 427),
(432, 'edit', 'expense.edit', 427),
(433, 'update', 'expense.update', 427),
(434, 'destroy', 'expense.destroy', 427),
(435, 'PurchaseController', NULL, NULL),
(436, 'index', 'purchase.index', 435),
(437, 'create', 'purchase.create', 435),
(438, 'store', 'purchase.store', 435),
(439, 'show', 'purchase.show', 435),
(440, 'edit', 'purchase.edit', 435),
(441, 'update', 'purchase.update', 435),
(442, 'destroy', 'purchase.destroy', 435),
(443, 'ItemController', NULL, NULL),
(444, 'index', 'item.index', 443),
(445, 'create', 'item.create', 443),
(446, 'store', 'item.store', 443),
(447, 'show', 'item.show', 443),
(448, 'edit', 'item.edit', 443),
(449, 'update', 'item.update', 443),
(450, 'destroy', 'item.destroy', 443),
(451, 'IssueController', NULL, NULL),
(452, 'index', 'issue.index', 451),
(453, 'create', 'issue.create', 451),
(454, 'store', 'issue.store', 451),
(455, 'show', 'issue.show', 451),
(456, 'edit', 'issue.edit', 451),
(457, 'update', 'issue.update', 451),
(458, 'destroy', 'issue.destroy', 451),
(459, 'ReportController', NULL, NULL),
(461, 'itemLadger', 'report.itemladger', 459),
(462, 'availableStock', 'report.availablestock', 459),
(463, 'LoanInfoController', NULL, NULL),
(464, 'index', 'loanInfo.index', 463),
(465, 'create', 'loanInfo.create', 463),
(466, 'store', 'loanInfo.store', 463),
(467, 'show', 'loanInfo.show', 463),
(468, 'edit', 'loanInfo.edit', 463),
(469, 'update', 'loanInfo.update', 463),
(470, 'destroy', 'loanInfo.destroy', 463),
(471, 'SalarySheetController', NULL, NULL),
(472, 'index', 'salarySheet.index', 471),
(473, 'create', 'salarySheet.create', 471),
(474, 'store', 'salarySheet.store', 471),
(475, 'show', 'salarySheet.show', 471),
(476, 'edit', 'salarySheet.edit', 471),
(477, 'update', 'salarySheet.update', 471),
(478, 'destroy', 'salarySheet.destroy', 471),
(479, 'approved', 'salarySheet.approved', 471),
(480, 'paynow', 'salarySheet.paynow', 471),
(481, 'PaymentController', NULL, NULL),
(482, 'index', 'payment.index', 481),
(483, 'create', 'payment.create', 481),
(484, 'store', 'payment.store', 481),
(485, 'show', 'payment.show', 481),
(486, 'edit', 'payment.edit', 481),
(487, 'update', 'payment.update', 481),
(488, 'destroy', 'payment.destroy', 481),
(489, 'approval', 'expense.approved', 427),
(490, 'approved', 'expense.approved', 427),
(491, 'approved', 'loanInfo.approved', 463),
(492, 'incomestatement', 'report.incomestatement', 459),
(493, 'expensestatement', 'report.expensestatement', 459),
(494, 'cashbook', 'report.cashbook', 459),
(495, 'ledger', 'report.ledger', 459),
(496, 'trialbalance', 'report.trialbalance', 459),
(497, 'receivable', 'report.receivable', 459),
(498, 'payable', 'report.payable', 459),
(499, 'balancesheet', 'report.balancesheet', 459),
(500, 'empimport', 'employee.empimport', 371),
(501, 'SalaryParameterController', NULL, NULL),
(502, 'index', 'salaryParameter.index', 501),
(503, 'create', 'salaryParameter.create', 501),
(504, 'store', 'salaryParameter.store', 501),
(505, 'show', 'salaryParameter.show', 501),
(506, 'edit', 'salaryParameter.edit', 501),
(507, 'update', 'salaryParameter.update', 501),
(508, 'destroy', 'salaryParameter.destroy', 501),
(509, 'CommissionController', NULL, NULL),
(510, 'index', 'commission.index', 509),
(511, 'create', 'commission.create', 509),
(512, 'store', 'commission.store', 509),
(513, 'show', 'commission.show', 509),
(514, 'edit', 'commission.edit', 509),
(515, 'update', 'commission.update', 509),
(516, 'destroy', 'commission.destroy', 509),
(518, 'AllInvoiceController', NULL, NULL),
(520, 'index', 'allinvoice.index', 518),
(521, 'BulkDataImportController', NULL, NULL),
(522, 'employee', 'bulkdataimport.employee', 521),
(523, 'client', 'bulkdataimport.client', 521),
(524, 'empimport', 'bulkdataimport.empimport', 521),
(525, 'clientimport', 'bulkdataimport.clientimport', 521),
(526, 'approved', 'commission.approved', 509),
(527, 'approvalcancel', 'commission.approvalcancel', 509),
(528, 'BankController', NULL, NULL),
(529, 'index', 'bank.index', 528),
(530, 'create', 'bank.create', 528),
(531, 'store', 'bank.store', 528),
(532, 'show', 'bank.show', 528),
(533, 'edit', 'bank.edit', 528),
(534, 'update', 'bank.update', 528),
(535, 'destroy', 'bank.destroy', 528),
(536, 'clientledger', 'report.clientledger', 459),
(537, 'approvalcancel', 'expense.approvalcancel', 427),
(538, 'uplinkproviderledger', 'report.uplinkproviderledger', 459),
(539, 'supplierledger', 'report.supplierledger', 459),
(540, 'employeeledger', 'report.employeeledger', 459),
(541, 'approvalcancel', 'salarySheet.approvalcancel', 471),
(542, 'approvalcancel', 'loanInfo.approvalcancel', 463),
(543, 'FundTransferController', NULL, NULL),
(544, 'index', 'fundTransfer.index', 543),
(545, 'create', 'fundTransfer.create', 543),
(546, 'store', 'fundTransfer.store', 543),
(547, 'show', 'fundTransfer.show', 543),
(548, 'edit', 'fundTransfer.edit', 543),
(549, 'update', 'fundTransfer.update', 543),
(550, 'destroy', 'fundTransfer.destroy', 543),
(551, 'WithdrawlController', NULL, NULL),
(552, 'index', 'withdrawl.index', 551),
(553, 'create', 'withdrawl.create', 551),
(554, 'store', 'withdrawl.store', 551),
(555, 'show', 'withdrawl.show', 551),
(556, 'edit', 'withdrawl.edit', 551),
(557, 'update', 'withdrawl.update', 551),
(558, 'destroy', 'withdrawl.destroy', 551),
(559, 'funds', 'report.funds', 459),
(560, 'actions', 'siteSetting.actions', 133),
(561, 'OrganizationController', NULL, NULL),
(562, 'expired', 'organization.expired', 561),
(563, 'WorkOrderController', NULL, NULL),
(564, 'index', 'workorder.index', 563),
(565, 'create', 'workorder.create', 563),
(566, 'store', 'workorder.store', 563),
(567, 'show', 'workorder.show', 563),
(568, 'edit', 'workorder.edit', 563),
(569, 'update', 'workorder.update', 563),
(570, 'destroy', 'workorder.destroy', 563),
(571, 'ChalanController', NULL, NULL),
(572, 'index', 'chalan.index', 571),
(573, 'create', 'chalan.create', 571),
(574, 'store', 'chalan.store', 571),
(575, 'show', 'chalan.show', 571),
(576, 'edit', 'chalan.edit', 571),
(577, 'update', 'chalan.update', 571),
(578, 'destroy', 'chalan.destroy', 571),
(579, 'CurrencyController', NULL, NULL),
(580, 'index', 'currency.index', 579),
(581, 'create', 'currency.create', 579),
(582, 'store', 'currency.store', 579),
(583, 'show', 'currency.show', 579),
(584, 'edit', 'currency.edit', 579),
(585, 'update', 'currency.update', 579),
(586, 'destroy', 'currency.destroy', 579),
(587, 'ChallanController', NULL, NULL),
(588, 'index', 'challan.index', 587),
(589, 'create', 'challan.create', 587),
(590, 'store', 'challan.store', 587),
(591, 'show', 'challan.show', 587),
(592, 'edit', 'challan.edit', 587),
(593, 'update', 'challan.update', 587),
(594, 'destroy', 'challan.destroy', 587),
(595, 'RobotController', NULL, NULL),
(596, 'index', 'robots.index', 595),
(597, 'getfilecontent', 'robots.getfilecontent', 595),
(598, 'savefilecontent', 'robots.savefilecontent', 595),
(599, 'XMLController', NULL, NULL),
(600, 'index', 'searchData.index', 599),
(601, 'generateXmlFile', 'searchData.generateXmlFile', 599),
(602, 'getXmlFile', 'searchData.getXmlFile', 599),
(603, 'getBrokenUrl', 'searchData.getBrokenUrl', 599),
(604, 'AgentController', NULL, NULL),
(605, 'index', 'agent.index', 604),
(606, 'create', 'agent.create', 604),
(607, 'store', 'agent.store', 604),
(608, 'show', 'agent.show', 604),
(609, 'edit', 'agent.edit', 604),
(610, 'update', 'agent.update', 604),
(611, 'destroy', 'agent.destroy', 604),
(612, 'agentledger', 'report.agentledger', 459),
(613, 'ColorController', NULL, NULL),
(614, 'index', 'color.index', 613),
(615, 'create', 'color.create', 613),
(616, 'store', 'color.store', 613),
(617, 'show', 'color.show', 613),
(618, 'edit', 'color.edit', 613),
(619, 'update', 'color.update', 613),
(620, 'destroy', 'color.destroy', 613),
(621, 'SizeController', NULL, NULL),
(622, 'index', 'size.index', 621),
(623, 'create', 'size.create', 621),
(624, 'store', 'size.store', 621),
(625, 'show', 'size.show', 621),
(626, 'edit', 'size.edit', 621),
(627, 'update', 'size.update', 621),
(628, 'destroy', 'size.destroy', 621);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` bigint UNSIGNED NOT NULL,
  `invoiceno` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_date` date NOT NULL,
  `supplier_id` int NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `is_closed` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_details`
--

CREATE TABLE `purchase_details` (
  `id` bigint UNSIGNED NOT NULL,
  `purchase_id` int NOT NULL,
  `category_id` int NOT NULL,
  `item_id` int NOT NULL,
  `color_id` int DEFAULT NULL,
  `size_id` int DEFAULT NULL,
  `qty` int NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `total_amount` decimal(8,2) NOT NULL,
  `unit_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'active', '2024-06-30 15:14:27', '2024-06-30 15:14:27'),
(2, 'System Admin', 'active', '2024-07-01 15:14:27', '2024-07-01 15:14:27'),
(8, 'Owner', 'active', '2026-07-15 07:48:00', '2026-07-15 07:48:00'),
(9, 'Manager', 'active', '2026-07-15 07:52:52', '2026-07-15 07:52:52'),
(10, 'Accounts', 'active', '2026-07-15 07:55:12', '2026-07-15 07:55:12'),
(11, 'Operator', 'active', '2026-07-15 08:00:17', '2026-07-15 08:00:17');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` bigint UNSIGNED NOT NULL,
  `permission_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(2, 1),
(1, 2),
(2, 2),
(8, 2),
(1, 3),
(2, 3),
(8, 3),
(1, 4),
(2, 4),
(8, 4),
(1, 5),
(2, 5),
(8, 5),
(1, 6),
(2, 6),
(8, 6),
(1, 7),
(2, 7),
(8, 7),
(1, 8),
(2, 8),
(1, 9),
(2, 9),
(8, 9),
(9, 9),
(10, 9),
(11, 9),
(1, 26),
(2, 26),
(1, 75),
(2, 75),
(1, 76),
(2, 76),
(8, 76),
(11, 76),
(1, 77),
(2, 77),
(8, 77),
(1, 78),
(2, 78),
(8, 78),
(1, 79),
(2, 79),
(8, 79),
(11, 79),
(1, 80),
(2, 80),
(8, 80),
(1, 81),
(2, 81),
(8, 81),
(1, 82),
(2, 82),
(8, 82),
(1, 115),
(2, 115),
(1, 116),
(2, 116),
(1, 117),
(2, 117),
(1, 118),
(2, 118),
(1, 119),
(2, 119),
(1, 120),
(2, 120),
(1, 121),
(2, 121),
(1, 122),
(2, 122),
(1, 123),
(2, 123),
(1, 124),
(2, 124),
(1, 125),
(2, 125),
(1, 126),
(2, 126),
(8, 126),
(1, 127),
(2, 127),
(8, 127),
(1, 128),
(2, 128),
(8, 128),
(1, 129),
(2, 129),
(8, 129),
(1, 130),
(2, 130),
(8, 130),
(1, 131),
(2, 131),
(8, 131),
(1, 132),
(2, 132),
(8, 132),
(1, 133),
(2, 133),
(1, 134),
(2, 134),
(8, 134),
(1, 135),
(2, 135),
(8, 135),
(1, 136),
(2, 136),
(8, 136),
(1, 137),
(2, 137),
(8, 137),
(1, 138),
(2, 138),
(8, 138),
(1, 139),
(2, 139),
(8, 139),
(1, 140),
(2, 140),
(8, 140),
(1, 141),
(2, 141),
(1, 142),
(2, 142),
(8, 142),
(1, 143),
(2, 143),
(1, 144),
(2, 144),
(1, 145),
(2, 145),
(8, 145),
(11, 145),
(1, 146),
(2, 146),
(1, 147),
(2, 147),
(8, 147),
(1, 148),
(2, 148),
(8, 150),
(8, 151),
(8, 152),
(8, 153),
(8, 154),
(8, 155),
(8, 156),
(8, 158),
(8, 159),
(8, 160),
(8, 161),
(8, 162),
(8, 163),
(8, 164),
(1, 165),
(2, 165),
(8, 165),
(11, 165),
(8, 167),
(8, 168),
(8, 169),
(8, 170),
(8, 171),
(8, 172),
(8, 173),
(8, 175),
(8, 176),
(8, 177),
(8, 179),
(8, 180),
(8, 181),
(8, 182),
(1, 183),
(2, 183),
(1, 184),
(2, 184),
(1, 185),
(2, 185),
(1, 186),
(2, 186),
(1, 187),
(2, 187),
(1, 188),
(2, 188),
(1, 189),
(2, 189),
(1, 190),
(2, 190),
(1, 191),
(2, 191),
(1, 192),
(2, 192),
(1, 193),
(2, 193),
(1, 194),
(2, 194),
(1, 195),
(2, 195),
(1, 196),
(2, 196),
(1, 197),
(2, 197),
(1, 198),
(2, 198),
(1, 199),
(2, 199),
(8, 199),
(1, 200),
(2, 200),
(1, 201),
(2, 201),
(1, 202),
(2, 202),
(1, 203),
(2, 203),
(1, 204),
(2, 204),
(1, 205),
(2, 205),
(1, 206),
(2, 206),
(1, 207),
(2, 207),
(1, 208),
(2, 208),
(8, 208),
(11, 208),
(1, 209),
(2, 209),
(1, 210),
(2, 210),
(1, 211),
(2, 211),
(1, 212),
(2, 212),
(1, 213),
(2, 213),
(1, 214),
(2, 214),
(1, 215),
(2, 215),
(1, 216),
(2, 216),
(1, 217),
(2, 217),
(1, 218),
(2, 218),
(8, 218),
(1, 219),
(2, 219),
(8, 219),
(1, 220),
(2, 220),
(8, 220),
(1, 221),
(2, 221),
(8, 221),
(1, 222),
(2, 222),
(8, 222),
(1, 223),
(2, 223),
(8, 223),
(1, 224),
(2, 224),
(8, 224),
(1, 225),
(2, 225),
(1, 226),
(2, 226),
(8, 226),
(9, 226),
(11, 226),
(1, 227),
(2, 227),
(8, 227),
(9, 227),
(11, 227),
(1, 228),
(2, 228),
(8, 228),
(9, 228),
(11, 228),
(1, 229),
(2, 229),
(8, 229),
(9, 229),
(11, 229),
(1, 230),
(2, 230),
(8, 230),
(9, 230),
(11, 230),
(1, 231),
(2, 231),
(8, 231),
(9, 231),
(11, 231),
(1, 232),
(2, 232),
(8, 232),
(9, 232),
(1, 281),
(2, 281),
(1, 282),
(2, 282),
(8, 282),
(1, 283),
(2, 283),
(8, 283),
(1, 284),
(2, 284),
(8, 284),
(1, 285),
(2, 285),
(8, 285),
(1, 286),
(2, 286),
(8, 286),
(1, 287),
(2, 287),
(8, 287),
(1, 288),
(2, 288),
(8, 288),
(1, 289),
(2, 289),
(1, 290),
(2, 290),
(8, 290),
(9, 290),
(10, 290),
(11, 290),
(1, 291),
(2, 291),
(8, 291),
(9, 291),
(10, 291),
(11, 291),
(1, 292),
(2, 292),
(8, 292),
(9, 292),
(10, 292),
(11, 292),
(1, 293),
(2, 293),
(8, 293),
(9, 293),
(10, 293),
(11, 293),
(1, 294),
(2, 294),
(8, 294),
(9, 294),
(10, 294),
(11, 294),
(1, 295),
(2, 295),
(8, 295),
(9, 295),
(10, 295),
(11, 295),
(1, 296),
(2, 296),
(8, 296),
(9, 296),
(10, 296),
(1, 327),
(2, 327),
(8, 327),
(9, 327),
(10, 327),
(11, 327),
(1, 328),
(2, 328),
(8, 328),
(9, 328),
(10, 328),
(11, 328),
(1, 329),
(2, 329),
(8, 329),
(9, 329),
(10, 329),
(11, 329),
(1, 330),
(2, 330),
(8, 330),
(9, 330),
(10, 330),
(11, 330),
(1, 331),
(2, 331),
(1, 332),
(2, 332),
(8, 332),
(11, 332),
(1, 333),
(2, 333),
(8, 333),
(1, 334),
(2, 334),
(8, 334),
(1, 335),
(2, 335),
(8, 335),
(11, 335),
(1, 336),
(2, 336),
(8, 336),
(1, 337),
(2, 337),
(8, 337),
(1, 338),
(2, 338),
(8, 338),
(1, 339),
(2, 339),
(1, 340),
(2, 340),
(8, 340),
(11, 340),
(1, 341),
(2, 341),
(8, 341),
(1, 342),
(2, 342),
(8, 342),
(1, 343),
(2, 343),
(8, 343),
(11, 343),
(1, 344),
(2, 344),
(8, 344),
(1, 345),
(2, 345),
(8, 345),
(1, 346),
(2, 346),
(8, 346),
(1, 347),
(2, 347),
(1, 348),
(2, 348),
(8, 348),
(11, 348),
(1, 349),
(2, 349),
(8, 349),
(1, 350),
(2, 350),
(8, 350),
(1, 351),
(2, 351),
(8, 351),
(11, 351),
(1, 352),
(2, 352),
(8, 352),
(1, 353),
(2, 353),
(8, 353),
(1, 354),
(2, 354),
(8, 354),
(1, 355),
(2, 355),
(1, 356),
(2, 356),
(8, 356),
(11, 356),
(1, 357),
(2, 357),
(8, 357),
(1, 358),
(2, 358),
(8, 358),
(1, 359),
(2, 359),
(8, 359),
(11, 359),
(1, 360),
(2, 360),
(8, 360),
(1, 361),
(2, 361),
(8, 361),
(1, 362),
(2, 362),
(8, 362),
(1, 363),
(2, 363),
(1, 364),
(2, 364),
(8, 364),
(11, 364),
(1, 365),
(2, 365),
(8, 365),
(1, 366),
(2, 366),
(8, 366),
(1, 367),
(2, 367),
(8, 367),
(11, 367),
(1, 368),
(2, 368),
(8, 368),
(1, 369),
(2, 369),
(8, 369),
(1, 370),
(2, 370),
(8, 370),
(1, 371),
(2, 371),
(1, 372),
(2, 372),
(8, 372),
(9, 372),
(10, 372),
(11, 372),
(1, 373),
(2, 373),
(8, 373),
(9, 373),
(10, 373),
(11, 373),
(1, 374),
(2, 374),
(8, 374),
(9, 374),
(10, 374),
(11, 374),
(1, 375),
(2, 375),
(8, 375),
(9, 375),
(10, 375),
(11, 375),
(1, 376),
(2, 376),
(8, 376),
(9, 376),
(10, 376),
(11, 376),
(1, 377),
(2, 377),
(8, 377),
(9, 377),
(10, 377),
(11, 377),
(1, 378),
(2, 378),
(8, 378),
(9, 378),
(10, 378),
(1, 387),
(2, 387),
(1, 388),
(2, 388),
(8, 388),
(9, 388),
(10, 388),
(11, 388),
(1, 389),
(2, 389),
(8, 389),
(9, 389),
(10, 389),
(1, 390),
(2, 390),
(8, 390),
(9, 390),
(10, 390),
(1, 391),
(2, 391),
(8, 391),
(9, 391),
(10, 391),
(11, 391),
(1, 392),
(2, 392),
(8, 392),
(9, 392),
(10, 392),
(1, 393),
(2, 393),
(8, 393),
(9, 393),
(10, 393),
(1, 394),
(2, 394),
(8, 394),
(9, 394),
(10, 394),
(1, 395),
(2, 395),
(1, 396),
(2, 396),
(8, 396),
(9, 396),
(11, 396),
(1, 397),
(2, 397),
(8, 397),
(9, 397),
(1, 398),
(2, 398),
(8, 398),
(9, 398),
(1, 399),
(2, 399),
(8, 399),
(9, 399),
(11, 399),
(1, 400),
(2, 400),
(8, 400),
(9, 400),
(1, 401),
(2, 401),
(8, 401),
(9, 401),
(1, 402),
(2, 402),
(8, 402),
(9, 402),
(1, 403),
(2, 403),
(1, 404),
(2, 404),
(8, 404),
(10, 404),
(11, 404),
(1, 405),
(2, 405),
(8, 405),
(10, 405),
(1, 406),
(2, 406),
(8, 406),
(10, 406),
(1, 407),
(2, 407),
(8, 407),
(10, 407),
(11, 407),
(1, 408),
(2, 408),
(8, 408),
(10, 408),
(1, 409),
(2, 409),
(8, 409),
(10, 409),
(1, 410),
(2, 410),
(8, 410),
(10, 410),
(1, 411),
(2, 411),
(1, 412),
(2, 412),
(8, 412),
(10, 412),
(1, 413),
(2, 413),
(8, 413),
(10, 413),
(1, 414),
(2, 414),
(8, 414),
(10, 414),
(1, 415),
(2, 415),
(8, 415),
(10, 415),
(1, 416),
(2, 416),
(8, 416),
(10, 416),
(1, 417),
(2, 417),
(8, 417),
(10, 417),
(1, 418),
(2, 418),
(8, 418),
(10, 418),
(1, 427),
(2, 427),
(1, 428),
(2, 428),
(8, 428),
(9, 428),
(10, 428),
(1, 429),
(2, 429),
(8, 429),
(9, 429),
(10, 429),
(1, 430),
(2, 430),
(8, 430),
(9, 430),
(10, 430),
(1, 431),
(2, 431),
(8, 431),
(9, 431),
(10, 431),
(1, 432),
(2, 432),
(8, 432),
(9, 432),
(10, 432),
(1, 433),
(2, 433),
(8, 433),
(9, 433),
(10, 433),
(1, 434),
(2, 434),
(8, 434),
(9, 434),
(10, 434),
(1, 435),
(2, 435),
(1, 436),
(2, 436),
(8, 436),
(9, 436),
(10, 436),
(1, 437),
(2, 437),
(8, 437),
(9, 437),
(10, 437),
(1, 438),
(2, 438),
(8, 438),
(9, 438),
(10, 438),
(1, 439),
(2, 439),
(8, 439),
(9, 439),
(10, 439),
(1, 440),
(2, 440),
(8, 440),
(9, 440),
(10, 440),
(1, 441),
(2, 441),
(8, 441),
(9, 441),
(10, 441),
(1, 442),
(2, 442),
(8, 442),
(9, 442),
(10, 442),
(1, 443),
(2, 443),
(1, 444),
(2, 444),
(8, 444),
(9, 444),
(11, 444),
(1, 445),
(2, 445),
(8, 445),
(9, 445),
(1, 446),
(2, 446),
(8, 446),
(9, 446),
(1, 447),
(2, 447),
(8, 447),
(9, 447),
(11, 447),
(1, 448),
(2, 448),
(8, 448),
(9, 448),
(1, 449),
(2, 449),
(8, 449),
(9, 449),
(1, 450),
(2, 450),
(8, 450),
(9, 450),
(1, 451),
(2, 451),
(1, 452),
(2, 452),
(8, 452),
(9, 452),
(1, 453),
(2, 453),
(8, 453),
(9, 453),
(1, 454),
(2, 454),
(8, 454),
(9, 454),
(1, 455),
(2, 455),
(8, 455),
(9, 455),
(1, 456),
(2, 456),
(8, 456),
(9, 456),
(1, 457),
(2, 457),
(8, 457),
(9, 457),
(1, 458),
(2, 458),
(8, 458),
(9, 458),
(1, 459),
(2, 459),
(1, 461),
(2, 461),
(8, 461),
(9, 461),
(10, 461),
(11, 461),
(1, 462),
(2, 462),
(8, 462),
(9, 462),
(11, 462),
(1, 463),
(2, 463),
(1, 464),
(2, 464),
(8, 464),
(9, 464),
(10, 464),
(1, 465),
(2, 465),
(8, 465),
(9, 465),
(10, 465),
(1, 466),
(2, 466),
(8, 466),
(9, 466),
(10, 466),
(1, 467),
(2, 467),
(8, 467),
(9, 467),
(10, 467),
(1, 468),
(2, 468),
(8, 468),
(9, 468),
(10, 468),
(1, 469),
(2, 469),
(8, 469),
(9, 469),
(10, 469),
(1, 470),
(2, 470),
(8, 470),
(9, 470),
(10, 470),
(1, 471),
(2, 471),
(1, 472),
(2, 472),
(8, 472),
(9, 472),
(10, 472),
(1, 473),
(2, 473),
(8, 473),
(9, 473),
(10, 473),
(1, 474),
(2, 474),
(8, 474),
(9, 474),
(10, 474),
(1, 475),
(2, 475),
(8, 475),
(9, 475),
(10, 475),
(1, 476),
(2, 476),
(8, 476),
(9, 476),
(10, 476),
(1, 477),
(2, 477),
(8, 477),
(9, 477),
(10, 477),
(1, 478),
(2, 478),
(8, 478),
(9, 478),
(10, 478),
(1, 479),
(2, 479),
(8, 479),
(9, 479),
(10, 479),
(1, 480),
(2, 480),
(8, 480),
(9, 480),
(10, 480),
(1, 481),
(2, 481),
(1, 482),
(2, 482),
(8, 482),
(9, 482),
(10, 482),
(1, 483),
(2, 483),
(8, 483),
(9, 483),
(10, 483),
(1, 484),
(2, 484),
(8, 484),
(9, 484),
(10, 484),
(1, 485),
(2, 485),
(8, 485),
(9, 485),
(10, 485),
(1, 486),
(2, 486),
(8, 486),
(9, 486),
(10, 486),
(1, 487),
(2, 487),
(8, 487),
(9, 487),
(10, 487),
(1, 488),
(2, 488),
(8, 488),
(9, 488),
(10, 488),
(1, 489),
(2, 489),
(8, 489),
(9, 489),
(10, 489),
(1, 490),
(2, 490),
(8, 490),
(9, 490),
(10, 490),
(1, 491),
(2, 491),
(8, 491),
(9, 491),
(10, 491),
(1, 492),
(2, 492),
(8, 492),
(10, 492),
(1, 493),
(2, 493),
(8, 493),
(9, 493),
(10, 493),
(1, 494),
(2, 494),
(8, 494),
(9, 494),
(10, 494),
(11, 494),
(1, 495),
(2, 495),
(8, 495),
(10, 495),
(1, 496),
(2, 496),
(8, 496),
(10, 496),
(1, 497),
(2, 497),
(8, 497),
(10, 497),
(11, 497),
(1, 498),
(2, 498),
(8, 498),
(10, 498),
(11, 498),
(1, 499),
(2, 499),
(8, 499),
(10, 499),
(1, 500),
(2, 500),
(8, 500),
(9, 500),
(10, 500),
(1, 501),
(2, 501),
(1, 502),
(2, 502),
(8, 502),
(9, 502),
(10, 502),
(1, 503),
(2, 503),
(8, 503),
(9, 503),
(10, 503),
(1, 504),
(2, 504),
(8, 504),
(9, 504),
(10, 504),
(1, 505),
(2, 505),
(8, 505),
(9, 505),
(10, 505),
(1, 506),
(2, 506),
(8, 506),
(9, 506),
(10, 506),
(1, 507),
(2, 507),
(8, 507),
(9, 507),
(10, 507),
(1, 508),
(2, 508),
(8, 508),
(9, 508),
(10, 508),
(1, 509),
(2, 509),
(1, 510),
(2, 510),
(8, 510),
(9, 510),
(10, 510),
(1, 511),
(2, 511),
(8, 511),
(9, 511),
(10, 511),
(1, 512),
(2, 512),
(8, 512),
(9, 512),
(10, 512),
(1, 513),
(2, 513),
(8, 513),
(9, 513),
(10, 513),
(1, 514),
(2, 514),
(8, 514),
(9, 514),
(10, 514),
(1, 515),
(2, 515),
(8, 515),
(9, 515),
(10, 515),
(1, 516),
(2, 516),
(8, 516),
(9, 516),
(10, 516),
(1, 518),
(2, 518),
(1, 520),
(2, 520),
(8, 520),
(9, 520),
(10, 520),
(11, 520),
(1, 521),
(2, 521),
(1, 522),
(2, 522),
(8, 522),
(9, 522),
(1, 523),
(2, 523),
(8, 523),
(9, 523),
(1, 524),
(2, 524),
(8, 524),
(9, 524),
(1, 525),
(2, 525),
(8, 525),
(9, 525),
(1, 526),
(2, 526),
(8, 526),
(9, 526),
(10, 526),
(1, 527),
(2, 527),
(8, 527),
(9, 527),
(10, 527),
(1, 528),
(2, 528),
(1, 529),
(2, 529),
(8, 529),
(9, 529),
(11, 529),
(1, 530),
(2, 530),
(8, 530),
(9, 530),
(1, 531),
(2, 531),
(8, 531),
(9, 531),
(1, 532),
(2, 532),
(8, 532),
(9, 532),
(11, 532),
(1, 533),
(2, 533),
(8, 533),
(9, 533),
(1, 534),
(2, 534),
(8, 534),
(9, 534),
(1, 535),
(2, 535),
(8, 535),
(9, 535),
(1, 536),
(2, 536),
(8, 536),
(9, 536),
(10, 536),
(1, 537),
(2, 537),
(8, 537),
(9, 537),
(10, 537),
(1, 538),
(2, 538),
(8, 538),
(1, 539),
(2, 539),
(8, 539),
(9, 539),
(10, 539),
(1, 540),
(2, 540),
(8, 540),
(9, 540),
(10, 540),
(11, 540),
(1, 541),
(2, 541),
(8, 541),
(9, 541),
(10, 541),
(1, 542),
(2, 542),
(8, 542),
(9, 542),
(10, 542),
(1, 543),
(2, 543),
(1, 544),
(2, 544),
(8, 544),
(10, 544),
(1, 545),
(2, 545),
(8, 545),
(10, 545),
(1, 546),
(2, 546),
(8, 546),
(10, 546),
(1, 547),
(2, 547),
(8, 547),
(10, 547),
(1, 548),
(2, 548),
(8, 548),
(10, 548),
(1, 549),
(2, 549),
(8, 549),
(10, 549),
(1, 550),
(2, 550),
(8, 550),
(10, 550),
(1, 551),
(2, 551),
(1, 552),
(2, 552),
(8, 552),
(10, 552),
(1, 553),
(2, 553),
(8, 553),
(10, 553),
(1, 554),
(2, 554),
(8, 554),
(10, 554),
(1, 555),
(2, 555),
(8, 555),
(10, 555),
(1, 556),
(2, 556),
(8, 556),
(10, 556),
(1, 557),
(2, 557),
(8, 557),
(10, 557),
(1, 558),
(2, 558),
(8, 558),
(10, 558),
(1, 559),
(2, 559),
(8, 559),
(10, 559),
(1, 560),
(2, 560),
(8, 560),
(1, 561),
(2, 561),
(1, 562),
(2, 562),
(8, 562),
(1, 563),
(2, 563),
(1, 564),
(2, 564),
(8, 564),
(9, 564),
(10, 564),
(1, 565),
(2, 565),
(8, 565),
(9, 565),
(10, 565),
(1, 566),
(2, 566),
(8, 566),
(9, 566),
(10, 566),
(1, 567),
(2, 567),
(8, 567),
(9, 567),
(10, 567),
(1, 568),
(2, 568),
(8, 568),
(9, 568),
(10, 568),
(1, 569),
(2, 569),
(8, 569),
(9, 569),
(10, 569),
(1, 570),
(2, 570),
(8, 570),
(9, 570),
(10, 570),
(1, 571),
(2, 571),
(1, 572),
(2, 572),
(1, 573),
(2, 573),
(1, 574),
(2, 574),
(1, 575),
(2, 575),
(1, 576),
(2, 576),
(1, 577),
(2, 577),
(1, 578),
(2, 578),
(1, 579),
(2, 579),
(1, 580),
(2, 580),
(8, 580),
(9, 580),
(10, 580),
(1, 581),
(2, 581),
(8, 581),
(9, 581),
(10, 581),
(1, 582),
(2, 582),
(8, 582),
(9, 582),
(10, 582),
(1, 583),
(2, 583),
(8, 583),
(9, 583),
(10, 583),
(1, 584),
(2, 584),
(8, 584),
(9, 584),
(10, 584),
(1, 585),
(2, 585),
(8, 585),
(9, 585),
(10, 585),
(1, 586),
(2, 586),
(8, 586),
(9, 586),
(10, 586),
(1, 587),
(2, 587),
(1, 588),
(2, 588),
(8, 588),
(9, 588),
(10, 588),
(11, 588),
(1, 589),
(2, 589),
(8, 589),
(9, 589),
(10, 589),
(1, 590),
(2, 590),
(8, 590),
(9, 590),
(10, 590),
(1, 591),
(2, 591),
(8, 591),
(9, 591),
(10, 591),
(11, 591),
(1, 592),
(2, 592),
(8, 592),
(9, 592),
(10, 592),
(1, 593),
(2, 593),
(8, 593),
(9, 593),
(10, 593),
(1, 594),
(2, 594),
(8, 594),
(9, 594),
(10, 594),
(1, 595),
(2, 595),
(1, 596),
(2, 596),
(1, 597),
(2, 597),
(1, 598),
(2, 598),
(1, 599),
(2, 599),
(1, 600),
(2, 600),
(1, 601),
(2, 601),
(1, 602),
(2, 602),
(1, 603),
(2, 603),
(1, 604),
(2, 604),
(1, 605),
(2, 605),
(1, 606),
(2, 606),
(1, 607),
(2, 607),
(1, 608),
(2, 608),
(1, 609),
(2, 609),
(1, 610),
(2, 610),
(1, 611),
(2, 611),
(1, 612),
(2, 612),
(1, 613),
(2, 613),
(1, 614),
(2, 614),
(1, 615),
(2, 615),
(1, 616),
(2, 616),
(1, 617),
(2, 617),
(1, 618),
(2, 618),
(1, 619),
(2, 619),
(1, 620),
(2, 620),
(1, 621),
(2, 621),
(1, 622),
(2, 622),
(1, 623),
(2, 623),
(1, 624),
(2, 624),
(1, 625),
(2, 625),
(1, 626),
(2, 626),
(1, 627),
(2, 627),
(1, 628),
(2, 628);

-- --------------------------------------------------------

--
-- Table structure for table `salary_parameters`
--

CREATE TABLE `salary_parameters` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_basic` tinyint NOT NULL DEFAULT '0',
  `percentage_of_basic` int NOT NULL DEFAULT '0',
  `sorting` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `salary_parameters`
--

INSERT INTO `salary_parameters` (`id`, `title`, `is_basic`, `percentage_of_basic`, `sorting`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 'Basic', 1, 0, 0, '2026-01-19 23:47:18', '2026-01-19 23:47:18', 'active', 1, 1, 1, 1),
(2, 'House Rentt', 0, 50, 1, '2026-01-19 17:49:59', '2026-01-19 17:49:59', 'active', 1, 1, 1, 1),
(3, 'Madical', 0, 30, 2, '2026-01-20 10:53:30', '2026-01-20 10:53:30', 'active', 1, 1, 1, 1),
(4, 'Communication', 0, 20, 3, '2026-01-20 10:54:10', '2026-01-20 10:54:10', 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `salary_sheets`
--

CREATE TABLE `salary_sheets` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `month` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` int NOT NULL,
  `generated_date` date NOT NULL,
  `approved_by` int DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_sheet_details`
--

CREATE TABLE `salary_sheet_details` (
  `id` bigint UNSIGNED NOT NULL,
  `salary_sheet_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `salary` decimal(8,2) NOT NULL,
  `commission` decimal(12,2) NOT NULL DEFAULT '0.00',
  `bonus` decimal(8,2) NOT NULL DEFAULT '0.00',
  `installment` decimal(12,2) NOT NULL DEFAULT '0.00',
  `installment_history` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `deduct` decimal(8,2) NOT NULL DEFAULT '0.00',
  `total` decimal(8,2) NOT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feedback_email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile1` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile2` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `logo_small` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `favicon` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `address_two` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `default_currency_id` int DEFAULT NULL,
  `system_mode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hs_code` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `swift_code` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_no` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `routing_number` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `title`, `short_title`, `contact_email`, `feedback_email`, `mobile1`, `mobile2`, `logo`, `logo_small`, `favicon`, `address`, `address_two`, `default_currency_id`, `system_mode`, `hs_code`, `swift_code`, `vat_no`, `bank_name`, `branch_name`, `account_number`, `routing_number`, `created_at`, `updated_at`) VALUES
(1, 'BSA GARMENTS ACCESSORIES INDUSTRIES.', 'BSA GARMENTS ACCESSORIES INDUSTRIES.', 'bsalabel@gmail.com', NULL, '+880 1912 252095', '+880 1620 221377', '{\"original\":\"upload\\/logo\\/original\\/240f22c9-9670-43f5-b7dd-798e6ba4f86b.webp\",\"204x70\":\"upload\\/logo\\/204x70\\/edef06b3-a58a-40a2-ae08-c8d8290075e2.webp\",\"175x60\":\"upload\\/logo\\/175x60\\/b4831379-6a3b-4e61-92ef-ea742b017776.webp\"}', '{\"original\":\"upload\\/logo_small\\/original\\/aca75385-8cd2-44ce-911f-2d03348b8a4f.webp\",\"600x200\":\"upload\\/logo_small\\/600x200\\/affa069a-7bbb-4b9e-8ad2-f629c1662d53.webp\",\"300x100\":\"upload\\/logo_small\\/300x100\\/d0669e68-a30c-492e-b620-a0a9fe6ed15a.webp\",\"150x50\":\"upload\\/logo_small\\/150x50\\/ab591984-892f-45cf-b3cb-2f2ab4251718.webp\"}', 'upload/conf/260706123728-5961nbi-favicon.png', 'MA-70,70/1 Merul Badda,BaddA PS;', NULL, 1, 'sales-based', '6217.10.00', 'PRMRBDDH', '004984961-0101', 'The Premier Bank LTD', 'Rampura Branch', '0011100000434', '235275747', '2024-07-02 03:14:27', '2026-07-12 03:31:58');

-- --------------------------------------------------------

--
-- Table structure for table `sizes`
--

CREATE TABLE `sizes` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(225) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transactions`
--

CREATE TABLE `stock_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `item_id` int NOT NULL,
  `color_id` int DEFAULT NULL,
  `size_id` int DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `transaction_type` enum('Opening','Purchase','Issue','Adjustment') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_type` enum('Purchase','Issue','','') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty_in` int NOT NULL DEFAULT '0',
  `qty_out` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_transactions`
--

INSERT INTO `stock_transactions` (`id`, `item_id`, `color_id`, `size_id`, `transaction_date`, `transaction_type`, `reference_type`, `reference_id`, `qty_in`, `qty_out`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 2, NULL, NULL, '2026-01-08', 'Purchase', 'Purchase', '1', 2000, 0, '2026-01-29 20:10:31', '2026-01-29 20:10:31', 'active', 1, 1, 1, 1),
(2, 5, NULL, NULL, '2026-01-08', 'Purchase', 'Purchase', NULL, 2000, 0, '2026-01-29 20:17:20', '2026-01-29 20:17:20', 'active', 1, 1, 1, 1),
(5, 6, NULL, NULL, '2026-01-08', 'Purchase', 'Purchase', NULL, 3103, 0, '2026-01-29 20:34:31', '2026-01-29 20:34:31', 'active', 1, 1, 1, 1),
(6, 5, NULL, NULL, '2026-01-08', 'Purchase', 'Purchase', NULL, 1000, 0, '2026-01-29 20:34:31', '2026-01-29 20:34:31', 'active', 1, 1, 1, 1),
(7, 5, NULL, NULL, '2026-01-08', 'Purchase', 'Purchase', '3', 1000, 0, '2026-01-29 20:41:06', '2026-01-29 20:41:06', 'active', 1, 1, 1, 1),
(8, 6, NULL, NULL, '2026-01-08', 'Purchase', 'Purchase', '3', 2915, 0, '2026-01-29 20:41:06', '2026-01-29 20:41:06', 'active', 1, 1, 1, 1),
(9, 5, NULL, NULL, '2026-01-31', 'Purchase', 'Purchase', '4', 2000, 0, '2026-01-31 22:19:32', '2026-01-31 22:19:32', 'active', 1, 1, 1, 1),
(10, 1, NULL, NULL, '2026-01-31', 'Purchase', 'Purchase', '4', 100, 0, '2026-01-31 22:19:32', '2026-01-31 22:19:32', 'active', 1, 1, 1, 1),
(12, 10, NULL, NULL, '2026-01-22', 'Purchase', 'Purchase', NULL, 1, 0, '2026-02-01 19:24:36', '2026-02-01 19:24:36', 'active', 1, 1, 1, 1),
(13, 11, NULL, NULL, '2026-01-22', 'Purchase', 'Purchase', NULL, 4, 0, '2026-02-01 19:24:36', '2026-02-01 19:24:36', 'active', 1, 1, 1, 1),
(14, 6, NULL, NULL, '2026-02-01', 'Purchase', 'Purchase', '6', 3020, 0, '2026-02-01 21:44:03', '2026-02-01 21:44:03', 'active', 1, 1, 1, 1),
(15, 5, NULL, NULL, '2026-02-01', 'Purchase', 'Purchase', '7', 2000, 0, '2026-02-01 21:45:42', '2026-02-01 21:45:42', 'active', 1, 1, 1, 1),
(16, 6, NULL, NULL, '2026-02-01', 'Purchase', 'Purchase', '7', 4007, 0, '2026-02-01 21:45:42', '2026-02-01 21:45:42', 'active', 1, 1, 1, 1),
(17, 5, NULL, NULL, '2026-02-03', 'Purchase', 'Purchase', '1', 1000, 0, '2026-02-03 08:00:38', '2026-02-03 08:00:38', 'active', 1, 1, 1, 1),
(18, 4, NULL, NULL, '2026-02-03', 'Purchase', 'Purchase', '1', 1, 0, '2026-02-03 08:00:38', '2026-02-03 08:00:38', 'active', 1, 1, 1, 1),
(20, 5, NULL, NULL, '2026-02-09', 'Purchase', 'Purchase', '1', 1000, 0, '2026-02-09 14:46:30', '2026-02-09 14:46:30', 'active', 1, 1, 1, 1),
(21, 6, NULL, NULL, '2026-02-09', 'Purchase', 'Purchase', '1', 1000, 0, '2026-02-09 14:46:30', '2026-02-09 14:46:30', 'active', 1, 1, 1, 1),
(22, 7, NULL, NULL, '2026-02-09', 'Purchase', 'Purchase', '1', 1000, 0, '2026-02-09 14:46:30', '2026-02-09 14:46:30', 'active', 1, 1, 1, 1),
(24, 1, NULL, NULL, '2026-02-18', 'Purchase', 'Purchase', NULL, 100, 0, '2026-02-18 06:29:03', '2026-02-18 06:29:03', 'active', 1, 1, 1, 1),
(25, 1, NULL, NULL, '2026-02-18', 'Purchase', 'Purchase', NULL, 100, 0, '2026-02-18 06:30:29', '2026-02-18 06:30:29', 'active', 1, 1, 1, 1),
(26, 1, NULL, NULL, '2026-02-18', 'Purchase', 'Purchase', NULL, 100, 0, '2026-02-18 06:31:19', '2026-02-18 06:31:19', 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint UNSIGNED NOT NULL,
  `supid` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `org_name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `website` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `routing` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `supid`, `org_name`, `name`, `designation`, `mobile`, `email`, `address`, `website`, `account_name`, `bank_name`, `branch`, `account_no`, `routing`, `created_at`, `updated_at`, `deleted_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, '111', 'ofs cable industries limited', 'Farid Ahmed', NULL, '01322876519', 'test@beeonline.com.bd', 'House 67, Road 4, Block c, banani, 1213 Dhaka', 'www.ofscables.com', NULL, 'Brac bank PLC', 'banani', '1507205076464001', '060260435', '2026-01-29 20:05:51', '2026-04-22 03:26:58', '2026-04-22 03:26:58', 'active', 1, 1, 1, 1),
(2, '112', 'SAS TECH Limited', 'Ariful Islam', NULL, '01896293304', 'sales@sastechbd.com', 'Samir Tower, 3rd Floor,,308 Elephant Road, dhaka', NULL, 'ECOM NETWORKS', NULL, 'MOTIZHEL', '2067365640001', NULL, '2026-02-01 19:19:07', '2026-04-22 03:26:54', '2026-04-22 03:26:54', 'active', 1, 1, 1, 1),
(3, '111', 'Medge Carver', 'Merritt Vanceeeee', 'Ut quo esse et sed', '01897518123', 'dybati@mailinator.com', 'Eos et perspiciatis', 'https://www.xedyzykelan.info', 'Kevyn Ellison', NULL, 'Qui ipsam laboriosam', '61663', '99866', '2026-04-22 03:27:11', '2026-04-22 03:28:58', NULL, 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` bigint UNSIGNED NOT NULL,
  `module` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `module`, `title`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 'Service', 'Mbps', '2025-12-16 05:15:49', '2025-12-16 05:15:49', 'active', 1, 1, 1, 1),
(2, 'Item', 'Meter', '2025-12-16 11:48:59', '2025-12-16 11:48:59', 'active', 1, 1, 1, 1),
(3, 'Item', 'Pcs', '2025-12-28 01:18:01', '2025-12-28 01:18:01', 'active', 1, 1, 1, 1),
(4, 'Item', 'Packet', '2025-12-28 01:18:29', '2025-12-28 01:18:29', 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_login_histories`
--

CREATE TABLE `user_login_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `user_guard` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_at` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_ip` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_browser_client` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sorting` int NOT NULL DEFAULT '0',
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_login_histories`
--

INSERT INTO `user_login_histories` (`id`, `user_id`, `user_guard`, `login_at`, `login_ip`, `login_browser_client`, `sorting`, `status`, `created_at`, `updated_at`) VALUES
(1, 30, 'admin', '2026-08-16 16:49:23', '::1', 'Google Chrome', 0, 'active', '2026-08-16 10:49:23', '2026-08-16 10:49:23'),
(2, 30, 'admin', '2026-08-16 16:58:29', '::1', 'Google Chrome', 0, 'active', '2026-08-16 10:58:29', '2026-08-16 10:58:29'),
(3, 30, 'admin', '2026-08-16 19:55:13', '::1', 'Google Chrome', 0, 'active', '2026-08-16 13:55:13', '2026-08-16 13:55:13'),
(4, 30, 'admin', '2026-08-17 05:31:45', '::1', 'Google Chrome', 0, 'active', '2026-08-16 23:31:45', '2026-08-16 23:31:45');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` bigint UNSIGNED NOT NULL,
  `voucherno` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `voucher_type` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `voucher_date` date NOT NULL,
  `narration` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `financial_year_id` int NOT NULL,
  `payment_id` int DEFAULT NULL,
  `source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`id`, `voucherno`, `voucher_type`, `voucher_date`, `narration`, `financial_year_id`, `payment_id`, `source`, `source_id`, `created_at`, `updated_at`, `deleted_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, '111', 'Journal', '2026-03-28', 'Receivable voucher auto generated', 6, NULL, 'Invoice', 1, '2026-03-28 09:20:41', '2026-03-29 13:11:46', '2026-03-29 19:11:46', 'active', 1, 1, 1, 1),
(2, '112', 'Journal', '2026-03-28', 'Receivable voucher auto generated', 6, NULL, 'Invoice', 1, '2026-03-28 13:54:01', '2026-03-29 13:11:46', '2026-03-29 19:11:46', 'active', 1, 1, 1, 1),
(3, '113', 'Journal', '2026-03-28', 'Receivable voucher auto generated', 6, NULL, 'Invoice', 1, '2026-03-28 16:57:44', '2026-03-29 13:11:46', '2026-03-29 19:11:46', 'active', 1, 1, 1, 1),
(4, '114', 'Journal', '2026-03-28', 'Receivable voucher auto generated', 6, NULL, 'Invoice', 1, '2026-03-28 17:02:31', '2026-03-29 13:11:46', '2026-03-29 19:11:46', 'active', 1, 1, 1, 1),
(5, '115', 'Journal', '2026-03-28', 'Receivable voucher auto generated', 6, NULL, 'Invoice', 2, '2026-03-28 17:03:32', '2026-03-29 02:08:54', '2026-03-29 08:08:54', 'active', 1, 1, 1, 1),
(6, '115', 'Journal', '2026-03-28', 'Receivable voucher auto generated', 6, NULL, 'Invoice', 2, '2026-03-29 02:08:54', '2026-03-29 02:42:37', '2026-03-29 08:42:37', 'active', 1, 1, 1, 1),
(7, '111', 'Journal', '2026-03-28', 'Receivable voucher auto generated', 6, NULL, 'Invoice', 1, '2026-03-29 13:11:46', '2026-03-29 13:11:46', NULL, 'active', 1, 1, 1, 1),
(8, '112', 'Receive', '2026-03-29', 'Payment processed via Payment Module', 6, 1, NULL, NULL, '2026-03-29 13:11:47', '2026-03-29 13:11:47', NULL, 'active', 1, 1, 1, 1),
(10, '114', 'Journal', '2026-04-26', 'Payable voucher auto generated', 6, NULL, 'ExpenseDetail', 2, '2026-04-26 14:03:52', '2026-04-26 14:03:52', NULL, 'active', 1, 1, 1, 1),
(11, '115', 'Journal', '2026-04-26', 'Payable voucher auto generated', 6, NULL, 'ExpenseDetail', 3, '2026-04-26 14:03:52', '2026-04-26 14:03:52', NULL, 'active', 1, 1, 1, 1),
(12, '116', 'Pay', '2026-07-11', 'Payment processed via Payment Module', 6, 2, NULL, NULL, '2026-07-11 12:11:04', '2026-07-11 12:11:04', NULL, 'active', 1, 1, 1, 1),
(13, '117', 'Opening', '2026-07-13', 'System Opening Balance', 6, NULL, NULL, NULL, '2026-07-13 16:46:57', '2026-07-13 16:46:57', NULL, 'active', 1, 1, 1, 1),
(14, '118', 'Opening', '2026-07-15', 'Opening Balance : Bank', 6, NULL, NULL, NULL, '2026-07-15 03:50:53', '2026-07-15 03:50:53', NULL, 'active', 1, 1, 1, 1),
(15, '119', 'Journal', '2026-07-29', 'Receivable voucher auto generated', 6, NULL, 'Invoice', 3, '2026-07-29 13:15:03', '2026-07-29 13:15:44', '2026-07-29 19:15:44', 'active', 1, 1, 1, 1),
(16, '119', 'Journal', '2026-07-29', 'Receivable voucher auto generated', 6, NULL, 'Invoice', 3, '2026-07-29 13:15:44', '2026-07-29 13:15:44', NULL, 'active', 1, 1, 1, 1),
(17, '120', 'Receive', '2026-07-29', 'Payment processed via Payment Module', 6, 3, NULL, NULL, '2026-07-29 13:15:44', '2026-07-29 13:15:44', NULL, 'active', 1, 1, 1, 1),
(18, '120', 'Journal', '2026-04-27', 'Payable voucher auto generated', 6, NULL, 'ExpenseDetail', 5, '2026-07-29 13:40:08', '2026-07-29 13:40:08', NULL, 'active', 1, 1, 1, 1),
(19, '121', 'Journal', '2026-08-11', 'Payable voucher auto generated', 6, NULL, 'Commission', 2, '2026-08-11 13:48:12', '2026-08-11 13:54:03', '2026-08-11 19:54:03', 'active', 1, 1, 1, 1),
(20, '121', 'Journal', '2026-08-11', 'Payable voucher auto generated', 6, NULL, 'Commission', 2, '2026-08-11 13:54:09', '2026-08-11 13:54:09', NULL, 'active', 1, 1, 1, 1),
(21, '122', 'Journal', '2026-08-11', 'Payable voucher auto generated', 6, NULL, 'Commission', 2, '2026-08-11 13:54:09', '2026-08-11 13:54:09', NULL, 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `voucher_details`
--

CREATE TABLE `voucher_details` (
  `id` bigint UNSIGNED NOT NULL,
  `voucher_id` int NOT NULL,
  `account_id` int NOT NULL,
  `dr_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `cr_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `reference_type` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` int DEFAULT NULL,
  `line_narration` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` date DEFAULT NULL,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `voucher_details`
--

INSERT INTO `voucher_details` (`id`, `voucher_id`, `account_id`, `dr_amount`, `cr_amount`, `reference_type`, `reference_id`, `line_narration`, `created_at`, `updated_at`, `deleted_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 1, 26, 4918.00, 0.00, 'Client', 1, NULL, '2026-03-28 09:20:41', '2026-03-29 13:11:46', '2026-03-29', 'active', 1, 1, 1, 1),
(2, 1, 40, 0.00, 4918.00, 'Client', 1, NULL, '2026-03-28 09:20:41', '2026-03-29 13:11:46', '2026-03-29', 'active', 1, 1, 1, 1),
(3, 2, 26, 239750.00, 0.00, 'Client', 1, NULL, '2026-03-28 13:54:01', '2026-03-29 13:11:46', '2026-03-29', 'active', 1, 1, 1, 1),
(4, 2, 40, 0.00, 239750.00, 'Client', 1, NULL, '2026-03-28 13:54:01', '2026-03-29 13:11:46', '2026-03-29', 'active', 1, 1, 1, 1),
(5, 3, 26, 239750.00, 0.00, 'Client', 1, NULL, '2026-03-28 16:57:44', '2026-03-29 13:11:46', '2026-03-29', 'active', 1, 1, 1, 1),
(6, 3, 40, 0.00, 239750.00, 'Client', 1, NULL, '2026-03-28 16:57:44', '2026-03-29 13:11:46', '2026-03-29', 'active', 1, 1, 1, 1),
(7, 4, 26, 239750.00, 0.00, 'Client', 1, NULL, '2026-03-28 17:02:31', '2026-03-29 13:11:46', '2026-03-29', 'active', 1, 1, 1, 1),
(8, 4, 40, 0.00, 239750.00, 'Client', 1, NULL, '2026-03-28 17:02:31', '2026-03-29 13:11:46', '2026-03-29', 'active', 1, 1, 1, 1),
(9, 5, 26, 375000.00, 0.00, 'Client', 1, NULL, '2026-03-28 17:03:32', '2026-03-29 02:08:54', '2026-03-29', 'active', 1, 1, 1, 1),
(10, 5, 40, 0.00, 375000.00, 'Client', 1, NULL, '2026-03-28 17:03:32', '2026-03-29 02:08:54', '2026-03-29', 'active', 1, 1, 1, 1),
(11, 6, 26, 250000.00, 0.00, 'Client', 1, NULL, '2026-03-29 02:08:54', '2026-03-29 02:42:36', '2026-03-29', 'active', 1, 1, 1, 1),
(12, 6, 40, 0.00, 250000.00, 'Client', 1, NULL, '2026-03-29 02:08:54', '2026-03-29 02:42:36', '2026-03-29', 'active', 1, 1, 1, 1),
(13, 7, 26, 239750.00, 0.00, 'Client', 1, NULL, '2026-03-29 13:11:46', '2026-03-29 13:11:46', NULL, 'active', 1, 1, 1, 1),
(14, 7, 40, 0.00, 239750.00, 'Client', 1, NULL, '2026-03-29 13:11:46', '2026-03-29 13:11:46', NULL, 'active', 1, 1, 1, 1),
(15, 8, 24, 239750.00, 0.00, NULL, NULL, NULL, '2026-03-29 13:11:47', '2026-03-29 13:11:47', NULL, 'active', 1, 1, 1, 1),
(16, 8, 26, 0.00, 239750.00, 'Client', 1, NULL, '2026-03-29 13:11:47', '2026-03-29 13:11:47', NULL, 'active', 1, 1, 1, 1),
(18, 10, 50, 5000.00, 0.00, 'Office Expense', NULL, NULL, '2026-04-26 14:03:52', '2026-04-26 14:03:52', NULL, 'active', 1, 1, 1, 1),
(19, 10, 80, 0.00, 5000.00, 'Office Expense', NULL, NULL, '2026-04-26 14:03:52', '2026-04-26 14:03:52', NULL, 'active', 1, 1, 1, 1),
(20, 11, 46, 3000.00, 0.00, 'Office Expense', NULL, NULL, '2026-04-26 14:03:52', '2026-04-26 14:03:52', NULL, 'active', 1, 1, 1, 1),
(21, 11, 80, 0.00, 3000.00, 'Office Expense', NULL, NULL, '2026-04-26 14:03:52', '2026-04-26 14:03:52', NULL, 'active', 1, 1, 1, 1),
(22, 12, 24, 0.00, 5000.00, NULL, NULL, NULL, '2026-07-11 12:11:04', '2026-07-11 12:11:04', NULL, 'active', 1, 1, 1, 1),
(23, 12, 80, 5000.00, 0.00, 'Office Expense', 2, NULL, '2026-07-11 12:11:04', '2026-07-11 12:11:04', NULL, 'active', 1, 1, 1, 1),
(25, 13, 24, 1500.00, 0.00, NULL, NULL, NULL, '2026-07-13 16:46:57', '2026-07-13 16:46:57', NULL, 'active', 1, 1, 1, 1),
(26, 14, 24, 5000.00, 0.00, 'OpeningBalance', 24, 'Opening Balance', '2026-07-15 03:50:53', '2026-07-15 03:53:46', NULL, 'active', 1, 1, 1, 1),
(27, 14, 82, 0.00, 5000.00, 'OpeningBalance', 24, 'Opening Balance Offset', '2026-07-15 03:50:53', '2026-07-15 03:53:46', NULL, 'active', 1, 1, 1, 1),
(28, 15, 26, 3000.00, 0.00, 'Client', 4, NULL, '2026-07-29 13:15:03', '2026-07-29 13:15:44', '2026-07-29', 'active', 1, 1, 1, 1),
(29, 15, 40, 0.00, 3000.00, 'Client', 4, NULL, '2026-07-29 13:15:03', '2026-07-29 13:15:44', '2026-07-29', 'active', 1, 1, 1, 1),
(30, 16, 26, 3000.00, 0.00, 'Client', 4, NULL, '2026-07-29 13:15:44', '2026-07-29 13:15:44', NULL, 'active', 1, 1, 1, 1),
(31, 16, 40, 0.00, 3000.00, 'Client', 4, NULL, '2026-07-29 13:15:44', '2026-07-29 13:15:44', NULL, 'active', 1, 1, 1, 1),
(32, 17, 24, 3000.00, 0.00, NULL, NULL, NULL, '2026-07-29 13:15:44', '2026-07-29 13:15:44', NULL, 'active', 1, 1, 1, 1),
(33, 17, 26, 0.00, 3000.00, 'Client', 4, NULL, '2026-07-29 13:15:44', '2026-07-29 13:15:44', NULL, 'active', 1, 1, 1, 1),
(34, 18, 45, 10000.00, 0.00, 'Office Expense', NULL, NULL, '2026-07-29 13:40:08', '2026-07-29 13:40:08', NULL, 'active', 1, 1, 1, 1),
(35, 18, 80, 0.00, 10000.00, 'Office Expense', NULL, NULL, '2026-07-29 13:40:08', '2026-07-29 13:40:08', NULL, 'active', 1, 1, 1, 1),
(36, 19, 74, 500.00, 0.00, 'Others', NULL, NULL, '2026-08-11 13:48:12', '2026-08-11 13:54:03', '2026-08-11', 'active', 1, 1, 1, 1),
(37, 19, 79, 0.00, 500.00, 'Others', NULL, NULL, '2026-08-11 13:48:12', '2026-08-11 13:54:03', '2026-08-11', 'active', 1, 1, 1, 1),
(38, 20, 74, 500.00, 0.00, 'Others', NULL, NULL, '2026-08-11 13:54:09', '2026-08-11 13:54:09', NULL, 'active', 1, 1, 1, 1),
(39, 20, 79, 0.00, 500.00, 'Others', NULL, NULL, '2026-08-11 13:54:09', '2026-08-11 13:54:09', NULL, 'active', 1, 1, 1, 1),
(40, 21, 74, 500.00, 0.00, 'Agent', 1, NULL, '2026-08-11 13:54:09', '2026-08-11 13:54:09', NULL, 'active', 1, 1, 1, 1),
(41, 21, 79, 0.00, 500.00, 'Agent', 1, NULL, '2026-08-11 13:54:09', '2026-08-11 13:54:09', NULL, 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `withdrawls`
--

CREATE TABLE `withdrawls` (
  `id` bigint UNSIGNED NOT NULL,
  `withdrwal_date` date NOT NULL,
  `amount` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_id` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remarks` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workorders`
--

CREATE TABLE `workorders` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint NOT NULL,
  `order_date` date NOT NULL,
  `order_no` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uno_no` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_date` date NOT NULL,
  `shipping` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_id` int NOT NULL,
  `currency_rate` decimal(12,2) DEFAULT NULL,
  `amount` decimal(8,2) NOT NULL,
  `due_amount` decimal(12,2) NOT NULL,
  `is_closed` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workorders`
--

INSERT INTO `workorders` (`id`, `client_id`, `order_date`, `order_no`, `uno_no`, `delivery_date`, `shipping`, `remarks`, `currency_id`, `currency_rate`, `amount`, `due_amount`, `is_closed`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 1, '2026-03-27', '39221', '24506', '2026-03-31', 'Quod non officiis ex', 'Qui iste minus sed s', 2, 0.00, 4918.00, 0.00, 1, '2026-03-27 03:28:28', '2026-07-29 13:15:04', 'active', 1, 1, 1, 1),
(2, 3, '2026-03-28', '24415', NULL, '2026-03-28', 'null', NULL, 2, NULL, 280.64, 210.46, 0, '2026-03-28 04:40:53', '2026-04-22 03:48:48', 'active', 1, 1, 1, 1),
(3, 1, '2026-07-12', '4566', '334', '2026-07-12', 'null', NULL, 2, 1.00, 75000.00, 75000.00, 0, '2026-07-12 17:30:26', '2026-07-12 17:35:04', 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `workorder_details`
--

CREATE TABLE `workorder_details` (
  `id` bigint UNSIGNED NOT NULL,
  `workorder_id` int NOT NULL,
  `item_id` int DEFAULT NULL,
  `description` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `actual_qty` decimal(8,2) NOT NULL,
  `ordered_qty` decimal(8,2) NOT NULL,
  `receive_qty` decimal(8,2) NOT NULL DEFAULT '0.00',
  `due_qty` decimal(8,2) NOT NULL,
  `unit_price` decimal(8,2) NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','deactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL DEFAULT '1',
  `updated_by` int NOT NULL DEFAULT '1',
  `created_ip` int NOT NULL DEFAULT '1',
  `updated_ip` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workorder_details`
--

INSERT INTO `workorder_details` (`id`, `workorder_id`, `item_id`, `description`, `actual_qty`, `ordered_qty`, `receive_qty`, `due_qty`, `unit_price`, `price`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(1, 1, NULL, 'Tempor consectetur ', 934.00, 950.00, 250.00, 700.00, 3.56, 3382.00, '2026-03-27 03:28:28', '2026-07-12 17:36:08', 'active', 1, 1, 1, 1),
(2, 1, 0, 'Cloth cutter blade', 600.00, 600.00, 300.00, 300.00, 2.56, 1536.00, '2026-03-27 03:28:28', '2026-03-27 03:31:05', 'active', 1, 1, 1, 1),
(335, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01J-101-C8\nCom style number - BSFJA01J-101\nColor - OFF WHITE\nSize - 2T\nUpc/ean (gtin) - 198740903450\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:48', '2026-04-22 03:48:48', 'active', 1, 1, 1, 1),
(336, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01J-101-C8\nCom style number - BSFJA01J-101\nColor - OFF WHITE\nSize - 3T\nUpc/ean (gtin) - 198740903467\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:48', '2026-04-22 03:48:48', 'active', 1, 1, 1, 1),
(337, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01J-101-C8\nCom style number - BSFJA01J-101\nColor - OFF WHITE\nSize - 4\nUpc/ean (gtin) - 198740903474\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:48', '2026-04-22 03:48:48', 'active', 1, 1, 1, 1),
(338, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01J-101-C8\nCom style number - BSFJA01J-101\nColor - OFF WHITE\nSize - 5\nUpc/ean (gtin) - 198740903481\nLabel id - DIL', 288.00, 308.00, 0.00, 0.00, 0.01, 3.08, '2026-04-22 03:48:48', '2026-04-22 03:48:48', 'active', 1, 1, 1, 1),
(339, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01J-101-C8\nCom style number - BSFJA01J-101\nColor - OFF WHITE\nSize - 6\nUpc/ean (gtin) - 198740903498\nLabel id - DIL', 288.00, 308.00, 0.00, 0.00, 0.01, 3.08, '2026-04-22 03:48:48', '2026-04-22 03:48:48', 'active', 1, 1, 1, 1),
(340, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01J-101-C8\nCom style number - BSFJA01J-101\nColor - OFF WHITE\nSize - 7\nUpc/ean (gtin) - 198740903504\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:48', '2026-04-22 03:48:48', 'active', 1, 1, 1, 1),
(341, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03J-401-C8\nCom style number - BSFJA03J-401\nColor - DARK DENIM HEATHER\nSize - 2T\nUpc/ean (gtin) - 198740900091\nLabel id - DIL', 81.00, 101.00, 0.00, 0.00, 0.01, 1.01, '2026-04-22 03:48:48', '2026-04-22 03:48:48', 'active', 1, 1, 1, 1),
(342, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03J-401-C8\nCom style number - BSFJA03J-401\nColor - DARK DENIM HEATHER\nSize - 3T\nUpc/ean (gtin) - 198740903405\nLabel id - DIL', 81.00, 101.00, 0.00, 0.00, 0.01, 1.01, '2026-04-22 03:48:48', '2026-04-22 03:48:48', 'active', 1, 1, 1, 1),
(343, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03J-401-C8\nCom style number - BSFJA03J-401\nColor - DARK DENIM HEATHER\nSize - 4\nUpc/ean (gtin) - 198740903412\nLabel id - DIL', 81.00, 101.00, 0.00, 0.00, 0.01, 1.01, '2026-04-22 03:48:48', '2026-04-22 03:48:48', 'active', 1, 1, 1, 1),
(344, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03J-401-C8\nCom style number - BSFJA03J-401\nColor - DARK DENIM HEATHER\nSize - 5\nUpc/ean (gtin) - 198740903429\nLabel id - DIL', 162.00, 182.00, 0.00, 0.00, 0.01, 1.82, '2026-04-22 03:48:48', '2026-04-22 03:48:48', 'active', 1, 1, 1, 1),
(345, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03J-401-C8\nCom style number - BSFJA03J-401\nColor - DARK DENIM HEATHER\nSize - 6\nUpc/ean (gtin) - 198740903436\nLabel id - DIL', 162.00, 182.00, 0.00, 0.00, 0.01, 1.82, '2026-04-22 03:48:48', '2026-04-22 03:48:48', 'active', 1, 1, 1, 1),
(346, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03J-401-C8\nCom style number - BSFJA03J-401\nColor - DARK DENIM HEATHER\nSize - 7\nUpc/ean (gtin) - 198740903443\nLabel id - DIL', 81.00, 101.00, 0.00, 0.00, 0.01, 1.01, '2026-04-22 03:48:48', '2026-04-22 03:48:48', 'active', 1, 1, 1, 1),
(347, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05J-415-C8\nCom style number - BSFJA05J-415\nColor - ODYSSEY GREY\nSize - 2T\nUpc/ean (gtin) - 198740903634\nLabel id - DIL', 114.00, 134.00, 0.00, 0.00, 0.01, 1.34, '2026-04-22 03:48:48', '2026-04-22 03:48:48', 'active', 1, 1, 1, 1),
(348, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05J-415-C8\nCom style number - BSFJA05J-415\nColor - ODYSSEY GREY\nSize - 3T\nUpc/ean (gtin) - 198740903641\nLabel id - DIL', 114.00, 134.00, 0.00, 0.00, 0.01, 1.34, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(349, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05J-415-C8\nCom style number - BSFJA05J-415\nColor - ODYSSEY GREY\nSize - 4\nUpc/ean (gtin) - 198740903658\nLabel id - DIL', 114.00, 134.00, 0.00, 0.00, 0.01, 1.34, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(350, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05J-415-C8\nCom style number - BSFJA05J-415\nColor - ODYSSEY GREY\nSize - 5\nUpc/ean (gtin) - 198740903665\nLabel id - DIL', 228.00, 248.00, 0.00, 0.00, 0.01, 2.48, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(351, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05J-415-C8\nCom style number - BSFJA05J-415\nColor - ODYSSEY GREY\nSize - 6\nUpc/ean (gtin) - 198740903672\nLabel id - DIL', 228.00, 248.00, 0.00, 0.00, 0.01, 2.48, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(352, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05J-415-C8\nCom style number - BSFJA05J-415\nColor - ODYSSEY GREY\nSize - 7\nUpc/ean (gtin) - 198740903689\nLabel id - DIL', 114.00, 134.00, 0.00, 0.00, 0.01, 1.34, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(353, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06J-436-C8\nCom style number - BSFJA06J-436\nColor - TRUE BLUE HEATHER\nSize - 2T\nUpc/ean (gtin) - 198740903573\nLabel id - DIL', 126.00, 146.00, 0.00, 0.00, 0.01, 1.46, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(354, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06J-436-C8\nCom style number - BSFJA06J-436\nColor - TRUE BLUE HEATHER\nSize - 3T\nUpc/ean (gtin) - 198740903580\nLabel id - DIL', 126.00, 146.00, 0.00, 0.00, 0.01, 1.46, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(355, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06J-436-C8\nCom style number - BSFJA06J-436\nColor - TRUE BLUE HEATHER\nSize - 4\nUpc/ean (gtin) - 198740903597\nLabel id - DIL', 126.00, 146.00, 0.00, 0.00, 0.01, 1.46, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(356, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06J-436-C8\nCom style number - BSFJA06J-436\nColor - TRUE BLUE HEATHER\nSize - 5\nUpc/ean (gtin) - 198740903603\nLabel id - DIL', 252.00, 272.00, 0.00, 0.00, 0.01, 2.72, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(357, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06J-436-C8\nCom style number - BSFJA06J-436\nColor - TRUE BLUE HEATHER\nSize - 6\nUpc/ean (gtin) - 198740903610\nLabel id - DIL', 252.00, 272.00, 0.00, 0.00, 0.01, 2.72, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(358, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06J-436-C8\nCom style number - BSFJA06J-436\nColor - TRUE BLUE HEATHER\nSize - 7\nUpc/ean (gtin) - 198740903627\nLabel id - DIL', 126.00, 146.00, 0.00, 0.00, 0.01, 1.46, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(359, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA07B\nMaterial number # - 25BSFJA07J-103-C8\nCom style number - BSFJA07J-103\nColor - OATMEAL\nSize - 2T\nUpc/ean (gtin) - 198740903511\nLabel id - DIL', 105.00, 125.00, 0.00, 0.00, 0.01, 1.25, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(360, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA07B\nMaterial number # - 25BSFJA07J-103-C8\nCom style number - BSFJA07J-103\nColor - OATMEAL\nSize - 3T\nUpc/ean (gtin) - 198740903528\nLabel id - DIL', 105.00, 125.00, 0.00, 0.00, 0.01, 1.25, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(361, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA07B\nMaterial number # - 25BSFJA07J-103-C8\nCom style number - BSFJA07J-103\nColor - OATMEAL\nSize - 4\nUpc/ean (gtin) - 198740903535\nLabel id - DIL', 105.00, 125.00, 0.00, 0.00, 0.01, 1.25, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(362, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA07B\nMaterial number # - 25BSFJA07J-103-C8\nCom style number - BSFJA07J-103\nColor - OATMEAL\nSize - 5\nUpc/ean (gtin) - 198740903542\nLabel id - DIL', 210.00, 230.00, 0.00, 0.00, 0.01, 2.30, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(363, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA07B\nMaterial number # - 25BSFJA07J-103-C8\nCom style number - BSFJA07J-103\nColor - OATMEAL\nSize - 6\nUpc/ean (gtin) - 198740903559\nLabel id - DIL', 210.00, 230.00, 0.00, 0.00, 0.01, 2.30, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(364, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA07B\nMaterial number # - 25BSFJA07J-103-C8\nCom style number - BSFJA07J-103\nColor - OATMEAL\nSize - 7\nUpc/ean (gtin) - 198740903566\nLabel id - DIL', 105.00, 125.00, 0.00, 0.00, 0.01, 1.25, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(365, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08J-440-C8\nCom style number - BSFJA08J-440\nColor - BLUE GRASS HEATHER\nSize - 2T\nUpc/ean (gtin) - 198740903252\nLabel id - DIL', 93.00, 113.00, 0.00, 0.00, 0.01, 1.13, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(366, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08J-440-C8\nCom style number - BSFJA08J-440\nColor - BLUE GRASS HEATHER\nSize - 3T\nUpc/ean (gtin) - 198740903269\nLabel id - DIL', 93.00, 113.00, 0.00, 0.00, 0.01, 1.13, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(367, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08J-440-C8\nCom style number - BSFJA08J-440\nColor - BLUE GRASS HEATHER\nSize - 4\nUpc/ean (gtin) - 198740903276\nLabel id - DIL', 93.00, 113.00, 0.00, 0.00, 0.01, 1.13, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(368, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08J-440-C8\nCom style number - BSFJA08J-440\nColor - BLUE GRASS HEATHER\nSize - 5\nUpc/ean (gtin) - 198740903283\nLabel id - DIL', 186.00, 206.00, 0.00, 0.00, 0.01, 2.06, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(369, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08J-440-C8\nCom style number - BSFJA08J-440\nColor - BLUE GRASS HEATHER\nSize - 6\nUpc/ean (gtin) - 198740903290\nLabel id - DIL', 186.00, 206.00, 0.00, 0.00, 0.01, 2.06, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(370, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08J-440-C8\nCom style number - BSFJA08J-440\nColor - BLUE GRASS HEATHER\nSize - 7\nUpc/ean (gtin) - 198740903306\nLabel id - DIL', 93.00, 113.00, 0.00, 0.00, 0.01, 1.13, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(371, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA10B\nMaterial number # - 25BSFJA10J-431-C8\nCom style number - BSFJA10J-431\nColor - BLUE RADIANCE\nSize - 2T\nUpc/ean (gtin) - 198740903696\nLabel id - DIL', 96.00, 116.00, 0.00, 0.00, 0.01, 1.16, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(372, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA10B\nMaterial number # - 25BSFJA10J-431-C8\nCom style number - BSFJA10J-431\nColor - BLUE RADIANCE\nSize - 3T\nUpc/ean (gtin) - 198740903702\nLabel id - DIL', 96.00, 116.00, 0.00, 0.00, 0.01, 1.16, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(373, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA10B\nMaterial number # - 25BSFJA10J-431-C8\nCom style number - BSFJA10J-431\nColor - BLUE RADIANCE\nSize - 4\nUpc/ean (gtin) - 198740903719\nLabel id - DIL', 96.00, 116.00, 0.00, 0.00, 0.01, 1.16, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(374, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA10B\nMaterial number # - 25BSFJA10J-431-C8\nCom style number - BSFJA10J-431\nColor - BLUE RADIANCE\nSize - 5\nUpc/ean (gtin) - 198740903726\nLabel id - DIL', 192.00, 212.00, 0.00, 0.00, 0.01, 2.12, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(375, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA10B\nMaterial number # - 25BSFJA10J-431-C8\nCom style number - BSFJA10J-431\nColor - BLUE RADIANCE\nSize - 6\nUpc/ean (gtin) - 198740903733\nLabel id - DIL', 192.00, 212.00, 0.00, 0.00, 0.01, 2.12, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(376, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA10B\nMaterial number # - 25BSFJA10J-431-C8\nCom style number - BSFJA10J-431\nColor - BLUE RADIANCE\nSize - 7\nUpc/ean (gtin) - 198740903740\nLabel id - DIL', 96.00, 116.00, 0.00, 0.00, 0.01, 1.16, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(377, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA12B\nMaterial number # - 25BSFJA12J-832-C8\nCom style number - BSFJA12J-832\nColor - PEACH\nSize - 2T\nUpc/ean (gtin) - 198740903818\nLabel id - DIL', 69.00, 89.00, 0.00, 0.00, 0.01, 0.89, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(378, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA12B\nMaterial number # - 25BSFJA12J-832-C8\nCom style number - BSFJA12J-832\nColor - PEACH\nSize - 3T\nUpc/ean (gtin) - 198740903825\nLabel id - DIL', 69.00, 89.00, 0.00, 0.00, 0.01, 0.89, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(379, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA12B\nMaterial number # - 25BSFJA12J-832-C8\nCom style number - BSFJA12J-832\nColor - PEACH\nSize - 4\nUpc/ean (gtin) - 198740903832\nLabel id - DIL', 69.00, 89.00, 0.00, 0.00, 0.01, 0.89, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(380, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA12B\nMaterial number # - 25BSFJA12J-832-C8\nCom style number - BSFJA12J-832\nColor - PEACH\nSize - 5\nUpc/ean (gtin) - 198740903849\nLabel id - DIL', 138.00, 158.00, 0.00, 0.00, 0.01, 1.58, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(381, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA12B\nMaterial number # - 25BSFJA12J-832-C8\nCom style number - BSFJA12J-832\nColor - PEACH\nSize - 6\nUpc/ean (gtin) - 198740903856\nLabel id - DIL', 138.00, 158.00, 0.00, 0.00, 0.01, 1.58, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(382, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA12B\nMaterial number # - 25BSFJA12J-832-C8\nCom style number - BSFJA12J-832\nColor - PEACH\nSize - 7\nUpc/ean (gtin) - 198740903863\nLabel id - DIL', 69.00, 89.00, 0.00, 0.00, 0.01, 0.89, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(383, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13J-421-C8\nCom style number - BSFJA13J-421\nColor - CHINA BLUE\nSize - 2T\nUpc/ean (gtin) - 198740903757\nLabel id - DIL', 126.00, 146.00, 0.00, 0.00, 0.01, 1.46, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(384, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13J-421-C8\nCom style number - BSFJA13J-421\nColor - CHINA BLUE\nSize - 3T\nUpc/ean (gtin) - 198740903764\nLabel id - DIL', 126.00, 146.00, 0.00, 0.00, 0.01, 1.46, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(385, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13J-421-C8\nCom style number - BSFJA13J-421\nColor - CHINA BLUE\nSize - 4\nUpc/ean (gtin) - 198740903771\nLabel id - DIL', 126.00, 146.00, 0.00, 0.00, 0.01, 1.46, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(386, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13J-421-C8\nCom style number - BSFJA13J-421\nColor - CHINA BLUE\nSize - 5\nUpc/ean (gtin) - 198740903788\nLabel id - DIL', 252.00, 272.00, 0.00, 0.00, 0.01, 2.72, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(387, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13J-421-C8\nCom style number - BSFJA13J-421\nColor - CHINA BLUE\nSize - 6\nUpc/ean (gtin) - 198740903795\nLabel id - DIL', 252.00, 272.00, 0.00, 0.00, 0.01, 2.72, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(388, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13J-421-C8\nCom style number - BSFJA13J-421\nColor - CHINA BLUE\nSize - 7\nUpc/ean (gtin) - 198740903801\nLabel id - DIL', 126.00, 146.00, 0.00, 0.00, 0.01, 1.46, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(389, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16J-002-C8\nCom style number - BSFJA16J-002\nColor - BLACK HEATHER\nSize - 2T\nUpc/ean (gtin) - 198740903870\nLabel id - DIL', 126.00, 146.00, 0.00, 0.00, 0.01, 1.46, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(390, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16J-002-C8\nCom style number - BSFJA16J-002\nColor - BLACK HEATHER\nSize - 3T\nUpc/ean (gtin) - 198740903887\nLabel id - DIL', 126.00, 146.00, 0.00, 0.00, 0.01, 1.46, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(391, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16J-002-C8\nCom style number - BSFJA16J-002\nColor - BLACK HEATHER\nSize - 4\nUpc/ean (gtin) - 198740903894\nLabel id - DIL', 126.00, 146.00, 0.00, 0.00, 0.01, 1.46, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(392, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16J-002-C8\nCom style number - BSFJA16J-002\nColor - BLACK HEATHER\nSize - 5\nUpc/ean (gtin) - 198740903900\nLabel id - DIL', 252.00, 272.00, 0.00, 0.00, 0.01, 2.72, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(393, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16J-002-C8\nCom style number - BSFJA16J-002\nColor - BLACK HEATHER\nSize - 6\nUpc/ean (gtin) - 198740903917\nLabel id - DIL', 252.00, 272.00, 0.00, 0.00, 0.01, 2.72, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(394, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16J-002-C8\nCom style number - BSFJA16J-002\nColor - BLACK HEATHER\nSize - 7\nUpc/ean (gtin) - 198740903924\nLabel id - DIL', 126.00, 146.00, 0.00, 0.00, 0.01, 1.46, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(395, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17J-030-C8\nCom style number - BSFJA17J-030\nColor - GREY HEATHER\nSize - 2T\nUpc/ean (gtin) - 198740903993\nLabel id - DIL', 165.00, 185.00, 0.00, 0.00, 0.01, 1.85, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(396, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17J-030-C8\nCom style number - BSFJA17J-030\nColor - GREY HEATHER\nSize - 3T\nUpc/ean (gtin) - 198740904006\nLabel id - DIL', 165.00, 185.00, 0.00, 0.00, 0.01, 1.85, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(397, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17J-030-C8\nCom style number - BSFJA17J-030\nColor - GREY HEATHER\nSize - 4\nUpc/ean (gtin) - 198740904013\nLabel id - DIL', 165.00, 185.00, 0.00, 0.00, 0.01, 1.85, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(398, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17J-030-C8\nCom style number - BSFJA17J-030\nColor - GREY HEATHER\nSize - 5\nUpc/ean (gtin) - 198740904020\nLabel id - DIL', 330.00, 350.00, 0.00, 0.00, 0.01, 3.50, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(399, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17J-030-C8\nCom style number - BSFJA17J-030\nColor - GREY HEATHER\nSize - 6\nUpc/ean (gtin) - 198740904037\nLabel id - DIL', 330.00, 350.00, 0.00, 0.00, 0.01, 3.50, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(400, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17J-030-C8\nCom style number - BSFJA17J-030\nColor - GREY HEATHER\nSize - 7\nUpc/ean (gtin) - 198740904044\nLabel id - DIL', 165.00, 185.00, 0.00, 0.00, 0.01, 1.85, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(401, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18J-001-C8\nCom style number - BSFJA18J-001\nColor - BLACK\nSize - 2T\nUpc/ean (gtin) - 198740903931\nLabel id - DIL', 66.00, 86.00, 0.00, 0.00, 0.01, 0.86, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(402, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18J-001-C8\nCom style number - BSFJA18J-001\nColor - BLACK\nSize - 3T\nUpc/ean (gtin) - 198740903948\nLabel id - DIL', 66.00, 86.00, 0.00, 0.00, 0.01, 0.86, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(403, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18J-001-C8\nCom style number - BSFJA18J-001\nColor - BLACK\nSize - 4\nUpc/ean (gtin) - 198740903955\nLabel id - DIL', 66.00, 86.00, 0.00, 0.00, 0.01, 0.86, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(404, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18J-001-C8\nCom style number - BSFJA18J-001\nColor - BLACK\nSize - 5\nUpc/ean (gtin) - 198740903962\nLabel id - DIL', 132.00, 152.00, 0.00, 0.00, 0.01, 1.52, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(405, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18J-001-C8\nCom style number - BSFJA18J-001\nColor - BLACK\nSize - 6\nUpc/ean (gtin) - 198740903979\nLabel id - DIL', 132.00, 152.00, 0.00, 0.00, 0.01, 1.52, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(406, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18J-001-C8\nCom style number - BSFJA18J-001\nColor - BLACK\nSize - 7\nUpc/ean (gtin) - 198740903986\nLabel id - DIL', 66.00, 86.00, 0.00, 0.00, 0.01, 0.86, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(407, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22J-100-C8\nCom style number - BSFJA22J-100\nColor - WHITE\nSize - 2T\nUpc/ean (gtin) - 198740904051\nLabel id - DIL', 108.00, 128.00, 0.00, 0.00, 0.01, 1.28, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(408, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22J-100-C8\nCom style number - BSFJA22J-100\nColor - WHITE\nSize - 3T\nUpc/ean (gtin) - 198740904068\nLabel id - DIL', 108.00, 128.00, 0.00, 0.00, 0.01, 1.28, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(409, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22J-100-C8\nCom style number - BSFJA22J-100\nColor - WHITE\nSize - 4\nUpc/ean (gtin) - 198740904075\nLabel id - DIL', 108.00, 128.00, 0.00, 0.00, 0.01, 1.28, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(410, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22J-100-C8\nCom style number - BSFJA22J-100\nColor - WHITE\nSize - 5\nUpc/ean (gtin) - 198740904082\nLabel id - DIL', 216.00, 236.00, 0.00, 0.00, 0.01, 2.36, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(411, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22J-100-C8\nCom style number - BSFJA22J-100\nColor - WHITE\nSize - 6\nUpc/ean (gtin) - 198740904099\nLabel id - DIL', 216.00, 236.00, 0.00, 0.00, 0.01, 2.36, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(412, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22J-100-C8\nCom style number - BSFJA22J-100\nColor - WHITE\nSize - 7\nUpc/ean (gtin) - 198740904105\nLabel id - DIL', 108.00, 128.00, 0.00, 0.00, 0.01, 1.28, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(413, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA00B\nMaterial number # - 25BSFJA00F-311-PC\nCom style number - BSFJA00F-311\nColor - LAUREL WREATH\nSize - XL18/20\nUpc/ean (gtin) - 198740882847\nLabel id - DIL', 120.00, 140.00, 0.00, 0.00, 0.01, 1.40, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(414, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA00B\nMaterial number # - 25BSFJA00F-311-PC\nCom style number - BSFJA00F-311\nColor - LAUREL WREATH\nSize - L14/16\nUpc/ean (gtin) - 198740882816\nLabel id - DIL', 240.00, 260.00, 0.00, 0.00, 0.01, 2.60, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(415, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA00B\nMaterial number # - 25BSFJA00F-311-PC\nCom style number - BSFJA00F-311\nColor - LAUREL WREATH\nSize - M10/12\nUpc/ean (gtin) - 198740882823\nLabel id - DIL', 240.00, 260.00, 0.00, 0.00, 0.01, 2.60, '2026-04-22 03:48:49', '2026-04-22 03:48:49', 'active', 1, 1, 1, 1),
(416, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA00B\nMaterial number # - 25BSFJA00F-311-PC\nCom style number - BSFJA00F-311\nColor - LAUREL WREATH\nSize - S8\nUpc/ean (gtin) - 198740882830\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(417, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01F-101-PC\nCom style number - BSFJA01F-101\nColor - OFF WHITE\nSize - XL18/20\nUpc/ean (gtin) - 198740882922\nLabel id - DIL', 72.00, 92.00, 0.00, 0.00, 0.01, 0.92, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(418, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01F-101-PC\nCom style number - BSFJA01F-101\nColor - OFF WHITE\nSize - L14/16\nUpc/ean (gtin) - 198740882892\nLabel id - DIL', 120.00, 140.00, 0.00, 0.00, 0.01, 1.40, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(419, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01F-101-PC\nCom style number - BSFJA01F-101\nColor - OFF WHITE\nSize - S8\nUpc/ean (gtin) - 198740882915\nLabel id - DIL', 72.00, 92.00, 0.00, 0.00, 0.01, 0.92, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(420, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA01B\nMaterial number # - 25BSFJA01F-101-PC\nCom style number - BSFJA01F-101\nColor - OFF WHITE\nSize - M10/12\nUpc/ean (gtin) - 198740882908\nLabel id - DIL', 120.00, 140.00, 0.00, 0.00, 0.01, 1.40, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(421, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA02B\nMaterial number # - 25BSFJA02F-030-PC\nCom style number - BSFJA02F-030\nColor - GREY HEATHER\nSize - S8\nUpc/ean (gtin) - 198740882878\nLabel id - DIL', 96.00, 116.00, 0.00, 0.00, 0.01, 1.16, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(422, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA02B\nMaterial number # - 25BSFJA02F-030-PC\nCom style number - BSFJA02F-030\nColor - GREY HEATHER\nSize - M10/12\nUpc/ean (gtin) - 198740882861\nLabel id - DIL', 192.00, 212.00, 0.00, 0.00, 0.01, 2.12, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(423, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA02B\nMaterial number # - 25BSFJA02F-030-PC\nCom style number - BSFJA02F-030\nColor - GREY HEATHER\nSize - XL18/20\nUpc/ean (gtin) - 198740882885\nLabel id - DIL', 96.00, 116.00, 0.00, 0.00, 0.01, 1.16, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(424, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA02B\nMaterial number # - 25BSFJA02F-030-PC\nCom style number - BSFJA02F-030\nColor - GREY HEATHER\nSize - L14/16\nUpc/ean (gtin) - 198740882854\nLabel id - DIL', 192.00, 212.00, 0.00, 0.00, 0.01, 2.12, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(425, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03F-401-PC\nCom style number - BSFJA03F-401\nColor - DARK DENIM HEATHER\nSize - S8\nUpc/ean (gtin) - 198740882953\nLabel id - DIL', 96.00, 116.00, 0.00, 0.00, 0.01, 1.16, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(426, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03F-401-PC\nCom style number - BSFJA03F-401\nColor - DARK DENIM HEATHER\nSize - M10/12\nUpc/ean (gtin) - 198740882946\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(427, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03F-401-PC\nCom style number - BSFJA03F-401\nColor - DARK DENIM HEATHER\nSize - L14/16\nUpc/ean (gtin) - 198740882939\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(428, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA03B\nMaterial number # - 25BSFJA03F-401-PC\nCom style number - BSFJA03F-401\nColor - DARK DENIM HEATHER\nSize - XL18/20\nUpc/ean (gtin) - 198740882960\nLabel id - DIL', 72.00, 92.00, 0.00, 0.00, 0.01, 0.92, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(429, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA04B\nMaterial number # - 25BSFJA04F-278-PC\nCom style number - BSFJA04F-278\nColor - PALE KHAKI\nSize - XL18/20\nUpc/ean (gtin) - 198740883004\nLabel id - DIL', 96.00, 116.00, 0.00, 0.00, 0.01, 1.16, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(430, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA04B\nMaterial number # - 25BSFJA04F-278-PC\nCom style number - BSFJA04F-278\nColor - PALE KHAKI\nSize - L14/16\nUpc/ean (gtin) - 198740882977\nLabel id - DIL', 168.00, 188.00, 0.00, 0.00, 0.01, 1.88, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(431, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA04B\nMaterial number # - 25BSFJA04F-278-PC\nCom style number - BSFJA04F-278\nColor - PALE KHAKI\nSize - S8\nUpc/ean (gtin) - 198740882991\nLabel id - DIL', 96.00, 116.00, 0.00, 0.00, 0.01, 1.16, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(432, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA04B\nMaterial number # - 25BSFJA04F-278-PC\nCom style number - BSFJA04F-278\nColor - PALE KHAKI\nSize - M10/12\nUpc/ean (gtin) - 198740882984\nLabel id - DIL', 168.00, 188.00, 0.00, 0.00, 0.01, 1.88, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(433, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05F-415-PC\nCom style number - BSFJA05F-415\nColor - ODYSSEY GREY\nSize - XL18/20\nUpc/ean (gtin) - 198740883042\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(434, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05F-415-PC\nCom style number - BSFJA05F-415\nColor - ODYSSEY GREY\nSize - L14/16\nUpc/ean (gtin) - 198740883035\nLabel id - DIL', 264.00, 284.00, 0.00, 0.00, 0.01, 2.84, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(435, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05F-415-PC\nCom style number - BSFJA05F-415\nColor - ODYSSEY GREY\nSize - M10/12\nUpc/ean (gtin) - 198740883011\nLabel id - DIL', 264.00, 284.00, 0.00, 0.00, 0.01, 2.84, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(436, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA05B\nMaterial number # - 25BSFJA05F-415-PC\nCom style number - BSFJA05F-415\nColor - ODYSSEY GREY\nSize - S8\nUpc/ean (gtin) - 198740883028\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(437, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06F-436-PC\nCom style number - BSFJA06F-436\nColor - TRUE BLUE HEATHER\nSize - S8\nUpc/ean (gtin) - 198740883158\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(438, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06F-436-PC\nCom style number - BSFJA06F-436\nColor - TRUE BLUE HEATHER\nSize - M10/12\nUpc/ean (gtin) - 198740883141\nLabel id - DIL', 216.00, 236.00, 0.00, 0.00, 0.01, 2.36, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(439, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06F-436-PC\nCom style number - BSFJA06F-436\nColor - TRUE BLUE HEATHER\nSize - L14/16\nUpc/ean (gtin) - 198740883134\nLabel id - DIL', 216.00, 236.00, 0.00, 0.00, 0.01, 2.36, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(440, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA06B\nMaterial number # - 25BSFJA06F-436-PC\nCom style number - BSFJA06F-436\nColor - TRUE BLUE HEATHER\nSize - XL18/20\nUpc/ean (gtin) - 198740883165\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(441, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08F-440-PC\nCom style number - BSFJA08F-440\nColor - BLUE GRASS HEATHER\nSize - XL18/20\nUpc/ean (gtin) - 198740883080\nLabel id - DIL', 96.00, 116.00, 0.00, 0.00, 0.01, 1.16, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(442, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08F-440-PC\nCom style number - BSFJA08F-440\nColor - BLUE GRASS HEATHER\nSize - L14/16\nUpc/ean (gtin) - 198740883059\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(443, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08F-440-PC\nCom style number - BSFJA08F-440\nColor - BLUE GRASS HEATHER\nSize - M10/12\nUpc/ean (gtin) - 198740883066\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(444, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA08B\nMaterial number # - 25BSFJA08F-440-PC\nCom style number - BSFJA08F-440\nColor - BLUE GRASS HEATHER\nSize - S8\nUpc/ean (gtin) - 198740883073\nLabel id - DIL', 96.00, 116.00, 0.00, 0.00, 0.01, 1.16, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(445, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA09B\nMaterial number # - 25BSFJA09F-473-PC\nCom style number - BSFJA09F-473\nColor - TRUE NAVY\nSize - L14/16\nUpc/ean (gtin) - 198740883097\nLabel id - DIL', 264.00, 284.00, 0.00, 0.00, 0.01, 2.84, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(446, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA09B\nMaterial number # - 25BSFJA09F-473-PC\nCom style number - BSFJA09F-473\nColor - TRUE NAVY\nSize - XL18/20\nUpc/ean (gtin) - 198740883127\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(447, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA09B\nMaterial number # - 25BSFJA09F-473-PC\nCom style number - BSFJA09F-473\nColor - TRUE NAVY\nSize - M10/12\nUpc/ean (gtin) - 198740883103\nLabel id - DIL', 264.00, 284.00, 0.00, 0.00, 0.01, 2.84, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(448, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA09B\nMaterial number # - 25BSFJA09F-473-PC\nCom style number - BSFJA09F-473\nColor - TRUE NAVY\nSize - S8\nUpc/ean (gtin) - 198740883110\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(449, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA11B\nMaterial number # - 25BSFJA11F-415-PC\nCom style number - BSFJA11F-415\nColor - ODYSSEY GREY\nSize - S8\nUpc/ean (gtin) - 198740883271\nLabel id - DIL', 72.00, 92.00, 0.00, 0.00, 0.01, 0.92, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(450, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA11B\nMaterial number # - 25BSFJA11F-415-PC\nCom style number - BSFJA11F-415\nColor - ODYSSEY GREY\nSize - M10/12\nUpc/ean (gtin) - 198740883264\nLabel id - DIL', 120.00, 140.00, 0.00, 0.00, 0.01, 1.40, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(451, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA11B\nMaterial number # - 25BSFJA11F-415-PC\nCom style number - BSFJA11F-415\nColor - ODYSSEY GREY\nSize - L14/16\nUpc/ean (gtin) - 198740883257\nLabel id - DIL', 120.00, 140.00, 0.00, 0.00, 0.01, 1.40, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(452, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA11B\nMaterial number # - 25BSFJA11F-415-PC\nCom style number - BSFJA11F-415\nColor - ODYSSEY GREY\nSize - XL18/20\nUpc/ean (gtin) - 198740883288\nLabel id - DIL', 72.00, 92.00, 0.00, 0.00, 0.01, 0.92, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(453, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13F-421-PC\nCom style number - BSFJA13F-421\nColor - CHINA BLUE\nSize - XL18/20\nUpc/ean (gtin) - 198740883363\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(454, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13F-421-PC\nCom style number - BSFJA13F-421\nColor - CHINA BLUE\nSize - L14/16\nUpc/ean (gtin) - 198740883332\nLabel id - DIL', 264.00, 284.00, 0.00, 0.00, 0.01, 2.84, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(455, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13F-421-PC\nCom style number - BSFJA13F-421\nColor - CHINA BLUE\nSize - M10/12\nUpc/ean (gtin) - 198740883349\nLabel id - DIL', 264.00, 284.00, 0.00, 0.00, 0.01, 2.84, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(456, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA13B\nMaterial number # - 25BSFJA13F-421-PC\nCom style number - BSFJA13F-421\nColor - CHINA BLUE\nSize - S8\nUpc/ean (gtin) - 198740883356\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(457, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA15B\nMaterial number # - 25BSFJA15F-450-PC\nCom style number - BSFJA15F-450\nColor - BLUE SURF\nSize - XL18/20\nUpc/ean (gtin) - 198740883448\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(458, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA15B\nMaterial number # - 25BSFJA15F-450-PC\nCom style number - BSFJA15F-450\nColor - BLUE SURF\nSize - L14/16\nUpc/ean (gtin) - 198740883417\nLabel id - DIL', 264.00, 284.00, 0.00, 0.00, 0.01, 2.84, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(459, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA15B\nMaterial number # - 25BSFJA15F-450-PC\nCom style number - BSFJA15F-450\nColor - BLUE SURF\nSize - M10/12\nUpc/ean (gtin) - 198740883424\nLabel id - DIL', 264.00, 284.00, 0.00, 0.00, 0.01, 2.84, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(460, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA15B\nMaterial number # - 25BSFJA15F-450-PC\nCom style number - BSFJA15F-450\nColor - BLUE SURF\nSize - S8\nUpc/ean (gtin) - 198740883431\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(461, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16F-002-PC\nCom style number - BSFJA16F-002\nColor - BLACK HEATHER\nSize - XL18/20\nUpc/ean (gtin) - 198740883486\nLabel id - DIL', 72.00, 92.00, 0.00, 0.00, 0.01, 0.92, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(462, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16F-002-PC\nCom style number - BSFJA16F-002\nColor - BLACK HEATHER\nSize - L14/16\nUpc/ean (gtin) - 198740883455\nLabel id - DIL', 120.00, 140.00, 0.00, 0.00, 0.01, 1.40, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(463, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16F-002-PC\nCom style number - BSFJA16F-002\nColor - BLACK HEATHER\nSize - M10/12\nUpc/ean (gtin) - 198740883462\nLabel id - DIL', 120.00, 140.00, 0.00, 0.00, 0.01, 1.40, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(464, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA16B\nMaterial number # - 25BSFJA16F-002-PC\nCom style number - BSFJA16F-002\nColor - BLACK HEATHER\nSize - S8\nUpc/ean (gtin) - 198740883479\nLabel id - DIL', 72.00, 92.00, 0.00, 0.00, 0.01, 0.92, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(465, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17F-030-PC\nCom style number - BSFJA17F-030\nColor - GREY HEATHER\nSize - S8\nUpc/ean (gtin) - 198740883516\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(466, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17F-030-PC\nCom style number - BSFJA17F-030\nColor - GREY HEATHER\nSize - M10/12\nUpc/ean (gtin) - 198740883509\nLabel id - DIL', 192.00, 212.00, 0.00, 0.00, 0.01, 2.12, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(467, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17F-030-PC\nCom style number - BSFJA17F-030\nColor - GREY HEATHER\nSize - L14/16\nUpc/ean (gtin) - 198740883493\nLabel id - DIL', 216.00, 236.00, 0.00, 0.00, 0.01, 2.36, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(468, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA17B\nMaterial number # - 25BSFJA17F-030-PC\nCom style number - BSFJA17F-030\nColor - GREY HEATHER\nSize - XL18/20\nUpc/ean (gtin) - 198740883523\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(469, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18F-001-PC\nCom style number - BSFJA18F-001\nColor - BLACK\nSize - S8\nUpc/ean (gtin) - 198740883608\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(470, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18F-001-PC\nCom style number - BSFJA18F-001\nColor - BLACK\nSize - M10/12\nUpc/ean (gtin) - 198740883585\nLabel id - DIL', 264.00, 284.00, 0.00, 0.00, 0.01, 2.84, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(471, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18F-001-PC\nCom style number - BSFJA18F-001\nColor - BLACK\nSize - L14/16\nUpc/ean (gtin) - 198740883578\nLabel id - DIL', 264.00, 284.00, 0.00, 0.00, 0.01, 2.84, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(472, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA18B\nMaterial number # - 25BSFJA18F-001-PC\nCom style number - BSFJA18F-001\nColor - BLACK\nSize - XL18/20\nUpc/ean (gtin) - 198740883592\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(473, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA19B\nMaterial number # - 25BSFJA19F-740-PC\nCom style number - BSFJA19F-740\nColor - RAFFIA\nSize - S8\nUpc/ean (gtin) - 198740883554\nLabel id - DIL', 72.00, 92.00, 0.00, 0.00, 0.01, 0.92, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(474, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA19B\nMaterial number # - 25BSFJA19F-740-PC\nCom style number - BSFJA19F-740\nColor - RAFFIA\nSize - M10/12\nUpc/ean (gtin) - 198740883547\nLabel id - DIL', 120.00, 140.00, 0.00, 0.00, 0.01, 1.40, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(475, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA19B\nMaterial number # - 25BSFJA19F-740-PC\nCom style number - BSFJA19F-740\nColor - RAFFIA\nSize - L14/16\nUpc/ean (gtin) - 198740883530\nLabel id - DIL', 120.00, 140.00, 0.00, 0.00, 0.01, 1.40, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(476, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA19B\nMaterial number # - 25BSFJA19F-740-PC\nCom style number - BSFJA19F-740\nColor - RAFFIA\nSize - XL18/20\nUpc/ean (gtin) - 198740883561\nLabel id - DIL', 72.00, 92.00, 0.00, 0.00, 0.01, 0.92, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(477, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA20B\nMaterial number # - 25BSFJA20F-512-PC\nCom style number - BSFJA20F-512\nColor - PURPLE ASH\nSize - M10/12\nUpc/ean (gtin) - 198740883622\nLabel id - DIL', 192.00, 212.00, 0.00, 0.00, 0.01, 2.12, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(478, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA20B\nMaterial number # - 25BSFJA20F-512-PC\nCom style number - BSFJA20F-512\nColor - PURPLE ASH\nSize - S8\nUpc/ean (gtin) - 198740883639\nLabel id - DIL', 120.00, 140.00, 0.00, 0.00, 0.01, 1.40, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(479, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA20B\nMaterial number # - 25BSFJA20F-512-PC\nCom style number - BSFJA20F-512\nColor - PURPLE ASH\nSize - L14/16\nUpc/ean (gtin) - 198740883615\nLabel id - DIL', 192.00, 212.00, 0.00, 0.00, 0.01, 2.12, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(480, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA20B\nMaterial number # - 25BSFJA20F-512-PC\nCom style number - BSFJA20F-512\nColor - PURPLE ASH\nSize - XL18/20\nUpc/ean (gtin) - 198740883646\nLabel id - DIL', 120.00, 140.00, 0.00, 0.00, 0.01, 1.40, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(481, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA21B\nMaterial number # - 25BSFJA21F-001-PC\nCom style number - BSFJA21F-001\nColor - BLACK\nSize - S8\nUpc/ean (gtin) - 198740883677\nLabel id - DIL', 192.00, 212.00, 0.00, 0.00, 0.01, 2.12, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1);
INSERT INTO `workorder_details` (`id`, `workorder_id`, `item_id`, `description`, `actual_qty`, `ordered_qty`, `receive_qty`, `due_qty`, `unit_price`, `price`, `created_at`, `updated_at`, `status`, `created_by`, `updated_by`, `created_ip`, `updated_ip`) VALUES
(482, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA21B\nMaterial number # - 25BSFJA21F-001-PC\nCom style number - BSFJA21F-001\nColor - BLACK\nSize - M10/12\nUpc/ean (gtin) - 198740883660\nLabel id - DIL', 216.00, 236.00, 0.00, 0.00, 0.01, 2.36, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(483, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA21B\nMaterial number # - 25BSFJA21F-001-PC\nCom style number - BSFJA21F-001\nColor - BLACK\nSize - L14/16\nUpc/ean (gtin) - 198740883653\nLabel id - DIL', 216.00, 236.00, 0.00, 0.00, 0.01, 2.36, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(484, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA21B\nMaterial number # - 25BSFJA21F-001-PC\nCom style number - BSFJA21F-001\nColor - BLACK\nSize - XL18/20\nUpc/ean (gtin) - 198740883684\nLabel id - DIL', 168.00, 188.00, 0.00, 0.00, 0.01, 1.88, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(485, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22F-100-PC\nCom style number - BSFJA22F-100\nColor - WHITE\nSize - XL18/20\nUpc/ean (gtin) - 198740883769\nLabel id - DIL', 96.00, 116.00, 0.00, 0.00, 0.01, 1.16, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(486, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22F-100-PC\nCom style number - BSFJA22F-100\nColor - WHITE\nSize - L14/16\nUpc/ean (gtin) - 198740883738\nLabel id - DIL', 168.00, 188.00, 0.00, 0.00, 0.01, 1.88, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(487, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22F-100-PC\nCom style number - BSFJA22F-100\nColor - WHITE\nSize - M10/12\nUpc/ean (gtin) - 198740883745\nLabel id - DIL', 168.00, 188.00, 0.00, 0.00, 0.01, 1.88, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(488, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA22B\nMaterial number # - 25BSFJA22F-100-PC\nCom style number - BSFJA22F-100\nColor - WHITE\nSize - S8\nUpc/ean (gtin) - 198740883752\nLabel id - DIL', 96.00, 116.00, 0.00, 0.00, 0.01, 1.16, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(489, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA23B\nMaterial number # - 25BSFJA23F-441-PC\nCom style number - BSFJA23F-441\nColor - JADE GREY\nSize - XL18/20\nUpc/ean (gtin) - 198740883721\nLabel id - DIL', 72.00, 92.00, 0.00, 0.00, 0.01, 0.92, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(490, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA23B\nMaterial number # - 25BSFJA23F-441-PC\nCom style number - BSFJA23F-441\nColor - JADE GREY\nSize - L14/16\nUpc/ean (gtin) - 198740883691\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(491, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA23B\nMaterial number # - 25BSFJA23F-441-PC\nCom style number - BSFJA23F-441\nColor - JADE GREY\nSize - M10/12\nUpc/ean (gtin) - 198740883707\nLabel id - DIL', 168.00, 188.00, 0.00, 0.00, 0.01, 1.88, '2026-04-22 03:48:50', '2026-04-22 03:48:50', 'active', 1, 1, 1, 1),
(492, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA23B\nMaterial number # - 25BSFJA23F-441-PC\nCom style number - BSFJA23F-441\nColor - JADE GREY\nSize - S8\nUpc/ean (gtin) - 198740883714\nLabel id - DIL', 96.00, 116.00, 0.00, 0.00, 0.01, 1.16, '2026-04-22 03:48:51', '2026-04-22 03:48:51', 'active', 1, 1, 1, 1),
(493, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA24B\nMaterial number # - 25BSFJA24F-270-PC\nCom style number - BSFJA24F-270\nColor - SAND KHAKI\nSize - XL18/20\nUpc/ean (gtin) - 198740883844\nLabel id - DIL', 72.00, 92.00, 0.00, 0.00, 0.01, 0.92, '2026-04-22 03:48:51', '2026-04-22 03:48:51', 'active', 1, 1, 1, 1),
(494, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA24B\nMaterial number # - 25BSFJA24F-270-PC\nCom style number - BSFJA24F-270\nColor - SAND KHAKI\nSize - L14/16\nUpc/ean (gtin) - 198740883813\nLabel id - DIL', 120.00, 140.00, 0.00, 0.00, 0.01, 1.40, '2026-04-22 03:48:51', '2026-04-22 03:48:51', 'active', 1, 1, 1, 1),
(495, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA24B\nMaterial number # - 25BSFJA24F-270-PC\nCom style number - BSFJA24F-270\nColor - SAND KHAKI\nSize - M10/12\nUpc/ean (gtin) - 198740883820\nLabel id - DIL', 120.00, 140.00, 0.00, 0.00, 0.01, 1.40, '2026-04-22 03:48:51', '2026-04-22 03:48:51', 'active', 1, 1, 1, 1),
(496, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJA24B\nMaterial number # - 25BSFJA24F-270-PC\nCom style number - BSFJA24F-270\nColor - SAND KHAKI\nSize - S8\nUpc/ean (gtin) - 198740883837\nLabel id - DIL', 72.00, 92.00, 0.00, 0.00, 0.01, 0.92, '2026-04-22 03:48:51', '2026-04-22 03:48:51', 'active', 1, 1, 1, 1),
(497, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJB20B\nMaterial number # - 25BSFJB20F-001-PC\nCom style number - BSFJB20F-001\nColor - BLACK\nSize - S8\nUpc/ean (gtin) - 198740882793\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:51', '2026-04-22 03:48:51', 'active', 1, 1, 1, 1),
(498, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJB20B\nMaterial number # - 25BSFJB20F-001-PC\nCom style number - BSFJB20F-001\nColor - BLACK\nSize - M10/12\nUpc/ean (gtin) - 198740882786\nLabel id - DIL', 240.00, 260.00, 0.00, 0.00, 0.01, 2.60, '2026-04-22 03:48:51', '2026-04-22 03:48:51', 'active', 1, 1, 1, 1),
(499, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJB20B\nMaterial number # - 25BSFJB20F-001-PC\nCom style number - BSFJB20F-001\nColor - BLACK\nSize - L14/16\nUpc/ean (gtin) - 198740882779\nLabel id - DIL', 240.00, 260.00, 0.00, 0.00, 0.01, 2.60, '2026-04-22 03:48:51', '2026-04-22 03:48:51', 'active', 1, 1, 1, 1),
(500, 2, 0, 'Trim code - 124650-Price Sticker\nPlm item number - BSFJB20B\nMaterial number # - 25BSFJB20F-001-PC\nCom style number - BSFJB20F-001\nColor - BLACK\nSize - XL18/20\nUpc/ean (gtin) - 198740882809\nLabel id - DIL', 144.00, 164.00, 0.00, 0.00, 0.01, 1.64, '2026-04-22 03:48:51', '2026-04-22 03:48:51', 'active', 1, 1, 1, 1),
(502, 3, 1, '', 200.00, 250.00, 0.00, 250.00, 300.00, 75000.00, '2026-07-12 17:35:04', '2026-07-13 12:16:51', 'active', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Structure for view `fund_account_balance`
--
DROP TABLE IF EXISTS `fund_account_balance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `fund_account_balance`  AS SELECT `a`.`id` AS `account_id`, `a`.`account_name` AS `account_name`, `a`.`opening_balance` AS `opening_balance`, `a`.`opening_balance_type` AS `opening_balance_type`, ifnull(sum((case when (`p`.`payment_type` = 'Receive') then (`p`.`amount` + ifnull(`p`.`discount`,0)) else 0 end)),0) AS `total_income`, ifnull(sum((case when (`p`.`payment_type` = 'Pay') then `p`.`amount` else 0 end)),0) AS `total_expense`, (((case when (`a`.`opening_balance_type` = 'Credit') then -(`a`.`opening_balance`) else `a`.`opening_balance` end) + ifnull(sum((case when (`p`.`payment_type` = 'Receive') then (`p`.`amount` + ifnull(`p`.`discount`,0)) else 0 end)),0)) - ifnull(sum((case when (`p`.`payment_type` = 'Pay') then `p`.`amount` else 0 end)),0)) AS `current_balance` FROM (`accounts` `a` left join `payments` `p` on(((`p`.`fund_account_id` = `a`.`id`) and (`p`.`status` = 'active') and (`p`.`deleted_at` is null)))) WHERE ((`a`.`is_fund_account` = 1) AND (`a`.`status` = 'active') AND (`a`.`deleted_at` is null)) GROUP BY `a`.`id`, `a`.`account_name`, `a`.`opening_balance`, `a`.`opening_balance_type` ;

-- --------------------------------------------------------

--
-- Structure for view `item_stock_summaries`
--
DROP TABLE IF EXISTS `item_stock_summaries`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `item_stock_summaries`  AS SELECT `stock_transactions`.`item_id` AS `item_id`, sum(`stock_transactions`.`qty_in`) AS `total_qty_in`, sum(`stock_transactions`.`qty_out`) AS `total_qty_out`, (sum(`stock_transactions`.`qty_in`) - sum(`stock_transactions`.`qty_out`)) AS `current_stock` FROM `stock_transactions` WHERE (`stock_transactions`.`status` = 'active') GROUP BY `stock_transactions`.`item_id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admins_role_id_index` (`role_id`);

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `challans`
--
ALTER TABLE `challans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `challan_details`
--
ALTER TABLE `challan_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `commissions`
--
ALTER TABLE `commissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contents`
--
ALTER TABLE `contents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `content_files`
--
ALTER TABLE `content_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `content_files_content_id_foreign` (`content_id`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `designations`
--
ALTER TABLE `designations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `districts`
--
ALTER TABLE `districts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_salaries`
--
ALTER TABLE `employee_salaries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expense_details`
--
ALTER TABLE `expense_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `financial_years`
--
ALTER TABLE `financial_years`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fund_transfers`
--
ALTER TABLE `fund_transfers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `help_infos`
--
ALTER TABLE `help_infos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice_details`
--
ALTER TABLE `invoice_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `issues`
--
ALTER TABLE `issues`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `issue_details`
--
ALTER TABLE `issue_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`);

--
-- Indexes for table `item_prices`
--
ALTER TABLE `item_prices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loan_infos`
--
ALTER TABLE `loan_infos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loan_installments`
--
ALTER TABLE `loan_installments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media_validators`
--
ALTER TABLE `media_validators`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `role_permissions_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `salary_parameters`
--
ALTER TABLE `salary_parameters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `salary_sheets`
--
ALTER TABLE `salary_sheets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `salary_sheet_details`
--
ALTER TABLE `salary_sheet_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sizes`
--
ALTER TABLE `sizes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_login_histories`
--
ALTER TABLE `user_login_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `voucher_details`
--
ALTER TABLE `voucher_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `withdrawls`
--
ALTER TABLE `withdrawls`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `workorders`
--
ALTER TABLE `workorders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `workorder_details`
--
ALTER TABLE `workorder_details`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `areas`
--
ALTER TABLE `areas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `challans`
--
ALTER TABLE `challans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `challan_details`
--
ALTER TABLE `challan_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=172;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commissions`
--
ALTER TABLE `commissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contents`
--
ALTER TABLE `contents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `content_files`
--
ALTER TABLE `content_files`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `designations`
--
ALTER TABLE `designations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `districts`
--
ALTER TABLE `districts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_salaries`
--
ALTER TABLE `employee_salaries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `expense_details`
--
ALTER TABLE `expense_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `financial_years`
--
ALTER TABLE `financial_years`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `fund_transfers`
--
ALTER TABLE `fund_transfers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `help_infos`
--
ALTER TABLE `help_infos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `invoice_details`
--
ALTER TABLE `invoice_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `issues`
--
ALTER TABLE `issues`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issue_details`
--
ALTER TABLE `issue_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `item_prices`
--
ALTER TABLE `item_prices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_infos`
--
ALTER TABLE `loan_infos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_installments`
--
ALTER TABLE `loan_installments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_validators`
--
ALTER TABLE `media_validators`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment_details`
--
ALTER TABLE `payment_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=629;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_details`
--
ALTER TABLE `purchase_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `salary_parameters`
--
ALTER TABLE `salary_parameters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `salary_sheets`
--
ALTER TABLE `salary_sheets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_sheet_details`
--
ALTER TABLE `salary_sheet_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sizes`
--
ALTER TABLE `sizes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_login_histories`
--
ALTER TABLE `user_login_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `voucher_details`
--
ALTER TABLE `voucher_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `withdrawls`
--
ALTER TABLE `withdrawls`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `workorders`
--
ALTER TABLE `workorders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `workorder_details`
--
ALTER TABLE `workorder_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=503;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `content_files`
--
ALTER TABLE `content_files`
  ADD CONSTRAINT `content_files_content_id_foreign` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
