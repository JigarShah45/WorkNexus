<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | WorkNexus</title>
    <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/images/logo-icon.svg'); ?>">
    <meta name="theme-color" content="#4f46e5">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/variables.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/utilities.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/login.css'); ?>">
    <script>
        (function () {
            var saved = localStorage.getItem('employeeTheme') || 'light';
            document.documentElement.setAttribute('data-theme', saved);
        })();
    </script>
</head>
<body class="login-page">

    <!-- Theme Toggle -->
    <button class="login-theme-toggle" id="themeToggleLogin" type="button" title="Toggle theme" aria-label="Toggle theme">
        <i class="bi bi-moon-stars-fill" id="loginThemeIcon"></i>
    </button>

    <div class="login-container">

        <!-- Left Branding Section -->
        <div class="login-branding">
            <!-- Ambient depth layers -->
            <div class="login-brand-decoration" aria-hidden="true">
                <span class="login-blob login-blob-1"></span>
                <span class="login-blob login-blob-2"></span>
                <span class="login-shape login-shape-ring"></span>
                <span class="login-shape login-shape-tile"></span>
                <span class="login-shape login-shape-dot-1"></span>
                <span class="login-shape login-shape-dot-2"></span>
                <span class="login-grid"></span>
            </div>

            <div class="login-branding-content anim">
                <a class="login-brand-logo anim d-1" href="<?= site_url('auth'); ?>" tabindex="-1">
                    <img src="<?= base_url('assets/images/logo-icon.svg'); ?>" alt="" width="46" height="46">
                    <span class="login-brand-name">Work<span>Nexus</span></span>
                </a>

                <h1 class="login-headline anim d-2">
                    Your people.<br>One platform.
                </h1>

                <p class="login-description anim d-3">
                    The modern workspace for employee management — attendance,
                    leave, payroll growth and workforce insights, beautifully unified.
                </p>

                <div class="login-features anim d-4">
                    <div class="login-feature">
                        <div class="login-feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="login-feature-text">
                            <strong>Employee Management</strong>
                            <span>Centralized records &amp; org structure</span>
                        </div>
                    </div>
                    <div class="login-feature">
                        <div class="login-feature-icon">
                            <i class="bi bi-calendar2-check"></i>
                        </div>
                        <div class="login-feature-text">
                            <strong>Attendance &amp; Leave</strong>
                            <span>Real-time tracking &amp; approvals</span>
                        </div>
                    </div>
                    <div class="login-feature">
                        <div class="login-feature-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div class="login-feature-text">
                            <strong>Workforce Insights</strong>
                            <span>Analytics that drive decisions</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="login-brand-footer anim d-5">
                <span><i class="bi bi-shield-check"></i> Enterprise-grade security</span>
                <span><i class="bi bi-pulse"></i> 99.9% uptime</span>
            </div>
        </div>

        <!-- Right Login Section -->
        <div class="login-form-section">
            <div class="login-form-wrapper anim-scale">

                <!-- Mobile Logo -->
                <div class="login-mobile-logo">
                    <img src="<?= base_url('assets/images/logo-icon.svg'); ?>" alt="WorkNexus" width="38" height="38">
                    <span>Work<span class="brand-accent">Nexus</span></span>
                </div>

                <div class="login-form-header">
                    <h2>Welcome back</h2>
                    <p>Sign in to your account to continue.</p>
                </div>

                <?php if($this->session->flashdata('error')): ?>
                <div class="login-alert anim-down" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?= $this->session->flashdata('error'); ?></span>
                </div>
                <?php endif; ?>

                <form action="<?= site_url('auth/loginProcess'); ?>" method="post" class="login-form">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

                    <div class="login-field">
                        <label class="login-label" for="username">Username</label>
                        <div class="login-input-wrap">
                            <i class="bi bi-person"></i>
                            <input type="text"
                                   id="username"
                                   name="username"
                                   class="login-input"
                                   placeholder="Enter your username"
                                   autocomplete="username"
                                   autofocus
                                   required>
                        </div>
                    </div>

                    <div class="login-field">
                        <div class="login-label-row">
                            <label class="login-label" for="password">Password</label>
                        </div>
                        <div class="login-input-wrap">
                            <i class="bi bi-lock"></i>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="login-input"
                                   placeholder="Enter your password"
                                   autocomplete="current-password"
                                   required>
                            <button type="button" class="login-eye-btn" id="togglePassword" aria-label="Show password">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="login-submit" id="loginBtn">
                        <span class="login-submit-text">
                            Sign In
                            <i class="bi bi-arrow-right"></i>
                        </span>
                    </button>

                    <div class="login-hint">
                        <i class="bi bi-info-circle"></i>
                        Contact your administrator if you've lost access to your account.
                    </div>
                </form>

                <div class="login-footer-text">
                    <p>&copy; <?= date('Y') ?> WorkNexus. All rights reserved.</p>
                </div>

            </div>
        </div>

    </div>

    <script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        var password = document.getElementById('password');
        var icon = document.getElementById('eyeIcon');
        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
        this.setAttribute('aria-label', password.type === 'password' ? 'Show password' : 'Hide password');
    });

    document.getElementById('themeToggleLogin').addEventListener('click', function () {
        var html = document.documentElement;
        var current = html.getAttribute('data-theme');
        var next = current === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        localStorage.setItem('employeeTheme', next);
        var icon = document.getElementById('loginThemeIcon');
        icon.classList.remove('bi-moon-stars-fill', 'bi-sun-fill');
        icon.classList.add(next === 'dark' ? 'bi-sun-fill' : 'bi-moon-stars-fill');
    });

    (function () {
        var saved = localStorage.getItem('employeeTheme') || 'light';
        var icon = document.getElementById('loginThemeIcon');
        icon.classList.remove('bi-moon-stars-fill', 'bi-sun-fill');
        icon.classList.add(saved === 'dark' ? 'bi-sun-fill' : 'bi-moon-stars-fill');
    })();

    document.querySelector('.login-form').addEventListener('submit', function(e) {
        var btn = document.getElementById('loginBtn');
        btn.disabled = true;
        btn.classList.add('login-submit-loading');
        btn.querySelector('.login-submit-text').innerHTML = '<i class="bi bi-arrow-repeat spin"></i><span>Signing in...</span>';
    });
    </script>

    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
        var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
        (function() {
            var s1 = document.createElement("script"),
                s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = 'https://embed.tawk.to/6a8d7fb6f242a0344a3cd589/1k0sbloc4';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();
    </script>
    <!--End of Tawk.to Script-->

</body>
</html>
