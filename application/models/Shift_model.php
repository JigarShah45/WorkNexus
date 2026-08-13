<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shift_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    public function getShifts()
    {
        return $this->db->get('tbl_shifts')->result();
    }

    public function getShiftById($id)
    {
        return $this->db->where('shift_id', $id)->get('tbl_shifts')->row();
    }

    public function getAllEmployeeShifts()
    {
        $this->db->select('tbl_employee_shifts.*, tbl_employee.employee_name, tbl_shifts.shift_name, tbl_shifts.start_time, tbl_shifts.end_time');
        $this->db->from('tbl_employee_shifts');
        $this->db->join('tbl_employee', 'tbl_employee.employee_id = tbl_employee_shifts.employee_id');
        $this->db->join('tbl_shifts', 'tbl_shifts.shift_id = tbl_employee_shifts.shift_id');
        $this->db->where('tbl_employee_shifts.effective_from <=', date('Y-m-d'));
        $this->db->group_start();
        $this->db->where('tbl_employee_shifts.effective_to IS NULL', NULL, FALSE);
        $this->db->or_where('tbl_employee_shifts.effective_to >=', date('Y-m-d'));
        $this->db->group_end();
        $this->db->order_by('tbl_employee.employee_name', 'ASC');
        return $this->db->get()->result();
    }

    public function assignShift($employee_id, $shift_id, $effective_from, $effective_to = NULL)
    {
        // Deactivate previous assignments for this employee
        $this->db->where('employee_id', $employee_id);
        $this->db->where('effective_to IS NULL', NULL, FALSE);
        $this->db->update('tbl_employee_shifts', array('effective_to' => date('Y-m-d', strtotime('-1 day'))));

        $data = array(
            'employee_id'   => $employee_id,
            'shift_id'      => $shift_id,
            'effective_from' => $effective_from,
            'effective_to'   => $effective_to
        );
        return $this->db->insert('tbl_employee_shifts', $data);
    }
}
