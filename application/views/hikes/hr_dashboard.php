<?php $this->load->view('layouts/header'); ?>
<?php $this->load->view('layouts/navbar'); ?>

<div class="container py-4">
    <div class="page-header d-flex align-items-center gap-3 mb-4">
        <div class="page-header-icon">
            <i class="bi bi-graph-up-arrow"></i>
        </div>
        <div class="flex-grow-1">
            <h2 class="fw-bold mb-1"><?= $title ?></h2>
            <p class="text-muted mb-0">Manage employee salary revisions, hike proposals and compensation history.</p>
        </div>
        <div class="page-header-actions d-flex gap-2">
            <a href="<?= site_url('hikes/pending') ?>" class="btn btn-outline-warning btn-sm">
                <i class="bi bi-hourglass-split me-1"></i> Pending
                <?php if ($hike_stats->pending > 0): ?>
                    <span class="badge bg-warning text-dark ms-1"><?= $hike_stats->pending ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= site_url('hikes/history') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-clock-history me-1"></i> History
            </a>
        </div>
    </div>

    <div class="salary-stats-row">
        <button class="salary-stat-card stat-card-pending" type="button" data-filter="pending" aria-label="Filter by pending hikes">
            <div class="stat-icon">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <span class="stat-number"><?= $hike_stats->pending ?></span>
            <span class="stat-label">Pending Hikes</span>
        </button>
        <button class="salary-stat-card stat-card-approved" type="button" data-filter="approved" aria-label="Filter by approved hikes this year">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <span class="stat-number"><?= $hike_stats->approved_this_year ?></span>
            <span class="stat-label">Approved This Year</span>
        </button>
        <button class="salary-stat-card stat-card-rejected" type="button" data-filter="rejected" aria-label="Filter by rejected hikes this year">
            <div class="stat-icon">
                <i class="bi bi-x-circle"></i>
            </div>
            <span class="stat-number"><?= $hike_stats->rejected_this_year ?></span>
            <span class="stat-label">Rejected This Year</span>
        </button>
        <div class="salary-stat-card stat-card-info">
            <div class="stat-icon">
                <i class="bi bi-percent"></i>
            </div>
            <span class="stat-number"><?= number_format($hike_stats->avg_approved_hike_pct ?? 0, 1) ?>%</span>
            <span class="stat-label">Avg Approved Hike</span>
        </div>
        <div class="salary-stat-card stat-card-primary">
            <div class="stat-icon">
                <i class="bi bi-currency-rupee"></i>
            </div>
            <span class="stat-number">₹<?= number_format($hike_stats->total_salary_increase ?? 0, 0) ?></span>
            <span class="stat-label">Total Salary Increase</span>
        </div>
    </div>

    <?php if (!empty($pending_hikes)): ?>
    <div class="form-card mb-4">
        <div class="card-header bg-warning-soft d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-exclamation-triangle text-warning me-2"></i>Pending Proposals - Action Required
            </h5>
            <a href="<?= site_url('hikes/pending') ?>" class="btn btn-sm btn-outline-warning">View All Pending</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Current Salary</th>
                            <th>Proposed Salary</th>
                            <th>Hike %</th>
                            <th>Proposed By</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($pending_hikes, 0, 5) as $hike): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($hike->employee_name) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($hike->department_name) ?></small>
                            </td>
                            <td>₹<?= number_format($hike->current_salary, 0) ?></td>
                            <td class="fw-semibold text-success">₹<?= number_format($hike->proposed_salary, 0) ?></td>
                            <td><span class="badge bg-info"><?= number_format($hike->hike_percentage, 1) ?>%</span></td>
                            <td><?= htmlspecialchars($hike->proposed_by_name ?? 'N/A') ?></td>
                            <td><?= date('d M Y', strtotime($hike->proposed_at)) ?></td>
                            <td class="text-center">
                                <button class="btn btn-success btn-sm btn-hike-action"
                                        data-id="<?= $hike->hike_id ?>" data-action="approve"
                                        data-employee="<?= htmlspecialchars($hike->employee_name) ?>"
                                        data-current="<?= number_format($hike->current_salary, 0) ?>"
                                        data-hike="<?= number_format($hike->hike_percentage, 1) ?>"
                                        data-new="<?= number_format($hike->proposed_salary, 0) ?>">
                                    <i class="bi bi-check-lg"></i> Approve
                                </button>
                                <button class="btn btn-danger btn-sm btn-hike-reject"
                                        data-id="<?= $hike->hike_id ?>" data-action="reject"
                                        data-employee="<?= htmlspecialchars($hike->employee_name) ?>">
                                    <i class="bi bi-x-lg"></i> Reject
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="form-card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">
                <i class="bi bi-people me-2 text-primary"></i>Employee Salary Overview
            </h5>
            <a href="<?= site_url('hikes/history') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-clock-history me-1"></i> Full History
            </a>
        </div>
        <div class="salary-filter-bar" id="salaryFilterBar" style="display: none;">
            <div class="salary-filter-info">
                <span class="salary-filter-badge" id="salaryFilterBadge"></span>
                <button type="button" class="btn btn-sm btn-outline-secondary salary-clear-filter" id="clearFilterBtn">
                    <i class="bi bi-x-circle me-1"></i> Clear Filter
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="hikesEmployeeTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Current Salary</th>
                            <th>Last Hike</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($employees)): ?>
                            <?php foreach ($employees as $index => $emp): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($emp->employee_name) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($emp->department_name ?? 'N/A') ?></td>
                                <td class="fw-semibold">₹<?= number_format($emp->employee_salary, 0) ?></td>
                                <td>
                                    <?php if ($emp->latest_hike): ?>
                                        <span class="text-success">+<?= number_format($emp->latest_hike->hike_percentage, 1) ?>%</span>
                                        <br><small class="text-muted"><?= date('M Y', strtotime($emp->latest_hike->approved_at)) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($emp->has_pending): ?>
                                        <span class="salary-badge badge-pending">PENDING</span>
                                    <?php else: ?>
                                        <span class="salary-badge badge-no-pending">NO PENDING</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($emp->has_pending): ?>
                                        <?php $pending = $this->Hike_model->getPendingHikeForEmployee($emp->employee_id); ?>
                                        <?php if ($pending): ?>
                                            <button class="btn btn-info btn-sm btn-review-pending"
                                                    data-id="<?= $pending->hike_id ?>"
                                                    data-employee="<?= htmlspecialchars($emp->employee_name) ?>">
                                                <i class="bi bi-eye me-1"></i> Review Proposal
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="<?= site_url('hikes/propose/' . $emp->employee_id) ?>" class="btn btn-primary btn-sm">
                                            <i class="bi bi-plus-circle me-1"></i> Propose Hike
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No employees found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

