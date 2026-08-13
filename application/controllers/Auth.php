<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->helper(array('url','form'));
        $this->load->library(array('session','form_validation'));
        $this->load->model('Auth_model');
        $this->load->model('UserLog_model');
        $this->load->model('Attendance_model');
    }

    public function index()
    {
        if ($this->session->userdata('logged_in'))
        {
            redirect('dashboard');
        }
        redirect('auth/login');
    }

    public function login()
    {
        if ($this->session->userdata('logged_in'))
        {
            redirect('dashboard');
        }
        $this->load->view('auth/login');
    }

    public function loginProcess()
    {
        $this->form_validation->set_rules('username', 'Username', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() == FALSE)
        {
            $this->load->view('auth/login');
            return;
        }

        $username = $this->input->post('username');
        $password = $this->input->post('password');

        $user = $this->Auth_model->getUserByUsername($username);

        if ($user && password_verify($password, $user->password))
        {
            // Get previous login BEFORE recording new one
            $last_login = $this->UserLog_model->getPreviousLogin($user->user_id);

            $this->Auth_model->updateLastLogin($user->user_id);

            // Record login log
            $this->UserLog_model->recordLog($user->user_id, 'LOGIN');

            // Auto clock-in for attendance
            $this->Attendance_model->autoClockIn($user->employee_id);

            $this->session->set_userdata(array(
                'user_id'            => $user->user_id,
                'employee_id'        => $user->employee_id,
                'employee_name'      => $user->employee_name,
                'username'           => $user->username,
                'role'               => $user->role,
                'logged_in'          => TRUE,
                'last_login_display' => $last_login,
                'profile_image'      => $user->profile_image
            ));

            // Load permissions
            $this->load->library('permission');
            $this->permission->loadUserPermissions();

            redirect('dashboard');
        }

        $this->session->set_flashdata('error', 'Invalid username or password.');
        redirect('auth/login');
    }

    public function logout()
    {
        $user_id = $this->session->userdata('user_id');
        $employee_id = $this->session->userdata('employee_id');

        if ($user_id)
        {
            // Record logout log
            $this->UserLog_model->recordLog($user_id, 'LOGOUT');

            // Auto clock-out for attendance
            $this->Attendance_model->autoClockOut($employee_id);
        }

        $this->session->set_flashdata('success', 'You have been logged out successfully');
        $this->session->sess_destroy();
        redirect('auth/login');
    }
}
