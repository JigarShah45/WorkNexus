<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<?php $this->load->view('layouts/header'); ?>
<?php $this->load->view('layouts/navbar'); ?>

<link rel="stylesheet" href="<?= base_url('assets/css/profile.css'); ?>">

<div class="profile-page">

    <!-- Hero Header -->
    <div class="profile-hero anim">
        <div class="profile-hero-avatar anim d-1">
            <?php if (!empty($employee->profile_image)): ?>
                <img src="data:image/jpeg;base64,<?= base64_encode($employee->profile_image) ?>" alt="Profile photo of <?= htmlspecialchars($employee->employee_name) ?>">
            <?php else: ?>
                <i class="bi bi-person-fill"></i>
            <?php endif; ?>
        </div>
        <div class="profile-hero-info anim d-2">
            <h2><?= htmlspecialchars($employee->employee_name) ?></h2>
            <p>
                <?= htmlspecialchars($employee->department_name ?? '—') ?> &middot;
                #EMP<?= str_pad($employee->employee_id, 4, '0', STR_PAD_LEFT) ?>
            </p>
            <div class="profile-hero-meta">
                <span class="profile-hero-chip">
                    <span class="dot"></span>
                    <?= $employee->status ?>
                </span>
                <?php
                switch (strtolower($user->role)) {
                    case 'admin':   $role_label = 'Admin';   break;
                    case 'hr':      $role_label = 'HR';      break;
                    case 'manager': $role_label = 'Manager'; break;
                    default:        $role_label = 'Employee'; break;
                }
                ?>
                <span class="role-badge <?= strtolower($user->role) ?>">
                    <i class="bi bi-person-badge"></i> <?= $role_label ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Main Two-Column Layout -->
    <div class="profile-layout">

        <!-- LEFT COLUMN - Profile Photo -->
        <div class="profile-card profile-photo-card anim d-1">
            <div class="profile-card-header">
                <span class="profile-card-header-icon"><i class="bi bi-camera"></i></span>
                <h5>Profile Photo</h5>
            </div>
            <div class="profile-card-body">
                <form method="post" action="<?= site_url('profile/upload_photo') ?>" enctype="multipart/form-data" id="photoUploadForm">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <div class="profile-photo-wrap">
                        <?php if (!empty($employee->profile_image)): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($employee->profile_image) ?>" alt="Profile" class="profile-photo" id="profilePreview">
                        <?php else: ?>
                            <div class="profile-photo-placeholder" id="profilePreview">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        <?php endif; ?>
                        <label for="profileImageInput" class="profile-photo-edit" title="Change photo">
                            <i class="bi bi-pencil-fill"></i>
                        </label>
                        <input type="file" name="profile_image" id="profileImageInput" accept="image/jpeg,image/png,image/gif" style="display:none;">
                    </div>

                    <button type="submit" class="btn btn-primary btn-upload-submit w-100 mb-2">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Upload new photo
                    </button>
                    <p class="profile-photo-hint mb-0">JPG, PNG or GIF (max 2MB)</p>
                </form>

                <div class="profile-photo-divider"></div>

                <div class="profile-photo-name"><?= htmlspecialchars($employee->employee_name) ?></div>
                <div class="profile-photo-email"><?= htmlspecialchars($employee->employee_email) ?></div>

                <ul class="profile-quick-facts">
                    <li>
                        <i class="bi bi-building"></i>
                        <span><?= htmlspecialchars($employee->department_name ?? 'No department') ?></span>
                    </li>
                    <li>
                        <i class="bi bi-calendar-event"></i>
                        <span>Joined <?= date('d M Y', strtotime($employee->joining_date ?? $employee->created_at)) ?></span>
                    </li>
                    <li>
                        <i class="bi bi-telephone"></i>
                        <span><?= !empty($employee->employee_phone) ? htmlspecialchars($employee->employee_phone) : 'No phone added' ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- RIGHT COLUMN - Personal Information -->
        <div class="profile-card anim d-2">
            <div class="profile-card-header">
                <span class="profile-card-header-icon"><i class="bi bi-person-lines-fill"></i></span>
                <h5>Personal Information</h5>
            </div>
            <div class="profile-card-body">
                <form method="post" action="<?= site_url('profile/update') ?>">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <div class="profile-form-grid">
                        <div class="profile-form-group">
                            <label for="employee_name">Full Name *</label>
                            <div class="profile-input-wrap">
                                <span class="profile-input-icon"><i class="bi bi-person"></i></span>
                                <input type="text" name="employee_name" id="employee_name" class="form-control"
                                       value="<?= htmlspecialchars($employee->employee_name) ?>" required>
                            </div>
                        </div>
                        <div class="profile-form-group">
                            <label for="employee_email">Email Address *</label>
                            <div class="profile-input-wrap">
                                <span class="profile-input-icon"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="employee_email" id="employee_email" class="form-control"
                                       value="<?= htmlspecialchars($employee->employee_email) ?>" required>
                            </div>
                        </div>
                        <div class="profile-form-group">
                            <label for="employee_phone">Mobile Number</label>
                            <div class="profile-input-wrap">
                                <span class="profile-input-icon"><i class="bi bi-phone"></i></span>
                                <input type="text" name="employee_phone" id="employee_phone" class="form-control"
                                       value="<?= htmlspecialchars($employee->employee_phone) ?>">
                            </div>
                        </div>
                        <div class="profile-form-group">
                            <label>User ID / Employee ID</label>
                            <div class="profile-input-wrap">
                                <span class="profile-input-icon"><i class="bi bi-fingerprint"></i></span>
                                <input type="text" class="form-control" readonly
                                       value="<?= htmlspecialchars($user->username) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary profile-btn-save">
                            <i class="bi bi-check-circle me-1"></i> Update Profile
                        </button>
                    </div>
                </form>

                <!-- Security Section -->
                <div class="profile-security-divider" id="security">
                    <span><i class="bi bi-shield-lock"></i> SECURITY</span>
                </div>

                <form method="post" action="<?= site_url('profile/change_password') ?>">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <div class="profile-security-grid">
                        <div class="profile-form-group">
                            <label for="current_password">Current Password</label>
                            <div class="profile-input-wrap">
                                <span class="profile-input-icon"><i class="bi bi-lock"></i></span>
                                <input type="password" name="current_password" id="current_password" class="form-control"
                                       placeholder="Enter current password" autocomplete="current-password">
                            </div>
                        </div>
                        <div class="profile-form-group">
                            <label for="new_password">New Password</label>
                            <div class="profile-input-wrap">
                                <span class="profile-input-icon"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" name="new_password" id="new_password" class="form-control"
                                       placeholder="Enter new password" minlength="6" autocomplete="new-password">
                            </div>
                        </div>
                        <div class="profile-form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <div class="profile-input-wrap">
                                <span class="profile-input-icon"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                                       placeholder="Confirm new password" minlength="6" autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary profile-btn-save">
                            <i class="bi bi-shield-check me-1"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Employment & Account Information -->
    <div class="profile-bottom-grid">

        <!-- Employment Information -->
        <div class="profile-card anim d-1">
            <div class="profile-card-header">
                <span class="profile-card-header-icon success"><i class="bi bi-briefcase"></i></span>
                <h5>Employment Information</h5>
            </div>
            <div class="profile-card-body">
                <div class="profile-info-grid">
                    <div class="profile-info-item">
                        <div class="profile-info-icon bg-warning-soft">
                            <i class="bi bi-building text-warning"></i>
                        </div>
                        <div>
                            <span class="profile-info-label">Department</span>
                            <span class="profile-info-value"><?= htmlspecialchars($employee->department_name ?? '—') ?></span>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-icon bg-success-soft">
                            <i class="bi bi-currency-rupee text-success"></i>
                        </div>
                        <div>
                            <span class="profile-info-label">Salary</span>
                            <span class="profile-info-value">&#8377;<?= number_format($employee->employee_salary) ?></span>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-icon bg-info-soft">
                            <i class="bi bi-hash text-info"></i>
                        </div>
                        <div>
                            <span class="profile-info-label">Employee ID</span>
                            <span class="profile-info-value">#EMP<?= str_pad($employee->employee_id, 4, '0', STR_PAD_LEFT) ?></span>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-icon bg-primary-soft">
                            <i class="bi bi-calendar-event text-primary"></i>
                        </div>
                        <div>
                            <span class="profile-info-label">Joining Date</span>
                            <span class="profile-info-value"><?= date('d M Y', strtotime($employee->joining_date ?? $employee->created_at)) ?></span>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-icon bg-<?= $employee->status === 'Active' ? 'success' : 'danger' ?>-soft">
                            <i class="bi bi-<?= $employee->status === 'Active' ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' ?>"></i>
                        </div>
                        <div>
                            <span class="profile-info-label">Employment Status</span>
                            <span class="profile-status-badge <?= $employee->status === 'Active' ? 'active' : 'inactive' ?>">
                                <?= $employee->status ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="profile-card anim d-2">
            <div class="profile-card-header">
                <span class="profile-card-header-icon warning"><i class="bi bi-shield-lock"></i></span>
                <h5>Account Information</h5>
            </div>
            <div class="profile-card-body">
                <div class="profile-info-grid">
                    <div class="profile-info-item">
                        <div class="profile-info-icon bg-primary-soft">
                            <i class="bi bi-person-gear text-primary"></i>
                        </div>
                        <div>
                            <span class="profile-info-label">Username</span>
                            <span class="profile-info-value"><?= htmlspecialchars($user->username) ?></span>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-icon bg-warning-soft">
                            <i class="bi bi-person-badge text-warning"></i>
                        </div>
                        <div>
                            <span class="profile-info-label">Role</span>
                            <span class="profile-info-value"><?= htmlspecialchars($user->role) ?></span>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-icon bg-<?= $user->status === 'Active' ? 'success' : 'danger' ?>-soft">
                            <i class="bi bi-<?= $user->status === 'Active' ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' ?>"></i>
                        </div>
                        <div>
                            <span class="profile-info-label">Account Status</span>
                            <span class="profile-status-badge <?= $user->status === 'Active' ? 'active' : 'inactive' ?>">
                                <?= $user->status ?>
                            </span>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-icon bg-info-soft">
                            <i class="bi bi-clock text-info"></i>
                        </div>
                        <div>
                            <span class="profile-info-label">Last Login</span>
                            <span class="profile-info-value">
                                <?= $user->last_login ? date('d M Y, h:i A', strtotime($user->last_login)) : 'Never' ?>
                            </span>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-icon bg-primary-soft">
                            <i class="bi bi-calendar-check text-primary"></i>
                        </div>
                        <div>
                            <span class="profile-info-label">Account Created</span>
                            <span class="profile-info-value">
                                <?= $user->created_at ? date('d M Y', strtotime($user->created_at)) : 'N/A' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
$(document).on('change', '#profileImageInput', function(e) {
    var file = e.target.files[0];
    if (file) {
        var reader = new FileReader();
        reader.onload = function(event) {
            var preview = $('#profilePreview');
            if (preview.is('img')) {
                preview.attr('src', event.target.result);
            } else {
                preview.replaceWith('<img src="' + event.target.result + '" alt="Profile" class="profile-photo" id="profilePreview">');
            }
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php $this->load->view('layouts/footer'); ?>
