<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Attendance_model');
        $this->load->model('Audit_model');
        $this->load->model('Employee_model');
        $this->load->model('Shift_model');
    }

    public function index()
    {
        $this->requirePermission('access_attendance');

        $data['title'] = "My Attendance";

        $employee_id = $this->session->userdata('employee_id');
        $month = $this->input->get('month') ?: date('m');
        $year = $this->input->get('year') ?: date('Y');

        $data['attendance'] = $this->Attendance_model->getEmployeeAttendance($employee_id, $month, $year);
        $data['month'] = $month;
        $data['year'] = $year;
        $data['employee_shifts'] = $this->Attendance_model->getEmployeeShifts($employee_id);

        $this->load->view('attendance/index', $data);
    }

    public function manage()
    {
        $this->requirePermission('access_attendance');

        $data['title'] = "Manage Attendance";

        $month = $this->input->get('month') ?: date('m');
        $year = $this->input->get('year') ?: date('Y');
        $department_id = $this->input->get('department_id') ?: '';

        $data['attendance'] = $this->Attendance_model->getAllAttendance($month, $year, $department_id);
        $data['departments'] = $this->Employee_model->getDepartments();
        $data['month'] = $month;
        $data['year'] = $year;
        $data['department_id'] = $department_id;

        $this->load->view('attendance/manage', $data);
    }

    public function assign_shift()
    {
        $this->requirePermission('access_attendance');

        $employee_id = $this->input->post('employee_id');
        $shift_id = $this->input->post('shift_id');
        $effective_from = $this->input->post('effective_from');

        $this->Attendance_model->assignShift($employee_id, $shift_id, $effective_from);

        $this->Audit_model->log('UPDATE', 'tbl_employee_shifts', NULL, NULL, array(
            'employee_id' => $employee_id,
            'shift_id' => $shift_id
        ));

        $this->session->set_flashdata('success', 'Shift assigned successfully.');
        redirect('attendance/manage');
    }
}
