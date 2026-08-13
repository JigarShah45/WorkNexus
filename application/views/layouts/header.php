<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php
        $page_title = isset($title) ? trim($title) : '';
        if ($page_title !== '' && $page_title !== 'WorkNexus') {
            echo htmlspecialchars($page_title) . ' | WorkNexus';
        } else {
            echo 'WorkNexus';
        }
    ?></title>
    <meta name="csrf-token" content="<?= $this->security->get_csrf_hash(); ?>">
    <meta name="description" content="WorkNexus Employee Management System">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/images/logo-icon.svg'); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/images/logo-icon.svg'); ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/images/logo-icon.svg'); ?>">
    <meta name="theme-color" content="#2563eb">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

    <!-- Theme restore (prevent flash) -->
    <script>
        (function () {
            var saved = localStorage.getItem('employeeTheme') || 'light';
            document.documentElement.setAttribute('data-theme', saved);
        })();
    </script>

<!-- Core CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/variables.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/utilities.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/layout.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/datatable.css'); ?>">

    <!-- Page-specific CSS -->
    <?php
        $controller = strtolower($this->router->fetch_class());
        $pageCss = [
            'dashboard'      => 'assets/css/dashboard.css',
            'employee'       => 'assets/css/employees.css',
            'department'     => 'assets/css/departments.css',
            'reports'        => 'assets/css/reportnew.css',
            'login'          => 'assets/css/login.css',
            'hikes'          => 'assets/css/salary.css',
            'leave'          => 'assets/css/reports.css',
            'meetings'       => 'assets/css/reports.css',
            'attendance'     => 'assets/css/attendance.css',
            'user'           => 'assets/css/user.css',
            'userlogs'       => 'assets/css/userlog.css',
            'audittrail'     => 'assets/css/audit.css',
            'notifications'  => 'assets/css/reports.css',
            'profile'        => 'assets/css/profile.css',
        ];

        if (isset($pageCss[$controller])) {
            echo '<link rel="stylesheet" href="' . base_url($pageCss[$controller]) . '">';
        }
    ?>

    <!-- Theme Overrides (last for proper cascade) -->
    <link rel="stylesheet" href="<?= base_url('assets/css/theme.css'); ?>">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
</head>

<body class="light-theme">

<?php if (strtolower($this->router->fetch_class()) !== 'auth'): ?>
    <?php $this->load->view('layouts/sidebar'); ?>
<?php endif; ?>

<div class="main-content">
