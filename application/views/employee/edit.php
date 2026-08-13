<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="container py-4">

    <?php $this->load->view('layouts/page_header', [
        'title' => 'Edit Employee',
        'subtitle' => 'Update employee details.',
        'icon' => 'bi-pencil-square',
        'show_back' => true,
        'back_url' => site_url('employee'),
        'back_label' => 'Back'
    ]); ?>

    <div class="form-card">
        <div class="card-body">
            <div class="text-center mb-4">
                <img src="<?= site_url('employee/image/' . $employee->employee_id); ?>"
                    id="previewImage"
                    class="profile-img-lg shadow"
                    alt="Employee">
            </div>

            <form action="<?= site_url('employee/update/' . $employee->employee_id); ?>"
                method="post"
                enctype="multipart/form-data">

                <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>

                <div class="row">
                    <!-- Employee Name -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Employee Name <span class="text-danger">*</span></label>
                        <input type="text" name="employee_name" class="form-control"
                            value="<?= set_value('employee_name', $employee->employee_name); ?>">
                        <?= form_error('employee_name', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="employee_email" class="form-control"
                            value="<?= set_value('employee_email', $employee->employee_email); ?>">
                        <?= form_error('employee_email', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <!-- Phone -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" name="employee_phone" class="form-control"
                            value="<?= set_value('employee_phone', $employee->employee_phone); ?>">
                        <?= form_error('employee_phone', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <!-- Salary -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Salary <span class="text-danger">*</span></label>
                        <input type="number" name="employee_salary" class="form-control"
                            value="<?= set_value('employee_salary', $employee->employee_salary); ?>">
                        <?= form_error('employee_salary', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <!-- Department -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Department <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-select">
                            <?php foreach ($departments as $department) { ?>
                                <option value="<?= $department->department_id; ?>"
                                    <?= set_select('department_id', $department->department_id, ($department->department_id == $employee->department_id)); ?>>
                                    <?= $department->department_name; ?>
                                </option>
                            <?php } ?>
                        </select>
                        <?= form_error('department_id', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <!-- Status -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select">
                            <option value="Active" <?= set_select('status', 'Active', ($employee->status == 'Active')); ?>>Active</option>
                            <option value="Inactive" <?= set_select('status', 'Inactive', ($employee->status == 'Inactive')); ?>>Inactive</option>
                        </select>
                        <?= form_error('status', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <!-- Profile Image -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Profile Picture</label>
                        <input type="file" name="profile_image" id="profile_image" class="form-control" accept="image/*">
                        <small class="text-muted">Leave empty if you don't want to change the picture.</small>
                    </div>
                </div>

                <div class="form-footer mt-3">
                    <a href="<?= site_url('employee'); ?>" class="btn btn-back-action">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle-fill me-1"></i> Update Employee
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

<script>
document.getElementById('profile_image').addEventListener('change', function(e){
    if(e.target.files.length > 0) {
        document.getElementById('previewImage').src = URL.createObjectURL(e.target.files[0]);
    }
});
</script>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    Swal.fire({
        title: 'Update Employee?',
        text: 'Employee details will be updated.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Update',
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
