-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2025 at 02:32 AM
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
-- Database: `kawit_rhu`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `target_audience` enum('all','patients','staff','rhu_staff','bhs_staff','pharmacy_staff') DEFAULT 'all',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `featured` tinyint(1) DEFAULT 0,
  `publish_date` datetime DEFAULT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `view_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `author_id`, `target_audience`, `priority`, `status`, `featured`, `publish_date`, `expiry_date`, `attachment_path`, `view_count`, `created_at`, `updated_at`) VALUES
(1, 'Welcome to Kawit RHU Online Portal', 'We are pleased to announce the launch of our new online health portal. Patients can now book appointments, request medical certificates, and access their health records online.', 1, 'all', 'high', 'published', 1, '2025-09-27 21:00:11', NULL, NULL, 0, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(2, 'Free Flu Vaccination Program', 'The Rural Health Unit will be conducting free flu vaccinations for senior citizens and children under 5 years old starting next Monday.', 3, 'patients', 'high', 'published', 1, '2025-10-01 21:00:11', NULL, NULL, 0, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(3, 'Online Consultation Now Available', 'You can now request online consultations through our portal. Submit your request and our doctors will schedule a Google Meet session with you.', 3, 'patients', 'high', 'published', 1, '2025-10-03 21:00:11', NULL, NULL, 0, '2025-10-04 13:00:11', '2025-10-04 13:00:11');

-- --------------------------------------------------------

--
-- Table structure for table `appointments_archive`
--

CREATE TABLE `appointments_archive` (
  `id` int(11) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `patient_id` int(11) NOT NULL,
  `service_type_id` int(11) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `appointment_location` enum('RHU','BHS') DEFAULT 'RHU',
  `barangay_id` int(11) DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled','no-show','rescheduled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `reason_for_visit` text DEFAULT NULL,
  `assigned_staff` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `confirmed_by` int(11) DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `cancelled_reason` text DEFAULT NULL,
  `rescheduled_from` int(11) DEFAULT NULL,
  `reminder_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments_archive`
--

INSERT INTO `appointments_archive` (`id`, `reference_id`, `reference_type`, `patient_id`, `service_type_id`, `appointment_date`, `appointment_time`, `appointment_location`, `barangay_id`, `status`, `notes`, `reason_for_visit`, `assigned_staff`, `created_by`, `confirmed_by`, `confirmed_at`, `cancelled_reason`, `rescheduled_from`, `reminder_sent`, `created_at`, `updated_at`) VALUES
(30, 17, 'medical_certificate', 1, NULL, '2025-10-18', '09:00:00', 'RHU', NULL, 'completed', 'Please bring a valid ID and arrive 10 minutes early.', 'Medical Certificate Check-up', NULL, 2, NULL, NULL, NULL, NULL, 0, '2025-10-16 16:26:43', '2025-10-16 16:27:16'),
(31, 24, 'medical_certificate', 6, NULL, '2025-10-18', '09:00:00', 'RHU', NULL, 'completed', 'Please bring a valid ID and arrive 10 minutes early.', 'Medical Certificate Check-up', NULL, 2, NULL, NULL, NULL, NULL, 0, '2025-10-17 07:47:55', '2025-10-17 07:59:42'),
(32, 18, 'medical_certificate', 1, NULL, '2025-10-21', '09:00:00', 'RHU', NULL, 'completed', 'Please bring a valid ID and arrive 10 minutes early.', 'Medical Certificate Check-up', NULL, 2, NULL, NULL, NULL, NULL, 0, '2025-10-17 12:57:02', '2025-10-17 12:57:09'),
(33, 32, 'medical_certificate', 1, NULL, '2025-10-18', '00:00:00', 'RHU', NULL, 'completed', 'Please bring a valid ID and arrive 10 minutes early.', 'Medical Certificate Check-up', NULL, 2, NULL, NULL, NULL, NULL, 0, '2025-10-17 13:22:23', '2025-10-17 13:22:43'),
(34, 33, 'medical_certificate', 6, NULL, '2025-10-19', '09:00:00', 'RHU', NULL, 'completed', 'Please bring a valid ID and arrive 10 minutes early.', 'Medical Certificate Check-up', NULL, 2, NULL, NULL, NULL, NULL, 0, '2025-10-18 03:28:00', '2025-10-18 03:28:20');

-- --------------------------------------------------------

--
-- Table structure for table `barangays`
--

CREATE TABLE `barangays` (
  `id` int(11) NOT NULL,
  `barangay_name` varchar(100) NOT NULL,
  `bhs_address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `bhs_coordinator_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangays`
--

INSERT INTO `barangays` (`id`, `barangay_name`, `bhs_address`, `contact_number`, `bhs_coordinator_id`, `is_active`, `created_at`) VALUES
(1, 'Binakayan-Kanluran', 'BHS Binakayan-Kanluran, Kawit, Cavite', '046-434-0001', NULL, 1, '2025-10-04 13:00:11'),
(2, 'Binakayan-Silangan', 'BHS Binakayan-Silangan, Kawit, Cavite', '046-434-0002', NULL, 1, '2025-10-04 13:00:11'),
(3, 'Kaingen', 'BHS Kaingen, Kawit, Cavite', '046-434-0003', NULL, 1, '2025-10-04 13:00:11'),
(4, 'Gahak', 'BHS Gahak, Kawit, Cavite', '046-434-0004', NULL, 1, '2025-10-04 13:00:11'),
(5, 'Panamitan', 'BHS Panamitan, Kawit, Cavite', '046-434-0005', NULL, 1, '2025-10-04 13:00:11'),
(6, 'Marulas', 'BHS Marulas, Kawit, Cavite', '046-434-0006', NULL, 1, '2025-10-04 13:00:11'),
(7, 'Putik', 'BHS Putik, Kawit, Cavite', '046-434-0007', NULL, 1, '2025-10-04 13:00:11'),
(8, 'Tabon I', 'BHS Tabon I, Kawit, Cavite', '046-434-0008', NULL, 1, '2025-10-04 13:00:11'),
(9, 'Tabon II', 'BHS Tabon II, Kawit, Cavite', '046-434-0009', NULL, 1, '2025-10-04 13:00:11'),
(10, 'Tabon III', 'BHS Tabon III, Kawit, Cavite', '046-434-0010', NULL, 1, '2025-10-04 13:00:11');

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE `consultations` (
  `id` int(11) NOT NULL,
  `consultation_number` varchar(20) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `philhealth_no` varchar(50) DEFAULT NULL,
  `osca_number` varchar(50) DEFAULT NULL,
  `family_number` varchar(50) DEFAULT NULL,
  `pwd_status` enum('YES','NO') DEFAULT 'NO',
  `nhts_4ps_status` enum('YES','NO') DEFAULT 'NO',
  `house_number` varchar(50) DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `municipality` varchar(100) DEFAULT 'Kawit',
  `contact_number` varchar(20) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `educational_attainment` varchar(100) DEFAULT NULL,
  `guardian_name` varchar(200) DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `consultation_type` enum('online','walk-in','follow-up','emergency') NOT NULL,
  `consultation_date` datetime NOT NULL,
  `scheduled_date` date DEFAULT NULL,
  `scheduled_time` time DEFAULT NULL,
  `chief_complaint` text DEFAULT NULL,
  `medication_taken` text DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `blood_pressure_systolic` int(11) DEFAULT NULL,
  `blood_pressure_diastolic` int(11) DEFAULT NULL,
  `heart_rate` int(11) DEFAULT NULL,
  `respiratory_rate` int(11) DEFAULT NULL,
  `oxygen_saturation` int(11) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `bmi` decimal(4,1) DEFAULT NULL,
  `current_smoker` enum('YES','NO') DEFAULT 'NO',
  `alcohol_intake` enum('YES','NO') DEFAULT 'NO',
  `illicit_drug_use` enum('YES','NO') DEFAULT 'NO',
  `eat_unhealthy_foods` enum('YES','NO') DEFAULT 'NO',
  `regular_exercise` enum('YES','NO') DEFAULT 'NO',
  `with_hypertension` enum('YES','NO') DEFAULT 'NO',
  `hypertension_meds_from_rhu` enum('YES','NO') DEFAULT 'NO',
  `with_diabetes` enum('YES','NO') DEFAULT 'NO',
  `diabetes_meds_from_rhu` enum('YES','NO') DEFAULT 'NO',
  `general_appearance` text DEFAULT NULL,
  `heent_exam` text DEFAULT NULL,
  `respiratory_exam` text DEFAULT NULL,
  `cardiovascular_exam` text DEFAULT NULL,
  `abdomen_exam` text DEFAULT NULL,
  `musculoskeletal_exam` text DEFAULT NULL,
  `history_of_present_illness` text DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `vital_signs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vital_signs`)),
  `physical_examination` text DEFAULT NULL,
  `assessment` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `assessment_notes` text DEFAULT NULL,
  `fitness_status` varchar(50) DEFAULT NULL,
  `restrictions` text DEFAULT NULL,
  `treatment_plan` text DEFAULT NULL,
  `medications_prescribed` text DEFAULT NULL,
  `prescribed_by_name` varchar(200) DEFAULT NULL,
  `prescribed_by_signature` varchar(255) DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `follow_up_instructions` text DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `assigned_doctor` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `consultation_location` enum('RHU','BHS') DEFAULT 'RHU',
  `google_meet_link` varchar(500) DEFAULT NULL,
  `patient_notes` text DEFAULT NULL,
  `barangay_id` int(11) DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consultations`
--

INSERT INTO `consultations` (`id`, `consultation_number`, `patient_id`, `philhealth_no`, `osca_number`, `family_number`, `pwd_status`, `nhts_4ps_status`, `house_number`, `street`, `municipality`, `contact_number`, `occupation`, `educational_attainment`, `guardian_name`, `blood_type`, `appointment_id`, `consultation_type`, `consultation_date`, `scheduled_date`, `scheduled_time`, `chief_complaint`, `medication_taken`, `temperature`, `blood_pressure_systolic`, `blood_pressure_diastolic`, `heart_rate`, `respiratory_rate`, `oxygen_saturation`, `weight`, `height`, `bmi`, `current_smoker`, `alcohol_intake`, `illicit_drug_use`, `eat_unhealthy_foods`, `regular_exercise`, `with_hypertension`, `hypertension_meds_from_rhu`, `with_diabetes`, `diabetes_meds_from_rhu`, `general_appearance`, `heent_exam`, `respiratory_exam`, `cardiovascular_exam`, `abdomen_exam`, `musculoskeletal_exam`, `history_of_present_illness`, `symptoms`, `vital_signs`, `physical_examination`, `assessment`, `diagnosis`, `assessment_notes`, `fitness_status`, `restrictions`, `treatment_plan`, `medications_prescribed`, `prescribed_by_name`, `prescribed_by_signature`, `recommendations`, `follow_up_instructions`, `priority`, `status`, `rejection_reason`, `assigned_doctor`, `approved_by`, `approved_at`, `consultation_location`, `google_meet_link`, `patient_notes`, `barangay_id`, `follow_up_date`, `created_at`, `updated_at`) VALUES
(17, 'CONS-2025-0001', 5, NULL, NULL, NULL, 'NO', 'NO', NULL, NULL, 'Kawit', NULL, NULL, NULL, NULL, NULL, NULL, 'online', '2025-10-14 13:00:00', NULL, NULL, 'n/a', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', NULL, NULL, NULL, NULL, NULL, NULL, '', 'fever', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'completed', NULL, 2, NULL, NULL, 'RHU', NULL, 'n/a', NULL, NULL, '2025-10-14 03:42:08', '2025-10-17 05:53:48'),
(18, 'CONS-2025-0002', 6, NULL, NULL, NULL, 'NO', 'NO', NULL, NULL, 'Kawit', NULL, NULL, NULL, NULL, NULL, NULL, 'online', '2025-10-14 01:00:00', NULL, NULL, 'inuubo grabe', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', NULL, NULL, NULL, NULL, NULL, NULL, '', 'cough', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'completed', NULL, 2, NULL, NULL, 'RHU', NULL, 'inuubo grabe', NULL, NULL, '2025-10-14 03:44:01', '2025-10-16 12:29:55'),
(19, '', 6, NULL, NULL, NULL, 'NO', 'NO', NULL, NULL, 'Kawit', NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-10-17 00:00:00', NULL, NULL, 'Employment', NULL, 36.5, 120, 80, 72, 16, 98, NULL, NULL, NULL, 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'Alert, well-nourished, no distress', 'No abnormalities detected', 'Clear breath sounds bilaterally', 'Regular rhythm, no murmurs', 'Soft, non-tender', 'Full range of motion, no deformities', NULL, NULL, NULL, NULL, NULL, 'No acute illness detected. Patient is healthy and cleared for normal activities.', 'Medical certificate examination completed', 'Fit', 'dont drink alcohol', NULL, NULL, NULL, NULL, 'Maintain healthy lifestyle. Follow-up if symptoms develop.', NULL, 'medium', 'completed', NULL, 2, NULL, NULL, 'RHU', NULL, NULL, NULL, NULL, '2025-10-17 12:16:24', '2025-10-17 12:16:24'),
(31, 'CONS-2025-0003', 1, NULL, NULL, NULL, 'NO', 'NO', NULL, NULL, 'Kawit', NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-10-17 00:00:00', NULL, NULL, 'School Requirements', NULL, 36.5, 120, 80, 72, 16, 98, NULL, NULL, NULL, 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'Alert, well-nourished, no distress', 'No abnormalities detected', 'Clear breath sounds bilaterally', 'Regular rhythm, no murmurs', 'Soft, non-tender', 'Full range of motion, no deformities', NULL, NULL, NULL, NULL, NULL, 'No acute illness detected. Patient is healthy and cleared for normal activities.', 'Medical certificate examination completed', 'Fit', 'dont drink alcohol', NULL, NULL, NULL, NULL, 'Maintain healthy lifestyle. Follow-up if symptoms develop.', NULL, 'medium', 'completed', NULL, 2, NULL, NULL, 'RHU', NULL, NULL, NULL, NULL, '2025-10-17 13:08:26', '2025-10-17 13:08:26'),
(32, 'CONS-2025-0004', 1, NULL, NULL, NULL, 'NO', 'NO', NULL, NULL, 'Kawit', NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-10-17 00:00:00', NULL, NULL, 'Employment', NULL, 36.5, 120, 80, 72, 16, 98, NULL, NULL, NULL, 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'Alert, well-nourished, no distress', 'No abnormalities detected', 'Clear breath sounds bilaterally', 'Regular rhythm, no murmurs', 'Soft, non-tender', 'Full range of motion, no deformities', NULL, NULL, NULL, NULL, NULL, 'No acute illness detected. Patient is healthy and cleared for normal activities.', 'Medical certificate examination completed', 'Fit', 'dont drink alcohol', NULL, NULL, NULL, NULL, 'Maintain healthy lifestyle. Follow-up if symptoms develop.', NULL, 'medium', 'completed', NULL, 2, NULL, NULL, 'RHU', NULL, NULL, NULL, NULL, '2025-10-17 13:23:00', '2025-10-17 13:23:00'),
(33, 'CONS-2025-0005', 6, NULL, NULL, NULL, 'NO', 'NO', NULL, NULL, 'Kawit', NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-10-18 00:00:00', NULL, NULL, 'Employment', NULL, 36.5, 120, 80, 72, 16, 98, NULL, NULL, NULL, 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'Alert, well-nourished, no distress', 'No abnormalities detected', 'Clear breath sounds bilaterally', 'Regular rhythm, no murmurs', 'Soft, non-tender', 'Full range of motion, no deformities', NULL, NULL, NULL, NULL, NULL, 'No acute illness detected. Patient is healthy and cleared for normal activities.', 'Medical certificate examination completed', 'Fit', NULL, NULL, NULL, NULL, NULL, 'Maintain healthy lifestyle. Follow-up if symptoms develop.', NULL, 'medium', 'completed', NULL, 2, NULL, NULL, 'RHU', NULL, NULL, NULL, NULL, '2025-10-18 04:02:03', '2025-10-18 04:02:03'),
(34, 'CONS-2025-0006', 7, '44-2424424-4242', '123456', '123456', 'NO', 'NO', '123', 'bagbag', 'Kawit', '', 'student', 'college', 'mama', '', NULL, 'walk-in', '2025-10-24 00:06:06', NULL, NULL, 'n/a', 'n/a', 0.0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.0, 'YES', 'YES', 'NO', 'NO', 'YES', 'YES', 'YES', 'YES', 'YES', NULL, NULL, NULL, NULL, NULL, NULL, '', '', '{\"temperature\":\"\",\"blood_pressure\":\"\",\"pulse_rate\":\"\",\"respiratory_rate\":\"\",\"weight\":\"\",\"height\":\"\",\"bmi\":\"\",\"oxygen_saturation\":\"\"}', '', '', 'n/a', NULL, NULL, NULL, '', 'biogesic', 'Roberto Santos', '', '', 'bawal uminom', 'medium', 'completed', NULL, 2, NULL, NULL, 'RHU', NULL, NULL, 5, '2025-10-09', '2025-10-23 16:06:06', '2025-10-23 16:22:29'),
(35, 'CONS-2025-0007', 7, '44-2424424-4242', '324344', '123456', 'NO', 'NO', '123', 'bagbag', 'Kawit', '0954458555', 'student', 'college', 'mama', '', NULL, 'walk-in', '2025-10-24 00:07:51', NULL, NULL, 'inuubo', 'no', 0.0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.0, 'YES', 'NO', 'YES', 'NO', 'YES', 'NO', 'YES', 'NO', 'NO', NULL, NULL, NULL, NULL, NULL, NULL, '', '', '{\"temperature\":\"\",\"blood_pressure\":\"\",\"pulse_rate\":\"\",\"respiratory_rate\":\"\",\"weight\":\"\",\"height\":\"\",\"bmi\":\"\",\"oxygen_saturation\":\"\"}', '', '', 'n/a', NULL, NULL, NULL, '', '', 'Roberto Santos', '', '', 'n/a', 'medium', 'completed', NULL, 2, NULL, NULL, 'RHU', NULL, NULL, NULL, '2025-10-15', '2025-10-23 16:07:51', '2025-10-23 16:40:14'),
(36, 'CONS-2025-0008', 6, '44-2424424-4242', '324344', '123456', 'NO', 'NO', '', '', 'Kawit', '', '', '', '', '', NULL, 'walk-in', '2025-10-24 00:10:12', NULL, NULL, 'n/a', 'n/a', 0.0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.0, 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'YES', NULL, NULL, NULL, NULL, NULL, NULL, '', '', '{\"temperature\":\"\",\"blood_pressure\":\"\",\"pulse_rate\":\"\",\"respiratory_rate\":\"\",\"weight\":\"\",\"height\":\"\",\"bmi\":\"\",\"oxygen_saturation\":\"\"}', '', '', 'n/a', NULL, NULL, NULL, '', '', 'Roberto Santos', '', '', 'n/a', 'medium', 'completed', NULL, 2, NULL, NULL, 'RHU', NULL, NULL, NULL, '2025-10-09', '2025-10-23 16:10:12', '2025-10-23 16:37:57'),
(37, 'CONS-2025-0009', 4, '44-2424424-4242', '324344', '123456', 'NO', 'NO', '', '', 'Kawit', '', '', '', '', '', NULL, 'walk-in', '2025-10-24 00:31:59', NULL, NULL, 'n/a', 'n/a', 0.0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.0, 'YES', 'NO', 'NO', 'NO', 'YES', 'NO', 'NO', 'NO', 'NO', NULL, NULL, NULL, NULL, NULL, NULL, '', '', '{\"temperature\":\"\",\"blood_pressure\":\"\",\"pulse_rate\":\"\",\"respiratory_rate\":\"\",\"weight\":\"\",\"height\":\"\",\"bmi\":\"\",\"oxygen_saturation\":\"\"}', '', '', 'n/a', NULL, NULL, NULL, '', '', 'Roberto Santos', '', '', 'n/a', 'medium', 'completed', NULL, 2, NULL, NULL, 'RHU', NULL, NULL, NULL, '2025-10-16', '2025-10-23 16:31:59', '2025-10-23 16:31:59'),
(38, 'CONS-2025-0010', 4, NULL, NULL, NULL, 'NO', 'NO', NULL, NULL, 'Kawit', NULL, NULL, NULL, NULL, NULL, NULL, 'online', '2025-10-23 00:41:00', NULL, NULL, 'n/a', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', NULL, NULL, NULL, NULL, NULL, NULL, 'n/a', 'fever', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'completed', NULL, 2, NULL, NULL, 'RHU', NULL, 'n/a', NULL, NULL, '2025-10-23 16:39:43', '2025-10-23 16:40:08'),
(39, 'CONS-2025-0011', 5, '44-2424424-4242', '324344', '123456', 'NO', 'NO', '', '', 'Kawit', '', '', '', '', '', NULL, 'walk-in', '2025-10-24 00:55:10', NULL, NULL, 'n/a', '', 0.0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.0, 'YES', 'YES', 'YES', 'YES', 'YES', 'YES', 'YES', 'YES', 'YES', NULL, NULL, NULL, NULL, NULL, NULL, '', '', '{\"temperature\":\"\",\"blood_pressure\":\"\",\"pulse_rate\":\"\",\"respiratory_rate\":\"\",\"weight\":\"\",\"height\":\"\",\"bmi\":\"\",\"oxygen_saturation\":\"\"}', '', '', 'n/a', NULL, NULL, NULL, '', '', 'Roberto Santos', '', '', '', 'medium', 'completed', NULL, 2, NULL, NULL, 'RHU', NULL, NULL, NULL, '2025-10-22', '2025-10-23 16:55:10', '2025-10-23 16:55:10');

-- --------------------------------------------------------

--
-- Table structure for table `laboratory_results`
--

CREATE TABLE `laboratory_results` (
  `id` int(11) NOT NULL,
  `lab_number` varchar(20) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `test_type` varchar(100) NOT NULL,
  `test_category` enum('Hematology','Chemistry','Urinalysis','Microbiology','Serology','Parasitology','Other') NOT NULL,
  `test_date` date NOT NULL,
  `result_date` date DEFAULT NULL,
  `specimen_type` varchar(50) DEFAULT NULL,
  `test_results` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`test_results`)),
  `normal_range` text DEFAULT NULL,
  `interpretation` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `performed_by` int(11) DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `released_by` int(11) DEFAULT NULL,
  `lab_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laboratory_results`
--

INSERT INTO `laboratory_results` (`id`, `lab_number`, `patient_id`, `consultation_id`, `test_type`, `test_category`, `test_date`, `result_date`, `specimen_type`, `test_results`, `normal_range`, `interpretation`, `remarks`, `status`, `performed_by`, `verified_by`, `released_by`, `lab_notes`, `created_at`, `updated_at`) VALUES
(1, 'LAB-2024-0001', 1, NULL, 'Complete Blood Count', 'Hematology', '2025-09-29', '2025-09-30', NULL, '{\"WBC\": \"11.5\", \"RBC\": \"4.8\", \"Hemoglobin\": \"14.2\", \"Hematocrit\": \"42.1\", \"Platelet\": \"250\"}', 'WBC: 4.5-11.0 x10^9/L, RBC: 4.0-5.5 x10^12/L', 'Mild leukocytosis', NULL, 'completed', 3, 3, NULL, NULL, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(2, 'LAB-2025-0001', 6, NULL, 'complete blood count', 'Urinalysis', '2025-10-18', '2025-10-18', 'blood', '{\"Color\":\"yellow\",\"Clarity\":\"clear\",\"pH\":\"5.0\",\"Specific_Gravity\":\"1.005\",\"Protein\":\"negative\",\"Glucose\":\"negative\",\"RBC\":\"0-2\\/hpf\",\"WBC\":\"0-5\\/hpf\",\"Bacteria\":\"none\",\"Crystals\":\"none\"}', 'none', 'normal', 'none', 'completed', 2, 2, NULL, NULL, '2025-10-18 10:12:13', '2025-10-18 10:12:13');

-- --------------------------------------------------------

--
-- Table structure for table `medical_certificates`
--

CREATE TABLE `medical_certificates` (
  `id` int(11) NOT NULL,
  `certificate_number` varchar(50) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `patient_full_name_override` varchar(255) DEFAULT NULL,
  `patient_age_override` int(11) DEFAULT NULL,
  `patient_gender_override` varchar(10) DEFAULT NULL,
  `patient_address_override` text DEFAULT NULL,
  `examination_date` date DEFAULT NULL,
  `checkup_instructions` text DEFAULT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `issued_by` int(11) DEFAULT NULL,
  `assigned_doctor_id` int(11) DEFAULT NULL,
  `certificate_type` enum('Fit to Work','Medical Certificate','Health Certificate','Vaccination Certificate','Other') NOT NULL,
  `purpose` varchar(200) NOT NULL,
  `date_issued` date NOT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `impressions` text DEFAULT NULL,
  `physical_findings` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `restrictions` text DEFAULT NULL,
  `fitness_status` enum('Fit','Unfit','Conditional','Pending Further Evaluation') DEFAULT 'Fit',
  `template_used` varchar(100) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `digital_signature` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved_for_checkup','completed_checkup','ready_for_download','downloaded','cancelled','expired','draft','issued','revoked') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_certificates`
--

