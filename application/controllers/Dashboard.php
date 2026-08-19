<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Employee_model');
        $this->load->model('Dashboard_model');
        $this->load->model('Attendance_model');
        $this->load->model('Audit_model');
    }

    public function index()
    {
        $this->requirePermission('access_dashboard');

        $data['title'] = "Dashboard";

        if ($this->isAdmin())
        {
            $this->_adminDashboard($data);
        }
        elseif ($this->isHR())
        {
            $this->_hrDashboard($data);
        }
        else
        {
            $this->_employeeDashboard($data);
        }
    }

    private function _adminDashboard(&$data)
    {
        $data['totalEmployees']   = $this->Employee_model->countEmployees();
        $data['totalDepartments'] = $this->Employee_model->countDepartments();
        $data['averageSalary']    = $this->Employee_model->averageSalary();
        $data['highestSalary']    = $this->Employee_model->highestSalary();
        $data['recentEmployees'] = $this->Employee_model->getRecentEmployees();
        $data['recentDepartments'] = $this->Employee_model->getRecentDepartments();
        $data['departmentChart'] = $this->Dashboard_model->getEmployeesByDepartment();
        $data['statusChart'] = $this->Dashboard_model->getEmployeeStatus();

        $this->load->view('dashboard/index', $data);
    }

    private function _hrDashboard(&$data)
    {
        // Keep the dashboard consistent with the Attendance module's 7:00 PM
        // auto-close rule before computing today's attendance exceptions.
        $this->Attendance_model->autoCloseOpenAttendance();

        $data['totalEmployees']       = $this->Employee_model->countEmployees();
        $data['presentToday']         = $this->Dashboard_model->getPresentToday();
        $data['onLeaveToday']         = $this->Dashboard_model->getOnLeaveToday();
        $data['absentToday']          = $this->Dashboard_model->getAbsentToday();
        $data['pendingLeaveCount']    = $this->Dashboard_model->getPendingLeaveCount();
        $data['recentLeaveRequests']  = $this->Dashboard_model->getRecentLeaveRequests(5);
        $data['departmentChart']      = $this->Dashboard_model->getEmployeesByDepartment();
        $data['attendanceExceptions'] = $this->Dashboard_model->getAttendanceExceptionsToday();
        $data['upcomingMeetings']     = $this->Dashboard_model->getUpcomingMeetings(4);
        $data['pendingHikes']         = $this->Dashboard_model->getPendingSalaryHikes(3);
        $data['pendingHikesCount']    = $this->Dashboard_model->getPendingHikesCount();

        $this->load->view('dashboard/hr', $data);
    }

    private function _employeeDashboard(&$data)
    {
        $employee_id = $this->session->userdata('employee_id');

        $data['employee'] = $this->Dashboard_model->getEmployeeProfile($employee_id);
        $data['attendance_summary'] = $this->Dashboard_model->getEmployeeAttendanceSummary($employee_id);
        $data['leave_summary'] = $this->Dashboard_model->getEmployeeLeaveSummary($employee_id);
        $data['salary_summary'] = $this->Dashboard_model->getEmployeeSalarySummary($employee_id);
        $data['recent_activity'] = $this->Dashboard_model->getEmployeeRecentActivity($employee_id);

        $this->load->view('dashboard/employee', $data);
    }
}
