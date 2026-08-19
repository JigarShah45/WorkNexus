<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>
<link rel="stylesheet" href="<?= base_url('assets/css/meetings.css'); ?>">

<div class="container py-4 meeting-details-page">

    <?php $this->load->view('layouts/page_header', [
        'title' => 'Meeting Details',
        'subtitle' => 'View meeting information and attendees.',
        'icon' => 'bi-calendar-event',
        'show_back' => true,
        'back_url' => site_url('meetings'),
        'back_label' => 'Back'
    ]); ?>

    <?php if (!empty($meeting->google_meet_link)) { ?>
    <div class="meetings-join-bar">
        <a href="<?= htmlspecialchars($meeting->google_meet_link); ?>"
           class="meetings-join-btn" target="_blank" rel="noopener">
            <i class="bi bi-camera-video-fill"></i> Join Google Meet
        </a>
    </div>
    <?php } ?>

    <div class="meetings-details-layout">

        <!-- Meeting Information -->
        <div class="card dashboard-panel-card">
            <div class="meetings-panel-header">
                <h5><i class="bi bi-info-circle"></i> Meeting Information</h5>
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
                        <div class="meeting-detail-label">Date &amp; Time</div>
                        <div class="meeting-detail-value">
                            <i class="bi bi-calendar-event"></i><?= date('d M Y', strtotime($meeting->meeting_date)); ?>
                            <br>
                            <i class="bi bi-clock"></i><?= date('h:i A', strtotime($meeting->meeting_date)); ?>
                        </div>
                    </div>
                    <div class="meeting-detail-item">
                        <div class="meeting-detail-label">Location</div>
                        <div class="meeting-detail-value">
                            <i class="bi bi-geo-alt"></i><?= htmlspecialchars($meeting->meeting_location); ?>
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
                            <i class="bi bi-person-badge"></i><?= htmlspecialchars($meeting->created_by_name); ?>
                        </div>
                    </div>
                    <div class="meeting-detail-item">
                        <div class="meeting-detail-label">Created At</div>
                        <div class="meeting-detail-value"><?= date('d M Y, h:i A', strtotime($meeting->created_at)); ?></div>
                    </div>
                    <?php if (!empty($meeting->updated_at)) { ?>
                    <div class="meeting-detail-item">
                        <div class="meeting-detail-label">Updated At</div>
                        <div class="meeting-detail-value"><?= date('d M Y, h:i A', strtotime($meeting->updated_at)); ?></div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- Attendees -->
        <div class="card dashboard-panel-card">
            <div class="meetings-panel-header">
                <h5><i class="bi bi-people-fill"></i> Attendees</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($meeting_employees)) { ?>
                    <div class="attendee-list">
                        <?php foreach ($meeting_employees as $emp) { ?>
                            <div class="attendee-item">
                                <div class="attendee-avatar"><?= strtoupper(substr($emp->employee_name, 0, 1)); ?></div>
                                <div class="attendee-name"><?= htmlspecialchars($emp->employee_name); ?></div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <p class="meetings-empty-text text-center py-3 mb-0">No attendees assigned.</p>
                <?php } ?>
            </div>
        </div>

    </div>

    <!-- Documents -->
    <?php if (!empty($meeting_files)) { ?>
    <div class="card dashboard-panel-card mt-4">
        <div class="meetings-panel-header">
            <h5><i class="bi bi-paperclip"></i> Documents</h5>
        </div>
        <div class="card-body">
            <div class="documents-grid">
                <?php foreach ($meeting_files as $file) { ?>
                    <?php
                        $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                        $iconMap = array(
                            'pdf'  => array('bi-file-earmark-pdf',  'document-icon--pdf'),
                            'ppt'  => array('bi-file-earmark-ppt',  'document-icon--ppt'),
                            'pptx' => array('bi-file-earmark-ppt',  'document-icon--ppt'),
                            'doc'  => array('bi-file-earmark-word', 'document-icon--word'),
                            'docx' => array('bi-file-earmark-word', 'document-icon--word'),
                            'txt'  => array('bi-file-earmark-text', 'document-icon--text')
                        );
                        $fileIcon = isset($iconMap[$ext]) ? $iconMap[$ext] : array('bi-file-earmark', 'document-icon--generic');
                    ?>
                    <div class="document-card">
                        <div class="document-icon <?= $fileIcon[1]; ?>">
                            <i class="bi <?= $fileIcon[0]; ?>"></i>
                        </div>
                        <div class="document-info">
                            <div class="document-name" title="<?= htmlspecialchars($file->file_name); ?>"><?= htmlspecialchars($file->file_name); ?></div>
                            <div class="document-meta"><?= strtoupper($ext); ?> &middot; <?= date('d M Y', strtotime($file->uploaded_at)); ?></div>
                        </div>
                        <a href="<?= site_url('meetings/download_file/' . $file->file_id); ?>"
                           class="btn btn-sm btn-outline-primary flex-shrink-0" title="Download">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php } ?>

</div>

<?php
$this->load->view('layouts/footer');
?>