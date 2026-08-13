<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->requirePermission('access_settings');

        $data['title'] = "Settings";
        $this->load->view('settings/index', $data);
    }
}
