<?php $this->load->view('layouts/header'); ?>
<?php $this->load->view('layouts/navbar'); ?>

<div class="container py-4 report-page">
    <?php $this->load->view('layouts/page_header', [
        'title' => 'Employee Report',
        'subtitle' => 'Review employee records, status trends, and compensation summaries.',
        'icon' => 'bi-people-fill',
        'show_back' => true,
        'back_url' => site_url('reports'),
        'back_label' => 'Reports'
    ]); ?>

    <div class="report-meta mb-4">Generated on <?= date('d M Y h:i A'); ?></div>

    <div class="report-stat-grid mb-4">
        <div class="report-stat-card">
            <div class="report-stat-icon bg-primary-soft">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="report-stat-content">
                <span class="report-stat-label">Total Employees</span>
                <span class="report-stat-value"><?= $totalEmployees; ?></span>
            </div>
        </div>

        <div class="report-stat-card">
            <div class="report-stat-icon bg-success-soft">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div class="report-stat-content">
                <span class="report-stat-label">Active</span>
                <span class="report-stat-value"><?= $activeEmployees; ?></span>
            </div>
        </div>

        <div class="report-stat-card">
            <div class="report-stat-icon bg-danger-soft">
                <i class="bi bi-person-x-fill"></i>
            </div>
            <div class="report-stat-content">
                <span class="report-stat-label">Inactive</span>
                <span class="report-stat-value"><?= $inactiveEmployees; ?></span>
            </div>
        </div>

        <div class="report-stat-card">
            <div class="report-stat-icon bg-warning-soft">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="report-stat-content">
                <span class="report-stat-label">Average Salary</span>
                <span class="report-stat-value">&#8377;<?= number_format($averageSalary); ?></span>
            </div>
        </div>
    </div>

    <div class="report-filter-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">
                <i class="bi bi-funnel-fill me-2"></i>
                Filter Employees
            </h5>
        </div>

        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select name="department" class="form-select">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept) { ?>
                            <option value="<?= $dept->department_id; ?>" <?= ($this->input->get('department') == $dept->department_id) ? 'selected' : ''; ?>>
                                <?= $dept->department_name; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="Active" <?= ($this->input->get('status') == 'Active') ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?= ($this->input->get('status') == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Min Salary</label>
                    <input type="number" name="min_salary" class="form-control" value="<?= $this->input->get('min_salary'); ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Max Salary</label>
                    <input type="number" name="max_salary" class="form-control" value="<?= $this->input->get('max_salary'); ?>">
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-primary">
                        <i class="bi bi-funnel-fill me-1"></i>
                        Apply
                    </button>
                    <a href="<?= site_url('reports/employees'); ?>" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="report-table-card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">
                <i class="bi bi-table me-2"></i>
                Employee Records
            </h5>
            <span class="badge bg-primary">Total Records: <?= count($employees); ?></span>
        </div>

        <div class="card-body">
            <table id="employeeReportTable" class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Salary</th>
                        <th>Status</th>
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
                            <td>
                                <?php if ($row->status == 'Active') { ?>
                                    <span class="badge bg-success">Active</span>
                                <?php } else { ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $this->load->view('layouts/footer'); ?>