INSERT INTO `medical_certificates` (`id`, `certificate_number`, `patient_id`, `patient_full_name_override`, `patient_age_override`, `patient_gender_override`, `patient_address_override`, `examination_date`, `checkup_instructions`, `consultation_id`, `issued_by`, `assigned_doctor_id`, `certificate_type`, `purpose`, `date_issued`, `valid_from`, `valid_until`, `diagnosis`, `impressions`, `physical_findings`, `recommendations`, `remarks`, `restrictions`, `fitness_status`, `template_used`, `qr_code`, `digital_signature`, `status`, `created_at`, `updated_at`) VALUES
(32, 'CERT-2025-0001', 1, 'Juan Dela Cruz ', 35, 'Male', '123 Rizal St., Binakayan-Kanluran', '2025-10-17', 'Please bring a valid ID and arrive 10 minutes early.', 32, 2, 3, '', 'Employment', '2025-10-17', '2025-10-17', '2028-09-17', 'No acute illness detected. Patient is healthy and cleared for normal activities.', NULL, 'General: Alert, well-nourished, no distress\nHEENT: No abnormalities detected\nRespiratory: Clear breath sounds bilaterally\nCardiovascular: Regular rhythm, no murmurs\nAbdomen: Soft, non-tender\nMusculoskeletal: Full range of motion, no deformities', 'Maintain healthy lifestyle. Follow-up if symptoms develop.', NULL, 'dont drink alcohol', 'Fit', NULL, NULL, NULL, 'downloaded', '2025-10-17 13:21:39', '2025-10-17 13:32:11'),
(33, 'CERT-2025-0002', 6, 'jeremayah tagalog gayondato ', 23, 'Male', 'n/a', '2025-10-18', 'Please bring a valid ID and arrive 10 minutes early.', 33, 2, 3, '', 'Employment', '2025-10-18', '2025-10-18', '2026-10-18', 'No acute illness detected. Patient is healthy and cleared for normal activities.', 'No significant findings. Patient is healthy and cleared for normal activities.', 'General: Alert, well-nourished, no distress\nHEENT: No abnormalities detected\nRespiratory: Clear breath sounds bilaterally\nCardiovascular: Regular rhythm, no murmurs\nAbdomen: Soft, non-tender\nMusculoskeletal: Full range of motion, no deformities', 'Maintain healthy lifestyle. Follow-up if symptoms develop.', 'Patient cleared for normal activities with no restrictions', NULL, 'Fit', NULL, NULL, NULL, 'downloaded', '2025-10-18 03:27:41', '2025-10-18 04:02:38'),
(34, 'CERT-2025-0003', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Medical Certificate', 'Employment', '2025-10-25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Fit', NULL, NULL, NULL, 'pending', '2025-10-25 01:27:41', '2025-10-25 01:27:41');

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `id` int(11) NOT NULL,
  `medicine_code` varchar(50) NOT NULL,
  `medicine_name` varchar(100) NOT NULL,
  `generic_name` varchar(100) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `dosage_strength` varchar(50) DEFAULT NULL,
  `dosage_form` enum('Tablet','Capsule','Syrup','Injection','Drops','Cream','Ointment','Suppository','Other') DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `unit_of_measure` varchar(20) DEFAULT 'piece',
  `reorder_level` int(11) NOT NULL DEFAULT 10,
  `maximum_level` int(11) NOT NULL DEFAULT 1000,
  `expiry_date` date DEFAULT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `lot_number` varchar(50) DEFAULT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `unit_cost` decimal(10,2) DEFAULT 0.00,
  `selling_price` decimal(10,2) DEFAULT 0.00,
  `storage_location` varchar(100) DEFAULT NULL,
  `storage_temperature` varchar(50) DEFAULT NULL,
  `requires_prescription` tinyint(1) DEFAULT 0,
  `is_controlled_substance` tinyint(1) DEFAULT 0,
  `barcode` varchar(100) DEFAULT NULL,
  `manufacturer` varchar(100) DEFAULT NULL,
  `date_received` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`id`, `medicine_code`, `medicine_name`, `generic_name`, `brand`, `category_id`, `dosage_strength`, `dosage_form`, `stock_quantity`, `unit_of_measure`, `reorder_level`, `maximum_level`, `expiry_date`, `batch_number`, `lot_number`, `supplier`, `unit_cost`, `selling_price`, `storage_location`, `storage_temperature`, `requires_prescription`, `is_controlled_substance`, `barcode`, `manufacturer`, `date_received`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'MED-001', 'Paracetamol 500mg', 'Acetaminophen', 'Biogesic', 1, '500mg', 'Tablet', 1000, 'tablet', 100, 2000, '2025-12-31', NULL, NULL, 'Mercury Drug Corporation', 2.50, 3.00, NULL, NULL, 0, 0, NULL, NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(2, 'MED-002', 'Amoxicillin 500mg', 'Amoxicillin', 'Amoxil', 2, '500mg', 'Capsule', 500, 'capsule', 50, 1000, '2025-06-30', NULL, NULL, 'Mercury Drug Corporation', 8.75, 10.00, NULL, NULL, 1, 0, NULL, NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(3, 'MED-003', 'Cetirizine 10mg', 'Cetirizine HCl', 'Zyrtec', 3, '10mg', 'Tablet', 300, 'tablet', 30, 500, '2025-09-30', NULL, NULL, 'Mercury Drug Corporation', 5.00, 6.00, NULL, NULL, 0, 0, NULL, NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(4, 'MED-004', 'Losartan 50mg', 'Losartan Potassium', 'Cozaar', 4, '50mg', 'Tablet', 400, 'tablet', 40, 800, '2025-08-31', NULL, NULL, 'TGP Pharma', 12.00, 15.00, NULL, NULL, 1, 0, NULL, NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(5, 'MED-005', 'Metformin 500mg', 'Metformin HCl', 'Glucophage', 5, '500mg', 'Tablet', 600, 'tablet', 60, 1200, '2025-10-31', NULL, NULL, 'TGP Pharma', 7.50, 9.00, NULL, NULL, 1, 0, NULL, NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(6, 'MED-006', 'Salbutamol 2mg', 'Salbutamol', 'Ventolin', 6, '2mg', 'Tablet', 200, 'tablet', 20, 400, '2025-11-30', NULL, NULL, 'Mercury Drug Corporation', 6.00, 7.50, NULL, NULL, 1, 0, NULL, NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(7, 'MED-007', 'Mefenamic Acid 500mg', 'Mefenamic Acid', 'Ponstan', 7, '500mg', 'Capsule', 400, 'capsule', 40, 800, '2025-07-31', NULL, NULL, 'Mercury Drug Corporation', 8.00, 10.00, NULL, NULL, 0, 0, NULL, NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(8, 'MED-008', 'Omeprazole 20mg', 'Omeprazole', 'Losec', 8, '20mg', 'Capsule', 350, 'capsule', 35, 700, '2025-09-30', NULL, NULL, 'TGP Pharma', 10.00, 12.50, NULL, NULL, 1, 0, NULL, NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(9, 'MED-009', 'Ascorbic Acid 500mg', 'Vitamin C', 'Cecon', 9, '500mg', 'Tablet', 800, 'tablet', 80, 1500, '2025-12-31', NULL, NULL, 'Mercury Drug Corporation', 3.00, 4.00, NULL, NULL, 0, 0, NULL, NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(10, 'MED-010', 'Betadine Solution', 'Povidone Iodine', 'Betadine', 10, '10%', '', 50, 'bottle', 10, 100, '2025-08-31', NULL, NULL, 'Mercury Drug Corporation', 85.00, 100.00, NULL, NULL, 0, 0, NULL, NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_categories`
--

CREATE TABLE `medicine_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_controlled` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_categories`
--

INSERT INTO `medicine_categories` (`id`, `category_name`, `description`, `is_controlled`, `is_active`, `created_at`) VALUES
(1, 'Analgesics/Antipyretics', 'Pain relievers and fever reducers', 0, 1, '2025-10-04 13:00:11'),
(2, 'Antibiotics', 'Antimicrobial medications', 1, 1, '2025-10-04 13:00:11'),
(3, 'Antihistamines', 'Allergy medications', 0, 1, '2025-10-04 13:00:11'),
(4, 'Antihypertensives', 'Blood pressure medications', 1, 1, '2025-10-04 13:00:11'),
(5, 'Antidiabetics', 'Diabetes medications', 1, 1, '2025-10-04 13:00:11'),
(6, 'Bronchodilators', 'Respiratory medications', 1, 1, '2025-10-04 13:00:11'),
(7, 'Anti-inflammatory', 'Inflammation reducers', 0, 1, '2025-10-04 13:00:11'),
(8, 'Gastrointestinal', 'Stomach and digestive medications', 0, 1, '2025-10-04 13:00:11'),
(9, 'Vitamins/Supplements', 'Nutritional supplements', 0, 1, '2025-10-04 13:00:11'),
(10, 'Topical Medications', 'Creams and ointments', 0, 1, '2025-10-04 13:00:11');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_transactions`
--

CREATE TABLE `medicine_transactions` (
  `id` int(11) NOT NULL,
  `transaction_number` varchar(20) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `transaction_type` enum('stock_in','dispensed','expired','damaged','returned','adjustment','transfer') NOT NULL,
  `quantity_before` int(11) NOT NULL,
  `quantity_transacted` int(11) NOT NULL,
  `quantity_after` int(11) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `transaction_date` datetime NOT NULL,
  `performed_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_transactions`
--

INSERT INTO `medicine_transactions` (`id`, `transaction_number`, `medicine_id`, `transaction_type`, `quantity_before`, `quantity_transacted`, `quantity_after`, `reference_id`, `reference_type`, `unit_cost`, `total_cost`, `transaction_date`, `performed_by`, `approved_by`, `notes`, `created_at`) VALUES
(1, 'TXN-2024-0001', 1, 'dispensed', 1012, 12, 1000, 1, 'prescription', NULL, NULL, '2025-09-29 21:00:11', 7, NULL, NULL, '2025-10-04 13:00:11'),
(2, 'TXN-2024-0002', 2, 'dispensed', 521, 21, 500, 2, 'prescription', NULL, NULL, '2025-10-01 21:00:11', 7, NULL, NULL, '2025-10-04 13:00:11');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('appointment_reminder','lab_result','prescription_ready','announcement','system','referral_update','consultation_ready') NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `is_read` tinyint(1) DEFAULT 0,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `data`, `is_read`, `priority`, `expires_at`, `created_at`, `read_at`) VALUES
(1, 9, 'appointment_reminder', 'Appointment Reminder', 'You have an appointment tomorrow at 9:00 AM', '{\"appointment_id\": 1}', 0, 'medium', NULL, '2025-10-04 13:00:11', NULL),
(2, 10, 'consultation_ready', 'Online Consultation Ready', 'Your doctor is ready for your consultation. Click to join the Google Meet session.', '{\"consultation_id\": 2}', 0, 'high', NULL, '2025-10-04 13:00:11', NULL),
(3, 9, 'system', 'Consultation Request Submitted', 'Your online consultation has been started. A doctor will review your case soon. Consultation number: CONS-2025-0001', '{\"consultation_id\":\"4\",\"consultation_number\":\"CONS-2025-0001\"}', 0, '', NULL, '2025-10-05 01:41:31', NULL),
(4, 9, 'consultation_ready', 'Online Consultation Ready', 'Your online consultation is ready! Join the video call. Consultation #: CONS-2025-0001', '{\"consultation_id\":4,\"google_meet_link\":\"https:\\/\\/meet.google.com\\/fff-kkgf-hqz\"}', 0, 'high', NULL, '2025-10-05 02:41:09', NULL),
(5, 10, 'system', 'Consultation Request Submitted', 'Your online consultation has been started. A doctor will review your case soon. Consultation number: CONS-2025-0002', '{\"consultation_id\":\"5\",\"consultation_number\":\"CONS-2025-0002\"}', 0, 'high', NULL, '2025-10-05 05:06:28', NULL),
(6, 10, 'consultation_ready', 'Online Consultation Ready', 'Your online consultation is ready! Click to join the video call. Consultation #: CONS-2025-0002', '{\"consultation_id\":5,\"consultation_number\":\"CONS-2025-0002\",\"google_meet_link\":\"https:\\/\\/meet.google.com\\/uah-msqo-run\"}', 0, 'high', NULL, '2025-10-05 05:08:15', NULL),
(7, 10, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0002', '{\"consultation_id\":\"5\"}', 0, 'medium', NULL, '2025-10-05 07:48:28', NULL),
(8, 9, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0001', '{\"consultation_id\":\"4\"}', 0, 'medium', NULL, '2025-10-05 07:48:31', NULL),
(9, 9, 'system', 'Consultation Request Submitted', 'Your online consultation request has been submitted. Please wait for admin approval. Consultation number: CONS-2025-0003', '{\"consultation_id\":\"6\",\"consultation_number\":\"CONS-2025-0003\"}', 0, 'low', NULL, '2025-10-05 08:00:27', NULL),
(10, 9, 'consultation_ready', 'Consultation Scheduled', 'Your online consultation has been scheduled for October 5, 2025 @ 4:00 PM. Please be online 5 minutes early. Consultation #: CONS-2025-0003', '{\"consultation_id\":6,\"consultation_number\":\"CONS-2025-0003\",\"scheduled_time\":\"2025-10-05 16:00\"}', 0, 'high', NULL, '2025-10-05 08:01:54', NULL),
(11, 10, 'system', 'Consultation Request Submitted', 'Your online consultation request has been submitted. Please wait for admin approval. Consultation number: CONS-2025-0004', '{\"consultation_id\":\"7\",\"consultation_number\":\"CONS-2025-0004\"}', 0, 'low', NULL, '2025-10-05 08:02:52', NULL),
(12, 10, 'consultation_ready', 'Consultation Scheduled', 'Your online consultation has been scheduled for October 5, 2025 @ 5:00 AM. Please be online 5 minutes early. Consultation #: CONS-2025-0004', '{\"consultation_id\":7,\"consultation_number\":\"CONS-2025-0004\",\"scheduled_time\":\"2025-10-05 05:00\"}', 0, 'high', NULL, '2025-10-05 08:12:27', NULL),
(13, 10, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0004', '{\"consultation_id\":\"7\"}', 0, 'medium', NULL, '2025-10-05 08:12:35', NULL),
(14, 9, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0003', '{\"consultation_id\":\"6\"}', 0, 'medium', NULL, '2025-10-05 08:12:39', NULL),
(15, 9, 'system', 'Consultation Request Submitted', 'Your online consultation request has been submitted. Please wait for admin approval. Consultation number: CONS-2025-0005', '{\"consultation_id\":\"8\",\"consultation_number\":\"CONS-2025-0005\"}', 0, 'medium', NULL, '2025-10-05 08:37:24', NULL),
(16, 9, 'consultation_ready', 'Consultation Scheduled', 'Your online consultation has been scheduled for October 5, 2025 @ 6:53 AM. Please be online 5 minutes early. Consultation #: CONS-2025-0005', '{\"consultation_id\":8,\"consultation_number\":\"CONS-2025-0005\",\"scheduled_time\":\"2025-10-05 06:53\",\"google_meet_link\":null}', 0, 'high', NULL, '2025-10-05 08:51:37', NULL),
(17, 9, 'appointment_reminder', 'Appointment Booked', 'Your appointment has been successfully booked for October 5, 2025 at 10:00 AM', '{\"appointment_id\":\"10\"}', 0, 'medium', NULL, '2025-10-05 12:32:50', NULL),
(18, 9, 'system', 'Medical Certificate Ready', 'Your medical certificate (CERT-2025-0003) has been issued and is ready for download.', '{\"certificate_id\":19,\"certificate_number\":\"CERT-2025-0003\"}', 0, 'high', NULL, '2025-10-06 05:10:43', NULL),
(19, 9, 'appointment_reminder', 'Appointment Booked', 'Your appointment has been successfully booked for October 9, 2025 at 9:30 AM', '{\"appointment_id\":\"11\"}', 0, 'medium', NULL, '2025-10-06 06:54:34', NULL),
(20, 13, 'appointment_reminder', 'Appointment Booked', 'Your appointment has been successfully booked for October 9, 2025 at 9:30 AM', '{\"appointment_id\":\"12\"}', 0, 'medium', NULL, '2025-10-06 06:56:06', NULL),
(21, 13, 'appointment_reminder', 'Appointment Booked', 'Your appointment has been successfully booked for October 7, 2025 at 12:00 AM', '{\"appointment_id\":\"13\"}', 0, 'medium', NULL, '2025-10-06 07:22:23', NULL),
(22, 9, 'appointment_reminder', 'Appointment Booked', 'Your appointment has been successfully booked for October 7, 2025 at 12:00 AM', '{\"appointment_id\":\"14\"}', 0, 'medium', NULL, '2025-10-06 10:22:24', NULL),
(23, 9, 'system', 'Medical Certificate Ready', 'Your medical certificate (CERT-2025-0004) has been issued and is ready for download.', '{\"certificate_id\":20,\"certificate_number\":\"CERT-2025-0004\"}', 0, 'high', NULL, '2025-10-08 10:49:03', NULL),
(24, 9, 'system', 'Consultation Request Submitted', 'Your online consultation request has been submitted. Please wait for admin approval. Consultation number: CONS-2025-0006', '{\"consultation_id\":\"9\",\"consultation_number\":\"CONS-2025-0006\"}', 0, 'medium', NULL, '2025-10-12 06:13:16', NULL),
(25, 9, 'appointment_reminder', 'Appointment Booked', 'Your appointment has been successfully booked for October 16, 2025 at 12:00 AM', '{\"appointment_id\":\"15\"}', 0, 'medium', NULL, '2025-10-12 06:15:42', NULL),
(26, 9, 'consultation_ready', 'Consultation Scheduled', 'Your online consultation has been scheduled for October 12, 2025 @ 2:26 PM. Please be online 5 minutes early. Consultation #: CONS-2025-0006', '{\"consultation_id\":9,\"consultation_number\":\"CONS-2025-0006\",\"scheduled_time\":\"2025-10-12 14:26\",\"google_meet_link\":null}', 0, 'high', NULL, '2025-10-12 06:25:02', NULL),
(27, 9, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0006', '{\"consultation_id\":\"9\"}', 0, 'medium', NULL, '2025-10-12 06:25:28', NULL),
(28, 9, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0005', '{\"consultation_id\":\"8\"}', 0, 'medium', NULL, '2025-10-12 06:25:31', NULL),
(29, 10, 'referral_update', 'Referral Created', 'You have been referred to dsadadwd. Referral Number: REF-2025-0001', '{\"referral_id\":\"2\",\"referral_number\":\"REF-2025-0001\"}', 0, 'high', NULL, '2025-10-13 03:05:53', NULL),
(30, 14, 'system', 'Consultation Request Submitted', 'Your online consultation request has been submitted. Please wait for admin approval. Consultation number: CONS-2025-0007', '{\"consultation_id\":\"10\",\"consultation_number\":\"CONS-2025-0007\"}', 0, 'medium', NULL, '2025-10-13 04:14:29', NULL),
(31, 14, 'consultation_ready', 'Consultation Scheduled', 'Your online consultation has been scheduled for October 13, 2025 @ 12:17 PM. Please be online 5 minutes early. Consultation #: CONS-2025-0007', '{\"consultation_id\":10,\"consultation_number\":\"CONS-2025-0007\",\"scheduled_time\":\"2025-10-13 12:17\",\"google_meet_link\":null}', 0, 'high', NULL, '2025-10-13 04:14:49', NULL),
(32, 14, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0007', '{\"consultation_id\":\"10\"}', 0, 'medium', NULL, '2025-10-13 04:14:53', NULL),
(33, 14, 'appointment_reminder', 'Appointment Booked', 'Your appointment has been successfully booked for October 15, 2025 at 12:00 AM', '{\"appointment_id\":\"16\"}', 0, 'medium', NULL, '2025-10-13 04:21:01', NULL),
(34, 14, 'system', 'Appointment Confirmed', 'Your appointment has been confirmed for October 15, 2025 at 8:00 AM. Please arrive 15 minutes early.', '{\"appointment_id\":\"16\",\"status\":\"confirmed\",\"time\":\"08:00:00\"}', 0, 'high', NULL, '2025-10-13 04:22:27', NULL),
(35, 14, 'appointment_reminder', 'Appointment Booked', 'Your appointment has been successfully booked for October 14, 2025 at 12:00 AM', '{\"appointment_id\":\"17\"}', 0, 'medium', NULL, '2025-10-13 04:36:51', NULL),
(36, 14, 'system', 'Appointment Confirmed', 'Your appointment has been confirmed for October 14, 2025 at 10:00 AM. Please arrive 15 minutes early.', '{\"appointment_id\":\"17\",\"status\":\"confirmed\",\"time\":\"10:00:00\"}', 0, 'high', NULL, '2025-10-13 04:37:29', NULL),
(37, 14, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0008', '{\"consultation_id\":\"11\",\"consultation_number\":\"CONS-2025-0008\"}', 0, 'medium', NULL, '2025-10-13 12:24:13', NULL),
(38, 14, 'appointment_reminder', 'Appointment Booked', 'Your appointment request has been submitted for October 14, 2025. Status: Pending Admin Approval. You will be notified once the time slot is confirmed.', '{\"appointment_id\":\"18\"}', 0, 'medium', NULL, '2025-10-13 13:52:21', NULL),
(39, 14, 'appointment_reminder', 'Appointment Rescheduled', 'Your appointment has been rescheduled to October 14, 2025 at 9:00 AM. ', '{\"appointment_id\":\"18\",\"new_date\":\"2025-10-14\",\"new_time\":\"09:00:00\"}', 0, 'high', NULL, '2025-10-13 13:53:26', NULL),
(40, 14, 'appointment_reminder', 'Appointment Booked', 'Your appointment request has been submitted for October 14, 2025. Status: Pending Admin Approval. You will be notified once the time slot is confirmed.', '{\"appointment_id\":\"19\"}', 0, 'medium', NULL, '2025-10-13 13:54:21', NULL),
(41, 14, 'appointment_reminder', 'Appointment Confirmed', 'Your appointment has been confirmed for October 14, 2025 at 8:00 AM. Please arrive 15 minutes early.', '{\"appointment_id\":\"19\",\"status\":\"confirmed\",\"time\":\"08:00:00\"}', 0, 'high', NULL, '2025-10-13 13:55:31', NULL),
(42, 14, 'appointment_reminder', 'Appointment Booked', 'Your appointment request has been submitted for October 14, 2025. Status: Pending Admin Approval. You will be notified once the time slot is confirmed.', '{\"appointment_id\":\"20\"}', 0, 'medium', NULL, '2025-10-13 13:56:22', NULL),
(43, 14, 'appointment_reminder', 'Appointment Rescheduled', 'Your appointment has been rescheduled to October 15, 2025 at 9:00 AM. Reason: too many scheduled that time\r\n', '{\"appointment_id\":\"20\",\"new_date\":\"2025-10-15\",\"new_time\":\"09:00:00\"}', 0, 'high', NULL, '2025-10-13 13:59:48', NULL),
(44, 14, 'appointment_reminder', 'Appointment Booked', 'Your appointment request has been submitted for October 14, 2025. Status: Pending Admin Approval. You will be notified once the time slot is confirmed.', '{\"appointment_id\":\"21\"}', 0, 'medium', NULL, '2025-10-13 14:09:33', NULL),
(45, 14, 'appointment_reminder', 'Appointment Rescheduled', 'Your appointment has been rescheduled to October 16, 2025 at 11:00 AM. ', '{\"appointment_id\":\"21\",\"new_date\":\"2025-10-16\",\"new_time\":\"11:00:00\"}', 0, 'high', NULL, '2025-10-13 14:09:47', NULL),
(46, 14, 'appointment_reminder', 'Appointment Booked', 'Your appointment request has been submitted for October 14, 2025. Status: Pending Admin Approval. You will be notified once the time slot is confirmed.', '{\"appointment_id\":\"22\"}', 0, 'medium', NULL, '2025-10-13 14:28:34', NULL),
(47, 14, 'appointment_reminder', 'Appointment Booked', 'Your appointment request has been submitted for October 14, 2025. Status: Pending Admin Approval. You will be notified once the time slot is confirmed.', '{\"appointment_id\":\"23\"}', 0, 'medium', NULL, '2025-10-13 14:33:26', NULL),
(48, 14, 'appointment_reminder', 'Appointment Booked', 'Your appointment request has been submitted for October 15, 2025. Status: Pending Admin Approval. You will be notified once the time slot is confirmed.', '{\"appointment_id\":\"24\"}', 0, 'medium', NULL, '2025-10-13 14:33:59', NULL),
(49, 14, 'appointment_reminder', 'Appointment Confirmed', 'Your appointment has been confirmed for October 14, 2025 at 11:00 AM. Please arrive 15 minutes early.', '{\"appointment_id\":\"23\",\"status\":\"confirmed\",\"time\":\"11:00:00\"}', 0, 'high', NULL, '2025-10-13 14:35:30', NULL),
(50, 14, 'appointment_reminder', 'Appointment Booked', 'Your appointment request has been submitted for October 14, 2025. Status: Pending Admin Approval. You will be notified once the time slot is confirmed.', '{\"appointment_id\":\"25\"}', 0, 'medium', NULL, '2025-10-13 14:36:39', NULL),
(51, 14, 'appointment_reminder', 'Appointment Rescheduled', 'Your appointment has been rescheduled to October 15, 2025 at 11:00 AM. ', '{\"appointment_id\":\"25\",\"new_date\":\"2025-10-15\",\"new_time\":\"11:00:00\"}', 0, 'high', NULL, '2025-10-13 14:37:03', NULL),
(52, 14, 'appointment_reminder', 'Appointment Booked', 'Your appointment request has been submitted for October 14, 2025. Status: Pending Admin Approval. You will be notified once the time slot is confirmed.', '{\"appointment_id\":\"26\"}', 0, 'medium', NULL, '2025-10-13 14:57:19', NULL),
(53, 14, 'system', 'Consultation Request Submitted', 'Your online consultation request has been submitted. Please wait for admin approval. Consultation number: CONS-2025-0009', '{\"consultation_id\":\"12\",\"consultation_number\":\"CONS-2025-0009\"}', 0, 'medium', NULL, '2025-10-13 14:57:29', NULL),
(54, 14, 'consultation_ready', 'Consultation Scheduled', 'Your online consultation has been scheduled for October 13, 2025 @ 11:58 PM. Please be online 5 minutes early. Consultation #: CONS-2025-0009', '{\"consultation_id\":12,\"consultation_number\":\"CONS-2025-0009\",\"scheduled_time\":\"2025-10-13 23:58\",\"google_meet_link\":null}', 0, 'high', NULL, '2025-10-13 14:58:17', NULL),
(55, 14, 'system', 'Medical Certificate Ready', 'Your medical certificate (CERT-2025-0005) has been issued and is ready for download.', '{\"certificate_id\":21,\"certificate_number\":\"CERT-2025-0005\"}', 0, 'high', NULL, '2025-10-13 15:13:03', NULL),
(56, 9, 'system', 'Medical Certificate Ready', 'Your medical certificate (CERT-2025-0006) has been issued and is ready for download.', '{\"certificate_id\":22,\"certificate_number\":\"CERT-2025-0006\"}', 0, 'high', NULL, '2025-10-13 15:15:46', NULL),
(57, 9, 'system', 'Medical Certificate Ready', 'Your medical certificate (CERT-2025-0007) has been issued and is ready for download.', '{\"certificate_id\":23,\"certificate_number\":\"CERT-2025-0007\"}', 0, 'high', NULL, '2025-10-13 16:26:49', NULL),
(58, 14, 'appointment_reminder', 'Appointment Confirmed', 'Your appointment has been confirmed for October 14, 2025 at 11:00 AM. Please arrive 15 minutes early.', '{\"appointment_id\":\"26\",\"status\":\"confirmed\",\"time\":\"11:00:00\"}', 0, 'high', NULL, '2025-10-14 02:13:03', NULL),
(59, 14, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0009', '{\"consultation_id\":\"12\"}', 0, 'medium', NULL, '2025-10-14 02:24:36', NULL),
(60, 14, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0010', '{\"consultation_id\":\"13\",\"consultation_number\":\"CONS-2025-0010\"}', 0, 'medium', NULL, '2025-10-14 02:26:17', NULL),
(61, 9, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0011', '{\"consultation_id\":\"14\",\"consultation_number\":\"CONS-2025-0011\"}', 0, 'medium', NULL, '2025-10-14 02:27:19', NULL),
(62, 13, 'system', 'Consultation Request Submitted', 'Your online consultation request has been submitted. Please wait for admin approval. Consultation number: CONS-2025-0012', '{\"consultation_id\":\"15\",\"consultation_number\":\"CONS-2025-0012\"}', 0, 'medium', NULL, '2025-10-14 03:23:01', NULL),
(63, 13, 'consultation_ready', 'Consultation Scheduled', 'Your online consultation has been scheduled for October 14, 2025 @ 12:00 PM. Please be online 5 minutes early. Consultation #: CONS-2025-0012', '{\"consultation_id\":15,\"consultation_number\":\"CONS-2025-0012\",\"scheduled_time\":\"2025-10-14 12:00\",\"google_meet_link\":\"https:\\/\\/meet.google.com\\/yuh-phov-qed\"}', 0, 'high', NULL, '2025-10-14 03:24:49', NULL),
(64, 13, 'consultation_ready', 'Google Meet Link Ready', 'Your Google Meet link is ready! You can now join your scheduled consultation. Consultation #: CONS-2025-0012', '{\"consultation_id\":15,\"consultation_number\":\"CONS-2025-0012\",\"google_meet_link\":\"https:\\/\\/meet.google.com\\/yuh-phov-qed\"}', 0, 'high', NULL, '2025-10-14 03:24:49', NULL),
(65, 13, 'system', 'Consultation Request Submitted', 'Your online consultation request has been submitted. Please wait for admin approval. Consultation number: CONS-2025-0001', '{\"consultation_id\":\"16\",\"consultation_number\":\"CONS-2025-0001\"}', 0, 'medium', NULL, '2025-10-14 03:38:44', NULL),
(66, 13, 'consultation_ready', 'Consultation Scheduled', 'Your online consultation has been scheduled for October 14, 2025 @ 1:38 AM. Please be online 5 minutes early. Consultation #: CONS-2025-0001', '{\"consultation_id\":16,\"consultation_number\":\"CONS-2025-0001\",\"scheduled_time\":\"2025-10-14 01:38\",\"google_meet_link\":\"https:\\/\\/meet.google.com\\/yuh-phov-qed\"}', 0, 'high', NULL, '2025-10-14 03:39:01', NULL),
(67, 13, 'consultation_ready', 'Google Meet Link Ready', 'Your Google Meet link is ready! You can now join your scheduled consultation. Consultation #: CONS-2025-0001', '{\"consultation_id\":16,\"consultation_number\":\"CONS-2025-0001\",\"google_meet_link\":\"https:\\/\\/meet.google.com\\/yuh-phov-qed\"}', 0, 'high', NULL, '2025-10-14 03:39:01', NULL),
(68, 13, 'system', 'Consultation Request Submitted', 'Your online consultation request has been submitted. Please wait for admin approval. Consultation number: CONS-2025-0001', '{\"consultation_id\":\"17\",\"consultation_number\":\"CONS-2025-0001\"}', 0, 'medium', NULL, '2025-10-14 03:42:08', NULL),
(69, 13, 'consultation_ready', 'Consultation Scheduled', 'Your online consultation has been scheduled for October 14, 2025 @ 1:00 PM. Please be online 5 minutes early. Consultation #: CONS-2025-0001', '{\"consultation_id\":17,\"consultation_number\":\"CONS-2025-0001\",\"scheduled_time\":\"2025-10-14 13:00\",\"google_meet_link\":null}', 0, 'high', NULL, '2025-10-14 03:42:32', NULL),
(70, 14, 'system', 'Consultation Request Submitted', 'Your online consultation request has been submitted. Please wait for admin approval. Consultation number: CONS-2025-0002', '{\"consultation_id\":\"18\",\"consultation_number\":\"CONS-2025-0002\"}', 0, 'medium', NULL, '2025-10-14 03:44:01', NULL),
(71, 14, 'consultation_ready', 'Consultation Scheduled', 'Your online consultation has been scheduled for October 14, 2025 @ 1:00 AM. Please be online 5 minutes early. Consultation #: CONS-2025-0002', '{\"consultation_id\":18,\"consultation_number\":\"CONS-2025-0002\",\"scheduled_time\":\"2025-10-14 01:00\",\"google_meet_link\":null}', 0, 'high', NULL, '2025-10-14 03:44:44', NULL),
(72, 14, 'appointment_reminder', 'Certificate Check-up Scheduled', 'Your medical certificate request (CERT-2025-0009) has been approved. Your check-up is scheduled on October 16, 2025 at 9:00 AM. Please arrive 10 minutes early.', '{\"certificate_id\":\"25\",\"certificate_number\":\"CERT-2025-0009\",\"appointment_date\":\"2025-10-16\",\"appointment_time\":\"09:00\"}', 0, 'high', NULL, '2025-10-16 11:28:07', NULL),
(73, 14, 'system', 'Check-up Completed', 'Your check-up for medical certificate (CERT-2025-0009) has been completed. Your certificate is being prepared by the doctor.', '{\"certificate_id\":\"25\",\"certificate_number\":\"CERT-2025-0009\"}', 0, 'medium', NULL, '2025-10-16 11:29:27', NULL),
(74, 14, 'system', 'Medical Certificate Ready for Download', 'Your medical certificate (CERT-2025-0009) has been approved and is now ready for download. You can download it from your Medical Certificates page.', '{\"certificate_id\":25,\"certificate_number\":\"CERT-2025-0009\"}', 0, 'high', NULL, '2025-10-16 11:30:23', NULL),
(75, 9, 'appointment_reminder', 'Certificate Check-up Scheduled', 'Your medical certificate request (CERT-2025-0003) has been approved. Your check-up is scheduled on October 17, 2025 at 9:00 AM. Please arrive 10 minutes early.', '{\"certificate_id\":\"19\",\"certificate_number\":\"CERT-2025-0003\",\"appointment_date\":\"2025-10-17\",\"appointment_time\":\"09:00\"}', 0, 'high', NULL, '2025-10-16 11:31:23', NULL),
(76, 14, 'appointment_reminder', 'Certificate Check-up Scheduled', 'Your medical certificate request (CERT-2025-0005) has been approved. Your check-up is scheduled on October 17, 2025 at 9:00 AM. Please arrive 10 minutes early.', '{\"certificate_id\":\"21\",\"certificate_number\":\"CERT-2025-0005\",\"appointment_date\":\"2025-10-17\",\"appointment_time\":\"09:00\"}', 0, 'high', NULL, '2025-10-16 12:14:53', NULL),
(77, 14, 'system', 'Check-up Completed', 'Your check-up for medical certificate (CERT-2025-0005) has been completed. Your certificate is being prepared by the doctor.', '{\"certificate_id\":\"21\",\"certificate_number\":\"CERT-2025-0005\"}', 0, 'medium', NULL, '2025-10-16 12:15:20', NULL),
(78, 14, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0002', '{\"consultation_id\":\"18\"}', 0, 'medium', NULL, '2025-10-16 12:29:56', NULL),
(79, 14, 'system', 'Medical Certificate Ready for Download', 'Your medical certificate (CERT-2025-0005) has been approved and is now ready for download. You can download it from your Medical Certificates page.', '{\"certificate_id\":21,\"certificate_number\":\"CERT-2025-0005\"}', 0, 'high', NULL, '2025-10-16 12:41:55', NULL),
(80, 9, 'system', 'Check-up Completed', 'Your check-up for medical certificate (CERT-2025-0003) has been completed. Your certificate is being prepared by the doctor.', '{\"certificate_id\":\"19\",\"certificate_number\":\"CERT-2025-0003\"}', 0, 'medium', NULL, '2025-10-16 15:58:42', NULL),
(81, 9, 'system', 'Medical Certificate Ready for Download', 'Your medical certificate (CERT-2025-0003) has been approved and is now ready for download. You can download it from your Medical Certificates page.', '{\"certificate_id\":19,\"certificate_number\":\"CERT-2025-0003\"}', 0, 'high', NULL, '2025-10-16 15:58:53', NULL),
(82, 9, 'appointment_reminder', 'Certificate Check-up Scheduled', 'Your medical certificate request (CERT-2025-0014) has been approved. Your check-up is scheduled on October 18, 2025 at 9:00 AM. Please arrive 10 minutes early.', '{\"certificate_id\":\"17\",\"certificate_number\":\"CERT-2025-0014\",\"appointment_date\":\"2025-10-18\",\"appointment_time\":\"09:00\"}', 0, 'high', NULL, '2025-10-16 16:26:43', NULL),
(83, 9, 'system', 'Check-up Completed', 'Your check-up for medical certificate (CERT-2025-0014) has been completed. Your certificate is being prepared by the doctor.', '{\"certificate_id\":\"17\",\"certificate_number\":\"CERT-2025-0014\"}', 0, 'medium', NULL, '2025-10-16 16:27:16', NULL),
(84, 9, 'system', 'Medical Certificate Ready for Download', 'Your medical certificate (CERT-2025-0014) has been approved and is now ready for download. You can download it from your Medical Certificates page.', '{\"certificate_id\":17,\"certificate_number\":\"CERT-2025-0014\"}', 0, 'high', NULL, '2025-10-16 16:27:59', NULL),
(85, 14, 'system', 'Password Changed', 'Your password has been changed by RHU admin. If you did not request this change, please contact the RHU immediately.', '{\"patient_id\":\"P-2025-0001\"}', 0, 'high', NULL, '2025-10-17 05:44:36', NULL),
(86, 13, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0001', '{\"consultation_id\":\"17\"}', 0, 'medium', NULL, '2025-10-17 05:53:48', NULL),
(87, 14, 'appointment_reminder', 'Certificate Check-up Scheduled', 'Your medical certificate request (CERT-2025-0008) has been approved. Your check-up is scheduled on October 18, 2025 at 9:00 AM. Please arrive 10 minutes early.', '{\"certificate_id\":\"24\",\"certificate_number\":\"CERT-2025-0008\",\"appointment_date\":\"2025-10-18\",\"appointment_time\":\"09:00\"}', 0, 'high', NULL, '2025-10-17 07:47:55', NULL),
(88, 14, 'system', 'Check-up Completed', 'Your check-up for medical certificate (CERT-2025-0008) has been completed. Your certificate is being prepared by the doctor.', '{\"certificate_id\":\"24\",\"certificate_number\":\"CERT-2025-0008\"}', 0, 'medium', NULL, '2025-10-17 07:59:42', NULL),
(89, 14, 'system', 'Medical Certificate Ready for Download', 'Your medical certificate (CERT-2025-0008) has been approved and is now ready for download. You can download it from your Medical Certificates page.', '{\"certificate_id\":24,\"certificate_number\":\"CERT-2025-0008\",\"consultation_id\":\"19\"}', 0, 'high', NULL, '2025-10-17 12:16:24', NULL),
(90, 9, 'appointment_reminder', 'Certificate Check-up Scheduled', 'Your medical certificate request (CERT-2025-0002) has been approved. Your check-up is scheduled on October 21, 2025 at 9:00 AM. Please arrive 10 minutes early.', '{\"certificate_id\":\"18\",\"certificate_number\":\"CERT-2025-0002\",\"appointment_date\":\"2025-10-21\",\"appointment_time\":\"09:00\"}', 0, 'high', NULL, '2025-10-17 12:57:03', NULL),
(91, 9, 'system', 'Check-up Completed', 'Your check-up for medical certificate (CERT-2025-0002) has been completed. Your certificate is being prepared by the doctor.', '{\"certificate_id\":\"18\",\"certificate_number\":\"CERT-2025-0002\"}', 0, 'medium', NULL, '2025-10-17 12:57:09', NULL),
(92, 9, 'system', 'Medical Certificate Ready for Download', 'Your medical certificate (CERT-2025-0002) has been approved and is now ready for download. You can download it from your Medical Certificates page.', '{\"certificate_id\":18,\"certificate_number\":\"CERT-2025-0002\",\"consultation_id\":\"31\"}', 0, 'high', NULL, '2025-10-17 13:08:26', NULL),
(93, 2, 'system', 'New Medical Certificate Request', 'New certificate request from Juan Cruz', '{\"certificate_id\":\"32\",\"certificate_number\":\"CERT-2025-0001\"}', 0, 'medium', NULL, '2025-10-17 13:21:39', NULL),
(94, 9, 'appointment_reminder', 'Certificate Check-up Scheduled', 'Your medical certificate request (CERT-2025-0001) has been approved. Your check-up is scheduled on October 18, 2025 at 12:00 AM. Please arrive 10 minutes early.', '{\"certificate_id\":\"32\",\"certificate_number\":\"CERT-2025-0001\",\"appointment_date\":\"2025-10-18\",\"appointment_time\":\"00:00\"}', 0, 'high', NULL, '2025-10-17 13:22:23', NULL),
(95, 9, 'system', 'Check-up Completed', 'Your check-up for medical certificate (CERT-2025-0001) has been completed. Your certificate is being prepared by the doctor.', '{\"certificate_id\":\"32\",\"certificate_number\":\"CERT-2025-0001\"}', 0, 'medium', NULL, '2025-10-17 13:22:43', NULL),
(96, 9, 'system', 'Medical Certificate Ready for Download', 'Your medical certificate (CERT-2025-0001) has been approved and is now ready for download. You can download it from your Medical Certificates page.', '{\"certificate_id\":32,\"certificate_number\":\"CERT-2025-0001\",\"consultation_id\":\"32\"}', 0, 'high', NULL, '2025-10-17 13:23:00', NULL),
(97, 2, 'system', 'New Medical Certificate Request', 'New certificate request from jeremayah gayondato', '{\"certificate_id\":\"33\",\"certificate_number\":\"CERT-2025-0002\"}', 0, 'medium', NULL, '2025-10-18 03:27:41', NULL),
(98, 14, 'appointment_reminder', 'Certificate Check-up Scheduled', 'Your medical certificate request (CERT-2025-0002) has been approved. Your check-up is scheduled on October 19, 2025 at 9:00 AM. Please arrive 10 minutes early.', '{\"certificate_id\":\"33\",\"certificate_number\":\"CERT-2025-0002\",\"appointment_date\":\"2025-10-19\",\"appointment_time\":\"09:00\"}', 0, 'high', NULL, '2025-10-18 03:28:01', NULL),
(99, 14, 'system', 'Check-up Completed', 'Your check-up for medical certificate (CERT-2025-0002) has been completed. Your certificate is being prepared by the doctor.', '{\"certificate_id\":\"33\",\"certificate_number\":\"CERT-2025-0002\"}', 0, 'medium', NULL, '2025-10-18 03:28:20', NULL),
(100, 14, 'system', 'Medical Certificate Ready for Download', 'Your medical certificate (CERT-2025-0002) has been approved and is now ready for download. You can download it from your Medical Certificates page.', '{\"certificate_id\":33,\"certificate_number\":\"CERT-2025-0002\",\"consultation_id\":\"33\"}', 0, 'high', NULL, '2025-10-18 04:02:03', NULL),
(101, 14, 'lab_result', 'Lab Results Available', 'Your complete blood count results are now available. Lab Number: LAB-2025-0001', '{\"lab_id\":\"2\",\"lab_number\":\"LAB-2025-0001\"}', 0, 'medium', NULL, '2025-10-18 10:12:13', NULL),
(102, 14, 'referral_update', 'Referral Created', 'You have been referred to Medical Center Imus. Referral Number: REF-2025-0002', '{\"referral_id\":\"3\",\"referral_number\":\"REF-2025-0002\"}', 0, 'medium', NULL, '2025-10-18 11:45:07', NULL),
(103, 15, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0006', '{\"consultation_id\":\"34\",\"consultation_number\":\"CONS-2025-0006\"}', 0, 'medium', NULL, '2025-10-23 16:06:06', NULL),
(104, 15, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0007', '{\"consultation_id\":\"35\",\"consultation_number\":\"CONS-2025-0007\"}', 0, 'medium', NULL, '2025-10-23 16:07:51', NULL),
(105, 14, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0008', '{\"consultation_id\":\"36\",\"consultation_number\":\"CONS-2025-0008\"}', 0, 'medium', NULL, '2025-10-23 16:10:12', NULL),
(106, 15, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0006', '{\"consultation_id\":\"34\"}', 0, 'medium', NULL, '2025-10-23 16:22:29', NULL),
(107, 12, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0009', '{\"consultation_id\":\"37\",\"consultation_number\":\"CONS-2025-0009\"}', 0, 'medium', NULL, '2025-10-23 16:31:59', NULL),
(108, 14, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0008', '{\"consultation_id\":\"36\"}', 0, 'medium', NULL, '2025-10-23 16:37:57', NULL),
(109, 12, 'system', 'Consultation Request Submitted', 'Your online consultation request has been submitted. Please wait for admin approval. Consultation number: CONS-2025-0010', '{\"consultation_id\":\"38\",\"consultation_number\":\"CONS-2025-0010\"}', 0, 'medium', NULL, '2025-10-23 16:39:43', NULL),
(110, 12, 'consultation_ready', 'Consultation Scheduled', 'Your online consultation has been scheduled for October 23, 2025 @ 12:41 AM. Please be online 5 minutes early. Consultation #: CONS-2025-0010', '{\"consultation_id\":38,\"consultation_number\":\"CONS-2025-0010\",\"scheduled_time\":\"2025-10-23 00:41\",\"google_meet_link\":null}', 0, 'high', NULL, '2025-10-23 16:39:59', NULL),
(111, 12, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0010', '{\"consultation_id\":\"38\"}', 0, 'medium', NULL, '2025-10-23 16:40:08', NULL),
(112, 15, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0007', '{\"consultation_id\":\"35\"}', 0, 'medium', NULL, '2025-10-23 16:40:14', NULL),
(113, 13, 'system', 'Consultation Completed', 'Your consultation has been completed. Consultation Number: CONS-2025-0011', '{\"consultation_id\":\"39\",\"consultation_number\":\"CONS-2025-0011\"}', 0, 'medium', NULL, '2025-10-23 16:55:10', NULL),
(114, 2, 'system', 'New Medical Certificate Request', 'New certificate request from Carlos Mendoza', '{\"certificate_id\":\"34\",\"certificate_number\":\"CERT-2025-0003\"}', 0, 'medium', NULL, '2025-10-25 01:27:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `patient_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `civil_status` enum('Single','Married','Widowed','Divorced','Separated') DEFAULT 'Single',
  `address` text NOT NULL,
  `barangay_id` int(11) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(15) DEFAULT NULL,
  `emergency_contact_relationship` varchar(50) DEFAULT NULL,
  `guardian_name` varchar(200) DEFAULT NULL,
  `guardian_relationship` varchar(100) DEFAULT NULL,
  `philhealth_number` varchar(20) DEFAULT NULL,
  `osca_number` varchar(50) DEFAULT NULL,
  `family_number` varchar(50) DEFAULT NULL,
  `pwd_status` enum('YES','NO') DEFAULT 'NO',
  `four_ps_status` enum('YES','NO') DEFAULT 'NO',
  `occupation` varchar(100) DEFAULT NULL,
  `educational_attainment` varchar(50) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `temperature` decimal(4,1) DEFAULT NULL COMMENT 'In Celsius',
  `blood_pressure` varchar(20) DEFAULT NULL COMMENT 'Format: 120/80',
  `heart_rate` int(11) DEFAULT NULL COMMENT 'Beats per minute',
  `respiratory_rate` int(11) DEFAULT NULL COMMENT 'Breaths per minute',
  `oxygen_saturation` int(11) DEFAULT NULL COMMENT 'Percentage',
  `weight_kg` decimal(5,2) DEFAULT NULL COMMENT 'Weight in kilograms',
  `height_cm` decimal(5,2) DEFAULT NULL COMMENT 'Height in centimeters',
  `bmi` decimal(4,1) DEFAULT NULL COMMENT 'Calculated BMI',
  `is_current_smoker` enum('YES','NO') DEFAULT 'NO',
  `has_alcohol_intake` enum('YES','NO') DEFAULT 'NO',
  `has_illicit_drug_use` enum('YES','NO') DEFAULT 'NO',
  `eats_unhealthy_foods` enum('YES','NO') DEFAULT 'NO',
  `has_regular_exercise` enum('YES','NO') DEFAULT 'NO',
  `has_hypertension` enum('YES','NO') DEFAULT 'NO',
  `gets_hypertension_meds_from_rhu` enum('YES','NO') DEFAULT 'NO',
  `has_diabetes` enum('YES','NO') DEFAULT 'NO',
  `gets_diabetes_meds_from_rhu` enum('YES','NO') DEFAULT 'NO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `user_id`, `patient_id`, `first_name`, `middle_name`, `last_name`, `suffix`, `date_of_birth`, `gender`, `civil_status`, `address`, `barangay_id`, `phone`, `email`, `blood_type`, `allergies`, `medical_history`, `emergency_contact_name`, `emergency_contact_phone`, `emergency_contact_relationship`, `guardian_name`, `guardian_relationship`, `philhealth_number`, `osca_number`, `family_number`, `pwd_status`, `four_ps_status`, `occupation`, `educational_attainment`, `religion`, `profile_picture`, `is_active`, `created_at`, `updated_at`, `temperature`, `blood_pressure`, `heart_rate`, `respiratory_rate`, `oxygen_saturation`, `weight_kg`, `height_cm`, `bmi`, `is_current_smoker`, `has_alcohol_intake`, `has_illicit_drug_use`, `eats_unhealthy_foods`, `has_regular_exercise`, `has_hypertension`, `gets_hypertension_meds_from_rhu`, `has_diabetes`, `gets_diabetes_meds_from_rhu`) VALUES
(1, 9, 'P-2024-0001', 'Juan', 'Dela', 'Cruz', NULL, '1990-05-15', 'Male', 'Married', '123 Rizal St., Binakayan-Kanluran', 1, '09181234567', 'juan.delacruz@email.com', 'O+', NULL, NULL, 'Juana Dela Cruz', '09181234568', 'Wife', NULL, NULL, '12-345678901-2', NULL, NULL, 'NO', 'NO', NULL, NULL, NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-16 05:55:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO'),
(2, 10, 'P-2024-0002', 'Maria', 'Clara', 'Santos', NULL, '1985-08-20', 'Female', 'Single', '456 Bonifacio St., Kaingen', 3, '09191234567', 'maria.santos@email.com', 'A+', NULL, NULL, 'Jose Santos', '09191234568', 'Father', NULL, NULL, '12-345678902-3', NULL, NULL, 'NO', 'NO', NULL, NULL, NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-16 05:55:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO'),
(3, 11, 'P-2024-0003', 'Pedro', 'Manuel', 'Garcia', NULL, '1995-03-10', 'Male', 'Single', '789 Aguinaldo St., Gahak', 4, '09201234567', 'pedro.garcia@email.com', 'B+', NULL, NULL, 'Ana Garcia', '09201234568', 'Mother', NULL, NULL, '12-345678903-4', NULL, NULL, 'NO', 'NO', NULL, NULL, NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-16 05:55:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO'),
(4, 12, 'P-2024-0004', 'Ana', 'Marie', 'Reyes', NULL, '1992-11-22', 'Female', 'Married', '321 Luna St., Panamitan', 5, '09211234567', 'ana.reyes.patient@email.com', 'AB+', NULL, NULL, 'John Reyes', '09211234568', 'Husband', NULL, NULL, '12-345678904-5', NULL, NULL, 'NO', 'NO', NULL, NULL, NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-16 05:55:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO'),
(5, 13, 'P-2024-0005', 'Carlos', 'Jose', 'Mendoza', NULL, '1988-07-08', 'Male', 'Married', '654 Mabini St., Marulas', 6, '09221234567', 'carlos.mendoza@email.com', 'O-', NULL, NULL, 'Carmen Mendoza', '09221234568', 'Wife', NULL, NULL, '12-345678905-6', NULL, NULL, 'NO', 'NO', NULL, NULL, NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-16 05:55:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO'),
(6, 14, 'P-2025-0001', 'jeremayah', 'tagalog', 'gayondato', '', '2002-02-28', 'Male', 'Single', 'n/a', 6, '9932-6385-73', 'jeremayah19@gmail.com', 'O-', 'n/a', NULL, 'Juana Dela Cruz', '0918-1234-568', 'Parent', NULL, NULL, '12-345678901-2', NULL, NULL, 'NO', 'NO', 'n/a', 'College Graduate', 'christian', NULL, 1, '2025-10-13 03:11:05', '2025-10-16 05:55:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO'),
(7, 15, 'P-2025-0002', 'ana', 'chan', 'marie', '', '2009-02-18', 'Female', 'Single', 'n/a', 3, '9932-6385-73', 'ana.marie@gmail.com', 'AB+', '', NULL, 'Juana Dela Cruz', '0918-1234-568', 'Friend', NULL, NULL, '12-345678901-2', NULL, NULL, 'NO', 'NO', '', 'Senior High School', 'christian', NULL, 1, '2025-10-17 03:11:56', '2025-10-17 03:11:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `prescription_number` varchar(20) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `medication_name` varchar(100) NOT NULL,
  `generic_name` varchar(100) DEFAULT NULL,
  `brand_name` varchar(100) DEFAULT NULL,
  `dosage_strength` varchar(50) NOT NULL,
  `dosage_form` varchar(50) NOT NULL,
  `quantity_prescribed` int(11) NOT NULL,
  `quantity_dispensed` int(11) DEFAULT 0,
  `dosage_instructions` text NOT NULL,
  `frequency` varchar(50) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `special_instructions` text DEFAULT NULL,
  `prescribed_by` int(11) NOT NULL,
  `prescription_date` date NOT NULL,
  `dispensed_by` int(11) DEFAULT NULL,
  `dispensed_date` datetime DEFAULT NULL,
  `pharmacy_notes` text DEFAULT NULL,
  `status` enum('pending','partially_dispensed','fully_dispensed','cancelled','expired') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `prescription_number`, `patient_id`, `consultation_id`, `medication_name`, `generic_name`, `brand_name`, `dosage_strength`, `dosage_form`, `quantity_prescribed`, `quantity_dispensed`, `dosage_instructions`, `frequency`, `duration`, `special_instructions`, `prescribed_by`, `prescription_date`, `dispensed_by`, `dispensed_date`, `pharmacy_notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 'RX-2024-0001', 1, NULL, 'Paracetamol', 'Acetaminophen', NULL, '500mg', 'Tablet', 12, 0, 'Take 1 tablet every 6 hours as needed for fever', 'Every 6 hours', '3 days', NULL, 3, '2025-09-29', NULL, NULL, NULL, 'fully_dispensed', '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(2, 'RX-2024-0002', 2, NULL, 'Amoxicillin', 'Amoxicillin', NULL, '500mg', 'Capsule', 21, 0, 'Take 1 capsule three times a day after meals', 'Three times a day', '7 days', NULL, 3, '2025-10-01', NULL, NULL, NULL, 'fully_dispensed', '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(3, 'RX-2024-0003', 2, NULL, 'Cetirizine', 'Cetirizine HCl', NULL, '10mg', 'Tablet', 5, 0, 'Take 1 tablet once daily at bedtime', 'Once daily', '5 days', NULL, 3, '2025-10-01', NULL, NULL, NULL, 'fully_dispensed', '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(4, 'RX-2024-0004', 3, NULL, 'Omeprazole', 'Omeprazole', NULL, '20mg', 'Capsule', 14, 0, 'Take 1 capsule once daily 30 minutes before breakfast', 'Once daily before breakfast', '14 days', NULL, 3, '2025-10-03', NULL, NULL, NULL, 'pending', '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(7, 'RX-2025-7203', 5, NULL, 'biogesic', 'paracetamol', NULL, '500mg', 'Tablet', 5, 0, 'twice a day', '3 times a day', '1 week', 'n/a', 2, '2025-10-14', NULL, NULL, NULL, 'pending', '2025-10-14 03:35:18', '2025-10-14 03:35:18'),
(8, 'RX-2025-1111', 7, 34, 'biogesic', 'paracetamol', NULL, '500mg', 'Tablet', 1, 0, '', '3x a day', '4 days', '', 2, '2025-10-24', NULL, NULL, NULL, 'pending', '2025-10-23 16:06:06', '2025-10-23 16:06:06');

-- --------------------------------------------------------

--
-- Table structure for table `queue`
--

CREATE TABLE `queue` (
  `id` int(11) NOT NULL,
  `queue_number` varchar(20) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `service_type` enum('consultation','laboratory','pharmacy','certificate','maternity','nip','family_planning') DEFAULT 'consultation',
  `priority` enum('urgent','senior_pwd','pregnant','regular') DEFAULT 'regular',
  `status` enum('waiting','serving','completed','cancelled','no_show') DEFAULT 'waiting',
  `check_in_time` datetime NOT NULL,
  `called_time` datetime DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `queue_date` date NOT NULL,
  `estimated_wait_minutes` int(11) DEFAULT 0,
  `actual_wait_minutes` int(11) DEFAULT 0,
  `position_in_queue` int(11) DEFAULT 0,
  `served_by` int(11) DEFAULT NULL,
  `counter_number` int(11) DEFAULT NULL,
  `location_type` enum('RHU','BHS') DEFAULT 'RHU',
  `barangay_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `queue`
--

INSERT INTO `queue` (`id`, `queue_number`, `patient_id`, `service_type`, `priority`, `status`, `check_in_time`, `called_time`, `start_time`, `end_time`, `queue_date`, `estimated_wait_minutes`, `actual_wait_minutes`, `position_in_queue`, `served_by`, `counter_number`, `location_type`, `barangay_id`, `notes`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(1, 'C-20251025-001', 6, 'certificate', 'regular', 'completed', '2025-10-25 14:36:37', NULL, '2025-10-25 14:39:45', '2025-10-25 14:41:40', '2025-10-25', 0, 54, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-25 06:36:37', '2025-10-25 06:41:40'),
(4, 'L-20251025-001', 2, 'laboratory', 'regular', 'waiting', '2025-10-25 15:55:54', NULL, NULL, NULL, '2025-10-25', 0, 0, 1, NULL, NULL, 'RHU', NULL, '', NULL, '2025-10-25 07:55:54', '2025-10-25 07:55:54'),
(9, 'C-20251026-001', 7, 'consultation', 'regular', 'no_show', '2025-10-26 18:01:17', NULL, NULL, NULL, '2025-10-26', 0, 0, 1, NULL, NULL, 'RHU', NULL, '', NULL, '2025-10-26 10:01:17', '2025-10-26 10:03:13'),
(10, 'C-20251026-002', 7, 'consultation', 'regular', 'cancelled', '2025-10-26 18:36:12', NULL, NULL, NULL, '2025-10-26', 0, 0, 1, NULL, NULL, 'RHU', NULL, '', '', '2025-10-26 10:36:12', '2025-10-26 10:37:34'),
(11, 'C-20251026-003', 6, 'consultation', 'regular', 'completed', '2025-10-26 18:36:19', NULL, '2025-10-26 18:37:52', '2025-10-26 18:39:07', '2025-10-26', 15, 57, 2, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 10:36:19', '2025-10-26 10:39:07'),
(12, 'C-20251026-004', 7, 'consultation', 'regular', 'completed', '2025-10-26 18:38:40', NULL, '2025-10-26 18:38:42', '2025-10-26 18:39:09', '2025-10-26', 0, 59, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 10:38:40', '2025-10-26 10:39:09'),
(13, 'C-20251026-005', 1, 'consultation', 'regular', 'completed', '2025-10-26 19:19:17', NULL, '2025-10-26 19:20:34', '2025-10-26 21:10:47', '2025-10-26', 0, 8, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 11:19:17', '2025-10-26 13:10:47'),
(14, 'C-20251026-006', 6, 'consultation', 'regular', 'completed', '2025-10-26 19:19:48', '2025-10-26 21:10:36', NULL, '2025-10-26 21:10:50', '2025-10-26', 15, 8, 2, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 11:19:48', '2025-10-26 13:10:50'),
(15, 'C-20251026-007', 7, 'consultation', 'regular', 'completed', '2025-10-26 19:19:53', '2025-10-26 21:10:56', NULL, '2025-10-26 21:11:23', '2025-10-26', 30, 8, 3, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 11:19:53', '2025-10-26 13:11:23'),
(16, 'C-20251026-008', 4, 'consultation', 'regular', 'completed', '2025-10-26 19:20:20', '2025-10-26 21:11:01', NULL, '2025-10-26 21:11:21', '2025-10-26', 45, 8, 4, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 11:20:20', '2025-10-26 13:11:21'),
(17, 'C-20251026-009', 6, 'consultation', 'regular', 'completed', '2025-10-26 23:33:51', NULL, '2025-10-26 23:50:52', '2025-10-26 23:52:14', '2025-10-26', 0, 41, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 15:33:51', '2025-10-26 15:52:14'),
(18, 'C-20251026-010', 7, 'consultation', 'regular', 'completed', '2025-10-26 23:34:44', NULL, '2025-10-26 23:50:52', '2025-10-26 23:52:17', '2025-10-26', 15, 42, 2, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 15:34:44', '2025-10-26 15:52:17'),
(19, 'C-20251026-011', 1, 'consultation', 'regular', 'completed', '2025-10-26 23:39:11', NULL, '2025-10-26 23:50:53', '2025-10-26 23:52:21', '2025-10-26', 30, 46, 3, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 15:39:11', '2025-10-26 15:52:21'),
(20, 'C-20251026-012', 3, 'consultation', 'regular', 'cancelled', '2025-10-26 23:51:52', NULL, NULL, NULL, '2025-10-26', 0, 0, 1, NULL, NULL, 'RHU', NULL, '', '', '2025-10-26 15:51:52', '2025-10-26 15:52:10'),
(21, 'C-20251026-013', 3, 'consultation', 'regular', 'no_show', '2025-10-26 23:52:35', NULL, NULL, NULL, '2025-10-26', 0, 0, 1, NULL, NULL, 'RHU', NULL, '', NULL, '2025-10-26 15:52:35', '2025-10-26 15:52:49'),
(22, 'C-20251026-014', 3, 'consultation', 'regular', 'completed', '2025-10-26 23:52:57', NULL, '2025-10-26 23:53:01', '2025-10-26 23:58:48', '2025-10-26', 0, 54, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 15:52:57', '2025-10-26 15:58:48'),
(23, 'C-20251026-015', 1, 'consultation', 'regular', 'completed', '2025-10-26 23:58:56', NULL, '2025-10-26 23:59:05', '2025-10-27 00:02:30', '2025-10-26', 0, 56, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 15:58:56', '2025-10-26 16:02:30'),
(24, 'C-20251026-016', 6, 'consultation', 'regular', 'completed', '2025-10-27 00:02:46', NULL, '2025-10-27 00:11:02', '2025-10-27 00:11:07', '2025-10-26', 0, 51, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:02:46', '2025-10-26 16:11:07'),
(25, 'C-20251026-017', 1, 'consultation', 'regular', 'completed', '2025-10-27 00:08:18', NULL, '2025-10-27 00:11:03', '2025-10-27 00:11:08', '2025-10-26', 15, 57, 2, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:08:18', '2025-10-26 16:11:08'),
(26, 'C-20251026-018', 7, 'consultation', 'regular', 'completed', '2025-10-27 00:10:39', NULL, '2025-10-27 00:11:04', '2025-10-27 00:11:11', '2025-10-26', 30, 59, 3, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:10:39', '2025-10-26 16:11:11'),
(27, 'C-20251026-019', 6, 'consultation', 'regular', 'completed', '2025-10-27 00:11:19', NULL, '2025-10-27 00:16:06', '2025-10-27 00:16:12', '2025-10-26', 0, 55, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:11:19', '2025-10-26 16:16:12'),
(28, 'C-20251026-020', 1, 'consultation', 'regular', 'completed', '2025-10-27 00:15:20', NULL, '2025-10-27 00:16:07', '2025-10-27 00:16:13', '2025-10-26', 15, 59, 2, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:15:20', '2025-10-26 16:16:13'),
(29, 'C-20251026-021', 7, 'consultation', 'regular', 'completed', '2025-10-27 00:16:29', NULL, '2025-10-27 00:25:55', '2025-10-27 00:26:01', '2025-10-26', 0, 50, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:16:29', '2025-10-26 16:26:01'),
(30, 'C-20251026-022', 6, 'consultation', 'regular', 'completed', '2025-10-27 00:20:38', NULL, '2025-10-27 00:25:56', '2025-10-27 00:26:02', '2025-10-26', 15, 54, 2, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:20:38', '2025-10-26 16:26:02'),
(31, 'C-20251026-023', 6, 'consultation', 'regular', 'completed', '2025-10-27 00:26:08', NULL, '2025-10-27 00:30:57', '2025-10-27 00:31:10', '2025-10-26', 0, 54, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:26:08', '2025-10-26 16:31:10'),
(32, 'C-20251026-024', 7, 'consultation', 'regular', 'completed', '2025-10-27 00:26:27', NULL, '2025-10-27 00:30:58', '2025-10-27 00:31:12', '2025-10-26', 15, 55, 2, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:26:27', '2025-10-26 16:31:12'),
(33, 'C-20251026-025', 1, 'consultation', 'regular', 'completed', '2025-10-27 00:30:19', NULL, '2025-10-27 00:31:07', '2025-10-27 00:31:13', '2025-10-26', 30, 59, 3, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:30:19', '2025-10-26 16:31:13'),
(34, 'C-20251026-026', 3, 'consultation', 'regular', 'completed', '2025-10-27 00:30:40', NULL, '2025-10-27 00:31:08', '2025-10-27 00:31:14', '2025-10-26', 45, 59, 4, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:30:40', '2025-10-26 16:31:14'),
(35, 'C-20251026-027', 6, 'consultation', 'regular', 'completed', '2025-10-27 00:31:21', NULL, '2025-10-27 00:46:27', '2025-10-27 00:46:33', '2025-10-26', 0, 44, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:31:21', '2025-10-26 16:46:33'),
(36, 'C-20251026-028', 1, 'consultation', 'regular', 'completed', '2025-10-27 00:31:47', NULL, '2025-10-27 00:46:28', '2025-10-27 00:46:34', '2025-10-26', 15, 45, 2, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:31:47', '2025-10-26 16:46:34'),
(37, 'C-20251026-029', 4, 'consultation', 'regular', 'completed', '2025-10-27 00:44:04', NULL, '2025-10-27 00:46:28', '2025-10-27 00:46:35', '2025-10-26', 30, 57, 3, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:44:04', '2025-10-26 16:46:35'),
(38, 'C-20251026-030', 3, 'consultation', 'regular', 'completed', '2025-10-27 00:44:13', NULL, '2025-10-27 00:46:28', '2025-10-27 00:46:35', '2025-10-26', 45, 57, 4, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:44:13', '2025-10-26 16:46:35'),
(39, 'C-20251026-031', 2, 'consultation', 'regular', 'completed', '2025-10-27 00:45:01', NULL, '2025-10-27 00:46:29', '2025-10-27 00:46:36', '2025-10-26', 60, 58, 5, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:45:01', '2025-10-26 16:46:36'),
(40, 'C-20251026-032', 5, 'consultation', 'regular', 'completed', '2025-10-27 00:45:13', NULL, '2025-10-27 00:46:29', '2025-10-27 00:46:37', '2025-10-26', 75, 58, 6, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:45:13', '2025-10-26 16:46:37'),
(41, 'C-20251026-033', 7, 'consultation', 'regular', 'completed', '2025-10-27 00:46:16', NULL, '2025-10-27 00:46:30', '2025-10-27 00:46:37', '2025-10-26', 90, 59, 7, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:46:16', '2025-10-26 16:46:37'),
(42, 'C-20251026-034', 6, 'consultation', 'regular', 'completed', '2025-10-27 00:46:49', NULL, '2025-10-27 00:47:16', '2025-10-27 00:47:29', '2025-10-26', 0, 59, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:46:49', '2025-10-26 16:47:29'),
(43, 'C-20251026-035', 7, 'consultation', 'regular', 'no_show', '2025-10-27 00:46:58', NULL, NULL, NULL, '2025-10-26', 15, 0, 2, NULL, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:46:58', '2025-10-26 16:47:31'),
(44, 'C-20251026-036', 4, 'consultation', 'regular', 'no_show', '2025-10-27 00:47:12', NULL, NULL, NULL, '2025-10-26', 30, 0, 3, NULL, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:47:12', '2025-10-26 16:47:33'),
(45, 'C-20251026-037', 7, 'consultation', 'regular', 'completed', '2025-10-27 00:47:45', NULL, '2025-10-27 00:48:49', '2025-10-27 00:49:42', '2025-10-26', 0, 58, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:47:45', '2025-10-26 16:49:42'),
(46, 'C-20251026-038', 6, 'consultation', 'regular', 'no_show', '2025-10-27 00:50:09', NULL, NULL, NULL, '2025-10-26', 0, 0, 1, NULL, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:50:09', '2025-10-26 16:52:42'),
(47, '39', 6, 'consultation', 'regular', 'no_show', '2025-10-27 00:52:48', NULL, NULL, NULL, '2025-10-26', 0, 0, 1, NULL, NULL, 'RHU', NULL, '', NULL, '2025-10-26 16:52:48', '2025-10-26 17:02:26'),
(48, '40', 7, 'consultation', 'regular', 'completed', '2025-10-27 01:01:02', NULL, '2025-10-27 01:02:47', '2025-10-27 01:02:52', '2025-10-26', 15, 58, 2, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 17:01:02', '2025-10-26 17:02:52'),
(49, '41', 6, 'consultation', 'regular', 'completed', '2025-10-27 01:02:38', NULL, '2025-10-27 01:02:48', '2025-10-27 01:02:53', '2025-10-26', 15, 59, 2, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 17:02:38', '2025-10-26 17:02:53'),
(50, '42', 6, 'consultation', 'regular', 'completed', '2025-10-27 01:03:36', NULL, '2025-10-27 01:04:23', '2025-10-27 01:05:36', '2025-10-26', 0, 57, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 17:03:36', '2025-10-26 17:05:36'),
(51, '43', 7, 'consultation', 'regular', 'completed', '2025-10-27 01:03:46', NULL, '2025-10-27 01:05:42', '2025-10-27 01:06:03', '2025-10-26', 15, 57, 2, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 17:03:46', '2025-10-26 17:06:03'),
(52, '44', 4, 'consultation', 'regular', 'completed', '2025-10-27 01:03:56', NULL, '2025-10-27 01:05:47', '2025-10-27 01:06:05', '2025-10-26', 30, 57, 3, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 17:03:56', '2025-10-26 17:06:05'),
(53, '45', 3, 'consultation', 'regular', 'cancelled', '2025-10-27 01:04:11', NULL, NULL, NULL, '2025-10-26', 45, 0, 4, NULL, NULL, 'RHU', NULL, '', '', '2025-10-26 17:04:11', '2025-10-26 17:05:56'),
(54, '46', 6, 'consultation', 'regular', 'completed', '2025-10-27 01:10:32', NULL, '2025-10-27 01:10:46', '2025-10-27 01:12:03', '2025-10-26', 0, 58, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 17:10:32', '2025-10-26 17:12:03'),
(55, '47', 7, 'consultation', 'regular', 'completed', '2025-10-27 01:10:43', NULL, '2025-10-27 01:11:02', '2025-10-27 01:12:04', '2025-10-26', 15, 58, 2, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 17:10:43', '2025-10-26 17:12:04'),
(56, '48', 6, 'consultation', 'regular', 'completed', '2025-10-27 01:13:18', NULL, '2025-10-27 01:13:32', '2025-10-27 01:27:27', '2025-10-26', 0, 45, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 17:13:18', '2025-10-26 17:27:27'),
(57, '49', 7, 'consultation', 'regular', 'completed', '2025-10-27 01:16:32', NULL, '2025-10-27 01:16:56', '2025-10-27 01:28:16', '2025-10-26', 0, 48, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 17:16:32', '2025-10-26 17:28:16'),
(58, '50', 6, 'consultation', 'regular', 'completed', '2025-10-27 01:29:37', NULL, '2025-10-27 01:29:43', '2025-10-27 01:29:53', '2025-10-26', 0, 59, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-26 17:29:37', '2025-10-26 17:29:53'),
(59, '1', 6, 'consultation', 'regular', 'completed', '2025-10-27 08:47:12', NULL, '2025-10-27 09:03:06', '2025-10-27 09:03:20', '2025-10-27', 0, 43, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-27 00:47:12', '2025-10-27 01:03:20'),
(60, '2', 7, 'consultation', 'regular', 'completed', '2025-10-27 08:47:44', NULL, '2025-10-27 09:03:06', '2025-10-27 09:03:20', '2025-10-27', 15, 44, 2, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-27 00:47:44', '2025-10-27 01:03:20'),
(61, '3', 6, 'consultation', 'regular', 'completed', '2025-10-27 09:03:27', NULL, '2025-10-27 09:24:19', '2025-10-27 09:24:41', '2025-10-27', 0, 38, 1, 2, NULL, 'RHU', NULL, '', NULL, '2025-10-27 01:03:27', '2025-10-27 01:24:41'),
(62, '4', 4, 'consultation', 'regular', 'no_show', '2025-10-27 09:12:29', NULL, NULL, NULL, '2025-10-27', 15, 0, 2, NULL, NULL, 'RHU', NULL, '', NULL, '2025-10-27 01:12:29', '2025-10-27 01:24:30'),
(63, '5', 7, 'consultation', 'regular', 'no_show', '2025-10-27 09:23:22', NULL, NULL, NULL, '2025-10-27', 30, 0, 3, NULL, NULL, 'RHU', NULL, '', NULL, '2025-10-27 01:23:22', '2025-10-27 01:24:31'),
(64, '6', 3, 'consultation', 'regular', 'no_show', '2025-10-27 09:23:43', NULL, NULL, NULL, '2025-10-27', 45, 0, 4, NULL, NULL, 'RHU', NULL, '', NULL, '2025-10-27 01:23:43', '2025-10-27 01:24:34'),
(65, '7', 2, 'consultation', 'regular', 'no_show', '2025-10-27 09:24:03', NULL, NULL, NULL, '2025-10-27', 60, 0, 5, NULL, NULL, 'RHU', NULL, '', NULL, '2025-10-27 01:24:03', '2025-10-27 01:24:35'),
(66, '8', 5, 'consultation', 'regular', 'no_show', '2025-10-27 09:24:07', NULL, NULL, NULL, '2025-10-27', 75, 0, 6, NULL, NULL, 'RHU', NULL, '', NULL, '2025-10-27 01:24:07', '2025-10-27 01:24:37'),
(67, '9', 6, 'consultation', 'regular', 'no_show', '2025-10-27 09:24:47', NULL, NULL, NULL, '2025-10-27', 0, 0, 1, NULL, NULL, 'RHU', NULL, '', NULL, '2025-10-27 01:24:47', '2025-10-27 01:25:48'),
(68, '10', 7, 'consultation', 'regular', 'no_show', '2025-10-27 09:24:52', NULL, NULL, NULL, '2025-10-27', 15, 0, 2, NULL, NULL, 'RHU', NULL, '', NULL, '2025-10-27 01:24:52', '2025-10-27 01:25:49'),
(69, '11', 4, 'consultation', 'regular', 'no_show', '2025-10-27 09:24:58', NULL, NULL, NULL, '2025-10-27', 30, 0, 3, NULL, NULL, 'RHU', NULL, '', NULL, '2025-10-27 01:24:58', '2025-10-27 01:25:50'),
(70, '12', 3, 'consultation', 'regular', 'no_show', '2025-10-27 09:25:06', NULL, NULL, NULL, '2025-10-27', 45, 0, 4, NULL, NULL, 'RHU', NULL, '', NULL, '2025-10-27 01:25:06', '2025-10-27 01:25:50'),
(71, '13', 1, 'consultation', 'regular', 'no_show', '2025-10-27 09:25:18', NULL, NULL, NULL, '2025-10-27', 60, 0, 5, NULL, NULL, 'RHU', NULL, '', NULL, '2025-10-27 01:25:18', '2025-10-27 01:25:51');

-- --------------------------------------------------------

--
-- Table structure for table `queue_analytics`
--

CREATE TABLE `queue_analytics` (
  `id` int(11) NOT NULL,
  `analytics_date` date NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `location_type` enum('RHU','BHS') DEFAULT 'RHU',
  `barangay_id` int(11) DEFAULT NULL,
  `total_queued` int(11) DEFAULT 0,
  `total_served` int(11) DEFAULT 0,
  `total_no_show` int(11) DEFAULT 0,
  `total_cancelled` int(11) DEFAULT 0,
  `avg_wait_time_minutes` decimal(10,2) DEFAULT 0.00,
  `avg_service_time_minutes` decimal(10,2) DEFAULT 0.00,
  `peak_hour` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `queue_analytics`
--

INSERT INTO `queue_analytics` (`id`, `analytics_date`, `service_type`, `location_type`, `barangay_id`, `total_queued`, `total_served`, `total_no_show`, `total_cancelled`, `avg_wait_time_minutes`, `avg_service_time_minutes`, `peak_hour`, `created_at`) VALUES
(1, '2025-10-25', 'certificate', 'RHU', NULL, 1, 1, 0, 0, 54.00, 0.00, NULL, '2025-10-25 06:41:40'),
(2, '2025-10-26', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 57.00, 0.00, NULL, '2025-10-26 10:39:07'),
(3, '2025-10-26', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 59.00, 0.00, NULL, '2025-10-26 10:39:09'),
(4, '2025-10-26', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 8.00, 0.00, NULL, '2025-10-26 13:10:47'),
(5, '2025-10-26', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 8.00, 0.00, NULL, '2025-10-26 13:10:50'),
(6, '2025-10-26', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 8.00, 0.00, NULL, '2025-10-26 13:11:21'),
(7, '2025-10-26', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 8.00, 0.00, NULL, '2025-10-26 13:11:23'),
(8, '2025-10-26', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 41.00, 0.00, NULL, '2025-10-26 15:52:13'),
(9, '2025-10-26', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 41.00, 0.00, NULL, '2025-10-26 15:52:14'),
(10, '2025-10-26', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 42.00, 0.00, NULL, '2025-10-26 15:52:17'),
(11, '2025-10-26', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 46.00, 0.00, NULL, '2025-10-26 15:52:21'),
(12, '2025-10-26', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 54.00, 0.00, NULL, '2025-10-26 15:58:48'),
(13, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 56.00, 0.00, NULL, '2025-10-26 16:02:30'),
(14, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 51.00, 0.00, NULL, '2025-10-26 16:11:07'),
(15, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 57.00, 0.00, NULL, '2025-10-26 16:11:08'),
(16, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 59.00, 0.00, NULL, '2025-10-26 16:11:11'),
(17, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 55.00, 0.00, NULL, '2025-10-26 16:16:12'),
(18, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 59.00, 0.00, NULL, '2025-10-26 16:16:13'),
(19, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 50.00, 0.00, NULL, '2025-10-26 16:26:01'),
(20, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 54.00, 0.00, NULL, '2025-10-26 16:26:02'),
(21, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 54.00, 0.00, NULL, '2025-10-26 16:31:10'),
(22, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 55.00, 0.00, NULL, '2025-10-26 16:31:12'),
(23, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 59.00, 0.00, NULL, '2025-10-26 16:31:13'),
(24, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 59.00, 0.00, NULL, '2025-10-26 16:31:14'),
(25, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 44.00, 0.00, NULL, '2025-10-26 16:46:33'),
(26, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 45.00, 0.00, NULL, '2025-10-26 16:46:34'),
(27, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 57.00, 0.00, NULL, '2025-10-26 16:46:35'),
(28, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 57.00, 0.00, NULL, '2025-10-26 16:46:35'),
(29, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 58.00, 0.00, NULL, '2025-10-26 16:46:36'),
(30, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 58.00, 0.00, NULL, '2025-10-26 16:46:37'),
(31, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 59.00, 0.00, NULL, '2025-10-26 16:46:37'),
(32, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 59.00, 0.00, NULL, '2025-10-26 16:47:29'),
(33, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 58.00, 0.00, NULL, '2025-10-26 16:49:42'),
(34, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 58.00, 0.00, NULL, '2025-10-26 17:02:52'),
(35, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 59.00, 0.00, NULL, '2025-10-26 17:02:53'),
(36, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 57.00, 0.00, NULL, '2025-10-26 17:05:36'),
(37, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 57.00, 0.00, NULL, '2025-10-26 17:06:03'),
(38, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 57.00, 0.00, NULL, '2025-10-26 17:06:05'),
(39, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 58.00, 0.00, NULL, '2025-10-26 17:12:03'),
(40, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 58.00, 0.00, NULL, '2025-10-26 17:12:04'),
(41, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 45.00, 0.00, NULL, '2025-10-26 17:27:27'),
(42, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 48.00, 0.00, NULL, '2025-10-26 17:28:16'),
(43, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 59.00, 0.00, NULL, '2025-10-26 17:29:53'),
(44, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 43.00, 0.00, NULL, '2025-10-27 01:03:20'),
(45, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 44.00, 0.00, NULL, '2025-10-27 01:03:20'),
(46, '2025-10-27', 'consultation', 'RHU', NULL, 1, 1, 0, 0, 38.00, 0.00, NULL, '2025-10-27 01:24:41');

-- --------------------------------------------------------

--
-- Table structure for table `queue_display`
--

CREATE TABLE `queue_display` (
  `id` int(11) NOT NULL,
  `counter_number` int(11) NOT NULL,
  `current_queue_id` int(11) DEFAULT NULL,
  `current_queue_number` varchar(20) DEFAULT NULL,
  `service_type` varchar(50) NOT NULL,
  `status` enum('calling','serving','idle') DEFAULT 'idle',
  `location_type` enum('RHU','BHS') DEFAULT 'RHU',
  `last_called_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `queue_display`
--

INSERT INTO `queue_display` (`id`, `counter_number`, `current_queue_id`, `current_queue_number`, `service_type`, `status`, `location_type`, `last_called_at`, `updated_at`) VALUES
(1, 1, 16, 'C-20251026-008', 'consultation', 'calling', 'RHU', '2025-10-26 21:11:01', '2025-10-26 13:11:01');

-- --------------------------------------------------------

--
-- Table structure for table `queue_notifications`
--

CREATE TABLE `queue_notifications` (
  `id` int(11) NOT NULL,
  `queue_id` int(11) NOT NULL,
  `notification_type` enum('near_turn','now_serving','completed','cancelled') NOT NULL,
  `sent_at` datetime NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `queue_settings`
--

CREATE TABLE `queue_settings` (
  `id` int(11) NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `average_service_time` int(11) DEFAULT 15 COMMENT 'Average minutes per patient',
  `daily_reset_time` time DEFAULT '00:00:00',
  `max_queue_size` int(11) DEFAULT 100,
  `enable_online_checkin` tinyint(1) DEFAULT 1,
  `enable_priority_queue` tinyint(1) DEFAULT 1,
  `counter_count` int(11) DEFAULT 1,
  `location_type` enum('RHU','BHS') DEFAULT 'RHU',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `queue_settings`
--

INSERT INTO `queue_settings` (`id`, `service_type`, `average_service_time`, `daily_reset_time`, `max_queue_size`, `enable_online_checkin`, `enable_priority_queue`, `counter_count`, `location_type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'consultation', 15, '00:00:00', 100, 1, 1, 2, 'RHU', 1, '2025-10-24 13:45:04', '2025-10-24 13:45:04'),
(2, 'laboratory', 10, '00:00:00', 100, 1, 1, 1, 'RHU', 1, '2025-10-24 13:45:04', '2025-10-24 13:45:04'),
(3, 'pharmacy', 5, '00:00:00', 100, 1, 1, 1, 'RHU', 1, '2025-10-24 13:45:04', '2025-10-24 13:45:04'),
(4, 'certificate', 10, '00:00:00', 100, 1, 1, 1, 'RHU', 1, '2025-10-24 13:45:04', '2025-10-24 13:45:04'),
(5, 'maternity', 20, '00:00:00', 100, 1, 1, 1, 'BHS', 1, '2025-10-24 13:45:04', '2025-10-24 13:45:04'),
(6, 'nip', 15, '00:00:00', 100, 1, 1, 1, 'BHS', 1, '2025-10-24 13:45:04', '2025-10-24 13:45:04'),
(7, 'family_planning', 15, '00:00:00', 100, 1, 1, 1, 'BHS', 1, '2025-10-24 13:45:04', '2025-10-24 13:45:04');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` int(11) NOT NULL,
  `referral_number` varchar(20) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `referred_by` int(11) NOT NULL,
  `referred_to_facility` varchar(200) NOT NULL,
  `referred_to_doctor` varchar(200) DEFAULT NULL,
  `referral_reason` text NOT NULL,
  `clinical_summary` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment_given` text DEFAULT NULL,
  `urgency_level` enum('routine','urgent','emergency') DEFAULT 'routine',
  `referral_date` date NOT NULL,
  `expected_return_date` date DEFAULT NULL,
  `transportation_needed` tinyint(1) DEFAULT 0,
  `covid_vaccination_status` enum('primary_series','booster','unvaccinated','unknown') DEFAULT 'unknown',
  `temperature` varchar(10) DEFAULT NULL,
  `blood_pressure` varchar(20) DEFAULT NULL,
  `pulse_rate` varchar(10) DEFAULT NULL,
  `respiratory_rate` varchar(10) DEFAULT NULL,
  `oxygen_saturation` varchar(10) DEFAULT NULL,
  `status` enum('pending','sent','accepted','completed','cancelled') DEFAULT 'pending',
  `feedback` text DEFAULT NULL,
  `return_summary` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `referrals`
--

INSERT INTO `referrals` (`id`, `referral_number`, `patient_id`, `consultation_id`, `referred_by`, `referred_to_facility`, `referred_to_doctor`, `referral_reason`, `clinical_summary`, `diagnosis`, `treatment_given`, `urgency_level`, `referral_date`, `expected_return_date`, `transportation_needed`, `covid_vaccination_status`, `temperature`, `blood_pressure`, `pulse_rate`, `respiratory_rate`, `oxygen_saturation`, `status`, `feedback`, `return_summary`, `created_at`, `updated_at`) VALUES
(1, 'REF-2024-0001', 1, NULL, 3, 'Cavite Provincial Hospital', 'Internal Medicine Department', 'Further evaluation for persistent fever', 'Patient with 5-day history of high-grade fever', 'Acute Febrile Illness', NULL, 'urgent', '2025-09-30', NULL, 0, 'unknown', NULL, NULL, NULL, NULL, NULL, 'completed', NULL, NULL, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(2, 'REF-2025-0001', 2, NULL, 2, 'dsadadwd', 'dasdadwa', 'sdadwad', '\n\nVITAL SIGNS: Temp: 32°C, BP: 32332, PR: 3232 bpm, RR: 1232` cpm, O2 Sat: 13232%', 'wawa', 'wasdadwa', 'emergency', '2025-10-13', NULL, 1, 'primary_series', '32', '32332', '3232', '1232`', '13232', 'cancelled', 'wala lang', NULL, '2025-10-13 03:05:53', '2025-10-17 05:55:33'),
(3, 'REF-2025-0002', 6, NULL, 2, 'Medical Center Imus', 'doctor jeremy', 'needs xray', '\n\nVITAL SIGNS: Temp: 32°C, BP: 32332, PR: 72 bpm, RR: 16 cpm, O2 Sat: 98%', 'may plema', 'inom biogesic', 'routine', '2025-10-18', NULL, 0, 'unvaccinated', '32', '32332', '72', '16', '98', 'pending', NULL, NULL, '2025-10-18 11:45:07', '2025-10-18 11:45:07');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `report_number` varchar(20) NOT NULL,
  `report_type` enum('monthly_summary','consultation_report','medicine_inventory','patient_demographics','service_statistics','financial_report','other') NOT NULL,
  `report_title` varchar(200) NOT NULL,
  `report_period_start` date NOT NULL,
  `report_period_end` date NOT NULL,
  `barangay_id` int(11) DEFAULT NULL,
  `department` enum('RHU','BHS','Pharmacy','Administration') DEFAULT NULL,
  `generated_by` int(11) NOT NULL,
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_data`)),
  `summary_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`summary_data`)),
  `file_path` varchar(255) DEFAULT NULL,
  `report_date` date NOT NULL,
  `status` enum('draft','submitted','approved','published') DEFAULT 'draft',
  `approved_by` int(11) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_types`
--

CREATE TABLE `service_types` (
  `id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `service_category` enum('General','Maternal','Child Care','Family Planning','Dental','Laboratory','Vaccination','Emergency') NOT NULL,
  `description` text DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT 30,
  `fee` decimal(8,2) DEFAULT 0.00,
  `requires_appointment` tinyint(1) DEFAULT 1,
  `available_at` enum('RHU','BHS','Both') DEFAULT 'Both',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_types`
--

INSERT INTO `service_types` (`id`, `service_name`, `service_category`, `description`, `duration_minutes`, `fee`, `requires_appointment`, `available_at`, `is_active`, `created_at`) VALUES
(1, 'General Consultation', 'General', 'Basic medical consultation', 30, 0.00, 1, 'RHU', 1, '2025-10-04 13:00:11'),
(2, 'Prenatal Care', 'Maternal', 'Pregnancy monitoring and care', 45, 0.00, 1, 'BHS', 1, '2025-10-04 13:00:11'),
(3, 'Child Immunization', 'Child Care', 'Vaccination for children', 20, 0.00, 1, 'BHS', 1, '2025-10-04 13:00:11'),
(4, 'Family Planning', 'Family Planning', 'Contraceptive counseling and services', 30, 0.00, 1, 'BHS', 1, '2025-10-04 13:00:11'),
(5, 'Dental Consultation', 'Dental', 'Dental examination and treatment', 60, 50.00, 1, 'RHU', 1, '2025-10-04 13:00:11'),
(6, 'Laboratory Tests', 'Laboratory', 'Various laboratory examinations', 15, 100.00, 1, 'RHU', 1, '2025-10-04 13:00:11'),
(7, 'Blood Pressure Monitoring', 'General', 'Hypertension monitoring', 15, 0.00, 1, 'RHU', 1, '2025-10-04 13:00:11'),
(8, 'Blood Sugar Monitoring', 'General', 'Blood sugar monitoring', 20, 0.00, 1, 'RHU', 1, '2025-10-04 13:00:11'),
(11, 'Postnatal Care', 'Maternal', 'Postpartum checkups for mother and newborn, including breastfeeding support and recovery monitoring', 45, 0.00, 1, 'BHS', 1, '2025-10-05 10:32:33');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `employee_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `department` enum('RHU','BHS','Pharmacy','Administration') NOT NULL,
  `assigned_barangay_id` int(11) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `date_hired` date DEFAULT NULL,
  `date_terminated` date DEFAULT NULL,
  `employment_status` enum('Active','Inactive','Terminated','On Leave') DEFAULT 'Active',
  `license_number` varchar(50) DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `user_id`, `employee_id`, `first_name`, `middle_name`, `last_name`, `suffix`, `position`, `department`, `assigned_barangay_id`, `phone`, `email`, `date_hired`, `date_terminated`, `employment_status`, `license_number`, `license_expiry`, `specialization`, `salary`, `profile_picture`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'SA-001', 'System', '', 'Administrator', NULL, 'System Administrator', 'Administration', NULL, '09171234567', 'superadmin@kawitrhu.gov.ph', '2024-01-01', NULL, 'Active', NULL, NULL, 'System Administration', NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(2, 2, 'RHU-001', 'Roberto', 'M', 'Santos', NULL, 'RHU Administrator', 'RHU', NULL, '09171234568', 'rhuadmin@kawitrhu.gov.ph', '2024-01-15', NULL, 'Active', NULL, NULL, 'Health Administration', NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(3, 3, 'RHU-002', 'Dr. Ana', 'M', 'Reyes', NULL, 'Municipal Health Officer', 'RHU', NULL, '09171234569', 'dr.reyes@kawitrhu.gov.ph', '2024-01-15', NULL, 'Active', 'PRC-12345', NULL, 'General Medicine', NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(4, 4, 'BHS-001', 'Nurse Joy', 'L', 'Mendoza', NULL, 'BHS Coordinator', 'BHS', 1, '09171234570', 'joy.mendoza@kawitrhu.gov.ph', '2024-02-01', NULL, 'Active', 'PRC-67890', NULL, 'Community Health', NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(5, 5, 'BHS-002', 'Nurse Mark', 'D', 'Lopez', NULL, 'BHS Coordinator', 'BHS', 3, '09171234571', 'mark.lopez@kawitrhu.gov.ph', '2024-02-01', NULL, 'Active', 'PRC-67891', NULL, 'Maternal and Child Health', NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(6, 6, 'BHS-003', 'Nurse Lisa', 'C', 'Torres', NULL, 'BHS Coordinator', 'BHS', 4, '09171234572', 'lisa.torres@kawitrhu.gov.ph', '2024-02-01', NULL, 'Active', 'PRC-67892', NULL, 'Family Planning', NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(7, 7, 'PH-001', 'Pharmacist Rose', 'T', 'Tan', NULL, 'Chief Pharmacist', 'Pharmacy', NULL, '09171234573', 'pharmacy@kawitrhu.gov.ph', '2024-01-20', NULL, 'Active', 'PRC-54321', NULL, 'Clinical Pharmacy', NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(8, 8, 'PH-002', 'Pharmacist Michael', 'A', 'Cruz', NULL, 'Staff Pharmacist', 'Pharmacy', NULL, '09171234574', 'rose.tan@kawitrhu.gov.ph', '2024-01-25', NULL, 'Active', 'PRC-54322', NULL, 'Hospital Pharmacy', NULL, NULL, 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11');

-- --------------------------------------------------------

--
-- Table structure for table `system_config`
--

CREATE TABLE `system_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `config_value` text DEFAULT NULL,
  `config_type` enum('string','integer','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_config`
--

INSERT INTO `system_config` (`id`, `config_key`, `config_value`, `config_type`, `description`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 'system_name', 'Kawit RHU Health Information Management System', 'string', 'System name', 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(2, 'system_version', '1.0.0', 'string', 'Current system version', 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(3, 'enable_online_consultation', 'true', 'boolean', 'Enable online consultation feature', 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(4, 'office_hours_start', '08:00', 'string', 'RHU office hours start', 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(5, 'office_hours_end', '17:00', 'string', 'RHU office hours end', 1, '2025-10-04 13:00:11', '2025-10-04 13:00:11');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `log_level` enum('INFO','WARNING','ERROR','CRITICAL') DEFAULT 'INFO',
  `action` varchar(100) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `table_affected` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `user_id`, `log_level`, `action`, `module`, `table_affected`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `session_id`, `created_at`) VALUES
(1, 9, 'INFO', 'LOGIN_FAILED', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-05 01:36:00'),
(2, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-05 01:36:02'),
(3, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-05 01:41:19'),
(4, 9, 'INFO', 'CONSULTATION_REQUEST', 'Consultations', NULL, 4, NULL, '{\"consultation_number\":\"CONS-2025-0001\",\"type\":\"online\",\"priority\":\"urgent\"}', NULL, NULL, NULL, '2025-10-05 01:41:31'),
(5, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 01:43:05'),
(6, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-05 02:02:57'),
(7, 2, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-05 02:03:26'),
(8, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-05 02:03:30'),
(9, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-05 02:22:21'),
(10, 2, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 02:41:18'),
(11, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 02:41:21'),
(12, 9, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 02:41:39'),
(13, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 05:05:31'),
(14, 9, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 05:05:44'),
(15, 10, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 05:05:58'),
(16, 10, 'INFO', 'CONSULTATION_REQUEST', 'Consultations', NULL, 5, NULL, '{\"consultation_number\":\"CONS-2025-0002\",\"type\":\"online\",\"priority\":\"high\"}', NULL, NULL, NULL, '2025-10-05 05:06:28'),
(17, 2, 'INFO', 'CONSULTATION_UPDATED', 'Consultations', NULL, 5, NULL, '{\"updated_by\":\"Roberto Santos\",\"google_meet_link\":\"https:\\/\\/meet.google.com\\/uah-msqo-run\"}', NULL, NULL, NULL, '2025-10-05 05:08:15'),
(18, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 05:14:48'),
(19, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 05:30:41'),
(20, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-05 07:28:51'),
(21, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 5, NULL, '{\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-05 07:48:28'),
(22, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 4, NULL, '{\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-05 07:48:31'),
(23, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 07:49:19'),
(24, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 07:55:08'),
(25, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 08:00:14'),
(26, 9, 'INFO', 'CONSULTATION_REQUEST', 'Consultations', NULL, 6, NULL, '{\"consultation_number\":\"CONS-2025-0003\",\"type\":\"online\",\"priority\":\"low\"}', NULL, NULL, NULL, '2025-10-05 08:00:27'),
(27, 9, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 08:02:31'),
(28, 10, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 08:02:35'),
(29, 10, 'INFO', 'CONSULTATION_REQUEST', 'Consultations', NULL, 7, NULL, '{\"consultation_number\":\"CONS-2025-0004\",\"type\":\"online\",\"priority\":\"low\"}', NULL, NULL, NULL, '2025-10-05 08:02:52'),
(30, 10, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 08:05:03'),
(31, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 08:05:07'),
(32, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 7, NULL, '{\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-05 08:12:35'),
(33, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 6, NULL, '{\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-05 08:12:39'),
(34, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 08:17:46'),
(35, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 08:31:52'),
(36, 9, 'INFO', 'CONSULTATION_REQUEST', 'Consultations', NULL, 8, NULL, '{\"consultation_number\":\"CONS-2025-0005\",\"type\":\"online\",\"priority\":\"medium\"}', NULL, NULL, NULL, '2025-10-05 08:37:24'),
(37, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 08:51:55'),
(38, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 09:10:33'),
(39, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 10:20:03'),
(40, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 10:28:36'),
(41, 9, 'INFO', 'APPOINTMENT_BOOKED', 'Appointments', NULL, 10, NULL, '{\"date\":\"2025-10-05\",\"time\":\"10:00:00\",\"location\":\"RHU\"}', NULL, NULL, NULL, '2025-10-05 12:32:50'),
(42, 9, 'INFO', 'APPOINTMENT_CANCELLED', 'Appointments', NULL, 1, NULL, '{\"reason\":\"n\\/a\"}', NULL, NULL, NULL, '2025-10-05 12:52:48'),
(43, 9, 'INFO', 'APPOINTMENT_CANCELLED', 'Appointments', NULL, 10, NULL, '{\"reason\":\"n\\/a\"}', NULL, NULL, NULL, '2025-10-05 12:53:10'),
(44, 9, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 13:16:19'),
(45, 10, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 13:16:23'),
(46, 10, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 4, NULL, '{\"certificate_type\":\"Medical Clearance\",\"purpose\":\"Employment\"}', NULL, NULL, NULL, '2025-10-05 13:16:39'),
(47, 10, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 13:18:16'),
(48, 13, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 13:18:24'),
(49, 13, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 5, NULL, '{\"certificate_type\":\"Vaccination Certificate\",\"purpose\":\"Employment\"}', NULL, NULL, NULL, '2025-10-05 13:18:50'),
(50, 13, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 13:43:11'),
(51, 13, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 6, NULL, '{\"certificate_type\":\"Fit to Work Certificate\",\"purpose\":\"Employment\"}', NULL, NULL, NULL, '2025-10-05 13:43:23'),
(52, 13, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 7, NULL, '{\"certificate_type\":\"Medical Clearance\",\"purpose\":\"License Application\"}', NULL, NULL, NULL, '2025-10-05 13:43:46'),
(53, 13, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 8, NULL, '{\"certificate_type\":\"Fit to Work Certificate\",\"purpose\":\"School Requirements\"}', NULL, NULL, NULL, '2025-10-05 13:44:02'),
(54, 13, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 13:57:36'),
(55, 13, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 9, NULL, '{\"certificate_type\":\"Medical Certificate\",\"purpose\":\"School Requirements\"}', NULL, NULL, NULL, '2025-10-05 13:59:37'),
(56, 13, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 10, NULL, '{\"certificate_type\":\"Health Certificate\",\"purpose\":\"License Application\"}', NULL, NULL, NULL, '2025-10-05 14:00:00'),
(57, 13, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 14:05:38'),
(58, 13, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-05 14:05:39'),
(59, 13, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 11, NULL, '{\"certificate_type\":\"Health Certificate\",\"purpose\":\"School Requirements\"}', NULL, NULL, NULL, '2025-10-05 14:05:55'),
(60, 2, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-05 14:07:20'),
(61, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-05 14:07:25'),
(62, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-05 14:07:51'),
(63, 9, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 12, NULL, '{\"certificate_type\":\"Vaccination Certificate\",\"purpose\":\"Employment\"}', NULL, NULL, NULL, '2025-10-05 14:08:14'),
(64, 9, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 13, NULL, '{\"certificate_type\":\"Fit to Work Certificate\",\"purpose\":\"Travel\"}', NULL, NULL, NULL, '2025-10-05 14:08:42'),
(65, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-05 15:02:48'),
(66, 9, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 14, NULL, '{\"certificate_type\":\"Fit to Work Certificate\",\"purpose\":\"Visa Application\"}', NULL, NULL, NULL, '2025-10-05 15:03:07'),
(67, 9, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 15, NULL, '{\"certificate_type\":\"Vaccination Certificate\",\"purpose\":\"Travel\"}', NULL, NULL, NULL, '2025-10-05 15:03:55'),
(68, 9, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 16, NULL, '{\"certificate_type\":\"Fit to Work Certificate\",\"purpose\":\"Government Requirements\"}', NULL, NULL, NULL, '2025-10-05 15:04:30'),
(69, 9, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 17, NULL, '{\"certificate_type\":\"Vaccination Certificate\",\"purpose\":\"Travel\"}', NULL, NULL, NULL, '2025-10-05 15:06:07'),
(70, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-06 03:44:13'),
(71, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-06 03:44:27'),
(72, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-06 05:03:13'),
(73, 2, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-06 05:03:18'),
(74, 13, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-06 05:03:22'),
(75, 13, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-06 05:03:50'),
(76, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-06 05:03:53'),
(77, 9, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 18, NULL, '{\"certificate_type\":\"Health Certificate\",\"purpose\":\"School Requirements\"}', NULL, NULL, NULL, '2025-10-06 05:04:17'),
(78, 9, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 19, NULL, '{\"certificate_type\":\"Fit to Work Certificate\",\"purpose\":\"School Requirements\"}', NULL, NULL, NULL, '2025-10-06 05:09:24'),
(79, 2, 'INFO', 'CERTIFICATE_ISSUED', 'Medical Certificates', NULL, 19, NULL, '{\"certificate_number\":\"CERT-2025-0003\",\"fitness_status\":\"Fit\",\"patient\":\"Juan Cruz\"}', NULL, NULL, NULL, '2025-10-06 05:10:43'),
(80, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-06 05:18:23'),
(81, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-06 05:33:36'),
(82, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-06 05:45:59'),
(83, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-06 05:52:05'),
(84, 9, 'INFO', 'APPOINTMENT_BOOKED', 'Appointments', NULL, 11, NULL, '{\"date\":\"2025-10-09\",\"time\":\"09:30:00\",\"location\":\"RHU\"}', NULL, NULL, NULL, '2025-10-06 06:54:34'),
(85, 9, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-06 06:55:37'),
(86, 13, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-06 06:55:40'),
(87, 13, 'INFO', 'APPOINTMENT_BOOKED', 'Appointments', NULL, 12, NULL, '{\"date\":\"2025-10-09\",\"time\":\"09:30:00\",\"location\":\"RHU\"}', NULL, NULL, NULL, '2025-10-06 06:56:06'),
(88, 13, 'INFO', 'APPOINTMENT_BOOKED', 'Appointments', NULL, 13, NULL, '{\"date\":\"2025-10-07\",\"time\":\"00:00:00\",\"location\":\"RHU\"}', NULL, NULL, NULL, '2025-10-06 07:22:23'),
(89, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-06 10:20:30'),
(90, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-06 10:21:21'),
(91, 9, 'INFO', 'APPOINTMENT_CANCELLED', 'Appointments', NULL, 11, NULL, '{\"reason\":\"n\\/a\"}', NULL, NULL, NULL, '2025-10-06 10:22:05'),
(92, 9, 'INFO', 'APPOINTMENT_BOOKED', 'Appointments', NULL, 14, NULL, '{\"date\":\"2025-10-07\",\"time\":\"00:00:00\",\"location\":\"RHU\"}', NULL, NULL, NULL, '2025-10-06 10:22:24'),
(93, 2, 'INFO', 'PASSWORD_CHANGE', 'Users', NULL, 2, NULL, NULL, NULL, NULL, NULL, '2025-10-06 11:10:08'),
(94, 2, 'INFO', 'PASSWORD_CHANGE', 'Users', NULL, 2, NULL, NULL, NULL, NULL, NULL, '2025-10-06 11:10:27'),
(95, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-08 07:11:26'),
(96, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-08 10:47:26'),
(97, 9, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 20, NULL, '{\"certificate_type\":\"Medical Certificate\",\"purpose\":\"Employment\"}', NULL, NULL, NULL, '2025-10-08 10:48:15'),
(98, 2, 'INFO', 'CERTIFICATE_ISSUED', 'Medical Certificates', NULL, 20, NULL, '{\"certificate_number\":\"CERT-2025-0004\",\"fitness_status\":\"Fit\",\"patient\":\"Juan Cruz\"}', NULL, NULL, NULL, '2025-10-08 10:49:03'),
(99, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-09 12:29:51'),
(100, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-10 10:36:35'),
(101, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-10 10:54:28'),
(102, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-10 11:57:26'),
(103, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-10 12:26:38'),
(104, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-10 12:35:33'),
(105, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-10 12:53:46'),
(106, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-12 05:14:58'),
(107, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-12 05:25:53'),
(108, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-12 05:39:01'),
(109, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-12 05:56:06'),
(110, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-12 06:12:32'),
(111, 9, 'INFO', 'CONSULTATION_REQUEST', 'Consultations', NULL, 9, NULL, '{\"consultation_number\":\"CONS-2025-0006\",\"type\":\"online\",\"priority\":\"medium\"}', NULL, NULL, NULL, '2025-10-12 06:13:16'),
(112, 9, 'INFO', 'APPOINTMENT_BOOKED', 'Appointments', NULL, 15, NULL, '{\"date\":\"2025-10-16\",\"time\":\"00:00:00\",\"location\":\"RHU\"}', NULL, NULL, NULL, '2025-10-12 06:15:42'),
(113, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 9, NULL, '{\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-12 06:25:28'),
(114, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 8, NULL, '{\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-12 06:25:31'),
(115, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-12 06:39:27'),
(116, 9, 'INFO', 'APPOINTMENT_CANCELLED', 'Appointments', NULL, 15, NULL, '{\"reason\":\"n\\/a\"}', NULL, NULL, NULL, '2025-10-12 07:04:29'),
(117, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-12 07:12:31'),
(118, 9, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-12 07:13:41'),
(119, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-12 09:35:11'),
(120, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-12 10:35:58'),
(121, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-12 10:38:26'),
(122, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-12 11:04:34'),
(123, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-13 02:35:17'),
(124, 2, 'INFO', 'REFERRAL_CREATED', 'Referrals', NULL, 2, NULL, '{\"referral_number\":\"REF-2025-0001\",\"patient_id\":\"2\"}', '::1', NULL, NULL, '2025-10-13 03:05:53'),
(125, 2, 'INFO', 'REFERRAL_STATUS_UPDATED', 'Referrals', NULL, 2, NULL, '{\"status\":\"cancelled\"}', '::1', NULL, NULL, '2025-10-13 03:06:50'),
(126, 2, 'INFO', 'PATIENT_REGISTERED', 'Patients', NULL, 6, NULL, '{\"patient_id\":\"P-2025-0001\",\"registered_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-13 03:11:05'),
(127, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-13 04:13:53'),
(128, 9, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-13 04:13:57'),
(129, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-13 04:14:02'),
(130, 14, 'INFO', 'CONSULTATION_REQUEST', 'Consultations', NULL, 10, NULL, '{\"consultation_number\":\"CONS-2025-0007\",\"type\":\"online\",\"priority\":\"medium\"}', NULL, NULL, NULL, '2025-10-13 04:14:29'),
(131, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 10, NULL, '{\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-13 04:14:53'),
(132, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-13 04:20:48'),
(133, 14, 'INFO', 'APPOINTMENT_BOOKED', 'Appointments', NULL, 16, NULL, '{\"date\":\"2025-10-15\",\"time\":\"00:00:00\",\"location\":\"RHU\"}', NULL, NULL, NULL, '2025-10-13 04:21:01'),
(134, 2, 'INFO', 'APPOINTMENT_STATUS_UPDATED', 'Appointments', NULL, 16, NULL, '{\"status\":\"confirmed\",\"updated_by\":\"Roberto Santos\",\"appointment_time\":\"08:00:00\"}', NULL, NULL, NULL, '2025-10-13 04:22:27'),
(135, 14, 'INFO', 'APPOINTMENT_CANCELLED', 'Appointments', NULL, 16, NULL, '{\"reason\":\"n\\/a\"}', NULL, NULL, NULL, '2025-10-13 04:36:33'),
(136, 14, 'INFO', 'APPOINTMENT_BOOKED', 'Appointments', NULL, 17, NULL, '{\"date\":\"2025-10-14\",\"time\":\"00:00:00\",\"location\":\"RHU\"}', NULL, NULL, NULL, '2025-10-13 04:36:51'),
(137, 2, 'INFO', 'APPOINTMENT_STATUS_UPDATED', 'Appointments', NULL, 17, NULL, '{\"status\":\"confirmed\",\"updated_by\":\"Roberto Santos\",\"appointment_time\":\"10:00:00\"}', NULL, NULL, NULL, '2025-10-13 04:37:29'),
(138, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-13 12:22:13'),
(139, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-13 12:22:23'),
(140, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 11, NULL, '{\"consultation_number\":\"CONS-2025-0008\",\"doctor\":\"Roberto Santos\",\"patient_id\":\"6\"}', NULL, NULL, NULL, '2025-10-13 12:24:13'),
(141, 14, 'INFO', 'APPOINTMENT_BOOKED', 'Appointments', NULL, 18, NULL, '{\"date\":\"2025-10-14\",\"time\":\"00:00:00\",\"location\":\"RHU\"}', NULL, NULL, NULL, '2025-10-13 13:52:21'),
(142, 2, 'INFO', 'APPOINTMENT_RESCHEDULED', 'Appointments', NULL, 18, NULL, '{\"new_date\":\"2025-10-14\",\"new_time\":\"09:00:00\",\"reason\":\"\",\"rescheduled_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-13 13:53:26'),
(143, 14, 'INFO', 'APPOINTMENT_BOOKED', 'Appointments', NULL, 19, NULL, '{\"date\":\"2025-10-14\",\"time\":\"00:00:00\",\"location\":\"RHU\"}', NULL, NULL, NULL, '2025-10-13 13:54:21'),
(144, 2, 'INFO', 'APPOINTMENT_STATUS_UPDATED', 'Appointments', NULL, 19, NULL, '{\"status\":\"confirmed\",\"updated_by\":\"Roberto Santos\",\"appointment_time\":\"08:00:00\"}', NULL, NULL, NULL, '2025-10-13 13:55:31'),
(145, 14, 'INFO', 'APPOINTMENT_CANCELLED', 'Appointments', NULL, 19, NULL, '{\"reason\":\"n\\/a\"}', NULL, NULL, NULL, '2025-10-13 13:56:09'),
(146, 14, 'INFO', 'APPOINTMENT_BOOKED', 'Appointments', NULL, 20, NULL, '{\"date\":\"2025-10-14\",\"time\":\"00:00:00\",\"location\":\"RHU\"}', NULL, NULL, NULL, '2025-10-13 13:56:22'),
(147, 2, 'INFO', 'APPOINTMENT_RESCHEDULED', 'Appointments', NULL, 20, NULL, '{\"new_date\":\"2025-10-15\",\"new_time\":\"09:00:00\",\"reason\":\"too many scheduled that time\\r\\n\",\"rescheduled_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-13 13:59:48'),
(148, 14, 'INFO', 'APPOINTMENT_BOOKED', 'Appointments', NULL, 21, NULL, '{\"date\":\"2025-10-14\",\"time\":\"00:00:00\",\"location\":\"RHU\"}', NULL, NULL, NULL, '2025-10-13 14:09:33'),
(149, 2, 'INFO', 'APPOINTMENT_RESCHEDULED', 'Appointments', NULL, 21, NULL, '{\"new_date\":\"2025-10-16\",\"new_time\":\"11:00:00\",\"reason\":\"\",\"rescheduled_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-13 14:09:47'),
(150, 14, 'INFO', 'APPOINTMENT_CANCELLED', 'Appointments', NULL, 21, NULL, '{\"reason\":\"n\\/a\"}', NULL, NULL, NULL, '2025-10-13 14:20:09'),
(151, 14, 'INFO', 'APPOINTMENT_CANCELLED', 'Appointments', NULL, 20, NULL, '{\"reason\":\"n\\/a\"}', NULL, NULL, NULL, '2025-10-13 14:20:13'),
(152, 14, 'INFO', 'APPOINTMENT_CANCELLED', 'Appointments', NULL, 18, NULL, '{\"reason\":\"n\\/a\"}', NULL, NULL, NULL, '2025-10-13 14:20:17'),
(153, 14, 'INFO', 'APPOINTMENT_BOOKED', 'Appointments', NULL, 22, NULL, '{\"date\":\"2025-10-14\",\"time\":\"00:00:00\",\"location\":\"RHU\"}', NULL, NULL, NULL, '2025-10-13 14:28:34'),
(154, 14, 'INFO', 'APPOINTMENT_CANCELLED', 'Appointments', NULL, 22, NULL, '{\"reason\":\"n\\/a\"}', NULL, NULL, NULL, '2025-10-13 14:33:04'),
(155, 14, 'INFO', 'APPOINTMENT_BOOKED', 'Appointments', NULL, 23, NULL, '{\"date\":\"2025-10-14\",\"time\":\"00:00:00\",\"location\":\"RHU\"}', NULL, NULL, NULL, '2025-10-13 14:33:26'),
(156, 14, 'INFO', 'APPOINTMENT_BOOKED', 'Appointments', NULL, 24, NULL, '{\"date\":\"2025-10-15\",\"time\":\"00:00:00\",\"location\":\"RHU\"}', NULL, NULL, NULL, '2025-10-13 14:33:59'),
(157, 2, 'INFO', 'APPOINTMENT_STATUS_UPDATED', 'Appointments', NULL, 23, NULL, '{\"status\":\"confirmed\",\"updated_by\":\"Roberto Santos\",\"appointment_time\":\"11:00:00\"}', NULL, NULL, NULL, '2025-10-13 14:35:30'),
(158, 14, 'INFO', 'APPOINTMENT_CANCELLED', 'Appointments', NULL, 24, NULL, '{\"reason\":\"n\\/a\"}', NULL, NULL, NULL, '2025-10-13 14:35:45'),
(159, 14, 'INFO', 'APPOINTMENT_CANCELLED', 'Appointments', NULL, 23, NULL, '{\"reason\":\"n\\/a\"}', NULL, NULL, NULL, '2025-10-13 14:36:09'),
(160, 14, 'INFO', 'APPOINTMENT_BOOKED', 'Appointments', NULL, 25, NULL, '{\"date\":\"2025-10-14\",\"time\":\"00:00:00\",\"location\":\"RHU\"}', NULL, NULL, NULL, '2025-10-13 14:36:39'),
(161, 2, 'INFO', 'APPOINTMENT_RESCHEDULED', 'Appointments', NULL, 25, NULL, '{\"new_date\":\"2025-10-15\",\"new_time\":\"11:00:00\",\"reason\":\"\",\"rescheduled_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-13 14:37:03'),
(162, 14, 'INFO', 'APPOINTMENT_BOOKED', 'Appointments', NULL, 26, NULL, '{\"date\":\"2025-10-14\",\"time\":\"00:00:00\",\"location\":\"RHU\"}', NULL, NULL, NULL, '2025-10-13 14:57:19'),
(163, 14, 'INFO', 'CONSULTATION_REQUEST', 'Consultations', NULL, 12, NULL, '{\"consultation_number\":\"CONS-2025-0009\",\"type\":\"online\",\"priority\":\"medium\"}', NULL, NULL, NULL, '2025-10-13 14:57:29'),
(164, 2, 'INFO', 'CONSULTATION_UPDATED', 'Consultations', NULL, 12, NULL, '{\"updated_by\":\"Roberto Santos\",\"google_meet_link\":\"https:\\/\\/meet.google.com\\/myo-tbnu-gnr\"}', NULL, NULL, NULL, '2025-10-13 14:59:40'),
(165, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-13 15:09:19'),
(166, 14, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 21, NULL, '{\"certificate_type\":\"Health Certificate\",\"purpose\":\"Insurance\"}', NULL, NULL, NULL, '2025-10-13 15:09:27'),
(167, 2, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-13 15:10:45'),
(168, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-13 15:10:50'),
(169, 2, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-13 15:11:26'),
(170, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-13 15:11:33'),
(171, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-13 15:11:41'),
(172, 2, 'INFO', 'CERTIFICATE_ISSUED', 'Medical Certificates', NULL, 21, NULL, '{\"certificate_number\":\"CERT-2025-0005\",\"fitness_status\":\"Fit with Restrictions\",\"patient\":\"jeremayah gayondato\"}', NULL, NULL, NULL, '2025-10-13 15:13:03'),
(173, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-13 15:15:06'),
(174, 14, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-13 15:15:18'),
(175, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-13 15:15:20'),
(176, 9, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 22, NULL, '{\"certificate_type\":\"Medical Clearance\",\"purpose\":\"Employment\"}', NULL, NULL, NULL, '2025-10-13 15:15:31'),
(177, 2, 'INFO', 'CERTIFICATE_ISSUED', 'Medical Certificates', NULL, 22, NULL, '{\"certificate_number\":\"CERT-2025-0006\",\"fitness_status\":\"Unfit\",\"patient\":\"Juan Cruz\"}', NULL, NULL, NULL, '2025-10-13 15:15:46'),
(178, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-13 15:25:44'),
(179, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-13 15:34:23'),
(180, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-13 15:49:08'),
(181, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-13 15:56:34'),
(182, 9, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 23, NULL, '{\"certificate_type\":\"Vaccination Certificate\",\"purpose\":\"School Requirements\"}', NULL, NULL, NULL, '2025-10-13 15:56:46'),
(183, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-13 16:07:43'),
(184, 2, 'INFO', 'CERTIFICATE_ISSUED', 'Medical Certificates', NULL, 23, NULL, '{\"certificate_number\":\"CERT-2025-0007\",\"fitness_status\":\"Fit\",\"patient\":\"Juan Cruz\"}', NULL, NULL, NULL, '2025-10-13 16:26:49'),
(185, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-14 01:58:49'),
(186, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-14 02:12:38'),
(187, 2, 'INFO', 'APPOINTMENT_STATUS_UPDATED', 'Appointments', NULL, 26, NULL, '{\"status\":\"confirmed\",\"updated_by\":\"Roberto Santos\",\"appointment_time\":\"11:00:00\"}', NULL, NULL, NULL, '2025-10-14 02:13:03'),
(188, 2, 'INFO', 'CONSULTATION_UPDATED', 'Consultations', NULL, 12, NULL, '{\"updated_by\":\"Roberto Santos\",\"google_meet_link\":\"https:\\/\\/meet.google.com\\/myo-tbnu-gnr\"}', NULL, NULL, NULL, '2025-10-14 02:21:27'),
(189, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 12, NULL, '{\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-14 02:24:36'),
(190, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 13, NULL, '{\"consultation_number\":\"CONS-2025-0010\",\"doctor\":\"Roberto Santos\",\"patient_id\":\"6\"}', NULL, NULL, NULL, '2025-10-14 02:26:17'),
(191, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 14, NULL, '{\"consultation_number\":\"CONS-2025-0011\",\"doctor\":\"Roberto Santos\",\"patient_id\":\"1\"}', NULL, NULL, NULL, '2025-10-14 02:27:19'),
(192, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-14 03:12:20'),
(193, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-14 03:22:40'),
(194, 14, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-14 03:22:45'),
(195, 13, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-14 03:22:49'),
(196, 13, 'INFO', 'CONSULTATION_REQUEST', 'Consultations', NULL, 15, NULL, '{\"consultation_number\":\"CONS-2025-0012\",\"type\":\"online\",\"priority\":\"medium\"}', NULL, NULL, NULL, '2025-10-14 03:23:01'),
(197, 2, 'INFO', 'CONSULTATION_UPDATED', 'Consultations', NULL, 15, NULL, '{\"updated_by\":\"Roberto Santos\",\"google_meet_link\":\"https:\\/\\/meet.google.com\\/yuh-phov-qed\"}', NULL, NULL, NULL, '2025-10-14 03:35:18'),
(198, 13, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-14 03:35:37'),
(199, 13, 'INFO', 'CONSULTATION_REQUEST', 'Consultations', NULL, 16, NULL, '{\"consultation_number\":\"CONS-2025-0001\",\"type\":\"online\",\"priority\":\"medium\"}', NULL, NULL, NULL, '2025-10-14 03:38:44'),
(200, 2, 'INFO', 'CONSULTATION_UPDATED', 'Consultations', NULL, 16, NULL, '{\"updated_by\":\"Roberto Santos\",\"google_meet_link\":\"https:\\/\\/meet.google.com\\/yuh-phov-qed\"}', NULL, NULL, NULL, '2025-10-14 03:39:59'),
(201, 13, 'INFO', 'CONSULTATION_REQUEST', 'Consultations', NULL, 17, NULL, '{\"consultation_number\":\"CONS-2025-0001\",\"type\":\"online\",\"priority\":\"medium\"}', NULL, NULL, NULL, '2025-10-14 03:42:08'),
(202, 13, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-14 03:43:38'),
(203, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-14 03:43:42'),
(204, 14, 'INFO', 'CONSULTATION_REQUEST', 'Consultations', NULL, 18, NULL, '{\"consultation_number\":\"CONS-2025-0002\",\"type\":\"online\",\"priority\":\"medium\"}', NULL, NULL, NULL, '2025-10-14 03:44:01'),
(205, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-14 08:31:46'),
(206, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-14 08:32:57'),
(207, 14, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 24, NULL, '{\"certificate_type\":\"Health Certificate\",\"purpose\":\"Employment\"}', NULL, NULL, NULL, '2025-10-14 08:36:40'),
(208, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-14 08:43:14'),
(209, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-16 05:35:39'),
(210, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 05:37:18'),
(211, 14, 'INFO', 'CERTIFICATE_REQUEST', 'Medical Certificates', NULL, 25, NULL, '{\"certificate_type\":\"Fit to Work Certificate\",\"purpose\":\"Employment\"}', NULL, NULL, NULL, '2025-10-16 05:51:00'),
(212, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 06:06:08'),
(213, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 06:48:38'),
(214, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 06:54:45'),
(215, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 07:04:39'),
(216, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 10:47:58'),
(217, 2, 'INFO', 'CERTIFICATE_APPROVED_FOR_CHECKUP', 'Medical Certificates', NULL, 25, NULL, '{\"certificate_number\":\"CERT-2025-0009\",\"patient_name\":\"jeremayah gayondato\",\"checkup_date\":\"2025-10-16\",\"checkup_time\":\"09:00\",\"approved_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-16 11:28:07'),
(218, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 11:28:24'),
(219, 2, 'INFO', 'CHECKUP_COMPLETED', 'Medical Certificates', NULL, 25, NULL, '{\"certificate_number\":\"CERT-2025-0009\",\"patient_name\":\"jeremayah gayondato\",\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-16 11:29:27'),
(220, 2, 'INFO', 'CERTIFICATE_READY_FOR_DOWNLOAD', 'Medical Certificates', NULL, 25, NULL, '{\"certificate_number\":\"CERT-2025-0009\",\"fitness_status\":\"Fit\",\"patient\":\"jeremayah gayondato\"}', NULL, NULL, NULL, '2025-10-16 11:30:23'),
(221, 14, 'INFO', 'CERTIFICATE_DOWNLOADED', 'Medical Certificates', NULL, 25, NULL, NULL, NULL, NULL, NULL, '2025-10-16 11:30:37'),
(222, 2, 'INFO', 'CERTIFICATE_APPROVED_FOR_CHECKUP', 'Medical Certificates', NULL, 19, NULL, '{\"certificate_number\":\"CERT-2025-0003\",\"patient_name\":\"Juan Cruz\",\"checkup_date\":\"2025-10-17\",\"checkup_time\":\"09:00\",\"approved_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-16 11:31:23'),
(223, 14, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 11:31:38'),
(224, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 11:31:43'),
(225, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 11:52:53'),
(226, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 12:00:30'),
(227, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 12:07:30'),
(228, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 12:13:49'),
(229, 9, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 12:14:00'),
(230, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 12:14:04'),
(231, 2, 'INFO', 'CERTIFICATE_APPROVED_FOR_CHECKUP', 'Medical Certificates', NULL, 21, NULL, '{\"certificate_number\":\"CERT-2025-0005\",\"patient_name\":\"jeremayah gayondato\",\"checkup_date\":\"2025-10-17\",\"checkup_time\":\"09:00\",\"approved_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-16 12:14:53');
INSERT INTO `system_logs` (`id`, `user_id`, `log_level`, `action`, `module`, `table_affected`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `session_id`, `created_at`) VALUES
(232, 2, 'INFO', 'CHECKUP_COMPLETED', 'Medical Certificates', NULL, 21, NULL, '{\"certificate_number\":\"CERT-2025-0005\",\"patient_name\":\"jeremayah gayondato\",\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-16 12:15:20'),
(233, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 12:28:11'),
(234, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 18, NULL, '{\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-16 12:29:56'),
(235, 2, 'INFO', 'CERTIFICATE_READY_FOR_DOWNLOAD', 'Medical Certificates', NULL, 21, NULL, '{\"certificate_number\":\"CERT-2025-0005\",\"fitness_status\":\"Unfit\",\"patient\":\"jeremayah gayondato\"}', NULL, NULL, NULL, '2025-10-16 12:41:55'),
(236, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 12:43:55'),
(237, 14, 'INFO', 'CERTIFICATE_DOWNLOADED', 'Medical Certificates', NULL, 21, NULL, NULL, NULL, NULL, NULL, '2025-10-16 12:44:04'),
(238, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-16 15:40:27'),
(239, 2, 'INFO', 'CHECKUP_COMPLETED', 'Medical Certificates', NULL, 19, NULL, '{\"certificate_number\":\"CERT-2025-0003\",\"patient_name\":\"Juan Cruz\",\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-16 15:58:42'),
(240, 2, 'INFO', 'CERTIFICATE_READY_FOR_DOWNLOAD', 'Medical Certificates', NULL, 19, NULL, '{\"certificate_number\":\"CERT-2025-0003\",\"fitness_status\":\"Unfit\",\"patient\":\"Juan Cruz\"}', NULL, NULL, NULL, '2025-10-16 15:58:53'),
(241, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 16:03:20'),
(242, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 16:26:11'),
(243, 2, 'INFO', 'CERTIFICATE_APPROVED_FOR_CHECKUP', 'Medical Certificates', NULL, 17, NULL, '{\"certificate_number\":\"CERT-2025-0014\",\"patient_name\":\"Juan Cruz\",\"checkup_date\":\"2025-10-18\",\"checkup_time\":\"09:00\",\"approved_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-16 16:26:43'),
(244, 14, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 16:26:53'),
(245, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-16 16:26:56'),
(246, 9, 'INFO', 'CERTIFICATE_DOWNLOADED', 'Medical Certificates', NULL, 19, NULL, NULL, NULL, NULL, NULL, '2025-10-16 16:27:05'),
(247, 2, 'INFO', 'CHECKUP_COMPLETED', 'Medical Certificates', NULL, 17, NULL, '{\"certificate_number\":\"CERT-2025-0014\",\"patient_name\":\"Juan Cruz\",\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-16 16:27:16'),
(248, 2, 'INFO', 'CERTIFICATE_READY_FOR_DOWNLOAD', 'Medical Certificates', NULL, 17, NULL, '{\"certificate_number\":\"CERT-2025-0014\",\"fitness_status\":\"Fit with Restrictions\",\"patient\":\"Juan Cruz\"}', NULL, NULL, NULL, '2025-10-16 16:27:59'),
(249, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-17 03:08:59'),
(250, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-17 03:10:19'),
(251, 2, 'INFO', 'PATIENT_REGISTERED', 'Patients', NULL, 7, NULL, '{\"patient_id\":\"P-2025-0002\",\"registered_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-17 03:11:56'),
(252, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-17 05:24:03'),
(253, 2, 'INFO', 'PASSWORD_CHANGED', 'Patients', NULL, 6, NULL, '{\"changed_by\":\"Roberto Santos\",\"patient_name\":\"jeremayah gayondato\"}', NULL, NULL, NULL, '2025-10-17 05:44:36'),
(254, 14, 'INFO', 'LOGIN_FAILED', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-17 05:44:49'),
(255, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-17 05:44:53'),
(256, 14, 'INFO', 'PASSWORD_CHANGE', 'Users', NULL, 14, NULL, NULL, NULL, NULL, NULL, '2025-10-17 05:45:16'),
(257, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 17, NULL, '{\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-17 05:53:48'),
(258, 2, 'INFO', 'REFERRAL_STATUS_UPDATED', 'Referrals', NULL, 2, NULL, '{\"status\":\"cancelled\"}', '::1', NULL, NULL, '2025-10-17 05:55:33'),
(259, 14, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-17 05:55:53'),
(260, 10, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-17 05:55:57'),
(261, 2, 'INFO', 'CERTIFICATE_APPROVED_FOR_CHECKUP', 'Medical Certificates', NULL, 24, NULL, '{\"certificate_number\":\"CERT-2025-0008\",\"patient_name\":\"jeremayah gayondato\",\"checkup_date\":\"2025-10-18\",\"checkup_time\":\"09:00\",\"approved_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-17 07:47:55'),
(262, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-17 07:48:04'),
(263, 2, 'INFO', 'CHECKUP_COMPLETED', 'Medical Certificates', NULL, 24, NULL, '{\"certificate_number\":\"CERT-2025-0008\",\"patient_name\":\"jeremayah gayondato\",\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-17 07:59:42'),
(264, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-17 08:01:43'),
(265, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-17 09:07:20'),
(266, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-17 10:49:48'),
(267, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-17 11:00:14'),
(268, 2, 'INFO', 'CERTIFICATE_ISSUED', 'Medical Certificates', NULL, 24, NULL, '{\"certificate_number\":\"CERT-2025-0008\",\"consultation_id\":\"19\",\"fitness_status\":\"Fit\",\"patient\":\"jeremayah gayondato\",\"issued_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-17 12:16:24'),
(269, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-17 12:17:03'),
(270, 14, 'INFO', 'CERTIFICATE_DOWNLOADED', 'Medical Certificates', NULL, 24, NULL, NULL, NULL, NULL, NULL, '2025-10-17 12:17:10'),
(271, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-17 12:35:00'),
(272, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-17 12:52:06'),
(273, 2, 'INFO', 'CERTIFICATE_APPROVED_FOR_CHECKUP', 'Medical Certificates', NULL, 18, NULL, '{\"certificate_number\":\"CERT-2025-0002\",\"patient_name\":\"Juan Cruz\",\"checkup_date\":\"2025-10-21\",\"checkup_time\":\"09:00\",\"approved_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-17 12:57:03'),
(274, 2, 'INFO', 'CHECKUP_COMPLETED', 'Medical Certificates', NULL, 18, NULL, '{\"certificate_number\":\"CERT-2025-0002\",\"patient_name\":\"Juan Cruz\",\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-17 12:57:09'),
(275, 2, 'INFO', 'CERTIFICATE_ISSUED', 'Medical Certificates', NULL, 18, NULL, '{\"certificate_number\":\"CERT-2025-0002\",\"consultation_id\":\"31\",\"fitness_status\":\"Fit\",\"patient\":\"Juan Cruz\",\"issued_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-17 13:08:26'),
(276, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-17 13:08:54'),
(277, 9, 'INFO', 'CERTIFICATE_DOWNLOADED', 'Medical Certificates', NULL, 17, NULL, NULL, NULL, NULL, NULL, '2025-10-17 13:09:06'),
(278, 9, 'INFO', 'CERTIFICATE_DOWNLOADED', 'Medical Certificates', NULL, 18, NULL, NULL, NULL, NULL, NULL, '2025-10-17 13:09:09'),
(279, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-17 13:15:14'),
(280, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-17 13:21:30'),
(281, 9, 'INFO', 'CERTIFICATE_REQUEST_SUBMITTED', 'Medical Certificates', NULL, 32, NULL, '{\"certificate_type\":\"Fit to Work Certificate\",\"purpose\":\"Employment\",\"certificate_number\":\"CERT-2025-0001\"}', NULL, NULL, NULL, '2025-10-17 13:21:39'),
(282, 2, 'INFO', 'CERTIFICATE_APPROVED_FOR_CHECKUP', 'Medical Certificates', NULL, 32, NULL, '{\"certificate_number\":\"CERT-2025-0001\",\"patient_name\":\"Juan Cruz\",\"checkup_date\":\"2025-10-18\",\"checkup_time\":\"00:00\",\"approved_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-17 13:22:23'),
(283, 2, 'INFO', 'CHECKUP_COMPLETED', 'Medical Certificates', NULL, 32, NULL, '{\"certificate_number\":\"CERT-2025-0001\",\"patient_name\":\"Juan Cruz\",\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-17 13:22:43'),
(284, 2, 'INFO', 'CERTIFICATE_ISSUED', 'Medical Certificates', NULL, 32, NULL, '{\"certificate_number\":\"CERT-2025-0001\",\"consultation_id\":\"32\",\"fitness_status\":\"Fit\",\"patient\":\"Juan Cruz\",\"issued_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-17 13:23:00'),
(285, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-17 13:31:34'),
(286, 9, 'INFO', 'CERTIFICATE_DOWNLOADED', 'Medical Certificates', NULL, 32, NULL, NULL, NULL, NULL, NULL, '2025-10-17 13:32:11'),
(287, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-18 03:25:28'),
(288, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-18 03:26:20'),
(289, 9, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-18 03:27:19'),
(290, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-18 03:27:21'),
(291, 14, 'INFO', 'CERTIFICATE_REQUEST_SUBMITTED', 'Medical Certificates', NULL, 33, NULL, '{\"certificate_type\":\"Fit to Work Certificate\",\"purpose\":\"Employment\",\"certificate_number\":\"CERT-2025-0002\"}', NULL, NULL, NULL, '2025-10-18 03:27:41'),
(292, 2, 'INFO', 'CERTIFICATE_APPROVED_FOR_CHECKUP', 'Medical Certificates', NULL, 33, NULL, '{\"certificate_number\":\"CERT-2025-0002\",\"patient_name\":\"jeremayah gayondato\",\"checkup_date\":\"2025-10-19\",\"checkup_time\":\"09:00\",\"approved_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-18 03:28:01'),
(293, 2, 'INFO', 'CHECKUP_COMPLETED', 'Medical Certificates', NULL, 33, NULL, '{\"certificate_number\":\"CERT-2025-0002\",\"patient_name\":\"jeremayah gayondato\",\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-18 03:28:20'),
(294, 2, 'INFO', 'CERTIFICATE_ISSUED', 'Medical Certificates', NULL, 33, NULL, '{\"certificate_number\":\"CERT-2025-0002\",\"consultation_id\":\"33\",\"fitness_status\":\"Fit\",\"patient\":\"jeremayah gayondato\",\"issued_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-18 04:02:03'),
(295, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-18 04:02:12'),
(296, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-18 04:02:18'),
(297, 14, 'INFO', 'CERTIFICATE_DOWNLOADED', 'Medical Certificates', NULL, 33, NULL, NULL, NULL, NULL, NULL, '2025-10-18 04:02:38'),
(298, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-18 04:22:03'),
(299, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-18 04:32:16'),
(300, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-18 04:39:00'),
(301, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-18 04:45:37'),
(302, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-18 04:53:31'),
(303, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-18 10:10:20'),
(304, 2, 'INFO', 'LAB_RESULT_CREATED', 'Laboratory', NULL, 2, NULL, '{\"lab_number\":\"LAB-2025-0001\",\"test_type\":\"complete blood count\"}', NULL, NULL, NULL, '2025-10-18 10:12:13'),
(305, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-18 10:23:40'),
(306, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-18 10:28:52'),
(307, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-18 10:44:07'),
(308, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-18 10:53:34'),
(309, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-18 11:14:24'),
(310, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-18 11:44:58'),
(311, 2, 'INFO', 'REFERRAL_CREATED', 'Referrals', NULL, 3, NULL, '{\"referral_number\":\"REF-2025-0002\",\"patient_id\":\"6\"}', '::1', NULL, NULL, '2025-10-18 11:45:07'),
(312, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-18 12:58:39'),
(313, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-20 05:53:28'),
(314, 9, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-20 05:53:39'),
(315, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-20 05:53:47'),
(316, 14, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-20 05:53:52'),
(317, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-20 05:54:02'),
(318, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-20 05:54:49'),
(319, 10, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-20 05:57:26'),
(320, 2, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-20 05:57:38'),
(321, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-20 05:57:52'),
(322, 14, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-20 05:59:20'),
(323, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-20 05:59:31'),
(324, 2, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-20 06:20:45'),
(325, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-20 06:20:57'),
(326, 14, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-20 06:23:08'),
(327, 2, 'INFO', 'LOGIN_FAILED', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-20 06:23:13'),
(328, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-20 06:23:15'),
(329, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-21 01:44:27'),
(330, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-21 03:01:14'),
(331, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-21 03:13:17'),
(332, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-21 10:13:23'),
(333, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-21 10:40:21'),
(334, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-23 08:09:08'),
(335, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-23 08:36:31'),
(336, 9, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-23 08:49:51'),
(337, 10, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 08:50:52'),
(338, 10, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 09:05:59'),
(339, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 09:06:03'),
(340, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 09:57:00'),
(341, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-23 09:57:19'),
(342, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-23 15:50:42'),
(343, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 15:56:14'),
(344, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 16:03:42'),
(345, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 34, NULL, '{\"consultation_number\":\"CONS-2025-0006\",\"doctor\":\"Roberto Santos\",\"patient_id\":\"7\"}', NULL, NULL, NULL, '2025-10-23 16:06:06'),
(346, 14, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 16:06:22'),
(347, 15, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 16:06:30'),
(348, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 35, NULL, '{\"consultation_number\":\"CONS-2025-0007\",\"doctor\":\"Roberto Santos\",\"patient_id\":\"7\"}', NULL, NULL, NULL, '2025-10-23 16:07:51'),
(349, 15, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 16:09:04'),
(350, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 16:09:07'),
(351, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 36, NULL, '{\"consultation_number\":\"CONS-2025-0008\",\"doctor\":\"Roberto Santos\",\"patient_id\":\"6\"}', NULL, NULL, NULL, '2025-10-23 16:10:12'),
(352, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 16:19:29'),
(353, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 34, NULL, '{\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-23 16:22:29'),
(354, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 37, NULL, '{\"consultation_number\":\"CONS-2025-0009\",\"doctor\":\"Roberto Santos\",\"patient_id\":\"4\"}', NULL, NULL, NULL, '2025-10-23 16:31:59'),
(355, 12, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 16:33:03'),
(356, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 36, NULL, '{\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-23 16:37:57'),
(357, 12, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 16:39:25'),
(358, 12, 'INFO', 'CONSULTATION_REQUEST', 'Consultations', NULL, 38, NULL, '{\"consultation_number\":\"CONS-2025-0010\",\"type\":\"online\",\"priority\":\"medium\"}', NULL, NULL, NULL, '2025-10-23 16:39:43'),
(359, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 38, NULL, '{\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-23 16:40:08'),
(360, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 35, NULL, '{\"completed_by\":\"Roberto Santos\"}', NULL, NULL, NULL, '2025-10-23 16:40:14'),
(361, 12, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 16:53:36'),
(362, 12, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 16:53:58'),
(363, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 16:54:01'),
(364, 14, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 16:54:25'),
(365, 13, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-23 16:54:30'),
(366, 2, 'INFO', 'CONSULTATION_COMPLETED', 'Consultations', NULL, 39, NULL, '{\"consultation_number\":\"CONS-2025-0011\",\"doctor\":\"Roberto Santos\",\"patient_id\":\"5\"}', NULL, NULL, NULL, '2025-10-23 16:55:10'),
(367, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-24 13:10:47'),
(368, 13, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-24 14:26:25'),
(369, 13, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-24 14:37:15'),
(370, 13, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-24 15:08:56'),
(371, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-25 01:22:01'),
(372, 13, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-25 01:23:12'),
(373, 13, 'INFO', 'CERTIFICATE_REQUEST_SUBMITTED', 'Medical Certificates', NULL, 34, NULL, '{\"certificate_type\":\"Medical Certificate\",\"purpose\":\"Employment\",\"certificate_number\":\"CERT-2025-0003\"}', NULL, NULL, NULL, '2025-10-25 01:27:42'),
(374, 13, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-25 01:34:58'),
(375, 13, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-25 02:06:33'),
(376, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-25 02:06:37'),
(377, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-25 02:06:57'),
(378, 2, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-25 06:36:49'),
(379, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-25 06:36:52'),
(380, 14, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-25 06:41:21'),
(381, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-25 06:41:24'),
(382, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-25 06:42:09'),
(383, 2, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-25 07:38:20'),
(384, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-25 07:38:25'),
(385, 2, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-25 08:34:05'),
(386, 12, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-25 08:34:10'),
(387, 12, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-25 08:34:15'),
(388, 15, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-25 08:34:18'),
(389, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-26 09:59:23'),
(390, 15, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-26 10:00:51'),
(391, 15, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-26 10:36:35'),
(392, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-26 10:36:44'),
(393, 14, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-26 10:37:44'),
(394, 15, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-26 10:37:47'),
(395, 15, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-26 10:37:56'),
(396, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-26 10:37:58'),
(397, 12, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-26 11:20:52'),
(398, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-26 15:18:36'),
(399, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-26 15:38:56'),
(400, 2, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-26 16:49:30'),
(401, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-26 16:49:33'),
(402, 14, 'INFO', 'LOGOUT', NULL, NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-26 17:05:14'),
(403, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-26 17:05:17'),
(404, 14, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, '2025-10-27 00:46:19'),
(405, 2, 'INFO', 'LOGIN_SUCCESS', 'Authentication', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, '2025-10-27 00:46:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('super_admin','rhu_admin','bhs_admin','pharmacy_admin','patient') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `password_changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `is_active`, `login_attempts`, `locked_until`, `last_login`, `password_changed_at`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin@kawitrhu.gov.ph', 'super_admin', 1, 0, NULL, NULL, '2025-10-04 13:00:11', '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(2, 'rhu_admin', '$2y$10$dJLHljvneZQ/rnYv8A0jZODlAYtzOgVJXOA.l7fqazZFwGVtoD0P6', 'rhuadmin@kawitrhu.gov.ph', 'rhu_admin', 1, 0, NULL, '2025-10-27 08:46:32', '2025-10-06 11:10:27', '2025-10-04 13:00:11', '2025-10-27 00:46:32'),
(3, 'dr.reyes', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dr.reyes@kawitrhu.gov.ph', 'rhu_admin', 1, 0, NULL, NULL, '2025-10-04 13:00:11', '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(4, 'bhs_binakayan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bhs.binakayan@kawitrhu.gov.ph', 'bhs_admin', 1, 0, NULL, NULL, '2025-10-04 13:00:11', '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(5, 'bhs_kaingen', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bhs.kaingen@kawitrhu.gov.ph', 'bhs_admin', 1, 0, NULL, NULL, '2025-10-04 13:00:11', '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(6, 'bhs_gahak', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bhs.gahak@kawitrhu.gov.ph', 'bhs_admin', 1, 0, NULL, NULL, '2025-10-04 13:00:11', '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(7, 'pharmacy_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pharmacy@kawitrhu.gov.ph', 'pharmacy_admin', 1, 0, NULL, NULL, '2025-10-04 13:00:11', '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(8, 'pharmacist_rose', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'rose.tan@kawitrhu.gov.ph', 'pharmacy_admin', 1, 0, NULL, NULL, '2025-10-04 13:00:11', '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(9, 'juan.delacruz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'juan.delacruz@email.com', 'patient', 1, 0, NULL, '2025-10-23 16:49:51', '2025-10-04 13:00:11', '2025-10-04 13:00:11', '2025-10-23 08:49:51'),
(10, 'maria.santos', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'maria.santos@email.com', 'patient', 1, 0, NULL, '2025-10-23 16:50:52', '2025-10-04 13:00:11', '2025-10-04 13:00:11', '2025-10-23 08:50:52'),
(11, 'pedro.garcia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pedro.garcia@email.com', 'patient', 1, 0, NULL, NULL, '2025-10-04 13:00:11', '2025-10-04 13:00:11', '2025-10-04 13:00:11'),
(12, 'ana.reyes.patient', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ana.reyes.patient@email.com', 'patient', 1, 0, NULL, '2025-10-26 19:20:52', '2025-10-04 13:00:11', '2025-10-04 13:00:11', '2025-10-26 11:20:52'),
(13, 'carlos.mendoza', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'carlos.mendoza@email.com', 'patient', 1, 0, NULL, '2025-10-25 09:34:58', '2025-10-04 13:00:11', '2025-10-04 13:00:11', '2025-10-25 01:34:58'),
(14, 'jere.mayah', '$2y$10$Q/oLiBmZAHOPP4/wpBapj.DFfx7B.oIdUysl/qScz0Gy65pGl7D4m', 'jeremayah19@gmail.com', 'patient', 1, 0, NULL, '2025-10-27 08:46:19', '2025-10-17 05:45:16', '2025-10-13 03:11:05', '2025-10-27 00:46:19'),
(15, 'ana.marie', '$2y$10$G5Tpyv.fmS7MXTd5/N5o4eLP6srvOtbfD4XJpSfaz3NkJ1F1tdCMi', 'ana.marie@gmail.com', 'patient', 1, 0, NULL, '2025-10-26 18:37:47', '2025-10-17 03:11:56', '2025-10-17 03:11:56', '2025-10-26 10:37:47');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_token`, `expires_at`, `ip_address`, `user_agent`, `is_active`, `created_at`) VALUES
(1, 9, 'a15808dcbbe782071e0c1c91bb890fd5ada62b1052ec6188346b02b057f53c29', '2025-10-05 10:36:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-05 01:36:02'),
(2, 9, '168a10aba0fa090114b72106aef3b1b246dd6d3af998c020559a3e828611cce4', '2025-10-05 10:41:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-05 01:41:19'),
(3, 2, '9765bedda8a4a1518b30a18d114c82da32db17ba34b93d6167fc4fa568fe7ba1', '2025-10-05 10:43:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 01:43:05'),
(4, 2, '058651df29193281556112247d4c742e7f9bad0587e5af334be778c6c08ebd4b', '2025-10-05 11:02:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-05 02:02:57'),
(5, 9, '5482b76ce33b633173c32ad926649523f686f116c85a0fd08bf6f17c56a8ba52', '2025-10-05 11:03:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-05 02:03:30'),
(6, 2, 'c33d72f35e23762199c2e1089e75194a7ec61c6555ea18d1850e6e315574f338', '2025-10-05 11:22:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-05 02:22:21'),
(7, 9, '0c8ddac9a7c01b57ca997b319b0a8e367c1bea1c357bbfe755a0a76608b8694b', '2025-10-05 11:41:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 02:41:21'),
(8, 9, 'f9f986a60661f81065040cbe6bcc19e27d50d5eca1cbf7e29f6da9fc5a5f9e97', '2025-10-05 14:05:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 05:05:31'),
(9, 10, 'fbb28a8d598ae42b17966708e83986af9000044057ff4d7d46d2564f6cf7b75f', '2025-10-05 14:05:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 05:05:58'),
(10, 9, 'ae4f426085829a1c68913d7888ca77b94a57182ccd9813f4d460e6b0b1112ced', '2025-10-05 14:14:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 05:14:48'),
(11, 9, '2a5b95b8b504d8a73aaec17fbc5628da1210a4a6f1f057194872529de234c898', '2025-10-05 14:30:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 05:30:41'),
(12, 2, 'ee3e8b4f1f21999c7625705f08491369f8ec92c27ffcad27cfc8f97807da8fab', '2025-10-05 16:28:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-05 07:28:51'),
(13, 9, '8bacd25ab4bbf39e798c4f3477e719edafdba3b57eddad00a4c04ab028617157', '2025-10-05 16:49:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 07:49:19'),
(14, 9, '0e6fe2e06b835e4eb6113bce8b690a3a920cbc8330366a5744e92f98270aec5b', '2025-10-05 16:55:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 07:55:08'),
(15, 9, 'ea2fca917083e48fe257d3f6fc125a7ae85d81a9dff1184c2675cc0ed9b30ac5', '2025-10-05 17:00:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 08:00:14'),
(16, 10, '677c384cbab7c76ac6bc9cf2087d99988c03689e018e734e05ef7d1d39844528', '2025-10-05 17:02:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 08:02:35'),
(17, 9, 'a9902603854c00c90c1a431c7941bee89c8ca13a24c9f3511bb62778130a0c45', '2025-10-05 17:05:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 08:05:07'),
(18, 9, 'dfb3a198ce2d208a0cb5ad84ff65550e90266eab4752ea38992a0cd23c87874b', '2025-10-05 17:17:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 08:17:46'),
(19, 9, '58d7f16df73979d3a6912957a0d3b43d25d26e59c8c158fae8d69b8536c305c6', '2025-10-05 17:31:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 08:31:52'),
(20, 9, '46787da85b46868a12816f1c635881f8d5f41235b14dddf4f62802984f43d39a', '2025-10-05 17:51:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 08:51:55'),
(21, 9, '174861d0430cf5ec6f88ff4a2d8198a80105d091b4e2cecfbd2b5a0de48b030d', '2025-10-05 18:10:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 09:10:33'),
(22, 9, '0f94d1fec2ed68d02f08140919a7a33f0d3146f82a286677dbfeec912e6b4db8', '2025-10-05 19:20:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 10:20:03'),
(23, 9, '96e60d342d91318f3ba90a09ae224901bbc464b67e35325f591a31e6482b0fe7', '2025-10-05 19:28:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 10:28:36'),
(24, 10, '2e5117187802c7a6129422e5d2dc963d31619e27945a3f0bfeeb721e3b5306bd', '2025-10-05 22:16:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 13:16:23'),
(25, 13, '94b5c649d6104f04f198f58f7e95cc4c8d81d170556f0c06c0972c2280c04b7d', '2025-10-05 22:18:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 13:18:24'),
(26, 13, 'e7240021c462b3cd636c8e7e31cdad362c24c52c1e365d3331b70585548a34a5', '2025-10-05 22:43:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 13:43:11'),
(27, 13, '64d08eaa5793cfc0e11bcaf05eff54fba661b523d1aba542e59739b70e48d739', '2025-10-05 22:57:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 13:57:36'),
(28, 13, '68730b77bc7d1de24792279b6a6af77b9163e7ca104d40b0e8612e60050a6b85', '2025-10-05 23:05:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-05 14:05:39'),
(29, 9, '3c64d729167b8c7c3c5b4b5d78a67e9e046d52b235b9f159692a4bb4fbc5972b', '2025-10-05 23:07:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-05 14:07:25'),
(30, 9, '7f26615a1b2a9164b3efc692b964314e1d542630a627ed51f5ce1df37e44ecaa', '2025-10-05 23:07:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-05 14:07:51'),
(31, 9, '7770fdca306e6c30bd3f376882df3e34ffbac8d34db0295a77f245499c4eddc3', '2025-10-06 00:02:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-05 15:02:48'),
(32, 2, '84db4867a3e5b0b5cef65a29dfb3ca96f1951376f28157d33866c0c9eaae8fb0', '2025-10-06 12:44:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-06 03:44:13'),
(33, 9, '909906a24d1946f041ab868db6915cc009bfbaf45362cd14e5593f61d31d4225', '2025-10-06 12:44:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-06 03:44:27'),
(34, 2, '5c91f1ad87e35f78a18eb8f64c943fb78e6a2acba871557210731b0b0abfce18', '2025-10-06 14:03:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-06 05:03:13'),
(35, 13, '7c487b33f7bbbf78e9cb961aee7960c8dcd335dcf6479c434da9e42329bc0078', '2025-10-06 14:03:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-06 05:03:22'),
(36, 9, '71b6c1ef51662540dfc863fdfdd486445017df3c22deca9a44c13bb44076e1f6', '2025-10-06 14:03:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-06 05:03:53'),
(37, 9, '3ca2dd6b6a79dc27d403aa6cd19f82b7dbed390a0d3df0d7381b866ac7d1c9a8', '2025-10-06 14:18:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-06 05:18:23'),
(38, 9, 'e9a6fa3f47f8707962cfe284eb071b94312191348c7604647019ca3e11ed10cb', '2025-10-06 14:33:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-06 05:33:36'),
(39, 9, 'c2623e690ef84a97f58027d9ae13edc7e782243e9b23e79dbb37cefb210c92c0', '2025-10-06 14:45:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-06 05:45:59'),
(40, 9, '0efa3f64582ddc92b89d371f04fdcb7a348cf80a5987c0a64bc3140cb60220a7', '2025-10-06 14:52:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-06 05:52:05'),
(41, 13, '2cea898a0150e827bbf027c5d1ba3d70bbd4ac64412d59bc01eb145dfe9abc6f', '2025-10-06 15:55:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-06 06:55:40'),
(42, 2, '2cbdfedcb11ca8b9110c6c400ab799f986a93540d5ee3d6397416e395167106a', '2025-10-06 19:20:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-06 10:20:30'),
(43, 9, '04e9b9612a0eced96c139051fe02f3972bb1e18d251dc1a043381030a85fb616', '2025-10-06 19:21:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-06 10:21:21'),
(44, 2, '9c70f7410a368f22d490f9fe0068223a5a314d3c8d334d84bda0ae6d9c9907e8', '2025-10-08 16:11:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-08 07:11:26'),
(45, 9, '97ff4696230f55d2b1bd669a61a372d2fe7b99a94e9c2387db8615b7d299fa06', '2025-10-08 19:47:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-08 10:47:26'),
(46, 2, '6090ac1f13bbd96e26e2edfeabdf12121ace7f781f9bd38d0deace625f7fb0dc', '2025-10-09 21:29:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-09 12:29:51'),
(47, 2, 'e67bc6fb62e9e073e3f699ea9aa0ef0d87a60cfc5f170bf1af41491211d4db2d', '2025-10-10 19:36:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-10 10:36:35'),
(48, 9, '366459293f04bb8cee9ea22e9999b9d39cb70e169e936088967e00f043eb6621', '2025-10-10 19:54:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-10 10:54:28'),
(49, 9, 'fd7a6ef02667acbda53b8b4538fc8756b1a972696117dc589ce10c17eefb5603', '2025-10-10 20:57:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-10 11:57:26'),
(50, 9, '6328741ec92f1ef442417e67390bc4397e297e522b9c80291f60e83bfe0c275c', '2025-10-10 21:26:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-10 12:26:38'),
(51, 9, '04a6927b5f28c003288fa4ab6dca73fcde60bdfc6720b075a301bc22e3597418', '2025-10-10 21:35:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-10 12:35:33'),
(52, 9, 'e661479a2ba12e4cd392baac27d1469ba9d5da47cedd6b0801978c1730481259', '2025-10-10 21:53:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-10 12:53:46'),
(53, 2, 'b516458be1458338911f9cab7a674a360fa1a2ae869f3a1036649df1682af036', '2025-10-12 14:14:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-12 05:14:58'),
(54, 9, '5e9ff1a1df51fd78a098995830a8dc3711ae1f988b6474dfb1b0938d9cd273d1', '2025-10-12 14:25:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-12 05:25:53'),
(55, 9, '854aa876f2f680bd8becdef28f8884e5f40dd3b55247796f620edf8126173fa1', '2025-10-12 14:39:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-12 05:39:01'),
(56, 9, '8ed3c2f2b1de8351fd0ea4a681cbfd0a6952124c74f2faea7548616b189d27e7', '2025-10-12 14:56:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-12 05:56:06'),
(57, 9, '66955fd849d0f27ee98d4b4683718fbbaf3f4b5eee57582a1d17e928b706e254', '2025-10-12 15:12:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-12 06:12:32'),
(58, 9, 'f78936dc02492c8d4f3c4219f5efbdb10f89d13228fdd68709f681a4d476cd52', '2025-10-12 15:39:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-12 06:39:27'),
(59, 9, '62527c6abe6091bca26a314529553b6cd529abfe8c70543fa7f97a8ea2d4fc6e', '2025-10-12 16:12:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-12 07:12:31'),
(60, 9, 'd7be2f44ef5d1f38fb88dd62d008492bf85d153ddffb77f33c84c13e7998ed46', '2025-10-12 18:35:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-12 09:35:11'),
(61, 9, '7d7fe57b73c4b506592d5d6512fdfeaf6127ba392db0bcc3a9cbc870214fbf9a', '2025-10-12 19:35:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-12 10:35:58'),
(62, 2, '84eb4fed81748de2d29965c6df9ae0a648a43ef10d9fd632033b82928b8fbef5', '2025-10-12 19:38:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-12 10:38:26'),
(63, 9, 'be6748a3d5b3c5744e718137fb965c71bfd0c6d8d09b83db748961689107316b', '2025-10-12 20:04:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-12 11:04:34'),
(64, 2, '52e1f0bdb530ca72d271225a524d39fcdcb1fd9a425e2db1c3a1a0a9459984de', '2025-10-13 11:35:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-13 02:35:17'),
(65, 9, '5598692cbf1fcbf04cec0e33ed4f11bb5fa369fd9059019536bd71360836faea', '2025-10-13 13:13:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-13 04:13:53'),
(66, 14, 'c941ed8fd300a63682db525b74f9c5122381b2f9637ed21d74a422f652273f97', '2025-10-13 13:14:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-13 04:14:02'),
(67, 14, 'ce5badb5f8ec7d636df0884e08374018bf2425391266732b3630aa71aab173fe', '2025-10-13 13:20:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-13 04:20:48'),
(68, 2, 'f8945014374b0683639d6ac60b09600c49590de0ce9d38b5b3decad288170414', '2025-10-13 21:22:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-13 12:22:13'),
(69, 14, '60c54892c8bc8c73cba304786a9228f8b8a788f12a7913666dd1c907b1b7d1ac', '2025-10-13 21:22:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-13 12:22:23'),
(70, 14, '4eddb54451a1cbdddde4c1737f6cda53e51d35c5b75146adcde9e5633d1efe5a', '2025-10-14 00:09:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-13 15:09:19'),
(71, 2, 'ce293c0b7e8237e7eb632cd8be57e7047a1c3a35a36d567a3b4805a8d48259af', '2025-10-14 00:10:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-13 15:10:50'),
(72, 2, 'b625d46471305727a40fdbfde96bcde5501723daf749b1f684a9bc17e9e8aaf2', '2025-10-14 00:11:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-13 15:11:33'),
(73, 2, '7823ba8ea94b8faf49d1b4719eb5396f720456f1aeef0c6398b52d4cc02d2ad2', '2025-10-14 00:11:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-13 15:11:41'),
(74, 14, '91d5b4ffbb1c90b6202a7a1533278b688536f02a5b7678810dc779cb5560a076', '2025-10-14 00:15:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-13 15:15:06'),
(75, 9, 'ba74d09d7417a10a7c8bd4ae0d47b6cd6866c3f4be297387f932e72ad323db6d', '2025-10-14 00:15:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-13 15:15:20'),
(76, 9, 'c14b1e38ff9186da607f21276e747ca97e5cfcac6d49b17fd73e92cd08403458', '2025-10-14 00:25:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-13 15:25:44'),
(77, 9, '468fa508e3ad1947782956497718f94210d723ee7a2722e9f4ac3f0cd0363c8d', '2025-10-14 00:34:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-13 15:34:23'),
(78, 9, 'c8375933d9c9f5bb8d0dc4febcf0b83ce4450c83471ea4db4503d80473d29c44', '2025-10-14 00:49:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-13 15:49:08'),
(79, 9, '4cddf0af66bd655213d12af44defbd00b67947abe73fb07b44f9936e49aee026', '2025-10-14 00:56:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-13 15:56:34'),
(80, 14, 'd2ca17371ff2b8c3300cb56dd84ac544cbddc6922b8e7415c7d783f446e4017c', '2025-10-14 01:07:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-13 16:07:43'),
(81, 2, '65a02ceb547580bddd13c0cfe2d2134f0d61fd5655fef2da38d83d46cc67a0c6', '2025-10-14 10:58:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-14 01:58:49'),
(82, 14, '8584859afdcba92b915d57b272d75f215eb29d308228284a3f5f80b78a68629c', '2025-10-14 11:12:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-14 02:12:38'),
(83, 14, '958ff7848c281e1b66d46dad4b8b825bf4b68e4028ff8fa78b80c53a82b74201', '2025-10-14 12:12:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-14 03:12:20'),
(84, 14, 'e7b5eb2247782cc53e921f41ee66e625e1555b80f3dcfd10600cf71fd5e2e91a', '2025-10-14 12:22:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-14 03:22:40'),
(85, 13, 'a617cd9eb8d93a231588f22f7ea4e150ec11cf983c6700f9891c2df910278ec9', '2025-10-14 12:22:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-14 03:22:49'),
(86, 13, 'c153319101dbe2c4dda767915a56685b89f384ebad5640afb8918734937f34e1', '2025-10-14 12:35:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-14 03:35:37'),
(87, 14, 'cd5d584d7407ac6d976689019c07503b958aa75ede9356038cde632c16f8b048', '2025-10-14 12:43:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-14 03:43:41'),
(88, 2, 'e4f11a99232bd53186373dd50870aa7b7ddf9057626237d983ce15c29ee8d6f5', '2025-10-14 17:31:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-14 08:31:46'),
(89, 14, 'a0f8e3b53617cacf5b365a546db3776cc61fee2e28fc78e4c8441e2eb9a9012b', '2025-10-14 17:32:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-14 08:32:57'),
(90, 14, 'faf35ee84d2ea2e1dda3dc5f4dca9de64b0f18478bd58f4e476d5332774ec7a2', '2025-10-14 17:43:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-14 08:43:14'),
(91, 2, 'e7ab68e9fb100623467331ef51e4502e609c9b8dc8649cbdf7f6c504bda643a4', '2025-10-16 14:35:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-16 05:35:39'),
(92, 14, '0ce7cf296362298cb3d82157a36aff7d232d1d8116f68c08cc6532f531c65955', '2025-10-16 14:37:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 05:37:18'),
(93, 14, 'a3bead183a108171b5937d5c17d5a4758e2444dbbcfa5e01e92058ca1b184a01', '2025-10-16 15:06:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 06:06:08'),
(94, 14, 'b746d48cb0a1f10a5aff73730f81d77cf98f06e68448c1acaffc08f3d4162e1d', '2025-10-16 15:48:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 06:48:38'),
(95, 14, 'a8ed0c6a5c5a352f391ba90918bedfc891c1c9c65520e3ec46eeba7d112983ca', '2025-10-16 15:54:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 06:54:45'),
(96, 14, 'e96208c694e73d3930bf455fc0ad6e9b9f4be0d4e203567d5522c7bc5bbe29c5', '2025-10-16 16:04:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 07:04:39'),
(97, 14, '0492eeb5203f72434bb8553fd45ed47e1994604fd27e0862aee165afd843a669', '2025-10-16 19:47:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 10:47:58'),
(98, 14, 'c13997340d1291d93a714c7ef0cc046887cdcf8632442620f21a30e99ae506bb', '2025-10-16 20:28:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 11:28:24'),
(99, 9, '5fd56bd43e64c9bafb72da41276381a29954566eb8cdbe1f06b5b4b5595af891', '2025-10-16 20:31:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 11:31:43'),
(100, 9, '34165a1f8f7f374796050f390c7baa40e3a6faf823d51701be84fe31f033c9c0', '2025-10-16 20:52:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 11:52:53'),
(101, 9, 'd70c7551364f71ad508b2076273fd3ad0434e51dabf0c4fa20992788870cdc37', '2025-10-16 21:00:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 12:00:30'),
(102, 9, 'da6ebe5a687f23fd5cc929448b1534a2fab905fb2e7d2a4c2f81721460b1f225', '2025-10-16 21:07:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 12:07:30'),
(103, 9, 'c9ca32772e185ca00b535ecc446422e0e94953441f19c5d6ed3188eca9e33a81', '2025-10-16 21:13:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 12:13:49'),
(104, 14, '05b969675777751bb7295004c5676f46dd7aac9939568b19da7d530a2e2e7de1', '2025-10-16 21:14:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 12:14:04'),
(105, 14, '9ff91697e3819522ca61b4c9ac336f00879ef94a0c5fedf7ddf52266c0bb12db', '2025-10-16 21:28:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 12:28:11'),
(106, 14, '5b3cba68091ebdffd7230e55d733246071a1b9b256d6793f6f3bd29dbd7c7e5b', '2025-10-16 21:43:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 12:43:55'),
(107, 2, '7d6b58b5dfd4000b0fe95ebd0bbe08496a1ce0d22c409d50f770e6ddce3a6f00', '2025-10-17 00:40:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-16 15:40:27'),
(108, 14, '23eefe138f63a04eea7c0360040f97a5274e46f5f13c3953995573176e40ff96', '2025-10-17 01:03:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 16:03:20'),
(109, 14, 'e2420fa0ed5fd29f43c847a42b64e78a45394120193ed56d202c3cc693dec4b3', '2025-10-17 01:26:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 16:26:11'),
(110, 9, '2eb250b2c59b7df17a78930c44499fba351c35732974adb7ccd76cf979881c0b', '2025-10-17 01:26:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-16 16:26:56'),
(111, 9, '4d07497e05b9f8ae4e74121f79afb0adda4777b6223a740f2fc8dc2646da5395', '2025-10-17 12:08:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-17 03:08:59'),
(112, 2, 'c6e8c33120671d7d16d6eac525f8bf8a7489bb2d89fac67d1af36d5662f6d0ee', '2025-10-17 12:10:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-17 03:10:19'),
(113, 2, 'b611d57b7f00fdafcc664337946cd96c0f368326cb278464beb02cef6d01b04c', '2025-10-17 14:24:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-17 05:24:03'),
(114, 14, '1d9cd806bd5e25947c3e34b292e9397a4f4d2391b529dc98fb9b5096eac5f8e0', '2025-10-17 14:44:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-17 05:44:53'),
(115, 10, '2e86e777ee85ca212c848c1492f9dc3ff3bafa347d593b86fddec6e6b13ce8c0', '2025-10-17 14:55:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-17 05:55:57'),
(116, 14, '5c1cc5a27c3e1c2801ab99f76bc6de031b9e5be936293caf0f503ffbfe40a5a1', '2025-10-17 16:48:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-17 07:48:04'),
(117, 14, '8ba2a8583c881ca53ca2a88828a8e8ac53a32ddc5e5a3b2608b695c48dc3a701', '2025-10-17 17:01:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-17 08:01:43'),
(118, 2, '3ddad7599bae4686a9b26266f39c3ca8f8710650e23df936fad6d4ceb51a4cf1', '2025-10-17 18:07:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-17 09:07:20'),
(119, 14, 'e9b5cbd26cddee35fb525e39346320eb585da9f18eed06b51079ed6a175d4b1a', '2025-10-17 19:49:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-17 10:49:48'),
(120, 2, 'f9c9f4e7275cc9e8fcb5f81ecd281f84868d8d4d1abed025e3b02369d967d9cf', '2025-10-17 20:00:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-17 11:00:14'),
(121, 14, '5930b32a139ad540bc4e8bed61308cbab85fc9f0b649ab7bcee5a7a6f524adaf', '2025-10-17 21:17:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-17 12:17:03'),
(122, 14, '56f256d8ae1469583e0fd4b02e1bdffb8cd4fea313e66ff35e037abce489dec2', '2025-10-17 21:35:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-17 12:35:00'),
(123, 14, '35c5ba0fd3540078e8feb462f2ec06202e0bbf14ec00ce07bded91603d2b461b', '2025-10-17 21:52:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-17 12:52:06'),
(124, 9, '79d33155f6dfb8c6e3e91774327995f532c98d3a0c96c7bd12947a86b43207ef', '2025-10-17 22:08:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-17 13:08:54'),
(125, 9, 'a2f9a4c6c23bb60833501d71b0ee208c3487b9ec8d05d3ca85d33074dfc81b4c', '2025-10-17 22:15:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-17 13:15:14'),
(126, 9, 'ace3edc6518b0f038ea6cba750b4e2126ce21eb4616f0b950040f4b824536419', '2025-10-17 22:21:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-17 13:21:30'),
(127, 9, '15d28ab004ad3c6f419700db38d009f00b64c6b452bb00315e969feeec4b8de8', '2025-10-17 22:31:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-17 13:31:34'),
(128, 2, '93eeb9a296fc12dc682750cdc4833e8fa8c197c5a249af188333a7c7b399763a', '2025-10-18 12:25:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-18 03:25:28'),
(129, 9, '5b702726e0438a66dd328449ce8cb9963a450b4dd8e0a460061d62e52f6a7805', '2025-10-18 12:26:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-18 03:26:20'),
(130, 14, '59aa8461fb5abfbbc832971d22057a73da6a816b1bad3498e338844e0e6c32e9', '2025-10-18 12:27:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-18 03:27:21'),
(131, 14, 'a284296f5d984b0624208bd49ecd5598ee6c0460f48bf1d1492269b3a72f6a58', '2025-10-18 13:02:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-18 04:02:12'),
(132, 14, 'fe5b142b6b63f9e601a325e006fe3f0473c7638a5e96555ac6b066fe1e22b588', '2025-10-18 13:02:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-18 04:02:18'),
(133, 14, 'f9b73dee52617c6188e8aa1cb1b238b7f5447b34f887947f95892e06d4a08711', '2025-10-18 13:22:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-18 04:22:03'),
(134, 14, 'e5d9c69982a0863518d33613629da9c146c94bcc1ffa8f1ab72b81fb18f20fb0', '2025-10-18 13:32:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-18 04:32:16'),
(135, 14, '1bd2d2432b2f7abd7b020b70a85d6e448dd8fe1fbdbadede4b10eb8b74a2cad8', '2025-10-18 13:39:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-18 04:39:00'),
(136, 14, '829ae7e77f40ec96f44d9be748bd96f9b2968c40fd5c9224c52ad05ce49270d5', '2025-10-18 13:45:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-18 04:45:37'),
(137, 14, '85a79a123d64c37a98215e0f22a4ac84455269f7ad94fb3ee813b0513d8b0e95', '2025-10-18 13:53:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-18 04:53:31'),
(138, 2, 'a478a429098ce6d60e12a1c86c71a219c4a313c1ba45fc5bf613a1a61e82f082', '2025-10-18 19:10:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-18 10:10:20'),
(139, 14, '0e1447716b31fc0be84aca43bb7a4cd8c5ffd49b89a6fdb39fb21b67c358a839', '2025-10-18 19:23:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-18 10:23:40'),
(140, 14, '6615bfcc814473612c4c4e9b2554a7afb8aa3d7e50b666cfc462d09ceba4d69c', '2025-10-18 19:28:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-18 10:28:52'),
(141, 14, '4d0171779bbabaa39aad5ea3286a11c46e9817a264b9686b2706b7693defe7a8', '2025-10-18 19:44:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-18 10:44:07'),
(142, 14, '90c1a00bfccc610fa00e05efc49e0a53c93da284f783b6a6079aa623369a7c60', '2025-10-18 19:53:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-18 10:53:34'),
(143, 14, 'ead960d21f6e3419d857ad886d5acb6c520efee2ab2a2e75d8acabf756b1e83f', '2025-10-18 20:14:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-18 11:14:24'),
(144, 14, '4714d388c5d6cf242f55e73d9e2212b5e9765022c949d728208ff63275fdbcb8', '2025-10-18 20:44:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-18 11:44:58'),
(145, 14, 'ddbcd01a2ac57987440d56ef22a498c2570c87ec0411bf9e2a996a7f795722ed', '2025-10-18 21:58:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-18 12:58:39'),
(146, 9, '31d48f7190c9d895070c9f5677fdd0d9d3d9b38088fb5f43b310723270465e7c', '2025-10-20 14:53:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-20 05:53:27'),
(147, 14, '135635249020817524a775e1be9d0c2b20616398de8c981bfa894d7a2e4373b2', '2025-10-20 14:53:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-20 05:53:47'),
(148, 2, '1fff34b9f885bca7e47cb3d64e9f5d4ad6c1d9903b706e56b4a4a55f62450b2f', '2025-10-20 14:54:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-20 05:54:02'),
(149, 14, '7f6d81975f2bcba5f68d2da4e7147ef2f9ccc7afe145355361d0bd7cd330fdbe', '2025-10-20 14:54:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-20 05:54:49'),
(150, 10, 'cee999688852794db6d409720fc53e7f6c02fc6b3199c0163a5b715d307d4df0', '2025-10-20 14:57:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-20 05:57:26'),
(151, 14, '49c209bd6ab8992195a85e6c16676906f83b6d9674b4b1118f150420f3cf51e2', '2025-10-20 14:57:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-20 05:57:52'),
(152, 2, 'e9546f03cf8287daa48d5dd64f7251d88d8e6c0df7831294bc6b779eefb7f1fa', '2025-10-20 14:59:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-20 05:59:31'),
(153, 14, 'ccc1e4859b861c79b256cea94dec464612a616796a6b95e147155afdb25e4cc3', '2025-10-20 15:20:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-20 06:20:57'),
(154, 2, '97c7298b61f7621b4652dd8118f5c7cdd9c7704c8f30260d95682201cf72a253', '2025-10-20 15:23:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-20 06:23:15'),
(155, 14, '83ed9c0fa117e1fd4d1d4b85bf62562bd9c2a2b0b79154a2e0569da029118bda', '2025-10-21 10:44:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-21 01:44:27'),
(156, 9, '4bf729934cd7df4b26ba55823174fdc0630fd93e8781ceee7f8cb059d346ff10', '2025-10-21 12:01:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-21 03:01:14'),
(157, 9, 'c518f2c458d645da70054560a5b1dd2a7cf0f625e1680305c59ad5fe9a3011b8', '2025-10-21 12:13:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-21 03:13:17'),
(158, 14, 'f6547a5e2291215a1e4ce31f7ddf153b94bbcbd96910e02d0bf6e194a29bf609', '2025-10-21 19:13:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-21 10:13:23'),
(159, 2, 'ab3f7c8ec80d67534375038b589495ae294b522b50b5201bcd4eca56c14e4caf', '2025-10-21 19:40:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-21 10:40:21'),
(160, 14, '1d945280990c4930f9e8b40af75e880d98c60eaaf8387154e10b1e64210cb05e', '2025-10-23 17:09:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-23 08:09:08'),
(161, 14, '3a303652504705981d00e8314db729e3ef7b477e914270fbdc616d594b3ff5c2', '2025-10-23 17:36:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-23 08:36:31'),
(162, 9, '18498a3f9780ca80d4b744876439b7b317a29c3109bc16f02aff88f1fe2c9e5a', '2025-10-23 17:49:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-23 08:49:51'),
(163, 10, '7e33d559291c10cfd847207604e979d621e45b86d00fe30f4d1f2d7699643197', '2025-10-23 17:50:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-23 08:50:52'),
(164, 2, 'b27782576cea2491ef1e8331671faa28b131d3e0032654541b32bb35f32d5a19', '2025-10-23 18:06:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-23 09:06:03'),
(165, 2, '2d5fd72cc954d155348e520742378ec1d8da204119d2eb9a6385f9105e96e4ae', '2025-10-23 18:57:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-23 09:57:00'),
(166, 2, 'fbc422c749aa368262ad59f94f5745314954c8d3eb7964905b65e1194c919b25', '2025-10-23 18:57:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-23 09:57:19'),
(167, 2, 'd8995d039d84fed8b2f9155877892c70d13d5fafa235599c3c2bbb8846b40e35', '2025-10-24 00:50:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-23 15:50:42'),
(168, 14, 'e80382533888d8223b04f3fa7d24e1b18d24c325435c7349ba0c26ee95089f34', '2025-10-24 00:56:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-23 15:56:14'),
(169, 14, '7f7d2a4c85d2e64627a27a143a439f026bcb2c02048ffcf436ba1d8c0530266b', '2025-10-24 01:03:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-23 16:03:42'),
(170, 15, '7303ee9636a92abe1efd2cfc471ec970602816e32ca5b7d55341030c3f7a7b61', '2025-10-24 01:06:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-23 16:06:30'),
(171, 14, '037cc911e1ec796d62cbcf636d3acaad6d0057ccb54307636d41fd5b0cf09354', '2025-10-24 01:09:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-23 16:09:07'),
(172, 14, '5e0af2cf2f5a696464b729a69f56316c4ef6b52ec20bdb6c39ed9c8fcd25ce06', '2025-10-24 01:19:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-23 16:19:29'),
(173, 12, '7ef53817605f530c8ed4fc26f97be94ca74e2fed045ca8de095e39786b459257', '2025-10-24 01:33:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-23 16:33:03'),
(174, 12, '608ad2997b6e8b9d79a53f305f076852433c054fe2f881eea43e2efbdd26474d', '2025-10-24 01:39:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-23 16:39:25'),
(175, 12, 'a71ce00f54d6063100090b01efcfe02fd5333fc24d9af58b9362887a77c22030', '2025-10-24 01:53:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-23 16:53:36'),
(176, 14, '1af67664eba347e8c2ecadae6eaa4a36ab0d1e7530eae3cf50e4e90a8b19dba5', '2025-10-24 01:54:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-23 16:54:01'),
(177, 13, '30c9328a625b70d98490bb6e0d1252b91c9310fee4ae1b4ff193a917771f6f13', '2025-10-24 01:54:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-23 16:54:30'),
(178, 2, 'f91ac61fe7031b403e91d6be7ffef31e6a46818a95800fbd02c77537c54ff0cc', '2025-10-24 22:10:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-24 13:10:47'),
(179, 13, 'e4e4e4918b9377410640271b9760d5ed304da9db21f1d135cc30add6f3c8a92f', '2025-10-24 23:26:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-24 14:26:25'),
(180, 13, '22b2c680d34713948b62a2ffccae00f719def42a15aacc8a05968d650a2b4689', '2025-10-24 23:37:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-24 14:37:15'),
(181, 13, '7b634fbd49fb606828e6af5289b27757a01bd1831838ab721a08eef564c0170f', '2025-10-25 00:08:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-24 15:08:55'),
(182, 14, '6dc2c6f2bcd5472b5eb56fb0a6521eb49dbb1d5be448f05ee8ddc8ca065efe99', '2025-10-25 10:22:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-25 01:22:01'),
(183, 13, 'a5ff11254732ef4673b8a09e2b4eb0063de8e9968d893355bd03e49c85f71507', '2025-10-25 10:23:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-25 01:23:12'),
(184, 13, '60a953a6f836197f80380c00935e72ca9692df4cf5295a54fb8888fdd994e190', '2025-10-25 10:34:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-25 01:34:58'),
(185, 2, 'e43276b2c65b635b0ed6554e91a178db37219869f77ee992dcba9efc174700a2', '2025-10-25 11:06:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-25 02:06:37'),
(186, 2, '867e365c13327f587938571a1238647f6f57e074dc1e5cda6530f4de4047906e', '2025-10-25 11:06:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-25 02:06:57'),
(187, 14, 'b9d4602f912f8ea3d8bd29c8c8c4b2efc62fd24a4a62d9822dd368ca567b95c1', '2025-10-25 15:36:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-25 06:36:52'),
(188, 2, '953e70077850845838038a46f7a88850b49dbae23e6491efecef56263df31fa4', '2025-10-25 15:41:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-25 06:41:24'),
(189, 2, '20d3c6fc5c5268b209a510a06cec8e73c88534d194f2a1c6a3c2d74e5138e5c8', '2025-10-25 15:42:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-25 06:42:09'),
(190, 2, '577226ddf0e953884158bc4e35a12cca3081dfa2dfb3bdf8801ea3a0165f751c', '2025-10-25 16:38:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-25 07:38:25'),
(191, 12, '6019376f6a43e3b893ac70482b58a17ae10ded6427d5b6fa9ab61ee66c3946b5', '2025-10-25 17:34:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-25 08:34:10'),
(192, 15, '2bf6417eb0a148f27649ff626f96a669dbbf7f29a3c5f981b80f2813f054755b', '2025-10-25 17:34:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-25 08:34:18'),
(193, 2, 'c0f59f96e0c4bb4289f1ae00ff1668cc7c29718b1d8f4793b133375d4db91930', '2025-10-26 18:59:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-26 09:59:23'),
(194, 15, '7f88855de8e392a8efaea72b6abd6a93317a4099af7055a4df09980fb44e84bf', '2025-10-26 19:00:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-26 10:00:51');
INSERT INTO `user_sessions` (`id`, `user_id`, `session_token`, `expires_at`, `ip_address`, `user_agent`, `is_active`, `created_at`) VALUES
(195, 14, '7b2e0dbf42b7507c349bd6f7919648dcdd971caf4389a6f922c973637ab032bd', '2025-10-26 19:36:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-26 10:36:44'),
(196, 15, 'dc4dfbec2550757ca3a394748194fdcaecb31f08f6effa3b2ae4f69eabad02b1', '2025-10-26 19:37:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-26 10:37:47'),
(197, 14, 'abab71b328eff7257658525b3a7d81389d5eb0b6f955fa51d17d5f2ee0c2bd94', '2025-10-26 19:37:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-26 10:37:58'),
(198, 12, '47802fe0762d10e3310bef814739e3ec4034b62dcb57eec58b34520240601328', '2025-10-26 20:20:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-26 11:20:52'),
(199, 2, '18710d2e2adec1edf326abf9453fafd911e53ee160f1aac06429fa6d3fdcd3f4', '2025-10-27 00:18:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-26 15:18:36'),
(200, 2, '77b4e41002a09a9315dcb3288279788c071b15989b0e73380c56b939a8205848', '2025-10-27 00:38:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-26 15:38:56'),
(201, 14, '710871ae39d7c997b62270ecc1e8ae8bb0aa283174ead76f1e27edd2216b715a', '2025-10-27 01:49:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-26 16:49:33'),
(202, 2, '50df50245bce9dc0ef133ffded1cb1b0961f7c069b80815c0bef5915d5679607', '2025-10-27 02:05:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-26 17:05:16'),
(203, 14, '7447888e27a21a5da5ec1c7345e893cf2a5c44c72e96a8f34ab717da4f60f18e', '2025-10-27 09:46:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 1, '2025-10-27 00:46:19'),
(204, 2, '65ac0c88f81d38170ad0b0f302204e9915e1f21c5e7495cb7fa4c0d72d4866a2', '2025-10-27 09:46:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 1, '2025-10-27 00:46:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `idx_status_publish` (`status`,`publish_date`),
  ADD KEY `idx_target_audience` (`target_audience`);

--
-- Indexes for table `appointments_archive`
--
ALTER TABLE `appointments_archive`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_type_id` (`service_type_id`),
  ADD KEY `assigned_staff` (`assigned_staff`),
  ADD KEY `barangay_id` (`barangay_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `confirmed_by` (`confirmed_by`),
  ADD KEY `rescheduled_from` (`rescheduled_from`),
  ADD KEY `idx_appointment_date` (`appointment_date`),
  ADD KEY `idx_patient_appointments` (`patient_id`,`appointment_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`);

--
-- Indexes for table `barangays`
--
ALTER TABLE `barangays`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_barangay_name` (`barangay_name`);

--
-- Indexes for table `consultations`
--
ALTER TABLE `consultations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `consultation_number` (`consultation_number`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `assigned_doctor` (`assigned_doctor`),
  ADD KEY `barangay_id` (`barangay_id`),
  ADD KEY `idx_consultation_date` (`consultation_date`),
  ADD KEY `idx_patient_consultations` (`patient_id`,`consultation_date`),
  ADD KEY `idx_consultation_number` (`consultation_number`),
  ADD KEY `idx_consultation_type_status` (`consultation_type`,`status`),
  ADD KEY `idx_consultations_online` (`consultation_type`,`status`),
  ADD KEY `fk_approved_by` (`approved_by`),
  ADD KEY `idx_consultation_status_type` (`status`,`consultation_type`),
  ADD KEY `idx_online_pending` (`consultation_type`,`status`);

--
-- Indexes for table `laboratory_results`
--
ALTER TABLE `laboratory_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lab_number` (`lab_number`),
  ADD KEY `consultation_id` (`consultation_id`),
  ADD KEY `performed_by` (`performed_by`),
  ADD KEY `verified_by` (`verified_by`),
  ADD KEY `released_by` (`released_by`),
  ADD KEY `idx_patient_labs` (`patient_id`,`test_date`),
  ADD KEY `idx_lab_number` (`lab_number`);

--
-- Indexes for table `medical_certificates`
--
ALTER TABLE `medical_certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificate_number` (`certificate_number`),
  ADD KEY `consultation_id` (`consultation_id`),
  ADD KEY `idx_certificate_number` (`certificate_number`),
  ADD KEY `idx_patient_certificates` (`patient_id`,`date_issued`),
  ADD KEY `assigned_doctor_id` (`assigned_doctor_id`),
  ADD KEY `medical_certificates_ibfk_3` (`issued_by`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `medicine_code` (`medicine_code`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_medicine_name` (`medicine_name`),
  ADD KEY `idx_generic_name` (`generic_name`),
  ADD KEY `idx_expiry` (`expiry_date`),
  ADD KEY `idx_stock_level` (`stock_quantity`,`reorder_level`);

--
-- Indexes for table `medicine_categories`
--
ALTER TABLE `medicine_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `medicine_transactions`
--
ALTER TABLE `medicine_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_number` (`transaction_number`),
  ADD KEY `performed_by` (`performed_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_transaction_date` (`transaction_date`),
  ADD KEY `idx_medicine_transactions` (`medicine_id`,`transaction_date`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_unread` (`user_id`,`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `patient_id` (`patient_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_patient_id` (`patient_id`),
  ADD KEY `idx_name` (`last_name`,`first_name`),
  ADD KEY `idx_barangay` (`barangay_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prescription_number` (`prescription_number`),
  ADD KEY `consultation_id` (`consultation_id`),
  ADD KEY `prescribed_by` (`prescribed_by`),
  ADD KEY `dispensed_by` (`dispensed_by`),
  ADD KEY `idx_patient_prescriptions` (`patient_id`,`prescription_date`),
  ADD KEY `idx_prescription_number` (`prescription_number`);

--
-- Indexes for table `queue`
--
ALTER TABLE `queue`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `queue_number` (`queue_number`),
  ADD KEY `served_by` (`served_by`),
  ADD KEY `barangay_id` (`barangay_id`),
  ADD KEY `idx_queue_date` (`queue_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_service_type` (`service_type`),
  ADD KEY `idx_check_in_time` (`check_in_time`),
  ADD KEY `idx_patient` (`patient_id`);

--
-- Indexes for table `queue_analytics`
--
ALTER TABLE `queue_analytics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_analytics` (`analytics_date`,`service_type`,`location_type`,`barangay_id`),
  ADD KEY `barangay_id` (`barangay_id`);

--
-- Indexes for table `queue_display`
--
ALTER TABLE `queue_display`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_counter_service` (`counter_number`,`service_type`,`location_type`),
  ADD KEY `current_queue_id` (`current_queue_id`);

--
-- Indexes for table `queue_notifications`
--
ALTER TABLE `queue_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_queue_id` (`queue_id`);

--
-- Indexes for table `queue_settings`
--
ALTER TABLE `queue_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_type` (`service_type`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referral_number` (`referral_number`),
  ADD KEY `consultation_id` (`consultation_id`),
  ADD KEY `referred_by` (`referred_by`),
  ADD KEY `idx_patient_referrals` (`patient_id`,`referral_date`),
  ADD KEY `idx_referral_number` (`referral_number`),
  ADD KEY `idx_covid_vaccination` (`covid_vaccination_status`),
  ADD KEY `idx_referral_urgency_status` (`urgency_level`,`status`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report_number` (`report_number`),
  ADD KEY `barangay_id` (`barangay_id`),
  ADD KEY `generated_by` (`generated_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_report_date` (`report_date`),
  ADD KEY `idx_report_type` (`report_type`);

--
-- Indexes for table `service_types`
--
ALTER TABLE `service_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `assigned_barangay_id` (`assigned_barangay_id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_department` (`department`);

--
-- Indexes for table `system_config`
--
ALTER TABLE `system_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_key` (`config_key`),
  ADD KEY `idx_config_key` (`config_key`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_logs` (`user_id`,`created_at`),
  ADD KEY `idx_log_level` (`log_level`),
  ADD KEY `idx_action` (`action`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_users_login` (`username`,`password`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `idx_session_token` (`session_token`),
  ADD KEY `idx_user_active` (`user_id`,`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `appointments_archive`
--
ALTER TABLE `appointments_archive`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `barangays`
--
ALTER TABLE `barangays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `laboratory_results`
--
ALTER TABLE `laboratory_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `medical_certificates`
--
ALTER TABLE `medical_certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `medicine_categories`
--
ALTER TABLE `medicine_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `medicine_transactions`
--
ALTER TABLE `medicine_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `queue`
--
ALTER TABLE `queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `queue_analytics`
--
ALTER TABLE `queue_analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `queue_display`
--
ALTER TABLE `queue_display`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `queue_notifications`
--
ALTER TABLE `queue_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `queue_settings`
--
ALTER TABLE `queue_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_types`
--
ALTER TABLE `service_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `system_config`
--
ALTER TABLE `system_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=406;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=205;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `staff` (`id`);

--
-- Constraints for table `appointments_archive`
--
ALTER TABLE `appointments_archive`
  ADD CONSTRAINT `appointments_archive_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_archive_ibfk_2` FOREIGN KEY (`service_type_id`) REFERENCES `service_types` (`id`),
  ADD CONSTRAINT `appointments_archive_ibfk_3` FOREIGN KEY (`assigned_staff`) REFERENCES `staff` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `appointments_archive_ibfk_4` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`),
  ADD CONSTRAINT `appointments_archive_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `appointments_archive_ibfk_6` FOREIGN KEY (`confirmed_by`) REFERENCES `staff` (`id`),
  ADD CONSTRAINT `appointments_archive_ibfk_7` FOREIGN KEY (`rescheduled_from`) REFERENCES `appointments_archive` (`id`);

--
-- Constraints for table `consultations`
--
ALTER TABLE `consultations`
  ADD CONSTRAINT `consultations_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consultations_ibfk_2` FOREIGN KEY (`appointment_id`) REFERENCES `appointments_archive` (`id`),
  ADD CONSTRAINT `consultations_ibfk_3` FOREIGN KEY (`assigned_doctor`) REFERENCES `staff` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `consultations_ibfk_4` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`),
  ADD CONSTRAINT `fk_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `staff` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `laboratory_results`
--
ALTER TABLE `laboratory_results`
  ADD CONSTRAINT `laboratory_results_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `laboratory_results_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `laboratory_results_ibfk_3` FOREIGN KEY (`performed_by`) REFERENCES `staff` (`id`),
  ADD CONSTRAINT `laboratory_results_ibfk_4` FOREIGN KEY (`verified_by`) REFERENCES `staff` (`id`),
  ADD CONSTRAINT `laboratory_results_ibfk_5` FOREIGN KEY (`released_by`) REFERENCES `staff` (`id`);

--
-- Constraints for table `medical_certificates`
--
ALTER TABLE `medical_certificates`
  ADD CONSTRAINT `medical_certificates_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_certificates_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`),
  ADD CONSTRAINT `medical_certificates_ibfk_3` FOREIGN KEY (`issued_by`) REFERENCES `staff` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `medical_certificates_ibfk_4` FOREIGN KEY (`assigned_doctor_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `medicines`
--
ALTER TABLE `medicines`
  ADD CONSTRAINT `medicines_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `medicine_categories` (`id`);

--
-- Constraints for table `medicine_transactions`
--
ALTER TABLE `medicine_transactions`
  ADD CONSTRAINT `medicine_transactions_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`),
  ADD CONSTRAINT `medicine_transactions_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `staff` (`id`),
  ADD CONSTRAINT `medicine_transactions_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `staff` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `patients_ibfk_2` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`);

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `prescriptions_ibfk_3` FOREIGN KEY (`prescribed_by`) REFERENCES `staff` (`id`),
  ADD CONSTRAINT `prescriptions_ibfk_4` FOREIGN KEY (`dispensed_by`) REFERENCES `staff` (`id`);

--
-- Constraints for table `queue`
--
ALTER TABLE `queue`
  ADD CONSTRAINT `queue_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `queue_ibfk_2` FOREIGN KEY (`served_by`) REFERENCES `staff` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `queue_ibfk_3` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `queue_analytics`
--
ALTER TABLE `queue_analytics`
  ADD CONSTRAINT `queue_analytics_ibfk_1` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `queue_display`
--
ALTER TABLE `queue_display`
  ADD CONSTRAINT `queue_display_ibfk_1` FOREIGN KEY (`current_queue_id`) REFERENCES `queue` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `queue_notifications`
--
ALTER TABLE `queue_notifications`
  ADD CONSTRAINT `queue_notifications_ibfk_1` FOREIGN KEY (`queue_id`) REFERENCES `queue` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `referrals_ibfk_3` FOREIGN KEY (`referred_by`) REFERENCES `staff` (`id`);

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`),
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`generated_by`) REFERENCES `staff` (`id`),
  ADD CONSTRAINT `reports_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `staff` (`id`);

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_ibfk_2` FOREIGN KEY (`assigned_barangay_id`) REFERENCES `barangays` (`id`);

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
