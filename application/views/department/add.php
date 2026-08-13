<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="container py-4">

    <?php $this->load->view('layouts/page_header', [
        'title' => 'Add Department',
        'subtitle' => 'Create a new department record.',
        'icon' => 'bi-building-add',
        'show_back' => true,
        'back_url' => site_url('department'),
        'back_label' => 'Back'
    ]); ?>

    <?php if(validation_errors()){ ?>
        <div class="alert alert-danger"><?= validation_errors(); ?></div>
    <?php } ?>

    <?php if($this->session->flashdata('error')){ ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div>
    <?php } ?>

    <!-- Form Card -->
    <div class="form-card">
        <div class="card-body">
            <form method="post" action="<?= site_url('department/store'); ?>">
                <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>

                <div class="mb-3">
                    <label class="form-label">Department Name <span class="text-danger">*</span></label>
                    <input type="text" name="department_name" class="form-control"
                        value="<?= set_value('department_name'); ?>" placeholder="Enter department name">
                </div>

                <div class="form-footer mt-3">
                    <a href="<?= site_url('department'); ?>" class="btn btn-back-action">
                        <i class="bi bi-arrow-left me-1"></i> Cancel
                    </a>
                    <button class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Department
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    Swal.fire({
        title: 'Save Department?',
        text: 'A new department will be created.',
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
