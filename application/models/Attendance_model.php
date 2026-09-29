<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance_model extends CI_Model {

    private $_timezone = 'Asia/Kolkata';
    private $_auto_closed_column = NULL;

    public function __construct()
    {
        parent::__construct();
        $this->config->load('attendance');
        $tz = $this->config->item('attendance_timezone');
        if ($tz) $this->_timezone = $tz;
        date_default_timezone_set($this->_timezone);
    }

    /* =====================================================================
     *  OFFICIAL SHIFT CONFIGURATION
     *  All times are IST (Asia/Kolkata). Values come from config/attendance.php.
     * =================================================================== */

    private function _cfg($key, $default)
    {
        $value = $this->config->item($key);
        return ($value === NULL) ? $default : $value;
    }

    private function _shiftStart()        { return $this->_cfg('attendance_shift_start', '11:00:00'); }
    private function _shiftEnd()          { return $this->_cfg('attendance_shift_end', '19:00:00'); }
    private function _graceEnd()          { return $this->_cfg('attendance_grace_end', '11:15:00'); }
    private function _validLogoutFrom()   { return $this->_cfg('attendance_valid_logout_from', '18:30:00'); }

    /* =====================================================================
     *  TIME HELPERS (always Asia/Kolkata)
     * =================================================================== */

    private function _tz()
    {
        return new DateTimeZone($this->_timezone);
    }

    private function _now($format = 'Y-m-d H:i:s')
    {
        return (new DateTime('now', $this->_tz()))->format($format);
    }

    private function _today()
    {
        return $this->_now('Y-m-d');
    }

    private function _dt($datetime)
    {
        return new DateTime($datetime, $this->_tz());
    }

    private function _buildDate($date, $time)
    {
        return new DateTime($date . ' ' . $time, $this->_tz());
    }

    private function _timeOfDay($datetime)
    {
        return $this->_dt($datetime)->format('H:i:s');
    }

    private function _hasAutoClosedColumn()
    {
        if ($this->_auto_closed_column === NULL)
        {
            $q = $this->db->query("SHOW COLUMNS FROM tbl_attendance LIKE 'auto_closed'");
            $this->_auto_closed_column = ($q && $q->num_rows() > 0);
        }
        return $this->_auto_closed_column;
    }

    /* =====================================================================
     *  ATTENDANCE BUSINESS LOGIC
     * =================================================================== */

    /**
     * Only ACTIVE users are eligible for automatic attendance.
     * All roles (Employee, HR, Admin, Manager) are treated consistently.
     * Inactive or deleted employee accounts never get auto-created
     * attendance records.
     */
    private function _attendanceEligible($employee_id)
    {
        if (!$employee_id)
        {
            return FALSE;
        }

        $this->db->select('tbl_employee.status AS employee_status');
        $this->db->from('tbl_employee');
        $this->db->where('tbl_employee.employee_id', $employee_id);
        $this->db->where('tbl_employee.deleted_at', NULL);
        $this->db->limit(1);

        $row = $this->db->get()->row();

        if (!$row || $row->employee_status !== 'Active')
        {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Effective clock-in for a raw login time.
     * Logging in before the official shift start (11:00 AM) counts as 11:00 AM,
     * so early logins never produce extra working hours.
     */
    private function _effectiveClockIn($raw_login)
    {
        $login = $this->_dt($raw_login);
        $shift_start = $this->_buildDate($login->format('Y-m-d'), $this->_shiftStart());

        if ($login < $shift_start)
        {
            return $shift_start->format('Y-m-d H:i:s');
        }

        return $raw_login;
    }

    /**
     * Attendance status is decided purely by the login time (grace period).
     * Login after 11:15 AM => Half-Day, regardless of how many hours are worked.
     */
    private function _loginStatus($clock_in)
    {
        $in = $this->_dt($clock_in);
        $cutoff = $this->_buildDate($in->format('Y-m-d'), $this->_graceEnd());

        return ($in > $cutoff) ? 'Half-Day' : 'Present';
    }

    /**
     * Compute hours worked and overtime for a single attendance record,
     * returning formatted display strings and raw decimal values.
     *
     * Rules:
     *  - No clock_in  => everything is NULL/dash
     *  - clock_in only, today, before 7 PM => live elapsed = current_time - clock_in
     *  - clock_in only, today, at/after 7 PM => 7 PM - clock_in
     *  - clock_in + clock_out => actual clock_out - clock_in
     *  - Regular hours capped at 7 PM shift end
     *  - Overtime = actual clock_out - 7 PM (only when real clock_out > 7 PM)
     */
    private function _computeHoursForRecord($row)
    {
        if (empty($row->clock_in))
        {
            $row->hours_worked   = 0;
            $row->overtime_hours = 0;
            $row->hours_display  = '-';
            $row->overtime_display = '-';
            return $row;
        }

        $start = $this->_dt($row->clock_in);
        $shift_end = $this->_buildDate($row->attendance_date, $this->_shiftEnd());
        $today = $this->_today();
        $now_dt = new DateTime('now', $this->_tz());

        $has_actual_clockout = !empty($row->clock_out);

        if ($has_actual_clockout)
        {
            $effective_out = $this->_dt($row->clock_out);
        }
        elseif ($row->attendance_date === $today && $now_dt < $shift_end)
        {
            $effective_out = $now_dt;
        }
        else
        {
            $effective_out = $shift_end;
        }

        $regular_end = ($effective_out < $shift_end) ? $effective_out : $shift_end;

        $working_seconds = 0;
        if ($regular_end > $start)
        {
            $working_seconds = $regular_end->getTimestamp() - $start->getTimestamp();
        }
        $hours_worked = round($working_seconds / 3600, 2);

        $overtime_seconds = 0;
        if ($has_actual_clockout)
        {
            $actual_out = $this->_dt($row->clock_out);
            if ($actual_out > $shift_end)
            {
                $overtime_seconds = $actual_out->getTimestamp() - $shift_end->getTimestamp();
            }
        }
        $overtime_hours = round($overtime_seconds / 3600, 2);

        $row->hours_worked   = $hours_worked;
        $row->overtime_hours = $overtime_hours;
        $row->hours_display  = $this->_formatHours($hours_worked);
        $row->overtime_display = ($overtime_hours > 0) ? $this->_formatHours($overtime_hours) : '-';

        return $row;
    }

    /**
     * Format decimal hours into a human-readable "Xh Ym" string.
     */
    private function _formatHours($decimal_hours)
    {
        if ($decimal_hours <= 0)
        {
            return '-';
        }

        $hours = floor($decimal_hours);
        $minutes = round(($decimal_hours - $hours) * 60);

        if ($minutes >= 60)
        {
            $hours += 1;
            $minutes = 0;
        }

        return $hours . 'h ' . str_pad($minutes, 2, '0', STR_PAD_LEFT) . 'm';
    }

    /**
     * Finalize a single attendance record with the official rules:
     *  - Effective Clock In  (already stored / normalized at login)
     *  - Clock Out           (the ACTUAL clock-out; NULL when not clocked out)
     *  - Effective Clock Out (actual clock-out, else 7:00 PM as a DERIVED
     *                         default - the 7:00 PM value is only used for
     *                         calculation and is never written to the DB)
     *  - Hours Worked        Clock In -> Effective Clock Out, capped at 7:00 PM
     *  - Overtime            Actual Clock Out - 7:00 PM (only a real logout
     *                         can prove work beyond 7:00 PM)
     *  - Status              Present / Half-Day based on the login time
     */
    private function _finalizeAttendance($attendance_id, $clock_out, $auto_closed)
    {
        $record = $this->db->where('attendance_id', $attendance_id)->get('tbl_attendance')->row();
        if (!$record || empty($record->clock_in))
        {
            return FALSE;
        }

        // A real logout must never move the clock-out backwards.
        if (!empty($clock_out) && !empty($record->clock_out))
        {
            if ($this->_dt($clock_out) <= $this->_dt($record->clock_out))
            {
                return FALSE;
            }
        }

        $start = $this->_dt($record->clock_in);
        $shift_end = $this->_buildDate($record->attendance_date, $this->_shiftEnd());

        // Derived / effective clock-out: a real logout, else the 7:00 PM shift end.
        $effective_out = !empty($clock_out) ? $this->_dt($clock_out) : $shift_end;

        // Regular working time ends at shift end (7:00 PM). Overtime is separate.
        $regular_end = ($effective_out < $shift_end) ? $effective_out : $shift_end;

        $working_seconds = 0;
        if ($regular_end > $start)
        {
            $working_seconds = $regular_end->getTimestamp() - $start->getTimestamp();
        }
        $hours_worked = round($working_seconds / 3600, 2);

        // Overtime = time actually worked after 7:00 PM. Only a real logout
        // can produce overtime - never an open (no clock-out) record.
        $overtime_seconds = 0;
        if (!empty($clock_out))
        {
            $actual_out = $this->_dt($clock_out);
            if ($actual_out > $shift_end)
            {
                $overtime_seconds = $actual_out->getTimestamp() - $shift_end->getTimestamp();
            }
        }
        $overtime_hours = round($overtime_seconds / 3600, 2);

        $data = array(
            'hours_worked'   => $hours_worked,
            'overtime_hours' => $overtime_hours,
            'status'         => $this->_loginStatus($record->clock_in)
        );

        // Only a REAL clock-out is persisted. When the employee has not
        // clocked out, the column stays NULL and the effective 7:00 PM value
        // is derived for every calculation/display.
        if (!empty($clock_out))
        {
            $data['clock_out'] = $clock_out;
        }

        if ($this->_hasAutoClosedColumn())
        {
            $data['auto_closed'] = $auto_closed ? 1 : 0;
        }

        $this->db->where('attendance_id', $attendance_id);
        $this->db->update('tbl_attendance', $data);

        return TRUE;
    }

    /**
     * Auto clock-in on login (first valid login of the day wins).
     * The stored clock-in is the EFFECTIVE clock-in:
     *  - login before 11:00 AM => 11:00 AM
     *  - login 11:00 - 11:15   => actual login time (Present)
     *  - login after 11:15     => actual login time (Half-Day)
     * Raw LOGIN events remain untouched in tbl_user_logs.
     */
    public function autoClockIn($employee_id)
    {
        if (!$employee_id)
        {
            return FALSE;
        }

        if (!$this->_attendanceEligible($employee_id))
        {
            return FALSE;
        }

        $today = $this->_today();
        $now = $this->_now('Y-m-d H:i:s');

        $existing = $this->db->where('employee_id', $employee_id)
            ->where('attendance_date', $today)
            ->get('tbl_attendance')
            ->row();

        if ($existing)
        {
            // Keep the first valid login of the day. Later logins must not
            // overwrite the clock-in time.
            if (empty($existing->clock_in))
            {
                $this->db->where('attendance_id', $existing->attendance_id);
                $this->db->update('tbl_attendance', array(
                    'clock_in' => $this->_effectiveClockIn($now),
                    'status'   => $this->_loginStatus($now)
                ));
            }
            return $existing->attendance_id;
        }

        $effective_in = $this->_effectiveClockIn($now);

        // Primary shift is stored for display only; calculations always use the
        // official shift timing from config/attendance.php.
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

        // Race-safe insert: if a concurrent login already created today's
        // record, MySQL keeps the existing row (the original clock-in is
        // preserved) and returns its attendance_id instead of raising a
        // duplicate-key error. Sequential logins are caught by the SELECT
        // check above; this protects against simultaneous requests and any
        // database-level unique key.
        $sql = "INSERT INTO tbl_attendance
                (employee_id, attendance_date, clock_in, shift_id, hours_worked, overtime_hours, status)
                VALUES (?, ?, ?, ?, 0, 0, ?)
                ON DUPLICATE KEY UPDATE attendance_id = LAST_INSERT_ID(attendance_id)";

        $this->db->query($sql, array(
            $employee_id,
            $today,
            $effective_in,
            $shift_id,
            $this->_loginStatus($now)
        ));

        return $this->db->insert_id();
    }

    /**
     * Auto clock-out on logout.
     *
     * A logout BEFORE 6:30 PM is a temporary/session logout and is NOT the
     * final attendance clock-out - the record stays open. This also means a
     * lunch-time logout (e.g. 2:30 PM) never closes the attendance record.
     *
     * A logout from 6:30 PM onwards is the valid final clock-out and is used
     * as the attendance Clock Out (overtime is computed if after 7:00 PM).
     *
     * Raw LOGOUT events remain untouched in tbl_user_logs.
     */
    public function autoClockOut($employee_id)
    {
        if (!$employee_id)
        {
            return FALSE;
        }

        if (!$this->_attendanceEligible($employee_id))
        {
            return FALSE;
        }

        $today = $this->_today();
        $now = $this->_now('Y-m-d H:i:s');

        // Session logout - keep the attendance record open.
        if ($this->_timeOfDay($now) < $this->_validLogoutFrom())
        {
            return FALSE;
        }

        $record = $this->db->where('employee_id', $employee_id)
            ->where('attendance_date', $today)
            ->where('clock_out IS NULL', NULL, FALSE)
            ->get('tbl_attendance')
            ->row();

        if (!$record)
        {
            // Today's record already has a REAL clock-out (e.g. an earlier
            // logout today). A later logout may upgrade it, but never
            // backwards (guarded in _finalizeAttendance).
            $closed = $this->db->where('employee_id', $employee_id)
                ->where('attendance_date', $today)
                ->where('clock_out IS NOT NULL', NULL, FALSE)
                ->get('tbl_attendance')
                ->row();

            if (!$closed)
            {
                return FALSE;
            }

            $this->_finalizeAttendance($closed->attendance_id, $now, FALSE);
            return $closed->attendance_id;
        }

        $this->_finalizeAttendance($record->attendance_id, $now, FALSE);
        return $record->attendance_id;
    }

    /**
     * Automatic 7:00 PM closure.
     *
     * Closes every open attendance record whose shift-end (7:00 PM IST on the
     * attendance date) has already passed. Regular hours are computed using the
     * DERIVED effective clock-out of 7:00 PM and the record is marked as
     * auto-closed, but the clock_out column stays NULL - the employee never
     * actually clocked out, so no fake clock-out is persisted. Idempotent -
     * only touches records with clock_out IS NULL.
     *
     * Called from:
     *  - a scheduled task  => php index.php cron auto_close_attendance
     *  - attendance page loads (Attendance::index / Attendance::manage)
     */
    public function autoCloseOpenAttendance()
    {
        $now = new DateTime('now', $this->_tz());

        // Nothing to do before the official shift end.
        if ($this->_timeOfDay($now->format('Y-m-d H:i:s')) < $this->_shiftEnd())
        {
            return 0;
        }

        $today = $this->_today();

        $open = $this->db->where('clock_out IS NULL', NULL, FALSE)
            ->where('attendance_date <=', $today)
            ->order_by('attendance_date', 'ASC')
            ->get('tbl_attendance')
            ->result();

        $closed = 0;

        foreach ($open as $row)
        {
            $shift_end_dt = $this->_buildDate($row->attendance_date, $this->_shiftEnd());
            if ($shift_end_dt > $now)
            {
                continue;
            }

            $this->_finalizeAttendance($row->attendance_id, NULL, TRUE);
            $closed++;
        }

        return $closed;
    }

    /**
     * Get shifts
     */
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
        $this->db->where('tbl_employee_shifts.effective_from <=', $this->_today());
        $this->db->group_start();
        $this->db->where('tbl_employee_shifts.effective_to IS NULL', NULL, FALSE);
        $this->db->or_where('tbl_employee_shifts.effective_to >=', $this->_today());
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

        $results = $this->db->get()->result();

        $today = $this->_today();
        $now_dt = new DateTime('now', $this->_tz());
        $shift_end_dt = $this->_buildDate($today, $this->_shiftEnd());

        foreach ($results as $row)
        {
            $computed = $this->_computeHoursForRecord($row);

            $needs_update = (
                ($computed->hours_worked != $row->hours_worked || $computed->overtime_hours != $row->overtime_hours)
                && !empty($computed->clock_in)
            );

            if ($needs_update)
            {
                $update_data = array(
                    'hours_worked'   => $computed->hours_worked,
                    'overtime_hours' => $computed->overtime_hours
                );

                if (empty($row->clock_out) && $row->attendance_date === $today && $now_dt >= $shift_end_dt)
                {
                    if ($this->_hasAutoClosedColumn())
                    {
                        $update_data['auto_closed'] = 1;
                    }
                }

                $this->db->where('attendance_id', $row->attendance_id);
                $this->db->update('tbl_attendance', $update_data);
            }

            $row->hours_worked    = $computed->hours_worked;
            $row->overtime_hours  = $computed->overtime_hours;
            $row->hours_display   = $computed->hours_display;
            $row->overtime_display = $computed->overtime_display;
        }

        return $results;
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

        $results = $this->db->get()->result();

        $today = $this->_today();
        $now_dt = new DateTime('now', $this->_tz());
        $shift_end_dt = $this->_buildDate($today, $this->_shiftEnd());

        foreach ($results as $row)
        {
            $computed = $this->_computeHoursForRecord($row);

            $needs_update = (
                ($computed->hours_worked != $row->hours_worked || $computed->overtime_hours != $row->overtime_hours)
                && !empty($computed->clock_in)
            );

            if ($needs_update)
            {
                $update_data = array(
                    'hours_worked'   => $computed->hours_worked,
                    'overtime_hours' => $computed->overtime_hours
                );

                if (empty($row->clock_out) && $row->attendance_date === $today && $now_dt >= $shift_end_dt)
                {
                    if ($this->_hasAutoClosedColumn())
                    {
                        $update_data['auto_closed'] = 1;
                    }
                }

                $this->db->where('attendance_id', $row->attendance_id);
                $this->db->update('tbl_attendance', $update_data);
            }

            $row->hours_worked    = $computed->hours_worked;
            $row->overtime_hours  = $computed->overtime_hours;
            $row->hours_display   = $computed->hours_display;
            $row->overtime_display = $computed->overtime_display;
        }

        return $results;
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
