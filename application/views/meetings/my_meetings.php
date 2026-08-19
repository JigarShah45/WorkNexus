<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>
<link rel="stylesheet" href="<?= base_url('assets/css/meetings.css'); ?>">

<div class="container py-4 meetings-page">

    <?php if ($this->session->flashdata('error')) { ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <?php $this->load->view('layouts/page_header', [
        'title' => 'Meetings',
        'subtitle' => 'Manage all meetings.',
        'icon' => 'bi-calendar-event',
        'show_back' => true,
        'back_url' => site_url('dashboard'),
        'back_label' => 'Back',
        'primary_url' => site_url('meetings/add'),
        'primary_label' => '  Meeting',
        'primary_icon' => 'bi-plus-circle',
        'primary_class' => 'btn btn-primary'
    ]); ?>


    <!-- Stats Row -->
    <div class="meetings-stats">
        <div class="meetings-stat-card">
            <div class="meetings-stat-icon meetings-stat-icon--primary">
                <i class="bi bi-calendar-event"></i>
            </div>
            <div class="meetings-stat-content">
                <div class="meetings-stat-label">Total Assigned</div>
                <div class="meetings-stat-value"><?= count($upcoming) + count($completed); ?></div>
            </div>
        </div>
        <div class="meetings-stat-card">
            <div class="meetings-stat-icon meetings-stat-icon--success">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="meetings-stat-content">
                <div class="meetings-stat-label">Upcoming</div>
                <div class="meetings-stat-value"><?= count($upcoming); ?></div>
            </div>
        </div>
        <div class="meetings-stat-card">
            <div class="meetings-stat-icon meetings-stat-icon--neutral">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="meetings-stat-content">
                <div class="meetings-stat-label">Completed</div>
                <div class="meetings-stat-value"><?= count($completed); ?></div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs meetings-tabs mb-4" id="meetingTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab">
                <i class="bi bi-clock me-1"></i> Upcoming <span class="meetings-tab-count"><?= count($upcoming); ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                <i class="bi bi-check2-circle me-1"></i> Completed <span class="meetings-tab-count"><?= count($completed); ?></span>
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="meetingTabContent">

        <!-- Upcoming Meetings -->
        <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
            <?php if (empty($upcoming)) { ?>
                <div class="card meetings-empty">
                    <div class="card-body text-center py-5">
                        <div class="meetings-empty-icon"><i class="bi bi-calendar-x"></i></div>
                        <p class="meetings-empty-text">No upcoming meetings assigned to you.</p>
                    </div>
                </div>
            <?php } else { ?>
                <div class="meetings-grid">
                    <?php foreach ($upcoming as $meeting) { ?>
                        <div class="card dashboard-panel-card h-100 meeting-card">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex align-items-start gap-3 mb-3">
                                    <div class="meeting-date-badge">
                                        <span class="meeting-date-day"><?= date('d', strtotime($meeting->meeting_date)); ?></span>
                                        <span class="meeting-date-month"><?= date('M', strtotime($meeting->meeting_date)); ?></span>
                                    </div>
                                    <div class="flex-grow-1 min-width-0">
                                        <h6 class="meeting-card-title text-truncate"><?= htmlspecialchars($meeting->meeting_title); ?></h6>
                                        <div class="meeting-card-client">
                                            <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($meeting->client_name); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="meeting-info-rows">
                                    <div class="meeting-info-row">
                                        <i class="bi bi-clock"></i>
                                        <span><?= date('h:i A', strtotime($meeting->meeting_date)); ?></span>
                                    </div>
                                    <div class="meeting-info-row">
                                        <i class="bi bi-geo-alt"></i>
                                        <span><?= htmlspecialchars($meeting->meeting_location); ?></span>
                                    </div>
                                    <div class="meeting-info-row">
                                        <i class="bi bi-person-badge"></i>
                                        <span>Organized by <?= htmlspecialchars($meeting->created_by_name); ?></span>
                                    </div>
                                </div>
                                <div class="meeting-card-actions mt-auto">
                                    <a href="<?= site_url('meetings/employee_view/' . $meeting->meeting_id); ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye me-1"></i> View Details
                                    </a>
                                    <?php if (!empty($meeting->google_meet_link)) { ?>
                                    <a href="<?= htmlspecialchars($meeting->google_meet_link); ?>"
                                       class="btn btn-sm btn-success" target="_blank" rel="noopener">
                                        <i class="bi bi-camera-video-fill me-1"></i> Join Google Meet
                                    </a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

        <!-- Completed Meetings -->
        <div class="tab-pane fade" id="completed" role="tabpanel">
            <?php if (empty($completed)) { ?>
                <div class="card meetings-empty">
                    <div class="card-body text-center py-5">
                        <div class="meetings-empty-icon"><i class="bi bi-calendar-check"></i></div>
                        <p class="meetings-empty-text">No completed meetings yet.</p>
                    </div>
                </div>
            <?php } else { ?>
                <div class="meetings-grid">
                    <?php foreach ($completed as $meeting) { ?>
                        <div class="card dashboard-panel-card h-100 meeting-card meeting-card-completed">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex align-items-start gap-3 mb-3">
                                    <div class="meeting-date-badge meeting-date-badge-completed">
                                        <span class="meeting-date-day"><?= date('d', strtotime($meeting->meeting_date)); ?></span>
                                        <span class="meeting-date-month"><?= date('M', strtotime($meeting->meeting_date)); ?></span>
                                    </div>
                                    <div class="flex-grow-1 min-width-0">
                                        <h6 class="meeting-card-title text-truncate"><?= htmlspecialchars($meeting->meeting_title); ?></h6>
                                        <div class="meeting-card-client">
                                            <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($meeting->client_name); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="meeting-info-rows">
                                    <div class="meeting-info-row">
                                        <i class="bi bi-clock"></i>
                                        <span><?= date('h:i A', strtotime($meeting->meeting_date)); ?></span>
                                    </div>
                                    <div class="meeting-info-row">
                                        <i class="bi bi-geo-alt"></i>
                                        <span><?= htmlspecialchars($meeting->meeting_location); ?></span>
                                    </div>
                                </div>
                                <div class="meeting-card-actions mt-auto">
                                    <a href="<?= site_url('meetings/employee_view/' . $meeting->meeting_id); ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

    </div>

</div>

<?php $this->load->view('layouts/footer'); ?>