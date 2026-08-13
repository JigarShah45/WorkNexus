<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Employee_model');
        $this->load->model('Audit_model');
        $this->load->library(array('form_validation'));
    }

    public function index()
    {
        $this->requirePermission('access_employee');

        $data['title'] = "Employees";
        $data['employees'] = $this->Employee_model->getEmployees();
        $data['totalEmployees']   = $this->Employee_model->countEmployees();
        $data['totalDepartments'] = $this->Employee_model->countDepartments();
        $data['averageSalary']    = $this->Employee_model->averageSalary();
        $data['highestSalary']    = $this->Employee_model->highestSalary();

        $this->load->view('employee/index',$data);
    }

    public function add()
    {
        $this->requirePermission('edit_employee_data');

        $data['title'] = "Add Employee";
        $data['departments'] = $this->Employee_model->getDepartments();

        $this->load->view('employee/add',$data);
    }

    public function store()
    {
        $this->requirePermission('edit_employee_data');

        $this->validateEmployeeForm();

        if($this->form_validation->run()==FALSE)
        {
            $this->add();
        }
        else
        {
            $this->Employee_model->insertEmployee();

            $this->Audit_model->log('CREATE', 'tbl_employee', $this->db->insert_id(), NULL, array(
                'employee_name' => $this->input->post('employee_name'),
                'employee_email' => $this->input->post('employee_email')
            ));

            $this->session->set_flashdata('success', 'Employee added successfully.');
            redirect('employee');
        }
    }

    public function edit($id)
    {
        $this->requirePermission('edit_employee_data');

        if(!$this->Employee_model->employeeExists($id))
        {
            show_404();
        }

        $data['title'] = "Edit Employee";
        $data['employee'] = $this->Employee_model->getEmployeeById($id);
        $data['departments'] = $this->Employee_model->getDepartments();

        $this->load->view('employee/edit',$data);
    }

    public function update($id)
    {
        $this->requirePermission('edit_employee_data');

        if(!$this->Employee_model->employeeExists($id))
        {
            show_404();
        }

        $old = $this->Employee_model->getEmployeeById($id);

        $this->validateEmployeeForm();

        if($this->form_validation->run()==FALSE)
        {
            $this->edit($id);
        }
        else
        {
            $this->Employee_model->updateEmployee($id);

            $this->Audit_model->log('UPDATE', 'tbl_employee', $id, (array)$old, array(
                'employee_name' => $this->input->post('employee_name'),
                'employee_email' => $this->input->post('employee_email')
            ));

            $this->session->set_flashdata('success', 'Employee updated successfully.');
            redirect('employee');
        }
    }

    private function validateEmployeeForm()
    {
        $this->form_validation->set_rules('employee_name', 'Employee Name', 'required');
        $this->form_validation->set_rules('employee_email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('employee_phone', 'Phone', 'required');
        $this->form_validation->set_rules('employee_salary', 'Salary', 'required|numeric');
        $this->form_validation->set_rules('department_id', 'Department', 'required');
        $this->form_validation->set_rules('status', 'Status', 'required');
    }

    public function delete($id)
    {
        $this->requirePermission('edit_employee_data');

        if (!$this->input->is_ajax_request())
        {
            show_404();
        }

        if (!$this->Employee_model->employeeExists($id))
        {
            echo json_encode(array('status' => false, 'message' => 'Employee not found.'));
            return;
        }

        $old = $this->Employee_model->getEmployeeById($id);

        $this->Employee_model->deleteEmployee($id);

        $this->Audit_model->log('DELETE', 'tbl_employee', $id, (array)$old, NULL);

        echo json_encode(array('status' => true, 'message' => 'Employee deleted successfully.'));
    }

    public function view($id)
    {
        $this->requirePermission('view_employee_data');

        if(!$this->Employee_model->employeeExists($id))
        {
            show_404();
        }

        $data['title'] = "Employee Details";
        $data['employee'] = $this->Employee_model->getEmployeeDetails($id);

        $this->load->view('employee/view', $data);
    }

    public function image($id)
    {
        $employee = $this->Employee_model->getEmployeeDetails($id);

        if (!$employee || empty($employee->profile_image))
        {
            show_404();
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_buffer($finfo, $employee->profile_image);
        finfo_close($finfo);

        header("Content-Type: ".$mime);
        echo $employee->profile_image;
    }
}
