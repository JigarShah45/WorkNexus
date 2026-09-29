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
                            <i class="bi bi-graph-up-arrow"></i>
                            Executive Overview
                        </span>
                        <h2>
                            Welcome, <?= $this->session->userdata('employee_name'); ?>!
                        </h2>
                        <p>
                            Here is a real-time snapshot of your workforce, departments, and compensation signals.
                        </p>
                        <!-- <?php $last_login = $this->session->userdata('last_login_display'); ?>
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
                <a href="<?= site_url('department'); ?>" class="dashboard-card-link">
                    <div class="dashboard-card dashboard-metric-card">
                        <span class="card-icon bg-success">
                            <i class="bi bi-building"></i>
                        </span>
                        <div class="card-content">
                            <span class="eyebrow">Structure</span>
                            <h6>Departments</h6>
                            <h3><?= $totalDepartments ?></h3>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="dashboard-card dashboard-metric-card">
                    <span class="card-icon bg-warning">
                        <i class="bi bi-currency-rupee"></i>
                    </span>
                    <div class="card-content">
                        <span class="eyebrow">Compensation</span>
                        <h6>Average Salary</h6>
                        <h3>₹<?= number_format($averageSalary) ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="dashboard-card dashboard-metric-card">
                    <span class="card-icon bg-danger">
                        <i class="bi bi-trophy-fill"></i>
                    </span>
                    <div class="card-content">
                        <span class="eyebrow">Top Tier</span>
                        <h6>Highest Salary</h6>
                        <h3>₹<?= number_format($highestSalary) ?></h3>
                    </div>
                </div>
            </div>

        </div>

        <!-- Quick Action Cards -->
        <div class="row g-4 dashboard-actions-grid">

            <div class="col-lg-4 col-md-6">
                <div class="dashboard-action-card card border-1">
                    <div class="card-body text-center">
                        <div class="dashboard-action-icon bg-primary-soft">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <h5>Manage Employees</h5>
                        <p>Register a new employee.</p>
                        <a href="<?= site_url('employee/add'); ?>" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Add Employee
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="dashboard-action-card card border-1">
                    <div class="card-body text-center">
                        <div class="dashboard-action-icon bg-success-soft">
                            <i class="bi bi-building-add"></i>
                        </div>
                        <h5>Manage Departments</h5>
                        <p>Create a new department.</p>
                        <a href="<?= site_url('department/add'); ?>" class="btn btn-success">
                            <i class="bi bi-plus-lg me-1"></i> Add Department
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="dashboard-action-card card border-1">
                    <div class="card-body text-center">
                        <div class="dashboard-action-icon bg-info-soft">
                            <i class="bi bi-file-earmark-bar-graph"></i>
                        </div>
                        <h5>Reports & Analytics</h5>
                        <p>View, print and export HR reports.</p>
                        <a href="<?= site_url('reports'); ?>" class="btn btn-info">
                            <i class="bi bi-graph-up-arrow me-1"></i> Open Reports
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Recent Employees & Departments -->
        <div class="row g-4 dashboard-recent-grid">

            <div class="col-lg-6">
                <div class="card dashboard-panel-card">
                    <div class="card-header dashboard-panel-header d-flex justify-content-between align-items-center">
                        <h5 class="">
                            <i class="bi bi-clock-history text-primary"></i>
                            Recent Employees
                        </h5>
                        <a href="<?= site_url('employee'); ?>">View All</a>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentEmployees as $employee) { ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center ">
                                <div class="employee-info" >
                                    <strong><?= $employee->employee_name; ?></strong>
                                  
                                    <small class="text-muted">
                                        <?= $employee->department_name; ?>
                                        &middot;
                                        <?= date('d M Y', strtotime($employee->created_at)); ?>
                                    </small>
                                </div>
                                <a href="<?= site_url('employee/view/' . $employee->employee_id); ?>"
                                    class="btn btn-view btn-icon btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card dashboard-panel-card">
                    <div class="card-header dashboard-panel-header d-flex justify-content-between align-items-center">
                        <h5>
                            <i class="bi bi-building text-success"></i>
                            Recent Departments
                        </h5>
                        <a href="<?= site_url('department'); ?>">View All</a>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentDepartments as $department) { ?>
                            <div class="list-group-item department-item">
                                <i class="bi bi-building text-success me-2"></i>
                                <strong><?= $department->department_name; ?></strong>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Charts Section -->
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
                            <i class="bi bi-pie-chart-fill text-success"></i>
                            Employee Status
                        </h5>
                    </div>
                    <div class="chart-wrap d-flex align-items-center justify-content-center">
                        <canvas id="statusChart"></canvas>
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

    const statusLabels = [
        <?php foreach ($statusChart as $row) { ?>
            "<?= $row->status; ?>",
        <?php } ?>
    ];

    const statusTotals = [
        <?php foreach ($statusChart as $row) { ?>
            <?= $row->total; ?>,
        <?php } ?>
    ];

    /**
     * Dynamically read theme CSS variables to avoid hardcoded colors.
     * This is called on init and on every theme switch.
     */
    function getChartColors() {
        const root = document.documentElement;
        const style = getComputedStyle(root);
        const isLight = root.getAttribute('data-theme') !== 'dark';

        const text   = style.getPropertyValue('--text').trim()   || '#0f172a';
        const muted  = style.getPropertyValue('--muted').trim()  || '#64748b';
        const grid   = style.getPropertyValue('--border').trim() || '#dbe4f0';
        const surface= style.getPropertyValue('--surface').trim()|| '#ffffff';

        return {
            text: text,
            muted: muted,
            grid: grid,
            surface: surface,
            // Explicit light / dark overrides for best contrast
            centerValue:   isLight ? '#111827' : '#ffffff',
            centerSubtitle:isLight ? '#6B7280' : '#94a3b8',
            legendText:    isLight ? '#0f172a' : '#e5edf7',
            gridLine:      isLight ? '#dbe4f0' : '#243247',
            datalabel:     isLight ? '#0f172a' : '#e5edf7',
        };
    }

    Chart.defaults.font.family = 'Inter, Segoe UI, Arial, sans-serif';

    let colors = getChartColors();
    Chart.defaults.color = colors.text;

    /* ---- Bar Chart (Employees by Department) ---- */
    const barCtx = document.getElementById('departmentChart');
    const barChart = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: departmentLabels,
            datasets: [{
                label: 'Employees',
                data: departmentTotals,
                backgroundColor: [
                    '#2563eb', '#0ea5e9', '#14b8a6',
                    '#22c55e', '#f59e0b', '#8b5cf6'
                ],
                borderRadius: 10,
                borderSkipped: false,
                barThickness: 24
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, precision: 0, color: colors.muted },
                    grid: { color: colors.grid }
                },
                y: {
                    grid: { display: false },
                    ticks: {
                        color: colors.muted,
                        font: { size: 12, weight: '700' }
                    }
                }
            }
        }
    });

    /* ---- Doughnut Chart (Employee Status) ---- */
    const pieCtx = document.getElementById('statusChart');
    const totalEmp = statusTotals.reduce((a, b) => a + b, 0);

    const centerTextPlugin = {
        id: 'centerText',
        afterDraw(chart) {
            const { ctx } = chart;
            const meta = chart.getDatasetMeta(0);
            if (!meta.data.length) return;

            const c = getChartColors();
            const x = meta.data[0].x;
            const y = meta.data[0].y;

            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            ctx.fillStyle = c.centerValue;
            ctx.font = '800 30px Inter, Arial';
            ctx.fillText(totalEmp, x, y - 10);

            ctx.fillStyle = c.centerSubtitle;
            ctx.font = '600 14px Inter, Arial';
            ctx.fillText('Employees', x, y + 20);

            ctx.restore();
        }
    };

    const doughnutChart = new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: [
                `Active (${statusTotals[0]})`,
                `Inactive (${statusTotals[1]})`
            ],
            datasets: [{
                data: statusTotals,
                backgroundColor: ['#16a34a', '#dc2626'],
                borderWidth: 4,
                borderColor: colors.surface,
                hoverOffset: 10
            }]
        },
        plugins: [centerTextPlugin, ChartDataLabels],
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: colors.legendText,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 18,
                        font: { size: 13, weight: '700' }
                    }
                },
                datalabels: {
                    color: colors.datalabel,
                    font: { weight: '700', size: 14 },
                    formatter: (value) => value
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label;
                        }
                    }
                }
            }
        }
    });

    /**
     * Update ALL chart instances when the theme changes.
     * Called automatically via MutationObserver below.
     */
    function updateChartTheme() {
        colors = getChartColors();

        // 1. Global defaults
        Chart.defaults.color = colors.text;

        // 2. Bar chart – scales
        barChart.options.scales.x.grid.color = colors.grid;
        barChart.options.scales.x.ticks.color = colors.muted;
        barChart.options.scales.y.ticks.color = colors.muted;
        barChart.update();

        // 3. Doughnut – dataset border (use surface so it blends with card bg)
        doughnutChart.data.datasets[0].borderColor = colors.surface;

        // 4. Doughnut – legend labels
        doughnutChart.options.plugins.legend.labels.color = colors.legendText;

        // 5. Doughnut – data labels
        doughnutChart.options.plugins.datalabels.color = colors.datalabel;

        // 6. Doughnut – the center-text plugin re-reads colors on every draw,
        //    but we force a full redraw to pick them up.
        doughnutChart.update();
    }

    /* Watch for data-theme changes and refresh charts automatically */
    const themeObserver = new MutationObserver(function () {
        updateChartTheme();
    });
    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-theme']
    });
</script>


<?php $this->load->view('layouts/footer'); ?>