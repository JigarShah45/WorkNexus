<?php $this->load->view('layouts/header'); ?>
<?php $this->load->view('layouts/navbar'); ?>

<div class="container py-4">
    <div class="page-header d-flex align-items-center gap-3 mb-4">
        <div class="page-header-icon">
            <i class="bi bi-graph-up-arrow"></i>
        </div>
        <div class="flex-grow-1">
            <h2 class="fw-bold mb-1"><?= $title ?></h2>
            <p class="text-muted mb-0">Manage employee salary hike proposals.</p>
        </div>
        <div class="page-header-actions">
            <a href="<?= site_url('hikes/history') ?>" class="btn btn-back">
                <i class="bi bi-clock-history me-1"></i> Hike History
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon bg-primary-soft mx-auto mb-2">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h3 class="mb-0"><?= $hike_stats->total_hikes ?></h3>
                    <small class="text-muted">Total Hikes</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon" style="background:var(--warning-soft);color:var(--warning-600);">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <h3 class="mb-0"><?= $hike_stats->proposed ?></h3>
                    <small class="text-muted">Proposed</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon" style="background:var(--success-soft);color:var(--success);">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h3 class="mb-0"><?= $hike_stats->approved ?></h3>
                    <small class="text-muted">Approved</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon" style="background:var(--danger-soft);color:var(--danger);">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <h3 class="mb-0"><?= $hike_stats->rejected ?></h3>
                    <small class="text-muted">Rejected</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon" style="background:var(--info-soft);color:var(--info);">
                        <i class="bi bi-percent"></i>
                    </div>
                    <h3 class="mb-0"><?= number_format($hike_stats->avg_hike_pct ?? 0, 1) ?>%</h3>
                    <small class="text-muted">Avg Hike %</small>
                </div>
            </div>
        </div>
    </div>

    <div class="form-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Employees</h5>
            <a href="<?= site_url('hikes/history') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-clock-history me-1"></i> Hike History
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="hikesTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee Name</th>
                            <th>Department</th>
                            <th>Current Salary</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($employees)): ?>
                            <?php foreach ($employees as $index => $emp): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($emp->employee_name) ?></td>
                                    <td><?= htmlspecialchars($emp->department_name) ?></td>
                                    <td><?= number_format($emp->employee_salary, 2) ?></td>
                                    <td class="text-center">
                                        <a href="<?= site_url('hikes/propose/' . $emp->employee_id) ?>" class="btn btn-primary btn-sm">
                                            <i class="bi bi-plus-circle me-1"></i> Propose Hike
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No employees found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
<?php $this->load->view('layouts/footer'); ?>
