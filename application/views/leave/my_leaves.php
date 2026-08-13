<?php
$CI =& get_instance();
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
        'title' => 'My Leave Requests',
        'subtitle' => 'View and manage your leave history.',
        'icon' => 'bi-calendar2-check',
        'show_back' => true,
        'back_url' => site_url('dashboard'),
        'back_label' => 'Back',
        'primary_url' => !$CI->isAdmin() ? site_url('leave/request_leave') : '',
        'primary_label' => 'Request Leave',
        'primary_icon' => 'bi-plus-circle',
        'primary_class' => 'btn btn-primary'
    ]); ?>

    <!-- Leave Table Card -->
    <div class="table-card">
        <div class="card-body p-0">
            <table id="myLeavesTable" class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Period</th>
                        <th>Urgency</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Reviewed By</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leave_requests as $row) { ?>
                        <tr>
                            <td><strong>#LV<?= str_pad($row->leave_id, 4, '0', STR_PAD_LEFT); ?></strong></td>
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
                            <td><?= $row->decided_by_name ? $row->decided_by_name : '<span class="text-muted">—</span>'; ?></td>
                            <td><?= date('d M Y', strtotime($row->created_at)); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
$this->load->view('layouts/footer');
?>

<script>
$(document).ready(function() {
    initDataTable('#myLeavesTable', {
        order: [[7, 'desc']],
        language: {
            emptyTable: "No leave requests found",
            searchPlaceholder: "Search leave requests...",
        }
    });
});
</script>
