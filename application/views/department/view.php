<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="container py-4">

    <?php $this->load->view('layouts/page_header', [
        'title' => 'Department Details',
        'subtitle' => 'View department information and employees.',
        'icon' => 'bi-building',
        'show_back' => true,
        'back_url' => site_url('department'),
        'back_label' => 'Back'
    ]); ?>

    <div class="profile-card">
        <div class="card-body">
            <table class="detail-table">
                <tr>
                    <th>Department ID</th>
                    <td>#DEP<?= str_pad($department->department_id,4,'0',STR_PAD_LEFT); ?></td>
                </tr>
                <tr>
                    <th>Department Name</th>
                    <td><?= $department->department_name; ?></td>
                </tr>
                <tr>
                    <th>Total Employees</th>
                    <td><?= count($employees); ?></td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td><?= date('d M Y', strtotime($department->created_at)); ?></td>
                </tr>
                <tr>
                    <th>Updated At</th>
                    <td><?= !empty($department->updated_at) ? date('d M Y', strtotime($department->updated_at)) : '-'; ?></td>
                </tr>
            </table>

            <hr class="my-4">

            <h5 class="fw-bold mb-3">
                <i class="bi bi-people-fill me-2"></i>
                Employees in this Department
            </h5>

            <?php if(count($employees)>0){ ?>
                <table class="detail-table-nested">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; foreach($employees as $emp){ ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= $emp->employee_name; ?></td>
                                <td><?= $emp->employee_email; ?></td>
                                <td><?= $emp->employee_phone; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    No employees assigned to this department.
                </div>
            <?php } ?>

            <div class="mt-4">
                <a href="<?= site_url('department/edit/'.$department->department_id); ?>" class="btn btn-warning">
                    <i class="bi bi-pencil-square me-1"></i> Edit Department
                </a>
            </div>
        </div>
    </div>

</div>

<?php
$this->load->view('layouts/footer');
?>
