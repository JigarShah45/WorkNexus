<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| WorkNexus Attendance Business Rules
|--------------------------------------------------------------------------
| All times below are in Indian Standard Time (Asia/Kolkata).
|
| Official shift            : 11:00 AM - 7:00 PM
| Grace period              : 15 minutes
| Valid login window        : 11:00 AM - 11:15 AM  (login after 11:15 => Half-Day)
| Valid clock-out window    : 6:30 PM onwards
|                            (logout before 6:30 PM is a session logout,
|                             NOT the final attendance clock-out)
| Automatic closure         : 7:00 PM when no valid logout occurred
|
| Hours Worked              : Clock In -> 7:00 PM (effective clock-out).
|                            The 7:00 PM value is DERIVED for calculation
|                            and display only - it is never written into the
|                            clock_out column when the employee has not
|                            actually clocked out.
| Overtime                  : Actual Clock Out - 7:00 PM. Overtime requires
|                            a real logout after 7:00 PM; an open record
|                            never produces overtime.
|--------------------------------------------------------------------------
*/

$config['attendance_timezone']          = 'Asia/Kolkata';
$config['attendance_shift_start']       = '11:00:00';
$config['attendance_shift_end']         = '19:00:00';
$config['attendance_grace_end']         = '11:15:00';
$config['attendance_valid_logout_from'] = '18:30:00';