<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('User_model');
        $this->load->model('Audit_model');
        $this->load->library(array('form_validation'));
        $this->load->library('permission');
    }

    public function index()
    {
        $this->requirePermission('access_user_management');

        $data['title'] = "User Management";
        $data['users'] = $this->User_model->getUsers();

        $this->load->view('user/index', $data);
    }

    public function add()
    {
        $this->requirePermission('access_user_management');

        $data['title'] = "Add User";
        $data['employees'] = $this->User_model->getEmployees();
        $data['permissions'] = $this->permission->getPermissionsByCategory();
        $data['role_defaults'] = array();
        $data['selected_permissions'] = array();

        $this->load->view('user/add', $data);
    }

    public function store()
    {
        $this->requirePermission('access_user_management');

        $this->validateUserForm();

        if ($this->form_validation->run() == FALSE)
        {
            $this->add();
        }
        else
        {
            if($this->User_model->usernameExists($this->input->post('username')))
            {
                $this->session->set_flashdata('error', 'Username already exists.');
                redirect('user/add');
            }

            // Role escalation protection: only Admin can assign Admin role
            $requested_role = $this->input->post('role');
            if ($requested_role === 'Admin' && !$this->isAdmin())
            {
                $this->session->set_flashdata('error', 'Only Admin can assign Admin role.');
                redirect('user/add');
            }

            $user_id = $this->User_model->insertUser();

            // Save permission checkboxes
            $permission_ids = $this->input->post('permission_ids');
            $is_enabled = $this->input->post('permissions_enabled');

            if (!empty($permission_ids))
            {
                $this->permission->saveUserPermissions($user_id, $permission_ids, $is_enabled ? $is_enabled : array());
            }

            $this->Audit_model->log('CREATE', 'tbl_users', $user_id, NULL, array(
                'username' => $this->input->post('username'),
                'role' => $this->input->post('role')
            ));

            $this->session->set_flashdata('success', 'User created successfully.');
            redirect('user');
        }
    }

    public function edit($id)
    {
        $this->requirePermission('access_user_management');

        if (!$this->User_model->userExists($id))
        {
            show_404();
        }

        $data['title'] = "Edit User";
        $data['user'] = $this->User_model->getUserById($id);
        $data['employees'] = $this->User_model->getEmployeesForEdit($id);
        $data['permissions'] = $this->permission->getPermissionsByCategory();
        $data['user_permissions'] = $this->User_model->getUserPermissions($id);

        $this->load->view('user/edit', $data);
    }

    public function update($id)
    {
        $this->requirePermission('access_user_management');

        if (!$this->User_model->userExists($id))
        {
            show_404();
        }

        $this->form_validation->set_rules('username', 'Username', 'required|min_length[4]');

        $password = $this->input->post('password');
        if (!empty($password))
        {
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');
        }

        $this->form_validation->set_rules('role', 'Role', 'required');
        $this->form_validation->set_rules('status', 'Status', 'required');

        if ($this->form_validation->run() == FALSE)
        {
            $this->edit($id);
        }
        else
        {
            // Role escalation protection: only Admin can assign Admin role
            $requested_role = $this->input->post('role');
            if ($requested_role === 'Admin' && !$this->isAdmin())
            {
                $this->session->set_flashdata('error', 'Only Admin can assign Admin role.');
                redirect('user/edit/' . $id);
            }

            // Prevent non-Admin from editing Admin accounts
            $existing = $this->User_model->getUserById($id);
            if ($existing->role === 'Admin' && !$this->isAdmin())
            {
                $this->session->set_flashdata('error', 'Only Admin can edit Admin accounts.');
                redirect('user');
            }

            $username_check = $this->User_model->usernameExistsExcept($this->input->post('username'), $id);

            if ($username_check)
            {
                $this->session->set_flashdata('error', 'Username already exists.');
                redirect('user/edit/' . $id);
            }

            $this->User_model->updateUser($id);

            // Update permissions
            $permission_ids = $this->input->post('permission_ids');
            $is_enabled = $this->input->post('permissions_enabled');

            if (!empty($permission_ids))
            {
                $this->permission->saveUserPermissions($id, $permission_ids, $is_enabled ? $is_enabled : array());
            }

            // Reload permissions if editing own account
            if ($id == $this->session->userdata('user_id'))
            {
                $this->permission->reloadPermissions();
            }

            $this->Audit_model->log('UPDATE', 'tbl_users', $id, (array)$existing, array(
                'username' => $this->input->post('username'),
                'role' => $this->input->post('role')
            ));

            $this->session->set_flashdata('success', 'User updated successfully.');
            redirect('user');
        }
    }

    public function delete($id)
    {
        $this->requirePermission('access_user_management');

        if (!$this->input->is_ajax_request())
        {
            show_404();
        }

        if (!$this->User_model->userExists($id))
        {
            echo json_encode(array('status' => false, 'message' => 'User not found.'));
            return;
        }

        // Prevent non-Admin from deleting Admin accounts
        $old = $this->User_model->getUserById($id);
        if ($old->role === 'Admin' && !$this->isAdmin())
        {
            echo json_encode(array('status' => false, 'message' => 'Only Admin can delete Admin accounts.'));
            return;
        }

        $this->User_model->deleteUser($id);

        $this->Audit_model->log('DELETE', 'tbl_users', $id, (array)$old, NULL);

        echo json_encode(array('status' => true, 'message' => 'User deleted successfully.'));
    }

    public function get_role_permissions()
    {
        $role = $this->input->post('role');
        $defaults = $this->permission->getRoleDefaults($role);
        echo json_encode($defaults);
    }

    private function validateUserForm()
    {
        $this->form_validation->set_rules('employee_id', 'Employee', 'required');
        $this->form_validation->set_rules('username', 'Username', 'required|min_length[4]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');
        $this->form_validation->set_rules('role', 'Role', 'required');
        $this->form_validation->set_rules('status', 'Status', 'required');
    }
}
