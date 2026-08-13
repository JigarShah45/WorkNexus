<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Department_model extends CI_Model
{

    /*
    |--------------------------------------------------------------------------
    | Get Departments
    |--------------------------------------------------------------------------
    */

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
    | Insert Department
    |--------------------------------------------------------------------------
    */

    public function insertDepartment()
    {
        $data = array(

            'department_name' => $this->input->post('department_name'),

            'created_at' => date('Y-m-d H:i:s')

        );

        return $this->db->insert('tbl_department', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Department
    |--------------------------------------------------------------------------
    */

    public function departmentExists($departmentName)
    {
        return $this->db
            ->where('department_name', $departmentName)
            ->where('deleted_at', NULL)
            ->count_all_results('tbl_department') > 0;
    }

    public function getDepartmentById($id)
{
    return $this->db
        ->where('department_id', $id)
        ->where('deleted_at', NULL)
        ->get('tbl_department')
        ->row();
}

/*
|--------------------------------------------------------------------------
| Update Department
|--------------------------------------------------------------------------
*/

public function updateDepartment($id)
{
    $data = array(

        'department_name' => $this->input->post('department_name'),

        'updated_at' => date('Y-m-d H:i:s')

    );

    $this->db->where('department_id', $id);

    return $this->db->update('tbl_department', $data);
}

/*
|--------------------------------------------------------------------------
| Check Duplicate Department Except Current
|--------------------------------------------------------------------------
*/

public function departmentExistsExcept($name, $id)
{
    return $this->db
        ->where('department_name', $name)
        ->where('department_id !=', $id)
        ->where('deleted_at', NULL)
        ->count_all_results('tbl_department') > 0;
}

public function getDepartmentDetails($id)
{
    return $this->db
        ->where('department_id', $id)
        ->where('deleted_at', NULL)
        ->get('tbl_department')
        ->row();
}

/*
|--------------------------------------------------------------------------
| Get Employees By Department
|--------------------------------------------------------------------------
*/

public function getDepartmentEmployees($department_id)
{
    return $this->db
        ->where('department_id', $department_id)
        ->where('deleted_at', NULL)
        ->order_by('employee_name', 'ASC')
        ->get('tbl_employee')
        ->result();
}

    /*
|--------------------------------------------------------------------------
| Check Employees in Department
|--------------------------------------------------------------------------
*/

public function hasEmployees($department_id)
{
    return $this->db
        ->where('department_id', $department_id)
        ->where('deleted_at', NULL)
        ->count_all_results('tbl_employee') > 0;
}

/*
|--------------------------------------------------------------------------
| Soft Delete Department
|--------------------------------------------------------------------------
*/

public function deleteDepartment($department_id)
{
    $this->db->where('department_id', $department_id);

    return $this->db->update('tbl_department', array(
        'deleted_at' => date('Y-m-d H:i:s')
    ));
}

}