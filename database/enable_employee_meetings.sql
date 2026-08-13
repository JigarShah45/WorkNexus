-- Enable access_meetings permission for Employee role
-- This allows employees to see their assigned meetings (My Meetings)

UPDATE `tbl_role_permissions`
SET `is_enabled` = 1
WHERE `role` = 'Employee' AND `permission_id` = 9;
