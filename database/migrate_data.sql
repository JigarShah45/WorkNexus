-- ============================================================
-- Migration: Import data from employee_db to current schema
-- Source: C:\Users\Admin\Downloads\employee_db.sql
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+05:30";

-- ============================================================
-- 1. tbl_department (13 records from source)
--    Note: department_id 12 (Cleaning) is skipped to avoid
--    UNIQUE KEY conflict with department_id 13 (same name).
-- ============================================================
INSERT INTO `tbl_department` (`department_id`, `department_name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,  'Information Technology',  '2026-07-22 11:32:51', '2026-07-22 08:21:33', NULL),
(2,  'Human Resources',        '2026-07-22 11:32:51', NULL, NULL),
(3,  'Marketing',              '2026-07-22 11:32:51', NULL, NULL),
(4,  'Finance',                '2026-07-22 11:32:51', NULL, NULL),
(5,  'Sales',                  '2026-07-22 11:32:51', NULL, NULL),
(6,  'Operations',             '2026-07-22 11:32:51', NULL, NULL),
(7,  'Customer Support',       '2026-07-22 11:32:51', NULL, NULL),
(8,  'Research & Development', '2026-07-22 11:32:51', NULL, NULL),
(9,  'Administration',         '2026-07-22 11:32:51', NULL, NULL),
(10, 'Quality Assurance',      '2026-07-22 11:32:51', NULL, NULL),
(11, 'Maintenance',            '2026-07-22 08:12:10', NULL, '2026-07-22 14:04:07'),
(13, 'Cleaning',               '2026-07-22 09:09:38', NULL, NULL);

-- ============================================================
-- 2. tbl_employee (27 records from source)
--    profile_image set to NULL (import via import_images.php)
-- ============================================================
INSERT INTO `tbl_employee` (`employee_id`, `employee_name`, `employee_email`, `employee_phone`, `employee_salary`, `department_id`, `created_at`, `status`, `updated_at`, `profile_image`, `deleted_at`) VALUES
(1,  'Jigar Shah',      'jigar@gmail.com',        '9876543201', '66000.00', 1, '2026-07-21 10:52:21', 'Active',   '2026-07-27 02:51:09', NULL, NULL),
(2,  'Rahul Patel',     'rahul@gmail.com',         '9988776655', '45000.00', 2, '2026-07-21 10:52:21', 'Inactive', NULL, NULL, NULL),
(3,  'Priya Sharma',    'priya@gmail.com',         '9871234567', '60000.00', 1, '2026-07-21 10:52:21', 'Active',   NULL, NULL, NULL),
(4,  'Amit Verma',      'amit.verma@gmail.com',    '9876543201', '42000.00', 1, '2026-07-21 10:56:56', 'Active',   NULL, NULL, NULL),
(5,  'Neha Kapoor',     'neha.kapoor@gmail.com',   '9876543202', '55000.00', 2, '2026-07-21 10:56:56', 'Inactive', NULL, NULL, NULL),
(6,  'Rohan Mehta',     'rohan.mehta@gmail.com',   '9876543203', '48000.00', 3, '2026-07-21 10:56:56', 'Active',   NULL, NULL, NULL),
(7,  'Sneha Joshi',     'sneha.joshi@gmail.com',   '9876543204', '62000.00', 1, '2026-07-21 10:56:56', 'Active',   NULL, NULL, NULL),
(8,  'Karan Singh',     'karan.singh@gmail.com',   '9876543205', '45000.00', 2, '2026-07-21 10:56:56', 'Active',   NULL, NULL, NULL),
(9,  'Pooja Shah',      'pooja.shah@gmail.com',    '9876543206', '53000.00', 3, '2026-07-21 10:56:56', 'Active',   NULL, NULL, NULL),
(10, 'Vikas Patel',     'vikas.patel@gmail.com',   '9876543207', '47000.00', 1, '2026-07-21 10:56:56', 'Active',   NULL, NULL, NULL),
(11, 'Anjali Desai',    'anjali.desai@gmail.com',  '9876543208', '58000.00', 2, '2026-07-21 10:56:56', 'Active',   NULL, NULL, NULL),
(12, 'Nikhil Sharma',   'nikhil.sharma@gmail.com', '9876543209', '51000.00', 3, '2026-07-21 10:56:56', 'Active',   NULL, NULL, NULL),
(13, 'Riya Gupta',      'riya.gupta@gmail.com',    '9876543211', '49500.00', 1, '2026-07-21 10:56:56', 'Active',   NULL, NULL, NULL),
(14, 'Arjun Nair',      'arjun.nair@gmail.com',    '9876543212', '61000.00', 2, '2026-07-21 10:56:56', 'Active',   NULL, NULL, NULL),
(15, 'Meera Iyer',      'meera.iyer@gmail.com',    '9876543213', '57000.00', 3, '2026-07-21 10:56:56', 'Active',   NULL, NULL, NULL),
(16, 'Sahil Khan',      'sahil.khan@gmail.com',    '9876543214', '46000.00', 1, '2026-07-21 10:56:56', 'Active',   NULL, NULL, NULL),
(17, 'Kavya Rao',       'kavya.rao@gmail.com',     '9876543215', '54000.00', 2, '2026-07-21 10:56:56', 'Active',   NULL, NULL, NULL),
(18, 'Deepak Mishra',   'deepak.mishra@gmail.com', '9876543216', '50000.00', 3, '2026-07-21 10:56:56', 'Active',   NULL, NULL, NULL),
(19, 'Isha Malhotra',   'isha.malhotra@gmail.com', '9876543217', '56500.00', 1, '2026-07-21 10:56:56', 'Active',   NULL, NULL, NULL),
(20, 'Yash Kulkarni',   'yash.kulkarni@gmail.com', '9876543218', '48500.00', 2, '2026-07-21 10:56:56', 'Active',   NULL, NULL, NULL),
(25, 'Umang Mehta',     'umang@gmail.com',         '9372960989', '50000.00', 4, '2026-07-24 02:39:43', 'Active',   '2026-07-24 02:47:02', NULL, NULL),
(26, 'Dhananjay Dube',  'dddada@gmail.com',        '9372960998', '50000.00', 4, '2026-07-24 02:40:28', 'Active',   '2026-07-24 02:47:13', NULL, NULL),
(27, 'Dhruv Shetty',    'dhruv@gmail.com',         '9912345678', '50000.00', 4, '2026-07-24 02:40:59', 'Active',   '2026-07-24 02:47:23', NULL, NULL),
(28, 'Sanket Sawant',   'sanket@gmail.com',        '9812345678', '50000.00', 4, '2026-07-24 02:41:33', 'Active',   '2026-07-24 02:47:34', NULL, NULL),
(29, 'Admin User',      'admin@example.com',       '9999999999', '100000.00', 1, '2026-07-29 10:34:49', 'Active',   NULL, NULL, NULL);

-- ============================================================
-- 3. tbl_users (2 records from source)
-- ============================================================
INSERT INTO `tbl_users` (`user_id`, `employee_id`, `username`, `password`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 1,  'jigar', '$2y$10$1qnDVlGgkCc4o9va.lPsNubNys7lO1e3XKM91EZzQbr6y1//DwKOa', 'Admin', 'Active', '2026-07-29 08:57:12', '2026-07-28 12:33:30', NULL),
(2, 29, 'admin', '$2y$10$omQ83KukT7HkcCvE2.Uow.BMGR4ztE66SFbN7pr5Olngy7H4XtVGO', 'Admin', 'Active', '2026-07-29 12:50:14', '2026-07-29 10:34:49', NULL);

-- ============================================================
-- Reset AUTO_INCREMENT values
-- ============================================================
ALTER TABLE `tbl_department` AUTO_INCREMENT = 14;
ALTER TABLE `tbl_employee` AUTO_INCREMENT = 30;
ALTER TABLE `tbl_users` AUTO_INCREMENT = 3;

-- ============================================================
-- Re-enable foreign key checks
-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;

COMMIT;
