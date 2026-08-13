-- ============================================================
-- FIX: Permission key consistency and cleanup
-- ============================================================
-- This script:
--   1. Removes duplicate permission entries from schema_updates.sql
--   2. Ensures all permission keys match the application code
--   3. Ensures tbl_user_permissions has is_enabled column
--   4. Ensures tbl_role_permissions is populated correctly
--   5. Fixes any user_permissions referencing wrong permission_ids
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. Ensure tbl_user_permissions has is_enabled column
-- ============================================================
SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_user_permissions'
      AND COLUMN_NAME = 'is_enabled'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tbl_user_permissions` ADD COLUMN `is_enabled` tinyint(1) NOT NULL DEFAULT 0 AFTER `permission_id`',
    'SELECT "is_enabled column already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- 2. Ensure tbl_role_permissions table exists
-- ============================================================
CREATE TABLE IF NOT EXISTS `tbl_role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` enum('Admin','HR','Manager','Employee') NOT NULL,
  `permission_id` int(11) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permission` (`role`, `permission_id`),
  KEY `permission_id` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================================
-- 3. Ensure correct permissions exist (matching application code)
-- ============================================================
-- These permission_key values MUST match the keys used in:
--   - Controllers (requirePermission calls)
--   - Sidebar (hasPermission calls)
--   - Permission library

INSERT INTO `tbl_permissions` (`permission_key`, `permission_name`, `category`) VALUES
('access_dashboard',        'Access Dashboard',              'Page Access'),
('access_employee',         'Access Employee Module',        'Page Access'),
('access_department',       'Access Department Module',      'Page Access'),
('access_user_management',  'Access User Management',        'Page Access'),
('access_reports',          'Access Reports',                'Page Access'),
('access_settings',         'Access Settings',               'Page Access'),
('access_attendance',       'Access Attendance',             'Page Access'),
('access_leave',            'Access Leave Management',       'Page Access'),
('access_meetings',         'Access Client Meetings',        'Page Access'),
('access_hike_management',  'Access Salary Hike',            'Page Access'),
('access_audit_trail',      'View Audit Trail',              'Security'),
('access_user_logs',        'View User Logs',                'Security'),
('view_employee_data',      'View Employee Data',            'Data Access'),
('edit_employee_data',      'Edit Employee Data',            'Data Access'),
('view_salary_data',        'View Salary Data',              'Data Access'),
('edit_salary_data',        'Edit Salary Data',              'Data Access')
ON DUPLICATE KEY UPDATE permission_name = VALUES(permission_name);

-- ============================================================
-- 4. Remove any duplicate/wrong permission entries
-- ============================================================
-- Remove entries with wrong keys that don't match application code
DELETE FROM `tbl_permissions`
WHERE `permission_key` IN (
    'dashboard_access', 'employee_access', 'department_access',
    'reports_access', 'settings_access', 'user_access',
    'audit_access', 'userlog_access', 'meeting_access',
    'attendance_access', 'attendance_manage_access',
    'leave_access', 'leave_approve_access', 'hike_access',
    'visualization_access'
);

-- ============================================================
-- 5. Fix user_permissions referencing deleted permission_ids
-- ============================================================
-- Remove any user_permissions rows whose permission_id no longer exists
DELETE up FROM `tbl_user_permissions` up
LEFT JOIN `tbl_permissions` p ON p.permission_id = up.permission_id
WHERE p.permission_id IS NULL;

-- ============================================================
-- 6. Re-populate role_permissions for all roles
-- ============================================================
DELETE FROM `tbl_role_permissions`;

-- Admin: All permissions enabled
INSERT INTO `tbl_role_permissions` (`role`, `permission_id`, `is_enabled`)
SELECT 'Admin', permission_id, 1 FROM `tbl_permissions`;

-- HR: Most permissions except user management and settings
INSERT INTO `tbl_role_permissions` (`role`, `permission_id`, `is_enabled`)
SELECT 'HR', permission_id,
    CASE WHEN permission_key IN ('access_user_management','access_settings','access_audit_trail','access_user_logs','edit_salary_data') THEN 0 ELSE 1 END
FROM `tbl_permissions`;

-- Manager: Limited access
INSERT INTO `tbl_role_permissions` (`role`, `permission_id`, `is_enabled`)
SELECT 'Manager', permission_id,
    CASE WHEN permission_key IN ('access_department','access_user_management','access_settings','access_audit_trail','access_user_logs','access_hike_management','edit_employee_data','view_salary_data','edit_salary_data') THEN 0 ELSE 1 END
FROM `tbl_permissions`;

-- Employee: Basic access only (hike_management and meetings enabled for My Salary and My Meetings)
INSERT INTO `tbl_role_permissions` (`role`, `permission_id`, `is_enabled`)
SELECT 'Employee', permission_id,
    CASE WHEN permission_key IN ('access_dashboard','access_attendance','access_leave','view_employee_data','access_hike_management','access_meetings') THEN 1 ELSE 0 END
FROM `tbl_permissions`;

-- ============================================================
-- 7. Ensure every user has default permissions
-- ============================================================
-- Give every user at least dashboard, attendance, leave access
INSERT INTO `tbl_user_permissions` (`user_id`, `permission_id`, `is_enabled`)
SELECT u.user_id, p.permission_id, 1
FROM `tbl_users` u
JOIN `tbl_permissions` p ON p.permission_key IN ('access_dashboard', 'access_attendance', 'access_leave')
ON DUPLICATE KEY UPDATE `is_enabled` = GREATEST(`is_enabled`, VALUES(`is_enabled`));

-- Give Admin users all permissions
INSERT INTO `tbl_user_permissions` (`user_id`, `permission_id`, `is_enabled`)
SELECT u.user_id, p.permission_id, 1
FROM `tbl_users` u
JOIN `tbl_permissions` p
WHERE u.role = 'Admin'
ON DUPLICATE KEY UPDATE `is_enabled` = 1;

SET FOREIGN_KEY_CHECKS = 1;
