<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserLog_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Record a login or logout event
     */
    public function recordLog($user_id, $action)
    {
        $data = array(
            'user_id'    => $user_id,
            'action'     => $action,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'timestamp'  => date('Y-m-d H:i:s')
        );

        return $this->db->insert('tbl_user_logs', $data);
    }

    /**
     * Get all user logs with optional user filter
     */
    public function getUserLogs($user_id = '', $action = '', $date_from = '', $date_to = '')
    {
        $this->db->select('tbl_user_logs.*, tbl_users.username, tbl_employee.employee_name');
        $this->db->from('tbl_user_logs');
        $this->db->join('tbl_users', 'tbl_users.user_id = tbl_user_logs.user_id', 'left');
        $this->db->join('tbl_employee', 'tbl_employee.employee_id = tbl_users.employee_id', 'left');

        if (!empty($user_id))
        {
            $this->db->where('tbl_user_logs.user_id', $user_id);
        }

        if (!empty($action))
        {
            $this->db->where('tbl_user_logs.action', $action);
        }

        if (!empty($date_from))
        {
            $this->db->where('tbl_user_logs.timestamp >=', $date_from . ' 00:00:00');
        }

        if (!empty($date_to))
        {
            $this->db->where('tbl_user_logs.timestamp <=', $date_to . ' 23:59:59');
        }

        $this->db->order_by('tbl_user_logs.timestamp', 'DESC');

        return $this->db->get()->result();
    }

    /**
     * Get previous login time for a user (excluding the current login being recorded)
     */
    public function getPreviousLogin($user_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('action', 'LOGIN');
        $this->db->order_by('timestamp', 'DESC');
        $this->db->limit(1);
        $row = $this->db->get('tbl_user_logs')->row();
        return $row ? $row->timestamp : NULL;
    }

    /**
     * Get last login time for a user
     */
    public function getLastLogin($user_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('action', 'LOGIN');
        $this->db->order_by('timestamp', 'DESC');
        $this->db->limit(1);
        $row = $this->db->get('tbl_user_logs')->row();
        return $row ? $row->timestamp : NULL;
    }

    /**
     * Get all users for filter dropdown
     */
    public function getUsers()
    {
        $this->db->select('tbl_users.user_id, tbl_users.username, tbl_employee.employee_name');
        $this->db->from('tbl_users');
        $this->db->join('tbl_employee', 'tbl_employee.employee_id = tbl_users.employee_id', 'left');
        $this->db->order_by('username', 'ASC');
        return $this->db->get()->result();
    }
}
