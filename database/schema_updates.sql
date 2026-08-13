-- =====================================================================
-- Employee Management System — New Feature Schema
-- =====================================================================
-- Run this AFTER your existing schema (tbl_admin, tbl_department,
-- tbl_employee, tbl_users) is already in place.
--
-- Sections:
--   1. RBAC / Permission checkboxes
--   2. Audit Trail (database-level append-only / read-only)
--   3. User Login / Logout activity log
--   4. Client Meetings
--   5. Attendance & Shifts
--   6. Leave Management
--   7. Salary Hike Management
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- 1. RBAC — PERMISSION CHECKBOXES
-- =====================================================================
-- NOTE: permission_key values MUST match the application code exactly.
-- The code uses keys like access_meetings, access_hike_management, etc.
-- Do NOT use alternative naming like meeting_access or hike_access.

CREATE TABLE IF NOT EXISTS `tbl_permissions` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_key` varchar(100) NOT NULL,
  `permission_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'General',
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `permission_key` (`permission_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_permission` (`user_id`, `permission_id`),
  KEY `permission_id` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Permission keys MUST match the keys used in controllers and sidebar.
-- ON DUPLICATE KEY UPDATE ensures re-running this script is safe.
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

-- Grant every existing user (if any) the default checkboxes so nobody
-- is locked out immediately after this migration runs.
INSERT INTO `tbl_user_permissions` (`user_id`, `permission_id`, `is_enabled`)
SELECT u.user_id, p.permission_id, 1
FROM `tbl_users` u
JOIN `tbl_permissions` p ON p.permission_key IN ('access_dashboard', 'access_attendance', 'access_leave')
ON DUPLICATE KEY UPDATE is_enabled = VALUES(is_enabled);

-- Give existing Admin-role users every checkbox so the app remains
-- usable immediately after migrating.
INSERT INTO `tbl_user_permissions` (`user_id`, `permission_id`, `is_enabled`)
SELECT u.user_id, p.permission_id, 1
FROM `tbl_users` u
JOIN `tbl_permissions` p
WHERE u.role = 'Admin'
ON DUPLICATE KEY UPDATE is_enabled = 1;


-- =====================================================================
-- 2. AUDIT TRAIL — database-level append-only / read-only
-- =====================================================================
-- The application can only INSERT into this table (never UPDATE or
-- DELETE it). That is enforced at TWO levels:
--   a) Application level  -> Audit_lib only ever calls $this->db->insert()
--   b) Database level     -> triggers below reject any UPDATE/DELETE
--      attempt even if a compromised/careless script tries it, and the
--      optional read-only MySQL user at the bottom of this file can be
--      handed to auditors/report tools with zero write access at all.

