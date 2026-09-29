<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Employee_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEmployees()
    {
        $this->db->select('tbl_employee.*, tbl_department.department_name');

        $this->db->from('tbl_employee');

        $this->db->join(
            'tbl_department',
            'tbl_department.department_id = tbl_employee.department_id'
        );

        // Show only active (not deleted) employees
        $this->db->where('tbl_employee.deleted_at', NULL);

        return $this->db->get()->result();
    }

    public function getDepartments()
    {
        return $this->db
            ->where('deleted_at', NULL)
            ->order_by('department_name', 'ASC')
            ->get('tbl_department')
            ->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Insert Employee
    |--------------------------------------------------------------------------
    */

    public function insertEmployee()
    {
        $data = array(

            'employee_name'   => $this->input->post('employee_name'),
            'employee_email'  => $this->input->post('employee_email'),
            'employee_phone'  => $this->input->post('employee_phone'),
            'employee_salary' => $this->input->post('employee_salary'),
            'department_id'   => $this->input->post('department_id'),
            'status'          => $this->input->post('status'),
            'created_at'      => date('Y-m-d H:i:s')

        );

        if (
            isset($_FILES['profile_image']) &&
            $_FILES['profile_image']['error'] == 0
        ) {
            $data['profile_image'] =
                file_get_contents($_FILES['profile_image']['tmp_name']);
        }

        return $this->db->insert('tbl_employee', $data);
    }

    public function countEmployees()
    {
        $this->db->where('deleted_at', NULL);
        $this->db->where('status', 'Active');

        return $this->db->count_all_results('tbl_employee');
    }

    public function countDepartments()
    {
        $this->db->where('deleted_at', NULL);

        return $this->db->count_all_results('tbl_department');
    }

    public function averageSalary()
    {
        $this->db->select_avg('employee_salary');

        $this->db->where('deleted_at', NULL);

        return $this->db->get('tbl_employee')->row()->employee_salary;
    }

    public function highestSalary()
    {
        $this->db->select_max('employee_salary');

        $this->db->where('deleted_at', NULL);

        return $this->db->get('tbl_employee')->row()->employee_salary;
    }

    /*
|--------------------------------------------------------------------------
| Get Employee By ID
|--------------------------------------------------------------------------
*/

    public function getEmployeeById($id)
    {
        $this->db->where('employee_id', $id);

        return $this->db->get('tbl_employee')->row();
    }

    /*
|--------------------------------------------------------------------------
| Update Employee
|--------------------------------------------------------------------------
*/

    public function updateEmployee($id)
    {
        $data = array(

            'employee_name'   => $this->input->post('employee_name'),

            'employee_email'  => $this->input->post('employee_email'),

            'employee_phone'  => $this->input->post('employee_phone'),

            'employee_salary' => $this->input->post('employee_salary'),

            'department_id'   => $this->input->post('department_id'),

            'status'          => $this->input->post('status'),

            'updated_at'      => date('Y-m-d H:i:s')

        );

        $this->db->where('employee_id', $id);
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $imageData = file_get_contents($_FILES['profile_image']['tmp_name']);

            $data['profile_image'] = $imageData;
        }
        return $this->db->update('tbl_employee', $data);
    }
    /*
|--------------------------------------------------------------------------
| Check Employee Exists
|--------------------------------------------------------------------------
*/

    public function employeeExists($id)
    {
        return $this->db
            ->where('employee_id', $id)
            ->where('deleted_at', NULL)
            ->count_all_results('tbl_employee') > 0;
    }

    /*
|--------------------------------------------------------------------------
| Soft Delete Employee
|--------------------------------------------------------------------------
*/

    public function deleteEmployee($id)
    {
        $data = array(
            'deleted_at' => date('Y-m-d H:i:s')
        );

        $this->db->where('employee_id', $id);

        return $this->db->update('tbl_employee', $data);
    }


    /*
|--------------------------------------------------------------------------
| Get Employee Details
|--------------------------------------------------------------------------
*/

    public function getEmployeeDetails($id)
    {
        $this->db->select('
        tbl_employee.*,
        tbl_department.department_name
    ');

        $this->db->from('tbl_employee');

        $this->db->join(
            'tbl_department',
            'tbl_department.department_id = tbl_employee.department_id'
        );

        $this->db->where('tbl_employee.employee_id', $id);

        $this->db->where('tbl_employee.deleted_at', NULL);

        return $this->db->get()->row();
    }

    public function getRecentEmployees($limit = 5)
    {
        $this->db->select('tbl_employee.*, tbl_department.department_name');
        $this->db->from('tbl_employee');
        $this->db->join(
            'tbl_department',
            'tbl_department.department_id = tbl_employee.department_id'
        );
        $this->db->where('tbl_employee.deleted_at', NULL);
        $this->db->order_by('tbl_employee.employee_id', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }

    public function getRecentDepartments($limit = 5)
    {
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('department_id', 'DESC');
        $this->db->limit($limit);

        return $this->db->get('tbl_department')->result();
    }

    public function getEmployeeReport($department = '', $status = '', $minSalary = '', $maxSalary = '')
    {
        $this->db->select('tbl_employee.*, tbl_department.department_name');

        $this->db->from('tbl_employee');

        $this->db->join(
            'tbl_department',
            'tbl_department.department_id = tbl_employee.department_id'
        );

        // Show only non-deleted employees
        $this->db->where('tbl_employee.deleted_at', NULL);

        // Department Filter
        if (!empty($department)) {
            $this->db->where('tbl_employee.department_id', $department);
        }

        // Status Filter
        if (!empty($status)) {
            $this->db->where('tbl_employee.status', $status);
        }

        // Minimum Salary Filter
        if (!empty($minSalary)) {
            $this->db->where('tbl_employee.employee_salary >=', $minSalary);
        }

        // Maximum Salary Filter
        if (!empty($maxSalary)) {
            $this->db->where('tbl_employee.employee_salary <=', $maxSalary);
        }

        $this->db->order_by('tbl_employee.employee_id', 'DESC');

        return $this->db->get()->result();
    }

    public function totalEmployeesReport()
    {
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('tbl_employee');
    }

    public function activeEmployeesReport()
    {
        $this->db->where('deleted_at', NULL);
        $this->db->where('status', 'Active');

        return $this->db->count_all_results('tbl_employee');
    }

    public function inactiveEmployeesReport()
    {
        $this->db->where('deleted_at', NULL);
        $this->db->where('status', 'Inactive');

        return $this->db->count_all_results('tbl_employee');
    }

    public function averageSalaryReport()
    {
        $this->db->select_avg('employee_salary');
        $this->db->where('deleted_at', NULL);

        return $this->db->get('tbl_employee')->row()->employee_salary;
    }

    public function lowestSalary()
    {
        $this->db->select_min('employee_salary');

        $this->db->where('deleted_at', NULL);

        return $this->db->get('tbl_employee')->row()->employee_salary;
    }
    public function totalSalary()
    {
        $this->db->select_sum('employee_salary');

        $this->db->where('deleted_at', NULL);

        return $this->db->get('tbl_employee')->row()->employee_salary;
    }
    public function getSalaryReport($department = '', $minSalary = '', $maxSalary = '')
    {
        $this->db->select('tbl_employee.*, tbl_department.department_name');

        $this->db->from('tbl_employee');

        $this->db->join(
            'tbl_department',
            'tbl_department.department_id = tbl_employee.department_id'
        );

        $this->db->where('tbl_employee.deleted_at', NULL);

        if (!empty($department)) {
            $this->db->where('tbl_employee.department_id', $department);
        }

        if (!empty($minSalary)) {
            $this->db->where('tbl_employee.employee_salary >=', $minSalary);
        }

        if (!empty($maxSalary)) {
            $this->db->where('tbl_employee.employee_salary <=', $maxSalary);
        }

        $this->db->order_by('tbl_employee.employee_salary', 'DESC');

        return $this->db->get()->result();
    }

    public function getSalarySummary($department = '', $minSalary = '', $maxSalary = '')
    {
        $this->db->select('
        MAX(employee_salary) AS highestSalary,
        MIN(employee_salary) AS lowestSalary,
        AVG(employee_salary) AS averageSalary,
        SUM(employee_salary) AS totalSalary
    ');

        $this->db->from('tbl_employee');

        $this->db->where('deleted_at', NULL);

        if (!empty($department)) {
            $this->db->where('department_id', $department);
        }

        if (!empty($minSalary)) {
            $this->db->where('employee_salary >=', $minSalary);
        }

        if (!empty($maxSalary)) {
            $this->db->where('employee_salary <=', $maxSalary);
        }

        return $this->db->get()->row();
    }

    public function highestPaidEmployee($department = '', $minSalary = '', $maxSalary = '')
    {
        $this->db->select('
        tbl_employee.employee_name,
        tbl_employee.employee_salary
    ');

        $this->db->from('tbl_employee');

        $this->db->where('deleted_at', NULL);

        if (!empty($department)) {
            $this->db->where('department_id', $department);
        }

        if (!empty($minSalary)) {
            $this->db->where('employee_salary >=', $minSalary);
        }

        if (!empty($maxSalary)) {
            $this->db->where('employee_salary <=', $maxSalary);
        }

        $this->db->order_by('employee_salary', 'DESC');

        $this->db->limit(1);

        return $this->db->get()->row();
    }
    public function getEmployeesByStatus($status)
    {
        $this->db->select('
        tbl_employee.*,
        tbl_department.department_name
    ');

        $this->db->from('tbl_employee');

        $this->db->join(
            'tbl_department',
            'tbl_department.department_id = tbl_employee.department_id'
        );

        $this->db->where('tbl_employee.deleted_at', NULL);

        $this->db->where('tbl_employee.status', $status);

        $this->db->order_by('tbl_employee.employee_id', 'DESC');

        return $this->db->get()->result();
    }
}
