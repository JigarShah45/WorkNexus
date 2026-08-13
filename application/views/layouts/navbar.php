<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI =& get_instance();
$employee_name = $this->session->userdata('employee_name') ?: 'User';
$role = $this->session->userdata('role') ?: 'User';
$last_login = $this->session->userdata('last_login_display');
$profile_image = $this->session->userdata('profile_image');

// Load notification count
$CI->load->model('Notification_model');
$unread_count = $CI->Notification_model->getUnreadCount($CI->session->userdata('user_id'));
$recent_notifications = $CI->Notification_model->getRecentNotifications($CI->session->userdata('user_id'), 5);

// Time formatting helper for notifications (if not already defined)
if ( ! function_exists('notification_format_time'))
{
    function notification_format_time($datetime) {
        $dt = new DateTime($datetime, new DateTimeZone('Asia/Kolkata'));
        $now = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
        $diff = $now->getTimestamp() - $dt->getTimestamp();
        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . ' min ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hour' . (floor($diff / 3600) > 1 ? 's' : '') . ' ago';
        if ($diff < 172800) return 'Yesterday';
        return $dt->format('d M Y, h:i A') . ' IST';
    }
}
?>
<nav class="top-header" id="topHeader">
    <div class="top-header-inner">

        <!-- Hamburger (controls sidebar) -->
        <button class="header-hamburger" id="mobileMenuBtn" type="button" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>

        <!-- Project Name (hidden on mobile, shown on tablet+) -->
        <a class="header-brand" href="<?= site_url('dashboard'); ?>">
            <img src="<?= base_url('assets/images/logo-icon.svg'); ?>" alt="WorkNexus" width="24" height="24">
            <span class="header-brand-text">WorkNexus</span>
        </a>

        <!-- Global Search -->
        <div class="header-search" id="headerSearch">
            <i class="bi bi-search"></i>
            <input type="text"
                   id="globalNavSearch"
                   placeholder="Search pages..."
                   autocomplete="off"
                   aria-label="Search pages">
            <span class="header-search-shortcut">Ctrl K</span>
            <!-- Search Dropdown -->
            <div class="search-dropdown" id="searchDropdown"></div>
        </div>

        <!-- Right Section -->
        <div class="header-actions">

            <!-- Last Login -->
            <div class="header-last-login">
                <span class="header-last-login-label">Last Login</span>
                <span class="header-last-login-time">
                    <?= $last_login ? date('d M Y, h:i A', strtotime($last_login)) : 'First login' ?>
                </span>
            </div>

            <!-- Fullscreen Toggle -->
            <button class="header-icon-btn" id="fullscreenBtn" type="button" title="Toggle fullscreen" aria-label="Toggle fullscreen">
                <i class="bi bi-arrows-fullscreen" id="fullscreenIcon"></i>
            </button>

            <!-- Theme Toggle -->
            <button class="header-icon-btn" id="themeToggleButton" type="button" title="Toggle theme" aria-label="Toggle theme">
                <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
            </button>

            <!-- Notification Bell -->
            <div class="header-notification" id="headerNotificationDropdown">
                <button class="header-icon-btn notification-bell-btn" id="notificationBellBtn" type="button" title="Notifications" aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                    <?php if ($unread_count > 0): ?>
                        <span class="notification-badge" id="notificationBadge"><?= $unread_count > 9 ? '9+' : $unread_count ?></span>
                    <?php endif; ?>
                </button>
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-dropdown-header">
                        <span class="notification-dropdown-title">Notifications</span>
                        <?php if ($unread_count > 0): ?>
                            <button type="button" class="notification-mark-all-btn" id="dropdownMarkAllRead">Mark all read</button>
                        <?php endif; ?>
                    </div>
                    <div class="notification-dropdown-list" id="notificationDropdownList">
                        <?php if (empty($recent_notifications)): ?>
                            <div class="notification-dropdown-empty">
                                <i class="bi bi-bell-slash"></i>
                                <p>You're all caught up.<br>No new notifications.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_notifications as $n): ?>
                                <a href="<?= $n->target_url ? site_url($n->target_url) : '#'; ?>"
                                   class="notification-dropdown-item <?= $n->is_read ? '' : 'unread' ?>"
                                   data-id="<?= $n->notification_id ?>"
                                   <?= $n->target_url ? '' : 'onclick="return false;"' ?>>
                                    <div class="notification-dropdown-dot <?= $n->is_read ? '' : 'active' ?>"></div>
                                    <div class="notification-dropdown-content">
                                        <div class="notification-dropdown-item-title"><?= htmlspecialchars($n->title) ?></div>
                                        <div class="notification-dropdown-item-msg"><?= htmlspecialchars($n->message) ?></div>
                                        <div class="notification-dropdown-item-time"><?= notification_format_time($n->created_at) ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="notification-dropdown-footer">
                        <a href="<?= site_url('notifications'); ?>">View All Notifications</a>
                    </div>
                </div>
            </div>

            <!-- User Profile Dropdown -->
            <div class="header-user" id="headerUserDropdown">
                <div class="header-user-trigger" role="button" tabindex="0" aria-expanded="false" aria-haspopup="true">
                    <div class="header-avatar">
                        <?php if (!empty($profile_image)): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($profile_image) ?>" alt="<?= htmlspecialchars($employee_name) ?>">
                        <?php else: ?>
                            <i class="bi bi-person-fill"></i>
                        <?php endif; ?>
                    </div>
                    <div class="header-user-info">
                        <span class="header-user-name"><?= htmlspecialchars($employee_name) ?></span>
                        <span class="header-user-role"><?= strtoupper(htmlspecialchars($role)) ?></span>
                    </div>
                    <i class="bi bi-chevron-down header-user-arrow"></i>
                </div>
                <div class="header-dropdown" id="userDropdown">
                    <a class="header-dropdown-item" href="<?= site_url('profile'); ?>">
                        <i class="bi bi-person"></i> My Profile
                    </a>
                    <a class="header-dropdown-item" href="<?= site_url('profile'); ?>">
                        <i class="bi bi-key"></i> Change Password
                    </a>
                    <div class="header-dropdown-divider"></div>
                    <a class="header-dropdown-item header-dropdown-danger" href="<?= site_url('auth/logout'); ?>">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>

        </div>
    </div>
</nav>
