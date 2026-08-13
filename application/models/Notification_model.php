<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a new notification for a user
     */
    public function createNotification($data)
    {
        return $this->db->insert('tbl_notifications', $data);
    }

    /**
     * Get notifications for a user with pagination
     */
    public function getUserNotifications($user_id, $limit = 20, $offset = 0, $filter = '')
    {
        $this->db->where('recipient_user_id', $user_id);

        if ($filter === 'unread')
        {
            $this->db->where('is_read', 0);
        }

        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get('tbl_notifications')->result();
    }

    /**
     * Get recent notifications for navbar dropdown
     */
    public function getRecentNotifications($user_id, $limit = 5)
    {
        $this->db->where('recipient_user_id', $user_id);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('tbl_notifications')->result();
    }

    /**
     * Get unread notification count for a user
     */
    public function getUnreadCount($user_id)
    {
        $this->db->where('recipient_user_id', $user_id);
        $this->db->where('is_read', 0);
        return $this->db->count_all_results('tbl_notifications');
    }

    /**
     * Mark a single notification as read
     */
    public function markAsRead($notification_id, $user_id)
    {
        $this->db->where('notification_id', $notification_id);
        $this->db->where('recipient_user_id', $user_id);
        return $this->db->update('tbl_notifications', array(
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($user_id)
    {
        $this->db->where('recipient_user_id', $user_id);
        $this->db->where('is_read', 0);
        return $this->db->update('tbl_notifications', array(
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Get total notification count for a user
     */
    public function getTotalCount($user_id)
    {
        $this->db->where('recipient_user_id', $user_id);
        return $this->db->count_all_results('tbl_notifications');
    }

    /**
     * Get the user_id linked to an employee_id
     */
    public function getUserIdByEmployeeId($employee_id)
    {
        $this->db->where('employee_id', $employee_id);
        $this->db->where('status', 'Active');
        $row = $this->db->get('tbl_users')->row();
        return $row ? $row->user_id : NULL;
    }

    /**
     * Get all admin user IDs
     */
    public function getAdminUserIds()
    {
        $this->db->where('role', 'Admin');
        $this->db->where('status', 'Active');
        $result = $this->db->get('tbl_users')->result();
        return array_column($result, 'user_id');
    }

    /**
     * Get all HR user IDs
     */
    public function getHRUserIds()
    {
        $this->db->where('role', 'HR');
        $this->db->where('status', 'Active');
        $result = $this->db->get('tbl_users')->result();
        return array_column($result, 'user_id');
    }

    /**
     * Get all admin and HR user IDs (for leave notifications)
     */
    public function getAdminAndHRUserIds()
    {
        $this->db->where_in('role', array('Admin', 'HR'));
        $this->db->where('status', 'Active');
        $result = $this->db->get('tbl_users')->result();
        return array_column($result, 'user_id');
    }

    /**
     * Get employee email by employee_id
     */
    public function getEmployeeEmail($employee_id)
    {
        $this->db->where('employee_id', $employee_id);
        $row = $this->db->get('tbl_employee')->row();
        return $row ? $row->employee_email : NULL;
    }

    /**
     * Get employee name by employee_id
     */
    public function getEmployeeName($employee_id)
    {
        $this->db->where('employee_id', $employee_id);
        $row = $this->db->get('tbl_employee')->row();
        return $row ? $row->employee_name : NULL;
    }
}
