<?php $this->load->view('layouts/header'); ?>
<?php $this->load->view('layouts/navbar'); ?>

<div class="dashboard-container">
    <div class="container">

        <!-- Welcome Hero Section -->
        <div class="dashboard-header">
            <div class="dashboard-hero">
                <div class="row align-items-center g-2">
                    <div class="col-lg-8">
                        <span class="hero-badge">
                            <i class="bi bi-people-fill"></i>
                            HR Operations
                        </span>
                        <h2>
                            Welcome, <?= $this->session->userdata('employee_name'); ?>!
                        </h2>
                        <p>
                            Manage employees, leave requests, attendance, and HR operations from one place.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <div class="hero-chip justify-content-lg-end">
                            <i class="bi bi-circle-fill text-success"></i>
                            System Live
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistic Cards -->
        <div class="row g-4 dashboard-metrics-grid">

            <div class="col-lg-3 col-md-6">
                <a href="<?= site_url('employee'); ?>" class="dashboard-card-link">
                    <div class="dashboard-card dashboard-metric-card">
                        <span class="card-icon bg-primary">
                            <i class="bi bi-people-fill"></i>
                        </span>
                        <div class="card-content">
                            <span class="eyebrow">Workforce</span>
                            <h6>Total Employees</h6>
                            <h3><?= $totalEmployees ?></h3>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="dashboard-card dashboard-metric-card">
                    <span class="card-icon bg-success">
                        <i class="bi bi-check-circle-fill"></i>
                    </span>
                    <div class="card-content">
                        <span class="eyebrow">Today</span>
                        <h6>Present</h6>
                        <h3><?= $presentToday ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="<?= site_url('leave?status=Approved'); ?>" class="dashboard-card-link">
                    <div class="dashboard-card dashboard-metric-card">
                        <span class="card-icon bg-warning">
                            <i class="bi bi-calendar-minus-fill"></i>
                        </span>
                        <div class="card-content">
                            <span class="eyebrow">Today</span>
                            <h6>On Leave</h6>
                            <h3><?= $onLeaveToday ?></h3>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="<?= site_url('leave'); ?>" class="dashboard-card-link">
                    <div class="dashboard-card dashboard-metric-card">
                        <span class="card-icon bg-danger">
                            <i class="bi bi-hourglass-split"></i>
                        </span>
                        <div class="card-content">
                            <span class="eyebrow">Action Needed</span>
                            <h6>Pending Leave</h6>
                            <h3><?= $pendingLeaveCount ?></h3>
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <!-- Attendance Overview + Pending Actions -->
        <div class="row g-4 dashboard-recent-grid">

            <div class="col-lg-6">
                <div class="card dashboard-panel-card">
                    <div class="card-header dashboard-panel-header">
                        <h5>
                            <i class="bi bi-clock-history text-primary"></i>
                            Today's Attendance Overview
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($attendanceOverview)): ?>
                        <div class="row g-3 text-center">
                            <?php
                            $statusIcons = array(
                                'Present' => array('icon' => 'bi-check-circle-fill', 'color' => 'text-success'),
                                'Absent'  => array('icon' => 'bi-x-circle-fill', 'color' => 'text-danger'),
                                'Late'    => array('icon' => 'bi-clock-fill', 'color' => 'text-warning'),
                                'Half-Day'=> array('icon' => 'bi-dash-circle-fill', 'color' => 'text-info'),
                            );
                            foreach ($attendanceOverview as $row):
                                $iconData = isset($statusIcons[$row->status]) ? $statusIcons[$row->status] : array('icon' => 'bi-circle', 'color' => 'text-muted');
                            ?>
                            <div class="col-3">
                                <div class="p-3 rounded bg-soft">
                                    <i class="bi <?= $iconData['icon'] ?> <?= $iconData['color'] ?> fs-3"></i>
                                    <h4 class="mt-2 mb-0"><?= $row->total ?></h4>
                                    <small class="text-muted"><?= htmlspecialchars($row->status) ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            <p>No attendance data for today.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card dashboard-panel-card">
                    <div class="card-header dashboard-panel-header d-flex justify-content-between align-items-center">
                        <h5>
                            <i class="bi bi-clipboard-check text-warning"></i>
                            Pending Leave Requests
                        </h5>
                        <a href="<?= site_url('leave'); ?>">View All</a>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php if (!empty($recentLeaveRequests)): ?>
                            <?php foreach ($recentLeaveRequests as $lr): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($lr->employee_name) ?></strong>
                                    <small class="text-muted d-block">
                                        <?= htmlspecialchars($lr->leave_type) ?> &middot;
                                        <?= date('d M', strtotime($lr->from_date)) ?> - <?= date('d M Y', strtotime($lr->to_date)) ?>
                                    </small>
                                </div>
                                <?php
                                $statusBadge = 'bg-warning text-dark';
                                if ($lr->status === 'Approved') $statusBadge = 'bg-success';
                                elseif ($lr->status === 'Rejected') $statusBadge = 'bg-danger';
                                ?>
                                <span class="badge <?= $statusBadge ?>"><?= $lr->status ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item text-center py-4 text-muted">
                                <i class="bi bi-check-circle fs-3 d-block mb-2"></i>
                                <p>No pending leave requests.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Department Chart + Quick Actions -->
        <div class="row g-4 dashboard-charts-grid">

            <div class="col-lg-8">
                <div class="card dashboard-panel-card">
                    <div class="card-header dashboard-panel-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>
                                <i class="bi bi-bar-chart-line-fill text-primary"></i>
                                Employees by Department
                            </h5>
                            <span class="badge bg-primary">
                                <?= count($departmentChart); ?> Departments
                            </span>
                        </div>
                    </div>
                    <div class="chart-wrap">
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card dashboard-panel-card h-100">
                    <div class="card-header dashboard-panel-header">
                        <h5>
                            <i class="bi bi-lightning-fill text-warning"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column gap-3">
                        <a href="<?= site_url('leave'); ?>" class="btn btn-outline-primary w-100">
                            <i class="bi bi-calendar-check me-2"></i>Manage Leave
                        </a>
                        <a href="<?= site_url('employee'); ?>" class="btn btn-outline-success w-100">
                            <i class="bi bi-people me-2"></i>View Employees
                        </a>
                        <a href="<?= site_url('attendance/manage'); ?>" class="btn btn-outline-info w-100">
                            <i class="bi bi-clock-history me-2"></i>Attendance
                        </a>
                        <a href="<?= site_url('hikes'); ?>" class="btn btn-outline-success w-100">
                            <i class="bi bi-graph-up-arrow me-2"></i>Salary & Compensation
                        </a>
                        <a href="<?= site_url('reports'); ?>" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-file-earmark-bar-graph me-2"></i>Reports
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
    const departmentLabels = [
        <?php foreach ($departmentChart as $row) { ?> "<?= $row->department_name; ?>",
        <?php } ?>
    ];

    const departmentTotals = [
        <?php foreach ($departmentChart as $row) { ?>
            <?= $row->total; ?>,
        <?php } ?>
    ];

    function getThemeColors() {
        const style = getComputedStyle(document.documentElement);
        return {
            text: style.getPropertyValue('--text').trim() || '#1f2937',
            muted: style.getPropertyValue('--muted').trim() || '#6b7280',
            border: style.getPropertyValue('--border-light').trim() || '#e5e7eb',
            surface: style.getPropertyValue('--surface').trim() || '#ffffff',
            primary: style.getPropertyValue('--primary').trim() || '#2563eb',
        };
    }

    function renderCharts() {
        const colors = getThemeColors();

        // Department Chart
        const deptCtx = document.getElementById('departmentChart');
        if (deptCtx) {
            if (deptCtx._chartInstance) deptCtx._chartInstance.destroy();
            deptCtx._chartInstance = new Chart(deptCtx, {
                type: 'bar',
                data: {
                    labels: departmentLabels,
                    datasets: [{
                        label: 'Employees',
                        data: departmentTotals,
                        backgroundColor: colors.primary + '33',
                        borderColor: colors.primary,
                        borderWidth: 2,
                        borderRadius: 6,
                        barPercentage: 0.6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: colors.muted }, grid: { color: colors.border } },
                        y: { beginAtZero: true, ticks: { color: colors.muted, stepSize: 1 }, grid: { color: colors.border } }
                    }
                }
            });
        }
    }

    renderCharts();

    document.addEventListener('themeChanged', function() { renderCharts(); });

    const observer = new MutationObserver(function() { renderCharts(); });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
</script>

<?php $this->load->view('layouts/footer'); ?>
