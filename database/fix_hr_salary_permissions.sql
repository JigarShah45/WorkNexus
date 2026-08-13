-- ============================================================
-- WorkNexus - Fix HR Salary Permissions
-- Fixes the "You do not have permission to view salary history" error
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+05:30";

-- -----------------------------------------------------------
-- Enable edit_salary_data (permission_id 16) for HR role
-- This allows HR to create proposals, approve, and reject
-- -----------------------------------------------------------
UPDATE `tbl_role_permissions`
    SET `is_enabled` = 1
    WHERE `role` = 'HR' AND `permission_id` = 16;

-- If the row doesn't exist, insert it
INSERT IGNORE INTO `tbl_role_permissions` (`role`, `permission_id`, `is_enabled`)
    VALUES ('HR', 16, 1);

-- -----------------------------------------------------------
-- Sync permissions for existing HR users
-- The permission system loads from tbl_role_permissions based on role,
-- but existing users may have cached permissions in their session.
-- Clearing the session forces re-login which reloads permissions.
--
-- However, we also need to ensure that if there are any
-- user-level overrides (tbl_user_permissions) that disable
-- salary permissions for HR users, we fix them.
-- -----------------------------------------------------------

-- Remove any user-level overrides that disable salary viewing for HR users
DELETE up FROM `tbl_user_permissions` up
    INNER JOIN tbl_users u ON u.user_id = up.user_id
    WHERE u.role = 'HR' AND up.permission_id IN (15, 16) AND up.is_enabled = 0;

-- -----------------------------------------------------------
-- Ensure view_salary_data (15) is enabled for HR role
-- -----------------------------------------------------------
UPDATE `tbl_role_permissions`
    SET `is_enabled` = 1
    WHERE `role` = 'HR' AND `permission_id` = 15;

INSERT IGNORE INTO `tbl_role_permissions` (`role`, `permission_id`, `is_enabled`)
    VALUES ('HR', 15, 1);

-- -----------------------------------------------------------
-- Also ensure access_hike_management (10) is enabled for HR
-- -----------------------------------------------------------
UPDATE `tbl_role_permissions`
    SET `is_enabled` = 1
    WHERE `role` = 'HR' AND `permission_id` = 10;

INSERT IGNORE INTO `tbl_role_permissions` (`role`, `permission_id`, `is_enabled`)
    VALUES ('HR', 10, 1);

-- -----------------------------------------------------------
-- Ensure Admin has view_salary_data (15) enabled
-- -----------------------------------------------------------
UPDATE `tbl_role_permissions`
    SET `is_enabled` = 1
    WHERE `role` = 'Admin' AND `permission_id` = 15;

-- -----------------------------------------------------------
-- Clean up: Remove any user-level overrides that might block
-- HR users from accessing salary features
-- -----------------------------------------------------------
DELETE up FROM `tbl_user_permissions` up
    INNER JOIN tbl_users u ON u.user_id = up.user_id
    WHERE u.role = 'HR'
    AND up.permission_id IN (10, 15, 16)
    AND up.is_enabled = 0;

COMMIT;
