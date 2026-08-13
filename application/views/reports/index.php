<?php $this->load->view('layouts/header'); ?>
<?php $this->load->view('layouts/navbar'); ?>

<div class="container py-4">
    <?php $this->load->view('layouts/page_header', [
        'title' => 'Reports',
        'subtitle' => 'Review employee, department, and payroll insights from one place.',
        'icon' => 'bi-graph-up',
        'show_back' => true,
        'back_url' => site_url('dashboard'),
        'back_label' => 'Back'
    ]); ?>

    <div class="reports-grid">

    <div class="report-item ">
        <div class="report-tile align-center">
            <div class="report-icon bg-primary-soft">
                <i class="bi bi-people-fill"></i>
            </div>

            <h5>Employee Report</h5>

            <p>
                View and export the full employee roster with
                filters for status and salary.
            </p>

            <a href="<?= site_url('reports/employees'); ?>"
               class="btn btn-primary">
                View Report
            </a>
        </div>
    </div>

    <div class="report-item">
        <div class="report-tile">
            <div class="report-icon bg-success-soft">
                <i class="bi bi-building-fill"></i>
            </div>

            <h5>Department Report</h5>

            <p>
                Browse department records to keep your org
                structure organized and current.
            </p>

            <a href="<?= site_url('reports/departments'); ?>"
               class="btn btn-success">
                View Report
            </a>
        </div>
    </div>

    <div class="report-item">
        <div class="report-tile">
            <div class="report-icon bg-warning-soft">
                <i class="bi bi-cash-stack"></i>
            </div>

            <h5>Salary Report</h5>

            <p>
                Analyze salary ranges, payroll totals, and
                compensation trends across teams.
            </p>

            <a href="<?= site_url('reports/salary'); ?>"
               class="btn btn-warning">
                View Report
            </a>
        </div>
    </div>

    <div class="report-item">
        <div class="report-tile">
            <div class="report-icon bg-success-soft">
                <i class="bi bi-person-check-fill"></i>
            </div>

            <h5>Active Employees</h5>

            <p>
                Review the team members currently marked
                as active and available.
            </p>

            <a href="<?= site_url('reports/activeEmployees'); ?>"
               class="btn btn-success">
                View Report
            </a>
        </div>
    </div>

    <div class="report-item">
        <div class="report-tile">
            <div class="report-icon bg-danger-soft">
                <i class="bi bi-person-x-fill"></i>
            </div>

            <h5>Inactive Employees</h5>

            <p>
                Check inactive records and keep employee
                data up to date.
            </p>

            <a href="<?= site_url('reports/inactiveEmployees'); ?>"
               class="btn btn-danger">
                View Report
            </a>
        </div>
    </div>

</div>
</div>

<?php $this->load->view('layouts/footer'); ?>
