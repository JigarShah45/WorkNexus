<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hikes extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Hike_model');
        $this->load->model('Attendance_model');
        $this->load->model('Audit_model');
        $this->load->model('Leave_model');
        $this->load->model('Notification_model');
        $this->load->library('form_validation');
        $this->load->library('Worknexusmailer');
    }

    public function index()
    {
        $this->requirePermission('access_hike_management');

        if ($this->isAdmin())
        {
            $this->admin_overview();
        }
        elseif ($this->isHR())
        {
            $this->hr_dashboard();
        }
        else
        {
            $this->my_compensation();
        }
    }

    private function hr_dashboard()
    {
        $data['title'] = "Salary & Compensation";
        $data['employees'] = $this->Hike_model->getEmployeesForHike();
        $data['hike_stats'] = $this->Hike_model->getHikeStats();
        $data['pending_hikes'] = $this->Hike_model->getPendingHikes();
        $data['approved_hikes'] = $this->Hike_model->getApprovedHikesThisYear();
        $data['rejected_hikes'] = $this->Hike_model->getRejectedHikesThisYear();

        foreach ($data['employees'] as $emp)
        {
            $emp->has_pending = $this->Hike_model->hasPendingHike($emp->employee_id);
            $emp->latest_hike = $this->Hike_model->getLatestHikeForEmployee($emp->employee_id);
        }

        $this->load->view('hikes/hr_dashboard', $data);
    }

    private function admin_overview()
    {
        $data['title'] = "Salary & Compensation Overview";
        $data['hike_stats'] = $this->Hike_model->getAdminHikeStats();
        $data['pending_hikes'] = $this->Hike_model->getPendingHikes();
        $data['recent_approved'] = $this->Hike_model->getRecentApprovedHikes(10);
        $data['all_hikes'] = $this->Hike_model->getAllHikes();

        $this->load->view('hikes/admin_overview', $data);
    }

    public function my_compensation()
    {
        $employee_id = $this->session->userdata('employee_id');

        $data['title'] = "My Compensation";
        $data['employee'] = $this->Hike_model->getEmployeeSalaryDetails($employee_id);
        $data['latest_hike'] = $this->Hike_model->getLatestHikeForEmployee($employee_id);
        $data['hike_history'] = $this->Hike_model->getEmployeeHikeHistory($employee_id);

        if (!$data['employee'])
        {
            show_404();
        }

        $this->load->view('hikes/my_compensation', $data);
    }

    public function my_salary()
    {
        $this->my_compensation();
    }

    public function propose($employee_id)
    {
        if (!$this->isHR() || !$this->hasPermission('edit_salary_data'))
        {
            $this->session->setflashdata('error', 'Only HR can propose salary hikes.');
            redirect('hikes');
        }

        if ($this->Hike_model->hasPendingHike($employee_id))
        {
            $this->session->setflashdata('error', 'This employee already has a pending salary hike proposal.');
            redirect('hikes');
        }

        $data['title'] = "Propose Salary Hike";
        $data['employee'] = $this->db->where('employee_id', $employee_id)->get('tbl_employee')->row();

        if (!$data['employee'])
        {
            show_404();
        }

        $from_date = date('Y-01-01');
        $to_date = date('Y-m-d');

        $data['attendance_stats'] = $this->Attendance_model->getAttendanceStats($employee_id, $from_date, $to_date);
        $data['leave_stats'] = $this->calculateLeaveStats($employee_id, $from_date, $to_date);

        $this->load->view('hikes/propose', $data);
    }

    public function store_proposal()
    {
        if (!$this->isHR() || !$this->hasPermission('edit_salary_data'))
        {
            $this->session->setflashdata('error', 'Only HR can create salary hike proposals.');
            redirect('hikes');
        }

        $this->form_validation->set_rules('employee_id', 'Employee', 'required|integer');
        $this->form_validation->set_rules('hike_percentage', 'Hike Percentage', 'required|numeric|greater_than[0]|less_than_equal_to[100]');
        $this->form_validation->set_rules('justification', 'Justification', 'required|min_length[10]');

        if ($this->input->post('effective_date'))
        {
            $this->form_validation->set_rules('effective_date', 'Effective Date', 'valid_date');
        }

        if ($this->form_validation->run() == FALSE)
        {
            $this->propose($this->input->post('employee_id'));
            return;
        }

        $employee_id = (int) $this->input->post('employee_id');

        if ($this->Hike_model->hasPendingHike($employee_id))
        {
            $this->session->setflashdata('error', 'This employee already has a pending salary hike proposal.');
            redirect('hikes');
        }

        $employee = $this->db->where('employee_id', $employee_id)->get('tbl_employee')->row();
        if (!$employee)
        {
            $this->session->setflashdata('error', 'Employee not found.');
            redirect('hikes');
        }

        $hike_pct = (float) $this->input->post('hike_percentage');
        $current_salary = (float) $employee->employee_salary;
        $hike_amount = round($current_salary * $hike_pct / 100, 2);
        $proposed_salary = round($current_salary + $hike_amount, 2);

        $from_date = date('Y-01-01');
        $to_date = date('Y-m-d');

        $att_stats = $this->Attendance_model->getAttendanceStats($employee_id, $from_date, $to_date);
        $leave_stats = $this->calculateLeaveStats($employee_id, $from_date, $to_date);

        $effective_date = $this->input->post('effective_date') ? $this->input->post('effective_date') : NULL;

        $data = array(
            'employee_id'       => $employee_id,
            'current_salary'    => $current_salary,
            'hike_percentage'   => $hike_pct,
            'hike_amount'       => $hike_amount,
            'proposed_salary'   => $proposed_salary,
            'attendance_score'  => $att_stats ? ($att_stats->total_days > 0 ? round(($att_stats->present_days / $att_stats->total_days) * 100, 2) : 0) : 0,
            'overtime_hours'    => $att_stats ? $att_stats->total_overtime : 0,
            'paid_leave_days'   => $leave_stats['paid'],
            'unpaid_leave_days' => $leave_stats['unpaid'],
            'justification'     => $this->input->post('justification'),
            'status'            => 'Pending',
            'proposed_by'       => $this->session->userdata('user_id'),
            'proposed_at'       => date('Y-m-d H:i:s'),
            'effective_date'    => $effective_date,
            'created_at'        => date('Y-m-d H:i:s')
        );

        $this->Hike_model->insertHike($data);
        $hike_id = $this->db->insert_id();

        if ($hike_id)
        {
            $this->Audit_model->log('CREATE', 'tbl_salary_hikes', $hike_id, NULL, array(
                'employee_id'     => $employee_id,
                'current_salary'  => $current_salary,
                'hike_percentage' => $hike_pct,
                'proposed_salary' => $proposed_salary,
                'status'          => 'Pending'
            ));

            $this->session->setflashdata('success', 'Salary hike proposal submitted successfully. Status: Pending review.');
        }
        else
        {
            $this->session->setflashdata('error', 'Failed to submit proposal. Please ensure the database migration has been run.');
        }
        redirect('hikes');
    }

    public function history()
    {
        if (!$this->hasPermission('view_salary_data'))
        {
            $this->session->setflashdata('error', 'You do not have permission to view salary history.');
            redirect('dashboard');
        }

        $data['title'] = $this->isAdmin() ? "Salary History - Organization Overview" : "Hike History";
        $data['hikes'] = $this->Hike_model->getAllHikes();
        $data['is_read_only'] = $this->isAdmin();

        $this->load->view('hikes/history', $data);
    }

    public function pending()
    {
        if (!$this->isHR())
        {
            $this->session->setflashdata('error', 'Only HR can access pending proposals.');
            redirect('hikes');
        }

        $data['title'] = "Pending Salary Hikes";
        $data['pending_hikes'] = $this->Hike_model->getPendingHikes();

        $this->load->view('hikes/pending', $data);
    }

    public function review($hike_id, $action)
    {
        if (!$this->isHR())
        {
            echo json_encode(array('status' => false, 'message' => 'Only HR can review salary hike proposals.'));
            return;
        }

        if (!$this->input->is_ajax_request())
        {
            show_404();
        }

        $hike_id = (int) $hike_id;
        $hike = $this->Hike_model->getHikeById($hike_id);

        if (!$hike)
        {
            echo json_encode(array('status' => false, 'message' => 'Proposal not found.'));
            return;
        }

        if ($hike->status !== 'Pending')
        {
            echo json_encode(array('status' => false, 'message' => 'This proposal has already been processed.'));
            return;
        }

        if ($action === 'approve')
        {
            $this->approve_hike($hike);
        }
        elseif ($action === 'reject')
        {
            $reason = $this->input->post('rejection_reason');
            if (empty($reason))
            {
                echo json_encode(array('status' => false, 'message' => 'Please provide a reason for rejection.'));
                return;
            }
            $this->reject_hike($hike, $reason);
        }
        else
        {
            echo json_encode(array('status' => false, 'message' => 'Invalid action.'));
        }
    }

    private function approve_hike($hike)
    {
        if (!$this->hasPermission('edit_salary_data'))
        {
            echo json_encode(array('status' => false, 'message' => 'You do not have permission to approve salary hikes.'));
            return;
        }

        $employee = $this->db->where('employee_id', $hike->employee_id)->get('tbl_employee')->row();
        if (!$employee)
        {
            echo json_encode(array('status' => false, 'message' => 'Employee not found.'));
            return;
        }

        $current_salary = (float) $employee->employee_salary;
        $hike_pct = (float) $hike->hike_percentage;
        $hike_amount = round($current_salary * $hike_pct / 100, 2);
        $new_salary = round($current_salary + $hike_amount, 2);

        $approved_by = $this->session->userdata('user_id');

        $this->db->trans_start();

        $this->Hike_model->updateEmployeeSalary($hike->employee_id, $new_salary);

        $this->Hike_model->approveHike($hike->hike_id, $approved_by);

        $this->Audit_model->log('UPDATE', 'tbl_salary_hikes', $hike->hike_id,
            array('status' => 'Pending', 'employee_salary' => $current_salary),
            array('status' => 'Approved', 'employee_salary' => $new_salary, 'approved_by' => $approved_by)
        );

        $this->Audit_model->log('UPDATE', 'tbl_employee', $hike->employee_id,
            array('employee_salary' => $current_salary),
            array('employee_salary' => $new_salary)
        );

        $user_id = $this->Notification_model->getUserIdByEmployeeId($hike->employee_id);
        if ($user_id)
        {
            $effective_display = $hike->effective_date ? date('d M Y', strtotime($hike->effective_date)) : date('d M Y');

            $this->Notification_model->createNotification(array(
                'recipient_user_id' => $user_id,
                'type'              => 'salary',
                'title'             => 'Salary Hike Approved',
                'message'           => 'Your salary hike of ' . number_format($hike_pct, 1) . '% has been approved. New salary: ₹' . number_format($new_salary, 0) . '. Effective: ' . $effective_display . '.',
                'related_module'    => 'hike',
                'related_record_id' => $hike->hike_id,
                'target_url'        => 'hikes/my_compensation',
                'is_read'           => 0,
                'created_at'        => date('Y-m-d H:i:s')
            ));
        }

        if (!$hike->email_sent)
        {
            $emp_email = $this->Notification_model->getEmployeeEmail($hike->employee_id);
            $emp_name = $this->Notification_model->getEmployeeName($hike->employee_id);
            if ($emp_email)
            {
                $effective_display = $hike->effective_date ? date('d M Y', strtotime($hike->effective_date)) : date('d M Y');

                $body  = '<p>Congratulations! Your salary hike proposal has been <strong>Approved</strong>.</p>';
                $body .= '<table style="width:100%;border-collapse:collapse;margin:16px 0;">';
                $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;width:140px;">Previous Salary</td><td style="padding:8px 12px;color:#374151;">₹' . number_format($current_salary, 0) . '</td></tr>';
                $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;">Hike</td><td style="padding:8px 12px;color:#16a34a;font-weight:700;">+' . number_format($hike_pct, 1) . '% (+₹' . number_format($hike_amount, 0) . ')</td></tr>';
                $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;">New Salary</td><td style="padding:8px 12px;color:#374151;font-weight:700;">₹' . number_format($new_salary, 0) . '</td></tr>';
                $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;">Effective Date</td><td style="padding:8px 12px;color:#374151;">' . $effective_display . '</td></tr>';
                $body .= '</table>';

                if ($this->worknexusmailer->send(
                    $emp_email,
                    'WorkNexus - Salary Hike Approved',
                    'Congratulations ' . $emp_name,
                    $emp_name,
                    $body,
                    site_url('hikes/my_compensation'),
                    'View in WorkNexus'
                ))
                {
                    $this->db->where('hike_id', $hike->hike_id)->update('tbl_salary_hikes', array('email_sent' => 1));
                }
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            echo json_encode(array('status' => false, 'message' => 'Transaction failed. Please try again.'));
            return;
        }

        echo json_encode(array('status' => true, 'message' => 'Hike approved successfully. Employee salary updated to ₹' . number_format($new_salary, 0) . '.'));
    }

    private function reject_hike($hike, $reason)
    {
        if (!$this->hasPermission('edit_salary_data'))
        {
            echo json_encode(array('status' => false, 'message' => 'You do not have permission to reject salary hikes.'));
            return;
        }

        $rejected_by = $this->session->userdata('user_id');

        $this->db->trans_start();

        $this->Hike_model->rejectHike($hike->hike_id, $rejected_by, $reason);

        $this->Audit_model->log('UPDATE', 'tbl_salary_hikes', $hike->hike_id,
            array('status' => 'Pending'),
            array('status' => 'Rejected', 'rejected_by' => $rejected_by, 'rejection_reason' => $reason)
        );

        $user_id = $this->Notification_model->getUserIdByEmployeeId($hike->employee_id);
        if ($user_id)
        {
            $this->Notification_model->createNotification(array(
                'recipient_user_id' => $user_id,
                'type'              => 'salary',
                'title'             => 'Salary Hike Rejected',
                'message'           => 'Your salary hike proposal of ' . number_format($hike->hike_percentage, 1) . '% has been rejected. Reason: ' . $reason,
                'related_module'    => 'hike',
                'related_record_id' => $hike->hike_id,
                'target_url'        => 'hikes/my_compensation',
                'is_read'           => 0,
                'created_at'        => date('Y-m-d H:i:s')
            ));
        }

        if (!$hike->email_sent)
        {
            $emp_email = $this->Notification_model->getEmployeeEmail($hike->employee_id);
            $emp_name = $this->Notification_model->getEmployeeName($hike->employee_id);
            if ($emp_email)
            {
                $body  = '<p>Your salary hike proposal has been <strong>Rejected</strong>.</p>';
                $body .= '<table style="width:100%;border-collapse:collapse;margin:16px 0;">';
                $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;width:140px;">Proposed Hike</td><td style="padding:8px 12px;color:#374151;">' . number_format($hike->hike_percentage, 1) . '%</td></tr>';
                $body .= '<tr><td style="padding:8px 12px;font-weight:600;color:#374151;">Reason</td><td style="padding:8px 12px;color:#dc2626;">' . htmlspecialchars($reason) . '</td></tr>';
                $body .= '</table>';

                if ($this->worknexusmailer->send(
                    $emp_email,
                    'WorkNexus - Salary Update',
                    'Salary Hike Rejected',
                    $emp_name,
                    $body,
                    site_url('hikes/my_compensation'),
                    'View in WorkNexus'
                ))
                {
                    $this->db->where('hike_id', $hike->hike_id)->update('tbl_salary_hikes', array('email_sent' => 1));
                }
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            echo json_encode(array('status' => false, 'message' => 'Transaction failed. Please try again.'));
            return;
        }

        echo json_encode(array('status' => true, 'message' => 'Hike rejected successfully.'));
    }

    private function calculateLeaveStats($employee_id, $from_date, $to_date)
    {
        $this->db->where('employee_id', $employee_id);
        $this->db->where('from_date >=', $from_date);
        $this->db->where('to_date <=', $to_date);
        $this->db->where('status', 'Approved');
        $leaves = $this->db->get('tbl_leave_requests')->result();

        $paid = 0;
        $unpaid = 0;

        foreach ($leaves as $leave)
        {
            $start = new DateTime($leave->from_date);
            $end = new DateTime($leave->to_date);
            $days = $end->diff($start)->days + 1;

            if ($leave->leave_type === 'Unpaid')
            {
                $unpaid += $days;
            }
            else
            {
                $paid += $days;
            }
        }

        return array('paid' => $paid, 'unpaid' => $unpaid);
    }
}
