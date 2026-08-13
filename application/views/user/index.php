<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="container py-4">

    <?php $this->load->view('layouts/page_header', [
        'title' => 'Users',
        'subtitle' => 'Manage system users.',
        'icon' => 'bi-people-fill',
        'show_back' => true,
        'back_url' => site_url('dashboard'),
        'back_label' => 'Back',
        'primary_url' => site_url('user/add'),
        'primary_label' => 'Add User',
        'primary_icon' => 'bi-plus-circle',
        'primary_class' => 'btn btn-primary'
    ]); ?>

    <div class="table-card">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h4 class="mb-0">

                <i class="bi bi-people-fill me-2"></i>

                User Management

            </h4>
        </div>
        <div class="card-body p-0">

            <table id="userTable" class="table table-hover align-middle mb-0">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Username</th>

                        <th>Employee</th>

                        <th>Department</th>

                        <th>Role</th>

                        <th>Status</th>

                        <th>Last Login</th>

                        <th width="170">Action</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach($users as $row){ ?>

                        <tr>

                            <td class="fw-semibold">
                                #USR<?= str_pad($row->user_id,4,"0",STR_PAD_LEFT); ?>
                            </td>

                            <td><?= $row->username; ?></td>

                            <td><?= $row->employee_name; ?></td>

                            <td>
                                <span class="badge bg-primary">
                                    <?= $row->department_name; ?>
                                </span>
                            </td>

                            <td>

                                <?php

                                $class = 'bg-secondary';

                                if($row->role == 'Admin')
                                    $class = 'bg-danger';

                                elseif($row->role == 'HR')
                                    $class = 'bg-success';

                                elseif($row->role == 'Manager')
                                    $class = 'bg-warning text-dark';

                                ?>

                                <span class="badge <?= $class; ?>">
                                    <?= $row->role; ?>
                                </span>

                            </td>

                            <td>

                                <?php if($row->status == 'Active'){ ?>

                                    <span class="badge bg-success">
                                        Active
                                    </span>

                                <?php } else { ?>

                                    <span class="badge bg-danger">
                                        Inactive
                                    </span>

                                <?php } ?>

                            </td>

                            <td>
                                <?php if (!empty($row->last_login) && $row->last_login != '0000-00-00 00:00:00') { ?>

                                    <?= date('d M Y', strtotime($row->last_login)); ?>
                                    <br>
                                    <small class="text-muted">
                                        <?= date('h:i A', strtotime($row->last_login)); ?>
                                    </small>

                                <?php } else { ?>

                                    <span class="text-muted">Never</span>

                                <?php } ?>
                            </td>

                            <td class="employee-actions">

                                <a href="<?= site_url('user/edit/'.$row->user_id); ?>"
                                class="btn btn-icon btn-edit"
                                title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>

                                <button
                                    class="btn btn-icon btn-delete deleteUser"
                                    data-url="<?= site_url('user/delete/'.$row->user_id); ?>"
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

<script>
$(document).ready(function () {

    initDataTable('#userTable', {

        order: [[0,'desc']],    
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        

        language:{
            searchPlaceholder:"Search users..."
        }

    });

});
</script>