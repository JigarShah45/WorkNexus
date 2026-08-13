<?php $this->load->view('layouts/header'); ?>
<?php $this->load->view('layouts/navbar'); ?>

<div class="container py-4">
    <div class="page-header d-flex align-items-center gap-3 mb-4">
        <div class="page-header-icon" style="background: var(--info-soft);">
            <i class="bi bi-shield-check text-info"></i>
        </div>
        <div class="flex-grow-1">
            <h2 class="fw-bold mb-1"><?= $title ?></h2>
            <p class="text-muted mb-0">Monitor organization-wide salary revisions and compensation activity. This is a read-only overview.</p>
        </div>
        <div class="page-header-actions d-flex gap-2">
            <a href="<?= site_url('hikes/history') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-clock-history me-1"></i> View History
            </a>
            <a href="<?= site_url('reports/salary') ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> Salary Report
            </a>
        </div>
    </div>

    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-info-circle me-2 fs-5"></i>
        <div>
            <strong>Oversight Mode:</strong> You are viewing salary data for organizational monitoring.
            Salary changes are managed exclusively by HR through the approval workflow.
        </div>
    </div>

    <div class="salary-kpi-grid mb-4">
        <div class="salary-kpi-card">
            <div class="kpi-icon" style="background:var(--warning-soft);color:var(--warning-600);">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="kpi-value"><?= $hike_stats->pending_proposals ?></div>
            <div class="kpi-label">Pending HR<br>Proposals</div>
        </div>
        <div class="salary-kpi-card">
            <div class="kpi-icon" style="background:var(--success-soft);color:var(--success);">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="kpi-value"><?= $hike_stats->approved_this_year ?></div>
            <div class="kpi-label">Approved<br>This Year</div>
        </div>
        <div class="salary-kpi-card">
            <div class="kpi-icon" style="background:var(--danger-soft);color:var(--danger);">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="kpi-value"><?= $hike_stats->rejected_this_year ?></div>
            <div class="kpi-label">Rejected<br>This Year</div>
        </div>
        <div class="salary-kpi-card">
            <div class="kpi-icon" style="background:var(--info-soft);color:var(--info);">
                <i class="bi bi-percent"></i>
            </div>
            <div class="kpi-value"><?= number_format($hike_stats->avg_hike_pct ?? 0, 1) ?>%</div>
            <div class="kpi-label">Avg Hike %</div>
        </div>
        <div class="salary-kpi-card">
            <div class="kpi-icon" style="background:var(--primary-soft);color:var(--primary);">
                <i class="bi bi-currency-rupee"></i>
            </div>
            <div class="kpi-value">₹<?= number_format($hike_stats->total_salary_increase ?? 0, 0) ?></div>
            <div class="kpi-label">Total Salary<br>Increase</div>
        </div>
    </div>

    <?php if (!empty($pending_hikes)): ?>
    <div class="form-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-hourglass-split text-warning me-2"></i>Pending HR Proposals
            </h5>
            <span class="badge bg-warning text-dark"><?= count($pending_hikes) ?> Pending</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Current Salary</th>
                            <th>Proposed Salary</th>
                            <th>Hike %</th>
                            <th>Proposed By</th>
                            <th>Proposed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_hikes as $hike): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($hike->employee_name) ?></strong></td>
                            <td><?= htmlspecialchars($hike->department_name ?? 'N/A') ?></td>
                            <td>₹<?= number_format($hike->current_salary, 0) ?></td>
                            <td class="fw-semibold text-success">₹<?= number_format($hike->proposed_salary, 0) ?></td>
                            <td><span class="badge bg-info"><?= number_format($hike->hike_percentage, 1) ?>%</span></td>
                            <td><?= htmlspecialchars($hike->proposed_by_name ?? 'N/A') ?></td>
                            <td><?= date('d M Y', strtotime($hike->proposed_at)) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light text-muted small">
            <i class="bi bi-info-circle me-1"></i>HR is responsible for reviewing and processing these proposals. No admin action required.
        </div>
    </div>
    <?php endif; ?>

    <div class="form-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-clock-history text-primary me-2"></i>Recent Compensation Activity
            </h5>
            <a href="<?= site_url('hikes/history') ?>" class="btn btn-outline-secondary btn-sm">
                View Complete History
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="adminHikesTable">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Previous Salary</th>
                            <th>New Salary</th>
                            <th>Hike %</th>
                            <th>Status</th>
                            <th>Processed By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($all_hikes)): ?>
                            <?php foreach ($all_hikes as $hike): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($hike->employee_name) ?></strong></td>
                                <td><?= htmlspecialchars($hike->department_name ?? 'N/A') ?></td>
                                <td>₹<?= number_format($hike->current_salary, 0) ?></td>
                                <td class="fw-semibold">₹<?= number_format($hike->proposed_salary, 0) ?></td>
                                <td><?= number_format($hike->hike_percentage, 1) ?>%</td>
                                <td>
                                    <?php
                                    $badgeClass = 'secondary';
                                    if ($hike->status === 'Pending') $badgeClass = 'warning';
                                    elseif ($hike->status === 'Approved') $badgeClass = 'success';
                                    elseif ($hike->status === 'Rejected') $badgeClass = 'danger';
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>"><?= $hike->status ?></span>
                                </td>
                                <td>
                                    <?php if ($hike->status === 'Approved'): ?>
                                        <?= htmlspecialchars($hike->approved_by_name ?? $hike->proposed_by_name ?? 'N/A') ?>
                                    <?php elseif ($hike->status === 'Rejected'): ?>
                                        <?= htmlspecialchars($hike->rejected_by_name ?? 'N/A') ?>
                                    <?php else: ?>
                                        <span class="text-muted">Pending review</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($hike->status === 'Approved' && $hike->approved_at): ?>
                                        <?= date('d M Y', strtotime($hike->approved_at)) ?>
                                    <?php elseif ($hike->status === 'Rejected' && $hike->rejected_at): ?>
                                        <?= date('d M Y', strtotime($hike->rejected_at)) ?>
                                    <?php else: ?>
                                        <?= date('d M Y', strtotime($hike->proposed_at ?? $hike->created_at)) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No salary hike records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable.isDataTable('#adminHikesTable')) {
        $('#adminHikesTable').DataTable().destroy();
    }

    if ($.fn.DataTable) {
        $('#adminHikesTable').DataTable({
            responsive: false,
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
            order: [[7, 'desc']],
            scrollX: false,
            scrollCollapse: false,
            dom:
                "<'dataTables-toolbar'<'dataTables-length'l><'dataTables-filter'f>>" +
                "<'dataTables-table-wrapper'tr>" +
                "<'dataTables-footer'<'dataTables-info'i><'dataTables-pagination'p>>",
            language: {
                search: '',
                searchPlaceholder: 'Search records...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ records',
                emptyTable: 'No salary hike records found.',
                paginate: {
                    previous: "<i class='bi bi-chevron-left'></i>",
                    next: "<i class='bi bi-chevron-right'></i>"
                }
            }
        });
    }
});
</script>

<?php $this->load->view('layouts/footer'); ?>
