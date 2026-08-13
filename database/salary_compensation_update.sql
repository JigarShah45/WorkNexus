-- ============================================================
-- WorkNexus - Salary & Compensation Module Redesign
-- Database Schema Updates
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+05:30";

-- -----------------------------------------------------------
-- Add new columns to tbl_salary_hikes for proper workflow
-- -----------------------------------------------------------

ALTER TABLE `tbl_salary_hikes`
    ADD COLUMN IF NOT EXISTS `proposed_by` int(11) DEFAULT NULL AFTER `justification`,
    ADD COLUMN IF NOT EXISTS `proposed_at` datetime DEFAULT NULL AFTER `proposed_by`,
    ADD COLUMN IF NOT EXISTS `approved_by` int(11) DEFAULT NULL AFTER `proposed_at`,
    ADD COLUMN IF NOT EXISTS `approved_at` datetime DEFAULT NULL AFTER `approved_by`,
    ADD COLUMN IF NOT EXISTS `rejected_by` int(11) DEFAULT NULL AFTER `approved_at`,
    ADD COLUMN IF NOT EXISTS `rejected_at` datetime DEFAULT NULL AFTER `rejected_by`,
    ADD COLUMN IF NOT EXISTS `rejection_reason` text DEFAULT NULL AFTER `rejected_at`,
    ADD COLUMN IF NOT EXISTS `effective_date` date DEFAULT NULL AFTER `rejection_reason`,
    ADD COLUMN IF NOT EXISTS `hike_amount` decimal(12,2) DEFAULT 0.00 AFTER `hike_percentage`;

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
-- New permissions for granular salary workflow
-- -----------------------------------------------------------

INSERT IGNORE INTO `tbl_permissions` (`permission_id`, `permission_key`, `permission_name`, `category`) VALUES
(17, 'create_salary_proposal',  'Create Salary Proposal',   'Data Access'),
(18, 'approve_salary_proposal', 'Approve Salary Proposal',  'Data Access'),
(19, 'reject_salary_proposal',  'Reject Salary Proposal',   'Data Access'),
(20, 'view_salary_history',     'View Salary History',      'Data Access'),
(21, 'view_salary_reports',     'View Salary Reports',      'Data Access');

-- -----------------------------------------------------------
-- Assign new permissions to roles
-- -----------------------------------------------------------

-- Admin: view-only permissions (no create/approve/reject)
INSERT IGNORE INTO `tbl_role_permissions` (`role`, `permission_id`, `is_enabled`) VALUES
('Admin', 17, 0), ('Admin', 18, 0), ('Admin', 19, 0), ('Admin', 20, 1), ('Admin', 21, 1);

-- HR: full operational permissions
INSERT IGNORE INTO `tbl_role_permissions` (`role`, `permission_id`, `is_enabled`) VALUES
('HR', 17, 1), ('HR', 18, 1), ('HR', 19, 1), ('HR', 20, 1), ('HR', 21, 1);

-- Manager: no salary workflow permissions
INSERT IGNORE INTO `tbl_role_permissions` (`role`, `permission_id`, `is_enabled`) VALUES
('Manager', 17, 0), ('Manager', 18, 0), ('Manager', 19, 0), ('Manager', 20, 0), ('Manager', 21, 0);

-- Employee: no salary workflow permissions
INSERT IGNORE INTO `tbl_role_permissions` (`role`, `permission_id`, `is_enabled`) VALUES
('Employee', 17, 0), ('Employee', 18, 0), ('Employee', 19, 0), ('Employee', 20, 0), ('Employee', 21, 0);

COMMIT;
