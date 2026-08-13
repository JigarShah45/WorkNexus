<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reports extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Employee_model');
        $this->load->model('Department_model');
    }

    public function index()
    {
        $this->requirePermission('access_reports');

        $data['title'] = "Reports";
        $this->load->view('reports/index', $data);
    }

    public function employees()
    {
        $this->requirePermission('access_reports');

        $department = $this->input->get('department');
        $status     = $this->input->get('status');
        $minSalary  = $this->input->get('min_salary');
        $maxSalary  = $this->input->get('max_salary');

        $data['title'] = "Employee Report";
        $data['departments'] = $this->Department_model->getDepartments();
        $data['employees'] = $this->Employee_model->getEmployeeReport($department, $status, $minSalary, $maxSalary);
        $data['totalEmployees'] = $this->Employee_model->totalEmployeesReport();
        $data['activeEmployees'] = $this->Employee_model->activeEmployeesReport();
        $data['inactiveEmployees'] = $this->Employee_model->inactiveEmployeesReport();
        $data['averageSalary'] = $this->Employee_model->averageSalaryReport();

        $this->load->view('reports/employees', $data);
    }

    public function departments()
    {
        $this->requirePermission('access_reports');

        $data['title'] = "Department Report";
        $data['departments'] = $this->Department_model->getDepartments();
        $this->load->view('reports/departments', $data);
    }

    public function salary()
    {
        $this->requirePermission('access_reports');

        $department = $this->input->get('department');
        $minSalary  = $this->input->get('min_salary');
        $maxSalary  = $this->input->get('max_salary');

        $data['title'] = "Salary Report";
        $data['departments'] = $this->Department_model->getDepartments();
        $data['employees'] = $this->Employee_model->getSalaryReport($department, $minSalary, $maxSalary);

        $summary = $this->Employee_model->getSalarySummary($department, $minSalary, $maxSalary);
        $data['highestSalary'] = $summary->highestSalary;
        $data['lowestSalary']  = $summary->lowestSalary;
        $data['averageSalary'] = $summary->averageSalary;
        $data['totalSalary']   = $summary->totalSalary;
        $data['highestEmployee'] = $this->Employee_model->highestPaidEmployee($department, $minSalary, $maxSalary);

        $this->load->view('reports/salary', $data);
    }

    public function activeEmployees()
    {
        $this->requirePermission('access_reports');

        $data['title'] = "Active Employees Report";
        $data['employees'] = $this->Employee_model->getEmployeesByStatus('Active');
        $data['totalEmployees'] = count($data['employees']);
        $this->load->view('reports/active_employees', $data);
    }

    public function inactiveEmployees()
    {
        $this->requirePermission('access_reports');

        $data['title'] = "Inactive Employees Report";
        $data['employees'] = $this->Employee_model->getEmployeesByStatus('Inactive');
        $data['totalEmployees'] = count($data['employees']);
        $this->load->view('reports/inactive_employees', $data);
    }
}
