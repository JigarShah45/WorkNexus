<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->config->load('attendance');
        $tz = $this->config->item('attendance_timezone');
        if ($tz) date_default_timezone_set($tz);
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
        $today = date('Y-m-d');

        $sql = "SELECT COUNT(DISTINCT a.employee_id) AS present
                FROM tbl_attendance a
                INNER JOIN tbl_employee e ON e.employee_id = a.employee_id
                WHERE a.attendance_date = ?
                  AND a.status IN ('Present', 'Half-Day')
                  AND e.deleted_at IS NULL
                  AND e.status = 'Active'
                  AND NOT EXISTS (
                      SELECT 1
                      FROM tbl_leave_requests lr
                      WHERE lr.employee_id = a.employee_id
                        AND lr.from_date <= ?
                        AND lr.to_date >= ?
                        AND lr.status = 'Approved'
                  )";

        $row = $this->db->query($sql, array($today, $today, $today))->row();

        return $row ? (int) $row->present : 0;
    }

    /**
     * Get number of employees on leave today
     */
    public function getOnLeaveToday()
    {
        $today = date('Y-m-d');
        $this->db->select('COUNT(DISTINCT tbl_leave_requests.employee_id) AS on_leave');
        $this->db->from('tbl_leave_requests');
        $this->db->join('tbl_employee', 'tbl_employee.employee_id = tbl_leave_requests.employee_id');
        $this->db->where('tbl_leave_requests.from_date <=', $today);
        $this->db->where('tbl_leave_requests.to_date >=', $today);
        $this->db->where('tbl_leave_requests.status', 'Approved');
        $this->db->where('tbl_employee.deleted_at', NULL);
        $this->db->where('tbl_employee.status', 'Active');

        $row = $this->db->get()->row();

        return $row ? (int) $row->on_leave : 0;
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
     * Attendance exceptions for today, computed in a single aggregated query.
     *
     * The grace threshold (11:15 AM IST) is read from config/attendance.php so
     * this consumes the exact same business rules as the Attendance module
     * instead of redefining them:
     *   - late_arrivals       : first login/clock-in after 11:15 AM IST
     *                           (the stored clock_in is the effective clock-in,
     *                           so clock_in > 11:15:00 is equivalent)
     *   - half_day            : today's records with status = 'Half-Day'
     *   - currently_working   : clocked in but no clock-out and not yet
     *                           auto-closed at the 7:00 PM shift end
     *   - overtime_employees  : today's records with overtime_hours > 0
     *   - overtime_total_hours: total overtime hours today
     */
    public function getAttendanceExceptionsToday()
    {
        $grace = $this->config->item('attendance_grace_end');
        if (empty($grace))
        {
            $grace = '11:15:00';
        }

        $today = date('Y-m-d');

        $sql = "SELECT
            COUNT(CASE WHEN TIME(clock_in) > ? THEN 1 END) AS late_arrivals,
            COUNT(CASE WHEN status = 'Half-Day' THEN 1 END) AS half_day,
            COUNT(CASE WHEN clock_in IS NOT NULL AND clock_out IS NULL AND auto_closed = 0 THEN 1 END) AS currently_working,
            COUNT(CASE WHEN overtime_hours > 0 THEN 1 END) AS overtime_employees,
            COALESCE(SUM(overtime_hours), 0) AS overtime_total_hours
        FROM tbl_attendance
        WHERE attendance_date = ?";

        $row = $this->db->query($sql, array($grace, $today))->row();

        if (!$row)
        {
            return (object) array(
                'late_arrivals'        => 0,
                'half_day'             => 0,
                'currently_working'    => 0,
                'overtime_employees'   => 0,
                'overtime_total_hours' => 0
            );
        }

        return $row;
    }

    /**
     * Next scheduled client meetings (future only), soonest first.
     *
     * The current schema has no meeting status column - a meeting counts as
     * "scheduled/upcoming" simply by existing with a future meeting_date.
     * This matches the logic already used by Meetings::my_meetings().
     */
    public function getUpcomingMeetings($limit = 4)
    {
        $this->db->select('meeting_id, client_name, meeting_title, meeting_date, meeting_location');
        $this->db->from('tbl_client_meetings');
        $this->db->where('meeting_date >=', date('Y-m-d H:i:s'));
        $this->db->order_by('meeting_date', 'ASC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    /**
     * Latest pending salary hike proposals (status = 'Pending'), newest first.
     */
    public function getPendingSalaryHikes($limit = 3)
    {
        $this->db->select('
            tbl_salary_hikes.hike_id,
            tbl_salary_hikes.hike_percentage,
            tbl_salary_hikes.proposed_salary,
            tbl_salary_hikes.proposed_at,
            tbl_employee.employee_name,
            tbl_department.department_name
        ');
        $this->db->from('tbl_salary_hikes');
        $this->db->join('tbl_employee', 'tbl_employee.employee_id = tbl_salary_hikes.employee_id');
        $this->db->join('tbl_department', 'tbl_department.department_id = tbl_employee.department_id', 'left');
        $this->db->where('tbl_salary_hikes.status', 'Pending');
        $this->db->order_by('tbl_salary_hikes.created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    /**
     * Total number of pending salary hike proposals.
     */
    public function getPendingHikesCount()
    {
        $this->db->where('status', 'Pending');
        return $this->db->count_all_results('tbl_salary_hikes');
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