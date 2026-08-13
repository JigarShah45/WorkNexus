-- ============================================================
-- FIX: Leave Table Schema Mismatch
-- ============================================================
-- The application uses leave_type and urgency values that
-- don't match the original enum definitions.
-- This script alters the enums to match the application code.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. Fix leave_type enum: add Personal, Maternity, Paternity
-- ============================================================
-- Original: enum('Sick','Casual','Paid','Unpaid','Other')
-- New:      enum('Sick','Casual','Personal','Maternity','Paternity','Paid','Unpaid','Other')
ALTER TABLE `tbl_leave_requests`
    MODIFY COLUMN `leave_type` enum('Sick','Casual','Personal','Maternity','Paternity','Paid','Unpaid','Other') NOT NULL DEFAULT 'Casual';

-- ============================================================
-- 2. Fix urgency enum: add Critical (form uses Critical, not Urgent)
-- ============================================================
-- Original: enum('Low','Medium','High','Urgent')
-- New:      enum('Low','Medium','High','Critical','Urgent')
ALTER TABLE `tbl_leave_requests`
    MODIFY COLUMN `urgency` enum('Low','Medium','High','Critical','Urgent') NOT NULL DEFAULT 'Low';

-- ============================================================
-- 3. Ensure from_date and to_date columns exist
-- (schema_updates.sql may have created start_date/end_date)
-- ============================================================
-- Check if start_date exists and rename it
SET @start_col = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_leave_requests'
      AND COLUMN_NAME = 'start_date'
);

SET @from_col = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_leave_requests'
      AND COLUMN_NAME = 'from_date'
);

-- If start_date exists but from_date doesn't, rename it
SET @sql = IF(@start_col > 0 AND @from_col = 0,
    'ALTER TABLE `tbl_leave_requests` CHANGE COLUMN `start_date` `from_date` date NOT NULL',
    'SELECT "Column rename not needed"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @end_col = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_leave_requests'
      AND COLUMN_NAME = 'end_date'
);

SET @to_col = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_leave_requests'
      AND COLUMN_NAME = 'to_date'
);

-- If end_date exists but to_date doesn't, rename it
SET @sql = IF(@end_col > 0 AND @to_col = 0,
    'ALTER TABLE `tbl_leave_requests` CHANGE COLUMN `end_date` `to_date` date NOT NULL',
    'SELECT "Column rename not needed"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- 4. Ensure total_days column exists
-- ============================================================
SET @total_days_col = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_leave_requests'
      AND COLUMN_NAME = 'total_days'
);

SET @sql = IF(@total_days_col = 0,
    'ALTER TABLE `tbl_leave_requests` ADD COLUMN `total_days` int(11) NOT NULL DEFAULT 1 AFTER `to_date`',
    'SELECT "total_days column already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- 5. Rename reviewed_by → decided_by if needed
-- ============================================================
SET @reviewed_col = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_leave_requests'
      AND COLUMN_NAME = 'reviewed_by'
);

SET @decided_col = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_leave_requests'
      AND COLUMN_NAME = 'decided_by'
);

SET @sql = IF(@reviewed_col > 0 AND @decided_col = 0,
    'ALTER TABLE `tbl_leave_requests` CHANGE COLUMN `reviewed_by` `decided_by` int(11) DEFAULT NULL',
    'SELECT "Column rename not needed"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- 6. Rename review_comment → hr_remarks if needed
-- ============================================================
SET @review_comment_col = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_leave_requests'
      AND COLUMN_NAME = 'review_comment'
);

SET @hr_remarks_col = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_leave_requests'
      AND COLUMN_NAME = 'hr_remarks'
);

SET @sql = IF(@review_comment_col > 0 AND @hr_remarks_col = 0,
    'ALTER TABLE `tbl_leave_requests` CHANGE COLUMN `review_comment` `hr_remarks` varchar(500) DEFAULT NULL',
    'SELECT "Column rename not needed"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- 7. Rename reviewed_at → decided_at if needed
-- ============================================================
SET @reviewed_at_col = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_leave_requests'
      AND COLUMN_NAME = 'reviewed_at'
);

SET @decided_at_col = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_leave_requests'
      AND COLUMN_NAME = 'decided_at'
);

SET @sql = IF(@reviewed_at_col > 0 AND @decided_at_col = 0,
    'ALTER TABLE `tbl_leave_requests` CHANGE COLUMN `reviewed_at` `decided_at` datetime DEFAULT NULL',
    'SELECT "Column rename not needed"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
