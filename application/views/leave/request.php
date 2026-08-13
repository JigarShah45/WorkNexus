<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="container py-4">

    <?php $this->load->view('layouts/page_header', [
        'title' => 'Request Leave',
        'subtitle' => 'Submit a new leave request.',
        'icon' => 'bi-calendar2-plus',
        'show_back' => true,
        'back_url' => site_url('dashboard'),
        'back_label' => 'Back'
    ]); ?>

    <?php if ($this->session->flashdata('error')) { ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <?php if (validation_errors()) { ?>
        <div class="alert alert-danger"><?= validation_errors(); ?></div>
    <?php } ?>

    <!-- Form Card -->
    <div class="add-employee-card">
        <form action="<?= site_url('leave/store_leave'); ?>" method="post" id="leaveForm" novalidate>
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="leave_type">Leave Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="leave_type" name="leave_type">
                            <option value="">Select Leave Type</option>
                            <option value="Sick" <?= set_select('leave_type', 'Sick'); ?>>Sick</option>
                            <option value="Casual" <?= set_select('leave_type', 'Casual'); ?>>Casual</option>
                            <option value="Personal" <?= set_select('leave_type', 'Personal'); ?>>Personal</option>
                            <option value="Maternity" <?= set_select('leave_type', 'Maternity'); ?>>Maternity</option>
                            <option value="Paternity" <?= set_select('leave_type', 'Paternity'); ?>>Paternity</option>
                            <option value="Paid" <?= set_select('leave_type', 'Paid'); ?>>Paid</option>
                            <option value="Unpaid" <?= set_select('leave_type', 'Unpaid'); ?>>Unpaid</option>
                        </select>
                        <?= form_error('leave_type', '<div class="form-error">', '</div>'); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="urgency">Urgency <span class="text-danger">*</span></label>
                        <select class="form-select" id="urgency" name="urgency">
                            <option value="">Select Urgency</option>
                            <option value="Low" <?= set_select('urgency', 'Low'); ?>>Low</option>
                            <option value="Medium" <?= set_select('urgency', 'Medium'); ?>>Medium</option>
                            <option value="High" <?= set_select('urgency', 'High'); ?>>High</option>
                            <option value="Critical" <?= set_select('urgency', 'Critical'); ?>>Critical</option>
                        </select>
                        <?= form_error('urgency', '<div class="form-error">', '</div>'); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="start_date">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?= set_value('start_date'); ?>">
                        <?= form_error('start_date', '<div class="form-error">', '</div>'); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="end_date">End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?= set_value('end_date'); ?>">
                        <?= form_error('end_date', '<div class="form-error">', '</div>'); ?>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label" for="reason">Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reason" name="reason" rows="4"
                            placeholder="Provide a reason for your leave request"><?= set_value('reason'); ?></textarea>
                        <?= form_error('reason', '<div class="form-error">', '</div>'); ?>
                    </div>
                </div>
            </div>

            <div class="form-footer">
                <a href="<?= site_url('leave/my_leaves'); ?>" class="btn btn-back-action">
                    <i class="bi bi-arrow-left me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send-fill me-1"></i> Submit Request
                </button>
            </div>
        </form>
    </div>

</div>

</div>

<script>
document.getElementById('leaveForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    Swal.fire({
        title: 'Submit Leave Request?',
        text: 'Your leave request will be sent for approval.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Submit',
        cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
</script>

<?php
$this->load->view('layouts/footer');
?>
