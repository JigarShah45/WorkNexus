<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AuditTrail extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Audit_model');
    }

    public function index()
    {
        $this->requirePermission('access_audit_trail');

        $data['title'] = "Audit Trail";

        $user_id = $this->input->get('user_id') ?: '';
        $action = $this->input->get('action') ?: '';
        $table_name = $this->input->get('table_name') ?: '';
        $date_from = $this->input->get('date_from') ?: '';
        $date_to = $this->input->get('date_to') ?: '';

        $data['audit_logs'] = $this->Audit_model->getAuditTrail($user_id, $action, $table_name, $date_from, $date_to);
        $data['users'] = $this->Audit_model->getUsers();

        $this->load->view('audit_trail/index', $data);
    }
}
