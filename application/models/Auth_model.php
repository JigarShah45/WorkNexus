<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getUserByUsername($username)
    {
        $this->db->select('
            tbl_users.*,
            tbl_employee.employee_name,
            tbl_employee.employee_email,
            tbl_employee.profile_image
        ');

        $this->db->from('tbl_users');

        $this->db->join(
            'tbl_employee',
            'tbl_employee.employee_id = tbl_users.employee_id'
        );

        $this->db->where('tbl_users.username', $username);
        $this->db->where('tbl_users.status', 'Active');
        $this->db->where('tbl_employee.deleted_at', NULL);

        return $this->db->get()->row();
    }

    public function updateLastLogin($user_id)
    {
        $this->db->where('user_id', $user_id);

        return $this->db->update('tbl_users', array(
            'last_login' => date('Y-m-d H:i:s')
        ));
    }

    public function getLastLogin($user_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('action', 'LOGIN');
        $this->db->order_by('timestamp', 'DESC');
        $this->db->limit(1);
        $row = $this->db->get('tbl_user_logs')->row();
        return $row ? $row->timestamp : NULL;
    }
}
