<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="container py-4">

    <?php $this->load->view('layouts/page_header', [
        'title' => 'Manage Attendance',
        'subtitle' => 'Manage employee attendance entries.',
        'icon' => 'bi-calendar2-week',
        'show_back' => true,
        'back_url' => site_url('dashboard'),
        'back_label' => 'Dashboard'
    ]); ?>

    <!-- Filter Card -->
    <div class="attendance-filter-card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-funnel-fill me-2"></i>
                Filter Attendance
            </h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <div class="col-md-3">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select">
                        <option value="">All Months</option>
                        <?php
                        $months = [
                            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                        ];
                        foreach ($months as $num => $name) { ?>
                            <option value="<?= $num; ?>" <?= ($month == $num) ? 'selected' : ''; ?>>
                                <?= $name; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        <option value="">All Years</option>
                        <?php for ($y = 2024; $y <= 2026; $y++) { ?>
                            <option value="<?= $y; ?>" <?= ($year == $y) ? 'selected' : ''; ?>>
                                <?= $y; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept) { ?>
                            <option value="<?= $dept->department_id; ?>" <?= ($department_id == $dept->department_id) ? 'selected' : ''; ?>>
                                <?= $dept->department_name; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary me-2">
                        <i class="bi bi-funnel-fill me-1"></i> Apply
                    </button>
                    <a href="<?= site_url('attendance/manage'); ?>" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="attendance-table-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-table me-2"></i>
                Attendance Records
            </h5>
            <span class="badge bg-primary">Total Records: <?= count($attendance); ?></span>
        </div>
        <div class="card-body p-0">
            <table id="manageAttendanceTable"  class="table align-middle mb-0 attendance-grid">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Shift</th>
                        <th>Hours Worked</th>
                        <th>Overtime</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance as $row) { ?>
                        <tr>
                            <td><span class="fw-semibold"><?= $row->employee_name; ?></span></td>
                            <td><span class="badge bg-primary"><?= $row->department_name; ?></span></td>
                            <td><?= date('d M Y', strtotime($row->attendance_date)); ?></td>
                            <td><?= !empty($row->clock_in) ? date('h:i A', strtotime($row->clock_in)) : '-'; ?></td>
                            <td>
                                <?php if (!empty($row->clock_out)) {
                                    echo date('h:i A', strtotime($row->clock_out));
                                } else {
                                    $att_date = date('Y-m-d', strtotime($row->attendance_date));
                                    $today_date = date('Y-m-d');
                                    $now_time = date('H:i:s');
                                    if ($att_date === $today_date && $now_time < '19:00:00') {
                                        echo '-';
                                    } else {
                                        echo '7:00 PM';
                                    }
                                } ?>
                            </td>
                            <td><span class="badge bg-primary"><?= $row->shift_name; ?></span></td>
                            <td><?= isset($row->hours_display) ? $row->hours_display : '-'; ?></td>
                            <td><?= isset($row->overtime_display) ? $row->overtime_display : '-'; ?></td>
                            <td>
                                <?php
                                $status = isset($row->status) ? $row->status : '';
                                if ($status == 'Present') { ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Present</span>
                                <?php } elseif ($status == 'Absent') { ?>
                                    <span class="badge bg-danger"><i class="bi bi-x-circle-fill me-1"></i> Absent</span>
                                <?php } elseif ($status == 'Half-Day') { ?>
                                    <span class="badge bg-warning"><i class="bi bi-dash-circle-fill me-1"></i> Half-Day</span>
                                <?php } elseif ($status == 'Late') { ?>
                                    <span class="badge badge-late"><i class="bi bi-alarm-fill me-1"></i> Late</span>
                                <?php } else { ?>
                                    <span class="badge bg-secondary"><?= $status; ?></span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
$this->load->view('layouts/footer');
?>

<script>
$(document).ready(function() {
    initDataTable('#manageAttendanceTable', {
        order: [[2, 'desc']],
        pageLength: 10,
        language: {
            emptyTable: "No attendance records found",
            searchPlaceholder: "Search attendance...",
        }
    });
});
</script>
