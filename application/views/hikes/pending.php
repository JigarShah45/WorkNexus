<?php $this->load->view('layouts/header'); ?>
<?php $this->load->view('layouts/navbar'); ?>

<div class="container py-4">
    <div class="page-header d-flex align-items-center gap-3 mb-4">
        <div class="page-header-icon" style="background:var(--warning-soft);">
            <i class="bi bi-hourglass-split text-warning"></i>
        </div>
        <div class="flex-grow-1">
            <h2 class="fw-bold mb-1"><?= $title ?></h2>
            <p class="text-muted mb-0">Review and process pending salary hike proposals.</p>
        </div>
        <div class="page-header-actions">
            <a href="<?= site_url('hikes') ?>" class="btn btn-back">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <?php if (empty($pending_hikes)): ?>
    <div class="form-card">
        <div class="card-body text-center py-5">
            <div class="mb-3">
                <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
            </div>
            <h5>No Pending Proposals</h5>
            <p class="text-muted">All salary hike proposals have been processed. No action required.</p>
            <a href="<?= site_url('hikes') ?>" class="btn btn-primary">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="form-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-check me-2"></i>
                <?= count($pending_hikes) ?> Proposal<?= count($pending_hikes) > 1 ? 's' : '' ?> Awaiting Review
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="pendingHikesTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Current Salary</th>
                            <th>Proposed Salary</th>
                            <th>Hike %</th>
                            <th>Hike Amount</th>
                            <th>Effective Date</th>
                            <th>Proposed By</th>
                            <th>Justification</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_hikes as $index => $hike): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><strong><?= htmlspecialchars($hike->employee_name) ?></strong></td>
                            <td><?= htmlspecialchars($hike->department_name ?? 'N/A') ?></td>
                            <td>₹<?= number_format($hike->current_salary, 0) ?></td>
                            <td class="fw-semibold text-success">₹<?= number_format($hike->proposed_salary, 0) ?></td>
                            <td><span class="badge bg-info"><?= number_format($hike->hike_percentage, 1) ?>%</span></td>
                            <td>+₹<?= number_format($hike->hike_amount, 0) ?></td>
                            <td><?= $hike->effective_date ? date('d M Y', strtotime($hike->effective_date)) : '-' ?></td>
                            <td><?= htmlspecialchars($hike->proposed_by_name ?? 'N/A') ?></td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($hike->justification) ?>">
                                    <?= htmlspecialchars($hike->justification) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-success btn-sm btn-approve"
                                        data-id="<?= $hike->hike_id ?>"
                                        data-employee="<?= htmlspecialchars($hike->employee_name) ?>"
                                        data-current="<?= number_format($hike->current_salary, 0) ?>"
                                        data-hike="<?= number_format($hike->hike_percentage, 1) ?>"
                                        data-new="<?= number_format($hike->proposed_salary, 0) ?>">
                                    <i class="bi bi-check-lg"></i> Approve
                                </button>
                                <button class="btn btn-danger btn-sm btn-reject"
                                        data-id="<?= $hike->hike_id ?>"
                                        data-employee="<?= htmlspecialchars($hike->employee_name) ?>">
                                    <i class="bi bi-x-lg"></i> Reject
                                </button>
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
    const csrfName = '<?= $this->security->get_csrf_token_name() ?>';
    const csrfHash = '<?= $this->security->get_csrf_hash() ?>';

    document.querySelectorAll('.btn-approve').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const hikeId = this.dataset.id;
            const employee = this.dataset.employee;
            const current = this.dataset.current;
            const hike = this.dataset.hike;
            const newSalary = this.dataset.new;

            Swal.fire({
                title: 'Approve Salary Hike?',
                html: '<div class="text-start">'
                    + '<p><strong>Employee:</strong> ' + employee + '</p>'
                    + '<p><strong>Current Salary:</strong> ₹' + current + '</p>'
                    + '<p><strong>Hike:</strong> ' + hike + '%</p>'
                    + '<p><strong>New Salary:</strong> ₹' + newSalary + '</p>'
                    + '<p class="text-muted small">The employee\'s salary will be updated immediately.</p>'
                    + '</div>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    submitAction(hikeId, 'approve', null);
                }
            });
        });
    });

    document.querySelectorAll('.btn-reject').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const hikeId = this.dataset.id;
            const employee = this.dataset.employee;

            Swal.fire({
                title: 'Reject Salary Hike?',
                html: '<p>Employee: <strong>' + employee + '</strong></p>'
                    + '<p class="text-muted small">Please provide a reason for rejection. This will be shared with the employee.</p>',
                input: 'textarea',
                inputPlaceholder: 'Enter reason for rejection (e.g., "Performance criteria not met for current review cycle")',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Reject',
                cancelButtonText: 'Cancel',
                inputValidator: function(value) {
                    if (!value || value.trim().length < 5) {
                        return 'Please provide a meaningful reason (at least 5 characters).';
                    }
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    submitAction(hikeId, 'reject', result.value);
                }
            });
        });
    });

    function submitAction(hikeId, action, reason) {
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
                Swal.fire({ icon: 'success', title: action === 'approve' ? 'Approved!' : 'Rejected', text: data.message, timer: 2000, showConfirmButton: false })
                    .then(function() { location.reload(); });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to process.' });
            }
        })
        .catch(function() {
            Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred. Please try again.' });
        });
    }

    if ($.fn.DataTable) {
        $('#pendingHikesTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [[8, 'desc']],
            language: {
                search: '',
                searchPlaceholder: 'Search proposals...',
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
