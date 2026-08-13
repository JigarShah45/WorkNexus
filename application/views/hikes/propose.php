<?php $this->load->view('layouts/header'); ?>
<?php $this->load->view('layouts/navbar'); ?>

<div class="container py-4">
    <div class="page-header d-flex align-items-center gap-3 mb-4">
        <div class="page-header-icon">
            <i class="bi bi-graph-up-arrow"></i>
        </div>
        <div class="flex-grow-1">
            <h2 class="fw-bold mb-1"><?= $title ?></h2>
            <p class="text-muted mb-0">Submit a salary hike proposal for management review.</p>
        </div>
        <div class="page-header-actions">
            <a href="<?= site_url('hikes') ?>" class="btn btn-back">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="form-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge me-2 text-primary"></i>Employee Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Employee Name</label>
                            <p class="fw-semibold fs-5 mb-0"><?= htmlspecialchars($employee->employee_name) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Current Salary</label>
                            <p class="fw-semibold fs-5 mb-0 text-primary">₹<?= number_format($employee->employee_salary, 0) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-data me-2 text-info"></i>Performance Snapshot (This Year)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <div class="p-3 rounded bg-soft text-center">
                                <i class="bi bi-calendar-check text-success fs-4"></i>
                                <h5 class="mt-2 mb-0">
                                    <?php
                                    $attPct = ($attendance_stats->total_days ?? 0) > 0 ? round((($attendance_stats->present_days ?? 0) / $attendance_stats->total_days) * 100, 1) : 0;
                                    echo $attPct;
                                    ?>%
                                </h5>
                                <small class="text-muted">Attendance</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 rounded bg-soft text-center">
                                <i class="bi bi-clock text-warning fs-4"></i>
                                <h5 class="mt-2 mb-0"><?= number_format($attendance_stats->total_overtime ?? 0, 1) ?></h5>
                                <small class="text-muted">Overtime Hrs</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 rounded bg-soft text-center">
                                <i class="bi bi-calendar-minus text-info fs-4"></i>
                                <h5 class="mt-2 mb-0"><?= $leave_stats['paid'] ?? 0 ?></h5>
                                <small class="text-muted">Paid Leaves</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 rounded bg-soft text-center">
                                <i class="bi bi-calendar-x text-danger fs-4"></i>
                                <h5 class="mt-2 mb-0"><?= $leave_stats['unpaid'] ?? 0 ?></h5>
                                <small class="text-muted">Unpaid Leaves</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil-square me-2 text-success"></i>Hike Details
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('hikes/store_proposal') ?>" method="post" id="proposalForm">
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                        <input type="hidden" name="employee_id" value="<?= $employee->employee_id ?>">

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="hike_percentage" class="form-label fw-semibold">Hike Percentage (%)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control form-control-lg" id="hike_percentage" name="hike_percentage"
                                           min="0.1" max="100" step="0.01" value="0" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="form-text">Enter the percentage increase for this employee.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="effective_date" class="form-label fw-semibold">Effective Date (Optional)</label>
                                <input type="date" class="form-control form-control-lg" id="effective_date" name="effective_date"
                                       min="<?= date('Y-m-d') ?>">
                                <div class="form-text">Leave blank for immediate effect upon approval.</div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light border">
                                    <div class="card-body">
                                        <h6 class="text-muted mb-3">Salary Calculation Preview</h6>
                                        <div class="row g-3">
                                            <div class="col-md-4 text-center">
                                                <small class="text-muted d-block">Current Salary</small>
                                                <span class="fs-4 fw-semibold" id="displayCurrent">₹<?= number_format($employee->employee_salary, 0) ?></span>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <small class="text-muted d-block">Hike Amount</small>
                                                <span class="fs-4 fw-semibold text-success" id="displayHikeAmount">+₹0</span>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <small class="text-muted d-block">Proposed Salary</small>
                                                <span class="fs-4 fw-bold text-primary" id="displayProposed">₹<?= number_format($employee->employee_salary, 0) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="justification" class="form-label fw-semibold">Reason / Justification</label>
                            <textarea class="form-control" id="justification" name="justification" rows="4"
                                      placeholder="Provide a detailed justification for this salary hike (e.g., performance achievements, market adjustment, promotion, etc.)" required></textarea>
                            <div class="form-text">Minimum 10 characters. Be specific about the reasons for this proposal.</div>
                        </div>

                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <div>
                                <strong>Note:</strong> The employee's current salary will NOT change until this proposal is reviewed and approved.
                                Only one pending proposal is allowed per employee at a time.
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="<?= site_url('hikes') ?>" class="btn btn-back-action btn-lg">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send me-2"></i> Submit Proposal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const currentSalary = <?= $employee->employee_salary ?>;
    const hikeInput = document.getElementById('hike_percentage');
    const displayCurrent = document.getElementById('displayCurrent');
    const displayHikeAmount = document.getElementById('displayHikeAmount');
    const displayProposed = document.getElementById('displayProposed');

    function updateCalculation() {
        const pct = parseFloat(hikeInput.value) || 0;
        const hikeAmount = Math.round((currentSalary * pct / 100) * 100) / 100;
        const proposed = Math.round((currentSalary + hikeAmount) * 100) / 100;

        displayCurrent.textContent = '₹' + currentSalary.toLocaleString('en-IN');
        displayHikeAmount.textContent = '+₹' + hikeAmount.toLocaleString('en-IN', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        displayProposed.textContent = '₹' + proposed.toLocaleString('en-IN');
    }

    hikeInput.addEventListener('input', updateCalculation);
    updateCalculation();

    document.getElementById('proposalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        const pct = parseFloat(hikeInput.value) || 0;

        if (pct <= 0) {
            Swal.fire({ icon: 'warning', title: 'Invalid Percentage', text: 'Please enter a hike percentage greater than 0.' });
            return;
        }

        Swal.fire({
            title: 'Submit Salary Hike Proposal?',
            html: '<div class="text-start">'
                + '<p><strong>Employee:</strong> ' + '<?= htmlspecialchars($employee->employee_name) ?>' + '</p>'
                + '<p><strong>Current Salary:</strong> ₹' + currentSalary.toLocaleString('en-IN') + '</p>'
                + '<p><strong>Hike:</strong> ' + pct.toFixed(1) + '%</p>'
                + '<p><strong>Proposed Salary:</strong> ₹' + displayProposed.textContent.replace('₹', '') + '</p>'
                + '</div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Submit',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>

<?php $this->load->view('layouts/footer'); ?>