<?php $this->load->view('layouts/footer'); ?>

<script>
(function() {
    'use strict';

    const csrfName = '<?= $this->security->get_csrf_token_name() ?>';
    const csrfHash = '<?= $this->security->get_csrf_hash() ?>';

    const employeesData = <?= json_encode(array_map(function($emp) {
        $pendingHike = null;
        if ($emp->has_pending) {
            $pending = $this->Hike_model->getPendingHikeForEmployee($emp->employee_id);
            if ($pending) {
                $pendingHike = $pending->hike_id;
            }
        }
        return [
            'id' => $emp->employee_id,
            'name' => $emp->employee_name,
            'department' => $emp->department_name ?? 'N/A',
            'salary' => $emp->employee_salary,
            'has_pending' => $emp->has_pending,
            'pending_hike_id' => $pendingHike,
            'latest_hike' => $emp->latest_hike ? [
                'percentage' => $emp->latest_hike->hike_percentage,
                'date' => $emp->latest_hike->approved_at
            ] : null
        ];
    }, $employees)) ?>;

    const pendingHikesData = <?= json_encode(array_map(function($hike) {
        return [
            'id' => $hike->hike_id,
            'employee_name' => $hike->employee_name,
            'department' => $hike->department_name,
            'current_salary' => $hike->current_salary,
            'proposed_salary' => $hike->proposed_salary,
            'hike_percentage' => $hike->hike_percentage,
            'justification' => $hike->justification,
            'proposed_by' => $hike->proposed_by_name,
            'proposed_at' => $hike->proposed_at
        ];
    }, $pending_hikes)) ?>;

    const approvedHikesData = <?= json_encode(array_map(function($hike) {
        return [
            'id' => $hike->hike_id,
            'employee_name' => $hike->employee_name,
            'department' => $hike->department_name,
            'current_salary' => $hike->current_salary,
            'proposed_salary' => $hike->proposed_salary,
            'hike_percentage' => $hike->hike_percentage,
            'hike_amount' => $hike->hike_amount,
            'approved_by' => $hike->approved_by_name,
            'approved_at' => $hike->approved_at
        ];
    }, $approved_hikes)) ?>;

    const rejectedHikesData = <?= json_encode(array_map(function($hike) {
        return [
            'id' => $hike->hike_id,
            'employee_name' => $hike->employee_name,
            'department' => $hike->department_name,
            'current_salary' => $hike->current_salary,
            'proposed_salary' => $hike->proposed_salary,
            'hike_percentage' => $hike->hike_percentage,
            'justification' => $hike->justification,
            'rejection_reason' => $hike->rejection_reason,
            'rejected_by' => $hike->rejected_by_name,
            'rejected_at' => $hike->rejected_at
        ];
    }, $rejected_hikes)) ?>;

    let activeFilter = null;
    let dataTable = null;

    function formatCurrency(amount) {
        return '₹' + Number(amount).toLocaleString('en-IN');
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    }

    function formatMonthYear(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return months[d.getMonth()] + ' ' + d.getFullYear();
    }

    function buildTableConfig(filterType) {
        let columns = [];
        let data = [];

        if (filterType === 'pending') {
            columns = [
                { title: '#', className: 'text-center' },
                { title: 'Employee' },
                { title: 'Department' },
                { title: 'Current Salary' },
                { title: 'Hike %' },
                { title: 'Proposed Salary' },
                { title: 'Proposed By' },
                { title: 'Date' },
                { title: 'Status', className: 'text-center' },
                { title: 'Actions', className: 'text-center', orderable: false }
            ];
            data = pendingHikesData.map(function(h, i) {
                return [
                    i + 1,
                    '<strong>' + h.employee_name + '</strong>',
                    h.department || 'N/A',
                    formatCurrency(h.current_salary),
                    '<span class="salary-badge badge-hike-pct">+' + parseFloat(h.hike_percentage).toFixed(1) + '%</span>',
                    '<span class="fw-semibold text-success">' + formatCurrency(h.proposed_salary) + '</span>',
                    h.proposed_by || 'N/A',
                    formatDate(h.proposed_at),
                    '<span class="salary-badge badge-pending">PENDING</span>',
                    '<div class="salary-actions">' +
                        '<button class="btn btn-outline-info btn-sm btn-view-pending" data-id="' + h.id + '" data-employee="' + h.employee_name + '"><i class="bi bi-eye"></i></button> ' +
                        '<button class="btn btn-success btn-sm btn-quick-approve" data-id="' + h.id + '" data-employee="' + h.employee_name + '" data-current="' + formatCurrency(h.current_salary) + '" data-hike="' + parseFloat(h.hike_percentage).toFixed(1) + '" data-new="' + formatCurrency(h.proposed_salary) + '"><i class="bi bi-check-lg"></i></button> ' +
                        '<button class="btn btn-danger btn-sm btn-quick-reject" data-id="' + h.id + '" data-employee="' + h.employee_name + '"><i class="bi bi-x-lg"></i></button>' +
                    '</div>'
                ];
            });
        } else if (filterType === 'approved') {
            columns = [
                { title: '#', className: 'text-center' },
                { title: 'Employee' },
                { title: 'Department' },
                { title: 'Previous Salary' },
                { title: 'Hike %' },
                { title: 'New Salary' },
                { title: 'Approved By' },
                { title: 'Date' },
                { title: 'Status', className: 'text-center' },
                { title: 'Action', className: 'text-center', orderable: false }
            ];
            data = approvedHikesData.map(function(h, i) {
                return [
                    i + 1,
                    '<strong>' + h.employee_name + '</strong>',
                    h.department || 'N/A',
                    formatCurrency(h.current_salary),
                    '<span class="salary-badge badge-hike-pct">+' + parseFloat(h.hike_percentage).toFixed(1) + '%</span>',
                    '<span class="fw-semibold text-success">' + formatCurrency(h.proposed_salary) + '</span>',
                    h.approved_by || 'N/A',
                    formatDate(h.approved_at),
                    '<span class="salary-badge badge-approved">APPROVED</span>',
                    '<a href="<?= site_url('hikes/history') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-clock-history me-1"></i> View History</a>'
                ];
            });
        } else if (filterType === 'rejected') {
            columns = [
                { title: '#', className: 'text-center' },
                { title: 'Employee' },
                { title: 'Department' },
                { title: 'Proposed Salary' },
                { title: 'Hike %' },
                { title: 'Reason' },
                { title: 'Rejected By' },
                { title: 'Date' },
                { title: 'Status', className: 'text-center' },
                { title: 'Action', className: 'text-center', orderable: false }
            ];
            data = rejectedHikesData.map(function(h, i) {
                return [
                    i + 1,
                    '<strong>' + h.employee_name + '</strong>',
                    h.department || 'N/A',
                    formatCurrency(h.proposed_salary),
                    '<span class="salary-badge badge-hike-pct">+' + parseFloat(h.hike_percentage).toFixed(1) + '%</span>',
                    '<span class="text-muted" title="' + (h.rejection_reason || '') + '">' + (h.rejection_reason ? (h.rejection_reason.length > 30 ? h.rejection_reason.substring(0, 30) + '...' : h.rejection_reason) : 'N/A') + '</span>',
                    h.rejected_by || 'N/A',
                    formatDate(h.rejected_at),
                    '<span class="salary-badge badge-rejected">REJECTED</span>',
                    '<a href="<?= site_url('hikes/history') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-clock-history me-1"></i> View Details</a>'
                ];
            });
        } else {
            columns = [
                { title: '#', className: 'text-center' },
                { title: 'Employee' },
                { title: 'Department' },
                { title: 'Current Salary' },
                { title: 'Last Hike' },
                { title: 'Status', className: 'text-center' },
                { title: 'Action', className: 'text-center', orderable: false }
            ];
            data = employeesData.map(function(e, i) {
                let lastHike = '<span class="text-muted">-</span>';
                if (e.latest_hike) {
                    lastHike = '<span class="text-success">+' + parseFloat(e.latest_hike.percentage).toFixed(1) + '%</span>' +
                        '<br><small class="text-muted">' + formatMonthYear(e.latest_hike.date) + '</small>';
                }
                let statusBadge = e.has_pending
                    ? '<span class="salary-badge badge-pending">PENDING</span>'
                    : '<span class="salary-badge badge-no-pending">NO PENDING</span>';
                let actionBtn = e.has_pending
                    ? '<button class="btn btn-info btn-sm btn-review-pending" data-id="' + e.pending_hike_id + '" data-employee="' + e.name + '"><i class="bi bi-eye me-1"></i> Review Proposal</button>'
                    : '<a href="<?= site_url('hikes/propose/') ?>' + e.id + '" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i> Propose Hike</a>';
                return [
                    i + 1,
                    '<strong>' + e.name + '</strong>',
                    e.department || 'N/A',
                    formatCurrency(e.salary),
                    lastHike,
                    statusBadge,
                    actionBtn
                ];
            });
        }

        return { columns: columns, data: data };
    }

    function getEmptyStateMessage(filterType) {
        switch(filterType) {
            case 'pending': return 'No pending salary hike proposals.';
            case 'approved': return 'No approved salary hikes found for this period.';
            case 'rejected': return 'No rejected salary proposals found for this period.';
            default: return 'No employees found.';
        }
    }

    function getFilterLabel(filterType) {
        switch(filterType) {
            case 'pending': return 'Pending Hikes';
            case 'approved': return 'Approved This Year';
            case 'rejected': return 'Rejected This Year';
            default: return '';
        }
    }

    function initTable(filterType) {
        if (dataTable) {
            dataTable.destroy();
            dataTable = null;
        }

        const table = document.getElementById('hikesEmployeeTable');
        const theadRow = table.querySelector('thead tr');
        const config = buildTableConfig(filterType);

        theadRow.innerHTML = '';
        config.columns.forEach(function(col) {
            const th = document.createElement('th');
            th.textContent = col.title;
            if (col.className) th.className = col.className;
            theadRow.appendChild(th);
        });

        const tbody = table.querySelector('tbody');
        tbody.innerHTML = '';

        config.data.forEach(function(row) {
            const tr = document.createElement('tr');
            row.forEach(function(cell) {
                const td = document.createElement('td');
                td.innerHTML = cell;
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });

        if ($.fn.DataTable) {
            dataTable = $('#hikesEmployeeTable').DataTable({
                responsive: false,
                autoWidth: false,
                pageLength: 10,
                dom:
                    "<'dataTables-toolbar'<'dataTables-length'l><'dataTables-filter'f>>" +
                    "<'dataTables-table-wrapper'tr>" +
                    "<'dataTables-footer'<'dataTables-info'i><'dataTables-pagination'p>>",
                order: [[1, 'asc']],
                language: {
                    search: '',
                    searchPlaceholder: 'Search employees...',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ records',
                    emptyTable: getEmptyStateMessage(filterType),
                    paginate: {
                        previous: "<i class='bi bi-chevron-left'></i>",
                        next: "<i class='bi bi-chevron-right'></i>"
                    }
                },
                columnDefs: [
                    { targets: -1, orderable: false, searchable: false }
                ]
            });
        }
    }

    function applyFilter(filterType) {
        if (activeFilter === filterType) {
            activeFilter = null;
            updateCardVisuals();
            showFilterBar(false);
            initTable(null);
            updateUrl(null);
        } else {
            activeFilter = filterType;
            updateCardVisuals();
            showFilterBar(true, filterType);
            initTable(filterType);
            updateUrl(filterType);
        }
    }

    function updateCardVisuals() {
        document.querySelectorAll('.salary-stat-card[data-filter]').forEach(function(card) {
            const filter = card.getAttribute('data-filter');
            if (filter === activeFilter) {
                card.classList.add('stat-card-active');
                card.setAttribute('aria-pressed', 'true');
            } else {
                card.classList.remove('stat-card-active');
                card.setAttribute('aria-pressed', 'false');
            }
        });
    }

    function showFilterBar(show, filterType) {
        const bar = document.getElementById('salaryFilterBar');
        const badge = document.getElementById('salaryFilterBadge');
        if (show) {
            badge.textContent = getFilterLabel(filterType) + ' ×';
            bar.style.display = 'flex';
        } else {
            bar.style.display = 'none';
        }
    }

    function updateUrl(filterType) {
        const url = new URL(window.location.href);
        if (filterType) {
            url.searchParams.set('status', filterType);
        } else {
            url.searchParams.delete('status');
        }
        window.history.replaceState({}, '', url.toString());
    }

    function getUrlFilter() {
        const params = new URLSearchParams(window.location.search);
        return params.get('status');
    }

    document.querySelectorAll('.salary-stat-card[data-filter]').forEach(function(card) {
        card.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            applyFilter(filter);
        });

        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const filter = this.getAttribute('data-filter');
                applyFilter(filter);
            }
        });
    });

    document.getElementById('clearFilterBtn').addEventListener('click', function() {
        applyFilter(activeFilter);
    });

    $(document).on('click', '.btn-view-pending', function() {
        const hikeId = this.dataset.id;
        const employee = this.dataset.employee;
        const hike = pendingHikesData.find(h => h.id == hikeId);
        if (!hike) return;

        Swal.fire({
            title: 'Pending Proposal - ' + employee,
            html: '<div class="text-start">' +
                '<p><strong>Department:</strong> ' + (hike.department || 'N/A') + '</p>' +
                '<p><strong>Current Salary:</strong> ' + formatCurrency(hike.current_salary) + '</p>' +
                '<p><strong>Hike:</strong> +' + parseFloat(hike.hike_percentage).toFixed(1) + '%</p>' +
                '<p><strong>Proposed Salary:</strong> ' + formatCurrency(hike.proposed_salary) + '</p>' +
                '<p><strong>Justification:</strong> ' + (hike.justification || 'N/A') + '</p>' +
                '<p><strong>Proposed By:</strong> ' + (hike.proposed_by || 'N/A') + '</p>' +
                '</div>',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Approve',
            denyButtonText: 'Reject',
            cancelButtonText: 'Close',
            confirmButtonColor: '#16a34a',
            denyButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d'
        }).then(function(result) {
            if (result.isConfirmed) {
                submitReview(hikeId, 'approve', null);
            } else if (result.isDenied) {
                Swal.fire({
                    title: 'Reason for Rejection',
                    input: 'textarea',
                    inputPlaceholder: 'Enter reason...',
                    showCancelButton: true,
                    confirmButtonText: 'Reject',
                    confirmButtonColor: '#dc2626',
                    inputValidator: function(value) {
                        if (!value || value.trim().length < 5) {
                            return 'Please provide a reason.';
                        }
                    }
                }).then(function(rejectResult) {
                    if (rejectResult.isConfirmed) {
                        submitReview(hikeId, 'reject', rejectResult.value);
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-quick-approve', function() {
        const hikeId = this.dataset.id;
        const employee = this.dataset.employee;
        const current = this.dataset.current;
        const hike = this.dataset.hike;
        const newSalary = this.dataset.new;

        Swal.fire({
            title: 'Approve Salary Hike?',
            html: '<div class="text-start">' +
                '<p><strong>Employee:</strong> ' + employee + '</p>' +
                '<p><strong>Current Salary:</strong> ' + current + '</p>' +
                '<p><strong>Hike:</strong> ' + hike + '%</p>' +
                '<p><strong>New Salary:</strong> ' + newSalary + '</p>' +
                '</div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (result.isConfirmed) {
                submitReview(hikeId, 'approve', null);
            }
        });
    });

    $(document).on('click', '.btn-quick-reject', function() {
        const hikeId = this.dataset.id;
        const employee = this.dataset.employee;

        Swal.fire({
            title: 'Reject Salary Hike?',
            html: '<p>Employee: <strong>' + employee + '</strong></p><p>Please provide a reason for rejection:</p>',
            input: 'textarea',
            inputPlaceholder: 'Enter reason for rejection...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Reject',
            cancelButtonText: 'Cancel',
            inputValidator: function(value) {
                if (!value || value.trim().length < 5) {
                    return 'Please provide a meaningful reason (at least 5 characters).';
                }
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                submitReview(hikeId, 'reject', result.value);
            }
        });
    });

    document.querySelectorAll('.btn-hike-action').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const hikeId = this.dataset.id;
            const employee = this.dataset.employee;
            const current = this.dataset.current;
            const hike = this.dataset.hike;
            const newSalary = this.dataset.new;

            Swal.fire({
                title: 'Approve Salary Hike?',
                html: '<div class="text-start">'
                    + '<p><strong>Employee:</strong> ' + employee + '</p>'
                    + '<p><strong>Current Salary:</strong> ₹' + current + '</p>'
                    + '<p><strong>Hike:</strong> ' + hike + '%</p>'
                    + '<p><strong>New Salary:</strong> ₹' + newSalary + '</p>'
                    + '</div>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    submitReview(hikeId, 'approve', null);
                }
            });
        });
    });

    document.querySelectorAll('.btn-hike-reject').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const hikeId = this.dataset.id;
            const employee = this.dataset.employee;

            Swal.fire({
                title: 'Reject Salary Hike?',
                html: '<p>Employee: <strong>' + employee + '</strong></p>'
                    + '<p>Please provide a reason for rejection:</p>',
                input: 'textarea',
                inputPlaceholder: 'Enter reason for rejection...',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Reject',
                cancelButtonText: 'Cancel',
                inputValidator: function(value) {
                    if (!value || value.trim().length < 5) {
                        return 'Please provide a meaningful reason (at least 5 characters).';
                    }
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    submitReview(hikeId, 'reject', result.value);
                }
            });
        });
    });

    $(document).on('click', '.btn-review-pending', function() {
        const hikeId = this.dataset.id;
        const employee = this.dataset.employee;

        Swal.fire({
            title: 'Review Proposal - ' + employee,
            html: '<p>Choose an action for this pending proposal:</p>',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Approve',
            denyButtonText: 'Reject',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#16a34a',
            denyButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d'
        }).then(function(result) {
            if (result.isConfirmed) {
                fetch('<?= site_url("hikes/review/") ?>' + hikeId + '/approve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: csrfName + '=' + encodeURIComponent(csrfHash)
                })
                .then(function(resp) { return resp.json(); })
                .then(function(data) {
                    if (data.status) {
                        Swal.fire({ icon: 'success', title: 'Approved!', text: data.message, timer: 2000, showConfirmButton: false })
                            .then(function() { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                    }
                });
            } else if (result.isDenied) {
                Swal.fire({
                    title: 'Reason for Rejection',
                    input: 'textarea',
                    inputPlaceholder: 'Enter reason...',
                    showCancelButton: true,
                    confirmButtonText: 'Reject',
                    confirmButtonColor: '#dc2626',
                    inputValidator: function(value) {
                        if (!value || value.trim().length < 5) {
                            return 'Please provide a reason.';
                        }
                    }
                }).then(function(rejectResult) {
                    if (rejectResult.isConfirmed) {
                        const formData = csrfName + '=' + encodeURIComponent(csrfHash)
                            + '&rejection_reason=' + encodeURIComponent(rejectResult.value);
                        fetch('<?= site_url("hikes/review/") ?>' + hikeId + '/reject', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        })
                        .then(function(resp) { return resp.json(); })
                        .then(function(data) {
                            if (data.status) {
                                Swal.fire({ icon: 'success', title: 'Rejected', text: data.message, timer: 2000, showConfirmButton: false })
                                    .then(function() { location.reload(); });
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                            }
                        });
                    }
                });
            }
        });
    });

    function submitReview(hikeId, action, reason) {
        let formData = csrfName + '=' + encodeURIComponent(csrfHash);
        if (reason) {
            formData += '&rejection_reason=' + encodeURIComponent(reason);
        }

        fetch('<?= site_url("hikes/review/") ?>' + hikeId + '/' + action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(function(resp) { return resp.json(); })
        .then(function(data) {
            if (data.status) {
                Swal.fire({ icon: 'success', title: 'Done!', text: data.message, timer: 2000, showConfirmButton: false })
                    .then(function() { location.reload(); });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to process.' });
            }
        })
        .catch(function() {
            Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred. Please try again.' });
        });
    }

    const urlFilter = getUrlFilter();
    const hasUrlFilter = urlFilter && ['pending', 'approved', 'rejected'].includes(urlFilter);

    if (hasUrlFilter) {
        activeFilter = urlFilter;
        updateCardVisuals();
        showFilterBar(true, urlFilter);
        initTable(urlFilter);
    } else {
        if ($.fn.DataTable.isDataTable('#hikesEmployeeTable')) {
            $('#hikesEmployeeTable').DataTable().destroy();
        }

        if ($.fn.DataTable) {
            dataTable = $('#hikesEmployeeTable').DataTable({
                responsive: false,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [
                    [5,10,25,50,-1],
                    [5,10,25,50,"All"]
                ],
                dom:
                    "<'dataTables-toolbar'<'dataTables-length'l><'dataTables-filter'f>>" +
                    "<'dataTables-table-wrapper'tr>" +
                    "<'dataTables-footer'<'dataTables-info'i><'dataTables-pagination'p>>",

                order: [[1, 'asc']],
                language: {
                    search: '',
                    searchPlaceholder: 'Search employees...',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ records',
                    emptyTable: 'No employees found.',
                    paginate: {
                        previous: "<i class='bi bi-chevron-left'></i>",
                        next: "<i class='bi bi-chevron-right'></i>"
                    }
                },
                columnDefs: [
                    { targets: 0, orderable: true },
                    { targets: 6, orderable: false, searchable: false }
                ]
            });
        }
    }
})();
</script>
