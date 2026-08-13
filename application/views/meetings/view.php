<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="container py-4">

    <?php $this->load->view('layouts/page_header', [
        'title' => 'Meeting Details',
        'subtitle' => 'View meeting information and attendees.',
        'icon' => 'bi-calendar-event',
        'show_back' => true,
        'back_url' => site_url('meetings'),
        'back_label' => 'Back'
    ]); ?>

    <div class="profile-card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <table class="detail-table">
                        <tr>
                            <th>Meeting ID</th>
                            <td>#MTG<?= str_pad($meeting->meeting_id, 4, '0', STR_PAD_LEFT); ?></td>
                        </tr>
                        <tr>
                            <th>Client Name</th>
                            <td><?= $meeting->client_name; ?></td>
                        </tr>
                        <tr>
                            <th>Title</th>
                            <td><?= $meeting->meeting_title; ?></td>
                        </tr>
                        <tr>
                            <th>Date & Time</th>
                            <td><?= date('d M Y, h:i A', strtotime($meeting->meeting_date)); ?></td>
                        </tr>
                        <tr>
                            <th>Location</th>
                            <td><?= $meeting->meeting_location; ?></td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td><?= nl2br($meeting->description); ?></td>
                        </tr>
                        <tr>
                            <th>Created By</th>
                            <td><?= $meeting->created_by_name; ?></td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td><?= date('d M Y, h:i A', strtotime($meeting->created_at)); ?></td>
                        </tr>
                        <?php if (!empty($meeting->updated_at)) { ?>
                        <tr>
                            <th>Updated At</th>
                            <td><?= date('d M Y, h:i A', strtotime($meeting->updated_at)); ?></td>
                        </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>

            <!-- Involved Employees -->
            <?php if (!empty($meeting_employees)) { ?>
                <hr class="my-4">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-people-fill me-2"></i> Involved Employees
                </h5>
                <table class="detail-table-nested">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($meeting_employees as $emp) { ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= $emp->employee_name; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>

            <!-- Uploaded Files -->
            <?php if (!empty($meeting_files)) { ?>
                <hr class="my-4">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-paperclip me-2"></i> Uploaded Files
                </h5>
                <table class="detail-table-nested">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>File Name</th>
                            <th>Type</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($meeting_files as $file) { ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= $file->file_name; ?></td>
                                <td><span class="badge bg-primary"><?= strtoupper(pathinfo($file->file_name, PATHINFO_EXTENSION)); ?></span></td>
                                <td>
                                    <a href="<?= site_url('meetings/download_file/' . $file->file_id); ?>"
                                        class="btn btn-icon btn-view" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </div>

</div>

<?php
$this->load->view('layouts/footer');
?>
