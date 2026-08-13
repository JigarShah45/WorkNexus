<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    public $permissions = array();

    public function __construct()
    {
        parent::__construct();

        $this->load->library('session');
        $this->load->helper(array('url', 'form'));

        // Check if logged in (except for auth controller)
        $controller = strtolower($this->router->fetch_class());

        if ($controller !== 'auth')
        {
            if (!$this->session->userdata('logged_in'))
            {
                redirect('auth/login');
            }

            // Load permissions into session if not already loaded
            if (!$this->session->userdata('permissions_loaded'))
            {
                $this->load->library('permission');
                $this->permission->loadUserPermissions();
            }

            $this->permissions = $this->session->userdata('permissions') ?: array();
        }
    }

    /**
     * Check if current user has a specific permission
     */
    public function hasPermission($key)
    {
        if (empty($this->permissions))
        {
            return FALSE;
        }

        return isset($this->permissions[$key]) && $this->permissions[$key] == 1;
    }

    /**
     * Require a specific permission - redirect to dashboard if not allowed
     */
    public function requirePermission($key)
    {
        if (!$this->hasPermission($key))
        {
            $this->session->set_flashdata('error', 'You do not have permission to access this page.');
            redirect('dashboard');
        }
    }

    /**
     * Get current user's role
     */
    public function getUserRole()
    {
        return $this->session->userdata('role');
    }

    /**
     * Check if current user is Admin
     */
    public function isAdmin()
    {
        return $this->getUserRole() === 'Admin';
    }

    /**
     * Check if current user is HR
     */
    public function isHR()
    {
        return $this->getUserRole() === 'HR';
    }

    /**
     * Check if current user is Admin or HR (for shared management permissions)
     */
    public function isAdminOrHR()
    {
        $role = $this->getUserRole();
        return $role === 'Admin' || $role === 'HR';
    }
}
