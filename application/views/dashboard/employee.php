<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI =& get_instance();
?>
<?php $this->load->view('layouts/header'); ?>
<?php $this->load->view('layouts/navbar'); ?>

<link rel="stylesheet" href="<?= base_url('assets/css/salary.css'); ?>">

<div class="dashboard-container">
    <div class="container">

        <!-- Welcome Hero Section -->
        <div class="dashboard-header">
            <div class="dashboard-hero">
                <div class="row align-items-center g-2">
                    <div class="col-lg-8">
                        <span class="hero-badge">
                            <i class="bi bi-person-circle"></i>
                            My Dashboard
                        </span>
                        <h2>
                            Welcome, <?= htmlspecialchars($employee->employee_name); ?>!
                        </h2>
                        <p>
                            Here is an overview of your employment information and recent activity.
                        </p>
                        <!-- <?php $last_login = $CI->session->userdata('last_login_display'); ?>
                        <?php if ($last_login): ?>
                        <small style="color:rgba(255,255,255,.8);">
                            <i class="bi bi-clock me-1"></i>
                            Last Login: <?= date('d M Y, h:i A', strtotime($last_login)) ?>
                        </small>
                        <?php endif; ?> -->
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <div class="hero-chip justify-content-lg-end">
                            <span class="live-dot" aria-hidden="true"></span>
                            <?= $employee->status; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Summary Card -->
        <div class="row g-4 dashboard-metrics-grid">
            <div class="col-lg-12">
                <div class="card dashboard-panel-card">
                    <div class="card-header dashboard-panel-header">
                        <h5>
                            <i class="bi bi-person-badge text-primary"></i>
                            Profile Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <div class="employee-detail-item">
                                    <div class="employee-detail-icon bg-primary-soft">
                                        <i class="bi bi-hash text-primary"></i>
                                    </div>
                                    <div>
                                        <span class="employee-detail-label">Employee ID</span>
                                        <span class="employee-detail-value">#EMP<?= str_pad($employee->employee_id, 4, '0', STR_PAD_LEFT) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="employee-detail-item">
                                    <div class="employee-detail-icon bg-success-soft">
                                        <i class="bi bi-person text-success"></i>
                                    </div>
                                    <div>
                                        <span class="employee-detail-label">Name</span>
                                        <span class="employee-detail-value"><?= htmlspecialchars($employee->employee_name) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="employee-detail-item">
                                    <div class="employee-detail-icon bg-warning-soft">
                                        <i class="bi bi-building text-warning"></i>
                                    </div>
                                    <div>
                                        <span class="employee-detail-label">Department</span>
                                        <span class="employee-detail-value"><?= htmlspecialchars($employee->department_name) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="employee-detail-item">
                                    <div class="employee-detail-icon bg-info-soft">
                                        <i class="bi bi-envelope text-info"></i>
                                    </div>
                                    <div>
                                        <span class="employee-detail-label">Email</span>
                                        <span class="employee-detail-value text-truncate" style="max-width:180px" title="<?= htmlspecialchars($employee->employee_email) ?>"><?= htmlspecialchars($employee->employee_email) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="employee-detail-item">
                                    <div class="employee-detail-icon bg-primary-soft">
                                        <i class="bi bi-telephone text-primary"></i>
                                    </div>
                                    <div>
                                        <span class="employee-detail-label">Phone</span>
                                        <span class="employee-detail-value"><?= htmlspecialchars($employee->employee_phone) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="employee-detail-item">
                                    <div class="employee-detail-icon bg-info-soft">
                                        <i class="bi bi-calendar-event text-info"></i>
                                    </div>
                                    <div>
                                        <span class="employee-detail-label">Joining Date</span>
                                        <span class="employee-detail-value"><?= date('d M Y', strtotime($employee->joining_date)) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="employee-detail-item">
                                    <div class="employee-detail-icon bg-<?= $employee->status === 'Active' ? 'success' : 'danger' ?>-soft">
                                        <i class="bi bi-<?= $employee->status === 'Active' ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' ?>"></i>
                                    </div>
                                    <div>
                                        <span class="employee-detail-label">Status</span>
                                        <span class="employee-detail-value"><?= $employee->status ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="employee-detail-item">
                                    <div class="employee-detail-icon bg-warning-soft">
                                        <i class="bi bi-currency-rupee text-warning"></i>
                                    </div>
                                    <div>
                                        <span class="employee-detail-label">Salary</span>
                                        <span class="employee-detail-value">₹<?= number_format($salary_summary->current_salary) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistic Cards -->
        <div class="row g-4 dashboard-metrics-grid">

            <div class="col-lg-3 col-md-6">
                <a href="<?= site_url('attendance'); ?>" class="dashboard-card-link">
                    <div class="dashboard-card dashboard-metric-card">
                        <span class="card-icon bg-success">
                            <i class="bi bi-check-circle-fill"></i>
                        </span>
                        <div class="card-content">
                            <span class="eyebrow">This Month</span>
                            <h6>Attendance Rate</h6>
                            <h3><?= $attendance_summary->attendance_rate; ?>%</h3>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="<?= site_url('leave'); ?>" class="dashboard-card-link">
                    <div class="dashboard-card dashboard-metric-card">
                        <span class="card-icon bg-primary">
                            <i class="bi bi-calendar-x"></i>
                        </span>
                        <div class="card-content">
                            <span class="eyebrow">This Year</span>
                            <h6>Leave Requests</h6>
                            <h3><?= $leave_summary->total_requests; ?></h3>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="dashboard-card dashboard-metric-card">
                    <span class="card-icon bg-warning">
                        <i class="bi bi-clock-history"></i>
                    </span>
                    <div class="card-content">
                        <span class="eyebrow">This Month</span>
                        <h6>Hours Worked</h6>
                        <h3><?= number_format($attendance_summary->total_hours, 1); ?>h</h3>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="dashboard-card dashboard-metric-card">
                    <span class="card-icon bg-info">
                        <i class="bi bi-graph-up-arrow"></i>
                    </span>
                    <div class="card-content">
                        <span class="eyebrow">Overtime</span>
                        <h6>Extra Hours</h6>
                        <h3><?= number_format($attendance_summary->total_overtime, 1); ?>h</h3>
                    </div>
                </div>
            </div>

        </div>

        <!-- Salary Summary & Quick Actions -->
        <div class="row g-4 dashboard-recent-grid">

            <!-- Salary Summary -->
            <div class="col-lg-6">
                <div class="card dashboard-panel-card">
                    <div class="card-header dashboard-panel-header d-flex justify-content-between align-items-center">
                        <h5>
                            <i class="bi bi-wallet2 text-success"></i>
                            Salary Summary
                        </h5>
                        <a href="<?= site_url('hikes'); ?>">View Details</a>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="employee-detail-item">
                                    <div class="employee-detail-icon bg-success-soft">
                                        <i class="bi bi-currency-dollar text-success"></i>
                                    </div>
                                    <div>
                                        <span class="employee-detail-label">Current Salary</span>
                                        <span class="employee-detail-value" style="font-size:1.2rem;font-weight:700;color:var(--text)">
                                            ₹<?= number_format($salary_summary->current_salary) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php if ($salary_summary->latest_hike): ?>
                            <div class="col-6">
                                <div class="employee-detail-item">
                                    <div class="employee-detail-icon bg-primary-soft">
                                        <i class="bi bi-graph-up-arrow text-primary"></i>
                                    </div>
                                    <div>
                                        <span class="employee-detail-label">Latest Hike</span>
                                        <span class="employee-detail-value">+<?= number_format($salary_summary->latest_hike->hike_percentage, 1) ?>%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="employee-detail-item">
                                    <div class="employee-detail-icon bg-info-soft">
                                        <i class="bi bi-calendar-check text-info"></i>
                                    </div>
                                    <div>
                                        <span class="employee-detail-label">Hike Date</span>
                                        <span class="employee-detail-value"><?= date('d M Y', strtotime($salary_summary->latest_hike->hike_date)) ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-6">
                <div class="card dashboard-panel-card">
                    <div class="card-header dashboard-panel-header">
                        <h5>
                            <i class="bi bi-lightning-fill text-warning"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <a href="<?= site_url('profile'); ?>" class="btn btn-outline-primary w-100 py-3">
                                    <i class="bi bi-person-badge me-2"></i>
                                    My Profile
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?= site_url('attendance'); ?>" class="btn btn-outline-success w-100 py-3">
                                    <i class="bi bi-clock-history me-2"></i>
                                    My Attendance
                                </a>
                            </div>
                            <?php if ($CI->hasPermission('access_leave')): ?>
                            <div class="col-6">
                                <a href="<?= site_url('leave/request_leave'); ?>" class="btn btn-outline-warning w-100 py-3">
                                    <i class="bi bi-calendar-plus me-2"></i>
                                    Request Leave
                                </a>
                            </div>
                            <?php endif; ?>
                            <?php if ($CI->hasPermission('access_leave')): ?>
                            <div class="col-6">
                                <a href="<?= site_url('leave'); ?>" class="btn btn-outline-info w-100 py-3">
                                    <i class="bi bi-calendar-x me-2"></i>
                                    My Leaves
                                </a>
                            </div>
                            <?php endif; ?>
                            <?php if ($CI->hasPermission('access_hike_management')): ?>
                            <div class="col-6">
                                <a href="<?= site_url('hikes/my_compensation'); ?>" class="btn btn-outline-danger w-100 py-3">
                                    <i class="bi bi-wallet2 me-2"></i>
                                    My Compensation
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Leave Summary & Recent Activity -->
        <div class="row g-4 dashboard-recent-grid">

            <!-- Leave Summary -->
            <div class="col-lg-6">
                <div class="card dashboard-panel-card">
                    <div class="card-header dashboard-panel-header">
                        <h5>
                            <i class="bi bi-calendar-check text-primary"></i>
                            Leave Summary (<?= date('Y') ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 text-center">
                            <div class="col-4">
                                <div class="p-3 rounded-3" style="background:var(--warning-soft);">
                                    <h3 class="mb-0" style="color:var(--text)"><?= $leave_summary->pending; ?></h3>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded-3" style="background:rgba(16,185,129,.12);">
                                    <h3 class="mb-0" style="color:var(--text)"><?= $leave_summary->approved; ?></h3>
                                    <small class="text-muted">Approved</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded-3" style="background:rgba(239,68,68,.12);">
                                    <h3 class="mb-0" style="color:var(--text)"><?= $leave_summary->rejected; ?></h3>
                                    <small class="text-muted">Rejected</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-lg-6">
                <div class="card dashboard-panel-card">
                    <div class="card-header dashboard-panel-header">
                        <h5>
                            <i class="bi bi-clock-history text-info"></i>
                            Recent Activity
                        </h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php if (!empty($recent_activity)): ?>
                            <?php foreach ($recent_activity as $activity): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="employee-info">
                                        <strong>
                                            <i class="bi bi-calendar-x text-primary me-1"></i>
                                            <?= htmlspecialchars($activity->description) ?> Leave
                                        </strong>
                                        <small class="text-muted">
                                            <?= date('d M Y', strtotime($activity->activity_date)) ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?= $activity->activity_status === 'Approved' ? 'success' : ($activity->activity_status === 'Rejected' ? 'danger' : 'warning') ?>">
                                        <?= $activity->activity_status ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size:2rem;"></i>
                                <p class="text-muted mb-0 mt-2">No recent activity</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<?php $this->load->view('layouts/footer'); ?>