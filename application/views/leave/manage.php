<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="container py-4">

    <?php if ($this->session->flashdata('error')) { ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <?php $this->load->view('layouts/page_header', [
        'title' => 'Manage Leave Requests',
        'subtitle' => 'Review and manage employee leave requests.',
        'icon' => 'bi-calendar2-check',
        'show_back' => true,
        'back_url' => site_url('dashboard'),
        'back_label' => 'Back'
    ]); ?>

    <!-- Leave Table Card -->
    <div class="table-card">
        <div class="card-header">
            <div class="d-flex gap-2 mb-3">
                <a href="<?= site_url('leave/manage'); ?>"
                    class="btn <?= ($status === '' || !isset($status)) ? 'btn-primary' : 'btn-outline-secondary'; ?> btn-sm">
                    <i class="bi bi-list-ul me-1"></i> All
                </a>
                <a href="<?= site_url('leave/manage?status=Pending'); ?>"
                    class="btn <?= (isset($status) && $status === 'Pending') ? 'btn-warning' : 'btn-outline-secondary'; ?> btn-sm">
                    <i class="bi bi-clock me-1"></i> Pending
                </a>
                <a href="<?= site_url('leave/manage?status=Approved'); ?>"
                    class="btn <?= (isset($status) && $status === 'Approved') ? 'btn-success' : 'btn-outline-secondary'; ?> btn-sm">
                    <i class="bi bi-check-circle me-1"></i> Approved
                </a>
                <a href="<?= site_url('leave/manage?status=Rejected'); ?>"
                    class="btn <?= (isset($status) && $status === 'Rejected') ? 'btn-danger' : 'btn-outline-secondary'; ?> btn-sm">
                    <i class="bi bi-x-circle me-1"></i> Rejected
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <table id="manageLeavesTable" class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Period</th>
                        <th>Urgency</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leave_requests as $row) { ?>
                        <tr>
                            <td><strong>#LV<?= str_pad($row->leave_id, 4, '0', STR_PAD_LEFT); ?></strong></td>
                            <td><?= $row->employee_name; ?></td>
                            <td><?= $row->leave_type; ?></td>
                            <td>
                                <?= date('d M Y', strtotime($row->from_date)); ?>
                                <br><small class="text-muted">to <?= date('d M Y', strtotime($row->to_date)); ?></small>
                            </td>
                            <td>
                                <?php
                                    $urgencyClass = 'secondary';
                                    if ($row->urgency === 'Medium') $urgencyClass = 'info';
                                    elseif ($row->urgency === 'High') $urgencyClass = 'warning';
                                    elseif ($row->urgency === 'Critical') $urgencyClass = 'danger';
                                ?>
                                <span class="badge bg-<?= $urgencyClass; ?>"><?= $row->urgency; ?></span>
                            </td>
                            <td><?= word_limiter($row->reason, 8); ?></td>
                            <td>
                                <?php
                                    $statusClass = 'warning';
                                    if ($row->status === 'Approved') $statusClass = 'success';
                                    elseif ($row->status === 'Rejected') $statusClass = 'danger';
                                ?>
                                <span class="badge bg-<?= $statusClass; ?>"><?= $row->status; ?></span>
                            </td>
                            <td class="employee-actions">
                                <?php if ($row->status === 'Pending') { ?>
                                    <button class="btn btn-icon btn-success reviewAction"
                                        data-id="<?= $row->leave_id; ?>"
                                        data-action="approve"
                                        data-employee="<?= $row->employee_name; ?>"
                                        title="Approve">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button class="btn btn-icon btn-danger reviewAction"
                                        data-id="<?= $row->leave_id; ?>"
                                        data-action="reject"
                                        data-employee="<?= $row->employee_name; ?>"
                                        title="Reject">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                <?php } else { ?>
                                    <span class="text-muted"><?= $row->decided_by_name ? 'By ' . $row->decided_by_name : '—'; ?></span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Review Comment Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel">
                    <i class="bi bi-pencil-square me-2"></i> Review Comment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="reviewModalBody" class="mb-3"></p>
                <div class="mb-3">
                    <label class="form-label" for="review_comment">Comment</label>
                    <textarea class="form-control" id="review_comment" rows="3" placeholder="Enter review comment (optional)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="confirmReviewBtn">
                    <i class="bi bi-check-circle me-1"></i> Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$this->load->view('layouts/footer');
?>

<script>
$(document).ready(function() {
    initDataTable('#manageLeavesTable', {
        order: [[0, 'desc']],
        language: {
            emptyTable: "No leave requests found",
            searchPlaceholder: "Search leave requests...",
        }
    });

    let reviewAction = '';
    let reviewId = '';

    $('.reviewAction').on('click', function() {
        reviewId = $(this).data('id');
        reviewAction = $(this).data('action');
        const employeeName = $(this).data('employee');

        const actionLabel = reviewAction === 'approve' ? 'Approve' : 'Reject';
        const btnClass = reviewAction === 'approve' ? 'btn-success' : 'btn-danger';

        $('#reviewModalBody').html(
            'Are you sure you want to <strong>' + actionLabel + '</strong> the leave request for <strong>' + employeeName + '</strong>?'
        );

        $('#confirmReviewBtn')
            .removeClass('btn-success btn-danger')
            .addClass(btnClass);

        $('#review_comment').val('');

        const reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
        reviewModal.show();
    });

    $('#confirmReviewBtn').on('click', function() {
        const comment = $('#review_comment').val();
        const url = '<?= site_url("leave/review/"); ?>' + reviewId + '/' + reviewAction;

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: { review_comment: comment },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                if (response.status) {
                    Swal.fire({
                        icon: 'success', title: 'Success!',
                        text: response.message || 'Leave request has been reviewed.',
                        timer: 1500, showConfirmButton: false
                    }).then(function() { location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Something went wrong.' });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred while processing the request.' });
            }
        });

        bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
    });
});
</script>
