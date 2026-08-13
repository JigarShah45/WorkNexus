<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="container py-4">

    <?php $this->load->view('layouts/page_header', [
        'title' => 'Schedule Meeting',
        'subtitle' => 'Plan a new meeting.',
        'icon' => 'bi-calendar2-plus',
        'show_back' => true,
        'back_url' => site_url('meetings'),
        'back_label' => 'Back'
    ]); ?>

    <!-- Main Form Card -->
    <div class="form-card">
        <form action="<?= site_url('meetings/store'); ?>"
              method="post"
              enctype="multipart/form-data"
              novalidate>
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

            <div class="card-body">
                <?php if (validation_errors()) { ?>
                    <div class="alert alert-danger"><?= validation_errors(); ?></div>
                <?php } ?>
                <?php if ($this->session->flashdata('error')) { ?>
                    <div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div>
                <?php } ?>

                <div class="row">
                    <!-- Client Name -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="client_name">Client Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="client_name" name="client_name"
                            value="<?= set_value('client_name'); ?>" placeholder="Enter client name">
                        <?= form_error('client_name', '<small class="text-danger">', '</small>'); ?>
                    </div>
                    <!-- Meeting Title -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="meeting_title">Meeting Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="meeting_title" name="meeting_title"
                            value="<?= set_value('meeting_title'); ?>" placeholder="Enter meeting title">
                        <?= form_error('meeting_title', '<small class="text-danger">', '</small>'); ?>
                    </div>
                    <!-- Meeting Date -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="meeting_date">Meeting Date <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="meeting_date" name="meeting_date"
                            value="<?= set_value('meeting_date'); ?>">
                        <?= form_error('meeting_date', '<small class="text-danger">', '</small>'); ?>
                    </div>
                    <!-- Meeting Location -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="meeting_location">Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="meeting_location" name="meeting_location"
                            value="<?= set_value('meeting_location'); ?>" placeholder="Enter meeting location">
                        <?= form_error('meeting_location', '<small class="text-danger">', '</small>'); ?>
                    </div>
                    <!-- Description -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"
                            placeholder="Enter meeting description"><?= set_value('description'); ?></textarea>
                    </div>
                </div>

                <!-- Involved Employees -->
                <div class="mb-3">
                    <label class="form-label">Involved Employees</label>
                    <div class="row">
                        <?php foreach ($employees as $emp) { ?>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="employee_ids[]" value="<?= $emp->employee_id; ?>"
                                        id="emp_<?= $emp->employee_id; ?>"
                                        <?= in_array($emp->employee_id, $meeting_employees) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="emp_<?= $emp->employee_id; ?>">
                                        <?= $emp->employee_name; ?>
                                    </label>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- File Uploads -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="minutes_file">Minutes File</label>
                        <input type="file" class="form-control" id="minutes_file" name="minutes_file" accept=".txt,.pdf">
                        <small class="text-muted">Accepted formats: .txt, .pdf</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="presentation_file">Presentation File</label>
                        <input type="file" class="form-control" id="presentation_file" name="presentation_file" accept=".ppt,.pptx">
                        <small class="text-muted">Accepted formats: .ppt, .pptx</small>
                    </div>
                </div>
            </div>

            <!-- Form Footer -->
            <div class="form-footer">
                <a href="<?= site_url('meetings'); ?>" class="btn btn-back-action">
                    <i class="bi bi-arrow-left me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle-fill me-1"></i> Save Meeting
                </button>
            </div>
        </form>
    </div>

</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    Swal.fire({
        title: 'Save Meeting?',
        text: 'A new meeting will be scheduled.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Save',
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
