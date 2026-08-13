-- ============================================================
-- Employee Management System - Seed Data
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+05:30";

-- -----------------------------------------------------------
-- Default Permissions (16 checkboxes)
-- -----------------------------------------------------------
INSERT INTO `tbl_permissions` (`permission_id`, `permission_key`, `permission_name`, `category`) VALUES
(1,  'access_dashboard',        'Access Dashboard',              'Page Access'),
(2,  'access_employee',         'Access Employee Module',        'Page Access'),
(3,  'access_department',       'Access Department Module',      'Page Access'),
(4,  'access_user_management',  'Access User Management',        'Page Access'),
(5,  'access_reports',          'Access Reports',                'Page Access'),
(6,  'access_settings',         'Access Settings',               'Page Access'),
(7,  'access_attendance',       'Access Attendance',             'Page Access'),
(8,  'access_leave',            'Access Leave Management',       'Page Access'),
(9,  'access_meetings',         'Access Client Meetings',        'Page Access'),
(10, 'access_hike_management',  'Access Salary Hike',            'Page Access'),
(11, 'access_audit_trail',      'View Audit Trail',              'Security'),
(12, 'access_user_logs',        'View User Logs',                'Security'),
(13, 'view_employee_data',      'View Employee Data',            'Data Access'),
(14, 'edit_employee_data',      'Edit Employee Data',            'Data Access'),
(15, 'view_salary_data',        'View Salary Data',              'Data Access'),
(16, 'edit_salary_data',        'Edit Salary Data',              'Data Access');

-- -----------------------------------------------------------
-- Default Role Permission Mappings
-- -----------------------------------------------------------
-- Admin: All permissions enabled
INSERT INTO `tbl_role_permissions` (`role`, `permission_id`, `is_enabled`) VALUES
('Admin', 1, 1), ('Admin', 2, 1), ('Admin', 3, 1), ('Admin', 4, 1),
('Admin', 5, 1), ('Admin', 6, 1), ('Admin', 7, 1), ('Admin', 8, 1),
('Admin', 9, 1), ('Admin', 10, 1), ('Admin', 11, 1), ('Admin', 12, 1),
('Admin', 13, 1), ('Admin', 14, 1), ('Admin', 15, 1), ('Admin', 16, 1);

-- HR: Most permissions except user management and settings
INSERT INTO `tbl_role_permissions` (`role`, `permission_id`, `is_enabled`) VALUES
('HR', 1, 1), ('HR', 2, 1), ('HR', 3, 1), ('HR', 4, 0),
('HR', 5, 1), ('HR', 6, 0), ('HR', 7, 1), ('HR', 8, 1),
('HR', 9, 1), ('HR', 10, 1), ('HR', 11, 0), ('HR', 12, 0),
('HR', 13, 1), ('HR', 14, 1), ('HR', 15, 1), ('HR', 16, 0);

-- Manager: Limited access
INSERT INTO `tbl_role_permissions` (`role`, `permission_id`, `is_enabled`) VALUES
('Manager', 1, 1), ('Manager', 2, 1), ('Manager', 3, 0), ('Manager', 4, 0),
('Manager', 5, 1), ('Manager', 6, 0), ('Manager', 7, 1), ('Manager', 8, 1),
('Manager', 9, 1), ('Manager', 10, 0), ('Manager', 11, 0), ('Manager', 12, 0),
('Manager', 13, 1), ('Manager', 14, 0), ('Manager', 15, 0), ('Manager', 16, 0);

-- Employee: Basic access only (hike_management enabled for My Salary page)
INSERT INTO `tbl_role_permissions` (`role`, `permission_id`, `is_enabled`) VALUES
('Employee', 1, 1), ('Employee', 2, 0), ('Employee', 3, 0), ('Employee', 4, 0),
('Employee', 5, 0), ('Employee', 6, 0), ('Employee', 7, 1), ('Employee', 8, 1),
('Employee', 9, 1), ('Employee', 10, 1), ('Employee', 11, 0), ('Employee', 12, 0),
('Employee', 13, 1), ('Employee', 14, 0), ('Employee', 15, 0), ('Employee', 16, 0);

-- -----------------------------------------------------------
-- Default Shifts
-- -----------------------------------------------------------
INSERT INTO `tbl_shifts` (`shift_id`, `shift_name`, `start_time`, `end_time`) VALUES
(1, 'Morning',   '09:00:00', '17:00:00'),
(2, 'Afternoon', '13:00:00', '21:00:00'),
(3, 'Night',     '21:00:00', '05:00:00');

COMMIT;
