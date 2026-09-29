<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI =& get_instance();
?>
<!-- Mobile overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar" aria-label="Main navigation">
    <div class="sidebar-header">
        <a class="sidebar-brand" href="<?= site_url('dashboard'); ?>">
            <img class="justify-content-center" src="<?= base_url('assets/images/logo-icon.svg'); ?>" alt="WorkNexus logo" width="34" height="34">
            <span class="sidebar-brand-text">Work<span class="brand-accent">Nexus</span></span>
        </a>
    </div>
    <nav class="sidebar-nav">
        <ul class="nav flex-column">

            <li class="sidebar-section">Overview</li>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'dashboard' ? 'active' : ''; ?>"
                   href="<?= site_url('dashboard'); ?>"
                   data-title="Dashboard"
                   <?= $CI->router->fetch_class() === 'dashboard' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-grid-1x2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <?php if ($CI->hasPermission('access_employee') || $CI->hasPermission('access_department') || $CI->hasPermission('access_user_management')): ?>
            <li class="sidebar-section">People</li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_employee')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'employee' ? 'active' : ''; ?>"
                   href="<?= site_url('employee'); ?>"
                   data-title="Employees"
                   <?= $CI->router->fetch_class() === 'employee' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-people"></i>
                    <span>Employees</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_department')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'department' ? 'active' : ''; ?>"
                   href="<?= site_url('department'); ?>"
                   data-title="Departments"
                   <?= $CI->router->fetch_class() === 'department' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-building"></i>
                    <span>Departments</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_user_management')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'user' ? 'active' : ''; ?>"
                   href="<?= site_url('user'); ?>"
                   data-title="User Management"
                   <?= $CI->router->fetch_class() === 'user' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-person-gear"></i>
                    <span>User Management</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_attendance') || $CI->hasPermission('access_leave') || $CI->hasPermission('access_meetings')): ?>
            <li class="sidebar-section">Operations</li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_attendance')): ?>
            <li class="nav-item">
                <?php if ($CI->isAdminOrHR()): ?>
                <a class="nav-link <?= $CI->router->fetch_class() === 'attendance' ? 'active' : ''; ?>"
                   href="<?= site_url('attendance/manage'); ?>"
                   data-title="Manage Attendance"
                   <?= $CI->router->fetch_class() === 'attendance' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-clock-history"></i>
                    <span>Manage Attendance</span>
                </a>
                <?php else: ?>
                <a class="nav-link <?= $CI->router->fetch_class() === 'attendance' ? 'active' : ''; ?>"
                   href="<?= site_url('attendance'); ?>"
                   data-title="My Attendance"
                   <?= $CI->router->fetch_class() === 'attendance' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-clock-history"></i>
                    <span>My Attendance</span>
                </a>
                <?php endif; ?>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_leave')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'leave' ? 'active' : ''; ?>"
                   href="<?= site_url('leave'); ?>"
                   data-title="Leave Management"
                   <?= $CI->router->fetch_class() === 'leave' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-calendar2-check"></i>
                    <span>Leave Management</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_meetings')): ?>
            <li class="nav-item">
                <?php if ($CI->isAdminOrHR()): ?>
                <a class="nav-link <?= $CI->router->fetch_class() === 'meetings' ? 'active' : ''; ?>"
                   href="<?= site_url('meetings'); ?>"
                   data-title="Client Meetings"
                   <?= $CI->router->fetch_class() === 'meetings' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-easel"></i>
                    <span>Client Meetings</span>
                </a>
                <?php else: ?>
                <a class="nav-link <?= $CI->router->fetch_class() === 'meetings' ? 'active' : ''; ?>"
                   href="<?= site_url('meetings/my_meetings'); ?>"
                   data-title="My Meetings"
                   <?= $CI->router->fetch_class() === 'meetings' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-calendar3-event"></i>
                    <span>My Meetings</span>
                </a>
                <?php endif; ?>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_reports') || $CI->hasPermission('access_hike_management')): ?>
            <li class="sidebar-section">Business</li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_reports')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'reports' ? 'active' : ''; ?>"
                   href="<?= site_url('reports'); ?>"
                   data-title="Reports"
                   <?= $CI->router->fetch_class() === 'reports' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>Reports</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_hike_management')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'hikes' ? 'active' : ''; ?>"
                   href="<?= site_url('hikes'); ?>"
                   data-title="<?php if ($CI->isHR()): ?>Salary &amp; Compensation<?php elseif ($CI->isAdmin()): ?>Salary &amp; Compensation<?php else: ?>My Compensation<?php endif; ?>"
                   <?= $CI->router->fetch_class() === 'hikes' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-graph-up-arrow"></i>
                    <span><?php if ($CI->isHR()): ?>Salary &amp; Compensation<?php elseif ($CI->isAdmin()): ?>Salary &amp; Compensation<?php else: ?>My Compensation<?php endif; ?></span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_audit_trail') || $CI->hasPermission('access_user_logs')): ?>
            <li class="sidebar-section">System</li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_audit_trail')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'audittrail' ? 'active' : ''; ?>"
                   href="<?= site_url('audittrail'); ?>"
                   data-title="Audit Trail"
                   <?= $CI->router->fetch_class() === 'audittrail' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-shield-lock"></i>
                    <span>Audit Trail</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_user_logs')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'userlogs' ? 'active' : ''; ?>"
                   href="<?= site_url('userlogs'); ?>"
                   data-title="User Logs"
                   <?= $CI->router->fetch_class() === 'userlogs' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-list-check"></i>
                    <span>User Logs</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="sidebar-section">Account</li>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'notifications' ? 'active' : ''; ?>"
                   href="<?= site_url('notifications'); ?>"
                   data-title="Notifications"
                   <?= $CI->router->fetch_class() === 'notifications' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-bell"></i>
                    <span>Notifications</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'profile' ? 'active' : ''; ?>"
                   href="<?= site_url('profile'); ?>"
                   data-title="My Profile"
                   <?= $CI->router->fetch_class() === 'profile' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-person"></i>
                    <span>My Profile</span>
                </a>
            </li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <span class="sidebar-version">WorkNexus &middot; v2.0</span>
    </div>
</aside>
