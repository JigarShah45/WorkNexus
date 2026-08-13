<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hike_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    public function getAllHikes()
    {
        $this->db->select('tbl_salary_hikes.*,
            tbl_employee.employee_name,
            tbl_employee.employee_email,
            tbl_department.department_name,
            proposer.username as proposed_by_name,
            approver.username as approved_by_name,
            rejecter.username as rejected_by_name');
        $this->db->from('tbl_salary_hikes');
        $this->db->join('tbl_employee', 'tbl_employee.employee_id = tbl_salary_hikes.employee_id');
        $this->db->join('tbl_department', 'tbl_department.department_id = tbl_employee.department_id', 'left');
        $this->db->join('tbl_users as proposer', 'proposer.user_id = tbl_salary_hikes.proposed_by', 'left');
        $this->db->join('tbl_users as approver', 'approver.user_id = tbl_salary_hikes.approved_by', 'left');
        $this->db->join('tbl_users as rejecter', 'rejecter.user_id = tbl_salary_hikes.rejected_by', 'left');
        $this->db->order_by('tbl_salary_hikes.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function getHikeById($hike_id)
    {
        $this->db->select('tbl_salary_hikes.*,
            tbl_employee.employee_name,
            tbl_employee.employee_email,
            tbl_employee.employee_salary,
            tbl_department.department_name,
            proposer.username as proposed_by_name,
            approver.username as approved_by_name,
            rejecter.username as rejected_by_name');
        $this->db->from('tbl_salary_hikes');
        $this->db->join('tbl_employee', 'tbl_employee.employee_id = tbl_salary_hikes.employee_id');
        $this->db->join('tbl_department', 'tbl_department.department_id = tbl_employee.department_id', 'left');
        $this->db->join('tbl_users as proposer', 'proposer.user_id = tbl_salary_hikes.proposed_by', 'left');
        $this->db->join('tbl_users as approver', 'approver.user_id = tbl_salary_hikes.approved_by', 'left');
        $this->db->join('tbl_users as rejecter', 'rejecter.user_id = tbl_salary_hikes.rejected_by', 'left');
        $this->db->where('tbl_salary_hikes.hike_id', $hike_id);
        return $this->db->get()->row();
    }

    public function insertHike($data)
    {
        return $this->db->insert('tbl_salary_hikes', $data);
    }

    public function updateHikeStatus($hike_id, $status, $decided_by)
    {
        $this->db->where('hike_id', $hike_id);
        return $this->db->update('tbl_salary_hikes', array(
            'status'     => $status,
            'decided_by' => $decided_by
        ));
    }

    public function approveHike($hike_id, $approved_by)
    {
        $this->db->where('hike_id', $hike_id);
        return $this->db->update('tbl_salary_hikes', array(
            'status'       => 'Approved',
            'approved_by'  => $approved_by,
            'approved_at'  => date('Y-m-d H:i:s'),
            'decided_by'   => $approved_by,
            'rejected_by'  => NULL,
            'rejected_at'  => NULL,
            'rejection_reason' => NULL
        ));
    }

    public function rejectHike($hike_id, $rejected_by, $reason)
    {
        $this->db->where('hike_id', $hike_id);
        return $this->db->update('tbl_salary_hikes', array(
            'status'           => 'Rejected',
            'rejected_by'      => $rejected_by,
            'rejected_at'      => date('Y-m-d H:i:s'),
            'rejection_reason' => $reason,
            'decided_by'       => $rejected_by,
            'approved_by'      => NULL,
            'approved_at'      => NULL
        ));
    }

    public function updateEmployeeSalary($employee_id, $new_salary)
    {
        $this->db->where('employee_id', $employee_id);
        return $this->db->update('tbl_employee', array(
            'employee_salary' => $new_salary,
            'updated_at'      => date('Y-m-d H:i:s')
        ));
    }

    public function getEmployeesForHike()
    {
        $this->db->select('tbl_employee.employee_id, tbl_employee.employee_name, tbl_employee.employee_salary, tbl_department.department_name');
        $this->db->from('tbl_employee');
        $this->db->join('tbl_department', 'tbl_department.department_id = tbl_employee.department_id', 'left');
        $this->db->where('tbl_employee.deleted_at', NULL);
        $this->db->where('tbl_employee.status', 'Active');
        $this->db->order_by('tbl_employee.employee_name', 'ASC');
        return $this->db->get()->result();
    }

    public function getPendingHikeForEmployee($employee_id)
    {
        $this->db->where('employee_id', $employee_id);
        $this->db->where('status', 'Pending');
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(1);
        return $this->db->get('tbl_salary_hikes')->row();
    }

    public function hasPendingHike($employee_id)
    {
        $this->db->where('employee_id', $employee_id);
        $this->db->where('status', 'Pending');
        return $this->db->count_all_results('tbl_salary_hikes') > 0;
    }

    public function getHikeStats()
    {
        $current_year = date('Y');

        $this->db->select("
            COUNT(*) as total_hikes,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'Approved' AND YEAR(approved_at) = {$current_year} THEN 1 ELSE 0 END) as approved_this_year,
            SUM(CASE WHEN status = 'Rejected' AND YEAR(rejected_at) = {$current_year} THEN 1 ELSE 0 END) as rejected_this_year,
            AVG(CASE WHEN status = 'Approved' THEN hike_percentage ELSE NULL END) as avg_approved_hike_pct,
            SUM(CASE WHEN status = 'Approved' THEN hike_amount ELSE 0 END) as total_salary_increase
        ");
        return $this->db->get('tbl_salary_hikes')->row();
    }

    public function getAdminHikeStats()
    {
        $current_year = date('Y');

        $this->db->select("
            COUNT(*) as total_hikes,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_proposals,
            SUM(CASE WHEN status = 'Approved' AND YEAR(approved_at) = {$current_year} THEN 1 ELSE 0 END) as approved_this_year,
            SUM(CASE WHEN status = 'Rejected' AND YEAR(rejected_at) = {$current_year} THEN 1 ELSE 0 END) as rejected_this_year,
            AVG(CASE WHEN status = 'Approved' THEN hike_percentage ELSE NULL END) as avg_hike_pct,
            SUM(CASE WHEN status = 'Approved' THEN hike_amount ELSE 0 END) as total_salary_increase
        ");
        return $this->db->get('tbl_salary_hikes')->row();
    }

    public function getEmployeeSalaryDetails($employee_id)
    {
        $this->db->select('
            tbl_employee.employee_id,
            tbl_employee.employee_name,
            tbl_employee.employee_email,
            tbl_employee.employee_phone,
            tbl_employee.employee_salary,
            tbl_employee.status,
            tbl_employee.created_at as joining_date,
            tbl_department.department_name
        ');
        $this->db->from('tbl_employee');
        $this->db->join('tbl_department', 'tbl_department.department_id = tbl_employee.department_id', 'left');
        $this->db->where('tbl_employee.employee_id', $employee_id);
        $this->db->where('tbl_employee.deleted_at', NULL);
        return $this->db->get()->row();
    }

    public function getLatestHikeForEmployee($employee_id)
    {
        $this->db->select('
            tbl_salary_hikes.hike_id,
            tbl_salary_hikes.hike_percentage,
            tbl_salary_hikes.hike_amount,
            tbl_salary_hikes.proposed_salary,
            tbl_salary_hikes.current_salary,
            tbl_salary_hikes.status,
            tbl_salary_hikes.created_at as hike_date,
            tbl_salary_hikes.approved_at,
            tbl_salary_hikes.effective_date
        ');
        $this->db->from('tbl_salary_hikes');
        $this->db->where('tbl_salary_hikes.employee_id', $employee_id);
        $this->db->where('tbl_salary_hikes.status', 'Approved');
        $this->db->order_by('tbl_salary_hikes.approved_at', 'DESC');
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    public function getEmployeeHikeHistory($employee_id)
    {
        $this->db->select('tbl_salary_hikes.*,
            proposer.username as proposed_by_name,
            approver.username as approved_by_name,
            rejecter.username as rejected_by_name');
        $this->db->from('tbl_salary_hikes');
        $this->db->join('tbl_users as proposer', 'proposer.user_id = tbl_salary_hikes.proposed_by', 'left');
        $this->db->join('tbl_users as approver', 'approver.user_id = tbl_salary_hikes.approved_by', 'left');
        $this->db->join('tbl_users as rejecter', 'rejecter.user_id = tbl_salary_hikes.rejected_by', 'left');
        $this->db->where('tbl_salary_hikes.employee_id', $employee_id);
        $this->db->order_by('tbl_salary_hikes.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function getPendingHikes()
    {
        $this->db->select('tbl_salary_hikes.*,
            tbl_employee.employee_name,
            tbl_department.department_name,
            proposer.username as proposed_by_name');
        $this->db->from('tbl_salary_hikes');
        $this->db->join('tbl_employee', 'tbl_employee.employee_id = tbl_salary_hikes.employee_id');
        $this->db->join('tbl_department', 'tbl_department.department_id = tbl_employee.department_id', 'left');
        $this->db->join('tbl_users as proposer', 'proposer.user_id = tbl_salary_hikes.proposed_by', 'left');
        $this->db->where('tbl_salary_hikes.status', 'Pending');
        $this->db->order_by('tbl_salary_hikes.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function getRecentApprovedHikes($limit = 10)
    {
        $this->db->select('tbl_salary_hikes.*,
            tbl_employee.employee_name,
            tbl_department.department_name,
            proposer.username as proposed_by_name,
            approver.username as approved_by_name');
        $this->db->from('tbl_salary_hikes');
        $this->db->join('tbl_employee', 'tbl_employee.employee_id = tbl_salary_hikes.employee_id');
        $this->db->join('tbl_department', 'tbl_department.department_id = tbl_employee.department_id', 'left');
        $this->db->join('tbl_users as proposer', 'proposer.user_id = tbl_salary_hikes.proposed_by', 'left');
        $this->db->join('tbl_users as approver', 'approver.user_id = tbl_salary_hikes.approved_by', 'left');
        $this->db->where('tbl_salary_hikes.status', 'Approved');
        $this->db->order_by('tbl_salary_hikes.approved_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function getApprovedHikesThisYear()
    {
        $current_year = date('Y');
        $this->db->select('tbl_salary_hikes.*,
            tbl_employee.employee_name,
            tbl_employee.employee_salary,
            tbl_department.department_name,
            proposer.username as proposed_by_name,
            approver.username as approved_by_name');
        $this->db->from('tbl_salary_hikes');
        $this->db->join('tbl_employee', 'tbl_employee.employee_id = tbl_salary_hikes.employee_id');
        $this->db->join('tbl_department', 'tbl_department.department_id = tbl_employee.department_id', 'left');
        $this->db->join('tbl_users as proposer', 'proposer.user_id = tbl_salary_hikes.proposed_by', 'left');
        $this->db->join('tbl_users as approver', 'approver.user_id = tbl_salary_hikes.approved_by', 'left');
        $this->db->where('tbl_salary_hikes.status', 'Approved');
        $this->db->where('YEAR(tbl_salary_hikes.approved_at)', $current_year);
        $this->db->order_by('tbl_salary_hikes.approved_at', 'DESC');
        return $this->db->get()->result();
    }

    public function getRejectedHikesThisYear()
    {
        $current_year = date('Y');
        $this->db->select('tbl_salary_hikes.*,
            tbl_employee.employee_name,
            tbl_employee.employee_salary,
            tbl_department.department_name,
            proposer.username as proposed_by_name,
            rejecter.username as rejected_by_name');
        $this->db->from('tbl_salary_hikes');
        $this->db->join('tbl_employee', 'tbl_employee.employee_id = tbl_salary_hikes.employee_id');
        $this->db->join('tbl_department', 'tbl_department.department_id = tbl_employee.department_id', 'left');
        $this->db->join('tbl_users as proposer', 'proposer.user_id = tbl_salary_hikes.proposed_by', 'left');
        $this->db->join('tbl_users as rejecter', 'rejecter.user_id = tbl_salary_hikes.rejected_by', 'left');
        $this->db->where('tbl_salary_hikes.status', 'Rejected');
        $this->db->where('YEAR(tbl_salary_hikes.rejected_at)', $current_year);
        $this->db->order_by('tbl_salary_hikes.rejected_at', 'DESC');
        return $this->db->get()->result();
    }
}
