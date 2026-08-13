<?php $this->load->view('layouts/header'); ?>
<?php $this->load->view('layouts/navbar'); ?>

<div class="container py-4">
    <div class="page-header d-flex align-items-center gap-3 mb-4">
        <div class="page-header-icon">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="flex-grow-1">
            <h2 class="fw-bold mb-1"><?= $title ?></h2>
            <p class="text-muted mb-0">
                <?php if ($is_read_only): ?>
                    Read-only view of all salary hike records across the organization.
                <?php else: ?>
                    View and manage all salary hike history records.
                <?php endif; ?>
            </p>
        </div>
        <div class="page-header-actions">
            <a href="<?= site_url('hikes') ?>" class="btn btn-back">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <?php if ($is_read_only): ?>
    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-shield-check me-2"></i>
        <div>This is a read-only overview. Salary modifications are managed by HR through the approval workflow.</div>
    </div>
    <?php endif; ?>

    <div class="form-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-table me-2"></i>Complete Hike History
            </h5>
            <span class="badge bg-secondary"><?= count($hikes) ?> Records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="hikeHistoryTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Previous Salary</th>
                            <th>Hike %</th>
                            <th>Hike Amount</th>
                            <th>New Salary</th>
                            <th>Status</th>
                            <th>Proposed By</th>
                            <th><?= $is_read_only ? 'Processed By' : 'Decision' ?></th>
                            <th>Date</th>
                            <?php if (!$is_read_only): ?>
                            <th class="text-center">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($hikes)): ?>
                            <?php foreach ($hikes as $index => $hike): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= htmlspecialchars($hike->employee_name) ?></strong></td>
                                <td><?= htmlspecialchars($hike->department_name ?? 'N/A') ?></td>
                                <td>₹<?= number_format($hike->current_salary, 0) ?></td>
                                <td><?= number_format($hike->hike_percentage, 1) ?>%</td>
                                <td>+₹<?= number_format($hike->hike_amount ?? ($hike->proposed_salary - $hike->current_salary), 0) ?></td>
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
                                <td><?= htmlspecialchars($hike->proposed_by_name ?? 'N/A') ?></td>
                                <td>
                                    <?php if ($hike->status === 'Approved'): ?>
                                        <span class="text-success">
                                            <?= htmlspecialchars($hike->approved_by_name ?? 'N/A') ?>
                                            <?php if ($hike->approved_at): ?>
                                                <br><small class="text-muted"><?= date('d M Y', strtotime($hike->approved_at)) ?></small>
                                            <?php endif; ?>
                                        </span>
                                    <?php elseif ($hike->status === 'Rejected'): ?>
                                        <span class="text-danger">
                                            <?= htmlspecialchars($hike->rejected_by_name ?? 'N/A') ?>
                                            <?php if ($hike->rejection_reason): ?>
                                                <br><small class="text-muted" title="<?= htmlspecialchars($hike->rejection_reason) ?>">
                                                    <?= htmlspecialchars(substr($hike->rejection_reason, 0, 50)) ?>...
                                                </small>
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Pending</span>
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
                                <?php if (!$is_read_only): ?>
                                <td class="text-center">
                                    <?php if ($hike->status === 'Pending'): ?>
                                        <button class="btn btn-success btn-sm btn-hike-action"
                                                data-id="<?= $hike->hike_id ?>" data-action="approve">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm btn-hike-reject"
                                                data-id="<?= $hike->hike_id ?>" data-action="reject"
                                                data-employee="<?= htmlspecialchars($hike->employee_name) ?>">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $is_read_only ? '11' : '12' ?>" class="text-center text-muted py-4">
                                    No hike records found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if (!$is_read_only): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfName = '<?= $this->security->get_csrf_token_name() ?>';
    const csrfHash = '<?= $this->security->get_csrf_hash() ?>';

    document.querySelectorAll('.btn-hike-action').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const hikeId = this.dataset.id;

            Swal.fire({
                title: 'Approve Salary Hike?',
                text: 'This will update the employee\'s salary.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    submitReview(hikeId, 'approve', null);
                }
            });
        });
    });

    document.querySelectorAll('.btn-hike-reject').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const hikeId = this.dataset.id;
            const employee = this.dataset.employee;

            Swal.fire({
                title: 'Reject Salary Hike?',
                html: '<p>Employee: <strong>' + employee + '</strong></p>',
                input: 'textarea',
                inputPlaceholder: 'Enter reason for rejection...',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Reject',
                cancelButtonText: 'Cancel',
                inputValidator: function(value) {
                    if (!value || value.trim().length < 5) {
                        return 'Please provide a reason (at least 5 characters).';
                    }
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    submitReview(hikeId, 'reject', result.value);
                }
            });
        });
    });

    function submitReview(hikeId, action, reason) {
        let formData = csrfName + '=' + encodeURIComponent(csrfHash);
        if (reason) {
            formData += '&rejection_reason=' + encodeURIComponent(reason);
        }

        fetch('<?= site_url("hikes/review/") ?>' + hikeId + '/' + action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(function(resp) { return resp.json(); })
        .then(function(data) {
            if (data.status) {
                Swal.fire({ icon: 'success', title: 'Done!', text: data.message, timer: 2000, showConfirmButton: false })
                    .then(function() { location.reload(); });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to process.' });
            }
        })
        .catch(function() {
            Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred. Please try again.' });
        });
    }
});
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable) {
        $('#hikeHistoryTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [[10, 'desc']],
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
