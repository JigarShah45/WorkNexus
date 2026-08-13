<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Permission_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    public function getAllPermissions()
    {
        return $this->db->order_by('permission_id', 'ASC')
            ->get('tbl_permissions')
            ->result();
    }

    public function getPermissionsByCategory()
    {
        $perms = $this->db->order_by('category, permission_id', 'ASC')
            ->get('tbl_permissions')
            ->result();

        $grouped = array();
        foreach ($perms as $p)
        {
            $grouped[$p->category][] = $p;
        }
        return $grouped;
    }

    public function getRolePermissions($role)
    {
        $this->db->select('tbl_role_permissions.*, tbl_permissions.permission_key, tbl_permissions.permission_name, tbl_permissions.category');
        $this->db->from('tbl_role_permissions');
        $this->db->join('tbl_permissions', 'tbl_permissions.permission_id = tbl_role_permissions.permission_id');
        $this->db->where('tbl_role_permissions.role', $role);
        return $this->db->get()->result();
    }

    public function getUserPermissions($user_id)
    {
        $this->db->select('tbl_user_permissions.*, tbl_permissions.permission_key, tbl_permissions.permission_name, tbl_permissions.category');
        $this->db->from('tbl_user_permissions');
        $this->db->join('tbl_permissions', 'tbl_permissions.permission_id = tbl_user_permissions.permission_id');
        $this->db->where('tbl_user_permissions.user_id', $user_id);
        return $this->db->get()->result();
    }

    public function saveRolePermissions($role, $permission_ids, $is_enabled_values)
    {
        // Delete existing
        $this->db->where('role', $role);
        $this->db->delete('tbl_role_permissions');

        // Insert new
        $data = array();
        foreach ($permission_ids as $index => $perm_id)
        {
            $data[] = array(
                'role' => $role,
                'permission_id' => $perm_id,
                'is_enabled' => isset($is_enabled_values[$perm_id]) ? 1 : 0
            );
        }

        if (!empty($data))
        {
            return $this->db->insert_batch('tbl_role_permissions', $data);
        }
        return TRUE;
    }

    public function saveUserPermissions($user_id, $permission_ids, $is_enabled_values)
    {
        // Delete existing
        $this->db->where('user_id', $user_id);
        $this->db->delete('tbl_user_permissions');

        // Insert new
        $data = array();
        foreach ($permission_ids as $perm_id)
        {
            $data[] = array(
                'user_id' => $user_id,
                'permission_id' => $perm_id,
                'is_enabled' => isset($is_enabled_values[$perm_id]) ? 1 : 0
            );
        }

        if (!empty($data))
        {
            return $this->db->insert_batch('tbl_user_permissions', $data);
        }
        return TRUE;
    }
}
