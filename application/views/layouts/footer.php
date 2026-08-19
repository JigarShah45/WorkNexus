<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI =& get_instance();
?>
    </div><!-- end .main-content -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <!-- DataTables Buttons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Global JS -->
    <script>
        const base_url = "<?= base_url(); ?>";

        // CSRF token for AJAX requests
        $.ajaxSetup({
            data: {
                '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
            }
        });

        // ==========================================
        // WorkNexusAlert - Centralized SweetAlert2 Helper
        // ==========================================
        var WorkNexusAlert = {
            _getTheme: function() {
                return document.documentElement.getAttribute('data-theme') || 'light';
            },

            _baseOptions: function() {
                var isDark = this._getTheme() === 'dark';
                return {
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#6b7280',
                    customClass: {
                        popup: isDark ? 'swal2-dark' : '',
                        title: isDark ? 'swal2-dark' : '',
                        htmlContainer: isDark ? 'swal2-dark' : '',
                        confirmButton: 'swal2-confirm',
                        cancelButton: 'swal2-cancel'
                    }
                };
            },

            success: function(title, text) {
                var opts = this._baseOptions();
                return Swal.fire($.extend(opts, {
                    icon: 'success',
                    title: title || 'Success!',
                    text: text || '',
                    timer: 2500,
                    showConfirmButton: false
                }));
            },

            error: function(title, text) {
                var opts = this._baseOptions();
                return Swal.fire($.extend(opts, {
                    icon: 'error',
                    title: title || 'Error',
                    text: text || 'Something went wrong.'
                }));
            },

            warning: function(title, text) {
                var opts = this._baseOptions();
                return Swal.fire($.extend(opts, {
                    icon: 'warning',
                    title: title || 'Warning',
                    text: text || ''
                }));
            },

            confirm: function(title, text, confirmText) {
                var opts = this._baseOptions();
                return Swal.fire($.extend(opts, {
                    icon: 'question',
                    title: title || 'Are you sure?',
                    text: text || '',
                    showCancelButton: true,
                    confirmButtonText: confirmText || 'Confirm',
                    reverseButtons: true
                }));
            }
        };

        // ==========================================
        // Flashdata SweetAlert Handler (success via redirect)
        // ==========================================
        <?php
        $flash_success = $this->session->flashdata('success');
        $flash_error   = $this->session->flashdata('error');
        ?>

        <?php if ($flash_success): ?>
        (function() {
            var msg = <?= json_encode($flash_success); ?>;
            if (msg) {
                WorkNexusAlert.success('Success!', msg);
            }
        })();
        <?php endif; ?>

        <?php if ($flash_error): ?>
        (function() {
            var msg = <?= json_encode($flash_error); ?>;
            if (msg) {
                WorkNexusAlert.error('Error', msg);
            }
        })();
        <?php endif; ?>

        // ==========================================
        // Shared DataTable Initialization
        // ==========================================
        function initDataTable(tableId, options) {
            if ($.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable().destroy();
            }

            var defaults = {
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                order: [[0, 'desc']],
                dom:
                "<'dataTables-toolbar'<'dataTables-length'l><'dataTables-filter'f>>" +
                "<'dataTables-table-wrapper'tr>" +
                "<'dataTables-footer'<'dataTables-info'i><'dataTables-pagination'p>>",
                language: {
                    search: '',
                    searchPlaceholder: 'Search...',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ records',
                    paginate: {
                        previous: "<i class='bi bi-chevron-left'></i>",
                        next: "<i class='bi bi-chevron-right'></i>"
                    }
                },
                initComplete: function () {
                    var wrapper = $(this).closest('.dataTables_wrapper');
                    var searchInput = wrapper.find('.dataTables_filter input');
                    var lengthSelect = wrapper.find('.dataTables_length select');

                    searchInput.attr('placeholder', searchInput.attr('placeholder') || 'Search...');
                    searchInput.addClass('form-control');
                    lengthSelect.addClass('form-select');
                }
            };

            var config = $.extend(true, {}, defaults, options);
            return $(tableId).DataTable(config);
        }

        // ==========================================
        // Shared DataTable Config with Export Buttons
        // ==========================================
        function initReportDataTable(tableId, options) {
            var defaults = {
                responsive: true,
                pageLength: 10,
                autoWidth: true,
                dom:
                    "<'row mb-3 align-items-center'<'col-md-6'B><'col-md-6 text-end'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3 pt-3 border-top'<'dataTables_info_wrap'i><'dataTables_paginate_wrap'p>>",
                language: {
                    search: "",
                    searchPlaceholder: "Search...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ records",
                    paginate: {
                        previous: "<i class='bi bi-chevron-left'></i>",
                        next: "<i class='bi bi-chevron-right'></i>"
                    }
                },
                buttons: [
                    {
                        extend: "copy",
                        className: "btn btn-sm btn-secondary",
                        text: '<i class="bi bi-clipboard"></i> Copy',
                    },
                    {
                        extend: "excel",
                        className: "btn btn-sm btn-success",
                        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                    },
                    {
                        extend: "pdf",
                        className: "btn btn-sm btn-danger",
                        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                    },
                    {
                        extend: "print",
                        className: "btn btn-sm btn-primary",
                        text: '<i class="bi bi-printer"></i> Print',
                    },
                ],
                initComplete: function () {
                    var searchInput = $(this).closest('.dataTables_wrapper').find('.dataTables_filter input');
                    searchInput.attr('placeholder', searchInput.attr('placeholder') || 'Search...');
                    
                    var lengthSelect = $(this).closest('.dataTables_wrapper').find('.dataTables_length select');
                    lengthSelect.addClass('form-select-sm');
                }
            };

            var config = $.extend(true, {}, defaults, options);
            return $(tableId).DataTable(config);
        }

        // ==========================================
        // DataTable Redraw After Sidebar Transition
        // ==========================================
        document.addEventListener('transitionend', function (e) {
            if (e.target && e.target.classList && e.target.classList.contains('main-content')) {
                if (typeof $ !== 'undefined' && $.fn.DataTable) {
                    $('table.dataTable').each(function () {
                        var dt = $(this).DataTable();
                        if (dt) dt.columns.adjust().draw();
                    });
                }
            }
        });

        // ==========================================
        // Mobile Menu
        // ==========================================
        $(document).on('click', '#mobileMenuBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('mobile-open');
            if (overlay) overlay.classList.toggle('active');
        });

        $(document).on('click', '#sidebarOverlay', function(e) {
            e.preventDefault();
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
        });

        // Close sidebar when nav link clicked on tablet/mobile
        $(document).on('click', '.sidebar-nav .nav-link', function(e) {
            if (window.innerWidth < 1200) {
                var sidebar = document.getElementById('sidebar');
                var overlay = document.getElementById('sidebarOverlay');
                sidebar.classList.remove('mobile-open');
                if (overlay) overlay.classList.remove('active');
            }
        });

        // ==========================================
        // Theme Toggle
        // ==========================================
        var savedTheme = localStorage.getItem('employeeTheme') || 'light';
        applyTheme(savedTheme);

        $('#themeToggleButton').click(function() {
            var newTheme = $('html').attr('data-theme') === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
            localStorage.setItem('employeeTheme', newTheme);
        });

        function applyTheme(theme) {
            $('html').attr('data-theme', theme);
            if (theme === 'dark') {
                $('#themeIcon').removeClass().addClass('bi bi-sun-fill');
                $('#themeLabel').text('Light');
            } else {
                $('#themeIcon').removeClass().addClass('bi bi-moon-stars-fill');
                $('#themeLabel').text('Dark');
            }
        }

        // ==========================================
        // Global Page Navigation Search
        // ==========================================
        var searchItems = [
            { title: 'Dashboard', desc: 'Overview & statistics', icon: 'bi-speedometer2', url: 'dashboard', permission: null },
            { title: 'Employees', desc: 'Manage employees', icon: 'bi-people', url: 'employee', permission: 'access_employee' },
            { title: 'Departments', desc: 'Manage departments', icon: 'bi-building', url: 'department', permission: 'access_department' },
            { title: 'User Management', desc: 'Manage user accounts', icon: 'bi-person-gear', url: 'user', permission: 'access_user_management' },
            { title: 'Reports', desc: 'View reports & analytics', icon: 'bi-file-earmark-bar-graph', url: 'reports', permission: 'access_reports' },
            { title: '<?= $CI->isAdminOrHR() ? "Manage Attendance" : "My Attendance" ?>', desc: '<?= $CI->isAdminOrHR() ? "Manage all employee attendance" : "View your attendance" ?>', icon: 'bi-clock-history', url: '<?= $CI->isAdminOrHR() ? "attendance/manage" : "attendance" ?>', permission: 'access_attendance' },
            { title: 'Leave Management', desc: 'Manage leaves', icon: 'bi-calendar-x', url: 'leave', permission: 'access_leave' },
            { title: 'Client Meetings', desc: 'Manage meetings', icon: 'bi-easel', url: 'meetings', permission: 'access_meetings' },
            { title: 'Salary & Compensation', desc: 'Manage salary hikes & compensation', icon: 'bi-graph-up-arrow', url: 'hikes', permission: 'access_hike_management' },
            { title: 'Audit Trail', desc: 'View audit logs', icon: 'bi-shield-lock', url: 'audittrail', permission: 'access_audit_trail' },
            { title: 'User Logs', desc: 'View user activity logs', icon: 'bi-clock-history', url: 'userlogs', permission: 'access_user_logs' },
            { title: 'Notifications', desc: 'View notifications', icon: 'bi-bell', url: 'notifications', permission: null },
            { title: 'My Profile', desc: 'View & edit profile', icon: 'bi-person', url: 'profile', permission: null }
        ];

        var currentSearchIndex = -1;
        var $searchInput = $('#globalNavSearch');
        var $searchDropdown = $('#searchDropdown');

        function getFilteredItems(query) {
            query = (query || '').toLowerCase();
            return searchItems.filter(function(item) {
                if (item.permission && typeof hasPermission === 'function' && !hasPermission(item.permission)) {
                    return false;
                }
                if (!query) return true;
                return item.title.toLowerCase().indexOf(query) > -1 ||
                       item.desc.toLowerCase().indexOf(query) > -1;
            });
        }

        function renderSearchDropdown(query) {
            var filtered = getFilteredItems(query);
            currentSearchIndex = -1;

            if (!query && filtered.length === 0) {
                $searchDropdown.removeClass('active').empty();
                return;
            }

            if (filtered.length === 0) {
                $searchDropdown.html('<div class="search-dropdown-empty">No matching pages found</div>');
                $searchDropdown.addClass('active');
                return;
            }

            var html = '';
            for (var i = 0; i < filtered.length; i++) {
                html += '<a class="search-dropdown-item" href="' + base_url + filtered[i].url + '" data-index="' + i + '">' +
                    '<i class="bi ' + filtered[i].icon + '"></i>' +
                    '<div class="search-dropdown-item-text">' +
                        '<span class="search-dropdown-item-title">' + filtered[i].title + '</span>' +
                        '<span class="search-dropdown-item-desc">' + filtered[i].desc + '</span>' +
                    '</div>' +
                '</a>';
            }
            $searchDropdown.html(html).addClass('active');
        }

        function navigateSearch(direction) {
            var $items = $searchDropdown.find('.search-dropdown-item');
            if (!$items.length) return;

            $items.removeClass('active');
            if (direction === 'down') {
                currentSearchIndex = (currentSearchIndex + 1) % $items.length;
            } else {
                currentSearchIndex = currentSearchIndex <= 0 ? $items.length - 1 : currentSearchIndex - 1;
            }
            $items.eq(currentSearchIndex).addClass('active');
            $items.eq(currentSearchIndex)[0].scrollIntoView({ block: 'nearest' });
        }

        $searchInput.on('input', function() {
            renderSearchDropdown($(this).val().trim());
        });

        $searchInput.on('focus', function() {
            renderSearchDropdown($(this).val().trim());
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#headerSearch').length) {
                $searchDropdown.removeClass('active');
            }
        });

        $(document).on('keydown', '#globalNavSearch', function(e) {
            if (e.key === 'Escape') {
                $searchDropdown.removeClass('active');
                $searchInput.blur();
                return;
            }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                navigateSearch('down');
            }
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                navigateSearch('up');
            }
            if (e.key === 'Enter') {
                e.preventDefault();
                var $active = $searchDropdown.find('.search-dropdown-item.active');
                if ($active.length) {
                    window.location.href = $active.attr('href');
                }
            }
        });

        // Ctrl+K shortcut
        $(document).on('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                $searchInput.focus().select();
                renderSearchDropdown($searchInput.val().trim());
            }
        });

        // ==========================================
        // User Profile Dropdown
        // ==========================================
        $(document).on('click', '.header-user-trigger', function(e) {
            e.stopPropagation();
            $(this).closest('.header-user').toggleClass('open');
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#headerUserDropdown').length) {
                $('#headerUserDropdown').removeClass('open');
            }
        });

        $(document).on('keydown', '.header-user-trigger', function(e) {
            if (e.key === 'Escape') {
                $(this).closest('.header-user').removeClass('open');
            }
        });

        // ==========================================
        // Notification Dropdown
        // ==========================================
        $(document).on('click', '#notificationBellBtn', function(e) {
            e.stopPropagation();
            $(this).closest('.header-notification').toggleClass('open');
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#headerNotificationDropdown').length) {
                $('#headerNotificationDropdown').removeClass('open');
            }
        });

        $(document).on('keydown', '#notificationBellBtn', function(e) {
            if (e.key === 'Escape') {
                $(this).closest('.header-notification').removeClass('open');
            }
        });

        // Mark single notification as read from dropdown
        $(document).on('click', '.notification-dropdown-item.unread', function(e) {
            e.preventDefault();
            var $item = $(this);
            var id = $item.data('id');
            if (id) {
                $.post('<?= site_url('notifications/mark_read/') ?>' + id, {}, function(resp) {
                    if (resp.status) {
                        $item.removeClass('unread');
                        $item.find('.notification-dropdown-dot').removeClass('active');
                        setNotificationBadge(resp.unread_count);
                    }
                }, 'json').fail(function() {
                    $.getJSON('<?= site_url('notifications/unread_count') ?>', function(r) {
                        if (r.status) setNotificationBadge(r.count);
                    });
                });
            }
        });

        // Mark all as read from dropdown
        $(document).on('click', '#dropdownMarkAllRead', function(e) {
            e.preventDefault();
            var $btn = $(this);
            $btn.prop('disabled', true);
            $.post('<?= site_url('notifications/mark_all_read') ?>', {}, function(resp) {
                if (resp.status) {
                    $('.notification-dropdown-item.unread').removeClass('unread');
                    $('.notification-dropdown-dot.active').removeClass('active');
                    setNotificationBadge(resp.unread_count);
                    if (!resp.unread_count) {
                        $btn.fadeOut(200, function() { $(this).remove(); });
                    } else {
                        $btn.prop('disabled', false);
                    }
                } else {
                    $btn.prop('disabled', false);
                }
            }, 'json').fail(function() {
                $btn.prop('disabled', false);
                $.getJSON('<?= site_url('notifications/unread_count') ?>', function(r) {
                    if (r.status) setNotificationBadge(r.count);
                });
            });
        });



        // ==========================================
        // Notification Polling (every 15s, pauses when tab hidden)
        // ==========================================
        var notificationPollTimer = null;
        var lastUnreadCount = parseInt($('#notificationBadge').text()) || 0;

        function setNotificationBadge(count) {
            var $badge = $('#notificationBadge');
            if (count > 0) {
                var text = count > 9 ? '9+' : count;
                if ($badge.length) {
                    $badge.text(text);
                } else {
                    $('#notificationBellBtn').append('<span id="notificationBadge" class="notification-badge">' + text + '</span>');
                }
            } else {
                $badge.remove();
            }
        }
        window.setNotificationBadge = setNotificationBadge;

        function pollNotifications() {
            if (document.hidden || document.visibilityState === 'hidden') return;

            $.getJSON('<?= site_url('notifications/unread_count') ?>', function(resp) {
                if (resp.status) {
                    var count = parseInt(resp.count) || 0;
                    setNotificationBadge(count);

                    if (count > lastUnreadCount) {
                        if (typeof Swal !== 'undefined') {
                            var diff = count - lastUnreadCount;
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'info',
                                title: 'You have ' + diff + ' new notification' + (diff > 1 ? 's' : ''),
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                        }
                    }
                    lastUnreadCount = count;
                }
            });
        }

        function startNotificationPoll() {
            stopNotificationPoll();
            notificationPollTimer = setInterval(pollNotifications, 15000);
        }

        function stopNotificationPoll() {
            if (notificationPollTimer) {
                clearInterval(notificationPollTimer);
                notificationPollTimer = null;
            }
        }

        $(document).on('visibilitychange', function() {
            if (document.hidden || document.visibilityState === 'hidden') {
                stopNotificationPoll();
            } else {
                pollNotifications();
                startNotificationPoll();
            }
        });

        if (typeof document.hidden !== 'undefined') {
            startNotificationPoll();
        }

        // ==========================================
        // Fullscreen Toggle
        // ==========================================
        $(document).on('click', '#fullscreenBtn', function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(function() {});
            } else {
                document.exitFullscreen().catch(function() {});
            }
        });

        $(document).on('fullscreenchange', function() {
            var icon = $('#fullscreenIcon');
            if (document.fullscreenElement) {
                icon.removeClass('bi-arrows-fullscreen').addClass('bi-fullscreen-exit');
            } else {
                icon.removeClass('bi-fullscreen-exit').addClass('bi-arrows-fullscreen');
            }
        });

        // ==========================================
        // Contextual Navbar Search (Legacy)
        // ==========================================
        $(document).on('input', '#globalSearch', function() {
            var query = $(this).val().trim();
            var page = $(this).data('page');

            var tables = $('table.dataTable');
            if (tables.length) {
                tables.each(function() {
                    var dt = $(this).DataTable();
                    if (dt) {
                        dt.search(query).draw();
                    }
                });
                return;
            }

            if (query === '') {
                $('[data-searchable]').show();
                return;
            }

            var lq = query.toLowerCase();
            $('[data-searchable]').each(function() {
                var text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(lq) > -1);
            });
        });

        $(document).on('keydown', '#globalSearch', function(e) {
            if (e.key === 'Escape') {
                $(this).val('').trigger('input');
                $(this).blur();
            }
        });
    </script>

    <!-- Page JS -->
    <script src="<?= base_url('assets/js/employee.js'); ?>"></script>
    <script src="<?= base_url('assets/js/department.js'); ?>"></script>
    <script src="<?= base_url('assets/js/reports.js'); ?>"></script>
    <script src="<?= base_url('assets/js/hikes.js'); ?>"></script>

</body>
</html>
