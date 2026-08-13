<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEmployeesByDepartment()
    {
        $this->db->select('
            tbl_department.department_name,
            COUNT(tbl_employee.employee_id) AS total
        ');

        $this->db->from('tbl_department');

        $this->db->join(
            'tbl_employee',
            'tbl_employee.department_id = tbl_department.department_id
            AND tbl_employee.deleted_at IS NULL',
            'left'
        );

        $this->db->where('tbl_department.deleted_at', NULL);

        $this->db->group_by('tbl_department.department_id');
        $this->db->having('COUNT(tbl_employee.employee_id) >', 0);

        return $this->db->get()->result();
    }
    public function getEmployeeStatus()
    {
        $this->db->select('status, COUNT(*) as total');

        $this->db->from('tbl_employee');

        $this->db->where('deleted_at', NULL);

        $this->db->group_by('status');

        return $this->db->get()->result();
    }

    /*
    |--------------------------------------------------------------------------
    | HR Dashboard Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get number of employees present today
     */
    public function getPresentToday()
    {
        $this->db->where('attendance_date', date('Y-m-d'));
        $this->db->where('status', 'Present');
        return $this->db->count_all_results('tbl_attendance');
    }

    /**
     * Get number of employees on leave today
     */
    public function getOnLeaveToday()
    {
        $today = date('Y-m-d');
        $this->db->where('from_date <=', $today);
        $this->db->where('to_date >=', $today);
        $this->db->where('status', 'Approved');
        return $this->db->count_all_results('tbl_leave_requests');
    }

    /**
     * Get number of employees absent today
     */
    public function getAbsentToday()
    {
        $this->db->where('attendance_date', date('Y-m-d'));
        $this->db->where('status', 'Absent');
        return $this->db->count_all_results('tbl_attendance');
    }

    /**
     * Get number of pending leave requests
     */
    public function getPendingLeaveCount()
    {
        $this->db->where('status', 'Pending');
        return $this->db->count_all_results('tbl_leave_requests');
    }

    /**
     * Get recent leave requests for HR dashboard
     */
    public function getRecentLeaveRequests($limit = 5)
    {
        $this->db->select('
            tbl_leave_requests.*,
            tbl_employee.employee_name
        ');
        $this->db->from('tbl_leave_requests');
        $this->db->join('tbl_employee', 'tbl_employee.employee_id = tbl_leave_requests.employee_id', 'left');
        $this->db->order_by('tbl_leave_requests.created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    /**
     * Get today's attendance overview (Present, Absent, Late, Half-Day counts)
     */
    public function getTodayAttendanceOverview()
    {
        $this->db->select('
            status,
            COUNT(*) as total
        ');
        $this->db->from('tbl_attendance');
        $this->db->where('attendance_date', date('Y-m-d'));
        $this->db->group_by('status');
        return $this->db->get()->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Employee Dashboard Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get employee profile with department info
     */
    public function getEmployeeProfile($employee_id)
    {
        $this->db->select('
            tbl_employee.employee_id,
            tbl_employee.employee_name,
            tbl_employee.employee_email,
            tbl_employee.employee_phone,
            tbl_employee.employee_salary,
            tbl_employee.status,
            tbl_employee.profile_image,
            tbl_employee.created_at as joining_date,
            tbl_department.department_name
        ');
        $this->db->from('tbl_employee');
        $this->db->join('tbl_department', 'tbl_department.department_id = tbl_employee.department_id', 'left');
        $this->db->where('tbl_employee.employee_id', $employee_id);
        $this->db->where('tbl_employee.deleted_at', NULL);
        return $this->db->get()->row();
    }

    /**
     * Get attendance summary for current month
     */
    public function getEmployeeAttendanceSummary($employee_id)
    {
        $month = date('m');
        $year = date('Y');

        $this->db->select('
            COUNT(*) as total_days,
            SUM(CASE WHEN status = "Present" THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN status = "Absent" THEN 1 ELSE 0 END) as absent_days,
            SUM(CASE WHEN status = "Half-Day" THEN 1 ELSE 0 END) as half_day_days,
            SUM(hours_worked) as total_hours,
            SUM(overtime_hours) as total_overtime
        ');
        $this->db->from('tbl_attendance');
        $this->db->where('employee_id', $employee_id);
        $this->db->where('MONTH(attendance_date)', $month);
        $this->db->where('YEAR(attendance_date)', $year);

        $result = $this->db->get()->row();

        if (!$result || $result->total_days == 0)
        {
            return (object) array(
                'total_days' => 0,
                'present_days' => 0,
                'absent_days' => 0,
                'half_day_days' => 0,
                'total_hours' => 0,
                'total_overtime' => 0,
                'attendance_rate' => 0
            );
        }

        $result->attendance_rate = $result->total_days > 0
            ? round(($result->present_days / $result->total_days) * 100, 1)
            : 0;

        return $result;
    }

    /**
     * Get leave summary for current year
     */
    public function getEmployeeLeaveSummary($employee_id)
    {
        $year = date('Y');

        $this->db->select('
            COUNT(*) as total_requests,
            SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = "Approved" THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = "Rejected" THEN 1 ELSE 0 END) as rejected
        ');
        $this->db->from('tbl_leave_requests');
        $this->db->where('employee_id', $employee_id);
        $this->db->where('YEAR(created_at)', $year);

        $result = $this->db->get()->row();

        if (!$result)
        {
            return (object) array(
                'total_requests' => 0,
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0
            );
        }

        return $result;
    }

    /**
     * Get salary summary for employee
     */
    public function getEmployeeSalarySummary($employee_id)
    {
        $this->db->select('employee_salary');
        $this->db->from('tbl_employee');
        $this->db->where('employee_id', $employee_id);
        $this->db->where('deleted_at', NULL);
        $employee = $this->db->get()->row();

        $this->db->select('
            hike_percentage,
            proposed_salary,
            status,
            created_at as hike_date
        ');
        $this->db->from('tbl_salary_hikes');
        $this->db->where('employee_id', $employee_id);
        $this->db->where('status', 'Approved');
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(1);
        $latest_hike = $this->db->get()->row();

        return (object) array(
            'current_salary' => ($latest_hike && $latest_hike->proposed_salary > 0) ? $latest_hike->proposed_salary : ($employee ? $employee->employee_salary : 0),
            'latest_hike' => $latest_hike
        );
    }

    /**
     * Get recent activity (recent leave requests and attendance)
     */
    public function getEmployeeRecentActivity($employee_id)
    {
        $this->db->select('
            "leave" as activity_type,
            leave_type as description,
            from_date as activity_date,
            status as activity_status,
            created_at
        ');
        $this->db->from('tbl_leave_requests');
        $this->db->where('employee_id', $employee_id);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(5);
        $leaves = $this->db->get()->result();

        return $leaves;
    }
}