CREATE TABLE IF NOT EXISTS `tbl_audit_trail` (
  `audit_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `module` varchar(60) NOT NULL,
  `action` varchar(60) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`audit_id`),
  KEY `user_id` (`user_id`),
  KEY `module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TRIGGER IF EXISTS `trg_audit_trail_no_update`;
DROP TRIGGER IF EXISTS `trg_audit_trail_no_delete`;

DELIMITER $$

CREATE TRIGGER `trg_audit_trail_no_update`
BEFORE UPDATE ON `tbl_audit_trail`
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'tbl_audit_trail is append-only: UPDATE is not permitted.';
END$$

CREATE TRIGGER `trg_audit_trail_no_delete`
BEFORE DELETE ON `tbl_audit_trail`
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'tbl_audit_trail is append-only: DELETE is not permitted.';
END$$

DELIMITER ;

-- OPTIONAL but recommended: a dedicated MySQL account that only has
-- SELECT on the audit table, for auditors / BI tools / DB admins who
-- should never be able to write to it at all. Run this manually as a
-- MySQL admin (replace the password), it is commented out so this
-- file can be imported as-is without requiring GRANT privileges:
--
-- CREATE USER 'audit_readonly'@'%' IDENTIFIED BY 'CHANGE_ME_STRONG_PASSWORD';
-- GRANT SELECT ON employee_db.tbl_audit_trail TO 'audit_readonly'@'%';
-- FLUSH PRIVILEGES;


-- =====================================================================
-- 3. USER LOGIN / LOGOUT ACTIVITY LOG
-- =====================================================================

CREATE TABLE IF NOT EXISTS `tbl_user_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `login_time` datetime DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tbl_user_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =====================================================================
-- 4. CLIENT MEETINGS
-- =====================================================================

CREATE TABLE IF NOT EXISTS `tbl_client_meetings` (
  `meeting_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_name` varchar(150) NOT NULL,
  `client_contact` varchar(100) DEFAULT NULL,
  `meeting_date` date NOT NULL,
  `meeting_time` time NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `agenda` text DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') NOT NULL DEFAULT 'Scheduled',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`meeting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_meeting_employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meeting_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `meeting_employee_unique` (`meeting_id`,`employee_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `tbl_meeting_employees_ibfk_1` FOREIGN KEY (`meeting_id`) REFERENCES `tbl_client_meetings` (`meeting_id`) ON DELETE CASCADE,
  CONSTRAINT `tbl_meeting_employees_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `tbl_employee` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- doc_type distinguishes the three upload kinds requested:
-- Minutes (.txt), Minutes (.pdf) and the Presentation (.ppt/.pptx) shown to the client.
CREATE TABLE IF NOT EXISTS `tbl_meeting_documents` (
  `document_id` int(11) NOT NULL AUTO_INCREMENT,
  `meeting_id` int(11) NOT NULL,
  `doc_type` enum('Minutes TXT','Minutes PDF','Presentation') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`document_id`),
  KEY `meeting_id` (`meeting_id`),
  CONSTRAINT `tbl_meeting_documents_ibfk_1` FOREIGN KEY (`meeting_id`) REFERENCES `tbl_client_meetings` (`meeting_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =====================================================================
-- 5. ATTENDANCE & SHIFTS
-- =====================================================================

CREATE TABLE IF NOT EXISTS `tbl_shifts` (
  `shift_id` int(11) NOT NULL AUTO_INCREMENT,
  `shift_name` varchar(50) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`shift_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tbl_shifts` (`shift_name`, `start_time`, `end_time`) VALUES
('Morning Shift', '09:00:00', '17:00:00'),
('Evening Shift',  '13:00:00', '21:00:00'),
('Night Shift',    '21:00:00', '05:00:00')
ON DUPLICATE KEY UPDATE shift_name = VALUES(shift_name);

-- An employee can be assigned to more than one shift.
CREATE TABLE IF NOT EXISTS `tbl_employee_shifts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `shift_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_shift_unique` (`employee_id`,`shift_id`),
  KEY `shift_id` (`shift_id`),
  CONSTRAINT `tbl_employee_shifts_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `tbl_employee` (`employee_id`) ON DELETE CASCADE,
  CONSTRAINT `tbl_employee_shifts_ibfk_2` FOREIGN KEY (`shift_id`) REFERENCES `tbl_shifts` (`shift_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_attendance` (
  `attendance_id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `shift_id` int(11) DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `clock_in` datetime DEFAULT NULL,
  `clock_out` datetime DEFAULT NULL,
  `overtime_minutes` int(11) NOT NULL DEFAULT 0,
  `status` enum('Present','Absent','Half Day','On Leave') NOT NULL DEFAULT 'Present',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`attendance_id`),
  UNIQUE KEY `employee_date_unique` (`employee_id`,`attendance_date`),
  KEY `shift_id` (`shift_id`),
  CONSTRAINT `tbl_attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `tbl_employee` (`employee_id`) ON DELETE CASCADE,
  CONSTRAINT `tbl_attendance_ibfk_2` FOREIGN KEY (`shift_id`) REFERENCES `tbl_shifts` (`shift_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =====================================================================
-- 6. LEAVE MANAGEMENT
-- =====================================================================

CREATE TABLE IF NOT EXISTS `tbl_leave_requests` (
  `leave_id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `leave_type` enum('Sick','Casual','Personal','Maternity','Paternity','Paid','Unpaid','Other') NOT NULL DEFAULT 'Casual',
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `total_days` int(11) NOT NULL DEFAULT 1,
  `reason` text DEFAULT NULL,
  `urgency` enum('Low','Medium','High','Critical','Urgent') NOT NULL DEFAULT 'Low',
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `hr_remarks` varchar(500) DEFAULT NULL,
  `decided_by` int(11) DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`leave_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `tbl_leave_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `tbl_employee` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =====================================================================
-- 7. SALARY HIKE MANAGEMENT
-- =====================================================================

CREATE TABLE IF NOT EXISTS `tbl_salary_hike` (
  `hike_id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `last_salary` decimal(10,2) NOT NULL,
  `hike_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `new_salary` decimal(10,2) NOT NULL,
  `attendance_percent` decimal(5,2) DEFAULT NULL,
  `overtime_hours` decimal(6,2) DEFAULT NULL,
  `paid_leaves` int(11) DEFAULT 0,
  `unpaid_leaves` int(11) DEFAULT 0,
  `reason` text DEFAULT NULL,
  `decided_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`hike_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `tbl_salary_hike_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `tbl_employee` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
