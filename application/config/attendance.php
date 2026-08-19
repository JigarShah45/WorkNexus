<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| WorkNexus Attendance Business Rules
|--------------------------------------------------------------------------
| All times below are in Indian Standard Time (Asia/Kolkata).
|
| Official shift         : 11:00 AM - 7:00 PM
| Grace period           : 15 minutes
| Valid login window     : 11:00 AM - 11:15 AM  (login after 11:15 => Half-Day)
| Lunch break (unpaid)   : 2:00 PM - 3:00 PM
| Valid clock-out window : 6:30 PM onwards
|                          (logout before 6:30 PM is a session logout,
|                           NOT the final attendance clock-out)
| Automatic closure      : 7:00 PM when no valid logout occurred
|--------------------------------------------------------------------------
*/

$config['attendance_timezone']          = 'Asia/Kolkata';
$config['attendance_shift_start']       = '11:00:00';
$config['attendance_shift_end']         = '19:00:00';
$config['attendance_grace_end']         = '11:15:00';
$config['attendance_lunch_start']       = '14:00:00';
$config['attendance_lunch_end']         = '15:00:00';
$config['attendance_valid_logout_from'] = '18:30:00';