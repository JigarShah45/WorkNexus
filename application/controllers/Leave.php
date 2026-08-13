<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leave extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('text');
        $this->load->model('Leave_model');
        $this->load->model('Audit_model');
        $this->load->model('Employee_model');
        $this->load->model('Notification_model');
        $this->load->library('form_validation');
        $this->load->library('Worknexusmailer');
    }

    public function index()
    {
        $this->requirePermission('access_leave');

        if ($this->isAdminOrHR())
        {
            $this->manage();
        }
        else
        {
            $this->my_leaves();
        }
    }

    public function my_leaves()
    {
        $this->requirePermission('access_leave');

        $data['title'] = "My Leave Requests";

        $employee_id = $this->session->userdata('employee_id');
        $data['leave_requests'] = $this->Leave_model->getLeaveRequestsByEmployee($employee_id);

        $this->load->view('leave/my_leaves', $data);
    }

    public function request_leave()
    {
        $this->requirePermission('access_leave');

        if ($this->isAdmin())
        {
            $this->session->set_flashdata('error', 'Admins cannot submit leave requests.');
            redirect('leave');
        }

        $data['title'] = "Request Leave";
        $data['employee_id'] = $this->session->userdata('employee_id');

        $this->load->view('leave/request', $data);
    }

    public function store_leave()
    {
        $this->requirePermission('access_leave');

        if ($this->isAdmin())
        {
            $this->session->set_flashdata('error', 'Admins cannot submit leave requests.');
            redirect('leave');
        }

        $this->form_validation->set_rules('leave_type', 'Leave Type', 'required');
        $this->form_validation->set_rules('start_date', 'Start Date', 'required');
        $this->form_validation->set_rules('end_date', 'End Date', 'required');
        $this->form_validation->set_rules('reason', 'Reason', 'required');
        $this->form_validation->set_rules('urgency', 'Urgency', 'required');

        if ($this->form_validation->run() == FALSE)
        {
            $this->request_leave();
            return;
        }

        $data = array(
            'employee_id' => $this->session->userdata('employee_id'),
            'leave_type'  => $this->input->post('leave_type'),
            'from_date'   => $this->input->post('start_date'),
            'to_date'     => $this->input->post('end_date'),
            'total_days'  => $this->calculateDays($this->input->post('start_date'), $this->input->post('end_date')),
            'reason'      => $this->input->post('reason'),
            'urgency'     => $this->input->post('urgency'),
            'status'      => 'Pending'
        );

        $this->Leave_model->createLeaveRequest($data);

        $leave_id = $this->db->insert_id();

        $this->Audit_model->log('CREATE', 'tbl_leave_requests', $leave_id, NULL, $data);

        // Notify all admins and HR about new leave request
        $employee_name = $this->session->userdata('employee_name');
        $recipients = $this->Notification_model->getAdminAndHRUserIds();
        $from_date = date('d M Y', strtotime($this->input->post('start_date')));
        $to_date = date('d M Y', strtotime($this->input->post('end_date')));

        foreach ($recipients as $recipient_user_id)
        {
            $this->Notification_model->createNotification(array(
                'recipient_user_id' => $recipient_user_id,
                'type'              => 'leave',
                'title'             => 'New Leave Request',
                'message'           => $employee_name . ' submitted a leave request for ' . $from_date . ' to ' . $to_date . '.',
                'related_module'    => 'leave',
                'related_record_id' => $leave_id,
                'target_url'        => 'leave',
                'is_read'           => 0,
                'created_at'        => date('Y-m-d H:i:s')
            ));
        }

        $this->session->set_flashdata('success', 'Leave request submitted successfully.');
        redirect('leave');
    }

    public function manage()
    {
        $this->requirePermission('access_leave');

        $data['title'] = "Manage Leave Requests";

        $status = $this->input->get('status') ?: '';
        $data['leave_requests'] = $this->Leave_model->getAllLeaveRequests($status);
        $data['status'] = $status;

        $this->load->view('leave/manage', $data);
    }

    public function review($leave_id, $action)
    {
        $this->requirePermission('access_leave');

        if (!$this->isAdminOrHR())
        {
            echo json_encode(array('status' => false, 'message' => 'Only admins or HR can review leave requests.'));
            return;
        }

        if (!$this->input->is_ajax_request())
        {
            show_404();
        }

        $leave = $this->Leave_model->getLeaveById($leave_id);

        if (!$leave)
        {
            echo json_encode(array('status' => false, 'message' => 'Leave request not found.'));
            return;
        }

        $hr_remarks = $this->input->post('review_comment') ?: '';
        $status = ($action === 'approve') ? 'Approved' : 'Rejected';

        $this->Leave_model->updateLeaveStatus($leave_id, $status, $this->session->userdata('user_id'), $hr_remarks);

        $this->Audit_model->log('UPDATE', 'tbl_leave_requests', $leave_id, array('status' => $leave->status), array('status' => $status));

        // Create in-app notification for the employee
        $user_id = $this->Notification_model->getUserIdByEmployeeId($leave->employee_id);
        if ($user_id)
        {
            $from_date = date('d M Y', strtotime($leave->from_date));
            $to_date = date('d M Y', strtotime($leave->to_date));

            $this->Notification_model->createNotification(array(
                'recipient_user_id' => $user_id,
                'type'              => 'leave',
                'title'             => 'Leave ' . $status,
                'message'           => 'Your leave request for ' . $from_date . ' to ' . $to_date . ' has been ' . strtolower($status) . '.',
                'related_module'    => 'leave',
                'related_record_id' => $leave_id,
                'target_url'        => 'leave',
                'is_read'           => 0,
                'created_at'        => date('Y-m-d H:i:s')
            ));
        }

        // Send email notification (duplicate protection)
        if ( ! $leave->email_sent)
        {
            if ($this->sendLeaveEmail($leave, $status, $hr_remarks))
            {
                $this->db->where('leave_id', $leave_id)->update('tbl_leave_requests', array('email_sent' => 1));
            }
        }

        echo json_encode(array('status' => true, 'message' => "Leave request {$status} successfully."));
    }

    private function sendLeaveEmail($leave, $status, $comment)
    {
        $from_date = date('d M Y', strtotime($leave->from_date));
        $to_date = date('d M Y', strtotime($leave->to_date));

        $body  = '<p>Your leave request has been <strong>' . htmlspecialchars($status) . '</strong>.</p>';
        $body .= '<table style="width:100%;border-collapse:collapse;margin:16px 0;">';
        $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;width:140px;">Leave Type</td><td style="padding:8px 12px;color:#374151;">' . htmlspecialchars($leave->leave_type) . '</td></tr>';
        $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;">From</td><td style="padding:8px 12px;color:#374151;">' . $from_date . '</td></tr>';
        $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;">To</td><td style="padding:8px 12px;color:#374151;">' . $to_date . '</td></tr>';
        $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;">Status</td><td style="padding:8px 12px;color:#374151;">' . htmlspecialchars($status) . '</td></tr>';
        if ( ! empty($comment))
        {
            $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;">Comment</td><td style="padding:8px 12px;color:#374151;">' . htmlspecialchars($comment) . '</td></tr>';
        }
        $body .= '</table>';

        return $this->worknexusmailer->send(
            $leave->employee_email,
            'WorkNexus - Leave Request ' . $status,
            'Leave ' . $status,
            $leave->employee_name,
            $body,
            site_url('leave'),
            'View in WorkNexus'
        );
    }

    private function calculateDays($start, $end)
    {
        $start_date = new DateTime($start);
        $end_date = new DateTime($end);
        return $start_date->diff($end_date)->days + 1;
    }
}
