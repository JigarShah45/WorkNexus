-- ============================================================
-- WorkNexus - Fix Salary Hikes Table Structure
-- Ensures all required columns exist for the new workflow
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+05:30";

-- -----------------------------------------------------------
-- Add missing columns to tbl_salary_hikes
-- -----------------------------------------------------------

ALTER TABLE `tbl_salary_hikes`
    ADD COLUMN IF NOT EXISTS `hike_amount` decimal(12,2) DEFAULT 0.00 AFTER `hike_percentage`,
    ADD COLUMN IF NOT EXISTS `proposed_by` int(11) DEFAULT NULL AFTER `justification`,
    ADD COLUMN IF NOT EXISTS `proposed_at` datetime DEFAULT NULL AFTER `proposed_by`,
    ADD COLUMN IF NOT EXISTS `approved_by` int(11) DEFAULT NULL AFTER `proposed_at`,
    ADD COLUMN IF NOT EXISTS `approved_at` datetime DEFAULT NULL AFTER `approved_by`,
    ADD COLUMN IF NOT EXISTS `rejected_by` int(11) DEFAULT NULL AFTER `approved_at`,
    ADD COLUMN IF NOT EXISTS `rejected_at` datetime DEFAULT NULL AFTER `rejected_by`,
    ADD COLUMN IF NOT EXISTS `rejection_reason` text DEFAULT NULL AFTER `rejected_at`,
    ADD COLUMN IF NOT EXISTS `effective_date` date DEFAULT NULL AFTER `rejection_reason`;

-- Add indexes for new columns
ALTER TABLE `tbl_salary_hikes`
    ADD KEY IF NOT EXISTS `idx_proposed_by` (`proposed_by`),
    ADD KEY IF NOT EXISTS `idx_approved_by` (`approved_by`),
    ADD KEY IF NOT EXISTS `idx_rejected_by` (`rejected_by`);

-- -----------------------------------------------------------
-- Update existing records to populate new columns from existing data
-- -----------------------------------------------------------
UPDATE `tbl_salary_hikes`
    SET `proposed_by` = `decided_by`,
        `proposed_at` = `created_at`,
        `hike_amount` = `proposed_salary` - `current_salary`
    WHERE `proposed_by` IS NULL;

UPDATE `tbl_salary_hikes`
    SET `approved_by` = `decided_by`,
        `approved_at` = DATE_ADD(`created_at`, INTERVAL 1 HOUR)
    WHERE `status` = 'Approved' AND `approved_by` IS NULL;

UPDATE `tbl_salary_hikes`
    SET `rejected_by` = `decided_by`,
        `rejected_at` = DATE_ADD(`created_at`, INTERVAL 1 HOUR)
    WHERE `status` = 'Rejected' AND `rejected_by` IS NULL;

-- -----------------------------------------------------------
-- Update the status enum to include 'Pending'
-- -----------------------------------------------------------
ALTER TABLE `tbl_salary_hikes`
    MODIFY COLUMN `status` enum('Pending','Approved','Rejected','Proposed') NOT NULL DEFAULT 'Pending';

-- -----------------------------------------------------------
-- Update existing 'Proposed' records to 'Pending' for consistency
-- -----------------------------------------------------------
UPDATE `tbl_salary_hikes`
    SET `status` = 'Pending'
    WHERE `status` = 'Proposed';

-- -----------------------------------------------------------
-- Enable edit_salary_data for HR role
-- -----------------------------------------------------------
UPDATE `tbl_role_permissions`
    SET `is_enabled` = 1
    WHERE `role` = 'HR' AND `permission_id` = 16;

INSERT IGNORE INTO `tbl_role_permissions` (`role`, `permission_id`, `is_enabled`)
    VALUES ('HR', 16, 1);

-- Ensure view_salary_data is enabled for HR
UPDATE `tbl_role_permissions`
    SET `is_enabled` = 1
    WHERE `role` = 'HR' AND `permission_id` = 15;

-- Remove user-level overrides that block HR salary access
DELETE up FROM `tbl_user_permissions` up
    INNER JOIN tbl_users u ON u.user_id = up.user_id
    WHERE u.role = 'HR'
    AND up.permission_id IN (10, 15, 16)
    AND up.is_enabled = 0;

COMMIT;
