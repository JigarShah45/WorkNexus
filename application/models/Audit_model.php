<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Log an audit trail entry
     */
    public function log($action, $table, $record_id = NULL, $old_values = NULL, $new_values = NULL)
    {
        $data = array(
            'user_id'     => $this->session->userdata('user_id'),
            'action'      => $action,
            'table_name'  => $table,
            'record_id'   => $record_id,
            'old_values'  => is_array($old_values) ? json_encode($old_values) : $old_values,
            'new_values'  => is_array($new_values) ? json_encode($new_values) : $new_values,
            'ip_address'  => $this->input->ip_address(),
            'timestamp'   => date('Y-m-d H:i:s')
        );

        return $this->db->insert('tbl_audit_trail', $data);
    }

    /**
     * Get all audit trail records with filters
     */
    public function getAuditTrail($user_id = '', $action = '', $table_name = '', $date_from = '', $date_to = '')
    {
        $this->db->select('tbl_audit_trail.*, tbl_users.username, tbl_employee.employee_name');
        $this->db->from('tbl_audit_trail');
        $this->db->join('tbl_users', 'tbl_users.user_id = tbl_audit_trail.user_id', 'left');
        $this->db->join('tbl_employee', 'tbl_employee.employee_id = tbl_users.employee_id', 'left');

        if (!empty($user_id))
        {
            $this->db->where('tbl_audit_trail.user_id', $user_id);
        }

        if (!empty($action))
        {
            $this->db->where('tbl_audit_trail.action', $action);
        }

        if (!empty($table_name))
        {
            $this->db->where('tbl_audit_trail.table_name', $table_name);
        }

        if (!empty($date_from))
        {
            $this->db->where('tbl_audit_trail.timestamp >=', $date_from . ' 00:00:00');
        }

        if (!empty($date_to))
        {
            $this->db->where('tbl_audit_trail.timestamp <=', $date_to . ' 23:59:59');
        }

        $this->db->order_by('tbl_audit_trail.timestamp', 'DESC');

        return $this->db->get()->result();
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
