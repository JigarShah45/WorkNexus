<?php $this->load->view('layouts/header'); ?>
<?php $this->load->view('layouts/navbar'); ?>

<div class="container py-4">

    <div class="page-header anim">
        <div class="page-header__content">
            <div class="page-header__icon">
                <i class="bi bi-gear"></i>
            </div>
            <div class="page-header__text">
                <h2>Settings</h2>
                <p>Manage your account and application preferences.</p>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- Profile -->
        <div class="col-md-6 col-lg-3 anim d-1">
            <div class="card settings-card h-100 text-center">
                <div class="card-body d-flex flex-column align-items-center">
                    <div class="card-icon-wrap bg-primary-soft">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h5>My Profile</h5>
                    <p>View and edit your profile details.</p>
                    <a href="<?= site_url('profile'); ?>" class="btn btn-primary mt-auto">
                        <i class="bi bi-arrow-right me-1"></i>
                        Open
                    </a>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="col-md-6 col-lg-3 anim d-2">
            <div class="card settings-card h-100 text-center">
                <div class="card-body d-flex flex-column align-items-center">
                    <div class="card-icon-wrap bg-warning-soft">
                        <i class="bi bi-key"></i>
                    </div>
                    <h5>Password</h5>
                    <p>Update your login password.</p>
                    <a href="<?= site_url('profile'); ?>#security" class="btn btn-warning mt-auto">
                        <i class="bi bi-arrow-right me-1"></i>
                        Open
                    </a>
                </div>
            </div>
        </div>

        <!-- System -->
        <div class="col-md-6 col-lg-3 anim d-3">
            <div class="card settings-card h-100 text-center">
                <div class="card-body d-flex flex-column align-items-center">
                    <div class="card-icon-wrap bg-success-soft">
                        <i class="bi bi-pc-display"></i>
                    </div>
                    <h5>System</h5>
                    <p>Application environment information.</p>
                    <button type="button" class="btn btn-success mt-auto" id="systemInfoBtn">
                        <i class="bi bi-info-circle me-1"></i>
                        View
                    </button>
                </div>
            </div>
        </div>

        <!-- About -->
        <div class="col-md-6 col-lg-3 anim d-4">
            <div class="card settings-card h-100 text-center">
                <div class="card-body d-flex flex-column align-items-center">
                    <div class="card-icon-wrap bg-danger-soft">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <h5>About</h5>
                    <p>WorkNexus version information.</p>
                    <button type="button" class="btn btn-danger mt-auto" id="aboutInfoBtn">
                        <i class="bi bi-arrow-right me-1"></i>
                        View
                    </button>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
$(function () {
    $('#systemInfoBtn').on('click', function () {
        WorkNexusAlert.confirm('System Information', [
            'PHP ' + <?= json_encode(PHP_VERSION); ?>,
            'CodeIgniter 3.1.13',
            'Database: MySQL'
        ].join('\n'), 'Close');
    });

    $('#aboutInfoBtn').on('click', function () {
        WorkNexusAlert.confirm('About WorkNexus', 'WorkNexus v2.0 — Employee Management System.\nPremium workforce platform.', 'Close');
    });
});
</script>

<?php $this->load->view('layouts/footer'); ?>
