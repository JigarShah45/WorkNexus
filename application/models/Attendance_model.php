<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance_model extends CI_Model {

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

    public function getEmployeeShifts($employee_id)
    {
        $this->db->select('tbl_employee_shifts.*, tbl_shifts.shift_name, tbl_shifts.start_time, tbl_shifts.end_time');
        $this->db->from('tbl_employee_shifts');
        $this->db->join('tbl_shifts', 'tbl_shifts.shift_id = tbl_employee_shifts.shift_id');
        $this->db->where('tbl_employee_shifts.employee_id', $employee_id);
        $this->db->where('tbl_employee_shifts.effective_from <=', date('Y-m-d'));
        $this->db->group_start();
        $this->db->where('tbl_employee_shifts.effective_to IS NULL', NULL, FALSE);
        $this->db->or_where('tbl_employee_shifts.effective_to >=', date('Y-m-d'));
        $this->db->group_end();
        return $this->db->get()->result();
    }

    public function assignShift($employee_id, $shift_id, $effective_from, $effective_to = NULL)
    {
        // Deactivate previous assignments
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

    /**
     * Auto clock-in on login
     */
    public function autoClockIn($employee_id)
    {
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');

        // Check if already clocked in today
        $existing = $this->db->where('employee_id', $employee_id)
            ->where('attendance_date', $today)
            ->get('tbl_attendance')
            ->row();

        if ($existing)
        {
            return $existing->attendance_id;
        }

        // Get primary shift
        $shift = $this->db->where('employee_id', $employee_id)
            ->where('effective_from <=', $today)
            ->group_start()
            ->where('effective_to IS NULL', NULL, FALSE)
            ->or_where('effective_to >=', $today)
            ->group_end()
            ->order_by('effective_from', 'DESC')
            ->limit(1)
            ->get('tbl_employee_shifts')
            ->row();

        $shift_id = $shift ? $shift->shift_id : 1;

        $data = array(
            'employee_id'     => $employee_id,
            'attendance_date' => $today,
            'clock_in'        => $now,
            'shift_id'        => $shift_id,
            'status'          => 'Present'
        );

        $this->db->insert('tbl_attendance', $data);
        return $this->db->insert_id();
    }

    /**
     * Auto clock-out on logout
     */
    public function autoClockOut($employee_id)
    {
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');

        $record = $this->db->where('employee_id', $employee_id)
            ->where('attendance_date', $today)
            ->where('clock_out IS NULL', NULL, FALSE)
            ->get('tbl_attendance')
            ->row();

        if (!$record)
        {
            return FALSE;
        }

        $clock_in = strtotime($record->clock_in);
        $clock_out = strtotime($now);
        $hours_worked = round(($clock_out - $clock_in) / 3600, 2);

        // Get shift end time to calculate overtime
        $shift = $this->getShiftById($record->shift_id);
        $overtime = 0;

        if ($shift)
        {
            $shift_end = strtotime($today . ' ' . $shift->end_time);
            if ($clock_out > $shift_end)
            {
                $overtime = round(($clock_out - $shift_end) / 3600, 2);
            }
        }

        $status = 'Present';
        if ($hours_worked < 4)
        {
            $status = 'Half-Day';
        }

        $this->db->where('attendance_id', $record->attendance_id);
        $this->db->update('tbl_attendance', array(
            'clock_out'     => $now,
            'hours_worked'  => $hours_worked,
            'overtime_hours' => $overtime,
            'status'        => $status
        ));

        return $record->attendance_id;
    }

    /**
     * Get attendance for an employee
     */
    public function getEmployeeAttendance($employee_id, $month = '', $year = '')
    {
        if (empty($month)) $month = date('m');
        if (empty($year)) $year = date('Y');

        $this->db->select('tbl_attendance.*, tbl_shifts.shift_name');
        $this->db->from('tbl_attendance');
        $this->db->join('tbl_shifts', 'tbl_shifts.shift_id = tbl_attendance.shift_id', 'left');
        $this->db->where('tbl_attendance.employee_id', $employee_id);
        $this->db->where('MONTH(tbl_attendance.attendance_date)', $month);
        $this->db->where('YEAR(tbl_attendance.attendance_date)', $year);
        $this->db->order_by('tbl_attendance.attendance_date', 'DESC');

        return $this->db->get()->result();
    }

    /**
     * Get all attendance for HR view
     */
    public function getAllAttendance($month = '', $year = '', $department_id = '')
    {
        if (empty($month)) $month = date('m');
        if (empty($year)) $year = date('Y');

        $this->db->select('tbl_attendance.*, tbl_employee.employee_name, tbl_shifts.shift_name, tbl_department.department_name');
        $this->db->from('tbl_attendance');
        $this->db->join('tbl_employee', 'tbl_employee.employee_id = tbl_attendance.employee_id');
        $this->db->join('tbl_shifts', 'tbl_shifts.shift_id = tbl_attendance.shift_id', 'left');
        $this->db->join('tbl_department', 'tbl_department.department_id = tbl_employee.department_id', 'left');
        $this->db->where('MONTH(tbl_attendance.attendance_date)', $month);
        $this->db->where('YEAR(tbl_attendance.attendance_date)', $year);
        $this->db->where('tbl_employee.deleted_at', NULL);

        if (!empty($department_id))
        {
            $this->db->where('tbl_employee.department_id', $department_id);
        }

        $this->db->order_by('tbl_attendance.attendance_date', 'DESC');

        return $this->db->get()->result();
    }

    /**
     * Calculate attendance stats for hike page
     */
    public function getAttendanceStats($employee_id, $from_date = '', $to_date = '')
    {
        if (empty($from_date)) $from_date = date('Y-01-01');
        if (empty($to_date)) $to_date = date('Y-m-d');

        $this->db->select('
            COUNT(*) as total_days,
            SUM(CASE WHEN status = "Present" THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN status = "Absent" THEN 1 ELSE 0 END) as absent_days,
            SUM(CASE WHEN status = "Half-Day" THEN 1 ELSE 0 END) as half_day_days,
            SUM(overtime_hours) as total_overtime,
            SUM(hours_worked) as total_hours
        ');
        $this->db->from('tbl_attendance');
        $this->db->where('employee_id', $employee_id);
        $this->db->where('attendance_date >=', $from_date);
        $this->db->where('attendance_date <=', $to_date);

        return $this->db->get()->row();
    }

    /**
     * Get attendance summary for all employees
     */
    public function getAttendanceSummary($from_date = '', $to_date = '')
    {
        if (empty($from_date)) $from_date = date('Y-01-01');
        if (empty($to_date)) $to_date = date('Y-m-d');

        $this->db->select('
            tbl_employee.employee_id,
            tbl_employee.employee_name,
            tbl_employee.employee_salary,
            COUNT(tbl_attendance.attendance_id) as total_days,
            SUM(CASE WHEN tbl_attendance.status = "Present" THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN tbl_attendance.status = "Absent" THEN 1 ELSE 0 END) as absent_days,
            SUM(tbl_attendance.overtime_hours) as total_overtime
        ');
        $this->db->from('tbl_employee');
        $this->db->join('tbl_attendance', 'tbl_attendance.employee_id = tbl_employee.employee_id', 'left');
        $this->db->where('tbl_employee.deleted_at', NULL);
        $this->db->where('tbl_attendance.attendance_date >=', $from_date);
        $this->db->where('tbl_attendance.attendance_date <=', $to_date);
        $this->db->group_by('tbl_employee.employee_id');

        return $this->db->get()->result();
    }
}
