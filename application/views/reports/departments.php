<?php $this->load->view('layouts/header'); ?>
<?php $this->load->view('layouts/navbar'); ?>

<div class="container py-4 report-page">
    <?php $this->load->view('layouts/page_header', [
        'title' => 'Department Report',
        'subtitle' => 'Browse departments and maintain a current view of the company structure.',
        'icon' => 'bi-building-fill',
        'show_back' => true,
        'back_url' => site_url('reports'),
        'back_label' => 'Reports'
    ]); ?>

    <div class="report-table-card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">
                <i class="bi bi-table me-2"></i>
                Department Records
            </h5>
            <span class="badge bg-primary">Total Records: <?= count($departments); ?></span>
        </div>

        <div class="card-body">
            <table id="departmentReportTable" class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Department Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $row) { ?>
                        <tr>
                            <td><?= $row->department_id; ?></td>
                            <td><?= $row->department_name; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $this->load->view('layouts/footer'); ?>
