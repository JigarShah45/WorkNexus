<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserLogs extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('UserLog_model');
    }

    public function index()
    {
        $this->requirePermission('access_user_logs');

        $data['title'] = "User Login Logs";

        $user_id = $this->input->get('user_id') ?: '';
        $action = $this->input->get('action') ?: '';
        $date_from = $this->input->get('date_from') ?: '';
        $date_to = $this->input->get('date_to') ?: '';

        $data['user_logs'] = $this->UserLog_model->getUserLogs($user_id, $action, $date_from, $date_to);
        $data['users'] = $this->UserLog_model->getUsers();

        $this->load->view('user_logs/index', $data);
    }
}
