-- ============================================================
-- WorkNexus Attendance Business Rules - Database Migration
--
-- Run this ONCE against the 'employee' database:
--   mysql -u root employee < database/attendance_business_rules.sql
-- (phpMyAdmin: import this file)
--
-- 1. Adds an `auto_closed` marker so records that were automatically
--    closed at 7:00 PM IST can be identified.
--    Backward compatible: existing rows default to 0, no data is removed.
--
-- 2. Updates the DEFAULT shift (shift_id = 1) to the official
--    11:00 AM - 7:00 PM IST day shift so the Shift column shown in the
--    Attendance UI matches the new attendance calculation.
--    Employees with an explicit shift assignment keep their assignment;
--    only the default/unassigned shift definition is corrected.
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- 1. Auto-closed marker on attendance records ---------------------
ALTER TABLE `tbl_attendance`
    ADD COLUMN `auto_closed` tinyint(1) NOT NULL DEFAULT 0 AFTER `status`;

-- 2. Default shift -> official 11:00 AM - 7:00 PM IST day shift -----
UPDATE `tbl_shifts`
SET `shift_name` = 'Day Shift',
    `start_time` = '11:00:00',
    `end_time`   = '19:00:00'
WHERE `shift_id` = 1;