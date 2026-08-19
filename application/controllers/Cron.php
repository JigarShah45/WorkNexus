<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cron controller for scheduled background tasks.
 *
 * Extends CI_Controller (not MY_Controller) on purpose so that scheduled
 * tasks can run without an authenticated web session.
 *
 * Auto-close open attendance records at / after 7:00 PM IST.
 * Recommended scheduled task (run daily after 7:00 PM IST):
 *
 *   Windows Task Scheduler:  php "C:\xampp\htdocs\employee_management\index.php" cron auto_close_attendance
 *   Linux cron:              0 19 * * *  php /path/to/employee_management/index.php cron auto_close_attendance
 *
 * The same closure also runs automatically whenever the Attendance pages are
 * loaded, so records are closed even if no scheduled task is configured.
 */
class Cron extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
        $this->load->model('Attendance_model');
    }

    public function auto_close_attendance()
    {
        $closed = $this->Attendance_model->autoCloseOpenAttendance();
        echo 'Auto-closed ' . (int) $closed . ' attendance record(s) at ' . date('Y-m-d H:i:s') . ' IST';
    }
}
