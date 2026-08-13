<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getUsers()
    {
        $this->db->select('
            tbl_users.*,
            tbl_employee.employee_name,
            tbl_department.department_name
        ');

        $this->db->from('tbl_users');

        $this->db->join(
            'tbl_employee',
            'tbl_employee.employee_id = tbl_users.employee_id'
        );

        $this->db->join(
            'tbl_department',
            'tbl_department.department_id = tbl_employee.department_id'
        );

        $this->db->where('tbl_employee.deleted_at', NULL);

        return $this->db->get()->result();
    }

    public function getEmployees()
    {
        $this->db->select('
        tbl_employee.employee_id,
        tbl_employee.employee_name
    ');

        $this->db->from('tbl_employee');

        $this->db->where('tbl_employee.deleted_at', NULL);

        $this->db->where("
        tbl_employee.employee_id NOT IN
        (
            SELECT employee_id
            FROM tbl_users
        )
    ", NULL, FALSE);

        $this->db->order_by('employee_name', 'ASC');

        return $this->db->get()->result();
    }

    public function getEmployeesForEdit($user_id)
    {
        $user = $this->getUserById($user_id);
        $current_employee_id = $user ? $user->employee_id : 0;

        $this->db->select('tbl_employee.employee_id, tbl_employee.employee_name');
        $this->db->from('tbl_employee');
        $this->db->where('tbl_employee.deleted_at', NULL);

        $this->db->group_start();
        $this->db->where('tbl_employee.employee_id', $current_employee_id);
        $this->db->or_where("
            tbl_employee.employee_id NOT IN (
                SELECT employee_id FROM tbl_users WHERE employee_id != {$current_employee_id}
            )
        ", NULL, FALSE);
        $this->db->group_end();

        $this->db->order_by('employee_name', 'ASC');
        return $this->db->get()->result();
    }

    public function insertUser()
    {
        $data = array(
            'employee_id' => $this->input->post('employee_id'),
            'username'    => $this->input->post('username'),
            'password'    => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
            'role'        => $this->input->post('role'),
            'status'      => $this->input->post('status'),
            'created_at'  => date('Y-m-d H:i:s')
        );

        $this->db->insert('tbl_users', $data);
        return $this->db->insert_id();
    }

    public function updateUser($id)
    {
        $data = array(
            'employee_id' => $this->input->post('employee_id'),
            'username'    => $this->input->post('username'),
            'role'        => $this->input->post('role'),
            'status'      => $this->input->post('status'),
            'updated_at'  => date('Y-m-d H:i:s')
        );

        $password = $this->input->post('password');
        if (!empty($password))
        {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->db->where('user_id', $id);
        return $this->db->update('tbl_users', $data);
    }

    public function getUserById($id)
    {
        return $this->db->where('user_id', $id)->get('tbl_users')->row();
    }

    public function getUserPermissions($user_id)
    {
        $this->db->select('permission_id, is_enabled');
        $this->db->from('tbl_user_permissions');
        $this->db->where('user_id', $user_id);
        $result = $this->db->get()->result();

        $perms = array();
        foreach ($result as $r)
        {
            $perms[$r->permission_id] = $r->is_enabled;
        }
        return $perms;
    }

    public function usernameExists($username)
    {
        return $this->db
            ->where('username', $username)
            ->count_all_results('tbl_users') > 0;
    }

    public function usernameExistsExcept($username, $id)
    {
        return $this->db
            ->where('username', $username)
            ->where('user_id !=', $id)
            ->count_all_results('tbl_users') > 0;
    }

    public function userExists($id)
    {
        return $this->db
            ->where('user_id', $id)
            ->count_all_results('tbl_users') > 0;
    }

    public function deleteUser($id)
    {
        $this->db->where('user_id', $id);
        return $this->db->delete('tbl_users');
    }
}
