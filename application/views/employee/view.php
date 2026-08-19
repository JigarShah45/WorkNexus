<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="employee-view-page">
    <div class="container py-4">

        <?php $this->load->view('layouts/page_header', [
            'title' => 'Employee Profile',
            'subtitle' => 'View employee details and information.',
            'icon' => 'bi-person-circle',
            'show_back' => true,
            'back_url' => site_url('employee'),
            'back_label' => 'Back'
        ]); ?>

        <!-- One clean employee details container -->
        <div class="employee-detail-layout">

            <!-- LEFT: Compact profile summary -->
            <aside class="employee-profile-summary">
                <div class="employee-profile-avatar">
                    <?php if (!empty($employee->profile_image)): ?>
                        <img src="<?= site_url('employee/image/' . $employee->employee_id); ?>" alt="<?= htmlspecialchars($employee->employee_name); ?>">
                    <?php else: ?>
                        <i class="bi bi-person-fill"></i>
                    <?php endif; ?>
                </div>
                <h3 class="employee-profile-name"><?= htmlspecialchars($employee->employee_name); ?></h3>
                <span class="employee-profile-id">#EMP<?= str_pad($employee->employee_id, 4, '0', STR_PAD_LEFT); ?></span>
                <span class="employee-profile-dept">
                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($employee->department_name); ?>
                </span>
                <span class="badge <?= $employee->status == 'Active' ? 'badge-active' : 'badge-inactive'; ?>">
                    <i class="bi bi-<?= $employee->status == 'Active' ? 'check-circle-fill' : 'x-circle-fill'; ?> me-1"></i>
                    <?= htmlspecialchars($employee->status); ?>
                </span>
            </aside>

            <!-- RIGHT: Information sections -->
            <div class="employee-info-panel">

                <!-- Section 1: Personal Information -->
                <section class="employee-info-section">
                    <h5><i class="bi bi-person-lines-fill text-primary me-2"></i>Personal Information</h5>
                    <div class="employee-info-row">
                        <span class="employee-info-label">Employee Name</span>
                        <span class="employee-info-value"><?= htmlspecialchars($employee->employee_name); ?></span>
                    </div>
                    <div class="employee-info-row">
                        <span class="employee-info-label">Email</span>
                        <span class="employee-info-value"><?= htmlspecialchars($employee->employee_email); ?></span>
                    </div>
                    <div class="employee-info-row">
                        <span class="employee-info-label">Phone</span>
                        <span class="employee-info-value"><?= htmlspecialchars($employee->employee_phone); ?></span>
                    </div>
                </section>

                <!-- Section 2: Employment Information -->
                <section class="employee-info-section">
                    <h5><i class="bi bi-briefcase-fill text-success me-2"></i>Employment Information</h5>
                    <div class="employee-info-row">
                        <span class="employee-info-label">Employee ID</span>
                        <span class="employee-info-value">#EMP<?= str_pad($employee->employee_id, 4, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="employee-info-row">
                        <span class="employee-info-label">Department</span>
                        <span class="employee-info-value"><?= htmlspecialchars($employee->department_name); ?></span>
                    </div>
                    <div class="employee-info-row">
                        <span class="employee-info-label">Salary</span>
                        <span class="employee-info-value">₹<?= number_format($employee->employee_salary); ?></span>
                    </div>
                    <div class="employee-info-row">
                        <span class="employee-info-label">Status</span>
                        <span class="employee-info-value">
                            <span class="badge <?= $employee->status == 'Active' ? 'badge-active' : 'badge-inactive'; ?>">
                                <i class="bi bi-<?= $employee->status == 'Active' ? 'check-circle-fill' : 'x-circle-fill'; ?> me-1"></i>
                                <?= htmlspecialchars($employee->status); ?>
                            </span>
                        </span>
                    </div>
                    <div class="employee-info-row">
                        <span class="employee-info-label">Joining Date</span>
                        <span class="employee-info-value"><?= date('d M Y', strtotime($employee->created_at)); ?></span>
                    </div>
                </section>

                <!-- Section 3: System Information -->
                <section class="employee-info-section">
                    <h5><i class="bi bi-gear-fill text-warning me-2"></i>System Information</h5>
                    <div class="employee-info-row">
                        <span class="employee-info-label">Created At</span>
                        <span class="employee-info-value"><?= date('d M Y', strtotime($employee->created_at)); ?></span>
                    </div>
                    <div class="employee-info-row">
                        <span class="employee-info-label">Updated At</span>
                        <span class="employee-info-value"><?= !empty($employee->updated_at) ? date('d M Y', strtotime($employee->updated_at)) : '-'; ?></span>
                    </div>
                </section>

                <!-- Primary action -->
                <div class="employee-detail-actions">
                    <a href="<?= site_url('employee/edit/' . $employee->employee_id); ?>" class="btn btn-warning">
                        <i class="bi bi-pencil-square me-1"></i> Edit Employee
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>

<?php
$this->load->view('layouts/footer');
?>
