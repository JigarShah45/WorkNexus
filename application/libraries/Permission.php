<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Permission {

    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('Permission_model');
    }

    /**
     * Load user permissions into session
     */
    public function loadUserPermissions()
    {
        $user_id = $this->CI->session->userdata('user_id');
        $role = $this->CI->session->userdata('role');

        if (!$user_id || !$role)
        {
            return FALSE;
        }

        // Get role-based permissions
        $rolePermissions = $this->CI->Permission_model->getRolePermissions($role);

        // Get user-level overrides
        $userOverrides = $this->CI->Permission_model->getUserPermissions($user_id);

        // Merge: user overrides take precedence over role defaults
        $permissions = array();
        foreach ($rolePermissions as $perm)
        {
            $permissions[$perm->permission_key] = $perm->is_enabled;
        }

        foreach ($userOverrides as $perm)
        {
            $permissions[$perm->permission_key] = $perm->is_enabled;
        }

        $this->CI->session->set_userdata(array(
            'permissions' => $permissions,
            'permissions_loaded' => TRUE
        ));

        return TRUE;
    }

    /**
     * Save permissions for a role
     */
    public function saveRolePermissions($role, $permission_ids, $is_enabled_values)
    {
        return $this->CI->Permission_model->saveRolePermissions($role, $permission_ids, $is_enabled_values);
    }

    /**
     * Save user-level permission overrides
     */
    public function saveUserPermissions($user_id, $permission_ids, $is_enabled_values)
    {
        return $this->CI->Permission_model->saveUserPermissions($user_id, $permission_ids, $is_enabled_values);
    }

    /**
     * Get all permissions
     */
    public function getAllPermissions()
    {
        return $this->CI->Permission_model->getAllPermissions();
    }

    /**
     * Get permissions grouped by category
     */
    public function getPermissionsByCategory()
    {
        return $this->CI->Permission_model->getPermissionsByCategory();
    }

    /**
     * Get default checked permissions for a role
     */
    public function getRoleDefaults($role)
    {
        $permissions = $this->CI->Permission_model->getRolePermissions($role);
        $defaults = array();
        foreach ($permissions as $perm)
        {
            $defaults[$perm->permission_id] = $perm->is_enabled;
        }
        return $defaults;
    }

    /**
     * Reload permissions (call after updates)
     */
    public function reloadPermissions()
    {
        $this->CI->session->unset_userdata('permissions_loaded');
        $this->CI->session->unset_userdata('permissions');
        $this->loadUserPermissions();
    }
}
