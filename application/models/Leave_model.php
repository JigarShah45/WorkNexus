<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leave_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    public function createLeaveRequest($data)
    {
        return $this->db->insert('tbl_leave_requests', $data);
    }

    public function getLeaveRequestsByEmployee($employee_id)
    {
        $this->db->select('tbl_leave_requests.*, tbl_users.username as decided_by_name');
        $this->db->from('tbl_leave_requests');
        $this->db->join('tbl_users', 'tbl_users.user_id = tbl_leave_requests.decided_by', 'left');
        $this->db->where('tbl_leave_requests.employee_id', $employee_id);
        $this->db->order_by('tbl_leave_requests.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function getAllLeaveRequests($status = '', $employee_id = '')
    {
        $this->db->select('tbl_leave_requests.*, tbl_employee.employee_name, tbl_users.username as decided_by_name');
        $this->db->from('tbl_leave_requests');
        $this->db->join('tbl_employee', 'tbl_employee.employee_id = tbl_leave_requests.employee_id');
        $this->db->join('tbl_users', 'tbl_users.user_id = tbl_leave_requests.decided_by', 'left');

        if (!empty($status))
        {
            $this->db->where('tbl_leave_requests.status', $status);
        }

        if (!empty($employee_id))
        {
            $this->db->where('tbl_leave_requests.employee_id', $employee_id);
        }

        $this->db->order_by('tbl_leave_requests.created_at', 'DESC');

        return $this->db->get()->result();
    }

    public function getLeaveById($leave_id)
    {
        $this->db->select('tbl_leave_requests.*, tbl_employee.employee_name, tbl_employee.employee_email');
        $this->db->from('tbl_leave_requests');
        $this->db->join('tbl_employee', 'tbl_employee.employee_id = tbl_leave_requests.employee_id');
        $this->db->where('tbl_leave_requests.leave_id', $leave_id);
        return $this->db->get()->row();
    }

    public function updateLeaveStatus($leave_id, $status, $decided_by, $hr_remarks = '')
    {
        $this->db->where('leave_id', $leave_id);
        return $this->db->update('tbl_leave_requests', array(
            'status'      => $status,
            'decided_by'  => $decided_by,
            'hr_remarks'  => $hr_remarks,
            'decided_at'  => date('Y-m-d H:i:s')
        ));
    }

    public function getPendingLeaveCount()
    {
        return $this->db->where('status', 'Pending')
            ->count_all_results('tbl_leave_requests');
    }
}
