<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI =& get_instance();
?>
<!-- Mobile overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a class="sidebar-brand" href="<?= site_url('dashboard'); ?>">
            <img src="<?= base_url('assets/images/logo-icon.svg'); ?>" alt="WorkNexus" width="32" height="32">
            <span class="sidebar-brand-text">WorkNexus</span>
        </a>
    </div>
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'dashboard' ? 'active' : ''; ?>"
                   href="<?= site_url('dashboard'); ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <?php if ($CI->hasPermission('access_employee')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'employee' ? 'active' : ''; ?>"
                   href="<?= site_url('employee'); ?>">
                    <i class="bi bi-people"></i>
                    <span>Employees</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_department')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'department' ? 'active' : ''; ?>"
                   href="<?= site_url('department'); ?>">
                    <i class="bi bi-building"></i>
                    <span>Departments</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_user_management')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'user' ? 'active' : ''; ?>"
                   href="<?= site_url('user'); ?>">
                    <i class="bi bi-person-gear"></i>
                    <span>User Management</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_reports')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'reports' ? 'active' : ''; ?>"
                   href="<?= site_url('reports'); ?>">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>Reports</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_attendance')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'attendance' ? 'active' : ''; ?>"
                   href="<?= site_url('attendance'); ?>">
                    <i class="bi bi-clock-history"></i>
                    <span>Attendance</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_leave')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'leave' ? 'active' : ''; ?>"
                   href="<?= site_url('leave'); ?>">
                    <i class="bi bi-calendar-x"></i>
                    <span>Leave Management</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_meetings')): ?>
            <li class="nav-item">
                <?php if ($CI->isAdminOrHR()): ?>
                <a class="nav-link <?= $CI->router->fetch_class() === 'meetings' ? 'active' : ''; ?>"
                   href="<?= site_url('meetings'); ?>">
                    <i class="bi bi-easel"></i>
                    <span>Client Meetings</span>
                </a>
                <?php else: ?>
                <a class="nav-link <?= $CI->router->fetch_class() === 'meetings' ? 'active' : ''; ?>"
                   href="<?= site_url('meetings/my_meetings'); ?>">
                    <i class="bi bi-calendar-check"></i>
                    <span>My Meetings</span>
                </a>
                <?php endif; ?>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_hike_management')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'hikes' ? 'active' : ''; ?>"
                   href="<?= site_url('hikes'); ?>">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span><?php if ($CI->isHR()): ?>Salary & Compensation<?php elseif ($CI->isAdmin()): ?>Salary & Compensation<?php else: ?>My Compensation<?php endif; ?></span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_audit_trail')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'audittrail' ? 'active' : ''; ?>"
                   href="<?= site_url('audittrail'); ?>">
                    <i class="bi bi-shield-lock"></i>
                    <span>Audit Trail</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($CI->hasPermission('access_user_logs')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'userlogs' ? 'active' : ''; ?>"
                   href="<?= site_url('userlogs'); ?>">
                    <i class="bi bi-clock-history"></i>
                    <span>User Logs</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'notifications' ? 'active' : ''; ?>"
                   href="<?= site_url('notifications'); ?>">
                    <i class="bi bi-bell"></i>
                    <span>Notifications</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $CI->router->fetch_class() === 'profile' ? 'active' : ''; ?>"
                   href="<?= site_url('profile'); ?>">
                    <i class="bi bi-person"></i>
                    <span>My Profile</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
