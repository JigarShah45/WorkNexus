<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="container py-4">

    <?php $this->load->view('layouts/page_header', [
        'title' => 'Employee Profile',
        'subtitle' => 'View employee details and information.',
        'icon' => 'bi-person-circle',
        'show_back' => true,
        'back_url' => site_url('employee'),
        'back_label' => 'Back'
    ]); ?>

    <!-- Profile Card -->
    <div class="profile-card">
        <div class="card-body">
            <div class="row">
                <!-- Profile Image -->
                <div class="col-md-3 text-center mb-4 mb-md-0">
                    <div class="d-inline-block">
                        <img src="<?= site_url('employee/image/' . $employee->employee_id); ?>"
                            class="profile-img-lg"
                            alt="Employee">
                    </div>
                </div>

                <!-- Employee Details -->
                <div class="col-md-9">
                    <table class="detail-table">
                        <tr>
                            <th>Employee Name</th>
                            <td><?= $employee->employee_name; ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?= $employee->employee_email; ?></td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td><?= $employee->employee_phone; ?></td>
                        </tr>
                        <tr>
                            <th>Department</th>
                            <td><?= $employee->department_name; ?></td>
                        </tr>
                        <tr>
                            <th>Salary</th>
                            <td>₹<?= number_format($employee->employee_salary); ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php if ($employee->status == 'Active') { ?>
                                    <span class="badge bg-success">Active</span>
                                <?php } else { ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td><?= $employee->created_at; ?></td>
                        </tr>
                        <tr>
                            <th>Updated At</th>
                            <td><?= !empty($employee->updated_at) ? $employee->updated_at : '-'; ?></td>
                        </tr>
                    </table>

                    <a href="<?= site_url('employee/edit/' . $employee->employee_id); ?>" class="btn btn-warning mt-3">
                        <i class="bi bi-pencil-square me-1"></i> Edit Employee
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
$this->load->view('layouts/footer');
?>
