-- ============================================================
-- Migration: Enable hike_management permission for Employee role
-- This allows employees to access the "My Salary" page
-- ============================================================

UPDATE `tbl_role_permissions` 
SET `is_enabled` = 1 
WHERE `role` = 'Employee' 
AND `permission_id` = (
    SELECT permission_id FROM (
        SELECT permission_id FROM `tbl_permissions` WHERE `permission_key` = 'access_hike_management'
    ) AS temp
);

-- Verify the change
SELECT rp.role, p.permission_key, p.permission_name, rp.is_enabled
FROM tbl_role_permissions rp
JOIN tbl_permissions p ON p.permission_id = rp.permission_id
WHERE rp.role = 'Employee'
ORDER BY rp.permission_id;
