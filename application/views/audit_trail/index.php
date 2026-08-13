<?php
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<div class="container py-4 audit-trail-page">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-icon">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <div class="flex-grow-1">
            <h2 class="fw-bold mb-1">Audit Trail</h2>
            <p class="text-muted mb-0">View all system activity and changes.</p>
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
                                <?= $u->username; ?> (<?= $u->employee_name; ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Action</label>
                    <select name="action" class="form-select">
                        <option value="">All Actions</option>
                        <option value="CREATE" <?= set_value('action', $action ?? '') == 'CREATE' ? 'selected' : ''; ?>>CREATE</option>
                        <option value="UPDATE" <?= set_value('action', $action ?? '') == 'UPDATE' ? 'selected' : ''; ?>>UPDATE</option>
                        <option value="DELETE" <?= set_value('action', $action ?? '') == 'DELETE' ? 'selected' : ''; ?>>DELETE</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Table</label>
                    <select name="table_name" class="form-select">
                        <option value="">All Tables</option>
                        <option value="tbl_employee" <?= set_value('table_name', $table_name ?? '') == 'tbl_employee' ? 'selected' : ''; ?>>Employees</option>
                        <option value="tbl_department" <?= set_value('table_name', $table_name ?? '') == 'tbl_department' ? 'selected' : ''; ?>>Departments</option>
                        <option value="tbl_users" <?= set_value('table_name', $table_name ?? '') == 'tbl_users' ? 'selected' : ''; ?>>Users</option>
                        <option value="tbl_leave_requests" <?= set_value('table_name', $table_name ?? '') == 'tbl_leave_requests' ? 'selected' : ''; ?>>Leaves</option>
                        <option value="tbl_client_meetings" <?= set_value('table_name', $table_name ?? '') == 'tbl_client_meetings' ? 'selected' : ''; ?>>Meetings</option>
                        <option value="tbl_salary_hikes" <?= set_value('table_name', $table_name ?? '') == 'tbl_salary_hikes' ? 'selected' : ''; ?>>Hikes</option>
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
            <div class="audit-table-wrapper">
                <table id="auditTable" class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Table</th>
                            <th>Record ID</th>
                            <th>Old Values</th>
                            <th>New Values</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($audit_logs as $log) { ?>
                            <tr>
                                <td>
                                    <span class="audit-id">#<?= $log->audit_id; ?></span>
                                </td>
                                <td>
                                    <div class="audit-user" title="<?= htmlspecialchars(($log->username ?? '') . ' - ' . ($log->employee_name ?? '')); ?>">
                                        <span class="audit-user-name"><?= htmlspecialchars($log->username ?? ''); ?></span>
                                        <span class="audit-user-employee"><?= htmlspecialchars($log->employee_name ?? ''); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $actionClass = 'action-create';
                                    if ($log->action == 'UPDATE') $actionClass = 'action-update';
                                    elseif ($log->action == 'DELETE') $actionClass = 'action-delete';
                                    ?>
                                    <span class="audit-action-badge <?= $actionClass; ?>"><?= $log->action; ?></span>
                                </td>
                                <td>
                                    <span class="audit-table-badge" title="<?= htmlspecialchars($log->table_name ?? ''); ?>"><?= htmlspecialchars($log->table_name ?? ''); ?></span>
                                </td>
                                <td>
                                    <span class="audit-record-id"><?= $log->record_id ?: '-'; ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($log->old_values)): ?>
                                        <span class="audit-value-preview" title="<?= htmlspecialchars($log->old_values); ?>"><?= htmlspecialchars($log->old_values); ?></span>
                                    <?php else: ?>
                                        <span class="audit-value-preview value-empty">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($log->new_values)): ?>
                                        <span class="audit-value-preview" title="<?= htmlspecialchars($log->new_values); ?>"><?= htmlspecialchars($log->new_values); ?></span>
                                    <?php else: ?>
                                        <span class="audit-value-preview value-empty">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="audit-ip"><?= htmlspecialchars($log->ip_address ?? ''); ?></span>
                                </td>
                                <td>
                                    <span class="audit-timestamp"><?= date('d M Y, h:i:s A', strtotime($log->timestamp)); ?></span>
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
$(document).ready(function() {
    initDataTable('#auditTable', {
        responsive: true,
        pageLength: 25,
        order: [[8, 'desc']],
        language: {
            searchPlaceholder: "Search audit logs...",
        },
        columnDefs: [
            { orderable: true, targets: '_all' }
        ]
    });
});
</script>

<?php $this->load->view('layouts/footer'); ?>
