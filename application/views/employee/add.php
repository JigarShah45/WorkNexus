<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="container py-4">

    <?php $this->load->view('layouts/page_header', [
        'title' => 'Add New Employee',
        'subtitle' => 'Create a new employee profile.',
        'icon' => 'bi-person-plus-fill',
        'show_back' => true,
        'back_url' => site_url('employee'),
        'back_label' => 'Back'
    ]); ?>

    <!-- Main Form Card -->
    <div class="add-employee-card">
        <form action="<?= site_url('employee/store'); ?>"
              method="post"
              enctype="multipart/form-data"
              id="employeeForm"
              novalidate>
            <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>

            <div class="card-body p-4">
                <div class="employee-form-layout">
                    <!-- LEFT: Profile Photo Section -->
                    <div class="photo-section">
                        <div class="photo-avatar-wrap">
                            <div id="previewContainer" class="photo-avatar">
                                <i id="defaultIcon" class="bi bi-person-fill" style="font-size:3rem;color:var(--primary-300);"></i>
                                <img id="previewImage" src="#" alt="Employee Photo" style="display:none;">
                            </div>
                            <div class="photo-overlay" id="photoOverlay">
                                <i class="bi bi-camera"></i>
                                <span>Change Photo</span>
                            </div>
                        </div>
                        <h6 class="photo-label">Employee Photo</h6>
                        <div class="upload-dropzone" id="uploadDropzone">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <span>Click or drag to upload</span>
                            <span class="upload-filename" id="uploadFilename"></span>
                        </div>
                        <input type="file" id="profile_image" name="profile_image" accept=".jpg,.jpeg,.png" hidden>
                        <label for="profile_image" class="btn btn-upload-photo">
                            <i class="bi bi-camera-fill me-1"></i> Choose Photo
                        </label>
                        <p class="photo-note">JPG, JPEG or PNG. Max 2 MB.</p>
                    </div>

                    <!-- RIGHT: Employee Details Form -->
                    <div class="form-section">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="employee_name">Employee Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="employee_name" name="employee_name"
                                    value="<?= set_value('employee_name'); ?>" placeholder="Enter employee name">
                                <?= form_error('employee_name', '<div class="form-error">', '</div>'); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="employee_email">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="employee_email" name="employee_email"
                                    value="<?= set_value('employee_email'); ?>" placeholder="Enter email address">
                                <?= form_error('employee_email', '<div class="form-error">', '</div>'); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="employee_phone">Phone <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="employee_phone" name="employee_phone"
                                    value="<?= set_value('employee_phone'); ?>" placeholder="Enter phone number">
                                <?= form_error('employee_phone', '<div class="form-error">', '</div>'); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="employee_salary">Salary <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="employee_salary" name="employee_salary"
                                    value="<?= set_value('employee_salary'); ?>" placeholder="Enter salary amount">
                                <?= form_error('employee_salary', '<div class="form-error">', '</div>'); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="department_id">Department <span class="text-danger">*</span></label>
                                <select class="form-select" id="department_id" name="department_id">
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $department) { ?>
                                        <option value="<?= $department->department_id; ?>" <?= set_select('department_id', $department->department_id); ?>>
                                            <?= $department->department_name; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <?= form_error('department_id', '<div class="form-error">', '</div>'); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Select Status</option>
                                    <option value="Active" <?= set_select('status', 'Active'); ?>>Active</option>
                                    <option value="Inactive" <?= set_select('status', 'Inactive'); ?>>Inactive</option>
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
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle-fill me-1"></i> Save Employee
                </button>
            </div>
        </form>
    </div>

</div>

<script>
document.getElementById('employeeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    Swal.fire({
        title: 'Save Employee?',
        text: 'A new employee profile will be created.',
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

<script>
(function() {
    'use strict';
    const imageInput = document.getElementById('profile_image');
    const preview = document.getElementById('previewImage');
    const icon = document.getElementById('defaultIcon');
    const dropzone = document.getElementById('uploadDropzone');
    const filename = document.getElementById('uploadFilename');
    const overlay = document.getElementById('photoOverlay');
    const avatarWrap = document.querySelector('.photo-avatar-wrap');

    function handleFile(file) {
        if (!file) return;
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!validTypes.includes(file.type)) { alert('Please select a valid image file (JPG, JPEG, or PNG).'); return; }
        if (file.size > 2 * 1024 * 1024) { alert('File size must not exceed 2 MB.'); return; }
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            icon.style.display = 'none';
            avatarWrap.classList.add('has-image');
        };
        reader.readAsDataURL(file);
        filename.textContent = file.name;
        filename.style.display = 'block';
        dropzone.classList.add('has-file');
    }

    imageInput.addEventListener('change', function() { handleFile(this.files[0]); });
    dropzone.addEventListener('click', function() { imageInput.click(); });
    dropzone.addEventListener('dragover', function(e) { e.preventDefault(); dropzone.classList.add('dragover'); });
    dropzone.addEventListener('dragleave', function(e) { e.preventDefault(); dropzone.classList.remove('dragover'); });
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(files[0]);
            imageInput.files = dataTransfer.files;
            handleFile(files[0]);
        }
    });
    overlay.addEventListener('click', function() { imageInput.click(); });
    avatarWrap.addEventListener('dragover', function(e) { e.preventDefault(); avatarWrap.classList.add('drag-over'); });
    avatarWrap.addEventListener('dragleave', function(e) { e.preventDefault(); avatarWrap.classList.remove('drag-over'); });
    avatarWrap.addEventListener('drop', function(e) {
        e.preventDefault();
        avatarWrap.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(files[0]);
            imageInput.files = dataTransfer.files;
            handleFile(files[0]);
        }
    });
})();
</script>

<?php
$this->load->view('layouts/footer');
?>
