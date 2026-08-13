<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="container py-4 user-logs-page">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-icon">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="flex-grow-1">
            <h2 class="fw-bold mb-1">User Login Logs</h2>
            <p class="text-muted mb-0">View user login and logout activity.</p>
        </div>
        <div class="page-header-actions">
            <a href="<?= site_url('dashboard'); ?>" class="btn btn-back">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="table-card">
        <div class="card-body">
            <!-- Filter Form -->
            <form method="GET" class="row g-3 filter-form">
                <div class="col-md-3">
                    <label class="form-label">User</label>
                    <select name="user_id" class="form-select">
                        <option value="">All Users</option>
                        <?php foreach ($users as $u) { ?>
                            <option value="<?= $u->user_id; ?>" <?= set_value('user_id', $user_id ?? '') == $u->user_id ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($u->username ?? ''); ?> (<?= htmlspecialchars($u->employee_name ?? ''); ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Action</label>
                    <select name="action" class="form-select">
                        <option value="">All Actions</option>
                        <option value="LOGIN" <?= set_value('action', $action ?? '') == 'LOGIN' ? 'selected' : ''; ?>>LOGIN</option>
                        <option value="LOGOUT" <?= set_value('action', $action ?? '') == 'LOGOUT' ? 'selected' : ''; ?>>LOGOUT</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="<?= set_value('date_from', $date_from ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="<?= set_value('date_to', $date_to ?? ''); ?>">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-search w-100"><i class="bi bi-search"></i></button>
                </div>
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
            </form>

            <!-- Table -->
            <div class="logs-table-wrapper">
                <table id="logsTable" class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>IP Address</th>
                            <th>User Agent</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_logs as $log) { ?>
                            <tr>
                                <td>
                                    <span class="log-id">#<?= $log->log_id; ?></span>
                                </td>
                                <td>
                                    <div class="log-user" title="<?= htmlspecialchars(($log->username ?? '') . ' - ' . ($log->employee_name ?? '')); ?>">
                                        <span class="log-user-name"><?= htmlspecialchars($log->username ?? ''); ?></span>
                                        <span class="log-user-employee"><?= htmlspecialchars($log->employee_name ?? ''); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($log->action == 'LOGIN') { ?>
                                        <span class="log-action-badge action-login">LOGIN</span>
                                    <?php } else { ?>
                                        <span class="log-action-badge action-logout">LOGOUT</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <span class="log-ip"><?= htmlspecialchars($log->ip_address ?? ''); ?></span>
                                </td>
                                <td>
                                    <span class="log-user-agent" title="<?= htmlspecialchars($log->user_agent ?? ''); ?>"><?= htmlspecialchars($log->user_agent ?? ''); ?></span>
                                </td>
                                <td data-order="<?= strtotime($log->timestamp); ?>">
                                    <span class="log-timestamp"><?= date('d M Y, h:i:s A', strtotime($log->timestamp)); ?></span>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
$(document).ready(function () {

    initDataTable('#logsTable', {
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'All']],
        order: [[5, 'desc']],
        columnDefs: [
            {
                targets: [2, 3],
                orderable: false
            }
        ],
        language: {
            search: '',
            searchPlaceholder: 'Search logs...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ logs',
            infoEmpty: 'No logs found',
            zeroRecords: 'No matching login logs found',
            paginate: {
                previous: "<i class='bi bi-chevron-left'></i>",
                next: "<i class='bi bi-chevron-right'></i>"
            }
        }
    });

});
</script>

<?php $this->load->view('layouts/footer'); ?>
