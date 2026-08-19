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

    <!-- Page Header -->
    <?php $this->load->view('layouts/page_header', [
        'title' => 'Meetings',
        'subtitle' => 'Manage all meetings.',
        'icon' => 'bi-calendar-event',
        'show_back' => true,
        'back_url' => site_url('dashboard'),
        'back_label' => 'Back',
        'primary_url' => site_url('meetings/add'),
        'primary_label' => 'Schedule Meeting',
        'primary_icon' => 'bi-plus-circle',
        'primary_class' => 'btn btn-primary'
    ]); ?>

    <!-- Meeting Table Card -->
    <div class="table-card">
        <div class="card-body p-0">
            <table id="meetingTable" class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Created By</th>
                        <th width="210">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($meetings as $row) { ?>
                        <tr>
                            <td>#MTG<?= str_pad($row->meeting_id, 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?= $row->client_name; ?></td>
                            <td><?= $row->meeting_title; ?></td>
                            <td><?= date('d M Y', strtotime($row->meeting_date)); ?></td>
                            <td><?= $row->meeting_location; ?></td>
                            <td><?= $row->created_by_name; ?></td>
                            <td class="employee-actions">
                                <?php if (!empty($row->google_meet_link)) { ?>
                                <a href="<?= htmlspecialchars($row->google_meet_link); ?>" class="btn btn-icon btn-success" title="Join Google Meet" target="_blank" rel="noopener">
                                    <i class="bi bi-camera-video-fill"></i>
                                </a>
                                <?php } ?>
                                <a href="<?= site_url('meetings/view/' . $row->meeting_id); ?>" class="btn btn-icon btn-view" title="View">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                <a href="<?= site_url('meetings/edit/' . $row->meeting_id); ?>" class="btn btn-icon btn-edit" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <button class="btn btn-icon btn-delete deleteMeeting"
                                    data-url="<?= site_url('meetings/delete/' . $row->meeting_id); ?>"
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

<input type="hidden" name="csrf_token" value="<?= $this->security->get_csrf_hash(); ?>">

<?php
$this->load->view('layouts/footer');
?>

<script>
$(document).ready(function () {
    initDataTable("#meetingTable", {
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        language: {
            searchPlaceholder: "Search meetings...",
            info: "Showing _START_ to _END_ of _TOTAL_ meetings",
        },
        order: [[0, "desc"]],
    });

    // Delete Meeting
    $(document).on("click", ".deleteMeeting", function () {
        let button = $(this);
        let deleteUrl = button.data("url");

        Swal.fire({
            title: "Are you sure?",
            text: "This meeting will be permanently deleted.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, Delete",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
                    type: "POST",
                    dataType: "json",
                    success: function (response) {
                        if (response.status) {
                            Swal.fire({
                                icon: "success", title: "Deleted!",
                                text: response.message, timer: 1200, showConfirmButton: false,
                            }).then(() => { location.reload(); });
                        } else {
                            Swal.fire({ icon: "error", title: "Error", text: response.message });
                        }
                    },
                    error: function () {
                        Swal.fire({ icon: "error", title: "Server Error", text: "Something went wrong." });
                    },
                });
            }
        });
    });
});
</script>
