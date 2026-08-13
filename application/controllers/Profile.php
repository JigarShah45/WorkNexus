<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Employee_model');
        $this->load->model('Audit_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $data['title'] = "My Profile";

        $employee_id = $this->session->userdata('employee_id');
        $user_id = $this->session->userdata('user_id');

        $data['employee'] = $this->Employee_model->getEmployeeDetails($employee_id);

        $this->db->where('user_id', $user_id);
        $data['user'] = $this->db->get('tbl_users')->row();

        $this->load->view('profile/index', $data);
    }

    public function update()
    {
        $employee_id = $this->session->userdata('employee_id');

        $this->form_validation->set_rules('employee_name', 'Full Name', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('employee_email', 'Email Address', 'required|trim|valid_email|max_length[255]');
        $this->form_validation->set_rules('employee_phone', 'Mobile Number', 'trim|max_length[50]');

        if ($this->form_validation->run() == FALSE)
        {
            $this->session->set_flashdata('error', validation_errors());
            redirect('profile');
            return;
        }

        $old_data = $this->Employee_model->getEmployeeById($employee_id);

        $update_data = array(
            'employee_name'  => $this->input->post('employee_name', TRUE),
            'employee_email' => $this->input->post('employee_email', TRUE),
            'employee_phone' => $this->input->post('employee_phone', TRUE),
            'updated_at'     => date('Y-m-d H:i:s')
        );

        $this->db->where('employee_id', $employee_id);
        $this->db->update('tbl_employee', $update_data);

        $this->Audit_model->log('UPDATE', 'tbl_employee', $employee_id, array(
            'employee_name'  => $old_data->employee_name,
            'employee_email' => $old_data->employee_email,
            'employee_phone' => $old_data->employee_phone
        ), array(
            'employee_name'  => $update_data['employee_name'],
            'employee_email' => $update_data['employee_email'],
            'employee_phone' => $update_data['employee_phone']
        ));

        $this->session->set_userdata('employee_name', $update_data['employee_name']);

        $this->session->set_flashdata('success', 'Profile updated successfully.');
        redirect('profile');
    }

    public function upload_photo()
    {
        $employee_id = $this->session->userdata('employee_id');

        if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] == 4)
        {
            $this->session->set_flashdata('error', 'Please select an image to upload.');
            redirect('profile');
            return;
        }

        $file = $_FILES['profile_image'];

        if ($file['error'] != 0)
        {
            $this->session->set_flashdata('error', 'Upload error. Please try again.');
            redirect('profile');
            return;
        }

        $max_size = 2 * 1024 * 1024;
        if ($file['size'] > $max_size)
        {
            $this->session->set_flashdata('error', 'File size exceeds 2MB limit.');
            redirect('profile');
            return;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        $allowed_mimes = array('image/jpeg', 'image/png', 'image/gif');
        if (!in_array($mime, $allowed_mimes))
        {
            $this->session->set_flashdata('error', 'Invalid file type. Only JPG, PNG, and GIF are allowed.');
            redirect('profile');
            return;
        }

        $ext_map = array(
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif'
        );

        $ext = $ext_map[$mime];
        $new_filename = 'profile_' . $employee_id . '_' . time() . '.' . $ext;

        $image_data = file_get_contents($file['tmp_name']);

        $this->db->where('employee_id', $employee_id);
        $this->db->update('tbl_employee', array(
            'profile_image' => $image_data,
            'updated_at'    => date('Y-m-d H:i:s')
        ));

        $this->Audit_model->log('UPDATE', 'tbl_employee', $employee_id, NULL, array('action' => 'profile_photo_changed'));

        $this->session->set_userdata('profile_image', $image_data);

        $this->session->set_flashdata('success', 'Profile photo updated successfully.');
        redirect('profile');
    }

    public function change_password()
    {
        $this->form_validation->set_rules('current_password', 'Current Password', 'required');
        $this->form_validation->set_rules('new_password', 'New Password', 'required|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Confirm New Password', 'required|matches[new_password]');

        if ($this->form_validation->run() == FALSE)
        {
            $this->session->set_flashdata('error', validation_errors());
            redirect('profile');
            return;
        }

        $user_id = $this->session->userdata('user_id');
        $current_password = $this->input->post('current_password');
        $new_password = $this->input->post('new_password');

        $user = $this->db->where('user_id', $user_id)->get('tbl_users')->row();

        if (!password_verify($current_password, $user->password))
        {
            $this->session->set_flashdata('error', 'Current password is incorrect.');
            redirect('profile');
            return;
        }

        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $this->db->where('user_id', $user_id);
        $this->db->update('tbl_users', array(
            'password'   => $hashed,
            'updated_at' => date('Y-m-d H:i:s')
        ));

        $this->Audit_model->log('UPDATE', 'tbl_users', $user_id, NULL, array('action' => 'password_changed'));

        $this->session->set_flashdata('success', 'Password changed successfully.');
        redirect('profile');
    }
}
