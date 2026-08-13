<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="container py-4 employee-list-page">

    <?php $this->load->view('layouts/page_header', [
        'title' => 'Employee List',
        'subtitle' => 'Manage all employee records.',
        'icon' => 'bi-people-fill',
        'show_back' => true,
        'back_url' => site_url('dashboard'),
        'back_label' => 'Back',
        'primary_url' => site_url('employee/add'),
        'primary_label' => 'Add Employee',
        'primary_icon' => 'bi-plus-circle',
        'primary_class' => 'btn btn-primary'
    ]); ?>

    <div class="table-card">
        <div class="card-body p-0">
            <table id="employeeTable" class="table employee-table align-middle mb-0 w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Salary</th>
                        <th>Joined</th>
                        <th style="width: 170px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $row) { ?>
                        <tr>
                            <td class="employee-id">
                                #EMP<?= str_pad($row->employee_id, 4, '0', STR_PAD_LEFT); ?>
                            </td>
                            <td class="employee-name">
                                <?= $row->employee_name; ?>
                            </td>
                            <td class="employee-email">
                                <?= $row->employee_email; ?>
                            </td>
                            <td class="employee-phone">
                                <?= $row->employee_phone; ?>
                            </td>
                            <td>
                                <span class="badge department-badge">
                                    <?= $row->department_name; ?>
                                </span>
                            </td>
                            <td class="employee-salary">
                                ₹<?= number_format($row->employee_salary); ?>
                            </td>
                            <td class="joined-date">
                                <?= date('d M Y', strtotime($row->created_at)); ?>
                            </td>
                            <td class="employee-actions">
                                <a href="<?= site_url('employee/view/' . $row->employee_id); ?>"
                                   class="btn btn-icon btn-view"
                                   title="View">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                <a href="<?= site_url('employee/edit/' . $row->employee_id); ?>"
                                   class="btn btn-icon btn-edit"
                                   title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <button class="btn btn-icon btn-delete deleteEmployee"
                                        data-url="<?= site_url('employee/delete/' . $row->employee_id); ?>"
                                        title="Delete">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
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