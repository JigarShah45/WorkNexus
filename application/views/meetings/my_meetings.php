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
        'title' => 'My Meetings',
        'subtitle' => 'Meetings you are assigned to.',
        'icon' => 'bi-calendar-check',
        'show_back' => true,
        'back_url' => site_url('dashboard'),
        'back_label' => 'Back'
    ]); ?>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card dashboard-panel-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0;">
                        <i class="bi bi-calendar-event text-primary fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Assigned</div>
                        <div class="fw-bold fs-4"><?= count($upcoming) + count($completed); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card dashboard-panel-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0;">
                        <i class="bi bi-clock-history text-success fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Upcoming</div>
                        <div class="fw-bold fs-4"><?= count($upcoming); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card dashboard-panel-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0;">
                        <i class="bi bi-check-circle text-secondary fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Completed</div>
                        <div class="fw-bold fs-4"><?= count($completed); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="meetingTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab">
                <i class="bi bi-clock me-1"></i> Upcoming (<?= count($upcoming); ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                <i class="bi bi-check2-circle me-1"></i> Completed (<?= count($completed); ?>)
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="meetingTabContent">

        <!-- Upcoming Meetings -->
        <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
            <?php if (empty($upcoming)) { ?>
                <div class="card dashboard-panel-card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">No upcoming meetings assigned to you.</p>
                    </div>
                </div>
            <?php } else { ?>
                <div class="row g-3">
                    <?php foreach ($upcoming as $meeting) { ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card dashboard-panel-card h-100 meeting-card">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-start gap-3 mb-3">
                                        <div class="meeting-date-badge">
                                            <span class="meeting-date-day"><?= date('d', strtotime($meeting->meeting_date)); ?></span>
                                            <span class="meeting-date-month"><?= date('M', strtotime($meeting->meeting_date)); ?></span>
                                        </div>
                                        <div class="flex-grow-1 min-width-0">
                                            <h6 class="fw-bold mb-1 text-truncate"><?= htmlspecialchars($meeting->meeting_title); ?></h6>
                                            <div class="text-muted small">
                                                <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($meeting->client_name); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="meeting-info-rows mb-3">
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
                                    <div class="mt-auto">
                                        <a href="<?= site_url('meetings/employee_view/' . $meeting->meeting_id); ?>" class="btn btn-sm btn-primary w-100">
                                            <i class="bi bi-eye me-1"></i> View Details
                                        </a>
                                    </div>
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
                <div class="card dashboard-panel-card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-calendar-check text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">No completed meetings yet.</p>
                    </div>
                </div>
            <?php } else { ?>
                <div class="row g-3">
                    <?php foreach ($completed as $meeting) { ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card dashboard-panel-card h-100 meeting-card meeting-card-completed">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-start gap-3 mb-3">
                                        <div class="meeting-date-badge meeting-date-badge-completed">
                                            <span class="meeting-date-day"><?= date('d', strtotime($meeting->meeting_date)); ?></span>
                                            <span class="meeting-date-month"><?= date('M', strtotime($meeting->meeting_date)); ?></span>
                                        </div>
                                        <div class="flex-grow-1 min-width-0">
                                            <h6 class="fw-bold mb-1 text-truncate"><?= htmlspecialchars($meeting->meeting_title); ?></h6>
                                            <div class="text-muted small">
                                                <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($meeting->client_name); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="meeting-info-rows mb-3">
                                        <div class="meeting-info-row">
                                            <i class="bi bi-clock"></i>
                                            <span><?= date('h:i A', strtotime($meeting->meeting_date)); ?></span>
                                        </div>
                                        <div class="meeting-info-row">
                                            <i class="bi bi-geo-alt"></i>
                                            <span><?= htmlspecialchars($meeting->meeting_location); ?></span>
                                        </div>
                                    </div>
                                    <div class="mt-auto">
                                        <a href="<?= site_url('meetings/employee_view/' . $meeting->meeting_id); ?>" class="btn btn-sm btn-outline-secondary w-100">
                                            <i class="bi bi-eye me-1"></i> View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

    </div>

</div>

<style>
/* Meeting Cards */
.meeting-card { border: 1px solid var(--border); transition: transform 0.15s, box-shadow 0.15s; }
.meeting-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.08); }
.meeting-card-completed { opacity: 0.75; }
.meeting-card-completed:hover { opacity: 1; }

/* Date Badge */
.meeting-date-badge {
    width: 52px; height: 56px; flex-shrink: 0;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    background: var(--primary); color: #fff; border-radius: 10px;
}
.meeting-date-badge-completed { background: var(--muted, #6c757d); }
.meeting-date-day { font-size: 1.25rem; font-weight: 700; line-height: 1.1; }
.meeting-date-month { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; opacity: .9; }

/* Info Rows */
.meeting-info-rows { display: flex; flex-direction: column; gap: 6px; }
.meeting-info-row { display: flex; align-items: center; gap: 8px; font-size: .85rem; color: var(--text-secondary, #6c757d); }
.meeting-info-row i { width: 16px; text-align: center; font-size: .8rem; }

/* Tabs override */
.nav-tabs .nav-link { border: none; color: var(--text-secondary, #6c757d); font-weight: 500; padding: 10px 18px; border-bottom: 2px solid transparent; }
.nav-tabs .nav-link.active { color: var(--primary); border-bottom-color: var(--primary); background: transparent; }
.nav-tabs { border-bottom: 1px solid var(--border); }

.min-width-0 { min-width: 0; }
</style>

<?php $this->load->view('layouts/footer'); ?>
