<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="department-details-page">
    <div class="container py-4">

        <?php $this->load->view('layouts/page_header', [
            'title' => 'Department Details',
            'subtitle' => 'View department information and employees.',
            'icon' => 'bi-building',
            'show_back' => true,
            'back_url' => site_url('department'),
            'back_label' => 'Back',
            'primary_url' => site_url('department/edit/' . $department->department_id),
            'primary_label' => 'Edit Department',
            'primary_icon' => 'bi-pencil-square',
            'primary_class' => 'btn btn-warning'
        ]); ?>

        <?php $CI =& get_instance(); ?>
        <?php $totalEmployees = count($employees); ?>
        <!-- Department Summary -->
        <section class="department-summary-card">
            <div class="department-summary-head">
                <div class="department-summary-head-icon">
                    <i class="bi bi-building"></i>
                </div>
                <div class="department-summary-head-text">
                    <h3 class="department-summary-title"><?= htmlspecialchars($department->department_name); ?></h3>
                    <span class="department-summary-id">#DEP<?= str_pad($department->department_id, 4, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="department-count-stat">
                    <span class="department-count-number"><?= $totalEmployees; ?></span>
                    <span class="department-count-label">Total Employees</span>
                </div>
            </div>
            <div class="department-summary-grid">
                <div class="department-info-item">
                    <span class="department-info-label">Department ID</span>
                    <span class="department-info-value">#DEP<?= str_pad($department->department_id, 4, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="department-info-item">
                    <span class="department-info-label">Department Name</span>
                    <span class="department-info-value"><?= htmlspecialchars($department->department_name); ?></span>
                </div>
                <div class="department-info-item">
                    <span class="department-info-label">Created At</span>
                    <span class="department-info-value"><?= date('d M Y', strtotime($department->created_at)); ?></span>
                </div>
                <div class="department-info-item">
                    <span class="department-info-label">Updated At</span>
                    <span class="department-info-value"><?= !empty($department->updated_at) ? date('d M Y', strtotime($department->updated_at)) : '-'; ?></span>
                </div>
            </div>
        </section>

        <!-- Employees Section -->
        <section class="department-employees-section">
            <div class="department-employees-head">
                <h4 class="department-employees-title">
                    <i class="bi bi-people-fill"></i>
                    Employees in this Department
                    <span class="department-employees-count"><?= $totalEmployees; ?> <?= $totalEmployees == 1 ? 'employee' : 'employees'; ?></span>
                </h4>
            </div>

            <?php if ($totalEmployees > 0) { ?>
                <div class="table-card">
                    <div class="card-body p-0">
                        <table id="departmentEmployeeTable" class="table department-employee-table align-middle mb-0 w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($employees as $emp) { ?>
                                <tr>
                                    <td class="dept-emp-serial"><?= $i++; ?></td>
                                    <td class="dept-emp-name-cell">
                                        <div class="dept-emp-name-wrap">
                                            <?php if (!empty($emp->profile_image)) { ?>
                                                <span class="dept-emp-avatar">
                                                    <img src="<?= site_url('employee/image/' . $emp->employee_id); ?>" alt="<?= htmlspecialchars($emp->employee_name); ?>">
                                                </span>
                                            <?php } else {
                                                $nm = trim($emp->employee_name);
                                                $parts = explode(' ', $nm);
                                                $inits = '';
                                                foreach (array_slice($parts, 0, 2) as $p) {
                                                    if ($p !== '') { $inits .= strtoupper($p[0]); }
                                                }
                                                if ($inits === '') { $inits = '?'; }
                                            ?>
                                                <span class="dept-emp-avatar dept-emp-avatar-initials"><?= $inits; ?></span>
                                            <?php } ?>
                                            <span class="dept-emp-name"><?= htmlspecialchars($emp->employee_name); ?></span>
                                        </div>
                                    </td>
                                    <td class="dept-emp-email"><?= htmlspecialchars($emp->employee_email); ?></td>
                                    <td class="dept-emp-phone"><?= htmlspecialchars($emp->employee_phone); ?></td>
                                    <td class="dept-emp-actions">
                                        <?php if ($CI->hasPermission('view_employee_data')) { ?>
                                            <a href="<?= site_url('employee/view/' . $emp->employee_id); ?>"
                                               class="btn btn-icon btn-view"
                                               title="View Employee">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } else { ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    No employees assigned to this department.
                </div>
            <?php } ?>
        </section>

    </div>
</div>

<script>
$(document).ready(function() {
    initDataTable('#departmentEmployeeTable', {
        pageLength: 10,
        order: [],
        language: {
            searchPlaceholder: "Search employees...",
            info: "Showing _START_ to _END_ of _TOTAL_ employees"
        }
    });
});
</script>

<?php
$this->load->view('layouts/footer');
?>
