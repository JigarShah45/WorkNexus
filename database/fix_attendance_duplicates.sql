-- ============================================================
-- WorkNexus Attendance - Duplicate Records Fix + DB Constraints
--
-- Run ONCE against the 'employee' database:
--   mysql -u root employee < database/fix_attendance_duplicates.sql
-- (or import this file via phpMyAdmin)
--
-- What this migration does:
--   1. PREVIEWS the existing duplicate (employee_id, attendance_date) groups
--      so you can review them before anything is deleted.
--   2. Removes ONLY genuine duplicate rows. For each employee/day the
--      retained record is the one with the EARLIEST / most valid clock-in
--      (ties keep the smallest attendance_id). No other data is touched.
--   3. Adds a UNIQUE KEY on (employee_id, attendance_date) so the database
--      itself rejects any future duplicate insert attempt.
--   4. Ensures the `auto_closed` marker column exists (used to identify
--      records automatically closed at the 7:00 PM effective clock-out).
--   5. Recalculates hours_worked / overtime_hours for already-finalized
--      records so existing data matches the new calculation rules:
--        Hours Worked = Clock In -> 7:00 PM (gross, no lunch deduction)
--        Overtime     = Actual Clock Out - 7:00 PM (0 when no real logout)
--   6. Displays a verification query at the end (run it to confirm).
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8;

-- ------------------------------------------------------------
-- 1. PREVIEW: duplicate groups (informational - nothing deleted yet)
-- ------------------------------------------------------------
SELECT employee_id, attendance_date, COUNT(*) AS duplicate_count,
       GROUP_CONCAT(attendance_id ORDER BY attendance_id) AS record_ids
FROM tbl_attendance
GROUP BY employee_id, attendance_date
HAVING COUNT(*) > 1
ORDER BY attendance_date DESC, employee_id;

-- ------------------------------------------------------------
-- 2. Remove genuine duplicates - keep the earliest / valid clock-in
--    For every (employee_id, attendance_date) the retained row is the one
--    with the earliest clock_in (ties: smallest attendance_id).
-- ------------------------------------------------------------
DELETE FROM tbl_attendance
WHERE attendance_id NOT IN (
    SELECT keeper_id
    FROM (
        SELECT MIN(a.attendance_id) AS keeper_id
        FROM tbl_attendance a
        INNER JOIN (
            SELECT employee_id, attendance_date, MIN(clock_in) AS min_clock_in
            FROM tbl_attendance
            GROUP BY employee_id, attendance_date
        ) m
            ON m.employee_id = a.employee_id
            AND m.attendance_date = a.attendance_date
        WHERE a.clock_in = m.min_clock_in
           OR (a.clock_in IS NULL AND m.min_clock_in IS NULL)
        GROUP BY a.employee_id, a.attendance_date
    ) keepers
);

-- ------------------------------------------------------------
-- 3. Unique constraint on (employee_id, attendance_date)
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS `fix_attendance_add_unique`;
DELIMITER $$

CREATE PROCEDURE `fix_attendance_add_unique`()
BEGIN
    DECLARE idx_count INT DEFAULT 0;

    -- Count unique indexes that contain BOTH employee_id and attendance_date.
    SELECT COUNT(*)
    INTO idx_count
    FROM (
        SELECT INDEX_NAME, COUNT(*) AS cols
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'tbl_attendance'
          AND NON_UNIQUE = 0
          AND COLUMN_NAME IN ('employee_id', 'attendance_date')
        GROUP BY INDEX_NAME
        HAVING cols = 2
    ) uq;

    IF idx_count = 0 THEN
        ALTER TABLE `tbl_attendance`
            ADD UNIQUE KEY `employee_date` (`employee_id`, `attendance_date`);
    END IF;
END$$

DELIMITER ;

CALL `fix_attendance_add_unique`();
DROP PROCEDURE IF EXISTS `fix_attendance_add_unique`;

-- ------------------------------------------------------------
-- 4. Ensure the auto_closed marker column exists
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS `fix_attendance_add_auto_closed`;
DELIMITER $$

CREATE PROCEDURE `fix_attendance_add_auto_closed`()
BEGIN
    DECLARE col_count INT DEFAULT 0;

    SELECT COUNT(*)
    INTO col_count
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_attendance'
      AND COLUMN_NAME = 'auto_closed';

    IF col_count = 0 THEN
        ALTER TABLE `tbl_attendance`
            ADD COLUMN `auto_closed` tinyint(1) NOT NULL DEFAULT 0 AFTER `status`;
    END IF;
END$$

DELIMITER ;

CALL `fix_attendance_add_auto_closed`();
DROP PROCEDURE IF EXISTS `fix_attendance_add_auto_closed`;

-- ------------------------------------------------------------
-- 5. Recalculate hours/overtime for finalized records only
--    (a real clock-out is present OR the record is auto-closed).
--    Open records are recalculated by the application when they are
--    finalized. Only runs when the calculation columns exist.
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS `fix_attendance_recalc_hours`;
DELIMITER $$

CREATE PROCEDURE `fix_attendance_recalc_hours`()
BEGIN
    DECLARE has_hours INT DEFAULT 0;
    DECLARE has_overtime INT DEFAULT 0;

    SELECT COUNT(*) INTO has_hours
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_attendance'
      AND COLUMN_NAME = 'hours_worked';

    SELECT COUNT(*) INTO has_overtime
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_attendance'
      AND COLUMN_NAME = 'overtime_hours';

    IF has_hours > 0 AND has_overtime > 0 THEN
        UPDATE `tbl_attendance`
        SET `hours_worked` = ROUND(
                GREATEST(
                    TIMESTAMPDIFF(
                        SECOND,
                        `clock_in`,
                        LEAST(
                            COALESCE(`clock_out`, DATE_ADD(`attendance_date`, INTERVAL 19 HOUR)),
                            DATE_ADD(`attendance_date`, INTERVAL 19 HOUR)
                        )
                    ),
                    0
                ) / 3600,
                2
            ),
            `overtime_hours` = IF(
                `clock_out` IS NOT NULL,
                ROUND(
                    GREATEST(
                        TIMESTAMPDIFF(
                            SECOND,
                            DATE_ADD(`attendance_date`, INTERVAL 19 HOUR),
                            `clock_out`
                        ),
                        0
                    ) / 3600,
                    2
                ),
                0.00
            )
        WHERE `clock_in` IS NOT NULL
          AND (`clock_out` IS NOT NULL OR `auto_closed` = 1);
    END IF;
END$$

DELIMITER ;

CALL `fix_attendance_recalc_hours`();
DROP PROCEDURE IF EXISTS `fix_attendance_recalc_hours`;

-- ------------------------------------------------------------
-- 6. Verification - should return 0 rows (no duplicates remain)
-- ------------------------------------------------------------
SELECT employee_id, attendance_date, COUNT(*) AS remaining_duplicates
FROM tbl_attendance
GROUP BY employee_id, attendance_date
HAVING COUNT(*) > 1;