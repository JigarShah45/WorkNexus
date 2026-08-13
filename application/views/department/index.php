<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="container py-4">

    <?php if ($this->session->flashdata('error')) { ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <?php $this->load->view('layouts/page_header', [
        'title' => 'Department Management',
        'subtitle' => 'Manage all departments.',
        'icon' => 'bi-building',
        'show_back' => true,
        'back_url' => site_url('dashboard'),
        'back_label' => 'Back',
        'primary_url' => site_url('department/add'),
        'primary_label' => 'Add Department',
        'primary_icon' => 'bi-plus-circle',
        'primary_class' => 'btn btn-primary'
    ]); ?>

    <!-- Department Table Card -->
    <div class="table-card">
        <div class="card-body p-0">
            <table id="departmentTable" class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Department Name</th>
                        <th width="180">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $row) { ?>
                        <tr>
                            <td><strong>#DEP<?= str_pad($row->department_id, 4, '0', STR_PAD_LEFT); ?></strong></td>
                            <td><?= $row->department_name; ?></td>
                            <td class="employee-actions">
                                <a href="<?= site_url('department/view/' . $row->department_id); ?>" class="btn btn-icon btn-view" title="View">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                <a href="<?= site_url('department/edit/' . $row->department_id); ?>" class="btn btn-icon btn-edit" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <button class="btn btn-icon btn-delete deleteDepartment"
                                    data-url="<?= site_url('department/delete/' . $row->department_id); ?>"
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

<script>
$(document).ready(function() {
    initDataTable('#departmentTable', {
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        language: {
            searchPlaceholder: "Search departments...",
            info: "Showing _START_ to _END_ of _TOTAL_ departments",
        },
        order: [[0, "desc"]],
    });
});
</script>

<?php
$this->load->view('layouts/footer');
?>
