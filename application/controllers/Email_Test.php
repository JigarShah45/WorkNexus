<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * TEMPORARY — Email SMTP Test Controller
 *
 * DEVELOPMENT USE ONLY. Remove after SMTP is confirmed working.
 * Access via: /email_test
 */
class Email_Test extends CI_Controller {

    public function index()
    {
        if (ENVIRONMENT !== 'development')
        {
            show_404();
        }

        $this->load->library('email');
        $this->config->load('email');

        $this->email->clear();
        $this->email->initialize(array(
            'protocol'     => 'smtp',
            'smtp_host'    => 'smtp.gmail.com',
            'smtp_port'    => 587,
            'smtp_user'    => $this->config->item('smtp_user'),
            'smtp_pass'    => $this->config->item('smtp_pass'),
            'smtp_crypto'  => 'tls',
            'smtp_timeout' => 30,
            'charset'      => 'utf-8',
            'newline'      => "\r\n",
            'crlf'         => "\r\n",
            'wordwrap'     => TRUE,
            'mailtype'     => 'text',
        ));

        $this->email->from($this->config->item('from_email'), $this->config->item('from_name'));
        $this->email->to('jjshah215@gmail.com');
        $this->email->subject('WorkNexus SMTP Test 2');
        $this->email->message('WorkNexus SMTP test successful.');

        $result = $this->email->send();

        echo '<h2>WorkNexus SMTP Test 2</h2>';

        if ($result)
        {
            echo '<p style="color:green;font-weight:bold;">Email sent successfully.</p>';
            echo '<p>Recipient: jjshah215@gmail.com</p>';
        }
        else
        {
            echo '<p style="color:red;font-weight:bold;">Email could not be sent.</p>';
            echo '<p>Check application/logs for details.</p>';

            $debug = $this->email->print_debugger(array('headers', 'subject', 'body'));
            echo '<pre>' . htmlspecialchars($debug) . '</pre>';

            log_message('error', 'Email_Test: SMTP test failed.');
        }
    }
}
