<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Department extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Department_model');
        $this->load->model('Audit_model');
        $this->load->library(array('form_validation'));
    }

    public function index()
    {
        $this->requirePermission('access_department');

        $data['title'] = "Departments";
        $data['departments'] = $this->Department_model->getDepartments();

        $this->load->view('department/index', $data);
    }

    public function add()
    {
        $this->requirePermission('access_department');

        $data['title'] = "Add Department";
        $this->load->view('department/add', $data);
    }

    public function store()
    {
        $this->requirePermission('access_department');

        $this->form_validation->set_rules('department_name', 'Department Name', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->add();
        } else {
            if ($this->Department_model->departmentExists($this->input->post('department_name'))) {
                $this->session->set_flashdata('error', 'Department already exists.');
                redirect('department/add');
            }

            $this->Department_model->insertDepartment();

            $this->Audit_model->log('CREATE', 'tbl_department', $this->db->insert_id(), NULL, array(
                'department_name' => $this->input->post('department_name')
            ));

            $this->session->set_flashdata('success', 'Department added successfully.');
            redirect('department');
        }
    }

    public function edit($id)
    {
        $this->requirePermission('access_department');

        $department = $this->Department_model->getDepartmentById($id);

        if (!$department) {
            show_404();
        }

        $data['title'] = 'Edit Department';
        $data['department'] = $department;

        $this->load->view('department/edit', $data);
    }

    public function update($id)
    {
        $this->requirePermission('access_department');

        $this->form_validation->set_rules('department_name', 'Department Name', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($id);
        } else {
            if ($this->Department_model->departmentExistsExcept(
                $this->input->post('department_name'),
                $id
            )) {
                $this->session->set_flashdata('error', 'Department already exists.');
                redirect('department/edit/' . $id);
            }

            $old = $this->Department_model->getDepartmentById($id);

            $this->Department_model->updateDepartment($id);

            $this->Audit_model->log('UPDATE', 'tbl_department', $id, (array)$old, array(
                'department_name' => $this->input->post('department_name')
            ));

            $this->session->set_flashdata('success', 'Department updated successfully.');
            redirect('department');
        }
    }

    public function view($id)
    {
        $this->requirePermission('access_department');

        $department = $this->Department_model->getDepartmentDetails($id);

        if (!$department) {
            show_404();
        }

        $data['title'] = 'View Department';
        $data['department'] = $department;
        $data['employees'] = $this->Department_model->getDepartmentEmployees($id);

        $this->load->view('department/view', $data);
    }

    public function delete($id)
    {
        $this->requirePermission('access_department');

        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $department = $this->Department_model->getDepartmentById($id);

        if (!$department) {
            echo json_encode(array('status' => false, 'message' => 'Department not found.'));
            return;
        }

        if ($this->Department_model->hasEmployees($id)) {
            echo json_encode(array('status' => false, 'message' => 'Cannot delete this department because employees are assigned to it.'));
            return;
        }

        $this->Department_model->deleteDepartment($id);

        $this->Audit_model->log('DELETE', 'tbl_department', $id, (array)$department, NULL);

        echo json_encode(array('status' => true, 'message' => 'Department deleted successfully.'));
    }
}
