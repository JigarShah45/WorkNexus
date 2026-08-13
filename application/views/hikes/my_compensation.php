<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI =& get_instance();
?>
<?php $this->load->view('layouts/header'); ?>
<?php $this->load->view('layouts/navbar'); ?>

<link rel="stylesheet" href="<?= base_url('assets/css/salary.css'); ?>">

<div class="container py-4">
    <div class="page-header d-flex align-items-center gap-3 mb-4">
        <div class="page-header-icon" style="background:var(--success-soft);">
            <i class="bi bi-wallet2 text-success"></i>
        </div>
        <div class="flex-grow-1">
            <h2 class="fw-bold mb-1"><?= $title ?></h2>
            <p class="text-muted mb-0">View your compensation details and salary history.</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="salary-card h-100">
                <div class="salary-card-header">
                    <div class="salary-card-icon bg-primary-soft">
                        <i class="bi bi-person-badge text-primary"></i>
                    </div>
                    <h5>Employee Details</h5>
                </div>
                <div class="salary-card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Employee ID</small>
                        <span class="fw-semibold">#EMP<?= str_pad($employee->employee_id, 4, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Name</small>
                        <span class="fw-semibold"><?= htmlspecialchars($employee->employee_name) ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Department</small>
                        <span class="fw-semibold"><?= htmlspecialchars($employee->department_name ?? 'N/A') ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Joining Date</small>
                        <span class="fw-semibold"><?= date('d M Y', strtotime($employee->joining_date)) ?></span>
                    </div>
                    <div>
                        <small class="text-muted d-block">Status</small>
                        <span class="salary-status-badge <?= $employee->status === 'Active' ? 'active' : 'inactive' ?>">
                            <?= $employee->status ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="salary-card h-100">
                <div class="salary-card-header">
                    <div class="salary-card-icon bg-success-soft">
                        <i class="bi bi-cash-stack text-success"></i>
                    </div>
                    <h5>Current Compensation</h5>
                </div>
                <div class="salary-card-body">
                    <div class="salary-stats-grid salary-stats-grid-wide">
                        <div class="salary-stat-card featured">
                            <div class="salary-stat-icon bg-success-soft">
                                <i class="bi bi-currency-dollar text-success"></i>
                            </div>
                            <span class="salary-stat-label">Current Salary</span>
                            <span class="salary-stat-value salary-highlight">₹<?= number_format($employee->employee_salary, 0) ?></span>
                        </div>

                        <?php if ($latest_hike): ?>
                        <div class="salary-stat-card">
                            <div class="salary-stat-icon bg-primary-soft">
                                <i class="bi bi-graph-up-arrow text-primary"></i>
                            </div>
                            <span class="salary-stat-label">Last Hike</span>
                            <span class="salary-stat-value hike-highlight">+<?= number_format($latest_hike->hike_percentage, 1) ?>%</span>
                        </div>

                        <div class="salary-stat-card">
                            <div class="salary-stat-icon bg-info-soft">
                                <i class="bi bi-calendar-check text-info"></i>
                            </div>
                            <span class="salary-stat-label">Effective Date</span>
                            <span class="salary-stat-value">
                                <?= $latest_hike->effective_date ? date('d M Y', strtotime($latest_hike->effective_date)) : date('d M Y', strtotime($latest_hike->approved_at)) ?>
                            </span>
                        </div>

                        <div class="salary-stat-card">
                            <div class="salary-stat-icon bg-warning-soft">
                                <i class="bi bi-cash text-warning"></i>
                            </div>
                            <span class="salary-stat-label">Previous Salary</span>
                            <span class="salary-stat-value">₹<?= number_format($latest_hike->current_salary, 0) ?></span>
                        </div>
                        <?php else: ?>
                        <div class="salary-empty-state">
                            <div class="salary-empty-icon">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <h5>No Salary Hike History</h5>
                            <p>Your salary is at the initial configured amount. No hikes have been applied yet.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($hike_history)): ?>
    <div class="form-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-clock-history me-2 text-primary"></i>Salary Revision History
            </h5>
            <span class="badge bg-secondary"><?= count($hike_history) ?> Record<?= count($hike_history) > 1 ? 's' : '' ?></span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="compensationHistoryTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Previous Salary</th>
                            <th>Hike %</th>
                            <th>Hike Amount</th>
                            <th>New Salary</th>
                            <th>Status</th>
                            <th>Effective Date</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hike_history as $index => $hike): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>₹<?= number_format($hike->current_salary, 0) ?></td>
                            <td><?= number_format($hike->hike_percentage, 1) ?>%</td>
                            <td class="text-success">+₹<?= number_format($hike->hike_amount ?? ($hike->proposed_salary - $hike->current_salary), 0) ?></td>
                            <td class="fw-semibold">₹<?= number_format($hike->proposed_salary, 0) ?></td>
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
                                <?php if ($hike->status === 'Approved' && $hike->approved_at): ?>
                                    <?= $hike->effective_date ? date('d M Y', strtotime($hike->effective_date)) : date('d M Y', strtotime($hike->approved_at)) ?>
                                <?php elseif ($hike->status === 'Rejected' && $hike->rejected_at): ?>
                                    <?= date('d M Y', strtotime($hike->rejected_at)) ?>
                                <?php else: ?>
                                    <span class="text-muted">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($hike->status === 'Approved'): ?>
                                    <span class="text-muted" title="<?= htmlspecialchars($hike->justification) ?>">
                                        <?= htmlspecialchars(substr($hike->justification, 0, 40)) ?>
                                        <?= strlen($hike->justification) > 40 ? '...' : '' ?>
                                    </span>
                                <?php elseif ($hike->status === 'Rejected' && $hike->rejection_reason): ?>
                                    <span class="text-danger" title="<?= htmlspecialchars($hike->rejection_reason) ?>">
                                        <?= htmlspecialchars(substr($hike->rejection_reason, 0, 40)) ?>
                                        <?= strlen($hike->rejection_reason) > 40 ? '...' : '' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted"><?= htmlspecialchars(substr($hike->justification, 0, 40)) ?>...</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable) {
        $('#compensationHistoryTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [[0, 'desc']],
            language: {
                search: '',
                searchPlaceholder: 'Search history...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ records',
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
