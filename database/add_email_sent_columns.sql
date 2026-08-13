-- ============================================================
-- Migration: Add email_sent columns for duplicate email protection
-- Run this ONCE against the WorkNexus database.
-- ============================================================
-- Uses dynamic checks so it works regardless of which schema
-- version was originally installed.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. Leave requests: track if notification email was sent
-- ============================================================
SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_leave_requests'
      AND COLUMN_NAME = 'email_sent'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tbl_leave_requests` ADD COLUMN `email_sent` TINYINT(1) NOT NULL DEFAULT 0',
    'SELECT "tbl_leave_requests.email_sent already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- 2. Meeting-employee mapping: track if assignment email sent
-- ============================================================
SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_meeting_employees'
      AND COLUMN_NAME = 'email_sent'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tbl_meeting_employees` ADD COLUMN `email_sent` TINYINT(1) NOT NULL DEFAULT 0',
    'SELECT "tbl_meeting_employees.email_sent already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- 3. Salary hikes: track if decision email was sent
--    Table may be named tbl_salary_hikes or tbl_salary_hike
-- ============================================================
SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_salary_hikes'
      AND COLUMN_NAME = 'email_sent'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tbl_salary_hikes` ADD COLUMN `email_sent` TINYINT(1) NOT NULL DEFAULT 0',
    'SELECT "tbl_salary_hikes.email_sent already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
