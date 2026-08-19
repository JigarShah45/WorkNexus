<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="employee-edit-page">
    <div class="container py-4">

        <?php $this->load->view('layouts/page_header', [
            'title' => 'Edit Employee',
            'subtitle' => 'Update employee details.',
            'icon' => 'bi-pencil-square',
            'show_back' => true,
            'back_url' => site_url('employee'),
            'back_label' => 'Back'
        ]); ?>

        <!-- Edit Form Card -->
        <div class="form-card">
            <form action="<?= site_url('employee/update/' . $employee->employee_id); ?>"
                  method="post"
                  enctype="multipart/form-data"
                  id="employeeForm"
                  novalidate>

                <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>

                <div class="card-body">
                    <div class="employee-form-layout">

                        <!-- LEFT: Profile Photo Section -->
                        <div class="photo-section">
                            <h6 class="photo-label">Employee Photo</h6>
                            <div class="photo-avatar-wrap">
                                <div id="previewContainer" class="photo-avatar">
                                    <i id="defaultIcon" class="bi bi-person-fill" style="font-size:3rem;color:var(--primary-300);"></i>
                                    <img id="previewImage"
                                         src="<?= site_url('employee/image/' . $employee->employee_id); ?>"
                                         alt="Employee Photo"
                                         style="display:none;"
                                         onload="document.getElementById('previewImage').style.display='block';document.getElementById('defaultIcon').style.display='none';"
                                         onerror="document.getElementById('previewImage').style.display='none';document.getElementById('defaultIcon').style.display='inline';">
                                </div>
                                <div class="photo-overlay" id="photoOverlay">
                                    <i class="bi bi-camera"></i>
                                    <span>Change Photo</span>
                                </div>
                            </div>
                            <input type="file" id="profile_image" name="profile_image" accept=".jpg,.jpeg,.png" hidden>
                            <label for="profile_image" class="btn btn-upload-photo">
                                <i class="bi bi-camera-fill me-1"></i> Change Photo
                            </label>
                            <p class="photo-note">JPG, JPEG or PNG. Max 2 MB.<br>Leave empty to keep the current picture.</p>
                        </div>

                        <!-- RIGHT: Employee Details Form -->
                        <div class="form-section">
                            <div class="row">
                                <!-- Employee ID (read-only) -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Employee ID</label>
                                    <input type="text" class="form-control" value="#EMP<?= str_pad($employee->employee_id, 4, '0', STR_PAD_LEFT); ?>" readonly>
                                </div>

                                <!-- Employee Name -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="employee_name">Employee Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="employee_name" name="employee_name"
                                        value="<?= set_value('employee_name', $employee->employee_name); ?>" placeholder="Enter employee name">
                                    <?= form_error('employee_name', '<div class="form-error">', '</div>'); ?>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="employee_email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="employee_email" name="employee_email"
                                        value="<?= set_value('employee_email', $employee->employee_email); ?>" placeholder="Enter email address">
                                    <?= form_error('employee_email', '<div class="form-error">', '</div>'); ?>
                                </div>

                                <!-- Phone -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="employee_phone">Phone <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="employee_phone" name="employee_phone"
                                        value="<?= set_value('employee_phone', $employee->employee_phone); ?>" placeholder="Enter phone number">
                                    <?= form_error('employee_phone', '<div class="form-error">', '</div>'); ?>
                                </div>

                                <!-- Salary -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="employee_salary">Salary <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="employee_salary" name="employee_salary"
                                        value="<?= set_value('employee_salary', $employee->employee_salary); ?>" placeholder="Enter salary amount">
                                    <?= form_error('employee_salary', '<div class="form-error">', '</div>'); ?>
                                </div>

                                <!-- Department -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="department_id">Department <span class="text-danger">*</span></label>
                                    <select class="form-select" id="department_id" name="department_id">
                                        <?php foreach ($departments as $department) { ?>
                                            <option value="<?= $department->department_id; ?>"
                                                <?= set_select('department_id', $department->department_id, ($department->department_id == $employee->department_id)); ?>>
                                                <?= $department->department_name; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <?= form_error('department_id', '<div class="form-error">', '</div>'); ?>
                                </div>

                                <!-- Status -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="Active" <?= set_select('status', 'Active', ($employee->status == 'Active')); ?>>Active</option>
                                        <option value="Inactive" <?= set_select('status', 'Inactive', ($employee->status == 'Inactive')); ?>>Inactive</option>
                                    </select>
                                    <?= form_error('status', '<div class="form-error">', '</div>'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Footer -->
                <div class="form-footer">
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
        document.getElementById('previewImage').style.display = 'block';
        document.getElementById('defaultIcon').style.display = 'none';
    }
});
</script>

<script>
(function() {
    'use strict';
    var overlay = document.getElementById('photoOverlay');
    var imageInput = document.getElementById('profile_image');
    if (overlay && imageInput) {
        overlay.addEventListener('click', function() { imageInput.click(); });
    }
})();
</script>

<script>
document.getElementById('employeeForm').addEventListener('submit', function(e) {
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
