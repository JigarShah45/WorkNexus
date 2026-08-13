<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Notification_model');
    }

    /**
     * Full notifications page
     */
    public function index()
    {
        $user_id = $this->session->userdata('user_id');
        $filter = $this->input->get('filter') ?: 'all';

        $per_page = 20;
        $page = max(1, (int) $this->input->get('page'));
        $offset = ($page - 1) * $per_page;

        $data['title'] = 'Notifications';
        $data['notifications'] = $this->Notification_model->getUserNotifications($user_id, $per_page, $offset, $filter);
        $data['total'] = $this->Notification_model->getTotalCount($user_id);
        $data['unread_count'] = $this->Notification_model->getUnreadCount($user_id);
        $data['filter'] = $filter;
        $data['current_page'] = $page;
        $data['per_page'] = $per_page;
        $data['total_pages'] = ceil($data['total'] / $per_page);

        $this->load->view('notifications/index', $data);
    }

    /**
     * AJAX: Get recent notifications for navbar dropdown
     */
    public function recent()
    {
        if ( ! $this->input->is_ajax_request())
        {
            show_404();
        }

        $user_id = $this->session->userdata('user_id');
        $notifications = $this->Notification_model->getRecentNotifications($user_id, 5);
        $unread = $this->Notification_model->getUnreadCount($user_id);

        echo json_encode(array(
            'status' => TRUE,
            'notifications' => $notifications,
            'unread_count' => $unread
        ));
    }

    /**
     * AJAX: Get unread count only (lightweight for polling)
     */
    public function unread_count()
    {
        if ( ! $this->input->is_ajax_request())
        {
            show_404();
        }

        $user_id = $this->session->userdata('user_id');
        $count = $this->Notification_model->getUnreadCount($user_id);

        echo json_encode(array(
            'status' => TRUE,
            'count' => $count
        ));
    }

    /**
     * AJAX: Get status (unread count + latest notifications) for polling
     */
    public function status()
    {
        if ( ! $this->input->is_ajax_request())
        {
            show_404();
        }

        $user_id = $this->session->userdata('user_id');
        $unread = $this->Notification_model->getUnreadCount($user_id);
        $notifications = $this->Notification_model->getRecentNotifications($user_id, 5);

        echo json_encode(array(
            'status'      => TRUE,
            'unread_count' => $unread,
            'notifications' => $notifications
        ));
    }

    /**
     * AJAX: Mark a single notification as read
     */
    public function mark_read($notification_id)
    {
        if ( ! $this->input->is_ajax_request())
        {
            show_404();
        }

        $user_id = $this->session->userdata('user_id');
        $result = $this->Notification_model->markAsRead($notification_id, $user_id);
        $unread = $this->Notification_model->getUnreadCount($user_id);

        echo json_encode(array(
            'status' => (bool) $result,
            'unread_count' => $unread
        ));
    }

    /**
     * AJAX: Mark all notifications as read
     */
    public function mark_all_read()
    {
        if ( ! $this->input->is_ajax_request())
        {
            show_404();
        }

        $user_id = $this->session->userdata('user_id');
        $this->Notification_model->markAllAsRead($user_id);
        $unread = $this->Notification_model->getUnreadCount($user_id);

        echo json_encode(array(
            'status' => TRUE,
            'unread_count' => $unread
        ));
    }
}
