<?php $this->load->view('layouts/header'); ?>
<?php $this->load->view('layouts/navbar'); ?>

<div class="container py-4 report-page">
    <?php $this->load->view('layouts/page_header', [
        'title' => 'Active Employees Report',
        'subtitle' => 'Review the employees currently marked as active.',
        'icon' => 'bi-person-check-fill',
        'show_back' => true,
        'back_url' => site_url('reports'),
        'back_label' => 'Reports'
    ]); ?>

    <div class="report-stat-grid report-stat-grid--single mb-4">
        <div class="report-stat-card">
            <div class="report-stat-icon bg-success-soft">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div class="report-stat-content">
                <span class="report-stat-label">Active Employees</span>
                <span class="report-stat-value"><?= $totalEmployees; ?></span>
            </div>
        </div>
    </div>

    <div class="report-table-card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">
                <i class="bi bi-table me-2"></i>
                Active Employees
            </h5>
            <span class="badge bg-success">Total Records: <?= count($employees); ?></span>
        </div>

        <div class="card-body">
            <table id="activeEmployeeTable" class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Salary</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($employees as $row) { ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= $row->employee_name; ?></td>
                            <td><?= $row->employee_email; ?></td>
                            <td><?= $row->employee_phone; ?></td>
                            <td><?= $row->department_name; ?></td>
                            <td><span class="badge bg-success fs-6">&#8377;<?= number_format($row->employee_salary); ?></span></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $this->load->view('layouts/footer'); ?>
