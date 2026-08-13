<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meetings extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Meeting_model');
        $this->load->model('Audit_model');
        $this->load->model('Employee_model');
        $this->load->model('Notification_model');
        $this->load->library('form_validation');
        $this->load->library('Worknexusmailer');
    }

    // ========================================================================
    // ADMIN METHODS
    // ========================================================================

    /**
     * Admin: List all meetings
     */
    public function index()
    {
        $this->requirePermission('access_meetings');

        if (!$this->isAdminOrHR())
        {
            redirect('meetings/my_meetings');
        }

        $data['title'] = "Client Meetings";
        $data['meetings'] = $this->Meeting_model->getAllMeetings();

        $this->load->view('meetings/index', $data);
    }

    /**
     * Admin: Show add meeting form
     */
    public function add()
    {
        $this->requirePermission('access_meetings');

        if (!$this->isAdmin())
        {
            show_404();
        }

        $data['title'] = "Add Meeting";
        $data['employees'] = $this->Employee_model->getEmployees();
        $data['meeting_employees'] = array();

        $this->load->view('meetings/add', $data);
    }

    /**
     * Admin: Create meeting
     */
    public function store()
    {
        $this->requirePermission('access_meetings');

        if (!$this->isAdmin())
        {
            show_404();
        }

        $this->form_validation->set_rules('client_name', 'Client Name', 'required');
        $this->form_validation->set_rules('meeting_title', 'Meeting Title', 'required');
        $this->form_validation->set_rules('meeting_date', 'Meeting Date', 'required');
        $this->form_validation->set_rules('meeting_location', 'Location', 'required');

        if ($this->form_validation->run() == FALSE)
        {
            $this->add();
            return;
        }

        $meeting_data = array(
            'client_name'      => $this->input->post('client_name'),
            'meeting_title'    => $this->input->post('meeting_title'),
            'meeting_date'     => $this->input->post('meeting_date'),
            'meeting_location' => $this->input->post('meeting_location'),
            'description'      => $this->input->post('description'),
            'created_by'       => $this->session->userdata('user_id'),
            'created_at'       => date('Y-m-d H:i:s')
        );

        $meeting_id = $this->Meeting_model->insertMeeting($meeting_data);

        // Save employees
        $employee_ids = $this->input->post('employee_ids');
        if (!empty($employee_ids))
        {
            $this->Meeting_model->saveMeetingEmployees($meeting_id, $employee_ids);

            // Notify each assigned employee
            $meeting_date = date('d M Y', strtotime($this->input->post('meeting_date')));
            $meeting_time = date('h:i A', strtotime($this->input->post('meeting_date')));

            foreach ($employee_ids as $emp_id)
            {
                $emp_user_id = $this->Notification_model->getUserIdByEmployeeId($emp_id);
                if ( ! $emp_user_id) continue;

                $this->Notification_model->createNotification(array(
                    'recipient_user_id' => $emp_user_id,
                    'type'              => 'meeting',
                    'title'             => 'New Meeting Assigned',
                    'message'           => 'You have been assigned to "' . htmlspecialchars($this->input->post('meeting_title')) . '" on ' . $meeting_date . ' at ' . $meeting_time . ' IST.',
                    'related_module'    => 'meeting',
                    'related_record_id' => $meeting_id,
                    'target_url'        => 'meetings/employee_view/' . $meeting_id,
                    'is_read'           => 0,
                    'created_at'        => date('Y-m-d H:i:s')
                ));

                // Send email
                $emp_email = $this->Notification_model->getEmployeeEmail($emp_id);
                $emp_name = $this->Notification_model->getEmployeeName($emp_id);
                if ($emp_email)
                {
                    $body  = '<p>You have been assigned to a new client meeting.</p>';
                    $body .= '<table style="width:100%;border-collapse:collapse;margin:16px 0;">';
                    $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;width:100px;">Meeting</td><td style="padding:8px 12px;color:#374151;">' . htmlspecialchars($this->input->post('meeting_title')) . '</td></tr>';
                    $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;">Client</td><td style="padding:8px 12px;color:#374151;">' . htmlspecialchars($this->input->post('client_name')) . '</td></tr>';
                    $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;">Date</td><td style="padding:8px 12px;color:#374151;">' . $meeting_date . '</td></tr>';
                    $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;">Time</td><td style="padding:8px 12px;color:#374151;">' . $meeting_time . ' IST</td></tr>';
                    $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;">Location</td><td style="padding:8px 12px;color:#374151;">' . htmlspecialchars($this->input->post('meeting_location')) . '</td></tr>';
                    $body .= '</table>';

                    $this->worknexusmailer->send(
                        $emp_email,
                        'WorkNexus - New Meeting Assigned',
                        'New Meeting Assigned',
                        $emp_name,
                        $body,
                        site_url('meetings/employee_view/' . $meeting_id),
                        'View in WorkNexus'
                    );
                }
            }
        }

        // Handle file uploads
        $this->uploadMeetingFiles($meeting_id);

        $this->Audit_model->log('CREATE', 'tbl_client_meetings', $meeting_id, NULL, $meeting_data);

        $this->session->set_flashdata('success', 'Meeting created successfully.');
        redirect('meetings');
    }

    /**
     * Admin: Show meeting detail
     */
    public function view($id)
    {
        $this->requirePermission('access_meetings');

        $meeting = $this->Meeting_model->getMeetingById($id);

        if (!$meeting) show_404();

        // Employees can only view meetings they are assigned to
        if (!$this->isAdmin())
        {
            $employee_id = $this->session->userdata('employee_id');
            if (!$this->Meeting_model->isEmployeeAssignedToMeeting($employee_id, $id))
            {
                $this->session->set_flashdata('error', 'You do not have access to this meeting.');
                redirect('meetings/my_meetings');
            }
            // Redirect employees to their view
            redirect('meetings/employee_view/' . $id);
        }

        $data['title'] = "Meeting Details";
        $data['meeting'] = $meeting;
        $data['meeting_employees'] = $this->Meeting_model->getMeetingEmployees($id);
        $data['meeting_files'] = $this->Meeting_model->getMeetingFiles($id);

        $this->load->view('meetings/view', $data);
    }

    /**
     * Admin: Show edit meeting form
     */
    public function edit($id)
    {
        $this->requirePermission('access_meetings');

        if (!$this->isAdmin())
        {
            show_404();
        }

        $meeting = $this->Meeting_model->getMeetingById($id);

        if (!$meeting) show_404();

        $data['title'] = "Edit Meeting";
        $data['meeting'] = $meeting;
        $data['employees'] = $this->Employee_model->getEmployees();

        $meeting_emps = $this->Meeting_model->getMeetingEmployees($id);
        $data['meeting_employees'] = array_column($meeting_emps, 'employee_id');

        $data['meeting_files'] = $this->Meeting_model->getMeetingFiles($id);

        $this->load->view('meetings/edit', $data);
    }

    /**
     * Admin: Update meeting
     */
    public function update($id)
    {
        $this->requirePermission('access_meetings');

        if (!$this->isAdmin())
        {
            show_404();
        }

        $this->form_validation->set_rules('client_name', 'Client Name', 'required');
        $this->form_validation->set_rules('meeting_title', 'Meeting Title', 'required');
        $this->form_validation->set_rules('meeting_date', 'Meeting Date', 'required');
        $this->form_validation->set_rules('meeting_location', 'Location', 'required');

        if ($this->form_validation->run() == FALSE)
        {
            $this->edit($id);
            return;
        }

        $meeting_data = array(
            'client_name'      => $this->input->post('client_name'),
            'meeting_title'    => $this->input->post('meeting_title'),
            'meeting_date'     => $this->input->post('meeting_date'),
            'meeting_location' => $this->input->post('meeting_location'),
            'description'      => $this->input->post('description')
        );

        $old = $this->Meeting_model->getMeetingById($id);

        $this->Meeting_model->updateMeeting($id, $meeting_data);

        $employee_ids = $this->input->post('employee_ids');
        if (!empty($employee_ids))
        {
            $this->Meeting_model->saveMeetingEmployees($id, $employee_ids);
        }

        // Notify assigned employees about meeting update
        $meeting_employees = $this->Meeting_model->getMeetingEmployees($id);
        $meeting_date = date('d M Y', strtotime($this->input->post('meeting_date')));
        $meeting_time = date('h:i A', strtotime($this->input->post('meeting_date')));

        foreach ($meeting_employees as $me)
        {
            $emp_user_id = $this->Notification_model->getUserIdByEmployeeId($me->employee_id);
            if ( ! $emp_user_id) continue;

            $this->Notification_model->createNotification(array(
                'recipient_user_id' => $emp_user_id,
                'type'              => 'meeting',
                'title'             => 'Meeting Updated',
                'message'           => '"' . htmlspecialchars($this->input->post('meeting_title')) . '" has been updated. Date: ' . $meeting_date . ' at ' . $meeting_time . ' IST.',
                'related_module'    => 'meeting',
                'related_record_id' => $id,
                'target_url'        => 'meetings/employee_view/' . $id,
                'is_read'           => 0,
                'created_at'        => date('Y-m-d H:i:s')
            ));

            // Send email (duplicate protection)
            if ( ! $me->email_sent)
            {
                $emp_email = $this->Notification_model->getEmployeeEmail($me->employee_id);
                $emp_name = $this->Notification_model->getEmployeeName($me->employee_id);
                if ($emp_email)
                {
                    $body  = '<p>The meeting you are assigned to has been updated.</p>';
                    $body .= '<table style="width:100%;border-collapse:collapse;margin:16px 0;">';
                    $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;width:100px;">Meeting</td><td style="padding:8px 12px;color:#374151;">' . htmlspecialchars($this->input->post('meeting_title')) . '</td></tr>';
                    $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;">Date</td><td style="padding:8px 12px;color:#374151;">' . $meeting_date . '</td></tr>';
                    $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;">Time</td><td style="padding:8px 12px;color:#374151;">' . $meeting_time . ' IST</td></tr>';
                    $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;">Location</td><td style="padding:8px 12px;color:#374151;">' . htmlspecialchars($this->input->post('meeting_location')) . '</td></tr>';
                    $body .= '</table>';

                    if ($this->worknexusmailer->send(
                        $emp_email,
                        'WorkNexus - Meeting Updated',
                        'Meeting Updated',
                        $emp_name,
                        $body,
                        site_url('meetings/employee_view/' . $id),
                        'View in WorkNexus'
                    ))
                    {
                        $this->db->where('id', $me->id)->update('tbl_meeting_employees', array('email_sent' => 1));
                    }
                }
            }
        }

        $this->uploadMeetingFiles($id);

        $this->Audit_model->log('UPDATE', 'tbl_client_meetings', $id, (array)$old, $meeting_data);

        $this->session->set_flashdata('success', 'Meeting updated successfully.');
        redirect('meetings');
    }

    /**
     * Admin: Delete meeting
     */
    public function delete($id)
    {
        $this->requirePermission('access_meetings');

        if (!$this->isAdmin())
        {
            show_404();
        }

        if (!$this->input->is_ajax_request()) show_404();

        $old = $this->Meeting_model->getMeetingById($id);

        if ( ! $old)
        {
            echo json_encode(array('status' => false, 'message' => 'Meeting not found.'));
            return;
        }

        // Collect affected employees BEFORE deletion
        $meeting_employees = $this->Meeting_model->getMeetingEmployees($id);

        // Mark email_sent to prevent duplicates on rapid double-click
        $this->db->where('meeting_id', $id)->update('tbl_meeting_employees', array('email_sent' => 1));

        $this->Meeting_model->deleteMeeting($id);

        $this->Audit_model->log('DELETE', 'tbl_client_meetings', $id, (array)$old, NULL);

        // Notify affected employees about meeting cancellation
        foreach ($meeting_employees as $me)
        {
            $emp_user_id = $this->Notification_model->getUserIdByEmployeeId($me->employee_id);
            if ( ! $emp_user_id) continue;

            $meeting_date = date('d M Y', strtotime($old->meeting_date));
            $meeting_time = date('h:i A', strtotime($old->meeting_date));

            $this->Notification_model->createNotification(array(
                'recipient_user_id' => $emp_user_id,
                'type'              => 'meeting',
                'title'             => 'Meeting Cancelled',
                'message'           => '"' . htmlspecialchars($old->meeting_title) . '" scheduled for ' . $meeting_date . ' at ' . $meeting_time . ' IST has been cancelled.',
                'related_module'    => 'meeting',
                'related_record_id' => $id,
                'target_url'        => 'meetings/my_meetings',
                'is_read'           => 0,
                'created_at'        => date('Y-m-d H:i:s')
            ));

            // Send email
            $emp_email = $this->Notification_model->getEmployeeEmail($me->employee_id);
            $emp_name = $this->Notification_model->getEmployeeName($me->employee_id);
            if ($emp_email)
            {
                $body  = '<p>The meeting you were assigned to has been cancelled.</p>';
                $body .= '<table style="width:100%;border-collapse:collapse;margin:16px 0;">';
                $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;width:100px;">Meeting</td><td style="padding:8px 12px;color:#374151;">' . htmlspecialchars($old->meeting_title) . '</td></tr>';
                $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;">Date</td><td style="padding:8px 12px;color:#374151;">' . $meeting_date . '</td></tr>';
                $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;">Time</td><td style="padding:8px 12px;color:#374151;">' . $meeting_time . ' IST</td></tr>';
                $body .= '</table>';
                $body .= '<p style="color:#6B7280;font-size:13px;">This meeting has been removed from your schedule.</p>';

                $this->worknexusmailer->send(
                    $emp_email,
                    'WorkNexus - Meeting Cancelled',
                    'Meeting Cancelled',
                    $emp_name,
                    $body,
                    site_url('meetings/my_meetings'),
                    'View My Meetings'
                );
            }
        }

        echo json_encode(array('status' => true, 'message' => 'Meeting deleted successfully.'));
    }

    /**
     * Admin: Delete file
     */
    public function delete_file($file_id)
    {
        $this->requirePermission('access_meetings');

        if (!$this->isAdmin())
        {
            show_404();
        }

        if (!$this->input->is_ajax_request()) show_404();

        $this->Meeting_model->deleteFile($file_id);

        echo json_encode(array('status' => true, 'message' => 'File deleted.'));
    }

    // ========================================================================
    // EMPLOYEE METHODS
    // ========================================================================

    /**
     * Employee: My meetings (assigned to me)
     */
    public function my_meetings()
    {
        $this->requirePermission('access_meetings');

        $employee_id = $this->session->userdata('employee_id');

        if (!$employee_id)
        {
            $this->session->set_flashdata('error', 'Employee profile not found.');
            redirect('dashboard');
        }

        $all_meetings = $this->Meeting_model->getMeetingsByEmployeeId($employee_id);

        // Split into upcoming and completed
        $now = date('Y-m-d H:i:s');
        $upcoming = array();
        $completed = array();

        foreach ($all_meetings as $meeting)
        {
            if ($meeting->meeting_date >= $now)
            {
                $upcoming[] = $meeting;
            }
            else
            {
                $completed[] = $meeting;
            }
        }

        $data['title'] = "My Meetings";
        $data['upcoming'] = $upcoming;
        $data['completed'] = $completed;

        $this->load->view('meetings/my_meetings', $data);
    }

    /**
     * Employee: View meeting detail (read-only, assigned meetings only)
     */
    public function employee_view($id)
    {
        $this->requirePermission('access_meetings');

        $employee_id = $this->session->userdata('employee_id');

        if (!$employee_id)
        {
            $this->session->set_flashdata('error', 'Employee profile not found.');
            redirect('dashboard');
        }

        $meeting = $this->Meeting_model->getMeetingById($id);

        if (!$meeting) show_404();

        // Authorization: employee must be assigned to this meeting
        if (!$this->Meeting_model->isEmployeeAssignedToMeeting($employee_id, $id))
        {
            $this->session->set_flashdata('error', 'You do not have access to this meeting.');
            redirect('meetings/my_meetings');
        }

        $data['title'] = "Meeting Details";
        $data['meeting'] = $meeting;
        $data['meeting_employees'] = $this->Meeting_model->getMeetingEmployees($id);
        $data['meeting_files'] = $this->Meeting_model->getMeetingFiles($id);

        $this->load->view('meetings/employee_view', $data);
    }

    // ========================================================================
    // SHARED METHODS
    // ========================================================================

    /**
     * Download file (Admin: any file; Employee: only files from assigned meetings)
     */
    public function download_file($file_id)
    {
        $this->requirePermission('access_meetings');

        $file = $this->Meeting_model->getFileById($file_id);

        if (!$file) show_404();

        // Employees can only download files from meetings they are assigned to
        if (!$this->isAdmin())
        {
            $employee_id = $this->session->userdata('employee_id');
            if (!$this->Meeting_model->isEmployeeAssignedToMeeting($employee_id, $file->meeting_id))
            {
                $this->session->set_flashdata('error', 'You do not have access to this file.');
                redirect('meetings/my_meetings');
            }
        }

        $mime_types = array(
            'pdf'  => 'application/pdf',
            'txt'  => 'text/plain',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        );

        $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
        $mime = isset($mime_types[$ext]) ? $mime_types[$ext] : 'application/octet-stream';

        header("Content-Type: {$mime}");
        header("Content-Disposition: attachment; filename=\"{$file->file_name}\"");
        header("Content-Length: " . strlen($file->file_data));

        echo $file->file_data;
    }

    // ========================================================================
    // PRIVATE HELPERS
    // ========================================================================

    private function uploadMeetingFiles($meeting_id)
    {
        // Upload minutes file
        if (isset($_FILES['minutes_file']) && $_FILES['minutes_file']['error'] == 0)
        {
            $allowed = array('txt', 'pdf');
            $ext = strtolower(pathinfo($_FILES['minutes_file']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed))
            {
                $data = file_get_contents($_FILES['minutes_file']['tmp_name']);
                $this->Meeting_model->saveFile($meeting_id, $_FILES['minutes_file']['name'], $_FILES['minutes_file']['type'], 'minutes', $data);
            }
        }

        // Upload presentation file
        if (isset($_FILES['presentation_file']) && $_FILES['presentation_file']['error'] == 0)
        {
            $allowed = array('ppt', 'pptx');
            $ext = strtolower(pathinfo($_FILES['presentation_file']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed))
            {
                $data = file_get_contents($_FILES['presentation_file']['tmp_name']);
                $this->Meeting_model->saveFile($meeting_id, $_FILES['presentation_file']['name'], $_FILES['presentation_file']['type'], 'presentation', $data);
            }
        }
    }
}
