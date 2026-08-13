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
        'back_url' => site_url('meetings/my_meetings'),
        'back_label' => 'My Meetings'
    ]); ?>

    <div class="row g-4">

        <!-- Meeting Info -->
        <div class="col-lg-8">
            <div class="card dashboard-panel-card">
                <div class="card-header dashboard-panel-header">
                    <h5>
                        <i class="bi bi-info-circle text-primary"></i>
                        Meeting Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="meeting-detail-grid">
                        <div class="meeting-detail-item">
                            <div class="meeting-detail-label">Meeting ID</div>
                            <div class="meeting-detail-value fw-bold">#MTG<?= str_pad($meeting->meeting_id, 4, '0', STR_PAD_LEFT); ?></div>
                        </div>
                        <div class="meeting-detail-item">
                            <div class="meeting-detail-label">Client</div>
                            <div class="meeting-detail-value"><?= htmlspecialchars($meeting->client_name); ?></div>
                        </div>
                        <div class="meeting-detail-item meeting-detail-full">
                            <div class="meeting-detail-label">Title</div>
                            <div class="meeting-detail-value fw-bold fs-5"><?= htmlspecialchars($meeting->meeting_title); ?></div>
                        </div>
                        <div class="meeting-detail-item">
                            <div class="meeting-detail-label">Date & Time</div>
                            <div class="meeting-detail-value">
                                <i class="bi bi-calendar-event text-primary me-1"></i>
                                <?= date('d M Y', strtotime($meeting->meeting_date)); ?>
                                <br>
                                <i class="bi bi-clock text-primary me-1"></i>
                                <?= date('h:i A', strtotime($meeting->meeting_date)); ?>
                            </div>
                        </div>
                        <div class="meeting-detail-item">
                            <div class="meeting-detail-label">Location</div>
                            <div class="meeting-detail-value">
                                <i class="bi bi-geo-alt text-primary me-1"></i>
                                <?= htmlspecialchars($meeting->meeting_location); ?>
                            </div>
                        </div>
                        <?php if (!empty($meeting->description)) { ?>
                        <div class="meeting-detail-item meeting-detail-full">
                            <div class="meeting-detail-label">Description</div>
                            <div class="meeting-detail-value"><?= nl2br(htmlspecialchars($meeting->description)); ?></div>
                        </div>
                        <?php } ?>
                        <div class="meeting-detail-item">
                            <div class="meeting-detail-label">Organized By</div>
                            <div class="meeting-detail-value">
                                <i class="bi bi-person-badge text-primary me-1"></i>
                                <?= htmlspecialchars($meeting->created_by_name); ?>
                            </div>
                        </div>
                        <div class="meeting-detail-item">
                            <div class="meeting-detail-label">Created At</div>
                            <div class="meeting-detail-value">
                                <?= date('d M Y, h:i A', strtotime($meeting->created_at)); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendees -->
        <div class="col-lg-4">
            <div class="card dashboard-panel-card h-100">
                <div class="card-header dashboard-panel-header">
                    <h5>
                        <i class="bi bi-people-fill text-primary"></i>
                        Attendees
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($meeting_employees)) { ?>
                        <div class="attendee-list">
                            <?php foreach ($meeting_employees as $emp) { ?>
                                <div class="attendee-item">
                                    <div class="attendee-avatar">
                                        <?= strtoupper(substr($emp->employee_name, 0, 1)); ?>
                                    </div>
                                    <div class="attendee-name"><?= htmlspecialchars($emp->employee_name); ?></div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <p class="text-muted text-center py-3 mb-0">No attendees assigned.</p>
                    <?php } ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Documents -->
    <?php if (!empty($meeting_files)) { ?>
    <div class="card dashboard-panel-card mt-4">
        <div class="card-header dashboard-panel-header">
            <h5>
                <i class="bi bi-paperclip text-primary"></i>
                Documents
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($meeting_files as $file) { ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="d-flex align-items-center gap-3 p-3 rounded border">
                            <div class="rounded d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex-shrink:0;
                                background: <?= $file->file_category === 'minutes' ? 'rgba(37,99,235,.1)' : 'rgba(124,58,237,.1)'; ?>;">
                                <i class="bi <?= $file->file_category === 'minutes' ? 'bi-file-earmark-text text-primary' : 'bi-easel text-purple'; ?> fs-5"></i>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-medium text-truncate small"><?= htmlspecialchars($file->file_name); ?></div>
                                <div class="text-muted" style="font-size:.75rem;">
                                    <?= strtoupper(pathinfo($file->file_name, PATHINFO_EXTENSION)); ?>
                                    &middot; <?= date('d M Y', strtotime($file->uploaded_at)); ?>
                                </div>
                            </div>
                            <a href="<?= site_url('meetings/download_file/' . $file->file_id); ?>"
                               class="btn btn-sm btn-outline-primary flex-shrink-0" title="Download">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php } ?>

</div>

<style>
.meeting-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
.meeting-detail-full { grid-column: 1 / -1; }
.meeting-detail-label { font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; color: var(--muted, #6c757d); margin-bottom: 4px; }
.meeting-detail-value { font-size: .95rem; line-height: 1.5; }

/* Attendee list */
.attendee-list { display: flex; flex-direction: column; gap: 10px; }
.attendee-item { display: flex; align-items: center; gap: 10px; }
.attendee-avatar {
    width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: var(--primary); color: #fff; font-weight: 600; font-size: .85rem;
}
.attendee-name { font-size: .9rem; }

.text-purple { color: #7c3aed; }

@media (max-width: 767.98px) {
    .meeting-detail-grid { grid-template-columns: 1fr; }
}

.min-width-0 { min-width: 0; }
</style>

<?php $this->load->view('layouts/footer'); ?>
