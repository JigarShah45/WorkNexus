<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="container py-4">
    <?php $this->load->view('layouts/page_header', [
        'title' => $title,
        'subtitle' => 'Create a new user account.',
        'icon' => 'bi-person-plus-fill',
        'show_back' => true,
        'back_url' => site_url('user'),
        'back_label' => 'Back'
    ]); ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (validation_errors()): ?>
        <div class="alert alert-danger">
            <?= validation_errors() ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <?= form_open('user/store', ['id' => 'addUserForm']) ?>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" id="employee_id" class="form-select" required>
                            <option value="">-- Select Employee --</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp->employee_id ?>"
                                    <?= set_value('employee_id') == $emp->employee_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($emp->employee_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= form_error('employee_id', '<small class="text-danger">', '</small>') ?>
                    </div>
                    <div class="col-md-6">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="username" class="form-control"
                               value="<?= set_value('username') ?>" required>
                        <?= form_error('username', '<small class="text-danger">', '</small>') ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" required>
                        <?= form_error('password', '<small class="text-danger">', '</small>') ?>
                    </div>
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                        <?= form_error('confirm_password', '<small class="text-danger">', '</small>') ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="Employee" <?= set_value('role') === 'Employee' ? 'selected' : '' ?>>Employee</option>
                            <option value="HR" <?= set_value('role') === 'HR' ? 'selected' : '' ?>>HR</option>
                            <option value="Manager" <?= set_value('role') === 'Manager' ? 'selected' : '' ?>>Manager</option>
                            <option value="Admin" <?= set_value('role') === 'Admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <?= form_error('role', '<small class="text-danger">', '</small>') ?>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="1" <?= set_value('status', '1') == '1' ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= set_value('status') == '0' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <?= form_error('status', '<small class="text-danger">', '</small>') ?>
                    </div>
                </div>

                <div class="permissions-section">
                <h5 class="perm-section-title mb-3"><i class="bi bi-key me-2"></i>Feature Access Permissions</h5>

                <?php foreach ($permissions as $category => $items): ?>
                    <div class="perm-group">
                        <div class="perm-group-header">
                            <h6 class="mb-0 fw-semibold"><?= htmlspecialchars($category) ?></h6>
                        </div>
                        <div class="perm-group-body">
                            <div class="perm-grid">
                                <?php foreach ($items as $perm): ?>
                                    <div class="perm-item">
                                        <div class="form-check perm-check">
                                            <input type="hidden" name="permission_ids[]" value="<?= $perm->permission_id ?>">
                                            <input type="checkbox"
                                                   name="permissions_enabled[<?= $perm->permission_id ?>]"
                                                   value="1"
                                                   id="perm_<?= $perm->permission_id ?>"
                                                   class="form-check-input permission-checkbox"
                                                   data-permission-id="<?= $perm->permission_id ?>"
                                                   <?= isset($role_defaults[$perm->permission_id]) && $role_defaults[$perm->permission_id] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="perm_<?= $perm->permission_id ?>">
                                                <?= htmlspecialchars($perm->permission_name) ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="<?= site_url('user') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save User
                    </button>
                </div>

            <?= form_close() ?>
        </div>
    </div>
</div>

<script>
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    Swal.fire({
        title: 'Save User?',
        text: 'A new user account will be created.',
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
document.getElementById('role').addEventListener('change', function() {
    var roleId = this.value;
    var checkboxes = document.querySelectorAll('.permission-checkbox');

    checkboxes.forEach(function(cb) {
        cb.checked = false;
    });

    if (roleId) {
        fetch('<?= site_url("user/get_role_permissions") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'role=' + encodeURIComponent(roleId) + '&csrf_test_name=' + encodeURIComponent(document.querySelector('input[name="<?= $this->security->get_csrf_token_name() ?>"]') ? document.querySelector('input[name="<?= $this->security->get_csrf_token_name() ?>"]').value : '')
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.permissions) {
                data.permissions.forEach(function(permId) {
                    var cb = document.querySelector('.permission-checkbox[data-permission-id="' + permId + '"]');
                    if (cb) cb.checked = true;
                });
            }
        })
        .catch(function(err) {
            console.error('Error fetching permissions:', err);
        });
    }
});
</script>

<?php $this->load->view('layouts/footer'); ?>
