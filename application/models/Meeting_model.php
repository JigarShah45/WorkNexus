<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meeting_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    public function getAllMeetings()
    {
        $this->db->select('tbl_client_meetings.*, tbl_users.username as created_by_name');
        $this->db->from('tbl_client_meetings');
        $this->db->join('tbl_users', 'tbl_users.user_id = tbl_client_meetings.created_by', 'left');
        $this->db->order_by('tbl_client_meetings.meeting_date', 'DESC');
        return $this->db->get()->result();
    }

    public function getMeetingById($meeting_id)
    {
        $this->db->select('tbl_client_meetings.*, tbl_users.username as created_by_name');
        $this->db->from('tbl_client_meetings');
        $this->db->join('tbl_users', 'tbl_users.user_id = tbl_client_meetings.created_by', 'left');
        $this->db->where('tbl_client_meetings.meeting_id', $meeting_id);
        return $this->db->get()->row();
    }

    public function insertMeeting($data)
    {
        $this->db->insert('tbl_client_meetings', $data);
        return $this->db->insert_id();
    }

    public function updateMeeting($meeting_id, $data)
    {
        $this->db->where('meeting_id', $meeting_id);
        return $this->db->update('tbl_client_meetings', $data);
    }

    public function deleteMeeting($meeting_id)
    {
        // Delete associated files
        $this->db->where('meeting_id', $meeting_id);
        $this->db->delete('tbl_meeting_files');

        // Delete employee mappings
        $this->db->where('meeting_id', $meeting_id);
        $this->db->delete('tbl_meeting_employees');

        // Delete meeting
        $this->db->where('meeting_id', $meeting_id);
        return $this->db->delete('tbl_client_meetings');
    }

    public function saveMeetingEmployees($meeting_id, $employee_ids)
    {
        $this->db->where('meeting_id', $meeting_id);
        $this->db->delete('tbl_meeting_employees');

        $data = array();
        foreach ($employee_ids as $emp_id)
        {
            $data[] = array(
                'meeting_id'  => $meeting_id,
                'employee_id' => $emp_id
            );
        }

        if (!empty($data))
        {
            return $this->db->insert_batch('tbl_meeting_employees', $data);
        }
        return TRUE;
    }

    public function getMeetingEmployees($meeting_id)
    {
        $this->db->select('tbl_meeting_employees.*, tbl_employee.employee_name');
        $this->db->from('tbl_meeting_employees');
        $this->db->join('tbl_employee', 'tbl_employee.employee_id = tbl_meeting_employees.employee_id');
        $this->db->where('tbl_meeting_employees.meeting_id', $meeting_id);
        return $this->db->get()->result();
    }

    public function saveFile($meeting_id, $file_name, $file_type, $file_category, $file_data)
    {
        $data = array(
            'meeting_id'    => $meeting_id,
            'file_name'     => $file_name,
            'file_type'     => $file_type,
            'file_category' => $file_category,
            'file_data'     => $file_data,
            'uploaded_at'   => date('Y-m-d H:i:s')
        );
        return $this->db->insert('tbl_meeting_files', $data);
    }

    public function getMeetingFiles($meeting_id)
    {
        $this->db->select('file_id, file_name, file_type, file_category, uploaded_at');
        $this->db->from('tbl_meeting_files');
        $this->db->where('meeting_id', $meeting_id);
        return $this->db->get()->result();
    }

    public function getFileById($file_id)
    {
        return $this->db->where('file_id', $file_id)
            ->get('tbl_meeting_files')
            ->row();
    }

    public function deleteFile($file_id)
    {
        return $this->db->where('file_id', $file_id)
            ->delete('tbl_meeting_files');
    }

    /**
     * Get all meetings assigned to a specific employee
     */
    public function getMeetingsByEmployeeId($employee_id)
    {
        $this->db->select('tbl_client_meetings.*, tbl_users.username as created_by_name');
        $this->db->from('tbl_client_meetings');
        $this->db->join('tbl_users', 'tbl_users.user_id = tbl_client_meetings.created_by', 'left');
        $this->db->join('tbl_meeting_employees', 'tbl_meeting_employees.meeting_id = tbl_client_meetings.meeting_id');
        $this->db->where('tbl_meeting_employees.employee_id', $employee_id);
        $this->db->order_by('tbl_client_meetings.meeting_date', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Check if an employee is assigned to a specific meeting
     */
    public function isEmployeeAssignedToMeeting($employee_id, $meeting_id)
    {
        return $this->db->where('employee_id', $employee_id)
            ->where('meeting_id', $meeting_id)
            ->count_all_results('tbl_meeting_employees') > 0;
    }
}
