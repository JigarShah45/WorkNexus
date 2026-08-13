<?php $this->load->view('layouts/header'); ?>
<?php $this->load->view('layouts/navbar'); ?>

<div class="container py-4">

    <div class="mb-4">

        <h2 class="fw-bold">

            <i class="bi bi-gear-fill me-2"></i>

            Settings

        </h2>

        <p class="text-muted">

            Manage your account and application settings.

        </p>

    </div>

    <div class="row g-4">

        <!-- Profile -->

        <div class="col-md-6 col-lg-3">

            <div class="card shadow-sm border-0 settings-card">

                <div class="card-body text-center">

                    <div class="card-icon-wrap bg-primary-soft">
                        <i class="bi bi-person-circle"></i>
                    </div>

                    <h4>My Profile</h4>

                    <p>View administrator profile.</p>

                    <a href="#" class="btn btn-primary">
                        <i class="bi bi-arrow-right-circle me-1"></i>
                        Open
                    </a>

                </div>

            </div>

        </div>

        <!-- Change Password -->

        <div class="col-md-6 col-lg-3">

            <div class="card shadow-sm border-0 settings-card">

                <div class="card-body text-center">

                    <div class="card-icon-wrap bg-warning-soft">
                        <i class="bi bi-key-fill"></i>
                    </div>

                    <h4>Password</h4>

                    <p>Change login password.</p>

                    <a href="#" class="btn btn-warning">
                        <i class="bi bi-arrow-right-circle me-1"></i>
                        Open
                    </a>

                </div>

            </div>

        </div>

        <!-- System -->

        <div class="col-md-6 col-lg-3">

            <div class="card shadow-sm border-0 settings-card">

                <div class="card-body text-center">

                    <div class="card-icon-wrap bg-success-soft">
                        <i class="bi bi-pc-display"></i>
                    </div>

                    <h4>System</h4>

                    <p>Application information.</p>

                    <a href="#" class="btn btn-success">
                        <i class="bi bi-arrow-right-circle me-1"></i>
                        Open
                    </a>

                </div>

            </div>

        </div>

        <!-- About -->

        <div class="col-md-6 col-lg-3">

            <div class="card shadow-sm border-0 settings-card">

                <div class="card-body text-center">

                    <div class="card-icon-wrap bg-danger-soft">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>

                    <h4>About</h4>

                    <p>HRMS Version Information.</p>

                    <a href="#" class="btn btn-danger">
                        <i class="bi bi-arrow-right-circle me-1"></i>
                        Open
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

<?php $this->load->view('layouts/footer'); ?>