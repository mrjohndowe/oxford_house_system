-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 13, 2026 at 05:24 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eagles_nest`
--
CREATE DATABASE IF NOT EXISTS `eagles_nest` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `eagles_nest`;

-- --------------------------------------------------------

--
-- Table structure for table `bedroom_essentials_checklists`
--

CREATE TABLE `bedroom_essentials_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `checklist_key` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Bedroom Essentials – Checklist',
  `items_json` longtext NOT NULL,
  `dark_mode` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chapter_meeting_minutes`
--

CREATE TABLE `chapter_meeting_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `meeting_date` varchar(50) NOT NULL,
  `form_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ees_member_ledger`
--

CREATE TABLE `ees_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(2) NOT NULL DEFAULT '',
  `move_in_day` varchar(2) NOT NULL DEFAULT '',
  `move_in_year` varchar(4) NOT NULL DEFAULT '',
  `ledger_rows` longtext DEFAULT NULL,
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_ledger_records`
--

CREATE TABLE `house_ledger_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `notes` mediumtext DEFAULT NULL,
  `rows_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_tour_forms`
--

CREATE TABLE `house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `tour_date` date DEFAULT NULL,
  `tour_time` varchar(20) NOT NULL DEFAULT '',
  `smoking_area` varchar(10) NOT NULL DEFAULT '',
  `notes` text DEFAULT NULL,
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `signature` varchar(255) NOT NULL DEFAULT '',
  `items_json` longtext DEFAULT NULL,
  `section_totals_json` text DEFAULT NULL,
  `grand_total` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_houses`
--

CREATE TABLE `house_visit_houses` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(150) NOT NULL,
  `meeting_day` varchar(20) NOT NULL DEFAULT '',
  `meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_reports`
--

CREATE TABLE `house_visit_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(100) NOT NULL DEFAULT '',
  `president` varchar(255) NOT NULL DEFAULT '',
  `secretary` varchar(255) NOT NULL DEFAULT '',
  `treasurer` varchar(255) NOT NULL DEFAULT '',
  `comptroller` varchar(255) NOT NULL DEFAULT '',
  `coordinator` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep` varchar(255) NOT NULL DEFAULT '',
  `overall_appearance` varchar(10) NOT NULL DEFAULT '',
  `overall_appearance_comments` text DEFAULT NULL,
  `members_behind_ees` decimal(10,2) DEFAULT NULL,
  `total_amount_owed` decimal(12,2) DEFAULT NULL,
  `rent_paid_monthly` decimal(12,2) DEFAULT NULL,
  `ees_paid_weekly` decimal(12,2) DEFAULT NULL,
  `utilities_monthly` decimal(12,2) DEFAULT NULL,
  `house_business_meeting` varchar(10) NOT NULL DEFAULT '',
  `house_business_comments` text DEFAULT NULL,
  `rating_reading_traditions` varchar(10) NOT NULL DEFAULT '',
  `rating_reading_minutes` varchar(10) NOT NULL DEFAULT '',
  `rating_treasurer_report` varchar(10) NOT NULL DEFAULT '',
  `rating_comptroller_report` varchar(10) NOT NULL DEFAULT '',
  `rating_coordinator_report` varchar(10) NOT NULL DEFAULT '',
  `rating_maintains_guidelines` varchar(10) NOT NULL DEFAULT '',
  `rating_handling_business` varchar(10) NOT NULL DEFAULT '',
  `rating_organization_order` varchar(10) NOT NULL DEFAULT '',
  `financial_comments` text DEFAULT NULL,
  `first_visit_date` varchar(50) NOT NULL DEFAULT '',
  `narcan_present` varchar(10) NOT NULL DEFAULT '',
  `narcan_trained` varchar(10) NOT NULL DEFAULT '',
  `follow_up_visit_dates` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep_signature` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_schedules`
--

CREATE TABLE `house_visit_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `schedule_label` varchar(150) NOT NULL,
  `base_month` varchar(7) NOT NULL DEFAULT '',
  `month_count` int(11) NOT NULL DEFAULT 1,
  `repeat_cycle` tinyint(1) NOT NULL DEFAULT 0,
  `step_size` int(11) NOT NULL DEFAULT 1,
  `month_label` varchar(50) NOT NULL,
  `month_index` int(11) NOT NULL DEFAULT 1,
  `visiting_house` varchar(150) NOT NULL,
  `host_house` varchar(150) NOT NULL,
  `host_meeting_day` varchar(20) NOT NULL DEFAULT '',
  `host_meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `housing_service_representative_reports`
--

CREATE TABLE `housing_service_representative_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `week_from` varchar(100) NOT NULL DEFAULT '',
  `week_to` varchar(100) NOT NULL DEFAULT '',
  `house_visit` text DEFAULT NULL,
  `audit_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `audit_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `next_chapter_meeting` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `next_state_meeting` text DEFAULT NULL,
  `number_of_applications` text DEFAULT NULL,
  `number_contacted_plan` longtext DEFAULT NULL,
  `new_members` longtext DEFAULT NULL,
  `interviews_setup` longtext DEFAULT NULL,
  `chapter_news` longtext DEFAULT NULL,
  `chapter_meeting_recap` longtext DEFAULT NULL,
  `upcoming_unity` longtext DEFAULT NULL,
  `upcoming_presentations` longtext DEFAULT NULL,
  `hsr_name` varchar(255) NOT NULL DEFAULT '',
  `report_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hsc_meeting_minutes_json`
--

CREATE TABLE `hsc_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `landlord_verification_forms`
--

CREATE TABLE `landlord_verification_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_name` varchar(255) NOT NULL DEFAULT '',
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `landlord_manager_name` varchar(255) NOT NULL DEFAULT '',
  `manager_street` varchar(255) NOT NULL DEFAULT '',
  `manager_city` varchar(150) NOT NULL DEFAULT '',
  `manager_state` varchar(50) NOT NULL DEFAULT '',
  `manager_zip` varchar(25) NOT NULL DEFAULT '',
  `manager_county` varchar(150) NOT NULL DEFAULT '',
  `manager_phone` varchar(50) NOT NULL DEFAULT '',
  `manager_email` varchar(255) NOT NULL DEFAULT '',
  `rental_street` varchar(255) NOT NULL DEFAULT '',
  `rental_apt_lot` varchar(100) NOT NULL DEFAULT '',
  `rental_city` varchar(150) NOT NULL DEFAULT '',
  `rental_state` varchar(50) NOT NULL DEFAULT '',
  `rental_zip` varchar(25) NOT NULL DEFAULT '',
  `rental_county` varchar(150) NOT NULL DEFAULT '',
  `bedrooms` varchar(20) NOT NULL DEFAULT '',
  `lease_start_date` date DEFAULT NULL,
  `lease_end_date` date DEFAULT NULL,
  `monthly_rent_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `next_payment_due_date` date DEFAULT NULL,
  `last_payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `last_payment_date` date DEFAULT NULL,
  `tenant_in_arrears` tinyint(1) NOT NULL DEFAULT 0,
  `amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `arrears_period_from` date DEFAULT NULL,
  `arrears_period_to` date DEFAULT NULL,
  `receiving_other_assistance` tinyint(1) NOT NULL DEFAULT 0,
  `other_assistance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_assistance_period` varchar(100) NOT NULL DEFAULT '',
  `payment_method` varchar(20) NOT NULL DEFAULT 'check',
  `check_payable_to` varchar(255) NOT NULL DEFAULT '',
  `send_to_landlord_address` tinyint(1) NOT NULL DEFAULT 1,
  `alternative_address` text DEFAULT NULL,
  `cert_name` varchar(255) NOT NULL DEFAULT '',
  `cert_title` varchar(255) NOT NULL DEFAULT '',
  `cert_signature` varchar(255) NOT NULL DEFAULT '',
  `cert_date` date DEFAULT NULL,
  `calc_balance_remaining` decimal(10,2) NOT NULL DEFAULT 0.00,
  `calc_total_assistance_gap` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheets`
--

CREATE TABLE `medication_count_sheets` (
  `id` int(10) UNSIGNED NOT NULL,
  `resident_name` varchar(255) NOT NULL,
  `sheet_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheet_rows`
--

CREATE TABLE `medication_count_sheet_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `sheet_id` int(10) UNSIGNED NOT NULL,
  `row_number` int(11) NOT NULL,
  `entry_date` varchar(50) DEFAULT NULL,
  `medication_name` varchar(255) DEFAULT NULL,
  `dosage` varchar(255) DEFAULT NULL,
  `frequency` varchar(255) DEFAULT NULL,
  `previous_count` varchar(100) DEFAULT NULL,
  `current_count` varchar(100) DEFAULT NULL,
  `member_initials` varchar(50) DEFAULT NULL,
  `witness_initials` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `new_house_tour_forms`
--

CREATE TABLE `new_house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `form_date` varchar(50) NOT NULL DEFAULT '',
  `form_time` varchar(50) NOT NULL DEFAULT '',
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `inspector_signature` varchar(255) NOT NULL DEFAULT '',
  `smoking_area_score` varchar(5) NOT NULL DEFAULT '',
  `smoking_area_comment` text DEFAULT NULL,
  `notes_text` text DEFAULT NULL,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_average` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payload_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nightly_kitchen_schedules`
--

CREATE TABLE `nightly_kitchen_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `trash_to_curb_on` varchar(255) NOT NULL DEFAULT '',
  `schedule_data` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_chore_lists`
--

CREATE TABLE `oxford_chore_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT 'CHORE LIST',
  `week1_start` varchar(20) NOT NULL DEFAULT '',
  `week2_start` varchar(20) NOT NULL DEFAULT '',
  `week3_start` varchar(20) NOT NULL DEFAULT '',
  `week4_start` varchar(20) NOT NULL DEFAULT '',
  `week5_start` varchar(20) NOT NULL DEFAULT '',
  `week6_start` varchar(20) NOT NULL DEFAULT '',
  `week7_start` varchar(20) NOT NULL DEFAULT '',
  `week8_start` varchar(20) NOT NULL DEFAULT '',
  `form_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_disruptive_contracts`
--

CREATE TABLE `oxford_disruptive_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_subject` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(50) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `behavior_1` text DEFAULT NULL,
  `behavior_2` text DEFAULT NULL,
  `behavior_3` text DEFAULT NULL,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgment_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(50) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_original_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_stored_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_mime` varchar(100) NOT NULL DEFAULT '',
  `uploaded_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_financial_audits`
--

CREATE TABLE `oxford_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `date_completed` date DEFAULT NULL,
  `bank_statement_ending_date` date DEFAULT NULL,
  `bank_statement_ending_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_past_due_bills` decimal(12,2) NOT NULL DEFAULT 0.00,
  `savings_account_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_outstanding_ees` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deposits_json` longtext DEFAULT NULL,
  `checks_json` longtext DEFAULT NULL,
  `treasurer_signature` varchar(255) DEFAULT NULL,
  `comptroller_signature` varchar(255) DEFAULT NULL,
  `president_signature` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_audits`
--

CREATE TABLE `oxford_house_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `completed_month` varchar(10) NOT NULL DEFAULT '',
  `completed_day` varchar(10) NOT NULL DEFAULT '',
  `completed_year` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_month` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_day` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_year` varchar(10) NOT NULL DEFAULT '',
  `past_due_bills` varchar(50) NOT NULL DEFAULT '',
  `savings_balance` varchar(50) NOT NULL DEFAULT '',
  `outstanding_ees` varchar(50) NOT NULL DEFAULT '',
  `bank_statement_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `deposits_total` varchar(50) NOT NULL DEFAULT '',
  `checks_total` varchar(50) NOT NULL DEFAULT '',
  `balance_after_audit` varchar(50) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_signature` varchar(255) NOT NULL DEFAULT '',
  `comptroller_signature` varchar(255) NOT NULL DEFAULT '',
  `president_signature` varchar(255) NOT NULL DEFAULT '',
  `checks_rows` longtext DEFAULT NULL,
  `deposits_rows` longtext DEFAULT NULL,
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `scan_stored_name` varchar(255) NOT NULL DEFAULT '',
  `scan_path` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_reports`
--

CREATE TABLE `oxford_house_financial_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `report_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_ledger_forms`
--

CREATE TABLE `oxford_house_ledger_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `rows_json` longtext DEFAULT NULL,
  `totals_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_meeting_minutes_json`
--

CREATE TABLE `oxford_house_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_member_ledger`
--

CREATE TABLE `oxford_house_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(10) NOT NULL DEFAULT '',
  `move_in_day` varchar(10) NOT NULL DEFAULT '',
  `move_in_year` varchar(10) NOT NULL DEFAULT '',
  `row_1` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_1`)),
  `row_2` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_2`)),
  `row_3` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_3`)),
  `row_4` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_4`)),
  `row_5` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_5`)),
  `row_6` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_6`)),
  `row_7` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_7`)),
  `row_8` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_8`)),
  `row_9` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_9`)),
  `row_10` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_10`)),
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date_paid` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_minutes`
--

CREATE TABLE `oxford_house_minutes` (
  `id` int(11) NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `meeting_date` varchar(50) DEFAULT NULL,
  `start_time` varchar(50) DEFAULT NULL,
  `tradition_number` varchar(50) DEFAULT NULL,
  `meeting_type_regular` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_emergency` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_interview` tinyint(1) NOT NULL DEFAULT 0,
  `minutes_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `amend_yes` tinyint(1) NOT NULL DEFAULT 0,
  `amend_no` tinyint(1) NOT NULL DEFAULT 0,
  `checking_0` varchar(50) DEFAULT NULL,
  `checking_1` varchar(50) DEFAULT NULL,
  `checking_2` varchar(50) DEFAULT NULL,
  `checking_3` varchar(50) DEFAULT NULL,
  `savings_0` varchar(50) DEFAULT NULL,
  `savings_1` varchar(50) DEFAULT NULL,
  `savings_2` varchar(50) DEFAULT NULL,
  `savings_3` varchar(50) DEFAULT NULL,
  `savings_4` varchar(50) DEFAULT NULL,
  `petty_0` varchar(50) DEFAULT NULL,
  `petty_1` varchar(50) DEFAULT NULL,
  `petty_2` varchar(50) DEFAULT NULL,
  `petty_3` varchar(50) DEFAULT NULL,
  `petty_receipts_yes` tinyint(1) NOT NULL DEFAULT 0,
  `petty_receipts_no` tinyint(1) NOT NULL DEFAULT 0,
  `treasurer_comments` text DEFAULT NULL,
  `treasurer_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `comptroller_comments` text DEFAULT NULL,
  `comptroller_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `ees_plan` longtext DEFAULT NULL,
  `coordinator_report` text DEFAULT NULL,
  `coordinator_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `housing_services_report` text DEFAULT NULL,
  `hsr_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_n` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_n` tinyint(1) NOT NULL DEFAULT 0,
  `unfinished_business` text DEFAULT NULL,
  `vacancy_updated_y` tinyint(1) NOT NULL DEFAULT 0,
  `vacancy_updated_n` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_y` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_n` tinyint(1) NOT NULL DEFAULT 0,
  `new_business` longtext DEFAULT NULL,
  `adjourn_hour` varchar(10) DEFAULT NULL,
  `adjourn_min` varchar(10) DEFAULT NULL,
  `secretary_name` varchar(255) DEFAULT NULL,
  `secretary_signature` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `roll_name_1` text DEFAULT NULL,
  `roll_y_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_2` text DEFAULT NULL,
  `roll_y_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_3` text DEFAULT NULL,
  `roll_y_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_4` text DEFAULT NULL,
  `roll_y_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_5` text DEFAULT NULL,
  `roll_y_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_6` text DEFAULT NULL,
  `roll_y_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_7` text DEFAULT NULL,
  `roll_y_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_8` text DEFAULT NULL,
  `roll_y_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_9` text DEFAULT NULL,
  `roll_y_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_10` text DEFAULT NULL,
  `roll_y_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_11` text DEFAULT NULL,
  `roll_y_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_12` text DEFAULT NULL,
  `roll_y_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_13` text DEFAULT NULL,
  `roll_y_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_14` text DEFAULT NULL,
  `roll_y_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_15` text DEFAULT NULL,
  `roll_y_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_16` text DEFAULT NULL,
  `roll_y_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_17` text DEFAULT NULL,
  `roll_y_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_18` text DEFAULT NULL,
  `roll_y_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_19` text DEFAULT NULL,
  `roll_y_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_20` text DEFAULT NULL,
  `roll_y_20` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_20` tinyint(1) NOT NULL DEFAULT 0,
  `comp_name_1` text DEFAULT NULL,
  `comp_bal_1` text DEFAULT NULL,
  `comp_name_2` text DEFAULT NULL,
  `comp_bal_2` text DEFAULT NULL,
  `comp_name_3` text DEFAULT NULL,
  `comp_bal_3` text DEFAULT NULL,
  `comp_name_4` text DEFAULT NULL,
  `comp_bal_4` text DEFAULT NULL,
  `comp_name_5` text DEFAULT NULL,
  `comp_bal_5` text DEFAULT NULL,
  `comp_name_6` text DEFAULT NULL,
  `comp_bal_6` text DEFAULT NULL,
  `comp_name_7` text DEFAULT NULL,
  `comp_bal_7` text DEFAULT NULL,
  `comp_name_8` text DEFAULT NULL,
  `comp_bal_8` text DEFAULT NULL,
  `comp_name_9` text DEFAULT NULL,
  `comp_bal_9` text DEFAULT NULL,
  `comp_name_10` text DEFAULT NULL,
  `comp_bal_10` text DEFAULT NULL,
  `comp_name_11` text DEFAULT NULL,
  `comp_bal_11` text DEFAULT NULL,
  `comp_name_12` text DEFAULT NULL,
  `comp_bal_12` text DEFAULT NULL,
  `comp_name_13` text DEFAULT NULL,
  `comp_bal_13` text DEFAULT NULL,
  `comp_name_14` text DEFAULT NULL,
  `comp_bal_14` text DEFAULT NULL,
  `comp_name_15` text DEFAULT NULL,
  `comp_bal_15` text DEFAULT NULL,
  `comp_name_16` text DEFAULT NULL,
  `comp_bal_16` text DEFAULT NULL,
  `comp_name_17` text DEFAULT NULL,
  `comp_bal_17` text DEFAULT NULL,
  `comp_name_18` text DEFAULT NULL,
  `comp_bal_18` text DEFAULT NULL,
  `comp_name_19` text DEFAULT NULL,
  `comp_bal_19` text DEFAULT NULL,
  `comp_name_20` text DEFAULT NULL,
  `comp_bal_20` text DEFAULT NULL,
  `secretary_signed_date` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_interview_minutes`
--

CREATE TABLE `oxford_interview_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `interview_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `interview_date` date DEFAULT NULL,
  `interviewer_name` varchar(255) NOT NULL DEFAULT '',
  `contact_phone` varchar(100) NOT NULL DEFAULT '',
  `outcome_status` varchar(100) NOT NULL DEFAULT '',
  `vote_percent` varchar(50) NOT NULL DEFAULT '',
  `move_in_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `intro_notes` mediumtext DEFAULT NULL,
  `interviewer_do_notes` mediumtext DEFAULT NULL,
  `interviewer_dont_notes` mediumtext DEFAULT NULL,
  `closing_notes` mediumtext DEFAULT NULL,
  `roll_name_1` varchar(255) NOT NULL DEFAULT '',
  `roll_present_1` varchar(20) NOT NULL DEFAULT '',
  `roll_name_2` varchar(255) NOT NULL DEFAULT '',
  `roll_present_2` varchar(20) NOT NULL DEFAULT '',
  `roll_name_3` varchar(255) NOT NULL DEFAULT '',
  `roll_present_3` varchar(20) NOT NULL DEFAULT '',
  `roll_name_4` varchar(255) NOT NULL DEFAULT '',
  `roll_present_4` varchar(20) NOT NULL DEFAULT '',
  `roll_name_5` varchar(255) NOT NULL DEFAULT '',
  `roll_present_5` varchar(20) NOT NULL DEFAULT '',
  `roll_name_6` varchar(255) NOT NULL DEFAULT '',
  `roll_present_6` varchar(20) NOT NULL DEFAULT '',
  `roll_name_7` varchar(255) NOT NULL DEFAULT '',
  `roll_present_7` varchar(20) NOT NULL DEFAULT '',
  `roll_name_8` varchar(255) NOT NULL DEFAULT '',
  `roll_present_8` varchar(20) NOT NULL DEFAULT '',
  `roll_name_9` varchar(255) NOT NULL DEFAULT '',
  `roll_present_9` varchar(20) NOT NULL DEFAULT '',
  `roll_name_10` varchar(255) NOT NULL DEFAULT '',
  `roll_present_10` varchar(20) NOT NULL DEFAULT '',
  `roll_name_11` varchar(255) NOT NULL DEFAULT '',
  `roll_present_11` varchar(20) NOT NULL DEFAULT '',
  `roll_name_12` varchar(255) NOT NULL DEFAULT '',
  `roll_present_12` varchar(20) NOT NULL DEFAULT '',
  `q1` mediumtext DEFAULT NULL,
  `q2` mediumtext DEFAULT NULL,
  `q3` mediumtext DEFAULT NULL,
  `q4` mediumtext DEFAULT NULL,
  `q5` mediumtext DEFAULT NULL,
  `q6` mediumtext DEFAULT NULL,
  `q7` mediumtext DEFAULT NULL,
  `q8` mediumtext DEFAULT NULL,
  `q9` mediumtext DEFAULT NULL,
  `q10` mediumtext DEFAULT NULL,
  `q11` mediumtext DEFAULT NULL,
  `q12` mediumtext DEFAULT NULL,
  `q13` mediumtext DEFAULT NULL,
  `q14` mediumtext DEFAULT NULL,
  `q15` mediumtext DEFAULT NULL,
  `q16` mediumtext DEFAULT NULL,
  `q17` mediumtext DEFAULT NULL,
  `q18` mediumtext DEFAULT NULL,
  `q19` mediumtext DEFAULT NULL,
  `q20` mediumtext DEFAULT NULL,
  `q21` mediumtext DEFAULT NULL,
  `q22` mediumtext DEFAULT NULL,
  `q23` mediumtext DEFAULT NULL,
  `q24` mediumtext DEFAULT NULL,
  `q25` mediumtext DEFAULT NULL,
  `q26` mediumtext DEFAULT NULL,
  `q27` mediumtext DEFAULT NULL,
  `q28` mediumtext DEFAULT NULL,
  `q29` mediumtext DEFAULT NULL,
  `q30` mediumtext DEFAULT NULL,
  `q31` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_member_financial_contracts`
--

CREATE TABLE `oxford_member_financial_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(100) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `total_amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgement_name` varchar(255) NOT NULL DEFAULT '',
  `signature_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(100) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `scanned_contract` varchar(255) NOT NULL DEFAULT '',
  `contract_stamp` varchar(50) NOT NULL DEFAULT '',
  `contract_stamp_at` datetime DEFAULT NULL,
  `contract_stamp_by_ip` varchar(64) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_new_member_packets`
--

CREATE TABLE `oxford_new_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `packet_date` date DEFAULT NULL,
  `check_membership_application` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_manual` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_guidelines` tinyint(1) NOT NULL DEFAULT 0,
  `check_membership_agreement` tinyint(1) NOT NULL DEFAULT 0,
  `check_plan_for_recovery` tinyint(1) NOT NULL DEFAULT 0,
  `check_relapse_contingency` tinyint(1) NOT NULL DEFAULT 0,
  `check_medical_release` tinyint(1) NOT NULL DEFAULT 0,
  `check_property_list` tinyint(1) NOT NULL DEFAULT 0,
  `member_initials_1` varchar(20) NOT NULL DEFAULT '',
  `president_initials_1` varchar(20) NOT NULL DEFAULT '',
  `member_initials_2` varchar(20) NOT NULL DEFAULT '',
  `president_initials_2` varchar(20) NOT NULL DEFAULT '',
  `member_initials_3` varchar(20) NOT NULL DEFAULT '',
  `president_initials_3` varchar(20) NOT NULL DEFAULT '',
  `member_initials_4` varchar(20) NOT NULL DEFAULT '',
  `president_initials_4` varchar(20) NOT NULL DEFAULT '',
  `member_initials_5` varchar(20) NOT NULL DEFAULT '',
  `president_initials_5` varchar(20) NOT NULL DEFAULT '',
  `member_initials_6` varchar(20) NOT NULL DEFAULT '',
  `president_initials_6` varchar(20) NOT NULL DEFAULT '',
  `member_initials_7` varchar(20) NOT NULL DEFAULT '',
  `president_initials_7` varchar(20) NOT NULL DEFAULT '',
  `member_initials_8` varchar(20) NOT NULL DEFAULT '',
  `president_initials_8` varchar(20) NOT NULL DEFAULT '',
  `member_signature_1` varchar(255) NOT NULL DEFAULT '',
  `member_signature_date_1` date DEFAULT NULL,
  `president_signature_1` varchar(255) NOT NULL DEFAULT '',
  `president_signature_date_1` date DEFAULT NULL,
  `membership_agreement_text` longtext DEFAULT NULL,
  `agreement_member_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_date` date DEFAULT NULL,
  `agreement_president_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_date` date DEFAULT NULL,
  `plan_name` varchar(255) NOT NULL DEFAULT '',
  `plan_text` longtext DEFAULT NULL,
  `aftercare_program` longtext DEFAULT NULL,
  `has_sponsor` varchar(10) NOT NULL DEFAULT '',
  `sponsor_by_date` date DEFAULT NULL,
  `meetings_per_week` varchar(50) NOT NULL DEFAULT '',
  `meeting_types` varchar(255) NOT NULL DEFAULT '',
  `plan_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_signature_date` date DEFAULT NULL,
  `plan_president_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_president_date` date DEFAULT NULL,
  `relapse_name` varchar(255) NOT NULL DEFAULT '',
  `relapse_family` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_friend` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_detox` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other_text` varchar(255) NOT NULL DEFAULT '',
  `relapse_details` longtext DEFAULT NULL,
  `notify_rows_json` longtext DEFAULT NULL,
  `pickup_rows_json` longtext DEFAULT NULL,
  `relapse_member_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_member_date` date DEFAULT NULL,
  `relapse_president_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_president_date` date DEFAULT NULL,
  `relapse_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_witness_date` date DEFAULT NULL,
  `medical_name` varchar(255) NOT NULL DEFAULT '',
  `physician_name` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(50) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance_info` varchar(255) NOT NULL DEFAULT '',
  `allergies` longtext DEFAULT NULL,
  `medications` longtext DEFAULT NULL,
  `medical_history` longtext DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `blood_type` varchar(20) NOT NULL DEFAULT '',
  `medical_contacts_json` longtext DEFAULT NULL,
  `medical_signature` varchar(255) NOT NULL DEFAULT '',
  `medical_date` date DEFAULT NULL,
  `property_name` varchar(255) NOT NULL DEFAULT '',
  `property_move_in_date` date DEFAULT NULL,
  `property_rows_json` longtext DEFAULT NULL,
  `uploaded_copy_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_path` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_mime` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_red_creek_member_packets`
--

CREATE TABLE `oxford_red_creek_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `signature_date` date DEFAULT NULL,
  `refund_move_in_date` date DEFAULT NULL,
  `new_member_signature` varchar(255) NOT NULL DEFAULT '',
  `new_member_signature_date` date DEFAULT NULL,
  `president_hsr_signature` varchar(255) NOT NULL DEFAULT '',
  `president_hsr_signature_date` date DEFAULT NULL,
  `expectations_signature` varchar(255) NOT NULL DEFAULT '',
  `expectations_signature_date` date DEFAULT NULL,
  `medication_signature` varchar(255) NOT NULL DEFAULT '',
  `medication_signature_date` date DEFAULT NULL,
  `emergency_name` varchar(255) NOT NULL DEFAULT '',
  `emergency_age` varchar(50) NOT NULL DEFAULT '',
  `emergency_dob` varchar(50) NOT NULL DEFAULT '',
  `blood_type` varchar(50) NOT NULL DEFAULT '',
  `primary_physician` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(100) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance` varchar(255) NOT NULL DEFAULT '',
  `allergies` text DEFAULT NULL,
  `medications` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `contact1_name` varchar(255) NOT NULL DEFAULT '',
  `contact1_phone` varchar(100) NOT NULL DEFAULT '',
  `contact2_name` varchar(255) NOT NULL DEFAULT '',
  `contact2_phone` varchar(100) NOT NULL DEFAULT '',
  `contact3_name` varchar(255) NOT NULL DEFAULT '',
  `contact3_phone` varchar(100) NOT NULL DEFAULT '',
  `property_items` longtext DEFAULT NULL,
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `property_signature` varchar(255) NOT NULL DEFAULT '',
  `property_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `property_date` date DEFAULT NULL,
  `property_removed_date` date DEFAULT NULL,
  `property_removed_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `move_in_fee` decimal(10,2) DEFAULT 250.00,
  `other_charge` decimal(10,2) DEFAULT 0.00,
  `total_due` decimal(10,2) DEFAULT NULL,
  `scan_path` varchar(500) NOT NULL DEFAULT '',
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_residency_forms`
--

CREATE TABLE `oxford_residency_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) DEFAULT '',
  `letter_date` varchar(20) DEFAULT '',
  `house_name` varchar(255) DEFAULT '',
  `accepted_date` varchar(20) DEFAULT '',
  `address_line` varchar(255) DEFAULT '',
  `city_state_zip` varchar(255) DEFAULT '',
  `move_in_fee` decimal(10,2) DEFAULT 0.00,
  `weekly_rent` decimal(10,2) DEFAULT 0.00,
  `president_contact` varchar(255) DEFAULT '',
  `president_name` varchar(255) DEFAULT '',
  `president_signature` varchar(255) DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_shopping_lists`
--

CREATE TABLE `oxford_shopping_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `shopping_date` date DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Oxford House Shopping List',
  `items_json` longtext NOT NULL,
  `checked_json` longtext NOT NULL,
  `total_checked` int(11) NOT NULL DEFAULT 0,
  `total_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_items` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy_path` varchar(500) DEFAULT NULL,
  `uploaded_copy_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledgers`
--

CREATE TABLE `petty_cash_ledgers` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `ledger_date` date DEFAULT NULL,
  `beginning_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledger_rows`
--

CREATE TABLE `petty_cash_ledger_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `ledger_id` int(10) UNSIGNED NOT NULL,
  `row_index` int(11) NOT NULL,
  `txn_date` varchar(50) NOT NULL DEFAULT '',
  `products_purchased` varchar(255) NOT NULL DEFAULT '',
  `vendor` varchar(255) NOT NULL DEFAULT '',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reimbursement_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `safety_inspection_checklists`
--

CREATE TABLE `safety_inspection_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `inspection_date` date DEFAULT NULL,
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `checklist_json` longtext NOT NULL,
  `satisfactory_total` int(11) NOT NULL DEFAULT 0,
  `unsatisfactory_total` int(11) NOT NULL DEFAULT 0,
  `completed_total` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy` varchar(500) DEFAULT NULL,
  `original_upload_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_checklist_key` (`checklist_key`);

--
-- Indexes for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_meeting_date` (`meeting_date`);

--
-- Indexes for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_dates` (`week_start`,`week_end`);

--
-- Indexes for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_tour_date` (`tour_date`);

--
-- Indexes for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `house_name` (`house_name`);

--
-- Indexes for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_label` (`schedule_label`);

--
-- Indexes for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`);

--
-- Indexes for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_name_idx` (`tenant_name`),
  ADD KEY `updated_at_idx` (`updated_at`);

--
-- Indexes for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resident_name` (`resident_name`),
  ADD KEY `idx_sheet_date` (`sheet_date`);

--
-- Indexes for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sheet_id` (`sheet_id`),
  ADD KEY `idx_row_number` (`row_number`);

--
-- Indexes for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_form_date` (`form_date`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_contract_date` (`contract_date`);

--
-- Indexes for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date_completed` (`date_completed`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- Indexes for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_dates` (`house_name`,`date_from`,`date_to`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_date_from` (`date_from`),
  ADD KEY `idx_date_to` (`date_to`);

--
-- Indexes for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_start` (`week_start`),
  ADD KEY `idx_week_end` (`week_end`);

--
-- Indexes for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`),
  ADD KEY `idx_house_date` (`house_name`,`meeting_date`);

--
-- Indexes for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_member_date_house` (`member_name`,`contract_date`,`house_name`);

--
-- Indexes for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_member` (`house_name`,`member_name`);

--
-- Indexes for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_shopping_date` (`shopping_date`);

--
-- Indexes for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_date` (`house_name`,`ledger_date`);

--
-- Indexes for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_petty_cash_ledger_rows_ledger` (`ledger_id`);

--
-- Indexes for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inspection_date` (`inspection_date`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `northmoor`
--
CREATE DATABASE IF NOT EXISTS `northmoor` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `northmoor`;

-- --------------------------------------------------------

--
-- Table structure for table `bedroom_essentials_checklists`
--

CREATE TABLE `bedroom_essentials_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `checklist_key` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Bedroom Essentials – Checklist',
  `items_json` longtext NOT NULL,
  `dark_mode` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chapter_meeting_minutes`
--

CREATE TABLE `chapter_meeting_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `meeting_date` varchar(50) NOT NULL,
  `form_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ees_member_ledger`
--

CREATE TABLE `ees_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(2) NOT NULL DEFAULT '',
  `move_in_day` varchar(2) NOT NULL DEFAULT '',
  `move_in_year` varchar(4) NOT NULL DEFAULT '',
  `ledger_rows` longtext DEFAULT NULL,
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_ledger_records`
--

CREATE TABLE `house_ledger_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `notes` mediumtext DEFAULT NULL,
  `rows_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_tour_forms`
--

CREATE TABLE `house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `tour_date` date DEFAULT NULL,
  `tour_time` varchar(20) NOT NULL DEFAULT '',
  `smoking_area` varchar(10) NOT NULL DEFAULT '',
  `notes` text DEFAULT NULL,
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `signature` varchar(255) NOT NULL DEFAULT '',
  `items_json` longtext DEFAULT NULL,
  `section_totals_json` text DEFAULT NULL,
  `grand_total` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_houses`
--

CREATE TABLE `house_visit_houses` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(150) NOT NULL,
  `meeting_day` varchar(20) NOT NULL DEFAULT '',
  `meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_reports`
--

CREATE TABLE `house_visit_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(100) NOT NULL DEFAULT '',
  `president` varchar(255) NOT NULL DEFAULT '',
  `secretary` varchar(255) NOT NULL DEFAULT '',
  `treasurer` varchar(255) NOT NULL DEFAULT '',
  `comptroller` varchar(255) NOT NULL DEFAULT '',
  `coordinator` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep` varchar(255) NOT NULL DEFAULT '',
  `overall_appearance` varchar(10) NOT NULL DEFAULT '',
  `overall_appearance_comments` text DEFAULT NULL,
  `members_behind_ees` decimal(10,2) DEFAULT NULL,
  `total_amount_owed` decimal(12,2) DEFAULT NULL,
  `rent_paid_monthly` decimal(12,2) DEFAULT NULL,
  `ees_paid_weekly` decimal(12,2) DEFAULT NULL,
  `utilities_monthly` decimal(12,2) DEFAULT NULL,
  `house_business_meeting` varchar(10) NOT NULL DEFAULT '',
  `house_business_comments` text DEFAULT NULL,
  `rating_reading_traditions` varchar(10) NOT NULL DEFAULT '',
  `rating_reading_minutes` varchar(10) NOT NULL DEFAULT '',
  `rating_treasurer_report` varchar(10) NOT NULL DEFAULT '',
  `rating_comptroller_report` varchar(10) NOT NULL DEFAULT '',
  `rating_coordinator_report` varchar(10) NOT NULL DEFAULT '',
  `rating_maintains_guidelines` varchar(10) NOT NULL DEFAULT '',
  `rating_handling_business` varchar(10) NOT NULL DEFAULT '',
  `rating_organization_order` varchar(10) NOT NULL DEFAULT '',
  `financial_comments` text DEFAULT NULL,
  `first_visit_date` varchar(50) NOT NULL DEFAULT '',
  `narcan_present` varchar(10) NOT NULL DEFAULT '',
  `narcan_trained` varchar(10) NOT NULL DEFAULT '',
  `follow_up_visit_dates` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep_signature` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_schedules`
--

CREATE TABLE `house_visit_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `schedule_label` varchar(150) NOT NULL,
  `base_month` varchar(7) NOT NULL DEFAULT '',
  `month_count` int(11) NOT NULL DEFAULT 1,
  `repeat_cycle` tinyint(1) NOT NULL DEFAULT 0,
  `step_size` int(11) NOT NULL DEFAULT 1,
  `month_label` varchar(50) NOT NULL,
  `month_index` int(11) NOT NULL DEFAULT 1,
  `visiting_house` varchar(150) NOT NULL,
  `host_house` varchar(150) NOT NULL,
  `host_meeting_day` varchar(20) NOT NULL DEFAULT '',
  `host_meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `housing_service_representative_reports`
--

CREATE TABLE `housing_service_representative_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `week_from` varchar(100) NOT NULL DEFAULT '',
  `week_to` varchar(100) NOT NULL DEFAULT '',
  `house_visit` text DEFAULT NULL,
  `audit_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `audit_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `next_chapter_meeting` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `next_state_meeting` text DEFAULT NULL,
  `number_of_applications` text DEFAULT NULL,
  `number_contacted_plan` longtext DEFAULT NULL,
  `new_members` longtext DEFAULT NULL,
  `interviews_setup` longtext DEFAULT NULL,
  `chapter_news` longtext DEFAULT NULL,
  `chapter_meeting_recap` longtext DEFAULT NULL,
  `upcoming_unity` longtext DEFAULT NULL,
  `upcoming_presentations` longtext DEFAULT NULL,
  `hsr_name` varchar(255) NOT NULL DEFAULT '',
  `report_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hsc_meeting_minutes_json`
--

CREATE TABLE `hsc_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `landlord_verification_forms`
--

CREATE TABLE `landlord_verification_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_name` varchar(255) NOT NULL DEFAULT '',
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `landlord_manager_name` varchar(255) NOT NULL DEFAULT '',
  `manager_street` varchar(255) NOT NULL DEFAULT '',
  `manager_city` varchar(150) NOT NULL DEFAULT '',
  `manager_state` varchar(50) NOT NULL DEFAULT '',
  `manager_zip` varchar(25) NOT NULL DEFAULT '',
  `manager_county` varchar(150) NOT NULL DEFAULT '',
  `manager_phone` varchar(50) NOT NULL DEFAULT '',
  `manager_email` varchar(255) NOT NULL DEFAULT '',
  `rental_street` varchar(255) NOT NULL DEFAULT '',
  `rental_apt_lot` varchar(100) NOT NULL DEFAULT '',
  `rental_city` varchar(150) NOT NULL DEFAULT '',
  `rental_state` varchar(50) NOT NULL DEFAULT '',
  `rental_zip` varchar(25) NOT NULL DEFAULT '',
  `rental_county` varchar(150) NOT NULL DEFAULT '',
  `bedrooms` varchar(20) NOT NULL DEFAULT '',
  `lease_start_date` date DEFAULT NULL,
  `lease_end_date` date DEFAULT NULL,
  `monthly_rent_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `next_payment_due_date` date DEFAULT NULL,
  `last_payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `last_payment_date` date DEFAULT NULL,
  `tenant_in_arrears` tinyint(1) NOT NULL DEFAULT 0,
  `amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `arrears_period_from` date DEFAULT NULL,
  `arrears_period_to` date DEFAULT NULL,
  `receiving_other_assistance` tinyint(1) NOT NULL DEFAULT 0,
  `other_assistance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_assistance_period` varchar(100) NOT NULL DEFAULT '',
  `payment_method` varchar(20) NOT NULL DEFAULT 'check',
  `check_payable_to` varchar(255) NOT NULL DEFAULT '',
  `send_to_landlord_address` tinyint(1) NOT NULL DEFAULT 1,
  `alternative_address` text DEFAULT NULL,
  `cert_name` varchar(255) NOT NULL DEFAULT '',
  `cert_title` varchar(255) NOT NULL DEFAULT '',
  `cert_signature` varchar(255) NOT NULL DEFAULT '',
  `cert_date` date DEFAULT NULL,
  `calc_balance_remaining` decimal(10,2) NOT NULL DEFAULT 0.00,
  `calc_total_assistance_gap` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheets`
--

CREATE TABLE `medication_count_sheets` (
  `id` int(10) UNSIGNED NOT NULL,
  `resident_name` varchar(255) NOT NULL,
  `sheet_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheet_rows`
--

CREATE TABLE `medication_count_sheet_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `sheet_id` int(10) UNSIGNED NOT NULL,
  `row_number` int(11) NOT NULL,
  `entry_date` varchar(50) DEFAULT NULL,
  `medication_name` varchar(255) DEFAULT NULL,
  `dosage` varchar(255) DEFAULT NULL,
  `frequency` varchar(255) DEFAULT NULL,
  `previous_count` varchar(100) DEFAULT NULL,
  `current_count` varchar(100) DEFAULT NULL,
  `member_initials` varchar(50) DEFAULT NULL,
  `witness_initials` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `new_house_tour_forms`
--

CREATE TABLE `new_house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `form_date` varchar(50) NOT NULL DEFAULT '',
  `form_time` varchar(50) NOT NULL DEFAULT '',
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `inspector_signature` varchar(255) NOT NULL DEFAULT '',
  `smoking_area_score` varchar(5) NOT NULL DEFAULT '',
  `smoking_area_comment` text DEFAULT NULL,
  `notes_text` text DEFAULT NULL,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_average` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payload_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nightly_kitchen_schedules`
--

CREATE TABLE `nightly_kitchen_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `trash_to_curb_on` varchar(255) NOT NULL DEFAULT '',
  `schedule_data` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_chore_lists`
--

CREATE TABLE `oxford_chore_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT 'CHORE LIST',
  `week1_start` varchar(20) NOT NULL DEFAULT '',
  `week2_start` varchar(20) NOT NULL DEFAULT '',
  `week3_start` varchar(20) NOT NULL DEFAULT '',
  `week4_start` varchar(20) NOT NULL DEFAULT '',
  `week5_start` varchar(20) NOT NULL DEFAULT '',
  `week6_start` varchar(20) NOT NULL DEFAULT '',
  `week7_start` varchar(20) NOT NULL DEFAULT '',
  `week8_start` varchar(20) NOT NULL DEFAULT '',
  `form_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_disruptive_contracts`
--

CREATE TABLE `oxford_disruptive_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_subject` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(50) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `behavior_1` text DEFAULT NULL,
  `behavior_2` text DEFAULT NULL,
  `behavior_3` text DEFAULT NULL,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgment_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(50) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_original_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_stored_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_mime` varchar(100) NOT NULL DEFAULT '',
  `uploaded_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_financial_audits`
--

CREATE TABLE `oxford_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `date_completed` date DEFAULT NULL,
  `bank_statement_ending_date` date DEFAULT NULL,
  `bank_statement_ending_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_past_due_bills` decimal(12,2) NOT NULL DEFAULT 0.00,
  `savings_account_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_outstanding_ees` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deposits_json` longtext DEFAULT NULL,
  `checks_json` longtext DEFAULT NULL,
  `treasurer_signature` varchar(255) DEFAULT NULL,
  `comptroller_signature` varchar(255) DEFAULT NULL,
  `president_signature` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_audits`
--

CREATE TABLE `oxford_house_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `completed_month` varchar(10) NOT NULL DEFAULT '',
  `completed_day` varchar(10) NOT NULL DEFAULT '',
  `completed_year` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_month` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_day` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_year` varchar(10) NOT NULL DEFAULT '',
  `past_due_bills` varchar(50) NOT NULL DEFAULT '',
  `savings_balance` varchar(50) NOT NULL DEFAULT '',
  `outstanding_ees` varchar(50) NOT NULL DEFAULT '',
  `bank_statement_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `deposits_total` varchar(50) NOT NULL DEFAULT '',
  `checks_total` varchar(50) NOT NULL DEFAULT '',
  `balance_after_audit` varchar(50) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_signature` varchar(255) NOT NULL DEFAULT '',
  `comptroller_signature` varchar(255) NOT NULL DEFAULT '',
  `president_signature` varchar(255) NOT NULL DEFAULT '',
  `checks_rows` longtext DEFAULT NULL,
  `deposits_rows` longtext DEFAULT NULL,
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `scan_stored_name` varchar(255) NOT NULL DEFAULT '',
  `scan_path` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_reports`
--

CREATE TABLE `oxford_house_financial_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `report_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_ledger_forms`
--

CREATE TABLE `oxford_house_ledger_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `rows_json` longtext DEFAULT NULL,
  `totals_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_meeting_minutes_json`
--

CREATE TABLE `oxford_house_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_member_ledger`
--

CREATE TABLE `oxford_house_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(10) NOT NULL DEFAULT '',
  `move_in_day` varchar(10) NOT NULL DEFAULT '',
  `move_in_year` varchar(10) NOT NULL DEFAULT '',
  `row_1` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_1`)),
  `row_2` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_2`)),
  `row_3` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_3`)),
  `row_4` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_4`)),
  `row_5` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_5`)),
  `row_6` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_6`)),
  `row_7` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_7`)),
  `row_8` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_8`)),
  `row_9` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_9`)),
  `row_10` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_10`)),
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date_paid` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_minutes`
--

CREATE TABLE `oxford_house_minutes` (
  `id` int(11) NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `meeting_date` varchar(50) DEFAULT NULL,
  `start_time` varchar(50) DEFAULT NULL,
  `tradition_number` varchar(50) DEFAULT NULL,
  `meeting_type_regular` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_emergency` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_interview` tinyint(1) NOT NULL DEFAULT 0,
  `minutes_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `amend_yes` tinyint(1) NOT NULL DEFAULT 0,
  `amend_no` tinyint(1) NOT NULL DEFAULT 0,
  `checking_0` varchar(50) DEFAULT NULL,
  `checking_1` varchar(50) DEFAULT NULL,
  `checking_2` varchar(50) DEFAULT NULL,
  `checking_3` varchar(50) DEFAULT NULL,
  `savings_0` varchar(50) DEFAULT NULL,
  `savings_1` varchar(50) DEFAULT NULL,
  `savings_2` varchar(50) DEFAULT NULL,
  `savings_3` varchar(50) DEFAULT NULL,
  `savings_4` varchar(50) DEFAULT NULL,
  `petty_0` varchar(50) DEFAULT NULL,
  `petty_1` varchar(50) DEFAULT NULL,
  `petty_2` varchar(50) DEFAULT NULL,
  `petty_3` varchar(50) DEFAULT NULL,
  `petty_receipts_yes` tinyint(1) NOT NULL DEFAULT 0,
  `petty_receipts_no` tinyint(1) NOT NULL DEFAULT 0,
  `treasurer_comments` text DEFAULT NULL,
  `treasurer_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `comptroller_comments` text DEFAULT NULL,
  `comptroller_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `ees_plan` longtext DEFAULT NULL,
  `coordinator_report` text DEFAULT NULL,
  `coordinator_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `housing_services_report` text DEFAULT NULL,
  `hsr_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_n` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_n` tinyint(1) NOT NULL DEFAULT 0,
  `unfinished_business` text DEFAULT NULL,
  `vacancy_updated_y` tinyint(1) NOT NULL DEFAULT 0,
  `vacancy_updated_n` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_y` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_n` tinyint(1) NOT NULL DEFAULT 0,
  `new_business` longtext DEFAULT NULL,
  `adjourn_hour` varchar(10) DEFAULT NULL,
  `adjourn_min` varchar(10) DEFAULT NULL,
  `secretary_name` varchar(255) DEFAULT NULL,
  `secretary_signature` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `roll_name_1` text DEFAULT NULL,
  `roll_y_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_2` text DEFAULT NULL,
  `roll_y_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_3` text DEFAULT NULL,
  `roll_y_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_4` text DEFAULT NULL,
  `roll_y_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_5` text DEFAULT NULL,
  `roll_y_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_6` text DEFAULT NULL,
  `roll_y_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_7` text DEFAULT NULL,
  `roll_y_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_8` text DEFAULT NULL,
  `roll_y_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_9` text DEFAULT NULL,
  `roll_y_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_10` text DEFAULT NULL,
  `roll_y_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_11` text DEFAULT NULL,
  `roll_y_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_12` text DEFAULT NULL,
  `roll_y_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_13` text DEFAULT NULL,
  `roll_y_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_14` text DEFAULT NULL,
  `roll_y_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_15` text DEFAULT NULL,
  `roll_y_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_16` text DEFAULT NULL,
  `roll_y_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_17` text DEFAULT NULL,
  `roll_y_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_18` text DEFAULT NULL,
  `roll_y_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_19` text DEFAULT NULL,
  `roll_y_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_20` text DEFAULT NULL,
  `roll_y_20` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_20` tinyint(1) NOT NULL DEFAULT 0,
  `comp_name_1` text DEFAULT NULL,
  `comp_bal_1` text DEFAULT NULL,
  `comp_name_2` text DEFAULT NULL,
  `comp_bal_2` text DEFAULT NULL,
  `comp_name_3` text DEFAULT NULL,
  `comp_bal_3` text DEFAULT NULL,
  `comp_name_4` text DEFAULT NULL,
  `comp_bal_4` text DEFAULT NULL,
  `comp_name_5` text DEFAULT NULL,
  `comp_bal_5` text DEFAULT NULL,
  `comp_name_6` text DEFAULT NULL,
  `comp_bal_6` text DEFAULT NULL,
  `comp_name_7` text DEFAULT NULL,
  `comp_bal_7` text DEFAULT NULL,
  `comp_name_8` text DEFAULT NULL,
  `comp_bal_8` text DEFAULT NULL,
  `comp_name_9` text DEFAULT NULL,
  `comp_bal_9` text DEFAULT NULL,
  `comp_name_10` text DEFAULT NULL,
  `comp_bal_10` text DEFAULT NULL,
  `comp_name_11` text DEFAULT NULL,
  `comp_bal_11` text DEFAULT NULL,
  `comp_name_12` text DEFAULT NULL,
  `comp_bal_12` text DEFAULT NULL,
  `comp_name_13` text DEFAULT NULL,
  `comp_bal_13` text DEFAULT NULL,
  `comp_name_14` text DEFAULT NULL,
  `comp_bal_14` text DEFAULT NULL,
  `comp_name_15` text DEFAULT NULL,
  `comp_bal_15` text DEFAULT NULL,
  `comp_name_16` text DEFAULT NULL,
  `comp_bal_16` text DEFAULT NULL,
  `comp_name_17` text DEFAULT NULL,
  `comp_bal_17` text DEFAULT NULL,
  `comp_name_18` text DEFAULT NULL,
  `comp_bal_18` text DEFAULT NULL,
  `comp_name_19` text DEFAULT NULL,
  `comp_bal_19` text DEFAULT NULL,
  `comp_name_20` text DEFAULT NULL,
  `comp_bal_20` text DEFAULT NULL,
  `secretary_signed_date` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_interview_minutes`
--

CREATE TABLE `oxford_interview_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `interview_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `interview_date` date DEFAULT NULL,
  `interviewer_name` varchar(255) NOT NULL DEFAULT '',
  `contact_phone` varchar(100) NOT NULL DEFAULT '',
  `outcome_status` varchar(100) NOT NULL DEFAULT '',
  `vote_percent` varchar(50) NOT NULL DEFAULT '',
  `move_in_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `intro_notes` mediumtext DEFAULT NULL,
  `interviewer_do_notes` mediumtext DEFAULT NULL,
  `interviewer_dont_notes` mediumtext DEFAULT NULL,
  `closing_notes` mediumtext DEFAULT NULL,
  `roll_name_1` varchar(255) NOT NULL DEFAULT '',
  `roll_present_1` varchar(20) NOT NULL DEFAULT '',
  `roll_name_2` varchar(255) NOT NULL DEFAULT '',
  `roll_present_2` varchar(20) NOT NULL DEFAULT '',
  `roll_name_3` varchar(255) NOT NULL DEFAULT '',
  `roll_present_3` varchar(20) NOT NULL DEFAULT '',
  `roll_name_4` varchar(255) NOT NULL DEFAULT '',
  `roll_present_4` varchar(20) NOT NULL DEFAULT '',
  `roll_name_5` varchar(255) NOT NULL DEFAULT '',
  `roll_present_5` varchar(20) NOT NULL DEFAULT '',
  `roll_name_6` varchar(255) NOT NULL DEFAULT '',
  `roll_present_6` varchar(20) NOT NULL DEFAULT '',
  `roll_name_7` varchar(255) NOT NULL DEFAULT '',
  `roll_present_7` varchar(20) NOT NULL DEFAULT '',
  `roll_name_8` varchar(255) NOT NULL DEFAULT '',
  `roll_present_8` varchar(20) NOT NULL DEFAULT '',
  `roll_name_9` varchar(255) NOT NULL DEFAULT '',
  `roll_present_9` varchar(20) NOT NULL DEFAULT '',
  `roll_name_10` varchar(255) NOT NULL DEFAULT '',
  `roll_present_10` varchar(20) NOT NULL DEFAULT '',
  `roll_name_11` varchar(255) NOT NULL DEFAULT '',
  `roll_present_11` varchar(20) NOT NULL DEFAULT '',
  `roll_name_12` varchar(255) NOT NULL DEFAULT '',
  `roll_present_12` varchar(20) NOT NULL DEFAULT '',
  `q1` mediumtext DEFAULT NULL,
  `q2` mediumtext DEFAULT NULL,
  `q3` mediumtext DEFAULT NULL,
  `q4` mediumtext DEFAULT NULL,
  `q5` mediumtext DEFAULT NULL,
  `q6` mediumtext DEFAULT NULL,
  `q7` mediumtext DEFAULT NULL,
  `q8` mediumtext DEFAULT NULL,
  `q9` mediumtext DEFAULT NULL,
  `q10` mediumtext DEFAULT NULL,
  `q11` mediumtext DEFAULT NULL,
  `q12` mediumtext DEFAULT NULL,
  `q13` mediumtext DEFAULT NULL,
  `q14` mediumtext DEFAULT NULL,
  `q15` mediumtext DEFAULT NULL,
  `q16` mediumtext DEFAULT NULL,
  `q17` mediumtext DEFAULT NULL,
  `q18` mediumtext DEFAULT NULL,
  `q19` mediumtext DEFAULT NULL,
  `q20` mediumtext DEFAULT NULL,
  `q21` mediumtext DEFAULT NULL,
  `q22` mediumtext DEFAULT NULL,
  `q23` mediumtext DEFAULT NULL,
  `q24` mediumtext DEFAULT NULL,
  `q25` mediumtext DEFAULT NULL,
  `q26` mediumtext DEFAULT NULL,
  `q27` mediumtext DEFAULT NULL,
  `q28` mediumtext DEFAULT NULL,
  `q29` mediumtext DEFAULT NULL,
  `q30` mediumtext DEFAULT NULL,
  `q31` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_member_financial_contracts`
--

CREATE TABLE `oxford_member_financial_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(100) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `total_amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgement_name` varchar(255) NOT NULL DEFAULT '',
  `signature_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(100) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `scanned_contract` varchar(255) NOT NULL DEFAULT '',
  `contract_stamp` varchar(50) NOT NULL DEFAULT '',
  `contract_stamp_at` datetime DEFAULT NULL,
  `contract_stamp_by_ip` varchar(64) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_new_member_packets`
--

CREATE TABLE `oxford_new_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `packet_date` date DEFAULT NULL,
  `check_membership_application` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_manual` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_guidelines` tinyint(1) NOT NULL DEFAULT 0,
  `check_membership_agreement` tinyint(1) NOT NULL DEFAULT 0,
  `check_plan_for_recovery` tinyint(1) NOT NULL DEFAULT 0,
  `check_relapse_contingency` tinyint(1) NOT NULL DEFAULT 0,
  `check_medical_release` tinyint(1) NOT NULL DEFAULT 0,
  `check_property_list` tinyint(1) NOT NULL DEFAULT 0,
  `member_initials_1` varchar(20) NOT NULL DEFAULT '',
  `president_initials_1` varchar(20) NOT NULL DEFAULT '',
  `member_initials_2` varchar(20) NOT NULL DEFAULT '',
  `president_initials_2` varchar(20) NOT NULL DEFAULT '',
  `member_initials_3` varchar(20) NOT NULL DEFAULT '',
  `president_initials_3` varchar(20) NOT NULL DEFAULT '',
  `member_initials_4` varchar(20) NOT NULL DEFAULT '',
  `president_initials_4` varchar(20) NOT NULL DEFAULT '',
  `member_initials_5` varchar(20) NOT NULL DEFAULT '',
  `president_initials_5` varchar(20) NOT NULL DEFAULT '',
  `member_initials_6` varchar(20) NOT NULL DEFAULT '',
  `president_initials_6` varchar(20) NOT NULL DEFAULT '',
  `member_initials_7` varchar(20) NOT NULL DEFAULT '',
  `president_initials_7` varchar(20) NOT NULL DEFAULT '',
  `member_initials_8` varchar(20) NOT NULL DEFAULT '',
  `president_initials_8` varchar(20) NOT NULL DEFAULT '',
  `member_signature_1` varchar(255) NOT NULL DEFAULT '',
  `member_signature_date_1` date DEFAULT NULL,
  `president_signature_1` varchar(255) NOT NULL DEFAULT '',
  `president_signature_date_1` date DEFAULT NULL,
  `membership_agreement_text` longtext DEFAULT NULL,
  `agreement_member_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_date` date DEFAULT NULL,
  `agreement_president_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_date` date DEFAULT NULL,
  `plan_name` varchar(255) NOT NULL DEFAULT '',
  `plan_text` longtext DEFAULT NULL,
  `aftercare_program` longtext DEFAULT NULL,
  `has_sponsor` varchar(10) NOT NULL DEFAULT '',
  `sponsor_by_date` date DEFAULT NULL,
  `meetings_per_week` varchar(50) NOT NULL DEFAULT '',
  `meeting_types` varchar(255) NOT NULL DEFAULT '',
  `plan_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_signature_date` date DEFAULT NULL,
  `plan_president_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_president_date` date DEFAULT NULL,
  `relapse_name` varchar(255) NOT NULL DEFAULT '',
  `relapse_family` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_friend` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_detox` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other_text` varchar(255) NOT NULL DEFAULT '',
  `relapse_details` longtext DEFAULT NULL,
  `notify_rows_json` longtext DEFAULT NULL,
  `pickup_rows_json` longtext DEFAULT NULL,
  `relapse_member_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_member_date` date DEFAULT NULL,
  `relapse_president_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_president_date` date DEFAULT NULL,
  `relapse_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_witness_date` date DEFAULT NULL,
  `medical_name` varchar(255) NOT NULL DEFAULT '',
  `physician_name` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(50) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance_info` varchar(255) NOT NULL DEFAULT '',
  `allergies` longtext DEFAULT NULL,
  `medications` longtext DEFAULT NULL,
  `medical_history` longtext DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `blood_type` varchar(20) NOT NULL DEFAULT '',
  `medical_contacts_json` longtext DEFAULT NULL,
  `medical_signature` varchar(255) NOT NULL DEFAULT '',
  `medical_date` date DEFAULT NULL,
  `property_name` varchar(255) NOT NULL DEFAULT '',
  `property_move_in_date` date DEFAULT NULL,
  `property_rows_json` longtext DEFAULT NULL,
  `uploaded_copy_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_path` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_mime` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_red_creek_member_packets`
--

CREATE TABLE `oxford_red_creek_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `signature_date` date DEFAULT NULL,
  `refund_move_in_date` date DEFAULT NULL,
  `new_member_signature` varchar(255) NOT NULL DEFAULT '',
  `new_member_signature_date` date DEFAULT NULL,
  `president_hsr_signature` varchar(255) NOT NULL DEFAULT '',
  `president_hsr_signature_date` date DEFAULT NULL,
  `expectations_signature` varchar(255) NOT NULL DEFAULT '',
  `expectations_signature_date` date DEFAULT NULL,
  `medication_signature` varchar(255) NOT NULL DEFAULT '',
  `medication_signature_date` date DEFAULT NULL,
  `emergency_name` varchar(255) NOT NULL DEFAULT '',
  `emergency_age` varchar(50) NOT NULL DEFAULT '',
  `emergency_dob` varchar(50) NOT NULL DEFAULT '',
  `blood_type` varchar(50) NOT NULL DEFAULT '',
  `primary_physician` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(100) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance` varchar(255) NOT NULL DEFAULT '',
  `allergies` text DEFAULT NULL,
  `medications` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `contact1_name` varchar(255) NOT NULL DEFAULT '',
  `contact1_phone` varchar(100) NOT NULL DEFAULT '',
  `contact2_name` varchar(255) NOT NULL DEFAULT '',
  `contact2_phone` varchar(100) NOT NULL DEFAULT '',
  `contact3_name` varchar(255) NOT NULL DEFAULT '',
  `contact3_phone` varchar(100) NOT NULL DEFAULT '',
  `property_items` longtext DEFAULT NULL,
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `property_signature` varchar(255) NOT NULL DEFAULT '',
  `property_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `property_date` date DEFAULT NULL,
  `property_removed_date` date DEFAULT NULL,
  `property_removed_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `move_in_fee` decimal(10,2) DEFAULT 250.00,
  `other_charge` decimal(10,2) DEFAULT 0.00,
  `total_due` decimal(10,2) DEFAULT NULL,
  `scan_path` varchar(500) NOT NULL DEFAULT '',
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_residency_forms`
--

CREATE TABLE `oxford_residency_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) DEFAULT '',
  `letter_date` varchar(20) DEFAULT '',
  `house_name` varchar(255) DEFAULT '',
  `accepted_date` varchar(20) DEFAULT '',
  `address_line` varchar(255) DEFAULT '',
  `city_state_zip` varchar(255) DEFAULT '',
  `move_in_fee` decimal(10,2) DEFAULT 0.00,
  `weekly_rent` decimal(10,2) DEFAULT 0.00,
  `president_contact` varchar(255) DEFAULT '',
  `president_name` varchar(255) DEFAULT '',
  `president_signature` varchar(255) DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_shopping_lists`
--

CREATE TABLE `oxford_shopping_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `shopping_date` date DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Oxford House Shopping List',
  `items_json` longtext NOT NULL,
  `checked_json` longtext NOT NULL,
  `total_checked` int(11) NOT NULL DEFAULT 0,
  `total_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_items` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy_path` varchar(500) DEFAULT NULL,
  `uploaded_copy_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledgers`
--

CREATE TABLE `petty_cash_ledgers` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `ledger_date` date DEFAULT NULL,
  `beginning_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledger_rows`
--

CREATE TABLE `petty_cash_ledger_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `ledger_id` int(10) UNSIGNED NOT NULL,
  `row_index` int(11) NOT NULL,
  `txn_date` varchar(50) NOT NULL DEFAULT '',
  `products_purchased` varchar(255) NOT NULL DEFAULT '',
  `vendor` varchar(255) NOT NULL DEFAULT '',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reimbursement_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `safety_inspection_checklists`
--

CREATE TABLE `safety_inspection_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `inspection_date` date DEFAULT NULL,
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `checklist_json` longtext NOT NULL,
  `satisfactory_total` int(11) NOT NULL DEFAULT 0,
  `unsatisfactory_total` int(11) NOT NULL DEFAULT 0,
  `completed_total` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy` varchar(500) DEFAULT NULL,
  `original_upload_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_checklist_key` (`checklist_key`);

--
-- Indexes for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_meeting_date` (`meeting_date`);

--
-- Indexes for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_dates` (`week_start`,`week_end`);

--
-- Indexes for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_tour_date` (`tour_date`);

--
-- Indexes for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `house_name` (`house_name`);

--
-- Indexes for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_label` (`schedule_label`);

--
-- Indexes for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`);

--
-- Indexes for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_name_idx` (`tenant_name`),
  ADD KEY `updated_at_idx` (`updated_at`);

--
-- Indexes for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resident_name` (`resident_name`),
  ADD KEY `idx_sheet_date` (`sheet_date`);

--
-- Indexes for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sheet_id` (`sheet_id`),
  ADD KEY `idx_row_number` (`row_number`);

--
-- Indexes for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_form_date` (`form_date`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_contract_date` (`contract_date`);

--
-- Indexes for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date_completed` (`date_completed`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- Indexes for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_dates` (`house_name`,`date_from`,`date_to`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_date_from` (`date_from`),
  ADD KEY `idx_date_to` (`date_to`);

--
-- Indexes for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_start` (`week_start`),
  ADD KEY `idx_week_end` (`week_end`);

--
-- Indexes for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`),
  ADD KEY `idx_house_date` (`house_name`,`meeting_date`);

--
-- Indexes for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_member_date_house` (`member_name`,`contract_date`,`house_name`);

--
-- Indexes for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_member` (`house_name`,`member_name`);

--
-- Indexes for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_shopping_date` (`shopping_date`);

--
-- Indexes for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_date` (`house_name`,`ledger_date`);

--
-- Indexes for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_petty_cash_ledger_rows_ledger` (`ledger_id`);

--
-- Indexes for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inspection_date` (`inspection_date`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `north_place`
--
CREATE DATABASE IF NOT EXISTS `north_place` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `north_place`;

-- --------------------------------------------------------

--
-- Table structure for table `bedroom_essentials_checklists`
--

CREATE TABLE `bedroom_essentials_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `checklist_key` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Bedroom Essentials – Checklist',
  `items_json` longtext NOT NULL,
  `dark_mode` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chapter_meeting_minutes`
--

CREATE TABLE `chapter_meeting_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `meeting_date` varchar(50) NOT NULL,
  `form_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ees_member_ledger`
--

CREATE TABLE `ees_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(2) NOT NULL DEFAULT '',
  `move_in_day` varchar(2) NOT NULL DEFAULT '',
  `move_in_year` varchar(4) NOT NULL DEFAULT '',
  `ledger_rows` longtext DEFAULT NULL,
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_ledger_records`
--

CREATE TABLE `house_ledger_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `notes` mediumtext DEFAULT NULL,
  `rows_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_tour_forms`
--

CREATE TABLE `house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `tour_date` date DEFAULT NULL,
  `tour_time` varchar(20) NOT NULL DEFAULT '',
  `smoking_area` varchar(10) NOT NULL DEFAULT '',
  `notes` text DEFAULT NULL,
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `signature` varchar(255) NOT NULL DEFAULT '',
  `items_json` longtext DEFAULT NULL,
  `section_totals_json` text DEFAULT NULL,
  `grand_total` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_houses`
--

CREATE TABLE `house_visit_houses` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(150) NOT NULL,
  `meeting_day` varchar(20) NOT NULL DEFAULT '',
  `meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_reports`
--

CREATE TABLE `house_visit_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(100) NOT NULL DEFAULT '',
  `president` varchar(255) NOT NULL DEFAULT '',
  `secretary` varchar(255) NOT NULL DEFAULT '',
  `treasurer` varchar(255) NOT NULL DEFAULT '',
  `comptroller` varchar(255) NOT NULL DEFAULT '',
  `coordinator` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep` varchar(255) NOT NULL DEFAULT '',
  `overall_appearance` varchar(10) NOT NULL DEFAULT '',
  `overall_appearance_comments` text DEFAULT NULL,
  `members_behind_ees` decimal(10,2) DEFAULT NULL,
  `total_amount_owed` decimal(12,2) DEFAULT NULL,
  `rent_paid_monthly` decimal(12,2) DEFAULT NULL,
  `ees_paid_weekly` decimal(12,2) DEFAULT NULL,
  `utilities_monthly` decimal(12,2) DEFAULT NULL,
  `house_business_meeting` varchar(10) NOT NULL DEFAULT '',
  `house_business_comments` text DEFAULT NULL,
  `rating_reading_traditions` varchar(10) NOT NULL DEFAULT '',
  `rating_reading_minutes` varchar(10) NOT NULL DEFAULT '',
  `rating_treasurer_report` varchar(10) NOT NULL DEFAULT '',
  `rating_comptroller_report` varchar(10) NOT NULL DEFAULT '',
  `rating_coordinator_report` varchar(10) NOT NULL DEFAULT '',
  `rating_maintains_guidelines` varchar(10) NOT NULL DEFAULT '',
  `rating_handling_business` varchar(10) NOT NULL DEFAULT '',
  `rating_organization_order` varchar(10) NOT NULL DEFAULT '',
  `financial_comments` text DEFAULT NULL,
  `first_visit_date` varchar(50) NOT NULL DEFAULT '',
  `narcan_present` varchar(10) NOT NULL DEFAULT '',
  `narcan_trained` varchar(10) NOT NULL DEFAULT '',
  `follow_up_visit_dates` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep_signature` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_schedules`
--

CREATE TABLE `house_visit_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `schedule_label` varchar(150) NOT NULL,
  `base_month` varchar(7) NOT NULL DEFAULT '',
  `month_count` int(11) NOT NULL DEFAULT 1,
  `repeat_cycle` tinyint(1) NOT NULL DEFAULT 0,
  `step_size` int(11) NOT NULL DEFAULT 1,
  `month_label` varchar(50) NOT NULL,
  `month_index` int(11) NOT NULL DEFAULT 1,
  `visiting_house` varchar(150) NOT NULL,
  `host_house` varchar(150) NOT NULL,
  `host_meeting_day` varchar(20) NOT NULL DEFAULT '',
  `host_meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `housing_service_representative_reports`
--

CREATE TABLE `housing_service_representative_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `week_from` varchar(100) NOT NULL DEFAULT '',
  `week_to` varchar(100) NOT NULL DEFAULT '',
  `house_visit` text DEFAULT NULL,
  `audit_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `audit_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `next_chapter_meeting` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `next_state_meeting` text DEFAULT NULL,
  `number_of_applications` text DEFAULT NULL,
  `number_contacted_plan` longtext DEFAULT NULL,
  `new_members` longtext DEFAULT NULL,
  `interviews_setup` longtext DEFAULT NULL,
  `chapter_news` longtext DEFAULT NULL,
  `chapter_meeting_recap` longtext DEFAULT NULL,
  `upcoming_unity` longtext DEFAULT NULL,
  `upcoming_presentations` longtext DEFAULT NULL,
  `hsr_name` varchar(255) NOT NULL DEFAULT '',
  `report_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hsc_meeting_minutes_json`
--

CREATE TABLE `hsc_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `landlord_verification_forms`
--

CREATE TABLE `landlord_verification_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_name` varchar(255) NOT NULL DEFAULT '',
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `landlord_manager_name` varchar(255) NOT NULL DEFAULT '',
  `manager_street` varchar(255) NOT NULL DEFAULT '',
  `manager_city` varchar(150) NOT NULL DEFAULT '',
  `manager_state` varchar(50) NOT NULL DEFAULT '',
  `manager_zip` varchar(25) NOT NULL DEFAULT '',
  `manager_county` varchar(150) NOT NULL DEFAULT '',
  `manager_phone` varchar(50) NOT NULL DEFAULT '',
  `manager_email` varchar(255) NOT NULL DEFAULT '',
  `rental_street` varchar(255) NOT NULL DEFAULT '',
  `rental_apt_lot` varchar(100) NOT NULL DEFAULT '',
  `rental_city` varchar(150) NOT NULL DEFAULT '',
  `rental_state` varchar(50) NOT NULL DEFAULT '',
  `rental_zip` varchar(25) NOT NULL DEFAULT '',
  `rental_county` varchar(150) NOT NULL DEFAULT '',
  `bedrooms` varchar(20) NOT NULL DEFAULT '',
  `lease_start_date` date DEFAULT NULL,
  `lease_end_date` date DEFAULT NULL,
  `monthly_rent_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `next_payment_due_date` date DEFAULT NULL,
  `last_payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `last_payment_date` date DEFAULT NULL,
  `tenant_in_arrears` tinyint(1) NOT NULL DEFAULT 0,
  `amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `arrears_period_from` date DEFAULT NULL,
  `arrears_period_to` date DEFAULT NULL,
  `receiving_other_assistance` tinyint(1) NOT NULL DEFAULT 0,
  `other_assistance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_assistance_period` varchar(100) NOT NULL DEFAULT '',
  `payment_method` varchar(20) NOT NULL DEFAULT 'check',
  `check_payable_to` varchar(255) NOT NULL DEFAULT '',
  `send_to_landlord_address` tinyint(1) NOT NULL DEFAULT 1,
  `alternative_address` text DEFAULT NULL,
  `cert_name` varchar(255) NOT NULL DEFAULT '',
  `cert_title` varchar(255) NOT NULL DEFAULT '',
  `cert_signature` varchar(255) NOT NULL DEFAULT '',
  `cert_date` date DEFAULT NULL,
  `calc_balance_remaining` decimal(10,2) NOT NULL DEFAULT 0.00,
  `calc_total_assistance_gap` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheets`
--

CREATE TABLE `medication_count_sheets` (
  `id` int(10) UNSIGNED NOT NULL,
  `resident_name` varchar(255) NOT NULL,
  `sheet_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheet_rows`
--

CREATE TABLE `medication_count_sheet_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `sheet_id` int(10) UNSIGNED NOT NULL,
  `row_number` int(11) NOT NULL,
  `entry_date` varchar(50) DEFAULT NULL,
  `medication_name` varchar(255) DEFAULT NULL,
  `dosage` varchar(255) DEFAULT NULL,
  `frequency` varchar(255) DEFAULT NULL,
  `previous_count` varchar(100) DEFAULT NULL,
  `current_count` varchar(100) DEFAULT NULL,
  `member_initials` varchar(50) DEFAULT NULL,
  `witness_initials` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `new_house_tour_forms`
--

CREATE TABLE `new_house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `form_date` varchar(50) NOT NULL DEFAULT '',
  `form_time` varchar(50) NOT NULL DEFAULT '',
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `inspector_signature` varchar(255) NOT NULL DEFAULT '',
  `smoking_area_score` varchar(5) NOT NULL DEFAULT '',
  `smoking_area_comment` text DEFAULT NULL,
  `notes_text` text DEFAULT NULL,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_average` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payload_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nightly_kitchen_schedules`
--

CREATE TABLE `nightly_kitchen_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `trash_to_curb_on` varchar(255) NOT NULL DEFAULT '',
  `schedule_data` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_chore_lists`
--

CREATE TABLE `oxford_chore_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT 'CHORE LIST',
  `week1_start` varchar(20) NOT NULL DEFAULT '',
  `week2_start` varchar(20) NOT NULL DEFAULT '',
  `week3_start` varchar(20) NOT NULL DEFAULT '',
  `week4_start` varchar(20) NOT NULL DEFAULT '',
  `week5_start` varchar(20) NOT NULL DEFAULT '',
  `week6_start` varchar(20) NOT NULL DEFAULT '',
  `week7_start` varchar(20) NOT NULL DEFAULT '',
  `week8_start` varchar(20) NOT NULL DEFAULT '',
  `form_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_disruptive_contracts`
--

CREATE TABLE `oxford_disruptive_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_subject` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(50) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `behavior_1` text DEFAULT NULL,
  `behavior_2` text DEFAULT NULL,
  `behavior_3` text DEFAULT NULL,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgment_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(50) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_original_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_stored_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_mime` varchar(100) NOT NULL DEFAULT '',
  `uploaded_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_financial_audits`
--

CREATE TABLE `oxford_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `date_completed` date DEFAULT NULL,
  `bank_statement_ending_date` date DEFAULT NULL,
  `bank_statement_ending_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_past_due_bills` decimal(12,2) NOT NULL DEFAULT 0.00,
  `savings_account_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_outstanding_ees` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deposits_json` longtext DEFAULT NULL,
  `checks_json` longtext DEFAULT NULL,
  `treasurer_signature` varchar(255) DEFAULT NULL,
  `comptroller_signature` varchar(255) DEFAULT NULL,
  `president_signature` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_audits`
--

CREATE TABLE `oxford_house_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `completed_month` varchar(10) NOT NULL DEFAULT '',
  `completed_day` varchar(10) NOT NULL DEFAULT '',
  `completed_year` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_month` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_day` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_year` varchar(10) NOT NULL DEFAULT '',
  `past_due_bills` varchar(50) NOT NULL DEFAULT '',
  `savings_balance` varchar(50) NOT NULL DEFAULT '',
  `outstanding_ees` varchar(50) NOT NULL DEFAULT '',
  `bank_statement_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `deposits_total` varchar(50) NOT NULL DEFAULT '',
  `checks_total` varchar(50) NOT NULL DEFAULT '',
  `balance_after_audit` varchar(50) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_signature` varchar(255) NOT NULL DEFAULT '',
  `comptroller_signature` varchar(255) NOT NULL DEFAULT '',
  `president_signature` varchar(255) NOT NULL DEFAULT '',
  `checks_rows` longtext DEFAULT NULL,
  `deposits_rows` longtext DEFAULT NULL,
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `scan_stored_name` varchar(255) NOT NULL DEFAULT '',
  `scan_path` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_reports`
--

CREATE TABLE `oxford_house_financial_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `report_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_ledger_forms`
--

CREATE TABLE `oxford_house_ledger_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `rows_json` longtext DEFAULT NULL,
  `totals_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_meeting_minutes_json`
--

CREATE TABLE `oxford_house_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_member_ledger`
--

CREATE TABLE `oxford_house_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(10) NOT NULL DEFAULT '',
  `move_in_day` varchar(10) NOT NULL DEFAULT '',
  `move_in_year` varchar(10) NOT NULL DEFAULT '',
  `row_1` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_1`)),
  `row_2` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_2`)),
  `row_3` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_3`)),
  `row_4` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_4`)),
  `row_5` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_5`)),
  `row_6` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_6`)),
  `row_7` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_7`)),
  `row_8` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_8`)),
  `row_9` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_9`)),
  `row_10` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_10`)),
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date_paid` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_minutes`
--

CREATE TABLE `oxford_house_minutes` (
  `id` int(11) NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `meeting_date` varchar(50) DEFAULT NULL,
  `start_time` varchar(50) DEFAULT NULL,
  `tradition_number` varchar(50) DEFAULT NULL,
  `meeting_type_regular` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_emergency` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_interview` tinyint(1) NOT NULL DEFAULT 0,
  `minutes_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `amend_yes` tinyint(1) NOT NULL DEFAULT 0,
  `amend_no` tinyint(1) NOT NULL DEFAULT 0,
  `checking_0` varchar(50) DEFAULT NULL,
  `checking_1` varchar(50) DEFAULT NULL,
  `checking_2` varchar(50) DEFAULT NULL,
  `checking_3` varchar(50) DEFAULT NULL,
  `savings_0` varchar(50) DEFAULT NULL,
  `savings_1` varchar(50) DEFAULT NULL,
  `savings_2` varchar(50) DEFAULT NULL,
  `savings_3` varchar(50) DEFAULT NULL,
  `savings_4` varchar(50) DEFAULT NULL,
  `petty_0` varchar(50) DEFAULT NULL,
  `petty_1` varchar(50) DEFAULT NULL,
  `petty_2` varchar(50) DEFAULT NULL,
  `petty_3` varchar(50) DEFAULT NULL,
  `petty_receipts_yes` tinyint(1) NOT NULL DEFAULT 0,
  `petty_receipts_no` tinyint(1) NOT NULL DEFAULT 0,
  `treasurer_comments` text DEFAULT NULL,
  `treasurer_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `comptroller_comments` text DEFAULT NULL,
  `comptroller_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `ees_plan` longtext DEFAULT NULL,
  `coordinator_report` text DEFAULT NULL,
  `coordinator_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `housing_services_report` text DEFAULT NULL,
  `hsr_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_n` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_n` tinyint(1) NOT NULL DEFAULT 0,
  `unfinished_business` text DEFAULT NULL,
  `vacancy_updated_y` tinyint(1) NOT NULL DEFAULT 0,
  `vacancy_updated_n` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_y` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_n` tinyint(1) NOT NULL DEFAULT 0,
  `new_business` longtext DEFAULT NULL,
  `adjourn_hour` varchar(10) DEFAULT NULL,
  `adjourn_min` varchar(10) DEFAULT NULL,
  `secretary_name` varchar(255) DEFAULT NULL,
  `secretary_signature` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `roll_name_1` text DEFAULT NULL,
  `roll_y_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_2` text DEFAULT NULL,
  `roll_y_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_3` text DEFAULT NULL,
  `roll_y_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_4` text DEFAULT NULL,
  `roll_y_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_5` text DEFAULT NULL,
  `roll_y_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_6` text DEFAULT NULL,
  `roll_y_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_7` text DEFAULT NULL,
  `roll_y_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_8` text DEFAULT NULL,
  `roll_y_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_9` text DEFAULT NULL,
  `roll_y_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_10` text DEFAULT NULL,
  `roll_y_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_11` text DEFAULT NULL,
  `roll_y_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_12` text DEFAULT NULL,
  `roll_y_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_13` text DEFAULT NULL,
  `roll_y_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_14` text DEFAULT NULL,
  `roll_y_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_15` text DEFAULT NULL,
  `roll_y_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_16` text DEFAULT NULL,
  `roll_y_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_17` text DEFAULT NULL,
  `roll_y_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_18` text DEFAULT NULL,
  `roll_y_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_19` text DEFAULT NULL,
  `roll_y_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_20` text DEFAULT NULL,
  `roll_y_20` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_20` tinyint(1) NOT NULL DEFAULT 0,
  `comp_name_1` text DEFAULT NULL,
  `comp_bal_1` text DEFAULT NULL,
  `comp_name_2` text DEFAULT NULL,
  `comp_bal_2` text DEFAULT NULL,
  `comp_name_3` text DEFAULT NULL,
  `comp_bal_3` text DEFAULT NULL,
  `comp_name_4` text DEFAULT NULL,
  `comp_bal_4` text DEFAULT NULL,
  `comp_name_5` text DEFAULT NULL,
  `comp_bal_5` text DEFAULT NULL,
  `comp_name_6` text DEFAULT NULL,
  `comp_bal_6` text DEFAULT NULL,
  `comp_name_7` text DEFAULT NULL,
  `comp_bal_7` text DEFAULT NULL,
  `comp_name_8` text DEFAULT NULL,
  `comp_bal_8` text DEFAULT NULL,
  `comp_name_9` text DEFAULT NULL,
  `comp_bal_9` text DEFAULT NULL,
  `comp_name_10` text DEFAULT NULL,
  `comp_bal_10` text DEFAULT NULL,
  `comp_name_11` text DEFAULT NULL,
  `comp_bal_11` text DEFAULT NULL,
  `comp_name_12` text DEFAULT NULL,
  `comp_bal_12` text DEFAULT NULL,
  `comp_name_13` text DEFAULT NULL,
  `comp_bal_13` text DEFAULT NULL,
  `comp_name_14` text DEFAULT NULL,
  `comp_bal_14` text DEFAULT NULL,
  `comp_name_15` text DEFAULT NULL,
  `comp_bal_15` text DEFAULT NULL,
  `comp_name_16` text DEFAULT NULL,
  `comp_bal_16` text DEFAULT NULL,
  `comp_name_17` text DEFAULT NULL,
  `comp_bal_17` text DEFAULT NULL,
  `comp_name_18` text DEFAULT NULL,
  `comp_bal_18` text DEFAULT NULL,
  `comp_name_19` text DEFAULT NULL,
  `comp_bal_19` text DEFAULT NULL,
  `comp_name_20` text DEFAULT NULL,
  `comp_bal_20` text DEFAULT NULL,
  `secretary_signed_date` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_interview_minutes`
--

CREATE TABLE `oxford_interview_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `interview_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `interview_date` date DEFAULT NULL,
  `interviewer_name` varchar(255) NOT NULL DEFAULT '',
  `contact_phone` varchar(100) NOT NULL DEFAULT '',
  `outcome_status` varchar(100) NOT NULL DEFAULT '',
  `vote_percent` varchar(50) NOT NULL DEFAULT '',
  `move_in_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `intro_notes` mediumtext DEFAULT NULL,
  `interviewer_do_notes` mediumtext DEFAULT NULL,
  `interviewer_dont_notes` mediumtext DEFAULT NULL,
  `closing_notes` mediumtext DEFAULT NULL,
  `roll_name_1` varchar(255) NOT NULL DEFAULT '',
  `roll_present_1` varchar(20) NOT NULL DEFAULT '',
  `roll_name_2` varchar(255) NOT NULL DEFAULT '',
  `roll_present_2` varchar(20) NOT NULL DEFAULT '',
  `roll_name_3` varchar(255) NOT NULL DEFAULT '',
  `roll_present_3` varchar(20) NOT NULL DEFAULT '',
  `roll_name_4` varchar(255) NOT NULL DEFAULT '',
  `roll_present_4` varchar(20) NOT NULL DEFAULT '',
  `roll_name_5` varchar(255) NOT NULL DEFAULT '',
  `roll_present_5` varchar(20) NOT NULL DEFAULT '',
  `roll_name_6` varchar(255) NOT NULL DEFAULT '',
  `roll_present_6` varchar(20) NOT NULL DEFAULT '',
  `roll_name_7` varchar(255) NOT NULL DEFAULT '',
  `roll_present_7` varchar(20) NOT NULL DEFAULT '',
  `roll_name_8` varchar(255) NOT NULL DEFAULT '',
  `roll_present_8` varchar(20) NOT NULL DEFAULT '',
  `roll_name_9` varchar(255) NOT NULL DEFAULT '',
  `roll_present_9` varchar(20) NOT NULL DEFAULT '',
  `roll_name_10` varchar(255) NOT NULL DEFAULT '',
  `roll_present_10` varchar(20) NOT NULL DEFAULT '',
  `roll_name_11` varchar(255) NOT NULL DEFAULT '',
  `roll_present_11` varchar(20) NOT NULL DEFAULT '',
  `roll_name_12` varchar(255) NOT NULL DEFAULT '',
  `roll_present_12` varchar(20) NOT NULL DEFAULT '',
  `q1` mediumtext DEFAULT NULL,
  `q2` mediumtext DEFAULT NULL,
  `q3` mediumtext DEFAULT NULL,
  `q4` mediumtext DEFAULT NULL,
  `q5` mediumtext DEFAULT NULL,
  `q6` mediumtext DEFAULT NULL,
  `q7` mediumtext DEFAULT NULL,
  `q8` mediumtext DEFAULT NULL,
  `q9` mediumtext DEFAULT NULL,
  `q10` mediumtext DEFAULT NULL,
  `q11` mediumtext DEFAULT NULL,
  `q12` mediumtext DEFAULT NULL,
  `q13` mediumtext DEFAULT NULL,
  `q14` mediumtext DEFAULT NULL,
  `q15` mediumtext DEFAULT NULL,
  `q16` mediumtext DEFAULT NULL,
  `q17` mediumtext DEFAULT NULL,
  `q18` mediumtext DEFAULT NULL,
  `q19` mediumtext DEFAULT NULL,
  `q20` mediumtext DEFAULT NULL,
  `q21` mediumtext DEFAULT NULL,
  `q22` mediumtext DEFAULT NULL,
  `q23` mediumtext DEFAULT NULL,
  `q24` mediumtext DEFAULT NULL,
  `q25` mediumtext DEFAULT NULL,
  `q26` mediumtext DEFAULT NULL,
  `q27` mediumtext DEFAULT NULL,
  `q28` mediumtext DEFAULT NULL,
  `q29` mediumtext DEFAULT NULL,
  `q30` mediumtext DEFAULT NULL,
  `q31` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_member_financial_contracts`
--

CREATE TABLE `oxford_member_financial_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(100) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `total_amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgement_name` varchar(255) NOT NULL DEFAULT '',
  `signature_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(100) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `scanned_contract` varchar(255) NOT NULL DEFAULT '',
  `contract_stamp` varchar(50) NOT NULL DEFAULT '',
  `contract_stamp_at` datetime DEFAULT NULL,
  `contract_stamp_by_ip` varchar(64) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_new_member_packets`
--

CREATE TABLE `oxford_new_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `packet_date` date DEFAULT NULL,
  `check_membership_application` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_manual` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_guidelines` tinyint(1) NOT NULL DEFAULT 0,
  `check_membership_agreement` tinyint(1) NOT NULL DEFAULT 0,
  `check_plan_for_recovery` tinyint(1) NOT NULL DEFAULT 0,
  `check_relapse_contingency` tinyint(1) NOT NULL DEFAULT 0,
  `check_medical_release` tinyint(1) NOT NULL DEFAULT 0,
  `check_property_list` tinyint(1) NOT NULL DEFAULT 0,
  `member_initials_1` varchar(20) NOT NULL DEFAULT '',
  `president_initials_1` varchar(20) NOT NULL DEFAULT '',
  `member_initials_2` varchar(20) NOT NULL DEFAULT '',
  `president_initials_2` varchar(20) NOT NULL DEFAULT '',
  `member_initials_3` varchar(20) NOT NULL DEFAULT '',
  `president_initials_3` varchar(20) NOT NULL DEFAULT '',
  `member_initials_4` varchar(20) NOT NULL DEFAULT '',
  `president_initials_4` varchar(20) NOT NULL DEFAULT '',
  `member_initials_5` varchar(20) NOT NULL DEFAULT '',
  `president_initials_5` varchar(20) NOT NULL DEFAULT '',
  `member_initials_6` varchar(20) NOT NULL DEFAULT '',
  `president_initials_6` varchar(20) NOT NULL DEFAULT '',
  `member_initials_7` varchar(20) NOT NULL DEFAULT '',
  `president_initials_7` varchar(20) NOT NULL DEFAULT '',
  `member_initials_8` varchar(20) NOT NULL DEFAULT '',
  `president_initials_8` varchar(20) NOT NULL DEFAULT '',
  `member_signature_1` varchar(255) NOT NULL DEFAULT '',
  `member_signature_date_1` date DEFAULT NULL,
  `president_signature_1` varchar(255) NOT NULL DEFAULT '',
  `president_signature_date_1` date DEFAULT NULL,
  `membership_agreement_text` longtext DEFAULT NULL,
  `agreement_member_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_date` date DEFAULT NULL,
  `agreement_president_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_date` date DEFAULT NULL,
  `plan_name` varchar(255) NOT NULL DEFAULT '',
  `plan_text` longtext DEFAULT NULL,
  `aftercare_program` longtext DEFAULT NULL,
  `has_sponsor` varchar(10) NOT NULL DEFAULT '',
  `sponsor_by_date` date DEFAULT NULL,
  `meetings_per_week` varchar(50) NOT NULL DEFAULT '',
  `meeting_types` varchar(255) NOT NULL DEFAULT '',
  `plan_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_signature_date` date DEFAULT NULL,
  `plan_president_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_president_date` date DEFAULT NULL,
  `relapse_name` varchar(255) NOT NULL DEFAULT '',
  `relapse_family` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_friend` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_detox` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other_text` varchar(255) NOT NULL DEFAULT '',
  `relapse_details` longtext DEFAULT NULL,
  `notify_rows_json` longtext DEFAULT NULL,
  `pickup_rows_json` longtext DEFAULT NULL,
  `relapse_member_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_member_date` date DEFAULT NULL,
  `relapse_president_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_president_date` date DEFAULT NULL,
  `relapse_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_witness_date` date DEFAULT NULL,
  `medical_name` varchar(255) NOT NULL DEFAULT '',
  `physician_name` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(50) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance_info` varchar(255) NOT NULL DEFAULT '',
  `allergies` longtext DEFAULT NULL,
  `medications` longtext DEFAULT NULL,
  `medical_history` longtext DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `blood_type` varchar(20) NOT NULL DEFAULT '',
  `medical_contacts_json` longtext DEFAULT NULL,
  `medical_signature` varchar(255) NOT NULL DEFAULT '',
  `medical_date` date DEFAULT NULL,
  `property_name` varchar(255) NOT NULL DEFAULT '',
  `property_move_in_date` date DEFAULT NULL,
  `property_rows_json` longtext DEFAULT NULL,
  `uploaded_copy_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_path` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_mime` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_red_creek_member_packets`
--

CREATE TABLE `oxford_red_creek_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `signature_date` date DEFAULT NULL,
  `refund_move_in_date` date DEFAULT NULL,
  `new_member_signature` varchar(255) NOT NULL DEFAULT '',
  `new_member_signature_date` date DEFAULT NULL,
  `president_hsr_signature` varchar(255) NOT NULL DEFAULT '',
  `president_hsr_signature_date` date DEFAULT NULL,
  `expectations_signature` varchar(255) NOT NULL DEFAULT '',
  `expectations_signature_date` date DEFAULT NULL,
  `medication_signature` varchar(255) NOT NULL DEFAULT '',
  `medication_signature_date` date DEFAULT NULL,
  `emergency_name` varchar(255) NOT NULL DEFAULT '',
  `emergency_age` varchar(50) NOT NULL DEFAULT '',
  `emergency_dob` varchar(50) NOT NULL DEFAULT '',
  `blood_type` varchar(50) NOT NULL DEFAULT '',
  `primary_physician` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(100) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance` varchar(255) NOT NULL DEFAULT '',
  `allergies` text DEFAULT NULL,
  `medications` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `contact1_name` varchar(255) NOT NULL DEFAULT '',
  `contact1_phone` varchar(100) NOT NULL DEFAULT '',
  `contact2_name` varchar(255) NOT NULL DEFAULT '',
  `contact2_phone` varchar(100) NOT NULL DEFAULT '',
  `contact3_name` varchar(255) NOT NULL DEFAULT '',
  `contact3_phone` varchar(100) NOT NULL DEFAULT '',
  `property_items` longtext DEFAULT NULL,
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `property_signature` varchar(255) NOT NULL DEFAULT '',
  `property_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `property_date` date DEFAULT NULL,
  `property_removed_date` date DEFAULT NULL,
  `property_removed_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `move_in_fee` decimal(10,2) DEFAULT 250.00,
  `other_charge` decimal(10,2) DEFAULT 0.00,
  `total_due` decimal(10,2) DEFAULT NULL,
  `scan_path` varchar(500) NOT NULL DEFAULT '',
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_residency_forms`
--

CREATE TABLE `oxford_residency_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) DEFAULT '',
  `letter_date` varchar(20) DEFAULT '',
  `house_name` varchar(255) DEFAULT '',
  `accepted_date` varchar(20) DEFAULT '',
  `address_line` varchar(255) DEFAULT '',
  `city_state_zip` varchar(255) DEFAULT '',
  `move_in_fee` decimal(10,2) DEFAULT 0.00,
  `weekly_rent` decimal(10,2) DEFAULT 0.00,
  `president_contact` varchar(255) DEFAULT '',
  `president_name` varchar(255) DEFAULT '',
  `president_signature` varchar(255) DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_shopping_lists`
--

CREATE TABLE `oxford_shopping_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `shopping_date` date DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Oxford House Shopping List',
  `items_json` longtext NOT NULL,
  `checked_json` longtext NOT NULL,
  `total_checked` int(11) NOT NULL DEFAULT 0,
  `total_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_items` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy_path` varchar(500) DEFAULT NULL,
  `uploaded_copy_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledgers`
--

CREATE TABLE `petty_cash_ledgers` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `ledger_date` date DEFAULT NULL,
  `beginning_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledger_rows`
--

CREATE TABLE `petty_cash_ledger_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `ledger_id` int(10) UNSIGNED NOT NULL,
  `row_index` int(11) NOT NULL,
  `txn_date` varchar(50) NOT NULL DEFAULT '',
  `products_purchased` varchar(255) NOT NULL DEFAULT '',
  `vendor` varchar(255) NOT NULL DEFAULT '',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reimbursement_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `safety_inspection_checklists`
--

CREATE TABLE `safety_inspection_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `inspection_date` date DEFAULT NULL,
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `checklist_json` longtext NOT NULL,
  `satisfactory_total` int(11) NOT NULL DEFAULT 0,
  `unsatisfactory_total` int(11) NOT NULL DEFAULT 0,
  `completed_total` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy` varchar(500) DEFAULT NULL,
  `original_upload_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_checklist_key` (`checklist_key`);

--
-- Indexes for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_meeting_date` (`meeting_date`);

--
-- Indexes for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_dates` (`week_start`,`week_end`);

--
-- Indexes for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_tour_date` (`tour_date`);

--
-- Indexes for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `house_name` (`house_name`);

--
-- Indexes for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_label` (`schedule_label`);

--
-- Indexes for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`);

--
-- Indexes for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_name_idx` (`tenant_name`),
  ADD KEY `updated_at_idx` (`updated_at`);

--
-- Indexes for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resident_name` (`resident_name`),
  ADD KEY `idx_sheet_date` (`sheet_date`);

--
-- Indexes for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sheet_id` (`sheet_id`),
  ADD KEY `idx_row_number` (`row_number`);

--
-- Indexes for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_form_date` (`form_date`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_contract_date` (`contract_date`);

--
-- Indexes for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date_completed` (`date_completed`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- Indexes for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_dates` (`house_name`,`date_from`,`date_to`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_date_from` (`date_from`),
  ADD KEY `idx_date_to` (`date_to`);

--
-- Indexes for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_start` (`week_start`),
  ADD KEY `idx_week_end` (`week_end`);

--
-- Indexes for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`),
  ADD KEY `idx_house_date` (`house_name`,`meeting_date`);

--
-- Indexes for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_member_date_house` (`member_name`,`contract_date`,`house_name`);

--
-- Indexes for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_member` (`house_name`,`member_name`);

--
-- Indexes for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_shopping_date` (`shopping_date`);

--
-- Indexes for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_date` (`house_name`,`ledger_date`);

--
-- Indexes for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_petty_cash_ledger_rows_ledger` (`ledger_id`);

--
-- Indexes for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inspection_date` (`inspection_date`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `norwich`
--
CREATE DATABASE IF NOT EXISTS `norwich` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `norwich`;

-- --------------------------------------------------------

--
-- Table structure for table `bedroom_essentials_checklists`
--

CREATE TABLE `bedroom_essentials_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `checklist_key` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Bedroom Essentials – Checklist',
  `items_json` longtext NOT NULL,
  `dark_mode` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chapter_meeting_minutes`
--

CREATE TABLE `chapter_meeting_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `meeting_date` varchar(50) NOT NULL,
  `form_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ees_member_ledger`
--

CREATE TABLE `ees_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(2) NOT NULL DEFAULT '',
  `move_in_day` varchar(2) NOT NULL DEFAULT '',
  `move_in_year` varchar(4) NOT NULL DEFAULT '',
  `ledger_rows` longtext DEFAULT NULL,
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_ledger_records`
--

CREATE TABLE `house_ledger_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `notes` mediumtext DEFAULT NULL,
  `rows_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_tour_forms`
--

CREATE TABLE `house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `tour_date` date DEFAULT NULL,
  `tour_time` varchar(20) NOT NULL DEFAULT '',
  `smoking_area` varchar(10) NOT NULL DEFAULT '',
  `notes` text DEFAULT NULL,
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `signature` varchar(255) NOT NULL DEFAULT '',
  `items_json` longtext DEFAULT NULL,
  `section_totals_json` text DEFAULT NULL,
  `grand_total` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_houses`
--

CREATE TABLE `house_visit_houses` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(150) NOT NULL,
  `meeting_day` varchar(20) NOT NULL DEFAULT '',
  `meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_reports`
--

CREATE TABLE `house_visit_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(100) NOT NULL DEFAULT '',
  `president` varchar(255) NOT NULL DEFAULT '',
  `secretary` varchar(255) NOT NULL DEFAULT '',
  `treasurer` varchar(255) NOT NULL DEFAULT '',
  `comptroller` varchar(255) NOT NULL DEFAULT '',
  `coordinator` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep` varchar(255) NOT NULL DEFAULT '',
  `overall_appearance` varchar(10) NOT NULL DEFAULT '',
  `overall_appearance_comments` text DEFAULT NULL,
  `members_behind_ees` decimal(10,2) DEFAULT NULL,
  `total_amount_owed` decimal(12,2) DEFAULT NULL,
  `rent_paid_monthly` decimal(12,2) DEFAULT NULL,
  `ees_paid_weekly` decimal(12,2) DEFAULT NULL,
  `utilities_monthly` decimal(12,2) DEFAULT NULL,
  `house_business_meeting` varchar(10) NOT NULL DEFAULT '',
  `house_business_comments` text DEFAULT NULL,
  `rating_reading_traditions` varchar(10) NOT NULL DEFAULT '',
  `rating_reading_minutes` varchar(10) NOT NULL DEFAULT '',
  `rating_treasurer_report` varchar(10) NOT NULL DEFAULT '',
  `rating_comptroller_report` varchar(10) NOT NULL DEFAULT '',
  `rating_coordinator_report` varchar(10) NOT NULL DEFAULT '',
  `rating_maintains_guidelines` varchar(10) NOT NULL DEFAULT '',
  `rating_handling_business` varchar(10) NOT NULL DEFAULT '',
  `rating_organization_order` varchar(10) NOT NULL DEFAULT '',
  `financial_comments` text DEFAULT NULL,
  `first_visit_date` varchar(50) NOT NULL DEFAULT '',
  `narcan_present` varchar(10) NOT NULL DEFAULT '',
  `narcan_trained` varchar(10) NOT NULL DEFAULT '',
  `follow_up_visit_dates` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep_signature` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_schedules`
--

CREATE TABLE `house_visit_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `schedule_label` varchar(150) NOT NULL,
  `base_month` varchar(7) NOT NULL DEFAULT '',
  `month_count` int(11) NOT NULL DEFAULT 1,
  `repeat_cycle` tinyint(1) NOT NULL DEFAULT 0,
  `step_size` int(11) NOT NULL DEFAULT 1,
  `month_label` varchar(50) NOT NULL,
  `month_index` int(11) NOT NULL DEFAULT 1,
  `visiting_house` varchar(150) NOT NULL,
  `host_house` varchar(150) NOT NULL,
  `host_meeting_day` varchar(20) NOT NULL DEFAULT '',
  `host_meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `housing_service_representative_reports`
--

CREATE TABLE `housing_service_representative_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `week_from` varchar(100) NOT NULL DEFAULT '',
  `week_to` varchar(100) NOT NULL DEFAULT '',
  `house_visit` text DEFAULT NULL,
  `audit_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `audit_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `next_chapter_meeting` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `next_state_meeting` text DEFAULT NULL,
  `number_of_applications` text DEFAULT NULL,
  `number_contacted_plan` longtext DEFAULT NULL,
  `new_members` longtext DEFAULT NULL,
  `interviews_setup` longtext DEFAULT NULL,
  `chapter_news` longtext DEFAULT NULL,
  `chapter_meeting_recap` longtext DEFAULT NULL,
  `upcoming_unity` longtext DEFAULT NULL,
  `upcoming_presentations` longtext DEFAULT NULL,
  `hsr_name` varchar(255) NOT NULL DEFAULT '',
  `report_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hsc_meeting_minutes_json`
--

CREATE TABLE `hsc_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `landlord_verification_forms`
--

CREATE TABLE `landlord_verification_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_name` varchar(255) NOT NULL DEFAULT '',
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `landlord_manager_name` varchar(255) NOT NULL DEFAULT '',
  `manager_street` varchar(255) NOT NULL DEFAULT '',
  `manager_city` varchar(150) NOT NULL DEFAULT '',
  `manager_state` varchar(50) NOT NULL DEFAULT '',
  `manager_zip` varchar(25) NOT NULL DEFAULT '',
  `manager_county` varchar(150) NOT NULL DEFAULT '',
  `manager_phone` varchar(50) NOT NULL DEFAULT '',
  `manager_email` varchar(255) NOT NULL DEFAULT '',
  `rental_street` varchar(255) NOT NULL DEFAULT '',
  `rental_apt_lot` varchar(100) NOT NULL DEFAULT '',
  `rental_city` varchar(150) NOT NULL DEFAULT '',
  `rental_state` varchar(50) NOT NULL DEFAULT '',
  `rental_zip` varchar(25) NOT NULL DEFAULT '',
  `rental_county` varchar(150) NOT NULL DEFAULT '',
  `bedrooms` varchar(20) NOT NULL DEFAULT '',
  `lease_start_date` date DEFAULT NULL,
  `lease_end_date` date DEFAULT NULL,
  `monthly_rent_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `next_payment_due_date` date DEFAULT NULL,
  `last_payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `last_payment_date` date DEFAULT NULL,
  `tenant_in_arrears` tinyint(1) NOT NULL DEFAULT 0,
  `amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `arrears_period_from` date DEFAULT NULL,
  `arrears_period_to` date DEFAULT NULL,
  `receiving_other_assistance` tinyint(1) NOT NULL DEFAULT 0,
  `other_assistance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_assistance_period` varchar(100) NOT NULL DEFAULT '',
  `payment_method` varchar(20) NOT NULL DEFAULT 'check',
  `check_payable_to` varchar(255) NOT NULL DEFAULT '',
  `send_to_landlord_address` tinyint(1) NOT NULL DEFAULT 1,
  `alternative_address` text DEFAULT NULL,
  `cert_name` varchar(255) NOT NULL DEFAULT '',
  `cert_title` varchar(255) NOT NULL DEFAULT '',
  `cert_signature` varchar(255) NOT NULL DEFAULT '',
  `cert_date` date DEFAULT NULL,
  `calc_balance_remaining` decimal(10,2) NOT NULL DEFAULT 0.00,
  `calc_total_assistance_gap` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheets`
--

CREATE TABLE `medication_count_sheets` (
  `id` int(10) UNSIGNED NOT NULL,
  `resident_name` varchar(255) NOT NULL,
  `sheet_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheet_rows`
--

CREATE TABLE `medication_count_sheet_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `sheet_id` int(10) UNSIGNED NOT NULL,
  `row_number` int(11) NOT NULL,
  `entry_date` varchar(50) DEFAULT NULL,
  `medication_name` varchar(255) DEFAULT NULL,
  `dosage` varchar(255) DEFAULT NULL,
  `frequency` varchar(255) DEFAULT NULL,
  `previous_count` varchar(100) DEFAULT NULL,
  `current_count` varchar(100) DEFAULT NULL,
  `member_initials` varchar(50) DEFAULT NULL,
  `witness_initials` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `new_house_tour_forms`
--

CREATE TABLE `new_house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `form_date` varchar(50) NOT NULL DEFAULT '',
  `form_time` varchar(50) NOT NULL DEFAULT '',
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `inspector_signature` varchar(255) NOT NULL DEFAULT '',
  `smoking_area_score` varchar(5) NOT NULL DEFAULT '',
  `smoking_area_comment` text DEFAULT NULL,
  `notes_text` text DEFAULT NULL,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_average` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payload_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nightly_kitchen_schedules`
--

CREATE TABLE `nightly_kitchen_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `trash_to_curb_on` varchar(255) NOT NULL DEFAULT '',
  `schedule_data` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_chore_lists`
--

CREATE TABLE `oxford_chore_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT 'CHORE LIST',
  `week1_start` varchar(20) NOT NULL DEFAULT '',
  `week2_start` varchar(20) NOT NULL DEFAULT '',
  `week3_start` varchar(20) NOT NULL DEFAULT '',
  `week4_start` varchar(20) NOT NULL DEFAULT '',
  `week5_start` varchar(20) NOT NULL DEFAULT '',
  `week6_start` varchar(20) NOT NULL DEFAULT '',
  `week7_start` varchar(20) NOT NULL DEFAULT '',
  `week8_start` varchar(20) NOT NULL DEFAULT '',
  `form_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_disruptive_contracts`
--

CREATE TABLE `oxford_disruptive_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_subject` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(50) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `behavior_1` text DEFAULT NULL,
  `behavior_2` text DEFAULT NULL,
  `behavior_3` text DEFAULT NULL,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgment_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(50) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_original_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_stored_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_mime` varchar(100) NOT NULL DEFAULT '',
  `uploaded_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_financial_audits`
--

CREATE TABLE `oxford_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `date_completed` date DEFAULT NULL,
  `bank_statement_ending_date` date DEFAULT NULL,
  `bank_statement_ending_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_past_due_bills` decimal(12,2) NOT NULL DEFAULT 0.00,
  `savings_account_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_outstanding_ees` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deposits_json` longtext DEFAULT NULL,
  `checks_json` longtext DEFAULT NULL,
  `treasurer_signature` varchar(255) DEFAULT NULL,
  `comptroller_signature` varchar(255) DEFAULT NULL,
  `president_signature` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_audits`
--

CREATE TABLE `oxford_house_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `completed_month` varchar(10) NOT NULL DEFAULT '',
  `completed_day` varchar(10) NOT NULL DEFAULT '',
  `completed_year` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_month` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_day` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_year` varchar(10) NOT NULL DEFAULT '',
  `past_due_bills` varchar(50) NOT NULL DEFAULT '',
  `savings_balance` varchar(50) NOT NULL DEFAULT '',
  `outstanding_ees` varchar(50) NOT NULL DEFAULT '',
  `bank_statement_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `deposits_total` varchar(50) NOT NULL DEFAULT '',
  `checks_total` varchar(50) NOT NULL DEFAULT '',
  `balance_after_audit` varchar(50) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_signature` varchar(255) NOT NULL DEFAULT '',
  `comptroller_signature` varchar(255) NOT NULL DEFAULT '',
  `president_signature` varchar(255) NOT NULL DEFAULT '',
  `checks_rows` longtext DEFAULT NULL,
  `deposits_rows` longtext DEFAULT NULL,
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `scan_stored_name` varchar(255) NOT NULL DEFAULT '',
  `scan_path` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_reports`
--

CREATE TABLE `oxford_house_financial_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `report_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_ledger_forms`
--

CREATE TABLE `oxford_house_ledger_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `rows_json` longtext DEFAULT NULL,
  `totals_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_meeting_minutes_json`
--

CREATE TABLE `oxford_house_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_member_ledger`
--

CREATE TABLE `oxford_house_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(10) NOT NULL DEFAULT '',
  `move_in_day` varchar(10) NOT NULL DEFAULT '',
  `move_in_year` varchar(10) NOT NULL DEFAULT '',
  `row_1` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_1`)),
  `row_2` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_2`)),
  `row_3` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_3`)),
  `row_4` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_4`)),
  `row_5` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_5`)),
  `row_6` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_6`)),
  `row_7` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_7`)),
  `row_8` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_8`)),
  `row_9` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_9`)),
  `row_10` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_10`)),
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date_paid` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_minutes`
--

CREATE TABLE `oxford_house_minutes` (
  `id` int(11) NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `meeting_date` varchar(50) DEFAULT NULL,
  `start_time` varchar(50) DEFAULT NULL,
  `tradition_number` varchar(50) DEFAULT NULL,
  `meeting_type_regular` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_emergency` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_interview` tinyint(1) NOT NULL DEFAULT 0,
  `minutes_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `amend_yes` tinyint(1) NOT NULL DEFAULT 0,
  `amend_no` tinyint(1) NOT NULL DEFAULT 0,
  `checking_0` varchar(50) DEFAULT NULL,
  `checking_1` varchar(50) DEFAULT NULL,
  `checking_2` varchar(50) DEFAULT NULL,
  `checking_3` varchar(50) DEFAULT NULL,
  `savings_0` varchar(50) DEFAULT NULL,
  `savings_1` varchar(50) DEFAULT NULL,
  `savings_2` varchar(50) DEFAULT NULL,
  `savings_3` varchar(50) DEFAULT NULL,
  `savings_4` varchar(50) DEFAULT NULL,
  `petty_0` varchar(50) DEFAULT NULL,
  `petty_1` varchar(50) DEFAULT NULL,
  `petty_2` varchar(50) DEFAULT NULL,
  `petty_3` varchar(50) DEFAULT NULL,
  `petty_receipts_yes` tinyint(1) NOT NULL DEFAULT 0,
  `petty_receipts_no` tinyint(1) NOT NULL DEFAULT 0,
  `treasurer_comments` text DEFAULT NULL,
  `treasurer_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `comptroller_comments` text DEFAULT NULL,
  `comptroller_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `ees_plan` longtext DEFAULT NULL,
  `coordinator_report` text DEFAULT NULL,
  `coordinator_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `housing_services_report` text DEFAULT NULL,
  `hsr_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_n` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_n` tinyint(1) NOT NULL DEFAULT 0,
  `unfinished_business` text DEFAULT NULL,
  `vacancy_updated_y` tinyint(1) NOT NULL DEFAULT 0,
  `vacancy_updated_n` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_y` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_n` tinyint(1) NOT NULL DEFAULT 0,
  `new_business` longtext DEFAULT NULL,
  `adjourn_hour` varchar(10) DEFAULT NULL,
  `adjourn_min` varchar(10) DEFAULT NULL,
  `secretary_name` varchar(255) DEFAULT NULL,
  `secretary_signature` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `roll_name_1` text DEFAULT NULL,
  `roll_y_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_2` text DEFAULT NULL,
  `roll_y_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_3` text DEFAULT NULL,
  `roll_y_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_4` text DEFAULT NULL,
  `roll_y_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_5` text DEFAULT NULL,
  `roll_y_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_6` text DEFAULT NULL,
  `roll_y_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_7` text DEFAULT NULL,
  `roll_y_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_8` text DEFAULT NULL,
  `roll_y_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_9` text DEFAULT NULL,
  `roll_y_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_10` text DEFAULT NULL,
  `roll_y_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_11` text DEFAULT NULL,
  `roll_y_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_12` text DEFAULT NULL,
  `roll_y_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_13` text DEFAULT NULL,
  `roll_y_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_14` text DEFAULT NULL,
  `roll_y_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_15` text DEFAULT NULL,
  `roll_y_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_16` text DEFAULT NULL,
  `roll_y_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_17` text DEFAULT NULL,
  `roll_y_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_18` text DEFAULT NULL,
  `roll_y_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_19` text DEFAULT NULL,
  `roll_y_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_20` text DEFAULT NULL,
  `roll_y_20` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_20` tinyint(1) NOT NULL DEFAULT 0,
  `comp_name_1` text DEFAULT NULL,
  `comp_bal_1` text DEFAULT NULL,
  `comp_name_2` text DEFAULT NULL,
  `comp_bal_2` text DEFAULT NULL,
  `comp_name_3` text DEFAULT NULL,
  `comp_bal_3` text DEFAULT NULL,
  `comp_name_4` text DEFAULT NULL,
  `comp_bal_4` text DEFAULT NULL,
  `comp_name_5` text DEFAULT NULL,
  `comp_bal_5` text DEFAULT NULL,
  `comp_name_6` text DEFAULT NULL,
  `comp_bal_6` text DEFAULT NULL,
  `comp_name_7` text DEFAULT NULL,
  `comp_bal_7` text DEFAULT NULL,
  `comp_name_8` text DEFAULT NULL,
  `comp_bal_8` text DEFAULT NULL,
  `comp_name_9` text DEFAULT NULL,
  `comp_bal_9` text DEFAULT NULL,
  `comp_name_10` text DEFAULT NULL,
  `comp_bal_10` text DEFAULT NULL,
  `comp_name_11` text DEFAULT NULL,
  `comp_bal_11` text DEFAULT NULL,
  `comp_name_12` text DEFAULT NULL,
  `comp_bal_12` text DEFAULT NULL,
  `comp_name_13` text DEFAULT NULL,
  `comp_bal_13` text DEFAULT NULL,
  `comp_name_14` text DEFAULT NULL,
  `comp_bal_14` text DEFAULT NULL,
  `comp_name_15` text DEFAULT NULL,
  `comp_bal_15` text DEFAULT NULL,
  `comp_name_16` text DEFAULT NULL,
  `comp_bal_16` text DEFAULT NULL,
  `comp_name_17` text DEFAULT NULL,
  `comp_bal_17` text DEFAULT NULL,
  `comp_name_18` text DEFAULT NULL,
  `comp_bal_18` text DEFAULT NULL,
  `comp_name_19` text DEFAULT NULL,
  `comp_bal_19` text DEFAULT NULL,
  `comp_name_20` text DEFAULT NULL,
  `comp_bal_20` text DEFAULT NULL,
  `secretary_signed_date` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_interview_minutes`
--

CREATE TABLE `oxford_interview_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `interview_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `interview_date` date DEFAULT NULL,
  `interviewer_name` varchar(255) NOT NULL DEFAULT '',
  `contact_phone` varchar(100) NOT NULL DEFAULT '',
  `outcome_status` varchar(100) NOT NULL DEFAULT '',
  `vote_percent` varchar(50) NOT NULL DEFAULT '',
  `move_in_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `intro_notes` mediumtext DEFAULT NULL,
  `interviewer_do_notes` mediumtext DEFAULT NULL,
  `interviewer_dont_notes` mediumtext DEFAULT NULL,
  `closing_notes` mediumtext DEFAULT NULL,
  `roll_name_1` varchar(255) NOT NULL DEFAULT '',
  `roll_present_1` varchar(20) NOT NULL DEFAULT '',
  `roll_name_2` varchar(255) NOT NULL DEFAULT '',
  `roll_present_2` varchar(20) NOT NULL DEFAULT '',
  `roll_name_3` varchar(255) NOT NULL DEFAULT '',
  `roll_present_3` varchar(20) NOT NULL DEFAULT '',
  `roll_name_4` varchar(255) NOT NULL DEFAULT '',
  `roll_present_4` varchar(20) NOT NULL DEFAULT '',
  `roll_name_5` varchar(255) NOT NULL DEFAULT '',
  `roll_present_5` varchar(20) NOT NULL DEFAULT '',
  `roll_name_6` varchar(255) NOT NULL DEFAULT '',
  `roll_present_6` varchar(20) NOT NULL DEFAULT '',
  `roll_name_7` varchar(255) NOT NULL DEFAULT '',
  `roll_present_7` varchar(20) NOT NULL DEFAULT '',
  `roll_name_8` varchar(255) NOT NULL DEFAULT '',
  `roll_present_8` varchar(20) NOT NULL DEFAULT '',
  `roll_name_9` varchar(255) NOT NULL DEFAULT '',
  `roll_present_9` varchar(20) NOT NULL DEFAULT '',
  `roll_name_10` varchar(255) NOT NULL DEFAULT '',
  `roll_present_10` varchar(20) NOT NULL DEFAULT '',
  `roll_name_11` varchar(255) NOT NULL DEFAULT '',
  `roll_present_11` varchar(20) NOT NULL DEFAULT '',
  `roll_name_12` varchar(255) NOT NULL DEFAULT '',
  `roll_present_12` varchar(20) NOT NULL DEFAULT '',
  `q1` mediumtext DEFAULT NULL,
  `q2` mediumtext DEFAULT NULL,
  `q3` mediumtext DEFAULT NULL,
  `q4` mediumtext DEFAULT NULL,
  `q5` mediumtext DEFAULT NULL,
  `q6` mediumtext DEFAULT NULL,
  `q7` mediumtext DEFAULT NULL,
  `q8` mediumtext DEFAULT NULL,
  `q9` mediumtext DEFAULT NULL,
  `q10` mediumtext DEFAULT NULL,
  `q11` mediumtext DEFAULT NULL,
  `q12` mediumtext DEFAULT NULL,
  `q13` mediumtext DEFAULT NULL,
  `q14` mediumtext DEFAULT NULL,
  `q15` mediumtext DEFAULT NULL,
  `q16` mediumtext DEFAULT NULL,
  `q17` mediumtext DEFAULT NULL,
  `q18` mediumtext DEFAULT NULL,
  `q19` mediumtext DEFAULT NULL,
  `q20` mediumtext DEFAULT NULL,
  `q21` mediumtext DEFAULT NULL,
  `q22` mediumtext DEFAULT NULL,
  `q23` mediumtext DEFAULT NULL,
  `q24` mediumtext DEFAULT NULL,
  `q25` mediumtext DEFAULT NULL,
  `q26` mediumtext DEFAULT NULL,
  `q27` mediumtext DEFAULT NULL,
  `q28` mediumtext DEFAULT NULL,
  `q29` mediumtext DEFAULT NULL,
  `q30` mediumtext DEFAULT NULL,
  `q31` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_member_financial_contracts`
--

CREATE TABLE `oxford_member_financial_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(100) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `total_amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgement_name` varchar(255) NOT NULL DEFAULT '',
  `signature_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(100) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `scanned_contract` varchar(255) NOT NULL DEFAULT '',
  `contract_stamp` varchar(50) NOT NULL DEFAULT '',
  `contract_stamp_at` datetime DEFAULT NULL,
  `contract_stamp_by_ip` varchar(64) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_new_member_packets`
--

CREATE TABLE `oxford_new_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `packet_date` date DEFAULT NULL,
  `check_membership_application` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_manual` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_guidelines` tinyint(1) NOT NULL DEFAULT 0,
  `check_membership_agreement` tinyint(1) NOT NULL DEFAULT 0,
  `check_plan_for_recovery` tinyint(1) NOT NULL DEFAULT 0,
  `check_relapse_contingency` tinyint(1) NOT NULL DEFAULT 0,
  `check_medical_release` tinyint(1) NOT NULL DEFAULT 0,
  `check_property_list` tinyint(1) NOT NULL DEFAULT 0,
  `member_initials_1` varchar(20) NOT NULL DEFAULT '',
  `president_initials_1` varchar(20) NOT NULL DEFAULT '',
  `member_initials_2` varchar(20) NOT NULL DEFAULT '',
  `president_initials_2` varchar(20) NOT NULL DEFAULT '',
  `member_initials_3` varchar(20) NOT NULL DEFAULT '',
  `president_initials_3` varchar(20) NOT NULL DEFAULT '',
  `member_initials_4` varchar(20) NOT NULL DEFAULT '',
  `president_initials_4` varchar(20) NOT NULL DEFAULT '',
  `member_initials_5` varchar(20) NOT NULL DEFAULT '',
  `president_initials_5` varchar(20) NOT NULL DEFAULT '',
  `member_initials_6` varchar(20) NOT NULL DEFAULT '',
  `president_initials_6` varchar(20) NOT NULL DEFAULT '',
  `member_initials_7` varchar(20) NOT NULL DEFAULT '',
  `president_initials_7` varchar(20) NOT NULL DEFAULT '',
  `member_initials_8` varchar(20) NOT NULL DEFAULT '',
  `president_initials_8` varchar(20) NOT NULL DEFAULT '',
  `member_signature_1` varchar(255) NOT NULL DEFAULT '',
  `member_signature_date_1` date DEFAULT NULL,
  `president_signature_1` varchar(255) NOT NULL DEFAULT '',
  `president_signature_date_1` date DEFAULT NULL,
  `membership_agreement_text` longtext DEFAULT NULL,
  `agreement_member_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_date` date DEFAULT NULL,
  `agreement_president_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_date` date DEFAULT NULL,
  `plan_name` varchar(255) NOT NULL DEFAULT '',
  `plan_text` longtext DEFAULT NULL,
  `aftercare_program` longtext DEFAULT NULL,
  `has_sponsor` varchar(10) NOT NULL DEFAULT '',
  `sponsor_by_date` date DEFAULT NULL,
  `meetings_per_week` varchar(50) NOT NULL DEFAULT '',
  `meeting_types` varchar(255) NOT NULL DEFAULT '',
  `plan_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_signature_date` date DEFAULT NULL,
  `plan_president_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_president_date` date DEFAULT NULL,
  `relapse_name` varchar(255) NOT NULL DEFAULT '',
  `relapse_family` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_friend` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_detox` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other_text` varchar(255) NOT NULL DEFAULT '',
  `relapse_details` longtext DEFAULT NULL,
  `notify_rows_json` longtext DEFAULT NULL,
  `pickup_rows_json` longtext DEFAULT NULL,
  `relapse_member_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_member_date` date DEFAULT NULL,
  `relapse_president_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_president_date` date DEFAULT NULL,
  `relapse_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_witness_date` date DEFAULT NULL,
  `medical_name` varchar(255) NOT NULL DEFAULT '',
  `physician_name` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(50) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance_info` varchar(255) NOT NULL DEFAULT '',
  `allergies` longtext DEFAULT NULL,
  `medications` longtext DEFAULT NULL,
  `medical_history` longtext DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `blood_type` varchar(20) NOT NULL DEFAULT '',
  `medical_contacts_json` longtext DEFAULT NULL,
  `medical_signature` varchar(255) NOT NULL DEFAULT '',
  `medical_date` date DEFAULT NULL,
  `property_name` varchar(255) NOT NULL DEFAULT '',
  `property_move_in_date` date DEFAULT NULL,
  `property_rows_json` longtext DEFAULT NULL,
  `uploaded_copy_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_path` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_mime` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_red_creek_member_packets`
--

CREATE TABLE `oxford_red_creek_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `signature_date` date DEFAULT NULL,
  `refund_move_in_date` date DEFAULT NULL,
  `new_member_signature` varchar(255) NOT NULL DEFAULT '',
  `new_member_signature_date` date DEFAULT NULL,
  `president_hsr_signature` varchar(255) NOT NULL DEFAULT '',
  `president_hsr_signature_date` date DEFAULT NULL,
  `expectations_signature` varchar(255) NOT NULL DEFAULT '',
  `expectations_signature_date` date DEFAULT NULL,
  `medication_signature` varchar(255) NOT NULL DEFAULT '',
  `medication_signature_date` date DEFAULT NULL,
  `emergency_name` varchar(255) NOT NULL DEFAULT '',
  `emergency_age` varchar(50) NOT NULL DEFAULT '',
  `emergency_dob` varchar(50) NOT NULL DEFAULT '',
  `blood_type` varchar(50) NOT NULL DEFAULT '',
  `primary_physician` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(100) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance` varchar(255) NOT NULL DEFAULT '',
  `allergies` text DEFAULT NULL,
  `medications` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `contact1_name` varchar(255) NOT NULL DEFAULT '',
  `contact1_phone` varchar(100) NOT NULL DEFAULT '',
  `contact2_name` varchar(255) NOT NULL DEFAULT '',
  `contact2_phone` varchar(100) NOT NULL DEFAULT '',
  `contact3_name` varchar(255) NOT NULL DEFAULT '',
  `contact3_phone` varchar(100) NOT NULL DEFAULT '',
  `property_items` longtext DEFAULT NULL,
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `property_signature` varchar(255) NOT NULL DEFAULT '',
  `property_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `property_date` date DEFAULT NULL,
  `property_removed_date` date DEFAULT NULL,
  `property_removed_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `move_in_fee` decimal(10,2) DEFAULT 250.00,
  `other_charge` decimal(10,2) DEFAULT 0.00,
  `total_due` decimal(10,2) DEFAULT NULL,
  `scan_path` varchar(500) NOT NULL DEFAULT '',
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_residency_forms`
--

CREATE TABLE `oxford_residency_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) DEFAULT '',
  `letter_date` varchar(20) DEFAULT '',
  `house_name` varchar(255) DEFAULT '',
  `accepted_date` varchar(20) DEFAULT '',
  `address_line` varchar(255) DEFAULT '',
  `city_state_zip` varchar(255) DEFAULT '',
  `move_in_fee` decimal(10,2) DEFAULT 0.00,
  `weekly_rent` decimal(10,2) DEFAULT 0.00,
  `president_contact` varchar(255) DEFAULT '',
  `president_name` varchar(255) DEFAULT '',
  `president_signature` varchar(255) DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_shopping_lists`
--

CREATE TABLE `oxford_shopping_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `shopping_date` date DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Oxford House Shopping List',
  `items_json` longtext NOT NULL,
  `checked_json` longtext NOT NULL,
  `total_checked` int(11) NOT NULL DEFAULT 0,
  `total_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_items` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy_path` varchar(500) DEFAULT NULL,
  `uploaded_copy_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledgers`
--

CREATE TABLE `petty_cash_ledgers` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `ledger_date` date DEFAULT NULL,
  `beginning_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledger_rows`
--

CREATE TABLE `petty_cash_ledger_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `ledger_id` int(10) UNSIGNED NOT NULL,
  `row_index` int(11) NOT NULL,
  `txn_date` varchar(50) NOT NULL DEFAULT '',
  `products_purchased` varchar(255) NOT NULL DEFAULT '',
  `vendor` varchar(255) NOT NULL DEFAULT '',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reimbursement_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `safety_inspection_checklists`
--

CREATE TABLE `safety_inspection_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `inspection_date` date DEFAULT NULL,
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `checklist_json` longtext NOT NULL,
  `satisfactory_total` int(11) NOT NULL DEFAULT 0,
  `unsatisfactory_total` int(11) NOT NULL DEFAULT 0,
  `completed_total` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy` varchar(500) DEFAULT NULL,
  `original_upload_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_checklist_key` (`checklist_key`);

--
-- Indexes for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_meeting_date` (`meeting_date`);

--
-- Indexes for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_dates` (`week_start`,`week_end`);

--
-- Indexes for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_tour_date` (`tour_date`);

--
-- Indexes for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `house_name` (`house_name`);

--
-- Indexes for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_label` (`schedule_label`);

--
-- Indexes for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`);

--
-- Indexes for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_name_idx` (`tenant_name`),
  ADD KEY `updated_at_idx` (`updated_at`);

--
-- Indexes for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resident_name` (`resident_name`),
  ADD KEY `idx_sheet_date` (`sheet_date`);

--
-- Indexes for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sheet_id` (`sheet_id`),
  ADD KEY `idx_row_number` (`row_number`);

--
-- Indexes for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_form_date` (`form_date`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_contract_date` (`contract_date`);

--
-- Indexes for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date_completed` (`date_completed`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- Indexes for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_dates` (`house_name`,`date_from`,`date_to`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_date_from` (`date_from`),
  ADD KEY `idx_date_to` (`date_to`);

--
-- Indexes for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_start` (`week_start`),
  ADD KEY `idx_week_end` (`week_end`);

--
-- Indexes for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`),
  ADD KEY `idx_house_date` (`house_name`,`meeting_date`);

--
-- Indexes for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_member_date_house` (`member_name`,`contract_date`,`house_name`);

--
-- Indexes for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_member` (`house_name`,`member_name`);

--
-- Indexes for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_shopping_date` (`shopping_date`);

--
-- Indexes for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_date` (`house_name`,`ledger_date`);

--
-- Indexes for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_petty_cash_ledger_rows_ledger` (`ledger_id`);

--
-- Indexes for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inspection_date` (`inspection_date`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `otro_dia`
--
CREATE DATABASE IF NOT EXISTS `otro_dia` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `otro_dia`;

-- --------------------------------------------------------

--
-- Table structure for table `bedroom_essentials_checklists`
--

CREATE TABLE `bedroom_essentials_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `checklist_key` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Bedroom Essentials – Checklist',
  `items_json` longtext NOT NULL,
  `dark_mode` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chapter_meeting_minutes`
--

CREATE TABLE `chapter_meeting_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `meeting_date` varchar(50) NOT NULL,
  `form_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ees_member_ledger`
--

CREATE TABLE `ees_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(2) NOT NULL DEFAULT '',
  `move_in_day` varchar(2) NOT NULL DEFAULT '',
  `move_in_year` varchar(4) NOT NULL DEFAULT '',
  `ledger_rows` longtext DEFAULT NULL,
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_ledger_records`
--

CREATE TABLE `house_ledger_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `notes` mediumtext DEFAULT NULL,
  `rows_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_tour_forms`
--

CREATE TABLE `house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `tour_date` date DEFAULT NULL,
  `tour_time` varchar(20) NOT NULL DEFAULT '',
  `smoking_area` varchar(10) NOT NULL DEFAULT '',
  `notes` text DEFAULT NULL,
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `signature` varchar(255) NOT NULL DEFAULT '',
  `items_json` longtext DEFAULT NULL,
  `section_totals_json` text DEFAULT NULL,
  `grand_total` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_houses`
--

CREATE TABLE `house_visit_houses` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(150) NOT NULL,
  `meeting_day` varchar(20) NOT NULL DEFAULT '',
  `meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_reports`
--

CREATE TABLE `house_visit_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(100) NOT NULL DEFAULT '',
  `president` varchar(255) NOT NULL DEFAULT '',
  `secretary` varchar(255) NOT NULL DEFAULT '',
  `treasurer` varchar(255) NOT NULL DEFAULT '',
  `comptroller` varchar(255) NOT NULL DEFAULT '',
  `coordinator` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep` varchar(255) NOT NULL DEFAULT '',
  `overall_appearance` varchar(10) NOT NULL DEFAULT '',
  `overall_appearance_comments` text DEFAULT NULL,
  `members_behind_ees` decimal(10,2) DEFAULT NULL,
  `total_amount_owed` decimal(12,2) DEFAULT NULL,
  `rent_paid_monthly` decimal(12,2) DEFAULT NULL,
  `ees_paid_weekly` decimal(12,2) DEFAULT NULL,
  `utilities_monthly` decimal(12,2) DEFAULT NULL,
  `house_business_meeting` varchar(10) NOT NULL DEFAULT '',
  `house_business_comments` text DEFAULT NULL,
  `rating_reading_traditions` varchar(10) NOT NULL DEFAULT '',
  `rating_reading_minutes` varchar(10) NOT NULL DEFAULT '',
  `rating_treasurer_report` varchar(10) NOT NULL DEFAULT '',
  `rating_comptroller_report` varchar(10) NOT NULL DEFAULT '',
  `rating_coordinator_report` varchar(10) NOT NULL DEFAULT '',
  `rating_maintains_guidelines` varchar(10) NOT NULL DEFAULT '',
  `rating_handling_business` varchar(10) NOT NULL DEFAULT '',
  `rating_organization_order` varchar(10) NOT NULL DEFAULT '',
  `financial_comments` text DEFAULT NULL,
  `first_visit_date` varchar(50) NOT NULL DEFAULT '',
  `narcan_present` varchar(10) NOT NULL DEFAULT '',
  `narcan_trained` varchar(10) NOT NULL DEFAULT '',
  `follow_up_visit_dates` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep_signature` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_schedules`
--

CREATE TABLE `house_visit_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `schedule_label` varchar(150) NOT NULL,
  `base_month` varchar(7) NOT NULL DEFAULT '',
  `month_count` int(11) NOT NULL DEFAULT 1,
  `repeat_cycle` tinyint(1) NOT NULL DEFAULT 0,
  `step_size` int(11) NOT NULL DEFAULT 1,
  `month_label` varchar(50) NOT NULL,
  `month_index` int(11) NOT NULL DEFAULT 1,
  `visiting_house` varchar(150) NOT NULL,
  `host_house` varchar(150) NOT NULL,
  `host_meeting_day` varchar(20) NOT NULL DEFAULT '',
  `host_meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `housing_service_representative_reports`
--

CREATE TABLE `housing_service_representative_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `week_from` varchar(100) NOT NULL DEFAULT '',
  `week_to` varchar(100) NOT NULL DEFAULT '',
  `house_visit` text DEFAULT NULL,
  `audit_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `audit_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `next_chapter_meeting` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `next_state_meeting` text DEFAULT NULL,
  `number_of_applications` text DEFAULT NULL,
  `number_contacted_plan` longtext DEFAULT NULL,
  `new_members` longtext DEFAULT NULL,
  `interviews_setup` longtext DEFAULT NULL,
  `chapter_news` longtext DEFAULT NULL,
  `chapter_meeting_recap` longtext DEFAULT NULL,
  `upcoming_unity` longtext DEFAULT NULL,
  `upcoming_presentations` longtext DEFAULT NULL,
  `hsr_name` varchar(255) NOT NULL DEFAULT '',
  `report_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hsc_meeting_minutes_json`
--

CREATE TABLE `hsc_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `landlord_verification_forms`
--

CREATE TABLE `landlord_verification_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_name` varchar(255) NOT NULL DEFAULT '',
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `landlord_manager_name` varchar(255) NOT NULL DEFAULT '',
  `manager_street` varchar(255) NOT NULL DEFAULT '',
  `manager_city` varchar(150) NOT NULL DEFAULT '',
  `manager_state` varchar(50) NOT NULL DEFAULT '',
  `manager_zip` varchar(25) NOT NULL DEFAULT '',
  `manager_county` varchar(150) NOT NULL DEFAULT '',
  `manager_phone` varchar(50) NOT NULL DEFAULT '',
  `manager_email` varchar(255) NOT NULL DEFAULT '',
  `rental_street` varchar(255) NOT NULL DEFAULT '',
  `rental_apt_lot` varchar(100) NOT NULL DEFAULT '',
  `rental_city` varchar(150) NOT NULL DEFAULT '',
  `rental_state` varchar(50) NOT NULL DEFAULT '',
  `rental_zip` varchar(25) NOT NULL DEFAULT '',
  `rental_county` varchar(150) NOT NULL DEFAULT '',
  `bedrooms` varchar(20) NOT NULL DEFAULT '',
  `lease_start_date` date DEFAULT NULL,
  `lease_end_date` date DEFAULT NULL,
  `monthly_rent_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `next_payment_due_date` date DEFAULT NULL,
  `last_payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `last_payment_date` date DEFAULT NULL,
  `tenant_in_arrears` tinyint(1) NOT NULL DEFAULT 0,
  `amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `arrears_period_from` date DEFAULT NULL,
  `arrears_period_to` date DEFAULT NULL,
  `receiving_other_assistance` tinyint(1) NOT NULL DEFAULT 0,
  `other_assistance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_assistance_period` varchar(100) NOT NULL DEFAULT '',
  `payment_method` varchar(20) NOT NULL DEFAULT 'check',
  `check_payable_to` varchar(255) NOT NULL DEFAULT '',
  `send_to_landlord_address` tinyint(1) NOT NULL DEFAULT 1,
  `alternative_address` text DEFAULT NULL,
  `cert_name` varchar(255) NOT NULL DEFAULT '',
  `cert_title` varchar(255) NOT NULL DEFAULT '',
  `cert_signature` varchar(255) NOT NULL DEFAULT '',
  `cert_date` date DEFAULT NULL,
  `calc_balance_remaining` decimal(10,2) NOT NULL DEFAULT 0.00,
  `calc_total_assistance_gap` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheets`
--

CREATE TABLE `medication_count_sheets` (
  `id` int(10) UNSIGNED NOT NULL,
  `resident_name` varchar(255) NOT NULL,
  `sheet_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheet_rows`
--

CREATE TABLE `medication_count_sheet_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `sheet_id` int(10) UNSIGNED NOT NULL,
  `row_number` int(11) NOT NULL,
  `entry_date` varchar(50) DEFAULT NULL,
  `medication_name` varchar(255) DEFAULT NULL,
  `dosage` varchar(255) DEFAULT NULL,
  `frequency` varchar(255) DEFAULT NULL,
  `previous_count` varchar(100) DEFAULT NULL,
  `current_count` varchar(100) DEFAULT NULL,
  `member_initials` varchar(50) DEFAULT NULL,
  `witness_initials` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `new_house_tour_forms`
--

CREATE TABLE `new_house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `form_date` varchar(50) NOT NULL DEFAULT '',
  `form_time` varchar(50) NOT NULL DEFAULT '',
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `inspector_signature` varchar(255) NOT NULL DEFAULT '',
  `smoking_area_score` varchar(5) NOT NULL DEFAULT '',
  `smoking_area_comment` text DEFAULT NULL,
  `notes_text` text DEFAULT NULL,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_average` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payload_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nightly_kitchen_schedules`
--

CREATE TABLE `nightly_kitchen_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `trash_to_curb_on` varchar(255) NOT NULL DEFAULT '',
  `schedule_data` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_chore_lists`
--

CREATE TABLE `oxford_chore_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT 'CHORE LIST',
  `week1_start` varchar(20) NOT NULL DEFAULT '',
  `week2_start` varchar(20) NOT NULL DEFAULT '',
  `week3_start` varchar(20) NOT NULL DEFAULT '',
  `week4_start` varchar(20) NOT NULL DEFAULT '',
  `week5_start` varchar(20) NOT NULL DEFAULT '',
  `week6_start` varchar(20) NOT NULL DEFAULT '',
  `week7_start` varchar(20) NOT NULL DEFAULT '',
  `week8_start` varchar(20) NOT NULL DEFAULT '',
  `form_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_disruptive_contracts`
--

CREATE TABLE `oxford_disruptive_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_subject` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(50) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `behavior_1` text DEFAULT NULL,
  `behavior_2` text DEFAULT NULL,
  `behavior_3` text DEFAULT NULL,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgment_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(50) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_original_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_stored_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_mime` varchar(100) NOT NULL DEFAULT '',
  `uploaded_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_financial_audits`
--

CREATE TABLE `oxford_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `date_completed` date DEFAULT NULL,
  `bank_statement_ending_date` date DEFAULT NULL,
  `bank_statement_ending_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_past_due_bills` decimal(12,2) NOT NULL DEFAULT 0.00,
  `savings_account_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_outstanding_ees` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deposits_json` longtext DEFAULT NULL,
  `checks_json` longtext DEFAULT NULL,
  `treasurer_signature` varchar(255) DEFAULT NULL,
  `comptroller_signature` varchar(255) DEFAULT NULL,
  `president_signature` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_audits`
--

CREATE TABLE `oxford_house_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `completed_month` varchar(10) NOT NULL DEFAULT '',
  `completed_day` varchar(10) NOT NULL DEFAULT '',
  `completed_year` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_month` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_day` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_year` varchar(10) NOT NULL DEFAULT '',
  `past_due_bills` varchar(50) NOT NULL DEFAULT '',
  `savings_balance` varchar(50) NOT NULL DEFAULT '',
  `outstanding_ees` varchar(50) NOT NULL DEFAULT '',
  `bank_statement_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `deposits_total` varchar(50) NOT NULL DEFAULT '',
  `checks_total` varchar(50) NOT NULL DEFAULT '',
  `balance_after_audit` varchar(50) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_signature` varchar(255) NOT NULL DEFAULT '',
  `comptroller_signature` varchar(255) NOT NULL DEFAULT '',
  `president_signature` varchar(255) NOT NULL DEFAULT '',
  `checks_rows` longtext DEFAULT NULL,
  `deposits_rows` longtext DEFAULT NULL,
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `scan_stored_name` varchar(255) NOT NULL DEFAULT '',
  `scan_path` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_reports`
--

CREATE TABLE `oxford_house_financial_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `report_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_ledger_forms`
--

CREATE TABLE `oxford_house_ledger_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `rows_json` longtext DEFAULT NULL,
  `totals_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_meeting_minutes_json`
--

CREATE TABLE `oxford_house_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_member_ledger`
--

CREATE TABLE `oxford_house_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(10) NOT NULL DEFAULT '',
  `move_in_day` varchar(10) NOT NULL DEFAULT '',
  `move_in_year` varchar(10) NOT NULL DEFAULT '',
  `row_1` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_1`)),
  `row_2` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_2`)),
  `row_3` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_3`)),
  `row_4` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_4`)),
  `row_5` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_5`)),
  `row_6` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_6`)),
  `row_7` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_7`)),
  `row_8` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_8`)),
  `row_9` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_9`)),
  `row_10` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_10`)),
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date_paid` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_minutes`
--

CREATE TABLE `oxford_house_minutes` (
  `id` int(11) NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `meeting_date` varchar(50) DEFAULT NULL,
  `start_time` varchar(50) DEFAULT NULL,
  `tradition_number` varchar(50) DEFAULT NULL,
  `meeting_type_regular` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_emergency` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_interview` tinyint(1) NOT NULL DEFAULT 0,
  `minutes_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `amend_yes` tinyint(1) NOT NULL DEFAULT 0,
  `amend_no` tinyint(1) NOT NULL DEFAULT 0,
  `checking_0` varchar(50) DEFAULT NULL,
  `checking_1` varchar(50) DEFAULT NULL,
  `checking_2` varchar(50) DEFAULT NULL,
  `checking_3` varchar(50) DEFAULT NULL,
  `savings_0` varchar(50) DEFAULT NULL,
  `savings_1` varchar(50) DEFAULT NULL,
  `savings_2` varchar(50) DEFAULT NULL,
  `savings_3` varchar(50) DEFAULT NULL,
  `savings_4` varchar(50) DEFAULT NULL,
  `petty_0` varchar(50) DEFAULT NULL,
  `petty_1` varchar(50) DEFAULT NULL,
  `petty_2` varchar(50) DEFAULT NULL,
  `petty_3` varchar(50) DEFAULT NULL,
  `petty_receipts_yes` tinyint(1) NOT NULL DEFAULT 0,
  `petty_receipts_no` tinyint(1) NOT NULL DEFAULT 0,
  `treasurer_comments` text DEFAULT NULL,
  `treasurer_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `comptroller_comments` text DEFAULT NULL,
  `comptroller_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `ees_plan` longtext DEFAULT NULL,
  `coordinator_report` text DEFAULT NULL,
  `coordinator_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `housing_services_report` text DEFAULT NULL,
  `hsr_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_n` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_n` tinyint(1) NOT NULL DEFAULT 0,
  `unfinished_business` text DEFAULT NULL,
  `vacancy_updated_y` tinyint(1) NOT NULL DEFAULT 0,
  `vacancy_updated_n` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_y` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_n` tinyint(1) NOT NULL DEFAULT 0,
  `new_business` longtext DEFAULT NULL,
  `adjourn_hour` varchar(10) DEFAULT NULL,
  `adjourn_min` varchar(10) DEFAULT NULL,
  `secretary_name` varchar(255) DEFAULT NULL,
  `secretary_signature` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `roll_name_1` text DEFAULT NULL,
  `roll_y_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_2` text DEFAULT NULL,
  `roll_y_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_3` text DEFAULT NULL,
  `roll_y_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_4` text DEFAULT NULL,
  `roll_y_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_5` text DEFAULT NULL,
  `roll_y_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_6` text DEFAULT NULL,
  `roll_y_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_7` text DEFAULT NULL,
  `roll_y_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_8` text DEFAULT NULL,
  `roll_y_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_9` text DEFAULT NULL,
  `roll_y_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_10` text DEFAULT NULL,
  `roll_y_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_11` text DEFAULT NULL,
  `roll_y_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_12` text DEFAULT NULL,
  `roll_y_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_13` text DEFAULT NULL,
  `roll_y_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_14` text DEFAULT NULL,
  `roll_y_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_15` text DEFAULT NULL,
  `roll_y_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_16` text DEFAULT NULL,
  `roll_y_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_17` text DEFAULT NULL,
  `roll_y_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_18` text DEFAULT NULL,
  `roll_y_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_19` text DEFAULT NULL,
  `roll_y_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_20` text DEFAULT NULL,
  `roll_y_20` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_20` tinyint(1) NOT NULL DEFAULT 0,
  `comp_name_1` text DEFAULT NULL,
  `comp_bal_1` text DEFAULT NULL,
  `comp_name_2` text DEFAULT NULL,
  `comp_bal_2` text DEFAULT NULL,
  `comp_name_3` text DEFAULT NULL,
  `comp_bal_3` text DEFAULT NULL,
  `comp_name_4` text DEFAULT NULL,
  `comp_bal_4` text DEFAULT NULL,
  `comp_name_5` text DEFAULT NULL,
  `comp_bal_5` text DEFAULT NULL,
  `comp_name_6` text DEFAULT NULL,
  `comp_bal_6` text DEFAULT NULL,
  `comp_name_7` text DEFAULT NULL,
  `comp_bal_7` text DEFAULT NULL,
  `comp_name_8` text DEFAULT NULL,
  `comp_bal_8` text DEFAULT NULL,
  `comp_name_9` text DEFAULT NULL,
  `comp_bal_9` text DEFAULT NULL,
  `comp_name_10` text DEFAULT NULL,
  `comp_bal_10` text DEFAULT NULL,
  `comp_name_11` text DEFAULT NULL,
  `comp_bal_11` text DEFAULT NULL,
  `comp_name_12` text DEFAULT NULL,
  `comp_bal_12` text DEFAULT NULL,
  `comp_name_13` text DEFAULT NULL,
  `comp_bal_13` text DEFAULT NULL,
  `comp_name_14` text DEFAULT NULL,
  `comp_bal_14` text DEFAULT NULL,
  `comp_name_15` text DEFAULT NULL,
  `comp_bal_15` text DEFAULT NULL,
  `comp_name_16` text DEFAULT NULL,
  `comp_bal_16` text DEFAULT NULL,
  `comp_name_17` text DEFAULT NULL,
  `comp_bal_17` text DEFAULT NULL,
  `comp_name_18` text DEFAULT NULL,
  `comp_bal_18` text DEFAULT NULL,
  `comp_name_19` text DEFAULT NULL,
  `comp_bal_19` text DEFAULT NULL,
  `comp_name_20` text DEFAULT NULL,
  `comp_bal_20` text DEFAULT NULL,
  `secretary_signed_date` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_interview_minutes`
--

CREATE TABLE `oxford_interview_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `interview_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `interview_date` date DEFAULT NULL,
  `interviewer_name` varchar(255) NOT NULL DEFAULT '',
  `contact_phone` varchar(100) NOT NULL DEFAULT '',
  `outcome_status` varchar(100) NOT NULL DEFAULT '',
  `vote_percent` varchar(50) NOT NULL DEFAULT '',
  `move_in_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `intro_notes` mediumtext DEFAULT NULL,
  `interviewer_do_notes` mediumtext DEFAULT NULL,
  `interviewer_dont_notes` mediumtext DEFAULT NULL,
  `closing_notes` mediumtext DEFAULT NULL,
  `roll_name_1` varchar(255) NOT NULL DEFAULT '',
  `roll_present_1` varchar(20) NOT NULL DEFAULT '',
  `roll_name_2` varchar(255) NOT NULL DEFAULT '',
  `roll_present_2` varchar(20) NOT NULL DEFAULT '',
  `roll_name_3` varchar(255) NOT NULL DEFAULT '',
  `roll_present_3` varchar(20) NOT NULL DEFAULT '',
  `roll_name_4` varchar(255) NOT NULL DEFAULT '',
  `roll_present_4` varchar(20) NOT NULL DEFAULT '',
  `roll_name_5` varchar(255) NOT NULL DEFAULT '',
  `roll_present_5` varchar(20) NOT NULL DEFAULT '',
  `roll_name_6` varchar(255) NOT NULL DEFAULT '',
  `roll_present_6` varchar(20) NOT NULL DEFAULT '',
  `roll_name_7` varchar(255) NOT NULL DEFAULT '',
  `roll_present_7` varchar(20) NOT NULL DEFAULT '',
  `roll_name_8` varchar(255) NOT NULL DEFAULT '',
  `roll_present_8` varchar(20) NOT NULL DEFAULT '',
  `roll_name_9` varchar(255) NOT NULL DEFAULT '',
  `roll_present_9` varchar(20) NOT NULL DEFAULT '',
  `roll_name_10` varchar(255) NOT NULL DEFAULT '',
  `roll_present_10` varchar(20) NOT NULL DEFAULT '',
  `roll_name_11` varchar(255) NOT NULL DEFAULT '',
  `roll_present_11` varchar(20) NOT NULL DEFAULT '',
  `roll_name_12` varchar(255) NOT NULL DEFAULT '',
  `roll_present_12` varchar(20) NOT NULL DEFAULT '',
  `q1` mediumtext DEFAULT NULL,
  `q2` mediumtext DEFAULT NULL,
  `q3` mediumtext DEFAULT NULL,
  `q4` mediumtext DEFAULT NULL,
  `q5` mediumtext DEFAULT NULL,
  `q6` mediumtext DEFAULT NULL,
  `q7` mediumtext DEFAULT NULL,
  `q8` mediumtext DEFAULT NULL,
  `q9` mediumtext DEFAULT NULL,
  `q10` mediumtext DEFAULT NULL,
  `q11` mediumtext DEFAULT NULL,
  `q12` mediumtext DEFAULT NULL,
  `q13` mediumtext DEFAULT NULL,
  `q14` mediumtext DEFAULT NULL,
  `q15` mediumtext DEFAULT NULL,
  `q16` mediumtext DEFAULT NULL,
  `q17` mediumtext DEFAULT NULL,
  `q18` mediumtext DEFAULT NULL,
  `q19` mediumtext DEFAULT NULL,
  `q20` mediumtext DEFAULT NULL,
  `q21` mediumtext DEFAULT NULL,
  `q22` mediumtext DEFAULT NULL,
  `q23` mediumtext DEFAULT NULL,
  `q24` mediumtext DEFAULT NULL,
  `q25` mediumtext DEFAULT NULL,
  `q26` mediumtext DEFAULT NULL,
  `q27` mediumtext DEFAULT NULL,
  `q28` mediumtext DEFAULT NULL,
  `q29` mediumtext DEFAULT NULL,
  `q30` mediumtext DEFAULT NULL,
  `q31` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_member_financial_contracts`
--

CREATE TABLE `oxford_member_financial_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(100) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `total_amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgement_name` varchar(255) NOT NULL DEFAULT '',
  `signature_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(100) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `scanned_contract` varchar(255) NOT NULL DEFAULT '',
  `contract_stamp` varchar(50) NOT NULL DEFAULT '',
  `contract_stamp_at` datetime DEFAULT NULL,
  `contract_stamp_by_ip` varchar(64) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_new_member_packets`
--

CREATE TABLE `oxford_new_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `packet_date` date DEFAULT NULL,
  `check_membership_application` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_manual` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_guidelines` tinyint(1) NOT NULL DEFAULT 0,
  `check_membership_agreement` tinyint(1) NOT NULL DEFAULT 0,
  `check_plan_for_recovery` tinyint(1) NOT NULL DEFAULT 0,
  `check_relapse_contingency` tinyint(1) NOT NULL DEFAULT 0,
  `check_medical_release` tinyint(1) NOT NULL DEFAULT 0,
  `check_property_list` tinyint(1) NOT NULL DEFAULT 0,
  `member_initials_1` varchar(20) NOT NULL DEFAULT '',
  `president_initials_1` varchar(20) NOT NULL DEFAULT '',
  `member_initials_2` varchar(20) NOT NULL DEFAULT '',
  `president_initials_2` varchar(20) NOT NULL DEFAULT '',
  `member_initials_3` varchar(20) NOT NULL DEFAULT '',
  `president_initials_3` varchar(20) NOT NULL DEFAULT '',
  `member_initials_4` varchar(20) NOT NULL DEFAULT '',
  `president_initials_4` varchar(20) NOT NULL DEFAULT '',
  `member_initials_5` varchar(20) NOT NULL DEFAULT '',
  `president_initials_5` varchar(20) NOT NULL DEFAULT '',
  `member_initials_6` varchar(20) NOT NULL DEFAULT '',
  `president_initials_6` varchar(20) NOT NULL DEFAULT '',
  `member_initials_7` varchar(20) NOT NULL DEFAULT '',
  `president_initials_7` varchar(20) NOT NULL DEFAULT '',
  `member_initials_8` varchar(20) NOT NULL DEFAULT '',
  `president_initials_8` varchar(20) NOT NULL DEFAULT '',
  `member_signature_1` varchar(255) NOT NULL DEFAULT '',
  `member_signature_date_1` date DEFAULT NULL,
  `president_signature_1` varchar(255) NOT NULL DEFAULT '',
  `president_signature_date_1` date DEFAULT NULL,
  `membership_agreement_text` longtext DEFAULT NULL,
  `agreement_member_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_date` date DEFAULT NULL,
  `agreement_president_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_date` date DEFAULT NULL,
  `plan_name` varchar(255) NOT NULL DEFAULT '',
  `plan_text` longtext DEFAULT NULL,
  `aftercare_program` longtext DEFAULT NULL,
  `has_sponsor` varchar(10) NOT NULL DEFAULT '',
  `sponsor_by_date` date DEFAULT NULL,
  `meetings_per_week` varchar(50) NOT NULL DEFAULT '',
  `meeting_types` varchar(255) NOT NULL DEFAULT '',
  `plan_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_signature_date` date DEFAULT NULL,
  `plan_president_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_president_date` date DEFAULT NULL,
  `relapse_name` varchar(255) NOT NULL DEFAULT '',
  `relapse_family` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_friend` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_detox` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other_text` varchar(255) NOT NULL DEFAULT '',
  `relapse_details` longtext DEFAULT NULL,
  `notify_rows_json` longtext DEFAULT NULL,
  `pickup_rows_json` longtext DEFAULT NULL,
  `relapse_member_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_member_date` date DEFAULT NULL,
  `relapse_president_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_president_date` date DEFAULT NULL,
  `relapse_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_witness_date` date DEFAULT NULL,
  `medical_name` varchar(255) NOT NULL DEFAULT '',
  `physician_name` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(50) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance_info` varchar(255) NOT NULL DEFAULT '',
  `allergies` longtext DEFAULT NULL,
  `medications` longtext DEFAULT NULL,
  `medical_history` longtext DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `blood_type` varchar(20) NOT NULL DEFAULT '',
  `medical_contacts_json` longtext DEFAULT NULL,
  `medical_signature` varchar(255) NOT NULL DEFAULT '',
  `medical_date` date DEFAULT NULL,
  `property_name` varchar(255) NOT NULL DEFAULT '',
  `property_move_in_date` date DEFAULT NULL,
  `property_rows_json` longtext DEFAULT NULL,
  `uploaded_copy_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_path` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_mime` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_red_creek_member_packets`
--

CREATE TABLE `oxford_red_creek_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `signature_date` date DEFAULT NULL,
  `refund_move_in_date` date DEFAULT NULL,
  `new_member_signature` varchar(255) NOT NULL DEFAULT '',
  `new_member_signature_date` date DEFAULT NULL,
  `president_hsr_signature` varchar(255) NOT NULL DEFAULT '',
  `president_hsr_signature_date` date DEFAULT NULL,
  `expectations_signature` varchar(255) NOT NULL DEFAULT '',
  `expectations_signature_date` date DEFAULT NULL,
  `medication_signature` varchar(255) NOT NULL DEFAULT '',
  `medication_signature_date` date DEFAULT NULL,
  `emergency_name` varchar(255) NOT NULL DEFAULT '',
  `emergency_age` varchar(50) NOT NULL DEFAULT '',
  `emergency_dob` varchar(50) NOT NULL DEFAULT '',
  `blood_type` varchar(50) NOT NULL DEFAULT '',
  `primary_physician` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(100) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance` varchar(255) NOT NULL DEFAULT '',
  `allergies` text DEFAULT NULL,
  `medications` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `contact1_name` varchar(255) NOT NULL DEFAULT '',
  `contact1_phone` varchar(100) NOT NULL DEFAULT '',
  `contact2_name` varchar(255) NOT NULL DEFAULT '',
  `contact2_phone` varchar(100) NOT NULL DEFAULT '',
  `contact3_name` varchar(255) NOT NULL DEFAULT '',
  `contact3_phone` varchar(100) NOT NULL DEFAULT '',
  `property_items` longtext DEFAULT NULL,
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `property_signature` varchar(255) NOT NULL DEFAULT '',
  `property_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `property_date` date DEFAULT NULL,
  `property_removed_date` date DEFAULT NULL,
  `property_removed_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `move_in_fee` decimal(10,2) DEFAULT 250.00,
  `other_charge` decimal(10,2) DEFAULT 0.00,
  `total_due` decimal(10,2) DEFAULT NULL,
  `scan_path` varchar(500) NOT NULL DEFAULT '',
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_residency_forms`
--

CREATE TABLE `oxford_residency_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) DEFAULT '',
  `letter_date` varchar(20) DEFAULT '',
  `house_name` varchar(255) DEFAULT '',
  `accepted_date` varchar(20) DEFAULT '',
  `address_line` varchar(255) DEFAULT '',
  `city_state_zip` varchar(255) DEFAULT '',
  `move_in_fee` decimal(10,2) DEFAULT 0.00,
  `weekly_rent` decimal(10,2) DEFAULT 0.00,
  `president_contact` varchar(255) DEFAULT '',
  `president_name` varchar(255) DEFAULT '',
  `president_signature` varchar(255) DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_shopping_lists`
--

CREATE TABLE `oxford_shopping_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `shopping_date` date DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Oxford House Shopping List',
  `items_json` longtext NOT NULL,
  `checked_json` longtext NOT NULL,
  `total_checked` int(11) NOT NULL DEFAULT 0,
  `total_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_items` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy_path` varchar(500) DEFAULT NULL,
  `uploaded_copy_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledgers`
--

CREATE TABLE `petty_cash_ledgers` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `ledger_date` date DEFAULT NULL,
  `beginning_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledger_rows`
--

CREATE TABLE `petty_cash_ledger_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `ledger_id` int(10) UNSIGNED NOT NULL,
  `row_index` int(11) NOT NULL,
  `txn_date` varchar(50) NOT NULL DEFAULT '',
  `products_purchased` varchar(255) NOT NULL DEFAULT '',
  `vendor` varchar(255) NOT NULL DEFAULT '',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reimbursement_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `safety_inspection_checklists`
--

CREATE TABLE `safety_inspection_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `inspection_date` date DEFAULT NULL,
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `checklist_json` longtext NOT NULL,
  `satisfactory_total` int(11) NOT NULL DEFAULT 0,
  `unsatisfactory_total` int(11) NOT NULL DEFAULT 0,
  `completed_total` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy` varchar(500) DEFAULT NULL,
  `original_upload_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_checklist_key` (`checklist_key`);

--
-- Indexes for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_meeting_date` (`meeting_date`);

--
-- Indexes for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_dates` (`week_start`,`week_end`);

--
-- Indexes for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_tour_date` (`tour_date`);

--
-- Indexes for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `house_name` (`house_name`);

--
-- Indexes for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_label` (`schedule_label`);

--
-- Indexes for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`);

--
-- Indexes for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_name_idx` (`tenant_name`),
  ADD KEY `updated_at_idx` (`updated_at`);

--
-- Indexes for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resident_name` (`resident_name`),
  ADD KEY `idx_sheet_date` (`sheet_date`);

--
-- Indexes for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sheet_id` (`sheet_id`),
  ADD KEY `idx_row_number` (`row_number`);

--
-- Indexes for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_form_date` (`form_date`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_contract_date` (`contract_date`);

--
-- Indexes for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date_completed` (`date_completed`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- Indexes for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_dates` (`house_name`,`date_from`,`date_to`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_date_from` (`date_from`),
  ADD KEY `idx_date_to` (`date_to`);

--
-- Indexes for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_start` (`week_start`),
  ADD KEY `idx_week_end` (`week_end`);

--
-- Indexes for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`),
  ADD KEY `idx_house_date` (`house_name`,`meeting_date`);

--
-- Indexes for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_member_date_house` (`member_name`,`contract_date`,`house_name`);

--
-- Indexes for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_member` (`house_name`,`member_name`);

--
-- Indexes for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_shopping_date` (`shopping_date`);

--
-- Indexes for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_date` (`house_name`,`ledger_date`);

--
-- Indexes for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_petty_cash_ledger_rows_ledger` (`ledger_id`);

--
-- Indexes for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inspection_date` (`inspection_date`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `oxford_central`
--
CREATE DATABASE IF NOT EXISTS `oxford_central` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `oxford_central`;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_master_activity`
--

CREATE TABLE `oxford_master_activity` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `house_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `page_name` varchar(255) NOT NULL DEFAULT '',
  `event_name` varchar(100) NOT NULL DEFAULT '',
  `details_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oxford_master_activity`
--

INSERT INTO `oxford_master_activity` (`id`, `house_id`, `user_id`, `page_name`, `event_name`, `details_json`, `created_at`) VALUES
(1, 1, 3, 'login.php', 'login_success', '{\"email\":\"mrjohndowe@outlook.com\"}', '2026-03-13 09:09:51'),
(3, 1, 3, 'index.php', 'page_opened', '{\"house_name\":\"Red Creek\",\"role\":\"central_admin\"}', '2026-03-13 09:09:51'),
(4, 1, 3, 'central_admin.php', 'page_opened', '{\"house_name\":\"Red Creek\",\"role\":\"central_admin\"}', '2026-03-13 09:09:53'),
(6, 1, 3, 'security.php', 'page_opened', '{\"house_name\":\"Red Creek\",\"role\":\"central_admin\"}', '2026-03-13 09:20:11'),
(7, 1, 3, 'central_admin.php', 'page_opened', '{\"house_name\":\"Red Creek\",\"role\":\"central_admin\"}', '2026-03-13 09:20:16'),
(8, 1, 3, 'logout.php', 'page_opened', '{\"house_name\":\"Red Creek\",\"role\":\"central_admin\"}', '2026-03-13 09:20:27'),
(9, 1, 3, 'logout.php', 'logout', NULL, '2026-03-13 09:20:27'),
(11, 1, 3, 'login.php', 'login_success', '{\"email\":\"mrjohndowe@outlook.com\"}', '2026-03-13 15:34:13'),
(13, 1, 3, 'index.php', 'page_opened', '{\"house_name\":\"Red Creek\",\"role\":\"central_admin\"}', '2026-03-13 15:34:13'),
(14, 1, 3, 'users_admin.php', 'page_opened', '{\"house_name\":\"Red Creek\",\"role\":\"central_admin\"}', '2026-03-13 15:34:15'),
(15, 1, 3, 'central_admin.php', 'page_opened', '{\"house_name\":\"Red Creek\",\"role\":\"central_admin\"}', '2026-03-13 15:34:34'),
(16, 1, 3, 'central_admin.php', 'post_request', '{\"post_keys\":[\"action\",\"house_name\",\"house_code\",\"database_name\",\"city\",\"state\",\"manager_name\",\"manager_email\"]}', '2026-03-13 15:37:46'),
(17, 2, 3, 'central_admin.php', 'house_created', '{\"house_name\":\"Eagles Nest\",\"database_name\":\"eagles_nest\",\"initial_login\":\"eaglesnest@oxfordhouse.us\",\"template_database\":\"secretary\",\"tables_created\":33}', '2026-03-13 15:38:30'),
(18, 1, 3, 'central_admin.php', 'post_request', '{\"post_keys\":[\"action\",\"house_name\",\"house_code\",\"database_name\",\"city\",\"state\",\"manager_name\",\"manager_email\"]}', '2026-03-13 15:38:30'),
(19, 3, 3, 'central_admin.php', 'house_created', '{\"house_name\":\"North Place\",\"database_name\":\"north_place\",\"initial_login\":\"northplace@oxfordhouse.us\",\"template_database\":\"secretary\",\"tables_created\":33}', '2026-03-13 15:40:18'),
(20, 1, 3, 'central_admin.php', 'post_request', '{\"post_keys\":[\"action\",\"house_name\",\"house_code\",\"database_name\",\"city\",\"state\",\"manager_name\",\"manager_email\"]}', '2026-03-13 15:40:18'),
(21, 4, 3, 'central_admin.php', 'house_created', '{\"house_name\":\"Northmoor\",\"database_name\":\"northmoor\",\"initial_login\":\"northmoor@oxfordhouse.us\",\"template_database\":\"secretary\",\"tables_created\":33}', '2026-03-13 15:41:42'),
(22, 1, 3, 'central_admin.php', 'post_request', '{\"post_keys\":[\"action\",\"house_name\",\"house_code\",\"database_name\",\"city\",\"state\",\"manager_name\",\"manager_email\"]}', '2026-03-13 15:41:42'),
(23, 5, 3, 'central_admin.php', 'house_created', '{\"house_name\":\"Norwich\",\"database_name\":\"norwich\",\"initial_login\":\"norwich@oxfordhouse.us\",\"template_database\":\"secretary\",\"tables_created\":33}', '2026-03-13 15:43:04'),
(24, 1, 3, 'central_admin.php', 'post_request', '{\"post_keys\":[\"action\",\"house_name\",\"house_code\",\"database_name\",\"city\",\"state\",\"manager_name\",\"manager_email\"]}', '2026-03-13 15:43:04'),
(25, 6, 3, 'central_admin.php', 'house_created', '{\"house_name\":\"Otro Dia\",\"database_name\":\"otro_dia\",\"initial_login\":\"otrodia@oxfordhouse.us\",\"template_database\":\"secretary\",\"tables_created\":33}', '2026-03-13 15:44:22'),
(26, 1, 3, 'central_admin.php', 'post_request', '{\"post_keys\":[\"action\",\"house_name\",\"house_code\",\"database_name\",\"city\",\"state\",\"manager_name\",\"manager_email\"]}', '2026-03-13 15:44:22'),
(27, 7, 3, 'central_admin.php', 'house_created', '{\"house_name\":\"Starlite\",\"database_name\":\"starlite\",\"initial_login\":\"starlite@oxfordhouse.us\",\"template_database\":\"secretary\",\"tables_created\":33}', '2026-03-13 15:45:22'),
(28, 1, 3, 'central_admin.php', 'post_request', '{\"post_keys\":[\"action\",\"house_name\",\"house_code\",\"database_name\",\"city\",\"state\",\"manager_name\",\"manager_email\"]}', '2026-03-13 15:45:22'),
(29, 8, 3, 'central_admin.php', 'house_created', '{\"house_name\":\"Sunset Park\",\"database_name\":\"sunset_park\",\"initial_login\":\"sunsetpark@oxfordhouse.us\",\"template_database\":\"secretary\",\"tables_created\":33}', '2026-03-13 15:46:21'),
(30, 1, 3, 'central_admin.php', 'post_request', '{\"post_keys\":[\"action\",\"house_name\",\"house_code\",\"database_name\",\"city\",\"state\",\"manager_name\",\"manager_email\"]}', '2026-03-13 15:46:21'),
(31, 1, 3, 'users_admin.php', 'page_opened', '{\"house_name\":\"Red Creek\",\"role\":\"central_admin\"}', '2026-03-13 15:47:16'),
(32, 1, 3, 'users_admin.php', 'post_request', '{\"post_keys\":[\"action\",\"user_id\",\"role\",\"status\",\"house_ids\"]}', '2026-03-13 16:07:25'),
(33, 1, 3, 'users_admin.php', 'post_request', '{\"post_keys\":[\"action\",\"user_id\",\"role\",\"status\",\"house_ids\"]}', '2026-03-13 16:07:37'),
(34, 1, 3, 'index.php', 'house_switched', '{\"house_id\":1}', '2026-03-13 16:09:11'),
(35, 1, 3, 'index.php', 'page_opened', '{\"house_name\":\"Red Creek\",\"role\":\"central_admin\"}', '2026-03-13 16:09:11'),
(36, 1, 3, 'logout.php', 'page_opened', '{\"house_name\":\"Red Creek\",\"role\":\"central_admin\"}', '2026-03-13 16:09:15'),
(37, 1, 3, 'logout.php', 'logout', NULL, '2026-03-13 16:09:15');

-- --------------------------------------------------------

--
-- Table structure for table `oxford_master_audit_log`
--

CREATE TABLE `oxford_master_audit_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `house_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action_name` varchar(100) NOT NULL DEFAULT '',
  `page_name` varchar(255) NOT NULL DEFAULT '',
  `target_table` varchar(150) NOT NULL DEFAULT '',
  `target_id` varchar(150) NOT NULL DEFAULT '',
  `ip_address` varchar(64) NOT NULL DEFAULT '',
  `details_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oxford_master_audit_log`
--

INSERT INTO `oxford_master_audit_log` (`id`, `house_id`, `user_id`, `action_name`, `page_name`, `target_table`, `target_id`, `ip_address`, `details_json`, `created_at`) VALUES
(1, 1, 3, 'login_success', 'login.php', '', '', '174.51.217.245', '{\"email\":\"mrjohndowe@outlook.com\"}', '2026-03-13 09:09:51'),
(3, 1, 3, 'logout', 'logout.php', '', '', '174.51.217.245', NULL, '2026-03-13 09:20:27'),
(4, 1, 3, 'login_success', 'login.php', '', '', '174.51.217.245', '{\"email\":\"mrjohndowe@outlook.com\"}', '2026-03-13 15:34:13'),
(6, 1, 3, 'post_request', 'central_admin.php', '', '', '174.51.217.245', '{\"post_keys\":[\"action\",\"house_name\",\"house_code\",\"database_name\",\"city\",\"state\",\"manager_name\",\"manager_email\"],\"query\":[]}', '2026-03-13 15:37:46'),
(7, 2, 3, 'house_created', 'central_admin.php', 'oxford_master_houses', '2', '174.51.217.245', '{\"house_name\":\"Eagles Nest\",\"house_code\":\"eagles-nest\",\"database_name\":\"eagles_nest\",\"template_database\":\"secretary\",\"tables_created\":[\"bedroom_essentials_checklists\",\"chapter_meeting_minutes\",\"ees_member_ledger\",\"house_ledger_records\",\"house_tour_forms\",\"house_visit_houses\",\"house_visit_reports\",\"house_visit_schedules\",\"housing_service_representative_reports\",\"hsc_meeting_minutes_json\",\"landlord_verification_forms\",\"medication_count_sheets\",\"medication_count_sheet_rows\",\"new_house_tour_forms\",\"nightly_kitchen_schedules\",\"oxford_chore_lists\",\"oxford_disruptive_contracts\",\"oxford_financial_audits\",\"oxford_house_financial_audits\",\"oxford_house_financial_reports\",\"oxford_house_ledger_forms\",\"oxford_house_meeting_minutes_json\",\"oxford_house_member_ledger\",\"oxford_house_minutes\",\"oxford_interview_minutes\",\"oxford_member_financial_contracts\",\"oxford_new_member_packets\",\"oxford_red_creek_member_packets\",\"oxford_residency_forms\",\"oxford_shopping_lists\",\"petty_cash_ledgers\",\"petty_cash_ledger_rows\",\"safety_inspection_checklists\"],\"initial_user_email\":\"eaglesnest@oxfordhouse.us\"}', '2026-03-13 15:38:30'),
(8, 1, 3, 'post_request', 'central_admin.php', '', '', '174.51.217.245', '{\"post_keys\":[\"action\",\"house_name\",\"house_code\",\"database_name\",\"city\",\"state\",\"manager_name\",\"manager_email\"],\"query\":[]}', '2026-03-13 15:38:30'),
(9, 3, 3, 'house_created', 'central_admin.php', 'oxford_master_houses', '3', '174.51.217.245', '{\"house_name\":\"North Place\",\"house_code\":\"north-place\",\"database_name\":\"north_place\",\"template_database\":\"secretary\",\"tables_created\":[\"bedroom_essentials_checklists\",\"chapter_meeting_minutes\",\"ees_member_ledger\",\"house_ledger_records\",\"house_tour_forms\",\"house_visit_houses\",\"house_visit_reports\",\"house_visit_schedules\",\"housing_service_representative_reports\",\"hsc_meeting_minutes_json\",\"landlord_verification_forms\",\"medication_count_sheets\",\"medication_count_sheet_rows\",\"new_house_tour_forms\",\"nightly_kitchen_schedules\",\"oxford_chore_lists\",\"oxford_disruptive_contracts\",\"oxford_financial_audits\",\"oxford_house_financial_audits\",\"oxford_house_financial_reports\",\"oxford_house_ledger_forms\",\"oxford_house_meeting_minutes_json\",\"oxford_house_member_ledger\",\"oxford_house_minutes\",\"oxford_interview_minutes\",\"oxford_member_financial_contracts\",\"oxford_new_member_packets\",\"oxford_red_creek_member_packets\",\"oxford_residency_forms\",\"oxford_shopping_lists\",\"petty_cash_ledgers\",\"petty_cash_ledger_rows\",\"safety_inspection_checklists\"],\"initial_user_email\":\"northplace@oxfordhouse.us\"}', '2026-03-13 15:40:18'),
(10, 1, 3, 'post_request', 'central_admin.php', '', '', '174.51.217.245', '{\"post_keys\":[\"action\",\"house_name\",\"house_code\",\"database_name\",\"city\",\"state\",\"manager_name\",\"manager_email\"],\"query\":[]}', '2026-03-13 15:40:18'),
(11, 4, 3, 'house_created', 'central_admin.php', 'oxford_master_houses', '4', '174.51.217.245', '{\"house_name\":\"Northmoor\",\"house_code\":\"northmoor\",\"database_name\":\"northmoor\",\"template_database\":\"secretary\",\"tables_created\":[\"bedroom_essentials_checklists\",\"chapter_meeting_minutes\",\"ees_member_ledger\",\"house_ledger_records\",\"house_tour_forms\",\"house_visit_houses\",\"house_visit_reports\",\"house_visit_schedules\",\"housing_service_representative_reports\",\"hsc_meeting_minutes_json\",\"landlord_verification_forms\",\"medication_count_sheets\",\"medication_count_sheet_rows\",\"new_house_tour_forms\",\"nightly_kitchen_schedules\",\"oxford_chore_lists\",\"oxford_disruptive_contracts\",\"oxford_financial_audits\",\"oxford_house_financial_audits\",\"oxford_house_financial_reports\",\"oxford_house_ledger_forms\",\"oxford_house_meeting_minutes_json\",\"oxford_house_member_ledger\",\"oxford_house_minutes\",\"oxford_interview_minutes\",\"oxford_member_financial_contracts\",\"oxford_new_member_packets\",\"oxford_red_creek_member_packets\",\"oxford_residency_forms\",\"oxford_shopping_lists\",\"petty_cash_ledgers\",\"petty_cash_ledger_rows\",\"safety_inspection_checklists\"],\"initial_user_email\":\"northmoor@oxfordhouse.us\"}', '2026-03-13 15:41:42'),
(12, 1, 3, 'post_request', 'central_admin.php', '', '', '174.51.217.245', '{\"post_keys\":[\"action\",\"house_name\",\"house_code\",\"database_name\",\"city\",\"state\",\"manager_name\",\"manager_email\"],\"query\":[]}', '2026-03-13 15:41:42'),
(13, 5, 3, 'house_created', 'central_admin.php', 'oxford_master_houses', '5', '174.51.217.245', '{\"house_name\":\"Norwich\",\"house_code\":\"norwich\",\"database_name\":\"norwich\",\"template_database\":\"secretary\",\"tables_created\":[\"bedroom_essentials_checklists\",\"chapter_meeting_minutes\",\"ees_member_ledger\",\"house_ledger_records\",\"house_tour_forms\",\"house_visit_houses\",\"house_visit_reports\",\"house_visit_schedules\",\"housing_service_representative_reports\",\"hsc_meeting_minutes_json\",\"landlord_verification_forms\",\"medication_count_sheets\",\"medication_count_sheet_rows\",\"new_house_tour_forms\",\"nightly_kitchen_schedules\",\"oxford_chore_lists\",\"oxford_disruptive_contracts\",\"oxford_financial_audits\",\"oxford_house_financial_audits\",\"oxford_house_financial_reports\",\"oxford_house_ledger_forms\",\"oxford_house_meeting_minutes_json\",\"oxford_house_member_ledger\",\"oxford_house_minutes\",\"oxford_interview_minutes\",\"oxford_member_financial_contracts\",\"oxford_new_member_packets\",\"oxford_red_creek_member_packets\",\"oxford_residency_forms\",\"oxford_shopping_lists\",\"petty_cash_ledgers\",\"petty_cash_ledger_rows\",\"safety_inspection_checklists\"],\"initial_user_email\":\"norwich@oxfordhouse.us\"}', '2026-03-13 15:43:04'),
(14, 1, 3, 'post_request', 'central_admin.php', '', '', '174.51.217.245', '{\"post_keys\":[\"action\",\"house_name\",\"house_code\",\"database_name\",\"city\",\"state\",\"manager_name\",\"manager_email\"],\"query\":[]}', '2026-03-13 15:43:04'),
(15, 6, 3, 'house_created', 'central_admin.php', 'oxford_master_houses', '6', '174.51.217.245', '{\"house_name\":\"Otro Dia\",\"house_code\":\"otro-dia\",\"database_name\":\"otro_dia\",\"template_database\":\"secretary\",\"tables_created\":[\"bedroom_essentials_checklists\",\"chapter_meeting_minutes\",\"ees_member_ledger\",\"house_ledger_records\",\"house_tour_forms\",\"house_visit_houses\",\"house_visit_reports\",\"house_visit_schedules\",\"housing_service_representative_reports\",\"hsc_meeting_minutes_json\",\"landlord_verification_forms\",\"medication_count_sheets\",\"medication_count_sheet_rows\",\"new_house_tour_forms\",\"nightly_kitchen_schedules\",\"oxford_chore_lists\",\"oxford_disruptive_contracts\",\"oxford_financial_audits\",\"oxford_house_financial_audits\",\"oxford_house_financial_reports\",\"oxford_house_ledger_forms\",\"oxford_house_meeting_minutes_json\",\"oxford_house_member_ledger\",\"oxford_house_minutes\",\"oxford_interview_minutes\",\"oxford_member_financial_contracts\",\"oxford_new_member_packets\",\"oxford_red_creek_member_packets\",\"oxford_residency_forms\",\"oxford_shopping_lists\",\"petty_cash_ledgers\",\"petty_cash_ledger_rows\",\"safety_inspection_checklists\"],\"initial_user_email\":\"otrodia@oxfordhouse.us\"}', '2026-03-13 15:44:22'),
(16, 1, 3, 'post_request', 'central_admin.php', '', '', '174.51.217.245', '{\"post_keys\":[\"action\",\"house_name\",\"house_code\",\"database_name\",\"city\",\"state\",\"manager_name\",\"manager_email\"],\"query\":[]}', '2026-03-13 15:44:22'),
(17, 7, 3, 'house_created', 'central_admin.php', 'oxford_master_houses', '7', '174.51.217.245', '{\"house_name\":\"Starlite\",\"house_code\":\"starlite\",\"database_name\":\"starlite\",\"template_database\":\"secretary\",\"tables_created\":[\"bedroom_essentials_checklists\",\"chapter_meeting_minutes\",\"ees_member_ledger\",\"house_ledger_records\",\"house_tour_forms\",\"house_visit_houses\",\"house_visit_reports\",\"house_visit_schedules\",\"housing_service_representative_reports\",\"hsc_meeting_minutes_json\",\"landlord_verification_forms\",\"medication_count_sheets\",\"medication_count_sheet_rows\",\"new_house_tour_forms\",\"nightly_kitchen_schedules\",\"oxford_chore_lists\",\"oxford_disruptive_contracts\",\"oxford_financial_audits\",\"oxford_house_financial_audits\",\"oxford_house_financial_reports\",\"oxford_house_ledger_forms\",\"oxford_house_meeting_minutes_json\",\"oxford_house_member_ledger\",\"oxford_house_minutes\",\"oxford_interview_minutes\",\"oxford_member_financial_contracts\",\"oxford_new_member_packets\",\"oxford_red_creek_member_packets\",\"oxford_residency_forms\",\"oxford_shopping_lists\",\"petty_cash_ledgers\",\"petty_cash_ledger_rows\",\"safety_inspection_checklists\"],\"initial_user_email\":\"starlite@oxfordhouse.us\"}', '2026-03-13 15:45:22'),
(18, 1, 3, 'post_request', 'central_admin.php', '', '', '174.51.217.245', '{\"post_keys\":[\"action\",\"house_name\",\"house_code\",\"database_name\",\"city\",\"state\",\"manager_name\",\"manager_email\"],\"query\":[]}', '2026-03-13 15:45:22'),
(19, 8, 3, 'house_created', 'central_admin.php', 'oxford_master_houses', '8', '174.51.217.245', '{\"house_name\":\"Sunset Park\",\"house_code\":\"sunset-park\",\"database_name\":\"sunset_park\",\"template_database\":\"secretary\",\"tables_created\":[\"bedroom_essentials_checklists\",\"chapter_meeting_minutes\",\"ees_member_ledger\",\"house_ledger_records\",\"house_tour_forms\",\"house_visit_houses\",\"house_visit_reports\",\"house_visit_schedules\",\"housing_service_representative_reports\",\"hsc_meeting_minutes_json\",\"landlord_verification_forms\",\"medication_count_sheets\",\"medication_count_sheet_rows\",\"new_house_tour_forms\",\"nightly_kitchen_schedules\",\"oxford_chore_lists\",\"oxford_disruptive_contracts\",\"oxford_financial_audits\",\"oxford_house_financial_audits\",\"oxford_house_financial_reports\",\"oxford_house_ledger_forms\",\"oxford_house_meeting_minutes_json\",\"oxford_house_member_ledger\",\"oxford_house_minutes\",\"oxford_interview_minutes\",\"oxford_member_financial_contracts\",\"oxford_new_member_packets\",\"oxford_red_creek_member_packets\",\"oxford_residency_forms\",\"oxford_shopping_lists\",\"petty_cash_ledgers\",\"petty_cash_ledger_rows\",\"safety_inspection_checklists\"],\"initial_user_email\":\"sunsetpark@oxfordhouse.us\"}', '2026-03-13 15:46:21'),
(20, 1, 3, 'post_request', 'central_admin.php', '', '', '174.51.217.245', '{\"post_keys\":[\"action\",\"house_name\",\"house_code\",\"database_name\",\"city\",\"state\",\"manager_name\",\"manager_email\"],\"query\":[]}', '2026-03-13 15:46:21'),
(21, NULL, 3, 'user_access_updated', 'users_admin.php', 'oxford_master_house_user_access', '3', '174.51.217.245', '{\"role\":\"central_admin\",\"status\":\"active\",\"house_ids\":[2,3,4,5,6,1,7,8]}', '2026-03-13 16:07:25'),
(22, 1, 3, 'post_request', 'users_admin.php', '', '', '174.51.217.245', '{\"post_keys\":[\"action\",\"user_id\",\"role\",\"status\",\"house_ids\"],\"query\":[]}', '2026-03-13 16:07:25'),
(23, NULL, 3, 'user_access_updated', 'users_admin.php', 'oxford_master_house_user_access', '5', '174.51.217.245', '{\"role\":\"central_admin\",\"status\":\"active\",\"house_ids\":[2,3,4,5,6,1,7,8]}', '2026-03-13 16:07:37'),
(24, 1, 3, 'post_request', 'users_admin.php', '', '', '174.51.217.245', '{\"post_keys\":[\"action\",\"user_id\",\"role\",\"status\",\"house_ids\"],\"query\":[]}', '2026-03-13 16:07:37'),
(25, 1, 3, 'house_switched', 'index.php', '', '', '174.51.217.245', '{\"house_id\":1}', '2026-03-13 16:09:11'),
(26, 1, 3, 'logout', 'logout.php', '', '', '174.51.217.245', NULL, '2026-03-13 16:09:15');

-- --------------------------------------------------------

--
-- Table structure for table `oxford_master_houses`
--

CREATE TABLE `oxford_master_houses` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(150) NOT NULL,
  `house_code` varchar(100) NOT NULL,
  `database_name` varchar(150) NOT NULL,
  `city` varchar(100) NOT NULL DEFAULT '',
  `state` varchar(50) NOT NULL DEFAULT '',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oxford_master_houses`
--

INSERT INTO `oxford_master_houses` (`id`, `house_name`, `house_code`, `database_name`, `city`, `state`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Red Creek', 'redcreek', 'secretary', '213 Fordham Cir', 'Pueblo, CO 81005', 1, '2026-03-12 07:54:50', '2026-03-12 07:58:17'),
(2, 'Eagles Nest', 'eagles-nest', 'eagles_nest', '9 Remington Ct', 'Pueblo, CO, 81008', 1, '2026-03-13 15:38:30', '2026-03-13 15:38:30'),
(3, 'North Place', 'north-place', 'north_place', '2116 North Pl', 'Pueblo, CO, 81008', 1, '2026-03-13 15:40:18', '2026-03-13 15:40:18'),
(4, 'Northmoor', 'northmoor', 'northmoor', '2100 Northmoor Terrace', 'Pueblo, CO, 81008', 1, '2026-03-13 15:41:42', '2026-03-13 15:41:42'),
(5, 'Norwich', 'norwich', 'norwich', '2719 Wills Blvd', 'Pueblo, CO, 81003', 1, '2026-03-13 15:43:04', '2026-03-13 15:43:04'),
(6, 'Otro Dia', 'otro-dia', 'otro_dia', '3506 Raccoon Ln', 'Pueblo, CO, 81005', 1, '2026-03-13 15:44:22', '2026-03-13 15:44:22'),
(7, 'Starlite', 'starlite', 'starlite', '2221 Cartier Dr', 'Pueblo, CO, 81005', 1, '2026-03-13 15:45:22', '2026-03-13 15:45:22'),
(8, 'Sunset Park', 'sunset-park', 'sunset_park', '39 Drake St', 'Pueblo, CO, 81005', 1, '2026-03-13 15:46:21', '2026-03-13 15:46:21');

-- --------------------------------------------------------

--
-- Table structure for table `oxford_master_house_settings`
--

CREATE TABLE `oxford_master_house_settings` (
  `house_id` int(10) UNSIGNED NOT NULL,
  `contract_stamp_password_hash` varchar(255) NOT NULL DEFAULT '',
  `updated_by_user_id` int(10) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_master_house_user_access`
--

CREATE TABLE `oxford_master_house_user_access` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oxford_master_house_user_access`
--

INSERT INTO `oxford_master_house_user_access` (`id`, `house_id`, `user_id`, `is_primary`, `created_at`) VALUES
(59, 1, 4, 1, '2026-03-12 09:13:26'),
(192, 1, 6, 1, '2026-03-12 18:49:43'),
(495, 2, 7, 1, '2026-03-13 15:38:30'),
(496, 2, 6, 1, '2026-03-13 15:40:18'),
(497, 3, 8, 1, '2026-03-13 15:40:18'),
(499, 4, 9, 1, '2026-03-13 15:41:42'),
(501, 5, 10, 1, '2026-03-13 15:43:04'),
(503, 6, 11, 1, '2026-03-13 15:44:22'),
(505, 7, 12, 1, '2026-03-13 15:45:22'),
(507, 8, 13, 1, '2026-03-13 15:46:21'),
(517, 2, 3, 1, '2026-03-13 16:07:25'),
(518, 3, 3, 0, '2026-03-13 16:07:25'),
(519, 4, 3, 0, '2026-03-13 16:07:25'),
(520, 5, 3, 0, '2026-03-13 16:07:25'),
(521, 6, 3, 0, '2026-03-13 16:07:25'),
(522, 1, 3, 0, '2026-03-13 16:07:25'),
(523, 7, 3, 0, '2026-03-13 16:07:25'),
(524, 8, 3, 0, '2026-03-13 16:07:25'),
(526, 2, 5, 1, '2026-03-13 16:07:37'),
(527, 3, 5, 0, '2026-03-13 16:07:37'),
(528, 4, 5, 0, '2026-03-13 16:07:37'),
(529, 5, 5, 0, '2026-03-13 16:07:37'),
(530, 6, 5, 0, '2026-03-13 16:07:37'),
(531, 1, 5, 0, '2026-03-13 16:07:37'),
(532, 7, 5, 0, '2026-03-13 16:07:37'),
(533, 8, 5, 0, '2026-03-13 16:07:37');

-- --------------------------------------------------------

--
-- Table structure for table `oxford_master_users`
--

CREATE TABLE `oxford_master_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('house_user','house_manager','regional_admin','central_admin','super_admin') NOT NULL DEFAULT 'house_user',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oxford_master_users`
--

INSERT INTO `oxford_master_users` (`id`, `full_name`, `email`, `password_hash`, `role`, `status`, `last_login_at`, `created_at`, `updated_at`) VALUES
(3, 'Jose Davila', 'mrjohndowe@outlook.com', '$2y$10$gs8ABF02otHF4I5ilwCP8e/0pMtVTyOnKiFaH/DUn4tu2otLiDGxu', 'central_admin', 'active', '2026-03-13 08:34:13', '2026-03-12 08:25:59', '2026-03-13 15:34:13'),
(4, 'Red Creek', 'redcreek@oxfordhouse.us', '$2y$10$dGkN9GUc7SR31kIbGUAmjerQ/k5fa6BxFUPdr.iogToLhUho06ubK', 'house_user', 'active', '2026-03-13 02:05:49', '2026-03-12 09:13:26', '2026-03-13 09:05:49'),
(5, 'Oxford Central Admin', 'admin@oxford.local', '$2y$10$hHED4/s6dP4kSEcY72j4FO3z4CTuuqaqXmNWrnVhOtcORE3gYctta', 'central_admin', 'active', '2026-03-12 20:05:20', '2026-03-12 18:41:39', '2026-03-13 03:05:20'),
(6, 'Default House Manager', 'houseuser@default-house.local', '$2y$10$.1euB1OkeFtyGwXTE.qa6ehn1m39XXSd2MKItxB7i/TZ8YdPqdCGK', 'house_manager', 'inactive', NULL, '2026-03-12 18:41:39', '2026-03-12 18:49:35'),
(7, 'Eagles Nest', 'eaglesnest@oxfordhouse.us', '$2y$10$ORMVDYVwBKZjYHTzpQWg7uAd19Lm2rikxie24c6vYoOa9ThNmd5gq', 'house_manager', 'active', NULL, '2026-03-13 15:38:30', '2026-03-13 15:38:30'),
(8, 'North Place', 'northplace@oxfordhouse.us', '$2y$10$GwN6FPsxtuBsbaof1zPote1he.wLoHllDkJFUgCu8hMRXL/040VUm', 'house_manager', 'active', NULL, '2026-03-13 15:40:18', '2026-03-13 15:40:18'),
(9, 'Northmoor', 'northmoor@oxfordhouse.us', '$2y$10$15x6441y4IwwdXXYK5vygOwIcSVaY7ifKojxO5reBW.wQCR9wpqNK', 'house_manager', 'active', NULL, '2026-03-13 15:41:42', '2026-03-13 15:41:42'),
(10, 'Norwich', 'norwich@oxfordhouse.us', '$2y$10$4tFTMoMgjgPbLj3oJGaT.ejKlit8nF20x2AMQ1Qv0raBIZH3GxNb.', 'house_manager', 'active', NULL, '2026-03-13 15:43:04', '2026-03-13 15:43:04'),
(11, 'Otro Dia', 'otrodia@oxfordhouse.us', '$2y$10$/vCTHydUN4tD8ragBBdicOtGheBMT83hTcgTv3R1aDWIw6rv5/GYa', 'house_manager', 'active', NULL, '2026-03-13 15:44:22', '2026-03-13 15:44:22'),
(12, 'Starlite', 'starlite@oxfordhouse.us', '$2y$10$q6TJotUP1KlftljE2HetLurT5cE2aPv78jZwMG0VP/1TAfSp18Rsy', 'house_manager', 'active', NULL, '2026-03-13 15:45:22', '2026-03-13 15:45:22'),
(13, 'Sunset Park', 'sunsetpark@oxfordhouse.us', '$2y$10$5IcTteJHYl08uWkvGudzme4X26MfQG0uT/leRWFGtTByp0Nhgcqcy', 'house_manager', 'active', NULL, '2026-03-13 15:46:21', '2026-03-13 15:46:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `oxford_master_activity`
--
ALTER TABLE `oxford_master_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_created` (`house_id`,`created_at`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`);

--
-- Indexes for table `oxford_master_audit_log`
--
ALTER TABLE `oxford_master_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_house` (`house_id`,`created_at`),
  ADD KEY `idx_audit_user` (`user_id`,`created_at`);

--
-- Indexes for table `oxford_master_houses`
--
ALTER TABLE `oxford_master_houses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_code` (`house_code`),
  ADD UNIQUE KEY `uniq_database_name` (`database_name`);

--
-- Indexes for table `oxford_master_house_settings`
--
ALTER TABLE `oxford_master_house_settings`
  ADD PRIMARY KEY (`house_id`),
  ADD KEY `fk_oxford_master_house_settings_user` (`updated_by_user_id`);

--
-- Indexes for table `oxford_master_house_user_access`
--
ALTER TABLE `oxford_master_house_user_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_user` (`house_id`,`user_id`),
  ADD KEY `fk_oxford_house_access_user` (`user_id`);

--
-- Indexes for table `oxford_master_users`
--
ALTER TABLE `oxford_master_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `oxford_master_activity`
--
ALTER TABLE `oxford_master_activity`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `oxford_master_audit_log`
--
ALTER TABLE `oxford_master_audit_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `oxford_master_houses`
--
ALTER TABLE `oxford_master_houses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `oxford_master_house_user_access`
--
ALTER TABLE `oxford_master_house_user_access`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=537;

--
-- AUTO_INCREMENT for table `oxford_master_users`
--
ALTER TABLE `oxford_master_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `oxford_master_activity`
--
ALTER TABLE `oxford_master_activity`
  ADD CONSTRAINT `fk_oxford_master_activity_house` FOREIGN KEY (`house_id`) REFERENCES `oxford_master_houses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_oxford_master_activity_user` FOREIGN KEY (`user_id`) REFERENCES `oxford_master_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `oxford_master_audit_log`
--
ALTER TABLE `oxford_master_audit_log`
  ADD CONSTRAINT `fk_oxford_master_audit_house` FOREIGN KEY (`house_id`) REFERENCES `oxford_master_houses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_oxford_master_audit_user` FOREIGN KEY (`user_id`) REFERENCES `oxford_master_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `oxford_master_house_settings`
--
ALTER TABLE `oxford_master_house_settings`
  ADD CONSTRAINT `fk_oxford_master_house_settings_house` FOREIGN KEY (`house_id`) REFERENCES `oxford_master_houses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_oxford_master_house_settings_user` FOREIGN KEY (`updated_by_user_id`) REFERENCES `oxford_master_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `oxford_master_house_user_access`
--
ALTER TABLE `oxford_master_house_user_access`
  ADD CONSTRAINT `fk_oxford_house_access_house` FOREIGN KEY (`house_id`) REFERENCES `oxford_master_houses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_oxford_house_access_user` FOREIGN KEY (`user_id`) REFERENCES `oxford_master_users` (`id`) ON DELETE CASCADE;
--
-- Database: `oxford_tables`
--
CREATE DATABASE IF NOT EXISTS `oxford_tables` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `oxford_tables`;

-- --------------------------------------------------------

--
-- Table structure for table `bedroom_essentials_checklists`
--

CREATE TABLE `bedroom_essentials_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `checklist_key` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Bedroom Essentials – Checklist',
  `items_json` longtext NOT NULL,
  `dark_mode` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chapter_meeting_minutes`
--

CREATE TABLE `chapter_meeting_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `meeting_date` varchar(50) NOT NULL,
  `form_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ees_member_ledger`
--

CREATE TABLE `ees_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(2) NOT NULL DEFAULT '',
  `move_in_day` varchar(2) NOT NULL DEFAULT '',
  `move_in_year` varchar(4) NOT NULL DEFAULT '',
  `ledger_rows` longtext DEFAULT NULL,
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_ledger_records`
--

CREATE TABLE `house_ledger_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `notes` mediumtext DEFAULT NULL,
  `rows_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_tour_forms`
--

CREATE TABLE `house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `tour_date` date DEFAULT NULL,
  `tour_time` varchar(20) NOT NULL DEFAULT '',
  `smoking_area` varchar(10) NOT NULL DEFAULT '',
  `notes` text DEFAULT NULL,
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `signature` varchar(255) NOT NULL DEFAULT '',
  `items_json` longtext DEFAULT NULL,
  `section_totals_json` text DEFAULT NULL,
  `grand_total` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_houses`
--

CREATE TABLE `house_visit_houses` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(150) NOT NULL,
  `meeting_day` varchar(20) NOT NULL DEFAULT '',
  `meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_reports`
--

CREATE TABLE `house_visit_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(100) NOT NULL DEFAULT '',
  `president` varchar(255) NOT NULL DEFAULT '',
  `secretary` varchar(255) NOT NULL DEFAULT '',
  `treasurer` varchar(255) NOT NULL DEFAULT '',
  `comptroller` varchar(255) NOT NULL DEFAULT '',
  `coordinator` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep` varchar(255) NOT NULL DEFAULT '',
  `overall_appearance` varchar(10) NOT NULL DEFAULT '',
  `overall_appearance_comments` text DEFAULT NULL,
  `members_behind_ees` decimal(10,2) DEFAULT NULL,
  `total_amount_owed` decimal(12,2) DEFAULT NULL,
  `rent_paid_monthly` decimal(12,2) DEFAULT NULL,
  `ees_paid_weekly` decimal(12,2) DEFAULT NULL,
  `utilities_monthly` decimal(12,2) DEFAULT NULL,
  `house_business_meeting` varchar(10) NOT NULL DEFAULT '',
  `house_business_comments` text DEFAULT NULL,
  `rating_reading_traditions` varchar(10) NOT NULL DEFAULT '',
  `rating_reading_minutes` varchar(10) NOT NULL DEFAULT '',
  `rating_treasurer_report` varchar(10) NOT NULL DEFAULT '',
  `rating_comptroller_report` varchar(10) NOT NULL DEFAULT '',
  `rating_coordinator_report` varchar(10) NOT NULL DEFAULT '',
  `rating_maintains_guidelines` varchar(10) NOT NULL DEFAULT '',
  `rating_handling_business` varchar(10) NOT NULL DEFAULT '',
  `rating_organization_order` varchar(10) NOT NULL DEFAULT '',
  `financial_comments` text DEFAULT NULL,
  `first_visit_date` varchar(50) NOT NULL DEFAULT '',
  `narcan_present` varchar(10) NOT NULL DEFAULT '',
  `narcan_trained` varchar(10) NOT NULL DEFAULT '',
  `follow_up_visit_dates` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep_signature` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_schedules`
--

CREATE TABLE `house_visit_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `schedule_label` varchar(150) NOT NULL,
  `month_label` varchar(50) NOT NULL,
  `month_index` int(11) NOT NULL DEFAULT 1,
  `visiting_house` varchar(150) NOT NULL,
  `host_house` varchar(150) NOT NULL,
  `host_meeting_day` varchar(20) NOT NULL DEFAULT '',
  `host_meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `housing_service_representative_reports`
--

CREATE TABLE `housing_service_representative_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `week_from` varchar(100) NOT NULL DEFAULT '',
  `week_to` varchar(100) NOT NULL DEFAULT '',
  `house_visit` text DEFAULT NULL,
  `audit_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `audit_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `next_chapter_meeting` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `next_state_meeting` text DEFAULT NULL,
  `number_of_applications` text DEFAULT NULL,
  `number_contacted_plan` longtext DEFAULT NULL,
  `new_members` longtext DEFAULT NULL,
  `interviews_setup` longtext DEFAULT NULL,
  `chapter_news` longtext DEFAULT NULL,
  `chapter_meeting_recap` longtext DEFAULT NULL,
  `upcoming_unity` longtext DEFAULT NULL,
  `upcoming_presentations` longtext DEFAULT NULL,
  `hsr_name` varchar(255) NOT NULL DEFAULT '',
  `report_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hsc_meeting_minutes_json`
--

CREATE TABLE `hsc_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `landlord_verification_forms`
--

CREATE TABLE `landlord_verification_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_name` varchar(255) NOT NULL DEFAULT '',
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `landlord_manager_name` varchar(255) NOT NULL DEFAULT '',
  `manager_street` varchar(255) NOT NULL DEFAULT '',
  `manager_city` varchar(150) NOT NULL DEFAULT '',
  `manager_state` varchar(50) NOT NULL DEFAULT '',
  `manager_zip` varchar(25) NOT NULL DEFAULT '',
  `manager_county` varchar(150) NOT NULL DEFAULT '',
  `manager_phone` varchar(50) NOT NULL DEFAULT '',
  `manager_email` varchar(255) NOT NULL DEFAULT '',
  `rental_street` varchar(255) NOT NULL DEFAULT '',
  `rental_apt_lot` varchar(100) NOT NULL DEFAULT '',
  `rental_city` varchar(150) NOT NULL DEFAULT '',
  `rental_state` varchar(50) NOT NULL DEFAULT '',
  `rental_zip` varchar(25) NOT NULL DEFAULT '',
  `rental_county` varchar(150) NOT NULL DEFAULT '',
  `bedrooms` varchar(20) NOT NULL DEFAULT '',
  `lease_start_date` date DEFAULT NULL,
  `lease_end_date` date DEFAULT NULL,
  `monthly_rent_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `next_payment_due_date` date DEFAULT NULL,
  `last_payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `last_payment_date` date DEFAULT NULL,
  `tenant_in_arrears` tinyint(1) NOT NULL DEFAULT 0,
  `amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `arrears_period_from` date DEFAULT NULL,
  `arrears_period_to` date DEFAULT NULL,
  `receiving_other_assistance` tinyint(1) NOT NULL DEFAULT 0,
  `other_assistance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_assistance_period` varchar(100) NOT NULL DEFAULT '',
  `payment_method` varchar(20) NOT NULL DEFAULT 'check',
  `check_payable_to` varchar(255) NOT NULL DEFAULT '',
  `send_to_landlord_address` tinyint(1) NOT NULL DEFAULT 1,
  `alternative_address` text DEFAULT NULL,
  `cert_name` varchar(255) NOT NULL DEFAULT '',
  `cert_title` varchar(255) NOT NULL DEFAULT '',
  `cert_signature` varchar(255) NOT NULL DEFAULT '',
  `cert_date` date DEFAULT NULL,
  `calc_balance_remaining` decimal(10,2) NOT NULL DEFAULT 0.00,
  `calc_total_assistance_gap` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheets`
--

CREATE TABLE `medication_count_sheets` (
  `id` int(10) UNSIGNED NOT NULL,
  `resident_name` varchar(255) NOT NULL,
  `sheet_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheet_rows`
--

CREATE TABLE `medication_count_sheet_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `sheet_id` int(10) UNSIGNED NOT NULL,
  `row_number` int(11) NOT NULL,
  `entry_date` varchar(50) DEFAULT NULL,
  `medication_name` varchar(255) DEFAULT NULL,
  `dosage` varchar(255) DEFAULT NULL,
  `frequency` varchar(255) DEFAULT NULL,
  `previous_count` varchar(100) DEFAULT NULL,
  `current_count` varchar(100) DEFAULT NULL,
  `member_initials` varchar(50) DEFAULT NULL,
  `witness_initials` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `new_house_tour_forms`
--

CREATE TABLE `new_house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `form_date` varchar(50) NOT NULL DEFAULT '',
  `form_time` varchar(50) NOT NULL DEFAULT '',
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `inspector_signature` varchar(255) NOT NULL DEFAULT '',
  `smoking_area_score` varchar(5) NOT NULL DEFAULT '',
  `smoking_area_comment` text DEFAULT NULL,
  `notes_text` text DEFAULT NULL,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_average` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payload_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nightly_kitchen_schedules`
--

CREATE TABLE `nightly_kitchen_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `trash_to_curb_on` varchar(255) NOT NULL DEFAULT '',
  `schedule_data` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_chore_lists`
--

CREATE TABLE `oxford_chore_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT 'CHORE LIST',
  `week1_start` varchar(20) NOT NULL DEFAULT '',
  `week2_start` varchar(20) NOT NULL DEFAULT '',
  `week3_start` varchar(20) NOT NULL DEFAULT '',
  `week4_start` varchar(20) NOT NULL DEFAULT '',
  `week5_start` varchar(20) NOT NULL DEFAULT '',
  `week6_start` varchar(20) NOT NULL DEFAULT '',
  `week7_start` varchar(20) NOT NULL DEFAULT '',
  `week8_start` varchar(20) NOT NULL DEFAULT '',
  `form_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_disruptive_contracts`
--

CREATE TABLE `oxford_disruptive_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_subject` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(50) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `behavior_1` text DEFAULT NULL,
  `behavior_2` text DEFAULT NULL,
  `behavior_3` text DEFAULT NULL,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgment_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(50) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_original_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_stored_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_mime` varchar(100) NOT NULL DEFAULT '',
  `uploaded_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_financial_audits`
--

CREATE TABLE `oxford_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `date_completed` date DEFAULT NULL,
  `bank_statement_ending_date` date DEFAULT NULL,
  `bank_statement_ending_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_past_due_bills` decimal(12,2) NOT NULL DEFAULT 0.00,
  `savings_account_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_outstanding_ees` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deposits_json` longtext DEFAULT NULL,
  `checks_json` longtext DEFAULT NULL,
  `treasurer_signature` varchar(255) DEFAULT NULL,
  `comptroller_signature` varchar(255) DEFAULT NULL,
  `president_signature` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_audits`
--

CREATE TABLE `oxford_house_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `completed_month` varchar(10) NOT NULL DEFAULT '',
  `completed_day` varchar(10) NOT NULL DEFAULT '',
  `completed_year` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_month` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_day` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_year` varchar(10) NOT NULL DEFAULT '',
  `past_due_bills` varchar(50) NOT NULL DEFAULT '',
  `savings_balance` varchar(50) NOT NULL DEFAULT '',
  `outstanding_ees` varchar(50) NOT NULL DEFAULT '',
  `bank_statement_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `deposits_total` varchar(50) NOT NULL DEFAULT '',
  `checks_total` varchar(50) NOT NULL DEFAULT '',
  `balance_after_audit` varchar(50) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_signature` varchar(255) NOT NULL DEFAULT '',
  `comptroller_signature` varchar(255) NOT NULL DEFAULT '',
  `president_signature` varchar(255) NOT NULL DEFAULT '',
  `checks_rows` longtext DEFAULT NULL,
  `deposits_rows` longtext DEFAULT NULL,
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `scan_stored_name` varchar(255) NOT NULL DEFAULT '',
  `scan_path` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_reports`
--

CREATE TABLE `oxford_house_financial_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `report_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_ledger_forms`
--

CREATE TABLE `oxford_house_ledger_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `rows_json` longtext DEFAULT NULL,
  `totals_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_meeting_minutes_json`
--

CREATE TABLE `oxford_house_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_member_ledger`
--

CREATE TABLE `oxford_house_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(10) NOT NULL DEFAULT '',
  `move_in_day` varchar(10) NOT NULL DEFAULT '',
  `move_in_year` varchar(10) NOT NULL DEFAULT '',
  `row_1` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_1`)),
  `row_2` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_2`)),
  `row_3` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_3`)),
  `row_4` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_4`)),
  `row_5` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_5`)),
  `row_6` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_6`)),
  `row_7` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_7`)),
  `row_8` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_8`)),
  `row_9` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_9`)),
  `row_10` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_10`)),
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date_paid` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_minutes`
--

CREATE TABLE `oxford_house_minutes` (
  `id` int(11) NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `meeting_date` varchar(50) DEFAULT NULL,
  `start_time` varchar(50) DEFAULT NULL,
  `tradition_number` varchar(50) DEFAULT NULL,
  `meeting_type_regular` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_emergency` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_interview` tinyint(1) NOT NULL DEFAULT 0,
  `minutes_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `amend_yes` tinyint(1) NOT NULL DEFAULT 0,
  `amend_no` tinyint(1) NOT NULL DEFAULT 0,
  `checking_0` varchar(50) DEFAULT NULL,
  `checking_1` varchar(50) DEFAULT NULL,
  `checking_2` varchar(50) DEFAULT NULL,
  `checking_3` varchar(50) DEFAULT NULL,
  `savings_0` varchar(50) DEFAULT NULL,
  `savings_1` varchar(50) DEFAULT NULL,
  `savings_2` varchar(50) DEFAULT NULL,
  `savings_3` varchar(50) DEFAULT NULL,
  `savings_4` varchar(50) DEFAULT NULL,
  `petty_0` varchar(50) DEFAULT NULL,
  `petty_1` varchar(50) DEFAULT NULL,
  `petty_2` varchar(50) DEFAULT NULL,
  `petty_3` varchar(50) DEFAULT NULL,
  `petty_receipts_yes` tinyint(1) NOT NULL DEFAULT 0,
  `petty_receipts_no` tinyint(1) NOT NULL DEFAULT 0,
  `treasurer_comments` text DEFAULT NULL,
  `treasurer_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `comptroller_comments` text DEFAULT NULL,
  `comptroller_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `ees_plan` longtext DEFAULT NULL,
  `coordinator_report` text DEFAULT NULL,
  `coordinator_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `housing_services_report` text DEFAULT NULL,
  `hsr_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_n` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_n` tinyint(1) NOT NULL DEFAULT 0,
  `unfinished_business` text DEFAULT NULL,
  `vacancy_updated_y` tinyint(1) NOT NULL DEFAULT 0,
  `vacancy_updated_n` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_y` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_n` tinyint(1) NOT NULL DEFAULT 0,
  `new_business` longtext DEFAULT NULL,
  `adjourn_hour` varchar(10) DEFAULT NULL,
  `adjourn_min` varchar(10) DEFAULT NULL,
  `secretary_name` varchar(255) DEFAULT NULL,
  `secretary_signature` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `roll_name_1` text DEFAULT NULL,
  `roll_y_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_2` text DEFAULT NULL,
  `roll_y_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_3` text DEFAULT NULL,
  `roll_y_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_4` text DEFAULT NULL,
  `roll_y_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_5` text DEFAULT NULL,
  `roll_y_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_6` text DEFAULT NULL,
  `roll_y_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_7` text DEFAULT NULL,
  `roll_y_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_8` text DEFAULT NULL,
  `roll_y_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_9` text DEFAULT NULL,
  `roll_y_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_10` text DEFAULT NULL,
  `roll_y_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_11` text DEFAULT NULL,
  `roll_y_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_12` text DEFAULT NULL,
  `roll_y_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_13` text DEFAULT NULL,
  `roll_y_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_14` text DEFAULT NULL,
  `roll_y_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_15` text DEFAULT NULL,
  `roll_y_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_16` text DEFAULT NULL,
  `roll_y_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_17` text DEFAULT NULL,
  `roll_y_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_18` text DEFAULT NULL,
  `roll_y_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_19` text DEFAULT NULL,
  `roll_y_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_20` text DEFAULT NULL,
  `roll_y_20` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_20` tinyint(1) NOT NULL DEFAULT 0,
  `comp_name_1` text DEFAULT NULL,
  `comp_bal_1` text DEFAULT NULL,
  `comp_name_2` text DEFAULT NULL,
  `comp_bal_2` text DEFAULT NULL,
  `comp_name_3` text DEFAULT NULL,
  `comp_bal_3` text DEFAULT NULL,
  `comp_name_4` text DEFAULT NULL,
  `comp_bal_4` text DEFAULT NULL,
  `comp_name_5` text DEFAULT NULL,
  `comp_bal_5` text DEFAULT NULL,
  `comp_name_6` text DEFAULT NULL,
  `comp_bal_6` text DEFAULT NULL,
  `comp_name_7` text DEFAULT NULL,
  `comp_bal_7` text DEFAULT NULL,
  `comp_name_8` text DEFAULT NULL,
  `comp_bal_8` text DEFAULT NULL,
  `comp_name_9` text DEFAULT NULL,
  `comp_bal_9` text DEFAULT NULL,
  `comp_name_10` text DEFAULT NULL,
  `comp_bal_10` text DEFAULT NULL,
  `comp_name_11` text DEFAULT NULL,
  `comp_bal_11` text DEFAULT NULL,
  `comp_name_12` text DEFAULT NULL,
  `comp_bal_12` text DEFAULT NULL,
  `comp_name_13` text DEFAULT NULL,
  `comp_bal_13` text DEFAULT NULL,
  `comp_name_14` text DEFAULT NULL,
  `comp_bal_14` text DEFAULT NULL,
  `comp_name_15` text DEFAULT NULL,
  `comp_bal_15` text DEFAULT NULL,
  `comp_name_16` text DEFAULT NULL,
  `comp_bal_16` text DEFAULT NULL,
  `comp_name_17` text DEFAULT NULL,
  `comp_bal_17` text DEFAULT NULL,
  `comp_name_18` text DEFAULT NULL,
  `comp_bal_18` text DEFAULT NULL,
  `comp_name_19` text DEFAULT NULL,
  `comp_bal_19` text DEFAULT NULL,
  `comp_name_20` text DEFAULT NULL,
  `comp_bal_20` text DEFAULT NULL,
  `secretary_signed_date` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_interview_minutes`
--

CREATE TABLE `oxford_interview_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `interview_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `interview_date` date DEFAULT NULL,
  `interviewer_name` varchar(255) NOT NULL DEFAULT '',
  `contact_phone` varchar(100) NOT NULL DEFAULT '',
  `outcome_status` varchar(100) NOT NULL DEFAULT '',
  `vote_percent` varchar(50) NOT NULL DEFAULT '',
  `move_in_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `intro_notes` mediumtext DEFAULT NULL,
  `interviewer_do_notes` mediumtext DEFAULT NULL,
  `interviewer_dont_notes` mediumtext DEFAULT NULL,
  `closing_notes` mediumtext DEFAULT NULL,
  `roll_name_1` varchar(255) NOT NULL DEFAULT '',
  `roll_present_1` varchar(20) NOT NULL DEFAULT '',
  `roll_name_2` varchar(255) NOT NULL DEFAULT '',
  `roll_present_2` varchar(20) NOT NULL DEFAULT '',
  `roll_name_3` varchar(255) NOT NULL DEFAULT '',
  `roll_present_3` varchar(20) NOT NULL DEFAULT '',
  `roll_name_4` varchar(255) NOT NULL DEFAULT '',
  `roll_present_4` varchar(20) NOT NULL DEFAULT '',
  `roll_name_5` varchar(255) NOT NULL DEFAULT '',
  `roll_present_5` varchar(20) NOT NULL DEFAULT '',
  `roll_name_6` varchar(255) NOT NULL DEFAULT '',
  `roll_present_6` varchar(20) NOT NULL DEFAULT '',
  `roll_name_7` varchar(255) NOT NULL DEFAULT '',
  `roll_present_7` varchar(20) NOT NULL DEFAULT '',
  `roll_name_8` varchar(255) NOT NULL DEFAULT '',
  `roll_present_8` varchar(20) NOT NULL DEFAULT '',
  `roll_name_9` varchar(255) NOT NULL DEFAULT '',
  `roll_present_9` varchar(20) NOT NULL DEFAULT '',
  `roll_name_10` varchar(255) NOT NULL DEFAULT '',
  `roll_present_10` varchar(20) NOT NULL DEFAULT '',
  `roll_name_11` varchar(255) NOT NULL DEFAULT '',
  `roll_present_11` varchar(20) NOT NULL DEFAULT '',
  `roll_name_12` varchar(255) NOT NULL DEFAULT '',
  `roll_present_12` varchar(20) NOT NULL DEFAULT '',
  `q1` mediumtext DEFAULT NULL,
  `q2` mediumtext DEFAULT NULL,
  `q3` mediumtext DEFAULT NULL,
  `q4` mediumtext DEFAULT NULL,
  `q5` mediumtext DEFAULT NULL,
  `q6` mediumtext DEFAULT NULL,
  `q7` mediumtext DEFAULT NULL,
  `q8` mediumtext DEFAULT NULL,
  `q9` mediumtext DEFAULT NULL,
  `q10` mediumtext DEFAULT NULL,
  `q11` mediumtext DEFAULT NULL,
  `q12` mediumtext DEFAULT NULL,
  `q13` mediumtext DEFAULT NULL,
  `q14` mediumtext DEFAULT NULL,
  `q15` mediumtext DEFAULT NULL,
  `q16` mediumtext DEFAULT NULL,
  `q17` mediumtext DEFAULT NULL,
  `q18` mediumtext DEFAULT NULL,
  `q19` mediumtext DEFAULT NULL,
  `q20` mediumtext DEFAULT NULL,
  `q21` mediumtext DEFAULT NULL,
  `q22` mediumtext DEFAULT NULL,
  `q23` mediumtext DEFAULT NULL,
  `q24` mediumtext DEFAULT NULL,
  `q25` mediumtext DEFAULT NULL,
  `q26` mediumtext DEFAULT NULL,
  `q27` mediumtext DEFAULT NULL,
  `q28` mediumtext DEFAULT NULL,
  `q29` mediumtext DEFAULT NULL,
  `q30` mediumtext DEFAULT NULL,
  `q31` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_member_financial_contracts`
--

CREATE TABLE `oxford_member_financial_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(100) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `total_amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgement_name` varchar(255) NOT NULL DEFAULT '',
  `signature_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(100) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `scanned_contract` varchar(255) NOT NULL DEFAULT '',
  `contract_stamp` varchar(50) NOT NULL DEFAULT '',
  `contract_stamp_at` datetime DEFAULT NULL,
  `contract_stamp_by_ip` varchar(64) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_new_member_packets`
--

CREATE TABLE `oxford_new_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `packet_date` date DEFAULT NULL,
  `check_membership_application` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_manual` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_guidelines` tinyint(1) NOT NULL DEFAULT 0,
  `check_membership_agreement` tinyint(1) NOT NULL DEFAULT 0,
  `check_plan_for_recovery` tinyint(1) NOT NULL DEFAULT 0,
  `check_relapse_contingency` tinyint(1) NOT NULL DEFAULT 0,
  `check_medical_release` tinyint(1) NOT NULL DEFAULT 0,
  `check_property_list` tinyint(1) NOT NULL DEFAULT 0,
  `member_initials_1` varchar(20) NOT NULL DEFAULT '',
  `president_initials_1` varchar(20) NOT NULL DEFAULT '',
  `member_initials_2` varchar(20) NOT NULL DEFAULT '',
  `president_initials_2` varchar(20) NOT NULL DEFAULT '',
  `member_initials_3` varchar(20) NOT NULL DEFAULT '',
  `president_initials_3` varchar(20) NOT NULL DEFAULT '',
  `member_initials_4` varchar(20) NOT NULL DEFAULT '',
  `president_initials_4` varchar(20) NOT NULL DEFAULT '',
  `member_initials_5` varchar(20) NOT NULL DEFAULT '',
  `president_initials_5` varchar(20) NOT NULL DEFAULT '',
  `member_initials_6` varchar(20) NOT NULL DEFAULT '',
  `president_initials_6` varchar(20) NOT NULL DEFAULT '',
  `member_initials_7` varchar(20) NOT NULL DEFAULT '',
  `president_initials_7` varchar(20) NOT NULL DEFAULT '',
  `member_initials_8` varchar(20) NOT NULL DEFAULT '',
  `president_initials_8` varchar(20) NOT NULL DEFAULT '',
  `member_signature_1` varchar(255) NOT NULL DEFAULT '',
  `member_signature_date_1` date DEFAULT NULL,
  `president_signature_1` varchar(255) NOT NULL DEFAULT '',
  `president_signature_date_1` date DEFAULT NULL,
  `membership_agreement_text` longtext DEFAULT NULL,
  `agreement_member_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_date` date DEFAULT NULL,
  `agreement_president_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_date` date DEFAULT NULL,
  `plan_name` varchar(255) NOT NULL DEFAULT '',
  `plan_text` longtext DEFAULT NULL,
  `aftercare_program` longtext DEFAULT NULL,
  `has_sponsor` varchar(10) NOT NULL DEFAULT '',
  `sponsor_by_date` date DEFAULT NULL,
  `meetings_per_week` varchar(50) NOT NULL DEFAULT '',
  `meeting_types` varchar(255) NOT NULL DEFAULT '',
  `plan_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_signature_date` date DEFAULT NULL,
  `plan_president_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_president_date` date DEFAULT NULL,
  `relapse_name` varchar(255) NOT NULL DEFAULT '',
  `relapse_family` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_friend` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_detox` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other_text` varchar(255) NOT NULL DEFAULT '',
  `relapse_details` longtext DEFAULT NULL,
  `notify_rows_json` longtext DEFAULT NULL,
  `pickup_rows_json` longtext DEFAULT NULL,
  `relapse_member_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_member_date` date DEFAULT NULL,
  `relapse_president_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_president_date` date DEFAULT NULL,
  `relapse_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_witness_date` date DEFAULT NULL,
  `medical_name` varchar(255) NOT NULL DEFAULT '',
  `physician_name` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(50) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance_info` varchar(255) NOT NULL DEFAULT '',
  `allergies` longtext DEFAULT NULL,
  `medications` longtext DEFAULT NULL,
  `medical_history` longtext DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `blood_type` varchar(20) NOT NULL DEFAULT '',
  `medical_contacts_json` longtext DEFAULT NULL,
  `medical_signature` varchar(255) NOT NULL DEFAULT '',
  `medical_date` date DEFAULT NULL,
  `property_name` varchar(255) NOT NULL DEFAULT '',
  `property_move_in_date` date DEFAULT NULL,
  `property_rows_json` longtext DEFAULT NULL,
  `uploaded_copy_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_path` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_mime` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_red_creek_member_packets`
--

CREATE TABLE `oxford_red_creek_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `signature_date` date DEFAULT NULL,
  `refund_move_in_date` date DEFAULT NULL,
  `new_member_signature` varchar(255) NOT NULL DEFAULT '',
  `new_member_signature_date` date DEFAULT NULL,
  `president_hsr_signature` varchar(255) NOT NULL DEFAULT '',
  `president_hsr_signature_date` date DEFAULT NULL,
  `expectations_signature` varchar(255) NOT NULL DEFAULT '',
  `expectations_signature_date` date DEFAULT NULL,
  `medication_signature` varchar(255) NOT NULL DEFAULT '',
  `medication_signature_date` date DEFAULT NULL,
  `emergency_name` varchar(255) NOT NULL DEFAULT '',
  `emergency_age` varchar(50) NOT NULL DEFAULT '',
  `emergency_dob` varchar(50) NOT NULL DEFAULT '',
  `blood_type` varchar(50) NOT NULL DEFAULT '',
  `primary_physician` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(100) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance` varchar(255) NOT NULL DEFAULT '',
  `allergies` text DEFAULT NULL,
  `medications` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `contact1_name` varchar(255) NOT NULL DEFAULT '',
  `contact1_phone` varchar(100) NOT NULL DEFAULT '',
  `contact2_name` varchar(255) NOT NULL DEFAULT '',
  `contact2_phone` varchar(100) NOT NULL DEFAULT '',
  `contact3_name` varchar(255) NOT NULL DEFAULT '',
  `contact3_phone` varchar(100) NOT NULL DEFAULT '',
  `property_items` longtext DEFAULT NULL,
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `property_signature` varchar(255) NOT NULL DEFAULT '',
  `property_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `property_date` date DEFAULT NULL,
  `property_removed_date` date DEFAULT NULL,
  `property_removed_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `move_in_fee` decimal(10,2) DEFAULT 250.00,
  `other_charge` decimal(10,2) DEFAULT 0.00,
  `total_due` decimal(10,2) DEFAULT NULL,
  `scan_path` varchar(500) NOT NULL DEFAULT '',
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_residency_forms`
--

CREATE TABLE `oxford_residency_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) DEFAULT '',
  `letter_date` varchar(20) DEFAULT '',
  `house_name` varchar(255) DEFAULT '',
  `accepted_date` varchar(20) DEFAULT '',
  `address_line` varchar(255) DEFAULT '',
  `city_state_zip` varchar(255) DEFAULT '',
  `move_in_fee` decimal(10,2) DEFAULT 0.00,
  `weekly_rent` decimal(10,2) DEFAULT 0.00,
  `president_contact` varchar(255) DEFAULT '',
  `president_name` varchar(255) DEFAULT '',
  `president_signature` varchar(255) DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_shopping_lists`
--

CREATE TABLE `oxford_shopping_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `shopping_date` date DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Oxford House Shopping List',
  `items_json` longtext NOT NULL,
  `checked_json` longtext NOT NULL,
  `total_checked` int(11) NOT NULL DEFAULT 0,
  `total_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_items` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy_path` varchar(500) DEFAULT NULL,
  `uploaded_copy_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledgers`
--

CREATE TABLE `petty_cash_ledgers` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `ledger_date` date DEFAULT NULL,
  `beginning_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledger_rows`
--

CREATE TABLE `petty_cash_ledger_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `ledger_id` int(10) UNSIGNED NOT NULL,
  `row_index` int(11) NOT NULL,
  `txn_date` varchar(50) NOT NULL DEFAULT '',
  `products_purchased` varchar(255) NOT NULL DEFAULT '',
  `vendor` varchar(255) NOT NULL DEFAULT '',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reimbursement_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `safety_inspection_checklists`
--

CREATE TABLE `safety_inspection_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `inspection_date` date DEFAULT NULL,
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `checklist_json` longtext NOT NULL,
  `satisfactory_total` int(11) NOT NULL DEFAULT 0,
  `unsatisfactory_total` int(11) NOT NULL DEFAULT 0,
  `completed_total` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy` varchar(500) DEFAULT NULL,
  `original_upload_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_checklist_key` (`checklist_key`);

--
-- Indexes for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_meeting_date` (`meeting_date`);

--
-- Indexes for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_dates` (`week_start`,`week_end`);

--
-- Indexes for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_tour_date` (`tour_date`);

--
-- Indexes for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `house_name` (`house_name`);

--
-- Indexes for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_label` (`schedule_label`);

--
-- Indexes for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`);

--
-- Indexes for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_name_idx` (`tenant_name`),
  ADD KEY `updated_at_idx` (`updated_at`);

--
-- Indexes for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resident_name` (`resident_name`),
  ADD KEY `idx_sheet_date` (`sheet_date`);

--
-- Indexes for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sheet_id` (`sheet_id`),
  ADD KEY `idx_row_number` (`row_number`);

--
-- Indexes for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_form_date` (`form_date`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_contract_date` (`contract_date`);

--
-- Indexes for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date_completed` (`date_completed`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- Indexes for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_dates` (`house_name`,`date_from`,`date_to`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_date_from` (`date_from`),
  ADD KEY `idx_date_to` (`date_to`);

--
-- Indexes for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_start` (`week_start`),
  ADD KEY `idx_week_end` (`week_end`);

--
-- Indexes for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`),
  ADD KEY `idx_house_date` (`house_name`,`meeting_date`);

--
-- Indexes for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_member_date_house` (`member_name`,`contract_date`,`house_name`);

--
-- Indexes for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_member` (`house_name`,`member_name`);

--
-- Indexes for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_shopping_date` (`shopping_date`);

--
-- Indexes for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_date` (`house_name`,`ledger_date`);

--
-- Indexes for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_petty_cash_ledger_rows_ledger` (`ledger_id`);

--
-- Indexes for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inspection_date` (`inspection_date`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  ADD CONSTRAINT `fk_medication_sheet` FOREIGN KEY (`sheet_id`) REFERENCES `medication_count_sheets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  ADD CONSTRAINT `fk_petty_cash_ledger_rows_ledger` FOREIGN KEY (`ledger_id`) REFERENCES `petty_cash_ledgers` (`id`) ON DELETE CASCADE;
--
-- Database: `secretary`
--
CREATE DATABASE IF NOT EXISTS `secretary` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `secretary`;

-- --------------------------------------------------------

--
-- Table structure for table `bedroom_essentials_checklists`
--

CREATE TABLE `bedroom_essentials_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `checklist_key` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Bedroom Essentials – Checklist',
  `items_json` longtext NOT NULL,
  `dark_mode` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bedroom_essentials_checklists`
--

INSERT INTO `bedroom_essentials_checklists` (`id`, `checklist_key`, `title`, `items_json`, `dark_mode`, `updated_at`) VALUES
(1, 'default_bedroom_essentials', 'Bedroom Essentials – Checklist', '[{\"section\":\"Bedding & Sleep Setup\",\"name\":\"Mattress\",\"price\":\"0.00\",\"original_price\":\"120.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Bedding & Sleep Setup\",\"name\":\"Mattress protector\",\"price\":\"0.00\",\"original_price\":\"10.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Bedding & Sleep Setup\",\"name\":\"Sheet set – 2 sets\",\"price\":\"0.00\",\"original_price\":\"20.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Bedding & Sleep Setup\",\"name\":\"Comforter or duvet\",\"price\":\"0.00\",\"original_price\":\"25.00\",\"status\":\"acquired\",\"is_custom\":0},{\"section\":\"Bedding & Sleep Setup\",\"name\":\"Pillows – 2–4\",\"price\":\"0.00\",\"original_price\":\"10.00\",\"status\":\"acquired\",\"is_custom\":0},{\"section\":\"Bedding & Sleep Setup\",\"name\":\"Throw blanket\",\"price\":\"0.00\",\"original_price\":\"10.00\",\"status\":\"acquired\",\"is_custom\":0},{\"section\":\"Laundry & Clothing Organization\",\"name\":\"Laundry basket\\/hamper\",\"price\":\"10.00\",\"original_price\":\"8.00\",\"status\":\"\",\"is_custom\":0},{\"section\":\"Laundry & Clothing Organization\",\"name\":\"Hangers – 20–30 pack\",\"price\":\"0.00\",\"original_price\":\"5.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Laundry & Clothing Organization\",\"name\":\"Small dresser or storage cubes\",\"price\":\"0.00\",\"original_price\":\"20.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Laundry & Clothing Organization\",\"name\":\"Shoe rack or under-bed storage\",\"price\":\"0.00\",\"original_price\":\"10.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Laundry & Clothing Organization\",\"name\":\"Lint roller\",\"price\":\"0.00\",\"original_price\":\"3.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Room Cleaning & Hygiene\",\"name\":\"Small trash can\",\"price\":\"0.00\",\"original_price\":\"5.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Room Cleaning & Hygiene\",\"name\":\"Trash bags\",\"price\":\"0.00\",\"original_price\":\"3.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Room Cleaning & Hygiene\",\"name\":\"Disinfecting wipes\",\"price\":\"0.00\",\"original_price\":\"3.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Room Cleaning & Hygiene\",\"name\":\"Air freshener or odor absorber\",\"price\":\"0.00\",\"original_price\":\"3.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Room Cleaning & Hygiene\",\"name\":\"Mini vacuum or broom\\/dustpan\",\"price\":\"0.00\",\"original_price\":\"10.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Room Cleaning & Hygiene\",\"name\":\"Laundry detergent\",\"price\":\"0.00\",\"original_price\":\"5.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Room Cleaning & Hygiene\",\"name\":\"Dryer sheets\",\"price\":\"0.00\",\"original_price\":\"3.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Room Cleaning & Hygiene\",\"name\":\"Tissues\",\"price\":\"0.00\",\"original_price\":\"2.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Privacy & Comfort\",\"name\":\"Blackout curtains\",\"price\":\"25.00\",\"original_price\":\"15.00\",\"status\":\"\",\"is_custom\":0},{\"section\":\"Privacy & Comfort\",\"name\":\"Fan or small air purifier\",\"price\":\"0.00\",\"original_price\":\"15.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Privacy & Comfort\",\"name\":\"Bedside lamp\",\"price\":\"39.94\",\"original_price\":\"8.00\",\"status\":\"\",\"is_custom\":0},{\"section\":\"Privacy & Comfort\",\"name\":\"Extension cord + surge protector\",\"price\":\"0.00\",\"original_price\":\"8.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Privacy & Comfort\",\"name\":\"Long phone charger\",\"price\":\"0.00\",\"original_price\":\"6.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Privacy & Comfort\",\"name\":\"Earplugs or white noise option\",\"price\":\"0.00\",\"original_price\":\"3.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Storage & Organization\",\"name\":\"Nightstand or bedside shelf\",\"price\":\"0.00\",\"original_price\":\"15.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Storage & Organization\",\"name\":\"Desk + chair (optional)\",\"price\":\"0.00\",\"original_price\":\"40.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Storage & Organization\",\"name\":\"Drawer organizers\",\"price\":\"19.98\",\"original_price\":\"5.00\",\"status\":\"\",\"is_custom\":0},{\"section\":\"Storage & Organization\",\"name\":\"Under-bed bins\",\"price\":\"8.00\",\"original_price\":\"8.00\",\"status\":\"\",\"is_custom\":0},{\"section\":\"Storage & Organization\",\"name\":\"Wall hooks or over-door hooks\",\"price\":\"0.00\",\"original_price\":\"5.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Personal Kitchen Basics\",\"name\":\"Microwave-safe bowl\\/plate\",\"price\":\"0.00\",\"original_price\":\"5.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Personal Kitchen Basics\",\"name\":\"Fork\\/spoon\\/knife set\",\"price\":\"0.00\",\"original_price\":\"3.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Personal Kitchen Basics\",\"name\":\"Water bottle\",\"price\":\"0.00\",\"original_price\":\"5.00\",\"status\":\"acquired\",\"is_custom\":0},{\"section\":\"Personal Kitchen Basics\",\"name\":\"Coffee maker or kettle (optional)\",\"price\":\"0.00\",\"original_price\":\"15.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Personal Kitchen Basics\",\"name\":\"Personal snacks\",\"price\":\"50.00\",\"original_price\":\"10.00\",\"status\":\"\",\"is_custom\":0},{\"section\":\"Personal Kitchen Basics\",\"name\":\"Mini fridge (optional)\",\"price\":\"188.00\",\"original_price\":\"60.00\",\"status\":\"\",\"is_custom\":0},{\"section\":\"Grooming & Personal Care\",\"name\":\"Shower caddy\",\"price\":\"7.98\",\"original_price\":\"5.00\",\"status\":\"\",\"is_custom\":0},{\"section\":\"Grooming & Personal Care\",\"name\":\"Towels – 2 bath, 2 hand\",\"price\":\"13.97\",\"original_price\":\"10.00\",\"status\":\"\",\"is_custom\":0},{\"section\":\"Grooming & Personal Care\",\"name\":\"Toiletries\",\"price\":\"0.00\",\"original_price\":\"10.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Grooming & Personal Care\",\"name\":\"Razor + shaving cream\",\"price\":\"0.00\",\"original_price\":\"5.00\",\"status\":\"acquired\",\"is_custom\":0},{\"section\":\"Grooming & Personal Care\",\"name\":\"Nail clippers\",\"price\":\"7.19\",\"original_price\":\"2.00\",\"status\":\"\",\"is_custom\":0},{\"section\":\"Grooming & Personal Care\",\"name\":\"Basic first aid items\",\"price\":\"0.00\",\"original_price\":\"5.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Optional but Nice-to-Have\",\"name\":\"Small TV or monitor\",\"price\":\"148.00\",\"original_price\":\"60.00\",\"status\":\"\",\"is_custom\":0},{\"section\":\"Optional but Nice-to-Have\",\"name\":\"LED lights or simple décor\",\"price\":\"0.00\",\"original_price\":\"8.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Optional but Nice-to-Have\",\"name\":\"Bookshelf\",\"price\":\"0.00\",\"original_price\":\"20.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Optional but Nice-to-Have\",\"name\":\"Scented candles (if allowed)\",\"price\":\"5.00\",\"original_price\":\"5.00\",\"status\":\"\",\"is_custom\":0},{\"section\":\"Optional but Nice-to-Have\",\"name\":\"Bluetooth speaker\",\"price\":\"0.00\",\"original_price\":\"15.00\",\"status\":\"bought\",\"is_custom\":0},{\"section\":\"Optional but Nice-to-Have\",\"name\":\"Notebook or planner\",\"price\":\"0.00\",\"original_price\":\"3.00\",\"status\":\"notneeded\",\"is_custom\":0},{\"section\":\"Optional but Nice-to-Have\",\"name\":\"Office Chair Mat\",\"price\":\"139.99\",\"original_price\":\"139.99\",\"status\":\"\",\"is_custom\":1},{\"section\":\"Optional but Nice-to-Have\",\"name\":\"Gaming Chair\",\"price\":\"126.00\",\"original_price\":\"126.00\",\"status\":\"\",\"is_custom\":1},{\"section\":\"Optional but Nice-to-Have\",\"name\":\"Gaming Desk\",\"price\":\"114.00\",\"original_price\":\"114.00\",\"status\":\"\",\"is_custom\":1}]', 0, '2026-03-11 07:19:10');

-- --------------------------------------------------------

--
-- Table structure for table `chapter_meeting_minutes`
--

CREATE TABLE `chapter_meeting_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `meeting_date` varchar(50) NOT NULL,
  `form_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chapter_meeting_minutes`
--

INSERT INTO `chapter_meeting_minutes` (`id`, `meeting_date`, `form_data`, `created_at`, `updated_at`) VALUES
(3, '2/21/2026', '{\"meeting_date\":\"2/21/2026\",\"start_time\":\"18:22\",\"officer_0\":\"Patrick\",\"officer_1\":\"Waylon\",\"officer_2\":\"Jose\",\"officer_3\":\"Ricky\",\"officer_4\":\"Moriah and Krystal\",\"officer_5\":\"Josey\",\"officer_6\":\"Frank and nataley\",\"officer_7\":\"Sean\",\"house_0\":\"Eagles Nest\",\"house_status_0\":\"Kaylee\",\"house_1\":\"North Place\",\"house_status_1\":\"Joel\",\"house_2\":\"Northmoor\",\"house_status_2\":\"Jeff\",\"house_3\":\"Norwich\",\"house_status_3\":\"Robin / Cat\",\"house_4\":\"Otra Dia\",\"house_status_4\":\"David\",\"house_5\":\"Red Creek\",\"house_status_5\":\"Alex\",\"house_6\":\"Starlite\",\"house_status_6\":\"Whalen\",\"house_7\":\"Sunset Park\",\"house_status_7\":\"Hannah\",\"absent\":\"\",\"guests\":\"John from alumni\",\"principles_read\":\"Y\",\"corrections\":\"Working on Starlight Lease Lowering, \",\"minutes_line\":\"\",\"minutes_accept_checked\":\"1\",\"minutes_accept\":\"\",\"checking_beginning\":\"17262.77\",\"checking_deposit\":\"2690.00\",\"checking_spent\":\"2670.66\",\"checking_current\":\"17282.11\",\"savings_beginning\":\"\",\"savings_deposits\":\"\",\"savings_withdrawals\":\"\",\"savings_current\":\"0.00\",\"money_collected\":\"\",\"money_collected_suffix\":\"\",\"treasurer_comments\":\"\",\"treasurer_accept_checked\":\"1\",\"treasurer_accept\":\"\",\"chairperson_report\":\"Patrick reported recently moving out of Oxford and is working to become more involved again\",\"chairperson_report_accept_checked\":\"1\",\"chairperson_report_accept\":\"\",\"vicechair_report\":\"Waylon reported positive progress across houses, increased participation in men\'s houses, he noted the need to discuss women\'s house in new business\",\"vicechair_report_accept_checked\":\"1\",\"vicechair_report_accept\":\"\",\"housing_report\":\"Moriah attended the statewide HSE meeting in Denver, Discussion focused on defining the purpose and structure of the statewide HSE body. Moriah and Cystal completed another Crossroads presentation in IRT. The session was informal and aligned with Tradition 8, emphasizing presence and genuine interaction over promotion. Interest in expanding into TRT and creating casual engagement opportunites. Encouragement to host community events and include IRT/TRT participants. Fundraising updates are forthcoming. HSE attendance is voluntary; houses may be excused by submitting their house form ahead of time. No fines will be issued for missing the meeting, only if failure to submit the required forms. Chapter HSR meetings continue to show strong attendance. Moriah attended the Arizona state Convention and noted significantly higher turnout; houses are encouraged to send members to the upcoming Colorado convention. Chapter currently has 21 open beds (12 women, 9 men); membership growth remains a priority.\",\"housing_report_accept_checked\":\"1\",\"housing_report_accept\":\"\",\"outreach_report\":\"Kat shared that Arizona grew 100 houses in 3 years due to strong re-entry support and dedicated fundraising. She suggested exploring similar fundraising locally to help new re-entry members to cover their $250 move-in fee or first month of EES, reducing financial strain on houses. Emphasized the importance of Chore Coordinator position. She referenced a recent incident in North Denver where a member relapsed, passed away, and left severe indications of prelapse behaviors, She stressed that relapse behaviors appear before the relapse itself, and regular room checks can save lives. Noted that the individual had been making amends days before the incident, underscoring the need for houses to stay attentive and engaged. Reported visiting Canyon City and being invited to Skyline\'s Beacon Program, a re-entry initiative focused on preventing individuals from being released into homelessness. They are actively seeing stronger connections with Oxford House.\",\"outreach_report_accept_checked\":\"1\",\"outreach_report_accept\":\"\",\"reentry_report\":\"Josey received 6 recent re-entry applications, with 1 accepted into Red Creek. 2 additional applicants are beginning their transition process. He is also encouraging halfway-house residents to apply, with 1 interview scheduled\",\"reentry_report_accept_checked\":\"1\",\"reentry_report_accept\":\"\",\"fundraising_report\":\"The chapter approved donating the remaining fundraiser balance to the Alumni Association. Randy then offered expanded support for re-entry members, including help with IDs, birth certs, resumes, rides, phones and connecting them to recovery groups that also include unity activites. He presented individual recongnition awards and introduced new laser-etched plaques he plans to continue using. Patrick Whalen: Beacon of Hope Award || A house award was given to a home recognized for resilience and commitment through challenges. Northmoor: House Beacon of Hope Award MMSP to pay $85 to the Oxford House State association meeting for Amber\",\"fundraising_report_accept_checked\":\"1\",\"fundraising_report_accept\":\"\",\"alumni_report\":\"The chapter approved donating the remaining fundraiser balance to the Alumni Association. Randy then offered expanded support for re-entry members, including help with IDs, birth certs, resumes, rides, phones and connecting them to recovery groups that also include unity activites. He presented individual recongnition awards and introduced new laser-etched plaques he plans to continue using. Patrick Whalen: Beacon of Hope Award || A house award was given to a home recognized for resilience and commitment through challenges. Northmoor: House Beacon of Hope Award MMSP to pay $85 to the Oxford House State association meeting for Amber\",\"alumni_accept_checked\":\"1\",\"alumni_accept\":\"\",\"old_business\":\"The committe reviewed current operational needs, noting that several houses - Starlite, Sunset, Northmoor, and North Place are missing required bed frames and must be addressed promptly. A structured schedule for supply distribution and cleaning responsibilities is needed to ensure consistency and accountability across all houses. Discussion also covered overall house needs and demand, along with ongoing lease negotiations for Starlite, Sunset and Eagles Nest. \\r\\nMMSP will not be opening a new house at this time.\\r\\n\\r\\nBed Frames Needs to be addressed ** need schedule for houses to provided supplies and responsibility of cleaning. *** talk about house needs/demand. ** negotiation about leases happening for Starlite, sunset eagle nests. ***starlite,sunset, north moor, north place missing bed frames*** \\r\\nMMSP Not to open a new house \",\"old_business_accept_checked\":\"1\",\"old_business_accept\":\"\",\"new_business\":\"MMSP for nine members to attend the State Convention, with approving sponsorship for four attendees: Jennifer (Eagle Nest), Nataly (Eagle Nest), Randy (Alumni), and Jessica (Eagle Nest).\\r\\nRegistrations include Moriah (Red Creek), Pat (Eagle Nest), and Cat (Starlight).\\r\\nMMSP that alumni will retain raffle proceeds, and will maintain a ledger for all alumni-related funds.\\r\\nMMSP Fundraising Committee will manage the 50/50 fundraiser\\r\\nMMSP the purchase of a supply box with a spending limit of $200 \\r\\nMMSP portion of supplies donated to the church\\r\\nMMSP a one-time $250 donation for church facility use\\r\\nMMSP a recurring monthly donation of $200 to the church for facility use\\r\\nAn invitation will be extended to Gabe for the next chapter meeting\\r\\nMMSP $528 to send two members from Chapter 4 to the State Convention\\r\\nMMSP Patrick Whalen to Attend aswell\\r\\nMMSP voted to end Melissa’s open-ended contract on March 23rd\\r\\nMMSP voted to end Nicole\'s open-ended contract on April 23rd due to absence from this meeting\\r\\nFines were issued as follows: $50 to Starlite and $50 to Eagle Nest for missing house visits, and $50 to Sunset Park for missing their House Summary Report\\r\\nMMSP also approved reimbursing Moriah $54.60 for attending the State HSC Meeting.\",\"new_business_accept_checked\":\"1\",\"new_business_accept\":\"\",\"adjourn_meeting_checked\":\"1\",\"adjourn_meeting\":\"\",\"adjourn_time\":\"20:55\",\"secretary_signature\":\"Jose D.\"}', '2026-03-09 05:40:24', '2026-03-09 05:52:53');

-- --------------------------------------------------------

--
-- Table structure for table `ees_member_ledger`
--

CREATE TABLE `ees_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(2) NOT NULL DEFAULT '',
  `move_in_day` varchar(2) NOT NULL DEFAULT '',
  `move_in_year` varchar(4) NOT NULL DEFAULT '',
  `ledger_rows` longtext DEFAULT NULL,
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_ledger_records`
--

CREATE TABLE `house_ledger_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `notes` mediumtext DEFAULT NULL,
  `rows_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `house_ledger_records`
--

INSERT INTO `house_ledger_records` (`id`, `house_name`, `week_start`, `week_end`, `ees_amount`, `notes`, `rows_json`, `created_at`, `updated_at`) VALUES
(2, 'Red Creek', '2026-03-01', '2026-03-07', 195.00, '', '[{\"member_name\":\"Moriah\",\"previous_balance\":\"275.73\",\"ees_due\":\"195.00\",\"fines_other\":\"0.00\",\"approved_receipts\":\"0.00\",\"total\":\"470.73\",\"amount_paid\":\"569.00\",\"ending_balance\":\"-98.27\"},{\"member_name\":\"Frank\",\"previous_balance\":\"1084.44\",\"ees_due\":\"195.00\",\"fines_other\":\"0.00\",\"approved_receipts\":\"0.00\",\"total\":\"1279.44\",\"amount_paid\":\"470.00\",\"ending_balance\":\"809.44\"},{\"member_name\":\"Tristan\",\"previous_balance\":\"1326.28\",\"ees_due\":\"195.00\",\"fines_other\":\"0.00\",\"approved_receipts\":\"0.00\",\"total\":\"1521.28\",\"amount_paid\":\"700.00\",\"ending_balance\":\"821.28\"},{\"member_name\":\"Troy\",\"previous_balance\":\"137.33\",\"ees_due\":\"195.00\",\"fines_other\":\"0.00\",\"approved_receipts\":\"0.00\",\"total\":\"332.33\",\"amount_paid\":\"0.00\",\"ending_balance\":\"332.33\"},{\"member_name\":\"Charles\",\"previous_balance\":\"1170.00\",\"ees_due\":\"195.00\",\"fines_other\":\"0.00\",\"approved_receipts\":\"0.00\",\"total\":\"1365.00\",\"amount_paid\":\"470.00\",\"ending_balance\":\"895.00\"},{\"member_name\":\"Jose\",\"previous_balance\":\"1665.00\",\"ees_due\":\"195.00\",\"fines_other\":\"0.00\",\"approved_receipts\":\"0.00\",\"total\":\"1860.00\",\"amount_paid\":\"200.00\",\"ending_balance\":\"1660.00\"},{\"member_name\":\"Neal\",\"previous_balance\":\"890.00\",\"ees_due\":\"195.00\",\"fines_other\":\"0.00\",\"approved_receipts\":\"0.00\",\"total\":\"1085.00\",\"amount_paid\":\"350.00\",\"ending_balance\":\"735.00\"},{\"member_name\":\"Alex\",\"previous_balance\":\"1065.00\",\"ees_due\":\"195.00\",\"fines_other\":\"0.00\",\"approved_receipts\":\"0.00\",\"total\":\"1260.00\",\"amount_paid\":\"0.00\",\"ending_balance\":\"1260.00\"},{\"member_name\":\"Todd\",\"previous_balance\":\"530.75\",\"ees_due\":\"195.00\",\"fines_other\":\"0.00\",\"approved_receipts\":\"0.00\",\"total\":\"725.75\",\"amount_paid\":\"175.00\",\"ending_balance\":\"550.75\"},{\"member_name\":\"Arturo\",\"previous_balance\":\"680.00\",\"ees_due\":\"195.00\",\"fines_other\":\"0.00\",\"approved_receipts\":\"0.00\",\"total\":\"875.00\",\"amount_paid\":\"250.00\",\"ending_balance\":\"625.00\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"195.00\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"195.00\",\"amount_paid\":\"\",\"ending_balance\":\"195.00\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"195.00\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"195.00\",\"amount_paid\":\"\",\"ending_balance\":\"195.00\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"195.00\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"195.00\",\"amount_paid\":\"\",\"ending_balance\":\"195.00\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"195.00\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"195.00\",\"amount_paid\":\"\",\"ending_balance\":\"195.00\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"195.00\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"195.00\",\"amount_paid\":\"\",\"ending_balance\":\"195.00\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"195.00\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"195.00\",\"amount_paid\":\"\",\"ending_balance\":\"195.00\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"195.00\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"195.00\",\"amount_paid\":\"\",\"ending_balance\":\"195.00\"}]', '2026-03-10 00:39:14', '2026-03-10 00:45:01');

-- --------------------------------------------------------

--
-- Table structure for table `house_tour_forms`
--

CREATE TABLE `house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `tour_date` date DEFAULT NULL,
  `tour_time` varchar(20) NOT NULL DEFAULT '',
  `smoking_area` varchar(10) NOT NULL DEFAULT '',
  `notes` text DEFAULT NULL,
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `signature` varchar(255) NOT NULL DEFAULT '',
  `items_json` longtext DEFAULT NULL,
  `section_totals_json` text DEFAULT NULL,
  `grand_total` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `house_tour_forms`
--

INSERT INTO `house_tour_forms` (`id`, `house_name`, `tour_date`, `tour_time`, `smoking_area`, `notes`, `inspected_by`, `inspector_name`, `signature`, `items_json`, `section_totals_json`, `grand_total`, `created_at`, `updated_at`) VALUES
(1, 'red cr', NULL, '22:21', '', '', '', '5', '5', '{\"exterior__yard\":{\"section\":\"exterior\",\"label\":\"Yard\",\"score\":\"5\",\"comment\":\"4\"},\"exterior__parking\":{\"section\":\"exterior\",\"label\":\"Parking\",\"score\":\"5\",\"comment\":\"5\"},\"exterior__paint_gutters\":{\"section\":\"exterior\",\"label\":\"Paint\\/Gutters\",\"score\":\"5\",\"comment\":\"5\"},\"exterior__porches\":{\"section\":\"exterior\",\"label\":\"Porches\",\"score\":\"5\",\"comment\":\"5\"},\"exterior__garage\":{\"section\":\"exterior\",\"label\":\"Garage\",\"score\":\"5\",\"comment\":\"5\"},\"exterior__overall\":{\"section\":\"exterior\",\"label\":\"Overall\",\"score\":\"5\",\"comment\":\"5\"},\"common_area__living_room_s\":{\"section\":\"common_area\",\"label\":\"Living Room(s)\",\"score\":\"4\",\"comment\":\"4\"},\"common_area__kitchen_s\":{\"section\":\"common_area\",\"label\":\"Kitchen(s)\",\"score\":\"4\",\"comment\":\"4\"},\"common_area__dining_room\":{\"section\":\"common_area\",\"label\":\"Dining Room\",\"score\":\"4\",\"comment\":\"4\"},\"common_area__bathrooms\":{\"section\":\"common_area\",\"label\":\"Bathrooms\",\"score\":\"4\",\"comment\":\"4\"},\"common_area__hallways\":{\"section\":\"common_area\",\"label\":\"Hallways\",\"score\":\"4\",\"comment\":\"4\"},\"common_area__office_area\":{\"section\":\"common_area\",\"label\":\"Office Area\",\"score\":\"4\",\"comment\":\"4\"},\"common_area__carpet\":{\"section\":\"common_area\",\"label\":\"Carpet\",\"score\":\"4\",\"comment\":\"4\"},\"common_area__walls\":{\"section\":\"common_area\",\"label\":\"Walls\",\"score\":\"4\",\"comment\":\"4\"},\"common_area__overall\":{\"section\":\"common_area\",\"label\":\"Overall\",\"score\":\"4\",\"comment\":\"22\"},\"bedrooms__cleanliness\":{\"section\":\"bedrooms\",\"label\":\"Cleanliness\",\"score\":\"2\",\"comment\":\"2\"},\"bedrooms__carpet\":{\"section\":\"bedrooms\",\"label\":\"Carpet\",\"score\":\"2\",\"comment\":\"2\"},\"bedrooms__walls\":{\"section\":\"bedrooms\",\"label\":\"Walls\",\"score\":\"2\",\"comment\":\"2\"},\"bedrooms__overall\":{\"section\":\"bedrooms\",\"label\":\"Overall\",\"score\":\"2\",\"comment\":\"\"},\"office_area__officer_binders\":{\"section\":\"office_area\",\"label\":\"Officer Binders\",\"score\":\"5\",\"comment\":\"5\"},\"office_area__filing_system\":{\"section\":\"office_area\",\"label\":\"Filing System\",\"score\":\"5\",\"comment\":\"5\"},\"office_area__organization\":{\"section\":\"office_area\",\"label\":\"Organization\",\"score\":\"5\",\"comment\":\"5\"},\"office_area__overall\":{\"section\":\"office_area\",\"label\":\"Overall\",\"score\":\"5\",\"comment\":\"\"},\"safety__smoke_detectors\":{\"section\":\"safety\",\"label\":\"Smoke Detectors\",\"score\":\"5\",\"comment\":\"5\"},\"safety__co2_detectors\":{\"section\":\"safety\",\"label\":\"CO2 Detectors\",\"score\":\"5\",\"comment\":\"5\"},\"safety__fire_extinguisher\":{\"section\":\"safety\",\"label\":\"Fire Extinguisher\",\"score\":\"5\",\"comment\":\"5\"},\"safety__rope_ladder\":{\"section\":\"safety\",\"label\":\"Rope Ladder\",\"score\":\"5\",\"comment\":\"5\"},\"safety__room_egress\":{\"section\":\"safety\",\"label\":\"Room Egress\",\"score\":\"5\",\"comment\":\"5\"},\"safety__first_aid_kit\":{\"section\":\"safety\",\"label\":\"First Aid Kit\",\"score\":\"5\",\"comment\":\"5\"}}', '{\"exterior\":30,\"common_area\":36,\"bedrooms\":8,\"office_area\":20,\"safety\":30}', 124, '2026-03-13 04:19:31', '2026-03-13 04:21:15');

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_houses`
--

CREATE TABLE `house_visit_houses` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(150) NOT NULL,
  `meeting_day` varchar(20) NOT NULL DEFAULT '',
  `meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `house_visit_houses`
--

INSERT INTO `house_visit_houses` (`id`, `house_name`, `meeting_day`, `meeting_time`, `created_at`, `updated_at`) VALUES
(1, 'Eagles Nest', 'Sunday', '15:30', '2026-03-09 06:34:49', '2026-03-09 06:34:49'),
(2, 'Northmoor', 'Monday', '14:30', '2026-03-09 06:34:49', '2026-03-09 06:34:49'),
(3, 'North Place', 'Friday', '18:30', '2026-03-09 06:34:49', '2026-03-09 06:34:49'),
(4, 'Norwich', 'Wednesday', '17:00', '2026-03-09 06:34:49', '2026-03-09 06:34:49'),
(5, 'Otro Dia', 'Sunday', '17:00', '2026-03-09 06:34:49', '2026-03-09 06:34:49'),
(6, 'Red Creek', 'Sunday', '18:00', '2026-03-09 06:34:49', '2026-03-09 06:34:49'),
(7, 'Starlight', 'Sunday', '10:00', '2026-03-09 06:34:49', '2026-03-09 06:34:49'),
(8, 'Sunset Park', 'Sunday', '14:00', '2026-03-09 06:34:49', '2026-03-09 06:34:49');

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_reports`
--

CREATE TABLE `house_visit_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(100) NOT NULL DEFAULT '',
  `president` varchar(255) NOT NULL DEFAULT '',
  `secretary` varchar(255) NOT NULL DEFAULT '',
  `treasurer` varchar(255) NOT NULL DEFAULT '',
  `comptroller` varchar(255) NOT NULL DEFAULT '',
  `coordinator` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep` varchar(255) NOT NULL DEFAULT '',
  `overall_appearance` varchar(10) NOT NULL DEFAULT '',
  `overall_appearance_comments` text DEFAULT NULL,
  `members_behind_ees` decimal(10,2) DEFAULT NULL,
  `total_amount_owed` decimal(12,2) DEFAULT NULL,
  `rent_paid_monthly` decimal(12,2) DEFAULT NULL,
  `ees_paid_weekly` decimal(12,2) DEFAULT NULL,
  `utilities_monthly` decimal(12,2) DEFAULT NULL,
  `house_business_meeting` varchar(10) NOT NULL DEFAULT '',
  `house_business_comments` text DEFAULT NULL,
  `rating_reading_traditions` varchar(10) NOT NULL DEFAULT '',
  `rating_reading_minutes` varchar(10) NOT NULL DEFAULT '',
  `rating_treasurer_report` varchar(10) NOT NULL DEFAULT '',
  `rating_comptroller_report` varchar(10) NOT NULL DEFAULT '',
  `rating_coordinator_report` varchar(10) NOT NULL DEFAULT '',
  `rating_maintains_guidelines` varchar(10) NOT NULL DEFAULT '',
  `rating_handling_business` varchar(10) NOT NULL DEFAULT '',
  `rating_organization_order` varchar(10) NOT NULL DEFAULT '',
  `financial_comments` text DEFAULT NULL,
  `first_visit_date` varchar(50) NOT NULL DEFAULT '',
  `narcan_present` varchar(10) NOT NULL DEFAULT '',
  `narcan_trained` varchar(10) NOT NULL DEFAULT '',
  `follow_up_visit_dates` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep_signature` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_schedules`
--

CREATE TABLE `house_visit_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `schedule_label` varchar(150) NOT NULL,
  `base_month` varchar(7) NOT NULL DEFAULT '',
  `month_count` int(11) NOT NULL DEFAULT 1,
  `repeat_cycle` tinyint(1) NOT NULL DEFAULT 0,
  `step_size` int(11) NOT NULL DEFAULT 1,
  `month_label` varchar(50) NOT NULL,
  `month_index` int(11) NOT NULL DEFAULT 1,
  `visiting_house` varchar(150) NOT NULL,
  `host_house` varchar(150) NOT NULL,
  `host_meeting_day` varchar(20) NOT NULL DEFAULT '',
  `host_meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `house_visit_schedules`
--

INSERT INTO `house_visit_schedules` (`id`, `schedule_label`, `base_month`, `month_count`, `repeat_cycle`, `step_size`, `month_label`, `month_index`, `visiting_house`, `host_house`, `host_meeting_day`, `host_meeting_time`, `created_at`) VALUES
(1, 'Rotation March 2026 - 2 Months', '', 1, 0, 1, 'March 2026', 1, 'Eagles Nest', 'North Place', 'Friday', '18:30', '2026-03-13 04:29:20'),
(2, 'Rotation March 2026 - 2 Months', '', 1, 0, 1, 'March 2026', 1, 'North Place', 'Northmoor', 'Monday', '14:30', '2026-03-13 04:29:20'),
(3, 'Rotation March 2026 - 2 Months', '', 1, 0, 1, 'March 2026', 1, 'Northmoor', 'Norwich', 'Wednesday', '17:00', '2026-03-13 04:29:20'),
(4, 'Rotation March 2026 - 2 Months', '', 1, 0, 1, 'March 2026', 1, 'Norwich', 'Otro Dia', 'Sunday', '17:00', '2026-03-13 04:29:20'),
(5, 'Rotation March 2026 - 2 Months', '', 1, 0, 1, 'March 2026', 1, 'Otro Dia', 'Red Creek', 'Sunday', '18:00', '2026-03-13 04:29:20'),
(6, 'Rotation March 2026 - 2 Months', '', 1, 0, 1, 'March 2026', 1, 'Red Creek', 'Starlight', 'Sunday', '10:00', '2026-03-13 04:29:20'),
(7, 'Rotation March 2026 - 2 Months', '', 1, 0, 1, 'March 2026', 1, 'Starlight', 'Sunset Park', 'Sunday', '14:00', '2026-03-13 04:29:20'),
(8, 'Rotation March 2026 - 2 Months', '', 1, 0, 1, 'March 2026', 1, 'Sunset Park', 'Eagles Nest', 'Sunday', '15:30', '2026-03-13 04:29:20'),
(9, 'Rotation March 2026 - 2 Months', '', 1, 0, 1, 'April 2026', 2, 'Eagles Nest', 'Northmoor', 'Monday', '14:30', '2026-03-13 04:29:20'),
(10, 'Rotation March 2026 - 2 Months', '', 1, 0, 1, 'April 2026', 2, 'North Place', 'Norwich', 'Wednesday', '17:00', '2026-03-13 04:29:20'),
(11, 'Rotation March 2026 - 2 Months', '', 1, 0, 1, 'April 2026', 2, 'Northmoor', 'Otro Dia', 'Sunday', '17:00', '2026-03-13 04:29:20'),
(12, 'Rotation March 2026 - 2 Months', '', 1, 0, 1, 'April 2026', 2, 'Norwich', 'Red Creek', 'Sunday', '18:00', '2026-03-13 04:29:20'),
(13, 'Rotation March 2026 - 2 Months', '', 1, 0, 1, 'April 2026', 2, 'Otro Dia', 'Starlight', 'Sunday', '10:00', '2026-03-13 04:29:20'),
(14, 'Rotation March 2026 - 2 Months', '', 1, 0, 1, 'April 2026', 2, 'Red Creek', 'Sunset Park', 'Sunday', '14:00', '2026-03-13 04:29:20'),
(15, 'Rotation March 2026 - 2 Months', '', 1, 0, 1, 'April 2026', 2, 'Starlight', 'Eagles Nest', 'Sunday', '15:30', '2026-03-13 04:29:20'),
(16, 'Rotation March 2026 - 2 Months', '', 1, 0, 1, 'April 2026', 2, 'Sunset Park', 'North Place', 'Friday', '18:30', '2026-03-13 04:29:20'),
(17, 'Rotation March 2026 - 2 Months - Step 2', '2026-03', 2, 0, 2, 'March 2026', 1, 'Eagles Nest', 'Northmoor', 'Monday', '14:30', '2026-03-13 04:47:11'),
(18, 'Rotation March 2026 - 2 Months - Step 2', '2026-03', 2, 0, 2, 'March 2026', 1, 'North Place', 'Norwich', 'Wednesday', '17:00', '2026-03-13 04:47:11'),
(19, 'Rotation March 2026 - 2 Months - Step 2', '2026-03', 2, 0, 2, 'March 2026', 1, 'Northmoor', 'Otro Dia', 'Sunday', '17:00', '2026-03-13 04:47:11'),
(20, 'Rotation March 2026 - 2 Months - Step 2', '2026-03', 2, 0, 2, 'March 2026', 1, 'Norwich', 'Red Creek', 'Sunday', '18:00', '2026-03-13 04:47:11'),
(21, 'Rotation March 2026 - 2 Months - Step 2', '2026-03', 2, 0, 2, 'March 2026', 1, 'Otro Dia', 'Starlight', 'Sunday', '10:00', '2026-03-13 04:47:11'),
(22, 'Rotation March 2026 - 2 Months - Step 2', '2026-03', 2, 0, 2, 'March 2026', 1, 'Red Creek', 'Sunset Park', 'Sunday', '14:00', '2026-03-13 04:47:11'),
(23, 'Rotation March 2026 - 2 Months - Step 2', '2026-03', 2, 0, 2, 'March 2026', 1, 'Starlight', 'Eagles Nest', 'Sunday', '15:30', '2026-03-13 04:47:11'),
(24, 'Rotation March 2026 - 2 Months - Step 2', '2026-03', 2, 0, 2, 'March 2026', 1, 'Sunset Park', 'North Place', 'Friday', '18:30', '2026-03-13 04:47:11'),
(25, 'Rotation March 2026 - 2 Months - Step 2', '2026-03', 2, 0, 2, 'April 2026', 2, 'Eagles Nest', 'Otro Dia', 'Sunday', '17:00', '2026-03-13 04:47:11'),
(26, 'Rotation March 2026 - 2 Months - Step 2', '2026-03', 2, 0, 2, 'April 2026', 2, 'North Place', 'Red Creek', 'Sunday', '18:00', '2026-03-13 04:47:11'),
(27, 'Rotation March 2026 - 2 Months - Step 2', '2026-03', 2, 0, 2, 'April 2026', 2, 'Northmoor', 'Starlight', 'Sunday', '10:00', '2026-03-13 04:47:11'),
(28, 'Rotation March 2026 - 2 Months - Step 2', '2026-03', 2, 0, 2, 'April 2026', 2, 'Norwich', 'Sunset Park', 'Sunday', '14:00', '2026-03-13 04:47:11'),
(29, 'Rotation March 2026 - 2 Months - Step 2', '2026-03', 2, 0, 2, 'April 2026', 2, 'Otro Dia', 'Eagles Nest', 'Sunday', '15:30', '2026-03-13 04:47:11'),
(30, 'Rotation March 2026 - 2 Months - Step 2', '2026-03', 2, 0, 2, 'April 2026', 2, 'Red Creek', 'North Place', 'Friday', '18:30', '2026-03-13 04:47:11'),
(31, 'Rotation March 2026 - 2 Months - Step 2', '2026-03', 2, 0, 2, 'April 2026', 2, 'Starlight', 'Northmoor', 'Monday', '14:30', '2026-03-13 04:47:11'),
(32, 'Rotation March 2026 - 2 Months - Step 2', '2026-03', 2, 0, 2, 'April 2026', 2, 'Sunset Park', 'Norwich', 'Wednesday', '17:00', '2026-03-13 04:47:11');

-- --------------------------------------------------------

--
-- Table structure for table `housing_service_representative_reports`
--

CREATE TABLE `housing_service_representative_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `week_from` varchar(100) NOT NULL DEFAULT '',
  `week_to` varchar(100) NOT NULL DEFAULT '',
  `house_visit` text DEFAULT NULL,
  `audit_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `audit_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `next_chapter_meeting` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `next_state_meeting` text DEFAULT NULL,
  `number_of_applications` text DEFAULT NULL,
  `number_contacted_plan` longtext DEFAULT NULL,
  `new_members` longtext DEFAULT NULL,
  `interviews_setup` longtext DEFAULT NULL,
  `chapter_news` longtext DEFAULT NULL,
  `chapter_meeting_recap` longtext DEFAULT NULL,
  `upcoming_unity` longtext DEFAULT NULL,
  `upcoming_presentations` longtext DEFAULT NULL,
  `hsr_name` varchar(255) NOT NULL DEFAULT '',
  `report_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hsc_meeting_minutes_json`
--

CREATE TABLE `hsc_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hsc_meeting_minutes_json`
--

INSERT INTO `hsc_meeting_minutes_json` (`id`, `house_name`, `meeting_date`, `start_time`, `end_time`, `report_json`, `created_at`, `updated_at`) VALUES
(1, 'Eagles Nest - Chapter 14', '2026-03-07', '18:07:00', '19:29:00', '{\n    \"main_house_name\": \"Eagles Nest - Chapter 14\",\n    \"meeting_date\": \"2026-03-07\",\n    \"start_time\": \"18:07\",\n    \"end_time\": \"19:29\",\n    \"hsc_position_rows\": [\n        {\n            \"position_name\": \"HSC Chair\",\n            \"member_name\": \"Krystal and Moriah\",\n            \"present\": \"Y\"\n        },\n        {\n            \"position_name\": \"Chapter Vice Chair\",\n            \"member_name\": \"Waylon\",\n            \"present\": \"Y\"\n        },\n        {\n            \"position_name\": \"HSC Treasurer\",\n            \"member_name\": \"Ricky\",\n            \"present\": \"Y\"\n        },\n        {\n            \"position_name\": \"HSC Coordinator\",\n            \"member_name\": \"Eric\",\n            \"present\": \"Y\"\n        },\n        {\n            \"position_name\": \"HSC Secretary\",\n            \"member_name\": \"Jose\",\n            \"present\": \"Y\"\n        },\n        {\n            \"position_name\": \"Re-Entry\",\n            \"member_name\": \"Josey\",\n            \"present\": \"Y\"\n        },\n        {\n            \"position_name\": \"Fundraiser\",\n            \"member_name\": \"Frank and Nataley\",\n            \"present\": \"Y\"\n        },\n        {\n            \"position_name\": \"Outreach\",\n            \"member_name\": \"Cat\",\n            \"present\": \"E\"\n        },\n        {\n            \"position_name\": \"State\",\n            \"member_name\": \"Paul, Alex, Anyssa\",\n            \"present\": \"Y\"\n        }\n    ],\n    \"roll_call_rows\": [\n        {\n            \"house_name\": \"Eagles Nest\",\n            \"president_proxy\": \"Kaylee\",\n            \"meeting_day\": \"Sunday\",\n            \"meeting_time\": \"15:30\",\n            \"hsr_completed\": \"Y\"\n        },\n        {\n            \"house_name\": \"Northmoor\",\n            \"president_proxy\": \"Jeff\",\n            \"meeting_day\": \"Monday\",\n            \"meeting_time\": \"14:30\",\n            \"hsr_completed\": \"Y\"\n        },\n        {\n            \"house_name\": \"North Place\",\n            \"president_proxy\": \"Joel\",\n            \"meeting_day\": \"Friday\",\n            \"meeting_time\": \"18:30\",\n            \"hsr_completed\": \"N\"\n        },\n        {\n            \"house_name\": \"Norwich\",\n            \"president_proxy\": \"Robin (Proxy)\",\n            \"meeting_day\": \"Wednesday\",\n            \"meeting_time\": \"17:00\",\n            \"hsr_completed\": \"N\"\n        },\n        {\n            \"house_name\": \"Otro Dia\",\n            \"president_proxy\": \"Ricky\",\n            \"meeting_day\": \"Sunday\",\n            \"meeting_time\": \"17:00\",\n            \"hsr_completed\": \"Y\"\n        },\n        {\n            \"house_name\": \"Red Creek\",\n            \"president_proxy\": \"Alex\",\n            \"meeting_day\": \"Sunday\",\n            \"meeting_time\": \"18:00\",\n            \"hsr_completed\": \"Y\"\n        },\n        {\n            \"house_name\": \"Starlight\",\n            \"president_proxy\": \"Waylon\",\n            \"meeting_day\": \"Sunday\",\n            \"meeting_time\": \"10:00\",\n            \"hsr_completed\": \"N\"\n        },\n        {\n            \"house_name\": \"Sunset Park\",\n            \"president_proxy\": \"Hannah\",\n            \"meeting_day\": \"Sunday\",\n            \"meeting_time\": \"14:00\",\n            \"hsr_completed\": \"Y\"\n        }\n    ],\n    \"mission_statement_read\": \"Y\",\n    \"secretary_member_name\": \"Moriah - Redcreek\",\n    \"secretary_comments\": \"No Corrections\",\n    \"secretary_motion\": 1,\n    \"secretary_yay\": \"\",\n    \"secretary_nay\": \"\",\n    \"treasurer_member_name\": \"Ricky\",\n    \"treasurer_beginning_balance\": \"3918.22\",\n    \"treasurer_money_received\": [\n        {\n            \"date\": \"2026-02-06\",\n            \"purpose\": \"North Place UA\",\n            \"amount\": \"125\"\n        },\n        {\n            \"date\": \"2026-02-08\",\n            \"purpose\": \"Starlite Fine\",\n            \"amount\": \"50\"\n        },\n        {\n            \"date\": \"2026-02-13\",\n            \"purpose\": \"Red Creek Fine\",\n            \"amount\": \"50\"\n        },\n        {\n            \"date\": \"2026-02-21\",\n            \"purpose\": \"Eagles Nest Fine\",\n            \"amount\": \"50\"\n        },\n        {\n            \"date\": \"\",\n            \"purpose\": \"\",\n            \"amount\": \"\"\n        },\n        {\n            \"date\": \"\",\n            \"purpose\": \"\",\n            \"amount\": \"\"\n        }\n    ],\n    \"treasurer_total_received\": \"275.00\",\n    \"treasurer_money_spent\": [\n        {\n            \"date\": \"2026-02-07\",\n            \"purpose\": \"Chapter 12 Convention Donation\",\n            \"check_no\": \"1177\",\n            \"amount\": \"500\"\n        },\n        {\n            \"date\": \"2026-02-16\",\n            \"purpose\": \"Reimbursement Sunset Park\",\n            \"check_no\": \"1178\",\n            \"amount\": \"177\"\n        },\n        {\n            \"date\": \"\",\n            \"purpose\": \"\",\n            \"check_no\": \"\",\n            \"amount\": \"\"\n        },\n        {\n            \"date\": \"\",\n            \"purpose\": \"\",\n            \"check_no\": \"\",\n            \"amount\": \"\"\n        },\n        {\n            \"date\": \"\",\n            \"purpose\": \"\",\n            \"check_no\": \"\",\n            \"amount\": \"\"\n        },\n        {\n            \"date\": \"\",\n            \"purpose\": \"\",\n            \"check_no\": \"\",\n            \"amount\": \"\"\n        }\n    ],\n    \"treasurer_total_spent\": \"677.00\",\n    \"treasurer_ending_balance\": \"3516.22\",\n    \"treasurer_motion\": 1,\n    \"treasurer_yay\": \"\",\n    \"treasurer_nay\": \"\",\n    \"chair_member_name\": \"Krystal and Moriah\",\n    \"chair_comments\": \"Open beds reduced from 21 to 13\\r\\nEncouragement for members to join Discord and exchange phone numbers\\r\\nReminder to lean on other Oxford House members for support\",\n    \"chair_motion\": 1,\n    \"chair_yay\": \"\",\n    \"chair_nay\": \"\",\n    \"vice_chair_member_name\": \"Waylon\",\n    \"vice_chair_comments\": \"- Positive feedback seeing houses filling up\\r\\n- increased calls from individuals seeking placement\\r\\n- Foundations Recovery is making operational changes\\r\\n- Crisis Life Ministries is closing down\\r\\n- House advised to check the SO registry for updates\",\n    \"vice_chair_motion\": 1,\n    \"vice_chair_yay\": \"\",\n    \"vice_chair_nay\": \"\",\n    \"state_member_name\": \"Paul, Alex, Anyssa\",\n    \"state_comments\": \"- New Comer orientation on the 21st\\r\\n    New Comers Orientation - Located at 2122 S. Lafayette St. Denver, CO @ Free Recovery Church, Every 3rd Saturday of the month @ 11am\\r\\n- Expansion of state service positions\\r\\n- Importance of financial viability calculator (EES calc)\\r\\n- Encouragement to participate in State Service Positions\\r\\n    State HSC Meeting - Located at 2122 S. Lafayette St. Denver, CO @ Free Recovery Church, 4\\/18\\/2026\\r\\nMMSP to reimburse gas for travel to State Meeting\",\n    \"state_motion\": 1,\n    \"state_yay\": \"\",\n    \"state_nay\": \"\",\n    \"reentry_member_name\": \"Josey\",\n    \"reentry_comments\": \"- Several Interviews conducted\\r\\n- New placements arranged in houses including North Place and Starlight\\r\\n- Re-entry demand increasing\\r\\n- 2 to 3 applications currently being reviewed for the Pueblo Area\",\n    \"reentry_motion\": 1,\n    \"reentry_yay\": \"\",\n    \"reentry_nay\": \"\",\n    \"fundraiser_member_name\": \"Frank and Nataley\",\n    \"fundraiser_comments\": \"Recent and Upcoming activities\\r\\n- Possible sky diving trip\\r\\n- Zipline event ideas\\r\\n- Adult Kickball\\r\\n- Outdoor Events\\r\\n- Social Recovery Activities\\r\\n- Looking for feedback from houses\",\n    \"fundraiser_motion\": 1,\n    \"fundraiser_yay\": \"\",\n    \"fundraiser_nay\": \"\",\n    \"outreach_member_name\": \"Cat\",\n    \"outreach_comments\": \"Outreach was out for an Oxford House Training\",\n    \"outreach_motion\": 1,\n    \"outreach_yay\": \"\",\n    \"outreach_nay\": \"\",\n    \"house_checkins\": [\n        {\n            \"house_name\": \"Eagles Nest\",\n            \"comments\": \"- Doing Well\\r\\n- House Unity Good\\r\\n- Some contact management issues being addressed\\r\\n- Some financial struggles\\r\\n- House is full\"\n        },\n        {\n            \"house_name\": \"Northmoor\",\n            \"comments\": \"- One member moved back to Las Vegas\\r\\n- 1 new member accepted\\r\\n- Recovery participation strong\\r\\n- 3 open beds\"\n        },\n        {\n            \"house_name\": \"North Place\",\n            \"comments\": \"- House running well\\r\\n- 1 member lost their employment but still active in recovery\"\n        },\n        {\n            \"house_name\": \"Norwich\",\n            \"comments\": \"- COVID affecting house\\r\\n- 1 new-comer dismissed for failing curfew and refusing UA\\r\\n- Financials slightly strained but manageable\"\n        },\n        {\n            \"house_name\": \"Otro Dia\",\n            \"comments\": \"= 1 member moved out\\r\\n- Re-entry applicant is pending\\r\\n- Everyone is employed\\r\\n- Oven recently broke (repair pending)\"\n        },\n        {\n            \"house_name\": \"Red Creek\",\n            \"comments\": \"- All members employed\\r\\n- Strong brotherhood and recovery engagement\\r\\n- Beds currently full\"\n        },\n        {\n            \"house_name\": \"Starlight\",\n            \"comments\": \"- 1 relapsed leading to dismissal\\r\\n- 1 member moved out\\r\\n- 1 vacancy\\r\\n- Planning social events (camping, bingo)\"\n        },\n        {\n            \"house_name\": \"Sunset Park\",\n            \"comments\": \"- 4 new members accepted\\r\\n- 1 currently behind\\r\\n- 3 members have jobs\"\n        },\n        {\n            \"house_name\": \"\",\n            \"comments\": \"\"\n        },\n        {\n            \"house_name\": \"\",\n            \"comments\": \"\"\n        }\n    ],\n    \"unfinished_business_member_name\": \"Moriah\",\n    \"unfinished_business_comments\": \"Reading of the previous minutes began at 19:09\\r\\nNo other unfinished business was conducted\",\n    \"unfinished_business_motion\": 1,\n    \"unfinished_business_yay\": \"\",\n    \"unfinished_business_nay\": \"\",\n    \"new_business_member_name\": \"\",\n    \"new_business_comments\": \"Coordinator Nomination: \\r\\n- Joel - Accepted \\r\\n- Johnathan - Not accepted\\r\\nMMSP Joel elected as HSC Coordinator\\r\\n\\r\\nHouse Visit Fines:\\r\\n- North Place - $50 for missing HSR report and not completing house visit\\r\\n- Starlight - $50 for not completing a house visit\\r\\n- Norwich - $100 (repeated offense) for missing responsibilities 2 months in a row\\r\\n\\r\\nAmendment made on 3\\/12\\/2026\\r\\n- Norwich - $50 for missing HSR report and not completing house visit\\r\\n- North Place - $50 for not completing a house visit\\r\\n- Starlight - $100 (repeated offense) for missing responsibilities 2 months in a row\\r\\n\\r\\nAll Chapter HAR Reports were completed on time\\r\\n\\r\\nMMSP approved to reimburse Eagles Nest for HSC meeting food and drinks in the amount of $82.72\",\n    \"new_business_motion\": 1,\n    \"new_business_yay\": \"\",\n    \"new_business_nay\": \"\",\n    \"secretary_name\": \"Jose D.\"\n}', '2026-03-08 19:33:54', '2026-03-13 02:51:11');

-- --------------------------------------------------------

--
-- Table structure for table `landlord_verification_forms`
--

CREATE TABLE `landlord_verification_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_name` varchar(255) NOT NULL DEFAULT '',
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `landlord_manager_name` varchar(255) NOT NULL DEFAULT '',
  `manager_street` varchar(255) NOT NULL DEFAULT '',
  `manager_city` varchar(150) NOT NULL DEFAULT '',
  `manager_state` varchar(50) NOT NULL DEFAULT '',
  `manager_zip` varchar(25) NOT NULL DEFAULT '',
  `manager_county` varchar(150) NOT NULL DEFAULT '',
  `manager_phone` varchar(50) NOT NULL DEFAULT '',
  `manager_email` varchar(255) NOT NULL DEFAULT '',
  `rental_street` varchar(255) NOT NULL DEFAULT '',
  `rental_apt_lot` varchar(100) NOT NULL DEFAULT '',
  `rental_city` varchar(150) NOT NULL DEFAULT '',
  `rental_state` varchar(50) NOT NULL DEFAULT '',
  `rental_zip` varchar(25) NOT NULL DEFAULT '',
  `rental_county` varchar(150) NOT NULL DEFAULT '',
  `bedrooms` varchar(20) NOT NULL DEFAULT '',
  `lease_start_date` date DEFAULT NULL,
  `lease_end_date` date DEFAULT NULL,
  `monthly_rent_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `next_payment_due_date` date DEFAULT NULL,
  `last_payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `last_payment_date` date DEFAULT NULL,
  `tenant_in_arrears` tinyint(1) NOT NULL DEFAULT 0,
  `amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `arrears_period_from` date DEFAULT NULL,
  `arrears_period_to` date DEFAULT NULL,
  `receiving_other_assistance` tinyint(1) NOT NULL DEFAULT 0,
  `other_assistance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_assistance_period` varchar(100) NOT NULL DEFAULT '',
  `payment_method` varchar(20) NOT NULL DEFAULT 'check',
  `check_payable_to` varchar(255) NOT NULL DEFAULT '',
  `send_to_landlord_address` tinyint(1) NOT NULL DEFAULT 1,
  `alternative_address` text DEFAULT NULL,
  `cert_name` varchar(255) NOT NULL DEFAULT '',
  `cert_title` varchar(255) NOT NULL DEFAULT '',
  `cert_signature` varchar(255) NOT NULL DEFAULT '',
  `cert_date` date DEFAULT NULL,
  `calc_balance_remaining` decimal(10,2) NOT NULL DEFAULT 0.00,
  `calc_total_assistance_gap` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `landlord_verification_forms`
--

INSERT INTO `landlord_verification_forms` (`id`, `tenant_name`, `property_owner_name`, `landlord_manager_name`, `manager_street`, `manager_city`, `manager_state`, `manager_zip`, `manager_county`, `manager_phone`, `manager_email`, `rental_street`, `rental_apt_lot`, `rental_city`, `rental_state`, `rental_zip`, `rental_county`, `bedrooms`, `lease_start_date`, `lease_end_date`, `monthly_rent_amount`, `next_payment_due_date`, `last_payment_amount`, `last_payment_date`, `tenant_in_arrears`, `amount_owed`, `arrears_period_from`, `arrears_period_to`, `receiving_other_assistance`, `other_assistance_amount`, `other_assistance_period`, `payment_method`, `check_payable_to`, `send_to_landlord_address`, `alternative_address`, `cert_name`, `cert_title`, `cert_signature`, `cert_date`, `calc_balance_remaining`, `calc_total_assistance_gap`, `created_at`, `updated_at`) VALUES
(1, 'Jose Davila', 'Phil Kilpatrick / Oxford House', 'Moriah Cunningham', '213 Fordham Cir', 'Pueblo', 'CO', '81005', 'Pueblo', '719-467-0680', 'cunninghammoriah23@gmail.com', '213 Fordham Cir', 'N/A', 'Pueblo', 'CO', '81005', 'Pueblo', '9', '2025-12-31', '2026-12-31', 800.00, '2026-03-14', 200.00, '2026-03-06', 1, 2010.00, '2026-03-01', '2026-04-01', 0, 0.00, '', 'check', 'Oxford House Red Creek', 1, '', 'Moriah Cunningham', 'House Manager', '', NULL, 600.00, 2010.00, '2026-03-10 07:54:55', '2026-03-10 08:02:52');

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheets`
--

CREATE TABLE `medication_count_sheets` (
  `id` int(10) UNSIGNED NOT NULL,
  `resident_name` varchar(255) NOT NULL,
  `sheet_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheet_rows`
--

CREATE TABLE `medication_count_sheet_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `sheet_id` int(10) UNSIGNED NOT NULL,
  `row_number` int(11) NOT NULL,
  `entry_date` varchar(50) DEFAULT NULL,
  `medication_name` varchar(255) DEFAULT NULL,
  `dosage` varchar(255) DEFAULT NULL,
  `frequency` varchar(255) DEFAULT NULL,
  `previous_count` varchar(100) DEFAULT NULL,
  `current_count` varchar(100) DEFAULT NULL,
  `member_initials` varchar(50) DEFAULT NULL,
  `witness_initials` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `new_house_tour_forms`
--

CREATE TABLE `new_house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `form_date` varchar(50) NOT NULL DEFAULT '',
  `form_time` varchar(50) NOT NULL DEFAULT '',
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `inspector_signature` varchar(255) NOT NULL DEFAULT '',
  `smoking_area_score` varchar(5) NOT NULL DEFAULT '',
  `smoking_area_comment` text DEFAULT NULL,
  `notes_text` text DEFAULT NULL,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_average` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payload_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nightly_kitchen_schedules`
--

CREATE TABLE `nightly_kitchen_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `trash_to_curb_on` varchar(255) NOT NULL DEFAULT '',
  `schedule_data` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_chore_lists`
--

CREATE TABLE `oxford_chore_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT 'CHORE LIST',
  `week1_start` varchar(20) NOT NULL DEFAULT '',
  `week2_start` varchar(20) NOT NULL DEFAULT '',
  `week3_start` varchar(20) NOT NULL DEFAULT '',
  `week4_start` varchar(20) NOT NULL DEFAULT '',
  `week5_start` varchar(20) NOT NULL DEFAULT '',
  `week6_start` varchar(20) NOT NULL DEFAULT '',
  `week7_start` varchar(20) NOT NULL DEFAULT '',
  `week8_start` varchar(20) NOT NULL DEFAULT '',
  `form_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_disruptive_contracts`
--

CREATE TABLE `oxford_disruptive_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_subject` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(50) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `behavior_1` text DEFAULT NULL,
  `behavior_2` text DEFAULT NULL,
  `behavior_3` text DEFAULT NULL,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgment_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(50) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_original_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_stored_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_mime` varchar(100) NOT NULL DEFAULT '',
  `uploaded_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oxford_disruptive_contracts`
--

INSERT INTO `oxford_disruptive_contracts` (`id`, `member_name`, `house_name`, `contract_subject`, `contract_date`, `contract_length`, `behavior_1`, `behavior_2`, `behavior_3`, `term_1`, `term_2`, `term_3`, `term_4`, `acknowledgment_name`, `signature_date`, `president_name`, `secretary_name`, `treasurer_name`, `comptroller_name`, `coordinator_name`, `hs_representative_name`, `member_1_name`, `member_2_name`, `member_3_name`, `member_4_name`, `uploaded_original_name`, `uploaded_stored_name`, `uploaded_mime`, `uploaded_size`, `created_at`, `updated_at`) VALUES
(1, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, '2026-03-11 03:18:17', '2026-03-11 03:18:17');

-- --------------------------------------------------------

--
-- Table structure for table `oxford_financial_audits`
--

CREATE TABLE `oxford_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `date_completed` date DEFAULT NULL,
  `bank_statement_ending_date` date DEFAULT NULL,
  `bank_statement_ending_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_past_due_bills` decimal(12,2) NOT NULL DEFAULT 0.00,
  `savings_account_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_outstanding_ees` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deposits_json` longtext DEFAULT NULL,
  `checks_json` longtext DEFAULT NULL,
  `treasurer_signature` varchar(255) DEFAULT NULL,
  `comptroller_signature` varchar(255) DEFAULT NULL,
  `president_signature` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oxford_financial_audits`
--

INSERT INTO `oxford_financial_audits` (`id`, `house_name`, `date_completed`, `bank_statement_ending_date`, `bank_statement_ending_balance`, `total_past_due_bills`, `savings_account_balance`, `total_outstanding_ees`, `deposits_json`, `checks_json`, `treasurer_signature`, `comptroller_signature`, `president_signature`, `created_at`, `updated_at`) VALUES
(1, 'RED CREEK', NULL, NULL, 0.00, 0.00, 0.00, 0.00, '[[\"\"],[\"\"],[\"\"],[\"\"],[\"\"],[\"\"],[\"100\"],[\"\"]]', '[[\"\",\"\",\"\",\"100\"],[\"\",\"\",\"\",\"\"],[\"\",\"\",\"\",\"\"],[\"\",\"\",\"\",\"\"],[\"\",\"\",\"\",\"\"],[\"\",\"\",\"\",\"\"],[\"\",\"\",\"\",\"\"],[\"\",\"\",\"\",\"\"],[\"\",\"\",\"\",\"\"],[\"\",\"\",\"\",\"\"],[\"\",\"\",\"\",\"\"],[\"\",\"\",\"\",\"\"]]', '', '', '', '2026-03-09 20:35:30', '2026-03-11 03:00:01');

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_audits`
--

CREATE TABLE `oxford_house_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `completed_month` varchar(10) NOT NULL DEFAULT '',
  `completed_day` varchar(10) NOT NULL DEFAULT '',
  `completed_year` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_month` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_day` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_year` varchar(10) NOT NULL DEFAULT '',
  `past_due_bills` varchar(50) NOT NULL DEFAULT '',
  `savings_balance` varchar(50) NOT NULL DEFAULT '',
  `outstanding_ees` varchar(50) NOT NULL DEFAULT '',
  `bank_statement_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `deposits_total` varchar(50) NOT NULL DEFAULT '',
  `checks_total` varchar(50) NOT NULL DEFAULT '',
  `balance_after_audit` varchar(50) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_signature` varchar(255) NOT NULL DEFAULT '',
  `comptroller_signature` varchar(255) NOT NULL DEFAULT '',
  `president_signature` varchar(255) NOT NULL DEFAULT '',
  `checks_rows` longtext DEFAULT NULL,
  `deposits_rows` longtext DEFAULT NULL,
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `scan_stored_name` varchar(255) NOT NULL DEFAULT '',
  `scan_path` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oxford_house_financial_audits`
--

INSERT INTO `oxford_house_financial_audits` (`id`, `house_name`, `completed_month`, `completed_day`, `completed_year`, `bank_ending_month`, `bank_ending_day`, `bank_ending_year`, `past_due_bills`, `savings_balance`, `outstanding_ees`, `bank_statement_ending_balance`, `deposits_total`, `checks_total`, `balance_after_audit`, `treasurer_name`, `comptroller_name`, `president_name`, `treasurer_signature`, `comptroller_signature`, `president_signature`, `checks_rows`, `deposits_rows`, `scan_original_name`, `scan_stored_name`, `scan_path`, `created_at`, `updated_at`) VALUES
(1, '', '', '', '', '', '', '', '', '', '', '', '0.00', '0.00', '0.00', '', '', '', '', '', '', '[]', '[]', '', '', '', '2026-03-09 20:26:58', '2026-03-09 20:27:01'),
(2, '', '', '', '', '', '', '', '', '', '', '', '0.00', '0.00', '0.00', '', 'John Doe', '', '', 'John Doe', '', '[]', '[]', '', '', '', '2026-03-11 05:26:28', '2026-03-11 05:26:34');

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_reports`
--

CREATE TABLE `oxford_house_financial_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `report_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oxford_house_financial_reports`
--

INSERT INTO `oxford_house_financial_reports` (`id`, `house_name`, `date_from`, `date_to`, `report_data`, `created_at`, `updated_at`) VALUES
(1, 'RED CREEK', '2026-03-01', '2026-03-07', '{\"house_name\":\"RED CREEK\",\"date_from\":\"2026-03-01\",\"date_to\":\"2026-03-07\",\"total_received\":\"0.00\",\"total_to_be_deposited\":\"3184.00\",\"total_spent\":\"350.00\",\"total_due\":\"0.00\",\"sav_begin\":\"10225.15\",\"sav_deposit\":\"0\",\"sav_withdraw\":\"0\",\"sav_interest\":\"0\",\"sav_end\":\"10225.15\",\"pc_begin\":\"29.09\",\"pc_spent\":\"0\",\"pc_repl\":\"0\",\"pc_end\":\"29.09\",\"receipts_reviewed\":\"yes\",\"eq_begin_bal\":\"1941.50\",\"eq_total_received\":\"0.00\",\"eq_total_spent\":\"350.00\",\"eq_ending_bal\":\"1591.50\",\"mr_date_1\":\"\",\"mr_source_1\":\"\",\"mr_amount_1\":\"\",\"mr_date_2\":\"\",\"mr_source_2\":\"\",\"mr_amount_2\":\"\",\"mr_date_3\":\"\",\"mr_source_3\":\"\",\"mr_amount_3\":\"\",\"mr_date_4\":\"\",\"mr_source_4\":\"\",\"mr_amount_4\":\"\",\"mr_date_5\":\"\",\"mr_source_5\":\"\",\"mr_amount_5\":\"\",\"mr_date_6\":\"\",\"mr_source_6\":\"\",\"mr_amount_6\":\"\",\"mr_date_7\":\"\",\"mr_source_7\":\"\",\"mr_amount_7\":\"\",\"mr_date_8\":\"\",\"mr_source_8\":\"\",\"mr_amount_8\":\"\",\"mr_date_9\":\"\",\"mr_source_9\":\"\",\"mr_amount_9\":\"\",\"mr_date_10\":\"\",\"mr_source_10\":\"\",\"mr_amount_10\":\"\",\"td_date_1\":\"3\\/8\",\"td_source_1\":\"EES\",\"td_amount_1\":\"3184\",\"td_date_2\":\"\",\"td_source_2\":\"\",\"td_amount_2\":\"\",\"ae_date_1\":\"3\\/5\",\"ae_to_1\":\"Bed Dues\",\"ae_check_1\":\"1516\",\"ae_amount_1\":\"350\",\"ae_date_2\":\"\",\"ae_to_2\":\"\",\"ae_check_2\":\"\",\"ae_amount_2\":\"\",\"ae_date_3\":\"\",\"ae_to_3\":\"\",\"ae_check_3\":\"\",\"ae_amount_3\":\"\",\"ae_date_4\":\"\",\"ae_to_4\":\"\",\"ae_check_4\":\"\",\"ae_amount_4\":\"\",\"ae_date_5\":\"\",\"ae_to_5\":\"\",\"ae_check_5\":\"\",\"ae_amount_5\":\"\",\"ub_to_1\":\"\",\"ub_due_1\":\"\",\"ub_amount_1\":\"\",\"ub_to_2\":\"\",\"ub_due_2\":\"\",\"ub_amount_2\":\"\",\"ub_to_3\":\"\",\"ub_due_3\":\"\",\"ub_amount_3\":\"\",\"ub_to_4\":\"\",\"ub_due_4\":\"\",\"ub_amount_4\":\"\",\"ub_to_5\":\"\",\"ub_due_5\":\"\",\"ub_amount_5\":\"\",\"ub_to_6\":\"\",\"ub_due_6\":\"\",\"ub_amount_6\":\"\"}', '2026-03-08 23:04:27', '2026-03-08 23:06:42');

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_ledger_forms`
--

CREATE TABLE `oxford_house_ledger_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `rows_json` longtext DEFAULT NULL,
  `totals_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oxford_house_ledger_forms`
--

INSERT INTO `oxford_house_ledger_forms` (`id`, `house_name`, `week_start`, `week_end`, `notes`, `rows_json`, `totals_json`, `created_at`, `updated_at`) VALUES
(1, 'RED CREEK', '2026-03-01', '2026-03-07', '3/4 Frank Paid $470 ** 3/6 Arturo paid $250 ** 3/6 Jose paid $200 ** 3/6 Charles paid $470\r\n3/6 Neal paid $350 ** 3/7 Moriah paid $569 ** 3/7 Tristan paid $700 ** 3/7 Todd paid $175', '[{\"member_name\":\"Moriah\",\"previous_balance\":\"275.73\",\"ees_due\":\"195\",\"fines_other\":\"0\",\"approved_receipts\":\"0\",\"total\":\"470.73\",\"amount_paid\":\"569.00\",\"ending_balance\":\"98.27\",\"ending_status\":\"AHEAD\"},{\"member_name\":\"Frank\",\"previous_balance\":\"1084.44\",\"ees_due\":\"195\",\"fines_other\":\"0\",\"approved_receipts\":\"0\",\"total\":\"1279.44\",\"amount_paid\":\"470.00\",\"ending_balance\":\"809.44\",\"ending_status\":\"BEHIND\"},{\"member_name\":\"Tristan\",\"previous_balance\":\"1326.28\",\"ees_due\":\"195\",\"fines_other\":\"0\",\"approved_receipts\":\"0\",\"total\":\"1521.28\",\"amount_paid\":\"700.00\",\"ending_balance\":\"821.28\",\"ending_status\":\"BEHIND\"},{\"member_name\":\"Troy\",\"previous_balance\":\"137.33\",\"ees_due\":\"195\",\"fines_other\":\"0\",\"approved_receipts\":\"0\",\"total\":\"332.33\",\"amount_paid\":\"0.00\",\"ending_balance\":\"332.33\",\"ending_status\":\"BEHIND\"},{\"member_name\":\"Charles\",\"previous_balance\":\"1170.00\",\"ees_due\":\"195\",\"fines_other\":\"0\",\"approved_receipts\":\"0\",\"total\":\"1365.00\",\"amount_paid\":\"470.00\",\"ending_balance\":\"895.00\",\"ending_status\":\"BEHIND\"},{\"member_name\":\"Jose\",\"previous_balance\":\"1665.00\",\"ees_due\":\"195\",\"fines_other\":\"0\",\"approved_receipts\":\"0\",\"total\":\"1860.00\",\"amount_paid\":\"200.00\",\"ending_balance\":\"1660.00\",\"ending_status\":\"BEHIND\"},{\"member_name\":\"Neal\",\"previous_balance\":\"890.00\",\"ees_due\":\"195\",\"fines_other\":\"0\",\"approved_receipts\":\"0\",\"total\":\"1085.00\",\"amount_paid\":\"350.00\",\"ending_balance\":\"735.00\",\"ending_status\":\"BEHIND\"},{\"member_name\":\"Alex\",\"previous_balance\":\"1065.00\",\"ees_due\":\"195\",\"fines_other\":\"0\",\"approved_receipts\":\"0\",\"total\":\"1260.00\",\"amount_paid\":\"0.00\",\"ending_balance\":\"1260.00\",\"ending_status\":\"BEHIND\"},{\"member_name\":\"Todd\",\"previous_balance\":\"530.75\",\"ees_due\":\"195\",\"fines_other\":\"0\",\"approved_receipts\":\"0\",\"total\":\"725.75\",\"amount_paid\":\"175.00\",\"ending_balance\":\"550.75\",\"ending_status\":\"BEHIND\"},{\"member_name\":\"Arturo\",\"previous_balance\":\"680.00\",\"ees_due\":\"195\",\"fines_other\":\"0\",\"approved_receipts\":\"0\",\"total\":\"875.00\",\"amount_paid\":\"250.00\",\"ending_balance\":\"625.00\",\"ending_status\":\"BEHIND\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"}]', '{\"previous_balance_total\":\"8824.53\",\"ees_due_total\":\"1950.00\",\"fines_other_total\":\"0.00\",\"approved_receipts_total\":\"0.00\",\"total_total\":\"10774.53\",\"amount_paid_total\":\"3184.00\",\"ending_behind_total\":\"7688.80\",\"ending_ahead_total\":\"98.27\"}', '2026-03-09 23:11:31', '2026-03-10 00:53:02'),
(2, '', NULL, NULL, '', '[{\"member_name\":\"\",\"previous_balance\":\"275\",\"ees_due\":\"195\",\"fines_other\":\"5\",\"approved_receipts\":\"40\",\"total\":\"435.00\",\"amount_paid\":\"200\",\"ending_balance\":\"235.00\",\"ending_status\":\"BEHIND\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"},{\"member_name\":\"\",\"previous_balance\":\"\",\"ees_due\":\"\",\"fines_other\":\"\",\"approved_receipts\":\"\",\"total\":\"\",\"amount_paid\":\"\",\"ending_balance\":\"\",\"ending_status\":\"\"}]', '{\"previous_balance_total\":\"275.00\",\"ees_due_total\":\"195.00\",\"fines_other_total\":\"5.00\",\"approved_receipts_total\":\"40.00\",\"total_total\":\"435.00\",\"amount_paid_total\":\"200.00\",\"ending_behind_total\":\"235.00\",\"ending_ahead_total\":\"\"}', '2026-03-11 05:02:26', '2026-03-11 05:03:09');

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_meeting_minutes_json`
--

CREATE TABLE `oxford_house_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_member_ledger`
--

CREATE TABLE `oxford_house_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(10) NOT NULL DEFAULT '',
  `move_in_day` varchar(10) NOT NULL DEFAULT '',
  `move_in_year` varchar(10) NOT NULL DEFAULT '',
  `row_1` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_1`)),
  `row_2` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_2`)),
  `row_3` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_3`)),
  `row_4` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_4`)),
  `row_5` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_5`)),
  `row_6` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_6`)),
  `row_7` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_7`)),
  `row_8` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_8`)),
  `row_9` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_9`)),
  `row_10` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_10`)),
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date_paid` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_minutes`
--

CREATE TABLE `oxford_house_minutes` (
  `id` int(11) NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `meeting_date` varchar(50) DEFAULT NULL,
  `start_time` varchar(50) DEFAULT NULL,
  `tradition_number` varchar(50) DEFAULT NULL,
  `meeting_type_regular` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_emergency` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_interview` tinyint(1) NOT NULL DEFAULT 0,
  `minutes_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `amend_yes` tinyint(1) NOT NULL DEFAULT 0,
  `amend_no` tinyint(1) NOT NULL DEFAULT 0,
  `checking_0` varchar(50) DEFAULT NULL,
  `checking_1` varchar(50) DEFAULT NULL,
  `checking_2` varchar(50) DEFAULT NULL,
  `checking_3` varchar(50) DEFAULT NULL,
  `savings_0` varchar(50) DEFAULT NULL,
  `savings_1` varchar(50) DEFAULT NULL,
  `savings_2` varchar(50) DEFAULT NULL,
  `savings_3` varchar(50) DEFAULT NULL,
  `savings_4` varchar(50) DEFAULT NULL,
  `petty_0` varchar(50) DEFAULT NULL,
  `petty_1` varchar(50) DEFAULT NULL,
  `petty_2` varchar(50) DEFAULT NULL,
  `petty_3` varchar(50) DEFAULT NULL,
  `petty_receipts_yes` tinyint(1) NOT NULL DEFAULT 0,
  `petty_receipts_no` tinyint(1) NOT NULL DEFAULT 0,
  `treasurer_comments` text DEFAULT NULL,
  `treasurer_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `comptroller_comments` text DEFAULT NULL,
  `comptroller_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `ees_plan` longtext DEFAULT NULL,
  `coordinator_report` text DEFAULT NULL,
  `coordinator_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `housing_services_report` text DEFAULT NULL,
  `hsr_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_n` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_n` tinyint(1) NOT NULL DEFAULT 0,
  `unfinished_business` text DEFAULT NULL,
  `vacancy_updated_y` tinyint(1) NOT NULL DEFAULT 0,
  `vacancy_updated_n` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_y` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_n` tinyint(1) NOT NULL DEFAULT 0,
  `new_business` longtext DEFAULT NULL,
  `adjourn_hour` varchar(10) DEFAULT NULL,
  `adjourn_min` varchar(10) DEFAULT NULL,
  `secretary_name` varchar(255) DEFAULT NULL,
  `secretary_signature` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `roll_name_1` text DEFAULT NULL,
  `roll_y_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_2` text DEFAULT NULL,
  `roll_y_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_3` text DEFAULT NULL,
  `roll_y_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_4` text DEFAULT NULL,
  `roll_y_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_5` text DEFAULT NULL,
  `roll_y_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_6` text DEFAULT NULL,
  `roll_y_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_7` text DEFAULT NULL,
  `roll_y_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_8` text DEFAULT NULL,
  `roll_y_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_9` text DEFAULT NULL,
  `roll_y_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_10` text DEFAULT NULL,
  `roll_y_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_11` text DEFAULT NULL,
  `roll_y_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_12` text DEFAULT NULL,
  `roll_y_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_13` text DEFAULT NULL,
  `roll_y_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_14` text DEFAULT NULL,
  `roll_y_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_15` text DEFAULT NULL,
  `roll_y_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_16` text DEFAULT NULL,
  `roll_y_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_17` text DEFAULT NULL,
  `roll_y_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_18` text DEFAULT NULL,
  `roll_y_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_19` text DEFAULT NULL,
  `roll_y_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_20` text DEFAULT NULL,
  `roll_y_20` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_20` tinyint(1) NOT NULL DEFAULT 0,
  `comp_name_1` text DEFAULT NULL,
  `comp_bal_1` text DEFAULT NULL,
  `comp_name_2` text DEFAULT NULL,
  `comp_bal_2` text DEFAULT NULL,
  `comp_name_3` text DEFAULT NULL,
  `comp_bal_3` text DEFAULT NULL,
  `comp_name_4` text DEFAULT NULL,
  `comp_bal_4` text DEFAULT NULL,
  `comp_name_5` text DEFAULT NULL,
  `comp_bal_5` text DEFAULT NULL,
  `comp_name_6` text DEFAULT NULL,
  `comp_bal_6` text DEFAULT NULL,
  `comp_name_7` text DEFAULT NULL,
  `comp_bal_7` text DEFAULT NULL,
  `comp_name_8` text DEFAULT NULL,
  `comp_bal_8` text DEFAULT NULL,
  `comp_name_9` text DEFAULT NULL,
  `comp_bal_9` text DEFAULT NULL,
  `comp_name_10` text DEFAULT NULL,
  `comp_bal_10` text DEFAULT NULL,
  `comp_name_11` text DEFAULT NULL,
  `comp_bal_11` text DEFAULT NULL,
  `comp_name_12` text DEFAULT NULL,
  `comp_bal_12` text DEFAULT NULL,
  `comp_name_13` text DEFAULT NULL,
  `comp_bal_13` text DEFAULT NULL,
  `comp_name_14` text DEFAULT NULL,
  `comp_bal_14` text DEFAULT NULL,
  `comp_name_15` text DEFAULT NULL,
  `comp_bal_15` text DEFAULT NULL,
  `comp_name_16` text DEFAULT NULL,
  `comp_bal_16` text DEFAULT NULL,
  `comp_name_17` text DEFAULT NULL,
  `comp_bal_17` text DEFAULT NULL,
  `comp_name_18` text DEFAULT NULL,
  `comp_bal_18` text DEFAULT NULL,
  `comp_name_19` text DEFAULT NULL,
  `comp_bal_19` text DEFAULT NULL,
  `comp_name_20` text DEFAULT NULL,
  `comp_bal_20` text DEFAULT NULL,
  `secretary_signed_date` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `oxford_house_minutes`
--

INSERT INTO `oxford_house_minutes` (`id`, `house_name`, `meeting_date`, `start_time`, `tradition_number`, `meeting_type_regular`, `meeting_type_emergency`, `meeting_type_interview`, `minutes_accepted`, `amend_yes`, `amend_no`, `checking_0`, `checking_1`, `checking_2`, `checking_3`, `savings_0`, `savings_1`, `savings_2`, `savings_3`, `savings_4`, `petty_0`, `petty_1`, `petty_2`, `petty_3`, `petty_receipts_yes`, `petty_receipts_no`, `treasurer_comments`, `treasurer_mmsp`, `comptroller_comments`, `comptroller_mmsp`, `ees_plan`, `coordinator_report`, `coordinator_mmsp`, `housing_services_report`, `hsr_mmsp`, `narcan_kit_y`, `narcan_kit_n`, `narcan_use_y`, `narcan_use_n`, `unfinished_business`, `vacancy_updated_y`, `vacancy_updated_n`, `email_checked_y`, `email_checked_n`, `voicemail_checked_y`, `voicemail_checked_n`, `checked_in_daily_y`, `checked_in_daily_n`, `new_business`, `adjourn_hour`, `adjourn_min`, `secretary_name`, `secretary_signature`, `created_at`, `updated_at`, `roll_name_1`, `roll_y_1`, `roll_n_1`, `roll_name_2`, `roll_y_2`, `roll_n_2`, `roll_name_3`, `roll_y_3`, `roll_n_3`, `roll_name_4`, `roll_y_4`, `roll_n_4`, `roll_name_5`, `roll_y_5`, `roll_n_5`, `roll_name_6`, `roll_y_6`, `roll_n_6`, `roll_name_7`, `roll_y_7`, `roll_n_7`, `roll_name_8`, `roll_y_8`, `roll_n_8`, `roll_name_9`, `roll_y_9`, `roll_n_9`, `roll_name_10`, `roll_y_10`, `roll_n_10`, `roll_name_11`, `roll_y_11`, `roll_n_11`, `roll_name_12`, `roll_y_12`, `roll_n_12`, `roll_name_13`, `roll_y_13`, `roll_n_13`, `roll_name_14`, `roll_y_14`, `roll_n_14`, `roll_name_15`, `roll_y_15`, `roll_n_15`, `roll_name_16`, `roll_y_16`, `roll_n_16`, `roll_name_17`, `roll_y_17`, `roll_n_17`, `roll_name_18`, `roll_y_18`, `roll_n_18`, `roll_name_19`, `roll_y_19`, `roll_n_19`, `roll_name_20`, `roll_y_20`, `roll_n_20`, `comp_name_1`, `comp_bal_1`, `comp_name_2`, `comp_bal_2`, `comp_name_3`, `comp_bal_3`, `comp_name_4`, `comp_bal_4`, `comp_name_5`, `comp_bal_5`, `comp_name_6`, `comp_bal_6`, `comp_name_7`, `comp_bal_7`, `comp_name_8`, `comp_bal_8`, `comp_name_9`, `comp_bal_9`, `comp_name_10`, `comp_bal_10`, `comp_name_11`, `comp_bal_11`, `comp_name_12`, `comp_bal_12`, `comp_name_13`, `comp_bal_13`, `comp_name_14`, `comp_bal_14`, `comp_name_15`, `comp_bal_15`, `comp_name_16`, `comp_bal_16`, `comp_name_17`, `comp_bal_17`, `comp_name_18`, `comp_bal_18`, `comp_name_19`, `comp_bal_19`, `comp_name_20`, `comp_bal_20`, `secretary_signed_date`) VALUES
(1, 'RED CREEK', '03/01/26', '18:11', '7', 1, 0, 0, 1, 1, 0, '3463.38', '2840.60', '4362.48', '1941.50', '10225.07', '0', '0', '0.08', '10225.15', '9.09', '0', '20.00', '29.09', 1, 0, 'Money Received: 2/28 EES $2489.60 ** 2/27 Jesse Void #1502 $350 ** 2/28 Correction in book $1.00 ** Total Received: $2810.60\r\nApproved Expenses: 2/22 XCEL #1511 $159.36 ** 2/27 Jesse Bed #1512 $350.00 ** 2/28 Phill Rent #1513 $3500.00 ** 2/28 Paintball #1514 $90.00 ** 2/22 Pueblo Water #1510 $118.51 ** 2/28 Sam\'s #1515 $144.60 Total Spent: $4362.48\r\nUpcoming Bills: Chapter Bed Dues $350 Due on 3/13 ** Total Spent: $4362.48\r\nAudit was Completed 3/1/26 MMSP to pay Chapter Dues', 1, '2/23 Troy Paid $600.00', 1, '- Mo\r\n- Will pay on Payday\r\n\r\nFrank\r\n- Will pay according to contract\r\n\r\nTristan\r\n- Will Honor Contract\r\n- Awaiting Oxford House Scholarship\r\n\r\nCharles\r\n- Awaiting Income Tax and may pay more than EES needed\r\n\r\nJose\r\n- Continue work and will pay upon paycheck\r\n- awaiting payment from DHS\r\n- Awaiting Oxford House Scholarship\r\n\r\nNeal\r\n- Awaiting his first paycheck\r\n\r\nAlex\r\n- Awaiting his first paycheck\r\n- Awaiting JBBS funds\r\n\r\nTodd\r\n- Pledge to make a payment of $200 asap\r\n\r\nArturo\r\n- Awaiting first paycheck on 3/6/26', 'Chores and Rooms Completed and No House Supplies Needed', 1, 'House Visit at Starlight, No Audit Done, HSR Report Completed, Chapter Meeting, \r\n8 applications, 8 Contacted, 1Accepted at NorthPlace\r\nHSC meeting at Eagles Nest at 6pm', 1, 1, 0, 1, 0, 'Contracts Signed', 1, 0, 1, 0, 1, 0, 1, 0, 'UA Frank and Tristan\r\nMMSP to call the landlord to look at the electrical in the house that includes $250\r\nMMSD to Lower Jose Contract to the current EES\r\nMMSP To Remove Tristan off New Contract\r\nMMSP to irrigate the from yard\r\nTable Franks Trip to Texas', '19', '36', 'Jose D.', 'Jose D.', '2026-03-07 14:06:55', '2026-03-08 18:37:24', 'Moriah', 1, 0, 'Frank', 1, 0, 'Tristan', 1, 0, 'Troy', 1, 0, 'Charles', 1, 0, 'Jose', 1, 0, 'Neal', 1, 0, 'Alex', 1, 0, 'Todd', 1, 0, 'Arturo', 1, 0, 'Crystal', 1, 0, 'Hannah', 1, 0, 'Carl', 1, 0, 'Josey', 1, 0, 'Dalton', 1, 0, '', 0, 1, '', 0, 1, '', 0, 1, '', 0, 1, '', 0, 1, 'Moriah', '225.73', 'Frank', '1074.44', 'Tristan', '1326.28', 'Troy', '137.33', 'Charles', '1170.00', 'Jose', '1665.00', 'Neal', '890.00', 'Alex', '1065.00', 'Todd', '530.75', 'Arturo', '680.00', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '03/07/2026'),
(2, 'RED CREEK', '03/08/2028', '18:15', '8', 1, 0, 0, 1, 0, 1, '1941.50', '0', '350.00', '1591.50', '10225.15', '00', '0', '0', '10225.15', '29.09', '0', '0', '29.09', 1, 0, 'TBD: $3184.00 on 3/926\r\nApproved Expenses: 3/5 Bed Dues #1516 $350.00\r\nNo Upcoming Bills\r\nMMSP to pay upcoming bills $480.76', 1, '3/4 Frank paid $470 ** 3//6 Arturo paid $250.00 ** 3/6 Jose paid $200.00 ** 3/6 Neal paid $350.00 ** 3/7 Moriah paid $569.00 \r\n** 3/7 Tristan paid $700. ** 3/7 Todd paid $175.00 Behind: 7688.25 Ahead: +90.27', 1, 'Mo — Excused.\r\n\r\nFrank — Contract honored; 2 weeks remaining.\r\n\r\nTristan — Contract honored.\r\n\r\nTroy — Plans to make a payment of approximately $500–$600 tomorrow.\r\n\r\nCharles — Payment expected on Friday.\r\n\r\nJose — Will continue making payments on payday.\r\n\r\nNeal — Payment expected next Thursday.\r\n\r\nAlex — JBBS invoice for EES; contract honored.\r\n\r\nTodd — Payment expected Thursday or Friday, approximately $300.\r\n\r\nArturo — Payment expected Friday.', 'Laundry Soap, Paper Plates, Coffee, Chores are completed, Rooms are ok;', 1, 'No audit no summary report completed, Chapter meeting on Saturday, State meeting on the 29th at the same hotel as the convention\r\n3 applications, contacted 3, 1 interview schedule for 3/9/26', 1, 1, 0, 1, 0, 'Tristan Honored Contract\r\nJose Violated Contract - MMSP but not dismissed\r\nCharles Contract - Not Violated\r\nNeal Contract Violated - MMSP to give Grace \r\nMMSP to recind the previous contract violation\r\nBreaker and Electricity and issues of the room, stairs havent been stained or sealed, Kitchen floors are already seperating\r\nQuote for irrigation of lawn care', 1, 0, 1, 0, 1, 0, 1, 0, 'Motions\r\nMMSP to reduce the EES amount to $175.00\r\nMMSP to rewrite the contract, with revisions to wording and a full re-evaluation.\r\nMMSP that the contract reflect the EES amount plus 10%\r\nMMSP that the motion be worded to reflect payment of the current EES amount plus the additional 10%\r\nMMSP to reimburse Tristan $48.42 toward EES for the knives\r\nMMSP allowing Frank to take Delano to Texas\r\nMMSP allowing Jose to remain past curfew to drive Frank to the airport\r\nMMSP confirming that Frank\'s allotted overnights will not be violated\r\n\r\nUAs Conducted this week was\r\nArturo and Charles', '20', '06', 'Jose D.', 'Jose D.', '2026-03-08 17:15:43', '2026-03-10 02:01:44', 'Moriah', 1, 0, 'Frank', 1, 0, 'Tristan', 1, 0, 'Troy', 1, 0, 'Charles', 1, 0, 'Jose', 1, 0, 'Neal', 1, 0, 'Alex', 1, 0, 'Todd', 1, 0, 'Arturo', 1, 0, '', 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, 'Moriah', '+90.27', 'Frank', '809.44', 'Tristan', '821.28', 'Troy', '332.33', 'Charles', '895.00', 'Jose', '1660.00', 'Neal', '735.00', 'Alex', '1260.00', 'Todd', '550.00', 'Arturo', '625.00', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '03/09/2026');

-- --------------------------------------------------------

--
-- Table structure for table `oxford_interview_minutes`
--

CREATE TABLE `oxford_interview_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `interview_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `interview_date` date DEFAULT NULL,
  `interviewer_name` varchar(255) NOT NULL DEFAULT '',
  `contact_phone` varchar(100) NOT NULL DEFAULT '',
  `outcome_status` varchar(100) NOT NULL DEFAULT '',
  `vote_percent` varchar(50) NOT NULL DEFAULT '',
  `move_in_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `intro_notes` mediumtext DEFAULT NULL,
  `interviewer_do_notes` mediumtext DEFAULT NULL,
  `interviewer_dont_notes` mediumtext DEFAULT NULL,
  `closing_notes` mediumtext DEFAULT NULL,
  `roll_name_1` varchar(255) NOT NULL DEFAULT '',
  `roll_present_1` varchar(20) NOT NULL DEFAULT '',
  `roll_name_2` varchar(255) NOT NULL DEFAULT '',
  `roll_present_2` varchar(20) NOT NULL DEFAULT '',
  `roll_name_3` varchar(255) NOT NULL DEFAULT '',
  `roll_present_3` varchar(20) NOT NULL DEFAULT '',
  `roll_name_4` varchar(255) NOT NULL DEFAULT '',
  `roll_present_4` varchar(20) NOT NULL DEFAULT '',
  `roll_name_5` varchar(255) NOT NULL DEFAULT '',
  `roll_present_5` varchar(20) NOT NULL DEFAULT '',
  `roll_name_6` varchar(255) NOT NULL DEFAULT '',
  `roll_present_6` varchar(20) NOT NULL DEFAULT '',
  `roll_name_7` varchar(255) NOT NULL DEFAULT '',
  `roll_present_7` varchar(20) NOT NULL DEFAULT '',
  `roll_name_8` varchar(255) NOT NULL DEFAULT '',
  `roll_present_8` varchar(20) NOT NULL DEFAULT '',
  `roll_name_9` varchar(255) NOT NULL DEFAULT '',
  `roll_present_9` varchar(20) NOT NULL DEFAULT '',
  `roll_name_10` varchar(255) NOT NULL DEFAULT '',
  `roll_present_10` varchar(20) NOT NULL DEFAULT '',
  `roll_name_11` varchar(255) NOT NULL DEFAULT '',
  `roll_present_11` varchar(20) NOT NULL DEFAULT '',
  `roll_name_12` varchar(255) NOT NULL DEFAULT '',
  `roll_present_12` varchar(20) NOT NULL DEFAULT '',
  `q1` mediumtext DEFAULT NULL,
  `q2` mediumtext DEFAULT NULL,
  `q3` mediumtext DEFAULT NULL,
  `q4` mediumtext DEFAULT NULL,
  `q5` mediumtext DEFAULT NULL,
  `q6` mediumtext DEFAULT NULL,
  `q7` mediumtext DEFAULT NULL,
  `q8` mediumtext DEFAULT NULL,
  `q9` mediumtext DEFAULT NULL,
  `q10` mediumtext DEFAULT NULL,
  `q11` mediumtext DEFAULT NULL,
  `q12` mediumtext DEFAULT NULL,
  `q13` mediumtext DEFAULT NULL,
  `q14` mediumtext DEFAULT NULL,
  `q15` mediumtext DEFAULT NULL,
  `q16` mediumtext DEFAULT NULL,
  `q17` mediumtext DEFAULT NULL,
  `q18` mediumtext DEFAULT NULL,
  `q19` mediumtext DEFAULT NULL,
  `q20` mediumtext DEFAULT NULL,
  `q21` mediumtext DEFAULT NULL,
  `q22` mediumtext DEFAULT NULL,
  `q23` mediumtext DEFAULT NULL,
  `q24` mediumtext DEFAULT NULL,
  `q25` mediumtext DEFAULT NULL,
  `q26` mediumtext DEFAULT NULL,
  `q27` mediumtext DEFAULT NULL,
  `q28` mediumtext DEFAULT NULL,
  `q29` mediumtext DEFAULT NULL,
  `q30` mediumtext DEFAULT NULL,
  `q31` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_member_financial_contracts`
--

CREATE TABLE `oxford_member_financial_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(100) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `total_amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgement_name` varchar(255) NOT NULL DEFAULT '',
  `signature_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(100) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `scanned_contract` varchar(255) NOT NULL DEFAULT '',
  `contract_stamp` varchar(50) NOT NULL DEFAULT '',
  `contract_stamp_at` datetime DEFAULT NULL,
  `contract_stamp_by_ip` varchar(64) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `oxford_member_financial_contracts`
--

INSERT INTO `oxford_member_financial_contracts` (`id`, `member_name`, `house_name`, `contract_date`, `contract_length`, `total_amount_owed`, `term_1`, `term_2`, `term_3`, `term_4`, `acknowledgement_name`, `signature_name`, `signature_date`, `president_name`, `treasurer_name`, `coordinator_name`, `member_1_name`, `member_2_name`, `secretary_name`, `comptroller_name`, `hs_representative_name`, `member_3_name`, `member_4_name`, `scanned_contract`, `contract_stamp`, `contract_stamp_at`, `contract_stamp_by_ip`, `created_at`, `updated_at`) VALUES
(1, 'Jose Davila', 'RED CREEK', '03/09/2026', 'Until EES is Under 2 weeks worth of EES', 1660.00, 'Pay 2 weeks of current EES plus 10% of every 2 weeks before house meeting starting date of signature no later than 2 weeks from the date of this written contract', 'This contract is to remain active until balance is below 2 weeks worth of current EES', 'Failure to pay the EES every 2 weeks will result in a VOTE for dismissal for disruptive behavior by not paying EES', 'Must attend 3 recovery meetings per week online or in person', 'Jose Davila', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '2026-03-09 22:30:36', '2026-03-11 03:22:21'),
(2, 'Neal Helmbrecht', 'RED CREEK', '03/09/2026', 'Until EES is Under 2 weeks worth of EES', 735.00, 'Pay 2 weeks of current EES plus 10% every 2 weeks before house meeting starting date of signature but not to exceed 2 weeks from the date of this written contract', 'This contract is to remain active until balance is below 2 weeks worth of current EES', 'Failure to pay the EES every 2 weeks will result in a VOTE for dismissal for disruptive behavior by not paying EES', 'Must attend 3 recovery meetings per week online or in person', 'Neal Helmbrecht', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '2026-03-10 05:39:25', '2026-03-10 05:50:31'),
(3, 'Charles Parker', 'RED CREEK', '03/09/2026', 'Until EES is Under 2 weeks worth of EES', 895.00, 'Pay 2 weeks of current EES plus 10% every 2 weeks before house meeting starting date of signature but not to exceed 2 weeks from the date of this written contract', 'This contract is to remain active until the balance is below 2 weeks worth of current EES', 'Failure to pay the EES every 2 weeks will result in a VOTE for dismissal for disruptive behavior by not paying EES', 'Must attend 3 recovery meetings per week either online or in-person', 'Charles Parker', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '2026-03-10 05:45:16', '2026-03-10 05:50:39'),
(4, 'Alex Encinias', 'RED CREEK', '03/09/2026', 'Until EES is Under 2 weeks worth of EES', 1260.00, 'Pay 2 weeks of current EES plus 10% every 2 weeks before house meeting starting date of signature but not to exceed 2 weeks from the date of this written contract', 'This contract is to remain active until the balance is below 2 weeks worth of current EES', 'Failure to pay the EES every 2 weeks will result in a VOTE for dismissal for disruptive behavior by not paying EES', 'Must attend 3 recovery meetings per week either online or in-person', 'Alex Encinias', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '2026-03-10 05:49:34', '2026-03-10 05:53:35'),
(5, 'Todd Weber', 'RED CREEK', '03/09/2026', 'Until EES is Under 2 weeks worth of EES', 550.00, 'Pay 2 weeks of current EES plus 10% every 2 weeks before house meeting starting date of signature but not to exceed 2 weeks from the date of this written contract', 'This contract is to remain active until the balance is below 2 weeks worth of current EES', 'Failure to pay the EES every 2 weeks will result in a VOTE for dismissal for disruptive behavior by not paying EES', 'Must attend 3 recovery meetings per week either online or in-person', 'Todd Weber', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '2026-03-10 05:52:46', '2026-03-10 05:58:06'),
(6, 'Arturo Arzate', 'RED CREEK', '03/09/2026', 'Until EES is Under 2 weeks worth of EES', 625.00, 'Pay 2 weeks of current EES plus 10% every 2 weeks before house meeting starting date of signature but not to exceed 2 weeks from the date of this written contract', 'This contract is to remain active until the balance is below 2 weeks worth of current EES', 'Failure to pay the EES every 2 weeks will result in a VOTE for dismissal for disruptive behavior by not paying EES', 'Must attend 3 recovery meetings per week either online or in-person', 'Arturo Arzate', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '2026-03-10 05:58:48', '2026-03-10 05:59:22'),
(7, 'Tristan Garnett', 'RED CREEK', '03/09/2026', 'Until EES is Under 2 weeks worth of EES', 821.28, 'Pay 2 weeks of current EES plus 10% every 2 weeks before house meeting starting date of signature but not to exceed 2 weeks from the date of this written contract', 'This contract is to remain active until the balance is below 2 weeks worth of current EES', 'Failure to pay the EES every 2 weeks will result in a VOTE for dismissal for disruptive behavior by not paying EES', 'Must attend 3 recovery meetings per week either online or in-person', 'Tristan Garnett', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '2026-03-10 06:00:12', '2026-03-10 06:01:27'),
(8, 'Frank Ayala', 'RED CREEK', '03/09/2026', 'Until EES is Under 2 weeks worth of EES', 809.44, 'Pay 2 weeks of current EES plus 10% every 2 weeks before house meeting starting date of signature but not to exceed 2 weeks from the date of this written contract', 'This contract is to remain active until the balance is below 2 weeks worth of current EES', 'Failure to pay the EES every 2 weeks will result in a VOTE for dismissal for disruptive behavior by not paying EES', 'Must attend 3 recovery meetings per week either online or in-person', 'Frank Ayala', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '2026-03-10 06:01:45', '2026-03-10 06:02:27'),
(9, '', '', '', '', 0.00, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '2026-03-11 03:13:41', '2026-03-11 03:13:41');

-- --------------------------------------------------------

--
-- Table structure for table `oxford_new_member_packets`
--

CREATE TABLE `oxford_new_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `packet_date` date DEFAULT NULL,
  `check_membership_application` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_manual` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_guidelines` tinyint(1) NOT NULL DEFAULT 0,
  `check_membership_agreement` tinyint(1) NOT NULL DEFAULT 0,
  `check_plan_for_recovery` tinyint(1) NOT NULL DEFAULT 0,
  `check_relapse_contingency` tinyint(1) NOT NULL DEFAULT 0,
  `check_medical_release` tinyint(1) NOT NULL DEFAULT 0,
  `check_property_list` tinyint(1) NOT NULL DEFAULT 0,
  `member_initials_1` varchar(20) NOT NULL DEFAULT '',
  `president_initials_1` varchar(20) NOT NULL DEFAULT '',
  `member_initials_2` varchar(20) NOT NULL DEFAULT '',
  `president_initials_2` varchar(20) NOT NULL DEFAULT '',
  `member_initials_3` varchar(20) NOT NULL DEFAULT '',
  `president_initials_3` varchar(20) NOT NULL DEFAULT '',
  `member_initials_4` varchar(20) NOT NULL DEFAULT '',
  `president_initials_4` varchar(20) NOT NULL DEFAULT '',
  `member_initials_5` varchar(20) NOT NULL DEFAULT '',
  `president_initials_5` varchar(20) NOT NULL DEFAULT '',
  `member_initials_6` varchar(20) NOT NULL DEFAULT '',
  `president_initials_6` varchar(20) NOT NULL DEFAULT '',
  `member_initials_7` varchar(20) NOT NULL DEFAULT '',
  `president_initials_7` varchar(20) NOT NULL DEFAULT '',
  `member_initials_8` varchar(20) NOT NULL DEFAULT '',
  `president_initials_8` varchar(20) NOT NULL DEFAULT '',
  `member_signature_1` varchar(255) NOT NULL DEFAULT '',
  `member_signature_date_1` date DEFAULT NULL,
  `president_signature_1` varchar(255) NOT NULL DEFAULT '',
  `president_signature_date_1` date DEFAULT NULL,
  `membership_agreement_text` longtext DEFAULT NULL,
  `agreement_member_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_date` date DEFAULT NULL,
  `agreement_president_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_date` date DEFAULT NULL,
  `plan_name` varchar(255) NOT NULL DEFAULT '',
  `plan_text` longtext DEFAULT NULL,
  `aftercare_program` longtext DEFAULT NULL,
  `has_sponsor` varchar(10) NOT NULL DEFAULT '',
  `sponsor_by_date` date DEFAULT NULL,
  `meetings_per_week` varchar(50) NOT NULL DEFAULT '',
  `meeting_types` varchar(255) NOT NULL DEFAULT '',
  `plan_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_signature_date` date DEFAULT NULL,
  `plan_president_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_president_date` date DEFAULT NULL,
  `relapse_name` varchar(255) NOT NULL DEFAULT '',
  `relapse_family` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_friend` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_detox` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other_text` varchar(255) NOT NULL DEFAULT '',
  `relapse_details` longtext DEFAULT NULL,
  `notify_rows_json` longtext DEFAULT NULL,
  `pickup_rows_json` longtext DEFAULT NULL,
  `relapse_member_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_member_date` date DEFAULT NULL,
  `relapse_president_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_president_date` date DEFAULT NULL,
  `relapse_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_witness_date` date DEFAULT NULL,
  `medical_name` varchar(255) NOT NULL DEFAULT '',
  `physician_name` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(50) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance_info` varchar(255) NOT NULL DEFAULT '',
  `allergies` longtext DEFAULT NULL,
  `medications` longtext DEFAULT NULL,
  `medical_history` longtext DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `blood_type` varchar(20) NOT NULL DEFAULT '',
  `medical_contacts_json` longtext DEFAULT NULL,
  `medical_signature` varchar(255) NOT NULL DEFAULT '',
  `medical_date` date DEFAULT NULL,
  `property_name` varchar(255) NOT NULL DEFAULT '',
  `property_move_in_date` date DEFAULT NULL,
  `property_rows_json` longtext DEFAULT NULL,
  `uploaded_copy_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_path` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_mime` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_red_creek_member_packets`
--

CREATE TABLE `oxford_red_creek_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `signature_date` date DEFAULT NULL,
  `refund_move_in_date` date DEFAULT NULL,
  `new_member_signature` varchar(255) NOT NULL DEFAULT '',
  `new_member_signature_date` date DEFAULT NULL,
  `president_hsr_signature` varchar(255) NOT NULL DEFAULT '',
  `president_hsr_signature_date` date DEFAULT NULL,
  `expectations_signature` varchar(255) NOT NULL DEFAULT '',
  `expectations_signature_date` date DEFAULT NULL,
  `medication_signature` varchar(255) NOT NULL DEFAULT '',
  `medication_signature_date` date DEFAULT NULL,
  `emergency_name` varchar(255) NOT NULL DEFAULT '',
  `emergency_age` varchar(50) NOT NULL DEFAULT '',
  `emergency_dob` varchar(50) NOT NULL DEFAULT '',
  `blood_type` varchar(50) NOT NULL DEFAULT '',
  `primary_physician` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(100) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance` varchar(255) NOT NULL DEFAULT '',
  `allergies` text DEFAULT NULL,
  `medications` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `contact1_name` varchar(255) NOT NULL DEFAULT '',
  `contact1_phone` varchar(100) NOT NULL DEFAULT '',
  `contact2_name` varchar(255) NOT NULL DEFAULT '',
  `contact2_phone` varchar(100) NOT NULL DEFAULT '',
  `contact3_name` varchar(255) NOT NULL DEFAULT '',
  `contact3_phone` varchar(100) NOT NULL DEFAULT '',
  `property_items` longtext DEFAULT NULL,
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `property_signature` varchar(255) NOT NULL DEFAULT '',
  `property_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `property_date` date DEFAULT NULL,
  `property_removed_date` date DEFAULT NULL,
  `property_removed_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `move_in_fee` decimal(10,2) DEFAULT 250.00,
  `other_charge` decimal(10,2) DEFAULT 0.00,
  `total_due` decimal(10,2) DEFAULT NULL,
  `scan_path` varchar(500) NOT NULL DEFAULT '',
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oxford_red_creek_member_packets`
--

INSERT INTO `oxford_red_creek_member_packets` (`id`, `member_name`, `move_in_date`, `signature_date`, `refund_move_in_date`, `new_member_signature`, `new_member_signature_date`, `president_hsr_signature`, `president_hsr_signature_date`, `expectations_signature`, `expectations_signature_date`, `medication_signature`, `medication_signature_date`, `emergency_name`, `emergency_age`, `emergency_dob`, `blood_type`, `primary_physician`, `physician_phone`, `hospital_clinic`, `insurance`, `allergies`, `medications`, `medical_history`, `contact1_name`, `contact1_phone`, `contact2_name`, `contact2_phone`, `contact3_name`, `contact3_phone`, `property_items`, `property_owner_name`, `property_signature`, `property_witness_signature`, `property_date`, `property_removed_date`, `property_removed_witness_signature`, `ees_amount`, `move_in_fee`, `other_charge`, `total_due`, `scan_path`, `scan_original_name`, `created_at`, `updated_at`) VALUES
(1, '', NULL, NULL, NULL, '', NULL, '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]', '', '', '', NULL, NULL, '', 195.00, 250.00, NULL, 445.00, '', '', '2026-03-11 07:00:44', '2026-03-11 07:00:44');

-- --------------------------------------------------------

--
-- Table structure for table `oxford_residency_forms`
--

CREATE TABLE `oxford_residency_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) DEFAULT '',
  `letter_date` varchar(20) DEFAULT '',
  `house_name` varchar(255) DEFAULT '',
  `accepted_date` varchar(20) DEFAULT '',
  `address_line` varchar(255) DEFAULT '',
  `city_state_zip` varchar(255) DEFAULT '',
  `move_in_fee` decimal(10,2) DEFAULT 0.00,
  `weekly_rent` decimal(10,2) DEFAULT 0.00,
  `president_contact` varchar(255) DEFAULT '',
  `president_name` varchar(255) DEFAULT '',
  `president_signature` varchar(255) DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_shopping_lists`
--

CREATE TABLE `oxford_shopping_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `shopping_date` date DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Oxford House Shopping List',
  `items_json` longtext NOT NULL,
  `checked_json` longtext NOT NULL,
  `total_checked` int(11) NOT NULL DEFAULT 0,
  `total_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_items` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy_path` varchar(500) DEFAULT NULL,
  `uploaded_copy_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oxford_shopping_lists`
--

INSERT INTO `oxford_shopping_lists` (`id`, `shopping_date`, `title`, `items_json`, `checked_json`, `total_checked`, `total_quantity`, `total_items`, `uploaded_copy_path`, `uploaded_copy_name`, `created_at`, `updated_at`) VALUES
(1, '2026-03-11', 'Oxford House Shopping List', '{\"all_purpose_cleaner\":\"\",\"glass_cleaner\":\"\",\"floor_cleaner\":\"\",\"kitchen_cleaner\":\"\",\"bathroom_cleaner\":\"\",\"toilet_bowl_cleaner\":\"\",\"carpet_powder\":\"\",\"wood_polish\":\"\",\"gloves\":\"\",\"laundry_soap\":\"\",\"fabric_softener\":\"\",\"dryer_sheets\":\"\",\"bleach\":\"\",\"stain_remover\":\"\",\"starch\":\"\",\"toilet_paper\":\"\",\"paper_towels\":\"\",\"kleenex\":\"\",\"napkins\":\"\",\"paper_plates\":\"\",\"plastic_ware\":\"\",\"large_trash_bags\":\"\",\"small_trash_bags\":\"\",\"sandwich_bags\":\"\",\"freezer_bags\":\"\",\"aluminum_foil\":\"\",\"plastic_wrap\":\"\",\"aspirin_advil\":\"\",\"band_aids\":\"\",\"light_bulbs\":\"\",\"salt\":\"\",\"pepper\":\"\",\"non_stick_spray\":\"\",\"cooking_oil\":\"\",\"air_freshener\":\"\",\"coffee\":\"\",\"filters\":\"\",\"sugar\":\"\",\"sweetener\":\"\",\"creamer\":\"\",\"rags\":\"\",\"sponges\":\"\",\"scrub_pads\":\"\",\"vacuum_bag_filter\":\"\",\"ac_filter\":\"\",\"hand_soap\":\"\",\"dish_soap\":\"\",\"dishwasher_soap\":\"\",\"other_1\":\"\",\"other_2\":\"\",\"other_3\":\"\",\"other_4\":\"\",\"other_5\":\"\",\"other_6\":\"\"}', '{\"all_purpose_cleaner\":0,\"glass_cleaner\":0,\"floor_cleaner\":0,\"kitchen_cleaner\":0,\"bathroom_cleaner\":0,\"toilet_bowl_cleaner\":0,\"carpet_powder\":0,\"wood_polish\":0,\"gloves\":0,\"laundry_soap\":0,\"fabric_softener\":0,\"dryer_sheets\":0,\"bleach\":0,\"stain_remover\":0,\"starch\":0,\"toilet_paper\":0,\"paper_towels\":0,\"kleenex\":0,\"napkins\":0,\"paper_plates\":0,\"plastic_ware\":0,\"large_trash_bags\":0,\"small_trash_bags\":0,\"sandwich_bags\":0,\"freezer_bags\":0,\"aluminum_foil\":0,\"plastic_wrap\":0,\"aspirin_advil\":0,\"band_aids\":0,\"light_bulbs\":0,\"salt\":0,\"pepper\":0,\"non_stick_spray\":0,\"cooking_oil\":0,\"air_freshener\":0,\"coffee\":0,\"filters\":0,\"sugar\":0,\"sweetener\":0,\"creamer\":0,\"rags\":0,\"sponges\":0,\"scrub_pads\":0,\"vacuum_bag_filter\":0,\"ac_filter\":0,\"hand_soap\":0,\"dish_soap\":0,\"dishwasher_soap\":0,\"other_1\":0,\"other_2\":0,\"other_3\":0,\"other_4\":0,\"other_5\":0,\"other_6\":0}', 0, 0.00, 0, NULL, NULL, '2026-03-11 07:39:17', '2026-03-11 07:39:18');

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledgers`
--

CREATE TABLE `petty_cash_ledgers` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `ledger_date` date DEFAULT NULL,
  `beginning_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `petty_cash_ledgers`
--

INSERT INTO `petty_cash_ledgers` (`id`, `house_name`, `ledger_date`, `beginning_balance`, `created_at`, `updated_at`) VALUES
(1, 'RED CREEK', '2026-03-09', 29.09, '2026-03-09 22:14:14', '2026-03-09 22:14:28');

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledger_rows`
--

CREATE TABLE `petty_cash_ledger_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `ledger_id` int(10) UNSIGNED NOT NULL,
  `row_index` int(11) NOT NULL,
  `txn_date` varchar(50) NOT NULL DEFAULT '',
  `products_purchased` varchar(255) NOT NULL DEFAULT '',
  `vendor` varchar(255) NOT NULL DEFAULT '',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reimbursement_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `petty_cash_ledger_rows`
--

INSERT INTO `petty_cash_ledger_rows` (`id`, `ledger_id`, `row_index`, `txn_date`, `products_purchased`, `vendor`, `amount`, `reimbursement_amount`, `balance`, `created_at`, `updated_at`) VALUES
(121, 1, 0, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(122, 1, 1, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(123, 1, 2, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(124, 1, 3, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(125, 1, 4, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(126, 1, 5, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(127, 1, 6, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(128, 1, 7, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(129, 1, 8, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(130, 1, 9, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(131, 1, 10, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(132, 1, 11, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(133, 1, 12, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(134, 1, 13, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(135, 1, 14, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(136, 1, 15, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(137, 1, 16, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(138, 1, 17, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(139, 1, 18, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(140, 1, 19, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(141, 1, 20, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(142, 1, 21, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(143, 1, 22, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35'),
(144, 1, 23, '', '', '', 0.00, 0.00, 29.09, '2026-03-09 22:14:35', '2026-03-09 22:14:35');

-- --------------------------------------------------------

--
-- Table structure for table `safety_inspection_checklists`
--

CREATE TABLE `safety_inspection_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `inspection_date` date DEFAULT NULL,
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `checklist_json` longtext NOT NULL,
  `satisfactory_total` int(11) NOT NULL DEFAULT 0,
  `unsatisfactory_total` int(11) NOT NULL DEFAULT 0,
  `completed_total` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy` varchar(500) DEFAULT NULL,
  `original_upload_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `safety_inspection_checklists`
--

INSERT INTO `safety_inspection_checklists` (`id`, `house_name`, `inspection_date`, `inspector_name`, `checklist_json`, `satisfactory_total`, `unsatisfactory_total`, `completed_total`, `uploaded_copy`, `original_upload_name`, `created_at`, `updated_at`) VALUES
(1, '', NULL, '', '{\"1\":{\"satisfactory\":\"\",\"unsatisfactory\":\"1\",\"when_completed\":\"\",\"notes\":\"\"},\"2\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"3\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"4\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"6\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"7\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"8\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"9\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"10\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"11\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"13\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"14\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"15\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"16\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"17\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"18\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"19\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"20\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"22\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"23\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"24\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"26\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"},\"27\":{\"satisfactory\":\"\",\"unsatisfactory\":\"\",\"when_completed\":\"\",\"notes\":\"\"}}', 0, 1, 0, NULL, NULL, '2026-03-11 08:18:32', '2026-03-11 08:18:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_checklist_key` (`checklist_key`);

--
-- Indexes for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_meeting_date` (`meeting_date`);

--
-- Indexes for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_dates` (`week_start`,`week_end`);

--
-- Indexes for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_tour_date` (`tour_date`);

--
-- Indexes for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `house_name` (`house_name`);

--
-- Indexes for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_label` (`schedule_label`);

--
-- Indexes for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`);

--
-- Indexes for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_name_idx` (`tenant_name`),
  ADD KEY `updated_at_idx` (`updated_at`);

--
-- Indexes for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resident_name` (`resident_name`),
  ADD KEY `idx_sheet_date` (`sheet_date`);

--
-- Indexes for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sheet_id` (`sheet_id`),
  ADD KEY `idx_row_number` (`row_number`);

--
-- Indexes for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_form_date` (`form_date`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_contract_date` (`contract_date`);

--
-- Indexes for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date_completed` (`date_completed`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- Indexes for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_dates` (`house_name`,`date_from`,`date_to`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_date_from` (`date_from`),
  ADD KEY `idx_date_to` (`date_to`);

--
-- Indexes for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_start` (`week_start`),
  ADD KEY `idx_week_end` (`week_end`);

--
-- Indexes for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`),
  ADD KEY `idx_house_date` (`house_name`,`meeting_date`);

--
-- Indexes for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_member_date_house` (`member_name`,`contract_date`,`house_name`);

--
-- Indexes for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_member` (`house_name`,`member_name`);

--
-- Indexes for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_shopping_date` (`shopping_date`);

--
-- Indexes for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_date` (`house_name`,`ledger_date`);

--
-- Indexes for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_petty_cash_ledger_rows_ledger` (`ledger_id`);

--
-- Indexes for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inspection_date` (`inspection_date`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  ADD CONSTRAINT `fk_medication_sheet` FOREIGN KEY (`sheet_id`) REFERENCES `medication_count_sheets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  ADD CONSTRAINT `fk_petty_cash_ledger_rows_ledger` FOREIGN KEY (`ledger_id`) REFERENCES `petty_cash_ledgers` (`id`) ON DELETE CASCADE;
--
-- Database: `starlite`
--
CREATE DATABASE IF NOT EXISTS `starlite` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `starlite`;

-- --------------------------------------------------------

--
-- Table structure for table `bedroom_essentials_checklists`
--

CREATE TABLE `bedroom_essentials_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `checklist_key` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Bedroom Essentials – Checklist',
  `items_json` longtext NOT NULL,
  `dark_mode` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chapter_meeting_minutes`
--

CREATE TABLE `chapter_meeting_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `meeting_date` varchar(50) NOT NULL,
  `form_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ees_member_ledger`
--

CREATE TABLE `ees_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(2) NOT NULL DEFAULT '',
  `move_in_day` varchar(2) NOT NULL DEFAULT '',
  `move_in_year` varchar(4) NOT NULL DEFAULT '',
  `ledger_rows` longtext DEFAULT NULL,
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_ledger_records`
--

CREATE TABLE `house_ledger_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `notes` mediumtext DEFAULT NULL,
  `rows_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_tour_forms`
--

CREATE TABLE `house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `tour_date` date DEFAULT NULL,
  `tour_time` varchar(20) NOT NULL DEFAULT '',
  `smoking_area` varchar(10) NOT NULL DEFAULT '',
  `notes` text DEFAULT NULL,
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `signature` varchar(255) NOT NULL DEFAULT '',
  `items_json` longtext DEFAULT NULL,
  `section_totals_json` text DEFAULT NULL,
  `grand_total` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_houses`
--

CREATE TABLE `house_visit_houses` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(150) NOT NULL,
  `meeting_day` varchar(20) NOT NULL DEFAULT '',
  `meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_reports`
--

CREATE TABLE `house_visit_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(100) NOT NULL DEFAULT '',
  `president` varchar(255) NOT NULL DEFAULT '',
  `secretary` varchar(255) NOT NULL DEFAULT '',
  `treasurer` varchar(255) NOT NULL DEFAULT '',
  `comptroller` varchar(255) NOT NULL DEFAULT '',
  `coordinator` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep` varchar(255) NOT NULL DEFAULT '',
  `overall_appearance` varchar(10) NOT NULL DEFAULT '',
  `overall_appearance_comments` text DEFAULT NULL,
  `members_behind_ees` decimal(10,2) DEFAULT NULL,
  `total_amount_owed` decimal(12,2) DEFAULT NULL,
  `rent_paid_monthly` decimal(12,2) DEFAULT NULL,
  `ees_paid_weekly` decimal(12,2) DEFAULT NULL,
  `utilities_monthly` decimal(12,2) DEFAULT NULL,
  `house_business_meeting` varchar(10) NOT NULL DEFAULT '',
  `house_business_comments` text DEFAULT NULL,
  `rating_reading_traditions` varchar(10) NOT NULL DEFAULT '',
  `rating_reading_minutes` varchar(10) NOT NULL DEFAULT '',
  `rating_treasurer_report` varchar(10) NOT NULL DEFAULT '',
  `rating_comptroller_report` varchar(10) NOT NULL DEFAULT '',
  `rating_coordinator_report` varchar(10) NOT NULL DEFAULT '',
  `rating_maintains_guidelines` varchar(10) NOT NULL DEFAULT '',
  `rating_handling_business` varchar(10) NOT NULL DEFAULT '',
  `rating_organization_order` varchar(10) NOT NULL DEFAULT '',
  `financial_comments` text DEFAULT NULL,
  `first_visit_date` varchar(50) NOT NULL DEFAULT '',
  `narcan_present` varchar(10) NOT NULL DEFAULT '',
  `narcan_trained` varchar(10) NOT NULL DEFAULT '',
  `follow_up_visit_dates` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep_signature` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_schedules`
--

CREATE TABLE `house_visit_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `schedule_label` varchar(150) NOT NULL,
  `base_month` varchar(7) NOT NULL DEFAULT '',
  `month_count` int(11) NOT NULL DEFAULT 1,
  `repeat_cycle` tinyint(1) NOT NULL DEFAULT 0,
  `step_size` int(11) NOT NULL DEFAULT 1,
  `month_label` varchar(50) NOT NULL,
  `month_index` int(11) NOT NULL DEFAULT 1,
  `visiting_house` varchar(150) NOT NULL,
  `host_house` varchar(150) NOT NULL,
  `host_meeting_day` varchar(20) NOT NULL DEFAULT '',
  `host_meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `housing_service_representative_reports`
--

CREATE TABLE `housing_service_representative_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `week_from` varchar(100) NOT NULL DEFAULT '',
  `week_to` varchar(100) NOT NULL DEFAULT '',
  `house_visit` text DEFAULT NULL,
  `audit_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `audit_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `next_chapter_meeting` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `next_state_meeting` text DEFAULT NULL,
  `number_of_applications` text DEFAULT NULL,
  `number_contacted_plan` longtext DEFAULT NULL,
  `new_members` longtext DEFAULT NULL,
  `interviews_setup` longtext DEFAULT NULL,
  `chapter_news` longtext DEFAULT NULL,
  `chapter_meeting_recap` longtext DEFAULT NULL,
  `upcoming_unity` longtext DEFAULT NULL,
  `upcoming_presentations` longtext DEFAULT NULL,
  `hsr_name` varchar(255) NOT NULL DEFAULT '',
  `report_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hsc_meeting_minutes_json`
--

CREATE TABLE `hsc_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `landlord_verification_forms`
--

CREATE TABLE `landlord_verification_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_name` varchar(255) NOT NULL DEFAULT '',
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `landlord_manager_name` varchar(255) NOT NULL DEFAULT '',
  `manager_street` varchar(255) NOT NULL DEFAULT '',
  `manager_city` varchar(150) NOT NULL DEFAULT '',
  `manager_state` varchar(50) NOT NULL DEFAULT '',
  `manager_zip` varchar(25) NOT NULL DEFAULT '',
  `manager_county` varchar(150) NOT NULL DEFAULT '',
  `manager_phone` varchar(50) NOT NULL DEFAULT '',
  `manager_email` varchar(255) NOT NULL DEFAULT '',
  `rental_street` varchar(255) NOT NULL DEFAULT '',
  `rental_apt_lot` varchar(100) NOT NULL DEFAULT '',
  `rental_city` varchar(150) NOT NULL DEFAULT '',
  `rental_state` varchar(50) NOT NULL DEFAULT '',
  `rental_zip` varchar(25) NOT NULL DEFAULT '',
  `rental_county` varchar(150) NOT NULL DEFAULT '',
  `bedrooms` varchar(20) NOT NULL DEFAULT '',
  `lease_start_date` date DEFAULT NULL,
  `lease_end_date` date DEFAULT NULL,
  `monthly_rent_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `next_payment_due_date` date DEFAULT NULL,
  `last_payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `last_payment_date` date DEFAULT NULL,
  `tenant_in_arrears` tinyint(1) NOT NULL DEFAULT 0,
  `amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `arrears_period_from` date DEFAULT NULL,
  `arrears_period_to` date DEFAULT NULL,
  `receiving_other_assistance` tinyint(1) NOT NULL DEFAULT 0,
  `other_assistance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_assistance_period` varchar(100) NOT NULL DEFAULT '',
  `payment_method` varchar(20) NOT NULL DEFAULT 'check',
  `check_payable_to` varchar(255) NOT NULL DEFAULT '',
  `send_to_landlord_address` tinyint(1) NOT NULL DEFAULT 1,
  `alternative_address` text DEFAULT NULL,
  `cert_name` varchar(255) NOT NULL DEFAULT '',
  `cert_title` varchar(255) NOT NULL DEFAULT '',
  `cert_signature` varchar(255) NOT NULL DEFAULT '',
  `cert_date` date DEFAULT NULL,
  `calc_balance_remaining` decimal(10,2) NOT NULL DEFAULT 0.00,
  `calc_total_assistance_gap` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheets`
--

CREATE TABLE `medication_count_sheets` (
  `id` int(10) UNSIGNED NOT NULL,
  `resident_name` varchar(255) NOT NULL,
  `sheet_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheet_rows`
--

CREATE TABLE `medication_count_sheet_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `sheet_id` int(10) UNSIGNED NOT NULL,
  `row_number` int(11) NOT NULL,
  `entry_date` varchar(50) DEFAULT NULL,
  `medication_name` varchar(255) DEFAULT NULL,
  `dosage` varchar(255) DEFAULT NULL,
  `frequency` varchar(255) DEFAULT NULL,
  `previous_count` varchar(100) DEFAULT NULL,
  `current_count` varchar(100) DEFAULT NULL,
  `member_initials` varchar(50) DEFAULT NULL,
  `witness_initials` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `new_house_tour_forms`
--

CREATE TABLE `new_house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `form_date` varchar(50) NOT NULL DEFAULT '',
  `form_time` varchar(50) NOT NULL DEFAULT '',
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `inspector_signature` varchar(255) NOT NULL DEFAULT '',
  `smoking_area_score` varchar(5) NOT NULL DEFAULT '',
  `smoking_area_comment` text DEFAULT NULL,
  `notes_text` text DEFAULT NULL,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_average` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payload_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nightly_kitchen_schedules`
--

CREATE TABLE `nightly_kitchen_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `trash_to_curb_on` varchar(255) NOT NULL DEFAULT '',
  `schedule_data` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_chore_lists`
--

CREATE TABLE `oxford_chore_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT 'CHORE LIST',
  `week1_start` varchar(20) NOT NULL DEFAULT '',
  `week2_start` varchar(20) NOT NULL DEFAULT '',
  `week3_start` varchar(20) NOT NULL DEFAULT '',
  `week4_start` varchar(20) NOT NULL DEFAULT '',
  `week5_start` varchar(20) NOT NULL DEFAULT '',
  `week6_start` varchar(20) NOT NULL DEFAULT '',
  `week7_start` varchar(20) NOT NULL DEFAULT '',
  `week8_start` varchar(20) NOT NULL DEFAULT '',
  `form_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_disruptive_contracts`
--

CREATE TABLE `oxford_disruptive_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_subject` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(50) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `behavior_1` text DEFAULT NULL,
  `behavior_2` text DEFAULT NULL,
  `behavior_3` text DEFAULT NULL,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgment_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(50) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_original_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_stored_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_mime` varchar(100) NOT NULL DEFAULT '',
  `uploaded_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_financial_audits`
--

CREATE TABLE `oxford_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `date_completed` date DEFAULT NULL,
  `bank_statement_ending_date` date DEFAULT NULL,
  `bank_statement_ending_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_past_due_bills` decimal(12,2) NOT NULL DEFAULT 0.00,
  `savings_account_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_outstanding_ees` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deposits_json` longtext DEFAULT NULL,
  `checks_json` longtext DEFAULT NULL,
  `treasurer_signature` varchar(255) DEFAULT NULL,
  `comptroller_signature` varchar(255) DEFAULT NULL,
  `president_signature` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_audits`
--

CREATE TABLE `oxford_house_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `completed_month` varchar(10) NOT NULL DEFAULT '',
  `completed_day` varchar(10) NOT NULL DEFAULT '',
  `completed_year` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_month` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_day` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_year` varchar(10) NOT NULL DEFAULT '',
  `past_due_bills` varchar(50) NOT NULL DEFAULT '',
  `savings_balance` varchar(50) NOT NULL DEFAULT '',
  `outstanding_ees` varchar(50) NOT NULL DEFAULT '',
  `bank_statement_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `deposits_total` varchar(50) NOT NULL DEFAULT '',
  `checks_total` varchar(50) NOT NULL DEFAULT '',
  `balance_after_audit` varchar(50) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_signature` varchar(255) NOT NULL DEFAULT '',
  `comptroller_signature` varchar(255) NOT NULL DEFAULT '',
  `president_signature` varchar(255) NOT NULL DEFAULT '',
  `checks_rows` longtext DEFAULT NULL,
  `deposits_rows` longtext DEFAULT NULL,
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `scan_stored_name` varchar(255) NOT NULL DEFAULT '',
  `scan_path` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_reports`
--

CREATE TABLE `oxford_house_financial_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `report_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_ledger_forms`
--

CREATE TABLE `oxford_house_ledger_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `rows_json` longtext DEFAULT NULL,
  `totals_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_meeting_minutes_json`
--

CREATE TABLE `oxford_house_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_member_ledger`
--

CREATE TABLE `oxford_house_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(10) NOT NULL DEFAULT '',
  `move_in_day` varchar(10) NOT NULL DEFAULT '',
  `move_in_year` varchar(10) NOT NULL DEFAULT '',
  `row_1` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_1`)),
  `row_2` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_2`)),
  `row_3` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_3`)),
  `row_4` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_4`)),
  `row_5` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_5`)),
  `row_6` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_6`)),
  `row_7` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_7`)),
  `row_8` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_8`)),
  `row_9` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_9`)),
  `row_10` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_10`)),
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date_paid` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_minutes`
--

CREATE TABLE `oxford_house_minutes` (
  `id` int(11) NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `meeting_date` varchar(50) DEFAULT NULL,
  `start_time` varchar(50) DEFAULT NULL,
  `tradition_number` varchar(50) DEFAULT NULL,
  `meeting_type_regular` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_emergency` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_interview` tinyint(1) NOT NULL DEFAULT 0,
  `minutes_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `amend_yes` tinyint(1) NOT NULL DEFAULT 0,
  `amend_no` tinyint(1) NOT NULL DEFAULT 0,
  `checking_0` varchar(50) DEFAULT NULL,
  `checking_1` varchar(50) DEFAULT NULL,
  `checking_2` varchar(50) DEFAULT NULL,
  `checking_3` varchar(50) DEFAULT NULL,
  `savings_0` varchar(50) DEFAULT NULL,
  `savings_1` varchar(50) DEFAULT NULL,
  `savings_2` varchar(50) DEFAULT NULL,
  `savings_3` varchar(50) DEFAULT NULL,
  `savings_4` varchar(50) DEFAULT NULL,
  `petty_0` varchar(50) DEFAULT NULL,
  `petty_1` varchar(50) DEFAULT NULL,
  `petty_2` varchar(50) DEFAULT NULL,
  `petty_3` varchar(50) DEFAULT NULL,
  `petty_receipts_yes` tinyint(1) NOT NULL DEFAULT 0,
  `petty_receipts_no` tinyint(1) NOT NULL DEFAULT 0,
  `treasurer_comments` text DEFAULT NULL,
  `treasurer_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `comptroller_comments` text DEFAULT NULL,
  `comptroller_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `ees_plan` longtext DEFAULT NULL,
  `coordinator_report` text DEFAULT NULL,
  `coordinator_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `housing_services_report` text DEFAULT NULL,
  `hsr_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_n` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_n` tinyint(1) NOT NULL DEFAULT 0,
  `unfinished_business` text DEFAULT NULL,
  `vacancy_updated_y` tinyint(1) NOT NULL DEFAULT 0,
  `vacancy_updated_n` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_y` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_n` tinyint(1) NOT NULL DEFAULT 0,
  `new_business` longtext DEFAULT NULL,
  `adjourn_hour` varchar(10) DEFAULT NULL,
  `adjourn_min` varchar(10) DEFAULT NULL,
  `secretary_name` varchar(255) DEFAULT NULL,
  `secretary_signature` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `roll_name_1` text DEFAULT NULL,
  `roll_y_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_2` text DEFAULT NULL,
  `roll_y_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_3` text DEFAULT NULL,
  `roll_y_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_4` text DEFAULT NULL,
  `roll_y_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_5` text DEFAULT NULL,
  `roll_y_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_6` text DEFAULT NULL,
  `roll_y_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_7` text DEFAULT NULL,
  `roll_y_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_8` text DEFAULT NULL,
  `roll_y_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_9` text DEFAULT NULL,
  `roll_y_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_10` text DEFAULT NULL,
  `roll_y_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_11` text DEFAULT NULL,
  `roll_y_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_12` text DEFAULT NULL,
  `roll_y_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_13` text DEFAULT NULL,
  `roll_y_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_14` text DEFAULT NULL,
  `roll_y_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_15` text DEFAULT NULL,
  `roll_y_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_16` text DEFAULT NULL,
  `roll_y_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_17` text DEFAULT NULL,
  `roll_y_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_18` text DEFAULT NULL,
  `roll_y_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_19` text DEFAULT NULL,
  `roll_y_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_20` text DEFAULT NULL,
  `roll_y_20` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_20` tinyint(1) NOT NULL DEFAULT 0,
  `comp_name_1` text DEFAULT NULL,
  `comp_bal_1` text DEFAULT NULL,
  `comp_name_2` text DEFAULT NULL,
  `comp_bal_2` text DEFAULT NULL,
  `comp_name_3` text DEFAULT NULL,
  `comp_bal_3` text DEFAULT NULL,
  `comp_name_4` text DEFAULT NULL,
  `comp_bal_4` text DEFAULT NULL,
  `comp_name_5` text DEFAULT NULL,
  `comp_bal_5` text DEFAULT NULL,
  `comp_name_6` text DEFAULT NULL,
  `comp_bal_6` text DEFAULT NULL,
  `comp_name_7` text DEFAULT NULL,
  `comp_bal_7` text DEFAULT NULL,
  `comp_name_8` text DEFAULT NULL,
  `comp_bal_8` text DEFAULT NULL,
  `comp_name_9` text DEFAULT NULL,
  `comp_bal_9` text DEFAULT NULL,
  `comp_name_10` text DEFAULT NULL,
  `comp_bal_10` text DEFAULT NULL,
  `comp_name_11` text DEFAULT NULL,
  `comp_bal_11` text DEFAULT NULL,
  `comp_name_12` text DEFAULT NULL,
  `comp_bal_12` text DEFAULT NULL,
  `comp_name_13` text DEFAULT NULL,
  `comp_bal_13` text DEFAULT NULL,
  `comp_name_14` text DEFAULT NULL,
  `comp_bal_14` text DEFAULT NULL,
  `comp_name_15` text DEFAULT NULL,
  `comp_bal_15` text DEFAULT NULL,
  `comp_name_16` text DEFAULT NULL,
  `comp_bal_16` text DEFAULT NULL,
  `comp_name_17` text DEFAULT NULL,
  `comp_bal_17` text DEFAULT NULL,
  `comp_name_18` text DEFAULT NULL,
  `comp_bal_18` text DEFAULT NULL,
  `comp_name_19` text DEFAULT NULL,
  `comp_bal_19` text DEFAULT NULL,
  `comp_name_20` text DEFAULT NULL,
  `comp_bal_20` text DEFAULT NULL,
  `secretary_signed_date` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_interview_minutes`
--

CREATE TABLE `oxford_interview_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `interview_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `interview_date` date DEFAULT NULL,
  `interviewer_name` varchar(255) NOT NULL DEFAULT '',
  `contact_phone` varchar(100) NOT NULL DEFAULT '',
  `outcome_status` varchar(100) NOT NULL DEFAULT '',
  `vote_percent` varchar(50) NOT NULL DEFAULT '',
  `move_in_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `intro_notes` mediumtext DEFAULT NULL,
  `interviewer_do_notes` mediumtext DEFAULT NULL,
  `interviewer_dont_notes` mediumtext DEFAULT NULL,
  `closing_notes` mediumtext DEFAULT NULL,
  `roll_name_1` varchar(255) NOT NULL DEFAULT '',
  `roll_present_1` varchar(20) NOT NULL DEFAULT '',
  `roll_name_2` varchar(255) NOT NULL DEFAULT '',
  `roll_present_2` varchar(20) NOT NULL DEFAULT '',
  `roll_name_3` varchar(255) NOT NULL DEFAULT '',
  `roll_present_3` varchar(20) NOT NULL DEFAULT '',
  `roll_name_4` varchar(255) NOT NULL DEFAULT '',
  `roll_present_4` varchar(20) NOT NULL DEFAULT '',
  `roll_name_5` varchar(255) NOT NULL DEFAULT '',
  `roll_present_5` varchar(20) NOT NULL DEFAULT '',
  `roll_name_6` varchar(255) NOT NULL DEFAULT '',
  `roll_present_6` varchar(20) NOT NULL DEFAULT '',
  `roll_name_7` varchar(255) NOT NULL DEFAULT '',
  `roll_present_7` varchar(20) NOT NULL DEFAULT '',
  `roll_name_8` varchar(255) NOT NULL DEFAULT '',
  `roll_present_8` varchar(20) NOT NULL DEFAULT '',
  `roll_name_9` varchar(255) NOT NULL DEFAULT '',
  `roll_present_9` varchar(20) NOT NULL DEFAULT '',
  `roll_name_10` varchar(255) NOT NULL DEFAULT '',
  `roll_present_10` varchar(20) NOT NULL DEFAULT '',
  `roll_name_11` varchar(255) NOT NULL DEFAULT '',
  `roll_present_11` varchar(20) NOT NULL DEFAULT '',
  `roll_name_12` varchar(255) NOT NULL DEFAULT '',
  `roll_present_12` varchar(20) NOT NULL DEFAULT '',
  `q1` mediumtext DEFAULT NULL,
  `q2` mediumtext DEFAULT NULL,
  `q3` mediumtext DEFAULT NULL,
  `q4` mediumtext DEFAULT NULL,
  `q5` mediumtext DEFAULT NULL,
  `q6` mediumtext DEFAULT NULL,
  `q7` mediumtext DEFAULT NULL,
  `q8` mediumtext DEFAULT NULL,
  `q9` mediumtext DEFAULT NULL,
  `q10` mediumtext DEFAULT NULL,
  `q11` mediumtext DEFAULT NULL,
  `q12` mediumtext DEFAULT NULL,
  `q13` mediumtext DEFAULT NULL,
  `q14` mediumtext DEFAULT NULL,
  `q15` mediumtext DEFAULT NULL,
  `q16` mediumtext DEFAULT NULL,
  `q17` mediumtext DEFAULT NULL,
  `q18` mediumtext DEFAULT NULL,
  `q19` mediumtext DEFAULT NULL,
  `q20` mediumtext DEFAULT NULL,
  `q21` mediumtext DEFAULT NULL,
  `q22` mediumtext DEFAULT NULL,
  `q23` mediumtext DEFAULT NULL,
  `q24` mediumtext DEFAULT NULL,
  `q25` mediumtext DEFAULT NULL,
  `q26` mediumtext DEFAULT NULL,
  `q27` mediumtext DEFAULT NULL,
  `q28` mediumtext DEFAULT NULL,
  `q29` mediumtext DEFAULT NULL,
  `q30` mediumtext DEFAULT NULL,
  `q31` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_member_financial_contracts`
--

CREATE TABLE `oxford_member_financial_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(100) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `total_amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgement_name` varchar(255) NOT NULL DEFAULT '',
  `signature_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(100) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `scanned_contract` varchar(255) NOT NULL DEFAULT '',
  `contract_stamp` varchar(50) NOT NULL DEFAULT '',
  `contract_stamp_at` datetime DEFAULT NULL,
  `contract_stamp_by_ip` varchar(64) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_new_member_packets`
--

CREATE TABLE `oxford_new_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `packet_date` date DEFAULT NULL,
  `check_membership_application` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_manual` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_guidelines` tinyint(1) NOT NULL DEFAULT 0,
  `check_membership_agreement` tinyint(1) NOT NULL DEFAULT 0,
  `check_plan_for_recovery` tinyint(1) NOT NULL DEFAULT 0,
  `check_relapse_contingency` tinyint(1) NOT NULL DEFAULT 0,
  `check_medical_release` tinyint(1) NOT NULL DEFAULT 0,
  `check_property_list` tinyint(1) NOT NULL DEFAULT 0,
  `member_initials_1` varchar(20) NOT NULL DEFAULT '',
  `president_initials_1` varchar(20) NOT NULL DEFAULT '',
  `member_initials_2` varchar(20) NOT NULL DEFAULT '',
  `president_initials_2` varchar(20) NOT NULL DEFAULT '',
  `member_initials_3` varchar(20) NOT NULL DEFAULT '',
  `president_initials_3` varchar(20) NOT NULL DEFAULT '',
  `member_initials_4` varchar(20) NOT NULL DEFAULT '',
  `president_initials_4` varchar(20) NOT NULL DEFAULT '',
  `member_initials_5` varchar(20) NOT NULL DEFAULT '',
  `president_initials_5` varchar(20) NOT NULL DEFAULT '',
  `member_initials_6` varchar(20) NOT NULL DEFAULT '',
  `president_initials_6` varchar(20) NOT NULL DEFAULT '',
  `member_initials_7` varchar(20) NOT NULL DEFAULT '',
  `president_initials_7` varchar(20) NOT NULL DEFAULT '',
  `member_initials_8` varchar(20) NOT NULL DEFAULT '',
  `president_initials_8` varchar(20) NOT NULL DEFAULT '',
  `member_signature_1` varchar(255) NOT NULL DEFAULT '',
  `member_signature_date_1` date DEFAULT NULL,
  `president_signature_1` varchar(255) NOT NULL DEFAULT '',
  `president_signature_date_1` date DEFAULT NULL,
  `membership_agreement_text` longtext DEFAULT NULL,
  `agreement_member_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_date` date DEFAULT NULL,
  `agreement_president_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_date` date DEFAULT NULL,
  `plan_name` varchar(255) NOT NULL DEFAULT '',
  `plan_text` longtext DEFAULT NULL,
  `aftercare_program` longtext DEFAULT NULL,
  `has_sponsor` varchar(10) NOT NULL DEFAULT '',
  `sponsor_by_date` date DEFAULT NULL,
  `meetings_per_week` varchar(50) NOT NULL DEFAULT '',
  `meeting_types` varchar(255) NOT NULL DEFAULT '',
  `plan_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_signature_date` date DEFAULT NULL,
  `plan_president_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_president_date` date DEFAULT NULL,
  `relapse_name` varchar(255) NOT NULL DEFAULT '',
  `relapse_family` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_friend` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_detox` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other_text` varchar(255) NOT NULL DEFAULT '',
  `relapse_details` longtext DEFAULT NULL,
  `notify_rows_json` longtext DEFAULT NULL,
  `pickup_rows_json` longtext DEFAULT NULL,
  `relapse_member_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_member_date` date DEFAULT NULL,
  `relapse_president_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_president_date` date DEFAULT NULL,
  `relapse_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_witness_date` date DEFAULT NULL,
  `medical_name` varchar(255) NOT NULL DEFAULT '',
  `physician_name` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(50) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance_info` varchar(255) NOT NULL DEFAULT '',
  `allergies` longtext DEFAULT NULL,
  `medications` longtext DEFAULT NULL,
  `medical_history` longtext DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `blood_type` varchar(20) NOT NULL DEFAULT '',
  `medical_contacts_json` longtext DEFAULT NULL,
  `medical_signature` varchar(255) NOT NULL DEFAULT '',
  `medical_date` date DEFAULT NULL,
  `property_name` varchar(255) NOT NULL DEFAULT '',
  `property_move_in_date` date DEFAULT NULL,
  `property_rows_json` longtext DEFAULT NULL,
  `uploaded_copy_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_path` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_mime` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_red_creek_member_packets`
--

CREATE TABLE `oxford_red_creek_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `signature_date` date DEFAULT NULL,
  `refund_move_in_date` date DEFAULT NULL,
  `new_member_signature` varchar(255) NOT NULL DEFAULT '',
  `new_member_signature_date` date DEFAULT NULL,
  `president_hsr_signature` varchar(255) NOT NULL DEFAULT '',
  `president_hsr_signature_date` date DEFAULT NULL,
  `expectations_signature` varchar(255) NOT NULL DEFAULT '',
  `expectations_signature_date` date DEFAULT NULL,
  `medication_signature` varchar(255) NOT NULL DEFAULT '',
  `medication_signature_date` date DEFAULT NULL,
  `emergency_name` varchar(255) NOT NULL DEFAULT '',
  `emergency_age` varchar(50) NOT NULL DEFAULT '',
  `emergency_dob` varchar(50) NOT NULL DEFAULT '',
  `blood_type` varchar(50) NOT NULL DEFAULT '',
  `primary_physician` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(100) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance` varchar(255) NOT NULL DEFAULT '',
  `allergies` text DEFAULT NULL,
  `medications` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `contact1_name` varchar(255) NOT NULL DEFAULT '',
  `contact1_phone` varchar(100) NOT NULL DEFAULT '',
  `contact2_name` varchar(255) NOT NULL DEFAULT '',
  `contact2_phone` varchar(100) NOT NULL DEFAULT '',
  `contact3_name` varchar(255) NOT NULL DEFAULT '',
  `contact3_phone` varchar(100) NOT NULL DEFAULT '',
  `property_items` longtext DEFAULT NULL,
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `property_signature` varchar(255) NOT NULL DEFAULT '',
  `property_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `property_date` date DEFAULT NULL,
  `property_removed_date` date DEFAULT NULL,
  `property_removed_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `move_in_fee` decimal(10,2) DEFAULT 250.00,
  `other_charge` decimal(10,2) DEFAULT 0.00,
  `total_due` decimal(10,2) DEFAULT NULL,
  `scan_path` varchar(500) NOT NULL DEFAULT '',
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_residency_forms`
--

CREATE TABLE `oxford_residency_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) DEFAULT '',
  `letter_date` varchar(20) DEFAULT '',
  `house_name` varchar(255) DEFAULT '',
  `accepted_date` varchar(20) DEFAULT '',
  `address_line` varchar(255) DEFAULT '',
  `city_state_zip` varchar(255) DEFAULT '',
  `move_in_fee` decimal(10,2) DEFAULT 0.00,
  `weekly_rent` decimal(10,2) DEFAULT 0.00,
  `president_contact` varchar(255) DEFAULT '',
  `president_name` varchar(255) DEFAULT '',
  `president_signature` varchar(255) DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_shopping_lists`
--

CREATE TABLE `oxford_shopping_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `shopping_date` date DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Oxford House Shopping List',
  `items_json` longtext NOT NULL,
  `checked_json` longtext NOT NULL,
  `total_checked` int(11) NOT NULL DEFAULT 0,
  `total_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_items` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy_path` varchar(500) DEFAULT NULL,
  `uploaded_copy_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledgers`
--

CREATE TABLE `petty_cash_ledgers` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `ledger_date` date DEFAULT NULL,
  `beginning_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledger_rows`
--

CREATE TABLE `petty_cash_ledger_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `ledger_id` int(10) UNSIGNED NOT NULL,
  `row_index` int(11) NOT NULL,
  `txn_date` varchar(50) NOT NULL DEFAULT '',
  `products_purchased` varchar(255) NOT NULL DEFAULT '',
  `vendor` varchar(255) NOT NULL DEFAULT '',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reimbursement_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `safety_inspection_checklists`
--

CREATE TABLE `safety_inspection_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `inspection_date` date DEFAULT NULL,
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `checklist_json` longtext NOT NULL,
  `satisfactory_total` int(11) NOT NULL DEFAULT 0,
  `unsatisfactory_total` int(11) NOT NULL DEFAULT 0,
  `completed_total` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy` varchar(500) DEFAULT NULL,
  `original_upload_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_checklist_key` (`checklist_key`);

--
-- Indexes for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_meeting_date` (`meeting_date`);

--
-- Indexes for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_dates` (`week_start`,`week_end`);

--
-- Indexes for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_tour_date` (`tour_date`);

--
-- Indexes for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `house_name` (`house_name`);

--
-- Indexes for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_label` (`schedule_label`);

--
-- Indexes for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`);

--
-- Indexes for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_name_idx` (`tenant_name`),
  ADD KEY `updated_at_idx` (`updated_at`);

--
-- Indexes for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resident_name` (`resident_name`),
  ADD KEY `idx_sheet_date` (`sheet_date`);

--
-- Indexes for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sheet_id` (`sheet_id`),
  ADD KEY `idx_row_number` (`row_number`);

--
-- Indexes for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_form_date` (`form_date`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_contract_date` (`contract_date`);

--
-- Indexes for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date_completed` (`date_completed`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- Indexes for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_dates` (`house_name`,`date_from`,`date_to`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_date_from` (`date_from`),
  ADD KEY `idx_date_to` (`date_to`);

--
-- Indexes for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_start` (`week_start`),
  ADD KEY `idx_week_end` (`week_end`);

--
-- Indexes for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`),
  ADD KEY `idx_house_date` (`house_name`,`meeting_date`);

--
-- Indexes for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_member_date_house` (`member_name`,`contract_date`,`house_name`);

--
-- Indexes for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_member` (`house_name`,`member_name`);

--
-- Indexes for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_shopping_date` (`shopping_date`);

--
-- Indexes for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_date` (`house_name`,`ledger_date`);

--
-- Indexes for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_petty_cash_ledger_rows_ledger` (`ledger_id`);

--
-- Indexes for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inspection_date` (`inspection_date`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `sunset_park`
--
CREATE DATABASE IF NOT EXISTS `sunset_park` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sunset_park`;

-- --------------------------------------------------------

--
-- Table structure for table `bedroom_essentials_checklists`
--

CREATE TABLE `bedroom_essentials_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `checklist_key` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Bedroom Essentials – Checklist',
  `items_json` longtext NOT NULL,
  `dark_mode` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chapter_meeting_minutes`
--

CREATE TABLE `chapter_meeting_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `meeting_date` varchar(50) NOT NULL,
  `form_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ees_member_ledger`
--

CREATE TABLE `ees_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(2) NOT NULL DEFAULT '',
  `move_in_day` varchar(2) NOT NULL DEFAULT '',
  `move_in_year` varchar(4) NOT NULL DEFAULT '',
  `ledger_rows` longtext DEFAULT NULL,
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_ledger_records`
--

CREATE TABLE `house_ledger_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `notes` mediumtext DEFAULT NULL,
  `rows_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_tour_forms`
--

CREATE TABLE `house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `tour_date` date DEFAULT NULL,
  `tour_time` varchar(20) NOT NULL DEFAULT '',
  `smoking_area` varchar(10) NOT NULL DEFAULT '',
  `notes` text DEFAULT NULL,
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `signature` varchar(255) NOT NULL DEFAULT '',
  `items_json` longtext DEFAULT NULL,
  `section_totals_json` text DEFAULT NULL,
  `grand_total` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_houses`
--

CREATE TABLE `house_visit_houses` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(150) NOT NULL,
  `meeting_day` varchar(20) NOT NULL DEFAULT '',
  `meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_reports`
--

CREATE TABLE `house_visit_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(100) NOT NULL DEFAULT '',
  `president` varchar(255) NOT NULL DEFAULT '',
  `secretary` varchar(255) NOT NULL DEFAULT '',
  `treasurer` varchar(255) NOT NULL DEFAULT '',
  `comptroller` varchar(255) NOT NULL DEFAULT '',
  `coordinator` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep` varchar(255) NOT NULL DEFAULT '',
  `overall_appearance` varchar(10) NOT NULL DEFAULT '',
  `overall_appearance_comments` text DEFAULT NULL,
  `members_behind_ees` decimal(10,2) DEFAULT NULL,
  `total_amount_owed` decimal(12,2) DEFAULT NULL,
  `rent_paid_monthly` decimal(12,2) DEFAULT NULL,
  `ees_paid_weekly` decimal(12,2) DEFAULT NULL,
  `utilities_monthly` decimal(12,2) DEFAULT NULL,
  `house_business_meeting` varchar(10) NOT NULL DEFAULT '',
  `house_business_comments` text DEFAULT NULL,
  `rating_reading_traditions` varchar(10) NOT NULL DEFAULT '',
  `rating_reading_minutes` varchar(10) NOT NULL DEFAULT '',
  `rating_treasurer_report` varchar(10) NOT NULL DEFAULT '',
  `rating_comptroller_report` varchar(10) NOT NULL DEFAULT '',
  `rating_coordinator_report` varchar(10) NOT NULL DEFAULT '',
  `rating_maintains_guidelines` varchar(10) NOT NULL DEFAULT '',
  `rating_handling_business` varchar(10) NOT NULL DEFAULT '',
  `rating_organization_order` varchar(10) NOT NULL DEFAULT '',
  `financial_comments` text DEFAULT NULL,
  `first_visit_date` varchar(50) NOT NULL DEFAULT '',
  `narcan_present` varchar(10) NOT NULL DEFAULT '',
  `narcan_trained` varchar(10) NOT NULL DEFAULT '',
  `follow_up_visit_dates` varchar(255) NOT NULL DEFAULT '',
  `hsc_rep_signature` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_visit_schedules`
--

CREATE TABLE `house_visit_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `schedule_label` varchar(150) NOT NULL,
  `base_month` varchar(7) NOT NULL DEFAULT '',
  `month_count` int(11) NOT NULL DEFAULT 1,
  `repeat_cycle` tinyint(1) NOT NULL DEFAULT 0,
  `step_size` int(11) NOT NULL DEFAULT 1,
  `month_label` varchar(50) NOT NULL,
  `month_index` int(11) NOT NULL DEFAULT 1,
  `visiting_house` varchar(150) NOT NULL,
  `host_house` varchar(150) NOT NULL,
  `host_meeting_day` varchar(20) NOT NULL DEFAULT '',
  `host_meeting_time` varchar(10) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `housing_service_representative_reports`
--

CREATE TABLE `housing_service_representative_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `week_from` varchar(100) NOT NULL DEFAULT '',
  `week_to` varchar(100) NOT NULL DEFAULT '',
  `house_visit` text DEFAULT NULL,
  `audit_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `audit_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_yes` tinyint(1) NOT NULL DEFAULT 0,
  `summary_done_no` tinyint(1) NOT NULL DEFAULT 0,
  `next_chapter_meeting` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `next_state_meeting` text DEFAULT NULL,
  `number_of_applications` text DEFAULT NULL,
  `number_contacted_plan` longtext DEFAULT NULL,
  `new_members` longtext DEFAULT NULL,
  `interviews_setup` longtext DEFAULT NULL,
  `chapter_news` longtext DEFAULT NULL,
  `chapter_meeting_recap` longtext DEFAULT NULL,
  `upcoming_unity` longtext DEFAULT NULL,
  `upcoming_presentations` longtext DEFAULT NULL,
  `hsr_name` varchar(255) NOT NULL DEFAULT '',
  `report_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hsc_meeting_minutes_json`
--

CREATE TABLE `hsc_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `landlord_verification_forms`
--

CREATE TABLE `landlord_verification_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_name` varchar(255) NOT NULL DEFAULT '',
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `landlord_manager_name` varchar(255) NOT NULL DEFAULT '',
  `manager_street` varchar(255) NOT NULL DEFAULT '',
  `manager_city` varchar(150) NOT NULL DEFAULT '',
  `manager_state` varchar(50) NOT NULL DEFAULT '',
  `manager_zip` varchar(25) NOT NULL DEFAULT '',
  `manager_county` varchar(150) NOT NULL DEFAULT '',
  `manager_phone` varchar(50) NOT NULL DEFAULT '',
  `manager_email` varchar(255) NOT NULL DEFAULT '',
  `rental_street` varchar(255) NOT NULL DEFAULT '',
  `rental_apt_lot` varchar(100) NOT NULL DEFAULT '',
  `rental_city` varchar(150) NOT NULL DEFAULT '',
  `rental_state` varchar(50) NOT NULL DEFAULT '',
  `rental_zip` varchar(25) NOT NULL DEFAULT '',
  `rental_county` varchar(150) NOT NULL DEFAULT '',
  `bedrooms` varchar(20) NOT NULL DEFAULT '',
  `lease_start_date` date DEFAULT NULL,
  `lease_end_date` date DEFAULT NULL,
  `monthly_rent_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `next_payment_due_date` date DEFAULT NULL,
  `last_payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `last_payment_date` date DEFAULT NULL,
  `tenant_in_arrears` tinyint(1) NOT NULL DEFAULT 0,
  `amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `arrears_period_from` date DEFAULT NULL,
  `arrears_period_to` date DEFAULT NULL,
  `receiving_other_assistance` tinyint(1) NOT NULL DEFAULT 0,
  `other_assistance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_assistance_period` varchar(100) NOT NULL DEFAULT '',
  `payment_method` varchar(20) NOT NULL DEFAULT 'check',
  `check_payable_to` varchar(255) NOT NULL DEFAULT '',
  `send_to_landlord_address` tinyint(1) NOT NULL DEFAULT 1,
  `alternative_address` text DEFAULT NULL,
  `cert_name` varchar(255) NOT NULL DEFAULT '',
  `cert_title` varchar(255) NOT NULL DEFAULT '',
  `cert_signature` varchar(255) NOT NULL DEFAULT '',
  `cert_date` date DEFAULT NULL,
  `calc_balance_remaining` decimal(10,2) NOT NULL DEFAULT 0.00,
  `calc_total_assistance_gap` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheets`
--

CREATE TABLE `medication_count_sheets` (
  `id` int(10) UNSIGNED NOT NULL,
  `resident_name` varchar(255) NOT NULL,
  `sheet_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_count_sheet_rows`
--

CREATE TABLE `medication_count_sheet_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `sheet_id` int(10) UNSIGNED NOT NULL,
  `row_number` int(11) NOT NULL,
  `entry_date` varchar(50) DEFAULT NULL,
  `medication_name` varchar(255) DEFAULT NULL,
  `dosage` varchar(255) DEFAULT NULL,
  `frequency` varchar(255) DEFAULT NULL,
  `previous_count` varchar(100) DEFAULT NULL,
  `current_count` varchar(100) DEFAULT NULL,
  `member_initials` varchar(50) DEFAULT NULL,
  `witness_initials` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `new_house_tour_forms`
--

CREATE TABLE `new_house_tour_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `form_date` varchar(50) NOT NULL DEFAULT '',
  `form_time` varchar(50) NOT NULL DEFAULT '',
  `inspected_by` varchar(255) NOT NULL DEFAULT '',
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `inspector_signature` varchar(255) NOT NULL DEFAULT '',
  `smoking_area_score` varchar(5) NOT NULL DEFAULT '',
  `smoking_area_comment` text DEFAULT NULL,
  `notes_text` text DEFAULT NULL,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_average` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payload_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nightly_kitchen_schedules`
--

CREATE TABLE `nightly_kitchen_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `trash_to_curb_on` varchar(255) NOT NULL DEFAULT '',
  `schedule_data` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_chore_lists`
--

CREATE TABLE `oxford_chore_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT 'CHORE LIST',
  `week1_start` varchar(20) NOT NULL DEFAULT '',
  `week2_start` varchar(20) NOT NULL DEFAULT '',
  `week3_start` varchar(20) NOT NULL DEFAULT '',
  `week4_start` varchar(20) NOT NULL DEFAULT '',
  `week5_start` varchar(20) NOT NULL DEFAULT '',
  `week6_start` varchar(20) NOT NULL DEFAULT '',
  `week7_start` varchar(20) NOT NULL DEFAULT '',
  `week8_start` varchar(20) NOT NULL DEFAULT '',
  `form_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_disruptive_contracts`
--

CREATE TABLE `oxford_disruptive_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_subject` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(50) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `behavior_1` text DEFAULT NULL,
  `behavior_2` text DEFAULT NULL,
  `behavior_3` text DEFAULT NULL,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgment_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(50) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_original_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_stored_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_mime` varchar(100) NOT NULL DEFAULT '',
  `uploaded_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_financial_audits`
--

CREATE TABLE `oxford_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `date_completed` date DEFAULT NULL,
  `bank_statement_ending_date` date DEFAULT NULL,
  `bank_statement_ending_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_past_due_bills` decimal(12,2) NOT NULL DEFAULT 0.00,
  `savings_account_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_outstanding_ees` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deposits_json` longtext DEFAULT NULL,
  `checks_json` longtext DEFAULT NULL,
  `treasurer_signature` varchar(255) DEFAULT NULL,
  `comptroller_signature` varchar(255) DEFAULT NULL,
  `president_signature` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_audits`
--

CREATE TABLE `oxford_house_financial_audits` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `completed_month` varchar(10) NOT NULL DEFAULT '',
  `completed_day` varchar(10) NOT NULL DEFAULT '',
  `completed_year` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_month` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_day` varchar(10) NOT NULL DEFAULT '',
  `bank_ending_year` varchar(10) NOT NULL DEFAULT '',
  `past_due_bills` varchar(50) NOT NULL DEFAULT '',
  `savings_balance` varchar(50) NOT NULL DEFAULT '',
  `outstanding_ees` varchar(50) NOT NULL DEFAULT '',
  `bank_statement_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `deposits_total` varchar(50) NOT NULL DEFAULT '',
  `checks_total` varchar(50) NOT NULL DEFAULT '',
  `balance_after_audit` varchar(50) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_signature` varchar(255) NOT NULL DEFAULT '',
  `comptroller_signature` varchar(255) NOT NULL DEFAULT '',
  `president_signature` varchar(255) NOT NULL DEFAULT '',
  `checks_rows` longtext DEFAULT NULL,
  `deposits_rows` longtext DEFAULT NULL,
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `scan_stored_name` varchar(255) NOT NULL DEFAULT '',
  `scan_path` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_financial_reports`
--

CREATE TABLE `oxford_house_financial_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `report_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_ledger_forms`
--

CREATE TABLE `oxford_house_ledger_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `rows_json` longtext DEFAULT NULL,
  `totals_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_meeting_minutes_json`
--

CREATE TABLE `oxford_house_meeting_minutes_json` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL,
  `meeting_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `report_json` longtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_member_ledger`
--

CREATE TABLE `oxford_house_member_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_month` varchar(10) NOT NULL DEFAULT '',
  `move_in_day` varchar(10) NOT NULL DEFAULT '',
  `move_in_year` varchar(10) NOT NULL DEFAULT '',
  `row_1` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_1`)),
  `row_2` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_2`)),
  `row_3` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_3`)),
  `row_4` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_4`)),
  `row_5` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_5`)),
  `row_6` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_6`)),
  `row_7` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_7`)),
  `row_8` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_8`)),
  `row_9` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_9`)),
  `row_10` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`row_10`)),
  `move_in_fee_amount` varchar(50) NOT NULL DEFAULT '',
  `move_in_fee_date_paid` varchar(50) NOT NULL DEFAULT '',
  `departure_date` varchar(50) NOT NULL DEFAULT '',
  `departure_ending_balance` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_house_minutes`
--

CREATE TABLE `oxford_house_minutes` (
  `id` int(11) NOT NULL,
  `house_name` varchar(255) DEFAULT NULL,
  `meeting_date` varchar(50) DEFAULT NULL,
  `start_time` varchar(50) DEFAULT NULL,
  `tradition_number` varchar(50) DEFAULT NULL,
  `meeting_type_regular` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_emergency` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_type_interview` tinyint(1) NOT NULL DEFAULT 0,
  `minutes_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `amend_yes` tinyint(1) NOT NULL DEFAULT 0,
  `amend_no` tinyint(1) NOT NULL DEFAULT 0,
  `checking_0` varchar(50) DEFAULT NULL,
  `checking_1` varchar(50) DEFAULT NULL,
  `checking_2` varchar(50) DEFAULT NULL,
  `checking_3` varchar(50) DEFAULT NULL,
  `savings_0` varchar(50) DEFAULT NULL,
  `savings_1` varchar(50) DEFAULT NULL,
  `savings_2` varchar(50) DEFAULT NULL,
  `savings_3` varchar(50) DEFAULT NULL,
  `savings_4` varchar(50) DEFAULT NULL,
  `petty_0` varchar(50) DEFAULT NULL,
  `petty_1` varchar(50) DEFAULT NULL,
  `petty_2` varchar(50) DEFAULT NULL,
  `petty_3` varchar(50) DEFAULT NULL,
  `petty_receipts_yes` tinyint(1) NOT NULL DEFAULT 0,
  `petty_receipts_no` tinyint(1) NOT NULL DEFAULT 0,
  `treasurer_comments` text DEFAULT NULL,
  `treasurer_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `comptroller_comments` text DEFAULT NULL,
  `comptroller_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `ees_plan` longtext DEFAULT NULL,
  `coordinator_report` text DEFAULT NULL,
  `coordinator_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `housing_services_report` text DEFAULT NULL,
  `hsr_mmsp` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_kit_n` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_y` tinyint(1) NOT NULL DEFAULT 0,
  `narcan_use_n` tinyint(1) NOT NULL DEFAULT 0,
  `unfinished_business` text DEFAULT NULL,
  `vacancy_updated_y` tinyint(1) NOT NULL DEFAULT 0,
  `vacancy_updated_n` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `email_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_y` tinyint(1) NOT NULL DEFAULT 0,
  `voicemail_checked_n` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_y` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_daily_n` tinyint(1) NOT NULL DEFAULT 0,
  `new_business` longtext DEFAULT NULL,
  `adjourn_hour` varchar(10) DEFAULT NULL,
  `adjourn_min` varchar(10) DEFAULT NULL,
  `secretary_name` varchar(255) DEFAULT NULL,
  `secretary_signature` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `roll_name_1` text DEFAULT NULL,
  `roll_y_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_1` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_2` text DEFAULT NULL,
  `roll_y_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_2` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_3` text DEFAULT NULL,
  `roll_y_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_3` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_4` text DEFAULT NULL,
  `roll_y_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_4` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_5` text DEFAULT NULL,
  `roll_y_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_5` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_6` text DEFAULT NULL,
  `roll_y_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_6` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_7` text DEFAULT NULL,
  `roll_y_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_7` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_8` text DEFAULT NULL,
  `roll_y_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_8` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_9` text DEFAULT NULL,
  `roll_y_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_9` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_10` text DEFAULT NULL,
  `roll_y_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_10` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_11` text DEFAULT NULL,
  `roll_y_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_11` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_12` text DEFAULT NULL,
  `roll_y_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_12` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_13` text DEFAULT NULL,
  `roll_y_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_13` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_14` text DEFAULT NULL,
  `roll_y_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_14` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_15` text DEFAULT NULL,
  `roll_y_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_15` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_16` text DEFAULT NULL,
  `roll_y_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_16` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_17` text DEFAULT NULL,
  `roll_y_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_17` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_18` text DEFAULT NULL,
  `roll_y_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_18` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_19` text DEFAULT NULL,
  `roll_y_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_19` tinyint(1) NOT NULL DEFAULT 0,
  `roll_name_20` text DEFAULT NULL,
  `roll_y_20` tinyint(1) NOT NULL DEFAULT 0,
  `roll_n_20` tinyint(1) NOT NULL DEFAULT 0,
  `comp_name_1` text DEFAULT NULL,
  `comp_bal_1` text DEFAULT NULL,
  `comp_name_2` text DEFAULT NULL,
  `comp_bal_2` text DEFAULT NULL,
  `comp_name_3` text DEFAULT NULL,
  `comp_bal_3` text DEFAULT NULL,
  `comp_name_4` text DEFAULT NULL,
  `comp_bal_4` text DEFAULT NULL,
  `comp_name_5` text DEFAULT NULL,
  `comp_bal_5` text DEFAULT NULL,
  `comp_name_6` text DEFAULT NULL,
  `comp_bal_6` text DEFAULT NULL,
  `comp_name_7` text DEFAULT NULL,
  `comp_bal_7` text DEFAULT NULL,
  `comp_name_8` text DEFAULT NULL,
  `comp_bal_8` text DEFAULT NULL,
  `comp_name_9` text DEFAULT NULL,
  `comp_bal_9` text DEFAULT NULL,
  `comp_name_10` text DEFAULT NULL,
  `comp_bal_10` text DEFAULT NULL,
  `comp_name_11` text DEFAULT NULL,
  `comp_bal_11` text DEFAULT NULL,
  `comp_name_12` text DEFAULT NULL,
  `comp_bal_12` text DEFAULT NULL,
  `comp_name_13` text DEFAULT NULL,
  `comp_bal_13` text DEFAULT NULL,
  `comp_name_14` text DEFAULT NULL,
  `comp_bal_14` text DEFAULT NULL,
  `comp_name_15` text DEFAULT NULL,
  `comp_bal_15` text DEFAULT NULL,
  `comp_name_16` text DEFAULT NULL,
  `comp_bal_16` text DEFAULT NULL,
  `comp_name_17` text DEFAULT NULL,
  `comp_bal_17` text DEFAULT NULL,
  `comp_name_18` text DEFAULT NULL,
  `comp_bal_18` text DEFAULT NULL,
  `comp_name_19` text DEFAULT NULL,
  `comp_bal_19` text DEFAULT NULL,
  `comp_name_20` text DEFAULT NULL,
  `comp_bal_20` text DEFAULT NULL,
  `secretary_signed_date` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_interview_minutes`
--

CREATE TABLE `oxford_interview_minutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `interview_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `interview_date` date DEFAULT NULL,
  `interviewer_name` varchar(255) NOT NULL DEFAULT '',
  `contact_phone` varchar(100) NOT NULL DEFAULT '',
  `outcome_status` varchar(100) NOT NULL DEFAULT '',
  `vote_percent` varchar(50) NOT NULL DEFAULT '',
  `move_in_date` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `intro_notes` mediumtext DEFAULT NULL,
  `interviewer_do_notes` mediumtext DEFAULT NULL,
  `interviewer_dont_notes` mediumtext DEFAULT NULL,
  `closing_notes` mediumtext DEFAULT NULL,
  `roll_name_1` varchar(255) NOT NULL DEFAULT '',
  `roll_present_1` varchar(20) NOT NULL DEFAULT '',
  `roll_name_2` varchar(255) NOT NULL DEFAULT '',
  `roll_present_2` varchar(20) NOT NULL DEFAULT '',
  `roll_name_3` varchar(255) NOT NULL DEFAULT '',
  `roll_present_3` varchar(20) NOT NULL DEFAULT '',
  `roll_name_4` varchar(255) NOT NULL DEFAULT '',
  `roll_present_4` varchar(20) NOT NULL DEFAULT '',
  `roll_name_5` varchar(255) NOT NULL DEFAULT '',
  `roll_present_5` varchar(20) NOT NULL DEFAULT '',
  `roll_name_6` varchar(255) NOT NULL DEFAULT '',
  `roll_present_6` varchar(20) NOT NULL DEFAULT '',
  `roll_name_7` varchar(255) NOT NULL DEFAULT '',
  `roll_present_7` varchar(20) NOT NULL DEFAULT '',
  `roll_name_8` varchar(255) NOT NULL DEFAULT '',
  `roll_present_8` varchar(20) NOT NULL DEFAULT '',
  `roll_name_9` varchar(255) NOT NULL DEFAULT '',
  `roll_present_9` varchar(20) NOT NULL DEFAULT '',
  `roll_name_10` varchar(255) NOT NULL DEFAULT '',
  `roll_present_10` varchar(20) NOT NULL DEFAULT '',
  `roll_name_11` varchar(255) NOT NULL DEFAULT '',
  `roll_present_11` varchar(20) NOT NULL DEFAULT '',
  `roll_name_12` varchar(255) NOT NULL DEFAULT '',
  `roll_present_12` varchar(20) NOT NULL DEFAULT '',
  `q1` mediumtext DEFAULT NULL,
  `q2` mediumtext DEFAULT NULL,
  `q3` mediumtext DEFAULT NULL,
  `q4` mediumtext DEFAULT NULL,
  `q5` mediumtext DEFAULT NULL,
  `q6` mediumtext DEFAULT NULL,
  `q7` mediumtext DEFAULT NULL,
  `q8` mediumtext DEFAULT NULL,
  `q9` mediumtext DEFAULT NULL,
  `q10` mediumtext DEFAULT NULL,
  `q11` mediumtext DEFAULT NULL,
  `q12` mediumtext DEFAULT NULL,
  `q13` mediumtext DEFAULT NULL,
  `q14` mediumtext DEFAULT NULL,
  `q15` mediumtext DEFAULT NULL,
  `q16` mediumtext DEFAULT NULL,
  `q17` mediumtext DEFAULT NULL,
  `q18` mediumtext DEFAULT NULL,
  `q19` mediumtext DEFAULT NULL,
  `q20` mediumtext DEFAULT NULL,
  `q21` mediumtext DEFAULT NULL,
  `q22` mediumtext DEFAULT NULL,
  `q23` mediumtext DEFAULT NULL,
  `q24` mediumtext DEFAULT NULL,
  `q25` mediumtext DEFAULT NULL,
  `q26` mediumtext DEFAULT NULL,
  `q27` mediumtext DEFAULT NULL,
  `q28` mediumtext DEFAULT NULL,
  `q29` mediumtext DEFAULT NULL,
  `q30` mediumtext DEFAULT NULL,
  `q31` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_member_financial_contracts`
--

CREATE TABLE `oxford_member_financial_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `contract_date` varchar(100) NOT NULL DEFAULT '',
  `contract_length` varchar(255) NOT NULL DEFAULT '',
  `total_amount_owed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `term_1` text DEFAULT NULL,
  `term_2` text DEFAULT NULL,
  `term_3` text DEFAULT NULL,
  `term_4` text DEFAULT NULL,
  `acknowledgement_name` varchar(255) NOT NULL DEFAULT '',
  `signature_name` varchar(255) NOT NULL DEFAULT '',
  `signature_date` varchar(100) NOT NULL DEFAULT '',
  `president_name` varchar(255) NOT NULL DEFAULT '',
  `treasurer_name` varchar(255) NOT NULL DEFAULT '',
  `coordinator_name` varchar(255) NOT NULL DEFAULT '',
  `member_1_name` varchar(255) NOT NULL DEFAULT '',
  `member_2_name` varchar(255) NOT NULL DEFAULT '',
  `secretary_name` varchar(255) NOT NULL DEFAULT '',
  `comptroller_name` varchar(255) NOT NULL DEFAULT '',
  `hs_representative_name` varchar(255) NOT NULL DEFAULT '',
  `member_3_name` varchar(255) NOT NULL DEFAULT '',
  `member_4_name` varchar(255) NOT NULL DEFAULT '',
  `scanned_contract` varchar(255) NOT NULL DEFAULT '',
  `contract_stamp` varchar(50) NOT NULL DEFAULT '',
  `contract_stamp_at` datetime DEFAULT NULL,
  `contract_stamp_by_ip` varchar(64) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_new_member_packets`
--

CREATE TABLE `oxford_new_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `packet_date` date DEFAULT NULL,
  `check_membership_application` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_manual` tinyint(1) NOT NULL DEFAULT 0,
  `check_house_guidelines` tinyint(1) NOT NULL DEFAULT 0,
  `check_membership_agreement` tinyint(1) NOT NULL DEFAULT 0,
  `check_plan_for_recovery` tinyint(1) NOT NULL DEFAULT 0,
  `check_relapse_contingency` tinyint(1) NOT NULL DEFAULT 0,
  `check_medical_release` tinyint(1) NOT NULL DEFAULT 0,
  `check_property_list` tinyint(1) NOT NULL DEFAULT 0,
  `member_initials_1` varchar(20) NOT NULL DEFAULT '',
  `president_initials_1` varchar(20) NOT NULL DEFAULT '',
  `member_initials_2` varchar(20) NOT NULL DEFAULT '',
  `president_initials_2` varchar(20) NOT NULL DEFAULT '',
  `member_initials_3` varchar(20) NOT NULL DEFAULT '',
  `president_initials_3` varchar(20) NOT NULL DEFAULT '',
  `member_initials_4` varchar(20) NOT NULL DEFAULT '',
  `president_initials_4` varchar(20) NOT NULL DEFAULT '',
  `member_initials_5` varchar(20) NOT NULL DEFAULT '',
  `president_initials_5` varchar(20) NOT NULL DEFAULT '',
  `member_initials_6` varchar(20) NOT NULL DEFAULT '',
  `president_initials_6` varchar(20) NOT NULL DEFAULT '',
  `member_initials_7` varchar(20) NOT NULL DEFAULT '',
  `president_initials_7` varchar(20) NOT NULL DEFAULT '',
  `member_initials_8` varchar(20) NOT NULL DEFAULT '',
  `president_initials_8` varchar(20) NOT NULL DEFAULT '',
  `member_signature_1` varchar(255) NOT NULL DEFAULT '',
  `member_signature_date_1` date DEFAULT NULL,
  `president_signature_1` varchar(255) NOT NULL DEFAULT '',
  `president_signature_date_1` date DEFAULT NULL,
  `membership_agreement_text` longtext DEFAULT NULL,
  `agreement_member_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_member_date` date DEFAULT NULL,
  `agreement_president_name` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_signature` varchar(255) NOT NULL DEFAULT '',
  `agreement_president_date` date DEFAULT NULL,
  `plan_name` varchar(255) NOT NULL DEFAULT '',
  `plan_text` longtext DEFAULT NULL,
  `aftercare_program` longtext DEFAULT NULL,
  `has_sponsor` varchar(10) NOT NULL DEFAULT '',
  `sponsor_by_date` date DEFAULT NULL,
  `meetings_per_week` varchar(50) NOT NULL DEFAULT '',
  `meeting_types` varchar(255) NOT NULL DEFAULT '',
  `plan_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_signature_date` date DEFAULT NULL,
  `plan_president_signature` varchar(255) NOT NULL DEFAULT '',
  `plan_president_date` date DEFAULT NULL,
  `relapse_name` varchar(255) NOT NULL DEFAULT '',
  `relapse_family` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_friend` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_detox` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other` tinyint(1) NOT NULL DEFAULT 0,
  `relapse_other_text` varchar(255) NOT NULL DEFAULT '',
  `relapse_details` longtext DEFAULT NULL,
  `notify_rows_json` longtext DEFAULT NULL,
  `pickup_rows_json` longtext DEFAULT NULL,
  `relapse_member_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_member_date` date DEFAULT NULL,
  `relapse_president_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_president_date` date DEFAULT NULL,
  `relapse_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `relapse_witness_date` date DEFAULT NULL,
  `medical_name` varchar(255) NOT NULL DEFAULT '',
  `physician_name` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(50) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance_info` varchar(255) NOT NULL DEFAULT '',
  `allergies` longtext DEFAULT NULL,
  `medications` longtext DEFAULT NULL,
  `medical_history` longtext DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `blood_type` varchar(20) NOT NULL DEFAULT '',
  `medical_contacts_json` longtext DEFAULT NULL,
  `medical_signature` varchar(255) NOT NULL DEFAULT '',
  `medical_date` date DEFAULT NULL,
  `property_name` varchar(255) NOT NULL DEFAULT '',
  `property_move_in_date` date DEFAULT NULL,
  `property_rows_json` longtext DEFAULT NULL,
  `uploaded_copy_name` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_path` varchar(255) NOT NULL DEFAULT '',
  `uploaded_copy_mime` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_red_creek_member_packets`
--

CREATE TABLE `oxford_red_creek_member_packets` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `move_in_date` date DEFAULT NULL,
  `signature_date` date DEFAULT NULL,
  `refund_move_in_date` date DEFAULT NULL,
  `new_member_signature` varchar(255) NOT NULL DEFAULT '',
  `new_member_signature_date` date DEFAULT NULL,
  `president_hsr_signature` varchar(255) NOT NULL DEFAULT '',
  `president_hsr_signature_date` date DEFAULT NULL,
  `expectations_signature` varchar(255) NOT NULL DEFAULT '',
  `expectations_signature_date` date DEFAULT NULL,
  `medication_signature` varchar(255) NOT NULL DEFAULT '',
  `medication_signature_date` date DEFAULT NULL,
  `emergency_name` varchar(255) NOT NULL DEFAULT '',
  `emergency_age` varchar(50) NOT NULL DEFAULT '',
  `emergency_dob` varchar(50) NOT NULL DEFAULT '',
  `blood_type` varchar(50) NOT NULL DEFAULT '',
  `primary_physician` varchar(255) NOT NULL DEFAULT '',
  `physician_phone` varchar(100) NOT NULL DEFAULT '',
  `hospital_clinic` varchar(255) NOT NULL DEFAULT '',
  `insurance` varchar(255) NOT NULL DEFAULT '',
  `allergies` text DEFAULT NULL,
  `medications` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `contact1_name` varchar(255) NOT NULL DEFAULT '',
  `contact1_phone` varchar(100) NOT NULL DEFAULT '',
  `contact2_name` varchar(255) NOT NULL DEFAULT '',
  `contact2_phone` varchar(100) NOT NULL DEFAULT '',
  `contact3_name` varchar(255) NOT NULL DEFAULT '',
  `contact3_phone` varchar(100) NOT NULL DEFAULT '',
  `property_items` longtext DEFAULT NULL,
  `property_owner_name` varchar(255) NOT NULL DEFAULT '',
  `property_signature` varchar(255) NOT NULL DEFAULT '',
  `property_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `property_date` date DEFAULT NULL,
  `property_removed_date` date DEFAULT NULL,
  `property_removed_witness_signature` varchar(255) NOT NULL DEFAULT '',
  `ees_amount` decimal(10,2) DEFAULT NULL,
  `move_in_fee` decimal(10,2) DEFAULT 250.00,
  `other_charge` decimal(10,2) DEFAULT 0.00,
  `total_due` decimal(10,2) DEFAULT NULL,
  `scan_path` varchar(500) NOT NULL DEFAULT '',
  `scan_original_name` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_residency_forms`
--

CREATE TABLE `oxford_residency_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(255) DEFAULT '',
  `letter_date` varchar(20) DEFAULT '',
  `house_name` varchar(255) DEFAULT '',
  `accepted_date` varchar(20) DEFAULT '',
  `address_line` varchar(255) DEFAULT '',
  `city_state_zip` varchar(255) DEFAULT '',
  `move_in_fee` decimal(10,2) DEFAULT 0.00,
  `weekly_rent` decimal(10,2) DEFAULT 0.00,
  `president_contact` varchar(255) DEFAULT '',
  `president_name` varchar(255) DEFAULT '',
  `president_signature` varchar(255) DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oxford_shopping_lists`
--

CREATE TABLE `oxford_shopping_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `shopping_date` date DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Oxford House Shopping List',
  `items_json` longtext NOT NULL,
  `checked_json` longtext NOT NULL,
  `total_checked` int(11) NOT NULL DEFAULT 0,
  `total_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_items` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy_path` varchar(500) DEFAULT NULL,
  `uploaded_copy_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledgers`
--

CREATE TABLE `petty_cash_ledgers` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `ledger_date` date DEFAULT NULL,
  `beginning_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_ledger_rows`
--

CREATE TABLE `petty_cash_ledger_rows` (
  `id` int(10) UNSIGNED NOT NULL,
  `ledger_id` int(10) UNSIGNED NOT NULL,
  `row_index` int(11) NOT NULL,
  `txn_date` varchar(50) NOT NULL DEFAULT '',
  `products_purchased` varchar(255) NOT NULL DEFAULT '',
  `vendor` varchar(255) NOT NULL DEFAULT '',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reimbursement_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `safety_inspection_checklists`
--

CREATE TABLE `safety_inspection_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `house_name` varchar(255) NOT NULL DEFAULT '',
  `inspection_date` date DEFAULT NULL,
  `inspector_name` varchar(255) NOT NULL DEFAULT '',
  `checklist_json` longtext NOT NULL,
  `satisfactory_total` int(11) NOT NULL DEFAULT 0,
  `unsatisfactory_total` int(11) NOT NULL DEFAULT 0,
  `completed_total` int(11) NOT NULL DEFAULT 0,
  `uploaded_copy` varchar(500) DEFAULT NULL,
  `original_upload_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_checklist_key` (`checklist_key`);

--
-- Indexes for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_meeting_date` (`meeting_date`);

--
-- Indexes for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_dates` (`week_start`,`week_end`);

--
-- Indexes for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_tour_date` (`tour_date`);

--
-- Indexes for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `house_name` (`house_name`);

--
-- Indexes for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_label` (`schedule_label`);

--
-- Indexes for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`);

--
-- Indexes for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_name_idx` (`tenant_name`),
  ADD KEY `updated_at_idx` (`updated_at`);

--
-- Indexes for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resident_name` (`resident_name`),
  ADD KEY `idx_sheet_date` (`sheet_date`);

--
-- Indexes for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sheet_id` (`sheet_id`),
  ADD KEY `idx_row_number` (`row_number`);

--
-- Indexes for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_form_date` (`form_date`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_contract_date` (`contract_date`);

--
-- Indexes for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date_completed` (`date_completed`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- Indexes for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_dates` (`house_name`,`date_from`,`date_to`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_date_from` (`date_from`),
  ADD KEY `idx_date_to` (`date_to`);

--
-- Indexes for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_week_start` (`week_start`),
  ADD KEY `idx_week_end` (`week_end`);

--
-- Indexes for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_meeting_date` (`meeting_date`),
  ADD KEY `idx_house_date` (`house_name`,`meeting_date`);

--
-- Indexes for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_member_date_house` (`member_name`,`contract_date`,`house_name`);

--
-- Indexes for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_member` (`house_name`,`member_name`);

--
-- Indexes for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_name` (`member_name`),
  ADD KEY `idx_house_name` (`house_name`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_shopping_date` (`shopping_date`);

--
-- Indexes for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_house_date` (`house_name`,`ledger_date`);

--
-- Indexes for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_petty_cash_ledger_rows_ledger` (`ledger_id`);

--
-- Indexes for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inspection_date` (`inspection_date`),
  ADD KEY `idx_house_name` (`house_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bedroom_essentials_checklists`
--
ALTER TABLE `bedroom_essentials_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chapter_meeting_minutes`
--
ALTER TABLE `chapter_meeting_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ees_member_ledger`
--
ALTER TABLE `ees_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_ledger_records`
--
ALTER TABLE `house_ledger_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_tour_forms`
--
ALTER TABLE `house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_houses`
--
ALTER TABLE `house_visit_houses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_reports`
--
ALTER TABLE `house_visit_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_visit_schedules`
--
ALTER TABLE `house_visit_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `housing_service_representative_reports`
--
ALTER TABLE `housing_service_representative_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hsc_meeting_minutes_json`
--
ALTER TABLE `hsc_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `landlord_verification_forms`
--
ALTER TABLE `landlord_verification_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_count_sheets`
--
ALTER TABLE `medication_count_sheets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_count_sheet_rows`
--
ALTER TABLE `medication_count_sheet_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `new_house_tour_forms`
--
ALTER TABLE `new_house_tour_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nightly_kitchen_schedules`
--
ALTER TABLE `nightly_kitchen_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_chore_lists`
--
ALTER TABLE `oxford_chore_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_disruptive_contracts`
--
ALTER TABLE `oxford_disruptive_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_financial_audits`
--
ALTER TABLE `oxford_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_financial_audits`
--
ALTER TABLE `oxford_house_financial_audits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_financial_reports`
--
ALTER TABLE `oxford_house_financial_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_ledger_forms`
--
ALTER TABLE `oxford_house_ledger_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_meeting_minutes_json`
--
ALTER TABLE `oxford_house_meeting_minutes_json`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_member_ledger`
--
ALTER TABLE `oxford_house_member_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_house_minutes`
--
ALTER TABLE `oxford_house_minutes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_interview_minutes`
--
ALTER TABLE `oxford_interview_minutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_member_financial_contracts`
--
ALTER TABLE `oxford_member_financial_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_new_member_packets`
--
ALTER TABLE `oxford_new_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_red_creek_member_packets`
--
ALTER TABLE `oxford_red_creek_member_packets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_residency_forms`
--
ALTER TABLE `oxford_residency_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oxford_shopping_lists`
--
ALTER TABLE `oxford_shopping_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_ledgers`
--
ALTER TABLE `petty_cash_ledgers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_ledger_rows`
--
ALTER TABLE `petty_cash_ledger_rows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `safety_inspection_checklists`
--
ALTER TABLE `safety_inspection_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
