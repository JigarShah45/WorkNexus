-- ============================================================
-- Migration: Add Google Calendar columns to client meetings
-- Run this ONCE against the WorkNexus database.
-- ============================================================
-- Adds google_event_id and google_meet_link to tbl_client_meetings
-- so WorkNexus can store the Google Calendar event reference and
-- the Google Meet join URL for each scheduled meeting.
--
-- Uses dynamic INFORMATION_SCHEMA checks so it works regardless of
-- which schema version was originally installed (same pattern as
-- add_email_sent_columns.sql).
-- ============================================================

-- ============================================================
-- 1. google_event_id: Google Calendar event identifier
-- ============================================================
SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_client_meetings'
      AND COLUMN_NAME = 'google_event_id'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tbl_client_meetings` ADD COLUMN `google_event_id` VARCHAR(255) DEFAULT NULL AFTER `created_at`',
    'SELECT "tbl_client_meetings.google_event_id already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- 2. google_meet_link: Google Meet join URL
-- ============================================================
SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_client_meetings'
      AND COLUMN_NAME = 'google_meet_link'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tbl_client_meetings` ADD COLUMN `google_meet_link` VARCHAR(500) DEFAULT NULL AFTER `google_event_id`',
    'SELECT "tbl_client_meetings.google_meet_link already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
