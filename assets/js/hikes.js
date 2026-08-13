$(document).ready(function () {

    /* ==========================================================
       HIKES EMPLOYEE DATATABLE
    ========================================================== */

    if ($('#hikesTable').length) {
        initDataTable('#hikesTable', {
            order: [[0, 'asc']],
            pageLength: 10,
            lengthMenu: [[5,10, 25, 50, 100, -1], [5,10, 25, 50, 100, 'All']],
            language: {
                searchPlaceholder: 'Search employees...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                zeroRecords: 'No employees found.',
                emptyTable: 'No employee records available.',
                infoEmpty: 'Showing 0 to 0 of 0 entries',
                infoFiltered: '(filtered from _MAX_ total entries)'
            },
            columnDefs: [
                { orderable: false, targets: 4 }
            ]
        });
    }

});
